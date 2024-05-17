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
	$res_author = mQuery("SELECT title, channel, amazon_tracking_id, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Shane Sturgeon" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 3457 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 3457
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=VIZIO Launches NEW XVT Pro(TM) Series of Advanced HDTV Technology&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/news/2010/01/vizio-launches-new-xvt-protm-series-of-advanced-hdtv-technology.php&amp;title=VIZIO Launches NEW XVT Pro(TM) Series of Advanced HDTV Technology">
		<span style="display:none">&lt;img src=&quot;http://www.hdtvmagazine.com/news/images/2010-01-05_vizios_xvtpro720sv_72_480hz_sps_truled_full_hd3d_hdtv.jpg&quot; alt=&quot;VIZIO's XVTPRO720SV 72&amp;quot; 480Hz SPS TruLED Full HD3D HDTV&quot; height=&quot;144&quot; width=&quot;192&quot; class=&quot;keyimg&quot; style=&quot;float:left; padding:0 5px 5px 0&quot;&gt;VIZIO, America's #1 LCD HDTV Company, has unveiled their new high-performance XVT Pro Series of 480Hz SPS(TM) 16x9 TruLED(TM) Full HD3D(TM)HDTVs available in 72&quot;, 55&quot; and 47&quot; sizes, as well as a 58&quot; Cinema Wide HDTV that displays content in 21 x 9 aspect ratio. The 480Hz SPS(TM) 16x9 TruLED(TM) Full HD3D(TM)HDTVs incorporate the very latest in advanced HDTV technology with 480Hz SPS(TM), TruLED(TM) brilliant LEDs that pervade the entire screen, Smart Dimming(TM) circuitry controls hundreds of zones of LEDs per screen to the precise light level per picture frame. In addition, the set's full HD3D(TM) delivers stunning 3D images in FULL HD 1080P resolution, with other advanced features like...</span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 3457";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download VIZIO Launches NEW XVT Pro(TM) Series of Advanced HDTV Technology" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="VIZIO Launches NEW XVT Pro(TM) Series of Advanced HDTV Technology" />
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
	<title>HDTV Magazine - VIZIO Launches NEW XVT Pro(TM) Series of Advanced HDTV Technology</title>
	<meta name="keywords" content="xvt pro, yes yes, wireless hdmi, pro series, internet apps, xvt, pro, full, technology, yes, wireless, sps, hdtv, hdmi, display, series, built, experience, tvs, content, truled, new, top, advanced, ratio" />
	<meta name="description" content="&lt;img src=&quot;http://www.hdtvmagazine.com/news/images/2010-01-05_vizios_xvtpro720sv_72_480hz_sps_truled_full_hd3d_hdtv.jpg&quot; alt=&quot;VIZIO's XVTPRO720SV 72&amp;quot; 480Hz SPS TruLED Full HD3D HDTV&quot; height=&quot;144&quot; width=&quot;192&quot; class=&quot;keyimg&quot; style=&quot;float:left; padding:0 5px 5px 0&quot;&gt;VIZIO, America's #1 LCD HDTV Company, has unveiled their new high-performance XVT Pro Series of 480Hz SPS(TM) 16x9 TruLED(TM) Full HD3D(TM)HDTVs available in 72&quot;, 55&quot; and 47&quot; sizes, as well as a 58&quot; Cinema Wide HDTV that displays content in 21 x 9 aspect ratio. The 480Hz SPS(TM) 16x9 TruLED(TM) Full HD3D(TM)HDTVs incorporate the very latest in advanced HDTV technology with 480Hz SPS(TM), TruLED(TM) brilliant LEDs that pervade the entire screen, Smart Dimming(TM) circuitry controls hundreds of zones of LEDs per screen to the precise light level per picture frame. In addition, the set's full HD3D(TM) delivers stunning 3D images in FULL HD 1080P resolution, with other advanced features like..." />
	<meta name="title" content="VIZIO Launches NEW XVT Pro(TM) Series of Advanced HDTV Technology" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/news/2010/01/vizio-launches-new-xvt-protm-series-of-advanced-hdtv-technology.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=3457', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2010/01/vizio-launches-new-xvt-protm-series-of-advanced-hdtv-technology.php">VIZIO Launches NEW XVT Pro(TM) Series of Advanced HDTV Technology</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>January  5, 2010</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=344&category=3D HDTV">3D HDTV</a></b>, <b><a href="/category.php?id=269&category=New Products & Equipment">New Products & Equipment</a></b>
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
				<p class="prtitle">VIZIO Launches NEW XVT Pro(TM) Series of Advanced HDTV Technology</p>

<p><img src="http://www.hdtvmagazine.com/news/images/2010-01-05_vizios_xvtpro720sv_72_480hz_sps_truled_full_hd3d_hdtv.jpg" alt="VIZIO's XVTPRO720SV 72&quot; 480Hz SPS TruLED Full HD3D HDTV" height="144" width="192" class="keyimg"><strong>LAS VEGAS (CES), and IRVINE, Calif., Jan. 5 /PRNewswire/</strong> -- VIZIO, America's #1 LCD HDTV Company, has unveiled their new high-performance XVT Pro Series of 480Hz SPS(TM) 16x9 TruLED(TM) Full HD3D(TM)HDTVs available in 72", 55" and 47" sizes, as well as a 58" Cinema Wide HDTV that displays content in 21 x 9 aspect ratio. The 480Hz SPS(TM) 16x9 TruLED(TM) Full HD3D(TM)HDTVs incorporate the very latest in advanced HDTV technology with 480Hz SPS(TM), TruLED(TM) brilliant LEDs that pervade the entire screen, Smart Dimming(TM) circuitry controls hundreds of zones of LEDs per screen to the precise light level per picture frame. In addition, the set's full HD3D(TM) delivers stunning 3D images in FULL HD 1080P resolution, with other advanced features like VIZIO INTERNET APPS(TM) (VIA) built-in high definition wireless (802.11n dual-band) and wired networking, a Bluetooth universal remote control with sliding QWERTY keyboard, and Wireless HDMI which allows the TV to receive HD video and audio from sources without an HDMI cable.</p>

<p>"VIZIO's entry into custom and specialty electronics is great for the industry. VIZIO has built strong brand recognition, so offering specialty retailers and custom installers exclusive access to a line with features they can market is a nice sales tool. Customers already know the VIZIO brand and it's an easy transition to educating them about the benefits of their advanced 3D, LED technology and unique solutions such as (21:9) 2.35:1 native aspect ratio HDTVs," said Tom LeBlanc, Senior Writer/Technology Editor, CE Pro magazine.</p>

<p><br />
<strong>XVTPRO 3D Ready 1080p TruLED(TM) 480Hz SPS(TM) TVs</strong><br />
<pre><br />
                                                  Advanced<br />
                                 HDMI   VIA       True<br />
                  Smooth Smart   1.3    Wi- Blue- Wide      SRS   In<br />
    Model    Size Motion Dimming Inputs Fi  Tooth Polarizer Audio Store MSRP</p>

<p>  XVTPRO720SV 72"  Yes -   480     5    Yes  Yes    No     Studio  Aug $3499<br />
                   480Hz  Zones                            Sound<br />
                   SPS                                      HD</p>

<p>  XVTPRO550SV 55"  Yes -   120     5    Yes  Yes    Yes    Studio  Aug $2499<br />
                   480Hz  Zones                            Sound<br />
                   SPS                                      HD</p>

<p>  XVTPRO470SV 47"  Yes -   160     5    Yes  Yes    Yes    Studio  Aug $1999<br />
                   480Hz  Zones                            Sound<br />
                   SPS                                      HD<br />
</pre></p>

<p>"Our 2010 XVT Pro series offers a refreshing combination of the latest technology like 480Hz SPS(TM) TruLED(TM) coupled with must-have features like immersive FULL HD3D(TM) experience and VIZIO INTERNET APPS (with built in wireless networking) and wireless HDMI and Bluetooth capabilities. We at VIZIO are setting new standards for picture quality and user experience," said Laynie Newsome, VIZIO Co-Founder and VP Sales and Marketing Communications. "50% of consumers want a 3D home theater, according to Quixel Research, and our new XVT Pro Series brings the latest technology to consumers who want the absolute BEST in class."</p>

<p>Defining state of the art performance, these VIZIO 72", 55" and 47" Full HD3D(TM) TVs advance refresh rates to 480Hz SPS(TM) with VIZIO's Smooth Motion(TM) technology. Their TruLED(TM) brilliant LEDs pervade the entire display (Under 3" profile) and have Smart Dimming(TM) circuitry that controls hundreds of zones of LEDs per screen to the precise light level per picture frame in 480, 120 and 160 zones, respectively. The XVT Pro series offers an incredible 10 million to 1 Mega Dynamic Contrast Ratio(TM) for extraordinary picture quality that delivers incredible color, and even brighter whites and deeper blacks than ever before while displaying 1.7 Billion Colors using a 10-Bit data input panel.</p>

