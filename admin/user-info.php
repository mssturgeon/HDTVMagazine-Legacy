<?
	require('../global.php');
	require(BASE_DIR .'/admin/user-overall_header.php');

	if (!access(ACCESS_ADMIN_ANY)) prompt_login(PHP_SELF);

	$id = isset($_GET['id']) ? $_GET['id'] : '';
	$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : '';
	$username = isset($_GET['username']) ? $_GET['username'] : '';
	$user_email = isset($_GET['user_email']) ? $_GET['user_email'] : '';
	$action = isset($_GET['action']) ? $_GET['action'] : '';
	if ($action == 'update user') {
		if (!access(ACCESS_ADMIN)) prompt_login(PHP_SELF);

		$subscriptions = is_array($_GET['subscriptions']) ? $_GET['subscriptions'] : array();
		$password = $_GET['password'];
		$md5_password = md5($password);
		$email_spam = ($_GET['email_spam'] == 1) ? 1 : 0;

		# Update user table
		$qry = "
		UPDATE user SET
			bb_id = '$_GET[bb_id]'
			,user_name = '$_GET[user_name]'
			,first_name = '$_GET[first_name]'
			,last_name = '$_GET[last_name]'
			,email_address = '$_GET[user_email]'
			,email_invalid = '$_GET[email_invalid]'
			,email_spam = $email_spam
			,email_preference = '$_GET[email_preference]'
			,password = '$password'
			,access = '$_GET[access]'
			,flg_autorenew = '$_GET[flg_autorenew]'
			,exp_pg = '$_GET[exp_pg]'
			,pricing_annual = '$_GET[pricing_annual]'
			,subscriptions = ". array_sum($subscriptions) ."
		WHERE id = $id";
		mQuery($qry);

		# Update USERS_TABLE table
		$qry = "UPDATE ". USERS_TABLE ." SET
			username = '$_GET[user_name]'
			,user_inactive_reason = '$_GET[user_inactive_reason]'
			,user_email = '$_GET[user_email]'
			,user_password = '$md5_password'
		WHERE user_id = ". $_GET['bb_id'];
		mQuery($qry);

	} elseif ($action == 'delete') {
		if (!access(ACCESS_ADMIN)) prompt_login(PHP_SELF);
		$bb_id = isset($_GET['bb_id']) ? $_GET['bb_id'] : '';

		delete_user($bb_id);

		js_replace('users.php');
		exit;
	} elseif ($action == 'add feeds') {
		if (!access(ACCESS_ADMIN)) prompt_login(PHP_SELF);
	}

	if ($id != '') {
		$where = "WHERE id = '$id'";
	} elseif ($user_id != '') {
		$where = "WHERE user_id = '$user_id'";
	} elseif ($username != '') {
		$where = "WHERE username = '$username'";
	} elseif ($user_email != '') {
		$where = "WHERE user_email = '$user_email'";
	}
	$sql = "SELECT u.*, bb.* FROM user u LEFT JOIN ". USERS_TABLE ." bb ON bb_id = user_id $where";
	$result = mQuery($sql);
	$user = mysql_fetch_assoc($result);

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - Edit User</title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
	<script language="javascript" type="text/javascript">
		function add_feeds() {
			frm.action.value = 'add feeds';
			frm.submit();
		}

		function deleteProfile() {
			if (confirm('You are about to delete this account, continue?')) {
				document.forms['frm'].elements['action'].value = 'delete';
				document.forms['frm'].submit();
			}
		}
	</script>
	<style>
		label.left {clear:left; float:left; padding:3px 2px; text-align:right; vertical-align:middle; width:125px}
		div.right {padding:4px; vertical-align:middle;}
	</style>
