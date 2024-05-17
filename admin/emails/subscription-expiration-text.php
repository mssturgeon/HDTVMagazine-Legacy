<?
	header('Content-Type: text/plain');
	
	// If nosession is passed, then use that, otherwise load global and use currently logged in user.
	if (isset($_GET['no_session'])) {
		require_once('/var/www/html/includes/constants.php');
		require_once('/var/www/html/includes/lib_common.php');
		require_once('/var/www/html/includes/lib_mysql.php');
	} else {
		require('../../global.php');
		if (!access(ACCESS_ADMIN_ANY)) prompt_login(PHP_SELF);
	}
	
	// Get User Info
	$id = isset($_GET['id']) ? $_GET['id'] : exit;
	$lead = isset($_GET['lead']) ? $_GET['lead'] : exit;
	$result = mQuery("SELECT exp_pg FROM user WHERE id = $id");
	$row = mysql_fetch_assoc($result);
	$lead_text = ($lead == 7) ? 'in 7 days' : 'tomorrow';
?>
Hello,

<?if ($lead != -1) {?>
This email is to remind you that your HDTV Magazine Premium membership will expire <?=$lead_text?> (<?=date('M jS', strtotime($row['exp_pg']))?>). If you elected to have your membership auto-renew when you subscribed a year ago, Paypal will take care of this process for you.

If you paid for your subscription through Paypal, you will likely receive one or more notices from them in the coming days regarding this renewal.

Thank you,
<?} else {?>
This email is to inform you that your HDTV Magazine Premium membership expired yesterday, <?=date('M jS', strtotime($row['exp_pg']))?>. If you wish to re-establish your subscription now, or at any time in the future, please use the following URL:
<?=FULL_URL_SUBSCRIBE?>

I would like to invite you to email us at feedback@hdtvmagazine.com (or simply reply to this message) and let us know why you chose not to renew.

Thank you for being a member.
<?}?>

-- Dale & Shane
HDTV Magazine
