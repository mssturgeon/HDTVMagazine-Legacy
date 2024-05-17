<?
	set_time_limit(0);
	$debug = isset($_GET[debug]);
	if ($debug) {
		header('Content-Type: text/plain');
	}
	
	// BASE_DIR is the full OS path to the web document directory.  User primarily in include/require statements
	define('BASE_DIR', '/var/www/html');
	require(BASE_DIR .'/includes/constants.php');
	require(BASE_DIR .'/includes/lib_common.php');
	require(BASE_DIR .'/includes/lib_mysql.php');

#	echo date('Y/m/d H:i:s') .": START daily-amazon\n";

#	echo date('Y/m/d H:i:s') .":\tSynching Amazon Books...";
#	include(BASE_DIR .'/admin/sched/daily-amazon_books.php');
#	echo "Done (Synching Amazon Books)\n";

#	echo date('Y/m/d H:i:s') .":\tSynching Amazon General...";
	mQuery("INSERT INTO log VALUES (NOW(), '". PHP_SELF ."', 'Synching Amazon - General', ". LOG_TYPE_START .")");
	include(BASE_DIR .'/admin/sched/daily-amazon_general.php');
#	echo "Done (Synching Amazon General)\n";
	mQuery("INSERT INTO log VALUES (NOW(), '". PHP_SELF ."', 'Done (Synching Amazon - General)', ". LOG_TYPE_END .")");

#	echo date('Y/m/d H:i:s') .":\tSynching Amazon Reviews...";
	mQuery("INSERT INTO log VALUES (NOW(), '". PHP_SELF ."', 'Synching Amazon - Reviews', ". LOG_TYPE_START .")");
	include(BASE_DIR .'/admin/sched/daily-amazon_reviews.php');
#	echo "Done (Synching Amazon Reviews)\n";
	mQuery("INSERT INTO log VALUES (NOW(), '". PHP_SELF ."', 'Done (Synching Amazon - Reviews)', ". LOG_TYPE_END .")");

#	echo date('Y/m/d H:i:s') .":\tSynching Amazon Electronics - START\n";
#	include(BASE_DIR .'/admin/sched/daily-amazon_electronics.php');
#	echo date('Y/m/d H:i:s') .":\tSynching Amazon Electronics - END\n";

#	echo date('Y/m/d H:i:s') .": END daily-amazon\n";
?>