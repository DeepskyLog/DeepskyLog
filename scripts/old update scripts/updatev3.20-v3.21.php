<?php
  include_once "../lib/database.php";
  include_once "../lib/instruments.php";
  include_once "../lib/locations.php";


  $db = new database;
  $dbid = $db->login();

  $sql= "ALTER TABLE `objects` ADD COLUMN `SBObj` FLOAT NOT NULL DEFAULT 0 AFTER `taki`;";
  $run = mysql_query($sql) or die(mysql_error());

  $sql2 = "update objects set SBObj = 999 WHERE (diam1=0);";
  $run2 = mysql_query($sql2) or die(mysql_error());

  $sql3 = "select * from objects";
  $run3 = mysql_query($sql3) or die(mysql_error());

  while($get = mysql_fetch_object($run3))
  {
    $diam1 = $get->diam1;

    if ($diam1 != 0)
    {
			$diam2 = $get->diam2;
			if ($diam2 == 0)
			{
				$diam2 = $diam1;
			}
			$name = $get->name;
			$mag = $get->mag;
			$sbobj = ($mag + (2.5 * log10(2827.0 * ($diam1/60) * ($diam2/60))));

			$sql4 = "update objects set SBObj = \"$sbobj\" where name = \"$name\";";
			$run4 = mysql_query($sql4) or die(mysql_error());
    }
  }

 echo "Database was updated succesfully!\n";

 $db->logout();
?>
