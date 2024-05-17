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
	$res_author = mQuery("SELECT title, channel, amazon_tracking_id, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Lee Wood'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Lee Wood" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 142 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 142
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=More News July 7, 2005 From Lee Wood&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/news/2005/07/more-news-july-7-2005-from-lee-wood.php&amp;title=More News July 7, 2005 From Lee Wood">
		<span style="display:none">DTV Station Status Per FCC CDBS - July 5, 2005(TV Technology) http://www.tvtechnology.com/dlrf/one.php?id=928 Senate Skeds 2 DTV Hearings [Paid Subscription Required] The Senate Commerce Committee will hold not one but two full-committee hearings on the DTV transition July 12 -- at...</span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 142";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download More News July 7, 2005 From Lee Wood" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="More News July 7, 2005 From Lee Wood" />
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
	<title>HDTV Magazine - More News July 7, 2005 From Lee Wood</title>
	<meta name="keywords" content="engineering broadcastengineering, broadcast engineering, broadcastengineering newsletters, technology tvtechnology, tvtechnology dlrf, news, digital, broadcast, dtv, engineering, new, article, technology, broadcastengineering, newsletters, display, television, cable, july, tech, top, set, consumer, tvtechnology, channel" />
	<meta name="description" content="DTV Station Status Per FCC CDBS - July 5, 2005(TV Technology) http://www.tvtechnology.com/dlrf/one.php?id=928 Senate Skeds 2 DTV Hearings [Paid Subscription Required] The Senate Commerce Committee will hold not one but two full-committee hearings on the DTV transition July 12 -- at..." />
	<meta name="title" content="More News July 7, 2005 From Lee Wood" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/news/2005/07/more-news-july-7-2005-from-lee-wood.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=142', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2005/07/more-news-july-7-2005-from-lee-wood.php">More News July 7, 2005 From Lee Wood</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Lee Wood</b> on <b>July  7, 2005</b>
							</td><td id="article_category">
								Categories: 
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
				<p><strong>DTV Station Status Per FCC CDBS - July 5, 2005</strong>(TV Technology)<br />
<a href="http://www.tvtechnology.com/dlrf/one.php?id=928">http://www.tvtechnology.com/dlrf/one.php?id=928</a> </p>

<p><strong>Senate Skeds 2 DTV Hearings  </strong>[Paid Subscription Required]<br />
The Senate Commerce Committee will hold not one but two full-committee hearings on the DTV transition July 12 -- at 10 a.m. and 2:30 p.m in room 253 of the Russell Building, for those marking their calendars.<br />
(Broadcasting & Cable)<br />
<a href="http://www.broadcastingcable.com/article/CA623242?display=Breaking+News">http://www.broadcastingcable.com/article/CA623242?display=Breaking+News</a> </p>

<p><strong>Commission to Revisit Must Carry</strong>(TV Technology)<br />
<a href="http://www.tvtechnology.com/dailynews/one.php?id=3070">http://www.tvtechnology.com/dailynews/one.php?id=3070</a> </p>

<p><strong>Martin Backs Into Dual 'Must'  </strong>[Paid Subscription Required]<br />
Plan Would Allow B'casters to Opt for Retrans Consent on a Second Signal<br />
(Multichannel News)<br />
<a href="http://www.multichannel.com/article/CA623069.html?display=Top+Stories">http://www.multichannel.com/article/CA623069.html?display=Top+Stories</a> <br />
 <br />
<strong>BIA Financial analyzes DTV channel election aftermath</strong>A review of the first round DTV channel elections shows that 246 stations have not received a tentative post-transition DTV channel assignment. <br />
(Broadcast Engineering)<br />
<a href="http://broadcastengineering.com/newsletters/hd_tech/20050706/#bia">http://broadcastengineering.com/newsletters/hd_tech/20050706/#bia</a><br />
 <br />
<strong>DTV legislation has 80 percent chance of passage by Congress</strong>Congress appears committed to passing legislation by the end of the year to set a date for analog broadcasts to cease. <br />
(Broadcast Engineering)<br />
<a href="http://broadcastengineering.com/newsletters/bth/20050704/#congress">http://broadcastengineering.com/newsletters/bth/20050704/#congress</a> </p>

<p><strong>Broadcast flag dispute continues</strong>An amendment authorizing federal regulators to mandate the broadcast flag has not been proposed, as many thought it might, leaving the issue to hang in the breeze. <br />
(Broadcast Engineering)<br />
<a href="http://www.broadcastengineering.com/404/#flag">http://www.broadcastengineering.com/404/#flag</a> </p>

<p><strong>Surviving the Digital TV Shift </strong>Soon, TV stations will give up their old analog licenses and broadcast solely in digital format. This means older TV sets will no longer receive broadcast signals. Here's how you can prepare for the big changeover.<br />
(Wired News)<br />
<a href="http://www.wired.com/news/digiwood/0,1412,68091,00.html?tw=rss.TEK">http://www.wired.com/news/digiwood/0,1412,68091,00.html?tw=rss.TEK</a> </p>

<p><strong>Winegard Exec: Education the Key to Successful DTV Transition</strong>(TV Technology)<br />
<a href="http://www.tvtechnology.com/dlrf/one.php?id=927">http://www.tvtechnology.com/dlrf/one.php?id=927</a> </p>

<p><strong>Defining Visions: Consumer Beware</strong>  [Joel Brinkley]<br />
(Ultimate AV)<br />
<a href="http://www.guidetohometheater.com/joelbrinkley/705jb/">http://www.guidetohometheater.com/joelbrinkley/705jb/</a> </p>

<p><strong>Consumer Group Refutes CEA Findings on OTA TV Viewing</strong>(TV Technology)<br />
<a href="http://www.tvtechnology.com/dlrf/one.php?id=921">http://www.tvtechnology.com/dlrf/one.php?id=921</a> </p>

<p><strong>Study says 80 million OTA TVs still in use</strong><br />
Results of a survey conducted by Consumers Union and the Consumer Federation of America indicate 15 percent of TVs are used exclusively to receive OTA television transmissions. <br />
(Broadcast Engineering)<br />
<a href="http://broadcastengineering.com/newsletters/hd_tech/20050706/#">http://broadcastengineering.com/newsletters/hd_tech/20050706/#</a> </p>

<p><strong>HD capabilities desired by majority of TV buyers</strong>, says survey<br />
The finding is part of a new report that explores consumer attitudes about different aspects of TV installation and usages. <br />
(Broadcast Engineering)<br />
<a href="http://broadcastengineering.com/newsletters/hd_tech/20050706/#buyers">http://broadcastengineering.com/newsletters/hd_tech/20050706/#buyers</a><br />
 <br />
<strong>July 1 DTV Update</strong>(Digital Television)<br />
<a href="http://digitaltelevision.com/articles/article_969.shtml">http://digitaltelevision.com/articles/article_969.shtml</a><br />
 <br />
<strong>ATSC Tuner Mandate Takes Effect</strong><br />
(TV Technology)<br />
<a href="http://">http://</a>http://www.tvtechnology.com/dlrf/one.php?id=922<br />
 <br />
<strong>20050627 Mark's Monday Memo</strong>(Digital Television)<br />
<a href="http://www.digitaltelevision.com/mondaymemo/mlist/frm02189.html">http://www.digitaltelevision.com/mondaymemo/mlist/frm02189.html</a> </p>

<p><strong>WGNM-TV expands service with new digital transmitter</strong>WGNM-TV announces the launch of its new digital transmitter on Channel 45.<br />
(Macon, GA Telegraph)<br />
<a href="http://www.macon.com/mld/macon/business/12038166.htm">http://www.macon.com/mld/macon/business/12038166.htm</a> </p>

<p><strong>FCC Sets Set-Top Check-Up Deadline  </strong>[Paid Subscription Required]<br />
The top six cable operators and the leading cable industry and consumer electronics trade groups must begin filing status reports with the FCC on Oct. 1 detailing their progress toward a new deadline for eliminating cable set-top boxes that combine anti-theft security with channel surfing and interactive functions.<br />
(Broadcasting & Cable)<br />
<a href="http://www.broadcastingcable.com/article/CA623348?display=Breaking+News">http://www.broadcastingcable.com/article/CA623348?display=Breaking+News</a> </p>

<p><strong>Charter, Advance/Newhouse Sue FCC</strong>Charter Communications and Advance/Newhouse Communications went to federal court Tuesday to overturn set-top-box rules adopted in March by the FCC.<br />
(Multichannel News)<br />
<a href="http://www.multichannel.com/article/CA623832.html?display=Breaking+News&referral=SUPP">http://www.multichannel.com/article/CA623832.html?display=Breaking+News&referral=SUPP</a> </p>

<p><strong>High Def-inn-ition  </strong>[Paid Subscription Required]<br />
The new luxury hotel amenity: HD on a flat-screen TV<br />
(Broadcasting & Cable)<br />
<a href="http://www.broadcastingcable.com/article/CA623030.html?display=Technology">http://www.broadcastingcable.com/article/CA623030.html?display=Technology</a></p>

<p><strong>Moore's Law Meets HDTV </strong>(Display Technology Investor via Forbes)<br />
<a href="http://www.forbes.com/investmentnewsletters/2005/07/01/sony-brillian-hitachi-lcos-spatialight-cx_dm_0701soapbox_inl.html?partner=rss">http://www.forbes.com/investmentnewsletters/2005/07/01/sony-brillian-hitachi-lcos-spatialight-cx_dm_0701soapbox_inl.html?partner=rss</a> </p>

<p><strong>Digital Terrestrial TV Set Tops Ready For Blast Off</strong>(In-Stat)<br />
<a href="http://www.in-stat.com/press.asp?ID=1390&sku=IN0501846ME">http://www.in-stat.com/press.asp?ID=1390&sku=IN0501846ME</a> <br />
 <br />
<strong>NAB, MSTV issue converter box RFQ</strong>The associations have issued a request for quote on a prototype digital-to-analog converter box. <br />
(Broadcast Engineering)<br />
<a href="http://broadcastengineering.com/newsletters/hd_tech/20050706/#rfq">http://broadcastengineering.com/newsletters/hd_tech/20050706/#rfq</a> </p>

<p><strong>MSTV, NAB seek proposals for DTV-to-NTSC converter box</strong>The consumer device would enable DTV viewing on analog receivers. <br />
(Broadcast Engineering)<br />
<a href="http://broadcastengineering.com/newsletters/t2d/20050705/#mstv">http://broadcastengineering.com/newsletters/t2d/20050705/#mstv</a> </p>

<p><strong>Emerging force in large LCD televisions </strong>FOR years, China has been one of the world's largest manufacturers of electrical and electronic goods. For the longest time, however, people have shun away from buying made-in-China products unless they are under the guise of and guaranteed by leading international brands.<br />
(New Straits Times)<br />
<a href="http://www.nst.com.my/Current_News/NST/Wednesday/Features/20050705150929/Article/indexb_html">http://www.nst.com.my/Current_News/NST/Wednesday/Features/20050705150929/Article/indexb_html</a> </p>

<p><strong>Help, I Need a New HDTV! (Part 1 of 5)</strong>  [Includes Links to All 5 Parts]<br />
(Architechtronics via Self SEO)<br />
<strong>http://www.selfseo.com/story-3400.php</strong> </p>

<p><strong>Hitachi 50VX915 Directors Series LCD RPTV</strong><br />
(Ultimate AV)<br />
<a href="http://www.guidetohometheater.com/directviewandptvtelevisions/705hitachi/">http://www.guidetohometheater.com/directviewandptvtelevisions/705hitachi/</a> </p>

<p><strong>Mitsubishi Wins the Race</strong>The Japanese giant is the first to bring a 1080p DLP TV to market.<br />
(Ultimate AV)<br />
<a href="http://www.guidetohometheater.com/news/070105Mitsubishi/">http://www.guidetohometheater.com/news/070105Mitsubishi/</a> <br />
 <br />
<strong>NEC MultiSync LCD2335WXM</strong>The NEC MultiSync LCD2335WXM is an admirable PC display that falls short as an HDTV.<br />
(PC Magazine)<br />
<a href="http://www.pcmag.com/article2/0,1759,1833400,00.asp?kc=PCRSS02129TX1K0000530">http://www.pcmag.com/article2/0,1759,1833400,00.asp?kc=PCRSS02129TX1K0000530</a><br />
 <br />
<strong>Pioneering</strong>Pioneer launches their sixth generation of plasma displays and two intriguing new AV receivers.<br />
(Ultimate AV)<br />
<a href="http://www.guidetohometheater.com/news/070405Pioneer/">http://www.guidetohometheater.com/news/070405Pioneer/</a> </p>

<p><strong>Yesterday"s Technology Provides a Clear Solution to a Modern Problem</strong><br />
Just a few short years ago, you would have been hard-pressed to find a rooftop television antenna on an afternoon drive through Middle America. But with the growing popularity of high-definition television (HDTV), antennas are making a comeback in homes across the country.<br />
(PRWeb via Yahoo News)<br />
<a href="http://news.yahoo.com/news?tmpl=story&u=/prweb/20050703/bs_prweb/prweb257605_1">http://news.yahoo.com/news?tmpl=story&u=/prweb/20050703/bs_prweb/prweb257605_1</a><br />
 <br />
<strong>MPEG-4 AVC H.264 to usher in new HD opportunities</strong><br />
Emerging products of tomorrow will be enabled by MPEG-4 AVC H.264 -- a technology that one industry expert says will drive the industry on the road to the future. <br />
(Broadcast Engineering)<br />
<a href="http://broadcastengineering.com/newsletters/hd_tech/20050706/#oppt">http://broadcastengineering.com/newsletters/hd_tech/20050706/#oppt</a> <br />
 <br />
<strong>DVB-T/USB chip drives digital TV reception for PCs</strong>(Electronic Engineering Times-Asia)<br />
<a href="http://www.eetasia.com/ART_8800370566_499489_896c0835_no.HTM">http://www.eetasia.com/ART_8800370566_499489_896c0835_no.HTM</a> </p>

<p><strong>3G Americas publishes Mobile TV paper</strong>(3G Newsroom)<br />
<a href="http://www.3gnewsroom.com/3g_news/jul_05/news_6031.shtml">http://www.3gnewsroom.com/3g_news/jul_05/news_6031.shtml</a> </p>

<p><strong>Crown Castle fixing to build a nationwide digital TV network for cellphones</strong>(Engadget)<br />
<a href="http://www.engadget.com/entry/1234000817049511/">http://www.engadget.com/entry/1234000817049511/</a> </p>

<p><strong>Abertis Telecom, Nokia and Telefonica Moviles to Start First Digital Mobile TV (DVB-H) Pilot in Spain </strong> [Spain]<br />
Live Broadcast Programs Will be Provided by Antena 3, Sogecable, Telemadrid, Telecinco, TVE and TV3<br />
(PR Newswire)<br />
<a href="http://www.prnewswire.com/cgi-bin/stories.pl?ACCT=104&STORY=/www/story/07-04-2005/0004051687&EDATE=">http://www.prnewswire.com/cgi-bin/stories.pl?ACCT=104&STORY=/www/story/07-04-2005/0004051687&EDATE=</a> </p>

<p><strong>Shaw Boosts its High Definition (HDTV) Lineup  [Canada]</strong><br />
(Digital Home)<br />
<a href="http://digitalhomecanada.com/index.php?option=com_content&task=view&id=475&Itemid=51">http://digitalhomecanada.com/index.php?option=com_content&task=view&id=475&Itemid=51</a> </p>

<p><strong> IT, HD drive growth in European broadcast market</strong>, says report<br />
Over the next five years, European broadcasters will begin HD transmission and require the hardware and software to make the transition, according to a new report. <br />
(Broadcast Engineering)<br />
<a href="http://broadcastengineering.com/newsletters/hd_tech/20050706/#europe">http://broadcastengineering.com/newsletters/hd_tech/20050706/#europe</a></p>

<p><strong>Who'll be the big winner in 2012? </strong> [UK]<br />
Digital TV providers face a race against time to sign up subscribers ahead of the analogue switch off.<br />
(Guardian Unlimited)<br />
<a href="http://www.guardian.co.uk/online/news/0,12597,1520972,00.html">http://www.guardian.co.uk/online/news/0,12597,1520972,00.html</a> </p>

<p><strong>Freeview tipped to top Sky at shutdown  </strong>[UK]<br />
(Digital Spy)<br />
<a href="http://www.digitalspy.co.uk/article/ds22318.html">http://www.digitalspy.co.uk/article/ds22318.html</a> </p>

<p><strong>Industry puts Freeview in pole position  </strong>[UK]<br />
(Digital TV Group)<br />
<a href="http://griffin.dtg.org.uk/news/news.php?class=countries&subclass=193&id=986">http://griffin.dtg.org.uk/news/news.php?class=countries&subclass=193&id=986</a> <br />
 <br />
<strong>Sky's HD service to include Premiership games  </strong>[UK]<br />
(Digital TV Group)<br />
<a href="http://griffin.dtg.org.uk/news/news.php?">http://griffin.dtg.org.uk/news/news.php?</a>class=countries&subclass=193&id=988<br />
 <br />
<strong>French Deputy supports EuroNews DTT candidacy  </strong>[France]<br />
(Advanced Television)<br />
<a href="http://www.advanced-television.com/2005/news_archive_2005/July4_July8.htm#frenchd">http://www.advanced-television.com/2005/news_archive_2005/July4_July8.htm#frenchd</a> <br />
 <br />
<strong>TV on PC</strong>  [Australia]<br />
Can your computer tune in digital shows, record them or pause live video?<br />
(Sydney Morning Herald)<br />
<a href="http://www.smh.com.au/news/icon/tv-on-pc/2005/06/30/1119724747965.html?oneclick=true">http://www.smh.com.au/news/icon/tv-on-pc/2005/06/30/1119724747965.html?oneclick=true</a> </p>

<p><strong>Setting up for set-tops  </strong>[Australia]<br />
(PC World)<br />
<a href="http://www.pcworld.idg.com.au/index.php/id;1202732134;fp;16;fpid;0">http://www.pcworld.idg.com.au/index.php/id;1202732134;fp;16;fpid;0</a> </p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Lee Wood</b>, <b>July  7, 2005  8:42 AM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(142)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Lee Wood', 142)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author['bio_short'] != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Lee Wood</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2005/07/more-news-july-7-2005-from-lee-wood.php" type="text/javascript" charset="utf-8"></script>
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
