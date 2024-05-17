<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 511";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 511 AND placement_is_primary = 1";
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
	<meta name="keywords" content="high definition, home theater, blu ray, contrast ratio, picture quality, sharp, Sharp, available, new, lcd, LCD, high, home, models, AQUOS, aquos, definition, msrp, MSRP, full, room, inch, theater, series, include" />
	<meta name="description" content="Sharp is once again raising the bar for LCD technology at 2007 CES with one-of-a-kind innovations and increased performance. The world's largest LCD TV, the 108-inch, is headlining the company's wide breadth of TV offerings, which also includes the newest lines of large-screen AQUOS HDTVs to come from the company's 8th generation factory in Kameyama, Japan, featuring enhanced contrast ratios, response times that are among the fastest in the industry, and more inputs than ever before. In addition, Sharp is showcasing..." />
	<title>HDTV Magazine Bulletins - Sharp Announces 108-inch LCD TV, Blu-ray Player, and More</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/sharp_announces_108-inch_lcd_tv_blu-ray_player_and_more';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Sharp Announces 108-inch LCD TV, Blu-ray Player, and More'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2007/01/sharp_announces_108-inch_lcd_tv_blu-ray_player_and_more.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Sharp Announces 108-inch LCD TV, Blu-ray Player, and More</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>January  8, 2007</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Products & Equipment">Products & Equipment</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2007/01/sharp_announces_108-inch_lcd_tv_blu-ray_player_and_more.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2007/01/sharp_announces_108-inch_lcd_tv_blu-ray_player_and_more.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2007/01/sharp_announces_108-inch_lcd_tv_blu-ray_player_and_more.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2007/01/sharp_announces_108-inch_lcd_tv_blu-ray_player_and_more.php&amp;phase=2&amp;title=Sharp%20Announces%20108-inch%20LCD%20TV%2C%20Blu-ray%20Player%2C%20and%20More&amp;bodytext=Sharp%20is%20once%20again%20raising%20the%20bar%20for%20LCD%20technology%20at%202007%20CES%20with%20one-of-a-kind%20innovations%20and%20increased%20performance.%20The%20world%27s%20largest%20LCD%20TV%2C%20the%20108-inch%2C%20is%20headlining%20the%20company%27s%20wide%20breadth%20of%20TV%20offerings%2C%20which%20also%20includes%20the%20newest%20lines%20of%20large-screen%20AQUOS%20HDTVs%20to%20come%20from%20the%20company%27s%208th%20generation%20factory%20in%20Kameyama%2C%20Japan%2C%20featuring%20enhanced%20contrast%20ratios%2C%20response%20times%20that%20are%20among%20the%20fastest%20in%20the%20industry%2C%20and%20more%20inputs%20than%20ever%20before.%20In%20addition%2C%20Sharp%20is%20showcasing...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p class="prtitle">Sharp Showcases High-Definition Display Dominance at CES with World's Largest LCD TV and Groundbreaking New Technologies</p>

<p><I><CENTER>New 8th Generation AQUOS&reg; HD LCD TVs and Unbelievable 108-Inch Model Plus New DLP Front Projectors Demonstrate Sharp's Display Technology Prowess</CENTER></I></p>

<p><b>LAS VEGAS--(BUSINESS WIRE)</b>--Sharp is once again raising the bar for LCD technology at 2007 CES with one-of-a-kind innovations and increased performance. The world's largest LCD TV, the 108-inch, is headlining the company's wide breadth of TV offerings, which also includes the newest lines of large-screen AQUOS HDTVs to come from the company's 8th generation factory in Kameyama, Japan, featuring enhanced contrast ratios, response times that are among the fastest in the industry, and more inputs than ever before. In addition, Sharp is showcasing groundbreaking technologies and applications that reinforce the company's dominance in the market. Sharp also further expands its large-screen offerings with new additions to its award-winning DLP&reg; front projection lineup.</p>

<p>"Sharp makes one-of-a-kind products that change the way we live," said Bob Scaglione, senior vice president and group manager, Product and Marketing Group, Sharp Electronics Corporation. "LCD is the best flat-panel technology, and we are focused on bringing LCD innovations to the next level at 2007 CES, not only with new AQUOS products that further raise the bar for LCD TV performance, but also with groundbreaking concepts that will change our consumers' lifestyles."</p>

