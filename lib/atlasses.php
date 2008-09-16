<?php

class Atlasses
{
  function getAtlasses()
	{ $atlasses = array();
	  $db = new database;
    $db->login();
    $run = mysql_query('SELECT atlasCode, atlasNr FROM atlasses;') 
		       or die(mysql_error());
    $db->logout();
	  while($get = mysql_fetch_object($run))
		  $atlasses[$get->atlasNr]=array($get->atlasCode, 'AtlasName' . $get->atlasCode);
	  return $atlasses;
	}
  function getSortedAtlasses()
	{ $atlasses = array();
	  $db = new database;
    $db->login();
    $run = mysql_query('SELECT atlasCode, atlasNr FROM atlasses;') 
		       or die(mysql_error());
    $db->logout();
	  while($get = mysql_fetch_object($run))
	    $atlasses[$get->atlasNr]=$GLOBALS['AtlasName' . $get->atlasCode];
	  asort($atlasses);
		return $atlasses;
	}
}
?>
