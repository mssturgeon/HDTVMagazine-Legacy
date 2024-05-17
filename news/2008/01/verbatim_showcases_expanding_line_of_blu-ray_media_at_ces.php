<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 891";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 891 AND placement_is_primary = 1";
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
	<meta name="keywords" content="blu ray, lth type, recording layer, type media, line blu, verbatim, Verbatim, media, recording, disc, layer, discs, Blu, blu, ray, type, lth, new, LTH, production, video, MKM, mkm, DVD, surface" />
	<meta name="description" content="Verbatim&amp;reg; Americas, LLC announced today that it has extended its support for Blu-ray Disc (BD) technology and will feature its expanded line of Blu-ray media at Booth #S4-36249 in the South Hall during CES this week. In addition to the 2x BD recordable (BD-R) and BD rewritable (BD-RE) media which are available now, Verbatim will showcase six new BD products scheduled to begin shipping in the first half of 2008. The new products include 4x BD-R discs, Single-sided Double-layer (DL) 2x BD-R and BD-RE discs, Mini BD-R and BD-RE discs and 1-2x BD-R LTH TYPE discs.

The market for BD blank media is being driven by the growing demand for..." />
	<title>HDTV Magazine Bulletins - Verbatim Showcases Expanding Line of Blu-Ray Media at CES</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/verbatim_showcases_expanding_line_of_blu-ray_media_at_ces';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Verbatim Showcases Expanding Line of Blu-Ray Media at CES'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2008/01/verbatim_showcases_expanding_line_of_blu-ray_media_at_ces.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Verbatim Showcases Expanding Line of Blu-Ray Media at CES</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>January  8, 2008</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=HD DVD & Blu-ray">HD DVD & Blu-ray</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2008/01/verbatim_showcases_expanding_line_of_blu-ray_media_at_ces.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2008/01/verbatim_showcases_expanding_line_of_blu-ray_media_at_ces.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2008/01/verbatim_showcases_expanding_line_of_blu-ray_media_at_ces.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2008/01/verbatim_showcases_expanding_line_of_blu-ray_media_at_ces.php&amp;phase=2&amp;title=Verbatim%20Showcases%20Expanding%20Line%20of%20Blu-Ray%20Media%20at%20CES&amp;bodytext=Verbatim%26reg%3B%20Americas%2C%20LLC%20announced%20today%20that%20it%20has%20extended%20its%20support%20for%20Blu-ray%20Disc%20%28BD%29%20technology%20and%20will%20feature%20its%20expanded%20line%20of%20Blu-ray%20media%20at%20Booth%20%23S4-36249%20in%20the%20South%20Hall%20during%20CES%20this%20week.%20In%20addition%20to%20the%202x%20BD%20recordable%20%28BD-R%29%20and%20BD%20rewritable%20%28BD-RE%29%20media%20which%20are%20available%20now%2C%20Verbatim%20will%20showcase%20six%20new%20BD%20products%20scheduled%20to%20begin%20shipping%20in%20the%20first%20half%20of%202008.%20The%20new%20products%20include%204x%20BD-R%20discs%2C%20Single-sided%20Double-layer%20%28DL%29%202x%20BD-R%20and%20BD-RE%20discs%2C%20Mini%20BD-R%20and%20BD-RE%20discs%20and%201-2x%20BD-R%20LTH%20TYPE%20discs.%0A%0AThe%20market%20for%20BD%20blank%20media%20is%20being%20driven%20by%20the%20growing%20demand%20for...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p class="prtitle">Verbatim Showcases Expanding Line of Blu-Ray Media at CES</p>

<center><i>New Technologies Enable Faster Write Speeds, Higher Capacities, Lower Production Costs</i></center><br />
<br />

<p>2008 International CES<br />
Booth #S4-36249, South Hall</p>

<p><B>LAS VEGAS--(BUSINESS WIRE)</B>--Verbatim&reg; Americas, LLC announced today that it has extended its support for Blu-ray Disc (BD) technology and will feature its expanded line of Blu-ray media at Booth #S4-36249 in the South Hall during CES this week. In addition to the 2x BD recordable (BD-R) and BD rewritable (BD-RE) media which are available now, Verbatim will showcase six new BD products scheduled to begin shipping in the first half of 2008. The new products include 4x BD-R discs, Single-sided Double-layer (DL) 2x BD-R and BD-RE discs, Mini BD-R and BD-RE discs and 1-2x BD-R LTH TYPE discs.</p>

<p>The market for BD blank media is being driven by the growing demand for BD hardware. In-Stat analysts predict that worldwide, shipments of blue-laser players and recorders will increase from an estimated 200,000 units in 2006 to 10 million by 2010. In Japan, Blu-ray has taken an early lead. According to the Blu-ray Disc Association (BDA), the sales figures from the week ending November 12, 2007 for next-generation recorders in Japan show that 97.2 percent were Blu-ray devices.</p>

<p>With the increased availability of recorders, players and camcorders that support BD technology, Verbatim sees 2008 as the "Year of Awakening" for blank Blu-ray discs. To meet the demand, Verbatim followed a proven strategy for success - combine the latest technologies and the highest quality with capacity and performance choices that will improve the user's digital experience.</p>

<p>Decreased Recording Times - With Verbatim's 25GB 4x BD-R media, users can record an entire disc in approximately 23 min. The higher performance makes the 4x BD-R media suitable not only for recording personal high-definition video, but also for backing up PC data or archiving photo collections.</p>

