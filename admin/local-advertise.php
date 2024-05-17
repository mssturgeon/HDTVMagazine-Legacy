<?
	require('../global.php');
	if (!access(ACCESS_ADMIN)) prompt_login(PHP_SELF);

	// Get user count
	$qry = "SELECT count(*) count FROM user WHERE date_format(modified, '%Y-%m-%d') <> '0000-00-00'";
	$result = mQuery($qry);
	$row = mysql_fetch_assoc($result);
	$users = $row['count'];

   // Set PV Counts
   $page_views = 140000;
   $page_views_updated = '10/04/2004';

	// Projected Monthly Users
	$proj_monthly_users = 400;

	$action = '';
	if (isset($_POST['action'])) $action = $_POST['action'];
	if ($action == 'submit') {
		$sql = "SELECT DISTINCT user_id
		FROM join_user_station j, prog_source source
		WHERE j.source_id = source.source_id
			AND source.dma_name = '". $_POST['dma_name'] ."'";
		$result = mQuery($qry);
		$market_users = mysql_num_rows($result);
	}

	$proj_market_users = round($market_users/$users*$proj_monthly_users);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>HDTV Magazine - Advertise With Us</title>
	<? require(BASE_DIR .'/includes/common_header.php')?>
</head>
<body>

   <table class="greyBox" align="center" style="margin-top:15px"><tr><td>
   	Shown below are site statistics and demographic information for our subscribers. If you wish to narrow it down to a local market, simply select your local market from the Market pull-down.
   </td></tr></table>

	<form name="frm" action="<?PHP_SELF?>" method="post">
		<input type="hidden"name="action" value="submit">
   	<table class="table1" align="center">
   		<tr><td class="table1Header" colspan="2">Advertise With Us</td></tr>
			<tr>
				<td class="inputLabel" width="150">Market:</td>
				<td><?=getSelectBox("SELECT DISTINCT dma_name, dma_name FROM prog_source WHERE dma_name <> '' ORDER BY dma_name", "dma_name", $_POST['dma_name'], "")?></td>
			</tr>
			<tr><td colspan="2" class="buttonBar">
				<input type="submit" class="inputButton" name="btn" value="Show Data">
			</td></tr>
   	</table>
	</form>

	<table class="table1" align="center">
		<tr><td class="table1Header" colspan="2">Overall Demographics</td></tr>
			<tr>
				<td width="150" align="right">Page Views/Month:</td>
				<td><b><?=number_format($page_views)?></b> (last updated <b><?=$page_views_updated?></b>)</td>
			</tr>
  	</table>

	<table class="table1" align="center">
		<tr><td class="table1Header" colspan="2">Market Demographics</td></tr>
			<tr>
				<td width="150" align="right">Market:</td>
				<td><?=$_POST['tf_dma_name']?></td>
			</tr><tr>
				<td width="150" align="right">Market Users:</td>
				<td><?=$market_users?></td>
			</tr><tr>
				<td width="150" align="right">Total Users:</td>
				<td><?=$users?></td>
			</tr><tr>
				<td width="150" align="right">New Users/Month:</td>
				<td><?=$proj_monthly_users?></td>
			</tr><tr>
				<td width="150" align="right">New Market Users/Month:</td>
				<td><?=$proj_market_users?></td>
			</tr>
  	</table>

</body>
</html>
