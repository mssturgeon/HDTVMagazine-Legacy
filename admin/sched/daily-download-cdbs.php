<?
	set_time_limit(0);

	$debug = isset($_GET['debug']);
#	$debug = true;
	$skipdownload = isset($_GET['skipdownload']);
#	$skipdownload = true;
	if ($debug) {
		header('Content-Type: text/plain');
		echo date('Y/m/d H:i:s') .': '. $_SERVER['PHP_SELF'] ."\n";
	}

	define('BASE_DIR', '/var/www/html');
	require(BASE_DIR .'/includes/constants.php');
	require(BASE_DIR .'/includes/lib_mysql.php');
	require(BASE_DIR .'/includes/lib_common.php');
	require(BASE_DIR .'/includes/lib_ftp.php');

	$download_dir = DATA_DIR .'/cdbs';
	$remote_dir = '/pub/Bureaus/MB/Databases/cdbs';

	if (!$skipdownload) {
		$files[] = "all-cdbs-files.zip";

		### Get FTP Files
		ftp_mget('ftp.fcc.gov', 'anonymous', 'feedback@hdtvmagazine.com', $files, $remote_dir, $download_dir);

		### Unzip Files
		foreach ($files as $filepath) {
			unzip("$download_dir/$filepath");
		}
	}

### Import files

	# tv_eng_data
	$sql = "
		LOAD DATA INFILE '$download_dir/tv_eng_data.dat' REPLACE INTO TABLE cdbs_tv_eng_data
		FIELDS TERMINATED BY '|' ESCAPED BY '\\\\' LINES TERMINATED BY '\\r\\n'";
	if ($debug) {
		echo "$sql\n";
		$start = time();
		mQuery($sql);
		$duration = time() - $start;
		echo 'Loaded in: '. date("i:s", $duration) ."\n";
		ob_flush(); flush();
	} else {mQuery($sql);}

	# facility
	$sql = "
		LOAD DATA INFILE '$download_dir/facility.dat' REPLACE INTO TABLE cdbs_facility
		FIELDS TERMINATED BY '|' ESCAPED BY '\\\\' LINES TERMINATED BY '\\r\\n'";
	if ($debug) {
		echo "$sql\n";
		$start = time();
		mQuery($sql);
		$duration = time() - $start;
		echo 'Loaded in: '. date("i:s", $duration) ."\n";
		ob_flush(); flush();
	} else {mQuery($sql);}

	# app_tracking
	$sql = "
		LOAD DATA INFILE '$download_dir/app_tracking.dat' REPLACE INTO TABLE cdbs_app_tracking
		FIELDS TERMINATED BY '|' ESCAPED BY '\\\\' LINES TERMINATED BY '\\r\\n'";
	if ($debug) {
		echo "$sql\n";
		$start = time();
		mQuery($sql);
		$duration = time() - $start;
		echo 'Loaded in: '. date("i:s", $duration) ."\n";
		ob_flush(); flush();
	} else {mQuery($sql);}

?>
