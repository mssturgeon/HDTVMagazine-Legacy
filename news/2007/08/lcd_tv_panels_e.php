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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 658 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 658
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=LCD TV Panels Enjoyed Record Quarter in Q2\'07, Doubled Up on PDPs at 40"+ as Plasma Sales Remained Weak&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/news/2007/08/lcd-tv-panels-enjoyed-record-quarter-in-q207-doubled-up-on-pdps-at-40-as-plasma-sales-remained-weak.php&amp;title=LCD TV Panels Enjoyed Record Quarter in Q2'07, Doubled Up on PDPs at 40&quot;+ as Plasma Sales Remained Weak">
		<span style="display:none">AUSTIN, TEXAS, July 31, 2007-DisplaySearch, the worldwide leader in flat panel display market research and consulting, reported in its latest Quarterly PDP Module and TV Shipment and Forecast ReportandWeekly TV Flash Report that plasma and LCD TV panel sales were headed in opposite directions in Q2'07.

LCD TV panel shipments enjoyed a record quarter in Q2'07, rising 32% Q/Q and 65% Y/Y to 19.6M panels.  The Q2'07 results exceeded suppliers' expectations and DisplaySearch's forecast by over 700K panels or 4%. Revenues grew 28% Q/Q and 39% Y/Y to a record $7.2B.  ASPs were only down 2% Q/Q and 16% Y/Y to $369. For the first time, twice as many 40&quot;+ LCD TV panels were shipped as 40&quot;+ plasma panels, as shown in Figure 1. The LCD TV share of the 40&quot;+ flat panel market rose from 42% in Q2'06 to 68% in Q2'07. This can be attributed to...</span></a>
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

	switch (7) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 658";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download LCD TV Panels Enjoyed Record Quarter in Q2\'07, Doubled Up on PDPs at 40"+ as Plasma Sales Remained Weak" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="LCD TV Panels Enjoyed Record Quarter in Q2\'07, Doubled Up on PDPs at 40"+ as Plasma Sales Remained Weak" />
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
	<title>HDTV Magazine - LCD TV Panels Enjoyed Record Quarter in Q2'07, Doubled Up on PDPs at 40"+ as Plasma Sales Remained Weak</title>
	<meta name="keywords" content="panel shipments, plasma panel, samsung sdi, record quarter, plasma sales, panel, panels, plasma, lcd, share, shipments, remained, increase, samsung, pdp, displaysearch, brands, growth, matsushita, enjoyed, sdi, record, suppliers, rising, rose" />
	<meta name="description" content="AUSTIN, TEXAS, July 31, 2007-DisplaySearch, the worldwide leader in flat panel display market research and consulting, reported in its latest Quarterly PDP Module and TV Shipment and Forecast ReportandWeekly TV Flash Report that plasma and LCD TV panel sales were headed in opposite directions in Q2'07.

