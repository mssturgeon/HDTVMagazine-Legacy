<?
	$debug = isset($_GET['debug']);
	if ($debug) {
		header('Content-Type: text/plain');
	}
	echo date('Y/m/d H:i:s') .": Send Daily Status Report\n";

	define('BASE_DIR', '/var/www/html');
	require_once(BASE_DIR .'/includes/constants.php');
	require_once(BASE_DIR .'/includes/lib_mysql.php');
	require_once(BASE_DIR .'/includes/lib_common.php');
	require_once('/usr/share/pear/Mail.php');

	// Get ID's for notification
	$result = mQuery("SELECT notify_ids FROM admin_settings WHERE id = 1");
	$admindata = mysql_fetch_assoc($result);

	// Get email addresses for notification
	$result = mQuery("SELECT email_address FROM user WHERE id in ($admindata[notify_ids])");
	while ($row = mysql_fetch_assoc($result)) {
#		$message = file_get_contents(FULL_URL_ADMIN_STATUS_REPORT .'?no_session');

#		Send email
	}
?>
