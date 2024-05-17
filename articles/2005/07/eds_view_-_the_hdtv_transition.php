<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 170";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Ed Milbourn'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 170 AND placement_is_primary = 1";
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
	<meta name="keywords" content="dtv transition, hdtv programming, consumer electronics, compelling hdtv, digital transmission, HDTV, hdtv, cable, Cable, DTV, dtv, digital, broadcasters, analog, programming, STB, OTA, ota, Broadcasters, stb, sets, most, transition, consumer, want" />
	<meta name="description" content="Talk about herding cats!  That's an apt analogy to the task the FCC has, and continues having, in an attempt to reconcile the positions of all the various entities with a vested interest in the DTV transition - and its star, HDTV.  These DTV &quot;stakeholders&quot; are, indeed, just like a bunch of cats - hissing, growling and pawing at each other. But this is understandable, for a lot is at stake as the most fundamental change in the history of US broadcasting takes place. So, let's take a look at the salient issues surrounding these stakeholders to get a better understanding of the various positions and their impact on the growth of HDTV.  " />
	<title>HDTV Magazine Articles - Ed's View - The (H)DTV Transition</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/eds_view_-_the_hdtv_transition';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Ed\'s View - The (H)DTV Transition'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/articles/2005/07/eds_view_-_the_hdtv_transition.php";
		if ($author[img] != '' && 1 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Ed Milbourn" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Ed's View - The (H)DTV Transition</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Ed Milbourn</b><br />
				<?=$author_title?>
				Posted on <b>July 31, 2005</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Politics & Policy">Politics & Policy</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2005/07/eds_view_-_the_hdtv_transition.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/articles/2005/07/eds_view_-_the_hdtv_transition.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/articles/2005/07/eds_view_-_the_hdtv_transition.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2005/07/eds_view_-_the_hdtv_transition.php&amp;phase=2&amp;title=Ed%27s%20View%20-%20The%20%28H%29DTV%20Transition&amp;bodytext=Talk%20about%20herding%20cats%21%20%20That%27s%20an%20apt%20analogy%20to%20the%20task%20the%20FCC%20has%2C%20and%20continues%20having%2C%20in%20an%20attempt%20to%20reconcile%20the%20positions%20of%20all%20the%20various%20entities%20with%20a%20vested%20interest%20in%20the%20DTV%20transition%20-%20and%20its%20star%2C%20HDTV.%20%20These%20DTV%20%22stakeholders%22%20are%2C%20indeed%2C%20just%20like%20a%20bunch%20of%20cats%20-%20hissing%2C%20growling%20and%20pawing%20at%20each%20other.%20But%20this%20is%20understandable%2C%20for%20a%20lot%20is%20at%20stake%20as%20the%20most%20fundamental%20change%20in%20the%20history%20of%20US%20broadcasting%20takes%20place.%20So%2C%20let%27s%20take%20a%20look%20at%20the%20salient%20issues%20surrounding%20these%20stakeholders%20to%20get%20a%20better%20understanding%20of%20the%20various%20positions%20and%20their%20impact%20on%20the%20growth%20of%20HDTV.%20%20&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p>Talk about herding cats! That's an apt analogy to the task the FCC has, and continues having, in an attempt to reconcile the positions of all the various entities with a vested interest in the DTV transition - and its star, HDTV. These DTV "stakeholders" are, indeed, just like a bunch of cats - hissing, growling and pawing at each other. But this is understandable, for a lot is at stake as the most fundamental change in the history of US broadcasting takes place. So, let's take a look at the salient issues surrounding these stakeholders to get a better understanding of the various positions and their impact on the growth of HDTV.  </p>

<p>Their numbers are numerous. They include:  Broadcasters, Cable Operations, Satellite System Operations, the US Government, Manufacturers, Retailers, Program Providers, and, the most important - us, the Ultimate Consumers.  Of these, let's focus on the "big five" who have the most active lobbying position in Washington, DC, and to some extent, represent the interests of all of the "stakeholders."  The "big five" are:  the Broadcasters, represented by the National Association of Broadcasters (NAB); the Cable industry, represented by the National Cable Television Association (NCTA); the Consumer Electronics Manufactures and Retailers, represented by the Consumer Electronics Association (CEA); the Programmers, represented by the Motion Picture Association of America (MPAA); and the US Government represented by the Federal Communications Commission (FCC) and the power of legislation (that's a big stick). </p>

