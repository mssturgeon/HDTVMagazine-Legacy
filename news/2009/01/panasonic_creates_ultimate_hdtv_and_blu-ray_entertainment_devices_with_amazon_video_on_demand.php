<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');

	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1634";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);

	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";

	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1634 AND placement_is_primary = 1";
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
	<meta name="keywords" content="viera cast, video demand, blu ray, amazon video, hdtvs blu, amazon, Amazon, panasonic, Panasonic, VIERA, viera, cast, CAST, video, demand, Blu, blu, ray, Video, Demand, customers, entertainment, movies, services, devices" />
	<meta name="description" content="Panasonic announced today a collaborative relationship that adds Amazon Video On Demand to Panasonic's line of 2009 VIERA CAST(R) enabled HDTVs and Blu-ray devices. Bringing unique internet enabled services to VIERA HDTV and Blu-ray owners, VIERA CAST will enable customers to instantly shop and watch their favorite movies and TV shows, selecting from Amazon Video On Demand's huge selection of more than 40,000 titles. Panasonic is a leader in built-in TV web entertainment via VIERA CAST and its award winning line of VIERA HDTVs and Blu-ray players, while Amazon.com is a leader in video-on-demand entertainment.

For 2009, Panasonic's VIERA CAST feature has been extended to..." />
	<title>HDTV Magazine Bulletins - Panasonic Creates Ultimate HDTV and Blu-ray Entertainment Devices With Amazon Video On Demand</title>
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

		$base_url = strleftback(PHP_SELF, '/') . '/panasonic_creates_ultimate_hdtv_and_blu-ray_entertainment_devices_with_amazon_video_on_demand';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Panasonic Creates Ultimate HDTV and Blu-ray Entertainment Devices With Amazon Video On Demand'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2009/01/panasonic_creates_ultimate_hdtv_and_blu-ray_entertainment_devices_with_amazon_video_on_demand.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Panasonic Creates Ultimate HDTV and Blu-ray Entertainment Devices With Amazon Video On Demand</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>January  7, 2009</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=HD DVD & Blu-ray">HD DVD & Blu-ray</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2009/01/panasonic_creates_ultimate_hdtv_and_blu-ray_entertainment_devices_with_amazon_video_on_demand.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2009/01/panasonic_creates_ultimate_hdtv_and_blu-ray_entertainment_devices_with_amazon_video_on_demand.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2009/01/panasonic_creates_ultimate_hdtv_and_blu-ray_entertainment_devices_with_amazon_video_on_demand.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2009/01/panasonic_creates_ultimate_hdtv_and_blu-ray_entertainment_devices_with_amazon_video_on_demand.php&amp;phase=2&amp;title=Panasonic%20Creates%20Ultimate%20HDTV%20and%20Blu-ray%20Entertainment%20Devices%20With%20Amazon%20Video%20On%20Demand&amp;bodytext=Panasonic%20announced%20today%20a%20collaborative%20relationship%20that%20adds%20Amazon%20Video%20On%20Demand%20to%20Panasonic%27s%20line%20of%202009%20VIERA%20CAST%28R%29%20enabled%20HDTVs%20and%20Blu-ray%20devices.%20Bringing%20unique%20internet%20enabled%20services%20to%20VIERA%20HDTV%20and%20Blu-ray%20owners%2C%20VIERA%20CAST%20will%20enable%20customers%20to%20instantly%20shop%20and%20watch%20their%20favorite%20movies%20and%20TV%20shows%2C%20selecting%20from%20Amazon%20Video%20On%20Demand%27s%20huge%20selection%20of%20more%20than%2040%2C000%20titles.%20Panasonic%20is%20a%20leader%20in%20built-in%20TV%20web%20entertainment%20via%20VIERA%20CAST%20and%20its%20award%20winning%20line%20of%20VIERA%20HDTVs%20and%20Blu-ray%20players%2C%20while%20Amazon.com%20is%20a%20leader%20in%20video-on-demand%20entertainment.%0A%0AFor%202009%2C%20Panasonic%27s%20VIERA%20CAST%20feature%20has%20been%20extended%20to...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p class="prtitle">Panasonic Creates Ultimate HDTV and Blu-ray Entertainment Devices With Amazon Video On Demand</p>

<center><i>VIERA CAST Functionality Expanded To Provide Amazon.com's Massive Selection of Movies & TV Shows On Demand To Consumers</i></center><br />
<br />

<p><B>LAS VEGAS, Jan. 7 /PRNewswire-FirstCall/</B> -- Panasonic announced today a collaborative relationship that adds Amazon Video On Demand to Panasonic's line of 2009 VIERA CAST(R) enabled HDTVs and Blu-ray devices. Bringing unique internet enabled services to VIERA HDTV and Blu-ray owners, VIERA CAST will enable customers to instantly shop and watch their favorite movies and TV shows, selecting from Amazon Video On Demand's huge selection of more than 40,000 titles. Panasonic is a leader in built-in TV web entertainment via VIERA CAST and its award winning line of VIERA HDTVs and Blu-ray players, while Amazon.com is a leader in video-on-demand entertainment.</p>

<p>For 2009, Panasonic's VIERA CAST feature has been extended to three VIERA Plasma HDTV series as well as Panasonic's 2009 line of Blu-ray devices. In addition to Amazon Video On Demand, VIERA CAST will continue to provide access to YouTube(TM), Picasa Web Albums(TM), Bloomberg News and a weather channel. The consumer will have automatic access to additional sites as they are added to the service. The newly expanded VIERA CAST with Amazon Video On Demand is expected to launch in early 2009.</p>

