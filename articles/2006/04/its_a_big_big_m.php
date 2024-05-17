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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 357 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 357
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=It\'s a Big, Big Mitsubishi World&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/articles/2006/04/its-a-big-big-mitsubishi-world.php&amp;title=It's a Big, Big Mitsubishi World">
		<span style="display:none">Mitsubishi Electric Digital Television presented their annual &quot;line show&quot; for the press who cover consumer electronics. The event this year fell on the 7th of April and was held at the elegantly appointed Hyatt Huntington Beach Resort and Conference Center in Orange County, California. It was tough duty but I was there for you! The Mitsubishi dealers gathered the following day for the same presentation.

Our afternoon led off with a brief economic report: &quot;We will end this year with $31 billion in global sales,&quot; said Cayce Blanchard, VP of Corporate Communications. &quot;The company,&quot; she emphasized,&quot; is in good financial health.&quot; Indeed, they posted a nifty $819 million net profit (Gee, just inching ahead of HDTV Magazine!!) 

Following on the heals of that report a &quot;simulated&quot; &lt;em&gt;broadcast &lt;/em&gt;of the new MTV HD channel was cued up-a channel which Mitsubishi is co-sponsoring. It's clearly not your grandfather's TV any more. Nor will pops admit to watching the bare midriff programming. This set maker is out to win a younger crowd...</span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 357";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download It\'s a Big, Big Mitsubishi World" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="It\'s a Big, Big Mitsubishi World" />
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
	<title>HDTV Magazine - It's a Big, Big Mitsubishi World</title>
	<meta name="keywords" content="big screen, mitsubishi electric, line show, consumer electronics, light source, mitsubishi, big, screen, hdtv, market, new, still, dlp, first, products, set, well, light, laser, show, business, manufacturers, models, far, color" />
	<meta name="description" content="Mitsubishi Electric Digital Television presented their annual &quot;line show&quot; for the press who cover consumer electronics. The event this year fell on the 7th of April and was held at the elegantly appointed Hyatt Huntington Beach Resort and Conference Center in Orange County, California. It was tough duty but I was there for you! The Mitsubishi dealers gathered the following day for the same presentation.

Our afternoon led off with a brief economic report: &quot;We will end this year with $31 billion in global sales,&quot; said Cayce Blanchard, VP of Corporate Communications. &quot;The company,&quot; she emphasized,&quot; is in good financial health.&quot; Indeed, they posted a nifty $819 million net profit (Gee, just inching ahead of HDTV Magazine!!) 

Following on the heals of that report a &quot;simulated&quot; &lt;em&gt;broadcast &lt;/em&gt;of the new MTV HD channel was cued up-a channel which Mitsubishi is co-sponsoring. It's clearly not your grandfather's TV any more. Nor will pops admit to watching the bare midriff programming. This set maker is out to win a younger crowd..." />
	<meta name="title" content="It's a Big, Big Mitsubishi World" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/articles/2006/04/its-a-big-big-mitsubishi-world.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=357', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2006/04/its-a-big-big-mitsubishi-world.php">It's a Big, Big Mitsubishi World</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Dale Cripps</b> on <b>April 10, 2006</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=14&category=Marketplace">Marketplace</a></b>
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
				<p>Mitsubishi Electric Digital Television presented their annual "line show" for the consumer electronics press corps. The event fell  on the 7th of April this year and was held at the elegantly appointed Hyatt Huntington Beach Resort and Conference Center in Orange County, California. It was tough duty but I was there for you! The Mitsubishi dealers gathered the following day for the same presentation.</p>

<p>Our afternoon led off with a brief economic report: "We will end this year with $31 billion in global sales," said Cayce Blanchard, VP of Corporate Communications. "The company," she emphasized," is in good financial health." Indeed, they posted a nifty $819 million net profit (Gee, just inching ahead of HDTV Magazine!!) </p>

<p>Following on the heals of that report a "simulated" <em>broadcast </em>of the new MTV HD channel was cued up-a channel which Mitsubishi is co-sponsoring. It's clearly not your grandfather's TV any more. Nor will old pops admit to watching the bare midriff programming. This set maker is clearly out to win a younger audience to a big screen experience. To do that they engaged "hip" stars using even "hipper" language, and, oh yes, GAMES. </p>

