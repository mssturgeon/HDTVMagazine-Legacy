<?
	require('../global.php');
	if (!access(ACCESS_PREMIUM)) prompt_login(PHP_SELF);

	$key = 'NBA Preseason Basketball';
	include('sports_body.php');
?>
