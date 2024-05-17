<?
	require('../global.php');
	if (!access(ACCESS_PREMIUM)) prompt_login(PHP_SELF);

	$user_id = $user->data[user_id];

	$action = isset($_GET[action]) ? $_GET[action] : '';
	if ($action == 'add') {
		$station_num = $_GET[id];
/*
		$qry = "INSERT IGNORE INTO join_user_station (user_id, tf_station_num, us_key, label, channel, priority)".
		" SELECT ". $user_id .", stat.tf_station_num, CONCAT('". $user_id ."', '-', stat.tf_station_num), stat.tf_station_name, CONCAT(tf_major_channel_number, '.', tf_minor_channel_number), 999".
		" FROM tms_dgstatrec stat LEFT JOIN tms_dgpsiprec psip ON stat.tf_station_num = psip.tf_station_num".
		" WHERE stat.tf_station_num = ". $station_num;
*/
		$qry = "INSERT IGNORE INTO join_user_station (user_id, tf_station_num, label, channel, priority)".
		" SELECT ". $user_id .", stat.tf_station_num, stat.tf_station_name, CONCAT(tf_major_channel_number, '.', tf_minor_channel_number), 999".
		" FROM tms_dgstatrec stat LEFT JOIN tms_dgpsiprec psip ON stat.tf_station_num = psip.tf_station_num".
		" WHERE stat.tf_station_num = ". $station_num;
		mQuery($qry);
		js_back('Station Added!');
		exit;
	} elseif ($action == 'remove') {
		$station_num = $_GET[id];
		$qry = "DELETE FROM join_user_station WHERE user_id = ". $user_id ." AND tf_station_num = ". $station_num;
		mQuery($qry);
		js_back('Station Removed!');
		exit;
	}

	// Build array of current stations
	$result = mQuery("SELECT tf_station_num FROM join_user_station WHERE user_id = ". $user_id);
	$x = 0;
	while ($row = mysql_fetch_assoc($result)) {
		$my_stations[$x++] = $row[tf_station_num];
	}

	$terms = isset($_GET[terms]) ? $_GET[terms] : '';
	$search_type = isset($_GET[type]) ? array_sum($_GET[type]) : 0;
	$count = isset($_GET[count]) ? $_GET[count] : 10;

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - Search</title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
	<meta name="description" content="HDTV Magazine Program Search">
	<meta name="keywords" content="hdtv,hdtv programming,hdtv guide,hdtv schedule,hdtv listing,hdtv guides,hdtv schedules,hdtv listings,hdtv programs,hdtv programming guide,hdtv programming schedule,hdtv program,hd guide,hd programming,hd schedule,abc hdtv programming,cbs hdtv programming,digital tv programming,digital tv schedule,digital tv schedules,dtv listings,dtv program,dtv programing,dtv programming,dtv schedule,hd programing,hd programming,hd programs,high definition guide,high definition programing,high definition programming,high definition programs,high definition schedule,nbc hdtv programming,guide,news,sports,hdtv stations">
	<meta name="rating" content="general">
	<script language="javascript" type="text/javascript">
		function search(s_type, s_count) {
			oType = document.getElementsByName('type[]');
			for (x=0; x < oType.length; x++) {
				if (parseInt(oType[x].value) & s_type) {
					oType[x].checked = true;
				} else {
					oType[x].checked = false;
				}
			}
			frmSearch.count.value = s_count;
			frmSearch.submit();
		}
	</script>
