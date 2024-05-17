<?
	# This appears to update the topic poster with the user id of the "first_post" user id
	header('Content-Type: text/plain');

	require('../global.php');
	if (!access(ACCESS_ADMIN)) prompt_login(PHP_SELF);

	$sql = "
		SELECT t.topic_id, topic_poster, post_id, poster_id FROM phpbb_topics t, phpbb_posts p WHERE t.topic_first_post_id = p.post_id";
	$result = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result)) {
/*		echo "topic_id: $row[topic_id]\n";
		echo "topic_poster: $row[topic_poster]\n";
		echo "post_id: $row[post_id]\n";
		echo "poster_id: $row[poster_id]\n";
*/
		$sql = "UPDATE phpbb_topics SET topic_poster = $row[poster_id] WHERE topic_id = $row[topic_id]";
		$db->sql_query($sql);
		echo "$sql\n";
	}
?>
