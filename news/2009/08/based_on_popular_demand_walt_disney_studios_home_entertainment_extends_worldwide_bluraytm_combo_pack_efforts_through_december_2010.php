<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');

	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "$author[title]<br />";

	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3196 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get enclosure info
	$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3196";
	$res_enclosure = mQuery($sql);
	$row_enclosure = mysql_fetch_assoc($res_enclosure);
	$enclosure_url = $row_enclosure['enclosure_url'];

	# Still need this to track page views. Used in the Links unit in the footer
	$google_links_channel = $author['channel'];

	# Set variables based on entry type. NOTE: Podcasts (& soon Reviews) has its own entry template, so it is not included amongst the choices below.
	switch (7) {
		case 1: # Articles
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/hdtv-articles" />';
			$feed_tracker = '<script src="http://feeds.feedburner.com/~s/hdtv-articles?i=http://www.hdtvmagazine.com/news/2009/08/based_on_popular_demand_walt_disney_studios_home_entertainment_extends_worldwide_bluraytm_combo_pack_efforts_through_december_2010.php" type="text/javascript" charset="utf-8"></script>';
			$container = 'article_container';
			$sub_type = SUB_ARTICLES;
			$sub_label = 'Receive instant notification of new articles';
			if ($userdata['session_logged_in']) {
				$sub_desc = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of new HDTV Magazine Articles via email as soon as they are published.';
			} else {
				$sub_desc = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of new HDTV Magazine Articles via email as soon as they are published.';
			}
			break;
		case 4: # Interviews
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/hdtv-interviews" />';
			$feed_tracker = '<script src="http://feeds.feedburner.com/~s/hdtv-interviews?i=http://www.hdtvmagazine.com/news/2009/08/based_on_popular_demand_walt_disney_studios_home_entertainment_extends_worldwide_bluraytm_combo_pack_efforts_through_december_2010.php" type="text/javascript" charset="utf-8"></script>';
			break;
		case 5: # History
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
			$feed_tracker = '<script src="http://feeds.feedburner.com/~s/hdtv-archive?i=http://www.hdtvmagazine.com/news/2009/08/based_on_popular_demand_walt_disney_studios_home_entertainment_extends_worldwide_bluraytm_combo_pack_efforts_through_december_2010.php" type="text/javascript" charset="utf-8"></script>';
			break;
		case 6: # Test
			$container = 'article_container';
			$sub_type = 0;
			$sub_label = 'Receive instant notification of "Stuff"';
			if ($userdata['session_logged_in']) {
				$sub_desc = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of "Stuff" via email as soon as they are published.';
			} else {
				$sub_desc = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of "Stuff" via email as soon as they are published.';
			}
			break;
		case 7: # Bulletins
			$google_links_channel = ''; # Don't count bulletins
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/hdtv-news" />';
			$feed_tracker = '<script src="http://feeds.feedburner.com/~s/hdtv-news?i=http://www.hdtvmagazine.com/news/2009/08/based_on_popular_demand_walt_disney_studios_home_entertainment_extends_worldwide_bluraytm_combo_pack_efforts_through_december_2010.php" type="text/javascript" charset="utf-8"></script>';
			$container = 'bulletin_container';
			$sub_type = SUB_BULLETINS;
			$sub_label = 'Receive instant notification of HDTV Bulletins';
			if ($userdata['session_logged_in']) {
				$sub_desc = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of HDTV Bulletins via email as soon as they are published.';
			} else {
				$sub_desc = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of HDTV Bulletins via email as soon as they are published.';
			}
			break;
		case 8: # Reviews
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/hdtv-reviews" />';
			$feed_tracker = '<script src="http://feeds.feedburner.com/~s/hdtv-reviews?i=http://www.hdtvmagazine.com/news/2009/08/based_on_popular_demand_walt_disney_studios_home_entertainment_extends_worldwide_bluraytm_combo_pack_efforts_through_december_2010.php" type="text/javascript" charset="utf-8"></script>';
			$container = 'article_container';
			$sub_type = SUB_REVIEWS;
			$sub_label = 'Receive instant notification of new reviews';
			if ($userdata['session_logged_in']) {
				$sub_desc = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of new HDTV Magazine Reviews via email as soon as they are published.';
			} else {
				$sub_desc = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of new HDTV Magazine Reviews via email as soon as they are published.';
			}
			break;
		case 9: # Podcasts
			$container = 'article_container';
			$sub_type = SUB_PODCAST;
			$sub_label = 'Receive instant notification of new episodes';
			if ($userdata['session_logged_in']) {
				$sub_desc_logged_in = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of new episodes of The HDTV Podcast via email as soon as they are published.';
			} else {
				$sub_desc_anon = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of new episodes of The HDTV Podcast via email as soon as they are published.';
			}
			break;
		case 10: # Columns
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/hdtv-columns" />';
			$feed_tracker = '<script src="http://feeds.feedburner.com/~s/hdtv-columns?i=http://www.hdtvmagazine.com/news/2009/08/based_on_popular_demand_walt_disney_studios_home_entertainment_extends_worldwide_bluraytm_combo_pack_efforts_through_december_2010.php" type="text/javascript" charset="utf-8"></script>';
			$container = 'article_container';
			$sub_type = SUB_COLUMNS;
			$sub_label = 'Receive instant notification of new columns';
			if ($userdata['session_logged_in']) {
				$sub_desc = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of new HDTV Magazine Columns via email as soon as they are published.';
			} else {
				$sub_desc = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of new HDTV Magazine Columns via email as soon as they are published.';
			}
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
	<? require(BASE_DIR .'/includes/common_header.php'); ?>
	<meta name="keywords" content="blu ray, home entertainment, walt disney, combo pack, disney studios, blu, ray, disney, home, entertainment, combo, pack, walt, studios, dvd, consumers, value, disc, quality, worldwide, december, available, exceptional, single, titles" />
	<meta name="description" content="Based On Popular Demand: Walt Disney Studios Home Entertainment Extends Worldwide Blu-ray(TM) Combo Pack Efforts through December 2010 Exceptional Value, Quality &amp; Versatility Included In The Purchase of Any Disney-Branded Blu-ray Disc BURBANK, Calif., Aug. 12 /PRNewswire-FirstCall/ -- In a..." />
	<title>HDTV Magazine Bulletins - Based On Popular Demand: Walt Disney Studios Home Entertainment Extends Worldwide Blu-ray(TM) Combo Pack Efforts through December 2010</title>
	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		digg_url = 'http://www.hdtvmagazine.com/news/2009/08/based_on_popular_demand_walt_disney_studios_home_entertainment_extends_worldwide_bluraytm_combo_pack_efforts_through_december_2010.php';
		digg_skin = 'compact';
		digg_window = 'new';
		digg_title = 'Based On Popular Demand: Walt Disney Studios Home Entertainment Extends Worldwide Blu-ray(TM) Combo Pack Efforts through December 2010';
		digg_bodytext = 'Based On Popular Demand: Walt Disney Studios Home Entertainment Extends Worldwide Blu-ray(TM) Combo Pack Efforts through December 2010 Exceptional Value, Quality & Versatility Included In The Purchase of Any Disney-Branded Blu-ray Disc BURBANK, Calif., Aug. 12 /PRNewswire-FirstCall/ -- In a...';
//		digg_media = '';
		digg_topic = 'tech_news';

//		tweetmeme_url = 'http://yoururl.com';
		tweetmeme_style = 'compact';
		tweetmeme_source = 'HDTVMagazine';

		function fbs_click() {
			u=location.href;
			t=document.title;
			window.open('http://www.facebook.com/sharer.php?u='+encodeURIComponent(u)+'&amp;t='+encodeURIComponent(t),'sharer','toolbar=0,status=0,width=626,height=436');
			return false;
		}
	</script>
	<style>
		html .fb_share_link {padding:2px 0 0 20px; height:16px; background:url(http://b.static.ak.fbcdn.net/images/share/facebook_share_icon.gif?8:26981) no-repeat top left;}
		/*div.snap_preview div {display:none;}*/
		.header_buttons {text-align:right;}
		.header_buttons span {float:right; margin-right:20px;}
	</style>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/ads/leaderboard.php');
		include(BASE_DIR .'/includes/body_header.php');

		$base_url = strleftback(PHP_SELF, '/') . '/based_on_popular_demand_walt_disney_studios_home_entertainment_extends_worldwide_bluraytm_combo_pack_efforts_through_december_2010';
		$title_encoded = rawurlencode(addslashes('Based On Popular Demand: Walt Disney Studios Home Entertainment Extends Worldwide Blu-ray(TM) Combo Pack Efforts through December 2010'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2009/08/based_on_popular_demand_walt_disney_studios_home_entertainment_extends_worldwide_bluraytm_combo_pack_efforts_through_december_2010.php";
		if ($author['img'] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author['img'] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}

	if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']), "google") === false) {?>
		<!-- Article Header -->
		<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
			<tr>
				<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
				<td class="article_title" colspan="2">Based On Popular Demand: Walt Disney Studios Home Entertainment Extends Worldwide Blu-ray(TM) Combo Pack Efforts through December 2010</td>
			</tr><tr>
			<td id="article_byline" nowrap="nowrap">
					By <b>Shane Sturgeon</b><br />
					<?=$author_title?>
					Posted on <b>August 12, 2009</b><br />
					Category: <b><a href="/category.php?id=<?=$category_id?>&category="></a></b><br />
				</td><td id="article_links">
					<span><script type="text/javascript" src="http://w.sharethis.com/button/sharethis.js#publisher=3da06545-0753-46cb-8739-3ffcef208c1f&amp;type=website&amp;send_services=email&amp;post_services=facebook%2Cdigg%2Cdelicious%2Cstumbleupon%2Cblogger%2Cmyspace%2Cybuzz%2Creddit%2Ctechnorati%2Cmixx%2Cwordpress%2Ctypepad%2Cgoogle_bmarks%2Cwindows_live%2Cfark%2Cbus_exchange%2Cpropeller%2Cnewsvine%2Clinkedin"></script></span>
					<span><a href="http://www.facebook.com/share.php?u=<url>" onclick="return fbs_click()" target="_blank" class="fb_share_link">Facebook</a></span>
					<span><img src="http://cdn.stumble-upon.com/images/16x16_su_3d.gif" alt="" align="absmiddle" /><a target="_blank" href="http://www.stumbleupon.com/submit?url=http://www.hdtvmagazine.com/news/2009/08/based_on_popular_demand_walt_disney_studios_home_entertainment_extends_worldwide_bluraytm_combo_pack_efforts_through_december_2010.php&title=Based On Popular Demand: Walt Disney Studios Home Entertainment Extends Worldwide Blu-ray(TM) Combo Pack Efforts through December 2010">StumbleUpon</a></span>
					<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2009/08/based_on_popular_demand_walt_disney_studios_home_entertainment_extends_worldwide_bluraytm_combo_pack_efforts_through_december_2010.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
					<span><img src="/images/save.gif" alt="Save Article" align="absmiddle" /><a target="_blank" href="<?=$base_url?>-save.php">Save</a></span>
					<span><img src="/images/email.gif" alt="Email Article" align="absmiddle" /><a href="<?=$email_url?>">Email</a></span>
					<span><img src="/images/print.png" alt="Print Article" align="absmiddle" /><a target="_blank" href="<?=$base_url?>-print.php">Print</a></span><br />
					<br /><br />
				</td>
			</tr>
		</table>
	<?}?>
	<div>
		<div id="right" style="float:right; margin:0 0 5px 5px; text-align:center;" align="center">
			<? include(BASE_DIR .'/ads/mrectangle.php');?>
			<br />
			<div align="center">
				<? include(BASE_DIR .'/ads/skyscraper.php');?>
			</div>
		</div>
		<div id="<?=$container?>">
			<? if ($sub_type > 0 && ($userdata['subscriptions'] & $sub_type)) {} else {?>
				<div class="important" style="display:table"><span class="corners-top"><span></span></span>
					<img src="/images/i_inbox.gif" align="left" style="float:left; padding-right:10px" />
					<span class="label"><?=$sub_label?>:</span>
					<?=$sub_desc?>
				<span class="corners-bottom"><span></span></span></div>
			<? }?>
			<div class="header_buttons">
				<? if (7 == 9 || 7 == 6) { # Only show in the test area and for podcasts?>
					<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle"></a></span>
					<span><a href="<?=$enclosure_url?>"><img src="/images/chicklet-mp3-podcast.gif" alt="Download Based On Popular Demand: Walt Disney Studios Home Entertainment Extends Worldwide Blu-ray(TM) Combo Pack Efforts through December 2010" /></a></span>
				<? }?>
				<span><script src="http://tweetmeme.com/i/scripts/button.js"></script></span>
				<span><script src="http://digg.com/api/diggthis.js"></script></span>
				<br />
			</div><br />
			<p class="prtitle">Based On Popular Demand: Walt Disney Studios Home Entertainment Extends Worldwide Blu-ray(TM) Combo Pack Efforts through December 2010</p>

<center><i>Exceptional Value, Quality & Versatility Included In The Purchase of Any Disney-Branded Blu-ray Disc</center></i><br />
<br />

<p><strong>BURBANK, Calif., Aug. 12 /PRNewswire-FirstCall/ -- </strong>In a time when price, quality and value are top of mind with consumers, Walt Disney Studios Home Entertainment (WDSHE) today announces the extension of it's Blu-ray Combo Pack initiatives worldwide, providing extended utility of DVD and exceptional quality of a Blu-ray Disc in a single value-pack. The Combo Packs will now be available across all Disney-branded Blu-ray titles releasing through December of 2010.</p>

<p>In a recent online survey* of US home entertainment consumers conducted by WDSHE and an independent market research company, Lieberman Research Worldwide showed that the value of the Combo Pack is undeniable for consumers. More than 77% consumers that intend to embrace Blu-ray technology in the near future agree that Combo Packs are a better value than purchasing a DVD or Blu-ray alone.</p>

<p>"Since introducing the industry's first Blu-ray Combo Pack in Fall of 2008, we've seen tremendous consumer interest towards the purchase of product that come fully-loaded with extras that fit into their budgets, desires and lifestyles," commented Bob Chapek, President of Walt Disney Studios Home Entertainment. "The Disney Blu-ray Combo Pack is the perfect marriage of value, quality and versatility all wrapped-up in a single package."</p>

<p>Some of the upcoming Combo Pack titles families can look forward to include an exciting new slate of Disney-branded theatrical, Diamond and Video Premiere titles, such as Hannah Montana The Movie, Snow White and the Seven Dwarfs, Tinker Bell and the Lost Treasure, UP and Santa Buddies.</p>

<p>WDSHE first pioneered the Combo Pack - a Blu-ray Disc plus a standard definition DVD in a single package - with the Blu-ray release of Sleeping Beauty in October of 2008 to, at the time, allow consumers without Blu-ray players the opportunity to "future proof" their collections. Today, you can find two different models of the Disney Blu-ray Combo Pack on shelf: (1) A Blu-ray + DVD and/or (2) A Blu-ray + DVD + DisneyFile Digital Copy of the movie. Additional Blu-ray Combo Packs current available in the marketplace include Race To Witch Mountain, Bedtime Stories, Bolt, Pinocchio and High School Musical 3: Senior Year.</p>

<p>In addition to the exceptional picture, sound and hours of extra bonus features provided on all Disney Blu-ray Discs, consumers can also take advantage of watching their favorite films across multiple formats of choice. For example, The Blu-ray Disc can be used at home to maximize high definition home entertainment systems, the DVD in the car, and the Digital Copy on a PC or mobile device.</p>

<p><br />
<strong>About Walt Disney Studios Home Entertainment</strong></p>

<p>Walt Disney Studios Home Entertainment, a recognized leader in the home entertainment industry, is the marketing, sales and distribution company for Walt Disney, Touchstone, Hollywood Pictures, Miramax and Buena Vista product which includes DVD, Blu-ray Disc, and electronic distribution. Walt Disney Studios Home Entertainment is a division of The Walt Disney Studios.</p>

<p>These press materials are available in electronic form at <a target="_blank" href="http://www.WDSHEpublicity.com/">www.WDSHEpublicity.com</a>.</p>

<p>* Survey conducted in July 2009, among a large (n=1,642), randomly selected sample representative of the US home entertainment households that intend to adopt BD technology in the next 3-6 months.</p>

<p>Source: Walt Disney Studios Home Entertainment</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>August 12, 2009  8:18 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 3196
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
 			<h2>More on </h2><ul><?
 			$sql = "
 			SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name
 			FROM mt_entry e, mt_author a, mt_placement p, mt_category c
 			WHERE entry_author_id = author_id
 				AND e.entry_id = p.placement_entry_id
 				AND p.placement_category_id = c.category_id
 				AND category_label = ''
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

 		<? if (7 <> 7) {
 			# Recent Articles by Author (exclude this one)
 			# Do not show recent articles for Bulletins.
 			$qry = "
 			SELECT entry_id, entry_blog_id, entry_created_on, entry_title, author_id, author_name
 			FROM mt_entry e, mt_author a
 			WHERE entry_blog_id = 7
 				AND entry_id <> 3196
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2009/08/based_on_popular_demand_walt_disney_studios_home_entertainment_extends_worldwide_bluraytm_combo_pack_efforts_through_december_2010.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
