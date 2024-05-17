<?
	set_time_limit(0);
	ini_set('memory_limit', '32M');

	$debug = isset($_GET['debug']);
	if ($debug) {
		header('Content-Type: text/plain');
	}

	$today = date('Y-m-d');

	// BASE_DIR is the full OS path to the web document directory.  User primarily in include/require statements
	define('BASE_DIR', '/var/www/html');
	require_once(BASE_DIR .'/includes/constants.php');
	require_once(BASE_DIR .'/includes/lib_common.php');
	require_once(BASE_DIR .'/includes/lib_mysql.php');
	require_once(BASE_DIR .'/includes/lib_xml.php');

	# Load admin settings
	$result = mQuery("SELECT * FROM admin_settings");
	$admindata = mysql_fetch_assoc($result);

	$base_url = 'http://ecs.amazonaws.com/onca/xml?Service=AWSECommerceService'.
	"&AWSAccessKeyId=$admindata[amazon_access_key]".
	"&AssociateTag=$admindata[amazon_associates_id]".
	'&Version=2008-08-19'.
	'&Operation=ItemLookup'.
	'&ResponseGroup=Reviews';

	# Get Customer Reviews
	echo date('Y/m/d H:i:s') .":\t\tGetting Customer Reviews\n";

	$sql = "SELECT ASIN FROM az_main WHERE TotalReviews > 0 AND date_updated > '$today' - INTERVAL {$admindata['amazon_update_window']} DAY";
	$result = mQuery($sql);
	while ($row = mysql_fetch_assoc($result)) {
		$sUrl = "$base_url&ItemId=$row[ASIN]";

		$cur_page = 1;
		$total_pages = 1;
		while ($cur_page <= $total_pages) {
			$az_url = $sUrl .'&ReviewPage='. $cur_page;
  		if ($debug) echo date('Y/m/d H:i:s') .": $az_url\n";

			$ch = curl_init($az_url);
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

			# Get TotalReviews and update az_main. Only update on the first page
			if ($cur_page == 1) {
				$total_reviews = getNodeValue($doc, 'TotalReviews');
				$average_rating = getNodeValue($doc, 'AverageRating');
				$sql = "UPDATE az_main SET TotalReviews = '$total_reviews', AverageRating = '$average_rating' WHERE ASIN = '$row[ASIN]'";
				if ($debug) {echo "$sql\n";} else {mQuery($sql);}
#				echo "$sql<br />";ob_flush;flush;
			}

			$total_pages = getNodeValue($doc, 'TotalReviewPages');
			if ($debug) echo date('Y/m/d H:i:s') .": Grabbing Page $cur_page / $total_pages\n";
/*
if ($total_pages > 400) {
				echo "Too many pages returned for item $row[ASIN]";
				exit;
			}
*/

			$item = $doc->getElementsByTagName('Item')->item(0);
			if ($item != NULL) {
				$customer_reviews = $item->getElementsByTagName('CustomerReviews')->item(0);
				if ($customer_reviews != NULL) {
//					$customer_reviews = $customer_reviews[0];
//					$total_pages = getNodeValue($customer_reviews, 'TotalReviewPages');
					$reviews = $item->getElementsByTagName('Review');
					foreach ($reviews as $customer_review) {
						$review_fields['ASIN'] = $asin = getNodeValue($customer_review, 'ASIN');
						$review_fields['CustomerId'] = getNodeValue($customer_review, 'CustomerId');
						$review_fields['Rating'] = getNodeValue($customer_review, 'Rating');
						$review_fields['HelpfulVotes'] = getNodeValue($customer_review, 'HelpfulVotes');
						$review_fields['TotalVotes'] = getNodeValue($customer_review, 'TotalVotes');
						$review_fields['Date'] = getNodeValue($customer_review, 'Date');
						$review_fields['Summary'] = addslashes(getNodeValue($customer_review, 'Summary'));
						$review_fields['Content'] = addslashes(getNodeValue($customer_review, 'Content'));

						# Build query  & insert
						$names = array();$values = array();
						foreach ($review_fields as $name => $value) {
							$names[] = $name;$values[] = $value;
						}
						$sql = "REPLACE INTO az_reviews (". implode(", ", $names) .") VALUES ('". implode("', '", $values) ."')";
						if ($debug) {echo "$sql\n";} else {mQuery($sql);}

						# Add to review aggregate
						$date = strtotime($review_fields[Date]);
						$sql = "REPLACE INTO reviews (review_table, review_key, review_product, review_user, review_timestamp, review_rating, review_source)".
						" VALUES ('az_reviews', 'ASIN', '$asin', '$review_fields[CustomerId]', $date, $review_fields[Rating], 'Amazon.com')";
						if ($debug) {echo "$sql\n";} else {mQuery($sql);}
					}
				}
			}
			$cur_page++;
			sleep(1);
		}
#		echo date('Y/m/d H:i:s') .":\t\tGrabbed $total_pages Pages\n";
	}
?>
