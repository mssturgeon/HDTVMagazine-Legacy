<?
	set_time_limit(0);
	ini_set('memory_limit', '32M');

	$debug = isset($_GET['debug']);
	if ($debug) header('Content-Type: text/plain');

	# Include necessary libraries for automated scripts
	define('BASE_DIR', '/var/www/html');
	require_once(BASE_DIR .'/includes/constants.php');
	require_once(BASE_DIR .'/includes/lib_common.php');
	require_once(BASE_DIR .'/includes/lib_mysql.php');
	require_once(BASE_DIR .'/includes/lib_admin.php'); // Tweet libraries
	require_once('/usr/share/pear/Mail.php');

	# Include phpbb library for table constants
	define('IN_PHPBB', true);
	$phpbb_root_path = BASE_DIR .'/forum/';
	$phpEx = substr(strrchr(__FILE__, '.'), 1);
	include_once($phpbb_root_path . 'common.' . $phpEx);

	# Load admindata
	$result = mQuery("SELECT * FROM admin_settings WHERE id = 1");
	$admindata = mysql_fetch_assoc($result);

	### New forum posts
	if ($debug) echo date('Y/m/d H:i:s') .": New Forum Posts\n";
	include(BASE_DIR .'/admin/sched/continuous-forum-post-notify.php');

	### Check for new content
	if ($debug) echo date('Y/m/d H:i:s') .": Check Content\n";
#	include(BASE_DIR .'/admin/sched/continuous-check-content.php');

	### New syndicated content
	#	include(BASE_DIR .'/admin/sched/hourly-get-news.php');

	### Reset IP address for miller
#	mQuery("UPDATE ". POSTS_TABLE ." SET poster_ip = '' WHERE poster_id = 25766");

	### Check for pending emails in queue
	########## THIS FILE IS NOW LOCATED ON 63.249.17.244 ##########
	#	if ($debug) echo date('Y/m/d H:i:s') .": Checking Email Queue\n";
	#	include(BASE_DIR .'/admin/sched/continuous-check-queue.php');
?>