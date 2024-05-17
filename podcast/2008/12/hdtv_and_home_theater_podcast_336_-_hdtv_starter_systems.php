<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1576";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1576 AND placement_is_primary = 1";
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
	<meta name="keywords" content="home theater, center channel, blu ray, channel home, theater system, buy, channel, system, HDTV, Buy, hdtv, theater, home, receiver, inch, speakers, year, hdmi, video, audio, HDMI, remote, Blu, color, Home" />
	<meta name="description" content="Each year we put together an entire starter home theater system. This year we know money is tight so we assembled something that we feel will provide a great experience without breaking the bank. " />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #336 - HDTV Starter Systems</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_336_-_hdtv_starter_systems';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #336 - HDTV Starter Systems'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/12/hdtv_and_home_theater_podcast_336_-_hdtv_starter_systems.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #336 - HDTV Starter Systems</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>December 11, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/12/hdtv_and_home_theater_podcast_336_-_hdtv_starter_systems.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/12/hdtv_and_home_theater_podcast_336_-_hdtv_starter_systems.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/12/hdtv_and_home_theater_podcast_336_-_hdtv_starter_systems.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/12/hdtv_and_home_theater_podcast_336_-_hdtv_starter_systems.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23336%20-%20HDTV%20Starter%20Systems&amp;bodytext=Each%20year%20we%20put%20together%20an%20entire%20starter%20home%20theater%20system.%20This%20year%20we%20know%20money%20is%20tight%20so%20we%20assembled%20something%20that%20we%20feel%20will%20provide%20a%20great%20experience%20without%20breaking%20the%20bank.%20&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
<div><a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-12-12.mp3">Listen Now - mp3</a></div>
<div><a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a></div>
<div><a href="http://www.htguys.com">Website</a></div><br>

