<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 436";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 436 AND placement_is_primary = 1";
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
	<meta name="keywords" content="blu ray, ray disc, master audio, lossless master, home entertainment, Blu, blu, ray, disc, fox, Fox, releases, Disc, first, Lossless, lossless, audio, Audio, dts, master, director, home, DTS, Master, Director" />
	<meta name="description" content="ICE AGE: THE MELTDOWN Is First Day-and-Date Release on Blu-ray and DVD in North America, Australia and Europe.

Continuing its unwavering and exclusive support for the Blu-ray Disc format, Twentieth Century Fox Home Entertainment President Worldwide Mike Dunn announced today the Studio's first wave of highly-anticipated motion pictures to debut on Blu-ray Disc (BD), which is the only high-definition packaged media platform broadly supported by the film, music, gaming, computing and consumer electronics industries. Representing more than $2 billion in box office and 90 million DVD units sold worldwide, the studio's first eight BD releases are right on target with the BD early adopter and Playstation 3 purchaser. Taking full advantage of the next generation format's high definition technology ..." />
	<title>HDTV Magazine Bulletins - Twentieth Century Fox Home Entertainment Announces Its First Blu-ray Disc Releases</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/twentieth_century_fox_home_entertainment_announces_its_first_blu-ray_disc_releases';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Twentieth Century Fox Home Entertainment Announces Its First Blu-ray Disc Releases'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2006/08/twentieth_century_fox_home_entertainment_announces_its_first_blu-ray_disc_releases.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Twentieth Century Fox Home Entertainment Announces Its First Blu-ray Disc Releases</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>August 31, 2006</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=HD DVD & Blu-ray">HD DVD & Blu-ray</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2006/08/twentieth_century_fox_home_entertainment_announces_its_first_blu-ray_disc_releases.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2006/08/twentieth_century_fox_home_entertainment_announces_its_first_blu-ray_disc_releases.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2006/08/twentieth_century_fox_home_entertainment_announces_its_first_blu-ray_disc_releases.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2006/08/twentieth_century_fox_home_entertainment_announces_its_first_blu-ray_disc_releases.php&amp;phase=2&amp;title=Twentieth%20Century%20Fox%20Home%20Entertainment%20Announces%20Its%20First%20Blu-ray%20Disc%20Releases&amp;bodytext=ICE%20AGE%3A%20THE%20MELTDOWN%20Is%20First%20Day-and-Date%20Release%20on%20Blu-ray%20and%20DVD%20in%20North%20America%2C%20Australia%20and%20Europe.%0A%0AContinuing%20its%20unwavering%20and%20exclusive%20support%20for%20the%20Blu-ray%20Disc%20format%2C%20Twentieth%20Century%20Fox%20Home%20Entertainment%20President%20Worldwide%20Mike%20Dunn%20announced%20today%20the%20Studio%27s%20first%20wave%20of%20highly-anticipated%20motion%20pictures%20to%20debut%20on%20Blu-ray%20Disc%20%28BD%29%2C%20which%20is%20the%20only%20high-definition%20packaged%20media%20platform%20broadly%20supported%20by%20the%20film%2C%20music%2C%20gaming%2C%20computing%20and%20consumer%20electronics%20industries.%20Representing%20more%20than%20%242%20billion%20in%20box%20office%20and%2090%20million%20DVD%20units%20sold%20worldwide%2C%20the%20studio%27s%20first%20eight%20BD%20releases%20are%20right%20on%20target%20with%20the%20BD%20early%20adopter%20and%20Playstation%203%20purchaser.%20Taking%20full%20advantage%20of%20the%20next%20generation%20format%27s%20high%20definition%20technology%20...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p class="prtitle">Twentieth Century Fox Home Entertainment Announces Its First Blu-ray Disc Releases; Available Worldwide In November, Initial Slate Targets The Early Adopter</p>

<p>IFA 2006 - World of Consumer Electronics</p>

<p>BERLIN & LOS ANGELES--(BUSINESS WIRE)--Aug. 31, 2006--</p>

<p>ICE AGE: THE MELTDOWN Is First Day-and-Date Release on Blu-ray and DVD in North America, Australia and Europe 	</p>

