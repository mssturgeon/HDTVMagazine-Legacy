<?
	require('../global.php');
	require(BASE_DIR .'/includes/lib_guide.php');

	$debug = isset($_GET['debug']);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>HDTV Magazine - HDTV Programming Guide</title>

	<?require(BASE_DIR .'/includes/common_header.php');?>
	<?($userdata['guide_refresh'] != 0) ? print '<meta http-equiv="refresh" content="'. $userdata['guide_refresh'] .'">'."\n" : print ''?>
	<meta http-equiv="expires" value="-1">
	<meta http-equiv="pragma" content="no-cache">
	<meta name="description" content="HDTV Magazine Programming Guide - Customize your stations, channel labels and receive it daily via email">
	<meta name="keywords" content="hdtv,hdtv programming,hdtv guide,hdtv schedule,hdtv listing,hdtv guides,hdtv schedules,hdtv listings,hdtv programs,hdtv programming guide,hdtv programming schedule,hdtv program,hd guide,hd programming,hd schedule,abc hdtv programming,cbs hdtv programming,digital tv programming,digital tv schedule,digital tv schedules,dtv listings,dtv program,dtv programing,dtv programming,dtv schedule,hd programing,hd programming,hd programs,high definition guide,high definition programing,high definition programming,high definition programs,high definition schedule,nbc hdtv programming,guide,news,sports,hdtv stations">

	<link rel="stylesheet" type="text/css" href="/stylesheets/guide_css.php">
	<script language="javascript" type="text/javascript">
		// Make sure we're not running in a frameset
		if (top != self) top.location.href = self.location.href;

		// Set OverLib Variables
		ol_width = 300;
		ol_offsety = 20;
		ol_cellpad = 0;
		ol_border=0;

		function showTime(t) {
			l = document.location.href.indexOf('?');
			document.location.href = document.location.href.substr(0, l) + '?s='+t;
		}
	</script>
