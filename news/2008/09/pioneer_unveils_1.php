<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1535";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1535 AND placement_is_primary = 1";
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
	<meta name="keywords" content="blu ray, ray disc, disc player, flagship blu, pioneer elite, pioneer, Pioneer, disc, blu, ray, Blu, Disc, player, audio, video, bdp, BDP, registered, live, players, Live, flagship, elite, performance, quality" />
	<meta name="description" content="Pioneer Electronics (USA) Inc. today introduces its Elite&amp;reg; BDP-09FD flagship Blu-ray Disc&amp;trade; player featuring BD-Live&amp;trade;. With the industry's most advanced internal components including a Pioneer&amp;reg;/Renesas&amp;reg; jointly developed core processor chipset and complex internal and external composition, the new flagship model illuminates Pioneer's uncompromised design and engineering philosophies to deliver the truest picture and sound performance.

Breaking away from the industry, Pioneer's flagship Blu-ray Disc player incorporates..." />
	<title>HDTV Magazine Bulletins - Pioneer Unveils Flagship Blu-ray Disc Player with Reference Class Picture and Sound Performance</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/pioneer_unveils_flagship_blu-ray_disc_player_with_reference_class_picture_and_sound_performance';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Pioneer Unveils Flagship Blu-ray Disc Player with Reference Class Picture and Sound Performance'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2008/09/pioneer_unveils_flagship_blu-ray_disc_player_with_reference_class_picture_and_sound_performance.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Pioneer Unveils Flagship Blu-ray Disc Player with Reference Class Picture and Sound Performance</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>September  4, 2008</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=HD DVD & Blu-ray">HD DVD & Blu-ray</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2008/09/pioneer_unveils_flagship_blu-ray_disc_player_with_reference_class_picture_and_sound_performance.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2008/09/pioneer_unveils_flagship_blu-ray_disc_player_with_reference_class_picture_and_sound_performance.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2008/09/pioneer_unveils_flagship_blu-ray_disc_player_with_reference_class_picture_and_sound_performance.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2008/09/pioneer_unveils_flagship_blu-ray_disc_player_with_reference_class_picture_and_sound_performance.php&amp;phase=2&amp;title=Pioneer%20Unveils%20Flagship%20Blu-ray%20Disc%20Player%20with%20Reference%20Class%20Picture%20and%20Sound%20Performance&amp;bodytext=Pioneer%20Electronics%20%28USA%29%20Inc.%20today%20introduces%20its%20Elite%26reg%3B%20BDP-09FD%20flagship%20Blu-ray%20Disc%26trade%3B%20player%20featuring%20BD-Live%26trade%3B.%20With%20the%20industry%27s%20most%20advanced%20internal%20components%20including%20a%20Pioneer%26reg%3B%2FRenesas%26reg%3B%20jointly%20developed%20core%20processor%20chipset%20and%20complex%20internal%20and%20external%20composition%2C%20the%20new%20flagship%20model%20illuminates%20Pioneer%27s%20uncompromised%20design%20and%20engineering%20philosophies%20to%20deliver%20the%20truest%20picture%20and%20sound%20performance.%0A%0ABreaking%20away%20from%20the%20industry%2C%20Pioneer%27s%20flagship%20Blu-ray%20Disc%20player%20incorporates...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p class="prtitle">Pioneer Unveils Flagship Blu-ray Disc Player with Reference Class Picture and Sound Performance</p>

<center><i>Offering BD-Live Functionality, Flagship Player Leads Pioneer's Blu-ray Line</i></center><br />
<br />

<p><br />
<B>DENVER--(BUSINESS WIRE)</B>--Pioneer Electronics (USA) Inc. today introduces its Elite&reg; BDP-09FD flagship Blu-ray Disc&trade; player featuring BD-Live&trade;. With the industry's most advanced internal components including a Pioneer&reg;/Renesas&reg; jointly developed core processor chipset and complex internal and external composition, the new flagship model illuminates Pioneer's uncompromised design and engineering philosophies to deliver the truest picture and sound performance.</p>

<p>"For more than 30 years, we've led optical disc innovation with product introductions that featured numerous industry firsts, ranging from the highest grade internal components to improved build constructions, all in the pursuit of developing first class products for consumers. It's with this mindset that our engineers were given the freedom to hand select every piece of hardware and wiring for the BDP-09FD to develop a true, reference-quality player," said Chris Walker, senior manager of marketing and product planning for Blu-ray Disc at Pioneer Electronics (USA) Inc. "The result is a flagship BD player that provides unparalleled picture and sound performance not just for Blu-ray Discs, but also for CD and DVD content."</p>

<p><br />
<B>Video Standards Raise the Bar</B></p>

<p>Breaking away from the industry, Pioneer's flagship Blu-ray Disc player incorporates a newly-engineered 16-bit video engine to provide best-in-class video decoding, I/P conversion, video scaling and video adjustments of HD and SD content for uncompromised viewing of both Blu-ray Disc and DVD collections. Working in concert, this unique video engine includes a newly developed 16-bit Pioneer video processor, the Marvell&reg; award-winning QDEO&trade; processor along with a jointly developed video decoder by Renesas Technology. Also, for the first time from Pioneer, DVDs can be enjoyed in 1080p 24 when connected to compatible displays providing a more cinematic experience of traditional film based DVDs. The inclusion of these sophisticated technologies creates a smooth picture free of the artificial noise that often plagues lower-quality players.</p>

