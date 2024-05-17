<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 131";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Dale Cripps'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 131 AND placement_is_primary = 1";
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
	<meta name="keywords" content="digital television, front auctions, grand alliance, free air, advisory committee, television, digital, spectrum, auctions, HDTV, hdtv, FCC, fcc, channels, broadcasters, atv, ATV, analog, congress, Congress, plan, technology, front, free, services" />
	<meta name="description" content="By Robert Graves Before the Commerce Committee, United States House of Representatives Good morning. My name is Robert Graves and I represent the digital HDTV Grand Alliance-AT&amp;T, General Instrument, MIT, Philips, Sarnoff, Thomson, and Zenith-the partners who developed the digital..." />
	<title>HDTV Magazine Archive &amp; History - 1996 - The Digital HDTV Grand Alliance--Robert Graves Commerce Committee Testamony</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/1996_-_the_digital_hdtv_grand_alliance--robert_graves_commerce_committee_testamony';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('1996 - The Digital HDTV Grand Alliance--Robert Graves Commerce Committee Testamony'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/history/2005/06/1996_-_the_digital_hdtv_grand_alliance--robert_graves_commerce_committee_testamony.php";
		if ($author[img] != '' && 5 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Dale Cripps" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">1996 - The Digital HDTV Grand Alliance--Robert Graves Commerce Committee Testamony</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Dale Cripps</b><br />
				<?=$author_title?>
				Posted on <b>June 26, 2005</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category="></a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/2005/06/1996_-_the_digital_hdtv_grand_alliance--robert_graves_commerce_committee_testamony.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/history/2005/06/1996_-_the_digital_hdtv_grand_alliance--robert_graves_commerce_committee_testamony.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/history/2005/06/1996_-_the_digital_hdtv_grand_alliance--robert_graves_commerce_committee_testamony.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/2005/06/1996_-_the_digital_hdtv_grand_alliance--robert_graves_commerce_committee_testamony.php&amp;phase=2&amp;title=1996%20-%20The%20Digital%20HDTV%20Grand%20Alliance--Robert%20Graves%20Commerce%20Committee%20Testamony&amp;bodytext=By%20Robert%20Graves%20Before%20the%20Commerce%20Committee%2C%20United%20States%20House%20of%20Representatives%20Good%20morning.%20My%20name%20is%20Robert%20Graves%20and%20I%20represent%20the%20digital%20HDTV%20Grand%20Alliance-AT%26T%2C%20General%20Instrument%2C%20MIT%2C%20Philips%2C%20Sarnoff%2C%20Thomson%2C%20and%20Zenith-the%20partners%20who%20developed%20the%20digital...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p>By Robert Graves<br />
Before the Commerce Committee, United States House of Representatives</p>

<p>Good morning. My name is Robert Graves and I represent the digital HDTV Grand Alliance-AT&T, General Instrument, MIT, Philips, Sarnoff, Thomson, and Zenith-the partners who developed the digital high-definition television system underlying the advanced television (ATV) standard recommended to the FCC by its Advisory Committee. I also serve as Chairman of the Advanced Television Systems Committee, a group of more than fifty entities developing standards for digital television.</p>

<p>I'd like to discuss a great technological achievement--digital high-definition television-offering theater-like, widescreen images and 6-channel CD-quality sound, as well as a fundamental improvement in the National Information Infrastructure. Over the last decade, a unique combination of consistent, bipartisan leadership from the Congress and the FCC--and a half-billion dollar private sector investment--has yielded the best digital television technology by far in the world. Our plea today is that government not forsake this superb plan right when it's about to pay off for the American people.</p>

<p>Before addressing spectrum auctions, let me summarize several key points. First, broadcasters must offer HDTV to remain competitive. Broadcasters understand this and plan to make S)TV the primary use of the ATV channel. Second, consumers want the dramatically improved performance HDTV offers. Third, besides dazzling pictures and stunning sound, HDTV will give consumers a high-resolution display and a huge data "pipe" that can deliver a host of other information services, and rapid penetration of entertainment HDTV will lower costs for HDTV applications in education, medicine, business, and national defense. And finally, capitalizing on this American technological triumph will create and preserve tens of thousands of highly skilled jobs and engender economic growth.</p>

