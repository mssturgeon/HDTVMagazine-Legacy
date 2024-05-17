<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 66";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Dale Cripps'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 66 AND placement_is_primary = 1";
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
	<meta name="keywords" content="advisory committee, new format, land mobile, advanced television, high definition, HDTV, hdtv, format, FCC, fcc, industry, cable, satellite, formats, new, spectrum, years, video, could, sets, broadcasting, fiber, committee, television, consensus" />
	<meta name="description" content="This article is from Dr. Jeffry Krauss and was written back in 1988. You will see the extraordinary foresight from this noted author. First published in HDTV World Review. HDTV: It's Going To Be Great...Or Is It? Dr. Jeffrey Krauss..." />
	<title>HDTV Magazine Archive &amp; History - HDTV: It's Going To Be Great...Or Is It?</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_its_going_to_be_greator_is_it';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('HDTV: It\'s Going To Be Great...Or Is It?'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/history/2005/06/hdtv_its_going_to_be_greator_is_it.php";
		if ($author[img] != '' && 5 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Dale Cripps" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV: It's Going To Be Great...Or Is It?</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Dale Cripps</b><br />
				<?=$author_title?>
				Posted on <b>June  9, 2005</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category="></a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/2005/06/hdtv_its_going_to_be_greator_is_it.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/history/2005/06/hdtv_its_going_to_be_greator_is_it.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/history/2005/06/hdtv_its_going_to_be_greator_is_it.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/2005/06/hdtv_its_going_to_be_greator_is_it.php&amp;phase=2&amp;title=HDTV%3A%20It%27s%20Going%20To%20Be%20Great...Or%20Is%20It%3F&amp;bodytext=This%20article%20is%20from%20Dr.%20Jeffry%20Krauss%20and%20was%20written%20back%20in%201988.%20You%20will%20see%20the%20extraordinary%20foresight%20from%20this%20noted%20author.%20First%20published%20in%20HDTV%20World%20Review.%20HDTV%3A%20It%27s%20Going%20To%20Be%20Great...Or%20Is%20It%3F%20Dr.%20Jeffrey%20Krauss...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p><em>This article is from Dr. Jeffry Krauss and was written back in 1988. You will see the extraordinary foresight from this noted author. First published in HDTV World Review.</em> </p>

<p><strong>HDTV: It's Going To Be Great...Or Is It?</strong><br />
Dr. Jeffrey Krauss</p>

<p>Over the past decade, a number of electronics products have migrated from the business environment to the consumer environment. Answering machines, large screen TVs and cellular phones are examples of products that gained acceptance in the consumer environment as their prices fell.</p>

<p>In the U.S. today, almost everybody has an answering machine at home. The next product to migrate from office to home will be the fax machine. Fax machines have become popular in Japanese homes, but the price is still too high in the U.S. Now that cellular telephones cost less than $500, there are growing numbers of families that have cellular telephones in two cars. Five years ago, most 26" or 27" TV sets were used for the business or industrial environment. Today, most new TV set purchases are in the larger screen sizes, because the price of this item has fallen below $500.</p>

<p>The market penetration of large screen TVs is one of the forces driving high definition television. With the big screen, the imperfections of our current TV format become more noticeable. For the first time since the introduction of color TV in 1953, consumers are demanding better picture quality.</p>

<p>HDTV Critical Factors</p>

<p>How successful will the next generation of television be? The answer is complicated and involves consumer, economic and technical considerations.</p>

<p>Only one thing is clear. Whichever HDTV format is chosen for the U.S., it will be a system that is unique to the U.S. Other countries will be adopting advanced television formats for satellite broadcasting. Only the United States is planning to adopt the new format for terrestrial television broadcasting. Only the United States will have a television distribution system that can deliver advanced television by terrestrial broadcasting, cable TV, satellite TV and optical fiber. And only the United States will have a system that is backward-compatible, in the same way that color TV was backward-compatible with black-and-white, so that consumers can continue to use their current TV sets.</p>

<p>For the consumer, there are two major questions during the first few years. Will the new format be good enough to justify the cost of a $3000 TV receiver? And how long will it take until there is enough programming produced in the new format to justify the cost? These are the same questions that were raised when color TV was introduced in the 1950s. Even though everyone agrees that HDTV is the most important advance in TV since color, some industry executives think the shift to HDTV will be far less significant than the shift from black-and-white to color.</p>

<p>For the economists, estimating market penetration for HDTV receivers is important and has already begun. Most estimates assume a 1% penetration eight years after the 1993 or 1994 start of HDTV broadcasting. One group of economists think that HDTV will achieve market penetration far more slowly than color TV did. They estimate a 20% penetration 10 years after the 1% penetration. By contrast, VCRs had a 20% penetration eight years after reaching 1%, and color TV had a 20% penetration only six years after reaching 1%. Other economists are more optimistic, of course. It is clear, however, that market penetration rate depends strongly on the cost of the consumer TV receiver.</p>

<p>On the technical side, there are many unresolved issues. One element of the debate centers around whether the new format will be "high definition television" or merely "advanced television." True high definition displays 1125 scan lines of video. In contrast, today's TV format displays only 525 lines. In addition, HDTV has a wider screen, so the display will be shaped more like the screen in a movie theater. The goal of HDTV is to have a TV format that is technically as good as 35mm movie film, with compact disk-quality audio. Advanced television (ATV) is anything that is better than our current TV system, but less than high definition.</p>

<p>Today's TV format uses radio channels of six MHz bandwidth. HDTV would need about 30 MHz per channel, unless the signal is compressed. Each of the formats that has been proposed involves compression, squeezing information that comfortably fits a 30 MHz channel into a channel of 12 or nine or even six MHz. In achieving compression, some of the sharpness may be lost. All the proposed formats have more picture scan lines than our current 525-line standard, but they don't all have 1125 lines. Some argue that 787 lines is enough, others argue for 1050 lines.</p>

<p>At least one proponent, Zenith, has said that the wider screen will make TV sets far more expensive than necessary, and the only goal should be improved sharpness. Others, however, say that consumers place a higher value on the wider screen than the improved sharpness. How much picture improvement is necessary to satisfy consumer demand? The technical experts can determine how much radio spectrum is needed to reach certain levels of improvement, but only the marketplace can determine whether the improvement is enough to justify the higher receiver costs.</p>

<p>Road Map to a Decision</p>

<p>How will we get to the next generation of TV? The FCC, which has the legal responsibility to make the decisions, has established an industry advisory committee to evaluate and test candidate HDTV formats and recommend which one should be chosen as the U.S. standard. The committee, with its various subcommittees and working parties, includes several hundred experts. There are numerous meetings every month. The FCC staff plays a minimal role in the activities of the advisory committee. Fifteen to 20 advanced television proposals were submitted to the advisory committee in September, 1988.</p>

<p>The next important step is testing of hardware. The proponents will have to submit hardware to be tested. The tests will cover both the perceived quality of the picture and sound, and the effect of interference on quality. Eventually, when testing is completed, the advisory committee will evaluate the results. Then it will make a recommendation to the FCC on which format should be adopted. What the FCC does then depends on the degree of industry consensus that has been reached. The FCC could move quickly to adopt the recommended format, or it could hold hearings, or it could reject all the work and start the process all over again. I will return to this point, because it appears to me that the FCC is not prepared to deal with a process that does not reach a consensus. And, I believe the chance of reaching an industry consensus is quite small.</p>

<p>Proposals</p>

<p>I mentioned that there were 15 to 20 proposals submitted. Some, according to experts, submitted proposals that appear to violate the laws of physics. One of the working parties of the FCC advisory committee held a full week review of proposed ATV systems in mid-November, 1988, where 13 proponents made presentations. A follow-on session was held in May, 1989.</p>

<p>About six to eight proposals are considered to be potentially viable, in the sense that they are likely to be presented in hardware form for testing. Of these, only Zenith, Philips, Sarnoff Labs and NHK expect to have hardware for testing by the end of 1989. Other proponents may have hardware available in 1990. But none of the systems has really stabilized, and all the proponents are continuing to refine their designs.</p>

<p>Testing</p>

<p>The FCC originally hoped that hardware testing could begin in late 1988. It now appears that testing will actually start in early 1990 and last one and one-half to two years.</p>

<p>The testing of TV broadcasting formats will be done by an organization called the Advanced Television Test Center (ATTC), which is funded by the commercial TV networks and the broadcast trade associations. The cable TV industry has formed its own research organization, Cable TV Labs, which is expected to contribute financial support to the ATTC test program. The ATTC is now in the process of finding laboratory space and has begun ordering the specialized, and very expensive, test equipment that will be used.</p>

<p>There remain, however, two controversies that need to be resolved before testing can begin. One deals with the format of input source material, and the other deals with audio.</p>

<p>Input Source Material</p>

<p>The input source material is defined as the programming that will be used for the testing and evaluation of each proposed format. In order that the tests be conducted fairly, the same test programming should be displayed in all the competing formats. However, the proposed formats are technically very different from one another. For example, the numbers of scan lines differ from one format to another, as do field and frame rates.</p>

<p>This means that the test programming must either be produced in several distinct technical formats, or must be converted to different formats, in order to be tested and displayed on the competing systems. When programming is converted from one format to another--the technical term is "transcoded"--sometimes impairments are introduced. Of course, none of the proponents is willing to risk having a system downgraded in the testing because of impairments caused by transcoding.</p>

<p>Audio Formats</p>

<p>Another unresolved controversy deals with testing of audio formats. The goal is to achieve compact disk-quality sound by digital processing and transmission of the audio. Two companies, Dolby and Digideck, have proposed specific audio coding formats. In addition, some of the video proposals include specific audio coding proposals as well. However, several of the video format proponents have simply set aside data channel capacity within their formats for digital audio. They are indifferent as to which digital audio format is used.</p>

<p>The unresolved questions are: 1) whether the testing of video should be done with audio signals present; 2) whether the video should be tested several times, in the presence several different audio signal formats; and 3) how to take into account the quality of the audio coding format when evaluating different video formats.</p>

