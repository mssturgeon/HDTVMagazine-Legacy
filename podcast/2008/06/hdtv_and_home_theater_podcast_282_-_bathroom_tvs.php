<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1422";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1422 AND placement_is_primary = 1";
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
	<meta name="keywords" content="ready morning, bathroom well, thing approach, approach computer, bathroom tvs, bathroom, Bathroom, need, equipment, TVs, tvs, run, solution, mirrors, get, while, thing, our, laptop, mirror, may, Homerun, homerun, want, ready" />
	<meta name="description" content="We have designed whole house video and audio systems and talked about them on our show. The one room we really haven't done much with is the Bathroom. We know what you are probably thinking: the bathroom is one area in the home where you don't want to be bombarded with the world's problems.  We hear you; but what if you are in the middle of a football game and your wife tells you that its time to get ready so you can go to your in-law's?  Having a TV in the bathroom sounds like a good way keep everyone happy." />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #282 - Bathroom TVs</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_282_-_bathroom_tvs';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #282 - Bathroom TVs'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/06/hdtv_and_home_theater_podcast_282_-_bathroom_tvs.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #282 - Bathroom TVs</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>June  6, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/06/hdtv_and_home_theater_podcast_282_-_bathroom_tvs.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/06/hdtv_and_home_theater_podcast_282_-_bathroom_tvs.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/06/hdtv_and_home_theater_podcast_282_-_bathroom_tvs.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/06/hdtv_and_home_theater_podcast_282_-_bathroom_tvs.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23282%20-%20Bathroom%20TVs&amp;bodytext=We%20have%20designed%20whole%20house%20video%20and%20audio%20systems%20and%20talked%20about%20them%20on%20our%20show.%20The%20one%20room%20we%20really%20haven%27t%20done%20much%20with%20is%20the%20Bathroom.%20We%20know%20what%20you%20are%20probably%20thinking%3A%20the%20bathroom%20is%20one%20area%20in%20the%20home%20where%20you%20don%27t%20want%20to%20be%20bombarded%20with%20the%20world%27s%20problems.%20%20We%20hear%20you%3B%20but%20what%20if%20you%20are%20in%20the%20middle%20of%20a%20football%20game%20and%20your%20wife%20tells%20you%20that%20its%20time%20to%20get%20ready%20so%20you%20can%20go%20to%20your%20in-law%27s%3F%20%20Having%20a%20TV%20in%20the%20bathroom%20sounds%20like%20a%20good%20way%20keep%20everyone%20happy.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
<a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-06-06.mp3">Listen Now - mp3</a>
<a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a>
<a href="http://www.htguys.com">Website</a>

<p><strong>Today's Show:</strong><br />
We have designed whole house video and audio systems and talked about them on our show. The one room we really haven't done much with is the Bathroom. We know what you are probably thinking: the bathroom is one area in the home where you don't want to be bombarded with the world's problems.  We hear you; but what if you are in the middle of a football game and your wife tells you that its time to get ready so you can go to your in-law's?  Having a TV in the bathroom sounds like a good way keep everyone happy.</p>

<p><strong>Bathroom TVs</strong><br />
TVs in the Bathroom are expensive propositions. We will discuss a couple of those and then give you the HT Guys low cost solution.</p>

<p><a href="http://www.hiddentelevision.com/" target="_blank" title="HiddenTelevision.com">HiddenTelevision.com</a> specializes in selling mirrors for making your own hidden television. They mount a transparent mirror over the TV to convert it into a hidden TV. Turn your TV on and your display will become visible through the mirror.</p>

<p>They have two different styles: dielectric and bathroom. The dielectric mirrors are optimized for maximum brightness and reduced glare. Their bathroom mirrors are highly reflective, so you can see yourself clearly while while you get ready in the morning. The mirrors are affordable running about $600 for a typical bathroom. You do need to provide your own TV and source equipment.</p>

<p><a href="http://www.waterprooftv.com/bathroom-tv/" target="_blank" title="WaterProfTV.com">WaterProofTV.com</a> is a UK company that specializes in waterproof TVs. These sets are specifically designed to function in showers, tubs, or next to the sink.  These TVs are expensive with the the lowest cost version costing about $2000.</p>

