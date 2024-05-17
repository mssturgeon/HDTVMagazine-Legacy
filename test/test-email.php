<?
	$debug = isset($_GET['debug']);
	if ($debug) header('Content-Type: text/plain');

	define('BASE_DIR', '/var/www/html');
	require_once(BASE_DIR .'/includes/constants.php');
	require_once(BASE_DIR .'/includes/lib_common.php');
	require_once(BASE_DIR .'/includes/lib_mysql.php');

	# create a boundary string. It must be unique so we use the MD5 algorithm to generate a random hash
	$boundary = "PHP-alt-". md5(date('r', time()));

	$orig_message = addslashes(file_get_contents(BASE_URL .'/hdtv-daily.php?boundary='. $boundary));

	$sql = "
	SELECT id, first_name, last_name, user_name, email_address
	FROM user
	WHERE email_address <> ''
		AND email_invalid < 3
		AND subscriptions & ". SUB_TEST;
	if ($debug) {echo "$sql\n";}
	$result = mQuery($sql);

	while ($row_email = mysql_fetch_assoc($result)) {
		# Replace unsub_code and email_address
		$message = str_replace('[[email_address]]', $row_email['email_address'], $orig_message);
		$message = str_replace('[[unsub_code]]', urlencode(base64_encode($row_email['id'] .':'. SUB_TEST)), $message);
		$message = str_replace('[[list_name]]', $SUB[SUB_TEST], $message);

		$name = $row_email['first_name'] .' '. $row_email['last_name'];
		if (trim($name) == '') {
			$to = $row_email['user_name'] .' <'. $row_email['email_address'] .'>';
		} else {
			$to = $name .' <'. $row_email['email_address'] .'>';
		}

		$sql = "INSERT INTO email_queue (send_to, subject, message, content_type)
		VALUES ('$to', '$subject', '$message)', 'multipart/alternative; boundary=\"$boundary\"')";
		if ($debug) {echo "$sql\n";} else {mQuery($sql);}
	}
?>