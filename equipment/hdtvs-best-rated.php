<?
	require('../global.php');
	require('hdtvs-overall_header.php');

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - Best Rated HDTVs</title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header-4.php');
	?>

	<h1>Best Rated HDTVs</h1>

	<? include('hdtvs-page_header.php');?>

	<div style="display:table;">
		<?=getTabHeader($tabs);?>
		<div class="tab-content">
			The Best HDTVs based on consumer rating. These include HDTVs available within the past <?=$update_days?> days and with at least 2 reviews (number of reviews listed in parenthesis).<br />
			<br />
			<?
				$types = array(
					'LCD' => 'subtype = '. MODEL_TYPE_LCD,
					'Plasma' => 'subtype = '. MODEL_TYPE_PLASMA,
					'DLP' => 'subtype = '. MODEL_TYPE_DLP,
					'LCoS' => 'subtype = '. MODEL_TYPE_LCOS,
					'CRT' => 'subtype = '. MODEL_TYPE_CRT,
				);
				echo '<table class="bare">';
				foreach ($types as $key => $clause) {
					$sql = "
					SELECT a.ASIN, Manufacturer, Model, Title, TotalNew, TotalUsed, ListPrice, LowestNewPrice, LowestUsedPrice, AverageRating, TotalReviews, SmallImageURL as image_url
					FROM az_main m, az_attributes a, az_aux aux
					WHERE m.ASIN = a.ASIN
						AND m.ASIN = aux.ASIN
						AND $clause
						AND ListPrice > 0
						AND date_updated > '$today' - INTERVAL {$admindata['amazon_update_window']} DAY
--						AND TotalReviews >= 2
					ORDER BY AverageRating DESC, TotalReviews DESC LIMIT 5";
					if ($debug) echo "$sql\n";
					$result = mQuery($sql);

					# Get first row and pull the picture
					$row = mysql_fetch_assoc($result);
					$alt = $row['Manufacturer'] .' '. $row['Model'];
					echo '<tr><td style="text-align:center; vertical-align:top; padding-right:10px;">'.
					'<img src="'. $row['image_url'] .'" alt="'. $alt .'"></td><td>';

					echo '<h2><a href="'. strtolower($key) .'.php">Best Rated '. $key .' HDTVs</a></h2>';
					mysql_data_seek($result, 0);
					while ($row = mysql_fetch_assoc($result)) {
						#echo '<img src="'. $row[image_url] .'" alt="" align="left">';
						$rating = ($row['AverageRating'] == '') ? '(Unrated)' : '<img src="/images/stars5-'. $row['AverageRating'] .'.gif" alt="'. $row['AverageRating'] .'" align="absmiddle">';
						$price = ($row['LowestUsedPrice'] > 0) ? $row['LowestUsedPrice'] : $row['LowestNewPrice'];
						$price = ($price > 0) ? $price : $row['ListPrice'];
						echo '<div style="color:#777777; padding:2px">'.
						$rating .' ('. $row['TotalReviews'] .') '.
						'<a href="'. URL_EQUIPMENT_MODEL .'?a='. $row['ASIN'] .'&man='. $row['Manufacturer'] .'&model='. $row['Model'] .'">'. $row['Title'] .'</a>'.
						' - <span class="primary_bold">$'. number_format($price/100, 2) .'</span>'.
						'</div>';
					}
					echo '<div class="shade_border"><a href="'. strtolower($key) .'.php">More '. $key .' Televisions</a></div><br clear="all"/></tr>';
				}
				echo '</table>';
			?>
		</div>
	</div>

	<?
		include(BASE_DIR .'/includes/body_footer-4.php');
#		include('hdtvs-page_footer.php');
	?>
</div></body>
</html>