<?php
  include('./Includes/Connection_inc.php');
  include('./Includes/FC_Colors.php');

  // get the variables posted in the page form; prepend 'r_' to them
  import_request_variables("gp", "r_");

  $intCounter = 0;
  $strDataXML = "";

  // get the list of years
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

  // generate the XML data for the chart
  $strXML = "<graph bgColor='F2F2F2' showValues='0' showLegend='0' rotateNames='0' decimalPrecision='2'
    anchorRadius='5' anchorBgAlpha='0' lineThickness='3' numberPrefix='\$' divLineDecimalPrecision='0'
    limitsDecimalPrecision='0'>";
  // the categories in this chart is 12 months abbreviated name
  // the categories in this chart is 12 months abbreviated name
  $strCategoriesXML = "<categories>";
  for ($i = 1; $i <= 12; $i++){
    $strCategoriesXML = $strCategoriesXML."<category name='".date("M", mktime(0, 0, 0, $i, 1, 2005))."' />";
  }
  $strCategoriesXML = $strCategoriesXML."</categories>";

  // retrieve the product categories from database
  $query = "SELECT CategoryID, CategoryName FROM Categories ORDER BY CategoryID ASC";
  $result = db_query($query, $DBLink) or die ('Error: Cannot get product categories !');
  // iterate through the categories and get sales for each category
  while ($row = db_fetch_assoc($result)){
	  $intCounter = $intCounter + 1;
	  // generate the XML for the chart
	  $strDataXML = $strDataXML."<dataset seriesName='".$row["CategoryName"]."' color='".
      $arr_FCColors[$intCounter % $intFCColors_count]."'>";
	  // create the SQL query to get category wise sales
	  $query = "SELECT MONTH(I.OrderDate) As SalesMonth, SUM(I.ExtendedPrice) As Total
              FROM Products P, Invoices I
              WHERE P.CategoryID = ".$row["CategoryID"]."
                AND P.ProductID = I.ProductID
                AND YEAR(I.OrderDate) = ".$r_year."
              GROUP BY MONTH(I.OrderDate)";
    $sales_result = db_query($query, $DBLink) or die ('Error: Cannot get sales !');
    $sales = '';
    while ($row2 = db_fetch_assoc($sales_result)) {
      $sales[$row2["SalesMonth"]] = $row2["Total"];
    }
    // go through the months
    for ($i = 1; $i <= 12; $i++) {
      // by default assume that the data for the month is not present
      // so set both value and alpha as 0
      if (isset($sales[$i])) {
        $setValue = $sales[$i];
        $setAlpha = "100";
      } else {
        $setValue = "0";
        $setAlpha = "0";
      }
      // create the <set> element
      $strDataXML = $strDataXML."<set value='".$setValue."' alpha='".$setAlpha.
        "' link='n-SalesComparisonCategoryDrill.php?year=".$r_year."&month=".$i. "&catID=".
        $row["CategoryID"]."'/>";
    }
	  // closing <dataset> element
    $strDataXML = $strDataXML."</dataset>";
  }
  // close tags
  $strXML = $strXML.$strCategoriesXML.$strDataXML."</graph>";
  // output the data
  echo ($strXML);
?>