<?
	set_time_limit(0);
	$debug = isset($_GET['debug']);
	if ($debug) {
		header('Content-Type: text/plain');
	}
	
	// BASE_DIR is the full OS path to the web document directory.  User primarily in include/require statements
	define('BASE_DIR', '/var/www/html');
	require(BASE_DIR .'/includes/constants.php');
	require(BASE_DIR .'/includes/lib_common.php');
	require(BASE_DIR .'/includes/lib_mysql.php');
	$script = '/admin/sched/weekly.php';

#	echo date('Y/m/d H:i:s') .": START WEEKLY PROCESSING\n";
	mQuery("INSERT INTO log VALUES (NOW(), '". $script ."', 'Weekly Processing', ". LOG_TYPE_START .")");
	
#	echo date('Y/m/d H:i:s') .":\tSynching EVDB - START\n";
	mQuery("INSERT INTO log VALUES (NOW(), '". $script ."', 'Synching EVDB', ". LOG_TYPE_START .")");
	include(BASE_DIR .'/admin/sched/sync-evdb.php');
#	echo date('Y/m/d H:i:s') .":\tSynching EVDB - END\n";
	mQuery("INSERT INTO log VALUES (NOW(), '". $script ."', 'Done (Synching EVDB)', ". LOG_TYPE_END .")");

#	echo date('Y/m/d H:i:s') .": END WEEKLY PROCESSING\n";
	mQuery("INSERT INTO log VALUES (NOW(), '". $script ."', 'Done (Weekly Processing)', ". LOG_TYPE_END .")");
?>
