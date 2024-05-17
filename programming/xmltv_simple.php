<?
	require('../global.php');
	header('Content-type: application/xml');

	$base_url = 'http://'. SERVER_NAME;
?>
<tv generator-info-name="www.hdtvmagazine.com">
	<?
		// Loop through channels
		$stat_num_old = '';
		$result = mQuery("SELECT stat.tf_station_num, tf_station_name, tf_title, tf_epi_title, tf_reduced_desc_8 descr, tf_air_time, image_file".
		" FROM tms_dgprogrec prog, tms_dgstatrec stat, tms_dgskedrec sked".
		" WHERE stat.tf_station_num = 28711".
		"	AND options & ". OPT_SKED_HDTV .
		"	AND sked.tf_station_num = stat.tf_station_num".
		"	AND sked.tf_database_key = prog.tf_database_key".
		" ORDER BY tf_station_num, tf_air_time LIMIT 10");
		while ($stat = mysql_fetch_assoc($result)) {
			$stat_num = $stat['tf_station_num'];
			if ($stat_num != $stat_num_old) {
				$image_path = $base_url .'/images/logos/'. str_replace('[size].', '15.', $stat['image_file']);

   			echo '<channel id="'. $stat_num .'">'.
   			'	<display-name>'. $stat['tf_station_name'] .'</display-name>'.
   			'	<icon>'. $image_path .'</icon>'.
   			'</channel>';
				$stat_num_old = $stat_num;
			}
			
			// Loop through programming
			echo '<programme start="'. date('YmdHis', $stat['tf_air_time']) .'" channel="'. $stat_num .'">'.
			'	<title>'. $stat['tf_title'] .'</title>';
			
			if ($stat['tf_epi_title'] != '') echo '	<sub-title>'. $stat['tf_epi_title'] .'</sub-title>';
			
			echo '	<desc>'. $stat['descr'] .'</desc>';
			
			echo '</programme>';
		}
	?>
</tv>
