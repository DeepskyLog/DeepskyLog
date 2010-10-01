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
echo "<script type=\"text/javascript\" src=\"".$baseURL."lib/javascript/phpjs.js\"></script>";
  
$DSLTitle="DeepskyLog";
$TitleText="";
$theDispatch=$objUtil->checkRequestKey('indexAction');
$theObject=$objUtil->checkRequestKey('object');
$theObject=($theObject?" - ".$theObject:"");
if($includeFile=='deepsky/content/new_observationcsv.php')           $TitleText=LangCSVTitle;
elseif($includeFile=='deepsky/content/new_observationxml.php')       $TitleText=LangXMLTitle;
elseif($includeFile=='deepsky/content/new_object.php')               $TitleText=LangNewObjectTitle;
elseif($includeFile=='deepsky/content/new_observation.php')          $TitleText=LangNewObservationTitle.$theObject;
elseif($includeFile=='deepsky/content/view_object.php')              $TitleText=LangViewObjectTitle.$theObject;
elseif($includeFile=='deepsky/content/view_observation.php')         $TitleText=LangViewObservationTitle.$theObject;
elseif($includeFile=='deepsky/content/dsatlas.php')                  $TitleText=LangAtlasPage.$theObject;
elseif($includeFile=='deepsky/content/new_listdatacsv.php')          $TitleText=LangCSVListTitle;
elseif($includeFile=='deepsky/content/tolist.php')                   $TitleText=LangList." - ".$listname;
elseif($includeFile=='deepsky/content/manage_objects_csv.php')       $TitleText=LangCSVObjectTitle;
elseif($includeFile=='deepsky/content/setup_objects_query.php')      $TitleText=LangQueryObjectsTitle;
elseif($includeFile=='deepsky/content/view_object.php')              $TitleText=LangSelectedObjectsTitle.$theObject;
elseif($includeFile=='deepsky/content/setup_observations_query.php') $TitleText=LangQueryObservationsTitle;
elseif($includeFile=='deepsky/content/top_objects.php')              $TitleText=LangTopObjectsTitle;
elseif($includeFile=='deepsky/content/top_observers.php')            $TitleText=LangTopObserversTitle;
elseif($includeFile=='deepsky/content/selected_objects.php')         $TitleText=LangSelectedObjectsTitle;
elseif($includeFile=='deepsky/content/selected_observations.php')   
{ if (array_key_exists('minyear', $_GET) && ($_GET['minyear'] == substr($theDate, 0, 4)) && array_key_exists('minmonth', $_GET) && ($_GET['minmonth'] == substr($theDate, 4, 2)) && array_key_exists('minday', $_GET) && ($_GET['minday'] == substr($theDate, 6, 2)))
    $TitleText=LangSelectedObservationsTitle3;
  elseif ($object) 
    $TitleText=LangSelectedObservationsTitle . $object;
  else
    $TitleText=LangSelectedObservationsTitle2;
}
elseif($includeFile=='deepsky/content/details_observer_catalog.php') $TitleText=LangTopObserversMessierHeader2." ".$objUtil->checkGetKey('catalog','M')." ".LangTopObserversMessierHeader3." - ".$objObserver->getObserverProperty($objUtil->checkGetKey('user'),'firstname')." ".$objObserver->getObserverProperty($objUtil->checkGetKey('user'),'name');
elseif($theDispatch=='detail_observer')                              $TitleText=LangDetailObserver;
elseif($includeFile=='common/content/change_account.php')            $TitleText=LangChangeAccountTitle;
elseif($theDispatch=='detail_eyepiece')                              $TitleText=LangDetailEyepiece;
elseif($includeFile=='common/content/change_eyepiece.php')           $TitleText=LangAddEyepieceButton2;
elseif($theDispatch=='detail_filter')                                $TitleText=LangDetailFilter;
elseif($includeFile=='common/content/change_filter.php')             $TitleText=LangChangeFilterButton;
elseif($theDispatch=='detail_instrument')                            $TitleText=LangDetailInstrument;   
elseif($includeFile=='common/content/change_instrument.php')         $TitleText=LangChangeInstrumentButton;
elseif($theDispatch=='detail_lens')                                  $TitleText=LangDetailLens; 
elseif($includeFile=='common/content/change_lens.php')               $TitleText=LangChangeLensButton;
elseif($theDispatch=='detail_location')                              $TitleText=LangDetailSite;  
elseif($includeFile=='common/content/change_site.php')               $TitleText=LangAddSiteButton2;
elseif($includeFile=='common/content/new_eyepiece.php')              $TitleText=LangAddEyepieceButton;
elseif($includeFile=='common/content/new_filter.php')                $TitleText=LangAddFilterButton;   
elseif($includeFile=='common/content/new_instrument.php')            $TitleText=LangAddInstrumentAdd;
elseif($includeFile=='common/content/new_lens.php')                  $TitleText=LangAddLensButton;
elseif($includeFile=='common/content/new_site.php')                  $TitleText=LangAddSiteButton;    
elseif($includeFile=='common/content/message.php')                   $TitleText="";
elseif($includeFile=='common/content/search_locations.php')          $TitleText=LangSearchLocations0;
elseif($includeFile=='common/content/getLocation.php')               $TitleText=LangGetLocation1;
elseif($includeFile=='common/content/register.php')                  $TitleText=LangRegisterNewTitle;
elseif($includeFile=='common/content/overview_eyepieces.php')        $TitleText=LangViewEyepieceTitle;
elseif($includeFile=='common/content/overview_filters.php')          $TitleText=LangOverviewFilterTitle;
elseif($includeFile=='common/content/overview_instruments.php')      $TitleText=LangOverviewInstrumentsTitle;
elseif($includeFile=='common/content/overview_lenses.php')           $TitleText=LangOverviewLensTitle;
elseif($includeFile=='common/content/overview_locations.php')        $TitleText=LangViewLocationTitle;
elseif($includeFile=='common/content/overview_observers.php')        $TitleText=LangViewObserverTitle;

