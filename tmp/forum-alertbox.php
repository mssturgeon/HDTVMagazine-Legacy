<?
	if ($user->data['subscriptions'] & SUB_FORUM_UPDATE) {} else {
		if ($user->data['is_registered']) {
			$alertbox_output = '<div class="important"><span class="corners-top"><span></span></span>'.
				'<a href="'. BASE_URL .'/profile-subscriptions.php"><img src="/images/i_inbox.gif" align="left" style="padding-right:10px" /></a>'.
				'<span class="label">Receive Daily Forum Updates via email.</span>'.
				'<a href="'. BASE_URL .'/profile-subscriptions.php">Modify your subscription profile</a>'.
				' to receive Daily Forum Updates each day in your inbox. This service will alert you to new topics and new posts within the past 24 hours.'.
			'<span class="corners-bottom"><span></span></span></div>';
		} else {
			$alertbox_output = '<div class="important"><span class="corners-top"><span></span></span>'.
				'<a href="'. BASE_URL .'/profile-create.php"><img src="/images/i_inbox.gif" align="left" style="padding-right:10px" /></a>'.
				'<span class="label">Receive Daily Forum Updates via email.</span>'.
				'<a href="'. BASE_URL .'/profile-create.php">Register Now</a>'.
				' to receive	Daily Forum Updates each day in your inbox. This service will alert you to new topics and new posts within the past 24 hours.'.
			'<span class="corners-bottom"><span></span></span></div>';
		}
	}
?>
