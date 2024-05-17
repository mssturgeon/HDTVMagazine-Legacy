<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1545";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1545 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category[placement_category_id];
	
	# Still need this to track page views. Used in the Links unit in the footer
	$google_links_channel = $author[channel];

	# Set variables based on entry type. NOTE: Podcasts has its own entry template, so it is not included amongst the choices below.
	switch (7) {
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
	<meta name="keywords" content="silicon mountain, blu ray, mountain holdings, holdings inc, ray dvd, Allio, allio, mountain, silicon, Silicon, Mountain, Blu, company, blu, ray, holdings, digital, Holdings, high, player, system, DVD, screen, statements, while" />
	<meta name="description" content="Silicon Mountain Holdings, Inc., (OTC:SLCM) (BULLETIN BOARD: SLCM) , a technology company specializing in high-performance interactive computing solutions, today announced its design for a 32 and 42-inch High Definition LCD-TV with an integrated, full-function PC and Blu-ray/DVD player. Named Allio, this system will define an entirely new category of converged products, where entertainment and instant, on-demand information and productivity blend together seamlessly, in stunning high-definition.

The Allio HD TV / PC is the first..." />
	<title>HDTV Magazine Bulletins - Silicon Mountain Unveils Allio(TM) 42-inch HDTV With Integrated PC and Blu-ray Player</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/silicon_mountain_unveils_alliotm_42-inch_hdtv_with_integrated_pc_and_blu-ray_player';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Silicon Mountain Unveils Allio(TM) 42-inch HDTV With Integrated PC and Blu-ray Player'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2008/11/silicon_mountain_unveils_alliotm_42-inch_hdtv_with_integrated_pc_and_blu-ray_player.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Silicon Mountain Unveils Allio(TM) 42-inch HDTV With Integrated PC and Blu-ray Player</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>November 10, 2008</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Products & Equipment">Products & Equipment</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2008/11/silicon_mountain_unveils_alliotm_42-inch_hdtv_with_integrated_pc_and_blu-ray_player.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2008/11/silicon_mountain_unveils_alliotm_42-inch_hdtv_with_integrated_pc_and_blu-ray_player.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2008/11/silicon_mountain_unveils_alliotm_42-inch_hdtv_with_integrated_pc_and_blu-ray_player.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2008/11/silicon_mountain_unveils_alliotm_42-inch_hdtv_with_integrated_pc_and_blu-ray_player.php&amp;phase=2&amp;title=Silicon%20Mountain%20Unveils%20Allio%28TM%29%2042-inch%20HDTV%20With%20Integrated%20PC%20and%20Blu-ray%20Player&amp;bodytext=Silicon%20Mountain%20Holdings%2C%20Inc.%2C%20%28OTC%3ASLCM%29%20%28BULLETIN%20BOARD%3A%20SLCM%29%20%2C%20a%20technology%20company%20specializing%20in%20high-performance%20interactive%20computing%20solutions%2C%20today%20announced%20its%20design%20for%20a%2032%20and%2042-inch%20High%20Definition%20LCD-TV%20with%20an%20integrated%2C%20full-function%20PC%20and%20Blu-ray%2FDVD%20player.%20Named%20Allio%2C%20this%20system%20will%20define%20an%20entirely%20new%20category%20of%20converged%20products%2C%20where%20entertainment%20and%20instant%2C%20on-demand%20information%20and%20productivity%20blend%20together%20seamlessly%2C%20in%20stunning%20high-definition.%0A%0AThe%20Allio%20HD%20TV%20%2F%20PC%20is%20the%20first...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p class="prtitle">Silicon Mountain Unveils Allio(TM) 42-inch HDTV With Integrated PC and Blu-ray Player</p>

<center><i>Product, First of Its Kind in North America, Now Available</i></center><br />
<br />

<p><B>BOULDER, Colo. & MENLO PARK, Calif., Nov. 10 /PRNewswire-FirstCall/</B> -- Silicon Mountain Holdings, Inc., (OTC:SLCM) (BULLETIN BOARD: SLCM) , a technology company specializing in high-performance interactive computing solutions, today announced its design for a 32 and 42-inch High Definition LCD-TV with an integrated, full-function PC and Blu-ray/DVD player. Named Allio, this system will define an entirely new category of converged products, where entertainment and instant, on-demand information and productivity blend together seamlessly, in stunning high-definition.</p>

<p>The Allio HD TV / PC is the first product of its kind in North America, and Silicon Mountain is the first company worldwide to develop a converged HD TV / PC solution that includes Blu-ray. It is available now, in time for U.S. holiday purchases. Orders are being taken now at the Visionman website at http://www.visionman.com/.</p>

<p>Additional information on where to buy Allio will be announced soon.</p>

<p>The flagship Allio model marries a Full-HD 42" LCD display with a combo Blu-ray/DVD player, integrated digital recorder for PVR and a powerful PC, based on the Intel Core2Duo E8400 processor, 4GB of RAM from Silicon Mountain, a 1TB hard drive and the 64-bit version of Windows Vista Home Premium. In addition to the analog and digital audio-video inputs common to high-def televisions, Allio includes wireless and wired networking capabilities and several USB ports to extend the experience to other computers and peripherals in the home.</p>

<p><br />
<B>Internet Video Comes to the Big Screen</B></p>

<p>Integrating the television with the Internet brings a new source of content to the digital lifestyle -- Internet video. Normally confined to smaller computer monitors, streaming high definition content from providers like Joost, Hulu and TidalTV now can be accessed on the TV, in addition to popular clip sites such as YouTube. Allio's channel choices span cable, satellite and Internet for a truly converged, hybrid entertainment experience. Media libraries can be combined, shared and played from a single device. The Allio HD TV / PC enables users to store their iTunes and DVD collections on a single system.</p>

<p>"The Allio HD TV / PC takes the digital experience into another dimension," said Tre Cates, Silicon Mountain President and CEO. "During product development, we discovered that our testers regularly expressed disappointment in their own large screen television and home theatre configurations after using Allio for just a few hours. The results are clear. The Allio HD TV / PC experience simply suits our modern digital lifestyle better. The converged experience will boost productivity and interactive behaviors, and bring families together around the next generation of appliance, just as the early television and radio did for generations past."</p>

<p>Picture-in-picture and split-screen capabilities allow multiple sources of content to operate together on a single large screen. A Blu-ray or DVD can be watched in one window, while television is viewed in another pane, with computing tasks occurring simultaneously. The uses of this split-screen capability are seemingly endless. On Sunday, a fantasy football player can watch multiple games simultaneously, while browsing NFL.com for real-time player stats and scores, while chatting on AOL Instant Messenger or Skype with other league owners. A student can watch educational programming from a satellite or cable provider while writing a paper and looking up unfamiliar terms and concepts on Wikipedia, then taking a quick break to update his or her MySpace page. A business executive can view streaming stock quotes while composing email and watching financial news.</p>

<p><br />
<B>Systems start at $1,599.99</B></p>

<p>Six configurations of The Allio HD TV / PC are available, with features and pricing to fit any budget. The entry-level 32" and 42" Allio with an Intel 2.5 GHz PDC E5200, 250GB of storage, 2GB of RAM, DVD/CD support and Vista Home Premium retails for $1,599 and $1,999. The middle system in the series adds PVR and Blu-ray support and an upgraded 2.54 GHz Core2Duo E7200 processor for $2,199 and $2,399. The flagship Allio system ups the ante on the intermediate option, upgrading the RAM and storage to 4GB and 1TB, respectively, for $2,399 and $2,799.</p>

<p>For the budget-minded, Silicon Mountain plans to launch configurations based on the popular Ubuntu Linux operating system for an even lower-cost solution. Every Allio HD TV / PC model will be assembled in Northern California.</p>

<p>To be added to Silicon Mountain's investor lists, please contact Haris Tajyar, Managing Partner with Investor Relations International at htajyar@irintl.com or at 818-382-9702.</p>

<p><br />
<B>About Silicon Mountain Holdings</B></p>

<p>Silicon Mountain Holdings, Inc. (OTCBB: SLCM) is a technology company specializing in high performance interactive computing solutions. The Company has a strong portfolio of product brands and services that address the storage and security demands of the digital media age. Silicon Mountain has recently announced the third-quarter launch of online backup and is making the transition from lower-margin memory components to higher-margin devices, systems and services. Additional news and information about the Company is available at http://www.slcmholdings.com/.</p>

<p>Forward-looking statements: This release may contain forward-looking statements regarding the future and expected performance of Silicon Mountain Holdings, Inc. based on assumptions that the Company believes are reasonable. No assurances can be given that these statements will prove to be accurate. A number of risks and uncertainties could cause actual results to differ materially from these statements, including, without limitation, reduced customer demand, higher costs for components, labor, and other aspects of manufacturing, assembling and/or marketing, increased competition, and other risk factors described in the Company's Joint Definitive Proxy Statement, Form 8-K, and other reports filed with the Securities and Exchange Commission. Silicon Mountain Holdings, Inc. undertakes no obligation to publicly update these forward-looking statements, whether as result of new information, future events or otherwise.</p>

<p>All trademarks acknowledged.<br />
Photo: NewsCom: http://www.newscom.com/cgi-bin/prnh/20081110/LAM060<br />
AP Archive: http://photoarchive.ap.org/<br />
AP PhotoExpress Network: PRN10<br />
PRN Photo Desk, photodesk@prnewswire.com</p>

<p>Source: Silicon Mountain Holdings, Inc. </p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>November 10, 2008 01:07 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 1545
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
 			<h2>More on Products & Equipment</h2><ul><?
 			$sql = "
 			SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name
 			FROM mt_entry e, mt_author a, mt_placement p, mt_category c
 			WHERE entry_author_id = author_id
 				AND e.entry_id = p.placement_entry_id
 				AND p.placement_category_id = c.category_id
 				AND category_label = 'Products & Equipment'
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
			
 		<?if (7 <> 7) {
 			# Recent Articles by Author (exclude this one)
 			# Do not show recent articles for Bulletins.
 			$qry = "
 			SELECT entry_id, entry_blog_id, entry_created_on, entry_title, author_id, author_name
 			FROM mt_entry e, mt_author a
 			WHERE entry_blog_id = 7
 				AND entry_id <> 1545
 				AND entry_status = 2
 				AND entry_author_id = a.author_id
 				AND a.author_name = 'Shane Sturgeon'
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
 				<h2>About Shane Sturgeon</h2>
 				<?=stripslashes($author[bio_short])?>
 			<span class="corners-bottom"><span></span></span></div>
 		<?}?>
		</td><td id="right">

 		<?if ($about != '') {?>
 			<div class="item"><span class="corners-top"><span></span></span>
 				<h2>About Bulletins</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2008/11/silicon_mountain_unveils_alliotm_42-inch_hdtv_with_integrated_pc_and_blu-ray_player.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
