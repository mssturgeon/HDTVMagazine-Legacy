<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 363";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Rodolfo La Maestra'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 363 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category[placement_category_id];
	
	# Still need this to track page views. Used in the Links unit in the footer
	$google_links_channel = $author[channel];

	# Set variables based on entry type. NOTE: Podcasts has its own entry template, so it is not included amongst the choices below.
	switch (1) {
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
	<meta name="keywords" content="dual link, silicon image, single link, pin connector, second link, dvi, DVI, link, digital, hdmi, HDMI, standard, pin, MHz, mhz, cable, HDCP, hdcp, dual, video, single, signal, connections, Mbps, signals" />
	<meta name="description" content="The DVI (Digital Visual Interface) 1.0 specification was introduced in April 1999 by the Digital Display Working Group integrated by Silicon Image, Intel, Compaq, Fujitsu, Hewlett-Packard, IBM and NEC for the purpose of creating an digital connection interface between a PC and a display device.  It is a connection with enough bandwidth for uncompressed HD signals.

IEEE1394 is a digital interface conceived by Apple Computer in 1986, and it was called &quot;Fire Wire&quot; for its fast speed of operation.  In 1995, the Institute of Electrical and Electronic Engineers (IEEE) adopted the serial bus as its standard 1394.  Sony trademarked their name iLink for their implementation of the 1394 bus as a 4-pin connector.

On December 9, 2002, the seven founders of HDMI (High-Definition Multimedia Interface) announced the 1.0 specification of this connectivity standard, the enhanced, more robust form of DVI.  The seven founders are Hitachi, Matsushita, Philips, Silicon Image, Sony, Thomson, and Toshiba." />
	<title>HDTV Magazine Articles - Digital Connectivity - A Tutorial</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/digital_connectivity_-_a_tutorial';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Digital Connectivity - A Tutorial'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/articles/2006/04/digital_connectivity_-_a_tutorial.php";
		if ($author[img] != '' && 1 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Rodolfo La Maestra" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Digital Connectivity - A Tutorial</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Rodolfo La Maestra</b><br />
				<?=$author_title?>
				Posted on <b>April 25, 2006</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Technology">Technology</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2006/04/digital_connectivity_-_a_tutorial.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/articles/2006/04/digital_connectivity_-_a_tutorial.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/articles/2006/04/digital_connectivity_-_a_tutorial.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2006/04/digital_connectivity_-_a_tutorial.php&amp;phase=2&amp;title=Digital%20Connectivity%20-%20A%20Tutorial&amp;bodytext=The%20DVI%20%28Digital%20Visual%20Interface%29%201.0%20specification%20was%20introduced%20in%20April%201999%20by%20the%20Digital%20Display%20Working%20Group%20integrated%20by%20Silicon%20Image%2C%20Intel%2C%20Compaq%2C%20Fujitsu%2C%20Hewlett-Packard%2C%20IBM%20and%20NEC%20for%20the%20purpose%20of%20creating%20an%20digital%20connection%20interface%20between%20a%20PC%20and%20a%20display%20device.%20%20It%20is%20a%20connection%20with%20enough%20bandwidth%20for%20uncompressed%20HD%20signals.%0A%0AIEEE1394%20is%20a%20digital%20interface%20conceived%20by%20Apple%20Computer%20in%201986%2C%20and%20it%20was%20called%20%22Fire%20Wire%22%20for%20its%20fast%20speed%20of%20operation.%20%20In%201995%2C%20the%20Institute%20of%20Electrical%20and%20Electronic%20Engineers%20%28IEEE%29%20adopted%20the%20serial%20bus%20as%20its%20standard%201394.%20%20Sony%20trademarked%20their%20name%20iLink%20for%20their%20implementation%20of%20the%201394%20bus%20as%20a%204-pin%20connector.%0A%0AOn%20December%209%2C%202002%2C%20the%20seven%20founders%20of%20HDMI%20%28High-Definition%20Multimedia%20Interface%29%20announced%20the%201.0%20specification%20of%20this%20connectivity%20standard%2C%20the%20enhanced%2C%20more%20robust%20form%20of%20DVI.%20%20The%20seven%20founders%20are%20Hitachi%2C%20Matsushita%2C%20Philips%2C%20Silicon%20Image%2C%20Sony%2C%20Thomson%2C%20and%20Toshiba.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<blockquote>This is an excerpt from the <b>HDTV Technology Review 2006 Report</b> by Rodolfo La Maestra. If you are interested in the full version of this report, it is currently available from the <a href="/reports/hdtv-technology-review.php">HDTV Technology Review</a> page.<br>
<br>
The other parts in the series are:<br>
Part 1: <a href="/articles/2006/03/hdtv_technology_review_part_1_introduction.php">HDTV Technology Review, Part 1: Introduction</a><br>
Part 2: <a href="/articles/2006/04/1080p_into_hdtv_displays.php">1080p into HDTV Displays</a><br>

<p>This tutorial article was drafted when DVI was starting to be implemented in HDTVs and first appeared on my 2003 HDTV report.  Since then, it is customarily included in a separate section within each yearly report to provide the basic background about digital connections used in HDTV equipment.</p>

<p>Additionally, on each annual report I use other sections to provide an update of how these connections are being implemented on audio and video equipment year after year, what type of problems they have, what functionally they facilitate, what is recommended regarding technical requirements when looking for a product (such as having HDCP compliance, a big issue in the 2003/4 reports), etc.</p>

<p>In the 2006 report, HDMI is mentioned throughout the report, additionally there is a separate large section that covers the HDMI chips, the HDMI implementation, the trends of manufacturer's adoption, the specifications of the released versions, the issues surrounding incompatibility of HDMI suited products, the issues surrounding the new multi-channel audio hi-bit transported with HDMI, the implementation of content protection over the HDMI connection, etc.</p>

<p>Each yearly report adds a new layer of the year regarding HDMI, as issues, as upgrades, as implementation trends, organizations involved with, manufacturers using it for 1080p sets and blu-laser players, etc. </p>

<p>In other words, this except is just a section to get the reader familiarized with the basics of digital connectivity.  For the complete picture, including wireless digital connectivity, please consult the annual reports.<br />
</blockquote></p>

<p><br />
<h2>DVI</h2><br />
The DVI (Digital Visual Interface) 1.0 specification was introduced in April 1999 by the Digital Display Working Group integrated by Silicon Image, Intel, Compaq, Fujitsu, Hewlett-Packard, IBM and NEC for the purpose of creating an digital connection interface between a PC and a display device.  It is a connection with enough bandwidth for uncompressed HD signals.</p>

<p>The 1.0 DVI specification is a point-to-point solution that supports video content but not audio.  DVI uses the Transition-Minimized Differential Signaling (TMDS) protocol developed by Silicon Image.  PanelLink is the Silicon Image's proprietary implementation of TMDS.</p>

<p>The HDCP (High-bandwidth Digital Content Protection) 1.0 specification was developed by Intel with contributions from Silicon Image in February 2000 to protect DVI outputs from being copied by providing a secure link between a video source and a display device.</p>

<p>HDCP offers authentication, encryption, and renewability.  The Motion Picture Association of America (MPAA) endorsed HDCP as the standard for the secure transmission of HD signals over DVI.</p>

<p>Most new DTV monitors and integrated displays have incorporated DVI or HDMI inputs, although on their first generation some panels were not HDCP compliant, now there is a large volume of H/DTV equipment that is.  However, some displays were reported to have interoperability problems regarding DVI/HDCP or HDMI/HDCP.</p>

<p>The DVI standard is able to handle single or dual link connections.  A single-link connection supports up to UXGA resolution of 1600 x 1200 at 60 Hz.  Dual-link connections provide bandwidth for resolutions beyond QXGA (2048 x 1536).</p>

<p>According to DVI specs a single link has 165 MHz/pixels capacity for 3 channels, Red, Green and Blue, each channel could support up to 1.65 Gbps speed rate, or a total of 4.95 Gbps for the 3 channels (165 MHz x 30 bits x sec).  Dual-link connections double that capacity to 330 MHz, with a speed-rate capacity up to 9.9 Gbps.</p>

<p>The 1080i HD format has 1125 total lines of 2200 pixels x frame (active image 1080x1920), requiring 74.25 MHz/pixels (1125 x 2200 x 30fps).  Each pixel contains data for RGB and is implemented by DVI with 30 bits (8 per each color plus another 6 for encoding).  An HD 74.25 MHz/pixel signal would require 2.2 Gbps speed rate.</p>

<p>A link of 3 channels supporting 165 MHz is sufficient for the 74.25 MHz HD 1080i signal without requiring the use of the second link, and will also be sufficient to transport a 1080p/60 frames x second signal at 148.5 MHz without requiring the second link.</p>

<p>If the signal to be transmitted would be higher than the single link capacity of 165 MHz, it would require the use of a dual DVI link connection, each link will carry half of the signal; the second link cannot be used with just what is exceeding 165 MHz of the first link.  For example, a 200 MHz signal would be carried with both links operating at 100 MHz each.</p>

<p>HDMI uses the same 165MHz capacity per link; dual-link uses the B connector with the second link pins.</p>

<p>DVI identifies and auto-configures the connected device.  If source equipment is connected with DVI single link to a display configured as dual link DVI, the image will experience a lower resolution.  Some first generation single link DVI cables use dual link connectors.  DVI standard cables have typically a five-meter distance limitation, although with better quality wiring, such as fiber-optic, higher distances are possible.</p>

<p>There are three types of DVI connectors:</p>

<p><u>DVI-I (integrated)</u>, carries a single or dual-link digital signal, with an additional analog signal for legacy devices.  The 29-pin DVI connector uses 24 pins for the digital data stream (12 for each link) and 5 pins (1 plus-shaped blade and 4 pins) to carry analog video and ground.</p>

<table><tr><td style="text-align:center">
<img src="/images/articles/HDTVTR2006/image352.gif" alt="DVI-I"><br>DVI-I
</td></tr></table>

<p><u>DVI-D (digital)</u> carries digital-only video data to a display.  It is designed for 12 or 24 pin connections, and single/dual link operation (notice the lack of 4 pins, 2 above/2 below the flat blade).</p>

<table><tr><td style="text-align:center">
<img src="/images/articles/HDTVTR2006/image354.gif" als="DVI-D"><br>DVI-D
</td></tr></table>

<p><u>DVI-A (analog)</u> is available for legacy analog applications to carry analog signals to a CRT monitor or an analog HDTV (claims to be better than VGA).  The three rows of eight pins have three pins missing in the first row, five missing in the second row and four missing in the third row, and that the "flat blade" contact seen to the left has two contacts above and below it.  There is no single or dual link in analog cables.</p>

<table><tr><td style="text-align:center">
<img src="/images/articles/HDTVTR2006/image356.jpg" alt="DVI-A"><br>DVI-A
</td></tr></table>

<p>Regarding connecting plugs to receptacles:</p>

<p>A DVI-D plug can be connected to either DVI-D or DVI-I receptacles,<br />
A DVI-A plug can be connected to either DVI-I/A or VGA (w/adapter) receptacles,<br />
A DVI-A receptacle would accept DVI-I but not DVI-D.<br />
A DVI-I plug can be connected to either DVI-I or DVI-A receptacles (the 'A' ignores 'I's digital pins)</p>

<p><br />
<h2>IEEE1394</h2><br />
IEEE1394 is a digital interface conceived by Apple Computer in 1986, and it was called "Fire Wire" for its fast speed of operation.  In 1995, the Institute of Electrical and Electronic Engineers (IEEE) adopted the serial bus as its standard 1394.  Sony trademarked their name iLink for their implementation of the 1394 bus as a 4-pin connector.</p>

<p>In March 2000, an updated specification was approved, the 1394a.  The "a" standard supports speeds of 100Mbps, 200Mbps, and 400Mbps over a distance of 4.5 meters, and up to 63 peer-to-peer nodes/devices.</p>

<p>In 2001, the IEEE 1394 "b" standard emerged as a network technology (rather than as serial bus); it is capable of moving data streams at faster speeds over longer distances than the original.</p>

<p>The "b" standard specifications were intended to support up to 3,200 Mbps depending on the cable material, and permit the use of cabling materials not supported by the "a" standard.  It supports speeds up to 100Mbps over 100 meters of Category 5 wiring, 400 Mbps over 100 meters of plastic optical fiber, and up to 3,200 Mbps (or 3.2 Gbps) over 100 meters of glass optical fiber.</p>

<p>The "b" standard is compatible with the "a" standard; if an "a" device were plugged into a "b" component, the bus would deliver a maximum speed limited by the "a" standard (400Mbps).  Each "b" device can be set up to 100 meters apart from the next in sequence, allowing the total network to be quite significant in cable length.</p>

<p>The licensing fee for the use of the patented technology is $ 0.25 per system; chipsets are less than $5 each in volume.</p>

<p>It supports hot swapping and plug-and-play, so a consumer's 1394 bus can recognize automatically a 1394 device when it is connected/disconnected, and reconfigure itself.</p>

<p>The connection is now being used by a growing number of DTV equipment manufacturers for the transmission of compressed HD signals, such as D-VHS recording and networking DTV equipment.</p>

<p>There are three types of cables used for 1394.  The 6-conductor type has two separately shielded twisted pairs for data and two power wires in an overall shielded cable with 6-pin connectors on either side.  The 4-wire cable uses two separately shielded data cables without power wires in an overall shielded cable with 4-pin connectors on either end.  The third type of cable uses either type of actual cable, with a 6-pin connector on one side, and a 4-pin connector on the other side of the cable.</p>

<p>The 4-pin connector is more common on digital video camcorders and other small external devices because of it's small size, while the 6-pin connector is more common on PC's, external hard drives due to it's durability and support for external power for 1394 peripherals.</p>

<table width="100%"><tr><td style="text-align:center">
<img src="/images/articles/HDTVTR2006/image358.gif" alt="IEEE female connectors"><br>6-pin female connector above left<br>4-pin female connector above right
</td><td style="text-align:center">
<img src="/images/articles/HDTVTR2006/image360.jpg" alt="IEEE 6-pin male"><br>The 6-pin male<br>connector
</td><td style="text-align:center">
<img src="/images/articles/HDTVTR2006/image362.jpg" alt="IEEE 4-pin male"><br>4-pin male<br>connector
</td></tr></table>

<p>HD signals are broadcast in compressed MPEG-2 format at approximately 19 Mbps.  D-VHS VCRs are able record compressed HD signals and require a 1394 connection to receive the digital data stream.  HDTV monitors require a MPEG-2 decoder to decompress the signal for display, as oppose to DVI that is uncompressed.</p>

<p>DTCP (Digital Transmission Content Protection) has been created for the purpose of copy protection over the 1394 connection.  DTCP is also known as 5c for the five companies that participated on the standard (Sony, Toshiba, Intel, Hitachi, and Matsushita).</p>

<p>During the last two to three years, there have been many discussions (and hype) about using these types of digital connections (DVI and 1394) for DTV equipment, rather than only the analog connections (component YPbPr, RGB, RGBHV, etc), for protecting HD digital content.</p>

<p>Since 2003, most manufacturers released a large variety of products adopting these two connections to enable their equipment for digital connectivity, IEEE1394 for compressed HD video from integrated TVs with tuners, cable and OTA HD-STBs mainly for recording purposes, and DVI for uncompressed HD video for the viewing of protected content (using HDCP).</p>

<p>HDMI is quickly replacing DVI and is being implemented already on many products, and is becoming the de-facto standard for transporting uncompressed signals over a cable.</p>

<p><br />
<h2>HDMI</h2><br />
On December 9, 2002, the seven founders of HDMI (High-Definition Multimedia Interface) announced the 1.0 specification of this connectivity standard, the enhanced, more robust form of DVI.  The seven founders are Hitachi, Matsushita, Philips, Silicon Image, Sony, Thomson, and Toshiba.</p>

<p>The standard supports HD uncompressed video, 8-channel digital audio (reportedly up to 192 KHz), and some control signals on a single cable (15 mm, 19 pin), while using less than half the available bandwidth.  HDMI has the same video capacity as DVI, or up to five Gbps of bandwidth, double what a HD signal would require, and is backward compatible with DVI by using an adapter.</p>

<p>Not included in the standard but used with DVI and HDMI is the HDCP (High-bandwidth Digital Content Protection) protocol.  HDCP is licensed by Intel, designed to protect HDMI and DVI signals from piracy, and used for authentication between A/V products.  In 2003, a license fee of five cents was applied to each product (four cents for HDMI, 1 cent for HDCP), that manufacturers had to pay to the HDMI founders and Intel.</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Rodolfo La Maestra</b>, <b>April 25, 2006 07:12 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 363
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
			
 		<?if (1 <> 7) {
 			# Recent Articles by Author (exclude this one)
 			# Do not show recent articles for Bulletins.
 			$qry = "
 			SELECT entry_id, entry_blog_id, entry_created_on, entry_title, author_id, author_name
 			FROM mt_entry e, mt_author a
 			WHERE entry_blog_id = 1
 				AND entry_id <> 363
 				AND entry_status = 2
 				AND entry_author_id = a.author_id
 				AND a.author_name = 'Rodolfo La Maestra'
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
 				<h2>About Rodolfo La Maestra</h2>
 				<?=stripslashes($author[bio_short])?>
 			<span class="corners-bottom"><span></span></span></div>
 		<?}?>
		</td><td id="right">

 		<?if ($about != '') {?>
 			<div class="item"><span class="corners-top"><span></span></span>
 				<h2>About Articles</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2006/04/digital_connectivity_-_a_tutorial.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
