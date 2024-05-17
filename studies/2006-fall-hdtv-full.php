<?
	require('../global.php');
	if (!access(ACCESS_ADMIN_ANY)) prompt_login(PHP_SELF);
	
	# Questions
	$visited = "QID = 30711947 AND OptionID = 264120431";
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title>HDTV Magazine - HDTV Study Results</title>
	<?require(BASE_DIR .'/includes/common_header.php');?>
</head>
<body>
	<?
		include(BASE_DIR .'/includes/body_header.php');
	?>
	
	<h1>HDTV Study Results</h1>
	
	<?
		$sql = "
		SELECT QID, QType, q.Heading
		FROM survey_p p, survey_q q
		WHERE p.PageID = q.PageID
		ORDER BY p.Position, q.Position";
#		echo $sql;
		$result_q = $db->sql_query($sql);
		while ($row_q = $db->sql_fetchrow($result_q)) {
			echo '<h2>'. $row_q[Heading] .'</h2>';

			$sql = "
			SELECT OptionText answer, COUNT(*) num
			FROM survey_q q, survey_qo qo, survey_r r
			WHERE q.QID = qo.QID
				AND q.QID = r.QID
				AND qo.OptionID = r.Key1
				AND q.QID = $row_q[QID]
			GROUP BY OptionText
			ORDER BY OptionID";
#			echo $sql;
			$result_r = $db->sql_query($sql);
			echo '<table>';
			while ($row_r = $db->sql_fetchrow($result_r)) {
				echo '<tr><td style="padding-right:5px">'. $row_r[num] .'</td><td>'. $row_r[answer] .'</td></tr>';
			}
			echo '</table>';
	
			$sql = "
			SELECT Value, COUNT(*) num 
			FROM survey_r 
			WHERE Key2 = 1 
				AND QID = $row_q[QID] 
			GROUP BY Value 
			ORDER BY Value";
#			echo $sql;
			$res_text = $db->sql_query($sql);
			if (mysql_num_rows($res_text) > 0) {
				$others = array();
				while ($row = $db->sql_fetchrow($res_text)) $others[] = $row[Value] .' ('. $row[num] .')';
				echo '<b>Other Answers:</b><br /><div class="scroll" style="height:200px;width:300px">'. implode('<br /> ', $others) .'</div>';
			}
			echo '<br /><br />';
		}
	?>
	
	<?include(BASE_DIR .'/includes/body_footer.php');?>
</body>
</html>
