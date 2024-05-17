<?
	require('../global.php');
	if (!access(ACCESS_PREMIUM)) prompt_login(PHP_SELF);

	$key = 'NASCAR Racing';
	include('sports_body.php');
?>
