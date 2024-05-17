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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 207 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 207
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=2005 HDTV Report, Part 1: CES Highlights&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/articles/2005/09/2005-hdtv-report-part-1-ces-highlights.php&amp;title=2005 HDTV Report, Part 1: CES Highlights">
		<span style="display:none">CES showed that 2005 will be the year when 1080p display started to compete.  Perhaps, content and distribution will equally be motivated to reach that level of quality, such as Hi Def DVD for late 2005 / early 2006.  More efficient compression algorithms like MPEG-4 AVC are making possible not only High Definition DVD, but also the expansion of HD satellite services and more HD channels, as it was recently announced by DIRECTV, Dish Network, and Voom.

Should quality be an objective (rather than multicasting DTV SD channels), the more efficient MPEG-4 compression has the potential to facilitate the distribution of 1080p content (at 60 frames x second) using a similar bandwidth allocated for today's 1080i (30 frames per second interlaced as 60 fields).

Although the ATSC standard does not include that level of quality, the potential could be applied for other services than over-the-air.  New 1080p sets, if designed to accept a 1080p signal, would be in a good position to display at that potential.  Read more in the highlights and later on the report.

In 2005, panel prices will come down at a faster rate relative to other types of displays, and panels will be more common at larger sizes, such as 70+ and 80-inches plasmas, and even a oversized 102&quot; model expected within two years.  LCD panels are joining the 40&quot; plus domain of the plasmas, with 40 to 65 inches from many manufacturers.  CES unveiled a good number of these oversized panels.  HD-DVRs are becoming to appear integrated within some TVs and plasmas, small portables, in addition to HD-STBs for cable, over the air, and satellite.

The report includes a large number of new products and technologies; the following pages are just a highlight of CES 2005:</span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 207";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download 2005 HDTV Report, Part 1: CES Highlights" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="2005 HDTV Report, Part 1: CES Highlights" />
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
	<title>HDTV Magazine - 2005 HDTV Report, Part 1: CES Highlights</title>
	<meta name="keywords" content="definition dvd, dvi hdmi, high definition, dish network, integrated atsc, ces, dvd, new, integrated, content, samsung, atsc, shown, hdmi, dvr, ttm, panels, tuners, video, lcd, dvi, set, announced, introduced, tuner" />
	<meta name="description" content="CES showed that 2005 will be the year when 1080p display started to compete.  Perhaps, content and distribution will equally be motivated to reach that level of quality, such as Hi Def DVD for late 2005 / early 2006.  More efficient compression algorithms like MPEG-4 AVC are making possible not only High Definition DVD, but also the expansion of HD satellite services and more HD channels, as it was recently announced by DIRECTV, Dish Network, and Voom.

Should quality be an objective (rather than multicasting DTV SD channels), the more efficient MPEG-4 compression has the potential to facilitate the distribution of 1080p content (at 60 frames x second) using a similar bandwidth allocated for today's 1080i (30 frames per second interlaced as 60 fields).

Although the ATSC standard does not include that level of quality, the potential could be applied for other services than over-the-air.  New 1080p sets, if designed to accept a 1080p signal, would be in a good position to display at that potential.  Read more in the highlights and later on the report.

In 2005, panel prices will come down at a faster rate relative to other types of displays, and panels will be more common at larger sizes, such as 70+ and 80-inches plasmas, and even a oversized 102&quot; model expected within two years.  LCD panels are joining the 40&quot; plus domain of the plasmas, with 40 to 65 inches from many manufacturers.  CES unveiled a good number of these oversized panels.  HD-DVRs are becoming to appear integrated within some TVs and plasmas, small portables, in addition to HD-STBs for cable, over the air, and satellite.

