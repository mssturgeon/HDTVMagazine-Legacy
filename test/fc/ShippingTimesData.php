<?php
  include('./Includes/Connection_inc.php');
  include('./Includes/FC_Colors.php');

  // get the variables posted in the page form; prepend 'r_' to them
  import_request_variables("gp", "r_");

  $intCounter = 0;

  // get the shipping data
  $query = " SELECT O.ShipCountry as Country, S.CompanyName, AVG(DAY(O.ShippedDate-O.OrderDate)) As Average
             FROM Shippers S,[orders] O
             WHERE S.ShipperID=O.ShipVia
             GROUP BY O.ShipCountry, S.CompanyName
             ORDER BY AVG(DAY(O.ShippedDate-O.OrderDate)) ASC";
  $result = db_query($query, $DBLink) or die ('Error: Cannot get shipping data !');

  // generate the XML for the charts
  $strXML = "<graph shownames='1' showvalues='0' showLegend='0' rotateNames='1' showAnchors='0'
    formatNumberScale='1' decimalPrecision='1' formatNumber='0' chartLeftMargin='5' chartRightMargin='5'
    chartTopMargin='15' animation='0'>";
  $strXMLData = "";
  while ($row = db_fetch_assoc($result)){
    // increase the counter (range - 1 to intFCColors_count)
    $intCounter = $intCounter + 1;
    // append the data
    $strXMLData = $strXMLData."<set name='".$row["Country"]."' hoverText='".$row["Country"].", ".
      $row["CompanyName"]."' value='".$row["Average"]."' color='".
      $arr_FCColors[$intCounter % $intFCColors_count]."'/>";
  }
  // close tags
  $strXML = $strXML.$strXMLData."</graph>";
  // output the data
  echo ($strXML);
?>