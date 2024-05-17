<tv generator-info-name="www.hdtvmagazine.com">
	<?
		// Loop through channels
		$stat_num_old = '';
		$result = mQuery("");
		while ($stat = mysql_fetch_assoc($result)) {
			$stat_num = $stat['tf_station_num'];
			if ($stat_num != $stat_num_old) {
   			echo '<channel id="'. $stat_num .'">'.
   			'	<display-name>'. $stat['tf_station_name'] .'</display-name>'.
   			'</channel>';
				$stat_num_old = $stat_num;
			}
			
			// Loop through programming
			echo '<programme start="200006031633" channel="3sat.de">'.
			'	<title lang="de">blah</title>'.
			'	<title lang="en">blah</title>'.
			'	<desc lang="de">'.
			'		Blah Blah Blah.'.
			'	</desc>'.
			'	<credits>'.
			'		<director>blah</director>'.
			'		<actor>a</actor>'.
			'		<actor>b</actor>'.
			'	</credits>'.
			'	<date>19901011</date>'.
			'	<country>ES</country>'.
			'	<episode-num system="xmltv_ns">2 . 9 . 0/1</episode-num>'.
			'	<video>'.
			'		<aspect>16:9</aspect>'.
			'	</video>'.
			'	<rating system="MPAA">'.
			'		<value>PG</value>'.
			'		<icon src="pg_symbol.png" />'.
			'	</rating>'.
			'	<star-rating>'.
			'		<value>3/3</value>'.
			'	</star-rating>'.
			'</programme>';
		}
	?>
</tv>