<p>Reaching a consensus on these questions may be difficult, because there is the feeling that some possible decisions could give big advantages to one format proponent over another. This will be a significant test. If the industry advisory committee cannot reach a consensus on these questions, the likelihood of reaching a consensus on which format to adopt will be very slim.</p>

<p>FCC Decisions</p>

<p>Even though this process seems to have years to go before it ends, the FCC has reached some tentative policy decisions. First, the FCC has decided that the consumer's investment in current model TV sets must be preserved. Today's TV sets must not be made obsolete. TV stations must not be allowed to abandon those consumers with current TV sets. This means either that any new format must be compatible with current TV sets, or that TV stations will have to simulcast programming in both formats on separate TV channels.</p>

<p>Second, the FCC tentatively decided that TV stations could only have up to six MHz more, in addition to their current assignment of six MHz, for advanced TV broadcasting. Depending on the format that is eventually adopted, this additional six MHz might be used in two ways. For example, the TV stations might use it to augment the information that is being broadcast on their current TV channels. The augmentation channel would carry the increased resolution data, the side panels needed for the wider picture, and the digital audio. Next-generation TV receivers would receive two channels--the current TV channel plus the augmentation channel. The TV receivers would then combine them into a high definition picture. Meanwhile, the current TV channel would continue to be available for reception by old-format TV sets.</p>

