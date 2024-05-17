<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');

	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1640";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);

	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";

	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1640 AND placement_is_primary = 1";
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
	<meta name="keywords" content="blu ray, home theater, aquos lcd, high definition, ray player, sharp, Sharp, new, AQUOS, aquos, lcd, LCD, sound, Blu, home, blu, ray, audio, models, theater, high, player, video, digital, available" />
	<meta name="description" content="At CES 2009, Sharp is showcasing several revolutionary products and technologies that demonstrate the company's drive to innovate and improve the LCD industry with increased performance, the widest array of screen size offerings, improvements in energy efficiency and dazzling new designs. While maintaining its commitment to the LCD industry, Sharp is also incorporating its leading display technologies into the video industry with new Blu-ray introductions and an array of..." />
	<title>HDTV Magazine Bulletins - Sharp&reg; Demonstrates Continued Leadership in LCD Innovation and Introduces Cutting-Edge Video and Audio Technologies at CES 2009</title>
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

		$base_url = strleftback(PHP_SELF, '/') . '/sharp_demonstrates_continued_leadership_in_lcd_innovation_and_introduces_cutting-edge_video_and_audio_technologies_at_ces_2009';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Sharp&reg; Demonstrates Continued Leadership in LCD Innovation and Introduces Cutting-Edge Video and Audio Technologies at CES 2009'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2009/01/sharp_demonstrates_continued_leadership_in_lcd_innovation_and_introduces_cutting-edge_video_and_audio_technologies_at_ces_2009.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Sharp&reg; Demonstrates Continued Leadership in LCD Innovation and Introduces Cutting-Edge Video and Audio Technologies at CES 2009</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>January  7, 2009</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Products & Equipment">Products & Equipment</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2009/01/sharp_demonstrates_continued_leadership_in_lcd_innovation_and_introduces_cutting-edge_video_and_audio_technologies_at_ces_2009.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2009/01/sharp_demonstrates_continued_leadership_in_lcd_innovation_and_introduces_cutting-edge_video_and_audio_technologies_at_ces_2009.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2009/01/sharp_demonstrates_continued_leadership_in_lcd_innovation_and_introduces_cutting-edge_video_and_audio_technologies_at_ces_2009.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2009/01/sharp_demonstrates_continued_leadership_in_lcd_innovation_and_introduces_cutting-edge_video_and_audio_technologies_at_ces_2009.php&amp;phase=2&amp;title=Sharp%26reg%3B%20Demonstrates%20Continued%20Leadership%20in%20LCD%20Innovation%20and%20Introduces%20Cutting-Edge%20Video%20and%20Audio%20Technologies%20at%20CES%202009&amp;bodytext=At%20CES%202009%2C%20Sharp%20is%20showcasing%20several%20revolutionary%20products%20and%20technologies%20that%20demonstrate%20the%20company%27s%20drive%20to%20innovate%20and%20improve%20the%20LCD%20industry%20with%20increased%20performance%2C%20the%20widest%20array%20of%20screen%20size%20offerings%2C%20improvements%20in%20energy%20efficiency%20and%20dazzling%20new%20designs.%20While%20maintaining%20its%20commitment%20to%20the%20LCD%20industry%2C%20Sharp%20is%20also%20incorporating%20its%20leading%20display%20technologies%20into%20the%20video%20industry%20with%20new%20Blu-ray%20introductions%20and%20an%20array%20of...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p class="prtitle">Sharp&reg; Demonstrates Continued Leadership in LCD Innovation and Introduces Cutting-Edge Video and Audio Technologies at CES 2009</p>

<center><i>Next-generation AQUOS&reg; LCD TV with built-in Blu-ray&trade; player, new 120 Hz offerings, new Blu-ray products highlight Sharp's CES lineup</i></center><br />
<br />

<p><br />
<B>LAS VEGAS--(BUSINESS WIRE)</B>--At CES 2009, Sharp is showcasing several revolutionary products and technologies that demonstrate the company's drive to innovate and improve the LCD industry with increased performance, the widest array of screen size offerings, improvements in energy efficiency and dazzling new designs. While maintaining its commitment to the LCD industry, Sharp is also incorporating its leading display technologies into the video industry with new Blu-ray introductions and an array of powerful and stylish new audio products.</p>

