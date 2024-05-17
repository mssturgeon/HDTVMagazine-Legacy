<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1271";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1271 AND placement_is_primary = 1";
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
	<meta name="keywords" content="mac mini, port gigabit, gigabit switch, express airtunes, airport express, mac, Mini, one, mini, Mac, Ara, ara, Gigabit, gigabit, Netgear, programs, airport, Airport, Switch, netgear, switch, port, system, network, GHz" />
	<meta name="description" content="We've received a few emails asking for Ara to describe his A/V network. By now you probably know that at the center of the system is a Mac Mini serving up DVDs but what else can it do and how many zones does Ara have in his house. Today's show will take you through a virtual walk through of Ara's home...." />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #253 - Ara's A/V Network</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_253_-_aras_av_network';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #253 - Ara\'s A/V Network'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/02/hdtv_and_home_theater_podcast_253_-_aras_av_network.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #253 - Ara's A/V Network</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>February 23, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/02/hdtv_and_home_theater_podcast_253_-_aras_av_network.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/02/hdtv_and_home_theater_podcast_253_-_aras_av_network.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/02/hdtv_and_home_theater_podcast_253_-_aras_av_network.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/02/hdtv_and_home_theater_podcast_253_-_aras_av_network.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23253%20-%20Ara%27s%20A%2FV%20Network&amp;bodytext=We%27ve%20received%20a%20few%20emails%20asking%20for%20Ara%20to%20describe%20his%20A%2FV%20network.%20By%20now%20you%20probably%20know%20that%20at%20the%20center%20of%20the%20system%20is%20a%20Mac%20Mini%20serving%20up%20DVDs%20but%20what%20else%20can%20it%20do%20and%20how%20many%20zones%20does%20Ara%20have%20in%20his%20house.%20Today%27s%20show%20will%20take%20you%20through%20a%20virtual%20walk%20through%20of%20Ara%27s%20home....&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
<div><a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-02-26.mp3">Listen Now - mp3</a>
<br>
<a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a>
<br>
<a href="http://www.htguys.com">Website</a>
<br>
<br>

<strong>Ara's A/V Network</strong></div>
<div>We've received a few emails asking for Ara to describe his A/V network. By now you probably know that at the center of the system is a Mac Mini serving up DVDs but what else can it do and how many zones does Ara have in his house. Today's show will take you through a virtual walk through of Ara's home.&nbsp;</div>
<div style="padding: 8px; text-align: left; direction: ltr;" dir="ltr">Click image for larger picture</div>
<div style="padding: 8px; text-align: left; direction: ltr;" dir="ltr" id="doc-contents">
<div style="padding: 1em 0pt; text-align: left;" id="f0l0"><a target="_blank" href="http://www.htguys.com/images/Network%20topology%20II.jpg"><img title="" alt="" src="http://docs.google.com/File?id=dfhn4f7q_214gg7mrxcx" border="0" height="362" width="484"></a></div>

<div style="padding: 1em 0pt; text-align: left;" id="f0l0">
<div>
<table style="border-collapse: separate;" id="yaz1" border="0" cellpadding="3" cellspacing="0" height="602" width="449"><tbody><tr><td valign="top">
<div><strong>&nbsp;</strong></div>
<div><strong>Zone 1:</strong><br>
<ul>
<li>Motorola Cable Modem</li>
<li>Airport Extreme Wireless N Gigabit Router</li>
<li>Mac Mini 1.8 GHz 1 GB RAM</li>
<li>Airport Express with Airtunes</li>
<li>Slingdevices SlingBox</li>

<li>Netgear GS605 5-port Gigabit Switch</li>
<li>32 inch Viewsonic 720pLCD TV</li></ul><strong><br>
</strong><strong>Zone 2:</strong><br>
<ul>
<li>Mac Mini 1.8 GHz 1 GB RAM</li>
<li>Newer Technology 750GB External Hard Drive</li>
<li>HD Homerun</li>
<li>Netgear GS605 5-port Gigabit Switch</li>
<li>Samsung 50 inch DLP</li></ul><br>

<strong><br>
</strong></div></td><td align="left" valign="top" width="50%"> 
<div style="text-align: left;"><br>
<strong></strong></div>
<div style="text-align: left;"><strong>Zone 3</strong><br>
<ul>
<li> Mac Mini 1.8 GHz 1 GB RAM</li>
<li> Netgear GS608 8-port Gigabit Switch</li>
<li> Airport Express with Airtunes</li>
<li> Slingdevices SlingBox</li>

<li> Yamaha RX-V2700 Receiver</li>
<li> Xbox 360</li>
<li> Toshiba HD-A3 HD DVD</li>
<li> Samsung BD-P1400 Blu Ray</li>
<li> DirecTV HD DVR</li>
<li> Mitsubishi WD-65831 DLP TV</li></ul><strong>&nbsp;</strong></div>

<div style="text-align: left;"><strong>Zone 4,5,6</strong><br>
</div>
<ul>
<li>Apple iMac</li></ul><strong><br>
</strong><strong>Zone 7</strong><br>
<ul>
<li>Airport Express with Airtunes</li></ul><strong><br>
</strong><strong>Communications Closet</strong><br>
<ul>
<li>Netgear GS608 8-port Gigabit Switch</li></ul>
<p>&nbsp;</p></td></tr></tbody></table></div><strong><a href="http://www.htguys.com/archive/2008/February26_Parts_List.html" target="_blank">Cost</a><br>

</strong><br>
<strong>How it all works Together<br>
</strong>
<ul>
<li>All movies are stored on the external drive connected to the Mac Mini in Zone 2 </li>
<li>Over the Air HD programs are recorded and stored on the Mac Mini in Zone 3. These programs are shared with all computers in all zones, even wireless laptops if connected</li>
<li>An off the shelf application (<a href="http://www.iospirit.com/index.php?mode=view&amp;obj_type=infogroup&amp;obj_id=24&amp;o_infogroup_objcode=infogroup-23&amp;sid=5753663Gf77c881b9b78e327" target="_blank">Remote Buddy</a>) provides access to all the movies, television programs pictures, and music on the system. The application also acts a web server so control of the system can by achieved via an iPhone, iPod Touch, or any other computer with a web browser.</li>
<li>The computers and music devices have played 4 simultaneous HD programs and one stream of audio served off the Mac Mini simultaneously.</li></ul></div></div>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>February 23, 2008 06:42 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1271
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
					AND entry_id <> 1271
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/02/hdtv_and_home_theater_podcast_253_-_aras_av_network.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
