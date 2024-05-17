<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 599";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 599 AND placement_is_primary = 1";
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
	<meta name="keywords" content="duo mediabridge, link systems, systems inc, link xtreme, content home, link, Link, wireless, Xtreme, xtreme, home, MediaBridge, mediabridge, Duo, duo, draft, performance, technology, systems, DAP, dap, router, Inc, GHz, inc" />
	<meta name="description" content="D-Link(R), the end-to-end networking solutions provider for consumers and business, today introduced the new D-Link(R) Xtreme N(TM) Duo(TM) MediaBridge(TM) which allows users to add 5GHz Draft 802.11n capabilities to an existing router. Wireless 802.11n operating at 5GHz makes it possible to stream high-definition (HD) multimedia content throughout the home, from PCs to home entertainment centers, with minimal interference and congestion from legacy (2.4GHz) networks. The new D-Link Xtreme N Duo MediaBridge (DAP-1555) helps avoid interference by allowing the user to use the 5GHz frequency band to provide a stable high-performance wireless link for streaming HD video and more." />
	<title>HDTV Magazine Bulletins - D-Link(R) Introduces Wireless Draft 11N Solution That Moves High-Definition Content Throughout the Home Using Any Router</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/d-linkr_introduces_wireless_draft_11n_solution_that_moves_high-definition_content_throughout_the_home_using_any_router';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('D-Link(R) Introduces Wireless Draft 11N Solution That Moves High-Definition Content Throughout the Home Using Any Router'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2007/05/d-linkr_introduces_wireless_draft_11n_solution_that_moves_high-definition_content_throughout_the_home_using_any_router.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">D-Link(R) Introduces Wireless Draft 11N Solution That Moves High-Definition Content Throughout the Home Using Any Router</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>May 15, 2007</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Products & Equipment">Products & Equipment</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2007/05/d-linkr_introduces_wireless_draft_11n_solution_that_moves_high-definition_content_throughout_the_home_using_any_router.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2007/05/d-linkr_introduces_wireless_draft_11n_solution_that_moves_high-definition_content_throughout_the_home_using_any_router.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2007/05/d-linkr_introduces_wireless_draft_11n_solution_that_moves_high-definition_content_throughout_the_home_using_any_router.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2007/05/d-linkr_introduces_wireless_draft_11n_solution_that_moves_high-definition_content_throughout_the_home_using_any_router.php&amp;phase=2&amp;title=D-Link%28R%29%20Introduces%20Wireless%20Draft%2011N%20Solution%20That%20Moves%20High-Definition%20Content%20Throughout%20the%20Home%20Using%20Any%20Router&amp;bodytext=D-Link%28R%29%2C%20the%20end-to-end%20networking%20solutions%20provider%20for%20consumers%20and%20business%2C%20today%20introduced%20the%20new%20D-Link%28R%29%20Xtreme%20N%28TM%29%20Duo%28TM%29%20MediaBridge%28TM%29%20which%20allows%20users%20to%20add%205GHz%20Draft%20802.11n%20capabilities%20to%20an%20existing%20router.%20Wireless%20802.11n%20operating%20at%205GHz%20makes%20it%20possible%20to%20stream%20high-definition%20%28HD%29%20multimedia%20content%20throughout%20the%20home%2C%20from%20PCs%20to%20home%20entertainment%20centers%2C%20with%20minimal%20interference%20and%20congestion%20from%20legacy%20%282.4GHz%29%20networks.%20The%20new%20D-Link%20Xtreme%20N%20Duo%20MediaBridge%20%28DAP-1555%29%20helps%20avoid%20interference%20by%20allowing%20the%20user%20to%20use%20the%205GHz%20frequency%20band%20to%20provide%20a%20stable%20high-performance%20wireless%20link%20for%20streaming%20HD%20video%20and%20more.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p class="prtitle">D-Link(R) Introduces Wireless Draft 11N Solution That Moves High-Definition Content Throughout the Home Using Any Router</p>

<p><B>Microsoft(R) Chairman Bill Gates Demonstrates Streaming of HD Content over Wi-Fi(R) During WinHEC Keynote using D-Link(R) Xtreme N(TM) Duo MediaBridge(TM)</B><br /><br />
<br /><br />
<B>LOS ANGELES, May 15 /PRNewswire/ -- WinHEC --</B> D-Link(R), the end-to-end networking solutions provider for consumers and business, today introduced the new D-Link(R) Xtreme N(TM) Duo(TM) MediaBridge(TM) which allows users to add 5GHz Draft 802.11n capabilities to an existing router. Wireless 802.11n operating at 5GHz makes it possible to stream high-definition (HD) multimedia content throughout the home, from PCs to home entertainment centers, with minimal interference and congestion from legacy (2.4GHz) networks. The new D-Link Xtreme N Duo MediaBridge (DAP-1555) helps avoid interference by allowing the user to use the 5GHz frequency band to provide a stable high-performance wireless link for streaming HD video and more.</p>

