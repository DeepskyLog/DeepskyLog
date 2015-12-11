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
    $db1 = $objDatabase->execSQL ( "DELETE from password_change_requests WHERE userid=\"" . $userid . "\"" );

    $sql = "INSERT INTO password_change_requests (id, userid) VALUES(\"" . $token . "\", \"" . $userid . "\")";

    $objDatabase->execSQL ( $sql );
//     // Test
//     $db1 = $objDatabase->selectSingleArray ( "select id from password_change_requests", "id" );
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

  // TODO: Function to change password.

  // TODO: The two next functions are the same...
  // TODO: Function to cancel change request.
  // TODO: Function to remove token when successfully logged in.
}
?>
