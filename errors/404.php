<?
	require('../global.php');

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - File Not Found</title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
</head>
<body><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>

	<h1>Sorry, we couldn't find the page you were looking for</h1>

	<div id="right" style="display:table; float:right; margin:0 0 5px 20px; text-align:center;" align="center">
		<? include(BASE_DIR .'/ads/mrectangle.php');?>
		<br />
	</div>

	<!--div class="infobox" style="float:none; width:50%">
		<img src="/images/i_bulletins.gif" align="left" / style="padding-right:10px"><b>Notice - 23 Sept, 2008 05:30am: </b>We experienced a server outage this morning
		that resulted in a permanent loss of data on our servers. The time period affected goes back to 26 July, 2008. We are working frantically to rebuild and restore
		this information. Please check back later, or click the <a href="/help/feedback.php">Feedback</a> link below to let us know of any information that may be missing.<br />
		<br />
		<b>If you created a user account</b> during this period, that information has been permanantly lost. Please <a href="/profile-create.php">register again</a>
		and <a href="/help/feedback.php">send us an email</a> ... we will do something to make it up to you!<br />
		<br />
		Thank you,<br />
		<br />
		Dale Cripps &amp; Shane Sturgeon<br />
		Publishers, HDTV Magazine
	</div-->

	<div style="float:left; padding-right:5px">
		<img src="/images/test_pattern.gif" alt="Indian Head Test Pattern" align="left" />
	</div><div>
		<p>Our Apologies!</p>
		<p>
			The page for which you are looking cannot be found.  Please check the list of links below to see if you may have been looking for one of those pages.
			You may also use the search tool below to find what you're looking for.
		</p>
		<p>Thank you,</p>
		<p>- Shane &amp; Dale</p>
	</div>

	<script type="text/javascript">
		var GOOG_FIXURL_LANG = 'en';
		var GOOG_FIXURL_SITE = 'http://www.hdtvmagazine.com/';
	</script>
	<script type="text/javascript" src="http://linkhelp.clients.google.com/tbproxy/lh/wm/fixurl.js"></script>

	<p>
		Please use our <a href="/help/feedback.php">Feedback</a> form to let us know if we can assist you further.
		And please be sure to include the page/URL you are trying to access.
	</p>

	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
</div></body>
</html>
