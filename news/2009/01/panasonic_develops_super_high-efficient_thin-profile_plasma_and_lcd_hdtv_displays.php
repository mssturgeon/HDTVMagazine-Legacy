<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');

	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1636";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);

	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";

	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1636 AND placement_is_primary = 1";
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
	<meta name="keywords" content="moving picture, picture resolution, resolution lines, newly developed, picture quality, panasonic, Panasonic, panel, pdp, PDP, picture, inch, moving, high, technology, thin, LCD, lcd, resolution, TVs, tvs, compared, developed, energy, world" />
	<meta name="description" content="Panasonic, the industry and technology leader in HDTVs, has developed new thin-profile display panel technologies for both Plasma (PDPs) and LCD HDTVs, achieving further advancements in picture quality and environmental performance. These prototypes will be featured at the 2009 International Consumer Electronics Show (CES) this week.

The newly developed NeoPDP technology has been incorporated into two types of PDPs. The first is..." />
	<title>HDTV Magazine Bulletins - Panasonic Develops Super High-Efficient Thin-Profile Plasma and LCD HDTV Displays</title>
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

		$base_url = strleftback(PHP_SELF, '/') . '/panasonic_develops_super_high-efficient_thin-profile_plasma_and_lcd_hdtv_displays';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Panasonic Develops Super High-Efficient Thin-Profile Plasma and LCD HDTV Displays'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2009/01/panasonic_develops_super_high-efficient_thin-profile_plasma_and_lcd_hdtv_displays.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Panasonic Develops Super High-Efficient Thin-Profile Plasma and LCD HDTV Displays</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>January  7, 2009</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Products & Equipment">Products & Equipment</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2009/01/panasonic_develops_super_high-efficient_thin-profile_plasma_and_lcd_hdtv_displays.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2009/01/panasonic_develops_super_high-efficient_thin-profile_plasma_and_lcd_hdtv_displays.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2009/01/panasonic_develops_super_high-efficient_thin-profile_plasma_and_lcd_hdtv_displays.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2009/01/panasonic_develops_super_high-efficient_thin-profile_plasma_and_lcd_hdtv_displays.php&amp;phase=2&amp;title=Panasonic%20Develops%20Super%20High-Efficient%20Thin-Profile%20Plasma%20and%20LCD%20HDTV%20Displays&amp;bodytext=Panasonic%2C%20the%20industry%20and%20technology%20leader%20in%20HDTVs%2C%20has%20developed%20new%20thin-profile%20display%20panel%20technologies%20for%20both%20Plasma%20%28PDPs%29%20and%20LCD%20HDTVs%2C%20achieving%20further%20advancements%20in%20picture%20quality%20and%20environmental%20performance.%20These%20prototypes%20will%20be%20featured%20at%20the%202009%20International%20Consumer%20Electronics%20Show%20%28CES%29%20this%20week.%0A%0AThe%20newly%20developed%20NeoPDP%20technology%20has%20been%20incorporated%20into%20two%20types%20of%20PDPs.%20The%20first%20is...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p class="prtitle">Panasonic Develops Super High-Efficient Thin-Profile Plasma and LCD HDTV Displays</p>

<center><i>Prototypes On Display At 2009 International CES</i></center><br />
<br />

<p><B>LAS VEGAS, Jan. 7 /PRNewswire-FirstCall/</B> -- Panasonic, the industry and technology leader in HDTVs, has developed new thin-profile display panel technologies for both Plasma (PDPs) and LCD HDTVs, achieving further advancements in picture quality and environmental performance. These prototypes will be featured at the 2009 International Consumer Electronics Show (CES) this week.</p>

<p>The newly developed NeoPDP technology has been incorporated into two types of PDPs. The first is a super high-efficiency 42-inch PDP that achieves triple luminance efficiency, while reducing the power consumption to 1/3 of the 2007 models*(1) yet achieving the same brightness. The second is an ultra-thin 50-inch PDP just 8.8 mm (approximately 1/3 inch) in profile*(2). This ultra-thin panel delivers the world's highest moving picture resolution*(3) of 1080 lines.</p>

<p>Panasonic's newly-developed NeoLCD technology is integrated into a super energy-efficient 90kWh per year 37-inch LCD panel, that achieves moving picture resolution*(3) of 1000 lines, close to that of a PDP. It has the lowest energy consumption of any LCD HDTV in the world*(4), cutting the energy requirement almost in half compared to the previous model.</p>

<p>With the growth of digital broadcasting services worldwide, the flat-panel TV market has been rapidly increasing and is expected to exceed half of the global TV demand in the fiscal year 2008 ending March 2009. The TV market is expected to continue its growth with the increase of digital broadcasting, the increased popularity of flat panel TVs, continuing need for replacement TVs and increased demand for business, educational and medical applications. As people's awareness of the need to improve the environment increases, it is vitally important that TVs be designed with their impact on the environment in mind.</p>

