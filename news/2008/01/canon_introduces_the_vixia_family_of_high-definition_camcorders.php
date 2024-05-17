<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 883";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 883 AND placement_is_primary = 1";
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
	<meta name="keywords" content="flash memory, dual flash, memory camcorders, memory card, high definition, canon, Canon, memory, Flash, flash, vixia, VIXIA, Memory, camcorder, camcorders, dual, Dual, video, image, consumers, DVD, dvd, card, high, consumer" />
	<meta name="description" content="Canon U.S.A., Inc. proudly announces the VIXIA family - a new lineup of consumer High-Definition camcorders embracing Canon optical and imaging technologies for superior image quality and flexibility - at the 2008 International Consumer Electronics Show in Las Vegas (Booth #12606).

The new HD camcorder family - the Canon VIXIA HF10 Dual Flash Memory camcorder, VIXIA HF100 Flash Memory camcorder and VIXIA HV30 HD camcorder - reflects Canon's commitment to High-Definition imaging excellence. In addition..." />
	<title>HDTV Magazine Bulletins - Canon Introduces the VIXIA Family of High-Definition Camcorders</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/canon_introduces_the_vixia_family_of_high-definition_camcorders';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Canon Introduces the VIXIA Family of High-Definition Camcorders'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2008/01/canon_introduces_the_vixia_family_of_high-definition_camcorders.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Canon Introduces the VIXIA Family of High-Definition Camcorders</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>January  7, 2008</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Products & Equipment">Products & Equipment</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2008/01/canon_introduces_the_vixia_family_of_high-definition_camcorders.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2008/01/canon_introduces_the_vixia_family_of_high-definition_camcorders.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2008/01/canon_introduces_the_vixia_family_of_high-definition_camcorders.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2008/01/canon_introduces_the_vixia_family_of_high-definition_camcorders.php&amp;phase=2&amp;title=Canon%20Introduces%20the%20VIXIA%20Family%20of%20High-Definition%20Camcorders&amp;bodytext=Canon%20U.S.A.%2C%20Inc.%20proudly%20announces%20the%20VIXIA%20family%20-%20a%20new%20lineup%20of%20consumer%20High-Definition%20camcorders%20embracing%20Canon%20optical%20and%20imaging%20technologies%20for%20superior%20image%20quality%20and%20flexibility%20-%20at%20the%202008%20International%20Consumer%20Electronics%20Show%20in%20Las%20Vegas%20%28Booth%20%2312606%29.%0A%0AThe%20new%20HD%20camcorder%20family%20-%20the%20Canon%20VIXIA%20HF10%20Dual%20Flash%20Memory%20camcorder%2C%20VIXIA%20HF100%20Flash%20Memory%20camcorder%20and%20VIXIA%20HV30%20HD%20camcorder%20-%20reflects%20Canon%27s%20commitment%20to%20High-Definition%20imaging%20excellence.%20In%20addition...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p class="prtitle">Canon U.S.A. Introduces the VIXIA Family of High-Definition Camcorders for the Ultimate Consumer Experience</p>

<center><i>Models Include Breakthrough Use of Dual Flash Memory, Genuine Canon Optics, And Other Proprietary Technologies, Expanding Consumers' Recording Options</i></center><br />
<br />

<p>2008 International CES</p>

