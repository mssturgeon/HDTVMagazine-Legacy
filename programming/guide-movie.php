<?
	header('Cache-Control: public');
	require('../global.php');
	require(BASE_DIR .'/includes/lib_guide.php');

	if (!access(ACCESS_PREMIUM)) prompt_login(PHP_SELF);

	$sort_order = 'air_time';
	$show_type_names['MO'] = 'Movie';
	$show_type_names['SE'] = 'Series Episode';
	$show_type_names['SM'] = 'Series Master';
	$show_type_names['OT'] = 'Other';
	$start = isset($_GET['start']) ? $_GET['start'] : 0;
	$count = isset($_GET['count']) ? $_GET['count'] : 50;

	$action = isset($_GET['action']) ? $_GET['action'] : '';
	if ($action == 'submit' | $action == 'csv') {
		// Set selected search criteria
		$stations = implode(',', $_GET['source_id']);
		$show_types = "'". implode('\',\'', $_GET['show_types']) ."'";
		$sort_order = $_GET['sort_order'];
		# Since show_rating only applies to 'MO' types, we have to make an exception for other show types
		$where_star_rating = ($_GET['star_rating'] == 0) ? '' : "AND ((show_type = 'MO' AND star_rating >= $_GET[star_rating]) OR (show_type <> 'MO'))";

#		$unique_title = strpos($sort_order, 'title_128') !== false;

		// Temporarily change the sort order
#		if ($sort_order == 'title_128, air_time') $sort_order = 'new_title, air_time';

		// Get all programming on preferred stations
		$sql = "
		SELECT *
		FROM prog_guide guide, j_user_source j
		WHERE
			guide.source_id = j.source_id
			AND user_id = ". $user->data['user_id'] ."
			AND j.source_id IN ($stations)
			AND guide.show_type IN ($show_types)
			$where_star_rating
		ORDER BY $sort_order";
		$total_result = mQuery($sql);
		$total = mysql_num_rows($total_result);

#		$sql .= " LIMIT $start,$count";
		if ($action != 'csv') $sql .= " LIMIT $start,$count";

		$result = mQuery($sql);
#		echo '<input type="hidden" value="'. $qry .'" />';

		// If sorting on title, we need to change the sort order back so the field defaults properly.
		if ($sort_order == 'new_title, air_time') $sort_order = 'title_alpha, air_time';

		// Count Airtimes
		$air_times = array();
		while ($row = mysql_fetch_assoc($result)) {
			$key = $row['program_id'];
			if (array_key_exists($key, $air_times)) {
				$air_times[$key] .= ','. $row['air_time'];
			} else {
				$air_times[$key] = $row['air_time'];
			}
		}
		if ($total > 0) mysql_data_seek($result, 0);

#		echo $_SERVER['QUERY_STRING'] ."<br />";
		foreach (split('&', $_SERVER['QUERY_STRING']) as $nv) {
			$qsName = split('=', $nv);
			if ($qsName[0] != 'start' && $qsName[0] != 'count') {
				$qs[] = $nv;
			}
		}
#		echo implode('&', $qs);
		$query_string = implode('&', $qs);
		$y = 1;
#		if (($start-$count) >=0) $page_links .= '<a href="?start='. ($start-$count) .'&count='. $count .'&'. $_SERVER['QUERY_STRING'] .'">&lt; Prev</a>&nbsp;&nbsp;&nbsp;';
		if (($start-$count) >=0) $page_links .= '<a href="?start='. ($start-$count) .'&count='. $count .'&'. $query_string .'">&lt; Prev</a>&nbsp;&nbsp;&nbsp;';
		for ($x=0; $x<$total; $x+=$count) {
			if ($x == $start) {
				$page_links .= $y++ .'&nbsp';
			} else {
				$page_links .= '<a href="?start='. $x .'&count='. $count .'&'. $_SERVER['QUERY_STRING'] .'">'. $y++ .'</a>&nbsp;';
			}
		}
		if (($start+$count) < $total) $page_links .= '&nbsp;&nbsp;<a href="?start='. ($start+$count) .'&count='. $count .'&'. $query_string .'">Next &gt;</a>';
	}

	if ($action == 'csv') {
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename=movies.csv');

		echo "Date,Time,Channel,Title,Episode Title,Year,Duration,Run-time,Star Ratings,Future Showings\n";

		$used_keys = array();
		while ($row = mysql_fetch_assoc($result)) {
			if (in_array($row['program_id'], $used_keys) === false) {
				$adj_air_time = $row['air_time'] + $user->data['time_zone_offset'];

				$duration = gmdate('G:i', $row['run_time']);

				$episode = ($row['episode_title'] != '') ? $row['episode_title'] : '';
				$episode .= ($row['episode_number'] != '') ? ' ('. $row['episode_number'] .')' : '';

				// Compile Additional air times
				$raw_array = explode(',', $air_times[$row['program_id']]);
				sort($raw_array);
				$x = 0;
				$add_showings = array();
				foreach ($raw_array as $show_time) {
					if ($show_time > $row['air_time']) {
						$add_showings[$x++] = gmdate('m/d g:ia', $show_time + $user->data['time_zone_offset']);
					}
				}
			}
			$showings = $x > 0 ? implode(', ', $add_showings) : '';
			echo '"'. gmdate('D, M jS', $adj_air_time) .'",'. gmdate('g:ia', $adj_air_time) .','. $row['channel'] .' '. $row['label'] .',"'. $row['title_128'] .'","'. $episode .'",'. $row['release_year'] .', '. $duration .', '. $runtime .', "'. $row['star_rating'] .'","'. $showings ."\"\n";
		}
		if ($unique_title) $used_keys[$x++] = $row['program_id'];
		exit;
	}

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - Movie Guide</title>
	<?
