<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 730";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 730 AND placement_is_primary = 1";
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
	<meta name="keywords" content="high definition, toshiba america, dvd enabled, systems inc, america information, DVD, dvd, toshiba, Toshiba, satellite, Satellite, experience, high, systems, definition, information, Qosmio, notebook, sound, division, qosmio, technology, america, America, products" />
	<meta name="description" content="Toshiba's Digital Product Division (DPD), a division of Toshiba America Information Systems, Inc., today announced that it has further expanded its lineup of HD DVD-ROM capable notebook computers to give users the industry's fullest portfolio of high-definition mobile computing solutions mixing price points and screen sizes. These five HD DVD-based notebooks will be showcased this week during the DigitalLife tradeshow in New York at the Jacob K. Javits Convention Center in Booth (#621) this week.

The five Toshiba HD DVD enabled notebook computers, include the Qosmio&amp;reg; G45, Qosmio F45, Satellite&amp;reg; X205, Satellite P205 and Satellite A205 with select configurations starting as low as $1,1491. These HD DVD enabled notebooks feature high-definition widescreen displays with resolutions of either 720p or 1080p and sizes ranging from 15.4-inches to 17-inches.

Along with the attractive price points of the HD DVD notebook..." />
	<title>HDTV Magazine Bulletins - Toshiba Announces Industry's Most Complete Portfolio of High-Definition Laptops</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/toshiba_announces_industrys_most_complete_portfolio_of_high-definition_laptops';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Toshiba Announces Industry\'s Most Complete Portfolio of High-Definition Laptops'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2007/09/toshiba_announces_industrys_most_complete_portfolio_of_high-definition_laptops.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Toshiba Announces Industry's Most Complete Portfolio of High-Definition Laptops</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>September 25, 2007</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Products & Equipment">Products & Equipment</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2007/09/toshiba_announces_industrys_most_complete_portfolio_of_high-definition_laptops.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2007/09/toshiba_announces_industrys_most_complete_portfolio_of_high-definition_laptops.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2007/09/toshiba_announces_industrys_most_complete_portfolio_of_high-definition_laptops.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2007/09/toshiba_announces_industrys_most_complete_portfolio_of_high-definition_laptops.php&amp;phase=2&amp;title=Toshiba%20Announces%20Industry%27s%20Most%20Complete%20Portfolio%20of%20High-Definition%20Laptops&amp;bodytext=Toshiba%27s%20Digital%20Product%20Division%20%28DPD%29%2C%20a%20division%20of%20Toshiba%20America%20Information%20Systems%2C%20Inc.%2C%20today%20announced%20that%20it%20has%20further%20expanded%20its%20lineup%20of%20HD%20DVD-ROM%20capable%20notebook%20computers%20to%20give%20users%20the%20industry%27s%20fullest%20portfolio%20of%20high-definition%20mobile%20computing%20solutions%20mixing%20price%20points%20and%20screen%20sizes.%20These%20five%20HD%20DVD-based%20notebooks%20will%20be%20showcased%20this%20week%20during%20the%20DigitalLife%20tradeshow%20in%20New%20York%20at%20the%20Jacob%20K.%20Javits%20Convention%20Center%20in%20Booth%20%28%23621%29%20this%20week.%0A%0AThe%20five%20Toshiba%20HD%20DVD%20enabled%20notebook%20computers%2C%20include%20the%20Qosmio%26reg%3B%20G45%2C%20Qosmio%20F45%2C%20Satellite%26reg%3B%20X205%2C%20Satellite%20P205%20and%20Satellite%20A205%20with%20select%20configurations%20starting%20as%20low%20as%20%241%2C1491.%20These%20HD%20DVD%20enabled%20notebooks%20feature%20high-definition%20widescreen%20displays%20with%20resolutions%20of%20either%20720p%20or%201080p%20and%20sizes%20ranging%20from%2015.4-inches%20to%2017-inches.%0A%0AAlong%20with%20the%20attractive%20price%20points%20of%20the%20HD%20DVD%20notebook...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p class="prtitle">Toshiba Announces Industry's Most Complete Portfolio of High-Definition Laptops</p>

<center><i>Toshiba Offers Five Free HD DVD's with Purchase of Toshiba HD DVD-ROM Notebook Computer</i></center><br />
<br />

<p>DigitalLife 2007</p>

