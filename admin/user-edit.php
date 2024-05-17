<?
	require('../global.php');
#	include_once(BASE_DIR .'/forum/includes/functions_user.php');
	require(BASE_DIR .'/includes/lib_admin.php');

	if (!access(ACCESS_ADMIN_ANY)) prompt_login(PHP_SELF);
	header('Cache-Control: no-store'); // HTTP/1.1

	$id = isset($_GET['id']) ? $_GET['id'] : '';
	$user_row_id = isset($_GET['user_id']) ? $_GET['user_id'] : '';
	$user_rowname = isset($_GET['username']) ? $_GET['username'] : '';
	$user_row_email = isset($_GET['user_email']) ? $_GET['user_email'] : '';
	$action = isset($_GET['action']) ? $_GET['action'] : '';
	if ($action == 'update user') {
		if (!access(ACCESS_ADMIN)) prompt_login(PHP_SELF);

		$subscriptions = is_array($_GET['subscriptions']) ? $_GET['subscriptions'] : array();
		$password = $_GET['password'];
		$md5_password = md5($password);
		$email_spam = ($_GET['email_spam'] == 1) ? 1 : 0;

		$exp_pg = ($_GET['exp_pg'] == '') ? 'NULL' : "'{$_GET['exp_pg']}'";

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
			,membership_freq = '$_GET[membership_freq]'
			,exp_pg = $exp_pg
			,pricing_annual = '$_GET[pricing_annual]'
			,subscriptions = ". array_sum($subscriptions) ."
		WHERE id = $id";
		mQuery($qry);

		# Update USERS_TABLE table
		$qry = "UPDATE ". USERS_TABLE ." SET
			username = '$_GET[user_name]'
			,user_type = '$_GET[user_type]'
			,user_inactive_reason = '$_GET[user_inactive_reason]'
			,user_email = '$_GET[user_email]'
			,user_password = '$md5_password'
		WHERE user_id = ". $_GET['bb_id'];
		mQuery($qry);

		# Check for Comment
		if ($_GET['comment'] != '') {
			mquery("INSERT INTO user_comments (user_id, timestamp, comment) VALUES ($id, ". time() .", '". addslashes($_GET['comment']) ."')");
		}

#		js_back();
#		exit;
	} elseif ($action == 'delete') {
		if (!access(ACCESS_ADMIN)) prompt_login(PHP_SELF);
		$bb_id = isset($_GET['bb_id']) ? $_GET['bb_id'] : '';

		delete_user($bb_id);

		js_replace('users.php');
		exit;
	} elseif ($action == 'add feeds') {
		if (!access(ACCESS_ADMIN)) prompt_login(PHP_SELF);

/*
		$qry = "INSERT IGNORE INTO join_user_station (user_id, tf_station_num, priority, label)
		SELECT $id, tf_station_num, 999, tf_station_name
		FROM tms_dgstatrec stat
		WHERE tf_station_num IN (28708,28711,28717,28719,32360,32362,33453)";
		mQuery($qry);
		js_back('Network Feeds Added!');
		exit;
*/
	}
	include(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - Edit User</title>
	<? require(BASE_DIR .'/includes/page_header.php'); ?>
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
<body id="body_container" style="margin:10px">
	<?
		if ($id != '') {
			$where = "WHERE id = '$id'";
		} elseif ($user_row_id != '') {
			$where = "WHERE user_id = '$user_row_id'";
		} elseif ($user_rowname != '') {
			$where = "WHERE username = '$user_rowname'";
		} elseif ($user_row_email != '') {
			$where = "WHERE user_email = '$user_row_email'";
		}
		$sql = "SELECT u.*, bb.* FROM user u LEFT JOIN ". USERS_TABLE ." bb ON bb_id = user_id $where";
		$result = mQuery($sql);
		$user_row = mysql_fetch_assoc($result);

		$checked = array();
		foreach ($SUB as $sub_value => $sub_label) {
			$checked[$sub_value] = ($user_row['subscriptions'] & $sub_value) ? 'CHECKED' : '';
		}

		// Set subscription preference
		$ck_email_preference_text = ($user_row['email_preference'] == EMAIL_PREF_TEXT) ? 'CHECKED' : '';
		$ck_email_preference_html = ($user_row['email_preference'] == EMAIL_PREF_HTML) ? 'CHECKED' : '';
		$ck_email_spam = ($user_row['email_spam'] == 1) ? 'CHECKED' : '';
	?>

	<h1>Details for User: <b><span class="primary_bold"><?=$user_row['user_name']?></span></b> (ID: <span class="primary_bold"><?=$user_row['id']?></span>)</h1>

	<form name="frm" action="<?=PHP_SELF?>" method="get">
		<input type="hidden" name="action" value="update user">
		<input type="hidden" name="id" value="<?=$user_row['id']?>">
		<input type="hidden" name="bb_id" value="<?=$user_row['bb_id']?>">

		<table class="simple" width="100%">
			<tr class="header"><td colspan=2>User Information</td></tr>
			<tr>
				<td>id:</td><td><?=$user_row['id']?></td>
			</tr><tr>
				<td>user_id:</td><td><?=$user_row['user_id']?></td>
			</tr><tr>
				<td>User Status:</td>
				<td><select name="user_type">
					<option value="0" <?@$user_row['user_type'] == 0 ? print 'selected="selected"' : print ""?>>Normal</option>
					<option value="1" <?@$user_row['user_type'] == 1 ? print 'selected="selected"' : print ""?>>Inactive</option>
					<option value="2" <?@$user_row['user_type'] == 2 ? print 'selected="selected"' : print ""?>>Ignore</option>
					<option value="3" <?@$user_row['user_type'] == 3 ? print 'selected="selected"' : print ""?>>Founder</option>
				</select></td>
			</tr><tr>
				<td>Inactive Reason:</td>
				<td><select name="user_inactive_reason">
					<option value="0" <?@$user_row['user_inactive_reason'] == 0 ? print 'selected="selected"' : print ""?>>None</option>
					<option value="1" <?@$user_row['user_inactive_reason'] == 1 ? print 'selected="selected"' : print ""?>>Register</option>
					<option value="2" <?@$user_row['user_inactive_reason'] == 2 ? print 'selected="selected"' : print ""?>>Profile</option>
					<option value="3" <?@$user_row['user_inactive_reason'] == 3 ? print 'selected="selected"' : print ""?>>Manual</option>
					<option value="4" <?@$user_row['user_inactive_reason'] == 4 ? print 'selected="selected"' : print ""?>>Remind</option>
				</select></td>
			</tr><tr>
				<td>Created: </td><td><?=date('Y-m-d H:i:s', $user_row['user_regdate'])?></td>
			</tr><tr>
				<td>Modified: </td><td><?=($user_row['modified'] == '0000-00-00 00:00:00' ? 'Never' : $user_row['modified'])?></td>
			</tr><tr>
				<td>Last Visit: </td><td><?=($user_row['user_lastvisit'] == 0 ? 'Never' : date('Y-m-d H:i:s', $user_row['user_lastvisit']))?></td>
			</tr><tr>
				<td>Last Trial: </td><td><?=($user_row['last_trial'])?></td>
			</tr><tr>
				<td>Time Zone Offset: </td><td><?=round($user_row['time_zone_offset'] / 3600, 1)?> Hrs.</td>
			</tr><tr>
				<td>Prime Time: </td><td><?=$user_row['prime_time']?></td>
			</tr><tr>
				<td>Membership: </td><td><input type="text" name="membership_freq" value="<?=$user_row['membership_freq']?>" class="inputText" size="2"></td>
			</tr><tr>
				<td>Subscription Exp: </td><td><input type="text" name="exp_pg" value="<?=$user_row['exp_pg']?>" class="inputText" size="10"></td>
			</tr><tr>
				<td>Pricing, Annual: </td><td><input type="text" name="pricing_annual" value="<?=$user_row['pricing_annual']?>" class="inputText" size="10"></td>
			</tr><tr>
				<td>Access: </td><td><input type="text" name="access" value="<?=$user_row['access']?>" class="inputText" size="4"></td>
			</tr><tr>
				<td>Auto-Renew: </td><td><input type="text" name="flg_autorenew" value="<?=$user_row['flg_autorenew']?>" class="inputText" size="4"></td>
			</tr><tr>
				<td>Username: </td><td><input type="text" name="user_name" value="<?=$user_row['user_name']?>" class="inputText" size="15" maxlength="50"> (phpBB: <?=$user_row['username']?>)</td>
			</tr><tr>
				<td>First Name: </td><td><input type="text" name="first_name" value="<?=@$user_row['first_name']?>" class="inputText" size="15" maxlength="50"></td>
			</tr><tr>
				<td>Last Name: </td><td><input type="text" name="last_name" value="<?=@$user_row['last_name']?>" class="inputText" maxlength="50"></td>
			</tr><tr>
				<td>Email Address: </td><td><input type="text" name="user_email" value="<?=@$user_row['user_email']?>" class="inputText" size="40" maxlength="50"> (phpBB: <?=$user_row['user_email']?>)</td>
			</tr><tr>
				<td>Email Bounces: </td><td><input type="text" name="email_invalid" value="<?=@$user_row['email_invalid']?>" class="inputText" size="2"></td>
			</tr><tr>
				<td>Spam Complaint?: </td><td><input type="checkbox" name="email_spam" value="1" <?=$ck_email_spam?>></td>
			</tr><tr>
				<td>Email Preference: </td>
				<td>
					<input type="radio" name="email_preference" value="<?=EMAIL_PREF_TEXT?>" <?=$ck_email_preference_text?>>Text
					<input type="radio" name="email_preference" value="<?=EMAIL_PREF_HTML?>" <?=$ck_email_preference_html?>>HTML
				</td>
			</tr><tr>
				<td>Password: </td><td><input type="text" name="password" value="<?=@$user_row['password']?>" class="inputText" maxlength="50"></td>
			</tr><tr>
				<td>md5 Password: </td><td><?=md5($user_row['password'])?></td>
			</tr><tr>
				<td>phpBB Password: </td><td><?=$user_row['user_password']?></td>
			</tr>
		</table>
		<br />

		<table class="simple" width="100%">
			<tr class="header"><td>User History</td></tr>
			<tr>
				<td>
					Add Comments:<br />
					<textarea id="comment" name="comment" style="width:50%"></textarea>
				</td>
			</tr><tr>
				<td><div style="height:200px; overflow:auto;"><?
					$result = mQuery("SELECT * FROM user_comments WHERE user_id = $user_row[id] ORDER BY timestamp");
					if (mysql_num_rows($result) > 0) {
						echo '<table class="bare" cellpadding="3" cellspacing="0" style="width:100%">';
						while ($row = mysql_fetch_assoc($result)) {
							echo '<tr>'.
							'	<td style="width:125px" nowrap>'. gmdate('Y-m-d H:i:s', $row['timestamp'] + $user_row->data['time_zone_offset']) .'</td><td>'. stripslashes($row['comment']) .'</td>'.
							'</tr>';
						}
						echo '</table>';
					}
				?></div></td>
			</tr>
		</table>
		<br />

		<table class="simple" width="100%">
			<tr class="header"><td colspan=2>Subscriptions</td></tr>
			<tr><td><?
				$x=1;
				foreach ($SUB as $sub_value => $sub_label) {
					echo '	<div style="float:left; width:25%">'.
						'<input type="checkbox" name="subscriptions[]" value="'. $sub_value .'" '. $checked[$sub_value] .'>'. $SUB[$sub_value] .
					'</div>';
				}
			?></td></tr>
		</table>

		<div align="center"><div style="width:400px" class="ad"><span class="corners-top"><span></span></span>
			<input type="submit" name="btnSubmit" value="&nbsp;Save&nbsp;" class="inputButton">&nbsp;
			<?
				if ($user_row['access'] & ACCESS_PREMIUM) {
					$pp_result = mQuery("SELECT payer_email FROM paypal_ipn WHERE custom = $user_row[id] AND txn_type = 'subscr_signup' ORDER BY timestamp");
					$pp_row = mysql_fetch_assoc($pp_result);
					echo '<input type="button" class="inputButton" value="Cancel Subscription" onclick="window.open(\'https://history.paypal.com/us/cgi-bin/webscr?cmd=_history-search&search_type='. $pp_row['payer_email'] .'&search_first_type=email_alias&span=broad&for=4\', \'\', \'\')"></a>&nbsp;';
				}
			?>
			<input type="button" class="inputButton" value="Delete Account" onclick="deleteProfile()">&nbsp;
			<input type="button" value="Add Network Feeds" class="inputButton" onclick="add_feeds()">
		<span class="corners-bottom"><span></span></span></div></div>

		<fieldset>
			<legend>Paypal Transactions</legend>
			<div style="height:200px; overflow:auto;"><table class="type1b" cellpadding="0" cellspacing="0" style="width:100%">
				<tr>
					<td class="type1b_header">Date</td>
					<td class="type1b_header" style="text-align:center">#</td>
					<td class="type1b_header" style="text-align:center">R</td>
					<td class="type1b_header">Pmt Status</td>
					<td class="type1b_header">Txn Type</td>
					<td class="type1b_header">Reason</td>
					<td class="type1b_header" style="text-align:right;padding-right:3px">Gross</td>
					<td class="type1b_header" style="text-align:right;padding-right:3px">Fee</td>
					<td class="type1b_header">Payer Email</td>
					<td class="type1b_header">Subscriber ID</td>
					<td class="type1b_header">Custom</td>
				</tr>
				<?
					$result = mQuery("SELECT * FROM paypal_ipn WHERE custom IN ($user_row[user_id], $user_row[id]) ORDER BY timestamp");
					while ($row = mysql_fetch_assoc($result)) {
						echo '<tr>'.
						'	<td class="type1b">'. date('Y-m-d g:ia', $row[timestamp]) .'</td>'.
						'	<td class="type1b" style="text-align:center">'. $row[item_number] .'</td>'.
						'	<td class="type1b" style="text-align:center">'. $row[recurring] .'</td>'.
						'	<td class="type1b">'. $row[payment_status] .'</td>'.
						'	<td class="type1b">'. $row[txn_type] .'</td>'.
						'	<td class="type1b">'. $row[reason_code] .'</td>'.
						'	<td class="type1b" style="text-align:right;padding-right:3px">$'. $row[mc_gross] .'</td>'.
						'	<td class="type1b" style="text-align:right;padding-right:3px">$'. $row[mc_fee] .'</td>'.
						'	<td class="type1b">'. $row[payer_email] .'</td>'.
						'	<td class="type1b">'. $row[subscr_id] .'</td>'.
						'	<td class="type1b">'. $row['custom'] .'</td>'.
						'</tr>';
					}
				?>
			</table></div>
		</fieldset>

	<fieldset>
		<legend>Preferred Stations (TMS)</legend>
		<div style="height:200px; overflow:auto;"><table class="type1b" cellpadding="0" cellspacing="0" style="width:100%">
			<tr>
				<td class="type1b_header">Priority</td>
				<td class="type1b_header">Label</td>
				<td class="type1b_header">Channel</td>
				<td class="type1b_header">#</td>
				<td class="type1b_header">Name</td>
				<td class="type1b_header">Call Sign</td>
				<td class="type1b_header">Affiliate</td>
				<td class="type1b_header">Market</td>
			</tr>
			<?
				$result = mQuery("SELECT * FROM join_user_station j LEFT JOIN tms_dgstatrec stat ON j.tf_station_num = stat.tf_station_num WHERE j.user_id = $user_row[id] ORDER BY priority");
				while ($row = mysql_fetch_assoc($result)) {
					echo '<tr>'.
					'	<td class="type1b">'. $row[priority] .'</td>'.
					'	<td class="type1b">'. $row[label] .'</td>'.
					'	<td class="type1b">'. $row[channel] .'</td>'.
					'	<td class="type1b">'. $row[tf_station_num] .'</td>'.
					'	<td class="type1b">'. $row[tf_station_name] .'</td>'.
					'	<td class="type1b">'. $row[tf_station_call_sign] .'</td>'.
					'	<td class="type1b">'. $row[tf_station_affil] .'</td>'.
					'	<td class="type1b">'. $row[dma_name] .'</td>'.
					'</tr>';
				}
			?>
		</table></div>
	</fieldset>

</body>
</html>
