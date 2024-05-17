<?
	require('global.php');
	if (!access(ACCESS_ADMIN)) prompt_login(PHP_SELF);
	
	$action = isset($_POST['action']) ? $_POST['action'] : '';
	if ($action == 'save') {
		$result = mQuery("REPLACE INTO glossary (term, definition) VALUES ('{$_POST['term']}', '". trim(nl2br($_POST['definition'])) ."')");
		js_close_reload();
		exit;
	} elseif ($action == 'add') {
		$result = mQuery("REPLACE INTO glossary (term, definition) VALUES ('{$_POST['term']}', '". trim(nl2br($_POST['definition'])) ."')");
	}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>HDTV Magazine - Add Term</title>
	<?require(BASE_DIR .'/includes/common_header.php');?>
	<meta name="description" content="HDTV Magazine Programming ">
	<meta name="keywords" content="hdtv,hdtv programming,hdtv guide,hdtv schedule,hdtv listing,hdtv guides,hdtv schedules,hdtv listings,hdtv programs,hdtv programming guide,hdtv programming schedule,hdtv program,hd guide,hd programming,hd schedule,abc hdtv programming,cbs hdtv programming,digital tv programming,digital tv schedule,digital tv schedules,dtv listings,dtv program,dtv programing,dtv programming,dtv schedule,hd programing,hd programming,hd programs,high definition guide,high definition programing,high definition programming,high definition programs,high definition schedule,nbc hdtv programming,guide,news,sports,hdtv stations">
	<meta name="rating" content="general">
	<script language="JavaScript" type="text/javascript"><!--
		function saveClose() {
			frm.action.value = 'save';
			frm.submit();
		}
	//--></script>

</head>
<body onload="frm.term.focus()">
	<?
		require(BASE_DIR .'/includes/tracker.php');
	?>
	<form name="frm" action="<?=PHP_SELF?>" method="post">
		<input type="hidden" name="action" value="add">
		<table class="type1b">
			<tr>
				<td class="type1b_header" colspan="2">Add Term</td>
			</tr><tr>
				<td class="inputLabel" valign="top">Term:</td>
				<td><input type="text" class="inputText" name="term" style="width:100%"></td>
			</tr><tr>
				<td class="inputLabel" valign="top" width="80">Definition:</td>
				<td><textarea name="definition" rows="19"></textarea></td>
			</tr><tr>
				<td class="buttonBar" colspan="2">
					<input type="button" name="btnSubmit" value="&nbsp;Save/Close&nbsp;" class="inputButton" onClick="saveClose()">
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<input type="button" name="btnClose" value="&nbsp;Cancel&nbsp;" onClick="window.close()" class="inputButton">
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<input type="submit" name="btnSubmit" value="&nbsp;Add Another&nbsp;" class="inputButton">
				</td>
			</tr>
		</table>
	</form>
	
</body>
</html>