<p>An early pivotal FCC decision has driven this successful effort--the simulcast approach whereby existing broadcasters would borrow a second channel to begin transmitting ATV signals while continuing to provide analog TV on their existing frequencies. This meant pulling a rabbit out of a hat--inventing technology to use the taboo channels already allocated to television, but unusable because of interference--providing a practical means for upgrading to digital television without disenfranchising the owners of 200 million analog TVs. It's a loan, not a giveaway.</p>

<p>Generally, using auctions to assign spectrum to competing applicants is a good idea. But auctioning these "loaner" channels is a bad idea that would render broadcast ATV stillborn, undermine free over-the-air television, lock in spectrum inefficiencies, and grossly reduce auction proceeds.</p>

<p>In the first place, any sensible discussion of up-front auctions must assume that the channels would be used for television or other data broadcast services using the proposed ATV standard, because these slivers of spectrum can only be used efficiently for one-way broadcast applications, and even inefficient other uses would require years of additional development and testing. Such auctions would yield far less than the inflated estimates often repeated.</p>

<p>But even so, up-front auctions don't make sense. Would small, local broadcasters even bid? Do we want large outside corporations to supplant today's local broadcasters-just because they have more money?</p>

<p>And what about the future of free TV? If these channels are auctioned, digital television, especially HDTV, will develop as a geographically spotty, premium pay service rather than the ubiquitous advertiser-supported service Americans enjoy today. Free, local, over-the-air TV is vital to our cultural and political fabric, and we should not give it up.</p>

<p>Even just for budget balancing, up-front auctions don't make sense. After analog transmissions cease, the digital channels can be repacked much more tightly, yielding huge blocks of unencumbered, nationwide spectrum that will be far more valuable than the small, noncontiguous taboo channels, bringing perhaps ten times more than up-front auctions.</p>

<p>And auctioning the taboo channels now would mean never getting the analog channels back, and locking in grossly inefficient spectrum use for decades.</p>

<p>Finally, up-front auctions would delay HDTV generally, and render it stillborn for free over-the-air TV. This would throw away America's technological lead, threaten jobs and global competitiveness, and thwart the delivery of valuable services.</p>

<p>Rather than up-front auctions, Congress and the FCC should hasten the conversion to AT; recap the AT channels once analog transmissions cease; organize the recovered spectrum into large, nationwide blocks; and assign the recovered spectrum using auctions. Such a course will enable free over-the-air television to compete, will vastly improve spectrum efficiency, and will maximize the proceeds from auctions.</p>

<p>Unlike up-front auctions, proposals for an "early give-back" of the analog channels have some merit. They offer many of the same advantages as our recommended approach, however, the stated time periods are too ambitious, especially for broadcasters in smaller markets.</p>

<p>We're in the home stretch of an international digital video horse race with a three-length lead, with proven digital technology that has leap-forged efforts in Japan and Europe. But at this late stage, second-guessing of the FCC's well-conceived plan gravely threatens our success-second-guessing by some members of Congress, and quite frankly, by Chairman Hundt--alone among the five commissioners and alone among the four FCC Chairmen who have led this unparalleled bipartisan effort over nearly a decade.</p>

<p>As more members of Congress become fully informed, we're confident that Congress and the Commission will follow through to win this race. We urge Congress to reiterate its support for the FCC plan upon which we've relied, and to direct the Commission expeditiously to adopt an AT standard and issue AT licenses to broadcasters in order to capture the benefits of this fertile technology for the American people. It would be tragically misguided and patently unfair, and would set a damaging precedent if the government were to jerk the rug out from under this splendid American success story</p>

<p>Thank you.</p>

<p><strong>SUMMARY</strong></p>

<p>The Grand Alliance implores Congress and the Federal Communications Commission to do everything possible to promote the rapid implementation of free over-the-air high-definition television (HDTV) and other digital Advanced Television (ATV) services. Specifically, Congress should reiterate its support for the plan the Commission and its Advisory Committee have pursued for almost a decade, including temporarily lending existing broadcasters a second 6 MHz channel during a transition period while the nation's consumers and broadcasters make the conversion to digital television. Congress should encourage the Commission to implement this ingenious plan as quickly as possible.</p>

<p>During the last eight years, through a unique combination of government leadership and private investment and competition, the U. S. has developed and thoroughly tested what is by far the world's best digital television system, dramatically leap-frogging earlier efforts in Japan and Europe to develop high-definition television. After investing half a billion dollars, with no government funding, U. S . industry now stands ready to deploy this fertile technology, giving not only breathtaking improvements in the video and audio quality of entertainment and news television, but also upgrading the nation's information infrastructure to enable the economical delivery of a host of useful information-age services that will help address pressing needs in education, health care, and other areas.</p>

