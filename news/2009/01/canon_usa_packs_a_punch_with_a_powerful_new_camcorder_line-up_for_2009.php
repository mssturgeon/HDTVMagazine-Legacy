<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');

	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1601";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);

	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";

	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1601 AND placement_is_primary = 1";
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
	<meta name="keywords" content="flash memory, memory camcorders, high definition, genuine canon, definition camcorders, canon, Canon, VIXIA, vixia, camcorders, image, camcorder, memory, video, new, Flash, flash, definition, advanced, high, Image, consumers, features, Memory, recording" />
	<meta name="description" content="Canon U.S.A., Inc, a leader in digital imaging technology, announces an exciting new line of five VIXIA high definition and six standard definition camcorders, which are available in a variety of different recording formats, including Flash Memory. The camcorders retain Canon's core imaging technologies, but add a wide selection of new features for enhanced image quality and added flexibility for sharing and storing memories.

Highlighting the list of new features is Canon's newest and most sophisticated image processor, DIGIC DV III. The new HD processor is featured in select VIXIA models and delivers..." />
	<title>HDTV Magazine Bulletins - Canon U.S.A. Packs a Punch with a Powerful New Camcorder Line-up for 2009</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
//		var federated_media_section = 'holiday';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/ads/leaderboard.php');
		include(BASE_DIR .'/includes/body_header.php');

		$base_url = strleftback(PHP_SELF, '/') . '/canon_usa_packs_a_punch_with_a_powerful_new_camcorder_line-up_for_2009';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Canon U.S.A. Packs a Punch with a Powerful New Camcorder Line-up for 2009'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2009/01/canon_usa_packs_a_punch_with_a_powerful_new_camcorder_line-up_for_2009.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Canon U.S.A. Packs a Punch with a Powerful New Camcorder Line-up for 2009</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>January  5, 2009</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=HD Camcorders & Cameras">HD Camcorders & Cameras</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2009/01/canon_usa_packs_a_punch_with_a_powerful_new_camcorder_line-up_for_2009.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2009/01/canon_usa_packs_a_punch_with_a_powerful_new_camcorder_line-up_for_2009.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2009/01/canon_usa_packs_a_punch_with_a_powerful_new_camcorder_line-up_for_2009.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2009/01/canon_usa_packs_a_punch_with_a_powerful_new_camcorder_line-up_for_2009.php&amp;phase=2&amp;title=Canon%20U.S.A.%20Packs%20a%20Punch%20with%20a%20Powerful%20New%20Camcorder%20Line-up%20for%202009&amp;bodytext=Canon%20U.S.A.%2C%20Inc%2C%20a%20leader%20in%20digital%20imaging%20technology%2C%20announces%20an%20exciting%20new%20line%20of%20five%20VIXIA%20high%20definition%20and%20six%20standard%20definition%20camcorders%2C%20which%20are%20available%20in%20a%20variety%20of%20different%20recording%20formats%2C%20including%20Flash%20Memory.%20The%20camcorders%20retain%20Canon%27s%20core%20imaging%20technologies%2C%20but%20add%20a%20wide%20selection%20of%20new%20features%20for%20enhanced%20image%20quality%20and%20added%20flexibility%20for%20sharing%20and%20storing%20memories.%0A%0AHighlighting%20the%20list%20of%20new%20features%20is%20Canon%27s%20newest%20and%20most%20sophisticated%20image%20processor%2C%20DIGIC%20DV%20III.%20The%20new%20HD%20processor%20is%20featured%20in%20select%20VIXIA%20models%20and%20delivers...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p class="prtitle">Canon U.S.A. Packs a Punch with a Powerful New Camcorder Line-up for 2009</p>

<center><i>New Camcorders Offer Advanced Technology for Higher Image Quality and Easy Operation Across a Variety of Recording Formats</i></center><br />
<br />

