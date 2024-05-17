<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 80";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Dale Cripps'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 80 AND placement_is_primary = 1";
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
	<meta name="keywords" content="goal hdtv, hdtv magazine, signal providers, original goal, hdtv every, HDTV, hdtv, new, VSB, vsb, standard, goal, say, think, industry, cofdm, COFDM, receivers, could, television, been, spectrum, most, FCC, things" />
	<meta name="description" content="Dale Cripps:
The original goal of HDTV was to create a worldwide electronic production standard competitive to 35mm film. The goal soon became one to create a new experience for the home--the next generation of television. Test in Japan determined that a 30 degree field of view and the 5.1 audio system made for a dramatically new experience. To do that visually, and without artifacts, you would need about 2 million pixels in a wide shape (later became 16:9).

In the beginning HD was not recordable. Neither were there displays for it. Few had hopes of ever transmitting it. Through to 1984 it was driven fundamentally by this desire to have one electronic production standard that would downconvert with equal ease to the existing transmission standards. Finally NHK developed a HD satellite broadcast system in 1985, intended to be used for the launch of HDTV satellite services in Japan. That transmission system, they once thought, would be sought after around the world and their own beginnings in Japan would mean a lower cost of introduction elsewhere. Japan would dominate the information age with the ultimate information appliance.
" />
	<title>HDTV Magazine Interviews - An Interview with Dale E. Cripps, HDTV Pioneer - 1998</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/an_interview_with_dale_e_cripps_hdtv_pioneer_-_1998';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('An Interview with Dale E. Cripps, HDTV Pioneer - 1998'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/history/interviews/2005/06/an_interview_with_dale_e_cripps_hdtv_pioneer_-_1998.php";
		if ($author[img] != '' && 4 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Dale Cripps" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">An Interview with Dale E. Cripps, HDTV Pioneer - 1998</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Dale Cripps</b><br />
				<?=$author_title?>
				Posted on <b>June 15, 2005</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category="></a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/interviews/2005/06/an_interview_with_dale_e_cripps_hdtv_pioneer_-_1998.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/history/interviews/2005/06/an_interview_with_dale_e_cripps_hdtv_pioneer_-_1998.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/history/interviews/2005/06/an_interview_with_dale_e_cripps_hdtv_pioneer_-_1998.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/interviews/2005/06/an_interview_with_dale_e_cripps_hdtv_pioneer_-_1998.php&amp;phase=2&amp;title=An%20Interview%20with%20Dale%20E.%20Cripps%2C%20HDTV%20Pioneer%20-%201998&amp;bodytext=Dale%20Cripps%3A%0AThe%20original%20goal%20of%20HDTV%20was%20to%20create%20a%20worldwide%20electronic%20production%20standard%20competitive%20to%2035mm%20film.%20The%20goal%20soon%20became%20one%20to%20create%20a%20new%20experience%20for%20the%20home--the%20next%20generation%20of%20television.%20Test%20in%20Japan%20determined%20that%20a%2030%20degree%20field%20of%20view%20and%20the%205.1%20audio%20system%20made%20for%20a%20dramatically%20new%20experience.%20To%20do%20that%20visually%2C%20and%20without%20artifacts%2C%20you%20would%20need%20about%202%20million%20pixels%20in%20a%20wide%20shape%20%28later%20became%2016%3A9%29.%0A%0AIn%20the%20beginning%20HD%20was%20not%20recordable.%20Neither%20were%20there%20displays%20for%20it.%20Few%20had%20hopes%20of%20ever%20transmitting%20it.%20Through%20to%201984%20it%20was%20driven%20fundamentally%20by%20this%20desire%20to%20have%20one%20electronic%20production%20standard%20that%20would%20downconvert%20with%20equal%20ease%20to%20the%20existing%20transmission%20standards.%20Finally%20NHK%20developed%20a%20HD%20satellite%20broadcast%20system%20in%201985%2C%20intended%20to%20be%20used%20for%20the%20launch%20of%20HDTV%20satellite%20services%20in%20Japan.%20That%20transmission%20system%2C%20they%20once%20thought%2C%20would%20be%20sought%20after%20around%20the%20world%20and%20their%20own%20beginnings%20in%20Japan%20would%20mean%20a%20lower%20cost%20of%20introduction%20elsewhere.%20Japan%20would%20dominate%20the%20information%20age%20with%20the%20ultimate%20information%20appliance.%0A&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p><em>This interview was conducted in 1998. It was widely published and is brought to you now in hopes of advancing your insight into the HDTV movement.</em></p>

<p><strong>An Interview with Dale E. Cripps, HDTV Pioneer</strong></p>

<p>Mr.Cripps has been active in the HDTV arena each and every day for the past 17 years. He is one among many directly responsible for HDTV in America, and the world. He is the founder and President of Advanced Television Publishing. We recently sat down with him for this exclusive interview.</p>

<p><br />
<strong>HDTV Magazine: What was the original goal of HDTV?</strong></p>

<p>Dale Cripps: The original goal of HDTV was to create a worldwide electronic production standard competitive to 35mm film. The goal soon became one to create a new experience for the home--the next generation of television. Test in Japan determined that a 30 degree field of view and the 5.1 audio system made for a dramatically new experience. To do that visually, and without artifacts, you would need about 2 million pixels in a wide shape (later became 16:9).</p>

<p>In the beginning HD was not recordable. Neither were there displays for it. Few had hopes of ever transmitting it. Through to 1984 it was driven fundamentally by this desire to have one electronic production standard that would downconvert with equal ease to the existing transmission standards. Finally NHK developed a HD satellite broadcast system in 1985, intended to be used for the launch of HDTV satellite services in Japan. That transmission system, they once thought, would be sought after around the world and their own beginnings in Japan would mean a lower cost of introduction elsewhere. Japan would dominate the information age with the ultimate information appliance.</p>

<p>A reaction to NHK's transmission system set in around mid-1986. A fear that free-over-the-air broadcasting could be overwhelmed by the popularity of a satellite or cable based HDTV service caused the NAB and others to petition the FCC. The NAB said in the petition that free broadcasting--the backbone of democracy--was facing a new threat, and they had to react by becoming technically and spectrum-ready. HDTV was coincidentally a wonderful excuse for asking the FCC to protect broadcast spectrum--freeze it from further allocations to anyone until there was an complete answer as to how much spectrum would be needed for HDTV. To a broadcaster in the late 80s there was nothing more valuable than their spectrum. HDTV fought off the challengers who had at that time had powerfullly risen to request broadcast spectrum. Saving spectrum, of course, was not its original goal. Many sub-plots have been added to the goal-line of HDTV that have nothing to do with the real goals that must in the end lead it.</p>

<p>In fact, the real goal for HDTV has been articulated by only a few to date. Since there is no clear business advantage for broadcasters to pioneer HDTV signals, it has been difficult for anyone of high standing to jump up and shout the good news that HDTV has finally arrived. If you give HDTV the stature of being the next generation of television you would think that everyone associated with television would need to be out their identifying with the new product. But they are not. Indeed, Jay Leno makes jokes about it, if he mentions it at all...and he is the only person being telecast in HDTV every weekday night. </p>

<p>I think the goal of manufacturers is to get HDTV into every part of the world where it will fit in. In my view the best reason to do that is because HDTV raises the standard of living for those who acquire it. I am sure most of the readers of the HDTV Magazine will agree that is its finest pay-off.</p>

<p>We are lucky that we had a pragmatic Trojan Horses--like saving spectrum--to drive the technical side. We are lucky that tax breaks were granted to networks in New York to insure the Big Apple had a hand in rolling out the next generation of TV (rather than New Jersey). We are lucky to have a good economy during the introductory period. All of these things, plus a creative renaissance, tell me we are indeed going to improve the standard of living, and to me that is the real, and too often, understated goal of HDTV."</p>

<p><strong>What will be the outcome of the current COFDM vs. 8-VSB issue?</strong></p>

<p>I wish I could tell you definitively. But I cannot. The outcome is beginning to take shape. Sinclair has courageously focused attention on a real problem. The performance of early 8-VSB receivers did not do justice to the standard. Some say that it is the standard that does not do justice to the needs of modern broadcasters. In comparative tests--some called them demonstrations--the COFDM receivers appeared superior for indoor reception to the 8-VSB receivers. Sinclair has a lot of inner city viewers. They were concerned that any tedious rotating of an outdoor antenna would slow the transition. That could impact their negotiations for carriage on cable systems, among other things. Dynamic multipath--called ghosts in standard TV--is harder for 8-VSB to handle. The decoder can be left not knowing if it is decoding the ghost or the main signal and crashes, leaving no picture. It is also highly directional. Most who subscribe to this publication may have fiddled with their antennas when acquiring their HDTV receivers. Being early adopters and problem solvers you likely overcame most of these annoyances with ease. Indeed, most places work just fine with 8-VSB. It is only that some places where COFDM is receivable, the 8-VSB has trouble, And none of what I am saying is definitive. Many groups are working on new algorithms for new chips and fine tuning with software existing chips to greatly improve this multipath performance. The COFDM may also find use in mobile applications where the 8-VSB simply cannot today. The 8-VSB is said superior over COFDM on reach--how far a usable signals travels. It is also more "robust" in handling impulse noise. Solving either reach or impulse noise might raise the cost of power for a COFDM choice. Of course, there are two sides to all of the claims with each saying their weaknesses are not so weak.</p>

<p>The two systems do have their respective strengths. So it's always a trade off. The unknowns are those things still left to be invented, but could one day they will be invented to solve all shortcomings and even extend the virtues they have. When I first began researching HDTV in 1984 Larry Thorpe from Sony carefully explained to me that while there were cameras, and there were monitors, there simply was no way to transmit HDTV. That has certainly changed, and to think that the multipath problem 8-VSB suffers with cannot be overcome with increased technical knowledge is faithless non-sense. The question is: how long will that take? That is what Sinclair and others are asking. So far the answers have not been so clear and those things put out as answers, disappointing.</p>

<p>To get more answers the FCC has decided to oversee independent side-by-side tests using the latest 8-VSB and COFDM receivers. The reports on that will likely set the stage for a decision that will settle the industry down. Those of you who have purchased an 8-VSB receiver will not lose your investment. It has already been discussed that should a new mandate be made which in any way obsoletes your receivers, the manufacturers will trade them in for whatever new is selected. This is not the best of situations, but the public relations resulting from not doing that is too horrible to contemplate.</p>

<p>Dr. Joe Flaherty--the god-father of HDTV from CBS, said in a recent interview with me that, unlike the 50 year reign of analog, this round of digital devices and solutions may not last much more than ten or fifteen years. New things will be introduced, and their proponents will bang at the door of signal providers and the FCC as well as exciting the public about their new gadgets. The new technology will leap frog what is adopted now, using for their introduction the current analog channels being returned to the FCC by broadcasters. These always-newer upgrades--mini-revolutions--will dazzle us in an era of global interconnected commerce and electro-social articulation in such ways that are hard to forecast today. Science is not sitting still, however, and applications of new developments are not slowing. Some will be attractive enough to move us to discard the old decoder box of today into the kid's room, the spare bedroom, the kitchen, and leave room for the next latest and greatest in our media rooms. The display will likely be the most stable of the components we will be acquiring over the next ten years. But efficiency in how that picture is created and distributed will certainly continue moving forward.</p>

<p>I do wish I could answer your question for the readers. I do hope we end this controversy soon over the transmission standard. This limbo period of doubt is no good. Brazil just issued their report on the modulations scheme--again a negative one for 8-VSB. They said they would go with COFDM. That will likely topple other nations on the fence like dominos. So, as I talk to you right now, I would have to say that 8-VSB is on the ropes in round 5. Maybe that is what it takes to pull all of the industry together and fix the standard to the extent that it at least meets the competition in multipath handling. Only when all doubts are removed throughout the industry will there be enough strength in the HDTV movement to overcome the gravity of NTSC. Right now HDTV is a little wart on a very big and powerful industry. That wart can be pealed off by most-anyone. A recent article in Forbes has already declared the DTV launch a failure. Forbes is a little premature, though they are not the first to say that our just-born baby should already be competing in the main Olympics. I would think that diapers should at least be removed before we start judging the career of this gifted prodigy.</p>

<p><br />
<strong>Do I think HDTV will be the end-game of this digital transition? </strong></p>

<p>In the long run, yes. I am far less certain in the interim. If we suddenly did away with all NTSC manufacturing, and only offered HDTV, then we would be in a hard transition. But we may not be making a transition at all. That could be the biggest myth we have circulating in the industry today. We could be making just a new business--one that is at the top of a pyramid and does not require the other to go away. I think we would profit by a debate as to whether we are transitioning by way of a transformation from NTSC to DTV, or whether we are giving birth to a new and independent business attracting its own distinct audience somewhere at the top?"</p>

<p><strong>What is your best advice to the current owners of HDTV?</strong></p>

<p>The best advice I can give them is to stay tuned to The HDTV Magazine, to enjoy HDTV with every opportunity they can, and to share their discovery with others.</p>

<p>In effect every early adopter is a salesperson for HDTV. The only way programming will increase is to have more receivers in use. Signal providers will not serve a dead-end street." </p>

<p><strong>What would be your advice to the industry?</strong><br />
To the professionals I say, "Get courageous. Be ready for the unorthodox since no old rules are reliable." I would also urge the end of any bickering that flared up between competing factions of manufacturers and broadcasters. If we are going to change modulation schemes, something I worry over because of the time element and the cost of money and reputation to those who have invested in it, I would urge that it be done as fast as is possible with an absolute minimum of congestion at the FCC. If we are to stay the course with 8-VSB, make that utterly definitive and collaborate to find new answers for better performance and set industry standards for receiver performance.</p>

<p>I also urge a search for a new rallying point that will give everyone a reason to act upon their own HDTV initiatives with renewed confidence adn vigor. Alan Greenspan said today that it is stability that engenders growth. I think that stability and vision are essential for HDTV to move forward. Both have been grievously missing for years. We have to have stability first in the standards. We can't start and stop, and start again, and expect the public, much less the retailers, to have any confidence in this product.</p>

<p>Then we need to revisit why we should do it in the first place. It is not enough to just say, "Oh, the people will love it when and if they ever see it,...and all the stations will want to deliver wonderful HDTV programs." Most of them upconvert from standard television and have little plans for more until there is a larger installed base of HDTV receivers. That may take ten years to develop. Far too much wishful thinking has prevailed for this large of a gamble. There are billions upon billions on the table!</p>

<p>Even though there are 119 stations on the air (pumping out upconverted NTSC and a little HD), I still urge the formation of a collective enterprise made up from the whole industry, hardware, software, signal providers, retailers--a for-profit entity owned by all these stakeholders in HDTV. This is a company that has the unremitting mission to pioneer to profitably the HD services to the early markets. I will talk more on this at a later date.</p>

<p>I know many think that to even discuss HDTV in this more enlightened era of digital connectivity is somehow missing the point. They say that digital is any and everything--resolution independent--the convergence of all things. They say to even use the term HDTV means you are a throwback to earlier times which are gone forever.</p>

<p>I have no doubt about convergence--the flexible functions in the digital age are clearly approaching. What I have doubt about is whether that fact negates the act of enjoying a television program--a movie, a special, a sporting event? Am I missing something by believing that people will for generations to come sit in front of their large screen television appliances watching beautiful pictures, hearing glorious sound, and absorbing a moving story? Will instead everyone be clicking on Yahoo, AOL, or eBay? Will the HDTV experience disappear? Let me be one to say absolutely not. It is here to stay.</p>

<p><strong> Thank you Mr. Cripps.</strong><br />
</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Dale Cripps</b>, <b>June 15, 2005 11:59 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 80
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
 				AND entry_id <> 80
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/interviews/2005/06/an_interview_with_dale_e_cripps_hdtv_pioneer_-_1998.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
