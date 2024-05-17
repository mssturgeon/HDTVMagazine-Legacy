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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 340 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 340
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=Interview - Mark Knox, Toshiba on HD DVD&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/articles/2006/02/interview-mark-knox-toshiba-on-hd-dvd.php&amp;title=Interview - Mark Knox, Toshiba on HD DVD">
		<span style="display:none">Just around the corner is the long-awaited launch of the HD DVD, one of two competing high-definition formats for the DVD optical disk. The stakes could not be higher for the movie business, less so for the manufacturers, and a hair pulling nightmare for the ones asked to finally pay for it all - the consumers.  

I interviewed Mark Knox last week. You will find below my lead-in. Mark has the task of explaining to you, as well as the motion picture industry, why the Toshiba-backed HD DVD is the right choice.

The current backdrop for this launch ... 
The movie business needs a smashing success using a new distribution format to restore expansion and youthful vigor to all parts of the business. They are presently plagued (in good economic times too) by a sagging box office returns and a flat-to-declining packaged goods business. I will not speak of the gamming side of entertainment here for while some ownership is common it is not entirely integrated with the movie culture.

The &quot;collapse&quot; of the box office over the last three years appears more than just a low ebb in a business cycle. Those explaining it away claim that... </span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 340";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Interview - Mark Knox, Toshiba on HD DVD" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Interview - Mark Knox, Toshiba on HD DVD" />
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
	<title>HDTV Magazine - Interview - Mark Knox, Toshiba on HD DVD</title>
	<meta name="keywords" content="mark knox, hdtv magazine, high def, def dvd, blu ray, dvd, hdtv, mark, knox, magazine, high, def, movie, new, people, going, hollywood, those, get, content, even, format, studios, most, any" />
	<meta name="description" content="Just around the corner is the long-awaited launch of the HD DVD, one of two competing high-definition formats for the DVD optical disk. The stakes could not be higher for the movie business, less so for the manufacturers, and a hair pulling nightmare for the ones asked to finally pay for it all - the consumers.  

I interviewed Mark Knox last week. You will find below my lead-in. Mark has the task of explaining to you, as well as the motion picture industry, why the Toshiba-backed HD DVD is the right choice.

The current backdrop for this launch ... 
The movie business needs a smashing success using a new distribution format to restore expansion and youthful vigor to all parts of the business. They are presently plagued (in good economic times too) by a sagging box office returns and a flat-to-declining packaged goods business. I will not speak of the gamming side of entertainment here for while some ownership is common it is not entirely integrated with the movie culture.

The &quot;collapse&quot; of the box office over the last three years appears more than just a low ebb in a business cycle. Those explaining it away claim that... " />
	<meta name="title" content="Interview - Mark Knox, Toshiba on HD DVD" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/articles/2006/02/interview-mark-knox-toshiba-on-hd-dvd.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=340', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2006/02/interview-mark-knox-toshiba-on-hd-dvd.php">Interview - Mark Knox, Toshiba on HD DVD</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Dale Cripps</b> on <b>February 22, 2006</b>
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
				<p>Just around the corner is the long-awaited launch of the HD DVD, one of two competing high-definition formats for the DVD optical disk. The stakes could not be higher for the movie business, less so for the manufacturers, and a hair pulling nightmare for the one's asked to finally pay for it all - the consumers. I interviewed Mark Knox last week. You will find the interview below my lead-in. Mark has the task of explaining to you, as well as to the motion picture industry, why the Toshiba-backed HD DVD is the right choice.</p>

<p><strong>The current backdrop for this launch ... </strong><br />
The movie business needs a smashing success using a new distribution format to restore expansion and youthful vigor to all parts of the business. They are presently plagued (in good economic times too) by sagging box office returns and a flat-to-declining packaged goods business. I will not speak of the gaming side of entertainment here, for while some ownership is common, it is not entirely integrated with the movie culture.</p>

<p>The "collapse" of the box office over the last three years appears more than just a low ebb in a business cycle. Those explaining it away claim with fading conviction that even with these declines there is nothing fundamentally wrong with the business. People still love movies just as much as they ever have and the sky is not falling. It is only a matter of getting back in synch with the public's mood. But 'sorry celluloid' has plagued the big screen before in the post-TV era without sending the box office so steeply into decline. </p>

<p><strong>Why now? </strong></p>

<p>For one ... it's getting old. The film business recently celebrated its 100th birthday. While it papers its walls with pretty young things, it is no longer moved by its dynamic founders with their strong personalities, creative zeal, penetrating insight, risk-taking capacities, and legendary decisiveness. It is managed today by the tight fisted, well-calculated disciplines of Corporate America where shareholder interest is first and foremost in mind, well above the ethereal domains of creativity. Some think that is good, for they will have to turn events over to the more creative young people in Hollywood and stand aside as the business evolves. Even before the corporate takeovers, special effects were used to overcome weak script choices ad nausea. Computer graphics, while often artful (and we can admire and appreciate that), produce little adrenalin unless used in an interactive game environment. The War of The (yawn) Worlds didn't war, King Kong died from a mouse move, and Godzilla had digital clones.</p>

<p>But even more significant to this part of the story is the fact that the ticket buying public of a few years back is now a digital citizen of the international electronic frontier. Minute-by-minute, day-after-day, a huge proportion of them live a significant part of their free time online and order down what they want NOW from a list of categories not even Hollywood has the guts to exploit. There are addiction rehab centers for those hooked by the adrenalin rushes.  (Not one center is to be found for traditional movie goers--the makers of movies, yes). It is evident that modern alternatives, such as Xbox and Play Station, have drained theaters of patrons and left DVD titles to linger on the shelves until discounted. One would think that a more worrisome fact than any is how the theater itself seems out-of-date to the cyber generation. While my analogy may be a stretch, it is like Vaudeville was to the TV generation. How can Hollywood hope to compete with an online passport to the world? Perhaps with digital projection and game controllers in every seat, a hint of what the future will be in movie theaters is revealed.</p>

