<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 718";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Dale Cripps'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 718 AND placement_is_primary = 1";
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
	<meta name="keywords" content="inch full, price premium, lcd tvs, displaybank tel, consumers prefer, inch, full, LCD, lcd, displaybank, Full, PDP, pdp, price, survey, market, tel, preference, consumers, inquires, Inquires, premium, Tel, research, consumer" />
	<meta name="description" content="&lt;em&gt;Consumers prefer a 46-inch full HD LCD TV rather than a 50-inch HD PDP TV, according to the latest survey data. &lt;/em&gt;

While the full HD TV market is continuing to flourish, led by large sizes, Displaybank (CEO Peter Kwon, www.displaybank.com &lt;http://www.displaybank.com/&gt; ), a display market research institute, has surveyed on the preference of 46-inch full HD LCD TV and 50-inch HF PDP TV targeting 673 website visitors,..." />
	<title>HDTV Magazine Bulletins - Large-Size TV Market: Consumers Prefer Full-HD to Larger Sets</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/large-size_tv_market_consumers_prefer_full-hd_to_larger_sets';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Large-Size TV Market: Consumers Prefer Full-HD to Larger Sets'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2007/09/large-size_tv_market_consumers_prefer_full-hd_to_larger_sets.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Dale Cripps" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Large-Size TV Market: Consumers Prefer Full-HD to Larger Sets</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Dale Cripps</b><br />
				<?=$author_title?>
				Posted on <b>September 14, 2007</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Marketplace">Marketplace</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2007/09/large-size_tv_market_consumers_prefer_full-hd_to_larger_sets.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2007/09/large-size_tv_market_consumers_prefer_full-hd_to_larger_sets.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2007/09/large-size_tv_market_consumers_prefer_full-hd_to_larger_sets.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2007/09/large-size_tv_market_consumers_prefer_full-hd_to_larger_sets.php&amp;phase=2&amp;title=Large-Size%20TV%20Market%3A%20Consumers%20Prefer%20Full-HD%20to%20Larger%20Sets&amp;bodytext=%3Cem%3EConsumers%20prefer%20a%2046-inch%20full%20HD%20LCD%20TV%20rather%20than%20a%2050-inch%20HD%20PDP%20TV%2C%20according%20to%20the%20latest%20survey%20data.%20%3C%2Fem%3E%0A%0AWhile%20the%20full%20HD%20TV%20market%20is%20continuing%20to%20flourish%2C%20led%20by%20large%20sizes%2C%20Displaybank%20%28CEO%20Peter%20Kwon%2C%20www.displaybank.com%20%3Chttp%3A%2F%2Fwww.displaybank.com%2F%3E%20%29%2C%20a%20display%20market%20research%20institute%2C%20has%20surveyed%20on%20the%20preference%20of%2046-inch%20full%20HD%20LCD%20TV%20and%2050-inch%20HF%20PDP%20TV%20targeting%20673%20website%20visitors%2C...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p class="prtitle">Large-Size TV Market: Consumers Prefer to Full-HD Clearly</p>

<center><i>Consumers prefer a 46-inch full HD LCD TV rather than a 50-inch HD PDP TV, according to the latest survey data.</i></center><br />
<br />
While the full HD TV market is continuing to flourish, led by large sizes, Displaybank (CEO Peter Kwon, www.displaybank.com <http://www.displaybank.com/> ), a display market research institute, has surveyed on the preference of 46-inch full HD LCD TV and 50-inch HF PDP TV targeting 673 website visitors, highlighting the fact that 65.8%, more than half the respondents, nominated 46-inch full HD LCD TVs.

<p>46-inch Full-HD LCD TV and 50-inch HD PDP TV Preference Survey</p>

<p> <http://www.displaybank.com/news_img3/938740.gif> </p>

<p><*Note that the data is applied where the prices are same.></p>

<p>The survey also proposes a gap between the two devices (46-inch full HD LCD TV: 3.2 million won or $3,430.80, 50-inch HD PDP TV: 2.5 million won or $2,680.53), and as a result, 47.8% of the respondents still prefers to the 46-inch full HD LCD TV despite the price differential. This seems to be attributable to the aggressive marketing activities for LCD TVs and consumer's recognition of a premium on the full HD TV to a certain extent.</p>

<p><br />
46-inch Full-HD LCD TV and 50-inch HD PDP TV Preference Survey</p>

<p> <http://www.displaybank.com/news_img3/795103.gif> </p>

<p>In addition, the survey on the price premium for a full HD TV reveals that 52.8% of the respondents said that a full HD TV has a 10% to 20% higher price premium than a HD TV, proving consumers' preference to a 46-inch full HD LCD TV.</p>

<p>HD TV vs. Full-HD TV Price Premium</p>

<p> <http://www.displaybank.com/news_img3/323279.gif> </p>

<p><*Note that the data is applied where the screen sizes are same.></p>

<p>The survey result seems to be an important analysis data for product roadmaps and marketing activities of TV vendors and distributors. </p>

<p>________________________________</p>

<p><br />
 <http://www.displaybank.com/eng2004/consulting/img/consulting_consumer_tit.gif><br />
Displaybank has implemented regular consumer researches in the field of displays and reported the result. For customers who desire to have a further detailed consumer research, an extra tailored consulting is also provided. </p>

<p>Displaybank's consumer research is more specific and more specialized than any rival research firms by using a wide range of worldwide networks of industry knowledge and 90,000 specialized panels. </p>

<p>	<br />
 <http://www.displaybank.com/eng2004/consulting/img/consulting_consumer_tit2.gif> 	<br />
 <http://www.displaybank.com/new2004/consulting/img/diagram_consumer.gif> <br />
 <http://www.displaybank.com/eng2004/consulting/img/consulting_sub3_tit.gif> 	<br />
	<br />
	<br />
	<br />
	</p>

<p> <http://www.displaybank.com/eng2004/consulting/img/consulting_sub2_ar.gif> 46-inch Full-HD LCD TV vs. 50-inch HD PDP TV <http://www.displaybank.com/eng2004/research/report.php?mode=show&id=364>  	<br />
	<br />
	<br />
	<br />
 <http://www.displaybank.com/eng2004/consulting/img/consulting_sub2_ar.gif> What are Complaints of LCD TV users? <http://www.displaybank.com/eng2004/research/report.php?mode=show&id=369>  	<br />
	<br />
	<br />
	<br />
 <http://www.displaybank.com/eng2004/consulting/img/consulting_sub2_ar.gif> What is Appropriate Price for 26-inch Wide Monitor? <http://www.displaybank.com/eng2004/research/report.php?mode=show&id=370>  	<br />
	<br />
	<br />
	<br />
 <http://www.displaybank.com/eng2004/consulting/img/consulting_sub2_ar.gif> What is the Limit Size for Personal Monitors? <http://www.displaybank.com/eng2004/research/report.php?mode=show&id=366>  	<br />
	<br />
	<br />
	<br />
 <http://www.displaybank.com/eng2004/consulting/img/consulting_sub2_ar.gif> What is the Price Point where 40-inch Range LED LCD TVs are Reasonable to be Accepted? <http://www.displaybank.com/eng2004/research/report.php?mode=show&id=367>  	<br />
	<br />
	<br />
	<br />
 <http://www.displaybank.com/eng2004/consulting/img/consulting_sub2_ar.gif> When will 30-inch Range OLED TV Hit the Market? <http://www.displaybank.com/eng2004/research/report.php?mode=show&id=365>  	<br />
	<br />
	<br />
	<br />
 <http://www.displaybank.com/eng2004/consulting/img/consulting_sub2_ar.gif> Who are Potential Customers of 26-inch Wide LCD Monitor? <http://www.displaybank.com/eng2004/research/report.php?mode=show&id=371>  	<br />
	<br />
	<br />
 	<br />
 <http://www.displaybank.com/letter/img/report_top_guide_eng.gif><br />
Kristy Oh (Korea)<br />
- Tel :+82.31.704.7188 (#132)<br />
- Fax :+82.31.704.7187<br />
- Inquires : kristy@displaybank.com<br />
David Yu (China)<br />
- Reports Inquires : david@displaybank.com<br />
- Tel :+86.137.6114.3581	 Noriko Ishida (Japan)<br />
- Reports Inquires : ishida@displaybank.com<br />
- Tel : +81-45-670-7114, Fax : +81-45-670-7001 Sue Chung (U.S & Europe)<br />
- Inquires : sue@displaybank.com<br />
- Tel :+1.408.366.1448<br />
</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Dale Cripps</b>, <b>September 14, 2007 08:11 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 718
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
 			<h2>More on Marketplace</h2><ul><?
 			$sql = "
 			SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name
 			FROM mt_entry e, mt_author a, mt_placement p, mt_category c
 			WHERE entry_author_id = author_id
 				AND e.entry_id = p.placement_entry_id
 				AND p.placement_category_id = c.category_id
 				AND category_label = 'Marketplace'
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
 				AND entry_id <> 718
 				AND entry_status = 2
 				AND entry_author_id = a.author_id
 				AND a.author_name = 'Dale Cripps'
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
 				<h2>About Dale Cripps</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2007/09/large-size_tv_market_consumers_prefer_full-hd_to_larger_sets.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
