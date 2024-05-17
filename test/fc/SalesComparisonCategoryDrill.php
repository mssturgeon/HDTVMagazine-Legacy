<HTML>
<HEAD>
<TITLE>FusionCharts Demo Application - PHP/MS SQL Northwind Reporting Application</TITLE>
<LINK REL='Stylesheet' HREF='Style.css'>
</HEAD>
<?php
  include('./Includes/Connection_inc.php');
  include('./Includes/FC_Colors.php');

  // get the variables posted in the page form; prepend 'r_' to them
  import_request_variables("gp", "r_");

  // define data URL
  $strDataURL = "SalesComparisonCategoryDrillData.php*year=".$r_year."*month=".$r_month."*catID=".$r_catID;
?>
<table width="1%" border="0" cellpadding="2" cellspacing="0" class="tableWithBorder">
	<tr>
		<td class="trdark"><div align="center"><span class="textboldlight">Sales Comparison For <?php echo(date("F", mktime(0, 0, 0, $r_month, 1, 2005)).' '.$r_year); ?></span></div></td>
	</tr>
	<tr>
		<td valign="middle" class="trlight">
			<div align="center">
			<OBJECT classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" WIDTH="650" HEIGHT="450" id="FC2Column" ALIGN="">
				<PARAM NAME=movie VALUE="Charts/FC_2_3_MSColumnLine_DY_3D.swf?dataURL=<?php echo($strDataURL); ?>&chartWidth=650&chartHeight=450">
				<PARAM NAME=quality VALUE=high>
				<PARAM NAME=bgcolor VALUE=#FFFFFF>
				<EMBED src="Charts/FC_2_3_MSColumnLine_DY_3D.swf?dataURL=<?php echo($strDataURL); ?>&chartWidth=650&chartHeight=450" quality=high bgcolor=#FFFFFF WIDTH="650" HEIGHT="450" NAME="FC2Column" ALIGN="" TYPE="application/x-shockwave-flash" PLUGINSPAGE="http://www.macromedia.com/go/getflashplayer"></EMBED>
			</OBJECT>
			</div></td>
	</tr>
	<tr>
		<td class="trmid" align="right"><a href="javascript:window.close();">[Close Window]</a></td>
	</tr>
</table>
</body>
</html>