<p>For environments with bright ambient lighting, the 55" and 47" models feature a unique anti-reflective panel that produces better contrast with rich and deep blacks even in brightly lit rooms.</p>

<p>FULL HD3D(TM)</p>

<p>Utilizing SENSIO® 3D technology to deliver 3D content over conventional 2D infrastructure, the new VIZIO XVT Pro Full HD3D TVs display stunning 3D content that the user can view with XpanD active-shutter glasses (sold separately). Unlike other 3D TVs that use passive stereoscopic imaging, VIZIO's Full HD3D TVs can display full 1080p video to each eye by rapidly alternating between the left-eye and right-eye images within the same visual space. The special active-shutter glasses, which communicate with the television over Bluetooth, then transform each lens from opaque to transparent in perfect synchronization with the images displayed on the TV, which allows for delivery of the full frame rate capable by the television for the ultimate 3D HDTV viewing experience.</p>

<p>These sets can produce 3D images from SENSIO encoded material on conventional DVD and Blu-ray players, as well as from future distribution channels such as pay per view, video on demand, DTV and HDTV broadcasts.</p>

<p>"We are pleased to be working closely with VIZIO and be a part of the creation of a substantial install-base of consumers ready to watch 3D movies, live concerts, and sporting events from the comfort of their home," states Nicholas Routhier, President and Chief Executive Officer of SENSIO Technologies.</p>

