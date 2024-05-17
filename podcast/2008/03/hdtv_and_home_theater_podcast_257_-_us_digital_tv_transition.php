<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1292";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1292 AND placement_is_primary = 1";
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
	<meta name="keywords" content="atsc tuner, converter box, cable satellite, million coupons, television service, television, digital, transition, tuner, coupons, ATSC, atsc, air, may, shows, coupon, those, antenna, need, ntsc, signals, get, HDTV, converter, box" />
	<meta name="description" content="US Digital TV Transition

The United States is set to transition all over the air, or free to air, television broadcasts from analog to digital in just under a year from now.  It acronym speak, we'll all need to use ATSC tuners instead of the NTSC tuners we've been using for the past half century.  There are a bunch of questions swirling about the transition, we'll try to answer the most popular ones.  If you live outside the US, this information may apply to you at some point in the future, when your governing body decides to make a similar transition." />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #257 - US Digital TV Transition</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_257_-_us_digital_tv_transition';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #257 - US Digital TV Transition'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/03/hdtv_and_home_theater_podcast_257_-_us_digital_tv_transition.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #257 - US Digital TV Transition</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>March  9, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/03/hdtv_and_home_theater_podcast_257_-_us_digital_tv_transition.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/03/hdtv_and_home_theater_podcast_257_-_us_digital_tv_transition.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/03/hdtv_and_home_theater_podcast_257_-_us_digital_tv_transition.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/03/hdtv_and_home_theater_podcast_257_-_us_digital_tv_transition.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23257%20-%20US%20Digital%20TV%20Transition&amp;bodytext=US%20Digital%20TV%20Transition%0A%0AThe%20United%20States%20is%20set%20to%20transition%20all%20over%20the%20air%2C%20or%20free%20to%20air%2C%20television%20broadcasts%20from%20analog%20to%20digital%20in%20just%20under%20a%20year%20from%20now.%20%20It%20acronym%20speak%2C%20we%27ll%20all%20need%20to%20use%20ATSC%20tuners%20instead%20of%20the%20NTSC%20tuners%20we%27ve%20been%20using%20for%20the%20past%20half%20century.%20%20There%20are%20a%20bunch%20of%20questions%20swirling%20about%20the%20transition%2C%20we%27ll%20try%20to%20answer%20the%20most%20popular%20ones.%20%20If%20you%20live%20outside%20the%20US%2C%20this%20information%20may%20apply%20to%20you%20at%20some%20point%20in%20the%20future%2C%20when%20your%20governing%20body%20decides%20to%20make%20a%20similar%20transition.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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

<br>
<a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-03-11.mp3">Listen Now - mp3</a>
<br>
<a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a>
<br>
<a href="http://www.htguys.com">Website</a>
<br>
<br>
<div><strong>Today's Show:</strong></div>
<p><strong>US Digital TV Transition</strong><br>
<br>The United States is set to
transition all over the air, or free to air, television broadcasts from
analog to digital in just under a year from now.&nbsp; It acronym speak,
we'll all need to use ATSC tuners instead of the NTSC tuners we've been
using for the past half century.&nbsp; There are a bunch of questions
swirling about the transition, we'll try to answer the most popular
ones.&nbsp; If you live outside the US, this information may apply to you at
some point in the future, when your governing body decides to make a
similar transition.<br>

<br><em>When will the transition happen?</em><br>
On
February 17, 2009 all analog broadcasts will go dark and the only
remaining television signals flying through the air will be digital.&nbsp;
Those digital signals may already exist, and if they don't they will
certainly be in place well before February 17.&nbsp; You should have plenty
of time to get any new equipment you need and make sure it works before
you lose television altogether.<br>
<br><em>Will this affect me?</em><br>
That
depends.&nbsp; If you use an antenna to watch television, you may be
affected.&nbsp; It doesn't matter if you have an indoor antenna, rabbit ears
or a massive antenna mounted to your roof, anyone with an antenna
should pay close attention.&nbsp; If you watch TV via Cable or Satellite,
you won't need to do a thing.&nbsp; Your provider will take care of
everything for you.&nbsp; If you're one of those whole only get television
over the air, you must have either a digital television with an ATSC
tuner built in, or an external ATSC tuner that can convert the digital
signal into analog for your existing TV.<br>

