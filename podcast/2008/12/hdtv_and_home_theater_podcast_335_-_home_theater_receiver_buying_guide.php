<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1573";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1573 AND placement_is_primary = 1";
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
	<meta name="keywords" content="home theater, theater receiver, guys store, store price, channel home, receiver, home, theater, audio, Receiver, Home, Theater, video, guys, Guys, channel, Audio, store, hdmi, Channel, HDMI, Store, dts, price, dolby" />
	<meta name="description" content="We start off the main content in today's show talking about a couple of low cost options we found for recording your Holiday memories in High Definition. Then we move on to the 2008 edition of our annual Home Theater Receiver buying guide. Like last year, this year we have given you our HTIB and HDTV buying guide. Once again our series would not be complete without talking about the hub of your Home Theater, the Receiver. This year we have three categories, $500 or less, greater than $500 and less than $1000, and greater than $1000. Last year the cheapest receiver supporting Dolby True HD was the Onkyo TX-SR605 which went for an incredible $380." />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #335 - Home Theater Receiver Buying Guide</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_335_-_home_theater_receiver_buying_guide';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #335 - Home Theater Receiver Buying Guide'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/12/hdtv_and_home_theater_podcast_335_-_home_theater_receiver_buying_guide.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #335 - Home Theater Receiver Buying Guide</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>December  8, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/12/hdtv_and_home_theater_podcast_335_-_home_theater_receiver_buying_guide.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/12/hdtv_and_home_theater_podcast_335_-_home_theater_receiver_buying_guide.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/12/hdtv_and_home_theater_podcast_335_-_home_theater_receiver_buying_guide.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/12/hdtv_and_home_theater_podcast_335_-_home_theater_receiver_buying_guide.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23335%20-%20Home%20Theater%20Receiver%20Buying%20Guide&amp;bodytext=We%20start%20off%20the%20main%20content%20in%20today%27s%20show%20talking%20about%20a%20couple%20of%20low%20cost%20options%20we%20found%20for%20recording%20your%20Holiday%20memories%20in%20High%20Definition.%20Then%20we%20move%20on%20to%20the%202008%20edition%20of%20our%20annual%20Home%20Theater%20Receiver%20buying%20guide.%20Like%20last%20year%2C%20this%20year%20we%20have%20given%20you%20our%20HTIB%20and%20HDTV%20buying%20guide.%20Once%20again%20our%20series%20would%20not%20be%20complete%20without%20talking%20about%20the%20hub%20of%20your%20Home%20Theater%2C%20the%20Receiver.%20This%20year%20we%20have%20three%20categories%2C%20%24500%20or%20less%2C%20greater%20than%20%24500%20and%20less%20than%20%241000%2C%20and%20greater%20than%20%241000.%20Last%20year%20the%20cheapest%20receiver%20supporting%20Dolby%20True%20HD%20was%20the%20Onkyo%20TX-SR605%20which%20went%20for%20an%20incredible%20%24380.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<!--HDTV and Home Theater Podcast #-->
<div align="center" style="height:55px; padding-top:20px">
<span style="margin:0 10px"><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="http://www.htguys.com/images/itunes_subscribe.gif" alt="iTunes"></a></span>
<span style="margin:0 10px"><a title="" href="zune://subscribe/?HDTV%20and%20Home%20Theater%20Podcast=http://feeds.feedburner.com/HdtvPodcast"><img title="" alt="" src="http://podcast411.com/07img/1click_Zune.gif" border="0"></a></span></div>
<div><a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-12-09.mp3">Listen Now - mp3</a></div>
<div><a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a></div>
<div><a href="http://www.htguys.com">Website</a></div><br>

