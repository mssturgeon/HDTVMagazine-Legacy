<?
	// If the user_id is given, then use that, otherwise load global and use currently logged in user.
	$emailed = false;
	if (isset($_GET[user_id])) {
		$emailed = true;
		define('BASE_DIR', '/var/www/html');
		require_once(BASE_DIR .'/includes/constants.php');
		require_once(BASE_DIR .'/includes/lib_mysql.php');
		require_once(BASE_DIR .'/includes/lib_common.php');

		$user_id = $_GET[user_id];
		$tzo = $_GET[tzo];
		$tzt = $_GET[tzt];
		$user_icons = $_GET[icons];
		$show_sd = isset($_GET[show_sd]);

		$result = mQuery("SELECT * FROM admin_settings");
		$admindata = mysql_fetch_array($result);
	} else {
		require('../global.php');
		$user_id = $user->data['user_id'];
		if (!access(ACCESS_PREMIUM)) prompt_login(PHP_SELF);

		$tzo = $user->data['time_zone_offset'];
		$tzt = $user->data['time_zone_text'];
		$user_icons = $user->data['icons'];
		$show_sd = $user->data['opt_showsd'];
	}
	$base_url = 'http://'. SERVER_NAME;

	$now = (isset($_GET[s])) ? $_GET[s] : time();
	$today = gmdate('m/d/Y', $now);
	$buffer_time = $now + 5*MINUTES;
	$start_display_time = $buffer_time - ($buffer_time % (30*MINUTES));

	function displayGrid($start_display_time, $tsSize, $user_time_offset, $user_id = 0, $show_sd = true, $tzo, $user_icons) {
		global $admindata;

		$round_unit = 15; // Round time to 15 minute chunks
		$base_url = 'http://'. SERVER_NAME; // All images are absolute references so that the email version works correctly.
		$height = 20; // Height of each row, used for accurately sizing scrollable div

		// The start... and end... display times are used for pulling data, while the adj_* times are used for display
		$end_display_time = $start_display_time + 24*HOURS;
		$adj_start_display_time = $start_display_time + $user_time_offset;
		$adj_end_display_time = $end_display_time + $user_time_offset;

		// Initialize the Grid Table
		echo '<table class="bare" style="table-layout:fixed;" cellspacing="0" cellpadding="0">'.
		'	<col width=190>'.
		'	<col width=524>'.
		'	<tr><td style="vertical-align:top">'.
		'		<table class="grid" style="width:100%" cellspacing=0 cellpadding=0>'.
		'			<tr><td class="stationLabel" height='. $height .'>&nbsp;</td></tr>'."\n";

		$qry = "
		SELECT stat.tf_station_num station_num, stat.tf_station_call_sign call_sign, image_file, channel, j.label
		FROM join_user_station j, $admindata[tbl_stat] stat
		WHERE j.tf_station_num = stat.tf_station_num
			AND user_id = $user_id
		ORDER BY j.priority, channel*1, j.label ASC";
		$rStations = mQuery($qry);
		if (mysql_num_rows($rStations) == 0) { // If no results were returned, let the user know
			echo '<tr><td class="stationLabel">No <a href="'. FULL_URL_PROFILE_STATIONS .'">Preferred Stations</a> Found.</td>';
			echo "</tr>\n";
		} else { // Loop through stations
			$num_stations = 0;
			while ($station_row = mysql_fetch_assoc($rStations)) {
				$num_stations++;
				$image_path = $base_url .'/images/logos/'. str_replace('[size].', '15.', $station_row[image_file]);
				$image = '<img alt="" height="15" src="'. $image_path .'" />';
				echo '<tr>'.
				'	<td class="stationLabel" height="'. $height .'" nowrap>'.
				'		<table class="bare" cellspacing=0 cellpadding=1 style="width:100%;table-layout:fixed" >'.
				'			<tr>'.
				'				<td class="stationImage">'. $image .'</td>'.
				'				<td><div class="grid"><a href="'. $base_url .'/programming/guide-station.php?id='. $station_row[station_num] .'">'. $station_row[label] .'</a></div></td>'.
				'				<td class="stationChannel">'. $station_row[channel] .'</td>'.
				'			</tr>'.
				'		</table>'.
				'	</td>'.
				"</tr>\n";
			}
		}
		$div_height = (($num_stations + 2) * $height);
		echo '<tr><td class="stationLabel" height='. $height .'>&nbsp;</td></tr>'.
		'		</table>'.
		'	</td><td style="vertical-align:top">'.
		'		<div style="overflow:auto;width:524;height:'. $div_height .'px;border-right:1px solid #'. BORDER_COLOR .'">'.
		'			<table class="grid" style="width:100%" cellspacing=0 cellpadding=0>'."\n";
		for ($x=0; $x<96; $x++) echo '<col width='. BLOCK_WIDTH .' height='. $height .'>'."\n";
		echo '<tr>';

		// Print timeslot headers
		for ($x=$adj_start_display_time; $x<$adj_end_display_time; $x+=($tsSize*MINUTES)) {
			echo '<td class="timeLabel" colspan=2 height='. $height .'>'. gmdate("g:ia", $x) .'</td>'."\n";
		}
		echo "</tr>\n";

		mysql_data_seek($rStations, 0);
		while ($station_row = mysql_fetch_assoc($rStations)) {
			// MAIN PROGRAM QUERY
			$show = ($show_sd) ? '' : " AND options & ". OPT_SKED_HDTV;
			$qry = "
			SELECT DISTINCT sked.*, prog.*
			FROM $admindata[tbl_prog] prog, $admindata[tbl_sked] sked
			WHERE sked.tf_database_key = prog.tf_database_key
				AND sked.tf_station_num = $station_row[station_num]
				AND (tf_air_time >= $start_display_time OR tf_air_time + (tf_duration*60) > $start_display_time)
				AND tf_air_time < $end_display_time
				$show
			ORDER BY tf_air_time ASC";
			$rProgramming = mQuery($qry);

			// If the result set is empty, fill with empty blocks
			if (mysql_num_rows($rProgramming) == 0) {
				for ($x=0; $x<96; $x++) echo '<td class="blank" height='. $height .'>&nbsp;</td>'."\n";
			} else { // Loop through entire day, and display programming
				$curTime = $adj_start_display_time;
				while ($row = mysql_fetch_assoc($rProgramming)) {
					$preTitle = '';
					$postTitle = '';
					$adj_air_time = $row[tf_air_time] + $user_time_offset;
					$round_start_time = round($adj_air_time / ($round_unit*MINUTES)) * ($round_unit*MINUTES);
					$round_end_time = round(($adj_air_time + $row[tf_duration]*MINUTES) / ($round_unit*MINUTES)) * ($round_unit*MINUTES);
					$col_start_time = $round_start_time;
					$col_end_time = $round_end_time;

					// Check overlap times
					if ($round_start_time < $adj_start_display_time) {
						$col_start_time = $adj_start_display_time;

						// Determine the number of '<'s to prepend
						$timeDiff = $adj_start_display_time - $round_start_time;
						for ($x=0; $x<ceil($timeDiff / ($round_unit*MINUTES)); $x+=2) $preTitle .= '&lt;';
					}

					if ($round_end_time > $adj_end_display_time) {
						$col_end_time = $adj_end_display_time;

						// Determine the number of '>'s to tack on
						$timeDiff = $round_end_time - $adj_end_display_time;
						for ($x=0; $x<ceil($timeDiff / ($round_unit*MINUTES)); $x+=2) $postTitle .= '&gt;';
					}

					$colspan = ceil(($col_end_time - $col_start_time) / MINUTES / $round_unit);

					// Front padding: Loop and display blanks until it is time to display the program bar
					while ($round_start_time > $curTime) {
						echo '<td class="blank">&nbsp;</td>'."\n";
						$curTime += $round_unit*MINUTES;
					}

					// **** Print out Program bar ****
					if ($colspan > 0) {
						$icons = getProgramIcons($row);
						$bar_icons = getProgramBarIcons($row, ($row[options] & OPT_SKED_HDTV), $tzo, $user_icons);
						$title = getProgramBarTitle($row, $colspan);
						$date = getProgramDate($row);
						$duration = getProgramDuration($row);

						$episode = $row[tf_epi_title];
						if ($row[tf_live_tape_delay] == 'Delay' | $row[tf_live_tape_delay] == 'Tape') $episode .= ' (Tape Delay)';

						$description = addslashes($row[tf_desc_160]);
						if ($episode !='') $description = '<i>'. addslashes($episode) .'</i><br>'. $description;

						// Ratings
						$rating = $row[tf_mpaa_rating];
						if ($rating == '') $rating = $row[tf_tv_rating];

						$program_class = ($row[options] & OPT_SKED_HDTV) ? 'programHD' : 'programSD';
						echo '<td colspan="'. $colspan .'" class="'. $program_class .'" height="'. $height .'">'.
							'<div class="grid">'.
								'<a href="'. FULL_URL_GUIDE_PROGRAM .'?k='. $row[tf_database_key] .'&amp;n='. $station_row[station_num] .'&amp;a='. $row[tf_air_time] .'">'. trim($preTitle .' '. $title .' '. $postTitle) .'</a>'. $bar_icons .
							"</div>\n".
						"</td>\n";
					}
					$curTime += $colspan * $round_unit*MINUTES;
				}

				// Rear padding
				while ($curTime < $adj_end_display_time) {
					echo '<td class="blank" width='. BLOCK_WIDTH .'>&nbsp;</td>'."\n";
					$curTime += $round_unit*MINUTES;
				}
			}
			echo "</tr>\n";
			mysql_free_result($rProgramming);
		}
		// Finish table
		echo '</table></div>'.
		'	</td>'.
		'</tr></table>';

		mysql_free_result($rStations);
		echo '<div style="font-size:8pt">* The double-D symbol is a registered trademark of Dolby Laboratories.</div>';
	}

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - Daily Program Grid  - <?=$today?></title>
	<script language="javascript" type="text/javascript">
		// Set OverLib Variables
		ol_width = 300;
		ol_offsety = 20;
		ol_cellpad = 0;
		ol_border=0;
	</script>
	<style>
		<?
			include(BASE_DIR .'/stylesheets/email_css.php');
			include(BASE_DIR .'/stylesheets/guide_css.php');
		?>
	</style>
	<?require(BASE_DIR .'/includes/lib_guide.php');?>
