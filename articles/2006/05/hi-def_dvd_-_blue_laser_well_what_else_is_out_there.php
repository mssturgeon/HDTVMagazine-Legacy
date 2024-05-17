<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 371";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Rodolfo La Maestra'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 371 AND placement_is_primary = 1";
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
	<meta name="keywords" content="def dvd, blu ray, blue laser, well else, evd player, DVD, dvd, format, EVD, evd, def, market, chinese, back, Chinese, ces, CES, could, time, Blu, blu, ray, product, content, price" />
	<meta name="description" content="Although I have been following the Hi-Def DVD development since 1996 when DVD was introduced, it was since January 2002 that I have started to provide details on my reports and articles about the several hi-def DVD formats.  Not only about Blu-ray and HD DVD that are now at market war trying to attract the consumer in the US, but also about the Chinese DVD HD video industry, which has now 4 formats (and we thought 2 were more than we needed)." />
	<title>HDTV Magazine Articles - Hi-Def DVD? - Blue laser?  Well, what else is out there?</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/hi-def_dvd_-_blue_laser_well_what_else_is_out_there';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Hi-Def DVD? - Blue laser?  Well, what else is out there?'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/articles/2006/05/hi-def_dvd_-_blue_laser_well_what_else_is_out_there.php";
		if ($author[img] != '' && 1 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Rodolfo La Maestra" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Hi-Def DVD? - Blue laser?  Well, what else is out there?</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Rodolfo La Maestra</b><br />
				<?=$author_title?>
				Posted on <b>May  8, 2006</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Technology">Technology</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2006/05/hi-def_dvd_-_blue_laser_well_what_else_is_out_there.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/articles/2006/05/hi-def_dvd_-_blue_laser_well_what_else_is_out_there.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/articles/2006/05/hi-def_dvd_-_blue_laser_well_what_else_is_out_there.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2006/05/hi-def_dvd_-_blue_laser_well_what_else_is_out_there.php&amp;phase=2&amp;title=Hi-Def%20DVD%3F%20-%20Blue%20laser%3F%20%20Well%2C%20what%20else%20is%20out%20there%3F&amp;bodytext=Although%20I%20have%20been%20following%20the%20Hi-Def%20DVD%20development%20since%201996%20when%20DVD%20was%20introduced%2C%20it%20was%20since%20January%202002%20that%20I%20have%20started%20to%20provide%20details%20on%20my%20reports%20and%20articles%20about%20the%20several%20hi-def%20DVD%20formats.%20%20Not%20only%20about%20Blu-ray%20and%20HD%20DVD%20that%20are%20now%20at%20market%20war%20trying%20to%20attract%20the%20consumer%20in%20the%20US%2C%20but%20also%20about%20the%20Chinese%20DVD%20HD%20video%20industry%2C%20which%20has%20now%204%20formats%20%28and%20we%20thought%202%20were%20more%20than%20we%20needed%29.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p><b><center>Hi-Def DVD? - Blue laser?  Well, what else is out there?</p>

<p>Part I</b></center></p>

<p>Although I have been following the Hi-Def DVD development since 1996 when DVD was introduced, it was since January 2002 that I have started to provide details on my reports and articles about the several hi-def DVD formats.  Not only about Blu-ray and HD DVD that are now at market war trying to attract the consumer in the US, but also about the Chinese DVD HD video industry, which has now 4 formats (and we thought 2 were more than we needed).  </p>

<p>I will cover the subject in several articles, including a meeting with the companies behind the new FVD HD format from Taiwan (full details in my 2006 report). </p>

<p>To start with, picture yourself for a few minutes back into an era of continuous appearances of just mockups and black-box prototypes of Hi-def DVD technology at every CES over the past 7 years.  </p>

<p>Imagine HD-DVD as AOD (original name) and the restructuring of the DVD-Forum to gain the forum approval of the format.  Imagine Blu-ray branching out with a separate BD Association claiming they do not need DVD Forum approval for their format and gaining dozens of major companies as supporters.  </p>

<p>In other words, we did not need the Chinese alternatives to make this format war more complicated than it is.  Well, everything about HD is complicated, look for my other articles and you will see why (Is HDTV Complex Enough? is one).   </p>

<p>Let us do a short recap of the background before you run for that Tylenol.  </p>

<p><u><b>Back in January 2002, I wrote:</u></b></p>

<p>"Pioneer and LGE/Zenith have shown similar HD-DVD (before HD-DVD and BD split) prototype units on two CES shows in a row.  Panasonic introduced their unit for their first time at CES.</p>

<p>According to LGE, their unit uses a different coding/error correction than the Panasonic prototype, which makes the disk incompatible if media needs to be exchanged.  It seems that these HD-DVD prototype units could be subjected to the same multiple format competition of the regular DVD-recorders mentioned earlier (-RW, +RW and RAM).</p>

<p>Perhaps by the time the motion picture industry would allow them to be commercially available, the format war of DVD recorders might be over, and hopefully these HD units would have then a single de-facto format.  (Note in 2006: what a dreamer!)</p>

<p>The LGE/Zenith has provided detailed information about their unit on both years, and claimed that it could possibly include a HD-PVR and guides in the future.  Pioneer's information about their unit has been very restricted over the same period."</p>

<p><br />
<u><b>Back in January 2003, I wrote:</u></b></p>

<p>(Pages 66 to 68 of the 2003 report, free from this magazine).<br />
 <br />
"The Hi-def DVD technology has been demonstrated for its third year at CES, still as prototypes and mockups, but now from a wider variety of manufacturers.  This year there was some progress; a proposal of the HD-DVD system standard was submitted, and several industry meetings (with Hollywood) were held.  </p>

<p>There are also some designs developed that claimed to have backward compatibility, so HD-DVDs could be played back downgraded on regular DVD players.  However, unresolved copy protection issues and format wars might further delay the introduction of this technology.  </p>

<p>We hope that one day soon we would actually see final products using a single standard format.  The implementation of this technology would certainly accelerate the public interest on HDTV monitors."  </p>

<p></p>

<p>Well, we are 6 years later, does it sound familiar?</p>

<p>Please consult the 2003, 4, 5, and 6 reports to gain details about how this technology evolved.  The reports dedicate a separate section to the subject.  </p>

<p>However, at this time, let us address the part of the title that says: <b>Well, what else is out there?</b></p>

<p><br />
<b><u>Chinese Hi-def DVD?</b></u></p>

<p>As detailed in my January 2004 report, when the Chinese market released their format called EVD, about 20 million players were expected to be released to the market within the next couple of years following that announcement.  EVD chips were produced and the format was expected to be an enhancement of the current DVD format.  What happened after that?  Where are all those Hi-def DVD players in the US?  Where are the movies?</p>

<p>At that time, it was anticipated that the EVD format could certainly affect much more than the Chinese market, although it was believed that content providers have not been contacted yet, an important factor for a pre-recorded format to be successful.   </p>

<p>Any R&D lab or manufacturer could create a wonderful product if given enough resources and time, but the success of this type of product depends on the availability of appealing content, content that needs to be protected to become widely available.  No egg no chicken.  Would you buy a PC if no software were available?   </p>

<p>Let us concentrate a bit on the hardware.  </p>

<p>To actually get a feeling of how real was this new Chinese DVD for HD (EVD) player, I met at CES 2004 with representatives of the Chinese company that manufactures the finished product, Changhong, Sichuan, China. </p>

<p>According to the company's profile (summarized version back then) "Changhong Electric Co., Ltd was founded in 1958, their R&D teams have joint labs with Toshiba, SANYO, Philips, Panasonic, Motorola, to name a few.  Changhong was regarded as a miracle in China, it grew to be one of the 3 leading TV manufacturers in the world, the aggressive DVD product supplier in China, the biggest electronics component supplier in Asia, with sale's volumes over 2 billion US dollars". </p>

<p>They were looking for US importers of the EVD player at that time.  Back then I left pending a confirmation of the information below with the US project manager; unfortunately, neither the manager nor the firm's headquarters in China returned my emails and calls, but I include below what was disclosed at my CES 2004 meeting.  First, it was shocking to know that for their internal market the EVD player sells for approximately $120 (no zeros missing on this HD product). </p>

<p>Back in 2003 I mentioned: Those that have followed past events regarding DVD for HD (I am using that term to avoid confusing it with the HD-DVD named by Toshiba), will notice the large price difference with Sony's Blu-ray product released in Japan in<br />
<img src="/images/articles/evd-fact-sheet.jpg" alt="EVD Fact Sheet" align="right"><br />
April 03, which, although it is a recorder unit w/satellite tuner in Blu-Ray format, it sells for $3,800 (or over 30 times the EVD).</p>

<p>Likewise, other Blu-ray (unreleased) player products shown at CES for the past 3 years were generally estimated by their manufacturers at price points around $2,000 for their future introduction by 2004/5.  </p>

<p>Note in 2006:  actually the Pioneer Elite is about to be released for $1,800 by mid 2006, so the estimates above were no so far off for some models.  Other models were recently announced for about $1,000 like Sony and Samsung.  I am not discussing the HD DVD killer price points here, Blu-ray says that Toshiba is manipulating the price to gain market share, but Sony plans to follow the same approach with the Blu-ray Play Station 3 game-console, if we ever going to see that one out.</p>

<p>At that time, this Chinese EVD manufacturer expected that the product could sell in the US market for about $250, with a listed importer cost of $80 per unit ($ from the exporter list shown at the meeting).  </p>

<p>Back then (and maybe even now), its low price brought the market hope that the low price pressure introduced by EVD could make more affordable this technology sooner than experienced on previous format wars, such as the recordable DVD, DVD-Audio and SACD, and even the old Beta vs. VHS.  </p>

<p>At that time I said: Regardless of what the actual outcome of the EVD pricing with the blue-laser market would be, the pre-recorded playing format could only compete (and make pressure on the market) as long as the hardware and software are of "uncompromised HD quality and storage capacity" and the major Hollywood content providers support it.</p>

<p>Note in 2006: and we all know how messy that is, just add the content protection bullet vests of AACS (Advanced Access Content System), BD+, BD ROM Mark, and the ICT (Image Constraint Token) for down-resing analog connections, PVP-OPM and PVP-UAB for PC protection, the multiple video codecs, the multiple hi-bit audio formats, the HDMI specification upgrades everyone is using as excuse instead of saying the real reasons their products are not out, the lack of vision for not including 1080p outputs on HD DVD when playing discs that already are 1080p 24fps, etc. and we have quite a gridlock. </p>

<p>One particular item that I was waiting for clarification from this Chinese company back then (and I never got) was the storage capacity declared on the last two lines of the EVD Fact Sheet (>=105 minutes for 720p, >=50 minutes for 1080i), how much > was >?  I assumed less than one minute.  </p>

<p>The EVD company did not return messages to confirm pricing and specs, so without confirmation it was safer to assume that the maximum of 50 minutes of 1080i HD would not attract any major Hollywood studio for blockbuster feature films, maybe not even a Kung Fu movie from the Chinese movie making industry, but 105 minutes of 720p might.  The specs were provided as supplied at their CES booth (in Jan 2004). </p>

<p>Ironically, I said back then: "In the year 2004, we will witness how this format competition evolves and which Hollywood content provider/s would actually support the format; additionally, some of the Hi-def player manufacturers have already announced that they would release their Hi-def products starting this year (2004)."  </p>

<p>Note in 2006:  over three years and we are still in the waiting, only Toshiba released their first player a few weeks ago, with a handful of discs.</p>

<p>The following EVD photographs were taken at CES 2004:<br />
<img src="/images/articles/evd-500.jpg" alt="EVD Fact Sheet" align="left"><br />
<img src="/images/articles/evd-500-2.gif" alt="EVD Fact Sheet" align="right"><br clear=all></p>

<p>Please stay tuned for the next part of this series, appearing soon.<br />
</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Rodolfo La Maestra</b>, <b>May  8, 2006 11:50 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 371
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
 				AND entry_id <> 371
 				AND entry_status = 2
 				AND entry_author_id = a.author_id
 				AND a.author_name = 'Rodolfo La Maestra'
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
 				<h2>About Rodolfo La Maestra</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2006/05/hi-def_dvd_-_blue_laser_well_what_else_is_out_there.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
