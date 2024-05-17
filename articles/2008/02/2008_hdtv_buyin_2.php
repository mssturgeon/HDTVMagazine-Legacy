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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1239 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 1239
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=2008 HDTV Buyers Guide, Part 4&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/articles/2008/02/2008-hdtv-buyers-guide-part-4.php&amp;title=2008 HDTV Buyers Guide, Part 4">
		<span style="display:none">The last in a four-part series of articles on buying an HDTV. The following topics are covered in this segment:

HDTV as a System, not Just a TV Set 
Recording and Digital Connections 
Analyze the Connectivity Issues 
HD Integrated Tuners 
Controls, Cables, Screen Shields, ISF, Stores, etc.</span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 1239";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download 2008 HDTV Buyers Guide, Part 4" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="2008 HDTV Buyers Guide, Part 4" />
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
	<title>HDTV Magazine - 2008 HDTV Buyers Guide, Part 4</title>
	<meta name="keywords" content="hdtv buyers, buyers guide, sweet spot, content protection, home theater, hdtv, cable, might, viewing, tuners, video, cost, integrated, home, dvd, audio, hdmi, digital, system, stbs, content, protection, screen, isf, better" />
	<meta name="description" content="The last in a four-part series of articles on buying an HDTV. The following topics are covered in this segment:

HDTV as a System, not Just a TV Set 
Recording and Digital Connections 
Analyze the Connectivity Issues 
HD Integrated Tuners 
Controls, Cables, Screen Shields, ISF, Stores, etc." />
	<meta name="title" content="2008 HDTV Buyers Guide, Part 4" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/articles/2008/02/2008-hdtv-buyers-guide-part-4.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=1239', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2008/02/2008-hdtv-buyers-guide-part-4.php">2008 HDTV Buyers Guide, Part 4</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>February 13, 2008</b>
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
				<div class="editorial">The following article is the latest in the 2008 HDTV Buyers Guide series. Other articles in this series are as follows:
