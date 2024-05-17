<?
/*
function getAttribute($name, $element) {
	if (!$element->has_attributes()) return '';
	$attributes = $element->attributes();
	foreach($attributes as $a) {
		if($a->name() == $name) return addslashes(trim($a->value()));
	}
}
*/

function getAttributeOld($name, $element) {
	if (!$element->has_attributes()) return '';
	$attributes = $element->attributes();
	foreach($attributes as $a) {
		if($a->name() == $name) return addslashes(trim($a->value()));
	}
}

function getNodeValue($from_node, $name) {
	global $debug;
	
	if ($from_node == NULL) {
		return '';
	} else {
		$node = $from_node->getElementsByTagName($name);
		if ($node->length == 0) {
			return '';
		} else {
			return $node->item(0)->nodeValue;
		}
	}
}

function getNodeValueOld($name, $element) {
	global $debug;
	$values = array();
	
	if ($element == '') return '';
	
	$nodes = $element->get_elements_by_tagname($name);

	if ($debug) {
		echo "$name\n";
		print_r($element);
	}
	
	foreach ($nodes as $n) {
		if ($n->node_type() == XML_ELEMENT_NODE) $values[] = addslashes(trim($n->get_content()));
	}
	return implode(',', array_unique($values));
}

?>