The report includes a large number of new products and technologies; the following pages are just a highlight of CES 2005:" />
	<meta name="title" content="2005 HDTV Report, Part 1: CES Highlights" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/articles/2005/09/2005-hdtv-report-part-1-ces-highlights.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=207', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2005/09/2005-hdtv-report-part-1-ces-highlights.php">2005 HDTV Report, Part 1: CES Highlights</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>September 28, 2005</b>
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
				<blockquote>This is the first in a series of articles taken from the <b>H/DTV Technology Review & CES 2005 Report</b> by Rodolfo La Maestra, published in March 2005. If you are interested in downloading the full version of this report, it is currently available for purchase from our <a href="/store/ces-2005.php">CES Report</a> page.</blockquote>

<p><br />
<h2>During the Year, Before CES</h2><br />
The High Definition DVD format wars and the Hollywood Studios showing their non-exclusive support to either side have started to become more public, and worrisome.  The first large LCD panels were shown last year at CES 2004, and by year-end 1.4 million LCD panels were sold; 290,000 LCD panels were sold in October alone.  2004 was also the starting year of the market attraction for panels.</p>

<p>2004 also showed considerable public interest in H/DTV with 7 million sets sold, 14 million sets were sold in total since November 1998, representing 23 billion dollar sales; 4 million of those were sold on the first four years (99-02), another 3 million in 2003.  In summary, 2004 sold the same as 99-03 together.</p>

<p>The last two years were remarkable for the growth; it appears that consumers are becoming more interested in H/DTV for the purpose of watching TV, not as the interest of early adopters in experimenting with the groundbreaking technology between 1998-2002.</p>

<p><br />
<h2>After CES</h2><br />
CES showed that 2005 will be the year when 1080p display started to compete.  Perhaps, content and distribution will equally be motivated to reach that level of quality, such as Hi Def DVD for late 2005 / early 2006.  More efficient compression algorithms like MPEG-4 AVC are making possible not only High Definition DVD, but also the expansion of HD satellite services and more HD channels, as it was recently announced by DIRECTV, Dish Network, and Voom.</p>

<p>Should quality be an objective (rather than multicasting DTV SD channels), the more efficient MPEG-4 compression has the potential to facilitate the distribution of 1080p content (at 60 frames x second) using a similar bandwidth allocated for today's 1080i (30 frames per second interlaced as 60 fields).</p>

<p>Although the ATSC standard does not include that level of quality, the potential could be applied for other services than over-the-air.  New 1080p sets, if designed to accept a 1080p signal, would be in a good position to display at that potential.  Read more in the highlights and later on the report.</p>

<p>In 2005, panel prices will come down at a faster rate relative to other types of displays, and panels will be more common at larger sizes, such as 70+ and 80-inches plasmas, and even a oversized 102" model expected within two years.  LCD panels are joining the 40" plus domain of the plasmas, with 40 to 65 inches from many manufacturers.  CES unveiled a good number of these oversized panels.  HD-DVRs are becoming to appear integrated within some TVs and plasmas, small portables, in addition to HD-STBs for cable, over the air, and satellite.</p>

<p>The report includes a large number of new products and technologies; the following pages are just a highlight of CES 2005:</p>

<p>LCD-TV Panels will be coming in 57" from Samsung, TTM Jun 05 ($16000), 1920x1080p, integrated with ATSC/QAM CableCARD tuners, 1000:1 CR, DNIe, AnyNet home-networking, 600 cd/m2 brightness, 6.2 million color capacity, response time faster than 8 ms, an improvement from the 12 ms of their 46" model 468W introduced 6 months ago, not to mention the comparable improvement over the 20ms+ of many other LCD products.</p>

<table align="center"><tr><td>
	<img src="/articles/images/mt/image004.jpg" alt="Samsung 57 inch LCD-TV">
</td><td>
	<img src="/articles/images/mt/image005.jpg" alt="Samsung 57 inch LCD-TV">
</td><tr><tr><td colspan="2" align="center">
	Samsung 57" LCD-TV
</td></tr></table>

<p>Below on the right, is the impressive prototype shown at CES of the Sharp AQUOS 65" LCD-TV panel, TTM 2H05, $TBA, 1920x1080, integrated ATSC/QAM CableCARD tuners, Quick Shoot video circuitry for 12 milliseconds response time, HDMI, IEEE1394, and DVI-I.</p>

