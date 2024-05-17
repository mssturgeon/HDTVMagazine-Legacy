<?
	require('../../global.php');
	$debug = isset($_GET[debug]);
	
	$sql = "SELECT page_id, page_title FROM wikidb.page WHERE page_restrictions <> 'sysop'";
	$result = mQuery($sql);
	while ($row = mysql_fetch_assoc($result)) $entries[$row[page_id]] = $row[page_title];
	
	$sql = "SELECT page_id, text FROM wikidb.page p, wikidb.text t WHERE p.page_id = t.old_id AND page_restrictions <> 'sysop'";
	$result = mQuery($sql);
	while ($row = mysql_fetch_assoc($result)) {
		
	}
?>