</head>
<body>
	<?
		include(BASE_DIR .'/includes/body_header-4.php');
	?>

	<h1>HDTV Program Guide Search</h1>
	<form method="get" name="frmSearch" action="<?=PHP_SELF?>">
		<input type="hidden" name="action" value="search">
		<input type="hidden" name="count" value="<?=$count?>">
		<table align="center">
			<tr><td>
				<input type="text" class="inputText" name="terms" style="width:30em" value="<?=stripslashes($terms)?>">
				<input type="submit" class="inputButton" name="btnSearch" value="Search">
			</td></tr>
			<tr><td>
				<input type="checkbox" name="type[]" value="1" <?$search_type & 1 ? print 'CHECKED' : print ''?>>Stations
				<input type="checkbox" name="type[]" value="2" <?$search_type & 2 ? print 'CHECKED' : print ''?>>Programming (Preferred Stations)
				<input type="checkbox" name="type[]" value="4" <?$search_type & 4 ? print 'CHECKED' : print ''?>>Programming (All)
			</td></tr>
		</table>
	</form>

	<? if ($action == 'search') {
		if ($search_type & 1) {
			/****** Get Station Results ******/
			$search_fields = 'tf_station_name, tf_station_call_sign, tf_dma_name';

			$qry = "SELECT stat.tf_station_num, tf_station_name, tf_station_call_sign, tf_station_affil, tf_dma_name, concat(tf_major_channel_number, '.', tf_minor_channel_number) channel".
			" ,MATCH ". $search_fields ." AGAINST ('". $terms ."') as rank".
			" FROM tms_dgstatrec stat LEFT JOIN tms_dgpsiprec psip ON stat.tf_station_num = psip.tf_station_num".
			" WHERE MATCH ". $search_fields ." AGAINST ('". $terms ."')";
			$stat_result = mQuery($qry);
			$stat_result_rows = mysql_num_rows($stat_result);
		}

		if ($search_type & 2 || $search_type & 4) {
			/****** Get National Programming Results ******/
			$search_fields = 'tf_title, tf_epi_title, tf_desc_160';

			$qry = "
			SELECT tf_database_key, tf_title, tf_epi_title, tf_desc_160, MATCH $search_fields AGAINST ('$terms') as rank
			FROM $admindata[tbl_prog] prog
			WHERE MATCH ". $search_fields ." AGAINST ('$terms')";
			$nat_result = mQuery($qry);
			$nat_result_rows = mysql_num_rows($nat_result);

			// Load array of database_keys for use in the preferred stations query
			$x = 0;
			$db_keys = array();
			while ($row = mysql_fetch_assoc($nat_result)) {
				$db_keys[$x++] = $row[tf_database_key];
			}
			if ($x > 0) mysql_data_seek($nat_result, 0);
		}

		if ($search_type & 2) {
			/****** Get Preferred Station Results ******/
			$search_fields = 'tf_title, tf_epi_title, tf_desc_160';

			$qry = "
			SELECT stat.tf_station_num, prog.tf_database_key, tf_air_time, tf_duration, tf_title, tf_epi_title, tf_desc_160
			FROM $admindata[tbl_prog] prog, $admindata[tbl_sked] sked, $admindata[tbl_stat] stat, join_user_station j
			WHERE prog.tf_database_key = sked.tf_database_key
				AND sked.tf_station_num = stat.tf_station_num
				AND options & ". OPT_SKED_HDTV ."
				AND stat.tf_station_num = j.tf_station_num
				AND j.user_id = ". $user->data[user_id] ."
				AND prog.tf_database_key IN ('". implode("','", $db_keys) ."')";
			$pref_result = mQuery($qry);
			$pref_result_rows = mysql_num_rows($pref_result);
		}

		if ($search_type & 1) {?>
			<br />
			<form name="frmQM" action="<?=PHP_SELF?>" method="get">
				<input type="hidden" name="action" value="">
				<input type="hidden" name="id" value="">
				<table class="bare" style="width:100%" cellspacing="0" align="center">
					<tr>
						<td style="font-weight:bold">Station Results</td>
						<td style="font-weight:bold; text-align:right;">
							Showing <?=$stat_result_rows == 0 ? 0 : 1?> - <?=$stat_result_rows < $count ? $stat_result_rows : $count?> of <?=$stat_result_rows?>
							 (<a href="javascript:search(1, '<?=$stat_result_rows?>')">Show All</a>)
						</td>
					</tr>
				</table>

				<table class="type1b" style="width:100%" align="center">
					<tr>
			  			<td class="type1b_header" align="center">Channel</td>
			  			<td class="type1b_header">Name</td>
			  			<td class="type1b_header">Call Sign</td>
			  			<td class="type1b_header">Affiliate</td>
			  			<td class="type1b_header">Market</td>
						<td class="type1b_header" align="center">Add/Remove</td>
			  		</tr><tr><?
					if ($stat_result_rows == 0) {
						echo '<tr><td class="type1b" align="center" colspan="6">No matches found for "'. $terms .'"</td><tr>';
					} else {
						$parity = 'odd';
						$x = 0;
						while (($row = mysql_fetch_assoc($stat_result)) && $x++ < $count) {
							$channel = $row[channel] == '.' ? '' : $row[channel];
							$qa_text = in_array($row[tf_station_num], $my_stations) ?
								'<img src="/images/add-d.gif" alt="Already Added">&nbsp;<a href="javascript:quickMod('. $row[tf_station_num] .', \'remove\')"><img src="/images/remove.gif" alt="Remove"></a>' :
								'<a href="javascript:quickMod('. $row[tf_station_num] .', \'add\')"><img src="/images/add.gif" alt="Add"></a>&nbsp;<img src="/images/remove-d.gif" alt="Not Added">';
							echo '<tr class="'. $parity .'Row">'.
							'	<td class="type1b" align="center">'. $channel .'</td>'.
							'	<td class="type1b"><a href="'. URL_GUIDE_STATION .'?id='. $row[tf_station_num] .'">'. $row[tf_station_name] .'</a></td>'.
							'	<td class="type1b">'. $row[tf_station_call_sign] .'</td>'.
							'	<td class="type1b">'. $row[tf_station_affil] .'</td>'.
							'	<td class="type1b">'. $row[tf_dma_name] .'</td>'.
							'	<td class="type1b" align="center">'. $qa_text .'</td>'.
							'</tr>';
							$parity = $parity == 'odd' ? 'even' : 'odd';
						}
					}
					?></tr>
				</table>
			</form><br />
		<? }?>

		<? if ($search_type & 2) {?>
			<br />
			<table class="bare" style="width:100%" cellspacing="0" align="center">
				<tr>
					<td style="font-weight:bold">Programming (Preferred Stations)</td>
					<td style="font-weight:bold; text-align:right;">
							Showing <?=$pref_result_rows == 0 ? 0 : 1?> - <?=$pref_result_rows < $count ? $pref_result_rows : $count?> of <?=$pref_result_rows?>
							 (<a href="javascript:search(2, '<?=$pref_result_rows?>')">Show All</a>)
					</td>
				</tr>
			</table>

			<table class="type1b" style="width:100%" align="center">
				<tr>
		  			<td class="type1b_header" nowrap>Air Date</td>
		  			<td class="type1b_header" nowrap>Air Time</td>
		  			<td class="type1b_header">Duration</td>
		  			<td class="type1b_header">Title/Episode</td>
		  			<td class="type1b_header">Description</td>
		  		</tr><tr><?
				if ($pref_result_rows == 0) {
					echo '<tr><td class="type1b" align="center" colspan="5">No matches found for "'. $terms .'"</td><tr>';
				} else {
					$parity = 'odd';
					$x = 0;
					while (($row = mysql_fetch_assoc($pref_result)) && $x++ < $count) {
						$episode = $row[tf_epi_title] != '' ? '<br><i>'. $row[tf_epi_title] .'</i>' : '';

						// Parse and compute duration
						$durhh = ($row[tf_duration] >= 60) ? floor($row[tf_duration] / 60) ."h": "";
						$durmm = (($row[tf_duration] % 60) > 0) ? $row[tf_duration] % 60 ."m" : "";
						$duration = trim($durhh .' '. $durmm);

						echo '<tr class="'. $parity .'Row">'.
						'	<td class="type1b">'. gmdate('m/d/Y', $row[tf_air_time] + $user->data[time_zone_offset]) .'</td>'.
						'	<td class="type1b">'. gmdate('g:ia', $row[tf_air_time] + $user->data[time_zone_offset]) .'</td>'.
						'	<td class="type1b">'. $duration .'</td>'.
						'	<td class="type1b" nowrap><a href="'. FULL_URL_GUIDE_PROGRAM .'?k='. $row[tf_database_key] .'&amp;a='. $row[tf_air_time] .'&amp;n='. $row[tf_station_num] .'">'. $row[tf_title] . $episode .'</a></td>'.
						'	<td class="type1b">'. $row[tf_desc_160] .'</td>'.
						'</tr>';
						$parity = $parity == 'odd' ? 'even' : 'odd';
					}
				}
				?></tr>
			</table><br />
		<?}?>

		<? if ($search_type & 4) {?>
			<br />
			<table class="bare" style="width:100%" cellspacing="0" align="center">
				<tr>
					<td style="font-weight:bold">Programming (All)</td>
					<td style="font-weight:bold; text-align:right;">
						Showing <?=$nat_result_rows == 0 ? 0 : 1?> - <?=$nat_result_rows < $count ? $nat_result_rows : $count?> of <?=$nat_result_rows?>
						 (<a href="javascript:search(4, '<?=$nat_result_rows?>')">Show All</a>)
					</td>
				</tr>
			</table>

			<table class="type1b" style="width:100%" align="center">
				<tr>
		  			<td class="type1b_header">Title/Episode</td>
		  			<td class="type1b_header">Description</td>
		  		</tr><tr><?
				if ($nat_result_rows == 0) {
					echo '<tr><td class="type1b" align="center" colspan="2">No matches found for "'. $terms .'"</td><tr>';
				} else {
					$parity = 'odd';
					$x = 0;
					while (($row = mysql_fetch_assoc($nat_result)) && $x++ < $count) {
						$episode = $row[tf_epi_title] != '' ? '<br><i>'. $row[tf_epi_title] .'</i>' : '';
						echo '<tr class="'. $parity .'Row">'.
						'	<td class="type1b" valign="top" nowrap>'.
							$row[tf_title] . $episode .
						'</td>'.
						'	<td class="type1b" valign="top">'. $row[tf_desc_160] .'</td>'.
						'</tr>';
						$parity = $parity == 'odd' ? 'even' : 'odd';
					}
				}
				?></tr>
			</table>
		<?}
	}?>

	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
</body>
</html>
