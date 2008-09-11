<?php
 include_once "../lib/database.php";
 include_once "../lib/objects.php";

 $object = new Objects;

 $db = new database;
 $db->login();

 // Add a column club to the observer
 $sql = "ALTER TABLE observers ADD club varchar(20) NOT NULL default ''";
 $run = mysql_query($sql) or die(mysql_error());

 // Add a column stdatlas to the observer
 $sql = "ALTER TABLE observers ADD stdatlas int(3) NOT NULL default '0'";
 $run = mysql_query($sql) or die(mysql_error());

 // Correct the uranometriapages
 $sql = "SELECT * FROM objects";
 $run = mysql_query($sql) or die(mysql_error());

 while($get = mysql_fetch_object($run))
 {
  $ra = $get->ra;
  $dec = $get->decl;
  $urano = $object->calculateUranometriaPage($ra, $dec);
  $name = $get->name;

  $sql2 = "UPDATE objects SET urano = \"$urano\" WHERE name = \"$name\"";
  $run2 = mysql_query($sql2) or die(mysql_error());
 }


 // Remove some objects from the database
 $sql = "DELETE FROM objects WHERE name=\"Eridanus Cluster\"";
 mysql_query($sql) or die(mysql_error());

 $sql = "UPDATE objects SET alternative_name = \"\" WHERE alternative_name = \"Hyades\"";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "DELETE FROM objects WHERE name=\"Coalsack\"";
 mysql_query($sql) or die(mysql_error());

 $sql = "UPDATE objects SET alternative_name = \"\" WHERE alternative_name = \"Small Magellanc Cl\"";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "DELETE FROM objects WHERE name=\"Lg Magellanic Cl\"";
 mysql_query($sql) or die(mysql_error());

 $sql = "DELETE FROM objects WHERE name=\"Wild's triplet\"";
 mysql_query($sql) or die(mysql_error());

 $sql = "DELETE FROM objects WHERE name=\"Zwicky's triplet\"";
 mysql_query($sql) or die(mysql_error());

 $sql = "UPDATE objects SET alternative_name = \"Arp 274\" WHERE alternative_name = \"ARP 274\"";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "UPDATE objects SET alternative_name = \"Ap 2-1\" WHERE alternative_name = \"AP 2-1\"";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "UPDATE objects SET alternative_name = \"vdB 66\" WHERE alternative_name = \"VDB 66\"";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "UPDATE objects SET alternative_name = \"Ced 55k\" WHERE alternative_name = \"CED 55K\"";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "UPDATE objects SET alternative_name = \"Ced 55q\" WHERE alternative_name = \"CED 55Q\"";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "UPDATE objects SET alternative_name = \"Ced 92\" WHERE alternative_name = \"CED 92\"";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "UPDATE objects SET alternative_name = \"Ced 208\" WHERE alternative_name = \"CED 208\"";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "UPDATE objects SET alternative_name = \"Ced 19i\" WHERE alternative_name = \"CED 19I\"";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "UPDATE objects SET alternative_name = \"Ced 55b\" WHERE alternative_name = \"CED 55B\"";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "UPDATE objects SET alternative_name = \"Ced 55c\" WHERE alternative_name = \"CED 55C\"";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "UPDATE objects SET alternative_name = \"Ced 55p\" WHERE alternative_name = \"CED 55P\"";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "UPDATE objects SET alternative_name = \"Ced 67a\" WHERE alternative_name = \"CED 67A\"";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "UPDATE objects SET alternative_name = \"Ced 89b\" WHERE alternative_name = \"CED 89B\"";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "UPDATE objects SET alternative_name = \"Ced 182b\" WHERE alternative_name = \"CED 182B\"";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "UPDATE objects SET alternative_name = \"Ced 182c\" WHERE alternative_name = \"CED 182C\"";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "DELETE FROM objects WHERE name=\"Pal 9\"";
 mysql_query($sql) or die(mysql_error());

 $sql = "UPDATE objects SET alternative_name = \"\" WHERE alternative_name = \"Pal 15\"";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "DELETE FROM objects WHERE name = \"NGC 4153\"";
 $run = mysql_query($sql) or die(mysql_error());

 $db->logout();


 echo "Database was updated succesfully!\n";
?>
