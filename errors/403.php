<?
	require('../global.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>HDTV Magazine - Forbidden</title>
	<? require(BASE_DIR .'/includes/common_header.php');?>
</head>
<body id="body_container">
	<?
		include(BASE_DIR .'/ads/leaderboard.php');
		include(BASE_DIR .'/includes/body_header.php');
	?>

	<h1>Forbidden</h1>

	<div id="right" style="display:table; float:right; margin:0 0 5px 20px; text-align:center;" align="center">
		<?include(BASE_DIR .'/ads/mrectangle.php');?>
		<br />
		<div align="center">
			<?include(BASE_DIR .'/ads/skyscraper.php');?>
		</div>
	</div>

	<div style="float:left; padding-right:5px">
		<img src="/images/test_pattern.gif" alt="Indian Head Test Pattern" align="left" />
	</div><div>
		<p>Our Apologies!</p>
		<p>
			You don't have permission to access <b><?=$_SERVER['REQUEST_URI']?></b> on this server.
			Please check the list of links below to see if you may have been looking for one of those pages.
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
		Please use our <a href="<?=URL_HELP_FEEDBACK?>">Feedback</a> form to let us know if we can assist you further.
		And please be sure to include the page/URL you are trying to access.
	</p>

	<?include(BASE_DIR .'/includes/body_footer.php');?>
</div></body>
</html>
