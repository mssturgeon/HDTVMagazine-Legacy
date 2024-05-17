<?php
	require('/var/www/html/includes/facebook/facebook.php');

	# HDTV Magazine Feed
	$api_key = '[REDACTED]';
	$app_secret = '[REDACTED]';
	$app_id = '222776463019';
	$token = 'GQAR7P';

	# HDTV Magazine
	$api_key = '[REDACTED]';
	$app_secret = '[REDACTED]';
	$app_id = '120965029026';
#	$token = 'T6J7RE';
	$token = '2U6IQS';

	$facebook = new Facebook($api_key, $app_secret);
#	$result = $facebook->api_client->auth_getSession($token, true);
#	$result = $facebook->api_client->auth_getSession('auth_token' = $token, 'generate_session_secret' => true);
	$result = $facebook->call_method('facebook.auth.getSession', array('auth_token' => $token, 'generate_session_secret' => true));

	echo "<br /><pre>";
	print_r($result);
	echo $session_key = $result['session_key'];
?>