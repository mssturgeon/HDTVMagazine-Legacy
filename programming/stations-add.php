<?
	require('../global.php');
	if ($user->data[user_id] == '') prompt_login(PHP_SELF);

	$user_id = $user->data[user_id];
	if (access(ACCESS_ADMIN)) {
		$user_id = isset($_GET[user_id]) ? $_GET[user_id] : $_POST[user_id];
	}

	$action = isset($_POST[action]) ? $_POST[action] : '';
	if ($action == 'save') {
#		$qry = "INSERT IGNORE INTO join_user_station (user_id, tf_station_num, us_key, label, channel, priority) VALUES ";
		$qry = "INSERT IGNORE INTO join_user_station (user_id, tf_station_num, label, channel, priority) VALUES ";
		for ($x=0; $x<count($_POST[num]); $x++) {
#			$key = $_POST[user_id] .'-'. $_POST[num][$x];

			$subQry = "SELECT tf_station_name name, tf_major_channel_number maj_channel, tf_minor_channel_number min_channel".
			" FROM tms_dgstatrec s LEFT JOIN tms_dgpsiprec p ON s.tf_station_num = p.tf_station_num".
			" WHERE s.tf_station_num = ". $_POST[num][$x];
			$result = mQuery($subQry);
			$subRow = mysql_fetch_array($result);
			$channel = '';
			if ($subRow[maj_channel] != '') $channel = $subRow[maj_channel] .'.'. $subRow[min_channel];

#			$qry .= " (". $_POST[user_id] .",". $_POST[num][$x] .", '". $key ."', '". addslashes($subRow[name]) ."', '". $channel ."', 999),";
			$qry .= " (". $_POST[user_id] .",". $_POST[num][$x] .", '". addslashes($subRow[name]) ."', '". $channel ."', 999),";
		}
		// Since the last character of $qry will always be a comma, we need to strip it
		$qry = substr($qry, 0, strlen($qry)-1);
		$result = mQuery($qry);
		echo "Inserted ". mysql_affected_rows($link_updated) ." records<br>";
		js_close_reload();
		exit;
	}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>HDTV Magazine - Add Broadcast Stations/Networks</title>
	<?require(BASE_DIR .'/includes/common_header.php');?>
	<script language="javascript" type="text/javascript">
		function validate() {
			with (document.frm) {
				s = tf_station_name.value + tf_station_affil.value + tf_dma_name.value;
				if (s.length == 0) {
					alert('Please enter at least one search term.');
					return false;
				}
			}
			return true;
		}

		function validateAdd() {
			obj = document.getElementsByName('num[]');
			for (x=0; x<obj.length; x++) {
				if (obj[x].checked) {
					return true;
				}
			}
	  		alert('You have no stations selected. Please select at least one station before clicking \'Add Selected\'.');
	  		return false;
		}

		function selectAll(obj, n) {
			o = document.getElementsByName(n);
			for (x=0; x<o.length; x++) {
				o[x].checked = obj.checked;
				highlight(o[x].value);
			}
		}

		function init() {
			window.focus();
			frm.tf_station_name.focus()
		}
	</script>
