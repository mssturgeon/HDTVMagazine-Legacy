<?
	if (!in_array('continuous.php', get_included_files())) { // Only initialize these items if we're calling directly (vs including from parent)
		$debug = isset($_GET['debug']);
		if ($debug) header('Content-Type: text/plain');

		# Include necessary libraries for automated scripts
		define('BASE_DIR', '/var/www/html');
		require_once(BASE_DIR .'/includes/constants.php');
		require_once(BASE_DIR .'/includes/lib_common.php');
		require_once(BASE_DIR .'/includes/lib_mysql.php');
		require_once(BASE_DIR .'/includes/lib_admin.php'); // Tweet libraries

		# Include phpbb library for table constants
		define('IN_PHPBB', true);
		$phpbb_root_path = BASE_DIR .'/forum/';
		$phpEx = substr(strrchr(__FILE__, '.'), 1);
		include_once($phpbb_root_path . 'common.' . $phpEx);

		# Load admindata
		$result = mQuery("SELECT * FROM admin_settings WHERE id = 1");
		$admindata = mysql_fetch_assoc($result);
	}

#	require_once(BASE_DIR .'/includes/twitteroauth/twitteroauth.php');

	# Create entries in the aux table
	$sql = "INSERT IGNORE INTO aux_phpbb_posts (post_id, notification_sent) SELECT post_id, 0 FROM ". POSTS_TABLE;
	if ($debug) {echo "$sql\n";} else {mQuery($sql);}
	mQuery($sql);

	# Get number of new responses in the "watched" forums
	$sql = "
	SELECT topic_title, post_subject, t.topic_id, forum_name, username, user_posts, post_time, p.post_id, post_text
	FROM ". TOPICS_TABLE ." t, ". FORUMS_TABLE ." f, ". USERS_TABLE ." u, ". POSTS_TABLE ." p, aux_phpbb_posts a, aux_phpbb_forums af
	WHERE p.post_id = a.post_id
		AND p.topic_id = t.topic_id
		AND t.forum_id = f.forum_id
		AND p.poster_id = u.user_id
		AND t.forum_id = af.forum_id
		AND af.exclude_from_notify = 0
		AND a.notification_sent = 0
	ORDER BY post_time";
	if ($debug) echo "$sql\n";
	$new_topics = mQuery($sql);

	while ($row = mysql_fetch_assoc($new_topics)) {
#		$title = html_entity_decode($row['topic_title']);
#		$title = htmlentities($row['topic_title'], ENT_COMPAT, 'UTF-8');
#		$title = htmlentities($row['topic_title']);
#		$title = utf8_decode($row['topic_title']);
#		$title = $row['topic_title'];

		# Need to do this until we fix the sync engine
		$title = addslashes(stripslashes($row['topic_title']));

#		$post_text = utf8_decode($row['post_text']);

		# Need to do this until we fix the sync engine
#		$post_text = $row['post_text'];
		$post_text = addslashes(stripslashes($row['post_text']));

		$username = utf8_decode($row['username']);
		$url = FULL_URL_FORUM_VIEWTOPIC .'?p='. $row['post_id'];
		$message = "$title\n".
		"$url\n".
		"Forum: $row[forum_name]\n".
		"Posts: $row[user_posts]\n".
		"Started by: $username\n\n".
		"$post_text";

		# Send to me
		$sql = "INSERT INTO email_queue
			(send_to, subject, message, content_type, unsub_code)
			VALUES ('shane@hdtvmagazine.com', 'New Thread: ". substr($title, 0, 255) ."', '". $message ."', 'text/plain', '')";
#			VALUES ('shane@hdtvmagazine.com', 'New Thread: ". substr(addslashes($row['topic_title']), 0, 255) ."', '". addslashes($message) ."', 'text/plain', '')";
		if ($debug) {echo "$sql\n";} else {mQuery($sql);}

		# Send Tweets
		if ($row['user_posts'] <=5) {
			$short_message = substr($title, 0, 20) ."\n".
			"F: $row[forum_name]\n".
			"P: $row[user_posts]\n".
			"B: $row[username]";
##			tweet_dm('shanesturgeon', $short_message, $admindata['twitter_username'], $admindata['twitter_password']);
			$response = tweet_dm('shanesturgeon', $short_message);
			if ($debug) print_r($response);
		}

		# Send to Richard
/*
		$sql = "
		INSERT INTO email_queue
			(send_to, subject, message, content_type, unsub_code)
			VALUES ('richard@hdtvmagazine.com', 'New Thread: ". addslashes($row['topic_title']) ."', '". addslashes($message) ."', 'text/plain', '')";
		if ($debug) {echo "$sql\n";} else {mQuery($sql);}
*/

		# Update aux table to indicate notification sent
		$sql = "UPDATE aux_phpbb_posts SET notification_sent = 1 WHERE post_id = $row[post_id]";
		if ($debug) {echo "$sql\n";} else {mQuery($sql);}
	}
?>
