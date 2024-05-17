<?
	header('Content-Type: application/xml');
	require('../global.php');
	if (!access(ACCESS_ADMIN_ANY)) access_denied();

	# Set colors
	$color[] = '0099FF';
	$color[] = 'FF66CC';
	$color[] = '996600';
	$color[] = '669966';
	$color[] = '7C7CB4';
	$color[] = 'FF9933';
	$color[] = 'CCCC00';
	$color[] = '9900FF';
	$color[] = '999999';
	$color[] = '99FFCC';
	$color[] = 'CCCCFF';
	$color[] = '669900';
	
	foreach ($SUB as $sub_value => $sub_label) $header["sub_$sub_value"] = $sub_label;

	# BEGIN GRAPH 
	echo "
		<graph
			alternateHGridColor='ff5904'
			alternateHGridAlpha='5'
			animation='0'
			baseFontColor='666666'
			canvasBorderColor='666666'
			caption='Subscribers'
			divLineAlpha='20'
			divLineColor='ff5904'
			formatNumber='0'
			formatNumberScale='0'
			rotateNames='0' 
			showAlternateHGridColor='1'
			showColumnShadow='1'
			showNames='1'
			showValues='0'
			subCaption='Previous 30 days'
		>\n";

	echo "<categories>\n";
	$sql = "
		SELECT DATE_FORMAT(datestamp, '%c/%e') date, a.*
		FROM admin_stats a
		WHERE
			datestamp BETWEEN CURDATE() - INTERVAL 30 DAY
			AND CURDATE() ORDER BY datestamp ASC";
	$result = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result)) {
		echo "<category name='$row[date]' />\n";
	}
	echo "</categories>\n";

	mysql_data_seek($result, 0);
	while ($row = $db->sql_fetchrow($result)) {
		foreach ($SUB as $sub_value => $sub_label) {
			$data["sub_$sub_value"][$row[datestamp]] = $row["sub_$sub_value"];
		}
	}

	$x = 0;
	foreach ($data as $type => $days) {
		echo "<dataset seriesname='$header[$type]' color='$color[$x]' showValue='0' alpha='100' anchorAlpha='100' lineThickness='2'>\n";
		foreach ($days as $day => $num) {
			echo "<set value='$num' />\n";
		}
		echo "</dataset>\n";
		$x++;
	}
	echo "</graph>\n";
?>
