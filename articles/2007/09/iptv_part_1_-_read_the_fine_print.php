<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 722";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Rodolfo La Maestra'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 722 AND placement_is_primary = 1";
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
	<meta name="keywords" content="iptv part, per month, real time, cable satellite, industry experts, iptv, IPTV, networks, content, video, distribution, quality, part, bandwidth, service, services, Part, cable, HDTV, channels, using, hdtv, month, fiber, broadband" />
	<meta name="description" content="IPTV is becoming a buzzword used to generally name TV distribution using IP networks. Some IP content distributors that only offer limited services making subscribers believe this is a full service with all the features of legacy digital cable are also using this term very loosely.

There are several camps on this subject. On one side some industry experts firmly believe that the infrastructure of our current IP networks is solid enough and is ready for full-blown HDTV distribution over IP. As you will see below..." />
	<title>HDTV Magazine Articles - IPTV Part 1 - Read the Fine Print</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/iptv_part_1_-_read_the_fine_print';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('IPTV Part 1 - Read the Fine Print'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/articles/2007/09/iptv_part_1_-_read_the_fine_print.php";
		if ($author[img] != '' && 1 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Rodolfo La Maestra" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">IPTV Part 1 - Read the Fine Print</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Rodolfo La Maestra</b><br />
				<?=$author_title?>
				Posted on <b>September 19, 2007</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Technology">Technology</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2007/09/iptv_part_1_-_read_the_fine_print.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/articles/2007/09/iptv_part_1_-_read_the_fine_print.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/articles/2007/09/iptv_part_1_-_read_the_fine_print.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2007/09/iptv_part_1_-_read_the_fine_print.php&amp;phase=2&amp;title=IPTV%20Part%201%20-%20Read%20the%20Fine%20Print&amp;bodytext=IPTV%20is%20becoming%20a%20buzzword%20used%20to%20generally%20name%20TV%20distribution%20using%20IP%20networks.%20Some%20IP%20content%20distributors%20that%20only%20offer%20limited%20services%20making%20subscribers%20believe%20this%20is%20a%20full%20service%20with%20all%20the%20features%20of%20legacy%20digital%20cable%20are%20also%20using%20this%20term%20very%20loosely.%0A%0AThere%20are%20several%20camps%20on%20this%20subject.%20On%20one%20side%20some%20industry%20experts%20firmly%20believe%20that%20the%20infrastructure%20of%20our%20current%20IP%20networks%20is%20solid%20enough%20and%20is%20ready%20for%20full-blown%20HDTV%20distribution%20over%20IP.%20As%20you%20will%20see%20below...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<div class="editorial">The following article is the latest in the IPTV series by Rodolfo La Maestra. Other articles in this series are as follows:
<ul>
<li><a href="/articles/2007/09/iptv_part_2_-_the_groups_forums_and_statistics.php">IPTV Part 2 - The Groups, Forums and Statistics</a></li>
<li><a href="/articles/2007/10/iptv_part_3_-_the_methods_and_a_working_technology.php">IPTV Part 3 - The Methods and a Working Technology</a></li>
<li><a href="/articles/2007/10/iptv_part_4_-_the_good_the_bad_and_the_ugly.php">IPTV Part 4 - The Good, the Bad and the Ugly</a></li>
<li><a href="/articles/2007/10/iptv_part_5_-_additional_implementations.php">IPTV Part 5 - Additional Implementations</a></li>
<li><a href="/articles/2007/10/iptv_part_6_-_more_implementations_and_final_thoughts.php">IPTV Part 6 - More Implementations and Final Thoughts</a></li>
</ul></div>
<br />
<B>Introduction</B>

<p>IPTV is becoming a buzzword used to generally name TV distribution using IP networks. Some IP content distributors that only offer limited services making subscribers believe this is a full service with all the features of legacy digital cable are also using this term very loosely.</p>

<p><img src="/images/products/matrixstream.jpg" alt="MatrixStream" align="right" />There are several camps on this subject. On one side some industry experts firmly believe that the infrastructure of our current IP networks is solid enough and is ready for full-blown HDTV distribution over IP. As you will see below several companies and Telcos have not only started testing IPTV, they have also successfully implemented services for thousands of subscribers.</p>

<p>Other industry experts assert that consumers customarily use more bandwidth than what current networks can support, and the current capacity would not be able to also support real time HD video in addition to the variety of digital activities consumers do every day. These experts say that HDTV distribution using IP would not replace real-time traditional broadband methods such cable and satellite.</p>

<p>Some actual implementations from manufactures and Telcos offer hardware and services that give the impression that IPTV is ready to handle several HDTVs programs in parallel to having several PCs in the same house downloading movies, music, files, photos, etc. all running over the same IP pipe.</p>

<p><br />
<B>What Does IPTV Mean to You?</B></p>

<p>IPTV manages TV signals stored as digital files that can be distributed within packets using Internet Protocol (IP) to devices such as video cell phones, iPods, and other portable receivers, in addition to the TVs at home.</p>

<p>One IPTV application is the video service distribution from telecommunications companies such as Verizon, Bell South, Qualcomm, and AT&T Wireless to deliver movies, TV shows and sports highlights to the subscriber's PCs or handsets.</p>

<p>IPTV is also the term used by Telcos to describe sending telephone, high-speed Internet and TV channels over hybrid coax or fiber-optic cable.</p>

<p>Some IPTV services supply only VOD; others include real-time HDTV as well.</p>

<p>One of the virtues of IPTV is the ability to maintain a two-way communication with subscribers, as opposed to unidirectional terrestrial broadcasters. However, with IPTV those broadcasters can generate new revenues from the same content by expanding the distribution via IPTV to destinations other than regular antennas.</p>

<p>According to Broadcast Engineering "ESPN and CNN HD content viewed by consumers at their PCs is distributed using a separate IP routing system that delivers the video over the Internet within IP packets. Tribune Broadcasting and Sinclair Broadcast Group use TeleStream's FlipFactory for IP delivery to share content and leverage technologies, such as from the Associated Press, CBS News Source, Path 1 and other video resources."</p>

<p><br />
<B>The Camp in Favor of IPTV</B></p>

<p>IPTV lowers costs, video is distributed in real-time or near real-time reliably, and can also be sent and stored for later viewing.</p>

<p><img src="/images/articles/ip-set-top-solutions.jpg" alt="IP Set-top Solutions" align="right" />Even if the receiving-end converts the video to baseband, the IP distribution system still moves and shares content easier than a typical baseband video distribution system (easier for the sender and for the receiver of the content).</p>

<p>A Telco may not need to lay down new wiring to deliver additional content to the subscriber. If the viewed channel is the only one delivered through the pipe, an IPTV channel line-up might grow independently of the distribution model. This is in contrast to the bandwidth limited services cable and satellite offer.</p>

<p>According to Broadcast Engineering: "With baseband, the assumption is that fat, proprietary pipes are available whenever you need them. IP comes from the computer data world, where bits are just bits, no matter what they describe. It's designed to accommodate limited bandwidths and high traffic networks, and employs security and error-correction algorithms that add overhead to a file and in some cases is not the most efficient (or fastest) distribution method. Connection protocols like Fiber Channel and Gigabit Ethernet help move these bits quickly and reliably. Television in its current form is becoming obsolete."</p>

<p><br />
<B>The "Not-so-Fast" Camp</B></p>

<p>Following are some excerpts from BernsteinResearch's Craig Moffett's testimony before the Senate Commerce Committee on March 17, 2006 on issues related to the pending telecommunications reform, "Net Neutrality", and IPTV:</p>

<p><br />
<br clear="all" /><blockquote>"...despite a great deal of arm waving from "visionaries," our telecommunications infrastructure is woefully unprepared for widespread delivery of advanced services, especially video, over the Internet. Downloading a single half hour TV show on the web consumes more bandwidth than does receiving 200 emails a day for a full year. Downloading a single high definition movie consumes more bandwidth than does the downloading of 35,000 web pages; it's the equivalent of downloading 2,300 songs over Apple's iTunes web site. Today's networks simply aren't scaled for that."</p>

<p>"In a series of recent research reports that I entitled "The Dumb Pipe Paradox" - which I believe provided the original impetus for the Committee's invitation to testify today - I tried to address the expectation that the telcos are rapidly rushing in to meet this need and to provide competition for cable incumbents. In fact, by their own best estimates, they'll be able to reach no more than 40% or so of American households with fiber over the next seven years. And most of that will be in the form of hybrid fiber/legacy copper networks, such as that being constructed by AT&T under the banner of "Project Lightspeed."</p>

<p>"These hybrid networks are expected to deliver 20Mbs average downstream bandwidth. After accounting for significant standard deviation around that average, that will mean many "enabled" subscribers will actually receive far less. I and many others on Wall Street harbor real doubts as whether these hybrid networks will prove technologically sufficient to meet future demands."</p>

<p>"More importantly, in 60% of the country, there are simply no new networks on the horizon, and the existing infrastructure from the telcos - DSL running at speeds of just 1.5Mbs or so - simply won't be adequate to be considered "broadband" in five years or so. That includes wireless networks, by the way. Current and planned wireless networks - including the over-hyped Wi-Max technology - offer the promise of satisfying today's definition of broadband, but simply can't feasibly support the kind of bandwidth required for the kind of dedicated point-to-point video connections that will be required to be considered broadband tomorrow. Those demands will continue to fall to terrestrial wired networks."</p>

<p>"In Part I of the "Dumb Pipe Paradox," I noted that if a telco was in the business of providing broadband connections only - that is, if phone service becomes, as many predict, simply another bit stream on top of a data connection - then the cost to provide service would be as much as $80 per month. And from a consumer's perspective, that would be the pipe only, before paying for any content over the web. And the cost, and therefore the price, would likely be much, much more."</p>

<p>"Some recent comments from BellSouth's Chief Architect, Henry Kafka, at the Optical Fiber Communication/National Fiber Optics Engineers Conference last week put this in perspective. He estimated that the average residential broadband user today consumes about two gigabytes of data per month. Heavy users who regularly download movies consume an average of 9 gigabytes of data per month. In the future, watching IPTV would consume 224 gigabytes, and would cost carriers $112 per month to deliver. And if IPTV is going to deliver High Definition, then the average user would be consuming more than one terabyte per month, at a cost to carriers of $560 per month."</blockquote></p>

<p><br />
<B>Lessons Learned from the HDTV World</B></p>

<p>After witnessing what the industry has done to image quality for the sake of squeezing in a few extra channels to generate a profit, I would say, "be cautious and read the fine print".</p>

<p>Before rushing to IPTV I now use a magnifying glass to read the smaller font on IPTV offerings, surprises most probably appear on hard-to-find footnotes.</p>

<p>Mark Cuban, the owner of probably the best quality HD feed, HDNet, was favoring the position above in one of his blogs and on the HDTV Conference sponsored by the CEA on 2006 at Washington DC.</p>

<p>Mark Cuban is a self-made millionaire and a visionary that is pro HD quality, and probably the best that could happen for HD content since 1998.</p>

<p>Mark strives to produce and distribute the best image that HD technology can provide to your home. He is certainly not about quantity: no compression, no bit-starving for more channels sacrificing the quality of the existing HD channels, no needless content protection that assumes everyone is a pirate, etc. Just one look at his HD channel will make all of this obvious.</p>

<p>I personally share the same principles. I am 100% against quantity-oriented models designed to generate a profit by offering an abundance of inferior content rather than a few well-chosen channels providing true HD quality within the bounds of the available bandwidth.</p>

<p>The few "early adopters" that have followed the evolution of HDTV since 1998 (and before) can attest that HD quality has been consistently butchered down to its knees by the "never enough" compression games implemented by satellite, cable, and broadcasters trying to send more channels over the same pipe, or by multicasting SD sub-channels while robbing from the bandwidth needed for the HD channel; and lately by the proposed A-VSB for broadcasting DTV for mobile purposes using the same 6MHz slot, which is discussed at length in the recently published series on the subject. (For more, start with <a href="/articles/2007/08/mobile_dtv_reception_-_advanced-vestigial_side-band_a-vsb_-_the_system.php">Mobile DTV Reception - Advanced-Vestigial Side-Band (A-VSB) - The System</a></p>

<p>Using the opportunities brought forth by the digital world to compromise the quality of HDTV as created is certainly a step backward.</p>

<p><br />
<B>Do Your Home Work</B></p>

<p>Some IPTV implementations could be acceptable to some subscribers that match their requirements with the characteristics of the service, but some IPTV services are not an actual improvement from the classical cable or satellite, not even in cost.</p>

<p>The trick is to completely research the technical, functional, performance and quality conditions of the IPTV service and hardware before you close an account with another service.</p>

<p>A short trial period in parallel to the existing service is a good idea if offered; it would give you the chance to also compare image quality side by side, and if one of the two services freezes up entirely while tennis star Federer is smashing the championship match-point, there is no need to compare any longer.</p>

<p>In the next article, I will have comprehensive coverage about IPTV implementation, companies involved, hardware options, what some companies do not disclose to potential subscribers, some success stories, and some lessons learned from not-so-good implementation stories.</p>

<p>Next Article: <a href="/articles/2007/09/iptv_part_2_-_the_groups_forums_and_statistics.php">IPTV Part 2 - The Groups, Forums and Statistics</a></p>
		</div>
	</div>
	<p class="posted">Posted by <b>Rodolfo La Maestra</b>, <b>September 19, 2007 10:45 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 722
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
 			<h2>More on Technology</h2><ul><?
 			$sql = "
 			SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name
 			FROM mt_entry e, mt_author a, mt_placement p, mt_category c
 			WHERE entry_author_id = author_id
 				AND e.entry_id = p.placement_entry_id
 				AND p.placement_category_id = c.category_id
 				AND category_label = 'Technology'
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
 				AND entry_id <> 722
 				AND entry_status = 2
 				AND entry_author_id = a.author_id
 				AND a.author_name = 'Rodolfo La Maestra'
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
 				<h2>About Rodolfo La Maestra</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2007/09/iptv_part_1_-_read_the_fine_print.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
