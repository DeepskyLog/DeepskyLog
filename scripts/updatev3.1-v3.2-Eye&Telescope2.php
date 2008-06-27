<?php
 include_once "../lib/database.php";
 include_once "../lib/objects.php";

 $object = new Objects;

 $db = new database;
 $db->login();

 $sql = "select * from objects where name like \"PK %+0%\"";
 $run = mysql_query($sql) or die(mysql_error());
 $get = mysql_fetch_object($run);

 while($get = mysql_fetch_object($run))
 {
  $name = $get->name;

  $datasource = $get->datasource;

  $tmp = explode("+0", $name);
  $newname = $tmp[0]."+".$tmp[1];
  $catname = explode(" ", $newname);

  if ($tmp[1][0] != ".")
  {
   $sql1 = "update objects set name = \"$newname\" where name = \"$name\";";
   $run1 = mysql_query($sql1) or die(mysql_error());

   $sql1 = "update objectnames set objectname = \"$newname\" where name = \"$name\";";
   $run1 = mysql_query($sql1) or die(mysql_error());

   $sql1 = "update objectnames set altname = \"$newname\" where name = \"$newname\";";
   $run1 = mysql_query($sql1) or die(mysql_error());

   $sql1 = "update objectnames set catindex = \"$catname[1]\" where name = \"$newname\";";
   $run1 = mysql_query($sql1) or die(mysql_error());
  }
 }


 $sql = "select * from objects where name like \"PK %-0%\"";
 $run = mysql_query($sql) or die(mysql_error());
 $get = mysql_fetch_object($run);

 while($get = mysql_fetch_object($run))
 {
  $name = $get->name;

  $datasource = $get->datasource;

  $tmp = explode("-0", $name);
  $newname = $tmp[0]."-".$tmp[1];
  $catname = explode(" ", $newname);

  if ($tmp[1][0] != ".")
  {
   $sql1 = "update objects set name = \"$newname\" where name = \"$name\";";
   $run1 = mysql_query($sql1) or die(mysql_error());

   $sql1 = "update objectnames set objectname = \"$newname\" where name = \"$name\";";
   $run1 = mysql_query($sql1) or die(mysql_error());

   $sql1 = "update objectnames set altname = \"$newname\" where name = \"$newname\";";
   $run1 = mysql_query($sql1) or die(mysql_error());

   $sql1 = "update objectnames set catindex = \"$catname[1]\" where name = \"$newname\";";
   $run1 = mysql_query($sql1) or die(mysql_error());
  }
 }

 $db->logout();

 print "Database was updated successfully!\n";
?>
