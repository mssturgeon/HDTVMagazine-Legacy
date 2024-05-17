<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1364";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1364 AND placement_is_primary = 1";
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
	<meta name="keywords" content="multimedia drive, iomega corporation, iomega screenplay, high definition, looking statements, iomega, Iomega, screenplay, ScreenPlay, drive, Drive, multimedia, Multimedia, video, corporation, product, Corporation, storage, media, photos, high, component, content, new, statements" />
	<meta name="description" content="Iomega Corporation (NYSE:IOM) , a global leader in data protection and security, today announced the new Iomega(R) ScreenPlay(TM) HD Multimedia Drive, a portable external hard drive that leaves the PC behind, delivering multimedia content to high-definition televisions and home theater systems.

The Iomega ScreenPlay HD Multimedia Drive is a 500GB* drive with the storage capacity to hold up to..." />
	<title>HDTV Magazine Bulletins - New Iomega(R) ScreenPlay(TM) HD Multimedia Drive Brings High-Definition Content, via Upscaling, From the PC to the Home Theater System</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/new_iomegar_screenplaytm_hd_multimedia_drive_brings_high-definition_content_via_upscaling_from_the_pc_to_the_home_theater_system';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('New Iomega(R) ScreenPlay(TM) HD Multimedia Drive Brings High-Definition Content, via Upscaling, From the PC to the Home Theater System'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2008/04/new_iomegar_screenplaytm_hd_multimedia_drive_brings_high-definition_content_via_upscaling_from_the_pc_to_the_home_theater_system.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">New Iomega(R) ScreenPlay(TM) HD Multimedia Drive Brings High-Definition Content, via Upscaling, From the PC to the Home Theater System</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>April 23, 2008</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Products & Equipment">Products & Equipment</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2008/04/new_iomegar_screenplaytm_hd_multimedia_drive_brings_high-definition_content_via_upscaling_from_the_pc_to_the_home_theater_system.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2008/04/new_iomegar_screenplaytm_hd_multimedia_drive_brings_high-definition_content_via_upscaling_from_the_pc_to_the_home_theater_system.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2008/04/new_iomegar_screenplaytm_hd_multimedia_drive_brings_high-definition_content_via_upscaling_from_the_pc_to_the_home_theater_system.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2008/04/new_iomegar_screenplaytm_hd_multimedia_drive_brings_high-definition_content_via_upscaling_from_the_pc_to_the_home_theater_system.php&amp;phase=2&amp;title=New%20Iomega%28R%29%20ScreenPlay%28TM%29%20HD%20Multimedia%20Drive%20Brings%20High-Definition%20Content%2C%20via%20Upscaling%2C%20From%20the%20PC%20to%20the%20Home%20Theater%20System&amp;bodytext=Iomega%20Corporation%20%28NYSE%3AIOM%29%20%2C%20a%20global%20leader%20in%20data%20protection%20and%20security%2C%20today%20announced%20the%20new%20Iomega%28R%29%20ScreenPlay%28TM%29%20HD%20Multimedia%20Drive%2C%20a%20portable%20external%20hard%20drive%20that%20leaves%20the%20PC%20behind%2C%20delivering%20multimedia%20content%20to%20high-definition%20televisions%20and%20home%20theater%20systems.%0A%0AThe%20Iomega%20ScreenPlay%20HD%20Multimedia%20Drive%20is%20a%20500GB%2A%20drive%20with%20the%20storage%20capacity%20to%20hold%20up%20to...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p class="prtitle">New Iomega(R) ScreenPlay(TM) HD Multimedia Drive Brings High-Definition Content, via Upscaling, From the PC to the Home Theater System</p>

<p><B>SAN DIEGO, April 23 /PRNewswire-FirstCall/</B> -- Iomega Corporation (NYSE:IOM) , a global leader in data protection and security, today announced the new Iomega(R) ScreenPlay(TM) HD Multimedia Drive, a portable external hard drive that leaves the PC behind, delivering multimedia content to high-definition televisions and home theater systems.</p>

<p>The Iomega ScreenPlay HD Multimedia Drive is a 500GB* drive with the storage capacity to hold up to 2 million photos, 9,250 hours of music, or 750 hours of video**, enabling family and friends to share high-resolution photos, music, and video clips in the comfort of the living room, without a computer.</p>

<p>"Today's mainstream home entertainment products are entering the high-definition era, which presents a huge opportunity for media devices in the living room like the Iomega ScreenPlay HD Multimedia Drive," said Loren Bryner, ScreenPlay product manager, Iomega Corporation. "The ScreenPlay Drive is a great solution for Windows users -- there's no better way to leverage a big-screen LCD or plasma TV investment. The combination of ScreenPlay and today's televisions give users a seamless way to show off family photos, play music, and enjoy their videos from the best seat in the house."</p>

<p>The portable ScreenPlay HD Multimedia Drive comes with a remote control for easy navigation and includes both HDMI and component video outputs for displaying high-resolution digital photos and video.</p>

