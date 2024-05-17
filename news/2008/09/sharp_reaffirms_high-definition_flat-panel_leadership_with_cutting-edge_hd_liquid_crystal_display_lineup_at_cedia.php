<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1531";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1531 AND placement_is_primary = 1";
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
	<meta name="keywords" content="high definition, blu ray, aquos lcd, picture quality, liquid crystal, AQUOS, aquos, sharp, Sharp, high, models, available, series, lcd, LCD, definition, new, hdmi, HDMI, digital, quality, well, screen, Blu, blu" />
	<meta name="description" content="With a groundbreaking AQUOS&amp;reg; LCD TV introduction at this year's CEDIA Expo, Sharp is solidifying its consumer electronics leadership with a product line-up that reflects the combination of advanced technology and contemporary styling that today's consumers demand. In addition to the next-generation LCD TV ultra-thin Limited Edition series, Sharp is announcing a new AQUOS LCD TV series with 120 Hz technology, and the company is also gaining momentum in the highly-sought-after 42-inch (diagonal 42 1/64&quot;) screen class category, with a 42-inch (diagonal 42 1/64&quot;) model available in several new LCD TV widescreen series.

Complementing the AQUOS LCD TV introductions are two new Sharp AQUOS Blu-ray players, which..." />
	<title>HDTV Magazine Bulletins - Sharp Reaffirms High-Definition Flat-Panel Leadership with Cutting-Edge HD Liquid Crystal Display Lineup at CEDIA</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/sharp_reaffirms_high-definition_flat-panel_leadership_with_cutting-edge_hd_liquid_crystal_display_lineup_at_cedia';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Sharp Reaffirms High-Definition Flat-Panel Leadership with Cutting-Edge HD Liquid Crystal Display Lineup at CEDIA'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2008/09/sharp_reaffirms_high-definition_flat-panel_leadership_with_cutting-edge_hd_liquid_crystal_display_lineup_at_cedia.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Sharp Reaffirms High-Definition Flat-Panel Leadership with Cutting-Edge HD Liquid Crystal Display Lineup at CEDIA</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>September  4, 2008</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Products & Equipment">Products & Equipment</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2008/09/sharp_reaffirms_high-definition_flat-panel_leadership_with_cutting-edge_hd_liquid_crystal_display_lineup_at_cedia.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2008/09/sharp_reaffirms_high-definition_flat-panel_leadership_with_cutting-edge_hd_liquid_crystal_display_lineup_at_cedia.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2008/09/sharp_reaffirms_high-definition_flat-panel_leadership_with_cutting-edge_hd_liquid_crystal_display_lineup_at_cedia.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2008/09/sharp_reaffirms_high-definition_flat-panel_leadership_with_cutting-edge_hd_liquid_crystal_display_lineup_at_cedia.php&amp;phase=2&amp;title=Sharp%20Reaffirms%20High-Definition%20Flat-Panel%20Leadership%20with%20Cutting-Edge%20HD%20Liquid%20Crystal%20Display%20Lineup%20at%20CEDIA&amp;bodytext=With%20a%20groundbreaking%20AQUOS%26reg%3B%20LCD%20TV%20introduction%20at%20this%20year%27s%20CEDIA%20Expo%2C%20Sharp%20is%20solidifying%20its%20consumer%20electronics%20leadership%20with%20a%20product%20line-up%20that%20reflects%20the%20combination%20of%20advanced%20technology%20and%20contemporary%20styling%20that%20today%27s%20consumers%20demand.%20In%20addition%20to%20the%20next-generation%20LCD%20TV%20ultra-thin%20Limited%20Edition%20series%2C%20Sharp%20is%20announcing%20a%20new%20AQUOS%20LCD%20TV%20series%20with%20120%20Hz%20technology%2C%20and%20the%20company%20is%20also%20gaining%20momentum%20in%20the%20highly-sought-after%2042-inch%20%28diagonal%2042%201%2F64%22%29%20screen%20class%20category%2C%20with%20a%2042-inch%20%28diagonal%2042%201%2F64%22%29%20model%20available%20in%20several%20new%20LCD%20TV%20widescreen%20series.%0A%0AComplementing%20the%20AQUOS%20LCD%20TV%20introductions%20are%20two%20new%20Sharp%20AQUOS%20Blu-ray%20players%2C%20which...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p class="prtitle">Sharp Reaffirms High-Definition Flat-Panel Leadership with Cutting-Edge HD Liquid Crystal Display Lineup at CEDIA</p>

