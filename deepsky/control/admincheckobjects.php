<?php
// admincheckobjects
// check for faulty objects - only for admins

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!$loggedUser) throw new Exception(LangException002);
elseif($_SESSION['admin']!="yes") throw new Exception(LangException001);
else admincheckobjects();

function admincheckobjects()
{ global $objDatabase,$objConstellation;
	echo "Checking ".($objCnt=count($_SESSION['Qobj']))." objects.<br />";
	echo "<hr />";
	echo "Checking objects constellation:<br />";
	echo "<br />";
	$correct=0;
	for($i=0;$i<$objCnt;$i++)
	{ if($_SESSION['Qobj'][$i]['objectconstellation']==$objConstellation->getConstellationFromCoordinates($_SESSION['Qobj'][$i]['objectra'],$_SESSION['Qobj'][$i]['objectdecl']))
	    $correct++;
	  else
	    echo "- ".$_SESSION['Qobj'][$i]['objectname']." constellation ".$_SESSION['Qobj'][$i]['objectconstellation']." should be ".$objConstellation->getConstellationFromCoordinates($_SESSION['Qobj'][$i]['objectra'],$_SESSION['Qobj'][$i]['objectdecl']).' '.$_SESSION['Qobj'][$i]['objectra'].' '.$_SESSION['Qobj'][$i]['objectdecl']."<br />"; 
	}
	echo "<br />";
	echo "Correct ".$correct.".<br />";
	echo "<hr />";
	echo "Checking objects and objectnames:<br />";
	echo "<br />";
	$correct=0;
	$sql="SELECT objectname FROM objectnames LEFT JOIN objects ON objectnames.objectname=objects.name WHERE objects.name IS NULL;";
	$results=$objDatabase->selectSingleArray($sql,'objectname');
	for($i=0;$i<count($results);$i++)
	{ echo $results[$i]."<br />"; 
	}
	echo "<br />";
	$sql="DELETE objectnames FROM objectnames LEFT JOIN objects ON objectnames.objectname=objects.name WHERE objects.name IS NULL;";
	$objDatabase->execSQL($sql);
	echo "Corrected ".count($results).".<br />";
	echo "<hr />";
	echo "Checking observations on unknown objects:<br />";
	echo "<br />";
	$correct=0;
	$sql="SELECT objectname FROM observations LEFT JOIN objects ON observations.objectname=objects.name WHERE objects.name IS NULL;";
	$results=$objDatabase->selectSingleArray($sql,'objectname');
	for($i=0;$i<count($results);$i++)
	{ echo $results[$i]."<br />"; 
	}
	echo "<br />";
	echo "To corrected ".count($results).".<br />";
	echo "<hr />";
	
}
?>