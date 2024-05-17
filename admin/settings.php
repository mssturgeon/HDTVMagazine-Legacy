<?
	require('../global.php');
	if (!access(ACCESS_ADMIN)) prompt_login(PHP_SELF);

	$action = isset($_POST[action]) ? $_POST[action] : '';
	if ($action =='save') {
	  	$update = "UPDATE admin_settings SET ";

		# Exclude the following fields
		$exclude = array('btnSave_x', 'btnSave_y', 'action', 'email_in_progress', 'upload_in_progress');
		foreach ($_POST as $name=>$value) {
			if (!in_array($name, $exclude)) {
				$update .= "$name = '$value',";
			}
		}
		// Since the last character will always be a comma, we need to strip it
		$update = substr($update, 0, strlen($update)-1);

		$update .= " WHERE id = 1";
		mQuery($update);
	}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>HDTV Magazine - Admin Settings</title>
	<? require(BASE_DIR .'/includes/common_header.php');?>
	<script type="text/javascript">
		function validate() {
			document.getElementById('saved').style.display = 'none';
			return true;
		}
	</script>
</head>
<body>
	<?
		include(BASE_DIR .'/includes/body_header.php');
	?>

	<?
		if ($action == 'save') {
			echo '<div class="alert_green" id="saved" align="center"><div>Settings saved successfully!</div></div>';
		}
	?>
	<div align="center"><div style="width:6.5in; text-align:left;"><form method="post" action="<?=PHP_SELF?>" name="frmSettings" onsubmit="return validate()" enctype="multipart/form-data">
		<input type="hidden" name="action" value="save">

		<fieldset>
			<legend>General Settings</legend>
			<label for="company_name">Company Name</label>
			<input type="text" name="company_name" id="company_name" value="<?=$admindata[company_name]?>" /><br />
			<br />
			<label for="street_address">Street Address</label>
			<input type="text" name="street_address" id="street_address" value="<?=$admindata[street_address]?>" style="width:15em;" /><br />
			<br />
			<label for="city">City, State Zip</label>
			<input type="text" name="city" id="city" value="<?=$admindata[city]?>" size="10" />,
			<input type="text" name="state_province" id="state_province" value="<?=$admindata[state_province]?>" size="2" />
			<input type="text" name="zip_postal_code" id="zip_postal_code" value="<?=$admindata[zip_postal_code]?>" size="7"/><br />
			<br />
			<label for="country">Country</label>
			<input type="text" name="country" id="country" value="<?=$admindata[country]?>" /><br />
			<br />
			<label for="notify_ids">Notify IDs</label>
			<input type="text" name="notify_ids" id="notify_ids" value="<?=$admindata[notify_ids]?>" /><br />
			<br />
		</fieldset>

		<fieldset>
			<legend>Email Settings</legend>
			<label for="email_display_name">Display Name</label>
			<input type="text" name="email_display_name" id="email_display_name" value="<?=$admindata[email_display_name]?>" /><br />
			<br />
			<label for="feedback_email">Feedback Address</label>
			<input type="text" name="feedback_email" id="feedback_email" value="<?=$admindata[feedback_email]?>" size="30" /><br />
			<br />
			<label for="email_reply_address">Reply Address</label>
			<input type="text" name="email_reply_address" id="email_reply_address" value="<?=$admindata[email_reply_address]?>" size="30" /><br />
			<br />
			<label for="email_return_path">Return Path Address</label>
			<input type="text" name="email_return_path" id="email_return_path" value="<?=$admindata[email_return_path]?>" size="30" /><br />
			<br />
			<label for="email_smtp_host">SMTP Host</label>
			<input type="text" name="email_smtp_host" id="email_smtp_host" value="<?=$admindata[email_smtp_host]?>" size="15" /><br />
			<br />
			<label for="email_smtp_username">SMTP Username</label>
			<input type="text" name="email_smtp_username" id="email_smtp_username" value="<?=$admindata[email_smtp_username]?>" size="15" /><br />
			<br />
			<label for="email_smtp_password">SMTP Password</label>
			<input type="text" name="email_smtp_password" id="email_smtp_password" value="<?=$admindata[email_smtp_password]?>" size="15" /><br />
			<br />
			<label for="email_in_progress">Email in progress</label>
			<?if ($admindata[email_in_progress] == 1) {echo 'Yes';} else {echo 'No';}?><br />
			<br />
		</fieldset>

		<fieldset>
			<legend>Amazon Settings</legend>
			<label for="amazon_associates_id">Associates ID</label>
			<input type="text" name="amazon_associates_id" id="amazon_associates_id" value="<?=$admindata[amazon_associates_id]?>" /><br />
			<br />
			<label for="amazon_access_key">Access Key ID</label>
			<input type="text" name="amazon_access_key" id="amazon_access_key" value="<?=$admindata[amazon_access_key]?>" style="width:15em;" /><br />
			<br />
			<label for="amazon_secret_access_key">Secret Access Key ID</label>
			<input type="text" name="amazon_secret_access_key" id="amazon_secret_access_key" value="<?=$admindata[amazon_secret_access_key]?>" style="width:30em;" /><br />
			<br />
			<label for="amazon_developer_token">Developer Token</label>
			<input type="text" name="amazon_developer_token" id="amazon_developer_token" value="<?=$admindata[amazon_developer_token]?>" size="30" /><br />
			<br />
			<label for="amazon_update_window">Update Window</label>
			<input type="text" name="amazon_update_window" id="amazon_update_window" value="<?=$admindata[amazon_update_window]?>" style="width:5em;" /><br />
			<br />
		</fieldset>

		<fieldset>
			<legend>Twitter Login</legend>
			<label for="twitter_username">User ID</label>
			<input type="text" name="twitter_username" id="twitter_username" value="<?=$admindata[twitter_username]?>" /><br />
			<br />
			<label for="twitter_password">Password</label>
			<input type="text" name="twitter_password" id="twitter_password" value="<?=$admindata[twitter_password]?>" /><br />
			<br />
		</fieldset>

		<fieldset>
			<legend>Facebook API</legend>
			<label for="facebook_api_key">API Key</label>
			<input type="text" name="facebook_api_key" id="facebook_api_key" value="<?=$admindata[facebook_api_key]?>" style="width:25em;" /><br />
			<br />
			<label for="facebook_app_id">App ID</label>
			<input type="text" name="facebook_app_id" id="facebook_app_id" value="<?=$admindata[facebook_app_id]?>" /><br />
			<br />
			<label for="facebook_app_secret">App Secret</label>
			<input type="text" name="facebook_app_secret" id="facebook_app_secret" value="<?=$admindata[facebook_app_secret]?>" style="width:25em;" /><br />
			<br />
			<label for="facebook_session_key">Session Key</label>
			<input type="text" name="facebook_session_key" id="facebook_session_key" value="<?=$admindata[facebook_session_key]?>" style="width:25em;" /><br />
			<br />
			<label for="facebook_page_id">Page ID</label>
			<input type="text" name="facebook_page_id" id="facebook_page_id" value="<?=$admindata[facebook_page_id]?>" style="width:15em;" /><br />
			<br />
		</fieldset>

		<fieldset>
			<legend>Program Guide Settings</legend>
			<label for="tms_ftp_server">TMS FTP Server</label>
			<input type="text" name="tms_ftp_server" id="tms_ftp_server" value="<?=$admindata[tms_ftp_server]?>" /><br />
			<br />
			<label for="tms_ftp_user_name">TMS FTP Username</label>
			<input type="text" name="tms_ftp_user_name" id="tms_ftp_user_name" value="<?=$admindata[tms_ftp_user_name]?>" /><br />
			<br />
			<label for="tms_ftp_password">TMS FTP Password</label>
			<input type="text" name="tms_ftp_password" id="tms_ftp_password" value="<?=$admindata[tms_ftp_password]?>" /><br />
			<br />
			<label for="tbl_sked">tbl_sked</label>
			<input type="text" name="tbl_sked" id="tbl_sked" value="<?=$admindata[tbl_sked]?>" /><br />
			<br />
			<label for="tbl_stat">tbl_stat</label>
			<input type="text" name="tbl_stat" id="tbl_stat" value="<?=$admindata[tbl_stat]?>" /><br />
			<br />
			<label for="tbl_prog">tbl_prog</label>
			<input type="text" name="tbl_prog" id="tbl_prog" value="<?=$admindata[tbl_prog]?>" /><br />
			<br />
			<label for="tbl_credits">tbl_credits</label>
			<input type="text" name="tbl_credits" id="tbl_credits" value="<?=$admindata[tbl_credits]?>" /><br />
			<br />
			<label for="tbl_genre">tbl_genre</label>
			<input type="text" name="tbl_genre" id="tbl_genre" value="<?=$admindata[tbl_genre]?>" /><br />
			<br />
			<label for="tbl_guide">tbl_guide</label>
			<input type="text" name="tbl_guide" id="tbl_guide" value="<?=$admindata[tbl_guide]?>" /><br />
			<br />
			<label for="tbl_headend">tbl_headend</label>
			<input type="text" name="tbl_headend" id="tbl_headend" value="<?=$admindata[tbl_headend]?>" /><br />
			<br />
			<label for="tbl_history">tbl_history</label>
			<input type="text" name="tbl_history" id="tbl_history" value="<?=$admindata[tbl_history]?>" /><br />
			<br />
			<label for="tbl_lineup">tbl_lineup</label>
			<input type="text" name="tbl_lineup" id="tbl_lineup" value="<?=$admindata[tbl_lineup]?>" /><br />
			<br />
			<label for="tbl_mso">tbl_mso</label>
			<input type="text" name="tbl_mso" id="tbl_mso" value="<?=$admindata[tbl_mso]?>" /><br />
			<br />
			<label for="tbl_premise">tbl_premise</label>
			<input type="text" name="tbl_premise" id="tbl_premise" value="<?=$admindata[tbl_premise]?>" /><br />
			<br />
			<label for="tbl_program">tbl_program</label>
			<input type="text" name="tbl_program" id="tbl_program" value="<?=$admindata[tbl_program]?>" /><br />
			<br />
			<label for="tbl_rating">tbl_rating</label>
			<input type="text" name="tbl_rating" id="tbl_rating" value="<?=$admindata[tbl_rating]?>" /><br />
			<br />
			<label for="tbl_schedule">tbl_schedule</label>
			<input type="text" name="tbl_schedule" id="tbl_schedule" value="<?=$admindata[tbl_schedule]?>" /><br />
			<br />
			<label for="tbl_source">tbl_source</label>
			<input type="text" name="tbl_source" id="tbl_source" value="<?=$admindata[tbl_source]?>" /><br />
			<br />
			<label for="tbl_zipcode">tbl_zipcode</label>
			<input type="text" name="tbl_zipcode" id="tbl_zipcode" value="<?=$admindata[tbl_zipcode]?>" /><br />
			<br />
			<label for="upload_in_progress">Update in Prograss</label>
			<?if ($admindata[upload_in_progress] == 1) {echo 'Yes';} else {echo 'No';}?><br />
			<br />
		</fieldset>

		<div align="center"><input type="image" src="/images/btn-save-changes.png" name="btnSave" /></div>
	</form></div></div>

	<?require(BASE_DIR .'/includes/body_footer.php');?>
</body>
</html>
