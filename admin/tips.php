<?
	require('../global.php');
	if (!access(ACCESS_ADMIN)) prompt_login(PHP_SELF);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>HDTV Magazine - Grid Guide Tips</title>
	<?require(BASE_DIR .'/includes/common_header.php');?>
	<script language="javascript">
		function editTip(tid) {
         var h = 250;
         var w = 500;
         var t = (screen.height - h) / 2;
         var l = (screen.width - w) / 2;

         window.open('/admin/tip-edit.php?tid='+tid, 'AddTip', 'resizable=yes,scrollbars=yes,width='+w+',height='+h+',top='+t+',left='+l)
		}
	</script>
</head>
<body>
	<?
		require(BASE_DIR .'/includes/tracker.php');
		require(BASE_DIR .'/includes/body_header.php');
	?>
	
	<table class="type1b" align="center">
		<tr><td class="type1b_header">Tips</td></tr>
		<tr><td class="buttonBar"><input type="button" class="inputButton" name="btnAdd" value="Add Tip" onClick="editTip()"></td></tr>
		<?	
      	$qry = "SELECT * FROM tips";
      	$result = mQuery($qry);
      	
      	while ($row = mysql_fetch_assoc($result)) {
      		echo '<tr><td>'. $row['tip'] .' (<a href="javascript:editTip('. $row['id'] .')">Edit</a>)</td></tr>';
      	}
		?>
	</table>

	<?require(BASE_DIR .'/includes/body_footer.php');?>
</body>
</html>
