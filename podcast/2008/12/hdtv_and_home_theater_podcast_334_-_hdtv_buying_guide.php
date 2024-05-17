<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1570";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1570 AND placement_is_primary = 1";
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
	<meta name="keywords" content="guys store, store price, contrast ratio, hdmi inputs, dynamic contrast, HDTV, hdtv, inch, Store, guys, price, Guys, Price, store, hdmi, color, contrast, HDMI, LCD, lcd, ratio, Inch, Color, plasma, viera" />
	<meta name="description" content=" Listen Now - mp3 RSS Website Today's Show: Its time for our annual HDTV buying guide. This year we are listing them by size instead of technology. Last year's most expensive model was the Pioneer Elite Pro-150FD 60 inch..." />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #334 - HDTV Buying Guide</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_334_-_hdtv_buying_guide';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #334 - HDTV Buying Guide'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/12/hdtv_and_home_theater_podcast_334_-_hdtv_buying_guide.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #334 - HDTV Buying Guide</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>December  4, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/12/hdtv_and_home_theater_podcast_334_-_hdtv_buying_guide.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/12/hdtv_and_home_theater_podcast_334_-_hdtv_buying_guide.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/12/hdtv_and_home_theater_podcast_334_-_hdtv_buying_guide.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/12/hdtv_and_home_theater_podcast_334_-_hdtv_buying_guide.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23334%20-%20HDTV%20Buying%20Guide&amp;bodytext=%20Listen%20Now%20-%20mp3%20RSS%20Website%20Today%27s%20Show%3A%20Its%20time%20for%20our%20annual%20HDTV%20buying%20guide.%20This%20year%20we%20are%20listing%20them%20by%20size%20instead%20of%20technology.%20Last%20year%27s%20most%20expensive%20model%20was%20the%20Pioneer%20Elite%20Pro-150FD%2060%20inch...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
<div><a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-12-05.mp3">Listen Now - mp3</a></div>
<div><a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a></div>
<div><a href="http://www.htguys.com">Website</a></div><br>