<p>(Logo: http://www.newscom.com/cgi-bin/prnh/20010327/DLINKLOGO )</p>

<p>The D-Link Xtreme N Duo MediaBridge will be on stage today at the Microsoft(R) WinHEC event along with the D-Link Xtreme NTM Gigabit Router (DIR-655) during Bill Gates' keynote in which he will demonstrate the ability to move multiple HD streams from PCs running Windows Vista to Media Center Extender devices.</p>

<p>To provide Draft 802.11n wireless performance to an existing router, the DAP-1555 attaches to an Ethernet port and then sends the dual-band draft 11n signal throughout the home. Users can utilize two MediaBridge adapters for optimal performance by connecting one to the router and the second to an Ethernet capable device in the home entertainment center. The D-Link DAP-1555 can connect up to five devices -- including game consoles, Digital Video Recorders (DVR) and Digital Media Adapters (DMA) -- all at the same time.</p>

<p>"When it comes to delivering superior wireless performance, clarity and coverage, the Xtreme N Duo MediaBridge is the ideal centerpiece for the home wireless network," said Brian Larsen, associate vice president of product development for D-Link Systems, Inc. "It's easy to install, add or upgrade to any home network and provides the best wireless technology available for viewing HD video."</p>

<p>"Windows Vista(TM) is designed to provide PC users a superior multimedia experience, as well as the ability to enjoy that experience elsewhere in their homes," said Glenn Ward, group partner manager for Windows Rally at Microsoft Corp. "We are extremely pleased that our collaboration with D-Link has resulted in a product that helps consumers make the Windows Vista vision a reality."</p>

<p>The DAP-1555 includes D-Link's MediaBand(TM) technology, which is the best technology available to help provide a stable high-performance wireless link for streaming multiple HD videos. By allowing users to operate in a clear wireless band for multimedia streams, it avoids interference that may slow down and limit the range of current wireless technologies such as 802.11b/g and draft 802.11n.</p>

<p>The Xtreme N Duo MediaBridge is the latest addition to the award-winning Xtreme N product family. The DAP-1555 works with next generation dual-band (2.4GHz and 5GHz) 802.11n wireless devices as well as legacy 802.11a/b/g products. It is designed for users looking to get a faster wireless connection capable of handling HD video streams while providing the maximum home coverage they need.</p>

<p><B> Key Features and Benefits:</B></p>

<p> -- IEEE 802.11n (draft 2.0), 802.11g, 802.11b, and 802.11a-compliant</p>

<p> -- Xtreme N Duo technology for superior wireless performance</p>

<p> -- MediaBand(TM) technology for an enhanced media experience</p>

<p> -- Includes WPS (Wi-Fi Protected Setup(TM)) for simple push-button<br />
 wireless network configuration</p>

<p> -- Supports secure wireless encryption using WEP, WPA(TM) or WPA2(TM)</p>

<p> -- 5-Port 10/100 switch</p>

<p> -- UPnP(TM) support</p>

<p> -- 24/7 advanced technical support and 1-Year limited warranty</p>

<p><B> Price and Availability:</B></p>

<p>The D-Link Xtreme N Duo MediaBridge with MediaBand technology (DAP-1555) is expected to ship in early Q3 to the company's network of retail outlets, value-added resellers and distributors. Pricing will be announced when the product ships.</p>

<p><B>About D-Link</B></p>

<p>D-Link is the global leader in connectivity for small, medium and large enterprise business networking. The company is an award-winning designer, developer and manufacturer of networking, broadband, digital electronics, voice, data and video communications solutions for the digital home, Small Office/Home Office (SOHO), Small to Medium Business (SMB), and Workgroup to Enterprise environments. With millions of networking and connectivity products manufactured and shipped, D-Link is a dominant market participant and price/performance leader in the networking and communications market. D-Link Systems, Inc. headquarters are located at 17595 Mt. Herrmann Street, Fountain Valley, Calif., 92708. Phone (800) 326-1688 or (714) 885-6000; FAX (866) 743-4905; Internet http://www.dlink.com/.</p>

<p>D-Link, Xtreme N, Duo, MediaBridge, MediaBand and the D-Link logo are trademarks or registered trademarks of D-Link Corporation or its subsidiaries in the United States and other countries. All other third party marks mentioned herein may be trademarks of their respective owners.</p>

<p>Copyright (C) 2007 D-Link Corporation/D-Link Systems, Inc. All Rights Reserved.<br />
Photo: NewsCom: http://www.newscom.com/cgi-bin/prnh/20010327/DLINKLOGO<br />
AP Archive: http://photoarchive.ap.org/<br />
PRN Photo Desk, photodesk@prnewswire.com</p>

<p><B>Source:</B> D-Link Systems, Inc.</p>

<p><B>CONTACT:</B> Raleigh Joffe, PR Contact, of TBPR, +1-818-781-8839,<br />
rjoffe@mindspring.com, for D-Link Systems, Inc.; or Michael Scott, Technical<br />
Media Mgr. of D-Link Systems, Inc., 800-326-1688, ext. 6243, mscott@dlink.com</p>

<p>Web site: http://www.dlink.com/</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>May 15, 2007 06:59 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 599
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
 				AND entry_id <> 599
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2007/05/d-linkr_introduces_wireless_draft_11n_solution_that_moves_high-definition_content_throughout_the_home_using_any_router.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