<p>"We took everything we learned from previous ScreenPlay products and created a better media-sharing tool that's right on for today's multi-media sharing experience," continued Bryner. "The new ScreenPlay HD Multimedia Drive is an intuitive, easy-to-use solution that not only makes it easier to enjoy all kinds of digital content but also facilitates moving and playing content from one display or location to another."</p>

<p><br />
<B>Iomega ScreenPlay HD Multimedia Drive Technical Description</B></p>

<p>The ScreenPlay HD Multimedia Drive 500GB weighs 2 pounds and measures a diminutive 7.7" x 2.3" x 5", or about the size of a paperback book.</p>

<p>Inside the ScreenPlay HD Multimedia Drive is a 500GB 3.5 inch 7200 RPM hard drive formatted with the NTFS file system. Video connection options include HDMI, component and composite video, and SCART (RGB). Audio connection options include composite RCA and coaxial S/PDIF outputs. PC transfers use the USB 2.0 interface. USB, composite video, and component video cables are included.</p>

<p>Using the HDMI or component outputs, the user can choose video settings from 480i/480p/720p/1080i (720p and 1080i are achieved through upscaling). Supported media formats include MP3, AC3 (Dolby(R) Digital Encoding), WAV, WMA, MPEG-1, MPEG-2 (AVI/VOB), MPEG-4 (AVI/DiVX 3.11, 4.x, 5.x/XViD) and JPEG.</p>

<p><br />
<B>System Requirements</B></p>

<p>The Iomega(R) ScreenPlay(TM) HD Multimedia Drive is designed for use with the following PC operating systems: Microsoft(R) Windows(R) 2000 Professional, XP Home/XP Professional/XP Professional x64, Windows Vista(TM).</p>

<p><br />
<B>Availability</B></p>

<p>The Iomega(R) ScreenPlay(TM) HD Multimedia Drive USB 2.0/AV 500GB is now available in the Americas for $209.95. (Price is U.S. suggested retail.) The Iomega ScreenPlay HD Multimedia Drive is expected to be available in international markets in late May for euro 179.99.</p>

<p><br />
<B>About Iomega</B></p>

<p>Iomega Corporation, headquartered in San Diego, is a worldwide leader in innovative storage and network security solutions for small and mid-sized businesses, consumers and others. The Company has sold more than 400 million digital storage drives and disks since its inception in 1980. Today, Iomega's product portfolio includes industry leading network attached storage products, external hard drives, and the award-winning removable storage technology, the REV(R) Backup Drive. OfficeScreen(R), Iomega's managed security services, available in the U.S. and select markets in Europe, provides enterprise quality perimeter security and secure remote network access for SMBs, which help protect small enterprises from data theft and liability. To learn about all of Iomega's digital storage products and managed services solutions, please go to the Web at http://www.iomega.com/. Resellers can visit Iomega at http://www.iomega.com/ipartner.</p>

<p>NOTE: The statements contained in this release regarding development, production and distribution of the Iomega(R) ScreenPlay(TM) HD Multimedia Drive USB 2.0/AV 500GB, anticipated product pricing and availability, expected product performance and specifications, future applications for the new product and all other statements that are not purely historical, are forward- looking statements within the meaning of the Private Securities Litigation Reform Act of 1995. All such forward-looking statements are based upon information available to Iomega as of the date hereof, and Iomega disclaims any intention or obligation to update any such forward-looking statements. Actual results could differ materially from current expectations. Factors that could cause or contribute to such differences include, but are not limited to, the successful completion of product development and testing, market acceptance of, and demand for, the Iomega product, any difficulties encountered in ramping up production or other manufacturing issues, including component availability and pricing, co-development, production, and distribution issues, product pricing and conformity to specifications, dependence upon third party suppliers, competition, intellectual property rights and other risks and uncertainties identified in the reports filed from time to time by Iomega with the U.S. Securities and Exchange Commission, including Iomega's Annual Report on Form 10-K for the year ended December 31, 2007, and its most recent Quarterly Report on Form 10-Q.</p>

<p>* 1GB = 1,000,000,000 bytes.<br />
** Examples refer to 3-megapixel JPEG photos (at 4 photos/MB), 128 Kbps MP3 audio (at 1.1 min/MB), and 720 x 480 MPEG 2 video (at 11MB/min).</p>

<p><br />
Copyright(C) 2008 Iomega Corporation. All rights reserved. Iomega, Zip, ScreenPlay, REV, OfficeScreen, StorCenter, and Media Xporter are either registered trademarks or trademarks of Iomega Corporation in the United States and/or other countries. All other trademarks, trade names, service marks, and logos referenced herein belong to their respective companies.</p>

<p>Media please contact:<br />
Chris Romoser, Iomega Corporation, (858) 314-7148 romoser@iomega.com</p>

<p>Analyst/Investors, please contact:<br />
Preston Romm, Iomega Corporation, (858) 314-7188</p>

<p>Source: Iomega Corporation</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>April 23, 2008 09:07 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 1364
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
 				AND entry_id <> 1364
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2008/04/new_iomegar_screenplaytm_hd_multimedia_drive_brings_high-definition_content_via_upscaling_from_the_pc_to_the_home_theater_system.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
