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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 231 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 231
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=2005 HDTV Report, Part 17: Digital Connectivity&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/articles/2005/10/2005-hdtv-report-part-17-digital-connectivity.php&amp;title=2005 HDTV Report, Part 17: Digital Connectivity">
		<span style="display:none">This part 17 deals with digital connectivity (DVI, HDMI, IEEE1394).

The DVI (Digital Visual Interface) 1.0 specification was introduced in April 1999 for creating a digital connection between a PC and a display device.  It is a point-to-point connection with enough bandwidth for uncompressed HD signals, but it was not implemented for audio.  On December 9, 2002, the seven founders of HDMI (High-Definition Multimedia Interface) announced the 1.0 specification of this connectivity standard, the enhanced, more robust form of DVI.  The standard supports HD uncompressed video, 8-channel digital audio (reportedly up to 192 KHz), and some control signals on a single cable (15 mm, 19 pin), while using less than half the available bandwidth.  HDMI has the same video capacity as DVI, or up to five Gbps of bandwidth, double what a HD signal would require, and is backward compatible with DVI by using an adapter.  There is a two-way communication between the source device and the receiving device by which the receiving device tells the source about its multi-channel capabilities.  Most new DTV monitors and integrated displays have incorporated DVI or HDMI inputs, however, some displays were reported to have interoperability problems.

IEEE1394 is a digital interface conceived by Apple Computer in 1986, and it was called &quot;Fire Wire&quot; for its fast speed of operation.  In 1995, the Institute of Electrical and Electronic Engineers (IEEE) adopted the serial bus as its standard 1394.  Sony trademarked their name iLink for their implementation of the 1394 bus as a 4-pin connector.  In March 2000, an updated specification was approved, the 1394a.  In 2001, the IEEE 1394 &quot;b&quot; standard emerged as a network technology (rather than &quot;a&quot;, as serial bus); it is capable of moving data streams at faster speeds over longer distances than the original.  The connection is now being used by a growing number of DTV equipment manufacturers for the transmission of compressed HD signals, such as D-VHS recording and networking DTV equipment.

Since 2003, most manufacturers released a large variety of products adopting these connections to enable their equipment for digital connectivity, IEEE1394 for compressed HD video from integrated TVs with tuners, cable and OTA HD-STBs mainly for recording purposes (using DTCP), and DVI/HDMI for uncompressed HD video for the viewing of protected content (using HDCP).</span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 231";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download 2005 HDTV Report, Part 17: Digital Connectivity" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="2005 HDTV Report, Part 17: Digital Connectivity" />
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
	<title>HDTV Magazine - 2005 HDTV Report, Part 17: Digital Connectivity</title>
	<meta name="keywords" content="silicon image, dual link, mbps meters, first generation, backward compatible, dvi, hdmi, digital, link, video, standard, pin, mbps, audio, hdcp, mhz, signal, cable, dual, signals, device, content, first, meters, channel" />
	<meta name="description" content="This part 17 deals with digital connectivity (DVI, HDMI, IEEE1394).

The DVI (Digital Visual Interface) 1.0 specification was introduced in April 1999 for creating a digital connection between a PC and a display device.  It is a point-to-point connection with enough bandwidth for uncompressed HD signals, but it was not implemented for audio.  On December 9, 2002, the seven founders of HDMI (High-Definition Multimedia Interface) announced the 1.0 specification of this connectivity standard, the enhanced, more robust form of DVI.  The standard supports HD uncompressed video, 8-channel digital audio (reportedly up to 192 KHz), and some control signals on a single cable (15 mm, 19 pin), while using less than half the available bandwidth.  HDMI has the same video capacity as DVI, or up to five Gbps of bandwidth, double what a HD signal would require, and is backward compatible with DVI by using an adapter.  There is a two-way communication between the source device and the receiving device by which the receiving device tells the source about its multi-channel capabilities.  Most new DTV monitors and integrated displays have incorporated DVI or HDMI inputs, however, some displays were reported to have interoperability problems.

