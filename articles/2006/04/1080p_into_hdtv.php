<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
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

	function getByASIN($asin) {
		global $admindata, $debug;

		# First check to see if it's in the database
		$sql_amazon = "SELECT * FROM az_main m, az_attributes a WHERE m.ASIN = '$asin' AND m.ASIN = a.ASIN";
		$res_amazon = mQuery($sql_amazon);
		if (mysql_num_rows($res_amazon) != 0) return mysql_fetch_assoc($res_amazon);

		# Not found ... try to get dynamically
		$parameters = "AWSAccessKeyId={$admindata['amazon_access_key']}".
		"&AssociateTag={$admindata['amazon_associates_id']}".
		"&ItemId=$asin".
		"&Operation=ItemLookup".
		"&ResponseGroup=ItemAttributes,OfferSummary,Images".
		"&Service=AWSECommerceService".
		"&Timestamp=". gmdate("Y-m-d\TH:i:s\Z") .
		"&Version=2009-11-01";
		$parameters = str_replace(array(':',','), array('%3A','%2C'), $parameters);

		$signature = base64_encode(hash_hmac("sha256", "GET\nwebservices.amazon.com\n/onca/xml\n$parameters", $admindata['amazon_secret_access_key'], true));
		$signature = str_replace(array('+','='), array('%2B','%3D'), $signature);
		$signed_request = "http://webservices.amazon.com/onca/xml?{$parameters}&Signature=$signature";

		$contents = file_get_contents($signed_request);
		$xml = new SimpleXMLElement( $contents );

		# Verify a successful request
		if (is_object($xml->OperationRequest->Errors->Error)) {
			foreach($xml->OperationRequest->Errors->Error as $error) {
				$err = "Error {$error->Code}: (line ". __LINE__ .") $error->Message\n";
				mail("shane@hdtvmagazine.com", "ERROR: "& $_SERVER['SCRIPT_URL'], $err);
				return '';
			}
		}

		$az_main = array();
		$az_attributes = array();
		$item = $xml->Items->Item;
		$az_main['ASIN'] = $item->ASIN;
		$az_main['DetailPageURL'] = $item->DetailPageURL;

		if (is_object($item->ItemAttributes)) { # Update az_item_attributes
			$az_attributes['ASIN'] = $item->ASIN;
			$az_attributes['Brand'] = $item->ItemAttributes->Brand;
			$az_attributes['EAN'] = $item->ItemAttributes->EAN;
			$az_main['Weight'] = $item->ItemAttributes->ItemDimensions->Weight;
#			$az_main['Label'] = $item->ItemAttributes->Label;
			$az_main['ListPrice'] = $item->ItemAttributes->ListPrice->Amount;
			$az_main['ListPriceFormatted'] = $item->ItemAttributes->ListPrice->FormattedPrice;
			$az_attributes['Manufacturer'] = $item->ItemAttributes->Manufacturer;
			$az_attributes['Model'] = $item->ItemAttributes->Model;
			$az_attributes['MPN'] = $item->ItemAttributes->MPN;
			$az_attributes['ProductGroup'] = $item->ItemAttributes->ProductGroup;
#			$az_main['ProductTypeName'] = $item->ItemAttributes->ProductTypeName;
			$az_attributes['Publisher'] = $item->ItemAttributes->Publisher;
			$az_attributes['Studio'] = $item->ItemAttributes->Studio;
			$az_attributes['Title'] = $item->ItemAttributes->Title;
			$az_attributes['UPC'] = $item->ItemAttributes->UPC;
		}

		if (is_object($item->OfferSummary)) { # Update az_main (might want to put into a separate table)
			$az_main['TotalNew'] = $item->OfferSummary->TotalNew;
			if ($az_main['TotalNew'] > 0) {
				$az_main['LowestNewPrice'] = $item->OfferSummary->LowestNewPrice->Amount;
				$az_main['LowestNewPriceFormatted'] = $item->OfferSummary->LowestNewPrice->FormattedPrice;
			}
			$az_main['TotalUsed'] = $item->OfferSummary->TotalUsed;
			if ($az_main['TotalUsed'] > 0) {
				$az_main['LowestUsedPrice'] = $item->OfferSummary->LowestUsedPrice->Amount;
				$az_main['LowestUsedPriceFormatted'] = $item->OfferSummary->LowestUsedPrice->FormattedPrice;
			}
			$az_main['TotalRefurbished'] = $item->OfferSummary->TotalRefurbished;
			if ($az_main['TotalRefurbished'] > 0) {
				$az_main['LowestRefurbishedPrice'] = $item->OfferSummary->LowestRefurbishedPrice->Amount;
				$az_main['LowestRefurbishedPriceFormatted'] = $item->OfferSummary->LowestRefurbishedPrice->FormattedPrice;
			}
		}

		if (is_object($item->SmallImage)) { # Update az_main (might want to put images in separate table)
			$az_main['SmallImageURL'] = $item->SmallImage->URL;
			$az_main['SmallImageHeight'] = $item->SmallImage->Height;
			$az_main['SmallImageWidth'] = $item->SmallImage->Width;
		}
		if (is_object($item->MediumImage)) { # Update az_main (might want to put images in separate table)
			$az_main['MediumImageURL'] = $item->MediumImage->URL;
			$az_main['MediumImageHeight'] = $item->MediumImage->Height;
			$az_main['MediumImageWidth'] = $item->MediumImage->Width;
		}

		# Update az_main
		$sql = "REPLACE INTO az_main (". join(", ", array_keys($az_main)) .", date_updated) VALUES ('". join("', '", array_values($az_main)) ."', NOW())";
		if ($debug) {echo "$sql\n";} else {mQuery($sql);}

		# Update az_attributes
		$sql = "REPLACE INTO az_attributes (". join(", ", array_keys($az_attributes)) .") VALUES ('". join("', '", array_values($az_attributes)) ."')";
		if ($debug) {echo "$sql\n";} else {mQuery($sql);}

		# Now that we KNOW it's in the database ...
		$res_amazon = mQuery($sql_amazon);
		return mysql_fetch_assoc($res_amazon);
	}

	# Get author information
	$res_author = mQuery("SELECT title, channel, amazon_tracking_id, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Rodolfo La Maestra'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Rodolfo La Maestra" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 360 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 360
		AND a.topic_id = t.topic_id";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	# Get Comments
	if ($row_aux['topic_replies'] > 0) {
		$comments = '<li class="li_horizontal"><img src="'. BASE_IMG_URL .'/images/icon_comments.gif" alt="" height="14" width="16" /> '.
		'<a href="/forum/viewtopic.php?t='. $row_aux['topic_id'] .'">'. $row_aux['topic_replies'] .' Comments</a></li>';
	} else {
		$comments = '<li class="li_horizontal"><img src="'. BASE_IMG_URL .'/images/icon_comments.gif" alt="" height="14" width="16" /> '.
		'<a class="red" href="/forum/viewtopic.php?t='. $row_aux['topic_id'] .'">Post First Comment</a></li>';
	}

	# Get Pricegrabber info
	if ($row_aux['pg_masterid'] != '') $row_pg = getByPGMasterID($row_aux['pg_masterid']);
	if ($row_pg != '') {
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=1080p into HDTV Displays&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
		$pg_url = $row_pg['url'];
		$pg_price = $row_pg['price_formatted'];
	}

	# Get Amazon info
	if ($row_aux['ASIN'] != '') $row_amazon = getByASIN($row_aux['ASIN']);
	if ($row_amazon != '') {
		$az_url = str_replace($admindata['amazon_associates_id'], $amazon_tracking_id, $row_amazon['DetailPageURL']);
		$az_image = '<img src="'. $row_amazon['MediumImageURL'] .'" alt="'. $row_amazon['Title'] .'" style="keyimg" height="'. $row_amazon['MediumImageHeight'] .'" width="'. $row_amazon['MediumImageWidth'] .'"/>';
		$review_header = <<<EOT
<table align="center" class="greygrid"><!--tr>
	<td style="font-weight:bold; text-align:center;" colspan="4">{$row_amazon['Title']}</td></tr>
<tr-->
	<td class="greygrid">&nbsp;</td>
	<td class="greygrid"><b>List</b></td>
	<td class="greygrid"><b>Street</b></td>
	<td class="greygrid"><b>Amazon.com</b></td>
</tr><tr>
	<td class="greygrid"><b>Current Pricing</b></td>
	<td class="greygrid">{$row_amazon['ListPriceFormatted']}</td>
	<td class="greygrid"><a href="$pg_url" target="_blank">$pg_price</a></td>
	<td class="greygrid"><a href="$az_url" target="_blank">{$row_amazon['LowestNewPriceFormatted']}</a></td>
</tr></table>
EOT;
	}

	# Set defaults which may be overridden by blog type below
	$container = 'article_container';
	$meta_medium_type = 'blog';
	$link_rel_image_src = $row_amazon['SmallImageURL'];
	$v_buttons = <<<EOT
<div id="dd_right"><ul>
	<li class="li_vertical">
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/articles/2006/04/1080p-into-hdtv-displays.php&amp;title=1080p into HDTV Displays">
		<span style="display:none">What are 1080p manufacturers doing on their current 1080p sets?  Are they really implementing all that 1080p can and should do?  Do people need all that 1080p can do?  When?  How could one find out if a set is actually suited to be ready for near future 1080p media, such as Hi Def DVD coming in a few months?

I will cover all those subjects gradually in short articles, but first let us mention a couple of key points.</span></a>
	</li>
</ul></div>
EOT;
	$h_buttons = <<<EOT
<div id="dd_right"><ul>
	<li class="li_horizontal">$comments</li>
	<li class="li_horizontal" id="tm_li"></li>
	<li class="li_horizontal"><a name="fb_share" type="button_count" href="http://www.facebook.com/sharer.php"></a></li>
</ul></div>
EOT;

	switch (1) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Articles Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 360";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download 1080p into HDTV Displays" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="1080p into HDTV Displays" />
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
	<title>HDTV Magazine - 1080p into HDTV Displays</title>
	<meta name="keywords" content="video processing, motion adaptive, pixel pixel, pixel motion, video processor, brillian, video, set, hdtv, quality, pixel, fps, even, deinterlacing, could, image, fields, sources, frames, motion, sets, inputs, generation, resolution, odd" />
	<meta name="description" content="What are 1080p manufacturers doing on their current 1080p sets?  Are they really implementing all that 1080p can and should do?  Do people need all that 1080p can do?  When?  How could one find out if a set is actually suited to be ready for near future 1080p media, such as Hi Def DVD coming in a few months?

I will cover all those subjects gradually in short articles, but first let us mention a couple of key points." />
	<meta name="title" content="1080p into HDTV Displays" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Articles Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />

	<script type="text/javascript">
		// Digg Script
		(function() {
			var s = document.createElement('SCRIPT'), s1 = document.getElementsByTagName('SCRIPT')[0];
			s.type = 'text/javascript';
			s.src = 'http://widgets.digg.com/buttons.js';
			s1.parentNode.insertBefore(s, s1);
		})();

		function init() {
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/articles/2006/04/1080p-into-hdtv-displays.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
		}

		function tweetMemeButton() {
			if (document.getElementById("tm_li")) {
				var iframeCode = '';
				iframeCode += '<iframe src="http://api.tweetmeme.com/button.js?url='+ escape(document.URL) +'&amp;style=normal&amp;source=SEOmofo&amp;service=bit.ly" scrolling="no" frameborder="0" width="50" height="61">';
				document.getElementById("tm_li").innerHTML = iframeCode;
			}
		}
		function getTMButton(url, style, source, service) {
			if (style == 'compact') {w = 70;h = 20;} else {w = 50;h = 61;}
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=360', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2006/04/1080p-into-hdtv-displays.php">1080p into HDTV Displays</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>April 17, 2006</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=11&category=Technology">Technology</a></b>
							</td>
						</tr><tr colspan="2">
							<td id="article_buttons" colspan="2"><?=$h_buttons?></td>
						</tr></table>
					</td>
				</tr>
			</table>

			<!-- Main Article Body -->
			<div id="<?=$container?>">
				<?=$v_buttons?>
				<?=$review_header?>
				<?=$az_image?>
				<blockquote>This is an excerpt from the <b>HDTV Technology Review 2006 Report</b> by Rodolfo La Maestra. If you are interested in the full version of this report, it is currently available from the <a href="/reports/hdtv-technology-review.php">HDTV Technology Review</a> page.<br>
<br>
The other parts in the series are:<br>
Part 1: <a href="/articles/2006/03/hdtv_technology_review_part_1_introduction.php">HDTV Technology Review, Part 1: Introduction</a><br>
</blockquote>

<p>What are 1080p manufacturers doing on their current 1080p sets?  Are they really implementing all what 1080p can and should do?  Do people need all what 1080p can do?  When?  How could one find out if a set is actually suited to be ready for near future 1080p media, such as Hi Def DVD coming in a few months?</p>

<p>I will cover all those subjects gradually in short articles in the HDTV Magazine, but first let us mention a couple of key points.</p>

<p>1080p resolution quality in displays, processors, players, recorders, pre-recorded media, etc. is rapidly becoming the next stage of this HDTV industry; the 1080p buzzword has been also loosely used to identify the "new breed of top quality HDTV sets."  In order to be actually ready for such level of quality throughout the HD system, digital display devices that claim 1920x1080p capabilities should be designed and suited to accept 1080p/24/30/60 fps signal from an external 1080p progressive source.</p>

<p>Not accepting 1080p from an external source will force the source to supply a 1080i version to the TV which would do the 1080p upconversion job with its internal/proprietary de-interlacer circuitry, typically not as good as one should expect of equipment at this level of resolution.</p>

<p>Regarding deinterlacing, do these new 1080p sets deinterlace properly 1080i?  What happens when is not properly done and you still want that TV?  One option could be to take that deinterlacing job outside the TV so a dedicated video processor can improve it.  However, if the TV does not accept 1080p, such limitation would preclude the use of a higher-quality 1080p video processor/scaler, which usually is expected to perform better 1080p upconversion, such as Faroudja, DVDO, Lumagen, or the Dragon Fly scaler/noise reduction implementing the new Silicon Optix "Realta" chip (a professional video technology originating from Teranex), among others.</p>

<p>Most people would consider irrational to spend $2000 on a 1080p video processor to feed a $3000 1080p HDTV just because the TV is weak in that area, but other people might consider the option of having 1080p inputs an important future proof feature that would allow the component approach for upgrading the overall quality of the HD system where and when is needed.</p>

<p>Separating the video processor from the display device to follow individual upgrade paths could be a good solution, especially for front projectors/large projection screens; the processor might be software upgradeable, while many HDTVs usually are not.  An owner of an otherwise good 1080p HDTV display might not like how the set handles the internal 1080p video processing that cannot be upgraded.</p>

<p>The higher quality of 1080p opens the opportunity to sit closer to the image and open the angle of view, which would immerse the viewer into a cinematic experience by enhancing the peripheral vision without sacrificing resolution; it also provides the possibility for using larger screens for a home theater environment.</p>

<p>However, viewing non-1080p content on a 1080p HDTV set that might have insufficient quality to properly upscale, deinterlace, and/or upconvert, could certainly produce a variety of video artifacts that would actually force the viewing position to be further back to avoid seeing them, which is the case of many of the first generation 1080p TV sets introduced over the last year; upgrading to a larger screen could accentuate the visibility of those artifacts.</p>

<p>Additionally, in many viewing situations the higher quality of 1080p resolution might not be noticed as an improvement by people accustomed to view the TV just as the typical TV box from far away; for those, a 1080i, or 720p, or even a 480p ED level DTV could be all they should need.  In other words, some people driven by the 1080p bug of "more is better, and I have to have it" might be paying extra for 1080p resolution they would never be able to see as an improvement on their room/viewing conditions.</p>

<p>The next part takes a look at an example of how some 1080p rear projection HDTVs are being implemented; on our first case we will step behind the technical curtain of Syntax-Brillian's new 1080p set.</p>

<p><br />
<h2>1080p by Brillian</h2><br />
Following with the subject of 1080p, this is the second part of the series of articles about the technology we will publish in the HDTV Magazine.  Today we will look behind the curtain of how Brillian had implemented their 1080p magic into their recently released LCoS rear projection set.</p>

<p>The company recently introduced their 65" 6580iFB 1080p LCoS set, which was slated to become available in 4Q05 and was the only size Brillian was planning to carry in 2005.  Brillian indicated that the video processing was implemented to get to the viewer all the resolution the 1920x1080 chip can promise, even with non-1080p sources.</p>

<p>During July/August of 2005, we held several technical exchanges with Vincent Sollitto (President and CEO), Hope Frank (Vice President of Marketing), and their technical team, continued with some meetings at the HDTV Display Search Conference held in Beverly Hills in late August, and culminated in January 2006 with a visit to their suite at CES to discuss with their engineers.</p>

<p>Although I have seen the RPTV myself in several opportunities, the following material should not be misinterpreted as my endorsement of the product, or a technical confirmation of some of the statements provided by Brillian.</p>

<p>The material might be more productive if the reader first becomes familiarized with the basic HDTV concepts of interlace and progressive I covered on other articles and the HDTV Glossary of this magazine; otherwise the information below could be a bit more technical than a casual reader might be comfortable with.  However, the subjects are covered with a tutorial approach, and are intended to help any reader to be acquainted with the concepts surrounding 1080p.</p>

<p><br />
<h2>Upconversion to 1080p</h2><br />
This 1080p set displays images at 120 fps; in Brillian's opinion the image quality obtained at that frame rate is much better than just 60 fps, which is typically what most other 1080p sets do.  The video processor does not perform motion adaptation when jumping the frame rate from 60 to 120 fps; Brillian considers it unnecessary.</p>

<p><u>480i (NTSC) Inputs</u>:  Brillian uses pixel-by-pixel motion adaptive deinterlacers with 3:2 cadence detection and compensation combined with advanced low angle interpolation to produce a 720x480p image.  According to Brillian, this conversion process is as good as any in the industry today.</p>

<p>Brillian then uses the highest quality scaling filters to upscale the image to 1440x1080, preserving the aspect ratio and converting from rectangular to square pixels.  If the user chooses one of the non-standard aspect ratios, the conversion will change to compensate.  For example, widescreen content viewed in the widescreen aspect ratio will be scaled horizontally to 1920, performing a one third stretch and converting from rectangular to square pixels.</p>

<p><u>1080i Inputs</u>:  As many current 1080p HDTV manufacturers do, Brillian treats 1920x1080i video as 1920x540p frames.  According to Brillian, to differentiate its set from the competition and ensure the highest quality 1080p image is presented; Brillian uses a proprietary set of sophisticated scaling filters to vertically scale the 1920x540 fields to 1920x1080.</p>

<p>As the next generation of image processors become more mature, the next generation 1080p units will incorporate hardware to perform the same high quality pixel-by-pixel motion adaptive deinterlacing on 1080i inputs Brillian currently only uses on 480i inputs.  Brillian stated: "Our next generation of products with pixel-by-pixel motion adaptive deinterlacing of 1080i sources will be brought to market when they are mature and don't cause more issues than they solve."</p>

<p><u>Progressive Inputs</u>:  Brillian accepts the standard 480p and 720p video formats as well as a multitude of PC formats such as VGA, SVGA, XGA, SXGA, and 1080p.  Brillian uses the highest quality scaling filters to convert these images to the 1920x1080 panels with options to preserve the aspect ratio or fill the screen.</p>

<p>A note on scaling filters:  Brillian does not use simple interpolation to scale the incoming data to fill its panels.  Interpolation, even the more advanced techniques, can cause loss of detail and in general have uncontrolled effects on the images.  Brillian uses up to 320 tap FIR filters to perform the image resolution conversion.  The use of FIR filters allow for control of the resulting image sharpness, which Brillian provides as its Picture Filter Modes.  Additionally, these scalers are multiregional, allowing for non-linear scaling to execute Brillian's Extended aspect ratios.</p>

<p><br />
<h2>Deinterlacing Implementation</h2><br />
Brillian does not add special artificial frames not intended by the material authors, however unless the image already comes externally as 60 fps, the set would have no other choice than to create 60 progressive frames from the provided 60 interlaced fields using pixel by pixel motion adaptive deinterlacing (480i).</p>

<p>Further, if the original material was 24fps from film, then the 60 interlaced fields need to be converted to 60 progressive frames using inverse 3:2 pull-down.  Given such video processing, I questioned if the pixel-by-pixel motion adaptive deinterlacing is also used for the added frames, in addition to the motion adaptation used for joining the fields.</p>

<p>They clarified that in their view 1080i deinterlacing is really no different than 480i deinterlacing and follows the same rules or patterns.  Standard video sources (those recorded interlaced) are handled by combining each field with the previous taking into account motion to prevent combing or blurring effects.</p>

<p>If the 60Hz interlaced source has the following fields A, B, C, D, E, then the process produces progressive frames 1-4 which are 1 (a combination of fields A and B), 2 (a combination of fields B and C), 3 (a combination of fields C and D), 4 (a combination of fields D and E) and so on.</p>

<p>In some sense, blending these fields together does produce images unique from the original material but motion adaptive deinterlacing should further reduce the artifacts generated by the process.  By how much and if it will be noticeable at all will highly depend on the content.  The result is something close to what would be viewed on a phosphor based monitor where only the lines contained in each of the fields are actively driven and decay while the other lines are driven on the next field.</p>

<p>Film sources at 24Hz have progressive frames A, B, C, D.  These sources are converted to 60Hz interlaced formats (like 480i and 1080i) by showing half the lines (odd) of A, then the other half of the lines (even) of A, then the first half of the lines (odd) again of A, then half the lines (even) of B are shown, followed by the other half of the lines (odd) of B, etc.  So the 60Hz fields sequence is A odd, A even, A odd, B even, B odd, C even, C odd, C even, D odd, D even.</p>

<p>According to Brillian, the proper way to deinterlace this content is to merge the even and odd lines of A to form one progressive scan frame and show it once for each original interlaced field or 3 times for A, C and correspondingly 2 times for B, D.  The de-interlaced 60Hz outcome results in the original film frames being shown A, A, A, B, B, C, C, C, D, D.</p>

<p>Therefore, 60Hz is always derived without adding unique frames.  Certain frames are repeated for film sources, but they are not altered just repeated.  This ensures that the Brillian image quality remains as the author intended, versus trying to combine the fields from two separate frames of film material, which would create unintended blurry images.</p>

<p>The 1080p set does not do 3:3 video processing to display 72 frames from 24fps sources, but rather upconverts the 24 to 60 fps (Pioneer Elite plasmas are known to have the 72fps capability, more suitable for displaying film based content)</p>

<p><br />
<h2>1080p Acceptance</h2><br />
Brillian reassured that their 1080p set is capable to accept an external 1080p signal on its digital (DVI) input, as 24, 30, or 60 fps.  The set's hardware can support 1920x1080p 24Hz and 30Hz ATSC standards.  This includes the transmission of the video data to the display section without altering the resolution of the 1920x1080p image.</p>

<p>An accepted 60fps 1080p signal is passed to the display as is without video processing, however, 24fps and 30fps DVI inputs are currently frame rate converted to 60fps using a video buffer with some loss of temporal/spatial resolution pixels due to video processing (about 30%).  Future software upgrades may overcome this performance degradation.  As these sources become readily available, Brillian's software can be upgraded to take full advantage of this hardware path (more on it further down).<br />
The TV's hardware can support 1920x1080p at 24Hz and 30Hz on the VGA and High Definition Component inputs.  However, 60Hz 1920x1080p analog sources will be too fast for the system.  The A/D converter itself is only 140MHz, so the VGA 148.5MHz standard will not run cleanly.  All the circuitry past the A/D converter is fast enough for 1080p 60Hz at 148.5MHz, up to and including the display's pixel matrix.</p>

<p>If the source of the material supports the CEA standard timings for 1080p at 24Hz or 30Hz, the set will be able to display this format.  However, since analog sources are not data enabled like DVI/HDMI, the source needs to provide the correct timing formats or else the data will not be detected and displayed properly.</p>

<p>It is important to note that although I am very specific on quoting some limitations on the way this set accepts 1080p (because readers looking for that feature deserve honest detail), the fact that the set actually accepts 1080p is putting this set in a very unique class of only a couple of first generation RPTV sets available today.  Brillian has made the effort to provide 1080p inputs on this first generation and that has an important future-proof value that most other manufacturers could not match on their recently released 1080p sets, although some have already announced their plans to provide such feature in the near future.</p>

<p><br />
<h2>Upgradeability</h2><br />
As these 24Hz and 30Hz 1080p sources become more prevalent the Brillian software may need to be updated to support all the nuances of the video timing, but the hardware platform is in place.</p>

<p>Brillian's current thinking is that there are so few devices providing material at this resolution and rate today that it is difficult to predict if they become more common and if the external sources will continue to conform to the standards.  Given this, Brillian said that software updates are available.</p>

<p>When inquiring about Brillian's plans of software upgradeability for TVs that were purchased with the current software, and how they could investment-protect consumers who buy the first generation 1080p model, the response was: "Brillian provides the new firmware on its website for home service technicians and home installers to access and install for customers who require the upgraded features.  The User's Manuals are also available to support the new firmware on the same web site."<br />
Brillian is working on the next generation video processing for 1080i deinterlacing to 1080p; the company indicated that they have no details as to how future hardware/software solutions for this feature would be implemented in current models, "if" it can be implemented as an upgrade.</p>

<p><br />
<h2>Integrated Tuners, FireWire, ISF, etc.</h2><br />
Although the following items are not necessarily related to the 1080p subject, consumers interested on this 1080p set might want to know how certain features are implemented.</p>

<p>Regarding tuning and connectivity capabilities, Brillian's 1080p set was suited with simple ATSC and Cable QAM on-the-clear tuners to meet basic tuning capabilities.  The CableCARD option was not pursued after an initial effort when finding out of the need to redo both tuner and Card to suit them for bidirectional capabilities, when implemented later.</p>

<p>The 1080p set does not have 1394 connections even though the hardware can support it from a design standpoint.  Brillian considered that the integrated basic tuners are not usually what customers of this type of TV use for HD reception, they typically use a Cable or OTA STB, which should have 1394 outputs to facilitate HD external recording (on D-VHS for example), in addition to possibly have integrated HD-DVR capabilities for time-shifting purposes.  The inclusion of 1394 interfaces on the second-generation sets will depend on market demand.</p>

<p>Brillian also showed at their CES suite a demo of a technology demonstration of a prototype 65" 1080p set that was actually a monitor configuration with a variety of external video processors showing how each performed 1080i to p deinterlacing.  This concept will offer videophiles the ability to have a true video system of components as audio does today.  Brillian also provided some insight into the performance achievable in future models, they also declared to be happy with the performance of the Silicon Optix chip.</p>

<p>The model that is in production has the ability to perform a wide variety of ISF calibration functions from the user menu (which could also be locked out to avoid accidental changes); there is no need to go to the service menu for the access to that functionality (as with other manufacturers, if they do provide access at all).  Some adjustments include selection of color palette (e.g. PC levels at 0-255 gray shades and TV levels at 16-235), 3 color-temperatures (normal 8500 Kelvin, cool 13000, warm 6500) that are also adjustable, sharpness filters, picture modes for each input, etc.</p>

<p>All typical menu settings such as contrast, brightness, etc., are set at halfway levels out of the box, as opposed to what many competitors do, usually cranking up the contrast and other settings to impress favorably on fluorescent lighted retail floors; many uninformed consumers continue using those settings at home, not obtaining the best image the set could provide at the home environment.</p>

<p>It also features a 200-page user manual I have not seen yet but quoted of exceptional clarity.  Upon purchasing this TV, an ISF (Imaging Science Foundation) technician visit is also included to perform calibration service for two inputs, which typically could run in the range of $300-$500 if hired separately; such feature is certainly an innovation among the competition, and shows that Brillian strives to produce the best quality image the TV could offer to a consumer.</p>

<p><br />
<h2>Brillian Moving Forward</h2><br />
According to Brillian, their sets distinguish themselves from other LCoS 1080p manufacturers in the way they employ an analog drive scheme with their pixel array, giving a much better result with less noise and contouring artifacts than the other digital implementations, such as JVC's DILA.  It's method of uniformity compensation is also unique and ensures even color rendering across the screen in solid images.</p>

<p>In the words of Brillian: "Pixelworks has been a good partner.  They have provided us a quality chip-set and base design kit.  Brillian's engineers have invested 2 years to customize the design to extract the distinguishing performance from the system."  Today they have a very capable system, which Brillian said is getting good reviews including Best HDTV of 2005 from several industry experts.</p>

<p>Moving forward to next generation designs, Pixelworks, along with all of the major video processor chip designers offer, will offer new chip sets to support the all-important pixel-by-pixel motion adaptive deinterlacing of 1080i sources.  Brillian continues to evaluate these chip sets, as well as those from other companies, to insure best in-class performance is delivered.</p>

<p>Silicon Optix is one such company under evaluation.  Their market buzz and pixel-by-pixel motion adaptive noise reduction makes Silicon Optix a player to be closely watched, Brillian said.  I have watched them and they have certainly progressed quite well judging by the manufacturers adopting their video processing technology since they introduced to the public their Realta chip at CES 2005, read the details at my HDTV Technology and CES 2005 report available at the pages of this HDTV Magazine.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>April 17, 2006  8:08 AM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(360)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 360)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author['bio_short'] != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Rodolfo La Maestra</h2>
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
		</td>
	</tr></table><br />

	<? include(BASE_DIR .'/includes/body_footer.php');?>
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2006/04/1080p-into-hdtv-displays.php" type="text/javascript" charset="utf-8"></script>
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