<p>For many months now, dozens of U. S. manufacturing firms offering broadcast equipment, consumer electronics equipment, and integrated circuits have been poised to make the final investments in converting proven prototype technology into competitive commercial products. Capitalizing on this American technological triumph will create and preserve tens of thousands of high-skill, high-wage jobs, engendering substantial economic growth while improving the quality of life for Americans across all economic strata. At this crucial stage, Congress must reiterate its support for the FCC plan, providing the clear and consistent policy that is required to galvanize industry to make the further investments required to capitalize on the commanding U. S. technological lead. In stark contrast to what is needed, proposals in the Congress to auction the spectrum reserved for the conversion to ATV have put industry investment plans on hold and threaten to scuttle the conversion to digital television and to throw away the technological advantage and the potential for economic development that government and industry have fought so hard to achieve. Second-guessing the FCC's well-conceived plan at this late stage is causing significant delay and tremendous uncertainty, and these are anathema to potential investors in this new technology.</p>

<p>Rather than auction the ATV spectrum, a far better course would be 1) to do everything possible to hasten the conversion to digital television, 2) to repack the ATV channels more tightly once today's analog transmissions cease, and 3) to organize the recovered television spectrum into large, contiguous nationwide blocks that could support a wide variety of innovative wireless services. Such reorganized spectrum could be assigned using auctions, and would be far more valuable than the small, non contiguous slices of television spectrum to be used for the conversion to digital broadcast television. Such a course will enable free over-the-air television to compete in the years and decades to come, will vastly improve the efficiency of television spectrum use, will maximize the proceeds from spectrum auctions, and will create jobs and engender economic growth.</p>

<p>Good morning, Mr. Chairman and members of the Subcommittee. My name is Robert Graves and I am a technology and policy consultant representing the members of the digital HDTV Grand Alliance--AT&T, General Instrument, MIT, Philips Electronics, the David Sarnoff Research Center, Thomson Consumer Electronics, and Zenith Electronics--the partners who have worked together under the direction of the FCC's Advisory Committee on Advanced Television Service to develop the digital high-definition television system that is the basis of a new advanced television (AIV) standard the Advisory Committee has recommended to the Commission. I also serve as Chairman of the Advanced Television Systems Committee, an industry group of more than fifty corporations, associations and research institutions developing standards for digital television.</p>

<p>I'd like to speak today about one of our nation's greatest technological achievements-the development of all-digital high-definition television, offering pristine, theater-like, widescreen images and 6-channel CD-quality surround sound, and a whole lot more--a fundamental improvement in the National Information Infrastructure (NII) that can soon become widely available to all Americans. This achievement would never have been possible without a unique combination of consistent, bipartisan leadership from the Congress and the FCC, and private investment in a process that used first competition and later cooperation to yield by far the best digital television technology in the world--with proven performance that surpassed even the lofty expectations of its developers. Our plea today is that the Congress and the FCC see this ingenious plan through the last stages to a successful conclusion. Don't forsake this superb plan right when it's about to pay off for the American people.</p>

<p>In 1987, when the FCC began the process of defining an HDTV transmission standard, the United States was nowhere compared to Japan and Europe where decade-long, government-funded efforts were poised to deliver analog HDTV. However, with strong support from Congress and visionary leadership from the FCC and from former FCC Chairman Richard Wiley who was asked to lead the Commission's Advisory Committee, by 1993 the U.S. had leap-frogged over Japan and Europe into a preeminent position in the development of all-digital HDTV. From an original field of 23 different system proposals, the Advisory Committee selected four all-digital systems as finalists, and with encouragement from the Advisory Committee and the Commission, the proponents of these systems formed the Grand Alliance, agreeing to develop a single digital system that combined the best features of each competing system.</p>

<p>Although the Advisory Committee raised the performance bar substantially in specifying the requirements for the combined system, the Grand Alliance built a world-leading prototype system that cleared the bar with room to spare in exhaustive laboratory and field tests conducted last year. Given this stellar performance, last November the Advisory Committee recommended an ATV standard to the Commission based on the Grand Alliance system. Although no government funding was involved, this stunning collective achievement did not come free. Dozens of companies invested upwards of $500 million and devoted the best efforts of hundreds of volunteers in the Advisory Cornmittee process over almost a decade. The Grand Alliance members alone have invested approximately $300 million and some of their best engineering talent--at the expense of other opportunities--to get to this point.</p>