<p>"We are excited to partner with VIZIO as catalysts in the 3D revolution for the home," said Maria Costeira, CEO of XpanD. "The combination of XpanD Cinema active-shutter glasses and Vizio displays will provide an immersive, dynamic and cost-effective solution for VIZIO XVT Pro owners."</p>

<p><br />
<strong>Wireless HDMI</strong></p>

<p>Eliminating the need for an HDMI cable from source components to the display, the 72", 55" and 47" XVT Pro Series have an integrated Wireless HDMI receiver built-in, using SiBEAM's robust 60 GHz technology to receive HD content from high definition sources such as Blu-ray players or set-top boxes with full HD 1080p resolution when paired with a separately available VIZIO XVT Pro Wireless HDMI Adapter. The XVT Pro Adapter supports up to 4 HDMI sources and operates at 60GHz to avoid interference with other wireless devices in the home like cordless telephones and wireless networks that operate at 2.4 and 5 GHz.</p>

<p><br />
<strong>SRS StudioSound HD</strong></p>

<p>VIZIO XVT Pro TVs will feature SRS StudioSound HD -- the ultimate all-in-one audio suite designed specifically for Flat Panel TVs. Years of excellence in audio, practical experience and patented technologies allow StudioSound HD to deliver the most immersive and natural surround sound ever using built-in TV speakers. The suite also delivers remarkably crisp and clear dialog, rich bass, an elevated sound stage and consistent, spike-free volume levels. StudioSound HD features optimized audio presets for movies, news, sports and music while also providing a built-in EQ toolset for peak audio performance.</p>

