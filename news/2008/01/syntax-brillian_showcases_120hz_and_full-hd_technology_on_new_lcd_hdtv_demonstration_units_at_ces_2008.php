<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 838";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 838 AND placement_is_primary = 1";
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
	<meta name="keywords" content="syntax brillian, new olevia, lcd hdtvs, olevia lcd, olevia booth, olevia, Olevia, new, LCD, lcd, brillian, Brillian, syntax, Syntax, products, technology, hdtvs, HDTVs, hdmi, company, HDMI, tilt, New, type, booth" />
	<meta name="description" content="Syntax-Brillian Corporation (Nasdaq:BRLC) today announced that at CES 2008 it will be exhibiting a diverse range of new Olevia&amp;trade; LCD HDTVs featuring the latest in display technology hardware and feature enhancements combining 120Hz Motion Estimation/Motion Compensation (ME/MC) technology with full-HD 1080p resolution, new industrial designs and firmware upgradeability via USB. These new technology demonstration units will be on exhibit from January 7 - 10, 2008 in the Olevia&amp;trade; booth #20401, Las Vegas Convention Center, South Hall.

At the Olevia&amp;trade; booth, visitors can experience..." />
	<title>HDTV Magazine Bulletins - Syntax-Brillian Showcases 120Hz and Full-HD Technology on New LCD HDTV Demonstration Units at CES 2008</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/syntax-brillian_showcases_120hz_and_full-hd_technology_on_new_lcd_hdtv_demonstration_units_at_ces_2008';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Syntax-Brillian Showcases 120Hz and Full-HD Technology on New LCD HDTV Demonstration Units at CES 2008'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2008/01/syntax-brillian_showcases_120hz_and_full-hd_technology_on_new_lcd_hdtv_demonstration_units_at_ces_2008.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Syntax-Brillian Showcases 120Hz and Full-HD Technology on New LCD HDTV Demonstration Units at CES 2008</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>January  3, 2008</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Products & Equipment">Products & Equipment</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2008/01/syntax-brillian_showcases_120hz_and_full-hd_technology_on_new_lcd_hdtv_demonstration_units_at_ces_2008.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2008/01/syntax-brillian_showcases_120hz_and_full-hd_technology_on_new_lcd_hdtv_demonstration_units_at_ces_2008.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2008/01/syntax-brillian_showcases_120hz_and_full-hd_technology_on_new_lcd_hdtv_demonstration_units_at_ces_2008.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2008/01/syntax-brillian_showcases_120hz_and_full-hd_technology_on_new_lcd_hdtv_demonstration_units_at_ces_2008.php&amp;phase=2&amp;title=Syntax-Brillian%20Showcases%20120Hz%20and%20Full-HD%20Technology%20on%20New%20LCD%20HDTV%20Demonstration%20Units%20at%20CES%202008&amp;bodytext=Syntax-Brillian%20Corporation%20%28Nasdaq%3ABRLC%29%20today%20announced%20that%20at%20CES%202008%20it%20will%20be%20exhibiting%20a%20diverse%20range%20of%20new%20Olevia%26trade%3B%20LCD%20HDTVs%20featuring%20the%20latest%20in%20display%20technology%20hardware%20and%20feature%20enhancements%20combining%20120Hz%20Motion%20Estimation%2FMotion%20Compensation%20%28ME%2FMC%29%20technology%20with%20full-HD%201080p%20resolution%2C%20new%20industrial%20designs%20and%20firmware%20upgradeability%20via%20USB.%20These%20new%20technology%20demonstration%20units%20will%20be%20on%20exhibit%20from%20January%207%20-%2010%2C%202008%20in%20the%20Olevia%26trade%3B%20booth%20%2320401%2C%20Las%20Vegas%20Convention%20Center%2C%20South%20Hall.%0A%0AAt%20the%20Olevia%26trade%3B%20booth%2C%20visitors%20can%20experience...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p class="prtitle">Syntax-Brillian Showcases 120Hz and Full-HD Technology on New LCD HDTV Demonstration Units at CES 2008</p>

<center><i>Advanced Features Include Firmware Upgradeability Via USB, Universal "Learning" Remote Control, On-screen Installation Guide, JPEG & MP3 Support and New Accessories & Olevia-Licensed CE Products</i></center><br />
<br />

<p>2008 International CES<br />
Booth #20401</p>

<p><B>TEMPE, Ariz.--(BUSINESS WIRE)</B>--Syntax-Brillian Corporation (Nasdaq:BRLC) today announced that at CES 2008 it will be exhibiting a diverse range of new Olevia&trade; LCD HDTVs featuring the latest in display technology hardware and feature enhancements combining 120Hz Motion Estimation/Motion Compensation (ME/MC) technology with full-HD 1080p resolution, new industrial designs and firmware upgradeability via USB. These new technology demonstration units will be on exhibit from January 7 - 10, 2008 in the Olevia&trade; booth #20401, Las Vegas Convention Center, South Hall.</p>

<p>"The new features and enhancements we are unveiling at CES 2008 represent our continued commitment to offering consumers innovative and quality digital entertainment products," commented James Li, Syntax-Brillian's CEO. "The company's renewed focus on the LCD business is allowing us to be a technology leader in the high growth, LCD HDTV market."</p>

<p>At the Olevia&trade; booth, visitors can experience the stunning reality of images in motion on the new full-HD 1080p Olevia LCD HDTVs in 42"-type, 47"-type, 52"-type, 55"-type and 65"-type sizes. The state-of-the-art 120Hz ME/MC post processor technology produces crystal-clear motion images by reducing image motion blur.</p>