<p>An early pivotal decision by the Commission formed the basis for this successful effort. This was the simulcast approach whereby existing broadcasters would be given the temporary use of a second 6 MHz television channel to begin transmitting ATV signals while continuing to provide today's analog television transmissions on their existing frequencies. This was something of a rabbit out of a hat, because the extra channels to be used are the so-called taboo channels, channels like Channel 8 in Washington that are already allocated to television service, but can't be used for analog TV because of interference considerations. Digital transmission technology, however, produces signals that are much more resistant to noise and interference. One of the "miracles" of this technology is that digital television signals can be transmitted in these otherwise unusable channels and provide much higher resolution pictures (five times as much picture information) with an equal or better coverage area than analog television, using just one-sixteenth of the power at the transmitter. Thus, the Commission's simulcast decision gives broadcasters a practical means for making the transition to digital television without disenfranchising the owners of approximately 200 million analog television sets. The simulcast plan is an ingenious transition plan, not a "spectrum giveaway."</p>

<p>Another "miracle" of this technology is the tremendous flexibility that comes along for the ride in deploying a digital HDTV transmission system. The system uses a packetized data transport system with packet headers that identify the type of data that each packet carries. This means that in addition to HDTV, the transmission system can carry three or four simultaneous programs of standard-definition television (SDTV) at other times of the day, or numerous audio programs, software, stock quotes, sports scores, weather reports or a host of other potential information services (but not two-way services such as mobile radio communications). Thus, when consumers invest in HDTV they'll get dazzling pictures, stunning sound and a whole lot more. They'll get a high resolution display and a huge "piper into their homes whereby each TV channel could deliver 19 million bits of data per second--a speed about 1,000 times faster than today's typical computer modem. Their investment in entertainment television will provide an economical means for delivering information services that will address pressing needs in health care, education and other areas.</p>

<p>The Grand Alliance HDTV system puts the U. S. way out in front in the race to develop digital video technology--a key technology that will enable innovative multimedia applications beyond entertainment, including applications in education and training, medicine, business communications, and national defense. Getting a lead in broadcast entertainment and news is important, because the high volume production associated with consumer electronics will lower the costs for these other uses of HDTV.</p>

<p>No one is more excited than the Grand Alliance about the opportunities for flexible use of the ATV transmission system. Attached to this testimony are materials filed with the FCC that describe in greater detail the benefits that such flexible use by broadcasters can provide. But these materials also thoroughly explain our belief that the centerpiece application of the ATV channel will be and ought to be high-definition television. We're convinced that in the not too distant future, entertainment and news television will be viewed predominantly in HDTV. It will be just as unusual then to watch prime television programs in standard definition television as it would be to watch them in black-and-white today. The only real question is whether government policies will provide local, free over-the-air broadcasters an ability to upgrade their service to HDTV, or whether local broadcasters will have fallen by the wayside, unable to compete with technically superior HDTV services offered over cable, telephone and satellite facilities.</p>

<p>We believe that broadcasters must be able to offer HDTV if they are to remain competitive with other delivery media in the years and decades to come. This means lending each broadcaster a full 6 Liz channel during the transition period, because HDTV cannot be provided with anything less. And we believe that sticking with the FCC's long-standing plan--as reinforced in the new telecommunications act--to limit initial eligibility for these transition channels to existing broadcasters is the best course for preserving free, over-the-air television, for promoting the most rapid possible conversion to digital television, for using scarce spectrum resources most efficiently, and for maximizing the revenues that could flow to the US. Treasury from potential auctions of spectrum.</p>

<p>Two misconceptions have been heard frequently over the past few months regarding HDTV: first, that broadcasters have no interest in providing HDTV; and second, that consumers have no great desire for higher-resolution pictures. Neither of these ideas could be further from the truth. As shown in the attached documents filed with the FCC, the vast majority of broadcasters recognize that HDTV is essential to their survival and they plan to make HDTV the centerpiece application provided over the digital television channel. The attachments also describe consumer research demonstrating conclusively that consumers who have actually seen HDTV are prepared to pay a substantial premium, if necessary, for the dramatically improved performance it offers, and that substantial early sales will provide the volumes required to drive costs and consumer prices down rapidly. We've shown HDTV to thousands of people, and almost without exception they only want to know how soon they can buy a high-definition set. The members of the Grand Alliance, and many other firms in the industry, have already bet hundreds of millions and are prepared to invest many hundreds of millions more on our belief that HDTV will be a resounding success in the marketplace.</p>

