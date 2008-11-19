<?php

// Class Atlasses
// INTERFACE
//   property atlassesCodes
//   function getAtlasses()
//   function getSortedAtlasses()
//   function getAtlasPage($atlas, $name)
//   function calculateUranometriaPage($ra, $decl)
//   function calculatePocketSkyAtlasPage($ra, $decl)
//   function calculateTorresCPage($ra, $decl)
//   function calculateTorresBCPage($ra, $decl)
//   function calculateTorresBPage($ra, $decl)
//   function calculateNewUranometriaPage($ra, $decl)
//   function calculateTakiPage($ra, $decl)
//   function calculateSkyAtlasPage($ra, $decl)
//   function calculateMilleniumPage($ra, $decl)
// PUBLIC OBJECT
//   $objAtlas


class Atlasses
{

  public $atlasCodes=array();
	
 	public function __construct()
	{ $this->atlasCodes=$this->getSortedAtlasses();
  }	

  function getAtlasses()
	{ return $GLOBALS['objDatabase']->selectSingleArray('SELECT atlasCode FROM atlasses;',atlasCode);
	}
  function getSortedAtlasses()
	{ $atlasses = array();
    $run = mysql_query('SELECT atlasCode FROM atlasses;') 
		       or die(mysql_error());
	  while($get = mysql_fetch_object($run))
	    $atlasses[$get->atlasCode]=$GLOBALS['AtlasName' . $get->atlasCode];
    asort($atlasses);
		return $atlasses;
	}
 function getAtlasPage($atlas, $object)
 {return $GLOBALS['objDatabase']->selectSingleValue("SELECT " . $atlas . " FROM objects WHERE name = \"".$object."\"",$atlas);
 }	
 // calculateUranometriaPage calculates the old uranometriapage of the object
 function calculateUranometriaPage($ra, $decl)
 {
  $urano = 0;

  /* Page from uranometria */
  /* 90 to 85 */
  if ($decl >= 85)
  {
   if ($ra < 12)
   {
    $urano = 1;
   }
   else
   {
    $urano = 2;
   }
  }

  /* 84 to 73 */
  else if ($decl >= 73)
  {
   if (($ra >= 1) && ($ra < 23))
   {
    $urano = (int)$ra - 1;
    $urano = $urano / 2;
    $urano = $urano + 4;
   }
   else
   {
    $urano = 3;
   }
  }

  /* 72 to 61 */
  else if ($decl >= 61)
  {
   $hulp = (int)$ra * 60;
   if (($hulp >= 32) && ($hulp < 1400))
   {
    $urano = (($hulp - 32) / 72) + 16;
   }
   else
   {
    $urano = 15;
   }
  }

  /* 60 to 50 */
  else if ($decl >= 50)
  {
   $hulp = (int)($ra * 60.0);
   if (($hulp >= 28) && ($hulp < 1408))
   {
    $urano = (($hulp - 28) / 60) + 36;
   }
   else
   {
    $urano = 35;
   }
  }

  /* 49 to 39 */
  else if ($decl >= 39)
  {
   $hulp = (int)($ra * 60.0);
   if (($hulp >= 24) && ($hulp < 1416))
   {
    $urano = (($hulp - 24) / 48) + 60;
   }
   else
   {
    $urano = 59;
   }
  }

  /* 38 to 28 */
  else if ($decl >= 28)
  {
   $hulp = (int)($ra * 60.0);
   if (($hulp >= 20) && ($hulp < 1420))
   {
    $urano = (($hulp - 20) / 40) + 90;
   }
   else
   {
    $urano = 89;
   }
  }

  /* 27 to 17 */
  else if ($decl >= 17)
  {
   $hulp = (int)($ra * 60.0);
   if (($hulp >= 16) && ($hulp < 1424))
   {
    $urano = (($hulp - 16) / 32) + 126;
   }
   else
   {
    $urano = 125;
   }
  }

  /* 16 to 6 */
  else if ($decl >= 6)
  {
   $hulp = (int)($ra * 60.0);
   if (($hulp >= 16) && ($hulp < 1424))
   {
    $urano = (($hulp - 16) / 32) + 171;
   }
   else
   {
    $urano = 170;
   }
  }

  /* 5 to -5 */
  else if ($decl >= -5)
  {
   $hulp = (int)($ra * 60.0);
   if (($hulp >= 16) && ($hulp < 1424))
   {
    $urano = (($hulp - 16) / 32) + 216;
   }
   else
   {
    $urano = 215;
   }
  }

  /* -16 to -6 */
  else if ($decl >= -16)
  {
   $hulp = (int)($ra * 60.0);
   if (($hulp >= 16) && ($hulp < 1424))
   {
    $urano = (($hulp - 16) / 32) + 261;
   }
   else
   {
    $urano = 260;
   }
  }

  /* -27 to -17 */
  else if ($decl >= -27)
  {
   $hulp = (int)($ra * 60.0);
   if (($hulp >= 16) && ($hulp < 1424))
   {
    $urano = (($hulp - 16) / 32) + 306;
   }
   else
   {
    $urano = 305;
   }
  }

  /* -38 to -28 */
  else if ($decl >= -38)
  {
   $hulp = (int)($ra * 60.0);
   if (($hulp >= 20) && ($hulp < 1420))
   {
    $urano = (($hulp - 20) / 40) + 351;
   }
   else
   {
    $urano = 350;
   }
  }

  /* -49 to -39 */
  else if ($decl >= -49)
  {
   $hulp = (int)($ra * 60.0);
   if (($hulp >= 24) && ($hulp < 1416))
   {
    $urano = (($hulp - 24) / 48) + 387;
   }
   else
   {
    $urano = 386;
   }
  }

  /* -60 to -50 */
  else if ($decl >= -60)
  {
   $hulp = (int)($ra * 60.0);
   if (($hulp >= 28) && ($hulp < 1408))
   {
    $urano = (($hulp - 28) / 60) + 417;
   }
   else
   {
    $urano = 416;
   }
  }

  /* -72 to -61 */
  else if ($decl >= -72)
  {
   $hulp = (int)($ra * 60.0);
   if (($hulp >= 32) && ($hulp < 1400))
   {
    $urano = (($hulp - 32) / 72) + 441;
   }
   else
   {
    $urano = 440;
   }
  }

  /* -84 to -73 */
  else if ($decl >= -84)
  {
   if (($ra >= 1.0) && ($ra < 23.0))
   {
    $urano = (int)($ra) - 1;
    $urano = $urano / 2;
    $urano = $urano + 461;
   }
   else
   {
    $urano = 460;
   }
  }

  /* -90 to -85 */
  else
  {
   if ($ra < 12.0)
   {
    $urano = 473;
   }
   else
   {
    $urano = 472;
   }
  }

  return (int)$urano;
 }
 // calculatePocketSkyAtlas calculates the pocket sky atlas page of the object
 function calculatePocketSkyAtlasPage($ra, $decl)
 {
  $psa = 0;

  /* Page from pocket sky atlas */
  if ($ra >= 0.0 && $ra <= 3.0) 
  {
   if ($decl >= 60)
   {
     $psa = 1;
   } else if ($decl >= 30) {
     if ($ra <= 1.5) {
       $psa = 3;
     } else {
       $psa = 2;
     }
   } else if ($decl >= 0) {
     if ($ra <= 1.5) {
       $psa = 5;
     } else {
       $psa = 4;
     }
   } else if ($decl >= -30) {
     if ($ra <= 1.5) {
       $psa = 7;
     } else {
       $psa = 6;
     }
   } else if ($decl >= -60) {
     if ($ra <= 1.5) {
       $psa = 9;
     } else {
       $psa = 8;
     }
   } else {
     $psa = 10;
   }
  } else if ($ra >= 3.0 && $ra <= 6.0) 
  {
   if ($decl >= 60)
   {
     $psa = 11;
   } else if ($decl >= 30) {
     if ($ra <= 4.5) {
       $psa = 13;
     } else {
       $psa = 12;
     }
   } else if ($decl >= 0) {
     if ($ra <= 4.5) {
       $psa = 15;
     } else {
       $psa = 14;
     }
   } else if ($decl >= -30) {
     if ($ra <= 4.5) {
       $psa = 17;
     } else {
       $psa = 16;
     }
   } else if ($decl >= -60) {
     if ($ra <= 4.5) {
       $psa = 19;
     } else {
       $psa = 18;
     }
   } else {
     $psa = 20;
   }
  } else if ($ra >= 6.0 && $ra <= 9.0) {
   if ($decl >= 60)
   {
     $psa = 21;
   } else if ($decl >= 30) {
     if ($ra <= 7.5) {
       $psa = 23;
     } else {
       $psa = 22;
     }
   } else if ($decl >= 0) {
     if ($ra <= 7.5) {
       $psa = 25;
     } else {
       $psa = 24;
     }
   } else if ($decl >= -30) {
     if ($ra <= 7.5) {
       $psa = 27;
     } else {
       $psa = 26;
     }
   } else if ($decl >= -60) {
     if ($ra <= 7.5) {
       $psa = 29;
     } else {
       $psa = 28;
     }
   } else {
     $psa = 30;
   }
  } else if ($ra >= 9.0 && $ra <= 12.0) {
   if ($decl >= 60)
   {
     $psa = 31;
   } else if ($decl >= 30) {
     if ($ra <= 10.5) {
       $psa = 33;
     } else {
       $psa = 32;
     }
   } else if ($decl >= 0) {
     if ($ra <= 10.5) {
       $psa = 35;
     } else {
       $psa = 34;
     }
   } else if ($decl >= -30) {
     if ($ra <= 10.5) {
       $psa = 37;
     } else {
       $psa = 36;
     }
   } else if ($decl >= -60) {
     if ($ra <= 10.5) {
       $psa = 39;
     } else {
       $psa = 38;
     }
   } else {
     $psa = 40;
   }
  } else if ($ra >= 12.0 && $ra <= 15.0) {
   if ($decl >= 60)
   {
     $psa = 41;
   } else if ($decl >= 30) {
     if ($ra <= 13.5) {
       $psa = 43;
     } else {
       $psa = 42;
     }
   } else if ($decl >= 0) {
     if ($ra <= 13.5) {
       $psa = 45;
     } else {
       $psa = 44;
     }
   } else if ($decl >= -30) {
     if ($ra <= 13.5) {
       $psa = 47;
     } else {
       $psa = 46;
     }
   } else if ($decl >= -60) {
     if ($ra <= 13.5) {
       $psa = 49;
     } else {
       $psa = 48;
     }
   } else {
     $psa = 50;
   }
  } else if ($ra >= 15.0 && $ra <= 18.0) {
   if ($decl >= 60)
   {
     $psa = 51;
   } else if ($decl >= 30) {
     if ($ra <= 16.5) {
       $psa = 53;
     } else {
       $psa = 52;
     }
   } else if ($decl >= 0) {
     if ($ra <= 16.5) {
       $psa = 55;
     } else {
       $psa = 54;
     }
   } else if ($decl >= -30) {
     if ($ra <= 16.5) {
       $psa = 57;
     } else {
       $psa = 56;
     }
   } else if ($decl >= -60) {
     if ($ra <= 16.5) {
       $psa = 59;
     } else {
       $psa = 58;
     }
   } else {
     $psa = 60;
   }
  } else if ($ra >= 18.0 && $ra <= 21.0) {
   if ($decl >= 60)
   {
     $psa = 61;
   } else if ($decl >= 30) {
     if ($ra <= 19.5) {
       $psa = 63;
     } else {
       $psa = 62;
     }
   } else if ($decl >= 0) {
     if ($ra <= 19.5) {
       $psa = 65;
     } else {
       $psa = 64;
     }
   } else if ($decl >= -30) {
     if ($ra <= 19.5) {
       $psa = 67;
     } else {
       $psa = 66;
     }
   } else if ($decl >= -60) {
     if ($ra <= 19.5) {
       $psa = 69;
     } else {
       $psa = 68;
     }
   } else {
     $psa = 70;
   }
  } else if ($ra >= 21.0) {
   if ($decl >= 60)
   {
     $psa = 71;
   } else if ($decl >= 30) {
     if ($ra <= 22.5) {
       $psa = 73;
     } else {
       $psa = 72;
     }
   } else if ($decl >= 0) {
     if ($ra <= 22.5) {
       $psa = 75;
     } else {
       $psa = 74;
     }
   } else if ($decl >= -30) {
     if ($ra <= 22.5) {
       $psa = 77;
     } else {
       $psa = 76;
     }
   } else if ($decl >= -60) {
     if ($ra <= 22.5) {
       $psa = 79;
     } else {
       $psa = 78;
     }
   } else {
     $psa = 80;
   }   
  }
  return (int)$psa;
 }
 
// calculateTorresC calculates the TriAtlas C (torres) page of the object
 function calculateTorresCPage($ra, $decl)
 {
  $torresC = 0;

  /* Page from torres C atlas */
  if ($decl >= 79.0) {
   if ($ra <= 1.2 || $ra >= 22.8) {
     $torresC = 1;
   } else {
     $torresC = 10 - (int)(($ra - 1.2) / 2.4);
   }
  } else if ($decl >= 69.0 ) {
   if ($ra <= 0.666 || $ra >= 23.333) {
     $torresC = 11;
   } else {
     $torresC = 28 - (int)(($ra - 0.666) / 1.33);
   }
  } else if ($decl >= 58.0 ) {
   if ($ra <= 0.4616 || $ra >= 23.5383) {
     $torresC = 29;
   } else {
     $torresC = 54 - (int)(($ra - 0.4616) / 0.9233);
   }
  } else if ($decl >= 48.0 ) {
   if ($ra <= 0.3633 || $ra >= 23.6366) {
     $torresC = 55;
   } else {
     $torresC = 87 - (int)(($ra - 0.3633) / 0.7266);
   }
  } else if ($decl >= 37.0 ) {
   if ($ra <= 0.315 || $ra >= 23.685) {
     $torresC = 88;
   } else {
     $torresC = 125 - (int)(($ra - 0.315) / 0.630);
   }
  } else if ($decl >= 27.0 ) {
   if ($ra <= 0.2783 || $ra >= 23.7216) {
     $torresC = 126;
   } else {
     $torresC = 168 - (int)(($ra - 0.2783) / 0.5566);
   }
  } else if ($decl >= 16.0 ) {
   if ($ra <= 0.2616 || $ra >= 23.7383) {
     $torresC = 169;
   } else {
     $torresC = 214 - (int)(($ra - 0.2616) / 0.5233);
   }
  } else if ($decl >= 5.0 ) {
   if ($ra <= 0.25 || $ra >= 23.75) {
     $torresC = 215;
   } else {
     $torresC = 262 - (int)(($ra - 0.25) / 0.5);
   }
  } else if ($decl >= -5.0 ) {
   if ($ra <= 0.255 || $ra >= 23.745) {
     $torresC = 263;
   } else {
     $torresC = 309 - (int)(($ra - 0.255) / 0.51);
   }
  } else if ($decl >= -16.0 ) {
   if ($ra <= 0.25 || $ra >= 23.75) {
     $torresC = 310;
   } else {
     $torresC = 357 - (int)(($ra - 0.25) / 0.5);
   }
  } else if ($decl >= -26.0 ) {
   if ($ra <= 0.2616 || $ra >= 23.7383) {
     $torresC = 358;
   } else {
     $torresC = 403 - (int)(($ra - 0.2616) / 0.5233);
   }
  } else if ($decl >= -37.0 ) {
   if ($ra <= 0.2783 || $ra >= 23.7216) {
     $torresC = 404;
   } else {
     $torresC = 446 - (int)(($ra - 0.2783) / 0.5566);
   }
  } else if ($decl >= -47.0 ) {
   if ($ra <= 0.315 || $ra >= 23.685) {
     $torresC = 447;
   } else {
     $torresC = 484 - (int)(($ra - 0.315) / 0.63);
   }
   
  } else if ($decl >= -58.0 ) {
   if ($ra <= 0.3633 || $ra >= 23.6366) {
     $torresC = 485;
   } else {
     $torresC = 517 - (int)(($ra - 0.3633) / 0.7266);
   }
  } else if ($decl >= -68.0 ) {
   if ($ra <= 0.4616 || $ra >= 23.5383) {
     $torresC = 518;
   } else {
     $torresC = 543 - (int)(($ra - 0.4616) / 0.9233);
   }
  } else if ($decl >= -79.0 ) {
   if ($ra <= 0.666 || $ra >= 23.333) {
   	$torresC = 544;
   } else {
    $torresC = 561 - (int)(($ra - 0.666) / 1.33);
   }
  } else {
   if ($ra <= 1.2 || $ra >= 22.8)
   {
     $torresC = 562;
   } else {
     $torresC = 571 - (int)(($ra - 1.2) / 2.4);
   }
  }

  return (int)$torresC;
 }
 // calculateTorresBC calculates the TriAtlas BC (torres) page of the object
 function calculateTorresBCPage($ra, $decl)
 {
  $torresBC = 0;

  /* Page from torres BC atlas */
  if ($decl >= 72.0) {
   if ($ra <= 1.2 || $ra >= 22.8) {
     $torresBC = 1;
   } else {
     $torresBC = 10 - (int)(($ra - 1.2) / 2.4);
   }
  } else if ($decl >= 54.0 ) {
   if ($ra <= 0.666 || $ra >= 23.333) {
     $torresBC = 11;
   } else {
     $torresBC = 28 - (int)(($ra - 0.666) / 1.33);
   }
  } else if ($decl >= 36.0 ) {
   if ($ra <= 0.5 || $ra >= 23.5) {
     $torresBC = 29;
   } else {
     $torresBC = 52 - (int)(($ra - 0.5) / 1.0);
   }
  } else if ($decl >= 18.0 ) {
   if ($ra <= 0.42833 || $ra >= 23.57166) {
     $torresBC = 53;
   } else {
     $torresBC = 80 - (int)(($ra - 0.42833) / 0.85666);
   }
  } else if ($decl >= 0.0 ) {
   if ($ra <= 0.41333 || $ra >= 23.5866) {
     $torresBC = 81;
   } else {
     $torresBC = 109 - (int)(($ra - 0.41333) / 0.82666);
   }
  } else if ($decl >= -18.0 ) {
   if ($ra <= 0.41333 || $ra >= 23.5866) {
     $torresBC = 110;
   } else {
     $torresBC = 138 - (int)(($ra - 0.41333) / 0.82666);
   }
  } else if ($decl >= -36.0 ) {
   if ($ra <= 0.42833 || $ra >= 23.57166) {
     $torresBC = 139;
   } else {
     $torresBC = 166 - (int)(($ra - 0.42833) / 0.85666);
   }
  } else if ($decl >= -54.0 ) {
   if ($ra <= 0.5 || $ra >= 23.5) {
     $torresBC = 167;
   } else {
     $torresBC = 190 - (int)(($ra - 0.5) / 1.0);
   }
  } else if ($decl >= -72.0 ) {
   if ($ra <= 0.6666 || $ra >= 23.3333) {
     $torresBC = 191;
   } else {
     $torresBC = 208 - (int)(($ra - 0.6666) / 1.3333);
   }
  } else {
   if ($ra <= 1.2 || $ra >= 22.8) {
     $torresBC = 209;
   } else {
     $torresBC = 218 - (int)(($ra - 1.2) / 2.4);
   }
  }

  return (int)$torresBC;
 }
 // calculateTorresB calculates the TriAtlas B (torres) page of the object
 function calculateTorresBPage($ra, $decl)
 {
  $torresB = 0;

  /* Page from torres B atlas */
  if ($decl >= 64.28333) {
   if ($ra <= 1.2 || $ra >= 22.8) {
     $torresB = 1;
   } else {
     $torresB = 9 - (int)(($ra - 1.2) / 2.4);
   }
  } else if ($decl >= 38.56666 ) {
   if ($ra <= 0.75 || $ra >= 23.25) {
     $torresB = 10;
   } else {
     $torresB = 25 - (int)(($ra - 0.75) / 1.5);
   }
  } else if ($decl >= 12.85 ) {
   if ($ra <= 0.63166 || $ra >= 23.36833) {
     $torresB = 26;
   } else {
     $torresB = 44 - (int)(($ra - 0.63166) / 1.2633);
   }
  } else if ($decl >= -12.85 ) {
   if ($ra <= 0.63166 || $ra >= 23.36833) {
     $torresB = 45;
   } else {
     $torresB = 63 - (int)(($ra - 0.63166) / 1.2633);
   }
  } else if ($decl >= -38.56666 ) {
   if ($ra <= 0.63166 || $ra >= 23.36833) {
     $torresB = 64;
   } else {
     $torresB = 82 - (int)(($ra - 0.63166) / 1.2633);
   }
  } else if ($decl >= -64.28333 ) {
   if ($ra <= 0.75 || $ra >= 23.25) {
     $torresB = 83;
   } else {
     $torresB = 98 - (int)(($ra - 0.75) / 1.5);
   }
  } else {
   if ($ra <= 1.2 || $ra >= 22.8) {
     $torresB = 99;
   } else {
     $torresB = 107 - (int)(($ra - 1.2) / 2.4);
   }
  }
  return (int)$torresB;
 }
 // calculateNewUranometriaPage calculates the new uranometriapage of the object
 function calculateNewUranometriaPage($ra, $decl)
 {
  $data = array(array(  84.5,   1,  1),  // 1st tier, chart 1
                array(  73.5,   7,  6),  // 2nd tier, charts 2->7
                array(  62.5,  17, 10),  // 3rd tier, charts 8->17
                array(  51.5,  29, 12),  // 4th tier, charts 18->29
                array(  40.5,  44, 15),  // 5th tier, charts 30->44
		array(  29.5,  62, 18),  // 6th tier, charts 45->62
  		array(  17.5,  80, 18),  // 7th tier, charts 63->80
		array(   5.5, 100, 20),  // 8th tier, charts 81->100
		array(  -5.5, 120, 20),  // 9th tier, charts 101->120
		array( -17.5, 140, 20),  // 10th tier, charts 121->140
		array( -29.5, 158, 18),  // 11th tier, charts 141->158
		array( -40.5, 176, 18),  // 12th tier, charts 159->176
		array( -51.5, 191, 15),  // 13th tier, charts 177->191
		array( -62.5, 203, 12),  // 14th tier, charts 192->203
		array( -73.5, 213, 10),  // 15th tier, charts 204->213
		array( -84.5, 219,  6),  // 16th tier, charts 214->219
		array( -90.0, 220,  1)); // 17th tier, chart 220

  // find proper tier
  for ($Tier = 0; $decl < $data[$Tier][0]; $Tier++);

  $HoursPerChart = 24.0 / $data[$Tier][2];

  $ra = $ra - ($HoursPerChart / 2);

  // Offset; middle of 1st map is in the middle of 0 hours RA

  $MapOffset = (int)($ra / $HoursPerChart);

  return (int)($data[$Tier][1] - $MapOffset);
 }
 // calculateTakiPage calculates the taki page of the object
 function calculateTakiPage($ra, $decl)
 {
   if ($decl >= 83)
   {
     $taki = 1;
   }
   else if ($decl >= 62)
   {
     $taki = 2 + floor((24 - $ra) / 2);
   }
   else if ($decl >= 37)
   {
     $taki = 14 + floor(24 - $ra);
   }
   else if ($decl >= 12)
   {
     $taki = 38 + floor(24 - $ra);
   }
   else if ($decl >= -12)
   {
     $taki = 62 + floor(24 - $ra);
   }
   else if ($decl >= -37)
   {
     $taki = 86 + floor(24 - $ra);
   }
   else if ($decl >= -62)
   {
     $taki = 110 + floor(24 - $ra);
   }
   else if ($decl >= -83)
   {
     $taki = 134 + floor((24 - $ra) / 2);
   }
   else
   {
     $taki = 146;
   }
   return $taki;
 }
 // calculateSkyAtlasPage calculates the page from the Sky Atlas of the object
 function calculateSkyAtlasPage($ra, $decl)
 {
  $data = array(array(  50.0,   1,  3),  // 1st tier, charts 1->3
                array(  20.0,   4,  6),  // 2nd tier, charts 4->9
                array( -20.0,  10,  8),  // 3rd tier, charts 10->17
                array( -50.0,  18,  6),  // 4th tier, charts 18->23
                array( -90.0,  24,  3)); // 5th tier, charts 24->26

  // find proper tier
  for ($Tier = 0; $decl < $data[$Tier][0]; $Tier++);

  $HoursPerChart = 24.0 / $data[$Tier][2];

  // Offset; middle of 1st map is in the middle of 0 hours RA

  $MapOffset = (int)($ra / $HoursPerChart);

  return (int)($data[$Tier][1] + $MapOffset);
 }
 // calculateMilleniumPage calculates the page from the Millenium Star atlas 
 // of the object
 function calculateMilleniumPage($ra, $decl)
 {
  $rao = $ra;
  if (abs($decl) > 87)
  {
   $ra = 0;
  }
 
  if ($ra >= 0 && $ra <= 8)
  {
   $vol = "I";
   $vl = 0;
  }
  
  if ($ra > 8 && $ra <= 16)
  {
   $vol = "II";
   $vl = 1;
  }

  if ($ra > 16 && $ra < 24)
  {
   $vol = "III";
   $vl = 2;
  }

  $pa = 0;
  $qt = 0;
  $qn = 0;

  if (abs($decl) <= 90)
  {
   $pa = 240;
   $qt = $qt + 2;
   $qn = 2;
  }
  
  if (abs($decl) < 87)
  {
   $pa = 120;
   $qt = $qt + 4;
   $qn = 4;
  }

  if (abs($decl) < 81)
  {
   $pa = 60;
   $qt = $qt + 8;
   $qn = 8;
  }

  if (abs($decl) < 75)
  {
   $pa = 48;
   $qt = $qt + 10;
   $qn = 10;
  }

  if (abs($decl) < 69)
  {
   $pa = 40;
   $qt = $qt + 12;
   $qn = 12;
  }

  if (abs($decl) < 63)
  {
   $pa = 480 / 14;
   $qt = $qt + 14;
   $qn = 14;
  }

  if (abs($decl) < 57)
  {
   $pa = 30;
   $qt = $qt + 16;
   $qn = 16;
  }

  if (abs($decl) < 51)
  {
   $pa = 24;
   $qt = $qt + 20;
   $qn = 20;
  }

  if (abs($decl) < 45)
  {
   $pa = 24;
   $qt = $qt + 20;
   $qn = 20;
  }

  if (abs($decl) < 39)
  {
   $pa = 480 / 22;
   $qt = $qt + 22;
   $qn = 22;
  }

  if (abs($decl) < 33)
  {
   $pa = 480 / 22;
   $qt = $qt + 22;
   $qn = 22;
  }

  if (abs($decl) < 27)
  {
   $pa = 20;
   $qt = $qt + 24;
   $qn = 24;
  }

  if (abs($decl) < 21)
  {
   $pa = 20;
   $qt = $qt + 24;
   $qn = 24;
  }

  if (abs($decl) < 15)
  {
   $pa = 20;
   $qt = $qt + 24;
   $qn = 24;
  }

  if (abs($decl) < 9)
  {
   $pa = 20;
   $qt = $qt + 24;
   $qn = 24;
  }

  if (abs($decl) < 3)
  {
   $pa = 20;
   $qt = $qt + 24;
   $qn = 24;
  }

  if ($ra == 8)
  {
   $ra = 7.99;
  }

  if ($ra == 16)
  {
   $ra = 15.99;
  }

  if ($ra == 24)
  {
   $ra = 23.99;
  }

  if ($ra > $vl * 8)
  {
   $ra = $ra - ($vl * 8);
  }

  $ca = (int)(($ra * 60) / $pa);

  if (abs($decl) > 87 && ($rao > 4 && $rao < 16))
  {
   $qt = 1;
   $qn = 0;
  }

  $ch = $qt - $ca + ($vl * 516);

  if ($decl < 0)
  {
   $ch = 516 + ($vl * 516) - $qt + $qn - $ca;
  }

 // return $ch."/".$vol;
  return $ch;
 }
 
}
$objAtlas=new Atlasses;

?>