<p>The Elite Blu-ray Disc player also features Pioneer's proprietary Adaptive Bit Length Expansion technology, which allows the purest color representation of a movie's original studio master. Through a dedicated chip, the player restores Blu-ray Disc movies, produced in 8-bit color back to the vivid 16-bit deep color gamut resulting in an HD picture filled with hues and tones that replicates the cinematic intentions of the films' creators.</p>

<p><br />
<B>Quality Audio Roots</B></p>

<p>Pioneer's BDP-09FD uses professional-level audio technologies to improve sound quality for both Blu-ray Disc and CD content. The BDP-09FD supports full decoding and bit-stream output for emerging high-resolution audio formats including Dolby&reg; TrueHD and DTS-HD Master Audio to deliver rich audio that completely immerses the listening audience.</p>

<p>Audiophiles will delight in the sophisticated internal engineering featured in the new player, including a devoted power supply for the analog audio board, eight Wolfson&reg; WM8740 Audio DACs, one for each audio channel, and custom-made capacitors. Unlike any player preceding it, the new BDP-09FD further enhances superior performance capabilities not possible with today's current Blu-ray Disc players.</p>

<p>The player also features Precision Quartz Lock System (PQLS) with which the player instinctively synchronizes audio data between the Blu-ray Disc player and select Pioneer Elite A/V receivers offering jitter-free audio playback from CD content to provide listeners with the ultimate precision in product performance.</p>

<p><br />
<B>BD-Live Compatibility</B></p>

<p>The BDP-09FD is outfitted with BD-Live interactivity that goes beyond the advanced audio and video playback capabilities offered with BONUSVIEW&trade; players to give consumers an array of interactive options when viewing BD-Live compatible titles. With an Internet connection, viewers can download bonus trailers and features, synchronize viewing with other BD-Live players and participate in online gaming, live chat and video messaging. Additionally, when updates become available, users will be able to upgrade their player via the BD-Live connection.</p>

<p><br />
<B>Built for Discerning Entertainment Connoisseurs</B></p>

<p>Exceeding the capabilities of current players in the market, the BDP-09FD raises the bar with a completely redesigned internal construction for ultimate efficiency and performance quality. The BDP-09FD is outfitted with:</p>

<p>    * Center loading disc tray, solid steel bottom plate, aluminum chassis, and rugged TAOC insulators which help dampen external vibrations and lend to uncompromised picture quality<br />
    * Dual HDMI&trade; 1.3a outputs allow for a variety of connection options that can be configured for specific audio or video entertainment needs<br />
    * Gold plated connectors provide maximum signal flow for stellar audio and video quality<br />
    * RS-232C port allows for control from third-party automation systems or custom installers</p>

<p><br />
<B>Product Synergy</B></p>

<p>For the complete home theater package, Pioneer has purposefully created a synergy between its line of Blu-ray Disc players, A/V receivers and KURO plasmas. The newly named project, KURO Link System, syncs the Blu-ray Disc player with the connected KURO plasma to allow for unified remote operation between products.</p>

<p>Pioneer's BPP-09FD joins Pioneer's BONUSVIEW Blu-ray Disc players, the Elite BDP-05FD and Pioneer BDP-51FD. The Pioneer Elite BDP-09FD flagship Blu-ray Disc player will begin shipping in 2008 for a suggested price of $2,199.</p>

<p>Pioneer's Home Entertainment and Business Solutions Group develops high definition home theater equipment for discerning entertainment junkies. Its flat panel televisions, Blu-ray Disc players, A/V receivers and speakers bring a new level of emotion to the HD experience. The company brands include Pioneer and Elite&reg;. When purchased from an authorized retailer, consumers receive a limited warranty for one year with Pioneer products and two years with Pioneer Elite products. More details can be located at www.pioneerelectronics.com.</p>

<p>PIONEER, the PIONEER logo, the ELITE logo and PureVision are registered trademarks of the Pioneer Corporation.</p>

<p>Renesas&reg; is a registered trademark of Renesas Technology Corp.</p>

<p>Marvell&reg; is a registered trademark of Marvell or its affiliates</p>

<p>QDEO&trade; is a trademark of Marvell or its affiliates</p>

<p>DOLBY and the double-D symbol are registered trademarks of Dolby Laboratories.</p>

<p>DTS and DTS Digital Surround are registered trademarks of Digital Theater Systems, Inc.</p>

<p>WOLFSON" is registered trade mark of Wolfson Microelectronics plc ("Wolfson")</p>

<p>BLU-RAY DISC is a registered trademark of Sony Corporation.</p>

<p>BD Live&reg; is a registered trademark of the Blu-ray Disc Association</p>

<p>HDMI, the HDMI logo and High-Definition Multimedia Interface are trademarks or registered trademarks of HDMI Licensing LLC. </p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>September  4, 2008 02:07 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 1535
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
 			<h2>More on HD DVD & Blu-ray</h2><ul><?
 			$sql = "
 			SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name
 			FROM mt_entry e, mt_author a, mt_placement p, mt_category c
 			WHERE entry_author_id = author_id
 				AND e.entry_id = p.placement_entry_id
 				AND p.placement_category_id = c.category_id
 				AND category_label = 'HD DVD & Blu-ray'
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
 				AND entry_id <> 1535
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2008/09/pioneer_unveils_flagship_blu-ray_disc_player_with_reference_class_picture_and_sound_performance.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
