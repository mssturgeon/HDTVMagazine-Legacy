<?
	// MySQL Connection Configuration Info
//	$mysql_servername = 'www.hdtvmagazine.com';
	$mysql_servername = 'localhost';
	$mysql_database = 'main';
	$mysql_username = isset($mysql_username_override) ? $mysql_username_override : 'hdtv_web';
	$mysql_password = isset($mysql_password_override) ? $mysql_password_override : 'hdtv03/01/2005';

	// Open Connection
	$link = mysql_pconnect($mysql_servername, $mysql_username, $mysql_password)
		or die (date('Y/m/d H:i:s') .": Could not connect to $mysql_servername as $mysql_username\n");
/*
	if (mysql_errno() == 1203) { // 1203 == ER_TOO_MANY_USER_CONNECTIONS (mysqld_error.h)
		header("Location: http://your.site.com/alternate_page.php");
		exit;
	}
*/
	
	// Select Database
	mysql_select_db ($mysql_database)
		or die ("Could not select database ($mysql_database)");

	function mQuery($qry) {
		$result = mysql_query ($qry)
			or die ('Error <b>'. mysql_errno() .'</b> (line <b>'. __LINE__ .'</b>): '. mysql_error() .'<br><br>Query:<br>'. $qry);
		return $result;
	}
?>
