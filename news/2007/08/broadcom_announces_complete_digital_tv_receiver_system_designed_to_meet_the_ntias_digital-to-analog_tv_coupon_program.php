<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 673";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 673 AND placement_is_primary = 1";
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
	<meta name="keywords" content="digital analog, converter boxes, our products, digital television, our ability, broadcom, our, Broadcom, digital, analog, products, communications, bcm, BCM, converter, television, receiver, ntia, ATSC, NTIA, atsc, Digital, program, boxes, system" />
	<meta name="description" content="Broadcom Corporation (NASDAQ:BRCM) , a global leader in semiconductors for wired and wireless communications, today announced a complete digital television receiver system targeted at the National Telecommunications and Information Administration's (NTIA's) digital-to-analog converter box program. The program, which is part of a Federal Communications Commission (FCC) initiative, includes a budget of $1.5 billion that will be used to assist U.S. households in making an affordable transition from existing analog televisions to digital by providing coupons to households to defray the cost of digital TV converter boxes. Broadcom has introduced a turnkey digital television-on-chip (TVoC) and associated software to enable these digital-to-analog converter boxes, extending the lives of analog-only TVs.

As written in the Department of Commerce's Federal Register..." />
	<title>HDTV Magazine Bulletins - Broadcom Announces Complete Digital TV Receiver System Designed to Meet the NTIA's Digital-to-Analog TV Coupon Program</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/broadcom_announces_complete_digital_tv_receiver_system_designed_to_meet_the_ntias_digital-to-analog_tv_coupon_program';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Broadcom Announces Complete Digital TV Receiver System Designed to Meet the NTIA\'s Digital-to-Analog TV Coupon Program'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2007/08/broadcom_announces_complete_digital_tv_receiver_system_designed_to_meet_the_ntias_digital-to-analog_tv_coupon_program.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Broadcom Announces Complete Digital TV Receiver System Designed to Meet the NTIA's Digital-to-Analog TV Coupon Program</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>August 20, 2007</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Politics & Policy">Politics & Policy</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2007/08/broadcom_announces_complete_digital_tv_receiver_system_designed_to_meet_the_ntias_digital-to-analog_tv_coupon_program.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2007/08/broadcom_announces_complete_digital_tv_receiver_system_designed_to_meet_the_ntias_digital-to-analog_tv_coupon_program.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2007/08/broadcom_announces_complete_digital_tv_receiver_system_designed_to_meet_the_ntias_digital-to-analog_tv_coupon_program.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2007/08/broadcom_announces_complete_digital_tv_receiver_system_designed_to_meet_the_ntias_digital-to-analog_tv_coupon_program.php&amp;phase=2&amp;title=Broadcom%20Announces%20Complete%20Digital%20TV%20Receiver%20System%20Designed%20to%20Meet%20the%20NTIA%27s%20Digital-to-Analog%20TV%20Coupon%20Program&amp;bodytext=Broadcom%20Corporation%20%28NASDAQ%3ABRCM%29%20%2C%20a%20global%20leader%20in%20semiconductors%20for%20wired%20and%20wireless%20communications%2C%20today%20announced%20a%20complete%20digital%20television%20receiver%20system%20targeted%20at%20the%20National%20Telecommunications%20and%20Information%20Administration%27s%20%28NTIA%27s%29%20digital-to-analog%20converter%20box%20program.%20The%20program%2C%20which%20is%20part%20of%20a%20Federal%20Communications%20Commission%20%28FCC%29%20initiative%2C%20includes%20a%20budget%20of%20%241.5%20billion%20that%20will%20be%20used%20to%20assist%20U.S.%20households%20in%20making%20an%20affordable%20transition%20from%20existing%20analog%20televisions%20to%20digital%20by%20providing%20coupons%20to%20households%20to%20defray%20the%20cost%20of%20digital%20TV%20converter%20boxes.%20Broadcom%20has%20introduced%20a%20turnkey%20digital%20television-on-chip%20%28TVoC%29%20and%20associated%20software%20to%20enable%20these%20digital-to-analog%20converter%20boxes%2C%20extending%20the%20lives%20of%20analog-only%20TVs.%0A%0AAs%20written%20in%20the%20Department%20of%20Commerce%27s%20Federal%20Register...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p class="prtitle">Broadcom Announces Complete Digital TV Receiver System Designed to Meet the NTIA's Digital-to-Analog TV Coupon Program</p>

