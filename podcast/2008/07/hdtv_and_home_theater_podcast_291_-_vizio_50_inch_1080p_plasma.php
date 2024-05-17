<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1459";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1459 AND placement_is_primary = 1";
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
	<meta name="keywords" content="vizio inch, inch lcd, rear projection, better picture, lbs stand, vizio, Vizio, Samsung, costco, samsung, Costco, inch, ”, HDTV, hdtv, stand, picture, Sharp, SONY, models, TVs, tvs, sony, sharp, plasma" />
	<meta name="description" content="We have been trying to get a hold of a Vizio TV for review for some time now but with no luck. This weekend while the family was in Costco stocking up on supplies Ara spent some time looking at the Vizio 50 inch 1080p Plasma. In actuality this feature is more of a Costco roundup than an in depth review of the TV." />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #291 - Vizio 50 inch 1080p Plasma</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_291_-_vizio_50_inch_1080p_plasma';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #291 - Vizio 50 inch 1080p Plasma'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/07/hdtv_and_home_theater_podcast_291_-_vizio_50_inch_1080p_plasma.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #291 - Vizio 50 inch 1080p Plasma</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>July  7, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/07/hdtv_and_home_theater_podcast_291_-_vizio_50_inch_1080p_plasma.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/07/hdtv_and_home_theater_podcast_291_-_vizio_50_inch_1080p_plasma.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/07/hdtv_and_home_theater_podcast_291_-_vizio_50_inch_1080p_plasma.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/07/hdtv_and_home_theater_podcast_291_-_vizio_50_inch_1080p_plasma.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23291%20-%20Vizio%2050%20inch%201080p%20Plasma&amp;bodytext=We%20have%20been%20trying%20to%20get%20a%20hold%20of%20a%20Vizio%20TV%20for%20review%20for%20some%20time%20now%20but%20with%20no%20luck.%20This%20weekend%20while%20the%20family%20was%20in%20Costco%20stocking%20up%20on%20supplies%20Ara%20spent%20some%20time%20looking%20at%20the%20Vizio%2050%20inch%201080p%20Plasma.%20In%20actuality%20this%20feature%20is%20more%20of%20a%20Costco%20roundup%20than%20an%20in%20depth%20review%20of%20the%20TV.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
<div><a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-07-08.mp3">Listen Now - mp3</a></div>
<div><a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a></div>
<div><a href="http://www.htguys.com">Website</a></div><br>

<strong>Today's Show:</strong><br>
We have been trying to get a hold of a Vizio TV for review for some time now but with no luck. This weekend while the family was in Costco stocking up on supplies Ara spent some time looking at the Vizio 50 inch 1080p Plasma. In actuality this feature is more of a Costco roundup than an in depth review of the TV.<br><br>
<strong>Vizio VP504FHDTV10A at Costco (<a href="http://www.costco.com/Browse/Product.aspx?Prodid=11292517&amp;search=Select%20Televisions&amp;Sp=S&amp;Mo=10&amp;cm_re=1_en-_-Top_Left_Nav-_-Top_search&amp;lang=en-US&amp;Nr=P_CatalogName:BC&amp;Ns=P_Price%7C1%7C%7CP_SignDesc1&amp;N=5000043&amp;whse=BC&amp;Dx=mode+matchallpartial&amp;Ntk=Text_Search&amp;Dr=P_CatalogName:BC&amp;Ne=4000000&amp;D=Select%20Televisions&amp;Ntt=Select%20Televisions&amp;No=3&amp;Ntx=mode+matchallpartial&amp;Nty=1&amp;topnav=&amp;s=1" title="" target="_blank">$1499.99</a>)</strong><br><br>
First some facts and figures. Our particular Costco had 28 TVs on display. Twenty seven of them were flat screens. The one rear projection TV for sale was the Samsung HL67A510C 67 inch DLP. This TV goes for $2000 including a matching Stand. That is an outstanding deal if you have the space! More rear projection models are available online.<br>
<br>Vizio was by far the most represented brand with nine models on display. Panasonic and Sharp each had four models, Samsung had three, SONY, Philips, and HP had two models and there was also one Toshiba LCD for sale.<br>
<br>All the TVs were on their default settings and displaying the same in-store feed. There was no way to adjust the settings or change the inputs. Essentially we were evaluating the picture exactly as every other Costco customer was. By the way, Costco extends the warranty on this TV to two years and allows for a 90 day return policy.<br>
<br><strong>Features:</strong><br>
<ul>
<li>Resolution: 1920 x 1080 (Native)</li>
<li>HDMI with HDCP: x4</li>
<li>Contrast Ratio: 30,000:1</li>
<li>Brightness: 1,000 cd/m2 (typical)</li>
<li>Viewable Angle: 178 degrees (horizontal and vertical)</li>
<li>HDTV Compatibility: 1080p</li>
<li>Signal Compatibility: 480i (SDTV), 480P (EDTV), 720P (HDTV), 1080i (HDTV), 1080p (HDTV)</li>
<li>Tuner: NTSC/ATSC/QAM</li>
<li>5.1 SPDIF Digital Optical Audio: x1</li>
<li>3D Comb Filter</li>
<li>PIP / POP / CC / Zoom / Freeze</li>
<li>V-Chip</li>
<li>Progressive Scan Video</li>
<li>Weight: 95.9 lbs (with stand), 88.7 lbs (without stand)</li>
<li>Dimensions: 48.6” W x 32.5” H x 10.3” D (with Stand), 48.6” W x 30.5” H x 3.8” D (without Stand)</li></ul><br>
The TV had decent colors and detail. When compared side by side with the Samsung and Sharp LCDs that were displayed right next to it the colors were not as vivid. The Sharp and Samsung LCDs had more detail and seemed clearer. Both the Sharp and Samsung LCDs were 720P which further proves that resolution is not the most important specification in producing a good picture. Two TVs down was another Vizio 50 inch plasma (VP50HDTV20A) which was going for $1100 that produced a decent picture for $400 less. <br>
<br>Ara stood back and watched the shoppers come in and go right to the Vizio. They didn't even give the TVs a critical look. With coupon in hand they went right for the lowest priced 1080p plasma in the store.<br>
<br>For fun, Ara asked his wife and kids to pick the best looking TV out of the 28. They weren't allowed to talk to each other and their responses were given to him in secret. His wife and oldest daughter picked the SONY KDL40WL135 40 inch LCD that goes for $1800. His younger daughter picked the Samsung LN46A540 46 inch LCD which goes for $1700. His pick was also the SONY. <br>
<br>We are sure that the Vizio will calibrate to produce a better picture and for the price you will not be dissatisfied. After all, you won't have the SONY and Samsung sitting next to your TV in your home. Every TV in Costco will produce a better picture than any Standard Definition analog TV you are replacing it with. Please be sure to calibrate it, though.<br>
<br>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>July  7, 2008 11:29 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1459
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
					AND entry_id <> 1459
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/07/hdtv_and_home_theater_podcast_291_-_vizio_50_inch_1080p_plasma.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
