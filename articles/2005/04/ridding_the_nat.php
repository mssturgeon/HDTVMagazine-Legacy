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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 12 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 12
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=Ridding the Nation of DTV Fables&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/articles/2005/04/ridding-the-nation-of-dtv-fables.php&amp;title=Ridding the Nation of DTV Fables">
		<span style="display:none">Under any scenario conceivable there will not be a successful termination of analog services as long as there are any with a dependancy upon those signals for local news (or even entertainment). If someone is deluded enough to insist that it does happen I want the pitchfork concession Washington. Nothing riles up the public more than the loss of their TV services. The industry is repleat with stories of outages where the wrath of god decended upon the service provider until things were restored. One cable company had been testing a new channel in preparation for placing one of the music services on it. To test the video an engineer pointed a camera on a fish tank and sent the signal down that newly created channel to the subscribers. The images of fish swiming around on your television set went on for several weeks. The day the music channel replaced the fish tank caused a meltdown of the cable company's customer service department as outraged viewers demanded that they get their fish back. The 911 system goes into overload everytime a cable system breaks down. You don't mess with what people have become familiar without careful preparation, which at minimum requires a complete education of the viewer.
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 12";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Ridding the Nation of DTV Fables" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Ridding the Nation of DTV Fables" />
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
	<title>HDTV Magazine - Ridding the Nation of DTV Fables</title>
	<meta name="keywords" content="nab mstv, dtv transition, percent requirement, house representativeswashington, consumer electronics, dtv, local, television, public, sets, percent, analog, nab, broadcasters, transition, set, cea, mstv, consumers, spectrum, air, free, congress, consumer, cable" />
	<meta name="description" content="Under any scenario conceivable there will not be a successful termination of analog services as long as there are any with a dependancy upon those signals for local news (or even entertainment). If someone is deluded enough to insist that it does happen I want the pitchfork concession Washington. Nothing riles up the public more than the loss of their TV services. The industry is repleat with stories of outages where the wrath of god decended upon the service provider until things were restored. One cable company had been testing a new channel in preparation for placing one of the music services on it. To test the video an engineer pointed a camera on a fish tank and sent the signal down that newly created channel to the subscribers. The images of fish swiming around on your television set went on for several weeks. The day the music channel replaced the fish tank caused a meltdown of the cable company's customer service department as outraged viewers demanded that they get their fish back. The 911 system goes into overload everytime a cable system breaks down. You don't mess with what people have become familiar without careful preparation, which at minimum requires a complete education of the viewer.
" />
	<meta name="title" content="Ridding the Nation of DTV Fables" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/articles/2005/04/ridding-the-nation-of-dtv-fables.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=12', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2005/04/ridding-the-nation-of-dtv-fables.php">Ridding the Nation of DTV Fables</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Dale Cripps</b> on <b>April 27, 2005</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=4&category=Politics & Policy">Politics & Policy</a></b>
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
				<p>The following letter from the National Association of Broadcasters' CEO, <a href="http://www.hdtvmagazine.com/articles/articles-author.php?id=5">Eddie Fritts</a> is in response to the High-Tech DTV Coalition seeking to hurry a "date certain" for the shut off of the analog TV channels. The government along with the powerful voices in industry are eager to get those channels back for both new business applications and for the money they will bring to the general fund of the U.S. Government. Regardless of what is said or what rules and legislation is written and passed, the determination of when the spectrum can be be returned is entirely "public certain", i.e., established by the public through their actions. Have they, the public, done their part in this transition? Do they understand what role it is they are to play, and why? If they have not done their part, will they sit still for having their favorite TV public services discontinued because of a distant lust over the new use and revenue from the analog spectrum?</p>

<p>Under any scenario conceivable there will not be a successful termination of analog services as long as there are any with a dependancy upon those signals for local news (or even entertainment). If someone is deluded enough to insist that it does happen I want the pitchfork concession Washington. Nothing riles up the public more than the loss of their TV services. The industry is repleat with stories of outages where the wrath of god decended upon the service provider until things were restored. One cable company had been testing a new channel in preparation for placing one of the music services on it. To test the video an engineer pointed a camera on a fish tank and sent the signal down that newly created channel to the subscribers. The images of fish swiming around on your television set went on for several weeks. The day the music channel replaced the fish tank caused a meltdown of the cable company's customer service department as outraged viewers demanded that they get their fish back. The 911 system goes into overload everytime a cable system breaks down. You don't mess with what people have become familiar without careful preparation, which at minimum requires a complete education of the viewer.</p>

