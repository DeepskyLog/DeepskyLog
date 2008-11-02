<?php

// The observers class collects all functions needed to enter, retrieve and
// adapt observer data from the database and functions to display the data.
//
// Version 0.8 : 19/11/2006, WDM
// Version 3.1, DE 20061119
//

include_once "database.php";
//include "setup/vars.php"; // if $_SESSION['deepskylog_id'] not set yet, $_SESSION['lang'] = $defaultLanguage!
include_once "observations.php";
include "setup/databaseInfo.php";

class Observers
{
 // addObserver adds a new observer to the database. The id, name, first name,
 // email address and password should be given as parameters. The password 
 // must be encoded using md5(...). The new observer will not be able to
 // log in yet. Before being able to do so, the administrator must validate 
 // the new user.
 function addObserver($id, $name, $firstname, $email, $password)
 {
  include_once "setup/databaseInfo.php";

  $db = new database;
  $db->login();

  if (!$_SESSION['lang'])
  {
   $_SESSION['lang'] = "English";
  }

  $array = array("INSERT INTO observers (id, name, firstname, email, password, role, language, club) VALUES (\"$id\", \"$name\", \"$firstname\", \"$email\", \"$password\", \"", RoleWaitlist, "\", \"", $_SESSION['lang'], "\", \"", $club, "\")");

  $sql = implode("", $array);

  mysql_query($sql) or die(mysql_error());

  $db->logout();
 }
 
 // checkPassword returns true if the password for the given id is the given 
 // password, otherwise false.
 function checkPassword($id, $passwd)
 {
  $ret = "false";

  if ($this->getPassword($id) == $passwd)
  {
   $ret = "true";
  }

  return $ret;
 }

 // deleteObserver removes the observer with id = $id 
 function deleteObserver($id)
 {
  $db = new database;
  $db->login();

  $sql = "DELETE FROM observers WHERE id=\"$id\"";
  mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // getAdministrators returns an array with the ids of all administrators
 function getAdministrators()
 {
  include_once "setup/databaseInfo.php";

  $db = new database;
  $db->login();

  if ($club !="")
  {
   $sql = "SELECT * FROM observers WHERE role = \"RoleAdmin\" and club = \"$club\"";
  }
  else
  {
   $sql = "SELECT * FROM observers WHERE role = \"RoleAdmin\"";
  }
  $run = mysql_query($sql) or die(mysql_error());

  while($get = mysql_fetch_object($run))
  {
   $admins[] = $get->id;
  }

  $db->logout();

  return $admins;
 }

 // getClub returns the club of the observer with the given id
 function getClub($id)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM observers WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  if($get)
	{
	   $club = $get->club;
	}
	else
	{
	   $club = ''; 
	}
  

  $db->logout();

  return $club;
 }

