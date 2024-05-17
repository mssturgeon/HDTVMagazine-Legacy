<?
	require('../global.php');
	$debug = isset($_GET[debug]);

	// Get Largest TV
	$result = mQuery("SELECT alias, model_name, size, display_name, pg_lowest_price FROM tbl_models m, tbl_companies c WHERE m.man_id = c.id AND m.type IN (2,3,4,5,6,7) ORDER BY size DESC LIMIT 1");
	$row = mysql_fetch_assoc($result);
	$largest = $row['size'] .'" - <a href="'. URL_EQUIPMENT_MODEL .'?man='. $row['alias'] .'&model='. $row['model_name'] .'">'. $row['alias'] .'  '. $row['display_name'] .'</a> ($'. number_format($row['pg_lowest_price'], 2) .')';

	// Get Most Expensive TV
	$result = mQuery("SELECT alias, model_name, display_name, pg_lowest_price FROM tbl_models m, tbl_companies c WHERE m.man_id = c.id AND m.type IN (2,3,4,5,6,7) ORDER BY pg_lowest_price DESC LIMIT 1");
	$row = mysql_fetch_assoc($result);
	$expensive = '<a href="'. URL_EQUIPMENT_MODEL .'?man='. $row['alias'] .'&model='. $row['model_name'] .'">'. $row['alias'] .'  '. $row['display_name'] .'</a> ($'. number_format($row['pg_lowest_price'], 2) .')';

	// Get Most Economical TV
	$sql = "
	SELECT alias, model_name, size, display_name, pg_lowest_price, pg_lowest_price/size ppi
	FROM tbl_models m, tbl_companies c
	WHERE pg_lowest_price > 0
		AND size > 0
		AND m.man_id = c.id
		AND m.type IN (2,3,4,5,6,7)
		AND projection <> ". MODEL_PROJ_TYPE_FRONT ."
		ORDER BY ppi LIMIT 1";
#		echo $sql;
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$economical = '<a href="'. URL_EQUIPMENT_MODEL .'?man='. $row['alias'] .'&model='. $row['model_name'] .'">'. $row['alias'] .'  '. $row['display_name'] .'</a> ($'. number_format($row['pg_lowest_price'], 2) .', '. $row['size'] .'")';

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - The HDTV Database</title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header-4.php');
	?>

	<h1>The HDTV Database</h1>
	<div id="eq-mrec"><? include(BASE_DIR .'/ads/mrectangle.php');?></div>

	This page lists the manufacturers of various types of HDTV hardware (TV's, Blu-ray players, etc.) and software (Blu-ray, Xbox 360, etc.) products.
	<br>
	Here are a few other ways to view our database of HDTV equipment:<br>
	- <a href="<?=URL_EQUIPMENT_SEARCH?>">Search HDTV's</a><br>
	- <a href="<?=URL_EQUIPMENT_TYPE?>">HDTV's by Type</a> (CRT, LCD, Plasma, etc.)<br>
	<!--a href="<?=URL_EQUIPMENT_SIZE?>">HDTV's by Size</a><br-->
	More coming soon ...<br>
	<br>
	<h2>Database Highlights</h2>
	<table class="type1b" cellpadding="0" cellspacing="0">
		<tr>
			<td class="type1b_header">Largest Set:</td>
			<td class="type1b"><?=$largest?></td>
		</tr><tr>
			<td class="type1b_header">Most Expensive:</td>
			<td class="type1b"><?=$expensive?></td>
		</tr><tr>
			<td class="type1b_header">Best Price per Inch:</td>
			<td class="type1b"><?=$economical?></td>
		</tr><tr>
	</table>
	<br clear="all" />

	<div id="eq-sky"><? include(BASE_DIR .'/ads/skyscraper.php');?></div>

	<div syle="">
	<h2>HDTV's by Manufacturer</h2>
	<table class="bare" cellpadding="0" cellspacing="0" style="">
		<tr><?
			$sql = "
			SELECT alias, count(*) count
			FROM tbl_companies c, tbl_models m
			WHERE m.edited = 1
				AND alias <> ''
				AND m.man_id = c.id
				AND m.type > 1
				AND flags & ". COMP_FLAG_EQUIP ."
			GROUP BY alias ORDER BY alias";
			if ($debug) echo "$sql<br />";
			$result = mQuery($sql);
	//		$rows = 15;
	//		$columns = ceil(mysql_num_rows($result) / $rows);
			$columns = 4;
			$rows = ceil(mysql_num_rows($result) / $columns);
			$c = 1;
			$r = 1;
	  		while ($row = mysql_fetch_assoc($result)) {
				$font = ($row['count'] > 10) ? 'font-weight:bold;' : '';
				$eq[$c][$r] = '<td style="width:'. round(100/$columns) .'%;padding:2px 4px" nowrap><a href="'. URL_EQUIPMENT_MANUFACTURER .'?man='. rawurlencode($row['alias']) .'" style="'. $font .'">'. $row['alias'] .'</a> ('. $row['count'] .')</td>'."\n";
	  			if ($c++ == $rows) {
					$r++;
					$c %= $rows;
				}
			}
			foreach ($eq as $c => $r_eq) {
				foreach ($r_eq as $r => $value) {
					echo $value;
				}
				echo '</tr><tr>';
			}

			/*
			$last_letter = '';
			while ($row = mysql_fetch_assoc($result)) {
				if ($last_letter == '') {
					echo '	<td style="width:'. round(100/$columns) .'%;vertical-align:top;padding-bottom:20px">';
				} elseif ($last_letter == strtolower($row['alias']{0})) {
					echo '<br>';
				} else {
					echo '</td>';
					if (++$x % $columns == 0) echo '</tr><tr>';
					echo '	<td style="width:'. round(100/$columns) .'%;vertical-align:top;padding-bottom:20px">';
				}
				echo '		<a href="'. URL_EQUIPMENT_MANUFACTURER .'?man='. $row['alias'] .'">'. $row['alias'] .'</a> ('. $row['count'] .')';

				$last_letter = strtolower($row['alias']{0});
			}
			*/
		?></td></tr>
	</table></div>

	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
</div></body>
</html>
