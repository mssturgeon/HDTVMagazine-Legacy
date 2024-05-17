<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 103";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Dale Cripps'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 103 AND placement_is_primary = 1";
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
	<meta name="keywords" content="cable operators, dtv tuners, broadcast flag, intellectual property, cable satellite, dtv, DTV, cable, digital, FCC, fcc, consumers, local, signals, broadcasters, transition, new, content, services, equipment, quality, broadcast, rights, flag, analog" />
	<meta name="description" content="The transition to digital television (DTV) is occurring in all the major industrialized countries and in a selected number of developing nations. I will focus today on the transition in the United States as well as discuss the experience of other countries where that helps us to understand the choices available.

Here are the key policy issues in making the transition: 

&lt;strong&gt;&lt;em&gt;- subsidizing poor and elderly consumers so that the analog broadcasts can be turned off (thus freeing spectrum for other uses); &lt;/em&gt;&lt;/strong&gt;

&lt;em&gt;&lt;strong&gt;- working out the relationships between over-the-air broadcasters on one hand and cable and satellite service providers on the other via &quot;must carry&quot; rules in a fair and equitable manner; &lt;/strong&gt;&lt;/em&gt;

&lt;em&gt;&lt;strong&gt;- allowing consumers to purchase add-on services without being forced to purchase unnecessary equipment from service providers (&quot;plug and play&quot;);&lt;/strong&gt;&lt;/em&gt;

&lt;strong&gt;&lt;em&gt;- protecting the intellectual property rights of content producers without violating the rights of consumers to engage in &quot;fair use&quot; of content; and&lt;/em&gt;&lt;/strong&gt;

&lt;strong&gt;&lt;em&gt;- maintaining the important role of local broadcasters &lt;/em&gt;&lt;/strong&gt;in providing local political information to citizens. 

