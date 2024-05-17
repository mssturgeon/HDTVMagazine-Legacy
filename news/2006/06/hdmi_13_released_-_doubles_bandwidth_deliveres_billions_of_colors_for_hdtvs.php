<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 392";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 392 AND placement_is_primary = 1";
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
	<meta name="keywords" content="high definition, hdmi specification, licensing llc, consumer electronics, hdmi licensing, hdmi, HDMI, digital, audio, high, color, devices, interface, definition, video, colors, consumer, specification, new, licensing, electronics, llc, Licensing, standard, LLC" />
	<meta name="description" content="HDMI Founder companies (Hitachi, Ltd., Matsushita Electric Industrial Co., Ltd. (Panasonic), Royal Philips Electronics, Silicon Image, Inc., Sony Corp., Thomson, Inc. and Toshiba Corp.) today released a major enhancement of the High-Definition Multimedia Interface&amp;trade; (HDMI&amp;trade;) specification, the de facto standard digital interface for high definition consumer electronics. HDMI 1.3 will enable the next generation of HDTVs, PCs and DVD players to transmit and display content in billions of colors with unprecedented vividness and accuracy." />
	<title>HDTV Magazine Bulletins - HDMI 1.3 Released - Doubles Bandwidth, Deliveres Billions of Colors for  HDTVs</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdmi_13_released_-_doubles_bandwidth_deliveres_billions_of_colors_for_hdtvs';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('HDMI 1.3 Released - Doubles Bandwidth, Deliveres Billions of Colors for  HDTVs'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2006/06/hdmi_13_released_-_doubles_bandwidth_deliveres_billions_of_colors_for_hdtvs.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDMI 1.3 Released - Doubles Bandwidth, Deliveres Billions of Colors for  HDTVs</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>June 23, 2006</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Technology">Technology</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2006/06/hdmi_13_released_-_doubles_bandwidth_deliveres_billions_of_colors_for_hdtvs.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2006/06/hdmi_13_released_-_doubles_bandwidth_deliveres_billions_of_colors_for_hdtvs.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2006/06/hdmi_13_released_-_doubles_bandwidth_deliveres_billions_of_colors_for_hdtvs.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2006/06/hdmi_13_released_-_doubles_bandwidth_deliveres_billions_of_colors_for_hdtvs.php&amp;phase=2&amp;title=HDMI%201.3%20Released%20-%20Doubles%20Bandwidth%2C%20Deliveres%20Billions%20of%20Colors%20for%20%20HDTVs&amp;bodytext=HDMI%20Founder%20companies%20%28Hitachi%2C%20Ltd.%2C%20Matsushita%20Electric%20Industrial%20Co.%2C%20Ltd.%20%28Panasonic%29%2C%20Royal%20Philips%20Electronics%2C%20Silicon%20Image%2C%20Inc.%2C%20Sony%20Corp.%2C%20Thomson%2C%20Inc.%20and%20Toshiba%20Corp.%29%20today%20released%20a%20major%20enhancement%20of%20the%20High-Definition%20Multimedia%20Interface%26trade%3B%20%28HDMI%26trade%3B%29%20specification%2C%20the%20de%20facto%20standard%20digital%20interface%20for%20high%20definition%20consumer%20electronics.%20HDMI%201.3%20will%20enable%20the%20next%20generation%20of%20HDTVs%2C%20PCs%20and%20DVD%20players%20to%20transmit%20and%20display%20content%20in%20billions%20of%20colors%20with%20unprecedented%20vividness%20and%20accuracy.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<div align="center">
