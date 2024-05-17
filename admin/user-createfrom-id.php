<?
	set_time_limit(0);
	$debug = isset($_GET[debug]);
	if ($debug) {
		header('Content-Type: text/plain');
	}

	require('../global.php');
	require(BASE_DIR .'/includes/lib_admin.php');
	if (!access(ACCESS_ADMIN)) prompt_login(PHP_SELF);

	$id = isset($_GET[id]) ? $_GET[id] : exit();

	$sql = "
	INSERT INTO ". USERS_TABLE ." (user_id, user_email, username, user_password)
	SELECT bb_id, email_address, user_name, md5(password) FROM user WHERE id = $id";
	if ($debug) {echo "$sql\n";} else {mQuery($sql);}

	js_back();
?>
