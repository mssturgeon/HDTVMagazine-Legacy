<?
	require('global.php');

	$email_address = isset($_GET['email']) ? $_GET['email'] : $user->data['user_email'];

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - Welcome (Basic Membership)</title>
	<meta name="robots" content="noindex, nofollow, noarchive" />
	<? require(BASE_DIR .'/includes/page_header.php');?>
</head>
<body>
	<? include(BASE_DIR .'/includes/body_header-4.php');?>

	<h1>Welcome to HDTV Magazine</h1>
	<p>
		Thank you for joining HDTV Magazine. An account has been created for you and you should soon be receiving the login details at the email address
		 you provided (<b><?=str_replace('@', ' "at" ', $email_address)?></b>). In this email is a link which will confirm your email address and activate your account.
		 <div class="primary_bold" style="font-size:10pt">** You will not be able to log in until your account is activated. **</div>
	</p><p>
		Your free Basic Membership includes:<br>
		- The HDTV Magazine Daily (a daily update on the world of HDTV)<br>
		- New DTV Station Notifications (instantly learn of new HDTV services in your area)<br>
		- New Article Notifications (keep up with the latest articles from our respected authors)<br>
		- HDTV Magazine Website Updates (informs you of new website tools and features)<br>
		- HDTV Event Notifications (alerts you to important industry events)<br>
		- New Product Notifications (alerts you to the newest HDTV Magazine products)<br>
	</p><p>
		The following checklist will help you get started configuring your HDTV Magazine membership to best suit your needs:<br>
		1. Edit your <a href="<?=URL_PROFILE?>">profile</a> and add any additional information you wish. But please be sure to at least set your time zone.<br>
		2. <a href="<?=URL_HELP_LOGIN_PASSWORD_CHANGE?>">Change your password</a> to something easier for you to remember.<br>
		3. Check your <a href="<?=URL_PROFILE_SUBSCRIPTIONS?>">subscription preferences</a> to ensure you are receiving only those updates you want, and in the format you want (Text or HTML)<br>
		4. We recommend that you add our email address to your address book so that future communications will not be lost (i.e. end up in your junk/bulk folder).<br>
	</p><p>
		While we are confident that you will enjoy your free "Basic" Membership, our <a href="<?=URL_SUBSCRIBE?>">Premium Membership</a> has even more to offer. You can get more details <a href="<?=URL_SUBSCRIBE?>">here</a>.
	</p><p>
		If you experience any problems or have any recommendations for improving our services please use our convenient <a href="<?=URL_HELP_FEEDBACK?>">feedback form</a>.
	</p><p>
		Enjoy,
	</p><p>
		- Dale Cripps &amp; Shane Sturgeon<br>
		HDTV Magazine<br>
	</p>

	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
</body>
</html>