<p>Continuing its unwavering and exclusive support for the Blu-ray Disc format, Twentieth Century Fox Home Entertainment President Worldwide Mike Dunn announced today the Studio's first wave of highly-anticipated motion pictures to debut on Blu-ray Disc (BD), which is the only high-definition packaged media platform broadly supported by the film, music, gaming, computing and consumer electronics industries. Representing more than $2 billion in box office and 90 million DVD units sold worldwide, the studio's first eight BD releases are right on target with the BD early adopter and Playstation 3 purchaser. Taking full advantage of the next generation format's high definition technology and advanced Java-based functionality, titles will be presented with the highest quality audiovisual elements including AVC (MPEG4 Compression) and HD "Lossless" Audio (select tracks) and many have unique interactive special features.</p>

<p>Fox's stellar, action-packed line-up of initial BD releases includes: BEHIND ENEMY LINES, FANTASTIC FOUR, KINGDOM OF HEAVEN (Director's Cut), KISS OF THE DRAGON, THE OMEN (666), THE LEAGUE OF EXTRAORDINARY GENTLEMEN, SPEED and THE TRANSPORTER. This first wave of BD titles will arrive at retail outlets worldwide in November. Japan launches November 10 and product hits North American (SRP US$39.98/CAN$49.98), Australian and European stores on November 14.</p>

<p>"Blu-ray is the superior high definition format and come this holiday season it will be evident that it is really the only choice for consumers who want to enjoy pre-recorded high-definition content at home. It'll be that simple," noted Dunn. "It fully delivers on the promise of a next generation format and Blu-ray represents the bright future of the $50 billion global home entertainment industry and the inevitable successor to the incredibly popular DVD."</p>

<p>Added Dunn, "For our consumers, this first wave of titles from Fox and the incredible special features on them is an early glimpse of what they can expect from us as we assume a leadership position in the high-definition packaged media market."</p>

<p>Of Fox's Blu-ray announcement, three-time Academy Award nominated Director Ridley Scott commented, "I reviewed my Director's Cut of KINGDOM OF HEAVEN, which is 3 hrs and 8 minutes thereabouts, on Blu-ray Disc and I was astounded. It was like looking through a window of clarity. It was the most impressive thing I've ever seen."</p>

<p>The Studio's first day-and-date release with DVD is the concurrent BD release of ICE AGE: THE MELTDOWN (known as ICE AGE 2 Internationally) on November 21 in North America and the week of November 13 in Australia and select European territories. This inaugural day-and-date release will benefit from the comprehensive, multi-million dollar marketing campaigns already in place for ICE AGE: THE MELTDOWN, which will tout the availability of the title on both DVD and BD.</p>

<p>The BD release of ICE AGE: THE MELTDOWN, the global $645 million box office behemoth and sequel to one of Fox's best-selling DVDs ever, is authored in HDMV and presented with DTS HD Lossless Master Audio and explodes with all-audience friendly HD bonus materials. Chief among the added features is the never-before-seen CGI short "No Time For Nuts" -- created exclusively for the DVD and BD releases -- featuring more antics by the nut-obsessed breakout character "Scrat," voiced by ICE AGE director Chris Wedge. The disc also includes a Director's commentary, "Crash and Eddie" Stunts -- three CGI short shorts, The Animation Director's Chair, Lost Historical Films on the Ice Age Period, "Scrat's Piranha Smackdown" Sound Effects Lab, "Crash And Eddie" Blooper, and much more.</p>

<p>Fox's commitment to emerging technologies is dedicated to enhancing the consumer experience of its products and providing for backward compatibility with their existing home entertainment libraries while also aggressively protecting its intellectual property from piracy. The Blu-ray companies fully embrace the Studio's steadfast commitment to the fight against piracy and the preservation of the integrity of its properties. Twentieth Century Fox is a member of the Board of Directors of the Blu-ray Disc Association.</p>

<p>Titles and disc configurations are detailed below:</p>

<p>-- BEHIND ENEMY LINES: Marked as one of the studio's first three BD-J releases, BEHIND ENEMY LINES features DTS HD Lossless Master Audio and MPEG 4 compression. The disc also includes commentaries by Director John Moore, Editor Martin Smith, and Producers John Davis and Wyck Godfrey, as well as selectable HD trailers of upcoming BD releases.</p>