IEEE1394 is a digital interface conceived by Apple Computer in 1986, and it was called &quot;Fire Wire&quot; for its fast speed of operation.  In 1995, the Institute of Electrical and Electronic Engineers (IEEE) adopted the serial bus as its standard 1394.  Sony trademarked their name iLink for their implementation of the 1394 bus as a 4-pin connector.  In March 2000, an updated specification was approved, the 1394a.  In 2001, the IEEE 1394 &quot;b&quot; standard emerged as a network technology (rather than &quot;a&quot;, as serial bus); it is capable of moving data streams at faster speeds over longer distances than the original.  The connection is now being used by a growing number of DTV equipment manufacturers for the transmission of compressed HD signals, such as D-VHS recording and networking DTV equipment.

Since 2003, most manufacturers released a large variety of products adopting these connections to enable their equipment for digital connectivity, IEEE1394 for compressed HD video from integrated TVs with tuners, cable and OTA HD-STBs mainly for recording purposes (using DTCP), and DVI/HDMI for uncompressed HD video for the viewing of protected content (using HDCP)." />
	<meta name="title" content="2005 HDTV Report, Part 17: Digital Connectivity" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/articles/2005/10/2005-hdtv-report-part-17-digital-connectivity.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=231', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2005/10/2005-hdtv-report-part-17-digital-connectivity.php">2005 HDTV Report, Part 17: Digital Connectivity</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>October 26, 2005</b>
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
				<blockquote>This is the next in a series of articles taken from the <b>H/DTV Technology Review & CES 2005 Report</b> by Rodolfo La Maestra, published in March 2005. If you are interested in downloading the full version of this report, it is currently available for purchase from our <a href="/store/ces-2005.php">CES Report</a> page.</blockquote>

<p><br />
<h2>DVI</h2><br />
The DVI (Digital Visual Interface) 1.0 specification was introduced in April 1999 by the Digital Display Working Group integrated by Silicon Image, Intel, Compaq, Fujitsu, Hewlett-Packard, IBM and NEC for the purpose of creating an digital connection interface between a PC and a display device.  It is a connection with enough bandwidth for <u>uncompressed</u> HD signals.</p>

<p>The 1.0 DVI specification is a point-to-point solution that supports video content but not audio.  DVI uses the Transition-Minimized Differential Signaling (TMDS) protocol developed by Silicon Image.  PanelLink is the Silicon Image's proprietary implementation of TMDS.</p>

<p>The HDCP (High-bandwidth Digital Content Protection) 1.0 specification was developed by Intel with contributions from Silicon Image in February 2000 to protect DVI outputs from being copied by providing a secure link between a video source and a display device.  </p>

<p>HDCP offers authentication, encryption, and renewability.  The Motion Picture Association of America (MPAA) endorsed HDCP as the standard for the secure transmission of HD signals over DVI.  </p>

<p>Most new DTV monitors and integrated displays have incorporated DVI or HDMI inputs, although on their first generation some panels were not HDCP compliant, now there is a large volume of H/DTV equipment that is.  However, some displays were reported to have interoperability problems regarding DVI/HDCP or HDMI/HDCP.   </p>

<p>The DVI standard is able to handle single or dual link connections.  A single-link connection supports up to UXGA resolution of 1600 x 1200 at 60 Hz.  Dual-link connections provide bandwidth for resolutions beyond QXGA (2048 x 1536).</p>

<p>According to DVI specs a single link has 165 MHz/pixels capacity for 3 channels, Red, Green and Blue, each channel could support up to 1.65 Gbps speed rate, or a total of 4.95 Gbps for the 3 channels (165 MHz x 30 bits x sec).  Dual-link connections double that capacity to 330 MHz, with a speed-rate capacity up to 9.9 Gbps.</p>

<p>The 1080i HD format has 1125 total lines of 2200 pixels x frame (active image 1080x1920), requiring 74.25 MHz/pixels (1125 x 2200 x 30fps).  Each pixel contains data for RGB and is implemented by DVI with 30 bits (8 per each color plus another 6 for encoding).  An HD 74.25 MHz/pixel signal would require 2.2 Gbps speed rate. </p>

