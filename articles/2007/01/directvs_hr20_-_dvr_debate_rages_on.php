<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 525";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Tom Starner'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 525 AND placement_is_primary = 1";
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
	<meta name="keywords" content="black screen, nationally version, released nationally, whatever reason, liberty media, directv, DIRECTV, DVR, dvr, Version, version, problems, issues, dbstalk, been, DBSTalk, tivo, TiVo, software, most, even, cnet, CNET, new, still" />
	<meta name="description" content="As I write this, the negative/problem posts continue to stack up on &lt;a href=&quot;http://www.hdtvmagazine.com/cgi-bin/ntlinktrack.cgi?http://www.dbstalk.com/&quot;&gt;DBSTalk.com&lt;/a&gt;, a Web forum devoted to satellite TV and its users. Since late summer, that particular forum has been ground zero for a major &quot;debate&quot; of sorts, one that, in essence, separated DIRECTV defenders from detractors (though to be fair, not everyone who posts is firmly on one side or the other)." />
	<title>HDTV Magazine Articles - DIRECTV's HR20 - DVR Debate Rages On</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/directvs_hr20_-_dvr_debate_rages_on';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('DIRECTV\'s HR20 - DVR Debate Rages On'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/articles/2007/01/directvs_hr20_-_dvr_debate_rages_on.php";
		if ($author[img] != '' && 1 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Tom Starner" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">DIRECTV's HR20 - DVR Debate Rages On</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Tom Starner</b><br />
				<?=$author_title?>
				Posted on <b>January 11, 2007</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Products & Equipment">Products & Equipment</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2007/01/directvs_hr20_-_dvr_debate_rages_on.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/articles/2007/01/directvs_hr20_-_dvr_debate_rages_on.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/articles/2007/01/directvs_hr20_-_dvr_debate_rages_on.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2007/01/directvs_hr20_-_dvr_debate_rages_on.php&amp;phase=2&amp;title=DIRECTV%27s%20HR20%20-%20DVR%20Debate%20Rages%20On&amp;bodytext=As%20I%20write%20this%2C%20the%20negative%2Fproblem%20posts%20continue%20to%20stack%20up%20on%20%3Ca%20href%3D%22http%3A%2F%2Fwww.hdtvmagazine.com%2Fcgi-bin%2Fntlinktrack.cgi%3Fhttp%3A%2F%2Fwww.dbstalk.com%2F%22%3EDBSTalk.com%3C%2Fa%3E%2C%20a%20Web%20forum%20devoted%20to%20satellite%20TV%20and%20its%20users.%20Since%20late%20summer%2C%20that%20particular%20forum%20has%20been%20ground%20zero%20for%20a%20major%20%22debate%22%20of%20sorts%2C%20one%20that%2C%20in%20essence%2C%20separated%20DIRECTV%20defenders%20from%20detractors%20%28though%20to%20be%20fair%2C%20not%20everyone%20who%20posts%20is%20firmly%20on%20one%20side%20or%20the%20other%29.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p><img src="/images/articles/hr20.gif" alt="DIRECTV HR20" align="left" /><b>The Debate</b><br />
As I write this, the negative/problem posts continue to stack up on <a href="http://www.hdtvmagazine.com/cgi-bin/ntlinktrack.cgi?http://www.dbstalk.com/">DBSTalk.com</a>, a Web forum devoted to satellite TV and its users. Since late summer, that particular forum has been ground zero for a major "debate" of sorts, one that, in essence, separated DIRECTV defenders from detractors (though to be fair, not everyone who posts is firmly on one side or the other).</p>

<p><b>The Issues</b><br />
Whether or not the HR20 DIRECTV Plus HD DVR, the satcaster's much-ballyhooed (and somewhat-delayed) flagship high-definition digital video recorder (DVR), is a dependable piece of equipment or a POS/POC (to use the euphemisms most bandied about by those having serious problems getting theirs to work reliably) - or something in between. Problem is, when a DVR is "something in between," it's probably not working as advertised. And that's certainly the case with the HR20 for some unknown but, judging by the extensive firmware downloads and plethora of posted problems, significant percentage of HR20 owners.</p>

<p>I admittedly fall into the DIRECTV detractor camp on this one (and I am an A+ DIRECTV sub, with a high monthly outlay and an 8-year history). Oddly enough, my HR20 is even currently working. For whatever reason (the HD Gods are smiling on me? Dumb luck?), my HR20 has worked for the past month or so after some early, and very irritating, problems. Oh, I still get the occasional video or audio dropout (a deal-breaker for my family, but more on that later). Other than that, it's doing what it is supposed to do. So why take a negative view of DIRECTV? Because for others posting on DBSTalk, the HR20 has been a "DVR from hell" experience, nothing less. And in response, DIRECTV has conducted itself extremely poorly on the customer service/tech support front.</p>

<p>There are those on DBSTalk.com who claim they have never had a single HR20 problem (and for reasons better left to a professional therapist, proceed to belittle others who have had problems for being "whiners " and/or "complainers"). Others give DIRECTV a pass because this is "bleeding edge" technology, so naturally some hiccups are to be expected. Another camp says complaining doesn't solve the problem, working to help DIRECTV fix things by reporting issues, etc., is the right-headed path. And my stance: No one buys (in this case, "leases") a $299 piece of home electronics gear that is clearly advertised as ready for prime time expecting to be a beta tester. A few minor early problems? Sure. But this mess? Not a chance.</p>

<p>Where to begin? How about there have been an amazing 14 software updates since Sept. 1 (see list below, which came directly from the DBSTalk.com forum). Of course, DIRECTV's defenders point to the software parade as proof that the satcaster cares. The detractors point to the list as strong evidence that DIRECTV doesn't know what it's doing when it comes to delivering a capable HD DVR (rather than letting someone else handle it, as they did with the first HD-DVR effort, the TiVo-powered HR10-250). Understand that unless you are plugged into the DBSTalk forum, the typical subscriber would have no idea that you received these software updates on their HR20 because they are delivered via satellite in the middle of the night. (How DBSTalk, a site not owned by DIRECTV, became the company's de facto clearinghouse for subscriber complaints, bug/data reporting, download information, etc., is another story, for another time.)</p>

<p><b>HR20 Revision History:</b><br />
Version 0x115 (1/8/07) <i>Limited release</i><br />
Version 0x10B (12/15/2006)<br />
Version 0x108 (12/12/2006) <i>Not released nationally</i><br />
Version 0x104 (12/06/2006) <i>Not released nationally</i><br />
Version 0xFA (11/22/2006)<br />
Version 0xF6 (11/21/2006) <i>Not released nationally</i><br />
Version 0xEF (11/15/2006)<br />
Version 0xEB (11/07/2006) <i>Not released nationally</i><br />
Version 0xE3 (10/19/2006)<br />
Version 0xDC (10/11/2006)<br />
Version 0xD8 (10/04/2006)<br />
Version 0xD1 (09/26/2006)<br />
Version 0xCC (09/16/2006)<br />
Version 0xBE (09/01/2006)</p>

<p>Granted, some of the revisions on the list added features (such as over-the-air functionality, which just became available on 12/15). But most have tried to fix ongoing issues that have nefarious names like "Black Screen of Death," "Unwatchable Bug," or the "Instant Keep or Delete Bug." There is even a newly posted "Catalog of HR20 Bugs" sticky thread on DBSTalk, and the list is impressive (if that's the right word). The acronym RBR (red button reboot) has become part of the HR20 lexicon, since it has been one of the only ways to get the box to behave (even though it doesn't always work).</p>

<p class="editorial"><b>Update</b>: On 1/8, DIRECTV released the latest&nbsp;(14th) software download. Based on early reports on DBSTalk, there are still issues with this release. However, it's "a release candidate" (DIRECTV's new strategy on the downloads: Make it voluntary and see how it goes. Then release it nationally when they think it's ready.) That recent strategy is an improvement, but past releases still had bugs despite the "test and report" process by willing subscribers (I'm not one of them). In other words, it has not made the HR20 stable for everyone, so the drama and debate continue.</p>

