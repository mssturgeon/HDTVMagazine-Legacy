<?
	require('../global.php');
	$debug = isset($_GET[debug]);
	
	$man = isset($_GET[man]) ? rawurldecode($_GET[man]) : '';
	$sort = isset($_GET[sort]) ? $_GET[sort] : 'size';
	switch ($sort) {
		case 'size':
			$sort_display = '<b>Screen Size</b> [<a href="?man='. $man .'&sort=model">Model Name</a>] [<a href="?man='. $man .'&sort=price">Price</a>]';
			$order_by = 'size';
			break;
		case 'model':
			$sort_display = '[<a href="?man='. $man .'&sort=size">Screen Size</a>] <b>Model Name</b> [<a href="?man='. $man .'&sort=price">Price</a>]';
			$order_by = 'display_name';
			break;
		case 'price':
			$sort_display = '[<a href="?man='. $man .'&sort=size">Screen Size</a>] [<a href="?man='. $man .'&sort=model">Model Name</a>] <b>Price</b>';
			$order_by = 'pg_lowest_price';
			break;
	}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>HDTV Magazine - Manufacturer: <?=$man?></title>
	<?require(BASE_DIR .'/includes/common_header.php');?>
	<script language="JavaScript" type="text/javascript">
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
		include(BASE_DIR .'/ads/leaderboard.php');
		include(BASE_DIR .'/includes/body_header.php');
	?>

	<h1>Manufacturer: <?=$man?></h1>
	
	<span style="text-align:right;width:100%">Sorted by: <?=$sort_display?></span><br /><br />
	
	<h2><?=($man .' HDTV\'s &amp Accessories')?></h2>
	<table class="type1b" cellpadding="0" cellspacing="0" width="100%">
		<tr>
			<td class="type1b_header" style="text-align:center;font-weight:normal">
				<a id="show" href="javascript:show_images()">Show Images</a>
				<a id="hide" style="display:none" href="javascript:hide_images()">Hide Images</a>
			</td>
			<td class="type1b_header">Model</td>
			<td class="type1b_header" style="text-align:center">Size</td>
			<td class="type1b_header">Type</td>
			<td class="type1b_header">Projection Type</td>
			<td class="type1b_header" style="text-align:center">Aspect</td>
			<td class="type1b_header" style="text-align:center">Resolution</td>
			<td class="type1b_header" style="text-align:right">Contrast</td>
			<td class="type1b_header" style="text-align:right">Lumens</td>
			<td class="type1b_header">Built-in Tuner</td>
			<td class="type1b_header" style="text-align:right">Lowest Price</td>
		</tr>
		<?
			$sql = "
			SELECT *
			FROM az_main m, az_attributes a, az_aux aux
			WHERE aux.type = '$category'
				AND m.ASIN = a.ASIN
				AND m.ASIN = aux.ASIN
			ORDER BY $order_by";
			if ($debug) echo "$sql<br />";
			$result = mQuery($sql);
			while ($row = mysql_fetch_assoc($result)) {
				$url = URL_EQUIPMENT_MODEL .'?man='. rawurlencode($row[alias]) .'&model='. rawurlencode($row[model_name]);
				$product_image = ($row[pg_product_image] == '') ? '' : '<img name="img" style="display:none" src="'. $row[pg_product_image] .'" alt="'. $row[display_name] .'"">';
				$model = '<a href="'. $url .'">'. $row[display_name] .'</a>';
				$size = ($row[size] == 0) ? '' : $row[size] .'"';
				$resolution = ($row[h_res] == 0) ? 'N/A' : $row[h_res] .'x'. $row[v_res];
				$contrast_ratio = ($row[contrast_ratio] == 0) ? 'N/A' : $row[contrast_ratio];
				$lumens = ($row[lumens] == 0) ? 'N/A' : $row[lumens];
				$tuner = ($row[hdtv_tuner] == 1) ? 'Yes' : '';
				$tuner = ($row[hdtv_tuner] === '0') ? 'No' : $tuner;
				$lowest_price = ($row[pg_lowest_price] == 0) ? 'N/A' : '<a href="'. $url .'">'. '$'. number_format($row[pg_lowest_price], 2) .'</a>';

				echo '<tr>'.
				'	<td class="grid" style="text-align:center">'. $product_image .'</td>'.
				'	<td class="grid">'. $model .'</td>'.
				'	<td class="grid" style="text-align:center">'. $size .'</td>'.
				'	<td class="grid">'. $MODEL_TYPE[$row[type]] .'</td>'.
				'	<td class="grid">'. $MODEL_PROJ_TYPE[$row[projection]] .'</td>'.
				'	<td class="grid" style="text-align:center">'. $row[screen_aspect] .'</td>'.
				'	<td class="grid" style="text-align:center">'. $resolution .'</td>'.
				'	<td class="grid" style="text-align:right">'. $contrast_ratio .'</td>'.
				'	<td class="grid" style="text-align:right">'. $lumens .'</td>'.
				'	<td class="grid" style="text-align:center">'. $tuner .'</td>'.
				'	<td class="grid" style="text-align:right">'. $lowest_price .'</td>'.
				"</tr>\n";
			}
		?>
	</table>

	<?include(BASE_DIR .'/includes/body_footer.php');?>
</div></body>
</html>