<table align="center"><tr><td>
	Below, LG's 55" LCD RU-55LP10, $TBA, TTM May 05, 1080p, ATSC/QAM/NTSC tuners.<br clear="all">
	<img src="/articles/images/mt/image006.jpg" alt="LG's 55 inch LCD RU-55LP10, 1080p, ATSC/QAM/NTSC tuners">
</td><td align="center">
	<img src="/articles/images/mt/image007.jpg" alt="Sharp AQUOS 65 inch LCD-TV"><br clear="all">
	Sharp AQUOS 65" LCD-TV
</td><tr></table>

<p>Sony introduced the Black 80" Front Projection Screen (HCS-W80, $2000, TTM summer 05) designed for daylight operation, reflecting only red, green, and blue wavelengths with a 2.1 gain, and absorbing all ambient light in the room.</p>

<table align="center">
	<tr><td align="center">
		<img src="/articles/images/mt/image008.jpg" alt="Sony Black Screen HCS-W80">
	</td><td align="center">
		<img src="/articles/images/mt/image009.jpg" alt="Sony Black Screen HCS-W80">
	</td><tr>
	<tr><td colspan="2" align="center">Sony Black Screen HCS-W80</td></tr>
</table>

<p>A number of new 1080p microchip 70" RPTVs was shown in DLP, D-ILA, and SXRD technologies. These HDTVs upconvert 480i/1080i signals to their native 1920x1080p resolution, they do not accept 1080p.  Some of the RPTVs shown were: Samsung HLR7078W DLP (TTM Jul 05, $8,000), TI DLP (concept demo, not for sale), Sony LCoS SXRD KDS-70Q006 (TTM Jan 05, $13,000), JVC LCoS D-ILA HD-70FH96 (TTM Sep 05, $9,000), LG had another set as well, etc.</p>

<p>I used the opportunity to perform some recurrent viewing over the four days at CES.  I found the TI set as the best of the group (but it is not for sale), followed closely by JVC's D-ILA, but not as closely by Sony and Samsung in a tie, although the last two were not shown in dedicated rooms as the first two.</p>

<p><img src="/articles/images/mt/image010.jpg" align="left" alt="TI Demo Unit">TI built and calibrated their demo set (left) as a demonstration of the level of quality that other manufacturers could also reach using the xHD3 technology, if also using good optics, engines, color wheel, etc.</p>

<p>JVC's D-ILA produced crisp images with excellent blacks, whites, good rendering of all colors, well resolved image, and faithful to fast motions of dynamic scenes.<br />
<br clear="all"><br />
<img src="/articles/images/mt/image012.jpg" align="left" alt="Samsung 70 inch DLP">The Samsung 70" DLP set to the left was also shown in an open area.</p>

<p><img src="/articles/images/mt/image011.gif" align="right" alt="Sony SXRD">The Sony SXRD on the right was shown in a large open room; it appeared as the contrast, sharpness, and edge enhancements were excessive; some artifacts could be attributed to the source material.</p>

<p><img src="/articles/images/mt/image013.jpg" align="right" alt="Sony Qualia">It seemed that the Sony set was not ISF calibrated, and that it could have shown better in a more appropriate environment; the set deserves another viewing opportunity.  Sony includes this set now as part of the QUALIA line for $13,000; the set was initially introduced a few months ago as part of the XBR line for $10,000.<br />
<br clear="all"></p>

<p><img src="/articles/images/mt/image014.jpg" align="left" alt="1080p vs. 720p by Samsung">A 1080p vs. 720p demonstration was made by Samsung (left). New 1080p TVs include proprietary de-interlacers that usually are not as good as external scaler/video processors. Considering that in the near future there might be 1080p sources, such as High Definition DVD for film content, the feature of accepting a 1080p input might become important for a TV of this level at that time. Accepting 1080p externally will also permit the connection of a higher-quality 1080p video processor, such as Faroudja, DVDO HD+, and Lumagen video processors, or the soon to be available "Dragon Fly" scaler/noise reduction processor.  The unit implements the new Silicon Optix/Teranex "Realta" HQV (Hollywood Quality Video) chip, a programmable DSP that can perform one trillion operations per second.</p>

