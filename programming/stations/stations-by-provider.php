<?
	header('Cache-Control: no-store'); // HTTP/1.1

	require('../global.php');
#	if (!access(ACCESS_PREMIUM)) prompt_login(PHP_SELF);
#	if ($_SESSION[user_id] == '') prompt_login(PHP_SELF);

	$pid = isset($_GET[provider_id]) ? $_GET[provider_id] : '';
	$action = isset($_POST[action]) ? $_POST[action] : '';
	if ($action == 'add') {
		$station_num = $_POST[id];
/*
		$qry = "INSERT IGNORE INTO join_user_station (user_id, tf_station_num, us_key, label, channel, priority)".
		" SELECT ". $_SESSION[user_id] .", stat.tf_station_num, CONCAT('". $_SESSION[user_id] ."', '-', stat.tf_station_num), j.label, j.channel, 999".
		" FROM tms_dgstatrec stat, join_provider_station j".
		" WHERE stat.tf_station_num = j.tf_station_num".
		"	AND stat.tf_station_num = ". $station_num;
*/
		$qry = "INSERT IGNORE INTO join_user_station (user_id, tf_station_num, label, channel, priority)".
		" SELECT ". $user->data[user_id] .", stat.tf_station_num, j.label, j.channel, 999".
		" FROM tms_dgstatrec stat, join_provider_station j".
		" WHERE stat.tf_station_num = j.tf_station_num".
		"	AND stat.tf_station_num = ". $station_num;
		mQuery($qry);
		js_back('Station Added!');
		exit;
	} elseif ($action == 'remove') {
		$station_num = $_POST[id];
		$qry = "DELETE FROM join_user_station WHERE user_id = ". $user->data[user_id] ." AND tf_station_num = ". $station_num;
		mQuery($qry);
		js_back('Station Removed!');
		exit;
	}

	// Build array of current stations
	$result = mQuery("SELECT tf_station_num FROM join_user_station WHERE user_id = ". $user->data[user_id]);
	$x = 0;
	while ($row = mysql_fetch_assoc($result)) {
		$my_stations[$x++] = $row[tf_station_num];
	}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>HDTV Magazine - Stations by Provider</title>
	<?require(BASE_DIR .'/includes/common_header.php');?>
	<meta name="description" content="HDTV Stations by Cable/Satellite Provider ">
	<meta name="keywords" content="hdtv,hdtv programming,hdtv guide,hdtv schedule,hdtv listing,hdtv guides,hdtv schedules,hdtv listings,hdtv programs,hdtv programming guide,hdtv programming schedule,hdtv program,hd guide,hd programming,hd schedule,abc hdtv programming,cbs hdtv programming,digital tv programming,digital tv schedule,digital tv schedules,dtv listings,dtv program,dtv programing,dtv programming,dtv schedule,hd programing,hd programming,hd programs,high definition guide,high definition programing,high definition programming,high definition programs,high definition schedule,nbc hdtv programming,guide,news,sports,hdtv stations">
	<meta name="rating" content="general">
</head>
<body>
	<?
		include(BASE_DIR .'/includes/tracker.php');
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');

		if ($pid != '') {
			$qry = "SELECT p.name p_name, p.id p_id, j.id s_id, j.channel, j.label, stat.tf_station_name, stat.tf_station_num".
			" FROM admin_providers p, join_provider_station j, tms_dgstatrec stat".
	  		" WHERE j.tf_station_num = stat.tf_station_num".
	  		"	AND p.id = j.provider_id".
  			"	AND j.provider_id = ". $pid .
  			" ORDER BY j.channel*1, label";

  			$result = mQuery($qry);
			$row = mysql_fetch_array($result);
		}
	?>

	<h1>Stations by Provider</h1>
			<div align="center">

				<?if ($pid == '') {?>
				<table class="box" align="center"><tr><td>
					Listed below are some stations grouped by Provider.  Please note that, at this point, this page is done by hand, and as such all providers are not available at this time.
				</td></tr></table>
				<?}?>

				<form name="frm" method="get" action="<?=PHP_SELF?>">
					<table class="bare" align="center" style="margin-top:20px">
						<tr>
							<td class="inputLabel">Choose a Provider:</td>
							<td><?=getSelectBox("SELECT DISTINCT a.name, a.id FROM admin_providers a, join_provider_station j WHERE a.id = j.provider_id ORDER BY name", "provider_id", $pid, 'onchange="submit()"')?></td>
					</td></tr></table>
				</form><br>

				<?if ($pid != '') {?>
					<form name="frmQM" action="<?=PHP_SELF?>" method="post">
						<input type="hidden" name="action" value="">
						<input type="hidden" name="id" value="">

						<table class="type1b" cellpadding="0" cellspacing="0" style="width:50%">
							<?
								mysql_data_seek($result, 0);
								while ($row = mysql_fetch_array($result)) {
									$label = ($row[label] == '') ? $row[tf_station_name] : $row[label];
									$qa_text = in_array($row[tf_station_num], $my_stations) ?
										'<img src="/images/add-d.gif" alt="Already Added">&nbsp;<a href="javascript:quickMod('. $row[tf_station_num] .', \'remove\')"><img src="/images/remove.gif" alt="Remove"></a>' :
										'<a href="javascript:quickMod('. $row[tf_station_num] .', \'add\')"><img src="/images/add.gif" alt="Add"></a>&nbsp;<img src="/images/remove-d.gif" alt="Not Added">';
									echo '<tr>'.
									'	<td class="type1b" width="30" align="right" style="padding-right:5px">'. $row[channel] .'</td>'.
									'	<td class="type1b"><a href="/programming/guide-station.php?id='. $row[tf_station_num] .'">'. $row[label] .'</a></td>';
									if ($user->data[user_id] > 0) echo '	<td class="type1b" align="right">'. $qa_text .'</td>';
									echo '</tr>';
								}
							?>
						</table>

					</form>
				<?}?>

			</div>

		<?include(BASE_DIR .'/includes/body_footer.php');?>

</body>
</html>