<p>-- FANTASTIC FOUR: Presented with DTS HD Lossless Master Audio, the HDMV Blu-Ray Disc of FANTASTIC FOUR boasts commentaries by Ioan Gruffud, Jessica Alba, Chris Evans, Michael Chiklis and Julian McMahon, and selectable HD trailers of upcoming BD releases.</p>

<p>-- KINGDOM OF HEAVEN (Director's Cut): To accommodate the full 3 hour and 42 minute run time of Ridley Scott's Director's cut version of his epic masterpiece, KINGDOM OF HEAVEN is one of the industry's first dual-layer BD releases and is authored in HDMV presented with DTS HD Lossless Master Audio.</p>

<p>-- KISS OF THE DRAGON: Authored in HDMV with DTS HD Lossless Master Audio, KISS OF THE DRAGON includes commentaries by Chris Nash, Bridget Fonda, and Jet Li, as well as selectable HD trailers of upcoming BD releases.</p>

<p>-- THE LEAGUE OF EXTRAORDINARY GENTLEMEN: One of the industry's most advanced BD releases, THE LEAGUE OF EXTRAORDINARY GENTLEMEN is authored in BD-J with DTS HD Lossless Master Audio and AVC (MPEG 4 compression) and includes commentaries by the cast and crew, a unique search index which allows the viewer to sort scenes from the movie into 72 categories ranging from actor (e.g., Shane West, Sean Connery) to character (e.g., Allan Quarterman, Agent Tom Sawyer) to locations (e.g., Paris, Venice), among others. Additional features include an interactive first person shooter game boasting 12 unique play modes, up to 99 bookmarks, an animated pop-up trivia track, and HD trailers of upcoming BD releases.</p>

<p>-- THE OMEN (666): Authored in HDMV with DTS HD Lossless Master Audio, THE OMEN (666) includes commentary by John Moore, Glenn Williamson and Dan Zimmerman, two featurettes and two extended scenes plus a BD-exclusive animated pop-up trivia track entitled "The Devil's Footnotes," which explores the history of the triple sixes (666).</p>

<p>-- SPEED: This BD-J release boasts DTS HD Lossless Master Audio and MPEG 4 compression. Special features include commentary tracks and commentary chapter selections by Jan De Bont, Graham Yost, and Mark Gordon, as well as an animated pop-up trivia track, up to 99 bookmarks, a 56-category search index (see description on THE LEAGUE OF EXTRAORDINARY GENTLEMEN) and a java game entitled, Speed: Take Down, touting six game play modes. The title also includes HD trailers for upcoming BD releases.</p>

<p>-- THE TRANSPORTER: Authored in HDMV and presented with DTS HD Lossless Master Audio, THE TRANSPORTER Blu-Ray Disc features commentaries by Actor Jason Statham and Producer Steven Chasman, in addition to host of selectable HD trailers of upcoming BD releases.</p>

<p>Blu-ray Disc is a next generation optical disc format developed for high-definition video and high-capacity software applications. A single-layer Blu-ray Disc holds up to 25 gigabytes of data and a dual-layer Blu-ray Disc holds up to 50 gigabytes of data. This greater storage capacity enables the Blu-ray Disc to store over six times the amount of content than is possible with current DVDs, and is particularly well-suited for high definition feature films with extended levels of additional bonus and interactive material. Blu-ray also features the most advanced copy protection, backward compatibility with the current DVD format, connectivity and advanced interactivity.</p>

<p>A recognized global industry leader, Twentieth Century Fox Home Entertainment LLC is the worldwide marketing, sales and distribution company for all Fox film and television programming on VHS and DVD as well as video acquisitions and original productions. Each year the Company introduces hundreds of new and newly enhanced products, which it services to retail outlets -- from mass merchants and warehouse clubs to specialty stores and e-commerce -- throughout the world. Twentieth Century Fox Home Entertainment LLC is a subsidiary of Twentieth Century Fox Film Corporation, a News Corporation company.</p>

<p>Contacts<br />
Fox Home Entertainment<br />
Steve Feldstein, +310-369-5369<br />
North America<br />
or<br />
Marla Rothschild, +310-369-5827 / +818-730-8393<br />
International</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>August 31, 2006 01:51 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 436
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
 				AND entry_id <> 436
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2006/08/twentieth_century_fox_home_entertainment_announces_its_first_blu-ray_disc_releases.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
