<?
	include("includes/global.php");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title></title>
	<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
</head>
<body>

<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
   <input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but24.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!">
   <input type="hidden" name="cmd" value="_xclick-subscriptions">
   <input type="hidden" name="business" value="shane@hdtvmagazine.com">
   <input type="hidden" name="item_name" value="HDTV Program Guide (Monthly)">
   <input type="hidden" name="no_shipping" value="1">
   <input type="hidden" name="no_note" value="1">
   <input type="hidden" name="currency_code" value="USD">
   <input type="hidden" name="a1" value="0.00">
   <input type="hidden" name="p1" value="2">
   <input type="hidden" name="t1" value="W">
   <input type="hidden" name="a3" value="1.50">
   <input type="hidden" name="p3" value="1">
   <input type="hidden" name="t3" value="M">
   <input type="hidden" name="src" value="1">
   <input type="hidden" name="sra" value="1">
</form>

<A HREF="https://www.paypal.com/cgi-bin/webscr?cmd=_subscr-find&alias=shane%40hdtvmagazine.com">
<IMG SRC="https://www.paypal.com/en_US/i/btn/cancel_subscribe_gen.gif" BORDER="0">
</A>

<?

// Email Link
// https://www.paypal.com/subscriptions/business=shane%40hdtvmagazine.com&item_name=HDTV+Program+Guide+%28Monthly%29&no_shipping=1&no_note=1&currency_code=USD&a1=0.00&p1=2&t1=W&a3=1.50&p3=1&t3=M&src=1&sra=1

// Email cancel link
// https://www.paypal.com/cgi-bin/webscr?cmd=_subscr-find&alias=shane%40hdtvmagazine.com


// Insert Code Here

?></body>
</html>
