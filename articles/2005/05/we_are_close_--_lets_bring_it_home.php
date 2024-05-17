<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 5";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Dale Cripps'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 5 AND placement_is_primary = 1";
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
	<meta name="keywords" content="marty franks, hdtv programming, cut date, hdtv content, dtv academy, HDTV, hdtv, CBS, cbs, dtv, DTV, years, today, been, analog, consumers, must, cea, transition, CEA, here, broadcast, success, date, programming" />
	<meta name="description" content="Even as CEA president Gary Shapiro was urging Congress to pass legislation for a &quot;certain&quot; cut off date for analog terrestrial broadcast services (thus ending broadcasting as we have known it) he offered high praise to CBS and their affiliates for their exemplary work in HDTV. His remarks were given at the annual CBS NAB Engineering breakfast where affiliate stations are brought up-to-speed on what CBS did during the last year and what the outlook is for the next. Shapiro's remarks, made in Las Vegas, are presented here unedited in order that you may understand and gain a greater sense of appreciation for the strong influences shaping this movement. _Dale Cripps


CBS Breakfast
NAB2005

Good morning!
Thank you for inviting me to speak to you all this morning. I am honored to be here today, celebrating HDTV with the network that has believed in HDTV and has backed HDTV where it counts  with HDTV content! And not just any HDTV content  compelling, original HDTV programming that has spurred more consumers to embrace this dazzling technology. The NCAA tournament, the Masters, Monday Night Football, the SuperBowlYou know the list and I join millions of sports fans in thanking you and the manufacturers who helped pay for it.
" />
	<title>HDTV Magazine Articles - We Are Close -- Let's Bring it Home</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/we_are_close_--_lets_bring_it_home';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('We Are Close -- Let\'s Bring it Home'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/articles/2005/05/we_are_close_--_lets_bring_it_home.php";
		if ($author[img] != '' && 1 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Dale Cripps" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">We Are Close -- Let's Bring it Home</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Dale Cripps</b><br />
				<?=$author_title?>
				Posted on <b>May 12, 2005</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Politics & Policy">Politics & Policy</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2005/05/we_are_close_--_lets_bring_it_home.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/articles/2005/05/we_are_close_--_lets_bring_it_home.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/articles/2005/05/we_are_close_--_lets_bring_it_home.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2005/05/we_are_close_--_lets_bring_it_home.php&amp;phase=2&amp;title=We%20Are%20Close%20--%20Let%27s%20Bring%20it%20Home&amp;bodytext=Even%20as%20CEA%20president%20Gary%20Shapiro%20was%20urging%20Congress%20to%20pass%20legislation%20for%20a%20%22certain%22%20cut%20off%20date%20for%20analog%20terrestrial%20broadcast%20services%20%28thus%20ending%20broadcasting%20as%20we%20have%20known%20it%29%20he%20offered%20high%20praise%20to%20CBS%20and%20their%20affiliates%20for%20their%20exemplary%20work%20in%20HDTV.%20His%20remarks%20were%20given%20at%20the%20annual%20CBS%20NAB%20Engineering%20breakfast%20where%20affiliate%20stations%20are%20brought%20up-to-speed%20on%20what%20CBS%20did%20during%20the%20last%20year%20and%20what%20the%20outlook%20is%20for%20the%20next.%20Shapiro%27s%20remarks%2C%20made%20in%20Las%20Vegas%2C%20are%20presented%20here%20unedited%20in%20order%20that%20you%20may%20understand%20and%20gain%20a%20greater%20sense%20of%20appreciation%20for%20the%20strong%20influences%20shaping%20this%20movement.%20_Dale%20Cripps%0A%0A%0ACBS%20Breakfast%0ANAB2005%0A%0AGood%20morning%21%0AThank%20you%20for%20inviting%20me%20to%20speak%20to%20you%20all%20this%20morning.%20I%20am%20honored%20to%20be%20here%20today%2C%20celebrating%20HDTV%20with%20the%20network%20that%20has%20believed%20in%20HDTV%20and%20has%20backed%20HDTV%20where%20it%20counts%20%C2%96%20with%20HDTV%20content%21%20And%20not%20just%20any%20HDTV%20content%20%C2%96%20compelling%2C%20original%20HDTV%20programming%20that%20has%20spurred%20more%20consumers%20to%20embrace%20this%20dazzling%20technology.%20The%20NCAA%20tournament%2C%20the%20Masters%2C%20Monday%20Night%20Football%2C%20the%20SuperBowl%C2%85You%20know%20the%20list%20and%20I%20join%20millions%20of%20sports%20fans%20in%20thanking%20you%20and%20the%20manufacturers%20who%20helped%20pay%20for%20it.%0A&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p>Even as CEA president Gary Shapiro was urging Congress to pass legislation for a "certain" cut off date for analog terrestrial broadcast services (thus ending broadcasting as we have known it) he offered high praise to CBS and their affiliates for their exemplary work in HDTV. His remarks were given at the annual CBS NAB Engineering breakfast where affiliate stations are brought up-to-speed on what CBS did during the last year and what the outlook is for the next. Shapiro's remarks, made in Las Vegas, are presented here unedited in order that you may understand and gain a greater sense of appreciation for the strong influences shaping this movement. _Dale Cripps</p>

<p><br />
CBS Breakfast<br />
NAB2005</p>

<p>Good morning!<br />
Thank you for inviting me to speak to you all this morning. I am honored to be here today, celebrating HDTV with the network that has believed in HDTV and has backed HDTV where it counts  with HDTV content! And not just any HDTV content  compelling, original HDTV programming that has spurred more consumers to embrace this dazzling technology. The NCAA tournament, the Masters, Monday Night Football, the SuperBowlYou know the list and I join millions of sports fans in thanking you and the manufacturers who helped pay for it.</p>

<p>So, here we are, gathered together during NAB 2005. It boggles the mind to consider that just a few years back, CEA and ATSC exhibited at this show just to ensure broadcasters saw what an HDTV looked like! Today, HDTV is not only prominently displayed and available at retail  including in Wal-mart  its gone beyond TV sets. Today HD is camcorders, DVRs, games  you name it. The spread of HD technology to other devices demonstrates how far down the road to DTV we really are today.</p>

<p>We are so far down the road that numerous policymakers are supporting proposals to establish a hard cut-off date for analog transmissions. Four out of four members of Congress who spoke at CEAs 10th annual HDTV Summit last month agreed its time to set a hard date. In fact, the whole day was dominated by discussion and overwhelming agreement that its time.</p>

<p>As we pondered the end of analog at our summit this year, I presented a retrospective of the transition in my opening remarks. And as I prepared my comments, I researched who said what along the way. Who hypothesized HDTVs success and who damned it.</p>

<p>Im sure it will come as no surprise to most of you in this room that CEA and CBS were lonely voices in the beginning. Marty Franks once characterized the early days as like being lonesome on the edge. Thankfully, today, we are leading a chorus.</p>

<p>Actually, your own Dr. Joe Flaherty has been evangelizing HDTVs ultimate industry and consumer adoption for more than 20 years. He told Broadcast Engineering back in June 1982 that HDTV would see early adoption by the broadcast industry and acceptance by the public within five years, and certainly not more than 10 years. And sure enough, just 6 years into HDTV set sales, American consumers have gobbled up more than 16.5 million products. And it wont be long before HDTVs out-sell analog TVs. More consumers than ever before say their next TV purchase will be an HDTV.</p>

<p>But the success of HDTV is little surprise to us. CEA and CBS have both been outspoken about the wonders of HDTV and its ultimate success. Manufacturers have been making phenomenal HDTV sets for years, but we all know that without dazzling content to enjoy on those sets, youve just got a box. CBS has been at the forefront of HDTV content.</p>

<p>In fact, CBS has been so aggressive in its HDTV programming that it has been recognized by the Academy of Digital Television Pioneers five years running in numerous awards categories. I see many of those DTV Academy Pioneers in the audience today, in fact.</p>

<p>I was delighted to honor Marty Franks recently at CEAs 10th annual HDTV Summit in Washington last month. He came to receive the latest CBS awards bestowed by the DTV Academy  Best Over-the-Air DTV Network, Best DTV Sporting Event (for coverage of Superbowl XXXVIII) and Best Original DTV Material (CSI ).</p>

<p>And it isnt just the DTV Academy that has recognized CBSs leadership in HDTV. Broadcasting and Cable wrote in 2000 that, If HDTV ever blossoms as a broadcast service, CBS will deserve much of the credit.</p>

<p>Here, here.</p>

<p>Yes, CBS got it when it came to HDTV practically from day one.</p>

<p>Marty Franks saw as early as 1999 that broadcastings future lay with HDTV. In fact in 2001, he told Electronic Media CBS and the local stations ''can do a better job of selling it'' in high-traffic venues and coming up with creative ideas for advertising and promoting the high-definition format. He knew back then that the ability to transmit HD programming was the one current digital business plan capable of generating a profit.</p>

<p>At the same time, CEA was also fueling the transition by helping to fund and open the Model HDTV Station in Washington and independently funding and launching an online antenna selector website  antennaweb.org  in order to provide consumers with information about over-the-air DTV reception. This resource has been available and promoted to consumers for more than 4 years. Site hits range from 150,000 to 200,000 a month.</p>

<p>So, where are we today? Well, Americans have embraced digital television faster than they took to color TV. We reached 15 percent household penetration with DTV products in 6 years, whereas it took 10 years to reach just 5 percent for color TV. And the future is even brighter. More and more consumers are familiar with HDTV and DTV than ever before.</p>

<p>High Definition, indeed, has gone mainstream.</p>

<p>But even as we celebrate HDTVs success, we face challenges that I want to enumerate again today to this crowd of HDTV believers.</p>

<p>· <strong>Broadcasters must deliver their DTV broadcasts at full transmission power.<br />
</strong><br />
· <strong>Cable operators must retransmit broadcast DTV signals in the same format in which they are delivered over-the-air. HDTV programs should be retransmitted in HD, not in a lower-quality format.<br />
</strong><br />
· <strong>Consumer rights must not be impeded. We must maintain an equitable balance between the legitimate rights of copyright holders and consumers fair use rights to record HDTV programming.<br />
</strong><br />
· <strong>Promote. Promote. Promote. Broadcasters, cable programmers and all involved in the digital transition should use our collective voice to extol the benefits of HD.<br />
</strong><br />
· <strong>A hard analog cut-off date must be established to define the end of analog.<br />
</strong></p>

<p>I congratulate CBS for its HDTV commitment, investment and hard work. You should be proud of your contributions to the overwhelming success of HDTV and the overall transition. But I encourage you, as leaders in this transition, to set the example for others in broadcasting, by embracing my prescriptions for the swift completion of analog. We are this close  lets bring it home!</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Dale Cripps</b>, <b>May 12, 2005 11:07 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 5
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
 				AND entry_id <> 5
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2005/05/we_are_close_--_lets_bring_it_home.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