<p><strong>Can Be Tough Sledding...</strong></p>

<p>While acknowledged as an important element in the next evolutionary chapter of Hollywood, the high-def DVD has tough sledding ahead before benefits can accrue. The biggest hurdle is the growing consumer reaction to high-def DVD studio policy. A backlash turning into an out-and-out resentment is festering, first, and most widely, over the fact that two high-def DVD formats are being rolled out at about the same time. "Why introduce TWO at the same time? Are you <em>craaaaazy</em>?" The consumers are up in arms about this confusing conundrum. </p>

<p>The second is the copy protection measures mandated by the studios. Those measures, or at least what the public thinks those measures are, have enraged the community. Groups are sounding as if they are ready to march on Washington and picket and lobby anyone and everyone against the "draconian" digital rights management measures incorporated in the AACS specification. Blogs and print publications fan the flames of this anger every day. The concern is so great in Hollywood that many fear this consumer reaction will mean that the high-def format--either one--will fail to reach the level of general use. That would set plans back in Hollywood a serious notch.</p>

<p><em>"But how in the world could it fail?" you ask. "Everyone is and has been anticipating it for years. We all love HDTV. So what's behind this question?"</em></p>

<p>From the perspective of comparative picture qualities, the high-def DVD is only modestly more compelling than the standard DVD version. OK, let me hastily qualify that statement since we are all biased towards HDTV. HDTV is what we want, but this market is more democratic than doing just what we want.</p>

<p>No less a major player in the high-def movement than David Niles in New York (the FIRST person to own any HDTV production equipment back in 1986) said years ago that DVD quality for movies is quite enough and the addition of HDTV does very little for the experience. If that isn't enough to jar you then consider the new scaling DVD players coming out at a fraction of the cost of high-def models. The image improvements to be made from a well mastered DVD serve to narrow the image quality distinctions even further. For years Yves Faroudja demonstrated just how much more video information is extractable and deliverable to the screen by using superior components and processing algorithms. </p>

<p>Of course, picture assessment is heavily, and I repeat <em>heavily</em>, dependant upon your viewing distance and the display performance itself. But if the spread between the new and the old format is perceived to be too slight, it will not have the market power to overtake the old standard. The CE landscape is strewn with failures that did not pass the 10 JND test (Just Noticeable Difference-units on a scale used in marketing assessments). </p>

<p>The Super VHS, while better by most all accounts, was still not enough better to overcome the price/performance value proposition of standard VHS, at least in the view of the all-important general public. As a consumer format it died for lack of sufficient comparative distinction. Beyond or besides picture quality, the ten 'Just Noticeable Differences' which is counted on for carrying the new high-def DVDs to victory comes in a large measure from the new set of viewer/users options. Those are first invisible to the customer reviewing the device in a retail setting. The features, which may prove essential perceived values in producing enough market tension to overwhelm standard DVDs will require some behavior changes before they can even be recognized as values. Nor will they be evenly assessed for some have no association with the present feature set on the present DVDs. They watch a movie and it goes back in the jewel case until next viewing. When it comes to behavior mofifications of consumers I always hear the echo of the late, great Howard Miller, former chief engineer for PBS and earlier with Westinghouse. As a young man Howard drove the Westinghouse interactive cable campaign. That was back in the 70s. It failed <em>miserably</em>. When asked why it failed so badly (it was not due to the technology) Howard replied, "Never trust a business plan that for its success requires your customers to change their behavior." </p>

<p>Getting high-def DVD player features known will be a major challenge to the retail sector. When Philips introduced Digital Video Interactive (DVI) in the mid 90s, retailers were trained extensively and long and expensive infomercials were also produced and shown repeatedly for months on end. In all the retail stores I visited during that rollout, none of the sales staff were paying the slightest attention to this new "consumer electronics wonder" and customers passing by had pushed so many buttons trying to get it to do something that they locked up the system, making it totally unusable for anyone else passing by. The president of Philips bet his career on the success of that format. He lost. </p>

<p>I learned that the reason the sales people paid no attention to the DVI was due to its rich features. Had it just played a movie they would have had no problem in selling it. But it took far too long to explain the use of the features. With the attention-span of the customer being so short in such an environment if he/she didn't quickly recognize a feature that they already wanted, interest was lost and they moved on. Salesmen quickly determined that it was not worth the time they had to spend on educating anyone and abandoned it--an abandonment which cost Philips hundreds of millions of dollars. Of course, since both of the new high-def DVD formats are backwards compatible, one easy solution to selling it is to stop making standard DVD players and let attrition take care the penetration problem and make all new programs available only on hybrid disks that play both the HD version and the SD version. That would take a whole lot of agreement...and we see how agreements go in this field.</p>

<p>Another very real dark cloud hanging over the success of either high-def DVD format is the sad fact that most of the early adopters of HD equipment have been pushed out of the first high-def DVD market by copyright measures that can (at the option of the studio) stop the player from sending full HD quality to the display on component cables. </p>

<p>Those vocal 5 or 6 million early supporters so impacted led all of the rest to HDTV and are now being severely discounted, causing them to voice anger directed to the studios. 6 million analog component-only sets can, but don't have to, be limited to playing SDTV. The only mitigating circumstance here is that most early adopters of means quickly become addicted to HDTV and so have acquired a second and third later vintage HDMI-equipped set located somewhere in the house. But this has put another clamp on things because the owners of the newer 1080p displays now want 1080p signals out of the player and HDMI does not yet have that as a mandatory inclusion and Toshiba, for one, won't make a 1080p out until that standard is codified and is enforced as mandatory. </p>

