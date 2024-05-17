<?
	require('../global.php');
	if (!access(ACCESS_PREMIUM)) prompt_login(PHP_SELF);

	$key = 'Boxing';
	include('sports_body.php');
?>
