<?php
  include('./Includes/Connection_inc.php');
  include('./Includes/FC_Colors.php');

  // get the variables posted in the page form; prepend 'r_' to them
  import_request_variables("gp", "r_");

  $intCounter = 0;
  $strDataXML = "";

  //get the data
  $strXML = "<graph bgColor='F2F2F2' showValues='0' decimalPrecision='2' anchorRadius='4' anchorBgAlpha='0'
    lineThickness='2' numberPrefix='\$' limitsDecimalPrecision='0' divLineDecimalPrecision='0'>";
  // the categories in this chart is 12 months abbreviated name
  $strCategoriesXML = "<categories>";
  for ($i = 1; $i <= 12; $i++){
    $strCategoriesXML = $strCategoriesXML."<category name='".date("M", mktime(0, 0, 0, $i, 1, 2005))."' />";
  }
  // retrieve the years
  $query = "SELECT DISTINCT YEAR(OrderDate) As year FROM Orders ORDER BY year";
  $result = db_query($query, $DBLink) or die ('Error: Cannot get the years !');

  // retrieve the sales info
  while ($row = db_fetch_assoc($result)) {
    $intCounter = $intCounter + 1;
    // create the SQL
    $query = "SELECT MONTH(O.OrderDate) As SalesMonth, SUM(S.SubTotal) As Total
              FROM [Order Subtotals] S, Orders O
              WHERE O.OrderID = S.OrderID
                AND YEAR(O.OrderDate)=".$row["year"]."
              GROUP BY MONTH(O.OrderDate)
              ORDER BY MONTH(O.OrderDate)";
    $sales_result = db_query($query, $DBLink) or die ('Error: Cannot get the sales !');
    // generate the <dataset> element for this year
    $sales = '';
    while ($row2 = db_fetch_assoc($sales_result)) {
      $sales[$row2["SalesMonth"]] = $row2["Total"];
    }
    $strDataXML = $strDataXML."<dataset anchorSides='".($intCounter + 2)."' seriesName='".$row["year"].
      "' color='".$arr_FCColors[$intCounter % $intFCColors_count]."' anchorBorderColor='".
      $arr_FCColors[$intCounter % $intFCColors_count]."'>";
    // go through the months
    for ($i = 1; $i <= 12; $i++) {
      // by default assume that the data for the month is not present
      // so set both value and alpha as 0
      if (isset($sales[$i])) {
        $salesValue = $sales[$i];
        $setAlpha = "100";
      } else {
        $salesValue = "0";
        $setAlpha = "0";
      }
      // create the <set> element
      $strDataXML = $strDataXML."<set value='".$salesValue."' alpha='".$setAlpha."'
        link='n-SalesComparisonDrill.php?year=".$row["year"]."&month=".$i."'/>";
    } // for ($i = 1; $i <= 12; $i++)
    $strDataXML = $strDataXML."</dataset>";
  } // while ($row = db_fetch_assoc($result))
  // close tags
  $strXML = $strXML.$strCategoriesXML."</categories>".$strDataXML."</graph>";
  // output the data
  echo($strXML);
?>