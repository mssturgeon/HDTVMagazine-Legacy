<?
	require('../global.php');

	$get_dma_name = isset($_GET['dma_name']) ? $_GET['dma_name'] : '';
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
		}
	}

	require(BASE_DIR .'/includes/doctype.php');
?>
<!--DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"-->
<!--DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"-->
<html>
<head>
	<title>HDTV Magazine - Broadcast HDTV Market : <?=urldecode(implode('/', $dma_name))?></title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
	<meta name="description" content="Broadcast HDTV Programming for the <?=urldecode(implode('/', $dma_name))?> Market(s) in a handy printable table.">
	<meta name="keywords" content="hdtv,hd,high definition,broadcast,programming,market,<?=urldecode(implode('/', $dma_name))?>">
</head>
<body style="background-image:none">
	<table class="type1b" cellpadding="0" cellspacing="0">
		<tr>
			<td class="type1b_header">&nbsp;</td>
			<td class="type1b_header">Station Name</td>
			<td class="type1b_header" style="text-align:right">Ch.</td>
			<td class="type1b_header" style="text-align:right">RF Ch.</td>
			<td class="type1b_header">Affiliate</td>
			<td class="type1b_header">Status</td>
			<td class="type1b_header" style="text-align:right">Height</td>
			<td class="type1b_header" style="text-align:right">Power</td>
			<td class="type1b_header" style="text-align:right">Distance</td>
			<td class="type1b_header" style="text-align:right">Bearing</td>
		</tr><?
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
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result)) {
				$channel =
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
					$distance = round((180 * 60 / M_PI * 1.15077945) * $d, 1) .' mi.';
					$bearing = ((atan2( sin($lon2 - $lon1) * cos($lat2), cos($lat1) * sin($lat2) - sin($lat1) * cos($lat2) * cos($lon2 - $lon1) ) * 180/M_PI) + 360) % 360 .'&deg;';
				}

				echo '<tr>'.
				'	<td class="grid" style="text-align:center"><img align="absmiddle" alt="'. $row['affiliation_1'] .'" src="/images/logos/'. str_replace('[size]', '15', $row['img']) .'"></td>'.
				'	<td class="grid"><a href="/programming/guide-station.php?id='. $row['source_id'] .'">'. $row['call_sign'] .'</a></td>'.
				'	<td class="grid" style="text-align:right">'. $row['virtual_channel_number'] .'</td>'.
				'	<td class="grid" style="text-align:right">'. $row['station_channel'] .'</td>'.
				'	<td class="grid">'. $row['affiliation_1'] .'</td>'.
				'	<td class="grid">'. $status .'</td>'.
				'	<td class="grid" style="text-align:right">'. $row['hag_rc_mtr'] .'</td>'.
				'	<td class="grid" style="text-align:right">'. $row['effective_erp'] .'</td>'.
				'	<td class="grid" style="text-align:right">'. $distance .'</td>'.
				'	<td class="grid" style="text-align:right">'. $bearing .'</td>'.
				'</tr>';
			}
		?>
	</table>

	<div align="center" style="margin:10px">
		* All antenna information is provided by the Consolidated Database System (CDBS) via the FCC's Media Bureau.<br>
		Power is Effective Power and Height is the height above ground of the radiation center.
	</div>

</body>
</html>
