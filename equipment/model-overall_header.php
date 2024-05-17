<?
	$debug = isset($_GET['debug']);

	$asin = $_GET['a'];
	$man = rawurldecode($_GET['man']);
	$model = rawurldecode($_GET['model']);

	if ($asin == '') { # Try to look up ASIN based on man and model
		$res_amazon = mQuery("SELECT ASIN FROM az_attributes WHERE Model <> '' AND REPLACE(Model, '-', '') = REPLACE('$model', '-', '')");
		$row_amazon = mysql_fetch_assoc($res_amazon);
		$asin = $row_amazon['ASIN'];
		if ($asin == '') {
			js_replace('/equipment/index.php');
			exit();
		}
	}
	$query_string = "a=$asin&amp;man=". rawurlencode($man) .'&amp;model='. rawurlencode($model);

	$sql = "
	SELECT *
	FROM az_main m, az_attributes a, az_aux aux
	WHERE m.ASIN = '$asin'
		AND m.ASIN = a.ASIN
		AND m.ASIN = aux.ASIN";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);

	$title = $row['Title'];
	$man = $row['Manufacturer'];
	$model = $row['Model'];
	$size = $row['DisplaySize'];
	$url = $row['DetailPageURL'];
	$url_man = "/equipment/manufacturer.php?man=$man";
	$url_category = '/equipment/category.php?category='. rawurlencode($row['type']);
	$price_list = ($row['ListPriceFormatted'] == '') ? '' : 'List Price: <b style="color:#000">'. $row['ListPriceFormatted'] .'</b><br />';
	$price_new = ($row['LowestNewPriceFormatted'] == '') ? '' : 'Lowest New Price: <b><a target="_blank" href="'. $url .'">'. $row['LowestNewPriceFormatted'] .'</a></b><br />';
	$price_refurb = ($row['LowestRefurbishedPriceFormatted'] == '') ? '' : 'Lowest Refurbished Price: <b>'. $row['LowestRefurbishedPriceFormatted'] .'</b><br />';
	$price_used = ($row['LowestUsedPriceFormatted'] == '') ? '' : 'Lowest Used Price: <b>'. $row['LowestUsedPriceFormatted'] .'</b><br />';
	$as_of = $row['date_updated'] > '0000-00-00' ? $row['date_updated'] : 'N/A';

/*
	$img_amazon = '<a href="http://www.amazon.com/gp/product/'. $asin .'?ie=UTF8&tag=hdtvmagazine-20&linkCode=as2&camp=1789&creative=9325&creativeASIN='. $asin .'"><img src="'. $row[MediumImageURL] .'" alt="'. $title .'"></a>'.
		'<img src="http://www.assoc-amazon.com/e/ir?t=hdtvmagazine-20&l=as2&o=1&a='. $asin .'" width="1" height="1" alt="" />';
*/
	$img_amazon = "<a target='_blank' href='$row[DetailPageURL]'><img src='$row[MediumImageURL]' alt='$title'></a>".
		'<img src="http://www.assoc-amazon.com/e/ir?t=hdtvmagazine-20&l=as2&o=1&a='. $asin .'" width="1" height="1" alt="" />';
	$img_rating = ($row['AverageRating'] == 0) ? '(Unrated)' : '<img src="/images/stars5-'. $row['AverageRating'] .'.gif" alt="'. $row['AverageRating'] .'" align="absmiddle">';

	# Set up array of tabs
	$tabs['Pricing Comparison'] = "model.php?$query_string";
	$tabs['Specifications (Specs)'] = "model-specs.php?$query_string";
	$tabs['Reviews'] = "model-reviews.php?$query_string";
	$tabs['eBay Listings'] = "model-ebay.php?$query_string";
	$tabs['News'] = "model-news.php?$query_string";
	$tabs['Similar Equipment'] = "model-similar.php?$query_string";

	$breadcrumbs = '&raquo; <a href="/equipment/index.php">Equipment</a>&nbsp;'.
	'&raquo; <a href="'. $url_category .'">'. $row['type'] .'</a>&nbsp;'.
	'&raquo; <a href="'. $url_man .'">'. $man .'</a>&nbsp;'.
	'&raquo; <b>'. $row['Model'] .'</b><br /><br />';
?>
