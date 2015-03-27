<?php
// database.php
// The database class collects all functions needed to login and logout from the database.
global $inIndex;
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
class Database {
	private $databaseId;
	private function mysql_query_encaps($sql) {
		global $loggedUser, $developversion;
		try {
			if ($developversion) {
				$run = $this->databaseId->query ( "INSERT INTO logging(loginid, logdate, logtime, logurl, navigator, screenresolution, language, sqlstatement) 
                       VALUES(\"" . ($loggedUser ? $loggedUser : "anonymous") . "\", " . date ( 'Ymd' ) . ", " . date ( 'His' ) . ", '', '', '', '', '" . $sql . "');" );
			}
			$run = $this->databaseId->query ( $sql );
		} catch ( PDOException $ex ) {
			$entryMessage = "A database error occured!"; // user friendly message
		}
		
		return $run;
	}
	public function execSQL($sql) {
		if (! $this->databaseId) {
			echo "Database connection lost...";
			$this->newLogin ();
		}
		$run = $this->mysql_query_encaps ( $sql );
	}
	public function selectKeyValueArray($sql, $key, $value) {
		if (! $this->databaseId) {
			echo "Database connection lost...";
			$this->newLogin ();
		}
		$run = $this->mysql_query_encaps ( $sql );
		while ( $get = $run->fetch ( PDO::FETCH_OBJ ) )
			$result [$get->$key] = $get->$value;
		if (isset ( $result ))
			return $result;
		else
			return array ();
	}
	public function selectRecordset($sql) {
		if (! $this->databaseId) {
			echo "Database connection lost...";
			$this->newLogin ();
		}
		$run = $this->mysql_query_encaps ( $sql );
		return $run;
	}
	public function selectRecordArray($sql) {
		if (! $this->databaseId) {
			echo "Database connection lost...";
			$this->newLogin ();
		}
		$result = array ();
		$run = $this->mysql_query_encaps ( $sql );
		if ($get = $run->fetch ( PDO::FETCH_OBJ ))
			while ( list ( $key, $value ) = each ( $get ) )
				$result [$key] = $value;
		return $result;
	}
	public function selectRecordsetArray($sql) {
		if (! $this->databaseId) {
			echo "Database connection lost...";
			$this->newLogin ();
		}
		
		try {
			$run = $this->databaseId->query ( $sql );
		} catch ( PDOException $ex ) {
			$entryMessage = "A database error occured!"; // user friendly message
		}
		
		$result = array ();
		while ( $get = $run->fetch ( PDO::FETCH_OBJ ) ) {
			$resultparts = array ();
			while ( list ( $key, $value ) = each ( $get ) )
				$resultparts [$key] = $value;
			$result [] = $resultparts;
		}
		
		return $result;
	}
	public function selectSingleArray($sql, $name) {
		if (! $this->databaseId) {
			echo "Database connection lost...";
			$this->newLogin ();
		}
		try {
			$run = $this->databaseId->query ( $sql );
		} catch ( PDOException $ex ) {
			$entryMessage = "A database error occured!"; // user friendly message
		}
		
		$result = array ();
		while ( $get = $run->fetch ( PDO::FETCH_OBJ ) )
			$result [] = $get->$name;
		return $result;
	}
	public function result($sql) {
		return $this->databaseId->query ( $sql )->fetchColumn ();
	}
	public function insert_id() {
		return $this->databaseId->lastInsertId ();
	}
	public function selectSingleValue($sql, $name, $nullvalue = '') {
		if (! $this->databaseId) {
			echo "Database connection lost...";
			$this->newLogin ();
		}
		try {
			$run = $this->databaseId->query ( $sql );
		} catch ( PDOException $ex ) {
			$entryMessage = "A database error occured!"; // user friendly message
		}
		
		$get = $run->fetch ( PDO::FETCH_ASSOC );
		if ($get)
			if ($get [$name] != '') {
				return $get [$name];
			} else
				return $nullvalue;
		else
			return $nullvalue;
	}
	function __construct() {
		global $dbname, $host, $user, $pass;
		if (! $this->databaseId) {
			$this->databaseId = new PDO ( 'mysql:host=' . $host . ';dbname=' . $dbname . ';charset=utf8', $user, $pass );
		}
		return $this->databaseId;
	}
}
?>
