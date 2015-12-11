<?php
// token.php
// handles password change requests.
token();

function token() {
  global $instDir, $objMessages, $entryMessage;

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
      $entryMessage = LangCancelRequestNewPasswordSubject . ".";
    }
}
?>
