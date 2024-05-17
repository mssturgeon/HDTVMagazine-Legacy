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

  // get employee sales
  $query = "SELECT E.LastName, SUM(S.SubTotal) As Total
            FROM Employees E, [Order Subtotals] S, Orders O
            WHERE E.EmployeeID = O.EmployeeID
              AND O.OrderID = S.OrderID
              AND YEAR(O.OrderDate)=".$r_year."
            GROUP BY E.LastName ORDER BY E.LastName";
  $result = db_query($query, $DBLink) or die ('Error: Cannot get the employee sales !');
  // define data URL
  $strDataURL = urlencode("EmployeeSalesData.php?year=".$r_year);
?>
<body>
<!-- We create the visual data table here -->
<table width="1%" border="0" cellpadding="2" cellspacing="0" class="tableWithBorder">
	<tr>
		<td class="trdark"><div align="center"><span class="textboldlight">Employee Sales for <?php echo($r_year); ?></span></div></td>
	</tr>
	<tr>
		<td valign="middle" class="trlight">
			<div align="center">
			<OBJECT classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" WIDTH="500" HEIGHT="300" id="FCPie" ALIGN="">
				<PARAM NAME=movie VALUE="Charts/FC_2_3_Pie3D.swf?dataURL=<?php echo($strDataURL); ?>&chartWidth=500&chartHeight=300">
				<PARAM NAME=quality VALUE=high>
				<PARAM NAME=bgcolor VALUE=#FFFFFF>
				<EMBED src="Charts/FC_2_3_Pie3D.swf?dataURL=<?php echo($strDataURL); ?>&chartWidth=500&chartHeight=300" quality=high bgcolor=#FFFFFF WIDTH="500" HEIGHT="300" NAME="FCPie" ALIGN="" TYPE="application/x-shockwave-flash" PLUGINSPAGE="http://www.macromedia.com/go/getflashplayer"></EMBED>
			</OBJECT>
			<OBJECT classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"  codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" WIDTH="500" HEIGHT="200" id="FCPie" ALIGN="">
				<PARAM NAME=movie VALUE="Charts/FC_2_3_SSGrid.swf?dataURL=<?php echo($strDataURL); ?>&chartWidth=500&chartHeight=200">
				<PARAM NAME="FlashVars" value="numberItemsPerPage=9&bgColor=FFFFFF&alternateRowBgColor=EFEFDF&listRowDividerColor=B0BF9D">
				<PARAM NAME=quality VALUE=high>
				<PARAM NAME=bgcolor VALUE=#FFFFFF>
				<EMBED src="Charts/FC_2_3_SSGrid.swf?dataURL=<?php echo($strDataURL); ?>&chartWidth=500&chartHeight=200" FlashVars="numberItemsPerPage=9&bgColor=FFFFFF&alternateRowBgColor=EFEFDF&listRowDividerColor=B0BF9D" quality=high bgcolor=#FFFFFF WIDTH="500" HEIGHT="200" NAME="FCPie" ALIGN="" TYPE="application/x-shockwave-flash" PLUGINSPAGE="http://www.macromedia.com/go/getflashplayer"></EMBED>
			</OBJECT>
			</div></td>
	</tr>

	<tr>
		<td class="trmid" align="right"><a href="javascript:window.close();">[Close Window]</a></td>
	</tr>
</table>
</body>
</html>