<p>Or, the second six MHz could be transmitted as a separate, standalone high definition channel. New TV sets would receive only the new channels. The old channels would continue to be used to broadcast programming to old-format TV sets, at least for some interim period of time.</p>

<p>These two tentative decisions have eliminated from consideration the one system that is farthest along in development: the Japanese system known as MUSE E. This system will be used in Japan for satellite broadcasting, requires a nine MHz channel, and is incompatible with old-format TV sets. Because MUSE E has effectively been eliminated, scientists at NHK (the Japanese broadcasting administration) have had to go back to the drawing board. They have come up with designs that use less spectrum and are backward-compatible with today's TV sets.</p>

<p>A third tentative decision by the FCC is that only the VHF and UHF spectrum now allocated for TV broadcasting will be used for HDTV broadcasting. The FCC will not take away any spectrum from land mobile or microwave or satellite services and give it TV stations. All other spectrum is either too heavily used already, or technically unsuitable for TV broadcasting.</p>

<p>A final tentative decision made by the FCC deals with non-broadcast media. For satellite TV, cable TV and fiber optic distribution of video, the FCC has decided not to adopt a standard. The standard format that the FCC adopts will apply only to terrestrial TV broadcasting.</p>

<p>Cable and satellite formats will still have to be compatible in some respects with the broadcast standard, in order for a consumer to be able to use the same TV receiver to display programming from all sources. But cable and satellite systems are not as spectrum-limited as terrestrial broadcasting, so they may be able to use different formats to take advantage of their available spectrum.</p>

