<?
	require('../global.php');
	if (!access(ACCESS_ADMIN_ANY)) access_denied();

	include(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - HDTV Magazine Authors</title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
	<script language="javascript" type="text/javascript">
		function edit(id) {
			 var h = 500;
			 var w = 500;
			 var t = (screen.height - h) / 2;
			 var l = (screen.width - w) / 2;

			 window.open('author-edit.php?author_id='+id, 'Add', 'resizable=yes,scrollbars=yes,width='+w+',height='+h+',top='+t+',left='+l)
		}
	</script>
</head>
<body>
	<h1>HDTV Magazine Authors</h1>

	<table class="simple" cellpadding="0" cellspacing="0" summary="" style="width:%">
		<tr>
			<td class="type1b_header">Author/Title</td>
			<td class="type1b_header">User ID</td>
			<td class="type1b_header">Channel Name / #</td>
			<td class="type1b_header">Amazon ID / VigLink ID</td>
			<td class="type1b_header">Share</td>
			<td class="type1b_header">Portrait</td>
			<td class="type1b_header">Include?</td>
		</tr>
		<?
			$sql = "SELECT m.author_id, title, user_id, channel, amazon_tracking_id, viglink_source, channel_name, img, author_name, revshare, include
			FROM mt_author m LEFT JOIN aux_author a ON m.author_id = a.author_id
			ORDER BY author_name";
			$result = mQuery($sql);
			while ($row = mysql_fetch_assoc($result)) {
				echo '<tr>'.
				'	<td style="width:200px;vertical-align:top">'.
						'<a href="author-edit.php?author_id='. $row['author_id'] .'">'. stripslashes($row['author_name']) .'</a><br />'. stripslashes($row['title']) .'</td>'.
				'	<td>'. $row['user_id'] .'</td>'.
				'	<td>'. $row['channel_name'] .'<br />'. $row['channel'] .'</td>'.
				'	<td>'. $row['amazon_tracking_id'] .'<br />'. $row['viglink_source'] .'</td>'.
				'	<td>'. $row['revshare'] .'</td>'.
				'	<td>'. $row['img'] .'</td>'.
				'	<td>'. $row['include'] .'</td>'.
				'</tr>';
			}
		?><tr>
		</tr>
	</table>
</body>
</html>
