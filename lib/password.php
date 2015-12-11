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
    $sql = "INSERT INTO password_change_requests (id, userid) VALUES(\"" . $token . "\", \"" . $userid . "\")";
    print $sql;
    $objDatabase->execSQL ( $sql );
print "1";
    // Test
    $db1 = $objDatabase->selectSingleArray ( "select id from password_change_requests", "id" );
    print_r($db1);
print "2";
    $db2 = $objDatabase->selectSingleArray ( "select userid from password_change_requests", "userid" );
    print_r($db2);
print "3";
    exit;
  }

  // TODO: Function to remove token when successfully logged in.
  // TODO: Function to change password.
  // TODO: Function to cancel change request.
}
?>
