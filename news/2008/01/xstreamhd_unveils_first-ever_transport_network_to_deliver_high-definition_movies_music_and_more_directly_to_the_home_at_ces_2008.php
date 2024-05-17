<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 893";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 893 AND placement_is_primary = 1";
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
	<meta name="keywords" content="transport network, directly home, sands booth, audio directly, network deliver, XStreamHD, xstreamhd, home, Full, full, video, satellite, network, media, content, audio, consumers, ces, CES, first, channels, booth, directly, titles, quality" />
	<meta name="description" content="XStreamHD, revolutionizing the delivery and distribution of Full HD entertainment to the home, today unveiled at CES 2008 (Sands Booth #71838) the first-ever transport network to deliver movies, TV, music, electronic games, and more in Full HD (1080p) video and 7.1 channels of DTS-HD Master Audio&amp;trade;, directly to consumers, via satellite.

XStreamHD is leading the Full HD revolution with the first transport network to bring..." />
	<title>HDTV Magazine Bulletins - XStreamHD&trade; Unveils First-Ever Transport Network to Deliver High-Definition Movies, Music, and More Directly to the Home at CES 2008</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/xstreamhd_unveils_first-ever_transport_network_to_deliver_high-definition_movies_music_and_more_directly_to_the_home_at_ces_2008';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('XStreamHD&trade; Unveils First-Ever Transport Network to Deliver High-Definition Movies, Music, and More Directly to the Home at CES 2008'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2008/01/xstreamhd_unveils_first-ever_transport_network_to_deliver_high-definition_movies_music_and_more_directly_to_the_home_at_ces_2008.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">XStreamHD&trade; Unveils First-Ever Transport Network to Deliver High-Definition Movies, Music, and More Directly to the Home at CES 2008</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>January  8, 2008</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Products & Equipment">Products & Equipment</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2008/01/xstreamhd_unveils_first-ever_transport_network_to_deliver_high-definition_movies_music_and_more_directly_to_the_home_at_ces_2008.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2008/01/xstreamhd_unveils_first-ever_transport_network_to_deliver_high-definition_movies_music_and_more_directly_to_the_home_at_ces_2008.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2008/01/xstreamhd_unveils_first-ever_transport_network_to_deliver_high-definition_movies_music_and_more_directly_to_the_home_at_ces_2008.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2008/01/xstreamhd_unveils_first-ever_transport_network_to_deliver_high-definition_movies_music_and_more_directly_to_the_home_at_ces_2008.php&amp;phase=2&amp;title=XStreamHD%26trade%3B%20Unveils%20First-Ever%20Transport%20Network%20to%20Deliver%20High-Definition%20Movies%2C%20Music%2C%20and%20More%20Directly%20to%20the%20Home%20at%20CES%202008&amp;bodytext=XStreamHD%2C%20revolutionizing%20the%20delivery%20and%20distribution%20of%20Full%20HD%20entertainment%20to%20the%20home%2C%20today%20unveiled%20at%20CES%202008%20%28Sands%20Booth%20%2371838%29%20the%20first-ever%20transport%20network%20to%20deliver%20movies%2C%20TV%2C%20music%2C%20electronic%20games%2C%20and%20more%20in%20Full%20HD%20%281080p%29%20video%20and%207.1%20channels%20of%20DTS-HD%20Master%20Audio%26trade%3B%2C%20directly%20to%20consumers%2C%20via%20satellite.%0A%0AXStreamHD%20is%20leading%20the%20Full%20HD%20revolution%20with%20the%20first%20transport%20network%20to%20bring...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p class="prtitle">XStreamHD&trade; Unveils First-Ever Transport Network to Deliver High-Definition Movies, Music, and More Directly to the Home at CES 2008</p>

<center><i>Witness the XStreamHD Revolution in Action with Live Demos Featuring The Latest Content from Leading Studios and More at Sands Booth #71838

<p>XStreamHD Press Conference on Tuesday, January 8 in Venetian Casanova 503 To Feature Two-Time Academy-Award Winner Michael Douglas</i></center><br /><br />
<br /></p>

<p>2008 International CES<br />
Sands Booth #71838</p>

