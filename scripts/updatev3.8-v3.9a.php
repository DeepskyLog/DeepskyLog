<?php


 require_once "../lib/setup/databaseInfo.php";
 require_once "../lib/database.php";
 require_once "../lib/reportlayouts.php";

  
 print "Database update will add the reports layout storage table.\n";
 $sql="DROP TABLE IF EXISTS reportlayouts";
 $run = mysql_query($sql) or die(mysql_error());
 $sql = "CREATE TABLE `reportlayouts` (
  `reportlayoutpk` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `observerid`     VARCHAR(255) NOT NULL DEFAULT '',
  `reportname`     VARCHAR(255) NOT NULL DEFAULT '',
  `reportlayout`   VARCHAR(255) NOT NULL DEFAULT '',
  `fieldname`      VARCHAR(255) NOT NULL DEFAULT '',
  `fieldline`      VARCHAR(15) NOT NULL DEFAULT '0',
  `fieldposition`  VARCHAR(15) NOT NULL DEFAULT '0',
  `fieldwidth`     VARCHAR(15) NOT NULL DEFAULT '0',
  `fieldheight`    VARCHAR(15) NOT NULL DEFAULT '12',
  `fieldstyle`     VARCHAR(255) NOT NULL DEFAULT '',
  `fieldbefore`    VARCHAR(255) NOT NULL DEFAULT '',
  `fieldafter`     VARCHAR(255) NOT NULL DEFAULT '',
  primary key(`reportlayoutpk`)
  ) TYPE=MyISAM;";
 $run = mysql_query($sql) or die(mysql_error());
 
 print "Starting making default report profiles.\n";
 $loggedUserName="Deepskylog";
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","default","pagesize"                        , '0',       'A4',  '0', '0', 'LAYOUTMETADATA','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","default","pageorientation"                 , '0','landscape',  '0', '0', 'LAYOUTMETADATA','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","default","startpagenumber"                 , '0',        '1',  '0', '0', 'LAYOUTMETADATA','','');
 
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","default","top"                             , '0',      '550',  '0', '0', 'LAYOUTMETADATA','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","default","header"                          , '0',      '570',  '0', '0', 'LAYOUTMETADATA','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","default","xleft"                           , '0',       '20',  '0', '0', 'LAYOUTMETADATA','','');
 
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","default","bottom"                          , '0',       '40',  '0', '0', 'LAYOUTMETADATA','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","default","footer"                          , '0',       '10',  '0', '0', 'LAYOUTMETADATA','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","default","xmid"                            , '0',      '431',  '0', '0', 'LAYOUTMETADATA','','');
 
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","default","fontSizeText"                    , '0',        '8',  '0', '0', 'LAYOUTMETADATA','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","default","SectionBarWidthbase"             , '0',      '400',  '0', '0', 'LAYOUTMETADATA','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","default","fontSizeSection"                 , '0',       '10',  '0', '0', 'LAYOUTMETADATA','','');
 
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","default","deltalineextra"                  , '0',        '4',  '0', '0', 'LAYOUTMETADATA','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","default","sectionBarHeightextra"           , '0',        '4',  '0', '0', 'LAYOUTMETADATA','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","default","deltalineSection"                , '0',        '2',  '0', '0', 'LAYOUTMETADATA','','');
 
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","default","deltaobjectline"                 , '0',        '4',  '0', '0', 'LAYOUTMETADATA','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","default","sectionbarspace"                 , '0',        '4',  '0', '0', 'LAYOUTMETADATA','','');
 
 
 
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","default","objectseen"                      , '0',        '0', '30', '0', '','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","default","objectlastseen"                  , '0',       '30', '40', '0', '','','');

 $objReportLayout->saveLayoutField("ReportQueryOfObjects","default","showname"                        , '0',       '70', '85', '0', 'bi','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","default","objectname"                      , '0',        '0',  '0', '0', '','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","default","altname"                         , '0',        '0',  '0', '0', '','','');
 
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","default","objecttype"                      , '0',      '155', '25', '0', '','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","default","objecttypefull"                  , '0',      '155',  '0', '0', '','','');
 
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","default","objectconstellation"             , '0',      '180', '20', '0', '','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","default","objectconstellationfull"         , '0',      '180',  '0', '0', '','','');
 
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","default","objectmagnitude"                 , '0',      '200', '17', '0', '','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","default","objectsurfacebrightness"         , '0',      '217', '18', '0', '','','');
 
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","default","objectradecl"                    , '0',      '235', '60', '0', '','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","default","objectra"                        , '0',        '0',  '0', '0', '','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","default","objectdecl"                      , '0',        '0',  '0', '0', '','','');
 
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","default","objectsizepa"                    , '0',      '295', '55', '0', '','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","default","objectdiam1"                     , '0',        '0',  '0', '0', '','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","default","objectdiam2"                     , '0',        '0',  '0', '0', '','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","default","objectsize"                      , '0',        '0',  '0', '0', '','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","default","objectpa"                        , '0',        '0',  '0', '0', '','','');

 $objReportLayout->saveLayoutField("ReportQueryOfObjects","default","objectuseratlaspage"             , '0',      '380', '20', '0', 'rb','','');
 
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","default","objectdescription"               , '1',       '20','400', '0', 'i','','');
  
  
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","default","objectcontrast"                  , '0',      '351', '17', '0', '','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","default","objectcontrastpopup"             , '0',        '0',  '0', '0', '','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","default","objectcontrasttype"              , '0',        '0',  '0', '0', '','','');
  
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","default","objectoptimalmagnification"      , '0',      '368', '17', '0', '','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","default","objectoptimalmagnificationvalue" , '0',        '0',  '0', '0', '','','');

 $objReportLayout->saveLayoutField("ReportQueryOfObjects","default","objectrise"                      , '0',        '0',  '0', '0', '','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","default","objectrisepopup"                 , '0',        '0',  '0', '0', '','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","default","objecttransit"                   , '0',        '0',  '0', '0', '','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","default","objecttransitpopup"              , '0',        '0',  '0', '0', '','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","default","objectset"                       , '0',        '0',  '0', '0', '','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","default","objectsetpopup"                  , '0',        '0',  '0', '0', '','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","default","objectbest"                      , '0',        '0',  '0', '0', '','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","default","objectbestpopup"                 , '0',        '0',  '0', '0', '','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","default","objectmaxaltitude"               , '0',        '0',  '0', '0', '','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","default","objectmaxaltitudepopup"          , '0',        '0',  '0', '0', '','','');
 
  print "Database update successful.\n";

?>
