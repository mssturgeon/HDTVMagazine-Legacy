<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 112";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Dale Cripps'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 112 AND placement_is_primary = 1";
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
	<meta name="keywords" content="working party, digital systems, dsc hdtv, — —, special panel, systems, system, atv, ATV, HDTV, hdtv, digital, ntsc, NTSC, television, channel, working, interference, proposed, Committee, committee, —, test, digicipher, DigiCipher" />
	<meta name="description" content="In early 1987 the Federal Communications Commission (FCC) was considering the reassignment of portions of the UHF spectrum from broadcasting to land mobile communications (&quot;two-way&quot;). Terrestrial broadcasters, concerned that the loss of spectrum would preclude their participation in high definition television (HDTV), sponsored a demonstration of terrestrial HDTV broadcasting in Washington, DC. The only operating system at that time was the MUSE (Multiple Sub-Nyquist Sampling Encoding) system which had been designed for satellite broadcasting by NHK, the Japan Broadcasting Corporation. The RF bandwidth requirement for the terrestrial demonstration was about 9 MHz - the television channel in the United States is 6 MHz - so two adjacent channels, 58 and 59, were used. The demonstration was very successful.
" />
	<title>HDTV Magazine Archive &amp; History - The Creation of The ATSC Standard - Dr. Robert Hopkins</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/the_creation_of_the_atsc_standard_-_dr_robert_hopkins';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('The Creation of The ATSC Standard - Dr. Robert Hopkins'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/history/2005/06/the_creation_of_the_atsc_standard_-_dr_robert_hopkins.php";
		if ($author[img] != '' && 5 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Dale Cripps" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">The Creation of The ATSC Standard - Dr. Robert Hopkins</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Dale Cripps</b><br />
				<?=$author_title?>
				Posted on <b>June 23, 2005</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category="></a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/2005/06/the_creation_of_the_atsc_standard_-_dr_robert_hopkins.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/history/2005/06/the_creation_of_the_atsc_standard_-_dr_robert_hopkins.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/history/2005/06/the_creation_of_the_atsc_standard_-_dr_robert_hopkins.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/2005/06/the_creation_of_the_atsc_standard_-_dr_robert_hopkins.php&amp;phase=2&amp;title=The%20Creation%20of%20The%20ATSC%20Standard%20-%20Dr.%20Robert%20Hopkins&amp;bodytext=In%20early%201987%20the%20Federal%20Communications%20Commission%20%28FCC%29%20was%20considering%20the%20reassignment%20of%20portions%20of%20the%20UHF%20spectrum%20from%20broadcasting%20to%20land%20mobile%20communications%20%28%22two-way%22%29.%20Terrestrial%20broadcasters%2C%20concerned%20that%20the%20loss%20of%20spectrum%20would%20preclude%20their%20participation%20in%20high%20definition%20television%20%28HDTV%29%2C%20sponsored%20a%20demonstration%20of%20terrestrial%20HDTV%20broadcasting%20in%20Washington%2C%20DC.%20The%20only%20operating%20system%20at%20that%20time%20was%20the%20MUSE%20%28Multiple%20Sub-Nyquist%20Sampling%20Encoding%29%20system%20which%20had%20been%20designed%20for%20satellite%20broadcasting%20by%20NHK%2C%20the%20Japan%20Broadcasting%20Corporation.%20The%20RF%20bandwidth%20requirement%20for%20the%20terrestrial%20demonstration%20was%20about%209%20MHz%20-%20the%20television%20channel%20in%20the%20United%20States%20is%206%20MHz%20-%20so%20two%20adjacent%20channels%2C%2058%20and%2059%2C%20were%20used.%20The%20demonstration%20was%20very%20successful.%0A&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p>Dr. Robert Hopkins<br />
April 1994</p>

<p>Robert Hopkins (Senior Member, IEEE) <em>joined the Advanced Television Systems Committee (ATSC) in January 1985 as the Executive Director. He is responsible for both the technical and administrative guidance of the ATSC. The ATSC is a standards organization sponsored by more than 50 companies involved in HDTV. The ATSC addresses issues concerning advanced television in order to provide private sector input to the Department of State for international conference preparation and to the Federal Communications Commission to assist in their HDTV decision-making process.</p>

<p>Previously, Dr. Hopkins was employed by RCA for twenty years. After several years at the RCA David Sarnoff Research Center, Princeton, NJ, where he was involved in television research, Dr. Hopkins transferred to the RCA Broadcast Systems Division, Gibbsboro, NJ where he held a number of positions including manager of strategic planning and manager, field camera products and engineering.  His most recent position at RCA was managing director of RCA Jersey Limited, Jersey Channel Islands, Great Britain, an overseas subsidiary that manufactured professional television equipment for a worldwide market.</p>

<p>While at RCA, Dr. Hopkins received two Outstanding Achievement Awards from RCA Laboratories.  He is a past chairman of SMPTE's Standards Committee and the Committee on New Technology. He was appointed the first chairman of the SMPTE Working Group on Digital Video Standards in 1977. He is a Fellow of SMPTE and a Senior Member of the IEEE. He serves as the United States representative on HDTV to the CCIR.</p>

<p>He received his Bachelor of Science degree in Electrical Engineering from Purdue University in West Lafayette, Indiana; Master of Science and PhD degrees from Rutgers University, New Brunswick, New Jersey; and is a graduate of the Harvard Business School Program for Management Development.</em> </p>

<p>____________________________________________</p>

<p>A public process has been in place in the United States for six years to establish an HDTV terrestrial broadcasting standard. The process, having moved through a planning phase, a competition phase, and an examination phase, has now entered a cooperation phase. Remarkable progress has been made - a testament to the process. During the next -few months, the American digital HDTV terrestrial broadcasting system will be tested, fully documented, and recommended to the FCC for adoption. </p>

<p><strong>I. INTRODUCTION</strong><br />
In early 1987 the Federal Communications Commission (FCC) was considering the reassignment of portions of the UHF spectrum from broadcasting to land mobile communications ("two-way"). Terrestrial broadcasters, concerned that the loss of spectrum would preclude their participation in high definition television (HDTV), sponsored a demonstration of terrestrial HDTV broadcasting in Washington, DC. The only operating system at that time was the MUSE (Multiple Sub-Nyquist Sampling Encoding) system which had been designed for satellite broadcasting by NHK, the Japan Broadcasting Corporation. The RF bandwidth requirement for the terrestrial demonstration was about 9 MHz - the television channel in the United States is 6 MHz - so two adjacent channels, 58 and 59, were used. The demonstration was very successful.</p>

<p>Shortly thereafter, a number of terrestrial broadcasters jointly petitioned the FCC to initiate a proceeding to explore the issues arising from the introduction of advanced television (ATV) technologies and their possible impact on the television broadcasting service {Note 2}. The broadcasters were concerned that alternative media (cable, satellite broadcasting, etc.) would be able to deliver HDTV to the viewing public while they could not without additional spectrum.</p>

<p>The FCC did initiate a proceeding (MM Docket No. 87-268) to consider the technical and public policy issues of ATV in July 1987. Late in 1987 the FCC formed the Advisory Committee on Advanced Television Service. The objective given to the Advisory Committee was:</p>

<p>The Committee will advise the Federal Communications Commission on the facts and circumstances regarding advanced television systems for Commission consideration of technical and public policy issues. In the event that the Commission decides that adoption of some form of advanced broadcast television is in the public interest, the Committee would also recommend policies, standards and regulations that would facilitate the orderly and timely introduction of advanced television services in the United States.</p>

<p>That proceeding, now six years in duration, has produced startling results. Six years ago it was conventional wisdom that more than 6 MHz would be required to broadcast HDTV. In Europe and Japan early decisions were made to obtain needed spectrum by using satellite broadcasting. That was not an acceptable solution in the United States where there are some 1,400 terrestrial broadcasters. Not only are they a very powerful force in our private sector, but they also provide a local public information service that is very important to our citizens. A different solution was required, and it has been found - digital compression. In 1987 virtually nobody believed that digital broadcasting would be possible for many years in the future. Now we know that the United States HDTV standard will be digital and it will fit into the 6 MHz channel.</p>

<p>In 1986 there were five proposed broadcasting systems that were being discussed in the United States. Two were satellite systems, two were augmentation systems {Note 3}, and one was a simulcast system {Note 4} that would have required a channel bandwidth of at least 9 MHz for the ATV signal.</p>

<p>One year later, there were ten systems - two more satellite systems, one more augmentation system, and two NTSC receiver-compatible systems.</p>

<p>In 1989 there were 21 systems - three analog 6 MHz simulcast proposals, five augmentation proposals, and ten NTSC receiver-compatible systems.</p>

<p>One year later only six systems remained in competition. Four were 6 MHz analog simulcast systems. The other two were NTSC receiver-compatible systems.</p>

<p>Consensus was forming. Satellite systems were out. So were augmentation systems. The public process was working well. Participants in the process put forth their ideas. The best survived peer review. Then, in June 1990, an even better idea was put forward - digital. A few months later there were three more digital simulcast proposals. When testing began in 1991, there was one NTSC receiver-compatible system, one analog simulcast system, and four digital simulcast systems. After the FCC made it clear that an HDTV simulcast system was preferred over an EDTV system {Note 5}, the NTSC receiver-compatible system was withdrawn from consideration.</p>

<p>The tests were completed in late 1992. Early in 1993, under critical examination, the analog simulcast system was eliminated from further consideration during a week-long meeting of a "Special Panel" established to compare the tested systems. The Special Panel recommended further testing of the remaining four digital systems.</p>

<p>Another significant event occurred in May 1993. The proponents of the four digital systems agreed to form a "Grand Alliance" and to merge their individual systems into a single system by incorporating the best features of each. At the time this paper was written, the Advisory Committee was examining the proposal of the Grand Alliance.</p>

<p>This paper will review the steps that have already been taken in this proceeding and those that are expected to occur in the future. The paper is organized to follow the phases of the public process. Those phases are: Planning, Competition, Examination, Cooperation, and Adoption.</p>

<p><strong>II. PLANNING PHASE</strong><br />
The planning phase began with the formation of the Advisory Committee in November 1987. The Advisory Committee was organized into three subcommittees and a number of working parties in each subcommittee Committee on Advanced Television Service.</p>

<p>-Planning Subcommittee <br />
-Technology Attributes and Assessments <br />
-Testing and Evaluation Specifications <br />
-Spectrum Utilization and Alternatives <br />
-Alternative Media and Broadcast Interface <br />
-Economic Factors and Market Penetration <br />
-ATV Systems Subjective Assessment <br />
-Audience Research <br />
-Creative Issues <br />
-Consumer/Trade Issues <br />
-Systems Subcommittee <br />
-Systems Analysis <br />
-Systems Evaluation and Testing <br />
-Systems Economic Assessment <br />
-Systems Standard <br />
-Implementation Subcommittee <br />
-Policy and Regulation <br />
-Transition Scenarios </p>

<p>A description of the objective of each of the Subcommittees and Working Parties can be found in [Ref. 2]. As may be expected, the Planning Subcommittee played the major role in the planning phase. This phase of the effort lasted about one year.</p>

<p>All Planning Subcommittee subgroups were quite active during that period. The different subgroups were defining the attributes of, and test specifications for, terrestrial ATV transmission systems, carrying out studies on possible spectrum schemes for ATV, examining the interfaces between broadcast and alternative media, examining the economic factors from a macro perspective, planning subjective assessment tests and test materials and market research programs, and examining the creative, consumer, and trade issues that might affect ATV broadcasting.</p>

<p>Much of the work of the Planning Subcommittee was completed during this phase. Two of the subgroups have continued their work throughout the entire process, however. Those two subgroups are the Working Party on Spectrum Utilization and Alternatives and the Working Party on ATV Systems Subjective Assessment. The work of the Working Party on Alternative Media Technology and Broadcast Interface continued through the competition and examination phases.</p>

<p><strong>III. COMPETITION PHASE</strong><br />
The beginning of the competition phase was marked by a week long meeting in November 1988, often referred to as "Hell Week" because of the long hours, the intensity of the meeting, and the strong technical challenges which had to be addressed "on the spot." This meeting was conducted by the Working Party on Systems Analysis to examine the system proposals that had been submitted by a number of proponents. Each proponent was given half a day to make a presentation on the proposed system at a block diagram level and respond to questions from a large number of technical experts. The primary purpose of the meeting was to "pre-certify" those systems that passed this first round of critical examination. A proposal received "pre-certification" if the technical experts believed that the proposed system could work and if the proponent appeared to have the wherewithal to construct the system and present it for testing.</p>

<p>Those systems that received "pre-certification" were examined in greater detail in a subsequent meeting. The timing of the second examination was about three months before testing was to begin on each particular system. A task force met with the proponent many times before the scheduled meeting of the working party to gain a detailed understanding of how the system worked and to propose tests specific to that system. Many more details of the system had to be divulged by the proponent during this period. During the meeting of the working party, the system received "final certification" if the technical experts were satisfied that they understood how the system worked and believed it would be ready for testing as scheduled.</p>

<p>The Working Party on Systems Evaluation and Testing began its work during this phase. Using the attributes and test specifications developed in the Planning Subcommittee, this working party began developing the specific test plans that would eventually be used in testing the surviving proposed systems. Not only was competition among the proponents evident in the design of their different systems, it was a factor in the working parties. Indeed, because the proponents had extensive technical expertise, they were welcomed as members of the working parties - in many cases they could offer the most perceptive critique of their competitors' systems. These inputs were most helpful in designing the tests.</p>

<p>The Working Party on Systems Standard developed a process that the Advisory Committee could use to recommend a single ATV system to the FCC. That process is shown in Figure 2. The process would begin with the identification of a set of "Selection Criteria," described as the key issues that must be considered in selecting a new television system. Once the Selection Criteria were identified, each proposed system would be analyzed with regard to the Selection Criteria. Systems found to satisfy the Selection Criteria would be compared with each other to determine the areas in which significant differences occur. From the process, superior systems could be identified. The process had feedback loops to ensure that the key issues were compared with sufficient sensitivity. The goal was to select the one system that would give the best balance in satisfying the Selection Criteria. </p>

<p>Ten issues were identified as being the most critical issues. They fell into three categories - Spectrum Utilization, Economics, and Technology - and are shown in Figure 3. </p>

<p>-Spectrum Utilization <br />
-Service Area <br />
-Accommodation Percentage <br />
-Economics <br />
-Cost to Broadcasters <br />
-Cost to Alternative Media <br />
-Cost to Consumers <br />
-Technology <br />
-Audio/Video Quality <br />
-Transmission Robustness <br />
-Scope of Services and Features <br />
-Extensibility <br />
-Interoperability Considerations </p>

<p><strong>IV. EXAMINATION PHASE</strong>A. <br />
<em>Analysis of Certified Systems</em><br />
During the examination phase, the certified systems were tested in three laboratories established for this specific purpose. In 1988 the Advanced Television Test Center (ATTC) was formed as a private, non-profit organization by television broadcasting organizations and other industry organizations to test the proposed systems. Cable Television Laboratories (CableLabs), a research and development consortium of cable television system operators, established a special test facility at the ATTC to conduct the cable tests. Also, the Advanced Television Evaluation Laboratory (ATEL) was established in Canada by the Department of Communications to undertake the video subjective test program using a large number of non-expert viewers.</p>

<p>Each certified system was tested for about two months at the ATTC and CableLabs facilities following the procedures developed by the Working Party on Systems Evaluation and Testing. While the system was at the ATTC, video tapes were made under various impaired and non-impaired conditions for use at ATEL in performing the subjective assessments developed by the Working Party on ATV Systems Subjective Assessment. The subjective assessments on each system also required about two months.Many of the tests conducted by ATTC and ATEL were designed to provide information for the Working Party on Spectrum Utilization and Alternatives so that an analysis could be performed to determine the spectrum utilization of each tested system.</p>

<p>As details of the certified systems became known, the Working Party on Systems Economic Assessment analyzed each system to determine approximately what the cost of the system would be to broadcasters and to consumers. Broadcasters' costs were defined as the cost to go on-air with a network feed (i.e., without studio/production equipment at the local station). Consumers' costs were defined as 2.5 times the material cost of a 34" widescreen direct view receiver or a 56" widescreen CRT type projector.</p>

<p>During this phase, the Working Party on Transition Scenarios was active examining issues which would need attention in making the transition to ATV broadcasting. With the involvement of the Working Party on Systems Economic Assessment, each certified system was examined to determine if there were any special needs posed by that system. The Working Party on Policy and Regulation examined issues related to regulations that might be required for the transition.</p>

<p>Toward the end of this phase, after most of the proposed systems had become digital systems, the Working Party on Alternative Media Technology and Broadcast Interface was given a new assignment to examine issues of interoperability with computers and other digital media. All systems were individually analyzed to determine their degree of interoperability.</p>

<p>The Working Party on Systems Standard was charged with drafting the ATV System Recommendation report for the Advisory Committee. The working party agreed to an outline for the report and drafted the portion defining the Selection Criteria before the first test data were available. Once test data became available, the Working Party on Spectrum Utilization and Alternatives and the Working Party on Systems Standard reviewed and summarized the test data for each system. Other information for the report was supplied by other working parties. All this information was edited and integrated into the report by the Working Party on Systems Standard.</p>

<p>In March 1992 the Advisory Committee agreed to appoint a Special Panel that would take the results of the analyses of the individual proponent systems in the ATV System Recommendation report and formulate recommendations for the Advisory Committee's consideration. The Special Panel membership included Advisory Committee staff leaders and other knowledgeable participants in the Advisory Committee who were not affiliated with any system proponent. The responsibility for drafting a chapter of the report comparing the proposed systems was assigned to the Special Panel.</p>

<p><strong>B. Comparison of Certified Systems</strong><br />
The ATV System Recommendation report gives the results comparing the tested systems. The report is available in [Ref. 3]. A summary of the comparative results is given in this section {Note 6}.</p>

<p>Five HDTV simulcast systems were examined in the report. Narrow-MUSE, an analog system proposed by NHK; DigiCipher, a digital system proposed by The American Television Alliance (General Instrument Corporation and the Massachusetts Institute of Technology); Digital Spectrum Compatible HDTV (DSC-HDTV), a digital system proposed by Zenith and AT&T; Advanced Digital HDTV (AD—HDTV), a digital system proposed by the Advanced Television Research Consortium (ATRC) which includes David Sarnoff Research Center, North American Philips, Thomson Consumer Electronics, NBC, and Compression Labs, Incorporated; and Channel Compatible DigiCipher (CCDC), a second digital system proposed by the American Television Alliance.</p>

<p><strong>1)  Special Panel findings and recommendations: </strong><br />
The Special Panel met on February 8 - 11, 1993. The resulting findings and recommendations follow.</p>

