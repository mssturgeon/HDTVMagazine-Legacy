<?
	/*************************************************************************************************
	* This file is sourced by every file within the site. It contains all the global information
	* necessary to provide configuration and session information while the user is visiting.
	*
	* Standards used:
	* - Constants are in ALL CAPS
	* - File system constants end in _DIR
	* - Relative URL constants end in _PATH
	*
	* Conversion Notes
	*
	*************************************************************************************************/

	define('BASE_DIR', '/var/www/html');

# --- BEGIN: PHPBB INIT CODE ---
	define('IN_PHPBB', true);
	$phpbb_root_path = BASE_DIR .'/forum/';
	$phpEx = substr(strrchr(__FILE__, '.'), 1);
	include($phpbb_root_path . 'common.' . $phpEx);

	$user->session_begin();
	$auth->acl($user->data);
	$user->setup();
# --- END: PHPBB INIT CODE ---

	// Load Constants
	require(BASE_DIR .'/includes/constants.php');

	// Load Libraries
	require(BASE_DIR .'/includes/lib_common.php');
	require(BASE_DIR .'/includes/lib_mysql.php');

	# Load data from user table into user->data array
//	if ($user->data['is_registered']) {
		$sql = "SELECT * FROM user WHERE bb_id = ". $user->data['user_id'];
		$user_result = mQuery($sql);
		if ($user_row = mysql_fetch_assoc($user_result)) {
			$user->data = array_merge($user_row, $user->data);
#			$user->data['time_zone_offset'] = $user->data['time_zone'] + (1*HOURS * $user->data['time_zone_dst'] * date("I"));
			$user->data['time_zone_offset'] = $user->data['time_zone'] + (HOURS * $user->data['time_zone_dst'] * date("I"));

			// Set corrected time_zone_text based on DST
			$text = $user->data['time_zone_text'];
			if (($user->data['time_zone_dst'] == 1) && (date("I") == 1)) {
				// The number to adjust is between "GMT" and ":"
				$num = strleft(strright($text, 'GMT'), ':');
				$text = 'GMT'. sprintf("%+02s", $num+1) .':'. strright($text, ":");
			}
			$user->data['time_zone_text'] = $text;
		}
//	}

	# Load admindata
	$admin_result = mQuery("SELECT * FROM admin_settings WHERE id = 1");
	$admindata = mysql_fetch_assoc($admin_result);
?>
