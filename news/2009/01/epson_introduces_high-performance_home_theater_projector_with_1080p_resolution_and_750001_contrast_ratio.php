<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');

	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1660";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);

	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";

	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1660 AND placement_is_primary = 1";
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
	<meta name="keywords" content="home cinema, epson america, lcd technology, america inc, light output, epson, Epson, home, lcd, Home, LCD, cinema, projector, color, technology, Cinema, theater, contrast, america, America, output, light, system, features, delivers" />
	<meta name="description" content="Building on its leadership position as the number-one selling projector brand worldwide(ii), Epson today announced the latest addition to its award-winning line of 3LCD 1080p front projectors, the PowerLite(R) Home Cinema 6500 UB. Offering true 1080p (1,920 x 1,080 pixels) resolution with the latest 3LCD D7 chip set for significantly higher contrast, a built-in HQV Reon-VX processor by Silicon Optix, and a wide range of new performance advantages, the Home Cinema 6500 UB delivers an outstanding viewing experience for..." />
	<title>HDTV Magazine Bulletins - Epson Introduces High-Performance Home Theater Projector With 1080p Resolution and 75,000:1 Contrast Ratio</title>
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

		$base_url = strleftback(PHP_SELF, '/') . '/epson_introduces_high-performance_home_theater_projector_with_1080p_resolution_and_750001_contrast_ratio';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Epson Introduces High-Performance Home Theater Projector With 1080p Resolution and 75,000:1 Contrast Ratio'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2009/01/epson_introduces_high-performance_home_theater_projector_with_1080p_resolution_and_750001_contrast_ratio.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Epson Introduces High-Performance Home Theater Projector With 1080p Resolution and 75,000:1 Contrast Ratio</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>January  9, 2009</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Products & Equipment">Products & Equipment</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2009/01/epson_introduces_high-performance_home_theater_projector_with_1080p_resolution_and_750001_contrast_ratio.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2009/01/epson_introduces_high-performance_home_theater_projector_with_1080p_resolution_and_750001_contrast_ratio.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2009/01/epson_introduces_high-performance_home_theater_projector_with_1080p_resolution_and_750001_contrast_ratio.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2009/01/epson_introduces_high-performance_home_theater_projector_with_1080p_resolution_and_750001_contrast_ratio.php&amp;phase=2&amp;title=Epson%20Introduces%20High-Performance%20Home%20Theater%20Projector%20With%201080p%20Resolution%20and%2075%2C000%3A1%20Contrast%20Ratio&amp;bodytext=Building%20on%20its%20leadership%20position%20as%20the%20number-one%20selling%20projector%20brand%20worldwide%28ii%29%2C%20Epson%20today%20announced%20the%20latest%20addition%20to%20its%20award-winning%20line%20of%203LCD%201080p%20front%20projectors%2C%20the%20PowerLite%28R%29%20Home%20Cinema%206500%20UB.%20Offering%20true%201080p%20%281%2C920%20x%201%2C080%20pixels%29%20resolution%20with%20the%20latest%203LCD%20D7%20chip%20set%20for%20significantly%20higher%20contrast%2C%20a%20built-in%20HQV%20Reon-VX%20processor%20by%20Silicon%20Optix%2C%20and%20a%20wide%20range%20of%20new%20performance%20advantages%2C%20the%20Home%20Cinema%206500%20UB%20delivers%20an%20outstanding%20viewing%20experience%20for...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p class="prtitle">Epson Introduces High-Performance Home Theater Projector With 1080p Resolution and 75,000:1 Contrast Ratio</p>

<center><i>New Epson PowerLite Home Cinema 6500 UB Delivers Brilliant Image Quality and Unrivaled Contrast in its Class(i) for Only $2,999</i></center><br />
<br />

<p><B>LAS VEGAS, Jan. 8 /PRNewswire/ -- (International CES, South Hall 1 Booth 21150) </B>- Building on its leadership position as the number-one selling projector brand worldwide(ii), Epson today announced the latest addition to its award-winning line of 3LCD 1080p front projectors, the PowerLite(R) Home Cinema 6500 UB. Offering true 1080p (1,920 x 1,080 pixels) resolution with the latest 3LCD D7 chip set for significantly higher contrast, a built-in HQV Reon-VX processor by Silicon Optix, and a wide range of new performance advantages, the Home Cinema 6500 UB delivers an outstanding viewing experience for home entertainment and AV enthusiasts.</p>

<p>Featuring 3LCD technology with Epson's new "Crystal Clear Fine" D7 chip set and UltraBlack(TM) (Vertical Alignment) technology, the Home Cinema 6500 UB displays superb black levels and extraordinary details. 3LCD technology enables the projector to deliver realistic, vibrant colors without the possibility of color break-up, unlike single-chip projectors, which use a spinning color wheel to create their colors. It also allows the Home Cinema 6500 UB to provide a significantly enhanced contrast ratio - up to an unrivaled 75,000:1 - to deliver darker blacks and brighter whites.</p>

