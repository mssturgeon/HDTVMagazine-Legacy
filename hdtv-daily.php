<?
	define('BASE_DIR', '/var/www/html');
	require(BASE_DIR .'/includes/constants.php');
	require(BASE_DIR .'/includes/lib_mysql.php');
	require(BASE_DIR .'/includes/lib_common.php');

	$emailed = isset($_GET['emailed']);
	$online_url = BASE_URL .'/daily.php';
	$today = date('l, F jS, Y');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>HDTV Magazine Daily (<?=$today?>)</title>
	<style>
		<? include(BASE_DIR .'/stylesheets/email_css.php') ?>
	</style>
	<base target="_blank">
</head>
<body><div id="email_container">
	<?
		$tracking = '?utm_source=hdtvmagazine&utm_medium=email&utm_content=open&utm_campaign=daily';
		include(BASE_DIR .'/includes/email_header.php');
	?>

	<div id="date"><?=$today?></div>
	<table cellpadding="0" cellspacing="0" border="0" style="margin:0; padding:0; width:100%"><tr><td style="width:200px; vertical-align:top">
		<div class="item"><span class="corners-top"><span></span></span>
			<h2>In this Issue</h2>
			<ul>
				<li><a href="<?=$online_url?>#thisweek">New This Week</a></li>
				<li><a href="<?=$online_url?>#articles">Latest Articles</a></li>
				<li><a href="<?=$online_url?>#columns">Current Columns</a></li>
				<li><a href="<?=$online_url?>#reviews">Recent Reviews</a></li>
				<li><a href="<?=$online_url?>#podcasts">Newest Podcasts</a></li>
				<li><a href="<?=$online_url?>#news">Top HDTV News</a></li>
				<li><a href="<?=$online_url?>#bulletins">Breaking Bulletins</a></li>
				<li><a href="<?=$online_url?>#forum">Latest Forum Discussions</a></li>
			</ul>
		<span class="corners-bottom"><span></span></span></div>
	</td><td style="padding-left:10px; vertical-align:top;">
		<?if (!$emailed) {?>
			<div class="important"><span class="corners-top"><span></span></span>
				<a href="<?=BASE_URL?>/profile-create.php"><img src="<?=BASE_URL?>/images/i_inbox.gif" align="left" style="padding-right:10px" /></a>
				<span class="label">Receive this page in your inbox daily:</span>
				<a href="<?=BASE_URL?>/profile-create.php">Register Now</a> to receive the HDTV Magazine Daily
				via email as soon as it is published.
			<span class="corners-bottom"><span></span></span></div>
		<?}?>
		<?
			include(BASE_DIR .'/ads/banner_email.php');

			$ad = array();

			# Run until 2009-09-02
