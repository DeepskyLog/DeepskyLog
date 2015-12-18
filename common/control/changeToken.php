<?php
// token.php
// handles password change requests.
token();

function token() {
  global $instDir, $objMessages, $entryMessage;

    // Get the userid
    include_once $instDir . "lib/password.php";
    $password = new Password();

    $token = $_GET['t'];

    if ($password->tokenExists($token)) {
      // Only go on when the token is not too old. If the token is too old, remove the token.
      if ($password->isValid($token)) {
        // Go to the correct 
        echo "<div id=\"main\">";
        // TODO: Add form to change the password.
        // TODO: Add scripts to change the password.
        print "TEST: " . $userid;
        echo "</div>";
      } else {
        // TODO: Change
        print "<br/>TOKEN IS NOT VALID ANYMORE!";
      }
    } else {
      // TODO: Change message
      $entryMessage = "TOKEN DOES NOT EXIST!";
      $_GET ['indexAction'] = 'main';

      // TODO: Return the index page
      return;
    }



    if (sizeof($userid) > 0) {
      // Clear the request
      $password->removeToken($token);

      // Send a mail that the request was canceled.
      if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
      } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
      } else {
        $ip = $_SERVER['REMOTE_ADDR'];
      }

      $subject = LangCancelRequestNewPasswordSubject;
      $message = LangCancelRequestNewPassword1 . $ip;
      $message .= LangCancelRequestNewPassword2;

      $objMessages->sendEmail($subject, $message, $userid);

      // Go to the DeepskyLog page and show 'Your password change request was canceled'
      $entryMessage = LangCancelRequestNewPasswordSubject . ".";
    }
}
?>