<p><br />
<strong>HDMI 1.3 DOUBLES BANDWIDTH, DELIVERS BILLIONS OF COLORS FOR HDTVs</strong></p>
<p>
<strong>High-Definition Multimedia Interface Also Adds Newest Digital Audio Formats, Mini Connector and Lip Sync</strong></p>
<p align="left">SUNNYVALE, Calif., June 22, 2006 &mdash; The seven HDMI Founder companies (Hitachi, Ltd., Matsushita Electric Industrial Co., Ltd. (Panasonic), Royal Philips Electronics, Silicon Image, Inc., Sony Corp., Thomson, Inc. and Toshiba Corp.) today released a major enhancement of the High-Definition Multimedia Interface&trade; (HDMI&trade;) specification, the de facto standard digital interface for high definition consumer electronics. HDMI 1.3 will enable the next generation of HDTVs, PCs and DVD players to transmit and display content in billions of colors with unprecedented vividness and accuracy.
<p align="left">The HDMI 1.3 specification more than doubles HDMI's bandwidth and adds support for Deep Color technology, a broader color space, new digital audio formats, automatic audio/video synching capability ("lip sync"), and an optional smaller connector for use with personal photo and video devices. The update reflects the determination of the HDMI founders to ensure HDMI continues evolving ahead of future consumer demands.</p>
<p align="left">The update arrives at a time of strong momentum for the HDMI standard. HDMI Licensing, LLC today announced that more than 400 makers of consumer electronics and PC products worldwide have adopted HDMI. Market researcher In-Stat expects 60 million devices featuring HDMI to ship in 2006.</p>
<p align="left">"PLAYSTATION&reg;3 will be the most advanced computer platform for enjoying a wide range of entertainment content, including the latest games and HD movies, in the home," said Ken Kutaragi, president and group CEO of Sony Computer Entertainment, Inc. "By introducing the next-generation HDMI 1.3 technology, with its high speed and deep color capabilities, PS3 will push the boundaries of audiovisual quality to the next level of more natural and smoother expression on the latest large flat panel displays."</p>
<p align="left">"HDMI is an established cornerstone for the whole High Definition TV industry and Philips is extremely pleased to see such significant improvements for picture and sound quality with this new version," said Johan van de Ven, CTO and Senior Vice President of  Philips Consumer Electronics. "We look forward to continuing to work with other HDMI Founder companies to extend the scope of HDMI across new devices and applications, while remaining entirely committed to ensuring full backward compatibility with existing products."</p>
<p align="left">With the adoption of Deep Color and the xvYCC color space, HDMI 1.3 removes the previous interface-related restrictions on color selection. The interface will no longer be a constraining pipe that forces all content to fit within a limited set of colors, unlike all previous video interfaces.</p>
<p align="left"><strong>New HDMI 1.3 capabilities include:</strong></p>

<ul>
	<li><div align="justify"><strong>Higher speed:</strong>  HDMI 1.3 increases its single-link bandwidth from 165MHz (4.95 gigabits per second) to 340 MHz (10.2 Gbps) to support the demands of future high definition display devices, such as higher resolutions, Deep Color and high frame rates. In addition, built into the HDMI 1.3 specification is the technical foundation that will let future versions of HDMI reach significantly higher speeds.</div></li>
	<li><div align="justify"><strong>Deep color:</strong> HDMI 1.3 supports 30-bit, 36-bit and 48-bit (RGB or YCbCr) color depths, up from the 24-bit depths in previous versions of the HDMI specification.</div>
		<ul>
			<li><div align="justify">Lets HDTVs and other displays go from millions of colors to billions of colors</div></li>
			<li><div align="justify">Eliminates on-screen color banding, for smooth tonal transitions and subtle gradations between colors</div></li>
			<li><div align="justify">Enables increased contrast ratio</div></li>
			<li><div align="justify">Can represent many times more shades of gray between black and white. At 30-bit pixel depth, four times more shades of gray would be the minimum, and the typical improvement would be eight times or more</div></li>
		</ul>
	</li>
	<li><div align="justify"><strong>Broader color space:</strong>  HDMI 1.3 removes virtually all limits on color selection.</div>
		<ul>
			<li><div align="justify">Next-generation "xvYCC" color space supports 1.8 times as many colors as existing HDTV signals</div></li>
			<li><div align="justify">Lets HDTVs display colors more accurately</div></li>
			<li><div align="justify">Enables displays with more natural and vivid colors</div></li>
		</ul>
	</li>
	<li><div align="justify"><strong>New mini connector:</strong> With small portable devices such as HD camcorders and still cameras demanding seamless connectivity to HDTVs, HDMI 1.3 offers a new, smaller form factor connector option.</div></li>
	<li><div align="justify"><strong>Lip Sync:</strong> Because consumer electronics devices are using increasingly complex digital signal processing to enhance the clarity and detail of the content, synchronization of video and audio in user devices has become a greater challenge and could potentially require complex end-user adjustments. HDMI 1.3 incorporates an automatic audio/video synching capability that allows devices to perform this synchronization automatically with accuracy.</div></li>
	<li><div align="justify"><strong>New lossless audio formats:</strong>  In addition to HDMI's current ability to support high-bandwidth uncompressed digital audio and currently-available compressed formats (such as Dolby&reg; Digital and DTS), HDMI 1.3 adds additional support for new, lossless compressed digital audio formats Dolby&reg; TrueHD and DTS-HD Master Audio&trade;.</div></li>