<p>The EIA is setting up a committee to develop a TV receiver architecture for HDTV that will be compatible both with terrestrial TV and non-broadcast media such as cable TV, satellite TV and fiber. This new receiver will probably be an evolution of today's TV receiver-monitors. It will be able to receive signals in TV broadcast format and also in what is known as baseband format from VCRs and other input sources. The EIA calls this a "multiport" receiver. This committee is controlled by the TV set manufacturers, and controversy could arise if it decides that functions now provided by cable TV converters and satellite TV receiver should be incorporated into future TV sets.</p>

<p>Future FCC Decisions</p>

<p>The FCC is expected to decide on a single format for terrestrial TV broadcasting. It is not expected to leave the decision to the marketplace, as it did with AM stereo. But the FCC is hoping for industry consensus. Without consensus, a decision will be difficult. I am concerned that the FCC will not be able to make the hard decisions in a timely fashion.</p>

<p>First, FCC staffing has decreased over the last five years. Staffing levels have fallen from 2200 to 1800, and could go below 1650 in the next year. Second, the FCC's staffing level for HDTV is distressingly slim. Only three or four staffers seem to be spending a significant part of their time on HDTV. This may pose no problem if the industry can reach a consensus on the hard issues, but it could be a disaster if there is no consensus.</p>

<p>Third, FCC has not committed the resources needed to make an independent decision. It does not have the test equipment or laboratory space to independently test the hardware. And, it is not prepared to independently analyze the test data that the advisory committee will produce.</p>

<p>Finally, the FCC has no contingency plan. Suppose the advisory committee, at the end of several years of deliberations, reaches the following conclusions: "Of the twenty or so systems tested, there are four that are superior. The four are equally good. We cannot reach a consensus on which should be the U.S. standard." Maybe this won't happen. And, if it does, maybe the four leading proponents will get together and devise a new format that is a synthesis of the best elements of each. But if no consensus can be reached, there are no procedures for forcing a synthesis.</p>

<p>In this scenario, the result could be years of delay, coming from a combination of administrative indecision and court appeals. Manufacturers would not commit to produce any TV sets in any new format while this uncertainty lasted. Even if this disaster does not come to pass, there is another serious problem lurking--limited spectrum. There might not be quite enough spectrum to satisfy all current TV stations in all markets.</p>

<p>The kind of problem that might then face the FCC can be illustrated by the following scenario: Suppose the advisory committee comes up with two formats that could be adopted. One requires a modest amount of additional spectrum, and it can be implemented at every existing TV station throughout the country. The second format provides better quality and a sharper picture, but requires more spectrum. This format can be implemented at every existing TV station throughout the country except in New York, Los Angeles and Chicago. In these three markets, there is only enough spectrum so that 75% of the TV stations can implement the new format.</p>

