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

  // get years from database
  $query = "SELECT DISTINCT YEAR(OrderDate) As year FROM Orders ORDER BY year";
  $result = db_query($query, $DBLink) or die ('Error: Cannot get years from database !');
  while ($row = db_fetch_assoc($result)) {
    $arr_years[] = $row['year'];
  }

  // if no year has been specified select the last one
  if (!isset($r_year) or ($r_year == "")) {
    $year = end($arr_years);
  } else {
    $year = $r_year;
  }

  //
  // CHART 1 - Top 5 Customers
  //

  // get top 5 customers data
  $query = "SELECT ".top(5)." CustomerName, SUM(ExtendedPrice) As Total, SUM(Quantity) As Quantity,
              SUM(Discount * ExtendedPrice) As Discount
            FROM Invoices
            WHERE YEAR(OrderDate)=".$year."
            GROUP BY CustomerName
            ORDER BY SUM(ExtendedPrice) DESC ".
            limit(5);
  $result = db_query($query, $DBLink) or die ('Error: Cannot get top 5 customers !');

  // generate the XML data for the chart
  $strChart1XML = "<graph PYAxisName='Amount' SYAxisName='Quantity' shownames='1' showvalues='0'
    rotateNames='1' showAnchors='1' decimalPrecision='2'  chartTopMargin='15' limitsDecimalPrecision='0'
    divLineDecimalPrecision='0' showCanvasBg='0' numDivLines='0' showLimits='0' hoverCapSepChar=' '
    showLegend='0'>";
  $strChart1CategoriesXML = "<categories>";
  // data set 1 represents the Amount for each customer
  $strChart1DataXML1 = "<dataset seriesname='' showValue='1' color='05CEFC' numberPrefix='\$' >";
  // data set 2 represents the discount allowed to each customer
  $strChart1DataXML2 = "<dataset seriesname='' showValue='1' color='FC3204' parentYAxis='S'
    numberSuffix=' pcs.' anchorSides='12' anchorRadius='3' anchorBorderColor='ff0000'>";
  // iterate through each data row
  while ($row = db_fetch_assoc($result)){
    $strChart1CategoriesXML = $strChart1CategoriesXML."<category name='".
      substr($row["CustomerName"], 0, 3)."...' hoverText='".$row["CustomerName"]."'/>";
    $strChart1DataXML1 = $strChart1DataXML1."<set value='".$row["Total"]."'/>";
    $strChart1DataXML2 = $strChart1DataXML2."<set value='".$row["Discount"]."'/>";
  }
  // closing tags
  $strChart1CategoriesXML = $strChart1CategoriesXML."</categories>";
  $strChart1DataXML1 = $strChart1DataXML1."</dataset>";
  $strChart1DataXML2 = $strChart1DataXML2."</dataset>";
  // entire XML - concatenation
  $strChart1XML = $strChart1XML.$strChart1CategoriesXML.$strChart1DataXML1.$strChart1DataXML2."</graph>";