<br><strong>Today's Show:</strong><br>
We start off the main content in today's show talking about a couple of low cost options we found for recording your Holiday memories in
High Definition.  Then we move on to the 2008 edition of our annual Home Theater Receiver buying guide.  Like last year, this year we have 
given you our HTIB and HDTV buying guide. Once again our series would not be complete without talking about the hub of your Home Theater, the 
Receiver.  This year we have three categories, $500 or less, greater than $500 and less than $1000, and greater than $1000. Last year 
the cheapest receiver supporting Dolby True HD was the Onkyo TX-SR605 which went for an incredible $380.
<br><br>
<strong>Capture HD memories for Christmas:</strong><br>
<a target="_blank" href="http://www.theflip.com/products_flip_mino.shtml#scene=sceneMinoHD">Flip Video MinoHD</a>, the world's smallest HD Camcorder<br>
It's <a target="_blank" href="http://www.htguys.com/shop.php?id=B001HSOFI2">available at the HT Guys store for $206</a>.<br>
We'll have a full review of this little guy on an upcoming episode.  You can also read an article about it at 
<a target="_blank" href="http://www.pcworld.com/article/154604/cool_tech_gifts_highdef_pocket_camcorder.html">PC World</a>.
<br><br>
<a target="_blank" href="http://us.creative.com/products/product.asp?category=833&subcategory=834&product=18108">Creative Vado HD</a>, pocket video cam.<br>
It's <a target="_blank" href="http://www.htguys.com/shop.php?id=B001LK8P14">available at the HT Guys store for $199</a>.
<br><br><br>
<strong>Home Theater Receiver Buying Guide</strong>
<br><br>
<strong>$500 and Less</strong> 
<br><br>
<a target="_blank" href="http://www.us.onkyo.com/model.cfm?m=TX-SR606&class=Receiver&p=i">Onkyo TX-SR606 7.1 Channel Home Theater Receiver</a> 
<br>(<a target="_blank" href="http://www.htguys.com/shop.php?id=B0015S8PGW">HT Guys Store Price $320</a>) 
7.1-channel home theater receiver with full HD integration and video upscaling, HDMI v1.3a (4 inputs, 1 output, 1080P compatible), 
1080i Upscaling powered by Faroudja DCDi Edge, TrueHD, DTS-HD Decoding, Audyssey 2EQ for Loudness Correction. Best value out there!
<br><br>
<a target="_blank" href="http://www.yamaha.com/yec/products/productdetail.html?CNTID=567571&CTID=5000300">Yamaha RX-V663BL 665 Watt 7.1-Channel Home Theater Receiver</a> 
<br>(<a target="_blank" href="http://www.htguys.com/shop.php?id=B0013ZGOWY">HT Guys Store Price $499.95</a>) 
HDMI 1.3 with Deep Color, xv Colorspace, 1080p compatible, and support for HD Audio formats, Analog video to HDMI up-conversion and 
also de-interlacing from 480i to 480p, Bluetooth music streaming using Yamaha YBA-10 Bluetooth Audio Receiver.
<br><br>
<strong>Greater than $500 and Less than $1000</strong>
<br><br>
<a target="_blank" href="http://www.usa.denon.com/ProductDetails/4241.asp">Denon AVR-1909 7.1 Channel Home Theater Receiver</a> 
<br>(<a target="_blank" href="http://www.htguys.com/shop.php?id=B001BKND5W">HT Guys Store Price $599</a>) 
can also be used as a 5.1+2 CH Independent Zone system.  Identical quality and power for all 7 channels (90W x 7ch), Dolby TrueHD 
and dts-HD Master Audio decoders, Faroudja DCDi video processing and up-conversion, Audyssey Dynamic Volume and Dynamic EQ, 3 HDMI 
inputs (Audio and Video), Sirius Satellite Radio ready
<br><br>
<a target="_blank" href="http://www.pioneerelectronics.com/PUSA/Products/HomeEntertainment/AV-Receivers/EliteReceivers/ci.VSX-03TXH.Kuro">Pioneer Elite VSX-03TXH 7.1-Channel A/V Receiver</a> 
<br>(<a target="_blank" href="http://www.htguys.com/shop.php?id=B001DEYVSE">HT Guys Store Price $999</a>) 
110 Watts x 7 Direct Energy Amplification, Rigid Trans Stabilizer Construction, THX Select2 Plus Processing and Certification, 
Dolby TrueHD, Dolby Digital Plus, dts-HD Master Audio/High Resolution Audio, New Freescale Dual Core DSP Engine, Standing Wave 
Control, Advanced Sound Retriever for enhanced playback from portable audio devices, Neural THX Surround, TBC Digital Video Conversion 
Converts S-Video, Composite and Component Up to HDMI, USB Memory Audio Playback
<br><br>
<strong>Greater than $1000</strong>
<br><br>
<a target="_blank" href="http://www.sonystyle.com/webapp/wcs/stores/servlet/ProductDisplay?catalogId=10551&storeId=10151&langId=-1&productId=8198552921665532067">Sony STR-DA5400ES ES Series 7.1 Channel Home Theater A/V Receiver</a> 
<br>(<a target="_blank" href="http://www.htguys.com/shop.php?id=B001GCUOEI">HT Guys Store Price $1419</a>) 
Enjoy your entertainment in multiple rooms with the STR-DA5400ES 7.1-channel A/V receiver. At the heart, this Elevated Standard (ES) 
receiver features dual Faroudja DCDi Cinema® chips that enable upscaling of standard definition content to 1080i in up to two rooms. 
It's also equipped with the necessary connections for full home integration allowing you to start an HD movie in the living room and 
finish it in the bedroom. You can also listen to your favorites songs in up to three rooms from your iPod® or Walkman® player, PC, 
or XM® and Sirius Satellite Radio® via dual Digital Media Port inputs. In addition, the STR-DA5400ES features advanced A/V technology 
that reproduces the latest audio formats from Dolby® Labs (Dolby Digital +/Dolby TrueHD) and dts® (dts HD/DTS High Resolution Audio). 
Offering plenty of connections, this receiver comes with six HDMI™ inputs and 2 HDMI outputs that are compatible with 1080 60p/24p, 
Deep Color and x.v.Color video signals.
<br><br>
<strong>HT Guys' Ultimate Christmas Present:</strong><br>
<a target="_blank" href="http://www.usa.denon.com/ProductDetails/3494.asp">Denon AVR-4308CI Advanced 7.1 Channel A/V Home Theater Receiver</a> 
<br>(<a target="_blank" href="http://www.htguys.com/shop.php?id=B000UL6KF8">HT Guys Store Price $2149</a>) 
140 watts x 7 channels, Wi-Fi and Network capable, Dolby® TrueHD and dts-HD™ Master Audio, DDSC-HD digital Dynamic Discrete Surround 
Circuitry, Expanded HDMI v1.3a ports with Deep Color, xvYCC, and SACD support, Pure Direct mode & Advanced AL24 Processing Multi 
Channel, DENON LINK III, enabling high-speed, high-grade digital signal transmission, Audyssey MultEQ XT & MultEQ Pro calibration 
installer ready, Audyssey Dynamic EQ calibration installer ready
<br><br>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>December  8, 2008 11:38 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1573
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
					AND entry_id <> 1573
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/12/hdtv_and_home_theater_podcast_335_-_home_theater_receiver_buying_guide.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