<ul>
<li><a href="/articles/2008/02/2008_hdtv_buyers_guide_part_1.php">2008 HDTV Buyers Guide, Part 1</a></li>
<li><a href="/articles/2008/02/2008_hdtv_buyers_guide_part_2.php">2008 HDTV Buyers Guide, Part 2</a></li>
<li><a href="/articles/2008/02/2008_hdtv_buyers_guide_part_3.php">2008 HDTV Buyers Guide, Part 3</a></li>
</ul></div>
<br />
<p>The following topics are covered in this segment:</p> <ul> <li>HDTV as a System, not Just a TV Set</li> <li>Recording and Digital Connections</li> <li>Analyze the Connectivity Issues</li> <li>HD Integrated Tuners</li> <li>Controls, Cables, Screen Shields, <a href="/glossary.php#ISF+%28Imaging+Science+Foundation%29" target="_blank">ISF</a>, Stores, etc</li></ul> <h2>HDTV as a System, not Just a TV Set</h2> <p>While performing the viewing tests decide if your sitting arrangement would need to be movable to adapt to the quality of the viewing material (closer for HDTV, farther back for NTSC), or perhaps it would be better to select a fixed viewing position at a compromise point in between, adequate to the viewing of both.  <p>Viewing distances might look well at the store but might be physically constrained by your actual room dimensions, and your planned sitting arrangements; remember to use your actual room's viewing distance measurements for store tests, and confirm at home. You might conclude that is better to reduce the screen size for all things to fit, or that you should consider a larger screen than you originally thought.  <p>Assuming you will use the HDTV with a multi-channel audio system (otherwise disregard all the statements relevant to this subject), evaluate how moving a couple of feet away from the screen to make a weak image acceptable might affect the audio sweet spot due to standing waves, or the listener distance from the speakers, and room boundaries.  <p>This test most probably would be done separately; the video test at the store to determine viewing distance, and the audio test at home with your own audio equipment using the video distances confirmed at the store. But the result might be very subjective until both video and audio equipment are together in the same room.  <p>You could conclude that might be better to relocate the speakers so the multi-channel sweet spot matches the viewing spot. Or you could start with your preferred sound sweet spot and adapt the screen size of the HDTV so the viewing sweet spot coincides with the sound sweet spot. If that is your case you better know all this in advance, before you commit to a screen size based only on viewing reasons.  <p>If all this gets too complicated and you just want to keep it simple with some surround in the room, you should concentrate on the viewing factors of this article.  <p>While we are on this topic I would like to mention that the use of any TV's small speakers (and small amps within the TV) as alternative for a missing center channel speaker on your surround system is not recommended as a permanent home-theater setup. The dialog and much of the sound of a movie comes from the center channel, estimated in the order of 60% of the movie soundtrack.  <p>When using the TV's small amp/speakers in a home theater their capacity to handle loud passages would be exceeded (and distortion would occur) much earlier than the L/R speakers/amp of the audio system, assuming the audio system is more powerful than the TV audio, as typically is. The effect could be worst if the system does not have a subwoofer to redirect the low frequencies from a small center speaker and surrounds. The distortion on the center channel would affect the clarity of the dialog over loud passages.  <p>Additionally, sounds that are panning side-to-side would have different timbre while switching among speakers (from left to TV center to right) accompanying the video movement in that direction. Voices of people walking side-to-side will change their tone as they enter the TV's center speaker and as they depart from it.  <p>While these are home-theater considerations, if the HDTV is to become a centerpiece for your home theater then the issues would require attention sooner or later. Plan for installing front (L/C/R) speakers with matching timbre and size, and plan for equal amplification for those three.  <p><u></u> <p><u></u> <p><u></u> <h2>Recording and Digital Connections </h2> <p>Decide if HD recording is a feature you would need at all, and if you do, identify if it is for long term archiving or temporary time shifting. HD time shifting recording is possible using HD DVRs (Digital Video Recorder, similar to TiVo) from cable and satellite services.  <p>If you actually need to do archiving in HD resolution, three manufacturers of HD-VHS VCRs (JVC, Marantz, and Mitsubishi) introduced their models several years ago. Although that format has not been as popular than regular VHS, you might still find some of those units in the market.  <p>HD-VHS VCRs record HD tapes from their IEEE 1394 (FireWire) input connection only, which requires the tuning device (integrated HDTV or HD-STB) to have that output as well. Some integrated TV sets have IEEE 1394 connectors but without output capabilities, or they are for video camcorder purposes only.  <p>DirecTV HD-STBs do not have the IEEE 1394 output, Dish Network HD-STBs neither, although the company once had the intention a few years ago to enable that connection on their 921 DVR model, which is already discontinued.  <p>Most prerecorded HD-VHS movies released use D-Theater content protection, and are playable on JVC and Marantz compatible HD-VHS VCRs, but not on the Mitsubishi HD-VHS VCR.  <p>Sony introduced a $3,800 Hi-Def DVD recorder in Japan in April 2003 with a HD satellite tuner, but no Hi-Def DVD standalone recorder has been introduced in the US so far (Dec 2007) other than some prototypes shown at electronics shows.  <p>The only Hi-Def DVD units introduced in the US were blue-laser players, not recorders, and they were introduced in early 2006 (HD DVD by Toshiba, and Blu-ray by Samsung a bit later).  <p>If the archiving is not required in HD quality, perhaps it could be sufficient for now to record HD content down-converted as SD analog on a regular VCR or DVD recorder, but you would notice the difference when comparing the SD recording with the original HD content.<b></b>  <p><b></b> <p><b></b> <p><b></b> <h2>Analyze the Connectivity Issues</h2> <p>Most HDTV sets have analog component video inputs to connect HD-STBs and other devices, but such connection "might" be subjected to copy protection viewing restrictions if the content provider instructs the playing/tuning device to disallow the full HD delivery of the signal over that connection.  <p>DVI, a digital connection for uncompressed HD video (no audio), was adopted to deter unlawful digital copying (by using <a href="/glossary.php#HDCP+%28High-bandwidth+Digital+Content+Protection%29" target="_blank">HDCP</a>, High-Definition-Content-Protection). Shortly after, HDMI was introduced to improve upon DVI, with a smaller connector and adding audio and control signal capabilities over the same single wire. HDMI also uses HDCP for content protection.  <p>Complete coverage of the subject of HDMI is on the series of 10 articles I wrote entitled <a href="/articles/2006/07/hdmi_-_a_digital_interface_solution.php" target="_blank">HDMI - A Digital Interface Solution</a>. <p>Make sure the HDTV (integrated or monitor) has DVI or HDMI inputs to connect HD-STBs and DVD players (regular or Hi-Def) with those outputs, the more inputs the better.  <p>Satellite HD tuners come only as separate HD-STBs, and they use DVI (or HDMI) and component outputs to connect to DTVs. Confirm that the DVI-HDMI connections on any equipment are HDCP compliant; some plasma panels with DVI inputs were designed to connect to PCs, and were not HDCP compliant.  <p>Check that the HD-STB can simultaneously send out the HD and SD signals to the corresponding outputs, to view the HD image while a) recording the down-converted SD version on a regular VCR or b) distributing the SD version to other devices on a home-network of SD quality. Some HD-STBs cannot do this simultaneously.  <p>Further coverage of the content protection and connectivity issues of HDTV can be read in the article I wrote titled <a href="/articles/2006/02/analysis_of_dtv_content_protection_rulings_and_agreements.php " target="_blank">Analysis of DTV Content Protection Rulings and Agreements</a>. <h2>HD Integrated Tuners</h2> <p>As mentioned at the beginning of this article, several years ago the FCC mandated an implementation plan for all manufacturers to gradually integrate HD tuners into digital TVs and digital recording devices. Likewise, a plug-and-play agreement with the cable industry was made (and approved by the FCC) to also include HD-cable tuners within TVs and HD-STBs with a POD (Point Of Deployment) card named "CableCARD".  <p>The CableCARD was implemented with unidirectional capabilities only. Bi-directional cable features of VOD, Impulse PPV, and cable-supplied programming guide would still require the leasing of a separate cable HD-STB, duplicating the cable tuning cost for a consumer that has purchased a cable-ready DTV.  <p>Since 70% of the U.S. population subscribes to cable, some people believe that integrated HDTVs with CableCARD tuners were practical and would accelerate DTV adoption. I believe that such acceleration comes with a high cost to consumers that do not need them, and they should have been offered a choice.  <p>Cable-subscribers would be paying extra for an integrated TV that has an over-the-air tuner that would not actually be used, and vice versa. Neither person would have the choice to just pay for the tuner they need (or for no tuner at all) if integrated within all sets.  <p>When the FCC issued the mandate in 2002, tuners were very high in cost ($400/$1000). Integrated TVs had an average extra MSRP of $704 above the price of equivalent monitor versions; that was the cost of having a digital tuner into a TV at that time.  <p>Now certainly the cost is lower but there still an extra cost for most consumers that do not actually use the tuners if subscribed to satellite or bi-directional cable.  <p>Details about the subject can be read on this article I wrote when the mandate was issued: <a href="/articles/2006/01/hdtv_integrated_tuners_and_you.php " target="_blank">HDTV Integrated Tuners and You</a>. <p>Since the beginning of HDTV many early adopters have criticized the operational problems and reliability of HD tuners.  <p>Having immature expensive tuners failing within a 300-pound HDTV set would require a costly in-home service when out of warranty. Having them within a separate STB facilitates their service, replacement, or flexible upgrade without having to open an HDTV, and cable subscribers would solve the problem by having the cable company switching the STB at no cost to the subscriber.  <p>Even today, most satellite and cable HD-STBs are plagued with performance and operational problems that in many cases require periodic exchanges, and firmware upgrades are not sufficient to correct some design problems.  <p>The theory of integration has merit, but careful consideration should have been given to the timing and the cost to consumers. The integration of components that are not mature enough and the price of tuners not yet reaching economies of scale, should have been both carefully analyzed before any mandate, and postpone until it makes more sense to consumers.  <h2>Controls, Cables, Screen Shields, ISF, Stores, etc</h2> <p>Most OTA HD reception is UHF, if you are to receive HDTV via terrestrial OTA, plan for having one antenna installed; even on the attic an antenna could receive good HDTV signals, and OTA is free.  <p>Budget for good quality HD video wiring, especially if long runs are required for a DVI or HDMI cable at 1080p resolution and if it will be installed within walls or ceilings. Drywall repair would cost much more for a cable replacement caused by sub-par cabling performance problems or to upgrade to a higher HDMI cable category to been able to handle higher-level video equipment as you upgrade it.  <p>Think about children around delicate screens lacking shield protection. Some sets have non-removable shields that reflect back room light that you might not notice at the store but you would object at your bright apartment at the beach.  <p>Some plasma panels might reflect back to the viewer ambient light from bright rooms with uncovered windows. Some LCD panels that might be great for those lighted environments might offer inferior video quality and unacceptable motion blur to certain viewers.  <p>Sometimes replacing a regular TV with an HDTV/home theater set-up becomes a nuisance for some family members due to the complexity of the system controls; in such case consider a simpler additional parallel wiring for just the HDTV/STB/DVD for stand-alone use, and budget for it.  <p>TV manufacturers are still delivering HDTVs with their image settings at exaggerated levels to attract the attention of consumers when the sets are viewed under the fluorescent lighting of typical retail showrooms, but at home those image settings would be unacceptable. Once the set has been used for at least 200 hours, consider performing a calibration with one of the calibration Hi-Def DVDs (or regular DVD if you do not have a Hi-Def DVD player) to establish the correct TV settings for your particular environment.  <p>Many videophiles prefer to perform an ISF (Imaging Science Foundation) calibration done by a trained technician using specialized calibration equipment. While most TVs improve after an ISF calibration, the cost of several hundred dollars depending on the number of inputs and resolutions you calibrate, could be relatively expensive for the price of your particular set, and your budget. Check your manufacturer's warranty about ISF, they might object the access and use of the TV's service menu by unauthorized service technicians and the warranty can be voided.  <p>Additionally, it is not uncommon that after the set is ISF calibrated the untrained eye of a regular viewer feels that the image has lost its "pop" compared to before calibration, so the do-it-yourself calibration DVD alternative above might be a better start to keep cost down initially, and decide for ISF later.  <p>Check for store policies regarding delivery, installation, extended warranties, and problem resolution policies, which are usually better coming from a reputable A/V store that could protect you when a heavy or expensive HDTV has problems, particularly in the case of the delivery and installation of delicate plasma panels.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>February 13, 2008  9:35 AM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(1239)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 1239)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2008/02/2008-hdtv-buyers-guide-part-4.php" type="text/javascript" charset="utf-8"></script>
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
