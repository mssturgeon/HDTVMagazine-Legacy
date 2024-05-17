<?
	require('../global.php');
	if (!access(ACCESS_ADMIN_ANY)) access_denied();
	header('Content-type: text/plain');

	$sql = "INSERT INTO email_queue VALUES ('sa-test@sendmail.net', '', '', 'text/plain', '', NULL)";
	mQuery($sql);
?>