//  echo(htmlspecialchars($strChart1XML));

  //
  // CHART 3 - Sales Per Employee
  //

  // get top 5 employees data
  $query = "SELECT ".top(5)." E.LastName, SUM(S.SubTotal) As Total
            FROM Employees E, [Order Subtotals] S, Orders O
            WHERE E.EmployeeID = O.EmployeeID
              AND O.OrderID = S.OrderID
              AND YEAR(O.OrderDate)=".$year."
            GROUP BY E.LastName
            ORDER BY E.LastName ".
            limit(5);
  $result = db_query($query, $DBLink) or die ('Error: Cannot get top 5 employees !');
  $intCounter = 0;
  // generate the XML for the chart
  $strChart3XML = "<graph shownames='0' showvalues='0' showLegend='1' decimalPrecision='2'
    yAxisName='Sales' chartLeftMargin='0' pieRadius='60' chartRightMargin='0' chartTopMargin='0'
    numberPrefix='\$' xAxisName='Employee'>";
  // create the HTML legend
  $strChart3LegendHTML = '<table width="100%" border="0" cellspacing="1" cellpadding="0">';
  while ($row = db_fetch_assoc($result)) {
    $intCounter = $intCounter + 1;
    $strChart3XML = $strChart3XML."<set name='".$row["LastName"]."' color='".
      $arr_FCColors[$intCounter % $intFCColors_count]."' value='".$row["Total"]."'/>";
    $strChart3LegendHTML = $strChart3LegendHTML."<tr><td bgcolor='".
      $arr_FCColors[$intCounter % $intFCColors_count]."' class='text'>&nbsp;</td><td class='text'>&nbsp;".
      $row["LastName"]."</td></tr>";
  }
  // closing tags
  $strChart3LegendHTML = $strChart3LegendHTML."</table>";
  $strChart3XML = $strChart3XML."</graph>";

  //
  // CHART 6 - Most Expensive Products
  //

  // get the 5 most expensive products data
  $query = "SELECT ".top(5)." ProductName, UnitPrice As Total
            FROM Products
            ORDER BY UnitPrice DESC".
            limit(5);
  // use 'LIMIT 0, 5' for mysql at the end of the statement instead of 'TOP 5'
  $result = db_query($query, $DBLink) or die ('Error: Cannot get top 5 most expensive products !');
  // generate the XML for the chart
  $strChart6XML = "<graph shownames='1' showvalues='0' showLegend='0' rotateNames='1' showAnchors='0'
    decimalPrecision='0' numberPrefix='\$' hoverCapSepChar=' ' chartRightMargin='0' chartLeftMargin='0'>";
  $intCounter = 0;
  $strChart6DataXML = "";
  while ($row = db_fetch_assoc($result)){
    $intCounter = $intCounter + 1;
    $strChart6DataXML = $strChart6DataXML."<set name='".substr($row["ProductName"], 0, 3).
      "...' hoverText='".str_replace("'", "`", $row["ProductName"])."' value='".$row["Total"]."' color='".
      $arr_FCColors[5 + ($intCounter % $intFCColors_count)]."'/>";
  }
  $strChart6XML = $strChart6XML.$strChart6DataXML."</graph>";

  //
  // CHART 4 - Top 5 Countries
  //

  // get the top 5 countries data
  $query = "SELECT".top(5)." Country, SUM(ExtendedPrice) As Total, COUNT(DISTINCT OrderID) As orderNumber
            FROM Invoices
            WHERE YEAR(OrderDate)=".$year."
            GROUP BY Country
            ORDER BY SUM(ExtendedPrice) DESC".
            limit(5);
  // use 'LIMIT 0, 5' for mysql at the end of the statement instead of 'TOP 5'
  $result = db_query($query, $DBLink) or die ('Error: Cannot get top 5 countries !');
  // generate the XML for the chart
  $strChart4XML = "<graph shownames='1' showvalues='0' showLegend='0' rotateNames='1' showAnchors='0'
    decimalPrecision='0' numberPrefix='\$' chartRightMargin='0' chartLeftMargin='0'>";
  $intCounter = 0;
  $strChart4DataXML = "";
  while ($row = db_fetch_assoc($result)){
    $intCounter = $intCounter + 1;
    $strChart4DataXML = $strChart4DataXML."<set name='".substr($row["Country"], 0, 3)."...' hoverText='".
      str_replace("'", "`", $row["Country"])."' value='".$row["Total"]."' color='".
      $arr_FCColors[$intCounter % $intFCColors_count]."'/>";
  }
  $strChart4XML = $strChart4XML.$strChart4DataXML."</graph>";

  //
  // CHART 7 - Shipping Times
  //

  // get the shipping times data
  $query = "SELECT".top(5)." O.ShipCountry as Country, AVG(DAY(O.ShippedDate-O.OrderDate)) As Average
            FROM Shippers S,[orders] O
            WHERE S.ShipperID=O.ShipVia
            GROUP BY O.ShipCountry
            ORDER BY AVG(DAY(O.ShippedDate-O.OrderDate)) ASC".
            limit(5);
  // use 'LIMIT 0, 5' for mysql at the end of the statement instead of 'TOP 5'
  $result = db_query($query, $DBLink) or die ('Error: Cannot get top 5 ccountries !');
  // generate the XML for the chart
  $strChart7XML = "<graph shownames='1' showvalues='0' showLegend='0' rotateNames='1' showAnchors='0'
    decimalPrecision='0' chartLeftMargin='5' chartRightMargin='5' chartTopMargin='15'>";
  $strChart7CategoriesXML = "<categories>";
  $strChart7DataXML = "<dataset seriesname='Time' color='FCCECC' showValue='1'>";
  while ($row = db_fetch_assoc($result)){
    $strChart7CategoriesXML = $strChart7CategoriesXML."<category name='".substr($row["Country"], 0, 3).
      "...' hoverText='".str_replace("'", "`", $row["Country"])."'/>";
    $strChart7DataXML = $strChart7DataXML."<set value='".$row["Average"]."'/>";
  }
  // closing tags
  $strChart7CategoriesXML = $strChart7CategoriesXML."</categories>";
  $strChart7DataXML = $strChart7DataXML."</dataset>";
  $strChart7XML = $strChart7XML.$strChart7CategoriesXML.$strChart7DataXML."</graph>";
