<?
	require('../global.php');
	if (!access(ACCESS_ADMIN_ANY)) prompt_login(PHP_SELF);
	
	$action = 'Edit';
	if (isset($_POST['action'])) {
		$action = $_POST['action'];
	} else if ($_GET['man_id'] == '') {
		$action = 'Add';
	}

	switch ($action) {
		case 'submit':
			$flags = is_array($_POST['flags']) ? array_sum($_POST['flags']) : 0;
			if ($_POST['id'] == '') {
				$qry = "INSERT INTO tbl_companies (name, alias, symbol, url, flags, type) VALUES ('{$_POST['name']}', '{$_POST['alias']}', '{$_POST['symbol']}', '{$_POST['url']}', $flags, {$_POST['type']})";
			} else {
				$qry = "UPDATE tbl_companies SET alias = '{$_POST['alias']}', url = '{$_POST['url']}', symbol = '{$_POST['symbol']}', flags = $flags, type = {$_POST['type']}  WHERE id = {$_POST['id']}";
			}
			$result = mQuery($qry);
			js_close();
			break;
		case 'Edit':
			$qry = "SELECT * FROM tbl_companies WHERE id = ". $_GET['man_id'];
			$result = mQuery($qry);
			$row = mysql_fetch_array($result);
			$ck_investment = ($row['flags'] & COMP_FLAG_INV) ? 'CHECKED' : '';
			$ck_equipment = ($row['flags'] & COMP_FLAG_EQUIP) ? 'CHECKED' : '';
		default:?>
         <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
         <html>
         <head>
         	<title>HDTV Magazine - <?=$action?> Manufacturer</title>
         	<?require(BASE_DIR .'/includes/common_header.php')?>
         	<script language="javascript" type="text/javascript">
         		function validate() {
         			with (document.frm) {
         				if (alias.value == '') {
         					alert('Alias Required!');
         					alias.focus();
         					return false;
         				}
         			}
         			return true;
         		}
         	</script>
         </head>
         <body onload="window.focus();document.frm.alias.focus()">
         	<form name="frm" action="<?=PHP_SELF?>" method="post" onSubmit="return validate()">
         		<input type="hidden" name="action" value="submit">
         		<input type="hidden" name="id" value="<?=$_GET['man_id']?>">
            	<table class="table1" style="width:100%">
            		<tr><td class="table1Header" colspan="2"><?=$action?> Manufacturer</td></tr>
						<tr>
            			<td class="inputLabel">Name:</td>
							<?if ($action == 'Edit') {
            				echo '<td>'. $row['name'] .'</td>'.
								'<input type="hidden" name="name" value="'. $row['name'] .'">';
							} else {
            				echo '<td><input type="text" class="inputText" name="name" maxlength="100" style="width:40ex" value=""> (limit 100 characters)</td>';
							}?>
            		</tr><tr>
            			<td class="inputLabel">Alias:</td>
            			<td><input type="text" class="inputText" name="alias" maxlength="100" style="width:40ex" value="<?=$row['alias']?>"> (limit 100 characters)</td>
            		</tr><tr>
            			<td class="inputLabel">Type:</td>
            			<td><?=getSelectBox("SELECT DISTINCT name, id FROM tbl_company_type WHERE active = 1 ORDER BY name", "type", $row['type'], "")?></td>
            		</tr><tr>
            			<td class="inputLabel">Stock Symbol:</td>
            			<td><input type="text" class="inputText" name="symbol" maxlength="5" style="width:5em" value="<?=$row['symbol']?>"> (limit 5 characters)</td>
            		</tr><tr>
            			<td class="inputLabel">Website:</td>
            			<td><input type="text" class="inputText" name="url" maxlength="100" style="width:40ex" value="<?=$row['url']?>"> (limit 100 characters)</td>
            		</tr><tr>
            			<td class="inputLabel">&nbsp;</td>
            			<td><input type="checkbox" name="flags[]" value="<?=COMP_FLAG_INV?>" <?=$ck_investment?>>Include on Investment page</td>
            		</tr><tr>
            			<td class="inputLabel">&nbsp;</td>
            			<td><input type="checkbox" name="flags[]" value="<?=COMP_FLAG_EQUIP?>" <?=$ck_equipment?>>Include on Equipment page</td>
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
