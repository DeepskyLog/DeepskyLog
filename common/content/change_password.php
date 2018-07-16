<?php

change_password ();
function change_password() {
  global $instDir, $entryMessage, $baseURL;

  include_once $instDir . "lib/password.php";
  $password = new Password();
  $token = $_GET['t'];

  // Move this to control, only setting the password to this file.

  // Only show the change password form when the token is known
  if ($password->tokenExists($token)) {
    $userid = $password->getUserId($token);

    // Check if the token is not too old. If the token is too old, remove the token.
    if ($password->isValid($token)) {
      // Add form to change the password.
      echo "<div id=\"main\">
              <form action=\"".$baseURL."index.php?indexAction=changepasswordToken\" method=\"post\">
              " . _("New password") . "
              <input type=\"hidden\" name=\"userid\" value=\"" . $userid . "\" />
              <input type=\"hidden\" name=\"token\" value=\"" . $token . "\" />
              <input type=\"password\" name=\"newPassword\" class=\"strength\" required>" .
              LangChangeAccountField6 . "
              <input type=\"password\" name=\"confirmPassword\" class=\"strength\" required data-show-meter=\"false\">
              <br />
              <input class=\"btn btn-danger\" type=\"submit\" name=\"changePasswordToken\" value=\"" . _("Change password") . "\" />";

      echo "</div>";
    } else {
      echo "<meta http-equiv=\"refresh\" content=\"0; url=/index.php\" />";
    }
  } else {
    echo "<meta http-equiv=\"refresh\" content=\"0; url=/index.php\" />";
  }
}
?>
