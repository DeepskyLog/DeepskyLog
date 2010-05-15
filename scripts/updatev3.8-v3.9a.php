<?php


 require_once "../lib/setup/databaseInfo.php";
 require_once "../lib/database.php";
 require_once "../lib/reportlayouts.php";

  
 print "Database update will add the reports layout storage table.\n";
 $sql="DROP TABLE IF EXISTS reportlayouts";
 $run = mysql_query($sql) or die(mysql_error());
 $sql = "CREATE TABLE `reportlayouts` (
  `reportlayoutpk` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `observerid`     VARCHAR(255) NOT NULL DEFAULT   '',
  `reportname`     VARCHAR(255) NOT NULL DEFAULT   '',
  `reportlayout`   VARCHAR(255) NOT NULL DEFAULT   '',
  `fieldname`      VARCHAR(255) NOT NULL DEFAULT   '',
  `fieldline`      VARCHAR(15)  NOT NULL DEFAULT  '0',
  `fieldposition`  VARCHAR(15)  NOT NULL DEFAULT  '0',
  `fieldwidth`     VARCHAR(15)  NOT NULL DEFAULT  '0',
  `fieldheight`    VARCHAR(15)  NOT NULL DEFAULT '12',
  `fieldstyle`     VARCHAR(255) NOT NULL DEFAULT   '',
  `fieldbefore`    VARCHAR(255) NOT NULL DEFAULT   '',
  `fieldafter`     VARCHAR(255) NOT NULL DEFAULT   '',
  `fieldlegend`    VARCHAR(255) NOT NULL DEFAULT   '',
  primary key(`reportlayoutpk`)
  ) TYPE=MyISAM;";
 $run = mysql_query($sql) or die(mysql_error());
 
 print "Starting making default report profiles.\n";
 $loggedUserName="Deepskylog default";
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","Two column, constellation sort","pagesize"                        , '0',       'A4',  '0', '0', 'LAYOUTMETADATA','','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","Two column, constellation sort","pageorientation"                 , '0','landscape',  '0', '0', 'LAYOUTMETADATA','','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","Two column, constellation sort","startpagenumber"                 , '0',        '1',  '0', '0', 'LAYOUTMETADATA','','','');
 
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","Two column, constellation sort","top"                             , '0',      '550',  '0', '0', 'LAYOUTMETADATA','','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","Two column, constellation sort","header"                          , '0',      '570',  '0', '0', 'LAYOUTMETADATA','','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","Two column, constellation sort","xleft"                           , '0',       '20',  '0', '0', 'LAYOUTMETADATA','','','');
 
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","Two column, constellation sort","bottom"                          , '0',       '40',  '0', '0', 'LAYOUTMETADATA','','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","Two column, constellation sort","footer"                          , '0',       '10',  '0', '0', 'LAYOUTMETADATA','','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","Two column, constellation sort","xmid"                            , '0',      '431',  '0', '0', 'LAYOUTMETADATA','','','');
 
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","Two column, constellation sort","fontSizeText"                    , '0',        '8',  '0', '0', 'LAYOUTMETADATA','','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","Two column, constellation sort","SectionBarWidthbase"             , '0',      '400',  '0', '0', 'LAYOUTMETADATA','','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","Two column, constellation sort","fontSizeSection"                 , '0',       '10',  '0', '0', 'LAYOUTMETADATA','','','');
 
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","Two column, constellation sort","deltalineextra"                  , '0',        '4',  '0', '0', 'LAYOUTMETADATA','','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","Two column, constellation sort","sectionBarHeightextra"           , '0',        '4',  '0', '0', 'LAYOUTMETADATA','','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","Two column, constellation sort","deltalineSection"                , '0',        '2',  '0', '0', 'LAYOUTMETADATA','','','');
 
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","Two column, constellation sort","deltaobjectline"                 , '0',        '4',  '0', '0', 'LAYOUTMETADATA','','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","Two column, constellation sort","sectionbarspace"                 , '0',        '4',  '0', '0', 'LAYOUTMETADATA','','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","Two column, constellation sort","showelements"                    , '0',     'hetp',  '0', '0', 'LAYOUTMETADATA','','','');
 
 
 
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","Two column, constellation sort","objectseen"                      , '0',        '0', '30', '0',   '','','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","Two column, constellation sort","objectlastseen"                  , '0',       '30', '40', '0',   '','','','');

 $objReportLayout->saveLayoutField("ReportQueryOfObjects","Two column, constellation sort","showname"                        , '0',       '70', '85', '0', 'bi','','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","Two column, constellation sort","objectname"                      , '0',        '0',  '0', '0',   '','','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","Two column, constellation sort","altname"                         , '0',        '0',  '0', '0',   '','','','');
 
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","Two column, constellation sort","objecttype"                      , '0',      '155', '25', '0',   '','','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","Two column, constellation sort","objecttypefull"                  , '0',      '155',  '0', '0',   '','','','');
 
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","Two column, constellation sort","objectconstellation"             , '0',      '180', '20', '0',   '','','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","Two column, constellation sort","objectconstellationfull"         , '0',      '180',  '0', '0',   '','','','');
 
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","Two column, constellation sort","objectmagnitude"                 , '0',      '200', '17', '0',   '','','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","Two column, constellation sort","objectsurfacebrightness"         , '0',      '217', '18', '0',   '','','','');
 
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","Two column, constellation sort","objectradecl"                    , '0',      '235', '60', '0',   '','','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","Two column, constellation sort","objectra"                        , '0',        '0',  '0', '0',   '','','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","Two column, constellation sort","objectdecl"                      , '0',        '0',  '0', '0',   '','','','');
 
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","Two column, constellation sort","objectsizepa"                    , '0',      '295', '55', '0',   '','','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","Two column, constellation sort","objectdiam1"                     , '0',        '0',  '0', '0',   '','','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","Two column, constellation sort","objectdiam2"                     , '0',        '0',  '0', '0',   '','','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","Two column, constellation sort","objectsize"                      , '0',        '0',  '0', '0',   '','','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","Two column, constellation sort","objectpa"                        , '0',        '0',  '0', '0',   '','','','');

 $objReportLayout->saveLayoutField("ReportQueryOfObjects","Two column, constellation sort","objectuseratlaspage"             , '0',      '380', '20', '0', 'rb','','','');
 
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","Two column, constellation sort","objectdescription"               , '1',       '20','400', '0',  'i','','','');
  
  
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","Two column, constellation sort","objectcontrast"                  , '0',      '351', '17', '0',   '','','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","Two column, constellation sort","objectcontrastpopup"             , '0',        '0',  '0', '0',   '','','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","Two column, constellation sort","objectcontrasttype"              , '0',        '0',  '0', '0',   '','','','');
  
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","Two column, constellation sort","objectoptimalmagnification"      , '0',      '368', '17', '0',   '','','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","Two column, constellation sort","objectoptimalmagnificationvalue" , '0',        '0',  '0', '0',   '','','','');

 $objReportLayout->saveLayoutField("ReportQueryOfObjects","Two column, constellation sort","objectrise"                      , '0',        '0',  '0', '0',   '','','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","Two column, constellation sort","objectrisepopup"                 , '0',        '0',  '0', '0',   '','','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","Two column, constellation sort","objecttransit"                   , '0',        '0',  '0', '0',   '','','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","Two column, constellation sort","objecttransitpopup"              , '0',        '0',  '0', '0',   '','','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","Two column, constellation sort","objectset"                       , '0',        '0',  '0', '0',   '','','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","Two column, constellation sort","objectsetpopup"                  , '0',        '0',  '0', '0',   '','','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","Two column, constellation sort","objectbest"                      , '0',        '0',  '0', '0',   '','','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","Two column, constellation sort","objectmaxaltitude"               , '0',        '0',  '0', '0',   '','','','');
 $objReportLayout->saveLayoutField("ReportQueryOfObjects","Two column, constellation sort","objectmaxaltitudepopup"          , '0',        '0',  '0', '0',   '','','','');
 
  print "Database update successful.\n";

?>
