<?php
// messages.php
// The messages class collects all functions needed to send, read and delete mesagges in DeepskyLog from the database.
global $inIndex;
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
class Messages {
	public function getNumberOfUnreadMails() {
		global $objDatabase, $loggedUser;
		if ($loggedUser) {
			return "" . count ( $this->getIdsNewMails ( $loggedUser ) ) . "/" . count ( $this->getIdsAllMails ( $loggedUser ) ) . "";
		} else {
			return "";
		}
	}
	public function getIdsNewMails($user) {
		global $objDatabase;
		$listOfAllMails = $this->getIdsAllMails ( $user );

		// Read mails should not be counted in the first part
		$readMails = $objDatabase->selectSingleArray ( "select id from messagesRead where receiver = \"" . $user . "\"", "id" );

		$cnt = 0;

		for($i = 0; $i < count ( $listOfAllMails ); $i ++) {
			if (! in_array ( $listOfAllMails [$i], $readMails )) {
				$listOfMails [$cnt] = $listOfAllMails [$i];
				$cnt ++;
			}
		}
		if (isset ( $listOfMails )) {
			// Swap the array
			$listOfMails = array_reverse ( $listOfMails );

			return $listOfMails;
		} else {
			return Array ();
		}
	}

	// Returns a list of all mails. The deleted mails are not included in the list of id's.
	public function getIdsAllMails($user) {
		global $objDatabase;
		$listOfAllMails = $objDatabase->selectSingleArray ( "select id from messages where receiver = \"" . $user . "\" or receiver = \"all\"", "id" );

		$listOfMails = Array ();

		// Removed mails should not be counted
		$removedMails = $objDatabase->selectSingleArray ( "select id from messagesDeleted where receiver = \"" . $user . "\"", "id" );

		$cnt = 0;

		for($i = 0; $i < count ( $listOfAllMails ); $i ++) {
			if (! in_array ( $listOfAllMails [$i], $removedMails )) {
				$listOfMails [$cnt] = $listOfAllMails [$i];
				$cnt ++;
			}
		}

		return $listOfMails;
	}
	public function getSubject($id) {
		global $objDatabase;
		return $objDatabase->selectSingleValue ( "select subject from messages where id = \"" . $id . "\"", "subject" );
	}
	public function getSender($id) {
		global $objDatabase;
		return $objDatabase->selectSingleValue ( "select sender from messages where id = \"" . $id . "\"", "sender" );
	}
	public function getReceiver($id) {
		global $objDatabase;
		return $objDatabase->selectSingleValue ( "select receiver from messages where id = \"" . $id . "\"", "receiver" );
	}
	public function getDate($id) {
		global $objDatabase;
		return $objDatabase->selectSingleValue ( "select date from messages where id = \"" . $id . "\"", "date" );
	}
	public function getContent($id) {
		global $objDatabase, $baseURL;
		$message = $objDatabase->selectSingleValue ( "select message from messages where id = \"" . $id . "\"", "message" );
		$message = str_replace ( 'http://www.deepskylog.be/', $baseURL, $message );
		$message = str_replace ( 'http://www.deepskylog.nl/', $baseURL, $message );
		$message = str_replace ( 'http://www.deepskylog.de/', $baseURL, $message );
		$message = str_replace ( 'http://www.deepskylog.fr/', $baseURL, $message );
		$message = str_replace ( 'http://www.deepskylog.org/', $baseURL, $message );
		return $message;
	}
	public function removeAllMessages($id) {
		global $objDatabase;
		if ($id != "") {
			$allMessages = $this->getIdsAllMails ( $id );
			for($cnt = 0; $cnt < count ( $allMessages ); $cnt ++) {
				$objDatabase->execSQL ( "insert into messagesDeleted VALUES(\"" . $allMessages [$cnt] . "\", \"" . $id . "\")" );
			}
		}
	}
	public function getContentWithoutLinks($id) {
		global $objDatabase;
		$message = $objDatabase->selectSingleValue ( "select message from messages where id = \"" . $id . "\"", "message" );
		return strip_tags ( $message, '<br>' );
	}
	public function isRead($id, $receiver) {
		global $objDatabase;
		$read = $objDatabase->selectSingleValue ( "select id from messagesRead where id = \"" . $id . "\" and receiver = \"" . $receiver . "\"", "id" );
		if ($read == "") {
			return false;
		} else {
			return true;
		}
	}
	public function isDeleted($id, $receiver) {
		global $objDatabase;
		$deleted = $objDatabase->selectSingleValue ( "select id from messagesDeleted where id = \"" . $id . "\" and receiver = \"" . $receiver . "\"", "id" );
		if ($deleted == "") {
			return false;
		} else {
			return true;
		}
	}
	public function markMessageRead($id, $receiver) {
		global $objDatabase;
		$objDatabase->execSQL ( "insert into messagesRead VALUES(\"" . $id . "\", \"" . $receiver . "\")" );
	}
	public function validateDeleteMessage() {
		global $objDatabase, $loggedUser;
		if ($loggedUser != "") {
			$id = $_GET ["id"];
			$objDatabase->execSQL ( "insert into messagesDeleted VALUES(\"" . $id . "\", \"" . $loggedUser . "\")" );
		}
	}
	public function getReplyToSubject($id) {
		$subject = $this->getSubject ( $id );
		if (strpos ( $subject, "Re : " ) === false) {
			return "Re : " . $subject;
		} else {
			return $subject;
		}
	}
	public function getReplyToMessage($id) {
		$message = $this->getContent ( $id );
		// Replace <br /> by <br />>
		$message = str_replace ( "<br />", "", $message );
		$message = str_replace ( "\n", "\n> ", $message );
		return "> " . $message;
	}
	public function validateMessage() {
		global $loggedUser;
		if (! ($loggedUser))
			throw new Exception (_('You should be logged in to be able to send messages.'));

		if (array_key_exists ( 'send_mail', $_POST ) && ($_POST ['send_mail'] == "on")) {
			$this->sendRealMessage ( $loggedUser, $_POST ['receiver'], $_POST ['subject'], nl2br ( addslashes ( $_POST ['message'] ) ) );
		} else {
			$this->sendMessage ( $loggedUser, $_POST ['receiver'], $_POST ['subject'], nl2br ( addslashes ( $_POST ['message'] ) ) );
		}
	}

