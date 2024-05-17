<?
	$debug = isset($_GET[debug]);
	if ($debug) header('Content-Type: text/plain');

	define('BASE_DIR', '/var/www/html');
	require(BASE_DIR .'/includes/constants.php');
	require(BASE_DIR .'/includes/lib_common.php');
	require(BASE_DIR .'/includes/lib_mysql.php');
#	require(BASE_DIR .'/includes/lib_admin.php');
	require_once(BASE_DIR .'/includes/twitteroauth/twitteroauth.php');
#	require_once(BASE_DIR .'/includes/twitteroauth/config.php');

	# Load admindata
	$result = mQuery("SELECT * FROM admin_settings WHERE id = 1");
	$admindata = mysql_fetch_assoc($result);

	### Twitter Followers
/*
	$curl_handle = curl_init();
	curl_setopt($curl_handle, CURLOPT_URL, 'http://twitter.com/followers/ids.xml?screen_name=HDTVMagazine');
	curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
	curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl_handle, CURLOPT_USERPWD, "$admindata[twitter_username]:$admindata[twitter_password]");
	$buffer = curl_exec($curl_handle);
	curl_close($curl_handle);
*/

if ($debug) {
	echo "Consumer Key: {$admindata['twitter_consumer_key']}\n";
	echo "Consumer Secret: {$admindata['twitter_consumer_secret']}\n";
	echo "Access Token: {$admindata['twitter_access_token']}\n";
	echo "Access Token Secret: {$admindata['twitter_access_token_secret']}\n";
}

$connection = new TwitterOAuth($admindata['twitter_consumer_key'], $admindata['twitter_consumer_secret'], $admindata['twitter_access_token'], $admindata['twitter_access_token_secret']);
if ($debug) print_r($connection);

/* If method is set change API call made. Test is called by default. */
$content = $connection->get('account/rate_limit_status');
echo "Current API hits remaining: {$content->remaining_hits}.\n";

# $content = $connection->get('account/verify_credentials');
# if ($debug) print_r($content);

#$connection->post('statuses/update', array('status' => date(DATE_RFC822)));
$response = $connection->post('direct_messages/new', array('screen_name' => 'shanesturgeon', 'text' => 'Testing out @oauthlib code'));
if ($debug) print_r($response);

?>