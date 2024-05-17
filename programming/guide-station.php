<?
	require('../global.php');
#	if (!access(ACCESS_PREMIUM)) prompt_login(PHP_SELF);

	$source_id = $_GET['id'];

	if ($source_id == '') {
		# Need to return 404 here
		js_back('Invalid Request. Not enough parameters to look up Station data.');
		exit();
	}

	function ordinalize($number) {
		# returns the ordinal value of any number (1 = 1st, 2 = 2nd, etc.)
		$driver = substr("$number", -1);
		switch($driver) {
			case '1':
				$number = $number . "st";
				break;
			case '2':
				$number = $number . "nd";
				break;
			case '3':
				$number = $number . "rd";
				break;
			default:
				$number = $number . "th";
	 			break;
		}
		return $number;
	}

	$sql = "
	SELECT source.*, headend.*, lineup.*, zipcode.*, mso.*
	FROM
		{$admindata['tbl_source']} source,
		{$admindata['tbl_headend']} headend,
		{$admindata['tbl_lineup']} lineup,
		{$admindata['tbl_zipcode']} zipcode,
		{$admindata['tbl_mso']} mso
	WHERE source.source_id = '$source_id'
		AND source.source_id = lineup.source_id
		AND lineup.headend_id = headend.headend_id
		AND headend.headend_id = zipcode.headend_id
		AND headend.mso_id = mso.mso_id";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - Broadcast Station/Network Guide (<?=$row['full_name']?>)</title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
	<link rel="stylesheet" type="text/css" href="/stylesheets/guide_css.php">
	<script language="javascript" type="text/javascript">
		// Set OverLib Variables
		ol_width = 300;
		ol_offsety = 20;
		ol_cellpad = 0;
		ol_border=0;
	</script>
</head>
<body id="body_container">
	<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
	<?
		require_once(BASE_DIR .'/includes/lib_guide.php');
		include(BASE_DIR .'/includes/body_header-4.php');

		// Set Market Information
		$market = ($row['dma_num'] > 0) ? 'Market: '. $row['dma_name'] .' ('. ordinalize($row['dma_rank']) .')<br />' : '';

		// Get Image
		$image_path = BASE_IMG_HOST .'/images/logos/'. str_replace('[size].', '120.', $row['image_file']);
	?>

	<h1><?=$row['full_name']?></h1>

	<div id="right" style="float:right; margin:0 0 5px 5px; text-align:center;" align="center">
		<? include(BASE_DIR .'/ads/mrectangle.php');?>
		<br />
		<div align="center">
			<? include(BASE_DIR .'/ads/skyscraper.php');?>
		</div>
	</div>

	<div><?
		$breadcrumbs = '&raquo; <a href="/programming/index.php">Programming</a>&nbsp;';
		if ($row['source_type'] == 'Broadcast') {
			$breadcrumbs .= '&raquo; <a href="/programming/broadcast.php">Broadcast</a>&nbsp;'.
			'&raquo; <a href="/programming/broadcast-market.php?dma_name[]='. $row['dma_name'] .'">'. urlencode($row['dma_name']) .' Market</a>&nbsp;';
		}
		$breadcrumbs .= '&raquo; <b>'. $row['full_name'] .'</b><br /><br />';

		echo $breadcrumbs;
		?>

		<fieldset id="network-information">
			<legend>Network Information</legend>
			<label>Name:</label> <?=$row['full_name']?><br />
			<label>Affiliate:</label> <?=$row['affiliate_1']?> (add _2 and _3)<br />
			<label>Owned/Operated:</label> <?=$row['owned_operated']?><br />
			<label>Call sign:</label> <?=$row['call_sign']?><br />
			<label>City:</label> <?=$row['city']?><br />
			<label>State:</label> <?=$row['state']?><br />
			<label>Country:</label> <?=$row['country']?><br />
			<label>Type:</label> (Analog/Digital) Broadcast, Premium, Basic, etc.<br />
			<label>OTA Ch:</label> <?=$row['ota_channel_number']?><br />
			<label>Digital Ch:</label> <?=$row['digital_channel_number']?><br />
			<label>Virtual Ch.:</label> <?=$row['virtual_channel_number']?><br />
			<label>PSIP VCT Active:</label> <?=$row['psip_vct_active']?><br />
			<label>DMA Name:</label> <?=$row['dma_name']?><br />
			<label>DMA Code:</label> <?=$row['dma_code']?><br />
			<label>DMA Rank:</label> <?=$row['dma_rank']?><br />
			<label>Primary Language:</label> <?=$row['primary_language']?><br />
			<label>System Name:</label> <?=$row['system_name']?><br />
			<? if ($row['headend_id'] != '') {?>
				<label>Headend City:</label> <?=$row['headend_city']?><br />
				<label>Headend State:</label> <?=$row['headend_state']?><br />
				<label>Headend timezone:</label> <?=$row['headend_timezone']?><br />
				<label>Headend DST:</label> <?=$row['headend_dst']?><br />
				<label>Channel Device:</label> <?=$row['channel_device']?><br />
				<label>Channel Number:</label> <?=$row['channel_number']?><br />
				<label>MSO Name:</label> <?=$row['mso_name']?><br />
			<?}?>
		</fieldset>

		<br clear="all" />
	  	<?
	  		if (access(ACCESS_PREMIUM)) {
#	  			displayStationGuide($num, 30);
	  		} else {
	  			echo '<b>Note:</b> The guide below is a representative image only, and not the actual station guide. If you are interested, our subscription information can be found <a href="'. URL_SUBSCRIBE .'">here</a>.<br>'.
	  			'If you are already subscribed, please be sure you are logged in.<br /><br />'.
	  			'<img src="/images/guide-grey.gif" alt="Station Guide" />';
	  		}
	  	?>

		<? if (access(ACCESS_ADMIN_ANY)) {?>
			<br />
			<table class="type1b">
				<tr><td colspan="2" class="type1b_header">Data (Admin Only)</td></tr><?
					foreach ($row as $name => $value) {
						echo '<tr><td class="grid" nowrap="nowrap">'. $name .':</td><td class="grid">'. $value .'</td></tr>';
				 	}?>
			</table>
		<?}?>
	</div>

  	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
</body>
</html>
