<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1412";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1412 AND placement_is_primary = 1";
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
	<meta name="keywords" content="home theater, been replaced, flat panel, convergence device, video rental, home, theater, video, Internet, internet, technology, dvi, movie, could, sed, SED, vhs, DVI, VHS, format, digital, CRT, crt, still, inch" />
	<meta name="description" content="Today we've decided to take a little walk down Home Theater memory lane.  The last couple years have seen some significant changes in home theater technology, and we've seen a few devices go extinct.  A few are still on the endangered species list, but will probably be gone very soon.  We'd like to bid farewell to some technological advances that changed the way we enjoy entertainment in our homes, or were supposed to, but maybe it didn't quite pan out." />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #279 - Home Theater Memory Lane</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_279_-_home_theater_memory_lane';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #279 - Home Theater Memory Lane'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/05/hdtv_and_home_theater_podcast_279_-_home_theater_memory_lane.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #279 - Home Theater Memory Lane</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>May 27, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/05/hdtv_and_home_theater_podcast_279_-_home_theater_memory_lane.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/05/hdtv_and_home_theater_podcast_279_-_home_theater_memory_lane.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/05/hdtv_and_home_theater_podcast_279_-_home_theater_memory_lane.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/05/hdtv_and_home_theater_podcast_279_-_home_theater_memory_lane.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23279%20-%20Home%20Theater%20Memory%20Lane&amp;bodytext=Today%20we%27ve%20decided%20to%20take%20a%20little%20walk%20down%20Home%20Theater%20memory%20lane.%20%20The%20last%20couple%20years%20have%20seen%20some%20significant%20changes%20in%20home%20theater%20technology%2C%20and%20we%27ve%20seen%20a%20few%20devices%20go%20extinct.%20%20A%20few%20are%20still%20on%20the%20endangered%20species%20list%2C%20but%20will%20probably%20be%20gone%20very%20soon.%20%20We%27d%20like%20to%20bid%20farewell%20to%20some%20technological%20advances%20that%20changed%20the%20way%20we%20enjoy%20entertainment%20in%20our%20homes%2C%20or%20were%20supposed%20to%2C%20but%20maybe%20it%20didn%27t%20quite%20pan%20out.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
<a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-05-27.mp3">Listen Now - mp3</a>
<a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a>
<a href="http://www.htguys.com">Website</a>

<p><strong>Today's Show:</strong><br />
Today we've decided to take a little walk down Home Theater memory lane.  The last couple years have seen some significant changes in home theater technology, and we've seen a few devices go extinct.  A few are still on the endangered species list, but will probably be gone very soon.  We'd like to bid farewell to some technological advances that changed the way we enjoy entertainment in our homes, or were supposed to, but maybe it didn't quite pan out.</p>

<p><strong><em>Aside: Is the Internet the ultimate convergence device?</em></strong></p>

<p>Panasonic recently announced a new Plasma TV line, the the <a target="_blank" href="http://www.i4u.com/article17468.html">PZ850 series</a>.  The new sets will feature a technology they're calling "VIERA CAST" which will give you direct access to web content like YouTube, Google’s Picasa online photo albums and Bloomberg, all directly on the HDTV without a set-top box.  They'll be available in mid-June in four screen sizes: 46-inch (TH-46PZ850), 50-inch class (TH-50PZ850), 58-inch (TH-58PZ850) and 65-inch (TH-65PZ850).  All will be 1080p and will support native 24p playback.  They'll range in price from $3000 to $8000 US.  We couldn't find any information on whether or not they will support wireless network connections, or will require a hardwired input.<br />
 <br />
Sure, Panasonic isn't the first to have a network connect TV, HP has been there for a while, but this whole "Internet connected" TV thing got us thinking.  With all the talk over the last few year about "convergence" of home theater devices, could the Internet be that ultimate convergence device?  You could presumably get to <a target="_blank" href="http://www.hulu.com/">Hulu.com</a> from a TV like this and watch all the shows and movies available there.  Add on sites like NetFlix "Watch Now" and you're starting to build quite a library of content, without needing Cable or Satellite service.  What if the drive that currently sits in your Vudu box was actually on the Internet somewhere.  You could buy or rent movies and watch them on any Internet connected TV in your home.  Later, when high speed mobile Internet (4G) becomes reality, you could even watch them from the car or your cell phone.</p>

<p>The only thing we'd still need to solve is access to live content.  It should be simple enough for each network to stream a feed on the Internet that anyone could get to.  Then sites could aggregate those feeds into "channels" to make them easy to find and use.  Why couldn't CNN and ESPN simply stream out a high quality feed on the Internet?  Make it subscription based or even add supported to cover bandwidth costs and what else is there?</p>

<p>The Internet may become the only convergence device you'll ever need.  Just ad a screen wherever you need it, or wherever you happen to be, and something to render the audio, and all of the rest of your home theater devices become obsolete. <br />
 <br />
<strong>Looking Back on Home Theater</strong></p>

<p><strong>CRT Televisions</strong><br />
The Home Theater revolution really began in 1946 after the end of World War II.  During the war, manufacture of televisions was halted, but when that restriction was lifted, the technology came into its own.  Of course those early CRTs were nothing like the ones that recently disappeared from store shelves, but they ushered in the era of in-home entertainment.  The earliest production sets sold hit the market even before the war.  In 1938 a 3-inch CRT television cost $125 US, the equivalent of $1863 in 2007 dollars.  The luxury 12-inch model cost $445 US, or $6633 in 2007.  While the adjusted-for-inflation prices haven't changed that much over the years, the definition of family time has been forever altered.  Most of us have fond memories of sitting around the TV with our parents, brothers and sisters enjoying a prime time show.</p>