<p>High-def DVD will, of course, succeed where huge corporations manage their destiny; the Blu-ray will go into the Sony PlayStation 3, while the HD DVD is destined for Microsoft's Xbox 360. The computer industry also wants a cheap new data storage scheme added soon. They already have sample lots from both camps and will soon go to market when more powerful processors are available. But what we are talking about here is a mass market which will meet enough conditions to succeed in holding the attention of all concerned. </p>

<p><br />
<strong>There are other problems impacting the launch....</strong></p>

<p>As a movie lover, I want to see Hollywood succeed and, as an HDTV owner, I want one of these high-def DVD formats to succeed. I used to care which one, but now I really don't. But Hollywood's rebirth is actually problematic. Any strategist will tell you that there is danger when a business reaches maturity (or old age) for no matter what is done it will fail because so much weighty legacy conditions hold it back from a total reinvention of itself. When it reaches its apex when decline is inevitable it begins to adapt with a new animation to what it perceives the future to be and it grows expansive in what it does in order to keep attention focused upon it for as long as possible. But it has too many old brittle bones to carry and so exhausts itself in the try. Historians will tell you that every movement, no matter how deeply rooted, that ceases to expand will turn and collapse in upon itself and finally disappear down a hole like the white rabbit. </p>

<p>That is why the stakes in high-def DVD authoring, distribution, and consumer acceptance are so high. The movie business needs to grow and live creatively again, even like a child's spurting through anxious adolescence, and it has to do it not only by adaptation to the future but by being part of the invention of the future where their place is carved out with their own hand. In effect, it has to die (shed the past entirely) and be born again. HDTV is the second coming of television just as it is proving to be for the motion picture business. The high-def DVD is new blood in the veins of Hollywood. Hollywood needs a new distribution model and that is enabled by a new class of technology in the hands of its customers--you and me. This same technology--the high-def DVD--will give Hollywood (and all other content providers attracting our attention) the room to invent and introduce to a huge paying audience new kinds of products which the talent of the world has been unleashed to invent. </p>

<p>But is all of this important enough to Hollywood executives that they will mend their fences with us citizens who, in the end, have our finger on the "Go, Pause, and Stop" buttons, and get us in harness to pull this high-def DVD format to victory? The way it sits now is that we, Hollywood's customers/partners, are going to suffer from the format wars. Some of us are going to lose money and time (who knows what else) by buying the wrong thing. Both camps have very compelling stories and we should never blame ourselves for miscalculating. But this lack of industry accord is far too amateurish for the management team of such a huge and significant public servant as is the entertainment field working in the 21st century. "Let the healthy competition be with the manufacturers and not formats," you will say, "We don't want to decide for Hollywood! That's their job. They have the power and should do it. Our job is to enjoy their products and pay a fair price for them. We can't make policy decisions for an industry we actually know very little about." </p>

<p>All-in-all, the Hollywood studios do understand that they have created a very serious PR problem with the copyright protection measures and there is a huge amount of misinformation floating around about fair use. It is a dilemma which can cost them hundreds of millions of dollars in lost revenues if, in our anger, we boycott or retreat from their products. It looks like a conundrum with no remedy except that which is in their hands...and the switch is in their hands. And if we are good citizens ourselves and don't mess around with infringements on legal copyrights they nor anyone have a need to throw that switch nor even have one. _DC</p>

<p><strong>And now we hear from ... </strong></p>

<p>Mark Knox ... who was called in by Toshiba, the developer of the HD DVD, as chief spokesperson for their March '06 launch. His duty is to explain and sell the system to the world. You will learn in this interview how HD DVD intends to lead the market and then we will have a serious discussion about digital rights management-the divisive producing so much anger in early adopters of HDTV.</p>

<p>In coming days we will interview the Blu-ray spokesperson to see how the grass grows on their side of the fence. But now ... </p>

<p> ... THE INTERVIEW with Mark Knox<br />
By Dale Cripps</p>

<p>Mark Knox is an independent consultant and writer from Union County New Jersey. He is a 25 year veteran of the Electronics Industry who <img alt="mark_knox_single.jpg" src="http://www.hdtvmagazine.com/articles/images/mark_knox_single.jpg" width="146" height="207"align="left"/>has been instrumental in the launch of many new product categories, including the first true Digital Pianos, Dolby Surround Sound, CD recording, Satellite Television, DVD, HDTV itself, and now HD DVD. He has been consistently active in the Consumer Electronics Alliance, having served on various product Division Boards as well as the Technology and Standards groups. In 1996, he received the "Digi" award from Wideband Magazine, recognizing his contributions to the Digital Products industry. Mark lives in New Jersey in a very old house with a very new wireless network along with his wife Kat and five other felines. He frequently enjoys watching terrestrial and cable HDTV, listening to high performance Audio and serenading his wife and the other cats with his guitar and keyboards.</p>

<p>I opened by noting that <a href="http://www.netflix.com/PressRoom?hnjr=8">NetFlix had made a commitment to support HD DVD</a>.</p>

<p><strong>HDTV Magazine:</strong>  We see that you have Netflix in your corner.</p>

<p><strong>Mark Knox:</strong> A few of their technology people hung out at the HD DVD promotion booth at the CES (January 2006) peppering me with very detailed technical questions.</p>

<p><strong>HDTV Magazine:</strong> What were the more penetrating questions?</p>

<p><strong>Mark Knox:</strong> Their primary interest was in durability. They ship DVDs all over the country. They needed to know what the reliability of the HD DVD format is when mailed, punched, sat on, dropped, heated and cooled, etc.</p>

<p><strong>HDTV Magazine:</strong> How did you respond?</p>

