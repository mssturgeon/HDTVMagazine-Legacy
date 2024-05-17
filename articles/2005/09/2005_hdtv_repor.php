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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 210 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 210
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=2005 HDTV Report, Part 2: HDTV Implementation Update&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/articles/2005/09/2005-hdtv-report-part-2-hdtv-implementation-update.php&amp;title=2005 HDTV Report, Part 2: HDTV Implementation Update">
		<span style="display:none">The original plan for DTV targeted the ending of analog broadcasting by 2007.  The FCC provided each station with one additional 6 MHz channel slot so they can broadcast their current analog channel and the DTV version of it simultaneously during the transition period.

By 2007, or when 85 percent of the nation receives DTV, each broadcaster is expected to return to the FCC one of the two channels lent for the transition.  That space on the spectrum would then be available for auction by the FCC.</span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 210";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download 2005 HDTV Report, Part 2: HDTV Implementation Update" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="2005 HDTV Report, Part 2: HDTV Implementation Update" />
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
	<title>HDTV Magazine - 2005 HDTV Report, Part 2: HDTV Implementation Update</title>
	<meta name="keywords" content="digital cable, new york, cable operators, low income, million homes, dtv, cable, digital, million, analog, channels, sets, fcc, july, channel, billion, agreement, transition, homes, plan, subscribers, proposal, receive, feed, new" />
	<meta name="description" content="The original plan for DTV targeted the ending of analog broadcasting by 2007.  The FCC provided each station with one additional 6 MHz channel slot so they can broadcast their current analog channel and the DTV version of it simultaneously during the transition period.

By 2007, or when 85 percent of the nation receives DTV, each broadcaster is expected to return to the FCC one of the two channels lent for the transition.  That space on the spectrum would then be available for auction by the FCC." />
	<meta name="title" content="2005 HDTV Report, Part 2: HDTV Implementation Update" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/articles/2005/09/2005-hdtv-report-part-2-hdtv-implementation-update.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=210', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2005/09/2005-hdtv-report-part-2-hdtv-implementation-update.php">2005 HDTV Report, Part 2: HDTV Implementation Update</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>September 30, 2005</b>
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
				<blockquote>This is the second in a series of articles taken from the <b>H/DTV Technology Review & CES 2005 Report</b> by Rodolfo La Maestra, published in March 2005. If you are interested in downloading the full version of this report, it is currently available for purchase from our <a href="/store/ces-2005.php">CES Report</a> page.</blockquote>

<p><br />
<h2>Brief Summary of the DTV Plan</h2></p>

<p>The original plan for DTV targeted the ending of analog broadcasting by 2007.  The FCC provided each station with one additional 6 MHz channel slot so they can broadcast their current analog channel and the DTV version of it simultaneously during the transition period.</p>

<p>By 2007, or when 85 percent of the nation receives DTV, each broadcaster is expected to return to the FCC one of the two channels lent for the transition.  That space on the spectrum would then be available for auction by the FCC.</p>

<p>In 2002, television manufacturers and retailers were asked to adhere to a phased-in schedule that would lead to terrestrial OTA DTV tuners in all television sets by Dec 31, 2006.</p>

<p>The FCC then mandated that all TV sets 13-inches and larger and other products that normally carry TV tuners -such as VCRs, personal video recorders, etc. are to include ATSC terrestrial DTV tuners by July 1, 2007.</p>

<p>Under the five-year phased-in guidelines DTV tuners are to be added to 50 percent of sets measuring 36 inches and larger by July 1, 2004, and 100 percent by July 1, 2005.  After that, 50 percent of sets measuring 25 inches to 35 inches are to add DTV tuners by July 1, 2005, and 100 percent by July 1, 2006.  The rest are to conform by July 1, 2007. </p>

<p>A cable agreement plan was also approved for phased-in use of two digital interface connectors on new digital cable-ready TVs and/or cable set-top converter boxes, including a) Starting April 1st 2004, IEEE-1394 'FireWire/iLink' connections with Digital Transmission Content Protection (DTCP) for recordable and networkable compressed video streams, and b) By July 1, 2005, the non-recordable DVI/HDMI with High-bandwidth Digital Content Protection (HDCP) connections on digital televisions and cable set-top boxes.</p>

<p>The agreement was made for an integrated one-way only digital cable television tuner.  Under this unidirectional agreement, bi-directional features that require a return-path of the cable system, such as video-on-demand (VOD), impulse-pay-per-view, and the use of cable-operator enhanced electronic program guide services, provided by the Cable Operator, would not be available, and a separate STB would be needed for those integrated TVs.</p>

<p>By implementing this interactive version of POD, digital televisions would eventually be able to directly receive interactive digital programs without the need for a digital set-top-box from their local cable provider.</p>

<p>In August 2003, the FCC announced the updated progress in the establishment of the two-way interactive plug-and-play cable interoperability agreement.  Under this two-way interoperability agreement, sets with interactive functionality will be labeled "Interactive Digital Cable Ready."</p>

