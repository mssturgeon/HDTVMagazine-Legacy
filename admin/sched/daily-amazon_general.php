<?
	set_time_limit(0);
	ini_set('memory_limit', '32M');

	$debug = isset($_GET['debug']);
	if ($debug) {
		header('Content-Type: text/plain');
	}

	// BASE_DIR is the full OS path to the web document directory.  User primarily in include/require statements
	define('BASE_DIR', '/var/www/html');
	require_once(BASE_DIR .'/includes/constants.php');
	require_once(BASE_DIR .'/includes/lib_common.php');
	require_once(BASE_DIR .'/includes/lib_mysql.php');
	require_once(BASE_DIR .'/includes/lib_xml.php');

	# Load admin settings
	$result = mQuery("SELECT * FROM admin_settings");
	$admindata = mysql_fetch_assoc($result);

	/*	Response Groups (2008-11-28)
	*		Large - Accessories, BrowseNodes, ListmaniaLists, Medium, Offers, Reviews, Similarities, Tracks
	*		Medium - EditorialReview, Images, ItemAttributes, OfferSummary, Request, SalesRank, Small
	*		Small - returns basic information about items in a response. The information includes the item's ASIN, DetailPageURL, title, product group, and author.
	*
	*		BrowseNodeInfo - For a given browse node ID, the BrowseNodeInfo response group returns the browse node name and ID of the child and parent browse nodes.
	*		CustomerReviews - For each customer in the response, the CustomerReviews response group returns: ReviewerRank, TotalHelpfulVotes, Reviews
	*			- Reviews include: ASIN reviewed, Product rating, Number of HelpfulVotes, Number of TotalVotes, Review Summary, Review Comment, DateOfReview
	*		EditorialReview - For each item in the response, the EditorialReview response group returns Amazon's review of the item
	*		Images - The Images response group returns the URLs to all available images of an item in three sizes: small, medium, and large.
	*		ItemAttributes - The ItemAttributes response group returns a potentially large number of attributes that describe an item.
	*		MostGifted - Returns the ASINs and titles of the ten items given as gifts most within a specified browse node.
	*		MostWishedFor - Returns the ASINs and titles of the ten items given as the items listed on the greatest number of wishlists within a specified browse node.
	*		NewReleases - Returns the ASIN and title of newly released items in a specified browse node.
	*		Offers - A parent response group that returns the contents of the OfferSummary response group plus, by default, seller and offer listing information.
	*		OfferSummary - returns, for each item in the response, the number of offer listings and the lowest price for each condition type.
	*		RelatedItems - Returns items related to an item specified in an ItemLookup request.
	*		Reviews - returns, for each item in the response, a: List of customer reviews, Average review rating (1 to 5 stars, where 5 is the best), Total number of reviews
	*		SalesRank - Returns the sales rank for each item in the response. One is the highest rating; a large number means the item has not sold well.
	*		TopSellers - returns the ASINs and titles of the ten best sellers within a specified browse node.
	*
	*/

	$orig_parameters = "AWSAccessKeyId=$admindata[amazon_access_key]".
	"&AssociateTag=$admindata[amazon_associates_id]".
	"&BrowseNode=[BrowseNode]".
	"&ItemPage=[ItemPage]".
	"[Keywords]".
	"&Operation=ItemSearch".
	"&ResponseGroup=Medium,Reviews".
	"&SearchIndex=[SearchIndex]".
	"&Service=AWSECommerceService".
	"&Timestamp=[Timestamp]".
	"&Version=2009-10-01";

	$signature_prefix = "GET\nwebservices.amazon.com\n/onca/xml\n";

