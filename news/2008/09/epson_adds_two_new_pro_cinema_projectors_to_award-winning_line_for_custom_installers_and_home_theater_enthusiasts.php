<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1537";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1537 AND placement_is_primary = 1";
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
	<meta name="keywords" content="pro cinema, high definition, home theater, epson america, lcd technology, epson, Epson, cinema, Cinema, Pro, pro, high, lcd, color, LCD, technology, features, new, projectors, system, projector, quality, home, picture, installers" />
	<meta name="description" content="Epson America announces today at CEDIA Expo 2008 two home theater projectors designed to meet the expanding needs of both custom installers and home theater enthusiasts. The PowerLite&amp;reg; Pro Cinema 7100 and 7500 UB feature true 1080p (1,920 x 1,080 pixels) resolution using the latest generation 3LCD chips with D7 technology to deliver substantially higher contrast and brightness. These projectors also give professional installers the benefits of ISF Certification and other features that help ensure their clients' projectors deliver the most brilliant image quality possible.
 
Epson, the number-one selling projector brand worldwidei, has packed the Pro Cinema 7100 and 7500 UB with advanced 3LCD technologies and impressive features to offer top-of-the-line projectors in each of their respective categories..." />
	<title>HDTV Magazine Bulletins - Epson Adds Two New Pro Cinema Projectors to Award-Winning Line for Custom Installers and Home Theater Enthusiasts</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/epson_adds_two_new_pro_cinema_projectors_to_award-winning_line_for_custom_installers_and_home_theater_enthusiasts';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Epson Adds Two New Pro Cinema Projectors to Award-Winning Line for Custom Installers and Home Theater Enthusiasts'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2008/09/epson_adds_two_new_pro_cinema_projectors_to_award-winning_line_for_custom_installers_and_home_theater_enthusiasts.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Epson Adds Two New Pro Cinema Projectors to Award-Winning Line for Custom Installers and Home Theater Enthusiasts</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>September  4, 2008</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Products & Equipment">Products & Equipment</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2008/09/epson_adds_two_new_pro_cinema_projectors_to_award-winning_line_for_custom_installers_and_home_theater_enthusiasts.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2008/09/epson_adds_two_new_pro_cinema_projectors_to_award-winning_line_for_custom_installers_and_home_theater_enthusiasts.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2008/09/epson_adds_two_new_pro_cinema_projectors_to_award-winning_line_for_custom_installers_and_home_theater_enthusiasts.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2008/09/epson_adds_two_new_pro_cinema_projectors_to_award-winning_line_for_custom_installers_and_home_theater_enthusiasts.php&amp;phase=2&amp;title=Epson%20Adds%20Two%20New%20Pro%20Cinema%20Projectors%20to%20Award-Winning%20Line%20for%20Custom%20Installers%20and%20Home%20Theater%20Enthusiasts&amp;bodytext=Epson%20America%20announces%20today%20at%20CEDIA%20Expo%202008%20two%20home%20theater%20projectors%20designed%20to%20meet%20the%20expanding%20needs%20of%20both%20custom%20installers%20and%20home%20theater%20enthusiasts.%20The%20PowerLite%26reg%3B%20Pro%20Cinema%207100%20and%207500%20UB%20feature%20true%201080p%20%281%2C920%20x%201%2C080%20pixels%29%20resolution%20using%20the%20latest%20generation%203LCD%20chips%20with%20D7%20technology%20to%20deliver%20substantially%20higher%20contrast%20and%20brightness.%20These%20projectors%20also%20give%20professional%20installers%20the%20benefits%20of%20ISF%20Certification%20and%20other%20features%20that%20help%20ensure%20their%20clients%27%20projectors%20deliver%20the%20most%20brilliant%20image%20quality%20possible.%0A%20%0AEpson%2C%20the%20number-one%20selling%20projector%20brand%20worldwidei%2C%20has%20packed%20the%20Pro%20Cinema%207100%20and%207500%20UB%20with%20advanced%203LCD%20technologies%20and%20impressive%20features%20to%20offer%20top-of-the-line%20projectors%20in%20each%20of%20their%20respective%20categories...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p class="prtitle">Epson Adds Two New Pro Cinema Projectors to Award-Winning Line for Custom Installers and Home Theater Enthusiasts</p>