</head>
<body style="background-image:none">
	<div align="center" style="margin-top:20px;">

		<table border="0" cellpadding="2" cellspacing="2" class="layout">
			<tr >
				<td style="background-color:#ffffff;">

  					<?include(BASE_DIR .'/includes/header_email.php');?>

					<table cellpadding="2" cellspacing="1" style="width:100%">
						<tr style="background-color: #666699">
							<td width="10" ><img border="0" height="10" src="<?=$base_url?>/images/pixel.gif" width="10" /></td>
							<td colspan="1" rowspan="1" width="220" ><img border="0" height="10" src="<?=$base_url?>/images/pixel.gif" width="220" /></td>
							<td style="background-color:#333366;text-align:right;padding-right:5px;font-size:8pt;font-weight:bold;color:#ffffff;" width="100%"><?=gmdate('l, F d, Y', $now)?></td>
						</tr><tr>
							<td rowspan="4" style="background-color: #333366;text-align: right;padding-right: 5px;" ><img border="0" height="50" src="<?=$base_url?>/images/pixel.gif" width="10" /></td>
							<td colspan="2" style="background-color:#9999cc;font-size:16pt;padding-left:5px;font-family:Georgia,'Times New Roman',Times,serif;">Daily Program Grid</td>
						</tr><tr>
							<td colspan="2"><?
