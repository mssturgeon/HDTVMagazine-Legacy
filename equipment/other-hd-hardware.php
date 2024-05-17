<?
	require('../global.php');
	require('hdtvs-overall_header.php');
	$debug = isset($_GET['debug']);

	$days = 60; # Number of days to include

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - Other HD Hardware</title>
	<? require(BASE_DIR .'/includes/page_header-4.php');?>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header-4.php');
	?>

	<h1>Other HD Hardware</h1>

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