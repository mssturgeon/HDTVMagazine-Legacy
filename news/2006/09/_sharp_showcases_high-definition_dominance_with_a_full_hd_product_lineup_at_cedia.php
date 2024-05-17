<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 447";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 447 AND placement_is_primary = 1";
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
	<meta name="keywords" content="home theater, high definition, front projector, contrast ratio, currently available, sharp, Sharp, home, high, AQUOS, aquos, available, theater, models, projector, definition, screen, system, inch, room, model, front, lcd, dlp, LCD" />
	<meta name="description" content="&lt;img src=&quot;/images/bulletins/sharp-aquos-lc-52d62u.jpg&quot; alt=&quot;Sharp AQUOS LC-52D62U&quot; align=&quot;left&quot;&gt;Sharp is rounding out the company's line of &quot;full HD&quot; home entertainment products at CEDIA 2006 with the unveiling of new AQUOS(R) 1080p LCD TVs and its first 1080p DLP(TM) front projector, a new flagship product. Sharp's &quot;full HD&quot; product lineup is highlighted by the first AQUOS models to come out of the company's brand-new Generation 8 factory, Kameyama No. 2. These 46- and 52-inch large-screen AQUOS HDTVs, together with a new 42-inch model, mark the introduction of three new screen sizes to the brand, all of which feature..." />
	<title>HDTV Magazine Bulletins -  Sharp Showcases High-Definition Dominance with a ''Full HD'' Product Lineup at CEDIA</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/_sharp_showcases_high-definition_dominance_with_a_full_hd_product_lineup_at_cedia';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes(' Sharp Showcases High-Definition Dominance with a \'\'Full HD\'\' Product Lineup at CEDIA'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2006/09/_sharp_showcases_high-definition_dominance_with_a_full_hd_product_lineup_at_cedia.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2"> Sharp Showcases High-Definition Dominance with a ''Full HD'' Product Lineup at CEDIA</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>September 14, 2006</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Products & Equipment">Products & Equipment</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2006/09/_sharp_showcases_high-definition_dominance_with_a_full_hd_product_lineup_at_cedia.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2006/09/_sharp_showcases_high-definition_dominance_with_a_full_hd_product_lineup_at_cedia.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2006/09/_sharp_showcases_high-definition_dominance_with_a_full_hd_product_lineup_at_cedia.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2006/09/_sharp_showcases_high-definition_dominance_with_a_full_hd_product_lineup_at_cedia.php&amp;phase=2&amp;title=%20Sharp%20Showcases%20High-Definition%20Dominance%20with%20a%20%27%27Full%20HD%27%27%20Product%20Lineup%20at%20CEDIA&amp;bodytext=%3Cimg%20src%3D%22%2Fimages%2Fbulletins%2Fsharp-aquos-lc-52d62u.jpg%22%20alt%3D%22Sharp%20AQUOS%20LC-52D62U%22%20align%3D%22left%22%3ESharp%20is%20rounding%20out%20the%20company%27s%20line%20of%20%22full%20HD%22%20home%20entertainment%20products%20at%20CEDIA%202006%20with%20the%20unveiling%20of%20new%20AQUOS%28R%29%201080p%20LCD%20TVs%20and%20its%20first%201080p%20DLP%28TM%29%20front%20projector%2C%20a%20new%20flagship%20product.%20Sharp%27s%20%22full%20HD%22%20product%20lineup%20is%20highlighted%20by%20the%20first%20AQUOS%20models%20to%20come%20out%20of%20the%20company%27s%20brand-new%20Generation%208%20factory%2C%20Kameyama%20No.%202.%20These%2046-%20and%2052-inch%20large-screen%20AQUOS%20HDTVs%2C%20together%20with%20a%20new%2042-inch%20model%2C%20mark%20the%20introduction%20of%20three%20new%20screen%20sizes%20to%20the%20brand%2C%20all%20of%20which%20feature...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p class="prtitle">Sharp Showcases High-Definition Dominance with a "Full HD" Product Lineup at CEDIA</p>

<p><b>DENVER--(BUSINESS WIRE) - Sept. 14, 2006</b> - New High-Definition AQUOS LCD TVs With Advanced Specifications and Competitive Pricing Plus a 1080p DLP Front Projector Headline Sharp's Elegant HD Home Entertainment Product Line 	</p>

