<?
	require('../global.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title>HDTV Magazine - Thank you</title>
	<?require(BASE_DIR .'/includes/common_header.php');?>
</head>
<body>
	<?
		include(BASE_DIR .'/includes/tracker.php');
		include(BASE_DIR .'/includes/body_header.php');
	?>
	
	<h1>Thank You!</h1>
	<table class="bare"><tr>
		<td style="vertical-align:top">
			<img src="/images/note-thanks.jpg" style="padding-right:10px">
		</td><td>
			<b style="font-size:14pt">Thank you for participating in the HDTV Study!</b><br />
			<p>
				Upon completion of the study, you can find the results on our <a href="/studies/index.php">HDTV Studies</a> page. If you are subscribed to the <b>Study Notifications</b> email list,
				you will also receive an email when the results are available. If you want to ensure you are notified, registered members can verify their subscription preferences
				on their <a href="/profile-subscriptions.php">Subscription Profile</a>.
			</p><p>
				Please feel free to forward the URL below to anyone else you think might have something to say about their HDTV (or lack thereof):
				<a href="http://www.surveymonkey.com/s.asp?u=79032455355">http://www.surveymonkey.com/s.asp?u=79032455355</a>
			</p>
		</td>
	</tr></table>
	
	<?include(BASE_DIR .'/includes/body_footer.php');?>
</body>
</html>