<p>A link of 3 channels supporting 165 MHz is sufficient for the 74.25 MHz HD 1080i signal without requiring the use of the second link, and will also be sufficient to transport a 1080p/60 frames x second signal at 148.5 MHz without requiring the second link.</p>

<p>If the signal to be transmitted would be higher than the single link capacity of 165 MHz, it would require the use of a dual DVI link connection, each link will carry half of the signal; the second link cannot be used with just what is exceeding 165 MHz of the first link.  For example, a 200 MHz signal would be carried with both links operating at 100 MHz each.</p>

<p>HDMI uses the same 165MHz capacity per link; dual-link uses the B connector with the second link pins.</p>

<p>DVI identifies and auto-configures the connected device.  If source equipment is connected with DVI single link to a display configured as dual link DVI, the image will experience a lower resolution.  Some first generation single link DVI cables use dual link connectors.  DVI standard cables have typically a five-meter distance limitation, although with better quality wiring, such as fiber-optic, higher distances are possible. </p>

<p>There are three types of DVI connectors:</p>

<p><img src="/articles/images/mt/image114.gif" alt="DVI-I Connector" align="right"><u>DVI-I (integrated)</u>, carries a single or dual-link digital signal, with an additional analog signal for legacy devices.  The 29-pin DVI connector uses 24 pins for the digital data stream (12 for each link) and and 5 pins (1 plus -shaped blade and 4 pins) to carry analog video and ground.</p>

<p><img src="/articles/images/mt/image115.gif" alt="DVI-D Connector" align="right"><u>DVI-D (digital)</u> carries digital-only video data to a display.  It is designed for 12 or 24 pin connections, and single/dual link operation (notice the lack of 4 pins, 2 above/2 below the flat blade).</p>

<p><img src="/articles/images/mt/image116.jpg" alt="DVI-A Connector" align="right"><u>DVI-A (analog)</u> is available for legacy analog applications to carry analog signals to a CRT monitor or an analog HDTV (claims to be better than VGA).  The three rows of eight pins have three pins missing in the first row, five missing in the second row and four missing in the third row, and that the "flat blade" contact seen to the left has two contacts above and below it.  There is no single or dual link in analog cables.</p>

<p>Regarding connecting plugs to receptacles:</p>

<p>A DVI-D plug can be connected to either DVI-D or DVI-I receptacles, <br />
A DVI-A plug can be connected to either DVI-I/A or VGA (w/adapter) receptacles,<br />
A DVI-A receptacle would accept DVI-I but not DVI-D.<br />
A DVI-I plug can be connected to either DVI-I or DVI-A receptacles (the 'A' ignores 'I's digital pins)</p>

<p><br />
<h2>IEEE1394</h2><br />
IEEE1394 is a digital interface conceived by Apple Computer in 1986, and it was called "Fire Wire" for its fast speed of operation.  In 1995, the Institute of Electrical and Electronic Engineers (IEEE) adopted the serial bus as its standard 1394.  Sony trademarked their name iLink for their implementation of the 1394 bus as a 4-pin connector.  </p>

<p>In March 2000, an updated specification was approved, the 1394a.  The "a" standard supports speeds of 100Mbps, 200Mbps, and 400Mbps over a distance of 4.5 meters, and up to 63 peer-to-peer nodes/devices.</p>

<p>In 2001, the IEEE 1394 "b" standard emerged as a network technology (rather than as serial bus); it is capable of moving data streams at faster speeds over longer distances than the original.  </p>

<p>The "b" standard specifications were intended to support up to 3,200 Mbps depending on the cable material, and permit the use of cabling materials not supported by the "a" standard.  It supports speeds up to 100Mbps over 100 meters of Category 5 wiring, 400 Mbps over 100 meters of plastic optical fiber, and up to 3,200 Mbps (or 3.2 Gbps) over 100 meters of glass optical fiber.</p>

