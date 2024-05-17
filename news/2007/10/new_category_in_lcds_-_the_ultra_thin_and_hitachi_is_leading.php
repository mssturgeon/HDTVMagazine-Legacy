<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 760";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Dale Cripps'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 760 AND placement_is_primary = 1";
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
	<meta name="keywords" content="ultra thin, thin displays, new ultra, new category, consumers want, hitachi, Hitachi, thin, new, ultra, Thin, displays, Ultra, consumers, Displays, picture, frame, high, HDTV, market, sound, frames, features, our, line" />
	<meta name="description" content="The HDTV revolution put the display industry into frenetic motion. Out of this phenomena sprang new departures in technology which promise to keep the &quot;WOW&quot; in HDTV for years to come. Hitachi sent us this &quot;WOW&quot; announcement (below) today. Watch for a luxury grade of new Ultra Thin flat panels as soon as next year at a retail outlet near you. _Dale

Available in 2008, Hitachi's Chic New HDTVs Mark the Debut of an Entirely New Category of Display

TOKYO - October 23, 2007 - Hitachi has achieved yet another consumer electronics breakthrough with today's announcement of its new line of 1.5-inch (35mm) Ultra Thin HDTV's. Hitachi also expects to be first to market with its new Ultra Thin series, which will be available in the Japan market in December of 2007. U.S. consumers can expect to see Ultra Thin models in early 2008 - many months before thin displays from other manufacturers. 

These slim, stylish LCDs from Hitachi represent an important new..." />
	<title>HDTV Magazine Bulletins - New Category in LCDs - The "Ultra Thin" and Hitachi Is Leading</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/new_category_in_lcds_-_the_ultra_thin_and_hitachi_is_leading';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('New Category in LCDs - The "Ultra Thin" and Hitachi Is Leading'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2007/10/new_category_in_lcds_-_the_ultra_thin_and_hitachi_is_leading.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Dale Cripps" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">New Category in LCDs - The "Ultra Thin" and Hitachi Is Leading</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Dale Cripps</b><br />
				<?=$author_title?>
				Posted on <b>October 23, 2007</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Marketplace">Marketplace</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2007/10/new_category_in_lcds_-_the_ultra_thin_and_hitachi_is_leading.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2007/10/new_category_in_lcds_-_the_ultra_thin_and_hitachi_is_leading.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2007/10/new_category_in_lcds_-_the_ultra_thin_and_hitachi_is_leading.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2007/10/new_category_in_lcds_-_the_ultra_thin_and_hitachi_is_leading.php&amp;phase=2&amp;title=New%20Category%20in%20LCDs%20-%20The%20%22Ultra%20Thin%22%20and%20Hitachi%20Is%20Leading&amp;bodytext=The%20HDTV%20revolution%20put%20the%20display%20industry%20into%20frenetic%20motion.%20Out%20of%20this%20phenomena%20sprang%20new%20departures%20in%20technology%20which%20promise%20to%20keep%20the%20%22WOW%22%20in%20HDTV%20for%20years%20to%20come.%20Hitachi%20sent%20us%20this%20%22WOW%22%20announcement%20%28below%29%20today.%20Watch%20for%20a%20luxury%20grade%20of%20new%20Ultra%20Thin%20flat%20panels%20as%20soon%20as%20next%20year%20at%20a%20retail%20outlet%20near%20you.%20_Dale%0A%0AAvailable%20in%202008%2C%20Hitachi%27s%20Chic%20New%20HDTVs%20Mark%20the%20Debut%20of%20an%20Entirely%20New%20Category%20of%20Display%0A%0ATOKYO%20-%20October%2023%2C%202007%20-%20Hitachi%20has%20achieved%20yet%20another%20consumer%20electronics%20breakthrough%20with%20today%27s%20announcement%20of%20its%20new%20line%20of%201.5-inch%20%2835mm%29%20Ultra%20Thin%20HDTV%27s.%20Hitachi%20also%20expects%20to%20be%20first%20to%20market%20with%20its%20new%20Ultra%20Thin%20series%2C%20which%20will%20be%20available%20in%20the%20Japan%20market%20in%20December%20of%202007.%20U.S.%20consumers%20can%20expect%20to%20see%20Ultra%20Thin%20models%20in%20early%202008%20-%20many%20months%20before%20thin%20displays%20from%20other%20manufacturers.%20%0A%0AThese%20slim%2C%20stylish%20LCDs%20from%20Hitachi%20represent%20an%20important%20new...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p class="editorial">The HDTV revolution put the display industry into frenetic motion. Out of this phenomena sprang new departures in technology which promise to keep the "WOW" in HDTV for years to come. Hitachi sent us this "WOW" announcement (below) today. Watch for a luxury grade of new Ultra Thin flat panels as soon as next year at a retail outlet near you. _Dale</p>