<p><B>LAKE SUCCESS, N.Y.--(BUSINESS WIRE)</B>--Canon U.S.A., Inc. proudly announces the VIXIA family - a new lineup of consumer High-Definition camcorders embracing Canon optical and imaging technologies for superior image quality and flexibility - at the 2008 International Consumer Electronics Show in Las Vegas (Booth #12606).</p>

<p>The new HD camcorder family - the Canon VIXIA HF10 Dual Flash Memory camcorder, VIXIA HF100 Flash Memory camcorder and VIXIA HV30 HD camcorder - reflects Canon's commitment to High-Definition imaging excellence. In addition, the previously released HG10 AVCHD Hard Disk Drive camcorder and HR10 AVCHD DVD camcorder join the VIXIA family, giving consumers a variety of formats to choose from, all of which deliver a superior High-Definition experience. Also being introduced is the DW-100 DVD Burner*.</p>

<p>"We are very excited about our new VIXIA family of camcorders, as well as being an innovator by offering Dual Flash Memory," said Yuichi Ishizuka, senior vice president and general manager, Consumer Imaging Group, Canon U.S.A. "Consumers are actively investing in HD televisions and they're discovering the value of capturing memories in HD. Whichever format consumers may prefer, including our revolutionary Dual Flash Memory, all VIXIA camcorders share Genuine Canon Optics and a host of Canon technologies allowing precious moments to be preserved with unrivaled color and clarity."</p>

<p><br />
<B>VIXIA Core Technologies</B></p>

<p>All VIXIA camcorders feature Canon core technologies to create HD video that possesses the highest level of image quality - a Genuine Canon HD Video Lens incorporates over 70 years of optics experience in professional broadcast and photography; a Canon designed and manufactured HD CMOS Image Sensor for Full HD (1920 x 1080) image capture; the Canon-developed DIGIC DV II Image Processor for superior color and clarity; Instant AutoFocus for fast and accurate auto focusing, crucial for HD; and SuperRange Optical Image Stabilization, which corrects a wide range of camcorder vibration for virtually shake-free images.</p>

<p><br />
<B>Dual Flash Memory - The Ultimate Consumer Convenience</B></p>

<p>Canon's breakthrough use of Dual Flash Memory - the ability to record to an internal Flash drive as well as a removable SDHC memory card - allows consumers to experience a new level of performance, style and flexibility. Dual Flash Memory allows consumers to record video to the camcorder's internal Flash drive even if they do not have a memory card. When the internal Flash drive becomes full, footage can be easily transferred to an SDHC memory card and when it comes time to view their video, the card is simply placed into a memory card reader in a computer or HDTV for instant viewing. Furthermore, having a SDHC memory card slot allows for expandability, since greater capacity can be added in the future by purchasing additional cards.</p>

<p>Flash Memory boasts a number of advantages and end-user benefits for maximum convenience and flexibility. Since Flash Memory is a solid-state memory format and has no moving parts, the camcorder can be smaller, more compact and lighter than ever before, allowing it to be carried anywhere. Additionally, Flash Memory is a highly stable method of storage, and as a result, accidental jolts to the camcorder are significantly less likely to result in failure or data loss. Consumers will also enjoy the camcorder's low power consumption, which leads to longer battery time. Compared with other types of storage, Flash Memory camcorders are able to read and write data faster, so users can start recording faster and have immediate access to their recorded scenes.</p>

<p><br />
<B>VIXIA HF10 Dual Flash Memory and VIXIA HF100 Flash Memory Camcorders</B></p>

<p>Despite their compact size, the VIXIA HF10 Dual Flash Memory and HF100 Flash Memory camcorders are packed with advanced technology and a wealth of features to create stunning quality video. The VIXIA HF10 Dual Flash Memory camcorder offers the flexibility of recording up to 6 hours of High-Definition video to a 16GB internal Flash drive, as well as the option of recording to an SDHC memory card. The HF100 Flash Memory camcorder features an SDHC memory card slot only. The SDHC slot provides future storage expandability with both models. These camcorders also offer other sophisticated new features, including a newly designed Genuine Canon 12x HD Video Lens, a robust Canon 3.3 Megapixel Full HD CMOS Image Sensor, and Full HD Lens-to-Screen (1920 x 1080 Full HD resolution to capture, record and output).</p>

<p>In addition to 24p Cinema Mode, which allows users to mimic the look of Hollywood-style movies, the VIXIA HF10 Dual Flash Memory and HF100 Flash Memory camcorders offer a new feature called 30p Progressive Mode. Canon's 30p Progressive Mode, once exclusive to pro-level camcorders, delivers clarity for fast action events, such as sports or news, and is the perfect frame rate for clips intended to be posted on the Web. A 2.7" Widescreen Multi-Angle Vivid LCD offers a wide viewing angle, making it visible from any direction. It also offers an expanded color range to more accurately reflect what users will see later on their HDTV. The models use an Intelligent Lithium-ion Battery, which indicate the remaining battery time down to the minute. Furthermore, the VIXIA HF10 Dual Flash Memory and HF100 Flash Memory camcorders offer a newly designed Mini Advanced Accessory Shoe, providing cable-free connectivity to an optional Canon microphone or video light. A microphone terminal with manual level control delivers additional audio flexibility and a fully functional 3.1 Megapixel digital camera is built right in, allowing consumers to capture high-quality still images with a wide selection of Advanced Photo features.</p>

<p><br />
<B>VIXIA HV30 HD Camcorder</B></p>

<p>As the successor to the highly acclaimed, award-winning Canon HV20 HD Camcorder, the VIXIA HV30 HD camcorder provides consumers with the ability to record HD quality video to MiniDV cassettes. Wrapped in a sophisticated black exterior, the VIXIA HV30 camcorder features a Genuine Canon 10X HD Video Lens, Canon 2.96 Megapixel Full HD CMOS Image Sensor, DIGIC DV II Image Processor, a 30p Progressive Mode (and 24p Cinema Mode), and a 2.7" Widescreen Multi-Angle Vivid LCD. In addition, the VIXIA HV30 camcorder is compatible with Canon's high capacity BP-2L24H Lithium-ion battery.</p>

<p><br />
<B>DW-100 DVD Burner*</B></p>

<p>The Canon DW-100 DVD Burner is the perfect companion for Canon Flash Memory camcorders and Canon Hard Disk Drive camcorders. It allows a consumer to burn all, part or previously recorded video from a compatible Canon camcorder to a DVD. In addition to burning Standard Definition DVDs, the DW-100 can also burn AVCHD DVDs which can be played in compatible Blu-Ray players. The DW-100 DVD Burner has only three buttons: power, record and eject, making operation fast and easy. Unlike other similar yet daunting devices, the DW-100 DVD Burner is designed for one-touch operation: connect via USB, set and burn. The DW-100 can also act as a player itself by connecting to an HDTV through the Canon VIXIA HF10, VIXIA HF100 or VIXIA HG10 camcorders.</p>

<p>Available in late April, the VIXIA HF10 Dual Flash Memory and VIXIA HF100 Flash Memory camcorders will have an estimated retail price of $1,099 and $899, respectively.** The Canon VIXIA HV30 is scheduled to be available in late February for the estimated retail price of $999.** The Canon DW-100 DVD Burner is scheduled to be available in late April for the estimated retail price of $269.**</p>

<p><br />
<B>About Canon U.S.A., Inc.</B></p>

<p>Canon U.S.A., Inc. delivers consumer, business-to-business, and industrial imaging solutions. Its parent company, Canon Inc. (NYSE:CAJ), a top patent holder of technology, ranking third overall in the U.S. in 2006†, with global revenues of $34.9 billion, is listed as one of Fortune's Most Admired Companies in America and is on the 2007 BusinessWeek list of "Top 100 Brands." To keep apprised of the latest news from Canon U.S.A., sign up for the Company's RSS news feed by visiting www.usa.canon.com/pressroom.</p>

<p>All referenced product names, and other marks, are trademarks of their respective owners.</p>

<p>Prices, availability and specifications are subject to change without notice.</p>

<p>* The DW-100 has not been authorized as required by the rules of the Federal Communications Commission or tested for compliance with the U.S. Federal Performance Standard For Laser Products as mandated by 21CFR; as such, this device is not, and may not be, offered for sale or lease, or sold or leased in the United States until such authorization is obtained.</p>

<p>**All prices are estimated retail prices. Actual prices are determined by individual dealers and may vary.</p>

<p>IFI Patent Intelligence, January 11, 2007</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>January  7, 2008 07:47 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 883
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
 				AND entry_id <> 883
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2008/01/canon_introduces_the_vixia_family_of_high-definition_camcorders.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
