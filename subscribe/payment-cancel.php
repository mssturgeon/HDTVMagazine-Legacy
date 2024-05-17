<?
	require('../global.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>HDTV Magazine - Payment Cancelled</title>
	<?require(BASE_DIR .'/includes/common_header.php');?>
</head>
<body>
	<?
		include(BASE_DIR .'/includes/tracker.php');
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
	?>

				<div align="center"><h1>Payment Process Cancelled</h1></div>
				<p>
					Welcome back to the HDTV Magazine web site. Listed below are some common reasons people typically cancel the payment process.  Please read on and <a href="<?=URL_HELP_FEEDBACK?>?category=payment cancelled">let us know</a>
					if you still have a concern.
				</p><p>
					<b>Why did we choose Paypal?</b><br>
					In order to get our site up and running quickly, we chose to utilize Paypal and their built-in subscription management process.  This allowed us to launch the site very quickly and work with a large part of the market
					who already utilized Paypal for their online transaction processing. We realize that some of you may have concerns about using Paypal to submit your payment, and will eventually have a more direct method
					of processing credit cards.
				</p><p>
					<b>How else can I pay?</b><br>
					The only other payment method we have at this point is check or money order via US Mail. If you wish to pay in that manner, please make your check/money order out to "HDTV Magazine, Ltd." and mail it to the
					following address:<br>
					<br>
					HDTV Magazine, Ltd.<br>
					c/o Shane Sturgeon<br>
					499 N King St<br>
					Xenia, OH 45385-2207<br>
					<br>
					If you would like to suggest another method of payment, such as another online payment provider, please feel free to send us an email via our <a href="<?=URL_HELP_FEEDBACK?>">Feedback</a> form. We 
					would obviously work with you in whatever manner possible to make your purchase experience an easy one.
				</p>

		<?include(BASE_DIR .'/includes/body_footer.php');?>

</body>
</html>
