<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 726";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Rodolfo La Maestra'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 726 AND placement_is_primary = 1";
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
	<meta name="keywords" content="iptv part, better mbps, iptv service, video card, implementations final, iptv, IPTV, channels, service, system, company, mbps, sasktel, Mbps, STB, part, stbs, better, STBs, might, stb, Part, Sasktel, using, vod" />
	<meta name="description" content="This is a continuation of the IPTV series of articles and covers specific IPTV implementations by Falcon Communications, Minerva Networks/Siemens/Espial, MyTVPal.com, and Sasktel." />
	<title>HDTV Magazine Articles - IPTV Part 5 - Additional Implementations</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/iptv_part_5_-_additional_implementations';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('IPTV Part 5 - Additional Implementations'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/articles/2007/10/iptv_part_5_-_additional_implementations.php";
		if ($author[img] != '' && 1 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Rodolfo La Maestra" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">IPTV Part 5 - Additional Implementations</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Rodolfo La Maestra</b><br />
				<?=$author_title?>
				Posted on <b>October 16, 2007</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Technology">Technology</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2007/10/iptv_part_5_-_additional_implementations.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/articles/2007/10/iptv_part_5_-_additional_implementations.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/articles/2007/10/iptv_part_5_-_additional_implementations.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2007/10/iptv_part_5_-_additional_implementations.php&amp;phase=2&amp;title=IPTV%20Part%205%20-%20Additional%20Implementations&amp;bodytext=This%20is%20a%20continuation%20of%20the%20IPTV%20series%20of%20articles%20and%20covers%20specific%20IPTV%20implementations%20by%20Falcon%20Communications%2C%20Minerva%20Networks%2FSiemens%2FEspial%2C%20MyTVPal.com%2C%20and%20Sasktel.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<div class="editorial">The following article is the latest in the IPTV series by Rodolfo La Maestra. Other articles in this series are as follows:
<ul>
<li><a href="/articles/2007/09/iptv_part_1_-_read_the_fine_print.php">IPTV Part 1 - Read the Fine Print</a></li>
<li><a href="/articles/2007/09/iptv_part_2_-_the_groups_forums_and_statistics.php">IPTV Part 2 - The Groups, Forums and Statistics</a></li>
<li><a href="/articles/2007/10/iptv_part_3_-_the_methods_and_a_working_technology.php">IPTV Part 3 - The Methods and a Working Technology</a></li>
<li><a href="/articles/2007/10/iptv_part_4_-_the_good_the_bad_and_the_ugly.php">IPTV Part 4 - The Good, the Bad and the Ugly</a></li>
<li><a href="/articles/2007/10/iptv_part_6_-_more_implementations_and_final_thoughts.php">IPTV Part 6 - More Implementations and Final Thoughts</a></li>
</ul></div>
<br />
<u>Falcon Communications</u>

<p>This company introduced an IP/Complete IPTV-delivery system targeted to small-scale Telcos.</p>

<p>The system features 145-channel selection, including 30 HDTV channels for 2007, and 4 local off-air HD channels; secured/encrypted signal throughout, single on-site relay rack, emergency-alert system standard across all channels.</p>

<p>BPS Telephone in Bernie, Mo. was aligned to become a service provider using the system.<br />
<br clear="all" /></p>

<p><u>Minerva Networks/Siemens/Espial</u></p>

<p>CES 2007</p>

<table><tr><td><img src="/images/articles/minerva-networks.jpg" alt="Minerva Networks" /></td><td><img src="/images/articles/ip-set-top-solutions.jpg" alt="IP Set-top Solutions" /></td></tr></table>

<p><u>MyTVPal.com</u></p>

<p>In November 2006, the company said they were launching North America's first 1080P High Definition IPTV service for streaming VOD and IPTV service using PC Player and IPTV receiver set top box (STB) clients from MatrixStream (mentioned above).</p>

<p>During 2007 the company expects to launch HD 1080p services and HD STBs to 100,000 subscribers, and grow to about 10 million users within the next 5 years. The company offers the PC Player for a free trial upon registering at <a href="/cgi-bin/ntlinktrack.cgi?http://www.MyTVPal.com/" target="_blank">www.MyTVPal.com</a></p>

<p>MyTVPal PC player is designed to allow any PC user to watch many different TV broadcasts and listen to many radio stations from around the world, as well as providing the ability to play HD videos via XMS streaming technology.</p>

<p>The lineup includes over 700 free IPTV SD and HD channels from over 70 countries, requiring a minimum of 1.5mbps speed or higher of network capacity. The company has plans to add more VOD titles and channels every month.</p>

<p>Features such as user uploaded content, DVR, and software upgrades for PC and STB clients are also planned during 2007.</p>

<p>The following are the minimal system requirements on the PC to play different videos on the latest beta MyTVPal player:</p>

<table class="bare"><tr><td class="type1b_header">Standard Definition 480P Videos</td><td class="type1b_header">High Definition 720P Videos</td><td class="type1b_header">High Definition 1080P Videos</td></tr><tr><td class="grid">Pentium 4 - 2.4 GHz or higher<br />
512 MB Ram<br />
256 MB Video Card or Better<br />
Broadband Speed 1.5 Mbps or faster<br />
Windows 2000/Windows XP or VISTA</td><td class="grid">Dual Core 1.6 GHz or better<br />
512 MB Ram<br />
256 MB Video Card or Better<br />
2.5 Mbps Broadband Speed or Better (3 Mbps recommended)<br />
Windows XP or VISTA</td><td class="grid">Dual Core 2.0 GHz or better<br />
1 GB Ram<br />
512 MB Video Card or Better<br />
5 Mbps Broadband Speed or Better (6 Mbps recommended)<br />
Windows XP or VISTA</td></tr></table><br />

<div align="center"><img src="/images/articles/mytvpal-screenshot.gif" alt="MyTVPal Screenshot" /><br />Sample Screen Shot of MyTVPal Video Player</div>

<p><u>Sasktel</u></p>

<p>In September 2006, Canada's SaskTel launched an HD IPTV service (Max HD Ultimate) over an IP network in North America. SaskTel is a government owned telecommunications company servicing the Canadian province of Saskatchewan.</p>

<p>The company said it had 45,000 IPTV subscribers out of the 425,000 clients for telecommunication services in 13 cities, 535 smaller communities and surrounding rural areas, including 49,000 farms.</p>

<p>Alcatel and Motorola's VIP1200 HD/H.264 set-top box are implemented in the service, as well as Kasenna's MediaBase media-streaming software, and Widevine Technologies' digital-rights-management system.</p>

<p>The service offers 27 HD channels, 35 Saskatchewan radio stations, high-speed-Internet service, video-on-demand, and other content, for $59 Canadian Dollars per month for an initial period of 4 months. The company plans a PVR service for early 2007.</p>

<p>HD movies from major studios such as NBC Universal, Paramount, Sony Pictures, Twentieth Century Fox and Warner Bros. were announced within the service, as well as TSN, Sportsnet, Discovery Channel, and other specialty channels.</p>

<p><a href="/cgi-bin/ntlinktrack.cgi?http://www.sasktel.com/" target="_blank">www.sasktel.com</a></p>

<p><u>Analysis of information Sasktel does not advertise</u></p>

<p>When analyzing the technical aspects of the HD IPTV service of Sasktel, one starts to see the limitations of it. I talked to Sasktel's technical department to discuss the subject and the following is a brief analysis:</p>

<p>Sasktel installs fiber wiring up to the street cabinet from where two regular copper phone lines run to the house. The two lines entering the house max out at 14Mbps each.</p>

<p>One is reserved for HD services using MPEG-4 compression and the other is reserved for SD STBs (MPEG-4 as well) and DSL services. That 2nd line is shared by a maximum of 4 SD STBs plus 1.5 Mbps DSL, or up to 7 Mbps DSL with less number of SD STBs, depending on the package. The HD line is not shared and it carries only the selected HD channel.</p>

<p>Sasktel uses Pace and Motorola STBs. Perhaps by April they would have a DVR STB.</p>

<p>The main drawback about this system is that only 1 HD channel can be tuned in the entire house no matter how many HDTVs you might have in the house. If you thought about using the 2nd line for an extra HD tuning, forget it, the second line cannot be used for another HD feed (in lieu of the standard SD / DSL service).</p>

<p>The 4 SD STBs for the 2nd line can tune different programs simultaneously.</p>

<p>Here is a second catch, if one requests a VOD SD program and a second SD STB in the house also wants the same VOD movie but a while later, it has to share the same running stream and view the movie as already started. It cannot initiate a separate VOD stream of that same movie to run in parallel at a different timing of the active stream. Some families might not even care about that limitation, some would.</p>

<p>The IPTV system is certainly open and flexible for the addition of more channels without being concerned about bandwidth issues of typical MSOs, but the limitation of no-parallel HD viewing at home could be a problem for many people.</p>

<p>At similar price conditions, many subscribers might put more value on being able to view multiple HD feeds than having the network flexibility to expand to another 100 channels which subscribers might not even have the time to see all.</p>

<p>Some people might be attracted to having a thousand channels, and if they have only one HDTV and live alone, the system might be perfect for their situation. However, a few years from now, it will be more common to have several HDTVs in a single family home, and using this solution, if one HD STB is already tuning an HD channel, the other HDTVs would be confined to display only the SD version of an HDTV program, or display only SD channels.</p>

<p>There will be future opportunities to improve the compression efficiency for this (or similar) IPTV system, which could make it eventually capable to offer parallel HD channels over the same pipe. However, this same future of more efficient compression algorithms could also benefit the growth of cable and satellite, which could offer more HD channels when using less space.</p>

<p>In both cases the systems would have to run the risk of eventual STB replacement if the new compression algorithm is not supported by the current STB design, like MPEG-2 STBs suffered when MPEG-4 was introduced by DirecTV and Dish Network.</p>

<p>Although those traditional systems have their own limitations of bandwidth, they already offer hundreds of channels. How many more you need? They also have dozens of HD programs for the viewer to watch in parallel at home today.</p>

<p>Additionally, when comparing this IPTV implementation with satellite, cable, and even regular broadcast, having the flexibility to view parallel HD programs might not be a point of negotiation at any price for some subscribers.</p>

<p>The price of this IPTV solution is not necessarily a bargain at $52 dollars a month for just the basic SD service (after the first 4 months), adding the HD services on premium channels the monthly bill gets closer to $100 per month. Cable and Satellite are not so different in price for that lineup.</p>

<p>Regarding technical problems of the implementation, the company had some technical problems with the feeds, but in general, they received 80% HD subscriber acceptance on a recent survey.</p>

<p>In the 6th and final article I include more implementations and some final thoughts about adopting IPTV.</p>

<p>Next Article: <a href="/articles/2007/10/iptv_part_6_-_more_implementations_and_final_thoughts.php">IPTV Part 6 - More Implementations and Final Thoughts</a></p>
		</div>
	</div>
	<p class="posted">Posted by <b>Rodolfo La Maestra</b>, <b>October 16, 2007 07:02 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 726
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
 				AND entry_id <> 726
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2007/10/iptv_part_5_-_additional_implementations.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
