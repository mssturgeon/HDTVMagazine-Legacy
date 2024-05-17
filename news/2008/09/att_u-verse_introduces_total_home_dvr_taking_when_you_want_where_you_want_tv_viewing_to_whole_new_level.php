<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1545";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1545 AND placement_is_primary = 1";
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
	<meta name="keywords" content="home dvr, total home, recorded programs, play back, verse total, verse, DVR, dvr, home, customers, recorded, Home, total, Total, programs, services, any, screen, additional, att, play, recordings, service, connected, want" />
	<meta name="description" content="DVRs have given customers the flexibility to watch TV programs on their schedules, but limited where the programs can be watched by restricting recorded content to certain TV sets and rooms in the home. Now that's about to change for AT&amp;T U-verse(SM) TV customers. Using the power of AT&amp;T's Internet Protocol (IP) network, families no longer have to plan how or where they watch and record their favorite shows.

AT&amp;T Inc. (NYSE:T) today announced the launch of AT&amp;T U-verse Total Home DVR, giving U-verse TV customers the freedom to play back Standard Definition (SD) and High Definition (HD) recorded programs on any connected TV in the home.

U-verse Total Home DVR is now being introduced to customers in..." />
	<title>HDTV Magazine Bulletins - AT&T U-verse Introduces Total Home DVR, Taking 'When You Want, Where You Want' TV Viewing to Whole New Level</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/att_u-verse_introduces_total_home_dvr_taking_when_you_want_where_you_want_tv_viewing_to_whole_new_level';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('AT&T U-verse Introduces Total Home DVR, Taking \'When You Want, Where You Want\' TV Viewing to Whole New Level'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2008/09/att_u-verse_introduces_total_home_dvr_taking_when_you_want_where_you_want_tv_viewing_to_whole_new_level.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">AT&T U-verse Introduces Total Home DVR, Taking 'When You Want, Where You Want' TV Viewing to Whole New Level</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>September 10, 2008</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Products & Equipment">Products & Equipment</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2008/09/att_u-verse_introduces_total_home_dvr_taking_when_you_want_where_you_want_tv_viewing_to_whole_new_level.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2008/09/att_u-verse_introduces_total_home_dvr_taking_when_you_want_where_you_want_tv_viewing_to_whole_new_level.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2008/09/att_u-verse_introduces_total_home_dvr_taking_when_you_want_where_you_want_tv_viewing_to_whole_new_level.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2008/09/att_u-verse_introduces_total_home_dvr_taking_when_you_want_where_you_want_tv_viewing_to_whole_new_level.php&amp;phase=2&amp;title=AT%26T%20U-verse%20Introduces%20Total%20Home%20DVR%2C%20Taking%20%27When%20You%20Want%2C%20Where%20You%20Want%27%20TV%20Viewing%20to%20Whole%20New%20Level&amp;bodytext=DVRs%20have%20given%20customers%20the%20flexibility%20to%20watch%20TV%20programs%20on%20their%20schedules%2C%20but%20limited%20where%20the%20programs%20can%20be%20watched%20by%20restricting%20recorded%20content%20to%20certain%20TV%20sets%20and%20rooms%20in%20the%20home.%20Now%20that%27s%20about%20to%20change%20for%20AT%26T%20U-verse%28SM%29%20TV%20customers.%20Using%20the%20power%20of%20AT%26T%27s%20Internet%20Protocol%20%28IP%29%20network%2C%20families%20no%20longer%20have%20to%20plan%20how%20or%20where%20they%20watch%20and%20record%20their%20favorite%20shows.%0A%0AAT%26T%20Inc.%20%28NYSE%3AT%29%20today%20announced%20the%20launch%20of%20AT%26T%20U-verse%20Total%20Home%20DVR%2C%20giving%20U-verse%20TV%20customers%20the%20freedom%20to%20play%20back%20Standard%20Definition%20%28SD%29%20and%20High%20Definition%20%28HD%29%20recorded%20programs%20on%20any%20connected%20TV%20in%20the%20home.%0A%0AU-verse%20Total%20Home%20DVR%20is%20now%20being%20introduced%20to%20customers%20in...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p class="prtitle">AT&T U-verse Introduces Total Home DVR, Taking 'When You Want, Where You Want' TV Viewing to Whole New Level</p>

