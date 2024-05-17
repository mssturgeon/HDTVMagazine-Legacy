<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1461";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1461 AND placement_is_primary = 1";
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
	<meta name="keywords" content="consumer electronics, flat antenna, multi directional, rabbit ears, looking statements, antenna, digital, products, new, flat, reception, rca, RCA, electronics, Antenna, audiovox, consumer, our, may, Flat, directional, Audiovox, ant, statements, company" />
	<meta name="description" content="Viewers looking for an alternative to soaring subscription TV bills should consider pairing a digital TV with a new RCA Flat Antenna designed to pick up more channels than a traditional &quot;rabbit ears&quot; antenna. Local broadcasters are now sending multiple digital TV channels to supplement their main programs, including informational and live radar and weather forecasts available at the touch of a button - for free!

In addition to receiving pristine uncompressed digital TV signals..." />
	<title>HDTV Magazine Bulletins - RCA Multi-Directional Flat Antenna Designed for Pinpoint Pickup of Free Over-the-Air Digital TV Signals</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/rca_multi-directional_flat_antenna_designed_for_pinpoint_pickup_of_free_over-the-air_digital_tv_signals';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('RCA Multi-Directional Flat Antenna Designed for Pinpoint Pickup of Free Over-the-Air Digital TV Signals'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2008/07/rca_multi-directional_flat_antenna_designed_for_pinpoint_pickup_of_free_over-the-air_digital_tv_signals.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">RCA Multi-Directional Flat Antenna Designed for Pinpoint Pickup of Free Over-the-Air Digital TV Signals</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>July 10, 2008</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=DTV Transition">DTV Transition</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2008/07/rca_multi-directional_flat_antenna_designed_for_pinpoint_pickup_of_free_over-the-air_digital_tv_signals.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2008/07/rca_multi-directional_flat_antenna_designed_for_pinpoint_pickup_of_free_over-the-air_digital_tv_signals.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2008/07/rca_multi-directional_flat_antenna_designed_for_pinpoint_pickup_of_free_over-the-air_digital_tv_signals.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2008/07/rca_multi-directional_flat_antenna_designed_for_pinpoint_pickup_of_free_over-the-air_digital_tv_signals.php&amp;phase=2&amp;title=RCA%20Multi-Directional%20Flat%20Antenna%20Designed%20for%20Pinpoint%20Pickup%20of%20Free%20Over-the-Air%20Digital%20TV%20Signals&amp;bodytext=Viewers%20looking%20for%20an%20alternative%20to%20soaring%20subscription%20TV%20bills%20should%20consider%20pairing%20a%20digital%20TV%20with%20a%20new%20RCA%20Flat%20Antenna%20designed%20to%20pick%20up%20more%20channels%20than%20a%20traditional%20%22rabbit%20ears%22%20antenna.%20Local%20broadcasters%20are%20now%20sending%20multiple%20digital%20TV%20channels%20to%20supplement%20their%20main%20programs%2C%20including%20informational%20and%20live%20radar%20and%20weather%20forecasts%20available%20at%20the%20touch%20of%20a%20button%20-%20for%20free%21%0A%0AIn%20addition%20to%20receiving%20pristine%20uncompressed%20digital%20TV%20signals...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p class="prtitle">RCA Multi-Directional Flat Antenna Designed for Pinpoint Pickup of Free Over-the-Air Digital TV Signals</p>

<center><i>Model ANT1500 Eliminates Unsightly "Rabbit Ears" with Patented Technology

<p>New Website Launched to Answer Digital TV Reception Questions</i></center><br /><br />
<br /></p>

<p><B>INDIANAPOLIS--(BUSINESS WIRE)</B>--Viewers looking for an alternative to soaring subscription TV bills should consider pairing a digital TV with a new RCA Flat Antenna designed to pick up more channels than a traditional "rabbit ears" antenna. Local broadcasters are now sending multiple digital TV channels to supplement their main programs, including informational and live radar and weather forecasts available at the touch of a button - for free!</p>

<p>In addition to receiving pristine uncompressed digital TV signals, over-the-air reception offers consumers local broadcasting multicast channels not available via cable or satellite. In some markets, an over-the-air antenna and digital TV receiver will be the viewer's only option for receiving vital weather, traffic, and news information.</p>

<p>And with the Digital TV transition now entering its final months, millions of viewers are now making important decisions about how to stay tuned to their favorite channels once analog TV broadcasting comes to an end in February. In addition to buying a new digital TV or converter box, viewers may also need to think about updating their over-the-air antenna as well.</p>

<p>A new RCA Antenna website - www.staytuned2tv.com -- now gives consumers an easy reference for selecting the proper antenna during the digital TV transition. The site includes easy-to-understand answers to common questions and links to popular digital TV destinations such as AntennaWeb.org and other industry and government websites.</p>

<p>Developed by its Indianapolis-based Research &Development team, the RCA ANT1500 Flat Indoor Antenna offers outstanding reception from multiple stations and minimizes the "cliff effect" of digital TV reception with its unique multi-directional capability. Now shipping to mass merchant and electronics retailers throughout the country, the ANT1500 has a suggested retail price of $59.99.</p>

