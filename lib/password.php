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

		// Make sure to store the token in UTC
		$now = new DateTime("now", new DateTimeZone('UTC'));

    $sql = "INSERT INTO password_change_requests (token, time, userid) VALUES(\"" . $token . "\", \"" . $now->format('Y-m-d H:i:s') . "\", \"" . $userid . "\")";

    $objDatabase->execSQL ( $sql );
  }

  // Removes the change request. This can happen in three occasions:
  // - When adding a new change request for the given user.
  // - When there is a Password Change Request, but the observer does log in successfully.
  // - When the time for the password change request has passed.
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

  // Returns the userid when a token is given.
  public function getUserId($token) {
    global $objDatabase;

    $userid = $objDatabase->selectSingleArray ( "select userid from password_change_requests where token=\"" . $token . "\"", "userid" );

    return $userid[0];
  }

	// Returns true if the token EXISTS
	public function tokenExists($token) {
		global $objDatabase;

		$token = $objDatabase->selectSingleArray ( "select * from password_change_requests where token=\"" . $token . "\"", "userid" );

		if (sizeof($token) > 0) {
			return true;
		}

		return false;
	}

	// Checks if the token is still valid. The token is not longer valid after 24 hours. If the token is invalid, we remove the token.
	public function isValid($token) {
		global $objDatabase;

		$time = $objDatabase->selectSingleArray ( "select time from password_change_requests where token=\"" . $token . "\"", "time" );
		$now = new DateTime("now", new DateTimeZone('UTC'));
		$tokenTime = new DateTime($time[0], new DateTimeZone('UTC'));

		// Get the time difference in seconds
		$diff = $now->getTimestamp() - $tokenTime->getTimestamp();

		// If the time difference is larger than a day, we remove the token and return false;
		if ($diff > 24*60*60) {
			$this->removeToken($token);
			return false;
		} else {
			return true;
		}
	}

  // TODO: Function to change password.

}
?>
