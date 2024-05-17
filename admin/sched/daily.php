<?
	set_time_limit(0);
	define('IN_DAILY', true);

	$debug = isset($_GET['debug']);
	if ($debug) header('Content-Type: text/plain');

	# Include necessary libraries for automated scripts
	define('BASE_DIR', '/var/www/html');
	require(BASE_DIR .'/includes/constants.php');
	require(BASE_DIR .'/includes/lib_common.php');
	require(BASE_DIR .'/includes/lib_mysql.php');
	require(BASE_DIR .'/includes/lib_admin.php');
	$script = '/admin/sched/daily.php';

	# Include phpbb library for table constants
	define('IN_PHPBB', true);
	$phpbb_root_path = BASE_DIR .'/forum/';
	$phpEx = substr(strrchr(__FILE__, '.'), 1);
	include_once($phpbb_root_path . 'common.' . $phpEx);


	mQuery("INSERT INTO log VALUES (NOW(), '". $script ."', 'START', ". LOG_TYPE_START .")");
	if ($debug) echo date('Y/m/d H:i:s') .": START Daily\n";
	echo date('Y/m/d H:i:s') .": START - Daily\n";

	mQuery("INSERT INTO log VALUES (NOW(), '". $script ."', 'Updating admin stats', ". LOG_TYPE_START .")");
	if ($debug) echo date('Y/m/d H:i:s') .": Updating admin stats\n";
	include(BASE_DIR .'/admin/sched/daily-admin-stats.php');
	if ($debug) echo date('Y/m/d H:i:s') .": Done (Updating admin stats)\n";
	mQuery("INSERT INTO log VALUES (NOW(), '". $script ."', 'Done (Updating admin stats)', ". LOG_TYPE_END .")");

	mQuery("INSERT INTO log VALUES (NOW(), '". $script ."', 'Updating subscriptions', ". LOG_TYPE_START .")");
	if ($debug) echo date('Y/m/d H:i:s') .": Updating subscriptions\n";
	include(BASE_DIR .'/admin/sched/daily-subscriptions.php');
	if ($debug) echo date('Y/m/d H:i:s') .": Done (Updating subscriptions)\n";
	mQuery("INSERT INTO log VALUES (NOW(), '". $script ."', 'Done (Updating subscriptions)', ". LOG_TYPE_END .")");

	mQuery("INSERT INTO log VALUES (NOW(), '". $script ."', 'Daily forum cleanup', ". LOG_TYPE_START .")");
	if ($debug) echo date('Y/m/d H:i:s') .": Daily forum cleanup\n";

	# Delete pending account activations older than 7 days
	#		AND user_regdate < unix_timestamp() - ". 7*DAYS;
	$sql = "
	SELECT user_id
	FROM ". USERS_TABLE ."
	WHERE user_inactive_reason = ". INACTIVE_REGISTER ."
		AND user_actkey <> ''
		AND FROM_UNIXTIME(user_regdate) < NOW() - INTERVAL 1 WEEK";
	$result = mQuery($sql);
	while ($row = mysql_fetch_assoc($result)) {
		delete_user($row['user_id']);
	}

	# Translate old Amazon Link code
	if ($debug) echo date('Y/m/d H:i:s') .": Translate Forum Amazon links\n";
	include(BASE_DIR .'/admin/sched/daily-forum-translate-links.php');

	if ($debug) echo date('Y/m/d H:i:s') .": Done (Daily forum cleanup)\n";
	mQuery("INSERT INTO log VALUES (NOW(), '". $script ."', 'Done (Daily forum cleanup)', ". LOG_TYPE_END .")");
### END Forum Tasks

### Replicate blog categories. Any created for articles (id 1) should be replicated for bulletins (id 7), columns (id 10) and podcasts (9)
	if ($debug) echo date('Y/m/d H:i:s') .": Replicating article categories\n";
	include(BASE_DIR .'/admin/sched/daily-update-categories.php');
	if ($debug) echo date('Y/m/d H:i:s') .": DONE (Replicating article categories)\n";

