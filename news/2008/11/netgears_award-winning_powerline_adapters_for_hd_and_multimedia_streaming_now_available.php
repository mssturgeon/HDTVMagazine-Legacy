<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1553";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1553 AND placement_is_primary = 1";
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
	<meta name="keywords" content="ethernet adapter, adapter kit, plus ethernet, high speed, netgear inc, netgear, powerline, NETGEAR, Powerline, Ethernet, ethernet, Adapter, adapter, kit, Kit, products, high, network, plus, Plus, home, Internet, networking, internet, speed" />
	<meta name="description" content="NETGEAR(R), Inc. (NASDAQ:NTGR) , a worldwide provider of technologically innovative, branded networking solutions, today announced the general availability of its latest powerline adapters for turning electrical power outlets into high-speed Internet and home network connections. Fast enough to stream high-definition video, the award-winning Powerline HD Plus Ethernet Adapter Kit (HDXB111) and Powerline AV Ethernet Adapter Kit (XAVB101) provide reliable, high-speed network connections up to..." />
	<title>HDTV Magazine Bulletins - NETGEAR's Award-Winning Powerline Adapters for HD and Multimedia Streaming Now Available</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
//		var federated_media_section = 'holiday';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/netgears_award-winning_powerline_adapters_for_hd_and_multimedia_streaming_now_available';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('NETGEAR\'s Award-Winning Powerline Adapters for HD and Multimedia Streaming Now Available'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2008/11/netgears_award-winning_powerline_adapters_for_hd_and_multimedia_streaming_now_available.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">NETGEAR's Award-Winning Powerline Adapters for HD and Multimedia Streaming Now Available</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>November 17, 2008</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Products & Equipment">Products & Equipment</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2008/11/netgears_award-winning_powerline_adapters_for_hd_and_multimedia_streaming_now_available.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2008/11/netgears_award-winning_powerline_adapters_for_hd_and_multimedia_streaming_now_available.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2008/11/netgears_award-winning_powerline_adapters_for_hd_and_multimedia_streaming_now_available.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2008/11/netgears_award-winning_powerline_adapters_for_hd_and_multimedia_streaming_now_available.php&amp;phase=2&amp;title=NETGEAR%27s%20Award-Winning%20Powerline%20Adapters%20for%20HD%20and%20Multimedia%20Streaming%20Now%20Available&amp;bodytext=NETGEAR%28R%29%2C%20Inc.%20%28NASDAQ%3ANTGR%29%20%2C%20a%20worldwide%20provider%20of%20technologically%20innovative%2C%20branded%20networking%20solutions%2C%20today%20announced%20the%20general%20availability%20of%20its%20latest%20powerline%20adapters%20for%20turning%20electrical%20power%20outlets%20into%20high-speed%20Internet%20and%20home%20network%20connections.%20Fast%20enough%20to%20stream%20high-definition%20video%2C%20the%20award-winning%20Powerline%20HD%20Plus%20Ethernet%20Adapter%20Kit%20%28HDXB111%29%20and%20Powerline%20AV%20Ethernet%20Adapter%20Kit%20%28XAVB101%29%20provide%20reliable%2C%20high-speed%20network%20connections%20up%20to...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p class="prtitle">NETGEAR's Award-Winning Powerline Adapters for HD and Multimedia Streaming Now Available</p>

<center><i>Innovative Powerline Devices Offer Consumers 200 Mbps Speeds with Broad Options in Technical Design</i></center><br />
<br />

<p><B>SAN JOSE, Calif., Nov. 17 /PRNewswire-FirstCall/</B> -- NETGEAR(R), Inc. (NASDAQ:NTGR) , a worldwide provider of technologically innovative, branded networking solutions, today announced the general availability of its latest powerline adapters for turning electrical power outlets into high-speed Internet and home network connections. Fast enough to stream high-definition video, the award-winning Powerline HD Plus Ethernet Adapter Kit (HDXB111) and Powerline AV Ethernet Adapter Kit (XAVB101) provide reliable, high-speed network connections up to 200 Mbps to the home network and Internet over existing electrical wires to devices such as digital media receivers, set-top boxes, game consoles, personal computers, and networked attached storage.</p>