<p><br />
<strong>VIZIO INTERNET APPS(TM) (VIA)</strong></p>

<p>All of the XVT Pro sets feature the VIZIO INTERNET APPS(TM) (VIA) Connected HDTV platform, delivering unprecedented choice and control of web-based content directly to the television without the need of a PC or set-top box. Able to connect to the Internet using the built-in high definition wireless (802.11n dual-band) or a wired connection, accessing on demand movies, TV episodes, music and other online content is easy, using the included Bluetooth universal remote control with sliding QWERTY keyboard that makes thumb-typing easy.</p>

<p><br />
<strong>58" Cinema Wide Display</strong></p>

<p>Taking a major step forward in enabling viewers to experience a fully immersive widescreen film experience without a separate projector and screen, VIZIO's first Cinema Wide Display, the XVTPRO580CD offers the ultimate experience for the movie enthusiast. This 58" Razor LED display has a 21x9 aspect ratio, with an incredible resolution of 2560 x 1080p, allowing consumers to view 2.35:1 "Scope" aspect ratio films using the entire display area, with no loss of resolution and no black bars. With 1 million to 1 Mega Dynamic Contrast Ratio(TM), Smart Dimming(TM), and 120 Hz with Smooth Motion technology, the XVTPRO580CD delivers brilliant details and rich colors to bring the cinematic experience into the home.</p>

<p>Its striking wide appearance is further enhanced by its brushed aluminum chassis. The Cinema Wide Display also includes VIA, built-in wireless (802.11n dual-band) or wired networking, and a Bluetooth universal remote control with sliding QWERTY keyboard. It is expected to ship later this year.</p>

<p>Demonstrations of the VIZIO XVT Pro products and the VIA platform will be held by appointment for key business partners, analysts and press in Las Vegas during private CES press meetings January 6th through 9th at the Wynn Hotel's, Chambertin ballrooms. Interested content and service partners should contact VIZIO at partners@vizio.com or call (949) 428-2525 ext. 2554.</p>

<p><br />
<strong>About VIZIO</strong></p>

<p>VIZIO, Inc., "Where Vision Meets Value," headquartered in Irvine, California, is America's HDTV and Consumer Electronics Company. In 2007, VIZIO skyrocketed to the top by becoming the #1 selling brand of flat panel HDTVs in North America and became the first American brand in over a decade to lead major categories in U.S. TV sales. Since 2007 VIZIO HDTV shipments remain in the TOP ranks in the U.S. and were again #1 in Q1, 2009 with over 20% market share. VIZIO is committed to bringing feature-rich flat panel televisions to market at a value through practical innovation. VIZIO offers a broad range of award winning LCD HDTVs including the new XVT series. VIZIO's products are found at Costco Wholesale, Sam's Club, Sears, Walmart, Target, BJ's Wholesale, and other retailers nationwide along with authorized online partners. VIZIO has won numerous awards including a #1 ranking in the Inc. 500 for Top Companies in Computers and Electronics, Good Housekeeping's Best Big-Screens, CNET's Top 10 Holiday Gifts and PC World's Best Buy among others. For more information, please call 888-VIZIOCE or visit on the web at <a target="_blank" href="http://www.VIZIO.com/">www.VIZIO.com</a>.</p>

<p>The V, VIZIO, TruLED, Extreme VIZIO Technology XVT, VIZIO Internet Apps, 480Hz SPS, 240Hz SPS, Full HD3D, Thin Line, Smooth Motion, Razor LED, Smart Dimming, Where Vision Meets Value names, phrase and symbols are trademarks or registered trademarks of VIZIO, Inc. All other trademarks may be the property of their respective holders.</p>

<p>Source: VIZIO, Inc.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>January  5, 2010  6:08 AM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(3457)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 3457)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author['bio_short'] != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Shane Sturgeon</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2010/01/vizio-launches-new-xvt-protm-series-of-advanced-hdtv-technology.php" type="text/javascript" charset="utf-8"></script>
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
