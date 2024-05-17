<?
	require('../global.php');
	$network = 'WideOpenWest';
	$network_lc = 'wideopenwest';
	$provider_id = 21;
	
	require(BASE_DIR .'/programming/provider_header.php');
	
	# NOTES
	# - Tabbed Interface like equipment
	# - Add News tab
	# - Featured Movies (like home page)
	# - Number of affiliates
	# - Hours of HD this week 
?>
	
	<!--h2>Useful Links &amp; Statistics</h2>
	<table class="bare" cellpadding="2">
		<tr><td style="font-weight:bold"><?=$network?> Website:</td><td><a href="/cgi-bin/ntlinktrack.cgi?http://www.directv.com/">www.directv.com</a></td></tr>
		<tr><td style="font-weight:bold">Official <?=$network?> HD Page:</td><td><a href="/cgi-bin/ntlinktrack.cgi?http://www.directv.com/hdtv">www.directv.com/hdtv</a></td></tr>
	</table><br /><br /-->
	
	<h2>HD Primetime on <?=$network?></h2>
	<br />
	<?
		# Set grid parameters
		$timeslot_size = 30;
	
		# Load parameters for the grid based on which user is logged in, if any
		if (access(ACCESS_PREMIUM)) { // Get current time adjusted to user timezone settings
			$user_id = $_SESSION[user_id];
			$user_time_offset = $_SESSION[time_zone_offset];
			$show_sd = $_SESSION[opt_showsd];
			$user_icons = $_SESSION[icons];
	  	} else { // Set to Eastern, adjusted for DST
			$user_id = 0;
			$user_time_offset = -18000 + (1*HOURS*date("I"));
			$show_sd = true;
			$user_icons = ICON_SHOW_NEW & ICON_SHOW_DD;
		}
	
		echo getProgramLegend() .'<br />';
		$start_display_time = strtotime('8:00pm GMT') - $user_time_offset;
		echo get_grid_provider($provider_id, $start_display_time, $timeslot_size, $user_time_offset, 6, $show_sd, $user_icons);
	?>
	<br />
	<br />

<?
	require(BASE_DIR .'/programming/provider_footer.php');
?>
