<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1399";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1399 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category[placement_category_id];
	
	# Still need this to track page views. Used in the Links unit in the footer
	$google_links_channel = $author[channel];

	# Set variables based on entry type. NOTE: Podcasts has its own entry template, so it is not included amongst the choices below.
	switch (8) {
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
	<meta name="keywords" content="blu ray, kdl xbr, cox cable, sony kdl, our friend, picture, looked, settings, our, blu, ray, Sony, Blu, good, color, sony, enhancer, Color, Cox, better, cox, calibration, Our, friend, see" />
	<meta name="description" content="The TV looks visually appealing and has a high build quality. There is a clear plastic frame that goes around the TV. The TV weighs 84 pounds (38 Kg) with the pedestal and measures 49.7&quot; (126 cm) wide by 31.3&quot; (79.5 cm) high by 17.7&quot; (32 cm) deep (4.8&quot; (12 cm) without the pedestal). We immediately took the TV off the default setting and changed it to cinema. Cox cable looked very bad. So bad that we recommended switching to satellite. Braden has Cox as one of his sources so his impression was that Cox does not look that bad on his TVs. It could be that this TV does not do well with compressed sources. OTA HD looked much better. In general OTA digital channels looked better than the SD coming from the Cox cable box.

We then popped in a Blu-ray disc..." />
	<title>HDTV Magazine Reviews - Sony KDL-46XBR4 46 inch LCD HDTV</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/sony_kdl-46xbr4_46_inch_lcd_hdtv';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Sony KDL-46XBR4 46 inch LCD HDTV'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/reviews/2008/05/sony_kdl-46xbr4_46_inch_lcd_hdtv.php";
		if ($author[img] != '' && 8 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Sony KDL-46XBR4 46 inch LCD HDTV</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>May 20, 2008</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=HDTV Displays">HDTV Displays</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/reviews/2008/05/sony_kdl-46xbr4_46_inch_lcd_hdtv.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/reviews/2008/05/sony_kdl-46xbr4_46_inch_lcd_hdtv.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/reviews/2008/05/sony_kdl-46xbr4_46_inch_lcd_hdtv.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/reviews/2008/05/sony_kdl-46xbr4_46_inch_lcd_hdtv.php&amp;phase=2&amp;title=Sony%20KDL-46XBR4%2046%20inch%20LCD%20HDTV&amp;bodytext=The%20TV%20looks%20visually%20appealing%20and%20has%20a%20high%20build%20quality.%20There%20is%20a%20clear%20plastic%20frame%20that%20goes%20around%20the%20TV.%20The%20TV%20weighs%2084%20pounds%20%2838%20Kg%29%20with%20the%20pedestal%20and%20measures%2049.7%22%20%28126%20cm%29%20wide%20by%2031.3%22%20%2879.5%20cm%29%20high%20by%2017.7%22%20%2832%20cm%29%20deep%20%284.8%22%20%2812%20cm%29%20without%20the%20pedestal%29.%20We%20immediately%20took%20the%20TV%20off%20the%20default%20setting%20and%20changed%20it%20to%20cinema.%20Cox%20cable%20looked%20very%20bad.%20So%20bad%20that%20we%20recommended%20switching%20to%20satellite.%20Braden%20has%20Cox%20as%20one%20of%20his%20sources%20so%20his%20impression%20was%20that%20Cox%20does%20not%20look%20that%20bad%20on%20his%20TVs.%20It%20could%20be%20that%20this%20TV%20does%20not%20do%20well%20with%20compressed%20sources.%20OTA%20HD%20looked%20much%20better.%20In%20general%20OTA%20digital%20channels%20looked%20better%20than%20the%20SD%20coming%20from%20the%20Cox%20cable%20box.%0A%0AWe%20then%20popped%20in%20a%20Blu-ray%20disc...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p><a href="http://www.sonystyle.com/webapp/wcs/stores/servlet/ProductDisplay?catalogId=10551&amp;storeId=10151&amp;langId=-1&amp;productId=8198552921665116636" target="_blank"><img style="margin: 0px 0px 5px 5px" height="160" alt="Sony KDL-46XBR4" src="http://www.hdtvmagazine.com/images/mt/SonyKDL46XBR446inchLCDHDTV_12FF5/image.png" width="160" border="0"></a>&nbsp; <table class="greygrid"> <tbody> <tr> <td style="font-weight: bold; text-align: center" colspan="4">Pricing at publication</td></tr> <tr> <td class="greygrid">&nbsp;</td> <td class="greygrid"><b>MSRP</b></td> <td class="greygrid"><b>Street</b></td> <td class="greygrid"><b>Amazon.com</b></td></tr> <tr> <td class="greygrid"><b>Sony KDL-46XBR4</b></td> <td class="greygrid"><a href="http://www.sonystyle.com/webapp/wcs/stores/servlet/ProductDisplay?catalogId=10551&amp;storeId=10151&amp;langId=-1&amp;productId=8198552921665116636" target="_blank">$3,299.99</a></td> <td class="greygrid"><a href="/equipment/model.php?man=Sony&amp;model=KDL46XBR4" target="_blank">$2,455.00</a></td> <td class="greygrid"><a href="http://www.amazon.com/gp/product/B000UN8MKM?ie=UTF8&amp;tag=hdtvmagazine-20&amp;linkCode=as2&amp;camp=1789&amp;creative=9325&amp;creativeASIN=B000UN8MKM" target="_blank">$2,799.99</a></td></tr></tbody></table> <p>Warranty: 1 Year Parts / 1 Year Labor </p> <p><b>Summary: A nice TV when a good quality HD signal is present, but a bit on the pricey side.</b></p> <p>As you would imagine, being a close friend of the HT Guys comes with some benefits. We have a friend that just bought the Sony KDL-46XBR4 46 inch LCD and a Sony Blu-ray player. He asked for some help setting the TV up and of course we said yes. Well, we said yes because it provides good material for our show. Our friend uses Cox cable, OTA and Blu-ray for his HD material.<a name="r-0p0"></a>  <p><a name="axgx0"></a><a name="qg%3Ax2"></a> <h2>Features</h2> <ul> <li>1080p  <li>10-bit Processing and 10-bit Display  <li>Motionflow&trade; 120 Hz with Full HD high frame rate capability  <li>Deep Color Support  <li>24p True Cinema (24p Input Capability)  <li>DMeX - Ready (Digital Media Extender) - <a name="agei0"></a><a name="sota0"></a><i>Sony's Digital Media Extender (DMeX) ready televisions offer a digital connection path for the addition of the optional modules like the new BRAVIA Internet Video Link. 6 With innovative DMeX expansion capabilities featuring the Emmy<sup>&reg;</sup> award winning XMB user interface, these models are not merely TVs, but powerful entertainment platforms that not only meet your needs today, but extend to add new features seamlessly.</i></li></ul> <h2>Impression</h2> <p>The TV looks visually appealing and has a high build quality. There is a clear plastic frame that goes around the TV. The TV weighs 84 pounds (38 Kg) with the pedestal and measures 49.7" (126 cm) wide by 31.3" (79.5 cm) high by 17.7" (32 cm) deep (4.8" (12 cm) without the pedestal). We immediately took the TV off the default setting and changed it to cinema. Cox cable looked very bad. So bad that we recommended switching to satellite. Braden has Cox as one of his sources so his impression was that Cox does not look that bad on his TVs. It could be that this TV does not do well with compressed sources. OTA HD looked much better. In general OTA digital channels looked better than the SD coming from the Cox cable box.<a name="hdvk0"></a><br><a name="hdvk1"></a><br>We then popped in a Blu-ray disc (Fantastic Four Rise of the Silver Surfer) and were quite impressed with the picture. We saw great detail in the picture and found that the skin tones looked very natural. Color representation was highly accurate. The TV has very good black levels for an LCD. We found that computer generated scenes looked fake for some reason. The skiing scene looked very good. There was a lot of contrast between the white snow and the blue sky. The TV has a Contrast Ratio of 2,000:1. We liked the off angle viewing of this TV. While not on par with Plasmas, it's much better than most LCDs we've seen.<a name="w1ch0"></a><br><a name="zfwt0"></a><br>Next we started playing around with the settings to dial the TV in. Our friend was happy with the pre-configured cinema settings and was getting antsy about us spending so much time with his TV. In the end, our quick calibration made the picture even better. Our calibration settings are included at the end of this review, but please use them as a starting point only. We ended up turning the all the special processing off, but you can play around with those to see if you like what you see. With HD film based material, we felt the Motion Enhancer took away from the viewing experience. With SD material it actually improved the picture. This is very subjective so if you plan on buying this TV or you already own one, experiment with these settings.<a name="w.hi0"></a><br><a name="w.hi1"></a><br>Once we had it dialed in, we went back and watched the Fantastic Four Blu-ray disc. It was clear to all that we had indeed improved the picture. Our friend was happy we came by. It was everything we said before about the clarity, color, and detail but improved. We also watched Standard Definition DVDs (Spider Man 3, 27 Dresses, and Enchanted) after calibration and were pleasantly surprised at how good the picture looked. The motion enhancer actually improved the picture. Our recommendation is to screen parts of the movie with the motion enhancer on and then turn it off, and see which one you prefer. You'll soon figure out what types of movies this will help and what types it won't help. Unfortunately we do not have a clear cut answer for its use.  <h2>Conclusion<a name="r3y30"></a></h2> <p>Overall, the Sony makes for a nice TV when a good quality HD signal is present. If you have an overly compressed signal via OTA or Cable you won't be happy with what you see. To really show off this set, you should also consider buying a Blu-ray player. Please do not leave the set in its default settings. At a minimum switch it to Cinema mode. Better yet calibrate it with any of the calibration DVDs or have a professional do it for you. This TV will really show its stuff when set up properly. The only negatives we can find are that its optical output does not do 5.1 for anything other than its ATSC tuner (but this is the case with almost all TVs), and that it's a bit on the pricey side.  <h2>Calibration Settings (Use only as a starting point)<a name="hatw1"></a></h2> <p>HDMI Settings (Blu-ray)<a name="ujn_0"></a><br><a name="ujn_1"></a><br>Picture Mode: Custom<a name="ujn_2"></a><br>Backlight: 3<a name="ujn_3"></a><br>Picture: 66<a name="ujn_4"></a><br>Brightness: 45<a name="ujn_5"></a><br>Color: 57<a name="ujn_6"></a><br>Hue: 0<a name="ujn_7"></a><br>Color Temperature: Neutral<a name="ujn_8"></a><br>Sharpness: 38<a name="ujn_9"></a><br>Noise Reduction: Off<a name="ujn_10"></a><br><a name="ujn_11"></a><br>Advanced Settings<a name="ujn_12"></a><br>White Balance:<a name="ujn_13"></a><br>R-Gain: 0<a name="ujn_14"></a><br>G-Gain: -5<a name="ujn_15"></a><br>B-Gain: -5<a name="ujn_16"></a><br>R-Bias: 0<a name="ujn_17"></a><br>G-Bias: -5<a name="ujn_18"></a><br>B-Bias: 0<a name="ujn_19"></a><br><a name="wtiu0"></a><br>Color Space: Standard<a name="ujn_20"></a><br>Detail Enhancer: off<a name="wtiu1"></a><br>Edge Enhancer: off<a name="ujn_21"></a><br>Everything else set to off<a name="ujn_22"></a><br>Motion Enhancer: off<a name="kbgg3"></a></p>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>May 20, 2008 09:23 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 1399
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
 			<h2>More on HDTV Displays</h2><ul><?
 			$sql = "
 			SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name
 			FROM mt_entry e, mt_author a, mt_placement p, mt_category c
 			WHERE entry_author_id = author_id
 				AND e.entry_id = p.placement_entry_id
 				AND p.placement_category_id = c.category_id
 				AND category_label = 'HDTV Displays'
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
			
 		<?if (8 <> 7) {
 			# Recent Articles by Author (exclude this one)
 			# Do not show recent articles for Bulletins.
 			$qry = "
 			SELECT entry_id, entry_blog_id, entry_created_on, entry_title, author_id, author_name
 			FROM mt_entry e, mt_author a
 			WHERE entry_blog_id = 8
 				AND entry_id <> 1399
 				AND entry_status = 2
 				AND entry_author_id = a.author_id
 				AND a.author_name = 'The HT Guys'
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
 				<h2>About The HT Guys</h2>
 				<?=stripslashes($author[bio_short])?>
 			<span class="corners-bottom"><span></span></span></div>
 		<?}?>
		</td><td id="right">

 		<?if ($about != '') {?>
 			<div class="item"><span class="corners-top"><span></span></span>
 				<h2>About Reviews</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/reviews/2008/05/sony_kdl-46xbr4_46_inch_lcd_hdtv.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
