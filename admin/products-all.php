<?
	require('../global.php');
	if (!access(ACCESS_ADMIN_ANY)) prompt_login(PHP_SELF);

	$action = isset($_POST['action']) ? $_POST['action'] : '';
	if ($action == 'submit') {
		for ($x=1; $x <= $_POST['number']; $x++) {
			$qry = 'UPDATE tbl_models SET '.
			'display_name = \''. $_POST["display_name$x"] .'\', '.
			'size = '. $_POST["size$x"] .', '.
			'type = \''. $_POST["type$x"] .'\', '.
			'projection = \''. $_POST["projection$x"] .'\', '.
			'h_res = \''. $_POST["h_res$x"] .'\', '.
			'v_res = \''. $_POST["v_res$x"] .'\', '.
			'contrast = \''. $_POST["contrast$x"] .'\', '.
			'lumens = \''. $_POST["lumens$x"] .'\', '.
			'tuner = '. $_POST["tuner$x"] .', '.
			'edited = 1 WHERE man_id = '. $_POST["man_id$x"] .' AND model_name = \''. $_POST["model_name$x"] .'\'';
			mquery($qry);
		}
		js_back();
		exit;
	}
	
//	$type = isset($_GET['type']) ? $_GET['type'] : '';
//	$where = ($type == 'category') ? 'category_id IS NULL' : 'rank = 0';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>HDTV Magazine - HDTV Products Admin (All Products)</title>
	<?require(BASE_DIR .'/includes/common_header.php');?>
