<?
	header('Content-Type: application/xml');
	require('../global.php');
	if (!access(ACCESS_ADMIN_ANY)) access_denied();

	$type = isset($_GET[type]) ? $_GET[type] : exit();
	
	$header = $SUB[$type];
	
	$sql = "
		SELECT DATE_FORMAT(datestamp, '%c/%e') date, sub_$type num
		FROM admin_stats a
		WHERE
			datestamp BETWEEN CURDATE() - INTERVAL 30 DAY
			AND CURDATE() ORDER BY datestamp ASC";
	$result = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result)) $values[] = $row[num];
	$min = round(min($values)-50, -2);
	$max = round(max($values)+50, -2);

	echo "
		<graph
			alternateHGridColor='eeeeee'
			alternateHGridAlpha='100'
			animation='0'
			baseFontColor='666666'
			canvasBorderColor='666666'
			caption='$header'
			divLineAlpha='20'
			divLineColor='333333'
			formatNumber='0'
			formatNumberScale='0'
			lineColor='003f87'
			rotateNames='1'
			showAlternateHGridColor='1'
			showColumnShadow='1'
			showNames='1'
			showValues='0'
			subCaption='Previous 30 days'
			yAxisMinValue='$min'
			yAxisMaxValue='$max'
		>\n";

	mysql_data_seek($result, 0);
	while ($row = $db->sql_fetchrow($result)) {
		echo "<set name='$row[date]' value='$row[num]' hoverText='$row[date]' />\n";
	}

	echo "</graph>\n";
?>
