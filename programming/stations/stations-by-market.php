<?
	header('Cache-Control: no-store'); // HTTP/1.1

	require('../global.php');
#	if (!access(ACCESS_PREMIUM)) prompt_login(PHP_SELF);
#	if ($_SESSION['user_id'] == '') prompt_login(PHP_SELF);

	$mid = isset($_GET['tf_dma_name']) ? $_GET['tf_dma_name'] : '';
	$action = isset($_POST['action']) ? $_POST['action'] : '';
	if ($action == 'add') {
		$station_num = $_POST['id'];
/*
		$qry = "INSERT IGNORE INTO join_user_station (user_id, tf_station_num, us_key, label, channel, priority)".
		" SELECT ". $_SESSION['user_id'] .", ". $station_num .", '". $_SESSION['user_id'] ."-". $station_num ."', stat.tf_station_name, CONCAT(CONCAT(tf_major_channel_number), '.', CONCAT(tf_minor_channel_number)), 999".
		" FROM tms_dgstatrec stat, tms_dgpsiprec psip".
		" WHERE psip.tf_station_num = ". $station_num .
		" 	AND stat.tf_station_num = ". $station_num;
*/
		$qry = "INSERT IGNORE INTO join_user_station (user_id, tf_station_num, label, channel, priority)".
		" SELECT ". $user->data['user_id'] .", ". $station_num .", stat.tf_station_name, CONCAT(CONCAT(tf_major_channel_number), '.', CONCAT(tf_minor_channel_number)), 999".
		" FROM tms_dgstatrec stat, tms_dgpsiprec psip".
		" WHERE psip.tf_station_num = ". $station_num .
		" 	AND stat.tf_station_num = ". $station_num;
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
	$result = mQuery("SELECT tf_station_num FROM join_user_station WHERE user_id = '$user->data[user_id]'");
	$x = 0;
	while ($row = mysql_fetch_assoc($result)) {
		$my_stations[$x++] = $row['tf_station_num'];
	}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>HDTV Magazine - Stations by Market</title>
	<?require(BASE_DIR .'/includes/common_header.php');?>
	<meta name="description" content="HDTV Stations by Market">
	<meta name="keywords" content="hdtv,hdtv programming,hdtv guide,hdtv schedule,hdtv listing,hdtv guides,hdtv schedules,hdtv listings,hdtv programs,hdtv programming guide,hdtv programming schedule,hdtv program,hd guide,hd programming,hd schedule,abc hdtv programming,cbs hdtv programming,digital tv programming,digital tv schedule,digital tv schedules,dtv listings,dtv program,dtv programing,dtv programming,dtv schedule,hd programing,hd programming,hd programs,high definition guide,high definition programing,high definition programming,high definition programs,high definition schedule,nbc hdtv programming,guide,news,sports,hdtv stations">
	<meta name="rating" content="general">
</head>
<body>
	<?
		include(BASE_DIR .'/includes/tracker.php');
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
	?>

	<h1>Stations by Market</h1>

	<p>
		Choose your Market below. You can then add/remove stations based on your preferences.
	</p>

	<form name="frm" method="get" action="<?=PHP_SELF?>">
		<table class="bare" align="center" style="margin-top:20px">
			<tr>
				<td class="inputLabel">Choose a Market:</td>
				<td><?=getSelectBox("SELECT DISTINCT tf_dma_name, tf_dma_name FROM tms_dgstatrec WHERE tf_dma_name <> '' ORDER BY tf_dma_name", "tf_dma_name", $mid, 'onchange="submit()"')?></td>
		</td></tr></table>
	</form>

	<?if ($mid != '') {
		$qry = "SELECT tf_dma_name, stat.tf_station_num, stat.tf_station_affil, stat.tf_station_name, concat(psip.tf_major_channel_number, '.', psip.tf_minor_channel_number) channel".
		" FROM tms_dgstatrec stat".
		" LEFT JOIN tms_dgpsiprec psip ON psip.tf_station_num = stat.tf_station_num".
		" WHERE stat.tf_dma_name = '". $_GET['tf_dma_name'] ."'".
  		" ORDER BY tf_major_channel_number, tf_minor_channel_number";
  		$result = mQuery($qry);
		$row = mysql_fetch_array($result);
		?>
		<form name="frmQM" action="<?=PHP_SELF?>" method="post">
			<input type="hidden" name="action" value="">
			<input type="hidden" name="id" value="">
			<table class="type1b" align="center"  style="width:75%">
				<tr><td class="type1b_header" colspan="4"><?=$row['tf_dma_name']?></td></tr>
				<?
					mysql_data_seek($result, 0);
					while ($row = mysql_fetch_array($result)) {
						$qa_text = in_array($row['tf_station_num'], $my_stations) ?
							'<img src="/images/add-d.gif" alt="Already Added">&nbsp;<img style="cursor:hand" src="/images/remove.gif" alt="Remove" onclick="quickMod('. $row['tf_station_num'] .', \'remove\')">' :
							'<img style="cursor:hand" src="/images/add.gif" alt="Add" onclick="quickMod('. $row['tf_station_num'] .', \'add\')">&nbsp;<img src="/images/remove-d.gif" alt="Not Added">';
						echo '<tr onMouseOver="hover_over(this)" onMouseOut="hover_out(this)">'.
						'	<td class="type1b" width="30" align="right" style="padding-right:5px">'. $row['channel'] .'</td>'.
						'	<td class="type1b"><a href="/programming/guide-station.php?id='. $row['tf_station_num'] .'">'. $row['tf_station_name'] .'</a></td>'.
						'	<td class="type1b">'. $row['tf_station_affil'] .'</td>';
						if ($user->data[user_id] > 0) echo '	<td class="type1b" align="right">'. $qa_text .'</td>';
						echo '</tr>';
					}
				?>
			</table>
		</form>
	<?}?>

	<?include(BASE_DIR .'/includes/body_footer.php');?>

</body>
</html>