?>
<table width="98%" border="0" cellpadding="2" cellspacing="0" class="tableWithBorder" align='center'>
  <tr>
    <td colspan="3" class="trdark"><div align="center"><span class="textboldlight">FusionCharts Demo Application</span></div></td>
  </tr>
  <form action="index.php" method="post">
  <tr>
    <td colspan="3" class="text" align="center">Please select the year for which you want to see the reports:
      <SELECT name='year' class='select' onChange="this.form.submit();">
<?php
  for ($i = 0; $i < count($arr_years); $i++){
    echo('<option value="'.$arr_years[$i].'"');
    if ($year == $arr_years[$i]) {
      echo(' selected');
    }
    echo('>'.$arr_years[$i].'</option>');
  }
?>
      </SELECT>
	  </td>
  </tr>
  </form>
  <tr>
    <td colspan="3">&nbsp;</td>
  </tr>
  <tr>
    <td width="175" valign="top"><table width="98%" border="0" cellspacing="0" cellpadding="2">
        <tr>
          <td><table width="98%" border="0" cellspacing="0" cellpadding="2">
              <tr>
                <td class="trdark"><div align="center"><span class="textboldlight">Top 5 Customers</span></div></td>
              </tr>
              <tr>
                <td><div align="center" class="text">
				<OBJECT classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" WIDTH="150" HEIGHT="150" id="FC2Column" ALIGN="">
					<PARAM NAME="FlashVars" value="&dataXML=<?php echo($strChart1XML); ?>">
					<PARAM NAME=movie VALUE="Charts/FC_2_3_MSColumnLine_DY_3D.swf?chartWidth=150&ChartHeight=150">
					<PARAM NAME=quality VALUE=high>
					<PARAM NAME=bgcolor VALUE=#FFFFFF>
					<EMBED src="FC_2_3FC_2_3_MSColumnLine_DY_3D.swf?chartWidth=150&ChartHeight=150" FlashVars="&dataXML=<?php echo($strChart1XML); ?>" quality=high bgcolor=#FFFFFF WIDTH="150" HEIGHT="150" NAME="FC2Column" ALIGN="" TYPE="application/x-shockwave-flash" PLUGINSPAGE="http://www.macromedia.com/go/getflashplayer"></EMBED>
				</OBJECT>
                  </div></td>
              </tr>
              <tr>
                <td class="text"><div align="center">
                    <p class="trmid"><a href="TopCustomers.php?year=<?php echo($year); ?>" target="_blank">See More...</a></p>
                  </div></td>
              </tr>
              <tr>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td class="trdark"><div align="center"><span class="textboldlight">Top 5 Countries</span></div></td>
              </tr>
              <tr>
                <td><div align="center" class="text">
				<OBJECT classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" WIDTH="150" HEIGHT="150" id="FC2Column" ALIGN="">
					<PARAM NAME="FlashVars" value="&dataXML=<?php echo($strChart4XML); ?>">
					<PARAM NAME=movie VALUE="Charts/FC_2_3_Column3D.swf?chartWidth=150&ChartHeight=150">
					<PARAM NAME=quality VALUE=high>
					<PARAM NAME=bgcolor VALUE=#FFFFFF>
					<EMBED src="FC_2_3FC_2_3_Column3D.swf?chartWidth=150&ChartHeight=150" FlashVars="&dataXML=<?php echo($strChart4XML); ?>" quality=high bgcolor=#FFFFFF WIDTH="150" HEIGHT="150" NAME="FC2Column" ALIGN="" TYPE="application/x-shockwave-flash" PLUGINSPAGE="http://www.macromedia.com/go/getflashplayer"></EMBED>
				</OBJECT>
                  </div></td>
              </tr>
              <tr>
                <td class="text"><div align="center">
                    <p class="trmid"><a href="TopCountries.php?year=<?php echo($year); ?>" target="_blank">See More...</a></p>
                  </div></td>
              </tr>
               <tr>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td class="trdark"><div align="center"><span class="textboldlight">Top 5 Expensive Products</span></div></td>
              </tr>
              <tr>
                <td><div align="center" class="text">
					<OBJECT classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"  codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" WIDTH="150" HEIGHT="150" id="FC2Column" ALIGN="">
						<PARAM NAME="FlashVars" value="&dataXML=<?php echo($strChart6XML); ?>">
						<PARAM NAME=movie VALUE="Charts/FC_2_3_Column3D.swf?chartWidth=150&ChartHeight=150">
						<PARAM NAME=quality VALUE=high>
						<PARAM NAME=bgcolor VALUE=#FFFFFF>
						<EMBED src="FC_2_3FC_2_3_Column3D.swf?chartWidth=150&ChartHeight=150" FlashVars="&dataXML=<?php echo($strChart6XML); ?>" quality=high bgcolor=#FFFFFF WIDTH="150" HEIGHT="150" NAME="FC2Column" ALIGN="" TYPE="application/x-shockwave-flash" PLUGINSPAGE="http://www.macromedia.com/go/getflashplayer"></EMBED>
					</OBJECT>
                  </div></td>
              </tr>
              <tr>
                <td>&nbsp;</td>
              </tr>
            </table></td>
        </tr>
      </table></td>
    <td valign="top"> <table width="98%" border="0" cellspacing="0" cellpadding="2">
        <tr>
          <td><table width="98%" border="0" cellpadding="2" cellspacing="0" class="tableWithBorder">
              <tr>
                <td class="trdark"><div align="center"><span class="textboldlight">Sales Comparison by Year (Click to drill down)</span></div></td>
              </tr>
              <tr>
                <td valign="middle" class="trlight">
                  <div align="center">
					<OBJECT classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" WIDTH="550" HEIGHT="300" id="FC2Column" ALIGN="">
						<PARAM NAME=movie VALUE="Charts/FC_2_3_MSColumn3D.swf?dataURL=SalesComparisonData.php&chartWidth=550&ChartHeight=300">
						<PARAM NAME=quality VALUE=high>
						<PARAM NAME=bgcolor VALUE=#FFFFFF>
						<EMBED src="Charts/FC_2_3_MSColumn3D.swf?dataURL=SalesComparisonData.php&chartWidth=550&ChartHeight=300" quality=high bgcolor=#FFFFFF WIDTH="550" HEIGHT="300" NAME="FC2Column" ALIGN="" TYPE="application/x-shockwave-flash" PLUGINSPAGE="http://www.macromedia.com/go/getflashplayer"></EMBED>
					</OBJECT>
                  </div></td>
              </tr>
            </table></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td><table width="98%" border="0" cellpadding="2" cellspacing="0" class="tableWithBorder">
              <tr>
                <td class="trdark"><div align="center"><span class="textboldlight">Sale Totals this Year (Click to drill)</span></div></td>
              </tr>
              <tr>
                <td valign="middle" class="trlight"> <div align="center">
					<OBJECT classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" WIDTH="550" HEIGHT="300" id="FC2Column" ALIGN="">
						<PARAM NAME=movie VALUE="Charts/FC_2_3_MSColumn2D.swf?dataURL=SalesTotalsCategoryData.php?year=<?php echo($year); ?>&chartWidth=550&ChartHeight=300">
						<PARAM NAME=quality VALUE=high>
						<PARAM NAME=bgcolor VALUE=#FFFFFF>
						<EMBED src="Charts/FC_2_3_MSColumn2D.swf?dataURL=SalesTotalsCategoryData.php?year=<?php echo($year); ?>&chartWidth=550&ChartHeight=300" quality=high bgcolor=#FFFFFF WIDTH="550" HEIGHT="300" NAME="FC2Column" ALIGN="" TYPE="application/x-shockwave-flash" PLUGINSPAGE="http://www.macromedia.com/go/getflashplayer"></EMBED>
					</OBJECT>
                  </div>
