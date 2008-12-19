<?php
// HERE HAS TO COME THE DATA GETTING PART

if($objUtil->checkGetKey('indexAction')=='rank_objects')
{ $_GET['source']='top_objects';
  require_once 'deepsky/data/data_get_objects.php';
}
if($objUtil->checkGetKey('indexAction')=='result_selected_observations')
  require_once 'deepsky/data/data_get_observations.php';

?>