<?
	header('Content-Type: application/xml');
	require('../global.php');
	if (!access(ACCESS_ADMIN_ANY)) access_denied();

//	$qry = isset($_GET['q']) ? stripslashes(rawurldecode($_GET['q'])) : exit;
	$qry = "SELECT from_unixtime(user_regdate, '%m/%d') name, count(*) value FROM ". USERS_TABLE ." WHERE from_unixtime(user_regdate, '%Y-%m-%d') > (curdate() - interval 14 day) GROUP BY name ORDER BY name ASC";
	$result = mQuery($qry);

	echo "<graph decimalPrecision='0' numberPrefix=''>\n";
	while ($row = mysql_fetch_assoc($result)) {
		echo "	<set name='{$row['name']}' value='{$row['value']}' color='". PRIMARY_COLOR_HEX ."'/>\n";
	}
	echo "</graph>\n";
?>
