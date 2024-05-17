<?
	header('Content-Type: text/plain');

	// If nosession is passed, then use that, otherwise load global and use currently logged in user.
	if (isset($_GET['no_session'])) {
		define('BASE_DIR', '/var/www/html');
		require_once(BASE_DIR .'/includes/constants.php');
		require_once(BASE_DIR .'/includes/lib_common.php');
		require_once(BASE_DIR .'/includes/lib_mysql.php');

		# Include phpbb library for table constants
		define('IN_PHPBB', true);
		$phpbb_root_path = BASE_DIR .'/forum/';
		$phpEx = substr(strrchr(__FILE__, '.'), 1);
		include_once($phpbb_root_path . 'common.' . $phpEx);
	} else {
		require('../../global.php');
		if (!access(ACCESS_ADMIN_ANY)) prompt_login(PHP_SELF);
	}

	// Get user info
	$id = isset($_GET[u]) ? $_GET[u] : '';
	$sql = "
	SELECT first_name, username, password, user_inactive_reason, user_actkey
	FROM user u, ". USERS_TABLE ." b
	WHERE u.bb_id = b.user_id
		AND user_id = '$id'";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
?>
Hello <?=$row['first_name']?>,

<? if ($row['user_inactive_reason'] == INACTIVE_REGISTER) {?>
*** NOTE: Your account is not yet activated. An activation link has been included below and must be clicked before you will be able to log in. Also, please search your inbox or spam/junk folder for a message with subject "Welcome to HDTV Magazine". This email contains important information to assist you in setting up your membership. ***

Please click the following link to activate your account:
http://www.hdtvmagazine.com/activate.php?act_key=<?=$row['user_actkey']?>&user_id=<?=$id?>

<?}?>

Per your request, your login credentials are included below:

username: <?=$row['username']?>

password: <?=$row['password']?>


It is recommended that you copy/paste the above values when logging in, but be sure not to include any leading or trailing "space" characters.

Here is a link to the login page for your convenience:
http://www.hdtvmagazine.com/login.php


Please feel free to reply to this message if you experience any difficulty.

-- Shane & Dale
HDTV Magazine
www.hdtvmagazine.com