<p>Digital TV sets capable of displaying one-way programming services, including premium channels, would be labeled 'Digital Cable Ready', and they require smart POD cards that will be supplied by cable TV operators to unlock scrambled channels.  The POD card is now called "CableCARD."</p>

<p><u>CableCARD</u><br />
<img src="/articles/images/mt/image032.jpg" alt="CableCARD" align="left">According to the agreement, by July 2004, digital cable operators are to provide a CableCARD to subscribers that request one.  </p>

<p><br clear="all"><br />
<h2>FCC's Proposal to Accelerate DTV Transition</h2><br />
Federal Communications Commission (FCC) media bureau chief Ken Ferree proposed on April 2004 a plan to turn off the analog TV signals by 2009 switching to DTV.  Broadcasters would then return the frequencies they use for the transition.  A cable system would have to carry the signal on its analog or digital tier.  </p>

<p>If the signal were carried on the cable's analog tier, cable systems would have to down-convert the broadcasted digital signal so legacy analog sets could display it.  Cable customers on the digital tier would receive the broadcasters' digital signals.  As digital sets increasingly appear into the market, customers would gradually switch to the digital tier.</p>

<p>Both groups would be counted as part of the digital TV audience targeted as 85% to complete the transition from NTSC to DTV, contributing to a faster DTV implementation.</p>

<p>NAB and the industry declared on that opportunity that the proposal would have a negative effect on the transition and the public would not be motivated to replace existing analog sets.  National Cable and Telecommunications Assn. president Robert Sachs did not endorse the plan but was open to further talks.</p>

<p><br />
<h2>DTV Summit</h2><br />
On March 2004, the DTV Summit discussed that even though the growth for DTV has remarkable, the deadline of 2007 or 85% penetration could not be met as planned.</p>

<p>The CEA estimated that by then there would only be about 62 million DTV sets/monitors installed with a penetration of 53% of US homes, but with only 33% of homes tuning/displaying DTV signals.</p>

<p>The CEA also said that DTV is being implemented at twice the speed color TV was, considering that in five years DTV reached 1 million market penetration, while it took 10 years for color TV to reach the million mark.  Although my statistics indicate that in five years, about 4 million HDTVs were sold and the one million number was actually for HDTV STB tuners, so the speed of penetration is more than twice considering those numbers, consult the 2003 and 2004 CES reports for more information.</p>

<p>It was mentioned at the summit that broadcasters must be required to supply their signals at full power (two thirds use low power at the time of the Summit), and cable operators should be required to retransmit DTV broadcast the same way they receive it, without affecting resolution. </p>

<p><br />
<h2>Proposal for Subsidizing HD-STBs</h2><br />
On July 2004, it was reported that the New America Foundation (NAF) proposed to the Senate in a private meeting for government to subsidize OTA HD-STBs at $50 each (estimated chip of $25) and applying tax credits.  The proposal was based on a model implemented in Germany in 2003 to about 6,000 homes in the area of Berlin-Brandenburg.</p>

<p>The expenditure was estimated at $385 million nationwide, including low-income households (income inferior to $40,000) that currently receive TV by antenna.   </p>

<p>An scenario covering all US households (about 350 million analog TVs) would cost 4.1 billion dollars, reduced to 3 billion when considering tax credits, etc. which is about 4% of the money expected to be obtained when auctioning the spectrum that broadcasters are planned to return when DTV is fully implemented (about $70 billion). </p>

<p>A variety of alternatives where evaluated: </p>

<p>A) Consider homes that subscribe to cable or satellite for the main TV but tune via an antenna on another pair of TVs (17 million homes), such effort could cost approximately $870 million ($610 million represent low-income households that would be fully subsidized, the rest would be subsidized to 50%); </p>

<p>B) Cover 62.5 million homes (17 million homes above plus half of 90 million homes that receive cable/satellite) would cost approximately 3.1 billion dollars (2.25 billion corresponds to fully subsidizing low-income and half-subsidizing the others);</p>

<p>C) The NAB supported subsidizing all households (82 million), which would cost $4.1 billion (2.9 billion to fully subsidize all low-income for the first set, half for the rest); </p>

<p>D) All households would obtain STBs fully subsidized with tax credits.    </p>

<p><br />
The government is considering the establishment of a tax to the stations that do not comply with the DTV transition schedule; the collected money would fund the proposal of subsidized STBs. </p>

<p><br />
<h2>Senate Approves Plan to Facilitate DTV Transition</h2><br />
On September 2004, the US Senate agreed to a plan that would provide additional communications airwaves to police, fire and rescue organizations.  The spectrum would be obtained from some television broadcasters by the end of 2007. </p>

<p>The agreement was a compromise from a broader proposal submitted by Sen. McCain that would also force all television broadcasters to return the complete analog spectrum by 2009 (which was rejected); Senator Burns opposed due to the possibility of leaving some American households without receiving some local television stations when not having the appropriate digital equipment.  The Senate intelligence bill required the approval of the House of Representatives. </p>

<p>Some of the stations impacted by the amendment are Paxson, Univision, Viacom, and Tribune; they are within the 24MHz spectrum in the 700 MHz band that covers channels 63, 64, 68, and 69.</p>

