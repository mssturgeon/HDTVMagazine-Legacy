<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');

	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1539";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);

	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Rodolfo La Maestra'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";

	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1539 AND placement_is_primary = 1";
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
	<meta name="keywords" content="dtv transition, tuner integration, transition help, help part, tuner mandate, tuner, DTV, dtv, digital, integrated, DTVs, dtvs, tuners, inches, analog, sets, FCC, part, mandate, fcc, transition, july, July, Transition, help" />
	<meta name="description" content="This part is dedicated to tuner integration and the role it was expected to play in the DTV Transition.  &lt;p&gt;As mentioned in &lt;a href=&quot;http://www.hdtvmagazine.com/articles/2008/10/dtv_transition_-_can_you_help_part_3.php&quot;&gt;part 3&lt;/a&gt; of this series, 15 million households have over-the-air-only TVs, and many cable/satellite subscribers may also tune broadcast on their secondary TVs.  &lt;p&gt;Would DTV tuner integration address this situation?..." />
	<title>HDTV Magazine Articles - DTV Transition - Can YOU Help? (Part 4) - DTV Tuner Integration</title>
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

		$base_url = strleftback(PHP_SELF, '/') . '/dtv_transition_-_can_you_help_part_4_-_dtv_tuner_integration';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('DTV Transition - Can YOU Help? (Part 4) - DTV Tuner Integration'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/articles/2008/11/dtv_transition_-_can_you_help_part_4_-_dtv_tuner_integration.php";
		if ($author[img] != '' && 1 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Rodolfo La Maestra" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">DTV Transition - Can YOU Help? (Part 4) - DTV Tuner Integration</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Rodolfo La Maestra</b><br />
				<?=$author_title?>
				Posted on <b>November  3, 2008</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Digital (DTV) Transition">Digital (DTV) Transition</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2008/11/dtv_transition_-_can_you_help_part_4_-_dtv_tuner_integration.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/articles/2008/11/dtv_transition_-_can_you_help_part_4_-_dtv_tuner_integration.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/articles/2008/11/dtv_transition_-_can_you_help_part_4_-_dtv_tuner_integration.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2008/11/dtv_transition_-_can_you_help_part_4_-_dtv_tuner_integration.php&amp;phase=2&amp;title=DTV%20Transition%20-%20Can%20YOU%20Help%3F%20%28Part%204%29%20-%20DTV%20Tuner%20Integration&amp;bodytext=This%20part%20is%20dedicated%20to%20tuner%20integration%20and%20the%20role%20it%20was%20expected%20to%20play%20in%20the%20DTV%20Transition.%20%20%3Cp%3EAs%20mentioned%20in%20%3Ca%20href%3D%22http%3A%2F%2Fwww.hdtvmagazine.com%2Farticles%2F2008%2F10%2Fdtv_transition_-_can_you_help_part_3.php%22%3Epart%203%3C%2Fa%3E%20of%20this%20series%2C%2015%20million%20households%20have%20over-the-air-only%20TVs%2C%20and%20many%20cable%2Fsatellite%20subscribers%20may%20also%20tune%20broadcast%20on%20their%20secondary%20TVs.%20%20%3Cp%3EWould%20DTV%20tuner%20integration%20address%20this%20situation%3F...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<div class="editorial">The following article is the latest in the "DTV Transition - Can YOU Help?" series. Other articles in this series are as follows:
<ul><li><a href="/articles/2008/10/dtv_transition_-_can_you_help_part_1_-_transition_reception_and_help.php">DTV Transition - Can YOU Help? (Part 1) - Transition, Reception and Help</a></li>
<li><a href="/articles/2008/10/dtv_transition_-_can_you_help_part_2_-_a_technical_view.php">DTV Transition - Can YOU Help? (Part 2) - A Technical View</a></li>
<li><a href="/articles/2008/10/dtv_transition_-_can_you_help_part_3_-_tvs_vs_households.php">DTV Transition - Can YOU Help? (Part 3) - TVs vs. Households</a></li>
<li><a href="/articles/2008/12/dtv_transition_-_can_you_help_part_5_was_tuner_integration_timed_right.php">DTV Transition - Can YOU Help? (Part 5) - Was Tuner Integration Timed Right?</a></li>
<li><a href="/articles/2008/12/dtv_transition_-_can_you_help_part_6_-_subsidy_set-top-boxes.php">DTV Transition - Can YOU Help? (Part 6) - Subsidy Set-Top-Boxes</a></li>
</ul></div>
<br />
<p align="center"><b>Part 4 - DTV Tuner Integration</b>
<p>This part is dedicated to tuner integration and the role it was expected to play in the DTV Transition.  <p>As mentioned in <a href="http://www.hdtvmagazine.com/articles/2008/10/dtv_transition_-_can_you_help_part_3.php">part 3</a> of this series, 15 million households have over-the-air-only TVs, and many cable/satellite subscribers may also tune broadcast on their secondary TVs.  <p>Would DTV tuner integration address this situation? Yes, for those that purchased an integrated DTV, but the rest would have to use a set-top-box tuner/converter to receive a digital signal, and in both cases an antenna pointing to the digital stations would be needed.  <p>The idea is simple, a digital tuner could be within the DTV, or could be within a separate set-top-box (STB). The STB could be used for an analog TV but also for a digital TV that lacks a tuner (a monitor manufactured before the FCC's tuner integration mandate).  <p>However, a digital monitor that does not have tuning capabilities can only give its full potential when connected to a digital HD-STB that is capable to tune and send to the DTV the SD/ED/HD program at its full resolution, a feature that is not possible with a government coupon-program tuner/converter.  <p>Analog TVs can only display at 480i resolution. A government-coupon-program converter would tune to digital channels but would downconvert their resolution to 480i so the analog TV could display them. Analog TVs do not need to be replaced if that level of image quality is satisfactory enough for the viewer.  <p>Coupon-program converters do not output digital HD signals as fully capable HD-STBs do. While it is possible to use these coupon-converters to feed analog 480i to even a tuner-less monitor DTV, the DTV resolution capabilities would be under utilized when a high-resolution image is tuned and the coupon-program converter box reduces its quality to just 480i.  <p>An integrated DTV tuner would perform a similar function as a separate HD-STB regarding tuning HD, but it has the virtue of not having a separate box around the TV and also save the cost/inconvenience of additional wiring to the TV.  <p>The trade-off? If the internal tuner fails, performs badly, or becomes obsolete, the integrated DTV itself is subjected to the inconvenience/service, rather than just a box that can be independently serviced or replaced while the TV can still be used with another STB or for other purposes.  <p><b></b> <h2>Original Integrated Tuner Mandate </h2> <p>In 2002, the FCC issued a mandate for over-the-air tuners to be gradually included into every DTV manufactured after that date. On-the-clear QAM cable tuners (for unscrambled content) were also included into DTVs as part of an industry agreement made around that time.  <p>Beware of some recent erroneous and misleading claims of uninformed journalists, such as: "<i>many of these DTVs lack ATSC tuners; all DTVs weren't mandated to include tuners until last March.</i>"  <p>The claim is "just" 5 years off.  <p>Actually <a href="http://www.hdtvmagazine.com/articles/2006/01/hdtv_integrated_tuners_and_you.php">integrated DTVs</a> were gradually introduced with 2003 models following a five-year phased-in plan, which originally was issued as follows:  <ul> <li>50 percent of sets measuring 36 inches and larger by July 1, 2004; 100 percent by July 1, 2005.  <li>50 percent of sets measuring 25 inches to 35 inches were to add DTV tuners by July 1, 2005; 100 percent by July 1, 2006.  <li>The rest were to conform by July 1, 2007. </li></ul> <p>In 2005, that plan was modified with stricter deadlines and screen sizes.  <h4></h4> <h2>Updated Integrated Tuner Mandate </h2> <p>In mid 2005, the FCC made a tentative decision to change the deadline, and requested feedback from manufacturers, broadcasters, and industry trade groups.  <p>The decision was to accelerate the deadline of tuner integration for TVs under 25-inches to make them compliant 6 months earlier than planned, advancing the date from July 1, 2007 to December 31, 2006. The decision also included smaller sets under 13 inches, not included within the original mandate.  <p>Advancing the deadline was also viewed as a way to reduce the number of D/A converters that would be needed in the subsidy program when analog signals stop broadcasting.  <p>On their response, the CEA requested to eliminate the July 1, 2005 deadline that required manufacturers to make half of the 25/35-inches sets capable of receiving digital. The argument from manufacturers was that consumers would end up buying the cheaper analog sets, and retailers were less willing to order the more expensive digital sets. The FCC rejected that request.  <p>Walt Disney, the NAB, and the Association for Maximum Service Television (MSTV) urged the FCC to adopt the advanced deadline of December 31<sup>st</sup>, 2006, while the CEA, the CERC (Consumer Electronics Retailers' Association), Sharp Electronics, and Philips Electronics North America opposed to it, and claimed not having enough time to manufacture those DTVs by the end of 2006.  <p>The CEA and CERC issued the following statements: "<a name="OLE_LINK2"></a><a name="OLE_LINK1">the FCC should refrain from making any rulings regarding the inclusion of digital tuners in new </a>receivers with screen sizes less than 13 inches until manufacturers, retailers and the commission adequately are able to examine the impact of the small chassis products that currently are subject to the commission's tuner requirements." They opposed accelerating the timetable claiming that no evidence justified the change.  <p>Other comments from the CEA regarding the effect of accelerating the mandate on manufacturing and consumers were as follows:  <p>"Some manufacturers could opt to market monitor-only models that remove both digital and analog tuners, or stop manufacturing certain sets altogether. For smaller sets, 13 to 26 inches, the requirement would double the development costs for manufacturers, as well as double the price of a typical 13-inch television to consumers," the CEA said, and added: "If the product is rejected by lower income and other consumers because the price exceeds their budget, it will not be carried by retailers and, eventually, not produced by manufacturers."  <p>The CEA also said "the unfortunate result of accelerating the tuner mandate deadlines for all sets would be to decrease the number of DTV tuners in the marketplace, which clearly does not serve the transition."  <p>Finally, in November 2005, the FCC voted for setting the new date as March 1, 2007 for all sizes including those smaller than 13 inches, which received the support from the NAB taking into consideration how important they are in times of emergency and are commonly used without STBs.  <p>The FCC revised the deadline dates for DTVs to have integrated tuners as follows:</p> <table cellspacing="0" cellpadding="2" width="669" border="0"> <tbody> <tr> <td valign="top" width="139">Upon approval </td> <td valign="top" width="528">&gt;=36 inches </td></tr> <tr> <td valign="top" width="140">By March 1, 06 for </td> <td valign="top" width="528">&gt;= 25 inches (was July 1, 06 on the original plan) </td></tr> <tr> <td valign="top" width="141">By March 1, 07 for </td> <td valign="top" width="528">&gt;= 13 inches (was July 1, 07, and was agreed for March 07 although the FCC proposed it for December 31, 06) </td></tr> <tr> <td valign="top" width="141">By March 1, 07 for </td> <td valign="top" width="528">&lt; 13 inches (was not required before) </td></tr></tbody></table> <p>The mandate does not apply to other small screen video capable devices that do not receive analog OTA broadcasting, even when they might be used to watch TV shows, such as PDAs, mobile phones, iPODs, etc., but it applies to other non-screen devices that have analog tuners to perform their purpose, such as VCRs and DVD recorders.  <p>11.8 million DTVs were produced between 1998 and 2003 and most are tuner-less monitors that need an external STB tuner to view digital TV. Most of those DTVs also do not have DVI/HDMI digital connections with HDCP content protection; they only include component analog connections for HD.  <p>The lack of protected digital connectivity could render <a href="http://www.hdtvmagazine.com/articles/2008/06/high_definition_movies_before_they_hit_blu-ray_only_if_your_hdtv_permits_it.php">them incapable</a> of eventually receiving protected premium content from an external STB when connected with analog component cables, which is another wrinkle of the DTV transition that hit hard on the efforts early-adopters did to help establish DTV in the US.  <p>The majority of the remaining 69.3 million DTVs that were sold between 2004 and 2007 have an integrated tuner to comply with the mandate, although many of that period were still permitted to be tuner-less depending on their size. A large part of these sets may also have QAM cable TV tuners for unscrambled content, and many of those also have CableCARDS for premium content, although with only unidirectional capabilities.  <p><b></b> <p><b></b> <h2>Integrated Tuner Mandate Enforcement </h2> <p>In June 2007 Regent USA (Maxent) and Syntax-Brillian (Olevia) were issued "apparent liability for forfeiture" notices by the FCC for allegedly failing to comply with the ATSC tuner mandate on the DTVs they manufactured, imported or shipped, and the FCC added, under "willful and repeated violations".  <h5></h5> <p>Regent was fined $63,650 for importing or shipping 1,182 non-compliant DTV tuners. Syntax-Brillian was fined $2,899,575, for 22,069 DTVs imported or shipped within the statute of limitations, and the FCC commented "We believe that the proposed forfeiture reflects the gravity of Syntax-Brillian's apparent violations, the company's ability to pay, and the need to deter Syntax-Brillian and other companies from future violations of the act and the rules".  <h2>Tuner-less DTVs Even Under the Mandate</h2> <p>In 2007, Toshiba introduced new lines of HD flat-panels, rear-projection, and direct-view sets that excluded the mandated over-the-air digital tuner by not including the analog tuner on the sets, which then qualified them as valid "monitors."  <p>Toshiba was not alone in cutting down on tuning components. CableCARD-less TV lines started to appear from various manufacturers that found no merit in integrating a unidirectional cable tuner with CableCARDs into DTVs while the cable industry was moving toward a bi-directional OCAP solution.  <p>Toshiba announced the 2007 monitors to cost $300 less than comparable "integrated" TVs with mandated tuners.  <p>In perspective, when integrated DTVs were introduced in 2003 the difference between a monitor DTV and an integrated version of the same TV <a href="http://www.hdtvmagazine.com/articles/2006/01/hdtv_integrated_tuners_and_you.php">was $704 more on average</a>. Since 2003, millions of consumers have paid for integrated tuners they do not use because most subscribe to satellite or cable.  <p>Additionally, consumers could not know of the extra cost of the unneeded tuner because monitor-only versions of their integrated sets where no longer produced.  <p>"This is all the video display a consumer needs if they get programming from a cable or satellite TV box," Toshiba said.  <p>The next part (5) in this series will be dedicated to "<a href="/articles/2008/12/dtv_transition_-_can_you_help_part_5_was_tuner_integration_timed_right.php">Was Tuner Integration Timed Right?</a>" </p>
		</div>
	</div>
	<p class="posted">Posted by <b>Rodolfo La Maestra</b>, <b>November  3, 2008 09:42 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 1539
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
 			<h2>More on Digital (DTV) Transition</h2><ul><?
 			$sql = "
 			SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name
 			FROM mt_entry e, mt_author a, mt_placement p, mt_category c
 			WHERE entry_author_id = author_id
 				AND e.entry_id = p.placement_entry_id
 				AND p.placement_category_id = c.category_id
 				AND category_label = 'Digital (DTV) Transition'
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
 				AND entry_id <> 1539
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2008/11/dtv_transition_-_can_you_help_part_4_-_dtv_tuner_integration.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
