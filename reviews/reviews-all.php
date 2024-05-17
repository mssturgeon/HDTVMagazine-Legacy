<?
	require('../global.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<?require(BASE_DIR .'/includes/common_header.php');?>
	<title>HDTV Magazine - All Articles</title>

	<link rel="stylesheet" href="/stylesheets/blog_css.php" type="text/css" />
</head>
<body>
	<?
		include(BASE_DIR .'/includes/tracker.php');
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
	?>
	
	<h2>All Articles</h2>
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td style="vertical-align:top">
			<table cellpadding="3" cellspacing="0">
				<?
					$qry = "SELECT entry_created_on, entry_title, entry_basename, author_name".
					" FROM mt_entry e, mt_author a".
					" WHERE".
					"	e.entry_author_id = a.author_id".
					"	AND entry_blog_id = 8 AND entry_status = 2".
					"	ORDER BY entry_created_on DESC";
					$result = mQuery($qry);
					while ($entry = mysql_fetch_assoc($result)) {
						$ts = strtotime($entry['entry_created_on']);
						$y = date('Y', $ts);
						$m = date('m', $ts);
						echo '<tr>'.
							'	<td class="entrydate">'. date('M j, Y', $ts) .'</td>'.
						'	<td><a href="'. $y .'/'. $m .'/'. $entry['entry_basename'] .'.php">'. $entry['entry_title'] .'</a><span class="grey"> ('. $entry['author_name'] .')</span></td>'.
						'</tr>';
					}
				?>
			</table>
		</td><td style="vertical-align:top;text-align:right">
			<?
				include(BASE_DIR .'/ads/wide_skyscraper.php');
			?>
		</td>
	</tr></table>

	<?include(BASE_DIR .'/includes/body_footer.php');?>
</body>
</html>
