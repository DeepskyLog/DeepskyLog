<?php
 require_once "../lib/setup/databaseInfo.php";
 require_once "../lib/database.php";

 print "Database update will erase users whithout observations, instruments... or so.\n";
 print "Database update may take some minutes...\n";
 
 $sql = "DELETE FROM observers
         WHERE (id NOT IN (SELECT DISTINCT observerid FROM observations))       AND
               (id NOT IN (SELECT DISTINCT observerid FROM cometobservations))  AND
               (id NOT IN (SELECT DISTINCT observer   FROM eyepieces))          AND
               (id NOT IN (SELECT DISTINCT observer   FROM filters))            AND
               (id NOT IN (SELECT DISTINCT observer   FROM lenses))             AND
               (id NOT IN (SELECT DISTINCT observer   FROM locations))          AND
               (id NOT IN (SELECT DISTINCT observer   FROM instruments))        AND
               (id NOT IN (SELECT DISTINCT observerid FROM observerobjectlist)) AND
               (role != 0);";
 $run = mysql_query($sql) or die(mysql_error());

 print "Database update was successful!\n";

?>