<center><i>New Digital Television-on-Chip Enables OEMs to Deliver NTIA Coupon-Eligible Converter Boxes that Extend the Lives of Millions of Analog-Only Televisions in the U.S.</i></center><br />
<br />

<p><B>IRVINE, Calif., Aug. 20 /PRNewswire-FirstCall/</B> -- Broadcom Corporation (NASDAQ:BRCM) , a global leader in semiconductors for wired and wireless communications, today announced a complete digital television receiver system targeted at the National Telecommunications and Information Administration's (NTIA's) digital-to-analog converter box program. The program, which is part of a Federal Communications Commission (FCC) initiative, includes a budget of $1.5 billion that will be used to assist U.S. households in making an affordable transition from existing analog televisions to digital by providing coupons to households to defray the cost of digital TV converter boxes. Broadcom has introduced a turnkey digital television-on-chip (TVoC) and associated software to enable these digital-to-analog converter boxes, extending the lives of analog-only TVs.</p>

<p>As written in the Department of Commerce's Federal Register, dated March 15, 2007, the Digital Television Transition and Public Safety Act of 2005 (the Act) directs the FCC to require full-power television stations to cease analog broadcasting and to only broadcast digital transmissions after February 17, 2009. As a result, televisions that receive over-the-air broadcasts through rabbit ear antennas will no longer work after February 17, 2009. At that point, the analog television spectrum will be freed up for public safety initiatives and will be auctioned off by the U.S. government for such applications as wireless networking.</p>

<p>For those consumers who want to continue receiving broadcast programming over-the-air using analog-only televisions not connected to cable or satellite service, a small digital-to-analog converter box will be required. These converter boxes are expected to be available in early 2008, at which time the NTIA has been authorized to create a digital-to-analog converter box assistance program for eligible households. As a result, the NTIA will provide two $40 discount coupons per household that consumers can redeem directly from retailers for these NTIA-qualified digital converter boxes. There is a total of 33.5 million coupons budgeted for the program, which begins January 1, 2008 and concludes on March 31, 2009.</p>

<p>Announced today is the Broadcom(R) BCM3543 ATSC (Advanced Television Systems Committee) receiver designed to receive ATSC high definition television (HDTV) broadcasts and convert them to NTSC (National Television Systems Committee) signals. Utilizing 65 nanometer process technology, the BCM3543 is a highly integrated, low power digital TVoC receiver that provides superior ATSC signal reception targeted at the NTIA's coupon-eligible "analog switch-off" converter box program. The BCM3543 features on-chip support to convert all ATSC standard and high definition inputs to 480i output formats for display on analog TVs.</p>

<p>"Broadcom is very excited to support the transition from analog-to-digital broadcasting by enabling our OEM partners to produce and quickly deliver digital-to-analog converter boxes as part of the NTIA coupon program," said Dan Marotta, Senior Vice President and General Manager of Broadcom's Broadband Communications Business Group. "With the introduction of the BCM3543 ATSC receiver, Broadcom continues its leadership in the digital television market and demonstrates its ability to efficiently integrate a wide variety of technologies required to meet the demands of the market."</p>

<p><br />
<B>Product Information</B></p>

<p>The BCM3543 converts ATSC signals to NTSC for displaying on analog TVs and features key system functionality that includes Channel 3 or 4 radio frequency modulated output, EIA/CEA-909 smart antenna support, and keypad and remote control support. This extensive level of support reduces the complexity and system cost associated with the design of NTIA-eligible converter boxes. The BCM3543 is also supported by an extensive hardware and software reference design that simplifies and minimizes the design and development process for Broadcom's OEM partners.</p>

<p>Compatible with an ATSC antenna design available exclusively from Broadcom, the BCM3543's antenna provides a high performance indoor solution that only requires a single cable interface, eliminating difficult and costly outdoor installations. The antenna control is seamlessly integrated into the BCM3543's user interface eliminating the need for consumers to have to manually adjust the antenna position in order to receive optimal signal reception.</p>

<p> The key features of the BCM3543 ATSC receiver include:</p>

