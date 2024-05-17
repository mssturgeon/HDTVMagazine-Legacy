<?
	// This line will stream the file to the user rather than spray it across the screen
	header("Content-type: application/octet-stream");
	header("Content-Disposition: attachment; filename=user.xls");
	header("Pragma: no-cache");
	header("Expires: 0");
	
//	require('../includes/config_mysql.php');

//	$result = mysql_query(stripslashes($_POST['query']));
	$result = mysql_query(stripslashes("SELECT id, created, DATE_FORMAT(modified, '%Y-%m-%d') modified, last_access, user_name, first_name, last_name, email_address FROM user WHERE modified = '0000-00-00' ORDER BY last_name"));
	$count = mysql_num_fields($result);

	$header = '';
	for ($i = 0; $i < $count; $i++){
		 $header .= mysql_field_name($result, $i)."\t";
	}
	
	while($row = mysql_fetch_row($result)){
	  $line = '';
	  foreach($row as $value){
		 if(!isset($value) || $value == ""){
			$value = "\t";
		 } else {
			// important to escape any quotes to preserve them in the data.
			$value = str_replace('"', '""', $value);
			
			// needed to encapsulate data in quotes because some data might be multi line.
			// the good news is that numbers remain numbers in Excel even though quoted.
			$value = '"' . $value . '"' . "\t";
		 }
		 $line .= $value;
	  }
	  $data .= trim($line)."\n";
	}
  	// this line is needed because returns embedded in the data have "\r"
  	// and this looks like a "box character" in Excel
	$data = str_replace("\r", "", $data);
	
	// Nice to let someone know that the search came up empty.
	// Otherwise only the column name headers will be output to Excel.
	if ($data == '') {
	  $data = "\nno matching records found\n";
	}
	
	echo $header."\n".$data; 
?> 