#		require(BASE_DIR .'/includes/common_header.php');
	?>
	<meta name="description" content="HDTV Magazine Movie Programming Guide">
	<meta name="keywords" content="hdtv,hdtv programming,hdtv guide,hdtv schedule,hdtv listing,hdtv guides,hdtv schedules,hdtv listings,hdtv programs,hdtv programming guide,hdtv programming schedule,hdtv program,hd guide,hd programming,hd schedule,abc hdtv programming,cbs hdtv programming,digital tv programming,digital tv schedule,digital tv schedules,dtv listings,dtv program,dtv programing,dtv programming,dtv schedule,hd programing,hd programming,hd programs,high definition guide,high definition programing,high definition programming,high definition programs,high definition schedule,nbc hdtv programming,guide,news,sports,hdtv stations">

	<link rel="stylesheet" type="text/css" href="<?=BASE_IMG_HOST?>/min/?b=css&f=guide.css,prototip.css">
	<? require(BASE_DIR .'/includes/page_header.php');?>
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/prototype/1.6.1.0/prototype.js"></script>
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/scriptaculous/1.8.3/scriptaculous.js"></script>
	<script type="text/javascript" src="<?=BASE_IMG_HOST?>/min/?f=js/prototip/prototip.js"></script>

	<!--link rel="stylesheet" type="text/css" href="/stylesheets/guide_css.php">
	<link rel="stylesheet" type="text/css" href="/stylesheets/prototip.css" />
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/prototype/1.6/prototype.js"></script>
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/scriptaculous/1.8/scriptaculous.js"></script>
	<script type="text/javascript" src="/scripts/prototip/prototip.js"></script-->
	<script language="javascript" type="text/javascript">
		// Bind tooltips
		document.observe('dom:loaded', function() {
			$$('div.pPrototip').each(function(element) {
				new Tip(element.parentNode, element.innerHTML, {
					style:'hdtvmagazine',
					title:element.title
				});
			});
		});

		function Search() {
			if (validate()) {
				document.forms['frm'].action.value = 'submit';
				document.forms['frm'].submit();
			}
		}

		function downloadCSV() {
			document.forms['frm'].action.value = 'csv';
			document.forms['frm'].submit();
		}

		function validate() {
			obj = document.getElementsByName('source_id[]');
			for (x=0; x<obj.length; x++) {
				if (obj[x].checked) {
					return true;
				}
			}
			alert('You must select at least one channel or station to search.');
	  		return false;
		}
	</script>
