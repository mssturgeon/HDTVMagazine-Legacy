<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1280";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1280 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category[placement_category_id];
	
	# Still need this to track page views. Used in the Links unit in the footer
	$google_links_channel = $author[channel];

	# Set variables based on entry type. NOTE: Podcasts has its own entry template, so it is not included amongst the choices below.
	switch (10) {
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
	<meta name="keywords" content="fpd conference, flat panel, vice president, president marketing, picture quality, DisplaySearch, displaysearch, tvs, TVs, market, lcd, LCD, President, industry, president, FPD, fpd, hdtvs, HDTVs, picture, products, panel, content, figure, Vice" />
	<meta name="description" content="In 2007, the flat panel display industry reached the $101 billion mark, and it is expected to grow to $131 billion by 2012. While there are many display-centric applications on the rise such as notebooks, mobile applications, digital photo frames, home appliances and navigation systems, flat panel HDTVs continue to comprise 50% of all 10-inch and larger LCD panels shipped on an area basis-a segment that on a revenue basis, hit the $33.5 billion mark in 2007, $11 billion more than in 2006.

DisplaySearch predicts the cumulative number of HDTVs in the US will..." />
	<title>HDTV Magazine Columns - From the Industry - The Big Picture: DisplaySearch's Outlook on the HDTV Market</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/from_the_industry_-_the_big_picture_displaysearchs_outlook_on_the_hdtv_market';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('From the Industry - The Big Picture: DisplaySearch\'s Outlook on the HDTV Market'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/columns/2008/02/from_the_industry_-_the_big_picture_displaysearchs_outlook_on_the_hdtv_market.php";
		if ($author[img] != '' && 10 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">From the Industry - The Big Picture: DisplaySearch's Outlook on the HDTV Market</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>February 28, 2008</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Marketplace">Marketplace</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/columns/2008/02/from_the_industry_-_the_big_picture_displaysearchs_outlook_on_the_hdtv_market.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/columns/2008/02/from_the_industry_-_the_big_picture_displaysearchs_outlook_on_the_hdtv_market.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/columns/2008/02/from_the_industry_-_the_big_picture_displaysearchs_outlook_on_the_hdtv_market.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/columns/2008/02/from_the_industry_-_the_big_picture_displaysearchs_outlook_on_the_hdtv_market.php&amp;phase=2&amp;title=From%20the%20Industry%20-%20The%20Big%20Picture%3A%20DisplaySearch%27s%20Outlook%20on%20the%20HDTV%20Market&amp;bodytext=In%202007%2C%20the%20flat%20panel%20display%20industry%20reached%20the%20%24101%20billion%20mark%2C%20and%20it%20is%20expected%20to%20grow%20to%20%24131%20billion%20by%202012.%20While%20there%20are%20many%20display-centric%20applications%20on%20the%20rise%20such%20as%20notebooks%2C%20mobile%20applications%2C%20digital%20photo%20frames%2C%20home%20appliances%20and%20navigation%20systems%2C%20flat%20panel%20HDTVs%20continue%20to%20comprise%2050%25%20of%20all%2010-inch%20and%20larger%20LCD%20panels%20shipped%20on%20an%20area%20basis-a%20segment%20that%20on%20a%20revenue%20basis%2C%20hit%20the%20%2433.5%20billion%20mark%20in%202007%2C%20%2411%20billion%20more%20than%20in%202006.%0A%0ADisplaySearch%20predicts%20the%20cumulative%20number%20of%20HDTVs%20in%20the%20US%20will...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p class="editorial">On occasion, we receive articles submitted from others in the HDTV industry that we think you may be interested in. In the past, we have just published these to our "Articles" section, but we thought it would be more appropriate to place these in a new "From the Industry" column in our new "Columns" section.<br><br>Enjoy,<br>- Dale &amp; Shane  <p>By Paul Gagnon, Director North America TV Research, DisplaySearch  <p><img style="margin: 0px 15px 5px 0px" height="150" alt="clip_image001" src="http://www.hdtvmagazine.com/images/test/FromtheIndustryTheBigPictureDisplaySearc_984B/clip_image001.jpg" width="132" align="left" border="0">  <p>In 2007, the flat panel display industry reached the $101 billion mark, and it is expected to grow to $131 billion by 2012. While there are many display-centric applications on the rise such as notebooks, mobile applications, digital photo frames, home appliances and navigation systems, flat panel HDTVs continue to comprise 50% of all 10-inch and larger LCD panels shipped on an area basis-a segment that on a revenue basis, hit the $33.5 billion mark in 2007, $11 billion more than in 2006.  <p>DisplaySearch predicts the cumulative number of HDTVs in the US will more than triple from 51 million at the end of 2007 to 169 million at the end of 2011. Strong consumer demand for 1080p LCD TV products throughout 2007 also contributed to a strong year of revenue growth overall, despite sluggish consumer sales in the final weeks of 2007. This article will provide an overview on some of the key drivers that indicate the future is bright for both the LCD and HDTV industries.  <p>As set prices fall rapidly and picture quality improves continually, HDTVs are dominating the North American TV market. As shown in Figure 1, just 27% of TVs shipped during 2005 in North America were HD (defined as 720 lines of vertical resolution or more); in 2008 this figure is expected to jump to 94%. Figure 1 also reveals that TVs with 1080 progressive lines (1080p) have a bright outlook despite the fact that they sell for a substantial premium. The share of 1080p TVs in the 40-inch and above segment is expected to surge from less than 4% in 2005 to 64% in 2008, while selling for a 56% premium over 720p/1080i TVs. 1080p HDTVs are expected to reach an 84% share of 40-inch and larger TVs by 2011.  <p>Figure 1. HD and 1080p Unit Share Forecasts  <p><img style="margin: 0px 0px 5px 5px" height="201" alt="clip_image003" src="http://www.hdtvmagazine.com/images/test/FromtheIndustryTheBigPictureDisplaySearc_984B/clip_image003.gif" width="442" border="0">  <p>Why are 1080p TVs forecast to be so successful? Because 1080p delivers the best picture possible and is as close to "future proof" as consumers are going to get. In addition, it is the format best suited to both HD broadcast and packaged media, as most HD broadcasts in the US are 1080i (aside from ABC, ESPN and Fox) and 1080i images scale better to 1080p than 720p on fixed pixel displays like LCD and plasma. Moreover, PlayStation 3 and most next-generation DVD players able to output content at 1080p, allowing consumers to maximize picture quality from their HD content and hardware. Finally, 1080p offers the highest level of pixel density for a smoother image, particularly important at larger screen sizes, which can reveal the grid structure inherent in fixed pixel displays. Retailers, TV brands and LCD panel manufacturers all stand to profit from 1080p TVs, which offer improved profitability over 720p counterparts. With LCDs facing supply constraints, DisplaySearch believes it will become increasingly difficult to find 720p TVs over 40-inches in the US TV Market.  <p>As the number of HDTVs and the amount of HD programming continues to grow, HD service will inevitably surge, and also benefit from the anticipated end of analog broadcast in February 2009. Both content and service providers are working to increase the amount of HD content available, without driving up costs to the consumers, particularly important considering only about half of HDTVs are currently receiving HD service.  <p>At its 10<sup>th</sup> Annual US FPD Conference, DisplaySearch will feature a session on "The Maturing FPD TV Market and Global TV Outlook" discussing the implications for growth and profitability in a mature flat panel TV market, as well as industry drivers such as increased availability of HD content. Executives will also discuss the latest technology advancements that will fuel growth, especially in larger screen sizes. Sony's Dr. Randy Waynick, Senior Vice President of Marketing in the Home Products Division, will give a presentation on OLED TVs during the session, and DisplaySearch will also provide the latest update on the state of the Global TV Market. Speakers include:  <ul> <li>Paul Gagnon, Director, North American TV Research, DisplaySearch  <li>Hide Harada, President, Panasonic US  <li>Dr. Randy Waynick, Senior Vice President, Marketing, Home Products Division, Sony  <li>Scott Ramirez, Vice President Marketing, TV Group, Toshiba America Consumer Products LLP </li></ul> <p>In addition, the DisplaySearch US FPD Conference will also have a session on the "Quest for the Best Looking Picture at the Lowest Cost" examining the most important developments for improving picture quality and lowering costs in the flat panel TV market. Are today's products future-proofed or are there critical developments right around the corner? Sample topics include LED dimming, 120 Hz with motion estimation and motion compensation, RGBW color filters, a‑Si based AMOLEDs, color filter on array (COA) technology, and higher luminous efficiency plasma panels.  <ul> <li>Ross Young, President and Founder  <li>Brian H. Berkeley, Vice President, LCD Business, Samsung Electronics  <li>Guido Voltolina, Director, Image Technology, Dolby Laboratories  <li>Bruce Berkoff, Chairman, LCD TV Association </li></ul> <p>The <em><b><a href="http://guest.cvent.com/EVENTS/Info/Summary.aspx?e=aaf8136e-9236-493d-85bf-db575fb06f0e"><strong><u>DisplaySearch 10th Annual US FPD Conference</u></strong></a></b></em> will be held March 10-13, 2008 at the Hilton La Jolla Torrey Pines in La Jolla, California. The event will focus on all major and emerging FPD applications, as well as production equipment and key components and materials. It will boast two-and-a-half days of general sessions, many interactive sessions, three days of cutting-edge exhibits, and numerous networking opportunities Attendees will gain an instant return on investment by walking away with tens of thousands of dollars worth of market data and industry insight.  <p>Other presenting companies include AMD, AMIMON, AU Optronics, Dell, Dolby Laboratories, Genesis, Goldman Sachs, Intel, Kodak Display Business, NEC, Nokia, Samsung, Sony, Panasonic, Universal Display, Verizon and more.  <p>To register for the <strong><i><a href="http://guest.cvent.com/EVENTS/Info/Summary.aspx?e=aaf8136e-9236-493d-85bf-db575fb06f0e"><u>DisplaySearch 10th Annual US FPD Conference</u></a></i></strong> and see the full agenda, please follow <a href="http://guest.cvent.com/EVENTS/Info/Summary.aspx?e=aaf8136e-9236-493d-85bf-db575fb06f0e">this link</a>.</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>February 28, 2008 07:45 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 1280
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
			
 		<?if (10 <> 7) {
 			# Recent Articles by Author (exclude this one)
 			# Do not show recent articles for Bulletins.
 			$qry = "
 			SELECT entry_id, entry_blog_id, entry_created_on, entry_title, author_id, author_name
 			FROM mt_entry e, mt_author a
 			WHERE entry_blog_id = 10
 				AND entry_id <> 1280
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
 				<h2>About Columns</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/columns/2008/02/from_the_industry_-_the_big_picture_displaysearchs_outlook_on_the_hdtv_market.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
