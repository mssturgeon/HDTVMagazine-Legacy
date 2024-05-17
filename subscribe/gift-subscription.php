<?
	require('../global.php');
	if ($user->data['user_id'] == '') prompt_login(PHP_SELF);

	require(BASE_DIR .'/includes/lib_paypal.php');

	$action = isset($_POST['action']) ? $_POST['action'] : '';
	$email_addresses = split("\n", $_POST['email_addresses']);

	// Get product Information
	$result = mQuery("SELECT * FROM hdtv_products WHERE id = 4");
	$row = mysql_fetch_assoc($result);

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - Gift Subscription</title>
	<meta name="description" content="">
	<meta name="keywords" content="hdtv,hd tv,high definition,high def tv,high definition television,high definition tv">
	<meta name="rating" content="general">
	<? require(BASE_DIR .'/includes/page_header.php');?>
</head>
<body>
	<?
		include(BASE_DIR .'/includes/body_header-4.php');
	?>

				<h1>Gift Subscription</h1><?
         	switch($action) {
         		case 'continue':?>
      				<p>
      					Please verify the information below to ensure it is correct, then click Submit. You will be redirected to Paypal to complete your transaction.
						</p><p>
							<b>Please note</b> that once payment is confirmed, their accounts will become active immediately and each person will receive instant email notification indicating your gift purchase for them.
      				</p>

     					<form name="frm" action="<?=PHP_SELF?>" method="post">
     						<input type="hidden" name="action" value="submit">
     						<input type="hidden" name="email_addresses" value="<?=$_POST['email_addresses']?>">
         				<table class="type1b" cellpadding="0" cellspacing="0" align="center" style="width:50%;margin-bottom:20px">
        						<tr>
        							<td class="type1b_header">Email</td>
        							<td class="type1b_header">Duration</td>
        							<td class="type1b_header">Amount</td>
   							</tr><?
                       		foreach ($email_addresses as $email) {
			              			$email = trim(strtolower($email));
                       			if ($email != '') {
   										echo '<tr>'.
   										'	<td class="type1b" nowrap>'. $email .'</td>'.
   										'	<td class="type1b" style="width:50px">1 year</td>'.
   										'	<td class="type1b" style="width:50px;text-align:right" nowrap>$'. $row['amount'] .'</td>'.
   										'</tr>';
											$total += $row['amount'];
                       			}
   								}
   							?>
								<tr>
									<td class="inputLabel" colspan="2">Total:</td>
									<td class="type1b" style="text-align:right;">$<?=number_format($total, 2)?></td>
								</tr>
   						</table>

         				<table cellpadding="0" cellspacing="0" align="center" style="width:50%">
        						<tr>
        							</td><td style="text-align:center">
        								<input type="button" value="<< Back" class="inputButton" onclick="window.history.back();">
        							</td><td style="text-align:center">
        								<input type="submit" value="Submit" class="inputButton">
        							</td>
        						</tr>
         				</table>
     					</form>
         			<? break;
         		case 'submit':
              		foreach ($email_addresses as $email) {
              			$email = trim(strtolower($email));
              			if ($email != '') { // Add emails to a pending list, but clear out list first
								mQuery("DELETE FROM gift_subscriptions WHERE from_user = ". $user->data['user_id']);
								mQuery("REPLACE INTO gift_subscriptions (email_address, from_user) VALUES ('$email', ". $user->data['user_id'] .")");
              			}
   					}?>
						Please wait while we redirect to Paypal to complete your transaction.
      				<form name="frmPaypal" action="<?=PAYPAL_URL?>" method="post">
      					<input type="hidden" name="cmd" value="_xclick">
      					<input type="hidden" name="business" value="<?=PAYPAL_EMAIL?>">
      					<input type="hidden" name="item_name" value="<?=$row['item_name']?>">
      					<input type="hidden" name="item_number" value="<?=$row['item_number']?>">
      					<input type="hidden" name="amount" value="<?=$row['amount']?>">
      					<input type="hidden" name="quantity" value="<?=count($email_addresses)?>">
      					<input type="hidden" name="no_note" value="1">
      					<input type="hidden" name="currency_code" value="<?=$row['currency']?>">
      					<input type="hidden" name="custom" value="<?=$user->data['user_id']?>">
      					<input type="hidden" name="return" value="<?=BASE_URL . $row['return']?>">
      					<input type="hidden" name="cancel_return" value="<?=BASE_URL . $row['cancel_return']?>">
      				</form>
						<script language="javascript" type="text/javascript">
							document.forms['frmPaypal'].submit();
						</script>
         			<?break;
         		default:?>
      				<p>
      					<img src="/images/gifts.jpg" alt="Gifts" align="left"> To begin your gift subscription order, enter each recipients email address in the box below (one per line) and click Continue:
      				</p>

      				<table align="center">
      					<form name="frm" action="<?=PHP_SELF?>" method="post">
      						<input type="hidden" name="action" value="continue">
      						<tr>
      							<td class="inputLabel" valign="top">Email Addresses:</td>
      							<td>
      								<textarea name="email_addresses" style="width:300px;height:200px"></textarea>
      							</td>
      						</tr><tr>
      							<td colspan="2" style="text-align:right">
      								<input type="submit" value="Continue >>" class="inputButton">
      							</td>
      						</tr>
      					</form>
      				</table>
					<?}?>

	<? include(BASE_DIR .'/includes/body_footer-4.php');?>

</body>
</html>
