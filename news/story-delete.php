<?
	require('../global.php');
	if (!access(ACCESS_ADMIN_NEWS)) prompt_login(PHP_SELF);
	
	$id = isset($_GET[id]) ? $_GET[id] : exit();
	$sql = "DELETE FROM hdtv_rss WHERE id = $id";
	$db->sql_query($sql);
	js_back();
?>