<p><em>Spectrum utilization findings:</em><br />
The analysis conducted by the Advisory Committee clearly demonstrates that a substantial difference exists in spectrum utilization performance between the analog Narrow-MUSE system and the four all-digital systems. The differences among the four digital systems generally are far less pronounced, however. Based on this analysis, it would appear that Narrow-MUSE will not prove to be a suitable terrestrial broadcasting ATV system for the United States. <br />
The Special Panel notes that many system proponents have proposed improvements to their systems in the area of spectrum utilization. The Special Panel finds that the system improvements, primarily those identified by its Technical Subgroup as ready for implementation in time for testing, may lead to improvements in spectrum utilization and should be subjected to testing as soon as possible. <br />
The Special Panel finds that the degree of interference from ATV-into-NTSC is recognized as an area of concern in certain markets. The Special Panel finds that the issue of ATV-into-NTSC interference, including interference to BTSC audio, should be addressed in the remaining stages of the system selection process, including the examination of refined allotment/assignment techniques, the study of possible beneficial effects of system improvements, and the consideration of any mitigations which might be achieved by transitional implementation policies. </p>

<p><strong>Economics findings:</strong><br />
No significant cost differences among the five proponent systems, either in costs to consumers or to broadcasters, are evident. Thus, based on cost alone, there is no basis to discriminate among systems. However, the additional benefits offered to broadcasters and others by the digital systems were noted as significant. </p>

