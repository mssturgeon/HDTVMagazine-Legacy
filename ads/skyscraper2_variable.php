<?
	if ($user->data['hide_banner_ads'] !== true) {
		$skyscraper2_output .= "<!-- FM Skyscraper (below the fold) Zone -->\n".
		'<div id="ad_skyscraper"><div><span class="corners-top"><span></span></span>'."\n".
			'<script type="text/javascript" src="http://static.fmpub.net/zone/833"></script>'."\n".
		'<span class="corners-bottom"><span></span></span></div></div>'."\n".
		'<!-- FM Skyscraper (below the fold) Zone -->'."\n";
	} else {
		$skyscraper2_output = '';
	}
?>