<?
	header('Content-Type: text/plain');

	require('../global.php');
	if (!access(ACCESS_ADMIN)) prompt_login(PHP_SELF);

	$debug = isset($_GET['debug']);

	$exclude = '1,64,93,96,111,115,134,139,140';

/*
	# Update user post counts
	$sql = "SELECT poster_id, COUNT(*) posts FROM phpbb_posts WHERE poster_id > 0 AND forum_id NOT IN ($exclude) GROUP BY poster_id";
	echo "$sql\n";
	$result = mQuery($sql);
	while ($row = mysql_fetch_assoc($result)) {
		if ($row[posts] > 0) {
			$sql = "UPDATE phpbb_users SET user_posts = $row[posts] WHERE user_id = $row[poster_id]";
			if ($debug) {echo "$sql\n";} else {mQuery($sql);}
		}
	}

	# Update forum post counts
	$sql = "SELECT forum_id, COUNT(*) posts FROM phpbb_posts GROUP BY forum_id";
	echo "$sql\n";
	$result = mQuery($sql);
	while ($row = mysql_fetch_assoc($result)) {
		if ($row[posts] > 0) {
			$sql = "UPDATE phpbb_forums SET forum_posts = $row[posts] WHERE forum_id = $row[forum_id]";
			if ($debug) {echo "$sql\n";} else {mQuery($sql);}
		}
	}

	# Update forum topic counts
	$sql = "SELECT forum_id, COUNT(*) topics FROM phpbb_topics GROUP BY forum_id";
	echo "$sql\n";
	$result = mQuery($sql);
	while ($row = mysql_fetch_assoc($result)) {
		if ($row[topics] > 0) {
			$sql = "UPDATE phpbb_forums SET forum_topics = $row[topics] WHERE forum_id = $row[forum_id]";
			if ($debug) {echo "$sql\n";} else {mQuery($sql);}
		}
	}

	# Update topic post counts
	$sql = "SELECT topic_id, COUNT(*) posts FROM phpbb_posts GROUP BY topic_id";
	echo "$sql\n";
	$result = mQuery($sql);
	while ($row = mysql_fetch_assoc($result)) {
		if ($row[posts] > 0) {
			$sql = "UPDATE phpbb_topics SET topic_replies = $row[posts] - 1 WHERE topic_id = $row[topic_id]";
			if ($debug) {echo "$sql\n";} else {mQuery($sql);}
		}
	}
*/

	# Update last post id
	$sql = "SELECT forum_id FROM phpbb_forums";
	echo "$sql\n";
	$result = mQuery($sql);
	while ($row = mysql_fetch_assoc($result)) {
		$sql = "SELECT topic_last_post_id FROM phpbb_topics WHERE forum_id = $row[forum_id] ORDER BY topic_time DESC LIMIT 1";
		echo "$sql\n";
		$result2 = mQuery($sql);
		while ($row2 = mysql_fetch_assoc($result2)) {
			$sql = "UPDATE phpbb_forums SET forum_last_post_id = $row2[topic_last_post_id] WHERE forum_id = $row[forum_id]";
			if ($debug) {echo "$sql\n";} else {mQuery($sql);}
		}
	}

?>
