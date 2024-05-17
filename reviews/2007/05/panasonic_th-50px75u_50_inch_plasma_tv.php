<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 602";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 602 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category[placement_category_id];
	
	# Still need this to track page views. Used in the Links unit in the footer
	$google_links_channel = $author[channel];

	# Set variables based on entry type. NOTE: Podcasts has its own entry template, so it is not included amongst the choices below.
	switch (8) {
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
	<meta name="keywords" content="recommend leaving, calibration settings, quite good, our, review, black, color, recommend, Color, good, settings, plasma, issues, picture, buy, time, pleased, panasonic, Panasonic, found, well, leaving, job, bezel, analog" />
	<meta name="description" content="Back in January of this year we reviewed the Panasonic TH-50PX60U and were impressed with what we saw. At that time we had three things we didn't like about the TV. Today we take a look at Panasonic's new 50 inch 720p plasma, the TH-50PX75U, which comes with a street price of $2100 and addresses the three nit picks we had with the earlier model. This is a 720p TV with a resolution of 1366 x 768, a 10,000:1 contrast ratio, two HDMI inputs, and an SD card reader. One of the issues we (mainly Ara) had with its predecessor was its silver bezel. The 50PX75U comes with a thin black bezel that hides the speakers very well and looks quite good even when its off. Rounding out the features, the TV come with built-in ATSC/QAM/NTSC Tuners. Build quality is quite good. The product dimensions are 47.7 x 3.8 x 31.3 inches and it weighs 89.8 pounds which is almost identical to the previous model." />
	<title>HDTV Magazine Reviews - Panasonic TH-50PX75U 50 Inch Plasma TV</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/panasonic_th-50px75u_50_inch_plasma_tv';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Panasonic TH-50PX75U 50 Inch Plasma TV'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/reviews/2007/05/panasonic_th-50px75u_50_inch_plasma_tv.php";
		if ($author[img] != '' && 8 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Panasonic TH-50PX75U 50 Inch Plasma TV</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>May 22, 2007</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=HDTV Displays">HDTV Displays</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/reviews/2007/05/panasonic_th-50px75u_50_inch_plasma_tv.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/reviews/2007/05/panasonic_th-50px75u_50_inch_plasma_tv.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/reviews/2007/05/panasonic_th-50px75u_50_inch_plasma_tv.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/reviews/2007/05/panasonic_th-50px75u_50_inch_plasma_tv.php&amp;phase=2&amp;title=Panasonic%20TH-50PX75U%2050%20Inch%20Plasma%20TV&amp;bodytext=Back%20in%20January%20of%20this%20year%20we%20reviewed%20the%20Panasonic%20TH-50PX60U%20and%20were%20impressed%20with%20what%20we%20saw.%20At%20that%20time%20we%20had%20three%20things%20we%20didn%27t%20like%20about%20the%20TV.%20Today%20we%20take%20a%20look%20at%20Panasonic%27s%20new%2050%20inch%20720p%20plasma%2C%20the%20TH-50PX75U%2C%20which%20comes%20with%20a%20street%20price%20of%20%242100%20and%20addresses%20the%20three%20nit%20picks%20we%20had%20with%20the%20earlier%20model.%20This%20is%20a%20720p%20TV%20with%20a%20resolution%20of%201366%20x%20768%2C%20a%2010%2C000%3A1%20contrast%20ratio%2C%20two%20HDMI%20inputs%2C%20and%20an%20SD%20card%20reader.%20One%20of%20the%20issues%20we%20%28mainly%20Ara%29%20had%20with%20its%20predecessor%20was%20its%20silver%20bezel.%20The%2050PX75U%20comes%20with%20a%20thin%20black%20bezel%20that%20hides%20the%20speakers%20very%20well%20and%20looks%20quite%20good%20even%20when%20its%20off.%20Rounding%20out%20the%20features%2C%20the%20TV%20come%20with%20built-in%20ATSC%2FQAM%2FNTSC%20Tuners.%20Build%20quality%20is%20quite%20good.%20The%20product%20dimensions%20are%2047.7%20x%203.8%20x%2031.3%20inches%20and%20it%20weighs%2089.8%20pounds%20which%20is%20almost%20identical%20to%20the%20previous%20model.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<center><a href="/cgi-bin/ntlinktrack.cgi?http://www.htguys.com/"><img src="/images/hdtv-podcast_227x100.gif" alt="The HDTV Podcast"></a><br /><b>This review is featured in the latest podcast from The HT Guys</b><br /><a href="http://www.htguys.com/archive/2007/May22.html">http://www.htguys.com/archive/2007/May22.html</a></center>
<br />

<p>Back in January of this year we reviewed the <a href="http://www2.panasonic.com/webapp/wcs/stores/servlet/vModelDetail?storeId=15001&catalogId=13401&itemId=97563&catGroupId=24973&modelNo=TH-50PX600U&surfModel=TH-50PX600U&cacheProgram=11002&cachePartner=7000000000000005702">Panasonic TH-50PX60U</a> and were impressed with what we saw. At that time we had three things we didn't like about the TV. Today we take a look at Panasonic's new 50 inch 720p plasma, the <a href="http://www2.panasonic.com/webapp/wcs/stores/servlet/vModelDetail?storeId=15001&catalogId=13401&itemId=112104&catGroupId=24973&modelNo=TH-50PX75U&surfModel=TH-50PX75U&cacheProgram=11002&cachePartner=7000000000000005702">TH-50PX75U</a>, which comes with a street price of $2100 (<a href="http://www.htguys.com/shop.php?id=B000O5TFRK">Buy Now</a>) and addresses the three nit picks we had with the earlier model. This is a 720p TV with a resolution of 1366 x 768, a 10,000:1 contrast ratio, two HDMI inputs, and an SD card reader. One of the issues we (mainly Ara) had with its predecessor was its silver bezel. The 50PX75U comes with a thin black bezel that hides the speakers very well and looks quite good even when its off. Rounding out the features, the TV come with built-in ATSC/QAM/NTSC Tuners. Build quality is quite good. The product dimensions are 47.7 x 3.8 x 31.3 inches and it weighs 89.8 pounds which is almost identical to the previous model.</p>

<p>We were very pleased with the 60U's performance and likewise we are pleased with the 75U. The TV does a great job with bright HD content. Colors are accurate and skins tones look natural. We found that dark scenes were not a problem for this TV and it we were able to make out detail even with the most demanding material. LCDs are making great strides with black but it is still difficult to beat a plasma. The television produced very black blacks.  So far this sounds pretty much like the review of the 60U TV. Well we did have two issues with the picture performance of the 60U that were addressed with this TV.</p>

<p>In our last review we were not pleased with how over compressed 1080i HD looked. In our case we are talking about HD on NBC from our local affiliate. They really compress the main HD channel. We have seen data rates as low as 12Mbps. The 75U does a much better job at dealing with this. For that reason we recommend leaving the MPEG NR on. Most reviewers suggest leaving it off. If you watch allot of HD from overly compressed sources (OTA, Cable, or Satellite) we recommend engaging this feature (See our calibration settings at the end of this review). It made a difference in our opinion. We also felt the TV did a good job reducing noise that our other TVs had a difficult time with. We recommend leaving the the Video NR feature on as well. If you have good clean content that is at a data rate near 18Mbps for 1080i or you are watching HD DVD or Blu Ray turn these two settings off. It will actually make the picture worse.</p>

<p>The other area we saw an improvement is in SD viewing. While it is not perfect it is definitely watchable. We used an over the air source for this assessment. We felt the TV was producing pictures that were close to what our old analog TVs were capable of. But to be honest with you, its been a while since we've seen analog TV on an analog screen so don't sue us if you disagree. But we definitely didn't say that looks terrible.</p>

<p>The following is taken from our January 5th review verbatim. It still applies:<br />
Menus are very clean and simple to navigate. The remote is basic and we recommend replacing it with a universal remote (but when have we not said that). The TV has a digital output that makes it possible to send the Dolby Digital audio track received over the air via the ATSC tuner to your Home Theater Receiver. The speakers as we mentioned before are underneath the screen and sound OK. The TV has a virtual surround mode that we found kind of useless. The audio was clear and easy to hear though.</p>

<p>The TV also supports Pansonic's proprietary EZ Sync control functionality that can be used with other Pansonic gear.<br />
<strong><br />
Conclusion:</strong><br />
The TH-50PX75U is a worthy follow on to the successful 60U line of plasma televisions. The new black color with thin bezel are welcome changes. Issues we found with the 60U have been addressed making this TV a great buy at $2100. If you can live with the issues of the 60U you can save about $315 and get one for $1750 (<a href="http://www.htguys.com/shop.php?id=B000F4CTUK">Buy Now</a>).</p>

<p><strong>Calibration Settings</strong> (<em>We did not get to spend enough time to dial these settings in. Please use them as starting points</em>)<br />
Normal: No<br />
Picture mode: Custom<br />
Picture: +20<br />
Brightness: +10<br />
Color: 0<br />
Tint: -2<br />
Sharpness: -10<br />
Color Temperature: Warm<br />
Color Management: Off<br />
Video NR: off<br />
3D Y/C filter: Off<br />
Color matrix: HD<br />
MPEG NR: On<br />
Black Level: Light</p>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>May 22, 2007 07:00 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 602
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
 			<h2>More on HDTV Displays</h2><ul><?
 			$sql = "
 			SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name
 			FROM mt_entry e, mt_author a, mt_placement p, mt_category c
 			WHERE entry_author_id = author_id
 				AND e.entry_id = p.placement_entry_id
 				AND p.placement_category_id = c.category_id
 				AND category_label = 'HDTV Displays'
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
			
 		<?if (8 <> 7) {
 			# Recent Articles by Author (exclude this one)
 			# Do not show recent articles for Bulletins.
 			$qry = "
 			SELECT entry_id, entry_blog_id, entry_created_on, entry_title, author_id, author_name
 			FROM mt_entry e, mt_author a
 			WHERE entry_blog_id = 8
 				AND entry_id <> 602
 				AND entry_status = 2
 				AND entry_author_id = a.author_id
 				AND a.author_name = 'The HT Guys'
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
 				<h2>About The HT Guys</h2>
 				<?=stripslashes($author[bio_short])?>
 			<span class="corners-bottom"><span></span></span></div>
 		<?}?>
		</td><td id="right">

 		<?if ($about != '') {?>
 			<div class="item"><span class="corners-top"><span></span></span>
 				<h2>About Reviews</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/reviews/2007/05/panasonic_th-50px75u_50_inch_plasma_tv.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
