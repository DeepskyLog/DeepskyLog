<?php
// atlasPagesIndex.pdf
// print the atlas pages index

$inIndex = true;
require_once 'common/entryexit/preludes.php';
require_once "lib/setup/vars.php";

atlasPagesIndex();

function atlasPagesIndex()
{
	global $filename,
	$objUtil,$objPrintAtlas;
	$filename="PageIndex.pdf";
	
	header("Content-type: application/pdf");
  header("Content-Disposition: attachment; filename=pageAtlasIndex.pdf");
  $objPrintAtlas->pdfAtlasIndex();
  unset($_SESSION['atlasPagesIndex']);
}
?>