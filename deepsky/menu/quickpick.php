<?php // quickpick.php - allows the user to quiclky enter the name of an object and search it, its observations or make a new observation
echo "<div   class=\"menuDiv\">";
echo "<form  action=\"".$baseURL."index.php\" method=\"get\">";
echo "<div>";
reset($_GET);
$link="";
while(list($key,$value)=each($_GET))
  if($key!="menuSearch")
    $link.="&amp;".$key."=".urlencode($value);
reset($_GET);
echo "<p  class=\"menuHead\">";
if($menuSearch=="collapsed")
  echo "<a href=\"".$baseURL."index.php?menuSearch=expanded".$link."\" title=\"".LangMenuExpand."\">+</a> ";
else
  echo "<a href=\"".$baseURL."index.php?menuSearch=collapsed".$link."\" title=\"".LangMenuCollapse."\">-</a> ";
echo LangSearch."</p>";
echo "<input type=\"hidden\" name=\"indexAction\" value=\"quickpick\" />";
echo "<input type=\"hidden\" name=\"titleobjectaction\" value=\"".LangSearch."\" />";
echo "<input type=\"hidden\" name=\"source\"      value=\"quickpick\" />";
echo "<input type=\"hidden\" name=\"myLanguages\" value=\"true\" />";
echo "<input type=\"text\" id=\"quickpickobject\" name=\"object\" class=\"inputfield menuInput\" title=\"".LangQuickPickHelp."\" value=\"".((array_key_exists('object',$_GET)&&($_GET['object']!='* '))?$_GET['object']:"")."\" />";
if($menuSearch=="expanded")
{	echo "<input type=\"submit\" name=\"searchObjectQuickPickQuickPick\" class=\"menuButton\" value=\"".LangQuickPickSearchObject."\" />";
	echo "<input type=\"submit\" name=\"searchObservationsQuickPick\" class=\"menuButton\" value=\"".LangQuickPickSearchObservations."\" />";
	if($loggedUser)	
  	echo "<input type=\"submit\" name=\"newObservationQuickPick\" class=\"menuButton\" value=\"".LangQuickPickNewObservation."\" />";
}
echo "</div>";
echo "</form>";
echo "</div>";
?>