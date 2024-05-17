<?
	### Test Template ###
	### 
	### This template is separated so that we can eliminate the join with the aux table.
	###
	###
	require('../global.php');
	header('Cache-Control: no-store'); // HTTP/1.1

	$feed_url = 'http://feeds.hdtvmagazine.com/hdtv-articles';
	$container = 'article_container';
	$category_page = 'category.php';
	$entry_type = 'Test Item';
	$sub_type = SUB_ARTICLES;
	$sub_label = 'Receive instant notification of new articles';
	$sub_desc_logged_in = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of new HDTV Magazine Articles via email as soon as they are published.';
	$sub_desc_anon = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of new HDTV Magazine Articles via email as soon as they are published.';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title>HDTV Magazine Testing Grounds</title>
	<?if ($feed_url != '') echo '<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="'. $feed_url .'" />';?>
	<meta name="generator" content="http://www.movabletype.org/" />
	<meta name="keywords" content="hdtv,hdtv news,hdtv reviews,hdtv information,hdtv articles,hdtv blog,hdtv guide,hdtv listings,hdtv faq,hdtv forum,hdtv forums,hdtv events,hdtv history,hdtv review,hdtv reviews,hdtv reference,hdtv book,hdtv books,hdtv products,hdtv help,hdtv satellite,hdtv cable,hdtv broadcast" />
	<meta name="description" content="" />
	<?require(BASE_DIR .'/includes/common_header.php');?>
