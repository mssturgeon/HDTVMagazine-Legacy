<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1308";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1308 AND placement_is_primary = 1";
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
	<meta name="keywords" content="energy star, home theater, energy efficient, options energy, blu ray, energy, Energy, star, Star, theater, home, efficient, options, while, our, power, DVD, dvd, Pioneer, website, want, pioneer, player, program, blu" />
	<meta name="description" content="Many of us want to make sure that we are the most efficient we can be in how we use the resources we consume.  One concern that has come up in our email inbox lately is all the power that is consumed by adding so many new home theater gadgets.  We're using bigger TVs than ever before, more surround sound processors are in use, often people will add a game system like and XBox360 or PS3 and still have a DVD player or a Blu-ray player along with it.  Of course you have to have a DVR, and there's the whole HTPC angle;  it just keeps going.  With all this technology proliferation, how do we keep our electricity usage in check, if for no other reason than we want to keep our monthly bills in a reasonable range?" />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #260 - Options for an Energy Efficient Home Theater</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_260_-_options_for_an_energy_efficient_home_theater';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #260 - Options for an Energy Efficient Home Theater'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/03/hdtv_and_home_theater_podcast_260_-_options_for_an_energy_efficient_home_theater.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #260 - Options for an Energy Efficient Home Theater</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>March 21, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/03/hdtv_and_home_theater_podcast_260_-_options_for_an_energy_efficient_home_theater.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/03/hdtv_and_home_theater_podcast_260_-_options_for_an_energy_efficient_home_theater.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/03/hdtv_and_home_theater_podcast_260_-_options_for_an_energy_efficient_home_theater.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/03/hdtv_and_home_theater_podcast_260_-_options_for_an_energy_efficient_home_theater.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23260%20-%20Options%20for%20an%20Energy%20Efficient%20Home%20Theater&amp;bodytext=Many%20of%20us%20want%20to%20make%20sure%20that%20we%20are%20the%20most%20efficient%20we%20can%20be%20in%20how%20we%20use%20the%20resources%20we%20consume.%20%20One%20concern%20that%20has%20come%20up%20in%20our%20email%20inbox%20lately%20is%20all%20the%20power%20that%20is%20consumed%20by%20adding%20so%20many%20new%20home%20theater%20gadgets.%20%20We%27re%20using%20bigger%20TVs%20than%20ever%20before%2C%20more%20surround%20sound%20processors%20are%20in%20use%2C%20often%20people%20will%20add%20a%20game%20system%20like%20and%20XBox360%20or%20PS3%20and%20still%20have%20a%20DVD%20player%20or%20a%20Blu-ray%20player%20along%20with%20it.%20%20Of%20course%20you%20have%20to%20have%20a%20DVR%2C%20and%20there%27s%20the%20whole%20HTPC%20angle%3B%20%20it%20just%20keeps%20going.%20%20With%20all%20this%20technology%20proliferation%2C%20how%20do%20we%20keep%20our%20electricity%20usage%20in%20check%2C%20if%20for%20no%20other%20reason%20than%20we%20want%20to%20keep%20our%20monthly%20bills%20in%20a%20reasonable%20range%3F&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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

<p><br><br />
<a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-03-18.mp3">Listen Now - mp3</a><br />
<br><br />
<a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a><br />
<br><br />
<a href="http://www.htguys.com">Website</a><br />
<br><br />
<br><br />
<div><strong>Today's Show:</strong></div><br />
<br><br />
Many of us want to make sure that we are the most efficient we can be in how we use the resources we consume.  One concern that has come up in our email inbox lately is all the power that is consumed by adding so many new home theater gadgets.  We're using bigger TVs than ever before, more surround sound processors are in use, often people will add a game system like and XBox360 or PS3 and still have a DVD player or a Blu-ray player along with it.  Of course you have to have a DVR, and there's the whole HTPC angle;  it just keeps going.  With all this technology proliferation, how do we keep our electricity usage in check, if for no other reason than we want to keep our monthly bills in a reasonable range?<br />
 <br />
<strong>Options for an Energy Efficient Home Theater</strong></p>

