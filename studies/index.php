<?
	require('../global.php');
	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - HDTV-Related Studies</title>
	<? require(BASE_DIR .'/includes/page_header.php'); ?>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header-4.php');
	?>

	<h1>HDTV Magazine Studies</h1>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left"><?
			if ($user->data['subscriptions'] & SUB_DAILY) {} else {
				if ($user->data['is_registered']) {?>
					<div class="important"><span class="corners-top"><span></span></span>
						<a href="<?=($base_url . URL_PROFILE_SUBSCRIPTIONS)?>"><img src="<?=$base_url?>/images/i_inbox.gif" align="left" style="padding-right:10px" /></a>
						<span class="label">Receive new study notifications:</span>
							<a href="<?=URL_PROFILE_SUBSCRIPTIONS?>">Modify your subscription profile</a> to receive
							Study notifications and results as soon as they are made available.
						<span class="corners-bottom"><span></span></span></div>
				<?} else {?>
					<div class="important"><span class="corners-top"><span></span></span>
						<a href="<?=($base_url . URL_PROFILE_CREATE)?>"><img src="<?=$base_url?>/images/i_inbox.gif" align="left" style="padding-right:10px" /></a>
						<span class="label">Receive new study notifications:</span>
						<a href="<?=($base_url . URL_PROFILE_CREATE)?>">Register Now</a> to receive
							Study notifications and results as soon as they are made available.
					<span class="corners-bottom"><span></span></span></div>
				<?}
			}?>

			<!--div class="important"><span class="corners-top"><span></span></span>
				<span class="label">The Fall 2007 HDTV Study is now closed.</span> You may <a href="<?=URL_PROFILE_SUBSCRIPTIONS?>">Modify your subscription profile</a> if you
				wish to be notified when the study results are available, or to be notified of future studies.
			<span class="corners-bottom"><span></span></span></div-->

			<fieldset>
				<legend>Most Recent Study</legend>
				<!--legend>Study In Progress</legend-->
				<div style="float:left; margin:0 5px 5px 0">
					<script>
						digg_window = 'new';
					</script>
					<script src="http://digg.com/api/diggthis.js"></script>
				</div>

				<!-- Uncomment following line when a new survey is coming -->
				<!--h2>Spring 2009 HDTV Study (Currently Running)</h2-->

				<!-- Uncomment following line when a survey is active -->
				<!--h2><a target="_blank" href="http://www.surveymonkey.com/s.aspx?sm=0wEev8EY9_2b5R9yqIk50crA_3d_3d">Spring 2009 HDTV Study</a></h2-->

				<!-- Uncomment following line when survey has closed and results are available -->
				<h2><a target="_blank" href="/studies/view-results.php?sid=3612805">Spring 2009 HDTV Study</a></h2>

				<b>Start Date:</b> 11 June, 2009<br />
				<b>End Date:</b> 10 July, 2009<br />
				<b>Results Available: </b>August, 2009<br clear="all" />
				<br />
				<!--b>Win one of the following prizes:</b>
				<ul>
				<li>One of two (2) Toshiba HD DVD players courtest of Microsoft HDi: <a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://clk.atdmt.com/MRT/go/hdtvdaub1080000468mrt/direct/01/<?=time()?>">This is HD DVD</a></li>
				<li><a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.oppodigital.com/dv980h/default.asp?partner=826">OPPO DV-980H</a> - 1080p Up-Converting Universal DVD Player (HDMI/Component)</li>
				<li><a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.oppodigital.com/?partner=826">OPPO DV-981HD</a> - 1080p Up-Converting Universal DVD Player (HDMI)</li>
				<li><a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.oppodigital.com/hm31/default.asp?partner=826">OPPO HM-31</a> - Advanced 3x1 HDMI Switch</li>
				</ul-->
				<!--p>
					Prize winners from this year's study:<br />
					<ul>
						<li>Jon N. (Springboro, OH) - Toshiba HD-A30 HD DVD Player</li>
						<li>Jim N. (Lacombe, LA) - OPPO HM-31 HDMI Switch</li>
						<li>Robert M. (Pasadena, CA) - OPPO DV-981HD Up-Converting DVD Player</li>
						<li>Shaun P. (Streamwood, IL) - OPPO DV-980H Up-Converting DVD Player</li>
					</ul>
				</p><p>
					Special thanks go out to our sponsors:
					<ul>
						<li><a href="http://www.hdtvmagazine.com/cgi-bin/ntlinktrack.cgi?http://clk.atdmt.com/MRT/go/hdtvdaub1080000468mrt/direct/01/1194303589">Microsoft HDi</a></li>
						<li><a href="http://www.hdtvmagazine.com/cgi-bin/ntlinktrack.cgi?http://www.oppodigital.com/?partner=826">OPPO Digital</a></li>
					</ul>
				</p-->
			</fieldset>

			<p style="margin:0">
				Our "HDTV Studies" are conducted of our membership and visitors on a periodic basis. These studies are meant to assess the current state
				of HDTV and the interest our readers have in HDTV-related technologies such as Blu-ray, OLED, Internet Video, etc. The studies generally run monthly,
				and for several weeks at a time. Listed below are our active and completed surveys.
			</p><p>
				If you would like to be notified when new studies are available, or when results have been posted, please sign up for our <b>Study Notification</b> list. Registered
				members can verify their subscription preferences on their <a href="/profile-subscriptions.php">Subscription Profile</a>.
			</p><p>
				If you would like to sponsor an HDTV Study, or have ideas for future studies, please <a href="<?=URL_HELP_FEEDBACK?>">let us know</a>.
			</p>

			<fieldset>
				<legend>Completed Studies<legend>
				<table class="type1b"><tr>
					<td class="type1b_header">Name</td>
					<td class="type1b_header">Start Date</td>
					<td class="type1b_header">End Date</td>
					<td class="type1b_header">Respondents</td>
					<td class="type1b_header">Results</td>
				</tr>
				<?
					$sql = "
					SELECT s.SID, title, active, from_date, to_date, COUNT(DISTINCT RespondentID) respondents
					FROM survey_s s, survey_p p, survey_q q, survey_r r
					WHERE
						s.SID = p.SID
						AND p.PageID = q.PageID
						AND q.QID = r.QID
					GROUP BY SID, title, active, from_date, to_date
					ORDER BY from_date DESC";
					$result = mQuery($sql);
					while ($row = mysql_fetch_assoc($result)) {
						$end_date = ($row[active] == 1) ? '<i>In Progress</i>' : date('j M, Y', strtotime($row[to_date]));
						echo '<tr><td class="grid"><a href="/studies/view-results.php?sid='. $row[SID] .'">'. $row[title] .'</a></td>'.
		#				echo '<tr><td class="grid">'. $row[title] .'</td>'.
						'<td class="grid">'. date('j M, Y', strtotime($row[from_date])) .'</td>'.
						'<td class="grid">'. $end_date .'</td>'.
						'<td class="grid" style="text-align:right">'. $row[respondents] .'</td>'.
						'<td class="grid"><a href="/studies/view-results.php?sid='. $row[SID] .'">View Results</a></td></tr>';
		#				'<td class="grid"><i>Coming Soon</i></td></tr>';
					}
				?>
				</table><br />
			</fieldset>
		</td><td id="right" align="center">
			<div id="right" style="float:right; margin:0 0 5px 5px; text-align:center;" align="center">
				<? include(BASE_DIR .'/ads/mrectangle.php');?>
				<br />
				<div align="center">
					<? include(BASE_DIR .'/ads/skyscraper.php');?>
				</div>
			</div>
			<div id="<?=$container?>">
				<$MTEntryBody$>
			</div>
		</td>
	</tr></table>
	<? include(BASE_DIR .'/includes/body_footer-4.php'); ?>

</div></body>
</html>
