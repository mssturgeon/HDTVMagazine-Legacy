<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	require(BASE_DIR .'/includes/lib_amazon.php');

	$debug = isset($_GET['debug']);
	if ($debug) header('Content-type: text.plain');

	function getByPGMasterID($masterid) {
		global $admindata, $debug;

		# First check to see if it's in the database
		$sql_pg = "SELECT * FROM pg_main WHERE masterid = '$masterid'";
		if ($debug) echo "$sql_pg\n";
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
		$pg_main['sellers'] = intval($product->num_sellers[0]);
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
				$releaseDate = date('M j, Y', strtotime($row_amazon['ReleaseDate']));
				$review_header .= <<<EOT
					<div class="item_review"><span class="corners-top"><span></span></span>
						<h2>{$row_amazon['Title']}</h2>
						<div class="image"><a href="$az_url">{$az_image}</a></div>
						<div class="text">
							<b>Studio:</b> {$row_amazon['Studio']}<br />
							<b>List Price:</b> {$az_list}<br />
							<b>Street Price:</b> <a href="{$pg_url}" target="_blank">{$pg_price}</a><br />
							<b>Amazon.com:</b> <a href="{$az_url}" target="_blank">{$az_price}</a><br />
							<b>Release Date:</b> $releaseDate<br />
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
	$sql = "SELECT title, channel, amazon_tracking_id, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Shane Sturgeon" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];

	if ($debug) echo "$amazon_tracking_id\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4463 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a, ". TOPICS_TABLE ." t
	WHERE a.entry_id = 4463
		AND a.topic_id = t.topic_id";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	# Get Pricegrabber info
	if (intval($row_aux['pg_masterid']) > 0) $row_pg = getByPGMasterID($row_aux['pg_masterid']);
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/test/2011/08/lgs-passivepolarizedglasses-3dtv-where-is-my-pixel.php&amp;title=LG's Passive-Polarized-Glasses 3DTV - Where is my Pixel?">
		<span style="display:none">In the previous articles I covered the subject of the current battle between active-shutter glasses 3DTV and passive-polarized-glasses 3DTV technologies to gain consumer acceptance. I also illustrated how both TV technologies show 3D images and briefly introduced LG’s new passive 3DTV.

In this article I illustrate how that 3DTV employs a proprietary feature to claim it displays the full resolution of the original 3D images, a claim LG used to challenge active-shutter glasses 3DTV technology known to be capable of full resolution per eye. 

As mentioned in other articles, passive technology discards half of the pixels of the original 3D images and LG’s implementation of the technology has a more complex pixel management. This article discusses that complexity.
</span></a>
	</li>
</ul></div>
EOT;
	$h_buttons = <<<EOT
<div id="dd_right"><ul>
	<li class="li_horizontal">$comments</li>
	<li class="li_horizontal" id="tm_li"></li>
	<li class="li_horizontal"><div id="fb-root"></div><fb:like layout="button_count"></fb:like></li>
	<li class="li_horizontal"><g:plusone size="medium"></g:plusone></li>
	<!--li class="li_horizontal"><a href="http://twitter.com/share" class="twitter-share-button">Tweet</a></li-->
	<!--li class="li_horizontal"><fb:like href="http://www.hdtvmagazine.com/test/2011/08/lgs-passivepolarizedglasses-3dtv-where-is-my-pixel.php" layout="button_count" show_faces="false" width="100"></fb:like></li-->
	<!--li class="li_horizontal"><a name="fb_share" type="button_count" href="http://www.facebook.com/sharer.php"></a></li-->
