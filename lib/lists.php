<?php

include_once "database.php";

class Lists
{
 function addList($name)
 {
  $db = new database;
  $db->login();
	$observer = $_SESSION['deepskylog_id'];
  $sql = "INSERT INTO observerobjectlist(observerid, objectname, listname, objectplace, objectshowname) VALUES (\"$observer\", \"\", \"$name\", '0', \"\")";
  mysql_query($sql) or die(mysql_error());
  $db->logout();
 }
 
 function removeList($name)
 {
	if(array_key_exists('deepskylog_id',$_SESSION) && $_SESSION['deepskylog_id'] && ($this->checkList($name)==2))
  {
    $db = new database;
    $db->login();
    $observer = $_SESSION['deepskylog_id'];
    $sql = "DELETE FROM observerobjectlist WHERE observerid = \"$observer\" AND listname = \"$name\"";
    mysql_query($sql) or die(mysql_error());
	  $db->logout();
  }
 }
 
 function renameList($nameFrom, $nameTo)
 {
	if(array_key_exists('deepskylog_id',$_SESSION) && $_SESSION['deepskylog_id'] && ($this->checkList($nameFrom)==2))
  {
    $db = new database;
    $db->login();
    $observer = $_SESSION['deepskylog_id'];
    $sql = "UPDATE observerobjectlist SET listname = \"$nameTo\" WHERE observerid = \"$observer\" AND listname = \"$nameFrom\"";
    mysql_query($sql) or die(mysql_error());
	  $db->logout();
  }
 }
 
 function emptyList($listname)
 {
  $db = new database;
  $db->login();
	$observer = $_SESSION['deepskylog_id'];
	$sql = "DELETE FROM observerobjectlist WHERE observerid = \"$observer\" AND listname = \"$listname\" AND objectplace<>0";
  mysql_query($sql) or die(mysql_error());
  $db->logout();
 }
 
 function checkList($name)
 {
  $db = new database;
  $db->login();
	$retval = 0;
	if(substr($name,0,7)=="Public:")
	{  
    $sql = "SELECT listname FROM observerobjectlist WHERE listname = \"$name\"";	
	  $run = mysql_query($sql) or die(mysql_error());
	  if($get = mysql_fetch_object($run))
	    $retval = 1;
	}
	if(array_key_exists('deepskylog_id',$_SESSION) && $_SESSION['deepskylog_id'])
  {
	  $sql = "SELECT listname FROM observerobjectlist WHERE observerid = \"" . $_SESSION['deepskylog_id'] . "\" AND listname = \"$name\"";
	  $run = mysql_query($sql) or die(mysql_error());
	  if($get = mysql_fetch_object($run))
      $retval = 2;
	}
  $db->logout();
  return $retval;
 }
 
 function getLists()
 {
	$result=array();
	if(array_key_exists('deepskylog_id',$_SESSION))
	{
    $db = new database;
    $db->login();
  	$observer = $_SESSION['deepskylog_id'];
  	$sql = "SELECT DISTINCT observerobjectlist.listname " .
  	       "FROM observerobjectlist " .
  				 "WHERE observerid = \"" . $observer . "\"" . 
  				 "OR listname LIKE \"Public: %\" " . 
  				 "ORDER BY observerobjectlist.listname";
  	$run = mysql_query($sql) or die(mysql_error());
  	$get = mysql_fetch_object($run);
  	if ($get)
  	{
  	  $result1=array();
  	  $result2=array();
  		while($get)
  		{
  		  if(substr($get->listname,0,7)=="Public:")
  			  $result2[]=$get->listname;
  			else
  			  $result1[]=$get->listname;
  			$get = mysql_fetch_object($run);	
  		}
  		$result=array_merge($result1,$result2);
    }
  	$db->logout();
	}
	return $result;
 }
 
 function getMyLists()
 {
  $db = new database;
  $db->login();
	$observer = $_SESSION['deepskylog_id'];
	$sql = "SELECT DISTINCT observerobjectlist.listname " .
	       "FROM observerobjectlist " .
				 "WHERE observerid = \"" . $observer . "\"";
	$run = mysql_query($sql) or die(mysql_error());
	$result=array();
	while($get = mysql_fetch_object($run))
	  $result[]=$get->listname;
	$db->logout();
	return $result;
 }
 
