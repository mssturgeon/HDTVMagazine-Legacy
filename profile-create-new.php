<?
	require('global.php');
	include_once(BASE_DIR .'/forum/includes/functions_user.php');

	$action = isset($_POST['action']) ? $_POST['action'] : '';
	$type = isset($_POST['type']) ? $_POST['type'] : '';

	if ($action == 'create') {
		$email_address = trim(strtolower($_POST['email_address']));

		// Make sure email address is valid and not already in use
		if (!preg_match('/^\w+((-\w+)|(\.\w+))*\@[a-z0-9]+((\.|-)[a-z0-9]+)*\.[a-z0-9]+$/', $email_address)) {
			js_back('The Email Address you provided ('. $email_address .') is not valid.\n\nPlease enter a valid email address.');
			exit;
		}

		$result = mQuery("SELECT id FROM user WHERE email_address = '$email_address'");
		if (mysql_num_rows($result) > 0) {
			js_redirect('/help/login-lookup.php?email_address='. $email_address, 'The Email Address you provided ('. $email_address .') is already registered.\n\n'.
			'We are redirecting you to the Username/Password lookup page so you can retrieve your current password.');
			exit;
		}

		# If the submission type is 'email_only', generate a safe user name & set subscriptions, otherwise check for validity
		if ($type == 'email_only') {
			$subscriptions = SUB_DAILY;
			$password = getRandomPassword();
			$first_name = '';
			$last_name = '';

			$x = 1;
			$user_name = strleft($email_address, '@');
			$sql = "SELECT username FROM ". USERS_TABLE ." WHERE LOWER(username) = '$user_name'";
			$result = mQuery($sql);
			if (mysql_num_rows($result) > 0) {
				while (mysql_num_rows($result) > 0) {
					$next_name = $user_name . $x++;
					$sql = "SELECT username FROM ". USERS_TABLE ." WHERE LOWER(username) = '$next_name'";
					$result = mQuery($sql);
				}
				$user_name = $next_name;
			}
		} else { // Not type=email_only
			$subscriptions = 0;
			$password = addslashes(trim($_POST['password']));
			$first_name = addslashes(trim($_POST['first_name']));
			$last_name = addslashes(trim($_POST['last_name']));

			$user_name = strtolower(trim($_POST['user_name']));
			if (!preg_match('/^\w+$/', $user_name)) {
				js_back('The Username you entered ('. addslashes($user_name) .') is not valid.\n\nPlease use only alphanumeric characters in your username (a-z, 0-9, _ ).');
				exit;
			}
			$result = mQuery("SELECT id FROM user WHERE LOWER(user_name) = '$user_name'");
			if (mysql_num_rows($result) > 0) {
				js_back('The Username you entered ('. addslashes($_POST['user_name']) .') is already registered.\n\n'+
				'Please choose a different username or if you have forgotten your Username or Password, please use the Username/Password lookup links on the Help page (hdtvmagazine.com/help).');
				exit;
			}
		}

		# Generate activation key
		$user_actkey = substr(getRandString(true), 0, 6);

		# Setup default time zone (Eastern w/DST)
		$tzo = (date('I')*HOURS) - 18000;
		$tzt = 'GMT-0'. (5 - (date('I')*HOURS)) .':00';
		$bb_tzo = $tzo / 3600;
		$time_zone_dst = 1;

		### NEW WAY - Add User
		# Get "Registered" Group Name
		$result = mQuery("SELECT group_id FROM ". GROUPS_TABLE ." WHERE group_name = 'REGISTERED' AND group_type = ". GROUP_SPECIAL);
		$row = mysql_fetch_assoc($result);
		$group_id = $row['group_id'];

		$user_row = array(
			'username' => $user_name,
			'user_password' => phpbb_hash($password),
			'user_email' => $email_address,
			'group_id' => $group_id,
			'user_timezone' => (float) $bb_tzo,
			'user_dst' => $time_zone_dst,
			'user_lang' => 'en-us',
			'user_type' => USER_INACTIVE,
			'user_actkey' => $user_actkey,
			'user_ip' => $user->ip,
			'user_regdate' => time(),
			'user_inactive_reason' => INACTIVE_REGISTER,
			'user_inactive_time' => time(),
		);
		$user_id = user_add($user_row, $cp_data = false);

		# Insert into user
		$sql_user = "INSERT INTO user (bb_id, user_name, password, first_name, last_name, email_address, time_zone_offset, time_zone_text, time_zone_dst, subscriptions)".
		" VALUES ($user_id, '$user_name', '$password', '$first_name', '$last_name', '$email_address', $tzo, '$tzt', $time_zone_dst, $subscriptions)";
		mQuery($sql_user);
		$id = mysql_insert_id();

		# Send Welcome Email and wait for activation to send the samples
		$welcome_email = BASE_URL .'/admin/emails/welcome-basic.php?id='. $id;
		$sql = "
		INSERT INTO email_queue
			(send_to, subject, message, content_type, unsub_code)
			VALUES ('$email_address', 'Welcome to HDTV Magazine', '". addslashes(file_get_contents($welcome_email)) ."', 'text/plain', '')";
		mQuery($sql);

		js_redirect(URL_WELCOME_BASIC ."?email=$email_address");
		exit;
	}

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - New Account</title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
	<script language="javascript" type="text/javascript">
		function validate() {
			with (document.frmProfile) {
				if (email_address.value.search(/^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/) == -1) {
					alert('Please enter a valid email address.');
					email_address.focus();
					return false;
				}

				if ((user_name.value.search(/^\w+$/) == -1) || (user_name.value.length > 20)) {
					alert('Invalid Username. Please make sure your username consists only of letters and numbers and is no more than 20 characters.');
					user_name.focus();
					return false;
				}

				if (password.value == '') {
					alert('Please enter a password.');
					password.focus();
					return false;
				}

				if (password.value != password_confirm.value) {
					alert('Passwords do not match!');
					password.focus();
					return false;
				}

				return true;
			}
		}

		function init() {
			document.frmProfile.first_name.focus();
		}
	</script>