<p><img src="/images/bulletins/sharp-aquos-lc-52d62u.jpg" alt="Sharp AQUOS LC-52D62U" align="left">Sharp is rounding out the company's line of "full HD" home entertainment products at CEDIA 2006 with the unveiling of new AQUOS(R) 1080p LCD TVs and its first 1080p DLP(TM) front projector, a new flagship product. Sharp's "full HD" product lineup is highlighted by the first AQUOS models to come out of the company's brand-new Generation 8 factory, Kameyama No. 2. These 46- and 52-inch large-screen AQUOS HDTVs, together with a new 42-inch model, mark the introduction of three new screen sizes to the brand, all of which feature 1080p resolution, dramatically enhanced contrast ratios and pixel response times that are among the fastest in the industry.</p>

<p>"High-definition is the future of entertainment, and we are proud to be bringing to market a complete line of 'full HD' display products that will enhance consumers' home entertainment experience," said Bob Scaglione, senior vice president and group manager, product and marketing Group, Sharp. "Sharp is committed to leading the high-definition products industry, and our CEDIA booth reflects that commitment."</p>

<p>With the addition of the AQUOS D62U line, Sharp offers full HD 1080p models in six screen sizes (37", 42", 46", 52", 57" and 65") - more than any other manufacturer. The company has unsurpassed LCD screen manufacturing capability, highlighted by its recently-opened state-of-the-art Generation 8 factory, Kameyama No. 2, which focuses solely on the creation of large-screen units. Kameyama No. 2 is the world's first and only 8th-generation LCD facility and will enable Sharp to build the most advanced flat-panel televisions in the world, and also help to meet the growing demand for competitively-priced large-screen high-definition LCD TVs in the U.S.</p>

<p>In addition to LCD TV, Sharp is further broadening its television line with an extensive line of high-definition front projectors that feature the award-winning Texas Instruments DLP technology. "Sharp's 'full HD' expertise extends beyond the flat-panel display category, and we are especially excited about the availability of our new 1080p flagship product, the XV-Z20000, which will change the front projection landscape," Scaglione continued.</p>

<p>Demonstrating the company's complete home theater prowess, Sharp's CEDIA 2006 booth will also feature a technology demonstration of the BD-MPC10, a multi-faceted Home Theater Studio System with Blu-Ray functionality and Time Domain Speaker Technology. The system consists of an AV receiver powered by Sharp's 1-Bit digital amplification operating at 11.2MHz sampling rate, a special pair of tower speakers with built-in Time Domain Speaker Technology, and a Blu-Ray high-definition player. Time Domain Speaker Technology produces a coherent and low distortion sound wave that results in true, crystal-clear sound. The BD-MPC10 also features a listener correction EQ system by Audyssey that adjusts sound for inconsistent room acoustics. Through an interface with the AQUOS Familink system, viewers can control the complete system with the push of a single button on an AQUOS TV's remote control.</p>

<p>For detailed information on Sharp's new products, please see the individual product announcements and fact sheets available for media.</p>

<p>AQUOS Widescreen 1080p Models</p>

<p>Sharp is expanding its wide range of AQUOS models with the introduction of three large-screen 1080p HDTV AQUOS Liquid Crystal Televisions, available in 42-, 46- and 52-inch screen sizes. The models produced at the new 8th-generation Kameyama plant (46- and 52-inch) will feature the latest version of Sharp's proprietary Advanced Super View panel for extraordinary LCD performance. This panel technology enables an incredible contrast ratio for deep blacks and crisp picture quality; enhanced Quick Shoot video circuitry for faster pixel response time; and wider viewing angles, so users can view the television from virtually anywhere in the room. The 46- and 52-inch models will also include Sharp's proprietary 4-wavelength backlight system that provides a wider color spectrum to achieve deeper, more vivid reds than was previously possible. Additionally, all units in this series include dual HDMI inputs, both of which are compatible with 1080p signals from Blu-ray devices. All three models will be available in October, the LC-42D62U for a Manufacturer's Suggested Retail Price (MSRP) of $2,499.99, the LC-46D62U for an MSRP of $3,499.99, and the LC-52D62U for an MSRP of $4,799.99.</p>

<p>Flagship DLP 1080p Home Theater Front Projector (model XV-Z20000):</p>

<p>Sharp brings home theater to the forefront with the new XV-Z20000, utilizing a single .95" DMD chip from Texas Instruments. This groundbreaking 1080p projector has a native resolution of "full HD" 1920 x 1080 for true 16:9 widescreen movie viewing, producing clear vivid images. The XV-Z20000 transforms any room into a high-tech home theater, using Sharp's CV-IC III Video Scaling Circuitry. DVI/HDCP (High Bandwidth Digital Content Protection) and two HDMI terminals ensure a secure digital connection with all high definition set top boxes. The XV-Z20000 will be available in October for an MSRP of $11,999.99.</p>

<p>AQUOS Widescreen HDTV Series (models LC-37D90U and LC-32D50U)</p>

