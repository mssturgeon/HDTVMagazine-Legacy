<?
	require('global.php');
	require(BASE_DIR .'/profile-overall_header.php');
	if (!$user->data['is_registered']) prompt_login(PHP_SELF);

	$debug = isset($_GET['debug']) && access(ACCESS_ADMIN);
	$action = isset($_POST['action']) ? $_POST['action'] : '';
	if ($action == 'save') {
		$icons = is_array($_POST['icons']) ? array_sum($_POST['icons']) : 0;
		$options = is_array($_POST['options']) ? array_sum($_POST['options']) : 0;

		// Build Query
		$sql = "
		UPDATE user SET
			modified = '". gmdate("Y-m-d G:i:s") ."',
			icons = '$icons',
			options = '$options',
			guide_refresh = '{$_POST['guide_refresh']}',
			guide_width = '{$_POST['guide_width']}',
			prime_time = '{$_POST['prime_time']}'
		WHERE bb_id = '{$_POST['user_id']}'";
		mQuery($sql);
		if ($debug) echo "$sql\n";
	}

	$sql = "SELECT * FROM user WHERE bb_id = '". $user->data['user_id'] ."'";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);

	if ($debug) print_r($row);

	# Set icon defaults
	$ck_dd = ($row['icons'] & ICON_SHOW_DD) ? 'CHECKED' : '';
	$ck_cc = ($row['icons'] & ICON_SHOW_CC) ? 'CHECKED' : '';
	$ck_new = ($row['icons'] & ICON_SHOW_NEW) ? 'CHECKED' : '';
	$ck_lb = ($row['icons'] & ICON_SHOW_LB) ? 'CHECKED' : '';

	# Set option defaults
	$ck_show_sd = ($row['options'] & OPT_SHOW_SD) ? 'CHECKED' : '';
	$ck_show_hd_level = ($row['options'] & OPT_SHOW_HD_LEVEL) ? 'CHECKED' : '';
	$ck_show_popover = ($row['options'] & OPT_SHOW_POPOVER) ? 'CHECKED' : '';

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - Profile - My Guide</title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
	<script language="javascript" type="text/javascript">
		function init() {
			if (frmProfile.subscriber.value == 1) {
				e = document.frmProfile.elements;
				for (x=0;x<e.length;x++) {
					e[x].disabled = false;
				}
			}
		}
	</script>
