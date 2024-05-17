<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 741";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 741 AND placement_is_primary = 1";
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
	<meta name="keywords" content="web enabled, dvd player, universal studios, home entertainment, evan almighty, DVD, dvd, universal, Universal, web, enabled, entertainment, experiences, movie, player, features, Studios, content, new, online, consumers, studios, shop, access, Entertainment" />
	<meta name="description" content="Today, Universal Studios Home Entertainment raised the bar on HD DVD's unique web-enabled experiences by unveiling an Internet infrastructure that allows it to take advantage of the guaranteed Internet connection in every HD DVD player and deliver compelling, title specific features. Heroes: Season 1 on HD DVD was the first title to take advantage of web-enabled capabilities, but with the forthcoming availability of Evan Almighty on HD DVD, Universal is setting the stage for new home entertainment experiences with web-enabled features such as the Download Center, U-Shop, and much more. Taking advantage of the infinite possibilities of web-enabled experiences, Universal is utilizing HD DVD to explore the promise of digitally distributed scenarios and infusing movie content with e-commerce and social media capabilities.

In addition to title-centric experiences available through any HD DVD player..." />
	<title>HDTV Magazine Bulletins - The Guaranteed Internet Connection in Every HD DVD Player Allows Movie-Specific Access to Content, Community Building and Online Purchases</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/the_guaranteed_internet_connection_in_every_hd_dvd_player_allows_movie-specific_access_to_content_community_building_and_online_purchases';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('The Guaranteed Internet Connection in Every HD DVD Player Allows Movie-Specific Access to Content, Community Building and Online Purchases'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2007/10/the_guaranteed_internet_connection_in_every_hd_dvd_player_allows_movie-specific_access_to_content_community_building_and_online_purchases.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">The Guaranteed Internet Connection in Every HD DVD Player Allows Movie-Specific Access to Content, Community Building and Online Purchases</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>October  3, 2007</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=HD DVD & Blu-ray">HD DVD & Blu-ray</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2007/10/the_guaranteed_internet_connection_in_every_hd_dvd_player_allows_movie-specific_access_to_content_community_building_and_online_purchases.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2007/10/the_guaranteed_internet_connection_in_every_hd_dvd_player_allows_movie-specific_access_to_content_community_building_and_online_purchases.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2007/10/the_guaranteed_internet_connection_in_every_hd_dvd_player_allows_movie-specific_access_to_content_community_building_and_online_purchases.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2007/10/the_guaranteed_internet_connection_in_every_hd_dvd_player_allows_movie-specific_access_to_content_community_building_and_online_purchases.php&amp;phase=2&amp;title=The%20Guaranteed%20Internet%20Connection%20in%20Every%20HD%20DVD%20Player%20Allows%20Movie-Specific%20Access%20to%20Content%2C%20Community%20Building%20and%20Online%20Purchases&amp;bodytext=Today%2C%20Universal%20Studios%20Home%20Entertainment%20raised%20the%20bar%20on%20HD%20DVD%27s%20unique%20web-enabled%20experiences%20by%20unveiling%20an%20Internet%20infrastructure%20that%20allows%20it%20to%20take%20advantage%20of%20the%20guaranteed%20Internet%20connection%20in%20every%20HD%20DVD%20player%20and%20deliver%20compelling%2C%20title%20specific%20features.%20Heroes%3A%20Season%201%20on%20HD%20DVD%20was%20the%20first%20title%20to%20take%20advantage%20of%20web-enabled%20capabilities%2C%20but%20with%20the%20forthcoming%20availability%20of%20Evan%20Almighty%20on%20HD%20DVD%2C%20Universal%20is%20setting%20the%20stage%20for%20new%20home%20entertainment%20experiences%20with%20web-enabled%20features%20such%20as%20the%20Download%20Center%2C%20U-Shop%2C%20and%20much%20more.%20Taking%20advantage%20of%20the%20infinite%20possibilities%20of%20web-enabled%20experiences%2C%20Universal%20is%20utilizing%20HD%20DVD%20to%20explore%20the%20promise%20of%20digitally%20distributed%20scenarios%20and%20infusing%20movie%20content%20with%20e-commerce%20and%20social%20media%20capabilities.%0A%0AIn%20addition%20to%20title-centric%20experiences%20available%20through%20any%20HD%20DVD%20player...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p class="prtitle">Universal Studios Home Entertainment Unveils Platform to Take HD DVD Web-Enabled Experiences to the Next Level</p>

<center><i>The Guaranteed Internet Connection in Every HD DVD Player Allows Movie-Specific Access to Content, Community Building and Online Purchases</i></center><br />
<br />

<p><B>UNIVERSAL CITY, Calif.--(BUSINESS WIRE)</B>--Today, Universal Studios Home Entertainment raised the bar on HD DVD's unique web-enabled experiences by unveiling an Internet infrastructure that allows it to take advantage of the guaranteed Internet connection in every HD DVD player and deliver compelling, title specific features. Heroes: Season 1 on HD DVD was the first title to take advantage of web-enabled capabilities, but with the forthcoming availability of Evan Almighty on HD DVD, Universal is setting the stage for new home entertainment experiences with web-enabled features such as the Download Center, U-Shop, and much more. Taking advantage of the infinite possibilities of web-enabled experiences, Universal is utilizing HD DVD to explore the promise of digitally distributed scenarios and infusing movie content with e-commerce and social media capabilities.</p>

