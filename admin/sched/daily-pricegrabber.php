<?
	set_time_limit(0);
	ini_set('memory_limit', '32M');

	define('BASE_DIR', '/var/www/html');
	require_once(BASE_DIR .'/includes/constants.php');
	require_once(BASE_DIR .'/includes/lib_mysql.php');
	require_once(BASE_DIR .'/includes/lib_common.php');
	require_once(BASE_DIR .'/includes/lib_xml.php');

	if (!CALLED_FROM_DAILY) {
		$debug = isset($_GET['debug']);
		if ($debug) header('Content-Type: text/plain');
	}

	$today = date('Y-m-d');

	# Load admin settings
	$result = mQuery("SELECT * FROM admin_settings");
	$admindata = mysql_fetch_assoc($result);

	$base_url = 'http://ah.pricegrabber.com/search_xml.php?pid=718&key=7e084a24802&version=2.14&upc=1&spec=2&offers=1';

	# Loop through recent ASINs and fetch masterid
	$sql = "
	SELECT a.ASIN
	FROM az_aux a, az_main m
	WHERE a.ASIN = m.ASIN
		AND date_updated > '$today' - INTERVAL {$admindata['amazon_update_window']} DAY
		AND pg_masterid = 0
		AND hide = 0";
	if ($debug) {echo "$sql\n";}
	$result = mQuery($sql);
	while ($row = mysql_fetch_assoc($result)) {
		$url = "$base_url&asin={$row['ASIN']}";
		if ($debug) echo date('Y/m/d H:i:s') ." $url\n";

		$ch = curl_init($url);
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

		$error = getNodeValue($doc, 'error');
		if ($error == '') {
			$num_results = getNodeValue($doc, 'num_results');
			if ($num_results > 1) {
#				if ($debug) echo date('Y/m/d H:i:s') ." More than 1 match found ({$row['ASIN']})\n";
				echo date('Y/m/d H:i:s') ." More than 1 match found ({$row['ASIN']})\n";
			}

			$product = $doc->getElementsByTagName('product')->item(0);
			$fields['masterid'] = getNodeValue($product, 'masterid');
			$fields['upc'] = getNodeValue($product, 'upc');
			$fields['manufacturer'] = getNodeValue($product, 'manufacturer');
			$fields['partnum'] = getNodeValue($product, 'partnum');
			$fields['num_reviews'] = getNodeValue($product, 'num_reviews');
			$fields['rating'] = getNodeValue($product, 'rating');
			$fields['price_formatted'] = getNodeValue($product, 'price');
			$fields['price'] = str_replace(array('$', '.', ','), array(''), $fields['price_formatted']);

			# Correct values for insertion
			$fields['num_reviews'] = ($fields['num_reviews'] == '') ? 0 ; $fields['num_reviews'];
			$fields['masterid'] = ($fields['masterid'] == '') ? 0 ; $fields['masterid'];
			$fields['price'] = ($fields['price'] == '') ? 0 ; $fields['price'];

			# Get review information

			# Build query  & insert
			$names = array();$values = array();
			foreach ($fields as $name => $value) {
				$names[] = $name;
				$values[] = $value;
			}
			$sql = "REPLACE INTO pg_main (". implode(", ", $names) .", date_updated) VALUES ('". implode("', '", $values) ."', CURDATE())";
			if ($debug) {echo "$sql\n";} else {mQuery($sql);}

			$sql = "UPDATE az_aux SET pg_masterid = '{$fields['masterid']}' WHERE ASIN = '{$row['ASIN']}'";
			if ($debug) {echo "$sql\n";} else {mQuery($sql);}
		} else {
			if ($debug) echo date('Y/m/d H:i:s') ." Error: $error ({$row['ASIN']})\n";
			unset($doc);
		}
	}
?>