<p><strong>Technology findings:</strong></p>

<p>As a result of the testing process, the Advisory Committee is confident that a digital terrestrial advanced television system can provide excellent picture and sound quality. All of the system proponents have proposed refinements that are likely to enhance the audio and video quality beyond that measured in the testing process. <br />
A variety of transmission formats was evaluated. The transmission robustness analysis conducted by the Advisory Committee clearly reveals that an all-digital approach is both feasible and desirable. All of the system proponents have proposed refinements that are likely to enhance robustness beyond that measured in the testing process. </p>

<p>An all-digital system approach is important to the scope of ATV services and features and in the areas of extensibility and interoperability. All four digital proponents have committed to a flexible packetized data transport structure and universal headers/descriptors. Progressive-scan/square-pixel transmission is considered beneficial to creating synergy between terrestrial ATV and national information initiatives. As well, scalability at the transmission data stream would permit trade-offs in "bandwidth on demand" network environments. <br />
Recommendations:</p>

<p>While all the proponents produced advanced television systems, the Special Panel notes that there are major advantages in the performance of digital HDTV systems in the United States environment and recommends that no further consideration be given to analog-based systems. The proponents of all four digital HDTV systems - DigiCipher, DSC-HDTV, AD-HDTV, and CCDC - have provided practical digital HDTV systems that lead the world in this technology. Because all four systems would benefit significantly from further development, the Special Panel does not recommend any one of these systems for adoption as a United States terrestrial ATV transmission standard at this time. Rather, the Special Panel recommends that these four finalist proponents be authorized to implement their improvements as submitted to the Advisory Committee and approved by the Special Panel's Technical Subgroup. <br />
The Special Panel further recommends that the approved system improvements be ready for testing not later than March 15, 1993, and that these improvements be laboratory and field tested as expeditiously as possible. The results of the supplemental tests, along with the already planned field tests, would provide the necessary additional data needed to select a single digital system for recommendation as a United States terrestrial ATV transmission standard. </p>

