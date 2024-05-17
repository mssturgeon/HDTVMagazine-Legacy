<?
	require('../../global.php');
	if (!access(ACCESS_ADMIN_ANY)) prompt_login(PHP_SELF);

	$debug = isset($_GET['debug']);
	if ($debug) {
		header('Content-Type: text/plain');
	}
	
	$sql = "SELECT id, bb_id FROM user";
	$result = mQuery($sql);
	while ($row = mysql_fetch_assoc($result)) {
		$sql = "UPDATE paypal_ipn SET user_id = $row[bb_id] WHERE id = $row[id]";
		if ($debug) {echo "$sql\n";} else {mQuery($sql);}
	}
?>
