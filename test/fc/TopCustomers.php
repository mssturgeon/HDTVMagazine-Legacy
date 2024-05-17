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

  //get the data
  $query = "SELECT ".top(10)." CustomerName, SUM(ExtendedPrice) As Total, SUM(Quantity) As Quantity,
              SUM(Discount*ExtendedPrice) As Discount
            FROM Invoices
            WHERE YEAR(OrderDate)=".$r_year."
            GROUP BY CustomerName
            ORDER BY SUM(ExtendedPrice) DESC".
            limit(10);
  $result = db_query($query, $DBLink) or die ('Error: Cannot get top 10 customers !');
  $strDataURL = urlencode("TopCustomersData.php?year=".$r_year);
?>
<body>
<!-- We create the visual data table here -->
<table width="1%" border="0" cellpadding="2" cellspacing="0" class="tableWithBorder">
	<tr>
		<td class="trdark"><div align="center"><span class="textboldlight">Top 10 Customers</span></div></td>
	</tr>
	<tr>
		<td valign="middle" class="trlight">
			<div align="center">
			<OBJECT classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" WIDTH="550" HEIGHT="400" id="FC2Column" ALIGN="">
				<PARAM NAME=movie VALUE="Charts/FC_2_3_MSColumnLine_DY_3D.swf?dataURL=<?php echo ($strDataURL); ?>&chartWidth=550&ChartHeight=400">
				<PARAM NAME=quality VALUE=high>
				<PARAM NAME=bgcolor VALUE=#FFFFFF>
				<EMBED src="Charts/FC_2_3_MSColumnLine_DY_3D.swf?dataURL=<?php echo ($strDataURL); ?>&chartWidth=550&ChartHeight=400" quality=high bgcolor=#FFFFFF WIDTH="550" HEIGHT="400" NAME="FC2Column" ALIGN="" TYPE="application/x-shockwave-flash" PLUGINSPAGE="http://www.macromedia.com/go/getflashplayer"></EMBED>
			</OBJECT>
			</div></td>
	</tr>
	<tr>
		<td>
			<table width="100%" cellspacing="2" cellpadding="2" border="0" class="tableWithBorder">
				<tr>
					<td class="trdark"><span class="textbolddark">Customer Name</span></td>
					<td class="trdark" align='right'><span class="textbolddark">Amount</span></td>
					<td class="trdark" align='right'><span class="textbolddark">Discount</span></td>
					<td class="trdark" align='right'><span class="textbolddark">Quantity</span></td>
				</tr>
<?php
while ($row = db_fetch_assoc($result)){
  echo('<tr>');
  echo('<td class="trlight"><span class="text">'.$row["CustomerName"].'</span></td>');
  echo('<td class="trlight" align="right"><span class="text">'.$row["Total"].'</span></td>');
  echo('<td class="trlight" align="right"><span class="text">$'.round($row["Discount"],2).'</span></td>');
  echo('<td class="trlight" align="right"><span class="text">'.$row["Quantity"].'</span></td>');
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