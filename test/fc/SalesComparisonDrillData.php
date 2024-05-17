<?php
  include('./Includes/Connection_inc.php');
  include('./Includes/FC_Colors.php');

  // get the variables posted in the page form; prepend 'r_' to them
  import_request_variables("gp", "r_");

  $intCounter = 0;

  // get the sales data
  $query = "SELECT C.CategoryID, C.CategoryName, SUM(Quantity) As Quantity, SUM(I.ExtendedPrice) As Total
            FROM Products P, Invoices I, Categories C
            WHERE P.CategoryID = C.CategoryID
              AND P.ProductID = I.ProductID
              AND YEAR(I.OrderDate)=".$r_year."
              AND MONTH(I.OrderDate)=".$r_month."
            GROUP BY C.CategoryID, C.CategoryName";
  $result = db_query($query, $DBLink) or die ('Error: Cannot get sales data !');

  // generate the XML for the chart
  $strXML = "<graph caption='Sales by Product Category' showValues='0' PYAxisName='Amount'
    SYAxisName='Quantity'>";
  $strXMLCategories = "<categories>";
  $strXMLData1 = "<dataset seriesName='Amount' color='".$arr_FCColors[3]."' numberPrefix='\$'>";
  $strXMLData2 = "<dataset seriesName='Quantity' color='".$arr_FCColors[1]."' anchorBorderColor='".
    $arr_FCColors[1]."'  parentYAxis='S'>";
  while ($row = db_fetch_assoc($result)){
    // increase the counter (range - 1 to intFCColors_count)
    $intCounter = $intCounter + 1;
    // append categories name
    $strXMLCategories = $strXMLCategories."<category name='".substr($row["CategoryName"], 0, 5).
      "...' hoverText='".$row["CategoryName"]."' />";
    // append the data
    $strXMLData1 = $strXMLData1."<set value='".$row["Total"].
      "' link='n-SalesComparisonCategoryDrill.php?year=".$r_year."&month=".$r_month."&catID=".
      $row["CategoryID"]."'/>";
    $strXMLData2  = $strXMLData2."<set value='".$row["Quantity"].
      "' link='n-SalesComparisonCategoryDrill.php?year=".$r_year."&month=".$r_month."&catID=".
      $row["CategoryID"]."'/>";
  }
  // closing tags
  $strXMLCategories = $strXMLCategories."</categories>";
  $strXMLData1 = $strXMLData1."</dataset>";
  $strXMLData2 = $strXMLData2."</dataset>";
  $strXML = $strXML.$strXMLCategories.$strXMLData1.$strXMLData2."</graph>";
  // output the data
  echo($strXML);
?>