<p>In 1946 only 0.5% of U.S. households had a television set, by 1954 55.7% had one, and that number hit 90% as early as 1962.  Meanwhile, in 1947 in Britain, there were 15,000 households with a TV, that number climbed to 1.4 million in 1952, and shot up to 15.1 million by 1968.  In recent years the trusted CRT television has been replaced by digital microdisplay technology like DLP and LCD, and newer, sexier flat panel technologies like flat LCD and plasma.  In 2007 LCD televisions surpassed CRT televisions in total global sales, hitting 47% - pushing CRT down to 46%.  And in 2007 Best Buy, one of the largest television retailers in the US, announced that they would no longer carry any analog TV sets.  While they still sell a few digital CRTs, were quickly seeing the good old CRT become part of the good old days.</p>

<p><strong>VCR</strong><br />
Nothing enabled Home Movie entertainment more then the advent of the VCR.  Before the mass market success of the Video Cassette Recorder in the early 1980s, we were all slaves to the television programming schedule.  You had to be home to watch something when it was on, or you'd miss it, and probably never see it again.  Forget the idea of sitting down to a movie with friends and family.  The VCR also gave us the first real home theater format war, pitting Sony's Betamax format against JVC's VHS format.  By most accounts Beta was a superior format, but VHS stole the hearts and minds of the consumer and won the war due, in part, to its longer recording times.  You could fit a two hour movie on one VHS tape, but the one hour limit on a Betmax tape (until the release of Beta II and Beta III) hurt its adoption.</p>

<p>With the VCR came video rental stores, and with that came a whole new revolution of in-home movie viewing, otherwise known as the home theater.  The stores popped up on every corner and in every strip mall you could find.  And the phenomenon exploded.  Blockbuster took over for most of the smaller, mom and pop style rental stores and became a huge player in home theater.  Lately online video rental like NetFlix and downloadable movie services like Vudu have threatened to destroy Blockbuster's business model, but it's still way too early to put brick and mortar movie rental on the endangered list.  Although VHS rental is entirely gone, having been replaced by DVD for some time.  It took a decade, but DVD overtook VHS in 2003 and hasn't looked back.  On the recording side, DVRs have begun to displace VHS as the technology of choice to record television programming.  It is estimated that one in five US households has at least one DVR, with that number set to reach 50% by 2011.</p>

<p><strong>Laserdisc</strong><br />
Laserdisc was going to usher in a new era of home theater quality with an experience unlike any other.  Technologically superior to VHS for both audio and video, it was the first optical media format to make it to consumers.  On the video side, the format supported 425 lines of resolution, compared with the 240 on a VHS tape.  The discs also supported digital audio like Dolby Digital and DTS, the same formats we find on DVD discs today.  However, while it provided superior quality, it also had a few shortcomings and never really caught on with consumers.  First of all, the discs were huge, measuring almost a full foot (30 cm) in diameter and were quite heavy.  This size made them difficult to deal with, easy to damage and required more powerful (in other words louder) equipment to spin them.  In addition, each disc only held 30 to 60 minutes of video per side, requiring consumers to flip the disc to see both halves of a movie.  If a movie was too big to fit on two sides of one disc, they'd have to swap in a second disc as well.</p>

<p>In 1998, it was estimated that laserdisc player had a market penetration of approximately two million US households or about 2%.  That number never really increased, and the technology was eventually completely displaced by DVD.</p>

<p><strong>DVI</strong><br />
We know we're sure to get email on this one, but as a home theater format, DVI or Digital Video Interface cables have all but been replaced by HDMI.  In the early days of HDTV, DVI was hailed as the best option for video cabling.  It provided the only digital connection between your HDTV source, like an ATSC tuner or a set-top box, and your display.  As HDMI crept into the picture, DVI began to lose its luster.  HDMI, or High Definition Multimedia Interface, carried the equivalent video information, but also bundled digital surround sound audio on the same cable.  Since those early days of HDMI 1.0 and 1.1, version 1.3 of the HDMI spec has been released, surpassing the video capabilities of the original DVI cable.  Still alive and kicking in the IT space, DVI has seen its days of glory as the go-to cable for HDTV fade into the sunset.  There are rumors that DisplayPort may begin to push DVI out of the PC/IT market as well.  Farewell DVI, it was fun while it lasted.</p>

<p><strong>SED</strong><br />
We first talked about SED in May of 2005, on <a target="_blank" href="http://www.htguys.com/archive/2005/May122005.html">Episode #7</a>.  <em>Surface-conduction Emission Display - SED works very much like traditional CRT technology, shooting a beam of electrons to excite phosphors on a screen. Unlike, CRTs, though SED uses a thin strip of Palladium Oxide as an electron emitter, instead of a huge cathode ray tube. This allows the whole display system to be manufactured as thin as 10 millimeters thick.</em>  We believed back in 2005 that SED had a chance to replace plasma and flat LCD as the dominant flat panel display technology.  In subsequent years at shows like CES we even saw amazing demos from the two main companies behind the technology, Canon and Toshiba.  Legal issues over patent infringement hurt SED, as did the time it took to get to market.  In that time plasma and LCD worked out the kinks, improved efficiency in manufacturing and economies of scale and greatly increased the barrier to entry in the flat panel display market.  It's rumored that Canon is still working on SED displays.  But at this point, it looks like SED will go the way of the Dodo.</p>

<p><br />
<em>Researched at <a href="http://www.wikipedia.org" target="_blank">wikipedia.org</a> and several other Internet sites.</em><br />
</p>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>May 27, 2008 02:15 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1412
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
					AND entry_id <> 1412
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/05/hdtv_and_home_theater_podcast_279_-_home_theater_memory_lane.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
