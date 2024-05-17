<?php

	error_reporting(E_ALL & ~E_NOTICE);

	if ($_REQUEST['v']) {
		echo '3';
		exit;
	}

	if (!function_exists('get_page')) {
		function get_page($url) {
			global $i, $fail_count;

			$ch = curl_init($url);

			if (!$ch) {
				if ($fail_count < 5) {
					$i--;
					$fail_count++;
				}
			} else {
				curl_setopt_array($ch, array(CURLOPT_RETURNTRANSFER => 1));

				if ($_REQUEST['c']) curl_setopt($ch, array(CURLOPT_COOKIE => $_REQUEST['c']));
				$string = curl_exec($ch);
				curl_close($ch);
				return $string;
			}
		}
	}

	$results = $query_order = array();

	if ($_REQUEST['t']) {
		$query_order = array(1);
	} else {
		$num = 10;
		if ($_REQUEST['se'] == 'y') $num = 50;

		for ($i = 1; $i <= $_REQUEST['d']; $i += $num) {
			$query_order[] = $i;
		}
		if ($_REQUEST['l'] > 0 && $_REQUEST['l'] < 1001) {
			$x = $_REQUEST['l'] - 1;
			$y = $x - ($x % $num) + 1;
			$query_order[$y / $num] = 1;
			$query_order[0] = $y;
			$slice = array_slice($query_order, 1, max (0, ($y / $num) - 1));
			rsort ($slice);
			foreach ($slice as $array_key => $value) {
				$query_order[$array_key + 1] = $value;
			}
		}
	}

	if ($_REQUEST['se'] == 'g') {
		$fail_count = 0;
		$all_keys = explode('|', $_REQUEST['k']);
		$current_key = 0;
		for ($i = 0; $i < count($query_order); $i++) {
			@set_time_limit(30);
			$start = $query_order[$i];

			$data = get_page ('https://www.googleapis.com/customsearch/v1?prettyprint=false&key=' . $all_keys[$current_key] . $_REQUEST['e'] . '&q=' . urlencode ($_REQUEST['q']) . '&start=' . $start);
			$parser = json_decode($data, true);

			if ($parser['error']['code'] == 403 && $current_key + 2 <= count($all_keys)) {
				$current_key++;
			} elseif ($parser['error']['code']) {
				$error = $parser['error']['message'];
			} elseif ($_REQUEST['u'] && is_array($parser['items'])) {
				unset ($results_detail);
				$position = $start;
				foreach ($parser['items'] as $result) {
					if ($result['snippet'] == 'Skipped Snippet' || trim($result['snippet']) == '') continue;

					if (substr_count (strtolower ($result['link']), $_REQUEST['u'])) $results[] = $position;

					if ($_REQUEST['s']) {
						$results_detail[$position]['title'] = $result['title'];
						$results_detail[$position]['url'] = $result['link'];
					}
					$position++;
				}
			}

			$results_total = $parser['queries']['request'][0]['totalResults'];
			if (!$results_total && $fail_count < 5) {
				$i--;
				$fail_count++;
			}
			if ($results) break;
		}
	} elseif ($_REQUEST['se'] == 'y') {
		$error = '';
		$fail_count = 0;
		for ($i = 0; $i < count($query_order); $i++) {
			@set_time_limit(30);
			$start = $query_order[$i];

			$data = get_page ('http://api.search.yahoo.com/WebSearchService/V1/webSearch?appid=keywordtracker&query=' . urlencode ($_REQUEST['q']) . '&start=' . $start . '&results=50');

			$parser = xml_parser_create('UTF-8');
			xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
			xml_parse_into_struct($parser, $data, $vals, $index);
			xml_parser_free($parser);

			if ($index['ERROR']) {
				$error = $vals[$index['MESSAGE'][0]]['value'];
			}

			if ($_REQUEST['u'] && is_array($index['URL'])) {
				unset ($results_detail);
				$position = $start;
				foreach ($index['URL'] as $url_key) {
					if ($vals[$url_key]['level'] == 3) {
						if (substr_count (strtolower ($vals[$url_key]['value']), $_REQUEST['u'])) $results[] = $position;

						if ($_REQUEST['s']) {
							$results_detail[$position]['title'] = $vals[$url_key - 2]['value'];
							$results_detail[$position]['summary'] = $vals[$url_key - 1]['value'];
							$results_detail[$position]['url'] = $vals[$url_key]['value'];
						}
						$position++;
					}
				}
			}
			$results_total = $vals[$index['RESULTSET'][0]]['attributes']['TOTALRESULTSAVAILABLE'];
			if ($error && $fail_count < 5) {
				unset ($error);
				$i--;
				$fail_count++;
			}
			if ($results_detail) {
				 if ($_REQUEST['s']) {

				 	$position_key = max(0, min ($results[0] - 4, count ($results_detail) - 10));
					$results_detail = array_slice ($results_detail, $position_key, 10);
					foreach ($results_detail as $result) {
						$position_key++;
						$results_new[$position_key] = $result;
					}
					$results_detail = $results_new;
				}
				break;
			}
		}

	} elseif ($_REQUEST['se'] == 'b') {
		$fail_count = 0;
		for ($i = 0; $i < count($query_order); $i++) {
			@set_time_limit(30);
			$start = $query_order[$i];

			$data = get_page ('http://www.bing.com/search?q=' . urlencode ($_REQUEST['q']) . '&first=' . $start . '&count=10&format=rss');

			$parser = xml_parser_create('UTF-8');
			xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
			xml_parse_into_struct($parser, $data, $vals, $index);
			xml_parser_free($parser);

			unset ($results_detail);
			$position = $start;
			if (is_array ($index['LINK'])) {
				foreach ($index['LINK'] as $url_key) {
					if ($vals[$url_key]['level'] == 4 && !strpos($vals[$url_key]['value'], 'www.bing.com:80/search')) {
						if (substr_count (strtolower ($vals[$url_key]['value']), $_REQUEST['u'])) $results[] = $position;

						if ($_REQUEST['s']) {
							$results_detail[$position]['title'] = $vals[$url_key - 1]['value'];
							$results_detail[$position]['summary'] = $vals[$url_key + 1]['value'];
							$results_detail[$position]['url'] = $vals[$url_key]['value'];
						}
						$position++;
					}
				}
			}
			if ($results) break;
			if (!$data && $fail_count < 5) {
				$i--;
				$fail_count++;
			}
		}
	}

	if (!$results) $results[] = 9999;
	$output['results'] = implode ('|', $results);

	if ($error) $output['error'] = $error;

	if ($_REQUEST['t']) {
		$output['total'] = $results_total;
	} elseif ($_REQUEST['s']) {
		$output['total'] = $results_total;
		$output['detail'] = $results_detail;
	}

	echo serialize($output);

?>