/*
	$base_url = 'http://ecs.amazonaws.com/onca/xml?Service=AWSECommerceService'.
	"&AWSAccessKeyId=$admindata[amazon_access_key]".
	"&AssociateTag=$admindata[amazon_associates_id]".
	'&Version=2008-08-19'.
	'&Operation=ItemSearch'.
	'&ResponseGroup=Medium,Reviews';
*/

	$base_url = 'http://webservices.amazon.com/onca/xml?';

	/* Define Browse Nodes. If there is a value, use it as a keyword array to search within the node.
	*		Browse Node URL:
http://ecs.amazonaws.com/onca/xml?Service=AWSECommerceService&AWSAccessKeyId=083AT3X540E9EFH7SA02&Operation=BrowseNodeLookup&ResponseGroup=BrowseNodeInfo&Version=2008-08-19&BrowseNodeId=352697011
	*/
	$browse_node[574568] = array(''); # HDTVs
	$type[574568] = 'HDTVs';
	$search_index[574568] = 'Electronics';

	$browse_node[352697011] = array(''); # Blu-ray Disc Players
	$type[352697011] = 'Blu-ray Players';
	$search_index[352697011] = 'Electronics';

	$browse_node[352696011] = array(''); # HD DVD Players
	$type[352696011] = 'HD DVD Players';
	$search_index[352696011] = 'Electronics';

	$browse_node[352695011] = array(''); # Upconverting DVD Players
	$type[352695011] = 'Upconverting DVD Players';
	$search_index[352695011] = 'Electronics';

	$browse_node[14220271] = array(''); # Xbox 360 - All Games
	$type[14220271] = 'Xbox 360 Games';
	$search_index[14220271] = 'Electronics';

	$browse_node[14112941] = array(''); # Xbox 360 - Hardware
	$type[14112941] = 'HD Game Systems';
	$search_index[14112941] = 'Electronics';

	$browse_node[14210861] = array(''); # PlayStation 3 - All Games
	$type[14210861] = 'PlayStation 3 Games';
	$search_index[14210861] = 'Electronics';

	$browse_node[14210671] = array(''); # PlayStation 3 - Hardware
	$type[14210671] = 'HD Game Systems';
	$search_index[14210671] = 'Electronics';

	$browse_node[110770011] = array(''); # Camcorders - High Definition
	$type[110770011] = 'HD Camcorders';
	$search_index[110770011] = 'Electronics';

	$browse_node[300334] = array('high definition'); # Projectors
	$type[300334] = 'HDTV Projectors';
	$search_index[300334] = 'Electronics';

	$browse_node[172665] = array('high definition'); # TV Antennas
	$type[172665] = 'TV Antennas';
	$search_index[172665] = 'Electronics';

	$browse_node[13447451] = array('high definition'); # Digital Video Recorders
	$type[13447451] = 'Digital Video Recorders';
	$search_index[13447451] = 'Electronics';

	$browse_node[572052] = array('HD'); # Satellite Television - Receivers
	$type[572052] = 'Satellite Receivers';
	$search_index[572052] = 'Electronics';

	$browse_node[193640011] = array(''); # Blu-ray Store
	$type[193640011] = 'Blu-ray Movies';
	$search_index[193640011] = 'Video';

	$browse_node[193642011] = array(''); # HD DVD Store
	$type[193642011] = 'HD DVD Movies';
	$search_index[193640011] = 'Video';

	/****************** NOTES ******************
	*	- Check on sorting the values returned so that newest items are updated first
	***********************************************/

	# Load aliases into array
	$sql = "SELECT DISTINCT Manufacturer, ManufacturerAlias FROM az_attributes a, az_aux aux WHERE a.ASIN = aux.ASIN";
	$result = mQuery($sql);
	while ($row = mysql_fetch_assoc($result)) {
		$m_alias[$row['Manufacturer']] = $row['ManufacturerAlias'];
	}

	foreach ($browse_node as $node => $keywords) {
		foreach ($keywords as $keyword) {
#			$bn_url = "$base_url&BrowseNode=$node&SearchIndex=$search_index[$node]";
#			if ($keyword != '') $bn_url .= '&Keywords='. rawurlencode($keyword);
			if ($debug) echo date('Y/m/d H:i:s') .": Browse Node: $node, Keywords: $keyword\n";

			$cur_page = 1;
			$total_pages = 1;
			while ($cur_page <= $total_pages) {
				if ($debug) echo date('Y/m/d H:i:s') .": Grabbing Page $cur_page / $total_pages\n";
#				$az_url = "$bn_url&ItemPage=$cur_page";
#  			if ($debug) echo date('Y/m/d H:i:s') .": $az_url\n";

				$parameters = str_replace('[BrowseNode]', $node, $orig_parameters);
				if ($keyword != '') {
					$parameters = str_replace('[Keywords]', '&Keywords='. rawurlencode($keyword), $parameters);
				} else {
					$parameters = str_replace('[Keywords]', '', $parameters);
				}
				$parameters = str_replace('[SearchIndex]', $search_index[$node], $parameters);
				$parameters = str_replace('[ItemPage]', $cur_page, $parameters);
				$timestamp = str_replace(":", "%3A", gmdate("Y-m-d\TH:i:s\Z"));
				$parameters = str_replace('[Timestamp]', $timestamp, $parameters);
				$parameters = str_replace(",", "%2C", $parameters);
				$signature = base64_encode(hash_hmac("sha256", $signature_prefix . $parameters, $admindata['amazon_secret_access_key'], true));
				$signature = str_replace("+", "%2B", $signature);
				$signature = str_replace("=", "%3D", $signature);
				$signed_request = $base_url . $parameters ."&Signature=". $signature;
  			if ($debug) echo date('Y/m/d H:i:s') .": $signed_request\n";

#				$ch = curl_init($az_url);
				$ch = curl_init($signed_request);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				$return_xml = curl_exec($ch);
				curl_close($ch);
				unset($ch);

				$doc = new DOMDocument();
				if (!$doc->loadXML($return_xml)) {
					echo "Error while parsing the document\n";
					echo $return_xml;
					exit;
				}
				unset($return_xml);

				$items = $doc->getElementsByTagName('Item');

				$total_pages = getNodeValue($doc, 'TotalPages');
				if ($debug) echo date('Y/m/d H:i:s') .": Grabbing Page $cur_page / $total_pages\n";
				if ($total_pages > 400) {
					echo "Too many pages returned for browse_node $node\n";
					exit;
				}

				for ($x=0; $x < $items->length; $x++) {
					$item = $items->item($x);
					$attributes['ASIN'] = $fields['ASIN'] = $asin = getNodeValue($item, 'ASIN');
					if ($debug) {echo "Processing $asin\n";}

					// Get Nodes
					$small_image = $item->getElementsByTagName('SmallImage')->item(0);
					$medium_image = $item->getElementsByTagName('MediumImage')->item(0);
					$large_image = $item->getElementsByTagName('LargeImage')->item(0);

					$item_attributes = $item->getElementsByTagName('ItemAttributes')->item(0);
					$item_dimensions = $item_attributes->getElementsByTagName('ItemDimensions')->item(0);
					$list_price = $item_attributes->getElementsByTagName('ListPrice')->item(0);

					$offer_summary = $item->getElementsByTagName('OfferSummary')->item(0);
					if ($offer_summary != NULL) {
						$lowest_new_price = $offer_summary->getElementsByTagName('LowestNewPrice')->item(0);
						$lowest_used_price = $offer_summary->getElementsByTagName('LowestUsedPrice')->item(0);
						$lowest_refurbished_price = $offer_summary->getElementsByTagName('LowestRefurbishedPrice')->item(0);
					}

					# Get TotalReviews and AverageRating. This will determine which items we update in the review sync
					$fields['TotalReviews'] = getNodeValue($item, 'TotalReviews');
					$fields['AverageRating'] = getNodeValue($item, 'AverageRating');

					// Get Values
					$fields['DetailPageURL'] = getNodeValue($item, 'DetailPageURL');
					$fields['SalesRank'] = getNodeValue($item, 'SalesRank');
					$fields['SmallImageURL'] = getNodeValue($small_image, 'URL');
					$fields['MediumImageURL'] = getNodeValue($medium_image, 'URL');
					$fields['LargeImageURL'] = getNodeValue($large_image, 'URL');
					$attributes['Binding'] = getNodeValue($item_attributes, 'Binding');
					$attributes['Brand'] = getNodeValue($item_attributes, 'Brand');
					$attributes['DisplaySize'] = getNodeValue($item_attributes, 'DisplaySize');
					$attributes['EAN'] = getNodeValue($item_attributes, 'EAN');

					# Features are processed later

					$fields['Length'] = getNodeValue($item_dimensions, 'Length');
					if ($fields['Length'] != '') {
						if ($item_dimensions->getElementsByTagName('Length')->item(0)->getAttribute('Units') == 'inches') {
							$fields['Length'] = $fields['Length'] * 100;
						}
					}
					$fields['Width'] = getNodeValue($item_dimensions, 'Width');
					if ($fields['Width'] != '') {
						if ($item_dimensions->getElementsByTagName('Width')->item(0)->getAttribute('Units') == 'inches') {
							$fields['Width'] = $fields['Width'] * 100;
						}
					}
					$fields['Height'] = getNodeValue($item_dimensions, 'Height');
					if ($fields['Height'] != '') {
						if ($item_dimensions->getElementsByTagName('Height')->item(0)->getAttribute('Units') == 'inches') {
							$fields['Height'] = $fields['Height'] * 100;
						}
					}
					$fields['Weight'] = getNodeValue($item_dimensions, 'Weight');
					if ($fields['Weight'] != '') {
						if ($item_dimensions->getElementsByTagName('Weight')->item(0)->getAttribute('Units') == 'pounds') {
							$fields['Weight'] = $fields['Weight'] * 100;
						}
					}

					$attributes['Label'] = getNodeValue($item_attributes, 'Label');
					$fields['ListPrice'] = getNodeValue($list_price, 'Amount');
					$fields['ListPriceFormatted'] = getNodeValue($list_price, 'FormattedPrice');
					$attributes['Manufacturer'] = getNodeValue($item_attributes, 'Manufacturer');
					$attributes['Model'] = $model = getNodeValue($item_attributes, 'Model');
					$attributes['MPN'] = $model = getNodeValue($item_attributes, 'MPN');
					$attributes['ProductGroup'] = $model = getNodeValue($item_attributes, 'ProductGroup');
					$attributes['Publisher'] = getNodeValue($item_attributes, 'Publisher');
					$attributes['Studio'] = getNodeValue($item_attributes, 'Studio');
					$attributes['Title'] = $title = getNodeValue($item_attributes, 'Title');
					$attributes['UPC'] = getNodeValue($item_attributes, 'UPC');

					$fields['TotalNew'] = getNodeValue($offer_summary, 'TotalNew');
					$fields['TotalUsed'] = getNodeValue($offer_summary, 'TotalUsed');
					$fields['TotalRefurbished'] = getNodeValue($offer_summary, 'TotalRefurbished');
					if ($fields['TotalNew'] > 0) {
						$fields['LowestNewPrice'] = getNodeValue($lowest_new_price, 'Amount');
						$fields['LowestNewPriceFormatted'] = getNodeValue($lowest_new_price, 'FormattedPrice');
					}
					if ($fields['TotalUsed'] > 0) {
						$fields['LowestUsedPrice'] = getNodeValue($lowest_used_price, 'Amount');
						$fields['LowestUsedPriceFormatted'] = getNodeValue($lowest_used_price, 'FormattedPrice');
					}
					if ($fields['TotalRefurbished'] > 0) {
						$fields['LowestRefurbishedPrice'] = getNodeValue($lowest_refurbished_price, 'Amount');
						$fields['LowestRefurbishedPriceFormatted'] = getNodeValue($lowest_refurbished_price, 'FormattedPrice');
					}

					# Fix Manufacturer
					if (array_key_exists($row['Manufacturer'], $m_alias)) {
						$row['Manufacturer'] = $m_alias[$row['Manufacturer']];
					}

					# Editorial Reviews are processed later

/* Not sure if I need these
					$attributes[NumberOfPages] = getNodeValue('NumberOfPages', $item_attributes);
					$attributes[Platform] = getNodeValue('Platform', $item_attributes);
					$attributes[Format] = getNodeValue('Format', $item_attributes);
					$attributes[AudienceRating] = getNodeValue('AudienceRating', $item_attributes);
					$attributes[ESRBAgeRating] = getNodeValue('ESRBAgeRating', $item_attributes);
					$attributes[Studio] = getNodeValue('Studio', $item_attributes);
					$attributes[ReleaseDate] = getNodeValue('ReleaseDate', $item_attributes);
					$attributes[TheatricalReleaseDate] = getNodeValue('TheatricalReleaseDate', $item_attributes);
					$attributes[RunningTime] = getNodeValue('RunningTime', $item_attributes);
					$attributes[AspectRatio] = getNodeValue('AspectRatio', $item_attributes);
					$attributes[Warranty] = getNodeValue('Warranty', $item_attributes);

					$fields[AverageRating] = getNodeValue('AverageRating', $customer_reviews);
					$fields[TotalReviews] = getNodeValue('TotalReviews', $customer_reviews);
*/

					# Build query  & insert
					$names = array();$values = array();
					foreach ($fields as $name => $value) {
						$names[] = $name;
						$values[] = $value;
					}
					$sql = "REPLACE INTO az_main (". implode(", ", $names) .", date_updated) VALUES ('". implode("', '", $values) ."', CURDATE())";
					if ($debug) {echo "$sql\n";} else {mQuery($sql);}
					unset($fields);

					# Set az_aux values
					$subtype = 0;
					if ($type[$node] == 'HDTVs' || $type[$node] == 'HDTV Projectors') {
						if (stristr($title, 'lcd') !== false || stristr($title, 'lifi') !== false) {
							$subtype = MODEL_TYPE_LCD;
						} elseif (stristr($title, 'plasma') !== false) {
							$subtype = MODEL_TYPE_PLASMA;
						} elseif (stristr($title, 'dlp') !== false) {
							$subtype = MODEL_TYPE_DLP;
						} elseif (stristr($title, 'oled') !== false) {
							$subtype = MODEL_TYPE_OLED;
						} elseif (
							(stristr($title, 'lcos') !== false) ||
							(stristr($title, 'sxrd') !== false) ||
							(stristr($title, 'hdila') !== false) ||
							(stristr($title, 'hd ila') !== false)
						) {
							$subtype = MODEL_TYPE_LCOS;
						} elseif (
							(stristr($title, 'crt') !== false) ||
							(stristr($title, 'projection') !== false) ||
							(stristr($title, 'slimfit') !== false) ||
							(stristr($title, 'flat') !== false) ||
							(stristr($title, 'widescreen') !== false)
						) {
							$subtype = MODEL_TYPE_CRT;
						}
					}

#					$sql = "REPLACE INTO az_aux (ASIN, type, subtype) VALUES ('$asin', '$type[$node]', '$subtype')";
					$sql = "
					INSERT INTO az_aux (ASIN, type, subtype)
					VALUES ('$asin', '$type[$node]', '$subtype')
					ON DUPLICATE KEY UPDATE type = '$type[$node]', subtype = '$subtype'";
					if ($debug) {echo "$sql\n";} else {mQuery($sql);}

					# Build query  & insert
					$names = array();$values = array();
					foreach ($attributes as $name => $value) {
						$names[] = addslashes($name);
						$values[] = addslashes($value);
					}
					$sql = "REPLACE INTO az_attributes (". implode(", ", $names) .") VALUES ('". implode("', '", $values) ."')";
					if ($debug) {echo "$sql\n";} else {mQuery($sql);}
					unset($attributes);

					# Get az_features
					$features = $item_attributes->getElementsByTagName('Feature');
					foreach($features as $feature) {
						$f = addslashes($feature->nodeValue);
						$sql = "REPLACE INTO az_features (ASIN, Feature) VALUES ('$asin', '$f')";
						if ($debug) {echo "$sql\n";} else {mQuery($sql);}
					}
					unset($features);

					# Get az_special_features
					$special_features = addslashes(getNodeValue($item_attributes, 'SpecialFeatures'));
					if (substr($special_features, 0, 3) == 'nv:') {
						$special_features = strright($special_features, 'nv:');
						foreach(explode('|', $special_features) as $nv) {
							$nv = explode('^', $nv);
							$sql = "REPLACE INTO az_special_features (ASIN, SFName, SFValue) VALUES ('$asin', '$nv[0]', '$nv[1]')";
						}
					} elseif ($special_features != '') {
						$sql = "REPLACE INTO az_special_features (ASIN, SFName, SFValue) VALUES ('$asin', 'Other', '$special_features')";
					} else {
						$sql = "REPLACE INTO az_special_features (ASIN, SFName, SFValue) VALUES ('$asin', 'Other', 'None')";
					}
					if ($debug) {echo "$sql\n";} else {mQuery($sql);}
					unset($special_features);

					# Get az_reviews_editorial
					$editorial_reviews = $item->getElementsByTagName('EditorialReview');
					foreach($editorial_reviews as $editorial_review) {
						$source = addslashes(getNodeValue($editorial_review, 'Source'));
						$content = addslashes(getNodeValue($editorial_review, 'Content'));
						$sql = "REPLACE INTO az_reviews_editorial (ASIN, Source, Content) VALUES ('$asin', '$source', '$content')";
						if ($debug) {echo "$sql\n";} else {mQuery($sql);}
					}
					unset($editorial_reviews);

					# Link up with Pricegrabber masterid
/*
					if ($model != '') {
						$model_search = str_replace('-', '', $model);
						$sql = "SELECT pg_id FROM tbl_models WHERE REPLACE(model_name, '-', '') = '$model_search' OR REPLACE(display_name, '-', '') = '$model_search'";
						if ($debug) {echo "$sql\n";}
						$res_pg = mQuery($sql);
						if (mysql_num_rows($res_pg) == 1) {
							$row_pg = mysql_fetch_assoc($res_pg);
							$sql = "UPDATE az_aux SET pg_masterid = '$row_pg[pg_id]' WHERE ASIN = '$asin'";
							if ($debug) {echo "$sql\n";} else {mQuery($sql);}
						} elseif (mysql_num_rows($res_pg) > 1) {
							echo "Error Setting pg_masterid: More than one match found for '$model'\n";
						}
					}
*/
					# Get az_promotions?

					# Need to queue new items for approval

				}
				$cur_page++;
			}
		} // foreach keyword
		echo date('Y/m/d H:i:s') .":\t\tGrabbed $total_pages Pages\n";
	} // foreach browse node
?>