<p>One could foresee the day and hour when some experimentation of the shut off takes place. For the sake of example lets say that Redding, California with their 6 analog channels gets the order that on December 31, the day before the Rose Bowl, all channels are shut down. Whatever reaction that comes from that market will be a legend which all others will see in their mind's eye from that moment on. While the law may force the channel dark the last telephone number on the analog screen will be that of the Congressman of the district and the Senators of the state.<br />
To do my part in helping this transition become the success it can I have invited both <a href="http://www.hdtvmagazine.com/articles/articles-author.php?id=5">Eddie Fritts</a>, CEO of the National Association of Broadcasters, and Gary Shapiro, CEO of the Consumer Electronics Association, to produce a BLOG for this web site. They are the first of many who have been or will be extended the same invitation<br />
<strong></strong><br />
<strong>The purpose of the invitation is three fold:</strong><br />
<strong></strong><br />
<strong>1. The arguments for the issues need to be articaulated clearly in full public view. </strong><br />
<span style="color:#666666;"><em><strong>(No remaining issue in the DTV transition is free of public impact and the leaders now must address an informed public in a candid manner.)</strong><br />
<strong></strong></em><br />
</span><strong>2. The resultant attention from both press and television to this web site will cause greater public participation and thus a greater promotion for H/DTV is made possible <span style="color:#666666;"><em>(With the clarifications given in these BLOGs an accelleration of the DTV transition can be expected.)</em></span></strong><span style="color:#666666;"><em><br />
<strong></strong></em></span><br />
<strong>3. The presence of these two national leaders will encourage leaders from other industries to participate with their own BLOGSs with the aim to rid the nation of DTV myths and fables. </strong><br />
<strong></strong><br />
<strong></strong><br />
The following is a letter from <a href="http://www.hdtvmagazine.com/articles/articles-author.php?id=5">Eddie Fritts</a>, president of the National Association of Broadcasters in Washinton, D.C. to the Congressman most responsible for crafting legislation for this thorny issue. In coming days I will present all of the influences reaching Congress on this matter.</p>

<p>Here is that letter...</p>

<p>April 27, 2005</p>

<p><strong>The Honorable Joe BartonChairmanHouse Committee on Energy and CommerceU.S. House of RepresentativesWashington, DC 20515</strong></p>

<p>The Honorable John DingellRanking MemberHouse Committee on Energy and CommerceU.S. House of RepresentativesWashington, DC 20515</p>

<p>The Honorable Fred UptonChairmanHouse Subcommittee on Telecommunications &the InternetU.S. House of RepresentativesWashington, DC 20515</p>

<p>The Honorable Ed MarkeyRanking MemberHouse Subcommittee on Telecommunications &amp;the InternetU.S. House of RepresentativesWashington, DC 20515</p>

<p>Dear Congressmen:</p>

<p>The Computer Systems Policy Project (CSPP) has written to ask that you pass legislation aimed at "completing the DTV transition as soon as possible." Local broadcasters are strongly supportive of efforts to bring this transition to a timely conclusion, and NAB stands ready to work with this Committee to accomplish that goal.</p>

<p>However, we also agree with the many members of Congress who have expressed concern that a premature end to analog television would be terribly disruptive to millions of Americans. Our viewers are your constituents, and we believe that an overriding priority in ending this transition must be the protection of consumers against losing access to local television.</p>

<p>To date, broadcasters have invested billions of dollars and risked the most to complete the DTV transition. According to the FCC, there are now 1,497 local stations on-air in digital operating in all 211 television markets. In addition, 87.54 percent of the more than 106 million U.S. TV households are in markets with five or more broadcasters airing DTV; another 69.23 percent of all homes are in markets with eight or more broadcasters sending digital signals. Moreover, the amount of high definition television offered by broadcast networks and local TV stations has soared. Clearly, local broadcasters have upheld our commitment to make digital television a reality.</p>

<p>As these hundreds of local broadcasters are transmitting in both analog and digital signals, they are paying dual operating costs without any additional revenue source. Clearly, we have every incentive to see the transition ended and the analog spectrum freed for other uses.</p>

