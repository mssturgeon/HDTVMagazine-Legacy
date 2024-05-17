<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');

	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "$author[title]<br />";

	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3456 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get enclosure info
	$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3456";
	$res_enclosure = mQuery($sql);
	$row_enclosure = mysql_fetch_assoc($res_enclosure);
	$enclosure_url = $row_enclosure['enclosure_url'];

	# Still need this to track page views. Used in the Links unit in the footer
	$google_links_channel = $author['channel'];

	# Set variables based on entry type. NOTE: Podcasts (& soon Reviews) has its own entry template, so it is not included amongst the choices below.
	switch (7) {
		case 1: # Articles
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/hdtv-articles" />';
			$feed_tracker = '<script src="http://feeds.feedburner.com/~s/hdtv-articles?i=http://www.hdtvmagazine.com/news/2010/01/dlink_debuts_boxee_box_at_ces_2010_directly_links_internet_entertainment_services_to_tvs_everywhere.php" type="text/javascript" charset="utf-8"></script>';
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
			$feed_tracker = '<script src="http://feeds.feedburner.com/~s/hdtv-interviews?i=http://www.hdtvmagazine.com/news/2010/01/dlink_debuts_boxee_box_at_ces_2010_directly_links_internet_entertainment_services_to_tvs_everywhere.php" type="text/javascript" charset="utf-8"></script>';
			break;
		case 5: # History
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
			$feed_tracker = '<script src="http://feeds.feedburner.com/~s/hdtv-archive?i=http://www.hdtvmagazine.com/news/2010/01/dlink_debuts_boxee_box_at_ces_2010_directly_links_internet_entertainment_services_to_tvs_everywhere.php" type="text/javascript" charset="utf-8"></script>';
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
			$feed_tracker = '<script src="http://feeds.feedburner.com/~s/hdtv-news?i=http://www.hdtvmagazine.com/news/2010/01/dlink_debuts_boxee_box_at_ces_2010_directly_links_internet_entertainment_services_to_tvs_everywhere.php" type="text/javascript" charset="utf-8"></script>';
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
			$feed_tracker = '<script src="http://feeds.feedburner.com/~s/hdtv-reviews?i=http://www.hdtvmagazine.com/news/2010/01/dlink_debuts_boxee_box_at_ces_2010_directly_links_internet_entertainment_services_to_tvs_everywhere.php" type="text/javascript" charset="utf-8"></script>';
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
			$feed_tracker = '<script src="http://feeds.feedburner.com/~s/hdtv-columns?i=http://www.hdtvmagazine.com/news/2010/01/dlink_debuts_boxee_box_at_ces_2010_directly_links_internet_entertainment_services_to_tvs_everywhere.php" type="text/javascript" charset="utf-8"></script>';
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
	<meta name="keywords" content="boxee box, social features, new content, internet entertainment, vice president, boxee, link, entertainment, box, users, internet, content, home, networking, shows, social, movies, digital, simple, friends, first, software, popular, video, new" />
	<meta name="description" content="D-LINK DEBUTS BOXEE BOX AT CES 2010; DIRECTLY LINKS INTERNET ENTERTAINMENT SERVICES TO TVs EVERYWHERE Networking pioneer and popular entertainment software create the best way to get the free entertainment the Internet has to offer with no monthly fee LAS..." />
	<title>HDTV Magazine - D-LINK DEBUTS BOXEE BOX AT CES 2010; DIRECTLY LINKS INTERNET ENTERTAINMENT SERVICES TO TVs EVERYWHERE</title>
	<!--title>HDTV Magazine Bulletins - D-LINK DEBUTS BOXEE BOX AT CES 2010; DIRECTLY LINKS INTERNET ENTERTAINMENT SERVICES TO TVs EVERYWHERE</title-->
	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript" src="http://www.sphere.com/widgets/sphereit/js?t=classic&amp;p=www.hdtvmagazine.com"></script>
	<script type="text/javascript">
		digg_url = 'http://www.hdtvmagazine.com/news/2010/01/dlink_debuts_boxee_box_at_ces_2010_directly_links_internet_entertainment_services_to_tvs_everywhere.php';
		digg_skin = 'compact';
		digg_window = 'new';
		digg_title = 'D-LINK DEBUTS BOXEE BOX AT CES 2010; DIRECTLY LINKS INTERNET ENTERTAINMENT SERVICES TO TVs EVERYWHERE';
		digg_bodytext = 'D-LINK DEBUTS BOXEE BOX AT CES 2010; DIRECTLY LINKS INTERNET ENTERTAINMENT SERVICES TO TVs EVERYWHERE Networking pioneer and popular entertainment software create the best way to get the free entertainment the Internet has to offer with no monthly fee LAS...';
//		digg_media = '';
		digg_topic = 'tech_news';

//		tweetmeme_url = 'http://yoururl.com';
		tweetmeme_style = 'compact';
		tweetmeme_source = 'HDTVMagazine';
		tweetmeme_service = 'bit.ly';

//		ReTweet settings
		url = 'http://www.hdtvmagazine.com/news/2010/01/dlink_debuts_boxee_box_at_ces_2010_directly_links_internet_entertainment_services_to_tvs_everywhere.php';
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

		$base_url = strleftback(PHP_SELF, '/') . '/dlink_debuts_boxee_box_at_ces_2010_directly_links_internet_entertainment_services_to_tvs_everywhere';
		$title_encoded = rawurlencode(addslashes('D-LINK DEBUTS BOXEE BOX AT CES 2010; DIRECTLY LINKS INTERNET ENTERTAINMENT SERVICES TO TVs EVERYWHERE'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2010/01/dlink_debuts_boxee_box_at_ces_2010_directly_links_internet_entertainment_services_to_tvs_everywhere.php";
		if ($author['img'] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author['img'] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}

	if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']), "google") === false) {?>
		<!-- Article Header -->
		<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
			<tr>
				<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
				<td class="article_title" colspan="2">D-LINK DEBUTS BOXEE BOX AT CES 2010; DIRECTLY LINKS INTERNET ENTERTAINMENT SERVICES TO TVs EVERYWHERE</td>
			</tr><tr>
			<td id="article_byline" nowrap="nowrap">
					By <b>Shane Sturgeon</b><br />
					<?=$author_title?>
					Posted on <b>January  5, 2010</b><br />
					Category: <b><a href="/category.php?id=<?=$category_id?>&category="></a></b><br />
				</td><td id="article_links">
					<span><script type="text/javascript" src="http://w.sharethis.com/button/sharethis.js#publisher=3da06545-0753-46cb-8739-3ffcef208c1f&amp;type=website&amp;send_services=email&amp;post_services=facebook%2Cdigg%2Cdelicious%2Cstumbleupon%2Cblogger%2Cmyspace%2Cybuzz%2Creddit%2Ctechnorati%2Cmixx%2Cwordpress%2Ctypepad%2Cgoogle_bmarks%2Cwindows_live%2Cfark%2Cbus_exchange%2Cpropeller%2Cnewsvine%2Clinkedin"></script></span>
					<span><a href="http://www.facebook.com/share.php?u=<url>" onclick="return fbs_click()" target="_blank" class="fb_share_link">Facebook</a></span>
					<span><img src="http://cdn.stumble-upon.com/images/16x16_su_3d.gif" alt="" align="absmiddle" /><a target="_blank" href="http://www.stumbleupon.com/submit?url=http://www.hdtvmagazine.com/news/2010/01/dlink_debuts_boxee_box_at_ces_2010_directly_links_internet_entertainment_services_to_tvs_everywhere.php&title=D-LINK DEBUTS BOXEE BOX AT CES 2010; DIRECTLY LINKS INTERNET ENTERTAINMENT SERVICES TO TVs EVERYWHERE">StumbleUpon</a></span>
					<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2010/01/dlink_debuts_boxee_box_at_ces_2010_directly_links_internet_entertainment_services_to_tvs_everywhere.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
					<span><a href="<?=$enclosure_url?>"><img src="/images/chicklet-mp3-podcast.gif" alt="Download D-LINK DEBUTS BOXEE BOX AT CES 2010; DIRECTLY LINKS INTERNET ENTERTAINMENT SERVICES TO TVs EVERYWHERE" /></a></span>
				<? }?>
				<!--span><script type="text/javascript" src="http://www.retweet.com/static/retweets.js"></script></span-->
				<span><script src="http://tweetmeme.com/i/scripts/button.js"></script></span>
				<span><script src="http://digg.com/api/diggthis.js"></script></span>
				<br />
			</div><br />
			<!-- sphereit start -->
			<p class="prtitle">D-LINK DEBUTS BOXEE BOX AT CES 2010; DIRECTLY LINKS INTERNET ENTERTAINMENT SERVICES TO TVs EVERYWHERE</p>

<center><i>Networking pioneer and popular entertainment software create the best way to get the free entertainment the Internet has to offer with no monthly fee</center></i><br />
<br />

<p><strong>LAS VEGAS, CES Booth 36232, South Hall, LVCC, Jan. 5, 2010</strong> - D-Link made lots of geeks and early adopters happy today by introducing the revolutionary Boxee Box by D-Link, winner of the CES Best of Innovations award in the Home Entertainment category.</p>

<p>The Boxee Box by D-Link reinterprets what TV should be. The Boxee Box delivers movies, TV shows, music, and photos from a user's computer, home network, and the Internet to their HDTV with no PC needed.  Additionally, Boxee's core social features make it easy for friends to discover new content from each other through social networks like Facebook, Twitter, and more. </p>

<p><br />
<strong>Internet Entertainment</strong></p>

<p>Boxee is a popular PC, Mac, and Linux software program that lets users watch hundreds of thousands of popular TV shows and movies. Instead of sifting through millions of confusing Web sites, when users search on Boxee, TV shows and movies are delivered to them with the click of a remote control. Nearly a million Internet users around the world have already downloaded Boxee to enjoy their online entertainment.</p>

<p>The Boxee Box by D-Link takes the same popular software and offers it up as a great device -- the perfect companion to a high definition TV. The Boxee Box by D-Link provides access to more than just traditional TV content.  It includes a huge library that spans the Internet, such as university courses, panel discussions, academic lectures, presentations, web-only videos and more from TED, Stanford, FORA.tv, Kid Mango, Next New Networks and others. Boxee also makes it easy for users to add their own favorite entertainment sources with simple RSS or XML feeds available for most online video. </p>

<p>In addition to video content, Boxee users can access great music from sites like Pandora, last.fm, shoutcast, and We are Hunted as well as stunning photos from sites like flickr, Picasa and Facebook.</p>

<p><br />
<strong>Personal Entertainment</strong></p>

<p>For entertainment lovers who have built their own collections of digital media stored on their computer hard drive or home network, Boxee automatically identifies their content and downloads relevant cover art, synopses, reviews, subtitles, lyrics and more.  This feature turns boring files and folders into beautiful media libraries that make it simple and appealing to navigate a collection of favorite movies, TV shows, and playlists with a simple remote. Furthermore, the Boxee Box by D-Link has extensive format support (see below) which ensures that when users hit the play button, they get instant gratification, with no need to download codecs or drivers. Also, with built-in 802.11n Wi-Fi support, it can transfer files without delay and from longer distances within a user's home.   </p>

<p><br />
<strong>Social Features</strong></p>

<p>The Boxee Box by D-Link keeps people connected with social features to help users discover new content from friends, experts, and tastemakers. <br />
The first step to discovery is sharing, and Boxee makes this easy by letting people recommend any playing content to friends.  Additionally Boxee automatically uses recommendations from a user's Twitter and Facebook friends so they can find new content and instantly enjoy it on the big screen.  Since anyone can build on top of Boxee's open App platform, users can craft their own truly custom experience by creating or downloading plug-ins, add-ons, games, and more. </p>

<p>"We are pleased to partner with Boxee and to be the first with such a ground-breaking device," said Nick Tidd, vice president of sales of D-Link Pan America and vice president of marketing for D-Link North America.  "This powerful device with its unique form factor truly leverages Boxee's service and is the best way for consumers to quickly access the growing volume of Internet content, organize it and stream it to their TVs and home entertainment centers." </p>

<p>"D-Link's successful track record in bringing to market, award-winning digital home networking products, and its global marketing, distribution and channel sales capabilities made them a great fit for our first hardware vendor." stated Andrew Kippen, vice president of marketing for Boxee, "The Boxee Box by D-Link gives consumers what they want - an easy way to watch Internet or personal entertainment in their living rooms with a simple set-top box that costs under $200 and has no monthly fees."</p>

<p>The Boxee Box by D-Link is scheduled to ship in the first half of 2010 through the company's vast network of retail and e-tail outlets, and at D-Link's online store, <a target="_blank" href="http://www.dlinkshop.com/">www.dlinkshop.com</a>.</p>

<p><br />
<strong>Supported Codecs &amp; Formats</strong></p>

<p>Boxee can be used to play/view practically all common multimedia formats, including:</p>

<p>VIDEO: <br />
Adobe Flash 10.1 <br />
H.264  (MKV, MOV) <br />
VC-1 <br />
WMV <br />
MPEG-1 <br />
MPEG-2 <br />
MPEG-4 <br />
AVI <br />
Xvid <br />
Divx   <br />
PCM/LPCM <br />
VOB </p>

<p>AUDIO: <br />
MP3 <br />
WMA <br />
WAV <br />
AIFF <br />
FLAC <br />
AAC <br />
DTS <br />
Dolby Digital <br />
Ogg Vorbis </p>

<p>PHOTO: <br />
JPEG <br />
TIFF <br />
BMP <br />
PNG </p>

<p><br />
<strong>About D-Link</strong></p>

<p>D-Link is the global leader in connectivity for small, medium and large enterprise business networking. The company is an award-winning designer, developer and manufacturer of networking, broadband, digital electronics, voice, data and video communications solutions for the digital home, Small Office/Home Office (SOHO), Small to Medium Business (SMB), and Workgroup to Enterprise environments.  With millions of networking and connectivity products manufactured and shipped, D-Link is a dominant market participant and price/performance leader in the networking and communications market.   D-Link Systems, Inc. headquarters are located at 17595 Mt. Herrmann Street, Fountain Valley, CA, 92708. Phone (800) 326-1688 or (714) 885-6000; FAX (866) 743-4905; Internet <a target="_blank" href="http://www.dlink.com/">www.dlink.com</a>.</p>

<p><br />
<strong>About Boxee</strong></p>

<p>Boxee is the first "social" media center, whose free, downloadable software and enabled devices are changing the way consumers experience home entertainment. On a computer or on a dedicated device connected to an HDTV, boxee gives users a simple way to bring all their entertainment into one place including personal movies, TV shows, music and photos, as well as streaming content from websites like Netflix, MLB.TV, Pandora, Last.fm, and flickr. Users can also share information about what they're watching so friends can enjoy it too through legal sources online. Nearly a million people use Boxee to get their entertainment.  Learn how you can join them at <a target="_blank" href="http://www.boxee.tv/">www.boxee.tv</a>.</p>

<p><br />
D-Link, Boxee Box by D-Link and the D-Link logo are trademarks or registered trademarks of D-Link Corporation or its subsidiaries. All other third party marks mentioned herein may be trademarks of their respective owners. Copyright &copy; 2009. D-Link. All Rights Reserved.</p>
			<!-- sphereit end -->
			<div align="right"><a class="iconsphere" title="Related Blogs &amp; Articles" onclick="return Sphere.Widget.search()" href="http://www.sphere.com/search?q=sphereit:http://www.hdtvmagazine.com/news/2010/01/dlink_debuts_boxee_box_at_ces_2010_directly_links_internet_entertainment_services_to_tvs_everywhere.php">Sphere: Related Content</a></div>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>January  5, 2010  5:46 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 3456
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
 				AND entry_id <> 3456
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2010/01/dlink_debuts_boxee_box_at_ces_2010_directly_links_internet_entertainment_services_to_tvs_everywhere.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
