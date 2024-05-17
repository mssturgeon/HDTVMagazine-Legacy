<?
	require('../global.php');
	if (!access(ACCESS_ADMIN_ANY)) access_denied();
	header('Cache-Control: no-store'); // HTTP/1.1

	$action = isset($_POST[action]) ? $_POST[action] : '';
	if ($action == 'submit') {
		for ($x=0; $x < $_POST[number]; $x++) {
			$sql = "UPDATE event SET rank = '". $_POST["rank$x"] ."' WHERE id = '". $_POST["id$x"] ."'";
			echo "$sql\n";
			mQuery($sql);
		}
		js_back();
		exit;
	}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>HDTV Magazine - Event Admin</title>
	<?require(BASE_DIR .'/includes/common_header.php');?>
	<script language="javascript" type="text/javascript">
		function add() {
			 var h = 325;
			 var w = 500;
			 var t = (screen.height - h) / 2;
			 var l = (screen.width - w) / 2;
		
			 window.open('event-add.php', 'Add', 'resizable=yes,scrollbars=yes,width='+w+',height='+h+',top='+t+',left='+l)
		}
	</script>
</head>
<body>
	<?
		include(BASE_DIR .'/includes/tracker.php');
		include(BASE_DIR .'/includes/body_header.php');
	?>

	<form action="<?=PHP_SELF?>" method="post" name="frm">
		<input type="hidden" name="action" value="submit">
		<table class="type1b" cellpadding="0" cellspacing="0">
			<tr>
				<!--td class="type1b_header">ID</td-->
				<td class="type1b_header">Title</td>
				<!--td class="type1b_header">Description</td-->
				<td class="type1b_header">Start Time</td>
				<td class="type1b_header">Stop Time</td>
				<td class="type1b_header">Venue Name</td>
				<td class="type1b_header">City Name</td>
				<td class="type1b_header">Region Name</td>
				<td class="type1b_header">Region Abbr</td>
				<td class="type1b_header">Country Name</td>
				<td class="type1b_header">Country Abbr</td>
				<td class="type1b_header">Latitude</td>
				<td class="type1b_header">Longitude</td>
				<td class="type1b_header" align="center" nowrap>Purge/Keep</td>
			</tr>
				<?
					$qry = "SELECT * FROM event WHERE rank >= 0 AND start_time > NOW() ORDER BY start_time";
					$result = mQuery($qry);
					$x = 0;
					echo '<input type="hidden" name="number" value="'. mysql_num_rows($result) .'">';

					while ($row = mysql_fetch_assoc($result)) {
						$purge_selected = ($row[rank] == -1) ? 'SELECTED' : '';
						$none_selected = ($row[rank] == 0) ? 'SELECTED' : '';
						$keep_selected = ($row[rank] == 1) ? 'SELECTED' : '';

						echo '<input type="hidden" name="id'. $x .'" value="'. $row[id] .'">';
						echo '<tr>'.
#						'	<td class="grid">'. $row[id] .'</td>'.
						'	<td class="grid"><a href="'. $row[link] .'">'. $row[title] .'</a></td>'.
#						'	<td class="grid">'. $row[description] .'</td>'.
						'	<td class="grid">'. $row[start_time] .'</td>'.
						'	<td class="grid">'. $row[stop_time] .'</td>'.
						'	<td class="grid">'. $row[venue_name] .'</td>'.
						'	<td class="grid">'. $row[city_name] .'</td>'.
						'	<td class="grid">'. $row[region_name] .'</td>'.
						'	<td class="grid">'. $row[region_abbr] .'</td>'.
						'	<td class="grid">'. $row[country_name] .'</td>'.
						'	<td class="grid">'. $row[country_abbr] .'</td>'.
						'	<td class="grid">'. $row[latitude] .'</td>'.
						'	<td class="grid">'. $row[longitude] .'</td>'.
						'	<td class="grid" align="center" nowrap><select name="rank'. $x++ .'">'.
						'		<option value="-1" '. $purge_selected .'>Purge</option>'.
						'		<option value="0" '. $none_selected .'></option>'.
						'		<option value="1" '. $keep_selected .'>Keep</option>'.
						'	</select></td>'.
						'</tr>';
					}
				?>
		</table><br />
		<div align="center">
			<!--input type="button" class="inputButton" name="btnAdd" value="Add Event" onClick="add()"-->
			<input type="button" class="inputButton" name="btnAdd" value="Refresh" onClick="location.href = '/admin/sched/sync-evdb.php?return'">
			&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="button" class="inputButton" name="btnAdd" value="Add Event" onClick="void window.open('http://eventful.com/events/new')">
			&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="submit" value="Submit Rankings" class="inputButton" />
		</div>
	</form>

	<?include(BASE_DIR .'/includes/body_footer.php');?>
</body>
</html>
