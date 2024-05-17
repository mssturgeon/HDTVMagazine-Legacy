<?
	require('../global.php');

	$get_dma_name = isset($_GET['dma_name']) ? $_GET['dma_name'] : js_replace(URL_PROG_BROADCAST);
	if (is_array($get_dma_name)) {
		$dma_name = $get_dma_name;
	} else {
		$dma_name[] = $get_dma_name;
	}

	// Get Home Address and Geocode
	$is_home_defined = 'false';
	$lat_home = 0;
	$lon_home = 0;
	if ($user->data['user_id'] > 0) {
		$lat_home = $user->data['lat'];
		$lon_home = $user->data['lon'];
		if ($lat_home != 0 && $lon_home != 0) {
			$is_home_defined = 'true';
			$lat1 = $lat_home * M_PI/180;
			$lon1 = $lon_home * M_PI/180;
			$user_address = '<b>Home Address:</b><br>'. $user->data['street_address_1'] .'<br>'. $user->data['city'] .', '. $user->data['state'] . '<br><a href="'. URL_PROFILE .'">Edit</a>';
		}
	}

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - Broadcast HDTV Market : <?=urldecode(implode('/', $dma_name))?></title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
	<meta name="description" content="Broadcast HDTV Programming for the <?=urldecode(implode('/', $dma_name))?> Market(s)">
	<meta name="keywords" content="hdtv,hd,high definition,broadcast,programming,market,<?=urldecode(implode('/', $dma_name))?>">
	<script src="http://maps.google.com/maps?file=api&v=1&key=[REDACTED]" type="text/javascript"></script>
	<script language="javascript" type="text/javascript">
		var map;
		var point = new Array();
		var marker = new Array();
		var data = new Array();
		var baseIcon = new GIcon();
		var isHomeDefined = <?=$is_home_defined?>;
		baseIcon.shadow = "/images/mapfiles/shadow50.png";
		baseIcon.iconSize = new GSize(20, 34);
		baseIcon.shadowSize = new GSize(37, 34);
		baseIcon.iconAnchor = new GPoint(9, 34);
		baseIcon.infoWindowAnchor = new GPoint(9, 2);
		baseIcon.infoShadowAnchor = new GPoint(18, 25);

		function init() {
			map = new GMap(document.getElementById("map"));
			map.addControl(new GSmallMapControl());
			map.addControl(new GMapTypeControl());
			map.centerAndZoom(startpoint, 8);
			for (x=0; x<point.length; x++) {
				map.addOverlay(marker[x]);
			}

			// Set Home
			if (isHomeDefined) {
				point_home = new GPoint(<?=$lon_home?>, <?=$lat_home?>);
				var icon = new GIcon(baseIcon);
				icon.image = "/images/mapfiles/marker-home.png";
				var marker_home = new GMarker(point_home, icon);

				// Show this marker's index in the info window when it is clicked
				GEvent.addListener(marker_home, "click", function() {
					marker_home.openInfoWindowHtml('<?=$user_address?>');
				});
				map.addOverlay(marker_home);

				// Draw a line from the home point to the first station
				var line = [];
				line.push(point_home);
				line.push(point[0]);
				polyline = new GPolyline(line, '#003f87', 5, .5);
				map.addOverlay(polyline);
			}
		}

		function mapTo(index) {
			// Color Table
			for (x=0; x<point.length; x++) {
				oTable = document.getElementById('listing'+ x);
				if (x == index) {
					oTable.style.backgroundColor = '#<?=BG_COLOR?>';
				} else {
					oTable.style.backgroundColor = '';
				}
			}

			// Recenter
			map.recenterOrPanToLatLng(point[index]);

			// Popup Infowindow
			marker[index].openInfoWindowHtml(data[index]);

			if (isHomeDefined) {
				// Remove old polyline
				map.removeOverlay(polyline);

				// Add new polyline
				var line = [];
				line.push(point_home);
				line.push(point[index]);
				polyline = new GPolyline(line, '#003f87', 5, .5);
				map.addOverlay(polyline);
			}

			return true;
		}

		// Creates a marker whose info window displays the letter corresponding to the given index
		function createMarker(p, index, channel) {
			// Create a lettered icon for this point using our icon class from above
			var icon = new GIcon(baseIcon);
			icon.image = "/images/mapfiles/marker"+ channel +".png";
			var m = new GMarker(p, icon);

			// Show this marker's index in the info window when it is clicked
			GEvent.addListener(m, "click", function() {
				m.openInfoWindowHtml(data[index]);
			});

			return m;
		}

		function addMarket(oSelect) {
			var val;
			val = oSelect[oSelect.selectedIndex].value;
			if (val.length > 0) document.location.href += '&dma_name[]='+ escape(val);
		}
	</script>
