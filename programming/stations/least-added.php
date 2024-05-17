<?
	header('Cache-Control: no-store'); // HTTP/1.1

	require('../global.php');
	if (!access(ACCESS_PREMIUM)) prompt_login(PHP_SELF);

	$action = (isset($_POST['action'])) ? $_POST['action'] : '';
	if ($action == 'add') {
		$station_num = $_POST['id'];
/*
		$qry = "INSERT IGNORE INTO join_user_station (user_id, tf_station_num, us_key, label, channel, priority)".
		" SELECT ". $_SESSION['user_id'] .", stat.tf_station_num, CONCAT('". $_SESSION['user_id'] ."', '-', stat.tf_station_num), stat.tf_station_name, CONCAT(tf_major_channel_number, '.', tf_minor_channel_number), 999".
		" FROM tms_dgstatrec stat LEFT JOIN tms_dgpsiprec psip ON stat.tf_station_num = psip.tf_station_num".
		" WHERE stat.tf_station_num = ". $station_num;
*/
		$qry = "INSERT IGNORE INTO join_user_station (user_id, tf_station_num, label, channel, priority)".
		" SELECT ". $user->data['user_id'] .", stat.tf_station_num, stat.tf_station_name, CONCAT(tf_major_channel_number, '.', tf_minor_channel_number), 999".
		" FROM tms_dgstatrec stat LEFT JOIN tms_dgpsiprec psip ON stat.tf_station_num = psip.tf_station_num".
		" WHERE stat.tf_station_num = ". $station_num;
		mQuery($qry);
		js_back('Station Added!');
		exit;
	} elseif ($action == 'remove') {
		$station_num = $_POST['id'];
		$qry = "DELETE FROM join_user_station WHERE user_id = ". $user->data['user_id'] ." AND tf_station_num = ". $station_num;
		mQuery($qry);
		js_back('Station Removed!');
		exit;
	}

	// Build array of current stations
	$result = mQuery("SELECT tf_station_num FROM join_user_station WHERE user_id = ". $user->data['user_id']);
	$x = 0;
	while ($row = mysql_fetch_assoc($result)) {
		$my_stations[$x++] = $row['tf_station_num'];
	}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>HDTV Magazine - Least Added Stations</title>
	<?require(BASE_DIR .'/includes/common_header.php');?>
	<meta name="description" content="HDTV Stations - Least Added">
	<meta name="keywords" content="hdtv,hdtv programming,hdtv guide,hdtv schedule,hdtv listing,hdtv guides,hdtv schedules,hdtv listings,hdtv programs,hdtv programming guide,hdtv programming schedule,hdtv program,hd guide,hd programming,hd schedule,abc hdtv programming,cbs hdtv programming,digital tv programming,digital tv schedule,digital tv schedules,dtv listings,dtv program,dtv programing,dtv programming,dtv schedule,hd programing,hd programming,hd programs,high definition guide,high definition programing,high definition programming,high definition programs,high definition schedule,nbc hdtv programming,guide,news,sports,hdtv stations">
	<meta name="rating" content="general">
</head>
<body>
	<?
		include(BASE_DIR .'/includes/tracker.php');
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
	?>

	<div align="center">
	 	Listed below are the 50 least added channels to user profiles (not including locals).
	</div>

	<form name="frmQM" action="<?=PHP_SELF?>" method="post">
		<input type="hidden" name="action" value="">
		<input type="hidden" name="id" value="">
		<table class="bare" align="center" style="margin-top:20px">
			<tr><td><table class="type1b" align="center">
				<tr>
					<td class="type1b_header">Name</td>
					<td class="type1b_header" align="center">Add/Remove</td>
					<td class="type1b_header" align="center">Count</td>
				</tr>
				<?
					$max = 0;
					$max_width = 175;

					$qry = "SELECT stat.tf_station_num, tf_station_name, count(*) count FROM join_user_station j, tms_dgstatrec stat WHERE ota = 0 AND j.tf_station_num = stat.tf_station_num AND j.user_id > 0 GROUP BY tf_station_name ORDER BY count ASC LIMIT 50";
					$result = mQuery($qry);

					// Get the last value, as that is the max
					mysql_data_seek($result, mysql_num_rows($result) - 1);
					$row = mysql_fetch_assoc($result);
					$max = $row['count'];

					mysql_data_seek($result, 0);
					while ($row = mysql_fetch_assoc($result)) {
						$bar_length = round($row['count'] / $max * $max_width);

						$qa_text = in_array($row['tf_station_num'], $my_stations) ?
							'<img src="/images/add-d.gif" alt="Already Added">&nbsp;<a href="javascript:quickMod('. $row['tf_station_num'] .', \'remove\')"><img src="/images/remove.gif" alt="Remove"></a>' :
							'<a href="javascript:quickMod('. $row['tf_station_num'] .', \'add\')"><img src="/images/add.gif" alt="Add"></a>&nbsp;<img src="/images/remove-d.gif" alt="Not Added">';

						echo '<tr>'.
		  				'	<td class="subTable1Cell"><a href="/programming/guide-station.php?id='. $row['tf_station_num'] .'">'. $row['tf_station_name'] .'</a></td>'.
		  				'	<td class="subTable1Cell" align="center">'. $qa_text .'</td>'.
						'	<td class="table1Cell">'.
						'		<table class="bare" cellspacing="1">'.
						'			<tr>'.
						'				<td class="primary_bold"><img src="/images/pixel.gif" height="1" width="'. $bar_length .'"></td>'.
						'				<td>'.  $row['count'] .'</td>'.
						'			</tr>'.
						'		</table>'.
						'	</td>'.
						'</tr>';
					}
				?>
			</table></td></tr>
		</table>
	</form>

	<?include(BASE_DIR .'/includes/body_footer.php');?>
</body>
</html>
