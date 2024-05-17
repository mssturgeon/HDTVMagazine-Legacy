<?
	define('BASE_DIR', '/var/www/html');
	require(BASE_DIR .'/includes/constants.php');
	require(BASE_DIR .'/includes/lib_mysql.php');
	require(BASE_DIR .'/includes/lib_common.php');

	$emailed = isset($_GET['emailed']);
	$ids = array();
	$today = date('Y-m-d');

	// Get number of new topics in the "watched" forums
	$qry = "
	SELECT topic_title, t.topic_id, forum_name, username, post_time, post_id, topic_replies
	FROM phpbb3_topics t, phpbb3_forums f, phpbb3_users u, phpbb3_posts p, aux_phpbb_forums af
	WHERE t.forum_id = f.forum_id
		AND t.forum_id = af.forum_id
		AND af.exclude_general = 0
		AND t.topic_poster = u.user_id
		AND t.topic_id = p.topic_id
		AND t.topic_first_post_id = p.post_id
		AND FROM_UNIXTIME(topic_time, '%Y-%m-%d') >= '$today' - INTERVAL 1 DAY
	ORDER BY post_time";
	$new_topics = mQuery($qry);
	while ($row = mysql_fetch_assoc($new_topics)) $ids[] = $row[post_id];

	// Get number of new responses in the "watched" forums
	$qry = "
	SELECT topic_title, t.topic_id, forum_name, u1.username as topic_author, u2.username as post_author, post_time, post_id, topic_replies
	FROM phpbb3_topics t, phpbb3_forums f, phpbb3_users u1, phpbb3_users u2, phpbb3_posts p, aux_phpbb_forums af
	WHERE t.forum_id = f.forum_id
		AND t.forum_id = af.forum_id
		AND af.exclude_general = 0
		AND t.topic_poster = u1.user_id
		AND p.poster_id = u2.user_id
		AND t.topic_id = p.topic_id
		AND t.topic_last_post_id = p.post_id
		AND FROM_UNIXTIME(post_time, '%Y-%m-%d') >= '$today' - INTERVAL 1 DAY
		AND post_id NOT IN ('". implode("','", $ids) ."')
	ORDER BY post_time";
	$new_responses = mQuery($qry);

	$num_topics = mysql_num_rows($new_topics);
	$num_responses = mysql_num_rows($new_responses);
	if ($num_topics + $num_responses > 0) {
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>HDTV Magazine - Daily Forum Update (<?=date('m/d/Y')?>)</title>
	<style>
		<?include(BASE_DIR .'/stylesheets/email_css.php')?>
	</style>
</head>
<body><div id="email_container">
	<?
		if (!$emailed) {include(BASE_DIR .'/ads/leaderboard.php');}
		include(BASE_DIR .'/includes/email_header.php');
		include(BASE_DIR .'/ads/banner_email.php');
	?>
	<div class="item_odd"><span class="corners-top"><span></span></span>
		There has been activity in the <a href="<?=$base_url?>/forum/index.php">HDTV Magazine Forums</a>:<br />
		New topics: <?=$num_topics?><br />
		New responses: <?=$num_responses?><br />
	<span class="corners-bottom"><span></span></span></div>

	<div class="item"><span class="corners-top"><span></span></span><?
		if ($num_topics > 0) {
			echo "<h2>New Topics</h2><ul>";
			mysql_data_seek($new_topics, 0);
			while ($row = mysql_fetch_assoc($new_topics)) {
				$last_post = date('n/j g:ia T', $row['post_time']);
				$title = html_entity_decode($row['topic_title']);

				if ($row['topic_id'] != '') {
					if ($row['topic_replies'] > 0) {
						$comments = '<a href="'. $base_url .'/forum/viewtopic.php?t='. $row['topic_id'] .'">Comments</a> ('. $row['topic_replies'] .')';
					} else {
						$comments = '<a class="red" href="'. $base_url .'/forum/viewtopic.php?t='. $row['topic_id'] .'">Post First Comment</a>';
					}
				}

				echo '<li>'.
				'<h3><a href="'. $base_url .'/forum/viewtopic.php?t='. $row['topic_id'] .'">'. $title .'</a></h3>'.
				'<h4 style="clear:right; float:right;"><img src="'. $base_url .'/images/icon_comments.gif" alt="" /> '. $comments .'</h4>'.
				'<h4>'.
					'<span style="text-transform:none">By</span> '. $row['username'] .' &bull; '.
					'<span style="color:#800000"> '. $last_post .'</span> &bull; '. $row['forum_name'] .
				"</h4></li>\n";
			}
		}
	?></ul><span class="corners-bottom"><span></span></span></div>
	<div class="item"><span class="corners-top"><span></span></span><?
		if ($num_responses > 0) {
			echo "<h2>New Responses</h2><ul>";
			while ($row = mysql_fetch_assoc($new_responses)) {
				if (!in_array($row['post_id'], $ids)) {
					$last_post = date('n/j g:ia T', $row['post_time']);
					$title = html_entity_decode($row['topic_title']);

					if ($row['topic_id'] != '') {
						if ($row['topic_replies'] > 0) {
							$comments = '<a href="'. $base_url .'/forum/viewtopic.php?t='. $row['topic_id'] .'">Comments</a> ('. $row['topic_replies'] .')';
						} else {
							$comments = '<a class="red" href="'. $base_url .'/forum/viewtopic.php?t='. $row['topic_id'] .'">Post First Comment</a>';
						}
					}

					echo '<li>'.
					'<h3><a href="'. $base_url .'/forum/viewtopic.php?t='. $row['topic_id'] .'">'. $title .'</a></h3>'.
					'<h4 style="float:right;"><img src="'. $base_url .'/images/icon_comments.gif" alt="" /> '. $comments .'</h4>'.
					'<h4>'.
						'<span style="text-transform:none">By</span> '. $row['post_author'] .' &bull; '.
						'<span style="color:#800000"> '. $last_post .'</span>'.
					'</h4>'.
					'<h4>'.
						'<span style="text-transform:none">Started by</span> '. $row['topic_author'] .
						'<span style="text-transform:none"> in forum</span> '. $row['forum_name'] .
					"</h4></li>\n";
				}
			}
		}
	?></ul><span class="corners-bottom"><span></span></span></div>
	<br />
	Enjoy,<br />
	<br />
	-- Dale &amp; Shane<br />
	<a href="http://www.hdtvmagazine.com/">HDTV Magazine</a>
</div></body>
</html>
<?}?>