<center><i>Next-generation AQUOS LCD TV, new 120Hz offerings and a 1080p Full HD Blu-ray Disc&trade; player demonstrate Sharp's role as a leading digital innovator</i></center><br />
<br />

<p><B>DENVER--(BUSINESS WIRE)</B>--With a groundbreaking AQUOS&reg; LCD TV introduction at this year's CEDIA Expo, Sharp is solidifying its consumer electronics leadership with a product line-up that reflects the combination of advanced technology and contemporary styling that today's consumers demand. In addition to the next-generation LCD TV ultra-thin Limited Edition series, Sharp is announcing a new AQUOS LCD TV series with 120 Hz technology, and the company is also gaining momentum in the highly-sought-after 42-inch (diagonal 42 1/64") screen class category, with a 42-inch (diagonal 42 1/64") model available in several new LCD TV widescreen series.</p>

<p>Complementing the AQUOS LCD TV introductions are two new Sharp AQUOS Blu-ray players, which allow consumers to view the latest high-definition films as well as enhanced definition titles in stunning high-definition quality.</p>

<p>"Sharp continues to expand its high-definition offerings and push LCD technology to new limits, offering our new, advanced Limited Edition Series with unmatched image quality and design, and a complete line of full HD display products," said Bob Scaglione, senior vice president and group manager, Product and Marketing Group, Sharp. "Sharp is committed to leading the high-definition products industry, and our CEDIA booth reflects that commitment. We are providing consumers with an unmatched array of LCD screen sizes, color and style choices and our new Blu-ray players offer consumers a true cinematic experience in the comfort of their homes."</p>

<p>Demonstrating the company's complete digital home theater prowess, Sharp's CEDIA 2008 booth will introduce the next-generation AQUOS Limited Edition Series, providing the ultimate in design and picture quality. With a depth of only one inch at its thinnest point, the new models provide an extremely small footprint. The new AQUOS models also achieve dramatic power reduction by intelligently balancing picture control and backlight intensity.</p>

<p>The 2008 CEDIA Sharp Electronics booth features a myriad of styles of AQUOS LCD TVs, ranging from the company's widescreen 19-inch (18 ½" diagonal) screen class AQUOS LCD TV offering, up through an impressive 108-inch (107.5" diagonal) screen class sizes with full HD 1080p models in seven screen size classes as well as the availability of 120Hz in eight AQUOS TVs. The booth is also packed with advanced digital technologies that complement the flat-panel TV revolution and one-of-a-kind new digital technologies. In addition to AQUOS LCD TVs and Blu-ray, highlights include a range of mini- and micro- audio shelf systems that allow the user to charge devices and play music directly from any iPod&reg; device.</p>

<p>For detailed information on Sharp's new products, please see the individual product announcements available for media.</p>

<p><br />
<B>AQUOS HDTV Limited Edition Series (models LC-65XS1U-S and LC-52XS1U-S)</B></p>

<p>Sharp's groundbreaking, next-generation AQUOS LCD TV Limited Edition Series represents the ultimate in design and picture quality. With a depth at its thinnest point of only one inch, the new 65- and 52-inch Class (64 33/64" and 52 1/32" diagonal respectively) ultra-thin models provide an extremely small footprint, establishing a new design standard for LCD TV and allowing for beautiful, unobtrusive wall-mounting placement. Both models incorporate an ultra-high performance RGB LED backlight, for an unprecedented 150 percent NTSC color gamut and extremely high Dynamic Contrast Ratio of more than 1,000,000:1 for extremely deep blacks and crisp picture quality, as well as Sharp's AQUOS Net Web-based service. These models also offer several energy-saving features, with very low power consumption. The Limited Edition models are equipped with a new system designed to lower power consumption during use by adjusting the screen brightness based on the level of brightness in the area of installation. Contributing to the slim frame of the screen, this next-generation Series has a separate AVC system set-top box for all input terminals that can be connected to the screen via the included HDMI&trade; cable or with an optional wireless connection to eliminate cable clutter. Additionally, the units include five HDMI inputs as well as dual HD component terminals, all of which are compatible with 1080p signals. For the ultimate in convenience, one HDMI and one component terminal are located on the front of the AVC system, enabling easy connections. The unit also houses a separate speaker with Sharp's 1-Bit amplifier for clear and natural sound, as well as an RS-232C port for custom installations and a dedicated PC input.</p>

