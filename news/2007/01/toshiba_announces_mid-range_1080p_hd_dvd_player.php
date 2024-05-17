<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 507";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 507 AND placement_is_primary = 1";
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
	<meta name="keywords" content="dvd players, high definition, toshiba america, second generation, may require, DVD, dvd, toshiba, Toshiba, high, players, may, definition, hdmi, new, HDMI, products, line, Dolby, content, dolby, dts, features, support, DTS" />
	<meta name="description" content="At CES, Toshiba is introducing the new HD-A20, which is expected to retail at $599.99. With the 1080p capabilities of the HD-A20, Toshiba has taken high definition to the next level at an attractive price point. The HD-A20 joins the existing Toshiba HD DVD line-up which includes the entry level HD-A2 and the top of the line model, HD-XA2. This expanded line of products offers..." />
	<title>HDTV Magazine Bulletins - Toshiba Announces Mid-range 1080p HD DVD Player</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/toshiba_announces_mid-range_1080p_hd_dvd_player';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Toshiba Announces Mid-range 1080p HD DVD Player'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2007/01/toshiba_announces_mid-range_1080p_hd_dvd_player.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Toshiba Announces Mid-range 1080p HD DVD Player</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>January  7, 2007</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=HD DVD & Blu-ray">HD DVD & Blu-ray</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2007/01/toshiba_announces_mid-range_1080p_hd_dvd_player.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2007/01/toshiba_announces_mid-range_1080p_hd_dvd_player.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2007/01/toshiba_announces_mid-range_1080p_hd_dvd_player.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2007/01/toshiba_announces_mid-range_1080p_hd_dvd_player.php&amp;phase=2&amp;title=Toshiba%20Announces%20Mid-range%201080p%20HD%20DVD%20Player&amp;bodytext=At%20CES%2C%20Toshiba%20is%20introducing%20the%20new%20HD-A20%2C%20which%20is%20expected%20to%20retail%20at%20%24599.99.%20With%20the%201080p%20capabilities%20of%20the%20HD-A20%2C%20Toshiba%20has%20taken%20high%20definition%20to%20the%20next%20level%20at%20an%20attractive%20price%20point.%20The%20HD-A20%20joins%20the%20existing%20Toshiba%20HD%20DVD%20line-up%20which%20includes%20the%20entry%20level%20HD-A2%20and%20the%20top%20of%20the%20line%20model%2C%20HD-XA2.%20This%20expanded%20line%20of%20products%20offers...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p class="prtitle">Toshiba Delivers -- Successful Launch of Second Generation HD DVD Players Completes the Seamless Transition to High Definition</p>

<center><i>Increased Shipment Volume to Meet Market Demand; New Player Introduction Enhances Line-up of HD DVD Players</i></center>

<p><b>CES, LAS VEGAS, Jan. 7 /PRNewswire/</b> -- Proving its leadership in the seamless transition to high definition, Toshiba today announced it has successfully introduced its second generation HD DVD players - delivering stunning image quality, astounding audio capabilities and a new level of interactivity never seen before. Building on its early lead in the market and proving the strength of the HD DVD format, Toshiba has now added a new player to enhance its family of HD DVD players and has increased shipment volume to meet the growing market demand.</p>

<p>"It's with great satisfaction that we announce the successful launch of Toshiba's second generation HD DVD players," said Jodi Sally, Vice President of Marketing, Toshiba America Consumer Products Digital A/V Group. "Our second generation HD-A2 is selling through very well indicating a stronger market demand than before, and now with a new line-up of second generation players, we're proving that Toshiba is the consumer's choice for next generation HD DVD."</p>

<p><br />
<h2>Bigger Commitment, Bigger Line-Up</h2><br />
At CES, Toshiba is introducing the new HD-A20, which is expected to retail at $599.99. With the 1080p capabilities of the HD-A20, Toshiba has taken high definition to the next level at an attractive price point. The HD-A20 joins the existing Toshiba HD DVD line-up which includes the entry level HD-A2 and the top of the line model, HD-XA2. This expanded line of products offers enhanced functionalities of the HD DVD format and is proof of Toshiba's commitment to the smooth transition to the next stage in high definition entertainment.</p>

<p>"According to NPD Group data, the sales of HDTVs grew 52 percent between January and September of 2006. With the continued growth over this most recent holiday selling period and throughout 2007, we anticipate the demand for HD DVD to complement the demand and adoption of HDTVs," continued Sally. "There is no other high definition format in the market that can meet this demand with the same breadth of line and availability of players as Toshiba."</p>

<p><br />
<h2>HD DVD - The Players</h2><br />
To meet the latest advancements in Audio/Video interfaces, Toshiba's models connect to HDTV sets via High Definition Multimedia Interface (HDMI(TM)) - the multi- industry-supported, all digital A/V connection capable of providing the transmission of uncompressed digital video and multi-channel audio on a single cable.</p>

<p>To match the resolution of your display, Toshiba's HD DVD players output HD DVD content through the HDMI interface in 720p or 1080i for the HD-A2, and 720p, 1080i or 1080p for the HD-A20 and the HD-XA2. Through the HDMI interface, standard definition DVDs can also be upconverted to match the resolution of HD displays. The HD-A2, HD-A20, and HD-XA2 are all backward compatible, so users can continue to enjoy their libraries of current DVD and CD software.</p>