<p><br />
<p class="prtitle">HITACHI LAUNCHES NEW LINE OF ULTRA THIN DISPLAYS</p></p>

<center><i>Available in 2008, Hitachi's Chic New HDTVs Mark the Debut of an Entirely New Category of Display</i></center><br />
<br />

<p><b>TOKYO - October 23, 2007</b> - Hitachi has achieved yet another consumer electronics breakthrough with today's announcement of its new line of 1.5-inch (35mm) Ultra Thin HDTV's. Hitachi also expects to be first to market with its new Ultra Thin series, which will be available in the Japan market in December of 2007. U.S. consumers can expect to see Ultra Thin models in early 2008 - many months before thin displays from other manufacturers. </p>

<p>These slim, stylish LCDs from Hitachi represent an important new category of product that is being called Ultra Thin Displays. This new type of display is designed for a highly affluent and refined segment of consumers who seek luxury, style and prestige. This extremely discerning audience also demands a set of features, technologies and design aesthetics that are separate and very distinct from those found in today's traditional Flat Panel Displays. </p>

<p>"As very large consumer markets grow and evolve, sub-segments with particular nuances will emerge," said Daniel Lee, vice president of marketing for Hitachi America, Ltd., Ubiquitous Platform Systems Division. "This is precisely what we're seeing in the HDTV market and our new designs are at the forefront of this shift. What's happening is that the more traditional Flat Panel Displays will continue to focus on 'bigger is better.' Hitachi knows this segment very well, and we have for years held a leadership position with our Director's Series plasmas. But our research shows a new trend emerging: consumers want access to information and entertainment throughout the home. This is the promise behind the 'networked' and 'digital' home. And it's also what's behind the emergence of these new Ultra Thin Displays from Hitachi, which are very thin, versatile, lightweight and stylish and can elegantly be placed in any room or multiple rooms throughout the home. At Hitachi, we will be tailoring our engineering product development and overall go-to-market strategy to address this important and exciting market dynamic."</p>

