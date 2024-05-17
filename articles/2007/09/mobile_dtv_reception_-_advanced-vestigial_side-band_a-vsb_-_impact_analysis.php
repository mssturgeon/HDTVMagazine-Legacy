<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 686";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Rodolfo La Maestra'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 686 AND placement_is_primary = 1";
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
	<meta name="keywords" content="dtv reception, advanced vestigial, vestigial side, side band, mobile dtv, quality, channel, mobile, dtv, HDTV, VSB, vsb, hdtv, DTV, channels, ATSC, atsc, system, Mobile, consumers, services, several, image, already, bandwidth" />
	<meta name="description" content="As I mentioned in my previous articles I am concerned with the bandwidth required for the A-VSB mobile transmission taken from the 8-VSB transmitted channel of DTV terrestrial television, an issue that I particularly consider a risky opportunity to further degrade the quality of full HDTV channels.

Many broadcasting stations are already using part of the HDTV 6 MHz channel (19.4 Mbps) to transmit several simultaneous multi-cast SD channels, and those are already degrading considerably the quality of the once alone HD channel, reducing its available bandwidth to close to half of the 19.4 Mbps, a bandwidth it requires to show acceptable quality." />
	<title>HDTV Magazine Articles - Mobile DTV Reception - Advanced-Vestigial Side-Band (A-VSB) - Impact Analysis</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/mobile_dtv_reception_-_advanced-vestigial_side-band_a-vsb_-_impact_analysis';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Mobile DTV Reception - Advanced-Vestigial Side-Band (A-VSB) - Impact Analysis'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/articles/2007/09/mobile_dtv_reception_-_advanced-vestigial_side-band_a-vsb_-_impact_analysis.php";
		if ($author[img] != '' && 1 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Rodolfo La Maestra" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Mobile DTV Reception - Advanced-Vestigial Side-Band (A-VSB) - Impact Analysis</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Rodolfo La Maestra</b><br />
				<?=$author_title?>
				Posted on <b>September  6, 2007</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Technology">Technology</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2007/09/mobile_dtv_reception_-_advanced-vestigial_side-band_a-vsb_-_impact_analysis.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/articles/2007/09/mobile_dtv_reception_-_advanced-vestigial_side-band_a-vsb_-_impact_analysis.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/articles/2007/09/mobile_dtv_reception_-_advanced-vestigial_side-band_a-vsb_-_impact_analysis.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2007/09/mobile_dtv_reception_-_advanced-vestigial_side-band_a-vsb_-_impact_analysis.php&amp;phase=2&amp;title=Mobile%20DTV%20Reception%20-%20Advanced-Vestigial%20Side-Band%20%28A-VSB%29%20-%20Impact%20Analysis&amp;bodytext=As%20I%20mentioned%20in%20my%20previous%20articles%20I%20am%20concerned%20with%20the%20bandwidth%20required%20for%20the%20A-VSB%20mobile%20transmission%20taken%20from%20the%208-VSB%20transmitted%20channel%20of%20DTV%20terrestrial%20television%2C%20an%20issue%20that%20I%20particularly%20consider%20a%20risky%20opportunity%20to%20further%20degrade%20the%20quality%20of%20full%20HDTV%20channels.%0A%0AMany%20broadcasting%20stations%20are%20already%20using%20part%20of%20the%20HDTV%206%20MHz%20channel%20%2819.4%20Mbps%29%20to%20transmit%20several%20simultaneous%20multi-cast%20SD%20channels%2C%20and%20those%20are%20already%20degrading%20considerably%20the%20quality%20of%20the%20once%20alone%20HD%20channel%2C%20reducing%20its%20available%20bandwidth%20to%20close%20to%20half%20of%20the%2019.4%20Mbps%2C%20a%20bandwidth%20it%20requires%20to%20show%20acceptable%20quality.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<div class="editorial">If you haven't done so already, please be sure to read the other articles in this series:
<ul>
<li><a href="/articles/2007/08/mobile_dtv_reception_-_advanced-vestigial_side-band_a-vsb_-_the_system.php">Mobile DTV Reception - Advanced-Vestigial Side-Band (A-VSB) - The System</a></li>
<li><a href="/articles/2007/08/mobile_dtv_reception_-_advanced-vestigial_side-band_a-vsb_-_bandwidth_requirements.php">Mobile DTV Reception - Advanced-Vestigial Side-Band (A-VSB) - Bandwidth Requirements</a></li>
<li><a href="/articles/2007/09/mobile_dtv_reception_-_advanced-vestigial_side-band_a-vsb_-_the_implementation.php">Mobile DTV Reception - Advanced-Vestigial Side-Band (A-VSB) - The Implementation</a></li>
</ul></div>
<br />
As I mentioned in my previous articles I am concerned with the bandwidth required for the A-VSB mobile transmission taken from the 8-VSB transmitted channel of DTV terrestrial television, an issue that I particularly consider a risky opportunity to further degrade the quality of full HDTV channels.

<p>Many broadcasting stations are already using part of the HDTV 6 MHz channel (19.4 Mbps) to transmit several simultaneous multi-cast SD channels, and those are already degrading considerably the quality of the once alone HD channel, reducing its available bandwidth to close to half of the 19.4 Mbps, a bandwidth it requires to show acceptable quality.</p>

<p><br />
<B>Points of Reference</B></p>

<p>One particular example I sadly watched deteriorate over the past 8 years is the WETA PBS HDTV channel in the area of Washington D.C. The once excellent PBS broadcaster gradually added several SD sub-channels into the 6MHz channel allocation, it started doing the mix only during the day, and switching to HD at night, however, in parallel to the HD channel an SD banner kept transmitting for each SD channel while not sending content.</p>

<p>Then, over the past few years WETA PBS has been transmitting them all in parallel during the whole day, 1HD and 3SD, and the HD channel quality has suffered to the point of being now a poor representation of HD even when no fast motion is present on the image.</p>

<p>PBS's approach has motivated me to skip the HD channel due to its poor quality. I cannot appreciate its content when is transmitted in such degraded quality.</p>

<p>Ironically, I started viewing the HD channel content in 1998 because I was attracted by its unique image quality, a pioneer station in the early adoption of HDTV. In other words, the quality of the image made me view a program that I might not have viewed otherwise, the power of HDTV quality produced the effect I expected, even when I am not a TV viewer.</p>

<p>WETA was first in HDTV in 1998; it was an example of early adoption of quality HD when only a few others in the nation did the effort to transmit HD, now their dedication to HD quality is sacrificed for multi-casting, and others are following the same path or quality degradation.</p>

<p><br />
<B>The Mbps Challenge</B></p>

<p>I can anticipate how the mobile A-VSB bandwidth requirements could become an invitation to further deteriorate main HDTV terrestrial channels if using the sharing approach within the 6MHz slot, especially on those channels that are already multi-casting several SD feeds together with their parallel HD channel.</p>

<p>Samsung's assessment that subtracting 7Mbps from a HD channel for A-VSB would only be "a bit of challenge" for the quality of an HDTV channel, is short by more than a bit, we have currently many cases with terrestrial broadcast, satellite, and cable services that are doing such bit starvation and the quality degradation is very noticeable, and very unacceptable.</p>

<p>Since Mr. Godfrey indicated that some broadcasters would probably use alternative channels or independent channels to distribute mobile services, while the HD channel is broadcast untouched separately, there is a chance that the AVSB implementation would not affect HDTV quality.</p>

<p>However, if such situation of alternative channels would not be available in certain areas, the possibility of quality reduction of the HD feed is quite possible.</p>

<p><br />
<B>How the Tool is Used Could be the Problem</B></p>

<p>Samsung is supplying the technology and the tool, broadcasters could take that tool and transform quality into a quantity revenue model, and we know the effect of that scenario already from the experiences with cable and satellite when using their over-compression to fit more channels and services within a limited pipe.</p>

<p>It is very important to note that although the DTV implementation does not mandate HDTV resolution, it is the main reason why consumers are investing in expensive sets, to view a quality image, not just a digital image. Digitizing HD to inferior quality would not motivate consumers embrace the DTV transition and buy new sets, and would certainly upset consumers that would see HDTV quality disappear after they invested on new sets.</p>

<p>Ideally, there should be a system to monitor quality across all video services offered in the nation, satellite, cable, and broadcast. Because that is not in place, consumers are left with the option to accept or switch services in a constant basis. Although that promotes competition it also causes unnecessary inconvenience and extra switching costs to consumers. We will have to wait to witness if the AVSB tools are actually used to benefit the consumers without degrading HD quality.</p>

<p><br />
<B>A Competitor - LG's MPH (Mobile-Pedestrian-Handheld)</B></p>

<p>In April 2007 at NAB, LG introduced a system developed by Zenith Electronics and Harris to broadcast ATSC DTV broadcasts to mobile devices. It was not disclosed if the system was submitted to consideration as a standard, to compete with the A-VSB system from Samsung and Sinclair, mentioned above.</p>

<p>The system uses a multiple-stream approach, the main stream for legacy DTV devices tuning to 8-VSB and the MPH stream for mobile devices even operating at high speed.</p>

<p><br />
<B>The Efforts for a Mobile Standard</B></p>

<p>Since I first wrote these articles, several other solutions were submitted for acceptance to the ATSC from other industry competitors.</p>

<p>In June 2007, the ATSC disclosed that a total of 10 proposals were submitted for consideration responding to a request for proposals issued by the ATSC in May, and due in July.  Several conditions were established, which include but are not limited to, protecting legacy receivers and existing services, be compatible with the current ATSC DTV system, and operate in the same RF channel without penalizing legacy equipment.</p>

<p>The mobile standard was named ATSC-M/H, and the work was assigned to the ATSC Specialist Group on ATSC-M/H (TSG/S4), led by Mark Aitken of Sinclair Broadcast Group.</p>

<p>In other parts of the world, the European Union initiated the implementation of DVB-H (Digital Video Broadcasting to Handhelds) as the mobile devices standard for TV broadcasting in Europe.</p>

<p>This concludes this series of articles regarding A-VSB as a DTV Mobile Broadcasting solution.</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Rodolfo La Maestra</b>, <b>September  6, 2007 05:48 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 686
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
 				AND entry_id <> 686
 				AND entry_status = 2
 				AND entry_author_id = a.author_id
 				AND a.author_name = 'Rodolfo La Maestra'
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
 				<h2>About Rodolfo La Maestra</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2007/09/mobile_dtv_reception_-_advanced-vestigial_side-band_a-vsb_-_impact_analysis.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
