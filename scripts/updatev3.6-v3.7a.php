<?php

  require_once "../lib/setup/databaseInfo.php";
  require_once "../lib/database.php";

  print "Database update will add field for registering the last observation read by the observer.\n";

  $sql="ALTER TABLE observers ADD COLUMN lastReadObservationId INT(20) NOT NULL DEFAULT 0 AFTER fstOffset";
  $run=mysql_query($sql) or die(mysql_error());

  print "Database update will add field for the copyright notice under observations / sketches.\n";
  $sql="ALTER TABLE dsltrunk.observers ADD COLUMN copyright VARCHAR(128) NOT NULL DEFAULT '' AFTER lastReadObservationId;";
  $run=mysql_query($sql) or die(mysql_error());
  
  
  print "Database update succesful.\n";

?>