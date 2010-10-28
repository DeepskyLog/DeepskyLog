<?php 
// objectnames.pdf
// print a pdf file with the selected objects names

$inIndex = true;
require_once 'common/entryexit/preludes.php';

objectnames();

function objectnames()
{ global $filename,
         $objUtil;
  $filename=str_replace(' ','_',html_entity_decode($objUtil->checkRequestKey('pdfTitle','Deepskylog_Objects_Names')));
  if(array_key_exists('SID', $_GET)&&$_GET['SID']&&array_key_exists($_GET['SID'],$_SESSION)&&$_SESSION[$_GET['SID']])
    $objUtil->pdfObjectnames($_SESSION[$_GET['SID']]);
}
?>