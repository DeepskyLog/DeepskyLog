<?php
// observerqueries.php
// manages the observation queries a user can makes, store, recall or remove

global $inIndex;
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";

class Observerqueries
{ function getObserverQueries($querytype)
  { global $objDatabase_new,$loggedUser;
    $sql = "SELECT * FROM legacy_observerqueries WHERE observerid=\"".$loggedUser."\" AND observerquerytype=\"".$querytype."\" ORDER BY observerqueryname;";
    return $objDatabase_new->selectRecordsetArray($sql);
  }
  function removeObserverQuery($querytype,$queryname)
  { global $objDatabase_new, $loggedUser;
    $sql="DELETE FROM legacy_observerqueries WHERE (observerid=\"".$loggedUser."\" AND 
                                     observerquerytype=\"".$querytype."\" AND
                                     observerqueryname=\"".$queryname."\");";
    $objDatabase_new->execSQL($sql);
    $sql="SELECT * FROM legacy_observerqueries WHERE observerid=\"".$loggedUser."\" AND observerquerytype=\"".$querytype."\" ORDER BY observerqueryname;";
	  return $objDatabase_new->selectRecordsetArray($sql);
  }
  function saveObserverQuery($querytype,$queryname)
  { global $objDatabase_new,$loggedUser;
    reset($_GET);
    $temp="";
    foreach ($_GET as $key => $value)
      $temp=$temp.$key."=".$value."&";
    $sql="INSERT INTO legacy_observerqueries(observerid,observerquerytype,observerqueryname,observerquery) 
                             VALUES (\"".$loggedUser."\", 
                                     \"".$querytype."\",
                                     \"".$queryname."\",
                                     \"".($temp)."\");";
    $objDatabase_new->execSQL($sql);
    $sql="SELECT * FROM legacy_observerqueries WHERE observerid=\"".$loggedUser."\" AND observerquerytype=\"".$querytype."\" ORDER BY observerqueryname;";
	  return $objDatabase_new->selectRecordsetArray($sql);
  }
}
?>