<p>The new HQV chip upconverts 1080i to 1080p at up to 120 fps, has received four awards since its introduction in 4Q04, and the Best of Innovations CES 2005 award.  The technology HQV was developed by Teranex, a company that was later acquired by Silicon Optix.  HQV has been used in professional products costing $60,000 and up.  It employs pixel-by-pixel processing, scaling, detail enhancement, and is fully programmable and capable to receive future firmware upgrades.</p>

<p>Ray Lego, Product Manager of Teranex Business Unit at Silicon Optix Inc, and by Menno Stoffels, Director of Systems Engineering of Silicon Optics Canada Inc. demo the product with impressive results on video comparisons at CES 2005.</p>

<p><img src="/articles/images/mt/image015.jpg" align="left" alt="HQV chip">The chip will be offered to interested manufacturers.  According to Mr. Michael Poirier, VP of Sales and Marketing of Algolith, the manufacturer of the "Mosquito" product, the company is working to release a scaler/noise reduction processor mentioned above by Jun/Jul 05 at a $3499 MSRP.  The unit outputs 1080p/60 frames x second (from 480i to 1080i inputs), but would probably output also 24 and 30fps, if added into the design by the time the unit is released to the market.</p>

<p>The High Definition DVD war is warming up and CES has shown units from most major companies, such as JVC, LG, Panasonic, Philips, Pioneer, Samsung, Sharp, and Sony with their HD Blu-ray DVD ROM players and BD-RE recorders/players; and Toshiba and Sanyo their HD DVD format versions.</p>

<p>Over the last 4 years, many prototypes were shown at CES but no production unit was released within the US market yet, although there are three released in Japan already, and several in China using the EVD format (among other formats they have).  Most HD DVD and Blu-ray manufacturers are now announcing that their products will be introduced by year-end 2005 or early 2006.</p>

<p>Regarding component analog outputs, no confirmation was provided if they would be allowed to carry 1080i HD in addition to DVI and HDMI, the preferred secured uncompressed HD connection.  Nor was confirmed if 1080p would be implemented on discs and players, however, most High Definition DVD companies at CES stated that content originating from film would most probably be recorded as 1080p/24fps in the Hi Def pre-recorded discs, but that decision would be made by the content providers.</p>

<p>Actually, the content would probably be stored as 1080i interlaced with flags to reconstruct the progressive cadence of film for progressive output connections, similar to the technique of regular DVD for 480i and 480p with film content.</p>

<table align="center"><tr><td align="center">
	<img src="/articles/images/mt/image016.jpg" alt="Toshiba HDD + HD DVD Recorder"><br>
	Toshiba HDD + HD DVD Recorder
</td><td align="center">
	<img src="/articles/images/mt/image017.jpg" alt="JVC Blu-ray DVD Combo ROM"><br>
	JVC Blu-ray DVD Combo ROM
</td><tr></table>

<p>Check the details of the High Definition DVD coverage on the dedicated section in page 97.</p>

<p>For its second year, Voom performed the demonstration of their 580 STB/server, now planned for release for mid 2005.  Unfortunately, the DVR was demo with no IEEE1394 outputs, which would have allowed HD D-VHS archival/home-networking maintaining the signal in the digital (compressed) domain.  A year ago at CES 2004, Voom said it would provide such connection.  Apparently, Voom is following the steps of DIRECTV and now Dish Network (covered further down) regarding that matter.</p>

<p><img src="/articles/images/mt/image018.jpg" align="right" alt="Voom HD-STB">Voom HD-STBs (580 and 550) will be MPEG-4 upgradeable via a Voom supplied card that would be inserted on a slot accessible by opening a small plastic door on its right side.  Further MPEG-4 software upgrades would be seamlessly installed as firmware downloads sent thru the satellite dish.</p>

