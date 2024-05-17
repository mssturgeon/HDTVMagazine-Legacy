<?
	# BASE_DIR is the full OS path to the web document directory.  User primarily in include/require statements
	define('BASE_DIR', '/var/www/html');
	require(BASE_DIR .'/includes/constants.php');
	require(BASE_DIR .'/includes/lib_mysql.php');
	require(BASE_DIR .'/includes/lib_common.php');
	require(BASE_DIR .'/includes/lib_paypal.php');

	$debug = true;
	$sql_error_reporting = 'none';

	# post back to PayPal system to validate
	$fp = fsockopen('ssl://'. PAYPAL_HOST, 443, $errno, $errstr, 30);
	if (!$fp) { # HTTP Error
		log_error($handle, "EXITING - HTTP Error");
	} else {
		if ($debug) log_action($handle, "Opened connection to ". PAYPAL_HOST);

		# read the post from PayPal system and add 'cmd'
		$req = 'cmd=_notify-validate';
		foreach ($_POST as $key => $value) {
			$paypal[$key] = urldecode($value);
			$log_record[] = "$key={$paypal[$key]}";

			$value = urlencode(stripslashes($value));
			$req .= "&$key=$value";
		}

		$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Content-Length: ". strlen($req) ."\r\n\r\n";

		fputs ($fp, $header . $req);
		while (!feof($fp)) {
			$res = fgets ($fp, 1024);
			if (strcmp($res, "VERIFIED") == 0) {
				if ($debug) log_action($handle, "Valid Response");

				# Export to log file
#				foreach ($_POST as $name => $value) $values[] = $value;
#				fwrite($handle, implode(',', $values) ."\n");
					fwrite($handle, implode($log_record, '&') ."\n");

				# check the payment_status is Completed
#				if ($paypal['payment_status'] == 'Completed') {
#					if ($debug) log_action($handle, "VALID - payment_status = Completed");

					# check that txn_id has not been previously processed
# txn_id could be re-used for Signup/Completed/Pending transactions, so skip for now
#					$result = mQuery("SELECT id FROM paypal_ipn WHERE txn_id = '{$paypal['txn_id']}'");
#					if (mysql_num_rows($result) == 0) {
#						if ($debug) log_action($handle, "Valid txn_id");

						# check that receiver_email is your Primary PayPal email
						if ($paypal['receiver_email'] == PAYPAL_EMAIL) {
							if ($debug) log_action($handle, "Valid receiver_email");

							# check that payment_amount/payment_currency are correct
# Don't need to check this for subscriptions, since it could be different amounts - move inside process_subscription, but only for new signups
#
#							$result = mQuery("SELECT * FROM hdtv_products WHERE item_number = '{$paypal['item_number']}' and a3 = '{$paypal['mc_gross']}'");
#							if (mysql_num_rows($result) == 1) {
#								if ($debug) log_action($handle, "Valid payment_amount/payment_currency");

								##### LOG & INSERT #####

								# Items to exclude from table insertion
								$exclude = array('charset', 'verify_sign', 'payment_gross', 'payment_fee', 'notify_version');

								# Add auxiliary values
								$paypal['timestamp'] = time();

								##########################
								process_subscription($paypal);
								##########################

								# Build SQL and insert into data table
								foreach ($paypal as $name => $value) {
									if (!in_array($name, $exclude)) {
										$names[] = $name;
										$values[] = $value;
									}
								}

								$sql = "INSERT IGNORE INTO paypal_ipn (". implode(', ', $names) .") VALUES ('". implode("', '", $values) ."')";
								log_action($handle, "$sql\n");
								if (!mQuery($sql)) log_action($handle, 'Error '. mysql_errno() .': '. mysql_error());

#							} else {
#								log_error($handle, "EXITING - item_number/item_amount don't match: {$paypal['item_number']}/{$paypal['mc_gross']}");
#							} # item_number/item_amount
						} else {
							log_error($handle, "EXITING - Forged receiver_email: {$paypal['receiver_email']}");
						} # receiver_email
#					} else {
#						log_error($handle, "EXITING - Transaction ID previously processed: {$paypal['txn_id']}");
#					} # txn_id

#				} else {
#					# payment_status <> Completed
#					# Further Processing?
#
#					log_error("Exiting - payment_status: {$_POST['payment_status']}");
#				} # payment_status
			} else if (strcmp ($res, "INVALID") == 0) { # log for manual investigation
				if ($debug) log_action($handle, $req);
				log_error($handle, "EXITING - INVALID Response");
			}
		}
		fclose ($fp);
	}

	fclose($handle);
?>