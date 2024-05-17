<?
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1237";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Rodolfo La Maestra'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1237 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category[placement_category_id];
	
	# Still need this to track page views. Used in the Links unit in the footer
	$google_links_channel = $GOOGLE_CHANNEL['Rodolfo La Maestra'];

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
			break;
		case 7: # Bulletins
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
	<meta name="keywords" content="guide part, viewing objectives, hdtv buyers, buyers guide, def dvd, HDTV, hdtv, viewing, set, need, still, might, audio, etc, Guide, guide, room, part, identify, Part, confirm, technology, time, much, satellite" />
	<meta name="description" content="The second in a four-part series of articles on buying an HDTV. The following topics are covered in this segment:

Decision to Go with HDTV 
Research 
The HDTV for Your Viewing Objectives 
Your Consumer Electronic Habits" />
	<title>HDTV Magazine Articles - 2008 HDTV Buying Guide, Part 2</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
#		$pagetag = "NTPT_PGEXTRA = 'author=". $AUTHOR_ID['Rodolfo La Maestra'] ."';\n";
#		include(BASE_DIR .'/includes/tracker.php');
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/2008_hdtv_buying_guide_part_2';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('2008 HDTV Buying Guide, Part 2'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/articles/2008/02/2008_hdtv_buying_guide_part_2.php";
		if (array_key_exists('Rodolfo La Maestra', $AUTHOR_PORTRAIT) && 1 <> 7) {
			$img = '<img src="'. $AUTHOR_PORTRAIT['Rodolfo La Maestra'] .'" alt="Rodolfo La Maestra" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">2008 HDTV Buying Guide, Part 2</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Rodolfo La Maestra</b><br />
				<?=$author_title?>
				Posted on <b>February  7, 2008</b><br />
				Category: <b><a href="/category.php?category=Marketplace&id=<?=$category_id?>">Marketplace</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2008/02/2008_hdtv_buying_guide_part_2.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/articles/2008/02/2008_hdtv_buying_guide_part_2.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/articles/2008/02/2008_hdtv_buying_guide_part_2.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
				<span><img src="/images/save.gif" alt="Save Article" align="absmiddle" /><a target="_blank" href="<?=$save_url?>">Save</a></span>
				<span><img src="/images/email.gif" alt="Email Article" align="absmiddle" /><a href="<?=$email_url?>">Email</a></span>
				<span><img src="/images/print.png" alt="Print Article" align="absmiddle" /><a target="_blank" href="<?=$print_url?>">Print</a></span><br />
				<br /><br />
			</td>
		</tr>
	</table>
	<?if ($userdata[subscriptions] & $sub_type) {} else {
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2008/02/2008_hdtv_buying_guide_part_2.php&amp;phase=2&amp;title=2008%20HDTV%20Buying%20Guide%2C%20Part%202&amp;bodytext=The%20second%20in%20a%20four-part%20series%20of%20articles%20on%20buying%20an%20HDTV.%20The%20following%20topics%20are%20covered%20in%20this%20segment%3A%0A%0ADecision%20to%20Go%20with%20HDTV%20%0AResearch%20%0AThe%20HDTV%20for%20Your%20Viewing%20Objectives%20%0AYour%20Consumer%20Electronic%20Habits&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
			} else {
				echo '<script src="http://digg.com/api/diggthis.js"></script>';
			}
		?></div>
		<div style="float:right; margin:0 0 5px 5px;">
			<?include(BASE_DIR .'/ads/mrectangle.php');?>
		</div>
		<div style="clear:right; float:right; margin:0 0 5px 5px;">
			<?include(BASE_DIR .'/ads/skyscraper.php');?>
		</div>
		<div id="<?=$container?>">
			<div class="editorial">The following article is the latest in the 2008 HDTV Buyers Guide series. Other articles in this series are as follows:
<ul>
<li><a href="/articles/2008/02/2008_hdtv_buyers_guide_part_1.php">2008 HDTV Buyers Guide, Part 1</a></li>
<!--li><a href="/articles/2008/02/2008_hdtv_buyers_guide_part_3.php">2008 HDTV Buyers Guide, Part 3</a></li-->
<li>2008 HDTV Buyers Guide, Part 3 (Feb 11th)</li>
<!--li><a href="/articles/2008/02/2008_hdtv_buyers_guide_part_4.php">2008 HDTV Buyers Guide, Part 4</a></li-->
<li>2008 HDTV Buyers Guide, Part 4 (Feb 13th)</li>
</ul></div>
<br />
<p>The following topics are covered in this segment:</p> <ul> <li>Decision to Go with HDTV</li> <li>Research</li> <li>The HDTV for Your Viewing Objectives</li> <li>Your Consumer Electronic Habits</li></ul> <h2>Decision to Go with HDTV</h2> <p>Confirm that now is your time for an HDTV, and that your family is ready for the change if the set is for everyone to use.  <p>Identify the major pieces of equipment you plan to acquire. Maybe you need only the HDTV, maybe you need also a progressive DVD player, or a Hi-Def DVD player, a STB for satellite, an HD-DVR, etc.  <p>Would you need any audio upgrades/purchases if the HDTV set would be connected to an external audio system?  <p>Set a preliminary itemized budget based on the initial target above, and include an estimate for the cost of cables, mounting hardware, delivery, installation, etc.  <h3></h3> <h2>Research</h2> <p>Review HDTV concepts and exchanges from online forums and magazines and be patient while learning terminology that seems confusing first (progressive, 720p, 1080i, 1080p, scaling, DVI, <a href="http://www.hdtvmagazine.com/glossary.php#HDMI" target="_blank">HDMI</a>, etc).  <p>Visit knowledgeable HDTV stores and do not be surprised if you get more confused with verbal explanations that seem to contradict what you might have read already from reliable sources, many HDTV salespeople are still in training, take your time in reconciling the inputs.  <p>Many early adopters did not have the benefit of all the HDTV information available today. If you have the time, you have now at your feet many sources to inform yourself and research in order to make an intelligent purchase. Do not make the mistake of using the approach of buying a 13'' TV for the kitchen.  <p>Learn how to identify the differences in technology (<a href="/glossary.php#DLP" target="_blank">DLP</a>, Plasma, <a href="/glossary.php#LCD+%28Liquid+Crystal+Display%29" target="_blank">LCD</a>, <a href="/glossary.php#LCoS+%28Liquid+Crystal+on+Silicon%29" target="_blank">LCoS</a>, etc). Confirm your understanding of the differences by actually viewing some sets representing each technology in various sizes, distances, angles, and lighting conditions. Not all technologies would be as effective for all room conditions, some are perfect for a beach apartment but they emit too much light for a dark room, some require a totally dark room.  <p><u></u> <p><u></u> <p><u></u> <h2>The HDTV for Your Viewing Objectives</h2> <p><b></b> <p>Once you comprehend the differences and capabilities of DTV technologies, you would have to concentrate on how they could apply to your viewing objectives. Evaluate if you would need a 42" plasma panel suitable for a studio or a 120" front projection screen for a home-theater in the basement.  <p>Does your viewing room have uncovered windows all the time? This could be too much light for a front projection TV or a plasma panel. Do you have enough depth on the room to allow for approximately 1-2 feet of depth of a rear projection TV cabinet, and still have enough distance between the screen and yourself to adjust your viewing position?  <p>Appraise your "preferred" viewing distance and screen size for your viewing objectives (SD or HD). Identify also the absolute minimum and maximum of the viewing distance range that your room would physically permit and still give you a pleasant viewing experience. You would need to use these limits while testing the viewing positions at the store, but many national-wide electronic stores demo DTV panels on narrow corridors with TV stands on both sides, not giving enough viewing depth to fully put that in practice.  <p>Due to the higher resolution of HDTV images they would permit you to sit much closer to the screen than what you are accustomed with regular analog TV, but remember that you might have to use the same set to also view a lot of non-HDTV material, which does not have enough resolution to sit as close as an HD image would allow.  <p>Identify the cable/satellite/over-the-air HD reception services available on your area. Ask for their HD plans on the near future, the adoption of HDTV by the cable industry is growing every year but it might not be the case for your area.  <p>Consider some factors that you might not be able to control, for example, a satellite dish's line of sight might be blocked by tree lines that can not be trimmed, UHF antennas might be a problem for your terrain/location/house/surrounding city buildings, etc.  <p>Inform yourself of the FCC's antenna regulations on their web site if your Home Owners Association (HOA) is rejecting the installation of your exterior antenna, the FCC is on your side and has authority over HOA's neighborhood regulations, like the small dish regulations a few years ago.  <p>Anticipate how much of non-HDTV 4x3 content you would still watch on your new HDTV set over the next few years. Although many new channels of cable and satellite are in HDTV, many are, and still could be for long, in digital 480i 4x3. Evaluate if your viewing will primarily be for 4x3 SD or for HDTV content or HD pre-recorded Hi-Def DVD movies.  <p>Review the equipment plan that you identified initially. Confirm if the HDTV would be used either as a stand-alone set, or as the centerpiece of a home-theater audio/video multi-channel system. If so, confirm also the need for any changes or upgrades of your current audio setup, such as replacing a limited A/V receiver by one with multi-channel audio for movies, and additional speakers, and with possibly hi-bit audio decoders for Hi-Def DVD soundtracks, such as <a href="/glossary.php#DTS-HD+%28%2B%2B%2C+and+Master+Audio%29" target="_blank">DTS-HD Master Audio</a> or <a href="/glossary.php#Dolby+TrueHD" target="_blank">Dolby TrueHD</a>, and HDMI 1.3 to allow for that, etc.  <p><u></u> <p><u></u> <h2>Your Consumer Electronic Habits</h2> <p><b></b> <p>HDTV is still haunted by many issues that are still evolving, such as digital connectivity, copy protection, recording ability, specs that are growing, updates that require hardware replacement rather than firmware updates, etc., those have the potential to make your purchase and upgrade path more confusing and frustrating.  <p>Some people replace TV sets only after they expire, others like to upgrade often to been able to experience new versions, these usually have their wallets regularly dominated by minor technology changes. The cost of owing HDTV/s within a 10-year period would be different for each case, identify which is your case and how that affects your budget and/or selection now and in the long term.  <p>The HDTV technology evolves constantly and you need to be tolerant if you get surprises of discontinued products two months after you bought the set. Some manufacturers replace their lines 2 to 3 times per year just to add some minor features.  <p>If you are planning to upgrade your new HDTV in a couple of years anyway maybe it is not that important to select a first one that has absolutely all of the future-proof features for long term performance and compatibility, or to wait for those features to become available in the set you like, such as HDMI 1.3 digital connectivity for example (a set with other versions of HDMI, or even just DVI, might be all you need for now). By evaluating your consumer electronics habits and long term cost of ownership you may confirm the selection and features with more clarity.  <p>&nbsp; <p>Please stay tuned for the next article in the series: 2008 HDTV Buying Guide, Part 3</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Rodolfo La Maestra</b>, <b>February  7, 2008 09:25 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1237
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

			<!-- Related Articles by Category -->
			<div class="item"><span class="corners-top"><span></span></span>
				<h2>More on Marketplace</h2>
   				<ul class="brownsquare">
					<li><a href="http://www.hdtvmagazine.com/articles/2008/02/2008_hdtv_buyers_guide_part_1.php">2008 HDTV Buyers Guide, Part 1</a> - <span class="grey">Rodolfo La Maestra</span> - Feb  5,  8:02AM</li>
				
					<li><a href="http://www.hdtvmagazine.com/articles/2007/11/eds_view_-_hdtvs_9-11.php">Ed's View - HDTV's 9-11</a> - <span class="grey">Ed Milbourn</span> - Nov 30,  7:11AM</li>
				
					<li><a href="http://www.hdtvmagazine.com/articles/2007/04/eds_view_-_mobile_digital_television_mdt_this_is_a_winner.php">Ed's View - Mobile Digital Television (MDT) This is a Winner</a> - <span class="grey">Ed Milbourn</span> - Apr 12, 12:04PM</li>
				
					<li><a href="http://www.hdtvmagazine.com/articles/2006/05/displaysearch_reports_global_lcd_tv_shipments_rise_135_in_q1.php">DisplaySearch Reports Global LCD TV Shipments Rise 135% in Q1</a> - <span class="grey">Dale Cripps</span> - May 24,  5:05PM</li>
				
					<li><a href="http://www.hdtvmagazine.com/articles/2006/04/its_a_big_big_mitsubishi_world.php">It's a Big, Big Mitsubishi World</a> - <span class="grey">Dale Cripps</span> - Apr 10,  6:04PM</li>
				
					<li><a href="http://www.hdtvmagazine.com/articles/2006/03/hdtv_technology_review_part_1_introduction.php">HDTV Technology Review, Part 1: Introduction</a> - <span class="grey">Rodolfo La Maestra</span> - Mar 27, 12:03PM</li>
				
					<li><a href="http://www.hdtvmagazine.com/articles/2006/02/interview_-_mark_knox_toshiba_on_hd_dvd.php">Interview - Mark Knox, Toshiba on HD DVD</a> - <span class="grey">Dale Cripps</span> - Feb 22, 10:02PM</li>
				
					<li><a href="http://www.hdtvmagazine.com/articles/2006/01/buying_an_hdtv_how_to_be_prepared_to_select_the_one_for_you.php">Buying an HDTV?  How to be prepared to select the one for you</a> - <span class="grey">Rodolfo La Maestra</span> - Jan 16,  1:01PM</li>
				
					<li><a href="http://www.hdtvmagazine.com/articles/2006/01/eds_view_-_ces_2006_trends.php">Ed's View - CES 2006 Trends</a> - <span class="grey">Ed Milbourn</span> - Jan 11,  1:01PM</li>
				
					<li><a href="http://www.hdtvmagazine.com/articles/2006/01/ces_-_day_4.php">CES - Day 4</a> - <span class="grey">Shane Sturgeon</span> - Jan  8,  9:01PM</li>
				</ul>
			<span class="corners-bottom"><span></span></span></div>
		
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
				ORDER BY entry_created_on DESC";
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
				SELECT entry_id, entry_blog_id, entry_created_on, entry_title, entry_basename, author_id, author_name
				FROM mt_entry e, mt_author a
				WHERE entry_blog_id = 1
					AND entry_id <> 1237
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2008/02/2008_hdtv_buying_guide_part_2.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
