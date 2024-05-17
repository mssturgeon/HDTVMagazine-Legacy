<?
	require('../global.php');
	if (!access(ACCESS_ADMIN_ANY)) access_denied();
	header("Cache-Control: no-cache, must-revalidate");
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

	$action = $_POST['action'];
	if ($action == 'submit') {
#		print_r($_POST);
		for ($x=0; $x<$_POST['number']; $x++) {
			if ($_POST["hide_$x"] == 1) {
				$sql = "
				UPDATE az_aux
				SET hide = 1
				WHERE ASIN IN (
					SELECT ASIN FROM az_attributes WHERE Manufacturer = '". addslashes($_POST["Manufacturer_$x"]) ."'
				)";
#				echo "$sql<br />";
				mQuery($sql);
			} else {
				if ($_POST["Manufacturer_$x"] <> '' && addslashes($_POST["ManufacturerAlias_$x"]) <> '') {
					$sql = "
					UPDATE az_aux
					SET ManufacturerAlias = '". addslashes($_POST["ManufacturerAlias_$x"]) ."'
					WHERE ASIN IN (
						SELECT ASIN FROM az_attributes WHERE Manufacturer = '". addslashes($_POST["Manufacturer_$x"]) ."'
					)";
#					echo "$sql<br />";
					mQuery($sql);
				}
			}
#			js_back();
		}
	}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>HDTV Magazine - HDTV Company Admin</title>
	<?require(BASE_DIR .'/includes/common_header.php');?>
</head>
<body>
	<?
		include(BASE_DIR .'/includes/body_header.php');
	?>

	<h1>Company Admin</h1>
	<p>
		This page permits alteration and addition of fields to the companies table. Mispellings from the table below
		 (imported from external sources) should all be "aliased" to the same (proper) company name.
	</p>

	<form method="POST" action="<?$_SERVER['PHP_SELF']?>">
		<input type="hidden" name="action" value="submit">
		<table class="type1b"><tr>
			<td class="type1b_header">Hide</td>
			<td class="type1b_header">Manufacturer</td>
			<td class="type1b_header"># Products</td>
			<td class="type1b_header">Type</td>
			<td class="type1b_header">Alias</td>
		</tr>
			<?
				$sql = "
				SELECT Manufacturer, type, count(*) num
				FROM az_attributes a, az_aux aux, az_main m
				WHERE a.ASIN = aux.ASIN
					AND a.ASIN = m.ASIN
					AND Manufacturer <> ''
					AND ManufacturerAlias = ''
					AND hide = 0
					AND date_updated > CURDATE() - INTERVAL {$admindata['amazon_update_window']} DAY
					GROUP BY Manufacturer, type
				ORDER BY num DESC, Manufacturer LIMIT 20";
				$result = mQuery($sql);
				$x = 0;
				echo '<input type="hidden" name="number" value="'. mysql_num_rows($result) .'">';
				while ($row = mysql_fetch_assoc($result)) {
					echo '<tr>'.
						'<td><input type="checkbox" name="hide_'. $x .'" value="1"></td>'.
						'<td class="grid">'.
							'<input type="hidden" name="Manufacturer_'. $x .'" value="'. htmlentities($row['Manufacturer']) .'">'.
							'<a target="_blank" href="/equipment/manufacturer.php?man='. rawurlencode($row['Manufacturer']) .'&sort=model">'. $row['Manufacturer'] .'</a></td>'.
						'<td class="grid">'. $row['num'] .'</td>'.
						'<td class="grid">'. $row['type'] .'</td>'.
						'<td class="grid"><input type="text" style="width:30em" name="ManufacturerAlias_'. $x .'" value="'. trim($row['Manufacturer']) .'"></td></tr>'."\n";
					$x++;
				}
			?>
		</table>

		<input type="submit" value="Submit">
	</form>
	<?include(BASE_DIR .'/includes/body_footer.php');?>
</body>
</html>
