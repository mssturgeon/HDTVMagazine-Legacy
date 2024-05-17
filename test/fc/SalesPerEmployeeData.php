<?php
  include('./Includes/Connection_inc.php');
  include('./Includes/FC_Colors.php');

  // get the variables posted in the page form; prepend 'r_' to them
  import_request_variables("gp", "r_");

  $intCounter = 0;
  // retrieve the sales per employee info
  $query = " SELECT E.LastName, SUM(S.SubTotal) As Total
             FROM Employees E, [Order Subtotals] S, Orders O
             WHERE E.EmployeeID = O.EmployeeID
               AND O.OrderID = S.OrderID
               AND YEAR(O.OrderDate)=".$r_year."
             GROUP BY E.LastName
             ORDER BY E.LastName";
  $result = db_query($query, $DBLink) or die ('Error: Cannot get the categories !');

  // generate the XML for the chart
  $strXML = "<graph numDivLines='4' shownames='1' showvalues='0' showLegend='0' rotateNames='0'
    decimalPrecision='2' chartLeftMargin='5' chartRightMargin='5' chartTopMargin='15' numberPrefix='\$'
    divLineDecimalPrecision='0' limitsDecimalPrecision='0'>";
  while ($row = db_fetch_assoc($result)) {
    // increase the counter (range - 1 to intFCColors_count)
    $intCounter = $intCounter + 1;
    // append the data
    $strXML = $strXML."<set name='".$row["LastName"]."' value='".round($row["Total"], 2)."' color='".
       $arr_FCColors[$intCounter % $intFCColors_count]."'/>";
  }
  // close tags
  $strXML = $strXML."</graph>";
  // output the data
  echo($strXML);
?>