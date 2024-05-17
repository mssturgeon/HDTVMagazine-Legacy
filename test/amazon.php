<?
	require('../global.php');
	if (!access(ACCESS_ADMIN)) access_denied();

	header('Content-Type: text/plain');

	# Load admindata
	$result = mQuery("SELECT * FROM admin_settings WHERE id = 1");
	$admindata = mysql_fetch_assoc($result);

	function amazonItemLookup($asin, $response_groups) {
		global $admindata, $amazon_tracking_id;

		$parameters = "AWSAccessKeyId={$admindata['amazon_access_key']}".
		"&AssociateTag=$amazon_tracking_id".
		"&ItemId=$asin".
		"&Operation=ItemLookup".
		"&ResponseGroup=". join(',', $response_groups) .
		"&Service=AWSECommerceService".
		"&Timestamp=". gmdate("Y-m-d\TH:i:s\Z") .
		"&Version=2009-11-01";
		$parameters = str_replace(array(':',','), array('%3A','%2C'), $parameters);

		$signature = base64_encode(hash_hmac("sha256", "GET\nwebservices.amazon.com\n/onca/xml\n$parameters", $admindata['amazon_secret_access_key'], true));
		$signature = str_replace(array('+','='), array('%2B','%3D'), $signature);

		$signed_request = "http://webservices.amazon.com/onca/xml?{$parameters}&Signature=$signature";
		$contents = file_get_contents($signed_request);
		$xml = new SimpleXMLElement( $contents );
		print_r($xml);

		# Verify a successful request
		if (is_object($xml->OperationRequest->Errors->Error)) {
			foreach($xml->OperationRequest->Errors->Error as $error) {
				echo "Error code: " . $error->Code . "\r\n";
				echo $error->Message . "\r\n";
			}
		}

		$az_main = array();$az_attributes = array();
		$item = $xml->Items->Item;
		$az_main['ASIN'] = $item->ASIN;
		$az_main['DetailPageURL'] = $item->DetailPageURL;

		if (in_array('ItemAttributes', $response_groups)) { # Update az_item_attributes
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

		if (in_array('OfferSummary', $response_groups)) { # Update az_main
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

		if (in_array('Images', $response_groups)) { # Update az_main
			$az_main['SmallImageURL'] = $item->SmallImage->URL;
			$az_main['SmallImageHeight'] = $item->SmallImage->Height;
			$az_main['SmallImageWidth'] = $item->SmallImage->Width;
			$az_main['MediumImage'] = $item->MediumImage->URL;
			$az_main['MediumImageHeight'] = $item->MediumImage->Height;
			$az_main['MediumImageWidth'] = $item->MediumImage->Width;
		}

		# Update az_main
		$columns = array_keys($az_main);
		$values = array_values($az_main);
		$sql = "REPLACE INTO az_main (". join(", ", $columns) .", date_updated) VALUES ('". join("', '", $values) ."', NOW())";
#		mQuery($sql);
		echo "$sql\n";

		# Update az_attributes
		$columns = array_keys($az_attributes);
		$values = array_values($az_attributes);
		$sql = "REPLACE INTO az_attributes (". join(", ", $columns) .") VALUES ('". join("', '", $values) ."')";
#		mQuery($sql);
		echo "$sql\n";

		return $row;
	}

	$amazon_tracking_id = $admindata['amazon_associates_id'];
#	$response_groups = array('');
#	$response_groups = array('ItemAttributes');
#	$response_groups = array('ItemAttributes', 'OfferSummary');
	$response_groups = array('ItemAttributes', 'OfferSummary', 'Images');

	$item = amazonItemLookup('B0038L4126', $response_groups);
#	print_r($item);
?>