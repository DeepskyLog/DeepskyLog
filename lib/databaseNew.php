<?php

// database.php
// The database class collects all functions needed to login and logout from the database.
global $inIndex;
if ((!isset($inIndex)) || (!$inIndex)) {
    include "../../redirect.php";
}
class database_new
{
    private $databaseId;
    private function mysql_query_encaps($sql)
    {
        global $loggedUser, $developversion;
        try {
            $run = $this->databaseId->query($sql);
        } catch (PDOException $ex) {
            $entryMessage = "A database error occured!"; // user friendly message
            print $entryMessage;
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
            $entryMessage = "A database error occured!"; // user friendly message
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

        try {
            $run = $this->databaseId->query($sql);
        } catch (PDOException $ex) {
            $entryMessage = "A database error occured!"; // user friendly message
        }

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
        try {
            $run = $this->databaseId->query($sql);
        } catch (PDOException $ex) {
            $entryMessage = "A database error occured!"; // user friendly message
        }

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
        try {
            $run = $this->databaseId->query($sql);
        } catch (PDOException $ex) {
            $entryMessage = "A database error occured!"; // user friendly message
        }

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
        global $dbnameNew, $host, $user, $pass;
        if (!$this->databaseId) {
            $this->databaseId = new PDO('mysql:host=' . $host . ';dbname=' . $dbnameNew . ';charset=utf8', $user, $pass);
        }
        return $this->databaseId;
    }
}