<p>There are other manufacturers out there as well. The one thing they have in common is that they are all expensive and require serious installation expenses. You need to mount the TVs, run power, and provide source material. This will run in the thousands of dollars.</p>

<p>We have a solution that you can install for less than $1000. Now it will not be as slick as the above mentioned solutions and you will need some counter space, but it will get you through.</p>

<p><strong>Hardware</strong><br />
The first solution is very simple and very mobile but it goes over our $1000 limit. You may want this solution for simplicity. We include it because you may have an old laptop around that you can put into service as a Bathroom TV. Buy yourself a small laptop. Here a <a href="/shop.php?id=B0013FPYRK" target="_blank" title="Macbook ($1300)">Macbook ($1300)</a> or <a href="/shop.php?id=B000VCHIJI" target="_blank" title="Sony Vaio ($1100)">Sony Vaio ($1100)</a> laptop are our computers of choice but any laptop will do. The nice thing about this approach is that the computer can be taken outside of the bathroom as well. The bad thing about this approach is that the computer can be taken out of the bathroom as well. You'll still need the rest of the equipment and software we will talk about later in this feature so the total cost will be a couple of hundred dollars more.</p>

<p>The second solution is a bit less mobile. Take any 15-inch wide screen TV and put it on your counter top, mount it to the wall or find any other suitable are to put it. In Ara's Bathroom there is an ideal spot between both sinks. We recently remodeled the bathroom and removed the full length mirror opting for framed mirrors instead. This leaves a great spot to wall mount a 15 inch LCD. For our bathroom we are choosing a 16-inch Viewsonic widescreen LCD (<a href="/shop.php?id=B00170V91G" target="_blank" title="N1630W $250">N1630W $250</a>). No need for 1080p due to screen size so this 720p will have plenty of resolution.</p>

<p>We will also use a <a href="/shop.php?id=B0006HU49Y" target="_blank" title="Mac Mini ($600)">Mac Mini ($600)</a>. You'll want to upgrade the RAM to 2GB which will run you an additional $50. Yes you can use a PC but the small form factor of the mini makes it ideal for use in confined spaces. Even with the small form factor you may have to drill some holes in your countertop to run the power cables for the Mini.</p>

<p>The last piece of equipment is the <a href="/shop.php?id=B000IVDIL4" target="_blank" title="SlingBox Pro">SlingBox Pro</a> ($240 including the <a href="/shop.php?id=B000J56H5I" target="_blank" title="HD Connect Cable">HD Connect Cable</a>). External speakers are optional. For this  exercise we are using the internal TV speakers.</p>

<p><strong>Software</strong><br />
The nice thing about the software is that it is all included with the hardware. The main thing we would need is the Sling Media Sling Player. This product actually gives a nice picture when streamed on an internal network. Its not quite HD but it would be great for watching news or a television program while getting ready in the morning. Sports is a bit dicey but its better than missing the game.</p>

<p>The reason we didn't go with El Gato's HD Homerun is that the Mini only supports 802.11g and we have found that we need 802.11n to watch HD programming. If you have a hard wired bathroom you could add the HD Homerun in addition to the Slingbox. The HD Homerun will get you the highest quality picture but will only work for OTA content. The other advantage of the HD Homerun is that you can use your Bathroom TV as a DVR for broadcast TV.</p>

<p>With this setup you could even watch movies that you have on your movie server. You could also listen to music, watch podcasts or even browse the Internet.</p>

<p><strong>Caution</strong><br />
None of this equipment is designed to run in the humidity of a bathroom so doing so may limit the life of the equipment. Also, make sure that the equipment is in a place that is protected from water splashing on it.</p>

<p><strong>Conclusion</strong><br />
No longer will you have to run from the bathroom to the bedroom to check traffic or weather while you are getting ready in the morning. If you want to keep tabs on the market, no problem, on DirecTV, CNBC is on channel 355.</p>

<p>While not as sexy as the in mirror or in shower devices, our solution will get you a decent size screen at an affordable price.<br />
</p>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>June  6, 2008 10:15 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1422
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
					AND entry_id <> 1422
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/06/hdtv_and_home_theater_podcast_282_-_bathroom_tvs.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
