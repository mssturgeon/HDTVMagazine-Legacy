<?
	require('../global.php');
	if (!access(ACCESS_ADMIN)) prompt_login(PHP_SELF);

	$debug = isset($_GET['debug']) ? 1 : 0;

	$action = isset($_POST['action']) ? $_POST['action'] : '';
	if ($action == 'submit') {
		$debug = ($_POST['debug'] == "1") ? true : false;
		header('Content-Type: text/plain');

		$sql = "
		SELECT author_name, channel, channel_name
		FROM mt_author m LEFT JOIN aux_author a ON m.author_id = a.author_id
		WHERE channel <> ''
			AND channel_name <> ''";
		$result = mQuery($sql);

		# Must manually map the default channel so that totals add up correctly
		$channel['Main - Links'] = 9752039117;
		while ($row = mysql_fetch_assoc($result)) {
			$channel[$row['channel_name']] = $row['channel'];
		}
		if ($debug) print_r($channel);

		foreach (split("\n", $_POST['csv']) as $line) {
			$row = split("\t", $line);
			$channel_id = $channel[$row[0]];
#			$pv = str_replace(',', '', $row[1]);
			$pv = str_replace(',', '', $row[2]);
			$year = $_POST['year'];
			$month = $_POST['month'];

			if ($channel_id != '') {
				$sql = "REPLACE INTO author_earnings (channel, year, month, pv, last_updated) VALUES ('$channel_id', $year, $month, $pv, CURDATE())";
				if ($debug) {echo "$sql\n";} else {mQuery($sql);}
			} else {if ($debug) echo "Skipping channel: $line\n";}
		}
		exit;
		//js_back('Email Updated.');
	}

	$year = date('Y');
	$month = date('n');

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - AdSense Import</title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
</head>
<body onload="document.frm.serp.focus()">
	<?
		include(BASE_DIR .'/includes/body_header-4.php');
	?>

	<p align="center">
		This page is used to import AdSense data for authors. To be used for <b>this month's</b> data only.
	</p>

	<div align="center" width="550">
	<form name="frm" action="<?=PHP_SELF?>" method="post">
		<input type="hidden" name="debug" value="<?=$debug?>">
		<input type="hidden" name="action" value="submit">
		<select name="year">
			<option value="<?=$year-1?>"><?=$year-1?></option>
			<option value="<?=$year?>" selected="selected"><?=$year?></option>
		</select>
		<select name="month"><?
			for ($x=1; $x<=12; $x++) {
				$selected = ($month-1 == $x) ? 'selected="selected"' : '';
				echo '<option value="'. $x .'" '. $selected .'>'. date('F', strtotime("2000-$x-1")) .'</option>';
			}
		?></select>
		<br />

		<textarea name="csv" id="csv" style="width:550px;height:450px;font-size:8pt;font-family:Monospace"></textarea>
		<br />
		<input type="submit" value="Submit" class="inputButton">
	</form>
	</div>

	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
</body>
</html>
