<?
	header('Cache-Control: no-store');
	require('../global.php');

	$today = date('Y-m-d');
	$id = isset($_GET[id]) ? $_GET[id] : '';
	if ($id == '') js_back();

	$category = isset($_GET[category]) ? urldecode($_GET[category]) : '';
	if ($category == '') js_replace('/news/bulletins.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>HDTV Magazine - <?=$category?> News</title>
	<meta name="description" content="HDTV Magazine news bulletins covering <?=$category?>." />
	<meta name="keywords" content="hdtv,hdtv articles,high definition,hd,news,bulletin,bulletins,headline,headlines,story,stories,category,rss,feed,<?=$category?>" />
	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Main Feed" href="http://feeds.hdtvmagazine.com/hdtv-news">
	<?require(BASE_DIR .'/includes/common_header.php');?>
</head>
<body>
	<?
		include(BASE_DIR .'/ads/leaderboard.php');
		include(BASE_DIR .'/includes/body_header.php');
	?>

	<div id="body_container">
		<h1><?=$category?> Bulletins</h1>
		<form name="frm" method="get" action="<?=PHP_SELF?>">
		<table class="bare" cellpadding="0" cellspacing="0"><tr>
			<td id="left">
				<div class="anchors">
	      		Related sections:
					<a href="#news"><?=$category?> News</a> &bull;
					<a href="#archives">Bulletin Archives</a>
				</div>
				<?
					$qry = "
					SELECT DISTINCT entry_blog_id, e.entry_id, entry_created_on, entry_title, entry_excerpt, author_name, topic_replies, aux.topic_id
					FROM mt_entry e, mt_author a, mt_placement p, mt_category c, aux_mt_entry aux, ". TOPICS_TABLE ." t
					WHERE entry_author_id = author_id
						AND e.entry_id = p.placement_entry_id
						AND p.placement_category_id = c.category_id
						AND e.entry_id = aux.entry_id
						AND aux.topic_id = t.topic_id
						AND e.entry_status = 2
						AND e.entry_blog_id = 7
						AND c.category_label = '$category'
					ORDER BY entry_created_on DESC LIMIT 20";
					$b_result = mQuery($qry);

					$x = 0;
					$format = 'excerpt';
					while ($row = mysql_fetch_assoc($b_result)) {
						$x++;
						$ts = strtotime($row[entry_created_on]);
						$y = date('Y', $ts);
						$m = date('m', $ts);
						$blog_dir = getBlogDir($row[entry_blog_id]);

						$entry[date] = getDateString($ts);
						$entry[link] = "/$blog_dir/$y/$m/". dirify($row[entry_title]) .".php";
						$entry[title] = $row[entry_title];
						$entry[author] = $row[author_name];
						$entry[excerpt] = $row[entry_excerpt];
						$entry[entry_type] = 'Bulletin';
						$entry[topic_replies] = $row[topic_replies];
						$entry[topic_id] = $row[topic_id];

						# Get categories
						$sql = "
						SELECT c.category_label, c.category_id FROM mt_category c, mt_placement p
						WHERE $row[entry_id] = p.placement_entry_id
							AND c.category_id = p.placement_category_id";
						$res_categories = mQuery($sql);
						$entry[catlinks] = array();
						while ($row_category = mysql_fetch_assoc($res_categories)) {
							$entry[catlinks][] = '<a href="/'. $blog_dir .'/category.php?id='. $row_category[category_id] .'&category='. urlencode(stripslashes($row_category[category_label])) .'">'. stripslashes($row_category[category_label]) .'</a>';
#							$entry[catlinks][] = stripslashes($row_category[category_label]);
						}

						if ($x == 10) {
							echo '<h2>More Bulletins ...</h2>';
							$format = 'title-only';
						}

						echo getFormattedEntry($entry, $format);
					}
				?>
			</td><td id="right">
		  		<?include(BASE_DIR .'/ads/mrectangle.php');?><br />
				<a name="news"></a>
				<div class="item">
					<span class="corners-top"><span></span></span>
					<h2><?=$category?> News ...</h2>
					<ul class="brownsquare"><?
						$sql = "
						SELECT DISTINCT id, pubDate, link, title, description, source, rank, category_label
						FROM hdtv_rss r
						WHERE r.category_id = $id
							AND rank > 0
						ORDER BY pubDate DESC LIMIT 20";
						$n_result = $db->sql_query($sql);

						while ($row = $db->sql_fetchrow($n_result)) {
							$ts = $row[pubDate];
							$date = getDateString($ts);
							echo '<li><a href="/news/story.php?title='. dirify($row[title]) .'&amp;id='. $row[id] .'">'. stripslashes($row[title]) .'</a> - <span class="grey">'. $row[source] .'</span> - '. $date .'</li>';
						}
					?></ul>
					<span class="corners-bottom"><span></span></span>
				</div><br />
				<div align="right"><?include(BASE_DIR .'/ads/skyscraper.php');?></div>

				<a name="archives"></a>
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
			</td>
		</tr></table>
		</form>
	</div>

	<?include(BASE_DIR .'/includes/body_footer.php');?>
</body>
</html>