<p>Both the HD-A20 and the high end HD-XA2 HD DVD player are designed to output 1920 x 1080p, the highest HD signal currently available, via HDMI. As the premium HD DVD player, the HD-XA2 also incorporates support for Deep Color output through HDMI, and a 297MHz / 12 bit Video DAC with high-quality, 4x oversampling for increased bandwidth for true playback of an HD picture to a video source. Additionally, it comes with a picture setting function allowing customers to optimize picture quality with user adjustable settings for color, contrast, brightness, edge enhancement and block noise, among others.</p>

<p>All of Toshiba's HD DVD players support a variety of HD audio options to complement its HD video offerings. This includes both lossy and lossless formats from Dolby Labs and DTS(R) including the Dolby(R) Digital Plus and Dolby TrueHD.</p>

<p>With black high gloss finishes and slim chassis designs, the new HD DVD player line has a refined, sleek appearance that complements Toshiba's extensive TV line-up.</p>

<h2>Expected Pricing and Expected Availability:</h2>
HD-A2 ($499.99, Available Now)<br />
HD-A20 ($599.99, Spring 2007)<br />
HD-XA2 ($999.99, Available Now)<br />

<p><br />
<h2>HD DVD - The format for a smooth transition</h2><br />
HD DVD is a seamless transition from DVD enhancing the home theater experience, particularly with the combination discs that contain both HD DVD and DVD content on the same disc. Taking advantage of the combination discs, consumers can enjoy the HD DVD quality with their HDTV and have the flexibility to view the standard definition version on existing DVD players and even portables. Additionally all Toshiba HD DVD players offer network connectivity and interactivity to reach a new high definition experience. Only HD DVD made it a mandatory part of the standard to support networking, persistent storage and secondary video decoders. Networking and persistent storage have the capability to let consumers download and store new content on their HD DVD players. With combination discs, network connectivity and additional storage, HD DVD is easing the transition from SD to HD providing consumers with the ultimate home entertainment experience.</p>

<p><br />
<h2>About Toshiba America Consumer Products, L.L.C.</h2><br />
Toshiba America Consumer Products, L.L.C. is owned by Toshiba America, Inc., a subsidiary of Toshiba Corporation, a world leader in high technology products with subsidiaries worldwide. Toshiba is a pioneer in HD DVD, DVD and DVD Recorder technology and a leading manufacturer of a full line of home entertainment products, including flat panel TV, rear projection and direct view televisions, combination products and portable devices. Toshiba America Consumer Products, L.L.C. is headquartered in Wayne, New Jersey. For additional information please visit http://www.tacp.toshiba.com.</p>

<p>Toshiba respects the rights of others. Our products are only intended for lawful recording, storage and playback of authorized content and any other lawful use.</p>

<p>Design specifications and dimensions are not final and are subject to change. Please confirm specific features and exact dimensions by reference to the product itself.</p>

<p><br />
<h2>IMPORTANT NOTES</h2><br />
HD DVD with high-definition content required for HD output. Viewing high- definition content and up-converting DVD content may require an HDCP capable DVI or HDMI input on your display device. Firmware update may be required for some interactive features depending on content, which may also require an always-on broadband internet connection. Some features may require additional bandwidth.</p>

<p>Some recordable media may not be supported. Dolby Digital Plus, Dolby TrueHD and DTS support for up to 5.1 channels (DTS HD support for DTS core only). MP3/WMA audio files not supported. Some current DVDs and CDs may not be compatible. HDMI audio support for PCM only. Because HD DVD is a new format that makes use of new technologies, certain disc, digital connection and other compatibility and/or performance issues are possible. This may, in rare cases, include disc freezing while accessing certain disc features or functions, or certain parts of the disc not playing back or operating as fully intended. If you experience such issues, please refer to the FAQ sections of http://www.toshibahddvd.com or http://www.tacp.toshiba.com for information on possible work- around solutions or the availability of firmware updates that may resolve your problem, or contact Toshiba Customer Solutions. Some features subject to delayed availability. 1080p capable display required for 1080p output resolution. Deep Color feature as specified in HDMI 1.3a requires compatible Deep Color capable HD display and/or device. Some devices may not be compatible.</p>

<p>Dolby Digital Plus and Dolby True HD support for up to 5.1 channels. DTS- HD support for up to 5.1 channels of DTS(R) core only. Firmware update may be required for some interactive features depending on content, which may also require an always-on broadband internet connection. Some features may require additional bandwidth.</p>

<p>Please see http://www.toshibahddvd.com and owner's manual for more information. Some features subject to delayed availability.</p>

<p>- Dolby is a registered trademark of Dolby Laboratories.<br />
- "DTS" is a registered trademark of DTS, Inc.<br />
- HDMI, the HDMI logo, and High-Definition Multimedia Interface are trademarks or registered trademarks of HDMI Licensing, LLC.<br />
- HD DVD and DVD are trademarks of DVD Format/Logo Licensing Corporation.</p>

<p>SOURCE Toshiba America Consumer Products, L.L.C.</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>January  7, 2007 07:58 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 507
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
 				AND entry_id <> 507
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2007/01/toshiba_announces_mid-range_1080p_hd_dvd_player.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
