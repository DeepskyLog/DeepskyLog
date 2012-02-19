<?php
 $inIndex=true;
 require_once "../lib/setup/databaseInfo.php";
 require_once "../lib/database.php";
 require_once "../lib/atlasses.php";
 require_once "../lib/objects.php";
 require_once "../lib/setup/language/nl/lang_main.php";
 $objDatabase=new Database();
 
 $objAtlas=new Atlasses;
 
 print "Database update will add the deepskylog atlasses page numbers for the DSL objects.\n";
 $result=$objDatabase->selectRecordsetArray("SELECT name, ra, decl FROM objects");

 while(list($key,$value)=each($result))
 { $objDatabase->execSQL("UPDATE objects SET DSLDL       = \"".$objAtlas->calculateAtlasPage('DSLDL'      ,$value['ra'],$value['decl'])."\" WHERE name = \"".$value['name']."\"");
   $objDatabase->execSQL("UPDATE objects SET DSLDP       = \"".$objAtlas->calculateAtlasPage('DSLDP'      ,$value['ra'],$value['decl'])."\" WHERE name = \"".$value['name']."\"");
   $objDatabase->execSQL("UPDATE objects SET DSLLL       = \"".$objAtlas->calculateAtlasPage('DSLLL'      ,$value['ra'],$value['decl'])."\" WHERE name = \"".$value['name']."\"");
   $objDatabase->execSQL("UPDATE objects SET DSLLP       = \"".$objAtlas->calculateAtlasPage('DSLLP'      ,$value['ra'],$value['decl'])."\" WHERE name = \"".$value['name']."\"");
   $objDatabase->execSQL("UPDATE objects SET DSLOL       = \"".$objAtlas->calculateAtlasPage('DSLOL'      ,$value['ra'],$value['decl'])."\" WHERE name = \"".$value['name']."\"");
   $objDatabase->execSQL("UPDATE objects SET DSLOP       = \"".$objAtlas->calculateAtlasPage('DSLOP'      ,$value['ra'],$value['decl'])."\" WHERE name = \"".$value['name']."\"");
 }
 
 print "Database update successful.\n";
?>