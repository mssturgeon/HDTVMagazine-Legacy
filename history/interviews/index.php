<?
	require('../../global.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<!--DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"-->
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<?require(BASE_DIR .'/includes/common_header.php');?>
	<meta http-equiv="Content-Type" content="text/html; charset=<$MTPublishCharset$>" />
	<title>HDTV Magazine - History &amp; Interviews</title>
</head>
<body>
	<?
		include(BASE_DIR .'/ads/leaderboard.php');
		include(BASE_DIR .'/includes/body_header.php');
	?>

		<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
			<td style="vertical-align:top;padding-right:20px;">
				<h2>HDTV Magazine Interviews</h2>
				<table cellpadding="3" cellspacing="0"><?
						$qry = "SELECT entry_created_on, entry_title, entry_basename, author_name".
						" FROM mt_entry e, mt_author a".
						" WHERE".
						"	e.entry_author_id = a.author_id".
						"	AND entry_blog_id = 4 AND entry_status = 2".
						"	ORDER BY entry_created_on DESC";
						$result = mQuery($qry);
						while ($entry = mysql_fetch_assoc($result)) {
							$ts = strtotime($entry['entry_created_on']);
							$y = date('Y', $ts);
							$m = date('m', $ts);
							echo '<tr>'.
	//										'	<td class="entrydate" nowrap>'. date('M j, Y', $ts) .'</td>'.
							'	<td><a href="'. URL_INTERVIEWS_DIR .'/'. $y .'/'. $m .'/'. $entry['entry_basename'] .'.php">'. $entry['entry_title'] .'</a></td>'.
							'</tr>';
						}
					?></table>
			</td><td style="vertical-align:top">
				<h2>HDTV History &amp; Archives</h2>
				<table cellpadding="3" cellspacing="0">
					<?
						$qry = "SELECT entry_created_on, entry_title, entry_basename, author_name".
						" FROM mt_entry e, mt_author a".
						" WHERE".
						"	e.entry_author_id = a.author_id".
						"	AND entry_blog_id = 5 AND entry_status = 2".
						"	ORDER BY entry_created_on DESC";
						$result = mQuery($qry);
						while ($entry = mysql_fetch_assoc($result)) {
							$ts = strtotime($entry['entry_created_on']);
							$y = date('Y', $ts);
							$m = date('m', $ts);
							echo '<tr>'.
//										'	<td class="entrydate" nowrap>'. date('M j, Y', $ts) .'</td>'.
							'	<td><a href="'. URL_HISTORY_DIR .'/'. $y .'/'. $m .'/'. $entry['entry_basename'] .'.php">'. $entry['entry_title'] .'</a></td>'.
							'</tr>';
						}
					?>
				</table>
		</td>
	</tr></table>

	<?include(BASE_DIR .'/includes/body_footer.php');?>

</body>
</html>

