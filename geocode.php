<script language="JavaScript" type="text/javascript">
<?
	require_once 'XML/RPC.php';

	$params = array(new XML_RPC_Value(implode(', ', $_GET), 'string'));
	$message = new XML_RPC_Message('geocode', $params);
	$client = new XML_RPC_Client('/service/xmlrpc', 'rpc.geocoder.us');
	$response = $client->send($message);
	if (!$response) {
		echo 'Communication error: ' . $client->errstr;
		exit;
	}

	if (!$response->faultCode()) {
		$value = $response->value();
		$address_data = XML_RPC_decode($value);
		if ($address_data[0]['lat'] == '' || $address_data[0]['long'] == '') {
			echo "alert('Could not locate address. Please use the Map It button to pinpoint your lat/long.');";
		} else {
			echo "parent.document.forms['frmProfile'].lat.value = '{$address_data[0]['lat']}';";
			echo "parent.document.forms['frmProfile'].lon.value = '{$address_data[0]['long']}';";
			echo "alert('Address found.');";
		}
	} else { // Display problems that have been gracefully cought and reported by the xmlrpc.php script
		echo 'Fault Code: ' . $response->faultCode() . "\n";
		echo 'Fault Reason: ' . $response->faultString() . "\n";
		echo "alert('Could not locate address. Please use the Map It button to pinpoint your lat/long.');";
	}
?>
</script>
