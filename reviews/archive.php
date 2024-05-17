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
	<title>HDTV Magazine - Review Archives: <?=$month?> <?=$year?></title>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header-4.php');
	?>

	<h1>Review Archives: <?=$month?> <?=$year?></h1>
	<table class="bare" cellpadding="0" cellspacing="0" width="100%"><tr>
		<td id="left">
			<?
				if ($user->data['subscriptions'] & SUB_REVIEWS) {} else {
					if ($user->data['is_registered']) {?>
						<div class="important"><span class="corners-top"><span></span></span>
							<span class="label">Receive instant notification of new reviews:</span>
							<a href="<?=URL_PROFILE_SUBSCRIPTIONS?>">Modify your subscription profile</a> to receive notification of new
							HDTV Magazine Reviews via email as soon as they are published.
						<span class="corners-bottom"><span></span></span></div>
					<?} else {?>
						<div class="important"><span class="corners-top"><span></span></span>
							<span class="label">Receive instant notification of new reviews:</span>
							<a href="<?=URL_PROFILE_CREATE?>">Register Now</a> to receive notification of new
							HDTV Magazine Reviews via email as soon as they are published.
						<span class="corners-bottom"><span></span></span></div>
					<? }
				}
				$sql = "
				SELECT DISTINCT entry_blog_id, e.entry_id, entry_excerpt, entry_created_on, entry_title, author_name, topic_replies, aux.topic_id, az.MediumImageURL img
				FROM mt_entry e, mt_author a, aux_mt_entry aux
				LEFT JOIN ". TOPICS_TABLE ." t ON (aux.topic_id = t.topic_id)
				LEFT JOIN az_main az ON (aux.ASIN = az.ASIN)
				WHERE entry_author_id = author_id
					AND e.entry_id = aux.entry_id
					AND e.entry_status = 2
					AND e.entry_blog_id = 8
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
				<h2>Review Archives</h2>
				<div style="float:right; width:50%">
					<b>Previous Years</b>
					<ul class="brownsquare" style="margin-top:0;"><?
						$qry = "
						SELECT YEAR(entry_created_on) y, COUNT(*) num
						FROM mt_entry
						WHERE entry_blog_id = 8
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
					WHERE entry_blog_id = 8
						AND entry_status = 2
						AND entry_created_on > '$today' - INTERVAL 1 YEAR
					GROUP BY y,m ORDER BY entry_created_on DESC";
					$result = mQuery($qry);
					while ($row = mysql_fetch_assoc($result)) {
						echo '<li><a href="archive.php?month='. $row[m] .'&year='. $row[y] .'">'. $row[m] .'</a><span class="grey"> ('. $row[num] .')</span></li>';
					}
				?></ul>
			<span class="corners-bottom"><span></span></span></div>

			<div class="item"><span class="corners-top"><span></span></span>
				<h2>RSS Feeds</h2>
				<ul class="nosquare">
					<li><a target="_blank" href="http://www.bloglines.com/sub/http://feeds.hdtvmagazine.com/hdtv-reviews"><img src="/images/chicklet-bloglines.gif" border="0" alt="Subscribe with Bloglines" /></a></li>
					<li><a target="_blank" href="http://feeds.hdtvmagazine.com/hdtv-reviews"><img src="/images/chicklet-rss.gif" alt="RSS 2.0"></a></li>
					<li><a target="_blank" href="http://my.msn.com/addtomymsn.armx?id=rss&ut=http://feeds.hdtvmagazine.com/hdtv-reviews&ru=<?=FULL_URL_REVIEWS?>"><img src="/images/chicklet-msn.gif" alt="Add to MyMSN"></a></li>
					<li><a target="_blank" href="http://add.my.yahoo.com/content?url=http://feeds.hdtvmagazine.com/hdtv-reviews"><img src="/images/chicklet-yahoo.gif" alt="Add to My Yahoo!"></a></li>
					<li><a target="_blank" href="http://www.newsgator.com/ngs/subscriber/subext.aspx?url=http://feeds.hdtvmagazine.com/hdtv-reviews"><img src="/images/chicklet-newsgator.gif" alt="Subscribe in NewsGator Online"></a></li>
					<li><a target="_blank" href="http://toolbar.google.com/buttons/add?url=<?=FULL_URL_REVIEWS_DIR?>/toolbar_button.xml">Add to Google Toolbar</a></li>
				</ul>
			<span class="corners-bottom"><span></span></span></div>

			<div align="right">
			<? include(BASE_DIR .'/ads/skyscraper.php');?>
			</div>

		</td>
	</tr></table>

	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
</div></body>
</html>
