<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 881";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 881 AND placement_is_primary = 1";
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
	<meta name="keywords" content="master audio, seagate technology, high definition, dts inc, consumer electronics, dts, DTS, XStreamHD, xstreamhd, seagate, Seagate, audio, technology, master, Audio, storage, home, Master, Technology, high, consumer, definition, bit, capacity, products" />
	<meta name="description" content="XStreamHD, setting a new standard for the delivery and distribution of Full HD entertainment to the home, today announced strategic partnerships with two recognized pioneers of high-definition technology, DTS, Inc. (DTS) and Seagate Technology&amp;reg; (NYSE: STX). XStreamHD will provide the first-ever transport network to deliver high-definition movies and music directly to the home - offering up to 7.1 channels of DTS-HD Master Audio&amp;trade;, and featuring Seagate's purpose-built DB35 Series&amp;trade; hard drives with up to an incredible one terabyte (TB) of storage, allowing consumers to use up to 2TB of storage in one solution. The XStreamHD solution will be unveiled for the first time at the Consumer Electronics Show in Las Vegas, January 7-10.

XStreamHD will offer affordable access to..." />
	<title>HDTV Magazine Bulletins - XStreamHD&trade; Announces Strategic Partnerships with High-Definition Audio Pioneer, DTS, Inc., and Worldwide Storage Leader, Seagate Technology</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/xstreamhd_announces_strategic_partnerships_with_high-definition_audio_pioneer_dts_inc_and_worldwide_storage_leader_seagate_technology';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('XStreamHD&trade; Announces Strategic Partnerships with High-Definition Audio Pioneer, DTS, Inc., and Worldwide Storage Leader, Seagate Technology'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2008/01/xstreamhd_announces_strategic_partnerships_with_high-definition_audio_pioneer_dts_inc_and_worldwide_storage_leader_seagate_technology.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">XStreamHD&trade; Announces Strategic Partnerships with High-Definition Audio Pioneer, DTS, Inc., and Worldwide Storage Leader, Seagate Technology</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>January  7, 2008</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Technology">Technology</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2008/01/xstreamhd_announces_strategic_partnerships_with_high-definition_audio_pioneer_dts_inc_and_worldwide_storage_leader_seagate_technology.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2008/01/xstreamhd_announces_strategic_partnerships_with_high-definition_audio_pioneer_dts_inc_and_worldwide_storage_leader_seagate_technology.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2008/01/xstreamhd_announces_strategic_partnerships_with_high-definition_audio_pioneer_dts_inc_and_worldwide_storage_leader_seagate_technology.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2008/01/xstreamhd_announces_strategic_partnerships_with_high-definition_audio_pioneer_dts_inc_and_worldwide_storage_leader_seagate_technology.php&amp;phase=2&amp;title=XStreamHD%26trade%3B%20Announces%20Strategic%20Partnerships%20with%20High-Definition%20Audio%20Pioneer%2C%20DTS%2C%20Inc.%2C%20and%20Worldwide%20Storage%20Leader%2C%20Seagate%20Technology&amp;bodytext=XStreamHD%2C%20setting%20a%20new%20standard%20for%20the%20delivery%20and%20distribution%20of%20Full%20HD%20entertainment%20to%20the%20home%2C%20today%20announced%20strategic%20partnerships%20with%20two%20recognized%20pioneers%20of%20high-definition%20technology%2C%20DTS%2C%20Inc.%20%28DTS%29%20and%20Seagate%20Technology%26reg%3B%20%28NYSE%3A%20STX%29.%20XStreamHD%20will%20provide%20the%20first-ever%20transport%20network%20to%20deliver%20high-definition%20movies%20and%20music%20directly%20to%20the%20home%20-%20offering%20up%20to%207.1%20channels%20of%20DTS-HD%20Master%20Audio%26trade%3B%2C%20and%20featuring%20Seagate%27s%20purpose-built%20DB35%20Series%26trade%3B%20hard%20drives%20with%20up%20to%20an%20incredible%20one%20terabyte%20%28TB%29%20of%20storage%2C%20allowing%20consumers%20to%20use%20up%20to%202TB%20of%20storage%20in%20one%20solution.%20The%20XStreamHD%20solution%20will%20be%20unveiled%20for%20the%20first%20time%20at%20the%20Consumer%20Electronics%20Show%20in%20Las%20Vegas%2C%20January%207-10.%0A%0AXStreamHD%20will%20offer%20affordable%20access%20to...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p class="prtitle">XStreamHD&trade; Announces Strategic Partnerships with High-Definition Audio Pioneer, DTS, Inc., and Worldwide Storage Leader, Seagate Technology</p>

