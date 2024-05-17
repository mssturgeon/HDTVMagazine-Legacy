<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');

	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1531";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);

	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Rodolfo La Maestra'");
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
	<meta name="keywords" content="million million, dtv transition, million dtvs, analog tvs, per household, million, digital, DTV, dtv, tvs, TVs, analog, DTVs, dtvs, cable, households, transition, CEA, cea, year, sets, number, ratio, household, years" />
	<meta name="description" content="Part 3 of the series has the objective of helping the reader get a general picture of the adoption of digital TVs; the growing of the DTV installed base, household coverage, the combined conditions of both to meet the deadline of the DTV Transition and a projection for the eventual replacement of the full inventory of analog TVs within the US.

Over recent years some of the figures tossed by..." />
	<title>HDTV Magazine Articles - DTV Transition - Can YOU Help? (Part 3) - TVs vs. Households</title>
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

		$base_url = strleftback(PHP_SELF, '/') . '/dtv_transition_-_can_you_help_part_3_-_tvs_vs_households';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('DTV Transition - Can YOU Help? (Part 3) - TVs vs. Households'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/articles/2008/10/dtv_transition_-_can_you_help_part_3_-_tvs_vs_households.php";
		if ($author[img] != '' && 1 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Rodolfo La Maestra" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">DTV Transition - Can YOU Help? (Part 3) - TVs vs. Households</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Rodolfo La Maestra</b><br />
				<?=$author_title?>
				Posted on <b>October 27, 2008</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Digital (DTV) Transition">Digital (DTV) Transition</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2008/10/dtv_transition_-_can_you_help_part_3_-_tvs_vs_households.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/articles/2008/10/dtv_transition_-_can_you_help_part_3_-_tvs_vs_households.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/articles/2008/10/dtv_transition_-_can_you_help_part_3_-_tvs_vs_households.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2008/10/dtv_transition_-_can_you_help_part_3_-_tvs_vs_households.php&amp;phase=2&amp;title=DTV%20Transition%20-%20Can%20YOU%20Help%3F%20%28Part%203%29%20-%20TVs%20vs.%20Households&amp;bodytext=Part%203%20of%20the%20series%20has%20the%20objective%20of%20helping%20the%20reader%20get%20a%20general%20picture%20of%20the%20adoption%20of%20digital%20TVs%3B%20the%20growing%20of%20the%20DTV%20installed%20base%2C%20household%20coverage%2C%20the%20combined%20conditions%20of%20both%20to%20meet%20the%20deadline%20of%20the%20DTV%20Transition%20and%20a%20projection%20for%20the%20eventual%20replacement%20of%20the%20full%20inventory%20of%20analog%20TVs%20within%20the%20US.%0A%0AOver%20recent%20years%20some%20of%20the%20figures%20tossed%20by...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
<ul>
<li><a href="/articles/2008/10/dtv_transition_-_can_you_help_part_1_-_transition_reception_and_help.php">DTV Transition - Can YOU Help? (Part 1) - Transition, Reception and Help</a></li>
<li><a href="/articles/2008/10/dtv_transition_-_can_you_help_part_2_-_a_technical_view.php">DTV Transition - Can YOU Help? (Part 2) - A Technical View</a></li>
<li><a href="/articles/2008/11/dtv_transition_-_can_you_help_part_4_-_dtv_tuner_integration.php">DTV Transition - Can YOU Help? (Part 4) - DTV Tuner Integration</a></li>
<li><a href="/articles/2008/12/dtv_transition_-_can_you_help_part_5_was_tuner_integration_timed_right.php">DTV Transition - Can YOU Help? (Part 5) - Was Tuner Integration Timed Right?</a></li>
<li><a href="/articles/2008/12/dtv_transition_-_can_you_help_part_6_-_subsidy_set-top-boxes.php">DTV Transition - Can YOU Help? (Part 6) - Subsidy Set-Top-Boxes</a></li>
</ul></div>
<br />
<p align="center"><b>TVs vs. Households</b></p> <p>Part 3 of the series has the objective of helping the reader get a general picture of the adoption of digital TVs; the growing of the DTV installed base, household coverage, the combined conditions of both to meet the deadline of the DTV Transition and a projection for the eventual replacement of the full inventory of analog TVs within the US.</p> <h2>Number of TVs vs. Households</h2> <p>Over recent years some of the figures tossed by the press mixed and loosely compared the number of households with the number of TV sets in the US, and the number of cable/satellite household subscribers with the number of analog, digital or HD set-top-boxes, for example.</p> <p>From that mix, deceptive percentages and ratios were derived and presented in articles made to sensationalize preconceived opinions for a journalistic profit, and many of those authors were not even related to the DTV industry.</p> <p>My objective is to help the public with factual and accurate information, and to provide an analysis without an agenda of profitability from the situation at hand, so let us get to work.</p> <p>Up until 2007 the Consumer Electronics Association (CEA) used an average ratio of 2.6 TV sets per household (289 million active/inactive TVs of any kind installed into 111 million US households). Now the average ratio is 3.1 and is calculated as 346 million active/inactive TVs within 112.8 million households.</p> <p>After discussing the subject with the CEA's Senior Director of Market Research, he confirmed my assumption that the increased ratio responds to consumers purchasing newer DTVs to experience digital, but not to immediately replace any old TV, which might still perform well for some secondary service in the home.</p> <p>This particular situation has the effect of making the overall inventory of TVs increase more than the simple effect of replacing analog by digital (289 to 346 million), and making the ratio higher when relative to an almost unchanged number of households (111 to 112.8 million).</p> <p>However, of those 346 million about 300 million TVs are actually active (in use), which actually makes the average ratio of "active" TVs per household to 2.6. In my discussion with the CEA we agreed that although the other 46 million inactive TVs might still be functional, they have been moved to other non-living areas of the house, such as the attic.</p> <p>According to information obtained by CEA, the Senior Director of Market Research of the CEA said, 13% of the sampled households reported to have 5 TVs in the house, and 35% of the sampled households reported to have 4 TVs. When those groups joined the remaining 52% of households of the sampling, the average of TVs per household is 3.1 on the most recent year of the research.</p> <p>Does the US need those 346 million TVs to be digital for the February 17, 2009 deadline to be met? No.</p> <p>Those TVs do not have to be all digital by then, and the reality is that they could not all be replaced for digital sets in such a short time either, and probably they will continue performing an analog service in a digital world for years to come.</p> <p>A total of 81.1 million digital TVs were sold between 1998, when the DTV transition started, and December 2007. It is projected that approximately a cumulative 116 million DTVs in total will be sold by the February 17, 2009 deadline.</p> <p>This means that the US would still have about 230 million analog TV sets (346-116=230) by the February 2007 deadline. How is this mix going to affect the public receiving the variety of TV services within the US, such as broadcast, cable, satellite, IPTV, etc?</p> <h2>The Set-Top-Box Comes to Help </h2> <p>After the deadline, any analog TV would require a set-top-box (STB) to tune to a digital signal whether they are connected to an antenna, cable, satellite, etc. Let us analyze how that would happen.</p> <p>Small dish satellite (DirecTV and Dish Network) is already 100% digital, and each satellite subscriber household must have at least one satellite digital STB already installed. Satellite subscribers are automatically ready for the digital transition regardless of the TV they have, and although HD is not a requirement for the digital service, the digital STB has to be HD capable to receive HD channels from the satellite provider.</p> <p>Some cable companies converted all of their subscribers to digital by replacing analog STBs by digital STBs when needed, and migrating their service to support only digital tiers. The complete switch to digital and the upfront investment in digital STBs can be an incentive to some companies when the bandwidth required by discontinued analog channels could be released and used for more digital channels or for more efficiently managed services that could generate more revenue in the long run.</p> <p>Other cable companies are gradually converting subscribers to digital tiers while temporarily continuing with analog tiers, under the flexibility given by the FCC to cable companies, by which they have until 2012 to switch to full digital.</p> <p>The flexibility also benefits many millions of analog subscribers that have their analog TVs directly connected to the wall-coax to tune basic analog channels without having to lease a cable set-top-box. </p> <p>In other words, regardless of whether the STBs are cable, satellite, or over-the-air, they would allow millions of analog TVs to still perform at their level of resolution (480i) regardless of the digital signal tuned by the STB.</p> <p>The vast majority of the remaining 230 million TVs mentioned earlier would probably not need to be replaced by DTVs any time soon as long as STBs are used, or analog tiers are continued by some cable companies until 2012.</p> <p>Although many integrated DTVs have internal digital QAM cable tuners the cable subscriber may still require a cable STB for certain services. The integrated tuners might even have Cable CARDs but the tuners are only uni-directional and do not support the cable supplied EPG (Electronic Program Guide), VOD (Video on Demand), and Impulse PPV (Pay-per-view) bi-directional cable features, so a cable STB is needed.</p> <p>This can be viewed as a duplicated investment for many DTV owners, who paid once for the integrated ATSC/cable tuners mandated by the FCC within the purchased DTV, and paid again when leasing the cable STB with bi-directional capabilities to support the features above.</p> <p>In other words, if the cable subscriber a) owns a DTV with QAM digital cable tuning capabilities w/o CableCARD and wants to tune to premium channels, or b) owns a DTV with a CableCARD for premium channels, but wants bi-directional services (VOD, etc), a digital cable STB will still be needed, which is now mandated by the FCC to have a CableCARD within it.</p> <h2>Number of DTVs Sold Since 1998</h2> <p>Although this article is not about reconciling numbers or counting beans for journalism purposes, the information below helps provide a sky-high perspective of where we are with the DTV transition.</p> <p>On each of my HDTV annual reports I analyze the progress of the DTV installed base by technology (plasma, LCD, DLP, etc), and provide a projection for the next several years. Below is a yearly summary of the DTVs sold to dealers since the beginning of the DTV transition. The first table immediately below shows figures sourced from the CEA as of July 31, 2008:</p> <table class="type1b"><tr><td class="type1b_header">Year</p></td> <td class="type1b_header">DTV Sets</td></tr> <tr> <td valign="top" width="57">2011</td> <td valign="top" width="580">40.8 million (cumulative end of year 2011, 228.7 million)</td></tr> <tr> <td valign="top" width="57">2010</td> <td valign="top" width="580">38.4 million</td></tr> <tr> <td valign="top" width="57">2009</td> <td valign="top" width="580">35.8 projected million</td></tr> <tr> <td valign="top" width="57">2008</td> <td valign="top" width="580">32.6 estimated million (cumulative end of year 2008, 113.7 million)</td></tr> <tr> <td valign="top" width="57">2007</td> <td valign="top" width="580">26.4 million (cumulative end of year 2007, 81.1 million)</td></tr> <tr> <td valign="top" width="57">2006</td> <td valign="top" width="580">23.5 million (cumulative end of year 2006, 54.7 million)</td></tr> <tr> <td valign="top" width="57">2005</td> <td valign="top" width="580">11.4 million</td></tr> <tr> <td valign="top" width="57">2004</td> <td valign="top" width="580">8 million</td></tr> <tr> <td valign="top" width="57">2003</td> <td valign="top" width="580">5.5 million</td></tr> <tr> <td valign="top" width="57">2002</td> <td valign="top" width="580">4.1 million</td></tr> <tr> <td valign="top" width="57">2001</td> <td valign="top" width="580">1.5 million</td></tr> <tr> <td valign="top" width="57">2000</td> <td valign="top" width="580">0.6 million</td></tr> <tr> <td valign="top" width="57">1999</td> <td valign="top" width="580">0.1 million</td></tr> <tr> <td valign="top" width="57">1998</td> <td valign="top" width="580">0.0 million</td></tr></tbody></table> <p>As you see from the above, a total of 81.1 million DTVs were confirmed as actually sold between 1998 and December 2007. Those 81.1 million DTVs are expected and are capable of replacing and performing the job as part of the whole inventory of 346 million TV sets available in the whole US.</p> <p>In summary, the installed base grew beyond a replacement purpose and has now a higher ratio (3.1) of TVs per household, the 81.1 million purchased DTVs are not necessarily replacing analog sets that will be inactive, but they make a household now DTV capable with at least one digital set.</p> <p>So it is obvious that quite a few more years will be needed before all legacy analog TVs can be actually replaced by digital sets, but the transition does not expect that all the TVs have to be replaced to be able to switch the analog NTSC system to digital, regardless of the date.</p> <p>For a long time to come there will be a mix of new digital TVs and old analog TVs that be eventually replaced, or in many cases not ever replaced, depending on their purpose and if they still perform well as display devices for the needed image.</p> <p>To have an estimate of an "until today" (July 2008) DTV-sold figure, one can take half of the 2008 projection above (16 million from the full year's 32.6 million) and add it to the 81.1 million figure of 1998-2007, making the total for the period 1998-1H08 to be about 97.1 million DTVs (81.1 + 16).</p> <p>However, it is customary to use actual annual figures only when the period is completed to have the opportunity to confirm or to revise the estimate for that year. The actual figure for 2008 would be available sometime in mid-2009.</p> <p>In the past, even actual figures published of earlier years, not just the estimate of the previous year, were subjected to further revision by the CEA to refine the count based on improved feedback from the CE industry. My annual reports include the CEA adjustments when they are made available, usually a few months after my reports are published for the year.</p> <p>The most accurate figures are in my 2007 report. The CEA figures mentioned in the table above are very similar to the ones I published in my 2007 report (below) with the information available at that time:</p> <table class="type1b"><tr><td class="type1b_header">Year</td> <td class="type1b_header">DTV sets (as I reported in the <a href="http://www.hdtvmagazine.com/reports/hdtv-technology-review.php">2007 HDTV Review</a>)</td></tr> <tr> <td valign="top" width="63">98-01</td> <td valign="top" width="516">1.4 million (revised now to 2.2 million by CEA in 2008 as above)</td></tr> <tr> <td valign="top" width="66">2002</td> <td valign="top" width="516">4.1 million (match, but also reported as 2.5 million in earlier reports)</td></tr> <tr> <td valign="top" width="68">2003</td> <td valign="top" width="516">5.5 million (match, but also reported as 4.1 million in earlier reports)</td></tr> <tr> <td valign="top" width="70">2004</td> <td valign="top" width="516">8.0 million (match, revised from 8.2 million reported in 2006)</td></tr> <tr> <td valign="top" width="71">2005</td> <td valign="top" width="516">11.3 million (revised to 11.4 million in 2008)</td></tr> <tr> <td valign="top" width="72">2006</td> <td valign="top" width="516">23.9 million (confirmed in 2008 as 23.5 million actual)</td></tr> <tr> <td valign="top" width="73">2007</td> <td valign="top" width="516">29.2 million (confirmed in 2008 as 26.4 million actual)</td></tr> <tr> <td valign="top" width="74">Total</td> <td valign="top" width="516"><b>83.4 million (Nov 98 - Dec 2007)</b> as I reported in 2007.<br><br>Confirmed now as <b>81.1</b> million actual in 2008 (in CEA table above)</td></tr></tbody></table> <p>In other words, a negative adjustment of about -2.3 million DTVs (from 83.4 to 81.1 million) was made for the 1998-2007 period, which is about 2.7% reduction over the figures provided on the 2007 report, mainly due to revising projected/estimated figures by actual figures, as it happens every year.</p> <h2>The Projections, and the Expected TV Replacement Behavior</h2> <p>Looking ahead over 3 years, CEA's estimate of 228.7 million DTVs sold by 2011 would still be short of replacing all the TVs in the US. If the 40 million per year rate for the single year 2011 projected by the CEA were consistently maintained for the years beyond 2011, an additional period of 7 years (counting from mid-2008) would be needed for the full replacement of analog TVs on the entire population (by 2014).</p> <p>But again, because many of the current analog TVs might still be useful for their purpose (video games, pre-recorded movies, external STB, etc) there should not be a rush in declaring them obsolete just because of a DTV transition deadline or because they are not digital.</p> <p>Additionally, the 346 million DTVs of today are a moving target inventory, when consumers buy new DTVs not necessarily to replace analog TVs and declare them inactive. The total number will grow and the ratio per household will grow as well perhaps for a few more years until a majority of households find no need to keep so many active and inactive sets in the home, regardless how many DTVs they buy.</p> <p>In retrospect, all B&amp;W TVs did not need to be replaced in a rush when color television arrived decades ago. Back then it was due to backward compatibility of the color system to a B&amp;W TV by design; now, it is thanks to a STB tuner/converter of digital-to-analog. Even today, some households might still have some of those B&amp;W sets around for some basic purpose, if they still work.</p> <p>People would naturally replace analog TVs as needed. Current DTVs would also be replaced for newer models as needed, and those replaced DTVs would probably be moved to other rooms in the house to perform other secondary tasks, replacing analog TVs that are doing that task, gradually shifting out old analog sets as new DTV sets come into the households.</p> <h2>Who Owns the 81.1 Million DTVs?</h2> <p>One item that I regularly analyze in my annual reviews is that the 81.1 million DTVs sold over the first 10 years of the DTV transition is not necessarily equivalent to a 1-to-1 installed ratio in a similar number of households (having one DTV at each home).</p> <p>Many early-adopters that bought DTVs since 1998 most likely have already purchased their second and even third DTV set for their homes during these first ten years of transition, making the actual number of households having DTV much smaller than the 81.1 million DTVs sold.</p> <p>The purchase pattern of early-adopters is usually driven by the satisfaction of experiencing technology challenges and innovations, having cost as a secondary factor, if at all. This is not the pattern of the budget oriented consumer electronics market, and certainly not the red-tag-sale weekend-ad consumers looking for the best deal at the right time for their pockets.</p> <p>By the end of 2007, Gary Shapiro, CEA president and CEO, declared, "I am proud to announce our nation has hit this digital milestone. With 50 percent of U.S. homes able to experience the reality of digital television, we have crossed a critical threshold."</p> <p>Although the statement from the CEA was an estimate, it seems to be quite accurate. 50% of 112 million households in the US = 56 million households. It is consistent with my analysis above of not following a 1-to-1 ratio for the first wave of DTV consumers.</p> <p>The estimate means that the 81.1 million DTVs sold are actually installed into 56 million households; many of those are early-adopters, making the sold-DTV average ratio as 1.5 DTVs per household.</p> <p>It is likely that the households of early-adopters that purchased most of the 81.1 million of DTVs until 2007 are the main reason of the 5 TV per household ratio mentioned earlier (13% of the sampling).</p> <h2>Final Thoughts on the DTV Adoption</h2> <p>In summary, the DTV market conditions and the ratio of DTV per household will gradually increase over the next few years because:</p> <p>a) DTV prices go further down and attract lower income groups, becoming more accessible to the remaining 56 million households,</p> <p>b) More households continue to acquire multiple DTVs without immediately disposing of current analog TVs,</p> <p>c) The total number of TVs in the US increases further beyond the actually used (active),</p> <p>d) That number is also a moving target that one should not necessarily expect to be fully replaced (such as the inactive 46 million out of the 346 in 2007 mentioned above),</p> <p>e) The analog shut-off will be a strong motivator when the event actually happens as planned in February 2009, and </p> <p>f) Blu-ray increases its footprint for the enjoyment of higher quality pre-recorded HD media, while consumers strive for larger 1080p screens, making the acquisition of an HDTV more appealing when able to realize the full potential of both technologies without having to resort to DVD upconversion/video processing. Blu-ray also brings a great opportunity to finally get "the real picture".</p> <p>Stay tuned for the next part (4) in this series, dedicated to Integrated DTVs.</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Rodolfo La Maestra</b>, <b>October 27, 2008 09:58 AM</b></p>

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
 				AND entry_id <> 1531
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2008/10/dtv_transition_-_can_you_help_part_3_-_tvs_vs_households.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
