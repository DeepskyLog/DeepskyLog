<?php

// view_lens.php
// view information of a lens 

session_start(); // start session

include_once "../lib/lenses.php"; // location table
include_once "../lib/util.php";
include_once "../lib/setup/language.php";

$util = new Util();
$util->checkUserInput();

$lenses = new Lenses; 

if(!$_GET['lens']) // no lens defined 
{
   header("Location: ../index.php");
}

$name = $lenses->getName($_GET['lens']);
$factor = $lenses->getFactor($_GET['lens']);

echo("<div id=\"main\">\n<h2>" . $name . "</h2><table width=\"490\">\n
<tr>\n
<td class=\"fieldname\">\n");

echo LangViewLensName;

echo("</td>\n<td>\n");
echo($name);
echo("</td></tr>");

echo("<tr><td class=\"fieldname\">");


echo("<tr><td class=\"fieldname\">");

echo LangViewLensFactor; 

echo("</td><td>");

echo $factor;

print("</td>
         </tr>");

echo ("</table>");

print("</div></div></body></html>");

?>
