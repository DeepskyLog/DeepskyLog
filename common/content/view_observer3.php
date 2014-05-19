<?php 
// view_observer.php
// shows accomplishments of an observer 

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!($user=$objUtil->checkGetKey('user'))) throw new Exception(LangException015b);
else view_observer();

function view_observer()
{ global $user,$modules,$deepsky,$comets,$baseURL,$instDir,$loggedUser,$objDatabase,
         $objInstrument,$objPresentations,$objObservation,$objUtil,$objCometObservation,$objObserver,$objLocation;
  $name=$objObserver->getObserverProperty($user,'name'); 
  $firstname=$objObserver->getObserverProperty($user,'firstname');
  echo "<div id=\"main\">";
  $objPresentations->line(array("<h4>".$firstname.' '. $name."</h4>"),"L",array(),30);
  echo "<hr />";
  echo "<ol id=\"toc\">
	       <li><a href=\"" . $baseURL . "index.php?indexAction=detail_observer&user=" . $user . "\"><span>" . GraphInfo . "</span></a></li>
	       <li><a href=\"" . $baseURL . "index.php?indexAction=detail_observer1&user=" . $user . "\"><span>" . GraphObservationsTitle . "</span></a></li>
	       <li><a href=\"" . $baseURL . "index.php?indexAction=detail_observer2&user=" . $user . "\"><span>" . GraphObservationsType . "</span></a></li>
	       <li class=\"current\"><a href=\"" . $baseURL . "index.php?indexAction=detail_observer3&user=" . $user . "\"><span>" . GraphAccomplishments . "</span></a></li>
	      </ol>";
  // Messier
  echo "<h2>Messier</h2>";                  

  $numberOfMessiers = $objObservation->getObservedCountFromCatalogOrList($user,"M");

  // TODO : Make pretty
  if ($numberOfMessiers >= 25) {
  	print "<b>Brons</b>";
  } else {
   	print "Brons";
  }
  print " - ";
  if ($numberOfMessiers >= 50) {
   	print "<b>Zilver</b>";
  } else {
   	print "Zilver";
  }
  print " - ";
  if ($numberOfMessiers >= 110) {
   	print "<b>Goud</b>";
  } else {
   	print "Goud";
  }

  // Caldwell
  $numberOfCaldwells = $objObservation->getObservedCountFromCatalogOrList($user,"Caldwell");

  echo "<h2>Caldwell</h2>";                  

  // TODO : Make pretty
  if ($numberOfCaldwells >= 25) {
  	print "<b>Brons</b>";
  } else {
   	print "Brons";
  }
  print " - ";
  if ($numberOfCaldwells >= 50) {
   	print "<b>Zilver</b>";
  } else {
   	print "Zilver";
  }
  print " - ";
  if ($numberOfCaldwells >= 110) {
   	print "<b>Goud</b>";
  } else {
   	print "Goud";
  }

  // Herschel - 400
  $numberOfHerschels = $objObservation->getObservedCountFromCatalogOrList($user,"H400");

  echo "<h2>Herschel 400</h2>";                  

  // TODO : Make pretty
  if ($numberOfHerschels >= 25) {
   	print "<b>Brons</b>";
  } else {
   	print "Brons";
  }
  print " - ";
  if ($numberOfHerschels >= 50) {
   	print "<b>Zilver</b>";
  } else {
   	print "Zilver";
  }
  print " - ";
  if ($numberOfHerschels >= 100) {
   	print "<b>Goud</b>";
  } else {
   	print "Goud";
  }
  print " - ";
  if ($numberOfHerschels >= 200) {
   	print "<b>Diamant</b>";
  } else {
   	print "Diamant";
  }
  print " - ";
  if ($numberOfHerschels >= 400) {
   	print "<b>Platina</b>";
  } else {
   	print "Platina";
  }

  // Herschel II
  $numberOfHIIs = $objObservation->getObservedCountFromCatalogOrList($user,"HII");

  echo "<h2>Herschel II</h2>";                  

  // TODO : Make pretty
  if ($numberOfHIIs >= 25) {
   	print "<b>Brons</b>";
  } else {
   	print "Brons";
  }
  print " - ";
  if ($numberOfHIIs >= 50) {
   	print "<b>Zilver</b>";
  } else {
   	print "Zilver";
  }
  print " - ";
  if ($numberOfHIIs >= 100) {
   	print "<b>Goud</b>";
  } else {
   	print "Goud";
  }
  print " - ";
  if ($numberOfHIIs >= 200) {
   	print "<b>Diamant</b>";
  } else {
   	print "Diamant";
  }
  print " - ";
  if ($numberOfHIIs >= 400) {
   	print "<b>Platina</b>";
  } else {
   	print "Platina";
  }

  // Total number of observations, different objects
   
  // Drawings (total number of drawings, also of 25 / 50 / 100 open clusters, ...)
  echo "<h2>Drawings</h2>";
  $drawingsMade = $objObservation->getDsDrawingsCountFromObserver($user);
   
   
  printScale($drawingsMade);

  // Comet observer
  echo "<h2>Comets</h2>";
  $userCometobservation=$objObserver->getNumberOfCometObservations($user);

  printScale($userCometobservation);

  // Number of different comets
  echo "<h2>Number of comets</h2>";
  $userCometObjects = $objCometObservation->getNumberOfObjects($user);
   
  printScale($userCometObjects);
	   
  // Open clusters
  echo "<h2>Open clusters</h2>";
  $opncl = count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"OPNCL\" and observations.observerid = \"" . $user . "\""));
  $opncl += count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"CLANB\" and observations.observerid = \"" . $user . "\""));

  printScale($opncl, 1700);   
      
  // Also drawings of Open Clusters
  echo "<h2>Drawings of Open clusters</h2>";
  
  $opnclDr = count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"OPNCL\" and observations.observerid = \"" . $user . "\" and observations.hasDrawing = 1"));
  $opnclDr += count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"CLANB\" and observations.observerid = \"" . $user . "\" and observations.hasDrawing = 1"));

  printScale($opnclDr, 1700);   
   
  // Globular cluster
  echo "<h2>Globular clusters</h2>";
   
  $glocl = count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"GLOCL\" and observations.observerid = \"" . $user . "\""));
   
  printScale($glocl, 152);

  // Also drawings of Globular Clusters
  echo "<h2>Drawings of Globular clusters</h2>";
  
  $gloclDr = count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"GLOCL\" and observations.observerid = \"" . $user . "\" and observations.hasDrawing = 1"));
   
  printScale($gloclDr, 152);

  // Planetary nebulae
  echo "<h2>Planetary nebulae</h2>";
   
  $plnnb = count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"PLNNB\" and observations.observerid = \"" . $user . "\""));

  printScale($plnnb, 1023);

  // Also drawings of Planetaries
  echo "<h2>Drawings of Planetary Nebulae</h2>";
  
  $plnnbDr = count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"PLNNB\" and observations.observerid = \"" . $user . "\" and observations.hasDrawing = 1"));

  printScale($plnnbDr, 1023);

  // Galaxies
  echo "<h2>Galaxies</h2>";
   
  $galxy = count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"GALXY\" and observations.observerid = \"" . $user . "\""));

  printScale($galxy);
   
  // Also drawings of Galaxies
  echo "<h2>Drawings of Galaxies</h2>";
  
  $galxyDr = count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"GALXY\" and observations.observerid = \"" . $user . "\" and observations.hasDrawing = 1"));

  printScale($galxyDr);

  // Nebulae
  echo "<h2>Nebulae</h2>";
   
  $eminb = count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"EMINB\" and observations.observerid = \"" . $user . "\""));
  $eminb += count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"ENRNN\" and observations.observerid = \"" . $user . "\""));
  $eminb += count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"ENSTR\" and observations.observerid = \"" . $user . "\""));
  $eminb += count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"REFNB\" and observations.observerid = \"" . $user . "\""));
  $eminb += count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"RNHII\" and observations.observerid = \"" . $user . "\""));
  $eminb += count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"HII\" and observations.observerid = \"" . $user . "\""));
  $eminb += count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"SNREM\" and observations.observerid = \"" . $user . "\""));
  $eminb += count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"WRNEB\" and observations.observerid = \"" . $user . "\""));
  
  printScale($eminb, 384);

  // Also drawings of Nebulae
  echo "<h2>Drawings of Nebulae</h2>";

  $eminbDr = count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"EMINB\" and observations.observerid = \"" . $user . "\" and observations.hasDrawing = 1"));
  $eminbDr += count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"ENRNN\" and observations.observerid = \"" . $user . "\" and observations.hasDrawing = 1"));
  $eminbDr += count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"ENSTR\" and observations.observerid = \"" . $user . "\" and observations.hasDrawing = 1"));
  $eminbDr += count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"REFNB\" and observations.observerid = \"" . $user . "\" and observations.hasDrawing = 1"));
  $eminbDr += count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"RNHII\" and observations.observerid = \"" . $user . "\" and observations.hasDrawing = 1"));
  $eminbDr += count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"HII\" and observations.observerid = \"" . $user . "\" and observations.hasDrawing = 1"));
  $eminbDr += count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"SNREM\" and observations.observerid = \"" . $user . "\" and observations.hasDrawing = 1"));
  $eminbDr += count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"WRNEB\" and observations.observerid = \"" . $user . "\" and observations.hasDrawing = 1"));
  
  printScale($eminbDr, 384);

  // At least 5 open clusters, globular clusters, ...
  echo "<h2>Allrounder</h2>";

  // Number of different DeepSky objects   
  $totalDSobjects = $objObservation->getNumberOfObjects($user);
   
  printScale($totalDSobjects);

  echo "</div>";
  echo "<br />";
}

