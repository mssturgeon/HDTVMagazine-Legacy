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
	$res_author = mQuery("SELECT title, channel, amazon_tracking_id, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Dale Cripps'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Dale Cripps" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 7 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 7
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=Digital Television Is Our Gift To The Next Generation&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/articles/2005/05/digital-television-is-our-gift-to-the-next-generation.php&amp;title=Digital Television Is Our Gift To The Next Generation">
		<span style="display:none">A few weeks ago I initiated discussions with several prominent officials in the field of H/DTV. I wanted, and still do, to assemble a set of authorities who can make comments that require no speculation as to what is being said on policy and products. We certainly don't need obfuscation now when clarity is absolutely essential. We cannot forget that we are tearing apart old institutions along with the way we have interacted with them for more than 50 years. We are replacing it all with what is still unknown--the panoply of services potential in the digital age.

</span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 7";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Digital Television Is Our Gift To The Next Generation" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Digital Television Is Our Gift To The Next Generation" />
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
	<title>HDTV Magazine - Digital Television Is Our Gift To The Next Generation</title>
	<meta name="keywords" content="high definition, digital high, local stations, consumer electronics, dtv transition, dtv, digital, our, broadcasters, analog, nab, transition, atsc, local, television, stations, congress, want, broadcast, consumers, year, fritts, work, been, consumer" />
	<meta name="description" content="A few weeks ago I initiated discussions with several prominent officials in the field of H/DTV. I wanted, and still do, to assemble a set of authorities who can make comments that require no speculation as to what is being said on policy and products. We certainly don't need obfuscation now when clarity is absolutely essential. We cannot forget that we are tearing apart old institutions along with the way we have interacted with them for more than 50 years. We are replacing it all with what is still unknown--the panoply of services potential in the digital age.

" />
	<meta name="title" content="Digital Television Is Our Gift To The Next Generation" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/articles/2005/05/digital-television-is-our-gift-to-the-next-generation.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=7', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2005/05/digital-television-is-our-gift-to-the-next-generation.php">Digital Television Is Our Gift To The Next Generation</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Dale Cripps</b> on <b>May 11, 2005</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=12&category=Global & Worldview">Global & Worldview</a></b>
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
				<p>A few weeks ago I initiated discussions with several prominent officials in the field of H/DTV. I wanted, and still do, to assemble a set of authorities who can make comments that require no speculation as to what is being said on policy and products. We certainly don't need obfuscation now when clarity is absolutely essential. We cannot forget that we are tearing apart old institutions along with the way we have interacted with them for more than 50 years. We are replacing it all with what is still unknown--the panoply of services potential in the digital age.</p>

<p>I shot for the highest authorities in the land.</p>

<p>I wanted, and still do, those key members of the H/DTV community to participate in a family of BLOGS with the aim of using the authority each brings to end speculations and confusions, and, thus, hasten the transition <em>realistically</em>.</p>

<p>CEOs, I learned, never write BLOGS. They leave that to others. They make speeches and write proclamations. Those CEOs I asked to join our family, including the author of the article below (Edward Fritts of NAB), and Gary Shapiro, CEO of the Consumer Electronics Association in Washington, respectfully declined my specific invitation but they quickly opened new doors for far better communications with you. The mission I had in mind became mostly accomplished through the focus we can now bring upon their statesman's made recently in official settings--statements not typically made privy to the general public. Without the full power of public knowledge acting upon the HDTV movement, it must suffer. In consumer electronics both ignorance and confusion seal shut wallets and prevent good things from happening -- often for years, sometimes forever. We want to bring that era of confusion and shortage of public education about HDTV to an abrupt end. _Dale Cripps</p>

<p>Edward O. "Eddie" Fritts is the CEO of the Washington, D.C. based National Association of Broadcasters (NAB). He has earned a reputation for being one of the most effective lobbyist in the the nation's Capital. He is retiring from his post in a few months time but will remain active in a town he has mastered. There are issues in communications which face the nation and the world and we can trust that Fritts will be among those clearing the way for our future. He understands the business he is in and while we may have differed with some of his positions, we never lacked respect for them nor for <a href="http://www.hdtvmagazine.com/articles/articles-author.php?id=5">Eddie Fritts</a>.</p>

<p>The ATSC held a large annual meeting yesterday in Washington where Fritts spoke in what will undoubtedly be a series of farewell speeches. I bring it to you in its entirety since all of the issues as seen from the broadcast perspective are articulated. _Dale Cripps</p>

<p><br />
<span style="font-size:130%;"><strong><em>Remarks by Edward. O. Fritts, President & CEO National Association of Broadcasters to the </em></strong><strong><em>Advanced Television Systems Committee Annual Membership Meeting May 10, 2005</em></strong><br />
</span><br />
Good morning.</p>

<p>First, let me acknowledge <a href="http://www.wrf.com/directory.cfm?attorney_id=634">Dick Wiley's </a>contribution to the digital and high definition television transition. It's hard to believe it's the 10-year anniversary of the FCC Advisory Committee's recommendation to the FCC for adoption of the <a href="http://atsc.org">ATSC DTV </a>Standard. Dick chaired that committee, as you know. Thanks to his work and the work of all of you in this room, we have made remarkable progress this past decade.</p>

<p>You may have heard that I'll be stepping aside as NAB President later this year. It's been such an honor to represent local broadcasters in Washington for nearly 23 years. I want to thank Mark Richer, Robert Rast, the ATSC Board and ATSC members for facilitating a productive partnership with NAB.</p>

<p>NAB is proud to have helped found ATSC over two decades ago. You've achieved key milestones on technical standards issues and by getting all parties involved in valuable cross-industry discussions. It's vitally important to have neutral forums where competing interests can debate and hammer out consensus. ATSC is just such a forum, and your successes have benefited us all.<br />
Since last year's ATSC Annual Meeting, I'm aware that several standards and recommended practices have been completed. I'm speaking of the ATSC Recommended Practice on Receiver Performance, the ATSC PMCP Standard, the ATSC Software Download Service standard, and the completed standard on Enhanced VSB transmission.</p>

<p>All these accomplishments demonstrate the success that can be achieved when competing interests work together.</p>

<p>Your mission is our mission: to bring the next generation of television to American consumers, with as little disruption as possible, and to oversee the continued evolution of digital and high definition television. We never envisioned this process to be a sprint, but rather a marathon.</p>

<p>And look how far we've come!</p>

<p>1,500 DTV stations now on air in 211 markets. Ninety percent of TV households in markets with five or more DTV stations  70 percent of homes in markets with eight or more DTV broadcasters. Broadcast HDTV is ubiquitous - in primetime, in late night, all the major sporting events, and high-profile events like the Oscars.</p>

<p>Collectively, broadcasters have invested billions to make DTV a reality. Local stations have put budgets and businesses on the line, and are delivering on the promise of digital.</p>

<p>So from the NAB perspective, we are on target with DTV. Broadcasters have pretty much built out the system. The bottom line is that this is no longer a broadcaster DTV transition; it's now a consumer DTV transition.</p>

<p>You know, part of the job that I will miss most when I leave NAB is sparring with my colleagues at competing trade associations. It's no secret that over the years, we've had some DTV policy dust-ups with our friends at NCTA and CEA.</p>

<p>Despite our differences, I've got great respect for Gary Shapiro and Kyle McSlarrow, along with Kyle's predecessors, and the companies that they represent. Last month, Gary received an award at our NAB Convention in Las Vegas. I was struck by one of his comments which appeared in the trades. The reporter wrote, and I quote: "Shapiro hailed a long and distinguished cooperation with NAB on technical standards. Despite headline-grabbing differences, the truth is that together, we (meaning CEA and NAB) have changed the world." End quote.</p>

<p>Gary's right on that one - we have changed the world of television. Think back to the 1980s, when we launched this DTV journey together. Some of you remember Ed Markey's hearings when Congress was worried the Japanese analog HDTV system would trump American technology.</p>

<p>We took a delegation to Japan to get a better look at their system, which at the time was analog HD and satellite delivered only.</p>

<p>But over time, U.S. companies developed and perfected the unthinkable: digital high definition TV.</p>

<p>Suddenly, home-grown technology leap-frogged the competition with the finest television pictures the world has ever seen.</p>

<p>We knew the obstacles would be many, and the difficulties would be great. But we worked with Congress, the FCC, and many of you in this room, and we're now on our way to completing the overhaul of how television is delivered to American homes.</p>

<p>We need to understand where we started to appreciate the progress that has been made.<br />
I'm confident the public policy stage is set to ensure that broadcasting plays a vital part of the digital future. Pioneers like Bill Paley and Stanley Hubbard emerged as giants of analog TV; so too will a new generation of broadcasters seize the digital world.</p>

<p>Just last month, Verizon CEO Ivan Seidenberg came to NAB's convention looking for partnership opportunities with local broadcasters. In my mind, that speaks volumes about the value of local television. Verizon and other phone companies know that broadcasters not only have a franchise on localism, but that broadcasting is still the "gathering place" for a nation.<br />
Local TV stations are expanding broadcasting's footprint on the Internet. Soon, our programming will be on cellphones and a multitude of other wireless devices, which bodes well for our future, too.</p>

<p>Admittedly, there have been twists and turns on the road to digital. And even a detour or two - last week's court decision overturning the broadcast flag was disappointing, and we'll work to correct that.</p>

<p>Some of us also get frustrated by the DTV myths. One myth is that broadcasters are profiteering off of DTV spectrum. Instead, the reality is that broadcasters have billions of dollars of stranded capital in this transition. At some point, there may be revenue generated by DTV; up to now, however, it has been a considerable cash drain on most local stations.</p>

<p>I get a chuckle from my cable friends when they claim they've spent $90 billion or $100 billion "investing" in the DTV transition. We all know that money is coming straight from consumers, who year in and year watch their cable bills rising 3 to 6 times the rate of inflation.</p>

<p>Broadcasters, of course, don't have the luxury of charging monthly fees. Our service is free to end users. Yet despite the huge cash outlays and enormous challenges, we have embraced the move to digital. We realize that local stations cannot remain competitive as analog players in a digital world.</p>

<p>The second big myth is that broadcasters want to hold on to analog spectrum indefinitely.<br />
Why would we want to do that? Why would stations want to continue paying tens of thousands of dollars in extra utility bills each year to send two signals -- both analog and digital?</p>

<p>The fact is broadcasters don't want to send two signals any longer than is necessary. There is no incentive whatsoever for local stations to want this transition to go on indefinitely.</p>

<p>Analog television will end, to be sure. The question for policymakers is when, and how do you not disenfranchise millions of consumers in the process.</p>

<p>We agree with those in Congress who warn of the potential for consumer outrage if this transition is not handled carefully. We should be mindful of the quote from Elliot Engle, a House telecom subcommittee member from New York. "If members of Congress turn off analog TV in 2006," he said, "we can all expect to be impeached in 2007."</p>

<p>We understand Congress's need for additional revenue from spectrum auctions. But it's noteworthy that the Congressional Budget Office now says that Congress will generate more revenue from spectrum auctions that are held later, not sooner.</p>

<p>Let me repeat that for added emphasis: The highly-respected, non-partisan Congressional Budget Office says analog TV auctions will generate more money for the Treasury if the auctions are held later, not sooner. So we're ready to roll up our sleeves and work with Congress on sensible DTV legislation.</p>

<p>Our priorities are straightforward:</p>

<p><strong>One:</strong> Deadlines that protect millions of Americans from losing access to local broadcasting;</p>

<p><strong>Two:</strong> Access to consumers for broadcast DTV programming carried on cable. Digital and high-definition TV is about consumers having more choice and better quality. Cable gatekeepers like Comcast and Time Warner ought not be allowed to deny consumers access to any broadcast digital programming. All free bits must flow to the consumer;</p>

<p><strong>Three:</strong> No cable headend down-conversion of broadcast programming from digital to analog;<br />
And Four: broadcast flag protection to ensure that high-quality programming not migrate away from free TV.</p>

<p><strong>Finally,</strong> I can't resist commenting on CEA's request to delay DTV tuner mandate rules. Let me say it again: this transition lets TV set makers share billions of dollars in the greatest transference of wealth in consumer electronics history.</p>

<p>If we're talking about ending analog TV, it makes no sense for manufacturers to flood the market this Christmas with millions of analog TV sets. That only elongates the transition.<br />
Rather than seeking delays in the tuner mandate, shouldn't we instead be labeling analog TV sets "soon to be obsolete?"</p>

<p>Last year at this meeting, I said that "DTV represents nothing short of a re-birth of over-the-air broadcasting. We think manufacturers should want over-the-air reception to be a principal feature in DTV sets, and to promote it as a primary, value-added feature. Consumers will appreciate the value of DTV broadcasts and the advantages of over-the-air reception compared with other distribution channels." That's still true.</p>

<p>In closing, let me reiterate NAB's willingness to work with lawmakers on this difficult issue. Our viewers are constituents of every member of Congress, and we need to be mindful that Americans have a timeless bond with local TV stations.</p>

<p>My friends, it's been a privilege for me to play a small role in this historic transition. Digital TV is our gift to the next generation. It is surely as important as the transition from black and white to color. Thank you, ATSC members, for your vision and guidance in this process, and thanks for your partnership with NAB these many years.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>May 11, 2005  9:42 AM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(7)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 7)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author['bio_short'] != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Dale Cripps</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2005/05/digital-television-is-our-gift-to-the-next-generation.php" type="text/javascript" charset="utf-8"></script>
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
