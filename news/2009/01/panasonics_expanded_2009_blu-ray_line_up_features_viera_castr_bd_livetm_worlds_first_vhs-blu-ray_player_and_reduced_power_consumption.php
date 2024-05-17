<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');

	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1632";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);

	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";

	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1632 AND placement_is_primary = 1";
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
	<meta name="keywords" content="blu ray, ray players, viera cast, power consumption, vhs blu, blu, ray, Blu, panasonic, Panasonic, players, DMP, dmp, viera, VIERA, player, power, consumer, high, first, technology, video, audio, vhs, VHS" />
	<meta name="description" content="Panasonic, a major developer and contributor to the success of the Blu-ray format, today introduced the successors to last year's award winning DMP-BD35 and DMP-BD55 players, as well as presenting the world's first Blu-ray-VHS dual player. All three of the new Blu-ray players - DMP-BD60, DMP-BD80, DMP-BD70V - combine high quality images with enhanced networking functions, including VIERA Cast's improved internet functionality that provides access to Amazon VOD's huge selection of titles. Continuing its commitment to producing products that stress ease of use, the 2009 line of Blu-ray Disc(TM) players continue to incorporate..." />
	<title>HDTV Magazine Bulletins - Panasonic's Expanded 2009 Blu-ray Line Up Features VIERA CAST(R), BD Live(TM), World's First VHS-Blu-ray Player and Reduced Power Consumption</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
//		var federated_media_section = 'holiday';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/ads/leaderboard.php');
		include(BASE_DIR .'/includes/body_header.php');

		$base_url = strleftback(PHP_SELF, '/') . '/panasonics_expanded_2009_blu-ray_line_up_features_viera_castr_bd_livetm_worlds_first_vhs-blu-ray_player_and_reduced_power_consumption';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Panasonic\'s Expanded 2009 Blu-ray Line Up Features VIERA CAST(R), BD Live(TM), World\'s First VHS-Blu-ray Player and Reduced Power Consumption'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2009/01/panasonics_expanded_2009_blu-ray_line_up_features_viera_castr_bd_livetm_worlds_first_vhs-blu-ray_player_and_reduced_power_consumption.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Panasonic's Expanded 2009 Blu-ray Line Up Features VIERA CAST(R), BD Live(TM), World's First VHS-Blu-ray Player and Reduced Power Consumption</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>January  7, 2009</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=HD DVD & Blu-ray">HD DVD & Blu-ray</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2009/01/panasonics_expanded_2009_blu-ray_line_up_features_viera_castr_bd_livetm_worlds_first_vhs-blu-ray_player_and_reduced_power_consumption.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2009/01/panasonics_expanded_2009_blu-ray_line_up_features_viera_castr_bd_livetm_worlds_first_vhs-blu-ray_player_and_reduced_power_consumption.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2009/01/panasonics_expanded_2009_blu-ray_line_up_features_viera_castr_bd_livetm_worlds_first_vhs-blu-ray_player_and_reduced_power_consumption.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2009/01/panasonics_expanded_2009_blu-ray_line_up_features_viera_castr_bd_livetm_worlds_first_vhs-blu-ray_player_and_reduced_power_consumption.php&amp;phase=2&amp;title=Panasonic%27s%20Expanded%202009%20Blu-ray%20Line%20Up%20Features%20VIERA%20CAST%28R%29%2C%20BD%20Live%28TM%29%2C%20World%27s%20First%20VHS-Blu-ray%20Player%20and%20Reduced%20Power%20Consumption&amp;bodytext=Panasonic%2C%20a%20major%20developer%20and%20contributor%20to%20the%20success%20of%20the%20Blu-ray%20format%2C%20today%20introduced%20the%20successors%20to%20last%20year%27s%20award%20winning%20DMP-BD35%20and%20DMP-BD55%20players%2C%20as%20well%20as%20presenting%20the%20world%27s%20first%20Blu-ray-VHS%20dual%20player.%20All%20three%20of%20the%20new%20Blu-ray%20players%20-%20DMP-BD60%2C%20DMP-BD80%2C%20DMP-BD70V%20-%20combine%20high%20quality%20images%20with%20enhanced%20networking%20functions%2C%20including%20VIERA%20Cast%27s%20improved%20internet%20functionality%20that%20provides%20access%20to%20Amazon%20VOD%27s%20huge%20selection%20of%20titles.%20Continuing%20its%20commitment%20to%20producing%20products%20that%20stress%20ease%20of%20use%2C%20the%202009%20line%20of%20Blu-ray%20Disc%28TM%29%20players%20continue%20to%20incorporate...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p class="prtitle">Panasonic's Expanded 2009 Blu-ray Line Up Features VIERA CAST(R), BD Live(TM), World's First VHS-Blu-ray Player and Reduced Power Consumption</p>

<center><i>Amazon Video-on-Demand Included in VIERA CAST For 2009</i></center><br />
<br />

<p><B>LAS VEGAS, Jan. 7 /PRNewswire-FirstCall/</B> -- Panasonic, a major developer and contributor to the success of the Blu-ray format, today introduced the successors to last year's award winning DMP-BD35 and DMP-BD55 players, as well as presenting the world's first Blu-ray-VHS dual player. All three of the new Blu-ray players - DMP-BD60, DMP-BD80, DMP-BD70V - combine high quality images with enhanced networking functions, including VIERA Cast's improved internet functionality that provides access to Amazon VOD's huge selection of titles. Continuing its commitment to producing products that stress ease of use, the 2009 line of Blu-ray Disc(TM) players continue to incorporate VIERA Link(TM), allowing the consumer to operate their audio/video components, via HDMI, with one remote. And, in keeping with Panasonic's pledge to reduce the planet's carbon footprint, the new Blu-ray players have been designed to reduce power consumption.</p>

