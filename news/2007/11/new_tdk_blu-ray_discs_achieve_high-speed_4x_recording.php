<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 802";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 802 AND placement_is_primary = 1";
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
	<meta name="keywords" content="blu ray, ray disc, ray discs, disc media, cover layer, recording, TDK, tdk, blu, ray, Blu, disc, media, Disc, discs, layer, Discs, new, coating, high, technology, durabis, recordable, cover, speed" />
	<meta name="description" content="TDK, a world leader in digital recording solutions, today announced the launch of two new write-once Blu-ray Discs capable of recording at 4x speed. TDK 4x 25GB BD-R Blu-ray Disc media and the 50GB version will both begin shipping later this summer. Retail pricing is set at $19.99 for a 4x 25GB BD-R25B (recordable), while pricing for the 4x 50GB BD-R50B (recordable) has not been finalized. A TDK 4x 25GB Blu-ray Disc can be fully recorded in 22 minutes, and a TDK 4x 50GB Blu-ray Disc can be fully recorded in 45 minutes, cutting the recording time in half in comparison to 2x Blu-ray Disc media. Additionally, TDK announced that it will offer business customers the world's first 50 piece spindles of 4x 25GB BD-R Blu-ray Disc media.

A pioneer of blue laser recording technology and founding member of the Blu-ray Disc Association, TDK began offering its highly anticipated 2x 25GB recordable and rewritable Blu-ray Disc media in the first quarter of 2006. TDK first shipped mass production samples in..." />
	<title>HDTV Magazine Bulletins - New TDK Blu-ray Discs Achieve High-Speed 4x Recording</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/new_tdk_blu-ray_discs_achieve_high-speed_4x_recording';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('New TDK Blu-ray Discs Achieve High-Speed 4x Recording'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2007/11/new_tdk_blu-ray_discs_achieve_high-speed_4x_recording.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">New TDK Blu-ray Discs Achieve High-Speed 4x Recording</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>November 29, 2007</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=HD DVD & Blu-ray">HD DVD & Blu-ray</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2007/11/new_tdk_blu-ray_discs_achieve_high-speed_4x_recording.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2007/11/new_tdk_blu-ray_discs_achieve_high-speed_4x_recording.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2007/11/new_tdk_blu-ray_discs_achieve_high-speed_4x_recording.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2007/11/new_tdk_blu-ray_discs_achieve_high-speed_4x_recording.php&amp;phase=2&amp;title=New%20TDK%20Blu-ray%20Discs%20Achieve%20High-Speed%204x%20Recording&amp;bodytext=TDK%2C%20a%20world%20leader%20in%20digital%20recording%20solutions%2C%20today%20announced%20the%20launch%20of%20two%20new%20write-once%20Blu-ray%20Discs%20capable%20of%20recording%20at%204x%20speed.%20TDK%204x%2025GB%20BD-R%20Blu-ray%20Disc%20media%20and%20the%2050GB%20version%20will%20both%20begin%20shipping%20later%20this%20summer.%20Retail%20pricing%20is%20set%20at%20%2419.99%20for%20a%204x%2025GB%20BD-R25B%20%28recordable%29%2C%20while%20pricing%20for%20the%204x%2050GB%20BD-R50B%20%28recordable%29%20has%20not%20been%20finalized.%20A%20TDK%204x%2025GB%20Blu-ray%20Disc%20can%20be%20fully%20recorded%20in%2022%20minutes%2C%20and%20a%20TDK%204x%2050GB%20Blu-ray%20Disc%20can%20be%20fully%20recorded%20in%2045%20minutes%2C%20cutting%20the%20recording%20time%20in%20half%20in%20comparison%20to%202x%20Blu-ray%20Disc%20media.%20Additionally%2C%20TDK%20announced%20that%20it%20will%20offer%20business%20customers%20the%20world%27s%20first%2050%20piece%20spindles%20of%204x%2025GB%20BD-R%20Blu-ray%20Disc%20media.%0A%0AA%20pioneer%20of%20blue%20laser%20recording%20technology%20and%20founding%20member%20of%20the%20Blu-ray%20Disc%20Association%2C%20TDK%20began%20offering%20its%20highly%20anticipated%202x%2025GB%20recordable%20and%20rewritable%20Blu-ray%20Disc%20media%20in%20the%20first%20quarter%20of%202006.%20TDK%20first%20shipped%20mass%20production%20samples%20in...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p class="prtitle">NEW TDK BLU-RAY DISCS ACHIEVE HIGH-SPEED 4X RECORDING</p>

<center><i>- TDK Recordable Blu-ray Discs Combine New Speed Milestone with Remarkable Capacities and Revolutionary Durability -</i></center><br />
<br />

<p><B>GARDEN CITY, NY, July 12, 2007</B> - TDK, a world leader in digital recording solutions, today announced the launch of two new write-once Blu-ray Discs capable of recording at 4x speed. TDK 4x 25GB BD-R Blu-ray Disc media and the 50GB version will both begin shipping later this summer. Retail pricing is set at $19.99 for a 4x 25GB BD-R25B (recordable), while pricing for the 4x 50GB BD-R50B (recordable) has not been finalized. A TDK 4x 25GB Blu-ray Disc can be fully recorded in 22 minutes, and a TDK 4x 50GB Blu-ray Disc can be fully recorded in 45 minutes, cutting the recording time in half in comparison to 2x Blu-ray Disc media. Additionally, TDK announced that it will offer business customers the world's first 50 piece spindles of 4x 25GB BD-R Blu-ray Disc media.</p>

