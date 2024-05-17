<?
	$debug = isset($_GET['debug']);
	$today = date('Y-m-d');

	# Set up array of tabs
	$tabs['Best Rated'] = "hdtvs-best-rated.php";
	$tabs['Best Selling'] = "hdtvs-best-selling.php";
#	$tabs['New Additions'] = "hdtvs-new-additions.php";
#	$tabs['by Type'] = "hdtvs-by-type.php";
	$tabs['by Manufacturer'] = "hdtvs-by-manufacturer.php";
	$tabs['by Size'] = "hdtvs-by-size.php";
	$tabs['by Price'] = "hdtvs-by-price.php";
#	$tabs['Other HD Hardware'] = "other-hd-hardware.php";
	$tabs['Search HDTVs'] = "hdtvs-search.php";
?>