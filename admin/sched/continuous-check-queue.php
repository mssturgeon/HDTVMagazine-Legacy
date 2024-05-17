<?
	########## THIS FILE IS NOW LOCATED ON 63.249.17.244 ##########

	## DO NOT EDIT ##

	set_time_limit(0);
	ini_set('memory_limit', '64M');

	$debug = isset($_GET['debug']);
#	$debug = true;
	if ($debug) {
		header('Content-Type: text/plain');
	}

	define('BASE_DIR', '/var/www/html');
	require_once(BASE_DIR .'/includes/constants.php');
	require_once(BASE_DIR .'/includes/lib_common.php');
	require_once(BASE_DIR .'/includes/lib_mysql.php');
	require_once('/usr/share/pear/Mail.php');

	# Load admindata
	$result = mQuery("SELECT * FROM admin_settings WHERE id = 1");
	$admindata = mysql_fetch_assoc($result);

	if ($admindata['email_in_progress'] == 1) {
		if ($debug) echo date('Y/m/d H:i:s') .": Email already in progress, exiting.\n";
		exit;
	}
	mQuery("UPDATE admin_settings SET email_in_progress = 1");

	# Init SMTP Connection
	$smtp = Mail::factory('smtp', array ('host' => $admindata['email_smtp_host'] ,'auth' => false));

	# FUNCTION mail_send
	function mail_send($to, $subject, $message, $content_type, $unsub_code = '') {
		global $smtp, $admindata, $debug;

#		$content_type = ($content_type == '') ? 'text/plain' : $content_type;

		// Add formatting to message
		if ($content_type == 'text/html') {
			$body = getHTMLMessage($to, $message, $unsub_code);
		} elseif ($content_type == 'text/plain') {
			$body = getTextMessage($to, $message, $unsub_code);
		} else {
			$body = $message;
		}

		$headers = array (
			'From' => $admindata['email_display_name'] .' <'. $admindata['email_reply_address'] .'>',
			'To' => $to,
			'Subject' => $subject,
			'Content-Type' => $content_type,
			'X-Sender' => $admindata['email_reply_address'],
			'Return-Path' => $admindata['email_return_path'],
			'Reply-To' => $admindata['email_reply_address'],
			'MIME-Version' => '1.0',
			'X-Priority' => '3',
			'X-Mailer' => 'PHP/'. phpversion());

		return $smtp->send($to, $headers, $body);
	} # END mail_send

	# Only send 5000 documents at a time due to memory limiations in PEAR
	$result = mQuery("SELECT * FROM email_queue LIMIT 5000");
	while ($row = mysql_fetch_assoc($result)) {
		if ($debug) echo "Sending to {$row['send_to']}...";
		if (mail_send($row['send_to'], stripslashes($row['subject']), stripslashes($row['message']), $row['content_type'], urlencode(base64_encode($row['unsub_code'])))) {
			mQuery("DELETE from email_queue WHERE id = {$row['id']}");
			if ($debug) echo "DONE\n";
		} else {
			if ($debug) echo "FAILED\n";
			echo date('Y/m/d H:i:s') .": Failed to send message '$row[subject]' to '$row[send_to]'.\n";
			print_r($headers);
		}
	}
	mQuery("UPDATE admin_settings SET email_in_progress = 0");
?>
