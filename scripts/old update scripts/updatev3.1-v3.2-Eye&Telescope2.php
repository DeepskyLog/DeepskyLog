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

  $sql2 = "select * from objects where name = \"$newname\"";
  $run2 = mysql_query($sql2) or die(mysql_error());

  $exist = 0;

  if ($get2 = mysql_fetch_object($run2))
  {
    $exist = 1;
  }

  if ($tmp[1][0] != ".")
  {
   if ($exist == 1)
   {
    $sql1 = "delete from objects where name = \"$newname\";";
    $run1 = mysql_query($sql1) or die(mysql_error());

    $sql1 = "update objects set name = \"$newname\" where name = \"$name\";";
    $run1 = mysql_query($sql1) or die(mysql_error());
   } else {
    $sql1 = "update objects set name = \"$newname\" where name = \"$name\";";
    $run1 = mysql_query($sql1) or die(mysql_error());

    $sql1 = "update objectnames set objectname = \"$newname\" where objectname = \"$name\";";
    $run1 = mysql_query($sql1) or die(mysql_error());

    $sql1 = "update objectnames set altname = \"$newname\" where objectname = \"$newname\";";
    $run1 = mysql_query($sql1) or die(mysql_error());

    $sql1 = "update objectnames set catindex = \"$catname[1]\" where objectname = \"$newname\";";
    $run1 = mysql_query($sql1) or die(mysql_error());
   }
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

  $sql2 = "select * from objects where name = \"$newname\"";
  $run2 = mysql_query($sql2) or die(mysql_error());

  $exist = 0;

  if ($get2 = mysql_fetch_object($run2))
  {
    $exist = 1;
  }

  if ($tmp[1][0] != ".")
  {
   if ($exist == 1)
   {
    $sql1 = "delete from objects where name = \"$newname\";";
    $run1 = mysql_query($sql1) or die(mysql_error());

    $sql1 = "update objects set name = \"$newname\" where name = \"$name\";";
    $run1 = mysql_query($sql1) or die(mysql_error());
   } else {
    $sql1 = "update objects set name = \"$newname\" where name = \"$name\";";
    $run1 = mysql_query($sql1) or die(mysql_error());

    $sql1 = "update objectnames set objectname = \"$newname\" where objectname = \"$name\";";
    $run1 = mysql_query($sql1) or die(mysql_error());

    $sql1 = "update objectnames set altname = \"$newname\" where objectname = \"$newname\";";
    $run1 = mysql_query($sql1) or die(mysql_error());

    $sql1 = "update objectnames set catindex = \"$catname[1]\" where objectname = \"$newname\";";
    $run1 = mysql_query($sql1) or die(mysql_error());
   }
  }
 }

 $db->logout();

 print "Database was updated successfully!\n";
?>
