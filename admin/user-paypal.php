<?
	require('../global.php');
	require(BASE_DIR .'/admin/user-overall_header.php');

	if (!access(ACCESS_ADMIN_ANY)) prompt_login(PHP_SELF);

	$id = isset($_GET['id']) ? $_GET['id'] : '';
	$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : '';
	$username = isset($_GET['username']) ? $_GET['username'] : '';
	$user_email = isset($_GET['user_email']) ? $_GET['user_email'] : '';

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
	<style>
		label.left {clear:left; float:left; padding:3px 2px; text-align:right; vertical-align:middle; width:125px}
		div.right {padding:4px; vertical-align:middle;}
	</style>
</head>
<body>
	<? include(BASE_DIR .'/includes/body_header-4.php');?>

	<div style="float:right; width:400px" class="ad"><span class="corners-top"><span></span></span>
		<form name="frm_search" action="/admin/users.php" method="post">
			<input type="hidden" name="action" value="search">
			Search for:
			<input type="text" name="search" value="" class="inputText" size="20" />
			<input type="submit" name="btnSubmit" value="&nbsp;Search&nbsp;" class="inputButton" />
		</form>
	<span class="corners-bottom"><span></span></span></div>

	<h1>Details for User: <b><span class="primary_bold"><?=$user['user_name']?></span></b> (ID: <span class="primary_bold"><?=$user['id']?></span>)</h1>

	<div align="center"><div id="tab-container" style="width:100%">
		<?=getTabHeader($tabs);?>
		<div class="tab-content" style="padding:10px" style="width:100%">
			<form action="<?=PHP_SELF?>" method="post" name="frmProfile" id="frm" onsubmit="return validate()">

				<div align="center"><div style="width:400px" class="ad"><span class="corners-top"><span></span></span>
					<input type="submit" name="btnSubmit" value="&nbsp;Save&nbsp;" class="inputButton">&nbsp;
					<?
						if ($user['access'] & ACCESS_PREMIUM) {
							$pp_result = mQuery("SELECT payer_email FROM paypal_ipn WHERE custom = $user[id] AND txn_type = 'subscr_signup' ORDER BY timestamp");
							$pp_row = mysql_fetch_assoc($pp_result);
							echo '<input type="button" class="inputButton" value="Cancel Subscription" onclick="window.open(\'https://history.paypal.com/us/cgi-bin/webscr?cmd=_history-search&search_type='. $pp_row['payer_email'] .'&search_first_type=email_alias&span=broad&for=4\', \'\', \'\')"></a>&nbsp;';
						}
					?>
					<input type="button" class="inputButton" value="Delete Account" onclick="deleteProfile()">&nbsp;
					<input type="button" value="Add Network Feeds" class="inputButton" onclick="add_feeds()">
				<span class="corners-bottom"><span></span></span></div></div>

				<fieldset>
					<legend>Paypal Transactions</legend>
					<!--div style="height:200px; overflow:auto;"--><table class="type1b" cellpadding="0" cellspacing="0" style="width:100%">
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
							$result = mQuery("SELECT * FROM paypal_ipn WHERE custom IN ($user[user_id], $user[id]) ORDER BY timestamp");
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
					</table><!--/div-->
				</fieldset>

			</form>
		</div>
	</div></div>

	<? include(BASE_DIR .'/includes/body_footer.php');?>
</body>
</html>