<p><B>IRVINE, Calif.--(BUSINESS WIRE)</B>--Toshiba's Digital Product Division (DPD), a division of Toshiba America Information Systems, Inc., today announced that it has further expanded its lineup of HD DVD-ROM capable notebook computers to give users the industry's fullest portfolio of high-definition mobile computing solutions mixing price points and screen sizes. These five HD DVD-based notebooks will be showcased this week during the DigitalLife tradeshow in New York at the Jacob K. Javits Convention Center in Booth (#621) this week.</p>

<p>The five Toshiba HD DVD enabled notebook computers, include the Qosmio&reg; G45, Qosmio F45, Satellite&reg; X205, Satellite P205 and Satellite A205 with select configurations starting as low as $1,1491. These HD DVD enabled notebooks feature high-definition widescreen displays with resolutions of either 720p or 1080p and sizes ranging from 15.4-inches to 17-inches.</p>

<p>Along with the attractive price points of the HD DVD notebook and a growing number of approximately 330 HD DVD movie titles, such as "300," "Disturbia," "Heroes," "The Matrix Trilogy," "Letters from Iwo Jima," "Smokin' Aces" and "Dream Girls," Toshiba is offering a limited time giveaway incentive. With the purchase of a HD DVD enabled Toshiba notebook, consumers qualify to receive five HD DVD titles for free, from a selection of 15 popular titles, via a mail-in offer. Eligible models and complete offer details are available at www.toshibadirect.com/hddvdpc, and will extend until February 28, 2008.</p>

<p>The HD DVD format provides viewers with a visual experience that is beyond the current DVD standard, in terms of resolution and sound quality. Today's DVD format has a maximum playback resolution of 480p, while HD DVDs offer an image resolution that is up to six times sharper. Next-generation HD DVDs provide a viewing experience with brighter colors, enhanced details and greater overall image sharpness, which can be compared to the dramatic quality leap forward from VHS to DVD.</p>

<p>In terms of sound quality, each HD DVD enabled notebook computer has the capability of outputting a cinema-like audio experience, through Dolby&reg; Home Theater&trade; or Dolby Sound Room&trade; technology. The Qosmio G45, Qosmio F45 and Satellite X205 utilize Dolby Home Theater technology, which places the user in the middle of the onscreen action for a rich and engaging surround sound experience from a variety of audio outputs, such as stereos, multi-channel audio sources, headphones, and 2-channel or 5.1-channel speaker systems and built-in Harman Kardon&reg; Bass Reflex stereo speakers with subwoofer. Meanwhile, the Satellite P205 and Satellite A205 notebooks harness Dolby Sound Room Technology, which delivers an amazing virtual surround sound experience from stereo speakers or any pair of headphones. Dolby Sound Room is designed for environments where multiple surround speakers are neither practical nor possible.</p>

<p>"HD DVD is the future of the high-definition lifestyle and we are excited to make this revolutionary technology available to everyone at an affordable price," said Mark Simons, vice president and general manager, Digital Products Division, Toshiba America Information Systems, Inc. "Being able to watch high-definition content across a wide range of our Satellite and Qosmio notebooks or via an HDTV is an experience that we are confident will thrill our users."</p>

<p>Along with the superior image quality made possible by HD DVD, Microsoft's HDi technology delivers an unprecedented and unique level of content interactivity that will change how people watch and experience movies. HDi introduces users to such features as picture-in-picture visual director's commentary and instant scene book-marking, for instant access to a user's favorite scenes on-the-fly.</p>

<p>Unlike today's DVD viewing experience, HD DVD interactive features can be enjoyed while the film is playing, as there is no need to stop the movie and return to the title menu. HD DVD is an intuitive means for learning about the interesting details that make the magic behind the moviemaking process, accessing exclusive high-definition internet content, such as high-definition movie trailers, music videos and other exclusive content. Toshiba's HD DVD notebook computers are a unique conduit for maximizing the HD DVD experience, since Ethernet and wireless ports makes it possible to quickly and easily access the internet.</p>

<p>For a larger viewing experience, select Toshiba HD DVD enabled notebooks include an HDMI port and can be connected to an HDTV to deliver a pure high-definition image that will equal the resolution capability of the HDTV itself, up to 1080p.</p>

<p>The Qosmio G45, Qosmio F45, Satellite X205, Satellite P205 and Satellite A205 featuring HD DVD drives, and will be available at www.toshibadirect.com and at a variety of major consumer electronics and computer stores nationwide.</p>

<p><br />
<B>About Toshiba America Information Systems, Inc. (TAIS)</B></p>

<p>Headquartered in Irvine, Calif., TAIS is comprised of four business units: Digital Products Division, Imaging Systems Division, Storage Device Division, and Telecommunication Systems Division. Together, these divisions provide mobile product and solutions, including industry leading portable computers; projectors; imaging products for the security, medical and manufacturing markets; storage products for automotive, computer and consumer electronics applications; and telephony equipment and associated applications.</p>

<p>TAIS provides sales, marketing and services for its wide range of information products in the United States and Latin America. TAIS is an independent operating company owned by Toshiba America, Inc., a subsidiary of Toshiba Corporation, which is a global leader in high technology and integrated manufacturing of electrical and electronic components, products and systems, as well as major infrastructure systems. Toshiba has more than 191,000 employees worldwide and annual sales of over US $60 billion (FY2006). For more information on Toshiba's leading innovations, visit the company's Web site at www.toshiba.com.</p>

<p>1 Reseller prices may vary. ESUP means "Estimated Single Unit Price."</p>

<p>&copy; 2007 Toshiba America Information Systems, Inc. All product, service and company names are trademarks, registered trademarks or service marks of their respective owners. Information including without limitation product prices, specifications, availability, content of services, and contact information is subject to change without notice.</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>September 25, 2007 06:59 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 730
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
 			<h2>More on Products & Equipment</h2><ul><?
 			$sql = "
 			SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name
 			FROM mt_entry e, mt_author a, mt_placement p, mt_category c
 			WHERE entry_author_id = author_id
 				AND e.entry_id = p.placement_entry_id
 				AND p.placement_category_id = c.category_id
 				AND category_label = 'Products & Equipment'
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
 				AND entry_id <> 730
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2007/09/toshiba_announces_industrys_most_complete_portfolio_of_high-definition_laptops.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
