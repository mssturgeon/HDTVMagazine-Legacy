<?
	require('../global.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>HDTV Magazine - Program Guide Features</title>
	<?require(BASE_DIR .'/includes/common_header.php');?>
	<meta name="description" content="HDTV Magazine Program Guide Features">
	<meta name="keywords" content="hdtv,hdtv programming,hdtv guide,hdtv schedule,hdtv listing,hdtv guides,hdtv schedules,hdtv listings,hdtv programs,hdtv programming guide,hdtv programming schedule,hdtv program,hd guide,hd programming,hd schedule,abc hdtv programming,cbs hdtv programming,digital tv programming,digital tv schedule,digital tv schedules,dtv listings,dtv program,dtv programing,dtv programming,dtv schedule,hd programing,hd programming,hd programs,high definition guide,high definition programing,high definition programming,high definition programs,high definition schedule,nbc hdtv programming,guide,news,sports,hdtv stations">
	<meta name="rating" content="general">
</head>
<body>
	<?
		include(BASE_DIR .'/includes/tracker.php');
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
	?>

	<h1>HDTV Program Guide Features</h1>
	
	Current Program Guide Features <span style="font:8pt;">(as of <?=date('M d, Y')?>)</span>
	<ul class="brownsquare">
		<li>Compact, easy to read design
		<li>Simple date & time guide browsing
		<li>Time zone customization
		<li>"Popover" program descriptions
		<li>Jump directly to your prime-time guide
		<li>Daily Digest feature available both via the website and email
		<li>One-of-a-kind HDTV Movie Guide
		<li>Order/prioritize your channel lineup
		<li>Assign your own channel labels/names
		<li>Optional email updates on new guide features
		<li>Optional email updates of new HDTV stations
		<li>Separate sports pages for every major sport
		<li>Various specialty and statistical pages
		<li>Display of both SD and HD digital programming
	</ul>
	
	<?include(BASE_DIR .'/includes/body_footer.php');?>
</body>
</html>