<p>Which format should the FCC choose? Should they pick the lower quality format, and penalize viewers throughout most of the country, for the sake of a few TV stations in three cities? Or should they pick the better quality format, and then decide which TV stations in the three big cities will be deprived of the opportunity to convert to the new format?</p>

<p>It seems likely that this kind of issue will arise. Spectrum is very scarce around the top five or ten cities, but more available everywhere else. That is a hard decision, and the FCC is not known for making hard decisions like this. Once again, the result could be years of delays caused by court appeals, particularly by any TV stations that think they will get a less desirable allocation than their competitors.</p>

<p>What the FCC won't decide is the question of standards for satellite TV, cable TV or fiber distribution of video. These industry segments will have the freedom and flexibility to choose other formats. This is really not much different than today, where satellite TV uses FM modulation rather than AM and uses digital sound in the horizontal blanking interval rather than analog sound as a subcarrier on the video. However, it is important that all these video distribution services are able to use the same display device. That means that whatever formats are eventually adopted by the cable TV and satellite TV industries need to be "friendly" and interoperable with the terrestrial standard.</p>

<p>HDTV Timetable</p>

<p>It appears that sometime in 1992 is the earliest date when the FCC could make a decision on a new TV standard. Hardware testing starts in early 1990, and could last up to two years. The advisory committee will make a recommendation to the FCC in late 1991 or early 1992; an FCC decision might appear six months later, sometime in 1992. The first HDTV transmitters and receivers might be available in 1994.</p>

<p>The Role of Congress</p>

<p>The Congress has grabbed onto HDTV because it is very sexy--it gets great press coverage. But Congressmen have only the faintest understanding of the technical issues in the debate. They understand what a TV display is, because they can touch it. They do not understand the problems of broadcast transmission. The issue that they can handle is "competitiveness." How will U.S. companies be able to compete against foreign companies? And what impact will this have on U.S. jobs?</p>

<p>In several Congressional hearings, witnesses have testified that participation in HDTV is crucial for the U.S. semiconductor industry. The semiconductor content of TV receivers will be higher in the future than today. And there will be spinoff products for the military, medical applications, air traffic control, and other industrial uses for HDTV.</p>

<p>The Congress wants to help U.S. companies avoid the fate of the VCR. That technology was invented in the U.S., but is now dominated by Japanese and other Pacific rim countries. There are Congressional proposals in the talking stage calling for Federal funding of research and development, for tax incentives, and for changes to antitrust laws that would allow competitors to join together to manufacture TV sets.</p>

<p>This is not another case of the U.S. vs. Japan, however. The largest market share for color TVs belongs to Thomson, the French company that bought the General Electric and RCA brands; Thomson has 22% of the market. Zenith is second with 12%. Philips, the European company that owns the Magnavox and Sylvania brands, is third with 10%, and Sony is fourth with 6%. All these companies manufacture in the U.S.</p>

<p>TV receivers are different than VCRs. Only about 10% of the cost of TV receivers today is in the semiconductor components. Cabinets and picture tubes make up a large part of the cost of color TV sets. Shipping costs are high for these components. So foreign-owned companies have established U.S. manufacturing plants. A study done for the EIA concludes that 13 million HDTV receivers will be purchased in the United States in the year 2003, and 92 percent of them will be made in the U.S.</p>

<p>The Congress is now starting to wrestle with this question: Should federal funding, tax breaks and other benefits be available only to U.S.-owned companies, or also to foreign-owned companies that have U.S. manufacturing plants and U.S. employees?</p>

<p>Congress depends upon a consensus to make decisions, even more than the FCC. It is possible that a consensus will emerge, but there is certainly no consensus yet on what action to take. Any Congressional action will be limited to the area of display technology, not the problems of setting a transmission standard.</p>

<p>How HDTV Will Affect TIA Members</p>

<p>The manufacturers that make up the membership of the Telecommunications Industry Association come from diverse parts of the telecommunications industry. As such, some will be affected by HDTV, some not. Some will have new business opportunities, some may see opportunities pass them by.</p>

