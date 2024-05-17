<?
	header('Content-Type: application/xml');
	define('BASE_DIR', '/var/www/html');
	require(BASE_DIR .'/includes/constants.php');
	require(BASE_DIR .'/includes/lib_mysql.php');

	$vote_id = isset($_GET['vote_id']) ? $_GET['vote_id'] : '';
	$qry = "SELECT vote_text caption, CONCAT(vote_option_id, '. ', vote_option_text) name, vote_result value FROM phpbb_vote_results r, phpbb_vote_desc d WHERE r.vote_id = d.vote_id AND r.vote_id = $vote_id ORDER BY vote_option_id";
	$result = mQuery($qry);
	
	$row = mysql_fetch_assoc($result);
	echo '<graph caption="'. $row['caption'] .'" canvasBorderColor="333333" animation="1" numdivlines="4" showNames="1" showValues="0" chartLeftMargin="0" chartRightMargin="5" chartTopMargin="5"'.
	' chartBottomMargin="5" showLegend="0" showAnchors="0" formatNumberScale="1" decimalPrecision="0" formatNumber="1" hoverCapSepChar=" " divLineColor="CCCCCC" showAlternateHGridColor="1" numberPrefix="">';
	mysql_data_seek($result, 0);
	while ($row = mysql_fetch_assoc($result)) {
		echo "	<set name='{$row['name']}' value='{$row['value']}' color='". PRIMARY_COLOR_HEX ."'/>\n";
	}
	echo "</graph>\n";
?>