<p>The "b" standard is compatible with the "a" standard; if an "a" device were plugged into a "b" component, the bus would deliver a maximum speed limited by the "a" standard (400Mbps).  Each "b" device can be set up to 100 meters apart from the next in sequence, allowing the total network to be quite significant in cable length. </p>

<p>The licensing fee for the use of the patented technology is $ 0.25 per system; chipsets are less than $5 each in volume.</p>

<p>It supports hot swapping and plug-and-play, so a consumer's 1394 bus can recognize automatically a 1394 device when it is connected/disconnected, and reconfigure itself.<br />
 <br />
The connection is now being used by a growing number of DTV equipment manufacturers for the transmission of <u>compressed</u> HD signals, such as D-VHS recording and networking DTV equipment.</p>

<p>There are three types of cables used for 1394.  The 6-conductor type has two separately shielded twisted pairs for data and two power wires in an overall shielded cable with 6-pin connectors on either side.  The 4-wire cable uses two separately shielded data cables without power wires in an overall shielded cable with 4-pin connectors on either end.  The third type of cable uses either type of actual cable, with a 6-pin connector on one side, and a 4-pin connector on the other side of the cable.</p>

<p>The 4-pin connector is more common on digital video camcorders and other small external devices because of it's small size, while the 6-pin connector is more common on PC's, external hard drives due to it's durability and support for external power for 1394 peripherals.</p>

<table cellpadding="5">
	<tr>
		<td width="33%" align="center">
			<img src="/articles/images/mt/image117.gif" alt="IEEE-1394 Connector">
		</td><td width="33%" align="center">
			<img src="/articles/images/mt/image118.jpg" alt="IEEE-1394 Connector">
		</td><td width="33%" align="center">
			<img src="/articles/images/mt/image119.jpg" alt="IEEE-1394 Connector">
		</td>
	</tr><tr>
		<td align="center">
			6-pin female connector on left w/4-pin female connector on the right
		</td><td align="center">
			The 6-pin male connector
		</td><td align="center">
			The 4-pin male connector
		</td>
	</tr>
</table>

<p>HD signals are broadcast in compressed MPEG-2 format at approximately 19 Mbps.  D-VHS VCRs are able record compressed HD signals and require a 1394 connection to receive the digital data stream.  HDTV monitors require a MPEG-2 decoder to decompress the signal for display, as oppose to DVI that is uncompressed.</p>

<p>DTCP (Digital Transmission Content Protection) has been created for the purpose of copy protection over the 1394 connection.  DTCP is also known as 5c for the five companies that participated on the standard (Sony, Toshiba, Intel, Hitachi, and Matsushita).</p>

<p>During the last two to three years, there have been many discussions (and hype) about using these types of digital connections (DVI and 1394) for DTV equipment, rather than only the analog connections (component YPbPr, RGB, RGBHV, etc), for protecting HD digital content.</p>

<p>Since 2003, most manufacturers released a large variety of products adopting these two connections to enable their equipment for digital connectivity, IEEE1394 for compressed HD video from integrated TVs with tuners, cable and OTA HD-STBs mainly for recording purposes, and DVI for uncompressed HD video for the viewing of protected content (using HDCP).    </p>

<p>HDMI is quickly replacing DVI and is being implemented already on many products, and is becoming the de-facto standard for transporting uncompressed signals over a cable.</p>

<p><br />
<h2>HDMI</h2><br />
On December 9, 2002, the seven founders of HDMI (High-Definition Multimedia Interface) announced the 1.0 specification of this connectivity standard, the enhanced, more robust form of DVI.  The seven founders are Hitachi, Matsushita, Philips, Silicon Image, Sony, Thomson, and Toshiba.</p>

<p>The standard supports HD uncompressed video, 8-channel digital audio (reportedly up to 192 KHz), and some control signals on a single cable (15 mm, 19 pin), while using less than half the available bandwidth.</p>

<p>HDMI has the same video capacity as DVI, or up to five Gbps of bandwidth, double what a HD signal would require, and is backward compatible with DVI by using an adapter.</p>

