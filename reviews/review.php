<?
	require('../global.php');
	$id = isset($_GET[id]) ? $_GET[id] : '';
	
	$sql = "SELECT * FROM reviews WHERE review_id = '$id'";
	$result = mQuery($sql);
	$review = mysql_fetch_assoc($result);
	
	# Temporarily hard-code to amazon structure ... but need to boil down to make it more generic
	$sql = "SELECT * FROM $review[review_table] WHERE $review[review_key] = '$review[review_product]'";
	echo $sql;
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	
	$title = "$review[type] Review - $row[manufacturer]  $row[model]";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title>HDTV Magazine - <?=$title?></title>
	<?require(BASE_DIR .'/includes/common_header.php');?>
</head>
<body>
	<?
		include(BASE_DIR .'/includes/tracker.php');
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
	?>
	
	<h1><?=$title?></h1>
	<p>
		
	</p>
				
	<?include(BASE_DIR .'/includes/body_footer.php');?>
</body>
</html>