<p>Designed to blend in with any room décor, the ultra compact antenna (which is less than 10.5" square and less than an inch thick) can be placed flat on a tabletop or hung on a wall. It also comes with a removable metal stand so that it can also easily sit upright if desired.</p>

<p>"Our patented duo-plane design on the RCA Flat Antenna incorporates both VHF and UHF reception on the same multi-directional antenna element. Simply put, this small pizza box-sized antenna can pick up more digital TV stations than a conventional antenna," explains Hank Caskey, Vice President of Reception Products for Audiovox Accessories Corporation, marketer of RCA antenna products. "An old-style 'rabbit ears' antenna works on a highly directional basis, which means you may have to adjust the 'ears' to pick up each individual station. The advantage of a multi-directional Flat Antenna is not just cosmetic. The design is discrete, but the performance is outstanding," Caskey said.</p>

<p>The ANT1500 is the first of seven new RCA antenna products designed especially for digital TV reception that will be introduced this year at suggested retail prices ranging from $14.99 to $99.99. The new offerings range from new designs for a set-top directional antenna (replacing the "rabbit ears" dipoles with flat wings) to a Flat Antenna incorporating the CEA-909 SmartAntenna interface for signal reception in exceptionally difficult environments.</p>

<p>The newly-introduced ANT1500 is ideal for HDTV reception from local broadcasters, as well. Local HDTV signals can be received in full 720progressive or 1080interlace format.</p>

<p>According to the Consumer Electronics Association, sales of digital TV sets are expected to top 30 million units in 2008. And more than 20 million more digital TV converter boxes will be sold before full-power analog TV broadcast signals are switched off in less than eight months.</p>

<p>"Consumers are buying some 20,000 TV antenna products every day, and many plan to connect a new antenna to a new digital TV or converter box. But not every antenna is the same, and we've created the ANT1500 to be the perfect complement to a digital TV receiver - whether that's a new TV or a digital TV converter box," Caskey said.</p>

<p><br />
<B>About Audiovox</B></p>

<p>Audiovox (Nasdaq:VOXX) is a recognized leader in the marketing of automotive entertainment, vehicle security and remote start systems, consumer electronics products and accessories. The company is number one in mobile video and places in the top ten of almost every category that it sells. Among the lines marketed by Audiovox are its mobile electronics products including mobile video systems, auto sound systems including satellite radio, vehicle security and remote start systems; consumer electronics products such as portable DVD players, Portable GPS, flat-panel TV's, extended range two-way radios, multi media products like digital picture frames and home and portable stereos as well as consumer accessories such as indoor/outdoor antennas, connectivity products, headphones, speakers, wireless solutions, remote controls, power & surge protectors and media cleaning & storage devices. The company markets its products through an extensive distribution network that includes power retailers, 12-volt specialists, mass merchandisers and an OE sales group. The company markets products under the Audiovox, Jensen, Acoustic Research, Advent, Code Alarm, Terk, and Prestige brands, as well as the recently-acquired rights from Thomson's America's consumer electronics accessory business to the RCA brand for Consumer Electronics accessories. The acquisition also includes the Recoton, Spikemaster, Ambico and Discwasher brands for use on any products and the Jensen, Advent, Acoustic Research and Road Gear brands for accessory products. Audiovox already owns Jensen, Advent, Acoustic Research and Road Gear brands for electronics products as part of prior acquisitions. For additional information, visit our web site at www.audiovox.com.</p>

<p>Except for historical information contained herein, statements made in this release that would constitute forward-looking statements may involve certain risks and uncertainties. All forward-looking statements made in this release are based on currently available information and the Company assumes no responsibility to update any such forward-looking statements. The following factors, among others, may cause actual results to differ materially from the results suggested in the forward-looking statements. The factors include, but are not limited to, risks that may result from changes in the Company's business operations; our ability to keep pace with technological advances; significant competition in the mobile and consumer electronics businesses; our relationships with key suppliers and customers; quality and consumer acceptance of newly introduced products; market volatility; non-availability of product; excess inventory; price and product competition; new product introductions; the possibility that the review of our prior filings by the SEC may result in changes to our financial statements; and the possibility that stockholders or regulatory authorities may initiate proceedings against Audiovox and/or our officers and directors as a result of any restatements. Risk factors associated with our business, including some of the facts set forth herein, are detailed in the Company's Form 10-K for the fiscal year ended February 28, 2007.</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>July 10, 2008 12:00 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 1461
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
 			<h2>More on DTV Transition</h2><ul><?
 			$sql = "
 			SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name
 			FROM mt_entry e, mt_author a, mt_placement p, mt_category c
 			WHERE entry_author_id = author_id
 				AND e.entry_id = p.placement_entry_id
 				AND p.placement_category_id = c.category_id
 				AND category_label = 'DTV Transition'
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
 				AND entry_id <> 1461
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2008/07/rca_multi-directional_flat_antenna_designed_for_pinpoint_pickup_of_free_over-the-air_digital_tv_signals.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
