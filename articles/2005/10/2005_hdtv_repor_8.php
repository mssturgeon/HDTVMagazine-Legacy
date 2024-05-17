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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 223 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 223
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=2005 HDTV Report, Part 9: LCD TV Panels&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/articles/2005/10/2005-hdtv-report-part-9-lcd-tv-panels.php&amp;title=2005 HDTV Report, Part 9: LCD TV Panels">
		<span style="display:none">This part 9 is dedicated to LCD panels, they are becoming larger and larger from one year to another; last year's 40-inch screens introduction seemed a big step forward, a step into the domain of plasmas; this year the competition for even larger panels is heating up.  The plasmas and LCD panels announced at CES 2005 overlap in the 37 to mid-50-inches range; the new large LCD TV panels generally cost more than similar size plasmas, twice as much in many cases, but prices are coming down fast for both types of panels.  The larger LCD TV panels are now at 1920x1080 resolution on the 45+ sizes, while in plasmas the 1080p resolution is seen on much larger sizes.  One example of large LCD TV panel is the Sharp's 1080p 45&quot; LCD TV panel (AQUOS models 45GD4U and 45GD6U) available since fall of 2004 at an original price of $10,000 MSRP (but seen at less than half of that on the street by mid year 2005). The first large LCD panels were shown last year at CES 2004, and by year-end 1.4 million LCD panels were sold; 2004 was a year of market attraction for panels over other
technologies.  LCD-TV Panels will be coming in 57&quot; and even 65”, a 57” is expected from Samsung, TTM Jun 05 ($16000), 1920x1080p, integrated with ATSC/QAM CableCARD tuners, response time faster than 8 ms, an improvement from the 12 ms of their earlier 46&quot; model 468W, not to mention the comparable improvement over the 20ms+ of many other LCD products.  Also shown at CES was the Sharp AQUOS 65&quot; LCD-TV panel, TTM 2H05, $TBA, 1920x1080p, integrated ATSC/QAM CableCARD tuners, Quick Shoot video circuitry for 12 milliseconds response time, HDMI, IEEE1394, and DVI-I.  When reviewing the MSRP and TTM (time to market) information keep in mind that is as of 1Q05 for the CES 2005 report and, as is usual with CE, prices and availability might change when products are actually released; additionally, street sale prices should be expected to be lower than the MSRP data used throughout the report.</span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 223";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download 2005 HDTV Report, Part 9: LCD TV Panels" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="2005 HDTV Report, Part 9: LCD TV Panels" />
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
	<title>HDTV Magazine - 2005 HDTV Report, Part 9: LCD TV Panels</title>
	<meta name="keywords" content="atsc qam, ttm mar, response time, ttm jun, tba ttm, ttm, lcd, ntsc, dvi, atsc, tuners, qam, brightness, ces, hdmi, integrated, panel, mar, jun, tba, monitor, models, response, time, may" />
	<meta name="description" content="This part 9 is dedicated to LCD panels, they are becoming larger and larger from one year to another; last year's 40-inch screens introduction seemed a big step forward, a step into the domain of plasmas; this year the competition for even larger panels is heating up.  The plasmas and LCD panels announced at CES 2005 overlap in the 37 to mid-50-inches range; the new large LCD TV panels generally cost more than similar size plasmas, twice as much in many cases, but prices are coming down fast for both types of panels.  The larger LCD TV panels are now at 1920x1080 resolution on the 45+ sizes, while in plasmas the 1080p resolution is seen on much larger sizes.  One example of large LCD TV panel is the Sharp's 1080p 45&quot; LCD TV panel (AQUOS models 45GD4U and 45GD6U) available since fall of 2004 at an original price of $10,000 MSRP (but seen at less than half of that on the street by mid year 2005). The first large LCD panels were shown last year at CES 2004, and by year-end 1.4 million LCD panels were sold; 2004 was a year of market attraction for panels over other
