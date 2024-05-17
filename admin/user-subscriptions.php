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

	$checked = array();
	foreach ($SUB as $sub_value => $sub_label) {
		$checked[$sub_value] = ($user['subscriptions'] & $sub_value) ? 'CHECKED' : '';
	}

	// Set subscription preference
	$ck_email_preference_text = ($user['email_preference'] == EMAIL_PREF_TEXT) ? 'CHECKED' : '';
	$ck_email_preference_html = ($user['email_preference'] == EMAIL_PREF_HTML) ? 'CHECKED' : '';
	$ck_email_spam = ($user['email_spam'] == 1) ? 'CHECKED' : '';

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
					<legend>Subscriptions</legend>
					<?
						$x=1;
						foreach ($SUB as $sub_value => $sub_label) {
							echo '	<div style="float:left; width:25%">'.
								'<input type="checkbox" name="subscriptions[]" value="'. $sub_value .'" '. $checked[$sub_value] .'>'. $SUB[$sub_value] .
							'</div>';
						}
					?>
				</fieldset>

			</form>
		</div>
	</div></div>

	<? include(BASE_DIR .'/includes/body_footer.php');?>
</body>
</html>