<p>To achieve 4x recording, Mitsubishi Kagaku Media (MKM), Verbatim's parent company, fine-tuned the proprietary Metal Ablative Recording Layer (MABL) technology it developed for Verbatim's first-generation BD-R media. Designed to ensure a wide power margin for stable recording, MKM improved the sensitivity of the MABL to attain the higher-speed recording while ensuring excellent recording compatibility and prolonged archival life. Verbatim will release its 25GB 4x BD-R media in Q1 2008.</p>

<p>Increased Capacity - To meet the capacity requirements of true, high-definition video recording, Verbatim will launch its 2x BD-R DL media in Q2 2008. Verbatim 4x BD-R DL media will be launched later in 2008. With two recording layers on a single side, users can enjoy seamless recording of up to 50GB or about 4 hours of HD-quality video on a single disc without having to flip or change the disc. This makes Verbatim BD-R DL media a compelling storage solution for professional video production, business storage, backup, archiving, radio and television broadcast storage, education, banking, healthcare and government applications. Verbatim is also planning to release 2x BD-RE DL in 2008 as compatible hardware becomes more available.</p>

<p>More Convenience - Measuring three inches (8cm) in diameter, Verbatim's new 7.5GB Mini BD-R/RW discs combine with a BD-compatible camcorder to provide approximately one hour of continuous video capture time on a single side when high-definition (1920×1080i) is used, With the ability to record priceless memories directly to a Mini BD disc, users can eliminate the time-consuming process of downloading captured video to their computer hard drive. They can also enjoy unlimited capacity by simply removing the disc from the camcorder and adding a new one. The Mini BD-R/RW discs will be available in Q1 2008.</p>

<p>The highly innovative recording layers used for Verbatim BD-R and BD-R DL media have exceptionally wide power margins. This makes them the best choice for critical applications because it ensures quality recording on the entire disc surface, regardless of drive power fluctuations or smudges on the disc surface.</p>

<p>To provide added protection from scratches, fingerprints and dust particles which can cause recording and playback errors, all Verbatim BD media will feature a proprietary, super-hardcoat finish. Similar to the surface coating technology found in touch panel displays and scratch-resistant eyeglass lenses, the coating technology developed by MKM protects the recording layer without warping the disc. This protective coating is vital in BD disc production because the BD data layer is close to the surface of the disc and is not protected by a plastic substrate like DVD media.</p>

<p>Expanding the Market for Blank Blu-ray Discs - With the availability of LTH Type hardware and firmware from industry leaders such as Sony and Panasonic, the momentum is already building for media that will enable consumers to move up to the more affordable BD media. Verbatim 25GB BD-R LTH Type discs feature a new technically advanced organic dye in the recording layer that can be burned at speeds of 1x and 2x. By switching from the more expensive inorganic layer used with current BD-R to the new organic layer, manufacturing costs can be reduced. Verbatim will release 2x BD-R LTH Type media in the Spring of 2008.</p>

<p>Leveraging its many years of success in developing organic AZO recording layers for CD-R and DVD-R media, MKM developed a new organic AZO recording layer for the BD-R LTH Type media and produced sample discs for testing.</p>

<p>The patented AZO dye used in the recording layer of Verbatim BD-R LTH Type media provides a unique combination of features that range from increased sensitivity to laser light - the key requirement for optimized recording performance, to control the heat interference between consecutive recorded marks for substantially less jitter and reduced degradation of recording marks. The innovative dye also features a wide power margin to ensure quality recording on the entire disc surface.</p>

<p>Unlike current BD-R discs, in which the inorganic recording layer is made by the sputtering process, the organic recording layer for BD-R LTH TYPE media can be applied using the same dye spin coating process as CD-R or DVD-R media. As a result, Verbatim will be able to begin mass production of its BD-R LTH TYPE by only slightly modifying existing CD-R or DVD-R production lines and by adding the cover layer coating process with super hard coat feature. With the investment in BD-R LTH TYPE production less than current BD-R production, Verbatim expects many CD/DVD manufacturers will be joining the BD world.</p>

<p>Verbatim/MKM is a contributing member of the Blu-ray Disc Association (BDA).</p>

<p><br />
<B>About Verbatim</B></p>

<p>Verbatim's businesses in the Americas, Europe/Middle East/Africa and Asia Pacific regions are wholly owned subsidiaries of Tokyo-based Mitsubishi Kagaku Media Co., Ltd. MKM's parent company, Mitsubishi Chemical Corporation (MCC), is Japan's largest chemical company.</p>

<p>Verbatim develops and markets innovative, high-quality products for storing, moving and using digital content. Known for its leadership in the optical, magnetic and flash storage and related accessories markets, the company provides reliable, unique technologies and products that are highly sought after and broadly distributed worldwide. For more information, contact Verbatim Americas, LLC, 1200 W.T. Harris Boulevard, Charlotte, NC 28262, (800) 421-4188. In Europe, Verbatim Ltd., Prestige House, 23-26 High Street, Egham, Surrey, TW20 9DU, UK, (+44) 1784 439 781. In Japan, Mitsubishi Kagaku Media Co., Ltd., 31-19, Shiba 5-Chome, Minato-ku, Tokyo 108-0014, (+81) 3-5454-3972. Or visit the web site at www.verbatim.com and select the country of your location.</p>

<p>Editor's Note: For photos and more information on Verbatim's new BD media, contact Andy Marken, Marken Communications, Inc.; (408) 986-0100 or email andy@markencom.com.</p>

<p>Verbatim is a registered trademark of Verbatim Americas, LLC. Other company and product names contained herein are trademarks of their respective companies. Specifications subject to change without notice.</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>January  8, 2008 12:01 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 891
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
 				AND entry_id <> 891
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2008/01/verbatim_showcases_expanding_line_of_blu-ray_media_at_ces.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