<p>The D90U and D50U Widescreen Series of HDTV AQUOS Liquid Crystal Televisions, available in 37- and 32-inch screen sizes, feature Sharp's proprietary multi-pixel technology for extraordinary LCD performance. This technology enables contrast ratio of 1200:1, enhanced Quick Shoot video circuitry for 6 ms pixel response time and wide viewing angles (176 degrees), so users can view the television from virtually anywhere in the room. The 37-inch model features full 1080p (1920 x 1080) HDTV resolution, providing consumers with an unparalleled high-definition experience, and also includes Sharp's proprietary 4-wavelength backlight system that provides a wider color spectrum to achieve deeper, more vivid reds than previously possible. The LC-32D50U has stellar 1366 x 768 resolution for viewing HD programming. Additionally, both units in this series include dual HDMI, HD component, DVI-I for PC compatibility and the DTVLink advanced digital interface. These elegantly-styled models are available in a titanium finish with detachable bottom speakers (model LC-37D90U) or fixed bottom speakers (model LC-32D50U). The LC-37D90U and LC-32D50U are currently available for MSRPs of $2,999.99 and $1,799.99, respectively.</p>

<p>AQUOS D40U Series (models LC-37D40U, LC-32D40U and LC-26D40U)</p>

<p>Widescreen 37-, 32- and 26-inch HDTV AQUOS D40U Liquid Crystal Televisions further bolster Sharp's unmatched selection of sophisticated designs and its superior-performing LCD TVs. This series features a contrast ratio of 1200:1, enhanced Quick Shoot video circuitry for 6 ms pixel response time and wide viewing angles (176 degrees), so users can view the television from almost anywhere in the room. This series features an elegant piano black finish with fixed bottom speakers and a detachable table stand for wall-mounting flexibility. With 1366 x 768 resolution and built-in ATSC/QAM/NTSC tuners, consumers can enjoy the latest HDTV programming. Models LC-37D40U, LC-32D40U and LC-26D40U are currently available for MSRPs of $2,299.99, $1,599.99 and $1,099.99, respectively.</p>

<p>AQUOS LC-20D30U</p>

<p>This 20-inch AQUOS is the only model in Sharp's extensive LCD TV lineup to offer true 16:9 widescreen aspect ratio and 1366 x 768 resolution for 720p HDTV compatibility in a 20-inch screen size. Perfect for a smaller or secondary television-watching space, the widescreen format allows movies to be seen in the aspect ratio originally intended by the director. The LC-20D30U provides outstanding picture quality with 800:1 contrast ratio, and multiple placement options with 170-degree viewing angles. In addition, the LC-20D30U includes HD compatibility, HDMI and PC compatibility, and features a black matte finish with black side accents, bottom speakers and a removable table stand, for a variety of placement options. Model LC-20D30U is currently available for an MSRP of $899.99.</p>

<p>AQUOS S5U Series (models LC-20S5U and LC-15S5U)</p>

<p>These 15- and 20-inch 4:3 AQUOS models are Enhanced Definition LCD TVs that feature 480p compatibility for viewing progressive DVDs. These models have fixed bottom speakers and a silver finish with black side trim that complements any decor. The S5U series includes Optical Picture Control (OPC) for automatic brightness adjustment to accommodate a room's lighting conditions; NTSC, PAL and SECAM video playback capability; HD compatible input; and a high brightness level. These models are currently available for MSRPs of $749.99 and $549.99, respectively.</p>

<p>Home Theater DLP Front Projector (model XV-Z3000):</p>

<p>Sharp's portable DLP front projector, the SharpVision XV-Z3000, is a 720p high-definition home entertainment solution that instantly transforms any room into a high-tech home theater. This widescreen, portable projector can be carried throughout a home or to a friend's home to create an instant home theater for watching TV, viewing DVDs or playing computer games on a big screen. The XV-Z3000 features brightness (1200 ANSI Lumens) and contrast levels (6500:1) superior to those available in current front projectors, so consumers can enjoy excellent picture quality in almost any lighting conditions. Additionally, a dual-iris system adjusts image brightness to show full detail and enhances contrast ratio to compensate for varied lighting environments. The low fan noise of 30 dBA (in economy mode) ensures that the viewer won't miss a minute of the film's dialogue and special effects. Other features include I/P conversion, 3-2 pull down, Color Management System (C.M.S.), 3-step Bright Boost, a 12 volt trigger and an HDMI interface. The XV-Z3000 is currently available for an MSRP of $3,499.99.</p>

<p>Home Theater DLP Front Projector (model DT-500):</p>