<p>The DIRECTV defenders say similar issues are reported on TiVo forums, and the DVRs provided by cable companies are no more reliable. But that's hardly consolation to subscribers who have had little or no problems with their SD DVRs (the ones not made by DIRECTV). I mean, try telling my wife and daughter that there will always be "growing pains" connected with new hardware, as they lose critical bits of dialogue from the latest Grey's Anatomy episode. Both refuse to even consider the HR20 after what they've seen in terms of audio/video dropouts, freezes and other glitches (they've stuck with the TiVo-based R10 SD recorders and refuse to even watch HD as a result - not a good thing for the HD industry in general, and DIRECTV in particular.). I want to be clear here: I like the HR20 when it works. No problem with the GUI. It delivers a very good picture. It even has a couple of advantages over the TiVo GUI. When working, the HR20 can be an excellent part of any HD lover's hardware arsenal. But, judging by the hostile reviews/feedback and multiple downloads (and my experience as well), that's been a big "if" for a significant number of users.</p>

<p>In particular, the HR20 is equipped to handle DIRECTV's new MPEG4 HD transmission of local channels in HD (as well as SD and MPEG2 HD). Prior to the HR20, subscribers who wanted to record HD signals from DIRECTV relied on the HR10-250, which captured MPEG2 HD signals as well as SD programming. Initially, that box was nearly $1,000, so cost kept it off my "must have" list. I stuck to SD recording via DIRECTV receivers equipped with TiVo, and for several years it was great, almost flawless.</p>

