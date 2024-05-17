<?
	require('global.php');

	$today = date('Y-m-d');
	$id = isset($_GET['id']) ? $_GET['id'] : '';

	if (isset($_GET['category'])) {
		$category = urldecode($_GET['category']);
	} elseif ($id != '') {
		$result = mQuery("SELECT category_label label FROM mt_category WHERE category_id = '$id'");
		$row = mysql_fetch_assoc($result);
		$category = $row['label'];
	}

	if ($id == '' && $category == '') js_back();

	include(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - <?=$category?></title>
	<meta name="description" content="Find the latest HDTV Magazine content related to <?=$category?>. Articles, columns, bulletins, reviews and more." />
	<meta name="keywords" content="hdtv,hdtv articles,high definition,hd,article,articles,column,columns,review,reviews,bulletin.bulletins,news,headlines,rss,feed,category,<?=$category?>" />
	<? require(BASE_DIR .'/includes/page_header.php');?>
</head>
<body><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>

	<h1><?=$category?></h1>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<?
				$sql = "
				SELECT DISTINCT entry_blog_id, e.entry_id, entry_excerpt, entry_created_on, entry_title, author_name, topic_replies, aux.topic_id, az.MediumImageURL img
				FROM mt_entry e, mt_author a, mt_placement p, mt_category c, aux_mt_entry aux
				LEFT JOIN ". TOPICS_TABLE ." t ON (aux.topic_id = t.topic_id)
				LEFT JOIN az_main az ON (aux.ASIN = az.ASIN)
				WHERE entry_author_id = author_id
					AND e.entry_id = p.placement_entry_id
					AND p.placement_category_id = c.category_id
					AND e.entry_id = aux.entry_id
					AND category_label = '$category'
					AND e.entry_status = 2
					AND e.entry_blog_id IN (". INCLUDE_BLOGS_ALL .")
				ORDER BY entry_created_on DESC";
				$result = mQuery($sql);

				$x = 0;
				$format = 'excerpt';
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

					if ($x == 6) {
						echo '<h2>More ...</h2>';
						$format = 'title-only';
					}

					echo getFormattedEntry($entry, $format);
				}
			?>
		</td><td id="right">

			<div align="center" style="margin:5px 0;">
				<? include(BASE_DIR .'/ads/mrectangle.php');?>
			</div>
			<br />

			<div class="item"><span class="corners-top"><span></span></span>
				<h2>Other Categories</h2>
				<ul class="brownsquare"><?
					$qry = "
					SELECT category_label label, COUNT(*) num
					FROM mt_entry e, mt_placement p, mt_category c
					WHERE e.entry_blog_id IN (". INCLUDE_BLOGS_ALL .")
						AND entry_status = 2
						AND entry_id = p.placement_entry_id
						AND p.placement_category_id = c.category_id
					GROUP BY label
					ORDER BY label";
					$result = mQuery($qry);
					while ($category = mysql_fetch_assoc($result)) {
						echo '<li><a href="/category.php?category='. urlencode($category[label]) .'">'. $category[label] .'</a><span class="grey"> ('. $category[num] .')</span></li>';
					}
				?></ul>
			<span class="corners-bottom"><span></span></span></div>

			<div class="item"><span class="corners-top"><span></span></span>
				<h2>Columnists</h2>
				<ul class="brownsquare"><?
					$qry = "
					SELECT author_id, author_name, COUNT(*) num
					FROM mt_author a, mt_entry e
					WHERE e.entry_blog_id IN (". INCLUDE_BLOGS_NO_BULLETINS .")
						AND entry_status = 2
						AND entry_author_id = author_id
					GROUP BY author_id, author_name
					ORDER BY num DESC";
					$result = mQuery($qry);
					while ($author = mysql_fetch_assoc($result)) {
						echo '<li><a href="/author.php?author='. urlencode($author[author_name]) .'&id='. $author[author_id] .'">'. $author[author_name] .'</a><span class="grey"> ('. $author[num] .')</span></li>';
					}
				?></ul>
			<span class="corners-bottom"><span></span></span></div>

			<div class="item"><span class="corners-top"><span></span></span>
				<h2>Archives</h2>
				<div style="float:right; width:50%">
					<b>Previous Years</b>
					<ul class="brownsquare" style="margin-top:0;"><?
						$qry = "
						SELECT YEAR(entry_created_on) y, COUNT(*) num
						FROM mt_entry e
						WHERE e.entry_blog_id IN (". INCLUDE_BLOGS_ALL .")
							AND entry_status = 2
							AND entry_created_on < '$today' - INTERVAL 1 YEAR
						GROUP BY y ORDER BY entry_created_on DESC";
						$result = mQuery($qry);
						while ($row = mysql_fetch_assoc($result)) {
							echo '<li><a href="/archive.php?year='. $row[y] .'">'. $row[y] .'</a><span class="grey"> ('. $row[num] .')</span></li>';
						}
					?></ul>
				</div>
				<b>Last 12 Months</b><ul class="brownsquare" style="margin-top:0;"><?
					$qry = "
					SELECT YEAR(entry_created_on) y, DATE_FORMAT(entry_created_on, '%M') m, COUNT(*) num
					FROM mt_entry e
					WHERE e.entry_blog_id IN (". INCLUDE_BLOGS_ALL .")
						AND entry_status = 2
						AND entry_created_on > '$today' - INTERVAL 1 YEAR
					GROUP BY y,m ORDER BY entry_created_on DESC";
					$result = mQuery($qry);
					while ($row = mysql_fetch_assoc($result)) {
						echo '<li><a href="/archive.php?month='. $row[m] .'&year='. $row[y] .'">'. $row[m] .'</a><span class="grey"> ('. $row[num] .')</span></li>';
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
