<?php

// view_eyepiece.php
// view information of an eyepiece 

session_start(); // start session

include_once "../lib/eyepieces.php"; // location table
include_once "../lib/util.php";
include_once "../lib/setup/language.php";

$util = new Util();
$util->checkUserInput();

$eyepieces = new Eyepieces; 

if(!$_GET['eyepiece']) // no instrument defined 
{
   header("Location: ../index.php");
}

$name = $eyepieces->getEyepieceName($_GET['eyepiece']);

echo("<div id=\"main\">\n<h2>" . $name . "</h2><table width=\"490\">\n
<tr>\n
<td class=\"fieldname\">\n");

echo LangViewEyepieceName;

echo("</td>\n<td>\n");
echo($name);
echo("</td></tr>");

echo("<tr><td class=\"fieldname\">");

$maxFocalLength = $eyepieces->getMaxFocalLength($_GET['eyepiece']);

echo("<tr><td class=\"fieldname\">");

echo LangViewEyepieceFocalLength; 

echo("</td><td>");

$focalLength = $eyepieces->getFocalLength($_GET['eyepiece']);

echo($focalLength);

print("</td></tr>");

if ($maxFocalLength > 0) {
	echo("<tr><td class=\"fieldname\">");

	echo LangAddEyepieceField4; 

	echo("</td><td>");

  echo($maxFocalLength);

  print("</td></tr>");
}
$fov = $eyepieces->getApparentFOV($_GET['eyepiece']);

print("<tr><td class=\"fieldname\">");

echo LangAddEyepieceField3;

echo("</td><td>");

echo($fov);

print("</td>
   </tr>");

echo ("</table>");

print("</div></div></body></html>");

?>
