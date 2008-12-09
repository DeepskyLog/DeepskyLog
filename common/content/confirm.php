<?php

// confirm.php
// displays a confirmation message after registration
// Version 0.2: 2004/09/25, JV

include_once "lib/setup/language.php";
include_once "lib/util.php";

$util = new Util();
$util->checkUserInput();

echo("<div id=\"main\">\n
      <h2>" . LangRegisterTitle . "</h2>\n<p>"

      . LangRegisterNotify .

     "</p></div>\n");

?>