<p><br />
<B>AQUOS HDTV D85U Series (models LC-52D85U, LC-46D85U and LC-42D85U)</B></p>

<p>The new widescreen series of Full HD1080p HDTV AQUOS Liquid Crystal Televisions, available in 52-, 46- and 42-inch Classes (52 1/32", 45 63/64" and 42 1/64" diagonal respectively) sets a new standard for large-screen flat-panel TVs. The D85U series features Sharp's proprietary 10 bit Advanced Super View panel with Spectral Contrast Engine UD (Ultra Dark), providing high Dynamic Contrast ratio for deep blacks and crisp picture quality. The models include Fine Motion Enhanced technology for 120 Hz Frame Rate Conversion, fast pixel response time of 4 ms, and wide viewing angles of 176 degrees, so users can view the television from virtually anywhere in a room. Additionally, all units in this series include five HDMI inputs and two HD component terminals, all of which are compatible with 1080p signals from Blu-ray Disc players and other new devices, in addition to RS-232C for custom installations and a dedicated PC input. These newly redesigned models feature a stunning piano black cabinet with a titanium outer frame that takes on the hue of the colors in the room, along with subtle recessed, bottom-mounted speakers and include a detachable table stand. The D85U series is Energy Star&reg; qualified and also offers a new energy-saving function. A "Power Saving Mode" is available through the unit's on-screen display menu, which enables active contrast and active backlight to reduce the energy of the television while in use. The LC-52D85U and LC-46D85U will be available in October for MSRPs of $2,599.99 and $2,199.99 respectively; the LC-42D85U will be available in November for an MSRP of $1,899.99.</p>

<p><br />
<B>AQUOS HDTV D65U Series (models LC-52D65U, LC-46D65U and LC-42D65U)</B></p>

<p>The widescreen series of Full HD1080p HDTV AQUOS LCD TVs, available in 52" Class (52 1/32" diagonal), 46" Class (45 63/64" diagonal) and 42" Class (42 1/64" diagonal) screen size class units, features a slim design and breathtaking picture quality that is second to none. This Full HD 1080p TV series features high performance, providing the ultimate home entertainment experience with the perfect balance of style and function. It uses Sharp's proprietary Advanced Super View LCD panel* for an unparalleled high-definition viewing experience. The ASV/Black TFT Panel with Spectral Contrast Engine UD (Ultra Dark) provides high Dynamic Contrast Ratio and a pixel response time of 6ms for stunning picture quality even on fast-moving action scenes, as well as impressive 176 degree viewing angles, enabling the consumer to view the TVs from virtually anywhere in a room. Rounding out the enhanced list of specs is the D65U's array of inputs, including an impressive five HDMI inputs and two HD component video inputs, all 1080p compatible, an RS-232C input for control, and a PC input so the TV can be used as a PC monitor, saving space and reducing clutter. The series is also Energy Star-compliant, with very low power consumption and offers a new "Power Saving Mode" available through the unit's on-screen display menu, which enables active contrast and active backlight to reduce the energy of the television while in use. The D65U series will be available with a stunning piano black inner bezel and a matching black outer frame, along with subtle recessed, bottom-mounted speakers and a detachable table stand. The LC-52D65U and LC-46D65U will be available in October for MSRPs of $2,399.99 and $1,899.99 respectively, the LC-42D65U is available now for an MSRP of $1,599.99.</p>

<p><br />
<B>AQUOS HDTV SE94 Series (models LC-65SE94U, LC-52SE94U and LC-46SE94U)</B></p>