<p><B>MCLEAN, Va.--(BUSINESS WIRE)</B>--XStreamHD, revolutionizing the delivery and distribution of Full HD entertainment to the home, today unveiled at CES 2008 (Sands Booth #71838) the first-ever transport network to deliver movies, TV, music, electronic games, and more in Full HD (1080p) video and 7.1 channels of DTS-HD Master Audio&trade;, directly to consumers, via satellite.</p>

<p>"We've worked hard to address the real needs consumers have and find the solutions they're looking for. What they're missing is access to Full HD content," said XStreamHD Founder and CEO George Gonzalez. "Today is all about Full HD. We are pleased to announce that we are the first to deliver Full HD and 7.1 channels of lossless audio directly to your home."</p>

<p><br />
<B>The XStreamHD Revolution</B></p>

<p>XStreamHD is leading the Full HD revolution with the first transport network to bring 1080p video and 7.1 channels of lossless audio directly to the home via satellite. Never before has there been a home theater experience that combines all of the features, quality, value, and convenience that consumers want. XStreamHD offers affordable access to the highest quality HD movies, music, broadcast TV, electronic games, and more - without the limitations of programming schedules, storage space, or physical media, and without the hassles of video rental stores, slow mail service, or out-of-stock titles. With XStreamHD, consumers will never miss the new releases or the broadcast HD content they love. The hottest movies and new releases are delivered via satellite, while vintage titles may be delivered over a broadband Internet connection.</p>

<p>With XStreamHD, consumers can build a customized, unique, and unparalleled in-home entertainment network and enjoy the content they want most, when it's most convenient, anywhere in the home, and at the quality today's home theaters were designed to support. Only XStreamHD's proprietary technology delivers video in Full HD (1080p) - twice the quality of current cable and satellite offerings - and up to 7.1 channels of lossless audio, achieving sound quality that is identical to the studio master and surpassing any other direct-to-home service available.</p>

<p>The complete XStreamHD solution includes three core components that are quickly and easily installed by the user or, if preferred, by a qualified technician. The first is a small outdoor satellite antenna that captures multiple streams of Full HD content delivered by XStreamHD through existing standard geosynchronous satellites. From the dish, titles are stored centrally in the XStreamHD Media Server located inside the home. The Media Server stores your pre-selected titles in your Virtual Personal Library until you access them with any XStreamHD Media Receiver or DLNA-compliant device throughout your XStreamHD home network.</p>

<p><br />
<B>Pricing & Availability</B></p>

<p>The XStreamHD solution will be available to consumers in early Q4 2008. The introductory price for a complete XStreamHD home solution, including a Media Server and a Media Receiver, is available to initial subscribers starting at just $399.</p>

<p><br />
<B>Featured System Highlights</B></p>

<p> * Satellite delivery of Full HD 1080p video, MPEG-2 or MPEG-4/H.264 in 4:3 or 16:9 screen formats<br />
 * Satellite delivery of up to 7.1 channels 96kHz/24bits of lossless DTS-HD&trade; Master Audio<br />
 * View four Full HD video streams simultaneously throughout the home<br />
 * Learns users' choices and preferences to adopt their entertainment profile and continuously updates the Virtual Personal Library with titles they're likely to enjoy<br />
 * Equipped with three ATSC tuners and a Network Video Recorder for viewing and recording three HDTV broadcasts at the same time - even while watching a fourth selection from your Virtual Personal Library&trade;<br />
 * Patent Pending Adaptive Recording&trade; ensures accurate recording of HDTV programs from start to finish, even if broadcast schedules change or are delayed<br />
 * Easy-to-use on-screen menus to manage content preferences, parental controls, spending limits<br />
 * DLNA v1.5 certified to integrate all compatible devices on the XStreamHD network<br />
 * Dual slide-in drive bays enable scalable storage featuring Seagate&reg; Technology hard drive storage - 500GB, 1 TB, 2 TB options - and the ability to configure an external storage subsystem via the eSATA interface<br />
 * Includes a feature-rich Personal PBX business-class phone system; also supports VoIP calls and offers free calls between XStreamHD subscribers<br />
 * Uses gigabit Ethernet (GigE) permitting the transfer of HD video and audio at 1 gbps throughout the home<br />
 * HDMI interface simplifies installation and ensures signal integrity for vibrant 1080p video<br />
 * Front panel USB port for MP3 audio downloads</p>

<p><br />
<B>XStreamHD @ CES</B></p>

<p>The XStreamHD solution is currently set up at CES in Sands Booth #71838, with live demos at the top and bottom of every hour featuring the latest content from leading studios. Visitors to the booth can sign up to become an XStreamVIP and gain access to the exclusive XStreamHD community. Demos are also running simultaneously in the booths of XStreamHD's strategic partners, DTS, Inc. (Las Vegas Convention Center, South Hall 1 #21913) and Seagate Technology (Las Vegas Convention Center, South Hall 3 #30659).</p>

<p>The solution will be introduced live to the media at the exclusive, invitation-only XStreamHD press conference featuring two-time Academy Award winner Michael Douglas on Tuesday, January 8 at 11 AM in Venetian Casanova 503, and at the Seagate BlogHaus in the Bellagio on Wednesday, January 9 at 6:30 PM. For more information, an invitation to the XStreamHD VIP press events, or to set up a media interview, please contact Ilana Zalika at izalika@XStreamHD.com or +1 (732) 266-5219.</p>

<p><br />
<B>About XStreamHD</B></p>

<p>XStreamHD is leading an HD revolution, setting a new standard for the delivery and distribution of Full HD entertainment throughout the home. Privately funded and five years in the making, XStreamHD is led by George Gonzalez, a recognized pioneer in broadband and satellite communications. XStreamHD's executive team and board of directors include veterans of the Fortune 500 who have been instrumental in shaping the television, entertainment, telecom, and consumer markets. For more information, visit www.XStreamHD.com or call +1 (703) 852-1300.</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>January  8, 2008 09:23 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 893
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
 				AND entry_id <> 893
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2008/01/xstreamhd_unveils_first-ever_transport_network_to_deliver_high-definition_movies_music_and_more_directly_to_the_home_at_ces_2008.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
