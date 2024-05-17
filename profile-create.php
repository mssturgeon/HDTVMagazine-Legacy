<?
	require('global.php');
	require(BASE_DIR .'/includes/lib_paypal.php');
	include_once(BASE_DIR .'/forum/includes/functions_user.php');

	$action = isset($_POST['action']) ? $_POST['action'] : '';
	$type = isset($_POST['type']) ? $_POST['type'] : '';

	$mt = isset($_GET['mt']) ? $_GET['mt'] : '';
	$basic_checked = 'CHECKED';
	if ($mt == 'premium') {
		$basic_checked = '';
		$premium_checked = 'CHECKED';
	}

	if ($action == 'create') {
		$email_address = trim(strtolower($_POST['email_address']));

		// Make sure email address is valid and not already in use
		if (!preg_match('/^\w+((-\w+)|(\.\w+))*\@[a-z0-9]+((\.|-)[a-z0-9]+)*\.[a-z0-9]+$/', $email_address)) {
			js_back('The Email Address you provided ('. $email_address .') is not valid.\n\nPlease enter a valid email address.');
			exit;
		}
		$result = mQuery("SELECT id FROM user WHERE email_address = '$email_address'");
		if (mysql_num_rows($result) > 0) {
#			js_back('The Email Address you provided ('. $email_address .') is already registered.\n\nIf you have forgotten your Username or Password, please use the Username/Password lookup links on the Help page (hdtvmagazine.com/help).');
			js_redirect('/help/login-lookup.php?email_address='. $email_address, 'The Email Address you provided ('. $email_address .') is already registered.\n\n'.
			'We are redirecting you to the Username/Password lookup page so you can retrieve your current password.');
			exit;
		}

		# If the submission type is 'email_only', generate a safe user name, otherwise check for validity
		if ($type == 'email_only') {
			$x = 1;
			$user_name = strleft(strtolower(trim($email_address)), '@');
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
		} else {
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

		# Get password, user_id, and set other variables used for user creation
		$password = ($type == 'email_only') ? getRandomPassword() : addslashes(trim($_POST['password']));
//		$md5_password = md5($password);
//		$result = mQuery("SELECT MAX(user_id) AS total FROM ". USERS_TABLE);
//		$row = mysql_fetch_assoc($result);
//		$user_id = $row['total'] + 1;
		$subscriptions = is_array($_POST['subscriptions']) ? array_sum($_POST['subscriptions']) : 0;
		$user_actkey = substr(getRandString(true), 0, 6);
		$bb_tzo = $tzo / 3600;

		### NEW WAY - Add User
		# Get Group Name
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

		### OLD WAY - Insert user into USERS_TABLE
/*
		$qry = "
		INSERT INTO ". USERS_TABLE ." (user_id, username, user_regdate, user_password, user_email, user_inactive_reason, user_actkey)
		VALUES ($user_id, '$user_name', UNIX_TIMESTAMP(), '$md5_password', '$email_address', 1, '$user_actkey')";
		mQuery($qry);

		$qry = "INSERT INTO ". GROUPS_TABLE ." (group_name, group_description, group_single_user, group_moderator) VALUES ('', 'Personal User', 1, 0)";
		mQuery($qry);
		$group_id = mysql_insert_id();
		$qry = "INSERT INTO ". USER_GROUP_TABLE ." (user_id, group_id, user_pending) VALUES ($user_id, $group_id, 0)";
		mQuery($qry);
*/

		# Insert into user
		if ($type == 'email_only') {
			$tzo = (date('I')*HOURS) - 18000;
			$tzt = 'GMT-0'. (5 - (date('I')*HOURS)) .':00';
			$sql_user = "
			INSERT INTO user (bb_id, user_name, password, email_address, subscriptions)
			VALUES ($user_id, '$user_name', '$password', '$email_address', ". SUB_DAILY .")";
			mQuery($sql_user);
			$id = mysql_insert_id();
		} else {
			$tzo = $_POST['time_zone_offset'];
			$tzt = $_POST['time_zone_text'];
			$time_zone_dst = ($_POST['time_zone_dst'] == "1") ? 1 : 0;
			$sql_user = "INSERT INTO user (bb_id, user_name, password, first_name, last_name, email_address, time_zone_offset, time_zone_text, time_zone_dst, zip, subscriptions, email_preference, prime_time)".
			" VALUES ($user_id, '$user_name', '$password', '$_POST[first_name]', '$_POST[last_name]', '$email_address', '$tzo', '$tzt', $time_zone_dst,".
			" '$_POST[zipcode]', $subscriptions, $_POST[email_preference], '$_POST[prime_time]')";
			mQuery($sql_user);
			$id = mysql_insert_id();

			# Insert selected providers & add stations
			if (!empty($_POST['providers'])) {
				foreach($_POST['providers'] as $headend_id) {
					$sql = "
					INSERT IGNORE INTO j_user_headend (user_id, headend_id, provider_name)
					SELECT $user_id, headend_id, CONCAT(system_name, ' (', headend_city, ')') FROM prog_headend WHERE headend_id = '$headend_id'";
					mQuery($sql);

					# Only build stations if user is a Premium Member
					if ($type == 'premium') {
						$sql = "
						INSERT IGNORE INTO j_user_source (user_id, source_id, label, channel)
							SELECT DISTINCT $user_id, source_id, call_letters, channel_number
							FROM prog_lineup
							WHERE headend_id = '$headend_id'
							ORDER BY channel_number+0";
						mQuery($sql);
					}
				}
			}
		}

		if ($type == 'premium') {
			$welcome_email = BASE_URL .'/admin/emails/welcome-premium.php?id='. $id;
		} else {
			$welcome_email = BASE_URL .'/admin/emails/welcome-basic.php?id='. $id;
		}

		# Send Welcome Email and wait for activation to send the samples
		$sql = "
		INSERT INTO email_queue
			(send_to, subject, message, content_type, unsub_code)
			VALUES ('$email_address', 'Welcome to HDTV Magazine', '". addslashes(file_get_contents($welcome_email)) ."', 'text/plain', '')";
		mQuery($sql);

		if ($type == 'premium') { # Submit to Paypal
			$product_result = mQuery("SELECT item_name, a3, p3, t3, currency FROM hdtv_products WHERE item_number = '{$_POST['item_number']}'");
			if (mysql_num_rows($product_result) !== 1) {
				echo 'Invalid Item Number: '. $_POST['item_number'];
				exit;
			}
			$product = mysql_fetch_assoc($product_result);
			?>
				<html>
				<head>
					<script language="javascript" type="text/javascript">
						function init() {
							document.forms['paypal'].submit();
						}
					</script>
				</head>
				<body onload="init()">
					<form action="<?=PAYPAL_URL?>" method="post" name="paypal">
						<input type="hidden" name="cmd" value="_xclick-subscriptions">
						<input type="hidden" name="business" value="<?=PAYPAL_EMAIL?>">
						<input type="hidden" name="item_name" value="<?=$product['item_name']?>">
						<input type="hidden" name="item_number" value="<?=$_POST['item_number']?>">
						<input type="hidden" name="no_note" value="1">
						<input type="hidden" name="currency_code" value="<?=$product['currency']?>">
						<input type="hidden" name="a1" value="0.00">
						<input type="hidden" name="p1" value="1">
						<input type="hidden" name="t1" value="W">
						<input type="hidden" name="a3" value="<?=$product['a3']?>">
						<input type="hidden" name="p3" value="<?=$product['p3']?>">
						<input type="hidden" name="t3" value="<?=$product['t3']?>">
						<input type="hidden" name="sra" value="1">
						<input type="hidden" name="src" value="1">
						<input type="hidden" name="custom" value="<?=$id?>">
						<input type="hidden" name="return" value="<?=FULL_URL_WELCOME_PREMIUM?>?email=<?=$email_address?>">
						<input type="hidden" name="cancel_return" value="<?=BASE_URL?>/subscribe/payment-cancel.php">
					</form>
				</body>
				</html>
			<?
		} else {
			js_redirect(URL_WELCOME_BASIC ."?email=$email_address");
		}
		exit;
	} elseif ($action == 'get_providers') {
		$zipcode = $_POST['zipcode'];
		$sql = "
		SELECT z.headend_id, system_name, headend_city FROM prog_zipcode z, prog_headend h
		WHERE z.headend_id = h.headend_id
			AND zipcode = '$zipcode'";
		$result = mQuery($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$name = "{$row['system_name']} ({$row['headend_city']})";
			echo '<input type="checkbox" name="providers[]" value="'. $row['headend_id'] .'" />'. $name .'<br />';
		}
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
				// Assign time_zone_text
				time_zone_text.value = time_zone_offset[time_zone_offset.selectedIndex].text.substr(1, 9);

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

				if (item_number.value == '') {
					type.value = 'basic';
				} else {
					alert('You will be redirected to our payment page to complete your transaction.');
					type.value = 'premium';
				}
				return true;
			}
		}

		function change_item(oItem) {
			document.frmProfile.item_number.value = oItem.value;
		}

		function getProvidersRequest(zipcode) {
			document.getElementById('provider_updating').style.display = '';
			return createAJAXRequest(document.location, 'action=get_providers&zipcode='+zipcode, 'getProviders(httpRequest, \''+ zipcode +'\')');
		}

		function getProviders(httpRequest, zipcode) {
			document.getElementById('provider_input').innerHTML = httpRequest.responseText;
			document.getElementById('provider_prompt').style.display = 'none';
			document.getElementById('provider_input').style.display = '';
			document.getElementById('provider_info').style.display = 'table';
			setTimeout("document.getElementById('provider_updating').style.display = 'none';", 1500);
		}

		function init() {
			document.frmProfile.first_name.focus();
		}
	</script>
