<?php
 $inIndex=true;
 require_once "../lib/setup/databaseInfo.php";
 require_once "../lib/database.php";
 require_once "../lib/atlasses.php";
 require_once "../lib/objects.php";
 require_once "../lib/setup/language/nl/lang_main.php";

 $objDatabase=new Database();
 $objAtlas=new Atlasses;

 print "Database update will add the interstellarum atlas as one of the standard atlasses.\n";
# $sql = "INSERT INTO atlasses VALUES ('Interstellarum');";
# $run = mysql_query($sql) or die(mysql_error());

# $sql = "ALTER TABLE objects ADD COLUMN Interstellarum VARCHAR(4) NOT NULL DEFAULT 0 ;";
# $run = mysql_query($sql) or die(mysql_error());

 $result=$objDatabase->selectRecordsetArray("SELECT name, ra, decl FROM objects");

 while(list($key,$value)=each($result))
 { $objDatabase->execSQL("UPDATE objects SET Interstellarum = \"".$objAtlas->calculateAtlasPage('Interstellarum' ,$value['ra'],$value['decl'])."\" WHERE name = \"".$value['name']."\"");
 }

 print "Database update successful.\n";
?>

