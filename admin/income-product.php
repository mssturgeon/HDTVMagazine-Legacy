<?
	require('../global.php');
	if (!access(ACCESS_ADMIN_ANY)) access_denied();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>HDTV Magazine - Sales by Product</title>
	<?require(BASE_DIR .'/includes/common_header.php');?>
</head>
<body>
	<?
		include(BASE_DIR .'/includes/tracker.php');
		include(BASE_DIR .'/includes/body_header.php');
		
		$today = getdate(time() + 28800 + $_SESSION['time_zone_offset']); // Adjust from PT to GMT, then back to local time
		$bom = getdate(gmmktime(0, 0, 0, $today['mon'], 1));
		$eom = getdate(gmmktime(0, 0, 0, $today['mon']+1, 1));
		
		// Get Totals
		$qry = "SELECT DATE_FORMAT(FROM_UNIXTIME(timestamp - {$_SESSION['time_zone_offset']}), '%Y-%m-%d') d, item_number, item_name, SUM(mc_gross) gross, SUM(mc_fee) fee".
		"	FROM paypal_ipn".
		"	WHERE item_number > 0".
		"	GROUP BY d, item_number, item_name".
		"	ORDER BY d DESC";
		$result = mQuery($qry);
		
		$gross_item = array();
		$fee_item = array();
		$item = array();
		while ($row = mysql_fetch_assoc($result)) {
			$gross_day[$row['d']] += $row['gross'];
			$gross_item[$row['item_number']] += $row['gross'];
			$gross_total += $row['gross'];

			$fee_day[$row['d']] += $row['fee'];
			$fee_item[$row['item_number']] += $row['fee'];
			$fee_total += $row['fee'];
			
			$item[$row['item_number']] = $row['item_name'];
			$gross[$row['d']][$row['item_number']] = $row['gross'];
			$fee[$row['d']][$row['item_number']] = $row['fee'];
		}
	?>
	
				<div align="center"><h1>Profit Table</h1>
					<table class="type1b" style="width:400px;">
						<tr><td class="type1b_header">#</td><td class="type1b_header">Name</td></tr>
						<?
							$parity = 'odd';
							ksort($item);
							foreach ($item as $x => $item_name) {
								echo '<tr class="'. $parity .'Row"><td class="type1b">'. $x .'</td><td class="type1b">'. $item_name .'</td></tr>';
								$parity = ($parity == 'odd') ? 'even' : 'odd';
							}
						?>
					</table><br>

					<table class="type1b" cellpadding="2" cellspacing="0" style="">
						<tr>
							<td class="type1b_header" align="right">Date</td>
							<td class="type1b_header" align="right">Item #1</td>
							<td class="type1b_header" align="right">Item #2</td>
							<td class="type1b_header" align="right">Item #3</td>
							<td class="type1b_header" align="right">Item #4</td>
							<td class="type1b_header" align="right">Item #5</td>
							<td class="type1b_header" align="right">Total</td>
						</tr>
						<?
							$parity = 'odd';
							foreach ($gross as $date => $b) {
								echo '<tr class="'. $parity .'Row">'.
								'	<td align="right" class="type1b">'. $date .'</td>';
								foreach ($item as $x => $item_name) {
									echo '<td align="right" class="type1b">'. sprintf('$%01.2f', $gross[$date][$x] - $fee[$date][$x]) .'</td>';
								}
								echo '<td align="right" class="type1b">'. sprintf('$%01.2f', $gross_day[$date] - $fee_day[$date]) .'</td>';
								echo '</tr>';
								$parity = ($parity == 'odd') ? 'even' : 'odd';
							}
						?>
						<tr>
							<td class="type1b_header" align="right">Total</td>
							<?
								foreach ($item as $x => $item_name) {
									echo '<td class="type1b_header" align="right">'. sprintf('$%01.2f', $gross_item[$x] - $fee_item[$x]) .'</td>';
								}
							?>
							<td class="type1b_header" align="right"><?=sprintf('$%01.2f', $gross_total - $fee_total)?></td>
						</tr>
					</table>
					
				</div>

	<?include(BASE_DIR .'/includes/body_footer.php');?>

</body>
</html>