<p>It is included into the compromise one billion dollars of subsidies to help consumers with equipment that would enable them to convert digital signals into analog to been able to view the digital channels, or to help consumers subscribe to cable or satellite to view those digital stations, both with the objective of not forcing consumers to replace their TV sets if they are not able to.  The funding from the subsidies originates from the moneys to be obtained from the auctioning of airwaves returned by the broadcast stations when they fully switch from analog to digital.</p>

<p><br />
<h2>Meeting the Analog Deadline</h2><br />
The FCC was expected to vote in November on a proposal to require the end of analog broadcasting by the end of 2009, but decided to postpone their vote until next spring at about the same time President Bush requested the Commerce Department to develop a plan to make sure the original deadline of December 31, 2006 is met.  </p>

<p><br />
<h2>Must-Carry Multicasting Channels</h2><br />
In December 2004, the FCC has confirmed their earlier opposition regarding forcing cable operators to carry any other broadcasted digital multicast sub-channel other than the primary.  The issue was brought by some broadcasters (including Paxon) to the United States Court of Appeals for the District of Colombia Circuit because they wanted cable operators to carry all of their multicast channels.  A vote was planned for February 10, 2005, before Mr. Powell departs from his post as FCC 's chair.</p>

<p><br />
<h2>H/DTV Programming</h2><br />
In past CES reports, I dedicated a section to the details on this subject.  I believe that now there are sufficient H/DTV channels to motivate adoption based on content not just technology, so I will limit this section to just some highlights.</p>

<p>In September 2004, ESPN announced that HD ESPN 2 would be launched on Jan 05 with 100 live HD telecasts the first year.</p>

<p>In November 2004, NBC Universal Cable renames Bravo HD+ to "Universal HD" and will offer hundreds of HD content from the NBC Universal Library starting December 1, 2004 to a total of 25 million subscribers of DIRECTV, Cablevision, Cox, Insight, Mediacom, Voom, etc. </p>

<p>In December 2004, DirecTV announced their agreement with Fox Television Station Group to carry FOX H/DTV channels in 26 market areas of eligible viewers (where the network has am owned and operated affiliate, 46% of US viewers), including New York, Los Angeles, Chicago, Philadelphia, Boston, Dallas-Fort Worth, Washington, D.C., Atlanta, Detroit, Houston, Tampa, Fla., Minneapolis, Cleveland, Phoenix, Denver, Orlando, Fla., St. Louis, Baltimore, Milwaukee, Kansas City, Mo., Salt Lake City, Birmingham, Ala., Memphis, Tenn. Greensboro, N.C., Austin, Texas and Gainesville, Fla.</p>

<p>Pacific and Mountain subscribers will receive LA 's KTTV, Central and Eastern subscribers will receive New York's WNYW.  The service would be free for subscribers of local channels package that live within the market areas above; they need to have HD IRDs. </p>

<p>In January 2005, DirecTV added ABC's network HD feed to their service in 10 markets including Chicago, Flint, Michigan, Fresno, Houston, Los Angeles, New York, Philadelphia, Raleigh, San Francisco, and Toledo.  The markets have Local stations that are owned and operated by ABC.  East coast subscribers will se the New York feed, West coast subscribers will see the Los Angeles feed.  With the addition of FOX and ABC, DirecTV has now all the four networks in HDTV (it already had CBS and NBC), although not all are yet available to certain markets.</p>

<p>In May 2004, DIRECTV stated that it currently offers the national CBS HD feed for customers living in CBS O&O markets, and soon the company expected to add the national NBC HD feed to customers living in NBC O&O markets.</p>

<p>In May 2004, Dish Network added TNT HD East coast feed in channel 9420, to include sports, movies, TNT originals, and shows in 16:9 5.1 DD.  Dish Network would then offer five channels within its $10 monthly package (TNT HD, HDNet, HDNet movies, and Discovery HD).</p>

<p>In May 2004, Voom, in addition to the HD channels, 2 HD Showtime, 2 HD HBO, 2 Cinemax HD, 1 Discovery, 2 Starz HD, NFL HD part-time, The Movie Channel HD, etc., announced their plans to add Encore HD, Bravo HD, NBA HD, TNT HD, ESPN HD, Playboy HD, and HD Preview channel.  HDNet and INHD will not be included as planned. </p>

<p>Time Warner introduced TNT HD to its Charlotte's NC subscribers starting on May 2004; East coast feed 24/7, in channel 281. </p>

<p>In May 2004, the Outdoor Channel HD announced that it will create new HD content to sell to others like INHD, in 2005 will launch as a 24/7 HD channel.</p>

<p>Be sure that you read the next article in this series: <a href="http://www.hdtvmagazine.com/articles/2005/10/2005_hdtv_repor_1.php">Market Penetration of H/DTV</a></p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>September 30, 2005  8:56 AM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(210)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 210)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2005/09/2005-hdtv-report-part-2-hdtv-implementation-update.php" type="text/javascript" charset="utf-8"></script>
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