</head>
<body onload="init()" id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>

	<h1>Broadcast HDTV Market : <?=urldecode(implode('/', $dma_name))?></h1>
	&raquo; <a href="<?=URL_PROG?>">Programming</a> &raquo; <a href="<?=URL_PROG_BROADCAST?>">Broadcast</a> &raquo; <?=urldecode(implode('/', $dma_name))?> Market

	<div id="right" style="float:right; margin:0 0 5px 5px; text-align:center;" align="center">
		<? include(BASE_DIR .'/ads/mrectangle.php');?>
		<br />
	</div>

	<p style="margin-bottom:20px">
		Below you will find all of the broadcast stations for the <?=urldecode(implode('/', $dma_name))?> market that are currently carrying Digital content,
		either as Standard or High Definition. Click the numbered marker next to each station to zoom to that station and pull up the station's information.
	</p><p>
		<b>Please note</b> that you may not see corresponding markers for all station listed on the left, since many stations either share towers, or have them in clusters.
		Clicking on each of the markers along the left will indicate on which antenna the station is located, along with height and power information.
		<!--If you are a <a href="<?=URL_GUIDE?>">Program Guide</a> subscriber, simply click on a station name for a detail of the programming available over the next 2 weeks.-->
	</p><p>
		If you have a home address defined in <a href="<?=URL_PROFILE?>">your profile</a>, it will be displayed on the map along with distance and bearing information for each station.
	</p><p>
		<a href="<?=URL_PROG_BROADCAST_MARKET_PRINT?>?<?=$_SERVER['QUERY_STRING']?>"><img src="/images/print.gif" alt="Print" align="absmiddle">Print</a>
		 - Need a hard-copy? Print a handy table to take with you to the roof-top.
	</p><br />

	<?
		$sql = "
		SELECT virtual_channel_number, affiliation_1, img, tv_dom_status, s.source_id, call_sign, hag_rc_mtr, effective_erp,
			lat_deg, lat_min, lat_sec, lat_dir, lon_deg, lon_min, lon_sec, lon_dir, analog_channel, station_channel
		FROM prog_source s, cdbs_tv_eng_data e, aux_prog_source a
		WHERE dma_name IN ('". urldecode(implode("', '", $dma_name)) ."')
			AND e.facility_id = a.facility_id
			AND a.source_id = s.source_id
			AND lon_deg <> 0 AND lat_deg <> 0
			AND eng_record_type = 'C'
			AND vsd_service = 'DT'
			AND tv_dom_status = 'LIC'
		ORDER BY virtual_channel_number+0";
