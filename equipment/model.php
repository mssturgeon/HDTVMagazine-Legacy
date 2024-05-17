<?
	require('../global.php');
	require('model-overall_header.php');

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - Pricing Comparison: <?=$title?></title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header-4.php');
	?>

	<h1><?=$title?></h1>
	<div id="eq-mrec"><? include(BASE_DIR .'/ads/mrectangle.php');?></div>
	<?=$breadcrumbs?>

	<? include('model-page_header.php');?>

	<br id="eq_br" />

	<div id="eq-sky"><? include(BASE_DIR .'/ads/skyscraper.php');?></div>

	<div style="display:table">
		<?=getTabHeader($tabs);?>
		<div class="tab-content">
			<?
/*
				if ($row['pg_masterid'] == 0) {
					echo 'No Price Comparison Data Available.';
				} else {
*/
				echo '<script language="javascript" type="text/javascript"
				src="http://ah.pricegrabber.com/cb_table.php?masterid='. $row['pg_masterid'] .
				'&keyword='. $row['ManufacturerAlias'] .' '. $row['Model'] .
				'&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=1&l=20&spic=1&ssbox=1"></script>';
/*		}*/
			?>
		</div>
	</div>

	<? if (access(ACCESS_ADMIN_ANY)) {
		include(BASE_DIR .'/includes/lib_admin.php');
		echo getRowInformation($row);
	}?>

	<?
		include(BASE_DIR .'/includes/body_footer-4.php');
		include('model_footer.php');
	?>
</div></body>
</html>