</head>
<body><div id="<?=$container?>">
	<?
		include(BASE_DIR .'/ads/leaderboard.php');
		include(BASE_DIR .'/includes/body_header.php');
	?>
	
	<h1>HDTV Magazine Testing Grounds</h1>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<?if ($userdata[subscriptions] & $sub_type) {} else {
				if ($userdata[session_logged_in]) {?>
					<div class="important"><span class="corners-top"><span></span></span>
						<img src="/images/i_inbox.gif" align="left" style="padding-right:10px" />
						<span class="label"><?=$sub_label?>:</span>
						<?=$sub_desc_logged_in?>
					<span class="corners-bottom"><span></span></span></div>
				<?} else {?>
					<div class="important"><span class="corners-top"><span></span></span>
						<img src="/images/i_inbox.gif" align="left" style="padding-right:10px" />
						<span class="label"><?=$sub_label?>:</span>
						<?=$sub_desc_anon?>
					<span class="corners-bottom"><span></span></span></div>
				<?}
			}
				$sql = "
				SELECT DISTINCT entry_blog_id, e.entry_id, entry_excerpt, entry_created_on, entry_title, author_name
				FROM mt_entry e, mt_author a
				WHERE entry_author_id = author_id
					AND e.entry_status = 2
					AND e.entry_blog_id = 6
				ORDER BY entry_created_on DESC LIMIT 15";
				$result = mQuery($sql);
				
				$x = 0;
				$format = 'excerpt';
				while ($row = mysql_fetch_assoc($result)) {
					$x++;
					$ts = strtotime($row[entry_created_on]);
					$y = date('Y', $ts);
					$m = date('m', $ts);
					$ts_offset = $ts + $userdata['time_zone_offset'];
					$entry = getEntryInfo($row[entry_blog_id]);
		
					$entry[date] = getDateString($ts);
					$entry[link] = "/$entry[blog_dir]/$y/$m/". dirify($row[entry_title]) .".php";
					$entry[title] = $row[entry_title];
					$entry[author] = $row[author_name];
					$entry[excerpt] = $row[entry_excerpt];
					$entry[entry_type] = $entry_type;
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
						$entry[catlinks][] = '<a href="/category.php?id='. $row_category[category_id] .'&category='. urlencode(stripslashes($row_category[category_label])) .'">'. stripslashes($row_category[category_label]) .'</a>';
					}
		
					if ($x == 6) {
						echo '<h2>More Testing Grounds ...</h2>';
						$format = 'title-only';
					}

					echo getFormattedEntry($entry, $format);
				}
			?>
		</td><td id="right">

			<div align="center" style="margin:5px 0;">
				<?include(BASE_DIR .'/ads/mrectangle.php');?>
			</div>
			<br />
		
			<div class="item"><span class="corners-top"><span></span></span>
				<h2>More From...</h2>
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
				<h2>Categories</h2>
				<ul class="brownsquare"><?
					$qry = "
					SELECT category_id id, category_label label, COUNT(*) num
					FROM mt_entry e, mt_placement p, mt_category c
					WHERE e.entry_blog_id IN (". INCLUDE_BLOGS_ALL .")
						AND entry_status = 2
						AND entry_id = p.placement_entry_id
						AND p.placement_category_id = c.category_id
					GROUP BY id, label
					ORDER BY label";
					$result = mQuery($qry);
					while ($category = mysql_fetch_assoc($result)) {
						echo '<li><a href="/category.php?category='. urlencode($category[label]) .'&id='. $category[id] .'">'. $category[label] .'</a><span class="grey"> ('. $category[num] .')</span></li>';
					}
				?></ul>
			<span class="corners-bottom"><span></span></span></div>

			<div class="item"><span class="corners-top"><span></span></span>
				<h2>Testing Grounds Archive</h2>
				<div style="float:right; width:50%">
					<b>Previous Years</b>
					<ul class="brownsquare" style="margin-top:0;"><?
						$qry = "
						SELECT YEAR(entry_created_on) y, COUNT(*) num
						FROM mt_entry e
						WHERE e.entry_blog_id IN (". INCLUDE_BLOGS_ALL .")
							AND entry_status = 2
							AND entry_created_on < NOW() - INTERVAL 1 YEAR
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
						AND entry_created_on > NOW() - INTERVAL 1 YEAR
					GROUP BY y,m ORDER BY entry_created_on DESC";
					$result = mQuery($qry);
					while ($row = mysql_fetch_assoc($result)) {
						echo '<li><a href="/archive.php?month='. $row[m] .'&year='. $row[y] .'">'. $row[m] .'</a><span class="grey"> ('. $row[num] .')</span></li>';
					}
				?></ul>
			<span class="corners-bottom"><span></span></span></div>

			<?if ($feed_url != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>RSS Feeds</h2>
					<ul class="nosquare">
						<li><a target="_blank" href="http://www.bloglines.com/sub/<?=$feed_url?>"><img src="/images/chicklet-bloglines.gif" border="0" alt="Subscribe with Bloglines" /></a></li>
						<li><a target="_blank" href="<?=$feed_url?>"><img src="/images/chicklet-rss.gif" alt="RSS 2.0"></a></li>
						<li><a target="_blank" href="http://my.msn.com/addtomymsn.armx?id=rss&ut=<?=$feed_url?>"><img src="/images/chicklet-msn.gif" alt="Add to MyMSN"></a></li>
						<li><a target="_blank" href="http://add.my.yahoo.com/content?url=<?=$feed_url?>"><img src="/images/chicklet-yahoo.gif" alt="Add to My Yahoo!"></a></li>
						<li><a target="_blank" href="http://www.newsgator.com/ngs/subscriber/subext.aspx?url=<?=$feed_url?>"><img src="/images/chicklet-newsgator.gif" alt="Subscribe in NewsGator Online"></a></li>
						<li><a target="_blank" href="http://toolbar.google.com/buttons/add?url=http://www.hdtvmagazine.com/test/toolbar_button.xml">Add to Google Toolbar</a></li>
					</ul>
				<span class="corners-bottom"><span></span></span></div>
			<?}?>
			<div align="right">
			<?include(BASE_DIR .'/ads/skyscraper.php');?>
			</div>
			
		</td>
	</tr></table>
				
	<?include(BASE_DIR .'/includes/body_footer.php');?>
</div></body>
</html>
