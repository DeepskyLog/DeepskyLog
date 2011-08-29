<?php
// atlasPagesOnePass
// generate the atlas pages in one pass

$inIndex = true;
require_once 'common/entryexit/preludes.php';

header("Content-type: application/pdf");
header("Content-Length: ".strlen($_SESSION['allonepass'.$_GET['item']]));
header("Content-Disposition: attachment; filename=".str_replace(' ','_','Page '.$_GET['filename']).".pdf");

atlasPagesOnePass_pdf();

function atlasPagesOnePass_pdf()
{ echo $_SESSION['allonepass'.$_GET['item']];
  $_SESSION['allonepass'.$_GET['item']]="";
  unset($_SESSION['allonepass'.$_GET['item']]);
}
?>