<p>Other innovative features and products demonstrated at the Olevia booth include a new eight-device universal "learning" remote that enables compatibility with all video sources from a single, convenient control. New Olevia LCD HDTVs can access the world's largest professionally maintained database of infrared function codes from Universal Electronics Inc.'s library of over 321,000 functions.</p>

<p>The new Olevia product line is comprised of the 2 Series, a competitively-priced true mainstream LCD HDTV, plus the Olevia 6 Series which offers premium quality with the latest features and additional inputs, and the best-in-class Olevia 7 Series. Some Olevia LCD HDTVs also incorporate new features that provide for enhanced user experiences including additional HDMI inputs and two USB ports. Users of theses Olevia models will be able to utilize these inputs not only to connect the TV to home entertainment equipment easier, but also to connect USB thumb drives or PCs to their TVs to install firmware upgrades, and use the TV to display photos or play MP3 music files through a home theater audio system.</p>

<p>The technology demonstration units on display in the Olevia&trade; booth are expected to comprise the core of new lines of Olevia&trade; LCD HDTVs scheduled to debut in the second quarter of 2008.</p>

<p>The new Olevia&trade; universal learning remote is expected to accompany select new Olevia&trade; models in January 2008.</p>

<p><br />
<B>New Olevia Accessories & Olevia-Licensed CE Products</B></p>

<p>New Olevia&trade; brand home theater products and accessories will also be on display at the Olevia booth including a three-in-one HDMI switcher box and a two-to-four HDMI switcher/splitter, each which enable users with multiple sources to distribute or split content through HDMI to ensure digital output to maximize the best possible picture. Each switcher/splitter is compatible with the new Olevia &trade; universal remote control and each comes with multiple HDMI cables. Olevia&trade; brand HDMI cables will also be on display. They are available in sizes including 6', 15', 25', 50' and 75'. These cables support resolutions of 1920 X 1080p video and 1920 X 1200 60Hz graphics in sizes 6' to 50' and 1080i video @ 60Hz in 75'. The 6', 15', 25' and 50' cables are HDMI 1.3 certified. Olevia LCD HDTVs from 19" to 52" can be easily wall mounted by using the new fixed, adjustable, swivel, articulating or ceiling mounts.</p>

<p>Seven models of wall mounts are also available for Olevia LCD HDTVs. The adjustable WM10D wall mount with 20° tilt (fwd/bkwd) is ideal for 19" and 23". The fixed WM30D with 15° forward tilt fits TVs up to 52", and the fixed WM35D with 5° forward tilt is designed for the 65" models. The WM40D swivel wall mount with 15°/5° tilt (fwd/bkwd) fits TVs up to 32". The articulating WM50D with 15°/5° tilt (fwd/bkwd) is used for 37" to 42" type Olevia TVs, while the articulating WD60D model fits 47" to 52" units. A ceiling mount WM70D with 30° vertical tilt can be used with Olevia LCD TVs up to 52".</p>

<p>Syntax-Brillian will also preview its new Olevia-licensed consumer electronics products that are scheduled for availability in Q1 2008. Included are a home theater sound bar for virtual surround sound, a 2.1 DVD home theater system, a 1080p upconverting DVD player, digital photo frames, a wireless 5.1 home theater speaker system, an ultra-slim vertical DVD player, and a wireless/waterproof LCD TV.</p>

<p>For more information please contact Pattie Adams at 909.859.8432 (pattie.adams@syntaxbrillian.com).</p>

<p><br />
<B>About Syntax-Brillian Corporation</B></p>

<p>Syntax-Brillian Corporation (www.syntaxbrillian.com) is a designer, developer and distributor of LCD HDTVs, digital cameras, and microdisplay entertainment products.</p>

<p>The company's lead products include its Olevia &trade; brand (www.Olevia.com) high definition widescreen LCD televisions - one of the fastest growing global TV brands - and Vivitar brand (www.vivitar.com) digital still and video cameras. Syntax-Brillian has built an Asian supply chain coupled with an international manufacturing and distribution network to support worldwide retail sales channels and position the company as a market leader in consumer digital entertainment products.</p>

<p>Olevia&trade;, Brillian, LCoS&trade; and Vivitar are trademarks or registered trademarks of Syntax-Brillian Corporation. All other trademarks are the property of their respective owners.</p>

<p>Forward-looking Statements:</p>

<p>Certain statements contained in this press release may be deemed to be forward-looking statements under federal securities laws, and Syntax-Brillian intends that such forward-looking statements be subject to the safe harbor created thereby. Syntax-Brillian cautions that these statements are qualified by important factors that could cause actual results to differ materially from those reflected by the forward-looking statements contained herein. Such factors include changes in markets for the Company's products; changes in the market for customers' products; the failure of the Company's products to deliver commercially acceptable performance; the ability of the Company's management, individually or collectively, to guide the Company in a successful manner; and other risks detailed in Syntax-Brillian's Annual Report on Form 10-K for the fiscal year ended June 30, 2007 and subsequent filings with the Securities and Exchange Commission.</p>

<p>Source: Syntax-Brillian</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>January  3, 2008 06:55 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 838
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
 				AND entry_id <> 838
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2008/01/syntax-brillian_showcases_120hz_and_full-hd_technology_on_new_lcd_hdtv_demonstration_units_at_ces_2008.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
