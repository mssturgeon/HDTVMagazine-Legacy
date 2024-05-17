<?php
  include('./Includes/Connection_inc.php');
  include('./Includes/FC_Colors.php');

  // get the variables posted in the page form; prepend 'r_' to them
  import_request_variables("gp", "r_");

  $intCounter = 0;

  // retrieve the employee data
  $query = "SELECT E.LastName, SUM(S.SubTotal) As Total
            FROM Employees E, [Order Subtotals] S, Orders O
            WHERE E.EmployeeID = O.EmployeeID
              AND O.OrderID = S.OrderID
              AND YEAR(O.OrderDate)=".$r_year."
            GROUP BY E.LastName
            ORDER BY E.LastName";
  $result = db_query($query, $DBLink) or die ('Error: Cannot employee data !');

  // generate the XML for the chart
  $strXML = "<graph xAxisName='Employee' shownames='1' showvalues='1' showLegend='1' formatNumberScale='1'
    decimalPrecision='0' formatNumber='1' chartLeftMargin='0' chartRightMargin='0' chartTopMargin='0'
    numberPrefix='\$' pieRadius='150' pieYScale='60'>";
  while ($row = db_fetch_assoc($result)) {
    // increase the counter (range - 1 to intFCColors_count)
    $intCounter = $intCounter + 1;
    // append the data
    $strXML = $strXML."<set name='".$row["LastName"]."' value='".$row["Total"]."' color='".
      $arr_FCColors[$intCounter % $intFCColors_count]."'/>";
  }
  // close tags
  $strXML = $strXML."</graph>";
  // output the data
  echo($strXML);
?>