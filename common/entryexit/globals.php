<?php
// All global data parameters not in databaseInfo.php or some otherr files
// intensions are to collect them here.

if((!isset($inIndex))||(!$inIndex)) include "/redirect.php";

$DSOcatalogsLists = $objObject->getCatalogsAndLists();
$DSOcatalogs      = $objObject->getCatalogs();

$sort;                                                  // defines in data_get_objects.php
$showPartOfs="";


$theDate='';
$lastReadObservation='';

$myList=False;
$listname='';
$listname_ss='';

$menuView="collapsed";
$menuAddChange="collapsed";
$menuAdmin="collapsed";
$menuLogin="expanded";
$menuSearch="expanded";
$menuMoon="collapsed";

?>