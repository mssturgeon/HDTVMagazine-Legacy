<?
	set_time_limit(0);
	$debug = isset($_GET[debug]);
	if ($debug) {
		header('Content-Type: text/plain');
	}

	require('../global.php');
	require(BASE_DIR .'/includes/lib_admin.php');
	if (!access(ACCESS_ADMIN)) prompt_login(PHP_SELF);
	
	$user_id = isset($_GET[user_id]) ? $_GET[user_id] : exit();
	
	delete_user($user_id);
	
	js_back();
?>
