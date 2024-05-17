<?
	require('../global.php');

	$today = date('Y-m-d');
	$id = isset($_GET['id']) ? $_GET['id'] : '';
	if ($id == '') js_back();

	if (isset($_GET['author'])) {
		$author = urldecode($_GET['author']);
	} else {
		$result = mQuery("SELECT author_name FROM mt_author WHERE author_id = $id");
		$row = mysql_fetch_assoc($result);
		$author = $row['author_name'];
	}

	<? include(BASE_DIR .'/includes/doctype.php');?>
?>
<html>
<head>
	<title>HDTV Magazine - Articles by <?=$author?></title>
	<?
		if ($id == 1) echo '<meta name="microid" content="b2a16b1ec40b67553a9d995d41a561e4ceaee096" />';
	?>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/hdtv-articles" />
	<meta name="description" content="HDTV Magazine articles written by <?=$author?>." />
	<meta name="keywords" content="hdtv,hdtv articles,high definition,hd,article,articles,author,<?=$author?>" />
	<meta name="rating" content="general" />
	<? require(BASE_DIR .'/includes/page_header.php');?>
</head>
<body><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>

	<h1>Articles by <?=$author?></h1>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<?
				if ($user->data['subscriptions'] & SUB_ARTICLES) {} else {
					if ($user->data['is_registered']) {?>
						<div class="important"><span class="corners-top"><span></span></span>
							<span class="label">Receive instant notification of new articles:</span>
							<a href="<?=URL_PROFILE_SUBSCRIPTIONS?>">Modify your subscription profile</a> to receive notification of new
							HDTV Magazine Articles via email as soon as they are published.
						<span class="corners-bottom"><span></span></span></div>
					<?} else {?>
						<div class="important"><span class="corners-top"><span></span></span>
							<span class="label">Receive instant notification of new articles:</span>
							<a href="<?=URL_PROFILE_CREATE?>">Register Now</a> to receive notification of new
							HDTV Magazine Articles via email as soon as they are published.
						<span class="corners-bottom"><span></span></span></div>
					<? }
				}
				$sql = "
				SELECT DISTINCT entry_blog_id, e.entry_id, entry_excerpt, entry_created_on, entry_title, author_name, topic_replies, aux.topic_id
				FROM mt_entry e, mt_author a, mt_placement p, mt_category c, aux_mt_entry aux, phpbb_topics t
				WHERE entry_author_id = author_id
					AND e.entry_id = p.placement_entry_id
					AND e.entry_id = aux.entry_id
					AND aux.topic_id = t.topic_id
					AND p.placement_category_id = c.category_id
					AND e.entry_status = 2
					AND e.entry_blog_id = 1
					AND entry_author_id = $id
					AND entry_author_id = a.author_id
				ORDER BY entry_created_on DESC";
				$result = mQuery($sql);

				$x = 0;
				$format = 'excerpt';
				while ($row = mysql_fetch_assoc($result)) {
					$x++;
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
					$entry[entry_type] = 'Article';
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

					if ($x == 6) {
						echo '<h2>More Articles ...</h2>';
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
				<h2>Columnists</h2>
				<ul class="brownsquare"><?
					$qry = "
					SELECT author_id, author_name, COUNT(*) num
					FROM mt_author a, mt_entry e
					WHERE entry_blog_id = 1
						AND entry_status = 2
						AND entry_author_id = author_id
					GROUP BY author_id, author_name
					ORDER BY num DESC";
					$result = mQuery($qry);
					while ($author = mysql_fetch_assoc($result)) {
						echo '<li><a href="author.php?author='. urlencode($author[author_name]) .'&id='. $author[author_id] .'">'. $author[author_name] .'</a><span class="grey"> ('. $author[num] .')</span></li>';
					}
				?></ul>
			<span class="corners-bottom"><span></span></span></div>

			<div class="item"><span class="corners-top"><span></span></span>
				<h2>Categories</h2>
				<ul class="brownsquare"><?
					$qry = "
					SELECT category_id id, category_label label, COUNT(*) num
					FROM mt_entry e, mt_placement p, mt_category c
					WHERE entry_blog_id = 1
						AND entry_status = 2
						AND entry_id = p.placement_entry_id
						AND p.placement_category_id = c.category_id
					GROUP BY id, label
					ORDER BY label";
					$result = mQuery($qry);
					while ($category = mysql_fetch_assoc($result)) {
						echo '<li><a href="category.php?category='. urlencode($category[label]) .'&id='. $category[id] .'">'. $category[label] .'</a><span class="grey"> ('. $category[num] .')</span></li>';
					}
				?></ul>
			<span class="corners-bottom"><span></span></span></div>

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

			<div class="item"><span class="corners-top"><span></span></span>
				<h2>RSS Feeds</h2>
				<ul class="nosquare">
					<li><a target="_blank" href="http://www.bloglines.com/sub/http://feeds.hdtvmagazine.com/hdtv-articles"><img src="/images/chicklet-bloglines.gif" border="0" alt="Subscribe with Bloglines" /></a></li>
					<li><a target="_blank" href="http://feeds.hdtvmagazine.com/hdtv-articles"><img src="/images/chicklet-rss.gif" alt="RSS 2.0"></a></li>
					<li><a target="_blank" href="http://my.msn.com/addtomymsn.armx?id=rss&ut=http://feeds.hdtvmagazine.com/hdtv-articles&ru=<?=FULL_URL_ARTICLES?>"><img src="/images/chicklet-msn.gif" alt="Add to MyMSN"></a></li>
					<li><a target="_blank" href="http://add.my.yahoo.com/content?url=http://feeds.hdtvmagazine.com/hdtv-articles"><img src="/images/chicklet-yahoo.gif" alt="Add to My Yahoo!"></a></li>
					<li><a target="_blank" href="http://www.newsgator.com/ngs/subscriber/subext.aspx?url=http://feeds.hdtvmagazine.com/hdtv-articles"><img src="/images/chicklet-newsgator.gif" alt="Subscribe in NewsGator Online"></a></li>
					<li><a target="_blank" href="http://toolbar.google.com/buttons/add?url=<?=FULL_URL_ARTICLES_DIR?>/toolbar_button.xml">Add to Google Toolbar</a></li>
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
