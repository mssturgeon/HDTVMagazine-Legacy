<?
	### INDEX.PHP.TPL ###
	require('../global.php');
	header('Cache-Control: no-store'); // HTTP/1.1

	$today = date('Y-m-d');

	# Set variables based on entry type. NOTE: This template is not used by Interviews, History, Bulletins or Podcasts
	switch (8) {
		case 1: # Articles
			$feed_url = 'http://feeds.hdtvmagazine.com/hdtv-articles';
			$container = 'body_container';
			$entry_type = 'Article';
			$sub_type = SUB_ARTICLES;
			$sub_label = 'Receive instant notification of new articles';
			$sub_desc_logged_in = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of new HDTV Magazine Articles via email as soon as they are published.';
			$sub_desc_anon = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of new HDTV Magazine Articles via email as soon as they are published.';
			break;
#		case 4: # Interviews
#		case 5: # History
		case 6: # Test
			$feed_url = 'http://feeds.hdtvmagazine.com/hdtv-articles';
			$container = 'article_container';
			$entry_type = 'Test Item';
			$sub_type = SUB_ARTICLES;
			$sub_label = 'Receive instant notification of new articles';
			$sub_desc_logged_in = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of new HDTV Magazine Articles via email as soon as they are published.';
			$sub_desc_anon = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of new HDTV Magazine Articles via email as soon as they are published.';
			break;
		case 7: # Bulletins
			$feed_name = 'hdtv-news';
#			$container = 'bulletin_container';
			$sub_type = SUB_BULLETINS;
			$sub_label = 'Receive instant notification of HDTV Bulletins';

			if ($user->data['is_registered']) {
				$sub_desc = '<a href="/profile-subscriptions.php">Modify your subscription profile</a> to receive notification of HDTV Bulletins via email as soon as they are published.';
			} else {
				$sub_desc = '<a href="/profile-create.php">Register Now</a> to receive notification of HDTV Bulletins via email as soon as they are published.';
			}
#			$meta_medium_type = 'news';

			$feed_url = 'http://feeds.hdtvmagazine.com/hdtv-news'; # User feed_name instead
			$container = 'body_container';
			$entry_type = 'Bulletin';
#			$sub_type = SUB_REVIEWS;
#			$sub_label = 'Receive instant notification of new reviews';

			# Use if/then above instead
			$sub_desc_logged_in = '<a href="/profile-subscriptions.php">Modify your subscription profile</a> to receive notification of HDTV Bulletins via email as soon as they are published.';
			$sub_desc_anon = '<a href="/profile-create.php">Register Now</a> to receive notification of HDTV Bulletins via email as soon as they are published.';
			break;
		case 8: # Reviews
			$feed_url = 'http://feeds.hdtvmagazine.com/hdtv-reviews';
			$container = 'body_container';
			$entry_type = 'Review';
			$sub_type = SUB_REVIEWS;
			$sub_label = 'Receive instant notification of new reviews';
			$sub_desc_logged_in = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of new HDTV Magazine Reviews via email as soon as they are published.';
			$sub_desc_anon = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of new HDTV Magazine Reviews via email as soon as they are published.';
			break;
#		case 9: # Podcasts
		case 10: # Columns
			$feed_url = 'http://feeds.hdtvmagazine.com/hdtv-columns';
			$container = 'body_container';
			$entry_type = 'Column';
			$sub_type = SUB_COLUMNS;
			$sub_label = 'Receive instant notification of new columns';
			$sub_desc_logged_in = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of new HDTV Magazine Columns via email as soon as they are published.';
			$sub_desc_anon = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of new HDTV Magazine Columns via email as soon as they are published.';
			break;
		default:
			$container = 'body_container';
			break;
	}

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<!-- index.php template -->
	<title>HDTV Magazine Reviews</title>
	<? if ($feed_url != '') echo '<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="'. $feed_url .'" />';?>
	<meta name="generator" content="http://www.movabletype.org/" />
	<meta name="keywords" content="hdtv,hdtv news,hdtv reviews,hdtv information,hdtv articles,hdtv blog,hdtv guide,hdtv listings,hdtv faq,hdtv forum,hdtv forums,hdtv events,hdtv history,hdtv review,hdtv reviews,hdtv reference,hdtv book,hdtv books,hdtv products,hdtv help,hdtv satellite,hdtv cable,hdtv broadcast" />
	<meta name="description" content="In-depth technical and consumer reviews on the latest high definition televisions (HDTV), programming and attached peripherals. Get our opinions on video quality, functions, cost and whether the extra features are worth the extra money." />
	<? require(BASE_DIR .'/includes/page_header.php');?>
