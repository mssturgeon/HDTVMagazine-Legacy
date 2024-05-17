<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1281";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1281 AND placement_is_primary = 1";
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
	<meta name="keywords" content="blu ray, ray player, home theater, high definition, stream network, blu, ray, Blu, player, network, support, really, video, audio, playback, need, players, theater, well, home, any, first, remote, internet, control" />
	<meta name="description" content="It's pretty clear by now that Blu-ray won the high definition video disk war.  By the end of this year HD-DVD could be a distant memory and those who want to watch High Definition movies will need a Blu-ray player.  There are a bunch of options out there for those in the market, and the Sony PlayStation 3 has been talked about as the most capable player available.  For a long time it was the least expensive way to get a Blu-ray player.  That isn't true anymore, but it's still close.  It's currently available in two models, a 40 GB model for $399.99 and an 80 GB model for $499.99.  Today we'll ignore the gaming side for the most part and look at how the device performs as a home theater device." />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #254 - Playstation 3 in your Home Theater</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_254_-_playstation_3_in_your_home_theater';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #254 - Playstation 3 in your Home Theater'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/02/hdtv_and_home_theater_podcast_254_-_playstation_3_in_your_home_theater.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #254 - Playstation 3 in your Home Theater</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>February 29, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/02/hdtv_and_home_theater_podcast_254_-_playstation_3_in_your_home_theater.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/02/hdtv_and_home_theater_podcast_254_-_playstation_3_in_your_home_theater.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/02/hdtv_and_home_theater_podcast_254_-_playstation_3_in_your_home_theater.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/02/hdtv_and_home_theater_podcast_254_-_playstation_3_in_your_home_theater.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23254%20-%20Playstation%203%20in%20your%20Home%20Theater&amp;bodytext=It%27s%20pretty%20clear%20by%20now%20that%20Blu-ray%20won%20the%20high%20definition%20video%20disk%20war.%20%20By%20the%20end%20of%20this%20year%20HD-DVD%20could%20be%20a%20distant%20memory%20and%20those%20who%20want%20to%20watch%20High%20Definition%20movies%20will%20need%20a%20Blu-ray%20player.%20%20There%20are%20a%20bunch%20of%20options%20out%20there%20for%20those%20in%20the%20market%2C%20and%20the%20Sony%20PlayStation%203%20has%20been%20talked%20about%20as%20the%20most%20capable%20player%20available.%20%20For%20a%20long%20time%20it%20was%20the%20least%20expensive%20way%20to%20get%20a%20Blu-ray%20player.%20%20That%20isn%27t%20true%20anymore%2C%20but%20it%27s%20still%20close.%20%20It%27s%20currently%20available%20in%20two%20models%2C%20a%2040%20GB%20model%20for%20%24399.99%20and%20an%2080%20GB%20model%20for%20%24499.99.%20%20Today%20we%27ll%20ignore%20the%20gaming%20side%20for%20the%20most%20part%20and%20look%20at%20how%20the%20device%20performs%20as%20a%20home%20theater%20device.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
<strong>Today's Show:</strong><br>
<br>
<a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-02-29.mp3">Listen Now - mp3</a>
<br>
<a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a>
<br>
<a href="http://www.htguys.com">Website</a>
<br>
<br>
<strong>Today's Show:</strong>
<br>It's pretty clear by now that Blu-ray won the high definition video disk war.  By the end of this year HD-DVD could be a distant memory and those who want to watch High Definition movies will need a Blu-ray player.  There are a bunch of options out there for those in the market, and the Sony PlayStation 3 has been talked about as the most capable player available.  For a long time it was the least expensive way to get a Blu-ray player.  That isn't true anymore, but it's still close.  It's currently available in two models, a 40 GB model for $399.99 and an 80 GB model for $499.99.  Today we'll ignore the gaming side for the most part and look at how it performs as a home theater device.<br><br>
<strong>Sony PS3 in the Home Theater</strong> <br>
<br>
  There are a few differences between the 40 GB and 80 GB models, other than the obvious difference in storage space, but for this discussion they aren't very important.  Both models are High Definition (1080p) capable, have Blu-ray players built in, support HDMI 1.3 and can connect to your network either wired or via WiFi.  For what we need, they both work.  But as an aside, if you own a PS2 and want to play all those existing games, you need to go with the more expensive 80 GB model, since it's the only one that supports backward compatibility.  At a glance the Blu-ray player supports 1080p video and 7.1 channel audio, the HDMI 1.3 connection allows for deep color content and it can upconvert standard DVDs to 1080p as well.<br>
