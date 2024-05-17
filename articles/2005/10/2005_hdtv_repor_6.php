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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 218 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid
	FROM aux_mt_entry a, phpbb_topics t
	WHERE a.entry_id = 218
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
		$pg_mlink = '<script language="javascript" type="text/javascript" 	src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row_pg['masterid'] .'&keyword=2005 HDTV Report, Part 7: DLP RPTVs and FPTV Projectors&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=0&l=20&spic=1&ssbox=1"></script>';
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
		<a class="DiggThisButton DiggMedium" rel="external" href="http://digg.com/submit?related=no&amp;url=http://www.hdtvmagazine.com/articles/2005/10/2005-hdtv-report-part-7-dlp-rptvs-and-fptv-projectors.php&amp;title=2005 HDTV Report, Part 7: DLP RPTVs and FPTV Projectors">
		<span style="display:none">This part 7 includes DLP RPTV's and FPTV projectors (monitors), and a brief summary of TI's efforts on the technology, which complements the DLP coverage of the 2004 report.  DLP has now reached 1080p image resolution levels, and many 1080p RPTV's were recently released by several main stream manufacturers, although some argue that the method used by TI is not true 1080p (without an actual 1920x1080 chip).  Additionally, most 1080p RPTV's (if not all) do not (yet) accept 1080p from an external source.  Separately, 1080p chips for FPTV's were recently announced and many front projection HT enthusiasts are awaiting to see the release of the first 1080p projectors implementing the chip. The 1080p DLP technology is also a good match with the soon to be available High Definition DVD, and with the D-VHS media available today. Consumers would be able to view 1080i/p
high resolution content on display devices that would not scale down that high resolution (such as 720p DLPs do).  Owning true 1080p-software content, and been able to display it to its full resolution, should dramatically accelerate HDTV adoption, not necessarily for the reason of TV, but for experiencing high quality video in your own HD home theater.  When reviewing the MSRP and TTM (time to market) information keep in mind that is as of 1Q05 for the CES 2005 report and, as is usual with CE, prices and availability might change when products are actually released; additionally, street sale prices should be expected to be lower than the MSRP data used throughout the report.</span></a>
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 218";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download 2005 HDTV Report, Part 7: DLP RPTVs and FPTV Projectors" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="2005 HDTV Report, Part 7: DLP RPTVs and FPTV Projectors" />
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
	<title>HDTV Magazine - 2005 HDTV Report, Part 7: DLP RPTVs and FPTV Projectors</title>
	<meta name="keywords" content="ansi lumens, dvi hdcp, color wheel, atsc qam, integrated atsc, ttm, chip, dvi, lumens, dlp, hdmi, integrated, hdcp, ces, rptv, series, models, ansi, component, atsc, color, rptvs, qam, tuners, fptv" />
	<meta name="description" content="This part 7 includes DLP RPTV's and FPTV projectors (monitors), and a brief summary of TI's efforts on the technology, which complements the DLP coverage of the 2004 report.  DLP has now reached 1080p image resolution levels, and many 1080p RPTV's were recently released by several main stream manufacturers, although some argue that the method used by TI is not true 1080p (without an actual 1920x1080 chip).  Additionally, most 1080p RPTV's (if not all) do not (yet) accept 1080p from an external source.  Separately, 1080p chips for FPTV's were recently announced and many front projection HT enthusiasts are awaiting to see the release of the first 1080p projectors implementing the chip. The 1080p DLP technology is also a good match with the soon to be available High Definition DVD, and with the D-VHS media available today. Consumers would be able to view 1080i/p
high resolution content on display devices that would not scale down that high resolution (such as 720p DLPs do).  Owning true 1080p-software content, and been able to display it to its full resolution, should dramatically accelerate HDTV adoption, not necessarily for the reason of TV, but for experiencing high quality video in your own HD home theater.  When reviewing the MSRP and TTM (time to market) information keep in mind that is as of 1Q05 for the CES 2005 report and, as is usual with CE, prices and availability might change when products are actually released; additionally, street sale prices should be expected to be lower than the MSRP data used throughout the report." />
	<meta name="title" content="2005 HDTV Report, Part 7: DLP RPTVs and FPTV Projectors" />
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
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/articles/2005/10/2005-hdtv-report-part-7-dlp-rptvs-and-fptv-projectors.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=218', 340, 125);">Add ASIN</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2005/10/2005-hdtv-report-part-7-dlp-rptvs-and-fptv-projectors.php">2005 HDTV Report, Part 7: DLP RPTVs and FPTV Projectors</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>October 16, 2005</b>
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

<p>In professional products, DLP Cinema technology is now installed in over 250 commercial movie theaters in the world.  Some of the DLP projectors used on those installations are made by Barco, Christie, Digital Projection, and NEC in the 1400x1050 and 1600x1200 resolutions.  More than 130 movies have been digitally released.</p>

<p>TI intends to reduce the conversion costs of theaters to DLP, estimated in the range of $100,000 to 150,000, by involving producers and film distributors to help in the transition.  One of the ideas is to charge theaters a fee for existing movies and create a fund to be used in the conversion investment that movie theaters are reluctant to afford.</p>

<p>Regarding consumer products, TI informally declared that their plans for the xHD3 1080p DMD chip are that it will stay as is, but it will receive some gradual improvements in the future, no details of what and when were provided.</p>

<p>The following are some of the latest innovations implemented by TI:</p>

<p>DynamicBlack&trade;: Dynamically optimizes picture quality, providing deeper black levels with incredible detail in dark scenes and a contrast ratio of 5000:1.</p>

<p>DarkChip2&trade;: The next generation of DLP's widely acclaimed</p>