 function listOwner($listname)
 {
  $db = new database;
  $db->login();
	$observer = $_SESSION['deepskylog_id'];
  $sql = "SELECT listname FROM observerobjectlist WHERE observerid = \"$observer\" AND listname = \"$listname\"";
  $run = mysql_query($sql) or die(mysql_error());
	$get = mysql_fetch_object($run);
  $db->logout();
	if ($get)
	  return 1;
	else
  	return 0;
 }
 
 function addObjectToList($name, $showname='')
 {
  $db = new database;
  $db->login();
	$observer = $_SESSION['deepskylog_id'];
	$listname = $_SESSION['listname'];
	if(!$showname)
	  $showname=$name;
  $sql = "SELECT objectplace AS ObjPl FROM observerobjectlist WHERE observerid = \"$observer\" AND listname = \"$listname\" AND objectname=\"$name\"";
  $run = mysql_query($sql) or die(mysql_error());
	$get = mysql_fetch_object($run);
  if(!$get)
	{
	  $sql = "SELECT MAX(objectplace) AS ObjPl FROM observerobjectlist WHERE observerid = \"$observer\" AND listname = \"$listname\"";
    $run = mysql_query($sql) or die(mysql_error());
	  $get = mysql_fetch_object($run);
    $objpl = ($get->ObjPl) + 1;
    $sql = "INSERT INTO observerobjectlist(observerid, objectname, listname, objectplace, objectshowname) VALUES (\"$observer\", \"$name\", \"$listname\", \"$objpl\", \"$showname\")";
    mysql_query($sql) or die(mysql_error());
  }
	$db->logout();
  if(array_key_exists('QOL',$_SESSION))
    unset($_SESSION['QOL']);
 }
 
 function addObservationToList($id)
 {
  $db = new database;
  $db->login();
	$observer = $_SESSION['deepskylog_id'];
	$listname = $_SESSION['listname'];
  $sql = "SELECT objectname, description FROM observations WHERE id=" . $id;
  $run = mysql_query($sql) or die(mysql_error());
	$get = mysql_fetch_object($run);
  if($get)
	{ $name = $get->objectname;
	  $description = $get->description;
    $sql = "SELECT objectplace AS ObjPl, description FROM observerobjectlist WHERE observerid = \"$observer\" AND listname = \"$listname\" AND objectname=\"$name\"";
    $run = mysql_query($sql) or die(mysql_error());
  	$get = mysql_fetch_object($run);
    if(!$get)
  	{
  	  $sql = "SELECT MAX(objectplace) AS ObjPl FROM observerobjectlist WHERE observerid = \"$observer\" AND listname = \"$listname\"";
      $run = mysql_query($sql) or die(mysql_error());
  	  $get = mysql_fetch_object($run);
      $objpl = ($get->ObjPl) + 1;
      $sql = "INSERT INTO observerobjectlist(observerid, objectname, listname, objectplace, objectshowname, description) VALUES (\"$observer\", \"$name\", \"$listname\", \"$objpl\", \"$name\", \"" . substr($description,0,1024) . "\")";
      mysql_query($sql) or die(mysql_error());
    }
		else
		{
      if($get->description)
			  $sql = "UPDATE observerobjectlist SET description = \"" . substr($get->description . " " . $description,0,1024) . "\" WHERE observerid = \"$observer\" AND listname = \"$listname\" AND objectname=\"$name\"";
      else
        $sql = "UPDATE observerobjectlist SET description = \"" . substr($description,0,1024) . "\" WHERE observerid = \"$observer\" AND listname = \"$listname\" AND objectname=\"$name\"";
			mysql_query($sql) or die(mysql_error());
		}
	}
	$db->logout();
  if(array_key_exists('QOL',$_SESSION))
    unset($_SESSION['QOL']);
 }
 
