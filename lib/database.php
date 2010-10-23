<?php  // The database class collects all functions needed to login and logout from the database.

class Database
{ private $databaseId;
  private function mysql_query_encaps($sql)
  { global $loggedUser, $developversion;
    if($developversion)
      mysql_query("INSERT INTO logging(loginid, logdate, logtime, logurl, navigator, screenresolution, language, sqlstatement) 
                       VALUES(\"".($loggedUser?$loggedUser:"anonymous")."\", ".
                                date('Ymd').", ".date('His').", '', '', '', '', '".$sql."');");
    $run = mysql_query($sql) or die(mysql_error());
    return $run;
  }
  public function execSQL($sql)
	{ if(!$this->databaseId) {echo "Database connection lost..."; $this->newLogin();}
		$run = $this->mysql_query_encaps($sql) or die(mysql_error());
  }
	public function selectKeyValueArray($sql,$key,$value)
	{ if(!$this->databaseId) {echo "Database connection lost..."; $this->newLogin();}
	  $run = $this->mysql_query_encaps($sql) or die(mysql_error());
    while($get = mysql_fetch_object($run))
		  $result[$get->$key]=$get->$value;
		if(isset($result)) return $result;
		else               return array();
  }
	public function selectRecordset($sql)
	{ if(!$this->databaseId) {echo "Database connection lost..."; $this->newLogin();}
	  $run = $this->mysql_query_encaps($sql) or die(mysql_error());
		return $run;
  }
	public function selectRecordArray($sql)
	{ if(!$this->databaseId) {echo "Database connection lost..."; $this->newLogin();}
	  $result=array();
		$run = $this->mysql_query_encaps($sql) or die(mysql_error());
		if($get = mysql_fetch_object($run))
		  while(list($key,$value)=each($get))
			  $result[$key]=$value;
		return $result;
  }
	public function selectRecordsetArray($sql)
	{ if(!$this->databaseId) {echo "Database connection lost..."; $this->newLogin();}
	  $result=array();
		$run = $this->mysql_query_encaps($sql) or die(mysql_error());
		while($get=mysql_fetch_object($run))
		{ $resultparts=array();
		  while(list($key,$value)=each($get))
			  $resultparts[$key]=$value;
		  $result[]=$resultparts;
		}
		return $result;
  }
  public function selectSingleArray($sql,$name)
	{ if(!$this->databaseId) {echo "Database connection lost..."; $this->newLogin();}
	  $run = $this->mysql_query_encaps($sql) or die(mysql_error());
    $result=array();
		while($get = mysql_fetch_object($run))
		  $result[]=$get->$name;
		return $result;
  }
  public function selectSingleValue($sql,$name,$nullvalue='')
	{ if(!$this->databaseId) {echo "Database connection lost..."; $this->newLogin();}
	  $run = mysql_query($sql) or die(mysql_error());
    $get = mysql_fetch_object($run);
		if($get) 
		  if ($get->$name!='')
		    return $get->$name;
		  else 
		    return $nullvalue;
		else     
		  return $nullvalue;
  }
  function __construct()
	{ global $dbname,$host,$user,$pass;
		if(!$this->databaseId)
    { $this->databaseId = mysql_pconnect($host, $user, $pass);
      mysql_select_db($dbname, $this->databaseId) or die("Cannot connect to database!");
    }
		return $this->databaseId;
	}
}
?>
