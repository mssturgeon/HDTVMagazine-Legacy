<?
	$debug = isset($_GET[debug]);
	if ($debug) {
		header('Content-Type: text/plain');
	} else {
		header('Content-Type: application/xml');
	}
	require('../global.php');
#	if (!access(ACCESS_ADMIN_ANY)) access_denied();

	# Input parameters
	$qid = isset($_GET[qid]) ? $_GET[qid] : exit();
	$width = isset($_GET[width]) ? $_GET[width] : exit();
	$type = isset($_GET[type]) ? $_GET[type] : exit();
	$order_by = isset($_GET[order]) ? $_GET[order] : exit();
	
   $strColor[] = '3C5987'; # Primary Color
   $strColor[] = '995905'; # Secondary color
   $strColor[] = 'AFD8F8';
   $strColor[] = 'F6BD0F';
   $strColor[] = '999999'; #Grey
   $strColor[] = '0099CC'; #Blue Shade
#   $strColor[] = '1941A5'; #Dark Blue
   $strColor[] = '8BBA00';
   $strColor[] = 'A66EDD';
   $strColor[] = 'F984A1';
   $strColor[] = 'CCCC00'; #Chrome Yellow+Green
   $strColor[] = 'FF0000'; #Bright Red
   $strColor[] = '006F00'; #Dark Green
   $strColor[] = '0099FF'; #Blue (Light)
   $strColor[] = 'FF66CC'; #Dark Pink
   $strColor[] = '669966'; #Dirty green
   $strColor[] = '7C7CB4'; #Violet shade of blue
   $strColor[] = 'FF9933'; #Orange
   $strColor[] = '9900FF'; #Violet
   $strColor[] = '99FFCC'; #Blue+Green Light
   $strColor[] = 'CCCCFF'; #Light violet
   $strColor[] = '669900'; #Shade of green

	$sql = "
   SELECT Heading, OptionID, OptionText answer, COUNT(*) num
   FROM survey_q q, survey_qo qo, survey_r r
   WHERE q.QID = qo.QID
   	AND q.QID = r.QID
   	AND qo.OptionID = r.Key1
   	AND q.QID = $qid
   GROUP BY OptionText
	ORDER BY $order_by";
	$result = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result)) {
#		$caption = $row[Heading];
		$values[$row[OptionID]] = $row[num];
	}
	$min = min($values);
	$max = max($values);
	
	# Define chart params
#	$subcaption = 'Previous 30 days';
	
/*
			numberPrefix='$'
			alternateHGridAlpha='100'
			alternateHGridColor='eeeeee'
			animation='0'
			baseFontColor='666666'
			bgColor='F1f1f1'
			canvasBorderColor='666666'
			decimalPrecision='0'
			divLineAlpha='20'
			divLineColor='333333'
			formatNumber='0'
			formatNumberScale='0'
			lineColor='003f87'
			pieYScale='45'
			pieBorderAlpha='40'
			pieFillAlpha='70'
			pieSliceDepth='15'
			rotateNames='1'
			showAlternateHGridColor='1'
			showColumnShadow='1'
			showNames='1'
			showPercentageInLabel='0'
			showPercentageValues='0'
			showValues='0'
			subCaption='$subcaption'
			yAxisMinValue='$min'
			yAxisMaxValue='$max'
*/
	$params = ($type == 'FC_2_3_Pie3D') ? 'pieRadius="50" pieSliceDepth="15" pieYScale="45" showPercentageInLabel="1"' : 'animation="0"';
	echo '<graph decimalPrecision="0" caption="'. $caption .'" '. $params .' showNames="1" showValues="1" >'."\n";

	mysql_data_seek($result, 0);
	$i = 0;
	while ($row = $db->sql_fetchrow($result)) {
		$answer = str_replace(array('\'', '<'), array('"', 'less than'), $row[answer]);
		$sliced = ($max == $values[$row[OptionID]] && $type == 'FC_2_3_Pie3D') ? ' isSliced="1"' : '';
		echo '<set '.
		'name="'. htmlspecialchars($answer, ENT_COMPAT, 'UTF-8') .'" '.
		'value="'. $row[num] .'" '.
		'hoverText="'. htmlspecialchars($answer, ENT_COMPAT, 'UTF-8') .'" '.
		'color="'. $strColor[$i++] .'" '. $sliced .'/>'."\n";
	}

	echo "</graph>\n";
?>
