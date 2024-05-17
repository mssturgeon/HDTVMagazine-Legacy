<?
	# Pricegrabber
	$ad[] = '<script type="text/javascript" src="http://ah.pricegrabber.com/export_feeds.php?pid=aididid&document_type=html&topcat_id=2&category=topcat:2&banner_size=728x90&banner_topc=003f87&banner_botc=003f87&banner_bgcolor=F5F7FA&banner_border_color=003f87&font_color=000000&link_color=995905&show_images=1&javascript=1" ></script>';

	# CJ Rotation
#	$ad[] = '';

	#Amazon
	$ad[] = '<script type="text/javascript">amazon_ad_tag = "hdtvmagazine-20"; amazon_ad_width = "728"; amazon_ad_height = "90"; amazon_ad_link_target = "new"; amazon_ad_discount = "remove"; amazon_color_border = "003F87"; amazon_color_background = "EFEFEF"; amazon_color_link = "003F87"; amazon_color_price = "995905"; amazon_color_logo = "003F87";></script><script type="text/javascript" src="http://www.assoc-amazon.com/s/ads.js"></script>';

	$x = rand(0, count($ad) - 1);
	echo $ad[$x];
?>
