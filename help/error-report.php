<?
	require('../global.php');
	if (!access(ACCESS_PREMIUM)) prompt_login(PHP_SELF);

	$action = isset($_POST['action']) ? $_POST['action'] : '';
	if ($action == 'save') {
		// Set the From address
		$from = $_POST['first_name'] .' '. $_POST['last_name'] .' <'. $_POST['email_address'] .'>';
		
		// Get Feedback Address
		$qry = "SELECT feedback_email FROM admin_settings WHERE id = 1";
		$result = mQuery($qry);
		$row = mysql_fetch_assoc($result);
		$to = $row['feedback_email'];
		
		$subject = "Error Report: ". $_POST['type'] ." [{$_SESSION['user_id']}]";;
		$message = "user_id:\t{$_SESSION['user_id']}\n";
		$message .= "user_name:\t{$_SESSION['user_name']}\n";
		$message .= "email_address:\t{$_POST['email_address']}\n";
		$message .= "access:\t\t{$_SESSION['access']}\n";
		$message .= "browser:\t{$_POST['browser']}\n";
		$message .= "category:\t{$_POST['category']}\n";
		$message .= "type:\t\t{$_POST['type']}\n";
		$message .= "provider_name:\t{$_POST['provider_name']}\n";
		$message .= "program_title:\t{$_POST['program_title']}\n";
		$message .= "station_name:\t{$_POST['station_name']}\n";
		$message .= "Edit User:\thttp://www.hdtvmagazine.com/admin/user-edit.php?id={$_SESSION['user_id']}\n";
		$message .= "Comments:\n\n". stripslashes($_POST['comments']);
		
		$headers = "From: $from\r\n" .
		"X-Mailer: PHP/" . phpversion() ."\r\n";
			
		if ($_POST['cc'] == 1) $headers .= "Cc: $from\r\n";

		if (mail($to, $subject, $message, $headers)) {
			js_replace(URL_PROG_HELP_ERROR_REPORT_THANKS);
		} else {
			js_back('Failed to send your message...');
		}
		exit;
	}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>HDTV Magazine - Report Errors</title>
	<?require(BASE_DIR .'/includes/common_header.php');?>
	<meta name="description" content="HDTV Program Guide Error Report">
	<meta name="keywords" content="hdtv,hdtv programming,hdtv guide,hdtv schedule,hdtv listing,hdtv guides,hdtv schedules,hdtv listings,hdtv programs,hdtv programming guide,hdtv programming schedule,hdtv program,hd guide,hd programming,hd schedule,abc hdtv programming,cbs hdtv programming,digital tv programming,digital tv schedule,digital tv schedules,dtv listings,dtv program,dtv programing,dtv programming,dtv schedule,hd programing,hd programming,hd programs,high definition guide,high definition programing,high definition programming,high definition programs,high definition schedule,nbc hdtv programming,guide,news,sports,hdtv stations">
	<meta name="rating" content="general">
	<script language="javascript" type="text/javascript">
		function init() {
			frm.browser.value = navigator.userAgent;
		}
	</script>
</head>
<body onload="init()">
	<?
		include(BASE_DIR .'/includes/tracker.php');
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');

		
   	// Make sure they have a valid email address specified.
   	$qry = "SELECT first_name, last_name, email_address FROM user WHERE id = {$_SESSION['user_id']} AND email_invalid = 0";
   	$result = mQuery($qry);
   	$row = mysql_fetch_assoc($result);
   	$first_name = $row['first_name'];
   	$last_name = $row['last_name'];
   	$email_address = $row['email_address'];
   ?>

				<?if ($email_address == '') {?>
  					<table class="box" align="center"><tr><td>
  						Sorry, you must have a valid email address specified in your <a href="<?=URL_PROFILE?>">Profile</a> to use this feedback form.
  					</td></tr></table>
					<form name="frm" action="<?=PHP_SELF?>" method="post">
						<input type="hidden" name="browser" value="">
					</form>
  				<?} else {?>
					<table class="box" align="center"><tr><td>
						Please fill out the following form as completely as possible.  The Site Administrators will be notified and will forward the information to our data provider as appropriate.<br>
						<br>
						If you have not already done so, please check <a href="<?=URL_PROG_HELP_FAQ?>">The FAQ</a> for a possible answer to your question.
					</td></tr></table>
					
					<table cellpadding="3" cellspacing="0" align="center" style="width:400px">
						<tr><td style="font-weight:bold" colspan="2">Known Issues:</td></tr>
						<tr><td valign="top">3/1/2005</td><td>Browser anomalies with Safari and IE (Mac version) related to the popover program descriptions leaving "remnants" behind.</td></tr>
						<tr><td valign="top">3/1/2005</td><td>Some layout issues being experienced with Firefox.</td></tr>
					</table>
					<form name="frm" action="<?=PHP_SELF?>" method="post">
						<input type="hidden" name="action" value="save">
						<input type="hidden" name="email_address" value="<?=$email_address?>">
						<input type="hidden" name="first_name" value="<?=$first_name?>">
						<input type="hidden" name="last_name" value="<?=$last_name?>">
						<input type="hidden" name="user_name" value="<?=$_SESSION['user_name']?>">
						<input type="hidden" name="user_id" value="<?=$_SESSION['user_id']?>">
						<input type="hidden" name="browser" value="">
						<table class="table1" align="center">
							<tr>
								<td class="table1Header" colspan="2">Error Report</td>
							</tr><tr>
								<td class="inputLabel" width="100">Username:</td>
								<td><?=$_SESSION['user_name']?> (<?=$email_address?>)</td>
							</tr><tr>
								<td class="inputLabel" width="100">Browser:</td>
								<td><script language="javascript" type="text/javascript">document.write(navigator.userAgent);</script></td>
							</tr><tr>
								<td class="inputLabel">&nbsp;</td>
								<td><input type="checkbox" name="cc" value="1">Send a copy of this feedback to my email address.</td>
							</tr><tr>
								<td class="inputLabel">Type of Error:</td>
								<td>
									<select name="type">
										<option value="">
										<option value="Missing Provider">Missing Provider
										<option value="Missing Program">Missing Program
										<option value="Missing Station">Missing Station
										<option value="Other">Other
									</select>
								</td>
							</tr><tr>
								<td class="inputLabel">Provider Name:</td>
								<td><input type="text" class="inputText" style="width:40ex" name="provider_name" value=""></td>
							</tr><tr>
								<td class="inputLabel">Program Title:</td>
								<td><input type="text" class="inputText" style="width:40ex" name="program_title" value=""></td>
							</tr><tr>
								<td class="inputLabel">Station Name:</td>
								<td><input type="text" class="inputText" name="station_name" value=""></td>
							</tr><tr>
								<td class="inputLabel" valign="top">Comments:</td>
								<td><textarea name="comments" value="" rows="10"></textarea></td>
							</tr><tr>
								<td class="buttonBar" colspan="2">
									<input type="submit" name="btnSubmit" value="Send" class="inputButton">
								</td>
							</tr>
						</table>
					</form>
				<?}?>

		<?include(BASE_DIR .'/includes/body_footer.php');?>

</body>
</html>
