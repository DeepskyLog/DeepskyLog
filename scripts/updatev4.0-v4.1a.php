<?php
 $inIndex=true;
 require_once "../lib/setup/databaseInfo.php";
 require_once "../lib/database.php";
 $objDatabase=new Database();
 print "Database update will add the highest altitudes possibilities to the report layouts of the users.\n\n";

 $sql="SELECT DISTINCT observerid, reportname, reportlayout FROM reportlayouts";
 $results=$objDatabase->selectRecordsetArray($sql);
 while(list($key,$value)=each($results))
 { echo "Writing data for ".$value['observerid'].' '.$value['reportname'].' '.$value['reportlayout']."...\n\n";
   $sql="INSERT INTO reportlayouts(observerid,reportname,reportlayout,fieldname,fieldline,fieldposition,fieldwidth,fieldheight,fieldstyle,fieldbefore,fieldafter,fieldlegend)
                            VALUES('".$value['observerid']."','".$value['reportname']."','".$value['reportlayout']."','objectmaxaltstarttext',3,20,50,'','','','','');";
   $objDatabase->execSQL($sql);
   $sql="INSERT INTO reportlayouts(observerid,reportname,reportlayout,fieldname,fieldline,fieldposition,fieldwidth,fieldheight,fieldstyle,fieldbefore,fieldafter,fieldlegend)
                            VALUES('".$value['observerid']."','".$value['reportname']."','".$value['reportlayout']."','objectmaxaltmidtext',3,70,50,'','','','','');";
   $objDatabase->execSQL($sql);
   $sql="INSERT INTO reportlayouts(observerid,reportname,reportlayout,fieldname,fieldline,fieldposition,fieldwidth,fieldheight,fieldstyle,fieldbefore,fieldafter,fieldlegend)
                            VALUES('".$value['observerid']."','".$value['reportname']."','".$value['reportlayout']."','objectmaxaltendtext',3,120,50,'','','','','');";
   $objDatabase->execSQL($sql);
   $sql="INSERT INTO reportlayouts(observerid,reportname,reportlayout,fieldname,fieldline,fieldposition,fieldwidth,fieldheight,fieldstyle,fieldbefore,fieldafter,fieldlegend)
                            VALUES('".$value['observerid']."','".$value['reportname']."','".$value['reportlayout']."','objectmaxalt',3,170,50,'','','','','');";
   $objDatabase->execSQL($sql);
 }
 print "Database update successful.\n";

?>
