<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 723";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Rodolfo La Maestra'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 723 AND placement_is_primary = 1";
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
	<meta name="keywords" content="iptv part, iptv subscribers, iptv services, part methods, next article, iptv, IPTV, part, Part, million, service, technology, worldwide, subscribers, cable, satellite, source, According, series, gartner, Gartner, Pay, existing, pay, telecom" />
	<meta name="description" content="In April 2006, the International Telecommunications Union (ITU) created a focus group on IPTV to coordinate and promote the development of global IPTV standards with the following agenda:

&lt;ul&gt;&lt;li&gt;Define IPTV&lt;/li&gt;&lt;li&gt;Review and perform gap analysis of existing standards and ongoing works&lt;/li&gt;&lt;li&gt;Coordinate existing standardization activities&lt;/li&gt;&lt;li&gt;Harmonize the development of new standards&lt;/li&gt;&lt;li&gt;Encourage interoperability with existing systems where possible&lt;/li&gt;&lt;/ul&gt;

Ericsson, Matsushita's Panasonic, Philips, Samsung Electronics, Siemens AG, Sony, AT&amp;T, Telecom Italia, and France Telecom, to work on a single IPTV standard, created an Open IPTV Forum.

The forum is open to other companies to join, supports IP Multimedia Subsystem (IMS) for unified Internet service delivery, and Digital Living Network Alliance (DLNA). The group was planning to..." />
	<title>HDTV Magazine Articles - IPTV Part 2 - The Groups, Forums and Statistics</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/iptv_part_2_-_the_groups_forums_and_statistics';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('IPTV Part 2 - The Groups, Forums and Statistics'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/articles/2007/09/iptv_part_2_-_the_groups_forums_and_statistics.php";
		if ($author[img] != '' && 1 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Rodolfo La Maestra" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">IPTV Part 2 - The Groups, Forums and Statistics</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Rodolfo La Maestra</b><br />
				<?=$author_title?>
				Posted on <b>September 25, 2007</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Technology">Technology</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2007/09/iptv_part_2_-_the_groups_forums_and_statistics.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/articles/2007/09/iptv_part_2_-_the_groups_forums_and_statistics.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/articles/2007/09/iptv_part_2_-_the_groups_forums_and_statistics.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2007/09/iptv_part_2_-_the_groups_forums_and_statistics.php&amp;phase=2&amp;title=IPTV%20Part%202%20-%20The%20Groups%2C%20Forums%20and%20Statistics&amp;bodytext=In%20April%202006%2C%20the%20International%20Telecommunications%20Union%20%28ITU%29%20created%20a%20focus%20group%20on%20IPTV%20to%20coordinate%20and%20promote%20the%20development%20of%20global%20IPTV%20standards%20with%20the%20following%20agenda%3A%0A%0A%3Cul%3E%3Cli%3EDefine%20IPTV%3C%2Fli%3E%3Cli%3EReview%20and%20perform%20gap%20analysis%20of%20existing%20standards%20and%20ongoing%20works%3C%2Fli%3E%3Cli%3ECoordinate%20existing%20standardization%20activities%3C%2Fli%3E%3Cli%3EHarmonize%20the%20development%20of%20new%20standards%3C%2Fli%3E%3Cli%3EEncourage%20interoperability%20with%20existing%20systems%20where%20possible%3C%2Fli%3E%3C%2Ful%3E%0A%0AEricsson%2C%20Matsushita%27s%20Panasonic%2C%20Philips%2C%20Samsung%20Electronics%2C%20Siemens%20AG%2C%20Sony%2C%20AT%26T%2C%20Telecom%20Italia%2C%20and%20France%20Telecom%2C%20to%20work%20on%20a%20single%20IPTV%20standard%2C%20created%20an%20Open%20IPTV%20Forum.%0A%0AThe%20forum%20is%20open%20to%20other%20companies%20to%20join%2C%20supports%20IP%20Multimedia%20Subsystem%20%28IMS%29%20for%20unified%20Internet%20service%20delivery%2C%20and%20Digital%20Living%20Network%20Alliance%20%28DLNA%29.%20The%20group%20was%20planning%20to...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<div class="editorial">The following article is the latest in the IPTV series by Rodolfo La Maestra. Other articles in this series are as follows:
<ul>
<li><a href="/articles/2007/09/iptv_part_1_-_read_the_fine_print.php">IPTV Part 1 - Read the Fine Print</a></li>
<li><a href="/articles/2007/10/iptv_part_3_-_the_methods_and_a_working_technology.php">IPTV Part 3 - The Methods and a Working Technology</a></li>
<li><a href="/articles/2007/10/iptv_part_4_-_the_good_the_bad_and_the_ugly.php">IPTV Part 4 - The Good, the Bad and the Ugly</a></li>
<li><a href="/articles/2007/10/iptv_part_5_-_additional_implementations.php">IPTV Part 5 - Additional Implementations</a></li>
<li><a href="/articles/2007/10/iptv_part_6_-_more_implementations_and_final_thoughts.php">IPTV Part 6 - More Implementations and Final Thoughts</a></li>
</ul></div>
<br />
<B>IPTV Groups</B>

<p>In April 2006, the International Telecommunications Union (ITU) created a focus group on IPTV to coordinate and promote the development of global IPTV standards with the following agenda:</p>

<ul><li>Define IPTV</li><li>Review and perform gap analysis of existing standards and ongoing works</li><li>Coordinate existing standardization activities</li><li>Harmonize the development of new standards</li><li>Encourage interoperability with existing systems where possible</li></ul>

<p>Ericsson, Matsushita's Panasonic, Philips, Samsung Electronics, Siemens AG, Sony, AT&T, Telecom Italia, and France Telecom, to work on a single IPTV standard, created an Open IPTV Forum.</p>

<p>The forum is open to other companies to join, supports IP Multimedia Subsystem (IMS) for unified Internet service delivery, and Digital Living Network Alliance (DLNA). The group was planning to complete technology requirements in Sep 06 and initial specifications by Dec 06.</p>

<p><a href="/cgi-bin/ntlinktrack.cgi?http://www.itu.int/" target="_blank">International Telecommunications Union (ITU)</a></p>

<p><br />
<B>IPTV - the Numbers</B></p>

<p>According to a recent market projection made by Gartner, by 2010 48.8 million households worldwide will subscribe to IPTV services from telecom carriers.</p>

<p>IPTV subscribers will double in number during 2007 to 13.3 million, which is expected to generate 13.2 billion of revenue, from the 6.4 million subscribers in 2006 who generated $872 million of revenue.</p>

<p>According to Gartner, one positive effect of incorporating an IPTV service into the lineup is that it would help a carrier retain current voice and broadband customers, (and coincidentally, that was actually declared by one of the Telcos that implemented IPTV, further below).</p>

<p>In my opinion, it could also be negative to the carrier if the IPTV service starts to show technical flaws like video breakups, program interruptions, etc.</p>

<p>A combination of reasons could produce a flawed service, from limited bandwidth, to hardware choices, software bugs, or simply because the technology might not be mature enough yet for the particular implementation, which was the case with several Telcos mentioned further below.</p>

<p><br />
<B><center>IPTV Implementation Growth</center></B><table class="bare" align="center"><tr><td class="type1b_header">&nbsp;</td><td class="type1b_header">2005</td><td class="type1b_header">2006</td><td class="type1b_header">2007</td><td class="type1b_header">2008</td><td class="type1b_header">2009</td><td class="type1b_header">2010</td><td class="type1b_header">(CAGR) 2010</td></tr><tr><td class="type1b_header">Total IPTV subscribers (in millions)</td><td class="grid">3.2</td><td class="grid">6.4</td><td class="grid">13.3</td><td class="grid">24.7</td><td class="grid">36.3</td><td class="grid">48.8</td><td class="grid">72.8%</td></tr><tr><td class="type1b_header">Growth (% year over year)</td><td class="grid">139.4</td><td class="grid">102.5</td><td class="grid">108.1</td><td class="grid">85.5</td><td class="grid">46.7</td><td class="grid">34.5</td><td class="grid">&nbsp;</td></tr><tr><td colspan="8">Source: Gartner Dataquest (August 2006)</td></tr></table><br />
<br /></p>

<p>From another research source, Infonetics Research projected worldwide IPTV subscribers doubling up yearly between 2005-09, totaling 68.9 million by 2009.</p>

<p>"IPTV is still in the 'kick the tire' phase, with service providers doing trials rather than mass deployments, but there's no question that IPTV is going mainstream," said Jeff Heynen, directing analyst for broadband and IPTV at Infonetics.</p>

<p>According to a forecast from iSuppli published by CED in March 07, worldwide revenues to be generated from Pay TV shows that IPTV services would grow more rapidly than the same services from cable and satellite.</p>

<p><br />
<B><center>Worldwide Subscription Revenues for Pay TV - Satellite, Cable and IPTV</center></B><table class="bare" align="center"><tr><td class="type1b_header">&nbsp;</td><td class="type1b_header">2004</td><td class="type1b_header">2005</td><td class="type1b_header">2006</td><td class="type1b_header">2007</td><td class="type1b_header">2008</td><td class="type1b_header">2009</td><td class="type1b_header">2010</td></tr><tr><td class="type1b_header">Satellite</td><td class="grid">31,390</td><td class="grid">$35,190</td><td class="grid">$40,263</td><td class="grid">$45,220</td><td class="grid">$50,067</td><td class="grid">$54,929</td><td class="grid">$58,751</td></tr><tr><td class="type1b_header">Cable</td><td class="grid">$68,808</td><td class="grid">$73,692</td><td class="grid">$78,178</td><td class="grid">$83,586</td><td class="grid">$88,460</td><td class="grid">$92,469</td><td class="grid">$96,074</td></tr><tr><td class="type1b_header">IPTV</td><td class="grid">$419</td><td class="grid">$681</td><td class="grid">$1,592</td><td class="grid">$4,413</td><td class="grid">$9,619</td><td class="grid">$15,998</td><td class="grid">$23,466</td></tr><tr><td class="type1b_header">Total Pay Television</td><td class="grid">$100,616</td><td class="grid">$109,563</td><td class="grid">$120,032</td><td class="grid">$133,219</td><td class="grid">$148,146</td><td class="grid">$163,397</td><td class="grid">$178,291</td></tr><tr><td colspan="8">(Numbers millions of U.S. dollars)<br />Source: iSuppli Corp., February 2007 as published by CED</td></tr></table><br /></p>

<p>In the next article in this series, I will discuss the different ways in which IPTV is being implemented as well as some companies who already have working models in place.</p>

<p>Next Article: <a href="/articles/2007/10/iptv_part_3_-_the_methods_and_a_working_technology.php">IPTV Part 3 - The Methods and a Working Technology</a></p>
		</div>
	</div>
	<p class="posted">Posted by <b>Rodolfo La Maestra</b>, <b>September 25, 2007 07:46 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 723
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
 				AND entry_id <> 723
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2007/09/iptv_part_2_-_the_groups_forums_and_statistics.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
