<?
	header('Content-Type: text/plain');

	require('/var/www/html/includes/lib_facebook.php');

	print "Date: ". date('g:ia', time() ) ."\n";
	print "App ID: {$facebook->getAppId()}\n";
	print "API Secret: {$facebook->getApiSecret()}\n";
	print "User: {$fb_user}\n";
	print "Code: {$_GET['code']}\n";
	print "Access Token: {$facebook->getAccessToken()}\n";
#	print_r($_SESSION);

# 	$token_url = "https://graph.facebook.com/oauth/access_token?client_id={$config['appId']}&client_secret={$config['secret']}&grant_type=client_credentials";
# 	echo "$token_url\n";
# 	$response = file_get_contents($token_url);
#	$params = null;
#	parse_str($response, $params);
#	print_r($params);

#	$fb_result = $facebook->api("/{$config['appId']}?fields=access_token", 'get', false);
#	print_r($fb_result);
#	exit();

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

	fbPost('/HDTVMagazine/feed/', $attachment);
?>