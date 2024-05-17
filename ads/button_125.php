<?
	$button_channel = ($google_channel == '') ? '5588699202' : $google_channel;
	$button_output .= '<script type="text/javascript"><!--'."\n".
	'google_ad_client = "pub-2099480674594177";'."\n".
#	'google_alternate_ad_url = "http://www.hdtvmagazine.com/ads/button_125_alt.php";'."\n".
	'google_ad_width = 125;'."\n".
	'google_ad_height = 125;'."\n".
	'google_ad_format = "125x125_as";'."\n".
	'google_ad_channel ="'. $button_channel .'";'."\n".
	'google_ad_type = "text_image";'."\n".
	'google_color_border = "F5F7FA";'."\n".
	'google_color_bg = "F5F7FA";'."\n".
	'google_color_link = "'. LINK_COLOR .'";'."\n".
	'google_color_url = "'. SECONDARY_COLOR .'";'."\n".
	'google_color_text = "'. TEXT_COLOR .'";'."\n".
	'//--></script>'."\n".
	'<script type="text/javascript"'."\n".
	'  src="http://pagead2.googlesyndication.com/pagead/show_ads.js">'."\n".
	'</script>'."\n";
	
	echo $button_output;
?>