<p>Sharp is a worldwide leader in flat-panel LCD TV and has even won an Emmy&reg; award for the technology behind the AQUOS line*. The company has unsurpassed LCD screen manufacturing at its new state-of-the-art 8th generation factory in Kameyama, Japan, where an entire production line focuses solely on the creation of large-screen units, ensuring that Sharp maintains their top position in the marketplace and exhibiting the company's commitment to achieving even larger screen sizes. This manufacturing superiority helps Sharp offer the most comprehensive flat-panel LCD TV selection, with more than 50 models in screen sizes ranging from 13- to 108-inches.</p>

<p>In addition to LCD TV, Sharp is further broadening its display products line with the debut of new high-definition front projectors that feature the award-winning Texas Instruments DLP&trade; technology. "Sharp's display expertise extends beyond the flat-panel TV category, and as a result we're pleased to be launching a number of new products in our line of DLP front projectors here at CES," Scaglione continued. "In addition to a new 1080p flagship product, we also have a number of smaller, portable projectors, for a full range of offerings, so that we can open up the world of projection to many more consumers. Sharp is completely committed to maintaining a leadership position in this segment, as well."</p>

<p>Cementing the company's future presence in tech-savvy households, Sharp is also demonstrating sophisticated, breakthrough technologies that capture the near- and long-term potential of LCD in home, mobile and broadcast environments. Featured technologies at CES include a preview of wireless image-beaming from cameras and cell phones to TVs, as well as the first productizing of modems that transfer two high-definition feeds through one power line in the home. In addition, Sharp will be demonstrating how consumers will be able to access PC content on their TV, bringing the Internet right into the living room. For the broadcast professional, a new LCD monitor with a 1,000,000:1 contrast ratio for a crystal-clear picture will be demonstrated for use in demanding lighting conditions and picture-quality requirements, as well as a four thousand by two thousand resolution model, which doubles today's 1080p resolution. Finally, for mobile use in automobiles, Sharp is showcasing a three-way viewing angle display that lets three or more users watch three different programs that occupy the full screen of a single display simultaneously.</p>

<p>Additionally, Sharp is showcasing an iPod&reg; docking system and a series of TVs specifically intended for gamers. For detailed information on Sharp's new products, please see the individual product announcements and fact sheets.</p>

<p><br />
<h2>108-inch High-Definition LCD TV</h2><br />
Sharp has successfully developed a 108-inch LCD TV, the world's largest, and will exhibit a prototype model. This 108-inch Full HD 1080p LCD TV, which measures 93.9-inches (W) by 52.9-inches (H) in size, features Sharp's Advanced Super View LCD Panel manufactured at Sharp's Kameyama Plant No. 2, the first facility in the world to produce panels from eighth-generation glass substrates. The success of this development means that it is now possible to produce LCD TVs in all sizes, from 13-inches to the super-large-size class.</p>

<p><br />
<h2>AQUOS Widescreen 1080p HDTV Series (models LC-42D92U, LC-46D92U, LC-52D92U and LC-65D93U)</h2><br />
The new widescreen series of Full HD1080p HDTV AQUOS Liquid Crystal Televisions, available in 42-, 46-, 52-, and 65-inch screen sizes, features the top-of-the-line version of Sharp's proprietary Advanced Super View panel, the most advanced LCD panel in the world. This panel technology enables an incredible 15,000:1 Dynamic Contrast Ratio for deep blacks and crisp picture quality; Fine Motion Advanced technology for 120 Hz Frame Rate Conversion; enhanced Quick Shoot video circuitry for faster pixel response time of 4ms; and wide viewing angles of 176 degrees, so users can view the proprietary 5-wavelength backlight system that provides a wider color spectrum to achieve deeper, more vivid reds and greens than was previously possible. Additionally, all units in this series include three HDMI&trade; inputs, two HD component terminals, and one DVI-I input, all of which are compatible with 1080p signals from Blu-ray and other new devices, in addition to RS-232C for custom installations. The entire series features Full HD 1080p (1920 x 1080) resolution for an unparalleled high-definition experience. These newly redesigned models are available in a stunning piano black finish with detachable bottom speakers and include a detachable table stand. Also joining these new Full HD 1080p AQUOS LCD TVs is a 65-inch model that shares the same advanced features as the D92U line but offers a varied design of high-gloss piano black finish with fixed bottom-placed speakers. The LC-42D92U will be available in April for a Manufacturer's Suggested Retail Price (MSRP) of $3,499.99 and the LC-46D92U and LC-52D92U and will be available in January for MSRPs of $4,199.99 and $5,299.99, respectively. The LC-65D93U will be available in March for an MSRP of $10,999.99.</p>