<p>Sharp's elegantly styled "Special Edition" SE94 widescreen AQUOS LCD TV series, available in 65" Class (64 33/64" diagonal), 52" Class (52 1/32" diagonal) and 46" Class (46" diagonal) screen size class units, combines a best-in-class picture quality with a distinguished "Cornerstone" design and "AQUOS Net" capability. With the latest version of Sharp's proprietary Advanced Super View panel, the most advanced LCD panel in the world, it has full HD 1080p (1920 x 1080) resolution for an unparalleled high-definition viewing experience and high Dynamic Contrast Ratio for deep blacks and crisp picture quality. The SE94 series also features a 10-bit panel combined with Fine Motion Enhanced technology for 120 Hz Frame Rate Conversion, as well as fast 4ms response time and wide viewing angles (176 degrees). Through the unit's Ethernet jack, users have access to AQUOS Net, a revolutionary service providing customized Web-based content as well as real-time customer support. Using this service, viewers can create and configure "widgets" to check the local weather forecast or get stock quotes while watching their favorite television show. Also available through AQUOS Net is the AQUOS Advantage Live tool, which provides unparalleled customer support including the ability to have dedicated Sharp agents connect to the TV and remotely optimize picture quality or diagnose problems at the touch of a button. The "Special Edition" AQUOS series features Sharp's proprietary 5-wavelength backlight system that provides an enhanced color spectrum, producing more vivid, deeper reds and greens than previously possible. Additionally, all the units include 3 HDMI (version 1.3 with x.v.color and Deep Color) inputs as well as dual HD component terminals, all of which are compatible with 1080p signals. The unit also houses an RS-232C port for custom installations and a dedicated PC input. These models are available in a stunning, textured finish with eye-catching corner accents and detachable bottom speakers to match modern home décors, and include a detachable table stand. The LC-65SE94U, LC-52SE94U and LC-46SE94U are available now for Manufacturer's Suggested List Prices of $8,499.99, $3,299.99 and $2,599.99 respectively.</p>

<p><br />
<B>AQUOS Blu-ray Disc&trade; Player BD-HP21U</B></p>

<p>Sharp's new slim-profile AQUOS&reg; Blu-ray player, model BD-HP21U, lets consumers realize the powerful next generation of home entertainment enabled by the high-definition Blu-ray format. Combining full digital 1080p video output with an AQUOS HDTV Liquid Crystal Television, the BD-HP21U allows consumers to view the latest high-definition films as well as enhanced definition titles in stunning high-definition quality. This unit allows the users to access additional content from Blu-ray titles, including movie trailers, special subtitles/audio and games. Performing as if it were all one system, the BD-HP21U's Quick Start feature lets you enjoy gorgeous Blu-ray Disc&trade; video with the touch of a button in less than 10 seconds**. Through an HDMI cable connection, users can experience AQUOS LINK&trade;, which enables integrated and seamless operation between the Blu-ray disc player and AQUOS LCD TV. With the AQUOS LINK feature, products connected via HDMI cables can be controlled through the AQUOS screen with a single TV remote. With both analog and digital outputs, including HDMI&trade; connections at 1080p as well as DVD up-conversion of standard DVDs, users can enjoy full digital high-definition video and high-fidelity audio. JPEG digital images can be viewed as a slide show from recorded CD-RW/R with digital images. The player also offers multi-channel audio output via HDMI connections by decoding Dolby&reg; TrueHD and DTS HD Master Audio. It also decodes Dolby&reg; Digital Plus, providing optimum surround sound to an appropriately equipped receiver. The BD-HP21U is available now for an MSRP of $349.99.</p>

<p><br />
<B>AQUOS Blu-ray Disc&trade; Player BD-HP50U</B></p>