</head>
<body><div id="<?=$container?>">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>

	<h1>HDTV Magazine Reviews</h1>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if ($user->data['subscriptions'] & $sub_type) {} else {
				if ($user->data['is_registered']) {?>
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
				ORDER BY entry_created_on DESC LIMIT 15";
				$result = mQuery($sql);

				$x = 0;
				$format = 'excerpt';
				while ($row = mysql_fetch_assoc($result)) {
					$x++;
					$ts = strtotime($row['entry_created_on']);
					$y = date('Y', $ts);
					$m = date('m', $ts);
					$ts_offset = $ts + $user->data['time_zone_offset'];
					$entry = getEntryInfo($row['entry_blog_id']);

					$entry['date'] = getDateString($ts);
					$entry['link'] = "/$entry[blog_dir]/$y/$m/". dirify($row['entry_title']) .".php";
					$entry['title'] = $row['entry_title'];
					$entry['author'] = $row['author_name'];
					$entry['excerpt'] = $row['entry_excerpt'];
					$entry['entry_type'] = $entry_type;
					$entry['topic_replies'] = $row['topic_replies'];
					$entry['topic_id'] = $row['topic_id'];
					$entry['image_src'] = $row['img'];

					# Get categories
					$sql = "
					SELECT c.category_label, c.category_id FROM mt_category c, mt_placement p
					WHERE {$row['entry_id']} = p.placement_entry_id
						AND c.category_id = p.placement_category_id";
					$res_categories = mQuery($sql);
					$entry['catlinks'] = array();
					while ($row_category = mysql_fetch_assoc($res_categories)) {
						$entry['catlinks'][] = '<a href="/category.php?id='. $row_category['category_id'] .'&category='. urlencode(stripslashes($row_category['category_label'])) .'">'. stripslashes($row_category['category_label']) .'</a>';
					}

					if ($x == 6) {
						echo '<h2>More Reviews ...</h2>';
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

			<div align="right">
				<? include(BASE_DIR .'/ads/skyscraper.php');?>
			</div>

			<?=getBoxAuthors()?>

			<?=getBoxCategories()?>

			<div class="item"><span class="corners-top"><span></span></span>
				<h2>Reviews Archive</h2>
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

			<? if ($feed_url != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>RSS Feeds</h2>
					<ul class="nosquare">
						<li><a target="_blank" href="http://www.bloglines.com/sub/<?=$feed_url?>"><img src="/images/chicklet-bloglines.gif" border="0" alt="Subscribe with Bloglines" /></a></li>
						<li><a target="_blank" href="<?=$feed_url?>"><img src="/images/chicklet-rss.gif" alt="RSS 2.0"></a></li>
						<li><a target="_blank" href="http://my.msn.com/addtomymsn.armx?id=rss&ut=<?=$feed_url?>"><img src="/images/chicklet-msn.gif" alt="Add to MyMSN"></a></li>
						<li><a target="_blank" href="http://add.my.yahoo.com/content?url=<?=$feed_url?>"><img src="/images/chicklet-yahoo.gif" alt="Add to My Yahoo!"></a></li>
						<li><a target="_blank" href="http://www.newsgator.com/ngs/subscriber/subext.aspx?url=<?=$feed_url?>"><img src="/images/chicklet-newsgator.gif" alt="Subscribe in NewsGator Online"></a></li>
						<li><a target="_blank" href="http://toolbar.google.com/buttons/add?url=http://www.hdtvmagazine.com/reviews/toolbar_button.xml">Add to Google Toolbar</a></li>
					</ul>
				<span class="corners-bottom"><span></span></span></div>
			<?}?>

		</td>
	</tr></table>

	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
</div></body>
</html>