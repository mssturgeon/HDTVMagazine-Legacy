<?
	$mysql_username_override = 'mssturgeon';
	$mysql_password_override = 'mer70lot';
	require('../../global.php');

	mQuery("ALTER TABLE tbl_models add column pg_lowest_price decimal(8,2)");
	mQuery("ALTER TABLE tbl_models add column pg_product_image varchar(255) not null");
//	mQuery("ALTER TABLE tbl_models add column lumens smallint unsigned after contrast");
?>
