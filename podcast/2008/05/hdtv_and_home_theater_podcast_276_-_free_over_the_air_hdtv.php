<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1400";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1400 AND placement_is_primary = 1";
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
	<meta name="keywords" content="signal booster, hdtv antenna, air hdtv, help pull, digital analog, antenna, signal, need, signals, HDTV, hdtv, reception, quality, digital, tuner, channel, cable, satellite, may, good, booster, better, range, air, installed" />
	<meta name="description" content="If you look at our DVRs you will see that the majority of the recorded shows are of over the air networks. These are high quality programs that arrive at your home free of charge. All you need to watch them is an ordinary antenna. On today's show we will talk about what you need to begin enjoying high quality picture and sound free of charge. We hope to give you the information you need to begin enjoying HDTV for free!" />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #276 - Free Over the Air HDTV</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_276_-_free_over_the_air_hdtv';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #276 - Free Over the Air HDTV'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/05/hdtv_and_home_theater_podcast_276_-_free_over_the_air_hdtv.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #276 - Free Over the Air HDTV</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>May 16, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/05/hdtv_and_home_theater_podcast_276_-_free_over_the_air_hdtv.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/05/hdtv_and_home_theater_podcast_276_-_free_over_the_air_hdtv.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/05/hdtv_and_home_theater_podcast_276_-_free_over_the_air_hdtv.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/05/hdtv_and_home_theater_podcast_276_-_free_over_the_air_hdtv.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23276%20-%20Free%20Over%20the%20Air%20HDTV&amp;bodytext=If%20you%20look%20at%20our%20DVRs%20you%20will%20see%20that%20the%20majority%20of%20the%20recorded%20shows%20are%20of%20over%20the%20air%20networks.%20These%20are%20high%20quality%20programs%20that%20arrive%20at%20your%20home%20free%20of%20charge.%20All%20you%20need%20to%20watch%20them%20is%20an%20ordinary%20antenna.%20On%20today%27s%20show%20we%20will%20talk%20about%20what%20you%20need%20to%20begin%20enjoying%20high%20quality%20picture%20and%20sound%20free%20of%20charge.%20We%20hope%20to%20give%20you%20the%20information%20you%20need%20to%20begin%20enjoying%20HDTV%20for%20free%21&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
<a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-05-16.mp3">Listen Now - mp3</a>
<a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a>
<a href="http://www.htguys.com">Website</a>

<p><strong>Today's Show:</strong></p>

<p><strong>What you need to know about Free Over the Air HDTV Content</strong><br />
If you look at our DVRs you will see that the majority of the recorded shows are of over the air networks. These are high quality programs that arrive at your home free of charge. All you need to watch them is an ordinary antenna. On today's show we will talk about what you need to begin enjoying high quality picture and sound free of charge. We hope to give you the information you need to begin enjoying HDTV for free!</p>

<p><strong>What do you get?</strong><br />
Over the air (OTA) reception is typically the highest quality signal available. Some local broadcasters in the LA area transmit a signal that is 18Mbps. Consider that some cable and satellite companies broadcast 10 to 12 Mbps for the same channel. Even when you account for better compression techniques that the satellite companies are using the highest quality picture is available via your antenna. Another benefit, or curse, of OTA signals is multicasting. This is where the broadcaster takes some of the data away from the main channel and allocates it to a sub channel. Here in LA, NBC and ABC put weather information on one sub channel and news on another. This degrades the main channel quality to less than that of satellite. In Ara's case DirecTV puts up a better picture for NBC than you can get OTA.</p>

<p><strong>What do you need?</strong><br />
There are two components to receive HDTV, an antenna and a tuner. Three if you include the coax cable connecting the two. A myth we would like to bust is that you need a special HDTV antenna. This is absolutely false. If you have an antenna on your roof that has been there for 30 years it will work with today's digital TV. Right now you need an antenna that is capable of puling in UHF signals. Sometime in the future some stations will go back to VHF so buying an antenna that works with both signals may be a good insurance policy.</p>

<p>Our television reviews in the past have indicated whether it had an ATSC tuner. ATSC stands for Advanced Television Systems&nbsp;Committee. An ATSC tuner is required to receive a digital signal over the air. Every TV sold today has this capability. If you have an older TV you will need to buy an external tuner. There are not that many external tuners left on the market today. You can try ebay if you are having a hard time finding one. Once the antenna is in place you have your tuner scan for channels and you are good to go. The nice thing about digital TV is that if you pick up the signal it will be perfect. Not like when I was a kid and you could only pick up a few channels clearly and the rest were snowy.</p>

