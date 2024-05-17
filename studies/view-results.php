<?
	require('../global.php');
#	if (!access(ACCESS_ADMIN_ANY)) prompt_login(PHP_SELF);

	$sid = isset($_GET[sid]) ? $_GET[sid] : '';
	$hide_qids[] = 45182137; # Email Addresses
	$hide_qids[] = 44983535;
	$hide_qids[] = 44983536;
	$hide_qids[] = 44983537;
	$hide_qids[] = 44983538;
	$hide_qids[] = 44983544;
	$hide_qids[] = 44983546;
	$hide_qids[] = 31217602;
	$hide_qids[] = 30678499;
	$hide_qids[] = 30678510;
	$hide_qids[] = 30678512;
	$hide_qids[] = 30678515;
	$hide_qids[] = 30678516;
	$hide_qids[] = 30678517;
	$hide_qids[] = 68892311;
	$hide_qids[] = 68892312;
	$hide_qids[] = 68892313;
	$hide_qids[] = 68892314;
	$hide_qids[] = 68892310;
	$hide_qids[] = 68892315;
	$hide_qids[] = 68892327; # Email Addresses

	$sql = "SELECT title, from_date, to_date FROM survey_s WHERE SID = '$sid'";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$title = $row[title];
	$from_date = $row[from_date];
	$to_date = $row[to_date];

	$sql = "
	SELECT COUNT(DISTINCT RespondentID) respondents
	FROM survey_p p, survey_q q, survey_r r
	WHERE p.SID = '$sid'
		AND p.PageID = q.PageID
		AND q.QID = r.QID";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$respondents = $row[respondents];

		include(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - <?=$title?> Results</title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
	<style>
		.bar {border:1px solid #73681F; background-color:#F6EBAD; color:#73681F; font-weight:bold; padding:1px}
	</style>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header-4.php');
	?>

	<h1><?=$title?> Results</h1>

	<div style="width:100%"><table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left">
      	<b>Study Title: </b><?=$title?><br />
      	<b>Start Date: </b><?=date('j M, Y', strtotime($from_date))?><br />
      	<b>End Date: </b><?=date('j M, Y', strtotime($to_date))?><br />
      	<b>Respondents: </b><?=$respondents?><br />
      	<p>
      		If you wish to republish any of this data, please reference the "<?=$title?>" and "HDTV Magazine",
      		with appropriate links to the study results page and our web site, respectively.
      	</p>

      	<?
      		$x = 1;
      		$sql = "
				SELECT q.QID, QType, q.Heading, COUNT(*) num
      		FROM survey_p p, survey_q q, survey_r r
      		WHERE SID = '$sid'
      			AND p.PageID = q.PageID
      			AND q.QID = r.QID
					AND q.QID NOT IN (". implode(',', $hide_qids) .")
      		GROUP BY q.QID, QType, q.Heading
      		ORDER BY p.Position, q.Position";
      		$result_q = $db->sql_query($sql);
      		while ($row_q = $db->sql_fetchrow($result_q)) {
      			$qid = access(ACCESS_ADMIN_ANY) ? ' ('. $row_q[QID] .')' : '';
      			echo "<h2>$x. $row_q[Heading]$qid</h2>";

      			$sql = "
      			SELECT OptionText answer, COUNT(*) num
      			FROM survey_q q, survey_qo qo, survey_r r
      			WHERE q.QID = qo.QID
      				AND q.QID = r.QID
      				AND qo.OptionID = r.Key1
      				AND q.QID = $row_q[QID]
      			GROUP BY OptionText
      			ORDER BY OptionID";
      			$result_r = $db->sql_query($sql);
      			echo '<table>';
      			while ($row_r = $db->sql_fetchrow($result_r)) {
      				$width = round($row_r[num] / $respondents * 500);
      #				echo '<tr><td style="padding-right:5px">'. $row_r[num] .'</td><td>'. $row_r[answer] .'</td></tr>';
      				echo '<tr>'.
							'<td style="padding-right:5px">'. $row_r[answer] .'</td>'.
							'<td><div class="bar" style="width:'. $width .'px;">'. $row_r[num] .' - '. number_format($row_r[num] * 100 / $row_q[num], 1) .'%</div>'.
							'</td>'.
						'</tr>';
      			}
      			echo '</table>';

      			$sql = "
      			SELECT Value, COUNT(*) num
      			FROM survey_r
      			WHERE Key2 = 1
      				AND QID = $row_q[QID]
      			GROUP BY Value
      			ORDER BY Value";
      			$res_text = $db->sql_query($sql);
      			if (mysql_num_rows($res_text) > 0) {
      				$others = array();
      				while ($row = $db->sql_fetchrow($res_text)) $others[] = $row[Value] .' ('. $row[num] .')';
      				echo '<b>Other Answers:</b><br /><div class="scroll" style="height:200px;width:300px">'. implode('<br /> ', $others) .'</div>';
      			}
      			echo '<br /><br />';
      			$x++;
      		}
      	?>
		</td><td style="vertical-align:top;">
			<? include(BASE_DIR .'/ads/skyscraper.php');?>
		</td>
	</tr></table></div>

	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
</div></body>
</html>
