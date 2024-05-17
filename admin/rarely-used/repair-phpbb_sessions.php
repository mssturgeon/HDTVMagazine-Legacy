<?
	header('Content-Type: text/plain');

	define('BASE_DIR', '/var/www/html');
#	require(BASE_DIR .'/includes/constants.php');
#	require(BASE_DIR .'/includes/lib_common.php');
	require(BASE_DIR .'/includes/lib_mysql.php');
#	require(BASE_DIR .'/includes/lib_admin.php');
	
	echo "Repairing phpbb_sessions table...";
	$result = mQuery("REPAIR TABLE phpbb_sessions");
	$row = mysql_fetch_assoc($result);
	echo "Done\n\n";
	echo "Table: $row[table]\n".
	"Op: $row[op]\n".
	"Message type: $row[msg_type]\n".
	"Messate text: $row[msg_text]\n";
?>