<p>Panasonic's newly developed technologies have achieved the world's highest level picture quality as well as exceptional environmental performance through energy conservation and thin panel design in both PDPs and LCDs. Panasonic names these technological developments "NeoPDP(eco)" and "NeoLCD(eco)". By taking advantage of the vertically integrated business model for PDPs and LCDs, Panasonic continues to accelerate its technology development, according to characteristics of each device, in order to facilitate the continuing evolution of flat-panel VIERA TVs and to respond to the ever-diversifying needs of its global customers.</p>

<p><br />
<B>About NeoPDP</B></p>

<p>As Plasma is a self-illuminating device - capable of adjusting luminance levels according to scene brightness - it is inherently advantageous in both picture quality and environmental performance. PDP TVs deliver dynamic contrast, high moving picture resolutions, true-to-life color reproduction, a wide viewing angle and energy efficiency. In 2006, Panasonic led the world in eliminating lead from PDP panels. With a thorough review of fundamental technologies, Panasonic developed and unveiled revolutionary NeoPDP panels at 2008 International CES, including panels with double energy-efficiency*(5), a 24.7 mm (less than one inch) super-thin full-flat PDP, and an ultra-large 150-inch PDP.</p>

<p>Panasonic continues to refine its base technologies. Employing newly developed materials, such as discharge gas and phosphor for electron generation source has improved discharge efficiency and cell structure. The introduction of a new circuit drive method has cut the electricity loss to one third and enabled low-voltage drive. The triple luminance efficiency technology has reduced the number of components and enabled a higher integration of components. As a result, the technology has been incorporated into a 42-inch Full HD PDP.</p>

<p>Further advancements made to the panel structure and circuit layout have lead to an even thinner profile. While a full HD PDP has a moving picture resolution of more than 900 lines, the newly developed drive technology and materials to shorten the afterglow have attained the world's highest moving resolution of 1080 lines, realizing precise reproduction of full HD programs of any speed without loss of detail. Panasonic's new 50-inch HD PDP is just 8.8mm (approximately 1/3 inch) deep, with superb picture quality and thin panel design.</p>

<p>The super-thin panel also allows users more setup flexibility including wall mounting and suspension from the ceiling. And with the WirelessHD-based transmission system, setup flexibility can be enjoyed even further. With a wide viewing angle, high contrast ratios, and newly achieved moving resolution of 1080 lines, PDP picture viewing is further enhanced for all types of settings.</p>

<p><br />
<B>About NeoLCD</B></p>

<p>IPS alpha LCD panels provide a high light transmission rate and low power consumption due to its panel structure. In addition, these panels feature high moving picture resolution and a wide viewing angle for displaying images that are truly natural when viewed from any angle.</p>

<p>Adoption of the new IPS alpha panel with improved light transmission rate*(6) and LED backlight driven by a unique technology allows for local lighting control according to the brightness of the picture scenes. With precise control, the panel dramatically improves contrast with its tight black expression. Moreover, this panel achieves exceptional energy saving performance by cutting power consumption in half compared with the 2008 model*(7) and consumes only 90 kWh per year, which is the world's lowest power consumption level.</p>

<p>The unique high-speed drive technology for LED backlight, developed by Panasonic, allows for precise control with 1000 lines of moving picture resolution, far exceeding the current models. As a result, the panel is capable of reproducing fast-moving pictures with unparalleled clarity and high contrast ratios that are close to that of PDP panels.</p>

<p><br />
<B>About Panasonic TVs</B></p>

<p>Panasonic started its R&D of TV in 1935, and began producing 17-inch black-and-white TVs in November 1952, the industry's largest size at that time. Panasonic achieved production of 300 millionth TV set in October 2008, which marks the 56th year since the company began producing them in 1952, which made Panasonic the world's first TV manufacturer to reach this level of production.</p>

<p>  *1: Compared with Panasonic's TH-42PZ750SK<br />
  *2: Referring to thinnest part of TV profile</p>

<p>*3: Moving picture resolution indicates the motion display performance by number of line which human eyes can recognize. (Measured by Advanced PDP Development Center Corporation Method)</p>

<p>*4: As of January 8, 2009, compared within the 37-inch full high-definition class LCD TVs</p>

<p>*5: Compared with Panasonic's TH-42PZ750SK</p>

<p>*6: 1.1 times better rate compared with the conventional IPS Alpha LCD panel, 1.8 times compared with LCDs of other methods</p>

<p>  *7: Compared with Panasonic's TH-37LZ85</p>

<p><br />
<B>About Panasonic</B></p>

<p>Panasonic Corporation is a worldwide leader in the development and manufacture of electronic products for a wide range of consumer, business, and industrial needs. Based in Osaka, Japan, the company recorded consolidated net sales of 9.07 trillion yen (US$90.7 billion) for the year ended March 31, 2008. The company's shares are listed on the Tokyo, Osaka, Nagoya and New York (NYSE:PC) stock exchanges. For more information on the company and the Panasonic brand, visit the company's website at http://panasonic.net/.</p>

<p>Source: Panasonic </p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>January  7, 2009 05:06 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 1636
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
 				AND entry_id <> 1636
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2009/01/panasonic_develops_super_high-efficient_thin-profile_plasma_and_lcd_hdtv_displays.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