<p>Not included in the standard but used with DVI and HDMI is the HDCP (High-bandwidth Digital Content Protection) protocol.  HDCP is licensed by Intel, designed to protect HDMI and DVI signals from piracy, and used for authentication between A/V products.</p>

<p>In 2003, a license fee of five cents was applied to each product (four cents for HDMI, 1 cent for HDCP), that manufacturers had to pay to the HDMI founders and Intel.</p>

<p><br />
<h2>HDMI Multi-channel Audio</h2><br />
In recent articles, there were claims that HDMI was not implemented by some manufacturers as a full multi-channel connection.  The confusion comes from the fact that the majority of first-generation HDMI devices were TVs with only two-channel stereo, which have no use for the full multi-channel signal.  However, most other equipment, from DVD players to A/V receivers, switchers, etc, is capable to receive, process, mix, or send the full multi-channel audio content across HDMI.</p>

<p>According to Silicon Image, there is a two-way communication between the source device and the receiving device by which the receiving device tells the source about its multi-channel capabilities.  The source device can then send a matching signal, such as two-channel stereo to a TV, or 5.1 DD channel to an A/V receiver.  In other words, the source device adapts to the receiving device when sending the signal.     </p>

<p>In the case of an A/V receiver receiving the signal from a 5.1 DD DVD player, both ends of the connection recognize the need to maintain the 5.1, but the receiver might redirect the signal to a TV that needs only L/R channels, for which the output of the receiver adapts on only that output jack by down-mixing the DD stream.</p>

<p>HDMI chips introduced on the first generation batch distributed to manufacturers did not have the capability of 1080p; second and third-generation chips (mentioned in the next section) have such capability now.  Some 1080p TV sets might not accept a 1080p input for reasons of their internal design but also for the use of the first batch of chips.  </p>

<p><br />
<h2>HDMI Connectivity Chips</h2><br />
<u>Silicon Image</u>:</p>

<p>A year ago, at CES 2004 the company announced the introduction of three PanelLink HDMI Cinema ICs for more features, lower cost per port, DVD-Audio support, higher video resolutions, more sampling frequencies, more HDMI ports, etc:</p>

<p><u>SiI 9030 transmitter</u>, targeted at the DVD-Audio players/recorders and receivers, supports D-Audio at 32-192kHz frequencies, backward compatible with SiI 9190 1st generation transmitter, 25-112 MHz video bandwidth, compliant w/CEA-861B and HDCP1.1, will support digital audio through S/PDIF digital audio interface, will support Plasma/LCD w/1024 lines (WSXGA)</p>

<p><u>SiI 9021 receiver</u>, dual HDMI inputs, designed for DTVs, backward compatible with first-generation SiI 9993 receiver chip, compatible with CEA-861B and HDCP 1.1, 25-112 MHz video support for Plasma/LCD w/1024 lines (WSXGA)</p>

<p><u>SiI 9031 receiver</u>, targeted at Home-theater receivers, DVD-Audio support, sample frequencies of 32-192kHz, dual HDMI inputs, backward compatible w/SiI 9993 first-generation receiver, HDMI 1.0 compatible, CEA-861B and HDCP 1.1 compatible, 32-112 MHz video bandwidth for support of Plasma/LCD (WSXGA) 1024 lines of resolution, support of compressed digital audio through S/PDIF interface. </p>

<p><u>CES 2005</u><br />
On January 5, 2005, Silicon Image announced a couple of new products, both supporting 1080p:</p>

<p><u>SiI 8100</u>: The first integrated video processor with HDMI/HDCP, HD RGB/YPbPr component video, and SD inputs, targeted to low-cost LCD and CRT TVs.  The processor performs video scaling, state-of-the-art 3D motion-adaptive video deinterlacing, programmable hue, saturation, brightness and contrast adjustments, 50 Hz to 60Hz video rate conversion, PIP and picture overlay, and is suited with an 8-bit on-screen display capability for graphics, menus, and EPGs. </p>

<p>The SiI 8100 is packaged as a 256-pin LQFP and comes with a complete set of hardware and software development tools for manufacturer implementation.  TTM 3Q05 (sampling May 05), $13.95 in 10K quantities, </p>

