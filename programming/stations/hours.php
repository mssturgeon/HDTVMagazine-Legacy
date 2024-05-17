<?
	include('../global.php');
	if (!access(ACCESS_PREMIUM)) prompt_login(PHP_SELF);

  	$user_id = isset($user->data['user_id']) ? $user->data['user_id'] : 0;

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - Hours of HD</title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
	<meta name="description" content="Hours of HDTV Programming per Station">
	<meta name="keywords" content="hdtv,hdtv programming,hdtv guide,hdtv schedule,hdtv listing,hdtv guides,hdtv schedules,hdtv listings,hdtv programs,hdtv programming guide,hdtv programming schedule,hdtv program,hd guide,hd programming,hd schedule,abc hdtv programming,cbs hdtv programming,digital tv programming,digital tv schedule,digital tv schedules,dtv listings,dtv program,dtv programing,dtv programming,dtv schedule,hd programing,hd programming,hd programs,high definition guide,high definition programing,high definition programming,high definition programs,high definition schedule,nbc hdtv programming,guide,news,sports,hdtv stations">
	<meta name="rating" content="general">
</head>
<body>
	<?
		include(BASE_DIR .'/includes/body_header-4.php');
	?>

				<table class="box" align="center"><tr><td>
					Hours-of-HD next week for your default stations (rounded to the nearest hour).
				</td></tr></table>
				<?
					// Get Dates
					$start_time = strtotime("Sunday");
					$end_time = $start_time + 7*DAYS;
					$start_date = gmdate("Y-m-d", $start_time);
					$end_date = gmdate("Y-m-d", $end_time);

					// Get Stations & Initialize
					$names = array();
					$qry = "SELECT label, tf_station_num FROM join_user_station WHERE user_id = ". $user_id;
					$result = mQuery($qry);
					while ($row = mysql_fetch_assoc($result)) {
						$k1 = $row['label'];
						$names[$k1] = array();
						$rTotal[$k1] = 0;
						$ids .= $row['tf_station_num'] .',';

						for ($x = $start_time; $x < $end_time; $x+=1*DAYS) {
							$names[$k1][date("m/d", $x)] = 0;
						}
					}
					$ids = substr($ids, 0, strlen($ids)-1);

					echo '<table class="table1" align="center">';
					echo '	<tr><td class="table1Header">Name</td>';
					for ($x = $start_time; $x < $end_time; $x+=1*DAYS) {
						echo '		<td class="table1Header">'. date("m/d", $x) .'</td>';
					}
					echo '<td class="table1Header">Total</td></tr>';

					$qry = "SELECT tf_station_name, tf_air_date, round(sum(tf_duration)/60) minutes".
					" FROM tms_dgskedrec k, tms_dgstatrec s".
					" WHERE k.tf_station_num = s.tf_station_num".
					"	AND tf_air_date >= '". $start_date ."'".
					"	AND tf_air_date < '". $end_date ."' ".
					"	AND k.tf_station_num IN (". $ids .")".
					"	AND subscriptions & ". OPT_SKED_HDTV .
					" GROUP BY tf_station_name, tf_air_date";
					$result = mQuery($qry);

					while ($row = mysql_fetch_assoc($result)) {
						$k1 = $row['tf_station_name'];
						$k2 = date("m/d", strtotime($row['tf_air_date']));
						$names[$k1][$k2] += $row['minutes'];

						$rTotal[$k1] += $row['minutes'];
						$cTotal[$k2] += $row['minutes'];
					}

					foreach ($names as $k1 => $sub_names) {
						echo '<tr><td class="blank">'. $k1 .'</td>';
						foreach ($sub_names as $k2 => $minutes) {
							echo '<td class="blank" align="right">'. $minutes .'</td>';
						}
						echo '<td class="blank" align="right"><b>'. $rTotal[$k1] .'</b></td></tr>';
					}

					// Write bottom row
					$total_total = 0;
					echo '<tr><td class="blank">&nbsp;</td>';
					foreach ($cTotal as $date => $minutes) {
						echo '<td class="blank" align="right"><b>'. $minutes .'</b></td>';
						$total_total += $minutes;
					}
					echo '<td class="blank" align="right"><b>'. $total_total .'</b></td></tr>';

					echo '</table>';
				?>

		<? include(BASE_DIR .'/includes/body_footer-4.php');?>

</body>
</html>
