<?php
// password.php
// The password class collects all functions needed to request a new password.
global $inIndex;
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
class Password {
  // Stores the token in the database.
  public function storeToken($userid, $token) {
    global $objDatabase;

    // We first check if there is already a token in the database for the userid.
    // If this is the case, we remove the token.
    $this->removeChangeRequest($userid);

    $sql = "INSERT INTO password_change_requests (token, userid) VALUES(\"" . $token . "\", \"" . $userid . "\")";

    $objDatabase->execSQL ( $sql );
    // Test
//     $db1 = $objDatabase->selectSingleArray ( "select token from password_change_requests", "token" );
//     print_r($db1);
// print "<br />";
//     $db2 = $objDatabase->selectSingleArray ( "select userid from password_change_requests", "userid" );
//     print_r($db2);
// print "<br />";
//   $db3 = $objDatabase->selectSingleArray ( "select time from password_change_requests", "time" );
//   print_r($db3);
//   print "<br />";
//     exit;
  }

  // Removes the change request. This can happen in three occasions:
  // - When adding a new change request for the given user.
  // - When there is a Password Change Request, but the observer does log in successfully.
  // - When the time for the password change request has passed: TODO
  public function removeChangeRequest($userid) {
    global $objDatabase;

    $objDatabase->execSQL ( "DELETE from password_change_requests WHERE userid=\"" . $userid . "\"" );
  }

  // Removes the change request from a token.
  // This happens when the cancel change request link is clicked.
  public function removeToken($token) {
    global $objDatabase;

    $objDatabase->execSQL ( "DELETE from password_change_requests WHERE token=\"" . $token . "\"" );
  }

  public function getUserId($token) {
    global $objDatabase;

    $userid = $objDatabase->selectSingleArray ( "select userid from password_change_requests where token=\"" . $token . "\"", "userid" );

    return $userid[0];
  }

  // TODO: Function to change password.

}
?>
