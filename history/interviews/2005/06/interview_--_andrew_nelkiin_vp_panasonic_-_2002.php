<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 127";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Dale Cripps'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 127 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category[placement_category_id];
	
	# Still need this to track page views. Used in the Links unit in the footer
	$google_links_channel = $author[channel];

	# Set variables based on entry type. NOTE: Podcasts has its own entry template, so it is not included amongst the choices below.
	switch (4) {
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
	<meta name="keywords" content="high definition, copy protection, top box, set top, content providers, television, agreement, cable, new, panasonic, Panasonic, set, HDTV, content, think, personal, digital, hdtv, unidirectional, products, industry, our, see, business, fair" />
	<meta name="description" content="Mr. Nelkin has been with the consumer side of Panasonic since 1985. From his New Jersey headquarters at One Panasonic Way he now oversees all of the digital developments for his company in the USA . We approached Panasonic for..." />
	<title>HDTV Magazine Interviews - INTERVIEW -- Andrew Nelkiin, VP Panasonic - 2002</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/interview_--_andrew_nelkiin_vp_panasonic_-_2002';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('INTERVIEW -- Andrew Nelkiin, VP Panasonic - 2002'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_--_andrew_nelkiin_vp_panasonic_-_2002.php";
		if ($author[img] != '' && 4 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Dale Cripps" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">INTERVIEW -- Andrew Nelkiin, VP Panasonic - 2002</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Dale Cripps</b><br />
				<?=$author_title?>
				Posted on <b>June 26, 2005</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category="></a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_--_andrew_nelkiin_vp_panasonic_-_2002.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/history/interviews/2005/06/interview_--_andrew_nelkiin_vp_panasonic_-_2002.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_--_andrew_nelkiin_vp_panasonic_-_2002.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_--_andrew_nelkiin_vp_panasonic_-_2002.php&amp;phase=2&amp;title=INTERVIEW%20--%20Andrew%20Nelkiin%2C%20VP%20Panasonic%20-%202002&amp;bodytext=Mr.%20Nelkin%20has%20been%20with%20the%20consumer%20side%20of%20Panasonic%20since%201985.%20From%20his%20New%20Jersey%20headquarters%20at%20One%20Panasonic%20Way%20he%20now%20oversees%20all%20of%20the%20digital%20developments%20for%20his%20company%20in%20the%20USA%20.%20We%20approached%20Panasonic%20for...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p><em>Mr. Nelkin has been with the consumer side of Panasonic since 1985. From his New Jersey headquarters at One Panasonic Way he now oversees all of the digital developments for his company in the USA . We approached Panasonic for some insight into the recent agreement made between themselves and CableLabs--their signing of the famous PHILA. This agreement allows Panasonic (and any others who sign) to produce cable-ready televisions--plug and play. The importance to the consumer is in the portability and convenience. No matter where you buy a cable-ready digital set you will be able to take it to any other part of the country and it will still operate with a digital cable hook up. </p>

<p>While the focus of this conversation was finally upon this historic agreement we began with a quick left turn towards the copy protection topic. While his comments broke no new ground it becomes importantly clearer with repetition that fair use rights are central values embraced by both manufacturers and content providers. </p>

<p>I opened the discussion asking about the changes he has seen during his career in consumer electronics. </em> </p>

<p><strong>HDTV Magazine: What has changed in consumer electronics since the time you started?</strong></p>

<p>Andrew Nelkin: Products were all analog then.They were all individual devices. Now it has become a digital world and it is all networked. In the future you will see products that are all-digital based electronic media--very little tape, very little analog products left. It is totally different.</p>

<p><strong>How far do you plan ahead in developing new markets?</strong></p>

<p>We have a shorter term vision that is typically eighteen months (time for development and marketing new products). We also have a longer term vision that can go out five or more years. </p>

<p><strong>What's coming up?</strong></p>

<p>We are at the beginning of the trend towards networked products. Products are not going to be just hardware, but software enabled. There will be features as a result of software. I think we are just at the beginning of that trend. You will see a lot more sharing of information, more migrating of files around the home, and ultimately out of the home.</p>

<p><strong>This 'out of the home' has been a nightmare content providers who see this networked world is an invitation to end the copyright value-based economic system. What is Panasonic's view with respect to the value of copyrighted material?</strong></p>

<p>Firstly, we firmly believe that the person who owns the content does have rights to that content. The person who owns it absolutely has a right in a say to its distribution and where it is used. At the same time we also believe that there is fair personal use of a lot of information by the end user that has nothing to do with moving content around. Companies within the 5C agreement have been able to fashion agreements with the content providers that meets everyone's needs.</p>

<p><strong>Is that an iron clad system that you can still envisage working in five, ten, fifteen years from now, or is it compromised?</strong></p>

<p>The beauty of the agreements within 5C is that they don't have to be iron clad. The agreements are flexible enough so that new business models can be brought to the table and new copy protection schemes can be proposed to fit those business models. </p>

<p><strong>Considering that great new processor power being handed to children do you remain confident that protection being sought is sound ?</strong></p>

<p>In a single word, yes.</p>

<p><strong>With 5C you have this security far into the future?</strong></p>

<p>Yes.</p>

<p></p>

<p>________________________________________________________________</p>

<p><em>Our own company philosophy is that people should be allowed to use something for their own personal use. The industry question is: What is personal use? In answering that they need to be sure that personal use doesn't invade the rights of the copyright owner?</em>_<br />
________________________________________________________________<br />
 </p>

<p>The analog hole -- people who have analog sets today have concern that their investment will be devalued. How do you answer those folks? </p>

<p>Our own company philosophy is that people should be allowed to use something for their own personal use. The industry question is: What is personal use? In answering that they need to be sure that personal use doesn't invade the rights of the copyright owner?</p>

<p><strong>What is the current thinking on this question within the industry? How do you define it?</strong></p>

<p>We clearly think that if you want to record a program and then later play it back for yourself, that is very fair use. At the same time there are event programs which should be 'record never' because the nature of the business model which is that the content should be used once, and only once. </p>

<p><strong>Is this not a slippery slope where the all programming starts to fall under this "vent" category, leaving fair use to wither? Or, is this something which the marketplace finally dictates? </strong></p>

<p>You ask a lot of questions there. The marketplace will find a fair and balanced answer for copy protection. I don't think we need to worry that either side is going to overstep its bounds. To maximize everyone's business the market will find the balance. We don't need to worry about it swinging too far either way. If all else fails the government will step in.</p>

<p><strong>The government has already asked for a solution on this matter. Is this a good thing?</strong></p>

<p>I think the industry should be able to work these things out. Whether that requires legislation in the end, we will have to wait and see.</p>

<p><strong>Let's talk about the recent cable agreement that you signed. How is the layman to understand this?</strong></p>

<p>What Panasonic signed was an agreement which allows us to create a unidirectional "host" which will allow for a digital ready cable television--a truly transportable digital television. </p>

<p><strong>What do you mean by "transportable"?</strong></p>

<p>What we were signed was an agreement where if you moved you could call up your new cable company and get a new POD for the television you already have.</p>

<p><strong>What is the POD to the layman?</strong></p>

<p>That is a PC card-sized device that allows the conditional access.</p>

<p><strong>This installs like a PC card?</strong></p>

<p>Yes.</p>

<p><strong>Does this mean sets employing this agreement will be rolling out in 18 months or so?</strong></p>

<p>Right now we are in development of the televisions that will include the unidirectional POD? <br />
<strong></p>

<p>Was the POD a major sticking point? </strong></p>

<p>I would not say that. By limiting our discussions to a unidirectional device (and not a bi-directional one) we were able to table, for lack of a better term, the discussion in certain areas that still need to be addressed. </p>

<p><strong>What services are enabled with unidirectional?</strong></p>

<p>A unidirectional device will allow you to get premium services on your television without a set top box. Things you will be able to get in a bi-directional device would be things like impulse pay-per-view.</p>

<p><strong>Does this have anything to do with the data services being sold by cable via cable modems?</strong></p>

<p>No.</p>

<p><strong>What are the benefits of this agreement?</strong></p>

<p>The first is to the end user. They can now enjoy all of the features of the television--the famous picture-in-picture. Now it can be done. </p>

<p>The second is that the customer no longer needs to lease the set top box. It is inside the television. </p>

<p>From the retailers' perspective--well, if you are a nationwide chain you can now sell one television that will attach to all of the cable systems. You don't have to sell a different television for each individual regional area. </p>

<p>The win for the television manufacturers is that we can bring in some of our functionality into the television set.</p>

<p><strong>What might be some of this functionality that would have us pulling out our check books?</strong></p>

<p>I think the fact that there is no set top box and picture-in-picture and the fact that you can control the entire TV with a single remote control.</p>

<p><strong>Let's talk about DTV and HDTV. </strong></p>

<p>All of the televisions that we anticipate incorporating this into are true high-definition televisions. This will definitely bring high-definition quicker than anything we could have so far done. Sixty-five to seventy percent are receiving their programming by cable. They are not getting it via terrestrial broadcast, which is 13 or 14%. They are not getting it through the satellite providers. Now that we are embedding the POD and allowing conditional access the result is that we are definitely going to accelerate the transition.</p>

<p><strong>Panasonic has taken the lead in forging this agreement and we see in other published reports that others are following. </strong></p>

<p>We have signed a private agreement and I don't know what the CEA agreement will finally look like. What we have done does not undermine anybody's ability to do whatever they have to do. The business deal we signed will allow us to make a set that is a unidirectional one which is very good for the industry.</p>

<p><strong>Let me change the subject a bit. Is the making of a high-definition television set a major challenge over making of a standard resolution set? </strong></p>

<p>In so far as the resolution is higher, there are some challenges.</p>

<p><strong>Could HDTV have been made 20 years ago? </strong></p>

<p>You could not have made these sets 20 years ago. These televisions are a direct result of the advances in technology that in manufacturing and micro processor design. </p>

<p><strong>Some people have voiced a concern that as price becomes more important there would be a dumbing down and HDTV might diminish into something less. Is that a valid concern?</strong></p>

<p>I don’t think there is too much to worry. If prices come down, they come down. That doesn’t mean that the quality necessarily comes down. The more people who can enjoy high-definition television the better off the industry is.<br />
<strong>|</p>

<p>How do you personally feel about HDTV? Is it just a nicer technical toy or does it have some social significance?</strong></p>

<p><strong>I think it really changes the way you can enjoy television, especially things like sports. When I watch sports on HD instead of on standard 4:3 I feel that I enjoyed programming more. I am like your readers in that respect.</strong></p>

<p><strong>Yes, many of our readers are unable to go back to standard definition once they have had a good taste of HDTV.</strong></p>

<p><strong>Thank you very much for sharing your views with HDTV Magazine.</strong><br />
</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Dale Cripps</b>, <b>June 26, 2005 11:53 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 127
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
			
 		<?if (4 <> 7) {
 			# Recent Articles by Author (exclude this one)
 			# Do not show recent articles for Bulletins.
 			$qry = "
 			SELECT entry_id, entry_blog_id, entry_created_on, entry_title, author_id, author_name
 			FROM mt_entry e, mt_author a
 			WHERE entry_blog_id = 4
 				AND entry_id <> 127
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
 				<h2>About Interviews</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_--_andrew_nelkiin_vp_panasonic_-_2002.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