<p>DarkChip&trade; technology; offers dramatically increased contrast ratio to provide increased depth, picture sharpness, and true blacks and whites</p>

<p>SmoothPicture&trade;: Combined with our cutting-edge third generation of 720p and 1080p chips, it offers the ultimate in picture quality, providing a smooth, seamless image</p>

<p>HD2+: The latest enhancement to HD2 product line, it offers DarkChip2&trade; which enhances contrast for rich and detailed dark scenes</p>

<p>HD3: The next generation DLP chip, offers improved contrast and features DarkChip2&trade; and SmoothPicture&trade; technologies</p>

<p>xHD3: The first in the x-series of products, it offers 1080p resolution and the finest in picture quality with DarkChip2&trade; and SmoothPicture&trade; technologies</p>

<p>In May 2004, TI provided some technical detail of how the new HD3 chips were able to provide the claimed resolution, an issue that raised a number critic comments form DLP and non-DLP enthusiasts that were expecting a chip with a matching array of mirrors for the pixels of image to be displayed, not half of it, as follows:</p>

<p>For 1920x1080p resolution, the .85" xHD3 chip has actually a mirror-array of 960x1080p.</p>

<p>For 1280x720p resolution, the .55" HD3 chip has actually a mirror-array of 640x720p.</p>

<p>Both chips have mirrors angled at 45 degrees so projectors will display half of pixels of the image in 1/120th of a second using the entire array of 960x1080 mirrors and, by shifting the image via a moving mirror, display the other half of pixels in another 1/120th of a second, to total the full 1920x1080 image in two movements with the same 960x1080 mirror-array.</p>

<p>The theory is that, having as objective the building of a 1920x1080 image 60 times per second, it represents a savings if done with a smaller chip in two fast passes because the human eye would see both 1/120th fast half images as one of 1/60th.</p>

<p>At CES 2005, TI indicated that they are going change the naming convention of chips used until now, and refer to the chips as 720p and 1080p.</p>

<p>The 1080p technology is also a good match with the soon to be available HD-DVD (or Blu-Ray, or EVD, or WMV HD), and the D-VHS media available today.  Consumers would be able to view high resolution content with no compromises on the display device.</p>

<p>Although, having now more devices that are able to display the full 1080x1920 resolution, we hope that we will not be subjected to signal quality constraints from multi-casting, satellite and cable over-compression, or from camera and distribution resolution limitations.</p>

<p>Owning true 1080p-software content, and been able to display it to its full resolution, would dramatically accelerate HDTV adoption, not necessarily for the reason of TV, but for experiencing high quality video in your own HD home theater.</p>

<p><br />
<h2>Akay</h2><br />
<u>RPTV</u><br />
46"	PT46DL20	$2300, TTM Nov 04, HD2 chip, ATSC tuner, no CableCARD slot, Circuit City distribution (another 46" set with different cabinet for Costco)</p>

<p><u>CES 2005</u><br />
<u>RPTVs</u><br />
Fully integrated (ATSC/QAM CableCARD tuners) and monitor only options<br />
HD3 chip<br />
50"<br />
52"<br />
56" (not confirmed at CES time)</p>

<p><br />
<h2>BenQ</h2><br />
<u>CES 2005</u><br />
<u>FPTVs</u><br />
PE8260	1024x768, 3200 lumens of brightness, 2000:1 CR, 802.11b for home-networking<br />
PE8720	$11000, TTM Mar 05, 1280x720, 5500:1 CR, 8-segment color wheel, DCDi, 23db low noise level, 800 ANSI lumens, 5 BNC, component</p>

<table><tr><td align="center"><img src="/articles/images/mt/image048.jpg" alt="BenQ PE8720 FPTV"><br>BenQ PE8720 FPTV</td></tr></table>

<p>PE7700	$3300, TTM Mar 05, 720p, 2500:1 CR<br />
All the other HT models (11) are included in the CES 2004 report</p>

<p><u>RPTVs</u><br />
57"	$N/A, TTM N/A<br />
72"	$N/A, 11 inches deep, TTM 2006, TI DMD chip choice not decided yet, 720p probably followed by 1080p later. </p>

<p><br />
<h2>Christie</h2><br />
<u>FPTVs</u><br />
1280x720, CERMAX Xenon lamp 1000 hours, 3-chip<br />
DW3K	3000 ANSI lumens <br />
DW6K	6000 ANSI lumens</p>

<p><br />
<h2>Crystal View</h2><br />
Dec 04<br />
<u>FPTV</u><br />
CV-720HD+	720p, 1200 lumens, DVI, HDMI</p>

<p><br />
<h2>Digital Projection Inc</h2><br />
<u>FPTV</u><br />
IVision HD-7 	$20000, TTM 3Q04, 3000:1 CR, 1000 ANSI lumens, second generation chip, 1280x720</p>

<p><br />
<h2>Dwin</h2><br />
<u>CES 2005</u><br />
<u>FPTV</u><br />
Transvision 4	$6500, TTM April 2005, Dark Chip 2 720p, native-rate outboard included </p>

<p><br />
<h2>HP</h2><br />
Sep 04<br />
<u>FPTV</u><br />
Single chip, TTM Sep 04<br />
ep7120	$3000, screen size 37-110 inches, 850 ANSI lumens, 4000 hrs lamp life, XGA 1024x768, component, DVI/HDCP, VGA for PC</p>

<p>ep9010	$2500, built-in-DVD player, EDTV SVGA (800x600), 2.1 stereo subwoofer system, DVI/HDCP, VGA for PC</p>

