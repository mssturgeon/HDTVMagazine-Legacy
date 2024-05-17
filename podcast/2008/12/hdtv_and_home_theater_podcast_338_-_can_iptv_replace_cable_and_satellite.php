<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1582";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1582 AND placement_is_primary = 1";
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
	<meta name="keywords" content="cable satellite, mbps mbps, iptv replace, news weather, replace cable, cable, watch, news, live, online, weather, mbps, Mbps, sports, Cable, iptv, may, IPTV, ABC, stream, abc, available, move, satellite, national" />
	<meta name="description" content="We've talked about wanting to give our television providers the boot for the past two years now. In the end, at least for us, there is no way to eliminate them completely. This week while reading an article in Broadcasting and Cable entitled Web Sports Enters HD Arena we thought it would be a good idea to revisit the subject. For this study we looked at our viewing habits and tried to come up with online equivalents.
" />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #338 - Can IPTV replace Cable and Satellite?</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_338_-_can_iptv_replace_cable_and_satellite';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #338 - Can IPTV replace Cable and Satellite?'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/12/hdtv_and_home_theater_podcast_338_-_can_iptv_replace_cable_and_satellite.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #338 - Can IPTV replace Cable and Satellite?</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>December 18, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/12/hdtv_and_home_theater_podcast_338_-_can_iptv_replace_cable_and_satellite.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/12/hdtv_and_home_theater_podcast_338_-_can_iptv_replace_cable_and_satellite.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/12/hdtv_and_home_theater_podcast_338_-_can_iptv_replace_cable_and_satellite.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/12/hdtv_and_home_theater_podcast_338_-_can_iptv_replace_cable_and_satellite.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23338%20-%20Can%20IPTV%20replace%20Cable%20and%20Satellite%3F&amp;bodytext=We%27ve%20talked%20about%20wanting%20to%20give%20our%20television%20providers%20the%20boot%20for%20the%20past%20two%20years%20now.%20In%20the%20end%2C%20at%20least%20for%20us%2C%20there%20is%20no%20way%20to%20eliminate%20them%20completely.%20This%20week%20while%20reading%20an%20article%20in%20Broadcasting%20and%20Cable%20entitled%20Web%20Sports%20Enters%20HD%20Arena%20we%20thought%20it%20would%20be%20a%20good%20idea%20to%20revisit%20the%20subject.%20For%20this%20study%20we%20looked%20at%20our%20viewing%20habits%20and%20tried%20to%20come%20up%20with%20online%20equivalents.%0A&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
<div><a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-12-19.mp3">Listen Now - mp3</a></div>
<div><a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a></div>
<div><a href="http://www.htguys.com">Website</a></div><br>

<br><strong>Today's Show:</strong><br>
We've talked about wanting to give our television providers the boot for the past two years now. In the end, at least for 
us, there is no way to eliminate them completely. This week while reading an article in 
<a target="_blank" href="http://www.broadcastingcable.com/">Broadcasting and Cable</a> entitled 
<a target="_blank" href="http://www.broadcastingcable.com/article/CA6616888.html">Web Sports Enters HD Arena</a> 
we thought it would be a good idea to revisit the subject. For this study we looked at our viewing 
habits and tried to come up with online equivalents.
<br><br>
<strong>IPTV - Can it replace Cable and Satellite?</strong>
<br><br>
<strong>News Weather and Sports</strong><br>
One of the issues with IPTV is live content like news and sports. For this experiment we were able to solve this with our 
local ABC affiliate. In 
<a target="_blank" href="http://www.abc7.com/">Los Angeles ABC 7</a> 
puts up a live stream of their news coverage. They also include live national news 
from ABC along with local news from Chicago, New York, San Francisco and other cities. All the ABC affiliates have local 
weather that is updated frequently so even if the news is not streaming you can get weather updates anytime. If you want 
more weather you can check 
<a target="_blank" href="http://www.weather.com/multimedia/videoplayer.html">The Weather Channel</a> 
for national and regional updates. Finally both Fox and CNN have news online. CNN has a 
<a target="_blank" href="http://www.cnn.com/video/?iref=videoglobal#">live feed</a> that is not always on.
<br><br>
News and weather are covered and you can throw in traffic too. How about live sports? Baseball has 
<a target="_blank" href="http://www.mlb.com">MLB.com</a>, the NBA has NBA 
League Pass Broadband and we saw what NBC did with the Olympics last summer. ESPN has begun to move to the web as well. The 
only thing missing is NFL. You can stream NFL if you have Sunday Ticket from DirecTV but that kind of defeats the purpose of 
this whole thing. No football is a deal killer for us but for others its not an issue. Major sporting events are available 
live on the net so again, you don't have to worry about missing most of your favorite sports.
<br><br>
<strong>Network Programming</strong><br>
This has been available online for quite some time. Every national network (we're counting 
<a target="_blank" href="http://www.hulu.com">Hulu</a> as both Fox and NBC) has an 
online presence. Many have "HD" versions available with limited commercials. If you have a Netflix account with Watch it Now, 
you can watch the same programming in many cases without commercials. The online HD versions are not really HD but they are 
decent and look OK on a 65 inch TV. A quick check shows that 80% of the shows we watch on TV are available online.
<br><br>
You can also go the way of iTunes. They now have better HD than streaming and Dolby Digital 5.1 audio. However it is not free, 
a season pass in HD to 30 Rock goes for $53. If you watch a lot of TV you may end up spending more than just buying HD service 
from the cable company. But if all you watch are a few TV series iTunes or Amazon may be something you want to consider.
<br><br>
<strong>What about picture quality</strong><br> 
We come a long way here, a lot has been done in this area. A company called 
<a target="_blank" href="http://www.movenetworks.com/">Move Networks</a> 
is working to bring you live HD 
streamed via the Internet. They currently handle ABCs online HD content. Right now Move Networks SD stream starts at 768 Kbps 
and goes up to 2.5Mbps for HD. They are testing 4Mbps HD for live 720p HD streams.  According to Move they have not gone above 
4Mbps because the majority of the computers out there do not have the horsepower required to process the video. The other 
limitation is fast internet connections and bandwidth caps.  Just for the record, at the time of this writing bandwidth at 
the HT Guys "Labs" range from 8 Mbps to 18 Mbps down and 1.7 Mbps up. 
<br><br>
<strong>Conclusion</strong><br>
If you had to, you could cut the cord between you and your cable company and not miss a beat. But in reality if you won't 
have the same video quality. The other thing to consider is that the Cable or Satellite box has an simple well understood 
interface. Connecting a computer to the TV requires using a keyboard and a mouse and there are no channel numbers. You'll 
have to bookmark your sites. Companies like Apple, Netflix and Boxee are all trying to provide a ten foot interface that 
is Grandma proof but we still have a ways to go. Finally, cable companies are not going to be happy with subscribers that 
watch a lot of Internet streamed content. So even if the quality improved you may not be able to stream a months worth of 
TV into your home. If you are a light TV watcher that is computer savy then you may be able to cut the cable cord completely. 
But in actually, for most of us, IPTV is a good way to augment what we already have. If you are on the road you can watch 
something on your terms. If you forget to record something then its a great backup. Its also a great way to watch old TV 
shows that are no longer aired.
<br><br><br>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>December 18, 2008 11:05 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1582
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
					AND entry_id <> 1582
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/12/hdtv_and_home_theater_podcast_338_-_can_iptv_replace_cable_and_satellite.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
