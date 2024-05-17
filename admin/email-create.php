<?
	set_time_limit(0);

	require('../global.php');
	if (!access(ACCESS_ADMIN)) prompt_login($_SERVER['PHP_SELF']);

	$from = "{$admindata['email_display_name']} <{$admindata['email_reply_address']}>";

	// Get Subscription Type to broadcast
	$sub_type = isset($_GET['sub_type']) ? $_GET['sub_type'] : SUB_TEST;
	$sub_type = isset($_POST['sub_type']) ? $_POST['sub_type'] : $sub_type;

	if ($sub_type == 'basic') {
		$qry = "
		SELECT id, bb_id, email_address, email_preference, first_name, last_name
		FROM user u, ". USERS_TABLE ." pu
		WHERE u.bb_id = pu.user_id
			AND pu.user_inactive_reason = 0
			AND email_address <> ''
			AND email_invalid < 3
			AND email_spam = 0
			AND (subscriptions & ". SUB_BROADCAST .")
			AND NOT(access & ". ACCESS_PREMIUM .")";
		$unsub_type = '';
	} elseif ($sub_type == 'premium') {
		$qry = "
		SELECT id, bb_id, email_address, email_preference, first_name, last_name
		FROM user u, ". USERS_TABLE ." pu
		WHERE u.bb_id = pu.user_id
			AND pu.user_inactive_reason = 0
			AND email_address <> ''
			AND email_invalid < 3
			AND email_spam = 0
			AND (subscriptions & ". SUB_BROADCAST .")
			AND (access & ". ACCESS_PREMIUM .")";
		$unsub_type = '';
	} elseif ($sub_type == 'email_user') {
		$qry = "
		SELECT id, bb_id, email_address, email_preference, first_name, last_name, username
		FROM user u, ". USERS_TABLE ." pu
		WHERE u.bb_id = pu.user_id
			AND pu.user_inactive_reason = 0
			AND email_invalid < 3
			AND email_spam = 0
			AND username LIKE '%@%'";
		$unsub_type = '';
	} elseif ($sub_type == 'expired_subscriptions') {
		$qry = "
		SELECT id, bb_id, email_address, email_preference, first_name, last_name
		FROM user u, ". USERS_TABLE ." pu
		WHERE u.bb_id = pu.user_id
			AND pu.user_inactive_reason = 0
			AND email_address <> ''
			AND email_invalid < 3
			AND email_spam = 0
			AND membership_freq = 'Y'
			AND exp_pg < curdate()";
		$unsub_type = '';
	} elseif ($sub_type == 'program_data_users') {
		$qry = "
		SELECT id, bb_id, email_address, email_preference, first_name, last_name
		FROM user u, ". USERS_TABLE ." pu
		WHERE u.bb_id = pu.user_id
			AND pu.user_inactive_reason = 0
			AND email_address <> ''
			AND email_invalid < 3
			AND email_spam = 0
			AND ((subscriptions & ". SUB_GUIDE_LISTING .") OR (access & ". ACCESS_PREMIUM ."))";
		$unsub_type = '';
	} else {
		$qry = "
		SELECT id, bb_id, email_address, email_preference, first_name, last_name, username
		FROM user u, ". USERS_TABLE ." pu
		WHERE u.bb_id = pu.user_id
			AND pu.user_inactive_reason = 0
			AND email_address <> ''
			AND email_invalid < 3
			AND email_spam = 0
			AND (subscriptions & $sub_type)";
		$unsub_type = $sub_type;
	}

	$SUB['pg_unsub'] = '* Basic Subscribers';
	$SUB['premium'] = '* Premium Subscribers';
	$SUB['email_user'] = '* Username LIKE Email Address';
	$SUB['expired_subscriptions'] = '* Expired Subscriptions';
	$SUB['program_data_users'] = '* Program data users';

	// Get User Count
	$result = mQuery($qry);
	$addr_count = mysql_num_rows($result);

	$action = isset($_POST['action']) ? $_POST['action'] : '';
	if ($action == 'send') {
		$orig_subject = stripslashes($_POST['subject']);
		$orig_message = stripslashes(str_replace('[campaign]', '?campaign='. $_POST['campaign'], html_entity_decode($_POST['message'])));
		$email_id = $_POST['email_id'];

		// Get Users
		$every = ceil($addr_count / 100);
		$x = 1;
		while ($row = mysql_fetch_assoc($result)) {
			$to = '<'. $row['email_address'] .'>';

			if ($_POST['content_type'] == '') {
				$content_type = ($row['email_preference'] == EMAIL_PREF_HTML) ? 'text/html' : 'text/plain';
			} else {
				$content_type = $_POST['content_type'];
			}

			// Prepare subject
			$subject = str_replace('[email_address]', $row['email_address'], $orig_subject);
			$subject = str_replace('[first_name]', $row['first_name'], $subject);
			$subject = str_replace('[last_name]', $row['last_name'], $subject);
			$subject = str_replace('[user_id]', $row['id'], $subject);
			$subject = str_replace('[username]', $row['username'], $subject);

			// Prepare message
			$message = str_replace('[email_address]', $row['email_address'], $orig_message);
			$message = str_replace('[first_name]', $row['first_name'], $message);
			$message = str_replace('[last_name]', $row['last_name'], $message);
			$message = str_replace('[user_id]', $row['id'], $message);
			$message = str_replace('[username]', $row['username'], $message);

			$unsub_code = ($unsub_type == '') ? '' : "{$row['id']}:$unsub_type";

			$sql = "
			INSERT INTO email_queue (send_to, subject, message, content_type, unsub_code)
			VALUES ('$to', '$subject', '". addslashes($message) ."', '$content_type', '$unsub_code')";
			mQuery($sql);
		}
		echo 'DONE<br>';
		ob_flush();
		ob_end_flush();
		exit;
	} elseif ($action == 'preview') { // Preview
		$orig_subject = stripslashes($_POST[subject]);
		$orig_message = stripslashes(str_replace('[campaign]', '?campaign='. $_POST['campaign'], $_POST['message']));
		$email_id = isset($_GET['id']) ? $_GET['id'] : '';
		?>
			<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
			<html>
			<head>
				<title>Broadcast Email Preview</title>
				<?require(BASE_DIR .'/includes/common_header.php');?>
				<script language="javascript" type="text/javascript">
					function validate() {
						if (!confirm('You are about to send this message to <?=$addr_count?> Email Addresses.  Are you sure?')) {return false}
						return true;
					}
				</script>
			</head>
			<body>
				<br>
				<form name="frm" action="<?=PHP_SELF?>" method="post" onSubmit="return validate()">
					<input type="hidden" name="action" value="send">
					<input type="hidden" name="email_id" value="<?=$email_id?>">
					<input type="hidden" name="sub_type" value="<?=$sub_type?>">
					<input type="hidden" name="subject" value="<?=$orig_subject?>">
					<input type="hidden" name="message" value="<?=htmlentities($orig_message)?>">
					<input type="hidden" name="campaign" value="<?=$_POST['campaign']?>">
					<input type="hidden" name="content_type" value="<?=$_POST['content_type']?>">
					<table class="type1b" style="width:95%;" align="center">
						<tr><td class="type1b_header" colspan="2">Broadcast Email Preview</td></tr>
						<tr>
							<td class="inputLabel" width="100">To:</td>
							<td><?=$sub[$sub_type]?> (<?=$addr_count?> Email Addresses)</td>
						</tr><tr>
							<td class="inputLabel">From:</td>
							<td><?=htmlentities($from)?></td>
						</tr><tr>
							<td class="inputLabel">Subject:</td>
							<td><?=$orig_subject?></td>
						</tr><tr>
							<td class="inputLabel" valign="top">Message:</td>
							<td style="padding-top:3px"><?
								if ($_POST['content_type'] == 'text/plain') {
									echo nl2br($orig_message);
								} else {
									echo $orig_message;
								}
							?></td>
						</tr><tr>
							<td class="buttonBar" colspan="2">
								<input type="submit" class="inputButton" name="btnSend" value="Send">
								&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								<input type="button" class="inputButton" name="btnBack" value="Back to Edit" onClick="location.href = '<?=URL_ADMIN_EMAIL_BROADCAST?>?id=<?=$email_id?>'">
							</td>
						</tr>
					</table>
				</form>
			</body>
			</html>
	<?} else {?>
		<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
		<html>
		<head>
			<title>Broadcast Email</title>
			<?require(BASE_DIR .'/includes/common_header.php');?>
			<script language="javascript" type="text/javascript">
				function init() {
					frm.subject.focus();
				}

				function validate() {
					if (frm.subject.value == '') {
						alert('Subject is required.');
						frm.subject.focus();
						return false;
					}

					if (frm.message.value == '') {
						alert('Message is required.');
						frm.message.focus();
						return false;
					}

					return true;
				}
			</script>
		</head>
		<body onload="init()">
			<table class="bare" cellpadding="0" cellspacing="0" summary="" width="90%" align="center">
				<tr>
					<td class="content">

						<p>
							Simply fill out or modifiy the Message portion of the email.  If you would like to include any specifc fields to be replaced with user information,
							please use the following rules:
						</p><p>
							[email_address] = User's Email Address<br>
							[first_name] = User's First Name<br>
							[last_name] = User's Last Name<br>
							[username] = Username<br>
							[user_id] = User's ID
						</p>

						<form name="frm" action="<?=PHP_SELF?>" method="post" onSubmit="return validate()">
							<input type="hidden" name="action" value="preview">
							<table class="type1b" style="width:95%;" align="center">
								<tr><td class="type1b_header" colspan="2">Broadcast Email</td></tr>
								<tr>
									<td class="inputLabel" width="100">To:</td>
									<td>
										<select name="sub_type" onchange="location.search = 'sub_type='+ this.value;">
											<?
												foreach ($SUB as $key => $value) {
													$selected = ($sub_type == $key) ? 'SELECTED' : '';
													echo '<option value="'. $key .'" '. $selected .'>'. $value .'</option>';
												}
											?>
										</select> (<?=$addr_count?> Email Addresses)
									</td>
								</tr><tr>
									<td class="inputLabel">From:</td>
									<td><?=htmlentities($from)?></td>
								</tr><tr>
									<td class="inputLabel">Subject:</td>
									<td><input type="text" class="inputText" name="subject" value="" maxlength="100" size="40"></td>
								</tr><tr>
									<td class="inputLabel">Send as:</td>
									<td>
										<input type="radio" name="content_type" value="text/plain">Text
										<input type="radio" name="content_type" value="text/html">HTML
										<input type="radio" name="content_type" value="">User Preference
									</td>
								</tr><tr>
									<td class="inputLabel">
										<?#show_help('Please be sure to set up the campaign in IndexTools before entering a keyword here. After entering a value here, place \'[campaign]\' wherever you would like to append the campaign name (i.e. '. URL_GUIDE .'[campaign]).');?>
										Campaign:
									</td>
									<td><input type="text" class="inputText" name="campaign" value="" maxlength="50" size="30"></td>
								</tr><tr>
									<td class="inputLabel" valign="top">Message:</td>
									<td>
										<textarea name="message" style="height:300px"></textarea>
									</td>
								</tr><tr>
									<td class="buttonBar" colspan="2">
										<input type="submit" class="inputButton" name="btnPreview" value="Preview">
										&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
										<input type="button" class="inputButton" name="btnCancel" value="Cancel" onClick="window.close()">
									</td>
								</tr>
							</table>
						</form>

					</td>
				</tr>
			</table>
		</body>
		</html>
<?}?>
