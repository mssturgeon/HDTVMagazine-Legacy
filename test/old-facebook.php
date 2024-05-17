<?
	header('Content-Type: text/plain');

	define('BASE_DIR', '/var/www/html');
	require(BASE_DIR .'/includes/constants.php');
	require(BASE_DIR .'/includes/lib_common.php');
	require(BASE_DIR .'/includes/lib_mysql.php');
	require(BASE_DIR .'/includes/lib_admin.php');
	require(BASE_DIR .'/includes/facebook/facebook.php');

	# General settings
	$target_id = '45415877375';

	# HDTV Magazine
	$api_key = '[REDACTED]';
	$app_secret = '[REDACTED]';
#	$app_id = '120965029026';
#	$token = 'T6J7RE';
	$session_key = '74c2c929dd124982262d72ba-618000991';
#	$session_secret = '[REDACTED]';

	# Paste to browser to get token
	# https://login.facebook.com/code_gen.php?api_key=API_KEY&v=1.0

	# Paste to browser to authorize
	# http://www.facebook.com/login.php?api_key=[REDACTED]&connect_display=popup&v=1.0&next=http://www.facebook.com/connect/login_success.html&cancel_url=http://www.facebook.com/connect/login_failure.html&fbconnect=true&return_session=true&req_perms=read_stream,publish_stream,offline_access&enable_profile_selector=1

	$facebook = new Facebook($api_key, $app_secret);
	$facebook->api_client->session_key = $session_key;
	$facebook->api_client->expires = 0;

	$message = '';
	$attachment = array(
		'name' => 'HDTV and Home Theater Podcast - Podcast #405: Listener Q & A',
		'href' => 'http://www.hdtvmagazine.com/podcast/2009/12/hdtv_and_home_theater_podcast_podcast_405_listener_q_a.php',
		'description' => 'Merry Christmas to all and to all another HDTV Podcast episode. May your tree be full of gifts to help you enjoy HDTV and Home Theater even more this coming year and may your stocking overflow with great gadgets.',
		'properties' => array(
			'Author' => array(
				'text' => 'The HT Guys',
				'href' => 'http://www.hdtvmagazine.com/author.php?author=The+HT+Guys&id=21'
			),
			'Category' => array(
				'text' => 'General Interest',
				'href' => 'http://www.hdtvmagazine.com/category.php?id=314&category=General+Interest'
			),
		)
	);
	$action_links = null;

	$facebook->api_client->stream_publish($message, $attachment, $action_links, null, $target_id);

	print_r($facebook, true);
?>