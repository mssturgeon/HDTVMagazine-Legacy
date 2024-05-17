<?
	# Set up array of tabs
	$tabs['My Account'] = 'profile.php';
	$tabs['My Subscriptions'] = 'profile-subscriptions.php';
	$tabs['My Guide'] = 'profile-guide.php';
	$tabs['My Stations'] = 'profile-stations.php';
	if (access(ACCESS_ADMIN_ANY)) {
		$tabs['My Equipment'] = 'profile-equipment.php';
		$tabs['My Details'] = 'profile-info.php';
	}
?>
