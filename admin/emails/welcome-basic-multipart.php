<?
	define('BASE_DIR', '/var/www/html');
	require_once(BASE_DIR .'/includes/constants.php');
	require_once(BASE_DIR .'/includes/lib_common.php');
	require_once(BASE_DIR .'/includes/lib_mysql.php');

	# Include phpbb library for table constants
	define('IN_PHPBB', true);
	$phpbb_root_path = BASE_DIR .'/forum/';
	$phpEx = substr(strrchr(__FILE__, '.'), 1);
	include_once($phpbb_root_path . 'common.' . $phpEx);

	$base_url = 'http://'. SERVER_NAME;

	$id = isset($_GET['id']) ? $_GET['id'] : exit;
	$boundary = isset($_GET['boundary']) ? $_GET['boundary'] : '';
	$hide_unsub = true;

	$online_url = $_SERVER['SCRIPT_URI'] ."?id=$id";

	# Load admindata
	$result = mQuery("SELECT * FROM admin_settings WHERE id = 1");
	$admindata = mysql_fetch_assoc($result);

	# Get user information
	$qry = "SELECT username, password, user_email, user_id, user_actkey FROM ". USERS_TABLE ." pu, user u WHERE u.bb_id = pu.user_id AND id = '$id'";
	$result = mQuery($qry);
	$row = mysql_fetch_assoc($result);

if ($boundary != '') {
	echo '--'. $boundary ."\r\n".
	"Content-Type: text/plain; charset=\"us-ascii\"\r\n".
	"MIME-Version: 1.0\r\n".
	"Content-Transfer-Encoding: 7bit\r\n";
?>
Welcome to HDTV Magazine!

Your user name is <?=$row['username']?>


To verify your email address and complete your registration, please use this link:
<?=$base_url?>/activate.php?act_key=<?=$row['user_actkey']?>&user_id=<?=$row['user_id']?>


Enjoy,

-- Shane & Dale
Publishers, HDTV Magazine


------------------------------------------------------------
This email was sent to: <?=$row['user_email']?>


You received this email because an HDTV Magazine account was created using <?=$row['user_email']?>. If you did not sign up for HDTV Magazine, please ignore this email and the account will be closed.

For best viewing of future emails, please add <?=$admindata['email_reply_address']?> to your Safe Senders List or Address Book.

This email was sent by: <?=$admindata['company_name']?>

<?=$admindata['street_address']?>

<?=$admindata['city']?>, <?=$admindata['state_province']?>, <?=$admindata['zip_postal_code']?> <?=$admindata['country']?>

<?
	echo '--'. $boundary ."\r\n".
	"Content-Type: text/html; charset=\"us-ascii\"\r\n".
	"MIME-Version: 1.0\r\n".
	"Content-Transfer-Encoding: 7bit\r\n";
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>Please confirm your email address for HDTV Magazine</title>
	<base target="_blank">
	<style type="text/css">
	table td {font-size:9pt; font-family:Arial,sans-serif; margin:0; padding:0; text-align:left;}
	a:visited { color: #<?=PRIMARY_COLOR?> !important; }
	a:hover { color: #<?=PRIMARY_COLOR?> !important; }
	a:active { color: #<?=PRIMARY_COLOR?> !important; }
	h2 a:visited { color: #<?=SECONDARY_COLOR?> !important; }
	h2 a:hover { color: #<?=SECONDARY_COLOR?> !important; }
	h2 a:active { color: #<?=SECONDARY_COLOR?> !important; }
	.menu a:visited { color: white !important; }
	.menu a:hover { color: white !important; }
	.menu a:active { color: white !important; }
	</style>
</head>
<body>
	<table border="0" cellpadding="0" cellspacing="0" width="600" style="background:#fff; color:#000000; font-family:Arial,sans-serif; font-size:9pt; margin:10px; padding:0; width:600px;"><tr><td>
		<? include(BASE_DIR .'/includes/email_header-html.php');?>
		<table border="0" cellpadding="0" cellspacing="0" class="entry" style="border:0; margin:0; padding:0; width:100%">
			<tr><td padding="10" style="padding:10px;">
				<h1 style="color:#<?=PRIMARY_COLOR?>;">Welcome to HDTV Magazine!</h1>
				Your user name is <b><?=$row['username']?></b><br />
				<br />
				Please <a href="<?=$base_url?>/activate.php?act_key=<?=$row['user_actkey']?>&user_id=<?=$row['user_id']?>"  style="color:#<?=PRIMARY_COLOR?>" target="_blank">verify your email</a> so we know you're not a robot!<br />
				<br />
				If that link doesn't work, you can copy and paste the following URL into your browser to complete your registration:<br />
				<?=$base_url?>/activate.php?act_key=<?=$row['user_actkey']?>&user_id=<?=$row['user_id']?>
			</td></tr><tr><td>
				<br />
				Enjoy,<br />
				<br />
				-- Shane &amp; Dale<br />
				Publishers, <a style="color:#<?=PRIMARY_COLOR?>" href="http://www.hdtvmagazine.com/">HDTV Magazine</a>
				<br />
			</td></tr><tr><td>
				<hr>
				This email was sent to: <b><?=$row['user_email']?></b><br />
				<br />
				You received this email because an HDTV Magazine account was created using <b><?=$row['user_email']?></b>.
				If you did not sign up for HDTV Magazine, please ignore this email and the account will be closed.<br />
				<br />
				For best viewing of future emails, please add <b><?=$admindata['email_reply_address']?></b> to your Safe Senders List or Address Book.<br />
				<br />
				This email was sent by: <b><?=$admindata['company_name']?></b><br />
				<?=$admindata['street_address']?><br />
				<?=$admindata['city']?>, <?=$admindata['state_province']?>, <?=$admindata['zip_postal_code']?> <?=$admindata['country']?><br />
				<br />
			</td></tr>
		</table>
	</td></tr></table>
</body>
</html>
<?
	if ($boundary != '') {echo '--'. $boundary ."--\r\n";}
?>