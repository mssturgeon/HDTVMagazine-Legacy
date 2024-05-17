<?
	require_once('/var/www/html/includes/facebook/facebook.php');

#	$debug = true;

	# Connect as app: HDTV Magazine
	$config = array();
	$config['appId'] = '635221083170071';
	$config['secret'] = '9aa7f95a1935c2de0732406751884454';
	$config['fileUpload'] = true; // optional
	$config['cookie'] = true; // optional

/*************
* Steps for obtaining an access token:
*
* 1) Using the Graph API Explorer, select the app click "Get Access Token"
* 2) Select the permissions you want the selected app to have and click "Get Access Token"
* 3) Query endpoint /me/accounts to get a list of access tokens for your pages
* 4) Select the (short-lived) access token from the page to which you want to publish
*/
	$access_token_short = '[REDACTED]';
/*
* 5) Visit the following URL to extend a short-lived token for a longer one:
*		https://graph.facebook.com/oauth/access_token?client_id=APP_ID&client_secret=APP_SECRET&grant_type=fb_exchange_token&fb_exchange_token=ACCESS_TOKEN_SHORT
*		Response: access_token=AAAJBuu8EJRcBAMzxZCStVhYedconuEMiQhZCX3SRuWsSjeC21ZA9rN0mivzCkHCsAfk2nckF7IQLZB3aqLaDjMmbDruHMDvsNmPvpebATgZDZD&expires=5184000
*/
	$access_token_long = '[REDACTED]';
/*
* 6) Query endpoint /me/accounts again to get a list of access tokens for your pages that DO NOT EXPIRE
*/
	$access_token_permanent = '[REDACTED]';
/*
/**************/

	$facebook = new Facebook($config);
	$facebook->setAccessToken($access_token_permanent);

	if ($debug) {
		print "Date: ". date('g:ia', time() ) ."\n";
		print "App ID: {$facebook->getAppId()}\n";
		print "API Secret: {$facebook->getApiSecret()}\n";
		print "User: {$facebook->getUser()}\n";
		print "Access Token: {$facebook->getAccessToken()}\n";
	}

	function fbQuery($fql) {
		global $config, $facebook, $debug;

/*
		$result = $facebook->api_client->fql_query($fql);
		return $result[0];
*/
		$hdtvmagazine = $facebook->api('/HDTVMagazine');
		return $hdtvmagazine['fan_count'];
	}

	function fbPost($target, $attachment) {
		global $config, $facebook, $debug;

		if ($facebook->getUser()) {
			try {
				$fb_result = $facebook->api($target, 'post', $attachment);
				if ($debug) print_r($fb_result);
			} catch(FacebookApiException $e) {
				echo("Error <b>{$e->getType()}</b>: {$e->getMessage()}<br />");
			}
		} else {
			$login_url = $facebook->getLoginUrl(array(
				'client_id' => $config['appId'],
				'client_secret' => $config['secret'],
#				'code' => $_GET['code'],
				'scope' => 'manage_pages'
			));
#			header("Location: $login_url");
			#print_r(file_get_contents($login_url));

/*
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $login_url);
			$get = curl_exec($ch);
			curl_close($ch);
*/
/*
			$access_token = str_replace('access_token=', '', $get);

			$ch = curl_init();
			$postFields = array('access_token' => $access_token, 'message' => 'test message');

			curl_setopt($ch, CURLOPT_URL, "https://graph.facebook.com/profile_id_here/feed");
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_VERBOSE, true);

			$tt = curl_exec($ch);
			curl_close($ch);
*/
			echo "Facebook couldn't log in";
			exit();
		}
	}
?>