technologies.  LCD-TV Panels will be coming in 57&quot; and even 65”, a 57” is expected from Samsung, TTM Jun 05 ($16000), 1920x1080p, integrated with ATSC/QAM CableCARD tuners, response time faster than 8 ms, an improvement from the 12 ms of their earlier 46&quot; model 468W, not to mention the comparable improvement over the 20ms+ of many other LCD products.  Also shown at CES was the Sharp AQUOS 65&quot; LCD-TV panel, TTM 2H05, $TBA, 1920x1080p, integrated ATSC/QAM CableCARD tuners, Quick Shoot video circuitry for 12 milliseconds response time, HDMI, IEEE1394, and DVI-I.  When reviewing the MSRP and TTM (time to market) information keep in mind that is as of 1Q05 for the CES 2005 report and, as is usual with CE, prices and availability might change when products are actually released; additionally, street sale prices should be expected to be lower than the MSRP data used throughout the report." />
	<meta name="title" content="2005 HDTV Report, Part 9: LCD TV Panels" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/articles/2005/10/2005-hdtv-report-part-9-lcd-tv-panels.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=223', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2005/10/2005-hdtv-report-part-9-lcd-tv-panels.php">2005 HDTV Report, Part 9: LCD TV Panels</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>October 18, 2005</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=10&category=New Products & Equipment">New Products & Equipment</a></b>
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
				<blockquote>This is the next in a series of articles taken from the <b>H/DTV Technology Review & CES 2005 Report</b> by Rodolfo La Maestra, published in March 2005. If you are interested in downloading the full version of this report, it is currently available for purchase from our <a href="/store/ces-2005.php">CES Report</a> page.</blockquote>

<p>Manufacturers started to compete in the oversized LCD-TV panels market (40" to 65"), as follows:</p>

<table cellpadding="5">
	<tr>
		<td>
			BenQ
		</td><td>
			46"
		</td><td>
			DV4680
		</td><td>
			$10000, TTM Mar 05, 8ms, ATSC/NTSC tuners
		</td>
	</tr><tr>
		<td>
			Infocus
		</td><td>
			40"
		</td><td>
			TD-40
		</td><td>
			$7000, TTM now, 1280x768, 600:1 CR, NTSC
		</td>
	</tr><tr>
		<td>
			JVC
		</td><td>
			40"
		</td><td>
			LT-40X776
		</td><td>
			TTM Jun 05, 1366x768, QAM CableCARD, 2HDMI
		</td>
	</tr><tr>
		<td>&nbsp;</td>
		<td>
			40"
		</td><td>
			LT-40FH96
		</td><td>
			TTM fall 05, 1080p, ATSC/QAM CableCARD, HDMI
		</td>
	</tr><tr>
		<td>
			LG
		</td><td>
			42"
		</td><td>
			L42000AT
		</td><td>
			Integrated; & DU-42LZ30 $8000 also integrated
		</td>
	</tr><tr>
		<td>&nbsp;</td>
		<td>
			55"
		</td><td>
			RU-55LP10
		</td><td>
			$TBA, TTM May 05, 1080p, ATSC/QAM/NTSC 
		</td>
	</tr><tr>
		<td>
			LG/Philips
		</td><td>
			42"
		</td><td>
			LC420W02
		</td><td>
			1366x768, 1200:1 CR, 600cd/ brightness
		</td>
	</tr><tr>
		<td>&nbsp;</td>
		<td>
			47"
		</td><td>
			LC470W01
		</td><td>
			1920x1080, 1200:1 CR
		</td>
	</tr><tr>
		<td>&nbsp;</td>
		<td>
			55"
		</td><td>
			LC550W01
		</td><td>
			1920x1080, 1200:1 CR
		</td>
	</tr><tr>
		<td>
			Luce/Epoq
		</td><td>
			40"
		</td><td>
			HTV-4062
		</td><td>
			$9000, TTM May 04, dual NTSC tuners
		</td>
	</tr><tr>
		<td>
			Philips
		</td><td>
			42"
		</td><td>
			42PF9830
		</td><td>
			TTM 2005, Ambilight 2 Series integr. ATSC/QAM
		</td>
	</tr><tr>
		<td>&nbsp;</td>
		<td>
			42"
		</td><td>
			42PF9996
		</td><td>
			$11000, monitor
		</td>
	</tr><tr>
		<td>&nbsp;</td>
		<td>
			50"
		</td><td>
			50PF9966
		</td><td>
			1000:1 CR, 900cd bright, 1366x768, NTSC, HDMI
		</td>
	</tr><tr>
		<td>
			Samsung
		</td><td>
			57"
		</td><td>
			LNR570D
		</td><td>
			$16000, TTM Jun 05, 8 ms, ATSC/QAM Cab.CARD 
		</td>
	</tr><tr>
		<td>&nbsp;</td>
		<td>
			46"
		</td><td>
			LNR460D
		</td><td>
			$9000, TTM Mar 05, LED backlight, ATSC/QAM 
		</td>
	</tr><tr>
		<td>&nbsp;</td>
		<td>
			40"
		</td><td>
			LNR409D
		</td><td>
			$5000, TTM Mar/May 05, 1080p, 3.2 billion colors
		</td>
	</tr><tr>
		<td>
			SharpAQUOS
		</td><td>
			45"
		</td><td>
			LC-45GD6U
		</td><td>
			$10000, TTM Aug 04, 1920x1080p, 12 ms 
		</td>
	</tr><tr>
		<td>&nbsp;</td>
		<td>
			65"
		</td><td>
			Model #N/A
		</td><td>
			$TBA, TTM 2H05, ATSC/QAM CableCARD, 12 ms
		</td>
	</tr><tr>
		<td>
			Sim2
		</td><td>
			46"
		</td><td>
			Model # N/A
		</td><td>
			$TBA, TTM 2005, now they have the 40"
		</td>
	</tr><tr>
		<td>
			Sony
		</td><td>
			46"
		</td><td>
			KDX-46Q005
		</td><td>
			$10000-$12000, TTM end 2004, 1920x1080p
		</td>
	</tr><tr>
		<td>
			Viewsonic
		</td><td>
			40"
		</td><td>
			N4050w
		</td><td>
			$5000, 1280x768, DVI-D, 600:1 CR, 450 nits
		</td>
	</tr><tr>
		<td>
			Westinghouse
		</td><td>
			40"
		</td><td>
			Model #N/A
		</td><td>
			$2500, 1280x768, integrated
		</td>
	</tr><tr>
		<td>&nbsp;</td>
		<td>
			47"
		</td><td>
			Model #N/A
		</td><td>
			$TBA, TTM Mar 05, art frames, 1920x1080p
		</td>
	</tr>
