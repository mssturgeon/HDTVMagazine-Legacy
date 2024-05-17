<?
	require('../global.php');
	if (!access(ACCESS_ADMIN)) prompt_login(PHP_SELF);

	$qry = "select u.id, item_number".
	" from user u, paypal_ipn p".
	" where u.id = p.user_id".
	"	and p.item_number in (2,3)".
	"	and txn_type = 'subscr_payment'".
	"	and payment_status = 'Completed'".
	" order by u.id, timestamp";
	$result = mQuery($qry);
	while ($row = mysql_fetch_assoc($result)) {
		$freq = ($row['item_number'] == 2) ? 'M' : 'Y';
		$qry = "UPDATE user SET membership_freq = '$freq' WHERE id = {$row['id']}";
		//mQuery($qry);
		echo "$qry\n";
	}
	
/*	// Get user_id
	$result = mQuery("SELECT MAX(user_id) AS total FROM phpbb_users");
	$row = mysql_fetch_assoc($result);
	$phpbb_id = $row['total'] + 1;

	// Create php_bb accounts for every user account
	$result = mQuery("SELECT user_name, email_address, password FROM user WHERE bb_id IS NULL AND email_address <> ''");
	$row = mysql_fetch_assoc($result);
	while ($row = mysql_fetch_assoc($result)) {
		$md5_password = md5($row['password']);
		$qry = "INSERT INTO phpbb_users (user_id, username, user_password, user_email) VALUES ($phpbb_id, '{$row['user_name']}', '$md5_password', '{$row['email_address']}')";
		mQuery($qry);
//		echo "$qry<br>";

		// Update user table with bb_id
		$qry = "UPDATE user SET bb_id = $phpbb_id WHERE user_name = '{$row['user_name']}'";
//		echo "$qry<br>";
		mQuery($qry);

		$phpbb_id++;
	}
*/



/*
	// Assign phpbb_id in user table based on username match
	$result = mQuery("select bb.user_id, user_name from user u, phpbb_users bb where u.user_name = bb.username and bb_id is null and username <> '' order by id");
	while ($row = mysql_fetch_assoc($result)) {
		$qry = "UPDATE user SET bb_id = {$row['user_id']} WHERE user_name = '{$row['user_name']}'";
		echo "$qry<br>";
		mQuery($qry);

/*
		if ($row['email_address'] <> '') {
   		$qry = "UPDATE phpbb_users SET user_email = '{$row['email_address']}' WHERE username = '{$row['username']}'";
//   		echo "$qry<br>";
   		mQuery($qry);
		}
*/
	}
*/


/*
	// Assign phpbb_id in user table based on email_address match
	$result = mQuery("SELECT bb.user_id id, bb.user_email email FROM phpbb_users bb, user WHERE user.email_address = bb.user_email and bb.user_email <> ''");
	$row = mysql_fetch_assoc($result);
	while ($row = mysql_fetch_assoc($result)) {
		$qry = "UPDATE user SET bb_id = {$row['id']} WHERE email_address = '{$row['email']}'";
//		echo "$qry<br>";
		mQuery($qry);
	}
*/

/*
	// Get user_id
	$result = mQuery("SELECT MAX(user_id) AS total FROM phpbb_users");
	$row = mysql_fetch_assoc($result);
	$phpbb_id = $row['total'] + 1;

	$result = mQuery("SELECT email_address, user_name, password FROM user");
	while ($row = mysql_fetch_assoc($result)) {
		$md5_password = md5($row['password']);

		$find = mQuery("SELECT user_id FROM phpbb_users WHERE username = '{$row['user_name']}'");
		if (mysql_num_rows($find) == 0) { // Insert new user
			$qry = "INSERT INTO phpbb_users (user_id, username, user_password, user_email) VALUES ($phpbb_id, '{$row['user_name']}', '$md5_password', '{$row['email_address']}')";
		} else { // Update user
			$qry = "UPDATE phpbb_users SET user_password = '$md5_password', user_email = '{$row['email_address']}' WHERE username = '{$row['user_name']}'";
		}
		mQuery($qry);
//		echo "$qry<br>";

		$phpbb_id++;
	}
*/

?>
