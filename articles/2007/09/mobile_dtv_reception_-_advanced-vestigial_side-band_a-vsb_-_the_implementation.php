<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 685";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Rodolfo La Maestra'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 685 AND placement_is_primary = 1";
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
	<meta name="keywords" content="mobile dtv, dtv reception, vestigial side, reception advanced, advanced vestigial, system, mobile, bandwidth, using, reception, dtv, turbo, DTV, avsb, channel, AVSB, programming, MHz, mhz, same, Samsung, channels, SRS, srs, quality" />
	<meta name="description" content="In the first two articles, I covered the details of how this mobile DTV system works, the requirements, and some potential effects on HDTV channels if not used with quality of terrestrial broadcast in mind.

In April 2007, I privately discussed with Mr. John Godfrey, VP of government and public affairs for Samsung Information Systems America, to confirm some of the technical aspects of AVSB." />
	<title>HDTV Magazine Articles - Mobile DTV Reception - Advanced-Vestigial Side-Band (A-VSB) - The Implementation</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/mobile_dtv_reception_-_advanced-vestigial_side-band_a-vsb_-_the_implementation';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Mobile DTV Reception - Advanced-Vestigial Side-Band (A-VSB) - The Implementation'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/articles/2007/09/mobile_dtv_reception_-_advanced-vestigial_side-band_a-vsb_-_the_implementation.php";
		if ($author[img] != '' && 1 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Rodolfo La Maestra" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Mobile DTV Reception - Advanced-Vestigial Side-Band (A-VSB) - The Implementation</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Rodolfo La Maestra</b><br />
				<?=$author_title?>
				Posted on <b>September  3, 2007</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Technology">Technology</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2007/09/mobile_dtv_reception_-_advanced-vestigial_side-band_a-vsb_-_the_implementation.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/articles/2007/09/mobile_dtv_reception_-_advanced-vestigial_side-band_a-vsb_-_the_implementation.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/articles/2007/09/mobile_dtv_reception_-_advanced-vestigial_side-band_a-vsb_-_the_implementation.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2007/09/mobile_dtv_reception_-_advanced-vestigial_side-band_a-vsb_-_the_implementation.php&amp;phase=2&amp;title=Mobile%20DTV%20Reception%20-%20Advanced-Vestigial%20Side-Band%20%28A-VSB%29%20-%20The%20Implementation&amp;bodytext=In%20the%20first%20two%20articles%2C%20I%20covered%20the%20details%20of%20how%20this%20mobile%20DTV%20system%20works%2C%20the%20requirements%2C%20and%20some%20potential%20effects%20on%20HDTV%20channels%20if%20not%20used%20with%20quality%20of%20terrestrial%20broadcast%20in%20mind.%0A%0AIn%20April%202007%2C%20I%20privately%20discussed%20with%20Mr.%20John%20Godfrey%2C%20VP%20of%20government%20and%20public%20affairs%20for%20Samsung%20Information%20Systems%20America%2C%20to%20confirm%20some%20of%20the%20technical%20aspects%20of%20AVSB.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<div class="editorial">If you haven't done so already, please be sure to read the other articles in this series:
<ul>
<li><a href="/articles/2007/08/mobile_dtv_reception_-_advanced-vestigial_side-band_a-vsb_-_the_system.php">Mobile DTV Reception - Advanced-Vestigial Side-Band (A-VSB) - The System</a></li>
<li><a href="/articles/2007/08/mobile_dtv_reception_-_advanced-vestigial_side-band_a-vsb_-_bandwidth_requirements.php">Mobile DTV Reception - Advanced-Vestigial Side-Band (A-VSB) - Bandwidth Requirements</a></li>
<li><a href="/articles/2007/09/mobile_dtv_reception_-_advanced-vestigial_side-band_a-vsb_-_impact_analysis.php">Mobile DTV Reception - Advanced-Vestigial Side-Band (A-VSB) - Impact Analysis</a></li>
</ul></div>
<br />
In the first two articles, I covered the details of how this mobile DTV system works, the requirements, and some potential effects on HDTV channels if not used with quality of terrestrial broadcast in mind.

<p><br />
<B>A Private Conference with Samsung</B></p>

<p>In April 2007, I privately discussed with Mr. John Godfrey, VP of government and public affairs for Samsung Information Systems America, to confirm some of the technical aspects of AVSB.</p>

<p>I started by sharing my concern about AVSB implementation for mobile devices sharing the bandwidth of HDTV channels within the 6MHz allocation, which has the potential to degrade its quality.</p>

<p>John said that Samsung loves HDTV, they want 1080p, they want quality, and they want the HD look great in their HDTV.</p>

<p>Many consider 15-16 Mbps acceptable quality for HD, and out of the total 19Mbps there would be enough left for mobile programming if sharing the same 6MHz channel allocation.</p>

<p><br />
<B>The Turbo Power</B></p>

<p>As mentioned above, at CES 2007, the AVSB system was demo using a bandwidth of 4 times the video signal requirements to demonstrate that it works. The system was able to provide an image of &frac14; VGA quality (320x240 resolution) using H.264 codec and encoding the video and audio at about 500 kbps. Multiply that x 4 to get the size of the turbo stream. That application would be useful for screens up to 12-13 inches for automobile use.</p>

<p>At NAB 2007, Samsung showed a successful reception even when using only half of that rate, meaning using a total bandwidth that was twice the required by the video/audio itself for the requirements of the turbo system to facilitate the lock of the receiving device into the signal.</p>

<p><br />
<B>The SRS System</B></p>

<p>In addition to the turbo system there is a SRS system that uses a tracking signal that requires 2.89 Mbps at its maximum rate. SRS is in use all the time and improves the reception of all services, terrestrial OTA DTV using 8VSB and mobile DTV using the turbo system of AVSB.</p>

<p><br />
<B>Using Both</B></p>

<p>The turbo and the SRS are needed together in order to get mobile reception, but one could run SRS and no Turbo to just improve the resistance to dynamic interference echoes on stationary and low speed portable reception applications, such as cars driving by the house, or staying in a cafe with people walking around.</p>

<p>Using turbo at half-load (half video/half overhead) added to SRS would still leave about 15-16 Mbps for the main terrestrial HD channel within the 6 MHz slot.</p>

<p>However, using a 6MHz channel slot for the simultaneous transmission of HD, SD, and A-VSB mobile is considered a challenge and a risk to the quality of the HD sub-channel.</p>

<p><br />
<B>Borrowing Bandwidth from Other Stations</B></p>

<p>Another alternative is to transmit mobile programming using another station within the same market, which is very common in the US, and would avoid taking Mbps out of the main HD channel.</p>

<p>For example, many NBC stations are in markets where there is a Telemundo station; Telemundo does not have significant HD programming, if any, and does not use all of the 6 MHz allocated bandwidth, so Telemundo could dedicate some of the unused bandwidth for the purpose of supporting the mobile service of another station, such as NBC within the same market.</p>

<p>There are also independent stations that are not own by the network stations but they could reach agreements to perform a mobile broadcast service similar to the above.</p>

<p>When looking around the United States, after analog broadcast is discontinued in Feb 2009, there will be a repacking of the digital stations on the channels 2 to 51, which is almost 50 channels (1 channel is left out in the middle). There will be a total bandwidth of 50 x 6MHz channels to work with, not all of them support each city but supposing that half of them are used, there will be a total bandwidth of about 25 x 6MHz channels to work with on each city.</p>

<p>In the view of Samsung, there are not 25 channels worth of HD broadcast programming in the US, "we are a long way from that" John said. So there is plenty of bandwidth for all of the HD programming to be transmitted at its full HD quality, and there is still bandwidth to perform a role of mobile distribution of services that could be transmitted using other frequencies in the same market.</p>

<p>As the Telemundo example above, if in one market there were a station that only transmits their programming in SD, the station would have most of the 6 MHz channel allocation available, and could be divided into several multi-turbo streams for mobile services of 5 or 6 network stations (NBC, CBS, etc).</p>

<p>From the beginning the system will have the built-in ability to send to the receiver the encoding rates of the transmitting station, so the broadcaster can change the encoding rate and the receiver would automatically adjust to it; there is nothing to be done by the consumer regarding software upgrades on the receivers.</p>

<p><br />
<B>The Audio System for AVSB</B></p>

<p>The audio system to be implemented with AVSB has not been decided yet. We discussed about using Dolby Digital Plus, which was approved by the ATSC a few years ago as an alternative standard for DTV audio, and claims to offer 50% bandwidth savings over the current Dolby Digital standard used for DTV.</p>

<p>I mentioned to John my conversations with Craig Eggers from Dolby to make them both aware that there could be an opportunity for both efforts to work together.</p>

<p><br />
<B>SFN Tower System</B></p>

<p>The Single Frequency Network (SFN) towers infrastructure is another system in addition to the turbo an SRS to facilitate DTV reception; SFN does not use any bandwidth of the broadcast channel, and has a partner company, Rohde & Schwarz.</p>

<p>Today there is not a system in the US that has been widely deployed, there is a couple of experimental deployments, the FCC has not issued rules and is not the standard practice to have multiple towers transmitting on the same frequency.</p>

<p>Because the multiple transmitters are not perfectly synchronized with each other transmitting the exactly the same thing at the same time, produces terrible ghost effects on receivers in that market.</p>

<p>It is very hard for the receiver to deal with that, so there is a need to synchronize the transmissions. Rohde & Schwarz noticed that with AVSB there was a need to find a way to make the data frames deterministic so they would start and stop at a specified point, which is not the case of the underline ATSC standard.</p>

<p>Samsung introduced a deterministic element to the data frames so that a reference sequence could be inserted and the error coding updated in real time without upsetting legacy receivers.</p>

<p>Combined with GPS time to synchronize the multiple transmitters, the SFN part of AVSB is able to distribute the same programming to all the different transmitters; along with the programming a little code is inserted to indicate when to start transmitting so each independent site through its GPS clock would be synchronized.</p>

<p>Even legacy TVs in the area of service will get a better picture because the multiple transmitters would help receivers located in difficult reception spots (such as behind a mountain) lock better into the broadcasted channel.</p>

<p>For NAB, Samsung obtained an experimental license from the FCC, to demonstrate with three low power transmitters, one in the convention center and two nearby and will operating in SFN.</p>

<p>Stay tuned for the final article in this series, which outlines the impact analysis of overusing AVSB.</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Rodolfo La Maestra</b>, <b>September  3, 2007 05:47 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 685
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
 				AND entry_id <> 685
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2007/09/mobile_dtv_reception_-_advanced-vestigial_side-band_a-vsb_-_the_implementation.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
