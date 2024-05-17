<?
	require('../global.php');
	require('hdtvs-overall_header.php');

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - HDTVs - By Manufacturer</title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header-4.php');
	?>

	<h1>HDTVs - By Manufacturer</h1>

	<? include('hdtvs-page_header.php');?>

	<div style="display:table">
		<?=getTabHeader($tabs);?>
		<div class="tab-content">
			<table class="simple" cellpadding="0" cellspacing="0">
				<tr class="header">
					<td>Company</td>
					<td>Products</td>
					<td>Types</td>
					<td>Display Sizes</td>
					<td>Average Rating</td>
					<td>Reviews</td>
					</tr>
				<?
					$sql = "
					SELECT ManufacturerAlias as man, COUNT(*) num, SUM(TotalReviews) num_reviews, TRUNCATE(ROUND(AVG(AverageRating)*2)/2, 1) avg_rating,
						AVG(SalesRank) avg_sales, MIN(DisplaySize) min_size, MAX(DisplaySize) max_size, GROUP_CONCAT(DISTINCT subtype) subtypes
					FROM az_attributes a, az_aux aux, az_main m
					WHERE a.ASIN = aux.ASIN
						AND a.ASIN = m.ASIN
						AND Manufacturer <> ''
						AND hide = 0
						AND type = 'HDTVs'
						AND subtype > 1
						AND date_updated > '$today' - INTERVAL {$admindata['amazon_update_window']} DAY
					GROUP BY man ORDER BY man";
					if ($debug) echo "$sql<br />";
					$result = mQuery($sql);
					while ($row = mysql_fetch_assoc($result)) {
						$min_size = ($row['min_size'] == 0) ? 'N/A' : $row['min_size'] .'"';
						$max_size = ($row['max_size'] == 0) ? 'N/A' : $row['max_size'] .'"';
						$avg_sales = ($row['avg_sales'] == 0) ? 'N/A' : '#'. number_format($row['avg_sales'], 0);
						$rating = ($row['avg_rating'] == 0) ? '(Unrated)' : '<img src="/images/stars5-'. $row['avg_rating'] .'.gif" alt="'. $row['avg_rating'] .'" align="absmiddle">';
						$subtype_array = array_flip(split(',', $row['subtypes']));

						echo '<tr>'.
							'<td><a href="manufacturer.php?man='. rawurlencode($row['man']) .'">'. $row['man'] .'</a></td>'.
							'<td align="center">'. $row['num'] .'</td>'.
							'<td>'. implode(', ', array_intersect_key($MODEL_TYPE, $subtype_array)) .'</td>'.
							'<td align="center">'. $min_size .' - '. $max_size .'</td>'.
							'<td align="center">'. $rating .'</td>'.
							'<td align="center">'. $row['num_reviews'] .'</td>'.
						'</tr>'."\n";
					}
				?></table>
		</div>
	</div>

	<?
		include(BASE_DIR .'/includes/body_footer-4.php');
	?>
</div></body>
</html>
