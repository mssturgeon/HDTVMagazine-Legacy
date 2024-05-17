<?
	require('../global.php');
	if (!access(ACCESS_DEALER)) prompt_login(PHP_SELF);
	
	if (isset($_POST['action'])) {
		$action = $_POST['action'];
		$id = $_POST['id'];
	} else {
		$id = $_GET['id'];
		$action = ($id == '') ? 'Add' : 'Edit';
	}

	switch ($action) {
		case 'submit':
			$dealer_name = addslashes($_POST['dealer_name']);
			$contact_name = addslashes($_POST['contact_name']);
			$contact_email = strtolower($_POST['contact_email']);
			$intro_text = addslashes($_POST['intro_text']);
			
			if ($_POST['id'] == '') {
				$qry = "INSERT INTO dealer (dealer_name, contact_name, contact_email, contact_phone, logo_234x60, intro_text, ad_1, ad_2, ad_3)".
				" VALUES ('$dealer_name', '$contact_name', '$contact_email', '{$_POST['contact_phone']}', '{$_POST['logo_234x60']}', '$intro_text', '{$_POST['ad_1']}', '{$_POST['ad_2']}', '{$_POST['ad_3']}')";
			} else {
				$qry = "UPDATE dealer SET ".
				"dealer_name = '$dealer_name', ".
				"contact_name = '$contact_name', ".
				"contact_email = '$contact_email', ".
				"contact_phone = '{$_POST['contact_phone']}', ".
				"logo_234x60 = '{$_POST['logo_234x60']}', ".
				"intro_text = '$intro_text', ".
				"ad_1 = '{$_POST['ad_1']}', ".
				"ad_2 = '{$_POST['ad_2']}', ".
				"ad_3 = '{$_POST['ad_3']}'".
				" WHERE id = {$_POST['id']}";
			}
			$result = mQuery($qry);
			js_close_reload();
			break;
		case 'Edit':
			$qry = "SELECT * FROM dealer WHERE id = ". $id;
			$result = mQuery($qry);
			$row = mysql_fetch_array($result);
		default:?>
         <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
         <html>
         <head>
         	<title>HDTV Magazine - <?=$action?> Dealer</title>
         	<?require(BASE_DIR .'/includes/common_header.php')?>
         </head>
         <body onload="window.focus();document.frm.dealer_name.focus()" style="background-color:white;background-image:none">
         	<form name="frm" action="<?=PHP_SELF?>" method="post">
         		<input type="hidden" name="action" value="submit">
         		<input type="hidden" name="id" value="<?=$_GET['id']?>">

					<table class="type1b" style="width:100%;background-color:#<?=BG_COLOR?>">
						<tr>
            			<td class="inputLabel">Dealer Name:</td>
            			<td><input type="text" class="inputText" name="dealer_name" maxlength="100" style="width:30ex" value="<?=stripslashes($row['dealer_name'])?>"> (limit 100 characters)</td>
            		</tr><tr>
            			<td class="inputLabel">Contact Name:</td>
            			<td><input type="text" class="inputText" name="contact_name" maxlength="100" style="width:30ex" value="<?=stripslashes($row['contact_name'])?>"> (limit 100 characters)</td>
            		</tr><tr>
            			<td class="inputLabel">Contact Email:</td>
            			<td><input type="text" class="inputText" name="contact_email" maxlength="100" style="width:35ex" value="<?=$row['contact_email']?>"> (limit 100 characters)</td>
            		</tr><tr>
            			<td class="inputLabel">Contact Phone:</td>
            			<td><input type="text" class="inputText" name="contact_phone" maxlength="20" style="width:15ex" value="<?=$row['contact_phone']?>"></td>
            		</tr><tr>
            			<td class="inputLabel" nowrap>Logo (234x60):</td>
            			<td><input type="file" class="inputText" name="logo_234x60" value="<?=$row['logo_234x60']?>" size="50"></td>
            		</tr><tr>
            			<td class="inputLabel" valign="top">Intro Text:</td>
            			<td><textarea name="intro_text" cols="20"><?=stripslashes($row['intro_text'])?></textarea></td>
            		</tr><tr>
            			<td class="inputLabel">Ad #1 (468x60):</td>
            			<td><input type="file" class="inputText" name="ad_1" value="<?=$row['ad_1']?>" size="50"></td>
            		</tr><tr>
            			<td class="inputLabel">Ad #2 (468x60):</td>
            			<td><input type="file" class="inputText" name="ad_2" value="<?=$row['ad_2']?>" size="50"></td>
            		</tr><tr>
            			<td class="inputLabel">Ad #3 (468x60):</td>
            			<td><input type="file" class="inputText" name="ad_3" value="<?=$row['ad_3']?>" size="50"></td>
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
