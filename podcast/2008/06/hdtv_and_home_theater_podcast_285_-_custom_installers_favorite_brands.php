<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1432";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1432 AND placement_is_primary = 1";
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
	<meta name="keywords" content="favorite brands, home theater, custom installers, half picture, triad paradigm, Sony, sony, custom, brands, pioneer, Pioneer, TVs, tvs, dealers, marantz, Runco, speakers, favorite, klipsch, Samsung, samsung, Triad, Speakers, theater, installers" />
	<meta name="description" content="Every year CE Pro puts together a list of the top 100 custom electronics dealers in the country.  Then they poll those top dealers for their favorite brands to sell in 37 different categories.  Of course half the picture for dealers is how much profit they can make selling a particular product, but the other half of the picture is reliability and returns.  We like to look at the list to see what all the custom home theater installers are endorsing.  If you've narrowed a purchase down to two brands, it may help to swing your vote." />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #285 - Custom Installers' Favorite Brands</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_285_-_custom_installers_favorite_brands';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #285 - Custom Installers\' Favorite Brands'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/06/hdtv_and_home_theater_podcast_285_-_custom_installers_favorite_brands.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #285 - Custom Installers' Favorite Brands</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>June 16, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/06/hdtv_and_home_theater_podcast_285_-_custom_installers_favorite_brands.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/06/hdtv_and_home_theater_podcast_285_-_custom_installers_favorite_brands.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/06/hdtv_and_home_theater_podcast_285_-_custom_installers_favorite_brands.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/06/hdtv_and_home_theater_podcast_285_-_custom_installers_favorite_brands.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23285%20-%20Custom%20Installers%27%20Favorite%20Brands&amp;bodytext=Every%20year%20CE%20Pro%20puts%20together%20a%20list%20of%20the%20top%20100%20custom%20electronics%20dealers%20in%20the%20country.%20%20Then%20they%20poll%20those%20top%20dealers%20for%20their%20favorite%20brands%20to%20sell%20in%2037%20different%20categories.%20%20Of%20course%20half%20the%20picture%20for%20dealers%20is%20how%20much%20profit%20they%20can%20make%20selling%20a%20particular%20product%2C%20but%20the%20other%20half%20of%20the%20picture%20is%20reliability%20and%20returns.%20%20We%20like%20to%20look%20at%20the%20list%20to%20see%20what%20all%20the%20custom%20home%20theater%20installers%20are%20endorsing.%20%20If%20you%27ve%20narrowed%20a%20purchase%20down%20to%20two%20brands%2C%20it%20may%20help%20to%20swing%20your%20vote.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
<div><a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-06-17.mp3">Listen Now - mp3</a></div>
<div><a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a></div>
<div><a href="http://www.htguys.com">Website</a></div><br>

<strong>Today's Show:</strong><br>
<div>Every year <a href="http://www.cepro.com" target="_blank">CE Pro</a> puts together a list of the top 100 custom electronics
dealers in the country.&nbsp; Then they poll those top dealers for their favorite brands to sell in 37 different categories.&nbsp; Of course half the picture for dealers is how much profit they can make selling a particular product, but the other half of the picture is reliability and returns.  We like to look at the list to see what all the custom home theater installers are endorsing.  If you've narrowed a purchase down to two brands, it may help to swing your vote.</div>
<br>
<strong>Custom Installers' Favorite Brands</strong><br>
We won't cover all 37 categories.&nbsp; To get all the info, you need to read the <a target="_blank" href="http://www.cepro.com/article/integrators_detail_favorite_brands">article</a>.<br>

<br><strong>A/V Receivers</strong><br>
<ul>
<li>Integra (46%)</li>
<li>Denon (36%) &nbsp;&nbsp; </li>
<li>Marantz (31%)</li>
<li>Pioneer (19%)</li>
<li>Sony (17%)</li></ul><br>
<strong>Bookshelf Speakers</strong><br>
<ul>
<li>B&amp;W (25%)</li>
<li>Klipsch (22%)</li>
<li>Triad (17%)</li>
<li>Paradigm (14%)</li>
<li>Canton (11%)</li></ul><br>
<strong>DVD Players</strong><br>
<ul>
<li>Sony  (43%)</li>
<li>Integra (34%)</li>
<li>Denon (29%)</li>
<li>Pioneer (25%)</li>
<li>Marantz (23%)</li></ul><br>
<strong>DVRs</strong><br>
<ul>
<li>DirecTV (23%)</li>
<li>Dedicated Micros (16%)</li>
<li>TiVo (13%)</li>
<li>Sony (4%)</li>
<li>Comcast and Motorola (3%)</li></ul><br>
<strong>Floorstanding Speakers</strong><br>
<ul>
<li>B&amp;W (27%)</li>
<li>Klipsch (23%)</li>
<li>Triad (13%)</li>
<li>Paradigm (13%)</li>
<li>Canton (9%)</li></ul><br>
<strong>Front Projection TVs</strong><br>
<ul>
<li>Runco (43%)</li>
<li>Sony (38%)</li>
<li>JVC (14%)</li>
<li>Marantz (14%)</li>
<li>Digital Projection (13%)</li></ul><br>
<strong>Home Theater Amplifiers</strong><br>
<ul>
<li>Integra (23%)</li>
<li>Lexicon (21%)</li>
<li>Rotel (16%)</li>
<li>McIntosh (13%)</li>
<li>JBL (13%)</li></ul><br>
<strong>In-Wall / In-Ceiling Speakers</strong><br>
<ul>
<li>Speakercraft (48%)</li>
<li>Sonance (25%)</li>
<li>Triad (23%)</li>
<li>B&amp;W (22%)</li>
<li>Klipsch (16%)</li></ul><br>
<strong>LCD TVs</strong><br>
<ul>
<li>Sharp (50%)</li>
<li>Sony (48%)</li>
<li>LG (37%)</li>
<li>Samsung (25%)</li>
<li>Runco (20%)</li></ul><br>
<strong>Media Servers</strong><br>
<ul>
<li>Kaleidescape (50%)</li>
<li>Escient (39%)</li>
<li>Request (23%)</li>
<li>Crestron (10%)</li>
<li>Elan (7%)</li></ul><br>
<strong>Plasma TVs</strong><br>
<ul>
<li>Pioneer (56%)</li>
<li>Runco (39%)</li>
<li>LG (28%)</li>
<li>Pioneer Elite (25%)</li>
<li>Samsung (22%)</li></ul><br>
<strong>Rear Projection TVs</strong><br>
<ul>
<li>Sony (38%)</li>
<li>Mitsubishi (29%)</li>
<li>Samsung (26%)</li>
<li>JVC (6%)</li>
<li>NuVision (4%)</li></ul><br>
<br><strong>Who's Cuisine Reigns Supreme?</strong><br>
Sony, appearing in 6 of the 12 categories we looked at.  Still the 800 lbs. gorilla.<br>
<br>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>June 16, 2008 11:11 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1432
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
					AND entry_id <> 1432
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/06/hdtv_and_home_theater_podcast_285_-_custom_installers_favorite_brands.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
