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

  $intCounter = 0;

  //get the data
  $query = "SELECT O.ShipCountry as Country, S.CompanyName, AVG(DAY(O.ShippedDate-O.OrderDate)) As Average
            FROM Shippers S,[orders] O
            WHERE S.ShipperID=O.ShipVia
            GROUP BY O.ShipCountry, S.CompanyName
            ORDER BY AVG(DAY(O.ShippedDate-O.OrderDate)) ASC";
  $result = db_query($query, $DBLink) or die ('Error: Cannot get top 10 countries !');
  // define data URL
  $strDataURL = urlencode("ShippingTimesData.php?year=".$r_year);
?>
<body>
<!-- We create the visual data table here -->
<table width="1%" border="0" cellpadding="2" cellspacing="0" class="tableWithBorder">
	<tr>
		<td class="trdark"><div align="center"><span class="textboldlight">Shipping Times</span></div></td>
	</tr>
	<tr>
		<td valign="middle" class="trlight">
			<div align="center">
			<OBJECT classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" WIDTH="720" HEIGHT="420" id="FC2Column" ALIGN="">
				<PARAM NAME=movie VALUE="Charts/FC_2_3_Column3D.swf?dataURL=<?php echo($strDataURL); ?>&chartWidth=720&ChartHeight=420">
				<PARAM NAME=quality VALUE=high>
				<PARAM NAME=bgcolor VALUE=#FFFFFF>
				<EMBED src="Charts/FC_2_3_Column3D.swf?dataURL=<?php echo($strDataURL); ?>&chartWidth=720&ChartHeight=420" quality=high bgcolor=#FFFFFF WIDTH="720" HEIGHT="420" NAME="FC2Column" ALIGN="" TYPE="application/x-shockwave-flash" PLUGINSPAGE="http://www.macromedia.com/go/getflashplayer"></EMBED>
			</OBJECT>
			</div></td>
	</tr>
	<tr>
		<td>
			<table width="100%" cellspacing="2" cellpadding="2" border="0" class="tableWithBorder">
				<tr>
					<td class="trdark"><span class="textbolddark">Country</span></td>
					<td class="trdark"><span class="textbolddark">Company Name</span></td>
					<td class="trdark"><span class="textbolddark">Average Days</span></td>
				</tr>
<?php
  while ($row = db_fetch_assoc($result)){
    echo('<tr>');
    echo('<td class="trlight"><span class="text">'.$row["Country"].'</span></td>');
    echo('<td class="trlight"><span class="text">'.$row["CompanyName"].'</span></td>');
    echo('<td class="trlight"><span class="text">'.$row["Average"].'</span></td>');
    echo('</tr>');
  }
?>
			</table>
		</td>
	</tr>
	<tr>
		<td class="trmid" align="right"><a href="javascript:window.close();">[Close Window]</a></td>
	</tr>
</table>
</body>
</html>