<p>Regarding proposals to auction the ATV conversion channels, we recognize that the electromagnetic spectrum is an extremely valuable natural resource that belongs to the citizens of this country. And we believe that using auctions to assign spectrum to competing applicants is, generally speaking, a good idea. But auctioning these "loaner" channels planned for the conversion to digital TV is a bad idea--a bad public policy that would render broadcast ATV stillborn, undermine the ability of free over-the-air television to compete technically in the decades to come, lock in an inefficient usage of scarce spectrum, and grossly reduce the funds that ultimately could flow to the Treasury by auctioning recaptured television spectrum at the end of the transition.</p>

<p>In the first place, most of the discussion surrounding such auctions, and most of the wildly inflated estimates of its value, mistakenly assume that this spectrum can readily be used for almost any purpose. In fact, this spectrum is substantially encumbered by the need to protect surrounding analog television signals from interference. The "miracle" of shoe-horning in 1,600 additional TV channels only works for a low-power, digital, one-way, point-to-multipoint service where the digital and analog transmitters can be more or less co-located. This spectrum is not well-suited for two-way mobile communications, the application most frequently associated with the incredible estimates of its auction value. Indeed, without years of additional development and testing that would still result in a much less efficient use of these channels, these slivers of spectrum interspersed among existing analog television channels can only be used for one-way, digital, point-to-multipoint broadcast applications using portions of the Advisory Committee's proposed ATV standard or something very smaller.</p>

<p>So any sensible discussion of up-front auctions should assume that the channels would be used for television broadcasts or other data broadcast services using the proposed ATV standard, and the proceeds of such auctions would be far less than the inflated estimates commonly repeated. But even so, up-front auctions just don't make sense. Who would bid? The small, local broadcaster? Or does Congress want telephone companies or other large outside corporations to come in and outbid and supplant today's local broadcasters-just because they have more money?</p>

<p>And what about the future of "free" advertiser-supported TV? If these channels are auctioned, can we expect free TV? With auctions, digital television, especially HDTV, would develop as a geographically spotty, premium pay service rather than the ubiquitous, free service that Americans enjoy today. Free over-the-air TV is important to the cultural fabric of America, including our democratic political processes--both for the 35% of households who rely directly upon it and for the 65% who watch it predominantly even when it's carried over subscription services like cable. We should not give it up.</p>

<p>Even if Congress' only concern were maximizing the proceeds from spectrum auctions in order to help balance the budget, up-front auctions wouldn't make sense. After today's analog TV transmissions cease, the digital channels can be repacked much more tightly, leaving perhaps as much as 150 or even 200 MHz of recaptured spectrum that can be organized into large, nationwide contiguous blocks that could be used for a wide variety of wireless services, including two-way mobile radio services. This huge amount of unencumbered spectrum would be far more valuable than the small, non contiguous slices of ATV transition spectrum, and would yield far more--perhaps ten times more--than up-front auctions of today's taboo channels. Is our government so shortsighted that it can't see the value of a 10-times appreciation over ten years?</p>

<p>And auctioning the taboo channels now would mean never getting the analog channels back. It's one thing to reclaim one channel after lending existing broadcasters a second channel in order to enable a practical upgrade of their service, and quite another to confiscate their licenses, even if they're already withering away with an outmoded analog service. Up-front auctions would mean forgoing a unique opportunity to put in place a vastly more efficient TV spectrum plan and would lock in a grossly inefficient use of this scarce resource for decades to come.</p>

<p>And finally, up-front auctions, or even the continued threat of up-front auctions, would delay the introduction of HDTV generally, and render it stillborn for ubiquitous free over-the-air TV. This would squander the US. technological lead, eliminate jobs and reduce global competitiveness, and thwart the delivery of valuable services to consumers. The apparent willingness of some government policy makers to throw this all away is tremendously frustrating to those of us involved in developing the standard over the last decade.</p>

