<?
	require('../global.php');
	require_once(BASE_DIR .'/includes/lib_forum.php');
	include_once($phpbb_root_path.'includes/bbcode.'.$phpEx);
	include_once($phpbb_root_path.'includes/functions_post.'.$phpEx);
	if (!access(ACCESS_ADMIN_ANY)) prompt_login(PHP_SELF);

	$debug = isset($_GET['debug']);
	header('Content-Type: text/plain');

	function insert_post($forum_id, $poster_id, $post_subject, $post_message, $watch_topic) {
		global $debug;

		if ($poster_id == '') {
			echo "Unknown poster_id ($poster_id)";
			exit;
		}

		# Get username
		$sql = "SELECT username FROM phpbb_users WHERE user_id = $poster_id";
		if ($debug) {echo "$sql\n";}
		$user_result = mQuery($sql);
		$user_row = mysql_fetch_assoc($user_result);
		$post_username = $user_row['username'];

		# Add Topic
		$sql = "INSERT INTO phpbb_topics (topic_title, topic_poster, topic_time, forum_id, topic_status, topic_type, topic_vote)
			VALUES ('$post_subject', $poster_id, UNIX_TIMESTAMP(), $forum_id, 0, 0, 0)";
		if ($debug) {echo "$sql\n";} else {mQuery($sql);}
		$topic_id = mysql_insert_id();

		if ($watch_topic) { # Watch topic
			$sql = "INSERT INTO phpbb_topics_watch (topic_id, user_id) VALUES ($topic_id, $poster_id)";
			if ($debug) {echo "$sql\n";} else {mQuery($sql);}
		}

		# Add Post
		$sql = "INSERT INTO phpbb_posts (topic_id, forum_id, poster_id, post_username, post_time, poster_ip, enable_bbcode, enable_html, enable_smilies, enable_sig)
			VALUES ($topic_id, $forum_id, $poster_id, '$post_username', UNIX_TIMESTAMP(), '$user_ip', 0, 1, 0, 0)";
		if ($debug) {echo "$sql\n";} else {mQuery($sql);}
		$post_id = mysql_insert_id();

		# Add Post Text
/*
		$uid = $bitfield = $flags = ''; // will be modified by generate_text_for_storage
		$allow_smilies = $allow_urls = $allow_bbcode = true; // false is default for generate_text_for_storage function
		generate_text_for_storage($post_message, $uid, $bitfield, $flags, $allow_bbcode, $allow_urls, $allow_smilies);
		if ($debug) {echo "\n$post_message\n";}
*/

		$uid = make_bbcode_uid();
		if ($debug) {echo "\n$uid\n";}

//		$post_message = prepare_message($post_message, true, true, true, $uid);
//		if ($debug) {echo "\n$post_message\n\n";}

//		$post_message = bbencode_first_pass($post_message, $uid);
//		if ($debug) {echo "\n$post_message\n\n";}

//		$post_message = bbencode_second_pass($post_message, $uid);
//		if ($debug) {echo "\n$post_message\n\n";}
//exit;

		$sql = "INSERT INTO phpbb_posts_text (post_id, post_subject, bbcode_uid, post_text)
			VALUES ($post_id, '$post_subject', '$uid', '$post_message')";
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
	}

	$id = isset($_GET['id']) ? $_GET['id'] : exit;

	$sql = "SELECT e.entry_id, entry_blog_id, entry_title, entry_excerpt, entry_created_on, entry_author_id, author_name, user_id, image_src, ASIN
	FROM mt_entry e, aux_mt_entry a, mt_author auth, aux_author aa
	WHERE e.entry_id = a.entry_id
		AND e.entry_author_id = auth.author_id
		AND e.entry_author_id = aa.author_id
		AND entry_status = 2
		AND entry_excerpt <> ''
		AND e.entry_id = $id";
	if ($debug) {echo "$sql\n";}
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);

	$entry = getEntryInfo($row['entry_blog_id']);
	$ts = strtotime($row['entry_created_on']);
	$y = date('Y', $ts);
	$m = date('m', $ts);
	$link = BASE_URL ."/$entry[blog_dir]/$y/$m/". dirify($row['entry_title']) .".php";

	$poster_id = $row['user_id'];
	$post_subject = addslashes($row['entry_title']);
	$forum_id = 64; // Test Forum
	$post_message = addslashes($row['entry_excerpt']) ."\n\n".'[url='. $link .']Read '. $entry['read_text'] .'[/url]';
	$watch_topic = false;

//	$sql = 'INSERT INTO ' . YOUR_TABLE . " (text, bbcode_uid, bbcode_bitfield) VALUES ('$text', '$uid', '$bitfield')";

//	insert_post($forum_id, $poster_id, $post_subject, $post_message, $watch_topic);
	$topic_id = create_topic($forum_id, $post_subject, $post_message, $poster_id, $poster_ip, $watch_topic)
?>