elseif($includeFile=='deepsky/control/admincheckobjects.php')        $TitleText="Checking objects";
    
elseif($includeFile=='comets/content/overview_observations.php')     $TitleText=LangOverviewObservationsTitle;
elseif($includeFile=='comets/content/view_object.php')               $TitleText=LangViewObjectTitle;
elseif($includeFile=='comets/content/view_observation.php')          $TitleText=LangViewObservationTitle;
elseif($includeFile=='comets/content/new_observation.php')           $TitleText=LangNewObservationTitle;
elseif($includeFile=='comets/content/selected_observations.php')     $TitleText=LangSelectedObservationsTitle;
elseif($includeFile=='comets/content/view_observation.php')          $TitleText=LangViewObservationTitle;
elseif($includeFile=='comets/content/new_object.php')                $TitleText=LangNewObjectTitle;
elseif($includeFile=='comets/content/view_object.php')               $TitleText=LangViewObjectTitle;
elseif($includeFile=='comets/content/overview_objects.php')          $TitleText=LangOverviewObjectsTitle;
elseif($includeFile=='comets/content/overview_observations.php')     $TitleText=LangOverviewObservationsTitle;
elseif($includeFile=='comets/content/execute_query_objects.php')     $TitleText=LangSelectedObjectsTitle;
elseif($includeFile=='comets/content/selected_observations2.php')    $TitleText=LangSelectedObservationsTitle2;
elseif($includeFile=='comets/content/top_observers.php')             $TitleText=LangTopObserversTitle;
elseif($includeFile=='comets/content/top_objects.php')               $TitleText=LangTopObjectsTitle;
elseif($includeFile=='comets/content/setup_observations_query.php')  $TitleText=LangQueryObservationsTitle;
elseif($includeFile=='comets/content/setup_objects_query.php')       $TitleText=LangQueryObjectsTitle;
elseif($objUtil->checkRequestKey('title'))
  echo "<title>DSL: ".$objUtil->checkRequestKey('title','')."</title>";  // 20081209 Here should come a better solution, see bug report 44
elseif($objUtil->checkRequestKey(('titleobject')))
  echo "<title>DSL: ".$objUtil->checkRequestKey('titleobject','')." - ".$objUtil->checkGetKey('object')."</title>";  // 20081209 Here should come a better solution, see bug report 44
elseif($objUtil->checkRequestKey(('titleobjectaction')))
{ if($objUtil->checkRequestKey('searchObjectQuickPickQuickPick',''))
    echo "<title>DSL: ".LangSelectedObjectsTitle." - ".$objUtil->checkGetKey('object')."</title>";  // 20081209 Here should come a better solution, see bug report 44
  elseif($objUtil->checkRequestKey('searchObservationsQuickPick',''))
    echo "<title>DSL: ".LangSelectedObservationsTitle2." - ".$objUtil->checkGetKey('object')."</title>";  // 20081209 Here should come a better solution, see bug report 44
  elseif($objUtil->checkRequestKey('newObservationQuickPick',''))
    echo "<title>DSL: ".LangQuickPickNewObservation." - ".$objUtil->checkGetKey('object')."</title>";  // 20081209 Here should come a better solution, see bug report 44
}
echo "<title>".$DSLTitle.($TitleText?": ".$TitleText:"")."</title>";
echo "<script type=\"text/javascript\" src=\"".$baseURL."lib/javascript/presentation.js\"></script>";
echo "<script type=\"text/javascript\">window.onresize=resizeForm('".$leftmenu."','".$topmenu."');</script>";
echo "</head>";
?>