<p><strong>2)  Spectrum utilization comparisons:   </strong></p>

<p>Two spectrum utilization selection criteria were compared: accommodation percentage and service area. "Accommodation percentage" specifies the fraction of existing NTSC television stations that could be assigned an ATV channel. "Service area" refers to the interference-limited coverage area of new ATV stations.</p>

<p>The analysis of spectrum usage of the proposed systems employed an allotment approach developed by the FCC staff and a service and interference model developed by the Working Party on Spectrum Utilization and Alternatives. Combining the two in a computer program permitted development of approximate allotment/assignment plans and a comparison of the service expected to be provided by each system, if implemented, with the service provided by the NTSC system currently in use.</p>

<p>The computer program seeks, station-by-station, to match or exceed the current interference-limited NTSC service area with future companion ATV service area. The analysis includes consideration of vacant noncommercial allotments as well as authorized stations and pending applications {Note 7}. Station locations and antenna heights above average terrain are assumed to be the same for both the NTSC and ATV services. Other input parameters to the program are the planning factors applicable to all ATV systems (see Figure 4) and factors specific to each ATV system (see Figure 5) as determined by the test programs at the ATTC and ATEL. </p>

<p>_________________________________<br />
 <br />
Figure 4. Receiver planning factors applicable to all ATV systems.</p>

<p> Low VHF High VHF UHF <br />
Antenna Impedance (ohms) 75.0 75.0 75.0 <br />
Bandwidth (MHz) 6.0 6.0 6.0 <br />
Thermal Noise (dBm) -106.2 -106.2 -106.2 <br />
Noise Figure (dB) 10.0 10.0 10.0 <br />
Frequency (MHz) 69 194 615 <br />
Antenna Factor (dBm/dBu) -111.7 -120.7 -130.7 <br />
Line Loss (dB) 1.0 2.0 4.0 <br />
Antenna Gain (dB) 4.0 6.0 10.0 <br />
Antenna F/B Ratio (dB)* 10 12 14 </p>

<p><br />
* In addition to F/B ratio, a formula is employed for the forward lobe simulating an actual receiving antenna pattern. </p>

