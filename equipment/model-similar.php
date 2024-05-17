<?
	require('../global.php');
	require('model-overall_header.php');

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - Similar Models: <?=$title?></title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header-4.php');
	?>

	<h1>Similar Models: <?=$title?></h1>
	<div id="eq-mrec"><? include(BASE_DIR .'/ads/mrectangle.php');?></div>
	<?=$breadcrumbs?>

	<? include('model-page_header.php');?>

	<br id="eq_br" />

	<div id="eq-sky"><? include(BASE_DIR .'/ads/skyscraper.php');?></div>

	<div style="display:table">
		<?=getTabHeader($tabs);?>
		<div class="tab-content">
			<table class="type1b" cellpadding="0" cellspacing="0" width="100%"><tr>
				<td class="type1b_header">Manufacturer</td>
				<td class="type1b_header">Model</td>
				<td class="type1b_header">Size</td>
				<td class="type1b_header">Type</td>
				<td class="type1b_header">Resolution</td>
				<td class="type1b_header">Price</td>
			</tr>
				<?
					$size = $row['size'];
					$qry = "SELECT alias, man_id, model_name, display_name, size, h_res, v_res, m.type, pg_lowest_price FROM tbl_companies c, tbl_models m WHERE m.man_id = c.id AND m.type = '$row[subtype]' AND size BETWEEN $size - 2 AND $size + 2";
					$result = mQuery($qry);
					while ($sim_row = mysql_fetch_assoc($result)) {
						$resolution = ($sim_row['h_res'] == 0) ? 'N/A' : $sim_row['h_res'] .'x'. $sim_row['v_res'];
						echo '<tr>'.
						'	<td class="type1b"><a href="'. URL_EQUIPMENT_MANUFACTURER .'?man='. rawurlencode($sim_row['alias']) .'">'. $sim_row['alias'] .'</a></td>'.
						'	<td class="type1b"><a href="'. URL_EQUIPMENT_MODEL .'?man='. rawurlencode($sim_row['alias']) .'&model='. rawurlencode($sim_row['model_name']) .'">'. $sim_row['display_name'] .'</a></td>'.
						'	<td class="type1b">'. $sim_row['size'] .'"</td>'.
						'	<td class="type1b">'. $MODEL_TYPE[$sim_row['type']] .'</td>'.
						'	<td class="type1b">'. $resolution .'</td>'.
						'	<td class="type1b">$'. number_format($sim_row['pg_lowest_price'], 2) .'</td>'.
						'</tr>';
					}
				?>
			</table>
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