<?php
  // get a list of categories
	$query = "SELECT CategoryID, CategoryName FROM Categories ORDER BY CategoryID ASC";
  $result = db_query($query, $DBLink) or die ('Error: Cannot get top 5 customers !');
	$intCounter = 0;
	echo('<table width="95%" cellpadding="1" cellspacing="2" align="center"><tr>');
	while ($row = db_fetch_assoc($result)){
    $intCounter = $intCounter + 1;
    echo('<td bgcolor="'.$arr_FCColors[$intCounter % $intFCColors_count].'" class="text">
      &nbsp;</td><td class="text">'.$row["CategoryName"].'</td>');
  }
?>
				  </tr></table>
				</td>
              </tr>
            </table></td>
        </tr>
      </table> </td>
    <td width="175" valign="top"><table width="98%" border="0" cellspacing="0" cellpadding="2">
        <tr>
          <td><table width="98%" border="0" cellspacing="0" cellpadding="2">
              <tr>
                <td class="trdark"><div align="center"><span class="textboldlight">Sales per Employee</span></div></td>
              </tr>
              <tr>
                <td><div align="center" class="text">
				<OBJECT classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" WIDTH="150" HEIGHT="120" id="FusionCharts" ALIGN="">
					<PARAM NAME="FlashVars" value="&dataXML=<?php echo($strChart3XML); ?>">
					<PARAM NAME=movie VALUE="Charts/FC_2_3_Pie3D.swf?chartWidth=150&ChartHeight=120">
					<PARAM NAME=quality VALUE=high>
					<PARAM NAME=bgcolor VALUE=#FFFFFF>
					<EMBED src="Charts/FC_2_3_Pie3D.swf?chartWidth=150&ChartHeight=120" FlashVars="&dataXML=<?php echo($strChart3XML); ?>" quality=high bgcolor=#FFFFFF WIDTH="150" HEIGHT="120" NAME="FusionCharts" ALIGN="" TYPE="application/x-shockwave-flash" PLUGINSPAGE="http://www.macromedia.com/go/getflashplayer"></EMBED>
				</OBJECT>
				<OBJECT classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"  codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" WIDTH="150" HEIGHT="100" id="FC2Column" ALIGN="">
					<PARAM NAME="FlashVars" value="&dataXML=<?php echo($strChart3XML); ?>&numberItemsPerPage=5&bgColor=FFFFFF&alternateRowBgColor=EFEFDF&listRowDividerColor=B0BF9D">
					<PARAM NAME=movie VALUE="Charts/FC_2_3_SSGrid.swf?chartWidth=150&ChartHeight=100">
					<PARAM NAME=quality VALUE=high>
					<PARAM NAME=bgcolor VALUE=#FFFFFF>
					<EMBED src="FC_2_3FC_2_3_SSGrid.swf?chartWidth=150&ChartHeight=100" FlashVars="&dataXML=<?php echo($strChart3XML); ?>&numberItemsPerPage=5&bgColor=FFFFFF&alternateRowBgColor=EFEFDF&listRowDividerColor=B0BF9D" quality=high bgcolor=#FFFFFF WIDTH="150" HEIGHT="100" NAME="FC2Column" ALIGN="" TYPE="application/x-shockwave-flash" PLUGINSPAGE="http://www.macromedia.com/go/getflashplayer"></EMBED>
				</OBJECT>
                  </div></td>
              </tr>
              <tr>
                <td class="text"><div align="center">
                    <p class="trmid"><a href="EmployeeSales.php?year=<?php echo($year); ?>" target="_blank">See More...</a></p>
                  </div></td>
              </tr>

              <tr>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td class="trdark"><div align="center"><span class="textboldlight">Shipping Times (days)</span></div></td>
              </tr>
              <tr>
                <td><div align="center" class="text">
					<OBJECT classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" WIDTH="150" HEIGHT="150" id="FC2Column" ALIGN="">
						<PARAM NAME="FlashVars" value="&dataXML=<?php echo($strChart7XML); ?>">
						<PARAM NAME=movie VALUE="Charts/FC_2_3_MSColumn2D.swf?chartWidth=150&ChartHeight=150">
						<PARAM NAME=quality VALUE=high>
						<PARAM NAME=bgcolor VALUE=#FFFFFF>
						<EMBED src="FC_2_3FC_2_3_MSColumn2D.swf?chartWidth=150&ChartHeight=150" FlashVars="&dataXML=<?php echo($strChart7XML); ?>" quality=high bgcolor=#FFFFFF WIDTH="150" HEIGHT="150" NAME="FC2Column" ALIGN="" TYPE="application/x-shockwave-flash" PLUGINSPAGE="http://www.macromedia.com/go/getflashplayer"></EMBED>
					</OBJECT>
                  </div></td>
              </tr>
              <tr>
                <td class="text"><div align="center">
                    <p class="trmid"><a href="ShippingTimes.php?year=<?php echo($year); ?>" target="_blank">See More...</a></p>
                  </div></td>
              </tr>
              <tr>
                <td>&nbsp;</td>
              </tr>
            </table></td>
        </tr>
      </table></td>
  </tr>
</table>
<p align="center" class="text">&copy;All Rights Reserved - InfoSoft Global - 2004
  - <a href="http://www.InfoSoftGlobal.com" target="_blank">www.InfoSoftGlobal.com</a></p>
</BODY></html>