</table>

<p><br />
<h2>Audiovox</h2><br />
Mar 04<br />
New LCD panel was introduced<br />
30"	AR3000	$3500, TTM 2Q04, 1280x768, one NTSC tuner, component</p>

<p><br />
<h2>BenQ</h2><br />
<u>CES 2005</u><br />
46"	DV4680	$10000, TTM Mar 05, 1920x1080p, 600 cd/m2 brightness, 800:1 CR, 8ms response time, 170 degrees of horizontal and vertical viewing, ATSC/NTSC tuners, Faroudja True Life video enhancement, HD component, DVI/HDCP</p>

<p>Other large models will be released</p>

<p><br />
<h2>Hitachi</h2><br />
June 2004 (company announcement of 2004/5 models)</p>

<p><u>LCD Flat-panel TV Direct-view</u><br />
32" 	32HDL51	$TBA, TTM 4Q04, 768p, two IEEE-1394, two HDMI</p>

<p><br />
<h2>HP</h2><br />
Sep 04<br />
30"	LC3040N	$3000, 1280x768, 16:9 AR, DCDi, Visual Fidelity System (VFS), dual NTSC tuners, DVI-D/HDCP, component, piano black finish, included matching table, optional wall mounting bracket</p>

<p>Also LC2640N 26 " version for $2700</p>

<p><br />
<h2>Infocus</h2><br />
Oct 04<br />
30"	TD-30<br />
40"	TD-40		$7000, TTM now, 1280x768, 600:1 CR, NTSC/SECAM tuners, DVI-D, RGB 15 pin  </p>

<p><br />
<h2>JVC</h2><br />
<u>CES 2005</u><br />
40"	LT-40X776	TTM Jun 05, ATSC/QAM CableCARD tuners, 2 HDMI & IEEE1394<br />
40"	40FH96	TTM fall 05, 1080p, dual HDMI, Integrated w/QAM Cable tuner, memory card slot, dual IEEE1394</p>

<p>Other smaller LCD-TVs were announced from 17" to 32" with HDMI, 720-770P resolution</p>

<p><br />
<h2>LG</h2><br />
Oct 04<br />
42"	L42000AT 	integrated<br />
<u>LCD TVs integrated no-CableCARD</u><br />
42"	DU-42LZ30 $8000<br />
37"<br />
30"</p>

<p><u>CES 2005</u><br />
23"	RU-23LG10P	TTM 2Q05, $ TBA, built-in progressive scan DVD player</p>

<p><img src="/articles/images/mt/image068.jpg" alt="LG 55 inch RU-55LP10" align="right"><br />
55"	RU-55LP10	TTM May 05, $ TBA, 1080p, integrated ATSC/NTSC/QAM tuners, 550:1 CR, 500cd/m2 brightness, viewing angle 178 degrees, XD engine, 15watts channel audio output<br />
<br clear=all></p>

