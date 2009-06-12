<?php // view_filter.php - view information of a filter 
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!($filterid=$objUtil->checkGetKey('filter'))) throw new Exception(LangException005b);
//elseif(!($objFilter->getFilterPropertyFromId($filterid,'name')))  throw new Exception("Filter not found in change_filter.php, please contact the developers with this message:".$filterid);
else
{
$filter=$objFilter->getFilterPropertiesFromId($filterid);
echo "<div id=\"main\">";
$objPresentations->line(array("<h4>".$filter['name']."</h4>"),"L",array(100),30);
echo "<hr />";
$objPresentations->line(array(LangViewFilterName,$filter['name']),"RL",array(20,80),'',array('fieldname','fieldvalue'));
$objPresentations->line(array(LangViewFilterType,$objFilter->getEchoType($filter['type'])),"RL",array(20,80),'',array('fieldname','fieldvalue')); 
$objPresentations->line(array(LangViewFilterColor,$objFilter->getEchoColor($filter['color'])),"RL",array(20,80),'',array('fieldname','fieldvalue'));
if($filter['wratten'])
  $objPresentations->line(array(LangViewFilterWratten,$filter['wratten']),"RL",array(20,80),'',array('fieldname','fieldvalue'));
if($filter['schott'])
  $objPresentations->line(array(LangViewFilterSchott,$filter['schott']),"RL",array(20,80),'',array('fieldname','fieldvalue'));
echo "<hr />";
echo "</div>";
}
?>
