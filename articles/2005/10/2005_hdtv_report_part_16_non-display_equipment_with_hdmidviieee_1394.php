<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 230";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Rodolfo La Maestra'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 230 AND placement_is_primary = 1";
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
	<meta name="keywords" content="dvd audio, dvd player, hdmi dvi, hdmi output, hdmi inputs, hdmi, HDMI, DVD, dvd, dvi, DVI, audio, receiver, video, output, input, Audio, TTM, ttm, ces, CES, inputs, player, ieee, Denon" />
	<meta name="description" content="This part 16 deals with Non-display Equipment with HDMI/DVI/IEEE1394 Capabilities.  Every year I follow the industry regarding this subject because more products are using DVI and HDMI and is becoming a problem trying to connect them all together within an HDTV system.  Many consumers have given up and purchased DVI/HDMI switchers (and additional cabling) because their receivers/pre-pros were not capable to do that job due to lack of sufficient digital video inputs, or any inputs at all.  Some of those receivers were already top of the line (expensive) relatively new products and suddenly became obsolete.  This year a larger number of units implementing DVI and HDMI were released, in addition to the monitors and integrated TVs mentioned on the previous sections of the 2005 Technology report.  Although the implementation of HDMI inputs/outputs was seen in more receivers, it was still more often seen on top-of-the–line receivers, which usually are on the $4000-$6000 range; more recent models (arrived after this report) that are capable of HDMI switching, were finally added on lower price ranges.  Fortunately, the transformation is moving faster than the first two years, and soon it might not be necessary for me to monitor and report units as exceptions to guide consumers on better purchases.  The data included on this part is just an example of some of the new units, not a complete list.  In the 2004 report there are more non-display receivers/pre-pros with DVI/HDMI switching that might still be available.  Additionally, several manufacturers of switching devices have added new intelligent self-detection models on their product list. However, the problem with routing out to the switcher DVI/HDMI connections is the decentralization of all video and audio, a function the receiver/pre-pro was expected to do. Another problem is backward compatibility on the design of the connectivity of receivers/pre-pros, they have no other choice than to have busy and oversized back panels to suit all the component analog, DVI and HDMI mix and match possibilities a given consumer might need. The price of evolution.  An evolution that is inducing consumers to replace equipment of otherwise acceptable performance.  Do not miss a future part analyzing digital connectivity, and the claims some have made about HDMI." />
	<title>HDTV Magazine Articles - 2005 HDTV Report, Part 16: Non-Display Equipment with HDMI/DVI/IEEE 1394</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/2005_hdtv_report_part_16_non-display_equipment_with_hdmidviieee_1394';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('2005 HDTV Report, Part 16: Non-Display Equipment with HDMI/DVI/IEEE 1394'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/articles/2005/10/2005_hdtv_report_part_16_non-display_equipment_with_hdmidviieee_1394.php";
		if ($author[img] != '' && 1 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Rodolfo La Maestra" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">2005 HDTV Report, Part 16: Non-Display Equipment with HDMI/DVI/IEEE 1394</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Rodolfo La Maestra</b><br />
				<?=$author_title?>
				Posted on <b>October 25, 2005</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Products & Equipment">Products & Equipment</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2005/10/2005_hdtv_report_part_16_non-display_equipment_with_hdmidviieee_1394.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/articles/2005/10/2005_hdtv_report_part_16_non-display_equipment_with_hdmidviieee_1394.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/articles/2005/10/2005_hdtv_report_part_16_non-display_equipment_with_hdmidviieee_1394.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2005/10/2005_hdtv_report_part_16_non-display_equipment_with_hdmidviieee_1394.php&amp;phase=2&amp;title=2005%20HDTV%20Report%2C%20Part%2016%3A%20Non-Display%20Equipment%20with%20HDMI%2FDVI%2FIEEE%201394&amp;bodytext=This%20part%2016%20deals%20with%20Non-display%20Equipment%20with%20HDMI%2FDVI%2FIEEE1394%20Capabilities.%20%20Every%20year%20I%20follow%20the%20industry%20regarding%20this%20subject%20because%20more%20products%20are%20using%20DVI%20and%20HDMI%20and%20is%20becoming%20a%20problem%20trying%20to%20connect%20them%20all%20together%20within%20an%20HDTV%20system.%20%20Many%20consumers%20have%20given%20up%20and%20purchased%20DVI%2FHDMI%20switchers%20%28and%20additional%20cabling%29%20because%20their%20receivers%2Fpre-pros%20were%20not%20capable%20to%20do%20that%20job%20due%20to%20lack%20of%20sufficient%20digital%20video%20inputs%2C%20or%20any%20inputs%20at%20all.%20%20Some%20of%20those%20receivers%20were%20already%20top%20of%20the%20line%20%28expensive%29%20relatively%20new%20products%20and%20suddenly%20became%20obsolete.%20%20This%20year%20a%20larger%20number%20of%20units%20implementing%20DVI%20and%20HDMI%20were%20released%2C%20in%20addition%20to%20the%20monitors%20and%20integrated%20TVs%20mentioned%20on%20the%20previous%20sections%20of%20the%202005%20Technology%20report.%20%20Although%20the%20implementation%20of%20HDMI%20inputs%2Foutputs%20was%20seen%20in%20more%20receivers%2C%20it%20was%20still%20more%20often%20seen%20on%20top-of-the%E2%80%93line%20receivers%2C%20which%20usually%20are%20on%20the%20%244000-%246000%20range%3B%20more%20recent%20models%20%28arrived%20after%20this%20report%29%20that%20are%20capable%20of%20HDMI%20switching%2C%20were%20finally%20added%20on%20lower%20price%20ranges.%20%20Fortunately%2C%20the%20transformation%20is%20moving%20faster%20than%20the%20first%20two%20years%2C%20and%20soon%20it%20might%20not%20be%20necessary%20for%20me%20to%20monitor%20and%20report%20units%20as%20exceptions%20to%20guide%20consumers%20on%20better%20purchases.%20%20The%20data%20included%20on%20this%20part%20is%20just%20an%20example%20of%20some%20of%20the%20new%20units%2C%20not%20a%20complete%20list.%20%20In%20the%202004%20report%20there%20are%20more%20non-display%20receivers%2Fpre-pros%20with%20DVI%2FHDMI%20switching%20that%20might%20still%20be%20available.%20%20Additionally%2C%20several%20manufacturers%20of%20switching%20devices%20have%20added%20new%20intelligent%20self-detection%20models%20on%20their%20product%20list.%20However%2C%20the%20problem%20with%20routing%20out%20to%20the%20switcher%20DVI%2FHDMI%20connections%20is%20the%20decentralization%20of%20all%20video%20and%20audio%2C%20a%20function%20the%20receiver%2Fpre-pro%20was%20expected%20to%20do.%20Another%20problem%20is%20backward%20compatibility%20on%20the%20design%20of%20the%20connectivity%20of%20receivers%2Fpre-pros%2C%20they%20have%20no%20other%20choice%20than%20to%20have%20busy%20and%20oversized%20back%20panels%20to%20suit%20all%20the%20component%20analog%2C%20DVI%20and%20HDMI%20mix%20and%20match%20possibilities%20a%20given%20consumer%20might%20need.%20The%20price%20of%20evolution.%20%20An%20evolution%20that%20is%20inducing%20consumers%20to%20replace%20equipment%20of%20otherwise%20acceptable%20performance.%20%20Do%20not%20miss%20a%20future%20part%20analyzing%20digital%20connectivity%2C%20and%20the%20claims%20some%20have%20made%20about%20HDMI.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<blockquote>This is the next in a series of articles taken from the <b>H/DTV Technology Review & CES 2005 Report</b> by Rodolfo La Maestra, published in March 2005. If you are interested in downloading the full version of this report, it is currently available for purchase from our <a href="/store/ces-2005.php">CES Report</a> page.</blockquote>

<p>This year a larger number of units implementing DVI and HDMI were released, in addition to the monitors and integrated TVs mentioned on the previous sections.</p>

<p>Although the implementation of HDMI inputs/outputs was also seen in more receivers, it is still mainly reserved for top-of-the-line receivers, which usually are on the $4000-$6000 range.    <br />
   <br />
The data included below is an example of some of the new units, not a complete list.</p>

<p><br />
<h2>HDMI/DVI Receivers/Switching</h2><br />
<b>Denon</b><br />
AVP-1x Studio Reference Video Preamp, DVI/HDCP, HDMI switching, video upconversion</p>

<p><u>New Denon receiver with multiple HDMI</u></p>

<p>In addition to the current AVR-5805 $6000 receiver with 3 HDMI/ 1 DVI inputs and one HDMI output (introduced in Sep 04), Denon announced at CES 2005 a new receiver AVR-4806 for $3000 due in March 2005 with 3 HDMI 1.1 (2.0) inputs (that can be used for DVD-Audio signals), one DVI input, a DVI-converting HDMI output, a IEEE1394 input for DVD-Audio/SACD, and a proprietary Denon Link DVD-Audio input.  Three video zones, component video upconversion, S-video up-down conversion, Audyssey Lab's MultEQ for correction of room acoustics with multiple listeners, Dolby Headphone, Dolby Virtual Speaker.</p>

<p><br />
<b>Halcro</b><br />
<u>Surround processors</u><br />
With HDMI outputs, deinterlacing upscaling to 1080p on HDMI, A/D conversion<br />
SSP100	$10000<br />
SSP80		$8000</p>

<p><br />
<b>Integra Research</b><br />
Sep 04<br />
RDC-7.1 preamp/processor, TTM Nov 04, $4000, HD radio and XM upgrade cards available after introduction.</p>

<p><br />
<b>JVC</b><br />
RXD701/702B		A/V top end receiver with 7x150 watt channels, HDMI input w/upscaling HDMI output, Dolby Prologic IIx, USB input for PC connection, built-in 802.11 2.4 GHz receiver for streaming audio, wireless transmitter that plugs into a PC's USB input  </p>

<p><br />
<b>Marantz</b><br />
<u>CES 2005</u><br />
SR9600	140x7 watts A/V receiver, 2 HDMI in/ 1out, IEEE1394 connection, $3500, TTM Apr 05, video upconversion to component analog from 480i S-video/composite w/TBC</p>

<p>SR8500	125x7 watts A/V receiver, 2 DVI in/ 1 out, $1600, TTM current</p>

<p><br />
<b>Onkyo</b><br />
TX-NR1000	$4000, 7.1 channels x150 watts, HDMI, IEEE1394</p>

<p><br />
<b>Samsung</b><br />
Oct 04<br />
Samsung announced for March 2005 the introduction of its first A/V receiver for a price ranging the $4000-$5000 mark.  The receiver will have 7x250 watt amps, 3 two-channel audio zones with its internal digital amplifier, RS-232, DC triggers, IEEE1394 input for DVD-Audio and SACD, two HDMI inputs and one output, two DVI inputs and one output, automatic room equalization, video up-conversion over HDMI or DVI, up-scaling of DVD to HD, FM tuner. </p>

<p><u>CES 2005</u><br />
The receiver announced above was demo at CES with a model # AV-R3000, for $5000, TTM Jun/Jul 05, 4 HDMI inputs/one HDMI output, 2 DVI inputs/ 1 DVI output, 250x7 watt channels, video upscaling, component 480i upscaled to 1080i of not-protected content</p>

<p><br />
<b>Sony</b><br />
<u>CES 2005</u><br />
ES Series A/V Receiver<br />
STR-DA7100ES	$2000, 170 Watts x 7, 7.1 channel A/V receiver, HDMI switching, 720p/1080i upconversion, 32-bit S-Master Pro Digital Amplifier, iLink IEEE1394 for audio, independent 5.2 channels and 2 channel surround, second zone video/audio, third zone audio only.</p>

<p><br />
<h2>HDMI/DVI DVD players</h2><br />
<b>Denon</b></p>

<p>New Denon DVD player DVD-5910 for $3500 with 1394 output for DVD-Audio/SACD, and HDMI output for DVD-Audio, the first DVD player to use Teranex de-interlacer chip Realta HQV, simultaneous SD and HD outputs.</p>

<p><br />
<b>Integra</b><br />
Sep 04<br />
Five-disc universal DVD player with HDMI; single DVD-Audio/video players are discontinued.</p>

<p><br />
<b>Integra Research</b><br />
Sep 04<br />
Universal RDV-1.1	$4000, TTM Oct 04, HDMI/HDCP input/switcher to connect other HDMI components thru the DVD player.</p>

<p><br />
<b>Marantz</b><br />
Sep 04<br />
DV9500 first universal DVD player to incorporate HDMI with upconversion, TTM Oct 04, $2100, DVD-Audio, and SACD with full bass management, Dolby Headphone </p>

<p><br />
<b>Theta</b><br />
Compli universal DVD player, $5900, DVI card for 480p, component</p>

<p><br />
<b>Yamaha</b><br />
DVD-S2500 first universal player with HDMI/HDCP upconverts to 720p/1080i, $700, TTM Jan 05</p>

<p>Be sure that you read the next article in the series: Digital Connectivity (Coming Soon)</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Rodolfo La Maestra</b>, <b>October 25, 2005 05:35 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 230
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
			
 		<?if (1 <> 7) {
 			# Recent Articles by Author (exclude this one)
 			# Do not show recent articles for Bulletins.
 			$qry = "
 			SELECT entry_id, entry_blog_id, entry_created_on, entry_title, author_id, author_name
 			FROM mt_entry e, mt_author a
 			WHERE entry_blog_id = 1
 				AND entry_id <> 230
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2005/10/2005_hdtv_report_part_16_non-display_equipment_with_hdmidviieee_1394.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
