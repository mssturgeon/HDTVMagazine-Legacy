<?
	require('/var/www/html/includes/facebook/facebook.php');
	header('Content-Type: text/plain');

	# Connect as app: HDTV Magazine
	$config = array();
	$config['appId'] = '120965029026';
	$config['secret'] = 'a260e8304dcc4e0c8728528e4b779391';
#	$config['fileUpload'] = true; // optional

	$facebook = new Facebook($config);

#	$fb_session = $facebook->getSession();
#	print_r($fb_session);

	$fb_result = $facebook->api('/'. $config['appId'] .'/accounts/', 'GET');
	print_r($fb_result);

?>