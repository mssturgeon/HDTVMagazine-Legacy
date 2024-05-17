<?
	define('PAYPAL_HOST', 'www.paypal.com');
#	define('PAYPAL_HOST', 'www.sandbox.paypal.com');
	define('PAYPAL_URL', 'https://'. PAYPAL_HOST .'/cgi-bin/webscr');
	define('PAYPAL_EMAIL', 'pp@hdtvmagazine.com');

	# Set up output file
	$handle = fopen(BASE_DIR .'/../paypal.out', 'a');

	function log_error($handle, $error_txt) {
		fwrite($handle, "$error_txt\n\n");
		fclose($handle);
		exit;
	}

	function log_action($handle, $action_txt) {
		fwrite($handle, "$action_txt\n");
	}

	function process_subscription($paypal) {
		global $debug, $handle;

#		if (($_POST['payment_status'] == 'Completed') && ($paypal['custom'] > 0)) { # User-based transaction
		if ($paypal['custom'] > 0) { # User-based transaction
			switch ($paypal['txn_type']) {
				case 'subscr_signup':
					$is_trial = false;
					if ($paypal['period1'] != '') { # Assign last_trial date. This keeps someone from setting up continuous trials
#						$sql = "UPDATE user SET trial_date = CURDATE() WHERE id = '{$paypal['custom']}'";
						$sql = "UPDATE user SET last_trial = CURDATE() WHERE id = '{$paypal['custom']}'";
#						if ($debug) {log_action($handle, "$sql\n");} else {mQuery($sql);}
						if ($debug) log_action($handle, "$sql\n");
						mQuery($sql);
						$is_trial = true;
					}
					# The auto-renew flag is set on signup, not payment
					$autorenew = "flg_autorenew = '{$paypal['recurring']}',";
#					break;
				case 'subscr_payment': # Update user record to extend expiration date
					# NOTE: Since there is no 'break' in the previous section, this will be executed for both signups and payments
					$update_user = false;
					if ($_POST['payment_status'] == 'Completed' || $is_trial) { # Don't process updates for "Cleared" payments ... wait until they're "Completed"
						if ($paypal['item_number'] == 2) { # Monthly subscription
							$exp_date = strtotime('+1 month');
							$membership_freq = 'M';
							$pricing_annual = $paypal['mc_gross'] * 12;
							$update_user = true;
						}
						if ($paypal['item_number'] == 3) { # Yearly subscription
							$exp_date = strtotime('+1 year');
							$membership_freq = 'Y';
							$pricing_annual = $paypal['mc_gross'];
							$update_user = true;
						}
					}

					if ($update_user) {
						$exp_pg = gmdate('Y-m-d', $exp_date);
						$sql = "UPDATE user SET exp_pg = '$exp_pg', membership_freq = '$membership_freq', pricing_annual = '$pricing_annual', $autorenew access = access | ". ACCESS_PREMIUM ." WHERE id = '{$paypal['custom']}'";
#						if ($debug) {log_action($handle, "$sql\n");} else {mQuery($sql);}
						if ($debug) log_action($handle, "$sql\n");
						mQuery($sql);
					}
					break;
				case 'subscr_cancel':
					$sql = "UPDATE user SET flg_autorenew = 0, pricing_annual = 0, membership_freq = '' WHERE id = '{$paypal['custom']}'";
#					if ($debug) {log_action($handle, "$sql\n");} else {mQuery($sql);}
					if ($debug) log_action($handle, "$sql\n");
					mQuery($sql);
					break;
				case 'subscr_eot':
					$sql = "UPDATE user SET access = access - ". ACCESS_PREMIUM ." WHERE id = '{$paypal['custom']}' AND (access & ". ACCESS_PREMIUM .")";
#					if ($debug) {log_action($handle, "$sql\n");} else {mQuery($sql);}
					if ($debug) log_action($handle, "$sql\n");
					mQuery($sql);
					break;
				case 'web_accept':
					if ($_POST['payment_status'] == 'Completed') { # Don't process updates for "Cleared" payments ... wait until they're "Completed"
						switch ($paypal['item_number']) {
							case 4: # Gift Subscriptions
								$sql = "UPDATE gift_subscriptions SET active = 1 WHERE from_user = '{$paypal['custom']}'";
#								if ($debug) {log_action($handle, "$sql\n");} else {mQuery($sql);}
								if ($debug) log_action($handle, "$sql\n");
								mQuery($sql);
								break;
							case 6: # CES Report
								$sql = "UPDATE user SET access = access | ". ACCESS_CES ." WHERE id = '{$paypal['custom']}'";
#								if ($debug) {log_action($handle, "$sql\n");} else {mQuery($sql);}
								if ($debug) log_action($handle, "$sql\n");
								mQuery($sql);
								break;
							default:
								# No action needed
						}
					}
					break;
				case 'subscr_failed':
					# Do nothing
					break;
				default:
					log_error($handle, "Exiting - Unknown txn_type: {$paypal['txn_type']}\n");
			}
		} else {
			# No id (custom) given or assigned
		}
	}
?>