</ul></div>
EOT;
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/test/2011/08/lgs-passivepolarizedglasses-3dtv-where-is-my-pixel.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (6) {
		case 1: # Articles
			$feed_name = 'hdtv-articles';
			$sub_type = SUB_ARTICLES;
			$sub_label = 'Receive instant notification of new articles';
			if ($user->data['is_registered']) {
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
			if ($user->data['is_registered']) {
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
			if ($user->data['is_registered']) {
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
			if ($user->data['is_registered']) {
				$sub_desc = '<a href="/profile-subscriptions.php">Modify your subscription profile</a> to receive notification of new HDTV Magazine Reviews via email as soon as they are published.';
			} else {
				$sub_desc = '<a href="/profile-create.php">Register Now</a> to receive notification of new HDTV Magazine Reviews via email as soon as they are published.';
			}
			break;
		case 9: # Podcasts
			# Get enclosure info
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4463";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download LG\'s Passive-Polarized-Glasses 3DTV - Where is my Pixel?" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="LG\'s Passive-Polarized-Glasses 3DTV - Where is my Pixel?" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
EOT;

			$sub_type = SUB_PODCAST;
			$sub_label = 'Receive instant notification of new episodes';
			if ($user->data['is_registered']) {
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
			if ($user->data['is_registered']) {
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
	<title>HDTV Magazine - LG's Passive-Polarized-Glasses 3DTV - Where is my Pixel?</title>
	<meta name="keywords" content="active shutter, video lines, video frame, blu ray, passive dtvs, lines, video, image, eye, pixels, cycle, passive, dtv, images, right, left, display, show, second, resolution, half, lg’s, same, shown, even" />
	<meta name="description" content="In the previous articles I covered the subject of the current battle between active-shutter glasses 3DTV and passive-polarized-glasses 3DTV technologies to gain consumer acceptance. I also illustrated how both TV technologies show 3D images and briefly introduced LG’s new passive 3DTV.

In this article I illustrate how that 3DTV employs a proprietary feature to claim it displays the full resolution of the original 3D images, a claim LG used to challenge active-shutter glasses 3DTV technology known to be capable of full resolution per eye. 

As mentioned in other articles, passive technology discards half of the pixels of the original 3D images and LG’s implementation of the technology has a more complex pixel management. This article discusses that complexity.
" />
	<meta name="title" content="LG's Passive-Polarized-Glasses 3DTV - Where is my Pixel?" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/test/2011/08/lgs-passivepolarizedglasses-3dtv-where-is-my-pixel.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4463', 340, 125);">Link Products</a>
				<span class="corners-bottom"><span></span></span></div>
			<? }?>

			<!-- Subscription box -->
			<? if ($sub_type > 0 && ($user->data['subscriptions'] & $sub_type)) {} else {?>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/test/2011/08/lgs-passivepolarizedglasses-3dtv-where-is-my-pixel.php">LG's Passive-Polarized-Glasses 3DTV - Where is my Pixel?</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>August  3, 2011</b>
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
				<p>In the previous articles I covered the subject of the <a href="http://www.hdtvmagazine.com/articles/2011/07/3dtv-the-battle-of-passive-vs-active-methods.php">current battle</a> between active-shutter glasses 3DTV and <a href="http://www.hdtvmagazine.com/articles/2011/07/typical-passive-3dtvs-displaying-and-perceiving-3d-images.php">passive-polarized-glasses 3DTV</a> technologies to gain consumer acceptance. I also illustrated how <a href="http://www.hdtvmagazine.com/articles/2011/07/displaying-3dtv-images-what-is-wrong-with-this-picture.php">both TV technologies show</a> 3D images and briefly introduced LG’s new passive 3DTV. <p>In this article I illustrate how that 3DTV employs a proprietary feature to claim it displays the full resolution of the original 3D images, a claim LG used to challenge active-shutter glasses 3DTV technology known to be capable of full resolution per eye.  <p>As mentioned in other articles, passive technology discards half of the pixels of the original 3D images and LG’s implementation of the technology has a more complex pixel management. This article discusses that complexity. <p>Let us get started by exhibiting an example of 3D images I included on earlier articles to help the reader understand the subject graphically. <p>The pictures below represent the two angles of a 3D view of real life objects as recorded by a 3D camera. Only 4 pixels of the 3D image-pair are shown, they contain a miniature bottle/wine-glass viewed from both angles. <p>Why miniature objects within pixels? Video pixels contain very small picture elements that provide sharp details of larger objects in the image. The overall image quality of the whole screen can be affected if the pixels contain inaccurate information and the concept is easier to grasp by miniaturizing larger objects within the pixels.  <p>The left column on the pictures below shows pixel 1 of video lines 1 and 2 containing a recorded miniature bottle/wine-glass as viewed from the left angle. The bottle/glass are larger than one pixel, so the top part of the bottle/glass was recorded in Odd-line-1, and the bottom part was recorded in Even-line 2. The right eye column shows also pixel 1 of video lines 1 and 2 of the same miniature bottle/glass but viewed from the right angle recorded by the right camera, as follows: <p><b><u>Original 3D Images</u></b> <p><b><img style="float:none" src="http://www.hdtvmagazine.us/articles/images/ac2007513ad7_144FE/clip_image002_f28bd295-76ba-4ee7-a7cd-68e4c91c6237.jpg" width="591" height="315"></b> <p>Please refer to the previous articles linked above for more details. <p><b></b> <p><b>How LG Passive 3DTVs display 3D images?</b> <p>Typical passive 3DTVs discard 2 million pixels out of the 4 million pixels of the 3D image-pair, but LG’s 3DTV claims to use two 120Hz cycles to show all the 4 million pixels. <p>On the first 1/120 cycle LG’s TV uses the <a href="http://www.hdtvmagazine.com/articles/2011/07/typical-passive-3dtvs-displaying-and-perceiving-3d-images.php">same approach of typical passive 3DTVs</a> to display the two 1080p images corresponding to both angles. It extracts the odd lines from left image and interleaves them with the even lines from right image, illustrated in the two pictures below:  <p><b><img style="float:none" src="http://www.hdtvmagazine.us/articles/images/ac2007513ad7_144FE/clip_image004_1e297b2c-bd3f-43e7-a86e-b009f7f8e5c3.jpg" width="624" height="278"></b> <p><b></b> <p>Note the shifted view of the bottle behind the wine-glass when merging both angles. Please consult the previous two articles for more detail. <p>On the second 1/120 cycle the LG 3DTV shows the video lines that the first cycle ignored from the 3D Blu-ray disc to claim that the full resolution from both angles is displayed by their 3DTV, as follows: <p><img style="float:none" src="http://www.hdtvmagazine.us/articles/images/ac2007513ad7_144FE/clip_image006_13a749d8-ef54-4805-bcab-d553be9f70ea.jpg" width="626" height="471"> <p>Source: LG Display R&amp;D Department <p>LG’s R&amp;D team said they compared the color of 3D between LG CINEMA 3D and active-shutter type of 3DTVs and there was no color artifact in 3D mode, the measurement data of color was more accurate than active-shutter type even in 3D mode (I have not done a review of this subject to confirm LG’s claim).  <p>Let us closely view LG’s graphic above. <p>Notice that the left (blue) glass/eye only sees the incoming left image lines (L1, L2, L3, L4, ... L1080) using both 120Hz cycles of the TV, whereby the odd lines (L1, L3..) are shown on the first 120Hz cycle and the even lines (L2, L4..) are shown on the second 120Hz cycle.  <p>To be specific to our example, L1 and L2 of the incoming left image are both shown using line 1 of the TV’s Film-Patterned-Retardant which is polarized for the left eye. The first 120Hz cycle it shows L1, the 2<sup>nd</sup> 120Hz cycle shows L2. The line 2 of the TV is polarized for the right eye as all the even lines of the screen, and cannot be used to display left eye lines, as follows. <p>The right (red) glass/eye sees only the even TV video lines on all the cycles, which display the right image’s lines (R1, R2, R3, R4,… R1080). The even lines (R2, R4, etc) are shown on the first 120Hz cycle and the odd lines (R1, R3, etc.) are shown on the second 120Hz cycle. <p>In other words, the fixed polarization of the screen permits the odd 540 TV lines to be seen only by the left eye, while the even 540 TV lines can only be seen by the right eye. Although the missed 540 lines of the incoming image for each eye are actually pulled from the 3D Blu-ray disc and shown as claimed by LG, they are shown vertically shifted/reversed (L2 shows in TV line 1, R1 shows in TV line 2, etc.) on the second 120Hz cycle. <p>The approach of the two 120Hz cycles reusing the same TV pixels substantiate that each eye can never see the 1080 lines of the original images simultaneously, but rather see half of the lines first, followed by the other half of the lines in the next cycle using the same TV lines/pixels, inverting the pixels of each line-pair of the incoming images.  <p>Would that actually affect viewing as much as this analysis conveys? Perhaps no if the viewing is done from far away as most people usually view in their family rooms from more distance than recommended for a 1080p panel. Additionally, many may not notice image artifacts on real content perhaps for the same reason many declared not realizing if they were watching HD or SD, or not noticing a difference between DVD and Blu-ray. A darker image seen thru 3D glasses facilitate not noticing video artifacts.  <p><b>Is the TV fast enough to be Effective? </b> <p>According to LG Display, the pixel response time of the 3D panel is 5ms (measured G-to-G, grey transforming to grey again after passing thru black). The video frame refresh time of 120Hz is 8.33ms (1/120 second = 0.00833sec = 8.33ms). In other words, the pixel readiness to show new content (5ms) is faster than the 8.33ms duration the panel holds a video frame before it quickly displays the next video frame. <p>Although the speed of 5ms may be fast enough for the pixel to rapidly twist and turn and be ready to show the next video frame, the <a href="http://www.hdtvmagazine.com/articles/2008/01/lcd-specs-playing-with-your-eyes.php">sample-and-hold</a> style of LCD operation keeps showing a video frame in the screen until the last very moment of the 8.33 ms cycle, then quickly shows the next video frame and holds it again, in this case to show the pixels that were ignored from the disc by the first 120Hz cycle. <p>Although the pixels on the 2<sup>nd</sup> 120Hz cycle are typically ignored by other passive 3DTVs, many passive 3DTV LCD panels use faster speed features to mitigate motion blur on the half-resolution images. LG rather uses the second 120Hz to display the missed pixels. <p>Throughout the years LCD manufacturing improved panel designs to deal with the motion <a href="http://www.hdtvmagazine.com/articles/2008/01/lcd-specs-playing-with-your-eyes.php">blurriness problem</a> typical of the sample-and-hold operation of LCD, they did by increasing the frame rates to 120Hz, 240Hz, and even 480Hz. Using those frame rates the LCDs may interpolate additional video frames with motion-calculated-content that looks ahead the next video frames to apply video processing with motion adaptation to mitigate blurriness, or to interpolate black frames, or dark frames, or simply repeat the same video frames faster to smooth out the video presentation without altering the artistic film cadence and appearance of 24fps for example. <p>To been able to show all the pixels of the original image-pair at full resolution as claimed by LG, the speed of the 3DTV actually is 60Hz, since it takes two cycles of 120Hz to display the two half-lines of the image-pair. <p>Therefore, the challenge for LG’s 3DTV is quite complex, is not only about showing 4 million pixels on a 2 million pixel panel, and for the pixels of both 120Hz cycles to keep up with fast motion to mitigate blurriness, but also about using the same polarized TV lines to display content sourced from adjacent video lines on second 120Hz cycles, show the pixels inverted, which increases the risk of more video artifacts, and still produce a decent image “that consumers prefer”, as LG says. <p><b></b> <p><b>Inverted Video Lines, how would they look? </b> <p>If you look closely the LG’s graph you may notice that the second 120Hz cycle uses line-1 of the TV to actually display line-2 of the left angle. L2 was omitted on the first 120 Hz cycle (bottom of bottle/glass viewed from left). <p>The second 120Hz cycle also uses line-2 of the TV to actually display line-1 of the right angle. R1 was also omitted on the first 120Hz cycle (top of bottle/glass viewed from right). <p>In other words L2 and R1 are shown in the same video frame in inverted order, as follows: <b></b> <p><b><img style="float:none" src="http://www.hdtvmagazine.us/articles/images/ac2007513ad7_144FE/clip_image008_8e33e007-5d95-4b53-9134-b0cac52bed24.jpg" width="624" height="277"></b> <p>Regardless which eye sees which angle, the bottom of the glass/bottle is incorrectly shown above the glass/bottle top. The content is shown up-side-down due to using the fixed polarization of the TV’s Film-Patterned-Retarder to show more lines than what it was designed for. Neither the 3D glasses nor the eyes are capable, nor are supposed, to reverse back the effect of the inverted lines. <p>I told LG Display R&amp;D my concern that “the second 120Hz cycle inverts the vertical order of the content of the original video lines”, LG Display responded as follows: <p>1<sup>st</sup> LG response: <i>“I understand your concern about it, but please consider the lines of information that reaches human eyes. Because user always watch 3D TV wearing 3D glasses, on 1st 120Hz cycle, left eye can see L1, L3, L5, L7... and right eye can see R2, R4, R6, R8..&nbsp; On 2nd 120Hz cycle, left eye can see L2, L4, L6, L8... and right eye can see R1, R3, R5, R7... Therefore human eye can see correct order even on 2nd 120Hz cycle. Please refer to the below picture that explain in more detail.”</i> <p>I insisted the order was reversed for the eyes as well and got a 2nd LG response:<i> “You are right if you consider the vertical order when you see the LCD panel without 3D glasses. But even though the vertical order is inversed on second 120Hz cycles at the LCD panel, (the) human eye can see right order through the 3D glasses.“</i> <p>Although LG admitted inverting the lines at the screen, I disagreed with the justification of the “human eye can see the right order”. As mentioned before, my understanding is that 3D glasses for passive-polarized 3DTV technology do not have the ability to reverse back an already inverted vertical order of lines as displayed by the Film-Patterned-Retardant (FPR) with fixed polarization. The eyes see a separation of left and right lines but maintaining the same vertical order displayed by the TV. <p><b></b> <p><b>Is LCD Motion Blurriness and Persistence of Vision not important anymore in 3D?</b> <p><img align="left" src="http://www.hdtvmagazine.us/articles/images/ac2007513ad7_144FE/clip_image010_84f1e1ed-a6d9-41b0-854d-d2b58fdd6739.jpg" width="349" height="280">Generally, image <a href="http://www.hdtvmagazine.com/articles/2008/01/lcd-specs-playing-with-your-eyes.php">blurriness</a> may occur when video content in motion moves faster than the display speed of an LCD panel. Over the years LCD panels have improved performance with faster speeds (such as 240Hz, 480Hz, etc.) to mitigate 2D motion blur. Likewise, 3D would need to mitigate motion blur for two images. <p>The <a href="http://www.hdtvmagazine.com/articles/2008/01/lcd-specs-playing-with-your-eyes.php">sample-and-hold</a> technique may not give sufficient time for a pixel to reset properly and be ready to show the next content. Additionally, the persistence of vision could make the eyes perceive the image as the “Times Square in the beach” example, not to mention if the palm tree/surf board would be shown up-side-down relative to Times Square.  <p>Regardless of the theoretical aspects of the subject of this article, do you think that LG’s half-resolution-plus-pixel-reversal method would show a better image than typical half-resolution passive technology, or active-shutter technology? Your eyes, knowledge, and pocket should decide that. <p><b>Interleaving 3D angles vs. Interlacing i-lines. Are they the same?</b> <p>The concept of “interlacing” video lines has been implemented by the analog-NTSC-TV system for over the past 60 years, and also inherited by DTV’s 480i/1080i standards for a similar objective, to maximize the available bandwidth for transmission of higher vertical resolution video. The human’s brain and persistence of vision was expected to merge the lines together in a seamless image without objectionable flicker. It was not perfect but the standard did a decent job considering the limitations and alternatives.  <p><a href="http://en.wikipedia.org/wiki/File:Interlaced_video_frame_(car_wheel).jpg"><img align="left" src="http://www.hdtvmagazine.us/articles/images/ac2007513ad7_144FE/clip_image012_e6fadadc-e4be-4dc9-a4d4-d8d1fb31bb84.jpg" width="220" height="166"></a> Although the interlaced technique merges video lines (fields) to complete a video frame, all the pixels of the merged lines come from their own x/y location within the original image although they are not recorded by the camera at the same exact time. If the subject (car on the picture) or the camera moves while recording the image, it can produce jaggy artifacts when displayed, even when merged and displayed progressively as a single video frame by a digital DTV. <p>A passive 3DTV, including LG’s technique, displays the image by “interleaving” (half of the) lines extracted from each image of the 3D image-pair, which was recorded at the same exact time if sourced from film and progressively recorded at 24fps.  <p>Although the lines originated (or transferred) from progressive 3D images and were recorded at the same time there could still be display artifacts considering that half of the lines were dropped, dual angles were merged, and inverted pixels (like LG’s second 120Hz cycle) were interleaved. Interleaving lines sourced from dual 3D 1080i interlaced images recorded by 3D video cameras can add interlacing-type of jaggy artifacts to the factors mentioned above (half-lines/dual-angles/inverted-pixels). How good is your brain? It uses <a href="http://www.charlierose.com/view/interview/10727">25% of its power for vision</a>.  <p><b></b> <p><b>Conclusions</b> <p>Recently LG claimed that consumers favor passive 3DTVs over active-shutter 3DTVs in approximately 3 to 1 proportion based on a test conducted at their request. Samsung defended the active-shutter industry, contested the claim, and declared that sales of their 3DTVs show exactly the opposite (reportedly 6 to 1 in favor of active-shutter sales). To which LG declared that their passive sets have recently been introduced and sales are expected to gradually shift to passive technology as consumers preferred it over the active-shutter sets introduced since mid-2010.  <p>Regardless of the advertising battles, LG’s claim of displaying the full resolution of the 3D Blu-ray disc by their passive 3DTV is quite clever considering the vast majority of consumers are technically misinformed and do not understand or care about how the pixels are displayed, but the claim is misleading behind a numbers-game to gain a market edge, in addition to the appealing factor of low cost passive 3D glasses. <p>However, could LG claim that they show all the 1080p lines per eye of the 3D Blu-ray progressive video frame? Theoretically yes, but the “how” and “where” the pixels are displayed cannot be ignored when analyzing the claim and evaluating image quality.  <p>As you may have concluded after this reading, regardless “which” pixels are displayed “where” on the second 120Hz cycle, LG’s 3DTV still renders an image that has no more than 540 video lines per eye at any given displayed video frame, similarly to other passive 3DTVs, which is half the resolution of 3D Blu-ray and active-shutter 3DTVs per eye.  <p>Reusing the same TV pixels to show another half-resolution-3D-image during the second 120Hz cycle to display content from adjacent video lines should not qualify for the claim of full resolution per eye because of the lack of simultaneity and line order of 1080p picture information for the displayed image. <p>Considering that the pixel content is shown shifted and up-side-down half of the time, the method can potentially show visual artifacts, not to mention if the content experiences fast motion beyond the processing capacity of the panel, which is limited to just 60Hz to show all the picture information of 3D, a speed that was left behind several generations ago by the LCD industry due to the motion blur issue. <p>As mentioned above, due to the fixed nature of the polarizer Film-Patterned-Retardant attached to the screen (odd-lines-for-left-eye and even-lines-for-right- eye) it is not physically possible for either eye to simultaneously see together all the 1080p lines for the given eye, like active-shutter does. <p>Quite frankly, I did not notice the major artifacts I expected to see when “informally” viewing LG’s passive 3DTVs from appropriate distance for a 1080p set, but I did notice the following:  <p>a) Distracting black horizontal lines of the film-patterned-retarder separating eye views (which reminded me of the effect of lenticular screens of rear-projection-CRTs more than a decade ago). The black lines were very noticeable on the white image of the 3D demo introduction, ironically made by LG to promote the TV,  <p>b) The limited vertical and horizontal angle of view, affecting contrast, color, brightness, like most LCDs, and  <p>c) The need for a minimum of 6-feet viewing distance to avoid noticing left/right images separating from each other, rather than merged in a 3D depth illusion. <p>I have not experienced any of the above with active-shutter 3DTVs. Lab tests would be useful to properly evaluate the actual merits of the second 120Hz cycle technique vs. its theoretical impact to picture quality.  <p>Is the <a href="http://www.charlierose.com/view/interview/10727">human brain smart</a> enough to simultaneously resolve the mixed angles of different video lines, reverse order line-pairs, half resolution images, etc. and still perceive an appealing 3D image? <p>Among the current implementations of 3DTV, the active-shutter technology seems to offer better quality because it a) displays pixels from both angles matching their relative position within the original images, b) displays full resolution for each eye (HDTV and Blu-ray were attractive to consumers precisely due to higher quality images, and 3D should be about 2 good HD images), c) is risk-free of interleaved/interlaced artifacts when viewing 3D Blu-ray progressive content because there is no mixing of angles with half resolution images and no merge of video lines that belong to other positions of the original image, and d) has no polarized layers on the screen that could affect 2D viewing. <p>Regardless how harmless for 2D viewing the polarizer layers of passive 3DTVs (such as the Film-Patterned-Retardant layer) are claimed to be, if the layers are <a href="http://www.hdtvmagazine.com/articles/2011/06/3d-technology-damaging-hd-viewing.php">in the path of all viewed images</a> they could affect <a href="http://www.hdtvmagazine.com/articles/2011/03/is-3dtv-a-replacement-of-digital-television-would-2d-viewing-be-affected.php">2D image quality</a> and the issue should be evaluated in light that most viewing is and for the near future will be in HDTV. <p>Recently Samsung demoed an LCD 3DTV panel that <a href="http://www.ultimateavmag.com/content/samsungreald-activepassive-3d-flat-panels">dynamically polarizes</a> the whole screen (the full set of 1080p TV lines) allowing the use of low-cost passive 3D glasses. Such method can actually claim that the whole 1080p content can be seen at once per eye using low-cost 3D glasses. The technology has been introduced only for LCD panels (no plasma), it was said to be more expensive although there were no specifics, and the whole screen polarization mechanism must still demonstrate that it can be fully removed out of the image path for HD viewing. <p>Ultimately, consumers should research which is the most appropriate 3DTV technology for them based on their preferences and hopefully equipped with accurate information.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>August  3, 2011  8:11 PM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(4463)?>
			<div class="dottedline"></div>

			<? if (6 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 4463)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/test/2011/08/lgs-passivepolarizedglasses-3dtv-where-is-my-pixel.php" type="text/javascript" charset="utf-8"></script>
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
