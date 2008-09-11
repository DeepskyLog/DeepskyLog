<?php
//version 3.1, DE 20061119

 session_start();
 include_once "language.php";
 include_once "databaseInfo.php";
 include_once $instDir . "/lib/observers.php";
 //include_once "../lib/observers.php";

 define("RoleAdmin", 0);
 define("RoleUser", 1);
 define("RoleWaitlist", 2);
 define("RoleRemoved", 3);
 define("RoleCometAdmin", 4);

 // Include the definitions for the specific language.
 $lang = new Language;

 if (isset($_COOKIE["deepskylogsec"])) // cookie set
 {
   $_SESSION['deepskylog_id'] = substr($_COOKIE['deepskylogsec'],32,255);
   $obs = new Observers();
   if($obs->getRole($_SESSION['deepskylog_id']) == "0") // administrator logs in
   {
      $_SESSION['admin'] = "yes"; // set session variable
   }
   $_SESSION['lang'] = $obs->getLanguage($_SESSION['deepskylog_id']); // set language variable
 }
 else if (!array_key_exists('lang',$_SESSION) || !$_SESSION['lang'])
 {
  $_SESSION['lang'] = $defaultLanguage;
 }
 $language = $lang->getPath($_SESSION['lang']);
 include "$language";

 
 define("OldUranometria", 0);
 define("NewUranometria", 1);
 define("SkyAtlas", 2);
 define("MilleniumStarAtlas", 3);
 define("Taki", 4);
 define("psa", 5);
 define("torresB", 6);
 define("torresBC", 7);
 define("torresC", 8);
 
 $atlases[0] = LangQueryObjectsUrano;
 $atlases[1] = LangQueryObjectsUranonew;
 $atlases[2] = LangQueryObjectsSkyAtlas;
 $atlases[3] = LangQueryObjectsMsa;
 $atlases[4] = LangQueryObjectsTaki;
 $atlases[5] = LangQueryObjectsPsa;
 $atlases[6] = LangQueryObjectsTorresB;
 $atlases[7] = LangQueryObjectsTorresBC;
 $atlases[8] = LangQueryObjectsTorresC;
  
 define("InstrumentOther", -1);
 define("InstrumentNakedEye", 0);
 define("InstrumentBinoculars", 1);
 define("InstrumentRefractor", 2);
 define("InstrumentReflector", 3);
 define("InstrumentFinderscope", 4);
 define("InstrumentRest", 5);
 define("InstrumentCassegrain", 6);
 define("InstrumentKutter", 7);
 define("InstrumentMaksutov", 8);
 define("InstrumentSchmidtCassegrain", 9);

 define("FilterOther", 0);
 define("FilterBroadBand", 1);
 define("FilterNarrowBand", 2);
 define("FilterOIII", 3);
 define("FilterHBeta", 4);
 define("FilterHAlpha", 5);
 define("FilterColor", 6);
 define("FilterNeutral", 7);
 define("FilterCorrective", 8);

 define("FilterColorLightRed", "1");
 define("FilterColorRed", "2");
 define("FilterColorDeepRed", "3");
 define("FilterColorOrange", "4");
 define("FilterColorLightYellow", "5");
 define("FilterColorDeepYellow", "6");
 define("FilterColorYellow", "7");
 define("FilterColorYellowGreen", "8");
 define("FilterColorLightGreen", "9");
 define("FilterColorGreen", "10");
 define("FilterColorMediumBlue", "11");
 define("FilterColorPaleBlue", "12");
 define("FilterColorBlue", "13");
 define("FilterColorDeepBlue", "14");
 define("FilterColorDeepViolet", "15");
?>
