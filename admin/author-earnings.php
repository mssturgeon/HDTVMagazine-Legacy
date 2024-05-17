<?
	require('../global.php');
	if (!access(ACCESS_AUTHOR)) access_denied();

/*
	$year = date('Y');
	$month = date('n') - 1;
*/

	# Get author info
	if (access(ACCESS_ADMIN)) {
#		2169 # Rodolfo
#		4603 # HT Guys
#		52687 # Alfred Poor
#		5764 # Pete Putman

		$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : '52687';
		$sql = "SELECT m.author_name, a.* FROM mt_author m, aux_author a WHERE m.author_id = a.author_id AND user_id = '$user_id'";
	} else {
		$sql = "SELECT m.author_name, a.* FROM mt_author m, aux_author a WHERE m.author_id = a.author_id AND user_id = ". $user->data['user_id'];
	}
	$res_author = mQuery($sql);
	$row_author = mysql_fetch_assoc($res_author);
	$zero = '<td class="grid" style="text-align:right;">0</td>';
	$na = '<td class="grid" style="text-align:right;">N/A</td>';

	# Get author page views and display header
	$sql = "
	SELECT year, month, pv, last_updated
	FROM author_earnings
	WHERE channel = '$row_author[channel]'
	ORDER BY year DESC, month DESC
	LIMIT 12";
	$result = mQuery($sql);
	$paydate = new DateTime();
	while ($row = mysql_fetch_assoc($result)) {
		$paydate->setDate($row['year'], $row['month'], 1);
		$paydate->modify('+4 month	');

		$chart_labels["$row[year]$row[month]"] = $row['month'] .'/'. substr($row['year'], -2);
		$chart_authorpv["$row[year]$row[month]"] = $row['pv'];

		$table_head["$row[year]$row[month]"] = '<td class="type1b_header" style="text-align:center;">'. $row['month'] .'/'. substr($row['year'], -2) .'</td>';
#		$table_paydate["$row[year]$row[month]"] = '<td class="grid" style="text-align:center;">'. (($row['month']+2 % 12) + 1) .'/1/'. substr($row['year'], -2) .'</td>';
		$table_paydate["$row[year]$row[month]"] = '<td class="grid" style="text-align:center;">'. $paydate->format('n/j/y') .'</td>';
		$author_total["$row[year]$row[month]"] = $row['pv'];
		$table_authorpv["$row[year]$row[month]"] = '<td class="grid" style="text-align:right;">'. number_format($row['pv']) .'</td>';
		$dataXML_dsauthorviews["$row[year]$row[month]"] = "<set value='$row[pv]' />";
		$dataXML_categories["$row[year]$row[month]"] = "<category name='$row[month]/". substr($row['year'], -2) ."' />";
	}

	# Display HDTV Magazine Totals
	$sql = "
	SELECT year, month, SUM(pv) as pv
	FROM author_earnings
	GROUP BY year DESC, month DESC
	LIMIT 12";
	$result = mQuery($sql);
	$m = 0;
	while ($row = mysql_fetch_assoc($result)) {
		$table_sitepv["$row[year]$row[month]"] = '<td class="grid" style="text-align:right;">'. number_format($row['pv']) .'</td>';
		$site_total["$row[year]$row[month]"] = $row['pv'];
		$perc["$row[year]$row[month]"] = number_format($author_total["$row[year]$row[month]"]/$site_total["$row[year]$row[month]"]*100, 2);
		$table_authorperc["$row[year]$row[month]"] = '<td class="grid" style="text-align:right;font-weight:bold;">'. $perc["$row[year]$row[month]"] .'%</td>';
		$dataXML_dsmagviews["$row[year]$row[month]"] = "<set value='$row[pv]' />";
	}

	# Get # of pieces published
	$sql = "
		SELECT COUNT(*) as num, YEAR(entry_created_on) as year, MONTH(entry_created_on) as month
		FROM mt_entry
		WHERE entry_author_id = $row_author[author_id]
		GROUP BY year DESC, month DESC
		LIMIT 12";
	$result = mQuery($sql);
	while ($row = mysql_fetch_assoc($result)) {
		$chart_pieces["$row[year]$row[month]"] = $row['num'];

		$table_pieces["$row[year]$row[month]"] = '<td class="grid" style="text-align:right;">'. $row['num'] .'</td>';
		$dataXML_dsnumpieces["$row[year]$row[month]"] = "<set value='$row[num]' />";
	}

	# Get monthly earnings
	$sql = "SELECT year, month, (amount_fm + amount_google) amount FROM earnings ORDER BY year DESC, month DESC LIMIT 12";
	$result = mQuery($sql);
	while ($row = mysql_fetch_assoc($result)) {
		$table_authorearnings["$row[year]$row[month]"] = '<td class="grid" style="color:#008000; font-weight:bold; text-align:right;">$'. number_format($row['amount'] * $perc["$row[year]$row[month]"] / 100 * $row_author[revshare], 2) .'</td>';
	}

	if (isset($_GET['xml'])) {
		echo "<graph caption='Page Performance for $row_author[author_name]' PYAxisName='Page Views' SYAxisName='Pieces Published'
			hovercapbg='CCCCCC' hovercapborder='003F87' formatNumberScale='1' decimalPrecision='0' animation='1'
			numdivlines='4' numVdivlines='0' yaxisminvalue='0' yaxismaxvalue='100000' rotateNames='0' chartRightMargin='5'>\n";

			echo "<dataset seriesname='HDTV Magazine ad views' parentYAxis='P' color='003f87' showValue='1' alpha='80' showAnchors='0'>\n";
			foreach ($author_total as $index => $total) {
				$temp = (array_key_exists($index, $dataXML_dsmagviews)) ? $dataXML_dsmagviews[$index] . $temp : '';
			}
			echo "$temp</dataset>\n";$temp = '';

			echo "<categories>\n";
			foreach ($author_total as $index => $total) {
				$temp = (array_key_exists($index, $dataXML_categories)) ? $dataXML_categories[$index] . $temp : '';
			}
			echo "$temp</categories>\n";$temp = '';

			echo "<dataset seriesname='# of pieces' parentYAxis='S' anchorRadius='4' anchorSides='10' anchorBorderColor='0099CC' color='0099CC' lineThickness='4' showValue='1' alpha='80'>\n";
			foreach ($author_total as $index => $total) {
				$temp = (array_key_exists($index, $dataXML_dsnumpieces)) ? $dataXML_dsnumpieces[$index] . $temp : "<set value='0' />$temp";
			}
			echo "$temp</dataset>\n";$temp = '';

			echo "<dataset seriesname='Your ad views' parentYAxis='P' color='995905' showValue='1' alpha='80' showAnchors='0'>\n";
			foreach ($author_total as $index => $total) {
				$temp = (array_key_exists($index, $dataXML_dsauthorviews)) ? $dataXML_dsauthorviews[$index] . $temp: '';
			}
			echo "$temp</dataset>\n";$temp = '';

			echo "</graph>";
		exit;
	}

	foreach ($chart_labels as $index => $label) {
		$chart_data[] = "['$chart_labels[$index]', $chart_authorpv[$index], $chart_pieces[$index]]";
	}
	$chart_data = array_reverse($chart_data);

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - Author Earnings</title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
	<script type="text/javascript" src="https://www.google.com/jsapi"></script>
	<script type="text/javascript">google.load('visualization', '1', {packages: ['corechart']});</script>
	<script type="text/javascript">
		function init() {
			drawVisualization();
		}

		function drawVisualization() {
			var data = google.visualization.arrayToDataTable([
				['Month', 'Author Ad Views', 'Pieces Published'],
				<?=implode(",\n", $chart_data);?>
				]);

			// Create and draw the visualization.
			var comboChart = new google.visualization.ComboChart(document.getElementById('chart_div'));
			comboChart.draw(data, {
				title : 'Page Performance for <?=$row_author['author_name']?>',
				vAxis: {title: "Page Views"},
				hAxis: {title: "Month"},
				seriesType: "bars",
				series: {1: {type: "line"}}
			});
		}
	</script>
