<?
	header('Cache-Control: no-store');
	require('../global.php');

	$today = date('Y-m-d');
	$id = isset($_GET[id]) ? $_GET[id] : '';
	if ($id == '') js_back();

	$category = isset($_GET[category]) ? urldecode($_GET[category]) : '';
	if ($category == '') js_replace('/news/index.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>HDTV Magazine - <?=$category?> News</title>
	<meta name="description" content="HDTV Magazine news covering <?=$category?>." />
	<meta name="keywords" content="hdtv,hdtv articles,high definition,hd,news,headline,headlines,story,stories,category,rss,feed,<?=$category?>" />
	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Main Feed" href="http://feeds.hdtvmagazine.com/hdtv-news">
	<?require(BASE_DIR .'/includes/common_header.php');?>
</head>
<body>
	<?
		include(BASE_DIR .'/includes/tracker.php');
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
	?>

	<div id="body_container">
		<h1><?=$category?> News</h1>
		<form name="frm" method="get" action="<?=PHP_SELF?>">
		<table class="bare" cellpadding="0" cellspacing="0"><tr>
			<td id="left">
				<div class="anchors">
					<a href="#bulletins">HDTV Bulletins</a> &bull;
					<a href="#archives">HDTV News Archives</a>
				</div>
				Category: <select name="category" onchange="submit()"><option value="">All<?
					$sql = "SELECT category_label FROM mt_category WHERE category_blog_id = 1 ORDER BY category_label;";
					$result = mQuery($sql);
					while ($row = mysql_fetch_assoc($result)) {
						$selected = ($category == $row[category_label]) ? 'selected="selected"' : '';
						echo '<option value="'. $row[category_label] .'"'. $selected .'>'. $row[category_label];
					}
				?></select><?
					$sql = "
					SELECT DISTINCT id, pubDate, link, title, description, source, rank, category_id
					FROM hdtv_rss r
					WHERE rank > 0
						AND category_id = $id
					ORDER BY pubDate DESC LIMIT 20";
					$result = $db->sql_query($sql);

					$x = 0;
					$format = 'excerpt';
					while ($row = mysql_fetch_assoc($result)) {
						$x++;
						$entry[date] = getDateString($row[pubDate]);
						$entry[link] = 'story.php?title='. dirify($row[title]) .'&amp;id='. $row[id];
						$entry[title] = $row[title];
						$entry[author] = $row[source];
						$entry[excerpt] = strip_tags($row[description]);
						$entry[entry_type] = 'Story';
#						$entry[topic_replies] = $row[topic_replies];
#						$entry[topic_id] = $row[topic_id];

						# Get categories
						$sql = "
						SELECT category_label FROM mt_category WHERE category_id = $row[category_id]";
						$res_categories = mQuery($sql);
						$entry[catlinks] = array();
						while ($row_category = mysql_fetch_assoc($res_categories)) {
							$entry[catlinks][] = '<a href="category.php?id='. $row[category_id] .'&category='. urlencode(stripslashes($row_category[category_label])) .'">'. stripslashes($row_category[category_label]) .'</a>';
#							$entry[catlinks][] = stripslashes($row_category[category_label]);
						}

						if ($x == 9) {
#							echo '<h2>Not-so-News ...</h2>';
							$format = 'title-only';
						}

						echo getFormattedEntry($entry, $format);
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
						FROM mt_entry e, mt_author a, aux_mt_entry aux, phpbb_topics t, mt_category c, mt_placement p
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
				<div align="right"><?include(BASE_DIR .'/ads/skyscraper.php');?></div>

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
