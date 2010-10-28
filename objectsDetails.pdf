<?php
// objectDetails.pdf
// print a pdf with the selected object in detail

$inIndex = true;
require_once 'common/entryexit/preludes.php';

objectsDetails();

function objectsDetails()
{ global $objUtil,$filename;
  $filename=str_replace(' ','_',html_entity_decode($objUtil->checkRequestKey('pdfTitle','Deepskylog_Objects_Details')));
  if(array_key_exists('SID', $_GET)&&$_GET['SID']&&array_key_exists($_GET['SID'],$_SESSION)&&$_SESSION[$_GET['SID']])
    $objUtil->pdfObjectsDetails($_SESSION[$_GET['SID']], $objUtil->checkGetKey('sort',''));
}
?>