function printScale($count, $max = 99999) {
	// TODO : Make pretty
	
   // Newbie
   // Rookie
   // Beginner
   // Talented
   // Skilled
   // Intermediate
   // Experienced
   // Advanced
   // Senior
   // Expert
   
  if (1 < $max) {
   if ($count >= 1) {
   	print "<b>Newbie</b>";
   } else {
   	print "Newbie";
   }
  }
  if (10 < $max) {
   print " - ";
   if ($count >= 10) {
   	print "<b>Rookie</b>";
   } else {
   	print "Rookie";
   }
  }
  if (25 < $max) {
   print " - ";
   if ($count >= 25) {
   	print "<b>Beginner</b>";
   } else {
   	print "Beginner";
   }
  }
  if (50 < $max) {
   print " - ";
   if ($count >= 50) {
   	print "<b>Talented</b>";
   } else {
   	print "Talented";
   }
  }
  if (100 < $max) {
   print " - ";
   if ($count >= 100) {
   	print "<b>Skilled</b>";
   } else {
   	print "Skilled";
   }
  }
  if (250 < $max) {
   print " - ";
   if ($count >= 250) {
   	print "<b>Intermediate</b>";
   } else {
   	print "Intermediate";
   }
  }
  if (500 < $max) {
   print " - ";
   if ($count >= 500) {
   	print "<b>Experienced</b>";
   } else {
   	print "Experienced";
   }
  }
  if (1000 < $max) {
   print " - ";
   if ($count >= 1000) {
   	print "<b>Advanced</b>";
   } else {
   	print "Advanced";
   }
  }
  if (2500 < $max) {
   print " - ";
   if ($count >= 2500) {
   	print "<b>Senior</b>";
   } else {
   	print "Senior";
   }
  }
  if (5000 < $max) {
   print " - ";
   if ($count >= 5000) {
   	print "<b>Expert</b>";
   } else {
   	print "Expert";
   }
  }
}
?>