</head>
<body onload="init()" style="margin:10px 50px">
	<?
		require(BASE_DIR .'/includes/tracker.php');
	?>

	<form name="frm" action="<?=PHP_SELF?>" onSubmit="return validate()" method="post">
		<input type="hidden" name="action" value="search">
		<input type="hidden" name="user_id" value="<?=$user_id?>">
		<br />
		<table class="type1b" cellpadding="2" cellspacing="0">
			<tr>
				<td class="type1b_header" colspan="2">Station Search</td>
			</tr><tr>
				<td class="inputLabel" width="100">Name/Call Sign:</td>
				<td><input type="text" name="tf_station_name" class="inputText" value="<?=@$_POST[tf_station_name]?>"></td>
			</tr><tr>
				<td class="inputLabel">Affiliate:</td>
				<td><?=getSelectBox("SELECT DISTINCT tf_station_affil, tf_station_affil FROM tms_dgstatrec ORDER BY tf_station_affil", "tf_station_affil",  @$_POST[tf_station_affil], "")?></td>
			</tr><tr>
				<td class="inputLabel">Market:</td>
				<td><?=getSelectBox("SELECT DISTINCT tf_dma_name, tf_dma_name FROM tms_dgstatrec WHERE tf_dma_name <> '' ORDER BY tf_dma_name", 'tf_dma_name', @$_POST[tf_dma_name], '')?> </td>
			</tr><tr>
				<td colspan="2" align="right">
					(<a href="#hints">Search hints</a>) <input type="submit" name="btnSearch" value="Search" class="inputButton">
					<input type="button" name="btnClose" value="Cancel" class="inputButton" onClick="window.close()">
				</td>
			</tr>
		</form>
		</table>

		<?if ($action == 'search') {
			// Build where clause
			$where = "";
			if ($_POST[tf_station_name] != "") {
				$where .= " (tf_station_name LIKE '%". $_POST[tf_station_name] ."%' OR tf_station_call_sign LIKE '%". $_POST[tf_station_name] ."%') AND";
			}
			if ($_POST[tf_station_affil] != "") {
				$where .= " tf_station_affil LIKE '%". $_POST[tf_station_affil] ."%' AND";
			}
			if ($_POST[tf_dma_name] != "") {
				$where .= " tf_dma_name = '". $_POST[tf_dma_name] ."' AND";
			}
			// Trim WHERE clause since it always ends in 'AND'
			$where = substr($where, 0, strlen($where)-4);

			$qry = "SELECT t.tf_station_num num, tf_station_name name, tf_station_affil affil, tf_dma_name, tf_major_channel_number maj_channel, tf_minor_channel_number min_channel".
			" FROM tms_dgstatrec t LEFT JOIN tms_dgpsiprec p".
			" ON t.tf_station_num = p.tf_station_num".
			" WHERE $where ";
			$result = mQuery($qry);
		?>

		<form name="frmAddStations" action="<?=PHP_SELF?>" method="post" onSubmit="return validateAdd();">
			<input type="hidden" name="action" value="save">
			<input type="hidden" name="user_id" value="<?=$user_id?>">

			<br />
			<div><h2>Search Results</h2></div>
			<table class="type1b" cellpadding="2" cellspacing="0">
				<tr><td>
					<table class="type1b" cellspacing="0" align="center">
						<tr>
							<td class="type1b_header"><b>Name</b></td>
							<td class="type1b_header"><b>Affiliate</b></td>
							<td class="type1b_header" align="right" style="padding-right:20px"><b>Channel</b></td>
							<td class="type1b_header"><b>Market</b></td>
						</tr><?
							if (mysql_num_rows($result) == 0) {
								echo '<tr><td colspan="6">No Stations Found.</td></tr>';
							} else {
								while ($row = mysql_fetch_array($result)) {
									$channel = '';
									if ($row[maj_channel] != '') $channel = $row[maj_channel] .'.'. $row[min_channel];

									echo '<tr id="r'. $row[num] .'">'.
									'	<td>'.
									'		<input type="checkbox" id="c'. $row[num] .'" name="num[]" value="'. $row[num] .'" onClick="highlight(\''. $row[num] .'\')">'. $row[name] .
									'		<input type="hidden" name="name[]" value="'. $row[name] .'">'.
									'		<input type="hidden" name="channel[]" value="'. $channel .'">'.
									'	</td>'.
									'	<td>'. $row[affil] .'</td>'.
									'	<td align="right" style="padding-right:20px">'. $channel .'</td>'.
									'	<td>'. $row[tf_dma_name] .'</td>'.
									'</tr>' ."\n";
								}
							}
						?>
					</table>
				</td></tr>
				<tr><td><table width="100%" cellspacing="0">
					<tr>
						<td><input type="checkbox" name="selectBox" onClick="selectAll(this, 'num[]')">Select All</td>
						<td align="right">
							<input type="submit" name="btnAdd" value="&nbsp;&nbsp;&nbsp;Add Selected&nbsp;&nbsp;&nbsp;" class="inputButton">
							<input type="button" name="btnCancel" value="Cancel" class="inputButton" onClick="window.close()">
						</td>
					</tr>
				</td></tr></table>
			</table>
		</form>

	<?} else {
		$top = 10;
		// Show most popular
		$qry = "SELECT stat.tf_station_num, tf_station_name, tf_station_affil, count(*) count".
		" FROM join_user_station j, tms_dgstatrec stat".
		" WHERE stat.tf_station_num = j.tf_station_num and ota = 0".
		" GROUP BY stat.tf_station_num".
		" ORDER BY count DESC, tf_station_name LIMIT $top";
		$result = mQuery($qry);
		?>
		<form name="frmAddStations" action="<?=PHP_SELF?>" method="post" onSubmit="return validateAdd();">
			<input type="hidden" name="action" value="save">
			<input type="hidden" name="user_id" value="<?=$user_id?>">

			<br />
			<h2>Most Popular Stations (Top <?=$top?>)</h2>
			<table class="type1b" cellpadding="2" cellspacing="0">
				<tr>
					<td class="type1b_header"><b>Name</b></td>
					<td class="type1b_header"><b>Affiliate</b></td>
					<td class="type1b_header" align="right"><b>Users</b></td>
				</tr>
				<?
					while ($row = mysql_fetch_array($result)) {
						echo '<tr id="r'. $row[tf_station_num] .'">'.
						'	<td class="type1b">'.
						'		<input type="checkbox" id="c'. $row[tf_station_num] .'" name="num[]" value="'. $row[tf_station_num] .'" onClick="highlight(\''. $row[tf_station_num] .'\')">'. $row[tf_station_name] .
						'		<input type="hidden" name="name[]" value="'. $row[tf_station_name] .'">'.
						'		<input type="hidden" name="channel[]" value="">'.
						'	</td>'.
						'	<td class="type1b">'. $row[tf_station_affil] .'</td>'.
						'	<td class="type1b" align="right">'. $row[count] .'</td>'.
						'</tr>' ."\n";
					}
				?>
				<tr>
					<td><input type="checkbox" name="selectBox" onClick="selectAll(this, 'num[]')">Select All</td>
					<td align="right" colspan="2">
						<input type="submit" name="btnAdd" value="&nbsp;&nbsp;&nbsp;Add Selected&nbsp;&nbsp;&nbsp;" class="inputButton">
						<input type="button" name="btnCancel" value="Cancel" class="inputButton" onClick="window.close()">
					</td>
  				</tr>
			</table>
		</form>
	<?}?>

	<br />
	<a name="hints">
	<h2>Search Hints</h2>
	<table class="type1b" cellpadding="2" cellspacing="0">
		<tr><td>Some helpful hints on searching:</td></tr>
		<tr><td>- If you know your local broadcast networks call sign (i.e. WNBC, WCBS, etc), you can enter those in the Name/Call Sign field.</td></tr>
		<tr><td>- If you don't know the call sign, try searching by the Market Area from which the station broadcasts.</td></tr>
		<tr><td>- If your searching for premium channels, search by Affiliate and select "PAY".</td></tr>
		<tr><td>- If your looking for DBS channels (HDNet, DHD, etc), search by Affiliate and select "Satellite".</td></tr>
		<tr><td>- ESPNHD is listed under "Sports Satellite".</td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr><td>IMPORTANT NOTE: The more fields you fill in, the more restrictive your search will be. My advice is to start out with a single criteria and add more if the results are too many.</td></tr>
	</table>

</body>
</html>
