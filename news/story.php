<?
	require('../global.php');

	// Try to get id from URL params. If it fails, we should redirect to a search. - Perhaps put this in a function?
	$id = isset($_GET['id']) ? $_GET['id'] : null;
	if (!preg_match("/^\d{1,10}$/", $id)) exit();

					$sql = "
					SELECT DISTINCT id, pubDate, link, title, full_description, source, r.category_id, category_label, r.topic_id, topic_replies
					FROM mt_category c, hdtv_rss r
					LEFT JOIN ". TOPICS_TABLE ." t ON r.topic_id = t.topic_id
					WHERE rank > 0
						AND r.category_id = c.category_id
         			AND id = '$id'
					ORDER BY pubDate DESC LIMIT 20";
	$res_news = $db->sql_query($sql);
	$row_news = $db->sql_fetchrow($res_news);
	$page_title = $row_news['title'] ." ($row_news[source])";
	$story['id'] = $row_news['id'];
	$story['date'] = getDateString($row_news[pubDate]);
	$story['link'] = $row_news['link'];
	$story['title'] = $row_news['title'];
	$story['author'] = $row_news['source'];
	$story['excerpt'] = $row_news['full_description'];
	$story['topic_replies'] = $row_news['topic_replies'];
	$story['topic_id'] = $row_news['topic_id'];
	$story['catlinks'] = array();
	$story['catlinks'][] = stripslashes($row_news['category_label']);

	# Get categories
/*
	$sql = "SELECT category_id, category_label FROM mt_category WHERE category_id = $row_news[category_id]";
	$res_categories = mQuery($sql);
	$catlinks = array();
	while ($row_category = mysql_fetch_assoc($res_categories)) {
		$category = stripslashes($row_category[category_label]);
		$catlinks[] = '<a href="category.php?id='. $row[category_id] .'&category='. urlencode($category) .'">'. $category .'</a>';
	}
*/

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - <?=$page_title?></title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
</head>
<body>
	<?
		include(BASE_DIR .'/includes/body_header-4.php');

		include(BASE_DIR .'/includes/lib_news.php');
	?>

	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<div class="anchors">
				Related sections:
				<a href="#othercategory">Other <?=$row_news[category_label]?> Stories</a> &bull;
				<a href="#othersource">Other <?=$row_news[source]?> Stories</a>
			</div>
			<div class="story_container"><?
/*
				$ts = $row_news[pubDate];
				$date = getDateString($ts);

				echo '<h3>'. implode(' &bull; ', $catlinks) .'</h3>'.
				'<h1><a target="_blank" href="/cgi-bin/ntlinktrack.cgi?'. stripslashes($row_news[link]) .'">'. stripslashes($row_news[title]) .'</a></h1>'.
				'<h3><span style="text-transform:none">By</span> '. $row_news[source] .' &bull; <span style="color:#800000"> '. $date .'</span></h3>'.
				'<!--h3>Published in: </h3-->'.
				nl2br(stripslashes($row_news[full_description]));
*/
				echo getFormattedStory($story, 'full');
			?></div>
		</td><td id="right" align="right">
			<? include(BASE_DIR .'/ads/mrectangle.php');?>
		</td>
	</tr><tr>
		<td id="left">
			<a name="othercategory"></a>
			<div class="item"><span class="corners-top"><span></span></span>
				<h2><a href="category.php?id=<?=$row_news[category_id]?>&category=<?=urlencode($row_news[category_label])?>">Other <?=$row_news[category_label]?> Stories</a></h2>
				<ul class="brownsquare"><?
					$sql = "
					SELECT DISTINCT id, pubDate, link, title, source, category_label
					FROM hdtv_rss r, mt_category c
					WHERE r.category_id = c.category_id
						AND id <> $id
						AND rank > 0
						AND c.category_label = '$row_news[category_label]'
					ORDER BY pubDate DESC LIMIT 10";
					$n_result = $db->sql_query($sql);

					$x = 1;
					while ($row = $db->sql_fetchrow($n_result)) {
						$ts = $row[pubDate];
						$date = getDateString($ts);
						echo '<li><a href="/news/story.php?title='. dirify($row[title]) .'&amp;id='. $row[id] .'">'. stripslashes($row[title]) .'</a> - <span class="grey">'. $row[source] .'</span> - '. $date .'</li>';
					}
				?></ul>
		  	<span class="corners-bottom"><span></span></span></div>

			<a name="othersource"></a>
			<div class="item"><span class="corners-top"><span></span></span>
				<h2><a href="/news/source.php?source=<?=urlencode($row_news[source])?>">Other <?=$row_news[source]?> Stories</a></h2>
				<ul class="brownsquare"><?
					$sql = "
					SELECT DISTINCT id, pubDate, link, title, source, category_label
					FROM hdtv_rss r, mt_category c
					WHERE r.category_id = c.category_id
						AND id <> '$id'
						AND rank > 0
						AND source = '$row_news[source]'
					ORDER BY pubDate DESC LIMIT 10";
					$n_result = $db->sql_query($sql);

					$x = 1;
					while ($row = $db->sql_fetchrow($n_result)) {
						$ts = $row[pubDate];
						$date = getDateString($ts);
						echo '<li><a href="/news/story.php?title='. dirify($row[title]) .'&amp;id='. $row[id] .'">'. stripslashes($row[title]) .'</a> - <span class="grey">'. $row[category_label] .'</span> - '. $date .'</li>';
					}
				?></ul>
		  	<span class="corners-bottom"><span></span></span></div>
		</td><td id="right" align="right">
			<br />
			<? include(BASE_DIR .'/ads/skyscraper.php');?>
		</td>
	</tr></table>

	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
</body>
</html>
