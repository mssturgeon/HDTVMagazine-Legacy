<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 149";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Dale Cripps'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 149 AND placement_is_primary = 1";
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
	<meta name="keywords" content="grand alliance, dale cripps, best picture, robert wright, same time, HDTV, hdtv, new, spectrum, digital, nbc, NBC, business, cable, standard, programs, wright, Wright, broadcasters, FCC, broadcast, fcc, big, could, broadcasting" />
	<meta name="description" content="Radio spectrum is the chief asset of the American broadcast system. As long as available it has been granted free to qualified applicants in the interest of public service. Without radio spectrum the broadcast business would disappear. The more spectrum broadcasters can have, the better it is... for them. That view must be kept in mind when divining the meaning behind statements touching upon radio spectrum. 
" />
	<title>HDTV Magazine Archive &amp; History -  1995 -- Being Wright For HDTV</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/_1995_--_being_wright_for_hdtv';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes(' 1995 -- Being Wright For HDTV'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/history/2005/07/_1995_--_being_wright_for_hdtv.php";
		if ($author[img] != '' && 5 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Dale Cripps" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2"> 1995 -- Being Wright For HDTV</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Dale Cripps</b><br />
				<?=$author_title?>
				Posted on <b>July  8, 2005</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category="></a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/2005/07/_1995_--_being_wright_for_hdtv.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/history/2005/07/_1995_--_being_wright_for_hdtv.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/history/2005/07/_1995_--_being_wright_for_hdtv.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/2005/07/_1995_--_being_wright_for_hdtv.php&amp;phase=2&amp;title=%201995%20--%20Being%20Wright%20For%20HDTV&amp;bodytext=Radio%20spectrum%20is%20the%20chief%20asset%20of%20the%20American%20broadcast%20system.%20As%20long%20as%20available%20it%20has%20been%20granted%20free%20to%20qualified%20applicants%20in%20the%20interest%20of%20public%20service.%20Without%20radio%20spectrum%20the%20broadcast%20business%20would%20disappear.%20The%20more%20spectrum%20broadcasters%20can%20have%2C%20the%20better%20it%20is...%20for%20them.%20That%20view%20must%20be%20kept%20in%20mind%20when%20divining%20the%20meaning%20behind%20statements%20touching%20upon%20radio%20spectrum.%20%0A&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p><em>It's now ten years since this article was published in various magazines. In it you will find some of the rich mechanizations which occurred prior to the standard being set and the commercialization activities begun. For those who take HDTV for granted this article is one of many that will give you a glimps at just how difficult a process it was to get clear picture to your home. There were powerful advocates, of course, but so were there powerful detractors.</em> _Dale Cripps, 2005</p>

<p><br />
<strong>"I Don't Think We Have A Business Unless We Have the Best Picture."</strong>--Robert Wright, President, NBC</p>

<p>By Dale Cripps <br />
1995</p>

<p><br />
Has the HDTV business heard the starting bell at last? At first blush it appears so. In April 1995 the president of NBC announced that the peacock network would begin broadcasting digital HDTV programs in 1997. Can others be far behind? Should we start saving our pennies and cashing in our 401s for a Christmas HDTV set? Or, is this announcement more vapor-speak?</p>

<p>Radio spectrum is the chief asset of the American broadcast system. As long as available it has been granted free to qualified applicants in the interest of public service. Without radio spectrum the broadcast business would disappear. The more spectrum broadcasters can have, the better it is... for them. That view must be kept in mind when divining the meaning behind statements touching upon radio spectrum. </p>

<p><br />
Broadcasting is today by far the most efficient means for sending "information" to the American public. TV signals reach more households than do telephones, and certainly more household than do cable connections. In such a gargantuan and influential business the big bucks are paid to those who are right more often than they are wrong. One big bucks executive is none other than Mr. Wright--Robert Wright, the president of NBC. Wright never shrinks from an opportunity to increase shareholder values. He has propelled the TV arm of GE well beyond the familiar entertainment and news network we all know. In the last ten years he fashioned two successful cable channels, acquired interest in 19 other cable services, launched NBC Super Channel in Europe, the NBC Data Net, and a Spanish language news channel in Latin America.</p>

<p>With fanfare and considerable surprise, Robert Wright dashed to the leading edge of technology as he came out strongly for HDTV. He announced in April of this year that his network would be ready to launch the broadcasting of HDTV programs and signals in 1997. Doing so will mark indelibly the day and hour when the commercialization phase of the HDTV begins. Others must follow such an important lead to remain competitive or face a future in an AM radio-type status. But many ask why NBC would decide to do this? HDTV has never looked like a bonanza, or even a business to the existing signal providers. Wright begins his answer, "It is inconceivable to me that broadcasters, of all people, would want to be in any way left out of complete parity in high definition. Unlike the satellite operators--and we are both--the broadcaster is paranoid about having anything but the best picture on the air. I don't think we have a business if we end up with less than the best picture."</p>

<p><strong>Things Constantly Change In Broadcasting</strong><br />
Like no other, the business of broadcasting is always in full public view. The Connie Chung dismissal illustrates this point only mildly. It is a shareholder owned entity thriving or thrashing from both government and public perceptions, tastes, and opinions. Broadcasters must frequently shift gracefully from one political position to another while appearing fully confident and consistent in serving all parties equally. Both defense and offense are the tools of the trade in combating innumerable threats which surface daily. One moment the threat is a hot program competitor, the next a whiz bang revolutionary technology from left field, another might be a curve ball wielded by government regulators. As a result, the managers of network television have become sublimely nimble in choosing advance or retreat, offense or defense as tools of their trade. These survivors have grown extremely skillful and are fully sensitized to seizing major opportunities, the likes that the lesser-experienced fail to recognize. That's why they get the big bucks.</p>

<p><strong>NBC Was Always A Player</strong><br />
As it did with color, NBC has played a significant role in advanced television in the US. Upon learning in 1987 that broadcasters needed to act on the HDTV question, the peacock network teamed with its world renowned R&D arm--the David Sarnoff Research Center in Princeton, New Jersey. They set out to find both a business model for HDTV and its most fitting technical solution--one that adequately responded to the "foreign led threat".</p>

<p>NBC's first of many differing positions on HDTV was to agree quickly with their adversary and become an advocate for HDTV by way of a "reasonable" industry proposal. NBC backed a compatible EDTV (Extended Definition of the old standard) solution, one that could be cheaply added to the broadcast plant and still evolve to HDTV later when displays were bigger, clearer, and cheaper. They reasoned that the compatible approach, while a forced compromise, was the only business decision that could honestly be made. But a FCC decree (initiated by George Bush) in 1991 demanded a departure from the old NTSC standard, making compatibility out-of-the-question. NBC abandoned quickly their compatible system while at the same time praising the FCC decision as a stroke of genius. That same day they allied their strength in a tighter pact with Thomson and Philips in an all-digital non-compatible approach.</p>

<p>A new world-beating digital standard (submitted by the Grand Alliance --Philips, Thomson, General Instrument, Zenith, AT&T, and MIT) is the end result of the eight years of work since the FCC began the process. As noted in earlier editions of Widescreen Review, the Grand Alliance system can do more than decode HDTV signals. It permits a high degree of flexibility, i.e., it can allocate varying amounts of bits per second to represent the image of a program(s), or send data services. Choosing anything less than the peak HDTV requirement of 18 Mbit/s would allow for more programs and data on the same 6 MHz channel. At least four programs with the current NTSC image quality can be squeezed into just one 6 MHz channel using the new system. Either decoder boxes or new digital sets (anything from standard resolution to HDTV) can decode the signals for display (or recording on the new digital VCR). Later on with improved encoding there could be even more programs per channel. This has been a somewhat intriguing alternative to broadcasters--one which is more than a replacement of one old NTSC channel for one new HDTV channel. This flexibility has decidedly enhanced strategies to compete with cable by delivering more over-the-air choices in a given region. Imagine a community where there are five standard channels. In the multiplex digital world those five could deliver to their community 20 or more separate ghost-free, noise free, programs, and with either more local content or cable-like programs, or premium pay movie services--enough to dissuade many from hooking up to their local cable or telephone TV program providers and turning back to everything offered from over-the-air.</p>

<p>Cable doesn't like this idea and wants broadcasters to be forced by the government to deliver full bodied HDTV in the new channel. But this imposes a dilemma on cable as well since the 'must carry' rules makes them carry the bandwidth intensive HD programs of all the local broadcasters at the expense of their own valuable spectrum.</p>

<p>Lobbying efforts in Washington by the National Association of Broadcasters had intensified over the last two years. They were asking for Congressional blessings in a flexible use approach for the new spectrum. That left HDTV appearing again little more than a side show in a completely changing all-digital era. This request for flexible use was heard in Congress and is written into both the Senate and House versions of the 1995 Telecommunications Act.</p>

<p><strong>Manufacturers Confused</strong><br />
Long suffering manufacturers had hoped that the FCC, or better Congress, would mandate the use of the new conversion channel for at least some HDTV programming. That mandate looks to be lost in this Telecommunications Act of 1995, if it passes. The manufacturers have never seen eye to eye with the broadcasters believing they were authors of numerous delays designed to slaughter the whole movement. Their first grievous concern was that the NBC compatible approach would scuttle, or, at a minimum, slow the market appeal of advanced TV due to quality compromises. On the other hand since NBC was a major network involved as a system proponent they were eager to believe that no matter what was being said or done, the lead taken by NBC meant that broadcasters were really behind the HDTV movement. That was bolstered by the fact that tens of thousands of hours of voluntary committee work had been contributed by broadcasters, cable, and satellite participants. It would, therefore, end in a great new business in America once a standard was set by the FCC. But NBC too withdrew from the FCC process when the Grand Alliance was formed in the spring of 1993, leaving an uneasiness among those same manufacturers and most of the industry observers. Committee work drew to a close and participants moved on to new things. The business of HDTV looked as if it would have to find a champion outside of the usual ranks, if even that. The wind was falling out of the sails and the Electronic Industries Association HDTV/ATV committee--made up of executives from the world's giant consumer electronic manufacturers--were adrift and losing heart.</p>

<p><strong>NAB</strong><br />
Critical mass is when there is enough interest to form a commitment that in itself has the power to discard the old and introduce the new. In the field of consumer television this critical mass is made up of thousands upon thousands of people who must be motivated to act their parts out at whatever cost until the bridge is crossed. The consumer is part of this critical mass, but not yet exposed nor participating in adopting the "new." The level of response and praise that has come to the Grand Alliance most businesses would die for. But when it comes to moving into the next generation of television, a tremendous strength must be aggregated. To date this strength has not been so aggregated and it pains this writer to say that with all of the smiling faces and self-congratulatory statements being made I have to continue saying that this is not yet sufficient. --Dale Cripps</p>

<p>The Grand Alliance valiantly demonstrated their hardware and transmission system at the '95 NAB Convention in Las Vegas. While it worked extremely well, the veterans in the industry passed by the tucked away exhibits in the Hilton Hotel with little more than a casual interest and perfunctory questioning--mostly to see how digital compression might apply to saving them money for what they are already doing. A brief fire of enthusiasm lit up when news of Wright's statements (carried to the NAB by NBC VP of Engineering, Mike Sherlock) wafted around the hallways. That faded into a disbelief, endemic to the general cynicism that has hounded HDTV over the last five years. The AMSTV's board breakfast, where a full endorsement of HDTV transmission was officially given by its several hundred broadcast members, was another big boost. But again, this lift seemed short lived due to the same heavy cynical attitude supported mostly by smaller broadcast and cable operators who feared the expense.</p>

<p>Even with the hot demonstrations, the industry endorsements, and a general underlying feeling that in spite of everything HDTV was coming along, no jubilation occurred among the developers. They too suspected that a public card was being played for spectrum rather than a sincere expression of intent to engage HDTV. Eager antenna and transmission manufacturers also talked it up and gave impressive demonstrations, but they were discounted as but eager vendors. The neophytes to HDTV ogled it, but acknowledged it was just "too much for them" to handle. It was still something for the big guys, or a new big guy who collected other big guys to the task of launching the next generation of moving imagery.</p>

<p>The best of all news was that HDTV production equipment looked to be cheaper. JVC was showing their new 1 million pixel 3/4" CCD camera for plus or minus $65,000--a far cry from the $1/2 million needed to acquire Sony's 1" CCD jewel. Also the W-VHS was demonstrated for under $10,000 for professional uses, i.e., business videos. While both of these new JVC entrants are compromises to the bandwidth of "traditional" HDTV, weep not... they look very good in comparison to studio grade NTSC.</p>

<p><strong>Never Was All Roses</strong><br />
To broadcast executives the writing had been on the wall from the very beginning. An expensive non-compatible HDTV approach looked like a turkey destined for major cooking. Non-compatible solutions would mean huge capital investments by the broadcast industry in new expensive equipment, and with no assurance whatsoever that the public would buy the new and expensive receivers to make potential any return on their investment. There hasn't been any more optimism among the three networks since it was learned they could use the new HDTV system to transmit several programs of old standard quality to digital receivers or receivers with a decoder box. Fox is a notable exception and they have championed this approach. Rupert Murdoch always hated the fact that he missed the cable opportunity in the US. Forgetting the receiver problem for the moment, he has both experience and programming from his European satellite business for dealing with multiple channel distributions. To the other networks programming is the big, big cost, so multiplexing may mean program hell. How do you continue to divide up a finite market and at the same time concentrate enough money to make high quality attractive programming? On the other hand, the non-compatible direction for advanced television provided a perfect smoke screen for which increasingly valuable spectrum might be obtained from the FCC at very little cost. That was worth pursuing by continuing with HDTV as their prime Trojan Horse.</p>

<p><strong>Critics Take Their Shots</strong><br />
With a shrinking support for HDTV clearly the case in knowledgeable circles critics like MIT Media Labs' Nicholas Negroponte seized the opportunity to get his point across. Above all Negroponte deplores any fixed HDTV standard. He favors an open device with multiple capacities able to accept scaled signals--more computer than television. In his best selling book, Being Digital, he suggests that if they are granted a license for transmitting 20 million bits of digital information every second "the very last thing a broadcaster would want do with it is to broadcast HDTV." He cites program scarcity and the limited receivers as basis for his reasoning. Negroponte also wants broadcast signals out of the airwaves. They should, in his view, be sent over wires and fiber cables and deliver variable resolution images and any number of aspect ratios to a variety of receivers--some with higher performance than others. Terrestrial spectrum, he concludes, should go to those appliances that travel. The devices which are least portable should be connected to a wire. TV set manufacturers wonder how you sell a device that does so many things. Computers outsold TV sets last year, so the answer may be clearer to those people than it is to traditional consumer electronic manufacturers.</p>

<p>In another new book --Television, Today and Tomorrow--past president of CBS, Gene Jankowski says that the American networks can't afford to enter HDTV at the sacrifice of their installed base of NTSC receivers. He points to the dismal showing in the Japanese HDTV satellite experiment and suggests that no one else can afford it either, thus reducing the competitive threat to nothing. More critically, the physical coverage from the traditional broadcast VHF band is greater than that from the poorer propagating UHF, which is the digital band. (The VHF band is to be reclaimed by the FCC following a 15 year transition period for the digital services. The FCC will reassign the reclaimed spectrum to whomsoever is in most need of it at that time, i.e., who will pay the most for it.)</p>

<p>Jankowski questions picture quality as being important to anyone. The one thing he doesn't question is the importance of original programming for creating a success in network broadcasting. He notes that American programs are dominant throughout the world and yet the production of them is in the 525 standard (used in the USA), fully one hundred lines less than the European standards of 625 scanning lines. "Despite the lower-quality picture, American programs are the standard of the world," says Jankowski, "proving that content is more important than technology." While 80% of the programs exported are produced on film, the post production for years was done using the 525 standard. The finished product was then upconverted to the 625 standards for distribution in PAL and SECAM countries. Complaints on technical quality have been heard, but to no great economic peril. With new digital recording such shortcomings have come under better control. Filmed programs are post-produced now using the 4:2:2 international digital standard, which converts to the optimum of either analog 525 or 625 standards.</p>

<p><strong>So, Why, NBC? What Does Wright See That These Others Don't?</strong><br />
The leading question is: With such negative commentary coming from high places why did Robert Wright commit NBC publicly to broadcast HDTV in 1997? In fact, Wright was not alone, just more visible. As mentioned above this year's National Association of Broadcasters Convention in Las Vegas in early April the Association of Maximum Services Telecasters--a major Washington based trade association focused to technical matters--at their annual breakfast endorsed the idea that broadcasters enter the new digital era with the commitment to broadcasting HDTV signals in prime time. This came as a surprise to many in the industry and to seasoned observers sounded too pat to be true. What was behind it? Why not promote the more economically promising multiple standard resolution channels, as had the NAB's VP John Abel? Why not kill it all-together while getting their hands on the newly reserved spectrum?</p>

<p>Columnist William Safire said in his nationally distributed column that this new spectrum for HDTV and other digital services is just too damn valuable in this day of budget contractions to be given away free. Spectrum auctioning has become the hot money making ticket in Washington. Over 7 billion dollars has been raised this year alone by the FCC auctioning off segments of it to the personal communicator and other telephone-like portable appliance companies. But auctioning of spectrum to broadcasters to enable them to compete effectively with cable and satellite HDTV services was not part of the original deal struck with the FCC in 1987. Only if the new spectrum is used for other than HDTV digital services is there any serious threat of changing the rules and ordering up what surely would be a very expensive auction. It could be, in fact, as much as a $40 billion price tag to broadcasters. (Interpreted from recent auctions of non-broadcast segments, experts contend that the broadcast spectrum could yield to the government treasury between $20 and 40 billion dollars at auction!) "It is far better to say you will do HDTV and get the spectrum assigned free. Then do as you want with it once it is in your hands. Its even OK if you actually mean what you say," came the sardonic advice from an FCC member at the NAB Convention.</p>

<p><strong>On The Other Hand...</strong><br />
Accusing Wright of using his HDTV strategy to avoid spectrum costs may be a bit harsh. They do seem to be linked topics in his talks, however, giving rise to the suspicion. He is, in essence, doing no more than coming full circle to NBC's original position on HDTV: If today's networks are to remain a high value to the American public they must be able to deliver HDTV signals and programs. "Of all the programmers (read satellite/cable, VCR/videodisk)," he said twitting Negroponte's statement above. "The broadcaster is the last one who wants to have anything but the best picture in the home." The market for "video-in-the-home" is estimated by Wright at $100 billion annually, and climbing. "Others outside of broadcasting will inevitably do HD," said the former GE executive. He doesn't want to lose ground in that big business.</p>

<p>Even if Wright's public statements have an embedded spectrum strategy contained in it, it still rings true. It will take only one successful HDTV service from cable or DBS to wake up the rest. Who should be first? This is a view consistent with all of the broadcaster's first industry-wide response to HDTV in 1987. Though the conditions for broadcasting may have dramatically changed since then, what they said in their 1987 petition to the FCC--namely that they could not afford to get caught with their pants down on the HDTV issue--is no less true today. To do that they needed a new standard and more spectrum. Still true. Competitors could deliver it without restrictive regulation or spectral scarcity. Still true--even truer considering that there is a successful DBS business going now which could easily deliver across the US perfect HDTV signals into their existing dishes from just one point in space.</p>

<p>Multiplexing is probably not such a good business idea either. Jankowski notes in his book that talent is the scarcer commodity, far more so than is spectrum or channels. If you continue to divide an audience, what choice is left but to increasingly reduce the concentration of money needed to acquire the talent to make the all-important attractive programs. You also tend to spread talent too thin, reducing the over-all-quality of the television experience. He believes there is room for no more than 6 or 7 national entertainment networks. They will produce most of the new programs and the other 500 channels, should they materialize, will acquire the afterlife syndication rights to them later. Original programming is the engine that drives any national network, and the network business is still unique and as good as free gold. Further fragmentation of audiences is more likely to affect the fragmenters' rather than the general interest audience served by the networks. Wright said about it in an April interview in Broadcasting & Cable Magazine that NBC has no business plan to exploit digital flexiblity other than to pay a little from ancillary services some of the conversion (to HDTV) costs. "I am disappointed there is so much focus on flexibility because in the end, the nature of broadcasting is such that the only way we will be able to survive is by offering all of our programming in what is the most attractive transmission and production scheme available."</p>

<p>Will these super salesman--head of the networks--earn their big bucks this time by saying anything they must to get $40 billion worth of spectrum assigned to their industry for free? Or, with enlightened self-interest will they make a preemptive HDTV strike to insure their future against any cable, telephone, or DBS ambitions... and get the spectrum fee at the same time? Or, could they actually be sincere in wanting to start the HDTV signal service as an expression of 1) gratitude for their current wealth mined from the pockets of the consumers, and 2) their good will, love, and respect for the American public... and get the spectrum free and have the preemptive strike all at the same time? I don't know which will be their reason, if any. If they hold to the third and last one, however, they get everything in the bargain, including the respect and appreciation, and perhaps even the love of their audiences.</p>

<p>We consumers are going to provide the next, and most crucial response to the HDTV question. We will undoubtedly ignore it if we feel somehow exploited by it. Or, we will jump on it like no consumer product ever introduced if we are greatly stimulated by a commitment to deliver attractive programming. Programming--content--will always reign as king. But given the choice to enjoy the programming in the old standard or in the new HDTV standard, the contest is over for me. Will we pay for HDTV receiving equipment if one or more signal providers gather up the courage to deliver us top flight HDTV program/signals to our homes via over-the-air, through a cable, down a fiber optic strand, etched on a little 5 inch disk (DvD), or on tiny digital video cassette tapes? How we start directing today our consumer electronics dollars will determine more than anything else that is happening now the future for advanced television and HDTV.</p>

<p>All aboard!</p>

<p>Dale Cripps, June 1995</p>

<p>Copyright 1995-2005</p>

<p>  </p>
		</div>
	</div>
	<p class="posted">Posted by <b>Dale Cripps</b>, <b>July  8, 2005 09:43 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 149
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
 				AND entry_id <> 149
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/2005/07/_1995_--_being_wright_for_hdtv.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
