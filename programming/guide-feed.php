<?
	require('../global.php');

	$debug = isset($_GET['debug']);
	if ($debug) {
		header('Content-type: text/plain');
	} else {
		header('Content-type: application/xml');
	}

	# Check for user_id first
	$user_id = isset($_GET['user_id']) ? mysql_real_escape_string($_GET['user_id']) : '';
	$dma_name = isset($_GET['dma_name']) ? mysql_real_escape_string($_GET['dma_name']) : '';
	if ($user_id != '') { # Get list of sources based on user_id
		$sql = "
		SELECT source.source_id, source.short_name name, channel, j.label, img, u.time_zone, time_zone_dst dst
		FROM prog_source source, j_user_source j, aux_prog_source a, user u
		WHERE source.source_id = j.source_id
			AND source.source_id = a.source_id
			AND j.user_id = u.bb_id
			AND j.user_id = '$user_id'
			AND u.access & ". ACCESS_PREMIUM ."
		ORDER BY j.sort, channel*1, j.label ASC";
		if ($debug) {echo "$sql\n";}
		$res_source = mQuery($sql);

		# Get first row so we can grab start time based on user timezone
		$row_source = mysql_fetch_assoc($res_source);
#		print_r($row_source);
		$time_offset = $row_source['time_zone'] - ($row_source['dst']*HOURS*date('I'));
#		$start_display_time = strtotime('Tuesday 8:00pm GMT') + $user_time_offset;
		$gmt_time = strtotime('Tomorrow 1am GMT');
		$start_display_time = $gmt_time + $time_offset;
		$start_display_time = $gmt_time;
	} elseif ($dma_name != '') { # Get list of sources based on dma_name
		$sql = "SELECT source_id, full_name label, call_letters, affiliation_1, virtual_channel_number channel, time_zone, dst
			FROM prog_source source
			WHERE digital_source='Y'
				AND dma_name = '$dma_name'
			ORDER BY virtual_channel_number*1, full_name ASC";
		if ($debug) {echo "$sql\n";}
		$res_source = mQuery($sql);
	} else exit();

	$today = gmdate('m/d/Y', $start_display_time);

#	$date = gmdate('D, d M Y');
	$guide_url = BASE_URL .'/programming/guide.php';
	$station_url = BASE_URL .'/programming/guide-station.php';
	$program_url = BASE_URL .'/programming/guide-program.php';

	echo '<?xml version="1.0" encoding="utf-8"?>'."\n";
?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
	<channel>
		<atom:link href="<?=(BASE_URL . $_SERVER['PHP_SELF'])?>" rel="self" type="application/rss+xml" />
		<title>HDTV Magazine - Daily HDTV Program Feed</title>
		<link><?=$program_url?>/</link>
		<description>This feed contains daily prime-time programming for all the major networks.</description>
		<language>en</language>
		<copyright>Copyright <?=date('Y')?></copyright>
		<lastBuildDate><?=gmdate('r', $start_display_time)?></lastBuildDate>
		<generator>HDTV Magazine</generator>
		<managingEditor>feedback@hdtvmagazine.com (HDTV Magazine Feedback)</managingEditor>
		<webMaster>feedback@hdtvmagazine.com (HDTV Magazine Feedback)</webMaster>
		<docs>http://blogs.law.harvard.edu/tech/rss</docs>
		<?
				$link = $guide_url .'?primetime&d='. $today;

				echo '<item>'."\n".
#					'<title>HDTV Prime Time Programming for '. $row_date['air_date'] .'</title>'."\n".
					'<title>HDTV Prime Time Programming for '. $today .'</title>'."\n".
					"<description>\n";
				while ($row_source = mysql_fetch_assoc($res_source)) {

					$content = '<img src="'. BASE_IMG_HOST .'/images/logos/'. str_replace('[size].', '15.', $row_source['img']) .'" alt="'. $row_source['label'] .'" />'."\n".
						'<a href="'. $station_url .'?id='. $row_source['source_id'] .'">'. $row_source['label'] .'</a><br />'."\n";
					echo htmlspecialchars($content, ENT_COMPAT, 'UTF-8');

#					$start_display_time = strtotime($row_date['air_date'] .' 1:00am GMT') - (1*HOURS*date('I'));
#					$start_display_time = strtotime($today .' 1:00am GMT') + $user_time_offset;
					$end_display_time = $start_display_time + 3*HOURS;
					$sql = "
						SELECT *
						FROM prog_guide
						WHERE
							source_id = {$row_source['source_id']}
							AND (air_time >= $start_display_time OR air_time + (duration*60) > $start_display_time)
							AND air_time < $end_display_time
							AND is_hdtv = 'Y'
						ORDER BY air_time ASC";
					if ($debug) {echo "$sql\n";}
					$program_result = mQuery ($sql);

					if (mysql_num_rows($program_result) > 0) {
						while ($program = mysql_fetch_assoc($program_result)) {
							$properties = array();
							$episode = ($program['episode_title'] != '') ? $program['episode_title'] : '';
							$episode .= ($program['episode_number'] != '') ? ' (#'. $program['episode_number'] .')' : '';

							if ($program['is_new'] == 'Y' && $program['show_type'] == 'SE') $properties[] = '<span style="color:red; font-weight:bold">New!</span>';
							if ($program['hdtv_level'] != '' && $program['hdtv_level'] != 'HD Level Unknown') $properties[] = $program['hdtv_level'];
							if ($program['audio_level'] != '') $properties[] = $program['audio_level'];
							$properties_text = (count($properties) > 0) ? ' ('. implode(', ', $properties) .')' : '';

							$year = ($program['release_year'] != '') ? '<b>Year:</b> '. $program['release_year'] .'<br />' : '';

							$content = gmdate("g:ia", $program['air_time'] + $time_offset) .' - '. gmdate("g:ia", $program['air_time'] + $time_offset + ($program['duration']*MINUTES)) .': '."\n".
								'<a href="'. $program_url .'?p='. $program['program_id'] .'&amp;s='. $row_source['source_id'] .'&amp;a='. $program['air_time'] .'">'.
								$program['title_128'] ."</a> - <i>$episode</i>$properties_text<br />\n";
							echo htmlspecialchars($content, ENT_COMPAT, 'UTF-8');
						}
					} else {
						$content = '<b>No HD Programming Tonight</b><br /><br />';
						echo htmlspecialchars($content, ENT_COMPAT, 'UTF-8');
					}
					$content = "<br />\n";
					echo htmlspecialchars($content, ENT_COMPAT, 'UTF-8');
				}
#				echo '<item>'."\n".
#					'<title>HDTV Prime Time Programming for '. $row_date['air_date'] .'</title>'."\n".
#					"<description>\n".
#						htmlspecialchars($content, ENT_COMPAT, 'UTF-8') ."\n".
					echo "</description>\n".
					'<link>'. htmlspecialchars($link, ENT_COMPAT, 'UTF-8') .'</link>'."\n".
					'<guid>'. htmlspecialchars($link, ENT_COMPAT, 'UTF-8') .'</guid>'."\n".
#					'<pubDate>'. gmdate('r', strtotime($row_date['air_date'])) .'</pubDate>'."\n".
					'<pubDate>'. gmdate('r', strtotime($today)) .'</pubDate>'."\n".
					'<source url="'. $guide_url .'">HDTV Magazine Program Guide</source>'."\n".
				'</item>'."\n\n";
#			}
		?>
	</channel>
</rss>