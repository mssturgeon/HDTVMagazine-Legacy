<?
	require('global.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>HDTV Magazine - Email Subscribe</title>
	<?require(BASE_DIR .'/includes/common_header.php');?>
</head>
<body>
	<?
		include(BASE_DIR .'/includes/tracker.php');
		include(BASE_DIR .'/includes/body_header.php');

		$u = split(':', $_GET[u]);
	?>
	
	<h1>Subscribe</h1>
	<?
		if (strpos($_GET[u], ':') == 0) {?>
			The purpose of this page is to provide our subscribers an easy method of subscribing to various email subscriptions. If you are trying to subscribe to a specific subscription
			type, please go to your <a href="<?=URL_PROFILE_SUBSCRIPTIONS?>">Subscription Profile</a>.<br />
			<br />
			Thank you,<br />
			<br />
			- Dale &amp; Shane<br />
			HDTV Magazine
		<?} elseif ($u[0] == 14446) {
			echo 'This is a system account for the Tips List, and cannot be altered.';
		} else {
			# Subscribe user
			$uu = base64_decode(urldecode($u[1]));

			# If not a valid subscription value, exit
			if (!array_key_exists($uu, $SUB)) {
				echo "Invalid subscription value ($uu)";
			} else {
	        	$qry = "UPDATE user SET subscriptions = subscriptions | $uu WHERE id = $u[0]";
  	      	$result = mQuery($qry);
			
				# Update user comments
				mQuery("INSERT INTO user_comments (user_id, timestamp, comment) VALUES ($u[0], ". time() .", 'Subscribed to $SUB[$uu]')");
				echo "Thank you. You have successfully subscribed to the <b>$SUB[$uu]</b> mailing list. If this was not your intent, you".
				' may unsubscribe via your <a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Subscription Profile</a>.';
			}
		}
	?>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>

	<?include(BASE_DIR .'/includes/body_footer.php');?>
</body>
</html>
