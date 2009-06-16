<?php
interface iLists
{ public  function addList($name);                                 // add a list with the specified name, after checking loggedUser and listname
  public  function addObjectToList($name, $showname='');           // adds an object to the active list, if the object isn't already in
  public  function addObservationToList($id);                      // adds the specified observation to the description of the object in the list, adding also the object if it isn't in already
  public  function checkList($name);                               // check a list: 0=non-existant, 1=existant, 2=existant and owner
  public  function checkObjectInMyActiveList($value);              // verifies if the object is in the active list 
  public  function checkObjectMyOrPublicList($value, $list);       // verifies if the object is in the active or the specified list
  public  function emptyList($listname);                           // empties the list after checking ownership
  public  function getListOwner();
  public  function getLists();                                     // returns an array of lists, first the private ones of the logged user, then the public ones
  public  function getListObjectDescription($object);              // returns a string with the list object description
  public  function getMyLists();                                   // returns an array of logged user lists
  public  function getObjectsFromList($theListname);               // returns an array with the deatils of the objects in the specified list
  public  function ObjectDownInList($place);                       // move object down in list after checking list ownership
  public  function ObjectFromToInList($from, $to);                 // move object from to in list after checking ownership
  public  function ObjectUpInList($place);                         // move object up in list after checking ownership
  public  function removeList($name);                              // remove a list with the specified name after checking ownership
  public  function removeObjectFromList($name);                    // remove the object from the active list, after checking list ownership
  public  function renameList($nameFrom, $nameTo);                 // renames a list after checking ownership
  public  function setListObjectDescription($object,$description); // sets the object description in the active list after checking ownership  
}
class Lists implements iLists
{
 public function addList($name)
 { global $objDatabase,$objUtil,$loggedUser;
   if($loggedUser&&$name&&(!($this->checkList($name))))
   { $objDatabase->execSQL("INSERT INTO observerobjectlist(observerid, objectname, listname, objectplace, objectshowname) VALUES (\"".$loggedUser."\", \"\", \"".$name."\", '0', \"\")");
     if(array_key_exists('QobjParams',$_SESSION)&&array_key_exists('source',$_SESSION['QobjParams'])&&($_SESSION['QobjParams']['source']=='tolist'))
       unset($_SESSION['QobjParams']);
   }
 }
 public  function addObjectToList($name, $showname='')
 { global $loggedUser, $listname, $objDatabase, $myList;
   if(!$myList)
     return;
   if(!$showname)
	   $showname=$name;
   if(!($objDatabase->selectSingleValue("SELECT objectplace AS ObjPl FROM observerobjectlist WHERE observerid = \"".$loggedUser."\" AND listname = \"".$listname."\" AND objectname=\"".$name."\"",'objectplace',0)))
	   $objDatabase->execSQL("INSERT INTO observerobjectlist(observerid, objectname, listname, objectplace, objectshowname, description) VALUES (\"".$loggedUser."\", \"$name\", \"$listname\", \"".(($objDatabase->selectSingleValue("SELECT MAX(objectplace) AS ObjPlace FROM observerobjectlist WHERE observerid = \"".$loggedUser."\" AND listname = \"$listname\"",'ObjPlace',0))+1)."\", \"$showname\", \"".$objDatabase->selectSingleValue("SELECT description FROM objects WHERE name=\"".$name."\"",'description')."\")");
   if(array_key_exists('QobjParams',$_SESSION)&&array_key_exists('source',$_SESSION['QobjParams'])&&($_SESSION['QobjParams']['source']=='tolist'))
     unset($_SESSION['QobjParams']);
 }
 public  function addObservationToList($id)
 { global $objDatabase, $loggedUser, $listname, $myList,$objPresentations;
   if(!$myList)
     return; 
   $sql = "SELECT observations.objectname, observations.description, observers.name, observers.firstname, locations.name as location, instruments.name AS instrument " .
	        "FROM observations " .
		 		  "JOIN observers ON observations.observerid=observers.id " .
				  "JOIN locations ON observations.locationid=locations.id " .
				  "JOIN instruments ON observations.instrumentid=instruments.id " .
				  "WHERE observations.id=".$id;
	 $get=$objDatabase->selectRecordArray($sql);
   if($get)
	 { $name=$get['objectname'];
     $description = '(' .$get['firstname'].' '.$get['name'];
	   $description .='/' .$get['instrument'];
	   $description .='/' .$get['location'];
	   $description .=') '.$objPresentations->br2nl($get['description']);
     $get=$objDatabase->selectRecordArray("SELECT objectplace AS ObjPl, description FROM observerobjectlist WHERE observerid = \"".$loggedUser."\" AND listname = \"".$listname."\" AND objectname=\"".$name."\"");
     if(!$get)
       $objDatabase->execSQL("INSERT INTO observerobjectlist(observerid, objectname, listname, objectplace, objectshowname, description) ".
  	                         "VALUES (\"".$loggedUser."\", \"".$name."\", \"".$listname."\",".
  	                         " \"".(($objDatabase->selectSingleValue("SELECT MAX(objectplace) AS ObjPl FROM observerobjectlist WHERE observerid = \"".$loggedUser."\" AND listname = \"".$listname."\"",'ObjPl',0))+1)."\", ".
  	                         "\"".$name."\", \"".substr((($tempDescription=$objDatabase->selectSingleValue("SELECT description FROM objects WHERE name=\"".$name."\"",'description'))?($tempDescription.' \n'):'').$description,0,1024) . "\")");
		 else
	     $objDatabase->execSQL("UPDATE observerobjectlist SET description = \"".substr((($get['description'])?($get['description']." "):'').$description,0,1024)."\" WHERE observerid = \"".$loggedUser."\" AND listname=\"".$listname."\" AND objectname=\"".$name."\"");
	}
  if(array_key_exists('QobjParams',$_SESSION)&&array_key_exists('source',$_SESSION['QobjParams'])&&($_SESSION['QobjParams']['source']=='tolist'))
    unset($_SESSION['QobjParams']);
 }
 public  function checkList($name)
 { global $loggedUser;
   $retval=0;
	 if(substr($name,0,7)=="Public:")
	 { $sql="SELECT listname FROM observerobjectlist WHERE listname=\"".$name."\"";	
	   $run=mysql_query($sql) or die(mysql_error());
	   if($get=mysql_fetch_object($run))
	     $retval =1;
	 }
	 if($loggesUser)
   { $sql="SELECT listname FROM observerobjectlist WHERE observerid = \"".$loggedUser."\" AND listname = \"".$name."\"";
	   $run=mysql_query($sql) or die(mysql_error());
	   if($get=mysql_fetch_object($run))
       $retval=2;
	 }
   return $retval;
 }
 public  function checkObjectInMyActiveList($value)
 { global $objDatabase,$loggedUser,$listname;
   return $objDatabase->selectSingleValue("SELECT observerobjectlist.objectplace FROM observerobjectlist WHERE observerid = \"".$loggedUser."\" AND objectname=\"".$value."\" AND listname=\"".$listname."\"",'objectplace',0);
 }
 public  function checkObjectMyOrPublicList($value, $list)
 { global $objDatabase,$loggedUser;
   return $objDatabase->selectSingleValue("SELECT observerobjectlist.objectplace FROM observerobjectlist WHERE ".((substr($list,0,7)=='Public:')?"":("observerid = \"".$loggedUser."\" AND "))."objectname=\"".$value."\" AND listname=\"".$list."\"",'objectplace',0);
 }
 public  function emptyList($listname)
 { global $objDatabase,$loggedUser,$myList;
   if($loggedUser&&$myList)
   { $objDatabase->execSQL("DELETE FROM observerobjectlist WHERE observerid = \"".$loggedUser."\" AND listname = \"".$listname."\" AND objectplace<>0");
     if(array_key_exists('QobjParams',$_SESSION)&&array_key_exists('source',$_SESSION['QobjParams'])&&($_SESSION['QobjParams']['source']=='tolist'))
       unset($_SESSION['QobjParams']);
   }
 }
 public  function getListObjectDescription($object)
 { global $loggedUser,$listname,$objDatabase; 
   return $objDatabase->selectSingleValue("SELECT observerobjectlist.description FROM observerobjectlist WHERE ".((substr($listname,0,7)=='Public:')?"":"observerid = \"".$loggedUser."\" AND ")."objectname=\"".$object."\" AND listname=\"".$listname."\"",'description','');
 }
 public  function getListOwner()
 { global $listname,$objDatabase; 
   return $objDatabase->selectSingleValue("SELECT observerobjectlist.observerid FROM observerobjectlist WHERE listname=\"".$listname."\" AND objectplace=0",'observerid','');
 }
 public  function getLists()
 { global $objDatabase, $loggedUser;
   $result=array();
	 if(array_key_exists('deepskylog_id',$_SESSION))
	 { $run=$objDatabase->selectRecordset("SELECT DISTINCT observerobjectlist.listname FROM observerobjectlist WHERE observerid=\"".$loggedUser."\" OR listname LIKE \"Public: %\" ORDER BY observerobjectlist.listname");
  	 $get=mysql_fetch_object($run);	
	   if($get)
  	 { $result1=array();
  	   $result2=array();
  		 while($get)
  		 { if(substr($get->listname,0,7)=="Public:")
  			   $result2[]=$get->listname;
  			 else
  			   $result1[]=$get->listname;
  			 $get = mysql_fetch_object($run);	
  		 }
  		 $result=array_merge($result1,$result2);
     }
	}
	return $result;
 }
 public  function getMyLists()
 { global $loggedUser, $objDatabase;
   return $objDatabase->selectSingleArray("SELECT DISTINCT observerobjectlist.listname FROM observerobjectlist WHERE observerid = \"".$loggedUser."\"",'listname');
 }
 public  function getObjectsFromList($theListname)
 { global $objObject,$objDatabase,$loggedUser;
   $obs=array();
	 if(substr($theListname,0,7)=="Public:")
     $sql = "SELECT observerobjectlist.objectname, observerobjectlist.objectplace, observerobjectlist.objectshowname, observerobjectlist.description FROM observerobjectlist " .
	          "JOIN objects ON observerobjectlist.objectname = objects.name " .
	 	    	  "WHERE listname=\"".$theListname."\" AND objectname <>\"\"";
	 else
     $sql = "SELECT observerobjectlist.objectname, observerobjectlist.objectplace, observerobjectlist.objectshowname, observerobjectlist.description FROM observerobjectlist " .
	          "JOIN objects ON observerobjectlist.objectname = objects.name " .
	 	    	  "WHERE observerid = \"".$loggedUser."\" AND listname = \"".$theListname."\" AND objectname <>\"\"";
   $run=$objDatabase->selectRecordset($sql);
   while($get=mysql_fetch_object($run))
     if(!in_array($get->objectname, $obs))
	     $obs[$get->objectshowname] = array($get->objectplace,$get->objectname,$get->description);
	 return $objObject->getSeenObjectDetails($obs, "D");	 
 }  
 public  function ObjectDownInList($place)
 { global $loggedUser,$listname,$objDatabase, $myList;
   if(!$myList)
     return;
  if($place&&($place>1))
	{ $objDatabase->execSQL("UPDATE observerobjectlist SET objectplace=-1 WHERE observerid = \"".$loggedUser."\" AND listname =\"".$listname."\" AND objectplace=".$place);
    $objDatabase->execSQL("UPDATE observerobjectlist SET objectplace=objectplace+1 WHERE observerid=\"".$loggedUser."\" AND listname=\"".$listname."\" AND objectplace=".$place-1);
    $objDatabase->execSQL("UPDATE observerobjectlist SET objectplace=".($place-1)." WHERE observerid=\"".$loggedUser."\" AND listname =\"".$listname."\" AND objectplace=-1");
  }
  if(array_key_exists('QobjParams',$_SESSION)&&array_key_exists('source',$_SESSION['QobjParams'])&&($_SESSION['QobjParams']['source']=='tolist'))
    unset($_SESSION['QobjParams']);
 }
 public  function ObjectFromToInList($from, $to)
 { global $loggedUser,$listname,$objDatabase,$myList;
   if(!($myList))
     return '';
   $max=$objDatabase->selectSingleValue("SELECT MAX(objectplace) AS ObjPl FROM observerobjectlist WHERE observerid = \"".$loggedUser."\" AND listname = \"".$listname."\"",'ObjPl');
	 if(($from>0)&&($from<=$max)&&($to>0)&&($to<=$max)&&($from!=$to))
	 { if($from<$to)
		 { $objDatabase->execSQL("UPDATE observerobjectlist SET objectplace=-1 WHERE ((observerid=\"".$loggedUser."\") AND (listname=\"".$listname."\") AND (objectplace=".$from."))");
       $objDatabase->execSQL("UPDATE observerobjectlist SET objectplace=objectplace-1 WHERE ((observerid=\"".$loggedUser."\") AND (listname=\"".$listname."\") AND (objectplace>".$from.") AND (objectplace<=".$to."))");
       $objDatabase->execSQL("UPDATE observerobjectlist SET objectplace=".$to." WHERE ((observerid=\"".$loggedUser."\") AND (listname=\"".$listname."\") AND (objectplace=-1))");
		 }
		 else
		 { $objDatabase->execSQL("UPDATE observerobjectlist SET objectplace=-1 WHERE ((observerid=\"".$loggedUser."\") AND (listname=\"".$listname."\") AND (objectplace=".$from."))");
       $objDatabase->execSQL("UPDATE observerobjectlist SET objectplace=objectplace+1 WHERE ((observerid=\"".$loggedUser."\") AND (listname=\"".$listname."\") AND (objectplace>=".$to.") AND (objectplace<".$from."))");
       $objDatabase->execSQL("UPDATE observerobjectlist SET objectplace=".$to." WHERE ((observerid=\"".$loggedUser."\") AND (listname=\"".$listname."\") AND (objectplace=-1))");
	   }
     if(array_key_exists('QobjParams',$_SESSION)&&array_key_exists('source',$_SESSION['QobjParams'])&&($_SESSION['QobjParams']['source']=='tolist'))
       unset($_SESSION['QobjParams']);
	   return LangToListMoved7.$_GET['ObjectToPlaceInList'].".";
	 }
	 else
	   return '';
 }
 public  function ObjectUpInList($place)
 { global $loggedUser,$listname,$objDatabase, $myList;
   if(!$myList)
     return;
	if($place<$objDatabase->selectSingleValue("SELECT MAX(objectplace) AS ObjPl FROM observerobjectlist WHERE observerid = \"".$loggedUser."\" AND listname=\"".$listname."\"",'ObjPl'))
	{ $objDatabase->execSQL("UPDATE observerobjectlist SET objectplace=-1 WHERE observerid=\"".$loggedUser."\" AND listname=\"".$listname."\" AND objectplace=".$place);
    $objDatabase->execSQL("UPDATE observerobjectlist SET objectplace=objectplace-1 WHERE observerid=\"".$loggedUser."\" AND listname=\"".$listname."\" AND objectplace=".$place+1);
    $objDatabase->execSQL("UPDATE observerobjectlist SET objectplace=".($place+1)." WHERE observerid=\"".$loggedUser."\" AND listname=\"".$listname."\" AND objectplace=-1");
  }
  if(array_key_exists('QobjParams',$_SESSION)&&array_key_exists('source',$_SESSION['QobjParams'])&&($_SESSION['QobjParams']['source']=='tolist'))
    unset($_SESSION['QobjParams']);
 }
 public function removeList($name)
 { global $objDatabase,$loggedUser,$myList;
 	 if($loggedUser&&$myList)
 	 { $objDatabase->execSQL("DELETE FROM observerobjectlist WHERE observerid = \"".$loggedUser."\" AND listname = \"".$name."\"");
     if(array_key_exists('QobjParams',$_SESSION)&&array_key_exists('source',$_SESSION['QobjParams'])&&($_SESSION['QobjParams']['source']=='tolist'))
       unset($_SESSION['QobjParams']);
 	 }
 }
 public  function removeObjectFromList($name)
 { global $loggedUser,$listname,$objDatabase, $myList;
   if(!$myList)
     return;
   if($place=$objDatabase->selectSingleValue("SELECT objectplace AS ObjPl FROM observerobjectlist WHERE observerid=\"".$loggedUser."\" AND listname = \"".$listname."\" AND objectname=\"".$name."\"",'ObjPl'))
	 { $objDatabase->execSQL("DELETE FROM observerobjectlist WHERE observerid=\"".$loggedUser."\" AND listname=\"".$listname."\" AND objectname=\"".$name."\"");
     $objDatabase->execSQL("UPDATE observerobjectlist SET objectplace=objectplace-1 WHERE observerid = \"".$loggedUser."\" AND listname=\"".$listname."\" AND objectplace>".$place);
	 }
   if(array_key_exists('QobjParams',$_SESSION)&&array_key_exists('source',$_SESSION['QobjParams'])&&($_SESSION['QobjParams']['source']=='tolist'))
     unset($_SESSION['QobjParams']);
 }
 public  function renameList($nameFrom, $nameTo)
 { global $loggedUser,$objDatabase,$myList;
   if($loggedUser&&$myList)
   { $objDatabase->execSQL("UPDATE observerobjectlist SET listname=\"".$nameTo."\" WHERE observerid=\"".$loggedUser."\" AND listname=\"".$nameFrom."\"");
     if(array_key_exists('QobjParams',$_SESSION)&&array_key_exists('source',$_SESSION['QobjParams'])&&($_SESSION['QobjParams']['source']=='tolist'))
       unset($_SESSION['QobjParams']);  
   }
 } 
 public  function setListObjectDescription($object,$description)
 { global $objDatabase, $loggedUser, $listname, $myList;
   if(!($myList))
     return;
   $objDatabase->execSQL("UPDATE observerobjectlist SET description=\"".$description."\" WHERE observerid=\"".$loggedUser."\" AND objectname=\"".$object."\" AND listname=\"".$listname."\"");
   if(array_key_exists('QobjParams',$_SESSION)&&array_key_exists('source',$_SESSION['QobjParams'])&&($_SESSION['QobjParams']['source']=='tolist'))
     unset($_SESSION['QobjParams']);
 }
}
$objList=new Lists;
?>