<p>The small client STBs linked to the 580 server DVR using the coax network will not be available until later in 2005, after the DVR 580 is introduced; however, according to Voom, the current Motorola's 550 STBs can operate as network clients controlling the 580 DVR functions remotely.  The recent sale of some of Voom's assets to Dish Network could potentially affect the plans of new products, additional satellites and channels, and MPEG-4 upgrade.</p>

<p>Dish Network announced the future release of a new HD DVR STB, but also confirmed the discontinuation of the famous PVR-921 and the company plans for IEEE1394 connections, a key feature announced over the past 3 years, and a reason many consumers endured the long waiting.</p>

<p>After the PVR921 introduction in 2004, the IEEE1394 feature was never activated as promised; the feature would have permitted D-VHS tape archival of the HD-DVR's stored or tuned satellite content.  The omission of IEEE1394 situates Dish Network on the ranks of Voom and DirecTV regarding that capability, giving an edge to Cable and OTA HD-STBs, which do have IEEE1394 outputs for the archiving of permitted content (under DTCP protection rules).</p>

<p><img src="/articles/images/mt/image019.jpg" align="left" alt="Dish Player DVR942">The new DVR is the Dish Player DVR942 (left), $700 with additional $50 for the dish, TTM Feb 05, 250GB HDD capable of recording up to 25hrs of HD content, or 180 hrs of SD.  It features a dual tuner satellite receiver with 2 TV outputs for multi-room viewing, up to 9 days EPG, records Dolby Digital audio, has an ATSC tuner to record OTA, caller ID with history, 2 USB ports for future use, optical audio out, DVI/HDCP, and component YPbPr outputs.  The STB is to be offered also for lease at $250 initiation fee (the subscriber must return the box at the end of the service).</p>

<p><img src="/articles/images/mt/image020.jpg" align="right" alt="Dish Network portable DVR's; 20GB and 40GB">To mate the DVR942 Dish Network also announced a couple of portable DVRs (right) that can store in their 20GB and 40 GB memories the content transferred from the DVR942, although at SD resolution.  The content cannot be output, is user erasable, and can be played back on their small screens (sizes of 2.2, 4, and 7-inches).  Some units feature inputs of Compact Flash cards, IEEE1394, and USB 2.0 to receive the content.</p>

<p><img src="/articles/images/mt/image021.jpg" align="left" alt="Scientific Atlanta DVR">Scientific Atlanta also showed a prototype of a new STB server DVR model, a networking centerpiece that can connect with other STBs from the company via coaxial cable.  The DVR (model # pending) has a planned release of late 2005, and will be capable of archiving HD content as a file copy format within a Hi Def DVD disc using an internal recorder.  The disc would also be playable on similar models, but not on other players of Hi Def DVD formats (such as Blu-ray or HD DVD).  The STB has a QAM Cable tuner (with expected Cable CARD capability when released) and will be available for distribution via cable companies only; it features IEEE1394, 160GB, and also records DVD-R/-RW dual layer 8.5 GB discs playable on regular DVD players, if the content is unprotected.</p>

<p><img src="/articles/images/mt/image022.jpg" align="right" alt="DynaFlat SlimFit CRT direct-view TV Series">Samsung, LG, and Toshiba introduced a new technology that allows a CRT direct-view tube to be manufactured with 30% less depth, Samsung calls it "SlimFit" and already announced a new line of sets called "DynaFlat SlimFit CRT direct-view TV Series", the first set of that line will be a 30" TX-R3079WH, $1,300, TTM Mar 05, 15.5 inches deep, w/integrated ATSC tuner.  LG also announced a similar 30" ATSC integrated set for later 2005.</p>

<p>New lines of CRT sets were introduced by several companies, Thomson in particular announced seven new sets w/ATSC tuners that display 480i SD, intended for second rooms; its 27" integrated entry level will be offered at just $269.  The company also announced a new line of CRT based RPTVs with ATSC integrated tuners at a price starting at $1,100 (52", 56" and 61" screen sizes).</p>