/*
			$ad[] = '<a target="_blank" href="http://www.displaysearch.com/tvecosystem"'.
			'><img src="'. BASE_URL .'/images/events/tve-2009-468x60.gif" alt="2009 TV Ecosystem Conference"></a>';

			if (count($ad) > 0) {
				$x = rand(0, count($ad) - 1);
				echo '<div class="ad" style="text-align:center"><span class="corners-top"><span></span></span>'. $ad[$x] .'<span class="corners-bottom"><span></span></span></div>';
			}
*/
		?>
	</td></tr></table>

		<a name="thisweek"></a>
		<div class="item" style="clear:both"><span class="corners-top"><span></span></span>
			<h2>New This Week</h2>
			<ul><?
				$qry = "
				SELECT e.entry_id, entry_blog_id, entry_created_on, entry_title, author_id, author_name, category_label, topic_replies, aux.topic_id
				FROM mt_entry e, mt_author a, mt_category c, mt_placement p, aux_mt_entry aux, ". TOPICS_TABLE ." t
				WHERE entry_status = 2
					AND entry_author_id = a.author_id
					AND e.entry_id = aux.entry_id
					AND aux.topic_id = t.topic_id
					AND e.entry_id = p.placement_entry_id
					AND p.placement_category_id = c.category_id
					AND p.placement_is_primary
					AND entry_created_on > CURDATE() - INTERVAL 7 DAY
					AND entry_blog_id IN (". INCLUDE_BLOGS_ALL .")
				ORDER BY entry_created_on DESC";
				$result = mQuery($qry);

				while ($row = mysql_fetch_assoc($result)) {
					$ts = strtotime($row['entry_created_on']);
					$y = date('Y', $ts);
					$m = date('m', $ts);
					$ts_offset = $ts + $user->data['time_zone_offset'];
					$entry = getEntryInfo($row['entry_blog_id']);

					$entry['date'] = getDateString($ts);
					$entry['link'] = "BASE_URL/{$entry['blog_dir']}/$y/$m/". dirify($row['entry_title']) .".php";

					# Get categories
					$sql = "
					SELECT c.category_label, c.category_id FROM mt_category c, mt_placement p
					WHERE {$row['entry_id']} = p.placement_entry_id
						AND c.category_id = p.placement_category_id";
					$res_categories = mQuery($sql);
					$entry['catlinks'] = array();
					while ($row_category = mysql_fetch_assoc($res_categories)) {
						$entry['catlinks'][] = '<a href="'. BASE_URL .'/category.php?id='. $row_category['category_id'] .'&category='. urlencode(stripslashes($row_category['category_label'])) .'">'. stripslashes($row_category['category_label']) .'</a>';
					}
					if ($entry['catlinks'] != '') $catlinks =  ' &bull; '. implode("\n &bull; ", $entry['catlinks']);

					if ($row['topic_id'] != '') {
						if ($row['topic_replies'] > 0) {
							$comments = '<a href="'. BASE_URL .'/forum/viewtopic.php?t='. $row['topic_id'] .'">Comments</a> ('. $row['topic_replies'] .')';
						} else {
							$comments = '<a class="red" href="'. BASE_URL .'/forum/viewtopic.php?t='. $row['topic_id'] .'">Post First Comment</a>';
						}
					}

					echo "<li>\n".
					'<h3><a href="'. $entry['link'] .'">'. $row['entry_title'] ."</a></h3>\n".
					'<h4 style="float:right;"><img src="'. BASE_URL .'/images/icon_comments.gif" alt="" /> '. $comments ."</h4>\n".
					'<h4><span style="text-transform:none">By</span> '. $row['author_name'] ." &bull; \n".
					'<span style="color:#800000"> '. $entry['date'] ."</span>\n".
					$catlinks ."</h4></li>\n";
				}
			?></ul>
		<span class="corners-bottom"><span></span></span></div>

		<a name="articles"></a>
		<div class="item"><span class="corners-top"><span></span></span>
			<h2>
				<a href="http://feeds.hdtvmagazine.com/hdtv-articles"><img src="<?=BASE_URL?>/images/livemark.png" alt="RSS" align="right" /></a>
				<img src="<?=BASE_URL?>/images/i_document.gif" align="absmiddle" style="padding-right:10px" />
				<a href="<?=BASE_URL?>/articles/index.php">Latest Articles</a>
			</h2>
			<ul><?
				$qry = "
				SELECT e.entry_id, entry_blog_id, entry_created_on, entry_title, author_id, author_name, category_label, topic_replies, aux.topic_id
				FROM mt_entry e, mt_author a, mt_category c, mt_placement p, aux_mt_entry aux, ". TOPICS_TABLE ." t
				WHERE entry_blog_id = 1
					AND entry_status = 2
					AND entry_author_id = a.author_id
					AND e.entry_id = aux.entry_id
					AND aux.topic_id = t.topic_id
					AND e.entry_id = p.placement_entry_id
					AND p.placement_category_id = c.category_id
					AND p.placement_is_primary
				ORDER BY entry_created_on DESC LIMIT 5";
				$result = mQuery($qry);

				while ($row = mysql_fetch_assoc($result)) {
					$ts = strtotime($row[entry_created_on]);
					$y = date('Y', $ts);
					$m = date('m', $ts);
					$date = getDateString($ts);
					$link = BASE_URL ."/articles/$y/$m/". dirify($row[entry_title]) .".php";

					if ($row[topic_id] != '') {
						if ($row[topic_replies] > 0) {
							$comments = '<a href="'. BASE_URL .'/forum/viewtopic.php?t='. $row[topic_id] .'">Comments</a> ('. $row[topic_replies] .')';
						} else {
							$comments = '<a class="red" href="'. BASE_URL .'/forum/viewtopic.php?t='. $row[topic_id] .'">Post First Comment</a>';
						}
					}

					echo "<li>\n".
					'<h3><a href="'. $link .'">'. $row['entry_title'] ."</a></h3>\n".
					'<h4 style="float:right;"><img src="'. BASE_URL .'/images/icon_comments.gif" alt="" /> '. $comments ."</h4>\n".
					'<h4><span style="text-transform:none">By</span> '. $row['author_name'] ." &bull; \n".
					'<span style="color:#800000"> '. $date ."</span> &bull; \n".
					"$row[category_label]</h4></li>\n";
				}
			?></ul>
		<span class="corners-bottom"><span></span></span></div>

		<a name="columns"></a>
		<div class="item"><span class="corners-top"><span></span></span>
			<h2>
				<a href="http://feeds.hdtvmagazine.com/hdtv-columns"><img src="<?=BASE_URL?>/images/livemark.png" alt="RSS" align="right" /></a>
				<img src="<?=BASE_URL?>/images/i_columns.gif" align="absmiddle" style="padding-right:10px" />
				<a href="<?=BASE_URL?>/columns/index.php">Current Columns</a>
			</h2>
			<ul><?
				$qry = "
				SELECT e.entry_id, entry_blog_id, entry_created_on, entry_title, author_id, author_name, category_label, topic_replies, aux.topic_id
				FROM mt_entry e, mt_author a, mt_category c, mt_placement p, aux_mt_entry aux, ". TOPICS_TABLE ." t
				WHERE entry_blog_id = 10
					AND entry_status = 2
					AND entry_author_id = a.author_id
					AND e.entry_id = aux.entry_id
					AND aux.topic_id = t.topic_id
					AND e.entry_id = p.placement_entry_id
					AND p.placement_category_id = c.category_id
					AND p.placement_is_primary
				ORDER BY entry_created_on DESC LIMIT 5";
				$result = mQuery($qry);

				while ($row = mysql_fetch_assoc($result)) {
					$ts = strtotime($row[entry_created_on]);
					$y = date('Y', $ts);
					$m = date('m', $ts);
					$date = getDateString($ts);
					$link = BASE_URL ."/columns/$y/$m/". dirify($row[entry_title]) .".php";

					if ($row[topic_id] != '') {
						if ($row[topic_replies] > 0) {
							$comments = '<a href="'. BASE_URL .'/forum/viewtopic.php?t='. $row[topic_id] .'">Comments</a> ('. $row[topic_replies] .')';
						} else {
							$comments = '<a class="red" href="'. BASE_URL .'/forum/viewtopic.php?t='. $row[topic_id] .'">Post First Comment</a>';
						}
					}

					echo "<li>\n".
					'<h3><a href="'. $link .'">'. $row['entry_title'] ."</a></h3>\n".
					'<h4 style="float:right;"><img src="'. BASE_URL .'/images/icon_comments.gif" alt="" /> '. $comments ."</h4>\n".
					'<h4><span style="text-transform:none">By</span> '. $row['author_name'] ." &bull; \n".
					'<span style="color:#800000"> '. $date ."</span> &bull; \n".
					"$row[category_label]</h4></li>\n";
#					echo '<li>'.
#					'<h3><a href="'. $link .'">'. $row[entry_title] .'</a></h3>'.
#					'<h4 style="float:right;"><img src="'. BASE_URL .'/images/icon_comments.gif" alt="" /> '. $comments .'</h4>'.
#					'<h4><span style="text-transform:none">By</span> '. $row[author_name] .' &bull; '.
#					'<span style="color:#800000"> '. $date .'</span> &bull; '.
#					"$row[category_label]</h4></li>\n";
				}
			?></ul>
		<span class="corners-bottom"><span></span></span></div>

		<a name="reviews"></a>
		<div class="item"><span class="corners-top"><span></span></span>
			<h2>
				<a href="http://feeds.hdtvmagazine.com/hdtv-reviews"><img src="<?=BASE_URL?>/images/livemark.png" alt="RSS" align="right" /></a>
				<img src="<?=BASE_URL?>/images/i_reviews.gif" align="absmiddle" style="padding-right:10px" />
				<a href="<?=BASE_URL?>/reviews/index.php">Recent Reviews</a>
			</h2>
			<ul><?
				$qry = "
				SELECT e.entry_id, entry_blog_id, entry_created_on, entry_title, author_id, author_name, category_label, topic_replies, aux.topic_id
				FROM mt_entry e, mt_author a, mt_category c, mt_placement p, aux_mt_entry aux, ". TOPICS_TABLE ." t
				WHERE entry_blog_id = 8
					AND entry_status = 2
					AND entry_author_id = a.author_id
					AND e.entry_id = aux.entry_id
					AND aux.topic_id = t.topic_id
					AND e.entry_id = p.placement_entry_id
					AND p.placement_category_id = c.category_id
					AND p.placement_is_primary
				ORDER BY entry_created_on DESC LIMIT 5";
				$result = mQuery($qry);

				while ($row = mysql_fetch_assoc($result)) {
					$ts = strtotime($row[entry_created_on]);
					$y = date('Y', $ts);
					$m = date('m', $ts);
					$date = getDateString($ts);
					$link = BASE_URL ."/reviews/$y/$m/". dirify($row[entry_title]) .".php";

					if ($row[topic_id] != '') {
						if ($row[topic_replies] > 0) {
							$comments = '<a href="'. BASE_URL .'/forum/viewtopic.php?t='. $row[topic_id] .'">Comments</a> ('. $row[topic_replies] .')';
						} else {
							$comments = '<a class="red" href="'. BASE_URL .'/forum/viewtopic.php?t='. $row[topic_id] .'">Post First Comment</a>';
						}
					}

					echo "<li>\n".
					'<h3><a href="'. $link .'">'. $row['entry_title'] ."</a></h3>\n".
					'<h4 style="float:right;"><img src="'. BASE_URL .'/images/icon_comments.gif" alt="" /> '. $comments ."</h4>\n".
					'<h4><span style="text-transform:none">By</span> '. $row['author_name'] ." &bull; \n".
					'<span style="color:#800000"> '. $date ."</span> &bull; \n".
					"$row[category_label]</h4></li>\n";
#					echo '<li>'.
#					'<h3><a href="'. $link .'">'. $row[entry_title] .'</a></h3>'.
#					'<h4 style="float:right;"><img src="'. BASE_URL .'/images/icon_comments.gif" alt="" /> '. $comments .'</h4>'.
#					'<h4><span style="text-transform:none">By</span> '. $row[author_name] .' &bull; '.
#					'<span style="color:#800000"> '. $date .'</span> &bull; '.
#					"$row[category_label]</h4></li>\n";
				}
			?></ul>
		<span class="corners-bottom"><span></span></span></div>

		<a name="podcasts"></a>
		<div class="item"><span class="corners-top"><span></span></span>
			<h2>
				<img src="<?=BASE_URL?>/images/i_podcast.gif" align="absmiddle" style="padding-right:10px" />
				<a href="<?=BASE_URL?>/columns/index.php">Newest Podcasts</a>
			</h2>
			<ul><?
				$qry = "
				SELECT e.entry_id, entry_blog_id, entry_created_on, entry_title, author_id, author_name, category_label, topic_replies, aux.topic_id
				FROM mt_entry e, mt_author a, mt_category c, mt_placement p, aux_mt_entry aux, ". TOPICS_TABLE ." t
				WHERE entry_blog_id = 9
					AND entry_status = 2
					AND entry_author_id = a.author_id
					AND e.entry_id = aux.entry_id
					AND aux.topic_id = t.topic_id
					AND e.entry_id = p.placement_entry_id
					AND p.placement_category_id = c.category_id
					AND p.placement_is_primary
				ORDER BY entry_created_on DESC LIMIT 5";
				$result = mQuery($qry);

				while ($row = mysql_fetch_assoc($result)) {
					$ts = strtotime($row[entry_created_on]);
					$y = date('Y', $ts);
					$m = date('m', $ts);
					$date = getDateString($ts);
					$link = BASE_URL ."/podcast/$y/$m/". dirify($row[entry_title]) .".php";

					if ($row[topic_id] != '') {
						if ($row[topic_replies] > 0) {
							$comments = '<a href="'. BASE_URL .'/forum/viewtopic.php?t='. $row[topic_id] .'">Comments</a> ('. $row[topic_replies] .')';
						} else {
							$comments = '<a class="red" href="'. BASE_URL .'/forum/viewtopic.php?t='. $row[topic_id] .'">Post First Comment</a>';
						}
					}

					echo "<li>\n".
					'<h3><a href="'. $link .'">'. $row['entry_title'] ."</a></h3>\n".
					'<h4 style="float:right;"><img src="'. BASE_URL .'/images/icon_comments.gif" alt="" /> '. $comments ."</h4>\n".
					'<h4><span style="text-transform:none">By</span> '. $row['author_name'] ." &bull; \n".
					'<span style="color:#800000"> '. $date ."</span> &bull; \n".
					"$row[category_label]</h4></li>\n";
#					echo '<li>'.
#					'<h3><a href="'. $link .'">'. $row[entry_title] .'</a></h3>'.
#					'<h4 style="float:right;"><img src="'. BASE_URL .'/images/icon_comments.gif" alt="" /> '. $comments .'</h4>'.
#					'<h4><span style="text-transform:none">By</span> '. $row[author_name] .' &bull; '.
#					'<span style="color:#800000"> '. $date .'</span> &bull; '.
#					"$row[category_label]</h4></li>\n";
				}
			?></ul>
		<span class="corners-bottom"><span></span></span></div>

		<a name="news"></a>
		<div class="item"><span class="corners-top"><span></span></span>
			<h2>
				<a href="http://feeds.hdtvmagazine.com/hdtv-news"><img src="<?=BASE_URL?>/images/livemark.png" alt="RSS" align="right" /></a>
				<img src="<?=BASE_URL?>/images/i_news.gif" align="absmiddle" style="padding-right:10px" />
				<a href="<?=BASE_URL?>/news/index.php">Top HDTV News</a>
			</h2>
			<ul><?
				$sql = "
				SELECT DISTINCT id, pubDate, link, title, source, category_label, r.topic_id, t.topic_replies
				FROM mt_category c, hdtv_rss r
				LEFT JOIN ". TOPICS_TABLE ." t ON r.topic_id = t.topic_id
				WHERE rank > 0
					AND r.category_id = c.category_id
				ORDER BY pubDate DESC LIMIT 5";
				$result = mQuery($sql);

				$x = 0;
				while ($row = mysql_fetch_assoc($result)) {
					$x++;
					$date = getDateString($row[pubDate]);
					$link = BASE_URL .'/news/story.php?title='. dirify($row[title]) .'&amp;id='. $row[id];

					if ($row[topic_replies] > 0) {
						$comments = '<img src="'. BASE_URL .'/images/icon_comments.gif" alt="" /> '.
						'<a href="'. BASE_URL .'/forum/viewtopic.php?t='. $row[topic_id] .'">Comments</a> ('. $row[topic_replies] .')';
					} else {
						if ($row[topic_id] > 0) {
							$comments = '<img src="'. BASE_URL .'/images/icon_comments.gif" alt="" /> '.
							'<a class="red" href="'. BASE_URL .'/forum/viewtopic.php?t='. $row[topic_id] .'">Post First Comment</a>';
						} else {
							$comments = '<img src="'. BASE_URL .'/images/icon_comments.gif" alt="" /> '.
							'<a class="red" href="'. BASE_URL .'/news/post_comment.php?id='. $row[id] .'">Post First Comment</a>';
						}
					}

					echo "<li>\n".
					'<h3><a href="'. $link .'">'. $row['title'] ."</a></h3>\n".
					'<h4 style="float:right;">'. $comments ."</h4>\n".
					'<h4><span style="text-transform:none">By</span> '. $row['source'] ." &bull; \n".
					'<span style="color:#800000"> '. $date ."</span> &bull; \n".
					"$row[category_label]</h4></li>\n";
#					echo '<li>'.
#					'<h3><a href="'. $link .'">'. $row[title] .'</a></h3>'.
#					'<h4 style="float:right;">'. $comments .'</h4>'.
#					'<h4><span style="text-transform:none">By</span> '. $row[source] .' &bull; '.
#					'<span style="color:#800000"> '. $date .'</span> &bull; '.
#					"$row[category_label]</h4></li>\n";
				}
			?></ul>
		<span class="corners-bottom"><span></span></span></div>

		<a name="bulletins"></a>
		<div class="item"><span class="corners-top"><span></span></span>
			<h2>
				<a href="http://feeds.hdtvmagazine.com/hdtv-bulletins"><img src="<?=BASE_URL?>/images/livemark.png" alt="RSS" align="right" /></a>
				<img src="<?=BASE_URL?>/images/i_bulletins.gif" align="absmiddle" style="padding-right:10px" />
				<a href="<?=BASE_URL?>/news/bulletins.php">Breaking Bulletins</a>
			</h2>
			<ul><?
				$qry = "
				SELECT e.entry_id, entry_blog_id, entry_created_on, entry_title, author_id, author_name, category_label, topic_replies, aux.topic_id
				FROM mt_entry e, mt_author a, mt_category c, mt_placement p, aux_mt_entry aux, ". TOPICS_TABLE ." t
				WHERE entry_blog_id = 7
					AND entry_status = 2
					AND entry_author_id = a.author_id
					AND e.entry_id = aux.entry_id
					AND aux.topic_id = t.topic_id
					AND e.entry_id = p.placement_entry_id
					AND p.placement_category_id = c.category_id
					AND p.placement_is_primary
				ORDER BY entry_created_on DESC LIMIT 5";
				$result = mQuery($qry);

				while ($row = mysql_fetch_assoc($result)) {
					$ts = strtotime($row[entry_created_on]);
					$y = date('Y', $ts);
					$m = date('m', $ts);
					$date = getDateString($ts);
					$link = BASE_URL ."/news/$y/$m/". dirify($row[entry_title]) .".php";

					if ($row[topic_id] != '') {
						if ($row[topic_replies] > 0) {
							$comments = '<a href="'. BASE_URL .'/forum/viewtopic.php?t='. $row[topic_id] .'">Comments</a> ('. $row[topic_replies] .')';
						} else {
							$comments = '<a class="red" href="'. BASE_URL .'/forum/viewtopic.php?t='. $row[topic_id] .'">Post First Comment</a>';
						}
					}

					echo "<li>\n".
					'<h3><a href="'. $link .'">'. $row['entry_title'] ."</a></h3>\n".
					'<h4 style="float:right;"><img src="'. BASE_URL .'/images/icon_comments.gif" alt="" /> '. $comments ."</h4>\n".
					'<h4><span style="text-transform:none">By</span> '. $row['author_name'] ." &bull; \n".
					'<span style="color:#800000"> '. $date ."</span> &bull; \n".
					"$row[category_label]</h4></li>\n";
#					echo '<li>'.
#					'<h3><a href="'. $link .'">'. $row[entry_title] .'</a></h3>'.
#					'<h4 style="float:right;"><img src="'. BASE_URL .'/images/icon_comments.gif" alt="" /> '. $comments .'</h4>'.
#					'<h4><span style="text-transform:none">By</span> '. $row[author_name] .' &bull; '.
#					'<span style="color:#800000"> '. $date .'</span> &bull; '.
#					"$row[category_label]</h4></li>\n";
				}
			?></ul>
		<span class="corners-bottom"><span></span></span></div>

		<a name="forum"></a>
		<div class="item"><span class="corners-top"><span></span></span>
			<h2>
				<a href="http://feeds.hdtvmagazine.com/hdtv-forum"><img src="<?=BASE_URL?>/images/livemark.png" alt="RSS" align="right" /></a>
				<img src="<?=BASE_URL?>/images/i_forum.gif" align="absmiddle" style="padding-right:10px" />
				<a href="<?=BASE_URL?>/forum/index.php">Latest Forum Discussions</a>
			</h2>
			<ul><?
				$qry = "
				SELECT topic_title, t.topic_id, username as post_author, post_time, post_id, forum_name, t.topic_id, t.topic_replies
				FROM ". TOPICS_TABLE ." t, ". USERS_TABLE ." u, ". POSTS_TABLE ." p, ". FORUMS_TABLE ." f, aux_phpbb_forums af
				WHERE
					t.forum_id = af.forum_id
					AND af.exclude_general = 0
					AND p.poster_id = u.user_id
					AND p.forum_id = f.forum_id
					AND t.topic_id = p.topic_id
					AND t.topic_last_post_id = p.post_id
				ORDER BY post_time DESC LIMIT 10";
				$result = mQuery($qry);

				while ($row = mysql_fetch_assoc($result)) {
					$last_post = getDateString($row[post_time]);
					$title = html_entity_decode($row[topic_title]);

					if ($row[topic_id] != '') {
						if ($row[topic_replies] > 0) {
							$comments = '<a href="'. BASE_URL .'/forum/viewtopic.php?t='. $row[topic_id] .'">Comments</a> ('. $row[topic_replies] .')';
						} else {
							$comments = '<a class="red" href="'. BASE_URL .'/forum/viewtopic.php?t='. $row[topic_id] .'">Post First Comment</a>';
						}
					}

					echo "<li>\n".
					'<h3><a href="'. FULL_URL_FORUM_VIEWTOPIC .'?t='. $row[topic_id] .'">'. $title ."</a></h3>\n".
					'<h4 style="float:right;"><img src="'. BASE_URL .'/images/icon_comments.gif" alt="" /> '. $comments ."</h4>\n".
					'<h4><span style="text-transform:none">By</span> '. $row[post_author] ." &bull; \n".
					'<span style="color:#800000"> '. $last_post ."</span> &bull; \n".
					"$row[forum_name]</h4></li>\n";
				}
			?></ul>
		<span class="corners-bottom"><span></span></span></div>

</div></body>
</html>