<p>But then, for whatever reason (and there is much speculation on why), DIRECTV decided to go it alone on the DVR front. After all, Rupert Murdoch's News Corp., which owned DIRECTV (it is now in control of John Malone's Liberty Media after a swap for stock and cash deal, which closes in mid-2007), also owns a company called NDS, which - surprise! - makes DVRs. But what might have sounded like a good idea on paper just hasn't panned out. There also is precedent for poor results on the NDS DVR front. DIRECTV has another non-TiVo DVR, the R15, that records only SD programming. According to many posters on DBSTalk (and some folks I know), the R15 is suffering from some of the same issues as the HR20, including missed recordings - and it's been on the market for more than a year! So it's not like this is the first DVR-building try at DIRECTV. I understand that the R15 and the HR20 are not made by the same subcontractors, nor do they share the same software code, but for whatever reason, subscribers are sharing some of the same headaches.</p>

<p>When local HD channels became available in my market this past summer (I had been a DIRECTV HD subscriber for 3-plus years, using a trusty non-DVR Zenith HD receiver pulling in the MPEG2 channels), I took the plunge and ordered a new 5LNB dish and the HR20. Mine arrived in early September, and the install eventually went smoothly. From day one, the HR20 acted screwy, freezing up unexpectedly and delivering the "black screen of death" regularly (it's akin to the old MS Windows blue screen of death, but it this case, instead of a recorded program, you got a black screen requiring an RBR). I was not a happy camper, but eventually the software downloads fixed things, on balance.</p>

<p><b>The Reviews</b><br />
This would all be very humorous, sort of a home entertainment Keystone Kops, if it weren't so infuriating for the owners who have had serious HR20 problems. To me, when you replace a very reliable piece of gear ("DirecTiVo", HR10-250, etc.) with a box promising to make things even better, then you'd better deliver at least the previous level of reliability. Of course, some new electronics products have their lemons (or issues), but the HR20 has become the undisputed unreliability champ, based on what is being reported at DBSTalk, at HDTV Magazine (see Ed Milbourn's critical October two-part review), user feedback (at CNET and big box retailer Circuit City), and on sites like tvpredictions.com.</p>

<p>A very interesting aspect of the entire debate raging over the HR20 can be found on <a href="/cgi-bin/ntlinktrack/cgi?http://reviews.cnet.com/DirecTV_HR20_DirecTV_Plus_HD_DVR/4505-6474_7-32065196.html">CNET.com</a>, which reviewed the box back in October - before most of the software downloads and the mounting, yet still unsolved, problems became widespread. The good folks at CNET, whose reviews don't always mesh with what their users have to say, gave the HR20 an "excellent" rating of 8.1 out of 10, based on one month of use. Of course, they luckily got one of the "good" HR20s (though they did report some issues, specifically "...We did experience a few snafus with the EPG [electronic program guide], but we expect DirecTV to work out those kinks soon."</p>

<p>Almost immediately, the negative user reviews on CNET started piling up like tires at a junkyard (the average user rating as of this writing is 4.2, with 107 users delivering feedback, myself included. I rated it a 5.5 based on reliability problems, but did say I liked it when it worked). That 4.2 average indicates a less than 50-50 satisfaction split, certainly no ringing endorsement of the HR20. In fact, the most recent wave of reviews are almost entirely negative (between December 14 and January 7, most of the scores are 3 or lower).</p>

<p>Back in early November, CNET took notice of the negative reviews and asked DIRECTV to explain. DIRECTV's carefully worded response offered some interesting language. In part, DIRECTV's CNET posting on 11/3/06 said...</p>

<blockquote>The vast majority of our HR20 customers have had the same good experience with their receivers as reported in the CNET review. They are happy with the performance of the HR20, its features, and functions.

<p>Some of the issues raised by CNET users were not all receiver-related, but involved a combination of out-of-spec HD signal feeds provided by local broadcast networks, our own broadcast configuration, and receiver software. These have been largely resolved and overall receiver performance is good and will continue to improve as we optimize its operation.</blockquote></p>

<p>Later, in the same statement, DIRECTV added:</p>

<blockquote>In summary, the HR20 is an outstanding product and demand is so high we are bringing a second manufacturer on-line.&nbsp; There have been issues since the launch in September--to be expected in the national rollout of any sophisticated consumer electronics product--but most of those issues have already been resolved and any remaining are being worked on aggressively. The fact is that the HR20 has had the smoothest launch of any of our previous DVRs including the TiVo HD DVR.

<p>The HR20 DirecTV Plus HD DVR is our flagship receiver and will be the launching pad for several new, exciting features and services in the coming year. We will not be satisfied until it is performing flawlessly.</blockquote></p>

<p>Uh-Huh. <i>Vast majority?</i> I'd love to get the actual numbers of problem HR20s (and the number of refurbished machines sent out as replacements).  Note: Some people reported that their replacement HR20s still had programming on them from former owners. In other words:Garbage in, garbage out.</p>

<p><i>The smoothest launch?</i> Somehow, based on the mounting evidence to the contrary, I seriously doubt it.</p>

<p><i>Most of the issues have been resolved?</i> Hardly. Remember, that statement was posted on Nov. 3! Of course, there are satisfied subs out there who have fared well with their HR20s, but even if only 15-20 percent are having the recurring reliability problems (downloads notwithstanding), it's still way too many (though my hunch is the number is higher).</p>

<p><b>DIRECTVs Response</b><br />
DIRECTV certainly has worked <i>aggressively</i>, but <i>effectively</i> would have been much better. A number of major bugs remain, and some users on DBSTalk report that while they initially had no problems, the latest download (Dec. 15) has caused new problems (geez, I hope my luck holds out).</p>

<p>Company spokesman Robert Mercer would only say DIRECTV is continuing to listen to customer feedback and improve the product via software downloads. "This is our workhorse set-top going forward, so we are committed to maximizing its performance," Mercer added via email. He did not respond a request for specific statistical data on problem HR20s, nor to questions about reliability, specific issues, etc.</p>

<p><b>Conclusion</b><br />
There's little doubt a significant number of subscribers will hit the boiling point (of course, some already have) if the HR20 isn't fixed real soon. I can only imagine the poor DIRECTV customer who never heard of an online forum or can't tell MPEG4 from MTV. He or she "leases" the HR20 for $299 (and don't forget the two-year commitment) and expects it to faithfully record the latest episode of Ugly Betty, just like the old "DirecTiVo". Instead, they get the ugly "keep or delete" message when they hit play (meaning they don't get to see the recording). Or, during playback, their HR20 doesn't respond to its remote control without an RBR. The list goes on and on.</p>

<p>No, DIRECTV clearly blew this one. Its aggressive "Let's get it fixed" stance notwithstanding (what else could it <i>really</i> do?), by not getting out in front of this situation, the company is seriously alienating a portion of the "big spender" segment of its subscriber base. Will those discouraged, frustrated HD subs stick around? Most probably will for now (not much real choice at this early point in HD recording history), but long-term, they will stay only if the HR20's performance really is "maximized" for everyone. In fact, forget maximized. How about getting it to work reliably as a DVR? Most people having problems could care less about video on demand, networking or other bells and whistles on the HR20. They just want the darn thing to record and play back their favorite shows!</p>

<p>Of course, it's nice to hear that Liberty Media (the new DIRECTV owner) had previously invested in TiVo, according to some recent reports. If nothing else, waiting to see what changes are brought about by Liberty Media running the show will be interesting. Despite its nine national firmware updates (with one pending), to date DIRECTV hasn't yet delivered a DVR that all of its subscribers can enjoy. And that's true no matter what side of the DBSTalk "debate" you find yourself on.</p>

<p class="editorial"><b>Postscript</b>: No sooner did I write the initial draft of this piece before the final weekend of regular season NFL games and my HR20 started acting flaky. When I turned off the power (in effect, putting it in standby) and turned it back on, I got only my local HD feeds and no other channels (only a black screen, one of a few "black screen" bugs). The "guide" and "info" functions still worked, but only an RBR got the picture back. Twice since then, I've also had a non-responsive remote (another RBR required), and finally, my trick play features (FF, Rew, etc.) started to act choppy, missing a lot of frames (very frustrating when trying to use them during an NFL game). Believe me,  I'd rather be wrong about DIRECTV on this one. But I'm afraid I'm not.</p>

<p>* DIRECTV is a registered trademarks of DIRECTV, Inc. TiVo is a registered trademarks of TiVo Inc. or its subsidiaries. All other trademarks and service marks are the property of their respective owners.</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Tom Starner</b>, <b>January 11, 2007 06:41 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 525
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
 				AND entry_id <> 525
 				AND entry_status = 2
 				AND entry_author_id = a.author_id
 				AND a.author_name = 'Tom Starner'
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
 				<h2>About Tom Starner</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2007/01/directvs_hr20_-_dvr_debate_rages_on.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