<p>If you are into video games, you might have interest on a 3-D projection system designed for that purpose; a 3-D RPTV prototype demonstration from LG was shown at CES as a technology statement using a Stereoscopic Projection System.  Check the LG displays group for details.  Radio Shack and Sears introduced their own HDTV sets in DLP and LCoS respectively, look into the corresponding sections for specifications, prices, and availability.</p>

<p>Regarding large plasma panels, Samsung introduced large plasmas up to 80 inches (HPR8072, $39,000, 1920x1080p, TTM May 05, integrated ATSC/QAM Cable CARD tuners) and a 102" prototype model announced as the largest TV in the world (Z102, 1920x1080, TTM two years, $80,000-$90,000 estimated MSRP)</p>

<table align="center"><tr><td align="center">
	<img src="/articles/images/mt/image024.jpg" alt="Samsung 80 inch Plasma HPR8072"><br>
	Samsung 80" Plasma HPR8072
</td><td align="center">
	<img src="/articles/images/mt/image023.jpg" alt="Samsung 102 inch Plasma Z102"><br>
	Samsung 102" Plasma Z102
</td><tr></table>

<p>LG unveiled their 71" plasma model MW-71PY10, TTM Feb/Mar 2005, $75,000, 1920x1080p, integrated ATSC/NTSC/QAM CableCARD tuners, 800 cd/m2 brightness, 1200:1 CR, DCDi, HDMI, DVI, component.  These panels are manufactured in limited numbers (5000) and will be distributed in Chicago, Los Angeles, and New York; according to LG, there is already a 3-month waiting list.  The same model is also made in gold finish and paired with a gold finished audio system, the system is offered in Korea for $100,000.  A 76" model demo at CES 2004 was not shown this time, and apparently has been dropped from the line.  LG and Samsung are the only companies competing in the 70+ inches 1080p market.</p>

<p>LG is incorporating a 160GB DVR in future plasmas (50 to 60 inches) expected by Mar/Apr 05, with integrated ATSC/NTSC/QAM CableCARD tuners, EPG, XG engine, four burn-in prevention processes, 1366x768, IEEE1394, and HDMI/HDCP, check the details in the plasma section.</p>

<p>Read about Toshiba's new SED technology intended to compete with today's panels, examine how it works in the first section of displays (page 32); the company will release a 36" and a 50" model, the latter will be available late 2005 or early 2006 with a resolution of 1920x1080, no prices were disclosed yet.  The new technology will offer a performance similar to CRT implemented in a panel of only a few centimeters of thickness, and would be able to operate as fast as 1 millisecond of response time, with 8600:1 CR; prices are expected to compete with LCD panels of similar sizes.</p>

<p>Across the street from the Las Vegas Convention Center at CES 2005 the much anticipated SONY HD Truck was waiting for the delights of the visitors that wanted to see a bit more than consumer products for HDTV.  Below are some photos to give you an idea of the professional HD equipment what was on that truck:</p>

<table align="center">
	<tr><td align="center">
		<img src="/articles/images/mt/image025.jpg">
	</td><td align="center">
		<img src="/articles/images/mt/image026.jpg">
	</td><tr>
	<tr><td align="center">
		<img src="/articles/images/mt/image027.jpg">
	</td><td align="center">
		<img src="/articles/images/mt/image028.jpg">
	</td><tr>
	<tr><td align="center">
		<img src="/articles/images/mt/image029.jpg">
	</td><td align="center">
		<img src="/articles/images/mt/image030.jpg">
	</td><tr>
</table>

<p>If you have followed my yearly coverage on the subject of digital connectivity, you might already know that the number of HDMI/DVI inputs in most HDTV displays is still insufficient to connect several components with DVI/HDMI outputs, reason by which you would need to use a DVI/HDMI switcher or an A/V receiver or Preamp/Processor to perform that function.</p>