<p>"As a leading presence in the LCD industry, Sharp continues to dedicate considerable resources and steadfast commitment to improving and furthering LCD technology, giving consumers one-of-a-kind product enhancements that improve their lifestyles," said Bob Scaglione, senior vice president and group manager, Product and Marketing Group, Sharp Electronics Corporation. "As in years past, Sharp continues to show why we are an LCD leader. But we're also introducing several new products that complete the home theater experience, to give consumers both the highest image quality available and powerful cinema-like sound."</p>

<p>Demonstrating the company's complete digital home theater prowess, Sharp is introducing the world's first LCD TV with a built-in Blu-ray player, for a convenient all-in-one home theater solution. Sharp is also expanding its 120 Hz offerings and debuting a brand new screen size class -- an 82-inch class LCD display that expands the company's extensive range of flat-panel Liquid Crystal Display (LCD) screen sizes. The booth is packed with an array of styles and sizes of AQUOS LCD TVs, with full-HD 1080p models in seven screen size classes as well as the availability of 120Hz in seven new AQUOS TVs. The newest lines of large-screen AQUOS HDTVs feature enhanced contrast ratios, response times that are among the fastest in the industry, and more inputs.</p>

<p>In addition to two new advanced Blu-ray players featuring BD-ROM Profile 2.0 for a multitude of interactive features through an Internet connection, Sharp is introducing several audio products that incorporate superior sound technology. A new audio home theater system with built-in Blu-ray as well as the company's first Sound Bar products, offer consumers improved sound to match the high-quality images on an AQUOS LCD TV and low-profile designs to complement modern home decors. To further complement the flat-panel TV revolution and exceptional new digital technologies, other highlights include new versions of the stylish docking systems made for iPod&reg; that allow the user to charge and play music directly from any iPod as well as a new affordable 1080p home theater projector. For detailed information on Sharp's new products, please see the individual product announcements.</p>

<p><br />
<B>AQUOS HDTV BD Series (models LC-52BD80U, LC-46BD80U, LC-42BD80U, LC-37BD60U and LC-32BD60U)</B></p>

<p>Sharp introduces the world's first high-definition AQUOS LCD TV Series with built-in Blu-ray DiscTM player, setting a new standard for home entertainment. The new 1080p AQUOS LCD / Blu-ray player combination product offers the convenience of an all-in-one home theater solution with a new Superlucent Advanced Super View (ASV) panel for a dramatically bright and crisp picture. The Superlucent ASV panel applies an ultra-smooth finish that minimizes gloss while intensifying panel brightness and contrast. A new AQUOS Pure Mode enables convenient optimized viewing of Blu-ray titles. Available in 52- (52-1/32" diagonal), 46- (45-63/64" diagonal), 42- (42-1/64" diagonal), 37- (37" diagonal) and 32-inch (31-35/64" diagonal) screen size classes, these models offer a slim frame with a new elegant "AQUOS Blue" design that includes a subtle blue accent at the bottom of the frame. The units also include a swivel stand for viewing convenience. The LC-52BD80U, LC-46BD80U and LC-42BD80U include Fine Motion Enhanced technology for 120 Hz Frame Rate Conversion, a 10-bit panel for Deep Color compatibility and a special "dejudder" feature that reduces background motion noise. All five models offer fast pixel response time and wide viewing angles of 176 degrees, so users can view the television from virtually anywhere in the room. Additionally, all units in this series include a multi-slot for BD, DVD and CD content. A single step operation feature turns on the TV and activates play when a BD disc is inserted. The series offers extensive HDMI inputs, with four on the LC-52BD80U, LC-46BD80U and LC-42BD80U and three on the LC-37BD60U and LC-32BD60U. The LC-52BD80U and LC-46BD80U will be available in February; pricing is TBD. The LC-42BD80U, LC-37BD60U and LC-32BD60U will available in January; pricing is TBD.</p>

<p><br />
<B>AQUOS HDTV E Series (models LC-65E77U, LC-52E77U, LC-46E77U, LC-40E77U, LC-40E67U, LC-32E67U)</B></p>

