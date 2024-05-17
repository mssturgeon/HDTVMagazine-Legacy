<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1494";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1494 AND placement_is_primary = 1";
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
	<meta name="keywords" content="slingbox pro, sling media, slingplayer windows, capable streaming, laptop desktop, slingbox, Slingbox, pro, PRO, Sling, sling, Media, media, video, digital, windows, Windows, home, both, including, SlingPlayer, slingplayer, product, available, features" />
	<meta name="description" content="Sling Media, Inc., a leading digital lifestyle products company, today announced its next generation Slingbox&amp;trade; PRO-HD is now available for purchase from www.slingmedia.com and leading online and brick and mortar retailers nationwide for $299.99. Sling Media is also pleased to announce the Slingbox PRO-HD will be available at leading Canadian retailers for $329.99 in the coming weeks. The Slingbox PRO-HD is a revolutionary new product that is capable of streaming HD content from a home television source, including over the air HD digital signals (ATSC), digital cable channels (Clear QAM), HDTV cable set-top boxes, HDTV satellite receivers, or HD DVR's, to a laptop or desktop computer in and around the house. Additionally, customers who have a high speed broadband connection that features upload speeds of 1.5 Megabits per second or higher, Slingbox PRO-HD can even stream HD TV signals outside the home to just about anywhere you can receive a high-speed network connection.

With the availability of the Slingbox PRO-HD, Sling Media has also updated its..." />
	<title>HDTV Magazine Bulletins - Sling Media Begins Shipping Slingbox PRO-HD</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/sling_media_begins_shipping_slingbox_pro-hd';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Sling Media Begins Shipping Slingbox PRO-HD'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2008/09/sling_media_begins_shipping_slingbox_pro-hd.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Sling Media Begins Shipping Slingbox PRO-HD</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>September 25, 2008</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Products & Equipment">Products & Equipment</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2008/09/sling_media_begins_shipping_slingbox_pro-hd.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2008/09/sling_media_begins_shipping_slingbox_pro-hd.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2008/09/sling_media_begins_shipping_slingbox_pro-hd.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2008/09/sling_media_begins_shipping_slingbox_pro-hd.php&amp;phase=2&amp;title=Sling%20Media%20Begins%20Shipping%20Slingbox%20PRO-HD&amp;bodytext=Sling%20Media%2C%20Inc.%2C%20a%20leading%20digital%20lifestyle%20products%20company%2C%20today%20announced%20its%20next%20generation%20Slingbox%26trade%3B%20PRO-HD%20is%20now%20available%20for%20purchase%20from%20www.slingmedia.com%20and%20leading%20online%20and%20brick%20and%20mortar%20retailers%20nationwide%20for%20%24299.99.%20Sling%20Media%20is%20also%20pleased%20to%20announce%20the%20Slingbox%20PRO-HD%20will%20be%20available%20at%20leading%20Canadian%20retailers%20for%20%24329.99%20in%20the%20coming%20weeks.%20The%20Slingbox%20PRO-HD%20is%20a%20revolutionary%20new%20product%20that%20is%20capable%20of%20streaming%20HD%20content%20from%20a%20home%20television%20source%2C%20including%20over%20the%20air%20HD%20digital%20signals%20%28ATSC%29%2C%20digital%20cable%20channels%20%28Clear%20QAM%29%2C%20HDTV%20cable%20set-top%20boxes%2C%20HDTV%20satellite%20receivers%2C%20or%20HD%20DVR%27s%2C%20to%20a%20laptop%20or%20desktop%20computer%20in%20and%20around%20the%20house.%20Additionally%2C%20customers%20who%20have%20a%20high%20speed%20broadband%20connection%20that%20features%20upload%20speeds%20of%201.5%20Megabits%20per%20second%20or%20higher%2C%20Slingbox%20PRO-HD%20can%20even%20stream%20HD%20TV%20signals%20outside%20the%20home%20to%20just%20about%20anywhere%20you%20can%20receive%20a%20high-speed%20network%20connection.%0A%0AWith%20the%20availability%20of%20the%20Slingbox%20PRO-HD%2C%20Sling%20Media%20has%20also%20updated%20its...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p class="prtitle">Sling Media Begins Shipping Slingbox PRO-HD</p>

<center><i>First of Its Kind Slingbox Capable of Streaming Both SDTV and HDTV; Now Available From Sling Media and Its Retail Partners Nationwide and Coming Soon to Canada.</i></center><br />
<br />