<p>VIERA CAST was widely acknowledged last year for its distinctive internet application, allowing the consumer to view targeted sites on a large HDTV, rather than a small computer screen. VIERA CAST is also unique in the industry in that, unlike other IPTVs, it has a built-in Ethernet interface -- no external box or PC is required. VIERA CAST is accessed via a single button on the television remote control and there is no fee to use the VIERA CAST functionality.</p>

<p>Accessed through the main VIERA CAST interface, Amazon Video On Demand has an easy to use customer experience that will enable customers to watch the latest new release movies the same day that they are released on DVD, and more. Customers will be able to watch titles right away with no downloading or waiting, and they can rent or purchase titles on a la carte basis with no subscription fees. Using the Your Video Library feature, customers can keep a virtual library of their purchases and re-watch them anywhere, including their VIERA TV, Panasonic Blu-ray player, or via the web on any PC or MAC.</p>

<p>Amazon Video On Demand delivers the best quality video possible through the use of the advanced h.264 codec and an automatic bandwidth detection feature that seamlessly plays back the best quality file at either 300, 600, 900, or 1200 kbps. Customers can choose from thousands of movies to rent from $1.99 to $3.99, purchase from $9.99 to $14.99, or thousands of television shows to purchase for $1.99 per episode.</p>

<p>"We see this as another step in providing the ultimate entertainment experience for the consumer," said Merwan Mereby, Vice President of New Business Development, Panasonic. "It's fitting that Panasonic and Amazon have joined creative forces to create this unique entertainment vehicle. Panasonic's HDTVs and Blu-ray players have garnered numerous best of awards, while Amazon is a pioneer in transforming in-home movie entertainment. The consumer will be able to instantly purchase, rent and watch, choosing from a massive selection of movie and TV entertainment at their finger tips with just a click of a button. Last year VIERA CAST was incorporated into one series of Plasmas, the PZ850. The success and acceptance of VIERA CAST was a motivating factor in increasing the penetration of the unique internet technology to additional model lines and the inclusion of the feature in our 2009 Blu-ray devices"</p>

<p>"Amazon Video On Demand is excited to enable its huge selection of movies and TV shows on Panasonic's internet connected HDTVs and Blu-ray players," said Bill Carr, Amazon.com Vice President Digital Media. "These are mainstream devices that customers want in their living rooms, and the addition of movies and TV shows on demand gives consumers another reason to purchase and enjoy Panasonic's industry leading products."</p>

<p><br />
<B>About Panasonic</B></p>

<p>Based in Secaucus, NJ, Panasonic Corporation of North America markets a broad line of digital and other electronics products for consumer, business and industrial use. The company is the principal North American subsidiary of Panasonic Corporation (NYSE:PC) , and the hub of Panasonic's U.S. branding, marketing, sales, service and R&D operations. Panasonic Broadcast & Television Systems Co. is a leading supplier of broadcast and professional video products and systems. Panasonic Broadcast is a unit company of Panasonic Corporation of North America. Information about Panasonic and its products is available at www.panasonic.com. Additional company information for journalists is available at www.panasonic.com/pressroom</p>

<p><br />
<B>About Amazon.com-Amazon Digital Services</B></p>

<p>Amazon.com, Inc. (NASDAQ:AMZN) , a Fortune 500 company based in Seattle, opened on the World Wide Web in July 1995 and today offers Earth's Biggest Selection. Amazon.com, Inc. seeks to be Earth's most customer-centric company, where customers can find and discover anything they might want to buy online, and endeavors to offer its customers the lowest possible prices. Amazon.com and other sellers offer millions of unique new, refurbished and used items in categories such as books, movies, music & games, digital downloads, electronics & computers, home & garden, toys, kids & baby, grocery, apparel, shoes & jewelry, health & beauty, sports & outdoors, and tools, auto & industrial.</p>

<p>Amazon Web Services provides Amazon's developer customers with access to in-the-cloud infrastructure services based on Amazon's own back-end technology platform, which developers can use to enable virtually any type of business. Examples of the services offered by Amazon Web Services are Amazon Elastic Compute Cloud (Amazon EC2), Amazon Simple Storage Service (Amazon S3), Amazon SimpleDB, Amazon Simple Queue Service (Amazon SQS), Amazon Flexible Payments Service (Amazon FPS), and Amazon Mechanical Turk.</p>

<p>Amazon and its affiliates operate websites, including www.amazon.com, www.amazon.co.uk, www.amazon.de, www.amazon.co.jp, www.amazon.fr, www.amazon.ca, and the Joyo Amazon websites at www.joyo.cn and www.amazon.cn.</p>

<p>As used herein, "Amazon.com," "we," "our" and similar terms include Amazon.com, Inc., and its subsidiaries, unless the context indicates otherwise.</p>

<p>Forward-Looking Statements -- This announcement contains forward-looking statements within the meaning of Section 27A of the Securities Act of 1933 and Section 21E of the Securities Exchange Act of 1934. Actual results may differ significantly from management's expectations. These forward-looking statements involve risks and uncertainties that include, among others, risks related to competition, management of growth, new products, services and technologies, potential fluctuations in operating results, international expansion, outcomes of legal proceedings and claims, fulfillment center optimization, seasonality, commercial agreements, acquisitions and strategic transactions, foreign exchange rates, system interruption, significant amount of indebtedness, inventory, government regulation and taxation, payments and fraud. More information about factors that potentially could affect Amazon.com's financial results is included in Amazon.com's filings with the Securities and Exchange Commission, including its Annual Report on Form 10-K for the year ended December 31, 2007, and subsequent filings.</p>

<p>Source: Panasonic </p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>January  7, 2009 05:01 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 1634
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
 				AND entry_id <> 1634
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2009/01/panasonic_creates_ultimate_hdtv_and_blu-ray_entertainment_devices_with_amazon_video_on_demand.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