 function removeObjectFromList($name)
 {
  $db = new database;
  $db->login();
	$observer = $_SESSION['deepskylog_id'];
	$listname = $_SESSION['listname'];
  $sql = "SELECT objectplace AS ObjPl FROM observerobjectlist WHERE observerid = \"$observer\" AND listname = \"$listname\" AND objectname=\"$name\"";
  $run = mysql_query($sql) or die(mysql_error());
	$get = mysql_fetch_object($run);
  if($place=$get->ObjPl)
	{
	  $sql = "DELETE FROM observerobjectlist WHERE observerid = \"$observer\" AND listname = \"$listname\" AND objectname=\"$name\"";
    mysql_query($sql) or die(mysql_error());
    $sql = "UPDATE observerobjectlist SET objectplace=objectplace-1 WHERE observerid = \"$observer\" AND listname = \"$listname\" AND objectplace>$place";
    mysql_query($sql) or die(mysql_error());
	}
	$db->logout();
  if(array_key_exists('QOL',$_SESSION))
    unset($_SESSION['QOL']);
 }
 
 function ObjectDownInList($place)
 {
  $db = new database;
  $db->login();
	$observer = $_SESSION['deepskylog_id'];
	$listname = $_SESSION['listname'];
  if($place && ($place>1))
	{
    $sql = "UPDATE observerobjectlist SET objectplace=-1 WHERE observerid = \"$observer\" AND listname = \"$listname\" AND objectplace=$place";
    mysql_query($sql) or die(mysql_error());
    $sql = "UPDATE observerobjectlist SET objectplace=objectplace+1 WHERE observerid = \"$observer\" AND listname = \"$listname\" AND objectplace=$place-1";
    mysql_query($sql) or die(mysql_error());
    $sql = "UPDATE observerobjectlist SET objectplace=$place-1 WHERE observerid = \"$observer\" AND listname = \"$listname\" AND objectplace=-1";
    mysql_query($sql) or die(mysql_error());
  }
	$db->logout();
  if(array_key_exists('QOL',$_SESSION))
    unset($_SESSION['QOL']);
 }
	
 function ObjectUpInList($place)
 {
  $db = new database;
  $db->login();
	$observer = $_SESSION['deepskylog_id'];
	$listname = $_SESSION['listname'];
  $sql = "SELECT MAX(objectplace) AS ObjPl FROM observerobjectlist WHERE observerid = \"$observer\" AND listname = \"$listname\"";
  $run = mysql_query($sql) or die(mysql_error());
  $get = mysql_fetch_object($run);
	if($place && ($place<$get->ObjPl))
	{
    $sql = "UPDATE observerobjectlist SET objectplace=-1 WHERE observerid = \"$observer\" AND listname = \"$listname\" AND objectplace=$place";
    mysql_query($sql) or die(mysql_error());
    $sql = "UPDATE observerobjectlist SET objectplace=objectplace-1 WHERE observerid = \"$observer\" AND listname = \"$listname\" AND objectplace=$place+1";
    mysql_query($sql) or die(mysql_error());
    $sql = "UPDATE observerobjectlist SET objectplace=$place+1 WHERE observerid = \"$observer\" AND listname = \"$listname\" AND objectplace=-1";
    mysql_query($sql) or die(mysql_error());
  }
	$db->logout();
  if(array_key_exists('QOL',$_SESSION))
    unset($_SESSION['QOL']);
 }
 
