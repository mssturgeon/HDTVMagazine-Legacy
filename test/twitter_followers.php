<?
	$debug = isset($_GET[debug]);
	if ($debug) header('Content-Type: text/plain');

	define('BASE_DIR', '/var/www/html');
	require(BASE_DIR .'/includes/constants.php');
	require(BASE_DIR .'/includes/lib_common.php');
	require(BASE_DIR .'/includes/lib_mysql.php');
	require(BASE_DIR .'/includes/lib_admin.php');

	# Load admindata
	$result = mQuery("SELECT * FROM admin_settings WHERE id = 1");
	$admindata = mysql_fetch_assoc($result);

	### Twitter Followers
	$curl_handle = curl_init();
	curl_setopt($curl_handle, CURLOPT_URL, 'http://twitter.com/followers/ids.xml?screen_name=HDTVMagazine');
	curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
	curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl_handle, CURLOPT_USERPWD, "$admindata[twitter_username]:$admindata[twitter_password]");
	$buffer = curl_exec($curl_handle);
	curl_close($curl_handle);

	// check for success or failure
	if (!empty($buffer)) {
		echo $buffer;
		$xml = new SimpleXMLElement( $buffer );
		foreach ($xml->id as $id) {
			$count++;
		}
		echo $count;
	} else {
		echo "ERROR";
	}

?>