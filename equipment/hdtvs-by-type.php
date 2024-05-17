<?
	require('../global.php');
	require('hdtvs-overall_header.php');

	$type = isset($_GET['type']) ? $_GET['type'] : '';
	$page_title = ($type == '') ? 'HDTVs by Type' : $MODEL_TYPE[$type];

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - <?=$page_title?></title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
	<script type="text/javascript">
		function show_images() {
			link = document.getElementById('show');
			link.style.display = 'none';

			imgs = document.getElementsByName('img');
			for (x=0;x<imgs.length;x++) {
				imgs[x].style.display = 'inline';
			}

			link = document.getElementById('hide');
			link.style.display = 'inline';
		}

		function hide_images() {
			link = document.getElementById('hide');
			link.style.display = 'none';

			imgs = document.getElementsByName('img');
			for (x=0;x<imgs.length;x++) {
				imgs[x].style.display = 'none';
			}

			link = document.getElementById('show');
			link.style.display = 'inline';
		}
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header-4.php');
	?>

	<h1><?=$page_title?></h1>

	<? include('hdtvs-page_header.php');?>

	<div style="display:table;">
		<?=getTabHeader($tabs);?>
		<div class="tab-content">
			<table class="shade_border" cellpadding="0" cellspacing="0" style="width:75%;" align="center">
				<tr>
						<?
						$result = mQuery("SELECT m.type, count(*) count FROM tbl_models m WHERE edited = 1 AND m.type > 1 GROUP BY m.type ORDER BY count DESC");
						$x = 0;
						$columns = 4;
						while ($row = mysql_fetch_assoc($result)) {
							if ($x++ % $columns == 0) echo '</td></tr><tr>';
							echo '	<td style="width:'. (100/$columns) .'%;padding:0px 3px 5px" nowrap>';
							if ($row['type'] == $type) echo '<span class="primary_bold_10">&raquo;</span>';
							echo '		<a href="'. PHP_SELF .'?'. $MODEL_TYPE[$row['type']] .'&type='. $row['type'] .'">'. $MODEL_TYPE[$row['type']] .'</a>&nbsp;&nbsp;('. $row['count'] .')';
						}
						echo '</td></tr></table><br>';

						if ($type !='') {
							echo '<table class="type1b" cellpadding="0" cellspacing="0" width="100%">';
							$header = '<tr>'.
								'	<td class="type1b_header" style="text-align:center;font-weight:normal">'.
							'		<a id="show" href="javascript:show_images()">Show Images</a>'.
							'		<a id="hide" style="display:none" href="javascript:hide_images()">Hide Images</a>'.
							'	</td>'.
								'	<td class="type1b_header">Manufacturer/Model</td>'.
								'	<td class="type1b_header" style="text-align:center">Size (in)</td>'.
							'	<td class="type1b_header">Projection Type</td>'.
								'	<td class="type1b_header" style="text-align:center">Aspect</td>'.
								'	<td class="type1b_header" style="text-align:center">Resolution</td>'.
								'	<td class="type1b_header" style="text-align:right">Contrast</td>'.
								'	<td class="type1b_header" style="text-align:right">Lumens</td>'.
								'	<td class="type1b_header">Built-in Tuner</td>'.
								'	<td class="type1b_header" style="text-align:right">Lowest Price</td>'.
							'</tr>';
							echo $header;

							$result = mQuery("SELECT alias, m.* FROM tbl_models m, tbl_companies c WHERE man_id = c.id AND edited = 1 AND m.type = $type ORDER BY size");
							$x = 0;
							while ($row = mysql_fetch_assoc($result)) {
								$row_class = (++$x % 2 == 0) ? 'evenRow' : 'oddRow';
								$product_image = ($row['pg_product_image'] == '') ? '' : '<img name="img" style="display:none" src="'. $row['pg_product_image'] .'" alt="'. $row['display_name'] .'"">';
								$model = '<a href="'. URL_EQUIPMENT_MODEL .'?man='. $row['alias'] .'&model='. $row['model_name'] .'">'. $row['display_name'] .'</a>';
								$size = ($row['size'] == 0) ? '' : $row['size'] .'"';
								$resolution = ($row['h_res'] == 0) ? '' : $row['h_res'] .'x'. $row['v_res'];
								$lumens = ($row['lumens'] == 0) ? '' : $row['lumens'];
								$tuner = ($row['tuner'] == 1) ? 'Yes' : '';
								$tuner = ($row['tuner'] === '0') ? 'No' : $tuner;
								$lowest_price = ($row['pg_lowest_price'] == 0) ? '' : '<a href="'. URL_EQUIPMENT_MODEL .'?man='. $row['alias'] .'&model='. $row['model_name'] .'">'. '$'. number_format($row['pg_lowest_price'], 2) .'</a>';

								echo '<tr class="'. $row_class .'">'.
								'	<td class="grid" style="text-align:center">'. $product_image .'</td>'.
								'	<td class="grid">'. $row['alias'] .' - '. $model .'</td>'.
								'	<td class="grid" style="text-align:center">'. $size .'</td>'.
								'	<td class="grid">'. $MODEL_PROJ_TYPE[$row['projection']] .'</td>'.
								'	<td class="grid" style="text-align:center">'. $row['screen_aspect'] .'</td>'.
								'	<td class="grid" style="text-align:center">'. $resolution .'</td>'.
								'	<td class="grid" style="text-align:right">'. $row['contrast'] .'</td>'.
								'	<td class="grid" style="text-align:right">'. $lumens .'</td>'.
								'	<td class="grid">'. $tuner .'</td>'.
								'	<td class="grid" style="text-align:right">'. $lowest_price .'</td>'.
								'</tr>';
							}
							echo '</table>';
						}
					?>
				</tr>
			</table>
		</div>
	</div>

	<?
		include(BASE_DIR .'/includes/body_footer-4.php');
#		include('hdtvs-page_footer.php');
	?>
</div></body>
</html>