</head>
<body id="body_container" onload="init()">
	<? include(BASE_DIR .'/includes/body_header-4.php');

		# Get product prices
		$product_result = mQuery("SELECT item_number, a3 FROM hdtv_products WHERE item_number IN (2,3)");
		while ($product = mysql_fetch_assoc($product_result)) $amount[$product['item_number']] = $product['a3'];

		if ($email_provided) echo '<div class="alert_green" id="saved" align="center"><div>'.
		'Thank You!<br /><br />'.
		'You have been subscribed to the HDTV Magazine Daily and will be receiving email confirmation shortly. Please complete your registration by providing the following information.'.
		'</div></div>';
	?>

	<div align="center"><div style="text-align:left; width:728px"><form action="<?=$_SERVER['PHP_SELF']?>" method="post" name="frmProfile" onsubmit="return validate()">
		<input type="hidden" name="action" value="create">
		<input type="hidden" name="type" value="">
		<input type="hidden" name="item_number" value="">

		<fieldset>
			<legend>User Information</legend>
			<div class="infobox">Your personal information is kept confidential and will never be disclosed to third parties.
				For more information, please read our <a target="_blank" href="/about/privacy.php">Privacy Policy</a>.</div>
			<label for="first_name">First Name:</label>
			<input type="text" name="first_name" id="first_name" value="" size="15" maxlength="50"><br />
			<br />
			<label for="last_name">Last Name</label>
			<input type="text" name="last_name" id="last_name" value="" maxlength="50" /><br />
			<br />
			<label for="last_name"><span style="color:red">*</span> Email Address:</label>
			<input type="text" name="email_address" id="email_address" value="<?=$email_address?>" size="40" maxlength="50"><br />
			<br />
			<label for="user_name"><span style="color:red">*</span> UserName:</label>
			<input type="text" name="user_name" id="user_name" value="<?=$user_name?>" maxlength="50"><br />
			<br />
			<label for="password"><span style="color:red">*</span> Password:</label>
			<input type="password" name="password" id="password" value="<?=$password?>" maxlength="20"><br />
			<br />
			<label for="password_confirm"><span style="color:red">*</span> Confirm Password:</label>
			<input type="password" name="password_confirm" id="password_confirm" value="<?=$password?>" maxlength="20"><br />
		</fieldset>

		<fieldset>
			<legend>Local Time Information</legend>

			<label for="time_zone_offset">Timezone:</label>
			<input type="hidden" name="time_zone_text" id="time_zone_text" value="">
			<select name="time_zone_offset">
				<option value="-43200">(GMT-12:00) Eniwetok, Kwajalein</option>
				<option value="-39600">(GMT-11:00) Midway Island, Samoa</option>
				<option value="-36000">(GMT-10:00) Hawaii</option>
				<option value="-32400">(GMT-09:00) Alaska</option>
				<option value="-28800">(GMT-08:00) Pacific Time (US & Canada), Tijuana</option>
				<option value="-25200">(GMT-07:00) Arizona</option>
				<option value="-25200">(GMT-07:00) Mountain Time (US & Canada)</option>
				<option value="-21600">(GMT-06:00) Central Time (US & Canada), Mexico City, Tegucigalpa, Saskatchewan</option>
				<option value="-18000">(GMT-05:00) Eastern Time (US & Canada), India (East), Bogota, Lima</option>
				<option value="-14400">(GMT-04:00) Atlantic Time (Canada), Caracas, La Paz</option>
				<option value="-12600">(GMT-03:30) Newfoundland</option>
				<option value="-10800">(GMT-03:00) Brasilia, Buenos Aires, GeorgeTown</option>
				<option value="-7200">(GMT-02:00) Mid-Atlantic</option>
				<option value="-3600">(GMT-01:00) Azores, Cape Verdes Is.</option>
				<option value="0" SELECTED>(GMT-00:00) Greenwich Mean Time : Dublin, Edinburgh, Lisbon, London</option>
				<option value="3600">(GMT+01:00) Berlin, Stockholm, Rome, Paris, Madrid</option>
				<option value="7200">(GMT+02:00) Athens, Helsinki, Istanbul, Cairo, Eastern Europe, Israel</option>
				<option value="10800">(GMT+03:00) Baghdad, Kuwait, Nairobi, Riyadh, Moscow, St. Petersburg</option>
				<option value="12600">(GMT+03:30) Tehran</option>
				<option value="14400">(GMT+04:00) Abu Dhabi, Muscat, Tbilisi</option>
				<option value="16200">(GMT+04:30) Kabul</option>
				<option value="18000">(GMT+05:00) Islamabad, Karachi, Tashkent</option>
				<option value="19800">(GMT+05:30) Calcutta, Chennai, Mumbai, New Delhi</option>
				<option value="20700">(GMT+05:45) Kathmandu</option>
				<option value="21600">(GMT+06:00) Almaty, Dahka</option>
				<option value="23400">(GMT+06:30) Rangoon</option>
				<option value="25200">(GMT+07:00) Bangkok, Hanoi, Jakarta</option>
				<option value="28800">(GMT+08:00) Beijing, Chongquing, Urumqi, Hong Kong, Perth, Singapore, Taipei</option>
				<option value="32400">(GMT+09:00) Osaka, Sapporo Tokyo, Seoul</option>
				<option value="34200">(GMT+09:30) Adelaide, Darwin</option>
				<option value="36000">(GMT+10:00) Brisbane, Melbourne, Sydney, Guam</option>
				<option value="39600">(GMT+11:00) Magadan, Solomon Is., New Caledonia</option>
				<option value="43200">(GMT+12:00) Fiji, Kamchatka, Marshall Is., Wellington, Auckland</option>
			</select><br />

			<label for="time_zone_dst">
				<input type="checkbox" name="time_zone_dst" id="time_zone_dst" value="1">Automatically adjust for Daylight Savings Time
			</label>
			<br />

			<label for="prime_time">PrimeTime Begins:</label>
			<select name="prime_time">
				<option value="18:00">6:00pm</option>
				<option value="19:00">7:00pm</option>
				<option value="20:00" SELECTED>8:00pm</option>
				<option value="21:00">9:00pm</option>
			</select> (Local Time)<br />
			<br />
		</fieldset>

		<fieldset>
			<legend>Programming Information</legend>
			<div class="infobox">Enter your Zip/Postal Code and click "Get Providers" to show your list of available providers.</div>
			<label for="zip">Zip/Postal Code:</label>
			<input type="text" name="zipcode" id="zipcode" value="" maxlength="11" size="10">
			<input type="button" value="Get Providers" onclick="getProvidersRequest(document.getElementById('zipcode').value)" /><br />
			<br />

			<div id="provider_info" style="display:none" class="infobox">Select your providers by checking each service you receive.
			If you are subscribing as a Premium Member, this will determine the initial stations available on your programming guide.</div>
			<label for="zip">Select your programming providers:</label>
			<div style="position:relative">
				<div id="provider_prompt" style="color:#808080; padding-left:5px">Enter your Zip/Postal code above<br /><br /><br /></div>
				<div id="provider_updating" class="updating" style="display:none; width:50%" align="center">
						<img alt="Please wait." src="/images/loading-big.gif" /><br />
						Updating...
				</div>
				<div id="provider_input" style="display:none"></div><br />
			</div>
		</fieldset>

		<fieldset>
			<legend>Email Subscriptions</legend>
				<!--p><label for="daily_program_brief">
					<input type="checkbox" name="subscriptions[]" id="daily_program_brief" value="<?=SUB_GUIDE_BRIEF?>" <?=$disabled?> <?=$checked[SUB_GUIDE_BRIEF]?>>Daily Program Brief
					- <span style="font-weight:normal">The Daily Program Brief subscription will deliver a terse listing of your preferred HDTV programming on a
				daily basis. <b>Note:</b> This is an HTML-only publication for the time being. (Regular: 1/day) [<a href="<?=URL_GUIDE_BRIEF?>">Read Today's Issue</a>]</span>
				</label></p>

				<p><label for="daily_program_grid">
					<input type="checkbox" name="subscriptions[]" id="daily_program_grid" value="<?=SUB_GUIDE_GRID?>" <?=$disabled?> <?=$checked[SUB_GUIDE_GRID]?>>Daily Program Grid
					- <span style="font-weight:normal">The Daily Program Grid subscription will deliver a grid-based guide of your preferred HDTV programming on a daily basis.
					<b>Note:</b> This is an HTML-only publication. (Regular: 1/day) [<a href="<?=URL_GUIDE_GRID?>">Read Today's Issue</a>]</span>
				</label></p>

				<p><label for="sub_4">
					<input type="checkbox" name="subscriptions[]" id="sub_4" value="4" CHECKED>Daily Program Listing
					- <span style="font-weight:normal">The Daily Program Listing subscription will deliver your preferred HDTV programming on a daily basis via email. (Regular: 1/day) [ <a href="/programming/guide-listing.php">Today's Issue</a> ]</span>
				</label></p-->

				<p><label for="sub_262144">
					<input type="checkbox" name="subscriptions[]" id="sub_262144" value="<?=SUB_DAILY?>" <?=$checked[SUB_DAILY]?>>The HDTV Magazine Daily
					- <span style="font-weight:normal">The HDTV Magazine Daily subscription will deliver recent news, articles,
					reviews, and more on a daily basis. <b>Note:</b> This is an HTML-only publication for the time being. (Regular: 1/day)
					[<a href="/daily.php">Read Today's Issue</a>]</span>
				</label></p>

				<p><label for="sub_2097152">
					<input type="checkbox" name="subscriptions[]" id="sub_2097152" value="<?=SUB_WEEKLY?>" <?=$checked[SUB_WEEKLY]?>>The HDTV Magazine Weekly
					- <span style="font-weight:normal">The HDTV Magazine Weekly subscription will deliver recent news, articles,
					reviews, and more on a weekly basis. <b>Note:</b> This is an HTML-only publication for the time being. (Regular: 1/week)
					[<a href="/weekly.php">Read This Week's Issue</a>]</span>
				</label></p>

				<p><label for="sub_524288">
					<input type="checkbox" name="subscriptions[]" id="sub_524288" value="<?=SUB_PODCAST?>" <?=$checked[SUB_PODCAST]?>>HDTV Podcast
					- <span style="font-weight:normal">HDTV Magazine is now syndicating The HDTV Podcast, produced by The HT Guys:
						Ara Derderian &amp; Braden Russell. Be notified as soon as new episodes are posted. <b>Note:</b> This is an HTML-only publication
						for the time being. (Regular: 2/week)
					</span>
				</label></p>

				<p><label for="sub_32">
					<input type="checkbox" name="subscriptions[]" id="sub_32" value="32" <?=$checked[32]?>>Website Updates
					- <span style="font-weight:normal">The Website News/Updates subscription will alert you whenever significant features and/or content have been added to our website. (Occasional: 2-10/month)</span>
				</label></p>

				<p><label for="sub_256">
					<input type="checkbox" name="subscriptions[]" id="sub_256" value="256" <?=$checked[256]?>>New Articles
					- <span style="font-weight:normal">Be among the first to be notified of new HDTV Magazine Articles. (Regular: 1-2/week)</span>
				</label></p>

				<p><label for="sub_1048576">
					<input type="checkbox" name="subscriptions[]" id="sub_1048576" value="1048576" <?=$checked[1048576]?>>New Columns
					- <span style="font-weight:normal">Be among the first to be notified of new HDTV Magazine Columns. (Regular: 1-2/day)</span>
				</label></p>

				<p><label for="sub_65536">
					<input type="checkbox" name="subscriptions[]" id="sub_65536" value="65536" <?=$checked[65536]?>>New Reviews
					- <span style="font-weight:normal">Be among the first to be notified of new HDTV Magazine Reviews. (Regular: 1-2/week)</span>
				</label></p>

				<p><label for="sub_128">
					<input type="checkbox" name="subscriptions[]" id="sub_128" value="128" <?=$checked[128]?>>News Bulletins
					- <span style="font-weight:normal">Bulletins and selected press releases about particularly important products and programming. (Regular: 1-2/week)</span>
				</label></p>

				<p><label for="sub_32768">
					<input type="checkbox" name="subscriptions[]" id="sub_32768" value="32768" <?=$checked[32768]?>>Daily Forum Updates
					- <span style="font-weight:normal">The Daily Forum Update subscription will send you a daily email summarizing new topics and new replies within our <a href="/forum/index.php">HDTV Forum</a>. (Regular: 1/day)</span>
				</label></p>

				<p><label for="sub_131072">
					<input type="checkbox" name="subscriptions[]" id="sub_131072" value="131072" <?=$checked[131072]?>>Study Notifications
					- <span style="font-weight:normal">The Study Notification subscription will alert you when new <a href="/studies/index.php">HDTV-Related Studies</a> are launched, or when their results are available. (Sporadic: 1-2/month)</span>
				</label></p>

				<p><label for="sub_2">
					<input type="checkbox" name="subscriptions[]" id="sub_2" value="2" <?=$checked[2]?>>New Stations
					- <span style="font-weight:normal">The New Stations subscription will alert you whenever new stations are added to either the National listing, or to the Market selected in your profile. (Occasional: 5-10/month)</span>
				</label></p>

				<p><label for="sub_8">
					<input type="checkbox" name="subscriptions[]" id="sub_8" value="8" <?=$checked[8]?>>Broadcast Announcements
					- <span style="font-weight:normal">The Broadcast Messages subscription will notify you whenever a broadcast message is sent by the website administrator.	This will generally only be done in emergency situations, or to notify you of very significant additions or enhancements to the website. (Sporadic: 1-3/month)</span>
				</label></p>

				<p><label for="sub_16">
					<input type="checkbox" name="subscriptions[]" id="sub_16" value="16" <?=$checked[16]?>>Product Updates &amp; Special Offers
					- <span style="font-weight:normal">Be the first to be notified of new products and special offers are available. (Sporadic: 1-3/month)</span>
				</label></p>

				<p><label for="sub_64">
					<input type="checkbox" name="subscriptions[]" id="sub_64" value="64" <?=$checked[64]?>>Events
					- <span style="font-weight:normal">Event Notifications will notify you of newly announced and upcoming HDTV-related events. (Sporadic: 1-2/month)</span>
				</label></p>

				<?
#						foreach ($SUB_DESC as $sub_value => $sub_desc) {
#							echo '					<p><label for="sub_'. $sub_value .'">'."\n".
#							'						<input type="checkbox" name="subscriptions[]" id="sub_'. $sub_value .'" value="'. $sub_value .'" '. $checked[$sub_value] .'>'. $SUB[$sub_value] ."\n".
#							'						- <span style="font-weight:normal">'. $sub_desc .'</span>'."\n".
#							"					</label></p>\n";
#						}
				?>
			<span style="font-weight:bold">Email Preference:</span>
			<input type="radio" name="email_preference" value="0">Text
			<input type="radio" name="email_preference" value="1" CHECKED>HTML
		</fieldset>

		<fieldset>
			<legend>Membership Options</legend>
				<p>Our Basic Membership is free, and includes access to almost everything on our website, including each of the email subscription
				types listed above. Choose to upgrade to a Premium Membership and you'll have the option of hiding banner advertisements site-wide
				and receive a 10% discount off of everything in our <a target="_blank" href="/hdstore">HD Store</a>.
				</p>
				<div align="center">
					<div class="primary_bold">Your Premium Membership includes a free 7-day trial.  No obligation.</div>
					<input type="radio" name="item_select" onchange="change_item(this)" value="3" <?=$premium_checked?>>Annual Premium Membership - <?printf('$%01.2f', $amount[3])?><br>
					<input type="radio" name="item_select" onchange="change_item(this)" value="2">Monthly Premium Membership - <?printf('$%01.2f', $amount[2])?><br>
					<input type="radio" name="item_select" onchange="change_item(this)" value="" <?=$basic_checked?>>Basic Membership - Free!
				</div>
				<div class="box" style="padding:3px;margin:10px">
					The service options above utilize the Paypal payment service and you will be redirected there to complete your transaction.
					If you would rather not use Paypal to complete your transaction, we have <a target="_blank" href="/subscribe/payment-methods.php">other payment methods</a> available.
				</div>
		</fieldset>
		<div align="center"><input type="image" src="/images/btn-create-account.png" name="btnSubmit" /></div>
	</form></div></div>
	<br />

	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
</body>
</html>