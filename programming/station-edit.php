<?
	require('../global.php');
//	if (!access(ACCESS_PREMIUM)) prompt_login(PHP_SELF);
	if ($user->data['user_id'] == '') prompt_login(PHP_SELF);

	$action = isset($_POST['action']) ? $_POST['action'] : '';
	if ($action == 'save') {
		if ($_POST['priority'] == '') $_POST['priority'] = 999;
		$qry = "UPDATE join_user_station SET label = '". $_POST['label'] ."', priority = ". $_POST['priority'] .", channel = '". $_POST['channel'] ."' WHERE id = ". $_POST['jid'];
		$result = mQuery ($qry);

		// Now the the record has been inserted/updated, renumber the items.
		$x = 1;
		$qry = "SELECT * FROM join_user_station WHERE user_id = ". $user->data['user_id'] ." ORDER BY priority, channel*1, label ASC";
		$renum_result = mQuery($qry);
		while ($renum_row = mysql_fetch_assoc($renum_result)) {
			$qry = "UPDATE join_user_station SET priority = ". $x++ ." WHERE id = ". $renum_row['id'];
			mQuery($qry);
		}
		js_close_reload();
		exit;
	}

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - Edit Station</title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
	<script language="javascript">
		function validate() {
			with (document.frmEditStation) {
				if (isNaN(priority.value)) {
					alert('Priority must be a number!');
					priority.focus();
					return false;
				}

				if (parseInt(priority.value) < 0 || parseInt(priority.value) > 65535) {
					alert('Priority must be between 0 and 65535!');
					priority.focus();
					return false;
				}
			}
			return true;
		}
	</script>
</head>
<body onload="window.focus();document.frmEditStation.label.focus()">
	<?
		$qry = "SELECT channel, label, priority FROM join_user_station j WHERE j.id = ". $_GET['jid'] ."";
		$result = mQuery($qry);
		$row = mysql_fetch_array($result);
	?>

   	<form name="frmEditStation" action="<?=PHP_SELF?>" method="post" onSubmit="return validate()">
   		<input type="hidden" name="action" value="save">
   		<input type="hidden" name="jid" value="<?=$_GET['jid']?>">
   		<table class="table1" style="width:100%">
   			<tr>
   				<td class="table1Header" colspan="2">Edit Station</td>
   			</tr><tr>
   				<td class="inputLabel">Label:</td>
   				<td><input type="text" class="inputText" name="label" maxlength="40" style="width:45ex" value="<?=$row['label']?>"> (limit 40 characters)</td>
   			</tr><tr>
   				<td class="inputLabel">Channel:</td>
   				<td><input type="text" class="inputText" name="channel" maxlength="8" style="width:8ex" value="<?=$row['channel']?>"> (For digital broadcast channels, use x.y format)</td>
   			</tr><tr>
   				<td class="inputLabel">Priority:</td>
   				<td><input type="text" class="inputText" name="priority" style="width:5ex" value="<?=round($row['priority'])?>"></td>
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
