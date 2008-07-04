<?php

// add_csv_observations.php
// adds observations from a csv file to the database

// Code cleanup - removed by David on 20080704
//include_once "../../lib/observers.php";
//$obs = new Observers;


session_start(); // start session

include_once "../../lib/objects.php";
include_once "../../lib/lists.php";
include_once "../../lib/setup/vars.php"; // sets language for errors
include_once "../../lib/util.php";

$util = new Util();
$util->checkUserInput();

$obj = new Objects;
$list = new Lists;

if($_FILES['csv']['tmp_name'] != "")
   $csvfile = $_FILES['csv']['tmp_name'];
$data_array = file($csvfile);
for ($i = 0; $i < count($data_array); $i++ )
  $parts_array[$i] = explode(";",$data_array[$i]);
if(!is_array($parts_array))
{
  $_SESSION['message'] = LangInvalidCSVListFile;
  header("Location:../../common/error.php");
}
else
{
  $objects = array();
	$objectsMissing = array();
  $j = 0;
	for ($i = 0;$i < count($parts_array); $i++)
  {
    if(trim($parts_array[$i][0]))
    {
		  $objectsquery = $obj->getExactObject(trim($parts_array[$i][0]));
      if (count($objectsquery)==0)
      {
        $objectsMissing[$j] = ucwords(trim($parts_array[$i][0]));
        $j++;
      }
  		else
			{
  		  if(array_key_exists(1,$parts_array[$i])&&($parts_array[$i][1]<>'')&&(ucwords(trim($parts_array[$i][1]))<>$objectsquery[0][0]))
				  $objects[$i] = array($objectsquery[0], trim($parts_array[$i][1]).' ('.$objectsquery[0].')');
				else
				  $objects[$i] = array($objectsquery[0], trim($parts_array[$i][0]));
			}
		}
  }
  if (count($objectsMissing) > 0)
  {
    $errormessage = "";
    $errormessage = LangCSVListError1 . "<br /> <ul><li>" . LangCSVListError2 . " : <ul>";
    for ($i = 0;$i < count($objectsMissing);$i++ )
      $errormessage = $errormessage . "<li>" . $objectsMissing[$i] . "</li>";
    $errormessage = $errormessage .  "</ul></li></ul>";
    $_SESSION['message'] = $errormessage;
		header("Location:../../common/error.php");
  }
  else
  {
    if(array_key_exists('deepskylog_id',$_SESSION) && $_SESSION['deepskylog_id'])
		{
		  if(array_key_exists('listname',$_SESSION) && $_SESSION['listname'] && ($list->checkList($_SESSION['listname'])==2))
			{
        for ($i=0;$i<count($objects);$i++)
  			  $list->addObjectToList($objects[$i][0],$objects[$i][1]);
				header("Location:../index.php?indexAction=listaction");
			}
			else
			{
        $_SESSION['message'] = LangListImportError2;
		    header("Location:../../common/error.php");
			}
    }
		else
		{
      $_SESSION['message'] = LangListImportError1;
		  header("Location:../../common/error.php");
		}
  }
}
?>
