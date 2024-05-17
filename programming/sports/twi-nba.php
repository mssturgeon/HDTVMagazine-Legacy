<?
	require('../global.php');
	if (!access(ACCESS_PREMIUM)) prompt_login(PHP_SELF);

	$key = 'NBA Basketball';
	include('sports_body.php');
?>
