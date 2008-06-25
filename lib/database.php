<?php

// The database class collects all functions needed to login and logout 
// from the database.
//
// Version 0.1 : 16/08/2004, WDM
//
class Database
{
  var $databaseId;
  function login()
  {
    if($this->databaseId)
    {
    }
    else
    {
      // Logs in to the database. 
      include "setup/databaseInfo.php";

      $db = mysql_pconnect($host, $user, $pass);
      mysql_select_db($dbname, $db) or die("Cannot connect to database!");

      $this->databaseId = $db;
    }
  }

  // Logs out from the database. 
  function logout()
  {
    mysql_close($this->databaseId);
	  $this->databaseId = NULL;
  }
}

?>