</head>
<body id="body_container" onload="init()">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<p>
		This page is intended to give authors and contributors to HDTV Magazine an idea of how well their contributions are doing from month to month.
	</p><p>
		The first table below shows <b>Page View Information</b>. This displays the number of <b>page views</b> received for a given month both for
		the HDTV Magazine site as a whole, and for the authors content specifically. This is further broken down into an <b>page view percentage</b>.
		This is a key indicator for the author on how well their content is doing relative to the rest of the site. Also included in this table is a
		<b># of pieces published</b> figure.
	</p><p>
		The second table shows <b>Earnings Information</b> for the author. In this table, we've provided the <b>page view percentage</b> again along
		with the corresponding <b>amount earned</b> and <b>approximate payment date</b> of that amount. We are being payed monthly by our ad agency
		at a 75-day lag.
	</p><p>
		Lastly, there is a graph at the bottom which depicts Page View Information visually for the author.
	</p>

	<h2>Page View Information</h2>
	<table class="type1b" cellspacing=0 cellpadding=0>
		<tr><td class="type1b_header">&nbsp;</td><?
			foreach ($author_total as $index => $total) {
				$temp = $table_head[$index] . $temp;
			}
			echo $temp;$temp = '';
		?></tr><tr><td class="type1b_header"># of pieces published</td><?
			foreach ($author_total as $index => $total) {
				$temp = (array_key_exists($index, $table_pieces)) ? $table_pieces[$index] . $temp : $zero . $temp;
			}
			echo $temp;$temp = '';
		?></tr><tr><td class="type1b_header">HDTV Magazine page views</td><?
			foreach ($author_total as $index => $total) {
				$temp = (array_key_exists($index, $table_sitepv)) ? $table_sitepv[$index] . $temp : $zero . $temp;
			}
			echo $temp;$temp = '';
		?></tr><tr><td class="type1b_header">Your page views</td><?
			foreach ($author_total as $index => $total) {
				$temp = (array_key_exists($index, $table_authorpv)) ? $table_authorpv[$index] . $temp : $zero . $temp;
			}
			echo $temp;$temp = '';
		?></tr><tr><td class="type1b_header">Your page view %</td><?
			foreach ($author_total as $index => $total) {
				$temp = (array_key_exists($index, $table_authorperc)) ? $table_authorperc[$index] . $temp : $zero . $temp;
			}
			echo $temp;$temp = '';
		?></tr>
	</table><br />

	<h2>Earnings Information</h2>
	<table class="type1b" cellspacing=0 cellpadding=0>
		<tr><td class="type1b_header">&nbsp;</td><?
			foreach ($author_total as $index => $total) {
				$temp = $table_head[$index] . $temp;
			}
			echo $temp;$temp = '';
		?></tr><tr><td class="type1b_header">Your page view %</td><?
			foreach ($author_total as $index => $total) {
				$temp = (array_key_exists($index, $table_authorperc)) ? $table_authorperc[$index] . $temp : $zero . $temp;
			}
			echo $temp;$temp = '';
		?></tr><tr><td class="type1b_header">Amount Earned</td><?
			foreach ($author_total as $index => $total) {
				$temp = (array_key_exists($index, $table_authorearnings)) ? $table_authorearnings[$index] . $temp : $na . $temp;
			}
			echo $temp;$temp = '';
		?></tr>
		<tr><td class="type1b_header">Approx. payment date</td><?
			foreach ($author_total as $index => $total) {
				$temp = $table_paydate[$index] . $temp;
			}
			echo $temp;
		?></tr>
	</table><br />

	<!--div>
		<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"
			codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" id="FC_2_3_MSColumnLine_DY_3D">
			<param name=movie value="/charts/FC_2_3_MSColumnLine_DY_3D.swf">
			<param name="FlashVars" value="&dataURL=<?=PHP_SELF?>?xml">
			<param name="quality" value="high">
			<param name="bgcolor" value="#FFFFFF">
			<embed src="/charts/FC_2_3_MSColumnLine_DY_3D.swf" FlashVars="&dataURL=<?=PHP_SELF?>?xml" quality="high" width="100%" height="420"
				bgcolor="#FFFFFF" name="FC_2_3_MSColumnLine_DY_3D" type="application/x-shockwave-flash"
				pluginspage="http://www.macromedia.com/go/getflashplayer"></embed>
		</object>
	</div-->

	<div id="chart_div" style="width: 700px; height: 400px;"></div>

	<p style="margin-bottom:50px"/>
	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
</body>
</html>
