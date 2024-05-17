<?
	if ($user->data['hide_banner_ads'] !== true) {
		$mrectangle_output = <<<EOT
<!-- FM Medium Rectangle Zone -->
	<div class='ad_mrectangle'>advertisement<br />
		<script type='text/javascript' src='http://static.fmpub.net/zone/541'></script>
	</div>
<!-- FM Medium Rectangle Zone -->
EOT;
	}
?>