<p>The new widescreen series of Full HD1080p HDTV AQUOS LCD TVs, including the E77U models and the E67U models, rounds out a lineup of new Full HD 1080p LCD TVs that bring enhanced picture quality to the forefront. The E Series features the E77U models in 65- (64 33/64" diagonal), 52- (52 1/32" diagonal), 46- (45 63/64" diagonal) and 40-inch (TBD diagonal) screen class sizes (LC-65E77U, LC-52E77U, LC-46E77U and LC-40E77U respectively), and the E67U models in 40- (TBD diagonal) and 32-inch (31 35/64" diagonal) screen class sizes (LC-40E67U and LC-32E67U respectively). The E77 models feature Sharp's new 10-bit Superlucent Advanced Super View (ASV) panel for a dramatically bright and crisp picture with reduced haze and reflectivity. These models also include Fine Motion Enhanced technology for 120 Hz Frame Rate Conversion and a special dejudder feature that helps eliminate background motion artifacts on Blu-ray movies, fast pixel response time of 4 ms, and wide viewing angles of 176 degrees, so users can view the television from virtually anywhere in the room. Additionally, these units include five HDMI inputs and two HD component terminals, all of which are compatible with 1080p signals from Blu-ray and other new devices, in addition to RS-232C for custom installations and a dedicated PC input. The new 1080p E67 models incorporate Sharp's Superlucent ASV technology, providing the most detailed Full HD 1080p picture possible. Additionally, these models come fully equipped with four HDMI inputs with 24p input capability and two component video inputs, all 1080p compatible. The LC-32E67U adds Sharp's Vyper Drive technology for video game enthusiasts. This feature reduces the lag time between the game console input and the TV display to imperceptible levels. The full E series is compliant with the most recent Energy Star&reg; standards and also offers energy saving through Sharp's OPC function, to automatically adjust the unit's brightness based on the lighting of the room. The models offer a new design, with a black cabinet that gives way to a soft gold hue that accents the bottom of the E77 series frame, and a subtle copper hue at the bottom of the E67 cabinet. The LC-65E77U will be available in June for an MSRP of $4,499.99. The LC-52E77U, LC-46E77U and LC-32E67U will be available in February for MSRPs of $2,399.99, $2,099.99 and $899.99 respectively. The LC-40E77U and LC-40E67U will be available in March for MSRPs of $1,399.99 and $1,199.99.</p>

<p><br />
<B>82-inch Screen Size Class High-Definition LCD</B></p>

<p>Sharp announces the world's first 82-inch class Full-HD 1080p LCD Monitor, expanding the company's extensive range of flat-panel Liquid Crystal Display (LCD) screen sizes. The LC-82MX1U was designed for the true home theater enthusiast and features the next generation of Sharp's proprietary 10-bit Advanced Super View (ASV) / Black TFT Panel, which provides deep blacks and crisp picture quality. This new size category was developed at Sharp's 8th generation Kameyama Plant No. 2, to fill the space between the 65- (64-17/32" diagonal) and 108-inch (107.5" diagonal) screen classes. The LC-82MX1U features Fine Motion Enhanced technology for 120Hz Frame Rate Conversion and a fast pixel response time of 4ms, ensuring smooth, flowing motion in fast-action scenes and an overall immersive experience. With Sharp's 10-bit ASV / Black TFT Panel, the display offers Spectral Contrast Engine XD (Extreme Dark), providing high Dynamic Contrast for deep blacks and crisp picture quality. This large-screen model also includes an array of connection options, including HDMI&trade; and DVI-I connectors, for greater connectivity with a wide variety of equipment and devices.</p>

<p><br />
<B>AQUOS Blu-ray Disc&trade; Player BD-HP22U</B></p>

<p>The slim-profile AQUOS&reg; Blu-ray Disc&trade; player, model BD-HP22U, provides Full HD 1080p digital output that when paired with an AQUOS LCD TV allows consumers to appreciate the superior quality of high-definition audio and video content. The Blu-ray player supports BD-ROM Profile 2.0, also known as BD-Live, which provides a multitude of interactive features through an Internet connection. Users can download and stream bonus content such as additional scenes, shorts, trailers and multi-player interactive games, providing the consumer full access to content otherwise unattainable. In addition to the BD-Live upgrade, the BD-HP22U incorporates several added features that help to improve the home theater experience, including advanced audio decoding, a high quality picture with AQUOS Pure Mode, and lower power consumption in both Power On mode and in Standby mode (19W power consumption). With AQUOS Pure mode, the new Blu-ray Disc players connect to a Sharp AQUOS LCD TV with AQUOS Link function via an HDMI&reg; cable to deliver video content with unparalleled clear contrast and details. Sharp engineers designed this mode so that the BD player recognizes the connection to the AQUOS TV, in turn producing the best picture possible. The BD-HP22U enables superior video quality with state-of-the-art HDMI 1.3 digital output with x.v. color, and 1920 x 1080 video at 24 frames per second output, matching the playback of the original film. For added convenience, the BD players include Sharp's proprietary Quick Start feature for quick disc loading. The player outputs the most advanced lossless surround-sound formats including Dolby&reg; TrueHD and DTS HD Master Audio via HDMI digital output. The player also decodes Dolby Digital Plus, which provides optimum surround sound for appropriately equipped receivers. The BD-HP22U is compatible with a wide variety of formats including BD-ROM/RE/R, DVD Video, DVD-RW/R, DVD+RW/R, and Audio CDs. It also includes 2GB of USB memory and includes a jpeg viewer. The BD-HP22U will be available in May for an MSRP of $299.99.</p>

