<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1314";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1314 AND placement_is_primary = 1";
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
	<meta name="keywords" content="dolby digital, dolby truehd, truehd dts, digital plus, master audio, audio, hdmi, dolby, Dolby, HDMI, Audio, dts, truehd, video, TrueHD, buy, DTS, Buy, digital, Digital, receivers, support, theater, master, Master" />
	<meta name="description" content="Today we discuss a recent article in CE Pro that runs down 9 A/V Receivers that you may want to consider for your next home theater. These receivers are not entry level and as such will cost a little more than your bargain receivers." />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #262 - A/V Receivers for your next Home Theater</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_262_-_av_receivers_for_your_next_home_theater';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #262 - A/V Receivers for your next Home Theater'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/03/hdtv_and_home_theater_podcast_262_-_av_receivers_for_your_next_home_theater.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #262 - A/V Receivers for your next Home Theater</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>March 27, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/03/hdtv_and_home_theater_podcast_262_-_av_receivers_for_your_next_home_theater.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/03/hdtv_and_home_theater_podcast_262_-_av_receivers_for_your_next_home_theater.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/03/hdtv_and_home_theater_podcast_262_-_av_receivers_for_your_next_home_theater.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/03/hdtv_and_home_theater_podcast_262_-_av_receivers_for_your_next_home_theater.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23262%20-%20A%2FV%20Receivers%20for%20your%20next%20Home%20Theater&amp;bodytext=Today%20we%20discuss%20a%20recent%20article%20in%20CE%20Pro%20that%20runs%20down%209%20A%2FV%20Receivers%20that%20you%20may%20want%20to%20consider%20for%20your%20next%20home%20theater.%20These%20receivers%20are%20not%20entry%20level%20and%20as%20such%20will%20cost%20a%20little%20more%20than%20your%20bargain%20receivers.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
<a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-03-28.mp3">Listen Now - mp3</a>
<a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a>
<a href="http://www.htguys.com">Website</a>