</head>
<body id="body_container" onload="init()">
	<?
		include(BASE_DIR .'/includes/body_header-4.php');

		if ($email_provided) echo '<div class="alert_green" id="saved" align="center"><div>'.
		'Thank You!<br /><br />'.
		'You have been subscribed to the HDTV Magazine Daily and will be receiving email confirmation shortly. Please complete your registration by providing the following information.'.
		'</div></div>';
	?>

	<div align="center"><div style="text-align:left; width:728px"><form action="<?=$_SERVER['PHP_SELF']?>" method="post" name="frmProfile" onsubmit="return validate()">
		<input type="hidden" name="action" value="create">

		<br />
		<fieldset>
			<legend>Create your account</legend>
			<div class="infobox">Your personal information is kept confidential and will never be disclosed to third parties.
				For more information, please read our <a target="_blank" href="/about/privacy.php">Privacy Policy</a>.</div>
			<label for="first_name">First Name:</label>
			<input type="text" name="first_name" id="first_name" value="" size="15" maxlength="50"><br />
			<br />
			<label for="last_name">Last Name:</label>
			<input type="text" name="last_name" id="last_name" value="" maxlength="50" /><br />
			<br />
			<label for="user_name"><span style="color:red">*</span> Username:</label>
			<input type="text" name="user_name" id="user_name" value="<?=$user_name?>" maxlength="50"><br />
			<br />
			<label for="last_name"><span style="color:red">*</span> Email:</label>
			<input type="text" name="email_address" id="email_address" value="<?=$email_address?>" size="40" maxlength="50"><br />
			<br />
			<label for="password"><span style="color:red">*</span> Password:</label>
			<input type="password" name="password" id="password" value="<?=$password?>" maxlength="20"><br />
			<br />
			<label for="password_confirm"><span style="color:red">*</span> Confirm Password:</label>
			<input type="password" name="password_confirm" id="password_confirm" value="<?=$password?>" maxlength="20"><br />
		</fieldset>
		<br />
		<div align="center"><input type="image" src="/images/btn-create-account.png" name="btnSubmit" /></div>
	</form></div></div>
	<br />

	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
</body>
</html>