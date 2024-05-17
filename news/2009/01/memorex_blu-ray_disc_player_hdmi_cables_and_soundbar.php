<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');

	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1608";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);

	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";

	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1608 AND placement_is_primary = 1";
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
	<meta name="keywords" content="blu ray, audio video, ray disc, home theatre, retailers starting, memorex, Memorex, audio, video, cables, home, DVD, digital, dvd, cable, experience, high, player, Blu, hdmi, blu, ray, soundbar, HDMI, sound" />
	<meta name="description" content="Recreate the cinema movie experience right in your home with easy-to-use and affordable Memorex home theatre products. Memorex, a portfolio brand of Imation Corp. (NYSE: IMN), today introduced the powerful SimpleSurround HDMI DVD SoundBar with iPod&amp;reg; dock, the feature-packed MVBD-2520 Blu-ray Disc player, and high performance audio/video cables including High Definition Multimedia Interface (HDMI) cables. The latest in the Memorex line of home theatre components and accessories represent the perfect blend of quality and value, and offer consumers the exciting opportunity to experience high-definition (HD) movies in their homes without breaking the bank.

The following home theatre components and accessories will be on display..." />
	<title>HDTV Magazine Bulletins - Memorex&reg; Blu-ray Disc Player, HDMI Cables and Soundbar</title>
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

		$base_url = strleftback(PHP_SELF, '/') . '/memorex_blu-ray_disc_player_hdmi_cables_and_soundbar';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Memorex&reg; Blu-ray Disc Player, HDMI Cables and Soundbar'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2009/01/memorex_blu-ray_disc_player_hdmi_cables_and_soundbar.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Memorex&reg; Blu-ray Disc Player, HDMI Cables and Soundbar</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>January  7, 2009</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=HD DVD & Blu-ray">HD DVD & Blu-ray</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2009/01/memorex_blu-ray_disc_player_hdmi_cables_and_soundbar.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2009/01/memorex_blu-ray_disc_player_hdmi_cables_and_soundbar.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2009/01/memorex_blu-ray_disc_player_hdmi_cables_and_soundbar.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2009/01/memorex_blu-ray_disc_player_hdmi_cables_and_soundbar.php&amp;phase=2&amp;title=Memorex%26reg%3B%20Blu-ray%20Disc%20Player%2C%20HDMI%20Cables%20and%20Soundbar&amp;bodytext=Recreate%20the%20cinema%20movie%20experience%20right%20in%20your%20home%20with%20easy-to-use%20and%20affordable%20Memorex%20home%20theatre%20products.%20Memorex%2C%20a%20portfolio%20brand%20of%20Imation%20Corp.%20%28NYSE%3A%20IMN%29%2C%20today%20introduced%20the%20powerful%20SimpleSurround%20HDMI%20DVD%20SoundBar%20with%20iPod%26reg%3B%20dock%2C%20the%20feature-packed%20MVBD-2520%20Blu-ray%20Disc%20player%2C%20and%20high%20performance%20audio%2Fvideo%20cables%20including%20High%20Definition%20Multimedia%20Interface%20%28HDMI%29%20cables.%20The%20latest%20in%20the%20Memorex%20line%20of%20home%20theatre%20components%20and%20accessories%20represent%20the%20perfect%20blend%20of%20quality%20and%20value%2C%20and%20offer%20consumers%20the%20exciting%20opportunity%20to%20experience%20high-definition%20%28HD%29%20movies%20in%20their%20homes%20without%20breaking%20the%20bank.%0A%0AThe%20following%20home%20theatre%20components%20and%20accessories%20will%20be%20on%20display...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p class="prtitle">Memorex&reg; Home Theatre Electronics, Components Bring the High-Definition Movie Experience Home at Affordable Prices</p>

<center><i>Powerful soundbar, feature-packed Blu-ray Disc player, and top-performing HDMI cables manufactured to meet highest standards, yet priced for outstanding value</i></center><br />
<br />

<p><br />
<B>LAS VEGAS--(BUSINESS WIRE)</B>--Recreate the cinema movie experience right in your home with easy-to-use and affordable Memorex home theatre products. Memorex, a portfolio brand of Imation Corp. (NYSE: IMN), today introduced the powerful SimpleSurround HDMI DVD SoundBar with iPod&reg; dock, the feature-packed MVBD-2520 Blu-ray Disc player, and high performance audio/video cables including High Definition Multimedia Interface (HDMI) cables. The latest in the Memorex line of home theatre components and accessories represent the perfect blend of quality and value, and offer consumers the exciting opportunity to experience high-definition (HD) movies in their homes without breaking the bank.</p>

