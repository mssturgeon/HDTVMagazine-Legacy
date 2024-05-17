<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	require(BASE_DIR .'/includes/lib_amazon.php');
	$debug = isset($_GET['debug']);

	function getByPGMasterID($masterid) {
		global $admindata, $debug;

		# First check to see if it's in the database
		$sql_pg = "SELECT * FROM pg_main WHERE masterid = '$masterid'";
		$res_pg = mQuery($sql_pg);
		if (mysql_num_rows($res_pg) != 0) return mysql_fetch_assoc($res_pg);

		# Not found ... try to get dynamically
		$pg_request_url = "http://ah.pricegrabber.com/search_xml.php?pid=718&key=7e084a24802&version=2.14&upc=1&spec=2&offers=1&masterid=$masterid";
		$contents = file_get_contents($pg_request_url);
		$xml = new SimpleXMLElement( $contents );

		$pg_main = array();
		$product = $xml->product;
		$pg_main['url'] = $product->url;
		$pg_main['masterid'] = $product->masterid;
		$pg_main['title'] = $product->title;
		$pg_main['image_small'] = $product->image_small;
		$pg_main['image_medium'] = $product->image_medium;
		$pg_main['image_large'] = $product->image_large;
		$pg_main['image_160'] = $product->image_160;
#		$pg_main['reative_rank'] = $product->reative_rank;
		$pg_main['manufacturer'] = $product->manufacturer;
		$pg_main['partnum'] = $product->partnum;
		$pg_main['upc'] = $product->upc;
#		$pg_main['price'] = $product->masterid;
		$pg_main['price_formatted'] = $product->price;
		$pg_main['sellers'] = $product->num_sellers[0];
		$pg_main['rating'] = $product->rating;
		$pg_main['num_reviews'] = $product->num_reviews;

		# Update pg_main
		$sql = "REPLACE INTO pg_main (". join(", ", array_keys($pg_main)) .", date_updated) VALUES ('". join("', '", array_values($pg_main)) ."', NOW())";
		if ($debug) {echo "$sql\n";} else {mQuery($sql);}

		# Now that we KNOW it's in the database ...
		$res_pg = mQuery($sql_pg);
		return mysql_fetch_assoc($res_pg);
	}

	function getReviewHeader($asin, $amazon_tracking_id, $pg_url, $pg_price) {
		global $admindata, $debug;

/*
		# Get auxiliary information
		$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
		FROM aux_mt_entry a, phpbb_topics t
		WHERE a.entry_id = $entry_id
			AND a.topic_id = t.topic_id";
		$res_aux = mQuery($sql);
		$row_aux = mysql_fetch_assoc($res_aux);

		# Get Pricegrabber info
		if ($row_aux['pg_masterid'] != '') $row_pg = getByPGMasterID($row_aux['pg_masterid']);
		if ($row_pg != '') {
			$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword='. urlencode($row_pg['title']) .'&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
			$pg_url = $row_pg['url'];
			$pg_price = $row_pg['price_formatted'];
		}
*/

		# Get Amazon info
		if ($asin != '') $row_amazon = getByASIN($asin);
		if ($row_amazon != '') {
			if ($debug) echo "amazon_tracking_id: $amazon_tracking_id<br />";
			if ($debug) echo "admin_data: {$admindata['amazon_associates_id']}<br />";
			$az_url = str_replace($admindata['amazon_associates_id'], $amazon_tracking_id, $row_amazon['DetailPageURL']);
			$az_image = ($row_amazon['MediumImageURL'] == '') ? '' : '<img src="'. $row_amazon['MediumImageURL'] .'" alt="'. $row_amazon['Title'] .'" height="'. $row_amazon['MediumImageHeight'] .'" width="'. $row_amazon['MediumImageWidth'] .'"/>';
			$az_product = $row_amazon['Manufacturer'] .' '. $row_amazon['Model'];
			$az_list = ($row_amazon['ListPriceFormatted'] == '') ? 'N/A' : $row_amazon['ListPriceFormatted'];
			$az_price = $row_amazon['LowestNewPriceFormatted'];
			$az_price = ($az_price == 'Too low to display') ? 'Unknown' : $az_price;

			if ($row_amazon['ProductGroup'] == 'DVD') {
				$review_header .= <<<EOT
					<div class="item_review"><span class="corners-top"><span></span></span>
						<h2>{$row_amazon['Title']}</h2>
						<div class="image"><a href="$az_url">{$az_image}</a></div>
						<div class="text">
							<b>Studio:</b> {$row_amazon['Studio']}<br />
							<b>List Price:</b> {$az_list}<br />
							<b>Street Price:</b> <a href="{$pg_url}" target="_blank">{$pg_price}</a><br />
							<b>Amazon.com:</b> <a href="{$az_url}" target="_blank">{$az_price}</a><br />
							<b>Release Date:</b> {$row_amazon['ReleaseDate']}<br />
							<b>Aspect Ratio:</b> {$row_amazon['AspectRatio']}<br />
							<b>Running Time:</b> {$row_amazon['RunningTime']} minutes<br />
						</div>
					<span class="corners-bottom"><span></span></span></div>
EOT;
			} else {
				$review_header .= <<<EOT
					<div class="item_review"><span class="corners-top"><span></span></span>
						<div class="image"><a href="$az_url">{$az_image}</a></div>
						<div class="text">
							<h2>{$row_amazon['Title']}</h2>
							<b>Manufacturer:</b> {$row_amazon['Manufacturer']}<br />
							<b>List Price:</b> {$az_list}<br />
							<b>Street Price:</b> <a href="{$pg_url}" target="_blank">{$pg_price}</a><br />
							<b>Amazon.com:</b> <a href="{$az_url}" target="_blank">{$az_price}</a><br />
						</div>
					<span class="corners-bottom"><span></span></span></div>
EOT;
			}

			return $review_header;
		}
	}

	# Get author information
	$res_author = mQuery("SELECT title, channel, amazon_tracking_id, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Shane Sturgeon" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];

	if ($debug) echo $amazon_tracking_id;

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4383 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 4383
		AND a.topic_id = t.topic_id";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	# Get Pricegrabber info
	if ($row_aux['pg_masterid'] != '') $row_pg = getByPGMasterID($row_aux['pg_masterid']);
	if ($row_pg != '') {
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword='. urlencode($row_pg['title']) .'&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
		$pg_url = $row_pg['url'];
		$pg_price = $row_pg['price_formatted'];
	}

	# Get Comments
	if ($row_aux['topic_replies'] > 0) {
		$comments = '<li class="li_horizontal"><img src="'. BASE_IMG_URL .'/images/icon_comments.gif" alt="" height="14" width="16" /> '.
		'<a href="/forum/viewtopic.php?t='. $row_aux['topic_id'] .'">'. $row_aux['topic_replies'] .' Comments</a></li>';
	} else {
		$comments = '<li class="li_horizontal"><img src="'. BASE_IMG_URL .'/images/icon_comments.gif" alt="" height="14" width="16" /> '.
		'<a class="red" href="/forum/viewtopic.php?t='. $row_aux['topic_id'] .'">Post First Comment</a></li>';
	}

	# Set defaults which may be overridden by blog type below
	$container = 'article_container';
	$meta_medium_type = 'blog';

	# Set image
	$link_rel_image_src = $row_aux['image_src'];
	if ($link_rel_image_src == '') $link_rel_image_src = $row_amazon['SmallImageURL'];

	$v_buttons = <<<EOT
