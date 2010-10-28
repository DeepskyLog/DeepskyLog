<?php 
// objects.pdf
// print a pdf with the selected objects, in a shaded table
 
$inIndex = true;
require_once 'common/entryexit/preludes.php';

objects_pdf();

function objects_pdf()
{ global $objUtil,$filename;
  $filename=str_replace(' ','_',html_entity_decode($objUtil->checkRequestKey('pdfTitle','Deepskylog_Objects')));
  if(array_key_exists('SID', $_GET)&&$_GET['SID']&&array_key_exists($_GET['SID'],$_SESSION)&&$_SESSION[$_GET['SID']])
    $objUtil->pdfObjects($_SESSION[$_GET['SID']]);
}
?>