<p>Initially offered in three sizes (32", 37" and 42"), the displays are designed to provide consumers with a range of options for placement throughout the home. Hitachi's research shows consumers want Ultra Thin Displays to be more discreet, flexible, modern and sleek, since they will often be purchased for a kitchen, bathroom, office or bedroom.</p>

<p>Ultra Thin, Yet Feature Rich <br />
"Hitachi understands that when selecting an Ultra Thin Display, consumers want a very modern, thin profile and a lightweight unit but they do not want to trade off any of the features or performance of a top-of-the-line HDTV," said Bill Whalen, director of product development for Hitachi America, Ltd., Ubiquitous Platform Systems Division. "The Ultra Thin Displays from Hitachi pack style and performance into a sleek, compact form factor that makes absolutely no compromises when it comes to innovative technologies, groundbreaking features, theater-quality image optimization, state-of-the-art electronics and wall-shaking sound. Typical of Hitachi's complete line of products, these new displays perform at the top of their class." The new Ultra Thin Displays embody the following features:</p>

<p>External Electrode Fluorescent Lamp (EEFL) - The thinness of the displays was achieved through Hitachi's proprietary implementation of a technology called EEFL, which affords greater power efficiency, delivers better and more flexible color accuracy and delivers a longer overall life span for the display.</p>

<p>Wide Viewing Angle - By implementing In Plane Switching (IPS), Hitachi has achieved the sharpest, clearest LCD possible, regardless of the angle at which the viewer is seated. A vertical and horizontal viewing angle of 178 degrees maintains natural colors and brightness, making it ideal for watching TV with the whole family and friends.</p>

<p>Hitachi's Proprietary "Anti-Judder" Technique (37" & 42") - Movies provide the illusion of motion by showing a series of still images over time. In fact, all Hollywood movies flash 24 individual images each second. However, Hollywood's 24 frames-per-second do not match our television systems, which show 60 frames each second. A conversion technique called "3:2 pull-down correction" is used to make the 24 frames of film fit the television's faster 60 frames. As this conversion is done, the viewer can often observe a jerky, troublesome visual effect that is called "judder." It appears as if the image is jittery or stuttering and is especially noticeable when the picture pans or makes sweeping, side-to-side movements. In its new Ultra Thin line, Hitachi has implemented proprietary technology which accurately and automatically eliminates the jerky "judder" motion. It does so by creating interpolated frames based on the original film images. It smoothes out the movement and correctly matches the motion of the original movie.</p>

<p>Picture Master Full HD -- Hitachi's enhanced high-resolution image processing engine, Picture Master Full HD, analyzes and processes image at a high speed, providing state-of-the-art high picture quality. It achieves this in three ways:</p>

<p>Advanced Dynamic Contrast -- analyzes every picture that appears on the screen and optimizes its contrast frame by frame. <br />
3D Color Management -- adjusts the three constituent components of color (hue, saturation, and brightness) pixel by pixel using 3D data. <br />
Advanced Dynamic Enhancer -- expresses images which are simultaneously detailed and dynamic, and controls detail gradation and sharp edges. In addition, Hitachi added a circuit which enhances the crispness in scenes to capture subtle details, such as details in human skin or a three-dimensional expression of mountain ridge, which reduces the grainy effect and pulls out the natural beauty. <br />
High Audio Quality -- The 6.0 watt + 6.0 watt digital amplifier produces an exceptionally clear sound. The speakers located at the left and right sides of the bottom of the monitor are Hitachi's newly developed box-type speakers which are high quality and slim enough to fit the 35mm thickness of the monitor. The three sound modes allow consumers to select the most suitable type of audio effect depending on the contents - "clear voice" to pick up human voices clearly, "surround" for natural, clear three-dimensional sound, and "bass boost" for the optimum bass sound depending on the input signal. </p>

<p>Highly Refined and Energy Efficient Design <br />
A glossy and high precision frame surrounds the picture area of each of the displays. The frame is beveled to present the viewing picture as if it were a work of art. The combination of a bevel on the outer frame combined with a radius on the inner frame presents a visually unique addition to any living environment. Not only are the new Ultra Thin Displays gorgeous in their appearance, they are responsible and sustainable in their design due to Hitachi's energy-efficient features such as "auto power off," to prevent consumers from forgetting to turn off the TV. The UT series also feature a "video power save" which allows consumers to switch to stand-by mode when there is no signal coming into the TV. </p>

<p>Resolution and Expected Availability </p>

<p>Screen Size     Resolution      Introduction   <br />
32"     1366x768        Q1 '08 <br />
37"     1920x1080       Q2 '08 <br />
42"     1920x1080       Q2 '08 </p>
		</div>
	</div>
	<p class="posted">Posted by <b>Dale Cripps</b>, <b>October 23, 2007 11:36 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 760
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
 				AND entry_id <> 760
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2007/10/new_category_in_lcds_-_the_ultra_thin_and_hitachi_is_leading.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
