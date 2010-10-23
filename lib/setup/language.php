<?php
// The language class collects all functions needed to work with different
// languages.

class Language
{function getLanguages()                                                        // getLanguages returns a list of all available translations.
 { $mydir=opendir("lib/setup/language/") ;
   $exclude=array("index.php",".","..",".svn","languages.xml");
   $langs=array();
   while($fn=readdir($mydir))
     if(!in_array($fn,$exclude)) 
       $langs[] = $fn;
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
 function getPath($lang)                                                        // getPath returns the path to include for the given language.
 { $path="language/$lang/lang_main.php";
   return $path;
 }
}
?>
