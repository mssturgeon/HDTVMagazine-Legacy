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
	$res_author = mQuery("SELECT title, channel, amazon_tracking_id, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Shane Sturgeon" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 800 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 800
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=Which is More Consumer Friendly: HD DVD or Blu-ray?&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/articles/2007/11/which-is-more-consumer-friendly-hd-dvd-or-bluray.php&amp;title=Which is More Consumer Friendly: HD DVD or Blu-ray?">
		<span style="display:none">No, this is not the standard HD DVD vs. Blu-ray article that you may be used to reading. I am not declaring a &amp;quot;winner&amp;quot; because I think we are at a point now where neither camp is going away. Instead, this article explains which format I believe is the better choice for the consumer (you) this holiday season. Could that change a year from now? Sure, but I want to help you decide what to buy this year.

This article is not written in an attempt to convince anyone who has already made an investment one way or the other, for that is an almost impossible feat. It was written for those that are still &amp;quot;on the fence&amp;quot;, as they say. It is for those who are either undecided, or are waiting to see which one will come out ahead (or which will be first to waive the white flag). It's time to hop down off of that fence.</span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 800";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Which is More Consumer Friendly: HD DVD or Blu-ray?" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Which is More Consumer Friendly: HD DVD or Blu-ray?" />
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
	<title>HDTV Magazine - Which is More Consumer Friendly: HD DVD or Blu-ray?</title>
	<meta name="keywords" content="blu ray, dvd blu, high definition, dvd players, standard dvd, dvd, blu, ray, players, support, standard, consumer, buy, format, high, player, features, both, definition, any, formats, titles, those, mbit, movie" />
	<meta name="description" content="No, this is not the standard HD DVD vs. Blu-ray article that you may be used to reading. I am not declaring a &amp;quot;winner&amp;quot; because I think we are at a point now where neither camp is going away. Instead, this article explains which format I believe is the better choice for the consumer (you) this holiday season. Could that change a year from now? Sure, but I want to help you decide what to buy this year.

This article is not written in an attempt to convince anyone who has already made an investment one way or the other, for that is an almost impossible feat. It was written for those that are still &amp;quot;on the fence&amp;quot;, as they say. It is for those who are either undecided, or are waiting to see which one will come out ahead (or which will be first to waive the white flag). It's time to hop down off of that fence." />
	<meta name="title" content="Which is More Consumer Friendly: HD DVD or Blu-ray?" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/articles/2007/11/which-is-more-consumer-friendly-hd-dvd-or-bluray.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=800', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2007/11/which-is-more-consumer-friendly-hd-dvd-or-bluray.php">Which is More Consumer Friendly: HD DVD or Blu-ray?</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>November 26, 2007</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=280&category=Blu-ray">Blu-ray</a></b>
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
				<p>No, this is not the standard HD DVD vs. Blu-ray article that you may be used to reading. I am not declaring a &quot;winner&quot; because I think we are at a point now where neither camp is going away. Instead, this article explains which format I believe is the better choice for the consumer (you) this holiday season. Could that change a year from now? Sure, but I want to help you decide what to buy this year.</p>  <p>This article is not written in an attempt to convince anyone who has already made an investment one way or the other, for that is an almost impossible feat. It was written for those that are still &quot;on the fence&quot;, as they say. It is for those who are either undecided, or are waiting to see which one will come out ahead (or which will be first to waive the white flag). It's time to hop down off of that fence.</p>  <h2>Why Choose Either Format?</h2>  <p>First let's take a look at the benefits that these formats have over standard DVD and even HDTV.</p>  <ul>   <li><strong>Increased resolution</strong>. Both HD DVD and Blu-ray support video at 1080 lines of <a href="/glossary.php#Vertical+Resolution" target="_blank">vertical resolution</a>, compared to standard DVDs' 480. The <a href="/glossary.php#Horizontal+Resolution" target="_blank">horizontal resolution</a> is also greater at 1920 lines vs standard DVDs' 720. In total, high definition DVD will display 2 million pixels on the screen at any given time, compared to about 350,000 with standard DVD. That's 6x the resolution in the same area. </li>    <li><strong>Higher bitrate.</strong> Resolution is the easy one to put your finger on, but the secret to better picture quality is in the bitrate, or amount of information sent to your TV each second. Standard DVD is limited to about 11Mbit/s (Megabits per second) while cable, satellite and broadcast (over-the-air) can be delivered at up to 19Mbit/s (although 12-13Mbit/s is more common). Both HD DVD and Blu-ray can support bitrates in excess of 36Mbit/s. The result is a much more detailed picture, even during fast motion scenes that can wreak havoc on the over-compressed signals of cable &amp; satellite. </li>    <li><strong>Better audio.</strong> Next on the list has to be audio. Both HD DVD and Blu-ray support more advanced audio codecs than standard DVD, including the <a href="/glossary.php#Lossless" target="_blank">lossless</a> <a href="/glossary.php#Dolby+TrueHD" target="_blank">Dolby TrueHD</a> and <a href="/glossary.php#DTS-HD+%28%2B%2B%2C+and+Master+Audio%29" target="_blank">DTS-HD Master Audio</a>. Lossless codecs provide sound exactly as the content creator intended, with nothing lost due to compression. </li>    <li><strong>Features and Interactivity (Extras)</strong>. Standard DVD has some basic interactivity. I've seen some of my kids' DVDs that include rudimentary games, etc. But with high definition DVD, a whole new world opens up. I'll gloss over the gory details and just say that with these next generation formats it will be more like browsing the web than just clicking the down arrow twice and Play. Another big difference is that these next generation players have secondary video processors. This in essence gives you the ability to toggle on a picture-in-picture display while watching the movie. This secondary video stream can include any number of features like director's commentary, out-takes, unedited footage ... the possibilities are nearly endless. </li>    <li><strong>What about download?</strong> Most download services available today don't support high definition video. Those that do have HD available don't &quot;sell&quot; the content, they &quot;rent&quot; it. And until you are able to &quot;buy&quot; a digital copy to store on your computer and play back to any of your TVs at your leisure, I can't recommend it as an adequate next step for home movie viewing. Another thing to consider is that the hard drive space required to store these downloadable movies in the same quality as HD DVD and Blu-ray would cost between $10 and $15 per movie. When all is said and done, it would cost you almost twice current HD DVD and Blu-ray prices to buy movies via download.       <p></p>      <p>In my searching, I did find one high definition movie download service that allowed you to <strong>buy </strong>movies. It's called <a href="http://www.vudu.com" target="_blank">Vudu</a>, and they have a selection similar to most movie stores. With Vudu, you first buy a set top box for $399.99. This set top box can store up to 100 hours of purchased movies, which can be purchased for $20 - $25 (for new releases). Also, in order to have instant viewing of movies, they recommend an internet connection speed of 2-3 Mbit/s. This is a definite step in the right direction, but quite a bit more expensive than HD DVD and Blu-ray, and not very practical if you plan on having a large collection of movies.</p>   </li>    <li><strong>What about combination players?</strong> A good universal option. LG has one out this year that is fully compliant with both specifications and Samsung is supposed to have one out this year as well. The problem is that they are much more expensive ($999 MSRP), which is more than you would pay if you bought both HD DVD and Blu-ray players. I therefore cannot recommend dual format players to consumers quite yet. </li> </ul>  <p>Now that I've given you a few reasons to consider investing in these formats, let's hear what consumers have to say who have already made the leap to high definition DVD:</p>  <ul>   <li>According to our <a href="http://www.hdtvmagazine.com/studies/index.php" target="_blank">Fall 2007 HDTV Study</a>, more than 42% of respondents have made the investment<sup>1</sup> </li>    <li>90% of consumers who have invested are &quot;highly satisfied&quot; with their purchase<sup>2</sup> </li>    <li>Those that have a high definition player plan to replace 25% of their DVD library with their high definition counterparts<sup>2</sup> </li>    <li>Another figure from our <a href="http://www.hdtvmagazine.com/studies/index.php" target="_blank">Fall Study</a> shows that for respondents that are upconverting their standard DVD content, only about 15% think it's &quot;Good enough.&quot; </li> </ul>  <p>If none of that convinces you that you need to have one of these formats in your living room, you can stop reading. If you want to know why I think HD DVD is the best option, read on dear friend.</p>  <h2>Why HD DVD?</h2>  <p>My reasoning below is not based on which format has higher bitrate or more capacity, nor is it based on which one has more studios in its pocket, or more titles on the shelves ... as all those are about equal when you're looking at the screen. The reason I am recommending HD DVD is for the benefit to the consumer ... you.</p>  <ul>   <li><strong>Standardization</strong> - No matter what player you buy, it will play all HD DVD titles with full features. Since HD DVD players began shipping, they have had one <strong>standard</strong> set of requirements for their players. 100% of the HD DVD players on the market today must support a minimum set of features. I've listed below some of the features that are guaranteed to be on <strong>all</strong> HD DVD players, but might not be on all Blu-ray players:       <ul>       <li>Support of <a href="/glossary.php#Dolby+Digital+Plus" target="_blank">Dolby Digital Plus</a> </li>        <li>Support of Dolby TrueHD </li>        <li>2nd video decoder </li>        <li>2nd audio decoder </li>        <li>Internet support (network connection) </li>        <li>Region Free </li>     </ul>   </li>    <li><strong>Less Copy Protection</strong> - The HD DVD specification requires no copyright enforcement. The Advanced Access Content System (AACS) is mandatory for Blu-ray and optional for HD DVD, although many studios are using it. Blu-ray also includes additional content protection schemes such as BD+ and ROM-Mark watermarking. Each of these layers of protection add a level of complexity to the players and increased production and licensing costs of both players and media. It has also been <a href="http://www.highdefdigest.com/news/show/1035" target="_blank">reported by High-Def Digest</a> that additional copy protection may result in more lengthy load times. </li>    <li><strong>Features &amp; Interactivity</strong> - I've never been one to make use of the &quot;Extras&quot; on standard DVDs. Well, maybe the deleted scenes and out-takes, but that's it. With HD DVD, I find myself actually looking at these features before I buy a movie to see if there's anything original. This is a highly subjective point, but my argument here is that the consumer benefits by having these features and interactivity available to them, should they happen to enjoy them. </li>    <li><strong>Internet Updating</strong> - To date, the <strong>only</strong> Blu-ray player that can update its software/firmware via network connection is the Sony Playstation 3.<p class="editorial"><b>Editorial Note:</b> It was pointed out to me that the Samsung models BDP-1400 and BDP-1200 can also update via network connection. I apologize for the oversight (11/27, 12:01am EST)</p> Other Blu-ray players require you to either order a DVD with the update or download and burn your own update DVD. Sony's BDP-S300 recently had a firmware release and I was <a href="/forum/viewtopic.php?t=8693" target="_blank">attempting to help</a> someone on our <a href="/forum/index.php" target="_blank">forums</a> download and install it. I checked the page and there were about 25 steps to follow to get it updated, along with another dozen or so &quot;Important Notes&quot; of things to make sure you do (or not do) when updating ... not very consumer friendly. </li>    <li><strong>Better Price</strong> - I mention this last because I want to stress that there are a lot of other reasons to choose HD DVD than just the price, but it can't be ignored. With street prices of Blu-ray players around <a href="/equipment/model.php?man=Sony&amp;model=BDPS300" target="_blank">$357</a> ($499 MSRP) and street prices of HD DVD players around <a href="/equipment/model.php?man=Toshiba&amp;model=HDA2" target="_blank">$169</a> ($299 MSRP) ... it's just icing on the cake. HD DVD players have even sold <a href="/news/2007/11/toshiba_hd-a2_hd_dvd_player_drops_below_100.php" target="_blank">as low as $99</a> this month in various sales at retailers like Wal-mart and Best Buy. </li> </ul>  <h2>From the That's-Not-Quite-True Department</h2>  <p>There are a lot of &quot;facts&quot; and figures that get thrown around whenever someone sticks their neck out in favor of one format or another. In this case, those that have already invested in Blu-ray may throw up some strongly-worded arguments to my recommendation. Let me attempt to disarm some of them by stating below some things you're likely to hear/read, and why they're &quot;Not Quite True&quot;:</p>  <ul>   <li><strong>Blu-ray has more studio support than HD DVD</strong> - Of the six big movie studios in North America, three of them are Blu-ray exclusive, two of them are HD DVD exclusive, and one (Warner Bros) is producing in both formats. But what we're really talking about here is the number of titles available, not the number of studios supporting it.&#160; According to Wikipedia, as of October 31st, 2007, 332 titles are available in the US on Blu-ray and 328 on HD DVD<sup>3</sup>. And as of November 6th, 2007, Netflix has 378 Blu-ray titles and 345 HD DVD titles. Sounds about even. That being said, you also must take into account whether there are titles available from only one format that you must have. That alone can make all other advantages of one format over the other irrelevant.</li>    <li><strong>Blu-ray has more manufacturer support than HD DVD</strong> - This one is true, but I include it for what it means. Usually, more manufacturers mean more competition, which leads to lower prices. HD DVD is far less expensive than Blu-ray, so what good are all those manufacturers doing for the Blu-ray format? </li>    <li><strong>Blu-ray has higher capacity/bitrate than HD</strong> <strong>DVD </strong>- I'll give you that. Blu-ray players currently support discs with a capacity of up to 50GB while HD DVD is limited to 30GB (although 51GB HD DVDs were recently approved). Also, Blu-ray bitrates can run to 54Mbit/s while HD DVD is limited to 36Mbit/s. That being said, show me how that makes a difference with a side-by-side comparison of picture quality. I doubt it's $200 better from any consumer's point of view, and that is the guiding principle of my recommendation. </li>    <li><strong>Blu-ray can do all that added feature and interactivity stuff too</strong> - Yes, but only certain players can support it, and only certain disks have it. It should not be up to the consumer to keep track of whether a player can take advantage of a specific feature they see on the back of the package ... they should <strong>know</strong> it's supported regardless of their player. </li>    <li><strong>Target went Blu-ray exclusive, the end is near</strong> - Actually, Target just bought an end-cap. A quick check in their online store shows that they are selling both the <a href="http://www.target.com/Toshiba-HD-DVD-Player-HDA30/dp/B000U6AHYS/sr=1-2/qid=1195622207/ref=sr_1_2/601-5215395-5382501?ie=UTF8&amp;index=target&amp;rh=k%3Ahd%20dvd&amp;page=1" target="_blank">Toshiba HDA30</a> and the new <a href="http://www.target.com/Venturer-HD-DVD-Player-SHD7000/dp/B000W7O43U/sr=1-3/qid=1195622207/ref=sr_1_3/601-5215395-5382501?ie=UTF8&amp;index=target&amp;rh=k%3Ahd%20dvd&amp;page=1" target="_blank">Venturer HD DVD player</a>. Also, since when is Target a bellwether in retail consumer electronics? </li>    <li><strong>Blockbuster went Blu-ray exclusive, the end is near</strong> - Again, Blockbuster's announcement was not quite that far reaching. The Blu-ray exclusivity is limited to about 87% of their stores, and they are still making HD DVD available via online rental. Also, Blockbuster later issued a <a href="http://blockbuster.mediaroom.com/index.php?s=press_releases&amp;item=727" target="_blank">press release</a> that indicated that they would continue to stock more HD DVDs in their stores as demand increases. </li>    <li><strong>Paramount got paid $150 million for HD DVD support</strong> - True, but let's not pretend money is not changing hands all over the place in this contest. It's business, and that's how business is done. I hardly think this is a reason to dislike HD DVD. </li>    <li><strong>HD DVDs scratch more easily because they don't have the hard coating that Blu-ray has</strong> - Blu-ray does utilize a hard coating on the surface of their media that resists scratches. This had to be done because the data layer in a Blu-ray disc is so much closer to the surface than in HD DVD. Regardless, this does not mean that HD DVD's are more susceptible to scratching and damage. I contacted a popular online rental company and asked them about damage reports and disc durability of the two formats. According to them, there is no appreciable difference in the number of returns for either format. </li> </ul>  <h2>Conclusion</h2>  <p>I'll restate what I've said above, but without all the detail. Here is why I believe HD DVD is the best choice for the consumer this holiday season:</p>  <ul>   <li>All HD DVD players are standard, and you can feel confident that you will not have any issues playing back any HD DVD title on any HD DVD player. </li>    <li>Since all HD DVD players are internet-capable, any updates that you may have to do to your player can be done without complicated downloads, DVD burns and upgrade routines. </li>    <li>HD DVD is region-free, meaning that no matter in which country you buy your HD DVD, it will play in your player. </li>    <li>HD DVD media has less copy protection. Less copy protection means faster disc load times. </li>    <li>Sale prices for HD DVD players this holiday are around $100-$200, much more consumer- (and wallet-) friendly than sale prices for Blu-ray players, which are around $400. </li> </ul>  <p>I expect (dare I say hope) that this will generate a lot of conversation. It remains to be seen how much of it will be in opposition to the recommendation I'm making. I will close this article with a recent quote that I came across that seems to be quite apropos:</p>  <blockquote>   <p>Human beings are perhaps never more frightening than when they are convinced beyond doubt that they are right.      <br />      <br />- Laurens van der Post, explorer and writer (1906-1996)</p> </blockquote>  <p>With that said, I welcome your comments.</p>  <p>&#160;</p>  <p><font size="1"><sup>1</sup> Source: HDTV Magazine's </font><a href="http://www.hdtvmagazine.com/studies/index.php" target="_blank"><font size="1">Fall 2007 HDTV Study</font></a><font size="1">. The Study is still in-progress, but the data above is based on 1600+ respondents.</font></p>  <p><font size="1"><sup>2</sup> Source: The NPD Group, a leading retail market research firm</font></p>  <p><font size="1"><sup>3</sup> Source: Wikipedia article: </font><a href="http://en.wikipedia.org/wiki/Comparison_of_high_definition_optical_disc_formats" target="_blank"><font size="1">Comparison of high definition optical disc formats</font></a></p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>November 26, 2007  5:58 AM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(800)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 800)?>

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
		</td>
	</tr></table><br />

	<? include(BASE_DIR .'/includes/body_footer.php');?>
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2007/11/which-is-more-consumer-friendly-hd-dvd-or-bluray.php" type="text/javascript" charset="utf-8"></script>
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