LCD TV panel shipments enjoyed a record quarter in Q2'07, rising 32% Q/Q and 65% Y/Y to 19.6M panels.  The Q2'07 results exceeded suppliers' expectations and DisplaySearch's forecast by over 700K panels or 4%. Revenues grew 28% Q/Q and 39% Y/Y to a record $7.2B.  ASPs were only down 2% Q/Q and 16% Y/Y to $369. For the first time, twice as many 40&quot;+ LCD TV panels were shipped as 40&quot;+ plasma panels, as shown in Figure 1. The LCD TV share of the 40&quot;+ flat panel market rose from 42% in Q2'06 to 68% in Q2'07. This can be attributed to..." />
	<meta name="title" content="LCD TV Panels Enjoyed Record Quarter in Q2'07, Doubled Up on PDPs at 40&quot;+ as Plasma Sales Remained Weak" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />

	<script type="text/javascript">
		// Digg Script
		(function() {
			var s = document.createElement('SCRIPT'), s1 = document.getElementsByTagName('SCRIPT')[0];
			s.type = 'text/javascript';
			s.src = 'http://widgets.digg.com/buttons.js';
			s1.parentNode.insertBefore(s, s1);
		})();

		function init() {
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/news/2007/08/lcd-tv-panels-enjoyed-record-quarter-in-q207-doubled-up-on-pdps-at-40-as-plasma-sales-remained-weak.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=658', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2007/08/lcd-tv-panels-enjoyed-record-quarter-in-q207-doubled-up-on-pdps-at-40-as-plasma-sales-remained-weak.php">LCD TV Panels Enjoyed Record Quarter in Q2'07, Doubled Up on PDPs at 40"+ as Plasma Sales Remained Weak</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Dale Cripps</b> on <b>August  1, 2007</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=262&category=Business & Investment">Business & Investment</a></b>
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
				<p><html xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns="http://www.w3.org/TR/REC-html40"></p>

<p><head><br />
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252"><br />
<link rel="File-List" href="new_page_1_files/filelist.xml"><br />
<title>New Page 1</title><br />
<style><br />
<!--<br />
p<br />
	{margin-right:0in;<br />
	margin-left:0in;<br />
	font-size:12.0pt;<br />
	font-family:"Times New Roman";<br />
	}<br />
span.title<br />
	{}<br />
 table.MsoNormalTable<br />
	{mso-style-parent:"";<br />
	font-size:10.0pt;<br />
	font-family:"Times New Roman";<br />
	}<br />
 p.MsoNormal<br />
	{mso-style-parent:"";<br />
	margin-bottom:.0001pt;<br />
	font-size:12.0pt;<br />
	font-family:"Times New Roman";<br />
	margin-left:0in; margin-right:0in; margin-top:0in}<br />
--><br />
</style><br />
<!--[if !mso]><br />
<style><br />
v\:*         { behavior: url(#default#VML) }<br />
o\:*         { behavior: url(#default#VML) }<br />
.shape       { behavior: url(#default#VML) }<br />
</style><br />
<![endif]--><!--[if gte mso 9]><br />
<xml><o:shapedefaults v:ext="edit" spidmax="1027"/><br />
</xml><![endif]--><br />
</head></p>

<p><body></p>

<p><span class="title">DisplaySearch Reports LCD TV Panels Enjoyed Record 
Quarter in Q2'07, Doubled Up on PDPs at 40&quot;+ as Plasma Sales Remained Weak
</span></p>
<p>AUSTIN, TEXAS, July 31, 2007-DisplaySearch, the worldwide leader in flat 
panel display market research and consulting, reported in its latest <em><b>
<a target="_blank" title="http://now.eloqua.com/e/er.aspx?s=488&amp;lid=54&amp;elq=E3DF1255F42445E5B55F15E2022183B9" style="color: blue; text-decoration: underline; text-underline: single" href="http://now.eloqua.com/e/er.aspx?s=488&lid=54&elq=E3DF1255F42445E5B55F15E2022183B9">
Quarterly PDP Module and TV Shipment and Forecast Report</a></b></em>and<em><b><a target="_blank" title="http://now.eloqua.com/e/er.aspx?s=488&amp;lid=117&amp;elq=E3DF1255F42445E5B55F15E2022183B9" style="color: blue; text-decoration: underline; text-underline: single" href="http://now.eloqua.com/e/er.aspx?s=488&lid=117&elq=E3DF1255F42445E5B55F15E2022183B9">Weekly 
TV Flash Report</a>&nbsp;</b></em>that plasma and LCD TV panel sales were headed in 
opposite directions in Q2'07. </p>
<p>LCD TV panel shipments enjoyed a record quarter in Q2'07, rising 32% Q/Q and 
65% Y/Y to 19.6M panels. &nbsp;The Q2'07 results exceeded suppliers' expectations and 
DisplaySearch's forecast by over 700K panels or 4%. Revenues grew 28% Q/Q and 
39% Y/Y to a record $7.2B.&nbsp; ASPs were only down 2% Q/Q and 16% Y/Y to $369. For 
the first time, twice as many 40&quot;+ LCD TV panels were shipped as 40&quot;+ plasma 
panels, as shown in Figure 1. The LCD TV share of the 40&quot;+ flat panel market 
rose from 42% in Q2'06 to 68% in Q2'07. This can be attributed to the amount of 
optimized Gen 7 and larger LCD fab capacity brought online, to the success of 
LCD TV brands and retailers in getting consumers excited over 1080p TVs where 
LCDs have dominated PDPs and to the greater number of LCD TV brands overwhelming 
plasma TV brands in retail. </p>
<p><strong>Figure 1: 40&quot;+ LCD TV and Plasma TV Panel Shipment Results</strong>
</p>
<p><!--[if gte vml 1]><v:shapetype id="_x0000_t75"
 coordsize="21600,21600" o:spt="75" o:preferrelative="t" path="m@4@5l@4@11@9@11@9@5xe"
 filled="f" stroked="f">
 <v:stroke joinstyle="miter"/>
 <v:formulas>
  <v:f eqn="if lineDrawn pixelLineWidth 0"/>
  <v:f eqn="sum @0 1 0"/>
  <v:f eqn="sum 0 0 @1"/>
  <v:f eqn="prod @2 1 2"/>
  <v:f eqn="prod @3 21600 pixelWidth"/>
  <v:f eqn="prod @3 21600 pixelHeight"/>
  <v:f eqn="sum @0 0 1"/>
  <v:f eqn="prod @6 1 2"/>
  <v:f eqn="prod @7 21600 pixelWidth"/>
  <v:f eqn="sum @8 21600 0"/>
  <v:f eqn="prod @7 21600 pixelHeight"/>
  <v:f eqn="sum @10 21600 0"/>
 </v:formulas>
 <v:path o:extrusionok="f" gradientshapeok="t" o:connecttype="rect"/>
 <o:lock v:ext="edit" aspectratio="t"/>
</v:shapetype><v:shape id="_x0000_s1025" type="#_x0000_t75" alt="" style='width:469.5pt;
 height:269.25pt'>
 <v:imagedata src="new_page_12_files/image001.jpg" o:href="http://img.en25.com/eloquaimages/clients/DisplaySearch/%7bd857f157-a33a-4f49-b3bb-327cdd13b83d%7d_lcdpdppanelshipment.jpg"/>
</v:shape><![endif]--><![if !vml]><img border=0 width=626 height=359
src="new_page_12_files/image001.jpg" v:shapes="_x0000_s1025"><![endif]></p>
<p>Of the total LCD TV panel shipments, the 40&quot;+ share rose from 22% in Q1'07 to 
23% in Q2'07 and the 1080p share of all LCD TV panel shipments rose from 10% to 
11%. Not all larger sized categories increased share in Q2'07; the 45-47&quot; 
category fell from 5.0% to 4.7% as prices remained too high for most consumers. 
32&quot; remained the dominant size category, earning a 38% share with OEMs and 
brands likely buying more 32&quot; panels than they needed as 32&quot; panel prices have 
been rising. LPL remained #1 on a unit basis followed by AUO and Samsung. On a 
revenue basis, Samsung remained #1 followed by LPL and AUO. </p>
<p>Plasma panel shipments were flat Q/Q and down 4% Y/Y to 2.3M panels. Year to 
date, plasma panel shipments are down 3% despite a 51% increase in capacity. 
Plasma panel manufacturers were expecting an increase of 18% to 2.7M panels, so 
clearly Q2'07 was disappointing. With plasma panel prices falling rapidly, 
plasma panel revenues were down 13% Q/Q and 37% Y/Y to $1.2B.&nbsp; ASPs were down 
13% Q/Q and 34% Y/Y to $507. </p>
<p>Despite the overall PDP decline, there was growth at 50&quot;+ and rapid growth in 
1080p panels. The 50&quot;+ share of PDP panel shipments continued to increase, 
rising from 29% in Q1'07 to 31% in Q2'07 on an 8% Q/Q increase and is expected 
to reach a 34% share in Q3'07 as PDP suppliers continue to shift their focus to 
larger sizes due to 42&quot; share losses to LCDs.&nbsp; There was also a dramatic 
increase in 1080p panel shipments, up 545% to 169K panels with 1080p penetration 
rising from 1.1% to 7.3%.&nbsp; The increase in 1080p shipments and improved supply 
should improve their competitive position against LCDs. </p>
<p>Matsushita remained #1 with Samsung SDI overtaking LGE for #2 as shown in 
Table 1. Of the five major suppliers, only Matsushita and Samsung SDI enjoyed 
Y/Y growth. By size, Matsushita led at 37&quot;, 42&quot;, 55-59&quot; and 60&quot;+ while Samsung 
SDI led at 50&quot;. 1080p panels accounted for an impressive 17% of Matsushita's 
mix, up from just 2% in Q1'07. </p>
<p><strong>Table 1:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Plasma Panel Share and Growth by Supplier </strong>
</p>
<div align="center">
	<table class="MsoNormalTable" border="1" cellspacing="0" cellpadding="0" width="100%" style="width: 100.0%" id="table1">
		<tr>
			<td style="padding: 0in; background: #333333">
			<p align="center" style="text-align:center"><strong><i>
			<span style="color:white">Supplier</span></i></strong><i><span style="color:white">
			</span></i></td>
			<td nowrap style="padding: 0in; background: #333333">
			<p align="center" style="text-align:center"><strong><i>
			<span style="color:white">Q1'07 Share </span></i></strong></td>
			<td nowrap style="padding: 0in; background: #333333">
			<p align="center" style="text-align:center"><strong><i>
			<span style="color:white">Q2'07 Share </span></i></strong></td>
			<td nowrap style="padding: 0in; background: #333333">
			<p align="center" style="text-align:center"><strong><i>
			<span style="color:white">Y/Y Growth </span></i></strong></td>
		</tr>
		<tr>
			<td nowrap valign="bottom" style="padding: 0in; background: white">
			<p align="center" style="text-align:center">Matsushita</td>
			<td nowrap valign="bottom" style="padding: 0in; background: white">
			<p align="center" style="text-align:center">31.3%</td>
			<td nowrap valign="bottom" style="padding: 0in; background: white">
			<p align="center" style="text-align:center">36.5%</td>
			<td nowrap valign="bottom" style="padding: 0in; background: white">
			<p align="center" style="text-align:center">14%</td>
		</tr>
		<tr>
			<td nowrap valign="bottom" style="padding: 0in; background: #999999">
			<p align="center" style="text-align:center">Samsung SDI</td>
			<td nowrap valign="bottom" style="padding: 0in; background: #999999">
			<p align="center" style="text-align:center">23.9%</td>
			<td nowrap valign="bottom" style="padding: 0in; background: #999999">
			<p align="center" style="text-align:center">27.4%</td>
			<td nowrap valign="bottom" style="padding: 0in; background: #999999">
			<p align="center" style="text-align:center">13%</td>
		</tr>
		<tr>
			<td nowrap valign="bottom" style="padding: 0in; background: white">
			<p align="center" style="text-align:center">LGE</td>
			<td nowrap valign="bottom" style="padding: 0in; background: white">
			<p align="center" style="text-align:center">27.1%</td>
			<td nowrap valign="bottom" style="padding: 0in; background: white">
			<p align="center" style="text-align:center">23.5%</td>
			<td nowrap valign="bottom" style="padding: 0in; background: white">
			<p align="center" style="text-align:center">-25%</td>
		</tr>
		<tr>
			<td nowrap valign="bottom" style="padding: 0in; background: #999999">
			<p align="center" style="text-align:center">Hitachi </td>
			<td nowrap valign="bottom" style="padding: 0in; background: #999999">
			<p align="center" style="text-align:center">10.4%</td>
			<td nowrap valign="bottom" style="padding: 0in; background: #999999">
			<p align="center" style="text-align:center">8.5%</td>
			<td nowrap valign="bottom" style="padding: 0in; background: #999999">
			<p align="center" style="text-align:center">-22%</td>
		</tr>
		<tr>
			<td nowrap valign="bottom" style="padding: 0in; background: white">
			<p align="center" style="text-align:center">Pioneer</td>
			<td nowrap valign="bottom" style="padding: 0in; background: white">
			<p align="center" style="text-align:center">7.2%</td>
			<td nowrap valign="bottom" style="padding: 0in; background: white">
			<p align="center" style="text-align:center">3.9%</td>
			<td nowrap valign="bottom" style="padding: 0in; background: white">
			<p align="center" style="text-align:center">-30%</td>
		</tr>
		<tr>
			<td nowrap valign="bottom" style="padding: 0in; background: #999999">
			<p align="center" style="text-align:center">Orion</td>
			<td nowrap valign="bottom" style="padding: 0in; background: #999999">
			<p align="center" style="text-align:center">0.1%</td>
			<td nowrap valign="bottom" style="padding: 0in; background: #999999">
			<p align="center" style="text-align:center">0.2%</td>
			<td nowrap valign="bottom" style="padding: 0in; background: #999999">
			<p align="center" style="text-align:center">20%</td>
		</tr>
		<tr>
			<td nowrap valign="bottom" style="padding: 0in; background: white">
			<p align="center" style="text-align:center">Total</td>
			<td nowrap valign="bottom" style="padding: 0in; background: white">
			<p align="center" style="text-align:center">100.0%</td>
			<td nowrap valign="bottom" style="padding: 0in; background: white">
			<p align="center" style="text-align:center">100.0%</td>
			<td nowrap valign="bottom" style="padding: 0in; background: white">
			<p align="center" style="text-align:center">-4%</td>
		</tr>
	</table>
</div>
<p>Looking forward, PDP suppliers are expecting over a 30% Q/Q increase to over 
3M panels in Q3'07. In order for this to happen, we would expect to see PDP TV 
brands make significant price moves within the next 60 days. </p>

<p><br />
DisplaySearch, an NPD Group company, has a core team of 46 employees located in <br />
Europe, North America and Asia who produce a valued suite of FPD-related market <br />
forecasts, technology assessments, surveys, studies and analyses. The company <br />
also organizes influential events worldwide. Headquartered in Austin, Texas, <br />
DisplaySearch has regional operations in Chicago, Houston, Kyoto, London, San <br />
Diego, San Jose, Seoul, Shenzhen, Taipei and Tokyo, and the company is on the <br />
web at<br />
<a title="http://now.eloqua.com/e/er.aspx?s=488&amp;lid=23&amp;elq=E3DF1255F42445E5B55F15E2022183B9" style="color: blue; text-decoration: underline; text-underline: single" href="http://now.eloqua.com/e/er.aspx?s=488&lid=23&elq=E3DF1255F42445E5B55F15E2022183B9"><br />
www.displaysearch.com</a>. </p><br />
 </p>

<p></body></p>

<p></html></p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>August  1, 2007  7:11 AM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(658)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 658)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2007/08/lcd-tv-panels-enjoyed-record-quarter-in-q207-doubled-up-on-pdps-at-40-as-plasma-sales-remained-weak.php" type="text/javascript" charset="utf-8"></script>
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