<p> -- An integrated 8/16-VSB digital terrestrial receiver<br />
 -- An EIA/CEA 909-compliant smart antenna controller<br />
 -- An ATSC-compliant, all-format MP@HL MPEG-2 high definition video<br />
 decoder<br />
 -- A Channel 3-4 modulator<br />
 -- A Dolby(R) Digital and MPEG audio decoder<br />
 -- High quality video scaling<br />
 -- A picture enhancement processor<br />
 -- A 200 MHz MIPS32(R) CPU<br />
 -- A 32-bit 200 MHz DDR-DRAM controller</p>

<p><br />
<B>65 Nanometer Process Technology</B></p>

<p>The 65 nanometer (nm) process is the most advanced lithographic node for manufacturing semiconductors in large volumes today and provides significant benefits over 90 nm and 130 nm processes by enabling lower power consumption, smaller size and higher levels of integration. For Broadcom, the move to 65 nanometer process technology is changing the competitive landscape because of the breadth and depth of the communications intellectual property the company possesses. Without a broad portfolio of market-leading solutions to integrate, competitors are not able to take full advantage of the benefits that these next-generation processes provide. Broadcom's vast communications intellectual property for transporting voice, video and data at home, work and on-the-go are helping to offer customers and service providers with a truly seamless communications experience for end users worldwide.</p>

<p><br />
<B>Availability and Pricing</B></p>

<p>The 65 nanometer BCM3543 ATSC receiver is now sampling to early access customers. Pricing is available upon request.</p>

<p><br />
<B>About Broadcom's Broadband Communications Group</B></p>

<p>Broadcom offers manufacturers a range of broadband communications and consumer electronics SoC's that enable voice, video and data services over residential wired and wireless networks. These highly integrated silicon solutions continue to enable the most advanced system solutions on the market, which include digital cable, satellite and IP set-top boxes and media servers, broadband modems and residential gateways, high definition and digital televisions, HD DVD and Blu-ray Disc(TM) players, DVD recorders and personal video recorders.</p>

<p><br />
<B>About Broadcom</B></p>

<p>Broadcom Corporation is a major technology innovator and global leader in semiconductors for wired and wireless communications. Broadcom products enable the delivery of voice, video, data and multimedia to and throughout the home, the office and the mobile environment. We provide the industry's broadest portfolio of state-of-the-art, system-on-a-chip and software solutions to manufacturers of computing and networking equipment, digital entertainment and broadband access products, and mobile devices. These solutions support our core mission: Connecting everything(R).</p>

<p>Broadcom is one of the world's largest fabless semiconductor companies, with 2006 revenue of $3.67 billion, and holds over 2,200 U.S. and 900 foreign patents, more than 6,600 additional pending patent applications, and one of the broadest intellectual property portfolios addressing both wired and wireless transmission of voice, video and data. Broadcom is headquartered in Irvine, Calif., and has offices and research facilities in North America, Asia and Europe. Broadcom may be contacted at +1.949.926.5900 or at http://www.broadcom.com/.</p>

<p>Safe Harbor Statement under the Private Securities Litigation Reform Act of 1995:</p>

<p>All statements included or incorporated by reference in this release, other than statements or characterizations of historical fact, are forward- looking statements. These forward-looking statements are based on our current expectations, estimates and projections about our industry and business, management's beliefs, and certain assumptions made by us, all of which are subject to change. Forward-looking statements can often be identified by words such as "anticipates," "expects," "intends," "plans," "predicts," "believes," "seeks," "estimates," "may," "will," "should," "would," "could," "potential," "continue," "ongoing," similar expressions, and variations or negatives of these words. These forward-looking statements are not guarantees of future results and are subject to risks, uncertainties and assumptions that could cause our actual results to differ materially and adversely from those expressed in any forward-looking statement.</p>

