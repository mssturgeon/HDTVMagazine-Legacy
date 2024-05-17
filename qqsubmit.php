<?
	require('global.php');
	require(BASE_DIR .'/includes/lib_forum.php');

	$question = isset($_GET['question']) ? $_GET['question'] : '';
	if ($question == '') exit;

	$email_address = isset($_GET['email_address']) ? $_GET['email_address'] : '';
	$ip_address = isset($_GET['ip_address']) ? $_GET['ip_address'] : '';
	$forum_id = 134;

	# Get User ID
	if ($user->data['is_registered']) {
		$user_id = $user->data['user_id'];
	} elseif ($email_address != '') { # Look up user by email address
		$sql = "SELECT user_id FROM ". USERS_TABLE ." WHERE user_email = '$email_address'";
		$result = mQuery($sql);
		if (mysql_num_rows($result) == 0) { # Create User
			$username = strleftback(str_replace('@', '', $email_address), '.');
			$user_id = create_user($username, '', $email_address);
		} else { # Pull user_id
			$row = mysql_fetch_assoc($result);
			$user_id = $row['user_id'];
		}
	} else { # Anonymous posting
		$user_id = 36589;
	}

	$topic_id = create_topic($forum_id, substr($question, 0, 50), $question, $user_id, $ip_address, true);
	echo $topic_id;
?>