<p><br />
<h2>AQUOS&reg; Widescreen 1080p HDTV Series (models LC-52D82U and LC-46D82U)</h2><br />
This new series of Full HD1080p HDTV AQUOS Liquid Crystal Televisions, available in 52- and 46-inch screen sizes, is produced at the new 8th-generation Kameyama plant for high picture quality and specifications. This panel features an incredible 10,000:1 Dynamic Contrast Ratio, for deep blacks and crisp picture quality; Fine Motion Advanced technology for 120 Hz Frame Rate Conversion; enhanced Quick Shoot video circuitry for faster pixel response time of 4ms; and wide viewing angles of 176 degrees, so users can view the television from virtually anywhere in the room. The D82 models also include Sharp's proprietary 4-wavelength backlight system that provides a wider color spectrum to achieve deeper, more vivid reds than was previously possible. Additionally, both models include three HDMI&trade; inputs as well as two HD component terminals, all of which are compatible with 1080p signals from Blu-ray and other new devices. These models feature Full HD 1080p (1920 x 1080) resolution for an unparalleled high-definition experience, and they are available in a piano black finish. Both models will be available in March; the LC-52D82U will have an MSRP of $4,799.99 and the LC-46D82U of $3,699.99.</p>

<p><br />
<h2>AQUOS Widescreen 720p HDTV Series (models LC-52D43U, LC-46D43U, LC-42D43U, LC-37D43U, LC-32D43U and LC-26D43U)</h2><br />
The new widescreen 52-, 46-, 42-, 37-, 32- and 26-inch HDTV AQUOS D43U Liquid Crystal Televisions further bolster Sharp's unmatched selection of sophisticated designs and its superior-performing LCD TVs. Sharp's proprietary Advanced Super View LCD panel enables a Dynamic Contrast Ratio of 6000:1, enhanced Quick Shoot video circuitry for fast pixel response time (6ms) and wide viewing angles (176 degrees), so users can view the television from almost anywhere in the room. The newly-designed series features an elegant piano black finish with fixed bottom speakers and a detachable table stand for wall-mounting flexibility. With 1366 x 768 resolution for true 16:9 aspect ratio, and built-in ATSC/QAM/NTSC tuners, consumers can enjoy the latest HDTV programming, and the addition of a PC input makes the panel multifunctional for any room. Models LC-37D43U and LC-32D43U are available now for MSRPs of $1,699.99 and $1,399.99, respectively. The LC-26D43U will be available in February for an MSRP of $1,099.99. The LC-42D43U will be available in May, pricing has not yet been determined. The LC-46D43U will have an MSRP of $2,699.99 and will be available in March. The LC-52D43U will be available in June and will have an MSRP of $3,999.99.</p>

<p><br />
<h2>AQUOS HDTV Game Players Series (models LC-37GP1U, LC-32GP1U)</h2><br />
Sharp has introduced a new Full HD 1080p AQUOS series crafted specifically for video game enthusiasts, the GP1U series, available in 32- and 37-inch screen sizes. These new models include special features that enhance the game-playing experience, including a "game mode" which optimizes the picture quality for game-playing, and a custom-designed remote control that allows the user to quickly "jump" into the game mode, and access the side-placed terminals for easy connections to video games. The game mode provides a newly developed "Vyper Drive" feature, which reduces lag time between the game console and the TV to be virtually imperceptible. The 32-inch panel boasts an incredible 10,000:1 Dynamic Contrast Ratio, and the 37-inch an 8500:1, for deep blacks and crisp picture quality; enhanced Fine Motion video circuitry for faster pixel response time of 6ms; and wide viewing angles of 176 degrees, so users can view the television from virtually anywhere in the room. They also include Sharp's proprietary 4-wavelength backlight system that provides a wider color spectrum to achieve deeper, more vivid reds than was previously possible. Additionally, both of the GP1U models include three HDMI&trade; inputs (with one on the side) as well as two HD component terminals (one on the side), all of which are compatible with 1080p signals from the latest video game devices. Both units in the series feature Full HD 1080p (1920 x 1080) resolution and 10Wx2 audio for an unparalleled high-definition viewing and listening experience. These models are available in a piano black finish with detachable bottom speakers and include a detachable table stand. The LC-32GP1U and LC-37GP1U will be available in March for MSRPs of $1,699.99 and $1,999.99 respectively.</p>

