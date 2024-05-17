<?
	define('BASE_DIR', '/var/www/html');
	require(BASE_DIR .'/includes/constants.php');
	require(BASE_DIR .'/includes/lib_mysql.php');
	require(BASE_DIR .'/includes/lib_common.php');
	require(BASE_DIR .'/includes/lib_email.php');

	$emailed = isset($_GET['emailed']);
	$online_url = BASE_URL .'/daily.php';
	$today = date('l, F jS, Y');
	$base_url = BASE_URL; # For use in heredocs

	if ($emailed) {
		$base_img_host = BASE_URL; # Use same URL for images in email clients to prevent them from being blocked
	} else {
		$base_img_host = BASE_IMG_HOST;
		$subscribe = <<<EOT
<div class="important"><span class="corners-top"><span></span></span>
	<a href="$base_url/profile-create.php"><img src="$base_img_host/images/i_inbox.gif" align="left" style="padding-right:10px" /></a>
	<span class="label">Receive this page in your inbox daily:</span>
	<a href="$base_url/profile-create.php">Register Now</a> to receive the HDTV Magazine Daily
	via email as soon as it is published.
<span class="corners-bottom"><span></span></span></div>
EOT;
	}

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine Daily (<?=$today?>) on Facebook</title>
	<style>
		<? include(BASE_DIR .'/css/email.css'); ?>
	</style>
	<base target="_blank">
</head>
<body><div id="email_container">
	<?
		$tracking = '?utm_source=hdtvmagazine&utm_medium=email&utm_content=open&utm_campaign=daily';
#		include(BASE_DIR .'/includes/email_body_header.php');

		$section['articles'] = getBlogContent(1, 'articles', 'Articles', 'i_document.gif', 'articles', 1);
		$section['bulletins'] = getBlogContent(7, 'news', 'News Bulletins', 'i_bulletins.gif', 'bulletins', 1);
		$section['reviews'] = getBlogContent(8, 'reviews', 'Reviews', 'i_reviews.gif', 'reviews', 1);
		$section['podcasts'] = getBlogContent(9, 'podcasts', 'Podcasts', 'i_podcast.gif', '', 1);
		$section['columns'] = getBlogContent(10, 'columns', 'Columns', 'i_columns.gif', 'columns', 1);
		$section['forum'] = getForumContent(1);
		$section['news'] = getOtherNewsContent(1);
	?>

	<table cellpadding="0" cellspacing="0" border="0" style="margin:0; padding:0; width:100%"><tr><td style="vertical-align:top">
		<div id="heading">HDTV Magazine Daily</div>
		<div id="date"><?=$today?></div>
	</td><td style="width:225px">
		<div class="item"><span class="corners-top"><span></span></span>
			<div class="label">Follow Us:</div>
			<a href="http://twitter.com/HDTVMagazine" target="_blank" style="float:left; margin-right:10px"><img src="<?=$base_img_host?>/images/i_twitter_32.png" alt="Twitter" align="left" height="32" width="32" /></a>
			<a href="<?=BASE_URL?>/rss-feeds.php" style="margin-right:10px; float:left;"><img src="<?=$base_img_host?>/images/i_rss_32.png" alt="RSS" align="left" height="32" width="32" /></a>
			<a href="http://www.facebook.com/HDTVMagazine" target="_blank" style="float:left; margin-right:10px"><img src="<?=$base_img_host?>/images/i_facebook_32.png" alt="Facebook" align="left" height="32" width="32" /></a>
		<span class="corners-bottom"><span></span></span></div>
	</td></tr></table>

	<table cellpadding="0" cellspacing="0" border="0" style="margin:0; padding:0; width:100%"><tr><td style="width:200px; vertical-align:top">
		<div class="item"><span class="corners-top"><span></span></span>
			<h2>What's New...</h2>
			<ul>
				<?=$section['articles']['heading']?>
				<?=$section['columns']['heading']?>
				<?=$section['reviews']['heading']?>
				<?=$section['podcasts']['heading']?>
				<?=$section['bulletins']['heading']?>
				<?=$section['forum']['heading']?>
				<?=$section['news']['heading']?>
			</ul>
		<span class="corners-bottom"><span></span></span></div>
	</td><td style="padding-left:10px; vertical-align:top;">
		<?
			echo $subscribe;
		?>
	</td></tr></table>

	<?=$section['articles']['item']?>
	<?=$section['columns']['item']?>
	<?=$section['reviews']['item']?>
	<?=$section['podcasts']['item']?>
	<?=$section['bulletins']['item']?>
	<?=$section['forum']['item']?>
	<?=$section['news']['item']?>

</div></body>
</html>