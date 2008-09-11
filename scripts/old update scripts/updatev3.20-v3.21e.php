<?php
 include_once "../lib/database.php";
 include_once "../lib/instruments.php";

 $db = new database;
 $dbid = $db->login();

 $sql = "ALTER TABLE eyepieces ADD maxFocalLength float default -1";
 $run = mysql_query($sql) or die(mysql_error());

 echo "Database was updated succesfully!\n";

 $db->logout();
?>
