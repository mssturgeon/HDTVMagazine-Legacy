<?
	require('global.php');
	if ($user->data['user_id'] == '') prompt_login(PHP_SELF);

	$action = isset($_POST['action']) ? $_POST['action'] : '';
	if ($action == 'save') {
		if ($_POST['comments'] != '') {
   		// Set the From address
   		$from = $user->data['first_name'] .' '. $user->data['last_name'] .' <'. $user->data['user_email'] .'>';
   		$subject = "Subscription Cancellation: ". stripslashes($_POST['subject']) ." [". $user->data['user_id'] ."]";

   		$message = "user_id:\t". $user->data['user_id'] ."\n";
   		$message .= "user_name:\t". $user->data['username'] ."\n";
   		$message .= "email_address:\t". $user->data['user_email'] ."\n";
   		$message .= "Edit User:\thttp://www.hdtvmagazine.com/admin/user-edit.php?id=". $user->data['user_id'] ."\n";
   		$message .= "Comments:\n\n". stripslashes($_POST['comments']);

   		$headers = "From: $from\r\n" .
   		"X-Mailer: PHP/" . phpversion() ."\r\n";

			$sql = "
			INSERT INTO email_queue
				(send_to, subject, message, content_type, unsub_code)
				VALUES ('{$admindata['feedback_email']}', 'Subscription Cancellation', '". addslashes($message) ."', 'text/plain', '')";
			mQuery($sql);
		}
		js_replace('https://www.paypal.com/cgi-bin/webscr?cmd=_subscr-find&alias=pp%40hdtvmagazine%2ecom');
		exit;
	}

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - Cancel Subscription</title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
</head>
<body id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header-4.php');
	?>

	<h1>Cancel Membership</h1>
	<div align="center"><div style="width:600px; text-align:left">
		<p>
			Thank you for being a member. We are sorry to see that you want to cancel your HDTV Magazine Premium Membership.
	 		We have provided a text box below for you to provide any feedback, if you wish. Please take a moment to tell us why you are cancelling
			so that we can further improve our services:
		</p>

		<form name="frm" action="<?=PHP_SELF?>" method="post">
			<input type="hidden" name="action" value="save">
			<textarea name="comments" style="width:400px;height:100px"></textarea><br>
			<p>When you click the button below, your comments will be sent and you will be redirected to the PayPal site, where you can cancel your
			subscription. If you do not complete the cancellation process on the PayPal site, your subscription will remain active.</p>
			<input type="submit" name="btnSubmit" value="Cancel Membership" class="inputButton">
		</form>
	</div></div>
	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
</body>
</html>
