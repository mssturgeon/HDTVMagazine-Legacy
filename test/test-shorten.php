<?
	require('../global.php');
	if (!access(ACCESS_ADMIN_ANY)) access_denied();
	header('Content-type: text/plain');

	function old_shorten_url($long_url) {
		$curl_handle = curl_init();
		$url = 'http://api.bit.ly/shorten?'.
		'login=hdtv'.'&amp;'.
		'apiKey=[REDACTED]'.'&amp;'.
		'version=2.0.1'.'&amp;'.
		'format=xml'.'&amp;'.
		'history=1'.'&amp;'.
		'longUrl='. urlencode($long_url);
		curl_setopt($curl_handle, CURLOPT_URL, $url);
		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl_handle, CURLOPT_HEADER, false);
 		$result = curl_exec($curl_handle);
		curl_close($curl_handle);

		$xml = new SimpleXMLElement( $result );

		return $xml;

#		return $xml->results->nodeKeyVal->shortUrl;
	}

	function shorten_url($long_url) {
		$curl_handle = curl_init();
		$url = 'http://api.bit.ly/v3/shorten?'.
		'login=hdtv&'.
		'apiKey=[REDACTED]&'.
		'format=xml&'.
		'longUrl='. urlencode($long_url);

		curl_setopt($curl_handle, CURLOPT_URL, $url);
		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl_handle, CURLOPT_HEADER, false);
 		$result = curl_exec($curl_handle);
		curl_close($curl_handle);

		$xml = new SimpleXMLElement( $result );

		return $xml->data->url;
	}

	$xml = shorten_url('http://www.hdtvmagazine.com/columns/2011/07/hdtv-almanac-iphone-users-like-video.php');

	print_r($xml);
	print_r($xml->data);

	echo $xml;
?>