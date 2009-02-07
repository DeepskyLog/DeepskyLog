<?php
echo "<table cellpadding=\"0\" cellspacing=\"0\" class=\"moduletable\">";
echo "<tr>"."</tr>";
echo "<form action=\"".$baseURL."index.php\" method=\"get\">";
echo "<input type=\"hidden\" name=\"indexAction\" value=\"quickpick\"></input>";
echo "<input type=\"hidden\" name=\"source\"      value=\"quickpick\"></input>";
echo "<input type=\"hidden\" name=\"myLanguages\" value=\"true\"></input>";
echo "<tr>"."<td>"."</td>"."</tr>";
echo "<tr>"."<td>"."<input type=\"text\" class=\"menuBotton\" class=\"inputfield\" maxlength=\"255\" name=\"object\" title=\"".LangQuickPickHelp."\" value=\"".((array_key_exists('object',$_GET)&&($_GET['object']!='* '))?$_GET['object']:"")."\" >"."</td>"."</tr>";
echo "<tr>"."<td>"."<input type=\"submit\" name=\"searchObjectQuickPickQuickPick\" value=\"".LangQuickPickSearchObject."\" class=\"menuButton\" accesskey=\"O\">"."</td>"."</tr>";
echo "<tr>"."<td>"."<input type=\"submit\" name=\"searchObservationsQuickPick\" value=\"".LangQuickPickSearchObservations."\" class=\"menuButton\" accesskey=\"v\">"."</td>"."</tr>";
if($loggedUser)	echo "<tr>"."<td>"."<input type=\"submit\" name=\"newObservationQuickPick\" value=\"".LangQuickPickNewObservation."\" class=\"menuButton\"  accesskey=\"N\">"."</td>"."</tr>";
echo "</form>";
echo "</table>";
?>