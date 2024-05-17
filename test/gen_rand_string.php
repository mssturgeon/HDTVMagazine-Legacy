<?
	require('../global.php');
	header('Content-type: text/plain');

	# Generate and store confirmation code for email confirmation
	for ($x=0; $x<100; $x++) {
		$user_actkey = gen_rand_string(true);
		$key_len = 54 - ( strlen($server_url) );
		$key_len = ( $key_len > 6 ) ? $key_len : 6;
		$user_actkey = substr($user_actkey, 0, $key_len);
		echo "$user_actkey\n";
	}
		
?>