<p><br />
<h2>Infocus</h2><br />
June 04<br />
<u>FPTV</u><br />
Model 777 3-chip HD2 prototype stage, $30,000, 12 degree tilt DMD, 2000 lumens, 3000:1 CR, DVI/HDCP, dig keystone correction +- 15 vertical degrees, uses FLI2310 DCDi next-generation Faroudja chip, 44.4 pounds, 30db fan, April 04 unofficial launch event, deinterlacing of 1080i by using 540 of each field to build a 1280x720 frame.</p>

<p><u>CES 2005</u><br />
<u>FPTVs</u><br />
ScreenPlay 7210	<$7000, TTM Feb 05, Dark Chip 3 720p, Carl Zeiss l1.3x zoom  lens, 1400 lumens of brightness, 2800:1 CR<br />
ScreenPlay 4805	$1500, DCDi</p>

<p><u>RPTVs Integrated</u><br />
ATSC/NTSC/QAM Cable CARD tuners, 1280x720, HDMI/HDCP, two IEEE1394/DTCP, AVC networking, TTM current<br />
50"	ScreenPlay 50md10	$7000<br />
61"	ScreenPlay 61md10	$9000, 3000:1 CR</p>

<p>61"	TD61	$11000, 1280x720 HD2+, 1000:1 CR, DVI-I, 15 Pin-Dsub</p>

<p><br />
<h2>LG</h2><br />
<u>RPTVs</u><br />
Slim, HD2+ chip, 1280x720, ten element lens system, DVI/HDCP, 3-2 pulldown, TTM 2Q04, air bearings:<br />
44"	RU-44SZ61D	$4,000 (now $2700)<br />
52"	RU-52SZ61D	$4,500 (now $3200), Cable-Card ready, ATSC tuner, HD2 Mustang chip, 1280x720, HDMI, RGB and 1394 inputs</p>

<p><u>FPTV</u><br />
RD-JT91, SVGA resolution, 1700 ANSI lumens, 2000:1 CR, 25dB noise level, zoom ratio 1.2:1, lamp life 3000 hours, 6.4 lbs.</p>

<p>Sep 04<br />
<u>Integrated RPTVs</u><br />
HD-2 Mustang chip, 1280x720, ATSC/QAM Cable CARD tuners, 120-watt bulb, air bearings color wheel for 50000 hours life (rather than 20000 hrs of ball bearing color wheel, according to LG)</p>

<p>52"	DU-52SZ61D	$3500, TTM fall 04, HDMI, RGB, 1394<br />
62"	DU-62SZ61D	$TBA, TTM fall 04</p>

<p>44"	RU-44SZ63D	$2700, TTM fall 04, HD2+, 1280x720, XD-engine, DVI, HDMI<br />
52"	RU-52SZ51D	$3200, TTM Fall 04, HD2+, 1280x720, XD-engine, DVI, HDMI</p>

<p><u>CES 2005</u><br />
<u>Integrated 720p RPTVs SX4D line</u><br />
1500:1 CR, 1280x720 HD3, IEEE1394, HDMI/HDCP, ATSC/NTSC/QAM Cable CARD tuners, air bearing color wheel<br />
52"	52SX4D	TTM Apr 05<br />
62"	62SX4D	TTM Apr 05</p>

<p><u>Integrated 1080p RPTVs SY2D line</u><br />
QAM cable w/CableCARD and ATSC tuner 5th generation chip, 1920x1080p xHD3 chip, HDMI, RGB, TTM 2005, 3000:1 CR, IEEE1394, air bearing color wheel<br />
52"<br />
56"	56SY2D	TTM May 05<br />
62"	62SY2D	$4500, TTM May 05	</p>

<p> <br />
<h2>Loewe</h2><br />
<u>RPTV monitor</u><br />
55"	Articos 55	$7000 to $8000 depending on finish, Carl-Zeiss lens technology, HD2 chip, 1500:1 CR, DVI/HDCP, optional motorized swivel base ($1000) to reduce reflections changing the angle </p>

<p><br />
<h2>Marantz</h2><br />
Sep 04 (CEDIA introduction)<br />
<u>FPTVs</u><br />
VP-12S4	$13,500, HD2+ chip, TTM end 2004, 3 lenses, HDMI, 1280x720<br />
VP-10S1	3-chip</p>

<p>Marantz VP-12S4 Gennum system was said to convert 1080i inputs to 1080p, and then scales down the 1080p image to 1280x 720.  Other systems are known to converting down 1080i to 540p and then upscale to 1280x720, which is said to be more digital, jerky, and blocky.</p>

<p><br />
<h2>Mitsubishi</h2><br />
April 2004 (company announcement of 2004/5 models)</p>

<p>The company announced the addition of six DLP high-definition sets between 52" and 62" to be available in the period Jul-Sep 04, and one 82" integrated LCoS micro display (Alpha's flagship RPTV).  The sets are integrated with ATSC/QAM CableCARD unidirectional tuners, and have HDMI, and IEEE-1394 digital connections.  These features are also included on most of the other newer sets of the 2004/5 lines.  In total, the company is adding 18 new integrated ATSC/cable-ready models. </p>

<p>According to the product development director, Mitsubishi designed a proprietary light engine using the 0.85-inch DMD chip on their new DLP additions, a return from their original 65" DLP introduction in 2000 (MSRP $15,000 at that time), the company said.  Diamond models will be distributed by A/V specialists, Medallion models by major accounts.</p>

<p><u>525 DLP Series Integrated RPTVs</u><br />
TTM Jul/Aug 04, NetCommand 4.0 networking with Learning Media Command, "AMVP2" motion video processing<br />
52"	WD-52525	$4200<br />
<img src="/articles/images/mt/image049.jpg" alt="Mitsubishi HDTV"><br />
62"	WD-62525	$5000</p>

