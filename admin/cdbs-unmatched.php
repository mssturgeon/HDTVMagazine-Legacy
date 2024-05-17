<?
	require('../global.php');
	if (!access(ACCESS_ADMIN_ANY)) prompt_login(PHP_SELF);
	header('Cache-Control: no-store'); // HTTP/1.1

	$action = isset($_POST['action']) ? $_POST['action'] : '';
	if ($action == 'submit') {
		for ($x=1; $x <= $_POST['number']; $x++) {
			$source_id = $_POST["source_id_$x"];
			$qry = "UPDATE aux_prog_source SET facility_id = ". $_POST["facility_id_$source_id"] ." WHERE source_id = ". $source_id;
			mQuery($qry);
#			echo "$qry<br>";
		}
		js_back();
		exit;
	}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>HDTV Magazine - HDTV Stations Admin</title>
	<?require(BASE_DIR .'/includes/common_header.php');?>
</head>
<body>
	<?
		include(BASE_DIR .'/includes/tracker.php');
		include(BASE_DIR .'/includes/body_header.php');
	?>
	<h1>HDTV Stations Admin</h1>

	<form action="<?=PHP_SELF?>" method="post" name="frm">
		<input type="hidden" name="action" value="submit">

		<table class="type1b" cellpadding="0" cellspacing="0">
			<tr>
				<td class="type1b_header">Station Number</td>
				<td class="type1b_header">Station Name</td>
				<td class="type1b_header">Call Sign</td>
				<td class="type1b_header">City</td>
				<td class="type1b_header">State</td>
				<td class="type1b_header">Facility</td>
			</tr>
			<?
				$qry = "SELECT s.source_id, full_name, call_sign, city, state, dma_name
				FROM prog_source s, aux_prog_source a
				WHERE s.source_id = a.source_id
					AND source_type = 'Broadcast'
					AND digital_source = 'Y'
					AND facility_id = 0
				LIMIT 200";
				$result = mQuery($qry);
				echo '<input type="hidden" name="number" value="'. mysql_num_rows($result) .'">';
				$x = 0;
				while ($row = mysql_fetch_assoc($result)) {
					$row_class = (++$x % 2 == 0) ? 'evenRow' : 'oddRow';
					$source_id = $row['source_id'];

					echo '<input type="hidden" name="source_id_'. $x .'" value="'. $source_id .'">'.
					'<tr class="'. $row_class .'">'.
					'	<td class="grid"><a href="/programming/guide-station.php?id='. $source_id .'">'. $source_id .'</a></td>'.
					'	<td class="grid">'. $row['full_name'] .'</td>'.
					'	<td class="grid">'. $row['call_sign'] .'</td>'.
					'	<td class="grid">'. $row['city'] .'</td>'.
					'	<td class="grid">'. $row['state'] .'</td>'.
					'	<td class="grid">'.
					'		<select name="facility_id_'. $source_id .'">'.
					'			<option value="0"></option>';

					# Check for call sign match and provide options. If no call sign match, display all sources within the same dma
					$qry = "SELECT fac_callsign, facility_id, comm_city, comm_state
					FROM cdbs_facility
					WHERE fac_service = 'TV'
						AND LEFT(fac_callsign, 4) = LEFT('$row[call_sign]', 4)
					ORDER BY fac_callsign";
					$fac_result = mQuery($qry);
					$num_choices = mysql_num_rows($fac_result);
					if ($num_choices == 1) {
						$fac_row = mysql_fetch_assoc($fac_result);
						echo '<option value="'. $fac_row['facility_id'] .'" selected>'. $fac_row['fac_callsign'] .' ('. $fac_row['comm_city'] .', '. $fac_row['comm_state'] .')</option>';
					} elseif ($num_choices > 0) {
						while ($fac_row = mysql_fetch_assoc($fac_result)) {
							echo '<option value="'. $fac_row['facility_id'] .'">'. $fac_row['fac_callsign'] .' ('. $fac_row['comm_city'] .', '. $fac_row['comm_state'] .')</option>';
						}
					} else {
						$qry = "SELECT fac_callsign, facility_id, comm_city, comm_state
						FROM cdbs_facility
						WHERE  fac_service = 'TV'
							AND nielsen_dma = '$row[dma_name]'
						ORDER BY fac_callsign";
						$fac_result = mQuery($qry);
						$num_choices = mysql_num_rows($fac_result);
						while ($fac_row = mysql_fetch_assoc($fac_result)) {
							echo '<option value="'. $fac_row['facility_id'] .'">'. $fac_row['fac_callsign'] .' ('. $fac_row['comm_city'] .', '. $fac_row['comm_state'] .')</option>';
						}
					}

/*
					mysql_data_seek($fac_result, 0);
					while ($fac_row = mysql_fetch_assoc($fac_result)) {
						$selected = (substr($fac_row['fac_callsign'], 0, 4) == substr($row['call_sign'], 0, 4)) ? 'SELECTED' : '';
						echo '<option value="'. $fac_row['facility_id'] .'" '. $selected .'>'. $fac_row['fac_callsign'] .' ('. $fac_row['comm_city'] .', '. $fac_row['comm_state'] .')</option>';
					}
*/
					echo '		</select>';
					echo ($num_choices > 1) ? " ($num_choices)" : '';
					echo '	</td>'.
					"</tr>\n";
				}
			?>
		</table>
		<br />

		<div align="center"><input type="submit" value="Submit" class="inputButton"></div>
	</form>

	<? include(BASE_DIR .'/includes/body_footer.php');?>
</body>
</html>