<p>"With top-of-the-line features and a sub-$3,000 price point, the Home Cinema 6500 UB nicely complements Epson's current lineup of home entertainment offerings," said Marge Ang, senior product manager, Epson America. "We have added tremendous performance enhancements to this projector to provide home theater buffs with unmatched features and advantages in this price range."</p>

<p>An exclusive Dynamic Iris system with automatic light output adjustments up to 60 times per second makes the Home Cinema 6500 UB projector ideal for fast-action movies and sports. It also features Epson's exclusive Cinema Filter, which delivers a larger color space for true-to-life colors. In addition, Epson's 12-bit 3LCD driver technology increases the projector's color gamut to 68.72 billion available colors for an enhanced viewing experience.</p>

<p>The Home Cinema 6500 UB also features a unique OptiCinema(TM) multi-lens system, developed by Fujinon, the leading provider of precision optics to the digital film and HDTV camera industry. This lens system allows for sharp, clear images, and precise focus and adjustment flexibility with the lens shift feature. The projector is also equipped with Silicon Optix's HQV Reon-VX scaling and deinterlacing video processor to reduce mosquito and block noise, while adding multi-level contrast enhancement and other picture improvement options. In addition, Epson's new FineFrame(TM) technology delivers smoother and sharper motion pictures, while virtually eliminating judder to provide optimum picture detail for viewers.</p>

<p><br />
<B>Additional Home Cinema 6500 UB features and benefits include:</B></p>

<p>  --  Brightness of up to 1,600 lumens of white light output and color light<br />
      output(iii)<br />
  --  Improved airflow system for more efficient use of power, reduced<br />
      cool-down periods and lower fan speeds resulting in noise as low as 22<br />
      dB<br />
  --  Advanced air filtration system with large surface area for greater<br />
      efficiency<br />
  --  New, brighter 200W E-TORL(R) lamp delivers high brightness using less<br />
      energy for up to 4000 hours of lamp life(iv)<br />
  --  Dual HDMI 1.3a digital inputs, S-video input, composite video input,<br />
      and VGA-type RGB input (D-sub 15)<br />
  --  Manual lens shift of 100 percent maximum up/down (vertical) and 50<br />
      percent maximum left/right (horizontal) to accommodate a wide range of<br />
      room configurations<br />
  --  6 Color Modes - Dynamic, Living Room, Natural, Theater, Theater Black<br />
      1, Theater Black 2, x.v.Color<br />
  --  Stylish, sleek design featuring white casing and reversible Epson logo<br />
      on the front panel allow the projector to be reoriented for tabletop,<br />
      shelf and ceiling mounting</p>

<p><br />
<B>Availability and Support</B></p>

<p>The Home Cinema 6500 UB will be available in late-January through authorized Epson projector dealers for an estimated street price of $2,999. The projector also comes with Epson's award-winning service and support, including with a two-year limited warranty; 90 day lamp warranty; toll-free access to PrivateLine(R), Epson's priority technical support; and free overnight exchange with Extra Care(SM) Home Service.</p>

<p><br />
<B>About 3LCD Technology</B></p>

<p>3LCD is the world's leading projection technology, delivering unbelievably bright and natural color, amazing detail and road-tested reliability. Using an advanced, 3-chip optical engine, 3LCD offers full-time color for brilliant quality images without the possibility of color break-up. 3LCD is based on LCD technology, which is used by leading manufacturers worldwide for the ultimate viewing experience in flat panel TVs and projectors. To find out why more users choose 3LCD than any other projection technology and to get the latest list of leading companies offering 3LCD technology in their products, visit the 3LCD website at http://www.3lcd.com/</p>

<p><br />
<B>About Epson America Inc.</B></p>

<p>Epson offers an extensive array of award-winning image capture and image output products for the consumer, business, photography, and graphic arts markets. The company is also a leading supplier of value-added point-of-sale (POS) printers and transaction terminals for the retail market. Founded in 1975, Epson America, Inc. is the U.S. affiliate of Japan-based Seiko Epson Corporation, a global manufacturer and supplier of high-quality technology products that meet customer demands for increased functionality, compactness, systems integration and energy efficiency. Epson America, Inc. is headquartered in Long Beach, Calif.</p>

<p>Note: Epson and E-TORL are registered trademarks and Epson Exceed Your Vision is a registered logomark of Seiko Epson Corporation. PrivateLine and PowerLite are registered trademarks, UltraBlack, OptiCinema and FineFrame are trademarks and Extra Care is a service mark of Epson America, Inc. All other product and brand names are trademarks and/or registered trademarks of their respective companies. Epson disclaims any and all rights in these marks.</p>

<p>  (i)   Based on home theater projectors under $4,000<br />
  (ii)  Based upon Q2 2008 worldwide front projection market share<br />
        estimates from Pacific Media Associates<br />
  (iii) Light output varies depending on modes (color and white light<br />
        output)<br />
  (iv)  Lamp life will vary depending on mode selected, environmental<br />
        conditions and usage.  Lamp brightness decreases over time.</p>

<p>Source: Epson America Inc. </p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>January  9, 2009 08:25 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 1660
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
 				AND entry_id <> 1660
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2009/01/epson_introduces_high-performance_home_theater_projector_with_1080p_resolution_and_750001_contrast_ratio.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
