<?
	if ($user->data['hide_banner_ads'] !== true) {
		$links_channel = ($google_links_channel == '') ? '9752039117' : $google_links_channel;

		# Write Google AdSense code
		$links_output = '<div id="ad_links"><div align="center"><script type="text/javascript"><!--'."\n".
		'google_ad_client = "pub-2099480674594177";'."\n".
		'google_ad_width = 728;'."\n".
		'google_ad_height = 15;'."\n".
		'google_ad_format = "728x15_0ads_al_s";'."\n".
		'google_ad_channel = "'. $links_channel .'";'."\n".
		'google_color_border = "FFFFFF";'."\n".
		'google_color_bg = "FFFFFF";'."\n".
		'google_color_link = "'. LINK_COLOR .'";'."\n".
		'google_color_url = "'. SECONDARY_COLOR .'";'."\n".
		'google_color_text = "'. TEXT_COLOR .'";'."\n".
		'//--></script>'."\n".
		'<script type="text/javascript"'."\n".
		'  src="http://pagead2.googlesyndication.com/pagead/show_ads.js">'."\n".
		"</script></div></div>\n";

	} else {
		$links_output = '';
	}
?>