<p><br />
_______________________________________</p>

<p>Figure 5. System-specific planning factors (D/U in dB).</p>

<p>CARRIER-TO-NOISE N-MUSE DigiCipher DSC-HDTV AD-HDTV CCDC <br />
 +38 +16.0 +16.0 +18.4 +15.4 <br />
      <br />
CO-CHANNEL N-MUSE DigiCipher DSC-HDTV AD-HDTV CCDC <br />
ATV-into-NTSC +16.8 +35 +35 +34 +36 <br />
NTSC-into-ATV +21 +7.6 +3.5 +0.50 +8.1 <br />
ATV-into-ATV +31 +16.4 +18.2 +19.1 +16.6 <br />
     <br />
ADJACENT-CHANNEL N-MUSE DigiCipher DSC-HDTV AD-HDTV CCDC <br />
Lower ATV-into-NTSC —31 —13.5 —17.2 —16.0 —17.8 <br />
Upper ATV-into-NTSC —12.0 —21 —7.5 —8.9 —17.0 <br />
Lower NTSC-into-ATV +28 —30 —43 —38 —37 <br />
Upper NTSC-into-ATV —11.8 —24 —42 —36 —37 <br />
Lower ATV-into-ATV —15.5 —23 —35 —33 —32 <br />
Upper ATV-into-ATV +16.6 —23 —36 —16.8 —32 </p>

<p><br />
___________________________</p>

<p> <br />
An initial program run using NTSC provided the reference for each of the ATV systems tested. The program output includes Grade B coverage area and interference-limited service area for each of the 1,657 authorized and applied-for television facilities in the August 1, 1992 FCC data base.</p>

<p>The analysis was conducted under two allotment scenarios (using both VHF and UHF channels for ATV stations, and using only UHF channels) and two sets of interference constraints (considering only co-channel interference, and considering both co-channel and adjacent-channel interference). In addition, the impact of taboos {Note 8} was assessed by recalculating coverage and interference for each scenario assuming the taboo performance measured in the laboratory. The Working Party on Spectrum Utilization and Alternatives determined that the analysis should be considered in the following priority order: 1) co-channel and adjacent-channel interference, 2) only co-channel interference, and 3) co-channel, adjacent-channel and taboo interference.</p>

<p>With the exception of one system - Narrow-MUSE - allotment/assignment schemes could be created to accommodate 100% of existing NTSC broadcast stations. Narrow-MUSE allotment/assignment plans accommodated 77.2% or 73.7% under the VHF/UHF and UHF-only channel availability options, respectively. Tradeoffs exist in the process of allotting ATV channels. While attempts were made to match the ATV coverage with that of companion NTSC stations, the provision of ATV allotments was accomplished by reducing ATV coverage areas for some stations and by introducing some new interference to the coverage areas of a portion of the set of existing NTSC stations.</p>

<p>Figure 6 depicts the interference-limited service area of each ATV station, during the transition period, relative to the interference-limited service area of its companion NTSC station under the VHF/UHF Scenario, taking into account both co-channel and adjacent-channel constraints. In this graph, the 1,657 current, and planned, NTSC stations are placed in order of decreasing ATV to NTSC service area ratio. Examination of the graphs reveals that about 1200 of the ATV stations would have an ATV service area equal to or greater than the size of their companion NTSC service area with any one of the four digital ATV systems. </p>

<p><br />
________________________________________</p>

<p>Figure 6. Interference-limited service area of each ATV station relative to the interference-limited service area of its companion NTSC station (co-channel and adjacent-channel constraints).</p>

<p> <br />
______________________________________</p>

<p><br />
Examination of the ATV coverage during and after the transition revealed that the performance of the DSC-HDTV and CCDC systems was slightly better than that of the DigiCipher and AD-HDTV systems. The performance of the Narrow-MUSE system in this category was significantly worse than that of the four all-digital systems.</p>

<p><strong>3)  Economic comparisons: </strong> <br />
There were some nominal cost differences among the systems in the estimated costs to both consumers and broadcasters. However, these differences in costs were of a minor magnitude and were judged to be indistinguishable.</p>

<p>Broadcasters costs were developed for each item on a station block diagram for each of the proposed ATV systems. It was assumed that the station's existing tower has sufficient capacity for installation of the new ATV antenna and transmission line and that the station's equipment space has room for additional gear. Additionally, the analysis was based on the use of a compressed NTSC signal multiplexed into the same STL with the ATV signal. The equipment cost for a station ranged from $1,700,500 to $1,785,500 for the five proposed systems.</p>

<p>Cost to consumers was based on technology predictions for 1998 using a manufacturing quantity of 1 million receivers. The proponents provided block diagrams, gate counts, and pin counts for suggested chip sets for their systems. It was generally recognized that the cost of the display would have a major impact on the cost of the receiver. The estimated total material cost for a 34" widescreen direct view receiver ranged from $978 to $1,048 for the five proposed systems. The estimated total material cost for a 56" widescreen projection receiver ranged from $1,494 to $1,564. Note that the estimated cost for the display was 70% of the total for both the 34" receiver and the 56" receiver. Note also that it was estimated that if selling price were 2.5 times the material cost, the 34" direct view receiver selling price would be about $2,530 and the 56" projection receiver selling price would be about $3,830.</p>

<p><strong>4)  Technology comparisons:  </strong><br />
The Special Panel examined five selection criteria under the heading Technology: Quality, Transmission, Scope of Services and Features, Extensibility, and Interoperability Considerations. Of the five, the first two - quality and transmission - were based on actual system testing. The other three were primarily the subject of detailed analyses of the systems as certified.</p>

<p>The Special Panel concluded that four excellent digital HDTV systems were developed as the result of this process. Digital ATV transmission is completely viable for over-the-air broadcasting and for transmission by the alternative media of cable and satellite. The overall picture quality of two systems came remarkably close to the quality of the 1125-line high-definition studio reference.</p>

<p>The extensive measured data and subjective assessments of the systems, however, also revealed the magnitude of the challenges associated with achievement of high overall picture and sound quality while simultaneously ensuring adequate coverage, transmission robustness, and acceptably low interference in a simulcast environment - all within the bounds of a reasonable average effective radiated power.</p>

