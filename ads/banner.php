<?
	$banner_channel = ($google_channel == '') ? '' : $google_channel;
	$banner_output = '<script type="text/javascript"><!--'."\n".
	'google_ad_client = "pub-2099480674594177";'."\n".
	'google_alternate_ad_url = "http://www.hdtvmagazine.com/ads/banner_alt.php";'."\n".
	'google_ad_width = 468;'."\n".
	'google_ad_height = 60;'."\n".
	'google_ad_format = "468x60_as";'."\n".
  	'google_ad_channel ="'. $banner_channel .'";'."\n".
	'google_ad_type = "text_image";'."\n".
	'google_color_border = "'. BORDER_COLOR .'";'."\n".
	'google_color_bg = "'. BG_COLOR .'";'."\n".
	'google_color_link = "'. LINK_COLOR .'";'."\n".
	'google_color_url = "'. SECONDARY_COLOR .'";'."\n".
	'google_color_text = "'. TEXT_COLOR .'";'."\n".
	'//--></script>'."\n".
	'<script type="text/javascript"'."\n".
	'	src="http://pagead2.googlesyndication.com/pagead/show_ads.js">'."\n".
	'</script>';
	
	echo $banner_output;
?>