<p>However, as a matter of public policy, the corporate financial interests of a handful of technology companies should not trump the needs of American television viewers. Make no mistake: a premature end to analog television could leave millions of Americans without access to free local TV station signals. The harm to these consumers -- a disproportionate number of whom come from poor and minority households -- must be considered against the purely parochial interests of high-tech companies hoping to profit from new uses of this spectrum. Today, 73 million television sets are in use in households that rely on free, over-the-air broadcasting as their only source for TV reception. Moreover, a recent study by the GAO found that 20.5 million TV households rely exclusively on over-the-air TV reception. The study also found that 28 percent of Hispanic households rely solely on over-the-air television, and that one-half of households where the head of the home is over 50 years of age and the annual income is less than thirty thousand dollars are over-the-air reliant. It is critically important that these Americans -- and those that may have second and third over-the-air TV sets in homes wired for cable and satellite -- not be disenfranchised from access to local television.</p>

<p>CSPP wrongly asserts that local stations' occupation of TV spectrum band is hindering the rollout of public safety communications interoperability. The fact of the matter is that in the ten cities most likely to be struck by a terrorist attack, the communications interoperability issue has been resolved. In September 2004, USA Today reported that then-Homeland Security Secretary Tom Ridge announced that in 10 of the cities considered at highest risk for a terrorist attack, firefighters, police and other emergency responders in charge during a disaster can now talk to each other to coordinate a quick response. (<a href="http://www.nab.org/xert/corpcomm/092704USAToday.asp">See attached article</a>). While expansion of public safety communications interoperability remains an important policy goal, CSPP appears to be overstating the problem for its own ulterior motives. It goes without saying that local broadcasting remains a primary "first responder" during times of crisis. Citizens know that local TV stations provide lifeline information during emergency weather situations, Amber Alerts, terrorist attacks, and other disasters. Local television stations also provide valuable services during good times, offering news and public affairs programming that citizens rely upon to be connected to their communities. We cover the local sports that communities rally around. Our partnerships with charities raise billions of dollars for non-profits that improve and strengthen communities. In short, local broadcasting has always been integral to the fabric of the American life.</p>

<p>As broadcasters, we are no strangers to technological innovation. The DTV transition represents a revolutionary milestone in broadcasting, and it will further enhance our ability to serve your constituents with compelling free local content.</p>

<p>In 1996, Congress and broadcasters entered into a public-private partnership aimed at bringing the next generation of free television to the viewing public. Congress, broadcasters and viewers are on the precipice of seeing this ambitious undertaking completed. As we near completion of this historic journey, we urge Congress to reject approaches that focus myopically upon clearance of spectrum to benefit the narrow interests of a small group of corporations. The overriding goal must be a seamless DTV transition that does NOT leave millions of Americans stranded from access to free TV.</p>

<p>NAB looks forward to working with the Committee as you fashion a solution that will end the transition, while ensuring that Americans can enjoy continued access to free local television.<br />
Sincerely,</p>

<p><br />
This article appeared in TV Technology...<br />
<strong></strong><br />
<strong><span style="font-size:130%;">NAB, MSTV Oppose DTV Tuner Mandate Delay</span><br />
</strong><br />
NAB and the Association for Maximum Service Television (MSTV) are urging the FCC to reject a proposal by the Consumer Electronics Association (CEA) to eliminate the requirement that 50 percent if all television sets shipped after July 1, 2005 have DTV tuners.<br />
NAB President/CEO <a href="http://www.hdtvmagazine.com/articles/articles-author.php?id=5">Eddie Fritts</a> accused the CEA of perpetuating fraud on the American consumer.</p>

<p>"CEA member companies continue to sell millions of analog TV sets every year, while refusing to tell consumers that these sets will soon be obsolete or need converters to work in the digital era," Fritts said.</p>

<p>"Every analog set sold to a consumer willing to purchase a new television set necessarily decreases the likelihood that a given market will soon reach the 85 percent statutory threshold. Such delay, aside from depriving consumers of the benefits of digital technology, will impede the return of analog spectrum allotted for future use by first responders and commercial wireless providers," according to NAB and MSTV.</p>

