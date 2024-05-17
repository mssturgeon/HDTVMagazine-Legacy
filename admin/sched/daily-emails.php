<?
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

	$debug = isset($_GET[debug]);
	if ($debug) header('Content-Type: text/plain');

 	$today = date('m/d/Y');

	echo date('Y/m/d H:i:s') .": START - Sending Daily Emails\n";

	### HDTV Magazine Daily
	echo date('Y/m/d H:i:s') .": START - Sending HDTV Magazine Daily\n";

	# Do not email if "get_contents" failed
	$attempt = 0;
	$message = '';
	while (strlen($message) <= 1 && $attempt < 3) {
		$attempt++;
		if ($attempt > 1) sleep(10); # Delay successive fetches
		$message = file_get_contents(BASE_URL .'/daily.php?emailed');
	}

	if ($attempt < 3) {
		$subject = 'HDTV Magazine Daily - '. $today;
		$sql = "
		SELECT id, first_name, last_name, user_name, email_address
		FROM user u, ". USERS_TABLE ." pu
		WHERE u.bb_id = pu.user_id
			AND pu.user_inactive_reason = 0
			AND email_invalid < 3
			AND email_spam = 0
			AND subscriptions & ". SUB_DAILY;
		$result = mQuery($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$unsub_code = $row['id'] .':'. SUB_DAILY;

			$name = $row['first_name'] .' '. $row['last_name'];
			if (trim($name) == '') {
				$to = $row['user_name'] .' <'. $row['email_address'] .'>';
			} else {
				$to = $name .' <'. $row['email_address'] .'>';
			}

			$sql = "
			INSERT INTO email_queue
				(send_to, subject, message, content_type, unsub_code)
				VALUES ('". addslashes($to) ."', '". addslashes($subject) ."', '". addslashes($message) ."', 'text/html', '$unsub_code')";
			if ($debug) {echo "$sql\n";} else {mQuery($sql);}
		}
	} else {
		echo date('Y/m/d H:i:s') .": FAILED - Sending HDTV Magazine Daily\n";
	}
	echo date('Y/m/d H:i:s') .": END - Sending HDTV Magazine Daily\n";

	### Forum Update
	echo date('Y/m/d H:i:s') .": START - Sending Daily Forum Update\n";

	# create a boundary string. It must be unique so we use the MD5 algorithm to generate a random hash
	$boundary = "PHP-alt-". md5(date('r', time()));

	# Do not email if "get_contents" failed
	$attempt = 0;
	$orig_message = '';
	while (strlen($orig_message) <= 1 && $attempt < 3) {
		$attempt++;
		if ($attempt > 1) sleep(10); # Delay successive fetches
		$orig_message = file_get_contents("http://www.hdtvmagazine.com/admin/emails/forum-update-multipart.php?emailed&boundary=". $boundary);
	}

	if ($attempt < 3) {
		$subject = 'Daily Forum Update - '. date('m/d/Y');
		$sql = "
		SELECT id, first_name, last_name, user_name, email_address
		FROM user u, ". USERS_TABLE ." pu
		WHERE u.bb_id = pu.user_id
			AND pu.user_inactive_reason = 0
			AND email_invalid < 3
			AND email_spam = 0
			AND subscriptions & ". SUB_FORUM_UPDATE;
		$result = mQuery($sql);
		while ($row = mysql_fetch_assoc($result)) {
			# Replace unsub_code and email_address
			$message = str_replace('[[email_address]]', $row['email_address'], $orig_message);
			$message = str_replace('[[unsub_code]]', urlencode(base64_encode($row['id'] .':'. SUB_FORUM_UPDATE)), $message);
			$message = str_replace('[[list_name]]', $SUB[SUB_FORUM_UPDATE], $message);

			$name = $row['first_name'] .' '. $row['last_name'];
			if (trim($name) == '') {
				$to = $row['user_name'] .' <'. $row['email_address'] .'>';
			} else {
				$to = $name .' <'. $row['email_address'] .'>';
			}

			$sql = "INSERT INTO email_queue (send_to, subject, message, content_type)
			VALUES ('". addslashes($to) ."', '". addslashes($subject) ."', '". addslashes($message) .")', 'multipart/alternative; boundary=\"$boundary\"')";
			if ($debug) {echo "$sql\n";} else {mQuery($sql);}
		}
	} else {
		echo date('Y/m/d H:i:s') .": FAILED - Sending Daily Forum Update\n";
	}

	echo date('Y/m/d H:i:s') .": END - Sending Daily Forum Update\n";

	echo date('Y/m/d H:i:s') .": END - Sending Daily Emails\n";
?>
