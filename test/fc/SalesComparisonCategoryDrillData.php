<?php
  include('./Includes/Connection_inc.php');
  include('./Includes/FC_Colors.php');

  // get the variables posted in the page form; prepend 'r_' to them
  import_request_variables("gp", "r_");

  // get the sales data
  $query = "SELECT C.CategoryName, P.ProductName, SUM(Quantity) As Quantity, SUM(I.ExtendedPrice) As Total
            FROM Products P, Invoices I, Categories C
            WHERE P.CategoryID = C.CategoryID
              AND P.ProductID = I.ProductID
              AND YEAR(I.OrderDate) = ".$r_year."
              AND MONTH(I.OrderDate) = ".$r_month."
              AND C.CategoryID = ".$r_catID."
            GROUP BY C.CategoryName, P.ProductName";
  $result = db_query($query, $DBLink) or die ('Error: Cannot get sales data !');
  // generate the XML for the chart
  $strXML = "<graph caption='Sales by Product' showValues='0' divLineDecimalPrecision='1'
    limitsDecimalPrecision='1'>";
  $strXMLCategories = "<categories>";
  $strXMLData1 = "<dataset seriesName='Total Amount' color='00CCFF'  numberPrefix='\$' >";
  $strXMLData2 = "<dataset seriesName='Quantity' parentYAxis='S' color='009933' anchorBorderColor='009933'>";
  while ($row = db_fetch_assoc($result)){
    // append categories name
    $strXMLCategories = $strXMLCategories."<category name='".
      substr(str_replace("'", "`", $row["ProductName"]), 0, 5)."...' hoverText='".
      str_replace("'", "`", $row["ProductName"])."' />";
    // append the data
    $strXMLData1 = $strXMLData1."<set value='".$row["Total"]."' />";
    $strXMLData2 = $strXMLData2."<set value='".$row["Quantity"]."' />";
  }
  // closing tags
  $strXMLCategories = $strXMLCategories."</categories>";
  $strXMLData1 = $strXMLData1."</dataset>";
  $strXMLData2 = $strXMLData2."</dataset>";
  $strXML = $strXML.$strXMLCategories.$strXMLData1.$strXMLData2."</graph>";
  // output the data
  echo($strXML);
?>