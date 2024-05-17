<?
	require('../global.php');

	function mail_send($to, $subject, $message, $content_type, $unsub_code = '') {
		global $admindata, $debug;

		$content_type = ($content_type == '') ? 'text/plain' : $content_type;

		// Add formatting to message
		if ($content_type == 'text/html') {
			$body = getHTMLMessage($to, $message, $unsub_code);
		} else {
			$body = getTextMessage($to, $message, $unsub_code);
		}

		$headers = array (
			'From' => "$admindata[email_display_name] <$admindata[email_reply_address]>",
			'To' => $to,
			'Subject' => $subject,
			'Content-Type' => "$content_type; charset=iso-8859-1",
			'X-Sender' => "$admindata[email_reply_address]",
			'Return-Path' => "$admindata[email_return_path]",
			'Reply-To' => "$admindata[email_reply_address]",
			'MIME-Version' => '1.0',
			'X-Priority' => '3',
			'X-Mailer' => 'PHP/'. phpversion());
		if ($debug) {
			echo "Headers:\n";
			print_r($headers);
		}
#		if (strpos($to, '@yahoo.com') === false) {
			$email_smtp_host = $admindata[email_smtp_host];
#		} else {
#			$email_smtp_host = 'localhost';
#			$email_smtp_host = 'www.hdtvmagazine.com';
#		}

		$smtp = Mail::factory('smtp', array ('host' => $email_smtp_host ,'auth' => false));
		if ($debug) {
			echo "SMTP:\n";
			print_r($smtp);
		}

		return $smtp->send($to, $headers, $body);
	}

	echo mail_send('check-auth@verifier.port25.com', 'Authentication Report', '', 'text/plain')
?>
