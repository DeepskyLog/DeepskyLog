<?php


if($includeFile=='deepsky/content/top_objects.php')
{ $_GET['source']='top_objects';
  require_once 'deepsky/data/data_get_objects.php';
}
if($includeFile=='deepsky/content/selected_observations2.php')
  require_once 'deepsky/data/data_get_observations.php';

?>