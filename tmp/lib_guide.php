<?
####################################################
#	getProgramBarIcons($row)
#		- Get's icon list to display on program bar
#	getProgramBarTitle($row, $colspan)
#		- Get's title for program bar
#	getProgramTooltip($row)
#		- Get the information to display in the mouseover tooltip
#	getProgramLegend()
#		- Get's the pegend of icons
#	display_grid($start_display_time, $tsSize, $user_time_offset, $guide_width = 6, $user_id = 0, $show_sd = true, $user_icons, $prog_highlight = '')
#		- Used on ???
#	display_grid_main($start_display_time, $tsSize, $user_time_offset, $user->data['guide_width'], $user_id, $show_sd, $user_icons)
#		- Used on guide.php
####################################################

	function getProgramBarIcons($row, $reverse) {
		global $user;

		$icons = $user->data['icons'];
		$suffix = ($reverse) ? '-r' : '';

		$bar_icons = '';
		$bar_icons .= (($row['audio_level'] == 'Dolby 5.1') && ($icons & ICON_SHOW_DD)) ? '&nbsp;<img src="'. BASE_IMG_HOST .'/images/dd'. $suffix .'.gif" alt="DD" class="dd" />' : '';
		$bar_icons .= (($row['is_close_captioned'] == 'Y') && ($icons & ICON_SHOW_CC)) ? '&nbsp;<img src="'. BASE_IMG_HOST .'/images/cc'. $suffix .'.gif" alt="CC" class="cc" />' : '';
		$bar_icons .= (($row['is_new'] == 'Y') && ($icons & ICON_SHOW_NEW)) ? '&nbsp;<img src="'. BASE_IMG_HOST .'/images/new'. $suffix .'.gif" alt="New" class="new" />' : '';
		$bar_icons .= (($row['is_letter_box'] == 'Y') && ($icons & ICON_SHOW_LB)) ? '&nbsp;<img src="'. BASE_IMG_HOST .'/images/lb'. $suffix .'.gif" alt="W/S" class="lb" />' : '';

		return $bar_icons;
	}

	function getProgramBarTitle($row, $colspan) {
		switch ($colspan) {
			case 1:
			case 2:
				$title = $row['title_8'];
				break;
			case 3:
				$title = $row['title_15'];
				break;
			case 4:
				$title = $row['title_30'];
				break;
			case 5:
			case 6:
			case 7:
			case 8:
			case 9:
			case 10:
				$title = $row['title_50'];
				break;
			default:
				$title = $row['title_128'];
		}

		return $title;
	}

	function getProgramTooltip($row) {
		$icons = ($row['audio_level'] == 'Dolby 5.1') ? '<img alt="Dolby Digital" src="'. BASE_IMG_HOST .'/images/logos/dolby-digital_20.png" align="right" />&nbsp;' : '';
		$audio_img = ($row['audio_level'] == 'Dolby 5.1') ? '<div class="pDD"></div>' : '';

		# Perhaps compute date on import?
		if ($row['event_date'] != '') {
			$program_date = '<b>Event Date: </b>'. date('n/j/Y', $row['event_date']) .'<br />';
		} elseif ($row['original_air_date'] != '') {
			$original_air_date = explode('-', $row['original_air_date']);
			$program_date = '<b>Air Date: </b>'. date('n/j/Y', strtotime($original_air_date[2] .'-'. $original_air_date[0] .'-'. $original_air_date[1])) .'<br />';
		} elseif ($row['release_year'] != '') {
			$program_date = '<b>Year: </b>'. $row['release_year'] .'<br />';
		} else {$program_date = '';}

		$duration = gmdate('G:i', $row['run_time']);

		$is_live = ($row['is_live'] == 'Y') ? '<div class="pLive">Live</div><br />' : '';
		$is_new = ($row['is_new'] == 'Y' && $row['show_type'] == 'SE') ? '<div class="pNew">New Episode</div><br />' : '';

		$hdtv_level = ($row['hdtv_level'] != '' && $row['hdtv_level'] != 'HD Level Unknown') ? '<b>HD Level:</b> '. $row['hdtv_level'] .'<br />' : '';
		$audio_level = ($row['audio_level'] != '') ? '<b>Audio Level:</b> '. $row['audio_level'] .'<br />' : '';

		$year = ($row['release_year'] != '') ? '<b>Year:</b> '. $row['release_year'] .'<br />' : '';

		$episode = ($row['episode_title'] != '') ? $row['episode_title'] : '';
		$episode .= ($row['episode_number'] != '') ? ' ('. $row['episode_number'] .')' : '';

		$rating_img = ($row['mpaa_rating'] != '' && $row['mpaa_rating'] != 'NR') ? '<img src="'. BASE_IMG_HOST .'/images/ratings/mpaa-'. strtolower($row['mpaa_rating']) .'_20.png" alt="MPAA '. $row['mpaa_rating'] .'" align="right" />' : '';
		$rating_img .= ($row['ustv_rating'] != '') ? '<img src="'. BASE_IMG_HOST .'/images/ratings/ustv-'. strtolower($row['ustv_rating']) .'_20.png" alt="USTV '. $row['ustv_rating'] .'" align="right" />' : '';
		if ($row['mpaa_rating'] != '' && $row['mpaa_rating'] != 'NR') {
			$rating_img = '<div class="rating-mpaa-'. $row['mpaa_rating'] .'"></div>';
		} elseif ($row['ustv_rating'] != '') {
			$rating_img = '<div class="rating-ustv-'. $row['ustv_rating'] .'"></div>';
		} else {
			$rating_img = '';
		}

		return '<div class="pInfo">'.
			$rating_img .
			'<div class="pEpisode">'. $episode .'</div>'.
			$row['description'] .' ('. $duration .')<br /><br />'.
			$is_live .
			$is_new .
			$audio_img .
			$program_date .
			$hdtv_level .
			$audio_level .
		'</div>';
	}

	function getProgramLegend() {
		return '<table class="bare" cellspacing="0" cellpadding="0"><tr><td nowrap>'.
			'<b>Icon Legend:</b>'.
			'<span class="legend"><img src="'. BASE_IMG_HOST .'/images/new.gif" alt="" class="new" /> New Show/Episode</span>&nbsp;'.
			'<span class="legend"><img src="'. BASE_IMG_HOST .'/images/lb.gif" alt="" class="lb" /> Widescreen</span>&nbsp;'.
			'<span class="legend"><img src="'. BASE_IMG_HOST .'/images/cc.gif" alt="" class="cc" /> Closed Captioned</span>&nbsp;'.
			'<span class="legend"><img src="'. BASE_IMG_HOST .'/images/dd.gif" alt="" class="dd" /> Dolby Digital</span>&nbsp;'.
		'</td></tr></table>';
	}

	/* Used in guide-program */
	function display_grid($start_display_time, $tsSize, $user_time_offset, $guide_width = 6, $user_id = 0, $show_sd = true, $user_icons, $prog_highlight = '') {
		global $admindata, $user;

		$round_unit = 15; // Round time to 15 minute chunks
		$height = 20; // Height of each row, used for accurately sizing scrollable div

		// The start... and end... display times are used for pulling data, while the adj_* times are used for display
		$end_display_time = $start_display_time + ($guide_width * $tsSize*MINUTES);
		$adj_start_display_time = $start_display_time + $user_time_offset;
		$adj_end_display_time = $end_display_time + $user_time_offset;

		// Initialize the Grid Table
		echo '<table class="grid" style="table-layout:fixed;" cellspacing="0" cellpadding="0">'.
		'<col width=175>';

		// Set col elements based on guide width
		for ($i=0; $i < ($guide_width*2); $i++) echo '<col width='. BLOCK_WIDTH .'>';

		echo '<tr><td class="stationLabel" height='. $height .'>&nbsp;</td>'."\n";

		// Print timeslot headers
		for ($x=$adj_start_display_time; $x<$adj_end_display_time; $x+=($tsSize*MINUTES)) {
			echo '<td class="timeLabel" colspan="2" height='. $height .'>'. gmdate("g:ia", $x) .'</td>'."\n";
		}
		echo "</tr>\n";

		// If the user is not logged in, and no URL parameters are given, then we display default station's, as defined by the administrator.
		$qry = "
		SELECT stat.tf_station_num station_num, stat.tf_station_call_sign call_sign, image_file, channel, j.label
		FROM join_user_station j, $admindata[tbl_stat] stat
		WHERE j.tf_station_num = stat.tf_station_num
			AND user_id = $user_id
		ORDER BY j.priority, channel*1, j.label ASC";
		$rStations = mQuery($qry);

		// If not results were returned, let the user know
		if (mysql_num_rows($rStations) == 0) {
			echo '<tr><td class="stationLabel">No <a href="profile-stations.php">Preferred Stations</a> Found</td>';
			for ($i=0; $i < ($guide_width*2); $i++) echo '<td class="blank" height='. $height .'>&nbsp;</td>';
			echo '</tr>';
		} else { // Loop through stations
			while ($station_row = mysql_fetch_assoc($rStations)) {
				$image_path = BASE_IMG_HOST .'/images/logos/'. str_replace('[size].', '15.', $station_row['image_file']);
				$image = '<img alt="" height="15" src="'. $image_path .'">';
				echo
					'<tr>'.
					'	<td class="stationLabel" height='. $height .'>'.
					'		<table class="bare" cellspacing=1 style="width:100%;table-layout:fixed;">'.
					'			<tr>'.
					'				<td class="stationImage">'. $image .'</td>'.
					'				<td><div class="grid"><a href="'. BASE_URL . '/programming/guide-station.php?id='. $station_row['station_num'] .'">'. $station_row['label'] .'</a></div></td>'.
					'				<td class="stationChannel">'. $station_row['channel'] .'</td>'.
					'			</tr>'.
					'		</table>'.
					"	</td>\n";

				// MAIN PROGRAM QUERY
				$show = ($show_sd) ? '' : " AND options & ". OPT_SKED_HDTV;
				$qry = "
				SELECT DISTINCT sked.*, prog.*
				FROM $admindata[tbl_prog] prog, $admindata[tbl_sked] sked
				WHERE
					sked.tf_database_key = prog.tf_database_key
					AND sked.tf_station_num = $station_row[station_num]
					AND (tf_air_time >= $start_display_time OR tf_air_time + (tf_duration*60) > $start_display_time)
					AND tf_air_time < $end_display_time
					$show
				ORDER BY tf_air_time ASC";
				$rProgramming = mQuery($qry);

				// If the result set is empty, fill with empty blocks
				if (mysql_num_rows($rProgramming) == 0) {
					for ($x=0; $x<$guide_width*2; $x++) echo '<td class="blank" height='. $height .'>&nbsp;</td>'."\n";
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

						// Front padding
						while ($round_start_time > $curTime) {
							echo '<td class="blank">&nbsp;</td>'."\n";
							$curTime += $round_unit*MINUTES;
						}

						// **** Print out Program bar ****
						if ($colspan > 0) {
							$icons = getProgramIcons($row);
							$bar_icons = getProgramBarIcons($row, ($row[options] & OPT_SKED_HDTV));
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

							$key = "$row[tf_database_key]:$station_row[station_num]:$row[tf_air_time]";
							if ($key == $prog_highlight) {
								$program_class = 'programHighlight';
							} elseif ($row[options] & OPT_SKED_HDTV) {
								$program_class = 'programHD';
							} else {
								$program_class = 'programSD';
							}
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
							echo '</table>\';'.
							'	</script>'.
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
			echo '</table>';
			mysql_free_result($rStations);
		}
		echo '<div style="font-size:8pt">* The double-D symbol is a registered trademark of Dolby Laboratories.</div>';
	} /*** END display_grid ***/

	function displayGridGuide($start_display_time, $tsSize, $user_time_offset, $guide_width = 8, $user_id = 0, $show_sd = true, $user_icons) {
		global $gmt_time, $admindata, $user, $debug;

		$round_unit = 15; // Round time to 15 minute chunks
		$guide_url = BASE_URL .'/programming/guide.php';
		$station_url = BASE_URL .'/programming/guide-station.php';
		$program_url = BASE_URL .'/programming/guide-program.php';

		// The start... and end... display times are used for pulling data, while the adj_* times are used for display
		$end_display_time = $start_display_time + ($guide_width * $tsSize*MINUTES);
		$adj_start_display_time = $start_display_time + $user_time_offset;
		$adj_end_display_time = $end_display_time + $user_time_offset;

		// Initialize the Grid Table
		echo '<table class="grid" cellspacing=0 cellpadding=0>'.
			'<col width=175>';

		// Set col elements based on guide width
		for ($i=0; $i < ($guide_width*2); $i++) echo '<col width='. BLOCK_WIDTH .'>';

		echo '	<tr height=22><td class="sLabel">';
		if ($user_id > 0) {
			echo '<table cellpadding="0" cellspacing="0" width="100%"><tr>'.
				'<td style="width:25px;text-align:center">'.
					'<a href="'. $guide_url .'?s='. ($gmt_time - 4*HOURS) .'"><img src="'. BASE_IMG_HOST .'/images/previous.gif" alt="-4" height="13" width"13" /></a>'.
				'</td><td>'.
					'&nbsp;'.
				'</td><td style="width:25px;text-align:center">'.
					'<a href="'. $guide_url .'?s='. ($gmt_time + 4*HOURS) .'"><img src="'. BASE_IMG_HOST .'/images/next.gif" alt="+4" height="13" width"13" /></a>'.
				'</td>'.
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
			$guide_user = $user->data['user_id'];
		} else {
			$guide_user = -1;
		}

		if ($debug) print_r($user->data);

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
			echo '<tr><td class="sLabel">No <a href="/profile-stations.php">Preferred Stations</a> Found</td>';
			for ($i=0; $i < ($guide_width*2); $i++) echo '<td class="blank">&nbsp;</td>';
			echo '</tr>';
		} else { // Loop through stations
			while ($row_source = mysql_fetch_assoc($res_source)) {
				echo '<tr>'.
					'<td class="sLabel">'.
						'<table class="bare" cellspacing=1 style="width:100%;table-layout:fixed;"><tr>'.
							'<td class="sImage"><img src="'. BASE_IMG_HOST .'/images/logos/'. str_replace('[size].', '15.', $row_source['img']) .'" alt=""></td>'.
							'<td><div class="grid">'.
								'<a href="'. $station_url .'?name='. dirify($row_source['label']) .'&id='. $row_source['source_id'] .'">'. $row_source['label'] .'</a>'.
							'</div></td>'.
							'<td class="sChannel">'. $row_source['channel'] .'</td>'.
						'</tr></table>'.
					"</td>\n";

				### MAIN PROGRAM QUERY ###
				$show = ($show_sd) ? '' : " AND is_hdtv = 'Y'";
				$sql = "
					SELECT *
					FROM prog_guide
					WHERE
						source_id = {$row_source['source_id']}
						AND (air_time >= $start_display_time OR air_time + (duration*60) > $start_display_time)
						AND air_time < $end_display_time
						$show
					ORDER BY air_time ASC";
				if ($debug) echo "$sql<br />";
				$res_guide = mQuery($sql);
				if ($debug) echo "Rows: ". mysql_num_rows($res_guide) ."<br />";

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

						// Check overlap times & determine the number of '<'s to prepend
						if ($round_start_time < $adj_start_display_time) {
							$col_start_time = $adj_start_display_time;
							$timeDiff = $adj_start_display_time - $round_start_time;
							for ($x=0; $x<ceil($timeDiff / ($round_unit*MINUTES)); $x+=2) $preTitle .= '&lt;';
						}

						// Check overlap times & determine the number of '>'s to append
						if ($round_end_time > $adj_end_display_time) {
							$col_end_time = $adj_end_display_time;
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
							$content = getProgramTooltip($row); # Compute info for tooltip

							# Compute info for program bar
							$bar_icons = getProgramBarIcons($row, ($row['is_hdtv'] == 'Y'));
							$title = getProgramBarTitle($row, $colspan);

							# Perhaps compute class on import?
							$program_class = ($row['is_hdtv'] == 'Y') ? 'pHD' : 'pSD';

							echo '<td colspan="'. $colspan .'" class="'. $program_class .'">'.
								'<div class="grid">'.
									'<div class="pPrototip" title="'. $row['title_128'] .'">'. $content .'</div>'.
									'<a href="'. $program_url .'?p='. $row['program_id'] .'&amp;s='. $row_source['source_id'] .'&amp;a='. $row['air_time'] .'">'.
										trim($preTitle .' '. $title .' '. $postTitle) .
									'</a>'. $bar_icons .
								'</div>'.
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
			echo '</table>';
			mysql_free_result($res_source);
		}
	} /*** END displayGridGuide ***/

	function displayStationGuide($station_num, $tsSize, $show_sd = true, $tzo = 0, $user_icons = 0) {
		global $admindata;

		$round_unit = 15; // Round time to 15 minute chunks
		$height = 20; // Height of each row, used for accurately sizing scrollable div

		echo getProgramLegend();

		// Initialize the Grid Table
		echo '<br /><table class="bare" style="table-layout:fixed;" cellspacing="0" cellpadding="0">'.
			'<col width=190>'.
			'<col width=800>'.
			'<tr><td style="vertical-align:top">'.
				'<table class="grid" style="width:100%" cellspacing="0" cellpadding="0">'.
					'<tr><td class="dateLabel" height='. $height .'>&nbsp;</td></tr>'."\n";

		$dates_result = mQuery("SELECT DISTINCT tf_air_date FROM tms_dgskedrec ORDER BY tf_air_date");
		while ($dates_row = mysql_fetch_assoc($dates_result)) { # Loop through dates
			echo '<tr><td class="dateLabel" height="'. $height .'" nowrap>'. gmdate('D, n/j', strtotime($dates_row[tf_air_date] .' 12:00am GMT')) .'</td></tr>';
		}

		$div_height = (($num_stations + 2) * $height);
		echo '<tr><td class="dateLabel" height="'. $height .'">&nbsp;</td></tr>'.
				'</table>'.
			'</td><td style="vertical-align:top">'.
				'<div style="overflow:auto;width:800px;height:320px;border-right:1px solid #'. BORDER_COLOR .'">'.
					'<table class="grid" style="width:100%" cellspacing=0 cellpadding=0>'."\n";
		for ($x=0; $x<96; $x++) echo '<col width='. BLOCK_WIDTH .' height='. $height .'>'."\n";
		echo '<tr>';

		// Print timeslot headers
		for ($x=0; $x<24*HOURS; $x+=($tsSize*MINUTES)) {
			echo '<td class="timeLabel" colspan="2" height="'. $height .'"><div title="'. $x .'">'. gmdate("g:ia", $x) .'</div></td>';
		}
		echo '</tr>';

		mysql_data_seek($dates_result, 0);
		while ($dates_row = mysql_fetch_assoc($dates_result)) {
			$start_display_time = strtotime($dates_row[tf_air_date] .' 12:00am GMT') - $user->data['time_zone_offset'];
			$end_display_time = $start_display_time + 24*HOURS;
			$adj_start_display_time = $start_display_time + $user->data['time_zone_offset'];
			$adj_end_display_time = $end_display_time + $user->data['time_zone_offset'];

			// MAIN PROGRAM QUERY
			$show = ($show_sd) ? '' : " AND options & ". OPT_SKED_HDTV;
			$qry = "
			SELECT sked.*, prog.*
			FROM $admindata[tbl_prog] prog, $admindata[tbl_sked] sked
			WHERE sked.tf_database_key = prog.tf_database_key
				AND sked.tf_station_num = $station_num
				AND (tf_air_time >= $start_display_time OR tf_air_time + (tf_duration*60) > $start_display_time)
				AND tf_air_time < $end_display_time
				$show
			ORDER BY tf_air_time ASC";
			$rProgramming = mQuery($qry);

			echo '<tr>';
			if (mysql_num_rows($rProgramming) == 0) { // If the result set is empty, fill with empty blocks
				for ($x=$start_display_time; $x<$end_display_time; $x+=$round_unit*MINUTES) echo '<td class="blank">&nbsp;</td>';
			} else { // Loop through entire day, and display programming
				$curTime = $adj_start_display_time;
				while ($row = mysql_fetch_assoc($rProgramming)) {
					$preTitle = '';
					$postTitle = '';
					$adj_air_time = $row[tf_air_time] + $user->data['time_zone_offset'];
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
						$bar_icons = getProgramBarIcons($row, ($row['options'] & OPT_SKED_HDTV), $user->data['time_zone_offset'], $user->data['icons']);
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
#			echo '<input type="hidden" name="query-'. $dates_row[tf_air_date] .'" value="'. $qry .'" DISABLED>';
			mysql_free_result($rProgramming);
		}
		// Finish table
		echo '</table></div>'.
		'	</td>'.
		'</tr></table>';

		mysql_free_result($dates_result);
		echo '<div style="font-size:8pt">* The double-D symbol is a registered trademark of Dolby Laboratories.</div>';
	}
?>