<div id="dd_right"><ul>
	<li class="li_vertical">
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/test/2011/06/hsu-research-speakers.php&amp;title=HSU Research Speakers">
		<span style="display:none">Recently Braden started looking for some new speakers for his house. Recommendations came flooding in, but one that really intrigued us came from good friend Ray. He suggested we check out what HSU Research has to offer. We know they make great subs, but were unfamiliar with their speakers, so we decided to check them out...</span></a>
	</li>
</ul></div>
EOT;
	$h_buttons = <<<EOT
<div id="dd_right"><ul>
	<li class="li_horizontal">$comments</li>
	<li class="li_horizontal" id="tm_li"></li>
	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/test/2011/06/hsu-research-speakers.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>
	<!--li class="li_horizontal"><fb:like href="http://www.hdtvmagazine.com/test/2011/06/hsu-research-speakers.php" layout="button_count" show_faces="false" width="100"></fb:like></li-->
	<!--li class="li_horizontal"><a name="fb_share" type="button_count" href="http://www.facebook.com/sharer.php"></a></li-->
</ul></div>
EOT;

	switch (6) {
		case 1: # Articles
			$feed_name = 'hdtv-articles';
			$sub_type = SUB_ARTICLES;
			$sub_label = 'Receive instant notification of new articles';
			if ($userdata['session_logged_in']) {
				$sub_desc = '<a href="/profile-subscriptions.php">Modify your subscription profile</a> to receive notification of new HDTV Magazine Articles via email as soon as they are published.';
			} else {
				$sub_desc = '<a href="/profile-create.php">Register Now</a> to receive notification of new HDTV Magazine Articles via email as soon as they are published.';
			}
			break;
		case 4: # Interviews
			$feed_name = 'hdtv-interviews';
			break;
		case 5: # History
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Testing Grounds Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
			break;
		case 6: # Test
			$sub_type = 0;
			$sub_label = 'Receive instant notification of "Stuff"';
			if ($userdata['session_logged_in']) {
				$sub_desc = '<a href="/profile-subscriptions.php">Modify your subscription profile</a> to receive notification of "Stuff" via email as soon as they are published.';
			} else {
				$sub_desc = '<a href="/profile-create.php">Register Now</a> to receive notification of "Stuff" via email as soon as they are published.';
			}
			break;
		case 7: # Bulletins
				# Moved to footer-4
#			$apture = '<script id="aptureScript" type="text/javascript" src="http://www.apture.com/js/apture.js?siteToken=kwQEuu6" charset="utf-8"></script>';
			$google_links_channel = ''; # Don't count bulletins
			$feed_name = 'hdtv-news';
			$container = 'bulletin_container';
			$author_headshot = '';
			$sub_type = SUB_BULLETINS;
			$sub_label = 'Receive instant notification of HDTV Bulletins';
			if ($userdata['session_logged_in']) {
				$sub_desc = '<a href="/profile-subscriptions.php">Modify your subscription profile</a> to receive notification of HDTV Bulletins via email as soon as they are published.';
			} else {
				$sub_desc = '<a href="/profile-create.php">Register Now</a> to receive notification of HDTV Bulletins via email as soon as they are published.';
			}
			$meta_medium_type = 'news';
			break;
		case 8: # Reviews
			$feed_name = 'hdtv-reviews';
			$sub_type = SUB_REVIEWS;
			$sub_label = 'Receive instant notification of new reviews';
			if ($userdata['session_logged_in']) {
				$sub_desc = '<a href="/profile-subscriptions.php">Modify your subscription profile</a> to receive notification of new HDTV Magazine Reviews via email as soon as they are published.';
			} else {
				$sub_desc = '<a href="/profile-create.php">Register Now</a> to receive notification of new HDTV Magazine Reviews via email as soon as they are published.';
			}
			break;
		case 9: # Podcasts
			# Get enclosure info
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4383";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HSU Research Speakers" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HSU Research Speakers" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
EOT;

			$sub_type = SUB_PODCAST;
			$sub_label = 'Receive instant notification of new episodes';
			if ($userdata['session_logged_in']) {
				$sub_desc = '<a href="/profile-subscriptions.php">Modify your subscription profile</a> to receive notification of new episodes of The HDTV Podcast via email as soon as they are published.';
			} else {
				$sub_desc = '<a href="/profile-create.php">Register Now</a> to receive notification of new episodes of The HDTV Podcast via email as soon as they are published.';
			}
			$itunes_chicklet = BASE_IMG_HOST .'/images/chicklet-itunes.gif';
			# Need a better check here if we add other podcasts
			$contents = @file_get_contents('https://feedburner.google.com/api/awareness/1.0/GetFeedData?uri=hdtvpodcast');
			$xml = new SimpleXMLElement( $contents );

			$h_buttons = <<<EOT
<div id="dd_right"><ul>
	<li class="li_horizontal">$comments</li>
	<li class="li_horizontal" id="tm_li"></li>
	<li class="li_horizontal"><a name="fb_share" type="button_count" href="http://www.facebook.com/sharer.php"></a></li>
	<li class="li_horizontal">
		<span style="line-height:16px;vertical-align:middle;">{$xml->feed->entry['circulation']}
			<a href="http://click.linksynergy.com/fs-bin/click?id=FK62p2waXuc&subid=&offerid=146261.1&type=10&tmpid=1826&RD_PARM1=http%3A%2F%2Fphobos.apple.com%2FWebObjects%2FMZStore.woa%2Fwa%2FviewPodcast%3Fid%3D73799860" target="_blank"
				><img src="$itunes_chicklet" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="top" height="15" width="80"></a>
		</span>
	</li>
</ul></div>
EOT;

			break;
		case 10: # Columns
			$feed_name = 'hdtv-columns';
			$sub_type = SUB_COLUMNS;
			$sub_label = 'Receive instant notification of new columns';
			if ($userdata['session_logged_in']) {
				$sub_desc = '<a href="/profile-subscriptions.php">Modify your subscription profile</a> to receive notification of new HDTV Magazine Columns via email as soon as they are published.';
			} else {
				$sub_desc = '<a href="/profile-create.php">Register Now</a> to receive notification of new HDTV Magazine Columns via email as soon as they are published.';
			}
			$about = 'HDTV Magazine Columns are written by various personalities within the HDTV industry. They are typically shorter than our standard <a href="/articles">Article</a> and quite often express the opinion of the author(s). And of course, opinions expressed by these authors are not necessarily those of HDTV Magazine.';
			break;
		default:
			break;
	}

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - HSU Research Speakers</title>
	<meta name="keywords" content="hsu research, center channel, satellite speakers, speakers cost, chance review, speakers, hsu, ”, setup, get, good, satellite, review, speaker, channel, course, really, research, center, we’ll, veneer, those, sound, don’t, much" />
	<meta name="description" content="Recently Braden started looking for some new speakers for his house. Recommendations came flooding in, but one that really intrigued us came from good friend Ray. He suggested we check out what HSU Research has to offer. We know they make great subs, but were unfamiliar with their speakers, so we decided to check them out..." />
	<meta name="title" content="HSU Research Speakers" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Testing Grounds Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />

	<script type="text/javascript">
		// Digg Script
		(function() {
			var s = document.createElement('SCRIPT'), s1 = document.getElementsByTagName('SCRIPT')[0];
			s.type = 'text/javascript';
			s.src = 'http://widgets.digg.com/buttons.js';
			s1.parentNode.insertBefore(s, s1);
		})();

		function init() {
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/test/2011/06/hsu-research-speakers.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
		}

		function tweetMemeButton() {
			if (document.getElementById("tm_li")) {
				var iframeCode = '';
				iframeCode += '<iframe src="http://api.tweetmeme.com/button.js?url='+ escape(document.URL) +'&amp;style=normal&amp;source=SEOmofo&amp;service=bit.ly" scrolling="no" frameborder="0" width="50" height="61">';
				document.getElementById("tm_li").innerHTML = iframeCode;
			}
		}
		function getTMButton(url, style, source, service) {
			if (style == 'compact') {w = 80;h = 20;} else {w = 50;h = 61;}
			return '<iframe src="http://api.tweetmeme.com/button.js?url='+ escape(url) +'&amp;style='+ style +'&amp;source='+ source +'&amp;service='+ service +'" scrolling="no" frameborder="0" width="'+ w +'" height="'+ h +'">';
		}
	</script>
	<style>
		#dd_right {float:right;padding:2px;text-align:right;}
		#dd_right ul {padding:0;margin:0;}
		#dd_right ul li {list-style-image:none;list-style-position:outside;padding:4px;margin:0;outline:0 none;background-color:transparent;border:0 none;list-style-type:none;background-image:none;}
		#dd_right .li_horizontal {align:right;display:inline;float:left;font-weight:bold;margin-top:2px;padding:0 10px}
		#dd_right .li_vertical {display:block;list-style-type:none;}
