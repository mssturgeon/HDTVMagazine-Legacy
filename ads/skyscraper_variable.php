<?
	if ($user->data['hide_banner_ads'] !== true) {
		$skyscraper_output .= "<!-- FM Skyscraper Zone -->\n".
		'<div id="ad_skyscraper"><div><span class="corners-top"><span></span></span>'."\n".
			'<script type="text/javascript" src="http://static.fmpub.net/zone/530"></script>'."\n".
		'<span class="corners-bottom"><span></span></span></div></div>'."\n".
		'<!-- FM Skyscraper Zone -->'."\n";
	} else {
		$skyscraper_output = '';
	}
?>