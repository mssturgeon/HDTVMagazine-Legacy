<?
	require('../global.php');
	
	$charts = array(
		30711947 => array(type => 'FC_2_3_Pie3D', width => 125, height => 125, height_adjust => -70, order_by => 'num DESC'),
		30925705 => array(type => 'FC_2_3_Pie3D', width => 125, height => 125, height_adjust => -30, order_by => 'num DESC'),
		30926348 => array(type => 'FC_2_3_Bar2D', width => 400, height => 200, height_adjust => 50, order_by => 'num DESC'),
		30926420 => array(type => 'FC_2_3_Bar2D', width => 400, height => 200, height_adjust => 60, order_by => 'OptionID'),
		30928488 => array(type => 'FC_2_3_Bar2D', width => 400, height => 175, height_adjust => -30, order_by => 'num DESC', has_text => ''),
		30928311 => array(type => 'FC_2_3_Pie3D', width => 250, height => 125, height_adjust => -30, order_by => 'num DESC'),
#		31217602 => array(type => 'FC_2_3_Bar2D', width => 400, height => 175, height_adjust => -40, order_by => 'num DESC'),
		31136450 => array(type => 'FC_2_3_Pie3D', width => 125, height => 125, height_adjust => -50, order_by => 'num DESC'),
		31136460 => array(type => 'FC_2_3_Pie3D', width => 125, height => 125, height_adjust => -50, order_by => 'num DESC'),
		31218392 => array(type => 'FC_2_3_Bar2D', width => 400, height => 125, height_adjust => -25, order_by => 'OptionText'),
		31220019 => array(type => 'FC_2_3_Bar2D', width => 400, height => 175, height_adjust => -35, order_by => 'OptionID', has_text => ''),
		30926686 => array(type => 'FC_2_3_Pie3D', width => 200, height => 125, height_adjust => -65, order_by => 'num DESC'),
		30927954 => array(type => 'FC_2_3_Pie3D', width => 125, height => 100, height_adjust => -50, order_by => 'num DESC'),
		30678608 => array(type => 'FC_2_3_Bar2D', width => 400, height => 175, height_adjust => 75, order_by => 'num DESC'),
		30678643 => array(type => 'FC_2_3_Bar2D', width => 400, height => 175, height_adjust => 35, order_by => 'OptionID'),
#		30678655 => array(type => 'FC_2_3_Pie3D', width => 175, height => 100, height_adjust => 70, order_by => 'num DESC'),
		30678656 => array(type => 'FC_2_3_Bar2D', width => 400, height => 175, height_adjust => 75, order_by => 'num DESC'),
		31218024 => array(type => 'FC_2_3_Pie3D', width => 125, height => 125, height_adjust => -50, order_by => 'num DESC'),
		31217160 => array(type => 'FC_2_3_Bar2D', width => 400, height => 200, height_adjust => 50, order_by => 'num DESC'),
		31221468 => array(type => 'FC_2_3_Pie3D', width => 425, height => 100, height_adjust => -20, order_by => 'num DESC'),
		31224566 => array(type => 'FC_2_3_Pie3D', width => 175, height => 100, height_adjust => -20, order_by => 'num DESC'),
		30678790 => array(type => 'FC_2_3_Bar2D', width => 400, height => 175, height_adjust => 10, order_by => 'num DESC', has_text => '')
#		31224190 => array(type => 'FC_2_3_Pie3D', width => 125, height => 125, height_adjust => -50, order_by => 'num DESC'),
#		30678499 => array(type => 'FC_2_3_Pie3D', width => 125, height => 100, height_adjust => -50, order_by => 'num DESC'),
#		30678510 => array(type => 'FC_2_3_Pie3D', width => 125, height => 100, height_adjust => 60, order_by => 'OptionID'),
#		30678512 => array(type => 'FC_2_3_Pie3D', width => 175, height => 100, height_adjust => 20, order_by => 'num DESC'),
#		30678515 => array(type => 'FC_2_3_Pie3D', width => 250, height => 100, height_adjust => 0, order_by => 'num DESC'),
#		30678517 => array(type => 'FC_2_3_Pie3D', width => 250, height => 100, height_adjust => 150, order_by => 'num DESC'),
#		30678516 => array(type => 'FC_2_3_Pie3D', width => 175, height => 100, height_adjust => -50, order_by => 'num DESC')
	);

	function getChart($qid) {
		global $charts;
		$flash_vars = "&alternateRowBgColor=E2CA9C&alternateRowBgAlpha=10&listRowDividerColor=995905&listRowDividerAlpha=70&textVerticalPadding=0&numberItemsPerPage=11";
		$base_url = 'http://'. SERVER_NAME;
		
		$name = $type = $charts[$qid][type];
		$width = $charts[$qid][width];
		$height = $charts[$qid][height];
		$order = $charts[$qid][order_by];
		$height_grid = $charts[$qid][height] + $charts[$qid][height_adjust];
		$url = rawurlencode("$base_url/xml/study_xml.php?qid=$qid&width=$width&type=$type&order=$order");

		$chart = '<object id="'. $qid .'" width="'. $width .'" height="'. $height .'" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0">'."\n".
		'	<param name="movie" value="'. $base_url .'/charts/'. $type .'.swf?dataURL='. $url .'&chartHeight='. $height .'&chartWidth='. $width .'">'."\n".
		'	<param name="FlashVars" value="">'."\n".
		'	<param name="quality" value="high">'."\n".
		'	<embed'."\n".
		'		src="'. $base_url .'/charts/'. $type .'.swf?dataURL='. $url .'&chartHeight='. $height .'&chartWidth='. $width .'"'."\n".
		'		FlashVars=""'."\n".
		'		quality="high"'."\n".
		'		width="'. $width .'"'."\n".
		'		height="'. $height .'"'."\n".
		'		name="$name"'."\n".
		'		type="application/x-shockwave-flash"'."\n".
		'		pluginspage="http://www.macromedia.com/go/getflashplayer">'."\n".
		'	</embed>'."\n".
		'</object><br />'."\n".
#		'<object id="'. $qid .'" width="'. $width .'" height="'. $height .'" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0">'."\n".
		'<object id="'. $qid .'" width="'. $width .'" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0">'."\n".
#		'	<param name="movie" value="'. $base_url .'/charts/FC_2_3_SSGrid.swf?dataURL='. $url .'&chartHeight='. $height .'&chartWidth='. $width .'">'."\n".
		'	<param name="movie" value="'. $base_url .'/charts/FC_2_3_SSGrid.swf?dataURL='. $url .'&chartWidth='. $width .'">'."\n".
		'	<param name="FlashVars" value="'. $flash_vars .'">'."\n".
		'	<param name="quality" value="high">'."\n".
		'	<embed'."\n".
#		'		src="'. $base_url .'/charts/FC_2_3_SSGrid.swf?dataURL='. $url .'&chartHeight='. $height .'&chartWidth='. $width .'"'."\n".
		'		src="'. $base_url .'/charts/FC_2_3_SSGrid.swf?dataURL='. $url .'&chartWidth='. $width .'"'."\n".
		'		FlashVars="'. $flash_vars .'"'."\n".
		'		quality="high"'."\n".
		'		width="'. $width .'"'."\n".
		'		height="'. $height_grid .'"'."\n".
		'		name="$name"'."\n".
		'		type="application/x-shockwave-flash"'."\n".
		'		pluginspage="http://www.macromedia.com/go/getflashplayer">'."\n".
		'	</embed>'."\n".
		'</object>';
		
		return $chart;
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title>HDTV Magazine - HDTV Study Results</title>
	<?require(BASE_DIR .'/includes/common_header.php');?>
</head>
<body>
	<?
		include(BASE_DIR .'/includes/tracker.php');
		include(BASE_DIR .'/includes/body_header.php');
		
		$sql = "
		SELECT 
			COUNT(DISTINCT RespondentID) respondents, 
			MIN(DateAdded) from_date, 
			MAX(DateAdded) to_date 
		FROM survey_r";
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
	?>
	
	<h1>HDTV Study Results</h1>
	<p>
		This page contains the result of the HDTV Study, conducted and sponsored by HDTV Magazine.
		The charts and data below reflect the preferences, buying habits, and general demographics of <b><?=$row[respondents]?></b>
		study respondents for the time period from <b><?=date('j M, Y', strtotime($row[from_date]))?></b> to <b><?=date('j M, Y', strtotime($row[to_date]))?></b>.
	</p><p>
		If you wish to republish any of this data, you must reference the "Fall 2006 HDTV Study" and "HDTV Magazine", 
		with appropriate links to the study results page and our website, respectively. We have included code that you may copy/paste next to each graph.
		Please do not alter this code.
	</p>
	
	<?
		$sql = "
		SELECT QID, QType, q.Heading
		FROM survey_p p, survey_q q
		WHERE p.PageID = q.PageID
		ORDER BY p.Position, q.Position";
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result)) {
			if (array_key_exists($row[QID], $charts)) {
				$width = $charts[$row[QID]][width];
				$height = $charts[$row[QID]][height];
				$height_adjust = $charts[$row[QID]][height_adjust];
				echo '<table class="type1b" cellpadding="0" cellspacing="0">'.
				'	<tr><td class="type1b_header" colspan="2" nowrap="nowrap">('. $row[QID] .') '. $row[Heading] .'</td></tr>'.
				'	<tr>'.
				'		<td class="grid" style="width:'. $width .'px;">'. getChart($row[QID]);
				if (array_key_exists('has_text', $charts[$row[QID]])) {
					$sql = "SELECT Value, COUNT(*) num FROM survey_r WHERE Key2 = 1 AND QID = $row[QID] GROUP BY Value ORDER BY Value";
					$res_text = $db->sql_query($sql);
					$others = array();
					while ($row_text = $db->sql_fetchrow($res_text)) $others[] = $row_text[Value] .' ('. $row_text[num] .')';
					echo '<br /><b>Other Answers:</b><br /><div class="scroll" style="height:200px">'. implode('<br /> ', $others);
				}
				if (access(ACCESS_ADMIN_ANY)) {
					echo '</td><td class="grid"><textarea style="width:300px;height:'. ($height*2 + $height_adjust) .'px" onfocus="select()" readonly>'. getChart($row[QID]) .'</textarea></td>';
				}
				echo '	</tr>'.
				'</table><br />'."\n";
			}
		}
	?>
	
	<?include(BASE_DIR .'/includes/body_footer.php');?>
</body>
</html>