</head>
<body>

	<div style="float:right; width:400px" class="ad"><span class="corners-top"><span></span></span>
		<form name="frm_search" action="/admin/users.php" method="post">
			<input type="hidden" name="action" value="search">
			Search for:
			<input type="text" name="search" value="" class="inputText" size="20" />
			<input type="submit" name="btnSubmit" value="&nbsp;Search&nbsp;" class="inputButton" />
		</form>
	<span class="corners-bottom"><span></span></span></div>

	<h1>Details for User: <b><span class="primary_bold"><?=$user['user_name']?></span></b> (ID: <span class="primary_bold"><?=$user['id']?></span>)</h1>

	<div align="center"><div id="tab-container">
		<?=getTabHeader($tabs);?>
		<div class="tab-content" style="padding:10px">
			<form action="<?=PHP_SELF?>" method="post" name="frmProfile" id="frm" onsubmit="return validate()">
				<input type="hidden" name="action" value="update user">
				<input type="hidden" name="id" value="<?=$user['id']?>">
				<input type="hidden" name="bb_id" value="<?=$user['bb_id']?>">

				<table class="simple"><tr>
					<td>Active:</td>
					<td><input type="text" id="user_active" name="user_active" value="<?=$user['user_active']?>" class="inputText" size="1" /></td>
				</tr><tr>
					<td>bb_id:</td>
					<td><input type="text" id="bb_id" name="bb_id" value="<?=$user['bb_id']?>" class="inputText" size="10" /></td>
				</tr><tr>
					<td>Created:</td>
					<td><?=date('Y-m-d H:i:s', $user['user_regdate'])?></td>
				</tr><tr>
					<td>Modified:</td>
					<td><?=($user['modified'] == '0000-00-00 00:00:00' ? 'Never' : $user['modified'])?></td>
				</tr><tr>
					<td>Last Access:</td>
					<td><?=($user['user_lastvisit'] == 0 ? 'Never' : date('Y-m-d H:i:s', $user['user_lastvisit']))?></td>
				</tr><tr>
					<td>Time Zone Offset:</td>
					<td><?=round($user['time_zone_offset'] / 3600, 1)?> Hrs.</td>
				</tr><tr>
					<td>Prime Time:</td>
					<td><?=$user['prime_time']?></td>
				</tr><tr>
					<td>Subscription Exp:</td>
					<td><input type="text" id="exp_pg" name="exp_pg" value="<?=$user['exp_pg']?>" class="inputText" size="10"></td>
				</tr><tr>
					<td>Pricing, Annual:</td>
					<td><input type="text" id="pricing_annual" name="pricing_annual" value="<?=$user['pricing_annual']?>" class="inputText" size="10"></td>
				</tr><tr>
					<td>Access:</td>
					<td><input type="text" id="access" name="access" value="<?=$user['access']?>" class="inputText" size="4"></td>
				</tr></table>

					<label for="flg_autorenew" class="left">Auto-Renew:</label>
					<input type="text" id="flg_autorenew" name="flg_autorenew" value="<?=$user['flg_autorenew']?>" class="inputText" size="4"><br />
					<label for="user_name" class="left">Username:</label>
					<input type="text" id="user_name" name="user_name" value="<?=$user['user_name']?>" class="inputText" size="15" maxlength="50"> (phpBB: <?=$user['username']?>)<br />
					<label for="first_name" class="left">First Name:</label>
					<input type="text" id="first_name" name="first_name" value="<?=@$user['first_name']?>" class="inputText" size="15" maxlength="50"><br />
					<label for="last_name" class="left">Last Name:</label>
					<input type="text" id="last_name" name="last_name" value="<?=@$user['last_name']?>" class="inputText" maxlength="50"><br />
					<label for="user_email" class="left">Email Address:</label>
					<input type="text" id="user_email" name="user_email" value="<?=@$user['user_email']?>" class="inputText" size="40" maxlength="50"> (phpBB: <?=$user['user_email']?>)<br />
					<label for="email_invalid" class="left">Email Bounces:</label>
					<input type="text" id="email_invalid" name="email_invalid" value="<?=@$user['email_invalid']?>" class="inputText" size="2"><br />
					<label for="email_spam" class="left">Spam Complaint?:</label>
					<div class="right"><input type="checkbox" id="email_spam" name="email_spam" value="1" <?=$ck_email_spam?>></div>
					<label for="" class="left">Email Preference:</label>
					<div class="right">
						<input type="radio" name="email_preference" value="<?=EMAIL_PREF_TEXT?>" <?=$ck_email_preference_text?>>Text
						<input type="radio" name="email_preference" value="<?=EMAIL_PREF_HTML?>" <?=$ck_email_preference_html?>>HTML
					</div>
					<label for="" class="left">Password:</label>
					<input type="text" name="password" value="<?=@$user['password']?>" class="inputText" maxlength="50"><br />
					<label for="" class="left">md5 Password:</label>
					<div class="right"><?=md5($user['password'])?></div>
					<label for="" class="left">phpBB Password:</label>
					<div class="right"><?=$user['user_password']?></div>
				</fieldset>

				<div class="btn btn_green"><a href="#" onclick="document.forms[0].submit();">Save Changes</a><span></span></div>
			</form>
		</div>
	</div></div>

</body>
</html>