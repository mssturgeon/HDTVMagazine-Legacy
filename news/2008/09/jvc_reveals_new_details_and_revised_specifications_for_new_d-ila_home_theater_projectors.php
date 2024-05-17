<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1493";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1493 AND placement_is_primary = 1";
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
	<meta name="keywords" content="native contrast, new projectors, contrast ratio, professional products, jvc company, DLA, dla, jvc, JVC, projectors, new, company, contrast, thx, Company, available, THX, lens, high, ila, ILA, professional, color, specifications, native" />
	<meta name="description" content="JVC today released additional technical information and revised specifications for four recently announced D-ILA home theater projectors to be available this fall. Among the four new full high-definition front projectors are two THX&amp;reg; Certified* models that offer a native contrast ratio of 50,000:1, the industry's highest. The other two models achieve native contrast ratios of 32,000:1 and 30,000:1. Like current highly-acclaimed JVC projectors, all four new models achieve high contrast ratios without the use of an auto iris.

The new projectors are the..." />
	<title>HDTV Magazine Bulletins - JVC Reveals New Details And Revised Specifications For New D-ILA Home Theater Projectors</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/jvc_reveals_new_details_and_revised_specifications_for_new_d-ila_home_theater_projectors';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('JVC Reveals New Details And Revised Specifications For New D-ILA Home Theater Projectors'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2008/09/jvc_reveals_new_details_and_revised_specifications_for_new_d-ila_home_theater_projectors.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">JVC Reveals New Details And Revised Specifications For New D-ILA Home Theater Projectors</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>September 25, 2008</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Products & Equipment">Products & Equipment</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2008/09/jvc_reveals_new_details_and_revised_specifications_for_new_d-ila_home_theater_projectors.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2008/09/jvc_reveals_new_details_and_revised_specifications_for_new_d-ila_home_theater_projectors.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2008/09/jvc_reveals_new_details_and_revised_specifications_for_new_d-ila_home_theater_projectors.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2008/09/jvc_reveals_new_details_and_revised_specifications_for_new_d-ila_home_theater_projectors.php&amp;phase=2&amp;title=JVC%20Reveals%20New%20Details%20And%20Revised%20Specifications%20For%20New%20D-ILA%20Home%20Theater%20Projectors&amp;bodytext=JVC%20today%20released%20additional%20technical%20information%20and%20revised%20specifications%20for%20four%20recently%20announced%20D-ILA%20home%20theater%20projectors%20to%20be%20available%20this%20fall.%20Among%20the%20four%20new%20full%20high-definition%20front%20projectors%20are%20two%20THX%26reg%3B%20Certified%2A%20models%20that%20offer%20a%20native%20contrast%20ratio%20of%2050%2C000%3A1%2C%20the%20industry%27s%20highest.%20The%20other%20two%20models%20achieve%20native%20contrast%20ratios%20of%2032%2C000%3A1%20and%2030%2C000%3A1.%20Like%20current%20highly-acclaimed%20JVC%20projectors%2C%20all%20four%20new%20models%20achieve%20high%20contrast%20ratios%20without%20the%20use%20of%20an%20auto%20iris.%0A%0AThe%20new%20projectors%20are%20the...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p class="prtitle">JVC Reveals New Details And Revised Specifications For New D-ILA Home Theater Projectors</p>

<center><i>Among four new projectors are two THX&reg; Certified models with the industry's highest native contrast ratio - 50,000:1.</i></center><br />
<br />

<p><B>WAYNE, NJ, September 25, 2008</B> - JVC today released additional technical information and revised specifications for four recently announced D-ILA home theater projectors to be available this fall. Among the four new full high-definition front projectors are two THX&reg; Certified* models that offer a native contrast ratio of 50,000:1, the industry's highest. The other two models achieve native contrast ratios of 32,000:1 and 30,000:1. Like current highly-acclaimed JVC projectors, all four new models achieve high contrast ratios without the use of an auto iris.</p>

<p>The new projectors are the DLA-HD750 and DLA-HD350, to be marketed by JVC's consumer group, JVC Company of America, and the DLA-RS20 and DLA-RS10 from JVC's professional group, JVC Professional Products Company. All four projectors were first announced earlier this month with lower contrast ratio specifications.</p>

<p>Contributing to the projectors' superior native contrast ratio is a newly developed lens with a 16-step fixed aperture in the DLA-HD750 and DLA-RS20 that helps eliminate extraneous light that can reduce contrast levels and allows adjustment of brightness to suit user preferences. The DLA-HD350 and DLA-RS10 feature a three-step aperture. In addition, changing the structural design of the optical section from a conventional L-shape with a mirror to a straight configuration reduced light leakage. Combining this new structure with a highly efficient lamp allows the new projectors to achieve a brightness of 900 (DLA-HD750/DLA-HD20) and 1,000 (DLA-HD350/DLA-RS10) lumens.</p>

<p>The DLA-HD750 and DLA-RS20 are both THX Certified to ensure accurate and exceptional picture quality in the home theater environment. A trusted cinema and consumer brand, THX certification provides JVC a means to further differentiate its projectors from competitive offerings and communicate a clear message about quality to customers.</p>