<p><B>LAKE SUCCESS, N.Y.--(BUSINESS WIRE)</B>--Canon U.S.A., Inc, a leader in digital imaging technology, announces an exciting new line of five VIXIA high definition and six standard definition camcorders, which are available in a variety of different recording formats, including Flash Memory. The camcorders retain Canon's core imaging technologies, but add a wide selection of new features for enhanced image quality and added flexibility for sharing and storing memories.</p>

<p>Highlighting the list of new features is Canon's newest and most sophisticated image processor, DIGIC DV III. The new HD processor is featured in select VIXIA models and delivers stunning color reproduction, clarity and enhanced noise reduction. The newly upgraded processor's high-speed engine powers a variety of other new camcorder features including: 8.0 Megapixel photo capture, Genuine Canon Face Detection Technology, and an advanced Auto Exposure system.</p>

<p>Also new to Canon's video line-up is Video Snapshot Mode, which enables users to capture the highlights of a once in a lifetime trip, or a family milestone, with the same ease as taking photos. Consumers can now record a series of four-second video clips, and along with supplied software which includes various background music compositions, blend in background music to create an exciting movie that will hold everyone's attention.</p>

<p>"Canon's latest camcorder lineup features an exciting new array of advanced technologies that deliver superb image quality and easy operation," said Yuichi Ishizuka, senior vice president and general manager, Consumer Imaging Group, Canon U.S.A. "These new camcorders are available in a variety of recording formats, providing consumers a camcorder choice that complements any lifestyle or situation."</p>

<p><br />
<B>VIXIA High Definition Camcorders:</B></p>

<p>All VIXIA camcorders feature Canon's trinity of core technologies that create the highest level of high definition image quality - a Genuine Canon HD Video Lens; Canon designed and manufactured HD CMOS Image Sensor for Full HD image capture; and Canon-developed DIGIC DV II and DIGIC DV III Image Processors. Additional features found on select VIXIA models include Instant AutoFocus, SuperRange Optical Image Stabilization and 24Mbps Recording - the highest bit rate in AVCHD.</p>

<p>The same high quality Genuine Canon Face Detection Technology used in Canon digital cameras is now available in Canon VIXIA high definition camcorders. Up to 35 faces can be detected automatically, and nine detection frames can be displayed at one time. The system is so intelligent that it will even recognize faces that are turned down or sideways. Consumers can select a face they would like the camcorder to continuously track. While in playback, consumers can access specific scenes based on chosen faces.</p>

<p><br />
<B>Canon VIXIA HF S10 and VIXIA HF S100 Flash Memory Camcorders</B></p>

<p>Canon's top-of-the-line high definition Flash Memory camcorders, the Canon VIXIA HF S10 and VIXIA HF S100, boast an impressive range of new and advanced features. The VIXIA HF S10 offers the option of recording video to a 32GB internal Flash drive or directly to an SDHC memory card, while the VIXIA HF S100 records to an SDHC memory card only. Both models feature the new DIGIC DV III Image Processor, an 8.59 Megapixel Full HD CMOS Image Sensor, Genuine Canon Face Detection Technology, an advanced Auto Exposure system and Video Snapshot and Dual Shot Modes. In addition, both models deliver stunning 8.0 Megapixel digital photographs.</p>

<p><br />
<B>Canon VIXIA HF20 and VIXIA HF200 Flash Memory Camcorders</B></p>

<p>Canon's most compact high definition Flash Memory camcorders, the VIXIA HF20 and VIXIA HF200 are powerhouse options for anyone looking to take their HD camcorder with them wherever they go. The VIXIA HF20 offers the option of recording to a 32GB internal Flash drive or SDHC card slot and the VIXIA HF200 records to an SDHC memory card only. Additional features include a 3.89 Megapixel Full HD CMOS Image Sensor, newly designed Genuine Canon 15x HD Video Lens, advanced Auto Exposure system, and Video Snapshot and Dual Shot Modes.</p>

<p><br />
<B>Canon VIXIA HV40 HDV Camcorder</B></p>

