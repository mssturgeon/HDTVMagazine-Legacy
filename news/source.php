<?
	header('Cache-Control: no-store');
	require('../global.php');

	$source = isset($_GET['source']) ? urldecode($_GET['source']) : '';
	if ($source == '') js_redirect('/news/index.php');

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - HDTV News from <?=$source?></title>
	<meta name="description" content="HDTV News from <?=$source?>.">
	<meta name="keywords" content="hdtv,hdtv articles,high definition,hd,news,headline,headlines,story,stories,category,rss,feed,<?=$source?>" />
	<meta name="rating" content="general">
	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Main Feed" href="http://feeds.hdtvmagazine.com/hdtv-news">
	<? require(BASE_DIR .'/includes/page_header.php');?>
</head>
<body>
	<?
		include(BASE_DIR .'/includes/body_header-4.php');

		include(BASE_DIR .'/includes/lib_news.php');
	?>

	<div id="body_container">
		<h1>HDTV News from <?=$source?></h1>
		<form name="frm" method="get" action="<?=PHP_SELF?>">
		<table class="bare" cellpadding="0" cellspacing="0"><tr>
			<td id="left"><?
					$sql = "
					SELECT DISTINCT id, pubDate, link, title, description, source, r.category_id, category_label, r.topic_id, topic_replies
					FROM mt_category c, hdtv_rss r
					LEFT JOIN ". TOPICS_TABLE ." t ON r.topic_id = t.topic_id
					WHERE rank > 0
						AND r.category_id = c.category_id
         			AND source = '$source'
					ORDER BY pubDate DESC LIMIT 20";
         		$result = $db->sql_query($sql);

					$x = 0;
					while ($row = mysql_fetch_assoc($result)) {
						$x++;
						$story['id'] = $row['id'];
						$story['date'] = getDateString($row['pubDate']);
						$story['link'] = '/news/story.php?title='. dirify($row['title']) .'&amp;id='. $row['id'];
						$story['title'] = $row['title'];
						$story['author'] = $row['source'];
						$story['excerpt'] = $row['description'];
						$story['entry_type'] = 'Story';
						$story['topic_replies'] = $row['topic_replies'];
						$story['topic_id'] = $row['topic_id'];

						$story['catlinks'] = array();
						$story['catlinks'][] = stripslashes($row['category_label']);
#							$catlinks[] = '<a href="?category='. urlencode(stripslashes($row_category[category_label])) .'">'. stripslashes($row_category[category_label]) .'</a>';

						if ($x <= 10) {
							echo getFormattedStory($story, 'excerpt');
						} else {
							if ($x == 11) echo '<h2>More Stories ...</h2>';
							echo getFormattedStory($story, 'title-only');
/*
							echo '<div class="story">'.
							'	<h4>'. implode(' &bull; ', $story[catlinks]) .'</h4>'.
							'	<h3><a href="'. stripslashes($story[link]) .'">'. stripslashes($story[title]) .'</a></h3>'.
							'	<h4><span style="text-transform:none">By</span> '. $story[author] .'<span style="color:#800000"> '. $story[date] .'</span></h4>'.
							'	<!--h4>Published in: </h4-->'.
							'</div>';
*/
						}
					}
				?>
			</td><td id="right">
		  		<? include(BASE_DIR .'/ads/mrectangle.php');?><br />
				<div align="center"><? include(BASE_DIR .'/ads/skyscraper.php');?></div>
				<div class="newsnet">
					<span class="corners-top"><span></span></span>
					<h2>In Other News ...</h2>
					<ul class="brownsquare"><?
						$sql = "
						SELECT DISTINCT id, pubDate, link, title, description, source, rank, category_label
						FROM hdtv_rss r, mt_category c
						WHERE r.category_id = c.category_id
							AND rank > 0
						ORDER BY pubDate DESC LIMIT 20";
						$n_result = $db->sql_query($sql);

						$x = 1;
						while ($row = $db->sql_fetchrow($n_result)) {
							$date = getDateString($row[pubDate]);
							echo '<li><a href="/news/story.php?title='. dirify($row[title]) .'&amp;id='. $row[id] .'">'. stripslashes($row[title]) .'</a> - <span class="grey">'. $row[source] .'</span> - '. $date .'</li>';
						}
					?></ul>
					<span class="corners-bottom"><span></span></span>
				</div><br />
			</td>
		</tr></table>
		</form>
	</div>

	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
</body>
</html>