<p><u>Medallion 725 DLP Series Integrated RPTVs</u><br />
TTM Aug/Sep 04, same as 525 features plus TV Guide Onscreen IPG, Anti-Glare Diamond Shield<br />
52"	WD-52725	$4500<br />
62"	WD-62725	$5300</p>

<p><u>Diamond 825 DLP Series Integrated RPTVs</u><br />
TTM Aug 04, 120GB HDD DVR for 12 hours HD, 72 hours SD, MPEG SD encoder, Gemstar guide, and subscription free<br />
<img src="/articles/images/mt/image050.jpg" alt="Mitsubishi HDTV"><br />
52"	WD-52825	$5500<br />
62"	WD-62825	$6300 </p>

<p>Oct 04 (InfoComm Trade Show)<br />
<u>FPTVs</u><br />
Single chip<br />
HC900U	$3000, EDTV 1024x576, 4000:1 CR, CineRich color, CineView processing, 1500 lumens, DVI/HDCP, 300-inch screen capability, 6 pounds<br />
HC2000U	1280x720, 700 ANSI lumens, HD2+, 3600:1 CR, 250" max screen size, DVI-I/HDCP, BNC, RGBHV, YPbPr</p>

<p><br />
<h2>NEC</h2><br />
Sep 04 (CEDIA introduction)<br />
<u>FPTVs</u><br />
HT410		$1500, 854x480 (EDTV)<br />
HT510		1024x576, high-performance and low cost, component, and RGB inputs </p>

<p><u>CES 2005</u><br />
<u>FPTVs</u><br />
EDTV resolution, 28 dB noise level, vertical manual lens shift, 1000 ANSI lumens, 1200:1 CR<br />
NEC HT410	$1300, VGA 854x480<br />
NEC HT510	$2000, XGA 1024x576</p>

<p><br />
<h2>Optoma</h2><br />
Apr 04<br />
<u>RPTV</u><br />
50"	RD50+ HD2+ chip, under development, $N/A, 1280x720, DVI, RGB, BNC, 3000:1 CR, 8000 hrs lamp life, 450 nits brightness, IEEE1394 front input (camcorder)</p>

<p>Sep 04 (CEDIA announcement)<br />
<u>Sovereign RPTVs</u><br />
HD2+ Dark Chip 1280x720p, HDMI to DVI adaptor, DVI-port audio cable, ISF calibrated on each port by manufacturer, 1% over-scan, DVI, HDMI, TTM 4Q04, 2500:1 CR, non-reflective screen</p>

<p>50"	SV50XF	$4000, 450 Nits of brightness 	<br />
65"	SV65XF	$6000, 400 Nits of brightness<br />
 <br />
<u>FPTVs</u> (current)<br />
H77	3500:1 CR, 23 DB noise level<br />
H57	1024x576 16:9, six-segment color wheel, 2500:1 CR, VGA, DVI/HDCP, component, DVI to HDMI adaptor for HDMI sources</p>

<p><u>CES 2005</u><br />
<u>RPTV</u><br />
65"	RD65	1280x720, 1500:1 CR, IEEE1394 front input (camcorder)</p>

<p>HD504 Dark Chip 3, 720p, 2500:1 CR, 14.8" depth, 90 pounds</p>

<p><u>FPTVs</u><br />
H79	Dark Chip3, 4000:1 CR, 1000 lumens, 1280x720p native, 8-segment color wheel, DVI-I/HDCP, DVI-I to HDMI adapter, component, 23 dB noise<br />
H77	1280x720, HD2+, 3500:1 CR, 23 dB noise, 900 lumens, DVI-I/HDCP, DVI-I to HDMI adapter, component<br />
H57	1024x576, Dark Chip 2, 3000:1 CR, DVI/HDCP, DVI-I to HDMI adapter, 28 db noise level, 3 component inputs, 1100 lumens<br />
H31	$1500, Dark Chip2, 854x480, 3000:1 CR, DVI/HDCP, 850 lumens, HD scaling enhancement, successor of H30 model, 32 dB noise, component<br />
H27	 $TBA but estimated as $1300, TTM Apr 05, single 0.54-inch DMD Dark Chip 2, EDTV 854x480, 2300:1 CR, 800-1000 lumens of brightness, six-segment color wheel, DVI-D/HDCP, 15-pin D-sub RGB, component </p>

<table><tr><td>
	<img src="/articles/images/mt/image051.jpg" alt="Optoma H31">
</td><td>
	<img src="/articles/images/mt/image052.jpg" alt="Optoma H57">
</td></tr></table>

<p>MovieTime DV10	$1500, 854x480, Dar Chip 2, 4000:1 CR, 7.8 pounds, 1000 lumens, DVD player, 5-watt audio speakers </p>

<p><br />
<h2>Panasonic</h2><br />
Oct 04<br />
<u>FPTVs</u><br />
PT-D5500U/UL	single chip FPTV, WGA, dual lamps, 4500 limens<br />
PT-DW7000U		3-chip, 1366x768, 5000 lumens, pair of UHM lamps<br />
DS+6K		single chip, SVGA, 1400x1050, 6000 lumens<br />
DS+4K		single chip, SVGA, 1400x1050, 4000 lumens<br />
DS-25			single chip, 2500 lumens</p>

<p><u>CES 2005</u><br />
<u>RPTVs</u><br />
50"	PT-50DL54	HD2+, 8-segment color wheel, 2500:1 CR, SD photo card slot<br />
<u>DLX75 Series</u><br />
Two new models with ATSC/QAM Cable CARD tuners, 720p, TTM Aug 05, 2500:1 CR, SD and PCMCIA slots, RGB PC and HDMI/HDCP<br />
56"	PT-56DLX75	<br />
61"	PT-61DLX75</p>