<p>The Canon VIXIA HV40 HDV Camcorder, a replacement to the highly acclaimed VIXIA HV30 camcorder, shares the core components found within the VIXIA line, but also offers a Genuine Canon 10x HD Video Lens and 2.96 Megapixel Full HD CMOS Image Sensor. What's more, the camcorder allows consumers to record in native 24p Mode, a feature previously found only on Canon's professional camcorders. Native 24p allows consumer to capture and record 24 progressive frames per second to a HDV tape, a big advantage for the serious filmmaker. Another add-on feature, Custom Key Mode, enables consumers to assign commonly used functions to a single button on the camcorder for easy access.</p>

<p><br />
<B>Standard Definition Camcorders:</B></p>

<p>Standard definition camcorders offer consumers the ability to capture and watch high quality video, even if they do not own a high definition television at home. All Canon standard definition camcorders come fully equipped with Canon's core expertise in optics and image processing.</p>

<p><br />
<B>Canon FS22, FS21 and FS200 Flash Memory Camcorders</B></p>

<p>The Canon FS22, FS21 and FS200 Flash Memory camcorders are ultra-sleek and compact - up to 17 percent smaller than previous FS series models. The FS22 and FS21 Dual Flash Memory camcorders incorporate 32GB and 16GB of internal Flash memory, respectively and can record video directly to an SDHC memory card. Additionally, these two models feature Genuine Canon 48x Advanced Zoom, which is great for capturing sideline action from the bleachers. The FS200 Flash Memory camcorder records video directly to an SDHC memory card and comes in three fashionable colors - Misty Silver, Sunrise Red and Evening Blue.</p>

<p><br />
<B>Canon DC420 and DC410 DVD Camcorders</B></p>

<p>The DC420 and DC410 DVD camcorders are perfect for consumers who want the convenience of recording their memories directly to DVD. The DC420 offers 48x Advanced Zoom, while the DC410 offers 41x Advanced Zoom. Both feature a DIGIC DV II Image Processor and Widescreen Recording, as well as the flexibility of optional add-on features, such as filters and lens accessories, to help achieve a designed look.</p>

<p><br />
<B>Canon ZR960 MiniDV Camcorder</B></p>

<p>For consumers who wish to record video to MiniDV, the ZR960 MiniDV camcorder is perfect. This easy-to-use option is a beginner's go-to product. While still incorporating Canon's core technologies and optics, this model provides 41x Advanced Zoom, great for capturing far-away shots, as well as a microphone terminal for better audio control. Additionally, the flexibility of add-on features, such as filters and lens accessories, help to achieve a designed look.</p>

<p><br />
<B>About Canon U.S.A., Inc.</B></p>

<p>Canon U.S.A., Inc. delivers consumer, business-to-business, and industrial imaging solutions. Its parent company, Canon Inc. (NYSE:CAJ), a top patent holder of technology, ranked third overall in the U.S. in 2007+, with global revenues of $39.3 billion, is listed as one of Fortune's Most Admired Companies in America and is on the 2007 BusinessWeek list of "Top 100 Brands." To keep apprised of the latest news from Canon U.S.A., sign up for the Company's RSS news feed by visiting www.usa.canon.com/pressroom.</p>

<p>+IFI Patent Intelligence Press Release, January 2008</p>

<p>Prices, specifications and availability subject to change without notice. Actual prices set by individual dealers and may vary.</p>

<p>All referenced product names, and other marks, are trademarks of their respective owners. </p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>January  5, 2009 07:04 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 1601
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
 			<h2>More on HD Camcorders & Cameras</h2><ul><?
 			$sql = "
 			SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name
 			FROM mt_entry e, mt_author a, mt_placement p, mt_category c
 			WHERE entry_author_id = author_id
 				AND e.entry_id = p.placement_entry_id
 				AND p.placement_category_id = c.category_id
 				AND category_label = 'HD Camcorders & Cameras'
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
 				AND entry_id <> 1601
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2009/01/canon_usa_packs_a_punch_with_a_powerful_new_camcorder_line-up_for_2009.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
