<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 102";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Dale Cripps'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 102 AND placement_is_primary = 1";
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
	<meta name="keywords" content="sam runco, line doubler, home theater, long time, could get, HDTV, hdtv, going, runco, Runco, get, people, hdtvmagazine, HDTVMagazine, because, good, been, could, time, years, better, Sam, line, sam, industry" />
	<meta name="description" content="Sam Runco President, Runco International With Dale E. Cripps HDTV Magazine 1998 &quot;The approach I prefer to take is that no matter what the problems of HDTV are, I'm happy it's here.&quot; -Sam Runco &quot;If we could just get the..." />
	<title>HDTV Magazine Interviews - The Legendary Sam Runco - 1998</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/the_legendary_sam_runco_-_1998';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('The Legendary Sam Runco - 1998'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/history/interviews/2005/06/the_legendary_sam_runco_-_1998.php";
		if ($author[img] != '' && 4 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Dale Cripps" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">The Legendary Sam Runco - 1998</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Dale Cripps</b><br />
				<?=$author_title?>
				Posted on <b>June 20, 2005</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category="></a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/interviews/2005/06/the_legendary_sam_runco_-_1998.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/history/interviews/2005/06/the_legendary_sam_runco_-_1998.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/history/interviews/2005/06/the_legendary_sam_runco_-_1998.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/interviews/2005/06/the_legendary_sam_runco_-_1998.php&amp;phase=2&amp;title=The%20Legendary%20Sam%20Runco%20-%201998&amp;bodytext=Sam%20Runco%20President%2C%20Runco%20International%20With%20Dale%20E.%20Cripps%20HDTV%20Magazine%201998%20%22The%20approach%20I%20prefer%20to%20take%20is%20that%20no%20matter%20what%20the%20problems%20of%20HDTV%20are%2C%20I%27m%20happy%20it%27s%20here.%22%20-Sam%20Runco%20%22If%20we%20could%20just%20get%20the...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p>Sam Runco<br />
President, Runco International</p>

<p>With Dale E. Cripps</p>

<p>HDTV Magazine<br />
1998 </p>

<p>"The approach I prefer to take is that no matter what the problems of HDTV are, I'm happy it's here." -Sam Runco</p>

<p>"If we could just get the world to realize that television did not go up in price." -Sam Runco</p>

<p><br />
Runco International was founded by Sam and Lori Runco in 1987. Sam Runco has been an innovator in the video projection business since the early 1970's, when his projectors first appeared with the Runco name. The new company was the first to coin the term "home theater," and promptly received a trademark for it from the state of California.  </p>

<p>In 1989, Runco introduced the CinemaPro 600 video projector, replacing the CinemaBeam product line that launched the company.  </p>

<p>The CinemaPro 600 was a major success for Runco and found its way into homes, nightclubs and bars throughout America. This product helped establish Runco as a force in the video projection marketplace.</p>

<p>Early in 1991, Runco expanded its product line with the introduction of the original Super IDTV (Improved Definition Television) system. This consisted of the IDP -800 projector mated to the SC-1050 line doubler. This was a revolutionary new product for the industry.  </p>

<p>This combination also marked Runco's entrance into the high-end home theater video market, a new market segment that Runco created with the Super IDTV, a market Runco has lead. For the first time, consumers could obtain a home video projection system capable of reproducing images with film like quality. </p>

<p>In 1992, Runco pioneered yet another industry first, the ARC IV Aspect Ratio Controller, the first of its kind for use with the Super IDTV system. </p>

<p>This and other technological innovations since secured Runco's position as industry leader in state-of-the-art video reproduction for the home.  </p>

<p> <br />
 In 1997 Runco improved his latest IDP-850 by designing and building an internal line doubler card. Dubbed the DTV-852 for its digital and HDTV capabilities, this projector was the first CRT projector to reach the market with a built-in line doubler. </p>

<p><br />
Sam Runco: My position is from experience. The most I can do is hope to drive someone who is reading this into a store-to have the experience. What will they get when they go into the store? That's up to the sales people. One of the things I'm working on is trying to find sales people to train. Show them HDTV, but also show them what they're going to be watching for the next few years. Spend your time on widescreen DVD. Sit back and enjoy the 5.1 Dolby digital audio. - Sam Runco</p>

<p><strong>HDTVMagazine: If you were king engineering and controling the roll-out of HDTV, would it be as it is with broadcasters leading the way? Or, would you do something else?</strong></p>

<p>Sam Runco: Do you mind if I back into that question? I'll tell you why, Dale. I'm usually pretty impromptu when I deal with things from my experience rather than from selected events. If we talked a little bit about my experience we could decide what I would do if I were king.</p>

<p>First, I've been watching the approach of HDTV for a long time. I won't go into what is right or wrong about the system-the 18 formats, the FCC, the grand alliance, or CEMA.</p>

<p>The approach I prefer to take is that no matter what the problems of HDTV are, I'm happy it's here. Whether it's 1080i, or 720p, or even a couple of added new ones in the next few years. Without a doubt  this is an evolutionary process. While many people will deny that. both the computer and the motion picture companies are strong and will flex their muscle over the next year or two. There will be some added formats. </p>

<p>Will they make the picture better? That's questionable. HDTV is such a jump over NTSC that when someone argues over 720p and 1080i it's a moot point. They're both so damn good in reference to the NTSC system that it just doesn't matter. </p>

<p>It may matter, however, after we get used to watching it for a year or two. Then we can get a little tweaky on it, adjusting it a little bit here and there. Some people who pick on it now-both the 1080i and the 720p. There are people who claim you're not going to be able to get good sports because of the motion, and so on. Again, I am just happy to be here-happy that it's around.  </p>

<p>The presentations that have been done so (for HDTV) get nothing but negative press. A couple of things are bringing that about. </p>

<p>First, the experience of HDTV cannot be experienced from a written article. It can't come from broadcast news on an NTSC set. All you can do is talk about what it looked like in print (like what we're doing now). You could see HDTV on a news broadcast, but the quality is only as good as is the particular device it's being displayed on.</p>

