<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 785";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 785 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category[placement_category_id];
	
	# Still need this to track page views. Used in the Links unit in the footer
	$google_links_channel = $author[channel];

	# Set variables based on entry type. NOTE: Podcasts has its own entry template, so it is not included amongst the choices below.
	switch (7) {
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
	<meta name="keywords" content="blu ray, cryptography research, research inc, conference call, ray disc, macrovision, Macrovision, security, technology, content, cryptography, research, blu, ray, Blu, Research, Cryptography, call, Inc, conference, our, inc, spdc, technologies, SPDC" />
	<meta name="description" content="Macrovision Corporation (Nasdaq:MVSN) today announced that it has signed a definitive agreement to acquire certain technology assets from Cryptography Research, Inc. (CRI), the San Francisco-based R&amp;D organization focused on solving complex security problems. Upon closing of the transaction, Macrovision will own the SPDC&amp;trade; (Self-Protecting Digital Content) technology, which formed the basis of BD+, a key element of Blu-ray's content security platform. The purchase includes certain CRI's patents, security software code, and related third party customer and partner agreements. Macrovision will also hire certain CRI employees involved in the technology.

The Blu-ray Disc Association adopted BD+, exclusive to the Blu-ray format, as an added layer of content protection for movies and other premium entertainment released on the Blu-ray Disc standard. The technology will complement Macrovision's..." />
	<title>HDTV Magazine Bulletins - Macrovision to Acquire Blu-ray Disc Security Technology from Cryptography Research, Inc.</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/macrovision_to_acquire_blu-ray_disc_security_technology_from_cryptography_research_inc';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Macrovision to Acquire Blu-ray Disc Security Technology from Cryptography Research, Inc.'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2007/11/macrovision_to_acquire_blu-ray_disc_security_technology_from_cryptography_research_inc.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Macrovision to Acquire Blu-ray Disc Security Technology from Cryptography Research, Inc.</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>November 19, 2007</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=HD DVD & Blu-ray">HD DVD & Blu-ray</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2007/11/macrovision_to_acquire_blu-ray_disc_security_technology_from_cryptography_research_inc.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2007/11/macrovision_to_acquire_blu-ray_disc_security_technology_from_cryptography_research_inc.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2007/11/macrovision_to_acquire_blu-ray_disc_security_technology_from_cryptography_research_inc.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2007/11/macrovision_to_acquire_blu-ray_disc_security_technology_from_cryptography_research_inc.php&amp;phase=2&amp;title=Macrovision%20to%20Acquire%20Blu-ray%20Disc%20Security%20Technology%20from%20Cryptography%20Research%2C%20Inc.&amp;bodytext=Macrovision%20Corporation%20%28Nasdaq%3AMVSN%29%20today%20announced%20that%20it%20has%20signed%20a%20definitive%20agreement%20to%20acquire%20certain%20technology%20assets%20from%20Cryptography%20Research%2C%20Inc.%20%28CRI%29%2C%20the%20San%20Francisco-based%20R%26D%20organization%20focused%20on%20solving%20complex%20security%20problems.%20Upon%20closing%20of%20the%20transaction%2C%20Macrovision%20will%20own%20the%20SPDC%26trade%3B%20%28Self-Protecting%20Digital%20Content%29%20technology%2C%20which%20formed%20the%20basis%20of%20BD%2B%2C%20a%20key%20element%20of%20Blu-ray%27s%20content%20security%20platform.%20The%20purchase%20includes%20certain%20CRI%27s%20patents%2C%20security%20software%20code%2C%20and%20related%20third%20party%20customer%20and%20partner%20agreements.%20Macrovision%20will%20also%20hire%20certain%20CRI%20employees%20involved%20in%20the%20technology.%0A%0AThe%20Blu-ray%20Disc%20Association%20adopted%20BD%2B%2C%20exclusive%20to%20the%20Blu-ray%20format%2C%20as%20an%20added%20layer%20of%20content%20protection%20for%20movies%20and%20other%20premium%20entertainment%20released%20on%20the%20Blu-ray%20Disc%20standard.%20The%20technology%20will%20complement%20Macrovision%27s...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p class="prtitle">Macrovision to Acquire Blu-ray Disc Security Technology from Cryptography Research, Inc.</p>

<center><i>Technology Selected as Standard by Blu-ray for Content Security Platform Known as BD+</i></center><br />
<br />

<p><B>SANTA CLARA, Calif.--(BUSINESS WIRE)</B>--Macrovision Corporation (Nasdaq:MVSN) today announced that it has signed a definitive agreement to acquire certain technology assets from Cryptography Research, Inc. (CRI), the San Francisco-based R&D organization focused on solving complex security problems. Upon closing of the transaction, Macrovision will own the SPDC&trade; (Self-Protecting Digital Content) technology, which formed the basis of BD+, a key element of Blu-ray's content security platform. The purchase includes certain CRI's patents, security software code, and related third party customer and partner agreements. Macrovision will also hire certain CRI employees involved in the technology. Macrovision will hold a conference call to discuss the transaction today at 1:00pm Eastern time; see dial-in information below.</p>

<p>The Blu-ray Disc Association adopted BD+, exclusive to the Blu-ray format, as an added layer of content protection for movies and other premium entertainment released on the Blu-ray Disc standard. The technology will complement Macrovision's existing ACP and Ripguard video security solutions.</p>

<p>"As we continue to build our business and work with our partners to develop and implement new distribution models in the digital marketplace, we seek to expand our capabilities to address emerging standards such as Blu-ray," commented Fred Amoroso, CEO of Macrovision Corporation. "The integration of SPDC into our product portfolio will enable us to continue to provide innovative technology to our customers as they expand their distribution vehicles. Not only is BD+ critical for content security, but it also supports value-added features that enhance the consumer playback experience, such as potentially unlocking bonus content." Mr. Amoroso also noted, "I am especially delighted to add some of the cryptographic talent for which CRI is renowned. We believe they will allow us to accelerate our future security solution development efforts."</p>

<p>"We are a research organization dedicated to solving difficult cryptography problems," commented Paul Kocher, President and Chief Scientist of Cryptography Research Inc. "We developed SPDC to enable consumers to experience content across a broad range of devices while simultaneously providing content owners with the control to manage the security of content in this dynamic environment. Macrovision shares this goal and now that SPDC has entered commercialization, we are confident Macrovision will take it to the next level."</p>

<p>Unlike previous DVD security technologies, a critical advantage of BD+ is its ability to respond dynamically to security threats. Similar to Macrovision's ACP technology, BD+ resides both in devices and on the media. Title-specific security code is embedded in each BD+ protected disc. On the device side, BD+ utilizes an embedded virtual machine and APIs that are integrated directly into the media player, which communicate with the code from the discs. As a result, new titles can carry unique security code to address emerging threats, thus providing content producers the ability to respond to security breaches without impacting legitimate consumers. This field upgradeability helps protect investments in the Blu-ray format over the long-term, a key concern of the motion picture industry.</p>

<p>BD+ has been adopted by more than 20 companies including major CE manufactures and motion studios. Upon the close of the transaction Macrovision will be the primary licensor of BD+ technology to Studios.</p>

<p>The consideration for the SPDC assets is $45 million in cash plus warrants exercisable for Macrovision stock, a portion of which are subject to certain performance milestones. The transition is subject to customary closing conditions and Macrovision expects it to close in the fourth quarter of 2007 and be slightly accretive to 2008 earnings.</p>

<p><br />
<B>Dial-in Information</B></p>

<p>Macrovision will hold an investor conference call on November 19, 2007, at 1:00 p.m. ET. Investors and analysts interested in participating in the conference are welcome to call 800-366-7417 (or international +1 303-262-2075) and reference the Macrovision call.</p>

<p>The conference call can also be accessed via live Webcast at http://www.macrovision.com/ or http://www.earnings.com/ (or http://www.streetevents.com/ for subscribers) on November 19, 2007 at 1:00 p.m. ET. The on-demand audio webcast of the conference call can be accessed approximately 1-2 hours after the live Webcast ends.</p>

<p>Investors and analysts interested in listening to a recorded replay of the conference are welcome to call 800-405-2236 (or international +1 303-590-3000) and enter passcode 11102772#. Access to the replay is available through November 21, 2007.</p>

<p><br />
<B>About Macrovision</B></p>

<p>Macrovision provides a broad set of solutions that enable businesses to protect, enhance and distribute their digital goods to consumers across multiple channels. Macrovision solutions are deployed by companies in the entertainment, consumer electronics, gaming, software, information publishing and corporate IT markets to solve industry-specific challenges and bring greater value to their customers. Macrovision holds approximately 270 issued or pending United States patents and more than 1,200 issued or pending international patents, and continues to increase its patent portfolio with new and innovative technologies in related fields. Macrovision is headquartered in Santa Clara, California, U.S.A. with other offices across the United States and around the world. More information about Macrovision can be found at www.macrovision.com.</p>

<p><br />
<B>About Cryptography Research, Inc.</B></p>

<p>Cryptography Research, Inc. provides technology to solve complex security problems. In addition to security evaluation and applied engineering work, the company is actively involved in long-term research and technology licensing in areas including content protection, tamper resistance, network security and financial services. Security systems designed by Cryptography Research engineers protect more than $100 billion of commerce annually for wireless, telecommunications, financial, digital television and Internet industries. For additional information please visit www.cryptography.com.</p>

<p>This press release contains "forward-looking" statements as that term is defined in the Private Securities Litigation Reform Act of 1995, including, but not limited to, statements regarding the closing of Macrovision's acquisition of the assets of Cryptography Research, the integration of its technologies into Macrovision's products and solutions offerings, Macrovision's plans for such offerings and customer demand for such offerings. A number of factors could cause Macrovision's actual results to differ from anticipated results expressed in such forward-looking statements. Such factors include, among others, satisfaction of closing conditions to the transaction, the Company's ability to successfully integrate the merged businesses and technologies, and customer demand for the technologies and integrated offerings. Such factors are further addressed in Macrovision's Annual Report on Form 10-K for the period ended December 31, 2006, its latest Quarterly Report on Form 10-Q for the period ended September 30, 2007 and other securities filings which are on file with the Securities and Exchange Commission (available at www.sec.gov). Macrovision assumes no obligation to update any forward-looking statements except as required by law.</p>

<p>@copy;Macrovision 2007. Macrovision is a registered trademark of Macrovision Corporation. All other brands and product names and trademarks are the registered property of their respective companies.</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>November 19, 2007 09:17 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 785
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
 			<h2>More on HD DVD & Blu-ray</h2><ul><?
 			$sql = "
 			SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name
 			FROM mt_entry e, mt_author a, mt_placement p, mt_category c
 			WHERE entry_author_id = author_id
 				AND e.entry_id = p.placement_entry_id
 				AND p.placement_category_id = c.category_id
 				AND category_label = 'HD DVD & Blu-ray'
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
			
 		<?if (7 <> 7) {
 			# Recent Articles by Author (exclude this one)
 			# Do not show recent articles for Bulletins.
 			$qry = "
 			SELECT entry_id, entry_blog_id, entry_created_on, entry_title, author_id, author_name
 			FROM mt_entry e, mt_author a
 			WHERE entry_blog_id = 7
 				AND entry_id <> 785
 				AND entry_status = 2
 				AND entry_author_id = a.author_id
 				AND a.author_name = 'Shane Sturgeon'
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
 				<h2>About Shane Sturgeon</h2>
 				<?=stripslashes($author[bio_short])?>
 			<span class="corners-bottom"><span></span></span></div>
 		<?}?>
		</td><td id="right">

 		<?if ($about != '') {?>
 			<div class="item"><span class="corners-top"><span></span></span>
 				<h2>About Bulletins</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2007/11/macrovision_to_acquire_blu-ray_disc_security_technology_from_cryptography_research_inc.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