<p><strong>Broadcasters (NAB)</strong><br />
The broadcasters have been ordered to switch to digital transmission and return their analog channel spectrum back to the Government for auctioning. They are trying everything they can to delay this. They want to hang on to their analog spectrum as long as possible.  Broadcasters argue that giving up their analog spectrum in the foreseeable future will disenfranchise a significant number of viewers who depend only on over-the-air (OTA) analog reception, and are too economically disadvantaged to buy a DTV set.  It is hard to justify this position in light of the fact that over 85% of viewers' receive signals from Cable, DBS or other providers, and this percentage is growing.  In fact, most of the "poor" viewers are connected to Cable - at least Basic Cable. </p>

<p>However, there is some argument that a very small percentage of those who must depend on OTA reception may not be able to receive digital OTA signals with satisfactory reliability - if at all. Even though theoretical calculation of DTV OTA coverage assures Broadcasters equivalency with analog coverage, in fact that is not always going to be true. Most viewers can tolerate a fairy degraded analog TV picture in spite of the snow and/or ghosts.  Such equivalent degraded DTV signals may result in no signal at all or intermittent loss, blocking or freezing of the picture along with intermittent or loss of sound. This will be true particularly with indoor antenna reception. There will be some improvement in this as DTV tuners become more sophisticated, but the OTA DTV problems will not be totally eliminated.</p>

<p>Congress is in the process of establishing a "hard" cutoff date for analog service, probably around January 1, 2009. However, the NAB is a very powerfully lobby, and Broadcasters do not want to alter their analog business models even with an ever increasing deteriorating OTA share. Therefore, they will continue to try to delay the analog "cut-off" as long as possible in spite of their increasing irrelevancy   In fact, we may be seeing the start of the demise of OTA TV broadcasting as we have known it.</p>

<p><strong>Cable (NCTA)</strong><br />
Cable is presently in the "catbird seat."  Approximately 70% of the US households are connected to cable. Well over 50% of these viewers watch cable programming compared to network and local programming (also carried by cable). Cable is rapidly converting to digital transmission and offering digital premium packages.  These packages (or tiers) provide not only HDTV programs but also video-on-demand (VOD), broadband IP, and telephone (VoIP) services. In addition, an increasing number of HDTV manufacturers are including in many models fully "cable ready" capability with digital cable tuners and the cable security card interface ("CableCard").   </p>

<p>However, in spite of the CableCard adoption, Cable companies do not want to relinquish their integrated security set-top-box (STB) business model and allow an "open" design.  Such a design would allow any CE manufacturer to offer an STB.  With some justification, Cable is concerned about the security costs and feature capability of an open STB design. The FCC has granted Cable a delay of the date that manufacturers are permitted to make and market the open STB design.   Supposedly, Cable is also working on a design that will allow security algorithms to be downloaded to the STB.  Certainly, Cable does not want to lose control over their "gateway" mechanism, the STB. But again, like Broadcasters, the DTV transition will force them to change their business model.</p>

<p>Cable is also fighting hard to prevent a ruling that would force them to carry the local broadcasters' full digital multiplex, claiming insufficient bandwidth. They also want the flexibility to convert the broadcasters' HDTV signal to SDTV for the same reasons. However, that argument is week because most cable systems are offering HDTV as part of their digital tiers. They simply do not want any ruling that will interfere with their ability to offer a premium HDTV tier without carrying non-premium SDTV channels with the same programming.  In fact, these two issues are somewhat vacuous because retransmission agreements between local broadcasters and the local cable companies are rectifying these issues on a market-to-market basis. </p>

<p><strong>Consumer Electronics Manufacturers and Retailers (CEA)</strong><br />
The Consumer Electronics (CE) group would like the DTV transition to proceed as quickly as possible - but not so fast as to not allow an expeditious reduction in their present analog inventories.  CE has always seen a fantastic opportunity with DTV, HDTV in particular. The larger screen sizes required to optimize the HDTV viewing experience offer price-premium opportunities for CE.  Add to that the developing market for HDTV peripherals such as HDTV DVD's and Digital Video Recorders (DVR's) and the opportunities exponentially increase.</p>

<p>The FCC has ordered CE to provide digital tuners in 50% of all 25-36" (diag.) sets manufactured since July 1, 2005 and in 100% of these sets manufactured after March 1, 2006.  CE accepted this in spite of the inventory problem, because they do not want to be accused of delaying the transition.</p>

