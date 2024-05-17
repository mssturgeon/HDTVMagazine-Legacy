<?
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 830";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 830 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category[placement_category_id];
	
	# Still need this to track page views. Used in the Links unit in the footer
	$google_links_channel = $GOOGLE_CHANNEL['Shane Sturgeon'];

	# This template is used only for articles and bulletins. Reviews are generated from a separate template
	switch (1) {
		case 1: # Articles
			$rss_link = '<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/hdtv-articles" />';
			$container = 'article_container';
			$category_page = 'category.php';
			break;
		case 6: # Test
			$rss_link = '<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/hdtv-articles" />';
			$container = 'article_container';
			$category_page = 'category.php';
			break;
		case 7: # Bulletins
			$rss_link = '<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/hdtv-bulletins" />';
			$container = 'bulletin_container';
			$category_page = 'bulletins-category.php';
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
	<meta name="keywords" content="content available, high definition, usb ports, live marketplace, vudu player, content, movies, available, vudu, video, VUDU, movie, DVD, digital, dvd, most, watch, quite, purchase, quality, player, titles, see, USB, marketplace" />
	<meta name="description" content=" I am writing a series of small reviews for the 2007 Holiday Gadget Guide, hosted by our advertising agency, Federated Media. One of the &amp;quot;gadgets&amp;quot; I reviewed recently was so interesting I thought it should have a more in-depth review. I have taken that original review, and gone into more detail in the review that follows. Unless you've been under a rock for the past two years, you are undoubtedly aware of the HD DVD vs. Blu-ray &amp;quot;Format War&amp;quot;..." />
	<title>HDTV Magazine Articles: VUDU: HD DVD &amp; Blu-ray Killer?</title>
	<?=$rss_link?>
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		$pagetag = "NTPT_PGEXTRA = 'author=". $AUTHOR_ID['Shane Sturgeon'] ."';\n";
		include(BASE_DIR .'/includes/tracker.php');
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/vudu_hd_dvd_blu-ray_killer';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('VUDU: HD DVD &amp; Blu-ray Killer?'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/articles/2007/12/vudu_hd_dvd_blu-ray_killer.php";
		if (array_key_exists('Shane Sturgeon', $AUTHOR_PORTRAIT) && 1 <> 7) {
			$img = '<img src="'. $AUTHOR_PORTRAIT['Shane Sturgeon'] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">VUDU: HD DVD &amp; Blu-ray Killer?</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>December 19, 2007</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=&id=<?=$category_id?>"></a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2007/12/vudu_hd_dvd_blu-ray_killer.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/articles/2007/12/vudu_hd_dvd_blu-ray_killer.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/articles/2007/12/vudu_hd_dvd_blu-ray_killer.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
				<span><img src="/images/save.gif" alt="Save Article" align="absmiddle" /><a target="_blank" href="<?=$save_url?>">Save</a></span>
				<span><img src="/images/email.gif" alt="Email Article" align="absmiddle" /><a href="<?=$email_url?>">Email</a></span>
				<span><img src="/images/print.png" alt="Print Article" align="absmiddle" /><a target="_blank" href="<?=$print_url?>">Print</a></span><br />
				<br /><br />
			</td>
		</tr>
	</table>
	<?if ($userdata[subscriptions] & SUB_ARTICLES) {} else {
		if ($userdata[session_logged_in]) {?>
			<div class="important"><span class="corners-top"><span></span></span>
				<img src="/images/i_inbox.gif" align="left" style="padding-right:10px" /><span class="label">Receive instant notification of new articles:</span>
				<a href="<?=URL_PROFILE_SUBSCRIPTIONS?>">Modify your subscription profile</a> to receive notification of new
				HDTV Magazine Articles via email as soon as they are published.
			<span class="corners-bottom"><span></span></span></div>
		<?} else {?>
			<div class="important"><span class="corners-top"><span></span></span>
				<img src="/images/i_inbox.gif" align="left" style="padding-right:10px" /><span class="label">Receive instant notification of new articles:</span>
				<a href="<?=URL_PROFILE_CREATE?>">Register Now</a> to receive notification of new
				HDTV Magazine Articles via email as soon as they are published.
			<span class="corners-bottom"><span></span></span></div>
		<?}
	}?>
	<div>
		<div style="float:left; margin:0 5px 5px 0;"><?
			if ($digg_url == '') {
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2007/12/vudu_hd_dvd_blu-ray_killer.php&amp;phase=2&amp;title=VUDU%3A%20HD%20DVD%20%26amp%3B%20Blu-ray%20Killer%3F&amp;bodytext=%20I%20am%20writing%20a%20series%20of%20small%20reviews%20for%20the%202007%20Holiday%20Gadget%20Guide%2C%20hosted%20by%20our%20advertising%20agency%2C%20Federated%20Media.%20One%20of%20the%20%26quot%3Bgadgets%26quot%3B%20I%20reviewed%20recently%20was%20so%20interesting%20I%20thought%20it%20should%20have%20a%20more%20in-depth%20review.%20I%20have%20taken%20that%20original%20review%2C%20and%20gone%20into%20more%20detail%20in%20the%20review%20that%20follows.%20Unless%20you%27ve%20been%20under%20a%20rock%20for%20the%20past%20two%20years%2C%20you%20are%20undoubtedly%20aware%20of%20the%20HD%20DVD%20vs.%20Blu-ray%20%26quot%3BFormat%20War%26quot%3B...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
			} else {
				echo '<script src="http://digg.com/api/diggthis.js"></script>';
			}
		?></div>
		<div style="float:right; margin:0 0 5px 5px;">
			<?include(BASE_DIR .'/ads/mrectangle.php');?>
		</div>
		<div style="clear:right; float:right; margin:0 0 5px 5px;">
			<?include(BASE_DIR .'/ads/skyscraper.php');?>
		</div>
		<div id="<?=$container?>">
			<p></p>  <p></p>  <p>I am writing a series of small reviews for the <a href="http://holidaygadgetguide.federatedmedia.net/" target="_blank">2007 Holiday Gadget Guide</a>, hosted by our advertising agency, Federated Media. One of the &quot;gadgets&quot; I reviewed recently was so interesting I thought it should have a more in-depth review. I have taken that <a href="http://holidaygadgetguide.federatedmedia.net/241" target="_blank">original review</a>, and gone into more detail in the review that follows.</p>  <p>Unless you've been under a rock for the past two years, you are undoubtedly aware of the HD DVD vs. Blu-ray &quot;Format War&quot; going on. The vast majority of you are choosing not to partake in this controversy because you are either waiting for one side to win, or are waiting for video download to be a viable alternative. Perhaps your wait is over.</p>  <h2>The Video Download Market</h2>  <p>The past year or two has seen a number of video download services hit the market. Each has their own strengths and weaknesses and carries with it varied cost, selection and video quality. I've included a brief list below of the major players and a summary of their offerings:</p>  <ul>   <li><strong><img style="margin: 0px" height="106" alt="Xbox Live Marketplace" src="http://upload.wikimedia.org/wikipedia/en/d/da/Xblmlogo.PNG" width="332" align="right" />Microsoft Xbox 360</strong> - Through the Microsoft Xbox 360 console, you can connect to Xbox Live Marketplace and purchase TV shows or rent movies and watch them on whatever TV you have your console connected to. This service launched in November of last year and since then they have accumulated a library of almost 300 TV series across 26 different networks and over 300 movies. All of their movie content is available in 480p, and a quick spot check shows that approximately half of the movie content is available in 720p HD as well. Movie prices range from $3 - $6 for rental only. Currently, they do not have movies available for purchase. </li>    <li><strong><img height="68" src="http://g-ecx.images-amazon.com/images/G/01/digital/video/illustrations/large-logo._V46762529_.png" width="345" align="right" border="0" />Amazon Unbox</strong> - Amazon unbox launched in September of last year and has quite a bit more content, but unless you have a Series 2 or 3 TiVo you will not be able to easily watch it on your main TV because it requires a software downloadable video player to view the content. Their selection is quite a bit better than Xbox Live Marketplace: A quick search on Amazon's site shows that they have just over 1000 TV series available and just under 10,000 movie titles available. Unlike Xbox Live Marketplace, you <strong>can</strong> purchase movies, as opposed to just renting them. Movie rentals are $1 - $4 and purchases are $10 - $15 for most titles. None of it is available in high definition, however. </li>    <li><strong><img src="http://cdn-0.nflximg.com/us/pages/corporate/mediacenter/home/colorlogo.gif" align="right" border="0" />Netflix Watch Instantly</strong> - Launched in January of this year, Netflix's Watch Instantly service provides access to over 6,000 movies and TV episodes. This service is streaming only, so there is no persistent storage of your purchases locally. However, there is no cost for renting because it is included as part of your Netflix membership, which ranges from $5 - $24. The amount of &quot;Watch Instantly&quot; time you get per day is determined by your membership level. At the $5 level you only get 5 hours of viewing, while at the $24 level you get 24 hours of viewing. Netflix Watch Instantly does not have any sort of hardware compatibility yet, so you will have to have a PC connected to your home entertainment system if you want to enjoy these movies on your main TV. Also with Netflix, there is not any high definition content as yet. </li> </ul>  <p>So in just over a year, we've seen a tremendous amount of online video flood the marketplace with varying degrees of quality and pricing, but we have yet to see one that has the ultimate combination of content choice, quality (high definition) video and usability.</p>  <p>Your wait may be over...</p>  <h2>Enter VUDU</h2>  <p><img style="margin: 0px 5px 0px 0px" height="119" alt="image" src="http://holidaygadgetguide.federatedmedia.net/wp-content/themes/hgg/images/content/image-thumb.png" width="200" align="left" border="0" />In September of this year, there was a new entry into the movie download market: <a href="http://www.vudu.com/">VUDU</a>. Unlike the Amazon and Netflix services mentioned above, VUDU is implemented as a hardware device rather than a software program you install on a PC or laptop. You simply connect the VUDU player set-top box to your television just like you would a cable, satellite or digital video recorder. This way you can easily enjoy movies on your larger living room television without the need for a PC or laptop hooked up to it. Another advantage that Vudu may have over most others is that you can <strong>buy</strong> these high definition movies, rather than just rent them.</p>  <h2>The Hardware</h2>  <p>All movies are stored on the Vudu player, which has capacity for 100 hours of owned movies and unlimited rentals. There are also two USB ports on the device that may be used for attaching additional hard drives to increase the storage capacity. In addition to the USB ports, the VUDU player has a variety of audio/video output options, including HDMI, of course.<a href="http://www.hdtvmagazine.com/images/articles/VuduPlayer_129CB/image.png"><img style="border-top-width: 0px; border-left-width: 0px; border-bottom-width: 0px; border-right-width: 0px" height="126" alt="image" src="http://www.hdtvmagazine.com/images/articles/VuduPlayer_129CB/image_thumb.png" width="200" align="right" border="0" /></a> </p>  <table cellspacing="0" cellpadding="2" width="490" border="0"><tbody>     <tr>       <td width="131">Dimensions</td>        <td valign="top" width="357">2.4&quot; H x 8.9&quot; W x 7.3&quot; D, Weight: 4.2 lbs </td>     </tr>      <tr>       <td valign="top" width="133">Storage</td>        <td valign="top" width="357">Unlimited rentals, 100 hours of owned movies </td>     </tr>      <tr>       <td valign="top" width="135">Audio Outputs</td>        <td valign="top" width="357">HDMI v1.1, Digital Optical, Digital Co-ax, RCA</td>     </tr>      <tr>       <td valign="top" width="137">Audio Format</td>        <td valign="top" width="357">Source: Dolby&#174; Digital Plus, Out: Dolby Digital 5.1</td>     </tr>      <tr>       <td valign="top" width="138">Video Outputs</td>        <td valign="top" width="357">HDMI v1.1, Component, S-Video, Composite</td>     </tr>      <tr>       <td valign="top" width="139">Video Resolution</td>        <td valign="top" width="357">1080p/24, 1080i, 720p, 480p, 480i</td>     </tr>      <tr>       <td valign="top" width="140">Connectivity</td>        <td valign="top" width="357">Ethernet, 2 USB ports</td>     </tr>      <tr>       <td valign="top" width="141">Remote control</td>        <td valign="top" width="357">RF, 22ft. range, unobstructed</td>     </tr>   </tbody></table>  <p>As with most internet connected consumer electronics these days, it can receive firmware updates via the internet. When I fired this one up, its first task was to update itself, which only took about 5 minutes total from start to reboot to ready-to-use.</p>  <h2>The Quality</h2>  <p>Also, unlike most other services, the VUDU service provides their movies in high definition. The VUDU player itself will output video in a number of formats to match your television: 1080p/24, 1080i, 720p, 480p and 480i. Yes, that's right, 1080p/24! I have not done any empirical analysis of the scaling components within the player, but the video quality is outstanding. And the HD content is perfect. I compared Bourne Identity with my HD DVD version and could not see any difference.</p>  <p>Where this improves on the content of even the Xbox 360 Live Marketplace is that all HD content on VUDU is encoded in 1080 lines, whereas Live Marketplace content max's out at 720. All SD content is encoded at 480 lines, which is similar to other services.</p>  <h2>The Selection</h2>  <p>Their title selection is quite impressive. Vudu has unprecedented partnerships with every major studio and 18 independent studios and distributors. In total, there are over 5,000 movies available as of this write-up, almost double most traditional video stores. Additional titles are added at a rate of about 5-20 per week. Many of the new titles being added are available for purchase on the same day as the DVD. They become available for rental later, anywhere from 15 to 45 days after release, depending on the studio.</p>  <p>It should also be noted that not all content is available to purchase, and not all content is available for rental. It is up to the studios to determine whether their content is available for rental, purchase or both. Roughly 80% of the content available can be rented, and roughly 80% can be purchased.</p>  <p>The only titles available in HD as of this review are the three &quot;Bourne&quot; flicks: Bourne Identity, Bourne Supremacy and Bourne Ultimatum. More HD content is coming in early 2008 from Universal, Paramount and Lion's Gate.</p>  <p>The movies cost about what you would expect. Rentals range from $0.99 - $3.99. When you rent a movie, it will be available and stored in &quot;My Movies&quot; for up to 30 days. Once you start watching it, you will have 24 hours in which to watch it as many times as you like. Those titles that are available for purchase range from $4.99 - $19.99.</p>  <p>Whether you rent or buy, you may be able to watch the movie immediately if your internet connection is fast enough. If your connection is between 2 and 3 Mbit/s, you can quite likely watch immediately. For slower connections, there may be up to a 30 minute delay before you can start watching in order to give the movie time to buffer. I found it quite gratifying to buy a movie from the comfort of my own living room and being able to start watching it immediately.</p>  <p>VUDU also carries about 1000 TV titles, although these are still in &quot;beta&quot; and not available yet for purchase. All of the TV content to this point is SD only.</p>  <h2>The Interface</h2>  <p><a href="http://www.hdtvmagazine.com/images/articles/VuduPlayer_129CB/image_3.png"><img style="border-right: 0px; border-top: 0px; margin: 0px 0px 5px 5px; border-left: 0px; border-bottom: 0px" height="142" alt="image" src="http://www.hdtvmagazine.com/images/articles/VuduPlayer_129CB/image_thumb_3.png" width="240" align="right" border="0" /></a> It has one of the most intuitive user interfaces I've seen on a consumer electronics device. I much prefer it to the Apple TV interface. The remote control has a clickable scroll wheel, quite like most computer mice, which makes navigation a breeze. Finding movies is quite simple: There are 5 main buttons: Find Movies, New on VUDU, My Movies, My Wish List and Info &amp; Settings. Each of those does about what you'd expect. Additionally, under Find Movies, you can search by genre, title, actor and director, or you can see the most watched movies as well as what's coming soon.</p>  <p>It is also quite easy to find other content you might be interested in related to a particular movie. In addition the the standard rating, runtime, etc. available from within the Movie Details screen, you can also select &quot;Similar Movies&quot; to search for other movies that have similar genres. Or you can scroll through a list of actors and directors and select them to see what other content on the system they are in.</p>  <p>To round out the user interface:</p>  <ul>   <li>Nice shiny buttons with good size pictures of the DVD box art. </li>    <li>HD content is indicated by a little &quot;HD&quot; bug in the upper right hard corner of the title banners. </li>    <li>Full parental controls are available based on rating, along with passcode restriction </li>    <li>Nice little overscan tool that allows you to precisely position the edges of the picture to your screen </li>    <li>Three options for display of 4:3 content: Stretched, Zoomed or Boxed </li>    <li>Ability to set preferred HDMI resolution </li>    <li>Ability to set remote control sensitivity </li>    <li>Displays &quot;hours left&quot; on your rentals </li> </ul>  <h2>The Technology</h2>  <p>All content is encoded as MPEG-4 using variable bitrate encoding. With SD content, the average bitrate hovers around 2Mbit/s, which is DVD quality. The source material varies depending on the studio and how well they manage their digital library. Some content is the same as what went on the DVD while other content is from digital masters. As digital download advances, let's hope the studios digital libraries do as well so that we continue to see an improvement in digital download quality.</p>  <p>One of the key elements that allows VUDU to handle such a large library of content and stream it instantly is Peer-to-peer technology. Roughly 10% of the hard drive of each VUDU box is used to pre-position content that might be popular for upload to other VUDU users. This allows the VUDU system to move massive amounts of traffic and help ensure an instant-on experience for their customers.</p>  <h2>The Future</h2>  <p>What I'd love to see from this hardware is creative use of the USB ports. It's not too far-fetched to imagine that one could one day attach a DVD burner via these USB ports and burn movies off from the hard drive for use in other devices. In a similar vein, it would be very cool if some day this box could interface with a networked PC or Media Server to move content back and forth and play video that resides on other systems.</p>  <h2>So What's the Catch</h2>  <p><a href="http://www.hdtvmagazine.com/images/articles/VuduPlayer_129CB/image_4.png"><img style="border-top-width: 0px; border-left-width: 0px; border-bottom-width: 0px; margin: 0px 5px 0px 0px; border-right-width: 0px" height="240" alt="image" src="http://www.hdtvmagazine.com/images/articles/VuduPlayer_129CB/image_thumb_4.png" width="91" align="left" border="0" /></a>The most obvious hindrance to most is probably the price of the unit. At $399 it is a little pricey, but no more so than an HD DVD or Blu-ray player with equivalent capabilities. For $399, your buying an excellent piece of hardware with clearly the best resolution of any downloadable content I've seen.</p>  <p>The remote may seem a bit weird in appearance, but I was pleasantly surprised how well it felt in my hand. After just a short time, I never had to even look to see if I was pressing the right button. The batteries load on one side toward the back, which makes it a little side-heavy, and hard to set down properly without it toppling over ... but that is a small issue, almost not worth mentioning. The biggest &quot;catch&quot; is probably that the remote is RF-only, and as such I could not &quot;train&quot; my Harmony to work with the VUDU box. I am told an IR USB adapter is in the works.</p>  <p>The only other potential catch is that it is currently only available in the U.S.</p>  <br clear="all" />  <h2>Conclusion</h2>  <p>The VUDU player provides a great movie watching experience without requiring a trip to the DVD shelf/rack/drawer. The cost is equivalent to HD DVD and Blu-ray players with similar audio/video quality, and the price of the content is in-line with what you would encounter in a traditional video store or movie retailer, but without leaving home. Could this be the end of the packaged media &quot;format war&quot;? I'd love to hear your thoughts.</p>  <p><font size="1">Dolby Digital Plus is a registered trademark of Dolby Laboratories.      <br />HDMI is a trademark of HDMI Licensing LLC.</font></p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>December 19, 2007 06:55 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 830
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

			<!-- Related Articles by Category -->
			<div class="item"><span class="corners-top"><span></span></span>
				<h2>More  Articles</h2>
				<ul class="brownsquare"></ul>
			<span class="corners-bottom"><span></span></span></div>
		
			<?if (1 <> 7) {
				# Recent Articles by Author (exclude this one)
				# Do not show recent articles for Bulletins.
				$qry = "
				SELECT entry_id, entry_blog_id, entry_created_on, entry_title, entry_basename, author_id, author_name
				FROM mt_entry e, mt_author a
				WHERE entry_blog_id = 1
					AND entry_id <> 830
					AND entry_status = 2
					AND entry_author_id = a.author_id
					AND a.author_name = 'Shane Sturgeon'
				ORDER BY entry_created_on DESC LIMIT 10";
				$result = mQuery($qry);
				
				if (mysql_num_rows($result) > 0) {
					$row = mysql_fetch_assoc($result);
					echo '<div class="item"><span class="corners-top"><span></span></span>'.
					'<h2><a href="../../author.php?author='. urlencode($row[author_name]) .'&id='. $row[author_id] .'">More from '. $row[author_name] .'</a></h2><ul>';
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
					WHERE entry_blog_id IN (1)
						AND entry_status = 2
						AND entry_author_id = author_id
					GROUP BY author_id, author_name
					ORDER BY num DESC";
					$res_authors = mQuery($qry);
					while ($row_authors = mysql_fetch_assoc($res_authors)) {
						echo '<li><a href="../../../articles/author.php?author='. urlencode($row_authors[author_name]) .'&id='. $row_authors[author_id] .'">'. $row_authors[author_name] .'</a><span class="grey"> ('. $row_authors[num] .')</span></li>';
					}
				?></ul>
			<span class="corners-bottom"><span></span></span></div>

			<div class="item"><span class="corners-top"><span></span></span>
				<h2>Categories</h2>
				<ul class="brownsquare"><?
					$qry = "
					SELECT category_id id, category_label label, COUNT(*) num
					FROM mt_entry e, mt_placement p, mt_category c
					WHERE entry_blog_id = 1
						AND entry_status = 2
						AND entry_id = p.placement_entry_id
						AND p.placement_category_id = c.category_id
					GROUP BY id, label
					ORDER BY label";
					$result = mQuery($qry);
					while ($category = mysql_fetch_assoc($result)) {
						echo '<li><a href="../../category.php?category='. urlencode($category[label]) .'&id='. $category[id] .'">'. $category[label] .'</a><span class="grey"> ('. $category[num] .')</span></li>';
					}
				?></ul>
			<span class="corners-bottom"><span></span></span></div>

		</td>
	</tr></table>

	<?
		include(BASE_DIR .'/includes/body_footer.php');
	?>
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/articles/2007/12/vudu_hd_dvd_blu-ray_killer.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