<p>NETGEAR offers a broad portfolio of networking products so consumers can choose the option that best meets their needs. NETGEAR's powerline technology products distribute high-speed, high-performance, affordable broadband throughout the home, without the need to run any cabling between rooms. Both the Powerline AV Ethernet Adapter Kit, which is based on the HomePlug(R) AV standard, and the UPA-based Powerline HD Plus Ethernet Adapter Kit, provide integrated Quality of Service (QoS), to enable hours of glitch-free connectivity for high-definition video streaming, online gaming and uninterrupted Voice over IP (VoIP) phone calls. The easy operation of both products is based on a plug-in design and push-button data encryption that provides privacy and security for a trouble-free set-up. In addition, the Powerline HD Plus Ethernet Adapter Kit features built-in power sockets to save power outlet space.</p>

<p>"The increasing demand for consistent high-speed Internet connectivity throughout the entire home compels networking providers to engineer products that are not only fast and reliable, but also simple to install for the average consumer," stated Chris Geiser, NETGEAR's product line manager for Powerline devices. "Powerline's ability to use existing wiring supports the distribution of high-quality broadband connection to all areas of the home, even wireless problem areas, enabling customers to enjoy bandwidth intensive applications such as online gaming, audio distribution and HD video streaming, in any room of the house."</p>

<p>About the NETGEAR Powerline HD Plus Ethernet Adapter Kit (HDXB111)</p>

<p>By simply plugging one stylish UPA-certified Powerline HD Plus Ethernet Adapter into an AC outlet near a modem, gateway, or router and another near any Ethernet-ready device, consumers can instantly enjoy speeds up to 200 Mbps and access to reliable HD streaming throughout the house. Furthermore, with outlet space limited in many homes, the kit is NETGEAR's first Powerline networking device to offer innovative pass-through outlet capabilities by incorporating a built-in, noise-filtered AC plug.</p>

<p>As testament to its innovative design and engineering qualities, the Powerline HD Plus Ethernet Adapter Kit won the prestigious Best of Innovations award in the home networking category of the Innovations Design and Engineering Awards Program at the 2008 Consumer Electronics Show. In addition to its unique pass-through functionality, the subtle, natural shade of the Powerline kit is designed to blend into its environment and minimize the appearance of clutter.</p>

<p>NETGEAR's plug-and-play Powerline HD Plus Ethernet Adapter Kit (HDXB111) is now available in North America through leading retailers, e-commerce sites and value-added resellers. It is backed by a one-year warranty and 24/7 technical support. The Powerline HD Plus Ethernet Adapter Kit, containing two Powerline HD Plus Ethernet Adapters, an Ethernet cable, an installation guide and a set-up CD, has an MSRP in the U.S. of $169.99. NETGEAR's Powerline HD Ethernet Adapter (HDX101) and Powerline HD Ethernet Adapter Kit (HDXB101) are compatible with the Powerline HD Plus Ethernet Adapter kit (HDXB111).</p>

