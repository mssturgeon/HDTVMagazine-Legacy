<?
	require('global.php');
#	require(BASE_DIR .'/includes/lib_profile.php');
	require(BASE_DIR .'/profile-overall_header.php');

	if (!$user->data['is_registered']) prompt_login(PHP_SELF);

	$action = isset($_POST['action']) ? $_POST['action'] : '';
	if ($action == 'save') {
		$subscriptions = is_array($_POST['subscriptions']) ? array_sum($_POST['subscriptions']) : 0;

		// Build Query
		$sql = "UPDATE user SET".
		"	modified = '". gmdate('Y-m-d G:i:s') ."'".
		"	,subscriptions = ". $subscriptions .
		"	,email_preference = ". $_POST['email_preference'] .
		" WHERE id = ". $user->data['id'];
		mQuery($sql);
	}

	// Get Profile Info
/*
	$sql = "
	SELECT id, email_address, email_preference, email_invalid, email_spam, subscriptions, flg_autorenew,
		DATE_FORMAT(exp_pg, '%M %D, %Y') exp_pg
	FROM user
	WHERE id = {$user->data['user_id']}";
	$result = mQuery ($sql);
	$user = mysql_fetch_assoc($result);
*/

	$checked = array();
	foreach ($SUB as $sub_value => $sub_label) {
		$checked[$sub_value] = ($user->data['subscriptions'] & $sub_value) ? 'CHECKED' : '';
	}

	if (!access(ACCESS_PREMIUM)) {
		$disabled = 'DISABLED';
		$disabled_style = 'color:#AAAAAA';
	}

	// Set subscription preference
	$ck_email_preference_text = ($user->data['email_preference'] == EMAIL_PREF_TEXT) ? 'CHECKED' : '';
	$ck_email_preference_html = ($user->data['email_preference'] == EMAIL_PREF_HTML) ? 'CHECKED' : '';

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - My Subscriptions</title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
	<script type="text/javascript">
		function init() {
			if (document.frmProfile.email_spam.value == '1') {
				alert('Subscriptions Unavailable: Per feedback from your ISP, you have marked one or more messages from us as "Spam". We have therefore disabled your subscriptions.\n\nTo have your subscriptions re-activated, please email feedback@hdtvmagazine.com.');
			} else if (document.frmProfile.email_invalid.value >= '3') {
				alert('Possible Invalid Email Address: Our system has received three (3) or more "bounced" messages in attempts to send email to this address. No further attempts will be made until it is updated.');
			}
		}
	</script>
