<?
	require('../global.php');
	if (!access(ACCESS_ADMIN)) access_denied();

	$action = isset($_POST['action']) ? $_POST['action'] : '';
	if ($action == 'save') {
		$id = $_POST['id'];
#		$notify = $_POST['notify'];
#		$emails = $_POST['emails'];
		for ($x=0; $x<count($id); $x++) {
			$sql = "UPDATE aux_phpbb_forums SET exclude_general = '". $_POST['general_'. $id[$x]] ."', exclude_from_notify = '". $_POST['notify_'. $id[$x]] ."' WHERE forum_id = '". $id[$x] ."'";
			mQuery($sql);
#			echo "$sql<br>";
		}
	}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>HDTV Magazine - Forum Management</title>
	<?require(BASE_DIR .'/includes/common_header.php');?>
</head>
<body id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
	?>

	<h1>Forum Management</h1>

	<form name="frmForumMgt" method="POST" action="<?+PHP_SELF?>">
		<input type="hidden" name="action" value="save">
		<table class="type1b" cellpadding="0" cellspacing="0" summary="" style="width:100%">
			<tr>
				<td class="type1b_header">Forum ID</td>
				<td class="type1b_header">Forum Name</td>
				<td class="type1b_header">Exclude<br />General</td>
				<td class="type1b_header">Exclude<br />Notify</td>
			</tr>
			<?
				# First do an insert to make sure all the forums have an entry
				$sql = "INSERT IGNORE INTO aux_phpbb_forums SELECT forum_id, 0, 0 FROM ". FORUMS_TABLE;
				mQuery($sql);

				$sql = "SELECT f.forum_id, forum_name, IF(exclude_general=1, 'checked', '') general, IF(exclude_from_notify=1, 'checked', '') notify
				FROM ". FORUMS_TABLE ." f, aux_phpbb_forums a
				WHERE f.forum_id = a.forum_id";
				$result = mQuery($sql);
				while ($row = mysql_fetch_assoc($result)) {
					$id = $row['forum_id'];
					echo '<input type="hidden" name="id[]" value="'. $id .'"><tr>'."\n".
					'	<td class="type1b">'. $id .'</td>'."\n".
					'	<td class="type1b">'. $row['forum_name'] .'</td>'."\n".
					'	<td class="type1b"><input type="checkbox" name="general_'. $id .'" '. $row['general'] .' value="1"></td>'."\n".
					'	<td class="type1b"><input type="checkbox" name="notify_'. $id .'" '. $row['notify'] .' value="1"></td>'."\n".
					'</tr>';
				}
			?><tr>
			</tr>
		</table>
		<input type="submit" name="btnSubmit" value="&nbsp;Save&nbsp;" class="inputButton">
	</form>

	<?include(BASE_DIR .'/includes/body_footer.php');?>
</body>
</html>
