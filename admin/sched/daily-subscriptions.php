<?
	define('BASE_DIR', '/var/www/html');
	require_once(BASE_DIR .'/includes/constants.php');
	require_once(BASE_DIR .'/includes/lib_mysql.php');
	require_once(BASE_DIR .'/includes/lib_common.php');

	if (!CALLED_FROM_DAILY) {
		$debug = isset($_GET['debug']);
		if ($debug) header('Content-Type: text/plain');
	}

	$today = date('Y-m-d');

	// Send notifications of upcoming yearly expirations (7-day notice, 1-day notice, and expired yesterday)
	if ($debug) echo date('Y/m/d H:i:s') .": Send notifications of upcoming yearly expirations\n";
	$qry = "SELECT 7 as lead, id, email_address FROM user WHERE email_invalid < 3 AND membership_freq = 'Y' AND exp_pg = '$today' + INTERVAL 7 DAY
	UNION
	SELECT 1 as lead, id, email_address FROM user WHERE email_invalid < 3 AND membership_freq = 'Y' AND exp_pg = '$today' + INTERVAL 1 DAY
	UNION
	SELECT -1 as lead, id, email_address FROM user WHERE email_invalid < 3 AND membership_freq = 'Y' AND exp_pg = '$today' - INTERVAL 1 DAY";
	$result = mQuery($qry);
	while ($row = mysql_fetch_assoc($result)) {
		if ($row['lead'] > 1) {
			$subject = "Your membership will expire in $row[lead] days";
		} elseif ($row['lead'] == 1) {
			$subject = "Your membership will expire tomorrow";
		} else {
			$subject = "Your membership has expired";
		}

		$message = addslashes(file_get_contents(FULL_URL_ADMIN_EMAILS_SUB_EXP_TEXT .'?no_session&id='. $row['id'] .'&lead='. $row['lead']));

		$sql = "
		INSERT INTO email_queue
			(send_to, subject, message, content_type, unsub_code)
			VALUES ('$row[email_address]', '$subject', '$message', 'text/plain', '')";
		if ($debug) {echo "$sql\n";} else {mQuery($sql);}
	}

	// Revoke expired subscriptions, giving a one day grace
	if ($debug) echo date('Y/m/d H:i:s') .": Revoke expired subscriptions\n";
	$sql = "SELECT id, user_name
	FROM user
	WHERE exp_pg < '$today' - INTERVAL 1 DAY
		AND access & ". ACCESS_PREMIUM;
	$result = mQuery($sql);
	while ($row = mysql_fetch_assoc($result)) {
		mQuery("UPDATE user SET access = access - ". ACCESS_PREMIUM .", hide_banner_ads = 0 WHERE id = '$row[id]'");
	}

	// Remove subscriptions to emails for expired accounts
	if ($debug) echo date('Y/m/d H:i:s') .": Remove subscriptions for expired accounts\n";
	$qry = "UPDATE user SET subscriptions = subscriptions - ". SUB_TODAY ." WHERE subscriptions & ". SUB_TODAY ." AND NOT(access & ". ACCESS_PREMIUM .")";
	mQuery($qry);
/*
	$qry = "UPDATE user SET subscriptions = subscriptions - ". SUB_GUIDE_GRID ." WHERE subscriptions & ". SUB_GUIDE_GRID ." AND NOT(access & ". ACCESS_PREMIUM .")";
	mQuery($qry);
	$qry = "UPDATE user SET subscriptions = subscriptions - ". SUB_GUIDE_BRIEF ." WHERE subscriptions & ". SUB_GUIDE_BRIEF ." AND NOT(access & ". ACCESS_PREMIUM .")";
	mQuery($qry);
*/
?>
