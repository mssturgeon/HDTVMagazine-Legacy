<?
	require('../global.php');
	if (!access(ACCESS_PREMIUM)) prompt_login(PHP_SELF);

	$key = 'MLS Soccer';
	include('sports_body.php');
?>