<p><strong>Mark Knox:</strong> I said the basic core materials are exactly the same as with the DVDs they ship now. The only difference is how big the bumps are in the middle of the disk. So, they should hold up at the same rate in the same way as do DVD disks today.<br />
 <br />
<strong>HDTV Magazine:</strong> Would that be any different than with Blu-ray?</p>

<p><strong>Mark Knox:</strong> It is more a matter of what Blu-ray will need to do to reach that same level of reliability. I am not going to claim that Blu-ray disks are going to scratch much more easily than ours. I understand they found the world's most expensive "Armoral" to protect the skinny layer. That may help. Our engineers were afraid of two materials bonded together with one thin and one thick. When they heat or cool they will not expand and contract at the same rate, which means that the disks could warp. Even if that warp were imperceptible to our eyes, it is not a small matter for the Blue Rey pickup tolerances.  I won't pretend that they can't fix those issues. They will have to do homework which the HD DVD does not have to do.</p>

<p> <strong>HDTV Magazine:</strong> Do you believe that HD DVD is heading to the winner's circle? If so why?</p>

<p><strong>Mark Knox:</strong> We have a long way to go before there is a winner's circle. We are sitting at the post position waiting for their race car to arrive. If it were a boxing match I would ask if I could put my warm-up jacket on until they arrive. I won't, by any stretch, however, claim that the game is over.  </p>

<p><strong>HDTV Magazine:</strong> Do you see your "first-to-market" as being a significant advantage to gaining a commanding lead? </p>

<p><strong>Mark Knox:</strong> I think it is an advantage. How significant will depend on the answers to some of the open questions about our competitors. If PS 3 really does ship in the Spring and can play Blu-ray movies our market advantage is less than if PS3 ships without a commitment to play Blu-ray movies. It would be a greater advantage for us if f PS3 doesn't ship until the fall.</p>

<p><strong>HDTV Magazine:</strong> Do the consumers stand to get hurt in this powerful contest?</p>

<p><strong>Mark Knox:</strong> The greatest damage is from the fact that we didn't reach one conclusion as we did with the DVD.  Unfortunately, a large number of consumers and publications have said not to plunk your money down until the dust settles. That is going to mean many consumers now enjoying high-definition from satellite or from cable are not going to watch their favorite movies until that dust settles. That could be a long time. That would be one disadvantage to the consumer. </p>

<p>I think one advantage we enjoy is that we are asking the consumer to (only) take a $500 gamble. We are saying, "Buy a (HD DVD) player for $500. A long list of movies insures that you will have content to play with this player. You will definitely be able to play all of your old movies (standard DVD) using it". </p>

<p>For many titles, Warner, Universal and others have said they are going to release hybrid discs for new releases. When you buy that movie (hybrid) you can also play it in your old DVD players. So, I think there is a big advantage in that all we are asking them to do is plunk down $500. That is not really much money considering what a new DVD player cost in 1997. That was a pretty pricey proposition.</p>

<p><strong>HDTV Magazine:</strong> I recall that the first VCRs were $2400. </p>

<p><strong>Mark Knox:</strong> I remember thinking what a hot screaming deal I got when I bought my Panasonic 6 motor, four headed monster for $800.</p>

<p><strong>HDTV Magazine:</strong> Let's go back to some of the basic concerns we have. For the general population-the mass audience--is there enough difference between a well mastered DVD when compared to an HD DVD disk of tomorrow? </p>

<p><strong>Mark Knox:</strong> Yes, for two fundamental reasons. </p>

<p>One - the picture quality itself: One idiosyncrasy that I have noticed when talking to general consumers and relatives is that those who own an HDTV begin to dislike the standard definition feeds. Even though the TV is scaling the standard def up to some approximating high-def, most consumers immediately recognize that the picture doesn't look as look as a high def source.  </p>

<p>We have produced demo content soon to be available where one piece of content was simulated digitally to show the difference between HD and SD. Instead of dividing the screen in the middle we moved the division left and right across the screen so one can look at exactly the same content in SDTV and HDTV. </p>

<p><strong>HDTV Magazine:</strong> Does this hold up when the observer is not at the prescribed viewing distance for HDTV? </p>

<p><strong>Mark Knox:</strong> Yes it does. Obviously, one of the issues with it is distance and screen size and resolution. When you are below a certain screen size, the consumers won't be able to see the difference between the two resolutions.</p>

<p><strong>HDTV Magazine:</strong> What is that screen size (where this makes a difference)?</p>

<p><strong>Mark Knox:</strong> Toshiba engineers ran a series of research projects on the screen size issue to determine what the sweet spots are. The other issue is that consumers typically "over-screen" for the size of their rooms. They have to have a bigger TV than Joe has. When below 20 inches, most people cannot tell you which is the SDTV and the HDTV. The Consumer Electronics Association forecast that from 2004 through 2009 for HDTV only (Direct View, CRT, any form of Micro Display, Direct View LCD, Plasma at 720p or greater) an accumulative 100 million units will be installed. Even if we never sold one unit of HDTV before 2003 some 97% of households would have high-definition at home by 2009. The break point based on today's technologies is that anything below 20" is not going to be HDTV. Beyond that depends upon the manufacturers. By the time you get to 30", with the exception of low priced Plasma, everything will be HDTV. </p>

<p><strong>HDTV Magazine:</strong> NHK, the original developers of HDTV, claimed that you don't get a real payoff until you reach 60 inches. </p>

<p><strong>Mark Knox:</strong> Our engineers determined that once you get to 25" people begin to recognize the difference between standard def and high-def, whether that is 720p or 1080i. Once you get to 36" a lot of consumers claim to see a difference in the quality between 720p and 1080i. Once you get to 40 inches there is a delta between the quality of 720p and 1080i, but 720p doesn't really take a nose dive until you get to that 60 inch point.  That may be what NHK is referring to. </p>

