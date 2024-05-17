<?
	set_time_limit(0);

	$debug = isset($_GET[debug]);
	$return = isset($_GET['return']);
	if ($debug) {
		header('Content-Type: text/plain');
	}
	define('BASE_DIR', '/var/www/html');
	require_once(BASE_DIR .'/includes/constants.php');
	require_once(BASE_DIR .'/includes/lib_common.php');
	require_once(BASE_DIR .'/includes/lib_mysql.php');
	require_once(BASE_DIR .'/includes/lib_xml.php');
	
	$url = 'http://api.evdb.com/rest/events/search?app_key=[REDACTED]&keywords=hdtv';
	if ($debug) echo "$url\n";
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

	$items = $doc->getElementsByTagName('event');
	for ($x=0; $x < $items->length; $x++) {
//		$id = getAttribute('id', $items->item($x));
		$id = $items->item($x)->getAttribute('id');
		$ids[] = $id;
	}

	# Cycle through event_id's and get event info
	foreach ($ids as $id) {
		$url = "http://api.evdb.com/rest/events/get?app_key=[REDACTED]&id=$id";
		if ($debug) echo "$url\n";
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
	
		$items = $doc->getElementsByTagName('event');
		$event = $items->item(0);
		
		$title = addslashes(getNodeValue($event, 'title'));
		$description = addslashes(getNodeValue($event, 'description'));
		$start_time = getNodeValue($event, 'start_time');
		$stop_time = getNodeValue($event, 'stop_time');
		$venue_name = addslashes(getNodeValue($event, 'venue_name'));
		$city_name = getNodeValue($event, 'city');
		$region_name = getNodeValue($event, 'region');
		$region_abbr = getNodeValue($event, 'region_abbr');
		$country_name = getNodeValue($event, 'country');
		$country_abbr = getNodeValue($event, 'country_abbr');
		$latitude = getNodeValue($event, 'latitude');
		$longitude = getNodeValue($event, 'longitude');

		$links = $doc->getElementsByTagName('links');
		$link = $links->item(0);
		$url = getNodeValue($link, 'url');
		
		$sql = "SELECT id FROM event WHERE id = '$id'";
		$result = mQuery($sql);
		if (mysql_num_rows($result) == 0) {
   		$sql = "
   		INSERT INTO event
   		(id, title, description, link, start_time, stop_time, venue_name, city_name, region_name, region_abbr, country_name, country_abbr, latitude, longitude)
   		VALUES ('$id', '$title', '$description', '$url', '$start_time', '$stop_time', '$venue_name', '$city_name', '$region_name', '$region_abbr', '$country_name', '$country_abbr', '$latitude', '$longitude')";
		} else {
			$sql = "
			UPDATE event SET
			title = '$title'
			,description = '$description'
			,link = '$url'
			,start_time = '$start_time'
			,stop_time = '$stop_time'
			,venue_name = '$venue_name'
			,city_name = '$city_name'
			,region_name = '$region_name'
			,region_abbr = '$region_abbr'
			,country_name = '$country_name'
			,country_abbr = '$country_abbr'
			,latitude = '$latitude'
			,longitude = '$longitude'
			WHERE id = '$id'";
		}
  		if ($debug) echo "$sql\n"; else mQuery($sql);
	}
	
	if ($return) js_back();
?>
