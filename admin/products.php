<?
	set_time_limit(0);
	require('../global.php');
	if (!access(ACCESS_ADMIN_ANY)) prompt_login(PHP_SELF);
	header('Cache-Control: no-store'); // HTTP/1.1

	$action = isset($_POST[action]) ? $_POST[action] : '';
	if ($action == 'submit') {
		for ($x=1; $x <= $_POST[number]; $x++) {
			$qry = 'UPDATE tbl_models SET '.
			'display_name = \''. $_POST["display_name$x"] .'\', '.
			'size = '. $_POST["size$x"] .', '.
			'type = \''. $_POST["type$x"] .'\', '.
			'projection = \''. $_POST["projection$x"] .'\', '.
			'h_res = \''. $_POST["h_res$x"] .'\', '.
			'v_res = \''. $_POST["v_res$x"] .'\', '.
			'contrast_ratio = \''. $_POST["contrast_ratio$x"] .'\', '.
			'lumens = \''. $_POST["lumens$x"] .'\', '.
//			'tuner = '. $_POST["tuner$x"] .', '.
			'edited = 1 WHERE man_id = '. $_POST["man_id$x"] .' AND model_name = \''. $_POST["model_name$x"] .'\'';
			mQuery($qry);
//			echo "$qry<br>";
		}
//		exit;
		js_back();
		exit;
	}
	
//	$type = isset($_GET[type]) ? $_GET[type] : '';
//	$where = ($type == 'category') ? 'category_id IS NULL' : 'rank = 0';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>HDTV Magazine - HDTV Products Admin</title>
	<?require(BASE_DIR .'/includes/common_header.php');?>