<p><br />
<h2>Flagship "Full HD" 1080p DLP&reg; Home Theater Front Projector (model XV-Z20000):</h2><br />
Sharp brings home theater to the forefront with the XV-Z20000, utilizing the latest 0.95"single DMD from Texas Instruments. This high-gloss black, groundbreaking "Full HD"1080p projector has a native resolution of 1920 x 1080 for a true 16:9 widescreen movie viewing experience, producing stunning vivid images. The XV-Z20000 transforms any room into a high-tech home theater, using Sharp's CV-IC III Video Scaling Circuitry that up-converts all signals to 1080p. The Z20000 also boasts a native 12000:1 contrast ratio and a brightness spec of 1000 ANSI lumens, delivering one of the best pictures available in consumer home theater projectors today. DVI/HDCP (High Bandwidth Digital Content Protection) and two HDMI&trade; terminals ensure a secure digital connection with all high definition set top boxes. The XV-Z20000 is available now for an MSRP of $11,999.99.</p>

<p><br />
<h2>High Definition DLP&reg; Home Theater Front Projector (model DT-510):</h2><br />
The DT-510 high-definition DLP&reg; front projector is feature-packed, with a stylish gloss-white design that is ideal for a dedicated home theater or any viewing room in the home. Weighing just 8.8 pounds, this portable unit can be moved easily from room to room, for an instant home theater anywhere. Utilizing the DLP&reg; technology from Texas Instruments, and with a resolution of 1280 x 720, the DT-510 produces a 4000:1 contrast ratio and a brightness rating of 1000 ANSI lumens, delivering one of the best pictures available in consumer home theater today. A powered optical iris system instantly changes brightness and contrast settings with the push of a button to allow the greatest flexibility for varying home theater environments. Home theater convenience is further enhanced with easy installation and whisper-quiet operation. A 6 Segment 5 X Speed color wheel achieves flicker-free, high-grade images and accurate color reproduction, resulting in an uninterrupted, detailed picture. Other features include I/P conversion, 3-2 pull down, Color Management System (C.M.S.), 3-step Bright Boost and an HDMI&trade; interface. The DT-510 will be available in February for an MSRP of $2,499.99.</p>

<p><br />
<h2>High Definition DLP&reg; Home Theater Front Projector (model XV-Z3100):</h2><br />
Sharp's next-generation portable DLP&reg; front projector, the SharpVision XV-Z3100, is a 720p high-definition home entertainment solution that instantly transforms any room into a high-tech home theater. This high-gloss black, widescreen portable projector can be carried throughout the house or to a friend's home to create an instant home theater for watching TV, viewing DVDs or playing computer games on a big screen. The XV-Z3100 features brightness (1000 ANSI Lumens) and a contrast level of 6500:1, superior to those available in current front projectors, so consumers can enjoy excellent picture quality in almost any lighting condition. The low fan noise of 29dB (in economy mode) ensures that the viewer won't miss a minute of the film's dialogue and special effects. Other features include I/P conversion, 3-2 pull down, Color Management System (C.M.S.), 3-step Bright Boost, a 12-volt trigger and an HDMI&trade; interface. The XV-Z3100 will be available in February for an MSRP of $2,699.99.</p>

<p><br />
<h2>Blu-Ray:</h2><br />
Sharp is demonstrating Blu-ray capabilities with a new product that will enter the market in the second quarter of spring 2007. This new Blu-ray player will help consumers realize the powerful next generation of home entertainment enabled by the high-definition Blu-ray format. The user-friendly player features a built-in HDMI&trade; digital AV interface that can be connected to an AQUOS LCD HDTV, allowing consumers to watch a Blu-ray disc in complete digital high-definition with high-quality picture and audio. The player will have an MSRP of $1,199.99.</p>

