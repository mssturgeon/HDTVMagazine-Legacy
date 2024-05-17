<?
	require('../global.php');

	$name = isset($_GET['name']) ? $_GET['name'] : '';
	$name_upper = strtoupper($name);

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - <?=$name_upper?> HDTV Network &amp; Programming</title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
	<meta name="description" content="<?=$name_upper?> HDTV Stations &amp; Programming. Listing HD Primetime programming and broadcast stations/markets.">
	<meta name="keywords" content="hdtv,hd,high definition,<?=$name?>,broadcast,market,programming,grid,listing,guide">
</head>
<body id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header-4.php');
		include(BASE_DIR .'/includes/lib_guide.php');

		function get_grid_station($source_id, $start_display_time, $timeslot_size, $user_time_offset, $guide_width, $show_sd, $user_icons) {
			global $admindata;

			$round_unit = 15; // Round time to 15 minute chunks
			$base_url = 'http://'. SERVER_NAME; // All images are absolute references so that the email version works correctly.
			$height = 20; // Height of each row, used for accurately sizing scrollable div

			// Initialize the Grid Table
			echo '<table class="grid" style="table-layout:fixed;" cellspacing="0" cellpadding="0">'.
			'	<col width=100>';

			// Set col elements based on guide width
			for ($i=0; $i < ($guide_width*2); $i++) echo '<col width='. BLOCK_WIDTH .'>';

			echo '<tr><td class="gridLabel" height='. $height .'>&nbsp;</td>'."\n";

			// Print timeslot headers
			for ($x=$start_display_time; $x<$start_display_time+($guide_width*$timeslot_size*MINUTES); $x+=$timeslot_size*MINUTES) {
				echo '<td class="timeLabel" colspan="2" height="'. $height .'"><div title="'. $x .'">'. gmdate("g:ia", $x + $user_time_offset) .'</div></td>';
			}
			echo '</tr>';

			$timestamp = time();
			$sql = 'SELECT DISTINCT FROM_UNIXTIME(air_time, "%Y-%m-%d") start_date
			FROM '. $admindata['tbl_guide'] .'
			WHERE air_time >= $timestamp
			ORDER BY start_date LIMIT 7';
			$dates_result = mQuery($sql);
			while ($dates_row = mysql_fetch_assoc($dates_result)) { # Loop through dates
				echo '<tr><td class="stationLabel" height="'. $height .'" nowrap>'. gmdate('l, F j', strtotime($dates_row['start_date'] .' 08:00pm GMT')) .'</td>';

#			while ($dates_row = mysql_fetch_assoc($dates_result)) {
				$start_display_time = strtotime($dates_row['start_date'] .' 08:00pm GMT') - $user->data['time_zone_offset'];
				$end_display_time = $start_display_time + $guide_width*$timeslot_size*MINUTES;
				$adj_start_display_time = $start_display_time + $user->data['time_zone_offset'];
				$adj_end_display_time = $end_display_time + $user->data['time_zone_offset'];

				// MAIN PROGRAM QUERY
				$sql = "
					SELECT *
					FROM {$admindata['tbl_guide']} guide
					WHERE
						guide.source_id = '$source_id'
						AND (air_time >= $start_display_time OR air_time + (duration*60) > $start_display_time)
						AND air_time < $end_display_time
						AND is_hdtv = 'Y'
					ORDER BY air_time ASC";
#				echo "$sql<br />";
				$res_guide = mQuery($sql);

				if (mysql_num_rows($res_guide) == 0) { // If the result set is empty, fill with empty blocks
					for ($x=$start_display_time; $x<$end_display_time; $x+=$round_unit*MINUTES) echo '<td class="blank">&nbsp;</td>';
				} else { // Loop through entire day, and display programming
					$curTime = $adj_start_display_time;
					while ($row = mysql_fetch_assoc($res_guide)) {
						$preTitle = '';
						$postTitle = '';
						$adj_air_time = $row['air_time'] + $user->data['time_zone_offset'];
						$round_start_time = round($adj_air_time / ($round_unit*MINUTES)) * ($round_unit*MINUTES);
						$round_end_time = round(($adj_air_time + $row['duration']*MINUTES) / ($round_unit*MINUTES)) * ($round_unit*MINUTES);
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
							$icons = getProgramIcons($row);
							$bar_icons = getProgramBarIcons($row, ($row['is_hdtv'] == 'Y'));
							$title = getProgramBarTitle($row, $colspan);
							$date = getProgramDate($row);
							$duration = getProgramDuration($row);

							$episode = $row['episode_title'];
#							if ($row[tf_live_tape_delay] == 'Delay' || $row[tf_live_tape_delay] == 'Tape') $episode .= ' (Tape Delay)';

							$description = addslashes($row['short_description']);
							if ($episode !='') $description = '<i>'. addslashes($episode) .'</i><br>'. $description;

/*
							// Ratings
							$rating = $row[tf_mpaa_rating];
							if ($rating == '') $rating = $row[tf_tv_rating];
*/

							$key = $row[source_id] . $row[program_id] . $row[air_time];
							$program_class = ($row['is_hdtv'] == 'Y') ? 'programHD' : 'programSD';
							$footer = array();
							$footer[] = $duration;
							$footer[] = $row[audio_level];
							$footer[] = $row[hdtv_level];
							echo '<td colspan="'. $colspan .'" class="'. $program_class .'" '.
							'	onContextMenu="openIMDb(\''. addslashes($row[title_128]) .'\');return false" '.
							'	onMouseOver="return overlib(popupText'. $key .', VAUTO, AUTOSTATUSCAP)" '.
							'	onMouseOut="return nd(2)"'.
							'><div class="grid">'.
							'		<a href="/programming/guide-program.php?p='. $row[program_id] .'&amp;s='. $row_source[source_id] .'&amp;a='. $row[air_time] .'">'. trim($preTitle .' '. $title .' '. $postTitle) .'</a>'. $bar_icons .
							"	</div>\n".
							'	<script language="javascript" type="text/javascript">'.
							'		var popupText'. $key .' = \''.
							'			<table class="popover" cellspacing="0">'.
							'				<tr>'.
							'					<td class="popoverCaption">'.
							'						<span style="float:right">'. gmdate("g:ia", $adj_air_time) .'</span>'.
													addslashes($row[title_128]) .
							'					</td>'.
							'				</tr><tr>'.
							'					<td class="popover">'. $description . $date .'</td>'.
							'				</tr><tr>'.
							'					<td class="popoverFooter">'.
							'						<span>'. $rating . $icons .'</span>'.
																	implode(', ', $footer) .
											'					</td>'.
							'				</tr>';
							if ($user_id == 1) {
								echo ''.
								'			<tr>'.
								'				<td class="popoverFooter">colspan: '. $colspan .'</td>'.
								'			</tr><tr>'.
								'				<td class="popoverFooter">air_time: '. $row[air_time] .' ('. date('r', $row[air_time]) .')</td>'.
								'			</tr><tr>'.
								'				<td class="popoverFooter">duration: '. $row[duration] .'</td>'.
								'			</tr>';
							}
							echo '</table>\';'.
							'	</script>'.
							'</td>';
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
#			}
			}
			// Finish table
			echo '</table>';

			mysql_free_result($dates_result);
			echo '<div style="font-size:8pt">* The double-D symbol is a registered trademark of Dolby Laboratories.</div>';
		}
	?>

	<h1><img src="/images/logos/<?=strtolower($name)?>_25.gif" alt="<?=$name_upper?>" align="absmiddle"> <?=$name_upper?> HDTV Stations &amp; Programming</h1>
	<div style="float:left;">
		&raquo; <a href="<?=URL_PROG?>">Programming</a>
		&raquo; <a href="<?=URL_PROG_BROADCAST?>">Broadcast</a>
		&raquo; <?=$name_upper?>
		<br /><br />
	</div>

	<div id="right" style="float:right; margin:0 0 5px 5px; text-align:center;" align="center">
		<? include(BASE_DIR .'/ads/mrectangle.php');?>
		<br />
		<div align="center">
			<? include(BASE_DIR .'/ads/skyscraper.php');?>
		</div>
	</div>

	<br clear="left">
	<?
		$sql = "SELECT s.source_id
		FROM prog_source s, aux_prog_source a
		WHERE s.source_id = a.source_id
			AND primary_station = 1
			AND call_letters = '$name_upper'";
		$result = mQuery($sql);
		if (mysql_num_rows($result) == 1) {
			$row = mysql_fetch_assoc($result);
			$source_id = $row['source_id'];

			echo "<h2>$name_upper HD Primetime This Week</h2><br />";

			# Set grid parameters
			$timeslot_size = 30;

			# Load parameters for the grid based on which user is logged in, if any
			if (access(ACCESS_PREMIUM)) { // Get current time adjusted to user timezone settings
				$user_id = $user->data['user_id'];
				$user_time_offset = $user->data['time_zone_offset'];
				$show_sd = $user->data['opt_showsd'];
				$user_icons = $user->data['icons'];
			} else { // Set to Eastern, adjusted for DST
				$user_id = 0;
				$user_time_offset = -18000 + (1*HOURS*date("I"));
				$show_sd = true;
				$user_icons = ICON_SHOW_NEW & ICON_SHOW_DD;
			}

			echo getProgramLegend() .'<br />';
			$start_display_time = strtotime('8:00pm GMT') - $user_time_offset;
			echo get_grid_station($source_id, $start_display_time, $timeslot_size, $user_time_offset, 6, $show_sd, $user_icons);
			echo "<br /><br />";
		}
	?>

	<div style="float:left">
		<h2><?=$name_upper?> Broadcast Markets/Stations</h2>
		<table class="type1b" cellpadding="0" cellspacing="0">
			<tr>
				<td class="type1b_header" style="text-align:left">Market</td>
				<td class="type1b_header" style="text-align:right">DMA Rank</td>
				<td class="type1b_header">Channels</td>
			</tr><?
				$x = 0;
				$sql = "
				SELECT dma_name, dma_rank
				FROM prog_source
				WHERE dma_name <> ''
					AND affiliation_1 LIKE '$network%'
				ORDER BY dma_name";
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result)) {
					$dma_name = $row['dma_name'];

					$dma_rank[$dma_name] = $row['dma_rank'];
					$dma_array[$dma_name] += 1;
					$net[$dma_name] += 1;
				}

				foreach($dma_array as $dma_name => $total) {
					echo '<tr onmouseover="hover_over(this)" onmouseout="hover_out(this)">'.
					'	<td class="grid" style="text-align:left"><a href="/programming/broadcast-market.php?dma_name[]='. urlencode($dma_name) .'">'. $dma_name .'</a></td>'.
					'	<td class="grid" style="text-align:right">'. $dma_rank[$dma_name] .'</td>'.
					'	<td class="grid" style="text-align:center">'. ($net[$dma_name] == 0 ? '-' : $net[$dma_name]) .'</td>'.
					'</tr>';
				}
			?>
		</table>
	</div>

	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
</body>
</html>
