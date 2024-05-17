<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 424";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Rodolfo La Maestra'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 424 AND placement_is_primary = 1";
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
	<meta name="keywords" content="testing program, silicon image, plc partners, simplay labs, cinema plc, simplay, Simplay, hdmi, program, HDMI, testing, Program, Corp, content, PLC, corp, plc, logo, Testing, cable, problems, people, equipment, standard, electronics" />
	<meta name="description" content="Over the last couple of years, Silicon Image is being doing efforts to help manufacturers test and deliver HDMI compliant equipment so consumers would experience the minimum of connectivity and compatibility problems.

As you might be aware by now, some people are having problems with the way HDMI has been implemented by some manufacturers. The problems are sometimes related to HDCP, sometimes related to limitations in the audio channels (we covered that), sometimes in the HDMI repeating ability of certain pieces of equipment, some related to the new Toshiba HD DVD player, some people with 1080p equipment using the wrong cable (and not necessarily that means a cheap cable), some due to the construction of the cable (and that could be a number of things), some plugs disconnect themselves from the back panels, some cable set-top-boxes not activating the HDMI outputs, etc." />
	<title>HDTV Magazine Articles - HDMI Part 10 - Meeting the Standard</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdmi_part_10_-_meeting_the_standard';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('HDMI Part 10 - Meeting the Standard'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/articles/2006/09/hdmi_part_10_-_meeting_the_standard.php";
		if ($author[img] != '' && 1 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Rodolfo La Maestra" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDMI Part 10 - Meeting the Standard</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Rodolfo La Maestra</b><br />
				<?=$author_title?>
				Posted on <b>September 12, 2006</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Technology">Technology</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2006/09/hdmi_part_10_-_meeting_the_standard.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/articles/2006/09/hdmi_part_10_-_meeting_the_standard.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/articles/2006/09/hdmi_part_10_-_meeting_the_standard.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2006/09/hdmi_part_10_-_meeting_the_standard.php&amp;phase=2&amp;title=HDMI%20Part%2010%20-%20Meeting%20the%20Standard&amp;bodytext=Over%20the%20last%20couple%20of%20years%2C%20Silicon%20Image%20is%20being%20doing%20efforts%20to%20help%20manufacturers%20test%20and%20deliver%20HDMI%20compliant%20equipment%20so%20consumers%20would%20experience%20the%20minimum%20of%20connectivity%20and%20compatibility%20problems.%0A%0AAs%20you%20might%20be%20aware%20by%20now%2C%20some%20people%20are%20having%20problems%20with%20the%20way%20HDMI%20has%20been%20implemented%20by%20some%20manufacturers.%20The%20problems%20are%20sometimes%20related%20to%20HDCP%2C%20sometimes%20related%20to%20limitations%20in%20the%20audio%20channels%20%28we%20covered%20that%29%2C%20sometimes%20in%20the%20HDMI%20repeating%20ability%20of%20certain%20pieces%20of%20equipment%2C%20some%20related%20to%20the%20new%20Toshiba%20HD%20DVD%20player%2C%20some%20people%20with%201080p%20equipment%20using%20the%20wrong%20cable%20%28and%20not%20necessarily%20that%20means%20a%20cheap%20cable%29%2C%20some%20due%20to%20the%20construction%20of%20the%20cable%20%28and%20that%20could%20be%20a%20number%20of%20things%29%2C%20some%20plugs%20disconnect%20themselves%20from%20the%20back%20panels%2C%20some%20cable%20set-top-boxes%20not%20activating%20the%20HDMI%20outputs%2C%20etc.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p>Over the last couple of years, Silicon Image is being doing efforts to help manufacturers test and deliver HDMI compliant equipment so consumers would experience the minimum of connectivity and compatibility problems.</p>

<p>As you might be aware by now, some people are having problems with the way HDMI has been implemented by some manufacturers. The problems are sometimes related to HDCP, sometimes related to limitations in the audio channels (we covered that), sometimes in the HDMI repeating ability of certain pieces of equipment, some related to the new Toshiba HD DVD player, some people with 1080p equipment using the wrong cable (and not necessarily that means a cheap cable), some due to the construction of the cable (and that could be a number of things), some plugs disconnect themselves from the back panels, some cable set-top-boxes not activating the HDMI outputs, etc.</p>

<p>However, most of the time people seem not to have problems with HDMI as implemented by manufacturers, or perhaps they did not notice or tested all the functionality yet.</p>

<p>For some people those problems are minor bumps they have learned to live with, other people have found a way around or the problem forced the return of the equipment or cable.</p>

<p>The bottom line is that if all cables and pieces of equipment were properly tested to make sure they meet the standard, one would suspect that the problems would not exist, and we all hope they would eventually go away, as manufacturers are gradually enrolling in the HDMI testing program; logos will be used to indicate the Category of the passed test (in cables for example), and to indicate the video and audio capabilities and features implemented in the equipment, rather than mentioning the HDMI version number.</p>

<p>Two programs were created to that effect, but not necessarily in parallel.</p>

<p><br />
<h2>PanelILink Cinema (PLC) Partners Program</h2></p>

<p>The program was designed to provide consumers with a simple means of identifying HDTVs and other consumer electronics devices capable of receiving and playing the most valuable digital content.</p>

<p>The PLC Partners logo assures consumers that HDMI systems bearing this logo have been tested for HDCP functionality and content-readiness, meaning they are interoperable and ready to receive and play premium digital content.</p>

<p>Sony, Mitsubishi, Samsung, Hitachi, LG, Sanyo and others have joined the program, which also has broad industry support from content providers The Walt Disney Co., Fox, Universal and Warner Bros. The first PLC-compliant TV, a 50" plasma from LG, was shown at CES 2005.</p>

<p>The following Silicon Image presentation highlights their efforts in implementing the PLC program (toward half of the presentation):</p>

<p><a href="http://www.siliconimage.com/presentations/hdmi/index.html">http://www.siliconimage.com/presentations/hdmi/index.html</a></p>

<p>The PanelLink Cinema (PLC) Partners Program was discontinued a year later (Jan 06) and the Simplay HD Testing Program was created, details further below.</p>

<p></p>

<h2>Simplay Labs</h2>

<p><img src="/images/simplay-hd.gif" alt="SimplayHD" align="left">In January 2006, Silicon Image announced the launch of Simplay Labs, LLC, and the Simplay HD(TM) Testing Program. According to the company:</p>

<p>"The Simplay HD Testing Program provides compatibility testing for high definition (HD) consumer electronics devices such as HDTVs, set-top boxes, audio/video (A/V) receivers and DVD players, helping manufacturers to achieve compatibility and deliver the highest-quality HDTV experience to consumers."</p>

<p>"Replacing the PanelLink Cinema (PLC) Partners Program, the Simplay HD Testing Program examines devices for compliance with the High-Definition Multimedia Interface(TM) (HDMI(TM)) and High-bandwidth Digital Content Protection (HDCP) specifications, as well as for compatibility with a suite of other devices that have passed the Simplay HD Testing Program. Products that have demonstrated adherence to the Simplay HD Compatibility Test Specification (CTS) in testing by Simplay Labs are identified with the Simplay HD logo.</p>

<p>Simplay HD participants have three testing service options:</p>

<p> -- Simplay Standard -- No annual fee, standard testing fees, standard<br />
 scheduling, and optional logo usage.<br />
 -- Simplay Preferred -- No annual fee, 25 percent discount on testing fees, priority<br />
 scheduling, logo usage, and website listing.<br />
 -- Simplay Elite -- $10,000 annual participation fee, four free tests, 25 percent<br />
 discount on subsequent tests, priority scheduling, logo usage, prominent<br />
 website listing, and five hours of test support services per product."</p>

<p>Initial participants in the Simplay HD Testing Program include BenQ Corp., Hitachi Ltd., LG Electronics, MediaTek Inc., Mitsubishi Digital Electronics, Monster Cable, Pace Micro Technology PLC, Renesas Technology Corp., Samsung Electronics, Sanyo Electric Company, Ltd., Scientific-Atlanta Inc., SerComm Corp., Silicon Image, Sony Corp., Sunplus Technology Co., TTE Corp, and Tweeter. The program also has broad industry support from content providers Fox, The Walt Disney Company, Universal Studios, and Warner Bros.</p>

<p>Upon its creation, Mitsubishi Digital Electronics, Sanyo Electric Company, Ltd., Sony Corp., and TTE Corp. (under the Thomson brand) have each submitted HDTVs for Simplay HD testing, ensuring that they are compatible and designed to access premium HD content. For a complete listing of Simplay HD verified products, please visit <a href="http://www.simplayhd.com/">http://www.simplayhd.com/</a>.</p>

<p>Now that HDMI receivers have fallen below the $400 price point, Joseph Lee said, we expect consumers will demand that the other HDMI devices they own (such as HD set top boxes) should meet the higher quality standard which the Simplay HD logo represents. Simplay verified products are required and tested to support all the capabilities available from the analog & traditional S/PDIF outputs on the HDMI output.</p>

<p>The Simplay HD program takes all such guesswork out of the equation, and allows the consumer to have the peace of mind that they are buying an HD component that will yield the best HD audio & video experience that they expect.</p>

<p>With this part 10 I am closing this series of articles, it might happen that in the near future we would need to add more parts to the series to cover areas not already covered by these articles, or to cover updates.</p>

<p>I would like to extend my appreciation to the people from Silicon Image, Simplay Labs, and HDMI Licensing that contributed to this work.</p>

<p>I hope this HDMI series of articles was helpful and informative to you.</p>

<p>RLM</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Rodolfo La Maestra</b>, <b>September 12, 2006 07:41 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 424
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
 				AND entry_id <> 424
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2006/09/hdmi_part_10_-_meeting_the_standard.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
