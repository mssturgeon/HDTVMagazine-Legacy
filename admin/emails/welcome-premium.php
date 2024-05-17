<?
	header('Content-Type: text/plain');
	require_once('/var/www/html/includes/constants.php');
	require_once('/var/www/html/includes/lib_common.php');
	require_once('/var/www/html/includes/lib_mysql.php');

	# Include phpbb library for table constants
	define('IN_PHPBB', true);
	$phpbb_root_path = BASE_DIR .'/forum/';
	$phpEx = substr(strrchr(__FILE__, '.'), 1);
	include_once($phpbb_root_path . 'common.' . $phpEx);

	$id = isset($_GET['id']) ? $_GET['id'] : '';
	$qry = "SELECT username, password FROM ". USERS_TABLE ." pu, user u WHERE u.bb_id = pu.user_id AND id = '$id'";
	$result = mQuery($qry);
	$row = mysql_fetch_assoc($result);
?>
Welcome,

Thank you for joining HDTV Magazine.  An account has been created for you as follows:

Username: <?=$row['username']?>
Password: <?=$row['password']?>

*Please click the following link to activate your account:*
<?=FULL_URL_ACTIVATE?>?act_key=<?=$row['user_actkey']?>&user_id=<?=$row['user_id']?>


Your Premium Membership includes:
- The HDTV Magazine Daily (a daily update on the world of HDTV)
- Daily HDTV Forum Update (a daily update on recent forum conversations)
- New DTV Station Notifications (instantly learn of new HDTV services in your area)
- New Article Notifications (keep up with the latest articles from our respected authors)
- New Review Notifications (keep up with the latest product reviews from our review team)
- HDTV Magazine Website Updates (informs you of new website tools and features)
- HDTV Event Notifications (alerts you to important industry events)
- New Product Notifications (alerts you to the newest HDTV Magazine products)

You may opt-out from these lists at any time through your subscription profile (<?=FULL_URL_PROFILE_SUBSCRIPTIONS?>).

The following checklist will help you get started configuring your HDTV Magazine membership to best suit your needs:
1. Log in and customize your stations list: <?=FULL_URL_PROFILE_STATIONS?>
2. Edit your profile and add any additional information you wish, but please be sure to at least set your time zone: <?=FULL_URL_PROFILE?>
3. Change your password to something easier for you to remember: <?=FULL_URL_HELP_LOGIN_PASSWORD_CHANGE?>
4. Check your subscription preferences and verify that you are receiving only those updates you want in the format you want (Text or HTML): <?=FULL_URL_PROFILE_SUBSCRIPTIONS?>
5. We recommend that you add our email address to your address book so that future communications will not be lost (i.e. end up in your junk/bulk folder).

We are confident that you will enjoy your Premium Membership, and are committed to your satisfaction. If you experience any problems or have any recommendations for improving our services please use our convenient feedback form (<?=FULL_URL_HELP_FEEDBACK?>).

Enjoy,

-- Dale Cripps & Shane Sturgeon
HDTV Magazine