<p>Every year I review at CES how manufacturers of consumer electronics are implementing DVI and HDMI on future home-theater equipment.  I look mainly into A/V receivers because they are what most people use when centralizing the control of their video/audio system, but I also include preamps/processors for the benefit of the separates-oriented consumers.  The future models are covered on the section dedicated to it later on the report (page 113).</p>

<p>At CES, it was noticeable that manufacturers of digital video products are broadly adopting DVI and HDMI connections, such as HDTV displays, HD-STBs, and DVD players with upconversion to HD, etc.  Read also about the details of how DVI and HDMI with HDCP were designed for the transmission of protected digital uncompressed HD video, with the added digital multi-channel audio feature of HDMI, all the coverage in the digital connectivity section (page 116).  A separate section unveils the subject of the recent claims that HDMI is only being implemented as two-channel audio (page 120).</p>

<p><img src="/articles/images/mt/image031.jpg" align="left" alt="Denon AVR3000">Although showing an increase from last CES 2004, CES 2005 introduced only a few future A/V receivers with such feature, and those are still top-end (and expensive) models.  Some from Denon ($3000 to $6000), Integra Research ($4000), JVC ($3500), Samsung ($5000), etc.  Check the details and specifications of those new products, and some of the DVD upconversion players with HDMI and DVI capabilities on the same section later in the report.</p>

<p>Read the coverage about the Broadcast Flag content protection mandate by the FCC; how is being implemented, and what systems were included within the 13 digital content protection technologies approved by the FCC so far, such as CPRM (4C for SD sources), D-VHS (JVC), VCPS (Vidi, developed by Philips and HP for recording DTV broadcast into DVD+R/RW discs), etc.</p>

<p>The report also has a dedicated section to cover the subject of how integrated DTVs that include a QAM CableCARD tuner actually operate only with uni-directional capabilities, which could force a consumer to lease a (bi-directional) HD-STB to been able to use the services of Video-On-Demand (VOD), Impulse-Pay-Per-View (ordering PPV movies using the remote, rather than the phone), and Cable company's Electronic Program Guide (EPG).</p>

<p>In summary, depending how a consumer receives the HD signals, he/she might be forced to pay for redundant or duplicated tuners; most consumers would not have enough information to notice were their additional money went on the integrated solution, unless they read reports like this one.</p>

<p>Check also in the report, how tuner integration is being implemented to comply the mandate of the FCC.  CES has shown a massive effort from manufacturers in installing ATSC and QAM Cable CARD tuners in most large TV sets, and it will become unusual to find a monitor version when you find the DTV set you like.  As covered in my earlier articles, the price of integrated tuners is expected by most in the industry to come down considerably and soon, but unfortunately, the 2005/6 lines are not showing a considerable drop yet.</p>

<p>There was an average $704 extra on the 2004/5 lines announced a year ago, and is now in the $500 range on the 2005/6 lines.  Samsung announced in 2004 that their integrated versions (Series 67 expected for Apr 05) would be priced $500 above the monitor versions (Series 63 released in Jun 04), and Toshiba showed a mark up of $400 for the integrated tuners on some of their newer models.</p>

<p>On the other hand, as mentioned above, Thomson was able to introduce a 480i SD integrated 27" TV with an ATSC tuner for just $269 MSRP.  Granted the integrated tuner outputs only 480i SD for the TV to operate at that resolution, but for the tuner to be DTV compliant it must be able to tune to the 18 ATSC formats.  The inclusion of such tuner in a $269 consumer product (that is also a TV) shows that is actually possible, today, to bring down the cost of integration to much lower levels than the $500 average.</p>

<p>Be sure that you read the next article in the series: <a href="http://www.hdtvmagazine.com/articles/2005/09/2005_hdtv_repor.php">HDTV Implementation Update</a></p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>September 28, 2005 10:03 AM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(207)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 207)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2005/09/2005-hdtv-report-part-1-ces-highlights.php" type="text/javascript" charset="utf-8"></script>
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
