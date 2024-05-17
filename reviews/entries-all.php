<?
	### ENTRIES-ALL.PHP.TPL ###
	require('../global.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<!-- entries-all.php template -->
	<?require(BASE_DIR .'/includes/common_header.php');?>
	<title>HDTV Magazine Reviews - All Entries</title>

	<link rel="stylesheet" href="/stylesheets/blog_css.php" type="text/css" />
</head>
<body>
	<?
		include(BASE_DIR .'/ads/leaderboard.php');
		include(BASE_DIR .'/includes/body_header.php');
	?>

	<h1>Reviews - All Entries</h1>
	<div id="body_container">
	  	<div style="background-color:#FFFFFF; float:right; padding-left:10px;">
	  		<? include(BASE_DIR .'/ads/mrectangle.php'); ?><br />
			<div class="newsnet">
				<span class="corners-top"><span></span></span>
				<h2>Columnists</h2>
				<ul class="brownsquare"><?
					$qry = "
					SELECT author_id, author_name, COUNT(*) num
					FROM mt_author a, mt_entry e
					WHERE entry_blog_id = 8
						AND entry_status = 2
						AND entry_author_id = author_id
					GROUP BY author_id, author_name
					ORDER BY num DESC";
					$result = mQuery($qry);
					while ($author = mysql_fetch_assoc($result)) {
						echo '<li><a href="author.php?author='. urlencode($author[author_name]) .'&id='. $author[author_id] .'">'. $author[author_name] .'</a><span class="grey"> ('. $author[num] .')</span></li>';
					}
				?></ul>

				<h2>Categories</h2>
				<ul class="brownsquare"><?
					$qry = "
					SELECT category_id id, category_label label, COUNT(*) num
					FROM mt_entry e, mt_placement p, mt_category c
					WHERE entry_blog_id = 8
						AND entry_status = 2
						AND entry_id = p.placement_entry_id
						AND p.placement_category_id = c.category_id
					GROUP BY id, label
					ORDER BY num DESC";
					$result = mQuery($qry);
					while ($category = mysql_fetch_assoc($result)) {
						echo '<li><a href="category.php?category='. urlencode($category[label]) .'&id='. $category[id] .'">'. $category[label] .'</a><span class="grey"> ('. $category[num] .')</span></li>';
					}
				?></ul>

				<h2>RSS Feeds</h2>
				<ul class="nosquare">
					<li><a target="_blank" href="http://www.bloglines.com/sub/http://feeds.hdtvmagazine.com/hdtv"><img src="/images/chicklet-bloglines.gif" border="0" alt="Subscribe with Bloglines" /></a></li>
					<li><a target="_blank" href="http://feeds.hdtvmagazine.com/hdtv"><img src="/images/chicklet-rss.gif" alt="RSS 2.0"></a></li>
					<li><a target="_blank" href="http://my.msn.com/addtomymsn.armx?id=rss&ut=http://feeds.hdtvmagazine.com/hdtv&ru=<?=FULL_URL_ARTICLES?>"><img src="/images/chicklet-msn.gif" alt="Add to MyMSN"></a></li>
					<li><a target="_blank" href="http://add.my.yahoo.com/content?url=http://feeds.hdtvmagazine.com/hdtv"><img src="/images/chicklet-yahoo.gif" alt="Add to My Yahoo!"></a></li>
					<li><a target="_blank" href="http://www.newsgator.com/ngs/subscriber/subext.aspx?url=http://feeds.hdtvmagazine.com/hdtv"><img src="/images/chicklet-newsgator.gif" alt="Subscribe in NewsGator Online"></a></li>
					<li><a target="_blank" href="http://toolbar.google.com/buttons/add?url=<?=FULL_URL_ARTICLES_DIR?>/toolbar_button.xml">Add to Google Toolbar</a></li>
				</ul>

				<span class="corners-bottom"><span></span></span>
			</div><br />
			<div><?include(BASE_DIR .'/ads/skyscraper.php');?></div>
		</div>

		<div id="container">
			<div class="content">
				<?
					$qry = "
					SELECT entry_created_on, entry_title, author_name
					FROM mt_entry e, mt_author a
					WHERE
						e.entry_author_id = a.author_id
						AND entry_blog_id = 8
						AND entry_status = 2
					ORDER BY entry_created_on DESC";
					$result = mQuery($qry);
					while ($entry = mysql_fetch_assoc($result)) {
						$ts = strtotime($entry[entry_created_on]);
						$y = date('Y', $ts);
						$m = date('m', $ts);
						echo '<div class="entry_date">'. date('M j, Y', $ts) .'</div>'.
						'<div class="entry"><a href="'. $y .'/'. $m .'/'. dirify($entry[entry_title]) .'.php">'. $entry[entry_title] .'</a><span class="grey"> ('. $entry[author_name] .')</span></div>';
					}
				?>
			</div>
		</div>
	</div>
	<div style="clear: both;">&#160;</div>
	<?include(BASE_DIR .'/includes/body_footer.php');?>
</body>
</html>