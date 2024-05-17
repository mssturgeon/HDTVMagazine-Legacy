<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 64";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Dale Cripps'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 64 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category[placement_category_id];
	
	# Still need this to track page views. Used in the Links unit in the footer
	$google_links_channel = $author[channel];

	# Set variables based on entry type. NOTE: Podcasts has its own entry template, so it is not included amongst the choices below.
	switch (4) {
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
	<meta name="keywords" content="great programs, monday night, our affiliates, revenue stream, lot money, ABC, abc, our, great, been, people, shows, television, think, HDTV, hdtv, going, network, news, local, watch, get, pvrs, world, PVRs" />
	<meta name="description" content="So, we know what wireless is. We can play in the wireless world. Wireless is not a threat. We can play in a broadband world. Broadband just needs more distribution. We can play in most of the distribution channels but the potential for disruption of advertising is the single greatest threat we have. 
" />
	<title>HDTV Magazine Interviews - INTERVIEW - Alex Wallau - President ABC Network</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/interview_-_alex_wallau_-_president_abc_network';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('INTERVIEW - Alex Wallau - President ABC Network'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_-_alex_wallau_-_president_abc_network.php";
		if ($author[img] != '' && 4 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Dale Cripps" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">INTERVIEW - Alex Wallau - President ABC Network</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Dale Cripps</b><br />
				<?=$author_title?>
				Posted on <b>June  9, 2005</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category="></a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_-_alex_wallau_-_president_abc_network.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/history/interviews/2005/06/interview_-_alex_wallau_-_president_abc_network.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_-_alex_wallau_-_president_abc_network.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_-_alex_wallau_-_president_abc_network.php&amp;phase=2&amp;title=INTERVIEW%20-%20Alex%20Wallau%20-%20President%20ABC%20Network&amp;bodytext=So%2C%20we%20know%20what%20wireless%20is.%20We%20can%20play%20in%20the%20wireless%20world.%20Wireless%20is%20not%20a%20threat.%20We%20can%20play%20in%20a%20broadband%20world.%20Broadband%20just%20needs%20more%20distribution.%20We%20can%20play%20in%20most%20of%20the%20distribution%20channels%20but%20the%20potential%20for%20disruption%20of%20advertising%20is%20the%20single%20greatest%20threat%20we%20have.%20%0A&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p><em>This interview was done in the fall of 2004.</em><br />
Interviewer was Dale Cripps</p>

<p><em>As president, ABC Network Operations and Administration, Alex Wallau has direct oversight of ABC News, Network Sales, Affiliate Relations, Broadcast Operations and Engineering, Research, as well as the integration of ABC Sports with the ABC Television Network. He reports directly to Anne Sweeney, co-chairman, Disney Media Networks Unit and president, Disney-ABC Television.</p>

<p>Mr. Wallau began his career with ABC in 1976, when he joined the network's Sports division as head of On-Air Promotion, working with the legendary Roone Arledge, then head of ABC Sports. Mr. Wallau went on to become a two-time Emmy Award-winning producer and director of ABC's sports coverage. In 1986 he moved in front of the cameras as ABC's boxing analyst, and was honored by the Boxing Writers of America as the top television boxing journalist in his first year. </p>

<p>Over the years, as network vice president (1993-96), executive vice president (1996-98), president, Network Operations and Administration (1998-2000) and president, ABC Television Network (2000-2004), Mr. Wallau has witnessed many milestones in ABC's broadcast history. He has contributed to a number of these through his own work, across virtually all divisions of the network, from Primetime Entertainment to News, Sports and Daytime. He has been a strong advocate for ABC's innovative digital ventures - including broadcasting the Primetime schedule in High Definition Television--and for its multiple new on-demand digital platforms. He has also been a leader in ABC's efforts to promote diversity across the entire network</p>

<p>Mr. Wallau serves on the Board of Directors of ESPN and the Advertising Council, and is a member of the Los Angeles Board of Governors of the Museum of Television and Radio.</p>

<p>Born in Fort McPherson, Georgia, Mr. Wallau was raised in the Bronx and Connecticut. He earned a BA degree from Williams College. He and his wife, Martha, live in Los Angeles.</em></p>

<p>I start the interview by asking...</p>

<p><strong>HDTVMagazine: ...what is the most notable changes you have seen in the HDTV movement since we last spoke a year ago?</strong></p>

<p>Wallau: There are two things: There has become more of a sense of inevitability about HDTV as a format. That is true for both consumers and broadcasters. Improvements in the 8-VSB (over-the-air transmission standard) have been a help at the broadcasting level. The amount of programming, including Monday Night Football, has helped the consumer's sense of the inevitable. You can add to this the most important thing--the (new low) price points. Prices moved faster than I expected to a level of acceptability. </p>

<p>I also think Ultra HD is stirring. In the background there is this specter of something else beyond HDTV. That is a challenge from a planning perspective. How much do you spend in your physical plant today (with that looming ahead)? </p>

<p><strong>How real do you think UltraHD is?</strong></p>

<p>It is absolutely going to happen just as HDTV happened. Whenever a satellite provider does a survey and asks people if they want more programs or higher quality they will always opt for more channels.. Having said that, it is apparent that picture quality has become more important than it has ever been in the American viewers' mind.. It is now part of their experience. These 4000 line formats are being talked about in Japan and being tested. I think they will become part of what we have to deal with. The improvement in picture quality is not going to stop with the ATSC standard. The technology will continue to improve. That is a challenge for us in terms of planning. I do not, of course, think that it is just around the corner.</p>

<p><strong>Does that mean you must have an extensibility in your planning?</strong></p>

<p>We can't do that right now because there is no upgradable hardware. Five years ago we made sure that every camera we bought was upgradable to HD, if it wasn't already HD. That situation doesn't exist for Ulta Hi Def. </p>

<p>Ulta Hi Def is not something that we talk about at our planning meeting. It is something we talk about after the meeting. There is brainstorming about it. We presently are planning for the near term for an increase in the amount of local HD programming. The networks are not maxed out either (with HD programming). We are certainly not doing all 21 hours of prime time in HD. We don't do reality (shows); we don't do news magazines in HD. I think that will come. The upside for the HD viewer is going to be in local TV. I hear much more talk from our affiliates about making HD investments. They are feeling the need to make it, especially in the local news area. I never thought I would hear that this early. </p>

<p><strong>Has the introduction of prosumer HD cameras, such as those from Sony and JVC, influenced some of that thinking?</strong></p>

<p>Yes. But it has not been the major driver. Broadcasters still want full broadcast quality capability. They know they can get good pictures from prosumer equipment but that is more for in the field, like in Iraq. Even the remote truck has more in it than a camcorder. There should be a natural competitiveness about it. You may be a manager who doesn't think he can monetize the investment, but if the competitor does it, he feels compelled to do it. </p>

<p><strong>Is there also a competitiveness outside active of broadcasting?</strong></p>

<p>We did it because we thought it was our responsibility as return for the granting of spectrum. We also want to give our viewers the best experience possible. Frankly, we were first in the chicken and egg  equation. We went before there was an installed base to watch it. We spent tens of millions of dollars during that time. But we had been given the spectrum to do it. It was our obligation to do it. We did it. We will continue to do it.</p>

<p><strong>There has been pressure on the Disney Company from Wall Street. Some of that has been directed at ABC. The cost associated with HDTV must have impacted the bottom line. What has Wall Street's attitude been towards ABC for doing HDTV? </strong></p>

<p>It's under the radar screen for Wall Street. First of all we were able to offset some cost with corporate sponsorships. It did not all effect the bottom line negatively. We have a great and helpful partnership with Zenith-LG. We are very grateful to them for their participation. Considering the billions of dollars in costs in broadcasting, the millions of dollars we spent on HD was not so significant. </p>

<p><strong>The last time we spoke we talked about satellite carriage. You said the affiliates were very uncomfortable with it. Does that remain true?</strong></p>

<p>The affiliates are very uncomfortable about us providing our High Def signals to satellite. That still is very true. I am aware of what CBS and NBC have done...and we may end up there. But there is significant resistance at the affiliate level. They see it is a way for people to get ABC Network television shows by some means other than the affiliate in the marketplace. They want to be the provider. There is a white area argument that says if their signals can't be received then the viewer should be able to get it from some other provider. The problem has been that the satellite companies have not held strictly to white areas and so put the onus on the station in the market to verify if the person can receive the signal. That is a costly process. It also is not a good public relations move to go through a procedure which disallows people television. </p>

<p><strong>Murdoch and DirecTV made a rather stunning announcement not long ago saying that they are going to have upwards of 1000 local HD Channels starting with 500 as soon as next year. How does that contrast with the kind of thing you just mentioned?</strong></p>

<p>Local into local is O.K. That retains the advertisers. That is just taking their signal and putting it on the satellite and playing it back into their own market. They have no problem with that. They have a problem with distant signals, such as WNBC in New York being nationally distributed. They get harmed because it is not their commercials being seen. It is not their local programming (being seen). It hurts their own viewer ship. They have an understandable concern.</p>

<p><strong>What is the relationship now with affiliates and cable? Are they finding a home for HD carriage?</strong></p>

<p>It varies. But it is still a battle. Cable and satellite operators clearly see HD as something of value. Whether individual station operators are able to extract value from MSOs is a part of the negotiations under way. Those negotiations are always difficult. </p>

<p><strong>What is the basis of cable's resistance?</strong></p>

<p>Far be it from me to take cable's side, but let me express their viewpoint. They feel that it is a free over-the-air broadcast, whether analog or digital, and so they have a right to take it down and turn it around for their own customers. Retransmission consent means that they don't have this right. The question is; what value do you get for giving retransmission consent? To the cable operator the ability to extract cash runs into an attitude of religious fervor by the MSO, who feels the pain for paying for free over-the-air broadcast. It's the end of the world for them. </p>

<p><strong>What is the future for broadcasting in a world full of PVRs (TIVO-like devices)?</strong></p>

<p>If I had a good answer to that I would be making a lot more money. The answer is that it is an unknown. It is a very threatening technology. Unlike cable channels, we are totally ad supported. PVRs threaten that revenue stream in a significant way. That  technology is a bad thing for us. There are other technologies--interactivity among them--which allow us to potentially grow our revenue stream. With interactivity we can eventually give advertisers more access to customers—better access—better connections. </p>

<p>There is also an opportunity in that PVR owners watch more television. That is a good thing for us. Our shows are still the dominant shows that people want to watch. The good thing (for us) is that they watch more television. The bad thing is that a certain number of them won't watch commercials. </p>

<p>We would be idiots if we didn't recognize the threat that PVRs present to our advertiser revenue base. <br />
 <br />
<strong>Do you know what that percentage is?</strong></p>

<p>Nobody knows. The early adopters are always more technically proficient and more interactive than the later adopters, when PVRs reach a significant installed base level. Any data from people who have them right now is like testing a 14 yr old computer user vs. an adult computer users. It is an entirely different sample of people.  </p>

<p>We would be idiots if we didn’t recognize the threat that PVRs present to our advertiser revenue base. </p>

<p><strong>Perhaps a PVR that carries an ABC label and is given to consumers could be made to not skip commercials. </strong></p>

<p>As I said to you before, Dale, the one thing I am sure about is that the America viewer wants great programs. Great programming requires a lot of money to produce. Up to know advertising dollars and subsidies have annually underwritten tens of billions of dollars of  programming costs. If that money goes away because of PVRs I don't think the American public is going to watch 500 channels of the equivalent of "Garage Band". They want the great actors and actresses and the great scripts--great shows with great story telling--great news gathering and, if they don’t have the advertising dollars to subsidize, its going to be subsidized in another way. That is either going to be from transaction fees or some other form of revenue generation to underwrite the cast of production. I do believe there is a world in which people would choose to watch commercials rather than to pay 99 cents for each show they watch. </p>

<p><strong>You find this evidence on the Internet. Pay sights may be ignored while more distracting advertiser supported sites are used.</strong></p>

<p>I think that may be replicated in the PVR world. You may get to a world where rather than pay out money at the end of the month for shows they have grown use to watching for free they choose advertiser supported insttead.</p>

<p>The one thing I am sure about is that the America viewer wants great programs. </p>

<p><strong>How are the DVD sales of television programs?</strong></p>

<p>A number of serial dramas have done very well. People will sit down for an involving experience with linear story telling that feels like a long mini-series. Those sales have exceeded ten million units and have become a significant source of revenue for those few programs. Some shows equally as popular but not as involving don't do so well. It has not been the majority by any means, but a significant part of the revenue stream.</p>

<p><strong>One thing PVRs don't bring much value to is in live sports. Few like to see the game a day after the event. How is Monday Night Foot ball doing for you?</strong></p>

<p>We are up year-to-year so far. We think the games look terrific in HD. I am proud that ABC is involved in what most believe is the single greatest driver for the adoption of  high-definition. </p>

<p><strong>I talked to Bryan Burns (ESPN) the other day and will publish his interview shortly. They made the announcement for a second ESPN in HDTV. Being a board member of ESPN were you involved with that discussion and decision?</strong></p>

<p>ESPN made the decision. That is not something for which they need our support. We have had a great dialog with ESPN regarding their roll out. Their adoption of 720p, along with Fox’s decision for that format, has been a great move for what we believe is a great High-def format.  </p>

<p><strong>Were you glad to see Fox come in?</strong></p>

<p>Yes, of course, The biggest single benefit for us was when the Department of Defense made the judgment that 720p was the superior format for their purposes For a number of reasons we thought that was the better format. Interlace artifacts in the digital world didn't make sense. When the DoD agreed it was very comforting since we were at that time out on a limb by ourselves. Since then the European Broadcasting Union has decided on 720p/50, Fox has. ESPN has. So, there are a lot of good companies that have made this choice.</p>

<p>We don't care if the manufacturers make 1080i or 720p. What we prefer is they do as did Panasonic with their D5 professional tape format--make it switchable between the two. We want our switchers that way too. We want our cameras that way. That is the way the world is headed. It is great that Fox came in. They are very savvy from a technological standpoint. The decision was very smart.</p>

<p><strong>Do you find additional use for the bandwidth that you don't need for HDTV when using 720p?</strong></p>

<p>Absolutely. That is the reason we chose it. There will be some benefits down the line for our affiliates because they will have more room in their digital spectrum to do other things. One of the funny things you hear in reviews about the PVRs is that people find that the capacity is 13.4 hours (for 1080i) but if you record ABC shows it's 16 hours. They are confused as to why there is a difference. </p>

<p><strong>What are the "other things" that you see being done with that spectrum?</strong></p>

<p>The primary one is that ABC's News Now which we started during the Democratic Convention. That  will go through to November 2nd. It has been a very successful experiment. It answers the question of how to use the digital spectrum in a way that viewers find compelling. It has had terrific reviews. There has not been huge distribution. We are in the middle of some research now. The final presentation of that research will be done tomorrow. But the viewers reaction to the service has been very, very positive. The combination of local and network content and the air of informality about is wholly different than what we have been able to with our traditional services. We look forward with our affiliates to making it permanent.</p>

<p><strong>Is that a 16:9 format or 4:3?</strong></p>

<p>It is a SDTV 4;3 service. It is distributed digitally with most of the news gathering done in the traditional analog format.</p>

<p><strong>What is the health of broadcasting today?</strong></p>

<p>It is a struggle. When you combine us with our station group we are profitable. It is a business that is extremely powerful but there are both challenges and opportunities in the future. If we can meet the challenges and take advantage of the opportunities then broadcasting will be a good business going forward. If we are not smart there is a chance that the challenges will continue to hurt the business. </p>

<p><strong>Which challenge just shakes you to the core?</strong></p>

<p>PVRs. In the mid-term that is the single biggest challenge. Peter Putman did an article the other day describing a wireless PVR, or media receiver. Someone raved about it at the CEDIA show and Peter said, "Well, wireless--that is real broadcasting isn't it?" It's been around for 60 years. So, we know what wireless is. We can play in the wireless world. Wireless is not a threat. We can play in a broadband world. Broadband just needs more distribution. We can play in most of the distribution channels but the potential for disruption of advertising is the single greatest threat we have. </p>

<p><strong>Do you see using the Internet for distribution in ten or more years is as being meaningful thing? </strong></p>

<p>It is but one of the things that makes broadcasting compelling is local content. Viewers want to see their local news shows, especially. So, it would have to be some kind of  broadband distribution that was in conjunction with our affiliate body to make it as strong as it is right now. One of the reasons we have such access to people's homes is because we are in combination with local affiliates and their programming. It would be important to keep that going as long as the affiliate relationship remains sound.</p>

<p><strong>What is the brightest spot providing you the most hope for overcoming all of these struggles?</strong></p>

<p>We are out in the first week of the season with a couple of shows that people have latched on to right away—"Lost" and "Wife Swap", On Monday night we hope Desperate Housewives does very well.  The excitement of what we do is great programs. That is what we are all about. We are only as important in people's lives as the shows that we produce. So, making great content, whether ABC News, ABC Sports or ABC entertainment, is the single most important focus that we have. </p>

<p><strong>Is the skill of making great programs growing? </strong></p>

<p>I think there are more great programs on then ever before. But there has been such a huge explosion in sources of content that it has not necessarily been met by an explosion of talent to meet the appetite. Today you need to be more aware of the people who can create great content then ever, whether it's David E. Kelley, Steven Bochco, or J.J. Abrams on the drama side, or those who create comedies, reality...or the sports franchises—the NBA, the NFL. We have to make sure that people like Peter Jennings, Ted Koppel, Barbara Walters, Dianne Sawyer, Charlie Gibson and the great people from ABC news have the access and means for doing the best reporting on the news stories of the day as they can. We have to have all of the people supporting them in studio and in the field which leads then to great information put out by ABC. It is a big investment, but in the end of the day networks are differentiated only by their content. You have to be smart about how you spend your money. You can't afford to cost cut your way to greatness. You have to invest your way to greatness with great content. </p>

<p><strong>We live increasingly in a global world. Do you find global themes that could be produced with international distribution in mind today?</strong></p>

<p>There always has been a great deal of reality programming coming out of Europe, specifically the UK. Archie Bunker was based on a British show. We are sending ideas their way. "Alias" is a huge hit in Europe. The stars of "Alias" were vacationing in the south of France and greeted in the streets by their show names. We still export a lot more content overseas than is being brought in but I don’t think the globalization of content has happened yet in the television business, but it may.</p>

<p><strong>Any specials coming up that will be ideally suited to HDTV and thus exciting for the public? </strong></p>

<p>Certainly! Monday Night Football, the Academy Awards in February, and the American Music Awards in January. We are trying to do virtually all of our specials in HDTV. We did not do the Emmys in HD this year.</p>

<p><strong>Why was that?</strong> </p>

<p>It fell through the cracks. It is something we should have done. It is produced outside and nobody asked anyone else if it should be in HD. </p>

<p><strong>Do you find that High-def is influencing the way programs are conceived and produced?</strong></p>

<p>I find that people are trying to raise the bar on all of what they are doing and HD is a part of that process. Creators are excited from any increase in quality and excited about HDTV. </p>

<p><strong>Let's talk about the transition itself. The transition seems to be going along pretty well in terms of consumer sales, though I was alarmed by the last CEA report which stated that DTV sales were up only 10% in August over last August.  Did you notice that figure and did it startle you?</strong></p>

<p>I did notice it. I don’t know what to make of it. It may be some aberration. It is counter intuitive. </p>

<p><strong>There is a theory which says following the relatively easy sales to the early adopters and the sphere they influence another circle exists which is insulated from those spheres and is fully satisfied with the TV as they have always had it and not willing to shell out additional money for something they don’t perceive as a product meant for them. What would any consumer softening do to the enthusiasm for continued investment by the broadcasters?</strong></p>

<p>If the format ultimately gets rejected that would reduce our enthusiasm. But that would also call for an action from Congress. The spectrum was given to us for use for digital distribution and HD. It would require a change in Washington if the format was not being adopted and then we would all have to figure out how are we going to use the spectrum if not for HD.</p>

<p> ABC Programming includes the benefactor with Mark Cuban</p>

<p><strong>Isn't the worst possible scenario the one were we get hung up on the fence 50 % analog and 50 % digital?</strong></p>

<p>That won't happen. I just don’t believe you will get to 50/50 where it just holds. </p>

<p><strong>Were you interested in the attempts by Senator John McCain to set the hard date for spectrum return to 2009 with a provision for a billion dollar loan to subsidize decoders for those refusing to buy one or unable to buy one?</strong></p>

<p>I think it will take a lot more money than one billion dollars for a 2009 date certain cut-off. It is absurd to think that Washington is going to turn off television to tens of millions of people. It is political suicide.  </p>

<p><strong>There has been talk about people looking less attractive on HDTV due to imperfections.</strong></p>

<p>It is an issue not just for people but also for our sets. The detail on soap operas where we have to upgrade the set. We didn’t have to upgrade on news because they were already pretty high tech. There is a lot more detail there and if the detail is bad you don't want the people to see it. You have to make things better. </p>

<p><strong>Is the Disney Corporation happy with HDTV—glad you made the move?</strong></p>

<p>Everyone is very pleased with the 720p decision. That was something felt to be very important by the Disney Imagineering people. Progressive scanning was the way to go. That has been a good thing for us with other areas of the company that are producing content.</p>

<p><strong>When you go home what is your own viewing experience? What are your habits like?</strong></p>

<p>I watch more D-VHS than I would bet than anyone in the world. I have D-VHS recorded both in New York and Los Angeles because I don’t get to watch a lot of the shows live. So, I have D-VHS of all of our competition and our own shows. I have four separate JVC 400s--the professional version of the D-VHS player. I also have two HD-PVRs. I have six more PVRs. I watch a lot of television.</p>

<p><strong>And your family…do they feel as enthusiastic about all of this?</strong></p>

<p>Martha and I have been married for 37 years. We don't have children but I can tell you that when mym niece and nephews come to visit they are more impressed with fact that the X Box has a component output that makes for better graphics then they are by seeing some HDTV show. </p>

<p><strong>Did you get a chance to look at our new HD Programming Grid Guide?</strong></p>

<p>I did. I use it. I am a fan.</p>

<p><strong>Thank you Alex.</strong><br />
</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Dale Cripps</b>, <b>June  9, 2005 08:58 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 64
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
			
 		<?if (4 <> 7) {
 			# Recent Articles by Author (exclude this one)
 			# Do not show recent articles for Bulletins.
 			$qry = "
 			SELECT entry_id, entry_blog_id, entry_created_on, entry_title, author_id, author_name
 			FROM mt_entry e, mt_author a
 			WHERE entry_blog_id = 4
 				AND entry_id <> 64
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
 				<h2>About Interviews</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_-_alex_wallau_-_president_abc_network.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
