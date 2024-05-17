<?
	// BASE_DIR is the full OS path to the web document directory.  User primarily in include/require statements
	define('BASE_DIR', '/var/www/html');
	require(BASE_DIR .'/includes/constants.php');
	require(BASE_DIR .'/includes/lib_mysql.php');
	require(BASE_DIR .'/includes/lib_common.php');
	require(BASE_DIR .'/includes/lib_paypal.php');

	// read the post from PayPal system and add 'cmd'
	$req = 'cmd=_notify-validate';

	foreach ($_POST as $key => $value) {
		$value = urlencode(stripslashes($value));
		$req .= "&$key=$value";
	}

	// post back to PayPal system to validate
	$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
	$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
	$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
	$fp = fsockopen (PAYPAL_HOST, 80, $errno, $errstr, 30);

	// assign posted variables to local variables
	$timestamp = time();

	$amount3 = $_POST['amount3'];
	$business = $_POST['business'];
	$custom = $_POST['custom'];
	$invoice = $_POST['invoice'];
	$item_name = $_POST['item_name'];
	$item_number = $_POST['item_number'];
	$mc_currency = $_POST['mc_currency'];
	$mc_fee = $_POST['mc_fee'];
	$mc_gross = $_POST['mc_gross'];
	$mc_handling = $_POST['mc_handling'];
	$mc_shipping = $_POST['mc_shipping'];
	$memo = $_POST['memo'];
	$parent_txn_id = $_POST['parent_txn_id'];
	$password = $_POST['password'];
	$payer_email = $_POST['payer_email'];
	$payment_date = $_POST['payment_date'];
	$payment_status = $_POST['payment_status'];
	$payment_type = $_POST['payment_type'];
	$pending_reason = $_POST['pending_reason'];
	$period1 = $_POST['period1'];
	$period3 = $_POST['period3'];
	$quantity = $_POST['quantity'];
	$reason_code = $_POST['reason_code'];
	$reattempt = $_POST['reattempt'];
	$receiver_email = $_POST['receiver_email'];
	$receiver_id = $_POST['receiver_id'];
	$recur_times = $_POST['recur_times'];
	$recurring = $_POST['recurring'];
	$retry_at = $_POST['retry_at'];
	$subscr_id = $_POST['subscr_id'];
	$subscr_date = $_POST['subscr_date'];
	$subscr_effective = $_POST['subscr_effective'];
	$tax = $_POST['tax'];
	$txn_id = $_POST['txn_id'];
	$txn_type = $_POST['txn_type'];
	$user_id = $custom;
	$id = $custom;
	$username = $_POST['username'];
	$first_name = $_POST['first_name'];
	$last_name = $_POST['last_name'];
	$payer_business_name = $_POST['payer_business_name'];
	$address_name = $_POST['address_name'];
	$address_street = $_POST['address_street'];
	$address_city = $_POST['address_city'];
	$address_state = $_POST['address_state'];
	$address_zip = $_POST['address_zip'];
	$address_country = $_POST['address_country'];
	$address_status = $_POST['address_status'];
	$payer_id = $_POST['payer_id'];
	$payer_status = $_POST['payer_status'];

	# Fetch user_name in case we need it
