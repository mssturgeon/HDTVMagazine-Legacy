<?
	require('../global.php');
	require(BASE_DIR .'/includes/lib_admin.php');
	header('Content-type: text/plain');
	$debug = isset($_GET[debug]);

	# Delete pending account activations
	$sql = "
	SELECT user_id
	FROM phpbb_users
	WHERE user_active = 0 
		AND user_actkey <> ''
		AND user_regdate < unix_timestamp() - ". 48*HOURS;
	$result = mQuery($sql);
	while ($row = mysql_fetch_assoc($result)) {
		delete_user($row[user_id]);
	}
?>
