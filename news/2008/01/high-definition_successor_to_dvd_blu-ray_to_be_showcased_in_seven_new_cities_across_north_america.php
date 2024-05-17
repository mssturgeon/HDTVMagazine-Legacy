<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 919";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 919 AND placement_is_primary = 1";
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
	<meta name="keywords" content="blu ray, home entertainment, high definition, ray disc, walt disney, Blu, blu, ray, Disney, disney, home, entertainment, panasonic, tour, Panasonic, disc, definition, high, Disc, Entertainment, Home, walt, studios, Tour, Walt" />
	<meta name="description" content="Following overwhelming consumer and media response of Blu-ray technology being the leading format of choice for home entertainment - Walt Disney Studios Home Entertainment and Panasonic Electronics today announce the official extension of Disney's Magical Blu-ray Tour to visit seven additional North American cities in 2008. The announcement was made by Bob Chapek, president of Walt Disney Studios Home Entertainment.

Disney's Magical Blu-ray Tour, sponsored by Panasonic, will continue to educate consumers across the country about..." />
	<title>HDTV Magazine Bulletins - High-Definition Successor to DVD (Blu-ray) to Be Showcased in Seven New Cities across North America</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/high-definition_successor_to_dvd_blu-ray_to_be_showcased_in_seven_new_cities_across_north_america';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('High-Definition Successor to DVD (Blu-ray) to Be Showcased in Seven New Cities across North America'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2008/01/high-definition_successor_to_dvd_blu-ray_to_be_showcased_in_seven_new_cities_across_north_america.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">High-Definition Successor to DVD (Blu-ray) to Be Showcased in Seven New Cities across North America</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>January 25, 2008</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=HD DVD & Blu-ray">HD DVD & Blu-ray</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2008/01/high-definition_successor_to_dvd_blu-ray_to_be_showcased_in_seven_new_cities_across_north_america.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2008/01/high-definition_successor_to_dvd_blu-ray_to_be_showcased_in_seven_new_cities_across_north_america.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2008/01/high-definition_successor_to_dvd_blu-ray_to_be_showcased_in_seven_new_cities_across_north_america.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2008/01/high-definition_successor_to_dvd_blu-ray_to_be_showcased_in_seven_new_cities_across_north_america.php&amp;phase=2&amp;title=High-Definition%20Successor%20to%20DVD%20%28Blu-ray%29%20to%20Be%20Showcased%20in%20Seven%20New%20Cities%20across%20North%20America&amp;bodytext=Following%20overwhelming%20consumer%20and%20media%20response%20of%20Blu-ray%20technology%20being%20the%20leading%20format%20of%20choice%20for%20home%20entertainment%20-%20Walt%20Disney%20Studios%20Home%20Entertainment%20and%20Panasonic%20Electronics%20today%20announce%20the%20official%20extension%20of%20Disney%27s%20Magical%20Blu-ray%20Tour%20to%20visit%20seven%20additional%20North%20American%20cities%20in%202008.%20The%20announcement%20was%20made%20by%20Bob%20Chapek%2C%20president%20of%20Walt%20Disney%20Studios%20Home%20Entertainment.%0A%0ADisney%27s%20Magical%20Blu-ray%20Tour%2C%20sponsored%20by%20Panasonic%2C%20will%20continue%20to%20educate%20consumers%20across%20the%20country%20about...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p class="prtitle">High-Definition Successor to DVD to Be Showcased in Seven New Cities across North America</p>

<center><i>Disney's Magical Blu-ray Tour Offers Mall-Going Consumers Special Opportunity to Check out Why Blu-ray is Leading Hi-Def Format of Choice</i></center><br />
<br />

<p><B>BURBANK, Calif.--(BUSINESS WIRE)</B>--Following overwhelming consumer and media response of Blu-ray technology being the leading format of choice for home entertainment - Walt Disney Studios Home Entertainment and Panasonic Electronics today announce the official extension of Disney's Magical Blu-ray Tour to visit seven additional North American cities in 2008. The announcement was made by Bob Chapek, president of Walt Disney Studios Home Entertainment.</p>

<p>Disney's Magical Blu-ray Tour, sponsored by Panasonic, will continue to educate consumers across the country about the leading, state-of-the-art high-definition home entertainment technology through hands-on interactive kiosks, product demonstrations, and a special presentation theater where consumers can experience first-hand the amazing capabilities of Blu-ray Hi-Def. The tour extension will commence in Toronto, Canada beginning January 24 and continue on to six additional locations across the nation.</p>

<p>"Last year, thousands of consumers in each market stopped by our Blu-ray Tour exhibit to better educate themselves on the Blu-ray format and its numerous technological advancements," stated Bob Chapek, president of Walt Disney Studios Home Entertainment. "The success of this interactive and educational vehicle helped clear up many misperceptions about high-definition in general. With that said, we are thrilled to be able to extend the tour into 2008."</p>

<p>Disney's 2008 Magical Blu-ray Tour will feature exciting first-look opportunities in each market including: previews of upcoming title releases, Finding Nemo and The Chronicles of Narnia: The Lion, The Witch and The Wardrobe; live demonstrations where consumers will get first-hand experience playing the interactive "Gusteau's Gourmet Game" from Ratatouille, "Car Finder Game" from Disney/Pixar's Cars, "Bowler Hat Barrage" from Meet The Robinsons; as well as experience a virtual tour of "Enter the Maelstrom" from Pirates of the Caribbean: At World's End. The Tour helps bring this to life via six interactive stations showcasing all aspects of the Blu-ray Disc technology and a special mini-theater that hosts hourly live presentations on Blu-ray high-definition technology.</p>

