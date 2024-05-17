<?
	require('../global.php');
	if (!access(ACCESS_ADMIN_ANY)) access_denied();

	if (isset($_POST[action])) {
		$sql = "REPLACE INTO earnings VALUES ($_POST[year], $_POST[month], $_POST[amount_fm], $_POST[amount_google])";
		mQuery($sql);
	}

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - Earnings</title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
</head>
<body id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header-4.php');
	?>

  	<h1>Earnings</h1>
	<form method="POST" name="earnings" action="<?$_SERVER[PHP_SELF]?>">
		<input type="hidden" name="action" value="save">
		<table class="type1b">
			<tr>
				<td class="type1b_header">Date</td>
				<td class="type1b_header">FM Earnings</td>
				<td class="type1b_header">Google Earnings</td>
			</tr>
			<tr>
				<td class="type1b_header">
					<input type="text" name="month" value="" style="width:2em"> /
					<input type="text" name="year" value="" style="width:4em">
				</td>
				<td class="type1b_header">
					<input type="text" name="amount_fm" value="" style="width:7em">
				</td>
				<td class="type1b_header">
					<input type="text" name="amount_google" value="" style="width:7em">
				</td>
			</tr>
			<?
				$sql = "SELECT * FROM earnings ORDER BY year DESC, month DESC";
				$result = mQuery($sql);
				while ($row = mysql_fetch_assoc($result)) {
					echo '<tr>'.
						'<td class="grid">'. $row['month'] .'/'. $row['year'] .'</td>'.
						'<td class="grid" align="right">'. $row['amount_fm'] .'</td>'.
						'<td class="grid" align="right">'. $row['amount_google'] .'</td>'.
					'</tr>';
				}
			?>
		</table><br />
		<input type="submit" value="Save">
	</form>

	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
</body>
</html>
