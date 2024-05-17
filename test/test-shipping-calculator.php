<?
	require('../global.php');
	require('../includes/lib_cart.php');

	$debug = ($_POST[debug] == '1');
	$action = isset($_POST[action]) ? $_POST[action] : '';
	if ($action == 'submit') {
		// Separate into pounds and ounces
		$pounds = floor($_POST[ship_weight]/16);
		$ounces = $_POST[ship_weight] % 16;

		$package = array(
			'service' => 'all',
			'zip_origin' => $_POST[shipfrom_zip],
			'zip_dest' => $_POST[shipto_zip],
//			'city' => $ship_city,
			'pounds' => $pounds,
			'ounces' => $ounces,
			'length' => $_POST[length],
			'width' => $_POST[width],
			'height' => $_POST[height],
			'size' => 'regular',
			'container' => 'Flat Rate Envelope',
			'machinable' => 'true'
		);
		
		// Set up shipping API's
//		if ($ship_country == 'US') {
			$usps = new USPS('722HDTVM4527', '', 'RateV2');
			$package[country] = 'US';
			$show_rates = array('First-Class Mail', 'Priority Mail', 'Media Mail');
/*		} else {
			$usps = new USPS('722HDTVM4527', '', 'IntlRate');
			$c_result = mQuery("SELECT printable_name name FROM opt_country WHERE iso = '$ship_country'");
			$c_row = mysql_fetch_assoc($c_result);
			$package[country] = $c_row[name];
			$package[mail_type] = 'Package';
			$show_rates = array('Airmail Letter Post', 'Global Priority Mail - Variable Weight (Single)', 'Airmail Parcel Post', 'Global Priority Mail - Flat-rate Envelope (Large)');
		}
*/		if ($debug) {echo "<pre>";print_r($package);echo "</pre>";}

		if(!$usps->add_package($package)) {
			$err = $usps->get_package_error();
			echo "USPS: Failed to add the package<br>";
			echo "<pre>";print_r($err);echo "</pre>";
			echo "<pre>";print_r($package);echo "</pre>";
			echo "<pre>$cart_id</pre>";
			exit;
		} else { // Submit to USPS API 
			$usps->submit_request();
			$err = $usps->get_package_error();
			if ($err) {
				echo "<pre>";print_r($err);echo "</pre>";
				echo "<pre>";print_r($package);echo "</pre>";
				echo "<pre>$cart_id</pre>";
				exit;
			}
		}

		$ups = new UPS("FBEA760D39C74F51", "hdtvmagazine", "1080i720p");
		if(!$ups->add_package($package)) {
			echo "UPS: Failed to add the package<br>";
			echo "<pre>". print_r($package) ."</pre>";
			exit;
		} else { // Submit to UPS API 
			$ups->submit_request();
			$err = $ups->get_package_error();
			if ($err) {
				echo "<pre>";print_r($err);echo "</pre>";
				echo "<pre>";print_r($package);echo "</pre>";
				echo "<pre>$cart_id</pre>";
				exit;
			}
		}
	}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>HDTV Magazine - Test Shipping Calculator</title>
	<?require(BASE_DIR .'/includes/common_header.php');?>
</head>
<body>
	<?
		include(BASE_DIR .'/includes/tracker.php');
		include(BASE_DIR .'/includes/body_header.php');
	?>
	
	<p>
		Enter ship-from, ship-to, and package weight and dimensions and click "Calculate" to see the rates available.
	</p>
	
	<form name="frm" action="<?=PHP_SELF?>" method="post">
		<input type="hidden" name="action" value="submit">
		<input type="hidden" name="debug" value="">
   	<table class="type1b" style="table-layout:fixed;width:450px">
   		<col width=125>
   		<col width=325>
   		<tr>
				<td class="type1b_header" colspan=2>Shipping Calculator</td>
			</tr><tr>
   			<td class="inputLabel" nowrap>Ship From Zip:</td>
   			<td><input type="text" class="inputText" name="shipfrom_zip" style="width:8ex" value="<?=$_POST[shipfrom_zip]?>"></td>
   		</tr><tr>
   			<td class="inputLabel" nowrap>Ship To Zip:</td>
   			<td><input type="text" class="inputText" name="shipto_zip" style="width:8ex" value="<?=$_POST[shipto_zip]?>"></td>
   		</tr><tr>
   			<td class="inputLabel" nowrap>Weight:</td>
   			<td><input type="text" class="inputText" name="ship_weight" style="width:4ex" value="<?=$_POST[ship_weight]?>"> oz.</td>
   		</tr><tr>
   			<td class="inputLabel" nowrap>Dimensions:</td>
   			<td>
   				<b>L:</b><input type="text" class="inputText" name="length" style="width:4ex" value="<?=$_POST[length]?>"> in.
   				<b>W:</b><input type="text" class="inputText" name="width" style="width:4ex" value="<?=$_POST[width]?>"> in.
   				<b>H:</b><input type="text" class="inputText" name="height" style="width:4ex" value="<?=$_POST[height]?>"> in.
   			</td>
			</tr><tr>
				<td class="buttonBar" colspan="2">
					<input type="submit" name="btnSubmit" value="&nbsp;Calculate&nbsp;" class="inputButton">
				</td>
   		</tr>
   	</table>
	</form>
	
  	<table class="type1b" style="table-layout:fixed;width:450px">
		<col width=350>
		<col width=50>
		<tr>
			<td class="type1b_header">Service</td><td class="type1b_header" style="text-align:right">Rate</td>
		</tr><?
         // Get rates
         $rates = $usps->get_rates(0);
         if ($debug) {echo "<pre>";print_r($rates);echo "</pre>";}
         if (is_array($rates)) {
         	foreach ($show_rates as $service) {
         		if (array_key_exists($service, $rates)) {
         			echo '<tr><td class="type1b">USPS '. $service .'&reg;</td><td class="type1b" style="text-align:right">$'. number_format($rates[$service],2) .'</td></tr>';
         		}
         	}
         }
   
         // Get rates
         $rates = $ups->get_rates(0);
         if ($debug) print_r($rates);
         if (is_array($rates)) {
         	$UPS_RATE = array('01' => 'Next Day Air&reg;', '02' => '2nd Day Air&reg;', '03' => 'Ground', 
         	'07' => 'Worldwide Express', '08' => 'Worldwide Expidited', '11' => 'Standard', '12' => '3 Day Select&reg;', 
         	'13' => 'Next Day Air Saver&reg;', '14' => 'Next Day Air&reg; Early A.M.&reg;', '54' => 'Worldwide Express Plus', '59' => '2nd Day Air A.M.&reg;');
         	$show_rates = array('01', '02', '03');
         	foreach ($show_rates as $service) {
         		if (array_key_exists($service, $rates)) {
         			echo '<tr><td class="type1b">UPS '. $UPS_RATE[$service] .'&reg;</td><td class="type1b" style="text-align:right">$'. number_format($rates[$service], 2) .'</td></tr>';
         		}
         	}
         }
   	?>
	<br>
	<br>
	<?include(BASE_DIR .'/includes/body_footer.php');?>
</body>
</html>