<p><br />
<img alt="mitsubishi1.jpg" src="http://www.hdtvmagazine.com/articles/images/mitsubishi1.jpg" width="384" height="271"align="right"/><strong>Get The big Picture</strong><br />
Size definitely counts. "BIG screen" (once a bad word in consumer electronics) was the underlying theme for this year's snazzy lineup. The average screen size is by all measure moving up. We are no longer content with anything mandated by CRT limitations and already showing dissatisfaction with the smaller HDTV models of recent history.  </p>

<p>Mitsubishi's ability to recover a once a commanding lead in the big screen HDTV market is still questioned by many industry analysts. They scratch their heads at what seems a vain attempt to stay competitively in play against the likes of Samsung, Sony, Toshiba, Philips, LG etc. Wall Street was showing no skepticism, though, and ran the Mitsubishi Electric's stock up nearly twofold over the last 12 months. The company is, of course, into much more than consumer television.  "...if you can imagine it, Mitsubishi Electric makes it!" proclaims their web site (with no apology for exaggeration). </p>

<p>Don't be confused by the name "Mitsubishi" either. Mitsubishi televisions are made under the Mitsubishi Digital Electronics America, Inc. umbrella, which for most of its U.S. business is headquartered in Irvine, California. Founded in 1870, Mitsubishi built a broadly based conglomerate and played a central role in the modernization of Japanese industry. The name "Mitsubishi" is found today on cars, trucks, power plants, banks, financial services, rubber plantations, airplanes, defense products, gas, oil, and heavy road equipment, but each of the 30 principal Mitsubishi companies (and hundreds of subsidiaries) have lived autonomously since 1946 when our post war occupation forces demanded decentralization (a breakup). The presidents of each of the 30 divisions bearing the Mitsubishi name still meet, however, on the second Friday of every month at what is known as Mitsubishi Kinyokai, or the Friday Club. So, while Mitsubishi Electric is itself big, it stands apart from all of the other like-named companies sharing only a common cultural heritage-something still important in a ruling class structure which exists today in Japan. </p>

<p>In their first venture into television Mitsubishi targeted the big screen (of that day) and delivered the largest CRT set in the world. They later abandoned the single tube to concentrate on larger three tube rear projectors. They lept into the HDTV business realizing their strength in big screen sizes would serve them well, and it did. They became the undisputed market leader in HDTV.  But they were trounced (in terms of market share) in subsequent years by their huge competitive rivals. That trouncing led to speculation over their chances of survival in television. Considering their great diversity they had no particular need to stay in the game. But for good or bad they stuck with it and now look to be a far more formidable and respected competitor than in the past and with a more broadened target market. </p>

<p><strong>Replay</strong><br />
Those who have been pleased with their first HD-Mitsubishi purchases will no doubt be back to them for the next round.  Like the rebirth of the Internet (now being called Interent-2) this next phase of the HDTV market development should be called HDTV-2 (you heard it hear first, folks). By that I mean that the second wave of early adopters are stepping out now to replace their first HD primary viewing device with one having the newer advances in color management, resolution, brightness, and digital connections like HDMI. Mitsubishi should do well if the innovations they have placed in imaging are promoted to their old audience as well as to the younger generation straying from TV into gaming. I can tell you from first hand observations that they do deliver the goods at a price that was unimaginable a few years ago. They do show heavy reliance upon rear projection and this category has weakened, especially in Japan where small living spaces demand flat screens. While the U.S. market has taken to flat screen LCDs and plasmas almost as a fashion statement the new level of performance in DLP projection is thought to successfully go swim that tide. Americans still have more room than most do in other nations.</p>

<p>If bigger is better more pixels is supreme. Mitsubishi is quickly becoming an all 1080p outfitter. No more of that wimpy 720 p stuff, which has you sitting at 4, 5, or even more picture heights so you can resolve the images clearly. In order to sell big screens within the average living space in America homes today you must sit closer to the screen-three or less picture heights without artifacts distracting or pushing you back. The best way to do that is to make sure all two million, two hundred thousand pixels (potential in the 1080 standard) are painted on the screen with progressive scanning with noise and motion anda interlace artifacts held to a minimum. That, in theory, allows, or even encourages, a closer viewing distance, which means you can buy more square inches of picture for more money! The challenge has been viewing of old NTSC grade content on these newer sets. You may have the seating arrangement suited to HDTV only to be driven back by the "ugly" NTSC presentation. As HDTV becomes the ONLY signal we are willing to watch, that issue will vanish.  </p>

