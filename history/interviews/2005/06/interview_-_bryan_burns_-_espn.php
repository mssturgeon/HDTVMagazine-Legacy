<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 63";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Dale Cripps'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 63 AND placement_is_primary = 1";
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
	<meta name="keywords" content="hdtv magazine, bryan burns, progressive scan, going hdtv, espn espn, espn, ESPN, HDTV, hdtv, our, going, time, bryan, Bryan, year, magazine, Magazine, sports, burns, Burns, right, events, every, been, cable" />
	<meta name="description" content="Back in 2003 ESPN made a huge commitment to HDTV. On the day of their launch we talked to Bryan Burns, who had a key responsibility for ESPN's venture into HDTV. INTERVIEW WITH BRYAN BURNS VICE PRESIDENT, STRATEGIC BUSINESS PLANNING..." />
	<title>HDTV Magazine Interviews - INTERVIEW - Bryan Burns - ESPN</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/interview_-_bryan_burns_-_espn';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('INTERVIEW - Bryan Burns - ESPN'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_-_bryan_burns_-_espn.php";
		if ($author[img] != '' && 4 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Dale Cripps" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">INTERVIEW - Bryan Burns - ESPN</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Dale Cripps</b><br />
				<?=$author_title?>
				Posted on <b>June  9, 2005</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category="></a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_-_bryan_burns_-_espn.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/history/interviews/2005/06/interview_-_bryan_burns_-_espn.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_-_bryan_burns_-_espn.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_-_bryan_burns_-_espn.php&amp;phase=2&amp;title=INTERVIEW%20-%20Bryan%20Burns%20-%20ESPN&amp;bodytext=Back%20in%202003%20ESPN%20made%20a%20huge%20commitment%20to%20HDTV.%20On%20the%20day%20of%20their%20launch%20we%20talked%20to%20Bryan%20Burns%2C%20who%20had%20a%20key%20responsibility%20for%20ESPN%27s%20venture%20into%20HDTV.%20INTERVIEW%20WITH%20BRYAN%20BURNS%20VICE%20PRESIDENT%2C%20STRATEGIC%20BUSINESS%20PLANNING...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p><em>Back in 2003 ESPN made a huge commitment to HDTV. On the day of their launch we talked to Bryan Burns, who had a key responsibility for ESPN's venture into HDTV.</em></p>

<p>INTERVIEW </p>

<p>WITH  </p>

<p>BRYAN BURNS<br />
VICE PRESIDENT, STRATEGIC BUSINESS PLANNING AND DEVELOPMENT</p>

<p>ESPN</p>

<p><br />
Bryan Burns</p>

<p>I am very pleased to bring to you an interview with the man who is making a great deal of HDTV history, Bryan Burns, from ESPN. It was last September when we sent out an HDTV Magazine EXTRA to break the news that ESPN was going to have an HDTV channel. This was particularly rewarding to me for ESPN has been a reader of ours (when we published the HDTV Newsletter) for 18 years. I wanted to bring you the words of Bryan, which I think are some of the most exciting I have heard since being in HDTV,  Sports fans, you have to know now there is a benevolent cosmos looking out for you.</p>

<p></p>

<p>--------------------------------------------------------------------------------</p>

<p>"We are going to use every source we have, and we have a bunch of sources."<br />
__Bryan Burns, ESPN</p>

<p><br />
--------------------------------------------------------------------------------</p>

<p>Bryan Burns came to ESPN from The Paragon Alliance, a consulting firm he founded in 1992, and that after sixteen years in professional sports team management. His MLB career included seven years as Senior Vice President of Major League Baseball, where his responsibilities included handling MLB’s worldwide television operations and overseeing special events such as the World Series, League Championship Series, and the All Star Game. Burns also served as Director of Marketing and Broadcasting for the Kansas City Royals from 1974-1983. At Comsat Video Enterprises from 1990-1992, Burns oversaw negotiations for the major sporting and special events included in the company’s Satellite Cinema and On Command Video pay-per-view services. Since April of 2000 Bryan Burns has been the strategic business planning and development Vice President for ESPN.</p>

<p>He joined ESPN in 1996. He has been responsible for the expansion of ESPN’s pay per view product to include ESPN FULL COURT for college basketball, ESPN Game Plan for college football, and MLS/ESPN Shootout for Major League Soccer. He also designed and launched ESPN NOW and ESPN EXTRA, the company’s channels, which are designed for digital cable and the expanded capacity of Direct Broadcast Satellite carriers. Burns also was responsible for ESPN’s special markets efforts to non-residential distributors such as commercial establishments and hotels.</p>

<p>Now Burns enters the new world of HDTVas the head of ESPN HD, the company’s new channel for high definition television, that launches the end of next month. </p>

<p>We talked to him by cell phone yesterday as he was driving in Eastern Connecticut</p>

<p>I started the interview by asking....</p>

<p>HDTV Magazine: How long had you considered going into HDTV as a strategic move for ESPN?</p>

<p>Bryan: I first saw HDTV in 1989 when working in Major League baseball. It has been on my radar screen ever since. </p>

<p>But specifically, we have been looking at HDTV with increasingly higher levels of intensity as time has gone by over the last two years. At about a year into our study we realized it was not a matter of if, it was only a matter of when. Choosing our "when" time was going to be our toughest assignment. When do we dive into the pool. Then we had to decide if we were going to dive in the shallow end or the deep end based on all kinds of market factors which you have been following for quite some time.</p>

<p>HDTV Magazine: What was the tipping point that actually caused you to make the decision?</p>

<p>Bryan: I don't know if there was one thing. We announced this our move into High Defintion in September of last year. We had gone to our distributors at national (NCTA) show for cable in June of last year and asked them about their interests, technical specifications, etc. Wen they basically said, "Bring it on because if we are going to HDTV in sports we want it to be ESPN." That gave us the last push to go. A story I often tell is this one. I was sitting in my office in November or early December where I have three TVs used to watch our various networks. On all of them at every commercial break we were selling widescreen TV for somebody. I would see Zenith in one break, Circuit City in the next, and Sears in the next. I sat back and thought, yes, we did make this call at the right time. This IS the right time.</p>

<p>HDTV Magazine: We believe it is. </p>

<p>Byran: We did keep quiet until our announcement. But when we did a reporter called and asked about a comment that Gary Shapiro, president of CEA, had made. He said that with ESPN going to HDTV a tipping point was reached in this business. I called him the next day to thank him and added, "We totally believe in what you said." </p>

<p>We felt all along with our kind of content and this kind of brand we had the opportunity to move the needle for the entire business. HDTV has needed content in sports from someone who can use the various mediums that we have, be that ESPN 1, ESPN2, ESPN Classic, ESPN News, ESPN.COM, ESPN the Magazine, ESPN Radio networks, etc., etc., to drive this information home to the consumer. We can do that like nobody else can!</p>

<p>HDTV Magazine: We have urged the entire broadcast community--all programmers and distributors--to unleash their tremendous power of influence across the nation, but they have hardly used any of that potential for this transition. </p>

<p>Bryan: I totally agree. </p>

<p>HDTV Magazine: That can only suggest to us that not everyone is ready yet to say, "Let's through it into high gear."</p>

<p>Bryan: Just moments ago I was on a conference call with our consumer marketing folks about the production of promotional spots that are going to run on all of our networks about the early ESPN HD events such, as the Opener of Sunday Night Baseball, the Women's Final Four. The discussion was about how we were going to put into those promotions the fact that these events are also on ESPN HD. I noticed last week in the promotion of the Grammys (CBS) that there was no mention of HDTV. </p>

<p>We have a new service to launch and we are going to use all the media we have to tell the distributor, the consumers, and retailer community that it is coming. We are going to use every source we have, and we have a bunch of sources. </p>

<p>HDTV Magazine: Will retailers have a free license to tune you in and display your programming in their retail environments?</p>

<p>Bryan: There is a step for us in-between, of course, and that is the distributor. Generally speaking our distributor agreements with cable or satellite allow them to provide our programming (ESPN 1 and ESPN 2 -- anything we have) to retailers without charge for promotional purposes. We want that. We encourage that. That is a big key here. Absolutely! </p>

<p>HDTV Magazine: I note from your background that you had spent time in developing markets for bars and hotels. Is the Sports Bar going to be a part of your strategy?</p>

<p>Bryan: We have a very interesting set of constituents with whom we work. We have consumers, fans, and people on the street, like you and I, who like sports and consume an awful lot of ESPN. We have distributors -- cable and satellite. We have advertisers...such as Anheuser-Busch, Coors, Miller...You can expect that once we get this thing in the air (30 days from today) we will work very hard across all of our constituents to marry them for maximum impact. We fully recognize how that can work. I also recognize that part of our advertising community...well, we have the largest list of advertisers of any telecaster in the country. I think there are  800 active advertisers on ESPN right now across our various family of networks, and for a very simple reason: We help folks sell things.</p>

<p>That list of companies includes the companies I already mentioned as well as Zenith, Samsung, etc. Yes, I am going to find a marriage somehow, some way, and walk into that community and say, " We have an opportunity here. How are we going to do this?" We just need to figure out how to do it and make sure all of the planets are aligned in the right way to bring a turn key operation into commercial establishments. It's pretty simple. When you walk into a bar, what's on TV? ESPN. We will find a way to do that. It would be silly not to.</p>

<p>HDTV Magazine: The programming that you mentioned strike me as  being of  the highest of marquis value? Is that correct?</p>

<p>Bryan: Let me take away any belief you have that we are not doing our highest marquis productions. We had manyf ways we could have gone. We could not physically do all of our events in a year's time right out of the box. We decided to go with what we call our big events strategy. We are going to do the NFL. We are going to do Major League Baseball. We are going to do the National Hockey League. We are going to do the NBA. So, yes, the four major pro sports leagues. We are the only television entity who has ever had all four under contract at one time. We are doing them all in High Def. </p>

<p>In our minds (the combination of) ESPN and HDTV really blossom about a year from now. ESPN HD as we started is kind of an on-ramp for what it is really going to be in a year from now. Chuck Pagano, your friend for many years, has built a new digital center in Bristol, Connecticut. It is going to be 120,000 square feet. Right now it is built; it is heated; it is cooled, but it is not yet outfitted. Chuck will start to buy the electronic guts for that building at the National Association of Broadcasters convention beginning in a few weeks time (Las Vegas in April). When it is done in a year from now we think it will be the largest HDTV facility in the world because we are converting our entire operation here. What that means for you and your readers is that when we get it done we are going to start producing most of our studio programming in native High-Definition. We will add 3700 hours per year studio native High Definition Television in about a year from now!</p>

<p>What does that mean for our on ramp year? We went to the national cable show (NCTA) and asked our distributors--cable and satellite--if they want us to do an event from time-to-time, or do they want something all of the time, knowing that if it is all the time we just can't yet do it all in HDTV. They said, "We want to set it and forget it. We do not want to have to send a guy to the head end every time you guys do a basketball game and tweak the bandwidth. We just can't do that."</p>

<p>Using that as our marching orders we are going to start by taking the ESPN service and upconvert it 24 hours a day. I understand that upconversion is like chalk across the blackboard to many of the 'purist' who have been in this for awhile, but it is the only way to start and have a service going all of the time.</p>

<p>From a remote production standpoint it would take 40 remote trucks to do what ESPN does (presently) in a year's time. I just could not make that economically work, as you might guess. We have commissioned three trucks to be built. The first comes off the production line today. That will allow us to do two to three events a week in the first year. We have to do 'truck logistics'. We look at things like the NBA finals and say, O.K. we will do game 2 and we will do game 5, but we can't do game 2 and game 5 if they are further than 600 miles away because I can't get the truck there in time. We are spending a lot of time maximizing the logistics of truck operations in an attempt to get the most telecast that we can squeeze out of these three units. So, what we have in this first "on ramp" year is an upconverted 24/7 signal with as many events as we can logistically put together, with an emphasis on our big events. </p>

<p>A year from now, when the center is built, we can start making programs almost overnight for half of a year's time produced daily. In a year and a half from now we will go on. Let there be no question that we have a long term commitment here. We are spending oodles of money and oodles of time, and our people are fully engaged. We have three thousand people working at ESPN and many from a technical background. They could work for us or channel 3. I t would not make much of a difference. But (with HDTV) they really care. They have waited their whole life to work on HDTV. </p>

<p>HDTV Magagzine That is a common phenomena found in every sector but retail.</p>

<p>HDTV Magazine: Are you all 720P.</p>

<p>Bryan: I am sure you have talked to Alex Wallau (president of ABC). He feels very strongly about it, as do we. Our view is that its not about the 1080 and the 720, it's about the "p" and about the "i". If you and I are doing the news, it's not a big deal (interlace or pgoressive), but for motion in sports, for pucks, bats, balls, nintey eight mile per hour sliders...progressive scan is going to cover the motion of sports better than interlace. It was a tough call for us, in part, because we knew it would make our costs higher. There is a lot of progressive scan pieces that have not yet been built. But we felt like we were in a VHS/BETAMAX decision process. We felt that, hey, we are ESPN and we have to make the right decision for all sports. The right decision for all sports is progressive scan. We see that if we go to Best Buy and ask to see the best DVD player that they have they show you a progressive scan. It is the best. We have to do things the best way.</p>

<p>HDTV Magazine: I think the audience is growing up with respect to this issue and have come to understand the trade offs. There is an increasing awareness that 1080 i may be better for still and filmed images while 720p delivers a better result when motion is present. I doubt it is an issue.</p>

<p>Byran. We felt we were making a ten year decision, perhaps a decision for all times. We were making a progressive decision over that of interlace. I was a little surprised to go to the CES this year and not get hit on this position.  I think you are right. It is a non issue.</p>

<p>HDTV Magazine: Who will be carrying you? </p>

<p>I will leave the specific answer to another in our company but will tell you that we are in very active conversations with every one of our distributers--cable, satellite, large, small--very active discussion with all of them on both business and engineering levels. I think it is clear that we are not going to start with every operator in the country signed up. But the conversations are ongoing and intense. We think the things we are going to do on our 'air' will help stimulate interest at every level and help in the process of carriage and clearance. Hopefully, we can wrap up carriage arrangements as soon as possible.</p>

<p>HDTV Magazine: Will you have any conditional access of any kind where you will be blacking out programming?</p>

<p>Bryan: Blackouts are a way of life with us. We will have dual pathways to our distributors just as we have in our standard definition. That is another expense we have  that other programmers in HDTV don't have. It is the nature of sports. </p>

<p>HDTV Magazine: Thank you Bryan and good luck in your HDTV ventures. We applaud you.</p>

<p>***</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Dale Cripps</b>, <b>June  9, 2005 08:50 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 63
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
 				AND entry_id <> 63
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_-_bryan_burns_-_espn.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
