<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 130";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Dale Cripps'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 130 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category[placement_category_id];
	
	# Still need this to track page views. Used in the Links unit in the footer
	$google_links_channel = $author[channel];

	# Set variables based on entry type. NOTE: Podcasts has its own entry template, so it is not included amongst the choices below.
	switch (5) {
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
	<meta name="keywords" content="per second, pixels per, hdtv format, per frame, dtv receivers, HDTV, hdtv, format, per, DTV, dtv, digital, quality, formats, frame, cbs, display, CBS, pixels, second, receivers, SDTV, programs, today, sdtv" />
	<meta name="description" content="By Joseph Flaherty April 6, 1998 Snell &amp; Wilcox says it all! The Dinosaurs are gone! Adapt or Die! Unlike the dinosaurs, CBS doesn't live in the past, and won't become extinct! As you have just heard from Mike Jordan..." />
	<title>HDTV Magazine Archive &amp; History - 1998 - CBS DTV/HDTV Rollout--How, Why, & When by Joseph Flaherty, Senior vp, CBS. Inc.</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/1998_-_cbs_dtvhdtv_rollout--how_why_when_by_joseph_flaherty_senior_vp_cbs_inc';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('1998 - CBS DTV/HDTV Rollout--How, Why, & When by Joseph Flaherty, Senior vp, CBS. Inc.'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/history/2005/06/1998_-_cbs_dtvhdtv_rollout--how_why_when_by_joseph_flaherty_senior_vp_cbs_inc.php";
		if ($author[img] != '' && 5 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Dale Cripps" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">1998 - CBS DTV/HDTV Rollout--How, Why, & When by Joseph Flaherty, Senior vp, CBS. Inc.</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Dale Cripps</b><br />
				<?=$author_title?>
				Posted on <b>June 26, 2005</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category="></a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/2005/06/1998_-_cbs_dtvhdtv_rollout--how_why_when_by_joseph_flaherty_senior_vp_cbs_inc.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/history/2005/06/1998_-_cbs_dtvhdtv_rollout--how_why_when_by_joseph_flaherty_senior_vp_cbs_inc.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/history/2005/06/1998_-_cbs_dtvhdtv_rollout--how_why_when_by_joseph_flaherty_senior_vp_cbs_inc.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/2005/06/1998_-_cbs_dtvhdtv_rollout--how_why_when_by_joseph_flaherty_senior_vp_cbs_inc.php&amp;phase=2&amp;title=1998%20-%20CBS%20DTV%2FHDTV%20Rollout--How%2C%20Why%2C%20%26%20When%20by%20Joseph%20Flaherty%2C%20Senior%20vp%2C%20CBS.%20Inc.&amp;bodytext=By%20Joseph%20Flaherty%20April%206%2C%201998%20Snell%20%26%20Wilcox%20says%20it%20all%21%20The%20Dinosaurs%20are%20gone%21%20Adapt%20or%20Die%21%20Unlike%20the%20dinosaurs%2C%20CBS%20doesn%27t%20live%20in%20the%20past%2C%20and%20won%27t%20become%20extinct%21%20As%20you%20have%20just%20heard%20from%20Mike%20Jordan...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p>By Joseph Flaherty<br />
April 6, 1998</p>

<p><strong>Snell & Wilcox says it all! The Dinosaurs are gone! Adapt or Die!</strong></p>

<p>Unlike the dinosaurs, CBS doesn't live in the past, and won't become extinct! As you have just heard from Mike Jordan and Bill Korn, CBS will make the digital transition, will do it on the FCC schedule, and will ensure that American viewers will see full HDTV, starting this Fall with a primetime HDTV schedule in the 1080I full HDTV format.</p>

<p>Your network has been our industry's leader in recognizing the vital importance of the digital transition to broadcasting's future. As far back as 1988, CBS set the DTV goals for the FCC mandated transition to digital transmission.</p>

<p>They were, and are:</p>

<p>- To ensure that terrestrial broadcasters will be able to deliver a fully competitive digital TV and HDTV service; </p>

<p>- To provide sufficient VHF and UHF spectrum for terrestrial broadcasters to effect the transition to digital transmission, replicating their present coverage area; - To preserve the value of existing TV receivers, and thus the existing TV audience, during the transition to digital TV and HDTV;</p>

<p>- To provide technical headroom to ensure future competitive parity for broadcasting as digital technologies improve.</p>

<p>We believe that these goals have been met in the ATSC digital television standard. The range of digital formats from SDTV to full HDTV at 1080 lines, 1920 pixels/line, interlace and progressively scanned will permit broadcasters to compete with all comers, now and in the future.</p>

<p>The digital channels have been assigned using the last VHF and UHF spectrum available for terrestrial broadcasting. Any lack of use of these channels will result in their loss and assignment to other services. DTV is "now or never" for broadcasters!</p>

<p>The NTSC audience will be served on the existing analog charnel and on the new digital receivers, thus, maintaining our audiences throughout the transition period.</p>

<p>The broadcast of 1080 HDTV programs will ensure that the 1080 HDTV decoders are integrated into the DTV receivers. <br />
The ATSC standard has the headroom to accommodate improvements in compression and transmission technologies that will keep terrestrial broadcasting in the competitive race for 21st century viewers. For example, full 1080 line progressive scan live pictures transmitted at 60 frames-per-second in our 6 MHz channel will be practical before the DTV transition period is over. steps in this direction are to be seen on the floor of this convention today.</p>

<p>As the DTV services are launched, we believe that the most common DTV formats will be 1080I & P for HDTV, and 480I for SDTV.</p>

<p>As you know, the 1080 format is transmitted in the interlace mode for electronically generated programs and in the progressive mode for film programs. In the live mode, the 1080 format provides 1.421,000 displayable pixels-per-frame, taking account of Kell factor losses.</p>

<p>The 1080P mode for film programs provides 1,866,000 displayable pixels per frame now.</p>

<p>The 720P HDTV format provides 829,000 displayable pixels per frame, or 592,000 fewer pixels-per-frame than 1080I, and 1,037,000 fewer pixels-per-frame than 1080P. In short, the 1080 HDTV format is simply the best quality HDTV format by a large margin.</p>

<p>Both 480I & P are Standard Definition (SDTV) systems. The 460I format has 236,000 displayable pixels-per-frame, and the 480P format has 304,000 displayable pixels-per-frame.</p>

<p>Naturally, these digital SDTV formats are still better than NTSC, which in digital terms, would have just 147,862 pixels-per-frame, or just 10t of the 1080I format.</p>

<p>The reason that the 480I SDTV format will be widely used is simply that all the TV stations in the country are already fully equipped with 4801, 60 fields-per-second equipment today. A move to 480P throughout their plants would require significant reinvestment - an investment offering only a marginal quality improvement over a 480I system.</p>

<p>Further, for multiplex broadcasting purposes, a 480I, 60 fields-Per-Second system, requiring about 5 Mb/s in transmission, supports an additional multiplex channel over a 480P, 60 frame-per-second system which requires about 8 mb/sec.</p>

<p>Picture quality for all the HDTV and SDTV formats is also greatly affected by the smoothness of the motion portrayal, that is, by the number of pictures transmitted-per-second. All American TV systems have always operated at 60 pictures per second to obtain smooth motion portrayal. A system operated at 30 frames-per-second to conserve transmission bit rate will exhibit poor motion portrayal as a function of The broadcast of 1080 HDTV programs will ensure that the 1080 HDTV decoders are integrated into the DTV receivers. <br />
the movement in the scene. Slow frame rates, such as the 24 frame-per-second film rate exhibit judder, strobing, and other artifacts. It's the reason the wheels go backwards in film and objects move across the screen in jerks. It is unlikely that 30 frame motion portrayal will be acceptable to the viewing audience or to our commercial clients for "live", or electronically produced programming.</p>

<p>Thus, CBS will use the 1080I & P HDTV format at 60 pictures-per-second because:</p>

<p>- It is the highest quality HDTV Format and puts us in the best competitive position with DBS and cable program distributors who have already announced the adoption of the 1080I & P format for their HDTV programs.</p>

<p>- The broadcast of 1080 HDTV programs will ensure that the 1080 HDTV decoders are integrated into the DTV receivers. Broadcasting has always depended on all the facilities needed to receive broadcast signals being within the receiver itself. DBS and cable have supplied, and always can supply, set top boxes to decode whatever they wish to sell. without HDTV decoders in the new DTV receivers, broadcasters would be locked out of competing in the HDTV marketplace,</p>

<p>- 1080I equipment is the least expensive HD equipment because it is already in its third and fourth generation and well down the price erosion curve. Among other things, this is due to the widespread use of 1000 plus line interlace formats worldwide. There are no progressive scan HDTV systems in-use, or planned, anywhere in the World outside of the U.S.</p>

<p>- The 1080I format is the "Common Image Format" standard established by the International Telecommunications Union (ITU) for international high definition production and program exchange.</p>

<p>All digital TV and HDTV receivers are being built to receive and decode all the ATSC formats but to display only the 1080I and 480 I & P formats. No receivers are being built to display the 720P format due to the excessive cost of the high frequency scanning system and to the need to switch horizontal scanning within the receiver. 720P signals will be down converted to 480I or P or upconverted to 1080I with the quality losses attendant thereto.</p>

<p>The information now available indicates that:</p>

<p>-CBS, NBC, HBO, MSG, Warner Bros., PBS, DirectTV, and the<br />
Discovery Channel, will use the 1080I & P HDTV format,</p>

<p>-ABC plans to use the 720P HDTV format,</p>

<p>-Fox plans to use only the 480P SDTV format,</p>

<p>-TCI/Microsoft plans to use the Microsoft 480P IIHD-011 SDTV format with a 720P & 1080I & P "pass through" as a premium, higher cost, option.</p>

<p>This broadcaster format list is attached as Figure 1.</p>

<p>At the opening of the convention the scorecard of DTV formats being planned, or offered, by major equipment manufacturers is shown in Figure 2. Note the dominance of 1080I equipment and the near absence of yet-to-be-designed 720P equipment. The main application of 720P is presently found only in format converters.</p>

<p>In the process of picking your DTV broadcasting format, beware of format demonstrations. Most are severely flawed and proponents speak highly of their product on the carton. An accurate comparison of the native quality of several formats is very difficult to produce and display. Such a comparison starts with the scenic elements, matched angles of view, the quality of the lenses, the pre-filtering and enhancement in the cameras, the filtering and response of the tape machines, and most importantly the aperture response of the display devices electronically and their resolution in actual light output.</p>

<p>This last element, the display device, is the biggest problem in critical comparison tests. Viewing HDTV today is a bit like Mark Twain's comment that "Wagner's music is better than it sounds". Today, HDTV is better than it looks! The display devices are the limiting quality factor. While improvements are being made by the month, as of today, no display achieves the full quality potential of America's HDTV system.</p>

<p>This is as it should be! The new wide screen HDTV system needs to be the platform that provides the headroom and challenge for further near term development.</p>

<p>The full potential of any new standard should never be fully encompassed by the existing state-of-the-art, nor should it be so futuristic as to not have its potential achievable in a foreseeable time. NTSC was well beyond the quality of the 1950s color displays, and America's HDTV standard is beyond the quality of today's HD displays. But, unless HD is transmitted, display improvements will not be made.</p>

<p>"Good enough" is no longer "perfect", and may become wholly unsatisfactory </p>

<p>Finally, all roads lead to the home! Digital TV will be a success, or failure, based on consumer reaction, and this, in turn, depends on the quality and quantity of DTV and HDTV programming and on the design, availability, and cost of the DTV receivers.</p>

<p>The latest information we have from the consumer equipment suppliers is shown in Figure 3. As noted before, column 6 - Native Display Format - indicates plans to display only 1080I and 480 I & P formats, converting 720P to one of these display formats.</p>

<p>1080I and 480 I & P will be the dominant DTV formats, and by November 1, 1999 at least 33 DTV stations are due to be on-air, reaching 53% of U.S. households!</p>

<p>So, as we evaluate DTV and HDTV and plan for their implementation, we must bear in mind that today's "standard of service" enjoyed by our viewers will not be their "level of expectation" tomorrow. "Good enough" is no longer "perfect", and may become wholly unsatisfactory.</p>

<p>"Quality is a moving target, both in programs and in technology. Our judgments as to the future must not be based on today's performance, nor on minor improvements thereto.:</p>

<p>____________________________________________________</p>

<p>About Joseph Flaherty</p>

<p><em>Joseph Flaherty is senior vice president of technology at CBS. In this position, he advises CBS management on issues and strategies related to broadcast technology, and represents CBS nationally and internationally with major manufacturers and on government and industry committees and organizations. Flaherty joined CBS in 1957, and has directed the Engineering and Development Department since 1967—first as general manager, then, since 1977, as vice president and general manager. During his career, he has received many prestigious broadcast industry awards, including several Emmys for technical achievement; the David Sarnoff Gold Medal for progress in television engineering; the NAB Engineering Award; the Progress Medal of the SMPTE; and the International Montreux Achievement Gold Medal. Flaherty also received France's Chevalier de l'Ordre des Arts et des Lettres, and in 1985 was awarded France's highest decoration, the Chevalier de l'[Ordre National de la Legion d'Honneur, by French President François Mitterand. He is a Fellow of the British Institution of Electrical Engineers; the British Royal Television Society; and SMPTE. Flaherty holds a degree in physics and an honorary doctorate of science from Rockhurst College in Kansas City, Missouri.</em></p>

<p><br />
</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Dale Cripps</b>, <b>June 26, 2005 01:22 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 130
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
			
 		<?if (5 <> 7) {
 			# Recent Articles by Author (exclude this one)
 			# Do not show recent articles for Bulletins.
 			$qry = "
 			SELECT entry_id, entry_blog_id, entry_created_on, entry_title, author_id, author_name
 			FROM mt_entry e, mt_author a
 			WHERE entry_blog_id = 5
 				AND entry_id <> 130
 				AND entry_status = 2
 				AND entry_author_id = a.author_id
 				AND a.author_name = 'Dale Cripps'
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
 				<h2>About Dale Cripps</h2>
 				<?=stripslashes($author[bio_short])?>
 			<span class="corners-bottom"><span></span></span></div>
 		<?}?>
		</td><td id="right">

 		<?if ($about != '') {?>
 			<div class="item"><span class="corners-top"><span></span></span>
 				<h2>About Archive &amp; History</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/2005/06/1998_-_cbs_dtvhdtv_rollout--how_why_when_by_joseph_flaherty_senior_vp_cbs_inc.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