<p>Depending on the length of the cable between your antenna and receiver you may want to use a signal booster. The signal booster will increase the strength of the signal coming into the receiver. There are two pieces to a signal booster. One is installed at the antenna and the other is installed just before the tuner. You need access to power as well. Typically the piece that is installed at the tuner needs to be plugged into the wall. It should be noted that a signal booster will not help you pull in faint signals but it will help maintain the signal strength across long cable runs. That is, if you have a short cable run, less than 50 feet or so, a signal booster won't help pull in channels you can't receive.</p>

<p>If you have a satellite installation and you want to put up an antenna you already have cables going to your TV so wouldn't it be nice if you could use them. The good news is that you can. You need a device called a diplexer. This device will allow the ATSC and Satellite signal to share a single cable. A diplexer has a combiner and a splitter. The combiner goes outside where you &quot;combine&quot; the satellite and ATSC signal. The splitter goes inside where you &quot;split&quot; the signal. These devices work great and can save you the hassle of running new cables. If you use a diplexer you won't be able to use a signal booster.</p>

<p>A great resource for setting up your antenna is <a href="http://www.antennaweb.org/aw/Address.aspx" target="_blank" title="AntennaWeb.org">AntennaWeb.org</a>.<br />
There you will find a resource that will help you determine what size antenna you need and where to point it for your address. This is only good for US addresses. Also, you should know that the numbers on what signals you can receive are a bit conservative. AntennaWeb said we could only pick up a few channels in our neighborhood but we actually get them all. Of course Ara has a Yagi Antenna with a nine foot boom.</p>

<p><strong>Antenna Installation Tips</strong><br />
<ul><li>Setting up your antenna outside will work better than putting it in your attic or even using an indoor antenna. If you live close (5 to 20 miles) to the transmitters then you may get away with an indoor antenna. Some have installed their antennas in the attic so the keep the outside of their home looking clean. This will reduce the reception by as much as 30%. If you have to do this buy an antenna that is bigger than you need if it were to be installed outside. If you have a chicken wire wrap around your house (underneath the stucco) it may block all reception regardless of the antenna.</li><li>The more height you can get the better. This minimizes interference from household electronics. Antenna Web recommends at least four feet above your roof-line.</li><li>This one is just common sense. The closer you are to the transmitters the better. Make sure you use antenna web to determine the right size antenna for your location.</li><li>Bigger is better.</li><li>Some structures can reflect TV signals which leads to the receiver picking up multiple signals for the same channel. If you live in an area with tall buildings this may interfere with your reception. Directional antennas are the most resistant to this artifact since they work in only one direction. The further away the structures are the less prone to the problem you will be.</li></ul></p>

<p><strong>What's it going to cost me?</strong><br />
The good news is that this is one of the least expensive parts of your system. Good quality antennas go for $30 to $100. There are some that package the antenna into an eye pleasing design that go for more. But in this case you are paying for the look and not necessarily the quality. You may have to pay for installation as well. If you are lucky you already have an antenna on your roof.</p>

<p><strong>Resources</strong><br />
<ul><li><a href="http://www.antennaweb.org/aw/welcome.aspx" target="_blank" title="AntenneaWeb">AntenneaWeb</a></li><li><a href="http://www.hdtvpub.com/" target="_blank" title="HDTV Pub">HDTV Pub</a> - A great resource for user reports about digital television reception in your neighborhood.</li><li><a href="http://www.avsforum.com/" target="_blank" title="AVS Forum">AVS Forum</a> - There are numerous digital reception threads with people willing to lend a helping hand</li></ul></p>

<p><strong>Antennas:</strong><br />
<ul><li><a href="/shop.php?id=B000FVTPX2" target="_blank">Channel Master 4221 Mid-range outdoor rooftop UHF antenna</a> - receives digital &amp; analog UHF TV signals range is 45 miles</li><li><a href="/shop.php?id=B000FVVKQM" target="_blank">Channel Master 4228 Long-range outdoor rooftop UHF antenna</a> - receives digital &amp; analog UHF TV signals range is 60 miles</li><li><a href="/shop.php?id=B000I1AQ4Q" target="_blank">Winegard SS2000 Square Shooter Amplified Version</a> - If you don't want something that looks like an antenna this is for you!</li><li><a href="/shop.php?id=B000LZ9EXI" target="_blank">Antennas Direct 91XG Uni-directional HDTV Antenna Long Range</a> - Range: 50-70 Miles or more.</li><li><a target="_blank" href="/shop.php?id=B0007MXZB2">Terk HDTV Indoor Amplified High-Definition Antenna for Off-Air HDTV Reception</a></li></ul></p>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>May 16, 2008 10:16 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1400
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
					AND entry_id <> 1400
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/05/hdtv_and_home_theater_podcast_276_-_free_over_the_air_hdtv.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