<p><br />
<B>AQUOS BD Audio Home Theater Systems (models BD-MPC40 and BC-MPC30)</B></p>

<p>Sharp introduces a compact full audio/video home theater system, designed to satisfy both audio and video enthusiasts. Joining the Sharp suite of AQUOS-brand products, including the extensive line of LCD TVs and, most recently, Blu-ray players, the new AQUOS BD-MPC40 and BD-MPC30 include the main unit, housing a Blu-ray player and amplifier, as well as five speakers and a subwoofer. The systems pack a powerful punch (720W power) and provide room-filling 5.1-channel surround sound. A high-gloss piano-black finish on the main unit matches the styling of AQUOS TVs, accompanied by black wooden cabinet speakers with the BD-MPC40, and black synthetic finish speakers with the BD-MPC30, allowing for limitless design and décor options that complement any room of the home. The home theater systems incorporate Dolby TrueHD and DTS-HD Master Audio surround sound capabilities for a true theater sound experience. The main unit includes a Blu-ray player for full high-definition 1080p/24 Hz output, creating a true cinematic experience in the comfort of the living room. The player supports BD-ROM Profile 2.0, also know as BD-Live, which provides a multitude of interactive features through an Ethernet jack-enabled Internet connection. Users can download and stream bonus content such as additional scenes, shorts, trailers and multi-player interactive games, providing the consumer full access to content otherwise unattainable. Additionally, the Blu-ray player features AQUOS Pure Mode, which automatically senses the aspect ratio of the Blu-ray title being played and optimizes the TV's view mode for the best possible HD picture possible. The systems come with an HDMI&reg; 1.3 digital output, allowing Blu-ray discs to be viewed in complete digital 1080p/24Hz high-definition. Providing outstanding versatility, the Blu-ray player is compatible with a wide variety of formats including BD-ROM/RE/R, DVD Video, DVD-RW/R, DVD+RW/R, and Audio CDs. It is also compatible with standard DVDs and is capable of upscaling them via HDMI to 1080p, improving the picture performance of an existing DVD library. The BD-MPC40 and BD-MPC30 will be available this spring for an MSRP of $799.99.</p>

<p><br />
<B>2.1 Sound Bar Home Theater Systems (models HT-SB300 and HT-SB200)</B></p>

<p>Sharp's new 2.1-channel sound bars, models HT-SB300 and HT-SB200, offer enhanced audio technology and easy setup for a dynamic home theater experience. The models feature a slim profile design, enclosing the main left and right speaker drivers as well as the sub woofer in one thin, wall-mountable sound bar. The bar can also be placed on a TV stand (32-inch LCD and up), with included mounting plates to straddle the base of an LCD TV. The 34-watt sound bar combines advanced HDSS (high-definition sound standard) sound technology and SRS WOW HD Sound for a more natural listening experience and a deeper, more natural bass response. Both models feature adjustable Bass, Treble and Sub level options for a unique surround sound effect without rear channel speakers. The HT-SB300 adds digital audio decoding with DTS, Dolby&reg; Digital and Dolby&reg; Pro logic II decoder, as well as Dolby&reg; Virtual Speaker to effectively simulate 5.1 channel surround sound. For flexibility and simple setup, both sound bars feature dual audio inputs, right and left RCA jacks and a 3.5mm sub mini jack, which allows for a second audio source such as an MP3 player. The HT-SB300 adds a subwoofer output jack as well. Both models also include a slim remote control with Sharp AQUOS LCD TV control, a programmed equalizer, and an auto On/Off function. The HT-SB300 will be available in April for an MSRP of $299.99; the HT-SB200 will be available in January for an MSRP of $249.99.</p>

