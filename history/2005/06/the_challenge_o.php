<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 122";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Dale Cripps'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 122 AND placement_is_primary = 1";
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
	<meta name="keywords" content="advisory committee, advanced television, second channel, united states, hdtv system, HDTV, hdtv, system, television, Committee, committee, could, technology, service, commission, Commission, data, second, advisory, Advisory, FCC, flexibility, channel, fcc, Alliance" />
	<meta name="description" content="The Challenge of Choice 1996 Richard E. Wiley Richard Wiley is a former chairman of the Federal Communications Commission. To this day he is often referred to as the 5th Commissioner. He is now a highly successful communications attorney with..." />
	<title>HDTV Magazine Archive &amp; History - The Challenge of Choice - Richard E. Wiley - 1996</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/the_challenge_of_choice_-_richard_e_wiley_-_1996';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('The Challenge of Choice - Richard E. Wiley - 1996'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/history/2005/06/the_challenge_of_choice_-_richard_e_wiley_-_1996.php";
		if ($author[img] != '' && 5 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Dale Cripps" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">The Challenge of Choice - Richard E. Wiley - 1996</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Dale Cripps</b><br />
				<?=$author_title?>
				Posted on <b>June 25, 2005</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category="></a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/2005/06/the_challenge_of_choice_-_richard_e_wiley_-_1996.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/history/2005/06/the_challenge_of_choice_-_richard_e_wiley_-_1996.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/history/2005/06/the_challenge_of_choice_-_richard_e_wiley_-_1996.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/2005/06/the_challenge_of_choice_-_richard_e_wiley_-_1996.php&amp;phase=2&amp;title=The%20Challenge%20of%20Choice%20-%20Richard%20E.%20Wiley%20-%201996&amp;bodytext=The%20Challenge%20of%20Choice%201996%20Richard%20E.%20Wiley%20Richard%20Wiley%20is%20a%20former%20chairman%20of%20the%20Federal%20Communications%20Commission.%20To%20this%20day%20he%20is%20often%20referred%20to%20as%20the%205th%20Commissioner.%20He%20is%20now%20a%20highly%20successful%20communications%20attorney%20with...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p>The Challenge of Choice <br />
1996 </p>

<p>Richard E. Wiley <br />
<em><br />
Richard Wiley is a former chairman of the Federal Communications Commission. To this day he is often referred to as the 5th Commissioner. He is now a highly successful communications attorney with the K Street firm of Wiley, Reins, and Fielding. He was appointed by the FCC as Chairman of the FCC Advisory Committee on Advanced Television Services, the all-volunteer blue ribbon organization that pulled the ATSC standard together in a mere nine years (the longest period ever taken for a communications standard to be produced). As the ACATS duties drew to a close Dick wrote the following article for all to reflect upon.</em></p>

<p><br />
Imagine organizing a league of multisport athletes like Deion Sanders, Bo Jackson, and Michael Jordan. You would have great competition, but it would be difficult to decide what game to play. With today's new and extraordinarily flexible communications technologies, a similar challenge of choice is facing the Federal Communications Commission (FCC or Commission) today. Indeed, at age sixty, the Commission is in the unfamiliar position of being able to choose, or not choose, among myriad services that could be offered in the same spectrum. The quintessential example of this technological dilemma is the technology developed in the United States for advanced television (ATV) service, including high-definition television (HDTV).</p>

<p>As recently as 1987, the United States was not a major force in ATV technology. The FCC recognized, however, that developments in advanced television technology in Japan and Europe could affect U.S. broadcasting. Accordingly, the Commission set aside spectrum within the existing broadcast bands to give licensees a "second channel" on which to offer ATV and established an all-industry Advisory Committee on Advanced Television Service (which I have been honored to chair) to assist it in establishing a new television standard for the country.</p>

<p>Japan, led by its broadcasting company NHK, already had built and demonstrated HDTV equipment. Japanese concerns had been working for nearly twenty years on a high quality system to replace existing National Television Systems Committee (NTSC) television technology, which was designed in the United States in the late 1930s and early 1940s, and improved, also in the United States, with color about a decade later. Western European governments and companies also were moving rapidly with their well-funded HDTV projects which, as in Japan, were focused on simply replacing current TV with much higher quality satellite-based technology. The Advisory Committee was charged with finding and recommending ATV technology that was appropriate for the more challenging terrestrial broadcasting environment.</p>

<p>Initially, the Committee received twenty-three ATV system proposals, all of which featured analog transmission techniques similar to those being developed in Japan and Europe. Through proponent mergers and attrition, this number soon was reduced to a handful. In April 1990, the Commission decided that the new, higher quality, terrestrial ATV service would be HDTV, not some sort of enhanced NTSC service. However, available HDTV technology at that time was not flexible; it could provide advanced pictures and sound, but little else. Shortly thereafter, one of the remaining system proponents, General Instrument Corporation, modified its proposal to incorporate all-digital transmission. Three of the other four remaining systems quickly adopted this technological advance, with only NHK retaining its original analog transmission format. All five systems then were subjected to an exacting program of laboratory tests conducted under Advisory Committee supervision at sophisticated technical facilities.</p>

<p>Based on the results of these tests, the Committee decided that the four digital transmission systems were superior to the NHK proposal, which thereafter was eliminated from consideration. In just a few years, the United States had progressed from a non-player to a potential world leader in advanced television technology. Despite the success of the four all-digital systems, it was clear that all of them had technical shortcomings that required further development. The Advisory Committee then gave the proponents a critical choice: to undergo an expensive second round of testing focusing on improvements that each system had proposed or, alternatively, to merge their proposals into a single, unified system.</p>

<p>The latter option was preferable from three standpoints. First, the systems were becoming more alike as they learned from each other's technical advances, making the Advisory Committee's eventual task of selecting between them both more problematic and more likely subject to challenge and resulting delay. Second, the process of retesting was certain to be expensive and time-consuming for all concerned. Finally, and most significantly, a single system encompassing the best features of various proposals might lead to the development of a truly superior technology. Accordingly, the Advisory Committee encouraged talks between the proponents which, in May 1993, resulted in the formation of a so-called "Grand Alliance."</p>

<p>At the time, I made clear to the Alliance members that they should not present the Advisory Committee with an inflexible, technical fait accompli and that the Committee's work had been, and must remain, a public process. After detailed discussions between the Committee and the Alliance extending over a number of months, a modified (and, I believe, considerably enhanced) system proposal was developed.</p>

<p>One of the most significant outcomes of this Advisory Committee-Grand Alliance dialogue was an agreement on a packetized data transport system, which allows the transmission of virtually any combination of video, audio, and data. The transport system arranges digital data into discrete groups called packets and labels each packet before transmission. At the receiver, packets are routed to specific desired applications according to the instructions in their labels. This highly flexible HDTV system has capabilities that extend far beyond what the FCC could have envisioned in 1990.</p>

<p>With a capability sometimes called "dynamic allocability," the Grand Alliance HDTV system can take advantage of the packet-based flexibility to transmit prodigious amounts of data during an HDTV program. The Alliance video compression subsystem sends only the picture information that is necessary to define the changes from one frame to the next. Thus, scenes with limited motion require little of the system's roughly twenty megabits per second payload data capacity. More data can be transmitted during lulls in the main video action. The packets containing such information simply are labelled as data, not HDTV video.</p>

<p>For example, while watching a baseball game, a fan could choose to have the set simultaneously display the statistics of the batter, the results of other games, stock market reports, or a local weather report. Such options could be controlled via on-screen interfaces. Other uses of the dynamic allocability feature could be completely unrelated to the television aspect of the broadcast. For example, at the same time that an HDTV program is being shown, a grocery store chain could broadcast data describing new inventory or price lists of all of its local outlets in a matter of a few seconds. Alternatively, a kind of paging service could be established that would transmit messages while HDTV is being broadcast.</p>

<p>In addition, utilizing a so-called "dynamic scalability" feature, the Grand Alliance HDTV system is capable of simultaneously offering several standard-definition, NTSC-quality television programs. Some broadcasters believe that the additional revenues-which could be generated from multiple programs shown during limited times of the day or from shows where high quality pictures are less important (for example, talk shows)-could help finance the introduction of HDTV for sports, prime-time programming, and movies.</p>

<p>These kinds of flexibility-dynamic allocability (ancillary or unrelated data) or dynamic scalability (multiple lower-resolution programs)-are not possible without a digital HDTV system operating on the second channel. The current NTSC analog television system cannot support such flexible use of the spectrum.</p>

<p>The Commission's options in this area are varied but perplexing. It could decide to give broadcasters total flexibility in the use of the second channel. If so, the question arises: given the FCC's new-found affinity for spectrum auctions, would the agency demand some payment by the industry for the additional frequencies? Currently, the Commission's policy is that making a second channel available to existing broadcasters would be in the public interest in order to maintain current NTSC service on the first channel while allowing the transition to higher quality advanced television on the second channel. Complete flexibility, including the possible elimination of HDTV service in favor of more revenue-producing alternative services, might dictate a different result.(note 1)</p>

<p>Alternatively, the Commission could opt to require that a certain portion of the broadcast day be devoted initially to HDTV programming. After a fair trial period, if the public demonstrates little interest in such a new service, this requirement could be eliminated.</p>

<p>My own judgment is that broadcasters should be granted some flexibility in the use of the second channel. By so doing, the government would be promoting new service to the consumer and also giving licensees the opportunity to earn revenues to support what clearly will be an expensive transition to digital broadcasting. However, I believe that such flexibility should not preclude HDTV broadcasting. This would be an abandonment of the Commission's principal justification for reserving the additional channel for over-the-air service against myriad other possible uses. It also would deprive the American television viewers of an opportunity to decide whether they really want higher quality reception.(note 2)</p>

<p>Digital transmission technology provides the FCC with the flexibility to make crucial public interest determinations. In this instance, the agency's spectrum choice can facilitate the public's service choice.</p>

<p>Notes</p>

<p>*The Author, a former Chairman of the Federal Communications Commission, is a partner in the Washington, D.C., firm of Wiley, Rein & Fielding. His associate Paul E. Misener assisted in writing this Essay. </p>

<p>1. The technology for flexible service use is not free, of course; it will be just as expensive as meeting the FCC's HDTV implementation requirements. Indeed, broadcasters must invest in HDTV technology to obtain this flexibility. </p>

<p>2. This is especially true for larger screen television sets, which are the fastest-growing segment of the receiver market.<br />
 </p>

<p> </p>
		</div>
	</div>
	<p class="posted">Posted by <b>Dale Cripps</b>, <b>June 25, 2005 09:16 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 122
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
 				AND entry_id <> 122
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/2005/06/the_challenge_of_choice_-_richard_e_wiley_-_1996.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
