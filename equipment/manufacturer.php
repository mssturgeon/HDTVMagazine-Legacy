<?
	require('../global.php');
	$debug = isset($_GET['debug']);

	$man = isset($_GET['man']) ? rawurldecode($_GET['man']) : '';
	$sort = isset($_GET['sort']) ? $_GET['sort'] : '';
	switch ($sort) {
/*
		case 'size':
			$sort_display = '<b>Screen Size</b> [<a href="?man='. $man .'&sort=model">Model Name</a>] [<a href="?man='. $man .'&sort=price">Price</a>]';
			$order_by = 'size';
			break;
*/
		case 'rating':
			$sort_display = '[<a href="?man='. $man .'&sort=sales">Sales Rank</a>] [<a href="?man='. $man .'&sort=model">Model Name</a>] [<a href="?man='. $man .'&sort=price">Price</a>] [<b>Rating</b>]';
			$order_by = 'AverageRating DESC';
			$sorted_by = 'Average Rating';
			break;
		case 'model':
			$sort_display = '[<a href="?man='. $man .'&sort=sales">Sales Rank</a>] <b>Model Name</b> [<a href="?man='. $man .'&sort=price">Price</a>] [<a href="?man='. $man .'&sort=rating">Rating</a>]';
			$order_by = 'Model';
			$sorted_by = 'Model Name';
			break;
		case 'price':
			$sort_display = '[<a href="?man='. $man .'&sort=sales">Sales Rank</a>] [<a href="?man='. $man .'&sort=model">Model Name</a>] <b>Price</b> [<a href="?man='. $man .'&sort=rating">Rating</a>]';
			$where = 'AND LowestNewPrice > 0';
			$order_by = 'LowestNewPrice';
			$sorted_by = 'Lowest New Price';
			break;
		default:
			$sort_display = '<b>Sales Rank</b> [<a href="?man='. $man .'&sort=model">Model Name</a>] [<a href="?man='. $man .'&sort=price">Price</a>] [<a href="?man='. $man .'&sort=rating">Rating</a>]';
			$where = 'AND SalesRank > 0';
			$order_by = 'SalesRank';
			$sorted_by = 'Amazon.com Sales Rank';
			break;
	}

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - <?=$man?> Products (by <?=$sorted_by?>)</title>
	<meta name="description" content="List of <?=$man?> Products sorted by <?=$sorted_by?> including images, Amazon.com sales rank, rating and prices." />
	<meta name="keywords" content="hdtv,<?=$man?>,products,hdtvs,images,amazon,rank,rating,prices,reviews" />
	<? require(BASE_DIR .'/includes/page_header.php');?>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header-4.php');
	?>

	<h1><?=$man?> Products</h1>

	<div id="right" style="display:table; float:right; margin:0 0 5px 20px; text-align:center;" align="center">
		<? include(BASE_DIR .'/ads/mrectangle.php');?>
		<br />
		<div align="center">
			<? include(BASE_DIR .'/ads/skyscraper.php');?>
		</div>
	</div>

	<span style="text-align:right;width:100%">Sorted by: <?=$sort_display?></span><br /><br />

	<div style="display:table">
		<table class="simple" style="width:100%">
			<tr class="header">
				<td>Model</td>
				<td>Category</td>
				<td nowrap="nowrap">Sales Rank</td>
				<td align="center">Rating</td>
				<td align="center">Reviews</td>
				<td align="right" nowrap="nowrap">List Price</td>
				<td align="right" nowrap="nowrap">New Price</td>
			</tr>
			<?
				$sql = "
				SELECT *
				FROM az_main m, az_attributes a, az_aux aux
				WHERE (Manufacturer = '". addslashes($man) ."' OR ManufacturerAlias = '". addslashes($man) ."')
					AND m.ASIN = a.ASIN
					AND m.ASIN = aux.ASIN
					$where
				ORDER BY $order_by";
				if ($debug) echo "$sql<br />";
				$result = mQuery($sql);
				while ($row = mysql_fetch_assoc($result)) {
					$rating = ($row['AverageRating'] > 0) ? '<img src="/images/stars5-'. $row['AverageRating'] .'.gif" alt="'. $row['AverageRating'] .'" align="absmiddle">' : '(Unrated)';
					$url = '/equipment/model.php?a='. $row['ASIN'] .'&man='. rawurlencode($row['ManufacturerAlias']) .'&model='. rawurlencode($row['Model']);
					$url_reviews = '/equipment/model-reviews.php?a='. $row['ASIN'] .'&man='. rawurlencode($row['ManufacturerAlias']) .'&model='. rawurlencode($row['Model']);
					$reviews = ($row['TotalReviews'] > 0) ? '<a href="'. $url_reviews.'">'. $row['TotalReviews'] .'</a>' : '';
					$price_list = ($row['ListPriceFormatted'] != '') ? $row['ListPriceFormatted'] : '';
					$price_new = ($row['LowestNewPriceFormatted'] != '') ? $row['LowestNewPriceFormatted'] : '';
					$price_new = ($price_new == 'Too low to display') ? '???' : $price_new;

					echo '<tr>'.
					'	<td><a href="'. $url .'">'. $row['Title'] .'</a></td>'.
					'	<td align="center">'. $row['type'] .'</td>'.
					'	<td align="center">#'. $row['SalesRank'] .'</td>'.
					'	<td align="center">'. $rating .'</td>'.
					'	<td align="center" nowrap="nowrap">'. $reviews .'</td>'.
					'	<td align="right">'. $price_list .'</td>'.
					'	<td align="right"><a href="'. $url .'">'. $price_new .'</a></td>'.
					"</tr>\n";
				}
			?>
		</table>
	</div>

	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
</div></body>
</html>
