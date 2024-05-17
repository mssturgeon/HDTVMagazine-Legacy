<?
	require('../global.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>HDTV Magazine - Investing</title>
	<?require(BASE_DIR .'/includes/common_header.php');?>
</head>
<body>
	<?
		include(BASE_DIR .'/includes/tracker.php');
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
	?>
	
	<h1>Investing in HDTV</h1>
	<p>
		This is a very new page, just launched 7/1.  We will be adding many more companies here in the coming days and weeks.
	</p>

	<table class="type1b" cellpadding="0" cellspacing="0" style="width:100%">
		<tr>
			<td class="type1b_header">Company</td>
			<td class="type1b_header">Symbol</td>
			<td class="type1b_header" style="text-align:right">Last Trade</td>
			<td class="type1b_header" style="text-align:right">Date</td>
			<td class="type1b_header" style="text-align:right">Time</td>
			<td class="type1b_header" style="text-align:right">$ Change</td>
			<td class="type1b_header" style="text-align:right">% Change</td>
			<td class="type1b_header" style="text-align:right">Open</td>
			<td class="type1b_header" style="text-align:right">High</td>
			<td class="type1b_header" style="text-align:right">Low</td>
			<td class="type1b_header" style="text-align:right">Volume</td>
		</tr>
	<?
		// Run 2 queries so we can get the most recent data
		$result = mQuery("SELECT MAX(date) max_date FROM quotes");
		$row = mysql_fetch_assoc($result);
		$result = mQuery("SELECT q.*, alias, url, flags, (price_change/trade) as perc FROM quotes q, tbl_companies c WHERE c.symbol = q.symbol AND date = '{$row['max_date']}' ORDER BY perc DESC");
//					$result = mQuery("SELECT * FROM quotes");
		while ($row = mysql_fetch_assoc($result)) {
			if ($row['price_change'] < 0) {
				$color = '#A00000';
				$img = '/images/arrow_down.gif';
			} else {
				$color = '#00A000';
				$img = '/images/arrow_up.gif';
			}
			$perc = ($row['price_change'] / $row['trade']) * 100;
			$link = ($row['url'] != '') ? '<a target="_blank" onmouseover="return link_over(this)" onmouseout="return link_out(this)" href="/cgi-bin/ntlinktrack.cgi?'. $row['url'] .'">'. $row['alias'] .'</a>' : $row['alias'];
			$link_products = ($row['flags'] & COMP_FLAG_EQUIP) ? '[<a href="'. URL_EQUIPMENT_MANUFACTURER .'?man='. rawurlencode($row['alias']) .'">products</a>]' : '';
			echo '<tr>'.
			'	<td class="grid">'.
					$link .
			'		[<a href="'. URL_NEWS_ARCHIVE .'?action=search&terms='. rawurlencode($row['alias']) .'">news</a>] '.
					$link_products .
			'	</td>'.
			'	<td class="grid">'. $row['symbol'] .'</td>'.
			'	<td class="grid" style="text-align:right">$'. number_format($row['trade'], 2) .'</td>'.
			'	<td class="grid" style="text-align:right">'. $row['date'] .'</td>'.
			'	<td class="grid" style="text-align:right">'. $row['time'] .'</td>'.
			'	<td class="grid" style="text-align:right;color:'. $color .'">'. number_format($row['price_change'], 2) .'&nbsp;<img src="'. $img .'"></td>'.
			'	<td class="grid" style="text-align:right;color:'. $color .'">'. number_format($perc, 2) .'</td>'.
			'	<td class="grid" style="text-align:right">$'. number_format($row['open'], 2) .'</td>'.
			'	<td class="grid" style="text-align:right">$'. number_format($row['high'], 2) .'</td>'.
			'	<td class="grid" style="text-align:right">$'. number_format($row['low'], 2) .'</td>'.
			'	<td class="grid" style="text-align:right">'. number_format($row['volume']) .'</td>'.
			'</tr>';
		}
	?></table>
				
	<?include(BASE_DIR .'/includes/body_footer.php');?>
</body>
</html>
