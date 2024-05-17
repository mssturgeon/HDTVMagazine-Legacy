<?
	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - <?=$network?> HDTV Stations &amp; Programming</title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
	<meta name="description" content="<?=$network?> HDTV Stations &amp; Programming">
	<meta name="keywords" content="hdtv,hd,high definition,<?=$network?>,broadcast">
</head>
<body>
	<?
		include(BASE_DIR .'/includes/body_header-4.php');
		include(BASE_DIR .'/includes/lib_guide.php');

		function get_grid_station($station_num, $start_display_time, $timeslot_size, $user_time_offset, $guide_width, $show_sd, $user_icons) {
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

			$dates_result = mQuery("SELECT DISTINCT tf_air_date FROM tms_dgskedrec WHERE tf_air_date >= SYSDATE() ORDER BY tf_air_date LIMIT 7");
			while ($dates_row = mysql_fetch_assoc($dates_result)) { # Loop through dates
				echo '<tr><td class="stationLabel" height="'. $height .'" nowrap>'. gmdate('l, F j', strtotime($dates_row[tf_air_date] .' 08:00pm GMT')) .'</td>';

#			while ($dates_row = mysql_fetch_assoc($dates_result)) {
				$start_display_time = strtotime($dates_row[tf_air_date] .' 08:00pm GMT') - $user->data[time_zone_offset];
				$end_display_time = $start_display_time + $guide_width*$timeslot_size*MINUTES;
				$adj_start_display_time = $start_display_time + $user->data[time_zone_offset];
				$adj_end_display_time = $end_display_time + $user->data[time_zone_offset];

				// MAIN PROGRAM QUERY
				$qry = "
				SELECT sked.*, prog.*
				FROM $admindata[tbl_prog] prog, $admindata[tbl_sked] sked
				WHERE sked.tf_database_key = prog.tf_database_key
					AND sked.tf_station_num = $station_num
					AND (tf_air_time >= $start_display_time OR tf_air_time + (tf_duration*60) > $start_display_time)
					AND tf_air_time < $end_display_time
					AND options & ". OPT_SKED_HDTV ."
				ORDER BY tf_air_time ASC";
				$rProgramming = mQuery($qry);

				if (mysql_num_rows($rProgramming) == 0) { // If the result set is empty, fill with empty blocks
					for ($x=$start_display_time; $x<$end_display_time; $x+=$round_unit*MINUTES) echo '<td class="blank">&nbsp;</td>';
				} else { // Loop through entire day, and display programming
					$curTime = $adj_start_display_time;
					while ($row = mysql_fetch_assoc($rProgramming)) {
						$preTitle = '';
						$postTitle = '';
						$adj_air_time = $row[tf_air_time] + $user->data[time_zone_offset];
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
							$bar_icons = getProgramBarIcons($row, ($row[options] & OPT_SKED_HDTV), $user->data[time_zone_offset], $user->data[icons]);
	#						$bar_icons = getProgramBarIcons($row, true, $_SESSION[time_zone_offset], $_SESSION[icons]);
							$title = getProgramBarTitle($row, $colspan);
							$date = getProgramDate($row);
							$duration = getProgramDuration($row);

							$episode = $row[tf_epi_title];
							if ($row[tf_live_tape_delay] == 'Delay' || $row[tf_live_tape_delay] == 'Tape') $episode .= ' (Tape Delay)';

							$description = addslashes($row[tf_desc_160]);
							if ($episode !='') $description = '<i>'. addslashes($episode) .'</i><br>'. $description;

							// Ratings
							$rating = $row[tf_mpaa_rating];
							if ($rating == '') $rating = $row[tf_tv_rating];

							$program_class = ($row[options] & OPT_SKED_HDTV) ? 'programHD' : 'programSD';
							echo '<td colspan="'. $colspan .'" class="'. $program_class .'" height="'. $height .'"'.
	#						echo '<td colspan="'. $colspan .'" class="program" '.
							'	onContextMenu="openIMDb(\''. addslashes($row[tf_title]) .'\');return false" '.
							'	onMouseOver="return overlib(popupText'. $row[tf_database_key] . $row[tf_air_time] .', VAUTO, AUTOSTATUSCAP)" '.
							'	onMouseOut="return nd(2)"'.
							'><div class="grid">'.
							'		<a href="'. URL_GUIDE_PROGRAM .'?k='. $row[tf_database_key] .'&amp;n='. $station_num .'&amp;a='. $row[tf_air_time] .'">'. trim($preTitle .' '. $title .' '. $postTitle) .'</a>'. $bar_icons .
		  					'	</div>'.
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
							if (access(ACCESS_ADMIN)) {
								echo ''.
			  					'			<tr>'.
			  					'				<td class="popoverFooter">colspan</td><td class="popoverFooter">'. $colspan .'</td>'.
			  					'			</tr><tr>'.
			  					'				<td class="popoverFooter">tf_air_time</td><td class="popoverFooter">'. $row[tf_air_time] .'</td>'.
			  					'			</tr><tr>'.
			  					'				<td class="popoverFooter">adj_air_time</td><td class="popoverFooter">'. $adj_air_time .'</td>'.
			  					'			</tr><tr>'.
			  					'				<td class="popoverFooter">tf_org_air_date</td><td class="popoverFooter">'. $row[tf_org_air_date] .'</td>'.
			  					'			</tr><tr>'.
			  					'				<td class="popoverFooter">tf_air_date</td><td class="popoverFooter">'. $row[tf_air_date] .'</td>'.
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
				mysql_free_result($rProgramming);
#			}
			}
			// Finish table
			echo '</table>';

			mysql_free_result($dates_result);
			echo '<div style="font-size:8pt">* The double-D symbol is a registered trademark of Dolby Laboratories.</div>';
		}
	?>

	<div style="width:100%"><table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="ad-left">
			<?include(BASE_DIR .'/ads/skyscraper.php');?>
		</td><td style="vertical-align:top;">
			<h1><img src="/images/logos/<?=$network_lc?>_25.gif" alt="<?=$network?>" align="absmiddle"> <?=$network?> HDTV Stations &amp; Programming</h1>
			&raquo; <a href="<?=URL_PROG?>">Programming</a>
			&raquo; <a href="<?=URL_PROG_BROADCAST?>">Broadcast</a>
			&raquo; <?=$network?>
			<br />
			<br />
			<br />