<h2>LG/Philips</h2>
<u>CES 2005</u>
Widescreen LCD panels using Super-IPS technology to improve viewing angle (178/178), high color saturation and fast response time, 600 cd/m2 brightness 
23"	LC230W02	1366x768
26'	LC260W01/2	1280x768 in 15:9 (1) and 1366x768 in 16:9 (2)
32"	LC320W01	1366x768 WXGA, 1200:1 CR
37"	LC370W01	1366x768 WXGA, 1200:1 CR 
42"	LC420W02	1366x768 WXGA, 1200:1 CR
47"	LC470W01	1920x1080 WUXGA, 1200:1 CR
55"	LC550W01	1920x1080 WUXGA, 1200:1 CR

<p><br />
<h2>Luce/Epoq</h2><br />
Apr 04<br />
Confirmed models below on Sep 04 at CEDIA under Titan Global Commerce banner<br />
Two 30" 1280x768 $4300 and $4500, NTSC tuners, DCDi<br />
30"	HTV-30A1	$4500, TTM current, 1280x768, 450 cd/m2 brightness, 400:1 CR, component, VGA<br />
40"	HTV-40A2	$9,000, TTM May 04, dual NTSC tuners</p>

<p><br />
<h2>Mitsubishi</h2><br />
Apr 04 (company announcement of 2004/5 models)</p>

<p><u>New Lines (five models) of LCD Flat-panel TVs</u><br />
<u>40 Series</u><br />
1280x768, PC compatibility, upgradeable, AMVP<br />
22"	LT-2240	$3000, TTM Jun 04<br />
30"	LT-3040	$5000, TTM Apr 04</p>

<p><u>Medallion LCD Flat-panel Monitor</u><br />
1280x768, TTM May 04, black bezel cosmetics, Color View picture control<br />
<img src="/articles/images/mt/image069.jpg" alt="Medallion LCD Flat-panel Monitor"><br />
30"	LT-3050	$5300</p>

<p><br />
<h2>Moxell Technology</h2><br />
Oct 04<br />
Seven panels ranging from 14" to 42"<br />
30"	HX-303	$2500, HD panel, 1280x768, DVI/HDCP<br />
42"	MH-422SU	$2500, EDTV 852x480, 1000:1 CR, DVI/HDCP, dual NTSC tuners</p>

<p><u>CES 2005</u><br />
<u>MAG Innovision</u><br />
20"	HD-202AT	$650, TTM now, EDTV monitor</p>

<p><u>Proview line</u><br />
14", 17", and 20" smaller sizes<br />
26"	HX-263	$1700, TTM Jan 05, HDTV monitor	<br />
30"	HX-303	$2000, TTM Jan 05, HDTV monitor</p>

<p><u>GL line</u><br />
TTM early 2005<br />
20"	GL-220-D	$700, TTM Mar 05, EDTV monitor<br />
27"	GL-427D	$1900, TTM Feb 05, HDTV monitor<br />
30"	GL-430D	$2200, TTM Feb 05, HDTV monitor</p>

<p><br />
<h2>Philips</h2><br />
<u>LCD panel monitors</u><br />
42"	42PF9996	$11000<br />
50"	50PF9996	1366x768, NTSC, HDMI, 1000:1 CR, 900 cd/m2 brightness</p>

<p><u>CES 2005</u><br />
<u>AmbiLight 2 Series Integrated</u><br />
Rear panel illumination system, TTM 2005, ATSC/QAM Cable CARD tuners, Pixel Plus 2 HD video processing<br />
32"<br />
37"<br />
42"	42PF9830A<br />
50"</p>

<p><br />
<h2>Samsung</h2><br />
Jun 04<br />
<u>LCD TV monitor</u><br />
46"	LTP468W	$10,000, TTM Jul 04, 1080p, 800:1 CR, 170degrees viewing angle, industry-leading 12 millisecond pixel response time, DVI, dual component, HDMI, 500 candelas brightness</p>

