<?
	require('../global.php');
	include(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - Help</title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
</head>
<body><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>

	<h1>How can we help you?</h1>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<h2>Formatting Problems?:</h2>
			<br />
			<p>
				If you are experiencing strange formatting problems, either throughout the website or in the program guide in particular,
				you may want to upgrade your browser to one that is more standards-compliant. The Firefox browser is one of the best available, and you
				can easily download it by clicking on the banner below.<br /><br />
				<a target="_blank" href='http://www.mozilla.com/en-US/?from=sfx&amp;uid=299364&amp;t=445'><img src='http://www.mozilla.org/contribute/buttons/110x32arrow_b.png' alt='Spread Firefox Affiliate Button' border='0' /></a>
			</p><br />

			<h2>Username/Password Recovery:</h2>
			<p>
				If you have misplaced or forgotten your username and/or password,
				you can recover them via the <a href="login-lookup.php">Username/Password Lookup</a> page.
			</p><br />

			<h2>Password Change:</h2>
			<p>
				If you would like to change your password, you can do so using the
				<!--a href="login-password-change.php">Password Change</a> form.-->
				<a href="/forum/ucp.php?i=profile&mode=reg_details">Password Change</a> screen.
			</p><br />

			<h2>Article Question/Comment:</h2>
			<p>
				If you have a question or comment about a posted article, please use the associated
				<a href="/forum/viewforum.php?f=22">Forum area</a>.
				This will ensure that the author of the article receives your question or comment.
			</p><br />

			<!--h2>Program Guide FAQ:</h2>
			If you are a Premium Member, and have questions about the Program Grid Guide,
			you can probably find the answer on the <a href="<?=URL_PROG_HELP_FAQ?>">Grid Guide FAQ</a> page.
			</p-->

			<h2>All Other Questions:</h2>
			<p>
				For anything else, please use our <a href="feedback.php">Feedback</a> form.
			</p><br />
		</td><td id="right">
			<div align="center" style="margin:5px 0;">
				<? include(BASE_DIR .'/ads/mrectangle.php');?>
			</div>
			<br />

			<div align="right">
				<? include(BASE_DIR .'/ads/skyscraper.php');?>
			</div>
		</td>
	</tr></table><br />

	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
</div></body>
</html>
