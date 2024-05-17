<?
	if ($user->data['hide_banner_ads'] !== true) {
		# Write FM tracking code
		$leaderboard_output = "<!-- FM Tracking Pixel -->\n".
			"<script type='text/javascript' src='http://static.fmpub.net/site/hdtv'></script>\n".
		"<!-- FM Tracking Pixel -->\n".
		"<!-- FM Leaderboard Zone -->\n".
		'<div id="ad_leaderboard" align="center"><div><span class="corners-top"><span></span></span>'."\n".
			"<script type='text/javascript'>\n".
				"var federated_media_section = '';\n".
			"</script>\n".
			'<script type="text/javascript" src="http://static.fmpub.net/zone/529"></script>'."\n".
		'<span class="corners-bottom"><span></span></span></div></div>'."\n".
		'<!-- FM Leaderboard Zone -->'."\n";

	} else {
		$leaderboard_output = '';
	}
?>