<?
	$mysql_username_override = 'mssturgeon';
	$mysql_password_override = 'mer70lot';
	require('../../global.php');

	mQuery("UPDATE tbl_models SET projection = 1 WHERE type = 4 AND projection = ''");
?>
