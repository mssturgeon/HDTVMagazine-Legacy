<?
	header('Content-Type: text/plain');

	require_once('/var/www/html/includes/constants.php');
	require_once('/var/www/html/includes/lib_common.php');
	require_once('/var/www/html/includes/lib_mysql.php');

	$ids = array();
	$today = date('Y-m-d');

	// Get number of new topics in the "watched" forums
	$qry = "
	SELECT topic_title, t.topic_id, forum_name, username, post_time, post_id
	FROM phpbb_topics t, phpbb_forums f, phpbb_users u, phpbb_posts p, aux_phpbb_forums af
	WHERE t.forum_id = f.forum_id
		AND f.forum_id = af.forum_id
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
	SELECT topic_title, t.topic_id, forum_name, u1.username as topic_author, u2.username as post_author, post_time, post_id
	FROM phpbb_topics t, phpbb_forums f, phpbb_users u1, phpbb_users u2, phpbb_posts p, aux_phpbb_forums af
	WHERE t.forum_id = f.forum_id
		AND f.forum_id = af.forum_id
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
		echo "There has been activity in the HDTV Magazine Forums:\n".
		"New topics: $num_topics\n".
		"New responses: $num_responses\n\n";

		if ($num_topics > 0) {
			echo "The following topics are new:\n";
			mysql_data_seek($new_topics, 0);
			while ($row = mysql_fetch_assoc($new_topics)) {
				$last_post = date('n/j g:ia T', $row[post_time]);
				$title = html_entity_decode($row[topic_title]);

				echo "************\n".
				"$title\n".
				FULL_URL_FORUM_VIEWTOPIC ."?t={$row[topic_id]}\n".
				"In forum: {$row[forum_name]}\n".
				"Started by: {$row[username]}\n".
				"Last post: $last_post\n\n";
			}
			echo "\n";
		}

		if ($num_responses > 0) {
			echo "The following responses are new:\n";
			while ($row = mysql_fetch_assoc($new_responses)) {
				if (!in_array($row[post_id], $ids)) {
					$last_post = date('n/j g:ia T', $row[post_time]);
					$title = html_entity_decode($row[topic_title]);

					echo "************\n".
					"$title\n".
					FULL_URL_FORUM_VIEWTOPIC ."?t={$row[topic_id]}\n".
					"In forum: {$row[forum_name]}\n".
					"Started by: {$row[topic_author]}\n".
					"Response by $row[post_author] on $last_post\n";
				}
			}
			echo "\n";
		}
	}
?>
