<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 416";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Rodolfo La Maestra'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 416 AND placement_is_primary = 1";
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
	<meta name="keywords" content="frame rate, pass thru, silicon image, hdtv accepts, hdtvmagazine articles, hdmi, HDMI, video, display, fps, feature, DVD, dvd, hdtv, could, player, HDTV, image, support, rate, require, input, chips, frame, thru" />
	<meta name="description" content="Although the 1080p capability exists since version 1.0, 1080p is not mandatory in the HDMI spec, in any of the versions.

It would be difficult for Silicon Image to require TV makers (especially those with non-SiI chips) to indicate the chip's 1080p feature to end-users. It is not too different from how consumers know whether their component analog input can handle 1080i, 720p, or 480p. It was not too long ago that 100% of component inputs handle 480i only. Then shortly after that there was 480i and 480p, etc." />
	<title>HDTV Magazine Articles - HDMI Part 6 - 1080p Support</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdmi_part_6_-_1080p_support';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('HDMI Part 6 - 1080p Support'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/articles/2006/08/hdmi_part_6_-_1080p_support.php";
		if ($author[img] != '' && 1 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Rodolfo La Maestra" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDMI Part 6 - 1080p Support</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Rodolfo La Maestra</b><br />
				<?=$author_title?>
				Posted on <b>August 15, 2006</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Technology">Technology</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2006/08/hdmi_part_6_-_1080p_support.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/articles/2006/08/hdmi_part_6_-_1080p_support.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/articles/2006/08/hdmi_part_6_-_1080p_support.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2006/08/hdmi_part_6_-_1080p_support.php&amp;phase=2&amp;title=HDMI%20Part%206%20-%201080p%20Support&amp;bodytext=Although%20the%201080p%20capability%20exists%20since%20version%201.0%2C%201080p%20is%20not%20mandatory%20in%20the%20HDMI%20spec%2C%20in%20any%20of%20the%20versions.%0A%0AIt%20would%20be%20difficult%20for%20Silicon%20Image%20to%20require%20TV%20makers%20%28especially%20those%20with%20non-SiI%20chips%29%20to%20indicate%20the%20chip%27s%201080p%20feature%20to%20end-users.%20It%20is%20not%20too%20different%20from%20how%20consumers%20know%20whether%20their%20component%20analog%20input%20can%20handle%201080i%2C%20720p%2C%20or%20480p.%20It%20was%20not%20too%20long%20ago%20that%20100%25%20of%20component%20inputs%20handle%20480i%20only.%20Then%20shortly%20after%20that%20there%20was%20480i%20and%20480p%2C%20etc.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<h2>Optional Since Day One</h2>

<p>Although the 1080p capability exists since version 1.0, 1080p is not mandatory in the HDMI spec, in any of the versions.</p>

<p>It would be difficult for Silicon Image to require TV makers (especially those with non-SiI chips) to indicate the chip's 1080p feature to end-users. It is not too different from how consumers know whether their component analog input can handle 1080i, 720p, or 480p. It was not too long ago that 100% of component inputs handle 480i only. Then shortly after that there was 480i and 480p, etc.</p>

<p>That information should be in the specs of the TV, and the educated consumer (or the consumer that cares) is expected to know how to look for that detail.</p>

<p>However, it may be something that the HDMI consortium could endorse, and that has been considered but it is a contentious issue, "do not hold your breath", Silicon Image said.</p>

<p>HDMI specifies requirements for sources (DVD players, STBs), sinks (TVs, projectors), and repeaters (A/V receivers). It has no specifications for chips any more than it has specification for lines of software code. One cannot require a source that plays back a 480p-only source (such as a DVD player) to always be able to output 1080p. One cannot require a TV with 1080i resolution to always be able to input 1080p.</p>

<p>This is like saying "if a source has a component video output then it is required to support 1080p", "if a TV has an RF input then it is required to handle DirecTV". A cable STBs could not be required to support DTS-HD Master Audio if it has no ability to get that stream from anywhere and a consumer does not want that feature.</p>

<p>1080p is an optional feature of zero importance to most of the market, Silicon Image said (and I add "for now"). Moreover, they expanded "It would not be responsible to say that 100% of all TVs, DVD player, STBs, A/V Receivers etc. that have HDMI must also support 1080p. It would also be expensive and could cause HDMI to fail as a standard by forcing a significant cost increase to products that do not need 1080p to meet consumer needs."</p>

<p>In summary, HDMI will never require a device to support 1080p for the same reason that HD-DVD will never require every single HD-DVD manufactured to support DTS-HD Master Audio and for the same reason that DTS will never require that all DTS-HD streams have 12 discrete channels. Those options make zero sense for a large number of applications but are available for the applications that want to use them.</p>

<p><br />
<h2>1080p Input and Pass-thru Features</h2></p>

<p>Regarding HDMI chips, HDMI chips introduced on the first generation batch did not have 1080p capability; second and third-generation chips, could have such capability.</p>

<p>In order to future proof the video part of a 1080p HD system it is recommended that a consumer looks for a 1080p HDTV or other 1080p video equipment that has an HDMI transmitter/receiver chip capable of transporting 1080p, which would give the capability to accept 1080p from an external source, even if not needed initially.</p>

<p>According to Silicon Image, although the HDMI spec always supported 1080p/60fps, some manufacturers could have used old (or cheaper) HDMI chip sets unsuited for 1080p performance, but suitable to the needed applications, such as TVs that only display 1080i/720p, or DVD players for example, as mentioned before.</p>

<p>A manufacturer of 1080p TVs could have also decided to use the remaining of an existing inventory of (non-1080p) chips because the TV's internal design (before the 1080p image is actually displayed) might be unable to handle the bandwidth required for 1080p video processing. Most of first generation 1080p sets out there were not suited with 1080p inputs.</p>

<p>In addition to the 1080p input feature above, a 1080p pass-thru feature is useful on external video processor/scalers intended for 1080p purposes. Such feature allows for seamless switching of 1080p input sources (Blu-ray) with other non-1080p source devices (DVD, DTV) connected to the processor. The inputted 1080p content is sent to the output untouched (pass-thru), while the non-1080p content is processed and upconverted to 1080p, over the same outputs.</p>

<p>If the pass-thru feature is missing, an alternative connection would be to send the 1080p source signal via an HDMI cable directly to the display, and run everything else thru the scaler for 1080p video upconversion of those sources (if the scaler is capable); the scaler would then connect to the display using a parallel 1080p cable.</p>

<p>That alternative connection however, would require that the display device (HDTV, projector) have at least two 1080p capable inputs, a feature not usually found. In such case, if the display has only one 1080p input, a 1080p pass-thru feature on the scaler would be needed to switch 1080p and non-1080p sources without disturbing the quality of true 1080p sources.</p>

<p>In summary, for the 1080p consumer purist, the HDMI chips used in the chain of the video signal must be 1080p capable and the video processors on that chain must refrain from doing any unneeded video processing on the inputted 1080p signal, so it can be output as cleanly as possible to a 1080p display accepting 1080p, unless the frame rate change is needed to match the display native rate.</p>

<p>This brings us to another wrinkle on the 1080p Holy Grail.</p>

<p><br />
<h2>HDMI, all 1080p from start to end, but:</h2></p>

<p>One actual case about the matching of the frame rate could be unique feature of the Pioneer Elite Blu-ray reading a film based disc as 1080p 24fps, and outputting it cleanly as 24fps, but to a 1080P HDTV that only accepts 1080p 60fps (not 24fps).</p>

<p>Although both devices are 1080p compliant and with 1080p inputs and outputs, the frame rate does not match, so the player would need to convert the 1080p 24fps to 1080p 60fps for the HDTV to been able to accept it.</p>

<p>This is when the content originates from 24fps film; original 1080i60 video camera footage would not need a frame rate conversion for proper display, but need to be deinterlaced to 1080p60 for a 1080p HDTV that accepts 1080p.</p>

<p>As with the legacy DVD story of 480i or 480p of the last decade, one has to look into who does the deinterlacing job (the player or the TV) and how well it does such job; it could make a difference on the final quality of the displayed image, although now the connection is HDMI digital, not just component analog, one less conversion to do than the DVD legacy (digital to analog, to digital again, unless the display is a CRT).</p>

<p>Such frame rate conversion for the player/TV to match the way they interface could potentially introduce artifacts; the player most probably would do a 2:3 pulldown video processing to add twelve fields to the video cadence of 48 half frames/fields of the disc to obtain an interlaced version of 60i, and subsequently the player would deinterlace that video sequence to 60p frames, then output it as 60p frames, the 1080p TV would accept it as 60p (or not), and would display it as is.</p>

<p>Some displays are able to accept the 24fps as the Pioneer Elite Blu-ray player outputs it and display such image speeding up the frame rate as 72fps (3 times the 24) or 120fps (5 times the 24) without going thru the 60i/p format conversions.</p>

<p>If the player outputs only 1080i (or the 1080p HDTV only accepts 1080i) then the 1080p HDTV would have to do the deinterlacing job to display it as 1080p60; how good is the TV doing that job? Sounds familiar? Remember the first few generations of HDTVs in the late 90s displaying 480p from 480i DVDs? Is this particular 1080p equipment doing it this way because the installed HDMI chip does not support 1080p or because the equipment was designed that way regardless of the chip?</p>

<p>Although HDMI is used as the 1080p transport in all the cases, HDMI is not responsible for the artifacts that could possibly be introduced on frame rates and other conversions for the player and HDTV to understand each other.</p>

<p>For additional coverage of the 1080p subject, please review the series of articles published recently about the subject:</p>

<p><a href="http://www.hdtvmagazine.com/articles/2006/01/why_1080p.php">http://www.hdtvmagazine.com/articles/2006/01/why_1080p.php</a></p>

<p><a href="http://www.hdtvmagazine.com/articles/2006/01/why_1080p_-_par.php">http://www.hdtvmagazine.com/articles/2006/01/why_1080p_-_par.php</a></p>

<p><a href="http://www.hdtvmagazine.com/articles/2006/05/why_1080p_-_part_3_-_front_projectors.php">http://www.hdtvmagazine.com/articles/2006/05/why_1080p_-_part_3_-_front_projectors.php</a></p>

<p>You might also find related HDMI and 1080p information in the section of Hi-def DVD of the 2006 HDTV Technology report</p>

<p><a href="http://www.hdtvmagazine.com/reports/hdtv-technology-review.php">http://www.hdtvmagazine.com/reports/hdtv-technology-review.php</a></p>

<p><br />
Stay tuned for Part 7 "Type A and B HDMI Connectors"</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Rodolfo La Maestra</b>, <b>August 15, 2006 07:00 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 416
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
 				AND entry_id <> 416
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2006/08/hdmi_part_6_-_1080p_support.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