<p>As added inducements all manufacturers have invented new features which a thinking HDTVite just can't stand being without. I mean, how can a thoughtful person buy a DLP the size of mount Everest that doesn't have a "6 Primary Color System, TurboLight 150TM, Plus 1080pTM, Tru1080p Processing, 4D Video Noise Reduction, PerfectColor TM , ClearThought-R, Easy Connect and enough interfaces to satisfy ... well ... anyone? And ... due to increased light output efficiencies you may need dark glasses to avoid retina damage! I mean...when is it too bright? That threshold seems crossed to me. Of course, this intense brightness is not for us at home but rather for us at the store so we can see that this or that set is far superior by the amount of light shinning in your eyes. The good news is that these over-bright displays moving into your local TV store are fully adjustable to a more pleasing level...like we movie aficionados (in particular) appreciate.</p>

<p><img alt="Mits2a.jpg" src="http://www.hdtvmagazine.com/articles/images/Mits2a.jpg" width="370" height="316"align="left"/><br />
It's a Laser in your future ... <br />
The BIG headline that preceded the event appeared in the April 3rd edition of the New York Times in the Business Day section. It announced Mitsubishi's plan for a new 3 color (red, green, blue) laser addressed DLP projector. The promise of this development is a smaller footprint, still more brightness, a light source that will last for the lifetime of the set, lower power consumption (becoming a huge deal in California), a wider color gamut, and it can slip into just about any space in your house.  </p>

<p>The announcement caused more than a few eyebrows to lift not to mention expectations. I went there with the dreamy eyed idea of buying one of these laser devices to replace my dearly departed Toshiba, which bit the dust a few weeks ago. But alas, all what was available to me and you was a handsome mock up and a standing promise from the technical team to fill the box by Christmas of 2007. </p>

<p>Why announce something so far in advance? That question produced several nonsensical answers. One, for instance, said that it would set up an anticipation in potential plasma buyers (Mitsubishi is only in LCD and DLP) so they would set aside their plasma purchase (and dollars) for this hot new thing when it finally arrives. The dealers, I am told, have disagreed and fear that the announcement will not only hold off a decision on plasmas but a decision on constant light source DLP models as well. Another more plausible answer was that they (Mitsubishi) are not the only ones working on laser addressing and wanted to get the first PR jump in the market. According to the company's newly installed president, Masaharu Abe, they have their hands full in making the production model. Large investments must still be made. Mr. Abe's strength lies in bringing highly complex developments to market quickly. I would speculate he was brought in to hasten the laser to its market.</p>

<p>As I walked about the exhibition pushing my ample nose up against the new LCD and DLP 1080p products it struck me how much more dependant these manufacturers have become upon the quality of the signals reaching their products. The old complaint one incessantly hears about DirecTV, DISH, and cable companies concerning "over compressing" signals (and, thus, producing visual artifacts) will not help big screen 1080p quality perception nor sales. It is precisely this point which makes the high-definition DVD an important component in the future of big screen 1080p success. Mitsubishi remains neutral on DVD formats and will have, I overheard, a high-def DVD product in the marketplace when the format war blood is mopped up. </p>

<p>Another concern expressed to me by top management is the retailing of 1080p displays. Vic Murty, who says his job intensifies after this line show, notes that one of the weak ends is still how retail exhibits their products. While things are better than when HDTV first hit the marketplace it is still a big stone's throw away from being perfect. Not only do manufacturers have to make their products artificially bright to attract showroom attention in overly light-filled spaces, but the signals used to show off their displays are far too often not uniform and seldom, if ever, 1080p native. One might wish that the retailers in the future would just lease out space to the manufacturers and let them install their own well-trained sales staff to present their products in a controlled environment that best shows the goods. But that would take a major restructuring of the retail business. Anyone going to the Chicago Mart knows, however, that this has been a means for doing high-end business for half a century or longer. In fact, the ancient bazaars around the world were often hundreds of individual shops renting space under one roof. </p>

<p>That's all for today. Tomorrow I will walk you through all of the models as they were presented at the line show and we will learn the meaning of the new acronyms swelling up the HDTV "dictionary".  I will post technical details along with the aesthetics of Mitsubishi's four "fully featured" 1080p LCDs, now in 37 and 46 inch sizes, and seven new 1080p DLP models ranging in size from 52, 57, 65 on up to a whopping 73 incher. <br />
Until then__Dale</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>April 10, 2006  6:30 PM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(357)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 357)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2006/04/its-a-big-big-mitsubishi-world.php" type="text/javascript" charset="utf-8"></script>
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
