<?
/*
	This generates a URL that authorizes the appID 'HDTV Magazine' to post updates to the 'HDTV Magazine' page (manage_pages)

	Once you visit this URL, you can point to HDTVMagazine/accounts and get an access token that permits posting (short-lived)

	With this short-lived access token, exchange it for a permanent one using the following endpoint:
		https://graph.facebook.com/oauth/access_token?
			client_id=APP_ID&
			client_secret=APP_SECRET&
			grant_type=fb_exchange_token&
			fb_exchange_token=EXISTING_ACCESS_TOKEN
*/


	require('/var/www/html/includes/facebook/facebook.php');

	# Specify the user or app you will be logging in with
#	$api_key = '[REDACTED]';
#	$app_secret = '[REDACTED]';
#	$app_secret = '[REDACTED]';
#	$app_id = '222776463019';



	# Connect as app: HDTV Magazine
	$config = array();
	$config['appId'] = '120965029026';
	$config['secret'] = 'a260e8304dcc4e0c8728528e4b779391';
#	$config['fileUpload'] = true; // optional

	$facebook = new Facebook($config);
	$fbuser = $facebook->getUser();

#	echo $user_id;
/*
	$params = array(
			'canvas' => 1,
			'scope'  => 'publish_stream,email,user_about_me,user_birthday,user_website',
			'fbconnect' => 1,
			'redirect_uri' => 'https://apps.facebook.com/HDTVMagazine',
	);
*/
#	$fb_login_url = $facebook->getLoginUrl($params);

	# As page HDTV Magazine, request permission to publish to stream
	$url = $facebook->getLoginUrl(array(
#		'scope' => 'publish_stream, manage_pages',
#		'scope' => 'publish_stream, read_stream, manage_pages, offline_access',
		'scope' => 'manage_pages',
		'redirect_uri' => 'http://www.hdtvmagazine.com/includes/facebook/auth-redirect.php'
 	));
#	echo '<a href="'. $url .'">'. $url .'</a>';

//	$url = $facebook->getLoginUrl(array('canvas' => 1, 'fbconnect' => 0));

 	header("Location: $url");
#	header_redirect($url);
?>