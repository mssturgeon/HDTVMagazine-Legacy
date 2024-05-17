<?
	require('../global.php');
	if (!access(ACCESS_ADMIN_ANY)) prompt_login(PHP_SELF);

	$type = isset($_GET['type']) ? $_GET['type'] : '';
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
	
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>HDTV Magazine - Invalid Products - <?=$type?></title>
	<?require(BASE_DIR .'/includes/common_header.php');?>
</head>
<body>
	<?
		include(BASE_DIR .'/includes/tracker.php');
		include(BASE_DIR .'/includes/body_header.php');
	?>

				<h1>Invalid Products - <?=$type?></h1>
				Invalid Products by:
				<?
					$types[] = 'Size';
					$types[] = 'Resolution';
					foreach ($types as $t) {
						echo '<a href="'. URL_ADMIN_PRODUCTS_INVALID .'?type='. $t .'">'. $t .'</a>&nbsp;&nbsp;&nbsp;';
					}
				?>
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
							
							switch (strtolower($type)) {
								case 'size':
									$excluded_types[] = MODEL_TYPE_HIDE;
									$excluded_types[] = MODEL_TYPE_STB;
									$excluded_types[] = MODEL_TYPE_ANTENNA;
									$excluded_types[] = MODEL_TYPE_DVR;
									$excluded_types[] = MODEL_TYPE_VHS;
									$excluded_types[] = MODEL_TYPE_SCREEN;
									$excluded_types[] = MODEL_TYPE_SCALER;
									$excluded_types[] = MODEL_TYPE_CABLE;
									$excluded_types[] = MODEL_TYPE_CARD;
									$where = "size = 0 AND projection <> ". MODEL_PROJ_TYPE_FRONT ." AND m.type NOT IN (". implode($excluded_types, ',') .")";
									break;
								case 'resolution':
									$excluded_types[] = MODEL_TYPE_HIDE;
									$where = "v_res < 720 AND v_res > 0 AND m.type NOT IN (". implode($excluded_types, ',') .")";
									break;
								default:
									$where = '1';
							}
							$qry = "SELECT c.name, m.* FROM tbl_companies c, tbl_models m WHERE c.id = m.man_id AND $where ORDER BY man_id, model_name LIMIT 15";
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
							}
						?>
				</table>
				<br>
				
 						<div align="center"><input type="submit" value="Submit Info" class="inputButton"></div>
					</form>

	<?include(BASE_DIR .'/includes/body_footer.php');?>

</body>
</html>
