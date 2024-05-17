<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 702";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 702 AND placement_is_primary = 1";
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
	<meta name="keywords" content="ray disc, blu ray, high definition, pioneer elite, disc player, high, home, pioneer, Pioneer, disc, blu, ray, player, Disc, Blu, elite, Elite, hdmi, audio, HDMI, BDP, bdp, theater, definition, digital" />
	<meta name="description" content="High resolution picture and sound performance come together in the new Pioneer&amp;reg; Elite&amp;reg; BDP-95FD Blu-ray Disc&amp;reg; player introduced at the CEDIA Expo today. Pioneer Electronics (USA) Inc. is showcasing its highly anticipated player, the industry's first to offer bitstream output of all advanced audio formats including: Dolby&amp;reg; TrueHD, Dolby&amp;reg; Digital+, DTS-HD&amp;trade; High Resolution and DTS-HD&amp;trade; Master Audio. The BDP-95FD ensures a near cinematic experience with its ability to handle 1080p 24 frames per second (fps) reproduction rate preserving a feature film's original sequence. Taking advantage of HDMI&amp;reg; 1.3 connectivity, the new player provides smooth, pristine imagery and dynamic 7.1 surround sound to deliver the full emotional impact of Blu-ray Disc feature films as the director intended.

The Pioneer Elite Blu-ray Disc player leads the industry with sophisticated home theater offerings including HDMI Consumer Electronics Control (CEC) to ensure seamless integration with other high definition theater components. In addition..." />
	<title>HDTV Magazine Bulletins - New Pioneer Elite Blu-Ray Disc Player Sets Benchmark for High Performance Video and Audio Playback</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/new_pioneer_elite_blu-ray_disc_player_sets_benchmark_for_high_performance_video_and_audio_playback';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('New Pioneer Elite Blu-Ray Disc Player Sets Benchmark for High Performance Video and Audio Playback'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2007/09/new_pioneer_elite_blu-ray_disc_player_sets_benchmark_for_high_performance_video_and_audio_playback.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">New Pioneer Elite Blu-Ray Disc Player Sets Benchmark for High Performance Video and Audio Playback</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>September  6, 2007</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=HD DVD & Blu-ray">HD DVD & Blu-ray</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2007/09/new_pioneer_elite_blu-ray_disc_player_sets_benchmark_for_high_performance_video_and_audio_playback.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2007/09/new_pioneer_elite_blu-ray_disc_player_sets_benchmark_for_high_performance_video_and_audio_playback.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2007/09/new_pioneer_elite_blu-ray_disc_player_sets_benchmark_for_high_performance_video_and_audio_playback.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2007/09/new_pioneer_elite_blu-ray_disc_player_sets_benchmark_for_high_performance_video_and_audio_playback.php&amp;phase=2&amp;title=New%20Pioneer%20Elite%20Blu-Ray%20Disc%20Player%20Sets%20Benchmark%20for%20High%20Performance%20Video%20and%20Audio%20Playback&amp;bodytext=High%20resolution%20picture%20and%20sound%20performance%20come%20together%20in%20the%20new%20Pioneer%26reg%3B%20Elite%26reg%3B%20BDP-95FD%20Blu-ray%20Disc%26reg%3B%20player%20introduced%20at%20the%20CEDIA%20Expo%20today.%20Pioneer%20Electronics%20%28USA%29%20Inc.%20is%20showcasing%20its%20highly%20anticipated%20player%2C%20the%20industry%27s%20first%20to%20offer%20bitstream%20output%20of%20all%20advanced%20audio%20formats%20including%3A%20Dolby%26reg%3B%20TrueHD%2C%20Dolby%26reg%3B%20Digital%2B%2C%20DTS-HD%26trade%3B%20High%20Resolution%20and%20DTS-HD%26trade%3B%20Master%20Audio.%20The%20BDP-95FD%20ensures%20a%20near%20cinematic%20experience%20with%20its%20ability%20to%20handle%201080p%2024%20frames%20per%20second%20%28fps%29%20reproduction%20rate%20preserving%20a%20feature%20film%27s%20original%20sequence.%20Taking%20advantage%20of%20HDMI%26reg%3B%201.3%20connectivity%2C%20the%20new%20player%20provides%20smooth%2C%20pristine%20imagery%20and%20dynamic%207.1%20surround%20sound%20to%20deliver%20the%20full%20emotional%20impact%20of%20Blu-ray%20Disc%20feature%20films%20as%20the%20director%20intended.%0A%0AThe%20Pioneer%20Elite%20Blu-ray%20Disc%20player%20leads%20the%20industry%20with%20sophisticated%20home%20theater%20offerings%20including%20HDMI%20Consumer%20Electronics%20Control%20%28CEC%29%20to%20ensure%20seamless%20integration%20with%20other%20high%20definition%20theater%20components.%20In%20addition...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p class="prtitle">New Pioneer Elite Blu-Ray Disc Player Sets Benchmark for High Performance Video and Audio Playback</p>

<center><i>New Player is Industry's First to Offer HDMI 1.3 Connectivity for Lossless Advanced Audio Decoding</i></center><br />
<br />

<p>CEDIA EXPO 2007<br />
Booth #740</p>

<p><B>DENVER--(BUSINESS WIRE)</B>--High resolution picture and sound performance come together in the new Pioneer&reg; Elite&reg; BDP-95FD Blu-ray Disc&reg; player introduced at the CEDIA Expo today. Pioneer Electronics (USA) Inc. is showcasing its highly anticipated player, the industry's first to offer bitstream output of all advanced audio formats including: Dolby&reg; TrueHD, Dolby&reg; Digital+, DTS-HD&trade; High Resolution and DTS-HD&trade; Master Audio. The BDP-95FD ensures a near cinematic experience with its ability to handle 1080p 24 frames per second (fps) reproduction rate preserving a feature film's original sequence. Taking advantage of HDMI&reg; 1.3 connectivity, the new player provides smooth, pristine imagery and dynamic 7.1 surround sound to deliver the full emotional impact of Blu-ray Disc feature films as the director intended.</p>

