<?
	$debug = isset($_GET['debug']);
	if ($debug) {
		header('Content-Type: text/plain');
	}

	require_once('/var/www/html/includes/constants.php');
	require_once('/var/www/html/includes/lib_common.php');
	require_once('/var/www/html/includes/lib_mysql.php');

	class NTParser {
		var $datum = array();
		var $inside_datum = false;
		var $data = '';
		var $value = '';
		
		function NTParser() {
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
			
		function startElement($parser, $tagName, $attrs) {
			if ($tagName == 'DATUM') {
//				$this->datum[$attrs['SHORTNAME']] = $attrs['VALUE'];
				$this->inside_datum = true;
				$this->value = $attrs['VALUE'];
			}
		}
		
		function characterData($parser, $data) {
			if ($this->inside_datum) {
				$this->data .= $data;
			}
		}
	
		function endElement($parser, $tagName) {
			if ($tagName == "DATUM") {
				$this->data = str_replace('Sept.', 'Sep.', $this->data);
				$this->datum[date('Y-m-d', strtotime($this->data))] = $this->value;
				$this->inside_datum = false;
//				echo "$this->data: $this->value\n";
				$this->data = '';
			}
		}
		
		function setFile($file_path) {
			$this->fp = fopen($file_path, 'r');
			return true;
		}
	}
		
// NetTracker XML import

	// Get all appropriate xml files
	function get_xml_files($dir, $file_name) {
		$files = array();
		
		ini_set("max_execution_time", 10);
		if ($root = @opendir($dir)) {
			 while ($file = readdir($root)) {
				  if ($file=="." || $file=="..") {continue;}
				  if (is_dir($dir."/".$file)) {
						$files = array_merge($files, get_xml_files($dir."/".$file, $file_name));
				  } else {
						if ($file == $file_name && (preg_match('/\d{6}/', $dir) > 0)) {
							$files[] = $dir."/".$file;
						}
				  }
			 }
		}
		return $files;
	}

	$base_path = '/var/www/html/NetTracker/main';
	function load_data($file_name, $column) {
		global $base_path, $debug;
		global $stats;
		
   	$xml_files = get_xml_files($base_path, $file_name);
   	foreach($xml_files as $xml_file) {
			if ($debug) echo date('Y/m/d H:i:s') .": $xml_file\n";
   	  	$oNT = new NTParser();
   		if ($oNT->setFile($xml_file)) {
   			if (!$oNT->parse()) die('XMLParse failed');
   		
   			foreach($oNT->datum as $date => $count) {
   				$stats[$date][$column] = $count;
//					echo "stats[$date][$column] = $count\n";
   			}
   		} else {
   			echo date('Y/m/d H:i:s') .": FAILED ($xml_file)\n";
   		}
   	}
	}

	$stats = array();
	load_data('trafficdash-hitstrend.xml', 'hits');
	load_data('trafficdash-viewstrend.xml', 'page_views');
	load_data('trafficdash-visitstrend.xml', 'visits');
	load_data('visdash-newvisitor.xml', 'visits_new');
	load_data('visdash-repeatvisitor.xml', 'visits_repeat');
	load_data('visdash-uniquevisitor.xml', 'visits_unique');
	
	// Insert data
	foreach ($stats as $date => $type) {
		$qry = "REPLACE INTO ntstats (date, hits, page_views, visits, visits_new, visits_repeat, visits_unique) VALUES ('$date', {$type['hits']}, {$type['page_views']}, {$type['visits']}, {$type['visits_new']}, {$type['visits_repeat']}, {$type['visits_unique']})";
		mQuery($qry);
		if ($debug) echo "$qry\n";
	}
?>