<center><i>New XStreamHD Solution Features DTS-HD Master Audio&trade; and Seagate's Purpose-Built DB35 Series&trade; Hard Drives</i></center><br />
<br />

<p>2008 International CES<br />
Sands # 71838</p>

<p><B>MCLEAN, Va.--(BUSINESS WIRE)</B>--XStreamHD, setting a new standard for the delivery and distribution of Full HD entertainment to the home, today announced strategic partnerships with two recognized pioneers of high-definition technology, DTS, Inc. (DTS) and Seagate Technology&reg; (NYSE: STX). XStreamHD will provide the first-ever transport network to deliver high-definition movies and music directly to the home - offering up to 7.1 channels of DTS-HD Master Audio&trade;, and featuring Seagate's purpose-built DB35 Series&trade; hard drives with up to an incredible one terabyte (TB) of storage, allowing consumers to use up to 2TB of storage in one solution. The XStreamHD solution will be unveiled for the first time at the Consumer Electronics Show in Las Vegas, January 7-10.</p>

<p>"As our name implies, XStreamHD is about bringing the highest resolution audio and video to the home with unmatched quality and convenience. We are thrilled to offer our customers the clarity and dynamic range provided by DTS-HD Master Audio, along with the unparalleled storage capacity offered by Seagate, to advance the home theater experience," said George Gonzalez, Founder and CEO of XStreamHD.</p>

<p>XStreamHD will offer affordable access to HD movies, music, broadcast TV, electronic games, and more with unmatched quality and convenience - without the limitations of program schedules or physical media.</p>

<p>"XStreamHD is what the public has been waiting for, and DTS is very excited to be the lossless audio format for this new HD home entertainment solution. DTS-HD Master Audio will enable uncompromised delivery of high-definition 7.1-channel lossless audio, offering the ultimate complement to the high-definition video delivered by XStreamHD," said Brian Towne, Senior Vice President and General Manager, Consumer Division at DTS.</p>

<p>"Seagate continues to push the boundaries of digital media storage by enabling the highest possible capacity for high-quality HD content libraries that consumers want - and that XStreamHD will finally bring to their homes," said Patrick King, Senior Vice President and General Manager of Seagate's consumer electronics business unit. "Seagate is enabling XStreamHD users to enjoy up to two terabytes of storage space for the HD content they love, without limits."</p>

<p>The XStreamHD solution, featuring DTS and Seagate Technology, will be on display in the XStreamHD booth at CES, Sands # 71838, with demos running simultaneously in the DTS booth, Las Vegas Convention Center, South Hall 1 #21913, and the Seagate booth, Las Vegas Convention Center, South Hall 3 #30659. For more information, an invitation to the XStreamHD VIP press event on Tuesday, January 8th, or to set up a media briefing, please contact Ilana Zalika at izalika@XStreamHD.com or +1 (732) 266-5219.</p>

<p>To inquire about partnership opportunities, please contact Brigette Polmar, Director, Public Relations, at bpolmar@XStreamHD.com, tel. +1 (703) 852-1346.</p>

<p><br />
<B>About XStreamHD</B></p>

