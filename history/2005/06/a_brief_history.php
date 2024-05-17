<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 42";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Dale Cripps'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 42 AND placement_is_primary = 1";
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
	<meta name="keywords" content="dtv transition, digital television, chairman kennard, public interest, business model, HDTV, hdtv, digital, broadcasters, new, spectrum, wireless, dtv, television, DTV, business, public, transition, FCC, fcc, Kennard, kennard, time, should, programming" />
	<meta name="description" content="Written for Widescreen Review in year 2001. This article shows the state of the HDTV movement in 2001. The issues are similar today though the game more mature. I hope you will decide that HDTV Magazine should be elevated so..." />
	<title>HDTV Magazine Archive &amp; History - A Brief History of HDTV to 2001</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/a_brief_history_of_hdtv_to_2001';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('A Brief History of HDTV to 2001'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/history/2005/06/a_brief_history_of_hdtv_to_2001.php";
		if ($author[img] != '' && 5 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Dale Cripps" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">A Brief History of HDTV to 2001</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Dale Cripps</b><br />
				<?=$author_title?>
				Posted on <b>June  1, 2005</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category="></a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/2005/06/a_brief_history_of_hdtv_to_2001.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/history/2005/06/a_brief_history_of_hdtv_to_2001.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/history/2005/06/a_brief_history_of_hdtv_to_2001.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/2005/06/a_brief_history_of_hdtv_to_2001.php&amp;phase=2&amp;title=A%20Brief%20History%20of%20HDTV%20to%202001&amp;bodytext=Written%20for%20Widescreen%20Review%20in%20year%202001.%20This%20article%20shows%20the%20state%20of%20the%20HDTV%20movement%20in%202001.%20The%20issues%20are%20similar%20today%20though%20the%20game%20more%20mature.%20I%20hope%20you%20will%20decide%20that%20HDTV%20Magazine%20should%20be%20elevated%20so...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p>Written for Widescreen Review in year 2001.</p>

<p>This article shows the state of the HDTV movement in 2001. The issues are similar today though the game more mature. I hope you will decide that HDTV Magazine should be elevated so it can express from the rooftops its unwaivering support for HDTV as being the key choice to be made by we consumers for the benefit of America. The reasons are clear. _Dale Cripps</p>

<p><br />
___________________________________________________________</p>

<p><br />
Historian Arnold Toynbee once observed that the 21 civilizations he studied all collapsed for the same reason: their inability to adapt to changes taking place either within them or surrounding them. Like Joseph Campbell, Toynbee noted the importance of myth in shaping the future of a civilization. For from the myth springs the vision of new possibilities. Each nation, each culture tells such an overarching story about itself. But when great changes take place, a new myth is required."_Dale Cripps</p>

<p>__________________________________</p>

<p>You would think by now that HDTV would have gained more traction than it has. Slow sales of DTV decoders made things very difficult for signal providers, manufacturers, and the public alike. Sinclair Broadcast Group's (see last issue interview with Nat Ostroff) plea for an alternative modulation scheme they want instituted by the FCC has utterly confused and paralyzed the market for all but the most stalwart fans of quality (like those of you in serious home theater. Good news is that this number of aficionados is growing).</p>

<p>The conclusion reached by many industry pundits is that the consumer DTV transition is in an outright hellish stall caused by bad business models and exacerbated by an unanswered technology question.</p>

<p>The entire movement to digital television is in danger of having the plug pulled if anyone can figure out where the plug is, or how they can get it out of the wall gracefully. Competition forces everyone to stay in the game, but that alone is no guarantee for success.</p>

<p>On the flip side you can feel that many key players have grown more hopeful for HDTV following the recent CBS announcement. You may have heard about the CBS deal. Panasonic is underwriting the cost of HDTV on all but a few hours of the CBS prime time line-up. Even ABC is said now to have some programming surprises they will announce shortly. NBC is...well...not very alert. Fox will have more 480p and likely some HDTV in the wings. The fact remains that even without them there is more HDTV prime time hours being broadcast this Fall and Winter season than during all of last year.</p>

<p>There is also the HBO, Showtime and the pay-per-view movies from both satellite companies and some cable companies. This deal with Panasonic was very wobbly until finally signed. Panasonic was reportedly looking for the pause button on their entire HDTV strategy. Too much confusion, they were saying, too few buyers for 8-VSB decoders they figured. Why spend more on programming? Panasonic was finally convinced that there was a bright future for HDTV by Peter Fannon (past president of the Advanced Television Test Center and now the man of Matsushita in Washington, DC).</p>

<p>Fannon is among the few remaining original stars of the HDTV movement who courageously convinced his company to not leave the game. Only a small core of such deeply devoted people are left to hold the entire HDTV ship together. The others, who have little loyalty to HDTV and think the spectrum could be used for other "cool" things, are tearing away at HDTV like mad dogs in a meat market. Long term thinking is the only means for seeing the HDTV puzzle take a positive form. The short term thinkers in the meat market want to compromise the picture quality in order to save bandwidth and fill that portion of the spectrum with fast selling data or lower quality multiplex channels. You have to sympathize with many of the small market broadcasters. They look down the barrel of a loaded rifle and see the FCC's finger on the trigger.</p>

<p>Not only is there lack of clarity with respect to which technology is going to stick, but they are totally in the dark about the vitality of the transition. What if the transition takes 20 or more years? That is a lot of power bills to pay in a time when power is not getting any cheaper. As pointed out here before, some see digital as their opportunity to provide ethnic programming--a multiplex of different cultures. Their program-starved viewers will buy a digital decoder just for that. There is certainly nothing to prevent such a choice in the Telecom or FCC laws. FCC Weighs InSome in FCC clearly believes there is a stall. FCC Chairman William Kennard stepped into the spotlight Tuesday, October 10th with a speech on the subject to an eclectic crowed at The Museum of Television and Radio in New York City. In 4200 words he fiddled dangerously with the American Constitution and most everything we once believed about free markets.</p>

<p>"When we realize that television should not only entertain us as consumers, he said, "but engage and ennoble us as citizens, we will have come a long way to establishing the 21st century's first true electronic democracy."</p>

<p>With a head-banging charge that "broadcasters are 'squatting' on empty (DTV) spectrum," and are smothering innovation and "endangering America's lead" in new technologies, Kennard said he wants Congress to intervene with new force to hurry-up the DTV transition--get that analog spectrum back in inventory. Kennard outlined a five-part strategy as a framework to rethink broadcasters' public interest obligations:</p>

<p>[1] Stations should commit to carry every single presidential debate, as well as cover state and local races. He said television reached "a new low" when the NBC and Fox networks chose to preempt the October 1 debate for sports and entertainment programming.</p>

<p>[2] Stations should recommit to show more public service announcements (PSAs) during peak viewing hours to educate viewers about issues of the day;</p>

<p>[3] Stations should provide free time to candidates for federal office during the last few weeks of an election season;</p>

<p>[4] The broadcast industry should establish a code of conduct for good citizenship by broadcasters; and, [5] On October 16, the Commission will hold a public meeting to further explore how television can enhance democracy by contributing to political discourse, serving local communities and protecting children. On the conversion from analog to digital television, Kennard said, "Broadcasters have decided to sit on these two highly valuable properties - licensed to them for free by Congress - for as long as they can." He warned broadcasters, "Squatting on empty spectrum smothers innovation and endangers America's lead in new technologies." As part of a three-part plan to push the conversion to digital TV and free up spectrum for the wireless web,</p>

<p>Kennard urged Congress to:</p>

<p>[1] Eliminate the "85% loophole" in the law thereby making 2006 a hard deadline for broadcasters to return their analog channels;</p>

<p>[2] Adopt a requirement that all new television sets include the capability to receive digital TV signals by a given date, such as January 1, 2003;</p>

<p>[3] Impose an escalating "spectrum squatters fee" on broadcasters if they do not meet the 2006 conversion deadline. The proceeds from the fee could help fund the digital conversion of public television and support programming that serves the public but is not provided by the market. He spoke before an audience that included students and representatives of the public interest community and the broadcast industry.</p>

<p>This being the political season any such non-sense can be said since nothing can be done about it. His comments will be long-forgotten by the time the next Congress is settled down to business in eight months. Many in recent months have said that broadcasters might decide to turn the digital channel back.</p>

<p>"If they are not going to use it for a public service," said former FCC Chairman, Newton Minnow in a TV interview, then they should give it back. The police, fire departments, and schools have all wanted use of the broadcast spectrum. "If broadcasters don't want the channel, they should turn it back and let others use it to "</p>

<p>Gary Shapiro, president of CEA, sent out a press release immediately following Kennard's remarks saying that "the Consumer Electronics Association (CEA) applauds Chairman Kennard for his continued leadership on digital television. We support his efforts to hold the broadcast community accountable for promises made long ago - promises to deliver digital television to American consumers. Our industry believes that broadcasters have a public interest obligation to use the $70 billion spectrum, loaned to the industry by the American people, to provide digital, high-definition programming.""CEA supports the enforcement of the Federal Communications Commission (FCC) DTV roll out schedule and actions that give broadcasters financial incentive to hasten their transition to digital television.</p>

<p>Gary continued but with now an objection to Kennard, "The FCC should avoid efforts that will shift the burden of broadcasters' responsibility to consumers. The proposed mandate requiring built-in DTV receivers in all televisions by January of 2003 will undoubtedly result in increased costs to consumers. Under this scenario, one manufacturer estimates that the cost of a 13" TV will be $1000 -- a significant increase over today's average price of $125. (These figures were immediately challenged as another Gore-like exaggeration.)</p>

<p>"The mandate also would severely limit consumer choice. Consumers have demonstrated that they want options when making the decision to upgrade to DTV. Many consumers are choosing to upgrade their monitor now - to enhance their DVD, DBS or analog experience -- and buy a digital tuner later when more programming becomes available. Requiring consumers to purchase a digital television set conflicts with every public policy goal behind a market-driven transition to DTV.</p>

<p>"Broadcasters have not delivered on DTV -- let's not make consumers suffer the consequences." so saith Gary."Chairman Kennard has it right," said a spokesperson for one of the huge wireless entities, Verizon, "when he says that the public interest will be served by speeding the transition to digital TV and returning valuable spectrum to the American people. This spectrum holds great promise for</p>

<p>Third Generation wireless services, but that promise will only be realized if wireless companies like Wireless can use it. Chairman Kennard's proposal to make this spectrum available sooner makes perfectly good sense for wireless consumers and the American taxpayer."Not all agreed. "It is regrettable that Chairman Kennard has failed the test of leadership." said the always effective president of the National Association of Broadcasters, <a href="http://www.hdtvmagazine.com/articles/articles-author.php?id=5"><a href="http://www.hdtvmagazine.com/articles/articles-author.php?id=5">Eddie Fritts</a></a>. "Sadly, he (Kennard) is trying to shift the blame for a faltering DTV transition (to broadcasting). As to the FCC's leadership, Fritts struggled to be kind, saying, "Congress gave the FCC authority to require all television sets to receive DTV channels, but it has not. Congress gave the FCC the authority to establish DTV/cable interoperability rules, but it has not. Congress gave the FCC authority to require cable systems to carry DTV stations, but it has not."</p>

<p>Brandishing a familiar cigar, Fritts defended his flock saying that broadcasters are "well ahead of schedule in the DTV transition. There are 158 stations now sending digital signals to almost 65 percent of all U.S. households. As FCC Commissioner Susan Ness noted recently: 'While we (the FCC) dabble in some of the crucial aspects of the transition to digital television, we are at the same time, in other contexts, holding back from addressing the critical issues that relate to this transition. SI hope that we will soon address holistically the crucial issues surrounding the transition of analog stations to the digital age."</p>

<p>Having said that, others think broadcasting itself is a fading reality. Viewer hours are down. Valuable spectrum is used for less than 20% of the audience that was first drawn together by it. The once terrestrial audience is now seeking signals from wires and satellites.</p>

<p>The argument that terrestrial is superfluous is generally countered by one pointing to a critical public service and public safety/service duty. Indeed what kicked off Kennard was NBC and FOX's opting out of carrying the second presidential debate. The Yugoslavian president was finally driven out of office because the opposition stormed his TV station. TV is still seen as a very important and influential service to the well being of any democratic country. It is looked upon with equal amounts of suspicion when under the control of a tyrant. The entire process for developing and standardizing HDTV in this nation was to insure that free over-the-air broadcasting would continue and pay services would not be the only source of news and entertainment for those who can't afford it.</p>

<p>That idea has nearly been forgotten and the government is asking more of broadcasting than it can deliver in short order. Towers and permits for the conversion to digital are scarce, if not impossible to obtain. Add to that the general confusion about the standard and you wonder how broadcasting, as good of a business as it has been and still is, can cope.</p>

<p>Others Show That They Want the SpectrumHigh-stakes pressure groups from the "wireless" world are working the streets in Washington. "Wireless" is the new communication's buzzword racing around the world. Executives from that field are at the top of their game. They present themselves to Belt-way officials as if they are the unique builders of a "titanic new economic future " that holds the greatest of all possible promises...if they can just get that spectrum.</p>

<p>Their pitch appeals to the Washington "job creators," or those taking credit for it. What Is WirelessWireless is an aggregate of things (many already in existence) just as mutlimedia is an aggregate of separate media disciplines. Transporting data/services/video/still image/voice/sound seemlessly and interactively over wireless digital distribution networks around the world is the fundamental end-vision for the "wireless" phase of our digital revolution. The term "wireless" suggests that we become untethered from anything stationary without losing the communications we have grown addicted to in our office or home.</p>

<p>Spectrum, then, for this wireless vision, equals no less than a new found freedom. Kennard said broadcasters are, by their "squatting" on unused and irreplaceable spectrum, and clearly pointed to stifling of the wireless technology and made that synonymous with America's technical lead. People have paid dearly for every freedom won. The next installment payment looks to be related to wireless. What does this have to do with HDTV today? It is the spectrum assigned to broadcasters 60 years ago that is the BIG prize here. This particular spectrum is by far the most ideal block of frequencies there will ever be for carrying "wireless" business applications.</p>

<p>Frequencies that are higher or lower incur more cost to sender and/or receiver. You can bet that if there is any gold in 'them thar' spectrum hills, these "new economy" entrepreneurs--the smartest and most powerful group of individuals in the world today--will recognize it, and compete effectively for it. They are already under a pressure from huge investment risks they have taken elsewhere in wireless spectrum acquisition. The only pay-off can come after the U.S. also moves seriously to wireless. Again, this is a spectrum issue and what we are witnessing is new pressure ratcheting up for the return of the old analog spectrum. That means there is pressure on getting the transition done. That may mean getting it done any way they can, rather than the longer road needed by HDTV assimilation.</p>

<p>One either grows or dies in this new economic environment. I think we must expect a very powerful spectrum war between "wireless" and broadcast companies, especially if the broadcasters appear to be "squatting" on spectrum. The wireless groups will not settle easily for silver if there is gold in the vault. The spectrum is in play, and broadcasters need to respect who is seeking it. Their solution is not difficult. Finish building out their DTV facility and insure that HDTV is part of the daily line-up.</p>

<p>Out of Phase<br />
The business model for networks/affiliates was firmly shaped by both human behavior patterns and the analog technology first employed. Broadcasters are slowly waking up now to the more "flexible" future of digital and asking, "What about some of this wireless business for us? What does it take to be a player? Does having control of the digital channel give me an edge, even if I pay for it as prescribed by Congress in the Telecom Act and FCC rules? What about the standards?"</p>

<p>A broadcaster, of course, could turn his or her business into a "wireless" company" and go in hot pursuit of the new growth potential. There is nothing to prevent that from happening. The followers from other nations of the U.S. direction are very nervous now, saying that there is no real business model for a DTV transition where you already have multiple programs available. Australia is about to launch their own digital services. They have no vision as to how it is going to succeed if HDTV is not a real effective driver. That requires outstanding programing.</p>

<p>A recent email from broadcast engineer/consultant, Keith G Malcolm from Canberra explained the how it is seen down under, "In Australia the broadcasters have publicly followed the U.S. "HDTV only" policy for DTV for much the same reasons as have the U.S. broadcasters. The Australian broadcasters have also successfully lobbied against the introduction of any meaningful datacasting service. The result has been an extended period of uncertainty over just what DTV receiver capabilities need to be provided, to the extent that the manufacturers "sat on their hands" for fear of backing the wrong horse, and it is now much too late to deliver even SDTV receivers in time for the 1 January start date. At the same time there is the awareness of the weakness of the "HDTV only" (business) model and the associated fact that DTV, as proposed, will offer nothing to convince Joe Public to shell-out his money on a new receiver for no new services. Hence no business model to support sales and no prospect of a rapid adoption."Author Fergal Ringrose wrote recently from Ireland where they are about to install digital standard TV.</p>

<p>"The planned start-up date," says Ringrose, " for Irish DTT is in the Fourth Quarter of 2001. "No matter how I juggled the figures for my business plan for Digital Terrestrial in Ireland I could not get the numbers to stack up to create a viable business plan without having an integrated wireless return path," said an Irish broadcast representative. "DVB-T is competing with satellite, cable and DSL. Any platform that can't stand alone and offer all the services has no long term future. If you are a broadcast network operator competing with cable, and then you give them business on the return path, you are cutting your own throat."</p>

<p>England is held up as the only real success story for DTV. Over half million decoders have been sold for decoding standard resolution programs playing on standard PAL sets. They use the COFDM DVB-T system and report quite reasonable service. To speed things along they have offered subsidized receivers with the payback tied into the subscription costs of the newly added programming. It is this last feature--more programs--that influences the sale. This gaining of new channels is the only advantage to going digital in England with the exception of interactive features recently incorporated. But few use those features.I submit that without HDTV for this nation we are cutting our own throats. DSL is not able to carry it. Satellites don't have the bandwidth for a large number of channels. Cable could carry it, but they are competing with satellite now with more channels, not broadcasting with a few better ones.</p>

<p>HDTV is the killer application for the DTV transition in the United States. Period. Getting Back To Basics BEGIN WITH THE END IN SIGHT"All things are created twice," claims author Stephan R. Covey. First (they are created mentally, second physically. Individuals , families, teams and organizations shape their own future by creating a mental vision and purpose for any project. They don't just live day-to-day without a clear purpose in mind. They mentally identify and commit themselves to principles, values, relationships, and purposes that matter most to them. A mission statement is the highest form of mental creation for an individual, a family, or an organization. It is the primary decision because it governs all other decisions.</p>

<p>Creating a culture behind a shared mission, vision, and values is the essence of leadership."HDTV is, of course, an unflawed concept for a public seeking a new and higher standard of living experience. It fits the change prepared for in the last century. It's a glorious addition. But we seem to have lost the sense of importance that was once attached to the HDTV movement. I risk repetition here in saying that every imaginable burden has been laid upon HDTV, including paying off the national debt! I think we all got tired from the unending burdens falling on it one by one. But we have to roust ourselves consumer and manufacturer alike in a new partnership that brings this marvelous level of perfection to our homes. We need a clear mission statement! Something like "We Further HDTV To Further Communications."</p>

<p>Every writer knows the value of editing. A story is first a jumble of idea, often incoherent and unstructured. Then comes a simplification process which prunes away words, sentences, paragraphs, and even chapters until the article, book, or screenplay is ready for prime time. The editing is hard, but not as hard as the act of creation. Editing a massive amount of information to make just one clear statement is what is now required for the advancement of high-quality television. The alternative is to lose the big picture in a sea of unedited and confusing ideas. In the last few issue I said that it was time to take sides on the modulation argument. Now I believe it is time to take sides on whether you want to support HDTV or the alternatives. Will this nation build an infrastructure to handle HDTV or will it become something else...like a standard resolution 4:3 world that processes some data and displays web pages, even if you are on the road again. If we are to have digital TV we also must break from the analog traditions and restriction. Thinking in the old way ties today's programmers and consumers to specific program construction. The upper management in leading communications giants must take time now to fuse two ways into one truly cool way. Oh God, did I say "cool?" My apologies. But that best explains the entire digital revolution. It's cool! But didn't I have music before it? Didn't I have pages to read before it? Didn't I have movies? Didn't I have shops to go to? Didn't I have libraries...with their endless reference material? I had everything that a digital service delivers before there was a digital web, but gee, it is cool the way I can get information now. And it will be "cooler" tomorrow.</p>

<p>But features are not the main event. Television is still the main event, and will always be the main event. At least that is the side I am taking on this argument. Television is here to stay. I want to make the very best of it!. That is my position even though I am an Internet guy.</p>

<p>It Takes A Creative Act To Launch HDTV<br />
It would seem from the press that our nation has forgotten why billions of developmental dollars and millions of highly skilled man hours were devoted to HDTV. It's as though the most fundamental reason to be for it had vanished behind a cloud of flashier things from the Internet or wireless. The waking-up of the public to the Internet took a great toll on the HDTV momentum and focus. It no longer seemed like the old business of broadcasting when they could do so many other things that were already being done from a web site.</p>

<p>An incomplete standard has also delivered a heavy price to momentum. Hollywood also sits on the digital wagon and completely escapes Kennard's darts. But Hollywood needs to wake up as well. theaters are going under. The future of motion picture distribution is digital. Yet they are keeping films in the vaults rather than trust them into the public's hands in high-definition digital formats. Who can deny that this fine new quality lift to America caused the most confusing marketing scenario ever written for a major consumer product! A guy here in Portland made more of a splash marketing his Garden Burgers to the world than has all of the HDTV effort. Had the wheel been introduced so poorly we would be skidding to work on two logs. VisionNo one would be so, well, er....how do I put this delicately--"disconnected" to launch a business without first a sustaining vision as to where that business is going and for what that business means for their customers (in this case society). It is hard to believed that an industry would spend billions of dollars gearing up for the NEXT generation of television and approach the subject as if it were rat poison. All those asking why the launch is not going well should be removed from high office. It has not gone well because the industry did not give it enough importance and made a half-hearted attempt. What else could be expected? Without a full set of thrusters I would be very nervous trying to leave earth's gravitational field on one of those NASA shuttle flights. Everyone in television feared leaving the ground with so many of the thrusters pointing in different directions. We need an alignment across the board from Hollywood, the networks, the independent broadcasters, and the satellite people. Cable will have to follow when they begin to feel threatened, but they have too many eyes on revenue enhancements today to pay enough attention to HDTV, at least until it is a roaring fire.</p>

<p>Once aligned we can see and use the power that this attraction inherently has and someone (not from the accounting department) will lead the programming to the public. That is what is needed now. People not only want more programming, but the programming they want takes full advantage of the performance of their new HDTV "hot rod."</p>

<p>Broadcaster...Thinking through to the core business--the broadcasting of programs--is bringing new dividends to television companies. This has been a good season if for no other reason than someone looked at programming through a different lens,. If you abandon or weaken the core business during this transition by seeking some ancillary grass that looks greener on the other side you risk losing it all. The public is going to tell you shortly in a new survey of DTV receiver owners from HDTV Magazine that they love HDTV. They are willing to support it--viewing many more hours per week--if broadcasters will just commit to it. It would suggest a certain inadequacy should someone think that broadcasters cannot do magnitudes better with HDTV if they now do well in standard television. I personally believe that HDTV is the best means for rebuilding the preeminent status of network television. It is that, or new broadcasters will rise quickly and permanently to prominance who do HDTV best.</p>

<p>It will take time, of course, as if building a profitable data business woundn't. I personally believe that the finest hours of home entertainment lay before us and only transmissible to us via HDTV. It's time to reaffirm that HDTV is the core centerpiece product of the DTV transition. It is recognized that some stations may opt for other choices, but the core of the network/affiliation business will attract the audience back and if cable and DBS don't add HDTV channels. The broadcaster's will be the ones increasingly sought out as sets finally penetrate a territory.</p>

<p>The survey I mentioned earlier makes that abundantly clear. Given the choice viewers will watch an HDTV program when available. The cable channels had best take note for the erosion they once caused to network audiences can be reversed by HDTV.</p>

<p>Next Month I will continue with an alternative view should broadcasters largely default on their transitional efforts.</p>

<p>    <br />
</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Dale Cripps</b>, <b>June  1, 2005 04:43 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 42
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
 				AND entry_id <> 42
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/2005/06/a_brief_history_of_hdtv_to_2001.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
