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

	<div align="center"><div id="tab-container">
		<?=getTabHeader($tabs);?>
		<div class="tab-content" style="padding:10px">
			<form action="<?=PHP_SELF?>" method="post" name="frmProfile" id="frm" onsubmit="return validate()">

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
							$result = mQuery("SELECT * FROM join_user_station j LEFT JOIN tms_dgstatrec stat ON j.tf_station_num = stat.tf_station_num WHERE j.user_id = $user[id] ORDER BY priority");
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

			</form>
		</div>
	</div></div>

	<? include(BASE_DIR .'/includes/body_footer.php');?>
</body>
</html>