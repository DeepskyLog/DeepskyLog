<?php

// database.php
// The database class collects all functions needed to login and logout from the database.
global $inIndex;
if ((!isset($inIndex)) || (!$inIndex)) {
    include "../../redirect.php";
}
class Database
{
    private $databaseId;
    private function mysql_query_encaps($sql)
    {
        global $loggedUser, $developversion;
        try {
            // Log the exact SQL prior to execution to help diagnose
            // syntax issues that may be caused by stray characters.
            $len = is_string($sql) ? strlen($sql) : 0;
            // Log the full SQL with clear delimiters so we can spot
            // leading/trailing parentheses or semicolons.
            $full = is_string($sql) ? $sql : '';
            error_log('=== Database (OLD) executing SQL START (len=' . $len . ') ===');
            error_log($full);
            error_log('=== Database (OLD) executing SQL END ===');
            $run = null;
            $run = $this->databaseId->query($sql);
        } catch (PDOException $ex) {
            $entryMessage = "A database error occured! (" . $ex->getMessage() . ")"; // include exception for debugging
            error_log($entryMessage);
            // also log the error and the full SQL to the error log for investigation
            error_log('Database error: ' . $ex->getMessage() . ' SQL (len=' . (is_string($sql) ? strlen($sql) : 0) . '):');
            error_log('=== Database (OLD) failed SQL START ===');
            error_log(is_string($sql) ? $sql : '');
            error_log('=== Database (OLD) failed SQL END ===');
            // return a dummy result object to avoid call to ->fetch() on null
            $run = new class {
                public function fetch($mode = null) { return false; }
                public function fetchColumn() { return false; }
                public function execute($params = null) { return false; }
                public function rowCount() { return 0; }
            };
        }
        return $run;
    }
    public function execSQL($sql)
    {
        if (!$this->databaseId) {
            echo "Database connection lost...";
            $this->newLogin();
        }
        $this->mysql_query_encaps($sql);
    }
    public function selectKeyValueArray($sql, $key, $value)
    {
        if (!$this->databaseId) {
            echo "Database connection lost...";
            $this->newLogin();
        }
        $run = $this->mysql_query_encaps($sql);
        while ($get = $run->fetch(PDO::FETCH_OBJ)) {
            $result [$get->$key] = $get->$value;
        }
        if (isset($result)) {
            return $result;
        } else {
            return array();
        }
    }
    public function selectRecordset($sql)
    {
        if (!$this->databaseId) {
            echo "Database connection lost...";
            $this->newLogin();
        }
        $run = $this->mysql_query_encaps($sql);
        return $run;
    }
    public function selectRecordArray($sql)
    {
        if (!$this->databaseId) {
            echo "Database connection lost...";
            $this->newLogin();
        }
        $result = array();
        $run = $this->mysql_query_encaps($sql);
        if ($get = $run->fetch(PDO::FETCH_OBJ)) {
            foreach ($get as $key => $value) {
                $result [$key] = $value;
            }
        }
        return $result;
    }
    public function prepareAndSelectRecordsetArray($sql, $values)
    {
        if (!$this->databaseId) {
            echo "Database connection lost...";
            $this->newLogin();
        }

        try {
            $run = $this->databaseId->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $run->execute($values);
        } catch (PDOException $ex) {
            $entryMessage = "A database error occured! (" . $ex->getMessage() . ")"; // include exception for debugging
            error_log($entryMessage);
            $run = new class {
                public function fetch($mode = null) { return false; }
                public function fetchColumn() { return false; }
                public function execute($params = null) { return false; }
                public function rowCount() { return 0; }
            };
        }

        $result = array();
        while ($get = $run->fetch(PDO::FETCH_OBJ)) {
            $resultparts = array();
            foreach ($get as $key => $value) {
                $resultparts [$key] = $value;
            }
            $result [] = $resultparts;
        }

        return $result;
    }

    public function selectRecordsetArray($sql)
    {
        if (!$this->databaseId) {
            echo "Database connection lost...";
            $this->newLogin();
        }
        // Use central query wrapper to ensure logging and consistent
        // dummy-result behaviour on exceptions.
        $run = $this->mysql_query_encaps($sql);

        $result = array();
        while ($get = $run->fetch(PDO::FETCH_OBJ)) {
            $resultparts = array();
            foreach ($get as $key => $value) {
                $resultparts[$key] = $value;
            }
            $result [] = $resultparts;
        }

        return $result;
    }
    public function selectSingleArray($sql, $name)
    {
        if (!$this->databaseId) {
            echo "Database connection lost...";
            $this->newLogin();
        }
        $run = $this->mysql_query_encaps($sql);

        $result = array();
        while ($get = $run->fetch(PDO::FETCH_OBJ)) {
            $result [] = $get->$name;
        }
        return $result;
    }
    public function result($sql)
    {
        return $this->databaseId->query($sql)->fetchColumn();
    }
    public function insert_id()
    {
        return $this->databaseId->lastInsertId();
    }
    public function selectSingleValue($sql, $name, $nullvalue = '')
    {
        if (!$this->databaseId) {
            echo "Database connection lost...";
            $this->newLogin();
        }
        $run = $this->mysql_query_encaps($sql);

        $get = $run->fetch(PDO::FETCH_ASSOC);
        if ($get) {
            if ($get [$name] != '') {
                return $get [$name];
            } else {
                return $nullvalue;
            }
        } else {
            return $nullvalue;
        }
    }
    public function __construct()
    {
        global $dbname, $host, $user, $pass;
        if (!$this->databaseId) {
            $this->databaseId = new PDO(
                'mysql:host=' . $host . ';dbname=' . $dbname . ';charset=utf8',
                $user,
                $pass,
                array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
            );
        }
        return $this->databaseId;
    }
}
