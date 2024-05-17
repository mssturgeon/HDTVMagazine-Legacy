<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 61";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Dale Cripps'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 61 AND placement_is_primary = 1";
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
	<meta name="keywords" content="hdtv magazine, nbc universal, universal cable, standard def, chief financial, cable, universal, perrette, Perrette, HDTV, hdtv, bravo, nbc, NBC, Universal, magazine, Magazine, new, Cable, channels, BRAVO, our, think, UNIVERSAL, content" />
	<meta name="description" content="This interview was conducted in early 2005. Jean-Briac (JB) Perrette Senior Vice President, New Media And Chief Financial Officer NBC Universal Cable Jean-Briac (JB) Perrette was named Senior Vice President, New Media, and Chief Financial Officer of NBC Universal Cable..." />
	<title>HDTV Magazine Interviews - INTERVIEW - Jean-Briac Parrette</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/interview_-_jean-briac_parrette';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('INTERVIEW - Jean-Briac Parrette'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_-_jean-briac_parrette.php";
		if ($author[img] != '' && 4 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Dale Cripps" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">INTERVIEW - Jean-Briac Parrette</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Dale Cripps</b><br />
				<?=$author_title?>
				Posted on <b>June  9, 2005</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category="></a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_-_jean-briac_parrette.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/history/interviews/2005/06/interview_-_jean-briac_parrette.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_-_jean-briac_parrette.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_-_jean-briac_parrette.php&amp;phase=2&amp;title=INTERVIEW%20-%20Jean-Briac%20Parrette&amp;bodytext=This%20interview%20was%20conducted%20in%20early%202005.%20Jean-Briac%20%28JB%29%20Perrette%20Senior%20Vice%20President%2C%20New%20Media%20And%20Chief%20Financial%20Officer%20NBC%20Universal%20Cable%20Jean-Briac%20%28JB%29%20Perrette%20was%20named%20Senior%20Vice%20President%2C%20New%20Media%2C%20and%20Chief%20Financial%20Officer%20of%20NBC%20Universal%20Cable...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p><em>This interview was conducted in early 2005.</em></p>

<p>Jean-Briac (JB) Perrette</p>

<p>Senior Vice President, New Media<br />
And Chief Financial Officer<br />
NBC Universal Cable </p>

<p><br />
Jean-Briac (JB) Perrette was named Senior Vice President, New Media, and Chief Financial Officer of NBC Universal Cable in May 2004. In his New Media role, Perrette spearheads the division’s worldwide strategy and development of new content distribution businesses, including video-on-demand (VOD), pay-per-view (PPV) and high definition (HD).  In this role he reports to David Zaslav, President of NBC Universal Cable. As CFO of NBC Universal Cable, Perrette is also responsible for the financial operations for cable distribution of NBC Universal’s leading portfolio of cable & broadcast assets, which are: Bravo, Bravo HD+, CNBC, CNBC World, MSNBC, mun2, Olympics, Sci-Fi, Telemundo, Trio and USA. In his CFO role, Perrette reports to Lynn Calpeter, Executive Vice President and Chief Financial Officer of NBC Universal.</p>

<p>As part of NBC’s acquisition of Vivendi Universal Entertainment, Perrette led the integration of NBC and Universal’s Cable divisions, creating one of the broadest, most profitable and fastest growing television groups. He also served as Chief Financial Officer of the Bravo Cable Network since January 2003, following his lead role in NBC’s acquisition of the network from Cablevision.  In 2003, Bravo became the fastest growing Cable network in the US and home to such hit series as Queer Eye for the Straight Guy and Celebrity Poker.</p>

<p>In his prior role at NBC, Perrette was Vice President & CFO of Business Development and was instrumental in completing over $1.5BN in acquisitions, most importantly of Bravo Cable Network and San Francisco station KNTV.  He also negotiated and executed several JVs and partnerships for CNBC International as part of its global strategy.  In his finance role, Perrette was responsible for NBC's strategic investment portfolio, which included holdings in A&E, Paxson, National Geographic International, and ValueVision International. </p>

<p>Before joining NBC, Perrette worked for NBC parent, GE and GE Capital, and was an analyst with CS First Boston in London and Tokyo.  He received a BA degree in Public Policy from Hamilton College.  Perrette lives in New York City with his wife Amy.</p>

<p><br />
<strong>About NBC Universal Cable </strong><br />
NBC Universal Cable, a division of NBC Universal, one of the world's preeminent media companies, drives the company’s cable strategic development and growth including video-on-demand, pay-per-view, HDTV and retransmission consent, and oversees the cable distribution, marketing and local ad sales of twelve properties (Bravo, Bravo HD+, CNBC, CNBC World, MSNBC, mun2, Sci-Fi, ShopNBC, Telemundo, Trio, USA and the Olympics on cable).  NBC Universal Cable also directs and manages the company’s cable and new media investments including A&E, The History Channel, History Channel International, The Biography Channel, National Geographic International, the Sundance Channel and Tivo.  </p>

<p><br />
INTERVIEW:</p>

<p><br />
Dale Cripps: How did you connect to the world of HDTV? </p>

<p>Perrette: I spent several years in business development with NBC prior to the merger with Universal. I spent a year in the finance role when we acquired BRAVO and then headed up the cable integrations of our entertainment cable assets over the course of the last year and then moved to this role working with David Zavlov back in June of this year. I come at it with a business development/finance background. We have taken all of the piece of business development that affect the cable and satellite players, so HD, Video on Demand, and Pay Per view, new channels launches—anything that is basically is related to the cable and satellite operators put under the “new media” heading .</p>

<p>HDTV Magazine: VOD has always had a great promise but seems to be forever disappointing. </p>

<p>Perrette  I think you have look to Comcast for breathing life into it. It has had this real promise, I agree with you, and it was the platform and technology that was always one year away from being big.  That said, you have never seen the focus and a messages so consistent and persistent coming from the biggest operator in the market (Comcast) on one platform. They have rallied around VOD as their differentiator. </p>

<p>HDTV Magazine:  Isn’t it their combating of satellite driving that initiative more than it is a business waiting to happen? </p>

<p>Certainly, in part, I think that is right. </p>

<p>HDTV Magazine: The great late Howard Miller suffered a huge loss at Westinghouse many years ago when he drove their interactive cable experiment back in the 70s. He said he learned that you can never trust a business plan that requires for its success your customers to change their behavior. HDTV doesn’t suffer from that condition you just need to change your taste a bit.</p>

<p>Perrette  I don’t think you need to change your taste but rather just have a teaser of  having seen and experience what HDTV can do. It is still a faily expensive investment for the technology upgrade to allow you to do it, but once bitten people are persuaded to do it.</p>

<p>HDTV Magazine: With you being from the financial side you know that HDTV defies any traditional financial logic. It bears a significant expense, it cost more bandwidth, and you have never been sure that the customers would embrace it.  When you saw it…how did it strike you?</p>

<p>Perrette   A lot of the credit for the  NBC or Universal’s HD strategy goes to David Zazlov, who is head of our cable When we bought BRAVO we listened to the industry and to the (cable) operators who were continuing to push HD two years ago. At that time there was such a limited amount of HD programming available. We need to increase the content available in order to push the service and develop the consumer value in it. David had the forsight to move the company to launch BRAVO HD back in July of 2003. We since then have found it to be a terrific asset. The operators responded to it. With our closing the Universal acquisition realize BRAVO was a smart and upscale brand whom you generally associate with the early adopters of technology, and so associating BRAVO and HD together was a smart play and why we did it. As the HD market has grown significantly over the last year and one half and we now had the opportunity to take more entertainment content that became available to us through the acquisition we wanted to again be responsive to the industry and the operators and develop a product that was no longer as niche but with a broader appeal that would attract many more people to the HD platform as consumer/viewers, and make it a compelling broad offering rather than the more niche offering we had in BRAVO.  So that iw why we rolled out the transformed BRAVO in UNIVERSAL HD—a broader and, we think, a more compelling service. </p>

<p>HDTV Magazine:  What are you selecting from the Universal library that will make it a compelling service?</p>

<p><br />
Perrette   We started with the US Open Tennis event, which we also broadcast on USA in standard def, and we simulcast it on BRAVO HD in high def and we will do that going forward. So, we start with the sports franchises, which we know are valuable and people enjoy watching, It is some of the most impressive material you can see in the HD environment.  We are looking to add additional sports including golf and other events. </p>

<p>HDTV Magazine: I do not receive BRAVO here so I need to ask if these are sponsored events or paid through premium subscriptions.</p>

<p>Perrette   It is similar to our traditional cable channels. Which will have a dual revenue stream of both advertiser and subscriber revenue. </p>

<p>HDTV Magazine: What is th4e carriage of UNIVERSAL HD?</p>

<p>Perrette   Right now we have carriage agreements for 25 million homes across the U.S. This includes DirecTV, COX, VOOM, and we are obviously looking to expand that in coming months?</p>

<p>HDTV Magazine: Where do you find any resistance to carriage? </p>

<p>Perrette   It is the natural evolution. For a new network to be in 25 million homes over 18 months in today’s environment is a terrific accomplishment.  We are confident that in the next six to twelve months we will add on to that significantly. The response has been very positive from our announcement to transform BRAVO HD into UNIVERSAL HD and moving to a much broader service.</p>

<p>HDTV Magazine: Is there anything you are doing with UNIVERSAL HD that is making you hold your breath and cross your fingers?</p>

<p>Perrette   No. There is nothing here I think that is concerning here.  It is a terrific development. I have Time Warner here in New York and the HD offering is till limited.  People are not into the rythem yet of just going into the HD channels. There is still not quite enough there and the programming it is not consistently HD. As they continue to have more HD channels with more HD programming on those channels our view is that you will then train people into becoming just truly HD watchers as opposed to switching from standard def to High def. Our service is 100% 1080i HD content and between sports, movies (mini blockbusters like Backdraft, Apollo 13, Meet the Parent) those are all titles that will be done without commercial interruption and in some cases uncut (not generally available on linear cable services). </p>

<p>The third thing is that we will have these franchises which we are taking from the best of our cable properties, including Monk, Battle Star Gallatica, Law and Order SVU from USA. Those are huge franchises that are from the top rated cable programming that we think will be a huge draw relative to the offering that is out there today. We take somewhat more of the limited bandwidth, which some of the operators are sensitive to, but instead really offer the most compelling product in one slot. <br />
HDTV Magazine:</p>

<p>HDTV Magazine: Are you firm on not compromising image quality?</p>

<p>Perrette   Absolutely! We think if you are going to be in this space and get consumer/viewers being compelled to it then going in and out of HD and standard def is a huge disservice to the viewer and to the roll out of HD overall.</p>

<p>HDTV Magazine: We have listened  to endless complaints about one cable channel who has chosen to upconvert and stretch (to fill the screen) a large percentage of what they list with the major listing services, ours, and TV Guide, that it is all HDTV. It is a major frustration for the viewers. You will not subject the viewer to that kind of thing?</p>

<p>Perrette   Ours is 100% HDTV programming. </p>

<p>HDTV Magazine: That will be very welcome news to our readers. </p>

<p>You have a great classic library at Universal. Will we see some of the old classics, such as the Frankenstein franchise from the old black and white era?  </p>

<p>The challenge is always the same. In re-launching the service as UNIVERSAL HD what we are going to do is make is a much broader appealing service. The problem with the old black and white and other older content is that it is  terrific and very valuable but from an audience appeal in today’s age it has less of an appeal than Appolo 13 or a Backdraft.  So, while we certainly look at doing things down the road that might use more of that content for now the service will largely stick with the content that has a broader appeal. </p>

<p>HDTV Magazine: Universal Studios is one of the great factories for producing original programming for television. Do you forecast any original productions for this network?</p>

<p>Perrette  It is something we will always look as the business model evolves and the viewership evolves. </p>

<p>HDTV Magazine: Since you have global responsibilities do you see establishing the UNIVERSAL HD franchise extending itself around the world? </p>

<p>Perrette  As you know the challenge is always International. It is hard to speak about as a  broad a single discussion because unlike the U.S. market it is so dependant upon market by market activities. But in general we are actively involved in the creation of new assets and new services and we are taking them as opportunity arises. While nothing I did we do have a series of international standard def channels that exists in Europe and Latin America and Asia. (CNBC Europe and Asia, Sci Fi Channels in Europe, 13th Street in several markets in Europe, We have Universal Channel in Latin America. So, we have a series of different channels that already excit internationally. We are constantly and actively looking to see how we can create new channels and new services. </p>

<p>HDTV Magazine:  Speaking of the SC fi Channel. Any chance of seeing the SCFi channels take the same route as UNIVERSAL HD? </p>

<p>Perrette: The question is; when will all of the linear services will move from standard def to HD? I think that will happen. Is that a three year item? Is that a five year item? Everyone has their own guess, but certainly over time you can imagine that the natural progression is going to be that all services will move to the HD platform. In the interim we are excited to use the UNIVERSAL HD umbrella to be able to put things like Battle Star Gallatica (ScFi's upcoming series) and provide SCiFI content there.</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Dale Cripps</b>, <b>June  9, 2005 08:38 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 61
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
 				AND entry_id <> 61
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_-_jean-briac_parrette.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