<p><br />
<B>1080p DLP&reg; Home Theater Front Projector (model XV-Z15000)</B></p>

<p>Sharp brings home theater to the forefront with a new 1080p DLP home theater front projector that represents an outstanding value in the home theater market. The XV-Z15000 projector bundles an unprecedented 30,000:1 dynamic contrast ratio and high brightness in a price-competitive model, recreating a true cinematic experience. Featuring a sleek high-gloss black design, the XV-Z15000 utilizes a single 1080p DLP 0.65" DMD chip from Texas Instruments (TI) to create one of the most spectacular images available in projection today. The true widescreen 16:9 aspect ratio projector provides cinema-quality images so consumers can enjoy movies in their original widescreen format, from the comfort of their own home. A 24 Hz film mode delivers the accurate representation of the film's originally intended frame rate, giving consumers a more realistic viewing experience. A high brightness level of 1600 ANSI lumens delivers a magnificent and crystal-clear picture and a 6 Segment 6-Speed color wheel achieves flicker-free, high-grade images and accurate color reproduction for an uninterrupted image. A powered iris switchover function gives the consumer enhanced control over brightness and contrast settings with the touch of a button on the remote control, providing flexibility in varying home theater environments with different lighting situations. For easier installations, the model includes two HDMI&trade; terminals (version 1.3 with x.v.Color) to ensure a secure digital connection with all high definition set top boxes or Blu-ray players and an RS-232C input for custom installations. The XV-Z15000 will be available in March for an MSRP of $2,999.99.</p>

<p><br />
<B>Music System for iPod&reg; (Model DK-AP7N)</B></p>

<p>This small, yet powerful, 2.1 channel audio system features an ultra-portable design that folds closed for safe keeping when on the go. The single system houses all the necessary components for an enjoyable listening experience, including the main drivers and subwoofer. With five hours of battery operation and an AC adapter and soft carry bag included, the DK-AP7N is truly a portable solution to enjoying high-quality audio from any location. The iPod terminal allows the user to charge and play music directly from any iPod through the unit's full range bass reflex speakers with HDSS (high-definition sound standard) sound technology. For optimum sound quality, the unit offers Esound, a digital signal processing technology that improves the quality of compressed digital music. By enhancing the sound frequency and increasing the sound pressure, ESound mode corrects deterioration to the sound quality that plagues most compressed music. The DK-AP7N also includes a video output so that when connected to a TV, users can enjoy their favorite iPod videos on a larger screen. The portable The DK-AP7N will be available in March for an MSRP of $129.99.</p>

<p>For more information on Sharp's full line of products, contact Sharp Electronics Corporation, Sharp Plaza, Mahwah, N.J. 07495-1163, or call 800-BE-SHARP. For online product information, visit Sharp's Web site at sharpusa.com.</p>

<p>Sharp Electronics Corporation is the U.S. subsidiary of Japan's Sharp Corporation, a worldwide developer of one-of-a-kind home entertainment products, appliances, networked multifunctional office solutions, solar energy solutions and mobile communication and information tools. Leading brands include AQUOS&reg; Liquid Crystal Televisions, 1-Bit&trade; digital audio products, SharpVision&reg; projection products, Insight&reg; Microwave Drawer&reg; appliances, Plasmacluster&reg; air purifiers, and Notevision&reg; multimedia projectors. For more information visit Sharp Electronics Corporation at www.sharpusa.com</p>

<p>* Quick Start time may vary depending on movie content, type of video connection, and type of monitor being used</p>

<p>AQUOS is a registered trademark of Sharp Corporation</p>

<p>HDMI, the HDMI logo and High Definition Multimedia Interface are trademarks of HDMI Licensing, LLC.</p>

<p>DLP is a trademark of Texas Instruments.</p>

<p>iPod is a trademark of Apple.</p>

<p>Energy Star is a trademark of the EPA.</p>

<p>All other trademarks are the property of their respective owners. </p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>January  7, 2009 05:27 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 1640
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
 				AND entry_id <> 1640
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2009/01/sharp_demonstrates_continued_leadership_in_lcd_innovation_and_introduces_cutting-edge_video_and_audio_technologies_at_ces_2009.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
