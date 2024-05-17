<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 758";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 758 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category[placement_category_id];
	
	# Still need this to track page views. Used in the Links unit in the footer
	$google_links_channel = $author[channel];

	# Set variables based on entry type. NOTE: Podcasts has its own entry template, so it is not included amongst the choices below.
	switch (7) {
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
	<meta name="keywords" content="limelight networks, looking statements, content delivery, high definition, digital media, content, media, Internet, internet, networks, Limelight, limelighthd, limelight, LimelightHD, Networks, video, delivery, statements, high, looking, quality, broadband, Microsoft, Media, deliver" />
	<meta name="description" content="Limelight Networks (NASDAQ:LLNW) , the leading content delivery network (CDN) for digital media, today introduced LimelightHD, a service for the delivery of high-definition (HD) media and digital content over the Internet. LimelightHD allows media and entertainment companies, global consumer brands, game publishers, and social media sites to deliver HD-quality movies, TV shows, video clips and games directly to their users' Internet-connected televisions, game consoles, and PCs. Leading Internet TV service Brightcove, and key media entities, including Fox Interactive Media, MSN Video and Rajshri.com, India's leading broadband video portal, are among those who announced they will offer HD content via the LimelightHD service. Media technology leaders supporting the LimelightHD initiative include Adobe Systems, Incorporated, Microsoft Corporation, Move Networks and Veoh Networks. LimelightHD will be available immediately on over 700 broadband access networks worldwide.

LimelightHD is designed to..." />
	<title>HDTV Magazine Bulletins - Limelight Networks Unveils High-Definition Content Delivery for the Internet</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/limelight_networks_unveils_high-definition_content_delivery_for_the_internet';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Limelight Networks Unveils High-Definition Content Delivery for the Internet'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2007/10/limelight_networks_unveils_high-definition_content_delivery_for_the_internet.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Limelight Networks Unveils High-Definition Content Delivery for the Internet</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>October 23, 2007</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Programming">Programming</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2007/10/limelight_networks_unveils_high-definition_content_delivery_for_the_internet.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2007/10/limelight_networks_unveils_high-definition_content_delivery_for_the_internet.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2007/10/limelight_networks_unveils_high-definition_content_delivery_for_the_internet.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2007/10/limelight_networks_unveils_high-definition_content_delivery_for_the_internet.php&amp;phase=2&amp;title=Limelight%20Networks%20Unveils%20High-Definition%20Content%20Delivery%20for%20the%20Internet&amp;bodytext=Limelight%20Networks%20%28NASDAQ%3ALLNW%29%20%2C%20the%20leading%20content%20delivery%20network%20%28CDN%29%20for%20digital%20media%2C%20today%20introduced%20LimelightHD%2C%20a%20service%20for%20the%20delivery%20of%20high-definition%20%28HD%29%20media%20and%20digital%20content%20over%20the%20Internet.%20LimelightHD%20allows%20media%20and%20entertainment%20companies%2C%20global%20consumer%20brands%2C%20game%20publishers%2C%20and%20social%20media%20sites%20to%20deliver%20HD-quality%20movies%2C%20TV%20shows%2C%20video%20clips%20and%20games%20directly%20to%20their%20users%27%20Internet-connected%20televisions%2C%20game%20consoles%2C%20and%20PCs.%20Leading%20Internet%20TV%20service%20Brightcove%2C%20and%20key%20media%20entities%2C%20including%20Fox%20Interactive%20Media%2C%20MSN%20Video%20and%20Rajshri.com%2C%20India%27s%20leading%20broadband%20video%20portal%2C%20are%20among%20those%20who%20announced%20they%20will%20offer%20HD%20content%20via%20the%20LimelightHD%20service.%20Media%20technology%20leaders%20supporting%20the%20LimelightHD%20initiative%20include%20Adobe%20Systems%2C%20Incorporated%2C%20Microsoft%20Corporation%2C%20Move%20Networks%20and%20Veoh%20Networks.%20LimelightHD%20will%20be%20available%20immediately%20on%20over%20700%20broadband%20access%20networks%20worldwide.%0A%0ALimelightHD%20is%20designed%20to...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p class="prtitle">Limelight Networks Unveils High-Definition Content Delivery for the Internet</p>

<center><i>Fox Interactive Media, Brightcove, Rajshri.com, Microsoft, Adobe, Move Networks and Veoh Networks All Sign On to Support LimelightHD(TM)</i></center><br />
<br />

<p><B>TEMPE, Ariz., Oct. 23 /PRNewswire-FirstCall/</B> -- Limelight Networks (NASDAQ:LLNW) , the leading content delivery network (CDN) for digital media, today introduced LimelightHD, a service for the delivery of high-definition (HD) media and digital content over the Internet. LimelightHD allows media and entertainment companies, global consumer brands, game publishers, and social media sites to deliver HD-quality movies, TV shows, video clips and games directly to their users' Internet-connected televisions, game consoles, and PCs. Leading Internet TV service Brightcove, and key media entities, including Fox Interactive Media, MSN Video and Rajshri.com, India's leading broadband video portal, are among those who announced they will offer HD content via the LimelightHD service. Media technology leaders supporting the LimelightHD initiative include Adobe Systems, Incorporated, Microsoft Corporation, Move Networks and Veoh Networks. LimelightHD will be available immediately on over 700 broadband access networks worldwide.</p>

<p>LimelightHD is designed to meet the rising demand for high-definition content delivered via the Internet, to television set-top boxes, media players, game consoles, and PCs. Consumers are broadly embracing HD-quality programming, with Forrester Research predicting that the majority of U.S. households will have an HD television by 2010.(1) A recent report by eMarketer noted that by 2011, there will be 200 million Internet users in the United States and 183 million online video viewers.(2) As broadcast HD becomes commonplace, consumer demand for Internet HD will grow rapidly, prompting leading media and online companies to expand their Internet programming strategies and begin introducing online HD offerings this year.</p>

<p>The LimelightHD service is specifically designed to provide end-users with a high-fidelity, high-definition media experience by bypassing the often-congested public Internet and delivering content directly to "last-mile" broadband access networks. At the heart of LimelightHD is Limelight's advanced global CDN architecture, consisting of thousands of high-performance content servers distributed worldwide, connected directly to leading broadband access networks, interconnected via a high-speed, dedicated optical network, and built to store and deliver entire content libraries. This global footprint reduces network latency and ensures that every title in every HD content library -- whether the most popular title or the least popular -- will be consistently available to every user, on demand.</p>

<p>LimelightHD will deliver video content of 720p and 1080p resolution supporting popular Internet formats and players including Adobe(R) Flash(R) Player software, Microsoft Windows Media(R), Microsoft Silverlight and Move Media Player(R).</p>

<p>"At Fox Interactive, the delivery of premium HD content on Fox on Demand is a critical element in accelerating the market," said Ron Berryman, Senior Vice President and General Manager, Fox Interactive Media. "Limelight's approach to HD content delivery -- fast, reliable and scalable -- will ensure that consumers can enjoy the programming they want to see, on multiple devices and formats."</p>

<p>"With the introduction of Brightcove Show and the integration of LimelightHD, we give thousands of our Internet TV customers the ability to provide full-screen, broadcast-quality video experiences directly to consumers from their websites," said Jeremy Allaire, Chairman and Chief Executive Officer of Brightcove. "Limelight is an ideal partner for Brightcove as we extend our Internet TV platform and ad product solutions for media owners who want to deliver long-form, HD-quality video content on the open Internet."</p>

<p>"As India's #1 broadband video portal, serving a South Asian audience worldwide, it is our constant endeavor to offer entertainment-hungry consumers premium Indian programming of the highest quality," said Rajjat A. Barjatya, Managing Director, Rajshri.com. "By leveraging Limelight's rock-solid HD delivery network, we will soon be able to offer our audience an unmatched online experience: HD-quality streams and downloads of India's finest films, TV shows, music videos and original made-for-online video programming."</p>

<p>"Industry enthusiasm for HD content is evident with major TV broadcasters and leading content publishers supporting the standard," said Mark Randall, Chief Strategist of Dynamic Media for Adobe. "As a pioneer in the delivery of seamless Web video experiences, Adobe is dedicated to ensuring viewers can watch the highest quality content -- via Flash Player compatible video. We are pleased to collaborate with Limelight and provide the next wave of innovation with HD media and digital content over the Internet."</p>

<p>"Microsoft Silverlight was designed from the outset to deliver an unrivaled HD experience. The efficient and highly scalable delivery capability for Silverlight-based applications and content positions LimelightHD extremely well for the growth of HD," said Sean Alexander, Director of Microsoft Silverlight. "We're delighted to be working with LimelightHD to deliver ever richer user experiences."</p>

<p>"The Internet has had inherent limitations for companies trying to distribute the massive files associated with HD content, and content providers have struggled to find different ways to monetize their HD content on-line," said David Hatfield, SVP of Global Products, Marketing and Sales at Limelight Networks. "LimelightHD is optimized to address these issues and deliver extensive libraries of rich media -- from a newly discovered indie movie to the most popular hit TV show -- with better clarity and speed than consumers experience with their existing broadband Internet connections. With LimelightHD, high-quality programming on the Internet is possible today, and we're looking forward to working with our customers and partners to continue to transform the digital media experience."</p>

<p><br />
<B>About Limelight Networks</B></p>

<p>Limelight Networks is a high-performance content delivery network for digital media, providing massively scalable, global delivery solutions for on-demand and live Internet distribution of video, music, games, software and social media. Limelight Networks' infrastructure is optimized for the large object sizes, large content libraries, and large audiences associated with compelling rich media content. Limelight is the content delivery network of choice for over 1,000 companies, including many of the world's top Internet, media and entertainment companies, including Microsoft Xbox LIVE, Sony Playstation 3, Akimbo, Amazon Unbox(TM), Belo Interactive, Brightcove, "BuyMusic" @ Buy.com, DreamWorks, LLC, Facebook, FOXNews.com, IFILM, ITV Play, MSNBC.com, NC Interactive and Valve. For more information, visit http://www.llnw.com/.</p>

<p>The names of actual companies and products mentioned herein may be the trademarks of their respective owners.</p>

<p>Safe Harbor Act Disclaimer: All forward-looking statements contained in this release are made within the meaning of and pursuant to the safe harbor provisions of the Private Securities Litigation Reform Act of 1995. Forward-looking statements are statements other than statements of historical facts, including but not limited to statements concerning the availability of LimelightHD on broadband access networks worldwide, growth in consumer demand for Internet HD, Limelight Networks' ability to transform the digital media experience, and other statements concerning the plans, intentions, expectations, projections, hopes, beliefs, objectives, goals and strategies of management. Forward-looking statements are not guarantees of future performance or events and are subject to a number of known and unknown risks, uncertainties and other factors that could cause actual results to differ materially from those expressed, projected or implied by such forward-looking statements. Accordingly, there can be no assurance that the results expressed, projected or implied by any forward-looking statements will be achieved, and readers are cautioned not to place undue reliance on any forward-looking statements. The forward-looking statements in this press release speak only as of the date hereof and are based on the current plans, goals, objectives, strategies, intentions, expectations and assumptions of, and the information currently available to, management. The Company assumes no duty or obligation to update or revise any forward-looking statements for any reason, whether as the result of changes in expectations, new information, future events, conditions or circumstances or otherwise.</p>

<p>(1) Forrester Research, "Benchmark 2007: The Five-Year Forecast for</p>

<p>Devices and Access," September 20, 2007 (2) eMarketer, "On-Line Video: Making Content Pay," August 2007</p>

<p>Source: Limelight Networks</p>

<p>CONTACT: Kristen Leon of Waggener Edstrom Worldwide, +1-415-547-7027,<br />
kristenl@waggeneredstrom.com, for Limelight Networks</p>

<p>Web site: http://www.limelightnetworks.com/</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>October 23, 2007 08:34 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 758
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
 			<h2>More on Programming</h2><ul><?
 			$sql = "
 			SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name
 			FROM mt_entry e, mt_author a, mt_placement p, mt_category c
 			WHERE entry_author_id = author_id
 				AND e.entry_id = p.placement_entry_id
 				AND p.placement_category_id = c.category_id
 				AND category_label = 'Programming'
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
			
 		<?if (7 <> 7) {
 			# Recent Articles by Author (exclude this one)
 			# Do not show recent articles for Bulletins.
 			$qry = "
 			SELECT entry_id, entry_blog_id, entry_created_on, entry_title, author_id, author_name
 			FROM mt_entry e, mt_author a
 			WHERE entry_blog_id = 7
 				AND entry_id <> 758
 				AND entry_status = 2
 				AND entry_author_id = a.author_id
 				AND a.author_name = 'Shane Sturgeon'
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
 				<h2>About Shane Sturgeon</h2>
 				<?=stripslashes($author[bio_short])?>
 			<span class="corners-bottom"><span></span></span></div>
 		<?}?>
		</td><td id="right">

 		<?if ($about != '') {?>
 			<div class="item"><span class="corners-top"><span></span></span>
 				<h2>About Bulletins</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2007/10/limelight_networks_unveils_high-definition_content_delivery_for_the_internet.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