<p>A pioneer of blue laser recording technology and founding member of the Blu-ray Disc Association, TDK began offering its highly anticipated 2x 25GB recordable and rewritable Blu-ray Disc media in the first quarter of 2006. TDK first shipped mass production samples in December 2005, and with the subsequent issuance of the relevant license, the company immediately commenced full force manufacturing.</p>

<p>"Demand for recordable Blu-ray Disc media has been growing rapidly," noted Bruce Youmans, TDK Vice President of Product Research and Development. "By achieving a 4x recording speed, TDK's new discs slash recording times in half compared to 2x media, extending the format's appeal even further by making it as convenient as it is powerful." He continued, "Our 4x, 50GB and 25GB Blu-ray Disc media products solidify TDK's role in enabling the achievement of remarkable capacities, fast transfer rates and revolutionary durability for TDK Blu-ray Discs."</p>

<p>The BDA (Blu-ray Disc Association) certified the specification for the new 4x, write-once type Blu-ray Discs, paving the way for their launch. The new discs support recording speeds from 1x to 4x, and like previous TDK Blu-ray Discs, incorporate a recording layer comprised of inorganic material. The discs are further highlighted by TDK's exclusive DURABIS 2 hard coating technology, an ultra-smooth cover layer created through the innovative spin coating method, and a host of other advanced TDK technologies that enable the creation of high-reliability media.</p>

<p>TDK Technologies Make Bare Blu-ray Discs a Reality<br />
TDK Blu-ray Discs are manufactured to the highest quality standards at the company's Chikumagawa Techno Factory in Japan. Outfitted with state of the art technology, the Chikumagawa factory is poised to lead the charge on optical media development well into the future. TDK heavily committed its worldwide engineering resources to Blu-ray, and has created new formulations and manufacturing techniques that constitute revolutionary milestones in recording technology.</p>

<p>Because Blu-ray Disc media's data tracks are quite narrow even in comparison with DVD media, precise, stable interaction between the laser and the recording material is especially critical to ensuring error-free recording and playback. That's why TDK developed DURABIS 2, an innovative hard coating technology that makes bare Blu-ray Disc media a reality by protecting the disc surface against common contaminants such as scratches and fingerprints. DURABIS 2's revolutionary resistance to fingerprints and scratches eliminates the need for a cartridge, minimizing manufacturing costs and allowing for the same user experience as with today's CDs and DVDs. TDK pioneered hard coating technology, and its DURABIS 2 is the ultimate protective coating for Blu-ray Discs.</p>

<p><br />
<B>Main Features of TDK 4x Blu-ray Discs</B></p>

<p>1. 4x recording compatible: Reduces recording time by half compared to 2x disc.</p>

<p>A recording layer boasting high sensitivity is utilized for compatibility with 4x recording (144Mbps transfer rate). 4x recording reduces recording time by half compared with the previous 2x disc. 4.7GB of data can be copied in less than 5 minutes, which is comparable to a DVD-R recording speed of 16x.</p>

<p>2. DURABIS 2 coating provides significantly greater resistance to scratches and dirt (particularly fingerprint smudges), ensuring safe use even without a cartridge.</p>

<p>Since the area of the laser spot on the Blu-ray Disc is small (about one-fifth that of the DVD), scratches or dirt on the recording surface can have an especially detrimental effect, causing errors. DURABIS 2 overcomes the issue by offering significantly higher resistance to scratches, and exceptional resistance to dirt and grime (particularly fingerprint smudges).</p>

<p>3. Exclusive, high-precision spin coating creates a cover layer with nano-precise smoothness for breakthrough stability.</p>

<p>The precision and smoothness of the cover layer is extremely important, because the laser beam must cleanly pass through the cover layer in its path to the recording layer. To form this cover layer, TDK utilizes an exclusive high-precision spin coating method. The thickness of the cover layer is controlled to the nano-level. As a result, the load on the focus servo circuit used to correct laser beam positioning is reduced, enabling breakthrough stability.</p>

<p>4. Recording layer utilizing inorganic material in a metal, dual-layer structure is unaffected by exposure to light, giving it outstanding archivability.</p>

<p>Previous write-once optical media such as CD-R and DVD-R utilized organic dye for their recording layers. Write-once type BD-R media is based on a completely new concept for the recording material wherein a two-layer structure composed of silicon (Si) and copper alloy (Cu) inorganic materials is utilized. When heated by the recording laser beam, these melt and the Si and Cu alloy become a composite to form recording marks. Because the material is inorganic, it is not affected by light, and offers superior archivability.</p>

<p>About TDK<br />
TDK Corporation (NYSE: TDK) is a leading global electronics company based in Japan. It was established in 1935 to commercialize "ferrite," a key material in electronics and magnetics. The company today is a leader in the development of next-generation technologies such as Blu-ray Disc recording media, an optical disc ideally suited for high-definition video recording. TDK offers a full line of recordable DVD and CD media, digital camcorder tapes, professional data storage solutions including LTO Ultrium media, and much more.</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>November 29, 2007 08:48 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 802
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
 			<h2>More on HD DVD & Blu-ray</h2><ul><?
 			$sql = "
 			SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name
 			FROM mt_entry e, mt_author a, mt_placement p, mt_category c
 			WHERE entry_author_id = author_id
 				AND e.entry_id = p.placement_entry_id
 				AND p.placement_category_id = c.category_id
 				AND category_label = 'HD DVD & Blu-ray'
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
 				AND entry_id <> 802
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2007/11/new_tdk_blu-ray_discs_achieve_high-speed_4x_recording.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
