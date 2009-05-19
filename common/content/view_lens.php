<?php // view_lens.php - view information of a lens 
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!($lensid=$objUtil->checkGetKey('lens'))) throw new Exception(LangException009b);
else
{
$name=$objLens->getLensPropertyFromId($lensid,'name');
echo "<div id=\"main\">";
$objPresentations->line(array("<h5>".$name."</h5>"),"L",array(100),50);
echo "<hr />";
$objPresentations->line(array(LangViewLensFactor,$objLens->getLensPropertyFromId($lensid,'factor')),"RL",array(20,80),'',array('fieldname','fieldvalue'));
echo "<hr />";
echo "</div>";
}
?>
