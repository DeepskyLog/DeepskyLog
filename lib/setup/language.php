<?php
// language.php
// The language class collects all functions needed to work with different languages.

global $inIndex;
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";

class Language
{function getLanguages()                                                        // getLanguages returns a list of all available translations.
 { $mydir=opendir("locale/") ;
   $exclude=array("messages.pot",".","..", ".DS_Store");
   $langs=array();
   while($fn=readdir($mydir))
     if(!in_array($fn,$exclude))
       $langs[] = $fn;
    $langs[] = "en";
   closedir($mydir);
   for($i=0;$i<count($langs);$i++)                                              // $langs is now a list of all available translations (en, nl, de, ...)
   { $countrylist=$this->getAllLanguages($langs[$i]);
     $languages[$langs[$i]]=$countrylist[$langs[$i]];
   }
   return $languages;
 }
 function getLanguageName($lang, $lang2)                                        // getLanguageName returns the name of the language in the language lang2
 { $languages=$this->getAllLanguages($lang2);
   return $languages[$lang];
 }
 function getAvailableLanguages()
 { $xml=simplexml_load_file("lib/setup/language/languages.xml");
   $lang=$xml->lang;
   $countrylist=array();
   foreach ($lang as $item=>$data)
     $countrylist[sprintf($data->attributes()->code)] = sprintf($data->attributes()->$country);
   return $countrylist;
 }
 function getAllLanguages($cur_language)                                        // getAllLanguages returns all the available languages, in the given language
 { global $instDir;
   $xml=simplexml_load_file($instDir."lib/setup/language/languages.xml");
   $lang=$xml->lang;
   $countrylist=array();
   $country="name_".$cur_language;
   foreach($lang as $item=>$data)
     $countrylist[sprintf($data->attributes()->code)] = sprintf($data->attributes()->$country);
   asort($countrylist);
   return $countrylist;
 }
 function getLanguageKeys($cur_language)                                        // getAllLanguages returns all keys of the available languages, in the given language
 { $xml = simplexml_load_file("lib/setup/language/languages.xml");
   $lang = $xml->lang;
   $countrylist = array();
   $country="name_".$cur_language;
   foreach($lang as $item=>$data)
     $countrylist[]=sprintf($data->attributes()->code);
   return $countrylist;
 }
 function setLocale()
 {
   // When adding a new language, also add the correct locale here!
   // LOCALES: NL: nl_NL, EN: en_US, FR: fr_FR, DE: de_DE, ES: es_ES
   if (strcmp($_SESSION['lang'], "nl") == 0) {
     setlocale(LC_ALL, 'nl_NL');
   } else if (strcmp($_SESSION['lang'], "fr") == 0) {
     setlocale(LC_ALL, 'fr_FR');
   } else if (strcmp($_SESSION['lang'], "de") == 0) {
     setlocale(LC_ALL, 'de_DE');
   } else if (strcmp($_SESSION['lang'], "es") == 0) {
     setlocale(LC_ALL, 'es_ES');
   } else {
     setlocale(LC_ALL, 'en_EN');
   }
 }
}
?>
