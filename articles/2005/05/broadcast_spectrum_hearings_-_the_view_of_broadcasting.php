<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 35";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Eddie Fritts'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 35 AND placement_is_primary = 1";
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
	<meta name="keywords" content="cable carriage, television stations, air television, multi cast, good consumers, consumers, stations, cable, television, air, draft, dtv, sets, DTV, viewers, good, let, analog, must, transition, services, local, carriage, today, digital" />
	<meta name="description" content="There is a hearing in Washington today with the House Telecommunications Subcommittee. It's about the DTV transition and cutting off the analog signals. Legislation is being crafted that seeks a specific date for shutting down analog services so the spectrum can be returned and reassigned by way of auctions to some presumably &quot;new&quot; highest bidder. As with any complex issue there is never an easy answer that fully satisfies all, but the one party that must be satisfied above all..." />
	<title>HDTV Magazine Articles - Broadcast Spectrum Hearings - The View Of Broadcasting</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/broadcast_spectrum_hearings_-_the_view_of_broadcasting';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Broadcast Spectrum Hearings - The View Of Broadcasting'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/articles/2005/05/broadcast_spectrum_hearings_-_the_view_of_broadcasting.php";
		if ($author[img] != '' && 1 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Eddie Fritts" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Broadcast Spectrum Hearings - The View Of Broadcasting</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Eddie Fritts</b><br />
				<?=$author_title?>
				Posted on <b>May 26, 2005</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Politics & Policy">Politics & Policy</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2005/05/broadcast_spectrum_hearings_-_the_view_of_broadcasting.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/articles/2005/05/broadcast_spectrum_hearings_-_the_view_of_broadcasting.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/articles/2005/05/broadcast_spectrum_hearings_-_the_view_of_broadcasting.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2005/05/broadcast_spectrum_hearings_-_the_view_of_broadcasting.php&amp;phase=2&amp;title=Broadcast%20Spectrum%20Hearings%20-%20The%20View%20Of%20Broadcasting&amp;bodytext=There%20is%20a%20hearing%20in%20Washington%20today%20with%20the%20House%20Telecommunications%20Subcommittee.%20It%27s%20about%20the%20DTV%20transition%20and%20cutting%20off%20the%20analog%20signals.%20Legislation%20is%20being%20crafted%20that%20seeks%20a%20specific%20date%20for%20shutting%20down%20analog%20services%20so%20the%20spectrum%20can%20be%20returned%20and%20reassigned%20by%20way%20of%20auctions%20to%20some%20presumably%20%22new%22%20highest%20bidder.%20As%20with%20any%20complex%20issue%20there%20is%20never%20an%20easy%20answer%20that%20fully%20satisfies%20all%2C%20but%20the%20one%20party%20that%20must%20be%20satisfied%20above%20all...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p>There is a hearing in Washington today with the House Telecommunications Subcommittee. It's about the DTV transition and cutting off the analog signals. Legislation is being crafted that seeks a specific date for shutting down analog services so the spectrum can be returned and reassigned by way of auctions to some presumably "new" highest bidder. As with any complex issue there is never an easy answer that fully satisfies all, but the one party that must be satisfied above all others is the public. </p>

<p>As other hearing panelists send us their papers we will publish them here in our HDTV Magazine blog. Let me urge you to comment on anything you have read from these panelist. You are the consumers and they say you are the ones who have to be protected above all else. Do you want to be protected by any government measure? Is it needed? Could the analog channels be discontinued today and leave you wihtout a negative consequence? Let me hear from you </p>

<p><br />
Here is one thing you can take to the bank: The broadcast community is motivated to conclude the transition as there are power bills and maintenance cost for the "extra" channel of bandwidth now under their management. </p>

<p><a href="http://www.marketwatch.com/news/story.asp?guid=%7BCC3E6C94%2D1CEE%2D44A0%2DAE87%2DEDC264A9DC07%7D&dist=rss&siteid=mktw">http://www.marketwatch.com/news/story.asp?guid=%7BCC3E6C94%2D1CEE%2D44A0%2DAE87%2DEDC264A9DC07%7D&dist=rss&siteid=mktw</a></p>

<p></p>

<p>Testimony of Mr. James Yager<br />
CEO of Barrington Broadcasting<br />
On behalf of NAB</p>

<p><br />
Before the House Telecommunications Subcommittee</p>

<p>5.26.05</p>

<p>Thank you, Mr. Chairman, Mr. Markey, Members of the Subcommittee. </p>

<p>I'm Jim Yager, CEO of Barrington Broadcasting, which owns and operates four television stations in Michigan, Illinois and Missouri. </p>

<p>Today, there are nearly 1,500 local television stations on-air in digital. Let me be very clear: all broadcasters -- large and small -- want to see the DTV transition brought to a successful close. </p>

<p>As an industry, we have consistently said that this transition is first and foremost about consumers - your constituents. . . our viewers. </p>

<p>Many good things can be said about the staff draft in this regard. </p>

<p>For instance, labeling as "soon to be obsolete" the 20 million analog-only sets that manufacturers plan to sell this year. . . is the right thing to do. . . for consumers. </p>

<p>Accelerating the FCC's tuner integration deadline is a good thing. . . for consumers. </p>

<p>Whether discussing the benefits of DTV, talking about cable carriage. . . or discussing over-the-air TV sets. . . the central piece of the equation must always be consumers. </p>

<p>In that vein, let me recommend a few modifications to the staff draft that would better protect consumers' interests. </p>

<p>First and foremost, protecting over-the-air viewers must be a priority. </p>

<p>The GAO has said that there are 21 million households in this country that rely exclusively upon over-the-air reception. That is more homes than are located in the states of Texas. . . Michigan. . . Massachusetts. . . Mississippi. . . Nebraska. . . New Mexico. . . Oregon. . . Tennessee and Wyoming combined. </p>

<p>Under the draft's hard date, viewers will either lose their television service or have to pay for converter boxes. . . or even worse. . . subscribe to pay TV. . . all just to keep something they currently get for free. </p>

<p>Many of these viewers are among society's most economically vulnerable demographics. </p>

<p>Low-income senior citizens are disproportionately dependent upon off-air reception. </p>

<p>African American households are 22% more likely to rely exclusively on over-the-air reception. </p>

<p>Forty-three percent of Spanish-language households rely solely on over-the-air television. </p>

<p>Moreover, when you count the second and third sets in the bedrooms and kitchens of cable and satellite homes, there are 73 million sets in this country that risk being rendered obsolete by a premature hard date. </p>

<p>Some on this Committee have recognized that turning off these TV sets will create a firestorm of consumer outrage. </p>

<p>But even setting aside the political considerations, all of us here share an obligation to protect over-the-air television viewers. </p>

<p>Local television broadcasters are ready to work with you to achieve that goal. </p>

<p>Consumers' interests should also drive the cable carriage discussion. </p>

<p>Broadcasters commend the draft's authors for trying to tackle down conversion. </p>

<p>The draft will allow cable subscribers with digital sets to enjoy the benefits of DTV. That's good for consumers. </p>

<p>Allowing cable systems to down convert as long as they also carry stations' digital signals will mean that subscribers with only analog sets won't be be cut off. That's good for consumers. </p>

<p>Under the current language, all must-carry stations in a market would be treated the same in terms of down conversion. That's good for consumers. </p>

<p>But, the draft leaves room for a scenario in which pay TV services could essentially withhold smaller stations- religious stations, Spanish language stations - from consumers who have not yet purchased DTV equipment. </p>

<p>We see great promise in this section of the draft. However, it does require modest clarification so that all stations in a market - large and small - are treated equally with regards to down conversion. </p>

<p>Finally, let me touch upon multi-cast cable carriage. </p>

<p>Today, 540 local television stations are using DTV to multi-cast and better serve their communities with enhanced programming options. </p>

<p>These free services are the beginning of a whole series of new offerings that will evolve to benefit consumers. . . . But only if cable does not withhold them. </p>

<p>Should cable operators block consumers from receiving this programming, it will be difficult for stations to justify the risk and costs of developing these services. . . and the potentials of multi-casting will not be realized. </p>

<p>This is not a capacity issue. Cable carriage of these services would occupy no more capacity than carrying a single, high-definition, digital channel. After all, 6 megahertz. . . is 6 megahertz. . . is 6 megahertz. </p>

<p>The Committee should protect consumers by adopting a strong multi-cast. . . must-carry rule. </p>

<p>Mr. Chairman, let me close by reiterating that the draft is a good starting point. </p>

<p>As the Committee moves forward, local broadcasters are ready to work with you to develop a DTV policy that ends the transition. . . that clears the analog spectrum. . . that protects the interests of over-the-air television viewers. . . and, ultimately enhances television for all consumers. </p>

<p><br />
</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Eddie Fritts</b>, <b>May 26, 2005 08:49 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 35
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
 			<h2>More on Politics & Policy</h2><ul><?
 			$sql = "
 			SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name
 			FROM mt_entry e, mt_author a, mt_placement p, mt_category c
 			WHERE entry_author_id = author_id
 				AND e.entry_id = p.placement_entry_id
 				AND p.placement_category_id = c.category_id
 				AND category_label = 'Politics & Policy'
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
 				AND entry_id <> 35
 				AND entry_status = 2
 				AND entry_author_id = a.author_id
 				AND a.author_name = 'Eddie Fritts'
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
 				<h2>About Eddie Fritts</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2005/05/broadcast_spectrum_hearings_-_the_view_of_broadcasting.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
