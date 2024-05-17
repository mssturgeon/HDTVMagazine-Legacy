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
  // run the SQL to retrieve categories and Employees information
  $query = "SELECT CategoryID, CategoryName FROM Categories ORDER BY CategoryID ASC";
  $categories_result = db_query($query, $DBLink) or die ('Error: Cannot get the categories !');
  // retrieve the employees information
  $query = "SELECT LastName From Employees ORDER BY LastName ASC";
  $employees_result = db_query($query, $DBLink) or die ('Error: Cannot get the employees !');
  // define data URL
  $strDataURL = urlencode("SalesPerEmployeeData.php?year=".$r_year);
?>
<body>
<table width="1%" border="0" cellpadding="2" cellspacing="0" class="tableWithBorder">
	<tr>
		<td class="trdark"><div align="center"><span class="textboldlight">Sales per Employee</span></div></td>
	</tr>
	<tr>
		<td valign="middle" class="trlight">
			<div align="center">
			<OBJECT classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" WIDTH="550" HEIGHT="400" id="FC2Column" ALIGN="">
				<PARAM NAME=movie VALUE="Charts/FC_2_3_Column3D.swf?dataURL=<?php echo ($strDataURL); ?>&chartWidth=550&chartHeight=400">
				<PARAM NAME=quality VALUE=high>
				<PARAM NAME=bgcolor VALUE=#FFFFFF>
				<EMBED src="Charts/FC_2_3_Column3D.swf?dataURL=<?php echo ($strDataURL); ?>&chartWidth=550&chartHeight=400" quality=high bgcolor=#FFFFFF WIDTH="550" HEIGHT="400" NAME="FC2Column" ALIGN="" TYPE="application/x-shockwave-flash" PLUGINSPAGE="http://www.macromedia.com/go/getflashplayer"></EMBED>
			</OBJECT>
			</div></td>
	</tr>
	<tr>
		<td class="trmid" align="right"><a href="javascript:window.close();">[Close Window]</a></td>
	</tr>
</table>
</body>
</html>