<p>The slim-profile AQUOS&reg; Blu-ray player, model BD-HP50U, features full digital 1080p video output, that when combined with an AQUOS HDTV Liquid Crystal Television, allows consumers to view the latest high-definition films as well as enhanced definition titles in stunning high-definition quality. This unit allows users to access additional content from Blu-ray titles, including movie trailers, special subtitles/audio and games. The user-friendly player features a built-in HDMI&trade; 1.3 digital output. This digital interface can be connected to an AQUOS LCD HDTV, allowing consumers to watch a Blu-ray disc in complete digital 1080p/24Hz high-definition with high-quality picture and high fidelity audio. For the ultimate convenience, users can operate the Blu-ray player and AQUOS LCD TV through AQUOS LINK&trade;, enabling seamless operation through one remote via the HDMI CEC control. The unit also outputs 1920 x 1080 24 fps (frames per second) high-definition video and features a Quick Start feature that allows the user to begin enjoying gorgeous Blu-ray video with a touch of a button in less than 10 seconds*. The player also offers multi-channel audio output via HDMI by decoding, as well as Bitstream output Dolby&reg; TrueHD, DTS HD Master Audio and Dolby&reg; Digital Plus, providing optimum surround sound to an appropriately equipped receiver. The BD-HP50U provides an RS-232C port for advanced control and is compatible with a wide variety of formats including BD-ROM/RE/R, DVD Video, DVD-RW/R, DVD+RW/R, and Audio CDs. Additionally, the BD-HP50U is compatible with standard DVDs and is capable of upscaling them via HDMI, improving the picture performance of an existing DVD library. The stylish powered front door with on-unit controls resonates high style design and quality in addition to RS-232 port for home theater control and a JPEG viewer for digital still image viewing on screen. The BD-HP50U is available now for a Manufacturer's Suggested List Price of $449.99.</p>

<p><br />
<B>Mini-Audio Shelf System (models CD-DK890N, CD-DK891N)</B></p>

<p>Sharp introduces a new stylish, yet practical mini-audio shelf system (models CD-DK890N and CD-DK891N) that offers the ideal audio solution for almost any environment. A built-in iPod&reg; docking slot allows users to play their music collection and charge their iPod device at the same time. The 250-watt systems include a two-way speaker system for precise, clean sound with X Bass for added bass response. These mini-audio systems also feature a five-disc CD changer that allows users to swap out four discs while a fifth is playing for uninterrupted music as well as a digital AM/FM tuner with 40 presets and a full-logic dual cassette deck. The audio systems are compatible with multiple formats including CD-R/CD-RW, MP3 and WMA. The CD-DK890N is available now with a black finish for an MSRP of $199.99; the CD-DK891N is available now with a silver finish for an MSRP of $199.99.</p>

<p><br />
<B>Sharp HDTV SB Series (models LC-52SB55U, LC-46SB54U and LC-42SB45U)</B></p>