<p>The Special Panel's examination further revealed that there are likely to be pragmatic tradeoffs required between the fundamental ATV requirements (under the quality and transmission criteria) and the sometimes conflicting but desirable capabilities described under the criteria of scope of services and features, extensibility and interoperability.</p>

<p>In Basic Received Quality {Note 9}, DigiCipher and AD-HDTV were judged, on average, only about 0.3 CCIR grades lower in quality than the 1125-line studio reference for most segments of test material; the other systems exhibited lower performance (see Figure 7). All systems, however, exhibited visible weaknesses in one or more tests designed to address other matters relating to quality (e.g., noisy source material, multiple encode/decode operations, etc.). </p>

<p><br />
_______________________________________</p>

<p>Figure 7. Average differences between quality judgments for the 1125-line studio quality reference and for each of the proposed ATV systems.</p>

<p> Color <br />
__________________________________</p>

<p>For still material, the ATV systems did not differ significantly overall. For live video and for film, however, the DigiCipher and AD-HDTV systems exhibited significantly better performance than the other systems. For a graphic sequence that stressed vertical and temporal performance, the DSC-HDTV and CCDC systems performed best.</p>

<p>For noisy source material, the DigiCipher and AD-HDTV systems performed significantly better than the other systems. For scene cuts, the AD-HDTV system performed best. For material subjected to concatenated encode/decode operations, the DigiCipher system performed best. For material designed to stress the source-coding algorithms of the four all-digital systems, the DigiCipher and CCDC systems performed best. And, finally, examinations of quality achieved under extended coverage conditions (made only for Narrow-MUSE, DSC-HDTV, and AD-HDTV) revealed a clear superiority for the Narrow-MUSE system.</p>

<p>Overall, these results show a clear advantage for the DigiCipher and AD-HDTV systems in terms of video quality. However, they also point to the necessity for improvement, even in the two leading systems.</p>

<p>In interpreting the results, three mitigating factors should be considered. First, the video and film material used in tests of the progressively scanned ATV systems (i.e., DSC-HDTV and CCDC) exhibited high levels of random noise, as well as horizontally coherent noise. Although this may have affected adversely the performance of these two systems, it is not possible to quantify the extent to which their performance would have been affected. Second, it is likely that all systems suffered from deficiencies in the prototype hardware brought to test. And, finally, since the time of test, all system proponents claim to have made improvements in image quality.</p>

<p>A number of tests related to transmission robustness were conducted. Ability to tolerate discrete, static echoes showed an advantage of about 20 dB to the digital systems. Among the digital systems, AD-HDTV was judged significantly superior for this attribute.</p>

<p>Flutter is time-varying multipath. DigiCipher and CCDC exhibited significantly superior tolerance of this impairment.</p>

<p>With regard to impulse noise, DSC-HDTV was significantly better than the other systems.</p>

<p>CCDC performed best for in-band discrete frequency rejection. DSC-HDTV performed best for out of band discrete frequency rejection.</p>

<p>The DigiCipher and CCDC systems each exhibited resistance to composite second order intermodulation distortion that was significantly greater than that of the other systems. The DSC-HDTV and AD-HDTV systems revealed significantly greater immunity to composite triple beat products than did the remaining systems.</p>

<p>All of the digital systems exhibited substantially greater immunity to phase noise than did the Narrow-MUSE system.</p>

<p>The DigiCipher and CCDC systems tolerated considerably greater residual frequency modulation than did the remaining systems.</p>

<p>The DigiCipher, DSC-HDTV, and CCDC systems demonstrated a substantially wider local oscillator pull-in range than the other systems. The DSC-HDTV system range exceeded ± 100 kHz, the maximum value prescribed in the formal test procedure.</p>

<p>DigiCipher and CCDC were most robust to co-channel interference from ATV. AD-HDTV was best at rejecting co-channel interference from NTSC. Narrow-MUSE performed significantly better than the digital systems for ATV-into-NTSC co-channel interference. All digital systems required about the same signal level to cause co—channel interference into NTSC. (See Figure 5.)</p>

<p>Narrow-MUSE performed significantly better than the digital systems on lower adjacent-channel ATV-into-NTSC interference by causing the least interference. Among the digital systems, DSC-HDTV performed best in rejecting ATV-into-ATV and NTSC-into-ATV adjacent-channel interference. DigiCipher and CCDC caused the least upper adjacent-channel ATV-into-NTSC interference. DSC-HDTV, AD-HDTV and CCDC caused the least lower adjacent-channel ATV-into-NTSC interference. (See Figure 5.)</p>

<p>Narrow-MUSE performed significantly better than the digital systems for ATV taboo interference into NTSC. Among the digital systems, DSC-HDTV had the best all-around ability to reject taboo interference on the nine channels tested; however, the performance of all digital systems was close.</p>

<p>The DigiCipher, DSC-HDTV, and CCDC systems completed a channel change under unimpaired conditions in approximately one second, versus substantially longer times recorded for Narrow-MUSE and for AD-HDTV.</p>

<p>The channel acquisition test measured the time required to acquire the signal and display a recognizable picture under a variety of impairment conditions; signal conditions were always above the threshold of visibility (TOV). The performance of DigiCipher, DSC-HDTV, and CCDC was judged superior to the other systems. The three cited systems were able to deliver a recognizable image within about one second under conditions of moderate impairment.</p>

<p>To determine failure and recovery picture appearance, signal strength was reduced below threshold level and then increased above threshold; the resulting image behavior was observed. This test simulated signal fading in fringe areas for digital systems. In general, all systems "froze" the image as the signal fell below threshold. Typically, the image became "blocky" and dissolved into other characteristic artifacts. Recovery was most rapid for AD-HDTV (much less than one second). DigiCipher recovered with characteristic panel wiping, lasting about 1/3 second. CCDC recovery generally consumed about 1/2 second but could last longer than one second. DSC-HDTV required the longest recovery period, generally 2-5 seconds. The speed and subjective appearance of AD-HDTV's recovery were judged significantly superior to the other systems.</p>

