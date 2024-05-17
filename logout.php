<?
	require ('global.php');

	// Must clear the old cookies
  	setcookie('user_name', '', time() + (30*DAYS), '/programming/', 'www.hdtvmagazine.com');
  	setcookie('user_id', '', time() + (30*DAYS), '/programming/', 'www.hdtvmagazine.com');
  	setcookie('user_name', '', time() + (30*DAYS), '/', COOKIE_DOMAIN);
  	setcookie('user_id', '', time() + (30*DAYS), '/', COOKIE_DOMAIN);

	// Clear phpBB Login info
	$user->session_kill();
	$user->session_begin();

	session_unset();
	js_replace($_GET[r]);
?>
