<?
	require('global.php');

	$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '/index.php';
	js_replace('/forum/ucp.php?mode=login&redirect='. $redirect);
?>
