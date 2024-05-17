<?
	require('../global.php');
	$debug = isset($_POST['debug']);
	if ($debug) header('Content-Type: text/plain');

	if (isset($_POST['id'])) { # Preferred method - using POST id
		$id = $_POST['id'];
	} elseif (isset($_GET['id'])) { # Secondary method - if they click a link from the new daily
		$id = $_GET['id'];
		if (!$user->data[''is_registered'']) { # To prevent search engine crawling, require user to be logged in to do the GET method, otherwise direct to story page
			$sql = "SELECT title FROM hdtv_rss WHERE id = '$id'";
			$result = mQuery($sql);
			$row = mysql_fetch_assoc($result);
			js_replace("/news/story.php?title=". dirify($row['title']) ."&id=$id");
		}
	} else {
		js_replace('/news/index.php');
	}

	$poster_id = '37906'; # This is the ID under which all news story threads will be started (Internet News) (old id: 35485)

	$sql = "
	SELECT link, title, pubDate, description, source, c.category_label, topic_id
	FROM hdtv_rss r, mt_category c
	WHERE id = '$id'
		AND r.category_id = c.category_id";
	if ($debug) {echo "$sql\n";}
	$result = mQuery($sql);
	$story = mysql_fetch_assoc($result);

	$ts = $story['pubDate'];
#	$y = date('Y', $ts);
#	$m = date('m', $ts);
#	$link = "/$entry['blog_dir']/$y/$m/". dirify($row['entry_title']) .".php";
	$link = '/news/story.php?title='. dirify($story['title']) .'&amp;id='. $id;

	# Check to see if "Category" forum exists. If not, create
	$sql = "SELECT forum_id FROM ". FORUMS_TABLE ." WHERE cat_id = 29 AND forum_name = '$story['category_label']'";
	$result = mQuery($sql);
	if ($debug) echo "$sql\n";
	if ($row = mysql_fetch_assoc($result)) {
		$forum_id = $row['forum_id'];
	} else {
		# Get next forum number
		$result = mQuery("SELECT MAX(forum_id) forum_id FROM ". FORUMS_TABLE);
		$row = mysql_fetch_assoc($result);
		$forum_id = $row['forum_id'] + 1;

		$sql = "
		INSERT INTO ". FORUMS_TABLE ."
		(forum_id, cat_id, forum_name, forum_order, forum_posts, forum_topics, auth_post, auth_reply, auth_edit, auth_delete, auth_sticky, auth_announce, auth_vote, auth_pollcreate)
		VALUES ($forum_id, 29, '$story['category_label']', 10, 0, 0, 1, 1, 1, 1, 3, 3, 1, 1)";
		if ($debug) {echo "$sql\n";} else {mQuery($sql);}
	}

	# Check to see if topic_id already exists. If so, redirect
	if ($story['topic_id'] !='') {
# Why did I do this? Makes no sense
#		js_replace("/news/story.php?title=". dirify($story['title']) ."&id=$story['topic_id']");
		js_replace("/forum/viewtopic.php?t=$story['topic_id']");
		exit;
	}

	### OLD INSERT POST ###
/*
	# Get username
	$sql = "SELECT username FROM ". USERS_TABLE ." WHERE user_id = $poster_id";
	if ($debug) {echo "$sql\n";}
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$post_username = $row['username'];

	# Add Topic
	$post_subject = addslashes($story['title']);
	$sql = "INSERT INTO ". TOPICS_TABLE ." (topic_title, topic_poster, topic_time, forum_id, topic_status, topic_type, topic_vote)
		VALUES ('$post_subject', $poster_id, $ts, $forum_id, 0, 0, 0)";
	if ($debug) {echo "$sql\n";} else {mQuery($sql);}
	$topic_id = mysql_insert_id();

	# Add Post
	$sql = "
	INSERT INTO ". POSTS_TABLE ." (topic_id, forum_id, poster_id, post_username, post_time, poster_ip, enable_bbcode, enable_html, enable_smilies, enable_sig)
		VALUES ($topic_id, $forum_id, $poster_id, '$post_username', $ts, '', 0, 1, 0, 0)";
	if ($debug) {echo "$sql\n";} else {mQuery($sql);}
	$post_id = mysql_insert_id();

	# Add Post Text
	$post_message = addslashes($story['description']) .'<br><br><a href="'. $link .'">Read Story</a><br>';
	$sql = "INSERT INTO phpbb_posts_text (post_id, post_subject, bbcode_uid, post_text)
		VALUES ($post_id, '$post_subject', '', '$post_message')";
	if ($debug) {echo "$sql\n";} else {mQuery($sql);}

	# Update phpbb_topics
	$sql = "UPDATE phpbb_topics SET topic_first_post_id = $post_id, topic_last_post_id = $post_id WHERE topic_id = $topic_id";
	if ($debug) {echo "$sql\n";} else {mQuery($sql);}

	# Update phpbb_forums
	$sql = "
	UPDATE phpbb_forums SET
		forum_posts = forum_posts + 1,
		forum_topics = forum_topics + 1,
		forum_last_post_id = $post_id
	WHERE forum_id = $forum_id";
	if ($debug) {echo "$sql\n";} else {mQuery($sql);}

	# Update phpbb_users
	$sql = "UPDATE phpbb_users SET user_posts = user_posts + 1 WHERE user_id = $poster_id";
	if ($debug) {echo "$sql\n";} else {mQuery($sql);}
*/

//	$forum_id = $entry['response_forum'];
	$post_subject = addslashes($story['title']);
	$post_message = addslashes($story['description']) ."\n\n[url=$link]Read Story[/url]";
//	$poster_id = $row['user_id'];

	$topic_id = create_topic($forum_id, $post_subject, $post_message, $poster_id, $poster_ip, true);

	# Update hdtv_rss with topic_id
	$sql = "UPDATE hdtv_rss SET topic_id = $topic_id WHERE id = $id";
	if ($debug) {echo "$sql\n";} else {mQuery($sql);}

	# Redirect to new Topic for posting
	js_replace("/forum/viewtopic.php?t=$topic_id");
?>