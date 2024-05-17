<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 174";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Dale Cripps'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 174 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category[placement_category_id];
	
	# Still need this to track page views. Used in the Links unit in the footer
	$google_links_channel = $author[channel];

	# Set variables based on entry type. NOTE: Podcasts has its own entry template, so it is not included amongst the choices below.
	switch (5) {
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
	<meta name="keywords" content="cable satellite, taboo channels, date certain, cant afford, our own, spectrum, HDTV, subsidy, transition, hdtv, services, FCC, new, fcc, broadcasting, could, congress, cable, channels, Committee, those, Congress, digital, committee, buy" />
	<meta name="description" content="I am no apologist for Congress, CEA, the manufacturers, or broadcasters, but I have been around long enough to know that this issue is very complex and not very well understood. 

The entire reason you have HDTV today is because Eddie Fritts, CEO of the National Association of Broadcasters sold Congress on the idea that free television is essential to our democracy. Free broadcasting provides entirely free signals to anyone who can receive them, be that in their home or in some group home, or whatever the scene is. The point is that an informed electorate is essential to democracy and Eddie convinced the FCC and Congress in 1987 that free TV was threatened until it could compete in the HDTV arena that was just starting to show itself. HDTV was much better fit to cable and DBS (when it would come), and, if wildly popular, could walk away with the power that free TV has to buy original programming and to run a free news service which has accountability (public airwaves-public service) to the govenrment, which the cable news does not.
" />
	<title>HDTV Magazine Archive &amp; History - 2005 - And What About This Subsidy and a Date Certain?</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/2005_-_and_what_about_this_subsidy_and_a_date_certain';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('2005 - And What About This Subsidy and a Date Certain?'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/history/2005/08/2005_-_and_what_about_this_subsidy_and_a_date_certain.php";
		if ($author[img] != '' && 5 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Dale Cripps" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">2005 - And What About This Subsidy and a Date Certain?</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Dale Cripps</b><br />
				<?=$author_title?>
				Posted on <b>August  4, 2005</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category="></a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/2005/08/2005_-_and_what_about_this_subsidy_and_a_date_certain.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/history/2005/08/2005_-_and_what_about_this_subsidy_and_a_date_certain.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/history/2005/08/2005_-_and_what_about_this_subsidy_and_a_date_certain.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/2005/08/2005_-_and_what_about_this_subsidy_and_a_date_certain.php&amp;phase=2&amp;title=2005%20-%20And%20What%20About%20This%20Subsidy%20and%20a%20Date%20Certain%3F&amp;bodytext=I%20am%20no%20apologist%20for%20Congress%2C%20CEA%2C%20the%20manufacturers%2C%20or%20broadcasters%2C%20but%20I%20have%20been%20around%20long%20enough%20to%20know%20that%20this%20issue%20is%20very%20complex%20and%20not%20very%20well%20understood.%20%0A%0AThe%20entire%20reason%20you%20have%20HDTV%20today%20is%20because%20Eddie%20Fritts%2C%20CEO%20of%20the%20National%20Association%20of%20Broadcasters%20sold%20Congress%20on%20the%20idea%20that%20free%20television%20is%20essential%20to%20our%20democracy.%20Free%20broadcasting%20provides%20entirely%20free%20signals%20to%20anyone%20who%20can%20receive%20them%2C%20be%20that%20in%20their%20home%20or%20in%20some%20group%20home%2C%20or%20whatever%20the%20scene%20is.%20The%20point%20is%20that%20an%20informed%20electorate%20is%20essential%20to%20democracy%20and%20Eddie%20convinced%20the%20FCC%20and%20Congress%20in%201987%20that%20free%20TV%20was%20threatened%20until%20it%20could%20compete%20in%20the%20HDTV%20arena%20that%20was%20just%20starting%20to%20show%20itself.%20HDTV%20was%20much%20better%20fit%20to%20cable%20and%20DBS%20%28when%20it%20would%20come%29%2C%20and%2C%20if%20wildly%20popular%2C%20could%20walk%20away%20with%20the%20power%20that%20free%20TV%20has%20to%20buy%20original%20programming%20and%20to%20run%20a%20free%20news%20service%20which%20has%20accountability%20%28public%20airwaves-public%20service%29%20to%20the%20govenrment%2C%20which%20the%20cable%20news%20does%20not.%0A&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p><img alt="dale1bnr.jpg" src="http://www.hdtvmagazine.com/history/images/mt/dale1bnr.jpg" width="606" height="96" /></p>

<p><em>On the HDForum I came across a scathing rebuke of Congress for considering the idea of subsidizing over-the-air ATSC decoders for those who have no reason to buy one, or can't buy one. My article below in response was met with some scorn for my "bleeding heart" approach. It is a cynical age in which we must live.</em>Dale<br />
______________________________________________________</p>

<p>I am no apologist for Congress, the CEA, the manufacturers, the broadcasters, or anyone else involved in this HDTV movement. But I have been around long enough to know that this issue for making a 'date certain' is complex and not well understood. </p>

<p>One of many reasons we have HDTV today is because Eddie Fritts, CEO of the National Association of Broadcasters, sold Congress on the idea that free television is essential to our democracy. Terrestrial broadcasting provides free signals to all who are equipped to receive them. The idea in play here is that an informed electorate is essential to the health of a democracy. Eddie convinced the FCC and Congress in 1987 that broadcasting was at risk until made able to compete effectively in the HDTV arena (which was just starting to show itself). HDTV was better suited technically speaking for cable and DBS. If wildly popular within those mediums, free TV could lose its audience and thus their power to buy programming and support local and national news services. Broadcasting must be accountable (public airwaves-public service) to the people whereas cable or satellite news does not have that same accountability to the public. Even if they did the fact that they are a pay service means the poorest among us could be shut out of the information he or she would need to make an informed decision in the voting booth.</p>

<p>A petition to the FCC was drafted in late 1996 by 57 broadcast groups asking for a freeze any broadcast band spectrum allocations until the question regarding HDTV was fully answered.  </p>

<p>At that time broadcasting was all analog. If you wanted more information in a signal you would have to have more signal or bandwidth. </p>

<p>The FCC created the Advisory Committee on Advanced Television Services, a private blue ribbon group headed by former FCC Chairman, Richard Wiley, shortly after the petition was created. The task assigned to the Committee was to determine the spectrum requirements for HDTV by creating a suitable standard for it. The Committee called upon private industry from both here and abroad to make proposals; build hardware; test the hardware, and then the Committee was to make a recommendation for a standard that would be rubber stamp-adopted as the official FCC H/DTV standard. A secondary, but powerful, mission of the Committee was to get America in the lead in HDTV technology.</p>

<p>On the way to doing it all digital broadcasting was invented (with its power of compression). Less bandwidth would be needed than first thought due to compression capabilities. But still another thing happened. </p>

<p>A great deal of broadcast spectrum has been useless for years because it had to be set aside for guard or "taboo" channels. Those guard channels protect licensed broadcasting services from interference from adjacent channels. Much of the spectrum could be reclaimed from these taboo channels by re-inventing and employing television using the latest in technologies. This spectrum, if reclaimed by the FCC, could be re-assigned by auction and used for new or expanded services. A plan was drawn up in the early 1990s where each broadcaster would be given enough spectrum (mined from the taboo and unused channels) to transition to a new channel. Once fully transitioned the station would vacate the old analog channel so that spectrum could be sold at auction to the highest bidder.  </p>

<p>The FCC forged a transition plan that called for ending NTSC altogether in 2006, or once 85% of the public had digital reception. When written that meant 85% over-the-air digital reception. That has grown less clear and many interpret it as meaning when 85% have some kind of digital decoder, be it for terrestrial, cable, or satellite services. </p>

<p>The tail end of the transition could prove difficult. The last quarter of the population may not have the same desires as the other three quarters. The poor and disinterested are likely to resist being pushed into buying a new format--especially one which they had nothing to do with creating or popularizing. There are many who simply do not have appreciation for what others nearly worship. Many may suffer from a variety of ailments--macular degeneration, for example--(20 million victims at present) providing no motivation to buy a new TV for just picture quality. TVs also last 20 or more years. The poorest and elderly are the most dependants upon television. Some 400 million old standard NTSC sets have been sold in this country since the transition began in 1998. The oldest of that lot still has 12 years of life expectancy left in it and those sold last year have at least 20 years. Those who are dependant upon over-the-air analog transmission services to light up their analog TVs will not easily change without some inducements or incentives. It always requires a new energy to produce a change.</p>

<p>We with HDTV and services are fortunate and we may not sense in our own euphoria the hardship this transition can bring to the less fortunate. The "let them eat cake" notion has been expressed on numerous forums, suggesting a certain misunderstanding of the plight of the very poor. Yet, how a nation treats its poorest citizens is the truest test of its greatness.  </p>

<p>I know we are more thoughtful than we first appear or this nation would have no attraction to the rest of the world. We are a nation where compassion is a fact and the majority of us won't knowingly hurt anyone just to get something nice for ourselves. The completion of the transition will likely take some subsidy. Many object to a subsidy seeing it as some form of government taxpayer giveaway to the fraudulent. England, and elsewhere where digital transitions are underway, are using a subsidy plan just as do our own cable and satellite providers. There is no value in prolonging the end of the transition just to save a few pennies. This has proven true in private industry, like cable and satellite, and it will prove true for over-the-air services once the benefits of the recovered spectrum are made clear. </p>

<p>And here is the good news. </p>

<p>Any subsidy needed to finish the transition will come from the sale of this spectrum which the new technology "liberated". The proceeds from the sale of this spectrum come with no cost to our government. To find subsidy money we need NOT draw from tax payer dollars or the general funding. If there was ever a win win it is this one. </p>

<p> There may also be new advertising potential in a subsidized decoder. One of the big discussions of today is branded TVs. This is where someone (like ESPN) markets their own TV and every time it is turned on a message for ESPN comes on for a few seconds. You can bypass it, but there is value added to keep you glued to the opening screen. </p>

<p>The same thing can be done in these decoder boxes. A subsidy can be paid for from a presale of this advertising "space" along with a few dollars from the future auctions. It is not going to be costly to a taxpayer. The power supply is the most expensive item at $6  to $9. Three manufacturers have shown Congress their boxes for $50 or less. </p>

<p>We should not fight this subsidy. It is the right thing to do.</p>

<p>_Dale Cripps</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Dale Cripps</b>, <b>August  4, 2005 06:10 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 174
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
			
 		<?if (5 <> 7) {
 			# Recent Articles by Author (exclude this one)
 			# Do not show recent articles for Bulletins.
 			$qry = "
 			SELECT entry_id, entry_blog_id, entry_created_on, entry_title, author_id, author_name
 			FROM mt_entry e, mt_author a
 			WHERE entry_blog_id = 5
 				AND entry_id <> 174
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
 				<h2>About Archive &amp; History</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/2005/08/2005_-_and_what_about_this_subsidy_and_a_date_certain.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
