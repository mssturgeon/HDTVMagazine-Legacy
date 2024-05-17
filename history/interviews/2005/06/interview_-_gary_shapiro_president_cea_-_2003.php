<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 117";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Dale Cripps'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 117 AND placement_is_primary = 1";
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
	<meta name="keywords" content="air antenna, air broadcasting, gary shapiro, aggressive campaign, digital television, HDTV, hdtv, cable, been, broadcasters, think, antenna, our, satellite, consumer, most, digital, air, time, consumers, industry, CEA, product, cea, FCC" />
	<meta name="description" content="Interviewed by Dale Cripps in the fall of 2003. It is doubtful that HDTV could have a better friend than CEA president, Gary Shapiro. He is a believer that HDTV is both a good thing and that everyone who sees..." />
	<title>HDTV Magazine Interviews - INTERVIEW - Gary Shapiro, President CEA - 2003</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/interview_-_gary_shapiro_president_cea_-_2003';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('INTERVIEW - Gary Shapiro, President CEA - 2003'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_-_gary_shapiro_president_cea_-_2003.php";
		if ($author[img] != '' && 4 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Dale Cripps" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">INTERVIEW - Gary Shapiro, President CEA - 2003</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Dale Cripps</b><br />
				<?=$author_title?>
				Posted on <b>June 24, 2005</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category="></a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_-_gary_shapiro_president_cea_-_2003.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/history/interviews/2005/06/interview_-_gary_shapiro_president_cea_-_2003.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_-_gary_shapiro_president_cea_-_2003.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_-_gary_shapiro_president_cea_-_2003.php&amp;phase=2&amp;title=INTERVIEW%20-%20Gary%20Shapiro%2C%20President%20CEA%20-%202003&amp;bodytext=Interviewed%20by%20Dale%20Cripps%20in%20the%20fall%20of%202003.%20It%20is%20doubtful%20that%20HDTV%20could%20have%20a%20better%20friend%20than%20CEA%20president%2C%20Gary%20Shapiro.%20He%20is%20a%20believer%20that%20HDTV%20is%20both%20a%20good%20thing%20and%20that%20everyone%20who%20sees...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p><strong>Interviewed by Dale Cripps in the fall of 2003.</strong></p>

<p><em>It is doubtful that HDTV could have a better friend than CEA president, Gary Shapiro. He is a believer that HDTV is both a good thing and that everyone who sees it, wants it. He sits in a position of power to aid HDTV and while his direction may be influenced by the responsibility he must have to his manufacturing constituency, the public interest remains firm. He is not faint-of-heart and strikes out at the related industries which all must do their part to make HDTV a happening thing. Gary has been in a leadership role in consumer electronics for 20 years. He is a lawyer by training and a manager by experience. He runs one of the largest trade shows in the world--the CES--held annually in Las Vegas. He brings to that venue manufacturers, developers, distributors, retailers, cable executives, DBS executives, broadcast executives, pre-recorded executives, and computer executives to mix with those from  Washington -- the FCC members and key legislators who have their hands in consumer electronics and signal policy. He was honored this last year with the prestigious Industry Leadership Award from the Academy of Digital Pioneers, an organization formed to honor those who have made important contributions to the digital television movement.  </em></p>

<p>My talk begins with a general assessments of the industry and quickly turns to where things can be made better:</p>

<p><strong>HDTVMagazine: We are approaching the fall season and there seems to be plenty of activity with regards to HDTV. Can you tell us what the CEA's present efforts are?</strong></p>

<p>Gary Shapiro: For several years CEA has been aggressive in promoting HDTV to Americans. I am sure you have been to our HDTV web. What we do here is focus on the macro--the well defined picture. The purpose of CEA is to grow the industry. We have been advocating HDTV for a decade and been at the forefront of many of the issues. There is no question that despite all of the mass media stories about the failure of HDTVwe have, as have you, believed that HDTV was inevitable even though there would be bumps in the road. We feel validated by the fact that HDTV is achieving enormous acceptance by program providers and consumers. Our sales figures on an aggregate basis are very, very positive. This has been a tipping point year. You understand that after having rocks thrown at us for many years about this failed product our faith, like yours, remains unshakable. </p>

<p><strong>I have. I believe you also have some helpful tools for the marketers of HDTV, do you not?</strong></p>

<p>We continue to work with every segment of every industry in many different ways. Our research is something is extensive on what consumers want with HDTV. Indeed, we have seen several of the major program providers join CEA recently, including NBC, ESPN and Discovery Channel and ABC because of the amount of information we are providing on HDTV. </p>

<p><strong>This is hard data your are pulling up from surveys and direct contact with consumers?</strong></p>

<p>We are active in several areas. We do original research on consumer's needs and expectations and opinions. We also do advocacy work on the Hill and with the FCC promoting HDTV as a concept. Our major focus with the government right now is the "plug and play." agreement with the FCC. That is the most critical thing we are working on in terms of advocacy right now. We also have our HDTV Guide, which lists all 450 models of DTV products. </p>

<p>We are also very active with www.Antennaweb.org, which is the web site that lays out the antenna map that is active in retail stores. We think that is an important part of what we do. Obviously our definitions is something we continue to push. We think they have generally been accepted in terms of what HDTV is vs. digital television. We are preparing for the fourth annual Academy of Digital Television Pioneer Awards for recognizing those who are instrumental in digital television. </p>

<p><strong>You and I share awards from that organization for last year, didn't we?</strong></p>

<p>Yes. It is important to recognize people in a positive way in every different category and segment for what they have done to help HDTV happen. As you know the Academy members themselves are the people who have made a difference in H/DTV. They are the ones who vote on the awards, although we did add a People's Choice Award this last time.</p>

<p><strong>Did you see the interview in our past Pagae 2 edition?</strong></p>

<p>Yes, the one with Best Buy.</p>

<p><strong>Bill Cody (BEST BUY) said that the most important thing left  to be accomplished is getting cable fully on board. That leads me back to your comment on the cable "Plug and Play" issue. What opponents do you find in the "Plug and Play" agreement and how are you overcoming them?</strong></p>

<p>Right now the major challenge is getting something out of the FCC. We are very hopeful that we will get something in September. If not it certainly will be tragic. </p>

<p>Our opposition to the mandatory tuner stems from the logic that most of Americans are getting most of their programming by satellite and cable. So, we thought the cable solution was much more critical. Even a government mandate cannot force Americans to put up antennas. We have been very aggressive in promoting antenna usage for several years. We continue to promote it. We would like to see the broadcasters step-up and also promote antenna usage. We are totally puzzled as to how they could focus on mandating TV tuners but in all of their efforts (with the exception of just a few like WRAL) I have yet to see a TV advertisement for over-the-air antenna usage.</p>

<p><strong>Do you have any data that suggests that antenna usage is being adopted by the consumers?</strong></p>

<p>No, I don't. We don't have that data because it is not being adopted by consumers, at least to my knowledge. It is sticking to around 10 to 15% (of TV households.) </p>

<p><strong>Is this due to the view that an antenna is a retro or old fashioned technology as opposed to something more modern?</strong></p>

<p>Seventy plus percent of Americans are relying on cable and fifteen to twenty percent are are relying upon satellite. For services that most of us are paying for it doesn't make sense for consumers to think about antennas unless there is an aggressive campaign to get them to think about it.</p>

<p><strong>There is one thing I find very interesting with our audience. They are quite devoted to watching HDTV and not the lower standard. Most of the HDTV that they are watching, with some obvious exceptions, is from over-the-air services. They have retreated from the "200" old standard cable channels and adopted viewing patterns which are more in line with what the networks are locally providing to them. That would suggest that for many an antenna is sufficient and, perhaps, the only thing they want for their HDTV viewing. </strong></p>

<p>I certainly find that to be the case in my home. I tend to exclusively watch HDTV programs. I do have an over-the-air antenna. </p>

<p>History will review HDTV as the most successful product launch of a major product category. </p>

<p><strong>We do see more and more positive, or, at least, less negative press coverage now occurring where the story ends up favoring HDTV if you have the money. Certainly, the quantity of programs is not considered in short supply, though we can always look forward to more and we see coming online all of the time, so there is no sense that the trend is against you...</strong></p>

<p>This is much better than the launch of color TV. On an inflation adjusted dollar basis HDTV is actually cheaper than color was at the same point in time after its introduction. The quality is also far superior to the analogous quality of color at this point. Color. Being an analog medium it was new and the colors were not that good. Yet, color TV was one of the most successful launches in history. History will review HDTV as the most successful product launch of a major product category. Here you have a product that cost over a $1000 and yet it is rapidly achieving mass market acceptance.</p>

<p><strong>When cars cost $24,000 or more it now does not seem much to spend $1000 on something with which you will spend more time with than your car.</strong></p>

<p>That is probably a good point. When color was introduced a car cost eight to ten times what a color set did. Today it is fair to say that a car is a great deal more. Obviously, that is not the fault of the auto industry. For the most part HDTV is a digital technology with no moving parts. We don't have to go up to 100 MPH. So, comparing the cost to a car may not be the fairest thing, but you do spend as much time, or more, in front of your TV than your car.  </p>

<p><strong>Let's talk about the decoder problem. I call it a   problem because of the ratio of decoders being sold to monitors -- about 11%. What is the cause of this disparity?</strong></p>

<p>_____________________________________________________________________<br />
<em>It has been broadcasters who have been most disappointing.</em> _Gary Shapiro <br />
_____________________________________________________________________</p>

<p>I don't think there is a problem as much as a marketplace a work. As much as we like to think that HDTV is this great, wonderful product which broadcasters are pushing, the truth is that people buying HDTV's do so for one of three reasons. </p>

<p>One is DVDs.Movies look better on HDTV. We all know that it doesn't use the full capability, but DVD does look spectacular on HDTV, plus you get the surround sound. The fact that people are buying monitors to watch their DVDs is the marketplace at work. </p>

<p>The second reason is satellite programming. If you look at DirecTV you can see that the HDTV stations are identified. There are many of them. It started out with just Mark Cuban on channel 198 and HBO. Now you have many more. </p>

<p>The third reason has to do with cable and broadcast programming. Broadcasters have been modest in their offerings, though that is increasing. The local broadcasters clearly have a long way to go. Cable started out very slowly but in the last year, especially the last few months, they are rushing to get to HDTV. It is a competitive issue. They realize that Americans want HDTV and they are losing their best viewers to satellite. </p>

<p>It has been broadcasters who have been most disappointing. They don't talk enough about HDTV. They don't push it. There is not a concerted campaign. It is not aggressive. I know that the National Association of Broadcasters have made some modest efforts, but there is zero promotion of over-the-air antennas. There has been very little promotion across the broadcaster industry. </p>

<p><strong>Can you put your finger on why? </strong></p>

<p>I think it is a tough economic time. It is a competitive model and people are focusing on the last quarter's numbers. You can see the differences in the quarter numbers In the cable and satellite industries because one is losing subscribers to another  because of HDTV. For broadcasters the pain for not being economically competitive with HDTV is similar to the same pain they experienced when asleep at the switch as cable was introduced and by not paying attention to satellite. It is a slower erosion of market share. That erosion is going to continue as it has for another 20 years. It is just going to get worse while being exacerbated by their lack of promotion of HDTV. So, it is a macro long term threat that broadcasters face. It is almost the "AM-ization, " if you will. That is something they have to face. Some, like CBS, specifically, have been very aggressive. That has been helpful. Others have not paid attention or they have made wrong decisions. The biggest wrong decision was Fox's 480p policy, which has now been switched to HDTV. They didn't want to go the way of AM or become the inferior medium. I think that is a very important decision made by Fox president Peter Chermin. I applaud him for it.</p>

<p><strong>Rupert Murdoch said at the May 2003 Congressional hearings on Newscorps' acquisition of DirecTV that HDTV was the only category capable of making the digital TV transition happen</strong>.</p>

<p>The fact that they (Europe) have gone to digital and not HDTV is, I think, a consumer tragedy. </p>

<p>I agree with Mr. Murdoch that HDTV is an incredibly important part of the digital transition.That was not been the broadcaster's view for a long time. There was a lot of talk about these various schemes to make money and monetize the spectrum. But when you read about how Europe...Europe is behind us in that they don't have HDTV. They do have digital. They are transitioning  quickly. I read recently that Berlin, Germany has already returned the spectrum. They are getting spectrum back quicker. From a Washington point-of-view that is very important. The fact that they have gone to digital and not HDTV is, however, a consumer tragedy. The US approach is right for the US and, perhaps, the European approach is best for the Europeans considering all of their different languages, etc.</p>

<p>I think CEA could be persuaded to withdraw that lawsuit if the broadcasters stepped forward with an aggressive campaign aimed at over-the-air antenna promotion. </p>

<p><strong>Going back to the discussion on decoders: The OTA/cable and satellite decoders for the US market are not being proportionally acquired. Are we using all of the promotional tools at our disposal?</strong></p>

<p>No, we are not. There is this lawsuit now in the courts where we (CEA) have challenged the mandatory tuner and the FCC's ability to mandate it. That lawsuit is working its way to an oral argument. I think CEA could be persuaded to withdraw that lawsuit if the broadcasters stepped forward with an aggressive campaign aimed at over-the-air antenna promotion. They have been quiet on promoting over-the-air broadcasting. If they would spend less time trying to put mandates on us and more time on promoting over-the-air antenna I think the marketplace would help out.</p>

<p><strong>We have heard broadcasters say that CEA and its members have given up on over-the-air broadcasting and you're not paying much attention to it.</strong></p>

<p>I can't imagine their saying that considering our aggressive campaign with antennaweb.org and the millions of dollars we have spent promoting it without any broadcaster assistance. I think it defies credulity. Manufacturers are themselves promoting over-the-air broadcasting while broadcasters are, instead, focusing their efforts in Washington (on mandates). </p>

<p>Our industry (CE) has a history of avoiding mandates in Washington. We focus on the marketplace. We know you can't force consumers to buy what they don't want. But you can do cleaver marketing to encourage wants into needs. You can create your product categories. You can create things that were once only an engineer's imagination and turn them into a consumer product whether that is  computer, a personal digital assistant, a wireless internet device, or even HDTV. None of these products came out by way of government. They came through people experimenting and introducing products at the CES, seeing what retailers, consumers, and the press would respond to. A few succeed from the long list of products introduced.</p>

<p>HDTV happens to be one of those products with a tremendous belief by Dick Wiley, Gary Shapiro, Joe Flaherty, Peter Fannon, Dale Cripps, and quite a few others. This was a product that captured our imaginations and we felt it would capture the imagination of the American Consumer, We have been rigid in that belief for ten years. Along the way we fought claims that it was a foreign invasion; that there was a better transmission system, or, that there is nothing in it for broadcasters or anyone else. We fought every step of the way and history has proven that the Flaherty's, the Cripps, the Wileys, and all other believers were absolutely right in their steadfast determination for HDTV. When you look back in history and ask, "What are you proud of?" I am pretty proud of what we did for HDTV. </p>

<p><strong>Getting back to antenna and consumer acceptance: The term "wireless" has grown popular and glamorous. Why is the TV  antenna not made more glamorous today? </strong></p>

<p>There are different flavors of wireless. There is the telephone that has an almost imperceptible antenna compared to the antenna you must put on your roof or in your house for TV. It is much more visible.</p>

<p>If somehow broadcasters can be organized to get together to use that powerful tool to help themselves promote over-the-air broadcasting they will unleash a torrent of potential AND marketplace acceptance of antennas. </p>

<p><strong>Is there no way to camouflage these devices or put them into an artistic form so they become aesthetically pleasing?</strong></p>

<p>A satellite dish is an antenna. There have been various devices to make them more visibly attractive for communities. With satellite radio services you are looking at an antenna on top of your car. I have one. Doesn't bother me at all. It is a question of consumer awareness. Broadcasters control the most powerful tool there is for creating consumer awareness. The whole business model for broadcasting is based on consumer awareness. That is what they are selling to others. If somehow they can be organized to get together to use that powerful tool to help themselves promote over-the-air broadcasting they will unleash a torrent of potential AND marketplace acceptance of antennas. That may help solve many of the their existing problems. I think it is fair to say that the cable industry and the satellite industry are secretly pleased that broadcasters have not figured out that they themselves have the power and ability to change their own future. </p>

<p><strong>That brings us to cable. I did talk to the NCTA which seem to be quite canned, but positive statements: "Yes, it is a good thing to do. It is our future. We are doing everything we can. Are you seeing what they are saying?</strong></p>

<p>I believe the cable industry -- for the majors --there is no question in my mind that they are very interested in HDTV as a competitive tool against satellite. It is in their business interest to be so. They are rushing to it. They don't want to be perceived as an inferior medium. I think the marketplace will be very helpful with cable in getting to HDTV providing there is a "plug and p lay" agreement, which allows consumer electronics companies to invest in a standardized technology. </p>

<p><strong>When I asked you what the major obstruction to this agreement was earlier in this interview you seemed to say that it was the FCC. Why?</strong></p>

<p>I would not say in the short term I would say the FCC is an obstruction. I didn't mean to say that. What I mean to say is that a failure to enact the plug and play agreement is the major impediment in the short term.</p>

<p><strong>What is impeding the FCC from making this decision?</strong></p>

<p>I think you will have to asked them that. They have their due process. In December 2002 we handed it over to them and Chairman Powell assured me and Robert Sachs (president of the National Cable Telecommunications Association) that he would do everything possible to move quickly. I think they did initially and got it out to comment (each rule must go through a process of comments and reply comments prior to an FCC action). Right now it is not going as fast as we want and we are very frustrated. </p>

<p><strong>I have heard that manufacturers want this done by September in order to give them time to fabricate for the next fall buying season. Is that the problem. </strong></p>

<p>Manufacturers are facing the dilemma of a manufacturing decision. While each manufacturer makes its own decision but if there is any risk of a change in that agreement they can't really go into production.</p>

<p><strong>Is there any serious risk in the agreement now?</strong></p>

<p>The agreement is very clear, helpful, and specific for almost all parties. There are those fringes of the "copy anything" people and the "copy nothing" people. Both sides have issues. That means we clearly end up in the middle. We are confident that it preserves consumer's traditional use rights and it protects the copyright interests as they have been protected with satellite. It steps up their protection. I think it is an agreement that is about as acceptable to everyone as will ever be created. It seems to be a slam dunk, but we are just counting the days.</p>

<p>H<strong>Back to broadcasting for a minute. The recent upfront markets (for advertising at the network level) enjoyed a record $9 billion in sales of advertising space. None of that is identified for HDTV or caused by HDTV. Is the problem that all bills are still being paid by the traditional business?</strong></p>

<p>Look at how cable came into the marketplace some 30 years ago. Broadcasters looked at cable and said, "Ah, what do I care about them? Ii is too small a market." In the exact same way cable and broadcasters responded to satellite, video games, VCRs, and everything else. Every time a new media arrives it starts with a small marketplace and they ignore it. Then it grows very big and takes away their marketshare, and, before you know it, they have a new competitor which they should have paid attention to in the beginning and focused on what consumers were asking for. With broadcasters vs. cable consumers were asking for choice. For cable vs satellite they were asking for choice and, I would argue, quality of programming. Now consumers are making a huge investment. The fact that are not buying tuners is not alarming to me, although they are increasingly doing so. They are investing in the largest portion of it--the monitor. They will be looking for programming that can be displayed beautifully on that monitor. </p>

<p>HDTVMag: Thank you Gary.</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Dale Cripps</b>, <b>June 24, 2005 02:39 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 117
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
 				AND entry_id <> 117
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_-_gary_shapiro_president_cea_-_2003.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