<p><strong>HDTV Magazine:</strong> The good news from the LCD display camp is that adding pixels is inconsequential to cost.  So, it would appear that we are going to have more 1080p (in LCD) and as the new fabs come online (with their improved materials processing and handling) they will be bigger and bigger. </p>

<p><em><strong>Copy Protection ...</strong></em> </p>

<p><em>According to the most recent estimates available from the International Federation of Phonographic Industries (IFPI), the movie industry lost more than $4.5 billion worldwide to physical piracy in 2003.</em> _MPAA.org web site.</p>

<p><strong>HDTV Magazine:</strong> One of the rancorous questions our people keep asking is about digital rights management and the likelihood of their component connections being down-graded. How do you respond to this? </p>

<p><strong>Mark Knox:</strong> AACS made an official announcement recently of an agreement. Being engineers they assigned an unfriendly acronym to this agreement. It is called an Image Constraint Token. </p>

<p>It is the same concept as the Broadcast Flag. But the important detail here is that it is not mandatory for any content owners to turn that flag on. </p>

<p><strong>HDTV Magazine:</strong> Yet it is the content owners who wish this control to be incorporated. How does that square with what you are just saying?</p>

<p><strong>Mark Knox:</strong> It is some of the content owner's wishes to control content to the analog outputs. An important thing to recognize is this: Some significant content owners who participated in those AACS negotiations offered, in return for the key handed to them by the manufacturers, greater flexibility in things like managed copy.</p>

<p><strong>HDTV Magazine:</strong> What is it and how does 'managed copy' work?</p>

<p><strong>Mark Knox:</strong> Managed copy in HD DVD means that every single disk must offer to the consumer the ability to make a legal encrypted copy of his/her disk in another location, most typically a media center PC. </p>

<p><strong>HDTV Magazine:</strong> How is that controlled?</p>

<p><strong>Mark Knox:</strong> Let's say you put a copy on your media center PC. That copy is still encrypted. In order to decrypt it you need a number of keys. One is the content key which is stored along with the (original) media. Another is a key which is unique to that PC derived from some unique element of that PC.</p>

<p><strong>HDTV Magazine:</strong> What about my summer house?</p>

<p><strong>Mark Knox:</strong> Several answers depending on your scenario. </p>

<p>If you have a portable device you can connect it to that same PC and use the managed copy to create a mobile copy (which you can play back on that mobile device). You can do that as long as that mobile device is able to "hand shake" with your media center PC (so they can hand the keys off). If that data file with the encrypted video were to be given to some other entity which won't connect directly the playback device to your computer (needed to successfully acquire all of the necessary keys), then you cannot make the copy. </p>

<p><strong>HDTV Magazine:</strong> And do you think the public is going to earn their PhD in advanced cryptology with all of this?</p>

<p><strong>Mark Knox:</strong>I think the process-the pain for the consumer to perform some of these operations-is going down. I agree that it is far from zero, but it is going down.  </p>

<p><strong>HDTV Magazine:</strong> Undoubtedly so, but ignorance doesn't vanish quickly.</p>

<p><strong>Mark Knox:</strong> We could facetiously say that the acronym for DRM stands for 'Deal Required by Movie Makers.' To some degree we are constrained. We have to recognize that the majority of content owners selling high-def movies are American owned companies and the majority of those selling hardware are not!  It is not like we can look to Congress to pursue the Hollywood community to drop their request. But it is also true that not all of the Hollywood content providers are going to throw that switch.  </p>

<p><strong>HDTV Magazine:</strong> Do you have any 'enlightened" views as to what the importance of copy protection and controls is to the over-all economy of Hollywood? </p>

<p><em><strong>The MPAA posts on its web site their definition of a pirate:</strong>Anyone who sells, acquires, copies or distributes copyrighted materials without permission is called a pirate. Downloading a movie without paying for it is no different than walking into a store and stealing a DVD off the shelf. Motion Picture Piracy is committed in many ways, including via the Internet through downloadable files, selling pirated DVDs on the street or capturing and redistributing live broadcasts or performances without a license on the Internet. Downloading movies and music without the authorization of copyright holders is a growing international problem that presents serious challenges for the movie industry and has serious legal consequences. </p>

<p>People often download movies on the Internet because they believe they are anonymous and will not be held responsible for their actions.  They are wrong.  The illegal downloading and swapping of movie files is a serious crime.  Pirates and their affiliates can and will be tracked for engaging in Internet piracy.</em></p>

<p><strong>Mark Knox:</strong> There is no question that robust digital rights management (DRM) is a requirement from many members of the Hollywood community prior to their submitting content.</p>

<p><strong>HDTV Magazine:</strong> But is this a paranoid reaction or is there hard evidential reality behind it?</p>

<p><strong>Mark Knox:</strong> I will say that some, though not all, understand that the occasional guy making a copy for his personal use is not where their problem lies. Their issue is vast quantities of pirated copies being made and sold. The fact that you can see brand new movies on tropical islands using a projector displaying on a sheet hanging from the palm trees cannot be ignored. That is the issue they are very concerned about, hence the whole digital cinema initiative (the idea where movies would never go to any packaged media or film but would be distributed by a secured network right to the theater.) </p>

<p><strong>HDTV Magazine:</strong> I talked to MPAA representatives a few years ago. At that time the copy protection measures were said to be for discouraging ordinary people who without a constraint might copy a movie for their children (away at school, for example), or a neighbor, or friends at work. Those in turn, went the scenario, would feel free for doing the same until a viral-like epidemic had occurred. That would leave the movie so widely distributed that any residual values enjoyed by today's stair step marketing structure would be lost.  It was not, as I understand it then, the fear of dedicated and organized pirates that drove this particular initiative. That view may have changed since then. Here is what the MPAA now posts on its web site: </p>

