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
	$res_author = mQuery("SELECT title, channel, amazon_tracking_id, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Richard Fisher'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Richard Fisher" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 663 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 663
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=Panasonic DMP-BD10A Blu-ray and SD DVD player&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/reviews/2007/08/panasonic-dmpbd10a-bluray-and-sd-dvd-player.php&amp;title=Panasonic DMP-BD10A Blu-ray and SD DVD player">
		<span style="display:none">The format battle for HD disc is heating up this year as both HD DVD and Blu-ray push for entry level products under $500 with the goal being less than $200 by the end of the year. Panasonic just released the DMP-BD10A with an MSRP of $599 and one of our readers, Jack Wilson of &lt;a href=&quot;/cgi-bin/ntlinktrack.cgi?http://www.bajaccess.com/&quot;&gt;BAJ Access Security&lt;/a&gt;, offered up one for a quick review.

The purchase of this product adds a Blu-ray 5 disc starter pack of the following titles: Pirates of the Caribbean: The Curse of the Black Pearl, Pirates of the Caribbean: Dead Man's Chest, Transporter, Fantastic Four and Crash. Limited quantities of players were shipped with the starter pack and promotional blurbs on the outside of box. If not included, this promotion runs until 09/30/07 to be claimed via US mail. Please check the &lt;a href=&quot;http://www2.panasonic.com/webapp/wcs/stores/servlet/vRebateDetail?storeId=15001&amp;catalogId=13151&amp;collateralId=637165&quot;&gt;Panasonic site&lt;/a&gt; for details.</span></a>
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

	switch (8) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Reviews Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 663";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Panasonic DMP-BD10A Blu-ray and SD DVD player" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Panasonic DMP-BD10A Blu-ray and SD DVD player" />
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
	<title>HDTV Magazine - Panasonic DMP-BD10A Blu-ray and SD DVD player</title>
	<meta name="keywords" content="blu ray, analog component, component video, via hdmi, dolby digital, panasonic, hdmi, video, blu, ray, dvd, audio, analog, output, player, performance, digital, scaling, component, disc, response, well, via, does, bitstream" />
	<meta name="description" content="The format battle for HD disc is heating up this year as both HD DVD and Blu-ray push for entry level products under $500 with the goal being less than $200 by the end of the year. Panasonic just released the DMP-BD10A with an MSRP of $599 and one of our readers, Jack Wilson of &lt;a href=&quot;/cgi-bin/ntlinktrack.cgi?http://www.bajaccess.com/&quot;&gt;BAJ Access Security&lt;/a&gt;, offered up one for a quick review.

The purchase of this product adds a Blu-ray 5 disc starter pack of the following titles: Pirates of the Caribbean: The Curse of the Black Pearl, Pirates of the Caribbean: Dead Man's Chest, Transporter, Fantastic Four and Crash. Limited quantities of players were shipped with the starter pack and promotional blurbs on the outside of box. If not included, this promotion runs until 09/30/07 to be claimed via US mail. Please check the &lt;a href=&quot;http://www2.panasonic.com/webapp/wcs/stores/servlet/vRebateDetail?storeId=15001&amp;catalogId=13151&amp;collateralId=637165&quot;&gt;Panasonic site&lt;/a&gt; for details." />
	<meta name="title" content="Panasonic DMP-BD10A Blu-ray and SD DVD player" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Reviews Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />

	<script type="text/javascript">
		// Digg Script
		(function() {
			var s = document.createElement('SCRIPT'), s1 = document.getElementsByTagName('SCRIPT')[0];
			s.type = 'text/javascript';
			s.src = 'http://widgets.digg.com/buttons.js';
			s1.parentNode.insertBefore(s, s1);
		})();

		function init() {
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/reviews/2007/08/panasonic-dmpbd10a-bluray-and-sd-dvd-player.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=663', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/reviews/2007/08/panasonic-dmpbd10a-bluray-and-sd-dvd-player.php">Panasonic DMP-BD10A Blu-ray and SD DVD player</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Richard Fisher</b> on <b>August 15, 2007</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=281&category=Blu-ray Players">Blu-ray Players</a></b>
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
				<p><img src="/images/products/panasonic-dmp-bd10a.jpg" alt=" Panasonic DMP-BD10A (DMP-BD10AK)" /><br /></p>

<table class="greygrid">
<tr>
<td>&nbsp;</td>
<td class="greygrid"><b>MSRP</b></td>
<td class="greygrid"><b>Street</b></td>
<td class="greygrid"><b>Amazon.com</b></td>
</tr><tr>
<td class="greygrid"><b>Pricing at publication</b></td>
<td class="greygrid">$599.95</td>
<td class="greygrid"><a target="_blank" href="/equipment/model.php?man=Panasonic&model=DMPBD10AK">$599.00</a></td>
<td class="greygrid"><a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.amazon.com/gp/product/B000S6MD6K/104-1967493-1307929?ie=UTF8&tag=hdtvmagazine-20&linkCode=xm2&camp=1789&creativeASIN=B000S6MD6K">569.99</a></td></tr>
</table>
<br />
Serial #: KV7DA01168R<br />
Warranty: 1 year parts and labor<br />
<br />
<B>Summary: Good 1080p Blu-ray performance but not quite as good at upconverting SD DVD. Solid Blu-ray and SD DVD performance for legacy displays!</B><br />
<br />

<p>The format battle for HD disc is heating up this year as both HD DVD and Blu-ray push for entry level products under $500 with the goal being less than $200 by the end of the year. Panasonic just released the DMP-BD10A with an MSRP of $599 and one of our readers, Jack Wilson of <a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.bajaccess.com/">BAJ Access Security</a>, offered up one for a quick review.</p>

<p>The purchase of this product adds a Blu-ray 5 disc starter pack of the following titles: Pirates of the Caribbean: The Curse of the Black Pearl, Pirates of the Caribbean: Dead Man's Chest, Transporter, Fantastic Four and Crash. Limited quantities of players were shipped with the starter pack and promotional blurbs on the outside of box. If not included, this promotion runs until 09/30/07 to be claimed via US mail. Please check the <a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www2.panasonic.com/webapp/wcs/stores/servlet/vRebateDetail?storeId=15001&catalogId=13151&collateralId=637165">Panasonic site</a> for details.</p>

<p><br />
<B>Common Features</B></p>

<ul><li>Super-high-speed P4HD processing with 296KHz/14-bit Video D/A Converter for analog video</li><li>User adjustable video controls: Sharpness, Contrast, Brightness, Color Saturation, Gamma, 3D-NR, and Integrated DNR</li><li>HDMI supporting 1080p60 </li><li>Analog Component Video Output supporting 1080i for Blu-ray and 480p for SD DVD </li><li>Composite and s-video output </li><li>7.1 multichannel analog audio output with calibration settings</li><li>High-resolution digital audio HDMI 1.3 output supporting all sound track codecs via bitstream only</li></ul>

<p><br />
<B>Not-So-Common Features</B></p>

<ul><li>HDMI RGB output range, normal and enhanced for DVI inputs - not tested<li>Coax or optical digital out for SD DVD soundtracks </li><li>50GB disc Storage </li><li>EZ Sync HDAVI Control - operate all of your Panasonic only home theater components by pressing a single button on your TV's remote control - not tested </li></ul>

<p><br />
<B>Opening the Box</B></p>

<p>The Panasonic is well packed coming in a larger cabinet typical of this price point similar to the recently reviewed LG dual format player. The front panel sports a door across the entire face and must be opened and closed for access to put your disc in the tray. While the door creates nice clean lines for the front panel I found it more of an inconvenience than enhancement. The black remote was quite nice and ergonomic in the hand. Main buttons you would access are readily available and when you open the top half underneath is a numerical keypad along with rarely accessed features related to setup and video settings.</p>

<p><br />
<B>Out of Box Performance</B></p>

<p>Hooking up the player to a BenQ W10000 I found it preset for 16:9 1080p and ran the DVE test material. Looking over at the receiver it showed the incoming codec so I entered the setup menu to make adjustments finding to my surprise individual settings for each codec the player supports. I set the SD codecs for bitstream and the HD codecs for PCM. After a quick look at a few Blu-ray titles I noticed a subtle artifact that appeared as a grainy, dithering or contouring of elements in particular scenes. Let's now move on to objective testing.</p>

<p><br />
<B>On the Test Bench </B></p>

<p>At this time there is no commercially available Blu-ray calibration or test disc, so that portion of testing is subjective only. What follows is objective testing for SD DVD content via HDMI at 720p, 1080i and 1080p along with component analog video at 480p only.</p>

<p>The very ability to inspect and view an HDMI video source goes directly against the copyright capability of the connection and copy protection since the means to see it would infer a means to steal it. At this time the recently reviewed <a href="/reviews/2007/05/panasonic_pt-ae1000u_lcd_front_projector.php">Panasonic PTAE-1000U</a> has been kept in the stable just for this purpose using the Wave Form Monitor feature. While the Wave Form Monitor does suffer when looking at high frequency response video such as bursts it is also the perfect tool for check IRE levels and color decoding. This does come with the limitation of only being able to check YPbPr output making me unable to verify the switching to RGB output that would be required for a DVI input. Some of the results are based on visual calibration checks as well as signal and is noted. All tests were performed using Digital Video Essentials as the source material.</p>

<p><br />
<B>Video Levels</B></p>

<p>Whether by visual calibration or waveform monitoring, the Panasonic outputs 0IRE and 100IRE at the correct 16/235 levels for HDMI. Analog component video also passed at 480p.</p>

<p><br />
<B>Color Decoding</B></p>

<p>Whether by visual calibration or waveform monitoring, the Panasonic outputs correct color decoding at 720p and 1080i/p. Analog component video also passed at 480p.</p>

<p><br />
Via HDMI the Panasonic had a higher level of scaling artifacts then normally observed for color bar patterns where two colors would meet. While having artifacts in this area of response is common the Panasonic, with an 18 pixel error in the horizontal, was clearly worse than a Sony PS3 with a 12 pixel error and the <a href="reviews/2007/06/oppo_dv-981hd_upconverting_sd_dvd_player.php">Oppo DV981HD</a> while not flawless was clearly better than the Sony for the same 12 pixels by generating the best scaling gradation between colors. Vertically the main area of error was about 4 pixels but there were visible artifacts in the same 18 pixel range. From the viewing position the Panasonic showed obvious scaling artifacts for this test with the Sony doing better and the Oppo providing the perceptive "Gotcha" because its scaling made the horizontal transition appear about the same size as the vertical at the viewing position of 3 screen heights.</p>

<p>Analog component vide connections passed at 480p.</p>

<p><br />
<B>Horizontal Frequency Response Luminance</B></p>

<p>As noted, waveform monitoring response was useless for this test. Visually the Panasonic passed the continuous frequency burst test quite well for luminance. For the low frequency pattern there is some banding for the highest frequency burst. Moving on to the high frequency pattern, recall that I have yet to see any player or scaler/player combo pass this pattern correctly and the Panasonic is no exception. This pattern always has banding so the best I can state on this is high, medium or a low contrast response with high being the best and low being the worst. For the Panasonic a medium contrast response was the norm; a typical response compared to others.</p>

<p>Analog component video had a similar response at 480p.</p>

<p><br />
<B>Vertical Frequency Response Luminance</B></p>

<p>Vertical frequency response was excellent in 1080p. With 720p the player could not figure out which dark and white stripes it should favor with white being predominant in the top burst and black predominant in the bottom burst.</p>

<p>Analog component video had a great response at 480p.</p>

<p><br />
<B>Frequency Response Color</B></p>

<p>It was in this area that the Panasonic was quite poor via HDMI and HD scan rates. As you reached the higher frequencies the alternating bands would be muted in output or in some places disappear. The contrast levels remained fairly equal from low to high for those bands that would appear but ultimately this was one of the poorest responses I have ever seen for this pattern. Oddly enough changing the output to 480p revealed an excellent response.</p>

<p>Analog component video responded quite well at 480p.</p>

<p><br />
<b>CUE, Chroma Upsampling Error</b></p>

<p>This causes a vertical breakup of color detail in the vertical plane typically expressed in reds but can show up for other colors and is related to the player using only one MPEG decoding method rather than both interlace and progressive and applying the correct version to the native source on the disc. The Panasonic passed this test via both HDMI and analog component video.</p>

<p><br />
<B>Aspect Ratio Control</B></p>

<p>The Panasonic provides an auto 16:9/4:3 switching mode so the player maintains correct aspect with special features or 4:3 movies by adding black side bars. The 4:3 mode also clips the 4:3 content with about 3-5% overscan.</p>

<p>For the DVD collector looking for great performance with all DVD mastering, time did not allow for testing of the Panasonic with 4:3 letter boxed sources.</p>

<p><br />
<B>SD DVD Scaling Analog Component Video</B></p>

<p>The Panasonic was tested at 480p HDMI feeding a 1080p DLP front projector with pixel mapped centered output, along with an adjusted viewing distance to compensate, using the DVE chapter 17 A/V Demonstration material. </p>

<p>The Panasonic passed with flying colors providing the same common level of performance I would expect from most any 480p analog component video output.</p>

<p><br />
<B>SD DVD Scaling HDMI</B></p>

<p>The Panasonic was tested at 1080p HDMI feeding a 1080p DLP front projector using the DVE chapter 17 A/V Demonstration material. </p>

<p>Noted during testing was a very poor response for color resolution as well as scaling errors between colors with color bar patterns. In the DVE demonstration material there is red detail content for a US flag, a Virgin Records billboard at Times Square, a PC graphics animation of blooming flowers and Ferris Wheel. All revealed visible artifacts appearing as anti-aliasing and or interpolation errors. During the CUE test this artifact also showed up slightly in the same areas checked for the CUE artifact. The Luminance portion, black and white video content, did quite well but did suffer from a higher level of noise, dithering and contouring than normally experienced being high enough in level to draw my attention. Another artifact was slight vertical edge enhancement that showed up occasionally through out the material. The typical line interpolation errors and aliasing showed up although far less at 1080p. Having more than double the pixels going from 720X480 to 1920X1080 greatly contributes to better scaling providing harder and more distinct edges since the change from peak black to peak white takes place over a smaller area by comparison due to the higher oversampling. This was not a surprise and expected technically, yet it was nice to see visual verification of this theory. An increase to a 2kX4k imaging chip would double the pixel count yet again easily providing SD DVD edges nearly as distinct as HD! Putting this in perspective though you would need a viewing distance less than 3 screen heights to perceive this benefit and that is typically reserved for front projection and large screens.</p>

<p>Due to the results garnered from objective testing I could not help but switch to 480p output and test again. Like the analog component video the Panasonic passed with flying colors. </p>

<p><br />
<B>Scaling Blu-ray via HDMI and Analog Video</B></p>

<p>Watching some Blu-ray movies via HDMI there was little to fault. Moving on to my test title, X-Men; The Last Stand, I did find one nit picky complaint. As noted in the SD DVD subjective testing there was a higher level of noise, dithering and contouring than normally experienced and this artifact made a subtle appearance with Blu-ray as well. </p>

<p>Via analog video at 1080i there was a hitch because the projector used applies the typical vertical filtering to 1080i content, which softens detail. That said, Blu-ray performed quite well in this application.</p>

<p><br />
<B>Audio Performance</B></p>

<p>Time did not allow for an audio review and in my quick review I was not concentrating on sound nearly as much as video. After the product had been returned I looked over the owner's manual for audio finding a mixed bag of responses if not using HDMI 1.3 and bitstream.</p>

<p>The unit does provide the necessary analog connections and management to support up to 7.1 channels. For digital multi channel PCM this management may need to be turned off or have settings at neutral positions if feeding a receiver since those operations already take place in the receiver. The A/D conversion is limited to 24/96 and any source above that will be down converted. </p>

<p>Per the manual, it does not fully support DTS HD, Dolby TrueHD and Dolby Digital Plus via HDMI PCM and is output as 2 channel stereo. These are only supported as bitstream to be decoded by the receiver. Going further in the manual it is unclear if the product fully supports these codecs in any other form other than HDMI bitstream. </p>

<p>PAGE 22, owner's manual, audio settings</p>

<p>Dolby Digital Plus, Dolby True HD<br />
<i>Bitstream: The bitstream signal of Dolby Digital is output.<br />
PCM: Dolby Digital Plus or Dolby TrueHD PCM sound is output in 2 channels.</i></p>

<p>DTS HD<br />
<i>Bitstream: The bitstream signal of DTS Digital Surround is output.<br />
PCM: DTS-HD PCM sound is output in 2 channels.</i></p>

<p>PAGE 26, owner's manual, troubleshooting guide - sound, No sound, Low volume, Distorted sound, Cannot hear the desired audio type.</p>

<p><i>When playing discs recorded with Dolby TrueHD or DTS-HD, audio will not be output properly unless the number of connected speakers is the same as the disc's channel specification.</i> This infers that some connection type, analog at minimum, at least supports multichannel.</p>

<p><i>If the audio track of the disc was recorded with Dolby Digital Plus or Dolby True HD, Dolby Digital audio will be output from the DIGITAL AUDIO OUT terminal. </i> This is referring to the SD audio digital audio coax and optical connection.</p>

<p><i>If the audio track of the disc was recorded with DTS-HD Master Audio, it will be output as DTS Digital Surround audio. </i> This implies the DTS indicator on your receiver and possibly an actual down conversion to DTS yet the specs indicate full support and that infers via HDMI 1.3 only and the receiver will indicate DTS.</p>

<p>One feature I really liked about this unit was the ability to individually specify the output for each codec and in my case I set the SD codecs for bitstream and the HD codecs for PCM since my receiver does not support the new HD disc codecs. Without reading a thing about the Blu-ray disc I knew what kind of audio I was dealing with simply by looking at my receiver display to see what was going on. Considering what I know now about the audio section this feature is irrelevant since the HD codecs are down converted to stereo for PCM multichannel HDMI.</p>

<p>For this review audio was either bitstream or PCM via HDMI. The following deserves mention for those upgrading a legacy home theater system and starting on the audio side. If you intend to use the HDMI connection for audio it will nonetheless be looking for an HDMI compatible display and if it does not find one it will limit the analog component outputs to 480p for Blu-ray. Only those with a display that does not support HDMI or DVI need to take note of this.</p>

<p>Ultimately, maximum audio benefits come only by using an HDMI 1.3 receiver and setting all codecs to bitstream for the receiver to decode.</p>

<p><br />
<B>Problems</B></p>

<p>None</p>

<p><br />
<B>Service</B></p>

<p>According to the Panasonic site there are no local service centers. This may be an error but more than likely it simply means you have to send it in to a repair depot like nearly all disc players these days.</p>

<p><br />
<B>Conclusion</B></p>

<p>When it came to Blu-ray playback the player appears to meet specs and there is little to find fault with whether HDMI or analog video. In the realm of the nitpicky videophile though the higher level of noise, dithering and contouring artifacts clearly noted with SD DVD made a very subtle appearance with Blu-ray as well. SD DVD is another matter and any videophile will not be pleased with the SD DVD performance and indeed it was in this area of upconversion to 1080p that the player clearly had warts that could be seen by any viewer if pointed out.  This was a surprising result for a Panasonic player. Their past record is one of meeting video standards at times, incorrect color decoding at times but never an artifact ridden image from scaling. Maybe this can be addressed in a firmware upgrade but in its current form I cannot recommend this player for HDMI/DVI videophile applications yet casual viewers, especially with long viewing distances, will likely not notice.</p>

<p>For analog component video applications the player does receive high marks for both Blu-ray at 1080i and SD DVD at 480p!</p>

<p>Audio is at its best using an HDMI 1.3 equipped receiver. It appears the analog connections will support DTS HD and Dolby True HD down converted to 24/96. Those with HDMI 1.2 support or less on their receivers are going to be missing out on those HD audio codecs via digital multi channel PCM.</p>

<p><br />
<B>Putting It in Perspective</B></p>

<p>The DMP-BD10A was a pleasant surprise as well as a disappointment.</p>

<p>If you have a legacy HDTV multi-scan CRT rear projection display for 480p and 1080i such as nearly all the Sony, Panasonic and Mitsubishi products of the time then the Panasonic provides a great entry into Blu-ray along with good 480p scaling whether you are a casual viewer or videophile. There is nothing to find fault with here and you will definitely be increasing both audio and video performance by a number of notches over SD DVD with Blu-ray movies! While the sound may not be the ultimate audiophile expression you will be floored none the less. </p>

<p>Current HDMI/DVI display applications are another matter.</p>

<p>The SD DVD scaling was disappointing. For SD DVD content the source is mastered with progressive flags that will tell any decent scaler how to perform conversion to 480p with little to no artifacts and in this mode the player shined but for HDMI/DVI applications we want good upconversion to bypass the internal scaler of your display and historically DVD players do a decent job at this for the most part. It appears from testing the problem is directly related to the upconversion/scaling process and that same process may be showing some influence with Blu-ray as well since the source is 1080p 24 frames and must have 2/3 pull down applied for conversion to 60 frames. This may relate to the very subtle noise, dithering and contouring artifacts observed with Blu-ray.</p>

<p>Drawing a comparison with other product the Sony PS3 which retailed for $600 just dropped to $500 and while it curiously has the same type of SD DVD source artifacts described for the DMP-BD10A Sony did a far better job of hiding them making their performance flaw far more of a concern for videophiles with close viewing distances and a passion for performance while being easily acceptable for many others; it at least has decent scaling. As for Blu-ray the Sony provides competitive if not ever so slightly better 1080p60 performance plus 1080p24 support which the Panasonic does not. On the other hand the PS3 has no multichannel analog audio support like the Panasonic does. The PS3 is clearly the better overall HDMI performer as well as a huge bargain when considering the additional gaming and media center benefits. On top of that Sony is releasing their own MSRP $600 player only at about this time and should be on your list of entry level Blu-ray players to check out.</p>

<p>If the particular HD format is of no concern then Toshiba, the HD DVD camp, has just dropped prices on two of their players reaching the $400 price point, HD-A20, for 1080p with a 1080p24 firmware upgrade coming early September along with an entry level 1080i player for $300, <a href="/reviews/2007/06/toshiba_hd-a2_hd-dvd_player_review.php">HD-A2</a>. Both are untested by me at this time.</p>

<p>The common everyday casual viewing application where the Panasonic would shine and receive a recommendation is a catch 22. A casual viewing application does not revolve around sheer performance concerns and nearly all are perfectly satisfied with SD DVD at their long viewing distances with many perceiving an HD level of performance as is. While we may be in the midst of a format war both formats combined represent little more than a blip on the horizon compared to SD DVD so no short term threat there for consumer habits. You can't readily rent Blu-ray or HD DVD except over the internet. The hardware price is not right for this market and they are not about to bite until the players reach less than $200. So while this level of performance is a shoe in for the casual viewing application the need does not really exist and the price is too high to be considered.</p>

<p><br />
<B>Conclusion</B></p>

<p>The Panasonic is a great leap board into Blu-ray for multiscan 480p/1080i CRT legacy displays. While there are other displays with more resolving power those old CRT products beat them hands down in other areas and the Panasonic is a great player to add new HD life to your experience as well as getting some more useful years out of the display. The included five free Blu-ray titles and digital or analog multi channel audio will definitely get your HD disc party of oohs and ahs started!</p>

<p>For those with new displays supporting HDMI or those with in between DVI legacy displays the player provides solid performance with Blu-ray for your HD disc party as well! The review points out what the performance concerns might be for you and your SD DVD application.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Richard Fisher</b>, <b>August 15, 2007 10:18 AM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(663)?>
			<div class="dottedline"></div>

			<? if (8 != 7) echo getBoxMoreFromAuthor('Richard Fisher', 663)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author['bio_short'] != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Richard Fisher</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/reviews/2007/08/panasonic-dmpbd10a-bluray-and-sd-dvd-player.php" type="text/javascript" charset="utf-8"></script>
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
