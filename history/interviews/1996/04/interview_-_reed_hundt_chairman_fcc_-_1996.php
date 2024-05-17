<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 115";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Dale Cripps'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 115 AND placement_is_primary = 1";
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
	<meta name="keywords" content="high definition, reed hundt, digital television, make sure, transmission standard, standard, industry, think, make, get, broadcasters, FCC, want, fcc, any, say, should, chairman, Hundt, Chairman, hundt, been, government, congress, Congress" />
	<meta name="description" content="I conducted this interview April 4, 1996 and it was first published in the HDTV Newsletter. _Dale Cripps Chairman Reed Hundt was guided by two principles: first, that the FCC should make decisions based on the public interest and second..." />
	<title>HDTV Magazine Interviews - INTERVIEW - Reed Hundt, Chairman, FCC - 1996</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/interview_-_reed_hundt_chairman_fcc_-_1996';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('INTERVIEW - Reed Hundt, Chairman, FCC - 1996'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/history/interviews/1996/04/interview_-_reed_hundt_chairman_fcc_-_1996.php";
		if ($author[img] != '' && 4 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Dale Cripps" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">INTERVIEW - Reed Hundt, Chairman, FCC - 1996</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Dale Cripps</b><br />
				<?=$author_title?>
				Posted on <b>April  4, 1996</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category="></a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/interviews/1996/04/interview_-_reed_hundt_chairman_fcc_-_1996.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/history/interviews/1996/04/interview_-_reed_hundt_chairman_fcc_-_1996.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/history/interviews/1996/04/interview_-_reed_hundt_chairman_fcc_-_1996.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/interviews/1996/04/interview_-_reed_hundt_chairman_fcc_-_1996.php&amp;phase=2&amp;title=INTERVIEW%20-%20Reed%20Hundt%2C%20Chairman%2C%20FCC%20-%201996&amp;bodytext=I%20conducted%20this%20interview%20April%204%2C%201996%20and%20it%20was%20first%20published%20in%20the%20HDTV%20Newsletter.%20_Dale%20Cripps%20Chairman%20Reed%20Hundt%20was%20guided%20by%20two%20principles%3A%20first%2C%20that%20the%20FCC%20should%20make%20decisions%20based%20on%20the%20public%20interest%20and%20second...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p>I conducted this interview April 4, 1996 and it was first published in the HDTV Newsletter. _Dale Cripps</p>

<p><em>Chairman Reed Hundt was guided by two principles: first, that the FCC should make decisions based on the public interest and second that the FCC should write fair rules of competition for the communications sector.  </p>

<p>Under his leadership the FCC conducted the first spectrum auction in U.S. history. In its first two years of auction authority the agency raised almost $20 billion for the national treasury.  </p>

<p>Chairman Hundt was the first to make himself accessible to a wide audience by participating in open, online conversations with the public and was the first FCC Chairman to have a personal computer on his desk and to be connected to an electronic network. </p>

<p>Before becoming Chairman Hundt was a partner in the Washington office of Latham & Watkins, a national and international law firm. His work included legal and regulatory issues in emerging technologies, such as cellular telephones, direct broadcast satellite, and interactive television. </p>

<p>He is a graduate of Yale College (1969) and Yale Law School (1974), where he was a member of the board of the Yale Law Journal. He clerked for the late Chief Judge Harrison L. Winter of the United States Court of Appeals for the Fourth Circuit, and is a member of the District of Columbia, Maryland, and California bars. </p>

<p>Hundt was named Chairman of the FCC by President Clinton and was sworn in by Vice President Gore on November 29, 1993.</em></p>

<p>***************************************************</p>

<p>The ATSC standard had been handed up to Reed Hundt six months prior to this interview. The industry was growing nervous and concerned. They wanted it voted upon by the Commission and commercially set in motion as soon as possible. Technologies march on and it was feared that technical announcements would compromise the ATSC standard. Hundt had been no great fan of the HDTV-only transition idea that the more vocal in the industry were calling for, including Senator McCain (R-AZ). Hundt was not so convinced that HDTV was as important as the flexible digital world. There were last ditch attempts by the computer industry and the Hollywood Cinematographers to make changes in the standard. Everyone who had a beef with any part of the standard lobbied the Chairman for its exclusion or inclusion. Many members of the industry urged me to interview the Chairman and "smoke out" the standard -- get him talking more about it in public and get it placed on the agenda for the all-important FCC vote. The interview did bring the issue higher but it was not settled due to an agreement with Congress until early 1977.</p>

<p>The interview begins with Reed Hundt picking up the phone: <br />
  <br />
Hi, This is Reed Hundt. How are you?</p>

<p><strong>Good morning Mr. Chairman. I am very happy to have this opportunity to talk with you. We haven't spoken in awhile. In that interim a number of things have stacked up that we would like to discuss now, if you have a moment?</strong></p>

<p>Go right ahead. </p>

<p><strong>I think the overarching question is: How do you see HDTV from within the Commission?</strong></p>

<p>Well, I don't have a high definition television set yet. </p>

<p>I always consider this to be digital television because high definition is one, but only one, of the many different features of this new transmission standard. So, what we need to do is to complete our work in building an industry consensus behind the transmission standard. Saul Shapiro is taking the leadership here. He will be meeting with the industry representatives for coming to the final stages of the consensus building over the next few weeks. I am hopeful that in the month of May we will have been able to put the Grade A stamp of acceptability on the transmission standard.</p>

<p><strong>Would that be a result of the forthcoming NPRM?</strong></p>

<p>Yes. The Notice would ask: Is there anything wrong with this standard? We hope the Notice, in fact, meets with widespread acceptance. We are trying to get this acceptance in advance.</p>

<p><strong>We all know that there are some who would like to adopt this standard for  purposes apart from broadcasting, and they may have some difference with it. How is that to be handled?</strong> </p>

<p>It is a Notice. So if people have any disagreements they get a chance to write it in the record and tell us what they think. But I don't anticipate any serious controversy about the standard. "Standard" means a million different things when you get down to the engineering. This is a very technical set of issues and it is important that knowledgeable people will examine it. </p>

<p>But, I don't see any large policy questions, except one--don't you want broadcast to be able to continue to explore the flexible uses of this new transmission technology? I would think the answer is quite obviously, yes. That should be obvious, but that would be a huge change here at the Commission since we always raised major impediments to invention within the scope of the NTSC signals. Even now we have a backlog of proposals to use the NTSC signal for delivering data. I think that is ridiculous. Why should the Commission bar evolution and innovations within a standard? </p>

<p><strong>Is it then no longer important to the Commission whether the channel is used for a single HDTV broadcast or any variations that may have been proposed and talked about?</strong></p>

<p>That question has nothing to do with the standard.</p>

<p><strong>Some are puzzled that you have spoken on several occasions about the multiplexing options that few in broadcasting, if any, have intention of doing. </strong></p>

<p>Perhaps Fox said it a year or so ago. Or what Bob Wright said yesterday! In Communications Daily he said 5 or 6 channels. But look, if they are accurate, and if everyone is going to do two high definition formats with their 6 MHz, so be it. That is up to the marketplace! I hope you express this to everyone. I think that people should do what they want to do with this invention. I wouldn't think that Henry Ford would say, "I have invented the model T, but you can only use it to drive from your home to work. I don't want you to ever go anywhere else with it." Well, I am not interested in telling anybody what to do with this invention. I am in favor of small government, deregulation, and a market-oriented approach, and letting marketers and engineers together decide how to make the most out of this wonderful invention. In any event what we are talking about has nothing to do with the standard.</p>

<p><strong>It is amazing to me. Because people are taking enormous steps to try to convey to you a message that they are devoted to HDTV, thinking that has importance in your decision making processes.</strong> </p>

<p>It is totally irrelevant to me. It doesn't have any impact on any decision that I will need to make. I agree we always talk about it, but I remain mystified about this holy grail of extremely high-definition resolution. I can't imagine saying to a photographer, "Look, I don't want you to use art, and I don't want you to think about content. I want this picture to be really sharp."  What would be the point of giving this kind of advice to a photographer you might hire to take a picture of your kids? What would be the point of us saying to anyone in the business community that the number one policy goal be that the picture be really sharp.  It is obvious to me that the number one goal would be that you be able to make the most out of the invention. If one guy thinks it is the way to get the biggest audience is to have a really high-definition picture, great! If the next person thinks his biggest audience is from having the option of five different programs,.. that is great with me to! There are many rooms in the digital television mansion. There is no reason for anyone to think that the government needs to interfere with the choices with the market.</p>

<p>I believe there is a perception that choices taken to emulate the NTSC service gives more credence to those demanding spectrum charges. <br />
I have often said that I think that spectrum fees-paying a monthly tythe to the government depending on what you decide to show-is a real bad idea. That is a way to meddle with the market, and I am against it. </p>

<p><strong>If you are talking about auctions... The auction issue is a one-time issue. That is about who gets the licenses. Do they go to someone who will plunk down a chunk change, or do they go to the current broadcasters? </strong></p>

<p>That is not our issue. We have no authority to decide that. Congress will decide that. When that is decided by Congress then we get to a different question. Should you charge broadcasters depending on whether they are showing multiple channels? Should you charge them a fee if they don't show high definition? Should you charge them an extra fee if they showed some subscription television? My view is that every idea like that represents a meddlesome intrusion by government on the unknown possibilities of digital television. I am against it. <em>Let the market work.</em></p>

<p>There is only one thing that the government, in my view, should ask of digital television. That is that it serve the public interest in some specific and quantifiable way. But serving the public interest does not, in my view, get coupled with requiring to pay a "bit tax" depending on the number of bits they devote to standard or high-definition. Those ideas are anathema to someone who trusts the free market, as I do. </p>

<p><strong>I think you have clarified that to the relief of those who think other ideas might be creeping into the process.</strong></p>

<p>Unfortunately, I don't have the say-so on this. Much of this is driven by budget debates, which I don't have any say-so in. But that is where I am coming from, and it is not the first time I have said this.</p>

<p>I realize everyone focuses on the spectrum auction issue. Once that is decided you are into a different set of issues. Do you want "bit taxes"? I say, please, NO! Do you want the government to tell you what resolution and what formats to use? I say, please NO! Trust in the market. Do you want vague, ambiguous public interest obligations? I say, please NO. Make them specific, quantifiable, tradable, and minimal. Do you want to have the government interfere in a variety of ways that digital television could be structured as an industry? I say, please NO. Let the market decide that. </p>

<p><strong>In the absence from any further legislation from the Hill regarding the allocation of the new channels, what will govern your process, and when will that be done following the May setting of the standard?</strong></p>

<p>All five of the Commissioners promised Congress that we would not get the licenses out in '96, or until the new Congress was formed and we get new guidance from the Congress coming in January of '97. We have to honor that promise. It was extracted from us by Tom Bliley (H, R-VA) and Bob Dole (S, R-KA) and a number of leaders in Congress. We ought to honor it. We will honor it. So, it will be the next Congress that tells us to pull the trigger on the granting of the licenses. What we ought to do before then is make sure the standard is OK'd and that we have raised all of the relevant issues about allocation-the process of describing what the license would exactly look like. So, we ought to get that work done prior to the  Congressional indication that I would expect in early '97 on who gets the licenses, and how they are distributed. </p>

<p>If you take a little historical view, that is pretty good. I have spent all of '94 and all of '95 nagging the Grand Alliance and my Advisory Committee to get the testing done, and they only got it done last November when the report came to us.</p>

<p><strong>There are complaints arising now that the standard has been on your desk since the 28th of November, and where is it going, and what has been done since?</strong></p>

<p>I wish we had gotten it in 1994! I nagged them for two years to get their work done. There was one excuse after another. But it does take time to get technology tested. Even now they have only begun to test it. I mean the testing of multiplexing is extremely limited. Almost no one knows the maximum number of channels of acceptable quality in the field. I think there is plenty more testing to do. I hope and believe that broadcasters will continue and to do this testing even without my pressure.</p>

<p><strong>I understand that the testing lab (ATTC) is now in severe jeopardy. Funding has not been granted.</strong></p>

<p>That is a really sad commentary because it makes you think that the commitment of the industry for exploring the potential of this technology is ephemeral. You worry about that. If people are not going to really explore the capabilities of this transmission standard, then this whole process will end up producing a hollow win for the broadcasters. They won't be ready to do it. The cable industry has a lab that is working night and day to figure out how to do digital. Whole industries, with billions of dollar of R&D, are devoted to figuring out how to make cable a two way medium. The telephone industry relies on not only Bell Labs, but literally hundreds and hundreds of other projects to figure out how to get its infrastructure to deliver video. I think it would be a tragic mistake if broadcasters don't continue to explore the potential of this standard. Think of how truly sad it would be if the FCC said, "Well the standard is acceptable," and then broadcasters didn't even bother to explore its full potential. </p>

<p> I think the problem is that historically broadcasters have not acted together to develop their own technology. The Grand Alliance effort is an exception, but even there it was not driven primarily by broadcasters. So, broadcasters don't have a history of working on their medium. One reason is that the FCC stultified innovation by freezing the NTSC signal for 50 years. This was a horrible policy mistake because we curtailed any initiative within the broadcasting industry. </p>

<p><strong>There are those that say it is not such a mistake because without it you would not have created this voluntary relationship between a telecast and a receiver maker and mobility of the product could be limited.</strong></p>

<p>That flies in the face of all logic. The cable industry has contractual relationship with manufacturers. The wireless industry has contractual relationships with manufacturers. The satellite industry is founded on a crucial deal between Thomson and Hughes (DSS). Every other industry knows how to have alliances in the marketplace that endure and deliver jointly-created services. Now, what in the world is the reason to think that broadcasters need the government to forge these combos? </p>

<p><strong>They may have a good answer to that. They usually say we need a firm standard set at the FCC to insure that the sender and receiver function at the most economical level.</strong></p>

<p>Look, broadcasters invented this standard themselves in conjunction with manufacturers over a period of almost nine years. There has been all kind of talk about the FCC's role. The truth is, our role has been between skimpy and tiny. That is good. This has been done by industry. It is the exact same process of innovation that has characterized the wireless telephone industry, the satellite industry, and the cable industry. If broadcasters are going to compete against their many rivals in delivering video, they need to learn from this experience and keep working together in voluntary industry groups to develop new uses for this transmission technology.</p>

<p>Let me put it positively. It is extremely predictable that the Commission will say that this standard is OK. After all, with respect to wireless we have said that CDMA is OK, and that TDMA is OK. With respect to the satellite industry, we are hands-off on their transmission standard. We are not even interfering in a major way with the cable industry, although they do have the bottlenecks that is a problem for broadcasters. </p>

<p>So, why would we not want to be equally lassie faire and market oriented vis a vis broadcasters. The problem here is that broadcasters need to make a commitment on a short and long term basis to fully explore this wonderful new invention if they want to make the most money they can from it. I have heard, by the way, that Westinghouse intends to do that. I don't know what you know about that?</p>

<p><strong>We know of their interest in high energy solid state emitters and some other things. There is always hope that a GE and a Westinghouse would provide special skills.</strong></p>

<p>My personal vision is that once this steps into the commercialization phase that there will be an unparalled explosion of things related.<br />
I hope so. It is all going to take R & D and marketing. </p>

<p><strong>What is then, the actual role of the FCC in this standard setting process? What is essential for the FCC to do in this case?</strong></p>

<p>We have to make sure that interference protocols are clear. We have to make sure that the standard is not proprietary. We have to make sure that anyone can use it. We have to make sure that it can evolve. You shouldn't need a lawyer to develop the potential of the technology. You should need engineers and scientist, not lawyers. This is just not going to be a controversial issue. </p>

<p>The allocations will be more problematic. Difference license holders will undoubtedly want more signal strength at the expense of the next guy. But we will deal with that. Everyone knows that at the end we will have to make some fair call.  Then, in '97 the new Congress will tell us what to do with the licenses, and we will do it. Hopefully, the government will not be asked to regulate, and we will be able to stay away from regulating the commercial activities. </p>

<p>Thank you Mr. Chairman.</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Dale Cripps</b>, <b>April  4, 1996 12:00 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 115
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
 				AND entry_id <> 115
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/interviews/1996/04/interview_-_reed_hundt_chairman_fcc_-_1996.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
