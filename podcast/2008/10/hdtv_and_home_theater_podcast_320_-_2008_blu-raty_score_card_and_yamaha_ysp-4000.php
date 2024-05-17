<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1521";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1521 AND placement_is_primary = 1";
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
	<meta name="keywords" content="sound projector, yamaha ysp, surround sound, home theater, side bed, sound, surround, YSP, ysp, projector, unit, room, Projector, Sound, Yamaha, calibration, experience, yamaha, microphone, home, beam, bed, Beam, placement, buy" />
	<meta name="description" content="We first saw the Yamaha YSP-4000 Sound Projector in use at CES 2007. At that time we were quite impressed that a single unit could generate a 5.1 sound field. For those of you who are not familiar with what the Yamaha Sound Projector, it is single unit resembling an oversized center channel that decodes surround sound and generates multi-channel experience by bouncing sound waves off the walls in your room. The advantages are obvious, no running of speaker wire, easy placement of a single component, no clutter! This type of device is ideal for an apartment dweller or for use in a master bedroom, or for the person who wants surround sound but doesn't want to deal with the installation of a multi piece system.
" />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #320 - 2008 Blu-raty score card and Yamaha YSP-4000</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_320_-_2008_blu-raty_score_card_and_yamaha_ysp-4000';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #320 - 2008 Blu-raty score card and Yamaha YSP-4000'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/10/hdtv_and_home_theater_podcast_320_-_2008_blu-raty_score_card_and_yamaha_ysp-4000.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #320 - 2008 Blu-raty score card and Yamaha YSP-4000</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>October 16, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/10/hdtv_and_home_theater_podcast_320_-_2008_blu-raty_score_card_and_yamaha_ysp-4000.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/10/hdtv_and_home_theater_podcast_320_-_2008_blu-raty_score_card_and_yamaha_ysp-4000.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/10/hdtv_and_home_theater_podcast_320_-_2008_blu-raty_score_card_and_yamaha_ysp-4000.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/10/hdtv_and_home_theater_podcast_320_-_2008_blu-raty_score_card_and_yamaha_ysp-4000.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23320%20-%202008%20Blu-raty%20score%20card%20and%20Yamaha%20YSP-4000&amp;bodytext=We%20first%20saw%20the%20Yamaha%20YSP-4000%20Sound%20Projector%20in%20use%20at%20CES%202007.%20At%20that%20time%20we%20were%20quite%20impressed%20that%20a%20single%20unit%20could%20generate%20a%205.1%20sound%20field.%20For%20those%20of%20you%20who%20are%20not%20familiar%20with%20what%20the%20Yamaha%20Sound%20Projector%2C%20it%20is%20single%20unit%20resembling%20an%20oversized%20center%20channel%20that%20decodes%20surround%20sound%20and%20generates%20multi-channel%20experience%20by%20bouncing%20sound%20waves%20off%20the%20walls%20in%20your%20room.%20The%20advantages%20are%20obvious%2C%20no%20running%20of%20speaker%20wire%2C%20easy%20placement%20of%20a%20single%20component%2C%20no%20clutter%21%20This%20type%20of%20device%20is%20ideal%20for%20an%20apartment%20dweller%20or%20for%20use%20in%20a%20master%20bedroom%2C%20or%20for%20the%20person%20who%20wants%20surround%20sound%20but%20doesn%27t%20want%20to%20deal%20with%20the%20installation%20of%20a%20multi%20piece%20system.%0A&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
<div><a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-10-17.mp3">Listen Now - mp3</a></div>
<div><a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a></div>
<div><a href="http://www.htguys.com">Website</a></div><br>

<br><strong>Today's Show:</strong><br>
<a href="http://www.soundandvisionmag.com/bluray/3000/blu-ray-2008-the-studio-report-card.html" title="" target="_blank">Blu-ray 2008: The Studio Report Card</a><br>

<br><a href="http://www.yamaha.com/yec/products/productdetail.html?CNTID=556966&amp;CTID=5001100#" id="fe11" target="_blank" title="Yamaha YSP-4000">Yamaha YSP-4000</a> (<a href="http://www.htguys.com/shop.php?id=B000V7H1HM" id="tmzh" target="_blank" title="Buy Now $1600">Buy Now $1600</a>)<br>
<br>
We first saw the Yamaha YSP-4000 Sound Projector in use at CES 2007. At
that time we were quite impressed that a single unit could generate a
5.1 sound field. For those of you who are not familiar with what the
Yamaha Sound Projector, it is single unit resembling an oversized
center channel that decodes surround sound and generates multi-channel
experience by bouncing sound waves off the walls in your room. The
advantages are obvious, no running of speaker wire, easy placement of a
single component, no clutter! This type of device is ideal for an
apartment dweller or for use in a master bedroom, or for the person who
wants surround sound but doesn't want to deal with the installation of
a multi piece system.<br>
<br>
<div> </div>
<div><strong>Some of the features of the YSP-4000:</strong></div>
<ul>
<li>
    Analog video to HDMI digital video up-conversion</li>

<li>
    High-Definition Video Upscaling to 1080i/720p</li>
<li>
    IntelliBeam Automated System Calibration with Direct Start</li>
