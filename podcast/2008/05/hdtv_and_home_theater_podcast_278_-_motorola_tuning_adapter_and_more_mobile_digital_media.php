<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1409";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1409 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category[placement_category_id];
*/
	
	# Still need this to track page views. Used in the Links unit in the footer
	$google_links_channel = $author[channel];

	# This template is used only for podcasts
#	$rss_link = '<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/hdtv-bulletins" />';
	$container = 'article_container';
#	$category_page = 'bulletins-category.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<?require(BASE_DIR .'/includes/common_header.php');?>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<meta name="generator" content="http://www.movabletype.org/" />
	<meta name="keywords" content="tuning adapter, motorola tuning, switched digital, digital media, cable service, cable, service, mobile, digital, adapter, video, Mobile, Motorola, box, motorola, Digital, programming, mediaflo, switched, Switched, tuning, channel, Tivo, tivo, Series" />
	<meta name="description" content="Motorola Tuning Adapter MTR700
If you are a Tivo lover and have your Series 3 box so that you can watch and record HD from your cable company, you may be wondering what will happen to your service when your cable company rolls out Switched Digital service. We have great news for you! Your Tivo Series 3 will be compatible with the new format. You will need an adapter to make it work however.

More Mobile Digital Media
A few weeks ago on Show 272  we discussed the emerging mobile TV standard of the Open Mobile Video Coalition. At that time we discussed how the standard was just being deployed. We received an email from Glenn, host of “The ZA Show ” asking us to talk about a format that is already deployed called MediaFlo." />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #278 - Motorola Tuning Adapter and More Mobile Digital Media</title>
	<?=$rss_link?>
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/tracker.php');
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_278_-_motorola_tuning_adapter_and_more_mobile_digital_media';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #278 - Motorola Tuning Adapter and More Mobile Digital Media'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/05/hdtv_and_home_theater_podcast_278_-_motorola_tuning_adapter_and_more_mobile_digital_media.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #278 - Motorola Tuning Adapter and More Mobile Digital Media</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>May 23, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/05/hdtv_and_home_theater_podcast_278_-_motorola_tuning_adapter_and_more_mobile_digital_media.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/05/hdtv_and_home_theater_podcast_278_-_motorola_tuning_adapter_and_more_mobile_digital_media.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/05/hdtv_and_home_theater_podcast_278_-_motorola_tuning_adapter_and_more_mobile_digital_media.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
				<span><img src="/images/email.gif" alt="Email Article" align="absmiddle" /><a href="<?=$email_url?>">Email</a></span>
				<br /><br />
			</td>
		</tr>
	</table>
	<?if ($userdata[subscriptions] & SUB_PODCAST) {} else {
		if ($userdata[session_logged_in]) {?>
			<div class="important"><span class="corners-top"><span></span></span>
				<img src="/images/i_inbox.gif" align="left" style="padding-right:10px" /><span class="label">Receive instant notification of new episodes:</span>
				<a href="<?=URL_PROFILE_SUBSCRIPTIONS?>">Modify your subscription profile</a> to receive notification of new
				episodes of The HDTV Podcast via email as soon as they are published.
			<span class="corners-bottom"><span></span></span></div>
		<?} else {?>
			<div class="important"><span class="corners-top"><span></span></span>
				<img src="/images/i_inbox.gif" align="left" style="padding-right:10px" /><span class="label">Receive instant notification of new episodes:</span>
				<a href="<?=URL_PROFILE_CREATE?>">Register Now</a> to receive notification of new
				episodes of The HDTV Podcast via email as soon as they are published.
			<span class="corners-bottom"><span></span></span></div>
		<?}
	}?>
	<div>
		<div style="float:left; margin:0 5px 5px 0;"><?
			if ($digg_url == '') {
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/05/hdtv_and_home_theater_podcast_278_-_motorola_tuning_adapter_and_more_mobile_digital_media.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23278%20-%20Motorola%20Tuning%20Adapter%20and%20More%20Mobile%20Digital%20Media&amp;bodytext=Motorola%20Tuning%20Adapter%20MTR700%0AIf%20you%20are%20a%20Tivo%20lover%20and%20have%20your%20Series%203%20box%20so%20that%20you%20can%20watch%20and%20record%20HD%20from%20your%20cable%20company%2C%20you%20may%20be%20wondering%20what%20will%20happen%20to%20your%20service%20when%20your%20cable%20company%20rolls%20out%20Switched%20Digital%20service.%20We%20have%20great%20news%20for%20you%21%20Your%20Tivo%20Series%203%20will%20be%20compatible%20with%20the%20new%20format.%20You%20will%20need%20an%20adapter%20to%20make%20it%20work%20however.%0A%0AMore%20Mobile%20Digital%20Media%0AA%20few%20weeks%20ago%20on%20Show%20272%20%20we%20discussed%20the%20emerging%20mobile%20TV%20standard%20of%20the%20Open%20Mobile%20Video%20Coalition.%20At%20that%20time%20we%20discussed%20how%20the%20standard%20was%20just%20being%20deployed.%20We%20received%20an%20email%20from%20Glenn%2C%20host%20of%20%E2%80%9CThe%20ZA%20Show%20%E2%80%9D%20asking%20us%20to%20talk%20about%20a%20format%20that%20is%20already%20deployed%20called%20MediaFlo.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<div align="center" style="height:55px; padding-top:20px"><span style="margin:0 10px"><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="/images/chicklet-itunes.gif" alt="iTunes"></a></span><span style="margin:0 10px"><a title="" href="zune://subscribe/?HDTV%20and%20Home%20Theater%20Podcast=http://feeds.feedburner.com/HdtvPodcast"><img title="" alt="" src="http://podcast411.com/07img/1click_Zune.gif" border="0"></a></span></div>
<a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-05-23.mp3">Listen Now - mp3</a>
<a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a>
<a href="http://www.htguys.com">Website</a>

<p><strong>Today's Show:</strong><br />
<strong>Motorola Tuning Adapter MTR700</strong></p>

<p>If you are a Tivo lover and have your Series 3 box so that you can watch and record HD from your cable company, you may be wondering what will happen to your service when your cable company rolls out Switched Digital service. We have great news for you! Your Tivo Series 3 will be compatible with the new format. You will need an adapter to make it work however.</p>

<p>Before we get into the adapter, here is a quick description of the Switched Digital cable service. SDV is a method distributing digital cable Television. Switched video only sends the digital video that is required by a neighborhood. The current method sends all the channels through the cable regardless whether the channel is being watched. A cable box is required to watch TV on a cable system that uses Switched Digital Video.</p>

<p>The Motorola Tuning Adapter (MTR700) is a device that goes between your Tivo Series 3 cable cards and the incoming cable from the wall. The purpose of the device is to allow you to continue enjoying your cable service as though nothing happened. <a target="_blank" href="http://www.engadgethd.com/2008/05/18/hands-on-with-the-motorola-tuning-adapter-mtr700/">Ben Drawbaugh of Engadget HD</a> spent some time with the device and reports that there is no difference in the functionality of the Tivo box using SDV. Cisco showed a static display of their STA1520 tuning adapter. The unit was larger than the Motorola MTR700.</p>

<p>The Motorola box will be available to cable companies in July. The Cisco implementaion will be available in Q3. No word on what cable companies will charge for use of the box.</p>

<p><strong>More Mobile Digital Media</strong></p>

<p>A few weeks ago on <a target="_blank" href="http://www.htguys.com/archive/2008/May02.html">Show 272</a>  we discussed the emerging mobile TV standard of the Open Mobile Video Coalition. At that time we discussed how the standard was just being deployed. We received an email from Glenn, host of "<a target="_blank" href="http://www.thezashow.com/">The ZA Show</a>" asking us to talk about a format that is already deployed called <a target="_blank" href="http://www.mediaflousa.com/content/index.shtml">MediaFlo</a>.</p>

<p>MediaFlo is a technology that enables mobile television with a global open standard for broadcasting multimedia. MediaFlo is the platform invented specifically to bring broadcast quality video to mobile efficiently and cost effectively.</p>

<p><strong>Programming Lineup</strong></p>

<p>The FLO TV service offers a lineup of entertainment, news, sports and kids’ programming from national networks including CBS, Comedy Central, ESPN, FOX, MTV, NBC and Nickelodeon. Shows include:<ul><li>MSNBC Hardball</li><li>Fox Report</li><li>NBC Nightly News</li><li>iCarly</li><li>And tons more</li></ul><br />
Many shows are simulcast with network television.</p>

<p><strong>Channel Guide</strong></p>

<p>The system has an on-screen programming guide that allows users to flip from one channel to the next. </p>

<p><strong>How do you get it?</strong></p>

<p>The FLOTV service is currently available through Verizon Wireless VCAST Mobile TV and AT&T Mobile TV in the US. The service ranges from $10 to $30 a month depending on the package. The service area is quite small.</p>

<p><strong>Dedicated Multicast Network</strong></p>

<p>MediaFLO USA multicasts the FLO TV™ service through the 716-722 MHz spectrum, UHF Channel 55, enabling the nearly instant, seamless delivery of TV-quality content to subscribers simultaneously. The FLO TV service does not utilize the wireless carrier’s existing 3G networks for transmitting programming content, thereby preventing any degradation to existing voice and data services.</p>

<p>Links:<ul><li><a target="_blank" href="http://www.wireless.att.com/learn/messaging-internet/mobile-tv/index.jsp">AT&T MobileTV</a></li><li><a target="_blank" href="http://products.vzw.com/index.aspx?id=mobileTV">Verizon V-Cast</a></li></ul></p>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>May 23, 2008 09:23 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1409
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

			<?if (9 <> 7) {
				# Recent Articles by Author (exclude this one)
				# Do not show recent articles for Bulletins.
				$qry = "
				SELECT entry_id, entry_blog_id, entry_created_on, entry_title, author_id, author_name
				FROM mt_entry e, mt_author a
				WHERE entry_blog_id = 9
					AND entry_id <> 1409
					AND entry_status = 2
					AND entry_author_id = a.author_id
					AND a.author_name = 'The HT Guys'
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
					<h2>About The HT Guys</h2>
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

		</td>
	</tr></table>

	<?
		include(BASE_DIR .'/includes/body_footer.php');
	?>
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/05/hdtv_and_home_theater_podcast_278_-_motorola_tuning_adapter_and_more_mobile_digital_media.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
