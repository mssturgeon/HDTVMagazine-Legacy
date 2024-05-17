<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 426";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 426 AND placement_is_primary = 1";
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
	<meta name="keywords" content="blu ray, buying season, titles available, making equal, get check, DVD, dvd, ray, blu, Blu, both, see, market, production, while, media, formats, price, available, player, marketing, winner, board, Sony, xbox" />
	<meta name="description" content="By now, I'm sure you've read a dozen articles like this one. In fact, I debated about whether or not to title it as such, as I was afraid most of you would skip it over. Obviously that is not the case if you are now reading these words.

If you are withholding your Next-Gen DVD player purchase until this so-called format war has a winner, it won't help ... there likely won't be one ... and there definitely won't be one any time soon." />
	<title>HDTV Magazine Articles - HD DVD vs. Blu-ray: And the Winner is ... No One</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/hd_dvd_vs_blu-ray_and_the_winner_is_no_one';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('HD DVD vs. Blu-ray: And the Winner is ... No One'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/articles/2006/08/hd_dvd_vs_blu-ray_and_the_winner_is_no_one.php";
		if ($author[img] != '' && 1 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HD DVD vs. Blu-ray: And the Winner is ... No One</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>August 16, 2006</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Technology">Technology</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2006/08/hd_dvd_vs_blu-ray_and_the_winner_is_no_one.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/articles/2006/08/hd_dvd_vs_blu-ray_and_the_winner_is_no_one.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/articles/2006/08/hd_dvd_vs_blu-ray_and_the_winner_is_no_one.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2006/08/hd_dvd_vs_blu-ray_and_the_winner_is_no_one.php&amp;phase=2&amp;title=HD%20DVD%20vs.%20Blu-ray%3A%20And%20the%20Winner%20is%20...%20No%20One&amp;bodytext=By%20now%2C%20I%27m%20sure%20you%27ve%20read%20a%20dozen%20articles%20like%20this%20one.%20In%20fact%2C%20I%20debated%20about%20whether%20or%20not%20to%20title%20it%20as%20such%2C%20as%20I%20was%20afraid%20most%20of%20you%20would%20skip%20it%20over.%20Obviously%20that%20is%20not%20the%20case%20if%20you%20are%20now%20reading%20these%20words.%0A%0AIf%20you%20are%20withholding%20your%20Next-Gen%20DVD%20player%20purchase%20until%20this%20so-called%20format%20war%20has%20a%20winner%2C%20it%20won%27t%20help%20...%20there%20likely%20won%27t%20be%20one%20...%20and%20there%20definitely%20won%27t%20be%20one%20any%20time%20soon.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p><img src="/images/helmets-100.gif" alt="HD DVD vs. Blu-ray" align="left">By now, I'm sure you've read a dozen articles like this one. In fact, I debated about whether or not to title it as such, as I was afraid most of you would skip it over. Obviously that is not the case if you are now reading these words.</p>

<p>If you are withholding your Next-Gen DVD player purchase until this so-called format war has a winner, it won't help ... there likely won't be one ... and there definitely won't be one any time soon.</p>

<p>Reading further, you will see how each is making equal progress on almost all fronts, and that both camps are investing heavily in their respective formats heading into the holiday buying season. This is not an in-depth analysis of the two formats, as I'm sure you've read too much about each already ... but rather is intended to be a summary of the "current state" of each of the standards.<br clear="all"></p>

<p><br />
<h2>HD DVD Status</h2></p>

<p><img src="/images/hddvd.png" alt="HD DVD" align="right">HD DVD has a market lead of about 2 months, they are half the price out of the gate, and all reports from the field are saying the quality is better than Blu-ray (at least from the titles/player available right now).</p>

<p>They also have Microsoft on board, who is coming out with an HD DVD add-on (~$200) for the Xbox 360 this fall ... which will speed adoption of the format.</p>

<p>And while they don't have as many production houses on board at the moment, I predict that many of the production houses in the Blu-ray camp (except for maybe Sony) will follow in footsteps of Warner Home Video and Paramount Pictures and begin producing in both formats once they see HD DVD leading the way into households this holiday season.</p>

<p>You should expect to see more than 200 titles available by the time the holiday buying season arrives. And now that they are through the "testing" phase, and have worked out some disc production kinks, you should also see more blockbuster-type films released.</p>

<p><br />
<h2>Blu-ray Status</h2></p>

<p><img src="/images/blu-ray.gif" alt="Blu-ray" align="right">Blu-ray is making great strides in penetrating the PC market, from a back-up media perspective. Both Sony and TDK are now shipping double-layer 50GB media, and TDK has prototyped 200GB media. The capacity advantage won't matter much for feature films, but the "TV on DVD" market will jump all over it.</p>

<p>Blu-ray also has many more manufacturers on board, which will lead to competition within the format itself, arguably resulting in better hardware.</p>

<p>And let's not forget about the Playstation 3, which is currently scheduled to arrive in the US on November 17th. In my opinion, that will have nearly the same penetrating effect as the Xbox drive will have for HD DVD, despite the PS3's $500-$600 price tag.</p>

<p><br />
<h2>Conclusion</h2></p>

<p>Both camps are so far invested, and are making equal headway both in terms of penetration and business partnerships that neither one will have any reason to "give in". And if you take into account the marketing dollars that will be spent through the next 6 months, that even further solidifies my position that we'll be living with both formats for a while to come ... and undoubtedly be reading another hundred articles like this one trying to predict the winner.. So if you're waiting for that, you'll be waiting a while.</p>

<p>I've outlined the key points in the table below for those that prefer a more visual representation:</p>

<table class="type1b">
	<tr>
		<td class="type1b_header">&nbsp;</td>
		<td class="type1b_header" style="text-align:center" nowrap>HD DVD</td>
		<td class="type1b_header" style="text-align:center">Blu-ray</td>
		<td class="type1b_header">Notes</td>
	</tr><tr>
		<td class="type1b_header" nowrap>Capacity</td>
		<td class="grid">&nbsp;</td>
		<td class="grid" style="text-align:center"><img src="/images/i_yes.gif" alt="check" style="padding:0"></td>
		<td class="grid">The predominant size for HD DVD is 30GB, while Blu-ray is 50GB. Granted, this applies only to burned media at present, but an advantage nonetheless.</td>
	</tr><tr>
		<td class="type1b_header" style="font-weight:bold" nowrap>Disc Production</td>
		<td class="grid" style="text-align:center"><img src="/images/i_yes.gif" alt="check" style="padding:0"></td>
		<td class="grid"></td>
		<td class="grid">Existing DVD production facilities can be converted to HD DVD more readily than to Blu-ray</td>
	</tr><tr>
		<td class="type1b_header" style="font-weight:bold" nowrap>Price</td>
		<td class="grid" style="text-align:center"><img src="/images/i_yes.gif" alt="check" style="padding:0"></td>
		<td class="grid"></td>
		<td class="grid">Blu-ray players are selling at about 100% premium over HD DVD</td>
	</tr><tr>
		<td class="type1b_header" style="font-weight:bold" nowrap>Sales</td>
		<td class="grid" style="text-align:center"><img src="/images/i_yes.gif" alt="check" style="padding:0"></td>
		<td class="grid" style="text-align:center"><img src="/images/i_yes.gif" alt="check" style="padding:0"></td>
		<td class="grid">Too early to tell. Comparing sales over the past 6 weeks, when both players were available, Blu-ray leads slightly in unit sales ... but HD DVD is gaining.</td>
	</tr><tr>
		<td class="type1b_header" style="font-weight:bold" nowrap>Movie Industry</td>
		<td class="grid" style="text-align:center"><img src="/images/i_yes.gif" alt="check" style="padding:0"></td>
		<td class="grid" style="text-align:center"><img src="/images/i_yes.gif" alt="check" style="padding:0"></td>
		<td class="grid">HD DVD currently has more titles available than does Blu-ray, but Blu-ray has more movie studios in their camp ... for the moment.</td>
	</tr><tr>
		<td class="type1b_header" style="font-weight:bold" nowrap>Gaming</td>
		<td class="grid" style="text-align:center"><img src="/images/i_yes.gif" alt="check" style="padding:0"></td>
		<td class="grid" style="text-align:center"><img src="/images/i_yes.gif" alt="check" style="padding:0"></td>
		<td class="grid">Historically, PS has been more popular than Xbox, but we'll have to wait another 6 months to see if the price differences has any effect on that.</td>
	</tr><tr>
		<td class="type1b_header" style="font-weight:bold" nowrap>PC Industry</td>
		<td class="grid"></td>
		<td class="grid" style="text-align:center"><img src="/images/i_yes.gif" alt="check" style="padding:0"></td>
		<td class="grid">HD DVD - Intel, Microsoft, HP, and Toshiba<br>Blu-ray - Apple, HP, Dell, Sony<br>Blu-ray is going to get the check-mark here simply due to their capacity and the fact that they are already deeply entrenched in the PC market</td>
	</tr><tr>
		<td class="type1b_header" style="font-weight:bold" nowrap>Timing</td>
		<td class="grid" style="text-align:center"><img src="/images/i_yes.gif" alt="check" style="padding:0"></td>
		<td class="grid"></td>
		<td class="grid">HD DVD has a 2-month advantage ... so they get the check-mark, for now</td>
	</tr><tr>
		<td class="type1b_header" style="font-weight:bold" nowrap>Marketing</td>
		<td class="grid" style="text-align:center"><img src="/images/i_yes.gif" alt="check" style="padding:0"></td>
		<td class="grid"></td>
		<td class="grid">HD DVD has announced a US marketing spend of $150M, but there are rumors of an impending $200M campaign by Sony to push Blu-ray</td>
	</tr>
</table>

<p><br />
<b>One final prediction:</b> It is more likely you will see a combo player, or combo media, before you will see either camp begin to pull ahead. While both Europe and Japan have these deeply staked out positions, Korea does not. Today manufacturers in Korea are designing and readying for market a combination player. The premium for doing so is said to be modest, although too early to know specifically.</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>August 16, 2006 11:43 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 426
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
 			<h2>More on Technology</h2><ul><?
 			$sql = "
 			SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name
 			FROM mt_entry e, mt_author a, mt_placement p, mt_category c
 			WHERE entry_author_id = author_id
 				AND e.entry_id = p.placement_entry_id
 				AND p.placement_category_id = c.category_id
 				AND category_label = 'Technology'
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
 				AND entry_id <> 426
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2006/08/hd_dvd_vs_blu-ray_and_the_winner_is_no_one.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