</head>
<body onload="init()">
	<?
		include(BASE_DIR .'/includes/body_header-4.php');

		if ($action == 'save') {
			echo '<div class="alert_green" id="saved" align="center"><div>Settings saved successfully!</div></div>';
		} else {
			echo '<div id="saved"></div>';
		}
	?>

	<div align="center"><div id="tab-container">
		<?=getTabHeader($tabs);?>
		<div class="tab-content" style="padding:10px">
			<form action="<?=PHP_SELF?>" method="post" name="frmProfile">
				<input type="hidden" name="action" value="save">
				<input type="hidden" name="subscriber" value="<?(access(ACCESS_PREMIUM)) ? print "1" : print "0"?>">
				<input type="hidden" name="user_id" value="<?=$user->data['user_id']?>">

				<fieldset><legend>Membership Status</legend><?
					if (access(ACCESS_PREMIUM)) {
						$exparray = split('-', ''. $row['exp_pg']);
						$expdate = date('F jS, ', strtotime("$exparray[1]/$exparray[2]")) . $exparray[0];
						echo '<span style="float:right"><input type="button" name="btnCancel" value="Cancel Membership" class="inputButton" onclick="location.href = \'subscription-cancel.php\';" /></span>'.
						'<div class="primary_bold_12">Premium Member</div>';
						if ($row['flg_autorenew'] == 1) {
							echo 'Auto-renews on: <b>'. $row['exp_pg'] .'</b>';
						} else {
							echo 'Expires: <b>'. $expdate .'</b>';
						}
					} else {
						echo '<div class="primary_bold_12">Basic Member</div><br /> (<a href="/subscribe/index.php">Upgrade to Premium Membership</a>)';
					}
				?></fieldset>

				<fieldset>
					<legend>Guide Settings</legend>
					<div class="infobox">This allows you to specify what time to jump to when you click the "Primetime Tonight" link.</div>
					<label for="prime_time">My PrimeTime Begins:</label>
					<select name="prime_time" id="prime_time" DISABLED>
						<option value="17:00" <?@$row['prime_time'] == '17:00' ? print "SELECTED" : print ""?>>5:00pm</option>
						<option value="18:00" <?@$row['prime_time'] == '18:00' ? print "SELECTED" : print ""?>>6:00pm</option>
						<option value="19:00" <?@$row['prime_time'] == '19:00' ? print "SELECTED" : print ""?>>7:00pm</option>
						<option value="20:00" <?@$row['prime_time'] == '20:00' ? print "SELECTED" : print ""?>>8:00pm</option>
						<option value="21:00" <?@$row['prime_time'] == '21:00' ? print "SELECTED" : print ""?>>9:00pm</option>
					</select> (Local Time)<br /><br />

					<label for="guide_width">Guide Width:</label>
					<select name="guide_width" id="guide_width" DISABLED>
						<option value="4" <?@$row['guide_width'] == 4 ? print "SELECTED" : print ""?>>2 Hours</option>
						<option value="6" <?@$row['guide_width'] == 6 ? print "SELECTED" : print ""?>>3 Hours</option>
						<option value="8" <?@$row['guide_width'] == 8 ? print "SELECTED" : print ""?>>4 Hours</option>
						<option value="12" <?@$row['guide_width'] == 12 ? print "SELECTED" : print ""?>>6 Hours</option>
 						<option value="16" <?@$row['guide_width'] == 16 ? print "SELECTED" : print ""?>>8 Hours</option>
 					</select><br /><br />

					<label for="guide_refresh">Guide Refresh Rate:</label>
					<select name="guide_refresh" id="guide_Refresh" DISABLED>
						<option value="0" <?@$row['guide_refresh'] == 0 ? print "SELECTED" : print ""?>>None</option>
						<option value="300" <?@$row['guide_refresh'] == 300 ? print "SELECTED" : print ""?>>5 minutes</option>
						<option value="900" <?@$row['guide_refresh'] == 900 ? print "SELECTED" : print ""?>>15 minutes</option>
						<option value="1800" <?@$row['guide_refresh'] == 1800 ? print "SELECTED" : print ""?>>30 minutes</option>
						<option value="3600" <?@$row['guide_refresh'] == 3600 ? print "SELECTED" : print ""?>>60 minutes</option>
					</select><br /><br />
				</fieldset>

				<fieldset>
					<legend>Guide Options</legend>
					<label for="show_sd">
						<input type="checkbox" name="options[]" id="show_sd" value="<?=OPT_SHOW_SD?>" <?=$ck_show_sd?> DISABLED>
						Show Standard Definition Programming
					</label>

					<label for="show_hd_level">
						<input type="checkbox" name="options[]" id="show_hd_level" value="<?=OPT_SHOW_HD_LEVEL?>" <?=$ck_show_hd_level?> DISABLED>
						Show HD Level (720p / 1080i)
					</label>

					<label for="show_popover">
						<input type="checkbox" name="options[]" id="show_popover" value="<?=OPT_SHOW_POPOVER?>" <?=$ck_show_popover?> DISABLED>
						Show Popover Descriptions
					</label>
				</fieldset>

				<fieldset>
					<legend>Show Icons:</legend>
					<div class="infobox">
						These icons will always show in the popover description and the Program page. Enabling the checkboxes below will also show them on
						the main grid guide.	Be aware that this may stretch your grid guide horizontally, and is recommended only for horizontal screen resolutions
						above 1280x1024.
					</div>
					<label for="dd">
						<input type="checkbox" name="icons[]" id="dd" value="<?=ICON_SHOW_DD?>" <?=$ck_dd?> DISABLED>
						<img align="absmiddle" src="/images/dd.gif"> Dolby Digital
					</label>

					<label for="cc">
						<input type="checkbox" name="icons[]" id="cc" value="<?=ICON_SHOW_CC?>" <?=$ck_cc?> DISABLED>
						<img align="absmiddle" src="/images/cc.gif"> Closed Captioning
					</label>

					<label for="new">
						<input type="checkbox" name="icons[]" id="new" value="<?=ICON_SHOW_NEW?>" <?=$ck_new?> DISABLED>
						<img align="absmiddle" src="/images/new.gif"> New Episodes
					</label>

					<label for="lb">
						<input type="checkbox" name="icons[]" id="lb" value="<?=ICON_SHOW_LB?>" <?=$ck_lb?> DISABLED>
						<img align="absmiddle" src="/images/lb.gif"> Widescreen
					</label>
				</fieldset>

			 	<? if(access(ACCESS_ADMIN)) { // Testing Area of the Profile Form?>
				<fieldset>
					<legend>Admin Only</legend>
				</fieldset>
				<? } ?>

				<input type="submit" name="btnSubmit" value="&nbsp;Save&nbsp;" class="inputButton" DISABLED>
			</form>
		</div>
	</div></div>

	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
</body>
</html>