<p><em><strong>WHAT IS OPTICAL DISC PIRACY?</strong></em></p>

<p><em>Optical disc piracy is the illegal manufacturing, sale, distribution or trading of copies of motion pictures in digital disc formats including DVD, DVD-R, CD, CD-R and VCD. These illegal hard goods are sold on web sites, online auction sites, via e-mail solicitation and by street vendors and flea markets around the world. Much like downloadable media, the pirated motion pictures in hard goods format are typically poor-quality video-camera recordings.  </p>

<p>While the majority of pirated optical disc products seized by law enforcement worldwide are made on advanced commercial replication lines, the low cost of disc burning hardware and blank discs has led to the proliferation of DVD-R and CD-R burner labs.</em></p>

<p><br />
<strong>The MPAA continues...</strong><br />
<em>Organized crime networks incorporate the use of threats, violence, intimidation and corruption to establish and maintain control. When you engage in piracy, you may be supporting these networked criminals.</em></p>

<p><u>Editor's Note:</u> How is the public to resist a gun to the head by organized crime and how is organized crime not going to have the keys to unlock any content they want to use in any way they see fit? The key to stopping this particular criminal activity is to see that the citizens of the world are led to not buy any pirated products. A movie is not crack cocaine and an addiction to the singular benefit of receiving a lower cost for an early release is easy to overcome. Those measures which will benignly discourage the public's interest and participation in this crime are the right measures to take. _DC</p>

<p><strong>Mark Knox:</strong> They do have legal recourse where piracy is concerned and there is a lot of forensic help built into the system to help the studios. And yes, you are right. They are concerned about mom and dad and sis and brother-in-law and that home (copying) syndrome. That is one reason the hybrid disc is such a popular concept with the studios.</p>

<p><strong>HDTV Magazine:</strong> Can you elaborate on the Hybrid disk?</p>

<p><strong>Mark Knox:</strong> There are two flavors. There is the one disk that contains both the standard DVD version and the high-def version on the same physical media. There is the single layer version that has one DVD layer and one HDTV layer on one side with the label on the other side. Then there is a double sided version which has two DVD layers on one side and two HD DVD layers on the other side. At a meeting in Hollywood, one of the replicators asked if it would not be cheaper to make two disks?  Two studio executives responded in near-unison saying, "It might be cheaper, but it's not better. We don't want them to give the standard version to their sister". So, with all on one disk you don't undermine the potential sale of yet another copy. </p>

<p>The sad reality is this: If we were not willing to acquiesce to the request coming from some of the studios to give them this ability to "plug the analog hole" the odds are that those studios would not be delivering content to the format. I do think that there are market forces in place, and you represent one of them, to impact these decisions. If two movies of equal standing to consumers come to market and one of them has the flag turned on and the other doesn't ... what is the response of the consumer (and editors like yourself) going to be to that fact? Even if some studios are adamant and throw the switch on the early releases, the market is going to have a say as what subsequently they do. </p>

<p>As far as the consumer's awareness is concerned there will be "language" in the player noting that the flag is "on".</p>

<p><strong>HDTV Magazine:</strong> Are you saying to the public that the component out to my early model HDTV will have an on-screen notification which says that the program has been down-converted even though I may have paid a premium for an HDTV disk?.</p>

<p><strong>Mark Knox:</strong> I fully understand the public's concerns. In this case the studios are deciding to disenfranchise a fairly large number of HDTV television owners (estimated at 6 million who are without HDMI or DVI connections).  </p>

<p>Unlike in 1997, when we launched the standard DVD, the industry's business structure was quite different than it is today. Many of the studios were independent then and you could appeal to the most senior management of the studio and need to go no further. That is not the case today. The senior studio people have to answer to one who is now over their heads and that one is not a Hollywood entity but rather a corporate one. That tends to place restrictions on things. </p>

<p>I do think the reality is that some studios will throw the switch (for down converting) and they will get beaten up in the press and by consumers. Because of the Internet, the power of the consumers is no small matter. I think many of those studios will ultimately change their tune.</p>

<p><strong>HDTV Magazine:</strong>  In his final days at the MPAA Jack Valenti spoke out on something we had urged, which is to recognize that society has at least some responsibility in producing or eliminating the need for these protective measures. No bank is without its steel vault and no one questions why. As the good guys we know that a certain percentile of our fellow citizens violate rules and so prisons are built. The collective price for their crimes is in the billions of dollars and more locally enforces endless inconveniences upon all of us. They are regrettable but understandable consequence of the way things have become in our social system. But when it comes to software all of that understanding seems to vanish. </p>

<p><strong>Mark Knox:</strong>The sad thing is that there are a lot of very knowledgeable people who are capable of making very compelling arguments, but they restricted by their management in delivering those messages in full. It is a fact that the theft of intellectual property is a fundamental problem in the United States. It is a huge challenge, but the pedestrian reality for this case in hand is, "Yes, I am sorry. There is going to be some case where you have an older HDTV and you will not be able to watch your movie in high def as much as you might like to."</p>

<p><strong>HDTV Magazine:</strong> And those are going to be the most vocal people in the nation who were the early adopters of HDTV technology and who are now screaming at the top of their lungs that they faultlessly pioneered the field only to now be abandoned and discarded while their HD investments are destined to operate undiminished for years to come. </p>

<p><strong>Mark Knox:</strong> And, I might add, they tend to be your most ardent readers. </p>

<p><strong>HDTV Magazine:</strong> There are also people who are now wanting to buy 1080p monitors with 1080p inputs but they are finding that not so easy. Why?.</p>

