<?php
// The observers class collects all functions needed to enter, retrieve and
// adapt observer data from the database and functions to display the data.

class Observers
{
 // addObserver adds a new observer to the database. The id, name, first name,
 // email address and password should be given as parameters. The password 
 // must be encoded using md5(...). The new observer will not be able to
 // log in yet. Before being able to do so, the administrator must validate 
 // the new user.
 function addObserver($id, $name, $firstname, $email, $password)
 { if(!$_SESSION['lang'])
     $_SESSION['lang']="English";
   $GLOBALS['objDatabase']->execSQL("INSERT INTO observers (id, name, firstname, email, password, role, language) VALUES (\"$id\", \"$name\", \"$firstname\", \"$email\", \"$password\", \"".RoleWaitlist."\", \"".$_SESSION['lang']."\"");
 }
 function checkPassword($id, $passwd)
 { return($this->getPassword($id) == $passwd);
 }
 function getAdministrators()
 { return $GLOBALS['objDatabase']->selectSingleArray("SELECT id FROM observers WHERE role = \"RoleAdmin\"",'id');
 }
 function getEmail($id)
 { return $GLOBALS['objDatabase']->selectSingleValue("SELECT email FROM observers WHERE id = \"$id\"",'email','');
 }
 function getFirstName($id)
 { return $GLOBALS['objDatabase']->selectSingleValue("SELECT firstname FROM observers WHERE id = \"$id\"",'firstname','');
 }
 function getIcqName($id)
 { return $GLOBALS['objDatabase']->selectSingleValue("SELECT icqname FROM observers WHERE id = \"$id\"",'icqname','');
 }
 function getLanguage($id)
 { return $GLOBALS['objDatabase']->selectSingleValue("SELECT language FROM observers WHERE id = \"$id\"",'language','');
 }
 function getUsedLanguages($id)
 { return  unserialize($GLOBALS['objDatabase']->selectSingleValue("SELECT usedLanguages FROM observers WHERE id = \"$id\"",'usedLanguages',''));
 }
 function getObservationLanguage($id)
 { return $GLOBALS['objDatabase']->selectSingleValue("SELECT observationlanguage FROM observers WHERE id = \"$id\"",'observationlanguage','');
 }
 function getListOfInstruments()                                                // getListOfInstruments returns a list of all StandardInstruments of all observers
 { return $GLOBALS['objDatabase']->selectSingleArray("SELECT stdtelescope FROM observers GROUP BY stdtelescope",'stdtelescope');
 }
 function getListOfLocations()                                                  // getListOfLocations returns a list of all StandardLocations of all observers
 { return $GLOBALS['objDatabase']->selectSingleArray("SELECT stdlocation FROM observers GROUP BY stdlocation",'stdlocation');
 }
 function getObserverName($id)
 {return $GLOBALS['objDatabase']->selectSingleValue("SELECT name FROM observers WHERE id = \"$id\"",'name','');
 }
 function getNumberOfDsObservations($observerid)                                // getNumberOfObservations($name) returns the number of observations of the given observerid
 { if($observerid )
     return $GLOBALS['objDatabase']->selectSingleValue("SELECT COUNT(observations.id) As Cnt FROM observations WHERE observerid = \"$observerid\"",'Cnt',0);
   else
	   return $GLOBALS['objDatabase']->selectSingleValue("SELECT COUNT(observations.id) As Cnt FROM observations",'Cnt',0);
 }
 function getNumberOfCometObservations($observerid)                             // getNumberOfCometObservations($name) returns the number of comet observations for the given observerid
 { if($observerid )
     return $GLOBALS['objDatabase']->selectSingleValue("SELECT COUNT(cometobservations.id) As Cnt FROM cometobservations WHERE observerid = \"$observerid\"",'Cnt',0);
   else
	   return $GLOBALS['objDatabase']->selectSingleValue("SELECT COUNT(cometobservations.id) As Cnt FROM cometobservations",'Cnt',0);
 }
 function getRank($observer)                                                    // getRank() returns the number of observations of the given observer
 { return(array_search($observer,$GLOBALS['objObservation']->getPopularObservers())+1);
 }
 function getCometRank($observer)                                               // getCometRank() returns the number of observations of the given observer
 { $observations = new CometObservations;
   $numberOfObservations = $observations->getPopularObservers();
   return(array_search($observer,$numberOfObservations)+1);
 }
 function getUseLocal($id)                                                      // getUseLocal returns if the user wants to use local time or UTC
 { return (!($GLOBALS['objDatabase']->selectSingleValue("SELECT observers.UT FROM observers WHERE id = \"$id\"",'UT',0)));
 }
 function getObservers()                                                        // getObservers returns an array with the ids of all observers
 { return $GLOBALS['objDatabase']->selectSingleArray("SELECT observers.id FROM observers",'id');
 }
 function getPassword($id)                                                      // getPassword returns the password of the given id
 { return $GLOBALS['objDatabase']->selectSingleValue("SELECT observers.password FROM observers WHERE BINARY id=\"$id\"", 'password', '');
 }
 function getRole($id)                                                          // getRole returns the role of the given id
 { return $GLOBALS['objDatabase']->selectSingleValue("SELECT observers.role FROM observers WHERE id=\"$id\"",'role',2);
 }
 function getSortedObservers($sort)                                             // getSortedObservers returns an array with the ids of all observers, sorted by the column specified in $sort
 { return $GLOBALS['objDatabase']->selectSingleArray("SELECT observers.id FROM observers ORDER BY $sort",'id');
 }
 function getPopularObserversByName()                                           // getSortedActiveObservers returns an array with the ids(key) and names(value) of all active observers, sorted by name
 { return $GLOBALS['objDatabase']->selectKeyValueArray("SELECT DISTINCT observers.id, CONCAT(observers.firstname,' ',observers.name) As observername, observers.name FROM observers JOIN observations ON (observers.id = observations.observerid) ORDER BY observers.name",'id','observername');
 }
 function getStandardAtlasCode($id)                                             // getStandardAtlas returns the standard atlas of the given id
 { return $GLOBALS['objDatabase']->selectSingleValue("SELECT standardAtlasCode FROM observers WHERE id=\"$id\"",'standardAtlasCode','');
 }
 function getStandardLocation($id)                                              // getStandardLocation returns the standard location of the given id
 { return $GLOBALS['objDatabase']->selectSingleValue("SELECT * FROM observers WHERE id=\"$id\"",'stdlocation','');
 }
 function getStandardTelescope($id)                                             // getStandardTelescope returns the standard telescope of the given id
 { return $GLOBALS['objDatabase']->selectSingleValue("SELECT * FROM observers WHERE id=\"$id\"",'stdtelescope','');
 }
 function setEmail($id, $email)                                                 // setEmail sets a new email for the observer with id = $id
 { $GLOBALS['objDatabase']->execSQL("UPDATE observers SET email = \"$email\" WHERE id=\"$id\"");
 }
 function setFirstName($id, $firstname)                                         // setFirstName sets a new first name for the observer with id = $id
 { $GLOBALS['objDatabase']->execSQL("UPDATE observers SET firstname = \"$firstname\" WHERE id=\"$id\"");
 }
 function setIcqName($id, $icqname)                                             // setIcqName sets a new icqname for the observer with id = $id
 { $GLOBALS['objDatabase']->execSQL("UPDATE observers SET icqname = \"$icqname\" WHERE id=\"$id\"");
 }
 function setObserverLanguage($id, $language)                                   //setLanguage sets the language for the observer with id = $id
 { $GLOBALS['objDatabase']->execSQL("UPDATE observers SET language = \"$language\" WHERE id = \"$id\"");
 }
 function setUsedLanguages($id, $language)                                      // setUsedLanguages sets all the used languages for the observer with id = $id
 { $GLOBALS['objDatabase']->execSQL("UPDATE observers SET usedLanguages = '".serialize($language)."' WHERE id=\"$id\"");
 }
 function setObserverObservationLanguage($id, $language)                        // setObserverObservationLanguage sets the language of the observations for the observer with id = $id
 { $GLOBALS['objDatabase']->execSQL("UPDATE observers SET observationlanguage = \"$language\" WHERE id=\"$id\"");
 }
 function setObserverName($id, $name)                                           // setName sets a new name for the observer with id = $id
 { $GLOBALS['objDatabase']->execSQL("UPDATE observers SET name = \"$name\" WHERE id=\"$id\"");
 }
 function setPassword($id, $pwd)                                                // setPassword sets a new password for the observer with id = $id
 { $GLOBALS['objDatabase']->execSQL("UPDATE observers SET password = \"$pwd\" WHERE id=\"$id\"");
 }
 function setRole($id, $role)                                                   // setRole sets a new role for the observer with id = $id
 { $GLOBALS['objDatabase']->execSQL("UPDATE observers SET role = \"$role\" WHERE id=\"$id\"");
 }
 function setStandardAtlas($id, $atlas)                                         // setStandardAtlas sets a new standard atlas for the given observer
 { $GLOBALS['objDatabase']->execSQL("UPDATE observers SET standardAtlasCode = \"$atlas\" WHERE id=\"$id\"");
 }
 function setStandardLocation($id, $location)                                   // setStandardLocation sets a new standard location for the given observer
 { $GLOBALS['objDatabase']->execSQL("UPDATE observers SET stdlocation = \"$location\" WHERE id=\"$id\"");
 }
 function setStandardTelescope($id, $telescope)                                 // setStandardTelescope sets a new standard telescope for the given observer
 { $GLOBALS['objDatabase']->execSQL("UPDATE observers SET stdtelescope = \"$telescope\" WHERE id=\"$id\"");
 }
 function setUseLocal($id, $local_time)                                         // setUseLocal lets the user use local time for everything
 { if ($local_time == 0)
    $GLOBALS['objDatabase']->execSQL("UPDATE observers SET UT=\"1\" WHERE id=\"$id\"");
   else
    $GLOBALS['objDatabase']->execSQL("UPDATE observers SET UT=\"0\" WHERE id=\"$id\"");
 }
 function validateObserver($id, $role)                                          // validateObserver validates the user with the given id and gives the user  the given role (which should be $ADMIN or $USER).
 { $GLOBALS['objDatabase']->execSQL("UPDATE observers SET role = \"$role\" WHERE id=\"$id\"");
   $subject = LangValidateSubject;
   if ($role == RoleAdmin) $ad = LangValidateAdmin;
	 else                    $ad = "";
   $array = array(LangValidateMail1, $id, LangValidateMail2, $ad, LangValidateMail3);
   $body = implode("", $array);
   $administrators = $this->getAdministrators();
   $fromMail = $this->getEmail($administrators[0]);
   $headers = "From:".$fromMail;
   mail($this->getEmail($id), $subject, $body, $headers);
 }
 function showObservers()                                                       // showObservers prints a table showing all observers. 
 { $observers = $this->getObservers();
   $locations = new Locations;
   $instruments = new Instruments;
   $count = 0;
   echo "<table width=\"100%\">";
	 echo "<tr class=\"type3\">";
	 echo "<td>id</td>";
	 echo "<td>Name</td>";
	 echo "<td>First Name</td>";
	 echo "<td>Email</td>";
	 echo "<td>Std. Location</td>";
	 echo "<td>Std. Instrument</td>";
	 echo "<td>pwd</td>";
	 echo "<td>role</td>";
	 echo "<td>language</td>";
	 echo "</tr>";
   while(list ($key, $value) = each($observers))
   { $type = "class=\"type\"".(2-($count%2));
     $name = $this->getObserverName($value);
     $firstname = $this->getFirstName($value);
     $email = $this->getEmail($value);
     $loc = $this->getStandardLocation($value);
     $location = $locations->getLocationName($loc);
     $inst = $this->getStandardTelescope($value);
     $telescope = $instruments->getInstrumentName($inst);
     $password = $this->getPassword($value);
     echo "<tr $type>";
		 echo "<td> $value </td>";
		 echo "<td> $name </td>";
		 echo "<td> $firstname </td>";
		 echo "<td> <a href=\"mailto:$email\"> $email</a> </td>";
		 echo "<td> $location </td>";
		 echo "<td> $telescope </td>";
		 echo "<td> $password </td>";
		 echo "<td> ";
     $role = $this->getRole($value);
     if ($role == RoleAdmin)
       echo "admin";
     elseif ($role == RoleUser)
       echo "user";
     elseif ($role == RoleCometAdmin)
       echo "comet admin";
     elseif ($role == RoleWaitlist)
       echo "waitlist";
     $language = $this->getLanguage($value);
     echo "</td>";
		 echo "<td> $language </td>";
		 echo "</tr>";
     $count++;
  }
  echo "</table>";
 }
}
$objObserver=new Observers;
/* OBSOLETE FUNCTION
 function deleteObserver($id)                                                   // deleteObserver removes the observer with id = $id 
 { $GLOBALS['objDatabase']->execSQL("DELETE FROM observers WHERE id=\"$id\"");
 }
*/
?>
