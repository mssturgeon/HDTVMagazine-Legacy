<?
	require('../global.php');
	if (!access(ACCESS_ADMIN_ANY)) prompt_login(PHP_SELF);
	header('Cache-Control: no-store'); // HTTP/1.1
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>HDTV Magazine - Accounts in user, but not in phpbb_users</title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
</head>
<body>
	<?
		include(BASE_DIR .'/includes/body_header-4.php');
	?>

	<h1>Accounts in user, but not in phpbb_users</h1>
	<p>
		Accounts listed here include entries from the user table without a corresponding entry in the phpbb_users table.  They should either be linked up or deleted.
		Matching email addresses and/or usernames are displayed.
	</p>

	<table class="type1b" cellpadding=0 cellspacing=0>
		<tr>
			<td class="type1b_header">&nbsp;</td>
			<td class="type1b_header">id</td>
			<td class="type1b_header">bb_id</td>
			<td class="type1b_header">user_name</td>
			<td class="type1b_header">email_address</td>
			<td class="type1b_header">Invalids</td>
			<td class="type1b_header">Modified</td>
			<td class="type1b_header" style="text-align:center">Email Match</td>
			<td class="type1b_header" style="text-align:center">Username Match</td>
			<td class="type1b_header" style="text-align:center">Create Row</td>
		</tr><?
			$bail = 0;
			$sql = "SELECT id, bb_id, user_name, password, email_address, email_invalid, modified FROM user ORDER BY id";
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result)) {
				$sql = "SELECT user_id FROM ". USERS_TABLE ." WHERE user_id = '$row[bb_id]'";
				$res_bb = $db->sql_query($sql);
				if ($db->sql_numrows($res_bb) == 0) {
					echo '<tr onmouseover="hover_over(this)" onmouseout="hover_out(this)">'.
					'	<td class="type1b">&nbsp;</td>'.
					'	<td class="type1b">'. $row[id] .'</td>'.
					'	<td class="type1b">'. $row[bb_id] .'</td>'.
					'	<td class="type1b">'. $row[user_name] .'</td>'.
					'	<td class="type1b">'. $row[email_address] .'</td>'.
					'	<td class="type1b">'. $row[email_invalid] .'</td>'.
					'	<td class="type1b">'. $row[modified] .'</td>';

					$found = false;

					# Find matching email address
					$res_email = $db->sql_query("SELECT user_id FROM ". USERS_TABLE ." WHERE user_email = '$row[email_address]'");
					if ($db->sql_numrows($res_email) > 0) {
						$match_row = $db->sql_fetchrow($res_email);
						echo '	<td class="type1b" style="text-align:center">'.
						'<a href="/admin/user-set-user_id.php?from='. $match_row[user_id] .'&to='. $row[username] .'">SET login = '. $row[username] .'</a>'.
						'</td>';
						$found = true;
					} else {
						echo '	<td class="type1b">&nbsp;</td>';
					}

					# Find matching username
					$res_email = $db->sql_query("SELECT user_id FROM ". USERS_TABLE ." WHERE username = '$row[user_name]'");
					if ($db->sql_numrows($res_email) > 0) {
						$match_row = $db->sql_fetchrow($res_email);
						echo '	<td class="type1b" style="text-align:center">'.
						'<a href="/admin/user-set-user_id.php?from='. $match_row[user_id] .'&to='. $row[username] .'">SET login = '. $row[username] .'</a>'.
						'</td>';
						$found = true;
					} else {
						echo '	<td class="type1b">&nbsp;</td>';
					}

					if (!$found) {
						echo '	<td class="type1b" style="text-align:center">'.
						'<a href="/admin/user-createfrom-id.php?id='. $row[id] .'">Create '. $row[id] .'</a>'.
						'</td>';
					}

					echo "</tr>\n";
				}
			}
		?>
	</table>

	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
</body>
</html>