<p><br />
<h2>i-Elegance Music Systems for iPod&reg;:</h2><br />
Sharp's new i-Elegance Docking stereo systems allow the user to charge and play music directly from any iPod. The lightweight units feature full range bass reflex speakers with built-in side firing subwoofers that illuminate. With a sleek design and rounded edges, the new stereo systems will be available in white (DK-A1 and DK-A10) and black (DK-A1BK and DK-A10BK) and come with a thin-style remote control. Sleek and compact, these Sharp systems (compatible with iPod) fit into small spaces, such as a shelf or side table, and can be carried from room to room. An AM/FM tuner provides radio playback for both models, and in addition, the DK-A10 and DK-A10BK have a front-loading CD slot and are capable of playing MP3 and WMA files from a CD-R/RW disc. Both models also feature Alarm Clock and Sleep Timer functions. The DK-A1 and DK-A1BK will be available in May for an MSRP of $229.99. The DK-A10 and DK-A10BK will be available in April for an MSRP of $329.99.</p>

<p><br />
<h2>Micro Audio Systems (model XL-UH270 and XL-UH250):</h2><br />
The XL-UH270 and XL-UH250 are new micro systems that incorporate digital audio playback into the system from an MP3 player via a USB connection. Consumers can take the music from a personal, portable MP3 player and play it for guests through the Micro System. In addition to MP3 playback, the XL-UH270 is one of Sharp's first systems to offer XM-Ready&reg; service so users can play XM Satellite Radio in any room of the home with activation and monthly subscription. The XL-UH270 features a powerful 230-watt amplifier and the XL-UH250 features a 190-watt amplifier; both units include a 5-tray CD Changer with Play Exchange for continuous CD playback, an AM/FM tuner, and a multicolor fluorescent display, and both support CD, CD-R/RW, MP3, & WMA playback capabilities. The XL-UH270 will be available in March 2007 for an MSRP of $159.99. The XL-UH250 will be available in April for an MSRP of $139.99.</p>

<p>For more information on Sharp's full line of products, contact Sharp Electronics Corporation, Sharp Plaza, Mahwah, N.J. 07430, or call 800-BE-SHARP. For online product information, visit Sharp's virtual press room at sharppressroom.com or sharpusa.com.</p>

<p>Sharp Electronics Corporation is the U.S. subsidiary of Japan's Sharp Corporation, a worldwide developer of one-of-a-kind home entertainment products, appliances, networked multifunctional office solutions, solar energy solutions and mobile communication and information tools. Leading brands include AQUOS&reg; Liquid Crystal Televisions, 1-Bit&trade; digital audio products, SharpVision&reg; projection products, Insight&trade; Microwave Drawers, IMAGER&trade; digital multifunctional systems, and Notevision&reg; multimedia projectors. For more information visit Sharp Electronics Corporation at www.sharpusa.com</p>

<p>*Sharp won a 2004 Technology & Engineering Emmy&reg; for Award for Development of Direct View Liquid Crystal Display Screens. Use of the trademarks and service marks of the National Television Academy, including the mark Emmy&reg;, requires the prior express written permission of the National Television Academy.</p>

<p>AQUOS is a registered trademark of Sharp Corporation</p>

<p>HDMI, the HDMI logo and High Definition Multimedia Interface are trademarks of HDMI Licensing, LLC.</p>

<p>DLP is a trademark of Texas Instruments.</p>

<p>iPod is a trademark of Apple.</p>

<p>XM-Ready is a registered trademark of XM Satellite Radio Inc.</p>

<p>All other trademarks are the property of their respective owners.<br />
Contacts</p>

<p>Stanton Crenshaw Communications<br />
Robin Feldman, 646-502-3504<br />
rfeldman@stantoncrenshaw.com</p>

<p>Sharp Electronics Corporation<br />
Chris Loncto, 201-529-8680<br />
lonctoc@sharpsec.com</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>January  8, 2007 06:53 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 511
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
 				AND entry_id <> 511
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2007/01/sharp_announces_108-inch_lcd_tv_blu-ray_player_and_more.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
