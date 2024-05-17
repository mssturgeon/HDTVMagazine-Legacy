<?
	set_time_limit(0);
	$debug = isset($_GET['debug']);
	if ($debug) header('Content-Type: text/plain');

	# Include necessary libraries for automated scripts
	define('BASE_DIR', '/var/www/html');
	require(BASE_DIR .'/includes/constants.php');
	require(BASE_DIR .'/includes/lib_common.php');
	require(BASE_DIR .'/includes/lib_mysql.php');
	require(BASE_DIR .'/includes/lib_admin.php');

	# Include phpbb library for table constants
	define('IN_PHPBB', true);
	$phpbb_root_path = BASE_DIR .'/forum/';
	$phpEx = substr(strrchr(__FILE__, '.'), 1);
	include_once($phpbb_root_path . 'common.' . $phpEx);

	# Load admindata
	$result = mQuery("SELECT * FROM admin_settings WHERE id = 1");
	$admindata = mysql_fetch_assoc($result);

 	$today = date('m/d/Y');

### HDTV Magazine Weekly

	# Do not email if "get_contents" failed
	$attempt = 0;
	$message = '';
	while (strlen($message) <= 1 && $attempt < 3) {
		$attempt++;
		if ($attempt > 1) sleep(10); # Delay successive fetches
		$message = file_get_contents(BASE_URL .'/weekly.php?emailed');
	}

	if ($attempt < 3) {
		$subject = 'HDTV Magazine Weekly - '. $today;
		$sql = "
		SELECT id, email_address, email_preference
		FROM user u, ". USERS_TABLE ." pu
		WHERE u.bb_id = pu.user_id
			AND pu.user_inactive_reason = 0
			AND email_invalid < 3
			AND email_spam = 0
			AND subscriptions & ". SUB_WEEKLY;
		$result = mQuery($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$unsub_code = $row['id'] .':'. SUB_WEEKLY;

			$sql = "
			INSERT INTO email_queue
				(send_to, subject, message, content_type, unsub_code)
				VALUES ('$row[email_address]', '". addslashes($subject) ."', '". addslashes($message) ."', 'text/html', '$unsub_code')";
			if ($debug) {echo "$sql\n";} else {mQuery($sql);}
		}

		# Send Tweet
		$url = BASE_URL .'/weekly.php';
		$tweet_text = html_entity_decode($subject, ENT_QUOTES, 'UTF-8');
		tweet_status($tweet_text, $url, $admindata['twitter_username'], $admindata['twitter_password']); # Sending notice to HDTVMagazine status
	}
?>
