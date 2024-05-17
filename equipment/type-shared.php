<?
	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - <?=$s?> HDTVs</title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header-4.php');
	?>

	<h1><?=$s?> HDTVs</h1>

	<div id="right" style="display:table; float:right; margin:0 0 5px 20px; text-align:center;" align="center">
		<? include(BASE_DIR .'/ads/mrectangle.php');?>
		<br />
		<div align="center">
			<? include(BASE_DIR .'/ads/skyscraper.php');?>
		</div>
	</div>

	<?
		$today = date('Y-m-d');
		$sql = "
		SELECT a.ASIN, Manufacturer, Model, Title, TotalNew, TotalUsed, ListPrice, LowestNewPrice, LowestUsedPrice, AverageRating, TotalReviews
		FROM az_main m, az_attributes a, az_aux aux
		WHERE m.ASIN = a.ASIN
			AND m.ASIN = aux.ASIN
			AND $clause
--			AND date_updated > '$today' - INTERVAL {$admindata['amazon_update_window']} DAY
		ORDER BY AverageRating DESC, TotalReviews DESC";
		$result = mQuery($sql);
		while ($row = mysql_fetch_assoc($result)) {
			#echo '<img src="'. $row[image_url] .'" alt="" align="left">';
			$rating = ($row['AverageRating'] == '0') ? '(Unrated)' : '<img src="/images/stars5-'. $row['AverageRating'] .'.gif" alt="'. $row['AverageRating'] .'" align="absmiddle">';
			$price = ($row['LowestUsedPrice'] > 0) ? $row['LowestUsedPrice'] : $row['LowestNewPrice'];
			$price = ($price > 0) ? $price : $row['ListPrice'];
			echo '<div style="color:#777777; padding:2px">'.
			$rating .' ('. $row['TotalReviews'] .') '.
			'<a href="'. URL_EQUIPMENT_MODEL .'?a='. $row['ASIN'] .'&man='. $row['Manufacturer'] .'&model='. $row['Model'] .'">'. $row['Title'] .'</a>'.
			' - <span class="primary_bold">$'. number_format($price/100, 2) .'</span>'.
			'</div>';
		}
	?>

	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
</div></body>
</html>