</head>
<body>
	<?
		include(BASE_DIR .'/includes/tracker.php');
		include(BASE_DIR .'/includes/body_header.php');
		
		$qry_zero_size = "SELECT c.name, m.* FROM tbl_companies c, tbl_models m WHERE c.id = m.man_id AND m.type IN (2,3,4,5,6,7) AND size = 0 AND projection <> ". MODEL_PROJ_TYPE_FRONT ." LIMIT 15";
		$unmatched_display_name = "SELECT c.name, m.* FROM tbl_companies c, tbl_models m WHERE c.id = m.man_id AND m.type NOT IN (0,1,9,11,12) AND LOCATE(display_name, title) = 0 ORDER BY man_id";
	?>

				<h1>HDTV Products Admin</h1>
				<a href="<?=URL_ADMIN_PRODUCTS_ALL?>">List All Products</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<a href="<?=URL_ADMIN_PRODUCTS?>?qry=<?=rawurlencode($qry_zero_size)?>">"Size = 0" Products</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<a href="<?=URL_ADMIN_PRODUCTS?>?qry=<?=rawurlencode($unmatched_display_name)?>">Unmatched Display Name</a>

				<table class="type1b" cellpadding="0" cellspacing="0" width="100%">
					<form action="<?=PHP_SELF?>" method="post" name="frm">
						<input type="hidden" name="action" value="submit">
						<?
							$header = '<tr>'.
   						'	<td class="type1b_header">Names</td>'.
   						'	<td class="type1b_header">Size</td>'.
   						'	<td class="type1b_header">Type</td>'.
	   					'	<td class="type1b_header">Projection</td>'.
   						'	<td class="type1b_header">Resolution</td>'.
   						'	<td class="type1b_header">Contrast</td>'.
   						'	<td class="type1b_header">Lumens</td>'.
   						'	<td class="type1b_header">Title</td>'.
	   					'</tr>';
							echo $header;
							
							$qry = isset($_GET[qry]) ? stripslashes(rawurldecode($_GET[qry])) : "SELECT c.name, m.* FROM tbl_companies c, tbl_models m WHERE c.id = m.man_id AND (edited = 0 OR m.type = '') ORDER BY man_id, model_name LIMIT 50";
							$result = mQuery($qry);
							echo '<input type="hidden" name="number" value="'. mysql_num_rows($result) .'">';
							
							$x = 0;
  							while ($row = mysql_fetch_assoc($result)) {
								$row_class = (++$x % 2 == 0) ? 'evenRow' : 'oddRow';
								$selected_yes = ($row[tuner] === '1') ? 'SELECTED' : '';
								$selected_no = ($row[tuner] === '0') ? 'SELECTED' : '';
								$xml = 'http://ah.pricegrabber.com/export_feeds.php?pid=aididid&document_type=xml&limit=&topcat_id=single&category=topcat:single&col_description=1&masterid='. $row[pg_id] .'&limit=1&stype=M';
								$xml = 'http://ah.pricegrabber.com/search_xml.php?masterid='. $row[pg_id] .'&upc=1&spec=2&pur=1&pid=718&key=7e084a24802';
								
								echo '<input type="hidden" name="man_id'. $x .'" value="'. $row[man_id] .'">'.
								'<input type="hidden" name="model_name'. $x .'" value="'. $row[model_name] .'">'.
								'<tr class="'. $row_class .'">'.
								'	<td class="grid"><table class="bare">'.
								'		<tr><td colspan="2">'. $row[name] .' - <a href="'. PG_URL_TECH_SPECS .'/masterid='. $row[pg_id] .'" target="_blank">'. $row[display_name] .'</a> (<a href="'. $xml .'">XML</a>)</td></tr>'.
								'		<tr><td class="inputLabel" nowrap>Display Name:</td><td><input type="text" class="inputText" style="width:10em" name="display_name'. $x .'" value="'. $row[display_name] .'"/></td></tr>'.
								'	</table></td>'.
								'	<td class="grid"><input type="text" class="inputText" style="width:3em" name="size'. $x .'" value="'. $row[size] .'"/></td>';

								echo '<td class="grid"><select name="type'. $x .'"><option value="">';
#								for ($value=1; $value<=count($MODEL_TYPE); $value++) {
								foreach ($MODEL_TYPE as $value => $text) {
									$selected = ($row[type] == $value) ? 'selected="selected"' : '';
									echo '<option value="'. $value .'" '. $selected .'>'. $text;
								}
								echo '</select></td>';

								echo '<td class="grid"><select name="projection'. $x .'"><option value="">';
								for ($t=1;$t<=count($MODEL_PROJ_TYPE);$t++) {
									$selected = ($row[projection] == $t) ? 'SELECTED' : '';
									echo '<option value="'. $t .'" '. $selected .'>'. $MODEL_PROJ_TYPE[$t];
								}
								echo '</select></td>';

								echo ''.
//								'	<td class="grid">'. $row[aspect] .'</td>'.
								'	<td class="grid"><table class="bare">'.
								'		<tr><td class="inputLabel" nowrap>H:</td><td><input type="text" class="inputText" style="width:4em" name="h_res'. $x .'" value="'. $row[h_res] .'"/></td>'.
								'		<td class="inputLabel" nowrap>V:</td><td><input type="text" class="inputText" style="width:4em" name="v_res'. $x .'" value="'. $row[v_res] .'"/></td></tr>'.
								'	</table></td>'.
								'	<td class="grid"><input type="text" style="text-align:right" class="inputText" size="4" name="contrast_ratio'. $x .'" value="'. $row[contrast_ratio] .'"/></td>'.
								'	<td class="grid"><input type="text" style="text-align:right" class="inputText" size="4" name="lumens'. $x .'" value="'. $row[lumens] .'"/></td>'.
//								'	<td class="grid"><select name="tuner'. $x .'"><option value="NULL">'.
//								'		<option value="1" '. $selected_yes .'>Yes'.
//								'		<option value="0" '. $selected_no .'>No'.
//								'	</select></td>'.
								'	<td class="grid">'. $row[title] .'</td>'.
								'</tr>';
							}
						?>
				</table>
				<br>
				
 						<div align="center"><input type="submit" value="Submit Info" class="inputButton"></div>
					</form>

	<?include(BASE_DIR .'/includes/body_footer.php');?>

</body>
</html>
