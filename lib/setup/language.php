<?php

// The language class collects all functions needed to work with different
// languages.
//
// Version 0.2 : 21/12/2004, WDM
// Version 1.0 : 01/04/2007, WDM
//

//include_once "databaseInfo.php";
//include_once "../util.php";
//$util = new Util();
//$util->checkUserInput();


// The php variable register_globals should be turned off. If register_globals
// is turned on, deepskylog will not install.
//if (ini_get('register_globals'))
//{
// $superglobals = array($_SERVER, $_ENV, $_FILES, $_COOKIE, $_POST, $_GET);
// if (isset($_SESSION))
// {
//  array_unshift($superglobals, $_SESSION);
// }
// foreach ($superglobals as $superglobal)
// {
//  foreach ($superglobal as $global => $value)
//  {
//   unset($GLOBALS[$global]);
//  }
// }
// ini_set('register_globals', false);
//}

class Language
{
 // getLanguages returns a list of all available translations.
 function getLanguages()
 {
   $maindir = "../lib/setup/language/" ;
   $mydir = opendir($maindir) ;
   $exclude = array( "index.php" , ".", "..", ".svn", "languages.xml") ;
   $langs = array();
   while($fn = readdir($mydir))
   { 
    if ($fn == $exclude[0] || $fn == $exclude[1] || $fn == $exclude[2] || $fn == $exclude[3] || $fn == $exclude[4]) continue;
    $langs[] = $fn;
   }
   closedir($mydir);

   // $langs is now a list of all available translations (en, nl, de, ...)

   for($i =  0;$i < count($langs);$i = $i + 1)
   {
     $countrylist = $this->getAllLanguages($langs[$i]);
     $languages[$langs[$i]] = $countrylist[$langs[$i]];
   }

   return $languages;
 }

 // getLanguageName returns the name of the language in the language lang2
 function getLanguageName($lang, $lang2)
 {
   $languages = $this->getAllLanguages($lang2);
   return $languages[$lang];
 }

 function getAvailableLanguages()
 {
   $xml = simplexml_load_file("../lib/setup/language/languages.xml");
   $lang = $xml->lang;
   $countrylist = array();
   foreach ($lang as $item => $data) {
     $countrylist[sprintf($data->attributes()->code)] = sprintf($data->attributes()->$country);
   }
   return $countrylist;
 }

 // getAllLanguages returns all the available languages, in the given language
 function getAllLanguages($cur_language)
 {
   $xml = simplexml_load_file("../lib/setup/language/languages.xml");
   $lang = $xml->lang;
   $countrylist = array();
   $country = "name_".$cur_language;
   foreach ($lang as $item => $data) {
     $countrylist[sprintf($data->attributes()->code)] = sprintf($data->attributes()->$country);
   }
   return $countrylist;
 }

 // getAllLanguages returns all keys of the available languages, in the given language
 function getLanguageKeys($cur_language)
 {
   $xml = simplexml_load_file("../lib/setup/language/languages.xml");
   $lang = $xml->lang;
   $countrylist = array();
   $country = "name_".$cur_language;
   foreach ($lang as $item => $data) {
     $countrylist[] = sprintf($data->attributes()->code);
   }
   return $countrylist;
 }

 // getPath returns the path to include for the given language.
 function getPath($lang)
 {
   $path = "language/$lang/lang_main.php";
   return $path;
 }
}
?>
