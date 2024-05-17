<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 123";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Dale Cripps'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 123 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category[placement_category_id];
	
	# Still need this to track page views. Used in the Links unit in the footer
	$google_links_channel = $author[channel];

	# Set variables based on entry type. NOTE: Podcasts has its own entry template, so it is not included amongst the choices below.
	switch (5) {
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
	<meta name="keywords" content="advanced television, hdtv system, dsc hdtv, high definition, television test, HDTV, hdtv, television, system, digital, film, advanced, ntsc, NTSC, picture, new, Television, systems, video, signal, Advanced, been, high, actv, ACTV" />
	<meta name="description" content="Alan Meckler of Meckler Media and I did 3 large HDTV conference in the late 80s and 1991. About the same time we started publishing together the HDTV World Review , which ran for four years. I did the gathering..." />
	<title>HDTV Magazine Archive &amp; History - 1991 Fall - HD World Review</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/1991_fall_-_hd_world_review';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('1991 Fall - HD World Review'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/history/2005/06/1991_fall_-_hd_world_review.php";
		if ($author[img] != '' && 5 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Dale Cripps" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">1991 Fall - HD World Review</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Dale Cripps</b><br />
				<?=$author_title?>
				Posted on <b>June 26, 2005</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category="></a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/2005/06/1991_fall_-_hd_world_review.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/history/2005/06/1991_fall_-_hd_world_review.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/history/2005/06/1991_fall_-_hd_world_review.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/2005/06/1991_fall_-_hd_world_review.php&amp;phase=2&amp;title=1991%20Fall%20-%20HD%20World%20Review&amp;bodytext=Alan%20Meckler%20of%20Meckler%20Media%20and%20I%20did%203%20large%20HDTV%20conference%20in%20the%20late%2080s%20and%201991.%20About%20the%20same%20time%20we%20started%20publishing%20together%20the%20HDTV%20World%20Review%20%2C%20which%20ran%20for%20four%20years.%20I%20did%20the%20gathering...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p><em>Alan Meckler of Meckler Media and I did 3 large HDTV conference in the late 80s and 1991. About the same time we started publishing together the <u>HDTV World Review </u>, which ran for four years. I did the gathering of the authors and assigned topics. Alan was responsible for physical publishing and mail distribution of the publication. This is just one issue but it is significant in that it shows the evolution of thinking towards what we now have. Without this seminal thinking HDTV would have been derailed along the way. I am constantly impressed with the far reaching vision that many of our authors had. They understood where HDTV was going, though in the early days the technical approach was not on as solid a footing as was the vision to where we would go.</em> _Dale Cripps</p>

<p>In This Issue...<br />
 <br />
<strong>Digital Spectrum Compatible HDTV</strong><br />
Wayne Luplow page 4</p>

<p><strong>Advanced Digital Television</strong><br />
Glenn Reitmeier and Carlo Basile  </p>

<p><strong>Advanced Compatible Television</strong><br />
Michael Isnardi et. al.  </p>

<p><strong>Advanced Television Test Center Update</strong><br />
Joel Chaseman  </p>

<p><strong>HDTV And Movies in Japan</strong><br />
Takehisa Ishida </p>

<p><strong>HDTV In A Stall -- A Vision</strong><br />
Dale E. Cripps  </p>

<p><strong><em>Editorial Board</em></strong><br />
Executive Editor: Dale E. Cripps<br />
Editor: Bija Gutoff<br />
Managing Editor: Doreen Beauregard<br />
Editorial Vice President: Anthony Abbott<br />
Advertising Director: Marilyn Reed<br />
Publisher: Alan M. Meckler</p>

<p><br />
Update July 13, 1999</p>

<p><em>Before there was the Grand Alliance, there were the parts. What follows are several of the parts which later fused into being the Grand Alliance.</em><br />
Dale E. Cripps</p>

<p><br />
<strong>Digital Spectrum Compatible HDTV</strong><br />
Wayne Luplow outlines the HDTV solution developed by Zenith and AT&T, called Digital Spectrum Compatible HDTV. DSC-HDTV is designed to address many of the real-world obstacles of today’s U.S. broadcasting environment. Building on a wide acceptance of the basic advantages of digital signal transmission, the Zenith/AT&T system extends the benefits of digital with new featuers. Progressive scanning is a faster form of high resolution scanning that permits close synergy between HDTV and computer technology. Low-power transmitters and special filters allow DSC to eliminate interference both to and from NTSC channels. And compression technology helps DSC squeeze data-heavy digital pictures into a 6 MHz channel. These advances, together with proposed solutions to application issues such as television station upconversion and interconnectivity with computers, make a cogent argument for DSC as a significant advance over today’s NTSC system.</p>

<p><strong>Advanced Digital Television</strong><br />
Glenn Reitmeier and Carlo Basile summarize the Advanced Television Research Consortium’s work on Advanced Digital Television, a fully digital system that delivers HDTV in a 6MHz channel. The ATRC has developed new digital compression and transmission techniques, including MPEG++, Prioritized Data Transport format, and Spectrally-Shaped QAM, as key elements in an ADTV system that provides high-quality broadcast digital HDTV service. Building on a foundation of accepted technologies and emerging standards, they also propose ADTV for its economy, reliable service, compatibility with other industries and growth potential.</p>

<p><strong>Advanced Compatible Television</strong><br />
Another view from the Advanced Television Research Consortium comes from Michael Isnardi and colleagues, who believe that existing NTSC channels should be upgraded even while new HDTV standards are being implemented. ACTV is a single-channel compatible widescreen transmission system that has been submitted as a candidate for the U.S. EDTV standard. With ACTV, broadcasters can take a gradual approach to equipping stations with advanced television systems, thereby gaining enhanced definition, wide aspect ratio and digital audio while remaining compatible with standard 6MHz NTSC channels and existing receivers.</p>

<p><strong>Advanced Television Test Center Update</strong><br />
Joel Chaseman, a television industry insider for many years, gives a brief progress report on the work of the Advanced Television Test Center, a group made up of broadcast networks and associations together with the Electronics Industry Association.</p>

<p><strong>HDTV and Movies In Japan</strong><br />
In Japan, HDTV has already been used to make many feature films. NHK’s Takehisa Ishida provides a look at HDTV’s role in movie system applications, including not only movie making itself but various combinations of film, broadcast and video with distribution via satellite, cable, tape or disk. He describes recent advances in HDTV equipment now available for movie systems, and offers a discussion of practical considerations in making HDTV movies, with synopses of a half-dozen such projects.</p>

<p><strong>HDTV In A Stall</strong><br />
In light of HDTV’s current stall--while testing, standard-setting and other issues remain to be worked out--Dale Cripps suggests the missing ingredient: one driving vision, and a visionary in the tradition of David Sarnoff to embody it. Cripps also proposes a new scenario, global networks, to reflect a new-world coalescence of viewer audiences, broadcasters, manufacturers and program producers.</p>

<p>Bija Gutoff<br />
Editor</p>

<p><br />
<strong>Digital Spectrum Compatible HDTV From Zenith/AT&T</strong>By Wayne Luplow</p>

<p>The advantages of high-definition television are commonly known--outstanding picture and equally impressive sound. But it is not commonly known how HDTV would overcome obstacles to those advantages in the U.S. terrestrial broadcasting environment. The proponents of a U.S. HDTV standard need to describe how their systems would operate in a real world fraught with picture distortion, interference, cliff effects, channel-change recovery difficulties and complex motion scenes. And they should describe how their HDTV systems could be applied in other media such as cable, computers, fiber, satellite and VCRs.</p>

<p>It is this context of real world applications, problems and solutions that reveals the excellence of the Digital Spectrum Compatible HDTV (DSC-HDTV) system from Zenith Electronics Corporation and AT&T.</p>

<p><strong>Digital HDTV</strong></p>

<p>All but one of the proposed HDTV systems uses a digital rather than an analog format. The advantages of digital signal transmission include pictures of movie theater quality that are free of snow and ghosts that degrade conventional broadcasts. Images possess a lifelike quality heretofore unseen in television broadcasts. That picture quality is accompanied by sound quality that matches a compact disc. Digital transmission also offers potential for easier interfaces and interference-free interaction with other media such as cable, satellite, fiber and computers.</p>

<p><strong>Digital Spectrum Compatible HDTV</strong></p>

<p>Several advantages of digital transmission technology are extended by specifics of the Zenith/AT&T system. DSC-HDTV uses progressive scanning, for example, which presents complete picture information twice as fast as interlace scanned systems. DSC-HDTV gives a complete picture every sixtieth of a second, twice as fast as interlace scanned systems, which give a complete picture every thirtieth of a second. Given equal time comparisons, the Zenith/AT&T system gives 1,575 lines of resolution in one-thirtieth of a second while proposed interlace systems give 1,125 or 1,050 lines every one-thirtieth of a second. The progressively scanned picture from DSC-HDTV is free of jagged edges and other picture-distorting problems that plague interlace scanned systems. Progressive scanning also gives the Zenith/AT&T system excellent synergy with computer technology, which also uses progressively scanned images. Thus, DSC-HDTV images could be displayed on a computer screen without going through complex processing. Synergy with computer systems also is enhanced by the use of square picture elements, which perform better with computer graphics and in special effect productions.</p>

<p>Zenith/AT&T transmission technology eliminates interference. The transmission system uses a low-power transmitter and signal processing steps to avoid interference into NTSC channels. The other side of the equation--preventing NTSC signals from interfering with weak HDTV signals--posed a significant challenge. The solution centers on a unique digital filter in the HDTV receiver and a complementary filter in the HDTV transmitter. As a result of this unique filtering technology, true high-definition television would be available in the same service area as traditional NTSC broadcasts. The system delivers a clear, digital-perfect signal even at the fringes of a reception area. Although some have speculated about sudden loss of picture in a digital environment, no such effect would exist in the Zenith/AT&T system. In other proposed digital systems, a compilation of errors can lead to a “cliff effect,” in which a receiver beyond a certain distance from the transmitter suddenly loses the HDTV picture. A viewer at that distance would essentially be without HDTV service. DSC-HDTV, however, would produce clear pictures even at the outer edges of today's typical terrestrial broadcast service area (55 miles from the transmitter). Good quality HDTV pictures with some visible errors would be received up to 70 miles away from the transmitter.</p>

<p>DSC-HDTV also would use unique compression technology to squeeze the massive amounts of digital picture information into a 6MHz channel. The system's compression algorithm has several unique features, including motion compensation, which analyzes each television frame at the transmitter to prepare it for transmission; and adaptive quantization, which takes into account the idiosyncrasies of the human visual system to present HDTV pictures without perceptible loss of resolution. Full high-definition resolution is retained even when broadcasting live, rapid-motion sports and similar events. Even temporary loss of resolution when changing channels is minor. Image recovery is crucial, especially when many channels are available. DSC-HDTV would give complete image recovery in a fraction of a second. Recovery in other proposed systems may be far slower.</p>

<p>A serious discussion of an HDTV standard for the U.S. market must include not only performance issues, but also issues of application. Broadcaster concerns about start-up costs are consistently raised. But, contrary to common belief, broadcasters will be able to enter the era of high-definition television without replacing all of their studio equipment.</p>

<p>With a simple, new “up-conversion” process, many television stations won't need to make a costly switch to a full high-definition studio with new cameras, recorders and encoders. By equipping their stations with a Zenith/AT&T Digital Spectrum Compatible simulcast transmitter and antenna, broadcasters would be able to “pass through” network television programming. For locally produced programming, they would use the new up-conversion process to broadcast very high quality NTSC images on the digital transmission system. With a minimal investment, television stations would be able to move to high-definition television broadcasts by acquiring simulcast transmission equipment necessary to broadcast a network-fed high-definition signal and the up-converted locally produced program material.</p>

<p>Issues of interconnectivity also are crucial to discerning the best HDTV system for the U.S. market. The synergy between DSC-HDTV and the computer environment is clear. Progressive scanning greatly reduces the complexity of processing HDTV information so it can be displayed on a computer workstation. Square pixels perform better with computer graphics and special effect productions.</p>

<p>DSC-HDTV also offers opportunities for interoperability, or ease of conversion among different formats, change resolution, frame rates, etc. Interoperability would come into play when HDTV signals are transmitted for communication and computing purposes, stored and interfaced for other uses. The DSC-HDTV limits the amount of processing and storage required for these processes.</p>

<p>Extensibility also is an issue that should come into play when discussing HDTV system proposals. The basic extensibility question for any HDTV system is: Is this particular system able to adapt to more advanced technology that is likely to be developed in the future? The Zenith/AT&T system is flexible, allowing it to economically adapt its current standards to higher resolution displays as they are developed. Any winning HDTV system should serve as a building block for future technological advances. An application of extensibility may be, for example, the use of a windowed computer program to display HDTV video within a high resolution image on a computer display. The window could use an image of different resolution from the image shown on the main section of the screen.</p>

<p>As extensibility is needed to build on a system's technology, a successful HDTV system would allow for scalability, which allows easy creation of lower resolution pictures from compressed HDTV material.</p>

<p><strong>Conclusion</strong></p>

<p>Proponents of specific HDTV systems should gain credibility as they explain how their systems would address real world broadcasting problems. These problems, and their solutions as proposed by Zenith and AT&T, indicate the strength of the Digital Spectrum Compatible HDTV system. Obvious benefits of digital HDTV include outstanding picture and sound, easier interface with computer, telephone data and other communications systems, and interference-free signals for cable, satellite, fiber and VCRs.</p>

<p>These positives are expanded in the Zenith/AT&T system through employment of progressive scanning to eliminate jagged edges and other picture-distorting problems, and square pixels to perform better with computer graphics and in special effect television productions. Image recovery from channel changes would be in a fraction of a second.</p>

<p>Because of unique transmission technology, DSC-HDTV would prevent NTSC and HDTV signals from interfering with one another. Unique compression technology in the DSC-HDTV system includes several features that retain full high-definition resolution even when broadcasting live, rapid-motion sports and similar events.</p>

<p>Other significant advantages of the DSC-HDTV include service area equal to today's typical NTSC service area and reasonably priced start-up costs to broadcasters via up-conversion.</p>

<p>Finally, the DSC-HDTV system has exceptional interconnectivity characteristics, a feature that is crucial to any system's success. Computer friendliness, interoperability, extensibility and scalability all are strong aspects of the Zenith/AT&T proposal.</p>

<p>Individually, any one of these improvements over today's NTSC system would be a significant advance. Combined, they represent a major technological development of the 1990s that should yield fruit well into the twenty-first century.</p>

<p>Wayne C. Luplow is Zenith Electronic Corporation’s division vice president of research and development for advanced television systems. He heads the company’s high-definition television R&D program, leading the Zenith/AT&T technical team developing the Digital Spectrum Compatible HDTV system. In conjunction with his Zenith responsibilities, Luplow is active in industry-wide HDTV activities, including the FCC’s Advisory Committee on Advanced Television Services, the Center for Advanced Television Services, and the executive committee of the Advanced Television Systems Committee. A senior member of the Institute of Electrical and Electronics Engineers, Luplow serves as editor of the IEEE’s Transactions on Consumer Electronics. He is an elected member of the administrative committee of IEEE’s Consumer Electronics Society and has been publication chairman for the committee since 1976. Before joining Zenith, Luplow was employed by RCA at the David Sarnoff Research Laboratories in Princeton, New Jersey. He holds a BSEE from the University of Wisconsin and a MSEE from the University of Pennsylvania.</p>

<p><br />
<strong>Advanced Digital Television</strong><br />
By Glenn Reitmeier and Carlo Basile</p>

<p>Introduction</p>

<p>The United States faces a significant challenge in establishing an HDTV standard that can survive the next 50 years. Digital compression and transmission technology represents a revolutionary new approach to the design of television systems, and the opportunity to establish a new standard is well-timed to coincide with the practical application of these technologies. HDTV needs to be flexible and extensible, accommodating new and as yet unforeseen media and applications, now and in the twenty-first century. Over the lifetime of a simulcast HDTV standard, the universality of digital technology will surely lead to novel combinations of applications that are now considered separate, and will enable greater compatibility among different types of consumer electronics, telecommunications, and computing equipment. This challenge--to deliver the best HDTV system for the U.S. that can be built upon in the future--is being met by the Advanced Television Research Consortium (ATRC) in the form of Advanced Digital Television (ADTV). ADTV's strengths are its superior quality image, its robust signal and graceful degradation characteristics, and its important means of providing for future developments.</p>

<p><strong>Advanced Digital Television</strong></p>

<p>ADTV is a fully digital system that delivers high-definition television in a 6MHz channel (Figure 1). The design of ADTV has been driven by the need of the terrestrial broadcast, cable television, and consumer electronics industries to have an economical HDTV simulcast system that provides robust and reliable service; that can peacefully co-exist with NTSC; and that can be expanded upon and grow in the future. To meet these needs, ADTV has made significant improvements to proven digital compression and transmission techniques, and molded them into a single cohesive system.</p>

<p><em><strong>There are three key elements in the ADTV system:</strong></em></p>

<p>-First, ADTV’s video compression, called MPEG++, is based on a special ATRC implementation of the MPEG** (Moving Pictures Expert Group) compression approach. MPEG++ upgrades the standard MPEG approach to HDTV performance level and incorporates a video data prioritization process that identifies the most important video data so that it can be transmitted with the greatest reliability. The MPEG foundation provides compatibility with the computer industry, which results in an economically attractive approach. Further, our MPEG++ extensions provide for reliable service in the terrestrial broadcast environment.</p>

<p>-Second, ADTV has a Prioritized Data Transport (PDT) layer. PDT is a cell relay-based data transport format that supports the prioritized delivery of video data, thus providing the feature of graceful service degradation under impaired channel conditions. The cell relay-based format is similar to that of broadband ISDN (Integrated Services Digital Network) and provides for a high degree of interoperability and growth potential. PDT also offers service flexibility, allowing broadcasters to deliver virtually any mixture of video, audio, and auxiliary data, as market opportunities evolve.</p>

<p>-Third, ADTV applies spectral-shaping techniques to Quadrature Amplitude Modulation (QAM) to carefully minimize interference from and to any co-channel NTSC signals. The result is an extremely robust data transmission system, known as the Spectrally-Shaped QAM (SS-QAM), that can co-exist with NTSC in the simulcast environment.</p>

<p><strong>System Rationale</strong></p>

<p>To understand some of the basic elements within ADTV and to fully appreciate their benefits, a discussion of some of the rationale that shaped the ADTV system is in order. A well designed digital HDTV system for terrestrial broadcast should offer significant advantages in performance and flexibility over analog approaches. These advantages manifest themselves in many ways in ADTV.</p>

<p>As a first example, although ADTV has been submitted to the ACATS as a 1050-line system, it is designed to provide flexible support of a wide range of services and future media formats. The initial hardware implementation will use the interlaced scan format for video source and display (1050/59.94/2:1), with a 16:9 aspect ratio and more than twice the NTSC resolution. Selection of this initial format was based on current camera and display technologies, and does not preclude a future adoption of other video formats, consistent with the evolution of studio equipment and production standards.</p>

<p>Another fundamental advantage of a digital approach is that computationally-based digital approaches allow a video compression system to efficiently and adaptively exploit spatial and temporal redundancies in a picture far better than analog approaches can. Research efforts in video data compression have been ongoing for several years within ATRC laboratories. Figure 2 illustrates the basic concept of motion-compensated video compression. At the encoder, motion vectors are computed to predict the current frame from a previous (and/or following) frame. This process is efficient because only the motion vectors and the difference are transmitted, not the entire frame. A rigorous and comprehensive evaluation of numerous video compression approaches was of primary importance to the design of ADTV. Among different video compression techniques, the MPEG approach represents a collection of well-known, well-proven compression methods that have been filtered from a great deal of image coding research conducted around the world during the past years. For example, Advanced Digital Television's MPEG is unique among the digital HDTV proposals because it uses forward and backward motion compensation resulting in improved picture quality. The picture quality of ATRC's implementation of MPEG was competitive with other ATRC-proprietary techniques. The combination of MPEG's performance and the fact that it already enjoys the benefit of being widely accepted as an emerging international standard caused it to be selected as the basis for ADTV.</p>

<p>Digital transmission techniques have the well-known property of being impervious to moderate levels of channel impairment, and avoiding accumulation of noise and other artifacts when passed through a series of transmission relay stages. But MPEG, like any video compression, is vulnerable to hostile transmission conditions. To combat the disruptive nature of transmission errors on the video data stream, a channel-specific video prioritization layer was designed into the ADTV compression algorithm. We use the name MPEG++ to indicate that MPEG has been adapted and improved to meet the requirements of the terrestrial broadcast environment. Coupled with prioritized data delivery, MPEG++ represents an extremely robust video compression approach.</p>

<p>The unique requirements of simulcast transmission demand special attention to the delivery and transport of the compressed video information. This motivated an ATRC research effort to design a transport format to provide extremely reliable delivery of the ADTV service to viewers. The result of this “channel-ruggedization” effort is the “fast packet” cell transport format. Cell-relay transport format refers to a data communication format in which information bits are carried in cells consisting of fixed-size data, header, and trailer fields (Figure 3). Because of the relative ease of handling a fixed-size cell, even at high-speed, most high-speed data communication networks have adopted a fixed-size cell relay format. A well-known example of a cell-relay transport format is the ATM (asynchronous transfer mode) protocol in broadband ISDN.</p>

<p>ADTV's cell transport format ensures a high degree of robustness of the signal. As reception worsens in fringe areas, for example, packet loss occurs. Other digital systems may take an entire frame time to recover from a packet loss. ADTV's PDT allows resynchronization of the video data to occur during the next cell. This is made possible by putting recovery information in each cell's header.</p>

<p>With its link-level asynchronous time division multiplexing features, ADTV offers the advantage of flexible multiplexing of video, audio, and data with bit-rates that do not need to be specified in advanced. This is made possible since each cell specifies its own service type (e.g., identifying video, audio 1, audio 2, auxiliary data, etc.) and any combination of cells is allowed!</p>

<p>Perhaps the most difficult part of a digital HDTV system is its RF transmission approach. Once the data have been compressed by the source coder, they must be “packaged” and transmitted over the hostile terrestrial broadcast channels. It is a mandatory design consideration that any new HDTV service be NTSC co-channel compatible in order to allow the new HDTV signal to be broadcast without requiring additional transmission spectrum. Currently-defined “taboo” channels are available in the sense that there are presently no signals that are transmitted at these taboo frequencies. These channels are taboo, however, because a typical NTSC broadcast signal transmitted at these frequencies would interfere with and be interfered by the broadcasts in existence today. This situation poses additional design requirements to include a transmission technique that will be robust in an NTSC co-channel and taboo-channel environment.</p>

<p>ADTV addresses these low interference requirements in two directions. First, minimal interference from the new HDTV signal into NTSC (so as not to disrupt existing NTSC services) is achieved with lower power for the ADTV signal. Second, minimal interference from NTSC into the HDTV signal (so that a high-quality HDTV service can be provided) is achieved by an ADTV signal that is relatively immune to NTSC interference and noise.</p>

<p><strong>Summary</strong></p>

<p>ADTV is a practical solution that meets the challenges of the digital simulcast environment. The efficient MPEG++ compression, the Prioritized Data Transport format, and the Spectrally-Shaped QAM have been melded into a powerful system approach for digital simulcast of HDTV. The successful integration of these three key system layers means that ADTV will provide robust high-quality broadcast digital HDTV service to the American public.</p>

<p>Economy, reliable service, compatibility with other industries, and growth potential are four significant benefits derived from ADTV. ADTV is a proposal that is built upon a solid foundation of widely accepted technologies and important emerging standards. This approach will lead to rapid industry acceptance, but perhaps even more importantly, it will establish an important common technology base across many business sectors vital to successful development of a globally-competitive HDTV industry in the United States.</p>

<p>Glenn Reitmeier is director of the High-Definition Imaging and Computing Laboratory at the David Sarnoff Research Center in Princeton, New Jersey, where he is responsible for computing research and digital high-definition television activities. He joined RCA Laboratories at the David Sarnoff Research Center in 1977, the same year he received a BSEE summa cum laude from Villanova University. He also holds a master's degree in systems engineering from the University of Pennsylvania. Reitmeier's professional expertise is in the fields of digital television, image processing, and computing. Carlo Basile is head of the Advanced Television Systems department at Philips Laboratories, North American Philips Corporation in Briarcliff Manor, New York. He joined the staff of Philips Laboratories in 1983 and has been involved in the development of various advanced television systems for terrestrial, cable, and satellite broadcasting. He holds a BSEE and a MSEE from the Polytechnic Institute of New York. The authors wish to express their thanks to Cynthia S. Gray for her help in the preparation of this article. The Advanced Television Research Consortium is comprised of the David Sarnoff Research Center, Thomson Consumer Electronics, Inc., NBC, and Philips Consumer Electronics Company.</p>

<p><br />
<strong>Advanced Compatible Television</strong><br />
By Michael Isnardi, C.B. Dieterich, T.R. Smith, R.N. Hunt and J.L. Koslov</p>

<p><strong>Introduction</strong></p>

<p>While the implementation of HDTV will mean simulcasting new channels with new amenities, current NTSC channels will remain in place for many years. Concurrent with the establishment of this new standard of quality for HDTV should be an upgrade of those existing channels, maximizing the full benefits of NTSC and providing a higher level of performance. Advanced Compatible Television (ACTV) will improve NTSC to its fullest advantage to benefit consumers, broadcasters, cable operators, and satellite service providers.</p>

<p>Advanced Compatible Television is a single-channel compatible widescreen transmission system submitted for testing as a candidate EDTV standard for the United States. It provides broadcasters a degree of flexibility in equipping stations for an advanced television system by allowing a gradual approach to a full or partial upgrade. A move to implement ACTV would also serve as an impetus to commence full-fledged widescreen production.</p>

<p><strong>Advanced Compatible Television</strong></p>

<p>ACTV offers enhanced definition, wide aspect ratio, and digital audio while fitting compatibly within a standard 6MHz NTSC channel. ACTV is compatible with all existing NTSC receivers. When ACTV is transmitted, NTSC receivers will display a normal (4x3) aspect ratio, NTSC-quality picture virtually indistinguishable from their present performance; ACTV receivers will display a 16x9 aspect ratio image, a horizontal resolution improvement over NTSC, and progressive scanning. ACTV is consistent with display technologies anticipated for the future, and it can be delivered without new channel allocations. ACTV has undergone continual improvement over the past five years to provide a 16x9 widescreen picture with enhanced resolution, reduced NTSC artifacts, and digital audio.</p>

<p>ACTV offers broadcasters a rapid, affordable way to deliver widescreen programming without losing their current audience share and without finding additional broadcast spectrum. Because of its affordability and ease of implementation, ACTV can serve as a catalyst for HDTV by building a population of widescreen programs and receivers. Even in a future HDTV simulcast environment, broadcasters will have the option to preserve the value of their NTSC channel by upgrading to ACTV. Each broadcaster can select the right system or combination of systems to meet that challenge from competing widescreen media.</p>

<p><strong><em>System Rationale</em></strong></p>

<p>The ACTV video signal is fully compatible when decoded by existing NTSC receivers. The transmitted ACTV signal consists of a main signal plus an auxiliary horizontal detail signal sent in RF quadrature. The main signal contains the NTSC encoded 4x3 center panel plus additional side panel information. Existing NTSC receivers decode the main signal as a normal color picture; the side panel and auxiliary signals are physically or perceptually hidden from view. All signals are recovered by an ACTV receiver to reconstruct a widescreen EDTV image with CD-quality sound. Also included are improved immunity to channel noise, ghost cancelling technology, and a channel equalizer that works in tandem with the ghost canceller to correct distortions in the transmitted signal.</p>

<p>The ACTV encoder accepts a widescreen source image and produces an “NTSC-like” version of the 4x3 center panel at its output. Side panel information, extra horizontal luma detail and digital audio are encoded and transmitted in such a manner as to be transparent to NTSC-compatible receivers.</p>

<p>The ACTV decoder, tuned to the same channel as the NTSC-compatible receiver, recovers the side panels, extra horizontal luma detail, and digital audio to produce a noticeably improved widescreen image with CD-quality sound.</p>

<p>Figures 1 and 2 show high-level block diagrams of the ACTV system and ACTV components as presented to the Advanced Television Test Center in July, 1991. The ACTV encoder has the following major features:</p>

<p><strong><em>Video Encoder Features:</em></strong></p>

<p>-The Encoder Pre-Processor converts the 525-line progressive scan (525/1:1) YIQ inputs to 525-line interlace (525/2:1) format after appropriate filtering and subsampling in the vertical-temporal (V-T) domain. All interlaced signals are horizontally bandlimited, and the luma highs and chroma signals are spatially tapered from center to side to enhance compatibility.</p>

<p>-The Component 1 Encoder produces an NTSC-encoded version of the 4x3 center panel, with luma side panel lows compressed into the left and right overscan regions.</p>

<p>-The Component 2 Encoder produces a modulated signal containing side panel information, which is added to Component 1.</p>

<p>-The Component 3 Encoder translates the extra horizontal luma detail to a band suitable for RF quadrature modulation.</p>

<p>-The Burst, Sync and Reference Signal Inserter adds composite sync and burst to the video and inserts a special waveform on one line in the vertical interval to aid the decoder in deghosting, equalizing and timing.</p>

<p><strong>Audio Encoder Features:</strong></p>

<p>-The Analog Audio Encoder produces a standard BTSC stereo signal.<br />
-The Digital Audio Encoder transforms two channels of audio into a 256 kbit/sec bit stream.</p>

<p><strong><em>Modulator Features:</em></strong><br />
-The IF Modulator combines the in-phase and quadrature phase video components, and analog and digital audio components at Intermediate Frequency (IF). The Advanced Television Test Center accepts the IF signal and up-converts to RF for testing on both VHF and UHF channels.</p>

<p><strong><em>The ACTV decoder has the following major features:</em></strong></p>

<p><strong><em>Tuner/IF Features:</em></strong></p>

<p>-The ACTV Tuner tunes to the VHF or UHF channel and translates the resulting band back to IF. It is a standard tuner design with improved flatness and carrier stability. A VSB filter in the IF quadrature demodulator ensures the recovery of both the in-phase and quadrature components.</p>

<p><strong>Audio Decoder Features:</strong></p>

<p>-The Analog Audio Decoder recovers the standard BTSC stereo signal.</p>

<p>-The Digital Audio Decoder transforms the 256 kbit/sec bit stream into two channels of audio. The analog audio signal will automatically replace the digital audio signal under poor carrier-to-noise (CNR) conditions.</p>

<p><strong>Video Decoder Features (Figure 4):</strong></p>

<p>-The Deghoster/Equalizer processes the reference signal to determine the location, amplitude and phase of ghosts. Short-delay ghosts (and pre-ghosts) are equalized with an FIR filter and long-delay ghosts are canceled with an IIR filter.</p>

<p>-The Component 1 Decoder recovers the 4x3 center panel YIQ components and side panel luma lows.</p>

<p>-The Component 2 Decoder recovers the side panel luma highs and and side panel chroma.</p>

<p>-The Component 3 Decoder recovers the extra horizontal luma detail under high signal-to noise (SNR) conditions and translates it to its original band of frequencies.</p>

<p>-The Decoder Post-Processor performs inverse spatial tapering under high SNR conditions and converts the recovered widescreen YIQ interlaced signals into progressive-scan format for display.</p>

<p><strong>Summary</strong></p>

<p>Performance and flexibility are key elements of the ACTV design philosophy. ACTV offers flexibity in terms of choices to broadcasters and consumers alike. For broadcasters concerned with controlling costs, ACTV is an economical alternative to simulcast HDTV. Even in a simulcast environment, a broadcaster has the option to upgrade the NTSC service with ACTV. For consumers, ACTV offers the widescreen viewing experience without confusion and at a fraction of the cost of true HDTV. By maximizing the capabilities of NTSC and extending the list of features it can provide, ACTV will enable broadcast services to provide the kind of performance the American viewing public deserves.</p>

<p><em>Michael Isnardi is head of Systems Research in the Television Research Laboratory at the David Sarnoff Research Center. He joined the Sarnoff Center in 1987 as a member of the ACTV technical staff. This article was adopted from a paper entitled, “Advanced Compatible Television: Progress and Improvements.” Collaborating on this paper were C.B. Dieterich, senior member of the Technical Staff; T.R. Smith, head of Advanced Video Technology Research; R.N. Hurst, associate member of the Technical Staff; and J.L. Koslov, member of the Technical Staff. The Advanced Television Research Consortium is comprised of the David Sarnoff Research Center, Thomson Consumer Electronics, Inc., NBC, and Philips Consumer Electronics Company.</em></p>

<p><strong>Advanced Television Test Center Update</strong><br />
By Joel Chaseman</p>

<p>With testing underway at the Advanced Television Test Center, the staff, the proponents and the board find themselves united with the FCC’s Advisory Committee and the FCC itself. Ours is a somewhat fractious union, turbulent from day to day, but together toward a common goal to see that North America emerges in a timely manner with a broadcast standard for advanced television that serves the greatest number of people in the most efficient and effective possible way.</p>

<p>Getting here has been no fun at all. Since Dennis Patrick had the wisdom and foresight to appoint Richard Wiley as chairman of a unique and precendential FCC Advisory Committee, the goal has been clear; but the means of getting there uncharted and unpredictable.</p>

<p>Broadcast networks, groups and associations joined with the Electronic Industry Association and financed the Advanced Television Test Center. In turn, the ATTC has worked in close cooperation with Cable Labs and Canada’s Advanced Television Evaluation Laboratory. We found it necessary to invent hardware systems and procedures, and to employ conventional gear in unconventional ways. Chief Scientist Charles Rhodes and Executive Director Peter Fannon recruited some of the best and the brightest to program the software, design the plant, and coordinate with the Advisory Committee’s working parties.</p>

<p>There has been no lack of surprises and adjustments. Much basic technology changed from analog to digital and remains to be proven. Tests are still being written and negotiated. There will be additional changes as technologies develop and as each new system is required to explain itself. The process has been a uniquely American one--a coming together of various industries and government itself in a public/private effort to create a new communications technology which will lead the world.</p>

<p>Despite all the difficulties and the occasional trauma, I can assure you that the Advanced Television Test Center will accomplish its goals. We are grateful for the FCC’s overseeing, expertise and day-to-day input under the leadership of Chairman Alfred C. Sikes as well as for the outstanding leadership of its Advisory Committee, the electronic industry itself, and, above all, the board and staff of this unique organization.</p>

<p>North America will soon emerge with an advanced television broadcast standard for the world. We at the ATTC have confidence that the five years of unprecedented international research, cooperation, invention and plain hard work will prove to be uniquely fulfilling and, perhaps, even a model for future international technical development and standard-setting.</p>

<p>Joel Chaseman is chairman of Chaseman Enterprises International, an investment firm organized to create and participate in ventures in communications, marketing and the fine arts. He is also chairman of the board of directors of the Advanced Television Test Center. Chaseman has served on the boards of several other television industry groups, including the National Association of Broadcasters and the National Academy of Television Arts and Sciences, and has received two Emmy awards and many other professional honors. He was the first president of Group W Productions, a television programming syndicator; chairman and chief executive officer of Post-Newsweek Stations, Inc.; and a vice president of The Washington Post company; he has also worked as a director, producer, narrator, reporter and sales and advertising manager. Chaseman is a graduate of Cornell University.</p>

<p><br />
<strong>HDTV And Movies in Japan</strong><br />
By Takehisa Ishida</p>

<p>HDTV, which has been under development by NHK for the past 20 years as the next generation television system, successfully brought live television broadcast of the Seoul Olympic Games to Japan in 1988. More recently, another step has been taken toward practical use by initiating regular daily one-hour experimental broadcasts in Japan by direct broadcast satellite. HDTV was originally intended for broadcasting use, but it is now expected to be widely applied to various industrial fields such as movies, printing, publishing and advertising. This is because it offers about five times the volume of information of conventional television systems and the picture quality is almost equal to that of 35mm motion picture film. Among the various proposed uses, the application of HDTV technology to movies--long the biggest image medium--is expected to have great promise and exert a major influence.</p>

<p><strong>The Application of HDTV to Movie Systems</strong></p>

<p>The application of HDTV to movie systems does not only mean movie production. Various applications have been proposed, as shown in Figure 1, such as using movies for HDTV and existing broadcasting programs after converting the films to video, distributing them to HDTV theaters via satellite or cable, tape or disk, and distributing the films to existing movie theaters after converting from HDTV to film. HDTV and film have different characteristics, so it is very important to use both most effectively, taking into consideration their respective advantages.</p>

<p><strong>The Present Status of HDTV Equipment for Movie Systems</strong></p>

<p>Over the past few years, the characteristics and function of various HDTV equipment have been improved significantly, and some new types of equipment have been developed and are available for application in the movie system. Following is a discussion of such equipment.</p>

<p><strong>Cameras</strong></p>

<p>The sensitivity of the early HDTV camera was so low that it required illumination of 2,000 lx. at F2.8, which is equivalent to ASA 50 film. Later, a second-generation camera was developed with two to three times higher sensitivity, allowing easy shooting of indoor scenes. Recently, an entirely new type of camera has been developed with about ten times higher sensitivity using a HARP target tube (High-Gain Avalanche Rushing Amorphous Photoconductor). It makes possible image pick-up under illumination conditions down to 200 lx. at F2.8.</p>

<p><strong>VTR</strong></p>

<p>One-inch C-type analog VTRs have long been used for HDTV systems, but have insufficient dubbing characteristics for such functions as sophisticated image composition. A one-inch digital VTR developed two years ago has superior dubbing characteristics, enabling sophisticated image editing and manipulation. It also has a slow play-back function, allowing new types of effects. A half-inch cassette VTR developed recently is very compact and effective for playback in HDTV theaters and other venues.</p>

<p><strong>Telecine</strong></p>

<p>Two types of telecine equipment, the laser scan system and the Saticon camera system, have been developed for practical use. For frame rate conversion from film to HDTV, a new method1 has been developed by NHK. The number of frames per second in the conventional film system is 24, whereas the HDTV system operates on 30 frames (or 60 fields). In order to convert the 24 frame/second film picture into a 60 field/second HDTV picture, two film frames must be converted into five HDTV fields, as illustrated in Figure 2. On the left is the conventional method. In this case, the smoothness of a moving subject--an animal in this figure--is degraded by a phenomenon known as judder. On the right is a newly developed method called “motion compensated frame rate conversion system,” which is applied in the laser telecine system. This method detects the amount and direction of the moving position of the picture from two consecutive frames of film in the form of motion vectors utilizing frame memory. Using these motion vectors, the converter creates a 60 field/second 2-to-1 interlaced picture in which the correct position of the motion area has been interpolated. This eliminates judder and results in natural movement.</p>

<p><strong>Post-Production</strong></p>

<p>Compounding, processing and editing pictures by optical processes in conventional movie production requires much time and advanced techniques. HDTV allows similar effects with less effort. Various kinds of HDTV special effects equipment, including Chroma-key, DVE (Digital Video Effect) and Video-matte, have already been developed for this purpose. Among them, the HDTV Video-matte system recently developed by NHK is a very effective tool for creating composite pictures. Unlike the above mentioned Chroma-key, the operator can generate an arbitrary key signal electronically by computer graphics, while watching the picture on the monitor. A composite picture can be obtained from two on-location pictures without using a blue mat.</p>

<p><strong>Film Recorder</strong></p>

<p>To show programs produced by HDTV at cinema theaters, they must be transferred to film. For this purpose, two such systems have been developed and applied. One utilizes laser beams and the other, an electronic beam. In the laser system, the intensity of three laser beams (red, green and blue) are modulated by the HDTV signals and are recorded directly on continuously running 35mm film. This system uses three laser beams of high intensity and can record signals in real time, not only on negative film but also on color positive film and color internegative film of high picture quality. In the electron beam system, an electron beam is modulated by the HDTV signal and is recorded on black and white film in red, green and blue frame sequential mode. This black and white film is overlap-printed on color internegative film by using a step printer. It is then printed on color positive film.</p>

<p><strong>Display</strong></p>

<p>To show the program in the HDTV video theater, display is very important because the evaluation of picture quality depends most strongly on the capability and characteristics of the display system. Various types of HDTV display have been used in Japan, such as the CRT front and rear projection display, the light-valve type display, the liquid crystal projection display and multi-cube type display. These various types of display have to be chosen according to the purpose for which they are going to be used and the environmental conditions for installation such as illumination, stray light, viewing distance and screen size, etc.</p>

<p><strong>Practical Movie Production by HDTV</strong></p>

<p>Nearly 20 movies have been produced using HDTV technology in Japan to date. Some were produced using only HDTV, but in general HDTV technology is preferred for making composite pictures. Here are some examples:</p>

<p>Saiyuhki is a movie made for children, based on a Chinese classic drama. HDTV was used to combine different formats, including film, HDTV and NTSC television. Figure 3 illustrates the production process2 of Saiyuhki. As shown in the figure, the film material was converted to an HDTV signal by laser telecine, and NTSC material was up-converted using a standard converter. All the material was combined and edited in the HDTV post-production process. After that, the HDTV picture was transferred to film by laser film recorder. Then the transferred film was edited, together with film shot originally by cine camera. The finished film was released at commercial theaters in 1988 and was well received.</p>

<p>Several notable points were observed during the actual production process:</p>

<p>-Differences in frame dimension and image size between film produced by the conventional movie method and that converted from HDTV.</p>

<p>-Difference in sharpness and S/N among the various picture materials, including film shot directly by cine camera, film converted from HDTV, and HDTV picture converted from film by telecine.</p>

<p>-Difference in dynamic range and gamma characteristics between HDTV and film.</p>

<p><br />
-Difference in the number of frames per second for film and HDTV.</p>

<p>-Problems in combining processes of the various pictures.</p>

<p>In order to solve these problems practically, some prior tests were done and several techniques were effectively applied.</p>

<p>Shuppatsu or, A Long Way From Home is a story produced by NHK about a young boy who sets out, travelling alone for the first time, on a three-day trip to see his father. It depicts the hurdles set up along the path of life in the boy’s struggles to grow toward adulthood while overcoming fears and uncertainties. This movie was shot and completely post-produced by the HDTV system and transferred to 35mm film by laser recorder. In the process, Ultimatte and Video-matte were used to synthesize the pictures in as many as 50 scenes; for example, in imaging synthesis to produce the effect of increasing the number of people or objects. This film was awarded a citation in the international electronic cinema festival held in Montreux in 1989.</p>

<p>Gen-Myoh, or Profound Beauty is a special effects film combining traditional Japanese Noh dance and a typical Japanese interior. This is an experimental program produced by MPTE (Motion Picture and Television Engineering of Japan) and sponsored by HVC (Hi-Vision Promotion Center, Inc.) to prove the viability of combining HDTV and traditional film techniques. Original picture materials taken from film, CG and HDTV were combined together in the HDTV processing. The finished HDTV video was then transferred to 35mm film by laser recorder. Gen-Myoh received an award in the technical competition of UNIATEC (Union International des Associations Techniques Cinematographiques) held in Montreal in 1989.</p>

<p>Ryu-Oh, Bi no Yuhgoh or Combination of the Beauty is an experimental program produced jointly by MPTE and CSMPTE (Chinese Society of Motion Picture and Television Engineering) in 1989. Ryu-Oh features performances of traditional dancing arts such as Japanese Kabuki and Chinese Peking Opera, or Kyo-Geki. As the program’s title implies, the technology of HDTV and cinema were combined. Shooting was done on 35mm film in China, while HDTV and 35mm film were used in Japan. Both film pictures were converted to HDTV signal by laser telecine, and chroma-key composition was added. After combining the material and editing, the finished HDTV video was transferred to film, utilizing a laser film recorder, and was shown in Japan in 1989.</p>

<p>Mai-Hime is a tragic love drama produced jointly by a Japanese-West German team. HDTV was skillfully used to synthesize a picture of an old ship floating in Tokyo Bay, a coastal area of western Japan, and a scene on the Suez Canal. After conversion from HDTV to film by Electron Beam Recorder and editing, the film was released in theaters across Japan in the spring of 1989.</p>

<p>Yume or Dreams is a fantasy cinema feature which consists of eight short stories produced by Japan’s best-known film director, Akira Kurosawa, and sponsored by Steven Spielberg. In one story, the main character enters into a painting and walks around inside the landscape of the picture. In making the film, the HDTV chroma-key was used to synthesize the figure with the painted picture. The film was released in 1990.</p>

<p><br />
<strong>HDTV Theater and Electronic Distribution</strong></p>

<p>At present, about 30 video theaters using the existing television system are installed in department stores and shopping centers in Japan. The PAL system is used to prevent illegal copying. CRT projection display is generally used and the screen size is about 150 inches. A bigger screen size is not available because of the limitation of the picture quality on the existing television system. The introduction of the HDTV system will therefore be appreciated. In Japan, there are no HDTV movie theaters yet; but there are a considerable number of places available for multipurpose use, including HDTV pictures, in Tokyo, Osaka and other parts of the country.</p>

<p>As a display, the CRT projection type in main use has a screen size of about 100 to 200 inches. In some cases, Talaria--a kind of light valve display--is being used for sizes larger than 200 inches. Talaria was used at the Tokyo Cinema Festival held in Tokyo last year, and received much praise.</p>

<p>In the future, as the number of HDTV theaters increases and they are linked via satellite or cable, an electronic distribution system will be established. Various programs, including movies, will be transmitted to theaters in the form of an HDTV signal. It will be a very effective method to reduce costs and save time in film distribution. As a result, a new type of theater will be created, combining movies and live HDTV broadcasts of programs such as sports and other events, utilizing the features of real-time transmission.</p>

<p>In 1988, there was an experiment3 on electronic distribution, connecting a television station in Tokyo with a movie theater and an event-hall in Nagoya, southern Japan, via communication satellite, as shown in Figure 4. In the experiment, the signal was transmitted in the form of MUSE and projected onto the screen by Talaria. For comparative evaluation, a film projector was also used; and the result proved that electronic distribution was possible.</p>

<p>At the annual SMPTE conference held in Los Angeles in October, 1989, some of the programs mentioned above (such as Ryu-Oh, Gen-Myoh and Mai-Hime) were transmitted from Japan to Los Angeles using the satellite Intelsat V. The encoded digital signal format CODEC was used and was successful in transmitting the movies from Japan to the United States. The subjects for a future study of electronic distribution are how to choose the signal format for transmission and how to scramble the system.</p>

<p><strong>Future Prospects</strong></p>

<p>We have described the overall application of the HDTV system to movies in Japan. The characteristics and function of HDTV equipment have improved significantly in recent years. However, there are several problems still to be resolved, and it is necessary to develop additional equipment most suitable for the movie system. For example:</p>

<p>-Development of small, light and easy to handle equipment similar to that used in the cinema system.</p>

<p>-Solution of several problems caused by the difference in the number of frames per second between film and HDTV.</p>

<p>-Development of more advanced and higher quality compound picture techniques.</p>

<p>-Strong promotion of HDTV theater.</p>

<p>-Development of a network utilizing satellite or cable for electronic distribution.</p>

<p>-Reducing the cost of HDTV equipment.<br />
The author believes that the application of HDTV to movie systems will be further promoted as each of these problems are solved.</p>

<p><em><strong>Notes</strong></em><br />
1. Y. Nojiri et al, “Present Status of Laser Telecine and Laser Beam Recording,” 130th SMPTE Technical Conference.<br />
2. T. Ishida et al, "Production of Saiyuhki Using Hi-Vision," ITEJ Technical Report, Volume 13, No. 13, OPT, January, 1989.<br />
3. S. Nakayama, "Experimental Hi-Vision Theater Using Communication Satellite," Journal of MPTE of Japan, July, 1988.</p>

<p>Takehisa Ishida has been engaged in developing HDTV systems at NHK Laboratories and Broadcasting Center for more than ten years. In the past few years, he has been studying and promoting the HDTV application for various industrial fields, such as movie systems, at NHK Engineering Services, Inc. He is now working at Video Engineering of Productions Operations Center in the Broadcast Engineering Department.</p>

<p><br />
<strong>HDTV In A Stall</strong><strong>Waiting For Global Win(d)s And A Vision</strong><br />
By Dale E. Cripps</p>

<p>HDTV has been a high profile item over the last five years. There was a growing anticipation that something good was on the horizon. HDTV was supposed to be the symbol of America’s competitiveness and the driver of essential technologies. Yet HDTV may fail as it faces an enormously entrenched base of well-working NTSC, PAL and SECAM color television systems around the world that block its realization. Political and technical obstructions also abound. Film remains the only single production standard for high-definition programming. Commercially speaking, HDTV is going nowhere. What the HDTV movement requires, in order not to lose its window of opportunity, is a new self-identity--a powerful vision of where it is going and how. In today’s global era, a new scenario being discussed is that of one or more great global networks.</p>

<p><strong>Global Television Networks</strong></p>

<p>There are those who say that HDTV is a brand new medium and should be treated only as such. Everything relating to it should maximize it and eliminate compromise. If technology and programming made for it can be used for other media, that's fine, but it's not the essential thing. This sets it completely apart from the existing television business and lets it seek brand new operators and markets without consideration for what is going on in the lower television systems, just as those systems do today with radio. It would be a media of special abilities and unique qualities, and it would attract all who seek its intrinsic value--exactly as people sought television as the next stage after radio. Just as people turned to radio, then to black and white television, and then to color, there will be those who will turn to HDTV as long as satisfying programming is there to meet them.</p>

<p>The world is both a bigger place and a smaller one at the same time. It is bigger because few of us are totally provincial now. We have all left our villages, towns and cities--physically or electronically--to travel the world. Film and television have sped us on our way. At the same time, it is smaller, as we are transported instantly to all the continents via CNN and others to see revolutions and walls falling in previously guarded corners of the world.</p>

<p>While these events do not erase cultural conditioning, the current trend is toward an international mentality through common global experiences. The language of music, computer language, and the language of shadows, light and color are common. Movies, whose themes embrace the fundamental emotions and de-emphasize regional fashions, do more or less equally well everywhere, with rare exceptions.</p>

<p>Can programming impresarios and technology Goliaths work together and woo international audiences to a brand new medium? Is there a reward that warrants all that risk? If there is such a reward socially or industrially, the challenge must be mounted with the combined strength of all the hardware makers and the prime program producers working together to these same ends. Not that there is insufficient financial power to underwrite a global network without these powerful players. But no financial rulers have shown the willingness to step into the breach until the supporting players are committed, focused on defined goals, and staffed with capable people. There must be a fusion of ambitions and wills of the likes of MCA, Paramount, Disney, Sony Pictures, MGM, etc., with the hardware makers such as Matsushita, Philips, Samsung, Thomson and Toshiba, and Sony. This fusion has not, of course, taken place.</p>

<p>The hardest problem to solve among all the players is the lack of a shared vision to bring all the elements together. Hardware and program groups don't communicate well enough, each being suspicious of the others' motives. To bring HDTV to a new reality it may take no less than the rise-to-power of a "new David Sarnoff" to play a role similar to the last Sarnoff, who in his time so effectively created the business of radio and television. The next David Sarnoff would employ the familiar ideas of the past, but this time on a global scale. The next Sarnoff would be granted command of the resources of both hardware and software constituents, for a time. All would be sheltered in a grand international coalition whose only raison d’être is the successful launch of the next generation of television. The coalition would have to trust and willingly put itself under the leadership of the next Sarnoff, who would organize the various parts to act as one.</p>

<p>Nothing less than this concentration of willpower will be able to break the general deadlock for HDTV, or pierce the armor built from today's entrenched television standards. For every step forward by individual companies there are a thousands snares made by competitors and alien services. If these snares remain, the HDTV movement will be worn down until the pain of continuing exceeds the potential rewards and HDTV is dropped as a main line item. It will be relegated to the junk heap of other consumer frustrations.</p>

<p>That would be a sad mistake. HDTV offers the finest means to date for nourishing the minds of humanity with all the astounding beauty and character of our nature, of our civilizations, and of the "thousands of stories in the naked city" that make up the fabric of our cultures. To the visionaries ushering in HDTV means a new era--perhaps a non-violent and peace <em>appreciating </em>era. HDTV could serve as an example of how to release the grips of outmoded systems. HDTV has a mission beyond the microcosm of making profits for individual companies and regions. It should be seen as a profit center to the whole world, a significant part to a new standard of living. If all the treasures of the world could be brought to you and your neighbor's house--for what good reason should that be denied?</p>

<p>The times may be right for this class of venture, given that the arts are sure to lead the world after the military era. Beauty can be lifted to the level of an essential ingredient of civilization, rather than an odd occurrence. That sublime communication is capable of passing though our HDTV systems very, very well. If the impresarios/technologists/humanitarians see a new opportunity to transfer back into their hands a business once lost to other people, a new golden era for television is sure to begin. If fusing together the diverse agendas for the arts and hardware groups occurs, television--high-definition television--may be one of the most significant advents in our history, even more so than its primitive predecessor.</p>

<p><em>Dale Cripps is a founder of Advanced Television Publishing, which publishes the HDTV Newsletter and custom reports on advanced television. Cripps also organizes HDTV conferences in the U.S. and Europe. In 1989, he co-founded the non-profit First International Academy, Institute and Foundation for High Definition Arts and Sciences to research and promote the value of HDTV. He also is a developer of the Trades Channel, a business-to-business direct satellite broadcast system scheduled to launch in the early 1990s using an advanced television format. Cripps studied business administration at Fullerton State College and Portland State University. He is a member of SMPTE, and participated in its committee for establishing the HDTV studio standard.</em></p>

<p></p>

<p>   <br />
</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Dale Cripps</b>, <b>June 26, 2005 12:00 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 123
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
 			<h2>More on </h2><ul><?
 			$sql = "
 			SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name
 			FROM mt_entry e, mt_author a, mt_placement p, mt_category c
 			WHERE entry_author_id = author_id
 				AND e.entry_id = p.placement_entry_id
 				AND p.placement_category_id = c.category_id
 				AND category_label = ''
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
			
 		<?if (5 <> 7) {
 			# Recent Articles by Author (exclude this one)
 			# Do not show recent articles for Bulletins.
 			$qry = "
 			SELECT entry_id, entry_blog_id, entry_created_on, entry_title, author_id, author_name
 			FROM mt_entry e, mt_author a
 			WHERE entry_blog_id = 5
 				AND entry_id <> 123
 				AND entry_status = 2
 				AND entry_author_id = a.author_id
 				AND a.author_name = 'Dale Cripps'
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
 				<h2>About Dale Cripps</h2>
 				<?=stripslashes($author[bio_short])?>
 			<span class="corners-bottom"><span></span></span></div>
 		<?}?>
		</td><td id="right">

 		<?if ($about != '') {?>
 			<div class="item"><span class="corners-top"><span></span></span>
 				<h2>About Archive &amp; History</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/2005/06/1991_fall_-_hd_world_review.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