<br><em>What do I need to do to be ready?</em><br>
If
you already have a television with a built-in ATSC tuner, you're all
set.&nbsp; If not, you need a converter box.&nbsp; Luckily, the US Government
isn't leaving you out in the cold.&nbsp; Every household can apply for up to
two TV Converter Box Coupons.&nbsp; These coupons are good for $40 towards
the purchase of any approved converter box.&nbsp; Everyone is eligible to
receive a coupon, but supplies are limited. There are 22.25 million
coupons
available to all US households. Once those coupons have been used,
there are an additional 11.25 million coupons available only to
households that solely receive their TV broadcasts over-the-air using
an antenna. Households with TVs connected to cable, satellite or other
pay TV service are not eligible for this second batch of coupons.
Consumers can apply for coupons until March 31, 2009, or until the
funds are exhausted.<br>
<br><em>How do I get a coupon?</em><br>
The easiest way to do it is to go online to <a title="https://www.dtv2009.gov/" target="_blank" href="https://www.dtv2009.gov/" id="ggok">https://www.dtv2009.gov/</a>.&nbsp;

There's a simple, four question form you fill in and you're done.&nbsp; Of
course there are phone numbers you can call and addresses or fax
numbers you can send a completed request form to, but you have to go
online to print out the form, so you may as well just do it right there
at the website.<br>
<br style="font-style: italic;"><em>What can I get with my coupon?</em><br>
There
are currently 50 different models listed as approved, coupon eligible
converter boxes.&nbsp; Major retailers such as Wal-Mart, Circuit City,
RadioShack and Best Buy carry them in the store, and two models are
available for purchase using the coupon online at <a title="http://dtv.bsat.net/" target="_blank" href="http://dtv.bsat.net/" id="hoi4">http://dtv.bsat.net/</a>.&nbsp;
The online available models cost $49.50 and $51.99, leaving you to make
up the approximately $10 difference.&nbsp; To be certified, a box must "not
contain features or functions except those necessary to enable a
consumer to
convert any channel broadcast in the digital television service into a
format that the consumer
can display on television receivers designed to receive and display
signals only in the analog
television service, but may also include a remote control device.”&nbsp;
This means it will be an ATSC tuner that can output NTSC signals and
nothing else.&nbsp; It will not be a QAM tuner.&nbsp; They typically output only
NTSC and composite video/stereo audio.&nbsp; They are allowed to have
s-video outputs, but not component video, DVI, HDMI, VGA or 1394.<br>

<br style="font-style: italic;"><em>I already have a tuner, will the transition affect me?</em><br>
Nope.&nbsp;
If you already have a tuner, whether it's an over the air ATSC tuner or
a Cable or Satellite tuner, you're all set.&nbsp; Nothing to do but relax
and watch TV.<br>
<br><em>After the transition, will all channels be HDTV channels?</em><br>
Sort
of.&nbsp; After the transition all channels will be digital, so they'll all
be capable of broadcasting an HDTV signal.&nbsp; However, there's no
guarantee that the shows they broadcast will be HDTV shows.&nbsp; They'll
come across as digital content, so they should look a little better
than the current NTSC content, but they might not be HD.&nbsp; If you
consider how many shows, like reality shows and such, are still not in
HD, it's a long shot to think all of those shows will be in high
definition by February of next year.
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>March  9, 2008 08:17 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1292
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
					AND entry_id <> 1292
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/03/hdtv_and_home_theater_podcast_257_-_us_digital_tv_transition.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
