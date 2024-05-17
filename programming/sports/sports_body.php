<?
	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - <?=$key?> in HD</title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
	<meta name="description" content="HDTV Magazine Programming ">
	<meta name="keywords" content="hdtv,hdtv programming,hdtv guide,hdtv schedule,hdtv listing,hdtv guides,hdtv schedules,hdtv listings,hdtv programs,hdtv programming guide,hdtv programming schedule,hdtv program,hd guide,hd programming,hd schedule,abc hdtv programming,cbs hdtv programming,digital tv programming,digital tv schedule,digital tv schedules,dtv listings,dtv program,dtv programing,dtv programming,dtv schedule,hd programing,hd programming,hd programs,high definition guide,high definition programing,high definition programming,high definition programs,high definition schedule,nbc hdtv programming,guide,news,sports,hdtv stations">
	<meta name="rating" content="general">
</head>
<body>
	<?
		include(BASE_DIR .'/includes/body_header-4.php');

		// Get last date in database
		$result = mQuery("SELECT DISTINCT tf_air_date FROM tms_dgskedrec ORDER BY tf_air_date DESC");
		$ldrow = mysql_fetch_assoc($result);
		$last_date = gmdate('m/d/Y', strtotime($ldrow[tf_air_date]));
	?>

	<p>
		Listed below are the <?=$key?> games scheduled to be in HD through <?=$last_date?>.  All times relative to the time zone set in your profile (<?=$user->data[time_zone_text]?>).
	</p>

	<table class="type1b" align="center">
		<tr><td class="type1b_header" colspan="4">Games in your market (<?=$user->data[tf_dma_name]?>)</td></tr>
	<? if ($user->data[tf_dma_name] != '') {
		  	$qry = "
			SELECT *
			 FROM tms_dgstatrec stat, tms_dgskedrec sked, tms_dgprogrec prog, user
			 WHERE stat.tf_station_num = sked.tf_station_num
				AND sked.tf_database_key = prog.tf_database_key
				AND stat.tf_dma_name = user.tf_dma_name
				AND user.id = ". $user->data[user_id] ."
				AND tf_title = '$key'
				AND user.options & ". OPT_SKED_HDTV ."
			 ORDER BY tf_air_time";
		  	$result = mQuery($qry);
			if (mysql_num_rows($result) == 0) {
				echo '<tr><td colspan="4">There are no games scheduled for your local market.</td></tr>';
			} else {
			  	while ($row = mysql_fetch_assoc($result)) {
					$air_date = gmdate('D, M jS', $row[tf_air_time] + $user->data[time_zone_offset]);
					$air_time = gmdate('g:ia', $row[tf_air_time] + $user->data[time_zone_offset]);
					$live = $row[tf_live_tape_delay];
			  		echo '<tr>'.
					'	<td class="type1b">'.
					'		<a href="javascript:openProgram(\''. $row[tf_database_key] .'\', \''. $row[tf_station_num] .'\')">'. $row[tf_epi_title] .'</a>'.
					'		<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;on '. $row[tf_station_name].
					'	</td>'.
					'	<td class="type1b">'. $live .'</td>'.
					'	<td class="type1b">'. $air_date .'</td>'.
					'	<td class="type1b">'. $air_time .'</td>'.
					'</tr>';
			  	}
			}
		} else {
			echo '<tr><td colspan="3">You do not have a local market selected.  Please <a href="'. URL_PROFILE .'">edit your profile</a> and select a market to see this information.</td></tr>';
		}?>
	</table>
	<br>

	<table class="type1b" align="center">
		<tr><td class="type1b_header" colspan="4">Games carried nationally</td></tr>
	  	<?
		  	$qry = "SELECT *
			FROM $admindata[tbl_stat] stat, $admindata[tbl_sked] sked, $admindata[tbl_prog] prog
			WHERE stat.tf_station_num = sked.tf_station_num
				AND sked.tf_database_key = prog.tf_database_key
				AND stat.ota = 0
				AND tf_title = '$key'
			ORDER BY tf_air_time";
		  	$result = mQuery($qry);
			if (mysql_num_rows($result) == 0) {
				echo '<tr><td colspan="4">There are no games scheduled nationally.</td></tr>';
			} else {
			  	while ($row = mysql_fetch_assoc($result)) {
					$air_date = gmdate('D, M jS', $row[tf_air_time] + $user->data[time_zone_offset]);
					$air_time = gmdate('g:ia', $row[tf_air_time] + $user->data[time_zone_offset]);
					$live = $row[tf_live_tape_delay];
			  		echo '<tr>'.
					'	<td class="type1b">'.
					'		<a href="'. FULL_URL_GUIDE_PROGRAM .'?k='. $row[tf_database_key] .'&amp;a='. $row[tf_air_time] .'&amp;n='. $row[tf_station_num] .'">'. $row[tf_epi_title] .'</a>'.
					'		<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;on '. $row[tf_station_name].
					'	</td>'.
					'	<td class="type1b">'. $live .'</td>'.
					'	<td class="type1b">'. $air_date .'</td>'.
					'	<td class="type1b">'. $air_time .'</td>'.
					'</tr>';
			  	}
			}
		?>
	</table>

	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
</body>
</html>
