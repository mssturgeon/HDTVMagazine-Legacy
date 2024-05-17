<?php
  include('./Includes/Connection_inc.php');
  include('./Includes/FC_Colors.php');

  // get the variables posted in the page form; prepend 'r_' to them
  import_request_variables("gp", "r_");

  $intCounter = 0;

  // get the countries data
  $query = "SELECT ".top(10)." Country, SUM(ExtendedPrice) As Total, COUNT(DISTINCT OrderID) As orderNumber
            FROM Invoices
            WHERE YEAR(OrderDate)=".$r_year."
            GROUP BY Country
            ORDER BY SUM(ExtendedPrice) DESC".
            limit(10);
  $result = db_query($query, $DBLink) or die ('Error: Cannot get top 10 countries !');

  // generate the XML for the chart
  $strXML = "<graph shownames='1' showvalues='0' showLegend='0' rotateNames='1' showAnchors='0'
    chartLeftMargin='5' chartRightMargin='5' chartTopMargin='15' numberPrefix='\$' decimalPrecision='2'
    divLineDecimalPrecision='0' limitsDecimalPrecision='0'>";
  while ($row = db_fetch_assoc($result)){
    // increase the counter (range - 1 to intFCColors_count)
    $intCounter = $intCounter + 1;
    // append the data
    $strXML = $strXML."<set name='".$row["Country"]."' value='".$row["Total"]."' color='".
      $arr_FCColors[$intCounter % $intFCColors_count]."'/>";
  }
  // close tags
  $strXML = $strXML."</graph>";
  echo($strXML);
?>