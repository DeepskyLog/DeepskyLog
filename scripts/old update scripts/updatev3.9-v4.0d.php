<?php
 require_once "../lib/setup/databaseInfo.php";
 require_once "../lib/database.php";
 print "Database update will expand the allowed description in object lists.\n";
 $sql = "ALTER TABLE observerobjectlist MODIFY COLUMN description VARCHAR(4096)";
 $run = mysql_query($sql) or die(mysql_error());
 print "Database update successful.\n";
?>