<p>Photos and other product information can be found on the NETGEAR web site at (http://www.netgear.com/Products/PowerlineNetworking/PowerlineEthernetAdapters /HDXB111.aspx).</p>

<p>About the NETGEAR Powerline AV Ethernet Adapter Kit (XAVB101)</p>

<p>Based on the HomePlug AV standard and compatible with both wired and wireless routers and gateways, consumers can simply plug one Powerline AV Ethernet Adapter into their router and another into any Ethernet-ready device to turn any electrical power outlet into a high speed Internet and home network connection. Securing a Powerline AV network is made simple with the push of a button as the kit features a 128-bit AES data encryption to ensure privacy and security.</p>

<p>As evidence of its reliability and user-friendly characteristics, the Powerline AV Ethernet Adapter Kit was an Editor's Choice in PC Magazine on October 21, 2008, awarded four out of five stars. Reviewer Mario Morejon wrote, " ... the NETGEAR Powerline can't be beat. This is a must-have product if you need a fast and secure network for your small business and can't afford remodeling for the sake of obtaining faster network speed."</p>

<p>NETGEAR's Powerline AV Ethernet Adapter Kit (XAVB101) is now available worldwide through leading retailers, e-commerce sites and value-added resellers. It is backed by a one-year warranty and 24/7 technical support. The Powerline AV Ethernet Adapter Kit, containing two Powerline AV Ethernet Adapters, an Ethernet cable, an installation guide and a set-up CD, has an MSRP in the U.S. of $149.99. NETGEAR's Powerline AV Ethernet Adapter (XAV101) is compatible with the Powerline AV Ethernet Adapter Kit (XAVB101), and can be purchased as a single unit.</p>

<p>Photos and other product information can be found on the NETGEAR web site at (http://www.netgear.com/Products/PowerlineNetworking/PowerlineEthernetAdapters /XAVB101.aspx).</p>

<p>"With today's ever-advancing technology, the production of innovative, fast, reliable, and easy-to-use networking solutions is key to providing consumers with the ability to connect to a wide-range of Ethernet-enabled applications, from personal computers to digital media adapters and gaming consoles," said Michael Cai, director of digital media and gaming with Parks Associates, which specializes in research and analysis for digital living technologies. "High-speed Powerline devices are able to support this growing bandwidth demand while also transmitting high-quality broadband Internet to the wireless problem areas of the home."</p>

<p>About NETGEAR, Inc.</p>

<p>NETGEAR (NASDAQGM: NTGR) designs innovative, branded technology solutions that address the specific networking, storage, and security needs of small- to medium-sized businesses and home users. The company offers an end-to-end networking product portfolio to enable users to share Internet access, peripherals, files, multimedia content, and applications among multiple computers and other Internet-enabled devices. Products are built on a variety of proven technologies such as wireless, Ethernet and powerline, with a focus on reliability and ease of use. NETGEAR products are sold in over 29,000 retail locations around the globe, and via more than 41,000 value-added resellers. The company's headquarters are in San Jose, Calif., with additional offices in 19 countries. NETGEAR is an ENERGY STAR(R) partner. More information is available by visiting http://www.netgear.com/ or calling (408) 907-8000.</p>

<p>(C)2008 NETGEAR, Inc. NETGEAR and the NETGEAR logo are trademarks or registered trademarks of NETGEAR, Inc. in the United States and/or other countries. Other brand and product names are trademarks or registered trademarks of their respective holders. Information is subject to change without notice. All rights reserved.</p>

<p>Note: Actual data throughput will vary from maximum signal rates stipulated. Network conditions and environmental factors, including volume of network traffic, building materials and construction, and network overhead, lower actual data throughput rate.</p>

<p>Safe Harbor Statement under the Private Securities Litigation Reform Act of 1995 for NETGEAR, Inc.:</p>

<p>This press release contains forward-looking statements within the meaning of the U.S. Private Securities Litigation Reform Act of 1995. Specifically, statements concerning NETGEAR's business and the expected performance characteristics, specifications, market acceptance, market growth, specific uses, user feedback and market position of NETGEAR's products and technology are forward-looking statements within the meaning of the Safe Harbor. These statements are based on management's current expectations and are subject to certain risks and uncertainties, including, without limitation, the following: the actual price, performance and ease of use of NETGEAR's products may not meet the price, performance and ease of use requirements of customers; product performance may be adversely affected by real world operating conditions; new viruses or Internet threats may develop that challenge the effectiveness of security features in NETGEAR's products; the ability of NETGEAR to market and sell its products and technology; the impact and pricing of competing products; and the introduction of alternative technological solutions. Further information on potential risk factors that could affect NETGEAR and its business are detailed in the Company's periodic filings with the Securities and Exchange Commission, including, but not limited to, those risks and uncertainties listed in the section entitled "Part II -- Item 1A. Risk Factors", pages 31 through 44, in the Company's Quarterly Report on Form 10-Q for the fiscal quarter ended September 28, 2008, filed with the Securities and Exchange Commission on November 7, 2008. NETGEAR undertakes no obligation to release publicly any revisions to any forward-looking statements contained herein to reflect events or circumstances after the date hereof or to reflect the occurrence of unanticipated events.</p>

<p>Source: NETGEAR, Inc. </p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>November 17, 2008 08:39 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 1553
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
 				AND entry_id <> 1553
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2008/11/netgears_award-winning_powerline_adapters_for_hd_and_multimedia_streaming_now_available.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