<p><u>SiI 9011</u>:  HDMI/HDCP low cost third generation PanelLink Cinema Receiver, HDCP repeater, backward compatible with prior-generation SiI 9021, 9031, 9993 HDMI receivers, supports DVD-Audio and 7.1 audio at 96 kHz, and stereo at 192 kHz, interfaces with 12, 24 and 48-bit modes, available in 128-pin LQFP and 144-pin TQFP.  LG and a number of other manufacturers are incorporating the IC in their new line of plasma and LCD models.  TTM current, $6.95 in 10K quantities (128-pin version).</p>

<p>To provide consumers with a simple means of identifying HDTVs and other consumer electronics devices capable of receiving and playing the most valuable digital content, Silicon Image operates the PanelLink Cinema (PLC) Partners Program.  The PLC Partners logo assures consumers that HDMI systems bearing this logo have been tested for HDCP functionality and content-readiness, meaning they are interoperable and ready to receive and play premium digital content.  Sony, Mitsubishi, Samsung, Hitachi, LG, Sanyo and others have joined the program, which also has broad industry support from content providers The Walt Disney Co., Fox, Universal and Warner Bros.  The first PLC-compliant TV, a 50" plasma from LG, was shown at CES.<br />
 </p>

<h2>Other Digital Connectivity - Update</h2>
On May 2004, the 1394 Trade Association announced their plans to enhance the FireWire standard by making it wireless and adding the 1394c standard to permit 1394 and Ethernet to share a CAT-5 cable network, and is expected to automatically sense either at the wall-jack, although the first version night require a manual switch.

<p>There is also in development an enhancement to use coaxial cable and CAT-6 for 1394b signals, and an extension of the current 50-meter limitation over plastic optical fiber at 250Mbps.  Regarding wireless IEEE1394, the association approved PAL (Protocol Adaptation Layer) to allow 1394 signals to be transported over wireless IEEE 802.15.3 or over ultrawideband (UWB) 802.15.3a faster pipe.</p>

<p>The 15.3 standard is a 2.4 GHz dual-use standard judged as more efficient and reliable than Wi-Fi for SD and HD home distribution.  15.3 has a data rate of 55Mbps at 50 meters, and 22Mbps at 100 meters, and meet the IEEE requirement of minimum 110 Mbps at 10 meters and 200 Mbps at four meters.   </p>

<p>On December 2004, the company Pulse~LINK introduced a new Gigabit chipset for Ultra Wide Band (UWB) wireless communication for HD signals, as an alternative to DVI, HDMI and 1394b wired solutions.  The product will become available in February 2005, and has been tested to handle 667Mb/s of capacity after error correction, but has been announced that within the next 60 days it would be able to exceed 1Gb/s data rate.</p>

<p>On January 2005, Audio Authority announced their Cat 5 HDTV signal distribution amplifiers, model 9860 series.  The 9861 ($194) Cat 5 Driver for HDTV 1080i/720p up to 1000 feet; the 9868 Adapter ($178) and 9869 Distribution Amplifier ($297) convert signals back to the original video/audio formats, the 9869 has 4 HD outputs, the 9868 has one.  The system uses cable length compensation circuitry for long cable runs.  The 9860 has a 9-channel wide architecture for simultaneous distribution of digital audio, analog stereo audio, composite video, and HD component video.</p>

<p>At CES, a new wireless network protocol (UWB) co-developed by Focus Enhancements was demo with an impressive data rate of up to 880Mbps at 3 meters, 110Mbps at 30 meters (which has sufficient capacity for up to four HD HDTV stream of 20Mbps each).  UWB is 10 times the 802.11b 11Mbps common WiFi (that usually provide actual rates of half of that speed).</p>

<p>Be sure that you read the next article in the series: Content Protection (Coming Soon)</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>October 26, 2005  5:35 AM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(231)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 231)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2005/10/2005-hdtv-report-part-17-digital-connectivity.php" type="text/javascript" charset="utf-8"></script>
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