#		echo $sql;
		$result = mQuery($sql);
		$num = mysql_num_rows($result);
		$x = 0;

		echo '<div style="font-weight:bold">Add another Market area to this map: ';
		echo getSelectBox("SELECT DISTINCT dma_name, dma_name FROM prog_source WHERE dma_name <> '' ORDER BY dma_name", "dma_name", '', ' onchange="addMarket(this)"');
	?>
	</div><br>
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr><td style="width:225px;"><div style="height:550px;overflow:auto">
		<?
			while ($row = $db->sql_fetchrow($result)) {
				$channel = $row['virtual_channel_number'];
				$image = '<img align="absmiddle" alt="'. $row['affiliation_1'] .'" src="/images/logos/'. str_replace('[size]', '15', $row['img']) .'">';
				$lon[$x] = round($row['lon_deg'] + ($row['lon_min']/60) + ($row['lon_sec']/3600), 6);
				$lat[$x] = round($row['lat_deg'] + ($row['lat_min']/60) + ($row['lat_sec']/3600), 6);
				$lon[$x] *= ($row['lon_dir'] == 'W') ? -1 : 1;
				$lat[$x] *= ($row['lat_dir'] == 'S') ? -1 : 1;

				switch($row['tv_dom_status']) {
					case 'APP':
						$status = 'Application';
						break;
					case 'CP':
						$status = 'Construction Permit';
						break;
					case 'CP MOD':
						$status = 'Construction Permit Modification';
						break;
					case 'LIC':
						$status = 'Licensed';
						break;
					default:
						$status = 'Unknown';
						break;
				}
				// Compute distance and bearing only if home point is defined
				if ($is_home_defined == 'true') {
					$lat2 = $lat[$x] * M_PI/180;
					$lon2 = $lon[$x] * M_PI/180;
					$d = acos(sin($lat1) * sin($lat2) + cos($lat1) * cos($lat2) * cos($lon2 - $lon1));
					$distance = round((180 * 60 / M_PI * 1.15077945) * $d, 2);
					$bearing = ((atan2( sin($lon2 - $lon1) * cos($lat2), cos($lat1) * sin($lat2) - sin($lat1) * cos($lat2) * cos($lon2 - $lon1) ) * 180/M_PI) + 360) % 360;
					$db_text = $distance .' mi, '. round($bearing) .'&deg;';
					$db_text_popup = '<tr>'.
					'	<td>Distance:</td>'.
					'	<td>'. $distance .' mi.</td>'.
					'</tr><tr>'.
					'	<td>Bearing:</td>'.
					'	<td>'. round($bearing) .'&deg;</td>'.
					'</tr>';
				}
				$data[$x] = '<table class="bare" cellpadding="1" cellspacing="0">'.
				'<tr>'.
				'	<td colspan="2">'.
						$image .' <a href="/programming/guide-station.php?id='. $row['source_id'] .'">'. $row['call_sign'] .'</a> ('. $channel .')'.
				'	</td>'.
				'</tr><tr>'.
				'	<td colspan="2">'.
						$row['affiliation_1'] .
				'	</td>'.
				'</tr><tr>'.
				'	<td>RF Channel:</td>'.
				'	<td>'. $row['station_channel'] .'</td>'.
				'</tr><tr>'.
				'	<td>Status:</td>'.
				'	<td>'. $status .'</td>'.
				'</tr><tr>'.
				'	<td>Height:</td>'.
				'	<td>'. $row['hag_rc_mtr'] .'m</td>'.
				'</tr><tr>'.
				'	<td>Power:</td>'.
				'	<td>'. $row['effective_erp'] .' kW</td>'.
				'</tr>'. $db_text_popup .'</table>';

				echo ''.
				'	<script language="javascript" type="text/javascript">'."\n".
				'		point['. $x .'] = new GPoint('. $lon[$x] .', '. $lat[$x] .');'."\n".
				'		marker['. $x .'] = createMarker(point['. $x .'], '. $x .', '. floor($row['virtual_channel_number']) .');'."\n".
				'		data['. $x .'] = \''. $data[$x] .'\';'."\n".
				'	</script>'."\n".
				'	<table id="listing'. $x .'" class="bare" cellpadding="3" cellspacing="0"><tr>'.
//							'		<td style="vertical-align:top;padding-right:3px"><img src="/images/mapfiles/marker'. ($x+1) .'.png" onclick="mapTo('. $x .')" ></td>'.
				'		<td style="vertical-align:top;padding-right:3px"><img src="/images/mapfiles/marker'. floor($row['virtual_channel_number']) .'.png" onclick="mapTo('. $x .')" ></td>'.
				'		<td style="vertical-align:top;">'.
							$image .' <a href="/programming/guide-station.php?id='. $row['source_id'] .'">'. $row['call_sign'] .'</a> Ch. '. $channel .'<br>'.
							$row['affiliation_1'] .'<br>'.
							$db_text .
				'		</td>'.
				'	</tr></table>';
				$x++;
			}

			// Assign point[0] to the average of all points for the purposes of map centering
					echo ''.
					'	<script language="text/javascript" type="text/javascript">'."\n".
					'		startpoint = new GPoint('. (array_sum($lon)/$x) .', '. (array_sum($lat)/$x) .');'."\n".
					'	</script>'."\n";

		?>
		</div></td><td style="vertical-align:top;padding-left:5px;">
			<div id="map"></div>
		</td></tr>
	</table>
	<div align="center" style="margin:10px">
		* All antenna information is provided by the Consolidated Database System (CDBS) via the FCC's Media Bureau.<br>
		Power is Effective Power and Height is the height above ground of the radiation center.
	</div>

	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
</body>
</html>