<p>Important factors that may cause such a difference for Broadcom in connection with BCM3543 digital TV receiver products include, but are not limited to, general economic and political conditions and specific conditions in the markets we address, including the volatility in the technology sector and semiconductor industry, trends in the broadband communications markets in various geographic regions, including seasonality in sales of consumer products into which our products are incorporated, and possible disruption in commercial activities related to terrorist activity or armed conflict in the United States and other locations; the rate at which our present and future customers and end-users adopt Broadcom's technologies and products in the markets for digital television applications; delays in the adoption and acceptance of industry standards in those markets; the timing, rescheduling or cancellation of significant customer orders and our ability, as well as the ability of our customers, to manage inventory; the gain or loss of a key customer, design win or order; our ability to scale our operations in response to changes in demand for our existing products and services or demand for new products requested by our customers; our ability to specify, develop or acquire, complete, introduce, market and transition to volume production new products and technologies in a cost- effective and timely manner; intellectual property disputes and customer indemnification claims and other types of litigation risk; the quality of our products and any remediation costs; changes in our product or customer mix; the volume of our product sales and pricing concessions on volume sales; the effectiveness of our expense and product cost control and reduction efforts; our ability to timely and accurately predict market requirements and evolving industry standards and to identify opportunities in new markets; problems or delays that we may face in shifting our products to smaller geometry process technologies and in achieving higher levels of design integration; our ability to retain, recruit and hire key executives, technical personnel and other employees in the positions and numbers, with the experience and capabilities, and at the compensation levels needed to implement our business and product plans; the risks and uncertainties associated with our international operations; competitive pressures and other factors such as the qualification, availability and pricing of competing products and technologies and the resulting effects on sales and pricing of our products; the timing of customer-industry qualification and certification of our products and the risks of non-qualification or non-certification; the availability and pricing of third party semiconductor foundry, assembly and test capacity and raw materials; fluctuations in the manufacturing yields of our third party semiconductor foundries and other problems or delays in the fabrication, assembly, testing or delivery of our products; the risks of producing products with new suppliers and at new fabrication and assembly facilities; the effects of natural disasters, public health emergencies, international conflicts and other events beyond our control; the level of orders received that can be shipped in a fiscal quarter; and other factors.</p>

<p>Our Annual Report on Form 10-K, subsequent Quarterly Reports on Form 10-Q, recent Current Reports on Form 8-K, and other Securities and Exchange Commission filings discuss the foregoing risks as well as other important risk factors that could contribute to such differences or otherwise affect our business, results of operations and financial condition. The forward-looking statements in this release speak only as of this date. We undertake no obligation to revise or update publicly any forward-looking statement for any reason.</p>

<p>Broadcom(R), the pulse logo, Connecting everything(R) and the Connecting everything logo are among the trademarks of Broadcom Corporation and/or its affiliates in the United States, certain other countries and/or the EU. Dolby(R) is a trademark of Dolby Laboratories Licensing Corporation. MIPS32(R) is a trademark of MIPS Technology, Inc. Blu-ray Disc(TM) is a trademark of the Blu-ray Disc Association. Any other trademarks or trade names mentioned are the property of their respective owners.</p>

<p> Broadcom Trade Press Contact<br />
 Laura Brandlin<br />
 Senior Director, Marketing Communications<br />
 949-926-5108<br />
 lbrandlin@broadcom.com</p>

<p> Broadcom Investor Relations Contact<br />
 T. Peter Andrew<br />
 Vice President, Corporate Communications<br />
 949-926-5663<br />
 andrewtp@broadcom.com</p>

<p> Broadcom Technical Contact<br />
 Charlie Lou<br />
 Product Marketing Manager, Digital TV<br />
 949-926-8508<br />
 clou@broadcom.com</p>

<p>Photo: NewsCom: http://www.newscom.com/cgi-bin/prnh/20060609/BROADCOMLOGO<br />
AP Archive: http://photoarchive.ap.org/<br />
PRN Photo Desk, photodesk@prnewswire.com</p>

<p>Source: Broadcom Corporation; BRCM Broadband</p>

<p>CONTACT: Trade Press, Laura Brandlin, Senior Director, Marketing<br />
Communications, +1-949-926-5108, lbrandlin@broadcom.com, or Investor<br />
Relations, T. Peter Andrew, Vice President, Corporate Communications,<br />
+1-949-926-5663, andrewtp@broadcom.com, or Technical Contact, Charlie Lou,<br />
Product Marketing Manager, Digital TV, +1-949-926-8508, clou@broadcom.com, all<br />
of Broadcom</p>

<p>Web site: http://www.broadcom.com/</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>August 20, 2007 06:19 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 673
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
 			<h2>More on Politics & Policy</h2><ul><?
 			$sql = "
 			SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name
 			FROM mt_entry e, mt_author a, mt_placement p, mt_category c
 			WHERE entry_author_id = author_id
 				AND e.entry_id = p.placement_entry_id
 				AND p.placement_category_id = c.category_id
 				AND category_label = 'Politics & Policy'
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
 				AND entry_id <> 673
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2007/08/broadcom_announces_complete_digital_tv_receiver_system_designed_to_meet_the_ntias_digital-to-analog_tv_coupon_program.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