<p><B>FOSTER CITY, Calif.--(BUSINESS WIRE)</B>--Sling Media, Inc., a leading digital lifestyle products company, today announced its next generation Slingbox&trade; PRO-HD is now available for purchase from www.slingmedia.com and leading online and brick and mortar retailers nationwide for $299.99. Sling Media is also pleased to announce the Slingbox PRO-HD will be available at leading Canadian retailers for $329.99 in the coming weeks. The Slingbox PRO-HD is a revolutionary new product that is capable of streaming HD content from a home television source, including over the air HD digital signals (ATSC), digital cable channels (Clear QAM), HDTV cable set-top boxes, HDTV satellite receivers, or HD DVR's, to a laptop or desktop computer in and around the house. Additionally, customers who have a high speed broadband connection that features upload speeds of 1.5 Megabits per second or higher, Slingbox PRO-HD can even stream HD TV signals outside the home to just about anywhere you can receive a high-speed network connection.</p>

<p>With the availability of the Slingbox PRO-HD, Sling Media has also updated its SlingPlayer for Windows software for PCs running Windows XP or Windows Vista. SlingPlayer 2.0 for Windows is now available for download from Sling Media's web site, www.slingmedia.com. SlingPlayer 2.0 for Windows features a live video buffer built into the software, allowing the viewer to pause and rewind a live video stream for easy navigation within a program, and avoiding potential conflicts with home viewers. Also included is an electronic programming guide (EPG) which slides out of the SlingPlayer and gives users a fast and intuitive way to see what is on their TV and navigate to it without having to bring up an on-screen guide on their home set-top box. Finally, SlingPlayer 2.0 for Windows incorporates Sling Accounts, a single sign-on feature that keeps key personal Slingbox information including Finder IDs, favorite channels and programming guide information, making them all accessible on any PC running SlingPlayer 2.0 for Windows software.</p>

<p>"With Slingbox PRO-HD, our customers will have the ability to place-shift their HD programming to a laptop or desktop computer both in and around the home or remotely while preserving the best possible picture quality," said Blake Krikorian, co-founder and CEO of Sling Media. "Slingbox PRO-HD marks a giant leap forward in hardware and software development from Sling Media including the powerful encoding enhancements that are a part of SlingStream 2.0. Because of this, Slingbox PRO-HD delivers picture quality at all resolutions that is truly amazing."</p>

<p>Slingbox PRO-HD features SlingStream 2.0, a major improvement to the company's proprietary SlingStream&trade; technology. SlingStream 2.0 allows the Slingbox PRO-HD to adaptively stream high quality television content across virtually any network connection. With SlingStream 2.0, audio/video quality is noticeably improved for remote clients operating in both high and low bitrate environments, including high definition players on a home network.</p>

<p>The Slingbox PRO-HD replaces the Slingbox PRO at the top-end of Sling Media's Slingbox family and sets itself apart as it is the first and only Slingbox product that is capable of streaming video content at HD resolutions and delivers it to a HD-compatible laptop or desktop computer. The Slingbox PRO and Slingbox SOLO both down-convert HDTV sources to SDTV resolutions.</p>

<p>Slingbox PRO-HD is designed to meet the varied requirements of today's multi-faceted TV-viewing households. The product includes support for both standard (4:3) and widescreen (16:9) video formats and features multiple integrated sets of audio-video inputs and outputs (Tuner support for HD Digital Antenna (ATSC), Analog and/or HD Digital Cable (Clear QAM) channels, S-Video, composite video, component video and both analog and multi-channel, 5.1 surround, digital audio (S/PDIF)) and features integrated looping outputs for each input. Slingbox PRO-HD transforms desktop PCs, laptop PCs, Macs and a wide range of smartphones into personal, portable TVs and builds upon the goals of the original Slingbox - to give consumers the freedom to view their home cable, satellite or DVR programming on a wide range of devices anywhere they can access the Internet.</p>

<p>Sling Media's pre-order partners, Amazon.com, Buy.com, J&R, Newegg.com, PC Mall.com and Sling Media's own online store, began shipping existing pre-orders to customers today and are now taking new orders for immediate delivery. Product will start rolling into retail stores nationwide including Best Buy, Fry's, J&R, Microcenter and others in the coming weeks. In Canada, Slingbox PRO-HD will be available from retailers including Best Buy, Future Shop and London Drugs in October.</p>

<p>About Sling Media</p>

<p>Sling Media, Inc., a wholly owned subsidiary of EchoStar Corporation (NASDAQ:SATS), is a leading digital lifestyle products company offering consumer services and products that are a natural extension of today's digital way of life. Sling Media's product family includes the internationally acclaimed, Emmy award-winning Slingbox&trade; that allows consumers to watch and control their living room television shows at any time, from any location, using PCs, Macs, PDAs, and smartphones. For more information on Sling Media or the Slingbox, visit www.slingmedia.com. </p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>September 25, 2008 07:51 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 1494
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
 				AND entry_id <> 1494
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2008/09/sling_media_begins_shipping_slingbox_pro-hd.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
