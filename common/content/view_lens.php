<?php // view_lens.php - view information of a lens 
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!($lensid=$objUtil->checkGetKey('lens'))) throw new Exception(LangException009b);
else
{
$name=$objLens->getLensPropertyFromId($lensid,'name');
echo "<div id=\"main\">";
$objPresentations->line(array("<h4>".$name."</h4>"),"L",array(100),30);
echo "<hr />";
$objPresentations->line(array(LangViewLensFactor,$objLens->getLensPropertyFromId($lensid,'factor')),"RL",array(20,80),'',array('fieldname','fieldvalue'));
echo "<hr />";
echo "</div>";
}
?>
