<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');

	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "$author[title]<br />";

	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3595 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get enclosure info
	$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3595";
	$res_enclosure = mQuery($sql);
	$row_enclosure = mysql_fetch_assoc($res_enclosure);
	$enclosure_url = $row_enclosure['enclosure_url'];

	# Still need this to track page views. Used in the Links unit in the footer
	$google_links_channel = $author['channel'];

	# Set defaults which may be overridden by blog type below
	$fb_medium_type = 'news';

	# Set variables based on entry type. NOTE: Podcasts (& soon Reviews) has its own entry template, so it is not included amongst the choices below.
	switch (7) {
		case 1: # Articles
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/hdtv-articles" />';
			$feed_tracker = '<script src="http://feeds.feedburner.com/~s/hdtv-articles?i=http://www.hdtvmagazine.com/news/2010/03/samsung_announces_pricing_and_availability_for_two_new_portable_media_players.php" type="text/javascript" charset="utf-8"></script>';
			$container = 'article_container';
			$sub_type = SUB_ARTICLES;
			$sub_label = 'Receive instant notification of new articles';
			if ($userdata['session_logged_in']) {
				$sub_desc = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of new HDTV Magazine Articles via email as soon as they are published.';
			} else {
				$sub_desc = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of new HDTV Magazine Articles via email as soon as they are published.';
			}
			break;
		case 4: # Interviews
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/hdtv-interviews" />';
			$feed_tracker = '<script src="http://feeds.feedburner.com/~s/hdtv-interviews?i=http://www.hdtvmagazine.com/news/2010/03/samsung_announces_pricing_and_availability_for_two_new_portable_media_players.php" type="text/javascript" charset="utf-8"></script>';
			break;
		case 5: # History
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
			$feed_tracker = '<script src="http://feeds.feedburner.com/~s/hdtv-archive?i=http://www.hdtvmagazine.com/news/2010/03/samsung_announces_pricing_and_availability_for_two_new_portable_media_players.php" type="text/javascript" charset="utf-8"></script>';
			break;
		case 6: # Test
			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Samsung Announces Pricing and Availability for Two New Portable Media Players" height="15" width="85"/></a></span>';
			$container = 'article_container';
			$sub_type = 0;
			$sub_label = 'Receive instant notification of "Stuff"';
			if ($userdata['session_logged_in']) {
				$sub_desc = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of "Stuff" via email as soon as they are published.';
			} else {
				$sub_desc = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of "Stuff" via email as soon as they are published.';
			}
			break;
		case 7: # Bulletins
			$google_links_channel = ''; # Don't count bulletins
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/hdtv-news" />';
			$feed_tracker = '<script src="http://feeds.feedburner.com/~s/hdtv-news?i=http://www.hdtvmagazine.com/news/2010/03/samsung_announces_pricing_and_availability_for_two_new_portable_media_players.php" type="text/javascript" charset="utf-8"></script>';
			$container = 'bulletin_container';
			$sub_type = SUB_BULLETINS;
			$sub_label = 'Receive instant notification of HDTV Bulletins';
			if ($userdata['session_logged_in']) {
				$sub_desc = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of HDTV Bulletins via email as soon as they are published.';
			} else {
				$sub_desc = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of HDTV Bulletins via email as soon as they are published.';
			}
			break;
		case 8: # Reviews
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/hdtv-reviews" />';
			$feed_tracker = '<script src="http://feeds.feedburner.com/~s/hdtv-reviews?i=http://www.hdtvmagazine.com/news/2010/03/samsung_announces_pricing_and_availability_for_two_new_portable_media_players.php" type="text/javascript" charset="utf-8"></script>';
			$container = 'article_container';
			$sub_type = SUB_REVIEWS;
			$sub_label = 'Receive instant notification of new reviews';
			if ($userdata['session_logged_in']) {
				$sub_desc = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of new HDTV Magazine Reviews via email as soon as they are published.';
			} else {
				$sub_desc = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of new HDTV Magazine Reviews via email as soon as they are published.';
			}
			break;
		case 9: # Podcasts
			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Samsung Announces Pricing and Availability for Two New Portable Media Players" height="15" width="85"/></a></span>';
/*
			<link rel="image_src" href="audio_image_src url (eg. album art)" />
			<link rel="audio_src" href="audio_src url" />
			<meta name="audio_type" content="Content-Type header field" />
*/
			$container = 'article_container';
			$sub_type = SUB_PODCAST;
			$sub_label = 'Receive instant notification of new episodes';
			if ($userdata['session_logged_in']) {
				$sub_desc_logged_in = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of new episodes of The HDTV Podcast via email as soon as they are published.';
			} else {
				$sub_desc_anon = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of new episodes of The HDTV Podcast via email as soon as they are published.';
			}
			break;
		case 10: # Columns
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/hdtv-columns" />';
			$feed_tracker = '<script src="http://feeds.feedburner.com/~s/hdtv-columns?i=http://www.hdtvmagazine.com/news/2010/03/samsung_announces_pricing_and_availability_for_two_new_portable_media_players.php" type="text/javascript" charset="utf-8"></script>';
			$container = 'article_container';
			$sub_type = SUB_COLUMNS;
			$sub_label = 'Receive instant notification of new columns';
			if ($userdata['session_logged_in']) {
				$sub_desc = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of new HDTV Magazine Columns via email as soon as they are published.';
			} else {
				$sub_desc = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of new HDTV Magazine Columns via email as soon as they are published.';
			}
			$about = 'HDTV Magazine Columns are written by various personalities within the HDTV industry. They are typically shorter than our standard <a href="/articles">Article</a> and quite often express the opinion of the author(s). And of course, opinions expressed by these authors are not necessarily those of HDTV Magazine.';
			break;
		default:
			$container = 'body_container';
			break;
	}

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - Samsung Announces Pricing and Availability for Two New Portable Media Players</title>
	<meta name="keywords" content="samsung electronics, portable media, media players, video content, electronics america, samsung, video, electronics, players, audio, content, experience, media, new, portable, both, quality, america, hours, availability, april, space, sound, pricing, users" />
	<meta name="description" content=" Samsung Announces Pricing and Availability for Two New Portable Media Players The R1 and R0 Satisfy the Needs of Movie and Video Enthusiasts RIDGEFIELD PARK, N.J.--(BUSINESS WIRE)--Samsung Electronics America, Inc., a market leader and award-winning innovator in consumer electronics,..." />
	<meta name="title" content="Samsung Announces Pricing and Availability for Two New Portable Media Players" />
	<meta name="medium" content="<?=$fb_medium_type?>" />
	<!--link rel="image_src" href="" /--><!-- For Facebook Link -->

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />

	<script type="text/javascript">

