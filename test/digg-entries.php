<?
	require('global.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>HDTV Magazine - </title>
	<meta name="description" content="">
	<meta name="keywords" content="hdtv,hd tv,high definition,high def tv,high definition television,high definition tv">
	<meta name="rating" content="general">
	<?require(BASE_DIR .'/includes/common_header.php');?>
</head>
<body>
	<?
		require(BASE_DIR .'/includes/tracker.php');
		getAdUnit('728', '90', '728x90_as');
	?>
	
	<table class="main" cellpadding="0" cellspacing="0" align="center">
		<tr>
			<td style="border-bottom:1px solid <?=BORDER_COLOR?>">
				<?require(BASE_DIR .'/includes/header.php');?>
			</td>
		</tr><tr>
			<td style="border-bottom:1px solid <?=BORDER_COLOR?>">
				<?require(BASE_DIR .'/includes/menu_bar.php');?>
			</td>
		</tr><tr>
			<td class="content">
				
				<h1></h1>
				
			</td>
		</tr><tr>
			<td class="footer">
				<?require(BASE_DIR .'/includes/footer.php');?>
			</td>
		</tr>
	</table>

</body>
</html>