 function ObjectFromToInList($from, $to)
 {
  $db = new database;
  $db->login();
	$observer = $_SESSION['deepskylog_id'];
	$listname = $_SESSION['listname'];
  $sql = "SELECT MAX(objectplace) AS ObjPl FROM observerobjectlist WHERE observerid = \"$observer\" AND listname = \"$listname\"";
  $run = mysql_query($sql) or die(mysql_error());
  $get = mysql_fetch_object($run);
	$max = $get->ObjPl;
	if(($from>0) && ($from<=$max) && ($to>0) && ($to<=$max) && ($from!=$to))
	{
	  if($from<$to)
		{
      $sql = "UPDATE observerobjectlist SET objectplace=-1 WHERE ((observerid=\"$observer\") AND (listname=\"$listname\") AND (objectplace=$from))";
      mysql_query($sql) or die(mysql_error());
      $sql = "UPDATE observerobjectlist SET objectplace=objectplace-1 WHERE ((observerid=\"$observer\") AND (listname=\"$listname\") AND (objectplace>$from) AND (objectplace<=$to))";
      mysql_query($sql) or die(mysql_error());
      $sql = "UPDATE observerobjectlist SET objectplace=$to WHERE ((observerid=\"$observer\") AND (listname=\"$listname\") AND (objectplace=-1))";
      mysql_query($sql) or die(mysql_error());
		}
		else
		{
	    $sql = "UPDATE observerobjectlist SET objectplace=-1 WHERE ((observerid=\"$observer\") AND (listname=\"$listname\") AND (objectplace=$from))";
      mysql_query($sql) or die(mysql_error());
      $sql = "UPDATE observerobjectlist SET objectplace=objectplace+1 WHERE ((observerid=\"$observer\") AND (listname=\"$listname\") AND (objectplace>=$to) AND (objectplace<$from))";
      mysql_query($sql) or die(mysql_error());
      $sql = "UPDATE observerobjectlist SET objectplace=$to WHERE ((observerid=\"$observer\") AND (listname=\"$listname\") AND (objectplace=-1))";
      mysql_query($sql) or die(mysql_error());
	  }
	}
  $db->logout();
  if(array_key_exists('QOL',$_SESSION))
    unset($_SESSION['QOL']);
 }
 
 function getObjectsFromList($listname)
 {
  include_once "../lib/objects.php";
  $db = new database;
  $db->login();
	$objects=new Objects;
  $obs=array();
	$observer = $_SESSION['deepskylog_id'];
	if(substr($listname,0,7)=="Public:")
    $sql = "SELECT observerobjectlist.objectname, observerobjectlist.objectplace, observerobjectlist.objectshowname, observerobjectlist.description FROM observerobjectlist " .
	         "JOIN objects ON observerobjectlist.objectname = objects.name " .
		    	 "WHERE listname = \"$listname\" AND objectname <>\"\"";
	else
    $sql = "SELECT observerobjectlist.objectname, observerobjectlist.objectplace, observerobjectlist.objectshowname, observerobjectlist.description FROM observerobjectlist " .
	         "JOIN objects ON observerobjectlist.objectname = objects.name " .
		    	 "WHERE observerid = \"$observer\" AND listname = \"$listname\" AND objectname <>\"\"";
  $run = mysql_query($sql) or die(mysql_error());
  while($get = mysql_fetch_object($run))
   if(!in_array($get->objectname, $obs))
	   $obs[$get->objectshowname] = array($get->objectplace,$get->objectname,$get->description);
	$obs=$objects->getSeenObjectDetails($obs, "D");	 

  $db->logout();
  return $obs;
 } 
 
 function checkObjectInMyActiveList($value)
 {
  $db = new database;
  $db->login();
  $observerid = $_SESSION['deepskylog_id'];
	$listname = $_SESSION['listname'];
  $sql = "SELECT observerobjectlist.objectplace FROM observerobjectlist WHERE observerid = \"$observerid\" AND objectname=\"$value\" AND listname=\"$listname\"";
  $run = mysql_query($sql) or die(mysql_error());
  $db->logout();
  $get = mysql_fetch_object($run);
  if($get)
    return $get->objectplace;
  else
    return 0;
 }

 function checkObjectMyOrPublicList($value, $list)
 {
  $db = new database;
  $db->login();
  $observerid = $_SESSION['deepskylog_id'];
  if(substr($list,0,7)=='Public:')
    $sql = "SELECT observerobjectlist.objectplace FROM observerobjectlist WHERE objectname=\"$value\" AND listname=\"$list\"";
  else
    $sql = "SELECT observerobjectlist.objectplace FROM observerobjectlist WHERE observerid = \"$observerid\" AND objectname=\"$value\" AND listname=\"$list\"";
  $run = mysql_query($sql) or die(mysql_error());
  $db->logout();
  $get = mysql_fetch_object($run);
  if($get)
    return $get->objectplace;
  else
   return 0;
 }
 
}
?>
