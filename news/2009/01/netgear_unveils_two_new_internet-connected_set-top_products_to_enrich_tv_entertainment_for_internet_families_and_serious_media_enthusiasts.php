<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');

	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1619";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);

	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";

	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1619 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category[placement_category_id];

	# Still need this to track page views. Used in the Links unit in the footer
	$google_links_channel = $author[channel];

	# Set variables based on entry type. NOTE: Podcasts has its own entry template, so it is not included amongst the choices below.
	switch (7) {
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
	<meta name="keywords" content="digital entertainer, entertainer elite, internet videos, digital media, serious media, netgear, Internet, internet, NETGEAR, digital, video, products, Digital, Entertainer, videos, Elite, entertainer, media, elite, wireless, player, content, storage, connected, Player" />
	<meta name="description" content="At the Consumer Electronics Show (CES) opening today, NETGEAR(R), Inc. (NASDAQ:NTGR) , a worldwide provider of technologically innovative, branded networking solutions, introduced two new products that enable consumers to enjoy the world of Internet videos and digital entertainment content on their HDTV screens. Designed to enhance the connected lifestyles of Internet-generation families and serious media enthusiasts, the NETGEAR Internet TV Player (ITV2000) and the NETGEAR Digital Entertainer Elite (EVA9150) will be..." />
	<title>HDTV Magazine Bulletins - NETGEAR Unveils Two New Internet-Connected Set-Top Products to Enrich TV Entertainment for Internet Families and Serious Media Enthusiasts</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
//		var federated_media_section = 'holiday';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/ads/leaderboard.php');
		include(BASE_DIR .'/includes/body_header.php');

		$base_url = strleftback(PHP_SELF, '/') . '/netgear_unveils_two_new_internet-connected_set-top_products_to_enrich_tv_entertainment_for_internet_families_and_serious_media_enthusiasts';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('NETGEAR Unveils Two New Internet-Connected Set-Top Products to Enrich TV Entertainment for Internet Families and Serious Media Enthusiasts'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2009/01/netgear_unveils_two_new_internet-connected_set-top_products_to_enrich_tv_entertainment_for_internet_families_and_serious_media_enthusiasts.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">NETGEAR Unveils Two New Internet-Connected Set-Top Products to Enrich TV Entertainment for Internet Families and Serious Media Enthusiasts</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>January  7, 2009</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Products & Equipment">Products & Equipment</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2009/01/netgear_unveils_two_new_internet-connected_set-top_products_to_enrich_tv_entertainment_for_internet_families_and_serious_media_enthusiasts.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2009/01/netgear_unveils_two_new_internet-connected_set-top_products_to_enrich_tv_entertainment_for_internet_families_and_serious_media_enthusiasts.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2009/01/netgear_unveils_two_new_internet-connected_set-top_products_to_enrich_tv_entertainment_for_internet_families_and_serious_media_enthusiasts.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2009/01/netgear_unveils_two_new_internet-connected_set-top_products_to_enrich_tv_entertainment_for_internet_families_and_serious_media_enthusiasts.php&amp;phase=2&amp;title=NETGEAR%20Unveils%20Two%20New%20Internet-Connected%20Set-Top%20Products%20to%20Enrich%20TV%20Entertainment%20for%20Internet%20Families%20and%20Serious%20Media%20Enthusiasts&amp;bodytext=At%20the%20Consumer%20Electronics%20Show%20%28CES%29%20opening%20today%2C%20NETGEAR%28R%29%2C%20Inc.%20%28NASDAQ%3ANTGR%29%20%2C%20a%20worldwide%20provider%20of%20technologically%20innovative%2C%20branded%20networking%20solutions%2C%20introduced%20two%20new%20products%20that%20enable%20consumers%20to%20enjoy%20the%20world%20of%20Internet%20videos%20and%20digital%20entertainment%20content%20on%20their%20HDTV%20screens.%20Designed%20to%20enhance%20the%20connected%20lifestyles%20of%20Internet-generation%20families%20and%20serious%20media%20enthusiasts%2C%20the%20NETGEAR%20Internet%20TV%20Player%20%28ITV2000%29%20and%20the%20NETGEAR%20Digital%20Entertainer%20Elite%20%28EVA9150%29%20will%20be...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p class="prtitle">NETGEAR Unveils Two New Internet-Connected Set-Top Products to Enrich TV Entertainment for Internet Families and Serious Media Enthusiasts</p>

<center><i>Bringing the World of Internet Videos, Live Internet TV, YouTube, Popular Websites, HD Media Collections, Family Photos, Music and More to the TV</i></center><br />
<br />

<p><B>LAS VEGAS, Jan. 7 /PRNewswire-FirstCall/</B> -- At the Consumer Electronics Show (CES) opening today, NETGEAR(R), Inc. (NASDAQ:NTGR) , a worldwide provider of technologically innovative, branded networking solutions, introduced two new products that enable consumers to enjoy the world of Internet videos and digital entertainment content on their HDTV screens. Designed to enhance the connected lifestyles of Internet-generation families and serious media enthusiasts, the NETGEAR Internet TV Player (ITV2000) and the NETGEAR Digital Entertainer Elite (EVA9150) will be demonstrated this week in the NETGEAR booth, 30769, in the South Hall 3 of the Las Vegas Convention Center. (See press release, "NETGEAR Delivers the Connected Lifestyle at the 2009 Consumer Electronics Show.")</p>

<p>"Internet video consumption is at an all-time high," said Vivek Pathela, NETGEAR's vice president and general manager of home/consumer products. "Just in the month of October 2008 alone, comScore(R) estimated that almost half of the total U.S. population viewed more than 13.5 billion online videos. That's a large number of Internet videos that are viewed mostly on PCs, even though many people would rather watch them on their TVs."</p>

<p>Pathela added, "Our new family of Internet-connected set-top products will, for the first time, enable users to benefit from viewing the broadest spectrum of Internet videos and digital content on their HDTVs, in the comfort of their living rooms. We're offering a diverse line-up of networked entertainment products designed to suit the needs of various connected lifestyles, from the Internet family to the serious media enthusiast."</p>

<p><br />
<B>Internet TV Player (ITV2000)</B></p>

<p>NETGEAR's Internet TV Player (ITV2000) is a compact, easy-to-use, "plug in and go" Internet set-top device with a simple remote control that enables viewers to catch up on the world of Internet videos including YouTube(TM), live Internet TV, popular Internet video websites, premium video-on-demand and online video searches retrieving billions of Internet videos from a place it was previously unavailable -- the TV in their living rooms. Rather than watching videos on PC screens in separate rooms, families can watch video from a variety of Internet sources on the TV together, in the comfort of their den or family room.</p>

<p>The Internet TV Player unlocks the value of new HDTVs as well as old analog TVs. It is ideal for the Internet families who enjoy online video, and for those who are geographically displaced from their preferred television content, such as international sporting events and Bollywood productions. It streams content from popular sites such as BBC.com, CNN.com, ESPN.com, EuroSport.com, NBC.com, PGATour and TMZ.com, as well as video powerhouses YouTube, Google Videos(TM), Yahoo Videos(TM) and MetaCafe(TM). NETGEAR's Internet TV Player supports streaming of live TV broadcasts from Internet sites around the world, and premium, paid movies on demand such as CinemaNow.com, in addition to downloaded videos from sites such as BitTorrent(R). Its superior VTap(TM) video search capabilities enable the intelligent search of Internet videos, including targeting video sites by country, topic of interest, person or popular website. Consumers are also able to play video, music, and photos from a local USB flash drive as well as from the NETGEAR ReadyNAS(R) family of storage solutions.</p>

<p>Slightly larger than a deck of cards, the compact Internet TV Player connects to the home network and the Internet via Ethernet or wireless USB adapter. It does not require a PC to play Internet video, nor does it require installing any PC software or setting up file sharing or firewall settings.</p>

<p>The NETGEAR Internet TV Player (ITV2000) is expected to be available in early Summer 2009 through leading retailers, e-commerce sites, and value-added resellers, at an MSRP in the U.S. of $199. More information can be found at (http://www.netgear.com/Products/Entertainment/DigitalMediaPlayers/itv2000.asp x).</p>

<p><br />
<B>Digital Entertainer Elite (EVA9150)</B></p>

<p>An ideal solution for serious media enthusiasts, NETGEAR's Digital Entertainer Elite (EVA9150) is the most powerful and flexible digital media player available, incorporating the latest video, audio and wireless technologies to deliver an unparalleled living room experience. Its integrated 500GB hard drive, easily user-upgradeable to larger capacity disks, makes the Digital Entertainer Elite the world's most powerful home video jukebox on the market. Consumers can play on their TVs Blu-Ray quality up to 1080p digital video, high-resolution digital photos, MP3s and recorded television shows from their PCs or storage on their network. They can also enjoy Internet content, such as YouTube, Internet radio, Flickr(TM), RSS feeds, and videos from popular websites. The Digital Entertainer Elite automatically finds all digital media files on the home network and organizes them into an easily accessible library.</p>

<p>The Digital Entertainer Elite is exceptional in that it works simultaneously with Windows(R), Mac(R) and Linux computers, and Networked Attached Storage (NAS) devices, such as NETGEAR's award-winning family of ReadyNAS(R) storage solutions. It supports an unmatched list of file formats, including:</p>

<p>  --  Video formats: AVI, DivX, Xvid WMV, MOV, M4V, MP4, VOB, MPG, MP1, MP2,<br />
      MP4, ISO, IFO, MKV, TS and M2TS;<br />
  --  Audio formats: MP1, MP2, MP3, WMA, WMA-Pro, AAC, FLAC, AIFF, WAV,<br />
      LPCM, Dolby AC3 5.1 stereo downmix, Dolby AC3 5.1 passthrough, DTS 5.1<br />
      passthrough, Dolby TrueHD Downmix, Dolby+ Passthrough and DTS-HD<br />
      Master Audio passthrough;<br />
  --  Subtitle formats: SUB, SRT, SMI, SAMI, TXT and DVD Subpicture; and,<br />
  --  Video codecs: MP1, MP2, MP4, Xvid, VC-1/WMV-9, H.264 and OpenDivX.</p>

<p></p>

<p>The Digital Entertainer Elite's two USB ports also enable instant access to content on a USB flash drive, digital camera, iPod(R) or other USB storage device. Three of NETGEAR's exclusive RangeMax(TM) internal metamaterial antennas and Dual-Band wireless 802.11n give the Digital Entertainer Elite the best wireless performance of any digital media player on the market. More specifically, the Dual-Band wireless enables the Digital Entertainer Elite to pick from not only three 2.4GHz channels, crowded by 802.11g wireless networks in the neighborhood, but up to twenty clearer channels in the 5GHz band. Further, the Sigma Designs 8635 chip ensures the most powerful and flexible audio and video performance.</p>

<p>Multiple NETGEAR Digital Entertainer Elite units can work in concert throughout a house. Using "Follow Me," consumers can pause a video in one room and resume it in another. While in "Party Mode," they can synchronize music playback for whole-home listening.</p>

<p>The Digital Entertainer Elite also features NETGEAR's award-winning Push 'N' Connect to seamlessly and securely connect wireless clients based on Wi-Fi Protected Set-up (WPS), eliminating the need to remember or input password keys. Further, NETGEAR's Digital Entertainer Elite includes environmentally friendly features, such as an energy-efficient power supply and auto-sleep mode.</p>

<p>Backed by a one-year warranty and full 24/7 technical support, the NETGEAR Digital Entertainer Elite (EVA9150) is expected to be available in February through leading retailers, e-commerce sites, and value-added resellers at an MSRP in the U.S. of $399. More information can be found at (http://www.netgear.com/Products/Entertainment/DigitalMediaPlayers/eva9150.asp x).</p>

<p>"NETGEAR's product introductions take advantage of two major trends that we are seeing with consumers -- digital media and connectivity," said Kurt Scherf, vice president and principal analyst of Parks Associates, which specializes in research and analysis for digital living technologies. "First, as digital media collections grow, we anticipate that the average broadband household will require a significant amount of additional storage and media centralization capabilities. Second, our Digital Media Evolution study finds high demand for connected CE experiences that can deliver premium Web content to the living room."</p>

<p><br />
<B>About NETGEAR, Inc.</B></p>

<p>NETGEAR (NASDAQGM: NTGR) designs innovative, branded technology solutions that address the specific networking, storage, and security needs of small- to medium-sized businesses and home users. The company offers an end-to-end networking product portfolio to enable users to share Internet access, peripherals, files, multimedia content, and applications among multiple computers and other Internet-enabled devices. Products are built on a variety of proven technologies such as wireless, Ethernet and powerline, with a focus on reliability and ease-of-use. NETGEAR products are sold in over 29,000 retail locations around the globe, and via more than 41,000 value-added resellers. The company's headquarters are in San Jose, Calif., with additional offices in 25 countries. NETGEAR is an ENERGY STAR(R) partner. More information is available by visiting www.netgear.com or calling (408) 907-8000.</p>

<p>(C) 2009 NETGEAR, Inc. NETGEAR(R), the NETGEAR logo, RangeMax and ReadyNAS are trademarks or registered trademarks of NETGEAR, Inc. in the United States and/or other countries. Other brand and product names are trademarks or registered trademarks of their respective holders. Information is subject to change without notice. All rights reserved.</p>

<p>Note: Maximum wireless signal rate derived from IEEE Standard 802.11 specifications. Actual data throughput will vary from maximum signal rates stipulated. Network conditions and environmental factors, including volume of network traffic, building materials and construction, and network overhead, lower actual data throughput rate.</p>

<p>Safe Harbor Statement under the Private Securities Litigation Reform Act of 1995 for NETGEAR, Inc.:</p>

<p>This press release contains forward-looking statements within the meaning of the U.S. Private Securities Litigation Reform Act of 1995. Specifically, statements concerning the expected performance characteristics, specifications, market acceptance, market growth, specific uses, user feedback and market position of NETGEAR's products and technology are forward-looking statements within the meaning of the Safe Harbor. These statements are based on management's current expectations and are subject to certain risks and uncertainties, including, without limitation, the following: the actual price, performance and ease of use of NETGEAR's products may not meet the price, performance and ease of use requirements of customers; product performance may be adversely affected by real world operating conditions; failure of products may under certain circumstances cause permanent loss of end user data; new viruses or Internet threats may develop that challenge the effectiveness of security features in NETGEAR's products; the ability of NETGEAR to market and sell its products and technology; the impact and pricing of competing products; and the introduction of alternative technological solutions. Further information on potential risk factors that could affect NETGEAR and its business are detailed in the Company's periodic filings with the Securities and Exchange Commission, including, but not limited to, those risks and uncertainties listed in the section entitled "Part II - Item 1A. Risk Factors," pages 31 through 44, in the Company's Quarterly Report on Form 10-Q for the fiscal quarter ended September 28, 2008, filed with the Securities and Exchange Commission on November 7, 2008. NETGEAR undertakes no obligation to release publicly any revisions to any forward-looking statements contained herein to reflect events or circumstances after the date hereof or to reflect the occurrence of unanticipated events.</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>January  7, 2009 11:02 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 1619
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

 		<?if (7 <> 7) {
 			# Recent Articles by Author (exclude this one)
 			# Do not show recent articles for Bulletins.
 			$qry = "
 			SELECT entry_id, entry_blog_id, entry_created_on, entry_title, author_id, author_name
 			FROM mt_entry e, mt_author a
 			WHERE entry_blog_id = 7
 				AND entry_id <> 1619
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2009/01/netgear_unveils_two_new_internet-connected_set-top_products_to_enrich_tv_entertainment_for_internet_families_and_serious_media_enthusiasts.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
