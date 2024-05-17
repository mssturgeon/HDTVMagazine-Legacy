<?
	require('../global.php');
	if (!access(ACCESS_ADMIN)) prompt_login(PHP_SELF);

	$action = isset($_POST['action']) ? $_POST['action'] : '';
	switch($action) {
		case 'save':
			if ($_POST['id'] == '') {
     			$qry = "INSERT INTO tips (tip) VALUES ('". $_POST['tip'] ."')";
			} else {
     			$qry = "UPDATE tips SET tip = '". $_POST['tip'] ."' WHERE id = ". $_POST['id'];
			}
      	$result = mQuery ($qry);
      
         js_close_reload();
			exit;
			break;
		case 'delete':
  			$qry = "DELETE FROM tips WHERE id = ". $_POST['id'];
      	$result = mQuery ($qry);
      
         js_close_reload();
			exit;
			break;
		default:
			if (isset($_GET['tid']) && $_GET['tid'] != undefined) {
				$qry = "SELECT * FROM tips WHERE id = ". $_GET['tid'];
				$result = mQuery($qry);
				$row = mysql_fetch_assoc($result);
			}
         ?>
         <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
         <html>
         <head>
         	<title>HDTV Magazine - Add Tip</title>
         	<?require(BASE_DIR .'/includes/common_header.php')?>
				<script language="javascript">
					function deleteTip() {
						if (confirm("Are you sure you wish to permanently remove this Tip?")) {
							document.frm.action.value = 'delete';
							document.frm.submit();
						}
					}
				</script>
         </head>
         <body style="margin:10px" onLoad="frm.tip.focus()">
         	<form name="frm" action="<?=PHP_SELF?>" method="post">
					<input type="hidden" name="action" value="save">
					<input type="hidden" name="id" value="<?=@$row['id']?>">
            	<table class="table1" style="width:100%">
            		<tr>
            			<td class="table1Header" colspan="2">Add Tip</td>
            		</tr><tr>
            			<td class="inputLabel">Tip:</td>
            			<td><input type="text" class="inputText" name="tip" style="width:70ex" value="<?=@htmlspecialchars($row['tip'])?>"></td>
            		</tr><tr>
            			<td class="buttonBar" colspan="2">
            				<input type="submit" name="btnSubmit" value="Save" class="inputButton">
            				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            				<input type="button" name="btnClose" value="Delete" onClick="deleteFAQ()" class="inputButton">
            				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            				<input type="button" name="btnClose" value="Cancel" onClick="window.close()" class="inputButton">
            			</td>
            		</tr>
            	</table>
         	</form>
         </body>
         </html>
<?}?>