<p>"HD DVD provides a consistent platform that allows us to go beyond DVD to deliver the best possible next generation HD experiences and begins to introduce consumers to the infinite potential of web-enabled content," said Craig Kornblau, President of Universal Studios Home Entertainment and Universal Pictures Digital Platforms. "With our web-enabled features, we wanted to deliver capabilities that compliment the HD movie watching experience while also offering a destination online that gives users the opportunity to dive deeper into their favorite movies and TV shows. We've just begun to explore HD DVD's potential and this infrastructure lays the foundation for us to easily evolve with consumer preferences."</p>

<p>In addition to title-centric experiences available through any HD DVD player, a new website, www.UniversalHiDef.com , will serve as an online destination for PC-based interaction, enabling cross platform consumer engagement with additional content, fan communities and much more. The site also establishes a cross-platform integration point to take advantage of the growing web properties already in place across NBC Universal.</p>

<p>This new infrastructure augments all the great features available through recent HD DVD releases, such as Heroes: Season 1, Knocked Up and the forthcoming Evan Almighty. Fans can download bonus scenes and make an online profile, or even add new, downloadable experiences long after a title leaves the store shelf. The interface allows consumers to register and create a personalized experience, while taking the first step toward participating in a fan community around their favorite movies or TV shows. Once registered, the account will be recognized by a consumer's player whenever a new web-enabled Universal disc is inserted. It can remember user preferences and multiple accounts with different passwords all registered on the same player.</p>

<p>The popularity and demand for such capabilities is evident with the recent release of Heroes Season 1 on HD DVD. Within the first two weeks following the release of the popular television series' seven-disc HD DVD set, nearly 40 percent of purchasers had registered an account in order to access unique connected features like the Heroes Ability Test and the Download Center.</p>

<p>One of most exciting new additions is the new U-Shop feature, an online store launching with Evan Almighty on HD DVD. By accessing U-Shop during the movie watching experience, consumers will be exposed to online purchase options related to the movie - from the latest featured fashions to movie-themed collectables.</p>

<p>The experience from your living room is also easy to use, designed with tailored, remote control friendly navigation tools to access information. An "above the fold" picture navigation offers pull-down menus and allows the consumer to easily descend into the subsequent pages.</p>

<p>An overview of some of the features coming to future Universal HD DVD titles and on UniversalHiDef.com includes:</p>

<p><br />
<B>U-Shop</B></p>

<p>The first functionality of U-Shop will be enabled with HD DVD release of Evan Almighty. With Universal U-Shop, viewers have the opportunity to shop for items from the movie right from their living room. Consumers can access the entire store from their HD DVD player and start purchasing exclusive items from titles that offer this feature.</p>

<p><br />
<B>Features/Download Center</B></p>

<p>Users will be able to scroll over numerous stills of the many interactive experiences that are already released on HD DVD. Web-enabled features allow consumers the opportunity to download exclusive trailers, content and games right to their HD DVD player, allowing one-click access to the Download Center. Consumers can also sign up for newsletters, sweepstakes and partner offers.</p>

<p><br />
<B>Best Buddies/My Scenes</B></p>

<p>Users will learn how create a Best Buddies list and collect and send their favorite movie clips to friends. Here, users will register their best buddies, who will be uploaded to the server and then accessible from their HD DVD player. Users can manage their buddy list using the HD DVD playback experience or via the PC to build a connected community of friends and movie fans.</p>

<p><br />
<B>Customer Support</B></p>

<p>The support section features instructions in the "screen grabs" format for easy step-by-step instructions. A list of error codes will also be provided to help identify any issues, along with links to customer support sites for Xbox and Toshiba are also available.</p>

<p>In addition to the current web-enabled titles of Heroes: Season 1, Knocked Up and soon Evan Almighty, experiences on Universal HD DVD titles and UniversalHiDef.com will continue to grow and evolve with the fan community, offering synched live events, user-generated content, user commentaries, 360 experiences, and more. The possibilities are endless with HD DVD's guaranteed web capabilities on every player regardless of make, model or price.</p>

<p><br />
<B>About HD DVD</B></p>

<p>HD DVD is the next generation, post-DVD standard for high capacity, high definition optical discs, approved by the DVD Forum, which develops and defines DVD formats. Its more than 220 strong membership brings together leaders in movies and entertainment, computing, consumer electronics and software. HD DVD is fast becoming the primary visual medium for the age of high-definition TV. The North American HD DVD Promotional Group, Inc. is an organization established to promote the HD DVD format and educate consumers in North America. For more information and a complete listing of HD DVD launch titles please visit http://www.TheLookAndSoundOfPerfect.com.</p>

<p><br />
<B>About Universal Studios Home Entertainment</B></p>

<p>Universal Studios Home Entertainment is a unit of Universal Pictures, a division of Universal Studios (www.universalstudios.com). Universal Studios is a part of NBC Universal, one of the world's leading media and entertainment companies in the development, production, and marketing of entertainment, news, and information to a global audience. Formed in May 2004 through the combining of NBC and Vivendi Universal Entertainment, NBC Universal owns and operates a valuable portfolio of news and entertainment networks, a premier motion picture company, significant television production operations, a leading television stations group, and world-renowned theme parks. NBC Universal is 80%-owned by General Electric, with 20% owned by Vivendi.</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>October  3, 2007 02:24 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 741
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
 				AND entry_id <> 741
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2007/10/the_guaranteed_internet_connection_in_every_hd_dvd_player_allows_movie-specific_access_to_content_community_building_and_online_purchases.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