<p><br />
<h2>Projection Design LLC</h2><br />
Oct 04<br />
<u>FPTVs</u><br />
Action! Model Three		$TBD, HD2+ 1280x720, 4000:1 CR, 4000 ANSI lumens, dual bulb design 2x250 watts, 8000 hrs of use, 32db operating noise,</p>

<p>Action! Model One MKII	full upgrade, for 7-segment color wheel, etc</p>

<p><u>CES 2005</u><br />
<u>FPTV</u><br />
F1+	1400x1050, 2500 ANSI lumens, 2500:1 CR, 28 dB noise-level</p>

<p><br />
<h2>Radio Shack</h2><br />
<u>CES 2005</u><br />
<u>FPTV</u><br />
Cinego package <br />
D-1000	$1250, TTM early 05, Carl Zeiss D-9-home optical engine, DVD player and sound system included, </p>

<p><br />
<h2>Runco</h2><br />
Jul 04<br />
<u>FPTVs</u><br />
<u>Video Xtreme Series</u><br />
VX-2c		This unit was announced in the CES 2004 report as TBA and TTM 1Q04, but no further detail could be provided at that time.  The unit now is appearing as 3 x HD2 chips, 16x9 (1280x720), TTM Jul 04, 2500 ANSI Lumens (1199 HT ANSI measured with Runco's Cinema Standards Measurement System, CSMS), 52.1 foot -Lamberts, 3100:1 CR (271:1 measured with CSMS), six lens options for up to 250 inches of screen (5 for zoom, one for RPTV), vertical shift of 60% down and 24% up, horizontal shift from 10 to 16% depending on the lens, DVI,<br />
Scan frequency: 15-100 KHz horizontal, 28-78 Hz vertical, 275Watts UHP lamp (life 2000 hours), and DHD digital video controller. </p>

<p>LiveLink DVI Cable System<br />
The VX-2c is the first projector to use Runco's LiveLink technology for transmitting HD over DVI up to 75 feet (reported up to 120 feet but 75 is the manufacturer spec), supports data rates from 25Mbps to 1.65 Gbps, HDCP compliant, DVI 1.0 compliant supports 720p and 1080i resolutions available in 25, 35, 50 and 75 feet.</p>

<p>The following other projectors are still current, check the CES 2004 report:<br />
VX-4c<br />
VX-6c<br />
CL-710<br />
CL-710LT<br />
VX-1000ci</p>

<p><u>CES 2005</u><br />
<u>FPTVs</u><br />
<u>Video Xtreme Series</u><br />
With Digital Video Controller (DHD) at 720p, DVI/HDCP, component, Vivix Processing<br />
VX-1000D	1280x720 HD2+, 2770/3300: 1 CR, 1500 ANSI lumens<br />
VX-4000D	1280x720 HD2+, 1600 ANSI lumens, 3400/4000:1 CR<br />
VX-5000D	1280x720 HD2+, 1700 ANSI lumens, 4400/5000:1 CR</p>

<p>CineWide technology for 2:35 AR installed in CL-710, and all six projectors above </p>

<p>CL-410	$3500, 1024x576, DVI</p>

<p><br />
<h2>Samsung</h2><br />
Jun 04 (company announcement of 2004/5 models)</p>

<p>A year ago, at CES 2004 Samsung informally projected that in July 2004 they would make available to the consumer a 1080p DLP RPTV line implementing TI's new xHD3 Digital Micromirror Device (DMD), followed by a 1080p DLP FPTV in November.  The line for 1080P RPTV was said to include sizes of 50", 56", and 61", at a projected range of $4000 to $6000.  In June, the company formally confirmed the initial release of the 1080p RPTV line for later this fall to be made available to select A/V retailers, using a new design of their light engine (fifth-generation).</p>

<p><u>97 Series RPTV 1080p Line</u><br />
61"	HL-P6197W	$6500, TTM Nov 04, 3000:1 CR, seven-segment color wheel, integrated ATSC/QAM unidirectional CableCARD tuners, to be distributed through select A/V retailers.  The $6000 projected MSRP upper range (for the larger set), provided in January at CES, was close to June's confirmation.  </p>

<p>In June's announcement no confirmation was issued about the release of the other smaller screens of the line, however, in September Samsung showed at CEDIA (September 04) the 56-inch model of this xHD3 1080p line:</p>

<p>56"	HL-P5697W	MSRP expected to be between $5500/$6000, TTM 1Q05, 3000:1 CR, pedestal base design, integrated with ATSC/NTSC/QAM Cable CARD tuners. </p>

<p>In June Samsung announced the release of nine fully integrated/CableCARD-ready rear-projection HDTV sets, six transitioned as HDTV monitors, three integrated models expected for release between September and November.  Other DLP models will be transitioned to become integrated w/CableCARD to comply with the FCC mandate with the objective of eventually discontinue the manufacturing of HD DLP monitors.</p>

<p>Integrated CableCARD models transitioning from monitor versions are identified with a number 7 on the last digit of the model number (instead of the 3 of the monitor version).  At CES 2004, the company anticipated that integrated versions would be priced $500 above the monitor versions below, expected for later in 2004.</p>

<p><u>63 Series DLP RPTV Monitors</u><br />
Will be distributed by national accounts, TTM Jun/Jul 04, 0.55" HD3 DMD chip with third-generation light engine, 1500:1 CR, improved brightness over 2003's HD2 models, one DVI and one HDMI inputs, two component video inputs, <br />
<img src="/articles/images/mt/image053.jpg" alt="63 Series DLP RPTV Monitors"><br />
46"	HL-P4663W	$3300 (now $2700)<br />
50"	HL-P5063W	$3700 (now $3000)<br />
56"	HL-P5663W	$4200 <br />
61"	HL-P6163W	$4700 (now $3800)<br />
The 56" is for regional accounts, but will also be available to national accounts by special order only</p>

<p><u>70 Series DLP RPTV Monitors</u><br />
Will be distributed by the PRO group and select A/V specialty retailers, 0.85" HD2+ DMD chip with fourth-generation light engine, claimed to have increased switching speed, reduced pivot point and higher reflective surface area for improved contrast (2500:1 CR) and brightness, improved optics and screen designs <br />
46"	$4000<br />
56"	$4500</p>

<p><img src="/articles/images/mt/image054.jpg" alt="Monitor Pedestal DLP RPTV Models" align="right"><br />
<u>Monitor Pedestal DLP RPTV Models</u></p>

<p>TTM Jul 04, HD2+ DMD chip, fourth-generation light engine, HDMI, DVI, PC inputs, component video inputs<br />
50"	HL-P5085W	$4300, distribution by national accounts<br />
56"	HL-P5685W	$5000, distribution by regional accounts </p>

<p><u>Reduction of price on HLN models:</u><br />
In March 2004 Samsung announced a $500 reduction of prices on the earlier 2003 DLP rear-projection HDTV lineup, the 43" entry model HLN4365W is now $3200 MSRP, the 50" HLN5065W is now $3700 MSRP, and the 61" HLN617W is now $4700 MSRP. </p>

<p><u>CES 2005</u><br />
<u>RPTVs</u><br />
The company will expand the line from 8 to 12 models, and anticipating a 12-15% price reduction over the 2004 DLP RPTV lines.<br />
<u>67 Series Integrated</u><br />
ATSC/QAM CableCARD/NTSC tuners, TTM Apr 2005, IEEE1394, 720P<br />
46"	HLR4667W	$2700<br />
50"	HLR5067W	$3000<br />
56"	HLR5667W	$3500<br />
61"	HLR6167W	$3800</p>

<p>Monitor versions of these sets were released mid-2004 as the 63 Series above. <br />
720p RPTV<br />
50"	HLR5087W	$3700</p>

<p><u>1080p Integrated RPTVs</u><br />
ATSC/QAM Cable CARD tuners, all accept 1080p externally, TV-stands $300-$400. </p>

<p>56"	HLR5688W	$5000, TTM Feb/Mar 05, 1920x1080p, 5000:1 CR, DNI-e, IEEE1394.  Apparently, this is the final version of the set announced in the 97 series last year (planned for 1Q05), mentioned at the beginning of the Samsung group</p>

<p><u>Series 68 of integrated 1080P</u><br />
56"	HLR5668W	$4200, TTM Jun 05<br />
61"	HLR6168W	$4500, TTM Jun 05<br />
67"	HLR6768W	$7000, TTM Jun 05, 1920x1080, "floating screen" cosmetic design, 1080p "world largest DLP RPTV" according to Samsung</p>

<p>70"	HLR7078W	$8000, TTM Jul 05, 1920x1080p, integrated with ATSC/QAM Cable CARD tuners, IEEE1394, Gemstar EPG</p>

<table><tr><td align="center">
	<img src="/articles/images/mt/image055.jpg" alt="Samsung 61 inch 1080p HLR6168W RPTV"><br>
	Samsung 61" 1080p HLR6168W RPTV
</td><td align="center">
	<img src="/articles/images/mt/image056.jpg" alt="Samsung 67 inch 1080p HLR6768W RPTV"><br>
	Samsung 67" 1080p HLR6768W RPTV
</td></tr></table>

<p><u>FPTVs</u><br />
SPH-500B	$3500, TTM Feb 05, EDTV 1024x576<br />
SPH-700AE</p>

<p><br />
<h2>Sharp</h2><br />
<u>FPTV</u><br />
XV-Z2000	$4500, TTM Nov 04, 720p, 1200 ANSI lumens, 2500:1 CR, HD2+ 1280x720, DVI/HDCP, component</p>

<p>Sharpvision XV-200U TTM Jan 04(details included in 2004 report)</p>

<p><u>CES 2005</u><br />
<u>FPTV</u><br />
Sharpvision XV-Z12000	$11000, TTM now, 5500:1 CR, 900 ANSI lumens, optical engine co-developed with Minolta, 3-position powered iris control, 1280x720 HD2+, DVI-I/HDCP, film-tone mode, 61-step color temperature adjustment, 33 dB noise</p>

<p>Sharpvision XV-Z10000	1280x720 HD2, 2600:1 CR, 6-segment color wheel, DVI/HDCP, 800 ANSI lumens, 32 dB noise, 300" maximum screen size</p>

<p><u>RPTVs</u><br />
Four new DLP models in two series marking the company's return to RPTV (started w/CRTs)<br />
<u>650 Series</u><br />
Integrated ATSC/NTSC tuners, no cable tuner, 1200:1 CR, 150 degree viewing angles, HDMI, component<br />
56"	56WDR650	$3300, TTM Mar 05<br />
65"	65WDR650	$3800, TTM May 05<br />
<u>750 Series</u><br />
HD4 DLP chip, integrated with QAM CableCARD/NTSC/ATSC tuners, 720p HD4 chip, EPG, $TBA, HDMI, component, 2000:1 CR, DVI-I, two component inputs, Gemstar<br />
56"	56WDR750	TTM 3Q05<br />
65"	65WDR750	TTM 3Q05</p>

<table><tr><td align="center">
	<img src="/articles/images/mt/image057.jpg" alt="Sharp 56 inch 56DR750 RPTV"><br>Sharp 56" 56DR750 RPTV
</td><td align="center">
	<img src="/articles/images/mt/image058.jpg" alt="Sharp 65 inch 65DR750 RPTV"><br>Sharp 65" 65DR750 RPTV
</td></tr></table>

<p><br />
<h2>Sim2</h2><br />
Sep 04 (CEDIA announcement)<br />
<u>FPTVs</u><br />
<u>Grand Cinema HT</u><br />
HD2+ DarkChip3 projectors, 0.8-inch 720p chip with smaller mirror hinges, and reduced gaps between mirrors, DCDi, 3500:1 CR <br />
HT300 E-LINK		$15000, DVI/HDCP, DigiOptic Image Processor<br />
HT300 E		$12000, HDMI/HDCP</p>

<p>3-chip projectors with HD2+ chips <br />
HT500 E-LINK		DVI<br />
HT500 E 		CR 4300:1, DCDi 2nd generation, HDMI</p>

<p><u>RPTVs</u><br />
<u>Domino</u><br />
55M	$7000, TTM Oct 04, HD2, 1280x720, 1800:1 CR, component, HDMI/HDCP, DCDi, 6-segment color wheel, 15.9" depth</p>

<p><u>Grand Cinema RTX</u><br />
HD2 Mustang 16:9, 1280x720, DCDi, outboard processor linked w/fiber optic cable to the RTX set, 16 inputs in outboard, 8000:1 CR, 6000 hours lamp<br />
45"<br />
55"</p>

<p><u>CES 2005</u><br />
<u>RPTV integrated</u><br />
The company returned to RPTV.  Specs and TTM TBA<br />
56"	56DR650<br />
65"</p>

<p><u>FPTVs</u><br />
Demonstrated the HT-500 line<br />
Introduced the EXSX1, 1400x1050, 2500:1 CR</p>

<p><br />
<h2>Studio Experience</h2><br />
Apr 04<br />
<u>FPTV</u><br />
Premiere 30, ship by mid march, $TBA, Matterhorn chip, 2000:1 CR</p>

<p><br />
<h2>Thomson/RCA</h2><br />
May 04 (company announcement of 2004/5 models)</p>

<p>As mentioned on the previous section Thomson join venture with China's CTL (TTE) starting in July 04 will produce for the US market eleven fully integrated ATSC and Digital Cable Ready models with QAM CableCARD unidirectional (seven RCA Scenium DLP models, four RCA CRT RPTV models), with HDMI, and with component inputs.  The new sets are said to recognize the Broadcast Flag.  According to TTE, the company will become the largest company in the world for color TV products; selling 18 million sets annually (with a 22 million production capacity), representing 11% globally. </p>

<p><u>"Profile" Scenium DLP Integrated</u><br />
Ultra-thin two new models, ATSC/QAM CableCARD tuners, TTM Sep 04, 6.85 inches deep and less than 103 pounds make them suitable for wall hanging, hanging bracket sold separately, dual IEEE-1394 two-way/DTCP, TV Guide Onscreen EPG and Internet browser, will detect broadcast flag, HD2 chip, HDMI/HDCP, dual component HD inputs<br />
50"	HD50THW263		$5000 (updated price Jan 05)<br />
61"	HD61THW263		$7000 (updated price Jan 05)<br />
Thomson did not update/confirm the status of the future 70" DLP mural-sized set announced in January at CES (HD70THW263), expected for early 2005, hopefully they on track.</p>

<p><u>Other Scenium DLP RPTV Integrated</u><br />
Five new sets, 16 inches cabinet depth, under 100 pounds, HDMI/HDCP, ATSC/QAM w/CableCARD/NTSC tuners, 160 degree viewing angle, component, HD2 chip, controls DVR functions of optional DVR2080</p>

<p><u>164 Series</u><br />
50"	HD50LPW164	$3100<br />
61"	HD61LPW164	$3700</p>

<p><u>165 Series</u><br />
IEEE-1394, Internet browser, TV Guide EPG, TTM fall 04<br />
44"	HD44LPW165	$3700 (now $3000)<br />
50"	HD50LPW165	$4000 (now $3400)<br />
61"	HD61LPW165	$4600 (now $4000)</p>

<p><u>CES 2005</u><br />
Announced ten new DLP integrated models with ATSC/QAM Cable CARD tuners, HDMI/HDCP ranging from 44" to 61", where the 44" model would be priced below $2000, among them:</p>

<p><u>52 Series</u><br />
50"	HD50LPW52<br />
61"	HD61LPW52</p>

<p><u>62 Series</u><br />
44"	HD44LPW62<br />
50"	HD50LPW62<br />
61	HD61LPW62</p>

<p><u>175 Series</u><br />
50"	HD50LPW175<br />
61"	HD61LPW175</p>

<p><u>167 Series</u><br />
44"	HD44LPW167<br />
50"	HD50LPW167<br />
61"	HD61LPW167 </p>

<p><br />
<h2>Toshiba</h2><br />
May 04 (company announcement of 2004/5 models)</p>

<p>Toshiba announced its 2004-05 television line to dealers.  The new line is mainly oriented to fixed-pixel digital display technologies, such as direct-view LCD TV, plasma, Digital Light Processing (DLP) rear-projection integrated sets and monitors, in addition to CRT-based rear-projection and direct-view products.</p>

<p>In January 2004 (CES), Toshiba announced their decision of discontinuing the LCoS line, which is now replaced by their support to DLP.  In May, Toshiba formally announced the addition of 10 RPTV DLP sets and monitors implementing the pairing of the HD2+ chip of Texas Instruments (0.8-inch Digital Micromirror Device - DMD) with the Toshiba Advanced Light Engine (TALEN).  The company's two-year goal is to position their DLP products within the top-three DLP TV manufacturers, by using advance optics together with the HD2+ DMD chip, as opposed to the HD3 chip-based sets of the competition.</p>

<p>New models of fully integrated HDTV sets include ATSC/QAM tuners with CableCARD slots for unidirectional digital cable ready capability, and TV Guide Onscreen interactive program guides.  Most sets also include both HDMI/HDCP and IEEE-1394/DTCP digital interfaces. </p>

<p><u>DLP RPTVs</u><br />
HD2+ chip, Magic Square Algorithm for smooth color gradation, Dynamic Contrast Enhancer for higher contrast, color purity, and saturation, Super Real Transient and Small Signal Sharpness for sharp transitions from dark to light, Color Transient Improver and Color Detail Enhancer, flat-panel plasma displays type of look, silver cosmetics and thin cabinets, and under-screen glass component cabinets.</p>

<p><u>TheaterWide Series DLP RPTVs</u><br />
Silver cabinets with a gray bezel<br />
<u>HM84 Tabletop Monitors</u><br />
TTM Jul 04<br />
46"	46HM84 	$3000<br />
52"	52HM84 	$3500<br />
62"	62HM84 	$4000</p>

<p><u>HM94 Tabletop Integrated</u><br />
ATSC/QAM CableCARD tuners, IEEE-1394, DTVLink, HDMI, TTM Sep 04<br />
46"	46HM94	$3400<br />
52"	52HM94	$3900<br />
62"	62HM94	$4400</p>

<p><u>Cinema Series DLP RPTVs</u><br />
Same as TheaterWide Series plus cabinetry with black bezel accents, virtual Dolby surround sound, 6-item A/V illuminated remote<br />
<u>HMX84 monitors</u><br />
52"	52HMX84	$3800, TTM Aug 04<br />
62"	62HMX84 	$4300, TTM Sep 04<br />
<u>HMX94 Integrated</u><br />
Two HDMI, TTM Oct 04<br />
52"	52HMX94	$4200<br />
62"	62HMX94	$4700</p>

<p>Note the price difference of $400 for tuner integration as opposed to the previous year higher price difference.</p>

<p>Sep 04 (CEDIA announcement)<br />
<u>FPTV</u><br />
TDPMT200	$1800, EDTV 480p, 2000:1 CR</p>

<p><u>CES 2005</u><br />
<u>FPTV</u><br />
TDPMT800	$8000, HD2+	1280x720, accepts 1080p, Carl-Zeiss lens, DCDi, 7-segment color wheel, 2200:1 CR, 1100 ANSI limens, two component, DVI/HDCP</p>

<p>Two new DLP 1080p engines will be announced on the next spring dealer show.  A second generation of models (Talen X) is planned for introduction during 2005.</p>

<p><br />
<h2>Vidikron</h2><br />
<u>CES 2005</u><br />
Vision Models w/DVI-I/HDCP	</p>

<p><img src="/articles/images/mt/image059.jpg" alt="Vidikron"></p>

<p>20		$5500, TMM Jun 03, HD2, 1024x576, 850 ANSI lumens, 1500:1 CR<br />
25		$6000, TTM Feb05, 1024x756, 900 ANSI lumens, 1600:1 CR<br />
40		$9000, TTM Jun 03, 1280x720 HD2+, 950 ANSI lumens, 1600:1 CR<br />
45		$10000, TTM Feb 05, 1289x720 HD2+, 1000 ANSI lumens, 1700:1 CR<br />
100		$30000, 3-chip, 1280x720, 3500 ANSI lumens, 2000:1 CR, DVI/HDCP</p>

<p><br />
<h2>Viewsonic</h2><br />
Jun 04 (InfoComm introduction)<br />
<u>FPTVs</u><br />
PJ255D	$2000, 2.1 pounds, 1024x768, 2000:1 CR, 1100 lumens<br />
PJ520		$1500, 5.9 pounds, SVGA 800x600, 2000 lumens,<br />
PJ1165	$4300, wall/ceiling installation, 3500 lumens, 800:1 CR, 1024x768</p>

<p><u>RPTV</u><br />
Oct 04<br />
56"	N5600W	1280x720, DDR, HD2+ chip</p>

<p><br />
<h2>V, Inc.</h2><br />
Jun 04<br />
<u>RPTV monitor</u><br />
56"	RP56	$3,000, 1280x720, HD2 chip, 19 inch cabinet, 1500:1 CR, DVI/HDCP, selective color space for PC (0-255) or HD (16-235), dual NTSC tuners, DCDi, PC RGB, 3:2 pull-down</p>

<p><br />
<h2>Yamaha</h2><br />
Sep 04 (CEDIA announcement)<br />
<u>FPTV</u><br />
DPX-1100	$12500, TTM Apr 04, </p>

<p><img src="/articles/images/mt/image060.gif" alt="Yamaha DPX-1100" align="left">HD2+ chip, 1280x720, 2.7-5.0 lens with motorized iris control, 270 Watt UHP lamp with variable power control up to 800 lumens of brightness, 4000:1 CR, DCDi, Faroudja TrueLife Enhancement circuitry, HDMI/HDCP, component with BNC, RGB, RS-232, 30 dB noise level<br clear=all></p>

<p><u>CES 2005</u><br />
FPTV<br />
DPX-1200	$13000, TTM Feb 05, 720 Dark Cip3, 5000:1 CR, 800 lumens, 1.6 motorized zoom lens</p>

<p>Be sure that you read the next article in the series: Plasma Panels (Coming Soon)</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>October 16, 2005  8:22 AM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(218)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 218)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2005/10/2005-hdtv-report-part-7-dlp-rptvs-and-fptv-projectors.php" type="text/javascript" charset="utf-8"></script>
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
