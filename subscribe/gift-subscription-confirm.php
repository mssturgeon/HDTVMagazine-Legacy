<?
	require('../global.php');
	if ($user->data['user_id'] == '') prompt_login(PHP_SELF);

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - Gift Subscription Confirmation</title>
	<meta name="description" content="">
	<meta name="keywords" content="hdtv,hd tv,high definition,high def tv,high definition television,high definition tv">
	<meta name="rating" content="general">
	<? require(BASE_DIR .'/includes/page_header.php');?>
</head>
<body>
	<?
		include(BASE_DIR .'/includes/body_header-4.php');
	?>

				<h1>Gift Subscription Confirmation</h1>
				<p>
					Thank you for ordering gift subscriptions for the email addresses listed below. Once payment is confirmed, their accounts will become active and each person will receive an email indicating your gift purchase for them.
				</p>
				<?
					$result = mQuery("SELECT * FROM gift_subscriptions WHERE from_user = ". $user->data['user_id']);
					while ($row = mysql_fetch_assoc($result)) {
						echo $row['email_address'] .'<br>';
					}
				?>

	<? include(BASE_DIR .'/includes/body_footer-4.php');?>

</body>
</html>
