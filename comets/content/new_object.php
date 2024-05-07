<?php

// new_object.php
// allows the user to add a comet to the database

global $inIndex,$loggedUser,$objUtil;
if((!isset($inIndex)) || (!$inIndex)) {
    include "../../redirect.php";
} elseif(!($loggedUser)) {
    throw new Exception(_("You need to be logged in as an administrator to execute these operations."));
} else {
    new_object();
}

/**
 * Creates a new object form and displays it on the page.
 *
 * @throws Exception if the user is not logged in as an administrator
 */
function new_object()
{
    global $baseURL,
    $objPresentations;
    echo "<div id=\"main\">";
    echo "<form action=\"".$baseURL."index.php?indexAction=comets_validate_object\" method=\"post\">";
    echo "<h4>"._("Add new object")."</h4>";
    echo "<input type=\"submit\" class=\"btn btn-success pull-right\" name=\"newobject\" value=\"" . _("Add object") . "\" />";
    echo "<br /><hr />";
    $content = "<input type=\"text\" required class=\"form-control\" name=\"name\" value=\"\" />";
    echo "<strong>" . _("Name")."&nbsp;*</strong>";
    echo $content;
    $content = "<input type=\"text\" required class=\"form-control\" name=\"icqname\" value=\"\" />";
    echo "<strong>" . _("ICQ name")."&nbsp;*</strong>";
    echo $content;
    echo "<br /><br /><input type=\"submit\" class=\"btn btn-success\" name=\"newobject\" value=\"" . _("Add object") . "\" />";
    echo "<hr />";
    echo "</form>";
    echo "</div>";
}
