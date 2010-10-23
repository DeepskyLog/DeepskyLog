<?php
class Observerqueries
{ function getObserverQueries($querytype)
  { global $objDatabase,$loggedUser;
    $sql = "SELECT * FROM observerqueries WHERE observerid=\"".$loggedUser."\" AND observerquerytype=\"".$querytype."\" ORDER BY observerqueryname;";
    return $objDatabase->selectRecordsetArray($sql);
  }
  function removeObserverQuery($querytype,$queryname)
  { global $objDatabase, $loggedUser;
    $sql="DELETE FROM observerqueries WHERE (observerid=\"".$loggedUser."\" AND 
                                     observerquerytype=\"".$querytype."\" AND
                                     observerqueryname=\"".$queryname."\");";
    $objDatabase->execSQL($sql);
    $sql="SELECT * FROM observerqueries WHERE observerid=\"".$loggedUser."\" AND observerquerytype=\"".$querytype."\" ORDER BY observerqueryname;";
	  return $objDatabase->selectRecordsetArray($sql);
  }
  function saveObserverQuery($querytype,$queryname)
  { global $objDatabase,$loggedUser;
    reset($_GET);
    $temp="";
    while(list($key,$value)=each($_GET))
      $temp=$temp.$key."=".$value."&";
    $sql="INSERT INTO observerqueries(observerid,observerquerytype,observerqueryname,observerquery) 
                             VALUES (\"".$loggedUser."\", 
                                     \"".$querytype."\",
                                     \"".$queryname."\",
                                     \"".($temp)."\");";
    $objDatabase->execSQL($sql);
    $sql="SELECT * FROM observerqueries WHERE observerid=\"".$loggedUser."\" AND observerquerytype=\"".$querytype."\" ORDER BY observerqueryname;";
	  return $objDatabase->selectRecordsetArray($sql);
  }
}
?>