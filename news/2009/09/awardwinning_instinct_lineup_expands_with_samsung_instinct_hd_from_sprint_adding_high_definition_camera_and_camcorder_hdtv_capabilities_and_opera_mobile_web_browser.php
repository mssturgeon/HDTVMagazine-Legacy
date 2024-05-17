<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');

	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "$author[title]<br />";

	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3291 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get enclosure info
	$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3291";
	$res_enclosure = mQuery($sql);
	$row_enclosure = mysql_fetch_assoc($res_enclosure);
	$enclosure_url = $row_enclosure['enclosure_url'];

	# Still need this to track page views. Used in the Links unit in the footer
	$google_links_channel = $author['channel'];

	# Set variables based on entry type. NOTE: Podcasts (& soon Reviews) has its own entry template, so it is not included amongst the choices below.
	switch (7) {
		case 1: # Articles
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/hdtv-articles" />';
			$feed_tracker = '<script src="http://feeds.feedburner.com/~s/hdtv-articles?i=http://www.hdtvmagazine.com/news/2009/09/awardwinning_instinct_lineup_expands_with_samsung_instinct_hd_from_sprint_adding_high_definition_camera_and_camcorder_hdtv_capabilities_and_opera_mobile_web_browser.php" type="text/javascript" charset="utf-8"></script>';
			$container = 'article_container';
			$sub_type = SUB_ARTICLES;
			$sub_label = 'Receive instant notification of new articles';
			if ($userdata['session_logged_in']) {
				$sub_desc = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of new HDTV Magazine Articles via email as soon as they are published.';
			} else {
				$sub_desc = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of new HDTV Magazine Articles via email as soon as they are published.';
			}
			break;
		case 4: # Interviews
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/hdtv-interviews" />';
			$feed_tracker = '<script src="http://feeds.feedburner.com/~s/hdtv-interviews?i=http://www.hdtvmagazine.com/news/2009/09/awardwinning_instinct_lineup_expands_with_samsung_instinct_hd_from_sprint_adding_high_definition_camera_and_camcorder_hdtv_capabilities_and_opera_mobile_web_browser.php" type="text/javascript" charset="utf-8"></script>';
			break;
		case 5: # History
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
			$feed_tracker = '<script src="http://feeds.feedburner.com/~s/hdtv-archive?i=http://www.hdtvmagazine.com/news/2009/09/awardwinning_instinct_lineup_expands_with_samsung_instinct_hd_from_sprint_adding_high_definition_camera_and_camcorder_hdtv_capabilities_and_opera_mobile_web_browser.php" type="text/javascript" charset="utf-8"></script>';
			break;
		case 6: # Test
			$container = 'article_container';
			$sub_type = 0;
			$sub_label = 'Receive instant notification of "Stuff"';
			if ($userdata['session_logged_in']) {
				$sub_desc = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of "Stuff" via email as soon as they are published.';
			} else {
				$sub_desc = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of "Stuff" via email as soon as they are published.';
			}
			break;
		case 7: # Bulletins
			$google_links_channel = ''; # Don't count bulletins
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/hdtv-news" />';
			$feed_tracker = '<script src="http://feeds.feedburner.com/~s/hdtv-news?i=http://www.hdtvmagazine.com/news/2009/09/awardwinning_instinct_lineup_expands_with_samsung_instinct_hd_from_sprint_adding_high_definition_camera_and_camcorder_hdtv_capabilities_and_opera_mobile_web_browser.php" type="text/javascript" charset="utf-8"></script>';
			$container = 'bulletin_container';
			$sub_type = SUB_BULLETINS;
			$sub_label = 'Receive instant notification of HDTV Bulletins';
			if ($userdata['session_logged_in']) {
				$sub_desc = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of HDTV Bulletins via email as soon as they are published.';
			} else {
				$sub_desc = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of HDTV Bulletins via email as soon as they are published.';
			}
			break;
		case 8: # Reviews
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/hdtv-reviews" />';
			$feed_tracker = '<script src="http://feeds.feedburner.com/~s/hdtv-reviews?i=http://www.hdtvmagazine.com/news/2009/09/awardwinning_instinct_lineup_expands_with_samsung_instinct_hd_from_sprint_adding_high_definition_camera_and_camcorder_hdtv_capabilities_and_opera_mobile_web_browser.php" type="text/javascript" charset="utf-8"></script>';
			$container = 'article_container';
			$sub_type = SUB_REVIEWS;
			$sub_label = 'Receive instant notification of new reviews';
			if ($userdata['session_logged_in']) {
				$sub_desc = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of new HDTV Magazine Reviews via email as soon as they are published.';
			} else {
				$sub_desc = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of new HDTV Magazine Reviews via email as soon as they are published.';
			}
			break;
		case 9: # Podcasts
			$container = 'article_container';
			$sub_type = SUB_PODCAST;
			$sub_label = 'Receive instant notification of new episodes';
			if ($userdata['session_logged_in']) {
				$sub_desc_logged_in = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of new episodes of The HDTV Podcast via email as soon as they are published.';
			} else {
				$sub_desc_anon = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of new episodes of The HDTV Podcast via email as soon as they are published.';
			}
			break;
		case 10: # Columns
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/hdtv-columns" />';
			$feed_tracker = '<script src="http://feeds.feedburner.com/~s/hdtv-columns?i=http://www.hdtvmagazine.com/news/2009/09/awardwinning_instinct_lineup_expands_with_samsung_instinct_hd_from_sprint_adding_high_definition_camera_and_camcorder_hdtv_capabilities_and_opera_mobile_web_browser.php" type="text/javascript" charset="utf-8"></script>';
			$container = 'article_container';
			$sub_type = SUB_COLUMNS;
			$sub_label = 'Receive instant notification of new columns';
			if ($userdata['session_logged_in']) {
				$sub_desc = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of new HDTV Magazine Columns via email as soon as they are published.';
			} else {
				$sub_desc = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of new HDTV Magazine Columns via email as soon as they are published.';
			}
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
	<? require(BASE_DIR .'/includes/common_header.php'); ?>
	<meta name="keywords" content="samsung instinct, best buy, samsung electronics, opera mobile, camera camcorder, sprint, samsung, instinct, mobile, data, based, wireless, customers, coverage, unlimited, available, web, information, network, ”, including, buy, recycling, video, best" />
	<meta name="description" content="Award-Winning Instinct Lineup Expands with Samsung Instinct HD from Sprint Adding High Definition Camera and Camcorder, HDTV Capabilities and Opera Mobile Web Browser Available from Best Buy on Sept. 27 and all Sprint retail channels on Oct. 11, Samsung Instinct..." />
	<title>HDTV Magazine Bulletins - Award-Winning Instinct Lineup Expands with Samsung Instinct HD from Sprint Adding High Definition Camera and Camcorder, HDTV Capabilities and Opera Mobile Web Browser</title>
	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		digg_url = 'http://www.hdtvmagazine.com/news/2009/09/awardwinning_instinct_lineup_expands_with_samsung_instinct_hd_from_sprint_adding_high_definition_camera_and_camcorder_hdtv_capabilities_and_opera_mobile_web_browser.php';
		digg_skin = 'compact';
		digg_window = 'new';
		digg_title = 'Award-Winning Instinct Lineup Expands with Samsung Instinct HD from Sprint Adding High Definition Camera and Camcorder, HDTV Capabilities and Opera Mobile Web Browser';
		digg_bodytext = 'Award-Winning Instinct Lineup Expands with Samsung Instinct HD from Sprint Adding High Definition Camera and Camcorder, HDTV Capabilities and Opera Mobile Web Browser Available from Best Buy on Sept. 27 and all Sprint retail channels on Oct. 11, Samsung Instinct...';
//		digg_media = '';
		digg_topic = 'tech_news';

//		tweetmeme_url = 'http://yoururl.com';
		tweetmeme_style = 'compact';
		tweetmeme_source = 'HDTVMagazine';
		tweetmeme_service = 'bit.ly';

//		ReTweet settings
		url = 'http://www.hdtvmagazine.com/news/2009/09/awardwinning_instinct_lineup_expands_with_samsung_instinct_hd_from_sprint_adding_high_definition_camera_and_camcorder_hdtv_capabilities_and_opera_mobile_web_browser.php';
		size = 'small';

		function fbs_click() {
			u=location.href;
			t=document.title;
			window.open('http://www.facebook.com/sharer.php?u='+encodeURIComponent(u)+'&amp;t='+encodeURIComponent(t),'sharer','toolbar=0,status=0,width=626,height=436');
			return false;
		}
	</script>
	<style>
		html .fb_share_link {padding:2px 0 0 20px; height:16px; background:url(http://b.static.ak.fbcdn.net/images/share/facebook_share_icon.gif?8:26981) no-repeat top left;}
		/*div.snap_preview div {display:none;}*/
		.header_buttons {text-align:right;}
		.header_buttons span {float:right; margin-right:20px;}
	</style>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/ads/leaderboard.php');
		include(BASE_DIR .'/includes/body_header.php');

		$base_url = strleftback(PHP_SELF, '/') . '/awardwinning_instinct_lineup_expands_with_samsung_instinct_hd_from_sprint_adding_high_definition_camera_and_camcorder_hdtv_capabilities_and_opera_mobile_web_browser';
		$title_encoded = rawurlencode(addslashes('Award-Winning Instinct Lineup Expands with Samsung Instinct HD from Sprint Adding High Definition Camera and Camcorder, HDTV Capabilities and Opera Mobile Web Browser'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2009/09/awardwinning_instinct_lineup_expands_with_samsung_instinct_hd_from_sprint_adding_high_definition_camera_and_camcorder_hdtv_capabilities_and_opera_mobile_web_browser.php";
		if ($author['img'] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author['img'] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}

	if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']), "google") === false) {?>
		<!-- Article Header -->
		<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
			<tr>
				<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
				<td class="article_title" colspan="2">Award-Winning Instinct Lineup Expands with Samsung Instinct HD from Sprint Adding High Definition Camera and Camcorder, HDTV Capabilities and Opera Mobile Web Browser</td>
			</tr><tr>
			<td id="article_byline" nowrap="nowrap">
					By <b>Shane Sturgeon</b><br />
					<?=$author_title?>
					Posted on <b>September 24, 2009</b><br />
					Category: <b><a href="/category.php?id=<?=$category_id?>&category="></a></b><br />
				</td><td id="article_links">
					<span><script type="text/javascript" src="http://w.sharethis.com/button/sharethis.js#publisher=3da06545-0753-46cb-8739-3ffcef208c1f&amp;type=website&amp;send_services=email&amp;post_services=facebook%2Cdigg%2Cdelicious%2Cstumbleupon%2Cblogger%2Cmyspace%2Cybuzz%2Creddit%2Ctechnorati%2Cmixx%2Cwordpress%2Ctypepad%2Cgoogle_bmarks%2Cwindows_live%2Cfark%2Cbus_exchange%2Cpropeller%2Cnewsvine%2Clinkedin"></script></span>
					<span><a href="http://www.facebook.com/share.php?u=<url>" onclick="return fbs_click()" target="_blank" class="fb_share_link">Facebook</a></span>
					<span><img src="http://cdn.stumble-upon.com/images/16x16_su_3d.gif" alt="" align="absmiddle" /><a target="_blank" href="http://www.stumbleupon.com/submit?url=http://www.hdtvmagazine.com/news/2009/09/awardwinning_instinct_lineup_expands_with_samsung_instinct_hd_from_sprint_adding_high_definition_camera_and_camcorder_hdtv_capabilities_and_opera_mobile_web_browser.php&title=Award-Winning Instinct Lineup Expands with Samsung Instinct HD from Sprint Adding High Definition Camera and Camcorder, HDTV Capabilities and Opera Mobile Web Browser">StumbleUpon</a></span>
					<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2009/09/awardwinning_instinct_lineup_expands_with_samsung_instinct_hd_from_sprint_adding_high_definition_camera_and_camcorder_hdtv_capabilities_and_opera_mobile_web_browser.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
					<span><img src="/images/save.gif" alt="Save Article" align="absmiddle" /><a target="_blank" href="<?=$base_url?>-save.php">Save</a></span>
					<span><img src="/images/email.gif" alt="Email Article" align="absmiddle" /><a href="<?=$email_url?>">Email</a></span>
					<span><img src="/images/print.png" alt="Print Article" align="absmiddle" /><a target="_blank" href="<?=$base_url?>-print.php">Print</a></span><br />
					<br /><br />
				</td>
			</tr>
		</table>
	<?}?>
	<div>
		<div id="right" style="float:right; margin:0 0 5px 5px; text-align:center;" align="center">
			<? include(BASE_DIR .'/ads/mrectangle.php');?>
			<br />
			<div align="center">
				<? include(BASE_DIR .'/ads/skyscraper.php');?>
			</div>
		</div>
		<div id="<?=$container?>">
			<? if ($sub_type > 0 && ($userdata['subscriptions'] & $sub_type)) {} else {?>
				<div class="important" style="display:table"><span class="corners-top"><span></span></span>
					<img src="/images/i_inbox.gif" align="left" style="float:left; padding-right:10px" />
					<span class="label"><?=$sub_label?>:</span>
					<?=$sub_desc?>
				<span class="corners-bottom"><span></span></span></div>
			<? }?>
			<div class="header_buttons">
				<? if (7 == 9 || 7 == 6) { # Only show in the test area and for podcasts?>
					<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle"></a></span>
					<span><a href="<?=$enclosure_url?>"><img src="/images/chicklet-mp3-podcast.gif" alt="Download Award-Winning Instinct Lineup Expands with Samsung Instinct HD from Sprint Adding High Definition Camera and Camcorder, HDTV Capabilities and Opera Mobile Web Browser" /></a></span>
				<? }?>
				<!--span><script type="text/javascript" src="http://www.retweet.com/static/retweets.js"></script></span-->
				<span><script src="http://tweetmeme.com/i/scripts/button.js"></script></span>
				<span><script src="http://digg.com/api/diggthis.js"></script></span>
				<br />
			</div><br />
			<p class="prtitle">Award-Winning Instinct Lineup Expands with Samsung Instinct HD from Sprint Adding High Definition Camera and Camcorder, HDTV Capabilities and Opera Mobile Web Browser</p>

<center><i>Available from Best Buy on Sept. 27 and all Sprint retail channels on Oct. 11, Samsung Instinct HD adds innovative entertainment tools on America’s most dependable 3G network</center></i><br />
<br />

<p><strong>OVERLAND PARK, Kan. &amp; DALLAS--(BUSINESS WIRE)--</strong>Sprint (NYSE: S) and Samsung Telecommunications America (Samsung Mobile), the #1 mobile phone provider in the United States1, today announced the upcoming availability of Samsung Instinct® HD, the latest follow-up to the award-winning Samsung Instinct, which made its debut last summer exclusively from Sprint.</p>

<p>Samsung Instinct HD boasts an attractive and intuitive user interface as well as the high-speed connectivity of America’s most dependable 3G network3 (EVDO Rev. A).With Samsung and Sprint’s first high-definition 5-megapixel camera and camcorder and TV-out HD connection, it allows photo and video playback on an HD capable auxiliary device but it does not provide HD playback directly on the handset.2</p>

<p>Samsung Instinct HD further improves on the original with an enhanced Web browsing experience, including a full Opera Mobile 9.7 browser, WiFi capabilities, an Ambient Light Sensor and Accelerometer. It also features a Proximity Sensor with haptic feedback that gives users a gentle vibration as they experience the virtual QWERTY keyboard.</p>

<p>Beginning Sept. 27, customers will be able to purchase Samsung Instinct HD at Best Buy Mobile, Sprint’s exclusive national retail partner. It will then be available Oct. 11 in all Sprint company-owned retail channels, including Web (<a target="_blank" href="http://www.sprint.com/">www.sprint.com</a>) and telesales (1-800-SPRINT1) for $249.99 with a new two-year service agreement after a $100 mail-in rebate (taxes and service charges excluded).</p>

<p>“When Instinct was announced last summer it received tremendous praise from our customers and showcased the power of application integration and a world class user-interface,” said Kevin Packingham, senior vice president of product development for Sprint. “Instinct HD adds to that legacy with the addition of a host of video services that are unmatched in the industry.”</p>

<p>”Samsung Instinct HD ups the ante with the full Opera Mobile 9.7 Web browser, WiFi connectivity and a 5-megapixel camera and camcorder, which allows the user to take high resolution pictures and HD-quality video,” said Omar Khan, senior vice president of strategy and product management for Samsung Mobile. “What sets Instinct HD apart even more is the ability to view those pictures and video on your HDTV or other HD-compatible monitor through the device’s HDTV out connection. These improvements raise the Instinct handset portfolio to a whole new level.”</p>

<p>Samsung Instinct HD offers all of the must-have features of its predecessors including:</p>

<p>    * Live Search for Sprint, powered by Microsoft, which provides easy access to directory information on-the-go, GPS-enabled directions, interactive maps and one-touch click to call access.<br />
    * Visual Voicemail, allowing users to listen to messages in their order of preference and manage them with a simple tap of the screen.<br />
    * Sprint TV®, with an extensive selection of channels and on-demand programming.<br />
    * Advanced stereo Bluetooth® 2.0.<br />
    * Support for personal and corporate email (sync with Microsoft® Outlook®).<br />
    * SMS voice and text messaging with threaded text.<br />
    * Easy access to social networking sites, including Facebook®, Flickr® and Twitter.</p>

<p>“We are excited about adding Instinct HD to our ever-growing assortment of smartphones at Best Buy Mobile,” said Jude Buckley, chief merchant and marketing officer for Best Buy Mobile. “Last year’s launch of the original Instinct was a big win for Sprint and Best Buy and, most important, for the customers we serve together. We expect the same from Instinct HD. Bringing this latest smartphone to our table will offer customers even greater choice and options as they look to upgrade to a smarter phone.”</p>

<p>Customers who purchase the new Instinct HD can also enjoy Sprint’s newly announced Any Mobile, Anytime(SM), which breaks the restrictive calling circle paradigm by providing unlimited calling to any wireless phone regardless of carrier at no additional charge with Everything Data plans from Sprint. Instinct HD requires activation on select pricing plans offering unlimited data, such as an Everything Data plan starting at just $69.99 per month or the Simply EverythingSM plan. Simply Everything p</p>

<p>rovides unlimited calling, unlimited text and unlimited data including e-mail, social networking, Web browsing, GPS navigation, Sprint TV, streaming music, NFL Mobile Live and NASCAR Sprint Cup Mobile for only $99.99 per month. That’s a savings of $1,200 over two years vs. a comparable AT&amp;T iPhone® plan4 (all price plans exclude Sprint surcharges and taxes).</p>

<p>The Sprint Mobile Broadband Network (inclusive of data roaming) reaches more than 269 million people, 18,652 cities and 1,838 airports. The Sprint Networks (inclusive of data roaming) have more than twice the coverage of AT&amp;T’s current 3G network and more than 20 times the coverage of T-Mobile’s current 3G network, both based on square miles5.</p>

<p>Customers looking to upgrade to Samsung Instinct HD should consider recycling their wireless device. Sprint is the industry leader in the reuse and recycling of wireless devices sold. Sprint has an aggressive industry-first goal of reaching a 90 percent phone collection rate for reuse/recycling compared with annual wireless device sales by 2017. To learn more about wireless recycling visit Sprint’s wireless recycling Web site.</p>

<p><br />
<strong>About Sprint Nextel</strong></p>

<p>Sprint Nextel offers a comprehensive range of wireless and wireline communications services bringing the freedom of mobility to consumers, businesses and government users. Sprint Nextel is widely recognized for developing, engineering and deploying innovative technologies, including two wireless networks serving almost 49 million customers at the end of the second quarter of 2009; industry-leading mobile data services; instant national and international push-to-talk capabilities; and a global Tier 1 Internet backbone. The company’s customer-focused strategy has led to improved first call resolution and customer care satisfaction scores. For more information, visit <a target="_blank" href="http://www.sprint.com/">www.sprint.com</a>.</p>

<p><br />
<strong>About Samsung Telecommunications America</strong></p>

<p>Samsung Telecommunications America, LLC, a Dallas-based subsidiary of Samsung Electronics Co., Ltd., researches, develops and markets wireless handsets and telecommunications products throughout North America. For more information, please visit <a target="_blank" href="http://www.samsungmobileusa.com/">www.samsungmobileusa.com</a>.</p>

<p><br />
<strong>About Samsung Electronics</strong></p>

<p>Samsung Electronics Co., Ltd. is a global leader in semiconductor, telecommunication, digital media and digital convergence technologies with 2008 consolidated sales of US$96 billion. Employing approximately 164,600 people in 179 offices across 61 countries, the company consists of two business units: Digital Media &amp; Communications and Device Solutions. Recognized as one of the fastest growing global brands, Samsung Electronics is a leading producer of digital TVs, memory chips, mobile phones and TFT-LCDs. For more information, please visit <a target="_blank" href="http://www.samsung.com/">www.samsung.com</a>.</p>

<p>1 Based upon reported shipment data from Strategy Analytics Q2 2009 U.S. Market Share Handset Shipments Report.</p>

<p>2 This handset allows photo and video playback on an HD-capable auxiliary device but it does not provide HD playback directly on the handset.</p>

<p>3“Dependable” based on independent, third-party drive tests for 3G data connection success, session reliability and signal strength for the top 50 most populous markets from January 2008 to July 2009. Not all services available on 3G and coverage may default to separate network when 3G unavailable. Coverage may not be available everywhere. Customers should refer to sprint.com/coverage for details.</p>

<p>4Savings based on publicly available information comparing AT&amp;T Nation Unlimited plus required iPhone data plan and optional unlimited text messaging totaling $149.99/month for AT&amp;T as of publication date, excluding taxes, surcharges and fees. iPhone is a registered trademark of Apple, Inc.</p>

<p>5 Coverage comparison based on publicly available information as of 4/01/09 inclusive of Sprint roaming partners. Based on square miles. Coverage comparison based on publicly available information as of 4/1/09 inclusive of Sprint roaming partners. Based on square miles.</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>September 24, 2009  3:58 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 3291
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
 			<h2>More on </h2><ul><?
 			$sql = "
 			SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name
 			FROM mt_entry e, mt_author a, mt_placement p, mt_category c
 			WHERE entry_author_id = author_id
 				AND e.entry_id = p.placement_entry_id
 				AND p.placement_category_id = c.category_id
 				AND category_label = ''
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

 		<? if (7 <> 7) {
 			# Recent Articles by Author (exclude this one)
 			# Do not show recent articles for Bulletins.
 			$qry = "
 			SELECT entry_id, entry_blog_id, entry_created_on, entry_title, author_id, author_name
 			FROM mt_entry e, mt_author a
 			WHERE entry_blog_id = 7
 				AND entry_id <> 3291
 				AND entry_status = 2
 				AND entry_author_id = a.author_id
 				AND a.author_name = 'Shane Sturgeon'
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
 				<h2>About Shane Sturgeon</h2>
 				<?=stripslashes($author[bio_short])?>
 			<span class="corners-bottom"><span></span></span></div>
 		<?}?>
		</td><td id="right">

 		<?if ($about != '') {?>
 			<div class="item"><span class="corners-top"><span></span></span>
 				<h2>About Bulletins</h2>
 				<?=$about?>
 			<span class="corners-bottom"><span></span></span></div>
		<?}?>

 		<div class="item"><span class="corners-top"><span></span></span>
 			<h2><a href="/forum/index.php">Other Recent Discussion</h2><ul class="brownsquare"><?
 				$qry = "
 				SELECT topic_title, t.topic_id, username as post_author, post_time, post_id
 				FROM phpbb_topics t, phpbb_users u, phpbb_posts p, aux_phpbb_forums af
 				WHERE
					t.forum_id = af.forum_id
					AND af.exclude_general = 0
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2009/09/awardwinning_instinct_lineup_expands_with_samsung_instinct_hd_from_sprint_adding_high_definition_camera_and_camcorder_hdtv_capabilities_and_opera_mobile_web_browser.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