 // getEmail returns the email of the given id
 function getEmail($id)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM observers WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);


  if($get)
	{
	   $email = $get->email;
	}
	else
	{
	   $email = ''; 
	}


  $db->logout();

  return $email;
 }

 // getFirstName returns the first name of the given id
 function getFirstName($id)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM observers WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  if($get)
	{
	   $name = $get->firstname;
	}
	else
	{
	   $name = ''; 
	}
	
	

  $db->logout();

  return $name;
 }

 // getIcqName returns the Icqname of the given id
 function getIcqName($id)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM observers WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  if($get)
	{
	   $name = $get->icqname;
	}
	else
	{
	   $name = ''; 
	}  

  $db->logout();

  return $name;
 }

 // getLanguage returns the language of the given id
 function getLanguage($id)
 {
  include "setup/databaseInfo.php";

  $db = new database;
  $db->login();

  $sql = "SELECT * FROM observers WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  if ($get)
  {
    $lang = $get->language;
  }
  else
  {
    $lang = $defaultLanguage;
  }

  $db->logout();

  return $lang;
 }

 // getUsedLanguages returns the languages of the observations to list
 function getUsedLanguages($id)
 {
  include "setup/databaseInfo.php";

  $db = new database;
  $db->login();

  $sql = "SELECT * FROM observers WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  $lang = $get->usedLanguages;

  $db->logout();

  return unserialize($lang);
 }

 // getObservationLanguage returns the preferred language of the observations for the given id
 function getObservationLanguage($id)
 {
  include "setup/databaseInfo.php";

  $db = new database;
  $db->login();

  $sql = "SELECT * FROM observers WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  $lang = $get->observationlanguage;

  $db->logout();

  return $lang;
 }

 // getListOfInstruments returns a list of all StandardInstruments of all 
 // observers
 function getListOfInstruments()
 {
  $db = new database;
  $db->login();

  $sql = "SELECT stdtelescope FROM observers GROUP BY stdtelescope";
  $run = mysql_query($sql) or die(mysql_error());

  while($get = mysql_fetch_object($run))
  {
   $telescopes[] = $get->stdtelescope;
  }
  $db->logout();

  return $telescopes;
 }

 // getListOfLocations returns a list of all StandardLocations of all observers
 function getListOfLocations()
 {
  $db = new database;
  $db->login();

  $sql = "SELECT stdlocation FROM observers GROUP BY stdlocation";
  $run = mysql_query($sql) or die(mysql_error());

  while($get = mysql_fetch_object($run))
  {
   $locations[] = $get->stdlocation;
  }
  $db->logout();
 
  return $locations;
 }

 // getObserverName returns the name of the given id
 function getObserverName($id)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM observers WHERE id = \"$id\"";

  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  if($get)
	{
	   $name = $get->name;
	}
	else
	{
	   $name = ''; 
	}

  $db->logout();

  return $name;
 }

 // getNumberOfObservations($name) returns the number of observations of the
 // given observerid
 function getNumberOfDsObservations($observerid)
 { $db = new database;
   $db->login();
   if($id )
   { $sql = "SELECT COUNT(observations.id) As Cnt FROM observations WHERE observerid = \"$id\"";
     $run = mysql_query($sql) or die(mysql_error());
     $get = mysql_fetch_object($run);
     return $get->Cnt;
	 }
	 else
	 {
     $sql = "SELECT COUNT(observations.id) As Cnt FROM observations";
     $run = mysql_query($sql) or die(mysql_error());
     $get = mysql_fetch_object($run);
     return $get->Cnt;
	 }
 }

 // getNumberOfCometObservations($name) returns the number of comet observations
 // for the given observerid
 function getNumberOfCometObservations($observerid)
 {
  $observations = new CometObservations;
  $obs = $observations->getPopularObservers();

  $observations = 0;

  if ($obs)
  {
   while(list($key, $value) = each($obs))
   {
    if ($key == $observerid)
    {
     $observations = $value;
    }
   }
  }

  return $observations;
 }

 // getRank() returns the number of observations of the given observer
 function getRank($observer)
 {
  $observations = new Observations;
  $numberOfObservations = $observations->getPopularObservers();

  $rank = 0;
  $counter = 0;

  if ($numberOfObservations)
  {
   while(list($key, $value) = each($numberOfObservations))
   {
    $counter++;
    if ($key == $observer)
    {
     $rank = $counter;
    }
   }
  }
  return $rank;
 }

 // getCometRank() returns the number of observations of the given observer
 function getCometRank($observer)
 {
  $observations = new CometObservations;
  $numberOfObservations = $observations->getPopularObservers();

  $rank = 0;
  $counter = 0;

  if ($numberOfObservations)
  {
   while(list($key, $value) = each($numberOfObservations))
   {
    $counter++;
    if ($key == $observer)
    {
     $rank = $counter;
    }
   }
  }
  return $rank;
 }

 // getUseLocal returns if the user wants to use local time or UTC
 function getUseLocal($id)
 {
  $db = new database;
  $db->login();

  if($id != "")
  {
   $sql = "SELECT * FROM observers WHERE id = \"$id\"";
   $run = mysql_query($sql) or die(mysql_error());

   $get = mysql_fetch_object($run);

   $useLocal = $get->UT;

   $db->logout();
  }
  else
  {
   $useLocal = 0;
  }

  return !$useLocal;
 }

 // getObservers returns an array with the ids of all observers
 function getObservers()
 {
  include_once "setup/databaseInfo.php";

  $db = new database;
  $db->login();

  if ($club !="")
  {
   $sql = "SELECT * FROM observers WHERE club =\"$club\"";
  }
  else
  {
   $sql = "SELECT * FROM observers";
  }

  $run = mysql_query($sql) or die(mysql_error());

  while($get = mysql_fetch_object($run))
  {
   $obs[] = $get->id;
  }

  $db->logout();

  return $obs;
 }

 // getObserversFromClub returns the id's of all observers from a 
 // given club in a sql-query to be used.
 function getObserversFromClub($club)
 {
  if ($club != "")
  {
   $db = new database;
   $db->login();

   $sql = "SELECT * FROM observers where club = \"$club\"";
   $run = mysql_query($sql) or die(mysql_error());
	 $sqlquery="";
   while($get = mysql_fetch_object($run))
     $sqlquery .= "OR observerid = \"$get->id\" ";
   $db->logout();
   return substr($sqlquery.")",4);
  }
 }
  
 // getPassword returns the password of the given id
 function getPassword($id)
 {
  include "setup/databaseInfo.php";

  if ($club != "")
  {
   if ($this->getClub($id) == $club)
   {
    $db = new database;
    $db->login();

    $sql = "SELECT * FROM observers WHERE BINARY id = \"$id\"";
    $run = mysql_query($sql) or die(mysql_error());

    $get = mysql_fetch_object($run);

    if($get)
	  {
	     $pwd = $get->password;
	  }
	  else
	  {
	     $pwd = ''; 
	  }  

    

    $db->logout();
   }
  }
  else
  {
   $db = new database;
   $db->login();

   $sql = "SELECT * FROM observers WHERE BINARY id = \"$id\"";
   $run = mysql_query($sql) or die(mysql_error());

   $get = mysql_fetch_object($run);

    if($get)
	  {
	     $pwd = $get->password;
	  }
	  else
	  {
	     $pwd = ''; 
	  }  

   $db->logout();
  }
  return $pwd;
 }

 // getRole returns the role of the given id
 function getRole($id)
 {
  if ($id != "")
  {
   $db = new database;
   $db->login();

   $sql = "SELECT * FROM observers WHERE id = \"$id\"";
   $run = mysql_query($sql) or die(mysql_error());

   $get = mysql_fetch_object($run);

   if ($get)
   {
    $role = $get->role;
   }
   else
   {
    $role = 2;    
   }

   $db->logout();
  }
  else
  {
   $role = 2;
  }
  return $role;
 }

 // getSortedObservers returns an array with the ids of all observers, sorted by the
 // column specified in $sort
 function getSortedObservers($sort)
 {
  include "setup/databaseInfo.php";

  $db = new database;
  $db->login();

  if ($club !="")
  {
   $sql = "SELECT * FROM observers WHERE club =\"$club\" ORDER BY $sort";
  }
  else
  {
   $sql = "SELECT * FROM observers ORDER BY $sort";
  }

  $run = mysql_query($sql) or die(mysql_error());

  while($get = mysql_fetch_object($run))
  {
   $obs[] = $get->id;
  }

  $db->logout();

  return $obs;
 }

 // getSortedActiveObservers returns an array with the ids of all active 
 // observers, sorted by the column specified in $sort
 function getSortedActiveObservers($sort)
 {
  include_once "setup/databaseInfo.php";

  $db = new database;
  $db->login();

  if ($club !="")
  {
   $sql = "SELECT * FROM observers WHERE club =\"$club\" AND observers.id IN (SELECT observerid FROM observations) ORDER BY observers." . $sort;
  }
  else
  {
   $sql = "SELECT DISTINCT * FROM observers JOIN observations ON (observers.id = observations.observerid) ORDER BY observers." . $sort;
  }
  $run = mysql_query($sql) or die(mysql_error());

  while($get = mysql_fetch_object($run))
  {
   $obs[] = $get->id;
  }

  $db->logout();

  return $obs;
 }
 
  // getStandardAtlas returns the standard atlas of the given id
 function getStandardAtlasCode($id)
 {
  $atlas = '';    
	$db = new database;
  $db->login();
  $sql = "SELECT standardAtlasCode FROM observers WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());
  $get = mysql_fetch_object($run);
  if($get)
	  $atlas = $get->standardAtlasCode;
  $db->logout();
  return $atlas;
 }

 // getStandardLocation returns the standard location of the given id
 function getStandardLocation($id)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM observers WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  if($get)
	{
	   $loc = $get->stdlocation;
	}
	else
	{
	   $loc = ''; 
	}  
  

  $db->logout();

  return $loc;
 }

 // getStandardTelescope returns the standard telescope of the given id
 function getStandardTelescope($id)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM observers WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  if($get)
	{
	   $telescope = $get->stdtelescope;
	}
	else
	{
	   $telescope = ''; 
	}  
  

  $db->logout();

  return $telescope;
 }
 
 // setClub sets a new club for the observer with id = $id
 function setClub($id, $club)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE observers SET club = \"$club\" WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // setEmail sets a new email for the observer with id = $id
 function setEmail($id, $email)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE observers SET email = \"$email\" WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // setFirstName sets a new first name for the observer with id = $id
 function setFirstName($id, $firstname)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE observers SET firstname = \"$firstname\" WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // setIcqName sets a new icqname for the observer with id = $id
 function setIcqName($id, $icqname)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE observers SET icqname = \"$icqname\" WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // setLanguage sets the language for the observer with id = $id
 function setObserverLanguage($id, $language)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE observers SET language = \"$language\" WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // setUsedLanguages sets all the used languages for the observer with id = $id
 function setUsedLanguages($id, $language)
 {
  $db = new database;
  $db->login();

  $usedLanguages = serialize($language);
  $sql = "UPDATE observers SET usedLanguages = '$usedLanguages' WHERE id = \"$id\"";

  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // setObserverObservationLanguage sets the language of the observations for the observer with id = $id
 function setObserverObservationLanguage($id, $language)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE observers SET observationlanguage = \"$language\" WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // setName sets a new name for the observer with id = $id
 function setObserverName($id, $name)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE observers SET name = \"$name\" WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // setPassword sets a new password for the observer with id = $id
 function setPassword($id, $pwd)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE observers SET password = \"$pwd\" WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // setRole sets a new role for the observer with id = $id
 function setRole($id, $role)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE observers SET role = \"$role\" WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // setStandardAtlas sets a new standard atlas for the given observer
 function setStandardAtlas($id, $atlas)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE observers SET standardAtlasCode = \"$atlas\" WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // setStandardLocation sets a new standard location for the given observer
 function setStandardLocation($id, $location)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE observers SET stdlocation = \"$location\" WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // setStandardTelescope sets a new standard telescope for the given observer
 function setStandardTelescope($id, $telescope)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE observers SET stdtelescope = \"$telescope\" WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // setUseLocal lets the user use local time for everything
 function setUseLocal($id, $local_time)
 {
  $db = new database;
  $db->login();

  if ($local_time == 0)
  {
    $ut = 1;
  }
  else
  {
    $ut = 0;
  }

  $sql = "UPDATE observers SET UT = \"$ut\" WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // validateObserver validates the user with the given id and gives the user 
 // the given role (which should be $ADMIN or $USER).
 function validateObserver($id, $role)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE observers SET role = \"$role\" WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();

  $subject = LangValidateSubject;

  $ad = "";

  if ($role == RoleAdmin)
  {
   $ad = LangValidateAdmin;
  }

  $array = array(LangValidateMail1, $id, LangValidateMail2, $ad, LangValidateMail3);

  $body = implode("", $array);

  $administrators = $this->getAdministrators();
  $fromMail = $this->getEmail($administrators[0]);
  $headers = "From:".$fromMail;

  mail($this->getEmail($id), $subject, $body, $headers);
 }

 // showObservers prints a table showing all observers. 
 function showObservers()
 {
  $observers = $this->getObservers();
  $locations = new Locations;
  $instruments = new Instruments;

  $count = 0;

  echo "<table width=\"100%\">
         <tr class=\"type3\">
          <td>id</td>
          <td>Name</td>
          <td>First Name</td>
          <td>Email</td>
          <td>Std. Location</td>
          <td>Std. Instrument</td>
          <td>pwd</td>
          <td>role</td>
          <td>language</td>
         </tr>";

  while(list ($key, $value) = each($observers))
  {
   if ($count % 2)
   {
    $type = "class=\"type1\"";
   }
   else
   {
    $type = "class=\"type2\"";
   }

   $name = $this->getObserverName($value);
   $firstname = $this->getFirstName($value);
   $email = $this->getEmail($value);
   $loc = $this->getStandardLocation($value);
   $location = $locations->getLocationName($loc);
   $inst = $this->getStandardTelescope($value);
   $telescope = $instruments->getInstrumentName($inst);
   $password = $this->getPassword($value);

   echo "<tr $type><td> $value </td><td> $name </td><td> $firstname </td><td> <a href=\"mailto:$email\"> $email</a> </td><td> $location </td><td> $telescope </td><td> $password </td><td> ";

   $role = $this->getRole($value);

   if ($role == RoleAdmin)
   {
    echo "admin";
   }
   elseif ($role == RoleUser)
   {
    echo "user";
   }
   elseif ($role == RoleCometAdmin)
   {
    echo "comet admin";
   }
   elseif ($role == RoleWaitlist)
   {
    echo "waitlist";
   }

   $language = $this->getLanguage($value);

   echo "</td><td> $language </td></tr>\n";

   $count++;
  }
  echo "</table>";
 }
}
?>
