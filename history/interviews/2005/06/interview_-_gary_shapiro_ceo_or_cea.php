<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 65";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Dale Cripps'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 65 AND placement_is_primary = 1";
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
	<meta name="keywords" content="consumer electronics, encoding rules, gary shapiro, cable compatibility, fcc tuner, HDTV, hdtv, cable, agreement, FCC, fcc, people, consumer, think, any, manufacturers, believe, our, set, mandate, issues, issue, going, rules, terms" />
	<meta name="description" content="From our 2003 archives INTERVIEW Gary Shapiro, CEO of Consumer Electronics Association by Dale Cripps Gary Shapiro is a disciplined man. He runs one of the largest trade associations in the world. His Consumer Electronics Show (CES) is one of..." />
	<title>HDTV Magazine Interviews - INTERVIEW - Gary Shapiro, CEO or CEA</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/interview_-_gary_shapiro_ceo_or_cea';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('INTERVIEW - Gary Shapiro, CEO or CEA'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_-_gary_shapiro_ceo_or_cea.php";
		if ($author[img] != '' && 4 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Dale Cripps" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">INTERVIEW - Gary Shapiro, CEO or CEA</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Dale Cripps</b><br />
				<?=$author_title?>
				Posted on <b>June  9, 2005</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category="></a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_-_gary_shapiro_ceo_or_cea.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/history/interviews/2005/06/interview_-_gary_shapiro_ceo_or_cea.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_-_gary_shapiro_ceo_or_cea.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_-_gary_shapiro_ceo_or_cea.php&amp;phase=2&amp;title=INTERVIEW%20-%20Gary%20Shapiro%2C%20CEO%20or%20CEA&amp;bodytext=From%20our%202003%20archives%20INTERVIEW%20Gary%20Shapiro%2C%20CEO%20of%20Consumer%20Electronics%20Association%20by%20Dale%20Cripps%20Gary%20Shapiro%20is%20a%20disciplined%20man.%20He%20runs%20one%20of%20the%20largest%20trade%20associations%20in%20the%20world.%20His%20Consumer%20Electronics%20Show%20%28CES%29%20is%20one%20of...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p><em>From our 2003 archives</em></p>

<p><br />
INTERVIEW</p>

<p>Gary Shapiro, CEO of Consumer Electronics Association<br />
by<br />
Dale Cripps</p>

<p>Gary Shapiro is a disciplined man. He runs one of the largest trade associations in the world. His Consumer Electronics Show (CES) is one of the largest in the nation. He runs in the morning. He is a lawyer by training. One rule he observes is the “20 minute interview” which he learned in a media relations class. That one can leave the interviewer hungry for more. My approach has always been to open  with some generosity and then close in on the tougher points I know we all want answered. Let’s be happy with what we have under Gary's rules, since that is all we have. Is this the year for HDTV? All indicators say it is. </p>

<p><br />
"HDTV is what we believe in."<br />
_Gary Shapiro 2003</p>

<p>DC: What does the HDTV business look like today? Or, do we call it the DTV business?</p>

<p>GS: We use the term “HDTV” around here unless someone forgets. There is a camp of people that I put you, Peter Fannon (Panasonic), and Dick Wiley (former chairman of FCC who headed the FCC Advisory Committee on Advanced Television Services) in who are, among others, the HDTV true believers...and you have been for at least five years. I think most in the DTV Pioneer's Academy would fall into this category. We (CEA) believe in HDTV in all of its glory. There are people at Circuit City and other retailers who would agree and say, “Let’s start with the best.” That was defined for us in some of the format battles going way back around the time of the multicasting issues. But HDTV is what we believe in.</p>

<p>DC: Is it living up to our beliefs?</p>

<p>GS: In many ways HDTV has exceeded our belief in terms of its popularity and in terms of its beauty. It has not come as quickly to broadcasters and cable as we would have hoped. But people love it, and satellite has provided close to enough HDTV. There are announcements showing up every day now about channels coming on. Certainly, sports programming broadcast on the networks has been excellent. In terms of the dollar volume traded at retail, it is huge. We projected 2.1 million units and wound up at 2.4 million units in year 2002. That is beyond our forecast. That is spectacular. So, in terms of consumer acceptance and dollar volume in sales, in terms of consumer appreciation, some of the beauty in the sporting events, and some of the satellite channels it has done terrific. In regards to sale of tuners for over-the-air television, locale stations going to it…it is not proceeding as we had anticipated. I think we were either naïve or optimistic in predicting how local broadcasters would embrace HDTV. In reality when someone is relying on an over-the-air antenna that means they are not a satellite or cable subscriber and suggests that they are the lower income people. That is not the group of people who are most likely to go out and buy a HDTV receiver. So, our assumptions were wrong. We also didn’t assume there would be such a strong DVD market, which would drive HDTV (monitors). Widescreen movies in DVD are clearly the biggest driver of HDTV sales.</p>

<p>DC: Do you think there will be some catch-up in the OTA tuner business and  in your estimation what has been the cause of it not meeting initial expectations?</p>

<p>GS: Looking at the history of it the manufacturers first thought that integrated TV sets were the way to go. Then we had this format question that was raised by Sinclair, and the manufacturers hesitated. They said that they believe in HDTV but "we will sell monitors" until this issue is resolved. Sinclair single-handedly set back the transition to over-the-air HDTV not by one year, as several people believe, but by several years. Manufacturers discovered that people wanted monitors and, for the most part, didn’t want to buy integrated sets. Because they were not buying integrated sets broadcasters held back even after the standards issues was resolved. So, Sinclair was the one that hurt the broadcast transition more than any other entity in the United States. Quite frankly at that time the FCC hesitated and was not very strong in putting down Sinclair. Sinclair caused some real problems and a lot of the blame goes to them for trying to switch the standard to COFDM. It was wrong at the time because Sinclair had an interest, and still does, in delaying the transition to digital. Their public filing discloses the fact that they will benefit as long as analog is around because of their other manufacturing interests. </p>

<p>DC: I understand that the manufacturing business you are referring to is all but out of business and insignificant.</p>

<p>GS: Yes, but at the time Nat Ostroff, their chief technical officer, was out to change the standard. </p>

<p>DC: Where are we standing now in integration vs. non-integration of ATSC tuners into monitors?</p>

<p>GS: The FCC has issued a mandate for tuner integration and manufacturers intend to follow the law. </p>

<p>DC: You are on record for standing in opposition to this FCC tuner inclusion mandate. Some folks went so far as to say that you went over to the dark side. What was that all about?</p>

<p>GS: We opposed the mandate. We continue to oppose it in a law suit because we don’t like mandates in the first place, and in the second place we thought the FCC exceeded their authority. Thirdly, we thought the tuner mandate was the incorrect approach. We think the issue has to do with cable and programming and the marketplace would follow. We have come a long ways towards solving the cable issue. But a mandate which affects 15% of American homes (CEA claims only 15% are dependant on OTA) and have everyone pay, especially with the large patent royalties involved, we continue to think that is improper. Having said that it is the intention of every manufacturer to follow the law.</p>

<p>DC: You mention large royalty figures. Are those numbers outside of the norm?</p>

<p>GS: I don’t know what the norm is. I heard that Zenith is asking $14 or $15 for their royalties and Thomson also has patents. There were five Grand Alliance members who each have patents involved.</p>

<p>DC: So, when stacked up there is significant money involved?</p>

<p>GS: There is a disagreement between the broadcasters and set manufacturers about the cost. Broadcasters talk about the cost of raw materials. That is like pricing a restaurant meal based on the cost of the raw materials of the food.</p>

<p>DC: Where does this suit stand today?</p>

<p>GS: It is in the Federal court of appeals. I don’t believe oral arguments have been scheduled yet. I assume they will be in a few months.</p>

<p>DC: Let’s assume that your appeal is unheard and tuners are included and the cost is burdensome. What does that do to the over-all market pace? </p>

<p>GS: We have not gone that far since that involves a lot of highly complex factors and individual manufacturer pricing and competitive decisions. There is no question that the more a product costs the fewer consumers buy it. What keeps consumer electronics prices low is intense competition among all sorts of features.</p>

<p>"What has made the FCC tuner mandate and law suit less important is the cable compatibility agreement."<br />
 <br />
What has made the FCC tuner mandate and law suit less important is the cable compatibility agreement (where all manufacturers have signed the PHILA agreement with CableLabs). Virtually every manufacturer has indicated that including OTA tuners is not so burdensome as long as they are putting the cable compatible features into the set anyway. Manufacturers will be rushing to meet the cable compatibility standard once there is a clear indication that it is going to be accepted fully by the FCC.</p>

<p>DC: Is there any question?</p>

<p>GS: Not that I am aware of. It was just filed a week or two ago and people are studying it. But in every meeting we have had there is every indication that the policy makers are very happy that the two industries came together on this. There is no question that others have the right to comment on it.</p>

<p>DC: In explaining this agreement to the consumer, how do you sum it up?</p>

<p>GS: In a couple of years you will be able to buy a cable-compatible HDTV set that will work with virtually any cable company in the nation. To wherever you move you can be assured your set will be plug and play.</p>

<p>DC: The only feature that seems to be out of that agreement is the interactive part of it. Where was the bone of contention on that?</p>

<p>GS: It is not a matter of there being a bone of contention. We agreed that it is a complex issue. We said, "Let’s take the biggest bite and develop some of the main principles for one way." We resolved tough issues like the encoding rules and what they would be. All of those would easily apply to 'interactive' as well. 'Interactive' raises other significant issues, such as where the intelligence lay--the cable system or the TV set--and what are the rules when there is intelligence in both places? It’s the question of who is king of the road? What does the set have to respond to? It is a very complex and will take some serious discussions with the cable industry. It is going to take the will on behalf of both industries to resolve these tougher issues and a desire to provide a truly interactive product. That is what is ultimately in the consumer's interest.</p>

<p>DC: It took a while to get to this agreement. What were the sticking points along the way?</p>

<p>If someone boycotts HDTV in this country they do so at their own peril. <br />
 <br />
GS: You have no idea what "a while" it did take. This agreement has occupied 20 years of my life in the consumer electronics industry. In the early 1980s we tried to define a compatible analog set. We had a draft pamphlet which we had agreed upon. We even had a standard in which RCA had invested a considerable amount called multiport. But cable companies didn't follow. There is also a mandate (now) from Congress on this. There were a lot of efforts (leading to the agreement). There is no question that we have here two industries who felt burnt by each other. There were a lot of missteps along the way. The credit goes to a lot of companies involved, especially from Comcast and Mitsubishi, who worked  very hard. They led a team facilitated by CEA and the National Cable Telecommunications Association. They worked through a whole range of highly complex issues. In the end everybody gave a little bit. </p>

<p>What is not part of it (the agreement) is the ability of the cable company to turn off a consumer electronics' product (called selectable output control). This was a very grievous concern to manufacturers. It was part of the licensing agreement that CableLabs was insisting upon. We felt it was potentially very harmful to our consumers. That is now off of the table and will not be a part of any of these agreements in the future. We had to swallow hard and accept the content copyright restrictions which the Motion Picture Association had sought in the 5C license. That was very difficult for us.</p>

<p>DC: What were the most difficult issues?</p>

<p>GS: The issue of content protection, selectable output control were extraordinarily difficult to resolve.</p>

<p>DC: Is the position of the consumer electronics industry that "we don't like anyone but our customers turning off anything for any reason?"</p>

<p>GS: We managed to have an agreement without selectable output control. They have agreed to it.</p>

<p>DC: Were the content people involved with that decision?</p>

<p>GS: The MPAA had sought and obtained things in the 5C license, so they will have to speak for themselves. My guess is that they were pleasantly surprised? Jack Valenti said that he was "pleased" that the CE industry is calling for the FCC to adopt encoding rules. The MPAA has not responded negatively, just that they were pleased that we are calling upon the FCC to adopt encoding rules.</p>

<p>DC: For those who are disciplined on following the rules of "fair use" copying how are the copy protection issues shaping up?</p>

<p>GS: The way it is shaping up is that you will be able to shift content around your home but you can't ship it out over the Internet to other people. </p>

<p>DC: I think our people would be in complete agreement.</p>

<p>GS: There may be an exception in the case of a pay-per-view event where you are paying to see a live boxing match or other value event. (In this case) you are paying to view it once and not to own it. That is what those copyright restrictions, which we have agreed to in the encoding rules, cover. It depends upon what you are watching. If you are watching free over-the-air broadcast, you certainly have the right to record it and view it as many times as you like. With pay-per-view you just have the right to watch it. </p>

<p>DC: How soon do you expect to see a HD-DVD on the market? </p>

<p>GS: My guess is 2004, if not earlier. Joe Flaherty (the father of HD in this nation) is very high on one of these companies.</p>

<p>DC: Are the war jitters causing any contingent plans in manufacturing or marketing to react to any shifting attitudes in this country?</p>

<p>GS: In any business in the United States today the issue of war is a factor--an unknown one. There is probably a greater focus on the economy than on war. We are in a tough time. Everyone is hoping that 2003 will be better. But no one is looking at major growth in any industry. We are hoping for rather modest growth. </p>

<p>With HDTV, though, we expect double digit growth. HDTV is one of the few bright spots in the entire US economy.</p>

<p>DC: There are some of us who believe that HDTV has enough demand going for it that it will sweep other tangential businesses, and those made popular through it, up   and make at least some measurable contribution to the economic recovery of the nation. Does anyone there see it that way?</p>

<p>GS: I think it is a positive factor. I would not say that it is going to lift up the whole country alone. HDTV is certainly a bright spot.</p>

<p>DC: What can government do to advance the cause of HDTV?</p>

<p>GS: Right now we are hoping that the FCC will move forward rapidly with the cable compatibility agreement. That is the biggest thing in the short term. In the long term I think we have to hope there will be something worked out between the broadcasters and the cable companies on 'must carry.' If not, that is something the government may have to step in on. Other than that I am not sure of the appropriate role for government right now. The broadcast flag issue has to be resolved.</p>

<p>DC: CBS has threatened to withdraw a year from HDTV delivery if no agreement is reached on a "broadcast flag." What is your comment upon that filing from Viacom which we reported on a few weeks ago?</p>

<p>GS: We are very disappointed that CBS has taken that approach. You know, for us CBS has taken such a phenomenal lead in HDTV...and we don't think it is wise to blackmail the government. It is a questionable strategy in terms of its effectiveness. It is also of great concern among us who have worked closely with CBS all of these years as a great leader in HDTV. </p>

<p>Of course, we value CBS's contribution on HDTV but we don't believe that one network is going to single-handedly make or break HDTV. The fact that cable and satellite programmers are rushing to HDTV, and with the success of pre-recorded formats (namely the DVD) indicates that consumers are rushing to the highest quality video formats. If someone boycotts HDTV in this country they do so at their own peril. </p>

<p>DC: Thank you Gary.</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Dale Cripps</b>, <b>June  9, 2005 09:16 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 65
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
 				AND entry_id <> 65
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_-_gary_shapiro_ceo_or_cea.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
