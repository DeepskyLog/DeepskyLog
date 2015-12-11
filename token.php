<?php
// token.php
// handles password change requests.
$inIndex = true;
require_once 'common/entryexit/preludes.php'; // Includes of all classes and assistance files

global $inIndex;
if ((! isset ( $inIndex )) || (! $inIndex))
include "redirect.php";
else
token ();
function token() {
  global $instDir, $objMessages;

  if (strcmp($_GET[a], "cxlpw") == 0) {
    // Get the userid
    include_once $instDir . "lib/password.php";
    $password = new Password();

    $token = $_GET[t];
    $userid = $password->getUserId($token);

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
      //print "Clearing the password request";

      //1. Show:  Your request has been canceled.

    }
  } else if (strcmp($_GET[a], "cfmpw") == 0) {
    print "Changing the password";
  }
}
?>