<p>"As a leading manufacturer of state-of-the-art home entertainment products, we are honored to be part of an exhibit that helps bring context to consumers who want to learn more about the functionality and advantages of Blu-ray," shared Eisuke Tsuyuzaki, vice president corporate development at Panasonic and general manager of its Blu-ray Disc Group. "By setting up demo stations using Panasonic VIERA HDTVs and Blu-ray players that feature exclusive Blu-ray Disc content, consumers can truly get a feel for what Blu-ray is all about and how it will work in their own home. It's an activity and technology the whole family will enjoy."</p>

<p><br />
<B>Mall Tour Schedule</B></p>

<p>Disney's Magical Blu-ray Tour, sponsored by Panasonic, will be open in all locations during regular mall hours from Friday - Sunday, with a special preview for the media on Friday mornings. The exhibits will be hosted by experts from both Disney and Panasonic, who will be available to answer questions about the technical aspects of Blu-ray and content featured on Disney Blu-ray Discs.</p>

<p>The first stop will be to Toronto, Canada the weekend of January 25th and continue on to Hartford, Raleigh-Durham, Nashville, Dallas, Denver and conclude with Chicago at Unity 2008 Conference in July.</p>

<pre>MALL				LOCATION	  	EVENT DATES
Yorkdale Shopping Center	Toronto, Canada 	January 25-27
Westfarms Mall			Farmington, CT 		February 15-18
Triangle Town Center		Raleigh, NC 		February 22-24
Cool Springs Galleria		Nashville, TN 		February 28 - March 1
North East Mall			Hurst, TX 		March 7-9
Flat Iron Crossing		Denver, CO 		March 14-16
UNITY 2008 Convention		Chicago, IL 		July 23-27</pre>

<p><br />
<B>Mall Tour Sweepstakes</B></p>

<p>Guests who visit Disney's Magical Blu-ray Tour can register for a chance to win one of the following three prizes: (1) Complete Hi-Def Home Entertainment Center - two winners will be drawn to win a complete Blu-ray Home Entertainment Package comprised of a Panasonic 50" HDTV, a Panasonic Blu-ray Disc player and a set of 10 Blu-ray Discs from Walt Disney Studios Home Entertainment; (2) Blu-ray Player & Movies - five winners will be drawn to win a Sony Playstation&reg;3 and a set of two Blu-ray Discs from Walt Disney Studios Home Entertainment; (3) Blu-ray Movies - ten winners will be drawn to win a set of five Blu-ray Discs from Walt Disney Studios Home Entertainment. No purchase necessary. Void where prohibited. Sweepstakes ends July 27th. Entries will be compiled from six markets, excluding Toronto, Canada, and winners will be selected in August of 2008. For information on the official rules, send a self addressed stamped envelope to: Blu-ray Magical Mall Tour Sweepstakes, Attn: Rules Request, P.O. Box 72382, Rockford, MN 55572.</p>

<p><br />
<B>About Blu-ray Disc</B></p>

<p>The Blu-ray Disc format is currently the leading high-definition packaged media supported by the foremost entertainment companies in film, music, gaming and computer industries. In the U.S., Blu-ray is continuing to outsell the competing format week-by-week, maintaining a 70 percent market share per week of all high-definition titles sold this year.</p>

<p>Blu-ray Disc is the next-generation optical disc format for high-definition video and high-capacity software applications. A single-layer Blu-ray Disc will hold up to 25GB of data, and a double-layer Blu-ray Disc will hold up to 50GB of data. A standard definition DVD holds only 10GB by comparison. Blu-ray Disc is supported by the world's leading consumer electronics, personal computer, gaming, music and film companies, and, with six of eight Hollywood studios releasing their high-definition content on Blu-ray Disc, Blu-ray will deliver the widest variety of high-definition, home entertainment content.</p>

<p>Blu-ray Discs deliver a truly unique high-definition viewing experience with a crystal-clear 1080p picture (the number "1080" represents 1,080 lines of vertical resolution, while the letter "p" stands for progressive scan) and up to an astounding 7.1 channel surround and 48 kHz, 24-bit uncompressed audio.</p>

<p><br />
<B>About Walt Disney Studios Home Entertainment</B></p>

<p>Walt Disney Studios Home Entertainment, a recognized leader in the home entertainment industry, is the marketing, sales and distribution company for Walt Disney, Touchstone, Hollywood Pictures, Miramax and Buena Vista product which includes DVD, Blu-ray Disc, and electronic distribution. Walt Disney Studios Home Entertainment is a division of The Walt Disney Studios.</p>

<p><br />
<B>About Panasonic Consumer Electronics Company</B></p>

<p>Based in Secaucus, N.J., Panasonic Consumer Electronics Company, a market and technology leader in high definition television, is a Division of Panasonic Corporation of North America, the principal North American subsidiary of Matsushita Electric Industrial Co. Ltd. (NYSE: MC) and the hub of Panasonic's U.S. marketing, sales, service and R&D operations. Information about Panasonic products is available at www.panasonic.com. Additional company information for journalists is available at www.panasonic.com/pressroom. </p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>January 25, 2008 01:40 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 919
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
 				AND entry_id <> 919
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2008/01/high-definition_successor_to_dvd_blu-ray_to_be_showcased_in_seven_new_cities_across_north_america.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
