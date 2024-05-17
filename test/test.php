<?
	require('../global.php');

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - Test</title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
</head>
<body>
	<?
		include(BASE_DIR .'/includes/body_header-4.php');
	?>

	<pre><?=var_dump($_SERVER);?></pre>
	<?
		$url = rawurlencode('/xml/sub_xml.php?type=2');
		$width = 900;
		$height = 200;
		echo '<div align="center">'.
		'	<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"'.
		'			codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" '.
		'			id="FusionCharts" width="'. $width .'" height="'. $height .'" viewastext>'.
		'		<param name=movie value="/charts/FC_2_3_Line.swf?dataUrl='. $url .'&amp;chartWidth='. $width .'&amp;chartHeight='. $height .'">'.
		'		<param name=FlashVars value="">'.
		'		<param name=quality value="high">'.
		'		<param name="bgcolor" value="#ffffff">'.
		'		<embed src="/charts/FC_2_3_Line.swf?dataUrl='. $url .'&amp;chartWidth='. $width .'&amp;chartHeight='. $height .'" width="'. $width .'" height="'. $height .'" FlashVars="" quality="high" bgcolor="#ffffff" name="FusionCharts" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer"></embed>'.
		'	</object><br />'.
		'</div>';
?>


	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
</body>
</html>