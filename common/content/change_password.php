<?php

change_password ();
function change_password() {
  global $instDir, $entryMessage;

  include_once $instDir . "lib/password.php";
  $password = new Password();
  $token = $_GET['t'];

  // Move this to control, only setting the password to this file.

  // Only show the change password form when the token is known
  if ($password->tokenExists($token)) {
    $userid = $password->getUserId($token);

    // TODO: Only go on when the token is not too old. If the token is too old, remove the token.
    if ($password->isValid($token)) {
      echo "<div id=\"main\">";
      // TODO: Add form to change the password.
      // TODO: Add scripts to change the password.
      print "TEST: " . $userid;
      echo "</div>";

      exit;

    } else {
      echo "<meta http-equiv=\"refresh\" content=\"0; url=/index.php\" />";
    }
  } else {
    echo "<meta http-equiv=\"refresh\" content=\"0; url=/index.php\" />";
  }
  // TODO: MAKE SURE THAT THE TOKEN IS REMOVED IF TIME > 1 DAY
}
?>
