<?
	require('../global.php');
	if (!access(ACCESS_ADMIN_ANY)) prompt_login(PHP_SELF);
	header('Cache-Control: no-store'); // HTTP/1.1
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>HDTV Magazine - Accounts in phpbb_users, but not in user</title>
	<?require(BASE_DIR .'/includes/common_header.php');?>
</head>
<body>
	<?
		include(BASE_DIR .'/includes/tracker.php');
		include(BASE_DIR .'/includes/body_header.php');
	?>
	
	<h1>Accounts in phpbb_users, but not in user</h1>
	<p>
		Accounts listed here include entries from the phpbb_users table without a corresponding entry in the main user table.  They should either be linked up or deleted.
		Matching email addresses and/or usernames are displayed.
	</p>
	
	<table class="type1b" cellpadding=0 cellspacing=0>
		<tr>
			<td class="type1b_header">&nbsp;</td>
			<td class="type1b_header">user_id</td>
			<td class="type1b_header">username</td>
			<td class="type1b_header">user_email</td>
			<td class="type1b_header">user_posts</td>
			<td class="type1b_header">Last Visit</td>
			<td class="type1b_header">Registered</td>
			<td class="type1b_header">Username Match</td>
			<td class="type1b_header">Email Match</td>
		</tr><?
			$bail = 0;
			$sql = "
			SELECT user_id, username, user_password, user_email, user_lastvisit, FROM_UNIXTIME(user_regdate) regdate, user_posts
			FROM phpbb_users
			WHERE user_id > 0
				AND user_active = 1
			ORDER BY user_lastvisit DESC";
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result)) {
				$sql = "SELECT id FROM user WHERE bb_id = $row[user_id]";
				$res_user = $db->sql_query($sql);
				if ($db->sql_numrows($res_user) == 0) {
					$lastvisit = ($row[user_lastvisit] == 0) ? 'Never' : date($row[user_lastvisit]);
					echo '<tr onmouseover="hover_over(this)" onmouseout="hover_out(this)">'.
					'	<td class="type1b"><a href="/admin/user-delete.php?user_id='. $row[user_id] .'"><img src="/images/remove.gif" alt="Remove"></a></td>'.
					'	<td class="type1b">'. $row[user_id] .'</td>'.
					'	<td class="type1b">'. $row[username] .'</td>'.
					'	<td class="type1b">'. $row[user_email] .'</td>'.
					'	<td class="type1b">'. $row[user_posts] .'</td>'.
					'	<td class="type1b" nowrap>'. $lastvisit .'</td>'.
					'	<td class="type1b" nowrap>'. $row[regdate] .'</td>';

					# Find matching username
					$sql = "SELECT id FROM user WHERE user_name = '$row[username]'";
					$res_username = $db->sql_query($sql);
					if ($db->sql_numrows($res_username) > 0) {
						$match_row = $db->sql_fetchrow($res_username);
						echo '	<td class="type1b" style="text-align:center"><a href="/admin/user-edit.php?id='. $match_row[id] .'">'. $match_row[id] .'</a></td>';
					} else {
						echo '	<td class="type1b">&nbsp;</td>';
					}
						
					# Find matching email address
					$res_email = $db->sql_query("SELECT id FROM user WHERE email_address = '$row[user_email]'");
					if ($db->sql_numrows($res_email) > 0) {
						$match_row = $db->sql_fetchrow($res_email);
						echo '	<td class="type1b" style="text-align:center"><a href="/admin/user-edit.php?id='. $match_row[id] .'">'. $match_row[id] .'</a></td>';
					} else {
						echo '	<td class="type1b">&nbsp;</td>';
					}
					
					echo "</tr>\n";
				}
			}
		?>
	</table>
	
	<?include(BASE_DIR .'/includes/body_footer.php');?>
</body>
</html>