<p>Land Mobile</p>

<p>One industry already affected is the land mobile industry. Land mobile was poised to get access to unused UHF television spectrum, when the FCC put a freeze on spectrum sharing. Now, that spectrum sharing possibility is dead.</p>

<p>But, land mobile might eventually turn out to be a winner. This possibility might happen if, for example, the Zenith format is chosen as the standard. The Zenith format fits the compressed HDTV signal into a six MHz channel that is incompatible with today's TV sets. This means that TV stations would have to simultaneously broadcast the new signal, as well as the old signal that is compatible with today's TVs. This simulcasting would only be needed for some transition period, say 15 to 20 years. At the end of the transition period, the FCC could decide to take away some of the TV spectrum now being used by TV stations and give it to land mobile. But this is a scenario with a 20-year horizon, so it does little to solve today's shortage of land mobile spectrum.</p>

<p>Fiber Optics</p>

<p>Fiber manufacturers and telephone companies think they have a stake in HDTV. The telephone companies assume that they will be delivering video to the home over optical fiber.</p>

<p>Telcos have asked the FCC to require that next-generation TV receivers have a connector port for a fiber optic link. That won't happen. First, the FCC probably doesn't have legal authority in this area. Second, the consumer electronics industry objects to anything that adds unnecessary costs to its products. But most importantly, the HDTV formats that have been proposed are analog formats. They could be converted to digital signals, but they would require huge data rates, 500 Mb/s or more. That would be too expensive. A compression format would be needed for digital HDTV, but nobody has yet proposed such a format. It is premature to talk seriously about a digital fiber optic connector on TV receivers, until the industry agrees to a digital HDTV compression format.</p>

<p>Actually, compressed digital HDTV might not be needed. Analog rather than digital transmission of video over fiber seems to be coming along quickly. The cable television industry plans to aggressively install fiber as a replacement for coaxial cable over the next decade. Cable industry plans call for analog transmission of video over fiber, not digital.</p>

<p>Video is inherently an analog signal, and digital coding is not yet at the point where it is equally efficient. Even with personal computers, the latest video interface standard, known as VGA, consists of analog signals. The earlier digital interface standards have been abandoned, because they cannot reproduce video with enough colors and enough resolution in the same bandwidth.</p>

<p>In Washington, everyone has a hidden agenda. The telco agenda is not that well hidden. The Bell Operating Companies' primary goal is to use the HDTV issue to help get out from under the Line of Business restrictions from the antitrust decision. Their secondary goal is to buy up the local cable companies, and eliminate a potential future competitor for telephone service. Finally, a lower priority is to bring fiber to every home. This will happen eventually, but it will happen faster if they can use cable TV service to share the costs.</p>

<p>This is a battle between telephone and cable TV giants. Manufacturers of fiber optic systems should not allow themselves to get caught in the middle in this battle between the telephone industry and the cable TV industry.</p>

<p>Satellite and Cable TV</p>

<p>The satellite and cable industries are potentially the big winners in HDTV. They have more technical freedom and flexibility than broadcast television. They will have programming available, since pay TV programmers like HBO are likely to be among the first HDTV programmers. They don't have to wait for an FCC standard if they can reach agreement on an industry standard.</p>

<p>The major technical question mark for cable TV is the effect on HDTV of the echoes and signal reflections in the cable. Cable TV systems have "close-in" echoes that produce ghosts that are very slightly separated from the actual image on the screen. The result is a softening of the picture sharpness that is usually not objectionable. It remains to be seen whether these ghosts will be a problem with HDTV signals.</p>

<p>Even though it will not be required, the cable TV industry will probably use the same HDTV format as terrestrial broadcasters, because it simplifies their technical system design. Initial demonstrations of some Japanese equipment seem to indicate that HDTV signals can pass down a cable system without impairment. But much more testing under controlled conditions will be needed.</p>