<p>The broadcast portion of the multiple impairment test determined the point of acquisition (POA) - which needed only to be a "recognizable" image, not a "watchable" one - under different conditions of random noise and co-channel impairments. The test results show that DSC-HDTV could acquire signal under the worst combination of these impairments, with AD-HDTV very close in performance. DigiCipher and CCDC required a significantly more favorable combination of conditions for signal acquisition. The cable portion of the multiple impairment test measured TOV under different combinations of random noise and composite triple beat. The test results show that DigiCipher, DSC-HDTV, and AD-HDTV exhibited better performance than CCDC. All of the digital ATV systems, however, are expected to operate with adequate margins of signal-to-noise and for composite triple beat on existing cable systems designed for carriage of NTSC signals using the nominal ATV power levels tested.</p>

<p>Narrow-MUSE, as expected from its analog signal format, exhibited gradual degradation of image quality with decreasing C/N. All of the digital systems had sharp thresholds, with image quality degrading from an unimpaired picture (TOV) to an unusable picture (POU) over less than a 2 dB change in C/N. Based on certification documents, this performance was expected for DigiCipher and CCDC. The claimed gradual thresholds of DSC-HDTV and AD-HDTV were judged to have utility only for short, temporary, and infrequent signal fading. The Special Panel found that no video performance advantages were found in the forms of gradual signal degradation tested, although it is desirable to maintain audio service during momentary disruptions in the picture.</p>

<p>The peak-to-average power ratios of DigiCipher and CCDC were judged significantly superior among the digital systems. It was noted that AD-HDTV required significantly more average ERP than the other systems.</p>

<p>Scope of Services and Features considered the need of an ATV system to support features and capabilities beyond those explicit in other selection criteria. All systems provided for data transmission. With respect to data, the AD-HDTV system was judged superior because it used a packetized data structure with headers and descriptors that, in general, is important for providing system flexibility. With respect to addressing, the AD-HDTV system was considered better than the other digital systems due to its ability to reassign its entire 18.5 Mbits/sec to addressing keys.</p>

<p>It was concluded that the use of a packetized data structure with universal headers and descriptors provides important flexibility for extensibility. For example, if a higher data rate channel is used to distribute programming to television stations, additional packets (with appropriate headers and descriptors) could provide higher quality images for post-production processing. Overall, the digital systems ranked better than the Narrow-MUSE system; however, there were no significant differences among the digital systems.</p>

<p>Interoperability considered delivery over alternative media (cable, satellite, packet networks), transcoding (with NTSC, film, and format conversion to other video standards), integration with computers and digital technology, interactive systems, the use of headers/descriptors, and scalability. Progressive scanning and square pixels are important for computer and other image applications. For interoperability with computers, DSC-HDTV and CCDC ranked better than the other systems. Only AD-HDTV had its final proposal for a packetized data structure and headers and descriptors implemented at the time the system was tested by ATTC, and it received the highest rating in the paper analysis on these characteristics. All digital system proponents now recognize the importance of a packetized data structure combined with headers and descriptors as a critical enabling concept for ATV flexibility. With respect to format conversion, Narrow-MUSE does not require conversion to 1125/60, and AD-HDTV's use of MPEG-1 provides the possibility of interoperability with MPEG (Moving Picture Experts Group) applications. The four digital systems were judged better than Narrow-MUSE for interoperability with digital technology, NTSC, film, still images, and interactive systems. Note that latency and acquisition time are important for interactive systems, but have not been completely determined. All five systems were judged suitably interoperable with satellite and cable.</p>

<p><strong>V. COOPERATION PHASE</strong>On <br />
May 24, 1993 the four digital system proponents announced that they had formed a "Grand Alliance" which would make a single system proposal to the Advisory Committee combining the best features of each of the individual proposals. The proposed system supported two scanning formats. The first proposed format had 720 active lines, 1280 pels (picture elements) per active line, and 60 frames per second scanned progressively. The second proposed format used interlaced scanning with 960 active lines and 1408 or 1728 pels per active line. The proposed ultimate target was 960 active lines with 1728 pels per active line scanned progressively at 60 frames per second. The proposed compression algorithm was similar to MPEG-2 with enhancements from each of the original individual systems. A single audio system was not proposed, but was to be selected from among the original individual systems after performing a comparative test. The proposed transport mechanism was packetized and similar to MPEG-2. A single modulation technique was not proposed, but was to be selected from among the original individual systems after performing a comparative test.</p>

<p>The Advisory Committee's Technical Subgroup examined the Grand Alliance proposal. At the request of the Advanced Television Systems Committee (ATSC), the Technical Subgroup suggested that the 960 active line format should be replaced with a 1080 active line format containing 1440 or 1920 pels per active line. The Technical Subgroup suggested also that the video compression algorithm and the transport mechanism should be compatible with MPEG-2. Finally, the Technical Subgroup decided to conduct a paper study of the COFDM (coded orthogonal frequency division multiplex) modulation technique.</p>

<p>At a meeting of the Technical Subgroup on October 21, 1993 the Grand Alliance announced that the 960 active line format would be replaced with a 1080 active line format, that both 60 Hz and 59.94 Hz vertical rates would be supported, that the video compression algorithm would be MPEG-2 (main profile, high level), that MPEG-2 transport mechanism would be used, and that the Dolby AC-3 audio system would be used. The Grand Alliance also announced that a test of three modulation techniques - 4 level VSB (vestigial-sideband), 6 level VSB, and 32 QAM (quadrature amplitude modulation) - would be conducted in January 1994. With the choice of a modulation technique, the Grand Alliance system will be fully specified. Key specifications now include:</p>

<p>Video compression: MPEG-2 main profile, high level <br />
Scanning formats supported: 720 lines x 1280 pels, 60 Hz, progressive scanning </p>

<p>1080 lines x 1920 pels, 60 Hz, interlaced scanning <br />
Film modes: 720 x 1280 at 30 Hz and 24 Hz, progressive scanning <br />
1080 x 1920 at 30 Hz and 24 Hz, progressive scanning <br />
Audio compression: Dolby AC-3 <br />
Transport technique: MPEG-2 </p>

<p><br />
Construction of the prototype is beginning and will continue through the summer of 1994. Laboratory tests are expected to begin in October 1994. Field tests are expected to be conducted in Charlotte, North Carolina in early 1995.</p>