<br>
  So how about the PS3 as a Blu-ray player?  In a lot of cases the primary motivator for buying a PS3 is to get Blu-ray playback.  In that capacity it does a great job with playback, but may have some usability issues.  First, the playback.  The video looks absolutely stunning and the audio quality is amazing.  We haven't seen Blu-ray look or sound any better than on the PS3, nor have we seen it any worse, but that just means it is as good as any Blu-ray player out there.  You do not suffer any quality loss because it's a gaming console and not a dedicated player.  <br>
<br>
  As for usability however, it's not the 100% experience you'd want.  First off, the Blu-ray remote, the one that allows you to control the PS3 as a player instead of using the game controller, is an add-on accessory.  If Sony really wants to market the PS3 as a Blu-ray player, that remote should really be bundled in the box.  It's only $24.99, ($19.99 online) not a deal breaker by any stretch, but you know what we're saying.  Trying to control a Blu-ray movie with the game controller is possible, but not practical or even really that pleasant.  After you add the remote, you can control the Box just like it's a stand-alone Blu-ray player.  It works really well for that.  Unfortunately it doesn't work using IR, it uses Bluetooth.  So your Harmony or other Universal remote can't control it.  There are some adapter devices out there, but they don't get you all the way there, closer, but not all the way.  We have a listener review of one on <a id="ct9p" href="http://www.htguys.com/archive/2007/December28.html" target="_blank" title="Episode 236">Episode 236</a>.<br>
<br>
We all know that not all Blu-ray players are created equal.  They support different audio codecs, some support interactivity, others don't, etc.  It's the whole Blu-ray profile 1.0, 1.1., 2.0 mess we've talked about in the past.  One big benefit of the PS3 is that it was designed to be Internet connected and upgradable, so it's pretty easy to add new functionality as it gets finalized by the Blu-ray group.  The PS3 will be among the first players to offer support for BDLive, or Internet-enabled interactive Blu-ray content.  The rumor is that a new firmware update will be available for the PS3 as early as May or June or this year, which coincides with the market release of the first stand-alone BDLive enabled players.  As to audio codecs, the PS3 supports the standard Dolby Digital and DTS, and the new Dolby TrueHD (PCM/onboard decoding, not Bitstream) but not the new DTS HD.  It's unclear whether or not the PS3 will ever support DTS HD, and there are a few players on the market that do, so in that regards you might be missing a little.<br>
<br>
But the PS3 can also do a little bit more, it actually has some non-Blu-ray home theater features to consider.  If you buy the PS3 as a Blu-ray player, these are added bonuses, as are the gaming abilities, but they're in there, so we'll talk about them.  First off the user interface on the PS3 is really slick.  Very easy to navigate and easy to understand.  If you start it up with a disc in the drive, or insert one after starting it up, it will go straight to the disc, bypassing the interface entirely, but if you do need to poke around in there, it's pretty simple.  And while poking around in there you'll find support for media playback.  You can play local content downloaded from the Internet or on USB portable storage, or stream from the network.  To stream from the network you need to have a DLNA server to dish out the files, but those are easy to find.  Here's a lit of a bunch of options <a id="v3a5" href="http://www.rbgrn.net/blog/2007/08/how-to-choose-dlna-media-server-software-in-windows-mac-os-x-or-linux.html" target="_blank" title="here">here</a> and <a id="qhov" href="http://mediaproductreview.blogspot.com/2005/12/review-of-upnpdlna-media-server.html" target="_blank" title="here">here</a>.<br>
<br>
The PS3 works well as a network player, but the interface isn't all that sexy.  It gets the job done, but without the "wow" factor.  It will playback audio, as MP3 or WMV, photos, and MPEG-4 videos including support for DIVX.  Many DLNA servers will actually transcode video on the fly to match what the PS3 needs, but not all of them, so you may need to do some re-encoding.  We watched I Am ... not going to tell you what we watched, but we did watch a few movies over the network and they looked and sounded fine.  Obviously the better the compression the better they'll look.  As a network based audio/video player, it works.  But don't buy it specifically for that.  Downloaded movie trailers looked absolutely amazing, so the PS3 is certainly capable of some awe inspiring video playback.  There is the option to install another OS on the PS3, but we haven't gotten that deep into it yet to comment on how well it works.  There are differing reports on the Internet about it.<br>
<br>
Overall the PS3 is a great Blu-ray player and upconverting DVD player, does a solid job playing network content, and is actually a really good game platform as well, if anyone is still interested in that.  For $399 it really makes a lot of sense because of the upgradability and future-proof nature of the architecture.<br>
<br>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>February 29, 2008 06:24 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1281
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
					AND entry_id <> 1281
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/02/hdtv_and_home_theater_podcast_254_-_playstation_3_in_your_home_theater.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