</ul>

<p align="left">Products implementing the new HDMI specification will continue to be backward compatible with earlier HDMI products.</p>
<p align="left">"The dramatic increase in maximum speed achieved in HDMI 1.3 will enable HDMI to stay far ahead of the bandwidth demands of future high definition source and display devices," said Leslie Chard, president of HDMI Licensing, LLC. "As the de facto standard digital interface for the high definition and consumer electronics markets, HDMI is implementing the most innovative technologies today to fulfill the demands of tomorrow's consumers."</p>
<p align="left">The latest HDMI specification can be downloaded at no cost by visiting www.hdmi.org.</p>
<p align="left"><strong>About HDMI</strong><br />
HDMI is the first and only consumer electronics industry-supported, uncompressed, all-digital audio/video interface. By delivering crystal-clear, all-digital audio and video via a single cable, HDMI dramatically simplifies cabling and helps provide consumers with the highest-quality home theater experience. HDMI provides an interface between any audio/video source, such as a set-top box, DVD player, or A/V receiver and an audio and/or video monitor, such as a digital television (DTV), over a single cable.</p>
<p align="left"><strong>About HDMI Licensing, LLC</strong><br />
HDMI Licensing, LLC, a wholly owned subsidiary of Silicon Image, Inc., is the agent responsible for licensing the HDMI specification, promoting the HDMI standard and providing education on the benefits of HDMI to retailers and consumers. The HDMI specification was developed by Hitachi, Matsushita (Panasonic), Philips, Silicon Image, Sony, Thomson and Toshiba as the digital interface standard for the consumer electronics market. The HDMI specification combines uncompressed high-definition video and multi-channel audio in a single digital interface to provide crystal-clear digital quality over a single cable. For more information about HDMI, please visit www.hdmi.org</p>
<p align="left"><strong>Forward-looking Statements</strong><br />
This news release contains forward-looking information within the meaning of federal securities regulations. These forward-looking statements include statements related to the anticipated growth, market, acceptance and consumer demand for HDMI 1.3 and high definition source and display devices, the anticipated volume of shipments of HDMI devices in 2006, and the benefits, capabilities, performance, evolution, design and implementation of HDMI 1.3, the HDMI standard, and products implementing HDMI. These forward-looking statements involve risks and uncertainties, including those described from time to time in the Securities and Exchange Commission (SEC) filings of Silicon Image, Inc., the parent corporation of HDMI Licensing, LLC, that could cause the actual results to differ materially from those anticipated by these forward-looking statements. In particular, the anticipated growth, market, acceptance and consumer demand for HDMI 1.3 and high definition source and display devices, the anticipated volume of shipments of HDMI devices in 2006, and the benefits, capabilities, performance, evolution, design and implementation of HDMI 1.3, the HDMI standard, and products implementing HDMI, may differ materially from what is currently anticipated. In addition, see the Risk Factors section of the most recent Form 10-K or Form 10-Q filed by Silicon Image with the SEC. Silicon Image assumes no obligation to update any forward-looking information contained in this press release.</p>
<p align="center">###</p>
<p align="left">HDMI&trade; and High-Definition Multimedia Interface are trademarks of HDMI Licensing, LLC in the United States and other countries. All other trademarks and registered trademarks are the property of their respective owners. PLAYSTATION is a registered trademark of Sony Computer Entertainment, Inc.<br /></p>
</div>
<br />
<br />
<strong>Media Contacts:</strong><br />
Kasey Holman<br />
Media Relations - HDMI Licensing, LLC <br />
Phone: 408-616-4192<br />
<a href="mailto:kholman@hdmi.org">kholman@hdmi.org</a><br />
<br />
Paul Sherer<br />
Ogilvy Public Relations Worldwide <br />
Phone: 415-677-2715<br />
<a href="mailto:paul.sherer@ogilvypr.com">paul.sherer@ogilvypr.com</a><br />
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>June 23, 2006 04:58 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 392
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
 				AND entry_id <> 392
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2006/06/hdmi_13_released_-_doubles_bandwidth_deliveres_billions_of_colors_for_hdtvs.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
