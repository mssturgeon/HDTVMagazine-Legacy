<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1333";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1333 AND placement_is_primary = 1";
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
	<meta name="keywords" content="converter boxes, atsc tuner, converter box, tuner change, digital analog, digital, get, converter, watch, box, Digital, analog, new, boxes, antenna, tuner, Converter, back, HDTV, change, able, work, signal, price, ATSC" />
	<meta name="description" content="As most of us know the big transition from analog to digital television in the US will happen in less than a year.  Either the public service announcements are starting to work, or people are just finally starting to pay attention, but our inbox is filling up with questions.  We tried to cover the whole issue back in &lt;a href=&quot;http://www.htguys.com/archive/2008/March11.html&quot;&gt;Episode 257&lt;/a&gt;, but we must have left out some crucial details because the email keeps coming.  We'll probably need to do this again in the future, but let's see if we can clarify things a little better." />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #265 - Digital Converter Boxes, a clarification</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_265_-_digital_converter_boxes_a_clarification';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #265 - Digital Converter Boxes, a clarification'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/04/hdtv_and_home_theater_podcast_265_-_digital_converter_boxes_a_clarification.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #265 - Digital Converter Boxes, a clarification</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>April  7, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/04/hdtv_and_home_theater_podcast_265_-_digital_converter_boxes_a_clarification.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/04/hdtv_and_home_theater_podcast_265_-_digital_converter_boxes_a_clarification.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/04/hdtv_and_home_theater_podcast_265_-_digital_converter_boxes_a_clarification.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/04/hdtv_and_home_theater_podcast_265_-_digital_converter_boxes_a_clarification.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23265%20-%20Digital%20Converter%20Boxes%2C%20a%20clarification&amp;bodytext=As%20most%20of%20us%20know%20the%20big%20transition%20from%20analog%20to%20digital%20television%20in%20the%20US%20will%20happen%20in%20less%20than%20a%20year.%20%20Either%20the%20public%20service%20announcements%20are%20starting%20to%20work%2C%20or%20people%20are%20just%20finally%20starting%20to%20pay%20attention%2C%20but%20our%20inbox%20is%20filling%20up%20with%20questions.%20%20We%20tried%20to%20cover%20the%20whole%20issue%20back%20in%20%3Ca%20href%3D%22http%3A%2F%2Fwww.htguys.com%2Farchive%2F2008%2FMarch11.html%22%3EEpisode%20257%3C%2Fa%3E%2C%20but%20we%20must%20have%20left%20out%20some%20crucial%20details%20because%20the%20email%20keeps%20coming.%20%20We%27ll%20probably%20need%20to%20do%20this%20again%20in%20the%20future%2C%20but%20let%27s%20see%20if%20we%20can%20clarify%20things%20a%20little%20better.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
<a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-04-08.mp3">Listen Now - mp3</a>
<a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a>
<a href="http://www.htguys.com">Website</a>

<p><strong>Today's Show:</strong><br />
As most of us know the big transition from analog to digital television in the US will happen in less than a year.  Either the public service announcements are starting to work, or people are just finally starting to pay attention, but our inbox is filling up with questions.  We tried to cover the whole issue back in <a href="http://www.htguys.com/archive/2008/March11.html">Episode 257</a>, but we must have left out some crucial details because the email keeps coming.  We'll probably need to do this again in the future, but let's see if we can clarify things a little better.</p>

<p><strong>Digital Converter Boxes - a clarification</strong></p>

<p><strong>What's happening?</strong><br />
Right now if you use an antenna to watch TV, it arrives in your home as an analog signal.  You need what's referred to as an NTSC tuner to change the channel and watch what the antenna picks up.  When the transition happens, the signal that will arrive in your home will be digital.  You'll need an ATSC tuner to change channels and watch what's on TV.  The two technologies are not compatible.  So if you do not have an ATSC tuner, will will not be able to watch TV.</p>

<p>Hopefully the public service announcements and this show have worked and everyone is up to speed on that part.</p>

<p><strong>What do the converter boxes do?</strong><br />
The government is giving out coupons to those who need them to help them purchase an ATSC tuner so they can continue to watch television.  These tuners will decode the new digital signal and turn it into the old analog signal you've been using up to this point.  It you use the TV to change channels, you'll be able to just disconnect the TV from the antenna, connect the box to the antenna and then connect the box to the TV and nothing in your life will change.  You just watch TV like you always have.  Essentially you just insert this new box between the TV and the antenna and it fixes everything for you.</p>

<p><strong>Are the new converter boxes HDTV?</strong><br />
No, absolutely not.  Some support S-Video, but for the most part they just output composite video or NTSC over coax.  You will be able to watch any HDTV shows that might happen to be on those digital channels, but they will be down converted to standard definition quality.  You can not use the coupon to get a free HDTV tuner.</p>

<p><strong>What one should you get?</strong><br />
There are over 50 models listed online to choose from, we haven't used a single one of them.  And most likely we never will.  Many of the emails we receive are asking for advice on which on to get for a family member or friend who needs help making the transition.  The specifications of the boxes are mandated by the government, so the feature set on all of them is going to be nearly identical.  Of course some of them may do the ATSC to NTSC conversion and down-convert the video a little better, but without using them, we'll never really know.  If it was up to us we'd get the Echostar TR-40, but it isn't available yet.  So if you already have your coupons, and that 90 day clock is ticking, we'd get one of these for our relatives:</p>

<p><a href="http://www.bestbuy.com/site/olspage.jsp?skuId=8624081&type=product&id=1199495190393">Insignia® - Digital-to-Analog Converter for Analog TVs</a><br />
Model: NS-DXA1<br />
Price: $59.99<br />
First of all Best Buy is convenient.  There's probably one near by.  And secondly, if it doesn't work you can very easily take it back and get a new one.  It's a bit on the pricey side, but you're paying for that "just in case" factor.  And it's Energy Star compliant.</p>

<p><a href="http://www.radioshack.com/sm-buy-the-digital-stream-dtx9900-digital-to-analog-converter-box--pi-3043012.html">Digital Stream DTX9900 Digital-to-Analog Converter Box</a><br />
Model: DTX9900<br />
Price: $59.99<br />
For all the same reasons as the Insignia one from Best Buy.  There's a Radio Shack in just about every neighborhood and mall, so you should be able to get your hands on a box without issue.  If it doesn't work out, just take it back for a new one.  In our experience, Radio Shack tends to be very easy to work with.</p>

<p><a href="http://www.walmart.com/catalog/product.do?product_id=8343230">RCA Digital TV Converter Box</a><br />
Model: DTA800<br />
Price: $49.87<br />
This is purely for the price.  Of course there's probably a Wal-mart in your back yard, but for $9.87 after you apply the coupon, that's hard to beat.  You can spend the extra few bucks you save on some snacks to munch on while you're watching digital television.</p>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>April  7, 2008 11:58 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1333
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
					AND entry_id <> 1333
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/04/hdtv_and_home_theater_podcast_265_-_digital_converter_boxes_a_clarification.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