<br><strong>Today's Show:</strong><br>
Its time for our annual HDTV buying guide. This year we are listing them by size instead of technology. Last year's most expensive 
model was the Pioneer Elite Pro-150FD 60 inch plasma TV which came in at $7500. This year the same TV can be found online for less 
than $5000. Our most inexpensive TV was the Olevia 237T 37 inch LCD which came in at $765. This year the prices for tier one 
manufacturers are quite affordable. Without further ado, here is the list for 2008:
<br><br>
<strong>HDTV Buying Guide</strong>
<br><br>
<strong>Less than 42"</strong>
<br><br>
<a target="_blank" href="http://www.samsung.com/us/consumer/detail/detail.do?group=televisions&type=televisions&subtype=lcdtv&model_cd=LN19A450C1DXZA">Samsung LN19A450 19-Inch 720p LCD HDTV</a> 
(<a target="_blank" href="http://www.htguys.com/shop.php?id=B001418WG8">HT Guys Store Price $317</a>) 
Perfect for a kitchen or dorm room, this LCD has 720p HD Resolution, Wide Color Enhancer, 1 HDMI Input, 8 ms response time and and 
built-in ATSC/NTSC and QAM tuners
<br><br>
<a target="_blank" href="http://www.samsung.com/us/consumer/detail/detail.do?group=televisions&type=televisions&subtype=lcdtv&model_cd=LN32A450C1DXZA">Samsung LN32A450 32-Inch 720p LCD HDTV</a> 
(<a target="_blank" href="http://htguys.com/shop.php?id=B00141AYIC">HT Guys Store Price $599.98</a>) 
Ara has the 37 inch version of this TV and is amazed at the picture each time he looks at it. Apparently he is not alone. The TV 
receives 5 out of 5 stars from 148 reviewers at Amazon.com. It has a 10,000:1 dynamic contrast ratio, 6ms response time and SRS 
TruSurround XT. The TV has three HDMI inputs as well.
<br><br>
<a target="_blank" href="http://www.sonystyle.com/webapp/wcs/stores/servlet/ProductDisplay?catalogId=10551&storeId=10151&langId=-1&productId=8198552921665381955">Sony Bravia S-Series KDL-40S4100 40-Inch 1080p LCD HDTV</a> 
(<a target="_blank" href="http://htguys.com/shop.php?id=B0017Q2W6G">HT Guys Store Price $915</a>) 
Four and a half star 40 inch 1080p TV for less than $1000! Three HDMI inputs, 24 fps capable, 2500:1 Contrast ratio.
<br><br>
<strong>42" to 50"</strong>
<br><br>
<a target="_blank" href="http://www2.panasonic.com/consumer-electronics/shop/Televisions/VIERA-Plasma-HDTVs/model.TH-42PZ85U_11002_7000000000000005702">Panasonic Viera TH-42PZ85U 42-Inch 1080p Plasma HDTV</a> 
(<a target="_blank" href="http://www.htguys.com/shop.php?id=B00142JKRW">HT Guys Store Price $1059</a>) 
No list would be complete without a Panasonic 42" Plasma.  And for just over $1000 it's hard to go wrong.  This one is full 1080p, 
native contrast ratio of 30,000:1, Dynamic contrast ratio of 1,000,000:1, Built-in SD card slot with Photo Viewer/Gallery Player 
software, x.v. Color and Deep Color (HDMI 1.3 features), Viera Link is now compatible with Onkyo and Yamaha home theater systems, 
3 HDMI inputs and Anti-Image Retention Mode for gaming.
<br><br>
<a target="_blank" href="http://www.sonystyle.com/webapp/wcs/stores/servlet/ProductDisplay?catalogId=10551&storeId=10151&langId=-1&productId=8198552921665411950">Sony Bravia Z-Series KDL-46Z4100/B 46-Inch 1080p 120Hz LCD HDTV</a> 
(<a target="_blank" href="http://www.htguys.com/shop.php?id=B0019HZINM">HT Guys Store Price $1664</a>) 
Full HD 1080p, Motionflow 120Hz, BRAVIA Engine 2 digital video processing, Advanced Contrast Enhancer circuit (ACE), 24p True 
Cinema capable, enhanced XMB user interface, BRAVIA Sync capable ... customers give it four and a half stars ... where do I sign?
<br><br>
<strong>Greater than 50"</strong>
<br><br>
<a target="_blank" href="http://www.samsung.com/us/consumer/detail/detail.do?group=televisions&type=televisions&subtype=lcdtv&model_cd=LN52A650A1FXZA">Samsung LN52A650 52-Inch 1080p 120Hz LCD HDTV with Red Touch of Color</a> 
(<a target="_blank" href="http://www.htguys.com/shop.php?id=B001413DF8">HT Guys Store Price $2059</a>) 
Four and a half star 1080p 120Hz LCD.  4 HDMI 1.3 ports, Side-mounted USB 2.0 port, InfoLink: Get free RSS feeds of news, weather 
and sports right to your HDTV with a built-in Ethernet port, 50,000:1 dynamic contrast ratio, Blazing 4ms response time, touch of 
color will please the aesthetics committee
<br><br>
<a target="_blank" href="http://www2.panasonic.com/consumer-electronics/shop/Televisions/VIERA-Plasma-HDTVs/model.TH-58PZ800U_11002_7000000000000005702">Panasonic Viera TH-58PZ800U 58-Inch 1080p Plasma HDTV</a> 
(<a target="_blank" href="http://www.htguys.com/shop.php?id=B00142JKSG">HT Guys Store Price $2649</a>) 
Four and a half star 1080p Plasma TV. THX, 4 HDMI Inputs, 1,000,000:1 Dynamic Contrast ratio, Deep Color Support, 24 FPS. THX 
Certified.  A few inches bigger and a step up in features from the 42" model previously in the list.  An all around fantastic TV!
<br><br>
<a target="_blank" href="http://www.mitsubishi-tv.com/product/WD65735">Mitsubishi WD-65735 65-Inch 1080p DLP HDTV</a> 
(<a target="_blank" href="http://www.htguys.com/shop.php?id=B00166BNGW">HT Guys Store Price $1440</a>) 
Four Star 1080p DLP. 3D Ready, Six color processor, Energy Efficient, 3 HDMI inputs, Support for Deep Color. Best bang for the 
buck TV on this years list. If you have the space you should really consider this TV!
<br><br>
<strong>Front Projector</strong>
<br><br>
<a target="_blank" href="http://www2.panasonic.com/consumer-electronics/shop/Televisions/Home-Theater-Projectors/model.PT-AE3000U_11002_7000000000000005702">Panasonic PT-AE3000U HD Home Cinema 1080p LCD Multimedia Projector</a> 
(<a target="_blank" href="http://www.htguys.com/shop.php?id=B001JTQBT0">HT Guys Store Price $2500</a>) 
Five star (only two ratings) 1080p, Smooth Screen technology, 1,600 ANSI lumens Dynamic contrast ratio - 60,000:1. Very large 
51/8"'' x 181/8"'' x 1125/32"'' at 16lbs
<br><br>
<a target="_blank" href="http://www.sonystyle.com/webapp/wcs/stores/servlet/ProductDisplay?catalogId=10551&storeId=10151&langId=-1&productId=8198552921665533556">Sony VPL-HW10 BRAVIA SXRD 1080p home theater projector</a> 
(<a target="_blank" href="http://www.htguys.com/shop.php?id=B001FB53PA">HT Guys Store Price $3399</a>) 30,000:1 dynamic contrast ratio, 1000 ANSI 
lumens, all-digital video signal processing via the BRAVIA Engine 2, 3-SXRD for full 1080p resolution, 2 HDMI inputs with x.v.Color, 
ultra-quiet fan, improved lamp life
<br><br>
<strong>HT Guy's Ultimate Christmas Present:</strong>
<br><br>
<a target="_blank" href="http://www2.panasonic.com/consumer-electronics/shop/Televisions/VIERA-Plasma-HDTVs/model.TH-65PZ850U.O_11002_7000000000000005702#tabsection">Panasonic TH 65PZ850U - 65" VIERA 1080p plasma HDTV</a> 
(<a target="_blank" href="http://www.htguys.com/shop.php?id=B001EBEPX2">HT Guys Store Price $5388</a>) 
65" 1080p Plasma, need I say more?  65" Widescreen VIERA Plasma 1080p HDTV with Increased Native Contrast Ratio (Native: 30,000:1, 
Dynamic: 1,000,000:1), VIERA Cast, Digital Cinema Color Technology, PC Input, New Anti-Reflective Filter, Game Mode, Built-In SD 
Card Slot, 4 HDMI Inputs and VIERA Link® HDAVI Control
<br><br>
<br><br><br>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>December  4, 2008 11:44 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1570
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
					AND entry_id <> 1570
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/12/hdtv_and_home_theater_podcast_334_-_hdtv_buying_guide.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