//		tweetmeme_url = 'http://yoururl.com';
//		tweetmeme_style = 'compact';
		tweetmeme_source = 'HDTVMagazine';
		tweetmeme_service = 'bit.ly';

//		ReTweet settings
/*
		url = 'http://www.hdtvmagazine.com/news/2010/03/samsung_announces_pricing_and_availability_for_two_new_portable_media_players.php';
		size = 'small';
*/
/*
		function fbs_click() {
			u=location.href;
			t=document.title;
			window.open('http://www.facebook.com/sharer.php?u='+encodeURIComponent(u)+'&amp;t='+encodeURIComponent(t),'sharer','toolbar=0,status=0,width=626,height=436');
			return false;
		}
*/
	</script>
	<style>
		#dd_right{
			float:right !important;
			padding:2px !important;
			text-align:right !important;
		}

/*
		#dd_right img {
			border:none !important;
		}
*/

		#dd_right ul {
			padding:0 !important;
			margin:0 !important;
		}

		#dd_right ul li{
			list-style-image:none !important;
			list-style-position:outside !important;
			padding:4px !important;
			margin:0 !important;
			outline:0 none !important;
			background-color:transparent !important;
			border:0 none !important;
			list-style-type:none !important;
			background-image:none !important;
		}

		#dd_right .li_horizontal{
			display:inline !important;
			float:left !important;
		}

		#dd_right .li_vertical{
			display:block !important;
			list-style-type:none !important;
		}
	</style>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header-4.php');

		$base_url = strleftback(PHP_SELF, '/') . '/samsung_announces_pricing_and_availability_for_two_new_portable_media_players';
		$title_encoded = rawurlencode(addslashes('Samsung Announces Pricing and Availability for Two New Portable Media Players'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2010/03/samsung_announces_pricing_and_availability_for_two_new_portable_media_players.php";
		if ($author['img'] != '' && 7 != 7) {
			$img = '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Shane Sturgeon" height="100" width="100"/>';
		} else {$img = '';}

	if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']), "google") === false) {?>
		<!-- Article Header -->
		<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
			<tr>
				<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
				<td class="article_title" colspan="2">Samsung Announces Pricing and Availability for Two New Portable Media Players</td>
			</tr><tr>
			<td id="article_byline" nowrap="nowrap">
					By <b>Shane Sturgeon</b><br />
					<?=$author_title?>
					Posted on <b>March  9, 2010</b><br />
					Category: <b><a href="/category.php?id=<?=$category_id?>&category="></a></b><br />
				</td><td id="article_links">
					<span><script type="text/javascript" src="http://w.sharethis.com/button/sharethis.js#publisher=3da06545-0753-46cb-8739-3ffcef208c1f&amp;type=website&amp;send_services=email&amp;post_services=facebook%2Cdigg%2Cdelicious%2Cstumbleupon%2Cblogger%2Cmyspace%2Cybuzz%2Creddit%2Ctechnorati%2Cmixx%2Cwordpress%2Ctypepad%2Cgoogle_bmarks%2Cwindows_live%2Cfark%2Cbus_exchange%2Cpropeller%2Cnewsvine%2Clinkedin"></script></span>
					<!--span><img src="http://cdn.stumble-upon.com/images/16x16_su_3d.gif" alt="" align="absmiddle" /><a target="_blank" href="http://www.stumbleupon.com/submit?url=http://www.hdtvmagazine.com/news/2010/03/samsung_announces_pricing_and_availability_for_two_new_portable_media_players.php&title=Samsung Announces Pricing and Availability for Two New Portable Media Players">StumbleUpon</a></span>
					<span><img src="<?=BASE_IMG_HOST?>/images/email.gif" alt="Email Article" align="absmiddle" /><a href="<?=$email_url?>">Email</a></span>
					<span><img src="<?=BASE_IMG_HOST?>/images/print.png" alt="Print Article" align="absmiddle" /><a target="_blank" href="<?=$base_url?>-print.php">Print</a></span-->
					<br />
					<br /><br />
				</td>
			</tr>
		</table>
	<?}?>
	<div>
		<div id="right" style="float:right; margin:0 0 5px 5px; text-align:center;" align="center">
			<? include(BASE_DIR .'/ads/mrectangle.php');?>
			<br />
			<div align="center">
				<? include(BASE_DIR .'/ads/skyscraper.php');?>
			</div>
		</div>
		<div id="<?=$container?>">
			<? if ($sub_type > 0 && ($userdata['subscriptions'] & $sub_type)) {} else {?>
				<div class="important" style="display:table"><span class="corners-top"><span></span></span>
					<img src="<?=BASE_IMG_HOST?>/images/i_inbox.gif" alt="" align="left" height="31" width="38" style="float:left; padding-right:10px" />
					<span class="label"><?=$sub_label?>:</span>
					<?=$sub_desc?>
				<span class="corners-bottom"><span></span></span></div>
			<? }?>

			<div id='dd_right'><ul>
					<li class='li_vertical'><script src="http://tweetmeme.com/i/scripts/button.js"></script></li>
					<li class='li_vertical'><a class="DiggThisButton" rel="external" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2010/03/samsung_announces_pricing_and_availability_for_two_new_portable_media_players.php&amp;title=Samsung Announces Pricing and Availability for Two New Portable Media Players"></a></li>
					<li class='li_vertical'><a name="fb_share" type="box_count" href="http://www.facebook.com/sharer.php"></a></li>
			</ul></div>

			<p class="prtitle"> Samsung Announces Pricing and Availability for Two New Portable Media Players</p>

<center><i>The R1 and R0 Satisfy the Needs of Movie and Video Enthusiasts</center></i><br />
<br />

<p><strong>RIDGEFIELD PARK, N.J.--(BUSINESS WIRE)--</strong>Samsung Electronics America, Inc., a market leader and award-winning innovator in consumer electronics, today launched two new portable media players, the R1 and R0, and announced pricing and availability for both. The players deliver high quality multimedia content while optimizing for memory space. The footprint of the R1 is smaller than the size of a credit card and can play a variety of video content without any conversion needed while the R0 delivers an excellent audio and video experience at an affordable value. Samsung's portable media players will be on public display at the Samsung Experience, located at The Time Warner Center in New York City beginning March 9, 2010.</p>

<pre>
PMP 	  	  	Estimated Selling Price	  	Availability
R1 (8GB) 	   	$149.99 	  	  	April 2010
R1 (16GB) 	   	$179.99 	  	  	April 2010
R0 (8GB) 	   	$99.99 	  		  	April 2010
R0 (16GB) 	   	$129.99 	  	  	April 2010
</pre>

<p>"Samsung is keeping the portable media player experience fresh with these two new offerings. We've combined high quality movie and video capability with a superior audio experience all in two compact designs," said Reid Sullivan, senior vice president of the Mobile Entertainment Group at Samsung Electronics America. "The R1 and R0 are a great fit for anyone who wants easy access to all of their favorite music, movies, video and photos."</p>

<p>Offered in black and silver, the R1 offers a responsive, clear 2.4-inch TFT LCD touch screen with a "drag and drop" feature that allows you to easily enjoy audio/video files and libraries with the swipe of a finger. Keeping pace with your busy life, it has video, music and photo playback for up to 50 hours of audio and four hours of video enjoyment. Small in size, the R1 still offers plenty of space for video content and is ideal for popping in your pocket when you're on the go. Within its tiny package, it includes an FM Radio and Recorder as well as voice recording and can also be paired with a Bluetooth TM enabled headset.</p>

<p>The R0 comes in a compact package as well, with a bright 2.6-inch LCD display making the experience of what you watch as bright as what you hear. Navigation is also easy with the addition of tactile keys. Offered in black, silver and pink, it is well suited for those on the run, playing up to six hours of video and 30 hours of audio. For fans who can't get enough of their favorite music, movies and videos, it also offers an option for expanded memory space with the inclusion of a Micro SD slot.</p>

<p>To ensure users get the best in audio quality, both the R1 and R0 incorporate Samsung's Digital Natural Sound Engine (DNSe) 3.0 sound enhancement technology. Being DNSe 3.0 enabled, users can enjoy higher sound quality that reproduces more natural effects and accurately matches the tone and feel originally intended. Additionally, both offer DivX so users can download more video content without sacrificing as much space. Both players come in 8GB and 16GB.</p>

<p>Samsung's press releases, video content and product images are available at <a target="_blank" href="http://www.samsung.com/newsroom/">www.samsung.com/newsroom</a>.</p>

<p><br />
<strong>About Samsung Electronics America, Inc.</strong></p>

<p>Headquartered in Ridgefield Park, NJ, Samsung Electronics America, Inc. (SEA), a wholly owned subsidiary of Samsung Electronics Co., Ltd., markets a broad range of award-winning, digital consumer electronics and home appliance products, including HDTVs, home theater systems, MP3 players, digital imaging products, refrigerators and washing machines. A recognized innovation leader in consumer electronics design and technology, Samsung is the HDTV market leader in the U.S. Please visit <a target="_blank" href="http://www.samsung.com/">www.samsung.com</a> for more information.</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>March  9, 2010  4:58 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 3595
 				AND a.topic_id = t.topic_id
 				AND t.topic_id = p.topic_id
 				AND p.post_id = pt.post_id
 			ORDER BY post_time";
 			$result = mQuery($sql);
 			$num_comments = mysql_num_rows($result);

 			if ($num_comments > 0) {
 				# Skip the first one, as it's just the excerpt post.
 				$row = mysql_fetch_assoc($result);
 				$thread_url = URL_FORUM_VIEWTOPIC .'?t='. $row['topic_id'];
 				echo '<h2 style="margin-bottom:10px"><a href="'. $thread_url .'">Reader Commentary</a></h2>'.
 				'<div class="item"><span class="corners-top"><span></span></span>'.
 					'<img src="'. BASE_IMG_HOST .'/images/icon_topic.gif" alt="" /><b> See Forum Topic</b>: '.
 					'<a href="'. $thread_url .'">'. $row['topic_title'] .'</a> <span class="grey">('. $row['topic_replies'] .' replies)</span>'.
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

 		<? if (7 <> 7) {
 			# Recent Articles by Author (exclude this one)
 			# Do not show recent articles for Bulletins.
 			$qry = "
 			SELECT entry_id, entry_blog_id, entry_created_on, entry_title, author_id, author_name
 			FROM mt_entry e, mt_author a
 			WHERE entry_blog_id = 7
 				AND entry_id <> 3595
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
 				FROM phpbb_topics t, phpbb_users u, phpbb_posts p, aux_phpbb_forums af
 				WHERE
					t.forum_id = af.forum_id
					AND af.exclude_general = 0
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

	<? include(BASE_DIR .'/includes/body_footer.php');?>
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2010/03/samsung_announces_pricing_and_availability_for_two_new_portable_media_players.php" type="text/javascript" charset="utf-8"></script>
	<script src="http://static.ak.fbcdn.net/connect.php/js/FB.Share" type="text/javascript"></script>
	<script src="http://digg.com/api/diggthis.js"></script>
</div></body>
</html>
