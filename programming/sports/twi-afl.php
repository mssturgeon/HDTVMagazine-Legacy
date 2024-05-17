<?
	require('../global.php');
	if (!access(ACCESS_PREMIUM)) prompt_login(PHP_SELF);

	$key = 'Arena Football';
	include('sports_body.php');
?>