<p>XStreamHD is leading an HD revolution, setting a new standard for the delivery and distribution of Full HD entertainment throughout the home. Privately funded and five years in the making, XStreamHD is led by George Gonzalez, a recognized pioneer in broadband and satellite communications. XStreamHD's executive team and board of directors include veterans of the Fortune 500 who have been instrumental in shaping the television, entertainment, telecom, and consumer markets. For more information, visit www.XStreamHD.com or call +1 (703) 852-1300.</p>

<p><br />
<B>About DTS-HD Master Audio&trade;</B></p>

<p>DTS-HD Master Audio delivers sound that is bit-for-bit identical to the studio master. It can deliver audio at variable bit rates which are significantly higher than standard DVDs. DTS-HD Master Audio can provide up to 7.1 audio channels at a 96k sampling frequency / 24-bit depth or 5.1 audio channels at 192 kHz that are identical to the original master. The DTS-HD Master Audio bit stream also contains the DTS 1.5 Mbps core for backwards compatibility with existing DTS-enabled home theater systems, and delivery of 5.1 channels of sound at twice the resolution found on most standard DVDs.</p>

<p><br />
<B>About DTS</B></p>

<p>DTS, Inc. (NASDAQ: DTSI) is a digital technology company dedicated to delivering the ultimate entertainment experience. DTS decoders are in virtually every major brand of 5.1-channel surround processor, and there are hundreds of millions of DTS-licensed consumer electronics products available worldwide. A pioneer in multi-channel audio, DTS technology is in home theatre, car audio, PC and game console products, as well as DVD-Video, HD DVD, Blu-ray Disc and Surround Music software. DTS audio products are featured on more than 27,000 motion picture screens worldwide. Additionally, DTS provides imaging technology and services for the motion picture industry; DTS Digital Images, formerly Lowry Digital Images, is a wholly-owned subsidiary of DTS and an industry leader in image restoration and enhancement. Founded in 1993, DTS is headquartered in Agoura Hills, California and has offices in the United Kingdom, Ireland, France, Italy, Canada, Hong Kong, Japan and China. For further information, please visit www.dts.com DTS is a registered trademark of DTS, Inc.</p>

<p><br />
<B>About Seagate</B></p>

<p>Seagate is the worldwide leader in the design, manufacture and marketing of hard disc drives, providing products for a wide-range of applications, including Enterprise, Desktop, Mobile Computing, Consumer Electronics and Branded Solutions. Seagate's business model leverages technology leadership and world-class manufacturing to deliver industry-leading innovation and quality to its global customers, and to be the low cost producer in all markets in which it participates. The company is committed to providing award-winning products, customer support and reliability to meet the world's growing demand for information storage. Seagate can be found around the globe and at www.seagate.com.</p>

<p>Seagate, Seagate Technology and the Wave logo are registered trademarks of Seagate Technology LLC in the United States and/or other countries. DB35 Series is either a trademark or registered trademark of Seagate Technology LLC or one of its affiliated companies in the United States and/or other countries. All other trademarks or registered trademarks are the property of their respective owners. When referring to hard drive capacity, one gigabyte, or GB, equals one billion bytes and one terabyte, or TB, equals one trillion bytes. Your computer's operating system may use a different standard of measurement and report a lower capacity. In addition, some of the listed capacity is used for formatting and other functions, and thus will not be available for data storage. Quantitative usage examples for various applications are for illustrative purposes. Actual quantities will vary based on various factors, including file size, file format, features and application software. Seagate reserves the right to change, without notice, product offerings or specifications.</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>January  7, 2008 07:42 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 881
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
			
 		<?if (7 <> 7) {
 			# Recent Articles by Author (exclude this one)
 			# Do not show recent articles for Bulletins.
 			$qry = "
 			SELECT entry_id, entry_blog_id, entry_created_on, entry_title, author_id, author_name
 			FROM mt_entry e, mt_author a
 			WHERE entry_blog_id = 7
 				AND entry_id <> 881
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2008/01/xstreamhd_announces_strategic_partnerships_with_high-definition_audio_pioneer_dts_inc_and_worldwide_storage_leader_seagate_technology.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