<br><strong>Today's Show:</strong><br>
Each year we put together an entire starter home theater system. This year we know money is tight so we assembled 
something that we feel will provide a great experience without breaking the bank.  
<br><br>
<strong>HDTV Starter Systems</strong>
<br><br>
<strong><em>Ara:</em></strong>
<br><br>
<strong>1) Samsung LN46A550 46-Inch 1080p LCD HDTV</strong> 
($1225 <a target="_blank" href="http://www.htguys.com/shop.php?id=B0014175E8">Buy Now</a>) 
Great TV for all the football in January and the Rose Parade will look fantastic with this TV's incredible color! 
Great looking TV even when its off.
<ul>
<li>3 HDMI 1.3 Ports</li>
<li>USB 2.0 port: connect devices directly to your HDTV--view JPEG photos or MPEG movies or easily listen to your MP3 player through your HDTV's audio system or your home theater system</li>
<li>178-degree viewing angle from all four axes with Super Patterned Vertical Alignment</li>
<li>5 ms response rate</li>
</ul>
<br>
<strong>2) Yamaha RX-V663BL 665 Watt 7.1-Channel Home Theater Receiver</strong> 
($500 <a target="_blank" href="http://www.htguys.com/shop.php?id=B0013ZGOWY">Buy Now</a>) 
I could have spent a little less on the receiver but I have been very happy with my Yamaha Equipment.
<ul>
<li>HDMI 1.3 with Deep Color, xv Colorspace</li>
<li>1080p compatible</li>
<li>Support for HD Audio formats</li>
<li>Analog video to HDMI up-conversion and also de-interlacing from 480i to 480p</li>
<li>Bluetooth music streaming using Yamaha YBA-10 Bluetooth Audio Receiver.</li>
</ul>
<br>
<strong>3) JBL CS480BG Complete 6-Piece Home Theater Speaker System</strong> 
($350 <a target="_blank" href="http://www.htguys.com/shop.php?id=B000O7N4IE">Buy Now</a>) 
I know the receiver I chose is 7.1 and I am only specifying a 5.1 speaker system but it is because these 
speakers are a steal at $350! They are easy on the Aesthetics Committee and they sound great. You can put 
the receiver into a 5.1 mode. When you are ready you can add the additional speakers of get a new set and 
move these into another room.
<ul>
<li>8-inch subwoofer rated at 125-watts (peak)</li>
<li>Titanium-laminate dome tweeters for outstanding high frequency sound</li>
<li>Single 3.5" midrange satellite drivers</li>
<li>Video shielded</li>
<li>Wall brackets included for satellites and center channel</li>
</ul>
<br>
<strong>4) Samsung BD-P1500 1080p Blu-ray Player</strong>  
($200 <a target="_blank" href="http://www.htguys.com/shop.php?id=B0014H16V0">Buy Now</a>) Add in a 1 GB thumb drive and you have a BD-Live capable Blu Ray player for $200!
<ul>
<li>Full HD 1080p Output for Blu-Ray Discs and selectable DVD Upconversion to 1080p</li>
<li>1080p with 24 Hz Video Output</li>
<li>HDMI version 1.3, component video, S-Video outputs</li>
<li>Dolby® Digital, Dolby® Digital Plus, Dolby TrueHD, dts-HD</li>
<li>Ethernet connection lets you easily check for the latest firmware upgrades online</li>
</ul>
<br>
<strong>5) Logitech Harmony 880 Remote Control</strong> 
($130 <a target="_blank" href="http://www.htguys.com/shop.php?id=B00093IIRA">Buy Now</a>) 
This is the product that ties it all together. Same as last year we are recommending this remote. You can save 
an additional $50 if you buy a refurbished one
<ul>
<li>Optimized for complicated HDTV and PVR systems</li>
<li>Quickly choose a 16:9 or 4:3 ratio depending on the program source</li>
<li>Includes a sleek docking station that keeps the remote fully charged</li>
<li>Color LCD is both functional and stylish, with user-customizable backgrounds, button icons and text</li>
<li>On-screen battery level indicator shows you charge status</li>
</ul>
Ara's system cost about $3600 last year and required about $200 in shipping costs. This year the system goes for $2405 
and all the shipping is free (at least through the HT Guys store). I could have gone bigger on the TV or included 
more and higher end speakers but for slightly more money than my first HDTV you will have an entire home theater 
system that will provide a fantastic experience for you and your entire family.
<br><br>
<strong><em>Braden:</em></strong>
<br><br>
<strong>1) Mitsubishi WD-65735 65-Inch 1080p DLP HDTV</strong> 
($1440 <a target="_blank" href="http://www.htguys.com/shop.php?id=B00166BNGW">Buy Now</a>)
<ul>
<li>Four Star 1080p DLP.</li>
<li>3D Ready, Six color processor, Energy Efficient, 3 HDMI inputs, Support for Deep Color.</li>
<li>Best bang for the buck TV on this year's HDTV buyer's guide list.</li>
<li>If you have the space you should really consider this TV!</li>
</ul>
<br>
<strong>2) Onkyo TX-SR606 7.1 Channel Home Theater Receiver</strong> 
($320 <a target="_blank" href="http://www.htguys.com/shop.php?id=B0015S8PGW">Buy Now</a>)
<ul>
<li>7.1-channel home theater receiver with full HD integration and video upscaling</li>
<li>HDMI v1.3a (4 inputs, 1 output, 1080P compatible), TrueHD, DTS-HD Decoding</li>
<li>1080i Upscaling powered by Faroudja DCDi Edge</li>
<li>Audyssey 2EQ for Loudness Correction</li>
<li>Best value out there! </li>
</ul>
<br>
<strong>3) Polk Audio RM6750 5.1 Channel Home Theater Speaker System</strong> 
($348 <a target="_blank" href="http://www.htguys.com/shop.php?id=B0002WTK4S">Buy Now</a>)
<ul>
<li>Six piece surround sound package that includes 2 front and 2 surround speakers, 1 center-channel speaker, and 1 subwoofer</li>
<li>Satellite and center-channel speakers utilize 3.25-inch polymer-composite cone drivers for distortion-free midrange</li>
<li>Features 0.5-inch silk and polymer-composite dome tweeter in each satellite speakers and 0.75-inch silk and polymer-composite dome tweeter in center-channel speaker</li>
<li>Subwoofer utilizes an eight-inch long-throw dynamic-balance poly-composite driver for longer driver excursion and better linearity</li>
<li>Satellite and center-channel speakers can accommodate 6.1 or 7.1 channel systems</li>
</ul>
<br>
<strong>4) Samsung BD-P2500 1080p Blu-ray Disc Player</strong> 
($307 <a target="_blank" href="http://www.htguys.com/shop.php?id=B001HBHLEY">Buy Now</a>)     
<ul>
<li>Full HD 1080p</li>
<li>BD Live 2.0 Ready</li>
<li>Ethernet and USB for firmware upgrades</li>
<li>Dolby Digital Plus, Dolby Digital True-HD</li>
<li>DTS-HD upgradable</li>
<li>HQV processing chip for the highest quality viewing of Blu-ray discs, upconversion of standard DVDs, and viewing of JPEG images</li>
<li>Stream Netflix Movies Right Over Your Player</li>
<li>7.1-Channel analog audio outputs</li>
</ul>
<br>
<strong>5) Logitech Harmony 720 Universal Remote</strong> 
($119 <a target="_blank" href="http://www.htguys.com/shop.php?id=B000GD3J3G">Buy Now</a>)
<ul>
<li>One-Touch Activity Control - For example, choose Watch a DVD and the Harmony will powers on your TV, DVD, stereo receiver, set all the inputs and even press play!!</li>
<li>Simple for Everyone - There's a handy HELP button so that anyone from the kids to the babysitter can easily access their entertainment</li>
<li>Easy to Set Up - The Harmony set-up wizard does the programming, so you don't have to.</li>
<li>Connected - Just connect your remote to your computer and answer a few questions about how you currently use your audio-visual equipment.</li>
<li>Logitech's patented Smart State Technology does the rest</li>
<li>Rechargable (includes Docking station)</li>
</ul>
<br>
Total: $2534.  Last year: $3908 (and it wasn't really a starter system.  You yelled, I responded)
<br><br><br>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>December 11, 2008 10:10 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1576
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
					AND entry_id <> 1576
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/12/hdtv_and_home_theater_podcast_336_-_hdtv_starter_systems.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
