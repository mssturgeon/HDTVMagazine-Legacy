<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>HDTV Magazine - <?=$network?> HDTV Stations &amp; Programming</title>
	<?require(BASE_DIR .'/includes/common_header.php');?>
	<meta name="description" content="<?=$network?> HDTV Stations &amp; Programming">
	<meta name="keywords" content="hdtv,hd,high definition,<?=$network?>,broadcast">
</head>
<body>
	<?
		include(BASE_DIR .'/ads/leaderboard.php');
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/includes/lib_guide.php');
		
		function get_grid_provider($provider_id, $start_display_time, $timeslot_size, $user_time_offset, $guide_width, $show_sd, $user_icons) {
			// Globals
			global $gmt_time, $admindata;
			
			$round_unit = 15; // Round time to 15 minute chunks
			$base_url = 'http://'. SERVER_NAME; // All images are absolute references so that the email version works correctly.
	
			// The start... and end... display times are used for pulling data, while the adj_* times are used for display
			$end_display_time = $start_display_time + ($guide_width * $timeslot_size*MINUTES);
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
			for ($x=$start_display_time; $x<$start_display_time+($guide_width*$timeslot_size*MINUTES); $x+=$timeslot_size*MINUTES) {
				echo '<td class="timeLabel" colspan="2" height="'. $height .'"><div title="'. $x .'">'. gmdate("g:ia", $x + $user_time_offset) .'</div></td>';
			}
			echo '</tr>';
	
			// If the user is not logged in, and no URL parameters are given, then we display default station's, as defined by the administrator.
			$qry = "
			SELECT stat.tf_station_num, stat.tf_station_call_sign, image_file, channel, j.label
			FROM join_provider_station j, $admindata[tbl_stat] stat
			WHERE j.tf_station_num = stat.tf_station_num
				AND provider_id = $provider_id
			ORDER BY channel*1, j.label ASC";
			$rStations = mQuery($qry);
			
			// If not results were returned, let the user know			
			if (mysql_num_rows($rStations) == 0) {
				echo '<tr><td class="stationLabel">No Stations Found</td>';
				for ($i=0; $i < ($guide_width*2); $i++) echo '<td class="blank">&nbsp;</td>';
				echo '</tr>';
			} else { // Loop through stations
				while ($station_row = mysql_fetch_assoc($rStations)) {
					$image_path = $base_url .'/images/logos/'. str_replace('[size].', '15.', $station_row[image_file]);
					$image = '<img alt="" height="15" src="'. $image_path .'">';
					echo 
						'<tr>'.
						'	<td class="stationLabel">'.
						'		<table class="bare" cellspacing=1 style="width:100%;table-layout:fixed;">'.
						'			<tr>'.
						'				<td class="stationImage">'. $image .'</td>'.
						'				<td><div class="grid"><a href="'. $base_url . '/programming/guide-station.php?id='. $station_row[tf_station_num] .'">'. $station_row[label] .'</a></div></td>'.
						'				<td class="stationChannel">'. $station_row[channel] .'</td>'.
						'			</tr>'.
						'		</table>'.
						"	</td>\n";
	
					// MAIN PROGRAM QUERY
					$qry = "
					SELECT sked.*, prog.*
					FROM $admindata[tbl_prog] prog, $admindata[tbl_sked] sked
					WHERE sked.tf_database_key = prog.tf_database_key
						AND sked.tf_station_num = $station_row[tf_station_num]
						AND (tf_air_time >= $start_display_time OR tf_air_time + (tf_duration*60) > $start_display_time)
						AND tf_air_time < $end_display_time
						AND options & ". OPT_SKED_HDTV ."
					ORDER BY tf_air_time ASC";
					$rProgramming = mQuery($qry);

					// If the result set is empty, fill with empty blocks
					if (mysql_num_rows($rProgramming) == 0) {
						for ($x=$start_display_time; $x<$end_display_time; $x+=$round_unit*MINUTES) echo '<td class="blank">&nbsp;</td>';
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
								echo '<td class="blank">&nbsp;</td>';
								$curTime += $round_unit*MINUTES;
							}
	
							// **** Print out Program bar ****
							if ($colspan > 0) {
								$icons = getProgramIcons($row);
								$bar_icons = getProgramBarIcons($row, ($row[options] & OPT_SKED_HDTV), $_SESSION[time_zone_offset], $_SESSION[icons]);
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
								echo '<td colspan="'. $colspan .'" class="'. $program_class .'" '.
								'	onContextMenu="openIMDb(\''. addslashes($row[tf_title]) .'\');return false" '.
								'	onMouseOver="return overlib(popupText'. $row[tf_database_key] . $row[tf_air_time] .', VAUTO, AUTOSTATUSCAP)" '.
								'	onMouseOut="return nd(2)"'.
								'><div class="grid">'.
								'		<a href="'. FULL_URL_GUIDE_PROGRAM .'?k='. $row[tf_database_key] .'&amp;n='. $station_row[station_num] .'&amp;a='. $row[tf_air_time] .'">'. trim($preTitle .' '. $title .' '. $postTitle) .'</a>'. $bar_icons .
								"	</div>\n".
								'	<script language="javascript" type="text/javascript">'.
								'		var popupText'. $row[tf_database_key] . $row[tf_air_time] .' = \''.
								'			<table class="popover" cellspacing="0">'.
								'				<tr>'.
								'					<td class="popoverCaption">'. addslashes($row[tf_title]) .'</td>'.
								'					<td class="popoverCaption" align="right">'. gmdate("g:ia", $adj_air_time) .'</td>'.
								'				</tr><tr>'.
								'					<td colspan="2" class="popover">'. $description . $date .'</td>'.
								'				</tr><tr>'.
								'					<td class="popoverFooter">'. $duration .'</td>'.
								'					<td class="popoverFooter" align="right" valign="middle"><span style="vertical-align:middle;font-weight:bold;font-size:6pt">'. $rating .'</span> '. $icons .'</td>'.
								'				</tr>';
								if ($user_id == 1) {
									echo ''.
									'			<tr>'.
									'				<td class="popoverFooter">colspan</td><td class="popoverFooter">'. $colspan .'</td>'.
									'			</tr><tr>'.
									'				<td class="popoverFooter">tf_org_air_date</td><td class="popoverFooter">'. $row[tf_org_air_date] .'</td>'.
									'			</tr><tr>'.
									'				<td class="popoverFooter">tf_air_date</td><td class="popoverFooter">'. $row[tf_air_date] .'</td>'.
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
					mysql_free_result($rProgramming);
				}
				// Finish table
				echo '</table>';
				mysql_free_result($rStations);
			}
			echo '<div style="font-size:8pt">* The double-D symbol is a registered trademark of Dolby Laboratories.</div>';
		}
	?>

	<div style="width:100%"><table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="ad-left">
			<?include(BASE_DIR .'/ads/skyscraper.php');?>
		</td><td style="vertical-align:top;">
			<h1><img src="/images/logos/<?=$network_lc?>_25.gif" alt="<?=$network?>" align="absmiddle"> <?=$network?> HDTV Stations &amp; Programming</h1>
			&raquo; <a href="<?=URL_PROG?>">Programming</a>
			&raquo; <a href="satellite.php">Satellite</a>
			&raquo; <?=$network?>
			<br />
			<br />
			<br />
