<?
	if (IN_DAILY !== true) {
		set_time_limit(0);
		define('BASE_DIR', '/var/www/html');
		require_once(BASE_DIR .'/includes/constants.php');
		require_once(BASE_DIR .'/includes/lib_mysql.php');
		require_once(BASE_DIR .'/includes/lib_common.php');

		define('IN_PHPBB', true);
		$phpbb_root_path = BASE_DIR .'/forum/';
		$phpEx = substr(strrchr(__FILE__, '.'), 1);
		include_once($phpbb_root_path . 'common.' . $phpEx);
	}

	$sql = "SELECT post_id, post_text FROM ". POSTS_TABLE ." WHERE post_text like '%hdtvmagazine-20%' OR post_text like '%hdlibrarandfo-20%'";
	$result = mQuery($sql);
	while ($row = mysql_fetch_assoc($result)) {
		$subject = $row['post_text'];
		$subject = str_replace('hdtvmagazine-20', 'hdtvforum0c-20', $subject);
		$subject = str_replace('hdlibrarandfo-20', 'hdtvforum0c-20', $subject);
		$subject = addslashes($subject);
		$sql_update = "UPDATE ". POSTS_TABLE ." SET post_text = '$subject' WHERE post_id = '{$row['post_id']}'";
		if ($debug) {echo "$sql_update\n";} else {mQuery($sql_update);}
	}
?>