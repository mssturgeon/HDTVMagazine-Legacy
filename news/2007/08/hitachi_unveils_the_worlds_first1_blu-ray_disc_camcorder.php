<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 659";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 659 AND placement_is_primary = 1";
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
	<meta name="keywords" content="high definition, full high, definition video, ray disc, blu ray, definition, hitachi, high, Hitachi, full, camcorder, video, camcorders, new, DVD, dvd, disc, world, drive, first, Full, High, blu, ray, Disc" />
	<meta name="description" content="Hitachi, Ltd. (NYSE:HIT / TSE:6501) announced the world's first Blu-ray Disc (BD) Camcorder, -which records one hour of 1920x1080 Full High-Definition video onto a BD (single side, single layer.) The new camcorders will start selling in Japan on August 30, 2007, and overseas market sequentially starting from October.

The new DZ-BD7H is a Hybrid Cam with a BD drive and a 30 gigabyte (GB) built-in hard disc drive (HDD) which can record approximately four hours of 1920x1080 full high-definition video, or up to eight hours of 1440x1080 high-definition video. It can also copy the contents from HDD to 8cm BD within the camcorder so that users do not need to use any external devices." />
	<title>HDTV Magazine Bulletins - Hitachi Unveils the World's First<sup>1</sup> Blu-ray Disc Camcorder</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/hitachi_unveils_the_worlds_first1_blu-ray_disc_camcorder';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Hitachi Unveils the World\'s First<sup>1</sup> Blu-ray Disc Camcorder'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2007/08/hitachi_unveils_the_worlds_first1_blu-ray_disc_camcorder.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Hitachi Unveils the World's First<sup>1</sup> Blu-ray Disc Camcorder</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>August  2, 2007</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=HD DVD & Blu-ray">HD DVD & Blu-ray</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2007/08/hitachi_unveils_the_worlds_first1_blu-ray_disc_camcorder.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2007/08/hitachi_unveils_the_worlds_first1_blu-ray_disc_camcorder.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2007/08/hitachi_unveils_the_worlds_first1_blu-ray_disc_camcorder.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2007/08/hitachi_unveils_the_worlds_first1_blu-ray_disc_camcorder.php&amp;phase=2&amp;title=Hitachi%20Unveils%20the%20World%27s%20First%3Csup%3E1%3C%2Fsup%3E%20Blu-ray%20Disc%20Camcorder&amp;bodytext=Hitachi%2C%20Ltd.%20%28NYSE%3AHIT%20%2F%20TSE%3A6501%29%20announced%20the%20world%27s%20first%20Blu-ray%20Disc%20%28BD%29%20Camcorder%2C%20-which%20records%20one%20hour%20of%201920x1080%20Full%20High-Definition%20video%20onto%20a%20BD%20%28single%20side%2C%20single%20layer.%29%20The%20new%20camcorders%20will%20start%20selling%20in%20Japan%20on%20August%2030%2C%202007%2C%20and%20overseas%20market%20sequentially%20starting%20from%20October.%0A%0AThe%20new%20DZ-BD7H%20is%20a%20Hybrid%20Cam%20with%20a%20BD%20drive%20and%20a%2030%20gigabyte%20%28GB%29%20built-in%20hard%20disc%20drive%20%28HDD%29%20which%20can%20record%20approximately%20four%20hours%20of%201920x1080%20full%20high-definition%20video%2C%20or%20up%20to%20eight%20hours%20of%201440x1080%20high-definition%20video.%20It%20can%20also%20copy%20the%20contents%20from%20HDD%20to%208cm%20BD%20within%20the%20camcorder%20so%20that%20users%20do%20not%20need%20to%20use%20any%20external%20devices.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p class="prtitle">Hitachi Unveils the World's First<sup>1</sup> Blu-ray Disc Camcorder</p>

<center><i>Two New Camcorders Capable of Recording One Hour 1920x1080 Full High-Definition Video on a Blu-ray Disc</i></center><br />

<p><img src="/images/products/hitachi-dz-bd7h-bd70.jpg" alt="Hitachi DZ-BD7H and DZ-BD70" /><br /></p>

<p><B>TOKYO August 2, 2007</B> - Hitachi, Ltd. (NYSE:HIT / TSE:6501) announced the world's first Blu-ray Disc (BD) Camcorder, -which records one hour of 1920x1080 Full High-Definition video onto a BD (single side, single layer.) The new camcorders will start selling in Japan on August 30, 2007, and overseas market sequentially starting from October.</p>

<p>The new DZ-BD7H is a Hybrid Cam with a BD drive and a 30 gigabyte (GB) built-in hard disc drive (HDD) which can record approximately four hours of 1920x1080 full high-definition video, or up to eight hours of 1440x1080 high-definition video. It can also copy the contents from HDD to 8cm BD within the camcorder so that users do not need to use any external devices.</p>

<p>The new DZ-BD70 is a BD single drive camcorder which can record approximately one hour of 1920x1080 full high-definition video (two hours of 1440x1080 high definition video) on a 8cm BD.</p>

