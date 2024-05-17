<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 92";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Dale Cripps'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 92 AND placement_is_primary = 1";
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
	<meta name="keywords" content="grand alliance, multichannel ntsc, advanced television, transition channel, second channel, HDTV, hdtv, broadcasters, service, spectrum, any, FCC, fcc, channel, services, digital, television, ntsc, NTSC, flexible, cofdm, COFDM, quality, letter, should" />
	<meta name="description" content="Broadcasters received an early St. Patrick's Day gift on March 16 when the House Telecommunications Subcommittee passed the markup of the Tauzin Amendment to H.R. 3636 with a unanimous voice vote. The amendment grants broadcasters flexible use of the transition channel for ATV. Subcommittee Chairman, Representative Edward Markey, decried &quot;Only those who created the Communications Act in 1934 have accomplished as much as we achieved here in 1994,&quot; hailing it as the most significant communications policy change in 60 years." />
	<title>HDTV Magazine Archive &amp; History - 1994 - HDTV: Moving Toward the Tube or Down It? by Shawn Welch</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/1994_-_hdtv_moving_toward_the_tube_or_down_it_by_shawn_welch';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('1994 - HDTV: Moving Toward the Tube or Down It? by Shawn Welch'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/history/2005/06/1994_-_hdtv_moving_toward_the_tube_or_down_it_by_shawn_welch.php";
		if ($author[img] != '' && 5 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Dale Cripps" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">1994 - HDTV: Moving Toward the Tube or Down It? by Shawn Welch</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Dale Cripps</b><br />
				<?=$author_title?>
				Posted on <b>June 17, 2005</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category="></a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/2005/06/1994_-_hdtv_moving_toward_the_tube_or_down_it_by_shawn_welch.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/history/2005/06/1994_-_hdtv_moving_toward_the_tube_or_down_it_by_shawn_welch.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/history/2005/06/1994_-_hdtv_moving_toward_the_tube_or_down_it_by_shawn_welch.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/2005/06/1994_-_hdtv_moving_toward_the_tube_or_down_it_by_shawn_welch.php&amp;phase=2&amp;title=1994%20-%20HDTV%3A%20Moving%20Toward%20the%20Tube%20or%20Down%20It%3F%20by%20Shawn%20Welch&amp;bodytext=Broadcasters%20received%20an%20early%20St.%20Patrick%27s%20Day%20gift%20on%20March%2016%20when%20the%20House%20Telecommunications%20Subcommittee%20passed%20the%20markup%20of%20the%20Tauzin%20Amendment%20to%20H.R.%203636%20with%20a%20unanimous%20voice%20vote.%20The%20amendment%20grants%20broadcasters%20flexible%20use%20of%20the%20transition%20channel%20for%20ATV.%20Subcommittee%20Chairman%2C%20Representative%20Edward%20Markey%2C%20decried%20%22Only%20those%20who%20created%20the%20Communications%20Act%20in%201934%20have%20accomplished%20as%20much%20as%20we%20achieved%20here%20in%201994%2C%22%20hailing%20it%20as%20the%20most%20significant%20communications%20policy%20change%20in%2060%20years.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p><em>This article was written for the HDTV Newsletter in 1994 shortly after "multicasting" was added to the list of possibilities for digital television by way of a Congressional provision. </em><br />
_______________________________________________________</p>

<p><strong>Sweet Deal</strong><br />
Broadcasters received an early St. Patrick's Day gift on March 16 when the House Telecommunications Subcommittee passed the markup of the Tauzin Amendment to H.R. 3636 with a unanimous voice vote. The amendment grants broadcasters flexible use of the transition channel for ATV. Subcommittee Chairman, Representative Edward Markey, decried "Only those who created the Communications Act in 1934 have accomplished as much as we achieved here in 1994," hailing it as the most significant communications policy change in 60 years. </p>

<p>Although many people responded to Markey's letter requesting information on the proposal of flexible use, the legislation passed appears to have ignored most of them, as broadcasters received fully flexible use of the spectrum. Additionally, the enforced usage clause, requiring the FCC to establish a minimum number of hours per day during which advanced television services will be required was intentionally written so as not to actually require broadcasting of HDTV. While advanced television services are inclusive of HDTV, it is not considered exclusive.</p>

<p>Some have speculated that referring to occupants of the transition channel as "advanced" and incorporating such improvements as digital audio merely represents avenues by way of which the restrictions of the Ashbacker ruling can be skirted. Furthermore, one could question which is the greater public service: more channels or one improved quality channel. In any case, Ashbacker is not thought to present a threat, according to Joe Flaherty, who also expects H.R. 3636 to now pass the full house with little opposition. </p>

<p>Broadcasters are now poised to move into the digital domain. Peter McCloskey and Gary Shapiro, president and vice president of the Electronic Industries Association (EIA), respectively, tell us that as long as broadcasters move toward HDTV, the EIA will have to support them. </p>

<p><strong>Wiley Letter Encourages Whisperers to Speak Up</strong><br />
Last month, Dick Wiley, Chairman of the Advisory committee on Advanced Television Services, issued a letter which resurrected three major issues commonly thought to be long dead and buried. The three issues, all of which could substantially delay the standardization process, are alternative/flexible use of the second channel, COFDM, and interoperability. We spoke with John Abel (NAB), James Quello (FCC), Bob Rast (General Instrument), Jules Cohen (ACATS PS/WP3, consultant), Julie Barnethon (consultant, former ABC engineer), William Schreiber (MIT), George Vradenburg (Fox), and Tom Stanley (FCC) to develop a comprehensive overview of these issues from a multitude of vantage points within the industry. Numerous unsuccessful attempts were also made to contact FCC Chairman Reed Hundt, with the intention of discovering the direction his leadership may take. </p>

<p>While Wiley's own view, "pending future developments, is that we should stay on the course—that is, continue and complete our work as expeditiously as feasible, proceed to give the FCC a recommendation on a new video standard." However, he recognizes that neither he alone nor any one group collectively can make such a decision, and that because these issues continue to reappear, that more input is necessary to ensure there are no major omissions or cumbersome elements poised to kill HDTV.</p>

<p><strong>FLEXIBLE USE</strong><br />
Although Wiley's letter asked for comments from the industry, it was not until Congressman Ed Markey issued a letter on March 3, seeking information from industry groups, the FCC, and other interested parties regarding a proposal to allow broadcasters flexibility to use their spectrum for ancillary or supplementary services. Respondents were given only one week to reply, but many did, including Chairman Hundt. With the recent developments in the Telecommunications Subcommittee, this issue is now decided, but not to the satisfaction of a great many people.</p>

<p>Many people had suggested—notably Fox and the NAB—of late that the broadcasters should be allowed to use their proposed transition channel for multiple, digital, standard definition channels, data transmission, and other services in addition to or even instead of HDTV. "Dynamic scalability," or the ability to shift between HDTV and lower definition multiple channels or other digital uses, could provide broadcasters significant revenue boosts, and was touted as a way to help underwrite the considerable costs associated with implementing HDTV. Obviously, the most enthusiastic proponents of such an arrangement have been the broadcasters, whose lobbying efforts were largely responsible for the entire discussion. </p>

<p><strong>Broadcasters Answer Call</strong><br />
NAB President and CEO Eddie Fritts, representing NAB, INTV, ABC, CBS, NBC, and Fox, responded to Markey's letter with a very solid, well researched report on the importance of granting broadcasters flexible use of the second channel. The ten page response, addressing all 14 questions posed by the congressman, cited many historical and legal precedents to support their position. </p>

<p><strong>Trust Us</strong><br />
Fritts claimed "broadcasters are committed to maintaining free, universal, over-the-air service" for every assigned channel, and would continue to meet all public service obligations, operating subject to FCC regulations. They believe a policy of flexible use would not undermine the FCC's discretion or authority over ATV, but would merely enable efficiency in the use of the spectrum. Additionally, they responded to the quality vs. quantity issue by stressing "there is no likelihood that broadcasters would reduce the quality of their product," as "reduced picture quality is simply not competitively viable" due to the variety of competitive program providers offering consumers high quality video. Thus, "the marketplace can be relied upon to discipline broadcasters when it comes to video quality. Further, the establishment of any digital transmission system requires the adoption of standards for both transmission and reception." However, the bottom line of broadcasters' quality argument was the fact that they are licensed by the FCC to provide public service, and "if a broadcaster did not continue to provide a full broadcast service throughout its license term, it would face a real possibility of losing its license at renewal." </p>

<p><strong>Assignment vs. Allocation</strong><br />
Broadcasters argued that providing a second channel for transition to ATV would not require any new spectrum allocation, since the UHF band, on which ATV will presumably be provided, was allocated to television broadcasting in 1945. The FCC needs only to assign some of that broadcast spectrum to broadcasters for ATV. This semantic difference could prove quite significant if challenges are made based on the Ashbacker ruling. Ancillary use on that channel, they maintain, "might be critical to successful implementation of ATV in its early stages, when receiver penetration is low," and "if spectrum for that service is not assigned to existing broadcasters and they are not permitted to make the most effective use of that spectrum as ATV service, it is unlikely that any advanced system of over-the-air broadcasting will be implemented, and the goal of providing universal advanced data services to the public would suffer."</p>

<p><strong>Auxiliary vs. Primary</strong><br />
The primary use of broadcast spectrum, they maintained, would still be the provision of free, over-the-air broadcast service, and "as a practical matter, the types of services which could be offered will be limited." Additionally, despite the fact that some of the ancillary services broadcasters may be interested in providing would require the purchase of a converter, the service would not become a subscription service any more than "the requirement that consumers purchase a television set in order to receive current television service" would make it "anything other than a free service." Flexible use of the channel, they argued, is not much different than the current practice of using subcarriers or the vertical blanking interval to provide a variety of services. Similarly, "any non-broadcast services would be in addition to a broadcasters main program service, just as broadcasters currently provide ancillary services on subcarriers or in the VBI."</p>

<p><strong>NII</strong><br />
Another point stressed in the letter was that "any bar on broadcasters offering a particular class of services would seem to be inconsistent with the overall goal of H.R. 3636 of permitting any technologically and economically viable competitive service to be offered by any class of provider." No new spectrum allocation would be needed for this purpose, and "the FCC's rules now allow television stations to provide a variety of telecommunications services in the VBI, including data transmission, teletext, paging, and distribution of computer software." Some of the wireless voice providers have expressed interest in providing video services which would not be subject to restrictions, a move supported by broadcasters who stressed "we do not believe that such restrictions going either way would advance the public interest."</p>

<p><strong>We'll Pay</strong><br />
Finally, the broadcasters "support a requirement that the FCC establish an appropriate fee for broadcasters based on the amounts paid by providers of competitive services which obtained spectrum in auctions." However, there have been no spectrum auctions as yet, and predicting the price for a particular piece of spectrum is not possible, thus the fees to be paid can not yet be determined. What is suggested is that since "the auction process will begin this year, while any new flexible uses under the amendment are probably several years away," the FCC will have ample time to determine appropriate fees. Broadcasters do warn that fees should not be so high as to stifle the beginnings of such businesses.</p>

<p><strong>Give Them The Flexibility, But . . .<br />
</strong>Most of the other respondents to the Markey letter, as well as the individuals we spoke with, advocated allowing the broadcasters the flexibility to use their spectrum in different ways, though under varying conditions and/or restrictions. Chairman Hundt's letter was somewhat ambiguous as to his personal opinion on the matter, though he did state "The digital technology under development is flexible enough to support different communications services in addition to broadcasting," which apparently signified his support.</p>

<p><strong>Early Introduction</strong><br />
Most of those calling for flexible usage of the transition channel did so with the belief that added broadcasting revenue would result in more rapid implementation of full-time HDTV service. James Carnes, President and CEO of the David Sarnoff Research Center noted that "encouraging the development of these digital services is fully consistent with the FCC's authority to set digital transmission standards and regulations," and that spectrum flexibility would incentive to deployment of HDTV, as well as allowing HDTV to become a key element of the NII. Additionally, he did not believe fees should be levied or licenses required for such services. AT&T Senior VP Gerald Lowrie concurred, advising Markey that "any legislation regarding broadcast flexibility should promote the early introduction of HDTV."</p>

<p><strong>No Interference</strong><br />
Dr. Joe Donahue, Senior VP at Thomson Consumer Electronics, responded to Markey's letter by advocating allocation of the new spectrum without a fee so as to preserve free broadcasting. Donahue also favored flexible use of both the NTSC channel and the HDTV channel without fee or license requirements, so long as the ancillary services do not interfere or detract from the television service. However, any such service that reduces the quality of or terminates HDTV service "should be subject to a full fee, if not more, since such use detracts from free over-air television and possibly jeopardizes its future." Zenith CEO Jerry Pearlman proposed that any digital service be subject to the restriction: that it "be interruptible so as not to disrupt the HDTV programming in those limited instances when all the bits are needed to make good pictures."</p>

<p><strong>HDTV Required</strong><br />
Jerry Pearlman, CEO of Zenith, commented on the success and investment of the Grand Alliance to date, and believes a rapid launch of HDTV is critical. However, he stated, "Zenith does see a possible role for flexible use of spectrum, including digital NTSC (D-NTSC), so long as it is part of a clear migration path to HDTV." Zenith believes that D-NTSC would increase broadcast revenue and fund HDTV conversion, but with substantial restrictions, including initial simulcast requirements to stimulate consumer interest, maintaining it as a free service, requiring HDTV during primetime, with established timetables for increasing HDTV airtime, and maintaining technical standards common with HDTV.</p>

<p>Joe Donahue also rejected any consideration of freedom to transmit single or multiple programs of lower quality full time, proposing a minimum requirement of six hours per day of HDTV programming [editor's note:  Current regulations only require two hours per day of NTSC broadcasting]. Finally, Dr. Joe claimed that any amendment considered by Congress must expedite the completion of the ATV process.</p>

<p><strong>Reclamation of Reversion Channel</strong><br />
Lowrie emphasized AT&T's support for the FCC plan to assign the conversion channel temporarily, and that "the FCC should reclaim the original reversion channel for other purposes at the end of a brief (15 year) conversion process," reinforcing the idea that the second channel is "warranted only as a transitional means for upgrading the quality of television service to HDTV," and that "no broadcaster should automatically receive an additional channel except in transition to implement HDTV," with all spectrum for other purposes assigned by auction.</p>

<p><strong>HDTV Not a Priority</strong><br />
Some advocates of flexibility actually favor abandoning HDTV completely, opting instead to multiplex 6 digital channels, each of lower resolution than current analog NTSC. Perhaps most notable of such people is Fox boss Rupert Murdoch, who claims that developments in NTSC have made the difference between it and HDTV minimal. However, Wiley pointed out that if HDTV is not ultimately pursued, the question arises as to whether the FCC would—or legally could under the Ashbacker principle—still grant broadcasters additional broadcast channels of spectrum, or instead offer it to other parties interested in providing services.</p>

<p><strong>Flexibility May Kill HDTV</strong>Several individuals expressed their opposition to broadcasting flexibility, either to us or to Ed Markey. Bob Rast, VP HDTV Development at General Instrument, in his letter to Markey, very forcefully stated that "spectrum should not be made available for any purpose other than high-definition television except on the basis of comparative licensing or auctions." He claimed that the only practical, immediate alternate use is multichannel NTSC, and that allowing it would subsidize broadcasters and create an unfair competitive advantage. Additionally, because digital multichannel NTSC can not coexist with HDTV in a new channel, Rast believes that the entire transition would be jeopardized and that "the spectrum recapture would be delayed, and likely threatened." </p>

<p>Dr. Jae Lim, Professor at MIT, also opposed allowing multichannel NTSC broadcasting, citing potential delays in the introduction of HDTV service, as broadcasters would have little incentive to invest in it, due to the fact that "they can send more programs within a single channel and there is a very large installed based of NTSC receivers that can be adapted to receive multichannel NTSC at a relatively small cost." Furthermore, Lim predicted that any introduction of multichannel NTSC would make interlaced scanning a permanent fixture in the new service, a result contrary to Grand Alliance intentions. </p>

<p><strong>COFDM</strong><br />
Although the VSB transmission subsystem was chosen over the QAM technique by the Grand Alliance, following extensive testing at the Advanced Television Test Center, the feline transmission technique known as coded orthogonal frequency multiplexing (COFDM) has demonstrated yet another of its proverbial nine lives. In a notable about-face from his earlier attempts to squelch any and all discussions of COFDM by the likes of PBS's Howard Miller, Wiley now says he "would welcome any views that you may have on this subject and its possible impact on our work." </p>

<p>Europe is involved in the development of COFDM, and the Advisory Committee sent over a technical study group to review their progress. However, because European channelization is mostly 8 MHz, there is no work being done on 6 MHz systems. Initiating such development would take an estimated 9-15 months at a cost of $7-8 million. The Broadcasters Caucus of the Advanced Television Systems Committee (ATSC) has now expressed interest in such development, as they believe COFDM could have advantages over both VSB and QAM, and plan to formulate technical specifications. The potential problem which accompanies such a plan is that development would not be likely to coincide with the Advisory Committee's timetable for testing the completed Grand Alliance prototype hardware, presenting the possibility that the Committee might be asked to substantially delay its process. The concerns which arise include how the testing facilities would be maintained in the interim, and how COFDM would affect cable interests, since developing a standard acceptable to both broadcasting and cable has been a primary concern since the outset in 1987. Although Wiley notes that no decision is immediately necessary, he suggests the importance of determining COFDM's potential merits and drawbacks before disrupting the current schedule. </p>

<p>Dr. Tom Stanley, Chief Engineer at the FCC, marvels at the success of the ACATS process to date, describing it as rational and orderly, and opposes straying from decisions which have already been made. He believes "the hard part is to stop talking and start to putting together the hardware. . . I hope that [final decision process] is not delayed or complicated by the COFDM or the issues of multiplexing. I think those are great questions that must be explored, but I think we should at least continue finishing this "A" (HDTV) quality signal in 6 MHz, which is computer compatible and can be impressed on a digital carrier that blankets a city in some fashion, yet-to-be-established. Retired MIT professor William Schreiber takes an opposite stance on this issue, noting "I gave up long ago the idea that this is a rational process. It isn't. It is a crazy process. Most of the statements that people make are not technical arguments. They are self serving statements which try to make you believe that their company's own perceived interests are the interests of the country at large or even the television industry at large." Schreiber recalls a 1992 submission he made to the FCC regarding the merits of COFDM. "My submission was really attacked by many of the other commentors," but "in this report, John Henderson's committee fully substantiates every single thing I said in 1992." When asked if those merits justify delays in development of a year and a half, he replied "there is no grass roots demand for HDTV, and I don't know what the hurry is. The experience of the inquiries of 1987 clearly shows that undue haste simply delays the process. In the last four years, the dates of making a decision has slipped about three years, and that is primarily because not nearly enough time was allowed."</p>

<p>Fox's George Vradenburg, serving as Rupert Murdoch's spokesman, offered us their company's line while winging his way west. "I think there is some promise there [with COFDM] and I think it has to be pursued. There's certainly some promise coming out of Europe, Japan and Canada. All of those areas of the world are studying it with some intensity and vigor, and I think it would be irresponsible of us to not fully exhaust that. I'd hate to be in the position in two or three years that Japan finds itself in today with its transmission system—that we've overlooked something, and find that we've adopted a VSB system that's obsolete." However, he also claimed "We can't waste any time here. ACATS has got a schedule for the completion of this work, and I think the study of COFDM has got to fit within that schedule. If we continually delay the ACATS schedule to look at the newest girl that comes down the block, we'd never finish. So, I think we've got to look at COFDM inside the ACATS schedule." </p>

<p><strong>INTEROPERABILITY</strong><br />
The testing of the four HDTV systems which were combined in the Grand Alliance system, according to Wiley, "revealed that a high-line number interlaced scanning system produced better television pictures than lower-line number progressive scanning systems." As we have monitored previously, a number of critics have charged that the Grand Alliance inclusion of interlaced scanning impedes interoperability with other imaging formats like computers, while others contend that "HDTV represents the initial introduction of a digital bit stream into the American home to which other advanced (National Information Infrastructure-type) applications could be added." </p>

<p>Of those we spoke to, only George Vradenburg commented on interoperability, and only then after being specifically questioned. Vradenburg believes that anyone who is uncomfortable with the inclusion of interlace simply does not trust the marketplace to make the best choice. He notes that both the transport and the transmission system inside the Grand Alliance will support either format, but that in the long run the marketplace is going to pick progressive. Asked how a company should deal with such distrust, he responds "If you are a computer maker, you need to go out there with good, inexpensive, progressive-line systems, and demonstrate their superiority and quality and price. Then, people are going to buy them."</p>

<p>Interestingly, in arguing against allowing flexible use of broadcasting spectrum above in his letter to Markey, Dr. Jae Lim made an applicable comment. Noting the Grand Alliance's intention to ultimately phase out interlace once a 1000+ line progressive system is developed, he worries that it will be a permanent fixture due to the presence of multiplexed digital NTSC service.</p>

<p>The old guard of such "progressive-only" warriors as Gary Demos, et al, are conspicuously absent from current discussions, causing one to recall their promise to press their point throughout the entire process. Considering the fact that there are still three or four more notices and reports to be issued by the FCC before the matter is concluded, chances are that they will return to fight another day. Thus the entire issue appears to be somewhat on hold, perhaps simply overshadowed by more popular issues such as flexibility. _Dale Cripps, 1994</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Dale Cripps</b>, <b>June 17, 2005 07:49 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 92
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
 				AND entry_id <> 92
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/2005/06/1994_-_hdtv_moving_toward_the_tube_or_down_it_by_shawn_welch.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
