<?
	require('../global.php');

	$today = date('Y-m-d');
	$year = isset($_GET[year]) ? $_GET[year] : '';
	if (isset($_GET[month])) {
		$month =$_GET[month];
		$and_month = "AND DATE_FORMAT(entry_created_on, '%M') = '$month'";
	}
	if ($year == '') js_back();

	include(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<? require(BASE_DIR .'/includes/page_header.php');?>
	<title>HDTV Magazine - Article Archive: <?=$month?> <?=$year?></title>
</head>
<body><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>

	<h1>Article Archive: <?=$month?> <?=$year?></h1>
	<table class="bare" cellpadding="0" cellspacing="0" width="100%"><tr>
		<td id="left">
			<?
				$sql = "
				SELECT DISTINCT entry_blog_id, e.entry_id, entry_excerpt, entry_created_on, entry_title, author_name, topic_replies, aux.topic_id, az.MediumImageURL img
				FROM mt_entry e, mt_author a, aux_mt_entry aux
				LEFT JOIN ". TOPICS_TABLE ." t ON (aux.topic_id = t.topic_id)
				LEFT JOIN az_main az ON (aux.ASIN = az.ASIN)
				WHERE entry_author_id = author_id
					AND e.entry_id = aux.entry_id
					AND e.entry_status = 2
					AND e.entry_blog_id = 10
					AND YEAR(entry_created_on) = $year
					$and_month
				ORDER BY entry_created_on DESC";
				$result = mQuery($sql);

				$x = 0;
				while ($row = mysql_fetch_assoc($result)) {
					$x++;
					$ts = strtotime($row['entry_created_on']);
					$y = date('Y', $ts);
					$m = date('m', $ts);
					$entry = getEntryInfo($row['entry_blog_id']);

					$entry['date'] = getDateString($ts);
					$entry['link'] = "/$entry[blog_dir]/$y/$m/". dirify($row['entry_title']) .".php";
					$entry['title'] = $row['entry_title'];
					$entry['author'] = $row['author_name'];
					$entry['excerpt'] = $row['entry_excerpt'];
					$entry['topic_replies'] = $row['topic_replies'];
					$entry['topic_id'] = $row['topic_id'];
					$entry['image_src'] = $row['img'];

					$entry['catlinks'] = getCatLinksByEntryId($row['entry_id']);

					echo getFormattedEntry($entry, 'excerpt');
				}
			?>
		</td><td id="right">

			<div align="center" style="margin:5px 0;">
				<? include(BASE_DIR .'/ads/mrectangle.php');?>
			</div>
			<br />

			<div class="item"><span class="corners-top"><span></span></span>
				<h2>Article Archives</h2>
				<div style="float:right; width:50%">
					<b>Previous Years</b>
					<ul class="brownsquare" style="margin-top:0;"><?
						$qry = "
						SELECT YEAR(entry_created_on) y, COUNT(*) num
						FROM mt_entry
						WHERE entry_blog_id = 1
							AND entry_status = 2
							AND entry_created_on < '$today' - INTERVAL 1 YEAR
						GROUP BY y ORDER BY entry_created_on DESC";
						$result = mQuery($qry);
						while ($row = mysql_fetch_assoc($result)) {
							echo '<li><a href="archive.php?year='. $row[y] .'">'. $row[y] .'</a><span class="grey"> ('. $row[num] .')</span></li>';
						}
					?></ul>
				</div>
				<b>Last 12 Months</b><ul class="brownsquare" style="margin-top:0;"><?
					$qry = "
					SELECT YEAR(entry_created_on) y, DATE_FORMAT(entry_created_on, '%M') m, COUNT(*) num
					FROM mt_entry
					WHERE entry_blog_id = 1
						AND entry_status = 2
						AND entry_created_on > '$today' - INTERVAL 1 YEAR
					GROUP BY y,m ORDER BY entry_created_on DESC";
					$result = mQuery($qry);
					while ($row = mysql_fetch_assoc($result)) {
						echo '<li><a href="archive.php?month='. $row[m] .'&year='. $row[y] .'">'. $row[m] .'</a><span class="grey"> ('. $row[num] .')</span></li>';
					}
				?></ul>
			<span class="corners-bottom"><span></span></span></div>

			<div align="right">
			<? include(BASE_DIR .'/ads/skyscraper.php');?>
			</div>

		</td>
	</tr></table>

	<? include(BASE_DIR .'/includes/body_footer.php');?>
</div></body>
</html>
