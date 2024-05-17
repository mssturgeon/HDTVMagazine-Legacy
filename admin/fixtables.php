<?php
// Check and repair all tables in a PHPBB structure

// verify that we're supposed to run
// change 'PX42m3' to something unique for your site, so it will only
// execute if you call it as 'http://mysite.com/fixtables.php?PX42m3=1'
if (!isset($_GET['somethingUNIQUE']))
{
   header("Location: http://127.0.0.1/");
   die();
}

define('IN_PHPBB', true);
$phpbb_root_path = '../forum/';
include($phpbb_root_path . 'config.php');

// connect to the database server
$db = mysql_connect($dbhost,$dbuser,$dbpasswd);
if (!$db) die("Unable to connect to database!\n");

// select the PHPBB database
mysql_select_db($dbname,$db);

// get a list of tables for this PHPBB
#$tablequery = "show tables like '".$table_prefix."%'";
$tablequery = "show tables";
$tablelist = mysql_query($tablequery, $db);


// cycle through them for repair
while ($tar = mysql_fetch_array($tablelist))
{
   $tablename = $tar[0];
   // output some verbosity for comfort
   echo "Now checking and repairing table $tablename ... ";
   $repres = mysql_query("REPAIR TABLE $tablename");
   $result = mysql_fetch_array($repres);
   echo $result['Msg_text'] . "<br>\n";
}

?>