<p>"Home theatre systems are natural focal points for family gatherings, but until recently, assembling a high-definition entertainment system has been prohibitively expensive," said Jessica Walton, Memorex global brand director, Imation Corp. "Memorex's latest line of audio and video electronics and cables represent a new paradigm shift in the affordability of home entertainment systems. With our products, assembling friends and family together to share and experience the magnificent, vivid images and rich surround sounds of cinematic movies in the home is finally within reach of the average consumer."</p>

<p>The following home theatre components and accessories will be on display at the Pepcom&reg; Digital Experience event from 7 to 10 p.m. tonight at The Mirage.</p>

<p><br />
<B>Memorex SimpleSurround SoundBar with iPod Dock</B></p>

<p>You'll leave your friends and family speechless when they experience the dramatic and powerful sounds playing from your Memorex SimpleSurround HDMI DVD SoundBar, a beautifully-designed and sleek all-in-one solution for your home audio/video needs, complete with a slot-loading HDMI DVD player and iPod dock at an outstanding value of less than $200. The SimpleSurround SoundBar is powerful enough to deliver the immersive sounds of the movie theatre right into your living or home entertainment room, and is simple to install compared to surround sound systems comprised of several speaker units and subwoofers that often require professional installations.</p>

<p>Perfect for the living room or bedroom, the SimpleSurround Soundbar is compact and sleek, reducing the visual clutter of having multiple speakers and an iPod dock as part of your home theatre set-up. Featuring true versatility, this Memorex HDMI DVD Soundbar with iPod dock not only lets you charge your iPod and play music directly from your MP3 player, but also offers FM radio with station presets, DVD playback, and a USB/SD/MMC card slot, a perfect feature for memory keepers looking to share digital photos and home videos on television displays.</p>

<p>Housed in a sleek black, compact design, the 2.1 channel SoundBar incorporates innovative audio technology from SRS Labs that creates a drastically fuller and more natural sound experience than other 2.1 channel sound systems. Featured SRS Labs technology includes Dialog Clarity, which makes speech and center dialogue more clear and crisp during playback; TruBass which enhances bass performance using psychoacoustic techniques that selectively boost a series of low bass harmonics to restore the perception of low frequency tones; and TruSurround XT, a sophisticated algorithm that enables a surround sound multi-channel immersive audio experience over two channels.</p>

<p>The SimpleSurround Soundbar is "Made for iPod" certified and comes preloaded with five relaxing and soothing ambient sounds.</p>

<p>Technical features include 2.1 channel stereo sound with integrated amplifier and built-in SRS TruBass, Dialog Clarity, TruSurround XT technologies. Playable media includes DVD, DVD-R, DVD-R/RW, DV+R/RW, CD, CD-R/RW, SVCD/VCD, WMV and MP3. Connectivity includes HDMI output, component/S-video composite output, USB/SD/MMC Card slot, and auxiliary input to connect other digital audio devices. It also features bass and treble control, reverse polarity LCD display, and full function remote control.</p>

<p>The Memorex SoundBar is available in black and will be shipping to retailers starting in April for an SRP of $199.99.</p>

<p><br />
<B>Memorex MVBD-2520 Blu-ray Disc Player (Profile 2.0 /BD-Live)</B></p>

<p>Experience the richer colors and finer details of HD images, and enjoy the crisp, vibrant multi-channel audio content of the Blu-ray Disc format with this sleek, elegant Blu-ray player. In addition to Blu-ray Disc playback, the Memorex MVBD-2520 features advanced offerings like Profile 2.0 or BD-Live for internet access via an Ethernet port. This dramatically enhances a movie viewer's experience with downloadable extra features, online bonus content and the ability to download firmware updates. In addition, the Memorex MVBD 2520 has the capability to convert standard-definition material to high-definition quality at 480p, 720p, 1080i and 1080p display resolutions. The Memorex MVBD 2520 Blu-ray Disc player is packed with advanced features and functionality, yet is offered at less than $200, representing an outstanding value.</p>

<p>Technical features include video resolution of full HD 1080p and video frame rates of 24p and 60p. Audio playback features include Dolby Digital, Dolby TrueHD, Dolby Digital Plus, DTS, DTS-HD High Resolution Audio decoding and bit stream output, as well as Master Audio bit stream output. The player is compatible with a wide range of audio and video formats including Blu-ray Disc (AV format), DVD-Video, MPEG-2, MPEG-4 AVC (H.264), VC-1, VCD, SVCD, JPEG, BD-ROM,DVD-ROM, DVD-R/-RW, DVD+R/+RW, CD-ROM, and CD-R/-RW, as well as WMA and MP3 files. This player also supports SD Card and USB 2.0.</p>

<p>The Memorex MVBD-2520 Blu-ray Disc Player will be shipping to retailers starting in early summer 2009 for suggested retail price (SRP) of $199.99.</p>

