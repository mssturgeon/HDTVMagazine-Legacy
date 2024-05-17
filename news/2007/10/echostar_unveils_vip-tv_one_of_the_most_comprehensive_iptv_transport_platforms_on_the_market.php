<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 761";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 761 AND placement_is_primary = 1";
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
	<meta name="keywords" content="echostar communications, industry leading, echostar fss, fss corporation, communications corporation, echostar, vip, EchoStar, ViP, programming, channels, service, satellite, transport, corporation, efssc, EFSSC, Corporation, dish, offers, DISH, most, high, network, video" />
	<meta name="description" content="EchoStar Communications Corporation (Nasdaq:DISH) and its subsidiary, EchoStar FSS Corporation (EFSSC), today announced the launch of ViP-TV(tm) service, through which EFFSC has the ability to transport over 300 channels of secure broadcast quality popular television programming via satellite to Telco, private and rural cable operators, municipalities and master planned community video providers that have obtained rights for distribution of programming over their wire-line networks. ViP-TV is EFSSC's turn-key solution for wholesale multi-channel content transport and distribution, and offers customers affordable, scalable and aggregated MPEG-4 Internet protocol encapsulated radio and television programming channels from a high-powered Ku-band satellite.

ViP-TV's suite of channels includes ViP-Premier(tm), which offers over 100 channels of the most popular television programming, ViP-HD(tm), which boasts 40 channels of industry-leading high definition programming..." />
	<title>HDTV Magazine Bulletins - EchoStar Unveils ViP-TV, One of the Most Comprehensive IPTV Transport Platforms On the Market</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/echostar_unveils_vip-tv_one_of_the_most_comprehensive_iptv_transport_platforms_on_the_market';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('EchoStar Unveils ViP-TV, One of the Most Comprehensive IPTV Transport Platforms On the Market'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2007/10/echostar_unveils_vip-tv_one_of_the_most_comprehensive_iptv_transport_platforms_on_the_market.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">EchoStar Unveils ViP-TV, One of the Most Comprehensive IPTV Transport Platforms On the Market</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>October 23, 2007</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Programming">Programming</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2007/10/echostar_unveils_vip-tv_one_of_the_most_comprehensive_iptv_transport_platforms_on_the_market.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2007/10/echostar_unveils_vip-tv_one_of_the_most_comprehensive_iptv_transport_platforms_on_the_market.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2007/10/echostar_unveils_vip-tv_one_of_the_most_comprehensive_iptv_transport_platforms_on_the_market.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2007/10/echostar_unveils_vip-tv_one_of_the_most_comprehensive_iptv_transport_platforms_on_the_market.php&amp;phase=2&amp;title=EchoStar%20Unveils%20ViP-TV%2C%20One%20of%20the%20Most%20Comprehensive%20IPTV%20Transport%20Platforms%20On%20the%20Market&amp;bodytext=EchoStar%20Communications%20Corporation%20%28Nasdaq%3ADISH%29%20and%20its%20subsidiary%2C%20EchoStar%20FSS%20Corporation%20%28EFSSC%29%2C%20today%20announced%20the%20launch%20of%20ViP-TV%28tm%29%20service%2C%20through%20which%20EFFSC%20has%20the%20ability%20to%20transport%20over%20300%20channels%20of%20secure%20broadcast%20quality%20popular%20television%20programming%20via%20satellite%20to%20Telco%2C%20private%20and%20rural%20cable%20operators%2C%20municipalities%20and%20master%20planned%20community%20video%20providers%20that%20have%20obtained%20rights%20for%20distribution%20of%20programming%20over%20their%20wire-line%20networks.%20ViP-TV%20is%20EFSSC%27s%20turn-key%20solution%20for%20wholesale%20multi-channel%20content%20transport%20and%20distribution%2C%20and%20offers%20customers%20affordable%2C%20scalable%20and%20aggregated%20MPEG-4%20Internet%20protocol%20encapsulated%20radio%20and%20television%20programming%20channels%20from%20a%20high-powered%20Ku-band%20satellite.%0A%0AViP-TV%27s%20suite%20of%20channels%20includes%20ViP-Premier%28tm%29%2C%20which%20offers%20over%20100%20channels%20of%20the%20most%20popular%20television%20programming%2C%20ViP-HD%28tm%29%2C%20which%20boasts%2040%20channels%20of%20industry-leading%20high%20definition%20programming...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p class="prtitle">EchoStar Unveils ViP-TV, One of the Most Comprehensive IPTV Transport Platforms On the Market</p>

<center><i>Offers Distributors the Ability to Deliver Up to 300 Programming Channels, Including 40 Channels of HD and Local Broadcast Networks</i></center><br />
<br />

