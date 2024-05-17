<?php
  include('./Includes/Connection_inc.php');
  include('./Includes/FC_Colors.php');

  // get the variables posted in the page form; prepend 'r_' to them
  import_request_variables("gp", "r_");

  // get the customer data
  $query = "SELECT ".top(10)." CustomerName, SUM(ExtendedPrice) As Total, SUM(Quantity) As Quantity,
              SUM(Discount*ExtendedPrice) As Discount
            FROM Invoices
            WHERE YEAR(OrderDate)=".$r_year."
            GROUP BY CustomerName
            ORDER BY SUM(ExtendedPrice) DESC".
            limit(10);
  $result = db_query($query, $DBLink) or die ('Error: Cannot get top 10 customers !');

  // generate the XML for the chart
  $strXML = "<graph PYAxisName='Amount' SYAxisName='Quantity' shownames='1' showvalues='0' showLegend='1'
    rotateNames='1' formatNumberScale='1' decimalPrecision='2' limitsDecimalPrecision='0'
    divLineDecimalPrecision='1' formatNumber='1' chartTopMargin='15'>";
  // initialize categories element
  $strXMLCategories = "<categories>";
  // initialize dataSet element
  $strXMLData1 = "<dataset seriesname='Amount' showValues='0' color='05CEFC' parentYAxis='P'
    numberPrefix='\$'>";
  $strXMLData2 = "<dataset seriesname='Discount' showValues='0' color='FC3204' parentYAxis='P'
    numberPrefix='\$'>";
  $strXMLData3 = "<dataset seriesname='Quantity' showValues='0' color='1C1ADC' anchorBorderColor='1C1ADC'
    parentYAxis='S'>";
  while ($row = db_fetch_assoc($result)){
    // append the category name
    $strXMLCategories = $strXMLCategories."<category name='".substr($row["CustomerName"], 0, 5).
      "...' hoverText='".$row["CustomerName"]."'/>";
    // append the data
    $strXMLData1 = $strXMLData1."<set value='".$row["Total"]."' />";
    $strXMLData2 = $strXMLData2."<set value='".$row["Discount"]."' />";
    $strXMLData3 = $strXMLData3."<set value='".$row["Quantity"]."' />";
  }
  // close tags
  $strXMLCategories = $strXMLCategories."</categories>";
  $strXMLData1 = $strXMLData1."</dataset>";
  $strXMLData2 = $strXMLData2."</dataset>";
  $strXMLData3 = $strXMLData3."</dataset>";
  // concatenate to form the whole XML document
  $strXML = $strXML.$strXMLCategories.$strXMLData1.$strXMLData2.$strXMLData3;
  $strXML = $strXML."</graph>";
  // output the data
  echo($strXML);
?>