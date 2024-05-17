<?
define('PG_TOPCAT_ELECTRONICS', '2');
define('PG_CAT_TELEVISIONS', 'catzero:19');
//define('PG_BASE_URL', 'http://ah.pricegrabber.com/export_feeds.php?pid=ddeehgd&document_type=xml&limit=250&col_mfrpartnum=1&col_mfrname=1&col_description=1&col_images=1');
define('PG_BASE_URL', 'http://ah.pricegrabber.com/export_feeds.php?pid=aididid&document_type=xml&limit=250&col_mfrpartnum=1&col_mfrname=1&col_description=1&col_images=1');

class PG_Parser {
	var $inside_product = false;
	var $inside_product_spec = false;
	var $tag_path = '';
	var $data = '';
	var $products = array();
	var $product_specs = array();
	var $index = 0;
	var $records = 0;
	var $last_name = '';	// Used to hold the previous-most name value
	var $sellers = 0;

	function PG_Parser() {
		$this->parser = xml_parser_create();
	}

	function parse() { // set the handlers
		xml_set_object($this->parser, $this);
		xml_set_element_handler($this->parser, 'startElement', 'endElement');
		xml_set_character_data_handler($this->parser, 'characterData');

		while ($data = fread($this->fp, 2048)) {
			$err = xml_parse($this->parser, $data);
			if (!$err) {
				fclose($this->fp);
				return $err;
			}
		}
		fclose($this->fp);
		xml_parser_free($this->parser);
		return true;
	}

	function startElement($parser, $tag_name, $attrs) {
		$this->data = '';

		if ($this->inside_product) {
			$this->tag_path .= '/'. strtoupper($tag_name);
			if ($tag_name == 'PRICE') $this->sellers = $attrs['SELLERS'];
			if ($tag_name == 'SPEC') $this->inside_product_spec = true;
		} elseif ($tag_name == 'PRODUCT') {
			$this->inside_product = true;
		}
	}

	function characterData($parser, $data) {
		$this->data .= $data;
	}

	function endElement($parser, $tag_name) {
		if ($this->inside_product) {
			$this->products[$this->index][$this->tag_path] = trim(addslashes($this->data));
		}
		if ($tag_name == 'NUM_RESULTS') $this->records = $this->data;
		if ($this->inside_product_spec) {
			if ($tag_name == 'NAME') {
				$this->last_name = strtoupper($this->data);
			} elseif ($tag_name == 'VALUE') {
				$this->product_specs[$this->index][$this->last_name] = $this->data;
			}
		}

		if ($tag_name == 'PRODUCT') {
			$this->inside_product = false;
			$this->tag_path = '';
			$this->index++;
		} elseif ($tag_name == 'SPEC') {
			$this->inside_product_spec = false;
			$this->last_name = '';
		}

		$slash_pos = strrpos($this->tag_path, '/');
		$this->tag_path = substr($this->tag_path, 0, $slash_pos);
	}

	function setInputUrl($sUrl, $iTimeout) {
		$arUrl = parse_url($sUrl);
		$sHost = $arUrl['host'];
		$sQuery = array_key_exists('query', $arUrl) ? $arUrl['query'] : '';
		if (!@$iPort = $arUrl['port']) {
			$iPort = 80;
		}
		$sFullPath = $arUrl['path'] . '?' . $sQuery;

		$fp = fsockopen($sHost, $iPort, $errno, $errstr, $iTimeout);
		if(!is_resource($fp)) {
			// return an error
			echo '<!-- '. $sUrl . " issued: $errstr ($errno)\n" .' -->@';
			return false;
		} else {
			fputs($fp,"GET $sFullPath HTTP/1.0\r\nHost: " . $sHost. "\r\n\r\n");

			// in blocking mode it will wait for data to become available on the socket
			socket_set_blocking($fp, true);

			// get the first line and determine the answer-code
			$sLine = fgets($fp , 1024);
			$iCode = preg_replace("/.*(\d\d\d).*/i" , "\\1" , $sLine);

			// an error occurred if code is not between 200 and 399
			$error = null;
			if ($iCode != 200) {
				// prevent html caching
				$_GLOBALS['bFeedError'] = true;
				error_log ("\n". time() .':'. $sUrl .':'. $sLine .':'. $_SERVER['REQUEST_URI'], 0);
				fclose($fp);
				return false;
			}

			$sHeader = '';
			// no error - now determine start of data (skipping header)
			while (!feof($fp)) {
				$sLine = fgets($fp , 1024);
				if (strlen($sLine) < 3) {
					break;
				}
			}

			$this->fp = $fp;
			return true;
		}
	}
}

function getByPGMasterID($masterid) {
	global $admindata, $debug;

	# First check to see if it's in the database
	$sql_pg = "SELECT * FROM pg_main WHERE masterid = '$masterid'";
	if ($debug) echo "$sql_pg\n";
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
	$pg_main['title'] = addslashes($product->title);
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
	$pg_main['sellers'] = intval($product->num_sellers[0]);
	$pg_main['rating'] = $product->rating;
	$pg_main['num_reviews'] = $product->num_reviews;

	# Update pg_main
	$sql = "REPLACE INTO pg_main (". join(", ", array_keys($pg_main)) .", date_updated) VALUES ('". join("', '", array_values($pg_main)) ."', NOW())";
	if ($debug) {echo "$sql\n";} else {mQuery($sql);}

	# Now that we KNOW it's in the database ...
	$res_pg = mQuery($sql_pg);
	return mysql_fetch_assoc($res_pg);
}

?>
