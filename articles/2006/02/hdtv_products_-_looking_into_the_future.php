<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 289";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Rodolfo La Maestra'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 289 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category[placement_category_id];
	
	# Still need this to track page views. Used in the Links unit in the footer
	$google_links_channel = $author[channel];

	# Set variables based on entry type. NOTE: Podcasts has its own entry template, so it is not included amongst the choices below.
	switch (1) {
		case 1: # Articles
			$feed_name = 'hdtv-articles';
			$container = 'article_container';
			$sub_type = SUB_ARTICLES;
			$sub_label = 'Receive instant notification of new articles';
			$sub_desc_logged_in = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of new HDTV Magazine Articles via email as soon as they are published.';
			$sub_desc_anon = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of new HDTV Magazine Articles via email as soon as they are published.';
			break;
		case 4: # Interviews
			$feed_name = 'hdtv-interviews';
			break;
		case 5: # History
			$feed_name = 'hdtv-archive';
			break;
		case 6: # Test
			$container = 'article_container';
			$sub_type = 0;
			break;
		case 7: # Bulletins
			$google_links_channel = ''; # Don't count bulletins
			$feed_name = 'hdtv-news';
			$container = 'bulletin_container';
			$sub_type = SUB_BULLETINS;
			$sub_label = 'Receive instant notification of HDTV Bulletins';
			$sub_desc_logged_in = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of HDTV Bulletins via email as soon as they are published.';
			$sub_desc_anon = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of HDTV Bulletins via email as soon as they are published.';
			break;
		case 8: # Reviews
			$feed_name = 'hdtv-reviews';
			$container = 'article_container';
			$sub_type = SUB_REVIEWS;
			$sub_label = 'Receive instant notification of new reviews';
			$sub_desc_logged_in = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of new HDTV Magazine Reviews via email as soon as they are published.';
			$sub_desc_anon = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of new HDTV Magazine Reviews via email as soon as they are published.';
			break;
#		case 9: # Podcasts
		case 10: # Columns
			$feed_name = 'hdtv-columns';
			$container = 'article_container';
			$sub_type = SUB_COLUMNS;
			$sub_label = 'Receive instant notification of new columns';
			$sub_desc_logged_in = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of new HDTV Magazine Columns via email as soon as they are published.';
			$sub_desc_anon = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of new HDTV Magazine Columns via email as soon as they are published.';
			$about = 'HDTV Magazine Columns are written by various personalities within the HDTV industry. They are typically shorter than our standard <a href="/articles">Article</a> and quite often express the opinion of the author(s). And of course, opinions expressed by these authors are not necessarily those of HDTV Magazine.';
			break;
		default:
			$container = 'body_container';
			break;
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<?require(BASE_DIR .'/includes/common_header.php');?>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<meta name="generator" content="http://www.movabletype.org/" />
	<meta name="keywords" content="atsc qam, qam cablecard, hdmi hdcp, cablecard tuners, direct view, TTM, ttm, integrated, new, models, series, HDMI, hdmi, Series, dlp, lcd, LCD, DLP, line, cablecard, QAM, qam, atsc, ATSC, tuners" />
	<meta name="description" content="The following article originally appeared in HDTVetc magazine in their August 2004 issue. It contains product information that is likewise, dated to mid-2004. The products in this article were &quot;New&quot; when originally published and should obviously not be considered as such when reading today. Although this article has some historical value, the primary value is the analysis to reach a forecasted vision of future market conditions (which eventually came to pass). This assisted many consumers in making more informed purchasing decisions. Reading the Analysis and Conclusions section is almost like time-travel: The historic vision has now transformed itself into current events and conditions ... mostly (we are still waiting for some of them to happen)." />
	<title>HDTV Magazine Articles - HDTV Products - Looking Into the Future</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_products_-_looking_into_the_future';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('HDTV Products - Looking Into the Future'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/articles/2006/02/hdtv_products_-_looking_into_the_future.php";
		if ($author[img] != '' && 1 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Rodolfo La Maestra" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV Products - Looking Into the Future</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Rodolfo La Maestra</b><br />
				<?=$author_title?>
				Posted on <b>February  3, 2006</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Products & Equipment">Products & Equipment</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2006/02/hdtv_products_-_looking_into_the_future.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/articles/2006/02/hdtv_products_-_looking_into_the_future.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/articles/2006/02/hdtv_products_-_looking_into_the_future.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
				<span><img src="/images/save.gif" alt="Save Article" align="absmiddle" /><a target="_blank" href="<?=$save_url?>">Save</a></span>
				<span><img src="/images/email.gif" alt="Email Article" align="absmiddle" /><a href="<?=$email_url?>">Email</a></span>
				<span><img src="/images/print.png" alt="Print Article" align="absmiddle" /><a target="_blank" href="<?=$print_url?>">Print</a></span><br />
				<br /><br />
			</td>
		</tr>
	</table>
	<?if ($sub_type > 0 && ($userdata[subscriptions] & $sub_type) || $_SERVER[HTTP_USER_AGENT] == 'Googlebot') {} else {
		if ($userdata[session_logged_in]) {?>
			<div class="important"><span class="corners-top"><span></span></span>
				<img src="/images/i_inbox.gif" align="left" style="padding-right:10px" />
				<span class="label"><?=$sub_label?>:</span>
				<?=$sub_desc_logged_in?>
			<span class="corners-bottom"><span></span></span></div>
		<?} else {?>
			<div class="important"><span class="corners-top"><span></span></span>
				<img src="/images/i_inbox.gif" align="left" style="padding-right:10px" />
				<span class="label"><?=$sub_label?>:</span>
				<?=$sub_desc_anon?>
			<span class="corners-bottom"><span></span></span></div>
		<?}
	}?>
	<div>
		<div style="float:left; margin:0 5px 5px 0;"><?
			if ($digg_url == '') {
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2006/02/hdtv_products_-_looking_into_the_future.php&amp;phase=2&amp;title=HDTV%20Products%20-%20Looking%20Into%20the%20Future&amp;bodytext=The%20following%20article%20originally%20appeared%20in%20HDTVetc%20magazine%20in%20their%20August%202004%20issue.%20It%20contains%20product%20information%20that%20is%20likewise%2C%20dated%20to%20mid-2004.%20The%20products%20in%20this%20article%20were%20%22New%22%20when%20originally%20published%20and%20should%20obviously%20not%20be%20considered%20as%20such%20when%20reading%20today.%20Although%20this%20article%20has%20some%20historical%20value%2C%20the%20primary%20value%20is%20the%20analysis%20to%20reach%20a%20forecasted%20vision%20of%20future%20market%20conditions%20%28which%20eventually%20came%20to%20pass%29.%20This%20assisted%20many%20consumers%20in%20making%20more%20informed%20purchasing%20decisions.%20Reading%20the%20Analysis%20and%20Conclusions%20section%20is%20almost%20like%20time-travel%3A%20The%20historic%20vision%20has%20now%20transformed%20itself%20into%20current%20events%20and%20conditions%20...%20mostly%20%28we%20are%20still%20waiting%20for%20some%20of%20them%20to%20happen%29.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
			} else {
				echo '<script src="http://digg.com/api/diggthis.js"></script>';
			}
		?></div>
		<div id="right" style="float:right; margin:0 0 5px 5px; text-align:center;" align="center">
			<?include(BASE_DIR .'/ads/mrectangle.php');?>
			<br />
			<div align="center">
				<?include(BASE_DIR .'/ads/skyscraper.php');?>
			</div>
		</div>
		<div id="<?=$container?>">
			<p><script type="text/javascript"><br />
	function shid(id) {<br />
		var oDiv = document.getElementById(id);<br />
		d = oDiv.style.display;<br />
		if (d == 'none') {<br />
			oDiv.style.display = 'inline';<br />
		} else {<br />
			oDiv.style.display = 'none';<br />
		}<br />
	}<br />
</script><br />
<blockquote>The following article originally appeared in HDTVetc magazine in their August 2004 issue. It contains product information that is likewise, dated to mid-2004. The products in this article were "New" when originally published and should obviously not be considered as such when reading today. Although this article has some historical value, the primary value is the analysis to reach a forecasted vision of future market conditions (which eventually came to pass). This assisted many consumers in making more informed purchasing decisions. Reading the Analysis and Conclusions section is almost like time-travel: The historic vision has now transformed itself into current events and conditions ... mostly (we are still waiting for some of them to happen). Enjoy the reading.</blockquote></p>

<p><br />
How do you know what HDTV products are aligned for future release to make an informed selection today?  Similar to when you are about to spend $3000 on any other product for your house.  How do you know if your selection would not to be replaced next month by a product introduced on trade shows?  Last year shows.  Would you expect to see near future products at your corner store when the retailer is actually trying to sell the current line and liquidating the line before that?  </p>

<p>Often, not even the store manager could be informed enough to help you with those questions, and the information is most probably limited to only the lines the store sells, and not looking ahead far enough so your purchase would not to be obsolete the minute it arrives to your door step.  Some sales personnel would not even bother to read basic video subscriptions, or to consult free information of the Internet, to serve you better.  Therefore, you are in your own to been able to anticipate, what to do?         </p>

<p>Consumer electronics is always very dynamic.  I have seen some manufacturers release self-replacing HDTV lines three times within the year, sometimes with minor improvements, other times with radical redesigns to include features you might have interest to wait for, if you knew they were coming next quarter.</p>

<p>The International Consumers Electronic Show (CES) held every January at Las Vegas is considered as one of the most important yearly consumer electronic events, if not the most.  Manufacturers have the opportunity to show their new products, prototypes, mockups, and to provide technology statements.  Many of those products would become available later on the year, or the following, some will later be released with different features or functionality than when shown, some will never make it to the retailing floors.</p>

<p>Every year I prepare a comprehensive review of future H/DTV products a month after CES; approximately 100 pages of documentation containing hundreds of new products with detailed specifications and company/technology trends.  If you need that kind of detail, the complete version could help you and is available in the Internet's HDTV Magazine, <a href="http://www.hdtvmagazine.com/store/ces-2005.php">http://www.hdtvmagazine.com/store/ces-2005.php</a>.  The material also serves as backbone for the printable summary version that appears on the pages of this magazine (HDTVetc) as "The Current State of HD Technology" (Spring 2004 issue for CES 2004). </p>

<p><a href="/cgi-bin/ntlinktrack.cgi?http://www.cedia.net/">CEDIA EXPO</a> (Custom Electronic Design and Installation Association) is another show usually held in September that manufacturers also use as an opportunity to show new products that are coming down the line, those products might be found displayed again four months later at CES.  <a href="/cgi-bin/ntlinktrack.cgi?http://www.CESweb.org/">CES International</a> has grown from 200 exhibitors/17,500 attendees in 1967 to 2,500 exhibitors/129,000 attendees in 2004, and is now over six times the size of CEDIA EXPO (with over 20,000 attendees in 2003).  </p>

<p>In other words, people attending CES might expect to see "all" of the products that will be introduced in the near future.  Some attendees come back from the show and start saving for their targeted product, or decide to buy another one that is already available because CES helped them confirm that it might not be worth the waiting.  However, those that follow the industry closely might already know that many major companies introduce full lines of new products after the first quarter, products that were not even hinted at CEDIA or CES.  By incorporating to the CES review those "during the year" announcements, one can be in a better position to compare the products introduced by company A at CES in January, against products introduced by company B a few months later after CES (due to company's marketing yearly cycles), and have then a more complete view of the 2004/5 lines.  </p>

<p>This article covers the new introductions/updates made between January and August (after CES, but before CEDIA) from several major DTV manufacturers such as Hitachi, Mitsubishi, Thomson, Toshiba, Samsung, and Sony.  Prices are MSRP (I took the liberty to round the 999s to the next dollar to facilitate reading).  Product availability is stated as "Time to Market" (TTM), reported in press releases by the manufacturer. </p>

<p>I hope the information would be useful for your buying/upgrade/planning decisions.  To help you put all this information in perspective I also include a brief analysis and conclusions section at the end.</p>

<blockquote>I would like to remind you that although some of the following products are still current, the announcements are not.</blockquote>

<p><br />
<strong><a href="javascript:shid('Hitachi')">Hitachi</a></strong><br />
<div id="Hitachi" style="display:none"></p>

<p>New York, June 04</p>

<p>Hitachi is concentrating its DTV efforts in LCD (RPTV and direct-view) and CRT RPTV for 2004/5 models, which includes the new "CineForm" design series of fully integrated/CableCARD sets, expected by year-end.  The company has switched from 3 to 21 integrated models transitioning to CableCARD tuner integration, using Hitachi's VirtualHD 1080p upconversion video processing on all models. </p>

<p><u>Eleven new Ultravision CineForm models (below)</u><br />
All integrated with ATSC/QAM CableCARD/dual NTSC tuners, Virtual HD 1080p video processor, identical vertical/horizontal look within the line, reduced height, two stage light engine, new dual-focus, advertising to start in Sep 04 </p>

<p><u>LCD RPTVs Integrated</u><br />
<u>Ultravision VS810 Series</u><br />
For open distribution, two HDMI/HDCP, 40 watt speaker system<br />
50"	50VS810	$4000, TTM 3Q04<br />
60"	60VS810	$4700, TTM 3Q04<br />
70"	70VS810	$7000, TTM 4Q04<br />
<u>Ultravision Director's VX915 Series</u><br />
For A/V retail stores, adds to above dual two-way 1394/DTCP, high-gloss cabinet w/black trim, deep-black anti-reflective shield, learning A/V Net III remote, TTM 4Q04<br />
50"	50VX915	$4700<br />
60"	60VX915	$5500<br />
70"	70VX915	$7500 </p>

<p><u>LCD Flat-panel TV Direct-view</u><br />
32" 	32HDL51	$TBA, TTM 4Q04, 768p, two IEEE-1394, two HDMI</p>

<p><u>Plasma Panels</u><br />
<u>Ultravision HDT51 Series Integrated</u><br />
TTM 3Q04, CineForm cosmetics, dual HDMI, dual 1394, Quick Start Seamless ATSC/NTSC/QAM CableCARD tuners, Virtual HD 1080p processing, USB, inputs/outputs housed on Hitachi's AV Center connected via single wire to panel and controlled with IR from screen for hide away installations<br />
42"	42HDT51	$6000, 1024x1024, AliS technology<br />
<img src="/images/articles/image001.jpg"><br />
55"	55HDT51	$10000, WXGA 1366x768</p>

<p><u>Ultravision HDX61 Director's Series Integrated</u><br />
Same features as HDT51 line plus enhanced industrial design w/high gloss, black trim, high-contrast deep black shield, two year warranty, TTM 3Q04<br />
42"	42HDX61	$7000<br />
55"	55HDX61	$11000</p>

<p><u>Non-CineForm LCD Integrated RPTV</u><br />
Fully integrated ATSC/QAM digital CableCARD tuning capability, TTM 3Q04<br />
<u>V710 (entry) Line</u><br />
720p, Virtual HD 1080p processing, HDMI, USB, 40watt 3-way speaker system<br />
42"	47V710	$2800<br />
50"	50V710	$3300<br />
60"	60V710	$4000<br />
<u>V715 (step-up) Line</u><br />
Titanium silver finish<br />
50"	50V715	$3300<br />
60"	60V715	$4000</p>

<p><u>Plasma EDTV Monitor</u><br />
42"	42EDT41	$4300, Virtual HD 1080p processing, 480p, DVI/HDCP, NTSC tuner, DVI/HDCP, TTM 2Q04<br />
<u>Plasma Professional Panel</u><br />
42"	CMP420V	$3500, DVI, 853x480, V1 black frame version, V2 silver frame </p>

<p><u>CRT RPTVs</u><br />
<u>Series F510 Monitor Line</u><br />
TTM 3Q04, HDMI, Virtual HD 1080p processing, 1080i/540p<br />
46"	46F510	$1500<br />
51"	51F510	$1700<br />
57"	57F510	$2000<br />
<u>Series F710 Integrated</u><br />
TTM 3Q04, adds to above integrated w/ATSC and QAM CableCARD tuners<br />
65"	65F710	$3000<br />
<u>Series S715 Ultravision Integrated Line</u><br />
TTM 3Q04, adds to above five element lens system, USB, 40 watt speaker system<br />
51"	51S715	$2200<br />
57"	57S715	$2500</p>

<p><u>LCD FPTV Ultra-vision</u><br />
PJTX100	$4000, TTM 2Q04, 16:9 LCD for screen sizes between 30" and 300", 1200 ANSI, 1200:1 CR, 1280X720, 1.6:1 zoom, horizontal/vertical lens shift, DVI/HDCP</p>

<p><br />
</div></p>

<p><br />
<strong><a href="javascript:shid('Mitsubishi')">Mitsubishi</a></strong><br />
<div id="Mitsubishi" style="display:none"><br />
April 2004</p>

<p>The company announced the addition of six DLP high-definition sets between 52" and 62" to be available in the period Jul-Sep 04, and one 82" integrated LCoS micro display (Alpha's flagship RPTV).  The sets are integrated with ATSC/QAM CableCARD unidirectional tuners, and have HDMI, and IEEE-1394 digital connections.  These features are also included on most of the other newer sets of the 2004/5 line.  In total, the company is adding 18 new integrated ATSC/cable-ready models. </p>

<p>According to the product development director, Mitsubishi designed a proprietary light engine using the 0.85-inch DMD chip on their new DLP additions, a return from their original 65" DLP introduction in 2000 (MSRP $15,000 at that time), the company said.  Diamond models will be distributed by A/V specialists, Medallion models by major accounts.</p>

<p><u>525 DLP Series Integrated</u><br />
TTM Jul/Aug 04, NetCommand 4.0 networking with Learning Media Command, "AMVP2" motion video processing<br />
52"	WD-52525	$4200<br />
<img src="/images/articles/image002.jpg"><br />
62"	WD-62525	$5000</p>

<p><u>Medallion 725 DLP Series Integrated</u><br />
TTM Aug/Sep 04, same as 525 features plus TV Guide Onscreen IPG, Anti-Glare Diamond Shield<br />
52"	WD-52725	$4500<br />
62"	WD-62725	$5300</p>

<p><u>Diamond 825 DLP Series Integrated</u><br />
TTM Aug 04, 120GB HDD DVR for 12 hours HD, 72 hours SD, MPEG SD encoder, Gemstar guide, subscription free<br />
<img src="/images/articles/image003.jpg"><br />
52"	WD-52825	$5500<br />
62"	WD-62825	$6300 </p>

<p><u>LCoS RPTV</u><br />
82"	Alpha 925	$21000, TTM Oct 04, 1920x1080 pixels resolution, 120GB DVR for 12 hours HD, 72 hours SD, MPEG SD encoder, diffusion screen, two-way speakers, this new unit has now an internal DVR, but still costing $21000 as last year's model Alpha WL-82913</p>

<p><u>New Lines (five models) of LCD Flat-panel TVs</u><br />
40 Series<br />
1280x768, PC compatibility, upgradeable, AMVP<br />
22"	LT-2240	$3000, TTM Jun 04<br />
30"	LT-3040	$5000, TTM Apr 04</p>

<p><u>Medallion LCD Flat-panel Monitor</u><br />
1280x768, TTM May 04, black bezel cosmetics, Color View picture control<br />
<img src="/images/articles/image004.jpg"><br />
30"	LT-3050	$5300<br />
 <br />
<u>Diamond Series LCD Integrated TVs</u><br />
ATSC/NTSC/QAM CableCARD tuners, IEEE-1394, HDMI/HDCP, 120GB HDD DVR for 12 hours HD, 72 hours SD, TV Guide Onscreen IPG, MPEG SD encoders, Net Command 4.0 <br />
<img src="/images/articles/image005.jpg"><br />
42"	LT-4260	$14000, 768x1365, TTM Oct 04, uses 20 fluorescent lamps<br />
55"	LT-5560	$TBA, 1080x1920, TTM TBA, uses 28 fluorescent lamps<br />
 <br />
<u>Plasmas Medallion Series</u> TTM Oct 04<br />
42"	PD-4245	$5000, 480x852 EDTV, MonitorLink DVI, speakers, stand<br />
50"	PD-5050	$8500, 768x1365 HD monitor, HDMI <br />
<img src="/images/articles/image006.jpg"><br />
61"	PD-6150	$18000, 768x1365 HD monitor, HDMI, improved brightness and contrast</p>

<p><u>CRT RPTVs</u><br />
<u>315 Series</u>, upgradeable monitors, DVI/HDCP<br />
42"	WT-42315	$1600, TTM Apr 04<br />
48"	WS-48315	$1800, TTM May 04<br />
55"	WS-55315	$2200, TTM Mar 04<br />
65"	WS-65351	$2700, TTM Apr 04</p>

<p><u>Eight CRT RPTV Fully Integrated Models</u><br />
ATSC/QAM CableCARD tuners, AMVP2 processing, IEEE-1394, Net-Command 4.0 system control, HDMI/HDCP <br />
<u>515 Series</u><br />
48"	WS-48515	$2300, TTM Jul 04<br />
55"	WS-55515	$2700, TTM Jul 04<br />
65"	WS-65515	$3200, TTM Apr 04</p>

<p><u>Medallion 615 Series</u>, TTM Aug 04<br />
55"	WS-55615	$3000<br />
65"	WS-65615	$3500<br />
73"	WS-73615	$5300<br />
<u>Diamond 815 Series</u>, TTM Aug 04<br />
<img src="/images/articles/image007.jpg"><br />
55"	WS-55815	$4500<br />
65"	WS-65815	$5500, 9-inch CRTs</p>

<p><u>New HDTV Receiver/Controller</u><br />
<img src="/images/articles/image008.gif"><br />
HD-6000	TTM later 04 at selected retailers, HD 120GB PVR (personal video recorder), up to 12 hours of HD recording, and 72 hours of non-HD, subcription free, MPEG SD encoder, AMVP2(TM) Mitsubishi's second-generation Advanced Multimedia Video Processor.</p>

<p>ATSC/QAM CableCARD/NTSC tuners, NetCommand(R) 4.0 system control, PerfectColor(TM) 6-way color adjustment, TV Guide On Screen(R) electronic program guide, seven inputs including one HDMI, and three component video inputs.  Outputs include one HDMI, and one component video.  Two FireWire(R) (IEEE 1394) digital home-networking ports.</p>

<p><br />
</div></p>

<p><br />
<strong><a href="javascript:shid('Samsung')">Samsung</a></strong><br />
<div id="Samsung" style="display:none"><br />
New York, June 2004</p>

<p>At CES 2004 Samsung informally projected that in July 2004 they would make available to the consumer a 1080p DLP RPTV line implementing TI's new xHD3 Digital Micromirror Device (DMD), followed by a 1080p DLP FPTV in November.  The line for 1080P RPTV was said to include sizes of 50", 56", and 61", at a projected range of $4000 to $6000.  In June, the company formally confirmed the initial release of the 1080p RPTV line for later this fall to be made available to select A/V retailers, using a new design of their light engine (fifth-generation).</p>

<p><u>97 Series Line</u><br />
61"	HL-P6197W	$6500, TTM Nov 04, 3000:1 CR, seven-segment color wheel, integrated ATSC/QAM unidirectional CableCARD tuners, to be distributed through select A/V retailers.  The $6000 projected MSRP upper range (for the larger set), provided in January at CES, was close to June's confirmation.  </p>

<p>In June's announcement no confirmation was issued about the release of the other smaller screens of the line, however, in September, just after this article was prepared, Samsung announced that they would show at CEDIA (September 04) the 56-inch model of this xHD3 1080p line:</p>

<p>56"	HL-P5697W	MSRP expected to be between $5500/$6000, TTM 1Q05, 3000:1 CR, pedestal base design, integrated with ATSC/NTSC/QAM Cable CARD tuners. </p>

<p>Samsung announced the release of nine fully integrated/CableCARD-ready rear-projection HDTV sets, six transitioned as HDTV monitors, three integrated models expected for release between September and November.  Other DLP models will be transitioned to become integrated w/CableCARD to comply with the FCC mandate with the objective of eventually discontinue the manufacturing of HD DLP monitors.</p>

<p>Integrated CableCARD models transitioning from monitor versions will be identified with a number 7 on the last digit of the model number (instead of the 3 of the monitor version), the new 56" HL-P5663W DLP RPTV monitor expected for July will transition to HL-P5667W as the CableCARD integrated version, within a year time-frame.  At CES 2004, the company anticipated that integrated versions would be priced $500 above the monitor versions below, expected for later in 2004.</p>

<p><u>63 Series DLP Monitors</u><br />
Will be distributed by national accounts, TTM Jun/Jul 04, 0.55" HD3 DMD chip with third-generation light engine, 1500:1 CR, improved brightness over 2003's HD2 models, one DVI and one HDMI inputs, two component video inputs, <br />
<img src="/images/articles/image009.jpg"><br />
46"	HL-P4663W	$3300<br />
50"	HL-P5063W	$3700<br />
56"	HL-P5663W	$4200<br />
61"	HL-P6163W	$4700<br />
The 56" is for regional accounts, but will also be available to national accounts by special order only</p>

<p><u>70 Series DLP Monitors</u><br />
Will be distributed by the PRO group and select A/V specialty retailers, 0.85" HD2+ DMD chip with fourth-generation light engine, claimed to have increased switching speed, reduced pivot point and higher reflective surface area for improved contrast (2500:1 CR) and brightness, improved optics and screen designs <br />
46"	$4000<br />
56"	$4500</p>

<p><u>Monitor Pedestal DLP Models</u><br />
TTM Jul 04, HD2+ DMD chip, fourth-generation light engine, HDMI, DVI, PC inputs, component video inputs</p>

<p><img src="/images/articles/image010.jpg"><br />
50"	HL-P5085W	$4300, distribution by national accounts<br />
56"	HL-P5685W	$5000, distribution by regional accounts </p>

<p><u>CRT RPTVs Monitors</u><br />
TTM Apr 04, HDMI/HDCP, two component inputs<br />
42"	HC-P4252W	$1200, tabletop<br />
47"	HC-P4752W	$1300, tabletop<br />
52"	HC-P5252W	$1500, floor-standing</p>

<p>At CES 2004, Samsung announced that it would drop the 55" and 65" CRT RPTV monitor sets to focus on micro-display technologies, and is actually happening.  The company also indicated that it plans to transition the above CRT RPTV monitors to fully integrated CableCARD sets later in the year, a difficult endeavor when adding a relatively expensive HD tuner to the low cost of CRT models, according to Samsung.  One integrated (transitioned) model mentioned at CES was:</p>

<p>52"	HC-P5256W	$2,200, TTM later 04, integrated w/ATSC/QAM CableCARD, DNIe, HDMI (note the difference of $700 of the MSRP of 52" monitor vs. the estimated integrated at CES time; hopefully the difference "might" be lower at actual release time)</p>

<p><u>CRT Direct-view Integrated Models</u> (eight)<br />
Built-in ATSC tuners in four screen sizes, in-the-clear QAM digital cable tuner (omit CableCARD), DynaFlat picture tube, DVI/HDCP, accept 1080i/720p to display as native 1080i <br />
30" in 16x9 AR, three models priced between $1000-$1200<br />
26" in 16x9 AR, two models priced at $700 each<br />
32" in 4x3 AR, two models at $1000 each<br />
27" in 4x3 AR, $700</p>

<p><u>LCD Flat Panel Monitors</u><br />
46"	LT-P468W	$10,000, TTM Jul 04, 1080p (1080x1920), 12-milisecond response-time, 800:1 CR, 500 candelas brightness, the model was originally gross estimated at CES 2004 as $20,000.</p>

<p>There were no comments issued from the company representatives regarding the status of the 57" version anticipated at CES 2004 (57" LTP578W, $TBA, TTM Jun 04, 1080x1920, 1000:1 CR, 600 cd/m2, DNIe, HDMI, DVI), hopefully they are on track.</p>

<p><u>Reduction of price on HLN models</u>:<br />
In March 2004 Samsung announced a $500 reduction of prices on the earlier 2003 DLP rear-projection HDTV lineup, the 43" entry model HLN4365W is now $3200 MSRP, the 50" HLN5065W is now $3700 MSRP, and the 61" HLN617W is now $4700 MSRP. </p>

<p><u>Plasma Panels</u><br />
At the time (August) this article was prepared, Samsung was expected to announce the future introduction of plasma panels at CEDIA (Sep 04), such as:</p>

<p>37"	HP-P3761 monitor (dual NTSC tuners only), 1000:1 CR, 1000 cd/m2 brightness, <br />
42"	HP-P4261 monitor (dual NTSC tuners only), 900:1 CR, 1366x768, 1000 cd/m2 brightness <br />
55"	HP-P5581 integrated 55 inches with ATSC/QAM CableCARD tuners, 3000:1 CR, 1000 cd/m2 brightness, DVI/HDCP, MSRP TBA</p>

<p><br />
</div></p>

<p><br />
<strong><a href="javascript:shid('SONY')">SONY</a></strong><br />
<div id="SONY" style="display:none"><br />
February/June/August 2004</p>

<p>On February 2004, Sony introduced twelve HDTV integrated models with ATSC/NTSC/QAM CableCARD unidirectional tuners with HDMI/HDCP digital connectivity and two HD-STBs with DVR for QAM Cable CARD tuning.  Of the twelve models, six are LCD Grand Wega RPTVs, four CRT Direct-View sets, and two CRT-based RPTV sets, as follows:</p>

<p><u>LCD Grand Wega Integrated RPTVs</u><br />
Two new series (WF and XS) were added to the entry-level (WE) and high-end (XBR) Series, and two new models were added within the WE Series, TTM Sep 04, 16:9 AR, Sony LCD Optical Engine video processing</p>

<p><u>New models on the WE Series</u><br />
42"	KDF-42WE655	$2800<br />
50"	KDF-50WE655	$3000<br />
<u>New WF Series</u><br />
55"	KDF-55WF655	$3700<br />
60"	KDF-60WF655	$4000<br />
<u>New XS Series</u><br />
55"	KDF-55XS955		$4000<br />
60"	KDF-60XS955		$4400</p>

<p><u>Direct-View Integrated CRT Tubes</u><br />
Trinitron Wega tubes, SuperFine Pitch CRT technology, Wega engine processing<br />
34"	KD-34XBR960		$2200, TTM Jun 04<br />
34"	KD-34SX955		$2000, TTM Aug 04<br />
36"	KD-36SX955		$1900, 4:3 AR, TTM Oct 04<br />
30"	KD-30SX955		$1400, 16:9 AR, TTM Aug 04</p>

<p><u>CRT-based RPTVs Integrated</u><br />
TTM Sep 04, WEGA engine, Direct Digital, DRCM (Digital Reality Creation MultiFunction), Multi-Image Driver (MID-X) circuitry<br />
51"	KDP-51WS655	$2100<br />
57"	KDP-57WS655	$2400</p>

<p><u>Flat Panel Monitors</u><br />
Large Scale Integrated (LSI) circuitry to improve response rates in LCD and contrast in plasma model<br />
23"	KLV-23M1	$2300, LCD TV, 1366x768<br />
32"	KLV-32M1	$4500, LCD TV, 1366x768<br />
42"	KE-42M1	$5000, TTM Jun 04, 480p EDTV</p>

<p><u>Hi-Scan Series FD Trinitron WEGA Monitors</u><br />
Solid silver tone, rounded corner cabinetry, dual component inputs, HDMI/HDCP<br />
27"	KV-27HS420	$750<br />
30"	KV-30HS420	$1000<br />
32"	KV-32HS420	$1000, 4:3 AR<br />
34"	KV-34HS420	$N/A, 16:9 AR<br />
36"	KV-36HS420	$N/A </p>

<p><u>LCoS RPTV (Sony's SXRD Technology)</u><br />
To pair their current QUALIA FPTV projector, Sony was expected to introduce an SXRD integrated RPTV soon.  After this article was prepared, Sony actually unveiled such set at CEDIA (September 04).  It is a 16:9 model KDS-70XBR100 within the XBR line (not the hi-end QUALIA line), native resolution of 1920x1080, 70 inches, NTSC/ATSC/QAM CableCARD tuners, 200-watt cooled lamp for 3000:1 CR, WEGA Engine System, HD component inputs, HDMI/HDCP, IEEE1394 (iLink), expected MSRP $10000, TTM early 05. </p>

<p><u>HD QAM Cable STBs</u><br />
On Sony's press release of February 04, the company announced the future introduction (by fall 04) of two new Cable HD-STBs with DVR capabilities, featuring ATSC/NTSC/QAM CableCARD tuners implementing Sony Passage integrated decryption technology.  The boxes were said to be suited with HDMI/HDCP, Gemstar integrated EPG, component output, flexible AR settings, DD 5.1 w/optical audio out, USB data ports, and memory stick for JPEG and MPEG1, as follows:</p>

<p>DHG-HDD100	$700, TTM fall 04, 120GB HDD, 120 hours SD, 12 hours HD <br />
DHG-HDD200	$800, TTM fall 04, 250GB HDD, 200 hours SD, 25 hours HD</p>

<p>However, later, in August 04, Sony issued a different press release announcing the future introduction of other models, as follows:</p>

<p>DHG-HDD250 $800, TTM fall 04, 250GB HDD, 20 hours of HD recording<br />
DHG-HDD500 $1000, TTM fall 04, 500GB HDD (two 250GB HDDs), 60 hours of HD </p>

<p>Although both units above (250 and 500) have similar tuning and connectivity features as the models previously announced in February (100 and 200), the new units were announced with different model numbers, prices, HDD capacity, and recording time. </p>

<p>Sony had not referred to the first models on the second press release of August to clarify if this new pair is a replacement or an addition to those; apparently, Sony has decided to replace the two original models (100 and 200) even before they were expected to appear in fall 04. </p>

<p>It is important to note that these HD-STBs have a connectivity limitation: they lack IEEE1394 Firewire &trade; input/outputs.  This means that a tuned/stored HD content would not be able to be output to a D-VHS recorder for HD tape archival, nor it could be part of digital networking of compressed HD video with other devices or displays.  </p>

<p>Additionally, such limitation does not comply with a specification requiring 1394 digital connectivity on Cable HD-STBs established in the plug-and-play agreement made by the Cable and Consumer Electronics industry, and approved by the FCC.  If you are interested in more details, this subject was covered on my article "HDTV Integrated Tuners and You" that appeared on the second issue of this magazine.</p>

<p>(Announced later on June 2004)<br />
<u>Plasma XS Series Integrated</u><br />
Digital Cable Ready, third-generation WEGA engine image processing, TTM Aug 04<br />
37"	KDE-37XS955		$5500, 1024x1024<br />
42"	KDE-42XS955		$7000, 1024x1024<br />
50"	KDE-50XS955		$9000, 1366x768</p>

<p><br />
</div></p>

<p><br />
<strong><a href="javascript:shid('Thomson')">Thomson</a></strong><br />
<div id="Thomson" style="display:none"><br />
New York, May 2004</p>

<p>Thomson join venture with China's CTL (TTE) starting in July 04 will produce for the US market eleven fully integrated ATSC and Digital Cable Ready models with QAM CableCARD unidirectional (seven RCA Scenium DLP models, four RCA CRT RPTV models), with HDMI, and with component inputs.  The new sets are said to recognize the Broadcast Flag.  According to TTE, the company will become the largest company in the world for color TV products; selling 18 million sets annually (with a 22 million production capacity), representing 11% globally.  </p>

<p><u>"Profile" Scenium DLP Integrated</u><br />
Ultra-thin two new models, ATSC/QAM CableCARD tuners, TTM Sep 04, 6.85 inches deep and less than 103 pounds make them suitable for wall hanging, hanging bracket sold separately, dual IEEE-1394 two-way/DTCP, TV Guide Onscreen EPG and Internet browser, will detect broadcast flag, HD2 chip, HDMI/HDCP, dual component HD inputs<br />
50"	HD50THW263		$9000<br />
61"	HD61THW263		$10000<br />
Thomson did not update/confirm the status of the future 70" DLP mural-sized set announced in January at CES (HD70THW263), expected for early 2005, hopefully they on track.<br />
<u>Other Scenium DLP Integrated</u><br />
Five new sets, 16 inches cabinet depth, under 100 pounds, HDMI/HDCP, ATSC/QAM w/CableCARD/NTSC tuners, 160 degree viewing angle, component, HD2 chip, controls DVR functions of optional DVR2080<br />
<u>165 Series</u><br />
IEEE-1394, Internet browser, TV Guide EPG, TTM fall 04<br />
44"	HD44LPW165	$3700 <br />
<u>163 Series</u><br />
IEEE-1394, Internet browser, TV Guide EPG, TTM Aug 04<br />
50"	HD50LPW163	$4000 <br />
61"	HD61LPW163	$4600</p>

<p><u>162 Series</u><br />
ATSC/QAM cable in-the-clear tuners, excludes: TV Guide EPG, Internet browser, and IEEE-1394 interface.  TTM Jul 04<br />
50"	HD50LPW162	$3800<br />
61"	HD61LPW162	$4400</p>

<p><u>CRT RPTV Integrated</u><br />
Four new sets w/ATSC and QAM cable tuners, HDMI/HDCP, component, TTM fall 04<br />
52" 	HD52W55	$1900<br />
52"	HD52W56	$2000<br />
<u>58 group</u><br />
Subwoofer output, protective screen shield, SRS Focus<br />
52"	HD52W58	$2300<br />
56"	HD56W58	$2500</p>

<p><u>Current 42 Series DLP Integrated</u> (continues in the line up)<br />
ATSC/QAM cable in-the-clear tuners, includes EPG, Internet browser, HDMI, and IEEE-1394<br />
50"	HD50LPW42	$3800<br />
61"	HD61LPW42	$4300</p>

<p><u>CRT RPTV Monitors</u> (carried over)<br />
DVI/HDCP <br />
52"	D52W15	$1500<br />
52"	D52W20	$1700<br />
56"	D56W20	$2000<br />
61"	D61W20	$2200</p>

<p><u>Plasma Flat Panels</u><br />
Thomson discontinued the plasma line; their supplier (NEC) was acquired by Pioneer.  Thomson will concentrate on DLP and LCD.</p>

<p><u>LCD TV</u><br />
<u>RCA Line Monitor</u><br />
26"	LCDX2620W	$2600, TTM Jun 04, 1280x768, open distribution, DVI/HDCP, component, RGB, NTSC tuner, 600:1 CR, 500 cd/m2 brightness<br />
<u>RCA Scenium Line</u><br />
NTSC tuning monitors, DVI/HDCP, component, RGB, 500:1 CR, 500 cd/m2 brightness, 170 degrees of vertical/horizontal viewing<br />
27"	LCDX2722W	$2800, TTM Jun 04, 1280x720<br />
32"	LCDX3022W	$3800, TTM Jul 04, 1280x768</p>

<p><u>Two new RCA Scenium HD DVRs</u><br />
Interface w/new integrated TV sets via IEEE-1394 connections; recognize DTCP and Broadcast Flag<br />
DVR2160	$550, 80 hours of SD, 18 hours of HD recording<br />
DVR2080	$450, 40 hours of SD, 9 hours of HD recording</p>

<p><br />
</div></p>

<p><br />
<strong><a href="javascript:shid('Toshiba')">Toshiba</a></strong><br />
<div id="Toshiba" style="display:none"><br />
May 2004 (Austin Texas) </p>

<p>Toshiba announced its 2004-05 television line to dealers.  The new line is mainly oriented to fixed-pixel digital display technologies, such as direct-view LCD TV, plasma, Digital Light Processing (DLP) rear-projection integrated sets and monitors, in addition to CRT-based rear-projection and direct-view products.</p>

<p>Additionally, Toshiba introduced an HD 160GB digital video recorder (Symbio 160HD4, $500) to connect via IEEE-1394 with their integrated sets.  The programming circuitry is based in part on Gemstar's TV Guide Onscreen interactive program guide, and since is built within Toshiba's fully integrated HDTV sets it makes the Symbio compatible only with those sets.</p>

<p>In January 2004 (CES), Toshiba announced their decision of discontinuing the LCoS line, which is now replaced by their support to DLP.  In May, Toshiba formally announced the addition of 10 RPTV DLP sets and monitors implementing the pairing of the HD2+ chip of Texas Instruments (0.8-inch Digital Micromirror Device - DMD) with the Toshiba Advanced Light Engine (TALEN).  The company's two-year goal is to position their DLP products within the top-three DLP TV manufacturers, by using advance optics together with the HD2+ DMD chip, as opposed to the HD3 chip-based sets of the competition.</p>

<p>New models of fully integrated HDTV sets include ATSC/QAM tuners with CableCARD slots for unidirectional digital cable ready capability, and TV Guide Onscreen interactive program guides.  Most sets also include both HDMI/HDCP and IEEE-1394/DTCP digital interfaces. </p>

<p><u>DLP RPTVs</u><br />
HD2+ chip, Magic Square Algorithm for smooth color gradation, Dynamic Contrast Enhancer for higher contrast, color purity, and saturation, Super Real Transient and Small Signal Sharpness for sharp transitions from dark to light, Color Transient Improver and Color Detail Enhancer, flat-panel plasma displays type of look, silver cosmetics and thin cabinets, and under-screen glass component cabinets.</p>

<p><u>TheaterWide Series DLP RPTVs</u><br />
Silver cabinets with a gray bezel<br />
<u>HM84 Tabletop Monitors</u><br />
TTM Jul 04<br />
46"	46HM84 	$3000<br />
52"	52HM84 	$3500<br />
62"	62HM84 	$4000</p>

<p><u>HM94 Tabletop Integrated</u><br />
ATSC/QAM CableCARD tuners, IEEE-1394, DTVLink, HDMI, TTM Sep 04<br />
46"	46HM94	$3400<br />
52"	52HM94	$3900<br />
62"	62HM94	$4400</p>

<p><u>Cinema Series DLP RPTVs</u><br />
Same as TheaterWide Series plus cabinetry with black bezel accents, virtual Dolby surround sound, 6-item A/V illuminated remote<br />
<u>HMX84 monitors</u><br />
52"	52HMX84	$3800, TTM Aug 04<br />
62"	62HMX84 	$4300, TTM Sep 04<br />
<u>HMX94 Integrated</u><br />
Two HDMI, TTM Oct 04<br />
52"	52HMX94	$4200<br />
62"	62HMX94	$4700</p>

<p><u>CRT RPTVs</u><br />
Analog and 4:3 aspect ratio sets are now discontinued <br />
<u>TheaterWide Monitors</u><br />
46"	46H84		$1400, Jun 04, tabletop<br />
51"	51H84		$1700, May 04<br />
57"	57H84		$1900, May 04<br />
65"	65H84		$2200, Jun 04</p>

<p><u>TheaterWide Integrated</u><br />
QAM CableCard/ATSC tuners, IEEE-1394, TV Guide On-screen interface <br />
51"	51H94		$2100, Jul 04<br />
57"	57H94		$2300, Sep 04</p>

<p><u>Cinema Series Integrated</u><br />
QAM CableCard/ATSC tuners, IEEE-1394, TV Guide On-Screen interface<br />
51"	51HX94	$2400, Aug 04<br />
57"	57HX94	$2600, Sep 04<br />
65"	65HX94	$2900, Oct 04</p>

<p><u>Flat-panel Displays</u></p>

<p><u>LCD Monitors</u><br />
DVI or HDMI, Cable clear DNR+ video noise reduction circuitry<br />
<u>EDTV 4:3 LCD Monitors</u><br />
500:1 CR, component input, 480x640<br />
14"	14DL74	$500, Jun 04<br />
20"	20DL74	$1000, Jun 04<br />
<u>TheaterWide 16:9 LCD Monitors</u><br />
DVI/HDCP<br />
23"	23HL84	$1800, Aug 04, 1280x768, 500:1 CR<br />
23"	23HLV84	$2000, Aug 04, LCD TV/DVD combi-unit, 1280x768, 500:1 CR<br />
26"	26HL84	$2500, Jun 04, 1366x768<br />
32"	32HL84	$3500, May 04, 1366x768</p>

<p><u>TheaterWide Plasma Monitors</u><br />
42"	42HP84	$5500, Sep 04, 1024x768<br />
50"	50HP84	$TBD (estimated at $7500), Oct 04, 1024x768 </p>

<p><u>Cinema Series Flat-panel Monitors</u><br />
New double-baffle design<br />
32"	32HLX84	$4000, Oct 04, LCD monitor, 1366x768, 800:1 CR<br />
42"	42HPX84	$6000, Sep 04, plasma display monitor, 1024x768 </p>

<p><u>Direct-view CRT Monitors</u><br />
<u>TheaterWide Line</u><br />
HDMI<br />
26"	26HF84	$700, Aug 04<br />
30"	30HF84	$900, Jul 04<br />
34"	34HF84	$1400, Jun 04<br />
<u>Cinema Series Line</u><br />
30"	30HFX84	$1000, Aug 04<br />
34"	34HFX84	$1600, Jul 04</p>

<p>Toshiba anticipates a demand for direct-view digital televisions and will keep producing complete lines of analog CRT direct-view models (curved and flat-faced).</p>

<p><u>DVD Recorders</u> (I am adding this non-DTV information due to the HD upconversion feature of the last unit)</p>

<p>In regards to DVD recording, Toshiba announced several new products, single-deck, DVD/VHS, DVD/DVR, DVD/TiVo, and TiVo/DVR combined with DVD-RW/-R recording.  TiVo units use the Series 2 platform with networking upgrade capability and free basic service, and come with 120GB ($600, Aug 04) and 160GB ($700, Aug 04) capacity.  They are suited with a new Easy Navi menu system (simplifying the archiving of camcorder tapes to disc), and with IR blaster capability for automatic recording from separate connected components.</p>

<p>D-R3	 	$350, TTM Sep 04, single-deck DVD recorder w/DVD-RAM/-R<br />
D-VR3		$500, TTM Jul 04, combination DVD-RAM/-RW/-R and VCR dual-deck recorder with bi-directional dubbing</p>

<p>One unit in particular features an interesting capability related to HD, is a DVD recorder with DVR and HD upconversion:<br />
 <br />
RDXS53	$700, TTM Oct 04, combination DVD-RAM/-R DVD recorder, 160GB hard drive recorder, TV Guide On-screen program guide, HDMI output, 720p/1080i upconversion over HDMI</p>

<p>In addition, Toshiba will introduce a full line of DVD players (single-drive, dual-deck and changer configurations) with HDMI.</p>

<p><br />
</div></p>

<p><br />
<strong>Analysis, conclusions</strong></p>

<p>It was noticed an increased support for microchip-based RPTV displays, mainly using LCD and DLP technology.  Some TV manufacturing companies are switching in and out of the LCoS technology due to difficulty/availability of chips, or due to company direction.  Additionally, Intel has announced in August their delay in the delivery of LCoS chips for projection TVs, not to be by year-end as planned; while its competitor, Advanced Micro Devices Inc., is still on course manufacturing chips as planned.</p>

<p>Some large LCD TV panels are now exceeding the 40" mark, a size long considered the domain of plasmas in the flat-panel market.  Although with a handful of new large LCD panels, there is now an overlap in the 37 to mid-40-inches range covered by both technologies, however, new large LCD TV panels generally retail for at least twice the cost of similar size plasmas.  It must be noted that LCD TV panels have reached 1080x1920 resolution on the 45+ size, a resolution that plasma will have in much larger panels, but does not have in the sizes of the overlapping range.  One LCD TV panel is the Sharp's 1080p 45" LCD TV panel (AQUOS models 45GD4U and 45GD6U) expected to ship in the fall of 2004 at $10000 MSRP.</p>

<p>The DTV industry is gradually moving from the manufacturing of CRT-based displays to support other technologies, however, most major companies are still introducing new lines of RPTV and Direct-view models based on CRT technology, which continue to offer the best value for a good quality display (and the rendition of black), if space and weight are not a constraint.  </p>

<p>As to be expected, there is a noticeable boost in the manufacturing volume of integrated sets to meet the FCC mandate and deadlines.  However, if you look carefully across 2004/5 TV lines, you will find that there is still a substantial additional cost charged to consumers for integrated OTA ATSC and QAM CableCARD tuners, the latter with only unidirectional cable limited functionality.  </p>

<p>While the boost in the release of new integrated lines and models is seen in the manufacturing, the actual sales to the final customer on this initial period of large availability of integrated models is not showing a similar boost.  In other words, some companies that still offer monitor-only versions experienced more sales of monitors than integrated models.  That phenomenon is expected to change, not because people prefer (or prefer to pay extra for) integrated tuners, but because most monitors will gradually disappear, as mandated by the FCC.</p>

<p>Customarily, manufacturers reduce the price of models that are about to be replaced, like Samsung, which in March 2004 had reduced by $500 its previous DLP RPTV HLN line, replaced by the 2004 HLP line.  However, many companies are not actually reducing prices when replacing their HDTV current models with newer models, which could be a way to accelerate the HDTV adoption by passing to consumers the savings gained from higher volume and more efficient manufacturing.</p>

<p>Instead, many companies are introducing new models at similar prices than the sets they replace, and in many cases even higher.  Some justify it by the implementation of newer upscale features/technology (like HD2 improved by HD3 and later by HD2+ TI's DMD chips).  Others justify it by the inclusion of the mandated built-in ATSC/QAM cable tuners, jacking up the TV price by $400-$1000 depending of the display technology and the manufacturer.  Although is starting to look better for consumers, "some" manufacturers that added $700 for the HD tuner integration of their 2003 models now charge $500.</p>

<p>Uninformed consumers looking for near future HDTV purchases would most probably not notice the actual dollar impact of integration to their pockets, because they eventually would not find monitor-only versions available to be able to compare their lower cost against their integrated versions, as they could now.  In other words, imagine being forced to buy a new refrigerator when just need to replace the kitchen counter top, because "now it comes with it", everyone sells them together, and the refrigerator is made by the counter top manufacturer.   </p>

<p>It was noticeable the large adoption of DVI and HDMI connectors in HDTV displays (also on HD-STBs and DVD players with upconversion to HD) for the transmission of protected digital HD uncompressed video.  If you have the choice go with HDMI.  Additionally, in most cases, the number of inputs in HDTV displays is still not enough to accept multiple DVI/HDMI suited equipment, which might force you to pay for a DVI/HDMI switcher (not coming cheap).  A/V receivers and pre/pro equipment with multiple DVI/HDMI input/outputs are still absent.  On the plus side, the industry is moving consistently in that direction.   </p>

<p>It was also noticed an increased support to suit integrated HDTV sets and HD-STBs (except DirecTV) with two-way IEEE1394 inputs/outputs for HD compressed video connectivity, which allow for HD networking and external HD recording to DVRs and D-VHS.  However, if you read the specifications on the announcements carefully, you might notice that a great number of integrated HDTVs do not mention the inclusion of IEEE-1394 connectors.  It could be an omission on the early announcement of the specifications, but it could also be an omission of the connection itself.  </p>

<p>My best suggestion to you: before committing yourself to wait for a future model, confirm the features you need if they are not fully spelled out on the announcements, and, in the case of 1394, do not buy an integrated HDTV without 'activated' IEEE-1394 two-way connections.  Even when the 1394 connection is present, it might only work with the manufacturer's proprietary implementation of it, incompatible to other brands and models, such as the case of the Toshiba's new stand-alone DVR (Symbio) that works only when paired to certain new Toshiba's integrated TVs.  </p>

<p>A handful of integrated HDTVs are now suited with internal DVR capabilities, but even when the unit can record internally a program for time-shifting, if you are also interested in archiving to D-VHS, make sure the HDTV has IEEE-1394 connections to permit for that.  </p>

<p>Some Broadcast Flag compliant products are starting to appear, earlier than mandated by the FCC.  The rush for a purchase of a compliant product, rather than a legacy product that does not recognize the Broadcast Flag, might not necessarily be of your best interest (for more in the subject check my article regarding DTV content protection regulations, on issue # 4 of this magazine).</p>

<p>Regarding the expectations for the arrival of some innovating products promised at CES:</p>

<p>The oversized 1080x1920 plasmas (LG 71" Nov 04, LG 76" Jan 05, and Samsung 70" HPP7071 4Q04),<br />
The 57" LTP578W LCD 1080x1920 TV (Samsung, Jun 04, later updated to 4Q04),<br />
The Blu-ray Hi-def DVD player/recorders (Samsung mid 04, LG 3Q04), <br />
The 1080p xHD3 DLP RPTVs (Samsung mid/late 04), and <br />
Voom's network HD-STBs (Motorola 580 DVR server/clients, mid 04)</p>

<p>Although they were originally expected by mid/late 2004, the products are still unreleased as of this writing (August).  Perhaps, by the time you read this, some of those products might start to appear, but if not, it would give you more time for saving for their purchase; there is always a positive side on everything.</p>

<p>On another positive side, the H/DTV industry is moving much faster than when the first sets appeared in 1998, this is very good to consumers and manufacturers alike, we have more technologies, better products, and a larger variety to choose from, the dream of HDTV is finally becoming a reality.</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Rodolfo La Maestra</b>, <b>February  3, 2006 05:44 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 289
 				AND a.topic_id = t.topic_id
 				AND t.topic_id = p.topic_id
 				AND p.post_id = pt.post_id
 			ORDER BY post_time";
 			$result = mQuery($sql);
 			$num_comments = mysql_num_rows($result);
 			
 			if ($num_comments > 0) {
 				# Skip the first one, as it's just the excerpt post.
 				$row = mysql_fetch_assoc($result);
 				$thread_url = URL_FORUM_VIEWTOPIC .'?t='. $row[topic_id];
 				echo '<h2 style="margin-bottom:10px"><a href="'. $thread_url .'">Reader Commentary</a></h2>'.
 				'<div class="item"><span class="corners-top"><span></span></span>'.
 					'<img src="/images/icon_topic.gif" alt="" /><b> See Forum Topic</b>: '.
 					'<a href="'. $thread_url .'">'. $row[topic_title] .'</a> <span class="grey">('. $row[topic_replies] .' replies)</span>'.
 				'<span class="corners-bottom"><span></span></span></div>';
 				
 				$x = 0;
 				while ($row = mysql_fetch_assoc($result)) {
 					if ($x == 10) break;
 					$x++;
 					$comment_url = URL_FORUM_VIEWTOPIC .'?p='. $row[post_id] .'#'. $row[post_id];
 					$text = strip_tags(str_replace('[', '<', str_replace(']', '>', $row[post_text])));
 					if ($row[post_subject] != '') {
 						$subject = $row[post_subject];
 					} else {
 						$subject = "Re: $row[topic_title]";
 					}
 	
 					$class = ($x % 2 == 0) ? 'item' : 'item_odd';
 					echo '<div class="'. $class .'"><span class="corners-top"><span></span></span>'.
 						'<div style="font-size:1.2em; font-weight:bold"><a href="'. $comment_url .'">'. $subject .'</a></div>'.
 						'<b>'. $row[poster_id] .'</b> '. date('M j, g:ia', $row[dt]) .'<br />'.
 						$text .
 					'<span class="corners-bottom"><span></span></span></div>';
 				}
 			}
 			if ($num_comments > $x) {
 				echo '<div align="center" class="important"><span class="corners-top"><span></span></span>'.
 				"Showing only excerpts from $x out of $num_comments, <a href='$thread_url'>Read More</a>".
 				'<span class="corners-bottom"><span></span></span></div>';
 			}
 		?><div class="dottedline"></div></div>

 		<div class="item"><span class="corners-top"><span></span></span>
 			<h2>More on Products & Equipment</h2><ul><?
 			$sql = "
 			SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name
 			FROM mt_entry e, mt_author a, mt_placement p, mt_category c
 			WHERE entry_author_id = author_id
 				AND e.entry_id = p.placement_entry_id
 				AND p.placement_category_id = c.category_id
 				AND category_label = 'Products & Equipment'
 				AND e.entry_status = 2
 				AND e.entry_blog_id IN (". INCLUDE_BLOGS_ALL .")
 				AND entry_author_id = a.author_id
 			ORDER BY entry_created_on DESC LIMIT 25";
 			$result = mQuery($sql);
 			while ($row = mysql_fetch_assoc($result)) {
 				$ts = strtotime($row[entry_created_on]);
 				$y = date('Y', $ts);
 				$m = date('m', $ts);
 				$entry = getEntryInfo($row[entry_blog_id]);
 	
 				$entry[date] = getDateString($ts);
 				$entry[link] = "/$entry[blog_dir]/$y/$m/". dirify($row[entry_title]) .".php";
 				$entry[title] = $row[entry_title];
 				$entry[author] = $row[author_name];

 				echo '<li><a href="'. $entry[link] .'">'. $entry[title] .'</a> - <span class="grey">'. $entry[author] .'</span> - '. $entry[date] .'</li>';
 			}
 		?></ul><span class="corners-bottom"><span></span></span></div>
			
 		<?if (1 <> 7) {
 			# Recent Articles by Author (exclude this one)
 			# Do not show recent articles for Bulletins.
 			$qry = "
 			SELECT entry_id, entry_blog_id, entry_created_on, entry_title, author_id, author_name
 			FROM mt_entry e, mt_author a
 			WHERE entry_blog_id = 1
 				AND entry_id <> 289
 				AND entry_status = 2
 				AND entry_author_id = a.author_id
 				AND a.author_name = 'Rodolfo La Maestra'
 			ORDER BY entry_created_on DESC LIMIT 10";
 			$result = mQuery($qry);
 			
 			if (mysql_num_rows($result) > 0) {
 				$row = mysql_fetch_assoc($result);
 				echo '<div class="item"><span class="corners-top"><span></span></span>'.
 				'<h2><a href="/author.php?author='. urlencode($row[author_name]) .'&id='. $row[author_id] .'">More from '. $row[author_name] .'</a></h2><ul>';
 				mysql_data_seek($result, 0);
 				while ($row = mysql_fetch_assoc($result)) {
 					# Get categories
 					$sql = "
 					SELECT category_label FROM mt_category c, mt_placement p
 					WHERE $row[entry_id] = p.placement_entry_id
 						AND c.category_id = p.placement_category_id";
 					$res_categories = mQuery($sql);
 					$row_categories = mysql_fetch_assoc($res_categories);
 					$category = $row_categories[category_label];

 					$ts = strtotime($row[entry_created_on]);
 					$y = date('Y', $ts);
 					$m = date('m', $ts);
 					$blog_dir = getBlogDir($row[entry_blog_id]);
 					$date = getDateString($ts);
 					$link = "/$blog_dir/$y/$m/". dirify($row[entry_title]) .".php";
 					echo '<li><a href="'. $link .'">'. $row[entry_title] .'</a> - <span class="grey">'. $category .'</span> - '. $date .'</li>';
 				}
 				echo '</ul><span class="corners-bottom"><span></span></span></div>';
				}
			}

 		if ($author[bio_short] != '') {?>
 			<div class="item"><span class="corners-top"><span></span></span>
 				<h2>About Rodolfo La Maestra</h2>
 				<?=stripslashes($author[bio_short])?>
 			<span class="corners-bottom"><span></span></span></div>
 		<?}?>
		</td><td id="right">

 		<?if ($about != '') {?>
 			<div class="item"><span class="corners-top"><span></span></span>
 				<h2>About Articles</h2>
 				<?=$about?>
 			<span class="corners-bottom"><span></span></span></div>
		<?}?>
		
 		<div class="item"><span class="corners-top"><span></span></span>
 			<h2><a href="/forum/index.php">Other Recent Discussion</h2><ul class="brownsquare"><?
 				$qry = "
 				SELECT topic_title, t.topic_id, username as post_author, post_time, post_id
 				FROM phpbb_topics t, phpbb_users u, phpbb_posts p
 				WHERE
 					t.forum_id NOT IN (". EXCLUDE_FORUMS .")
 					AND p.poster_id = u.user_id
 					AND t.topic_id = p.topic_id
 					AND t.topic_last_post_id = p.post_id
 				ORDER BY post_time DESC LIMIT 10";
 				$result = mQuery($qry);
 				
 				while ($row = mysql_fetch_assoc($result)) {
   					$last_post = date('n/j g:ia T', $row[post_time]);
   					$title = html_entity_decode($row[topic_title]);
 		  					
 					echo '<li><a href="'. FULL_URL_FORUM_VIEWTOPIC .'?t='. $row[topic_id] .'">'. $title .'</a> - <span class="grey">'. $row[post_author] .'</span> - '. $last_post .'</li>';
 				}
 			?></ul>
 		<span class="corners-bottom"><span></span></span></div>

 		<div class="item"><span class="corners-top"><span></span></span>
 			<h2>Authors</h2>
 			<ul class="brownsquare"><?
 				$qry = "
 				SELECT author_id, author_name, COUNT(*) num
 				FROM mt_author a, mt_entry e
 				WHERE e.entry_blog_id IN (". INCLUDE_BLOGS_NO_BULLETINS .")
 					AND entry_status = 2
 					AND entry_author_id = author_id
 				GROUP BY author_id, author_name
 				ORDER BY num DESC";
 				$res_authors = mQuery($qry);
 				while ($row_authors = mysql_fetch_assoc($res_authors)) {
 					echo '<li><a href="/author.php?author='. urlencode($row_authors[author_name]) .'&id='. $row_authors[author_id] .'">'. $row_authors[author_name] .'</a><span class="grey"> ('. $row_authors[num] .')</span></li>';
 				}
 			?></ul>
 		<span class="corners-bottom"><span></span></span></div>

 		<div class="item"><span class="corners-top"><span></span></span>
 			<h2>Categories</h2>
 			<ul class="brownsquare"><?
 				$qry = "
 				SELECT category_label label, COUNT(*) num
 				FROM mt_entry e, mt_placement p, mt_category c
 				WHERE e.entry_blog_id IN (". INCLUDE_BLOGS_ALL .")
 					AND entry_status = 2
 					AND entry_id = p.placement_entry_id
 					AND p.placement_category_id = c.category_id
 				GROUP BY label
 				ORDER BY label";
 				$result = mQuery($qry);
 				while ($category = mysql_fetch_assoc($result)) {
 					echo '<li><a href="/category.php?category='. urlencode($category[label]) .'">'. $category[label] .'</a><span class="grey"> ('. $category[num] .')</span></li>';
 				}
 			?></ul>
 		<span class="corners-bottom"><span></span></span></div>

		</td>
	</tr></table>

	<?
		include(BASE_DIR .'/includes/body_footer.php');
	?>
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2006/02/hdtv_products_-_looking_into_the_future.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