</head>
<body>
	<?
		include(BASE_DIR .'/includes/tracker.php');
		include(BASE_DIR .'/includes/body_header.php');
	?>

				<h1>HDTV Products Admin (All)</h1>
				<table class="grid" cellpadding="0" cellspacing="0" width="100%">
					<form action="<?=PHP_SELF?>" method="post" name="frm">
						<input type="hidden" name="action" value="submit">
						<?
							$header = '<tr>'.
   						'	<td class="type1b_header">Names</td>'.
   						'	<td class="type1b_header">Size</td>'.
   						'	<td class="type1b_header">Type</td>'.
	   					'	<td class="type1b_header">Projection</td>'.
   						'	<td class="type1b_header">Aspect</td>'.
   						'	<td class="type1b_header">Resolution</td>'.
   						'	<td class="type1b_header">Contrast</td>'.
   						'	<td class="type1b_header">Lumens</td>'.
   						'	<td class="type1b_header">Tuner</td>'.
   						'	<td class="type1b_header">Title</td>'.
	   					'</tr>';
							echo $header;
							
							$qry = "SELECT c.name, m.* FROM tbl_companies c, tbl_models m WHERE c.id = m.man_id ORDER BY man_id, model_name";
							$result = mQuery($qry);
							echo '<input type="hidden" name="number" value="'. mysql_num_rows($result) .'">';
							
							$x = 0;
  							while ($row = mysql_fetch_assoc($result)) {
								$row_class = (++$x % 2 == 0) ? 'evenRow' : 'oddRow';
								$selected_yes = ($row['tuner'] === '1') ? 'SELECTED' : '';
								$selected_no = ($row['tuner'] === '0') ? 'SELECTED' : '';
								
								echo '<input type="hidden" name="man_id'. $x .'" value="'. $row['man_id'] .'">'.
								'<input type="hidden" name="model_name'. $x .'" value="'. $row['model_name'] .'">'.
								'<tr class="'. $row_class .'">'.
								'	<td class="grid"><table class="bare">'.
								'		<tr><td colspan="2">'. $row['name'] .' - <a href="'. PG_URL_PRODUCT .'/masterid='. $row['pg_id'] .'" target="_blank">'. $row['display_name'] .'</a></td></tr>'.
								'		<tr><td class="inputLabel" nowrap>DIsplay Name:</td><td><input type="text" class="inputText" style="width:10em" name="display_name'. $x .'" value="'. $row['display_name'] .'"/></td></tr>'.
								'	</table></td>'.
								'	<td class="grid"><input type="text" class="inputText" style="width:3em" name="size'. $x .'" value="'. $row['size'] .'"/></td>';

								echo '<td class="grid"><select name="type'. $x .'"><option value="">';
								for ($t=1;$t<=count($MODEL_TYPE);$t++) {
									$selected = ($row['type'] == $t) ? 'SELECTED' : '';
									echo '<option value="'. $t .'" '. $selected .'>'. $MODEL_TYPE[$t];
								}
								echo '</select></td>';

								echo '<td class="grid"><select name="projection'. $x .'"><option value="">';
								for ($t=1;$t<=count($MODEL_PROJ_TYPE);$t++) {
									$selected = ($row['projection'] == $t) ? 'SELECTED' : '';
									echo '<option value="'. $t .'" '. $selected .'>'. $MODEL_PROJ_TYPE[$t];
								}
								echo '</select></td>';

								echo '	<td class="grid">'. $row['aspect'] .'</td>'.
								'	<td class="grid"><table class="bare">'.
								'		<tr><td class="inputLabel" nowrap>H:</td><td><input type="text" class="inputText" style="width:4em" name="h_res'. $x .'" value="'. $row['h_res'] .'"/></td></tr>'.
								'		<tr><td class="inputLabel" nowrap>V:</td><td><input type="text" class="inputText" style="width:4em" name="v_res'. $x .'" value="'. $row['v_res'] .'"/></td></tr>'.
								'	</table></td>'.
								'	<td class="grid"><input type="text" style="text-align:right" class="inputText" style="width:4em" name="contrast'. $x .'" value="'. $row['contrast'] .'"/></td>'.
								'	<td class="grid"><input type="text" style="text-align:right" class="inputText" style="width:4em" name="lumens'. $x .'" value="'. $row['lumens'] .'"/></td>'.
								'	<td class="grid"><select name="tuner'. $x .'"><option value="NULL">'.
								'		<option value="1" '. $selected_yes .'>Yes'.
								'		<option value="0" '. $selected_no .'>No'.
								'	</select></td>'.
								'	<td class="grid">'. $row['title'] .'</td>'.
								'</tr>';
/*
								$selected = ($row['category_id'] == 'NULL') ? 'SELECTED' : '';
								'	<td style="padding:0px 3px" nowrap><select name="category'. $x .'">'.
								'		<option value="NULL" '. $selected .'>None</option>';
								$opt_result = mQuery('SELECT category_id, category_label FROM mt_category ORDER BY category_label');
								while ($opt_row = mysql_fetch_assoc($opt_result)) {
									$selected = ($row['category_id'] == $opt_row['category_id']) ? 'SELECTED' : '';
									echo '		<option value="'. $opt_row['category_id'] .'" '. $selected .'>'. $opt_row['category_label'] .'</option>';
								}
								echo '	</select></td>';
								echo '	<td style="padding:0px 3px" nowrap><select name="rank'. $x++ .'">'.
								'		<option value="-1">Purge</option>'.
								'		<option value="0" SELECTED>Unranked</option>'.
								'		<option value="1">1 - Low</option>'.
								'		<option value="2">2</option>'.
								'		<option value="3">3</option>'.
								'		<option value="4">4</option>'.
								'		<option value="5">5 - High</option>'.
								'	</select></td>'.
								'</tr><tr class="'. $row_class .'">'.
								'	<td>'. stripslashes($row['source']) .' - '. gmdate('M j, Y g:ia', $row['pubDate'] + $_SESSION['time_zone_offset']) .'</td><td>&nbsp;</td><td>&nbsp;</td>'.
								'</tr><tr class="'. $row_class .'">'.
								'	<td>'. stripslashes($row['description']) .'</td><td>&nbsp;</td><td>&nbsp;</td>'.
								'</tr>';
*/
							}
						?>
				</table>
				<br>
				
 						<div align="center"><input type="submit" value="Submit Info" class="inputButton"></div>
					</form>

	<?include(BASE_DIR .'/includes/body_footer.php');?>

</body>
</html>
