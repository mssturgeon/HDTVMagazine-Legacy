<?
	require('../global.php');
	require('model-overall_header.php');

	$contrast_ratio = ($row['contrast_ratio'] == '' | $row['contrast_ratio'] == '0') ? 'N/A' : $row['contrast_ratio'] .':1';
	$resolution = ($row['h_res'] == 0) ? 'N/A' : $row['h_res'] .'x'. $row['v_res'];
	$lumens = ($row['lumens'] == 0) ? 'N/A' : $row['lumens'];
	$tuner = ($row['hdtv_tuner'] == 1) ? 'Yes' : '';
	$tuner = ($row['hdtv_tuner'] === '0') ? 'No' : $tuner;
	$pixel_aspect = ($row['v_res'] == 0) ? 'N/A' : number_format($row['h_res']/$row['v_res'], 2);
	$screen_aspect = ($row['screen_aspect'] == '') ? 'N/A' : $row['screen_aspect'];
	$manuf_url = ($row['url'] == '') ? '&nbsp;' : 'Check the <a onmouseover="return link_over(this)" onmouseout="return link_out(this)" href="/cgi-bin/ntlinktrack.cgi?'. $row['url'] .'">'. $row['alias'] .'</a> website for details.';
	$size = ($row['size'] == 0) ? 'N/A' : $row['size'] .'"';

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - Specifications (Specs): <?=$title?></title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header-4.php');
	?>

	<h1>Specifications (Specs): <?=$title?></h1>
	<div id="eq-mrec"><? include(BASE_DIR .'/ads/mrectangle.php');?></div>
	<?=$breadcrumbs?>

	<? include('model-page_header.php');?>

	<br id="eq_br" />

	<div id="eq-sky"><? include(BASE_DIR .'/ads/skyscraper.php');?></div>

	<div style="display:table">
		<?=getTabHeader($tabs);?>
		<div class="tab-content">
			<table class="type1b" cellpadding="0" cellspacing="0" style="width:100%">
				<tr><td class="type1b_header" style="width:100px">Size:</td><td class="type1b"><?=$size?></td></tr>
				<tr><td class="type1b_header" style="width:100px">Built-in Tuner(s):</td><td class="type1b"><?=$tuner?></td></tr>
				<tr><td class="type1b_header" style="width:100px">Display Type:</td><td class="type1b"><?=$MODEL_TYPE[$row['type']]?></td></tr>
				<tr><td class="type1b_header" style="width:100px">Product Type:</td><td class="type1b"><?=$row['product_type']?></td></tr>
				<tr><td class="type1b_header" style="width:100px">Resolution:</td><td class="type1b"><?=$resolution?></td></tr>
				<tr><td class="type1b_header" style="width:100px">Pitch:</td><td class="type1b"><?=$row['pitch']?></td></tr>
				<tr><td class="type1b_header" style="width:100px">Response Time:</td><td class="type1b"><?=$row['response_time']?></td></tr>
				<tr><td class="type1b_header" style="width:100px">Projection:</td><td class="type1b"><?=$MODEL_PROJ_TYPE[$row['projection']]?></td></tr>
				<tr><td class="type1b_header" style="width:100px" nowrap>Horiz. Viewing Angle:</td><td class="type1b"><?=$row['h_va']?></td></tr>
				<tr><td class="type1b_header" style="width:100px">Vert. Viewing Angle:</td><td class="type1b"><?=$row['v_va']?></td></tr>
				<tr><td class="type1b_header" style="width:100px">Screen Aspect:</td><td class="type1b"><?=$screen_aspect?></td></tr>
				<tr><td class="type1b_header" style="width:100px">Pixel Aspect:</td><td class="type1b"><?=$pixel_aspect?></td></tr>
				<tr><td class="type1b_header" style="width:100px">Comb Filter:</td><td class="type1b"><?=$row['comb_filter']?></td></tr>
				<tr><td class="type1b_header" style="width:100px">Contrast Ratio:</td><td class="type1b"><?=$contrast_ratio?></td></tr>
				<tr><td class="type1b_header" style="width:100px">Lumens:</td><td class="type1b"><?=$lumens?></td></tr>
				<tr><td class="type1b_header" style="width:100px">Input:</td><td class="type1b"><?=$row['input']?></td></tr>
				<tr><td class="type1b_header" style="width:100px">Interface Type:</td><td class="type1b"><?=$row['interface_type']?></td></tr>
				<tr><td class="type1b_header" style="width:100px">Slots:</td><td class="type1b"><?=$row['slots']?></td></tr>
				<tr><td class="type1b_header" style="width:100px">Video Standards:</td><td class="type1b"><?=$row['video_standards']?></td></tr>
				<tr><td class="type1b_header" style="width:100px">Formats Supported:</td><td class="type1b"><?=$row['format_supp']?></td></tr>
				<tr><td class="type1b_header" style="width:100px">Formats Displayed:</td><td class="type1b"><?=$row['format_disp']?></td></tr>
				<tr><td class="type1b_header" style="width:100px">Dimensions:</td><td class="type1b"><?=$row['height']?>"H x <?=$row['width']?>"W x <?=$row['depth']?>"D</td></tr>
				<tr><td class="type1b_header" style="width:100px">Weight:</td><td class="type1b"><?=$row['weight']?></td></tr>
				<tr><td class="type1b_header" style="width:100px">SKU:</td><td class="type1b"><?=$row['sku']?></td></tr>
				<tr><td class="type1b_header" style="width:100px">Warranty:</td><td class="type1b"><?=$row['warranty']?></td></tr>
				<tr><td class="type1b_header" style="width:100px">General Features:</td><td class="type1b"><?=$row['general_features']?></td></tr>
				<tr><td class="type1b_header" style="width:100px">Notes:</td><td class="type1b"><?=$row['notes']?></td></tr>
			</table>
		</div>
	</div>

	<? if (access(ACCESS_ADMIN_ANY)) {
		include(BASE_DIR .'/includes/lib_admin.php');
		echo getRowInformation($row);
	}?>

	<?
		include(BASE_DIR .'/includes/body_footer-4.php');
		include('model_footer.php');
	?>
</div></body>
</html>