### Sync forum profile changes with main user database
	// Since email changes are synched upon profile save for the main database, if email address is different between the two databases,
	// it must have been changed via the forum UI, so overwirte the primary db with that email address
	mQuery("INSERT INTO log VALUES (NOW(), '". $script ."', 'Synching forum profile changes', ". LOG_TYPE_START .")");
	if ($debug) echo date('Y/m/d H:i:s') .": Synching forum profile changes\n";
	$qry = "SELECT user_id, user_email FROM ". USERS_TABLE ." b, user u WHERE u.bb_id = b.user_id AND b.user_email <> u.email_address";
	$email_change = mQuery($qry);
	while ($ec_row = mysql_fetch_assoc($email_change)) {
		$sql = "UPDATE user SET email_address = '$ec_row[user_email]', email_invalid = 0 WHERE bb_id = $ec_row[user_id]";
		if ($debug) {echo "$sql\n";} else {mQuery($sql);}
	}
	if ($debug) echo date('Y/m/d H:i:s') .": Done (Synching forum profile changes)\n";
	mQuery("INSERT INTO log VALUES (NOW(), '". $script ."', 'Done (Synching forum profile changes)', ". LOG_TYPE_END .")");

### Make sure all Premium subscribers are members of the Premium Members group in phpbb
	mQuery("INSERT INTO log VALUES (NOW(), '". $script ."', 'Synching forum group membership', ". LOG_TYPE_START .")");
	if ($debug) echo date('Y/m/d H:i:s') .": Synching forum group membership\n";
	$qry = "SELECT user_id FROM ". USERS_TABLE ." pu, user u WHERE u.bb_id = pu.user_id AND access & 2";
	$res_ps = mQuery($qry);
	while ($row_ps = mysql_fetch_assoc($res_ps)) {
		# Need to check for existence first, since the table indexes are non-unique
		$sql = "SELECT user_id FROM ". USER_GROUP_TABLE ." WHERE group_id = 16968 AND user_id = $row_ps[user_id]";
		$res_check_ps = mQuery($sql);
		if (mysql_num_rows($res_check_ps) == 0) {
			$sql = "INSERT INTO ". USER_GROUP_TABLE ." (group_id, user_id, user_pending) VALUES (16968, $row_ps[user_id], 0)";
			if ($debug) {echo "$sql\n";} else {mQuery($sql);}
		}
	}
	if ($debug) echo date('Y/m/d H:i:s') .": Done (Synching forum group membership)\n";
	mQuery("INSERT INTO log VALUES (NOW(), '". $script ."', 'Done (Synching forum group membership)', ". LOG_TYPE_END .")");

/*
### Execute the keyword search
	mQuery("INSERT INTO log VALUES (NOW(), '". $script ."', 'Updating keywords', ". LOG_TYPE_START .")");
	$dev_null = fopen('/dev/null', 'w');
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, 'http://hdtvmagazine:1080i720p@www.digitalpoint.com/tools/keywords/?action=lookup_thread');
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_FILE, $dev_null);
	curl_exec($ch);
	curl_close($ch);
	mQuery("INSERT INTO log VALUES (NOW(), '". $script ."', 'Done (Updating keywords)', ". LOG_TYPE_END .")");

	// Pause for 30 minutes to give the keywords a chance to update
	sleep(1800);

### Execute the keyword email
	mQuery("INSERT INTO log VALUES (NOW(), '". $script ."', 'Fetching keywords', ". LOG_TYPE_START .")");
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, 'http://hdtvmagazine:1080i720p@www.digitalpoint.com/tools/keywords/?action=email&type=keywords');
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_FILE, $dev_null);
	curl_exec($ch);
	curl_close($ch);
	mQuery("INSERT INTO log VALUES (NOW(), '". $script ."', 'Done (Fetching keywords)', ". LOG_TYPE_END .")");
*/

### Purge old entries from log file
	mQuery("DELETE FROM log WHERE date < NOW() - INTERVAL 30 DAY");

### Move to separately timed script
	mQuery("INSERT INTO log VALUES (NOW(), '". $script ."', 'Synching Pricegrabber', ". LOG_TYPE_START .")");
	if ($debug) {
		echo date('Y/m/d H:i:s') .": Skipping Pricegrabber sync\n";
	} else {
		include(BASE_DIR .'/admin/sched/daily-pricegrabber.php');
	}
	mQuery("INSERT INTO log VALUES (NOW(), '". $script ."', 'Done (Synching Pricegrabber)', ". LOG_TYPE_END .")");

	mQuery("INSERT INTO log VALUES (NOW(), '". $script ."', 'END', ". LOG_TYPE_END .")");
	if ($debug) echo date('Y/m/d H:i:s') .": END Daily\n";
	echo date('Y/m/d H:i:s') .": END - Daily\n";
?>