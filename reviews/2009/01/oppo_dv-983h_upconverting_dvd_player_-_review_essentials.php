<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');

	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1625";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);

	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Richard Fisher'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";

	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1625 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category[placement_category_id];

	# Still need this to track page views. Used in the Links unit in the footer
	$google_links_channel = $author[channel];

	# Set variables based on entry type. NOTE: Podcasts has its own entry template, so it is not included amongst the choices below.
	switch (8) {
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
	<meta name="keywords" content="anchor bay, blu ray, dvd audio, multi channel, dvd player, OPPO, oppo, audio, DVD, dvd, video, player, analog, while, performance, While, remote, players, external, Audio, bay, Blu, hdmi, test, channel" />
	<meta name="description" content="Since 2006, OPPO has been providing a DVD performance envelope covering the main feature, the movie, at roughly the $200 mark directly competing with other players and external scalers costing $1000 plus. They have been earning my recommendation since then along with a full model line review last year. When it was announced that OPPO was releasing a flag ship DVD player at nearly double the price, I requested a review sample to find out how OPPO has raised the bar on an already very successful product line.

As with past models..." />
	<title>HDTV Magazine Reviews - OPPO DV-983H Upconverting DVD Player - Review Essentials</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
//		var federated_media_section = 'holiday';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/ads/leaderboard.php');
		include(BASE_DIR .'/includes/body_header.php');

		$base_url = strleftback(PHP_SELF, '/') . '/oppo_dv-983h_upconverting_dvd_player_-_review_essentials';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('OPPO DV-983H Upconverting DVD Player - Review Essentials'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/reviews/2009/01/oppo_dv-983h_upconverting_dvd_player_-_review_essentials.php";
		if ($author[img] != '' && 8 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Richard Fisher" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">OPPO DV-983H Upconverting DVD Player - Review Essentials</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Richard Fisher</b><br />
				<?=$author_title?>
				Posted on <b>January  7, 2009</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Upconverting DVD Players">Upconverting DVD Players</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/reviews/2009/01/oppo_dv-983h_upconverting_dvd_player_-_review_essentials.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/reviews/2009/01/oppo_dv-983h_upconverting_dvd_player_-_review_essentials.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/reviews/2009/01/oppo_dv-983h_upconverting_dvd_player_-_review_essentials.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/reviews/2009/01/oppo_dv-983h_upconverting_dvd_player_-_review_essentials.php&amp;phase=2&amp;title=OPPO%20DV-983H%20Upconverting%20DVD%20Player%20-%20Review%20Essentials&amp;bodytext=Since%202006%2C%20OPPO%20has%20been%20providing%20a%20DVD%20performance%20envelope%20covering%20the%20main%20feature%2C%20the%20movie%2C%20at%20roughly%20the%20%24200%20mark%20directly%20competing%20with%20other%20players%20and%20external%20scalers%20costing%20%241000%20plus.%20They%20have%20been%20earning%20my%20recommendation%20since%20then%20along%20with%20a%20full%20model%20line%20review%20last%20year.%20When%20it%20was%20announced%20that%20OPPO%20was%20releasing%20a%20flag%20ship%20DVD%20player%20at%20nearly%20double%20the%20price%2C%20I%20requested%20a%20review%20sample%20to%20find%20out%20how%20OPPO%20has%20raised%20the%20bar%20on%20an%20already%20very%20successful%20product%20line.%0A%0AAs%20with%20past%20models...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p> <table class="greygrid"> <tbody> <tr> <td style="font-weight: bold; text-align: center" colspan="4">Pricing at publication</td></tr> <tr> <td class="greygrid">&nbsp;</td> <td class="greygrid"><b>MSRP</b></td> <td class="greygrid"><b>Street</b></td> <td class="greygrid"><b>Amazon.com</b></td></tr> <tr> <td class="greygrid"><b>OPPO DV-983H</b></td> <td class="greygrid"><a href="http://www.oppodigital.com/dv983h/default.asp?partner=826" target="_blank">$399</a></td> <td class="greygrid"><a href="/equipment/model.php?a=B001DTJYYK&amp;man=OPPO%20Digital&amp;model=DV-983H" target="_blank">N/A</a></td> <td class="greygrid"><a href="http://www.amazon.com/OPPO-DV-983H-Universal-Up-Converting-DVD-Audio/dp/B001DTJYYK%3FSubscriptionId%3D083AT3X540E9EFH7SA02%26tag%3Dhdtvmagazine-20%26linkCode%3Dxm2%26camp%3D2025%26creative%3D165953%26creativeASIN%3DB001DTJYYK" target="_blank">N/A</a></td></tr></tbody></table> <p>Serial #: VD0803702169<br>Product Source: Manufacturer </p> <p><b>Summary: Good Videophile performance with all content</b> </p> <p><a href="http://www.oppodigital.com/dv983h/default.asp?partner=826" target="_blank"><img title="OPPO DV-983H" style="border-right: 0px; border-top: 0px; display: inline; margin: 0px 5px 5px 0px; border-left: 0px; border-bottom: 0px" height="72" alt="OPPO DV-983H" src="http://www.hdtvmagazine.com/images/articles/79c3ccb709d3_CE85/oppodv983h.jpg" width="300" align="left" border="0"></a> Since 2006, OPPO has been providing a DVD performance envelope covering the main feature, the movie, at roughly the $200 mark directly competing with other players and external scalers costing $1000 plus. They have been earning my recommendation since then along with a full model line review last year. When it was announced that OPPO was releasing a flag ship DVD player at nearly double the price, I requested a review sample to find out how OPPO has raised the bar on an already very successful product line.  <p>As with past models, you will need an HDMI input on your display to receive the full video benefits of the product. While OPPO decided to include analog component video outputs with this model, it is limited to 480p with most discs and while those discs that aren't flagged can be output up to 1080i, the Anchor Bay video processing is not used at all in that application. As with the DV-981HD, the flag ship player supports SACD and DVD Audio.  <p>On the surface the DV-983H doesn't appear to offer anything different from last year's DV-981HD, yet those differences are there, buried in the details. Along with links to the very well documented OPPO website are the following differences between the DV-981HD and the new <a href="http://www.oppodigital.com/dv983h/default.asp?partner=826" target="_blank">DV-983H</a>.  <h2>Features</h2> <ul> <li>VRS by Anchor Bay video processing technology  <li>Anamorphic Aspect Ratio for anamorphic 2.35 lens applications (not tested)  <li>Directly supports 6.1 Multi-channel audio Dolby Digital or DTS  <li>7.1 multi-channel audio via analog or HDMI  <li>Optimized High Fidelity Audio Circuit Design for the analog outputs  <li>Kodak Picture CD compatible - high resolution picture slide show  <li>USB 2.0 back panel connector supporting video, picture and music playback  <li>RS232 port for custom Home Theater installations  <li>IEC 14 gauge AC Power cord  <li>Heavy gauge black brushed aluminum front panel </li></ul> <h2>Not-So-Common Features for the DV-983H </h2> <ul> <li>Includes Anchor Bay Technologies video test disc  <li>PAL/NTSC disc and TV compatible with automatic or manual system conversion  <li>Analog Component Video up to 480p with CSS encrypted discs or up to 1080i without CSS  <li>8 channel analog audio outputs with 24 bit 192kHz D/A convertors  <li>Audio Only mode turns off the video circuits for high-resolution multi-channel digital audio output through HDMI or analog audio for CD, DVD-Audio and SACD  <li>Dimmer Control allows the front panel display to be turned off for improved analog audio fidelity  <li>Y/C Delay, on/off/auto CUE correction along with in depth De-interlacing Mode, Video Mode and Color Space besides basic picture controls  <li>Alternate Remote Control Code - allows other manufacturers DVD player controls to operate the OPPO </li></ul> <h2>Optional Accessories </h2> <ul> <li>External IR remote Sensor, IR-ES1 </li></ul> <h2>Opening the Box </h2> <p>Like past products the DV-983H was well packed with the player in a nice bag, a nice black OPPO box containing all the accessories along with a full sized manual and Anchor Bay Technologies test disc. The remote looks identical to past versions but for this model it is in black. While appearing identical, some of the buttons have different functions. Either way, do not expect much here. While certainly not hailing from the land of cheese, the remote is not back-lit and the button layout veers more towards a tabled layout with button shape similarity adding to the confusion. While it has glow-in-the-dark keys that won't help much once the glow has extinguished itself in your darkened room. I don't place too much emphasis on remotes though, as most folks use a system (universal) remote for everyday use.  <h2>Out of Box Performance </h2> <p>Hooking up the player to a <a href="http://www.hdtvmagazine.com/reviews/2007/07/benq_w10000_1080p_dlp_front_projector.php">BenQ W10000</a>, I found it preset for 16:9, adjusted the output for 1080p and ran the DVE test material. After watching the video performance tests and test patterns I was left scratching my head trying to figure out exactly what the improvement was. Based on all players tested thus far, the Achilles heal had always been material that was not properly captured or encoded, and more importantly 4:3 letterboxed presentations. With my fingers crossed I loaded The Poseidon Adventure (1972 4:3 letterboxed) in the tray pushed play, hit the zoom button and was greeted with a palatable presentation of this OAR, original aspect ratio 2.35 movie in 16:9 mode. On to objective testing.  <h2>Problems </h2> <p>Towards the very end of my time with the OPPO, the HDMI kept resetting itself going through a handshaking routine. Oddly enough this cleared up after returning to the machine a few days later.  <h2>Service </h2> <p>This is one of those rare moments where I can report from direct experience. OPPO is great. I lost my DV-971HD during the warranty period. I called them up explaining I was a service center and they sent me a part! While it did not resolve the problem they deserve kudos for providing that potential convenience. I ended up having to ship it back but lo and behold they offer a prepaid service so I could simply order one and send the old one back for credit. If I was needy I could have also had them overnight one, naturally at my expense. Now that is service!  <h2>Putting It in Perspective </h2> <p>While the DV-983 may not be as refined and detailed as the Toshiba HD-A35, that is not its strong suit. The key to this player is how it handles all the other stuff on your disc besides the movie; special features, TV shows, anime and letterboxed titles. In essence OPPO is delivering all the capabilities of an external scaler for only $170 more than their DV-981HD and about $600-400 less than an external scaler. While not quite as refined as a $1000 plus external scaler, it provides a quality solution for DVD collectors who want that kind of capability for all the material they own and don't seek the ultimate in performance along with the ultimate performance price. Based on the viewing environments and habits of most folks, the refining difference won't be seen anyway and the DV-983H can easily be perceived as $399 worth of videophile gold! With this capability the DV-983H fills a niche that very few players (if any) can touch regardless of price.  <p>For general everyday audio performance, using an HDMI equipped receiver accepting linear PCM or analog multichannel inputs you have access to thousands of HD audio titles. If you are an audiophile though you can do far better and this is not the right product for such a demanding application.  <p>With Blu-ray players hitting the market that can also play your DVDs, do you really need yet another box, remote and available connection to deal with? If you want the external scaling "I can handle it all" DV-983H solution then the only answer is yes.  <p>If the movie is your only concern, then a Blu-ray player is worthy of your attention. In my opinion, OPPO needs to get involved with the Blu-ray format or they will be left with great SD DVD players that fulfill only half a need. OPPO has been working on the BDP-83 using the same Anchor Bay video processing provided here for DVD along with Blu-ray plus DVD Audio and SACD support listed as "Coming Soon" on their site.  <h2>Conclusion </h2> <p>OPPO has given other far better-known manufacturers a great deal of competition with their past players and the DV-983H ups the ante significantly, but it is a niche player. If all you care about is the main feature, the DV-981HD performs just as well. If you are picking nits you can find a single hair improvement and save yourself $170 or even spend that difference for a Blu-ray player instead. If you want it all with every bit of content on your shiny DVD discs then the DV-983H is the bargain player of the year that will provide videophile nirvana for every single minute of that content. Along with that you get multi-channel digital support or 8 decent analog outputs for SACD and DVD Audio. This product comes highly recommended for the DVD collector and their vast library along with the variety of mastering that naturally comes with that!</p> <p></p> <p></p> <p>Stay tuned tomorrow for the remainder of this review where we put the <a href="/reviews/2009/01/oppo_dv-983h_upconverting_dvd_player_-_on_the_test_bench.php">OPPO DV-983H "On the Test Bench"</a>.</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Richard Fisher</b>, <b>January  7, 2009 03:15 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 1625
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
 			<h2>More on Upconverting DVD Players</h2><ul><?
 			$sql = "
 			SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name
 			FROM mt_entry e, mt_author a, mt_placement p, mt_category c
 			WHERE entry_author_id = author_id
 				AND e.entry_id = p.placement_entry_id
 				AND p.placement_category_id = c.category_id
 				AND category_label = 'Upconverting DVD Players'
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

 		<?if (8 <> 7) {
 			# Recent Articles by Author (exclude this one)
 			# Do not show recent articles for Bulletins.
 			$qry = "
 			SELECT entry_id, entry_blog_id, entry_created_on, entry_title, author_id, author_name
 			FROM mt_entry e, mt_author a
 			WHERE entry_blog_id = 8
 				AND entry_id <> 1625
 				AND entry_status = 2
 				AND entry_author_id = a.author_id
 				AND a.author_name = 'Richard Fisher'
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
 				<h2>About Richard Fisher</h2>
 				<?=stripslashes($author[bio_short])?>
 			<span class="corners-bottom"><span></span></span></div>
 		<?}?>
		</td><td id="right">

 		<?if ($about != '') {?>
 			<div class="item"><span class="corners-top"><span></span></span>
 				<h2>About Reviews</h2>
 				<?=$about?>
 			<span class="corners-bottom"><span></span></span></div>
		<?}?>

 		<div class="item"><span class="corners-top"><span></span></span>
 			<h2><a href="/forum/index.php">Other Recent Discussion</h2><ul class="brownsquare"><?
 				$qry = "
 				SELECT topic_title, t.topic_id, username as post_author, post_time, post_id
 				FROM phpbb_topics t, phpbb_users u, phpbb_posts p, aux_phpbb_forums af
 				WHERE
					t.forum_id = af.forum_id
					AND af.exclude_general = 0
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/reviews/2009/01/oppo_dv-983h_upconverting_dvd_player_-_review_essentials.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
