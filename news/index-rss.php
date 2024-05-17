<?
	header('Cache-Control: no-store');
	require('../global.php');
	$today = date('Y-m-d');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>HDTV Magazine - HDTV News</title>
	<meta name="description" content="HDTV headlines and the stories behind those headlines.">
	<meta name="keywords" content="hdtv,news,archive,hdtv news,hdtv information,news archive">
	<meta name="rating" content="general">
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/hdtv-news" />
	<?require(BASE_DIR .'/includes/common_header.php');?>
</head>
<body>
	<?
		include(BASE_DIR .'/ads/leaderboard.php');
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/includes/lib_news.php');
	?>

	<div id="body_container">
		<h1>HDTV News</h1>
		<form name="frm" method="get" action="/news/category.php">
		<table class="bare" cellpadding="0" cellspacing="0"><tr>
			<td id="left">
				<div class="anchors">
					Related sections:
					<a href="#bulletins">HDTV Bulletins</a> &bull;
					<a href="#archives">News Archives</a>
				</div>
				<?
					$sql = "
					SELECT DISTINCT id, pubDate, link, title, description, source, r.category_id, category_label, r.topic_id, topic_replies
					FROM mt_category c, hdtv_rss r
					LEFT JOIN ". TOPICS_TABLE ." t ON r.topic_id = t.topic_id
					WHERE rank > 0
						AND r.category_id = c.category_id
					ORDER BY pubDate DESC LIMIT 20";
					$result = $db->sql_query($sql);

					$x = 0;
					$format = 'excerpt';
					while ($row = mysql_fetch_assoc($result)) {
						$x++;
						$entry[id] = $row[id];
						$entry[date] = getDateString($row[pubDate]);
						$entry[link] = 'story.php?title='. dirify($row[title]) .'&amp;id='. $row[id];
						$entry[title] = $row[title];
						$entry[author] = $row[source];
						$entry[excerpt] = strip_tags($row[description]);
#						$entry[entry_type] = 'Story';
						$entry[topic_replies] = $row[topic_replies];
						$entry[topic_id] = $row[topic_id];

						$entry[catlinks] = array();
						$entry[catlinks][] = '<a href="category.php?id='. $row[category_id] .'&category='. urlencode(stripslashes($row[category_label])) .'">'. stripslashes($row[category_label]) .'</a>';

						if ($x == 9) {
#							echo '<h2>Not-so-News ...</h2>';
							$format = 'title-only';
						}

						echo getFormattedStory($entry, $format);
					}
				?>
			</td><td id="right">
		  		<?include(BASE_DIR .'/ads/mrectangle.php');?><br />
				<a name="bulletins"></a>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2><a href="bulletins.php">Recent HDTV Bulletins ...</a></h2>
					<ul class="brownsquare"><?
						$qry = "
						SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, topic_replies, aux.topic_id, category_label
						FROM mt_entry e, mt_author a, aux_mt_entry aux, ". TOPICS_TABLE ." t, mt_category c, mt_placement p
						WHERE entry_author_id = author_id
							AND e.entry_id = p.placement_entry_id
							AND p.placement_category_id = c.category_id
							AND p.placement_is_primary = 1
							AND e.entry_id = aux.entry_id
							AND aux.topic_id = t.topic_id
							AND e.entry_status = 2
							AND e.entry_blog_id = 7
						ORDER BY entry_created_on DESC LIMIT 10";
						$b_result = mQuery($qry);

						$x = 1;
						while ($row = $db->sql_fetchrow($b_result)) {
							$ts = strtotime($row[entry_created_on]);
							$y = date('Y', $ts);
							$m = date('m', $ts);
							$blog_dir = getBlogDir($row[entry_blog_id]);
							$link = "/$blog_dir/$y/$m/". dirify($row[entry_title]) .".php";
							$date = getDateString($ts);

							echo '<li><a href="'. $link .'">'. stripslashes($row[entry_title]) .'</a> - <span class="grey">'. $row[category_label] .'</span> - '. $date .'</li>';
						}
					?></ul>
				<span class="corners-bottom"><span></span></span></div>
				<div align="center"><?include(BASE_DIR .'/ads/skyscraper.php');?></div>

				<a name="archives"></a>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>News Archives</h2>
					<div style="float:right; width:50%">
						<b>Previous Years</b>
						<ul class="brownsquare" style="margin-top:0;"><?
							$qry = "
							SELECT FROM_UNIXTIME(pubDate, '%Y') y, COUNT(*) num
							FROM hdtv_rss
							WHERE rank > 0
								AND FROM_UNIXTIME(pubDate) < '$today' - INTERVAL 1 YEAR
							GROUP BY y ORDER BY pubDate DESC";
							$result = mQuery($qry);
							while ($row = mysql_fetch_assoc($result)) {
								echo '<li><a href="archive.php?year='. $row[y] .'">'. $row[y] .'</a><span class="grey"> ('. $row[num] .')</span></li>';
							}
						?></ul>
					</div>
					<b>Last 12 Months</b><ul class="brownsquare" style="margin-top:0;"><?
						$qry = "
						SELECT FROM_UNIXTIME(pubDate, '%Y') y, FROM_UNIXTIME(pubDate, '%M') m, COUNT(*) num
						FROM hdtv_rss
						WHERE rank > 0
							AND FROM_UNIXTIME(pubDate) > '$today' - INTERVAL 1 YEAR
						GROUP BY y,m ORDER BY pubDate DESC";
						$result = mQuery($qry);
						while ($row = mysql_fetch_assoc($result)) {
							echo '<li><a href="archive.php?month='. $row[m] .'&year='. $row[y] .'">'. $row[m] .'</a><span class="grey"> ('. $row[num] .')</span></li>';
						}
					?></ul>
				<span class="corners-bottom"><span></span></span></div>

			</td>
		</tr></table>
		</form>
	</div>

	<?include(BASE_DIR .'/includes/body_footer.php');?>
</body>
</html>