<p><strong>Mark Knox:</strong> There are more TVs with P on the front than connectors that can receive P in the back. It is an issue of standards that are evolving. Here is what I understand as of today (I am told I will learn more on an upcoming trip to Japan). We will develop documentation in Japan that we can deliver to you later explaining all of this issue. But here is what I now understand and then I will explain to you what I don't understand but hope to learn.  </p>

<p>The current approved standard for HDMI is Ver 1.1. Within that version there are several mentions of some frame rates for 1080p, but they are listed as an optional specification. What that means is that if you are building a product you are allowed to support 1080p, but only at certain frame rates. But you are not required to support 1080p.</p>

<p><strong>HDTV Magazine:</strong> What is required?</p>

<p><strong>Mark Knox:</strong> That would be 1080i and below. Then there is a similar story when it comes to the higher resolution and lossless audio codecs. In Toshiba's case there is a very clear set of internal regulations for hardware. When they release a product it can be based only upon an existing mandatory specification. It is a very easy matter for you to allow the player to scale to whatever is native on the disk to 1080p at 24, 30 or 60 fps. But, if you do that there is no guarantee that the receiving device is going to know what to do with that data since with the current Ver 1.1 of HDMI there is no requirement that any device has to support any of those formats. For that reason, and the fact that Ver 1.1 will be the only certified standard for at least some months-well after we launch hardware in March-we are not willing to put 1080p output over the HDMI connector. </p>

<p>The other issue, where my own CPU speed begins to fail me, is with respect to the telecine process used. The raw data format for HD is very different than what was used for DVD. Even though DVD generally was captured in high-def and then processed down to standard def, the new telecine format is very different.  It is related to the fact that you have more than one codec to play with. I am promised an education on exactly what those data formats are when in Japan. The telecine people have been involved with HDTV for some time now and are a bit ahead of where we are. They are dealing with both MPEG 2 and MPEG 4 or VC1. Because of the technology behind that there was the desire to change the telecine process. The raw data format is, in turn, deciding what native format will be on the disk. That in turn is deciding what we will do when we get to the player. I have only dangerous bits of information so far.</p>

<p><strong>HDTV Magazine:</strong> We have on our forums some very knowledgeable and influential people. They have made it known that they will not make this move to upgrade to any HD DVD format until they can be assured of 1080p end-to-end. </p>

<p><strong>Mark Knox:</strong> I understand that completely. That is why the current plan from the HD DVD Promotion Group is to send me to all of the engineers who understand everything perfectly. When I am at full knowledge I will develop a document and distribute to you and the others in the press. It will say clearly, "This is the way it works from end to end."  </p>

<p><strong>HDTV Magazine:</strong> It looks to me as if we are about to introduce with the high def DVD a completely new set of confusing conditions to further confound the public on HDTV. What is being done in your circle to minimize this confusion, if anything?</p>

<p><strong>Mark Knox:</strong> There are a couple of initiatives. One, if it doesn't sound too bold or optimistic, is that under inspiration from other members of the HD DVD group, Toshiba decided they needed someone to be a dedicated spokesperson with no other distracting responsibilities for the format. That is why you and I are having this conversation.</p>

<p>Second, is the member companies, and most obviously, Toshiba, have invested a lot of time and resources in material which is being developed as well as web site assets that are dedicated specifically to HD DVD. When a consumer goes to the Toshiba web site right they can get some preliminary information. That will be flushed out quite a bit. Another set of materials consist of "white papers" which will be released soon. I have made the final translations. That document will also be a primer on the technology. </p>

<p>But I think the most important force that will be assisting the consumers is our retail partners. They have been pushing us on how to best present the answers to the nagging questions. Whether it's the guys at Amazon, Crutchfields, or Best Buy, or the Tweeter Group, all recognize that it is going to be consumer confusion. </p>

<p>One of the fundamentals to the HD DVD approach is that because the format allows multiple codecs but multiple video streams because the HD DVD platform requires multiple video memories. It means that you can do things in parallel process that will make operation of the content easier than it is now in a traditional DVD.</p>

<p><strong>HDTV Magazine:</strong> Can you provide an illustration?</p>

<p><strong>Mark Knox:</strong> The most popular extra feature that is put on a DVD today is the director and actor comments. You are watching the move and you see a particular scene and say, "Gee, I wonder what actor thought about that scene? In order to get to that information on today's DVD for the most part you have to stop the disk and navigate to an upper menu, find that special feature, presuming it's on the same disk, and navigate back. On HD DVD you will push a button. The movie will continue to play and you will be able to select from a menu that is translucent right on top of your movie the director's comments. That function will recognize where it is in the movie and will not only drop the film audio down, but not out, and you can now hear the actor talking about the scene and you will be able to see his face as well. That is an option because you have two video streams. </p>

<p>Another interesting benefit of this technology is all of us on occasion will use closed captions. A phone rings, something is going on. You don't want to stop it. But closed caption is just written language and yet there are two or three people talking. How do you figure out who said what? With HD DVD you can have little images so when a voice comes up there is a face of an actor telling you that this or that actor is the one speaking at any given moment. There is a whole plethora of other features which are easier to implement and easier to operate because the platform is capable of doing all of this stuff at the same time. There are actually three levels of video and three levels of audio. All must be processed in real time. </p>

<p><strong>HDTV Magazine:</strong> From the standpoint of CPU power is this player groundbreaking?</p>

<p><strong>Mark Knox:</strong> It is a powerful machine. Even through there have been sample HD drives for both desktop and notebooks available to OEMs for weeks, there have not been many announcing finished products with drives installed. The horsepower of the PC has to be sufficient to handle the computations. In the case of the Toshiba player, it is not only some really high power CPUs and massive amounts of memory, there is a whole bunch of hardware that is there to do the dedicated decoding. It gets even more complex when you figure that the main video feed might be MPEG 4 and the sub-video feed could be VC1. You have to mix them together. One set of audio could be Dolby Digital. Another could de DTS. You have to decode them box, mix them together and then re-encode and deliver them to multiple outputs.</p>

