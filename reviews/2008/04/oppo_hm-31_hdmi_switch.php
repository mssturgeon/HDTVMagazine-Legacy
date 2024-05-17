<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1322";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Richard Fisher'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1322 AND placement_is_primary = 1";
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
	<meta name="keywords" content="auto switching, hdmi switch, hdmi inputs, power supply, switching logic, hdmi, HDMI, input, cable, switch, switching, OPPO, oppo, remote, auto, source, receiver, active, inputs, priority, power, Cable, wall, Auto, logic" />
	<meta name="description" content="It's just a switcher; there is nothing to set up so installation is a newbie breeze. The power supply wall wart is offset to the side to avoid taking up two outlets. That seems like a great feature but that will depend on the orientation of your outlets. Oddly enough for me my outlets and strips made that the most difficult part of this installation. It supports hot plug and play and auto switching as evidenced when I hooked it up. I simply inserted the HM-31 in between a Toshiba HD-A35 HD DVD player and a Denon AVR-3808Ci receiver while in the midst of a movie. I looked up at the screen and within a few moments there was Phantom of the Opera in 1080p24 with sound. A quick look at the receiver confirmed my bitstream DTS True HD was still engaged. From there I connected..." />
	<title>HDTV Magazine Reviews - OPPO HM-31 HDMI Switch</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/oppo_hm-31_hdmi_switch';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('OPPO HM-31 HDMI Switch'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/reviews/2008/04/oppo_hm-31_hdmi_switch.php";
		if ($author[img] != '' && 8 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Richard Fisher" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">OPPO HM-31 HDMI Switch</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Richard Fisher</b><br />
				<?=$author_title?>
				Posted on <b>April 29, 2008</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Switchers & Amplifiers">Switchers & Amplifiers</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/reviews/2008/04/oppo_hm-31_hdmi_switch.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/reviews/2008/04/oppo_hm-31_hdmi_switch.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/reviews/2008/04/oppo_hm-31_hdmi_switch.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/reviews/2008/04/oppo_hm-31_hdmi_switch.php&amp;phase=2&amp;title=OPPO%20HM-31%20HDMI%20Switch&amp;bodytext=It%27s%20just%20a%20switcher%3B%20there%20is%20nothing%20to%20set%20up%20so%20installation%20is%20a%20newbie%20breeze.%20The%20power%20supply%20wall%20wart%20is%20offset%20to%20the%20side%20to%20avoid%20taking%20up%20two%20outlets.%20That%20seems%20like%20a%20great%20feature%20but%20that%20will%20depend%20on%20the%20orientation%20of%20your%20outlets.%20Oddly%20enough%20for%20me%20my%20outlets%20and%20strips%20made%20that%20the%20most%20difficult%20part%20of%20this%20installation.%20It%20supports%20hot%20plug%20and%20play%20and%20auto%20switching%20as%20evidenced%20when%20I%20hooked%20it%20up.%20I%20simply%20inserted%20the%20HM-31%20in%20between%20a%20Toshiba%20HD-A35%20HD%20DVD%20player%20and%20a%20Denon%20AVR-3808Ci%20receiver%20while%20in%20the%20midst%20of%20a%20movie.%20I%20looked%20up%20at%20the%20screen%20and%20within%20a%20few%20moments%20there%20was%20Phantom%20of%20the%20Opera%20in%201080p24%20with%20sound.%20A%20quick%20look%20at%20the%20receiver%20confirmed%20my%20bitstream%20DTS%20True%20HD%20was%20still%20engaged.%20From%20there%20I%20connected...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p><a href="http://www.oppodigital.com/hm31/default.asp" target="_blank"><img height="81" alt="OPPO HM-31" src="http://www.hdtvmagazine.com/images/mt/OppoHM31HDMISwitch_13133/image.png" width="250" border="0"></a>&nbsp;</p> <table class="greygrid"> <tbody> <tr> <td style="font-weight: bold; text-align: center" colspan="4">Pricing at publication</td></tr> <tr> <td class="greygrid"><b>&nbsp;</b></td> <td class="greygrid"><b>MSRP</b></td> <td class="greygrid"><b>Street</b></td> <td class="greygrid"><b>Amazon.com</b></td></tr> <tr> <td class="greygrid"><b>HM-31</b></td> <td class="greygrid"><a href="http://www.oppodigital.com/hm31/default.asp" target="_blank">$99.00</a></td> <td class="greygrid"><a href="http://hdtv.pricegrabber.com/search_getprod.php/masterid=51262090/search=HM-31/st=product/sv=title" target="_blank">$99.00</a></td> <td class="greygrid"><a href="http://www.amazon.com/gp/product/B000UQCAKW?ie=UTF8&amp;tag=hdtvmagazine-20&amp;linkCode=as2&amp;camp=1789&amp;creative=9325&amp;creativeASIN=B000UQCAKW" target="_blank">$99.99</a></td></tr></tbody></table> <p>Serial #: HM0735940367<br>Warranty: 1 year, parts and labor</p> <p><b>Summary: A full featured 3x1 HDMI switch at a very competitive price</b></p> <p>OPPO has garnered a solid reputation with their upconverting DVD players and seeks to expand their market with the HM-31 HDMI switch.</p> <h2>Features</h2> <ul> <li>3x1, 3 inputs, 1 output (supports DVI with adapters)  <li>Remote control  <li>Auto switching and priority switching  <li>Hot plug and play for HDMI  <li>External IR input and RS232C serial port for custom installations  <li>HDMI 1.3 supporting 1080p video, Deep Color, bit stream and PCM HD audio  <li>HDMI Cable EQ circuit  <li>External power supply  <li>Optional wall mount capability </li></ul> <h2>Optional Accessories</h2> <ul> <li>External IR remote sensor, IR-ES1 </li></ul> <p>OPPO has a great website with oodles of information about their products. For the HM-31 you will find everything from specs and a wall mount template to RS 232 codes: <a href="http://oppodigital.com/hm31/" target="_blank">HM-31 Advanced 3x1 HDMI Switch</a>.</p> <h2>Opening the Box</h2> <p>OPPO enhances even the simple things with quality packaging. The switch box comes in a cloth envelope along with stick-on rubber cushions for set top applications versus wall mounting. When you take it out of the bag you find this cute and appealing little plastic box with piano black on the sides, a sleek silver trim on the top beveled edge along with a black brushed aluminum top that can easily compliment other visible products in your system. A card-type remote provides one button for switching to the next active input or 3 individual buttons for each input. All of this is packed in a nice velvet plastic divider along with the power supply. </p> <h2>Hooking It Up</h2> <p>It's just a switcher; there is nothing to set up so installation is a newbie breeze. The power supply wall wart is offset to the side to avoid taking up two outlets. That seems like a great feature but that will depend on the orientation of your outlets. Oddly enough for me my outlets and strips made that the most difficult part of this installation. It supports hot plug and play and auto switching as evidenced when I hooked it up. I simply inserted the HM-31 in between a Toshiba HD-A35 HD DVD player and a Denon AVR-3808Ci receiver while in the midst of a movie. I looked up at the screen and within a few moments there was Phantom of the Opera in 1080p24 with sound. A quick look at the receiver confirmed my bitstream DTS True HD was still engaged. From there I connected a Sony PS3 and switched over finding a picture and "PCM" displayed on the receiver.</p> <h2>Priority, Auto and Remote Switching</h2> <p>Priority and Auto switching logic can easily make remote control unnecessary. Auto switching logic detects live HDMI connections automatically selecting the active source if the other two sources are turned off. Simply by turning sources on and off you can automatically switch without doing anything more. There is a catch though; this logic will not work if one of your sources leaves the HDMI output active even though you have turned it off, such as cable or satellite receivers. To turn off the HDMI on these devices requires pulling the AC power. In this application, Priority Switching logic becomes your auto switching best friend. Input 1 has the highest priority and input 3 the least. By connecting your always active HDMI source to input 3 you can switch to input 2 by turning on that source. You can leave input 2 on along with input 3, but when you turn on input 1 it will switch to that. Conversely, if input 1 is on then you can't auto switch to input 2 or 3 without turning it off. If you have more than one HDMI source always active you can toggle through the three inputs via the front panel or use direct input select via the remote. If the HM-31 can't be easily accessed or will be hidden from view, OPPO provides an external IR sensor accessory to get the IR detector in view of your remote(s).  <h2>HDMI Cable EQ Circuit</h2> <p>HDMI has problems with long distances because it is based on DVI, and that was developed for the typical PC user application where the display and PC are near each other; long DVI cable runs were not even considered during development. HDMI does not specify cable length, only a minimum performance standard which can be extended by the quality of materials used. Due to this anomaly, manufacturers have stepped forward with chip sets that incorporate Transition Minimized Differential Signaling Equalization (TMDS EQ), which samples the signal and automatically tunes the connection impedance for maximum signal integrity supporting output or input tuning. This is what OPPO calls Cable EQ. This is a very beneficial feature, but should be taken with a grain of salt. The OPPO website documents a successful 100 foot HDMI application, two 50 foot cables with the HM-31 in between, yet provides a disclaimer that your mileage may vary. The best general advice is that once you hit 30 feet, you are pushing the tolerance of the HDMI system design. At 40 feet you have reached design limitations so going beyond that can open yourself up to problems. How much further you can go will depend on the integrity of the source, cable and display HDMI design.  <h2>Putting it in Perspective</h2> <p>The most interesting aspect of the HM-31 is that for $99 you can expand the HDMI inputs on a new HDMI-equipped A/V receiver for less than what you would be charged to upgrade to the next model up; typically a $200-300 additional charge for just one or two more HDMI inputs. </p> <h2>Final Conclusion</h2> <p>If you need to expand the HDMI inputs on your display or A/V receiver OPPO has provided a highly competitive and wonderful $99 solution that covers newbie to custom home theater applications.  <h2>For More Information</h2> <p>When it comes to cable lengths and the HDMI specification, you can find more information in the <a href="http://hdmi.org/learningcenter/learningcenter/faq.aspx" target="_blank">HDMI FAQ</a> at HDMI.org. However, I find that their site tends to gloss over the finer points of the specification. For a thorough analysis of the HDMI specification and dealing with it in the real world, I recommend <a href="http://www.bluejeanscable.com/store/hdmi-cables/store/hdmi-cables/index.htm" target="_blank">Blue Jeans Cable</a>.</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Richard Fisher</b>, <b>April 29, 2008 09:20 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 1322
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
 			<h2>More on Switchers & Amplifiers</h2><ul><?
 			$sql = "
 			SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name
 			FROM mt_entry e, mt_author a, mt_placement p, mt_category c
 			WHERE entry_author_id = author_id
 				AND e.entry_id = p.placement_entry_id
 				AND p.placement_category_id = c.category_id
 				AND category_label = 'Switchers & Amplifiers'
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
 				AND entry_id <> 1322
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/reviews/2008/04/oppo_hm-31_hdmi_switch.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