</head>
<body><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<h1>HDTV Movie Guide</h1>

	<div align="center">
		Use the following form to search programming within your preferred stations.  All times displayed are relative to the time zone selected in your profile.
	</div><br />

	<form name="frm" method="get" action="<?=PHP_SELF?>" onsubmit="return validate()">
		<input type="hidden" name="action" value="submit">
		<table class="type1b" cellspacing="0" align="center" style="width:500px">
			<tr><td class="type1b_header" colspan="2">Select your search criteria</td>
			</tr><tr>
				<td class="inputLabel" valign="top" nowrap>Include Types:</td>
				<td>
					<input type="checkbox" name="show_types[]" value="MO" <?strpos($show_types, 'MO') !== false ? print "CHECKED" : print ""?>>Movie
					<input type="checkbox" name="show_types[]" value="SE" <?strpos($show_types, 'SE') !== false ? print "CHECKED" : print ""?>>Series Episode
					<input type="checkbox" name="show_types[]" value="SM" <?strpos($show_types, 'SM') !== false ? print "CHECKED" : print ""?>>Series Masters
					<input type="checkbox" name="show_types[]" value="OT" <?strpos($show_types, 'OT') !== false ? print "CHECKED" : print ""?>>Other
				</td>
			</tr><tr>
				<td class="inputLabel" valign="top" nowrap>Rating (at least):</td>
				<td><select name="star_rating">
					<option value="0" <? $_GET['star_rating'] == 0 ? print 'SELECTED' : print ''?>>All
					<option value="1" <? $_GET['star_rating'] == 1 ? print 'SELECTED' : print ''?>>1 star
					<option value="2" <? $_GET['star_rating'] == 2 ? print 'SELECTED' : print ''?>>2 star
					<option value="3" <? $_GET['star_rating'] == 3 ? print 'SELECTED' : print ''?>>3 star
					<option value="4" <? $_GET['star_rating'] == 4 ? print 'SELECTED' : print ''?>>4 star
				</select></td>
			</tr><tr>
				<td class="inputLabel" valign="top" nowrap>Include Channels:</td>
				<td><?=getCRBox("SELECT CONCAT(channel, ' ', label), source_id FROM j_user_source WHERE user_id = ". $user->data['user_id'] ." ORDER BY sort, channel*1, label", "checkbox", "source_id[]", $stations, 3, "")?></td>
			</tr><tr>
				<td class="inputLabel" valign="top" nowrap>Sort by:</td>
				<td>
					<input type="radio" name="sort_order" value="air_time" <?$sort_order == 'air_time' ? print "CHECKED" : print ""?>>Air Time
					<input type="radio" name="sort_order" value="title_alpha, air_time" <?$sort_order == 'title_alpha, air_time' ? print "CHECKED" : print ""?>>Title
					<input type="radio" name="sort_order" value="star_rating DESC, air_time" <?$sort_order == 'star_rating DESC, air_time' ? print "CHECKED" : print ""?>>Rating
				</td>
			</tr><tr>
				<td class="buttonBar" colspan="2">
					<input type="button" name="btnSearch" value="Search" class="inputButton" onClick="Search()">
					<input type="button" name="btnDownload" value="Download (CSV)" class="inputButton" onClick="downloadCSV()">
				</td>
			</tr>
		</table>
	</form>
	<? if ($action == 'submit') {?>
		<br />
		<div class="item"><span class="corners-top"><span></span></span>
			<span class="label" style="font-size:12pt">Search Results: <b><?=$start+1?> - <?=min($start+$count, $total)?></b> of <b><?=$total?></b></span>
			<span style="float:right; margin-top:3px">
				Page: <?=$page_links?>
			</span>
		<span class="corners-bottom"><span></span></span></div>

		<table class="type1b" align="center">
			<tr>
				<td class="type1b_header" nowrap="nowrap">Air Time</td>
				<td class="type1b_header">Channel</td>
				<td class="type1b_header">Type</td>
				<td class="type1b_header">Title</td>
				<td class="type1b_header" nowrap="nowrap">Episode Title</td>
				<td class="type1b_header">Year</td>
				<td class="type1b_header">Duration</td>
				<td class="type1b_header">Rating</td>
				<td class="type1b_header" nowrap="nowrap">Future Showings</td>
			</tr>
			<?
				$used_keys = array();
				while ($row = mysql_fetch_assoc($result)) {
					if (in_array($row['program_id'], $used_keys) === false) {
						# Compute info for tooltip
						$content = getProgramTooltip($row);

						$duration = gmdate('G:i', $row['run_time']);

						$adj_air_time = $row['air_time'] + $user->data['time_zone_offset'];

						$episode = ($row['episode_title'] != '') ? $row['episode_title'] : '';
						$episode .= ($row['episode_number'] != '') ? ' ('. $row['episode_number'] .')' : '';

						// Compile Additional air times
						$raw_array = explode(',', $air_times[$row['program_id']]);
						sort($raw_array);
						$x = 0;
						$add_showings = array();
						foreach ($raw_array as $show_time) {
							if ($show_time > $row['air_time']) {
								$add_showings[$x++] = gmdate('m/d g:ia', $show_time + $user->data['time_zone_offset']);
							}
						}
						$showings = $x > 0 ? implode(', ', $add_showings) : '&lt;None&gt;';

						echo '<tr onMouseOver="hover_over(this)" onMouseOut="hover_out(this)">'.
							'<td class="type1b">'. gmdate('m/d g:ia', $adj_air_time) .'</td>'.
							'<td class="type1b">'. $row['channel'] .' '. $row['label'] .'</td>'.
							'<td class="type1b">'. $show_type_names[$row['show_type']] .'</td>'.
							'<td class="type1b">'.
								'<div id="p_'. $row['program_id'] .'" class="grid">'.
									'<div class="pPrototip" title="'. $row['title_128'] .'">'. $content .'</div>'.
									'<a href="'. BASE_URL .'/programming/guide-program.php?p='. $row['program_id'] .'&amp;s='. $row['source_id'] .'&amp;a='. $row['air_time'] .'">'. $row['title_128'] .'</a>'.
								'</div>'.
							'</td>'.
							'<td class="type1b">'. $episode .'</td>'.
							'<td class="type1b">'. $row['release_year'] .'</td>'.
							'<td class="type1b">'. $duration .'</td>'.
							'<td class="type1b">'. $row['star_rating'] .'</td>'.
							'<td class="type1b">'. $showings .'</td>';
						'</tr>';
					}
					if ($unique_title) {
						$used_keys[$x++] = $row['program_id'];
					}
				}
			?>
		</table>
	<?}?>

	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
</div></body>
</html>
