<?php
 include_once "../lib/database.php";
 include_once "../lib/observations.php";

 $db = new database;
 $db->login();
 $observation = new Observations;

 // Add a column seeing and limiting magnitude to the observation
 $sql = "ALTER TABLE observations ADD seeing int(1) default NULL";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "ALTER TABLE observations ADD limmag float default NULL";
 $run = mysql_query($sql) or die(mysql_error());

 // Get limiting magnitude and seeing
 $sql = "SELECT * FROM observations";
 $run = mysql_query($sql) or die(mysql_error());

 while($get = mysql_fetch_object($run))
 {
  $description = $get->description;

  $vars = explode("\n\n[LIMMAG ", $description);

  if ($vars[1] == "")
  {
   $limmag = -1;
  }
  else
  {
   $limmag = preg_replace("/(\d+)]/", "$1", $vars[1]);
  }

  $vars = explode("\n\n[SEEING ", $description);

  if ($vars[1] == "")
  {
   $seeing[0] = -1;
  }
  else
  {
   $seeing = explode("]", $vars[1]);
  }
  $id = $get->id;

  if ($seeing[0] != -1)
  {
   $observation->setSeeing($id, $seeing[0]);
  }
  if ($limmag != -1)
  {
   $observation->setLimitingMagnitude($id, $limmag);
  }
  $observation->setDescription($id, htmlspecialchars($vars[0]));
 }

 echo "Database was updated succesfully!\n";
?>
