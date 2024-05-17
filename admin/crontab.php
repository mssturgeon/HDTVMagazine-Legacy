<?
	require('../global.php');
	if (!access(ACCESS_ADMIN_ANY)) access_denied();

	header('Content-Type: text/plain');
	header('Content-Disposition: filename="crontab.txt"');
	
	include('/var/www/crontab.out');
?>