<p><strong>VI. ADOPTION PHASE</strong><br />
In June 1992, the ATSC offered to document the selected ATV system for the FCC. In its filing with the FCC, ATSC noted a number of standards efforts needed and suggested the appropriate bodies to perform the various standardization functions. Those bodies included the founders and Charter Members of the ATSC - Institute of Electrical and Electronics Engineers, Electronic Industries Association, National Association of Broadcasters, National Cable Television Association, and Society of Motion Picture and Television Engineers. ATSC was founded to coordinate the development of voluntary national technical standards for advanced television systems. ATSC's fifty plus members, in addition to its Charter Members, are manufacturers of professional and consumer equipment, broadcasters, cable operators, satellite operators, motion picture companies, and universities.</p>

<p>With the formation of the Grand Alliance, ATSC plans to begin the documentation as the full specifications become available. This action will be concurrent with prototype construction and laboratory and field testing of the system.</p>

<p>Adoption of the system by the FCC could happen the first half of 1995.</p>

<p><strong>ACKNOWLEDGMENT</strong><br />
Much of this paper (section IV - B) was extracted from the ATV System Recommendation report, often quoting directly from the report. As such, it represents the work of a large number of dedicated people. Note that the author of this paper served as the chairman of the working party of the Advisory Committee that wrote the first draft of the report and as the chairman of the "Special Panel" which completed the report.</p>

<p><em>REFERENCES</em><br />
[1] B. Crutchfield, "Broadcasting High Definition Television," 15th International Montreux Television Symposium - Broadcast Sessions Record, pp. 158-165, June 1987.</p>

<p>[2] R. Hopkins and K. P. Davies, "HDTV Emission Systems Approach in North America," ITU Telecommunication Journal, vol. 57, pp. 330-336, May 1990.</p>

<p>[3] "ATV System Recommendation," IEEE Transactions on Broadcasting, volume 39, number 1, pp. 2-245, March 1993.</p>

<p>NOTES<br />
{1} NHK also demonstrated a terrestrial microwave delivery system at the same time.</p>

<p>{2} In this paper, the term ATV includes HDTV. The FCC has defined ATV to include advanced television features ranging from improvements to the current NTSC system to HDTV. Note that only HDTV systems are under consideration by the FCC at this time.</p>

<p>{3} The NTSC signal would be broadcast in its current channel while the additional information needed to complete the ATV signal would be broadcast in a different channel.</p>

<p>{4} The NTSC signal would be broadcast in its current channel while the same program in the ATV signal format would be broadcast in a different channel.</p>

<p>{5} EDTV systems have spatial resolution higher than NTSC but lower than HDTV. EDTV systems may have a wide aspect ratio. HDTV is generally assumed to have twice the spatial resolution of NTSC (horizontal and vertical) and a wide aspect ratio.</p>

<p>{6} The information presented in this section does not necessarily represent the view of the author. The information is extracted from the ATV System Recommendation report, often quoting directly from the report.</p>

<p>{7} In Puerto Rico, the large number of television stations assigned within the limited area of the island precludes the development of a plan providing 100% accommodation by the methodology employed herein. As a result, those stations are not included in the analysis. The comparative analysis attempted to protect all existing noncommercial vacant allotments; however, it did not attempt to assign them an ATV channel.</p>

<p>{8} The UHF taboo channels arise because of interference mechanisms in receivers. For each UHF channel assigned to a given geographic location, 14 prohibited assignments result at that location in addition to the adjacent channels which are prohibited at both UHF and VHF.</p>

<p>{9} The video quality subjective judgments were made using the CCIR Five-Point (five-interval) Continuous Quality Scale with the terms "Excellent", "Good", "Fair", "Poor" and "Bad". This method uses double presentations of reference and test signals in blind, pseudo-random orders. The responses were graded from 0 to 100, where 0-20 corresponds to "Bad", 20-40 to "Poor", 40-60 to "Fair", 60-80 to "Good" and 80-100 to "Excellent". The twenty-three video selections were compared using a t-test with an individual error rate of 5%. Emphasis is placed on describing the size of the differences between the 1125-line reference and the test signal using averages and ranges, rather than on statistical significance.</p>

<p>BIOGRAPHY<br />
Robert Hopkins (Senior Member, IEEE) joined the Advanced Television Systems Committee (ATSC) in January 1985 as the Executive Director. He is responsible for both the technical and administrative guidance of the ATSC. The ATSC is a standards organization sponsored by more than 50 companies involved in HDTV. The ATSC addresses issues concerning advanced television in order to provide private sector input to the Department of State for international conference preparation and to the Federal Communications Commission to assist in their HDTV decision-making process.</p>

<p>Previously, Dr. Hopkins was employed by RCA for twenty years. After several years at the RCA David Sarnoff Research Center, Princeton, NJ, where he was involved in television research, Dr. Hopkins transferred to the RCA Broadcast Systems Division, Gibbsboro, NJ where he held a number of positions including manager of strategic planning and manager, field camera products and engineering.  His most recent position at RCA was managing director of RCA Jersey Limited, Jersey Channel Islands, Great Britain, an overseas subsidiary that manufactured professional television equipment for a worldwide market.</p>

<p>While at RCA, Dr. Hopkins received two Outstanding Achievement Awards from RCA Laboratories.  He is a past chairman of SMPTE's Standards Committee and the Committee on New Technology. He was appointed the first chairman of the SMPTE Working Group on Digital Video Standards in 1977. He is a Fellow of SMPTE and a Senior Member of the IEEE. He serves as the United States representative on HDTV to the CCIR.</p>

<p>He received his Bachelor of Science degree in Electrical Engineering from Purdue University in West Lafayette, Indiana; Master of Science and PhD degrees from Rutgers University, New Brunswick, New Jersey; and is a graduate of the Harvard Business School Program for Management Development.</p>

<p> </p>
		</div>
	</div>
	<p class="posted">Posted by <b>Dale Cripps</b>, <b>June 23, 2005 09:21 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 112
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
 				AND entry_id <> 112
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/2005/06/the_creation_of_the_atsc_standard_-_dr_robert_hopkins.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