<p><strong>HDTV Magazine:</strong> How distant is the HD DVD away from being a game console? </p>

<p><strong>Mark Knox:</strong> In terms of its pixel pushing power it is no slouch. It definitely is not a Xbox 360 nor a PS3, however. The reasons get involved with respect to the internal architecture. The key point is that the HD DVD engine uses most of the horsepower in the machine to accomplish all of the encoding, decoding, recoding of the content. There is still enough remaining to integrate a bunch of different things. From the beginning the platform was designed to not only be powerful, but to be efficient enough so that you can do cool things without writing miles of code. You have some very creative people within the Holy Trinity - GDMX, Technicolor, and Delux. Some of those people are authoring and pressing Xbox 360 and PS3 games. Additionally, digital studios has already installed a server farm to support the Internet-enhancements for some of the PS3 games. They have also authored for HD DVD. I am no gaming expert but I think there are a lot of creative people who have the contacts and resources and you will see in the next couple of years some really interesting content. It is going to blur the line between what is a video game and what is a film. I think that is going to occur first on the package media side.</p>

<p><strong>HDTV Magazine:</strong> Are there people who are presently developing programming that take full advantage of all of the horse power of HD DVD? </p>

<p><strong>Mark Knox:</strong> Yes, there have been many in that mode for some time. Supporting companies are insinuating themselves into the (movie making) process before anyone gets to film. They are gathering additional elements before, during, and after the shooting of the movie. They capture things about the movie being made in order to get "gems of stuff". They seem to get more involved in the entire making of the film. I can't pretend to understand all plans but I do understand that after the product is delivered they have a lot of "stuff" to be added on the DVD.</p>

<p>Recently when in Japan, I went to Memortek, the largest disc replicator there. I asked to see the mastering process. I was not allowed. At that time of my request I was in a clean room with very tight fitting clean suit on. In still another glass enclosure in side of this one there stood another class of clean room. All of the windows were covered with paper with Xbox 360 logos on it. If those fellas at Memortek are inundated in Xbox 360 logos I must presume that there are a lot of people learning about both sides--movies and games. I think you will see a shift where you no longer buy a movie, watch it, and it's done with. There will be more compelling things for you to do with that content which will be interesting to you for a greater period of time.</p>

<p>It is in the studio's interest. If this addition is really compelling they can look to value added merchandise. </p>

<p><strong>HDTV Magazine:</strong> A lot of people say that the stakes between you and Blu-ray are really high. What are those stakes?</p>

<p><strong>Mark Knox:</strong> For Toshiba this is just one of many initiatives. Toshiba fully recognizes that packaged media is only one element of consumer electronics. As one of the major manufacturers of hard disk drives - both big high capacity for desktops and little ones for notebooks, and even smaller ones for things like iPod--there is an era coming where people will never buy a piece of plastic to get packated contente. So yes, stakes are high for both parties but from Toshiba's perspective there are many other projects which we are working on in parallel. The biggest stakeholders are the Hollywood people. Unlike with DVD, where they only had to make one thing, they must face hard decision over release of their movie. Do they do it in both formats? "Which one can I expect to sell more." </p>

<p><strong>HDTV Magazine:</strong> Is that a big problem for them or an accounting problem?</p>

<p><strong>Mark Knox:</strong> This where we look to you guys to ferret out answers. We would not get answers directly from them. We suspect that even today the complexity of making a single layer 25 Gigabyte disk is higher than is being let on. But we don't have hard numbers to back that up.</p>

<p>One thing is clear. Warner's, among others, petitioned Blu-ray to adopt iHD as the interactive platform in addition to BBJ. Why did they do that? Because the GDMX, which is the Warner's authoring arm, is not looking forward to dealing with BBJ. The iHD has been working closely with us and with Microsoft in order to get a handle on interactive authoring. But even allowing that iHD will be easier to program it is still not going to be easy. Why? It is because of all of the flexibility available to authors. That is the chief reason. On one hand they are fat and happy because they can do all kinds of thing which the original DVD did not support, but on the other hand they have to implement them. </p>

<p>There are a series of projects under the DVD Forums technical coordination group where the authoring teams work with the studios, especially Disney. Disney was deeply involved with interactivity from the start. These teams are presently making a list of the most common types of interactivity and are putting the best engineers to work for implementing the examples. They will put that code out duty free to anyone who is authoring disks. With this free code you have a "cook book" for implementing interactivity which enables you, for example, to put a talking head on top of existing video </p>

<p><strong>HDTV Magazine:</strong> Are these studios amenable to this kind of free sharing?</p>

<p><strong>Mark Knox:</strong> I think so. These are very competitive studios, of course. We have conference calls where with us they are willing to say things which they are not when competitors are on the line. One thing very true, however, is that the guys doing the work are not allied to only one studio. People from Delux Digital Studios and the folks at Technicolor have multiple studios as their customers. They are more than happy to take advantage of some of this shared work. </p>

<p>The second point about authoring and its complexity is that Toshiba and NEC and Memortek, along with many other companies, have voluntarily committed LOTS of resources to help these contractors get this stuff done, whether that be encoding tools for the new codecs or advanced authoring platforms. We have a lot of engineers who are already frequent flyers, and it's only February. We set up a permanent office in Burbank so when a studio has a question they call up and say, "Hey, this is the issue. What can I do?" As often as not our guy says, "I will be right over"?</p>

<p><br />
<em><strong>More tomorrow....</strong></em><br />
</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>February 22, 2006 10:59 PM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(340)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 340)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2006/02/interview-mark-knox-toshiba-on-hd-dvd.php" type="text/javascript" charset="utf-8"></script>
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
