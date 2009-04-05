<?php
echo "<table cellpadding=\"0\" cellspacing=\"0\" class=\"moduletable\">";
echo "<tr>"."</tr>";
echo "<form action=\"".$baseURL."index.php\" method=\"get\">";
echo "<input type=\"hidden\" name=\"indexAction\" value=\"quickpick\"></input>";
echo "<input type=\"hidden\" name=\"source\"      value=\"quickpick\"></input>";
echo "<input type=\"hidden\" name=\"myLanguages\" value=\"true\"></input>";
echo "<tr>"."<td>"."</td>"."</tr>";
echo "<tr>"."<td>"."<input type=\"text\"   
                           name=\"object\"                        
                           class=\"inputfield\"
                           style=\"width:145px\"
                           title=\"".LangQuickPickHelp."\" 
                           value=\"".((array_key_exists('object',$_GET)&&($_GET['object']!='* '))?$_GET['object']:"")."\" >"."</td>"."</tr>";
echo "<tr>"."<td>"."<input type=\"submit\" name=\"searchObjectQuickPickQuickPick\" 
                           class=\"menuButton\"                     
                           value=\"".LangQuickPickSearchObject."\"  
                           accesskey=\"O\">"."</td>"."</tr>";
echo "<tr>"."<td>"."<input type=\"submit\" 
                           name=\"searchObservationsQuickPick\"    
                           class=\"menuButton\"                     
                           value=\"".LangQuickPickSearchObservations."\" 
                           accesskey=\"v\">"."</td>"."</tr>";
if($loggedUser)	
echo "<tr>"."<td>"."<input type=\"submit\" 
                           name=\"newObservationQuickPick\" 
                           class=\"menuButton\"                     
                           value=\"".LangQuickPickNewObservation."\" 
                           accesskey=\"N\">"."</td>"."</tr>";
echo "</form>";
echo "</table>";
?>