/*		#dd_right img {border:none !important;}*/
		a.stbar.chicklet img {border:0;height:16px;width:16px;margin-right:3px;vertical-align:middle;}
		a.stbar.chicklet {height:16px;line-height:16px;}
	</style>
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4383', 340, 125);">Link Products</a>
				<span class="corners-bottom"><span></span></span></div>
			<? }?>

			<!-- Subscription box -->
			<? if ($sub_type > 0 && ($userdata['subscriptions'] & $sub_type)) {} else {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<img src="<?=BASE_IMG_HOST?>/images/i_inbox.gif" alt="" align="left" height="31" width="38" style="float:left; padding-right:10px" />
					<span class="label"><?=$sub_label?>:</span>
					<?=$sub_desc?>
				<span class="corners-bottom"><span></span></span></div>
			<? }?>

			<!-- Article Header -->
			<table class="bare" cellpadding="0" cellspacing="0" style="width:100%">
				<tr>
					<td id="article_headshot" rowspan="3"><?=$author_headshot?></td>
					<td>
						<table class="bare" cellspacing="0" style="width:100%"><tr>
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/test/2011/06/hsu-research-speakers.php">HSU Research Speakers</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>June  5, 2011</b>
							</td><td id="article_category">
								Categories: 
							</td>
						</tr><tr colspan="2">
							<td id="article_buttons" colspan="2"><?=$h_buttons?></td>
						</tr></table>
					</td>
				</tr>
			</table>

			<!-- Main Article Body -->
			<div id="<?=$container?>">
				<?=getReviewHeader($row_aux['ASIN'], $amazon_tracking_id, $pg_url, $pg_price)?>
				<!--?=$v_buttons?-->
				<p>Recently Braden started looking for some new speakers for his house. Recommendations came flooding in, but one that really intrigued us came from good friend Ray. He suggested we check out what <a href="http://www.google.com/url?q=http%3A%2F%2Fwww.hsuresearch.com%2Findex.html&amp;sa=D&amp;sntz=1&amp;usg=AFQjCNEXWsxmHHz-qfSKjUn88RPhdLEIFw">HSU</a><a href="http://www.google.com/url?q=http%3A%2F%2Fwww.hsuresearch.com%2Findex.html&amp;sa=D&amp;sntz=1&amp;usg=AFQjCNEXWsxmHHz-qfSKjUn88RPhdLEIFw">Research</a> has to offer. We know they make great subs, but were unfamiliar with their speakers, so we decided to check them out. <h2>The Speakers</h2> <p>In addition to the unbelievable <a href="http://www.google.com/url?q=http%3A%2F%2Fwww.hsuresearch.com%2Fsubwoofers.html&amp;sa=D&amp;sntz=1&amp;usg=AFQjCNH-iaS2if63_uXEeEeOod-t7hBiXw">subwoofers</a> made by HSU Research, they also make a bookshelf speaker, the <a href="http://www.google.com/url?q=http%3A%2F%2Fwww.hsuresearch.com%2Fproducts%2Fhb-1.html&amp;sa=D&amp;sntz=1&amp;usg=AFQjCNHUGJBrMqvulXamw0tMtwCihf6_6w">HB</a><a href="http://www.google.com/url?q=http%3A%2F%2Fwww.hsuresearch.com%2Fproducts%2Fhb-1.html&amp;sa=D&amp;sntz=1&amp;usg=AFQjCNHUGJBrMqvulXamw0tMtwCihf6_6w">-1 </a><a href="http://www.google.com/url?q=http%3A%2F%2Fwww.hsuresearch.com%2Fproducts%2Fhb-1.html&amp;sa=D&amp;sntz=1&amp;usg=AFQjCNHUGJBrMqvulXamw0tMtwCihf6_6w">MK</a><a href="http://www.google.com/url?q=http%3A%2F%2Fwww.hsuresearch.com%2Fproducts%2Fhb-1.html&amp;sa=D&amp;sntz=1&amp;usg=AFQjCNHUGJBrMqvulXamw0tMtwCihf6_6w">2</a>, a center channel speaker, the <a href="http://www.google.com/url?q=http%3A%2F%2Fwww.hsuresearch.com%2Fproducts%2Fhc-1.html&amp;sa=D&amp;sntz=1&amp;usg=AFQjCNGN37ViwE-FY12hXXOTLmeuwC4pfA">HC</a><a href="http://www.google.com/url?q=http%3A%2F%2Fwww.hsuresearch.com%2Fproducts%2Fhc-1.html&amp;sa=D&amp;sntz=1&amp;usg=AFQjCNGN37ViwE-FY12hXXOTLmeuwC4pfA">-1 </a><a href="http://www.google.com/url?q=http%3A%2F%2Fwww.hsuresearch.com%2Fproducts%2Fhc-1.html&amp;sa=D&amp;sntz=1&amp;usg=AFQjCNGN37ViwE-FY12hXXOTLmeuwC4pfA">MK</a><a href="http://www.google.com/url?q=http%3A%2F%2Fwww.hsuresearch.com%2Fproducts%2Fhc-1.html&amp;sa=D&amp;sntz=1&amp;usg=AFQjCNGN37ViwE-FY12hXXOTLmeuwC4pfA">2</a>, an in-wall speaker, the <a href="http://www.google.com/url?q=http%3A%2F%2Fwww.hsuresearch.com%2Fproducts%2Fhiwspeaker.html&amp;sa=D&amp;sntz=1&amp;usg=AFQjCNFD-cWBbcpYyj8XasCmRmnsMJ56zg">HIW</a><a href="http://www.google.com/url?q=http%3A%2F%2Fwww.hsuresearch.com%2Fproducts%2Fhiwspeaker.html&amp;sa=D&amp;sntz=1&amp;usg=AFQjCNFD-cWBbcpYyj8XasCmRmnsMJ56zg">-1</a> and a satellite speaker based surround sound system called the <a href="http://www.google.com/url?q=http%3A%2F%2Fwww.hsuresearch.com%2Fproducts%2Fvt-12.html&amp;sa=D&amp;sntz=1&amp;usg=AFQjCNHJ14qjXyk8c8mlrhjDgJ_Mf9C-fw">VT</a><a href="http://www.google.com/url?q=http%3A%2F%2Fwww.hsuresearch.com%2Fproducts%2Fvt-12.html&amp;sa=D&amp;sntz=1&amp;usg=AFQjCNHJ14qjXyk8c8mlrhjDgJ_Mf9C-fw">-12 </a><a href="http://www.google.com/url?q=http%3A%2F%2Fwww.hsuresearch.com%2Fproducts%2Fvt-12.html&amp;sa=D&amp;sntz=1&amp;usg=AFQjCNHJ14qjXyk8c8mlrhjDgJ_Mf9C-fw">Ventriloquist</a>. For our review we were specifically interested in an LCR setup so we got two of the bookshelf speakers and one center channel. <p>The HB-1 MK2 bookshelf speaker is comprised of a improved, controlled directivity horn with a more powerful Neodymium magnet for the tweeter and a 6½ inch woofer. It measures 15” H x 8” W x 8” D and weighs 15.5 lbs. It retails for $149 for the satin black finish and $179 for the Rosenut veneer (real wood veneer). <p>The HC-1 MK2 is similarly built, but adds a second 6 ½ inch woofer. It measures 8” H x 23” W x 10” D and weighs 22 lbs. Each one comes with an optional base the bumps the height up to 9”. The HC1-MK2 sells for $239 for satin black and $279 for Rosenut. If all you need is a center channel, they’re currently clearing out the Walnut veneer models for $219. <p>The VT-12 Ventriloquist is a unique looking setup. It includes a large-ish center channel that almost looks like a soundbar and five satellite speakers. It retails for $299; extra satellite speakers cost $40. Add a sub and you’ve got a full 5.1 setup. We didn’t get a chance to review this one, but we did get a few of the satellite speakers by themselves to try out for surrounds for those who can’t use the HB-1 MK2s in the back of the room. We haven’t had a chance to review them yet, so we’ll update you in a couple weeks on how they do. <p><b></b> <p>We’ll skip the typical “setup” portion of the review because, well, they’re speakers. You plug them into your amp. You probably want to recalibrate your amp if you change speakers, but that’s nothing to do with the speakers themselves. The only other item to note is that they don’t have keyholes or threaded inserts for wall mounts. These speakers are meant to be placed on stands. <h2>Performance</h2> <p>There’s really not much more to say than “wow.” We’ll admit that we weren’t expecting awe inspiring results from $150 speakers, but HSU Research has built a great product. Dialog in movies and HDTV was clear and sounded very real. The HC-1 did an excellent job with the sound of the human voice. For surround effects the HB-1s were every bit as good and very complimentary to the center channel. Together they produced a very full and very crisp soundstage. <p>We don’t often listen to music in our home theaters, call it a weakness, but we’re really more movie and TV guys. But of course doing a speaker review we had to do music. We played some 2 channel stuff from CD and it rocked. Then we decided to get the best of both worlds and plugged in some Dave Matthews live stuff in 5.1. It sounded like we were there. For the price, we were simply amazed. <p>Of course we wanted to make sure that it wasn’t just the amp/receiver that was doing such a good job, so we ran the speakers on two different receivers, an Onkyo TX-SR608 and a Denon AVR-3806. You always hear subtle differences between receivers, but we’ve never heard those differences so clearly. It’s almost like the speakers removed disappeared into the sound and let us hear every subtle difference in the personality of the two amps. But in both cases, they sounded excellent.  <p>Coming from a company known for its subwoofers, the HB-1 and HC-1 speakers are really meant to be used in conjunction with a good sub to really get that low frequency punch. They do well with bass, but you do miss something without the dot-one in your 5.1 setup. The setup we used got us 4 of the 6 ½ inch woofers, but that still wasn’t enough to replace the need for a good sub. Luckily, HSU Reseach knows where you can get a good one of those. <h2>Buying Options</h2> <p>HSU recommends using 5 HB-1 MK2 speakers if you want a 5.1 setup, and of course 6 of them for 6.1 and 7 for 7.1. Doing the math, that comes out to $984 for a 5.1 setup in black satin, that goes up to $1282 for 7.1 - not including the subwoofer. Obviously the Rosenut veneer adds a bit of a premium to the bottom line. HSU has a few packages built with a subwoofer included. The <a href="http://www.google.com/url?q=http%3A%2F%2Fwww.hsuresearch.com%2Fproducts%2Fenthusiast3.html&amp;sa=D&amp;sntz=1&amp;usg=AFQjCNEN_WzbxrdKbwC4aXLt6HohMghXTg">Enthusiast</a><a href="http://www.google.com/url?q=http%3A%2F%2Fwww.hsuresearch.com%2Fproducts%2Fenthusiast3.html&amp;sa=D&amp;sntz=1&amp;usg=AFQjCNEN_WzbxrdKbwC4aXLt6HohMghXTg"> 3</a> is a great package for any home theater, it is a 6.1 setup and costs $1599. <p>Of course, if you want to get creative, HSU also sells the satellite speakers you can get with the VT-12 for only $40 each. Using those for the rear surrounds drops the price of a package like the Enthusiast 3 by about $435, getting the price under $1200. Of course we don’t set prices for HSU Research or their package deals, so don’t quote us on the numbers. We’re just going off the prices listed on the website. And again, we haven’t had a chance to demo the satellite speakers yet. We’ll update the review as soon as we have. <h2>Conclusion</h2> <p>Overall we were very impressed with the HSU Research speakers. If you’re in the market for new speakers, they should certainly be on your short list. They’ll sound every bit as good as speakers that cost much, much more. You can thank us later for letting you in on this little gem.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>June  5, 2011  5:41 PM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(4383)?>
			<div class="dottedline"></div>

			<? if (6 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 4383)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author['bio_short'] != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Shane Sturgeon</h2>
					<?=stripslashes($author['bio_short'])?>
				<span class="corners-bottom"><span></span></span></div>
			<? }?>
		</td><td id="right">
			<div align="center" style="margin:5px 0;">
				<? include(BASE_DIR .'/ads/mrectangle.php');?>
			</div>
			<br />

			<div align="right">
				<? include(BASE_DIR .'/ads/skyscraper.php');?>
			</div>

			<?=getBoxAuthors()?>

			<?=getBoxCategories()?>

			<?=getBoxDiscussions()?>

