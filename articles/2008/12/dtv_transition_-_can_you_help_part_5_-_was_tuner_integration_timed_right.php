<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');

	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1566";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);

	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Rodolfo La Maestra'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";

	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1566 AND placement_is_primary = 1";
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
	<meta name="keywords" content="directional cable, dtv transition, tuner integration, integrated dtv, integrated dtvs, DTV, dtv, cable, tuner, integrated, tuners, STB, stb, DTVs, dtvs, digital, stbs, STBs, analog, directional, satellite, integration, OTA, ota, transition" />
	<meta name="description" content="This part in the series discusses the implementation of tuner integration, its timing within the DTV Transition, and its impact on existing and future DTVs. I will talk about over-the-air (OTA) and cable reception, integrated tuners and set-top-boxes (STBs), and satellite STBs.

This is an analysis of the facts related to tuner integration as implemented over the past 6 years within the 10-year DTV transition, and how those facts affect consumers depending on the service they use (broadcast, cable, satellite, or Telco).

At closing I will also identify..." />
	<title>HDTV Magazine Articles - DTV Transition - Can YOU Help? (Part 5) - Was Tuner Integration Timed Right?</title>
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

		$base_url = strleftback(PHP_SELF, '/') . '/dtv_transition_-_can_you_help_part_5_-_was_tuner_integration_timed_right';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('DTV Transition - Can YOU Help? (Part 5) - Was Tuner Integration Timed Right?'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/articles/2008/12/dtv_transition_-_can_you_help_part_5_-_was_tuner_integration_timed_right.php";
		if ($author[img] != '' && 1 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Rodolfo La Maestra" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">DTV Transition - Can YOU Help? (Part 5) - Was Tuner Integration Timed Right?</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Rodolfo La Maestra</b><br />
				<?=$author_title?>
				Posted on <b>December 18, 2008</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Digital (DTV) Transition">Digital (DTV) Transition</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2008/12/dtv_transition_-_can_you_help_part_5_-_was_tuner_integration_timed_right.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/articles/2008/12/dtv_transition_-_can_you_help_part_5_-_was_tuner_integration_timed_right.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/articles/2008/12/dtv_transition_-_can_you_help_part_5_-_was_tuner_integration_timed_right.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2008/12/dtv_transition_-_can_you_help_part_5_-_was_tuner_integration_timed_right.php&amp;phase=2&amp;title=DTV%20Transition%20-%20Can%20YOU%20Help%3F%20%28Part%205%29%20-%20Was%20Tuner%20Integration%20Timed%20Right%3F&amp;bodytext=This%20part%20in%20the%20series%20discusses%20the%20implementation%20of%20tuner%20integration%2C%20its%20timing%20within%20the%20DTV%20Transition%2C%20and%20its%20impact%20on%20existing%20and%20future%20DTVs.%20I%20will%20talk%20about%20over-the-air%20%28OTA%29%20and%20cable%20reception%2C%20integrated%20tuners%20and%20set-top-boxes%20%28STBs%29%2C%20and%20satellite%20STBs.%0A%0AThis%20is%20an%20analysis%20of%20the%20facts%20related%20to%20tuner%20integration%20as%20implemented%20over%20the%20past%206%20years%20within%20the%2010-year%20DTV%20transition%2C%20and%20how%20those%20facts%20affect%20consumers%20depending%20on%20the%20service%20they%20use%20%28broadcast%2C%20cable%2C%20satellite%2C%20or%20Telco%29.%0A%0AAt%20closing%20I%20will%20also%20identify...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
<li><a href="/articles/2008/11/dtv_transition_-_can_you_help_part_4_-_dtv_tuner_integration.php">DTV Transition - Can YOU Help? (Part 4) - DTV Tuner Integration</a></li>
<li><a href="/articles/2008/12/dtv_transition_-_can_you_help_part_6_-_subsidy_set-top-boxes.php">DTV Transition - Can YOU Help? (Part 6) - Subsidy Set-Top-Boxes</a></li>
</ul><p>Please note that we have included references to external websites within this article. These are included for research and information purposes only and should not be interpreted as an endorsement of their products and/or services.</p></div>
<br />
<p align="center"><b>Part 5 - Was Tuner Integration Timed Right?</b> <p>This part in the series discusses the implementation of tuner integration, its timing within the DTV Transition, and its impact on existing and future DTVs. I will talk about over-the-air (OTA) and cable reception, integrated tuners and set-top-boxes (STBs), and satellite STBs.  <p>Let me be clear on one point right up front: This article is neither an endorsement nor a criticism of a mandate that is now history. This is an analysis of the facts related to tuner integration as implemented over the past 6 years within the 10-year DTV transition, and how those facts affect consumers depending on the service they use (broadcast, cable, satellite, or Telco).  <p>At closing I will also identify the available options of DTVs and STBs so you can decide what is best for your particular tuning situation. Hopefully you could help others with this as well, as that is the primary purpose of this series of articles.  <h2>Why Integrated Tuners?</h2> <p>Since its inception in the mid 1900s, TV traditionally performed a tune-and-display role in a world of broadcast-only tuning. In time, cable and satellite came along and brought an alternative to broadcast content distribution, but they required a different tuner.  <p>This gave birth to the STB approach, a tuner outside the TV. Later, analog cable tuners were incorporated into cable-ready analog TVs to tune to unscrambled content.  <p>When premium content (e.g. HBO) arrived, in order to protect the investment and the effort of creating the content, service providers implemented security controls under a pay distribution model to unscramble premium content, which required an STB even when the analog TV was cable-ready.  <p>When turning the page from analog to digital, the video content distribution model grew with more features but also with more complexity for equipment and connectivity, with CableCARDs, digital/analog conversions, image resolution controls, integrated digital DVRs, selectable output controls for content protection, digital audio and video connections, etc.  <p>The complexity certainly affected user friendliness in millions of households that were already accustomed to live with blinking 12:00 VCRs. Incidentally, JVC (the creator of VHS) just announced the end of the manufacturing of single VCRs units.  <p><a name="OLE_LINK1"></a> <p>The idea of integrating tuners into DTVs responds to the same tune-and-display concept of decades of broadcast TV, however, the timing of applying the same concept to DTV is a subject that looks simpler than it is.  <p>Factors like cost, maturity, reliability, upgradeability, and serviceability of a digital tuner were not at their prime in 2002, enough to discourage integration at that time.  <p>However, the alternative of not-integrating then and wait for the best timing of the combination of all of those factors might not have helped the transition and could potentially expand the risk beyond the effort of coupon-program converters to help 15 million households with analog TVs not go dark on February 2009.  <p>Why beyond? Because not integrating could have instead meant having 100+ million <b>tuner-less DTV monitors</b> installed in more than <a href="http://www.hdtvmagazine.com/articles/2008/10/dtv_transition_-_can_you_help_part_3_-_tvs_vs_households.php">56 million households</a> (about 50% of US households) by the transition deadline, and many millions of those risking going dark if used for broadcast and have no tuner.  <p>Although they could be connected to HD-STBs to avoid going dark, HD-STBs are <a href="http://www.solidsignal.com/cat_display.asp?main_cat=03&amp;CAT=HDTV%20Receivers">3 times more expensive</a> than <a href="http://www.solidsignal.com/cat_display.asp?main_cat=03&amp;CAT=Digital%20Converter%20Boxes">coupon-program digital-to-analog converters</a>, and are not subsidized by the Government coupon-program as converters for analog TVs are.  <p>I will mention a few more factors surrounding this matter later in this article.  <h2>Integrating Cable</h2> <p>As mentioned in <a href="http://www.hdtvmagazine.com/articles/2008/11/dtv_transition_-_can_you_help_part_4_-_dtv_tuner_integration.php">part 4</a>, integrated DTVs have been gradually manufactured with internal over-the-air (OTA) tuners to comply with the FCC's mandate proposed in 2002.  <p>At that time an agreement was made with the cable industry for DTVs to also include a QAM digital cable tuner for on-the-clear unscrambled programming; some DTVs also included a CableCARD slot for the QAM cable tuner to be able to tune scrambled premium programming (e.g. HBO) without using a cable set-top-box (STB).  <p>While digital cable STBs are bi-directional to permit Video-on-Demand (VOD), Impulse Pay-Per-View (PPV), and cable supplied Electronic Program Guide (EPG), the cable tuners integrated within DTVs are only uni-directional and cannot perform those services.  <p>More details about cable integration are included further below.  <h2>(Dis)Integrating Satellite </h2> <p>Although digital cable tuners were integrated into DTVs, small-dish-satellite tuners were not, except for two Thomson/RCA CRT DTVs with DirecTV tuners manufactured in the late 90s, a 38" Direct-view and a 61" rear-projection set.  <p>Our own <a href="http://www.hdtvmagazine.com/about/contact.php?name=milbourn">Edward Milbourn</a>, a Thomson/RCA manager at that time, participated in the introduction of those and in the creation of the DTC-100, the first DirecTV HD-STB.  <p>The DTC-100 was one of the most reliable satellite HD-STBs ever created. It was a workhorse that even received occasional firmware upgrades though the dish to improve its functionality and performance, a normal feature now, but visualize that almost a decade ago. I still keep my DTC-100 as a symbol of the beginnings of small-satellite HDTV.  <p>In Ed's words "the DTC-100 satellite HD-STB, the 61" DTV, and the 38" DTV, in that order, were introduced over a six month period of time because of the time required to obtain DirecTV certification (the hardest part of the project)."  <p>There are no plans disclosed to the public to integrate satellite tuners within DTVs, so get accustomed to having a perennial HD-STB solution, HD wires, A/V rack space, the need for managing multiple HD inputs, eventual selectable output controls for protected content when connected with component analog connections (a problem for DTV early adopters), and the mood of HDCP, DVI and HDMI in some equipment.  <p>The same picture applies to digital cable STBs now, but help is on its way with Tru2way DTV integration, more on that later.  <h2>Integrate All Tuners?</h2> <p>Traditionally, cable STBs do not include satellite tuners and vice versa, but that should not surprise anyone, they are in direct competition for subscribers.  <p>Cable-company supplied STBs usually do not include over-the-air tuners for free broadcast TV, although some cable DVRs and combo STBs for PC networking have OTA tuners.  <p>Satellite HD-STBs from Dish Network, such as the ViP722, include OTA tuners (analog and digital), however, while DirecTV used to include OTA tuners on their HD-STBs, the <a href="http://www.solidsignal.com/prod_display.asp?PROD=H21" target="_blank">most recent DirecTV STB models</a> do not, which can be solved by adding <a href="http://www.solidsignal.com/prod_display.asp?PROD=AM21">another STB </a>between the antenna and the satellite tuner. This STB provides the missing broadcast tuning functionality.  <p><a href="http://www.solidsignal.com/cat_display.asp?main_cat=03&amp;CAT=HDTV%20Receivers">Over-the-air STBs</a> for broadcast do not include any other tuners.  <p>Telecom companies and <a href="http://www.hdtvmagazine.com/articles/2007/09/iptv_part_1_-_read_the_fine_print.php">IPTV (Internet Protocol Television)</a> are also competing for TV subscribers and use STBs.  <p>In other words, no STB has multiple tuners for all possible services into a single cabinet, nor is it viewed that a DTV should have all of them integrated, because consumers usually select only one of those services to receive TV (broadcast, cable, satellite, or Telco), and considering the price of tuners, why would a consumer be interested in paying for all of those tuners within a DTV or within a universal STB?  <p>Therefore, a consumer should expect to have multiple STBs on the audio/video rack if all of those services are wanted, even when having a DTV that is already tuner-integrated with OTA and cable.  <p>The cost of internal tuner parts installed into a DTV should be lower than the price of an STB because a DTV does not need the STB cabinet, front panel, buttons, rear connections, power supply, remote control, STB assembly labor, etc., but the reality is that after 10 years of DTV transition, the price consumers pay for tuner integration has not come down as low as one might expect (details later in the article).  <p>Every service provider company has introductory packages for new subscribers, but individually purchased HD-STBs/DVRs for <a href="http://www.solidsignal.com/prod_display.asp?prod=TCD653080">cable</a>, <a href="http://www.solidsignal.com/cat_display.asp?main_cat=02&amp;CAT=DIRECTV%20Receivers">DirecTV</a>, <a href="http://www.solidsignal.com/cat_display.asp?main_cat=02&amp;CAT=DISH%20Network%20Receivers">Dish Network</a>, and <a href="http://www.solidsignal.com/cat_display.asp?main_cat=03&amp;CAT=HDTV%20Receivers">OTA</a> are not as low as it should be expected.  <p><b></b> <h2>The Benefit of Monitors </h2> <p>Before the integrated-tuner idea was proposed by the FCC in 2002, digital televisions manufactured since 1998 were monitors without digital tuning capabilities.  <p>The monitor DTVs need an HD-STB to tune over-the-air broadcast when connected to a UHF/VHF antenna. Later, HD-STBs for satellite reception (DirecTV and Dish Network) and for digital cable were introduced. The HD-STBs require component analog or DVI/HDMI wires for HD signals.  <p>The monitor approach provided several benefits to consumers, especially early in the transition, when DTVs and STBs were very pricey and immature. It was common to recommend a separation between the STB and the DTV monitor, at least until tuners cost less and become more reliable to be part of an expensive TV set.  <p>In perspective, a 1999 Pioneer Elite OTA tuner ($3000) was connected to a 64" CRT rear-projection HDTV Pioneer Elite monitor (close to $10,000). Should the two have been integrated back then a $13,000 300-pound DTV might have forced a costly home service if the internal tuner failed.  <p>I recall my comment on my 2003 <a href="http://www.hdtvmagazine.com/reports/hdtv-technology-review.php">HDTV Technology Review</a> "Over the last five years we have experienced all kinds of early adoption let downs regarding HD-STBs. Noisy fans, hot units, slow/unfriendly menus, weird software behavior, frozen units, dead units, killed units on firmware upgrades, etc."  <p>During the first couple of years of the integration mandate, the cost of integrated DTVs was considerably higher than the monitor versions. Not only were the added tuner-parts expensive and low in volume, but there were risk factors about tuner's performance, serviceability, failure, obsolescence, etc., whereas a troubled tuner in a separate HD-STB would not compromise and inconvenience the larger DTV investment, in fact it could be as easy as asking the service provider for a new upgraded unit at no cost.  <p><b></b> <h2>An Integrated DTV Looking for an HD-STB </h2> <p>Regarding tuner's performance and obsolescence, in a recent <a href="http://www.hdtvmagazine.com/forum/viewtopic.php?p=34793">thread</a> (29 Oct 2008 01:19 pm) in response to <a href="http://www.hdtvmagazine.com/articles/2008/10/dtv_transition_-_can_you_help_part_3_-_tvs_vs_households.php">Part 3 </a>of this series of articles a reader was surprised when a low-cost coupon-program tuner was sensitive enough to pick up 40+ DTV stations, better than the integrated tuner of his high quality new Sony DTV using the same over-the-air antenna.  <p>Enticed by the experience, he was looking for an HD-STB that would be as sensitive as the coupon-program-tuner, and use the HD-STB to do the tuning for his Sony DTV, rather than having the DTV do the tuning.  <p>Ironically, adding an STB to an integrated DTV defeats the basic concept of integration, but if having a separate box and cabling is not an issue it could be worth a try. Modularity generally offers better flexibility, upgradeability, serviceability, and replace-ability.  <p>Although new generations of tuners are expected to perform better, it is a mixed bag considering the numerous choices, prices, and quality among dozens of STB and DTV manufacturers.  <h2>Cable Impact on Integrated DTVs </h2> <p><b></b> <p>Since the mandate, consumers have purchased 107.4 million DTVs (from 2003 to 4Q08, <a href="http://www.hdtvmagazine.com/articles/2008/10/dtv_transition_-_can_you_help_part_3.php">refer to Part 3 of this series</a>). Although some of those are monitors, most are integrated DTVs.  <p>As mentioned earlier, because QAM cable tuners in integrated DTVs were only implemented with uni-directional capabilities, millions of DTV owners had to lease or purchase a cable HD-STB for bi-directional functionality, an additional expense to their "tuner-ready" integrated DTV.  <p>Although tuner integration is also expected to eventually support bi-directional cable tuners within DTVs, it has only reached a mid-point solution.  <p>Panasonic <a href="http://www2.panasonic.com/webapp/wcs/stores/servlet/prModelDetail?storeId=11301&amp;catalogId=13251&amp;itemId=304735&amp;modelNo=Content10152008035707436&amp;surfModel=Content10152008035707436">just announced</a> the introduction by fall 2008 of a couple of integrated DTV models with bi-directional cable tuners (facilitated by a platform named <a href="http://www.tru2way.com/">tru2way</a>), but the industry keeps implementing millions of uni-directional cable tuners within most cable-ready DTVs, and the impact to consumers will grow until all models from all DTV manufacturers are tru2way capable.  <p>Visualize this analogy for a minute: imagine that since 2002 people can only buy cars having a forward-only transmission (uni-directional tuners in DTVs) that cannot be modified or upgraded for reverse. Conversely, imagine if rent-a-cars were built capable to drive in both forward and reverse (bi-directional cable features in STBs). The days you need to park in reverse you better lease a rent-a-car (cable STB) and leave your car (integrated DTV tuner) in the garage.  <p>Here is a short quiz: As a car (DTV) owner, what would be your overall cost to be able to drive in both directions?  <p>How long would you think the car sale industry could have lasted under those rules?  <p>When the tuner integration mandate idea started in 2002 the expectation was that it might take another couple of years for bi-directionally to be into DTV integrated cable tuners, so it was agreed to start installing uni-directional cable tuners to move on. We are almost in 2009 and are still waiting.  <p>Assuming it could take another couple of years before we see tru2way fully deployed by all in the industry, another<a href="http://www.hdtvmagazine.com/articles/2008/10/dtv_transition_-_can_you_help_part_3.php"> 74.2 million DTVs</a> are projected to be in the hands of consumers in 2009 and 2010, their cable tuners would be only uni-directional.  <p>Adding 74.2 million DTVs to the 107.4 million DTVs that were already sold until 2008 makes a total of 181.6 million integrated DTVs estimated by the end of 2010, which is about 52% of the existing 346 million TVs in the whole US (analog and digital). Over the past 5 years I predicted the above scenario in every <a href="http://www.hdtvmagazine.com/reports/hdtv-technology-review.php">HDTV Technology annual report</a>.  <p>However, the alternative of waiting until a bi-directional solution was created, embraced by the industry, and integrated in volume was discouraged because it was apparently less favorable to the DTV transition and the consumer. Some related factors are mentioned below.  <h2>Was Tuner Integration Timed Right?</h2> <p>Again, this article is neither an endorsement nor a criticism of a mandate that is now history, but rather it is an analysis after 6 years of facts.  <p>In theory, the concept of tuner integration should have spared a viewer of DTV broadcast from the need to purchase an external OTA digital STB, but because only a minority tunes to OTA broadcast, it meant that a majority of DTV owners still needed to purchase or lease an STB (satellite, bi-directional cable, FiOS, etc).  <p>The driver of the tuning consumer decision was the preference of the content distribution service, but what if the cost factor of monitor vs. integrated was included in the analysis?  <p>When the mandate was issued, integrated DTVs cost consumers <a href="http://www.hdtvmagazine.com/articles/2006/01/hdtv_integrated_tuners_and_you.php">$704 more </a>on average compared to their tuner-less monitor versions (2003/2004 models). Over the past few years several manufactures (Westinghouse, Hitachi, etc.) introduced a few compliant monitors (because they also lacked analog tuners), but most if not all 2008 integrated DTVs from most manufacturers have no monitor versions of the same set, therefore I cannot make an industry wide comparison study as I did in 2003.  <p>However, in 2007, one major manufacturer announced monitors that cost $300 less than their similar integrated versions, and in September 2008 at CEDIA, Bob Perry, Senior VP of Panasonic, said that the new "tru2way" bi-directional cable integrated DTVs (mentioned earlier for fall 2008) will be priced $300 more than their non-tru2way counterparts. This only compares integrated uni-directional to integrated bi-directional cable DTVs. The price difference to tuner-less monitors should obviously be higher, if available.  <p>In summary, even after 6 years of the issued mandate, the price to consumers for tuner integration is not as negligible as OTA/cable analog tuners were for NTSC color television. Some say that is the price of innovation.  <p>On the other hand, if the integration mandate/cable agreement would have been implemented only when matured tuners reached a negligible cost to consumers, the delay could have affected other factors, and could have potentially damaged the success and the timing of the overall transition.  <p>Leaving aside how you receive content and which are your personal preferences, how could you evaluate if the 2002 decision was timed right for the overall public?  <p>Many issues merge together to make the decision more complex than it looks on the surface. I will mention just a few factors to consider:  <p>a) If waiting too long for integration, the fast grown DTV installed base (107.4 million until 2008) could have been made of 100% tuner-less monitors, one third of the US TV inventory (346 million DTVs). Some may consider that enough reason not to wait,  <p>b) From another view, the above could have been a positive cost factor for consumers if the monitors were installed in households that did not need broadcast tuners (the vast majority). The larger that group, the lower the overall cost, regardless who pays for it,  <p>c) The high cost of OTA tuners back in 2002. The uncertainty of when economies of scale will kick-in for reliable tuners in large volume,  <p>d) The need for sufficient volume of matured and reliable tuners for the millions of DTVs produced since 2003,  <p>e) The risk of waiting for tuner maturity, upgradeability, proper functionality, and reliability, to provide enough confidence for the tuner to be a part of a large DTV investment,  <p>f) The lack of cable industry readiness within the DTV transition in regard to hardware, software, content protection, CableCARD, uni/bi-directionality,  <p>g) The timing of designing and implementing bi-directional hardware/software solutions for cable STBs and integrated tuners should have been earlier on the DTV development phase (pre-1998), rather than waiting a few years after 1998 to show interest in HDTV, and wait until the integrated-tuner mandate to just implement a half-way uni-directional cable solution.  <p>h) Work on content protection and digital connectivity could have started much earlier in the pre-DTV transition phase. Both affect HD premium content distribution for cable and satellite subscribers, not to mention 11.8 million HDTVs of early adopters with component analog connections (pre DVI/HDMI).  <p>i) The over-the-air tuner mandate was for 100% of the DTVs; only <a href="http://www.hdtvmagazine.com/articles/2008/10/dtv_transition_-_can_you_help_part_1.php">15 million households</a> are estimated to receive broadcast, which is about 13.3% of the total 112.8 households in the US,  <p>j) The remaining 86.7% of households subscribe to satellite/cable/Telco. As mentioned in <a href="http://www.hdtvmagazine.com/articles/2008/10/dtv_transition_-_can_you_help_part_3.php">Part 3 of this series of articles</a> a few of those may have secondary DTVs that are connected to antennas, for which an integrated OTA tuner could be useful. On the other hand, a lower cost monitor and an HD-STB could have made the overall cost of ownership lower, even today,  <p>k) The 86.7% of satellite/cable/Telco subscriber household footprint is large enough (97.8 million households) to justify offering a monitor option, letting consumers decide what to buy depending on tuning needs,  <p>l) The content distribution model in the 21<sup>st</sup> century is more competitive (cable, satellite, broadcast, FiOS, etc.) compared to the broadcast-only NTSC analog beginnings in the mid 1900s, which required an OTA tuner for all TVs,  <p>m) If tuner integration were decided to be optional, having monitors and integrated models for similar DTVs would have added complexity to the manufacturing, inventory management, distribution, dealer showroom, sale, service, warranty, and parts.  <h2>Solving your Specific Tuning </h2> <p>a) <u>If you are a satellite (or Telco TV) subscriber</u> and:  <p>a.1) Tune local channels with the STB: buy a monitor if available, there is no need for the integrated OTA tuner within the DTV.  <p>a.2) Tune local channels with an OTA antenna: buy an integrated DTV and use its internal OTA tuner, or buy a DTV monitor with an <a href="http://www.solidsignal.com/cat_display.asp?main_cat=03&amp;CAT=HDTV%20Receivers">over-the-air HD-STB</a> (not a <a href="http://www.solidsignal.com/cat_display.asp?main_cat=03&amp;CAT=Digital%20Converter%20Boxes">coupon-program converter STB</a>, which is only 480i analog/SD resolution quality).  <p>In both cases the STB has to be connected to the DTV with component or DVI/HDMI digital video connections to view HD.  <p>b) <u>If you view broadcast TV</u> and:  <p>b.1) Are not ready to buy a DTV: you can still use a current analog TV with a <a href="http://www.solidsignal.com/cat_display.asp?main_cat=03&amp;CAT=Digital%20Converter%20Boxes">coupon-program converter</a>. <a href="https://www.dtv2009.gov/">Request the $40 coupon</a> and pay for the difference, if any. Connect the converter to your analog TV using the RF, composite, or S-video connections, which are limited to the TV's 480i resolution. There is no need for the higher quality component or DVI/HDMI digital video cables.  <p>If you need more than two coupon-program converters for additional analog TVs you would have to pay the full price of each extra <a href="http://www.solidsignal.com/cat_display.asp?main_cat=03&amp;CAT=Digital%20Converter%20Boxes">coupon-program converter</a>, there is no government subsidy for extra converters beyond the allowed two.  <p>b.2) Are ready and want to view DTV: buy an integrated DTV, it should have the needed OTA digital (and analog) tuner, otherwise, if the DTV is not integrated, you need to purchase an <a href="http://www.solidsignal.com/cat_display.asp?main_cat=03&amp;CAT=HDTV%20Receivers">over-the-air HD-STB</a> because the DTV is a tuner-less monitor: <p>b.2.1) Manufactured before the mandated deadline for the screen size (mentioned in <a href="http://www.hdtvmagazine.com/articles/2008/11/dtv_transition_-_can_you_help_part_4_-_dtv_tuner_integration.php">Part 4</a>), or  <p>b.2.2) Was recently manufactured but is one of the monitor exceptions mentioned earlier in this article.  <p>c) <u>If you are a cable subscriber</u> and:  <p>c.1) Do not need DTV quality for your image requirements: you can keep using your existing analog TV. If the cable service is offered only in digital lease or buy a digital cable STB. If the cable company still sends the analog feed connect the TV directly to the wall RF coax plate to tune to basic programming, or lease an analog cable STB if you want premium services. Refer to (a) for local channels.  <p>c.2) Need to view DTV but do not need bi-directional cable functionality:  <p>c.2.1) Buy a cable-ready integrated DTV with a QAM on-the-clear tuner for non-premium channels; connect the coax wire directly to the DTV.  <p>c.2.2) Buy a cable-ready integrated DTV with CableCARD for premium channels; connect the coax wire directly to the DTV.  <p>c.2.3) Buy a DTV monitor and connect to a digital cable STB (even when you do not need bi-directional cable functionality). The STB has to be connected to the DTV with component or DVI/HDMI digital video connections to view HD.  <p>Refer to (a) for local channels.  <p>c.3) Need to view DTV, want bi-directional cable functionality but cannot wait for a tru2way integrated DTV: buy a monitor DTV (or integrated DTV if none is available) and lease an HD-STB/DVR from the cable company, or purchase a <a href="http://www.solidsignal.com/prod_display.asp?prod=TCD653080">TIVO DVR</a>. Refer to (a) for local channels.  <p>c.4) Need to view DTV, want bi-directional cable functionality and a tru2way DTV is available: buy it, it should have all you need for bi-directional cable functionality without resorting to digital cable STBs and extra wiring. Refer to (a) for local channels.  <p>Stay tuned for the next part (6) in this series, dealing with Subsidy Set-top-boxes</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Rodolfo La Maestra</b>, <b>December 18, 2008 09:07 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 1566
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
 				AND entry_id <> 1566
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2008/12/dtv_transition_-_can_you_help_part_5_-_was_tuner_integration_timed_right.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