<p>CE's biggest threat is an obsolete Cable decoding standard. In order to accommodate an increasing number of HDTV programs, Cable is converting to the MPEG-4 codec standard, allowing for an up to 4:1 improvement in bandwidth conservation. Since the built-in Cable Ready decoders in CE sets will only decode MPEG-2, it is not clear how viable DTV sets will be in a future cable environment, except as monitors.</p>

<p>Cable and CE are also struggling with a second-generation cable interface standard that would allow advanced cable service offerings such as VOD, to be downloaded directly to the TV set or an open standard STB. The technical challenges of this are daunting, but progress is slowing being made.</p>

<p><strong>Programmers (MPAA)</strong><br />
The network programmers have been chastised by the Government for not providing sufficient, compelling HDTV programming to advance the DTV transition. CEA has been especially vocal relative to this issue, stating that without compelling HDTV programming, customers will not be interested in purchasing HDTV sets.  The programmers' position has been that there are not enough HDTV sets in the market to justify the additional HDTV production costs.  So, we have had a classic "chicken/egg" marketing block. However, the HDTV programming situation is now rapidly changing for the better. More and more cable programmers are providing HDTV offerings, forcing the traditional broadcast network to step-up to the challenge. More than 50% of prime-time network programming is now offered in HDTV with more and more non-prime-time and sports offerings on the way. This should rapidly drive HDTV set sales to the magical 20% household penetration level that defines the boundary between the "early adopter" and the "commodity" consumers. That penetration level should occur next year as the projected percentage of household receiving HDTV in 2005 is already at approximately 13%.  At the 20% penetration level HDTV sales will increase exponentially, giving programmers increased economic justification to product HDTV programming.</p>

<p>However, one significant obstacle looms in the way of providing increased compelling HDTV programming - the issue of adequate content protection. MPAA has a very high stake in the DTV transition relative to assuring against real-time theft of high quality productions. The full potential of HDTV cannot be realized until this issue is satisfactorily resolved for broadcast, cable, DVD, games and other HDTV sources. It will be necessary to implement recording rules and adequate security for all HDTV program sources in order for HDTV to move to the next level. The HDMI interface goes a long way in providing content protection between the STB and the HDTV display monitor, but much remains to be done. </p>

<p><strong>U.S. Government</strong><br />
The "big stick" wants the broadcasters' analog spectrum back so they can auction in off for several billion dollars. One of the primary reasons to switch to digital transmission is that the lower DTV power levels for a given coverage area allow the channels to be located ("packed") closer to each other. This frees-up a block of bandwidth that can be auctioned. In addition, this available extra spectrum is in demand for homeland security purposes. A tertiary reason is the public expectation of HDTV. The whole national advanced television program was sold on this premise, and after 15 years, the public is getting somewhat impatient.  </p>

<p>That leads us to the final, unofficial, but most important stakeholder - that's us, the consumer and ultimate beneficiary of HDTV. The potential for high definition television to advance our entertainment pleasure and information level cannot be overestimated. Other than the obvious commercial and scientific value of high quality video, the fact that a near perfect view of the world can be had in our homes is a very powerful thing indeed. </p>

<p>Ed  </p>

<p><br />
___________________<br />
<strong>About Ed Milbourn</strong><br />
After graduating from Purdue University with degrees in Electrical Engineering and Industrial Education in 1961 and 1963 respectively, Ed Milbourn joined the RCA Home Entertainment Division in 1963. During his thirty-eight year career with RCA (later GE and Thomson multimedia), Mr. Milbourn held the positions of Field Service Engineer, Manager of Technical Training and Manager of Sales Training. In 1987, he joined Thomson's Product Management group as Manager of Advanced Television Systems Planning, with responsibilities including Digital Television and High Definition Television Product Management. Mr. Milbourn retired from Thomson multimedia in December 2001, and is now a Consumer Electronics Industry consultant.<br />
</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Ed Milbourn</b>, <b>July 31, 2005 04:43 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 170
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
 				AND entry_id <> 170
 				AND entry_status = 2
 				AND entry_author_id = a.author_id
 				AND a.author_name = 'Ed Milbourn'
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
 				<h2>About Ed Milbourn</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2005/07/eds_view_-_the_hdtv_transition.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
