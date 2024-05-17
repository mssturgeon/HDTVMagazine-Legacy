<?
	set_time_limit(0);

	$debug = isset($_GET[debug]);
	if ($debug) header('Content-Type: text/plain');
	
	# BASE_DIR is the full OS path to the web document directory.  User primarily in include/require statements
	define('BASE_DIR', '/var/www/html');
	require(BASE_DIR .'/includes/constants.php');
	require(BASE_DIR .'/includes/lib_common.php');
	require(BASE_DIR .'/includes/lib_mysql.php');

	echo date('Y/m/d H:i:s') .":\tStarting link-az-pg...";

	$sql = "
	SELECT a.ASIN, pg_id
	FROM az_attributes a, tbl_models m, tbl_companies c
	WHERE m.man_id = c.id
		AND a.Model IN (m.model_name, m.display_name)
		AND a.Manufacturer IN (c.name, c.alias)
	";
	if ($debug) echo "$sql\n";
	$result = mQuery($sql);
	while ($row = mysql_fetch_assoc($result)) {
		$sql = "UPDATE az_aux SET pg_masterid = '$row[pg_id]' WHERE ASIN = '$row[ASIN]'";
		if ($debug) {echo "$sql\n";} else {mQuery($sql);}
	}
	echo "Done (link-az-pg)\n";
?>