<p>The new widescreen SB series of Full HD 1080p LCD TVs (models LC-52SB55U, LC-46SB54U and LC-42SB45U) provides consumers with affordably-priced large-screen options, available in 52-, 46- and 42-inch Class units (52 1/32", 45 63/64" and 42 1/64" diagonal respectively). These entry-level Full-HD 1080p (1920 x 1080 resolution) models open up the true high-definition viewing experience to more consumers, providing deep blacks and crisp images with Sharp's Spectral Contrast Engine. With excellent picture quality on fast-moving action scenes, all three models tout an impressive pixel response time of 6ms (6.5ms on the LC-42SB45U) and wide viewing angles so the TV can be viewed from virtually anywhere in a room. Additionally, these models come fully equipped with built-in ATSC/QAM/NTSC tuners and an array of inputs, including four HDMI&trade; inputs on the LC-52SB55U, two on the LC-46SB54U and three on the LC-42SB45U, as well as two HD component terminals on each. These units also come outfitted with a dedicated PC input so the TV screen can also function as a PC monitor. An OPC Power Save function automatically adjusts luminance to room brightness and lighting conditions. All SB models are cased in a glossy piano-black bezel with a detachable table stand for wall mounting applications. The LC-52SB55U and LC-46SB54U are available now, while the LC-42SB45U will be available this month for MSRPs of $2,299.99, $1,699.99 and $1,399.99 respectively.</p>

<p><br />
<B>AQUOS HDTV Gaming LCD TV (models LC-32GP3U-R, LC-32GP3U-W, LC-32GP3U-B)</B></p>

<p>Sharp's Full HD 1080p AQUOS gaming model series, the LC-32GP3U, is crafted specifically for video game enthusiasts and available in a 32-inch Class unit (31 35/64" diagonal). The GP3U set has a striking design, featuring a thin, "slim-line" body that provides a significantly smaller footprint than previous models, as well as a unique swivel stand for ultimate viewing and gaming flexibility. To complement any living room or game room, this AQUOS is available in three glossy-finish colors - black, red and white. The GP3U models also offers the same special features that enhance the game-playing experience as the previous gaming models, including a "game mode" which optimizes the picture quality for game-playing, and a custom-designed remote control that allows the user to quickly "jump" into the game mode, and access the side-placed terminals for easy connections to video games. The game mode provides a "Vyper Drive" Game Reaction Speed, which reduces lag time between the game console and the TV to be virtually imperceptible. The panel boasts an incredible 10,000:1 Dynamic contrast ratio, for deep blacks and crisp picture quality; enhanced Fine Motion video circuitry for fast pixel response time of 6 ms; and wide viewing angles of 176 degrees, so users can view the television from virtually anywhere in the room. It also includes Sharp's proprietary 4-wavelength backlight system that provides a wider color spectrum to achieve deeper, more vivid reds than was previously possible. With three HDMI v1.3 inputs (with one on the side) as well as two HD component terminals (one on the side), the LC-32GP3U models are compatible with 1080p signals from the latest video game devices, as well as supports 1920 x 1080 24 fps (frames per second) high definition video, the highest resolution possible via an HDMI connection. The LC-32GP3U-R, LC-32GP3U-W and LC-32GP3U-B are available now for an MSRP of $1,299.99.</p>

<p><br />
<B>AQUOS LC-19D45U</B></p>

<p>Sharp is showcasing a 19-inch Class (18 ½" diagonal) widescreen AQUOS&reg; model, the LC-19D45U LCD TV. Boasting a true 16:9 aspect ratio, 1366 x 768 resolution and a distinctive small footprint, the LC-19D45U delivers a stunning high-definition picture in a screen size that's perfect for secondary viewing spaces including kitchens, bedrooms and offices. The LC-19D45U is bringing the 16:9 viewing experience to smaller TVs, allowing movies to be seen in the aspect ratio originally intended by the director. The LC-19D45U includes ATSC/NTSC tuners to receive off-air broadcasts as well as digital cable QAM capability to receive non-scrambled digital cable programming***. In addition, the LC-19D45U features an HDMI input and PC compatibility, and provides outstanding picture quality. The LC-19D45U also has a unique remote with a built-in clock/timer and a magnetic back for placing the remote on metallic surface for ease of use. A timer function provides a convenient count down for use in a kitchen when cooking or in the bedroom to catch a favorite program. Model LC-19D45U is available now for an MSRP of $549.99.</p>

<p>For more information on Sharp's full line of Liquid Crystal Televisions, contact Sharp Electronics Corporation, Sharp Plaza, Mahwah, N.J. 07495-1163, or call 800-BE-SHARP. For online product information, visit sharpusa.com.</p>

<p>Sharp Electronics Corporation is the U.S. subsidiary of Japan's Sharp Corporation, a worldwide developer of one-of-a-kind home entertainment products, appliances, networked multifunctional office solutions, solar energy solutions and mobile communication and information tools. Leading brands include AQUOS&reg; Liquid Crystal Televisions, 1-Bit&trade; digital audio products, SharpVision&reg; projection products, Insight&reg; Microwave Drawer&reg; appliances, Plasmacluster&reg; air purifiers, and Notevision&reg; multimedia projectors. For more information visit Sharp Electronics Corporation at www.sharpusa.com</p>

<p>* LC-52D65U and LC-46D65U only</p>

<p>** Quick Start time may vary depending on movie content, type of video connection, and type of monitor being used</p>

<p>*** Contact local cable or satellite provider for available HDTV</p>

<p>Sharp and AQUOS are registered trademarks of Sharp Corporation</p>

<p>HDMI, the HDMI logo and High Definition Multimedia Interface are trademarks of HDMI Licensing, LLC.</p>

<p>iPod is a trademark of Apple</p>

<p>All other trademarks are property of their respective owners </p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>September  4, 2008 01:57 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 1531
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
 				AND entry_id <> 1531
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2008/09/sharp_reaffirms_high-definition_flat-panel_leadership_with_cutting-edge_hd_liquid_crystal_display_lineup_at_cedia.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