	// Returns a list of all read mails.
	public function getIdsReadMails($user) {
		global $objDatabase;
		$listOfAllMails = $this->getIdsAllMails ( $user );
		$listOfNewMails = $this->getIdsNewMails ( $user );

		$listOfReadMails = Array ();

		$cnt = 0;

		for($i = 0; $i < count ( $listOfAllMails ); $i ++) {
			if (! in_array ( $listOfAllMails [$i], $listOfNewMails )) {
				$listOfReadMails [$cnt] = $listOfAllMails [$i];
				$cnt ++;
			}
		}

		// Swap the array
		$listOfReadMails = array_reverse ( $listOfReadMails );

		return $listOfReadMails;
	}
	public function sendMessage($sender, $receiver, $subject, $message) {
		global $objDatabase, $objObserver;
		$date = $mysqldate = date ( 'Y-m-d H:i:s' );

		// We check whether the observer wants to receive the DeepskyLog messages as email. If so, we send an email.
		if ($objObserver->getObserverProperty ( $receiver, 'sendMail' )) {
			$senderName = $objObserver->getFullName($sender);
			$message = sprintf(_("DeepskyLog message from %s:"), $senderName) . "<br /><br />" . $message . "<br /><br />";
			$this->sendEmail($subject, $message, $receiver);
		}

		if ($receiver == "all") {
			// We loop over all observers and send all observers who wants to receive the messages as email a mail.
			$toMail = $objDatabase->selectSingleArray ( "select * from observers where sendMail=\"1\" and role=\"1\"", "id" );
			if (sizeof ( $toMail ) > 0) {
				foreach ( $toMail as $mailTo ) {
					$this->sendEmail ( $subject, $message . "<br /><br />", $mailTo );
				}
			}
		}
		$objDatabase->execSQL ( "INSERT into messages (sender, receiver, subject, message, date) VALUES(\"" . $sender . "\", \"" . $receiver . "\", \"" . $subject . "\", '" . $message . "', \"" . $date . "\")" );
	}
	public function sendEmail($subject, $message, $userid, $cc = false) {
		// Sends a html mail to the given userid. If $userid == "developers", then we send a mail to the DeepskyLog team.
		// If $cc is true, we also send a CC to the developers.
		global $mailFrom, $instDir, $objObserver;
		global $mailHost, $mailSMTPAuth, $mailServerUsername, $mailServerPassword, $mailSMTPSecure, $mailPort;

		require_once('PHPMailer/class.phpmailer.php');

		// Making the headers for the html mail
		$headers = "From: " . $mailFrom . "\r\n";
		$headers .= "Reply-To: ". $mailFrom . "\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=UTF-8\r\n";

		// Add header and footer to mail.
		$messageHeader = '<html><body>';
		$messageHeader .= '<h1>' . $subject . '</h1>';
		$messageFooter = '<a href=""><img src="cid:logo" style="width:80%;"></a>';
		$messageFooter .= '</body></html>';

		$mail = new PHPMailer();

		$mail->IsSMTP();    			// set mailer to use SMTP
		$mail->Host = $mailHost;
		$mail->SMTPAuth = $mailSMTPAuth;
		$mail->Username = $mailServerUsername;    // SMTP username
		$mail->Password = $mailServerPassword;    // SMTP password
		$mail->SMTPSecure = $mailSMTPSecure;
		$mail->Port = $mailPort;    // SMTP Port

		$mail->From = $mailFrom;    //From Address
		$mail->FromName = "DeepskyLog Team";    //From Name

		// We get the mailaddress and the full name from the userid
		if (strcmp($userid, "developers") == 0) {
			$fullName = "DeepskyLog Team";
			$mailAddress = $mailFrom;
		} else {
			$fullName = $objObserver->getFullName($userid);
			$mailAddress = $objObserver->getObserverProperty($userid, "email", '');
		}

		$mail->AddAddress($mailAddress, $fullName);    //To Address
		$mail->AddReplyTo($mailFrom, "DeepskyLog Team"); //Reply-To Address

		if ($cc) {
			$mail->AddCC( $mailFrom );
		}

		$mail->WordWrap = 50;    // set word wrap to 50 characters
		$mail->IsHTML(true);     // set email format to HTML
		$mail->AddEmbeddedImage($instDir . '/images/logo.png', 'logo');

		$mail->Subject = $subject;
		$mail->Body    = $messageHeader . $message . $messageFooter;

		$mail->send();
	}
	public function sendRealMessage($sender, $receiver, $subject, $message) {
		global $objDatabase, $objObserver;
		$date = $mysqldate = date ( 'Y-m-d H:i:s' );

		// We loop over all observers and send all observers who wants to receive the messages as email a mail.
		$toMail = $objDatabase->selectSingleArray ( "select * from observers where role=\"1\"", "id" );
		if (sizeof ( $toMail ) > 0) {
			foreach ( $toMail as $mailTo ) {
				$this->sendEmail ( $subject, $message . "<br /><br />", $mailTo );
			}
		}
		$objDatabase->execSQL ( "INSERT into messages (sender, receiver, subject, message, date) VALUES(\"" . $sender . "\", \"" . $receiver . "\", \"" . $subject . "\", '" . $message . "', \"" . $date . "\")" );
	}
	public function showListMails($newMails, $readMails) {
		global $baseURL, $baseURL, $objPresentations, $objObserver, $dateformat, $loggedUser, $objUtil;

		// Add the button to select which columns to show
		$objUtil->addTableColumSelector ();

		echo "<table class=\"table sort-table table-condensed table-striped table-hover tablesorter custom-popup\">\n";

		// Making the header for the mails
		echo "<thead><tr>";
		echo "<th data-priority=\"critical\">";
		echo _('Subject');
		echo "</th>";
		echo "<th>";
		echo _('Sender');
		echo "</th>";

		echo "<th>";
		echo _('Date');
		echo "</th>";

		echo "</tr></thead>";
		echo "<tbody>";
		// Combining all mails
		$allMails = array_merge ( $newMails, $readMails );

		$count = 0;

		// Showing the mails, loop over the id's of the combined array of new and read mails.
		for($cnt = 0; $cnt < count ( $allMails ); $cnt ++) {
			if ($loggedUser == "") {
				echo "<tr table-no-border>";
			} else {
				echo "<tr>";
			}
			echo "<td>";

			if (! in_array ( $allMails [$cnt], $readMails )) {
				echo "<span class=\"label label-success\">New</span>&nbsp;";
			}
			echo "<a href = \"" . $baseURL . "index.php?indexAction=view_message&amp;id=" . $allMails [$cnt] . "\">" . $this->getSubject ( $allMails [$cnt] ) . "</a>";
			echo "</td>";
			$senderId = $this->getSender ( $allMails [$cnt] );
			if ($senderId == "DeepskyLog") {
				$senderName = $senderId;
			} else {
				$senderName = $objObserver->getObserverProperty ( $senderId, "firstname" ) . "&nbsp;" . $objObserver->getObserverProperty ( $senderId, "name" );
				$senderName = "<a href=\"" . $baseURL . "index.php?indexAction=detail_observer&amp;user=" . $senderId . "\">" . $senderName . "</a>";
			}
			echo "<td>" . $senderName . "</td>";

			// Use the date format from databaseInfo
			$phpdate = strtotime ( $this->getDate ( $allMails [$cnt] ) );
			echo "<td>" . date ( $dateformat . " G:i:s", $phpdate ) . "</td>";

			echo "</tr>";
			$count++;
		}
		echo "</tbody>
           </table>";

	 $objUtil->addPager ( "", $count );
	}
}
?>