<p>Meanwhile, some cable operators are delaying their rebuilds until they know what performance specifications will support HDTV. This slowdown has had some effect on cable TV equipment manufacturers. At the same time, the apparent cable industry enthusiasm for analog fiber optics has created some important new marketplace opportunities for fiber optic manufacturers.</p>

<p>The satellite industry will almost certainly not use the same format as terrestrial broadcasting, because virtually all the proposed formats contain quadrature signal components that are incompatible with FM modulation. In Europe and Japan, satellite TV is the only way that HDTV will be distributed. Consequently, there are satellite formats available for use fairly quickly. But satellite is not totally free to choose any format it wants. Consumers will not be willing to buy two different TV receivers. The satellite HDTV format will have to be closely related to the terrestrial TV format, in terms of scan lines and other parameters.</p>

<p>In addition, the satellite TV industry includes some uncertainties. The recent failure of the HBO-GE partnership known as Crimson reflects this. Perhaps Hughes will launch a high power DBS satellite in the early 1990s, or perhaps the launch will be postponed. For now, however, the only satellite TV industry is the home dish market of 1-2 million subscribers. This is simply not a big enough business to become a leader in HDTV.</p>

<p>In my opinion, both cable TV and satellite TV will be followers, rather than leaders, in switching to HDTV. The leader will be either terrestrial TV, or possibly pre-recorded media such as laser disks. The role of pre-recorded media in HDTV remains to be seen.</p>

<p>Microwave</p>

<p>The microwave industry is also affected to some extent by HDTV. TV broadcast stations are major users of microwave, both for portable links used for live news coverage and fixed microwave links between the TV station studio and the transmitter tower. When they buy new HDTV transmitters, they will have to buy new microwave links. In the same way that broadcasters will need more TV spectrum for HDTV broadcasting, they will also need more microwave spectrum.</p>

<p>Unfortunately, the microwave spectrum doesn't seem to be available in most cities. The FCC has given very little thought to this aspect of HDTV. Perhaps the FCC can go to the Federal government and "borrow" some lightly-used government microwave spectrum for 20 or 30 years. Or perhaps this becomes an important new opportunity for the fiber optics industry. But it is a matter of concern that has not been given much attention.</p>

<p>Conclusion</p>

<p>The broadcasting industry used to be a stodgy, dull, mature industry. HDTV has changed all that. It has created excitement. There have probably been more advances in video technology, at least on paper, in the last two years than in the previous twenty. But for now just about everything is still on paper. The next three years will be even more exciting, as theory is translated into hardware, hardware is tested, test results are evaluated, and decisions are made.</p>

<p>My hope is that three years is the right timetable for a decision, not five years or ten years. My hope is that the decisions will be easy, not hard. My hope is that the industry will reach a consensus, in spite of all the forces lined up to make that difficult. My hope is that HDTV will create tremendous new opportunities for manufacturers and at the same time give consumers the improved quality they want.</p>

<p>Jeffrey Krauss<br />
Jeffrey Krauss holds a BS from the Illinois Institute of Technology and a PhD from Case Western Reserve University, both in physics. He is currently an independent consultant helping clients understand the impact of government regulations on new technologies.</p>

<p>Dr. Krauss has worked at Bell Laboratories as a technical staff member for network analysis and operations research. At American Satellite Corporation he was responsible for the economic modelling of satellite networks. He held the position of assistant chief at the FCC's Office of Plans and Policy, working on the policy impact of new technologies. He was vice president of corporate affairs at M/A-COM, Inc., a telecommunications equipment manufacturer, representing the company before the FCC, Congress, and other government agencies on telecommunications policy matters.</p>

<p>Dr. Krauss is a member of the FCC's Advanced Television Advisory Committee. He also is a member of the National Cable Television Association's Engineering Committee, and the Satellite Broadcast and Communication Association's Technical Committee. In addition, he is a member of the EIA's HDTV Subcommittee.<br />
</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Dale Cripps</b>, <b>June  9, 2005 09:27 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 66
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
 				AND entry_id <> 66
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/2005/06/hdtv_its_going_to_be_greator_is_it.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
