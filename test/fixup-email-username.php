<?
	header('Content-Type: text/plain');

	require('../global.php');
	if (!access(ACCESS_ADMIN)) prompt_login(PHP_SELF);

	$debug = isset($_GET['debug']);

	$sql = "
	SELECT user_id, username, user_email
	FROM phpbb_users
	WHERE username LIKE '%@%.%'";
	$result = mQuery($sql);
	while ($row = mysql_fetch_assoc($result)) {
#		$sql = "UPDATE user SET email_spam = 1 WHERE id = $row[user_id]";
#	  	if ($debug) {echo "$sql\n";} else {mQuery($sql);}
#		$username_trimmed = strleft($row['username'], '@');
		$username_trimmed = strleftback(str_replace('@', '', $row['username']), '.');
		$sql = "SELECT username, user_email FROM phpbb_users WHERE username = '$username_trimmed'";
		$res_match = mQuery($sql);
		echo "$sql\n";
		$num_matches = mysql_num_rows($res_match);
		if ($num_matches == 0) {
			$sql_update = "UPDATE phpbb_users SET username = '$username_trimmed', old_user_email = '{$row['user_email']}' WHERE user_id = '{$row['user_id']}'";
			echo "$sql_update\n";
			if ($debug) {echo "$sql_update\n";} else {mQuery($sql_update);}
		} else { # Generate new username
			echo "DUPLICATE: $row[user_email] -> $username_trimmed\n";
#			$new_username = $username_trimmed . $num_matches;
#			$sql = "SELECT username, user_email FROM phpbb_users WHERE username = '$new_username'";
#			$res_match = mQuery($sql);
#			if (mysql_num_rows($res_match) > 0) {
#				echo "DOUBLE DUPLICATE (". $ucount[$username_trimmed] ."): $username_trimmed\n";
#				echo $row['username'] ." -> ". $new_username ."\n";
#			}
		}
	}
?>
