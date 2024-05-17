<?
	require('../global.php');
	require('hdtvs-overall_header.php');

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - HDTVs - By Price</title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header-4.php');
	?>

	<h1>HDTVs - By Price</h1>

	<? include('hdtvs-page_header.php');?>

	<div style="display:table;">
		<?=getTabHeader($tabs);?>
		<div class="tab-content">
			Coming soon...
		</div>
	</div>

	<?
		include(BASE_DIR .'/includes/body_footer-4.php');
#		include('hdtvs-page_footer.php');
	?>
</div></body>
</html>