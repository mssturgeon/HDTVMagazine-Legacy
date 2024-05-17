<?
	// If the user_id is given, then use that, otherwise load global and use currently logged in user.
	$emailed = false;
	if (isset($_GET[user_id])) {
		$emailed = true;
		define('BASE_DIR', '/var/www/html');
		require(BASE_DIR .'/includes/constants.php');
		require(BASE_DIR .'/includes/lib_mysql.php');
		require(BASE_DIR .'/includes/lib_common.php');
		require(BASE_DIR .'/includes/lib_guide.php');

		$user_id = $_GET[user_id];
		$tzo = $_GET[tzo];

		$result = mQuery("SELECT * FROM admin_settings");
		$admindata = mysql_fetch_array($result);
	} else {
		require('../global.php');
		require(BASE_DIR .'/includes/lib_guide.php');
		$user_id = $user->data[user_id];
		if ($user_id == '') prompt_login(PHP_SELF);

		$tzo = $user->data['time_zone_offset'];
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
	<title>HDTV Magazine - Daily HDTV Program Listing - <?=$today?></title>
	<style>
		<?require(BASE_DIR .'/stylesheets/email_css.php');?>
	</style>
</head>
<body>
	<!-- Tracking Pixel -->
	<img src="<?=$base_url?>/images/pixel.gif?lc=http://www.hdtvmagazine.com/ntlinktrack.cgi%3Flisting_<?=rawurlencode($today)?>&pv=1&sc=302" alt="">

	<div align="center" style="margin-top:20px">

     	<? if ($subscriptions & SUB_GUIDE_LISTING) {} else {
     		echo '<div align="center"><div class="alertbox">'.
     		'	<b>Receive this page daily via email.</b> <a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive'.
			'	The HDTV Program Listing each day in your inbox, customized to your stations.'.
     		'</div></div>';
     	}?>

		<table border="0" cellpadding="2" cellspacing="2" class="layout">
			<tr >
				<td colspan="1" rowspan="1" style="background-color:#ffffff;">

  					<? include(BASE_DIR .'/includes/header_email.php');?>

					<table border="0" cellpadding="2" cellspacing="1" style="width:100%">
						<tr style="background-color: #666699">
							<td colspan="1" rowspan="1" width="10" ><img border="0" height="10" src="<?=$base_url?>/images/pixel.gif" width="10" /></td>
							<td colspan="1" rowspan="1" width="220" ><img border="0" height="10" src="<?=$base_url?>/images/pixel.gif" width="220" /></td>
							<td colspan="1" rowspan="1" style="background-color:#333366;text-align:right;padding-right:5px;font-size:8pt;font-weight:bold;color:#ffffff;" width="100%"><?=gmdate('l, F d, Y', $now)?></td>
						</tr><tr>
							<td colspan="1" rowspan="7" style="background-color: #333366;text-align: right;padding-right:5px;" ><img border="0" height="50" src="<?=$base_url?>/images/pixel.gif" width="10" /></td>
							<td colspan="2" rowspan="1" style="background-color:#9999cc;font-size:16pt;padding-left:5px;font-family:Georgia,'Times New Roman',Times,serif;" >Daily HDTV Program Listing</td>
						</tr><tr>
							<td colspan="2" rowspan="1"><?
								// Get station_num List
								$qry = "SELECT t.tf_station_num, u.label, lcase(t.tf_station_affil) affil, t.ota, tf_station_call_sign, image_file, u.channel".
								" FROM {$admindata[tbl_stat]} t, join_user_station u".
								" WHERE t.tf_station_num = u.tf_station_num".
								"	AND u.user_id = ". $user_id .
								" ORDER BY u.priority, u.label";
								$result = mQuery($qry);

								// If there is an empty data set, let the user know
								if (mysql_num_rows($result) == 0) {
									echo 'Thank you for subscribing to the Daily Program Listing.  This email provides a daily listing, by network, of all HD programming for each of your preferred stations.'.
									'  Since you do not yet have any preferred stations configured in your profile, this email is empty.  Please <a href="'. FULL_URL_PROFILE_STATIONS .'">Edit your Profile</a> and select the stations you would like to include in this listing.';
									echo '</td></tr></table></body></html>';
									exit;
								}

								// Write Network Index
								$x = 1;
								echo '<a name="index"></a><table class="networkIndex" cellspacing="10" cellpadding="0" summary=""><tr>';
								while ($row = mysql_fetch_assoc($result)) {
									// Get Image
									if ($row[image_file] == 'no-image_[size].gif') {
										$image_tag = $row[tf_station_call_sign];
									} else {
										$image_path = '/images/logos/'. str_replace('[size].', '75x40.', $row[image_file]);
										$img[$row[tf_station_num]] = $image_path;
										$image_tag = '<img alt="'. $row[tf_station_call_sign] .'" src="'. $base_url . $image_path .'"><br>'. $row[tf_station_call_sign];
									}

									echo '<td class="networkIndex">'.
									'	<a href="'. $base_url . PHP_SELF .'#'. $row[tf_station_num] .'">'. $image_tag .'</a>'.
									"</td>\n";

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
								include(BASE_DIR .'/ads/banner_email_listing.php');?>
							</td>
						</tr><tr>
							<td colspan="2" rowspan="1"><?
								// Reset and Loop through stations
								mysql_data_seek($result, 0);
								while ($row = mysql_fetch_assoc($result)) {
									// Create Network table
									echo '<a name="'. $row[tf_station_num] .'"></a><table class="dailyNetwork" cellspacing="0" align="center">'.
									'<tr>'.
									'	<td class="dailyNetworkHeaderImage">'.
									'		<img alt="'. $row[tf_station_call_sign] .'" src="'. $base_url . $img[$row[tf_station_num]] .'">'.
									'	</td>'.
									'	<td class="dailyNetworkHeader">'.
									$row[channel] .'  '. $row[label] .
									'	</td>'.
									'	<td class="dailyNetworkHeaderBack">'.
									'		<a href="'. $base_url . PHP_SELF .'#index"><img width="75" height="1" src="'. $base_url . '/images/pixel.gif"><br>Back to Index</a>'.
									'	</td>'.
									'</tr>'.
									'<tr><td class="dailyNetwork" colspan="3">';

									$qry = "
									SELECT DISTINCT p.tf_database_key, p.tf_title, tf_epi_title, tf_tv_rating, tf_mpaa_rating, tf_org_air_date, tf_air_time, tf_duration,
										tf_desc_160 tf_desc, tf_dolby, tf_run_time
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
											$episode = ($program[tf_epi_title] != '') ? '"'. $program[tf_epi_title] .'"<br>' : '';

   										// Ratings
   										$rating = $program[tf_mpaa_rating];
			   							if ($rating == '') $rating = $program[tf_tv_rating];
											if ($rating != '') $rating = ' <b>'. $rating .'</b>';

											$program_date = gmdate("m/d/Y", $program[tf_air_time] + $tzo);
											$orig_air_date = $program[tf_org_air_date];
											$date = substr($orig_air_date, 4, 2) .'/'. substr($orig_air_date, 6, 2) .'/'. substr($orig_air_date, 0, 4);
											$new = ($date == $program_date) ? '<span style="font-weight:bold;color:red">New!</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' : '';

											$details = array();
											$details[] = getProgramDuration($program);
											$details[] = getProgramDate($program);
									  		?>
												<table class="dailyProgram" cellspacing="0" cellpadding="0">
													<tr>
														<td class="dailyProgramTitle">
															<img src="<?=$base_url?>/images/pixel.gif" width="75" height="1" /><br />
															<?=gmdate("g:ia", $program[tf_air_time] + $tzo)?>
														</td><td class="dailyProgramTitle" style="width:100%">
															<?
																echo $new .'<a href="'. FULL_URL_GUIDE_PROGRAM .'?k='. $program[tf_database_key] .'&amp;n='. $row[tf_station_num] .'&amp;a='. $program[tf_air_time] .'">'. $program[tf_title] .'</a>';
															?>
														</td><td class="dailyProgramTitle" style="width:100px;text-align:right">
															<?=$rating?>
														</td>
													</tr><tr>
														<td class="dailyProgramDesc" colspan="3">
															<?=($episode . $program[tf_desc])?>
														</td>
													</tr><tr>
														<td class="dailyProgramDesc" colspan="3">
															<?=implode(' ', $details);?>
														</td>
													</tr>
												</table>
											<?
										}
									} else {
										?>
										<table class="dailyProgram" cellspacing="0">
											<tr><td class="dailyProgramDesc" style="text-align:center;padding:10px 0px"><b>No HD Programming Tonight</b></td></tr>
										</table>
										<?
									}
									// Finish Network table
									echo "</td></tr></table>\n";
								}
							?></td>
						</tr><tr>
							<td colspan="2" rowspan="1" style="background-color:#9999CC;"><img border="0" height="10" src="<?=$base_url?>/images/pixel.gif" width="10" /></td>
						</tr><tr>
							<td colspan="2" align="center"><?include(BASE_DIR .'/ads/banner_email_listing.php');?></td>
						</tr><tr>
							<td style="background-color: #333366;text-align: right;padding-right: 5px;" ><img border="0" height="50" src="<?=$base_url?>/images/pixel.gif" width="10" /></td>
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