<center><i>Epson PowerLite Pro Cinema 7100 and 7500 UB Projectors Give Installers Outstanding Package with Extraordinary Image Quality, High-Performance and Value-Add Features at a Superior Price</i></center><br />
<br />
 
<B>DENVER, Colo. - Sept. 4, 2008 </B>- Epson America announces today at CEDIA Expo 2008 two home theater projectors designed to meet the expanding needs of both custom installers and home theater enthusiasts. The PowerLite&reg; Pro Cinema 7100 and 7500 UB feature true 1080p (1,920 x 1,080 pixels) resolution using the latest generation 3LCD chips with D7 technology to deliver substantially higher contrast and brightness. These projectors also give professional installers the benefits of ISF Certification and other features that help ensure their clients' projectors deliver the most brilliant image quality possible.
 
Epson, the number-one selling projector brand worldwidei, has packed the Pro Cinema 7100 and 7500 UB with advanced 3LCD technologies and impressive features to offer top-of-the-line projectors in each of their respective categories, sub $3,000 and sub-$4,500. Epson's D7 high definition 3LCD technology is at the core of each projector's optical imaging engine, delivering realistic and vibrant colors without the possibility of color break-up, unlike projectors that use a spinning color wheel with a white segment. This latest technology delivers significantly improved contrast, with the Pro Cinema 7100 attaining an 18,000:1 dynamic contrast ratio and the Pro Cinema 7500 UB reaching an industry unprecedented 75,000:1, resulting in brighter whites and darker blacks. 
 
"Epson is committed to providing the custom installation channel with high-performing, high quality products that are designed to support enormously successful businesses," said Rajeev Mishra, director, Projector Marketing and Development, Epson America. "The latest additions to the Pro Cinema home theater line allow installers to provide their customers with the greatest performance and quality combination available today."
 

<p><B>Flagship Epson Pro Cinema 7500 UB</B></p>

<p>Housed in a newly-designed sleek all-black casing, the flagship Pro Cinema 7500 UB features a host of technological refinements and upgrades to provide the ultimate at-home big-screen experience, including C2Fine &trade; technology for visibly increased high definition picture detail, Epson's exclusive UltraBlack&trade; technology, and new Vertical Alignment technology that together combine to deliver a new industry standard of deep blacks and impressive brightness and contrast.<br />
 <br />
For optimum picture detail with both movie- and video-originated content, the Pro Cinema 7500 UB also adds Epson's new FineFrameTM technology to deliver substantially smoother and sharper motion pictures while eliminating judder. Additionally, Epson's new 12-bit 3LCD driver technology provides a dramatically increased color gamut over 10-bit drivers, which translates into 68.72 billion available colors. This eliminates gradation artifacts to provide a much smoother, natural-looking picture. The Pro Cinema 7500 UB is also equipped with Silicon Optix's HQV Reon-VX scaling and deinterlacing video processor for true four-field deinterlacing and scaling of 1080i HD signals, and features impressive noise reduction tools to eliminate mosquito and block noise, along with multi-level contrast enhancement and other picture improvement options. Typically found only on high-end high definition broadcast monitors, the Pro Cinema 7500 UB also features Color Space selection, which allows a user to select between the three industry color space standards - SMPTE-C for standard definition (Rec.601), HD (Rec. 709) for high definition, and EBU to match the European PAL video standard.<br />
 <br />
The Pro Cinema 7500 UB offers the ultimate widescreen high definition experience with anamorphic lens compatibility and "vertical stretch" picture mode. With an optional external anamorphic lens (available from Epson) , the viewer can use the Pro Cinema 7500 UB with a cinematic-sized front projection widescreen for true 2.35:1 and 2.40:1 ultra widescreen viewing, eliminating the black bars above and below the picture for a full theatrical image.<br />
 <br />