<!-- FM Medium Rectangle Zone -->
	<div class='ad_mrectangle'>advertisement<br />
		<script type='text/javascript' src='http://static.fmpub.net/zone/541'></script>
	</div>
<!-- FM Medium Rectangle Zone -->
		</td>
	</tr></table><br />

	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/test/2011/06/hsu-research-speakers.php" type="text/javascript" charset="utf-8"></script>
	<script src="http://static.ak.fbcdn.net/connect.php/js/FB.Share" type="text/javascript"></script>
	<script type="text/javascript" src="http://w.sharethis.com/button/sharethis.js#publisher=3da06545-0753-46cb-8739-3ffcef208c1f&amp;type=website&amp;post_services=email%2Ctwitter%2Cdigg%2Cfacebook%2Cmyspace%2Csms%2Cdelicious%2Cstumbleupon%2Cgoogle_bmarks%2Clinkedin%2Cwindows_live%2Creddit%2Cbebo%2Cybuzz%2Cblogger%2Cyahoo_bmarks%2Cmixx%2Ctechnorati%2Cfriendfeed%2Cpropeller%2Cwordpress%2Cnewsvine%2Cxanga&amp;linkfg=%23003F87&amp;button=false"></script>
	<script type="text/javascript">
		var shared_object = SHARETHIS.addEntry({title: document.title,url: document.location.href});

		shared_object.attachButton(document.getElementById("ck_sharethis"));
		shared_object.attachChicklet("email", document.getElementById("ck_email"));
		shared_object.attachChicklet("facebook", document.getElementById("ck_facebook"));
		shared_object.attachChicklet("twitter", document.getElementById("ck_twitter"));
	</script>
</div></body>
</html>
