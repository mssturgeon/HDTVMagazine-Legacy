<?
	/***********************************************
	* This page REALLY needs re-done to update only one table, the phpbb table, for those fields that it contains.
	************************************************/

	require('global.php');
	require(BASE_DIR .'/profile-overall_header.php');

	if (!$user->data['is_registered']) prompt_login($_SERVER['PHP_SELF']);

	$action = isset($_POST['action']) ? $_POST['action'] : '';
	if ($action == 'save') {
		$user_email = trim(strtolower($_POST['user_email']));
		$username = trim($_POST['username']);
		$user_id = $_POST['user_id'];

		# Make sure username is not already in use
		$result = mQuery("SELECT user_id FROM ". USERS_TABLE ." WHERE LOWER(username) = '". strtolower($username) ."' AND user_id <> ". $user->data['user_id']);
		if (mysql_num_rows($result) > 0) {
			js_back('The Username you entered ('. $username .') is already registered.\n\nIf you have forgotten your Username or Password, please use the Username/Password lookup links on the Help page (hdtvmagazine.com/help).');
			exit;
		}
		# Make sure email address is not already in use
		$qry = "SELECT user_id FROM ". USERS_TABLE ." WHERE user_email = '$user_email' AND user_id <> ". $user->data['user_id'];
		$result = mQuery($qry);
		if (mysql_num_rows($result) > 0) {
			js_back('The Email Address you provided ('. $user_email .') is already registered.\n\nIf you have forgotten your Username or Password, please use the Username/Password lookup page (located on the Help menu).');
			exit;
		}

		$time_zone_dst = ($_POST['time_zone_dst'] == "1") ? 1 : 0;
#		$pids = ($_POST['provider_id'] != '') ? implode(',', $_POST['provider_id']) : '';
		$lat = ($_POST['lat'] != '') ? round($_POST['lat'], 6) : '';
		$lon = ($_POST['lon'] != '') ? round($_POST['lon'], 6) : '';

		# compute banner ad code
		$hide_banner_ads = ($_POST['hide_banner_ads'] == '') ? 0 : $_POST['hide_banner_ads'];
		$hide_intext_ads = ($_POST['hide_intext_ads'] == '') ? 0 : $_POST['hide_intext_ads'];

		# Update user info
		$qry = "UPDATE user SET
			modified = '". gmdate("Y-m-d G:i:s") ."'
			, first_name = '$_POST[first_name]'
			, last_name = '$_POST[last_name]'
			, user_name = '$username'
			, email_address = '$user_email'
			, email_invalid = '$_POST[email_invalid]'
			, time_zone = '$_POST[time_zone]'
			, time_zone_text = '$_POST[time_zone_text]'
			, time_zone_dst = '$time_zone_dst'
			, dma_name = '$_POST[dma_name]'
			, street_address_1 = '$_POST[street_address_1]'
			, street_address_2 = '$_POST[street_address_2]'
			, city = '$_POST[city]'
			, state = '$_POST[state]'
			, zip = '$_POST[zipcode]'
			, lat = '$lat'
			, lon = '$lon'
			, hide_banner_ads = '$hide_banner_ads'
			, hide_intext_ads = '$hide_intext_ads'
		WHERE id = '$_POST[id]'";
		mQuery($qry);

		# Update Providers (DELETE before inserting in case they unchecked one or more providers)
		mQuery("DELETE FROM j_user_headend WHERE user_id = ". $user->data['user_id']);
		if (is_array($_POST['providers'])) {
			foreach($_POST['providers'] as $headend_id) {
				$sql = "
				INSERT IGNORE INTO j_user_headend (user_id, headend_id, provider_name)
				SELECT ". $user->data['user_id'] .", headend_id, CONCAT(system_name, ' (', headend_city, ')') FROM prog_headend WHERE headend_id = '$headend_id'";
				mQuery($sql);
			}
		}

		# Update phpBB table
		mQuery("UPDATE ". USERS_TABLE ." SET user_email = '$user_email', username = '$username' WHERE user_id = '$user_id'");

		# Update POSTS_TABLE to change username ... in case they edited it
		$old_user_name = $_POST['old_user_name'];
		mQuery("UPDATE ". POSTS_TABLE ." SET post_username = '$username' WHERE post_username = '$old_user_name'");

	} elseif ($action == 'delete') {
		require(BASE_DIR .'/includes/lib_admin.php');
		delete_user($user->data['user_id']);

		# Log user out
		js_replace('logout.php');
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

	$modified = date("j M Y g:ia (T)", strtotime($user->data['modified']));
	$ck_dst = ($user->data['time_zone_dst'] == 1) ? 'checked="checked"' : '';
	$ck_hide_intext_ads = ($user->data['hide_intext_ads'] == 1) ? 'checked="checked"' : '';
	$ck_hide_banner_ads = ($user->data['hide_banner_ads'] == 1) ? 'checked="checked"' : '';

	$premiumonly = 'disabled="disabled"';
	$premiumonly_class = 'premium_only';
	if (access(ACCESS_PREMIUM)) {
#		print_r($user->data);
		$premiumonly = '';
		$premiumonly_class = '';
	} else {
		$upgrade = '<a href="'. URL_SUBSCRIBE .'">Upgrade to Premium!</a>';
	}

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - My Account</title>
	<? require(BASE_DIR .'/includes/page_header.php'); ?>
	<script type="text/javascript">
		function validate() {
			document.getElementById("saved").style.display = 'none';
			with (document.frmProfile) {
				// Assign time_zone_text
				time_zone_text.value = time_zone[time_zone.selectedIndex].text.substr(1, 9);

				if ((username.value.search(/^\w+$/) == -1) | (username.value.length > 20)) {
					alert('Invalid Username. Please make sure your username consists only of letters and numbers and is no more than 20 characters.');
					username.focus();
					return false;
				}

				if (user_email.value.search(/^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/) == -1) {
					alert('Please enter your email address.');
					user_email.focus();
					return false;
				}

				return true;
			}
		}

		function reset_email() {
			with (document.frmProfile) {
				email_invalid.value = '0';
				user_email.disabled = false;
				user_email.select();
			}
		}

		function deleteProfile() {
			if (confirm('You are about to delete your account, continue?')) {
				frmProfile.action.value = 'delete';
				frmProfile.submit();
			}
		}

		function getGeo() {
			var oProfile = document.getElementById('frmProfile')
			var oGeo = document.getElementById('geoFrame')
			oGeo.src = '/geocode.php?a='+ escape(oProfile.street_address_1.value) +'&amp;b='+ escape(oProfile.city.value) +'&amp;c='+ escape(oProfile.state.value);
		}

		function mapIt() {
			window.open('/mapit.php', 'MapIt', 'top=0,left=0,width=780,height=575');
		}

		function getProvidersRequest(zipcode) {
			document.getElementById('provider_updating').style.display = '';
			return createAJAXRequest(document.location, 'action=get_providers&zipcode='+zipcode, 'getProviders(httpRequest, \''+ zipcode +'\')');
		}

		function getProviders(httpRequest, zipcode) {
			document.getElementById('provider_input').innerHTML = httpRequest.responseText;
//			document.getElementById('provider_prompt').style.display = 'none';
			document.getElementById('provider_input').style.display = '';
			document.getElementById('provider_info').style.display = 'table';
			setTimeout("document.getElementById('provider_updating').style.display = 'none';", 1500);
		}
	</script>
</head>
<body id="">
	<?
		include(BASE_DIR .'/includes/body_header-4.php');

		if ($action == 'save') {
			echo '<div class="alert_green" id="saved" align="center"><div>Settings saved successfully!</div></div>';
		} else {
			echo '<div id="saved"></div>';
		}
		if ($user->data[email_invalid] >= 3) {
			echo '<div class="alert_red" align="center"><div>There appears to be a problem with your email address. Please be sure it is correct.</div></div>';
		}
	?>

	<div align="center"><div id="tab-container">
		<?=getTabHeader($tabs);?>
		<div class="tab-content" style="padding:10px">
			<form action="<?=PHP_SELF?>" method="post" name="frmProfile" id="frmProfile" onsubmit="return validate()">
				<input type="hidden" name="action" value="save" />
				<input type="hidden" name="id" value="<?=$user->data['id']?>" />
				<input type="hidden" name="user_id" value="<?=$user->data['user_id']?>" />
				<input type="hidden" name="old_user_name" value="<?=$user->data['user_name']?>" />
				<input type="hidden" name="email_invalid" value="<?=$user->data['email_invalid']?>" />

				<fieldset>
					<legend>Site Settings</legend>
					<div class="infobox">Only <a href="/subscribe/index.php">Premium Members</a> may hide banner ads.
						Please note that this will not hide banner ads within the TVGuide grid.<?=$upgrade?></div>
					<label for="hide_intext_ads">
						<input type="checkbox" name="hide_intext_ads" id="hide_intext_ads" value="1" <?=$ck_hide_intext_ads?> />Hide In-text Ads
					</label><br />
					<label for="hide_banner_ads" class="<?=$premiumonly_class?>">
						<input type="checkbox" name="hide_banner_ads" id="hide_banner_ads" value="1" <?=$ck_hide_banner_ads .' '. $premiumonly?> />Hide Banner Ads
					</label>
				</fieldset>

				<fieldset>
					<legend>Personal Information</legend>
					<div class="infobox">
						Email Address is the only required field. We consider your Email Address as confidential information,
						and will never sell or rent it to anyone.
						<?if ($user->data[email_invalid] >= 3) {?>
							<br /><br /><div style="color:red">
								<b>Possible Invalid Email Address</b> (Our system has received three or more "bounced" messages in
								attempts to send email to this address.  No further attempts will be made to email you until it is updated.)
							</div>
						<?}?>
					</div>
					<label for="username">Username</label>
					<input type="text" name="username" id="username" value="<?=@$user->data[username]?>" size="15" maxlength="50" /><br />
					<br />
					<label for="first_name">First Name</label>
					<input type="text" name="first_name" id="first_name" value="<?=@$user->data[first_name]?>" size="15" maxlength="50" /><br />
					<br />
					<label for="last_name">Last Name</label>
					<input type="text" name="last_name" id="last_name" value="<?=@$user->data[last_name]?>" maxlength="50" /><br />
					<br />
					<label for="user_email"><span style="color:red">*</span> Email Address</label>
					<?if ($user->data[email_invalid] >= 3) {?>
						<input type="text" name="user_email" id="user_email" value="<?=@$user->data[user_email]?>" size="40" maxlength="50" DISABLED />
						<input type="button" class="inputButton" value="Update Email Address" onclick="reset_email()"><br>
					<?} else {?>
						<input type="text" name="user_email" id="user_email" value="<?=@$user->data[user_email]?>" size="40" maxlength="50" /><br />
					<?}?>
					<br />
					<label for="user_password">Password</label>
					<span id="user_password" style="padding-left:3px">Please visit the <a href="<?=URL_HELP_LOGIN_PASSWORD_CHANGE?>">Change Password</a> page to change your password<br /></span>
				</fieldset>

				<fieldset>
					<legend>Programming Information</legend>
					<label for="time_zone_text">Timezone</label>
					<input type="hidden" name="time_zone_text" id="time_zone_text" value="" />
					<select name="time_zone">
						<option value="-43200" <?@$user->data[time_zone] == -43200 ? print 'selected="selected"' : print ""?>>(GMT-12:00) Eniwetok, Kwajalein</option>
						<option value="-39600" <?@$user->data[time_zone] == -39600 ? print 'selected="selected"' : print ""?>>(GMT-11:00) Midway Island, Samoa</option>
						<option value="-36000" <?@$user->data[time_zone] == -36000 ? print 'selected="selected"' : print ""?>>(GMT-10:00) Hawaii</option>
						<option value="-32400" <?@$user->data[time_zone] == -32400 ? print 'selected="selected"' : print ""?>>(GMT-09:00) Alaska</option>
						<option value="-28800" <?@$user->data[time_zone] == -28800 ? print 'selected="selected"' : print ""?>>(GMT-08:00) Pacific Time (US &amp; Canada), Tijuana</option>
						<option value="-25200" <?@$user->data[time_zone] == -25200 ? print 'selected="selected"' : print ""?>>(GMT-07:00) Arizona</option>
						<option value="-25200" <?@$user->data[time_zone] == -25200 ? print 'selected="selected"' : print ""?>>(GMT-07:00) Mountain Time (US &amp; Canada)</option>
						<option value="-21600" <?@$user->data[time_zone] == -21600 ? print 'selected="selected"' : print ""?>>(GMT-06:00) Central Time (US &amp; Canada), Mexico City, Tegucigalpa, Saskatchewan</option>
						<option value="-18000" <?@$user->data[time_zone] == -18000 ? print 'selected="selected"' : print ""?>>(GMT-05:00) Eastern Time (US &amp; Canada), India (East), Bogota, Lima</option>
						<option value="-14400" <?@$user->data[time_zone] == -14400 ? print 'selected="selected"' : print ""?>>(GMT-04:00) Atlantic Time (Canada), Caracas, La Paz</option>
						<option value="-12600" <?@$user->data[time_zone] == -12600 ? print 'selected="selected"' : print ""?>>(GMT-03:30) Newfoundland</option>
						<option value="-10800" <?@$user->data[time_zone] == -10800 ? print 'selected="selected"' : print ""?>>(GMT-03:00) Brasilia, Buenos Aires, GeorgeTown</option>
						<option value="-7200" <?@$user->data[time_zone] == -7200 ? print 'selected="selected"' : print ""?>>(GMT-02:00) Mid-Atlantic</option>
						<option value="-3600" <?@$user->data[time_zone] == -3600 ? print 'selected="selected"' : print ""?>>(GMT-01:00) Azores, Cape Verdes Is.</option>
						<option value="0" <?@$user->data[time_zone] == 0 ? print 'selected="selected"' : print ""?>>(GMT-00:00) Greenwich Mean Time : Dublin, Edinburgh, Lisbon, London</option>
						<option value="3600" <?@$user->data[time_zone] == 3600 ? print 'selected="selected"' : print ""?>>(GMT+01:00) Berlin, Stockholm, Rome, Paris, Madrid</option>
						<option value="7200" <?@$user->data[time_zone] == 7200 ? print 'selected="selected"' : print ""?>>(GMT+02:00) Athens, Helsinki, Istanbul, Cairo, Eastern Europe, Israel</option>
						<option value="10800" <?@$user->data[time_zone] == 10800 ? print 'selected="selected"' : print ""?>>(GMT+03:00) Baghdad, Kuwait, Nairobi, Riyadh, Moscow, St. Petersburg</option>
						<option value="12600" <?@$user->data[time_zone] == 12600 ? print 'selected="selected"' : print ""?>>(GMT+03:30) Tehran</option>
						<option value="14400" <?@$user->data[time_zone] == 14400 ? print 'selected="selected"' : print ""?>>(GMT+04:00) Abu Dhabi, Muscat, Tbilisi</option>
						<option value="16200" <?@$user->data[time_zone] == 16200 ? print 'selected="selected"' : print ""?>>(GMT+04:30) Kabul</option>
						<option value="18000" <?@$user->data[time_zone] == 18000 ? print 'selected="selected"' : print ""?>>(GMT+05:00) Islamabad, Karachi, Tashkent</option>
						<option value="19800" <?@$user->data[time_zone] == 19800 ? print 'selected="selected"' : print ""?>>(GMT+05:30) Calcutta, Chennai, Mumbai, New Delhi</option>
						<option value="20700" <?@$user->data[time_zone] == 20700 ? print 'selected="selected"' : print ""?>>(GMT+05:45) Kathmandu</option>
						<option value="21600" <?@$user->data[time_zone] == 21600 ? print 'selected="selected"' : print ""?>>(GMT+06:00) Almaty, Dahka</option>
						<option value="23400" <?@$user->data[time_zone] == 23400 ? print 'selected="selected"' : print ""?>>(GMT+06:30) Rangoon</option>
						<option value="25200" <?@$user->data[time_zone] == 25200 ? print 'selected="selected"' : print ""?>>(GMT+07:00) Bangkok, Hanoi, Jakarta</option>
						<option value="28800" <?@$user->data[time_zone] == 28800 ? print 'selected="selected"' : print ""?>>(GMT+08:00) Beijing, Chongquing, Urumqi, Hong Kong, Perth, Singapore, Taipei</option>
						<option value="32400" <?@$user->data[time_zone] == 32400 ? print 'selected="selected"' : print ""?>>(GMT+09:00) Osaka, Sapporo Tokyo, Seoul</option>
						<option value="34200" <?@$user->data[time_zone] == 34200 ? print 'selected="selected"' : print ""?>>(GMT+09:30) Adelaide, Darwin</option>
						<option value="36000" <?@$user->data[time_zone] == 36000 ? print 'selected="selected"' : print ""?>>(GMT+10:00) Brisbane, Melbourne, Sydney, Guam</option>
						<option value="39600" <?@$user->data[time_zone] == 39600 ? print 'selected="selected"' : print ""?>>(GMT+11:00) Magadan, Solomon Is., New Caledonia</option>
						<option value="43200" <?@$user->data[time_zone] == 43200 ? print 'selected="selected"' : print ""?>>(GMT+12:00) Fiji, Kamchatka, Marshall Is., Wellington, Auckland</option>
					</select><br />

					<label for="time_zone_dst"><input type="checkbox" name="time_zone_dst" id="time_zone_dst" value="1" <?=$ck_dst?> />Automatically adjust for Daylight Savings Time</label>
					<br />

					<label for="dma_name">Local Market</label>
					<?=getSelectBox("SELECT DISTINCT dma_name, dma_name FROM prog_source WHERE dma_name <> '' ORDER BY dma_name", "dma_name", $user->data['dma_name'], " id=\"dma_name\"")?><br />
					<br />

					<div class="infobox">Enter your Zip/Postal Code and click "Get Providers" to show your list of available providers.</div>
					<label for="zip">Zip/Postal Code:</label>
					<input type="text" name="zipcode" id="zipcode" value="<?=$user->data['zip']?>" maxlength="11" size="10">
					<input type="button" value="Get Providers" onclick="getProvidersRequest(document.getElementById('zipcode').value)" /><br />
					<br />

					<div id="provider_info" style="display:none" class="infobox">Select your providers by checking each service you receive.
					If you are subscribing as a Premium Member, this will determine the initial stations available on your programming guide.</div>
					<label for="zip">Select your programming providers:</label>
					<div style="position:relative">
						<?
							# Get user selected providers
							$sql = "SELECT headend_id, provider_name from j_user_headend WHERE user_id = ". $user->data['user_id'];
							$result = mQuery($sql);
							while ($row = mysql_fetch_assoc($result)) {
								echo '<input type="checkbox" name="providers[]" value="'. $row['headend_id'] .'" checked="CHECKED"/>'. $row['provider_name'] .'<br />';
							}

							# If zipcode is provided, load additional providers from zip code
						?>
						<div id="provider_updating" class="updating" style="display:none; width:50%" align="center">
								<img alt="Please wait." src="/images/loading-big.gif" /><br />
								Updating...
						</div>
						<div id="provider_input" style="display:none"></div><br />
					</div>
				</fieldset>

				<fieldset>
					<legend>Address Information</legend>
					<div class="infobox">
						After entering your address, click the "Compute Lat/Long" button to lookup your location. If you receive an error that it couldn't find your address, click the
						"Map it" button to manually pinpoint your location. This will allow you to see distance and azimuth (bearing) information for your local HDTV channels
						on our <a href="/programming/broadcast.php">DMA map</a>.
					</div>

					<label for="street_address_1">Address:</label>
					<input type="text" name="street_address_1" id="street_address_1" value="<?=@$user->data[street_address_1]?>" maxlength="50" size="25" /><br />
					<input type="text" name="street_address_2" id="street_address_2" value="<?=@$user->data[street_address_2]?>" maxlength="50" size="25" /><br />
					<br />
					<label for="city">City:</label>
					<input type="text" name="city" id="city" value="<?=@$user->data[city]?>" maxlength="50" size="20" /><br />
					<br />
					<label for="state">State:</label>
					<?=getSelectBox("SELECT DISTINCT name, abbrev FROM tbl_states ORDER BY name", "state", $user->data['state'], ' id="state"');?><br />
					<br />
					<!--label for="zipcode">Zip/Postal Code:</label>
					<input type="text" name="zipcode" id="zipcode" value="<?=@$user->data['zipcode']?>" maxlength="11" size="10" /><br />
					<br /-->

					<label for="lat">Lat/Long:</label>
					<input type="text" name="lat" id="lat" value="<?=@$user->data[lat]?>" class="inputTextDisabled" style="width:7em;" readonly="readonly" /> /
					<input type="text" name="lon" value="<?=@$user->data[lon]?>" class="inputTextDisabled" style="width:7em;" readonly="readonly"	/>
					<input type="button" class="inputButton" value="Compute Lat/Long" onclick="getGeo()" />
					<input type="button" class="inputButton" value="&nbsp;&nbsp;Map It&nbsp;&nbsp;" onclick="mapIt()" /><br />
				</fieldset>

				<? if(access(ACCESS_ADMIN)) { // Testing Area of the Profile Form?>
					<fieldset>
						<legend>Admin Only</legend>
							<label for="show_record"><input type="checkbox" name="show_record" id="show_record" value="1" <?=$progCHECKED?> />Enable Programmability (For PC tuner cards)</label>
							<br />
					</fieldset>
				<? }?>

				<input type="submit" name="btnSubmit" value="&nbsp;Save Changes&nbsp;" class="inputButton">
			</form>
		</div>
	</div></div>

	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
	<iframe name="geo" id="geoFrame" style="width:100%;height:50px;display:none" src=""></iframe>
</body>
</html>
