<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 129";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Dale Cripps'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 129 AND placement_is_primary = 1";
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
	<meta name="keywords" content="high definition, prime time, production program, program exchange, program production, high, definition, production, programs, television, program, HDTV, hdtv, transmission, electronic, film, system, Europe, europe, worldwide, today, world, distribution, digital, time" />
	<meta name="description" content="By Joseph A. Flaherty CBS, Inc. High definition television is the latest major advance in world telecommunications. Changes in technology, changes in culture, changes in our perception of the world, all taking place more rapidly with every passing day, are..." />
	<title>HDTV Magazine Archive &amp; History - 1991 - A World Of Change By Joseph A. Flaherty</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/1991_-_a_world_of_change_by_joseph_a_flaherty';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('1991 - A World Of Change By Joseph A. Flaherty'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/history/2005/06/1991_-_a_world_of_change_by_joseph_a_flaherty.php";
		if ($author[img] != '' && 5 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Dale Cripps" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">1991 - A World Of Change By Joseph A. Flaherty</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Dale Cripps</b><br />
				<?=$author_title?>
				Posted on <b>June 26, 2005</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category="></a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/2005/06/1991_-_a_world_of_change_by_joseph_a_flaherty.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/history/2005/06/1991_-_a_world_of_change_by_joseph_a_flaherty.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/history/2005/06/1991_-_a_world_of_change_by_joseph_a_flaherty.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/2005/06/1991_-_a_world_of_change_by_joseph_a_flaherty.php&amp;phase=2&amp;title=1991%20-%20A%20World%20Of%20Change%20By%20Joseph%20A.%20Flaherty&amp;bodytext=By%20Joseph%20A.%20Flaherty%20CBS%2C%20Inc.%20High%20definition%20television%20is%20the%20latest%20major%20advance%20in%20world%20telecommunications.%20Changes%20in%20technology%2C%20changes%20in%20culture%2C%20changes%20in%20our%20perception%20of%20the%20world%2C%20all%20taking%20place%20more%20rapidly%20with%20every%20passing%20day%2C%20are...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p>By Joseph A. Flaherty<br />
CBS, Inc.</p>

<p>High definition television is the latest major advance in world telecommunications. Changes in technology, changes in culture, changes in our perception of the world, all taking place more rapidly with every passing day, are the hallmark of our times and make the prediction of the future almost impossible. Certainly the future will look nothing like the past—it will look nothing like the present—and it will look nothing like what we today think the future will look like!</p>

<p>Nevertheless, we find in human nature a widespread aversion to, and fear of change, the necessary precursor of progress. This led the historian Elting Morrison to suggest: "It is possible, if one sets aside the long-run social benefits, to look upon invention as a hostile act—a dislocation of existing schemes, a way of disturbing the comfortable bourgeois routines and calculations."</p>

<p>This in turn led Secretary Adams to write in Smithsonian Horizons "There are some obvious lessons in all this. Inventions, especially visionary ones that disturb established patterns, rarely diffuse rapidly and successfully of their own accord...Centralized, bureaucratic managers and industrial giants are alike in too often being obstacles to, rather than sources of, broader perspectives. Enmeshed in present constraints, higher echelons fail to notice that most future problems and solutions lie outside of their sphere of influence."</p>

<p>Today we are addressing the next major problem in television—the change to high definition television. In this, there are two different aspects: the production, or making of programs and their worldwide distribution, and the transmission, or delivery of programs to the home viewer. Work on both program production and transmission is proceeding in Europe, Japan, and North America in different ways. In this article, I will cover both HDTV program production and program transmission, emphasizing the regional differences in approach, and the real need for global commonality.</p>

<p>Managing and directing change in technology can be achieved by setting certain goals and establishing certain standards, and thus bringing to our global village the benefits of this new technology. Standards have always been important to progress and to the development of a stable marketplace. If electronic HDTV program production and program exchange is to prosper, standards must make the exchange and distribution of programs worldwide technically simple and economically viable.</p>

<p>There are two ways to accomplish this. Either we need a single worldwide standard for high definition studio production and program exchange, or we need transparent and cost-effective high definition-to-high definition standards converters. Today we have neither, and HDTV electronic productions cannot be distributed worldwide! In fact, today we do not even have a transparent standards converter for converting 625-line signals to 525-line signals and vice versa. After 30 years of work, all converters including the latest designs, still produce serious artifacts in the conversion process.</p>

<p>Today, the only worldwide standard for high definition program production and program exchange is 35mm film, notwithstanding its poor motion portrayal (the wheels still go backwards). Nevertheless we muddle through, and 35mm film dominates the world's program exchange marketplace.</p>

<p>In the U.S., up to 90 percent of all prime time evening programs for all the commercial television networks have for 40 years been produced in high definition 35 mm film. This notwithstanding, we have never delivered a single frame of high definition to the home viewer. Consider for a moment the production of prime time programs for television broadcast. By prime time, we refer to the three to four hours in the evening when the largest audience is viewing, and when the programs of greatest appeal are presented. Figure 1 shows the types of programs involved. Series drama, such as "Dallas," occupies 50 percent of prime time, followed by telefeatures and feature films. Situation comedies, for the most part shot on electronic television cameras, are of course broadcast in the electronic medium. Only the 15 percent segment of news and sports programs is broadcast live.</p>

<p>With this massive base, the Hollywood film industry now provides 85 percent of the world's exports of cinema and television programs, for television, cable, cinema, DBS, and home video. In fact, program exports account for 36 percent of the revenues returned to the U.S. motion picture and television program producers, or $5 billion annually. These revenues are the second largest export of the United States, after defense products (Figure 2).</p>

<p>Figure 3 shows that the revenues from motion picture cinema distribution have remained nearly flat, in constant dollars, for some years, while the income from pay cable and home video, taken together, equalled the cinema revenues in 1985, and continues to grow. Today, distribution to the electronic media returns more revenue to the production studios than does the cinema box office. The trend continues and is expected to continue with the growth of DBS, cable, and private television worldwide.</p>

<p>Further, emphasizing the importance of this fact, Figure 4 analyzes all the sources of revenue for U.S. studios. The decline in the percentage of total revenues derived from the cinema is shown in the left hand column for each year. In the right hand column, home video alone now provides the biggest single source of revenue, followed by domestic television, foreign syndication television, and pay cable. Here note that the right hand column consists entirely of electronic distribution media, compared with the 24 percent of revenue provided by the last mechanical distribution medium—the cinema.</p>

<p>Figure 5 shows that the steady growth in exports derives entirely from the electronic distribution media. Over the last five years, these electronic program exports have grown at a rate of 55 percent annually.</p>

<p>The engine that produces these programs is large (Figure 6). Today Hollywood alone produces over 6000 programs annually, comprising 8000 hours of program time. Production time for this output is 36,000 days per year and occupies 210 stages, almost all of it in high definition. In Europe, the situation is entirely different. No large and viable indigenous film industry exists for the production of television programs. Thus, if 35mm film continues as the world's only standard for production and program exchange, it will assure the continued dominance of Hollywood in programs worldwide.</p>

<p>To compete in Europe, a massive investment in new high definition studios would be required before all the distribution channels could be filled with indigenous product. I believe in 1991 it is too late, and ill-advised, to try to duplicate such a monster 35mm television film production operation in Europe. Europe's strength, on the other hand, has been in electronic production for television, and its future strength will be in high definition electronic production—not film for television.</p>

<p>Moreover, as shown in Figure 7, the high and escalating cost of program production, where each episode of a serial drama, like "Dynasty," costs $1 million to $1.5 million, and a season's production of prime time programs can cost each network $400 to $600 million, demands international co-productions and worldwide marketing, to achieve a return on the production investment. This worldwide market has been the formula for Hollywood's success. For international co-production to succeed, programs must be saleable worldwide, and once again in electronic HDTV this can only be achieved by a single standard for production and program exchange, or by a transparent and cost-efficient standards converter.</p>

<p>Little, if any, progress is being made in either of these issues today. Lacking a solution, the creative community in electronic HDTV will be disadvantaged and its work relegated to a ghetto. On the other hand, high definition 35mm film programs occupy no such ghetto, and the U.S. situation is unique, because of the preponderance of 35mm film in prime time. Europe's stake in an HDTV production and distribution standard is greater than America's.</p>

<p>Thus, unlike other regions of the world, which must begin high definition program production with a large investment in electronic production equipment, the U.S. networks can convert the 75 percent of their prime time programming shot on high definition 35mm film, to widescreen high definition by merely deciding to do so.</p>

<p>This gives the U.S. an advantageous position, and enables it to make a rapid and efficient change to high definition, simply by arranging to shoot the film with a 16:9 wide screen aspect ratio.</p>

<p>No expensive conversion of existing studios is required initially. Programs may be shot on the same film using the same cameras as used today to shoot normal television programs. Thus a large proportion of the program schedule can be broadcast without an initial investment in electronic high definition studio equipment. Of course, the HDTV investment will involve network playback and distribution equipment.</p>

<p>Naturally, ultimate conversion will require a sizeable investment in high definition production equipment for the studios, but initial conversion to HDTV can be accomplished in the U.S. more quickly and inexpensively than in other regions of the world.</p>

<p><strong>Transmission and Delivery</strong></p>

<p>As to transmission and delivery to the home, Europe, North America, and Japan have all opted for a simulcast solution. That is, current normal signals will continue on today's channels, and high-definition will be delivered on a separate channel.</p>

<p>In Europe and Japan, HDTV will be delivered by satellite, and by cable or fiber. In the U.S., satellite and cable will play a role, but primary emphasis is placed on terrestrial transmission of HDTV by the 1420 independent television stations in the U.S. On these new channels, Japan and the U.S. have opted to go directly from current television to HDTV .</p>

<p>Alfred Sikes, chairman of the FCC has stated earlier this year: "Although we aspire to establish a simulcast system, the Commission believes it advisable to be fully apprised of all aspects of an enhanced definition system (EDTV), including its technical attributes, its consumer acceptance and its cost effectiveness. We do not envision, however, that the Commission would adopt an EDTV standard, if at all, prior to reaching a final decision on an HDTV standard, which, as I indicated above, will be made in the second quarter of 1993."</p>

<p>On the other hand, Europe has been pursuing a multi-step program, beginning with a 4:3 625-line enhanced system—D2MAC, through a wide screen 16:9 625-line system (WIDE MAC), and finally to a full HDTV system (HD-MAC).</p>

<p>It is believed in the U.S. that the market will not bear the cost of several such steps to HDTV. On September 6, 1990, Chairman Sikes made this comment: "Pursuing EDTV options would tend to maximize transition costs for both industry and consumers. Stations would need to make a series of sequential investments, as they inched towards full high definition operations. At the same time, however, consumers would almost certainly be confused, and would probably resist buying equipment which, in relatively short order, might be rendered obsolete."</p>

<p>The commercial marketplace philosophy of the U.S. encourages all interested parties to propose systems for terrestrial broadcast of HDTV. In 1989 there were 23 proponents, today there are six. One proposal is for an enhanced television system, ACTV-1, by Thomson-Sarnoff-NBC.</p>

<p>Five proposals are for HDTV systems from General Instrument, MIT, NHK, Thomson-Philips and Zenith. General Instrument proposes an all-digital system. MIT has proposed a hybrid, analog-digital system, but it is not fully designed. NHK offers a terrestrial MUSE analog transmission system. Thomson-Philips had an analog system, but it is widely rumored that they will soon propose an all-digital approach. Zenith offers a hybrid analog-digital system. All these systems will be tested in 1991-1992.</p>

<p>While all this might look confusing to the outside, America, like Darwin, believes in the survival of the fittest. Other regions of the world tend to pick a single solution and hope that the technology can be developed to support it.</p>

<p>Naturally, developers of all-digital transmission systems will encounter a great technical challenge to fit HDTV into a terrestrial 6 MHz channel, but an all-digital transmission standard for satellite transmission appears quite practical.</p>

<p>Surely we are in the twilight zone of analog transmission, and recognizing that transmission standards cannot be changed easily once adopted, should we not reconsider the setting of any analog or digitally-assisted standard, especially for satellite transmission? If General Instrument and Thomson-Philips can perfect an all-digital terrestrial transmission system in a 6 MHz channel, should not Europe consider the same for 27 MHz satellite channels?</p>

<p><strong>To conclude I offer three messages.</strong></p>

<p>1. First, it is essential for the creative community and for the free flow of information worldwide to find a common technique for high definition production and program exchange. This means a single world standard, or a high quality transparent standards converter in the high definition domain. If neither of these things occur, programs largely produced on 35mm film in the U.S. will remain the main source of programs on the world market.</p>

<p>2. Second, the global marketplace is not only a hardware market, but more importantly a software market, i.e., programs, language, culture and ideas—fields in which France has been a leader for centuries. High definition must not be only an industrial affair, it must also be the affair of the artist, for whom we engineers are but toolmakers.</p>

<p>3. Third, it is important to re-examine the analog or digitally assisted analog approach to HDTV transmission in favor of an all-digital transmission system for satellite and cable. This not only recognizes the certainty that digital transmission will come sooner than expected, but digital transmission could catapult Europe and its industry into a position of clear leadership as we approach the 21st century.</p>

<p>Knowing full well that these are not easy matters to resolve, keep in mind the words of Machiavelli in The Prince: "There is nothing more difficult to take in hand, more perilous to conduct or more uncertain of success, than to take the lead in the introduction of a new order of things, because the innovator has for enemies all those who have done well under the old conditions, and but lukewarm defenders in those who may do well under the new."</p>

<p> _________________________________________________________</p>

<p><br />
<em>Joseph Flaherty is senior vice president of technology at CBS. In this position, he advises CBS management on issues and strategies related to broadcast technology, and represents CBS nationally and internationally with major manufacturers and on government and industry committees and organizations. Flaherty joined CBS in 1957, and has directed the Engineering and Development Department since 1967—first as general manager, then, since 1977, as vice president and general manager. During his career, he has received many prestigious broadcast industry awards, including several Emmys for technical achievement; the David Sarnoff Gold Medal for progress in television engineering; the NAB Engineering Award; the Progress Medal of the SMPTE; and the International Montreux Achievement Gold Medal. Flaherty also received France's Chevalier de l'Ordre des Arts et des Lettres, and in 1985 was awarded France's highest decoration, the Chevalier de l'[Ordre National de la Legion d'Honneur, by French President François Mitterand. He is a Fellow of the British Institution of Electrical Engineers; the British Royal Television Society; and SMPTE. Flaherty holds a degree in physics and an honorary doctorate of science from Rockhurst College in Kansas City, Missouri.</em><br />
 </p>
		</div>
	</div>
	<p class="posted">Posted by <b>Dale Cripps</b>, <b>June 26, 2005 01:06 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 129
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
 				AND entry_id <> 129
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/2005/06/1991_-_a_world_of_change_by_joseph_a_flaherty.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
