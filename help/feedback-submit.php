<?
	require('../global.php');

	$action = isset($_POST['action']) ? $_POST['action'] : '';
	if ($action == 'save') {
		# Check for crap
		if ($_POST['subject'] == 'hello' || $_POST['link'] != '' || $_POST['url'] != '') {
			header("HTTP/1.0 404 Not Found");
			exit;
		}

		// Set the From address
		$from = $_POST['name'] .' <'. $_POST['user_email'] .'>';

		$subject = "Feedback: ". stripslashes($_POST['subject']) ." [{$_POST['user_id']}]";

		$message = "user_id:\t{$_POST['user_id']}\n";
		$message .= "ip_address:\t{$_SERVER['REMOTE_ADDR']}\n";
		$message .= "referer:\t{$_POST['referer']}\n";
		$message .= "link:\t{$_POST['link']}\n";
		$message .= "url:\t{$_POST['url']}\n";
		$message .= "user_name:\t". $user->data['user_name'] ."\n";
		$message .= "email_address:\t{$_POST['user_email']}\n";
		$message .= "access:\t\t". $user->data['access'] ."\n";
		$message .= "Browser:\t{$_POST['browser']}\n";
		$message .= "Category:\t{$_POST['category']}\n";
		$message .= "Edit User:\thttp://www.hdtvmagazine.com/admin/user-edit.php?user_id={$_POST['user_id']}\n";
		$message .= "Edit User:\thttp://www.hdtvmagazine.com/admin/user-edit.php?user_email={$_POST['user_email']}\n";
		$message .= "Comments:\n\n". stripslashes($_POST['comments']);

		$headers = "From: $from\r\n" .
		"X-Mailer: PHP/" . phpversion() ."\r\n";

		if ($_POST['cc'] == 1) $headers .= "Cc: $from\r\n";

		if (mail($admindata['feedback_email'], $subject, $message, $headers)) {
			js_replace('/help/feedback-thanks.php');
		} else {
			js_back('Failed to send your message...');
		}
		exit;
	}
?>