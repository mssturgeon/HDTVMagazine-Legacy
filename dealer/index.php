<?
	require('../global.php');
	if (!access(ACCESS_DEALER)) prompt_login(PHP_SELF);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>HDTV Magazine - Dealer Administration Section</title>
	<meta name="description" content="">
	<meta name="keywords" content="hdtv,hd tv,high definition,high def tv,high definition television,high definition tv">
	<meta name="rating" content="general">
	<?require(BASE_DIR .'/includes/common_header.php');?>
	<script language="javascript" type="text/javascript">
		function edit(id) {
			 var h = 400;
			 var w = 500;
			 var t = (screen.height - h) / 2;
			 var l = (screen.width - w) / 2;
		
			 window.open('dealer-edit.php?id='+id, 'Add', 'resizable=yes,scrollbars=yes,width='+w+',height='+h+',top='+t+',left='+l)
		}
	</script>
</head>
<body>
	<?
		include(BASE_DIR .'/includes/tracker.php');
		include(BASE_DIR .'/includes/body_header.php');
	?>

	<h1>Dealer Administration</h1>
	
	<input type="button" class="inputButton" name="btnAdd" value="Add Dealer" onclick="edit('')"><br>
	<br>
	
	<table class="type1b"><tr>
		<td class="type1b_header">Active?</td>
		<td class="type1b_header">Dealer Name</td>
		<td class="type1b_header">Contact Name</td>
		<td class="type1b_header">Contact Phone</td>
		<td class="type1b_header">Intro Text</td>
		<td class="type1b_header">Logo</td>
		<td class="type1b_header">Ad #1</td>
		<td class="type1b_header">Ad #2</td>
		<td class="type1b_header">Ad #3</td>
	<?
		$result = mQuery("SELECT * FROM dealer");
		while ($row = mysql_fetch_assoc($result)) {
			echo '<tr>'.
			'	<td class="type1b">'. $row['active'] .'</td>'.
			'	<td class="type1b"><a href="javascript:edit('. $row['id'] .')">'. stripslashes($row['dealer_name']) .'</a></td>'.
			'	<td class="type1b"><a href="mailto:'. $row['contact_email'] .'">'. stripslashes($row['contact_name']) .'</a></td>'.
			'	<td class="type1b">'. $row['contact_phone'] .'</td>'.
			'	<td class="type1b">'. stripslashes($row['intro_text']) .'</td>'.
			'	<td class="type1b">'. $row['logo_234x60'] .'</td>'.
			'	<td class="type1b">'. $row['ad_1'] .'</td>'.
			'	<td class="type1b">'. $row['ad_2'] .'</td>'.
			'	<td class="type1b">'. $row['ad_3'] .'</td>'.
			'</tr>';
		}
	?>
	</table>
				
	<?include(BASE_DIR .'/includes/body_footer.php');?>
</body>
</html>
