<?
	$debug = isset($_GET['debug']);
	if ($debug) header('Content-Type: text/plain');

	if (IN_DAILY !== true) {
		set_time_limit(0);
		define('BASE_DIR', '/var/www/html');
		require_once(BASE_DIR .'/includes/constants.php');
		require_once(BASE_DIR .'/includes/lib_mysql.php');
		require_once(BASE_DIR .'/includes/lib_common.php');
		require_once(BASE_DIR .'/includes/lib_admin.php');

		define('IN_PHPBB', true);
		$phpbb_root_path = BASE_DIR .'/forum/';
		$phpEx = substr(strrchr(__FILE__, '.'), 1);
		include_once($phpbb_root_path . 'common.' . $phpEx);
	}

	# phpbb_users -> user
	$sql = "SELECT user_id, username, user_email, user_password
	FROM ". USERS_TABLE ." WHERE user_id > 0 AND user_inactive_reason = 0";
	$res_bb = mQuery($sql);
	while ($row_bb = mysql_fetch_assoc($res_bb)) {
		# The following query determines both whether a user exists and get's their password for the xcart query if they do
		$res_user = mQuery("SELECT password FROM user WHERE bb_id = $row_bb[user_id]");
		if (mysql_num_rows($res_user) == 0) {
			$sql = "INSERT INTO user (bb_id, user_name, email_address, password, subscriptions)".
			" VALUES ($row_bb[user_id], '$row_bb[username]', '$row_bb[user_email]', '$row_bb[user_password]', ". SUB_DEFAULTS_BASIC .")";
			if ($debug) {echo "$sql\n";} else {mQuery($sql);}
		}
	}

	### No need to work the other way since registrations are disabled via the forum software.
	if ($debug) echo "\n";
?>
