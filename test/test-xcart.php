<?
	header('Content-Type: text/plain');
	define('BASE_DIR', '/var/www/html');
	
	# The auth.php includes this file as well, but it only searches the tree above it.  So we need to explicitly include it here
	# if we want to use it outside the hdstore subdir
	include BASE_DIR .'/hdstore/top.inc.php';
	require BASE_DIR .'/hdstore/auth.php';
	
	require('../global.php');
	$uname = $userdata[username];
	$usertype = 'C';
	
				$sql = "UPDATE $sql_tbl[customers] SET last_login = '".time()."' WHERE login = '$uname'";
				echo "$sql\n";
				mQuery($sql);
				
				$auto_login = true;
				$login = $uname;
				$login_type = $usertype;
				$logged = "";
				x_session_register("identifiers",array());
				$identifiers[$usertype] = array (
					'login' => $login,
					'login_type' => $login_type,
				);
				x_session_save();

	echo 'HTTP_COOKIE_VARS: ';
	print_r($HTTP_COOKIE_VARS);
	echo "\n";

	echo 'HTTP_SESSION_VARS: ';
	print_r($HTTP_SESSION_VARS);
	echo "\n";
	
	echo 'XCART_SESSION_VARS: ';
	print_r($XCART_SESSION_VARS);
	echo "\n";
	
	echo 'identifiers: ';
	print_r($identifiers);
	echo "\n";
	
	echo "Login: $login\n";
	
	echo "Logout_user: $logout_user\n";

	echo "XCARTSESSID: $XCARTSESSID\n";
?>