<li>
    HDMI interface (1080p/24Hz and 60Hz compatible)</li>
<li>
    XM ready with XM HD Surround powered by Neural Surround</li>
<li>

    iPod compatibility via optional Yamaha YDS-10</li>
<li>
    Front panel mini jack for connecting portable audio player</li>
<li>
    New modes: My Surround and 5ch Stereo</li>
<li>
    Five other modes: 5 Beam, 3 Beam, 3 Beam + Stereo, 2ch Stereo and My Beam with dual remote doors</li>
<li>
    Built-in FM tuner</li>

<li>
    Custom installation compatibility</li></ul><br>
<div><strong>
  Setup:</strong></div>
<div>Setting up the YSP-4000 is no different than setting up any
switching home theater receiver. You connect your source components to
the Sound Projector and then connect and HDMI output to the TV. The
YSP-4000 on supports two HDMI inputs. The 4000 also supports two
component, three composite, and two digital optical and coaxial audio
inputs. Once all the connections were made you connect a microphone and
start the automatic room calibration. This took a few times to get it
to run successfully.<br>
<br> </div>
<div> </div>
<div>There are some
requirements about where you can place the microphone in relation to
the Sound Projector. This may limit how well the surround performs. In
general the microphone needs down the centerline of the Sound Projector
and and no more than 1 meter above or below. Due to the room's
dimensions that it was installed in, we had to move the orientation of
the 4000 to get a calibration that succeeded. <br>
<br></div>

<div> </div>
<div>The
unit also supports an iPod dock and XM Radio module. We received both
and installed them into the unit. Each has a port on the back of the
unit and only took a few seconds to install. One word of caution on the
XM Radio unit. If you don't have a South facing window near the unit
you won't be able to receive XM radio. The unit also supports a
subwoofer. Our tests did not include one but we strongly recommend that
you use one for the full surround experience.<br>
<br></div>
<div> </div>
<div>A
nice feature of the 4000 is that it can store three calibration setups
in memory. This is very important because the room configuration is
important to the experience. In our case we ran two calibrations, one
with the windows and blinds open and one with them closed. The Sound
Projector produced a better experience with the windows and blinds
closed. However, if we did not run a calibration with the windows open
the surround experience would have been completely lost. There is also
a feature called "My Beam" that directs all the audio to your sitting
position eliminating any surround effects. Before you buy the YSP-4000
we recommend downloading the <a id="i4vc" href="http://www.yamaha.com/yamahavgn/Documents/YEC/Digital_Sound_Projectors/Manual/YSP-4000_UA.pdf" target="_blank" title="User's Manual">User's Manual</a> (Free
Registration Required) and reading the section on installation. It has
clear diagrams for optimal room placement. This will save you some
grief if you have a room that the projector just won't work in.<br>
<br></div>
<div> </div>
<div><strong>Performance:</strong></div>

<div>After
hearing how good the YSP-4000 sounded at CES and CEDIA we were hoping
for the best. Due to the placement of the TV and the shape of the room (we tested in a master bedroom, <a id="jdia" href="http://www.htguys.com/archive/2008/YSP-4000.html" target="_blank" title="See Pictures">See Pictures</a>)
we weren't expecting it to be perfect. But if we were able to get any
sort of multi-channel sound we'd call it a success. The answer is that on one side of the bed the sound was fantastic and on the other, just 20 inches to
the right, the sound was good.&nbsp; Due to the positioning of the microphone
per the instructions the optimal sound was on one side of the
bed when sitting, not lying down. In that position the surround sound was very
strong. During playback we would look at the walls around us in
amazement. There were no speakers there but the sound was definitely
coming from the wall just above our heads. <br>
<br></div>
<div> </div>
<div>The
experience was not as good on the other side of the bed. While the sound
was far more expansive it was not as pinpoint. It was still an
improvement over the TV speakers alone. This drove home the fact that
the proper placement of the speaker makes a big difference. We ran a
final setup with the unit in a position that would not have been acceptable
to the aesthetics committee but was more conducive to the proper calibration. For this last
test the microphone was placed right down the middle of the bed. In this
case both sides of the bed performed quite well. The sound projector really works well if
placed properly.<br>
<br></div>
<div> </div>
<div>The other area that was
tested was music playback. We were quite pleased with how well the 4000
produced music. There was a good sense of stereo and openness.<br>
<br> </div>

<div> </div>
<div><strong>HT Guys Advice:</strong></div>
<div>The
Sound Projector is an amazing device that makes setting up a home
theater simple. While its true that you can buy a traditional home
theater surround sound system for less, you can't buy one that is
easier to install. Just be sure to read the <a id="n3jx" href="http://www.yamaha.com/yamahavgn/Documents/YEC/Digital_Sound_Projectors/Manual/YSP-4000_UA.pdf" target="_blank" title="User's Manual">User's Manual</a> first to make sure that the Yamaha YSP-4000 will work in your room layout.<br><br><br>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>October 16, 2008 11:38 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1521
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
					AND entry_id <> 1521
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/10/hdtv_and_home_theater_podcast_320_-_2008_blu-raty_score_card_and_yamaha_ysp-4000.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
