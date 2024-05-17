<?
	require('/var/www/html/includes/facebook/facebook.php');

	# Connect as app: HDTV Magazine
	$config = array();
	$config['appId'] = '120965029026';
	$config['secret'] = 'a260e8304dcc4e0c8728528e4b779391';
	$config['fileUpload'] = true; // optional
	$config['cookie'] = true; // optional

	$facebook = new Facebook($config);
	$fb_user = $facebook->getUser();

	print "Date: ". date('g:ia', time() ) ."<br />";
	print "App ID: {$facebook->getAppId()}<br />";
	print "API Secret: {$facebook->getApiSecret()}<br />";
	print "User: {$fb_user}<br />";
	print "Code: {$_GET['code']}<br />";
	print "Access Token: {$facebook->getAccessToken()}<br />";
#	print_r($_SESSION);

	if ($fb_user) {
		try {
			$attachment = array(
				'access_token' => $facebook->getAccessToken(),
#				'message' => 'test message',
				'name' => 'test title',
#				'caption' => "test caption",
				'link' => 'http://www.hdtvmagazine.com/',
				'description' => 'test description',
#				'picture' => 'http://www.hdtvmagazine.com/images/hdtvmagazine.gif',
#				'source' => 'http://www.hdtvmagazine.com/',
#				'place' => 'test place',
#				'tags' => '618000991',
				'properties' => array(
					'Author' => array(
						'text' => 'test author name',
						'href' => 'http://www.hdtvmagazine.com/author.php?author=Alfred+Poor&id=29'
					),
					'Category' => 'test category',
				),
				'actions' => array(
					array(
						'name' => 'test action name',
						'link' => 'http://www.hdtvmagazine.com/columns/2012/07/hdtv-almanac-best-buy-10-per-inch-for-50-lcd.php'
					)
				)
			);
			print_r($attachment);

			$fb_result = $facebook->api('/HDTVMagazine/feed/', 'post', $attachment);
			print_r($fb_result);
		} catch(FacebookApiException $e) {
			echo("Error <b>{$e->getType()}</b>: {$e->getMessage()}<br />");
		}
	} else {
		$login_url = $facebook->getLoginUrl(array(
			'client_id' => $config['appId'],
			'client_secret' => $config['secret'],
			'code' => $_GET['code'],
			'scope' => 'manage_pages'
		));
#		echo 'Please <a href="' . $login_url . '">login.</a>';
		header("Location: $login_url");
	}
?>