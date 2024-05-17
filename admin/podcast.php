<?
	require('../global.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title>HDTV Magazine - Podcast Admin</title>
	<?require(BASE_DIR .'/includes/common_header.php');?>
	<script>
		function editPodcast(id) {
			var h = 500;
			var w = 500;
			var t = (screen.height - h) / 2;
			var l = (screen.width - w) / 2;
			
			window.open('podcast-edit.php?id='+id, 'EditPodcast', 'resizable=yes,scrollbars=yes,width='+w+',height='+h+',top='+t+',left='+l)
		}
	</script>
</head>
<body>
	<?
		include(BASE_DIR .'/includes/tracker.php');
		include(BASE_DIR .'/includes/body_header.php');
	?>
	
	<h1>Podcast Management</h1>

	<input type="button" class="inputButton" value="New Entry" onclick="editPodcast();" />
	<input type="button" class="inputButton" value="Ping" target="_blank" onclick="location.href='https://phobos.apple.com/WebObjects/MZFinance.woa/wa/pingPodcast?id=189033712'" />
	<br />
	<br />
	<table class="type1b" align="center"><tr>
		<td class="type1b_header">Pub Date</td>
		<td class="type1b_header">Title</td>
		<td class="type1b_header">Author</td>
		<td class="type1b_header">Length</td>
		<td class="type1b_header">Type</td>
		<td class="type1b_header">Duration</td>
		<td class="type1b_header">Keywords</td>
	</tr><?
		$sql = "SELECT * FROM podcast ORDER BY pubDate DESC";
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result)) {
			echo '<tr>'.
			'	<td class="grid">'. date('Y-m-d', $row[pubDate]) .'</td>'.
			'	<td class="grid" nowrap><b><a href="javascript:editPodcast('. $row[id] .')">'.
					$row[title] .'</a></b><br />'.
					$row[subtitle] .
			'	</td>'.
			'	<td class="grid" nowrap>'. $row[author] .'</td>'.
			'	<td class="grid">'. $row[length] .'</td>'.
			'	<td class="grid">'. $row[type] .'</td>'.
			'	<td class="grid">'. $row[duration] .'</td>'.
			'	<td class="grid">'. $row[keywords] .'</td>'.
			'</tr>';
		}
	?>
	</table>
	
	<br />
				
	<?include(BASE_DIR .'/includes/body_footer.php');?>
</body>
</html>