<p><br />
<B>Audio/Video Cables</B></p>

<p>Memorex enters the audio and video (A/V) cable market with six offerings made of the highest quality materials. Memorex A/V cables have been designed and engineered to ensure they consistently transmit clean error-free signals for the best imaging and sound quality. Memorex cables feature state-of-the-art manufacturing techniques and high performance materials including superior cable shielding, a reliable soldering process and armor-locked technology to improve strain relief to protect and preserve audio and video signals. Affordably priced, Memorex cables are ideal for the value-conscious movie enthusiast.</p>

<p>Leading the Memorex cable lineup is its High-Definition Audio Video Cable. Consumers can experience the sharpest picture, deepest color, crystal-clear sounds and smoothest video possible with new HDMI-Certified cables from Memorex. Memorex HDMI cables have been manufactured under very precise standards using the highest quality materials to transport uncompressed audio and video signals from HD sources like Blu-ray players, HD digital satellite receivers, and PlayStation&reg; 3 consoles at ultra-fast speeds of up to 20GB per second via a single cable. Memorex HDMI cables have been constructed and designed for optimum signal transfer and high performance featuring 24k gold plated connectors and oxygen-free copper coating. These cables have undergone rigorous testing to ensure they can deliver the most crystal clear signals possible. They are offered in 4 feet, 8 feet and 16 feet to accommodate any home theatre configuration, and will begin shipping to retailers starting in spring 2009 for an SRP of $34.99, $39.99 and $59.99, respectively.</p>

<p>Other cables from the Memorex family include:</p>

<p>    * Component Video Cables for clear, vibrant images and clearer sound from video sources including DVD players, satellite receivers, cable boxes and digital video recorders. The 8-foot Memorex Component Video Cable will begin shipping to retailers starting in spring 2009 for an SRP of $34.99.<br />
    * Composite Video Coax Digital Cables are designed to minimize signal distortion. This is the perfect conduit to carry low frequency analog video signals of up to 4.2MHz, and digital audio signals up to 24.576MHz from your camcorder, VCR, DVD player and cable/satellite box to your television display or monitor. The 8-foot Memorex Composite Video Coax Digital Cable will begin shipping to retailers starting in spring 2009 for an SRP of $29.99.<br />
    * Digital Optical Audio Cables offer smoother, more detailed and undistorted sounds from audio/video components. These digital optical fiber cables address the corruption of timing or "jitter" which causes sound distortion. Offered in 8-feet, the Memorex Digital Optical Audio Cable will begin shipping to retailers starting in spring 2009 for an SRP of $35.99.<br />
    * Stereo Audio Cables deliver clear, high-quality sound from audio components. High-quality metals such as long grain copper conductors enable accurate, low distortion analog audio signal transmission. The 8-foot Memorex Stereo Audio Cable will begin shipping to retailers starting in spring 2009 for an SRP of $29.99.<br />
    * Subwoofer Cables allow you to experience floor-shaking bass and the ultimate realism in sound from your home theatre audio system. The cable uses two identical solid 1.25 percent silver conductors for positive and negative charges, eliminating any strand-interaction distortion. The cables also feature silver-plated copper (SPC) conductors which create an "artificial edge" to enhance the articulation and intelligibility of subwoofer or low frequency sounds. Memorex Subwoofer Cables come with chassis ground connections to prevent hum, and are offered in 12 feet. They will begin shipping to retailers starting in spring 2009 for an SRP of $44.99.</p>

<p><br />
<B>About Memorex</B></p>

<p>Memorex is one of the most trusted and recognized consumer brands in modern marketing history. A portfolio brand of Imation Corp. (NYSE: IMN), Memorex is the North American market share leader in optical media and media accessories at retail and one of the best known names in the consumer electronics industry. Memorex reaches into millions of homes with home audio and video products, MP3 players, digital picture frames, iPod&reg; accessories, and LCD televisions that are stylish and simple in form and function. For more information about Memorex, please visit www.memorex.com.</p>

<p>Memorex, the Memorex logo, and Imation are trademarks of Imation Corp. and its subsidiaries. All other trademarks are property of their respective owners. </p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>January  7, 2009 07:31 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 1608
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
 			<h2>More on HD DVD & Blu-ray</h2><ul><?
 			$sql = "
 			SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name
 			FROM mt_entry e, mt_author a, mt_placement p, mt_category c
 			WHERE entry_author_id = author_id
 				AND e.entry_id = p.placement_entry_id
 				AND p.placement_category_id = c.category_id
 				AND category_label = 'HD DVD & Blu-ray'
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
 				AND entry_id <> 1608
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2009/01/memorex_blu-ray_disc_player_hdmi_cables_and_soundbar.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
