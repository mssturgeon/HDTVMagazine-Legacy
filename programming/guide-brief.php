<?
	// If the user_id is given, then use that, otherwise load global and use currently logged in user.
	define('BASE_DIR', '/var/www/html');

	$emailed = false;
	if (isset($_GET[user_id])) {
		$emailed = true;
		require(BASE_DIR .'/includes/constants.php');
		require(BASE_DIR .'/includes/lib_mysql.php');
		require(BASE_DIR .'/includes/lib_common.php');
		require(BASE_DIR .'/includes/lib_guide.php');

		$user_id = $_GET[user_id];
		$tzo = $_GET[tzo];
		$tzt = $_GET[tzt];

		$result = mQuery("SELECT * FROM admin_settings");
		$admindata = mysql_fetch_array($result);
	} else {
		require(BASE_DIR .'/global.php');
		require(BASE_DIR .'/includes/lib_guide.php');
		$user_id = $user->data['user_id'];
		if (!access(ACCESS_PREMIUM)) prompt_login(PHP_SELF);

		$tzo = $user->data['time_zone_offset'];
		$tzt = $user->data['time_zone_text'];
	}
	$base_url = 'http://'. SERVER_NAME;

	// Get subscription data
	$result = mQuery("SELECT subscriptions FROM user WHERE id = $user_id");
	$row = mysql_fetch_assoc($result);
	$subscriptions = $row[subscriptions];

	if (isset($_GET[d])) {
		$now = strtotime($_GET[d] .' 12:00am GMT');
		$today = $_GET[d];
	} else {
		$now = time();
		$today = gmdate('m/d/Y', $now);
	}

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - Daily HDTV Program Brief - <?=$today?></title>
	<style type="text/css">
		<?require(BASE_DIR .'/stylesheets/email_css.php');?>
	</style>
</head>
<body>
	<div align="center" style="margin-top:20px">

     	<? if ($subscriptions & SUB_GUIDE_BRIEF) {} else {
     		echo '<div align="center"><div class="alertbox">'.
     		'	<b>Receive this page daily via email.</b> <a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive'.
			'	The HDTV Program Brief each day in your inbox, customized to your stations.'.
     		'</div></div>';
     	}?>

		<table border="0" cellpadding="2" cellspacing="2" class="layout">
			<tr >
				<td colspan="1" rowspan="1" style="background-color:#ffffff;">

  					<?
						include(BASE_DIR .'/includes/header_email.php');
						echo "\n";
					?>

					<table border="0" cellpadding="2" cellspacing="1" style="width:100%">
						<tr style="background-color: #666699">
							<td colspan="1" rowspan="1" width="10" ><img border="0" height="10" src="<?=$base_url?>/images/pixel.gif" alt="" width="10" /></td>
							<td colspan="1" rowspan="1" width="220" ><img border="0" height="10" src="<?=$base_url?>/images/pixel.gif" alt="" width="220" /></td>
							<td colspan="1" rowspan="1" style="background-color:#333366;text-align:right;padding-right:5px;font-size:8pt;font-weight:bold;color:#ffffff;" width="100%"><?=gmdate('l, F d, Y', $now)?> (<?=$tzt?>)</td>
						</tr><tr>
							<td colspan="1" rowspan="7" style="background-color: #333366;text-align: right;padding-right:5px;" ><img border="0" height="50" src="<?=$base_url?>/images/pixel.gif" alt="" width="10" /></td>
							<td colspan="2" rowspan="1" style="background-color:#9999cc;font-size:16pt;padding-left:5px;font-family:Georgia,'Times New Roman',Times,serif;" >Daily HDTV Program Brief</td>
						</tr><tr>
							<td colspan="2" rowspan="1"><?
								// Get Default station_num List
								$qry = "SELECT t.tf_station_num, u.label, lcase(t.tf_station_affil) affil, t.ota, tf_station_call_sign, image_file".
								" FROM {$admindata[tbl_stat]} t, join_user_station u".
								" WHERE t.tf_station_num = u.tf_station_num".
								"	AND u.user_id = ". $user_id .
								" ORDER BY u.priority, u.label";
								$result = mQuery($qry);

								// If there is an empty data set, let the user know
								if (mysql_num_rows($result) == 0) {
									echo 'Thank you for subscribing to the Daily Program Brief.  This email provides a daily listing, by network, of all HD programming for each of your preferred stations.'.
									'  Since you do not yet have any preferred stations configured in your profile, this email is empty.  Please <a href="'. FULL_URL_PROFILE_STATIONS .'">Edit your Profile</a> and select the stations you would like to include in this listing.';
									echo '</td></tr></table></body></html>'."\n";
									exit;
								}

								// Write Network Index
								$x = 1;
								echo '<a name="index"></a><table class="networkIndex" cellspacing="10" cellpadding="0" summary=""><tr>'."\n";
								while ($row = mysql_fetch_assoc($result)) {
									// Get Image
									$image_path = '/images/logos/'. str_replace('[size].', '15.', $row[image_file]);
									$img[$row[tf_station_num]] = $image_path;
									$image_tag = '<img src="'. $base_url . $image_path .'" alt="'. $row[tf_station_call_sign] .'" align="top"> '.
										'<a href="'. $base_url . PHP_SELF .'#'. $row[tf_station_num] .'">'. $row[tf_station_call_sign] .'</a>';

									echo '<td class="networkIndex" style="text-align:left" nowrap>'. $image_tag ."</td>\n";

									if ($x++ % 5 == 0) {
										echo "</tr><tr>\n";
									}
								}
								echo "</tr></table>\n";
							?></td>
						</tr><tr>
							<td colspan="2" rowspan="1" style="background-color:#9999CC;"><img border="0" height="10" src="<?=$base_url?>/images/pixel.gif" width="10" /></td>
						</tr><tr>
							<td colspan="2" align="center"><?
								# This channel assignment covers both this ad and the one at the end of the email ... no need to specify it twice.