Full-Featured Epson Pro Cinema 7100<br />
Featuring a stylish black and silver design, the new PowerLite Pro Cinema 7100 is a high-performing projector featuring native 1080p resolution, higher brightness of up to 1,800 lumens and superb contrast of up to 18,000:1. For easy set-up and calibration, the Pro Cinema 7100 offers ISF Day and Night modes which provide for easy one-button switching between picture modes and lamp output, as well as Epson's Color Isolation system to allow for quick, easy and accurate fine-tuning of color saturation and hue without the need for blue and red optical filters.<br />
 <br />
<B>Shared Features of the Pro Cinema Line</B></p>

<p> The Pro Cinema home theater projector line integrates a range of value-add features with high-performance and versatility, including:</p>

<p>    *<br />
      Dynamic Iris System:  Exclusive system makes automatic light output adjustments at up to 120 times per second - ideal for fast-action movies.<br />
    *<br />
      Cinema Filter: Unique feature delivers larger color space for improved color accuracy and a more film-like image.<br />
    *<br />
      OptiCinema&trade; Multi-Lens Optics System: Developed by Epson and Fujinon - the leading provider of precision optics to the digital film and HDTV camera industry - the OptiCinema lens delivers clean, precise edges with consistent image quality across the entire screen while providing users with more options and flexibility in terms of where they wish to install the projector.<br />
    *<br />
      E-TORL&reg; (Epson Twin Optics Reflection Lamp): Exclusive and newly-updated 200 watt high efficiency light source delivers optimum light uniformity and increased white and color light output for larger screen sizes (more than ten feet diagonal); both models come equipped with a spare lamp.  <br />
    *<br />
      ISF Certification:  Suite of video calibration tools allow installers and calibrators to fine-tune picture quality and match output with front projection screens.<br />
    *<br />
      New Airflow System: Enhanced airflow system contributes to more efficient use of power, reduced cool-down periods and lower fan speeds that result in quieter operation (only 22 db); new system also takes advantage of an advanced air filtration system with 98 percent efficiency for longer filter life.<br />
    *<br />
      Input Options: Panel features high definition component video input, dual HDMI 1.3a digital inputs, S-video input, composite video input, and VGA-type RGB input (D-sub 15).<br />
    *<br />
      Installation Versatility: Installer-friendly features include a ceiling mount, reversible front panel Epson logo that can be reoriented for various mounting positions, included rear panel cable cover to hide wires from the input panel and an integrated cable hook to ensure connections remain secure.</p>

<p> <br />
<B>Availability and Support</B></p>

<p>The Epson Pro Cinema 7100 is available in November and the Pro Cinema 7500 UB will be available in December through authorized Epson projector dealers and select retail outlets; pricing will be available at that time. The projector also comes with the service and support only Epson can offer, including a three-year limited warranty with toll-free access to PrivateLineSM, Epson's priority technical support, and free overnight exchange with ExtraCareSM Home Service.<br />
 </p>

<p><B>About 3LCD Technology</B></p>

<p>3LCD is the world's leading projection technology, delivering unbelievably bright and natural color, amazing detail and road-tested reliability. Using an advanced, 3-chip optical engine, 3LCD offers full-time color for brilliant quality images without the possibility of color break-up. 3LCD is based on LCD technology, which is used by leading manufacturers worldwide for the ultimate viewing experience in flat panel TVs and projectors. To find out why more users choose 3LCD than all other projection technologies combined and to get the latest list of leading companies offering 3LCD technology in their products, visit the 3LCD website at http://www.3LCD.com.<br />
 </p>

<p><B>About Epson America Inc.</B></p>

<p>Epson offers an extensive array of award-winning image capture and image output products for the consumer, business, photography, and graphic arts markets. The company is also a leading supplier of value-added point-of-sale (POS) printers and transaction terminals for the retail market. Founded in 1975, Epson America Inc. is the U.S. affiliate of Japan-based Seiko Epson Corporation, a global manufacturer and supplier of high-quality technology products that meet customer demands for increased functionality, compactness, systems integration and energy efficiency. Epson America Inc. is headquartered in Long Beach, Calif.</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>September  4, 2008 02:18 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 1537
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
 				AND entry_id <> 1537
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2008/09/epson_adds_two_new_pro_cinema_projectors_to_award-winning_line_for_custom_installers_and_home_theater_enthusiasts.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