<p>Oct 04<br />
57"	There were no comments issued from the company representatives regarding the status of the 57" version anticipated at CES 2004 (57" LTP578W, $TBA, TTM Jun 04, 1080x1920, 1000:1 CR, 600 cd/m2, DNIe, HDMI, DVI)</p>

<p><img src="/articles/images/mt/image070.jpg" alt="Samsung 1080p 57 inch LNR570D" align="left"><br />
<u>CES 2005</u><br />
The 57" 1080p set anticipated one year ago (left) was finally shown as model: </p>

<p>57"	LNR570D	$16000 (also reported as $18000), TTM Jun 05, 1920x1080, 600 cd/m2 brightness, 6.2 million color capacity, integrated with ATSC/QAM Cable CARD tuners, 1000:1 CR, DNIe, AnyNet home-networking, response time faster than 8 seconds.  Note the delay of one year in availability date announcements, and the improvement of response time to 8ms from the 12 ms of the 46" 468W introduced 6 months ago, although I still see lag at the need but is more tolerable than other LCD screens that run at 20ms.<br />
<br clear="all"><br />
<img src="/articles/images/mt/image071.jpg" alt="Samsung 1080p 46 inch LNR460D" align="right"><br />
Samsung also showed a new 46" 1080p model: <br />
46"	LNR460D	$9000 (also reported as $13000), TTM Mar 05, LED backlighting, integrated ATSC/QAM Cable CARD tuners, LED technology with 100000 hours of panel life, less power, less heat, consistent illumination across the panel, 105 percent of the NTSC color gamut<br />
<br clear="all"><br />
And two new models with FFL (Flat Fluorescent Lamp) technology:<br />
32"	LNR328W	$3500, TTM Mar 05 <br />
40"	LNR409D	$5000, TTM Mar/May 05, 1080p, 3.2 billion color gradations, same LED life span of 100000 hours</p>

<p><br />
<h2>Sharp</h2><br />
<u>LCD AQUOS</u><br />
45"	LC-45GX6U	$10,000, TTM Aug 04, 1080p, AVS system included<br />
45" 	LC-45GD6U 	Same features of above model except that lacks the AVS system, S-video connections in/out are different, and is heavier because it has the AVS System within the unit.<br />
45"	LC-45GD4U  	Same features as the GD6U except that the cabinet is silver (rather than titanium), and the unit is designed for 120 volts (rather than 120-240 volts as both 6U models above)</p>

<p><img src="/articles/images/mt/image072.jpg" alt="Sharp Aquos 65 inch LCD TV" align="right"><br />
And also 37", 32" and 26" GD6U at 1366x768<br><br />
<br>Sep 04 (CEATEC in Japan)<br />
65"	AQUOS shown as the world's largest LCD panel (according to Sharp), planned production at its 6G plant in mid-2005, no firm TTM dates, 1920x1080 resolution, plans to produce LCD TVs of 50" and larger during FY05 (April 05-Mar 06) 	</p>

<p><u>CES 2005</u><br />
Sharp has shown in the US the 65" model above<br />
65"	integrated ATSC/QAM Cable CARD tuners, 1920x1080p, Quick Shoot video circuitry for 12 milliseconds response time, HDMI, IEEE1394, DVI-I, TTM 2H05, $TBA. </p>

<p>Also introduced were the following smaller AQUOS LCD panels:<br />
37"	37GD4U	$TBA, TTM Mar 05, 1366x768, ATSC/QAM tuner<br />
And 32" and 26" at 1366x768<br />
 <br />
<u>D7U and D5U Series</u><br />
1366x768, EPG, HDMI, IEEE1394, DVI-I, DTUs with titanium finish/bottom speakers, D5U with silver finish/side speakers, TTM 1Q05, $TBA<br />
26"	LC-26D7U/D5U<br />
32"	LC-32D7U/D5U<br />
37"	LC-37D7U/D5U<br />
 <br />
AQUOS panels use Quick Shoot (QS) Circuit for a response time of 16 milliseconds, 45" AQUOS panels at 12 milliseconds. </p>

<p><br />
<h2>Sim2</h2><br />
40"	HTL40 LINK	$12000, TTM Oct 04, 1366x768, 1000:1 CR, 600 cd/m2 brightness, 170 degrees viewing, 60000 hours lamp life, HDMI and DVI w/HDCP, DCDi<br />
A 46" panel is in the plans for 2005</p>

<p><br />
<h2>Sony</h2><br />
(Company announcement of 2004/5 models)</p>

<p><u>Flat Panel Monitors</u><br />
Large Scale Integrated (LSI) circuitry to improve response rates <br />
23"	KLV-23M1	$2300, LCD TV, 1366x768<br />
32"	KLV-32M1	$4500, LCD TV, 1366x768</p>

<p>Sep 04<br />
<u>LCD Qualia</u> Triluminos technology<br />
46"	KDX-46Q005	$10000 (also reported as $12000), TTM end 2004, 1920x1080, 450 cd/m2 brightness, 170 degree horiz/vertical viewing angle, replaces cold-cathode backlight tubes (which Sony says reproduce 65 to 75% of NTSC color space) with 8 rows of red, blue and green LEDs that render richer color  (105% of the color space of NTSC).	</p>

<p><br />
<h2>Syntax</h2><br />
<u>CES 2005</u><br />
<u>Olevia line</u><br />
Super-IPS (super-in-plane-switching) technology, 176 degrees viewing angle, 1200:1 CR, 1366x768, 8 ms response time, 800 cd/m2 brightness, NTSC tuner for split screens, YPbPr and YCbCr 480i, 480p, 720p and 1080i, VGA for PCs, DVI/HDCP</p>

<p>32"	LT32HV	$2000, TTM Nov 04 <br />
37"	LT37HV	$3000, TTM Dec 04</p>

<p><br />
<h2>Thomson</h2><br />
May 04 (company announcement of 2004/5 models)</p>

<p><u>LCD TV</u><br />
<u>RCA Line Monitor</u><br />
26"	LCDX2620W	$2600, TTM Jun 04, 1280x768, open distribution, DVI/HDCP, component, RGB, NTSC tuner, 600:1 CR, 500 cd/m2 brightness<br />
<u>RCA Scenium Line</u><br />
NTSC tuning monitors, DVI/HDCP, component, RGB, 500:1 CR, 500 cd/m2 brightness, 170 degrees of vertical/horizontal viewing<br />
27"	LCDX2722W	$2800, TTM Jun 04, 1280x720<br />
32"	LCDX3022W	$3800, TTM Jul 04, 1280x768</p>

<p><u>CES 2005</u><br />
Introduced seven new LCD-TV 2005 panels, two w/ATSC tuners, some w/built-in DVD players<br />
15", 20", 23", 26", and 32"</p>

<p><br />
<h2>Toshiba</h2><br />
(Company announcement of 2004/5 models)</p>

<p><u>LCD Monitors</u><br />
DVI or HDMI, Cable clear DNR+ video noise reduction circuitry<br />
<u>EDTV 4:3 LCD Monitors</u><br />
500:1 CR, component input, 480x640<br />
14"	14DL74	$500, Jun 04<br />
20"	20DL74	$1000, Jun 04<br />
<u>TheaterWide 16:9 LCD Monitors</u><br />
DVI/HDCP<br />
23"	23HL84	$1800, Aug 04, 1280x768, 500:1 CR<br />
23"	23HLV84	$2000, Aug 04, LCD TV/DVD combi-unit, 1280x768, 500:1 CR<br />
26"	26HL84	$2500, Jun 04, 1366x768<br />
32"	32HL84	$3500, May 04, 1366x768</p>

<p><u>Cinema Series Flat-panel Monitors</u><br />
New double-baffle design<br />
32"	32HLX84	$4000, Oct 04, LCD monitor, 1366x768, 800:1 CR</p>

<p>Oct 04 (CEATEC)<br />
TTM in Japan in Nov 04, records HD video to an external LAN HDD as part of a home network, Meta Brain system LSI<br />
37"	$6600 (two versions)<br />
32"	$5500</p>

<p><br />
<h2>Viewsonic</h2><br />
Sep 04<br />
32"	Next Vision N3200w	$3000, 1280x768, 600:1 CR, 170 degree viewing angle, 18 ms response time<br />
 <br />
<u>CES 2005</u><br />
40"	N4050w	$5000, 1280x768, 600:1 CR, 450 nits of brightness, DVI-D, component<br />
27"	N2750w	$1200, 1280x768, 600:1 CR, 550 nits of brightness, EDTV and HDTV inputs for 480p/720p/1080i</p>

<p><br />
<h2>Westinghouse</h2><br />
<u>CES 2005</u><br />
37"<br />
42"		$2500, 1280x768, integrated<br />
47" 		1080p, TTM Mar 05, 1920x1080p, art frames</p>

<p>Be sure that you read the next article in the series: HDTV Tuners & Tuning DVR's (Coming Soon)</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>October 18, 2005  5:13 AM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(223)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 223)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2005/10/2005-hdtv-report-part-9-lcd-tv-panels.php" type="text/javascript" charset="utf-8"></script>
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
