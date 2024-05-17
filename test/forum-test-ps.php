<?
	set_time_limit(0);
	$debug = isset($_GET['debug']);
	if ($debug) {
		header('Content-Type: text/plain');
		header('Content-Disposition: filename="daily.txt"');
	}
	
	// BASE_DIR is the full OS path to the web document directory.  User primarily in include/require statements
	define('BASE_DIR', '/var/www/html');
	require(BASE_DIR .'/includes/constants.php');
	require(BASE_DIR .'/includes/lib_common.php');
	require(BASE_DIR .'/includes/lib_mysql.php');
	require(BASE_DIR .'/includes/lib_hdtv.php');
	require(BASE_DIR .'/includes/lib_admin.php');

	# The following makes sure all Premium subscribers are members of the Premium Members group in phpbb
	echo date('Y/m/d H:i:s') .":\tSyncing Forum Group Membership...";
	$qry = "SELECT user_id FROM phpbb_users pu, user u WHERE u.bb_id = pu.user_id AND access & 2";
	$res_ps = mQuery($qry);
	while ($row_ps = mysql_fetch_assoc($res_ps)) {
		# Need to check for existence first, since the table indexes are non-unique
		$sql = "SELECT user_id FROM phpbb_user_group WHERE group_id = 16968 AND user_id = $row_ps[user_id]";
		$res_check_ps = mQuery($sql);
		echo mysql_num_rows($res_check_ps);
		if (mysql_num_rows($res_check_ps) == 0) {
			$sql = "INSERT INTO phpbb_user_group (group_id, user_id, user_pending) VALUES (16968, $row_ps[user_id], 0)";
			if ($debug) {echo "$sql\n";} else {echo $sql;}
		}
	}
	echo "Done (Syncing Forum Group Membership)\n";
?>
