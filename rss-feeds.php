<?
	require('global.php');

	require(BASE_DIR .'/includes/doctype.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>HDTV Magazine - RSS Feeds</title>
	<meta name="description" content="RSS Feeds available from various parts of the HDTV Magazine website.">
	<meta name="keywords" content="hdtv rss feeds,hdtv,rss,feed,hd tv,high definition,high def tv,high definition television,high definition tv,xml feed,xml">
	<meta name="rating" content="general">
	<? require(BASE_DIR .'/includes/page_header.php'); ?>
	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Main Feed" href="http://feeds.hdtvmagazine.com/hdtv">
	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Main Feed" href="http://feeds.hdtvmagazine.com/hdtv-news">
	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Reviews" href="http://feeds.hdtvmagazine.com/hdtv-reviews">
	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Articles" href="http://feeds.hdtvmagazine.com/hdtv-articles">
	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins" href="http://feeds.hdtvmagazine.com/hdtv-bulletins">
	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Columns" href="http://feeds.hdtvmagazine.com/hdtv-columns">
	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Forum" href="http://feeds.hdtvmagazine.com/hdtv-forum">
	<!--link rel="alternate" type="application/rss+xml" title="HDTV Magazine Interviews" href="<?=URL_INTERVIEWS_RSS?>">
	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine History" href="<?=URL_HISTORY_RSS?>"-->
</head>
<body id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header-4.php');
	?>

	<h1>HDTV Magazine RSS Feeds</h1>

	<div id="right" style="float:right; margin:0 0 5px 5px; text-align:center;" align="center">
		<? include(BASE_DIR .'/ads/mrectangle.php'); ?>
		<br />
		<div align="center">
			<? include(BASE_DIR .'/ads/skyscraper.php'); ?>
		</div>
	</div>
	<div style="display:table">
		<p>
			All feeds are RSS 2.0 unless otherwise indicated.  If you would like an additional format to be made available, please <a href="<?=URL_HELP_FEEDBACK?>">let us know</a>.
		</p><!--p>
			If your newsreader is capable of importing an OPML file, you can get the entire list of feeds below here:
			<a href="/opml.php"><img src="/images/chicklet-opml.png" alt="OPML" align="absmiddle"></a>
		</p-->

		<div class="item" ><span class="corners-top"><span></span></span>
			<a href="http://feeds.feedburner.com/hdtv"><img src="http://feeds.feedburner.com/~fc/hdtv?bg=003f87&amp;fg=ffffff&amp;anim=0" alt="" align="right" width="88" height="26" /></a>
			<img src="<?=BASE_IMG_HOST?>/images/livemark.png" alt="XML" align="left" style="padding-right:2px;" width="16" height="16">
			<div class="label"><a href="http://feeds.hdtvmagazine.com/hdtv">HDTV Magazine Main Feed</a></div>
			- This is the main RSS feed for HDTV Magazine. It consists of the Articles, Reviews, Bulletins and Columns feeds listed below.
		<span class="corners-bottom"><span></span></span></div>

		<div class="item" ><span class="corners-top"><span></span></span>
			<a href="http://feeds.feedburner.com/hdtv-reviews"><img src="http://feeds.feedburner.com/~fc/hdtv-reviews?bg=003f87&amp;fg=ffffff&amp;anim=0" alt="" align="right" width="88" height="26" /></a>
			<img src="<?=BASE_IMG_HOST?>/images/livemark.png" alt="XML" align="left" style="padding-right:2px;" width="16" height="16">
			<div class="label"><a href="http://feeds.hdtvmagazine.com/hdtv-reviews">HDTV Magazine Reviews</a></div>
			- In-depth technical and consumer reviews on the latest high definition televisions (HDTV), programming and attached peripherals. Get our opinions on
			video quality, functions, cost and whether the extra features are worth the extra money.
		<span class="corners-bottom"><span></span></span></div>

		<div class="item" ><span class="corners-top"><span></span></span>
			<a href="http://feeds.feedburner.com/hdtv-bulletins"><img src="http://feeds.feedburner.com/~fc/hdtv-bulletins?bg=003f87&amp;fg=ffffff&amp;anim=0" alt="" align="right" width="88" height="26" /></a>
			<img src="<?=BASE_IMG_HOST?>/images/livemark.png" alt="XML" align="left" style="padding-right:2px;" width="16" height="16">
			<div class="label"><a href="http://feeds.hdtvmagazine.com/hdtv-bulletins">HDTV Magazine News Bulletins</a></div>
			- Breaking HDTV news and up-to-the-minute press releases from all the major high definition manufacturers: Sony, Samsung, LG, Philips, Sharp,
			Toshiba and more. Also find the latest from the programming providers like Dish Network, DirecTV, Comcast, Time Warner and others. Get it first here.
		<span class="corners-bottom"><span></span></span></div>

		<div class="item" ><span class="corners-top"><span></span></span>
			<a href="http://feeds.feedburner.com/hdtv-articles"><img src="http://feeds.feedburner.com/~fc/hdtv-articles?bg=003f87&amp;fg=ffffff&amp;anim=0" alt="" align="right" width="88" height="26" /></a>
			<img src="<?=BASE_IMG_HOST?>/images/livemark.png" alt="XML" align="left" style="padding-right:2px;" width="16" height="16">
			<div class="label"><a href="http://feeds.hdtvmagazine.com/hdtv-articles">HDTV Magazine Articles</a></div>
			- HDTV Magazine's Articles cover the hard topics in-depth. From 1080p to YPrPb, we've got it covered. Get the technical details on all the latest technologies:
			Blu-ray, IPTV, HDMI, Dolby TrueHD, DTS HD Master Audio, and more.
		<span class="corners-bottom"><span></span></span></div>

		<div class="item" ><span class="corners-top"><span></span></span>
			<a href="http://feeds.feedburner.com/hdtv-columns"><img src="http://feeds.feedburner.com/~fc/hdtv-columns?bg=003f87&amp;fg=ffffff&amp;anim=0" alt="" align="right" width="88" height="26" /></a>
			<img src="<?=BASE_IMG_HOST?>/images/livemark.png" alt="XML" align="left" style="padding-right:2px;" width="16" height="16">
			<div class="label"><a href="http://feeds.hdtvmagazine.com/hdtv-columns">HDTV Magazine Columns</a></div>
			- The latest thoughts from the greatest in the industry in our HDTV Columns. Currently featuring columns from The HT Guys (Ara Derderian and Braden Russell)
			called Newbie's Corner, a thought provoking daily by Terry Paullin call "Another Opinion" and Ed Milbourn'd "Ed's View". Keep up with the HD buzz.
		<span class="corners-bottom"><span></span></span></div>

		<div class="item" ><span class="corners-top"><span></span></span>
			<a href="http://feeds.feedburner.com/hdtv-news"><img src="http://feeds.feedburner.com/~fc/hdtv-news?bg=003f87&amp;fg=ffffff&amp;anim=0" alt="" align="right" width="88" height="26" /></a>
			<img src="<?=BASE_IMG_HOST?>/images/livemark.png" alt="XML" align="left" style="padding-right:2px;" width="16" height="16">
			<div class="label"><a href="http://feeds.hdtvmagazine.com/hdtv-news">HDTV Internet News</a></div>
			- Hand-picked sources of HDTV News. Categorized and ranked by HDTV experts.
		<span class="corners-bottom"><span></span></span></div>

		<div class="item" ><span class="corners-top"><span></span></span>
			<a href="http://feeds.feedburner.com/hdtv-forum"><img src="http://feeds.feedburner.com/~fc/hdtv-forum?bg=003f87&amp;fg=ffffff&amp;anim=0" alt="" align="right" width="88" height="26" /></a>
			<img src="<?=BASE_IMG_HOST?>/images/livemark.png" alt="XML" align="left" style="padding-right:2px;" width="16" height="16">
			<div class="label"><a href="http://feeds.hdtvmagazine.com/hdtv-forum">HDTV Magazine Forum</a></div>
			- Topics posted to the HDTV Magazine Forum and HD Library, as they happen.
		<span class="corners-bottom"><span></span></span></div>

	</div>

	<? include(BASE_DIR .'/includes/body_footer-4.php'); ?>
</body>
</html>