<p><strong>Today's Show:</strong><br />
Today we discuss a recent article in CE Pro that runs down 9 A/V Receivers that you may want to consider for your next home theater. These receivers are not entry level and as such will cost a little more than your bargain receivers. For more information on the author please see his website (<a href="http://www.leedistad.com/">http://www.leedistad.com</a>). Now on to the list:</p>

<p><a href="http://www.cepro.com/article/9_a_v_receivers_for_your_next_home_theater">A/V Receivers for your next Home Theater</a> - An article from CE Pro  Written by Lee Distad</p>

<p><a href="http://www.cambridgeaudio.com/summary.php?PID=139&amp;Title=Azur+640R+7.1+HDMI+AV+Receiver" title="Cambridge Audio 640R">Cambridge Audio 640R</a> $1200 online<br />
<ul><br />
<li>HDMI switching allows latest DVD players and set-top boxes to be routed to the TV</li><br />
<li>Proprietary Forced / Convection Heat Tunnel - X-Tract™ Allows for very quiet and cool operation</li><br />
<li>Multi room support for video as well as audio</li></ul><br />
<a href="http://usa.denon.com/ProductDetails/3510.asp" title="Denon AVR-3808CI">Denon AVR-3808CI</a> $1260 online (<a href="http://www.htguys.com/shop.php?id=B000TW78AO" title="Buy Now">Buy Now</a>)<br />
<ul><br />
<li>Dolby® TrueHD and dts-HD™ Master Audio</li><br />
<li>DNLA certified</li><br />
<li>Expanded HDMI v1.3a ports with Deep Color, xvYCC and SACD support</li><br />
<li>New GUI menu support</li></ul><br />
<a href="http://www.integrahometheater.com/model.cfm?class=Receiver&amp;m=DTR-7.8&amp;p=i" title="Integra DTR-7.8">Integra DTR-7.8</a> $1300 from Integra Dealers<br />
<ul><br />
<li>THX Ultra2 Certified</li><br />
<li>HDMI (v.1.3a) Audio &amp; Video Processing </li><br />
<li>Dolby TrueHD, Dolby Digital Plus Decoding, DTS-HD Master Audio, DTS-HD High-Resolution Audio </li><br />
<li>De-interlacer w/Faroudja DCDi Edge™ Technology</li><br />
<li>Bi-directional Ethernet &amp; RS-232 Port for 3rd Party Control Systems (Device Discovery, E-control, etc.)</li></ul><br />
<a href="http://us.marantz.com/Products/2205.asp" title="Marantz SR7002">Marantz SR7002</a> $1400 (<a id="o2r_" href="http://www.htguys.com/shop.php?id=B000X14W4G" title="Buy Now">Buy Now</a>)<br />
<ul><br />
<li>THX Select2 Certified</li><br />
<li>HDMI V1.3a Repeating/Switching: 4-In/2-Out</li><br />
<li>Dolby TrueHD, Dolby Digital Plus, dts-HD Master Audio</li><br />
<li>Deep Color, xvYCC and SACD Support by HDMI</li></ul><br />
<a href="http://nadelectronics.com/products/av-receivers/T775-A/V-Surround-Sound-Receiver" title="NAD T775">NAD T775</a> $2500<br />
<ul><br />
<li>“Music First” philosophy</li><br />
<li>4 v1.3 1080p HDMI Digital Audio/Video Inputs </li><br />
<li>Holmgren™ Toroidal Power Supply</li><br />
<li>Back Surround Amplifier Channels can be reassigned as Zone 2, 3, or 4, or front Bi-Amp</li></ul><br />
<a href="http://www.us.onkyo.com/model.cfm?m=TX-SR875&amp;class=Receiver&amp;p=i" title="Onkyo TX-SR875">Onkyo TX-SR875</a> $1150 (<a href="http://www.htguys.com/shop.php?id=B000OBLARW" title="Buy Now">Buy Now</a>)<br />
<ul><br />
<li>Reon-HQV™ HD video scaling up to 1080p</li><br />
<li>HDMI V1.3 repeater (4in/1out)</li><br />
<li>Dolby Digital Plus, Dolby TrueHD and DTS-HD decoding</li><br />
<li>Top line of Burr-Brown DACs for clean sound</li><br />
<li>Internet Radio</li><br />
<li>Over 50 pounds</li></ul><br />
<a href="http://www.pioneerelectronics.com/PUSA/Products/HomeEntertainment/AV-Receivers/EliteReceivers/ci.VSX-94TXH.Kuro" title="Pioneer Elite VSX-94TXH">Pioneer Elite VSX-94TXH</a> $1200 (<a href="http://www.htguys.com/shop.php?id=B000WY0HUW" title="Buy Now">Buy Now</a>) <a href="http://www.htguys.com/archive/2008/March07.html">See HT Guys Review</a><br />
<ul><br />
<li>HDMI® 1.3a</li><br />
<li>dtsHD® &amp; Dolby® TrueHD Decoders</li><br />
<li>DLNA® Compliant Network Music and Internet Radio via I/P</li></ul><br />
<a href="http://www.sonystyle.com/webapp/wcs/stores/servlet/ProductDisplay?catalogId=10551&amp;langId=-1&amp;productId=8198552921665186458&amp;storeId=10151" title="Sony ES STR-DA5300ES">Sony ES STR-DA5300ES</a> $1430 (<a href="http://www.htguys.com/shop.php?id=B000VJRSB4" title="Buy Now">Buy Now</a>)<br />
<ul><br />
<li>HDMI &quot;Active Intelligence1&quot; (6 inputs/1 output)</li><br />
<li>2nd Room HD video distribution via component video</li><br />
<li>Accepts 8ch linear PCM, Dolby Digital +, Dolby TrueHD and DTS HD via HDMI3</li><br />
<li>Faroudja® DCDi Cinema™ Up Scaling Technology </li></ul><br />
<a href="http://www.yamaha.com/yec/products/productdetail.html?CNTID=558316&amp;CTID=5000300" title="Yamaha RXV-3800">Yamaha RXV-3800</a> $1600 (<a href="http://www.htguys.com/shop.php?id=B000V4YQGY" title="Buy Now">Buy Now</a>)<br />
<ul><br />
<li>Latest HDMI 1.3a (4 in/1 out) specification supports deep color (30/36 bit) transmission, 120Hz and 24Hz refresh rates and auto lip-sync</li><br />
<li>Analog video (480i or 480p) upscaling to full HD 1080p</li><br />
<li>Supports Dolby Digital Plus, Dolby TrueHD, DTS-HD High Resolution Audio and DTS-HD Master Audio</li><br />
<li>Network Receiver functions allow you to play Internet radio, stream PC music files, use Yamaha MusicCast, and play USB portable audio players and flash drives</li></ul></p>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>March 27, 2008 08:04 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1314
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
					AND entry_id <> 1314
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/03/hdtv_and_home_theater_podcast_262_-_av_receivers_for_your_next_home_theater.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
