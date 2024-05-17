<?php
  //
  // server independent functions for handling the database
  //


  // database connection
  function db_connect($server, $user, $password){
    if (DB_SERVER == 'MSSQL') {
      $link = mssql_connect($server, $user, $password);
    } elseif (DB_SERVER == 'MYSQL'){
      $link = mysql_connect($server, $user, $password);
    } else {
      die('Error: Database server not supported !');
    }
    return $link;
  }

  // database selection
  function db_select_db($db, $link){
    if (DB_SERVER == 'MSSQL') {
      mssql_select_db($db, $link);
    } elseif (DB_SERVER == 'MYSQL'){
      mysql_select_db($db, $link);
    } else {
      die('Error: Database server not supported !');
    }
  }

  // run query
  function db_query($query, $link){
    if (DB_SERVER == 'MSSQL') {
      $result = mssql_query($query, $link);
    } elseif (DB_SERVER == 'MYSQL'){
      $result = mysql_query($query, $link);
    } else {
      die('Error: Database server not supported !');
    }
    return $result;
  }

  // fetch a row from a query
  function db_fetch_assoc($result){
    if (DB_SERVER == 'MSSQL') {
      $row = mssql_fetch_assoc($result);
    } elseif (DB_SERVER == 'MYSQL'){
      $row = mysql_fetch_assoc($result);
    } else {
      die('Error: Database server not supported !');
    }
    return $row;
  }

  // MSSQL SQL string for getting top 5 rows from a query
  function top($value){
    if (DB_SERVER == 'MSSQL') {
      $string = " TOP ".$value." ";
    } elseif (DB_SERVER == 'MYSQL'){
      $string = "";
    } else {
      die('Error: Database server not supported !');
    }
    return $string;
  }

  // MYSQL SQL string for getting top 5 rows from a query
  function limit($value){
    if (DB_SERVER == 'MSSQL') {
      $string = "";
    } elseif (DB_SERVER == 'MYSQL'){
      $string = " LIMIT 0, ".$value." ";
    } else {
      die('Error: Database server not supported !');
    }
    return $string;
  }
?>
