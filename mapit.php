<?
	require('global.php');
?>
<DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
	<title>Map It</title>
	<script src="http://maps.google.com/maps?file=api&v=1&key=[REDACTED]" type="text/javascript"></script>
	<link rel="stylesheet" type="text/css" href="/stylesheets/main3_css.php">
	<script language="javascript" type="text/javascript">
		function init() {
			var map = new GMap(document.getElementById("map"));
			map.addControl(new GSmallMapControl());
			map.addControl(new GMapTypeControl());
			map.centerAndZoom(new GPoint(-95, 41), 13);
			
			GEvent.addListener(map, 'click', function(overlay, point) {
				if (overlay) {
					map.removeOverlay(overlay);
				} else if (point) {
					map.clearOverlays();
					map.addOverlay(new GMarker(point));
					with (window.opener.document.forms['frmProfile']) {
						lat.value = point.y;
						lon.value = point.x;
					}
				}
			});

		}
	</script>
</head>
<body style="margin:5px;background-image:none;text-align:center;" onload="init()">
	Find your location on the map and click to add a marker. Once you have your marker on your "home" location, click OK to return to your profile.<br>
	<div id="map" style="width:768px;height:512px;border:1px solid #<?=BORDER_COLOR?>;margin-bottom:7px"></div>
	<input type="button" class="inputButton" style="width:100px" value="OK" onclick="window.close()">
</body>
</html>
