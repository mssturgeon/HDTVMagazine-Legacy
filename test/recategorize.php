<?
	require('../global.php');

	$debug = isset($_GET['debug']);

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - Your Guide to High Definition Television</title>

	<? require(BASE_DIR .'/includes/page_header.php');?>
</head>
<body><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');

	$sql = "SELECT entry_id, entry_blog_id, entry_title, entry_excerpt, category_label, entry_created_on
	FROM mt_entry e, mt_placement p, mt_category c
	WHERE e.entry_id = p.placement_entry_id
		AND p.placement_category_id = c.category_id
		AND e.entry_blog_id = 10
		AND category_label = 'HD Video Production'";
	$result = mQuery($sql);
	if ($debug) echo '<table class="simple">';
	while ($row = mysql_fetch_assoc($result)) {
		$ts = strtotime($row['entry_created_on']);
		$y = date('Y', $ts);
		$m = date('m', $ts);
		$entry = getEntryInfo($row['entry_blog_id']);

		$entry['link'] = "/$entry[blog_dir]/$y/$m/". dirify($row['entry_title']) .".php";
		if ($debug) echo '<tr><td><a target="_blank" href="'. $entry['link'] .'">'. $row['entry_title'] .'</a></td>';

		$old_categories = array();$new_categories = array();$new_cat_labels = array();
		$sql = "SELECT category_label FROM mt_placement p, mt_category c WHERE p.placement_category_id = c.category_id AND placement_entry_id = {$row['entry_id']}";
		$res_categories = mQuery($sql);
		while ($row_categories = mysql_fetch_assoc($res_categories)) $old_categories[] = $row_categories['category_label'];
		if ($debug) echo '<td nowrap="nowrap">'. join('<br />', $old_categories) ."</td>";

		$text = addslashes("{$row['entry_title']}\n{$row['entry_excerpt']}");

		$sql = "SELECT MATCH(category_description) AGAINST ('$text') AS relevance, category_label, category_id
		FROM mt_category
		WHERE category_blog_id = '{$row['entry_blog_id']}'
			AND MATCH(category_description) AGAINST ('$text') > 3
		ORDER BY relevance DESC LIMIT 4";
		$res_match = mQuery($sql);
#		if ($debug) {echo "$sql\n";}

		if ($debug) echo '<td><table class="bare">';
		if (mysql_num_rows($res_match) > 0) {
			while ($row_match = mysql_fetch_assoc($res_match)) {
				if ($debug) echo '<tr><td nowrap="nowrap">'. $row_match['category_label'] .'</td><td>'. number_format($row_match['relevance'], 2) ."</td></tr>";
				if (!in_array($row_match['category_id'], $new_categories)) $new_categories[] = $row_match['category_id'];
			}
		} else { # No match found, assign default category
			$sql = "SELECT category_id, category_label FROM mt_category WHERE category_blog_id = '{$row['entry_blog_id']}' AND category_label = 'General Interest'";
#			if ($debug) {echo "$sql\n";}
			$res_match = mQuery($sql);
			$row_match = mysql_fetch_assoc($res_match);
			$new_categories[] = $row_match['category_id'];
		}
		if ($debug) echo "</table></td>";

		$sql = "SELECT category_label FROM mt_category c WHERE c.category_id IN (". join(', ', $new_categories) .")";
		$res_clabel = mQuery($sql);
		while ($row_clabel = mysql_fetch_assoc($res_clabel)) $new_cat_labels[] = $row_clabel['category_label'];
		if ($debug) echo '<td nowrap="nowrap">'. join('<br />', $new_cat_labels) ."</td></tr>";

		# Delete old categories
		$sql = "DELETE FROM mt_placement WHERE placement_entry_id = {$row['entry_id']}";
		if ($debug) {echo "$sql\n";} else {mQuery($sql);}

		# Insert new categories
		$is_primary = 1;
		foreach ($new_categories as $category_id) {
			$sql = "INSERT INTO mt_placement (placement_entry_id, placement_blog_id, placement_category_id, placement_is_primary)
			VALUES ({$row['entry_id']}, '{$row['entry_blog_id']}', $category_id, $is_primary)";
			if ($debug) {echo "$sql\n";} else {mQuery($sql);}
			$is_primary = 0; # Only indicate the first category as primary
		} # categories

	}
	if ($debug) echo '</table>';

	include(BASE_DIR .'/includes/body_footer.php');?>
	<br />
</div></body>
</html>