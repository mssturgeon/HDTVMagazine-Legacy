<?
	require('../global.php');

	$today = date('Y-m-d');
	$year = isset($_GET[year]) ? $_GET[year] : '';
	if (isset($_GET[month])) {
		$month =$_GET[month];
		$and_month = "AND DATE_FORMAT(entry_created_on, '%M') = '$month'";
	}
	if ($year == '') js_back();
	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<? require(BASE_DIR .'/includes/page_header.php');?>
	<title>HDTV Magazine - Bulletin Archive: <?=$month?> <?=$year?></title>
</head>
<body><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>

	<h1>Bulletin Archive: <?=$month?> <?=$year?></h1>
	<table class="bare" cellpadding="0" cellspacing="0" width="100%"><tr>
		<td id="left">
			<?
				$sql = "
				SELECT DISTINCT entry_blog_id, e.entry_id, entry_excerpt, entry_created_on, entry_title, author_name, topic_replies, aux.topic_id
				FROM mt_entry e, mt_author a, mt_placement p, mt_category c, aux_mt_entry aux, ". TOPICS_TABLE ." t
				WHERE entry_author_id = author_id
					AND e.entry_id = p.placement_entry_id
					AND e.entry_id = aux.entry_id
					AND aux.topic_id = t.topic_id
					AND p.placement_category_id = c.category_id
					AND e.entry_status = 2
					AND e.entry_blog_id = 7
					AND YEAR(entry_created_on) = $year
					$and_month
				ORDER BY entry_created_on";
				$result = mQuery($sql);

				while ($row = mysql_fetch_assoc($result)) {
					$ts = strtotime($row[entry_created_on]);
					$y = date('Y', $ts);
					$m = date('m', $ts);
					$ts_offset = $ts + $user->data['time_zone_offset'];
					$blog_dir = getBlogDir($row[entry_blog_id]);

					$entry[date] = getDateString($ts);
					$entry[link] = "/$blog_dir/$y/$m/". dirify($row[entry_title]) .".php";
					$entry[title] = $row[entry_title];
					$entry[author] = $row[author_name];
					if (strlen($row[entry_excerpt]) > 600) $row[entry_excerpt] = trim(substr($row[entry_excerpt], 0, 600) .' ...');
					$entry[excerpt] = $row[entry_excerpt];
					$entry[entry_type] = ($row[entry_blog_id] == 1) ? 'Article' : 'Review';
					$entry[topic_replies] = $row[topic_replies];
					$entry[topic_id] = $row[topic_id];

					# Get categories
					$sql = "
					SELECT category_label FROM mt_category c, mt_placement p
					WHERE $row[entry_id] = p.placement_entry_id
						AND c.category_id = p.placement_category_id";
					$res_categories = mQuery($sql);
					$entry[catlinks] = array();
					while ($row_category = mysql_fetch_assoc($res_categories)) {
#						$catlinks[] = '<a href="?category='. urlencode(stripslashes($row_category[category_label])) .'">'. stripslashes($row_category[category_label]) .'</a>';
						$entry[catlinks][] = stripslashes($row_category[category_label]);
					}

					echo getFormattedEntry($entry, 'title-only');
				}
			?>
		</td><td id="right">

			<div align="center" style="margin:5px 0;">
				<? include(BASE_DIR .'/ads/mrectangle.php');?>
			</div>
			<br />

			<div class="item"><span class="corners-top"><span></span></span>
				<h2>Bulletin Archives</h2>
				<div style="float:right; width:50%">
					<b>Previous Years</b>
					<ul class="brownsquare" style="margin-top:0;"><?
						$qry = "
						SELECT YEAR(entry_created_on) y, COUNT(*) num
						FROM mt_entry
						WHERE entry_blog_id = 7
							AND entry_status = 2
							AND entry_created_on < '$today' - INTERVAL 1 YEAR
						GROUP BY y ORDER BY entry_created_on DESC";
						$result = mQuery($qry);
						while ($row = mysql_fetch_assoc($result)) {
							echo '<li><a href="bulletins-archive.php?year='. $row[y] .'">'. $row[y] .'</a><span class="grey"> ('. $row[num] .')</span></li>';
						}
					?></ul>
				</div>
				<b>Last 12 Months</b><ul class="brownsquare" style="margin-top:0;"><?
					$qry = "
					SELECT YEAR(entry_created_on) y, DATE_FORMAT(entry_created_on, '%M') m, COUNT(*) num
					FROM mt_entry
					WHERE entry_blog_id = 7
						AND entry_status = 2
						AND entry_created_on > '$today' - INTERVAL 1 YEAR
					GROUP BY y,m ORDER BY entry_created_on DESC";
					$result = mQuery($qry);
					while ($row = mysql_fetch_assoc($result)) {
						echo '<li><a href="bulletins-archive.php?month='. $row[m] .'&year='. $row[y] .'">'. $row[m] .'</a><span class="grey"> ('. $row[num] .')</span></li>';
					}
				?></ul>
			<span class="corners-bottom"><span></span></span></div>

			<div align="right">
			<? include(BASE_DIR .'/ads/skyscraper.php');?>
			</div>

		</td>
	</tr></table>

	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
</div></body>
</html>