<p>In the US, the government created the <a href="http://www.energystar.gov/">Energy Star</a> program.  It certifies electronics that meet a minimum level of energy efficiency.  From the Energy Star website:<br />
"<em>ENERGY STAR is a joint program of the U.S. Environmental Protection Agency and the U.S. Department of Energy helping us all save money and protect the environment through energy efficient products and practices.  Results are already adding up. Americans, with the help of ENERGY STAR, saved enough energy in 2007 alone to avoid greenhouse gas emissions equivalent to those from 27 million cars — all while saving $16 billion on their utility bills.</em>"  So what would it take to put together a complete, Energy Star approved, home theater?  Here's a look:</p>

<p><strong>Television options:</strong><br />
There are 442 big screen televisions listed at the Energy Star website, ranging in size from 37" to 70".  For example, you can get a Sharp 46" LCD, a Hitachi 50" plasma, a JVC 70" HD-ILA, and almost anything and everything in between.  Unfortunately with TVs, the Energy Star logo might be misleading.  According to an article by Patrick Barry entitled "<a href="http://www.bu.edu/phpbin/news-cms/news/?dept=1127&id=41208">This is Your TV on Juice</a>", the Energy Star program rates TVs based on how much electricity they consume while turned off.  Since the TV spends a majority of its time turned off, this seems like a valid measurement, but according to the article there's a Hitachi 55" plasma that is certified compliant, but it consumed more power than any other set in a 20 TV shootout conducted by CNET, a whopping 434 watts.  But turn it off and it disappears from the (power) grid.</p>

<p><strong>DVD options:</strong><br />
There's about 100 products listed in the DVD section, including several upconverting DVD players from Philips and a Pioneer Blu-ray player.  Shopping around you can find an upconverting player for as little as $59.99.  The Pioneer Blu-ray, model <a href="http://www.pioneerelectronics.com/PUSA/Products/HomeEntertainment/Blu-rayDisc+DVD/Blu-rayDiscPlayers/ci.BDP-95FD.Kuro">BDP-95FD</a>, is a great device.  The marketing quote is that it "doesn’t compromise a thing when it comes to high-definition picture and audio."  Although we haven't used it, we have enough experience with Pioneer to know that this is probably a true statement.  However, with a suggested retail price of $999 US, you may not be sacrificing any audio or video quality, but you might not be able to buy groceries for a few months.  Being environmentally conscious a good thing, but it will take quite a while to recoup that kind of investment in your monthly electricity bill.</p>

<p><strong>Receiver options:</strong><br />
Energy Star lists 23 receivers on the website, including a handful of options from Panasonic, Pioneer and RCA.  But doing a little more research, we found that the <a href="http://www.onkyousa.com/model.cfm?m=TX-SR705&class=Receiver&p=i">Onkyo TX-SR705</a> is actually quite energy efficient.  While not certified compliant, it is a great option for those who want the best of both worlds.  The step up from our recent fave the <a href="http://www.onkyousa.com/model.cfm?m=TX-SR605&class=Receiver&p=i">Onkyo TX-SR605</a>, the 705 is quite efficient and actually consumes less power than the 605.  It supports HDMI 1.3 with built-in decoding for Dolby TrueHD and DTS-HD Master Audio.  It has Faroudja processing and HDMI video upconversion, and you can find it online for $595 (<a href="http://www.htguys.com/shop.php?id=B000OBMX0K">Buy now</a>).  So while it isn't officially Energy Star, it's certainly worth looking at.</p>

<p><strong>Conclusion:</strong><br />
It's probably more than just a "noble" idea to want to conserve energy, and it goes deeper than just wanting to save money on your bills.  We applaud the Energy Star concept and hope that more manufacturers will embrace it in the future.  It sure makes it convenient for a consumer to simply look for that special logo and know that they're getting a certified product.  But as we found in our research, it can be a bit limiting in designing your home theater around that logo and you may need to do a little research on your own.  But with some digging and due diligence you can put together a home theater that not only rocks, but lets you do your part as well.<br />
</p>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>March 21, 2008 12:15 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1308
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
					AND entry_id <> 1308
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/03/hdtv_and_home_theater_podcast_260_-_options_for_an_energy_efficient_home_theater.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
