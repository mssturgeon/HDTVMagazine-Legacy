<?
	require('../global.php');
	if (!access(ACCESS_ADMIN)) prompt_login(PHP_SELF);

	$action = isset($_POST['action']) ? $_POST['action'] : '';
	if ($action == 'submit') {
		foreach (split("\n", $_POST['serp']) as $line) {
			$row = split('  +', $line);
			$keywords = $row[0];
			$url = $row[1];
			$se = $row[2];
			$serp = ($row[3] == 'N/A') ? '200' : $row[3];
			
			$qry = "REPLACE INTO search_rank (timestamp, keywords, url, se, serp) VALUES (UNIX_TIMESTAMP(), '$keywords', '$url', '$se', $serp)";
			mQuery($qry);
		}
		exit;
		//js_back('Email Updated.');
	}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>HDTV Magazine - SERP Import</title>
	<?require(BASE_DIR .'/includes/common_header.php');?>
</head>
<body onload="document.frm.serp.focus()">
	<?
		include(BASE_DIR .'/includes/tracker.php');
		include(BASE_DIR .'/includes/body_header.php');
	?>
	
				<table class="box" align="center"><tr><td>
					This page is used to import the emailed SERP report from DPS.
				</td></tr></table>
			
				<table align="center">
					<form name="frm" action="<?=PHP_SELF?>" method="post">
						<input type="hidden" name="action" value="submit">
						<tr>
							<td class="inputLabel" valign="top">SERP Text:</td>
							<td>
								<textarea name="serp" style="width:550px;height:450px;font-size:8pt;font-family:Monospace"></textarea>
								<input type="submit" value="Submit" class="inputButton">
							</td>
						</tr>
					</form>
				</table>

	<?include(BASE_DIR .'/includes/body_footer.php');?>

</body>
</html>
