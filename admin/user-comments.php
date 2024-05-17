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
			<form action="<?=PHP_SELF?>" method="post" name="frm" id="frm" onsubmit="return validate()">
				<input type="hidden" name="action" value="update user">
				<input type="hidden" name="id" value="<?=$user['id']?>">
				<input type="hidden" name="bb_id" value="<?=$user['bb_id']?>">

				<fieldset>
					<legend>User History</legend>

					<label for="comment">Add Comments</label>
					<textarea id="comment" name="comment"></textarea>
					<div style="height:200px; overflow:auto;"><?
						$result = mQuery("SELECT * FROM user_comments WHERE user_id = $user[id] ORDER BY timestamp");
						if (mysql_num_rows($result) > 0) {
							echo '<table class="bare" cellpadding="3" cellspacing="0" style="width:100%">';
							while ($row = mysql_fetch_assoc($result)) {
								echo '<tr>'.
								'	<td style="width:125px" nowrap>'. gmdate('Y-m-d H:i:s', $row['timestamp'] + $user->data['time_zone_offset']) .'</td><td>'. stripslashes($row['comment']) .'</td>'.
								'</tr>';
							}
							echo '</table>';
						}
					?></div>
				</fieldset>

				<div class="btn btn_green"><a href="#" onclick="document.forms[0].submit();">Save Changes</a><span></span></div>
			</form>
		</div>
	</div></div>

	<? include(BASE_DIR .'/includes/body_footer.php');?>
</body>
</html>