<p>Rather than up-front auctions of the ATV spectrum, Congress and the FCC should 1) do everything possible to hasten the conversion to digital television; 2) repack the ATV channels more tightly once today's analog transmissions cease; 3) organize the recovered television spectrum into large, contiguous nationwide blocks that could support a wide variety of innovative wireless services; and 4) assign the recovered, reorganized spectrum to competing applicants using auctions. Such a course will enable free over-the-air television to compete in the years and decades to come, will vastly improve the efficiency of television spectrum use, will maximize the funds that can flow to the Treasury from spectrum auctions, and will create and preserve jobs and engender economic growth.</p>

<p>Unlike proposals for up-front auctions, proposals for an "early give-back" of the analog channels have some merit. As with our recommended approach, in principle they would allow for a practical, but expeditious conversion to digital television, would greatly improve television's use of scarce spectrum resources, and would maximize the proceeds from auctions of recovered spectrum. However, the specific time periods recommended are too ambitious, especially for broadcasters in smaller markets.</p>

<p>In the attached documents filed with the FCC, the Grand Alliance argued that the current 15 year transition period included as part of the FCC plan can be reduced to twelve or even ten years. We also urged the FCC to set a nominal target date for the cessation of analog TV broadcasts, to evaluate progress along the way, and then to fix a final end of the transition period with three years advance notice of the final date for consumers and broadcasters. Proposals to auction channels after seven years with the spectrum actually relinquished after ten years appear on their face to be consistent with this time frame, however, they overlook several important factors.</p>

<p>First of all, the specific dates of 2002 and 2005 are not appropriate, because it's already 1996 and the standard has not yet been accepted by the FCC nor licenses assigned to broadcasters.</p>

<p>Moreover, the current FCC plan allows six years for broadcasters to apply for ATV licenses and to construct digital facilities. The ten-year transition period cannot begin until the standard is formally adopted, licenses are assigned, facilities are constructed, and ATV transmissions commence. Although we believe that most broadcasters will be able to be on the air with digital service in much less than six years, the smallest broadcasters may need that much time. Thus, although auctions of prospective recovered spectrum in theory could be held at any time, the earliest actual availability of the spectrum would probably be 2008 in the largest markets and up to five years later in the smaller markets, assuming that the proposed standard is adopted by the FCC and ATV licenses are assigned to existing broadcasters before the end of this year.</p>

<p>Although we believe the FCC can do much to promote a rapid transition and we're convinced that consumers will flock to digital television, especially HDTV, the ability to cease analog broadcasts will depend on the extent to which consumers who still rely exclusively on over-the-air broadcast television have invested in digital televisions or at least in converters that will allow them to view digital signals on their old analog TVs. In light of these uncertainties, we encourage Congress not to legislate a date certain for the return of analog TV frequencies, but rather to direct the FCC to do everything possible to hasten the conversion, and to authorize the Commission to assign recovered spectrum using auctions, on a market-by-market basis, if appropriate, including auctions prior to the actual availability of the spectrum.</p>

<p>In conclusion, our nation is now in the home stretch of an international digital video horse race with a three-length lead, with proven all-digital HDTV technology in hand that has leap-frogged earlier efforts in Japan and Europe--technology that will deliver quantum improvements in entertainment television and a host of other valuable services, while creating jobs and engendering economic growth. The fundamental government and industry planning are long since done, the pioneering technical work completed. And now with the finish line in sight, some government leaders have stopped the race while they debate the various virtuous contributions to society that horses can make. At this late stage, second-guessing of the FCC's well-conceived plan threatens to scuttle the whole process and throw away these hard-won benefits. Second-guessing by some members and even leaders of Congress, and quite frankly, by Chairman Hundt himself--alone among the five commissioners and alone among the four FCC Chairmen who have led this unparalleled bipartisan effort over nearly a decade--is causing significant delay and tremendous uncertainty, and these are anathema to potential investors in this new technology.</p>

<p>The Grand Alliance is confident that as more members of Congress fully understand this situation, Congress and the Commission will indeed show the will to win this race. We urge Congress to reiterate its support for the FCC plan upon which industry has relied over the last decade; and to direct the Commission to adopt an ATV standard, to issue ATV licenses to broadcasters, and to otherwise implement its ATV plan as expeditiously as possible in order to bring the benefits of this fertile technology to the American people. It would be tragically misguided and patently unfair, and would set a damaging precedent if the government were to jerk the rug out from under this splendid American success story.<br />
</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Dale Cripps</b>, <b>June 26, 2005 01:39 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 131
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
 				AND entry_id <> 131
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/2005/06/1996_-_the_digital_hdtv_grand_alliance--robert_graves_commerce_committee_testamony.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
