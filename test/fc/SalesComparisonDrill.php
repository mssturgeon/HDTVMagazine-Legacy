<HTML>
<HEAD>
<TITLE>FusionCharts Demo Application - PHP/MS SQL Northwind Reporting Application</TITLE>
<LINK REL='Stylesheet' HREF='Style.css'>
</HEAD>
<?php
  // get the variables posted in the page form; prepend 'r_' to them
  import_request_variables("gp", "r_");
  $strDataURL = "SalesComparisonDrillData.php*year=".$r_year."*month=".$r_month;
?>
<body>
<table width="1%" border="0" cellpadding="2" cellspacing="0" class="tableWithBorder">
	<tr>
		<td class="trdark"><div align="center">
      <span class="textboldlight">Sales Comparison For <?php echo(date("F", mktime(0, 0, 0, $r_month, 1, 2005)).' '.$r_year); ?> (Click to drill)</span></div></td>
	</tr>
	<tr>
		<td valign="middle" class="trlight">
			<div align="center">
			<OBJECT classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" WIDTH="550" HEIGHT="350" id="FC2Column" ALIGN="">
				<PARAM NAME=movie VALUE="Charts/FC_2_3_MSColumnLine_DY_3D.swf?dataURL=<?php echo($strDataURL); ?>&chartWidth=550&chartHeight=350">
				<PARAM NAME=quality VALUE=high>
				<PARAM NAME=bgcolor VALUE=#FFFFFF>
				<EMBED src="Charts/FC_2_3_MSColumnLine_DY_3D.swf?dataURL=<?php echo($strDataURL); ?>&chartWidth=550&chartHeight=350" quality=high bgcolor=#FFFFFF WIDTH="550" HEIGHT="350" NAME="FC2Column" ALIGN="" TYPE="application/x-shockwave-flash" PLUGINSPAGE="http://www.macromedia.com/go/getflashplayer"></EMBED>
			</OBJECT>
			</div></td>
	</tr>
	<tr>
		<td class="trmid" align="right"><a href="javascript:window.close();">[Close Window]</a></td>
	</tr>
</table>
</body>
</html>