<?php

// change_eyepiece.php
// allows the administrator to change eyepiece details 
// version 3.2 : WDM, 16/01/2008

//include_once "../lib/observers.php";

include_once "../lib/eyepieces.php";
include_once "../lib/util.php";

$util = new Util();
$util->checkUserInput();

$eyepieces = new Eyepieces();

echo("<div id=\"main\">
   \n<h2>");

echo stripslashes($eyepieces->getEyepieceName($_GET['eyepiece']));

echo("</h2>

   <form action=\"common/control/validate_eyepiece.php\" method=\"post\">
   <table>
   <tr>
   <td class=\"fieldname\">");

echo(LangAddEyepieceField1);

echo("</td>
   <td><input type=\"text\" class=\"inputfield\" maxlength=\"64\" name=\"eyepiecename\" size=\"30\" value=\"");

echo stripslashes($eyepieces->getEyepieceName($_GET['eyepiece']));

echo("\" /></td>
   <td class=\"explanation\">" . LangAddEyepieceField1Expl . "</td>
   </tr>
   <tr>
   <td class=\"fieldname\">");

echo(LangAddEyepieceField2);

echo("</td>
   <td><input type=\"text\" class=\"inputfield\" maxlength=\"5\" name=\"focalLength\" size=\"5\" value=\"");

echo stripslashes($eyepieces->getFocalLength($_GET['eyepiece']));

echo("\" /></td>
   <td class=\"explanation\">");

echo(LangAddEyepieceField2Expl);

echo("</td>
   </tr>
   <tr>
   <td class=\"fieldname\">");

echo(LangAddEyepieceField4);

echo("</td>
   <td><input type=\"text\" class=\"inputfield\" maxlength=\"5\" name=\"maxFocalLength\" size=\"5\" value=\"");

$mfl = stripslashes($eyepieces->getMaxFocalLength($_GET['eyepiece']));
if ($mfl < 0) {
  $mfl = "";
}
echo $mfl;

echo("\" /></td>
   <td class=\"explanation\">");

echo(LangAddEyepieceField4Expl);

echo("</td>
   </tr>
   <tr>
   <td class=\"fieldname\">");

echo(LangAddEyepieceField3);

echo("</td>
   <td><input type=\"text\" class=\"inputfield\" maxlength=\"5\" name=\"apparentFOV\" size=\"5\" value=\"");

echo $eyepieces->getApparentFOV($_GET['eyepiece']);

echo("\" /></td>
   <td class=\"explanation\">");

echo(LangAddEyepieceField3Expl);

echo("</td>
   </tr>");


echo("<tr>
   <td></td>
   <td><input type=\"submit\" name=\"change\" value=\"");

echo (LangAddEyepieceButton2);

echo("\" /><input type=\"hidden\" name=\"id\" value=\"");

echo ($_GET['eyepiece']);

echo("\"></input></td>
   <td></td>
   </tr>
   </table>
   </form>");

echo("</div>
</div>
</body>
</html>");

?>
