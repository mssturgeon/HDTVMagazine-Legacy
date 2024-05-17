<?
	require('../global.php');
	include_once($phpbb_root_path.'includes/bbcode.'.$phpEx);

	if (!access(ACCESS_ADMIN)) prompt_login(PHP_SELF);

	header('Content-Type: text/plain');
	$debug = isset($_GET['debug']);

//	$search[0] = '<br><br><a href="';
//	$search[1] = '">Read ';
//	$search[2] = '</a><br>';
	$search[0] = '\n\n[url=http://www.hdtvmagazine.com';
//	$replace[0] = '\n\n[url=http://www.hdtvmagazine.com';
//	$replace[1] = ']Read ';
//	$replace[2] = '[/url]';
	$replace[0] = "\n\n[url=http://www.hdtvmagazine.com";

	$sql = "SELECT post_id, bbcode_uid, post_text FROM phpbb_posts_text ORDER BY post_id DESC";
	$result = mQuery($sql);
	while ($row = mysql_fetch_assoc($result)) {
		if (stripos($row['post_text'], $search[0]) !== false) {
			$uid = $row['bbcode_uid'];

//			echo "Post Before:\n{$row['post_text']}\n\n";
			$new_post = addslashes(str_replace($search, $replace, $row['post_text']));
//			echo "Post After:\n$new_post\n\n";

			// Need to make sure it has a bbcode_uid
			if ($uid == '') $uid = make_bbcode_uid();

			$sql = "UPDATE phpbb_posts_text SET bbcode_uid = '$uid', post_text = '$new_post' WHERE post_id = $row[post_id]";
			if ($debug) {echo "$sql\n";} else {mQuery($sql);}
//			echo "$sql\n";
//			exit;
		}
	}
?>