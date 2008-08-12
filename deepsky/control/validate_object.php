<?php

// validate_object.php
// checks if the add new object form is correctly filled in
// and eventually adds the object to the database

// Version 0.1: 20040929, JV

session_start(); // start session

include "../../lib/objects.php";
include_once "../../lib/observers.php";
include_once "../../lib/setup/vars.php";
include_once "../../lib/util.php";

$util = new Util();
$util->checkUserInput();

if ($_POST['newobject']) // pushed add new object button
{
  $check = true;
  $ra = $_POST['RAhours'] + ($_POST['RAminutes'] / 60) + ($_POST['RAseconds'] / 3600);

  if(array_key_exists('DeclDegrees', $_POST) && (($_POST['DeclDegrees'] < 0) || (strcmp($_POST['DeclDegrees'], '-0') == 0)))
  {
    $declination = $_POST['DeclDegrees'] - ($_POST['DeclMinutes'] / 60) - ($_POST['DeclSeconds'] / 3600);
  }
  else
  {
    $declination = $_POST['DeclDegrees'] + ($_POST['DeclMinutes'] / 60) + ($_POST['DeclSeconds'] / 3600);
  }

  // check if required fields are filled in
  if (!$_POST['number'] || !$_POST['type'] || !$_POST['con'] || $ra == 0.0 || $declination == 0.0)
  {
    $_SESSION['message'] = LangValidateObjectMessage1;
    header("Location:../../common/error.php");
		$check = false;
  }

  // check name
  if($check)
  {
    $objects = new Objects();
    $catalogue = trim($_POST['catalogue']);
    $catalogues = $objects->getCatalogues(); // get all catalogues in database
    $foundcatalogue = "";
    while(list($key, $value) = each($catalogues))
    {
      if(strtoupper($value) == strtoupper($catalogue)) // catalogue found
      {
        $foundcatalogue = $value;
      }
    }

    if($foundcatalogue != "")
    {
      $catalogue = $foundcatalogue;
    }
    $name = trim($catalogue . " " . ucwords(trim($_POST['number'])));
    $query1 = array("name" => $name);

    if($objects->getObjectFromQuery($query1, 1)) // object already exists
    {
      $_SESSION['message'] = LangValidateObjectMessage2;
      header("Location:../../common/error.php");
      $check = false;
    }
  }
  // name checked


  // calculate right ascension
  if($check)
  {
    if($_POST['RAhours'] < 0 || $_POST['RAhours'] > 23 || $_POST['RAminutes'] < 0 || $_POST['RAseconds'] < 0 || $_POST['RAminutes'] > 59 || $_POST['RAseconds'] > 59)
    {
      $_SESSION['message'] = LangValidateObjectMessage4;
      header("Location:../../common/error.php");
      $check = false;
    }
  }
  // ra checked
  // calculate declination
  if($check)
  {
    if(($_POST['DeclDegrees'] < -89) || ($_POST['DeclDegrees'] > 89) || ($_POST['DeclMinutes'] < 0) || 
      ($_POST['DeclMinutes'] > 59) || ($_POST['DeclSeconds'] < 0) || ($_POST['DeclSeconds'] > 59)) 
    {
      $_SESSION['message'] = LangValidateObjectMessage5;
      header("Location:../../common/error.php");
      $check = false;
    }
  }
  // decl checked

  // magnitude
  if($check)
  {
    $magnitude = "99.9";
    if($_POST['magnitude'] && (!(ereg('^([0-9]{1,2})[.,]{0,1}([0-9]{0,1})$', $_POST['magnitude'], $matches)))) // wrong magnitude
    {
      $_SESSION['message'] = LangValidateObjectMessage8;
      header("Location:../../common/error.php");
      $check = false;
    }
    elseif($_POST['magnitude'])
    {
      $magnitude = $matches[1] . ".";
      if($matches[2] != "")
      {
        $magnitude = $magnitude . $matches[2];
      }
      else
      {
        $magnitude = $magnitude . "0";
      }
    }
  }
  // magnitude checked

  // postion angle
  if($check)
  {
    $posangle = "999";
    if($_POST['posangle'] && ($_POST['posangle'] < 0 || $_POST['posangle'] > 360))
    {
      $_SESSION['message'] = LangValidateObjectMessage6;
      header("Location:../../common/error.php");
      $check = false; 
    }
    elseif($_POST['posangle'])
    {
      $posangle = $_POST['posangle'];
    }
  }
  // pasangle checked

  // surface brightness
  if($check)
  {
    $sb = "99.9";
    if($_POST['sb'] && ereg('^([0-9]{1,2})[.,]{0,1}([0-9]{0,1})$', $_POST['sb'], $matches)) // correct sb 
    {
      $sb = "" . $matches[1] . ".";
      if($matches[2] != "")
      {
        $sb = $sb . $matches[2];
      }
      else
      {
        $sb = $sb . "0";
      }
    }
  }
  // sb checked

  // size
  if($check)
  {
    $diam1 = 0.0;
    if($_POST['size_x'] && $_POST['size_x_units'])
    {
      if($_POST['size_x_units'] == "min")
      {
        $diam1 = $_POST['size_x'] * 60.0;
      }
      elseif($_POST['size_x_units'] == "sec")
      {
        $diam1 = $_POST['size_x'];
      }
      else
      {
        $_SESSION['message'] = LangValidateObjectMessage7;
        header("Location:../../common/error.php");
        $check = false;
      }
    }
  }
  // diam1 checked

  // check diam2
  if($check)
  {
    $diam2 = 0.0;
    if($_POST['size_y'] && $_POST['size_y_units'])
    {
      if($_POST['size_y_units'] == "min")
      {
        $diam2 = $_POST['size_y'] * 60.0;
      }
      elseif($_POST['size_y_units'] == "sec")
      {
        $diam2 = $_POST['size_y'];
      }
      else
      {
        $_SESSION['message'] = LangValidateObjectMessage7;
        header("Location:../../common/error.php");
        $check = false;
      }
    }
  }
  // diam2 checked

  // fill database
  if($check)
  {
    $objects->addDSObject($name, $catalogue , ucwords(trim($_POST['number'])), $_POST['type'], $_POST['con'], $ra, $declination, $magnitude, $sb, $diam1, $diam2, $posangle, "", "DeepskyLogUser");
    $admins = $obs->getAdministrators();
    $obs = new Observers;
		
    while(list ($key, $value) = each($admins))
    {
     if ($obs->getEmail($value) != "")
     {
      $adminMails[] = $obs->getEmail($value);
     }
    }
    $to = implode(",", $adminMails);

    // message subject

    $subject = LangValidateAccountEmailTitleObject . " " . $name;

    // other headers

    $administrators = $obs->getAdministrators();
    $fromMail = $obs->getEmail($administrators[0]);
    $headers = "From:".$fromMail;
    $body = LangValidateAccountEmailTitleObject . " " . $name . " " . " www.deepskylog.org/deepsky/index.php?indexAction=detail_object&object=" . urlencode($name) . " " .
		        LangValidateAccountEmailTitleObjectObserver . " " . $obs->getName($_SESSION['deepskylog_id']) . " " . $obs->getFirstName($_SESSION['deepskylog_id']) . " www.deepskylog.org/common/detail_observer.php?user=" . urlencode($_SESSION['deepskylog_id']);
						
		// send message
    mail($to, $subject, $body, $headers);
    
		header("Location:../index.php?indexAction=detail_object&object=" . urlencode($name));
  }
}
elseif ($_POST['clearfields']) // pushed clear fields button
{
  header("Location:../index.php?indexAction=add_object");
}	
?>