</head>
<body id="body_container">
	<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
	<?
		include(BASE_DIR .'/ads/leaderboard.php');
		include(BASE_DIR .'/includes/body_header.php');

		function display_grid_main($start_display_time, $tsSize, $user_time_offset, $guide_width = 8, $user_id = 0, $show_sd = true, $user_icons) {
			// Globals
			global $gmt_time, $admindata, $userdata, $debug;

			$round_unit = 15; // Round time to 15 minute chunks

### Is this needed?
			$base_url = 'http://'. SERVER_NAME; // All images are absolute references so that the email version works correctly.
			$base_url = '';

			// The start... and end... display times are used for pulling data, while the adj_* times are used for display
			$end_display_time = $start_display_time + ($guide_width * $tsSize*MINUTES);
			$adj_start_display_time = $start_display_time + $user_time_offset;
			$adj_end_display_time = $end_display_time + $user_time_offset;

			// Initialize the Grid Table
			echo '<table class="grid" cellspacing=0 cellpadding=0>'.
			'	<col width=175>';

			// Set col elements based on guide width
				for ($i=0; $i < ($guide_width*2); $i++) echo '<col width='. BLOCK_WIDTH .'>';

			echo '	<tr height=22><td class="stationLabel">';
			if ($user_id > 0) {
				echo '<table cellpadding="0" cellspacing="0" width="100%"><tr>'.
				'	<td style="width:25px;text-align:center">'.
				'		<a href="'. FULL_URL_GUIDE .'?s='. ($gmt_time - 4*HOURS) .'"><img src="'. $base_url .'/images/previous.gif" alt="-4" border="0"></a>'.
				'	</td><td>'.
				'		&nbsp;'.
				'	</td><td style="width:25px;text-align:center">'.
				'		<a href="'. FULL_URL_GUIDE .'?s='. ($gmt_time + 4*HOURS) .'"><img src="'. $base_url .'/images/next.gif" alt="+4" border="0"></a>'.
				'	</td>'.
				'</tr></table>';
			} else {
				echo '&nbsp;';
			}
			echo '</td>';

			// Print timeslot headers
			for ($x=$adj_start_display_time; $x<$adj_end_display_time; $x+=($tsSize*MINUTES)) {
				echo '<td class="timeLabel" colspan="2">'. gmdate("g:ia", $x) .'</td>';
			}
			echo "</tr>\n";

			// If the user is not a premium member, and no URL parameters are given, then we display default station's, as defined by the administrator.
			if (access(ACCESS_PREMIUM)) {
				$guide_user = $userdata['user_id'];
			} else {
				$guide_user = -1;
			}
			$sql = "
			SELECT source.source_id, source.short_name, channel, j.label, img
			FROM $admindata[tbl_source] source, j_user_source j, aux_prog_source a
			WHERE source.source_id = j.source_id
				AND source.source_id = a.source_id
				AND user_id = $guide_user
			ORDER BY j.sort, channel*1, j.label ASC";
			$res_source = mQuery($sql);
			if ($debug) echo "$sql<br />";

			// If not results were returned, let the user know
			if (mysql_num_rows($res_source) == 0) {
				echo '<tr><td class="stationLabel">No <a href="profile-stations.php">Preferred Stations</a> Found</td>';
				for ($i=0; $i < ($guide_width*2); $i++) echo '<td class="blank">&nbsp;</td>';
				echo '</tr>';
			} else { // Loop through stations
				while ($row_source = mysql_fetch_assoc($res_source)) {
					echo
						'<tr>'.
							'<td class="stationLabel">'.
								'<table class="bare" cellspacing=1 style="width:100%;table-layout:fixed;"><tr>'.
									'<td class="stationImage"><img alt="" height="15" src="/images/logos/'. str_replace('[size].', '15.', $row_source['img']) .'"></td>'.
									'<td><div class="grid">'.
										'<a href="'. $base_url . '/programming/guide-station.php?name='. dirify($row_source['label']) .'&id='. $row_source['source_id'] .'">'. $row_source['label'] .'</a>'.
									'</div></td>'.
									'<td class="stationChannel">'. $row_source['channel'] .'</td>'.
								'</tr></table>'.
						"	</td>\n";

					### MAIN PROGRAM QUERY ###
					$show = ($show_sd) ? '' : " AND is_hdtv = 'Y'";
					$sql = "
						SELECT *
						FROM $admindata[tbl_guide] guide
						WHERE
							guide.source_id = $row_source[source_id]
							AND (air_time >= $start_display_time OR air_time + (duration*60) > $start_display_time)
							AND air_time < $end_display_time
							$show
						ORDER BY air_time ASC";
					if ($debug) echo "$sql<br />";
					$res_guide = mQuery($sql);

					// If the result set is empty, fill with empty blocks
					if (mysql_num_rows($res_guide) == 0) {
						for ($x=0; $x<$guide_width*2; $x++) echo '<td class="blank">&nbsp;</td>';
					} else { // Loop through entire day, and display programming
						$curTime = $adj_start_display_time;
						while ($row = mysql_fetch_assoc($res_guide)) {
							$preTitle = '';
							$postTitle = '';
							$adj_air_time = $row['air_time'] + $user_time_offset;
							$round_start_time = round($adj_air_time / ($round_unit*MINUTES)) * ($round_unit*MINUTES);
							$round_end_time = round(($adj_air_time + $row[duration]*MINUTES) / ($round_unit*MINUTES)) * ($round_unit*MINUTES);
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
								echo '<td class="blank">&nbsp;</td>';
								$curTime += $round_unit*MINUTES;
							}

							// **** Print out Program bar ****
							if ($colspan > 0) {
### Need to update functions to return correct columns
								$icons = getProgramIcons($row);
#								$bar_icons = getProgramBarIcons($row, ($row[flags] & FLAG_HDTV), $_SESSION[time_zone_offset], $_SESSION[icons]);
								$bar_icons = getProgramBarIcons($row, ($row['is_hdtv'] == 'Y'));
								$title = getProgramBarTitle($row, $colspan);
								$date = getProgramDate($row);
								$duration = getProgramDuration($row);

								$episode = $row['episode_title'];
#								if ($row[tf_live_tape_delay] == 'Delay' | $row[tf_live_tape_delay] == 'Tape') $episode .= ' (Tape Delay)';

								$description = addslashes($row[short_description]);
								if ($episode !='') $description = '<i>'. addslashes($episode) .'</i><br>'. $description;

### Need to get ratings from prog_rating table
/*
								// Ratings
								$rating = $row[tv_rating];
								if ($rating == '') $rating = $row[tf_tv_rating];
*/

								$key = $row[source_id] . $row['program_id'] . $row['air_time'];
								$program_class = ($row['is_hdtv'] == 'Y') ? 'programHD' : 'programSD';
                        $footer = array();
                        $footer[] = $duration;
                        $footer[] = $row['audio_level'];
                        $footer[] = $row['hdtv_level'];
								echo '<td colspan="'. $colspan .'" class="'. $program_class .'" '.
								'	onContextMenu="openIMDb(\''. addslashes($row['title_128']) .'\');return false" '.
								'	onMouseOver="return overlib(popupText'. $key .', VAUTO, AUTOSTATUSCAP)" '.
								'	onMouseOut="return nd(2)"'.
								'><div class="grid">'.
								'		<a href="/programming/guide-program.php?p='. $row['program_id'] .'&amp;s='. $row_source['source_id'] .'&amp;a='. $row['air_time'] .'">'. trim($preTitle .' '. $title .' '. $postTitle) .'</a>'. $bar_icons .
								"	</div>\n".
								'	<script language="javascript" type="text/javascript">'.
								'		var popupText'. $key .' = \''.
								'			<table class="popover" cellspacing="0">'.
								'				<tr>'.
								'					<td class="popoverCaption">'.
								'						<span style="float:right">'. gmdate("g:ia", $adj_air_time) .'</span>'.
														addslashes($row['title_128']) .
								'					</td>'.
								'				</tr><tr>'.
								'					<td class="popover">'. $description . $date .'</td>'.
								'				</tr><tr>'.
								'					<td class="popoverFooter">'.
								'						<span>'. $rating . $icons .'</span>'.
                        						implode(', ', $footer) .
                        '					</td>'.
								'				</tr>';
								if (access(ACCESS_ADMIN)) {
									echo ''.
									'			<tr>'.
									'				<td class="popoverFooter">colspan: '. $colspan .'</td>'.
									'			</tr><tr>'.
									'				<td class="popoverFooter">air_time: '. $row['air_time'] .' ('. date('r', $row['air_time']) .')</td>'.
									'			</tr><tr>'.
									'				<td class="popoverFooter">duration: '. $row['duration'] .'</td>'.
									'			</tr>';
								}
								echo '</table>\';'.
								'	</script>'.
								"</td>\n";
							}
							$curTime += $colspan * $round_unit*MINUTES;
						}

						// Rear padding
						while ($curTime < $adj_end_display_time) {
							echo '<td class="blank">&nbsp;</td>';
							$curTime += $round_unit*MINUTES;
						}
					}
					echo "</tr>\n";
					mysql_free_result($res_guide);
				}
				// Finish table
				echo '</table>';
				mysql_free_result($res_source);
			}
		} /*** END displayGuide ***/

		$gmt_time = isset($_GET[s]) ? $_GET[s] : time();
		if (isset($_GET['primetime'])) {
			$gmt_time = strtotime($userdata['prime_time'] . str_replace(':', '', strright($userdata['time_zone_text'], 'GMT')));
		}

		// Set grid parameters
		$tsSize = 30;

		// This computes time offset based on the users profile settings
		if (access(ACCESS_PREMIUM)) { // Get current time adjusted to user timezone settings
			$user_id = $userdata['user_id'];
			$user_time_offset = $userdata['time_zone_offset'];
			$bufferTime = $gmt_time + 5*MINUTES;
			$start_display_time = $bufferTime - ($bufferTime % ($tsSize*MINUTES));
			$show_sd = $userdata['options'] & OPT_SHOW_SD;
			$user_icons = $userdata['icons'];
		} else { // Set to 8:00pm Eastern, adjusted for DST
			$user_id = 0;
			$user_time_offset = -18000 + (1*HOURS*date("I"));
			$start_display_time = strtotime('Tomorrow 1:00am GMT') - (1*HOURS*date('I'));
			$show_sd = true;
			$user_icons = ICON_SHOW_NEW & ICON_SHOW_DD;
		}
	?>

	<?
		if(access(ACCESS_PREMIUM)) { //Only show the date/time selections to registered users?>
			<div class="item" style="display:table"><span class="corners-top"><span></span></span>
				<span class="label">Programming Menu:</span>
				<a href="/profile-guide.php">Edit Channels</a>
				&bull; <a href="guide.php?primetime">Primetime Tonight</a>
				<!--
				&bull; <a href="guide-movie.php">Movie Guide</a>
				&bull; <a href="sports/index.php">HDTV Sports</a>
				&bull; <a href="stations/stations-by-market.php">Stations by Market</a>
				&bull; <a href="stations/stations-by-provider.php">Stations by Provider</a>
				&bull; <a href="search.php">Search</a>
				-->
			<span class="corners-bottom"><span></span></span></div>

			<div><table class="bare" cellspacing="2" cellpadding="2" width="100%"><tr>
			<form name="frmTimeSelect" autocomplete="off">
				<td class="inputLabel">Date:</td>
				<td nowrap>
					<select id="date_select" onChange="showTime(this.value)"><?
						$today = gmdate("j", $gmt_time);
						$result = mQuery("SELECT DISTINCT FROM_UNIXTIME(air_time,'%Y-%m-%d') as air_date FROM $admindata[tbl_guide] ORDER BY air_date");
						while($row = mysql_fetch_array($result)) {
							$air_date = strtotime($row[air_date] ." ". gmdate("g:ia", $start_display_time) ." GMT");
							$selected = gmdate("j", $air_date) == $today ? 'SELECTED' : '';
							echo '<option value="'. $air_date .'" '. $selected .'>'. gmdate("l, j F, Y", $air_date + $user_time_offset);
						}
					?></select>
				</td>
				<td class="inputLabel">Time:</td>
				<td>
					<select id="time_select" name="grid_start" onChange="showTime(this.value)"><?
						$start = strtotime("Today 12:00am", $gmt_time);
						$increment = 60*MINUTES;
						for ($x = $start; $x < $start + 24*HOURS; $x += $increment) {
							$selected = ($x == floor($start_display_time/$increment) * $increment) ? 'SELECTED' : '';
							echo '<option value="'. $x .'" '. $selected .'>'. gmdate("g:ia", ($x+$user_time_offset)) .' - '. gmdate("g:ia", ($x+$user_time_offset)+($tsSize*$userdata['guide_width']*MINUTES)) ."\n";
						}
					?></select>
				</td>
				<td class="inputLabel" align="left" nowrap><?=$userdata['time_zone_text']?></td>
				<td align="right" width="100%">
					<?echo getProgramLegend();?>
				</td>
			</form>
			</tr></table><?
		} else { ?>
			<h1>HDTV Program Guide</h1>
			<div id="eq-mrec"><?include(BASE_DIR .'/ads/mrectangle.php');?></div>
			&raquo; <a href="<?=URL_PROG?>">Programming</a>
			&raquo; <b>Guide</b>

			<div class="important" style="display:table"><span class="corners-top"><span></span></span>
				<table class="bare"><tr><td style="vertical-align:top">
					<img src="/images/i_subscribe.gif" alt="Subscribe" style="float:left; margin-right:10px" />
				</td><td>
					<div class="label"><a href="/subscribe/index.php">Subscribe</a> to a Premium Membership and customize your guide:</div><br />
					<ul>
						<li>Add your local network, cable &amp; satellite channels</li>
						<li>Show up to 8 hours at a time
						<li>Rename and assign channel numbers</li>
						<li>Receive a daily listing of HD programming via email</li>
						<li>Hide advertisements</li>
						<li>and more ...</li>
					</ul>
					<a href="/subscribe/index.php">Click here for more details</a>
				</td></tr></table>
			<span class="corners-bottom"><span></span></span></div>

			<br id="eq_br" />
			<div id="eq-sky"><?include(BASE_DIR .'/ads/skyscraper.php');?></div>

			<div style="display:table">
			<table class="bare" width="100%"><tr>
				<td style="font-size:16pt;font-weight:bold;padding-top:10px;">East Coast Prime Time</td>
				<td style="font-size:10pt" valign="bottom" align="right"><?=gmdate("j F, Y", $start_display_time + $user_time_offset)?></td>
			</tr></table><?
		}
		display_grid_main($start_display_time, $tsSize, $user_time_offset, $userdata['guide_width'], $user_id, $show_sd, $user_icons);
	?></div>
	<div style="font-size:7pt">* The double-D symbol is a registered trademark of Dolby Laboratories.</div>

	<?include(BASE_DIR .'/includes/body_footer.php');?>

	<form name="frmIMDb" method="post" target="_new" action="http://www.imdb.com/Find">
		<input type="hidden" name="for" value="">
	</form>
</body>
</html>