&lt;strong&gt;Short Description&lt;/strong&gt;
" />
	<title>HDTV Magazine Articles - The Politics of the Transition to DTV - Jeff Hart</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/the_politics_of_the_transition_to_dtv_-_jeff_hart';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('The Politics of the Transition to DTV - Jeff Hart'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/articles/2005/06/the_politics_of_the_transition_to_dtv_-_jeff_hart.php";
		if ($author[img] != '' && 1 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Dale Cripps" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">The Politics of the Transition to DTV - Jeff Hart</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Dale Cripps</b><br />
				<?=$author_title?>
				Posted on <b>June 20, 2005</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Politics & Policy">Politics & Policy</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2005/06/the_politics_of_the_transition_to_dtv_-_jeff_hart.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/articles/2005/06/the_politics_of_the_transition_to_dtv_-_jeff_hart.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/articles/2005/06/the_politics_of_the_transition_to_dtv_-_jeff_hart.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2005/06/the_politics_of_the_transition_to_dtv_-_jeff_hart.php&amp;phase=2&amp;title=The%20Politics%20of%20the%20Transition%20to%20DTV%20-%20Jeff%20Hart&amp;bodytext=The%20transition%20to%20digital%20television%20%28DTV%29%20is%20occurring%20in%20all%20the%20major%20industrialized%20countries%20and%20in%20a%20selected%20number%20of%20developing%20nations.%20I%20will%20focus%20today%20on%20the%20transition%20in%20the%20United%20States%20as%20well%20as%20discuss%20the%20experience%20of%20other%20countries%20where%20that%20helps%20us%20to%20understand%20the%20choices%20available.%0A%0AHere%20are%20the%20key%20policy%20issues%20in%20making%20the%20transition%3A%20%0A%0A%3Cstrong%3E%3Cem%3E-%20subsidizing%20poor%20and%20elderly%20consumers%20so%20that%20the%20analog%20broadcasts%20can%20be%20turned%20off%20%28thus%20freeing%20spectrum%20for%20other%20uses%29%3B%20%3C%2Fem%3E%3C%2Fstrong%3E%0A%0A%3Cem%3E%3Cstrong%3E-%20working%20out%20the%20relationships%20between%20over-the-air%20broadcasters%20on%20one%20hand%20and%20cable%20and%20satellite%20service%20providers%20on%20the%20other%20via%20%22must%20carry%22%20rules%20in%20a%20fair%20and%20equitable%20manner%3B%20%3C%2Fstrong%3E%3C%2Fem%3E%0A%0A%3Cem%3E%3Cstrong%3E-%20allowing%20consumers%20to%20purchase%20add-on%20services%20without%20being%20forced%20to%20purchase%20unnecessary%20equipment%20from%20service%20providers%20%28%22plug%20and%20play%22%29%3B%3C%2Fstrong%3E%3C%2Fem%3E%0A%0A%3Cstrong%3E%3Cem%3E-%20protecting%20the%20intellectual%20property%20rights%20of%20content%20producers%20without%20violating%20the%20rights%20of%20consumers%20to%20engage%20in%20%22fair%20use%22%20of%20content%3B%20and%3C%2Fem%3E%3C%2Fstrong%3E%0A%0A%3Cstrong%3E%3Cem%3E-%20maintaining%20the%20important%20role%20of%20local%20broadcasters%20%3C%2Fem%3E%3C%2Fstrong%3Ein%20providing%20local%20political%20information%20to%20citizens.%20%0A%0A%3Cstrong%3EShort%20Description%3C%2Fstrong%3E%0A&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p>Few have been as academically prepared to understand the political dynamite encased in the HDTV movement as Dr. Jeffery Hart. I am proud to count this distinguished educator/author among my friends for the last two decades. I was especially delighted to receive his permission to publish a paper he prepared for the Technology Conference, University of California, Berkeley, California which was held earlier this year.  </p>

<p>For those of you who are just joining this "party" you may find all of the machinations about HDTV beyond comprehension. Jeff uses straight forward language to help you become a more educated electorate. There are things going on that you should know about and have a say in, but without understanding the story that makes up this move to a new communications era you quickly discover that you are far from being qualified to cast your vote, much less spend your money. All of the things I bring to you here in these columns (please check out the History link as well) are designed to give you a background so you too may feel a part of this historic move to what some insists is going to be a better world. His talk is aptly entitled:</p>

<p><strong>"The Politics of the Transition to Digital Television"</strong></p>

<p>The arguments to which he gives lucid form frame the debates raging today as the political will of the nation struggles to craft and pass legislation (Senators John McCain R-AZ, Joseph Liberman D-CT -- "Save Lives Act of 2005"). The goal of this legislation is to complete the DTV terrestrial transition as quickly as possible and reassign the digital spectrum for new uses, one being homeland security communications. The concluding sentence of Jeff's work is particularly important for all to consider as a requirement for completing this transition._Dale Cripps</p>

<p><br />
by<br />
Jeffrey A. Hart<br />
  </p>

<p> <br />
 </p>

<p><strong>The Politics of the Transition to Digital Television</strong> </p>

<p>The transition to digital television (DTV) is occurring in all the major industrialized countries and in a selected number of developing countries. I will focus on the transition in the United States as well as discuss the experience of other countries where that helps us to understand the choices available.</p>

<p>I will address first the key policy issues in making the transition: </p>

<p><strong><em>-subsidizing poor and elderly consumers so that the analog broadcasts can be turned off (thus freeing spectrum for other uses); </em></strong></p>

<p><em><strong>-working out the relationships between over-the-air broadcasters on one hand and cable and satellite service providers on the other via "must carry" rules in a fair and equitable manner; </strong></em></p>

<p><em><strong>-allowing consumers to purchase add-on services without being forced to purchase unnecessary equipment from service providers ("plug and play");</strong></em></p>

<p><strong><em>-protecting the intellectual property rights of content producers without violating the rights of consumers to engage in "fair use" of content; and</em></strong></p>

<p><strong><em>-maintaining the important role of local broadcasters </em></strong>in providing local political information to citizens. </p>

<p><strong>Short Description</strong></p>

<p>  <br />
<strong>Introduction</strong><br />
The transition to digital television (DTV) is occurring in all the major industrialized countries and in a selected number of developing countries. I want to focus today on the transition in the United States, but I will also discuss the experience of other countries where that helps us to understand the choices available. The U.S. case is important not just because of the size of the U.S. economy but also because of the leadership of U.S. firms in global markets.  The distinctive features of business-government relations in the United States have been a key determinant of U.S. policy choices for DTV. The dominance of broadcasting and other forms of TV signal delivery by privately owned firms is probably the most important difference, but there are others. The tradition of regulation by a quasi-autonomous government agency, the Federal Communication Commission (FCC), distinguishes the U.S. transition from those in Europe and Asia.   </p>

<p>One key result is that the interests of U.S. electronics firms and consumers were taken into account earlier than in other regions. The United States was the first to opt for all-digital as opposed to hybrid digital-analog standards. The U.S. government, unlike those in Europe and Japan, did not support standards put forward by a coalition of consumer electronics manufacturers and broadcasters.  U.S. regulatory institutions were sensitive to a number of issues that were until ignored elsewhere, such as the cost to consumers of purchasing new equipment and the need to promote continued innovation in digital technologies. On the negative side, however, the final U.S. government decisions on DTV standards produced considerable confusion on the part of manufacturers, broadcasters, and consumers, a confusion that can be observed directly by anyone attempting to buy new DTV equipment and services at their local electronics outlets.  Coping with that confusion and dealing with the inability or reluctance of some customers to pay for new DTV equipment has become the key challenge of completing the transition to digital television in the United States.</p>

<p><strong>Coping with Confusion</strong><br />
The key DTV decisions by the FCC in the 1990s guaranteed that there would be confusion in the marketplace of DTV equipment and services.  No specific format for encoding or delivering DTV signals over the air was mandated.  Broadcasters and manufacturers were left to figure out what types of signals customers would be willing to pay for at premium DTV prices. So, for example, some over-the-air broadcasters decided not to use their DTV channels to broadcast in high definition. Instead they experimented with multicasting: i.e. the use of a single TV channel to broadcast a number of standard definition TV signals. This means that the broadcaster was using the allocated spectrum to become a sort of mini-cable operator. The bet was that the customer would be willing to pay for more choice in programming (but not for higher picture quality).</p>

<p>Other over-the-air broadcasters were betting that customers would be willing to pay for better picture quality, but they disagreed on what quality increment was required. The standards debates leading up the FCC decisions of the 1990s identified a range of choices for picture and signal formats. The ones that emerged with substantial corporate backing were 480p, 720p, 1080i and 1080p. The number in the number/letter combination stands for the number of scanning lines per image. The small letter p stands for progressive scanning; the small letter i stands for interlaced scanning. Interlaced scanning involves the sending of every other line in an image in one burst followed by the sending of the rest of the lines in the next burst and so on.  Interlacing was invented in the early days of monochrome TV broadcasting to conserve spectrum. All standard definition televisions use interlacing. Progressive scanning involves the sending of all the lines in an image in one burst (not two). All computer monitors, unlike standard definition TVs, use progressive scanning. While progressive scanning is less conserving of spectrum, it has the advantage of eliminating certain visual artefacts in the final image like "flicker."  Progressive scanning is better for the display of text information than interlacing.</p>

<p>480p provides a progressively scanned digital version of a standard definition TV image. It is the cheapest to provide but does not provide as large an increment in picture quality as the other alternatives. 480p is the format of choice for broadcasters who chose the multicasting option.</p>

<p>720p provides a higher quality image than 480p and possibly as high image quality as 1080i because it is progressive. ABC, NBC, and their affiliates opted for 720p and made major investments in production facilities for broadcasting in this format. They focused initially on converting broadcasts of sporting events to 720p.</p>

<p>1080i was the choice of CBS and its affiliates because of their strong belief that 720p did not provide a high enough quality increment over standard definition analog TV to make consumers willing to pay the premium for DTV signals.  Their preference for interlacing was partly the result of the relationship between CBS and Sony, in which the latter provided 1080i equipment to the former.  CBS had allies also in the film industry, including Sony Pictures (formerly Columbia Pictures), who swore by 1080i as a better format in which to view movies.</p>

<p>1080p had the least support of the main alternative formats because it was the most expensive to produce and display. Some of the technological components necessary to produce content in that format were still not widely available in 2005. Nevertheless, all the chips that were in ATSC-compatible HDTV tuners (I will call them DTV tuners for short) were capable of decoding 1080p images and so some companies were betting that the higher picture quality of 1080p would eventually triumph over the other alternatives.</p>

<p>To deal with the diversity of signal formats the FCC mandated in 2002 the progressive phasing in of TV sets with DTV tuners, requiring that new sets with a given screen size or larger contain tuners.  Here are the specific phase-in requirements: </p>

<p>Receivers with screen sizes 36 inches and above -- 50% of a responsible party's units must include DTV tuners effective July 1, 2004; 100% of such units must include DTV tuners effective July 1, 2005. Receivers with screen sizes 25 to 35 inches -- 50% of a responsible party's units must include DTV tuners effective July 1, 2005; 100% of such units must include DTV tuners effective July 1, 2006. Receivers with screen sizes 13 to 24 inches -- 100% of all such units must include DTV tuners effective July 1, 2007. <br />
By the year 2007, therefore, all new TV sets with 13-inch screens or larger would be required to have DTV tuners. (<a href="http://www.dtg.org.uk/news/news.php?class=countries&subclass=0&id=933">this has sense been accelerated</a>). </p>

<p>In the meantime, consumers would continue to have to cope with complexity in stores where labelling of DTV sets and equipment includes such unfamiliar terms as HDTV-ready, HDTV-capable, HDTV-compatible, and HDTV-upgradeable. The sets themselves came in the following technological varieties: CRT (direct view), CRT-based projection, LCD flat panel, LCD projection, DLP projection, and LCOS projection (I won't bother to explain the acronyms here). On the back and there were the following kinds of "secure" DTV connectors: DVI, HDMI, and Broadcast Flag (more on this later). There were also a variety of connectors for antennas, VCRs, DVDs, DVRs, set-top boxes, and other such devices. Customers would be asked if they wanted to get their signal over the air, or via cable or satellite. If customers wanted to connect a DTV to a Windows Media Center personal computer, they would be in another vast new world of acronym-filled complexity. For the fanatics and insanely rich, there was the world of the "home theater" to master. The rich would simply pay someone who knew enough about all this stuff to do it for them, but then they were left with the problem of figuring out how to make it all work the way it was supposed to.</p>

<p><strong>Turning Off Analog</strong><br />
The FCC DTV decisions of the 1990s resulted in the loaning of a second channel to over-the-air broadcasters to use for converting to digital broadcasting while continuing to provide analog services.  The FCC's idea was that once the digital transition was complete the analog channels would be returned to the government to dispose of as needed. The return of spectrum would permit the FCC to auction it off to the highest bidder, so the government had a strong incentive to get back all those old analog TV channels as soon as possible. The revenues from auctions were already being included in estimates of future government revenues during the Clinton Administration, so key members of the government were eager to push for the rapid completion of the digital transition.  </p>

<p>The problem was that the FCC and Congress had recognized that the analog signals should not be shut off until a good percentage of consumers were receiving or at least were able to receive digital  broadcasts. In 1997 when the DTV transition plan was launched, Congress passed a "sense of Congress" resolution as part of an intelligence reform act that stipulated that the spectrum would be returned on December 31, 2006, but only if 85 percent of the residents of any given local community had the necessary equipment to display digital signals.The interpretation of this somewhat vague rule would be left to the FCC.</p>

<p>Less than three percent of American homes had sets capable of decoding DTV signals as of early 2005 although a much larger percentage, perhaps more than 80 percent, received TV signals in digital formats from either cable or satellite services and the 2006 deadline was fast approaching. The sales of such sets were growing rapidly, especially as lower cost DTVs started to be featured in the major consumer stores. The number of cable and satellite services offering HDTV-quality signals was also growing rapidly. In 2006, the prices of flat panel plasma TVs were expected to continue to descend below the current average price of around $2,000, especially as the larger LCD TVs also were expected to decline in price from the current $2,000 average to around $1,000. Nevertheless, it was unlikely that 85 percent or more of the households in more than a handful of communities would own TVs with DTV tuners by the end of 2006</p>

<p>An additional problem, highlighted by outgoing FCC Commissioner Michael Powell was that many households possessed more than one TV, but were not likely to be receiving digital signals on every set they own.  Also, a number of over-the-air broadcasters failed to comply with FCC orders to begin broadcasting in DTV formats, so households with DTV sets in those localities but without cable or satellite services would obviously not be able to contribute to meeting the 85 percent goal.</p>

<p>As a result, the FCC, in its desire to get the spectrum back sooner rather than later, proposed a new deadline of 2009 and an easier test of the ability of households to decode DTV signals: i.e., that the use of cable or satellite services where the service provides a digital signal either to a set-top box, or, even less ambitiously, to a nearby connection point, would count toward the 85 percent goal.  If the household opted not to purchase a DTV set, therefore, it might still enjoy TV broadcasts if it either purchased or was given a box to convert the DTV signal to a standard definition analog signal. All cable subscribers qualified as DTV-ready households by that standard. Problem of rapid transition solved!</p>

<p>That proposal, engineered by Kenneth Ferree, the chief of the FCC's Media Bureau, in January 2005, but had not been approved as of February 2005. Ferree left the FCC soon after making the proposal. The plan was strongly opposed by the National Association of Broadcasters, whose members were not in a hurry to return their analog channels to the federal government. They claimed that to meet the 85 percent goal, 73 million sets not connected to a cable or satellite service would have to be fitted with a converter at an estimated cost of around $300 per unit, at a total estimated cost of $22 billion. It should not come as a surprise that the $300 price tag given by the NAB was contested. Motorola Corporation, for example estimated the boxes could be produced in high volume for between $50 and $75 per unit. Motorola and other electronics manufacturers like Intel were interested in seeing the analog spectrum returned and auctioned off for new wireless uses. </p>

<p>The important underlying issue, however, was that the shutting off of the analog signals would greatly inconvenience millions of TV watchers who either could not afford or were not willing to purchase the necessary converters and therefore raised the question of whether there needed to be government subsidies to allow these individuals to continue using their analog equipment.   </p>

<p><strong>Must Carry</strong><br />
Another difficult question was how to set the rules for the relationships between over-the-air broadcasters and cable and satellite service providers during and after the transition. Cable operators were bound by "must carry" rules that impelled them to give their customers access to the analog signals of local over-the-air broadcasters via the cable service. The cable operators did not get paid for this service, even though the local broadcasters continued to receive advertising revenues based on the audience (cable plus non-cable) that their signal could reach. This really irritated the cable operators so they looked for ways to get compensated for carrying the signals of local broadcasters on increasingly scarce cable bandwidth. No such must carry rules governed the relationship between local over-the-air broadcasters and satellite service providers.</p>

<p><strong>Cable operators</strong> - led by Ted Turner initially - challenged the "must carry" rules on Constitutional grounds as a violation of their right to free speech, but ultimately lost this battle in the Supreme Court. They insisted that they could not be forced to carry digital signals the way they had been forced to carry analog ones, especially multicasts because this violated the intention of policy makers to promote a higher quality of broadcasts not simply a proliferation of channels. They wanted over-the-air broadcasters and cable network programmers to compete on an equal basis for cable bandwidth and obviously to pay for carriage and they wanted local cable operators to have full control over the programming packages offered to cable customers in their service area. Cable companies particularly objected to efforts of broadcasters to get compensation for providing DTV signals for carriage by cable operators (especially HDTV coverage of popular sporting  events).  A spokesman for Time Warner Cable, Keith Cocozza said "The issue at heart is that broadcasters are trying to insist that they are compensated for something that they get from the government for free." </p>

<p>What the local broadcasters wanted was for both cable and satellite to be bound by "must carry" rules for digital signals, especially those who had already investing in multicast technology (e.g. Belo. They also wanted the cable operators to pay them for carrying their content on cable networks.  The DTV decisions of the 1990s gave the local broadcasters the right to use their digital channel either for HDTV or for other purposes including the broadcasting of multiple standard definition signals (multicasting). Some broadcasting networks opted for multicasting, thus defining the choice for their local affiliates. The problem was that the cable companies did not want to carry the multicasts which they saw as direct competition and wanted to be compensated for carrying whatever they decided to carry. In short, disagreements over these matters were blocking cable carriage not just about multicasts but also of local- and network-produced HDTV-quality digital signals. Consumers who purchased DTVs to view this content were disappointed.</p>

<p><strong>Plug and Play</strong><br />
Related closely to the must carry controversy was the question of what sorts of equipment consumers had to purchase or rent from cable operators in order to display DTV signals on their televisions.  The decision of the FCC to mandate the inclusion of DTV tuners in new televisions meant that after 2007 it would not be necessary to include DTV tuners in the set-top boxes sold or rented to cable subscribers.  Nevertheless, the cable operators continued to insist that they had the right to sell or rent set-top boxes because of the interactive (two-way) services they wanted to provide -- such as pay per view, virtual digital video recorders, or electronic program guides -- that went beyond the one-way service of decoding DTV signals.  </p>

<p>In the interest of saving consumers unnecessary expense and clutter, the FCC ordered in October 2003 that televisions that were "Digital Cable Ready" should be labelled as such and that the two stakeholders (set manufacturers and cable operators) should work together to ensure that televisions so labelled would be compatible with cable services and equipment. The Consumer Electronics Association (CEA) and the National Cable Television Association (NCTA) had issued a Memorandum of Understanding in December 2002 calling for a "plug and play" format for one-way signals from cable to DTV sets. Thus, to some extent, the later FCC order was an endorsement of the earlier CEA/NCTA agreement and a plea for further negotiations. The two industries were urged to go beyond the one-way plug and play agreement to negotiate a similar one for two-way interactive services. Such an agreement was still under negotiation in February 2005.</p>

<p>One of the near-term consequences of the Digital Cable Ready Order of 2003 was the development of the CableCard system. CableCard is a card-shaped object that plugs into a socket in a Digital Cable Ready TV that gives the consumer access to the cable services of a specific cable provider.  The primary function of the CableCard is to assure that only paying customers get access, but a secondary and quite valuable function is to do this in a way that does not require the purchase or rental of a set-top box with a redundant DTV tuner.</p>

<p>The CableCard system was similar to one developed for the DVB standard in Western Europe. From the consumer standpoint, not having to have multiple set-top boxes when subscribing to more than one service or to buy or rent a new box when changing services made a lot of sense. This decision, in short, assured that there would be lower switching costs for consumers and lower barriers to entry for potential competitors in local DTV cable service markets.</p>

<p>The cable operators resisted the CableCard initially because they thought it would reduce their ability to realize the revenues associated with proprietary features they planned to build into their next-generation set-top boxes. The set manufacturers were worried that the increased cost of including a DTV tuner in sets would have to be passed along to consumers in the form of higher prices and that higher prices would deter DTV sales.  Another disadvantage mentioned by critics of the CableCard decision was that equipment purchased before the decision, like digital video recorders, might not work with Digital Cable Ready televisions.  These sorts of timing and incompatibility issues came up also in the area of intellectual property protection devices (see below). The FCC held firm on both the Digital Cable Ready and Plug and Play decisions, however, and both set manufacturers and cable operators began to plan their next moves accordingly.</p>

<p><strong>Intellectual Property Protections vs. Fair Use</strong><br />
The preceding sections dealt with a number of regulatory decisions that were motivated at least partly by concerns about how to ensure continued technological innovation in the wake of the DTV decisions of the 1990s. This was not an idle concern. One of the unfortunate potential impacts of major standards decisions was to freeze technological development, even when that may not be in the best interests of society. The FCC frequently justified its standards decisions in terms of the need to guarantee that there would continue to be competitive markets.  In their view, competition was the best way to ensure continued innovation in technologies. Nevertheless, the agency also recognized that technical standards sometimes were needed to reduce confusion among consumers and producers and that there needed to be regulatory intervention occasionally to reduce the tendency of different stakeholders to squabble among themselves, thus holding back the development of the market. The FCC led by Michael Powell was particularly focused on stopping this sort of infighting.</p>

<p>Unfortunately, decisions made on other issues might eventually reduce the scope for technological innovation precisely because they are designed to protect intellectual property rights of a certain set of rights owners, in this case the film, TV content, and recorded music industries, at the expense of consumer rights to fair use.</p>

<p>Intellectual property rights are granted to ensure that creative people will be adequately compensated for their creativity and so that the fruits of their creativity will be enjoyed by all. The main method used to accomplish this end is to grant a temporary monopoly of usage rights for a growing list of products and services that embody individual creativity: books, movies, recorded music, chemical formulas of new pharmaceuticals, etc.  The legal system of intellectual property rights permits the rights holders to obtain compensation not just for the direct sale of the resulting products and services but also for licensing others to commercialize those products and services.  </p>

<p>There are separate intellectual property rights (IPR) regimes intended to protect different types of creative activity.  The patent regime protects both innovative products and manufacturing processes. The copyright regime protects literary creativity and other forms of recorded performance and/or storytelling.  The semiconductor mask protection act protects integrated circuit designs that are embodied in the masks used to duplicate those designs on silicon. These regimes have been extended gradually and incrementally to cover creative activity not originally envisioned by legislators. The extensive use of patents and copyrights by the managers of high technology companies and the liberal granting of intellectual property rights to those firms has created a bit of a backlash and occasionally bitter fights.</p>

<p>One outcome of this contestation is the judicial delimitation of intellectual property rights in key decisions that are often lumped under the rubric of "fair use."  Section 107 of the Copyright Act of 1976 reads as follows: "... the fair use of a copyrighted work, including such use by reproduction in copies or phonorecords or by any other means specified by that section, for purposes such as criticism, comment, news reporting, teaching (including multiple copies for classroom use), scholarship, or research, is not an infringement of copyright."   This portion of the act has been used in a variety of court decisions not just to protect academics and journalists but also, increasingly, artists, disk jockeys, and others using "sampled" information to create something new.  </p>

<p>With the increasing digitalization of media content that has accompanied digitalization of the telephone networks, the rise of the Internet, the World Wide Web and now the transition to digital television, the ease of copying digitized texts, images, audio and visual materials has generated a whole new generation of digital piracy - that is, the theft of content protected by IPRs by illegal copying and sale of that content. This was possible prior to digitalization of course but digitalization has made the process faster, cheaper, and easier.  As the speed of computers and telecommunications networks continues to increase, so does the size of the problem of illegal copying and sales of protected content.</p>

<p>In recent years, Congressional attempts to tighten IPRs in the new digital environment took the form of the Digital Millennium Copyright Act (DMCA) of 1998 and the Inducing Infringement of Copyrights Act of 2004. The Broadcast Flag decision of the FCC in 2004 was consistent with the spirit of the Congressional acts. In the DMCA, the Congress weighed in heavily on behalf of IP rights holders by stressing the responsibility of businesses that provide access to telecommunications networks to guard against illegal activities including illegal copying and file sharing. The act did not adequately address fair use in this new context, however, nor did it pay adequate attention to its potential impact on legitimate research activities. The Supreme Court had ruled in the "Betamax" decision, Sony Corp. v. Universal City Studios, that the sale of video recorders could not be banned because some consumers misused the machine to make illegal recordings. In addition, the Betamax case established the right of consumers to make copies of copyrighted materials for their personal use within the household as long as they did not attempt to sell the copies. The Betamax ruling was consistent with the more general principle was that a manufacturer or service provider should not be made responsible for the illegal use of their products and services by final consumers. Such a transfer of responsibility would make the manufacturer or service provider in effect an arm of the police. </p>

<p>The Inducing Infringements of Copyrights Act of 2004 is aimed at identifying and punishing one who "intentionally aids, abets, induces, counsels, or procures ...  to induce infringement" of copyright laws, but in fact the main targets of this particular legislation are the peer-to-peer or file-sharing networks established via networking software like the original Napster and its descendants: Kazaa, Grokster, and Morpheus. The bill was sponsored chiefly by Senator Orrin Hatch (R-Utah) in reaction to a 2003 court ruling that the use of file-sharing software was legal. Its main business supporters were the Recording Industry Association of America and the Motion Picture Association of America. Its main opponents included Congressman Rick Boucher (D-Va.), the Consumers Union, the American Library Association, and the Electronic Frontier Foundation.</p>

<p>On November 4, 2003, the FCC released its so-called "Broadcast Flag" decision. The basic idea behind the Broadcast Flag was that all DTV content that was protected by IPR laws would contain a coded digital "flag" that could be detected by any piece of DTV equipment.  Once the flag was detected by the circuitry of the device, it would be impossible to make copies of the content or to pass digital versions of the content to other devices.</p>

<p>The effect of the Broadcast Flag, therefore, would be to prevent consumers from making backup copies of high definition tapes and DVDs or to record high definition movies delivered over the air, via cable, or via satellite. Thus, for consumers, the Broadcast Flag, like the DMCA and the Inducing Infringements of Copyrights Act was a step backward for both home recording rights and fair use.</p>

<p><strong>Localism in Broadcasting</strong><br />
A related matter was maintaining the role of local television news broadcasting in providing political information to voters.  The FCC was bound to consider this question along with its concerns about the efficient use of broadcasting spectrum.  </p>

<p>Contemporary research on voting indicated that a large percentage of voters, more than a majority, received most of their information about local elections from local TV broadcasts. Prior to the nearly universal access to TV broadcasts, however, most citizens obtained that information from the print media.  Given the dependence of voters on TV news, the existence of local TV news broadcasts became an important pillar of American democracy.  </p>

<p>There were questions, of course, about the quality of information obtained in this way, and about the long-term impact on the quality of U.S. democracy that resulted from dependence on television news because of its heavy emphasis on visual images and short "sound bites" rather than the lengthier and more deliberative coverage of previous eras.  There was also some interesting research on whether dependence on TV news coverage made for a more manipulable public and overdependence of candidates on the raising of campaign funds to pay for TV advertising.</p>

<p>Even if there were legitimate concerns about the quality of local political information conveyed via local broadcasting (and other media), clearly if that flow of information was interrupted in the transition to DTV, then there had to be an alternative channel for conveying that information if local politics was to continue to play an important role in the federal system.  </p>

<p>The FCC, under the leadership of Michael Powell, began to address this issue by holding a series of hearings around the country about "localism" in broadcasting. On July 1, 2004, the FCC issued a  "Notice of Inquiry" (NOI) on localism in broadcasting partly as a response to the heavy criticism of an earlier decision reversing decades of enforcing rules designed to prevent concentration of ownership of media outlets in local communities. Following the issuance of the NOI, various "stakeholders" submitted documents to the FCC on this question and testified at hearings stating their views. The process was still ongoing as of February 2005.<br />
Conclusions</p>

<p>The transition to digital television in the United States will be delayed if the issues discussed above are not resolved swiftly and in a fair and equitable manner. If poor or elderly consumers are not subsidized, they will forced to buy their own converters. If they choose not to do so, as is quite likely, they will lose access to an important source of timely information about local politics. Either way, there is a loss to democratic legitimacy. So what may appear at first glance to be technical or budget-driven decision is really a political decision about who gets access and at what cost to the political process. Similarly, overzealous protection of the intellectual property of content producers can undermine the rights of consumers to use televisions and computers for educational or creative/artistic purposes, thus impoverishing our culture. While digital or high definition television may not be all that important in the larger scheme of things, a number of more important issues lie just below the surface.</p>

<p><strong>About the Author</strong><br />
Jeffrey Hart is Professor of Political Science at Indiana University, Bloomington, where he has taught international politics and international political economy since 1981. His first teaching position was at Princeton University from 1973 to 1980. He was a professional staff member of the President's Commission for a National Agenda for the Eighties from 1980 to 1981. Hart worked at the Office of Technology Assessment of the U.S. Congress in 1985-86 and helped to write their report, International Competition in Services (1987). He was visiting scholar at the Berkeley Roundtable on the International Economy, 1987-89. His major publications include The New International Economic Order (1983), Interdependence in the Post Multilateral Era (1985), Rival Capitalists (1992), (edited with Aseem Prakash) Globalization and Governance (1999), Coping with Globalization (2000), and Responding to Globalization (2000), (with Joan Edelmann Spero) The Politics of International Economic Relations 6th edition (2002), Technology, Television and Competition (2004), and scholarly articles in World Politics, International Organization, the British Journal of Political Science, New Political Economy, and the Journal of Conflict Resolution.<br />
</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Dale Cripps</b>, <b>June 20, 2005 03:52 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 103
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
 			<h2>More on Politics & Policy</h2><ul><?
 			$sql = "
 			SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name
 			FROM mt_entry e, mt_author a, mt_placement p, mt_category c
 			WHERE entry_author_id = author_id
 				AND e.entry_id = p.placement_entry_id
 				AND p.placement_category_id = c.category_id
 				AND category_label = 'Politics & Policy'
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
 				AND entry_id <> 103
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2005/06/the_politics_of_the_transition_to_dtv_-_jeff_hart.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