<p>Both groups also point to Congressional actions in their comments, noting, recent press reports, "House Commerce Committee Chairman Joe Barton has stated his intention to ask the Commission to accelerate the deadline for the final DTV tuner mandate (i.e., the date by which all sets sold that are 13 inches or greater in size must include a DTV tuner) to 2005 or early 2006. Against this backdrop, the Commission should not take any action that could delay consumers' acceptance of DTV technology."</p>

<p>NAB and MSTV included results of a study showing that in 54 percent of U.S. homes, the largest TV set is between the 25 and 35-inch screen size covered under the July 2005 50 percent rule.<br />
I presented CEA's side of argument in the <a href="http://www.tvtechnology.com/dlrf/issue.php?w=2005-02-22">Feb. 22, 2005 RF Report</a> . One of the arguments CEA made for delaying the 50 percent date and accelerating the 100 percent date from July 2006 to March 2006 was that this would cause retailers to over-order analog sets, creating a surplus of DTV sets. In their comments, "MSTV and NAB do not disagree that phased implementation of a given size of receiver may be inefficient from an enforcement standpoint. Nevertheless, the number of sets that become available to consumers while the 50 percent requirement is in effect would certainly be greater than if there were no mandate during that time. Some is better than none, and CEA-CERC should not be allowed to make the perfect the enemy of the good. Also, turning the 50 percent requirement into a 0 percent requirement is not the only, or even most logical, option should the Commission conclude that a phased approach to 25-35 inch sets is inefficient."</p>

<p>CEA recommends that Congress enact a 100 percent requirement effective July 2005 to avoid the inefficiencies of a phased approach and avoid harm to public interest.<br />
NAB and MSTV note that the rationale for the 50 percent requirement was to give manufacturers time to "develop efficiencies in production" of DTV sets and keep prices reasonable and it no longer applies. "The innovations of some manufacturers have achieved those efficiencies ahead of schedule; thus, it is unlikely that consumers will see an appreciable 'spike' in prices of 25-35 inch receivers if manufacturers are required to produce only DTV sets in that category by July 2005. For example, RCA has announced a 27-inch set, available this summer, which will sell for less than $300. In short, the economic thesis that underlies the CEA-CERC petition is simply denied by the reality of the new RCA sets." NAB and MSTV explain the consumers expect their TV sets to be able to pick up all broadcast signals.</p>

<p>NAB and MSTV complained that retailers, with support from CEA, have not consistently explained the importance of DTV tuner functionality to their customers. As a result, "Not surprisingly, consumer confusion has resulted. In most major electronic retail outlets throughout the country, it is next to impossible to find an in-store display of off-air DTV reception and capability. As recently as January, at the 2005 International Consumer Electronics Show in Las Vegas, CEA introduced a brochure called 'The 3 Simple Steps to HDTV.' Billed on its cover as a brochure that is designed to make it easy for you to learn the simple steps to get the full high definition experience in your home, the words 'broadcast', 'antenna' or 'over-the-air' do not even appear in this brochure, as if terrestrial broadcasting of HDTV programming did not exist. Step 2 of the brochure, titled 'Get the Programming,' brazenly states, '[c]all your local cable or satellite provider to order HDTV programming - the only way to get the full HD movie theater experience in your home."</p>

<p><br />
I urge you to take a look at the <a href="http://www.mstv.org/docs/DTV%20Tuner%20-%20Joint%20Comments%20MSTV_NAB%20[FINAL%20-%20PDF].PDF">Joint Comments of MSTV and NAB </a>.<br />
This is an important issue for broadcasters, especially with Congress considering shutting down analog TV broadcasting possible as soon as Dec. 31 next year. I'd be interested in readers' comments about their experiences purchasing set-top boxes or DTV sets for OTA reception at consumer electronics stores. Was it possible to buy a DTV set or set-top box without getting a sales pitch for a satellite HDTV service? At NAB I heard of a case where an electronics store refused to sell the customer an ATSC set-top box unless they also purchased a DBS HDTV package! Somewhat contradictory to this, I also heard Wal-Mart was prevented from selling the USDTV HDTV set-top boxes without the USDTV subscription package at a higher price than those purchased with the subscription. The USDTV subscription provides cable TV programming for a fee using broadcast DTV spectrum.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>April 27, 2005  1:25 PM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(12)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 12)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2005/04/ridding-the-nation-of-dtv-fables.php" type="text/javascript" charset="utf-8"></script>
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
