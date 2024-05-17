<?
	set_time_limit(0);
	$debug = isset($_GET['debug']);
	if ($debug) {
		header('Content-Type: text/plain');
	}

	define('BASE_DIR', '/var/www/html');
	require(BASE_DIR .'/includes/constants.php');
	require(BASE_DIR .'/includes/lib_common.php');
	require(BASE_DIR .'/includes/lib_mysql.php');

#	echo date('Y/m/d H:i:s') .": Running Hourly\n";

### New syndicated content
	if ($debug) echo date('Y/m/d H:i:s') .": Check Syndicated Content\n";
	require(BASE_DIR .'/admin/sched/hourly-check-syn.php');

### Check Email Queue size
	$result = mQuery("select count(*) q_size from email_queue");
	$row = mysql_fetch_assoc($result);
	if ($row['q_size'] > 0) echo date('Y/m/d H:i:s') .": Email Queue: {$row['q_size']}\n";

### Import RSS News Feeds (done last due to length of processing
	if ($debug) echo date('Y/m/d H:i:s') .": Get News\n";
#	mQuery("INSERT INTO log VALUES (NOW(), '/admin/sched/hourly.php', 'Getting News', ". LOG_TYPE_START .")");
	require(BASE_DIR .'/admin/sched/hourly-get-news.php');
#	mQuery("INSERT INTO log VALUES (NOW(), '/admin/sched/hourly.php', 'Done (Getting News)', ". LOG_TYPE_END .")");
?>