<p>The Pioneer Elite Blu-ray Disc player leads the industry with sophisticated home theater offerings including HDMI Consumer Electronics Control (CEC) to ensure seamless integration with other high definition theater components. In addition, movies, music and photos can be easily streamed from a home PC to connected 1080p television via the player's enhanced home networking functionality for unrivaled entertainment.</p>

<p>"Our newest Elite BDP-95FD player is like having a movie theater projector delivering best-in-class picture and sound at home. Its high performance is a testament to our optical disc heritage and commitment to delivering the ultimate home theater by introducing lossless high resolution audio that entertainment junkies will truly appreciate," said Chris Walker, senior manager of marketing and product planning at Pioneer Electronics (USA) Inc. "When combined with a KURO television and Elite A/V receiver, the BDP-95FD immerses home audiences in a seeing and hearing experience like never before."</p>

<p><br />
<B>Next Generation High Definition Performance</B></p>

<p>Leading high definition home theater, the BDP-95FD delivers unprecedented picture and sound as a result of HDMI 1.3 capability. The new generation HDMI version is designed for smoother connectivity to emerging 1080p flat panel televisions and other high resolution devices. With increased bandwidth capacity, the Pioneer Elite Blu-ray Disc player can transfer larger amounts of uncompressed high definition video and audio resolutions as well as standard DVD that will immerse viewers in an unforgettable viewing experience.</p>

<p>Stunning colors and enhanced picture reproduction are complimented by the player's additional support of lossless digital audio formats Dolby&reg; TrueHD, Dolby&reg; Digital+, DTS-HD&trade; High Resolution Audio and DTS-HD&trade; Master Audio. As a result of HDMI 1.3, the BDP-95FD brings the highest quality sound performance of Blu-ray Disc film titles to the living room giving audiophiles up to 7-channels of pristine audio that defines ultimate home theater.</p>

<p><br />
<B>High Definition Integration Made Simple</B></p>

<p>Streamlining the integration of multiple home theater components, the BDP-95FD offers HDMI-CEC technology. This new convenience feature synchronizes the Blu-ray Disc player with other CEC-enabled products and controls an entire setup with a single remote. With an easy to navigate graphical user interface, HDMI-CEC requires minimal user effort while ensuring premium performance and maximum entertainment.</p>

<p><br />
<B>Cinematic Picture At Home</B></p>

<p>Mastered at 1080p 24fps, Blu-ray Disc movie titles preserve a feature film's original sequence to faithfully deliver stellar image quality. Pioneer engineers designed the Elite BDP-95FD Blu-ray Disc to handle and output high performance 1080p 24fps signal for natural, pristine film reproduction as the director intended.</p>

<p><br />
<B>Home Media Gallery</B></p>

<p>Digital media fans can playback their favorite downloaded video and music files as well as personal photos straight from a home PC hard drive to their high definition television through Pioneer's exclusive Home Media Gallery. This home networking feature provides users a rich, high definition graphical interface with fast navigation to search, select and play desired content. Home Media Gallery offers IP networking for quick access and downloading of new digital media files straight from the computer for immediate viewing through the BDP-95FD on a connected flat screen television. The Pioneer Elite Blu-ray Disc player is compliant with Digital Living Network Alliance (DLNA) sources, as well as Microsoft Windows XP, Vista, and Media Center editions, as well as 3rd party DLNA server software available for both Mac and Linux based computers.</p>

<p>The BDP-95FD arrives at specialty retailers in October for a suggested price of $1000.</p>

<p>Pioneer's Home Entertainment and Business Solutions Group develops high definition home theater equipment for sports and entertainment junkies. Its flat panel televisions, Blu-ray Disc players, A/V receivers and speakers heighten the emotions created by great HD content. The company brands include Pioneer&reg; and Elite&reg;. When purchased from an authorized retailer, consumers receive a limited warranty for one year with Pioneer products and two years with Elite products. More details can be located at www.pioneerelectronics.com.</p>

<p>PIONEER, the PIONEER logo, the ELITE logo and PureVision are registered trademarks of the Pioneer Corporation.</p>

<p>DOLBY and the double-D symbol are registered trademarks of Dolby Laboratories.</p>

<p>HDMI, the HDMI logo and High-Definition Multimedia Interface are trademarks or registered trademarks of HDMI Licensing LLC.</p>

<p>DTS and DTS Digital Surround are registered trademarks of Digital Theater Systems, Inc.</p>

<p>Windows, Windows Media are either registered trademarks or trademarks of Microsoft Cooperation in the United States and/or other countries and are used under license from Microsoft.</p>

<p>DLNA and DLNA CERTIFIED are trademarks and/or servicemarks of Digital Network Alliance.</p>

<p>BLU-RAY DISC is a registered trademark of Sony Corporation.</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>September  6, 2007 11:36 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 702
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
 			<h2>More on HD DVD & Blu-ray</h2><ul><?
 			$sql = "
 			SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name
 			FROM mt_entry e, mt_author a, mt_placement p, mt_category c
 			WHERE entry_author_id = author_id
 				AND e.entry_id = p.placement_entry_id
 				AND p.placement_category_id = c.category_id
 				AND category_label = 'HD DVD & Blu-ray'
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
 				AND entry_id <> 702
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2007/09/new_pioneer_elite_blu-ray_disc_player_sets_benchmark_for_high_performance_video_and_audio_playback.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