<center><i>Latest U-verse TV Enhancement - Made Possible by IP Technology - Enables DVR Playback on Any TV Throughout the Home</i></center><br />
<br />

<p><B>DALLAS, Sept. 9 /PRNewswire-FirstCall/</B> -- DVRs have given customers the flexibility to watch TV programs on their schedules, but limited where the programs can be watched by restricting recorded content to certain TV sets and rooms in the home. Now that's about to change for AT&T U-verse(SM) TV customers. Using the power of AT&T's Internet Protocol (IP) network, families no longer have to plan how or where they watch and record their favorite shows.</p>

<p>AT&T Inc. (NYSE:T) today announced the launch of AT&T U-verse Total Home DVR, giving U-verse TV customers the freedom to play back Standard Definition (SD) and High Definition (HD) recorded programs on any connected TV in the home.</p>

<p>U-verse Total Home DVR is now being introduced to customers in the Bay Area at no additional charge and is planned for deployment to all U-verse TV customers by the end of 2008.</p>

<p>"AT&T U-verse is about providing the latest in entertainment and technology for a better TV experience," said Jeff Weber, AT&T vice president of video products. "With our 100 percent IP network, we are able to constantly evolve features and services to match the needs of viewers. Total Home DVR is the latest addition to our portfolio of unmatched features that give U-verse customers more control, on any TV, at a great price."</p>

<p>  AT&T U-verse Total Home DVR customers can:</p>

<p>  -- Watch HD and SD DVR recordings on other connected TVs in the home. In<br />
     addition to your DVR, you can access, play, pause, rewind and fast<br />
     forward any recorded SD or HD program on up to seven additional<br />
     U-verse-connected TVs. All U-verse DVRs and receivers are HD-capable.<br />
  -- Pause a recorded show and pick up where you left off in another room.<br />
  -- Play back multiple, independent viewings of the same recorded show on<br />
     different TVs.<br />
  -- Play back up to four recorded shows at once. Up to three can be HD<br />
     recorded programs.<br />
  -- Watch up to five HD programs simultaneously throughout the home,<br />
     including two live HD programs and three recorded HD programs.<br />
  -- Record more of the show you want to see with soft padding, which<br />
     automatically adds 1 minute to the beginning and 2 minutes to the end<br />
     of each pre-scheduled recording.<br />
  -- Organize recorded content by series. Series recordings will be grouped<br />
     as a single heading in the recorded TV menu, making it easier for<br />
     customers to manage and select their recorded programs.<br />
  -- Store up to 37 hours of HD content or up to 133 hours of SD content,<br />
     which is more storage than most cable providers' DVRs.<br />
  -- Record up to four programs at once on a single DVR -- another feature<br />
     that is exclusive to AT&T U-verse TV.<br />
  -- Set the DVR while on the go from your PC or wireless phone. With AT&T<br />
     Yahoo!(R) Web and Mobile Remote Access to DVR, you can schedule<br />
     recordings from any Web-connected PC or compatible mobile phone<br />
     (wireless service charges apply) by using your AT&T High Speed Internet<br />
     account.</p>

<p></p>

<p>"While some other providers may claim to offer some form of whole home DVR, AT&T U-verse Total Home DVR is the only one that truly lets you play back recorded programs from a single DVR on any connected TV in the house," Weber said.</p>

<p>Using IP technology, Total Home DVR capabilities will be seamlessly provided to existing customers' DVRs without the need to swap their current equipment. The U-verse network architecture and IPTV service allow Total Home DVR functionality to be enabled by a software update, without any action or hassle for existing customers. The updates occur on a market-by-market basis, and existing AT&T U-verse customers in a market will gain the new functionality as their home equipment receives the update.</p>

<p>Total Home DVR is the latest addition to the constantly evolving suite of features that has been introduced to all U-verse TV customers at no extra charge since the AT&T U-verse launch in June 2006. These include:</p>