#								$google_channel = $GOOGLE_CHANNEL[Email_Leaderboard];
								include(BASE_DIR .'/ads/leaderboard_email.php');
							?></td>
						</tr><tr>
							<td colspan="2" style="background-color:#9999cc;font-size:12pt;padding:0px 0px 3px 5px;font-family:Georgia,'Times New Roman',Times,serif;font-weight:bold">
								<?=gmdate('g:ia', $start_display_time + $tzo)?> - <?=gmdate('g:ia', $start_display_time + $tzo + 24*HOURS)?> (<?=$tzt?>)
							</td>
						</tr><tr >
	  						<td colspan="2" style="">
								<?displayGrid($start_display_time, 30, $tzo, $user_id, $show_sd, $tzo, $user_icons);?>
							</td>
						</tr>
						<tr>
							<td style="background-color:#333366;"><img height="50" src="<?=$base_url?>/images/pixel.gif" width="10" /></td>
							<td colspan="2" rowspan="1" style="background-color: #9999CC;text-align: left;padding-left: 5px;" >
								<div style="text-align: left;padding: 0px 10px 0px 10px;" >
									<div style="line-height:20px">
										email: <a href="mailto:feedback@hdtvmagazine.com">feedback@hdtvmagazine.com</a>
									</div>
									<div style="line-height:20px">
										web: <a href="http://www.hdtvmagazine.com/">http://www.hdtvmagazine.com/</a>
									</div>
								</div>
							</td>
						</tr>
					</table>

				</td>
			</tr>
		</table>
	</div>

</body>
</html>