#								$google_channel = $GOOGLE_CHANNEL[Email_Banner];
								include(BASE_DIR .'/ads/banner_email.php');?>
							</td>
						</tr><tr>
							<td colspan="2" rowspan="1"><?
								// Reset and Loop through stations
								mysql_data_seek($result, 0);
								while ($row = mysql_fetch_assoc($result)) {
									// Create Network table
									echo '<a name="'. $row[tf_station_num] .'"></a>'.
									'<table class="bare" cellspacing="0" cellpadding="3" style="margin-bottom:20px;background-color:#'. BG_COLOR .'"><tr style="background-color:#9999CC;">'."\n".
									'	<td><img src="'. $base_url . $img[$row[tf_station_num]] .'" alt="'. $row[tf_station_call_sign] .'"></td>'."\n".
									'	<td style="width:100%;vertical-align:middle;"><a href="'. FULL_URL_GUIDE_STATION .'?id='. $row[tf_station_num] .'">'. $row[label] .'</a></td>'."\n".
									'	<td style="vertical-align:middle;" nowrap><a href="'. $base_url . PHP_SELF .'#index">Back to Index</a></td>'."\n".
									'</tr><tr>'."\n".
									'	<td colspan="3">';

									$qry = "
									SELECT DISTINCT p.*, tf_air_time, tf_station_num, tf_duration
									FROM $admindata[tbl_prog] p, $admindata[tbl_sked] s
									WHERE s.tf_database_key = p.tf_database_key
										AND FROM_UNIXTIME(s.tf_air_time + $tzo, '%m/%d/%Y') = '$today'
										AND s.tf_station_num = $row[tf_station_num]
										AND options & ". OPT_SKED_HDTV ."
									ORDER BY tf_air_time ASC";
									$program_result = mQuery ($qry);

									if (mysql_num_rows($program_result) > 0) {
										// Loop through programming
										while ($program = mysql_fetch_assoc ($program_result)) {
											$episode = ($program[tf_epi_title] != '') ? ' - '. $program[tf_epi_title] : '';
			   							$duration = getProgramDuration($program);

   										// Ratings
   										$rating = $program[tf_mpaa_rating];
			   							if ($rating == '') $rating = $program[tf_tv_rating];
			   							if ($rating != '') $rating = '('. $rating .')';

											$program_date = gmdate("m/d/Y", $program[tf_air_time] + $tzo);
											$orig_air_date = $program[tf_org_air_date];
											$date = substr($orig_air_date, 4, 2) .'/'. substr($orig_air_date, 6, 2) .'/'. substr($orig_air_date, 0, 4);
											$new = ($date == $program_date) ? '<span style="font-weight:bold;color:red">New!</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' : '';

											echo '<table cellpadding="2" cellspacing="0" class="program_entry"><tr>'."\n".
											'	<td><img src="'. $base_url .'/images/pixel.gif" alt="" width="120" height="1"><br>'. gmdate("g:ia", $program[tf_air_time] + $tzo) .' - '. gmdate("g:ia", $program[tf_air_time] + $tzo + ($program[tf_duration]*MINUTES)) .'</td>'."\n".
											'	<td width="100%">'.
													$new .
											'		<a href="'. FULL_URL_GUIDE_PROGRAM .'?k='. $program[tf_database_key] .'&amp;a='. $program[tf_air_time] .'&amp;n='. $program[tf_station_num] .'">'. $program[tf_title] . $episode .'</a>'.
											"		$rating $duration".
											"</tr></table>\n";
										}
									} else {
										echo '<center><b>No HD Programming Tonight</b></center>';
									}
									// Finish Network table
									echo "</td></tr></table>\n";
								}
							?></td>
						</tr><tr>
							<td colspan="2" rowspan="1" style="background-color:#9999CC;"><img border="0" height="10" src="<?=$base_url?>/images/pixel.gif" width="10" /></td>
						</tr><tr>
							<td colspan="2" align="center"><?include(BASE_DIR .'/ads/banner_email.php');?></td>
						</tr><tr>
							<td style="background-color: #333366;text-align: right;padding-right: 5px;" ><img border="0" height="50" src="<?=$base_url?>/images/pixel.gif" alt="" width="10" /></td>
							<td colspan="2" rowspan="1" style="background-color: #9999CC;text-align: left;padding-left: 5px;" >
								<div style="text-align: left;padding: 0px 10px 0px 10px;" >
									<div style="line-height:20px">
										email: <a href="mailto:feedback@hdtvmagazine.com">feedback@hdtvmagazine.com</a>
									</div>
									<div style="line-height:20px">
										web: <a href="http://www.hdtvmagazine.com/">http://www.hdtvmagazine.com/</a>
									</div>
								</div>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</div>
	<br />

</body>
</html>
