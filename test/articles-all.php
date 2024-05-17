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
		require(BASE_DIR .'/includes/tracker.php');
		getAdUnit('728', '90', '728x90_as', $GOOGLE_CHANNEL['Index']);
	?>
	
	<table class="main" cellpadding="0" cellspacing="0" summary="" align="center">
		<tr>
			<td style="border-bottom:1px solid <?=BORDER_COLOR?>">
				<?require(BASE_DIR .'/includes/header.php');?>
			</td>
		</tr><tr>
			<td style="border-bottom:1px solid <?=BORDER_COLOR?>">
				<?require(BASE_DIR .'/includes/menu_bar.php');?>
			</td>
		</tr><tr>
			<td class="content">

				<h2>All Articles</h2>
				<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
					<td style="vertical-align:top">
						<table cellpadding="3" cellspacing="0">
							<?
								$qry = "SELECT entry_created_on, entry_title, entry_basename, author_name".
								" FROM mt_entry e, mt_author a".
								" WHERE".
								"	e.entry_author_id = a.author_id".
								"	AND entry_blog_id = 6 AND entry_status = 2".
								"	ORDER BY entry_created_on DESC";
								$result = mQuery($qry);
								while ($entry = mysql_fetch_assoc($result)) {
									$ts = strtotime($entry['entry_created_on']);
									$y = date('Y', $ts);
									$m = date('m', $ts);
									echo '<tr>'.
										'	<td class="entrydate">'. date('M j, Y', $ts) .'</td>'.
									'	<td><a href="'. URL_ARTICLES_DIR .'/'. $y .'/'. $m .'/'. $entry['entry_basename'] .'.php">'. $entry['entry_title'] .'</a><span style="color:#666666"> ('. $entry['author_name'] .')</span></td>'.
									'</tr>';
								}
							?>
						</table>
					</td><td style="vertical-align:top;text-align:right">
						<?getAdUnit('160', '600', '160x600_as', $GOOGLE_CHANNEL['Index']);?>
					</td>
				</tr></table>

			</td>
		</tr><tr>
			<td class="footer">
				<?require(BASE_DIR .'/includes/footer.php');?><br>
			</td>
		</tr>
	</table>

</body>
</html>