<p>The new BD camcorders have a system which captures, records and stores 1920x1080 full high-definition video throughout the whole process with its newly developed full high-definition lens, 5.3 mega pixel progressive CMOS (Complementary Metal Oxide Semiconductor) image sensor<sup>2</sup> with effective 2.07 mega pixels for video and 4.32 mega pixels for still photo, the newly developed full high definition signal processor "Picture Master Full HD," and the world's first 8cm BD drive, which can record 1920x1080 full high definition video on a 8cm BD which has 5 times more capacity than a 8cm DVD.</p>

<p><br />
<B>Model Name and Introduction (Japan)</B></p>

<table bgcolor="#999999" border="0" cellpadding="4" cellspacing="1"><tbody><tr valign="top"><td class="brb" align="center" bgcolor="#cccccc">Product</td><td class="brb" align="center" bgcolor="#cccccc">Model Name</td><td class="brb" align="center" bgcolor="#cccccc">HDD</td><td class="brb" align="center" bgcolor="#cccccc">Image<br>Sensor</td><td class="brb" align="center" bgcolor="#cccccc">Selling Starts</td><td class="brb" align="center" bgcolor="#cccccc">Suggested<br>Retail Price<br></td><td class="brb" align="center" bgcolor="#cccccc">Initial<br>Production/Month<br></td></tr><tr><td rowspan="2" align="center" bgcolor="#ffffff">BD Camcorder</td><td align="center" bgcolor="#ffffff">DZ-BD7H</td><td align="center" bgcolor="#ffffff">30GB</td><td rowspan="2" align="center" bgcolor="#ffffff">Approx.<br>5.3Mega Pixels</td><td align="center" bgcolor="#ffffff">Aug.30, 2007</td><td rowspan="2" align="center" bgcolor="#ffffff">Open</td><td rowspan="2" align="center" bgcolor="#ffffff">20,000 units</td></tr><tr><td align="center" bgcolor="#ffffff">DZ-BD70</td><td align="center" bgcolor="#ffffff"> - </td><td align="center" bgcolor="#ffffff">Aug.30, 2007</td></tr></tbody></table>

<p><a href="/cgi-bin/ntlinktrack.cgi?http://www.hitachi.com/New/cnews/070802_china.pdf">News Release (chinese)</a> (PDF, 317kbyte)</p>

<p><br />
<B>Background of Development</B></p>

<p>Non-tape media camcorders such as DVD and HDD camcorders have grown to dominate more than 80% of the camcorder market<sup>3</sup> High definition camcorders have taken more than 30% of the consumer camcorder market<sup>3</sup>, and are expected to continue growing.</p>

<p>Hitachi, with its corporate statement "Inspire the Next, "has been creating new product categories and established new standards in the industry. In 2000, Hitachi introduced the world's first DVD camcorder, and in 2006, the world's first hybrid camcorder with a DVD drive and an HDD drive, which makes it easy to dub the contents on the HDD to the DVD. The spirit behind the innovation was always a careful consideration of the customer's needs.</p>

<p>This time, with the keyword "A True Breakthrough in Your Hand," Hitachi developed an 8cm BD/DVD Drive for Camcorders, Full High Definition Signal Processor Engine "Picture Master Full HD", and CMOS Image Sensor for Full High Definition<sup>2</sup> With these core technologies, Hitachi adopted BD as a media since it has more than five times the capacity of a DVD and can record 1 hour of 1920x1080 full high definition video on one disc to create two models of the world's first BD camcorders, which shoot, playback, and store full high-definition in every process. Hitachi will continue to mobilize its technology to introduce camcorders that meet the needs of the consumers.</p>

<p>*1) 	as of August, 2007<br />
*2) 	5.3 mega pixel CMOS image sensor is developed by AltaSens, Inc. (Thousand Oaks, CA) under the cooperation between Hitachi, Ltd. and AltaSens, Inc. All rights are reserved by AltaSens, Inc.<br />
*3) 	refers to consumer camcorder market in Japan</p>

<p><br />
<B>About Hitachi, Ltd.</B></p>

<p>Hitachi, Ltd., (NYSE: HIT / TSE: 6501), Hitachi, Ltd., (NYSE: HIT / TSE: 6501), headquartered in Tokyo, Japan, is a leading global electronics company with approximately 384,000 employees worldwide. Fiscal 2006 (ended March 31, 2007) consolidated revenues totaled 10,247 billion yen ($86.8 billion). The company offers a wide range of systems, products and services in market sectors including information systems, electronic devices, power and industrial systems, consumer products, materials and financial services. For more information on Hitachi, please visit the company's website at http://www.hitachi.com.</p>

<p><br />
<B>Notes</B></p>

<p>Microsoft&reg; and Windows&reg; are registered trademarks or trademark of Microsoft Corporation in United States and/or other countries.</p>

<p>HDMI, HDMI logo, High-Definition Multimedia Interface are registered trademarks or trademark of HDMI Licensing LLC.</p>

<p>Blu-ray Disc and logo are registered trademarks.</p>

<p>All brand names are trademarks, registered trademarks, or trade names of their respective holders.</p>

<p>For a comprehensive presentation of the new Hitachi BD camcorders, please visit http://av.hitachi.com/camcorder<br />
Information contained in this news release is current as of the date of the press announcement, but may be subject to change without prior notice.</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>August  2, 2007 06:35 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 659
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
 				AND entry_id <> 659
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2007/08/hitachi_unveils_the_worlds_first1_blu-ray_disc_camcorder.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