<p>  -- Mobile Remote Access to DVR, which lets you schedule and manage DVR<br />
     recordings from any compatible mobile phone.<br />
  -- AT&T U-bar, which brings customizable weather, stock, sports and<br />
     traffic information to the U-verse TV screen, without interrupting the<br />
     current program.<br />
  -- AT&T Online Photos from Flickr, which allows you to simply and<br />
     conveniently browse the photos you've uploaded to flickr.com and watch<br />
     slide shows on your U-verse TV screen from the comfort of your couch.<br />
  -- Yahoo! Sports Fantasy Football, which allows you to track the progress<br />
     of your fantasy team -- including current team matchups and league<br />
     standings -- directly from your TV screen through the AT&T U-bar.<br />
  -- YELLOWPAGES.COM TV, for fast and easy searches to find local businesses<br />
     and other information via your TV screen.<br />
  -- AT&T Yahoo! Games, so you can play your favorite online games --<br />
     including Sudoku, Solitaire, JT's Blocks, Mah-jongg Tiles and Chess --<br />
     on the TV screen.</p>

<p></p>

<p>AT&T has also announced today the availability of AT&T U-verse Voice to all U-verse eligible customers in the Bay Area, bringing consumers a next- generation digital voice service with unique integrated features. AT&T U-verse services are currently available to more than 580,000 living units in the greater Bay Area, marking a significant expansion since AT&T U-verse launched locally in December 2006.</p>

<p>In the future, AT&T plans to add to its Total Home DVR service with the ability to schedule recordings and pause or control live TV from non-DVR receivers. AT&T is deploying next-generation AT&T U-verse services as part of its mission to connect people with their world, everywhere they live and work, and do it better than anyone else. Customers benefit from integrated AT&T services across the three screens they value most: the TV, the PC and the wireless phone.</p>

<p>For additional information on AT&T U-verse -- or to find out if it's available in your area -- visit http://uverse.att.com/, call 800-ATT-2020 or visit a local AT&T retail location.</p>

<p><br />
<B>About AT&T</B></p>

<p>AT&T Inc. (NYSE:T) is a premier communications holding company. Its subsidiaries and affiliates, AT&T operating companies, are the providers of AT&T services in the United States and around the world. Among their offerings are the world's most advanced IP-based business communications services and the nation's leading wireless, high speed Internet access and voice services. In domestic markets, AT&T is known for the directory publishing and advertising sales leadership of its Yellow Pages and YELLOWPAGES.COM organizations, and the AT&T brand is licensed to innovators in such fields as communications equipment. As part of its three-screen integration strategy, AT&T is expanding its TV entertainment offerings. In 2008, AT&T again ranked No. 1 on Fortune magazine's World's Most Admired Telecommunications Company list and No. 1 on America's Most Admired Telecommunications Company list. Additional information about AT&T Inc. and the products and services provided by AT&T subsidiaries and affiliates is available at http://www.att.com/.</p>

<p>(C) 2008 AT&T Intellectual Property. All rights reserved. AT&T, the AT&T logo and all other AT&T marks contained herein are trademarks of AT&T Intellectual Property and/or AT&T affiliated companies. All other marks contained herein are the property of their respective owners.</p>

<p>Note: This AT&T news release and other announcements are available as part of an RSS feed at http://www.att.com/rss. For more information, please review this announcement in the AT&T newsroom at http://www.att.com/newsroom.</p>

<p>Geographic and service restrictions apply to U-verse. Call or go to http://www.uverse.att.com/ to see if you qualify. Total Home DVR feature requires additional U-verse receivers for each additional TV at $5 a month. Four channels can be recorded to the DVR or viewed simultaneously, up to two can be HD, subject to availability.</p>

<p>Source: AT&T Inc. </p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>September 10, 2008 05:26 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 1545
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
 				AND entry_id <> 1545
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2008/09/att_u-verse_introduces_total_home_dvr_taking_when_you_want_where_you_want_tv_viewing_to_whole_new_level.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
