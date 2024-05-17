<?
/*************************************************************************
This script runs each morning and updates statistics for the
previous day (or current day where applicable).
*************************************************************************/

	define('BASE_DIR', '/var/www/html');
	require_once(BASE_DIR .'/includes/constants.php');
	require_once(BASE_DIR .'/includes/lib_mysql.php');
	require_once(BASE_DIR .'/includes/lib_facebook.php');
#	require_once(BASE_DIR .'/includes/facebook/facebook.php');

	# Include phpbb library for table constants
	define('IN_PHPBB', true);
	$phpbb_root_path = BASE_DIR .'/forum/';
	$phpEx = substr(strrchr(__FILE__, '.'), 1);
	include_once($phpbb_root_path . 'common.' . $phpEx);

	if (CALLED_FROM_DAILY !== true) {
		$debug = isset($_GET['debug']);
		if ($debug) header('Content-Type: text/plain');
	}

	$ts = strtotime('Yesterday');
	$datestamp = date('Y-m-d', $ts);

	# Load admindata
	$sql = "SELECT * FROM admin_settings WHERE id = 1";
	$result = mQuery($sql);
	$admindata = mysql_fetch_assoc($result);
	if ($debug) print_r($admindata);

### Total users
		if ($debug) echo date('Y/m/d H:i:s') .": Get Total users\n";
		$sql = "SELECT id FROM user";
		$result = mQuery($sql);
		$users_total = mysql_num_rows($result);
		if ($debug) echo "Total Users: $users_total\n";

### New Users
		if ($debug) echo date('Y/m/d H:i:s') .": Get New Users\n";
		$sql = "SELECT user_id FROM ". USERS_TABLE ." WHERE FROM_UNIXTIME(user_regdate, '%Y-%m-%d') = '$datestamp'";
		$result = mQuery($sql);
		$users_reg = mysql_num_rows($result);
		if ($debug) echo "New Users: $users_reg\n";

### New Users - Active
		if ($debug) echo date('Y/m/d H:i:s') .": Get New Users - Active\n";
		$sql = "SELECT user_id FROM ". USERS_TABLE ." WHERE user_inactive_reason = 0 AND FROM_UNIXTIME(user_regdate, '%Y-%m-%d') = '$datestamp'";
		$result = mQuery($sql);
		$users_active = mysql_num_rows($result);
		if ($debug) echo "Active Users: $users_active\n";

### Paid Subs - All with access
		if ($debug) echo date('Y/m/d H:i:s') .": Get Paid Subs - All with access\n";
		$sql = "SELECT DISTINCT user_id, item_number FROM paypal_ipn p, user u WHERE u.id = p.custom AND item_number IN (2,3) AND exp_pg > '$datestamp'";
		$result = mQuery($sql);
		$users_pd = mysql_num_rows($result);
		if ($debug) echo "Paid Users: $users_pd\n";

### Paid Subs - Monthly
		if ($debug) echo date('Y/m/d H:i:s') .": Get Paid Subs - Monthly\n";
		$sql = "SELECT DISTINCT user_id, item_number FROM paypal_ipn p, user u WHERE u.id = p.custom AND item_number = 2 AND exp_pg > '$datestamp'";
		$result = mQuery($sql);
		$users_pd_m = mysql_num_rows($result);
		if ($debug) echo "Paid Users (Monthly): $users_pd_m\n";

### Twitter Followers
	$followers = 0;
	if ($debug) echo date('Y/m/d H:i:s') .": Get Twitter Followers\n";
	$curl_handle = curl_init();
//	curl_setopt($curl_handle, CURLOPT_URL, 'http://twitter.com/followers/ids.xml?screen_name=HDTVMagazine');
	curl_setopt($curl_handle, CURLOPT_URL, 'https://api.twitter.com/1/followers/ids.xml?screen_name=HDTVMagazine');
	curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
	curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
//	curl_setopt($curl_handle, CURLOPT_USERPWD, "$admindata[twitter_username]:$admindata[twitter_password]");
	$buffer = curl_exec($curl_handle);
	curl_close($curl_handle);

	// check for success or failure
	if (!empty($buffer)) {
		$xml = new SimpleXMLElement( $buffer );
		$followers = count($xml->ids->id);
/*
		foreach ($xml->id as $id) {
			$followers++;
		}
*/
	} else {
		echo "Error, buffer is empty!";
	}
	if ($debug) echo "Twitter Followers: $followers\n";

### Facebook fans
/*
	if ($debug) echo date('Y/m/d H:i:s') .": Get Facebook fans\n";
	$facebook = new Facebook($admindata['facebook_api_key'], $admindata['facebook_app_secret']);
#	$data = $facebook->api_client->pages_getInfo(45415877375, 'fan_count', '', '');
	$data = $facebook->api_client->pages_getInfo($admindata['facebook_page_id'], 'fan_count', '', '');
	$facebook_fans = $data[0]['fan_count'];
	if ($debug) echo "Facebook Fans: $facebook_fans\n";
*/
	$hdtvmagazine = $facebook->api('/HDTVMagazine');
	if ($debug) print_r($hdtvmagazine);
	$facebook_fans = $hdtvmagazine['likes'];
	if ($debug) echo "Facebook Fans: $facebook_fans\n";

### UPDATE admin_stats
	if ($debug) echo date('Y/m/d H:i:s') .": Update admin_stats\n";
	$sql = "REPLACE INTO admin_stats (datestamp, users_total, users_reg, users_active, users_pd, users_pd_m, twitter_followers, facebook_fans)
	VALUES ('$datestamp', $users_total, $users_reg, $users_active, $users_pd, $users_pd_m, $followers, $facebook_fans)";
	if ($debug) {echo "$sql\n";} else {mQuery($sql);}

	foreach ($SUB as $sub_value => $sub_label) {
		$sql = "SELECT id
		FROM user u, ". USERS_TABLE ." pu
		WHERE u.bb_id = pu.user_id
			AND pu.user_inactive_reason = 0
			AND email_invalid < 3
			AND email_address <> ''
			AND email_spam = 0
			AND subscriptions & $sub_value";
		$res_count = mQuery($sql);
		$count = mysql_num_rows($res_count);
		$sql = "INSERT INTO admin_stats (datestamp, sub_$sub_value) VALUES ('$datestamp', $count) ON DUPLICATE KEY UPDATE sub_$sub_value = $count";
		if ($debug) {echo "$sql\n";} else {mQuery($sql);}
	}
?>