<?php
// All global data parameters not in databaseInfo.php or some otherr files
// intensions are to collect them here.

if((!isset($inIndex))||(!$inIndex)) include "/redirect.php";


$DSOcatalogsLists = array();
$DSOcatalogs      = array();

$sort;                                                  // defines in data_get_objects.php
$showPartOfs="";


$theDate='';
$lastReadObservation='';

$myList=false;
$listname='';
$listname_ss='';

$leftmenu="show"; 
$topmenu="show"; 


$menuView="collapsed";
$menuAddChange="collapsed";
$menuAdmin="collapsed";
$menuLogin="expanded";
$menuSearch="expanded";
$menuDownloads="expanded";
$menuMoon="collapsed";


$entryMessage="";
$resizeElement="";
$resizeSize=0;
$loadAtlasPage=0;

$FF=false;
$MSIE=false;

$today=date('Ymd',strtotime('today'));
$thisYear=date("Y");
$thisMonth=date("n");
$thisDay=date("j");

$loginErrorText="";
$loginErrorCode="";

?>