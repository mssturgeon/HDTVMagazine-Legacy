<?
	require('../global.php');
	if (!access(ACCESS_ADMIN_ANY)) prompt_login(PHP_SELF);

	$action = isset($_POST['action']) ? $_POST['action'] : '';
	switch($action) {
		case 'save':
			$qry = "UPDATE tbl_models SET".
			" display_name = '{$_POST['display_name']}', ".
			" size = '{$_POST['size']}', ".
			" type = '{$_POST['type']}', ".
			" projection = '{$_POST['projection']}', ".
			" v_res = '{$_POST['v_res']}', ".
			" h_res = '{$_POST['h_res']}', ".
			" lumens = '{$_POST['lumens']}', ".
			" contrast_ratio = '{$_POST['contrast_ratio']}' ".
			"WHERE man_id = {$_POST['man_id']} AND model_name = '{$_POST['model_name']}'";
			mQuery($qry);
			js_close_reload();
			break;
		default:?>
			<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
			<html>
			<head>
				<title>HDTV Magazine - Admin Edit Equipment</title>
				<?require(BASE_DIR .'/includes/common_header.php');

				$qry = "SELECT * FROM tbl_models WHERE man_id = {$_GET['man_id']} AND model_name = '{$_GET['model_name']}'";
				$result = mQuery($qry);
				$row = mysql_fetch_array($result);
				
				?>
			</head>
			<body style="margin:10px" onLoad="window.focus();document.frmEdit.display_name.focus()">
				<form name="frmEdit" action="<?=PHP_SELF?>" method="post" enctype="multipart/form-data">
					<input type="hidden" name="action" value="save">
					<input type="hidden" name="man_id" value="<?=$row['man_id']?>">
					<input type="hidden" name="model_name" value="<?=$row['model_name']?>">
					<table class="table1" style="width:100%">
						<tr>
							<td class="table1Header" colspan="2">Edit Equipment</td>
						</tr><tr>
							<td class="inputLabel" nowrap>Model Name:</td>
							<td>
								<?=$row['model_name']?>
								(<a target="_blank" href="<?=$row['manuf_url']?>">Manufacturer Link</a>)
								(<a target="_blank" href="<?=FULL_URL_SHOPPING?>/search_techspecs_full.php/masterid=<?=$row['pg_id']?>">PriceGrabber</a>)
							</td>
						</tr><tr>
							<td class="inputLabel" nowrap>Title:</td>
							<td><?=$row['title']?></td>
						</tr><tr>
							<td class="inputLabel" nowrap>Display Name:</td>
							<td><input type="text" class="inputText" name="display_name" value="<?=$row['display_name']?>"></td>
						</tr><tr>
							<td class="inputLabel">Size:</td>
							<td><input type="text" class="inputText" name="size" value="<?=$row['size']?>" style="width:3em"></td>
						</tr><tr>
							<td class="inputLabel">Type:</td>
   						<td><select name="type"><?
								for ($x=2; $x<count($MODEL_TYPE); $x++) {
									$selected = ($type == $x) ? 'SELECTED' : '';
   								echo '<option value="'. ($x+1) .'" '. $selected .'>'. $MODEL_TYPE[$x+1];
   							}
   						?></select></td>
						</tr><tr>
							<td class="inputLabel" nowrap>Projection Type:</td>
   						<td><select name="projection"><?
								for ($x=2; $x<count($MODEL_PROJ_TYPE); $x++) {
									$selected = ($type == $x) ? 'SELECTED' : '';
   								echo '<option value="'. $x .'" '. $selected .'>'. $MODEL_PROJ_TYPE[$x];
   							}
   						?></select></td>
						</tr><tr>
							<td class="inputLabel">Resolution:</td>
							<td><input type="text" class="inputText" name="h_res" value="<?=$row['h_res']?>" style="width:3em"> x <input type="text" class="inputText" name="v_res" value="<?=$row['v_res']?>" style="width:3em"></td>
						</tr><tr>
							<td class="inputLabel">Lumens:</td>
							<td><input type="text" class="inputText" name="lumens" value="<?=$row['lumens']?>" style="width:4em"></td>
						</tr><tr>
							<td class="inputLabel">Contrast:</td>
							<td><input type="text" class="inputText" name="contrast_ratio" value="<?=$row['contrast_ratio']?>" style="width:4em;text-align:right">:1</td>
						</tr><tr>
							<td class="inputLabel" style="vertical-align:top">Notes:</td>
							<td><?=$row['notes']?></td>
						</tr><tr>
							<td class="buttonBar" colspan="2">
								<input type="submit" name="btnSubmit" value="&nbsp;Save&nbsp;" class="inputButton">
								&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								<input type="button" name="btnClose" value="&nbsp;Close&nbsp;" onClick="window.close()" class="inputButton">
							</td>
						</tr>
					</table>
				</form>
			</body>
			</html>
	<?}?>