#	$res_user = mQuery("SELECT user_name FROM user WHERE id = '$user_id'");
	$res_user = mQuery("SELECT username FROM phpbb_users WHERE user_id = '$user_id'");
	if (mysql_num_rows($res_user) > 0) {
		$row_user = mysql_fetch_assoc($res_user);
		$user_name = $row_user[user_name];
	} else {
		#
	}

	if (!$fp) {
		// HTTP ERROR

	} else {
		// Add data to MySQL
		$qry = "INSERT INTO paypal_ipn (".
		"	timestamp, amount3, business, custom, invoice, item_name, item_number, mc_currency, mc_fee, mc_gross, mc_handling, mc_shipping, memo, parent_txn_id, password, payer_email, payment_date,".
		"	payment_status, payment_type, pending_reason, period1, period3, quantity, reason_code, reattempt, receiver_email, receiver_id, recur_times, recurring, retry_at, subscr_id, subscr_date, subscr_effective, tax,".
		"	txn_id, txn_type, user_id, username, first_name, last_name, payer_business_name, address_name, address_street, address_city, address_state, address_zip, address_country, address_status, payer_id, payer_status".
		") VALUES (".
		"	'$timestamp', '$amount3', '$business', '$custom', '$invoice', '$item_name', '$item_number', '$mc_currency', '$mc_fee', '$mc_gross', '$mc_handling', '$mc_shipping', '$memo', '$parent_txn_id', '$password', '$payer_email', '$payment_date',".
		"	'$payment_status', '$payment_type', '$pending_reason', '$period1', '$period3', '$quantity', '$reason_code', '$reattempt', '$receiver_email', '$receiver_id', '$recur_times', '$recurring', '$retry_at', '$subscr_id', '$subscr_date', '$subscr_effective', '$tax',".
		"	'$txn_id', '$txn_type', '$user_id', '$username', '$first_name', '$last_name', '$payer_business_name', '$address_name', '$address_street', '$address_city', '$address_state', '$address_zip', '$address_country', '$address_status', '$payer_id', '$payer_status')";
		mQuery($qry);
		$ipn_id = mysql_insert_id();

		fputs ($fp, $header . $req);
		while (!feof($fp)) {
			$res = fgets ($fp, 1024);
			if (strcmp ($res, "VERIFIED") == 0) {
				// check that payment_status is Completed
				if ($payment_status == 'Completed' && $txn_type == 'subscr_payment') {
					//OK
				} else {
//					exit;
				}

				// check that txn_id has not been previously processed
				$result = mQuery("SELECT ipn_id FROM paypal_ipn WHERE txn_id = '$txn_id'");
				if (mysql_num_rows($result) > 0) {
					//OK
				} else {
					// Email notification
				}

				// check that receiver_email is your Primary PayPal email
				if ($receiver_email == PAYPAL_EMAIL) {
					//OK
				} else {
					// Forgery?
				}

				// check that payment_amount/payment_currency are correct
				$result = mQuery("SELECT * FROM hdtv_products WHERE item_number = '$item_number' and amount = '$mc_gross'");
				if (mysql_num_rows($result) == 1) {
					//OK
				} else {
					// Forgery?
				}

				// process payment & grant appropriate privileges
				if ($custom > 0) {
					switch ($item_number) {
/*
						case 6: // CES Report
							mQuery("UPDATE user SET access = access | ". ACCESS_CES ." WHERE id = '$user_id'");
							break;
*/
						case 2: // Premium Membership (Monthly)
							if ($txn_type == 'subscr_signup' || $txn_type == 'subscr_payment') {
								if (gmdate('m') == 12) {
									$exp_pg = gmdate('Y-m-d', gmmktime(0, 0, 0, 1, gmdate('d'), gmdate('Y') + 1));
								} else {
									$exp_pg = gmdate('Y-m-d', gmmktime(0, 0, 0, (gmdate('m') % 12) + 1));
								}
#								mQuery("UPDATE user SET exp_pg = '$exp_pg', membership_freq = 'M', flg_autorenew = '$recurring', access = access | ". ACCESS_PREMIUM ." WHERE bb_id = '$user_id'");
								mQuery("UPDATE user SET exp_pg = '$exp_pg', membership_freq = 'M', flg_autorenew = '$recurring', access = access | ". ACCESS_PREMIUM ." WHERE id = '$id'");

/*** XCART ***
								# Update xcart_customers
								mQuery("UPDATE xcart_customers SET membershipid = 5 WHERE login = '$user_name'");
***/

								// Assign last_trial date
#								if ($period1 != '') mQuery("UPDATE user SET trial_date = CURDATE() WHERE bb_id = '$user_id'");
								if ($period1 != '') mQuery("UPDATE user SET trial_date = CURDATE() WHERE id = '$id'");
							} elseif ($txn_type == 'subscr_eot') { // Remove access
#								mQuery("UPDATE user SET access = access - ". ACCESS_PREMIUM ." WHERE bb_id = '$user_id' AND (access & ". ACCESS_PREMIUM .")");
								mQuery("UPDATE user SET access = access - ". ACCESS_PREMIUM ." WHERE id = '$id' AND (access & ". ACCESS_PREMIUM .")");

/*** XCART ***
								# Update xcart_customers
								mQuery("UPDATE xcart_customers SET membershipid = 0 WHERE login = '$user_name'");
***/
							} elseif ($txn_type == 'subscr_cancel') { // Remove Auto-renewal
#								mQuery("UPDATE user SET flg_autorenew = 0 WHERE bb_id = '$user_id'");
								mQuery("UPDATE user SET flg_autorenew = 0 WHERE id = '$id'");
							}

							if (mysql_affected_rows($result) == 0) {
								// Notify
							}
							break;
						case 3: // Premium Membership (Yearly)
							if ($txn_type == 'subscr_signup' || $txn_type == 'subscr_payment') {
								$exp_pg = gmdate('Y-m-d', gmmktime(0, 0, 0, gmdate('m'), gmdate('d'), gmdate('Y')+1));
#								mQuery("UPDATE user SET exp_pg = '$exp_pg', membership_freq = 'Y', flg_autorenew = '$recurring', access = access | ". ACCESS_PREMIUM ." WHERE bb_id = '$user_id'");
								mQuery("UPDATE user SET exp_pg = '$exp_pg', membership_freq = 'Y', flg_autorenew = '$recurring', access = access | ". ACCESS_PREMIUM ." WHERE id = '$id'");

/*** XCART ***
								# Update xcart_customers
								mQuery("UPDATE xcart_customers SET membershipid = 5 WHERE login = '$user_name'");
***/

								// Assign last_trial date
#								if ($period1 != '') mQuery("UPDATE user SET trial_date = CURDATE() WHERE bb_id = '$user_id'");
								if ($period1 != '') mQuery("UPDATE user SET trial_date = CURDATE() WHERE id = '$id'");
							} elseif ($txn_type == 'subscr_eot') { // Remove access
#								mQuery("UPDATE user SET access = access - ". ACCESS_PREMIUM ." WHERE bb_id = '$user_id' AND (access & ". ACCESS_PREMIUM .")");
								mQuery("UPDATE user SET access = access - ". ACCESS_PREMIUM ." WHERE id = '$id' AND (access & ". ACCESS_PREMIUM .")");

/*** XCART ***
								# Update xcart_customers
								mQuery("UPDATE xcart_customers SET membershipid = 0 WHERE login = '$user_name'");
***/
							} elseif ($txn_type == 'subscr_cancel') { // Remove Auto-renewal
#								mQuery("UPDATE user SET flg_autorenew = 0 WHERE bb_id = '$user_id'");
								mQuery("UPDATE user SET flg_autorenew = 0 WHERE id = '$id'");
							}

							if (mysql_affected_rows($result) == 0) {
								// Notify
							}
							break;
						case 4: // Gift Subscriptions
							mQuery("UPDATE gift_subscriptions SET active = 1 WHERE from_user = '$id'");
							break;
						default:
							// Notify (Unknown Item Number)
					}
				} else {
					// No user_id given or assigned
				}
			} else if (strcmp ($res, "INVALID") == 0) {
				// log for manual investigation
				mQuery("UPDATE paypal_ipn SET invalid = 1 WHERE id = $ipn_id");

				// Email notification

				fclose ($fp);
				exit;
			}
		}
		fclose ($fp);
	}
?>
