<?
#	include('leaderboard_variable.php');
#	echo $leaderboard_output;

	if ($user->data['hide_banner_ads'] !== true) {?>
		<!-- FM Tracking Pixel -->
			<script type='text/javascript' src='http://static.fmpub.net/site/hdtv'></script>
		<!-- FM Tracking Pixel -->
		<!-- FM Leaderboard Zone -->
		<div id="ad_leaderboard" align="center"><div><span class="corners-top"><span></span></span>
			<script type='text/javascript'>
				var federated_media_section = '';
			</script>
			<script type="text/javascript" src="http://static.fmpub.net/zone/529"></script>
		<span class="corners-bottom"><span></span></span></div></div>
		<!-- FM Leaderboard Zone -->
	<? }?>