<?php
// The database class collects all functions needed to login and logout 
// from the database.
//
// Version 0.1 : 16/08/2004, WDM
//
class Database
{
  var $databaseId;
	
	public function __constructor()
	{  //$this->newlogin();
	}
	
  function newlogin()
  { if(!$this->databaseId)
    { $this->databaseId = mysql_pconnect($GLOBALS['host'], $GLOBALS['user'], $GLOBALS['pass']);
      mysql_select_db($GLOBALS['dbname'], $this->databaseId) or die("Cannot connect to database!");
    }
		return $this->databaseId;
  }
	
  function login()
  { /*
	  if(!$this->databaseId)
    { $this->databaseId = mysql_pconnect($host, $user, $pass);
      mysql_select_db($GLOBALS['dbname'], $db) or die("Cannot connect to database!");
    }
		return $this->databaseId;
		*/
  }

  function logout()
  { //mysql_close($this->databaseId);
	  //$this->databaseId = NULL;
  }
	
	function execSQL($sql)
	{ if(!$this->databaseId) {echo "Database connection lost..."; $this->newLogin();}
		$run = mysql_query($sql) or die(mysql_error());
  }
	function selectSingleValue($sql,$name,$nullvalue='')
	{ if(!$this->databaseId) {echo "Database connection lost..."; $this->newLogin();}
	  $run = mysql_query($sql) or die(mysql_error());
    $get = mysql_fetch_object($run);
		if($get) return $get->$name;
		else     return $nullvalue;
  }
	function selectSingleArray($sql,$name)
	{ if(!$this->databaseId) {echo "Database connection lost..."; $this->newLogin();}
	  $run = mysql_query($sql) or die(mysql_error());
    while($get = mysql_fetch_object($run))
		  $result[]=$get->$name;
		if($result) return $result;
		else        return array();
  }
	function selectKeyValueArray($sql,$key,$value)
	{ if(!$this->databaseId) {echo "Database connection lost..."; $this->newLogin();}
	  $run = mysql_query($sql) or die(mysql_error());
    while($get = mysql_fetch_object($run))
		  $result[$get->$key]=$get->$value;
		if($result) return $result;
		else        return array();
  }
	function selectRecordset($sql)
	{ if(!$this->databaseId) {echo "Database connection lost..."; $this->newLogin();}
	  $run = mysql_query($sql) or die(mysql_error());
		return $run;
  }
}
$objDatabase=new Database;
?>