<p><strong>HDTVMagazine: It's trying to explain sex, right?</strong></p>

<p>Exactly the same thing. The most we can sell through the media that is available to us is an appointment-get the individual into a store and into a position where they can experience HDTV, along with the big sound. (Fortunately for us the 5.1 digital audio is one of those things that's part of the system.) Then they can have that experience. From that point on their level of entertainment, their level of quality is going to drastically change. Once they've done that, they are never going to want to watch things the way they were before.</p>

<p><strong>HDTVMagazine: That's what happened to me! </strong></p>

<p>It is an experience, and like most experiences, it has to be experienced. It can't be synthesized. As an industry we're coming in and trying to drop a bomb on top of them now. The media is reacting to that bomb. Out here in San Francisco there was an article called "Who Cares TV", and "HDTV is Finally Here, and Nobody Gives a Damn." These are the headings for the articles that are coming out in Silicon Valley, of all places! </p>

<p><strong>HDTVMagazine: The Japanese have been asking me from NHK, "What is going on over here? People seem to be reacting negatively."</strong></p>

<p>The whole industry can profit from the experiences of a few companies. One of those companies is Runco. What I'm going to tell you, Dale, is going to sound like a sales pitch  </p>

<p><strong>HDTVMagazine: Sell it, Sam!</strong></p>

<p>Don't take it as that. Let's excerpt the information from the sales pitch, because there's no other way to explain it. </p>

<p>We introduced to the home theater industry in 1989 the line-doubler. Faroudja came out in 1991 with the LD100. He built it for the broadcast industry then found that, "Gee, it's starting to sell into the home theater industry." It was 98% of Faroudja's business last year-these kinds of products. Runco is still the premier company, and that's maybe bragging, but it happens to be so.</p>

<p><strong>HDTVMagazine: Keep bragging!</strong></p>

<p>Because we are, we have a pool of information. The information is that in 1991 we came out with a controller. From that time until now we've been selling widescreen television. We have a lot of widescreens in place. We have been selling the improved definition television to people for a long time-almost a decade. As a matter of fact, without the widescreen, it's long than that.</p>

<p><strong>HDTVMagazine: Could you get any of those customers to go back to whatever they came from?</strong></p>

<p>Those customers are spoiled. In many cases, Dale. In the last 8 or 9 years a Runco customer may have, and in many cases definitely has,  purchased 2, 3, or 4 models of our products. It is sometimes to replace the last one. I find that unbelievable, but they do. </p>

<p>Once they get used to watching a football game using a good, data-grade quality projector-one that can do the proper spot-size and has a processor...and we all know the processors have gotten better and better and better...they don't go back. When Faroudja introduced the LD100, it was the reference standard because of the decoder and the 3:2 pull down feature. Well, in the last year the scalers with 3:2 pull down in component input have made an advancement over that particular piece. In those days we didn't think it could get any better. Now that old one looks like it's broken. It's amazing.</p>

<p>You look at a good processed picture-a good picture on an 8 or a 9 inch tube using full-scale tripling with an anamorphic 16 by 9 picture (and assuming that the software is as good as it can be), it's terrific.</p>

<p>We have been creating really good pictures for a long long time. People have been enjoying them. They enjoy them to the point that the don't enjoy themselves when they have to watch something else. </p>

<p>Now comes the HDTV experience. It looks like it's going to be a while before this stuff hits (unless we handle it right). By the way, I have to preface this with this: I'm really a proponent. I'm high on HDTV. . That's important for me to say. Some of my (following) statements are going to sound like I'm not. They're (made) only because I'm dealing with what I believe is the reality of the moment, which is pushing me in the direction I'm going. </p>

<p>I think in order to sell HDTV-to get it accepted-the first thing is to get high-definition devices into the home. What I'm noticing is a reluctance on the buyer's part to purchase the set because he's afraid it's going to be obsolete by the time the signals hit it. That is because the industry really can't decide what the hell it wants.</p>

<p><strong>HDTVMagazine: It's a big problem, Sam.</strong></p>

<p>It's a monster problem. The companies that may look like they're progressive-the companies building the receivers-decoders into the sets-are the ones that are going to come to the biggest harm. Why?  They are not going to be upgradable or expandable. There are companies that are using component, or RGB inputs that have capabilities of a minimum of 1080i. They stand to be in good shape, assuming that they can deliver good process video right now. That will cause someone to purchase the set based on the quality of what that picture is right NOW. "Oh, by the way," you say to them, "when HDTV finally broadcast en masse, this thing is going to be able to do it. Don't worry about the thing being out-dated." </p>

<p>The way of getting people to move off the dime is to have them realize that the thing is worth the $5 or $10 thousand NOW.  HDTV is free later. That is a better approach than trying to sell them HDTV (now). </p>

<p><strong>HDTVMagazine: In other words, the improvements in an HDTV monitor over existing sets for existing signals is worth it alone. </strong></p>

<p>It is worth it alone. </p>

<p><strong>HDTVMagazine: And you say that with ample evidence.</strong></p>

<p>I stand here with all the evidence in the world that it is true! The argument that could come from outside Runco might be, "Yeah, well all your buyers are people that have so much money they don't really care." That's not the way it works. </p>

<p>When I started selling expensive equipment -I can't call it high-end because I never believed I was in the high-end business. I believed that the stuff I was selling just cost a lot of money. In other words, I never paired a Runco projector, for instance, with Wilson speakers. I always felt that Wilson can do what they're doing for a long, long time. Krell can do what they're doing for a long, long time. But the day that someone comes up with better picture than mine for $5,000, I'm in trouble. There's no fancy box or container-no slick gizmo that I could produce to allow me to sell mine for $50, 000 if the picture is better (from someone else) for $5,000. </p>

<p>Visuals are different than audios. There is a lot of subjectivity in audio. You can install a brand new surround-sound system into a home. The center channel could have two mid-ranges-one of them could be out. One of the two sub-woofers could be out. The tweeter in the surround could be out. No one would give a damn, or able to tell. Yet, if you see a phosphor burn on the TV in the right hand corner, I get a call the next day. You don't have to be a genius. The salesman cannot convince you that the spot is not there! But I have seen that happen in audio. </p>

<p><strong>HDTVMagazine: Some of the early pay-per-view were in letter-box which resulted in a lot of complaints.</strong></p>

<p>Oh, boy, I'll bet. "Where's the rest of my picture!"</p>

<p>My position is this: "Hey, this stuff I'm selling is really high technology. In order to get these kind of pictures right now, they cost a lot of money."</p>

<p>When I first started I put a line doubler along with a projector for the home theater industry. My reason for doing that, Dale, was because I thought I was going to sell a lot of $5,000 projectors by having a flag-ship line. What happened? I ended up selling more $15,000 projectors than $5,000 projectors.</p>

<p><strong>HDTVMagazine: How did that happen?</strong></p>

<p>Whatever it was, I'm glad it happened! And, it keeps on going up. When Faroudja came out with his processor I had a couple of competitors. Up until that time, I didn't. For two years it was a free market for me. Then the competitors came out. What happened? Because the Faroudja processor was so expensive nobody worried about paying $20, $25, or even $30,000 for a projector. The bar raised again. </p>

<p>I found myself dealing with very, very wealthy people. The misnomer is that rich people have so much money they don't care what they do with it. They throw it around. "They're stupid." Someone who says that doesn't realize that the words "rich" and "stupid" just don't go together. </p>

<p>For years I've heard people say, "This guy is such a good customer. He just signs a blank check." Well, I have never seen anybody do that. It just doesn't happen. What I've observed, and it certainly is the rule rather than the exception, is that when a person is wealthy they'll pay more. Why? Because they know they're buying your soul. The type of people buying Runco projectors are buying cutting-edge technology. They are also buying the dealer. The dealer may think, "Wow, am I going to make a fortune from this guy." But they find out that, "Wow, this guy owns me." The people who have been successful in the business are the ones who realize, "Yes, I'm for sale". </p>

<p>My position as a manufacturer has always been-my soul belongs to the dealers-the dealers' souls belong to the end users. Our job is  service. We're absolute service. But while we were dealing with them we learned they pay for technology and enjoy it. Their feedback was the most objective that you could get. Now, that's an arguable statement, because someone would say, "Just because a guy is richer doesn't mean that he's smarter. It may not mean that he's smarter, but he's probably more objective, because to get where he is...</p>

<p><strong>HDTVMagazine: ...you don't make uneducated and emotional decisions.</strong></p>

<p>Yes. He had all the pieces to his puzzle together. </p>

<p>The feedback we got  was very, very good. Now these guys (our customers) are elated, because more than anything nobody likes to get screwed. The fact that they bought something in 1991 that they now can plug an HDTV receiver/decoder into (even if they don't particularly like the projector since there are better ones), makes them feel really good. But the big thing is that these people have been watching good pictures for a lot of years, and now HDTV comes along as a bonus. </p>

<p>If we could just get the world to realize that television did not go up in price. What happened is that improved definition television came down in price. It finally came down to meet the needs of the upper end of the main-stream buyer.  </p>

<p>When I'm saying television, I'm referring to the Mitsubishis, Toshibas, Sharps, Panasonic, Sonys-the companies that are out there who have real HDTV. The sets are worth a lot more money than the price tag just for the improved definition (they deliver). I can now watch a football game, and it really looks. It feels like I'm on the field. Better yet, I can put a movie on and watch it in widescreen and have my whole family enjoy it. I am finally able to afford something that, up-until-now only Bill Gates and Paul Allens could afford.</p>

<p>I don't think we're getting that message across. What we're getting across, is: "Here's HDTV. Oh, by-the-way, there is anything on (in HDTV formats). Oh, by-the-way, there are fights over standards. This may end-up being nothing. If you dish out $6,000 or $8,000, this thing is going to become obsolete.: </p>

<p>That is the message we are (the industry and press) sending out today. The customer says, "I'm screwed." </p>

<p><strong>HDTVMagazine: The low-cost way for entering into what has heretofore been a very expensive market is with today's HDTV?. </strong></p>

<p>Yes. I wanted to let you know my experience so that you could pull from that. You're the writer. What I feel that I've given you is some strong, hard experiential information</p>

<p><strong>HDTVMagazine: Now another big question: HDTV was designed when realizable improvements to NTSC were modest. What everyone (in the 70s and 80s) said we needed to succeed in a new product category (HDTV) was a 10 JND (a scale of Just Noticeable Difference units) improvement. That is what led to the twice vertical, twice horizontal resolution conclusions. Considering we have DvD, satellite and digital cable, the gap perceptual seems to have closed to less than 10 JND. Do we have enough improvements between today's state-of-the-art and the state-of-the-art in HDTV to go forward successfully?</strong></p>

<p>We had the Runco line with 1050 (line doubled) from 1989 to 1991. It wasn't the best line doubler in the world. It was the only line doubler in the world! Faroudja came out in 1991 with the LD100. By comparison it just wowed you. Now let's compare the LD100 to the Snell&Wilcox (pixel) interpolator. All of a sudden there's another jump-wow. Look at the difference in quality.  </p>

<p>There is still more to be squeezed out of the NTSC system from the display devices and processors. We're getting pretty close to perfect. What we're not perfect on doing yet, and this will tally into HDTV too-we're not pulling the full quality out of the film-to-video transfers. There are some good colorists putting out great transfers, but up until now I ask you, what has the main market been for a transfer from film to video? It's been VHS. Why should they go through all the trouble of making sure that they color it correct, and that the focus is correct on each one of those frames when it's going to be performing at 220 lines? Now that the medium's getting higher they're starting to pay attention. The DVD's are getting better, and better.</p>

<p><strong>HDTVMagazine:</strong> Bob Hopkins (Vice-president of Sony High-definition Facility in Hollywood) said that everything that's ending up  up as a DVD from Sony Pictures was first transferred as HDTV. Is that helping?</p>

<p>That's fabulous. Now, in answer to your question: There's nothing that can replace true resolution. We can fabricate. We can manufacture, interpolate, and do all the other things to bring out the picture. We've come to the point where we do pixel-by-pixel interpolation instead of just line by line. This stuff has gotten really sophisticated. But it's a manufactured pixels . It's not a real one. When next to the best IDTV, HDTV is still going to show noticeable improvement. Television has 50 years with the NTSC system. It wasn't designed to do any of the stuff we're doing with it right now. Considering what we've squeezed out of it, and you mentioned Yves-what Yves has done to squeeze out of it is phenomenal! </p>

<p>It's time for a change. I'd probably sit here and say exactly  the same thing about the film industry. A hundred years! How many things in a hundred years? Wouldn't the computer industry love to get just one tenth of that! A hundred years of film... It's time for a change. I think that HDTV is very, very welcome right now. When things start to get broadcast in HDTV-when people begin to see.... </p>

<p>Let's back up a bit. Digital TV. You know, you've heard the statement that "HDTV is digital. But digital is not necessarily HDTV." Digital TV along  with HDTV is going to bring a whole new world from the television side simply because there's no bad signals. Now in some cases there may be NO signals, but in the stream it's going to be perfect. There's going to be no bad ones anymore! That's going to bring a whole new world. Yeah, I think it's necessary, I think it's time for a change.</p>

<p><strong>HDTVMagazine: When I entered this field I read one of these classic overviews of manufacturing. I learned that whenever there is a new product on the horizon that is ready to supplant the position of an old one, the old suddenly goes into a vigorous animation in a vain attempt to look like the new and strive to reach the potential of the new. They always fall short and fail. The new takes off and takes over.</strong></p>

<p>Yes, and that is exactly what is happening. There are a couple of things that are going on. Right now, the broadcasters are doubting the time lines. I sat on a panel the other day in San Antonio. I was with a number of people, including Charles Pantusa (HD Vision, Dallas, Texas), whom you know.</p>

<p><strong>HDTVMagazine: Yes, for years.</strong></p>

<p>We listened to one of the speakers from the local television stations. The next one was from Paragon Cable (the cable network in San Antonio). Then we heard from a representative from Unity Motion. It was a great panel. One thing I got out of it is that they all believe the move to HDTV isn't really going to take off for a lot of years. I don't agree. Why do they think that? They're all looking at it as being systematic, like it's going to take so long to sell so many of these (HDTV receiver) this way, and then we're going to get this into the market at this time, and so on, and so forth. </p>

<p>It doesn't work that way. Supply and demand suggest that if we get the hardware in the homes, and the broadcasters have someone to broadcast to, they will start to broadcast. It's the thing that's going to make them money. It's the chicken and the egg, of course. The whole idea is to make sure that we can get that wheel spinning. If the customers started to demand it, the broadcasters would have this stuff up and running in six months!</p>

<p><strong>HDTVMagazine: This is part of the background of my question: If you were king would you continue urging broadcasters to pioneer the business? Or, is there another way to pioneer the business?  </strong></p>

<p>Of the many things I've been called over the years (and I've been called a few nasty ones), I certainly have been called a pioneer. We at Runco have done the pioneering for this kind of thing (Sam coined the term 'Home Theater'-Ed). I see that the solution is to get the display devices into homes. How do we get them into homes? Well, that's the whole story I just told.</p>

<p><strong>HDTVMagazine: Let me sum it up, Sam. You said, "Hey, you can buy a new breed of television. It's a terrific value because it makes the existing signals look great. AND, you have the bonus that when HDTV signals come, you get that too." That's the right story?</strong><br />
Yes. You don't have to wait for anything.</p>

<p><strong>HDTVMagazine: And so, there's the motive. Everybody's got a signal already. You don't have wait for any signals, it's already better. Did you make that point in the panel?</strong></p>

<p>I made that point. We had about a hundred and twenty five in each session. You can always count on there being a bunch of baby-boomers there. Baby-boomers are a big part of my business. Since I'm one, I can pick on them. The way I approached it was to talk about the baby-boomers after World War II. Our parents were different than we were. For one thing, they were totally unselfish,. All they cared about was us. They worked and lived for us. To do right by them we carried on the tradition by being selfish and taking care of ourselves, just the way they took care of us. We were all liberals in the 60's-hippies, and using all kinds of drugs, and we never thought we'd have two nickels to rub together. All of a sudden we find that we turned 30, 40, and now 50, and we actually have some money. What do you know, we turn Republican. It's a shock. A lot of these people in the audience were shocked because they knew it was true! You know the guys that were yelling and screaming, "Ban the bomb; free sex; blah blah blah..." Well, you're Republicans! What can I tell you, I said, that's the local customer.</p>

<p>What I was leading to is this: If you're waiting for HDTV, you got one reason not to wait. You ain't got much years left. You better buy something now, because you're not going to be able to see soon. I use that approach to lead into the. "Hey guys, this is a faster way. If you can get good pictures now, take a look at what's out there-go see what they're doing", I mean the Mitsubishi processor (in their HDTV) looks real good. The processors that are out there are not perfect-they're not Faroudja quality-but they are on the market already. You can go into one of these stores today and buy one of these things and go home with something that's really nice for a few dollars. I think that's the point that we have to drive home. That's what I was driving home at that conference.</p>

<p>My position is from experience. The most I can do is hope to drive someone who is reading this into a store-to have the experience. What will they get when they go into the store? That's up to the sales people. One of the things I'm working on is trying to find sales people to train. Show them HDTV, but also show them what they're going to be watching for the next few years. Spend your time on widescreen DVD. Sit back and enjoy the 5.1 Dolby digital audio. If you really want to trick them, throw an HDTV picture in the middle of the demo and see if they can pick out which part was in HDTV. In many cases they are not going to do that. </p>

<p>Mitsubishi has come up with the Sencor unit. (the Sencor hard drive system is used for providing demo material where there are no other signals available-ed). The dealers are starting to use these systems, and they're doing HDTV demos. Now that's great, but if they limit their demos to just that bit of HDTV footage the people are going to walk out with the same attitude that we just talked about here. Show them the TV in the way it's supposed to be used, and then cap it with an HDTV demo.</p>

<p><strong>HDTVMagazine: Thank you Sam.</strong></p>
		</div>
	</div>
	<p class="posted">Posted by <b>Dale Cripps</b>, <b>June 20, 2005 12:56 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 102
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
 				AND entry_id <> 102
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/interviews/2005/06/the_legendary_sam_runco_-_1998.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
