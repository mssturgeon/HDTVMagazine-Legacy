<?
	require('../global.php');
	if (!access(ACCESS_ADMIN)) prompt_login($_SERVER['PHP_SELF']);

	define('FULL_URL_ADMIN_EMAILS_COMPLAINT', 'http://'. SERVER_NAME .'/admin/emails/complaint.php');

	$action = isset($_POST['action']) ? $_POST['action'] : '';
	if ($action == 'submit') {
		$processed = array();
		echo '<html><head></head><body style="font-size:8pt">';
		foreach (split("\n", $_POST['email_addresses']) as $email_address) {
			$email_address = trim($email_address);
			if ($email_address != '' && !in_array($email_address, $processed)) {
				# Get user, but don't email them again if they have already been marked
				$res_user = mQuery("SELECT id FROM user WHERE email_address = '$email_address' AND email_spam = 0");
				$row_user = mysql_fetch_assoc($res_user);
				if (mysql_num_rows($res_user) == 0) {
					echo "Email Address '$email_address' not found.<br />";
				} else {
					# Flag users account with Spam reported
					$sql = "UPDATE user SET email_spam = 1 WHERE email_address = '$email_address'";
					mQuery($sql);
					$updated = mysql_affected_rows();

					# Update user comments
					mQuery("INSERT INTO user_comments (user_id, timestamp, comment) VALUES ({$row_user['id']}, ". time() .", 'Spam complaint received')");

					# Send Welcome Email and wait for activation to send the samples
					$sql = "
					INSERT INTO email_queue
						(send_to, subject, message, content_type, unsub_code)
						VALUES ('$email_address', 'Complaint received from your ISP', '". addslashes(file_get_contents('http://'. SERVER_NAME .'/admin/emails/complaint.php')) ."', 'text/plain', '')";
					mQuery($sql);

					$processed[] = $email_address;
					echo "Updated $email_address ($updated times)<br>";
				}
			}
		}
		echo '</body></html>';
		exit;
	}

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - Email Complaints</title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
</head>
<body onload="document.frm.email_addresses.focus()">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>

	<p align="center">
		This page is used to record email addresses who have reported our messages as spam.
		Entering an address below and clicking submit will block that user from receiving any future email from us.
	</p>

	<table align="center">
		<form name="frm" action="<?=$_SERVER['PHP_SELF']?>" method="post">
			<input type="hidden" name="action" value="submit">
			<tr>
				<td class="inputLabel" valign="top">Email Address:</td>
				<td>
					<!--input type="text" name="email_address" class="inputText"-->
					<textarea name="email_addresses" style="width:300px;height:300px"></textarea>
					<input type="submit" value="Submit" class="inputButton">
				</td>
			</tr>
		</form>
	</table>

	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
</body>
</html>
