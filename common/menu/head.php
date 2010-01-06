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

$DSLTitle="DeepskyLog";
$TitleText="";
$theObject=$objUtil->checkRequestKey('object');
$dispatch=$objUtil->checkRequestKey('indexAction');
if($dispatch=='add_csv')                $TitleText=LangCSVTitle;
elseif($dispatch=='add_xml')            $TitleText=LangXMLTitle;
elseif($dispatch=='add_object')         $TitleText=LangNewObjectTitle;
elseif($dispatch=='add_observation')    $TitleText=LangNewObservationTitle;
elseif($dispatch=='detail_object')      $TitleText=LangViewObjectTitle.($theObject?" - ".$theObject:'');
elseif($dispatch=='detail_observation') $TitleText=LangViewObservationTitle.($theObject?" - ".$theObject:'');
elseif($dispatch=='atlaspage')          $TitleText=LangAtlasPage;
elseif($dispatch=='import_csv_list')    $TitleText=LangCSVListTitle;
elseif($dispatch=='listaction')         $TitleText=LangList." ".$listname;
elseif($dispatch=='manage_csv_object')  $TitleText=LangCSVObjectTitle;
//elseif($dispatch=='query_objects')      $TitleText=LangQueryObjectsTitle;
elseif($includeFile=="deepsky/content/view_object.php") $TitleText=LangSelectedObjectsTitle;
/*              ,'deepsky/content/setup_objects_query.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll   ('query_observations'                 ,'deepsky/content/setup_observations_query.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll   ('rank_objects'                       ,'deepsky/content/top_objects.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll   ('rank_observers'                     ,'deepsky/content/top_observers.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll   ('result_query_objects'               ,'deepsky/content/execute_query_objects.php'))) 
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll   ('result_selected_observations'       ,'deepsky/content/selected_observations2.php')))  
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll   ('view_image'                         ,'deepsky/content/show_image.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll   ('view_observer_catalog'              ,'deepsky/content/details_observer_catalog.php')))
    
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('change_account'                     ,'common/content/change_account.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('adapt_eyepiece'                     ,'common/content/change_eyepiece.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('adapt_filter'                       ,'common/content/change_filter.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('adapt_instrument'                   ,'common/content/change_instrument.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('adapt_lens'                         ,'common/content/change_lens.php')))   
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('adapt_site'                         ,'common/content/change_site.php')))   
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('add_eyepiece'                       ,'common/content/new_eyepiece.php')))     
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('add_filter'                         ,'common/content/new_filter.php')))    
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('add_instrument'                     ,'common/content/new_instrument.php')))    
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('add_lens'                           ,'common/content/new_lens.php')))    
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('add_site'                           ,'common/content/new_site.php')))    
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll   ('detail_eyepiece'                    ,'common/content/change_eyepiece.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll   ('detail_filter'                      ,'common/content/change_filter.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll   ('detail_instrument'                  ,'common/content/change_instrument.php')))   
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll   ('detail_lens'                        ,'common/content/change_lens.php')))   
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll   ('detail_location'                    ,'common/content/change_site.php')))   
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll   ('detail_observer'                    ,'common/content/view_observer.php')))   
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll   ('message'                            ,'common/content/message.php')))   
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('search_sites'                       ,'common/content/search_locations.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('site_result'                        ,'common/content/getLocation.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll   ('subscribe'                          ,'common/content/register.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('validate_lens'                      ,'common/control/validate_lens.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('view_eyepieces'                     ,'common/content/overview_eyepieces.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('view_filters'                       ,'common/content/overview_filters.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('view_instruments'                   ,'common/content/overview_instruments.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('view_lenses'                        ,'common/content/overview_lenses.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('view_locations'                     ,'common/content/overview_locations.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('view_observers'                     ,'common/content/overview_observers.php')))

    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('admin_check_objects'                ,'deepsky/control/admincheckobjects.php')))
    
    
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll   ('comets_all_observations'            ,'comets/content/overview_observations.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll   ('comets_detail_object'               ,'comets/content/view_object.php'))) 
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll   ('comets_detail_observation'          ,'comets/content/view_observation.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('comets_adapt_observation'           ,'comets/content/new_observation.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('comets_add_observation'             ,'comets/content/new_observation.php')))   
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll   ('comets_result_query_observations'   ,'comets/content/selected_observations.php')))   
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll   ('comets_detail_observation'          ,'comets/content/view_observation.php')))   
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('comets_add_object'                  ,'comets/content/new_object.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll   ('comets_detail_object'               ,'comets/content/view_object.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll   ('comets_view_objects'                ,'comets/content/overview_objects.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll   ('comets_all_observations'            ,'comets/content/overview_observations.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll   ('comets_result_query_objects'        ,'comets/content/execute_query_objects.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll   ('comets_result_selected_observations','comets/content/selected_observations2.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll   ('comets_rank_observers'              ,'comets/content/top_observers.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll   ('comets_rank_objects'                ,'comets/content/top_objects.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll   ('comets_query_observations'          ,'comets/content/setup_observations_query.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionall   ('comets_query_objects'               ,'comets/content/setup_objects_query.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionDSquickPick()))
      $indexActionInclude=$this->utilitiesGetIndexActionDefaultAction();



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
elseif(defined("LangTitle".$objUtil->checkGetKey('indexAction','')))
  echo "<title>DSL ". constant("LangTitle".$objUtil->checkGetKey('indexAction',''))." ".$objUtil->checkGetKey('object')."</title>";  // 20081209 Here should come a better solution, see bug report 44
else
  echo "<title>DSL ". $objUtil->checkGetKey('indexAction','')."</title>";  // 20081209 Here should come a better solution, see bug report 44
*/
echo "<title>".$DSLTitle.($TitleText?": ".$TitleText:"")."</title>";
echo "<script type=\"text/javascript\" src=\"".$baseURL."lib/javascript/presentation.js\"></script>";
echo "<script type=\"text/javascript\">window.onresize=resizeForm;</script>";
echo "</head>";
?>
