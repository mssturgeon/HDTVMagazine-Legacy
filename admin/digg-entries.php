<?
	set_time_limit(0);
	require('../global.php');
	if (!access(ACCESS_ADMIN_ANY)) prompt_login(PHP_SELF);
	header('Cache-Control: no-store'); // HTTP/1.1

	$action = isset($_POST[action]) ? $_POST[action] : '';
	if ($action == 'submit') {
		for ($x=1; $x <= $_POST['number']; $x++) {
			$qry = 'UPDATE aux_mt_entry SET '.
			'digg_url = \''. urlencode($_POST["digg_url$x"]) .'\''.
			'WHERE entry_id = '. $_POST["entry_id$x"];
			mQuery($qry);
			echo "$qry<br>";
		}
		js_back();
		exit;
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title>HDTV Magazine - Digg Entries</title>
	<?require(BASE_DIR .'/includes/common_header.php');?>
</head>
<body>
	<?
		include(BASE_DIR .'/includes/tracker.php');
		include(BASE_DIR .'/includes/body_header.php');
	?>
	
  	<h1>Digg Entries</h1>
	<form action="<?=PHP_SELF?>" method="post" name="frm">
		<input type="hidden" name="action" value="submit">
		<table class="type1b" cellpadding="0" cellspacing="0" width="100%">
		<?

			$sql = "
			SELECT e.entry_id, digg_url, entry_title, entry_created_on
			FROM mt_entry e, aux_mt_entry a
			WHERE e.entry_id = a.entry_id
				AND digg_url IS NULL
			ORDER BY entry_created_on LIMIT 10";
			$result = mQuery($sql);
			echo '<input type="hidden" name="number" value="'. mysql_num_rows($result) .'">';

			$x = 1;
			while ($row = mysql_fetch_assoc($result)) {
				echo '<input type="hidden" name="entry_id'. $x .'" value="'. $row[entry_id] .'">'.
				date('Y-m-d', strtotime($row[entry_created_on])) .' - <b>'. stripslashes($row[entry_title]) .'</b><br />'.
				'Digg URL: <input type="text" size="50" name="digg_url'. $x .'" value="'. urldecode($row[digg_url]) .'"/><br /><br />';
				$x++;
			}
		?>
		</table>
		<div align="center"><input type="submit" value="Submit" class="inputButton"></div>
	</form>
	
	<?include(BASE_DIR .'/includes/body_footer.php');?>
</body>
</html>