<p>Each of the three models employs the PHL Reference Chroma Processor Plus. Developed in collaboration with Panasonic Hollywood Laboratory, this high image processing technology reproduces clear, vivid colors that are faithful to the original film. Recognizing that audio is important to the overall entertainment experience, Panasonic's three 2009 Blu-ray Disc players feature a high definition audio decoder (Dolby(R) Digital Plus, Dolby(R) TrueHD, DTS-HD Master Audio Essential) to take advantage of the exceptionally high quality 7.1 channel surround sound now integrated in Blu-ray Discs. The DMP-BD70V distinguishes itself as the world's first dual deck VHS-Blu-ray player, providing the consumer with a video product that features multi-format playback allowing the user to play VHS, CD, DVD and 1080p high definition Blu-ray Discs. The BD70V allows for premium 1080p up-conversion for all video formats.</p>

<p>VIERA CAST technology, introduced in Panasonic's PZ850 2008 VIERA Plasma, is now available in Panasonic's 2009 Blu-ray players. The internet enabled technology lets the consumer access the internet without the need of either an external box or a PC and enjoy the entertainment value provided by such targeted sites as Amazon VOD, with an extensive library of streamed titles, YouTube(TM), Google's Picasa(TM) Web Album, Bloomberg and a weather channel. The DMP-BD60, DMP-BD80 and DMP-BD70V include an SD Memory card slot and USB slot, making it easy for the consumer to view and share both digital still images and HD video recorded with an HD camcorder in the AVCHD format.</p>

<p>"With the expansion of the unique VIERA Cast functionality and the introduction of the industry's first dual VHS-Blu-ray deck, Panasonic's 2009 line cements our position as technology leaders and places Panasonic in the forefront of the Blu-ray arena," said Richard Simone, Director, Panasonic, the Entertainment Group. "Panasonic was the first company to produce a Blu-ray player with Bonus View and the first to incorporate BD Live functionality into a stand alone player. Now we are the first to bring to market a dual VHS-Blu ray player. When coupled with a Panasonic HDTV, Blu-ray gives the consumer the essential 1080p high definition experience."</p>

<p>In order to produce the ultimate picture quality, Panasonic's Blu-ray players employ high precision 4:4:4 signal technology, which working in tandem with PHL Reference Chroma Processor Plus processes each pixel of the Blu-ray Disc video signal in the horizontal direction, to compliment vertical direction processing. P4HD (Pixel Precision Progressive Processing for HD) is another technology that contributes to the superior picture quality of the Blu-ray players. P4HD processes more than 15 billion pixels per second and applies the optimal processing to every pixel. Panasonic's Blu-ray players further utilize 16-level motion detection to categorize the image motion of each pixel into one of 16 levels; diagonal processing to detect diagonals and correct the pixels accordingly; 1080p up-conversion to up-convert content recorded in the 480i/p or 720p format to 1080p. The Blu-ray players also provide 1080/24p output, thereby reproducing cinema images from a Blu-ray Disc and DVD in their original 24p form with no need for conversion. This allows the user to enjoy cinema images in the same format used in cinema with a 1080/24p compatible TV.</p>

<p>Complimenting the HD audio codecs the three Blu-ray players feature 96kHz surround re-master, a function that enhances the sound quality of CDs and other sources, and even improves the quality of the multi-channel audio data on Blu-ray Discs and DVDs. The DMP-BD80 further enhances the audio experience with 7.1 channel analog out to produce true 7.1 surround sound, thereby affording the consumer a home theater environment that rivals the movie theater. The BD80 also includes a playback information window that can be used to display detailed image information while a movie is playing.</p>

<p>In order to obtain a reduction in power consumption the 2009 Blu-ray players use Auto Power Stand-By, a function that automatically turns off the player when you return to TV operation using the VIERA Link menu. In addition, the Stand-by Power Save automatically turns off the player's Quick Start function. When VIERA is turned on, Quick Start also turns on. In addition, the development of the UniPhier(R) single chip LSI makes it possible to pack an entire video signal processing circuit onto a single chip. This helps lower power consumption, reduces the number of parts needed and allows for a more compact design. The BD60 consumes 16% less power in standby mode than last year's model, the DMP-BD35.</p>

<p>About Panasonic Consumer Electronics Company</p>

<p>Based in Secaucus, N.J., Panasonic Consumer Electronics Company (PCEC), a market and technology leader in High Definition television, is a Division of Panasonic Corporation of North America, the principal North American subsidiary of Panasonic Corporation (NYSE:PC) and the hub of Panasonic's U.S. marketing, sales, service and R&D operations. Panasonic is pledged to practice prudent, sustainable use of the earth's natural resources and protect our environment through the company's Eco Ideas programs. Information about Panasonic products is available at www.panasonic.com. Additional company information for journalists is available at www.panasonic.com/pressroom.</p>

<p>Source: Panasonic </p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>January  7, 2009 04:53 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 1632
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
 				AND entry_id <> 1632
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

	<?
		include(BASE_DIR .'/includes/body_footer.php');
	?>
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2009/01/panasonics_expanded_2009_blu-ray_line_up_features_viera_castr_bd_livetm_worlds_first_vhs-blu-ray_player_and_reduced_power_consumption.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