</head>
<body onload="init()">
	<?
		include(BASE_DIR .'/includes/body_header-4.php');

		if ($action == 'save') {
			echo '<div class="alert_green" id="saved" align="center"><div>Settings saved successfully!</div></div>';
		}
		if ($user->data['email_invalid'] >= 3) {
			echo '<div class="alert_red" align="center"><div>There appears to be a problem with your email address. Please be sure it is correct.</div></div>';
		}
		if ($user->data['email_spam'] >= 1) {
			echo '<div class="alert_red" align="center"><div><b>Subscriptions Unavailable:</b> Per feedback from your ISP, you have marked one or more messages'.
				' from us as "Spam". We have therefore disabled your subscriptions. To have your subscriptions re-activated, please <a href="'. URL_HELP_FEEDBACK .
				'">notify us</a>.</div></div>';
		}
	?>

	<div align="center"><div id="tab-container">
		<?=getTabHeader($tabs);?>
		<div class="tab-content" style="padding:10px">
			<form action="<?=PHP_SELF?>" method="post" name="frmProfile">
				<input type="hidden" name="action" value="save">
				<input type="hidden" name="user_id" value="<?=$user->data['id']?>">
				<input type="hidden" name="user_email" value="<?=$user->data['user_email']?>">
				<input type="hidden" name="email_invalid" value="<?=$user->data['email_invalid']?>">
				<input type="hidden" name="email_spam" value="<?=$user->data['email_spam']?>">

				<fieldset><legend>Membership Status</legend>
					<? if (access(ACCESS_PREMIUM)) {
						$exparray = split('-', ''. $user->data['exp_pg']);
						$expdate = date('F jS, ', strtotime("$exparray[1]/$exparray[2]")) . $exparray[0];
						echo '<span style="float:right"><input type="button" name="btnCancel" value="Cancel Membership" class="inputButton" onclick="location.href = \'subscription-cancel.php\';" /></span>'.
						'<div class="primary_bold_12">Premium Member</div>';
						if ($user->data['flg_autorenew'] == 1) {
							echo 'Auto-Renews on: <b>'. $expdate .'</b>';
						} else {
							echo 'Expires: <b>'. $expdate .'</b>';
						}
					} else {
						echo '<div class="primary_bold_12">Basic Member</div><br /> (<a href="/subscribe/index.php">Upgrade to Premium Membership</a>)';
					}?>
				</fieldset>

				<fieldset><legend>Email Subscriptions</legend>
					<span class="inputLabel">Email Preference:</span>
					<input type="radio" name="email_preference" value="0" <?=$ck_email_preference_text?>>Text
					<input type="radio" name="email_preference" value="1" <?=$ck_email_preference_html?>>HTML

					<!--p><label for="daily_program_brief">
						<input type="checkbox" name="subscriptions[]" id="daily_program_brief" value="<?=SUB_GUIDE_BRIEF?>" <?=$disabled?> <?=$checked[SUB_GUIDE_BRIEF]?>>Daily Program Brief
						- <span style="font-weight:normal">The Daily Program Brief subscription will deliver a terse listing of your preferred HDTV programming on a
					daily basis. <b>Note:</b> This is an HTML-only publication for the time being. (Regular: 1/day) [<a href="<?=URL_GUIDE_BRIEF?>">Read Today's Issue</a>]</span>
					</label></p>

					<p><label for="daily_program_grid">
						<input type="checkbox" name="subscriptions[]" id="daily_program_grid" value="<?=SUB_GUIDE_GRID?>" <?=$disabled?> <?=$checked[SUB_GUIDE_GRID]?>>Daily Program Grid
						- <span style="font-weight:normal">The Daily Program Grid subscription will deliver a grid-based guide of your preferred HDTV programming on a daily basis.
						<b>Note:</b> This is an HTML-only publication. (Regular: 1/day) [<a href="<?=URL_GUIDE_GRID?>">Read Today's Issue</a>]</span>
					</label></p>

					<p><label for="sub_4">
						<input type="checkbox" name="subscriptions[]" id="sub_4" value="4" CHECKED>Daily Program Listing
						- <span style="font-weight:normal">The Daily Program Listing subscription will deliver your preferred HDTV programming on a daily basis via email. (Regular: 1/day) [ <a href="/programming/guide-listing.php">Today's Issue</a> ]</span>
					</label></p-->

					<p><label for="sub_262144">
						<input type="checkbox" name="subscriptions[]" id="sub_262144" value="<?=SUB_DAILY?>" <?=$checked[SUB_DAILY]?>>The HDTV Magazine Daily
						- <span style="font-weight:normal">The HDTV Magazine Daily subscription will deliver recent news, articles,
						reviews, and more on a daily basis. <b>Note:</b> This is an HTML-only publication for the time being. (Regular: 1/day)
						[<a href="/daily.php">Read Today's Issue</a>]</span>
					</label></p>

					<p><label for="sub_2097152">
						<input type="checkbox" name="subscriptions[]" id="sub_2097152" value="<?=SUB_WEEKLY?>" <?=$checked[SUB_WEEKLY]?>>The HDTV Magazine Weekly
						- <span style="font-weight:normal">The HDTV Magazine Weekly subscription will deliver recent news, articles,
						reviews, and more on a weekly basis. <b>Note:</b> This is an HTML-only publication for the time being. (Regular: 1/week)
						[<a href="/weekly.php">Read This Week's Issue</a>]</span>
					</label></p>

					<p><label for="sub_524288">
						<input type="checkbox" name="subscriptions[]" id="sub_524288" value="<?=SUB_PODCAST?>" <?=$checked[SUB_PODCAST]?>>HDTV Podcast
						- <span style="font-weight:normal">HDTV Magazine is now syndicating The HDTV Podcast, produced by The HT Guys:
							Ara Derderian &amp; Braden Russell. Be notified as soon as new episodes are posted. <b>Note:</b> This is an HTML-only publication
							for the time being. (Regular: 2/week)
						</span>
					</label></p>

					<p><label for="sub_32">
						<input type="checkbox" name="subscriptions[]" id="sub_32" value="32" <?=$checked[32]?>>Website Updates
						- <span style="font-weight:normal">The Website News/Updates subscription will alert you whenever significant features and/or content have been added to our website. (Occasional: 2-10/month)</span>
					</label></p>

					<p><label for="sub_256">
						<input type="checkbox" name="subscriptions[]" id="sub_256" value="256" <?=$checked[256]?>>New Articles
						- <span style="font-weight:normal">Be among the first to be notified of new HDTV Magazine Articles. (Regular: 1-2/week)</span>
					</label></p>

					<p><label for="sub_1048576">
						<input type="checkbox" name="subscriptions[]" id="sub_1048576" value="1048576" <?=$checked[1048576]?>>New Columns
						- <span style="font-weight:normal">Be among the first to be notified of new HDTV Magazine Columns. (Regular: 1-2/day)</span>
					</label></p>

					<p><label for="sub_65536">
						<input type="checkbox" name="subscriptions[]" id="sub_65536" value="65536" <?=$checked[65536]?>>New Reviews
						- <span style="font-weight:normal">Be among the first to be notified of new HDTV Magazine Reviews. (Regular: 1-2/week)</span>
					</label></p>

					<p><label for="sub_128">
						<input type="checkbox" name="subscriptions[]" id="sub_128" value="128" <?=$checked[128]?>>News Bulletins
						- <span style="font-weight:normal">Bulletins and selected press releases about particularly important products and programming. (Regular: 1-2/week)</span>
					</label></p>

					<p><label for="sub_32768">
						<input type="checkbox" name="subscriptions[]" id="sub_32768" value="32768" <?=$checked[32768]?>>Daily Forum Updates
						- <span style="font-weight:normal">The Daily Forum Update subscription will send you a daily email summarizing new topics and new replies within our <a href="/forum/index.php">HDTV Forum</a>. (Regular: 1/day)</span>
					</label></p>

					<p><label for="sub_131072">
						<input type="checkbox" name="subscriptions[]" id="sub_131072" value="131072" <?=$checked[131072]?>>Study Notifications
						- <span style="font-weight:normal">The Study Notification subscription will alert you when new <a href="/studies/index.php">HDTV-Related Studies</a> are launched, or when their results are available. (Sporadic: 1-2/month)</span>
					</label></p>

					<p><label for="sub_2">
						<input type="checkbox" name="subscriptions[]" id="sub_2" value="2" <?=$checked[2]?>>New Stations
						- <span style="font-weight:normal">The New Stations subscription will alert you whenever new stations are added to either the National listing, or to the Market selected in your profile. (Occasional: 5-10/month)</span>
					</label></p>

					<p><label for="sub_8">
						<input type="checkbox" name="subscriptions[]" id="sub_8" value="8" <?=$checked[8]?>>Broadcast Announcements
						- <span style="font-weight:normal">The Broadcast Messages subscription will notify you whenever a broadcast message is sent by the website administrator.	This will generally only be done in emergency situations, or to notify you of very significant additions or enhancements to the website. (Sporadic: 1-3/month)</span>
					</label></p>

					<p><label for="sub_16">
						<input type="checkbox" name="subscriptions[]" id="sub_16" value="16" <?=$checked[16]?>>Product Updates &amp; Special Offers
						- <span style="font-weight:normal">Be the first to be notified of new products and special offers are available. (Sporadic: 1-3/month)</span>
					</label></p>

					<p><label for="sub_64">
						<input type="checkbox" name="subscriptions[]" id="sub_64" value="64" <?=$checked[64]?>>Events
						- <span style="font-weight:normal">Event Notifications will notify you of newly announced and upcoming HDTV-related events. (Sporadic: 1-2/month)</span>
					</label></p>

					<?
#						foreach ($SUB_DESC as $sub_value => $sub_desc) {
#							echo '					<p><label for="sub_'. $sub_value .'">'."\n".
#							'						<input type="checkbox" name="subscriptions[]" id="sub_'. $sub_value .'" value="'. $sub_value .'" '. $checked[$sub_value] .'>'. $SUB[$sub_value] ."\n".
#							'						- <span style="font-weight:normal">'. $sub_desc .'</span>'."\n".
#							"					</label></p>\n";
#						}
					?>
				</fieldset>
				<input type="submit" name="btnSubmit" value="&nbsp;Save Changes&nbsp;" class="inputButton">
			</form>
		</div>
	</div></div>
	<br />

	<h2>Subscribe in other ways...</h2>
	<table class="bare"style="margin-top:10px"><tr><td style="border-right:1px solid black; padding:0 10px; width:33%; vertical-align:top">
		<a href="http://www.facebook.com/pages/HDTV-Magazine/45415877375" target="_blank" style="float:left; margin-right:10px"><img src="/images/i_facebook_64.png" alt="Facebook" align="left" /></a>
		HDTV Magazine has a Facebook page where we publish all the stories you would receive via email.
		<a href="http://www.facebook.com/pages/HDTV-Magazine/45415877375" target="_blank">Become a fan</a> and you'll receive our published story
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

	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
</body>
</html>
