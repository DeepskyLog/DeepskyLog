<?php // head.php - prints the html headers
echo "<head>";
echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\" />";
echo "<meta http-equiv=\"pragma\" content=\"no-cache\" />";
echo "<meta name=\"revisit-after\" content=\"1 day\" />";
echo "<meta name=\"copyright\" content=\"Copyright &copy; 2005-2009 VVS. Alle Rechten Voorbehouden.\" />";
echo "<meta name=\"author\" content=\"DeepskyLog - VVS\" />";
echo "<meta name=\"description\" content=\"Vereniging voor sterrenkunde\" />";
echo "<meta name=\"keywords\" content=\"VVS, Vereniging Voor Sterrenkunde, astronomie, sterrenkunde, JVS, Heelal, Astra, Hemelkalender, Sterrenkijkdag, Sterrenkijkdagen, sterr, Nieuws, Laatste nieuws\" />";
echo "<meta name=\"robots\" content=\"index, follow\" />";
echo "<base href=\"".$baseURL."\" />";
echo "<link rel=\"shortcut icon\" href=\"".$baseURL."styles/images/favicon.ico\" />";
echo "<link href=\"".$baseURL."styles/style.css\" rel=\"stylesheet\" type=\"text/css\" />";
echo "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"DeepskyLog - latest observations\" href=\"observations.rss\" />";
if($objUtil->checkRequestKey(('title')))
  echo "<title>DSL: ".$objUtil->checkRequestKey('title','')."</title>";  // 20081209 Here should come a better solution, see bug report 44
elseif($objUtil->checkRequestKey(('titleobject')))
  echo "<title>DSL: ".$objUtil->checkRequestKey('titleobject','')." ".$objUtil->checkGetKey('object')."</title>";  // 20081209 Here should come a better solution, see bug report 44
elseif($objUtil->checkRequestKey(('titleobjectaction')))
{ if($objUtil->checkRequestKey('searchObjectQuickPickQuickPick',''))
    echo "<title>DSL: ".LangQuickPickSearchObject." ".$objUtil->checkGetKey('object')."</title>";  // 20081209 Here should come a better solution, see bug report 44
  elseif($objUtil->checkRequestKey('searchObservationsQuickPick',''))
    echo "<title>DSL: ".LangQuickPickSearchObservations." ".$objUtil->checkGetKey('object')."</title>";  // 20081209 Here should come a better solution, see bug report 44
  elseif($objUtil->checkRequestKey('newObservationQuickPick',''))
    echo "<title>DSL: ".LangQuickPickNewObservation." ".$objUtil->checkGetKey('object')."</title>";  // 20081209 Here should come a better solution, see bug report 44
}
elseif(defined("LangTitle".$objUtil->checkGetKey('indexAction','')))
  echo "<title>DSL ". constant("LangTitle".$objUtil->checkGetKey('indexAction',''))." ".$objUtil->checkGetKey('object')."</title>";  // 20081209 Here should come a better solution, see bug report 44
else
  echo "<title>DSL ". $objUtil->checkGetKey('indexAction','')."</title>";  // 20081209 Here should come a better solution, see bug report 44
echo "<script type=\"text/javascript\" src=\"".$baseURL."lib/javascript/presentation.js\"></script>";
echo "<script type=\"text/javascript\">window.onresize=resizeForm;</script>";
echo "</head>";
?>
