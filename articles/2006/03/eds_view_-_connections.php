<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 347";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Ed Milbourn'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 347 AND placement_is_primary = 1";
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
	<meta name="keywords" content="technical identification, definition purpose, identification ieee, data rates, digital audio, digital, HDTV, hdtv, network, interface, ethernet, audio, Ethernet, technical, definition, purpose, Definition, most, Identification, USB, comments, Purpose, Comments, ieee, IEEE" />
	<meta name="description" content="I continue to be amazed at the growing number of input jacks one finds on the back (and front) of today's HDTV sets.  This all started in the mid 1980's with the advent of the first audio/video components such as VCR's and early videodisc players.  These devices gave rise to the &quot;monitor/receiver&quot; with one or two sets of composite (Right, Left, Video) RCA jacks.  With the introduction of S-Video, another jack was added along with audio output jacks for the rising audio receiver market.  The final addition to the analog complement was the &quot;component&quot; inputs (Y, Pr, Pb or YUV).  This interface allowed the coupling of the wider bandwidth video information" />
	<title>HDTV Magazine Articles - Ed's View  -  Connections</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/eds_view_-_connections';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Ed\'s View  -  Connections'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/articles/2006/03/eds_view_-_connections.php";
		if ($author[img] != '' && 1 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Ed Milbourn" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Ed's View  -  Connections</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Ed Milbourn</b><br />
				<?=$author_title?>
				Posted on <b>March  7, 2006</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Technology">Technology</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2006/03/eds_view_-_connections.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/articles/2006/03/eds_view_-_connections.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/articles/2006/03/eds_view_-_connections.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2006/03/eds_view_-_connections.php&amp;phase=2&amp;title=Ed%27s%20View%20%20-%20%20Connections&amp;bodytext=I%20continue%20to%20be%20amazed%20at%20the%20growing%20number%20of%20input%20jacks%20one%20finds%20on%20the%20back%20%28and%20front%29%20of%20today%27s%20HDTV%20sets.%20%20This%20all%20started%20in%20the%20mid%201980%27s%20with%20the%20advent%20of%20the%20first%20audio%2Fvideo%20components%20such%20as%20VCR%27s%20and%20early%20videodisc%20players.%20%20These%20devices%20gave%20rise%20to%20the%20%22monitor%2Freceiver%22%20with%20one%20or%20two%20sets%20of%20composite%20%28Right%2C%20Left%2C%20Video%29%20RCA%20jacks.%20%20With%20the%20introduction%20of%20S-Video%2C%20another%20jack%20was%20added%20along%20with%20audio%20output%20jacks%20for%20the%20rising%20audio%20receiver%20market.%20%20The%20final%20addition%20to%20the%20analog%20complement%20was%20the%20%22component%22%20inputs%20%28Y%2C%20Pr%2C%20Pb%20or%20YUV%29.%20%20This%20interface%20allowed%20the%20coupling%20of%20the%20wider%20bandwidth%20video%20information&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p><img alt="M=MEDIUMEDMILBOURN.jpg" src="http://www.hdtvmagazine.com/articles/Images/M%3DMEDIUMEDMILBOURN.jpg" width="150" height="150"align="right"/>I continue to be amazed at the growing number of input jacks one finds on the back (and front) of today's HDTV sets.  This all started in the mid 1980's with the advent of the first audio/video components such as VCR's and early videodisc players.  These devices gave rise to the "monitor/receiver" with one or two sets of composite (Right, Left, Video) RCA jacks.  With the introduction of S-Video, another jack was added along with audio output jacks for the rising audio receiver market.  The final addition to the analog complement was the "component" inputs (Y, Pr, Pb or YUV).  This interface allowed the coupling of the wider bandwidth video information from DVD players to pass directly to the display, resulting in sharper pictures. However, because of the possibility of being able to copy high definition video via this interface, very few, if any, external HDTV decoder boxes use component connections.  </p>

<p>With the digital revolution and the increasing convergence of both TV and computer technologies, not only are we blessed with a plethora of multiple legacy analog jacks, but a whole new collection of digital interfaces, all with their own special purpose.  This must be confusing to the HDTV retail selling force and most likely their customers.  Most customers and retail sales personnel do not have a college degree in electrical engineering and computer science to fully understand what all of these holes and their complementary hoses do.  The instruction books help and are probably the best reference, but it seems a little more in-depth knowledge is needed to make full use of the capabilities of these multiple digital interface features.</p>

<p>I am not going to dwell on the analog connections as these are now well known, but let's take a moderately hard look at the various digital interfaces that are now (or will be) used in today's HDTV sets and related equipment.  Because of the growing convergence of HDTV with computer and telephone technologies, the more salient interfaces related to both HDTV and computer equipment will also be included.</p>

<p><strong>HDMI</strong></p>

<p><strong>Technical Identification</strong> - High Definition Multimedia Interface<br />
<strong>Definition/Purpose</strong> - HDMI is a secure, high frequency, uncompressed (baseband), single cable A/V interface.  HDMI is designed specifically to couple digital HDTV signals from various sources, such as DVRs, Cable and DBS boxes, to an HDTV display system.	<br />
<strong>Comments</strong> - 5Gbp/s capability handles all 18 DTV formats including 1080p.  HDMI supports up to eight channels of digital audio information.</p>

<p><strong>DVI</strong></p>

<p><strong>Technical Identification</strong> - Digital Video Interface<br />
<strong>Definition/Purpose</strong> - DVI is designed to provide a digital video connection between computer and monitor devices.  DVI has similar digital video bandwidth characteristics as HDMI, but it is not secure, nor does it carry audio signals.<br />
<strong>Comments</strong> - DVI is physically compatible with HDMI via an adapter.  However, the audio signals must be connected via separate audio cables.</p>

<p><strong>SPDIF </strong><br />
(Digital Audio Interface)</p>

<p><strong>Technical Identification</strong> - Sony/Philips Digital Interconnect Format<br />
<strong>Definition/Purpose</strong> - SPDIF provides a serial digital output stream from a digital audio source in either un-decoded PCM (Pulse Code Modulation) or decoded Dolby Digital (5.1 channel) formats.  SPDIF is normally used to connect digital audio signals from an HDTV receiver, DVD and/or DVR to a separate multi-channel (surround sound) audio processing (home theater) system.<br />
<strong>Comments</strong> - SPDIF employs either a coaxial (RCA jack) or optical physical interface.  Most new HDTV equipment incorporates only the optical interface.  Various sampling formats and sampling rates are automatically supported.  Bit rates can approach 3Mb/s.<br />
Note:  The SPDIF interface on some HDTV models will not support all Dolby Digital 5.1 channels if the HDMI is also utilized; only two-channel stereo is output.  In this case it is necessary to connect the SPDIF cable directly between the external A/V source and the "home theater" audio system.</p>

<p><strong>FIREWIRE</strong> (iLink)</p>

<p><strong>Technical Identification</strong> - IEEE 1394<br />
<strong>Definition/Purpose</strong> - IEEE 1394 provides a simple, low-cost, high frequency, bi-directional digital interface designed to interconnect various A/V products, including computers.  FireWire is designed to allow various '1394 compatible A/V components to be connected as a network with the components being coupled to each other in a "daisy chain" configuration.<br />
<strong>Comments</strong> - The ability of FireWire to simultaneous handle a wide range of various digital data formats, including real-time high data rate A/V streams and low-speed control streams as well as the ability to self-configure, makes it a prime candidate as the format for the illusive A/V local area (A/V cluster) network standard.  IEEE 1394 supports data rates of up to 400Mbps ('1394a) and 800Mbps ('1394b), sufficient to handle several independent HDTV compressed digital streams.</p>

<p><strong>DTV LINK</strong></p>

<p><strong>Technical Identification</strong> - IEEE 1394 plus 5C content protection<br />
<strong>Definition/Purpose</strong> - DTV Link is a specialized application of FireWire whereby a content (copy) protection layer, called 5C*, as well as specific control formats, are added to the basic IEEE 1394 interface.<br />
<strong>Comments</strong> - In order for FireWire to be a serious contender as the default format for local HDTV component cluster networks, it must be protected.  DTV Link provides that capability.</p>

<p><strong>ETHERNET</strong></p>

<p><strong>Technical Identification</strong> - IEEE 802.3 or 10 -100BaseT (Mbps over Twisted Pair)<br />
<strong>Definition/Purpose</strong> - Ethernet is the oldest and most popular Local Area Network (LAN) and Wide Area Network (WAN) technology.  Originally developed as a means to network computers and printers, it has evolved as the default solution for networking most any type of digital data.  Most new houses are now wired for physically transporting Ethernet via Category Five or Six (CAT 5 or 6) twisted-pair wiring.  Further, most Cable and DSL modems employ an Ethernet interface to allow direct connection to devices with Ethernet functionality.  Some HDTV models have built-in web browser software with an Ethernet interface, allowing these units to be directly connected to the Web via an Ethernet network or Ethernet enabled modem.<br />
<strong>Comments</strong> - Ethernet's evolving bandwidth capability, now over 1Gbps, and technical flexibility place this format in position to be the technology-of-choice as the A/V network standard for in-home and multiple building WANs.  Most any other network formats, such as FireWire, can be coupled to an Ethernet network via routing devices (Routers).</p>

<p>The above descriptions are of the most salient digital connections found on today's HDTV receivers and associated components.  However, because of that aforementioned increasing convergence of traditional consumer electronics and all information technologies, i.e. computers, games et al, other emerging connection technologies are certain to be embraced by HDTV equipment.  Therefore, for completeness, I have added a summary of the most important of these emerging and expanding connection technologies.</p>

<p><strong>USB</strong></p>

<p><strong>Technical Identification</strong> - Universal Serial Bus 1.1 and 2.0<br />
<strong>Definition/Purpose</strong> - USB was developed as a simple, easy means to physically connect computers with a variety of peripherals.  USB enables up to 127 separate devices to be "hot" connected to and self configured by a computer, therefore providing true "plug-n-play" capability.  USB 2.0 is a higher performance USB protocol, allowing data rates of to 480Mbps versus 12Mbps for USB 1.1.  USB 1.1 is fully back compatible with computers hosting USB 2.0.<br />
<strong>Comments</strong> - Although USB and FireWire are similar in concept, USB is designed mainly to network asynchronous peripherals such as printers, scanners and cameras, while FireWire is formatted for networking compressed, real-time audio/video multimedia devices.</p>

<p><strong>Wi-Fi </strong><br />
(Wireless Fidelity)</p>

<p><strong>Technical Identification</strong>  - IEEE 802.11b, a, g, and n<br />
<strong>Definition/Purpose</strong> - Wi-Fi is a short distance (LAN), broadband radio transceiver system designed to provide wireless digital network capability.  Wi-Fi is sometimes called "wireless Ethernet" because it uses the Ethernet protocol to drive various modulation schemes. Wi-Fi is transmitted in frequency bands centered at 2.4GHz and 5GHz.  The adoption of IEEE 1394 techniques to the basic Wi-Fi Ethernet protocol has resulted in robust multimedia data rates up to 1.6Gbps.<br />
<strong>Comments</strong> - As connections become increasingly wireless, Wi-Fi will become ubiquitous in the HDTV world.  Wi-Fi will be the basis for wirelessly connecting HDTV monitors to a variety of components located throughout the home.</p>

<p><strong>WiMax</strong><br />
(Worldwide Interoperability for Microwave Access)</p>

<p><strong>Technical Identification</strong> - IEEE 802.16a<br />
<strong>Definition/Purpose</strong> - WiMax is an extension of the basic Wi-Fi protocols designed to allow broadband data network coverage over a comparatively large area, called a Metropolitan Area Network (MAN).  WiMax is capable of 70Mbps data transfer rates over a 30-mile range.<br />
Microwave frequencies utilized are between 2 and 11GHz.<br />
<strong>Comments</strong> - WiMax is presently being deployed worldwide in many major urban and suburban areas to provide broadband access without the expense of the "last mile" premises connections.  With the present deregulation of many state and municipal telecommunications systems, WiMax networks are in a position to become a serious competitive threat to traditional Cable and Telco installations.<br />
  <br />
<strong>Bluetooth</strong></p>

<p><strong>Technical Identification</strong> - IEEE 802.15<br />
<strong>Definition/Purpose</strong> - Bluetooth is designed to be a short range (10 meter), low cost, low power, automatically self-configuring, signal/control network for personal-area networks (PANs).  <br />
<strong>Comments</strong> - Bluetooth has found its greatest commercial success in wireless earpiece extensions for cell phones; but, at this time, this technology does not have sufficient bandwidth capability for interconnecting HDTV A/V components. However, with more capacity, Bluetooth could be a contender for HDTV signal connection applications.  Bluetooth 2.0 can handle data rates up to 3 Mbps and employs spread-spectrum modulation centered at 2.45Ghz.  A local Bluetooth PAN network can manage up to eight devices.</p>

<p>At this time there are at least five different inter-industry groups working on DTV/HDTV network standards. Hopefully, there will be some convergence of thought from these groups that will result in a robust, secure HDTV A/V interconnectivity standard.  The tremendous pace of evolving technology is sometimes a nemesis to developing and establishing technical standards.  Further, the longer the network standards process takes, the harder it becomes to accommodate legacy equipment.  The best consumer strategy is to protect the investment in the display system (i.e. monitor), which should have a lifetime of at least ten years.  Separate signal delivering devices (i.e. boxes) are much less expensive to replace than the display.   HDMI is probably the most stable connection technology as we look to the future.  That's why I listed it first, and the more you have, the merrier.</p>

<p>Ed    </p>

<p>*Also known as Digital Transmission Licensing Administration (DTLA).  The "5C" refers to the five companies that developed and comprise the licensing group.</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Ed Milbourn</b>, <b>March  7, 2006 11:41 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 347
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
 			<h2>More on Technology</h2><ul><?
 			$sql = "
 			SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name
 			FROM mt_entry e, mt_author a, mt_placement p, mt_category c
 			WHERE entry_author_id = author_id
 				AND e.entry_id = p.placement_entry_id
 				AND p.placement_category_id = c.category_id
 				AND category_label = 'Technology'
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
 				AND entry_id <> 347
 				AND entry_status = 2
 				AND entry_author_id = a.author_id
 				AND a.author_name = 'Ed Milbourn'
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
 				<h2>About Ed Milbourn</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2006/03/eds_view_-_connections.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