<p>Core technologies and many of the features that earned earlier JVC projectors accolades from reviewers and users alike are also incorporated into the new projectors. But improvements in several key areas allow all four new models to deliver brighter, more intense images. What's more, JVC has engineered an all-new chassis that is both smaller and more cost-efficient to manufacture.</p>

<p>Critical carried over technologies include JVC's proprietary 0.7-inch full HD D-ILA devices and the company's wire-grid optical engine. These will once again deliver the industry's highest native contrast ratio, meaning that there is no dynamic iris to artificially inflate contrast specifications. As a result, the projectors simultaneously deliver true blacks and extraordinary luminance detail thanks to native contrast ratios of 50,000:1 (DLA-HD750/DLA-RS20), 32,000:1(DLA-RS10) and 30,000:1 (DLA-HD350).</p>

<p>JVC-developed D-ILA technology employs three (RGB) devices for a naturally rich, flicker-free picture even when there is rapid movement in the image. In addition, the optical engine with wire grid polarizers<br />
ensures precise light polarization without light leakage for true black level reproduction. Together, the D-ILA devices and the engine guarantee a smooth picture with vivid colors and high contrast.</p>

<p>Among key new developments is the use of the HQV Reon-VX video processor developed by Silicon Optix to ensure excellent image reproduction with precision I/P conversion and scaling. In addition, the DLA-HD750 and DLA-RS20 feature color management that allows control of colors individually by R, G, B, C, Y, or M, in three separate axes of color phase, chroma saturation and brightness. Up to three customized color settings can be stored for future use.</p>

<p>Also unique to the DLA-HD750 and DLA-RS20 is THX Certification. With a strict focus on image quality and signal processing performance, THX certification promises that the HD750 and RS20 are capable of presenting a wide range of video content at maximum resolutions with the correct color and luminance levels. In addition, THX has created a battery of signal processing tests that challenge the projector's scaling, motion conversion and de-interlacing capabilities. This type of in-depth analysis predicts how the HD750 and RS20 will present a variety of high definition and standard definition content.</p>

<p>The JVC HD750 and RS20 will also feature THX Movie Mode, a pre-calibrated video setting for watching movies on DVD, Blu-ray HD or broadcast television. This playback feature is designed to recreate the cinema experience at home by setting the display's gamma, luminance, color temperature and other settings to mirror those used by filmmakers in post-production. THX Movie Mode also ensures projector brightness is optimal for large screen viewing.</p>

<p>Features shared by all four new projectors include:</p>

<p>      1. High-performance 2x motorized zoom lens<br />
      A new high-performance 2x zoom lens with motorized focus, zoom and shift features a large diameter, all-glass lens system with 17 elements in 15 groups that includes an ED lens to project a full HD image with exceptional depth. In addition, a unique automatic lens cover opens when the projector is powered up and closes when powered down to protect against dust.</p>

<p>      2. Customized on-screen gamma control<br />
      Manual adjustment of the gamma curve is possible via an on-screen display, allowing the viewer to adjust projector luminance levels by either increasing contrast in scenes that are too dark or dimming washed-out scenes to ensure precise brightness levels suited to individual preferences. Up to three settings can be adjusted and stored for future use.</p>

<p>      3. Flexible set-up<br />
      Setup is easy with the ±80 percent vertical and ±34 percent horizontal motorized lens shift function that allows the projected picture to be moved horizontally or vertically via the remote control.</p>

<p>      4. Quiet Operation<br />
      A more efficient cooling system reduces fan noise to 19dB.</p>

<p></p>

<p>Other features include an external 12-volt trigger (not available on the DLA-HD350) that can be used to automatically raise and lower a screen or draw curtains, or it can be linked to engaging the V-Stretch mode and move an optional anamorphic wide screen lens system into place.</p>

<p>All four projectors comply with HDMI version 1.3 (Deep Color/CEC) specifications, which reproduce subtler shades of grey, and CEC compatibility for system control.</p>

<p>The DLA-HD350, available in either glossy piano black or glossy white, and the DLA-HD750, available in a pearl black finish, will be available in November through JVC Company of America. The DLA-HD350 will be available for less than $6,000, while the DLA-HD750 will be available for less than $8,000. Both projectors will be part of the company's Procision series of premium-level products.</p>

<p>The DLA-RS10 and the DLA-RS20, available in a pearl black finish, will be available from JVC Professional Products Company in November for less than $6,000 and $8,000, respectively, and will be part of the company's Reference Series high end projector line.</p>

<p>About JVC<br />
JVC Company of America and JVC Professional Products Company are both divisions of JVC Americas Corp., based in Wayne, New Jersey and a wholly-owned subsidiary of Victor Company of Japan Ltd. JVC Professional Products Company is a leading manufacturer and distributor of a complete line of broadcast and professional equipment. JVC Company of America markets a complete line of consumer video and audio equipment. For further product information, visit JVC's website at http://www.jvc.com.</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>September 25, 2008 07:48 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 1493
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
 				AND entry_id <> 1493
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2008/09/jvc_reveals_new_details_and_revised_specifications_for_new_d-ila_home_theater_projectors.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