<p>The DT-500 high-definition DLP front projector is a stylish, feature-packed projector that is ideal for a dedicated home theater or any viewing room. This portable unit can be moved easily from room to room, for an instant home theater anywhere. Utilizing DLP technology from Texas Instruments, and with a resolution of 1280 x 768, the DT-500 produces a 4000:1 contrast ratio and a brightness rating of 1200 ANSI lumens, delivering one of the best pictures available in consumer home theater today. Weighing just 8.6 pounds, consumers can carry the projector to any room of the house to watch TV, DVD movies or play computer games on a big screen and then store the entire system in a cabinet to save space. A powered optical iris system instantly changes brightness and contrast settings with the push of a button to allow the greatest flexibility for varying home theater environments. Home theater convenience is further enhanced with easy installation and whisper-quiet operation. A 6 Segment 5 X Speed color wheel achieves flicker-free, high-grade images and accurate color reproduction, resulting in an uninterrupted, detailed picture. Other features include I/P conversion, 3-2 pull down, Color Management System (C.M.S.), 3-step Bright Boost and an HDMI interface. The DT-500 is currently available for an MSRP of $3,299.99.</p>

<p>Portable DLP Front Projector (model DT-100):</p>

<p>Weighing just over eight and a half pounds, this portable DLP front projector can be moved easily from room to room, for an instant big-screen theater anywhere in the home. Using DLP technology from Texas Instruments, this stylish, feature-packed projector is ideal for consumers to watch TV, DVDs or play computer games on a full-size screen and then pack it all up and put it away, saving space and avoiding clutter. The DT-100 provides consumers with a compact, lightweight product that will easily fit on a shelf, cabinet or small side table. The projector is EDTV (enhanced definition television), with a resolution of 854 x 480 that is high-definition compatible. Upgraded features include an extremely high contrast ratio of 2500:1 as well as 1000 ANSI Lumen brightness for brilliant clarity and a superior image. The low fan noise of 30 dBA (in economy mode) ensures that a film's dialogue and special effects are the only sounds that movie-watching guests will hear. The projector is outfitted with a 6 Segment 5 X Speed color wheel that minimizes "color breaking" and provides high quality images with accurate color reproduction. The DT-100 is currently available for an MSRP of $1,299.99.</p>

<p>Widescreen Liquid Crystal Television/DVD Combos (models LC-26DV20U and LC-20DV20U):</p>

<p>The Widescreen LC-26DV20U and LC-20DV20U LCD TVs provide a slim, versatile, all-in-one television and video solution. Both units feature HDMI and HD component inputs for high-definition compatibility when connected to a separate set-top box. A built-in progressive-scan DVD Player loads discs into the TV from the side, keeping the sleek appearance of the unit and creating a complete home theater solution. The DV20U Series provides a high contrast ratio (800:1) and wide viewing angles (170 degrees). The 26-inch model is an HDTV and includes built-in NTSC/ATSC/QAM tuners. The 20-inch model is an HDTV Monitor offering HD compatibility and PC connectivity. These televisions are silver and feature bottom-placed speakers that will complement any decor. The LC-26DV20U and LC-20DV20U are currently available for MSRPs of $1,049.99 and $899.99 respectively.</p>

<p>For more information on Sharp's full line of Liquid Crystal Televisions, contact Sharp Electronics Corporation, Sharp Plaza, Mahwah, N.J. 07430, or call 800-BE-SHARP. For online product information, visit sharpusa.com.</p>

<p>Sharp Electronics Corporation is the Mahwah, N.J.-based marketing and sales subsidiary of Japan's Sharp Corporation, a worldwide developer of the core technologies that are integral to shaping the next generation of home entertainment products, appliances, networked, multifunctional office solutions, solar energy and mobile communication and information tools. Leading brands include AQUOS(R) Liquid Crystal Televisions, 1-Bit(TM) digital audio products, SharpVision(R) projection products, Carousel(R) microwaves, IMAGER(TM) digital multifunctional systems, and Notevision(R) multimedia projectors. Sharp Electronics Corporation employs approximately 2,000 people throughout the U.S. supporting more than 50 product lines.</p>

<p>*Sharp won a 2004 Technology & Engineering Emmy(R) for Award for Development of Direct View Liquid Crystal Display Screens. Use of the trademarks and service marks of the National Television Academy, including the mark Emmy(R), requires the prior express written permission of the National Television Academy.</p>

<p>**Cable system must deliver HDTV programming. Consumers should check with their local cable company to determine available HDTV channels.</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>September 14, 2006 07:24 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 447
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
 				AND entry_id <> 447
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2006/09/_sharp_showcases_high-definition_dominance_with_a_full_hd_product_lineup_at_cedia.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
