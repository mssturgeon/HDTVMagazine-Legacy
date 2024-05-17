<?
	$sql_error_reporting = 'html'; # Other values are "text" and "none" (default)

# MySQL Connection Configuration Info
	$mysql_master = '66.113.104.122';
	$mysql_database = 'main';
	$mysql_username = isset($mysql_username) ? $mysql_username : 'hdtv_web';
	$mysql_password = isset($mysql_password) ? $mysql_password : '[REDACTED]';

# Open Connection
	$dblink = mysql_connect($mysql_master, $mysql_username, $mysql_password);
	if (!$dblink) die('Could not connect: ' . mysql_error());

/*
	if (mysql_errno() == 1203) { // 1203 == ER_TOO_MANY_USER_CONNECTIONS (mysqld_error.h)
		header("Location: http://your.site.com/alternate_page.php");
		exit;
	}
*/

# Select Database
	mysql_select_db ($mysql_database) or die ("Could not select database ($mysql_database)");

	function old_mQuery($qry) {
		global $dblink;

		$result = mysql_query($qry, $dblink)
			or die ('Error <b>'. mysql_errno() .'</b>: '. mysql_error() .'<br><br>Query:<br>'. $qry);
		return $result;
	}

	function mQuery($qry) {
		global $dblink, $sql_error_reporting;

		$result = mysql_query($qry, $dblink);
		if ($result === false) {
			if ($sql_error_reporting == 'html') {
				echo 'Error <b>'. mysql_errno() .'</b>: '. mysql_error() .'<br /><br />Query:<br />'. $qry;
			} elseif ($sql_error_reporting == 'text') {
				echo 'Error '. mysql_errno() .': '. mysql_error() ."\n\nQuery:\n". $qry;
			}
		}

		return $result;
	}
?>