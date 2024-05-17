<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 85";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Dale Cripps'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 85 AND placement_is_primary = 1";
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
	<meta name="keywords" content="under signal, definition television, blank areas, second signal, study group, HDTV, hdtv, signal, noise, system, resolution, chrominance, meeting, broadcasting, going, digital, channel, television, cost, snr, information, SNR, being, iwp, IWP" />
	<meta name="description" content="In due time I will post all of the HDTV Newsletters that I produced for over 10 years. The one below was from our 3rd year in the 7th month. That was in 1989. This issue will give you a..." />
	<title>HDTV Magazine Archive &amp; History - HDTV Newsletters Provide The History of the Movement</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_newsletters_provide_the_history_of_the_movement';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('HDTV Newsletters Provide The History of the Movement'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/history/2005/06/hdtv_newsletters_provide_the_history_of_the_movement.php";
		if ($author[img] != '' && 5 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Dale Cripps" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV Newsletters Provide The History of the Movement</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Dale Cripps</b><br />
				<?=$author_title?>
				Posted on <b>June 16, 2005</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category="></a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/2005/06/hdtv_newsletters_provide_the_history_of_the_movement.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/history/2005/06/hdtv_newsletters_provide_the_history_of_the_movement.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/history/2005/06/hdtv_newsletters_provide_the_history_of_the_movement.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/2005/06/hdtv_newsletters_provide_the_history_of_the_movement.php&amp;phase=2&amp;title=HDTV%20Newsletters%20Provide%20The%20History%20of%20the%20Movement&amp;bodytext=In%20due%20time%20I%20will%20post%20all%20of%20the%20HDTV%20Newsletters%20that%20I%20produced%20for%20over%2010%20years.%20The%20one%20below%20was%20from%20our%203rd%20year%20in%20the%207th%20month.%20That%20was%20in%201989.%20This%20issue%20will%20give%20you%20a...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p><em>In due time I will post all of the HDTV Newsletters that I produced for over 10 years. The one below was from our 3rd year in the 7th month. That was in 1989. This issue will give you a glimps into the world of HDTV at that tumultous time. To bring HDTV to you a tremendous struggle took place among giant intellects and bigger companies and still bigger nations. How we ever came to agreement is still the grand mystery, and, of course, agreement on the standards for demodulation and decoding HDTV signals around the world wound up not being the same. Each standard fit the environment where it was to be used. The lesson of biological progress is that a mutation will tend in any direction but the one which survives to be the father and mother of a new species is the mutation which is best fitted to the environment in which it was born. That is exactly what happened with HDTV standards that were fitted best for the various parts of the world. A single world standard was the holy grail among the engineers but it did not materialize. Due to enormous steps taken in silicon, however, they all my live on one chip.</em></p>

<p><br />
<strong>Vol. 3, No. 7</strong><br />
<strong>The Heat Is On </strong>or<br />
<strong>We're Dancing As Fast As We Can</strong></p>

<p><br />
<strong><em>How It Is Shaping Up With Broadcasting</em></strong></p>

<p>It looks like the lines are drawn, at least if you ask the NAB HDTV Task Force Chairman, Hal Protter, or ABC's president of Operations, Julius Barnathan. If you are going to have the broadcast and cable people pay for all the upgrade in their equipment, pay the cost of bandwidth from the satellites, etc., the public is going to get an IDTV (Improved Definition TeleVision) system. Faroudja, an EDTV (Extended Definition TeleVision) system, is looking also quite good and should not be a disappointment to anyone for the price. </p>

<p>If the public is willing to pay for HDTV in some form and in some way, i.,e., subscription price, tax, surcharge or value added fee on their set purchase, etc, etc, then they can "buy" their HDTV service. But nobody yet has put forth a plan where the public is involved with the cost of the up-grade except HBO, and they are laying low for the time being. Beside buying the sets for whatever they cost to make - that's it as far as the public is concerned. </p>

<p>One of the most refreshing things in talking with those involved with advancing HDTV is the relaxation of the 'suspicious motive'. Various positions have been staked out and there has always been underlying statements that this guy or that was in somebody's pocket and, therefore, saying this or that for those dubious reasons. But there is a friendlier and more respectful atmosphere developing. The door is opening for closer talks among adversaries. </p>

<p><strong>ACTV</strong></p>

<p>Steve Bonica, vp Engineering for NBC, talks about the philosophy behind ACTV E & I:</p>

<p><strong>ACTV E</strong> is the entry system for wide screen 525 delivery. The cost to the broadcaster and consumer alike is held to a minimum. NBC has developed ACTV E, I & II on a business base asking technology to accommodate the business conclusions. Bonica visited Japan and returned all-the-more secure in NBC's choices.</p>

<p><em><strong>The color introduction was slow, taking years for broadcasters to overhaul their stations. Is this what is anticipated today?</</strong>em></p>

<p>I think that is only somewhat right. The broadcasting business has changed quite a bit. There is a lot more flexibility required. When color came along it was acceptable for a long time for news film to be in black and white. I don’t think the American public is tolerant of that any more. Their expectations are higher. The conversion to ATV (Advanced Television) is likely to be requested by the consumer to be a little faster and more complete. Also, the broadcast facility is more complex and sophisticated than it was. The taking of sources from many different spots and trying to integrate all of that over a short period of the broadcast day puts a lot of demand on automated systems. </p>

<p>Trying to introduce something unique in the broadcast system these days trips you up. You want to have it all capable. The piecemeal migration just doesn't live up to the expectation of the broadcaster or on the part of the consumer. Because of that I think there is a lot of concern for the cost of production and distribution equipment associated with high definition television. </p>

<p><em><strong>If I see a good wide-screen program on NBC at 8:00, am I going to be so demanding of my 11:00 local news?</strong></em></p>

<p>I think that is exactly what is going to happen. On the other side of that coin if it is created in such a way that you can only introduce it slowly, then what kind of a business is it? How interested will people be in it? If it is made and and designed so it can be implemented in a cost-effective way and not send the business you are trying to convert to the poor house, then it is likely to propagate very quickly and be successful for everybody involved. The manufacturer needs the confidence it is really going to happen. The guy who stocks the tapes on his shelves in the rental store needs to know it is going to grow...and the broadcaster, cablecaster, DBS caster -- all those business entities need the confidence to <em>know </em>that this is not going to go down the drain in a short period of time and be a fad. <strong><em>That is a part of the equation.</em></strong></p>

<p>We look back to the Henry Ford philosophy. The conclusion reached: if it is anything it has to be mass produced; it has to be affordable. ACTV is designed to be practical. It is good for the masses and the suppliers. That doesn’t mean we have anything against HDTV. It has a place, and that is in production. You still have that tremendous display cost. </p>

<p>My trip to Japan produced intense discussion with manufacturers who convinced me true 2 x NTSC resolution--HDTV--is not affordable in the living room for most of the 1990s. It will be a spectator item. Over and over again in Japan I said to the manufacturers; "It is 1994 and I want to deliver 800 lines of resolution in each direction (verticle and horizontal) to the consumer. Right away they discount the silicone as not being a big deal in total cost. That television is likely to cost $4000 to $6000 in 1994. Now what happens, I said, if I lower the resolution to 500 lines in each direction? "Now you are talking about 30% more on the list you are paying now." That sort of convinced me we are on the right path [with ACTV]." (Editor's Note: ACTV 1 was a proposal from NBC for a compatible (to old standard) widescreen EDTV system that used an augmentation channel for carrying extra detail).</p>

<p>***</p>

<p>CCIR UP-DATE</p>

<p>October 1989 will see the final meeting of the CCIR's 1986-1990 study period, a major focus point in the struggle for HDTV standardization. Professor M. Krivocheev, chairman of Study Group 11, (responsible for television broadcasting) sends us this status report. </p>

<p>The extraordinary meeting of Study Group 11 on HDTV, deemed necessary last fall to examine the issues, will take place in Geneva May 10-16 to prepare the report for October. Its purposes and subgroup activities are summarized for you here:</p>

<p><em>The International Radio Consultative Committee (CCIR) is the permanent organ of the International Telecommunication Union (ITU) responsible for studying technical and operating questions relating specifically to radiocommunications, and issuing recommendations on them. </em></p>

<p>All telecommunication administrations that are members of the ITU are automatically members of the CCIR. Broadcasting and telecommunication operators (e.g., CBS, AT&T) may become members by forwarding a request through the respective Administration and paying an annual fee. Participation in an advisory capacity is open to scientific and industrial organizations (e.g., manufacturers, laboratories) on the same basis, and related international organizations (e.g., regional broadcasting and telecommunication organizations) may participate in an advisory capacity.</p>

<p>The CCIR is the only international organization working on HDTV standardization on a worldwide basis. Although the CCIR has dealt with High-definition television since 1972, activities were intensified after the 16th Plenary Assembly in Dubrovnik (May, 1986) to study and develop HDTV techniques. Ten different Interim Working Parties and Joint IWP's are dealing with various aspects of HDTV, both analog and digital: programme production, transmission and emission of signals in terrestrial or satellite services, transcoding to the traditional television systems and also technical links to other existing or forthcoming media. Careful coordination is overseen by by a group of associated chairmen, also under the leadership of Prof. Krivocheev. All substantive issues are dealt with by the IWP's and JIWP's themselves.</p>

<p>The first meeting of the coordinating group was held in Geneva at the end of September 1988 and concluded that the extraordinary meeting of CCIR Study Group 11 should take place in Geneva from 10 to 16 May 1989 to deal with HDTV. It also defined topics as well as a timetable for further preparatory work. The second meeting took place in Geneva on 30 and 31 January 1989.</p>

<p><strong>General Situation</strong><br />
The 1987 interim meeting of Study Group 11 took a comprehensive approach towards HDTV. This is reflected in draft new Report AU/11 "A Global Approach to HDTV Systems" and comprises the internationally-agreed strategy for the study and standardization of HDTV on a global basis.</p>

<p>"The Extraordinary Meeting of Study Group 11 will contribute to further progress in international standardization for HDTV. This meeting will be the first international forum where HDTV will be considered in its entirety and in detail, and will also provide a framework for studying constraints and the interrelationship between the various items. It will give a good opportunity for exchanging and co-ordinating ideas on various aspects of HDTV. In this sense, the meeting is very important to aid administrations and broadcasting organizations in formulating their future technical policy in HDTV. It will also identify the further studies to be undertaken."</p>

<p>In preparing for the extraordinary Meeting, related events must be taken into account, e.g., the World Conference of Broadcasting Unions (Washington, 12-17 March, 1989), a preliminary report from broadcasting organizations about tests of HDTV equipment carried out by the Moscow Group of HDTV experts (March 1989), meeting of the EBU Technical Committee (18-21 April, 1989), meeting of the OIRT Technical Commission (9-14 April, 1989).</p>

<p>While the diverse objectives of HDTV parties and existence of various technical solutions makes a complicated environment, past examples, such as Recommendation 601 on the digital studio standard, give evidence that excellent solutions can be found and confidence that similarly good results are possible in the field of HDTV.</p>

<p>The IWP 11/6 (Studio and emission) experts worked out a draft Recommendation during the January 1989 meeting on a single world-wide HDTV studio standard, unanimously supported by the participants as a first step to international standardization. HDTV production is seen an important element of the CCIR studies, particularly the definition and specification of a broadcasting distribution interface. IWP 11/7 is responsible for digital HDTV, investigating in more detail the relationship of future digital HDTV specifications to Recommendation 601. Because a digital approach to HDTV might give a better chance for a world-wide uniform standard that analog systems can provide, the work of IWP's 11/6 and 11/7 must be carried out jointly in this area.</p>

<p>During WARC-Orb-88 the subject of HDTV satellite transmission was discussed at length, largely based on the technical work of JIWP 10-11/3. The results are contained in Resolution COM.5/3 which recommends to the forthcoming ITU Plenipotentiary Conference (late May 1989) to make a definite selection of a frequency bank to be used for satellite emission of HDTV, preferably on a world-wide basis.</p>

<p><u>The transmission of HDTV signals via satellites will be an important element </u>during the Olympic Games in France and Spain, 1992, taking into account the excellent experience from Seoul. In the not-too-distant future, it would be worthwhile for this JIWP to extend its studies to include HDTV satellite news gathering with the introduction of HDTV-SNG for the 1992 Olympic Games as a target date. </p>

<p>During this study period, studies on HDTV have been intensified considerably and the field of study has been enlarged. This may lead to conclusions that respective Questions and Study Programmes need revision or that a rearrangement of all of them is required. This work is being coordinated by IWP11/8, chaired by Mr. Wu Xianlun (China), Vice-Chairman, Study Group 11.</p>

<p><strong>PROGRESS REPORTS</strong><br />
11/4 (Subjective Assessment of Television)<br />
Chairman: Mr. David Wood (European Broadcasting Union)</p>

<p>At the next meeting of IWP11/4 (May 1-3, 1989) it is hoped to prepare and agree on a formal draft Recommendation (working title as above), extending the work of 1988.</p>

<p>In addition, supplement has been suggested to cover several areas, including the comparative evaluation of studio standards. </p>

<p>IWP 11/5 (Protection Ratios for Television)<br />
Chairman:  Mr. S. Dinsel (IRT, Federal Republic of Germany)</p>

<p>The IWP 11/5 meeting from 7 to 19 March 1989 will prepare the report to the Extrodinary Meeting on general system requirements; protection-ratio figures; requirements to and from the other IPS's or JIWP's concerning terrestrial and cable transmission.</p>

<p>The present protection-ratio figures for existing PAL/SECAM/NTSC systems, especially in the case of cable transmission, cover in many cases the requirements for HDTV transmission. The report will cover more than 70% of the parameters and requirements for a single HDTV transmission standard.</p>

<p>Additional information from IWP 11/6 is necessary concerning the sensitivity against interference, in the case of the data/sound part and of the bandwidth compression part of the signal. </p>

<p><br />
IWP 11/6 (HDTV Studio and Emission Standards)<br />
Chairman:  Mr. Y. Tadokoro (Japan)</p>

<p>The 7th meeting of IWP 11/6 was held in Geneva from 28 to 29 January 1989, with about 60 participants and 68 technical papers collected and examined by three working groups. A draft proposal for a Recommendation was prepared with major parameter values of the single worldwide HDTV studio standard. Progress reports were drafted on studio, emission and transmission technologies. </p>

<p>IWP 11/6 will meet again in Geneva at the beginning of May 1989, to try to fill in the vacant parameters not yet agreed, and on revision of existing CCIR documents. </p>

<p>IWP 11/7 (Digital Television)<br />
Chairman:  Mr. A. N. Heightman (United Kingdom)</p>

<p>Since IWP 11/7 cannot give definitive proposals as to HDTV standards without regard to IWP 11/6 in relation to encoding parameters, its output document indicates rather the possible approaches that may be taken, i.e., via a "unified", a "unique", or a "dual" standard. Reference is also made to bit-rate reduction in relation to distribution between broadcasters and to viewers.<br />
Recent contributions should also enable dealing with encoding parameters. Little information on bit-rate reduction for HDTV is available, but the extensive work already in progress for other standards should provide a firm basis for decision. In that a digital approach might give a better chance for a worldwide uniform standard, closer cooperation between IWP 11/6 and IWP 11/7 is targeted.</p>

<p>JIWP 10-11/1 (Feeder Links to the Boadcasting-Satellite Service)<br />
Chairman:  Mr. Daniel Sauvet-Goichon (TDF, France)</p>

<p>Did not convene but prepared the summary report of the relevant results of WARC-Orb-88 as a contribution from the Chairman to Study Group 11. It contains two parts, first dealing with feeder-link planning for 12 GHz-band satellites; and second a brief summary of HDTV broadcasting in relation to Resolution COM5/3 of the WARC-conference.</p>

<p>JIWP 10-11/3 (Satellite Broadcasting of HDTV)<br />
Chairman:  Mr. Osten Makitalo (Sweden)</p>

<p>This group met in Geneva from January 19-25 with 23 participants and 30 new documents to consider. The major conclusions of their report to the Extrodinary Meeting are: 1.) Several separate techniques exist for the broadcasting by satellite of HDTV over a range of frequencies up to about 23 GHz. The possibilities comprise bandwidth reduction, channel coding, multiplexing, modulation and reception. The technology for implementation of these techniques is either available now (12GHz), or is expected to be available within the next ten years if high power levels are needed at 23 GHz.</p>

<p>Source signal compression, such as the narrow RF-band systems MUSE and HD-MAC provide, will be required for the 12GHz bands planned in Regions 1 and 3, at a cost of temporal resolution. For satellite broadcasting of wider RF-band HDTV service on a worldwide basis, potentially providing quality close to that of the studio standard, a new frequency band would be required. WARC Resolution COM5/3 indicates that:<br />
 <br />
• studies should be continued on the long-range future suitability of the 12 GHz band for HDTV without prejudice to the existing plans in this band.</p>

<p>• WARC be authorized to make the definitive selection of a frequency band for HDTV in the broadcasting satellite service, preferably on a worldwide basis.</p>

<p>They concluded: "The continuation and expansion of a domestic, regional and international system of television program delivery through diverse media calls for defining interfaces between the media. It also dictates the need for a hierarchical set of transmission and emission formats that can take best advantage of the different characteristics of each medium, that can also be converted from one format to the other with minimal degradation and at reasonable cost. Easy convertibility should also be required to reduce the cost and complexity of home receivers." Study targets: radio frequency and emission parameters including modulation, channel coding and multiplexing of HDTV satellite broadcasting, propagation characteristics for wide RF-bands suitable for high definition television emissions, and inter- and intra- service sharing and interference, inter-regional sharing.</p>

<p>JIWP 10-11/4 (Recording for Television)<br />
Chairman:  Mr. P. Zaccarian (CBS)</p>

<p>At this moment, no actual contribution has been received yet. Contributions will be solicited again. Work is in progress that may eventually lead to a proposed draft Recommendation on the photogram area scanned by HDTV telecines on 35mm film, and to another on the photogram area exposed by HDTV film recorders. It is not clear at this moment whether work will be completed by the time of the Extraordinary Meeting.</p>

<p>JIWP 10-11/5 (Data Broadcasting)<br />
Chairman:  Mr. F. Cappuccini (Italy)</p>

<p>Meeting in Geneva October 18-20,1988, the consensus was that, in the development of an HDTV standard, increased quality in vision and sound should be in any case complemented by a parallel enhancement in data broadcasting capability and performance. Future work planned:</p>

<p>• the extended use of the 7-layer reference model for describing data broadcasting transmission procedures and services in the light of the coming availability of high capacity digital channels;</p>

<p>• the balanced harmonization of vision, sound and data resources in an HDTV channel;</p>

<p>• "transparent" transport of data signals in the design of new digital TV systems, either associated to the vision signal, multiplexed with sound signals or completely independent.<br />
• the existing trend towards the integration between interactive and broadcast digital networks.<br />
At present only one new contribution has been received. </p>

<p>***<br />
Our thanks to Professor Krivocheev and CCIR chairman Richard Kirby. (Editor).<br />
__________________</p>

<p></p>

<p><strong>INDUSTRIAL APPLICATIONS</strong></p>

<p><strong><em>They will start up a lot sooner</em></strong></p>

<p>"I think that is the realistic way to go at it," explained Indraneel Paul from Bellcore. We have agreed since the beginning that is the way to get the cost down.” Bellcore reports industrial interest is at its highest. Bellcore supported the NASA March 13th lift off shoot with their largest aggragation of HDTV equipment. </p>

<p>What are these industrial applications? The well known publishing applications make sense. Business broadcasting makes sense to some. Telemedicine raises interest. </p>

<p><strong>1250/59.94</strong> (A proposed production standard from NBC adopting the European 1250 line count and the color NTSC frame rate) </p>

<p>"As with the 1125/60, they are going to face some thorny issues." says Indraneel Paul about NBC's recent proposal for an international dual-standard shared with 1250/50. "Nobody is going to get a rubber stamp." Several key players say we should give it time to develop as nobody is going to buy equipment until it works.  </p>

<p><strong>Fiber Theater Promoter Robin Orr:</strong></p>

<p>"A lot of people are focused on the consumer electronics industry and they have no idea the power HDTV has and what it can do in the control of communications, instead of just hardware sales. If, indeed, this is a war for high definition television or the future of communications, the large screen display system is the bomb.” Orr believes that the DARPA large screen funding will be classified though not the signal processing because the latter is not an important issue. The important issue is a leadership edge in a technology when implemented can effectively influence American competitiveness. That is the large screen image display."</p>

<p><br />
<strong>INTERVIEW</strong></p>

<p>Mr. Larry Thorpe, vp Sony Advanced Systems:</p>

<p><em>Larry Thorpe, inveterate and prolific, has done as much to advance the causes of HDTV as anyone in the world. Numerous articles, papers and speeches mark his efforts worldwide. Thorpe, originally from Ireland, had worked with the BBC, then moved to RCA in the camera division developing over 10 patents, finally moving to Sony, a company he regards with an unhidden reverence. Larry opens talking of the spectacular demonstration he has helped coordinate for the NAB at the Tropicana Hotel with 37 other companies related to the 1125/60 production standard.</em></p>

<p><strong>"It's going to be a hell of a show."</strong></p>

<p><strong><em>We hear there will be some of the theatrical side this year.</em></strong></p>

<p>Yes, but it is going to be practical as well as theatrical. We are going to show what this baby can do, to be a little more imaginative than before. </p>

<p><strong><em>How do you think the "buy America" issues will shake out?</em></strong></p>

<p>I think we are in for a lot of pain in the next year or two but it will all settle down. Japan and the United States are inextricably tied whether we like it or not. People don’t quite yet fully understand the degree in which they are allies. You are just seeing the joustling of giants. </p>

<p><strong><em>There is this talk that while Japan is ascending economically they have no philosophical agenda.</em></strong></p>

<p>Well, they are stepping on the world stage bit by bit. They do it like they do everything - steadily and cautiously. You only have to look to the loans in the world. They are number 1 in aid. They are involved with some mammoth projects around the world, so there is no question that they are on the world stage. </p>

<p><em><strong>People are saying they would like to use this equipment but not all of the peripherals are there. Do you see some developments in this area?</strong></em></p>

<p>Oh yes. You will see a bunch of companies with peripherals at the NAB. They are slowly happening. It has been slow because of the confusion of standards. That is what the 1125/60 Group is all about - trying to encourage that. </p>

<p><em><strong>At the 6th International Broadcasters Union the closing statements were that multiple standards were before us.</strong></em></p>

<p>Well, it certainly looms. No question.</p>

<p><em><strong>Does the multiple standards scenario really effect the economies of scale as so many people have been saying?</strong></em></p>

<p>Yes it really does. HDTV equipment is really expensive. You only have to look at the prices of this company’s products to get a rather dramatic feel for that. It is not easy to reduce and it does worry us. If you enter into a lot of production standards there is no question it is going to work in the wrong direction. You might argue as to how much, but the point is it is going to work in the wrong direction and we have a big battle facing us anyway. </p>

<p><em><strong>You are head of the FCC's ATV economic assessments committee. What kind of numbers are showing up of interest to you?</strong></em></p>

<p>The only good handle we have is on the studio origination equipment since that is all that is out there. We have no feel whatsoever yet for any of the proponent systems. - none whatever. The numbers for the studio origination are very high.</p>

<p><strong><em>We hear from Westinghouse that the cost for equipping a studio runs to $38 million. Is that your finding?</em></strong></p>

<p>Depending on the size. We are looking at a number of scenarios, analyzing a small station and a network. The are both very, very high. You are talking 2 to 3 times what it will cost to outfit with normal broadcast equipment.</p>

<p>A recent NAB Newsletter says it will be 8 years for a 1% penetration into any broadcaster's reach.</p>

<p>That is approximately the figure that came from working party 5 of Joe Flaherty's Planning Subcommittee who put forth the scenario for my group. Based on all real information available today they elected to be conservative. This is not unusual. That is about what color took. </p>

<p><strong><em>Is this such a long range event it is not consequential to today's broadcast management?</em></strong></p>

<p>It is inconsequential to purchasing decisions that are being made today. Some people have been hesitating, saying maybe I should postpone that tape recorder or camera today because I have to bring some HDTV equipment into my studio in the next 2 or 3 years. I think that 2 or 3 years is wrong. They should not allow today’s purchasing decision to be influenced by HDTV. Whether it is 8 years or 7 or 9, it certainly is going to be [some time] before anybody is really changing stations around. </p>

<p><strong><em>Indraneel Paul of Bellcore believes the industrial applications are going to take root first and aid in bringing the cost down. Do you see any evidence?</em></strong></p>

<p>There is no evidence of it yet. I would certainly agree with him. Our speculation is that HDTV will start to move in the business and industrial, particularly with some of the new building blocks that will be seen at the NAB such as the 1/2' distribution format and video disc. </p>

<p><em><strong>What would you think about an HDTV college tour taking some 50 or 100 dates a year with a disc and projector?</strong></em></p>

<p>There are a variety of groups talking about such a thing. In fact there is one broadcast group wondering if they shouldn’t be doing the same for broadcast stations. A number of groups are beginning to believe that it's time for education and the only way is to expose a larger silent majority to HDTV by road shows like this.</p>

<p><em><strong>There has been some talk of a Hollywood effort by your company. Any comments?</strong></em></p>

<p>No, other than we are very active in doing our home work.</p>

<p><strong><em>There are those in Hollywood saying 1125/60 is just not good enough, that it ought to invent a system for itself using 24 frames and more lines. Would they be able to afford that?</em></strong></p>

<p>Again, there is the economies of scale issue. We honestly believe 1125/60 is going to do all that is needed to be done. I probably should say we are going to make it do things that can't be done today. We may shed a little more light on that at NAB, having quite an emphasis on the relationship of 1125/60 and film. I think showing is the best way of doing it. </p>

<p><strong><em>The Europeans want to present their case for 1250/50. Should that become a standard of choice is there a Japanese agenda to stick with 1125/60?</em></strong></p>

<p>No, not at all. The Japanese and those in North America who believe in 1125/60, will remain loyal to it as long as other standards are being proposed at the rate at which they are being proposed. One North American organization has 6 variations right now. We believe that is ludicrous because the stakes are too big. To verify any one of them as being capable of doing what 1125/60 can do or better is a lot of homework. Some of these people are not doing that. One has to give credit to Eureka 95. They at least picked one set of numbers and they are doing their work on that. As to whether they intend to bring it to fruition as a real HDTV system in the near term is a real doubt. I think their agenda is one of smoke screen. They must protect MAC, and are fighting very hard. That is understandable. Our agenda in Japan and the United States is to have HDTV much earlier than that.</p>

<p><strong><em>We heard at the Markey Congressional Hearings [March 8-9] that COCOM treaty agreements are being brought to bear on the proposed side by side standards testing in Moscow. Have you been impacted by that?</em></strong></p>

<p>Only to the degree that Sony has been fairly widely beaten up in the press. But we had done our homework. We had followed through with MITI and everything. It is all documented and there is no problem whatsoever, no details left undone.</p>

<p><em><strong>People have said that a camera would not be permitted into Russia, while David Niles [1125/60 Productions, New York] was over shooting a piece in Russia in HD when the COCOM question arose. </strong></em></p>

<p>That is right. This particular camera is delisted. The particular analog VTR is delisted. There is no problem. </p>

<p><strong><em>You are establishing in your San Diego plant now research, development, and product design. Can we speculate some HDTV activities will ensue there?</em></strong></p>

<p>Right. We have every intention of having HDTV activities within this country. That was all part of our submission to DARPA. </p>

<p><strong><em>Is there a defusing factor here? </em></strong></p>

<p>No, probably Sony, more than most, has a global plan. We are a multinational company in the truest sense of the word in that we have established plants all over the world - in Southeast Asia, Venezuela, Mexico, 5 in North America, in Europe - we just opened another one in Germany. We have a game plan for the next decade. We are bringing very sophisticated manufacturing and R&D to these places. That plan was in place before all of this rather heated discussion of the last 12 months.</p>

<p><strong><em>Will there be a repatriation of technology to this country?</em></strong></p>

<p>Our company employs 7000 people in this country right now, many involved with very high tech manufacturing. We don’t have at this moment a lot of R&D - just in Palo Alto and here in New Jersey. We have a very renowned R&D facility in Basingstoke, England, second to none when it comes to digital technology. We plan those sort of things here in the United States in the future. That will revitalize expertise that is latent here. </p>

<p><strong><em>You were with RCA quite awhile. Do you see any revival of the camera industry within this country?</em></strong></p>

<p>Not yet. No sign of it.</p>

<p><em><strong>Is there any cause for it to happen here in say 5 years.?</strong></em></p>

<p>Sure, there are companies here that make CCDs - Kodak, Fairchild. There is no reason why they couldn't get in on the new era of CCD cameras. Who knows in coming years what we and others might bring to this country. The same might hold true for VTRs.</p>

<p><strong><em>Any sign of seeing VCR manufacturing returned to the U.S?</em></strong></p>

<p>Oh sure. The technologies that we have in this country right now, for example in television set design, are extremely high technology. There is no reason why the VCR couldn’t be built in this country. I strongly suspect that would be part of our plan long term along with other companies.</p>

<p>It is important to understand that these are not token gestures suddenly brought out to defuse a hot situation. I honestly believe these are natural evolutions. Our company and others have the same international plans that the United States multinationals had decades ago. Now we are acquiring a stature - it only in the last 5 or 6 years that Sony has reached the $6 or 7 billion level - to do it. As a corporation we strongly believe in globalization.</p>

<p><em><strong>What do you see on the horizon for big screen displays that looks affordable?</strong></em></p>

<p>On the far horizon it is, of course, the famous or infamous flat panel display that everyone is dreaming about. But that is far down the road.</p>

<p><em><strong>Anything else in the interim?</strong></em><br />
Not near term.</p>

<p>***</p>

<p><strong>AN IDEA FOR THE REST OF US</strong></p>

<p>Not everyone owns a television network, station, or cable distribution system. Few do. But it is those relatively few people that are trying to adapt the idea of wider screen and higher resolution to their existing systems with considerable loss to the originally intended quality. No one beast is suitable for all burdens. </p>

<p>But let's examine a scenario for the rest of us. The ideal HDTV system may not yet have been invented but several are in the process that make pretty good candidates. In hardware we have the 1125/60 and soon we shall have the 1250/50. Take one - it doesn’t matter for the time being which. First rule - don’t let it out into the existing distribution systems. Don't encourage it into the film business. It is a stand alone system not trying to become anything else or serve anyone else. Keep it isolated.</p>

<p>Make a business from it alone. It's abilities are familiar. It has capture, digital stereo sound, a transmission means, and display. It can be live or on tape. It can be transferred to film if some artistic reasons determine and transferred back to tape for distribution. The important thing for the rest of us is to keep it contained and refrain from trying to capture the other guy’s business. Be satisfied with starting small but don’t be satisfied with a no-growth plan.</p>

<p>OK. What do we have? We have pictures and sound that we know can be sold because they emulate media that is already being sold. They can generate revenue by selling tickets to an audience. They can be sold to people making presentations in business, to doctors for medical analysis, to department stores, shopping malls, airports - where ever a picture is required to transfer information that leads to economic activity.</p>

<p>Where shouldn’t the pictures be sold? They should not be sold to those who don’t want them. They should not be sold to those who want them to conform to what they already have with vast sacrifices to the original intent. They should not be sold where the appropriate economy doesn’t exist. They need not be sold to the existing television system except where that system buys them on the terms laid down by the HDTV people - not the other way around. </p>

<p>So what are we saying? Look at HDTV as a separate and new business that, yes, has similarities to what was done in the past but is not a rerun. Plant it where it is usable in the beginning without it being dragged to the old ways. Be brave and forego which is near to gain what is afar. Make sure you are not tempted to overly adapt the HDTV system; let lower systems come more to you. Think globally, and see the beginnings of the single transmission system is started. All real success is based on mutual attraction. That being true the thing which makes the new business real is attraction. Keeping that principal higher than any other will eventually have the world conforming to you instead of the other way around. </p>

<p>***</p>

<p><br />
SCHREIBER & AL.: COMPATIBLE EDTV</p>

<p>A Compatible High-Definition Television System Using<br />
The Noise-Margin Method of Hiding Enhancement Information<br />
William F. Schreiber, Edward H. Adelson, Andrew B. Lippman, Bernd Girod, Peter Monta, Ashok Popat, Helene Sallic, Paul Shen, Adam Tom, and Kambiz Zangi, The Media Laboratory, MIT, Cambridge, MA 02139, and Arun N. Netravali, AT&T Bell Laboratories, Murray Hill, NJ 07974<br />
Abstract<br />
This paper discloses a new method by which a substantial improvement of resolution may be achieved in NTSC in a compatible manner. Combined with methods of adding digital audio to chrominance, it permits the design of a receiver-compatible (RC) EDTV system with more than twice the area resolution in the stationary parts of the image under good, but reasonable, transmission conditions.<br />
It is found that when video signals are transmitted in the usual over-the-air channels, if the SNR is satisfactory at the receiver, it is actually higher than required in some spatial-frequency bands. This 'noise margin' provides a place to add a large amount of extra information. The additional video information is hidden in such a way that the added signal appears on NTSC receivers as low-level additive random noise, processed and distributed so as to minimize its visibility, while at the same time achieving the maximum possible SNR of the hidden information. On a special EDTV receiver, the second signal is detected independently, combined with the first signal, and displayed at high resolution. The method does require a channel SNR high enough to make good NTSC pictures; however, an adequate SNR is required for any method of transmitting high-quality NTSC-compatible EDTV/HDTV images through analog channels.<br />
It is shown that the NTSC chrominance channel has excess temporal and vertical resolution. At the cost of a very small decrease in luminance resolution on today's most expensive receivers, a scheme is introduced for transmitting digital audio in the excess temporal bandwidth. The excess vertical chrominance resolution is used to increase chrominance horizontal resolution, which is very low in NTSC.</p>

<p><strong>The Noise-Margin Method</strong><br />
<em>The General Idea. </em></p>

<p>Shannon's theorem for the information capacity of analog transmission systems implies that bandwidth and SNR are exchangeable. For example, if the channel SNR is 100, about 100 levels of signal can be distinguished. If we think of the numbers from 0 to 99 as corresponding to voltage levels in a channel, they may represent one signal with 100 levels or two independent signals with 10 levels each, symbolized by the first and second decimal digits. With this in mind, a very simple scheme is to quantize the first signal to 10 levels and add to it the second signal, divided in amplitude by 10. Separation is accomplished by digitizing the sum to 2 decimal digits, assigning each of the digits to one of the output signals.  Equivalently, the received sum signal can be quantized using exactly the same characteristic as at the encoder to obtain the first signal and subtracting the quantizer output from the input to obtain the second.</p>

<p>There are two problems with this method. In order to transmit near the Nyquist rate, intersymbol interference must be negligible. The channel therefore must be well equalized. If there is any noise in the channel, both signals will be corrupted. The signal represented by the MSB's (the 'digital under' signal) will be shifted by one level if the noise and/or distortion plus the second signal exceeds one quantum level. The signal represented by the LSB's (the 'analog over' signal) will be shifted by its maximum amount, a relatively much larger error (a wrap-around error), in the same case. The latter effect may be reduced but not totally avoided by further reducing the 'analog over' signal to allow a margin for noise. However, with Gaussian noise, the instantaneous value will be quite large some fraction of the time. For video signals, an isolated large noise pulse of this character, called a 'click' in FM, can often be removed by intelligent processing.</p>

<p>In a receiver-compatible (RC) system, the 'digital under' signal is the NTSC signal, while the 'analog over' signal is the enhancement information. The second signal appears like noise to the first if it has a noise-like character. If the second signal is a high-frequency component, it will be masked to some extent by the detail in the first signal. If the two signals are spatially coherent, masking will also take place. The noise on the first signal would be quantizing noise while the noise on the second signal would be channel noise.  Both noises would be increased, as mentioned above, if the 'analog over' signal plus channel noise add up to more than one quantum level.<br />
It is clear that the acceptability of this method, if it can be made to work, depends on the SNR being higher than absolutely necessary in some parts of the spectrum. We believe that is generally the case if the picture quality is good. In over-the-air transmission, all spectral components have the same SNR, but the required SNR goes down with frequency, as shown in Fig. 1.<br />
If the noise can be mainly confined to the complex areas of the image, then its visibility can be reduced for a given average SNR in a particular component by adaptive modulation. In a compatible system, this cannot be done for the NTSC components, since their format is prescribed. However, it can be done for the enhancement components, so that the amount of channel capacity they require is actually very small.</p>

<p>  Fig. 1   A Conceptual Explanation of the Noise-Margin Method.<br />
Required SNR decreases with frequency. If a signal has uniform SNR and it is high enough, it is higher than necessary at the higher frequencies, providing a 'noise margin.' Enhancement information can be hidden in this area as long as it looks like noise. In the EDTV receiver, the hidden signal is extracted and used to enhance the image.</p>

<p>Calculating the SNR.  For CNR's low enough to cause few large errors of the type mentioned above, the SNR of the 'digital under' signal will be determined by the number of quantum levels. The ratio of peak signal to rms quantization noise, in dB, is 10.8 + 6n, where n is the number of bits/sample. The SNR of the 'analog over' signal is CNR - 6n. For example, with a CNR of 40 dB, which represents a good but far from perfect transmission, and if 4 bits/sample are used for the first signal, its SNR is 35 dB and that of the second signal is nominally 16 dB. As we shall see, the actual noise performance of the added signals can be greatly improved.</p>

<p>Improving the Noise Performance. Each high-frequency component has zero average value and is very small in the blank areas of the picture where noise is most visible. Due to the lower level of the 'over' signal in these areas, clicks are much rarer than in the busy areas, where they tend to be masked by picture detail. For the 40 dB case mentioned, clicks almost never occur in the blank areas. However, without employing some special method, the SNR would be rather low, since the enhancement signal must be multiplied by a rather large factor before being added to the NTSC signal.<br />
An effective means of noise reduction is adaptive modulation. In this scheme, the highs components, where they are small, are multiplied by a factor large enough so as just not to cause overload. Since the signals so modulated are divided by the same adaptation factor at the decoder, channel noise is divided at the same time. In the noise-margin system, making the 'over' signal higher does increase the click frequency somewhat. However, it also decreases the amplitude of the clicks as they appear in the output signal. Typical maximum adaptation factors are 16 to 32. These factors are at least as large as the factor by which the highs must be decreased before being added to the quantized 'under' signal. Thus the SNR of the decoded highs signal in the blank areas of the image is no smaller than if the signal had been transmitted in the normal manner.</p>

<p><strong><em>Improving the Performance in the Presence of Frequency Distortion</em></strong><br />
 <br />
We have previously shown than transmitting the samples of an image in pseudorandom order disperses linear frequency distortion, including echoes, as random noise. When the dispersed noise appears in blank image areas, it is decreased in amplitude by adaptive demodulation. Thus the two techniques cooperate to provide an effective means to transmit accurately in the presence of frequency distortion. One of the principal technical hazards of the noise-margin method is crosstalk between the 'over' and 'under' signals when sharp transitions of the 'under' signal are distorted. Whenever the sum of the 'over' signal plus channel noise plus distortion improperly passes a decision level of the receiver quantizer, a single-level error is made in the 'under' signal and a click is produced in the 'over' signal. Scrambling of the 'over' signal moves most of its edge points away from the edge points of the 'under' signal. In the blank areas of the latter, the channel frequency distortion response produces little actual distortion. Thus most clicks in those areas are avoided. Click associated with large values of the 'over' signal that occur in blank areas of the 'under signal appear near edges and are at least partially masked. Clicks associated with small values of 'over' signal that are scrambled into proximity with edge values of the 'under' signal are decreased by adaptive demodulation. Although not very conspicuous when they appear in blank areas scrambling does raise the noise level in the blank areas of the NTSC (but not the EDTV) signal.</p>

<p>Two additional methods of decreasing the visibility of clicks in the 'over' signal are available. One is to use a specific click eliminator that replaces isolated improbably large signal values by an average of neighboring values. Another is to reverse the polarity of the 'over' signal on alternate levels of the 'under' signal. This amounts to employing a 'reflected' code similar to those often used in shaft-position indicators for much the same reason. At the cost of a very small degree of complication, this reduces the click amplitude considerably.</p>

<p><strong><em>Adding Information to Chrominance</em></strong></p>

<p>It is well established that the temporal bandwidth of the human visual system is lower for color than for luminance. In fact, 30 Hz chrominance flicker is largely undetectable, so that 15 Hz is sufficient for chrominance information.  This is the one respect in which NTSC exceeds the perceptual capability of viewers. In addition, in the original NTSC system, vertical chrominance resolution is the same as that of luminance, 480 lines per picture height. Actually, neither luminance or chrominance ever reaches this limit; if they did, interline flicker would result. At the same time, the horizontal resolution of chrominance is very low, and this does reduce the image quality. We propose to take advantage of the extra capacity of the chrominance channel by incorporating digital audio in the excess temporal bandwidth and to exchange reduced vertical for increased horizontal spatial resolution.<br />
The chrominance channel transmission rate is 65 pels/picture width times 480 lines/picture height times 30 frames/sec, or 936 Kpels/sec.  Half of this, or 468 Ksamples/sec is sufficient for good quality digital audio using one of the methods already proposed to the FCC Advisory Committee. A simple way to add this information to the chrominance signal is to cause each successive pair of chrominance frames to be identical, reducing the effective temporal rate to 15 fps.  The audio information would be added in opposite polarities on successive frames.  The two components would then be separable by a simple 'frame comb' in which successive frames are added to produce chrominance and subtracted to produce audio.  The audio information would appear to an NTSC receiver as 3- Hz color flicker.  The scheme is shown in Fig. 2.</p>

<p> Fig. 2. Hiding Extra Information in the Chrominance Channel.<br />
Separating luma and chroma with one-dimensional filters gives a chroma spectrum 65 samples/picture width wide, 480 lines/picture height high, and 30 frames/sec long.  By reducing the chroma frame rate to 15 frames/sec, half of this spectral region can be used for audio and data. The extra data looks like 30 frames/sec chroma flicker.  (Some additional degradation may occur on receivers using 3-d comb filters.) By reducing the vertical resolution by half, the horizontal chroma resolution can be doubled. The hidden information will look like 30 Hz chroma flicker around horizontal edges.</p>

<p>The exchange between vertical and horizontal chrominance resolution can be accomplished by several methods. One is to form a horizontal enhancement signal, shift it to baseband, and then to add it in opposite phase to successive scan lines of chrominance. The chrominance signal would first be vertically filtered and subsampled, with two successively transmitted lines of chrominance data being identical. The two signals could then be separated at the receiver by a line comb in strict analogy to the frame comb used for audio. The added data, being coherent with the reduced-resolution basic chrominance signal, would be of very low visibility.</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Dale Cripps</b>, <b>June 16, 2005 02:24 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 85
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
 				AND entry_id <> 85
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/2005/06/hdtv_newsletters_provide_the_history_of_the_movement.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
