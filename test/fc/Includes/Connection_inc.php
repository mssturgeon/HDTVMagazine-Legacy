<?php
  //*****************************************************************************************
  //   Page Description: This page contains the database connections
  //*****************************************************************************************  
  // Update the relevant fields below  
  
  //Define your server name
  define('SERVER', 'localhost');
  //User name for the database
  define('USER', 'hdtv_web');
  //Password the database
  define('PASSWORD', 'hdtv03/01/2005');
  //Name of the database
  define('DB', 'hdtv_pg');
  // DB_SERVER supports 2 values: MSSQL (if you're using MS SQL Server) and MYSQL (if you're using mySQL).
  define('DB_SERVER', 'MYSQL');

  //Include the database handling functions
  include('functions.php');

  //Setup connection to the database
  $DBLink = db_connect(SERVER, USER, PASSWORD);
  db_select_db(DB, $DBLink);

?>