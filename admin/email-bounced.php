<?
	require('../global.php');
	if (!access(ACCESS_ADMIN)) prompt_login(PHP_SELF);

	$action = isset($_POST[action]) ? $_POST[action] : '';
	if ($action == 'submit') {
		echo '<html><head></head><body style="font-size:8pt">';
		foreach (split("\n", $_POST[email_addresses]) as $email_address) {
			$email_address = trim($email_address);
			if ($email_address != '') {
				$qry = "UPDATE user SET email_invalid = email_invalid + 1 WHERE email_address = '$email_address'";
				$result = mQuery($qry);
				$updated = mysql_affected_rows();

				$qry = "SELECT email_invalid FROM user WHERE email_address = '$email_address'";
				$result = mQuery($qry);
				$row = mysql_fetch_assoc($result);

				switch ($updated) {
					case 0:
						echo "Email Address '$email_address' not found.<br />";
						break;
					case 1:
						echo "[$row[email_invalid]] Updated $email_address<br />";
						break;
					default:
						echo "[$row[email_invalid]] Updated $email_address ($updated times)<br>";
						break;
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
	<title>HDTV Magazine - Email Bounced</title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
</head>
<body onload="document.frm.email_addresses.focus()">

	<p align="center">
		This page is used to record bounced email addresses.  Entering an address below and clicking submit will increase the "invalid" counter.  Once it reaches 3, no more emails will be sent to this address until corrected.
	</p>

	<table align="center">
		<form name="frm" action="<?=PHP_SELF?>" method="post">
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

</body>
</html>