<p><B>ENGLEWOOD, Colo., Oct 23, 2007 (PrimeNewswire via COMTEX News Network)</B> -- EchoStar Communications Corporation (Nasdaq:DISH) and its subsidiary, EchoStar FSS Corporation (EFSSC), today announced the launch of ViP-TV(tm) service, through which EFFSC has the ability to transport over 300 channels of secure broadcast quality popular television programming via satellite to Telco, private and rural cable operators, municipalities and master planned community video providers that have obtained rights for distribution of programming over their wire-line networks. ViP-TV is EFSSC's turn-key solution for wholesale multi-channel content transport and distribution, and offers customers affordable, scalable and aggregated MPEG-4 Internet protocol encapsulated radio and television programming channels from a high-powered Ku-band satellite.</p>

<p>ViP-TV's suite of channels includes ViP-Premier(tm), which offers over 100 channels of the most popular television programming, ViP-HD(tm), which boasts 40 channels of industry-leading high definition programming; ViP-Movies(tm), a menu of 40 of the most popular movie services; ViP-Latino(tm), offering 30 of the top-rated Spanish-language programming services; and the ViP-International(tm) programming package, providing over 30 programming channels in 10 different languages.</p>

<p>The ViP-TV service is also offered in connection with transport of local broadcast networks from more than 165 local designated market areas (DMAs) currently provided by EchoStar to over 1,000 cable and Telco systems in standard definition. VIP-TV also offers transport of high definition local broadcast networks in over 30 local DMAs.</p>

<p>EFSSC's ViP-TV delivers its service using a high-performance, high-powered Ku Band satellite with full continental United Sates coverage, so that the service can be rapidly deployed and is low cost to maintain. In addition, EFSSC offers full-service design, engineering and installation of head-end equipment together with its award-winning ViP(tm) series set top box technology and applications.</p>

<p>"ViP-TV is one of the most comprehensive video iP transport and distribution platforms available in the marketplace," said Michael Kelly, executive vice president of EchoStar Fixed Satellite Services. "More than just programming, it is a premier service buoyed by industry-leading technology, service and support. With over 12 years of experience in the transport and distribution business, EchoStar can create a true end-to-end solution offering everything from set top box technology to satellite distribution to service and customer support."</p>

<p>For more information about EchoStar, visit www.echostar.com. For more information on ViP-TV, visit www.echostarviptv.com</p>

<p><br />
<B>About EchoStar Communications Corporation</B></p>

<p>EchoStar Communications Corporation (Nasdaq:DISH) has been a leader for more than 27 years in satellite TV equipment sales and support worldwide. The Company's DISH Network(r) is the fastest-growing pay-TV provider in the country since 2000, providing more than 13.585 million satellite TV customers with industry-leading customer satisfaction which has surpassed major cable companies for seven years running. DISH Network customers also enjoy access to a premier line of award-winning Digital Video Recorders (DVRs), hundreds of video and audio channels, the most International channels in the U.S., industry-leading Interactive TV applications, Latino programming, and the best sports and movies in HD. DISH Network offers a variety of package and price options including the lowest all-digital price in America, the DishDVR Advantage Package, high-speed Internet service, a free upgrade to the best HD DVR in the industry, and six months free of DishHD. EchoStar is included in the Nasdaq-100 Index (NDX) and is a Fortune 300 company. Visit www.echostar.com or call 1-800-333-DISH (3474) for more information.</p>

<p><br />
<B>About EchoStar FSS Corporation</B></p>

<p>EchoStar FSS Corporation (EFSSC), a subsidiary of EchoStar Communication Corporation, provides commercial satellite capabilities, quality service and competitive pricing for broadcast services, business television and engineering operations. EchoStar, through EFSSC and its other subsidiaries, owns or leases 15 in-orbit satellites operating in FSS Ku-band, BSS and Ka-bands; has access to an extensive terrestrial fiber optic network with points of presence in over 168 cities; and owns seven regional gateways, allowing EFSSC to provide customers with end-to-end connectivity; and a reliable platform to distribute video and data throughout the U.S.</p>

<p>SOURCE: EchoStar</p>

<p>EchoStar Communications Corp.<br />
Media Contact:<br />
Kathie Gonzalez<br />
(720) 514-5351<br />
press@echostar.com</p>

<p>ViP-TV Customer Contact:<br />
Bill Everett<br />
(303) 723-2215<br />
william.everett@echostar.com</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>October 23, 2007 06:31 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 761
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
 			<h2>More on Programming</h2><ul><?
 			$sql = "
 			SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name
 			FROM mt_entry e, mt_author a, mt_placement p, mt_category c
 			WHERE entry_author_id = author_id
 				AND e.entry_id = p.placement_entry_id
 				AND p.placement_category_id = c.category_id
 				AND category_label = 'Programming'
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
 				AND entry_id <> 761
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2007/10/echostar_unveils_vip-tv_one_of_the_most_comprehensive_iptv_transport_platforms_on_the_market.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
