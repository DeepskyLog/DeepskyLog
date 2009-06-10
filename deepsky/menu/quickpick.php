<?php // quickpick.php - allows the user to quiclky enter the name of an object and search it, its observations or make a new observation
echo "<div   class=\"menuDiv\">";
echo "<form  action=\"".$baseURL."index.php\" method=\"get\">";
echo "<div>";
echo "<p class=\"menuHead\">".LangSearch."</p>";
echo "<input type=\"hidden\" name=\"indexAction\" value=\"quickpick\" />";
echo "<input type=\"hidden\" name=\"source\"      value=\"quickpick\" />";
echo "<input type=\"hidden\" name=\"myLanguages\" value=\"true\" />";
echo "<input type=\"text\"
             name=\"object\"
             class=\"inputfield menuInput\"
             title=\"".LangQuickPickHelp."\"
             value=\"".((array_key_exists('object',$_GET)&&($_GET['object']!='* '))?$_GET['object']:"")."\" 
             />";
echo "<input type=\"submit\"
             name=\"searchObjectQuickPickQuickPick\" 
             class=\"menuButton\"                     
             value=\"".LangQuickPickSearchObject."\"  
             />";
echo "<input type=\"submit\" 
             name=\"searchObservationsQuickPick\"    
             class=\"menuButton\"                     
             value=\"".LangQuickPickSearchObservations."\" 
             />";
if($loggedUser)	
echo "<input type=\"submit\" 
             name=\"newObservationQuickPick\" 
             class=\"menuButton\"                     
             value=\"".LangQuickPickNewObservation."\" 
             />";
echo "</div>";
echo "</form>";
echo "</div>";
?>