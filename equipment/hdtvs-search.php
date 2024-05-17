<?
	require('../global.php');
	require('hdtvs-overall_header.php');

	$db_result = mQuery("SELECT ASIN FROM az_aux WHERE type = 'HDTVs'");
	$db_size = mysql_num_rows($db_result);

	$manufacturer = isset($_GET['manufacturer']) ? $_GET['manufacturer'] : '';
	$model = isset($_GET['model']) ? $_GET['model'] : '';
	$size_min = isset($_GET['size_min']) ? $_GET['size_min'] : '';
	$res_min = isset($_GET['res_min']) ? $_GET['res_min'] : '';
	$size_max = isset($_GET['size_max']) ? $_GET['size_max'] : '';
	$width = isset($_GET['width']) ? $_GET['width'] : '';
	$height = isset($_GET['height']) ? $_GET['height'] : '';
	$depth = isset($_GET['depth']) ? $_GET['depth'] : '';
	$price_min = isset($_GET['price_min']) ? $_GET['price_min'] : '';
	$price_max = isset($_GET['price_max']) ? $_GET['price_max'] : '';
	$subtype = isset($_GET['subtype']) ? $_GET['subtype'] : '';
	$format_disp = isset($_GET['format_disp']) ? $_GET['format_disp'] : '';
	$format_supp = isset($_GET['format_supp']) ? $_GET['format_supp'] : '';
	$sort = isset($_GET['sort']) ? $_GET['sort'] : '';

	function getFormattedProduct($product) {
		/* Given an ASIN, builds and returns a product listing */
		global $MODEL_TYPE;

		$model_link = URL_EQUIPMENT_MODEL .'?a='. $product['asin'] .'man='. rawurlencode($product['manufacturer']) .'&model='. rawurlencode($product['model']);
		if ($product['reviews'] > 0) {
			$reviews_text = '<a href="'. URL_EQUIPMENT_MODEL_REVIEWS .'?a='. $product['asin'] .
			'&man='. rawurlencode($product['manufacturer']) .
			'&model='. rawurlencode($product['model']) .'">'. $product['reviews'] .' Reviews</a>';
		} else {
			$reviews_text = 'No Reviews';
		}
		$manufacturer_link = URL_EQUIPMENT_MANUFACTURER .'?man='. rawurlencode($product['manufacturer']);
		$subtype_link = strtolower($MODEL_TYPE[$product['subtype']]) .'.php';
		if ($product['rating'] > 0) {
			$rating_text = '<img src="/images/stars5-'. $product['rating'] .'.gif" alt="'. $product['rating'] .'" align="absmiddle">';
		} else {
			$rating_text = '(Unrated)';
		}

		$formatted = '<div class="product" onmouseover="this.style.backgroundColor = \'#ecf3f7\';"'.
		' onmouseout="this.style.backgroundColor = \'transparent\';">'.
			'<span class="corners-top"><span></span></span>'.
			'<table class="bare" width="100%"><tr><td class="image">'.
				'<img src="'. $product['image'] .'" alt="'. $product['model'] .'"/>'.
			'</td><td>'.
				'<h2><a href="'. $model_link .'">'. stripslashes($product['title']) .'</a></h2>'.
				'<h4>'.
					'Manufacturer: <a href="'. $manufacturer_link .'">'. $product['manufacturer'] .'</a> &bull; '.
					'Model: <a href="'. $model_link .'">'. $product['model'] .'</a> &bull; '.
					'Type: <a href="'. $subtype_link .'">'. $MODEL_TYPE[$product['subtype']] .'</a> &bull; '.
					'Size: '. $product['size'] .' &bull; '.
					'Dimensions: '. $product['dimensions'] .
				'</h4>'.
				'<div class="features">'. stripslashes($product['description']) .'</div>'.
				'<div class="rating">'. $rating_text .' &bull; '. $reviews_text .'</div>'.
			'</td><td class="price">'.
				$product['price'] .
			'</td></tr></table>'.
			'<span class="corners-bottom"><span></span></span>'.
		'</div>'.
		'<div class="dottedline"></div>';

		return "$formatted\n";
	}

	include(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - HDTV Search</title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
</head>
<body id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>

	<h1>HDTV Search</h1>

	<? include('hdtvs-page_header.php');?>

	<form name="frmSearchEq" method="get" action="hdtvs-search.php">
		<input type="hidden" name="action" value="hdtv-search">
		<fieldset>
			<legend>HDTV Search</legend>
			<div style="float:left">
				<label for="price_min">Price:
					<input type="text" class="inputText" id="price_min" name="price_min" value="<?=$price_min?>" style="width:4em"> to
					<input type="text" class="inputText" name="price_max" value="<?=$price_max?>" style="width:4em"> (USD)
				</label><br />

				<label for="size_min">Size:
					<input type="text" id="size_min" name="size_min" value="<?=$size_min?>" style="width:3em"> to
					<input type="text" id="size_max" name="size_max" value="<?=$size_max?>" style="width:3em"> (inches)
				</label><br />

				<label for="subtype">Type:
					<select id="subtype" name="subtype">
						<option value=""><?
							$model_types = array(2, 3, 4, 5, 6, 21);
							foreach ($model_types as $model_type) {
								$selected = ($model_type == $subtype) ? 'SELECTED' : '';
								echo '<option value="'. $model_type .'" '. $selected .'>'. $MODEL_TYPE[$model_type];
							}
						?></select>
				</label><br />

				<label for="width">Physical Size (max):
				<div style="padding-left:4px">
					W: <input type="text" class="inputText" id="width" name="width" value="<?=$width?>" style="width:2.5em">
					H: <input type="text" class="inputText" id="height" name="height" value="<?=$height?>" style="width:2.5em">
					D: <input type="text" class="inputText" id="depth" name="depth" value="<?=$depth?>" style="width:2.5em"> (inches)
				</div>
				</label><br />
			</div>

			<div style="float:right">
				<label for="manufacturer">Manufacturer:
					<?=getSelectBox("SELECT DISTINCT ManufacturerAlias, ManufacturerAlias FROM az_aux aux WHERE type = 'HDTVs' ORDER BY ManufacturerAlias", "manufacturer", $manufacturer, 'id="manufacturer"')?>
				</label><br />

				<label for="model">Model:
					<input type="text" id="model" name="model" style="width:10em" value="<?=$model?>">
				</label><br />

				<label for="format_supp">Format Supported (Input):
					<select id="format_supp" name="format_supp">
						<option value="">
						<option value="720p" <?=($format_supp == '720p') ? 'SELECTED' : ''?>>720p
						<option value="1080i" <?=($format_supp == '1080i') ? 'SELECTED' : ''?>>1080i
						<option value="1080p" <?=($format_supp == '1080p') ? 'SELECTED' : ''?>>1080p
					</select>
				</label><br />

				<label for="format_disp">Format Displayed (Output):
					<select id="format_disp" name="format_disp">
						<option value="">
						<option value="720p" <?=($format_supp == '720p') ? 'SELECTED' : ''?>>720p
						<option value="1080i" <?=($format_supp == '1080i') ? 'SELECTED' : ''?>>1080i
						<option value="1080p" <?=($format_supp == '1080p') ? 'SELECTED' : ''?>>1080p
					</select>
				</label><br />
			</div>

			<br clear="both" />
			<div class="dottedline"></div>
			<label for="sort">Sort by:
				<select name="sort">
					<option value="Price" <?=($sort == 'Price' || $sort == '') ? 'SELECTED' : ''?>>Price (Low)
					<option value="Price DESC" <?=($sort == 'Price DESC') ? 'SELECTED' : ''?>>Price (High)
					<option value="DisplaySize" <?=($sort == 'DisplaySize') ? 'SELECTED' : ''?>>Size (Small)
					<option value="DisplaySize DESC" <?=($sort == 'DisplaySize DESC') ? 'SELECTED' : ''?>>Size (Large)
					<option value="ManufacturerAlias, Model" <?=($sort == 'ManufacturerAlias, Model') ? 'SELECTED' : ''?>>Manufacturer
				</select>
				<input type="submit" class="inputButton" value="Search">
			</label>
		</fieldset>
	</form><br />

	<div style="display:table;">
		<?=getTabHeader($tabs);?>
		<div class="tab-content">
			<div style="display:table"><?
				$action = isset($_GET['action']) ? $_GET['action'] : '';
				if ($action == 'hdtv-search') {
					// Update search log
					$qry = "
					INSERT INTO search_hdtv (timestamp, user_id, man_alias, model_name, size_min, size_max, width, height, depth, price_min, price_max, type, format_supp, format_disp, sort, res_min)
					VALUES (UNIX_TIMESTAMP(), '". $user->data['user_id'] ."', '$man_alias', '$model_name', '$size_min', '$size_max', '$width', '$height', '$depth', '$price_min', '$price_max', '$type', '$format_supp', '$format_disp', '$sort', '$res_min')";
					mQuery($qry);

					// Perform search
					$where = "m.ASIN = a.ASIN AND m.ASIN = aux.ASIN AND m.ASIN = f.ASIN AND type = 'HDTVs' AND LowestNewPrice > 0";
					$where .= ($manufacturer != '') ? " AND ManufacturerAlias = '$manufacturer'" : '';
					$where .= ($model != '') ? " AND (Model LIKE '%$model%')" : '';
					$where .= ($size_min != '') ? " AND DisplaySize >= $size_min" : '';
					$where .= ($size_max != '') ? " AND DisplaySize <= $size_max" : '';
					$where .= ($width != '') ? " AND Width <= ". ($width*100) : '';
					$where .= ($height != '') ? " AND Height <= ". ($height*100) : '';
					$where .= ($depth != '') ? " AND Length <= ". ($depth*100) : '';
					$where .= ($price_min != '') ? " AND LowestNewPrice >= ". ($price_min*100) : '';
					$where .= ($price_max != '') ? " AND LowestNewPrice <= ". ($price_max*100) : '';
					$where .= ($subtype != '') ? " AND subtype = $subtype" : '';
		#			$where .= ($format_disp != '') ? "AND LOCATE('$format_disp', format_disp) > 0 " : '';
		#			$where .= ($format_supp != '') ? "AND LOCATE('$format_supp', format_supp) > 0 " : '';
					$sql = "
					SELECT m.ASIN, SmallImageURL img, subtype, Title, ManufacturerAlias, Model, DisplaySize,
						FORMAT(Width/100, 1) Width, FORMAT(Height/100, 1) Height, FORMAT(Length/100, 1) Length,
						LowestNewPrice Price, LowestNewPriceFormatted, TotalReviews, AverageRating, GROUP_CONCAT(Feature) features
					FROM az_main m, az_attributes a, az_aux aux, az_features f
					WHERE $where
					GROUP BY m.ASIN, img, subtype, Title, ManufacturerAlias, Model, DisplaySize, Width, Height, Length, LowestNewPriceFormatted, TotalReviews, AverageRating
					ORDER BY $sort";
		#			echo $sql;
					$result = mQuery($sql);

					echo '<div style="background-color:#CEDFEF; border-bottom:1px solid #003f87; padding:2px">Returned <b>'. mysql_num_rows($result) .'</b> matches out of <b>'. $db_size .'</b> products</div>';
					while ($row = mysql_fetch_assoc($result)) {
						$product['asin'] = $row['ASIN'];
						$product['image'] = $row['img'];
						$product['title'] = $row['Title'];
						$product['subtype'] = $row['subtype'];
						$product['manufacturer'] = $row['ManufacturerAlias'];
						$product['model'] = $row['Model'];
						$product['size'] = ($row['DisplaySize'] == 0) ? '' : $row['DisplaySize'] .'"';
						$product['dimensions'] = $row['Width'] .'w '. $row['Height'] .'h '. $row['Length'] .'d';
						$product['price'] = $row['LowestNewPriceFormatted'];
						$product['description'] = $row['features'];
						$product['reviews'] = $row['TotalReviews'];
						$product['rating'] = $row['AverageRating'];

		#				$admin_edit_link = access(ACCESS_ADMIN_ANY) ? '<a href="javascript:edit_item(\''. $row[man_id] .'\', \''. $row[model_name] .'\')"><img src="/images/edit.gif" alt="edit"></a>' : '';

						echo getFormattedProduct($product);
					}
				} else {
					echo 'Please specify your search conditions above.';
				}
			?></div>
		</div>
	</div>

	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
</body>
</html>