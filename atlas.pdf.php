<?php 
// atlas.pdf
// print one atlas page

$inIndex = true;
require_once 'common/entryexit/preludes.php';
require_once "lib/setup/vars.php";

atlas();

function atlas()
{ global $filename,
         $objUtil,$objPrintAtlas;
	if($objUtil->checkRequestKey('filename'))
	  $filename=$objUtil->checkRequestKey('filename');
	elseif(($zoom=$objUtil->checkRequestKey('zoom'))==ATLASOVERVIEWZOOM)
	  $filename=_('Overview');
	elseif($zoom==ATLASLOOKUPZOOM)
	  $filename=_('Lookup');
	elseif($zoom==ATLASDETAILZOOM)
	  $filename=_('Detail');
	else
	  $filename="Zoom_".$zoom;
	$filename.="_".str_replace(' ','_',$objUtil->checkRequestKey('object','Deepskylog_Atlas'));
	
	/*
	header ("Content-Type: application/pdf");
	header ("Content-Disposition: attachment; filename=".$filename.".pdf");
	
	echo "<head>";
	echo "<title>".$objUtil->checkRequestKey('object','Deepskylog Atlas page')."</title>";
	echo "</head>";
	*/
	
	$objPrintAtlas->pdfAtlas();
}
?>
