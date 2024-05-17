<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 771";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Ed Milbourn'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 771 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category[placement_category_id];
	
	# Still need this to track page views. Used in the Links unit in the footer
	$google_links_channel = $author[channel];

	# Set variables based on entry type. NOTE: Podcasts has its own entry template, so it is not included amongst the choices below.
	switch (1) {
		case 1: # Articles
			$feed_name = 'hdtv-articles';
			$container = 'article_container';
			$sub_type = SUB_ARTICLES;
			$sub_label = 'Receive instant notification of new articles';
			$sub_desc_logged_in = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of new HDTV Magazine Articles via email as soon as they are published.';
			$sub_desc_anon = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of new HDTV Magazine Articles via email as soon as they are published.';
			break;
		case 4: # Interviews
			$feed_name = 'hdtv-interviews';
			break;
		case 5: # History
			$feed_name = 'hdtv-archive';
			break;
		case 6: # Test
			$container = 'article_container';
			$sub_type = 0;
			break;
		case 7: # Bulletins
			$google_links_channel = ''; # Don't count bulletins
			$feed_name = 'hdtv-news';
			$container = 'bulletin_container';
			$sub_type = SUB_BULLETINS;
			$sub_label = 'Receive instant notification of HDTV Bulletins';
			$sub_desc_logged_in = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of HDTV Bulletins via email as soon as they are published.';
			$sub_desc_anon = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of HDTV Bulletins via email as soon as they are published.';
			break;
		case 8: # Reviews
			$feed_name = 'hdtv-reviews';
			$container = 'article_container';
			$sub_type = SUB_REVIEWS;
			$sub_label = 'Receive instant notification of new reviews';
			$sub_desc_logged_in = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of new HDTV Magazine Reviews via email as soon as they are published.';
			$sub_desc_anon = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of new HDTV Magazine Reviews via email as soon as they are published.';
			break;
#		case 9: # Podcasts
		case 10: # Columns
			$feed_name = 'hdtv-columns';
			$container = 'article_container';
			$sub_type = SUB_COLUMNS;
			$sub_label = 'Receive instant notification of new columns';
			$sub_desc_logged_in = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of new HDTV Magazine Columns via email as soon as they are published.';
			$sub_desc_anon = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of new HDTV Magazine Columns via email as soon as they are published.';
			$about = 'HDTV Magazine Columns are written by various personalities within the HDTV industry. They are typically shorter than our standard <a href="/articles">Article</a> and quite often express the opinion of the author(s). And of course, opinions expressed by these authors are not necessarily those of HDTV Magazine.';
			break;
		default:
			$container = 'body_container';
			break;
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<?require(BASE_DIR .'/includes/common_header.php');?>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<meta name="generator" content="http://www.movabletype.org/" />
	<meta name="keywords" content="broadcast spectrum, business model, traditional ota, broadcast standard, digital transition, spectrum, HDTV, digital, hdtv, broadcast, business, broadcasters, standard, traditional, systems, band, most, network, model, may, OTA, ota, technical, make, via" />
	<meta name="description" content="This provocative article from our Ed Milbourn will echo forward for a long time to come. While broadcasting is still a robust business the cracks in its business model are severe. Analog technology once dictated the business model for telecasting, but that is now remade beyond recognition with the advent of digital technology. What lays ahead for the use of broadcast spectrum? Let Ed Milbourn open your mind to an exciting and creative future. _ Dale Cripps

__________________________________________________ 
 

Don't panic!  This may be a very good thing for HDTV.  Sometimes it takes a seminal, very disruptive event to cause a fundamental change in traditional business and/or political models to ensure survival.  Failure to make those changes usually results in complete disaster.  Successful change, however, usually results in the surviving entity being stronger, more vibrant and successful than before.  History is replete with examples or this phenomenon, so I won't belabor this tome with any further philosophical discussions.  Suffice saying, however, traditional OTA (over-the-air) television broadcast may be upon that seminal event - the 2009 digital transition date.

In spite of all of the publicity, the subsidized digital converter and economic attraction of &quot;free&quot; HDTV, an increasingly smaller percentage of viewers are..." />
	<title>HDTV Magazine Articles - Ed's View - The Demise of Broadcasting</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/eds_view_-_the_demise_of_broadcasting';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Ed\'s View - The Demise of Broadcasting'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/articles/2007/11/eds_view_-_the_demise_of_broadcasting.php";
		if ($author[img] != '' && 1 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Ed Milbourn" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Ed's View - The Demise of Broadcasting</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Ed Milbourn</b><br />
				<?=$author_title?>
				Posted on <b>November  1, 2007</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Broadcast">Broadcast</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2007/11/eds_view_-_the_demise_of_broadcasting.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/articles/2007/11/eds_view_-_the_demise_of_broadcasting.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/articles/2007/11/eds_view_-_the_demise_of_broadcasting.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
				<span><img src="/images/save.gif" alt="Save Article" align="absmiddle" /><a target="_blank" href="<?=$save_url?>">Save</a></span>
				<span><img src="/images/email.gif" alt="Email Article" align="absmiddle" /><a href="<?=$email_url?>">Email</a></span>
				<span><img src="/images/print.png" alt="Print Article" align="absmiddle" /><a target="_blank" href="<?=$print_url?>">Print</a></span><br />
				<br /><br />
			</td>
		</tr>
	</table>
	<?if ($sub_type > 0 && ($userdata[subscriptions] & $sub_type) || $_SERVER[HTTP_USER_AGENT] == 'Googlebot') {} else {
		if ($userdata[session_logged_in]) {?>
			<div class="important"><span class="corners-top"><span></span></span>
				<img src="/images/i_inbox.gif" align="left" style="padding-right:10px" />
				<span class="label"><?=$sub_label?>:</span>
				<?=$sub_desc_logged_in?>
			<span class="corners-bottom"><span></span></span></div>
		<?} else {?>
			<div class="important"><span class="corners-top"><span></span></span>
				<img src="/images/i_inbox.gif" align="left" style="padding-right:10px" />
				<span class="label"><?=$sub_label?>:</span>
				<?=$sub_desc_anon?>
			<span class="corners-bottom"><span></span></span></div>
		<?}
	}?>
	<div>
		<div style="float:left; margin:0 5px 5px 0;"><?
			if ($digg_url == '') {
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2007/11/eds_view_-_the_demise_of_broadcasting.php&amp;phase=2&amp;title=Ed%27s%20View%20-%20The%20Demise%20of%20Broadcasting&amp;bodytext=This%20provocative%20article%20from%20our%20Ed%20Milbourn%20will%20echo%20forward%20for%20a%20long%20time%20to%20come.%20While%20broadcasting%20is%20still%20a%20robust%20business%20the%20cracks%20in%20its%20business%20model%20are%20severe.%20Analog%20technology%20once%20dictated%20the%20business%20model%20for%20telecasting%2C%20but%20that%20is%20now%20remade%20beyond%20recognition%20with%20the%20advent%20of%20digital%20technology.%20What%20lays%20ahead%20for%20the%20use%20of%20broadcast%20spectrum%3F%20Let%20Ed%20Milbourn%20open%20your%20mind%20to%20an%20exciting%20and%20creative%20future.%20_%20Dale%20Cripps%0A%0A__________________________________________________%20%0A%20%0A%0ADon%27t%20panic%21%20%20This%20may%20be%20a%20very%20good%20thing%20for%20HDTV.%20%20Sometimes%20it%20takes%20a%20seminal%2C%20very%20disruptive%20event%20to%20cause%20a%20fundamental%20change%20in%20traditional%20business%20and%2For%20political%20models%20to%20ensure%20survival.%20%20Failure%20to%20make%20those%20changes%20usually%20results%20in%20complete%20disaster.%20%20Successful%20change%2C%20however%2C%20usually%20results%20in%20the%20surviving%20entity%20being%20stronger%2C%20more%20vibrant%20and%20successful%20than%20before.%20%20History%20is%20replete%20with%20examples%20or%20this%20phenomenon%2C%20so%20I%20won%27t%20belabor%20this%20tome%20with%20any%20further%20philosophical%20discussions.%20%20Suffice%20saying%2C%20however%2C%20traditional%20OTA%20%28over-the-air%29%20television%20broadcast%20may%20be%20upon%20that%20seminal%20event%20-%20the%202009%20digital%20transition%20date.%0A%0AIn%20spite%20of%20all%20of%20the%20publicity%2C%20the%20subsidized%20digital%20converter%20and%20economic%20attraction%20of%20%22free%22%20HDTV%2C%20an%20increasingly%20smaller%20percentage%20of%20viewers%20are...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
			} else {
				echo '<script src="http://digg.com/api/diggthis.js"></script>';
			}
		?></div>
		<div id="right" style="float:right; margin:0 0 5px 5px; text-align:center;" align="center">
			<?include(BASE_DIR .'/ads/mrectangle.php');?>
			<br />
			<div align="center">
				<?include(BASE_DIR .'/ads/skyscraper.php');?>
			</div>
		</div>
		<div id="<?=$container?>">
			<p class="editorial">This provocative article from our Ed Milbourn will echo forward for a long time to come. While broadcasting is still a robust business the cracks in its business model are severe. Analog technology once dictated the business model for telecasting, but that is now remade beyond recognition with the advent of digital. What lays ahead for the use of broadcast spectrum? Let Ed Milbourn open your mind to an exciting and creative future. _ Dale Cripps</P>
 

<p>Don't panic!  This may be a very good thing for HDTV.  Sometimes it takes a seminal, very disruptive event to cause a fundamental change in traditional business and/or political models to ensure survival.  Failure to make those changes usually results in complete disaster.  Successful change, however, usually results in the surviving entity being stronger, more vibrant and successful than before.  History is replete with examples or this phenomenon, so I won't belabor this tome with any further philosophical discussions.  Suffice saying, however, traditional OTA (over-the-air) television broadcast may be upon that seminal event - the 2009 digital transition date.</p>

<p>In spite of all of the publicity, the subsidized digital converter and economic attraction of "free" HDTV, an increasingly smaller percentage of viewers are receiving TV via traditional OTA broadcasts.  Other than providing a convenient and very economical means to couple local signals to Cable, IPTV, and DBS distribution services, the broadcast transmitters are becoming an increasingly economic "drag" on broadcasters.  Of course, network and local broadcasters are aggressively exploring and embracing all of the alternate distribution systems for mainstream and re-purposed programming.  The problem is that their most valuable asset - their licensed digital bandwidth - will, in 2009, be mostly gobbled up by HDTV, which most viewers will be watching via Cable!</p>

<p>The only way to salvage their spectrum and make it profitable after the Transition is to adopt a "mobile/handheld (M/H)" multiplexing technical standard that maximizes program choices to compatible devices, and most importantly, to develop the business relationships that allow this to occur.  But one thing is clear; there is no room for "HDTV" in this spectrum equation, nor should there be.  This does not at all mean that broadcasters and networks will not produce and distribute the highest quality HDTV programming possible.  Indeed, that business is now established and growing.  It just will not be distributed via their OTA spectrum and will not have to be compromised by it. </p>

<p>However, broadcasters must quickly act to establish this M/H technical standard and business model.  At least two of several competing systems are actively deploying or conducting test programs in the US at this time.  They are MediaFLO, a complete network solution developed by Qualcomm, and DVB-H, an M/H version of the European terrestrial DVB broadcast standard.  There are several others in the hunt including those successfully deployed in Japan and Korea.  In the US these competing systems have obtained licenses in the auctioned 700 MHz (UHF) band made available to them as part of the DTV digital transition. </p>

<p>All the rest of the US TV band spectrum (most in the UHF band but some in the VHF band) is licensed to the traditional TV broadcasters, and they are just now attempting to come up with a cogent plan to compete in the M/H arena.  Indeed the Advanced Television Systems Committee (ATSC), the mother and father of our present DTV standards, this past year, has finally beginning to focus its efforts on developing an M/H standard for the broadcast spectrum.  So far there have been approximately ten responses to their request for proposals, two of which were successfully demonstrated at the 2007 NAB.  But much work needs to be done before the ATSC can present to the industry and the FCC a viable option.  In the meantime their competition is rapidly forging ahead.</p>

<p>The political and technical battles to adopt an M/H broadcast standard promises to be the next big heated battle in the digital spectrum arena.  It will engage all the classic issues of compatibility, compression, performance, "network neutrality," antitrust, cross-licensing, security, etc. etc.  And to make matters all the more interesting, the focus of the action will be back in Washington DC, where the rate of standards development is inversely proportional to the square of the number of lawyers involved.  A first level regressive analysis indicates there will be no resolution of these issues within the lifetimes of the participants.  </p>

<p>But, clearly, no terrestrial broadcast M/H solution can afford HDTV if an economically viable "critical mass" is to be maintained.  From a legal standpoint, justifying eliminating terrestrial HDTV from the broadcast spectrum mix may be comparatively easy.  In fact none of the FCC Reports and Orders establishing the present digital TV structure specifies that broadcasters must use any part of their digital spectrum to provide HDTV.  In only specifies that at least one of the "services" (multiplexes) be "free."  </p>

<p>It's going to get very interesting.  Stay tuned.</p>

<p><br />
Ed  <br />
</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Ed Milbourn</b>, <b>November  1, 2007 05:46 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 771
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

 		<div class="item"><span class="corners-top"><span></span></span>
 			<h2>More on Broadcast</h2><ul><?
 			$sql = "
 			SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name
 			FROM mt_entry e, mt_author a, mt_placement p, mt_category c
 			WHERE entry_author_id = author_id
 				AND e.entry_id = p.placement_entry_id
 				AND p.placement_category_id = c.category_id
 				AND category_label = 'Broadcast'
 				AND e.entry_status = 2
 				AND e.entry_blog_id IN (". INCLUDE_BLOGS_ALL .")
 				AND entry_author_id = a.author_id
 			ORDER BY entry_created_on DESC LIMIT 25";
 			$result = mQuery($sql);
 			while ($row = mysql_fetch_assoc($result)) {
 				$ts = strtotime($row[entry_created_on]);
 				$y = date('Y', $ts);
 				$m = date('m', $ts);
 				$entry = getEntryInfo($row[entry_blog_id]);
 	
 				$entry[date] = getDateString($ts);
 				$entry[link] = "/$entry[blog_dir]/$y/$m/". dirify($row[entry_title]) .".php";
 				$entry[title] = $row[entry_title];
 				$entry[author] = $row[author_name];

 				echo '<li><a href="'. $entry[link] .'">'. $entry[title] .'</a> - <span class="grey">'. $entry[author] .'</span> - '. $entry[date] .'</li>';
 			}
 		?></ul><span class="corners-bottom"><span></span></span></div>
			
 		<?if (1 <> 7) {
 			# Recent Articles by Author (exclude this one)
 			# Do not show recent articles for Bulletins.
 			$qry = "
 			SELECT entry_id, entry_blog_id, entry_created_on, entry_title, author_id, author_name
 			FROM mt_entry e, mt_author a
 			WHERE entry_blog_id = 1
 				AND entry_id <> 771
 				AND entry_status = 2
 				AND entry_author_id = a.author_id
 				AND a.author_name = 'Ed Milbourn'
 			ORDER BY entry_created_on DESC LIMIT 10";
 			$result = mQuery($qry);
 			
 			if (mysql_num_rows($result) > 0) {
 				$row = mysql_fetch_assoc($result);
 				echo '<div class="item"><span class="corners-top"><span></span></span>'.
 				'<h2><a href="/author.php?author='. urlencode($row[author_name]) .'&id='. $row[author_id] .'">More from '. $row[author_name] .'</a></h2><ul>';
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
 				<h2>About Ed Milbourn</h2>
 				<?=stripslashes($author[bio_short])?>
 			<span class="corners-bottom"><span></span></span></div>
 		<?}?>
		</td><td id="right">

 		<?if ($about != '') {?>
 			<div class="item"><span class="corners-top"><span></span></span>
 				<h2>About Articles</h2>
 				<?=$about?>
 			<span class="corners-bottom"><span></span></span></div>
		<?}?>
		
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
 				WHERE e.entry_blog_id IN (". INCLUDE_BLOGS_NO_BULLETINS .")
 					AND entry_status = 2
 					AND entry_author_id = author_id
 				GROUP BY author_id, author_name
 				ORDER BY num DESC";
 				$res_authors = mQuery($qry);
 				while ($row_authors = mysql_fetch_assoc($res_authors)) {
 					echo '<li><a href="/author.php?author='. urlencode($row_authors[author_name]) .'&id='. $row_authors[author_id] .'">'. $row_authors[author_name] .'</a><span class="grey"> ('. $row_authors[num] .')</span></li>';
 				}
 			?></ul>
 		<span class="corners-bottom"><span></span></span></div>

 		<div class="item"><span class="corners-top"><span></span></span>
 			<h2>Categories</h2>
 			<ul class="brownsquare"><?
 				$qry = "
 				SELECT category_label label, COUNT(*) num
 				FROM mt_entry e, mt_placement p, mt_category c
 				WHERE e.entry_blog_id IN (". INCLUDE_BLOGS_ALL .")
 					AND entry_status = 2
 					AND entry_id = p.placement_entry_id
 					AND p.placement_category_id = c.category_id
 				GROUP BY label
 				ORDER BY label";
 				$result = mQuery($qry);
 				while ($category = mysql_fetch_assoc($result)) {
 					echo '<li><a href="/category.php?category='. urlencode($category[label]) .'">'. $category[label] .'</a><span class="grey"> ('. $category[num] .')</span></li>';
 				}
 			?></ul>
 		<span class="corners-bottom"><span></span></span></div>

		</td>
	</tr></table>

	<?
		include(BASE_DIR .'/includes/body_footer.php');
	?>
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2007/11/eds_view_-_the_demise_of_broadcasting.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
