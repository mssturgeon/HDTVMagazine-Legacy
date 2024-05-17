<?
	require('../global.php');
	if (!access(ACCESS_ADMIN_ANY)) prompt_login(PHP_SELF);

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - Users</title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
</head>
<body onload="frm.search.select();">
	<?
		$action = isset($_POST[action]) ? $_POST[action] : '';
		if ($action == 'search') {
			$sql = "
			SELECT u.*, bb.*
			FROM user u
				LEFT JOIN ". USERS_TABLE ." bb ON bb_id = user_id
			WHERE id LIKE '%$_POST[search]%'
					OR bb_id LIKE '%$_POST[search]%'
					OR user_name LIKE '%$_POST[search]%'
					OR first_name LIKE '%$_POST[search]%'
					OR last_name LIKE '%$_POST[search]%'
					OR email_address LIKE '%$_POST[search]%'
				";
			$result = mQuery($sql);

			// If there is only 1 resulting record, redirect to the user-edit form
			if (mysql_num_rows($result) == 1) {
				$row = mysql_fetch_assoc($result);
				js_replace("user-edit.php?id={$row[id]}");
				exit;
			}
		}
	?>

	<div align="center" ><div style="width:400px" class="ad"><span class="corners-top"><span></span></span>
		<form name="frm" action="/admin/users.php" method="post">
			<input type="hidden" name="action" value="search">
			Search for:
			<input type="text" name="search" value="<?=@$_POST['search']?>" class="inputText" size="20" />
			<input type="submit" name="btnSubmit" value="&nbsp;Search&nbsp;" class="inputButton" />
		</form>
	<span class="corners-bottom"><span></span></span></div></div>

	<? if ($action == 'search') { ?>
		<table class="subTable1" align="center">
			<tr><td><b><?=mysql_num_rows($result)?></b> Records Returned.</td></tr>
		</table>

		<table class="type1b" align="center">
			<tr>
				<td class="type1b_header">id</td>
				<td class="type1b_header">user_id</td>
				<td class="type1b_header">UserName</td>
				<td class="type1b_header">Name</td>
				<td class="type1b_header">Access</td>
				<td class="type1b_header">Invalids</td>
				<td class="type1b_header">Registered</td>
				<td class="type1b_header">Last Visit</td>
			</tr>
			<?
				while ($row = mysql_fetch_assoc($result)) {
					$email_class = ($row['email_invalid'] >= 3 || $row['user_inactive_reason'] > 0) ? 'grey_italic' : '';
					echo '<tr id="'. $row['id'] .'" class="reportData" onMouseOver="hover_over(this)" onMouseOut="hover_out(this)">'.
						'	<td class="grid">'. $row['id'] .'</td>'.
						'	<td class="grid">'. $row['user_id'] .'</td>'.
						'	<!--td class="grid">'. $row['login'] .'</td-->'.
						'	<td class="grid"><a href="user-edit.php?id='. $row['id'] .'">'. $row['user_name'] .'</a><br /><span class="'. $email_class .'">'. $row['email_address'] .'</span></td>'.
						'	<td class="grid">'. $row['last_name'] .', '. $row['first_name'] .'</td>'.
						'	<td class="grid">'. $row['access'] .'</td>'.
						'	<td class="grid">'. $row['email_invalid'] .'</td>'.
						'	<td class="grid">'. date('Y-m-d g:ia', $row['user_regdate']) .'</td>'.
						'	<td class="grid">'. ($row['user_lastvisit'] == 0 ? 'Never' : date('Y-m-d H:i:s', $row['user_lastvisit'])) .'</td>'.
						'</tr>';
				}
			?>
		</table>
	<? } ?>

</body>
</html>
