<?
	require('global.php');

	$debug = isset($_GET['debug']);

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - Unsubscribe</title>
	<meta name="description" content="Cancellation/Unsubscribe page for HDTV Magazine">
	<meta name="robots" content="noindex">
	<? require(BASE_DIR .'/includes/page_header.php'); ?>
</head>
<body id="body_container">
	<?
		require(BASE_DIR .'/includes/body_header-4.php');
	?>

	<?
		$params = base64_decode(urldecode($_GET['u']));
		if ($debug) {echo "$params<br />";}
		$u = split(':', $params);

		if (strpos($params, ':') == 0) {?>
			<h1>Unsubscribe</h1>
			<p>There was an error with your unsubscribe request.</p>
			<p>Please <a href="/help/feedback.php">email us directly</a> and let us know of the issue, and from which list you wish to unsubscribe, and we will take care of it personally.</p>
			<p>You may also manage your subscription preferences via your <a href="/profile-subscriptions.php">Subscription Profile</a>.</p>
			<p>Thanks,</p>
			<p>- Shane &amp; Dale<br />
			HDTV Magazine</p>
		<? } elseif ($u[0] == 14446) {?>
			<h1>Unsubscribe Failed</h1>
			<p>This is a system account for the Tips List, and cannot be unsubscribed.</p>
			<p>Thanks,</p>
			<p>- Shane &amp; Dale<br />
			HDTV Magazine</p>
			<br />
			<br />
		<? } else {
			if ($u[0] != 7729) { # Don't process unsubscribes for mssturgeon
				# Unsubscribe user
				$sql = "UPDATE user SET subscriptions = subscriptions - $u[1] WHERE id = $u[0] AND (subscriptions & $u[1])";
				if ($debug) {echo "$sql<br />";} else {mQuery($sql);}
			}

			# Update user comments
			$sql = "INSERT INTO user_comments (user_id, timestamp, comment) VALUES ($u[0], ". time() .", 'Unsubscribed from ". $SUB[$u[1]] ."')";
			if ($debug) {echo "$sql<br />";} else {mQuery($sql);}

			# Get user info
			$sql = "SELECT first_name, last_name, subscriptions, email_address FROM user WHERE id = $u[0]";
			if ($debug) {echo "$sql<br />";}
			$result = mQuery($sql);
			$row = mysql_fetch_assoc($result);
			$masked_email = substr($row['email_address'], 0, 1) . str_repeat('*', strlen(strleft($row['email_address'], '@')) - 1) .'@'. strright($row['email_address'], '@');

			# Check for other subscriptions
			$subscribed = array();
			$unsubscribed = array();
			foreach ($SUB as $sub_value => $sub_label) {
				if (($sub_value != 1) && ($sub_value != $u[1])) {
					if ($sub_value & $row['subscriptions']) {
						$subscribed[] = $sub_label;
					} else {
						$unsubscribed[] = $sub_label;
					}
				}
			}
			?>
				<h1>Unsubscribe Confirmation</h1>

				<? if (count($subscribed) > 0) {?>
					<div class="important" style="display:table"><span class="corners-top"><span></span></span>
						<img src="/images/i_subscribe.gif" align="left" style="float:left; padding-right:10px" />
						<span class="label">You have other active subscriptions!!!</span><br />
						You have one or more other active subscriptions to HDTV Magazine notifications. Please check your
						<a href="/profile-subscriptions.php">Subscription Preferences</a> and verify that you are only subscribed to those emails you wish to receive.
					<span class="corners-bottom"><span></span></span></div>
					<br />
				<? }?>

				<div class="alert_green"><div>
					<table class="bare"><tr><td style="vertical-align:top">
						<img src="/images/i_check.gif" alt="" align="absmiddle" style="padding:0 5px 5px 0" /><b>Thank you! You have successfully unsubscribed.</b><br />
						<br />
						We have received your unsubscribe request and have removed <?=$masked_email?> from the <b><?=$SUB[$u[1]]?></b> mailing list, effective immediately.<br />
						<br />
						Please take a moment to tell us why you chose to unsubscribe. We read all feedback and address each comment individually.
					</td><td style="vertical-align:top; width:40%">
						<form name="frm" action="/help/feedback.php" method="post">
							<input type="hidden" name="action" value="save">
							<input type="hidden" name="category" value="unsubscribe">
							<input type="hidden" name="subject" value="Unsubscribed from <?=$SUB[$u[1]]?>">
							<input type="hidden" name="user_id" value="<?=$u[0]?>">
							<input type="hidden" name="user_email" value="<?=$row['email_address']?>">
							<input type="hidden" name="name" value="<?=($row['first_name'] .' '. $row['last_name'])?>">
							<textarea name="comments" style="height:60px" onkeyup="adjustAreaSize(this, 60, 300)"></textarea><br />
							<input type="submit" name="btnSubmit" value="Send Feedback" class="inputButton">
						</form>
					</td></tr></table>
				</div></div>
				<br />

				<h2>Subscribe in other ways...</h2>
				<table class="bare"style="margin-top:10px"><tr><td style="border-right:1px solid black; padding:0 10px; width:33%; vertical-align:top">
					<a href="http://www.facebook.com/HDTVMagazine" target="_blank" style="float:left; margin-right:10px"><img src="/images/i_facebook_64.png" alt="Facebook" align="left" /></a>
					HDTV Magazine has a Facebook page where we publish all the stories you would receive via email.
					<a href="http://www.facebook.com/HDTVMagazine" target="_blank">Become a fan</a> and you'll receive our published story
					excerpts integrated with you Facebook news feed.<br clear="all"/><hr style="border-width:0; border-top:1px solid white; margin:3px 0; padding:0"/>
				</td><td style="border-right:1px solid black; padding:0 10px; width:33%; vertical-align:top">
					<a href="http://twitter.com/HDTVMagazine" target="_blank" style="float:left; margin-right:10px"><img src="/images/i_twitter_64.png" alt="Twitter" align="left" /></a>
					No Facebook account? Perhaps you'd prefer to receive our notifications via Twitter?
					<a href="http://twitter.com/HDTVMagazine" target="_blank">Follow us</a> on Twitter @HDTVMagazine and receive
					the same updates for newly published articles, etc.<br clear="all" /><hr style="border-width:0; border-top:1px solid white; margin:3px 0; padding:0"/>
				</td><td style="vertical-align:top; padding-left:10px;">
					<a href="/rss-feeds.php" style="margin-right:10px; float:left;"><img src="/images/i_rss_64.png" alt="RSS" align="left" /></a>
					No Facebook or Twitter? Go "Old School" and simply subscribe to our RSS Feeds. We have
					<a href="/rss-feeds.php">many to choose from</a>.<br clear="all" />
				</td></tr></table><br />
				<br /><br />

				<!--div style="float:left; width:45%"><div class="item"><span class="corners-top"><span></span></span>
					<h2>Your current subscriptions</h2>
					<ul class="brownsquare"><?
						if (count($subscribed) == 0) {
							echo "<li>None</li>";
						} else {
							foreach ($subscribed as $sub_label) {
								echo "<li>$sub_label</li>";
							}
						}
					?></ul>
				<span class="corners-bottom"><span></span></span></div></div-->

				<!--div style="float:right; width:45%"><div class="item"><span class="corners-top"><span></span></span>
					<h2>Other subscriptions available</h2>
					<ul class="brownsquare"><?
						if (count($unsubscribed) == 0) {
							echo "<li>None</li>";
						} else {
							foreach ($unsubscribed as $sub_label) {
								echo "<li>$sub_label</li>";
							}
						}
					?></ul>
				<span class="corners-bottom"><span></span></span></div></div>
				<br clear="all" /-->
			<?
		}

		include(BASE_DIR .'/includes/body_footer-4.php');
	?>
</body>
</html>
