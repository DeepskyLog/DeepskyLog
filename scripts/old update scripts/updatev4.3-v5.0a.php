<?php
 $inIndex=true;
 require_once "../lib/setup/databaseInfo.php";
 require_once "../lib/database.php";
 require_once "../lib/observers.php";
 require_once "../lib/cometobservations.php";
 require_once "../lib/accomplishments.php";
 require_once "../lib/messages.php";

 date_default_timezone_set('UTC');

 $objDatabase=new Database();
 $objObserver=new Observers();
 $objCometObservation=new CometObservations();
 $objAccomplishments=new Accomplishments();
 $objMessages=new Messages();

 print "Database update add a hasDrawing field to cometobservations.<br />\n";
 $sql = "ALTER TABLE cometobservations ADD COLUMN hasDrawing INT(1) NOT NULL DEFAULT 0";
 $run = $objDatabase->execSQL($sql);
 
 $upload_dir = '../comets/cometdrawings';
 $dir = opendir($upload_dir);
 while (FALSE !== ($file = readdir($dir))) {
   if((!(("."==$file)OR(".."==$file)OR(".svn"==$file)))
         && (strpos($file,'resized')==0)) {
                 $objCometObservation->setHasDrawing(substr($file,0,strpos($file,'.jpg')));
   }
 }
 print "Database update add an accomplishments table and fill it for all users.<br />\n";

 $sql ="DROP TABLE IF EXISTS accomplishments";
 $run = $objDatabase->execSQL($sql);
 $sql = "CREATE TABLE accomplishments (
             observer                                   VARCHAR(255)            NOT NULL DEFAULT '',
             messierBronze                              INT(1)                  NOT NULL DEFAULT 0,
             messierSilver                              INT(1)                  NOT NULL DEFAULT 0,
             messierGold                                INT(1)                  NOT NULL DEFAULT 0,
             messierDrawingsBronze                      INT(1)                  NOT NULL DEFAULT 0,
             messierDrawingsSilver                      INT(1)                  NOT NULL DEFAULT 0,
             messierDrawingsGold                        INT(1)                  NOT NULL DEFAULT 0,
             caldwellBronze                             INT(1)                  NOT NULL DEFAULT 0,
             caldwellSilver                             INT(1)                  NOT NULL DEFAULT 0,
             caldwellGold                               INT(1)                  NOT NULL DEFAULT 0,
             caldwellDrawingsBronze                     INT(1)                  NOT NULL DEFAULT 0,
             caldwellDrawingsSilver                     INT(1)                  NOT NULL DEFAULT 0,
             caldwelldrawingsGold                       INT(1)                  NOT NULL DEFAULT 0,
             herschelBronze                             INT(1)                  NOT NULL DEFAULT 0,
             herschelSilver                             INT(1)                  NOT NULL DEFAULT 0,
             herschelGold                               INT(1)                  NOT NULL DEFAULT 0,
             herschelDiamond                            INT(1)                  NOT NULL DEFAULT 0,
             herschelPlatina                            INT(1)                  NOT NULL DEFAULT 0,
             herschelDrawingsBronze                     INT(1)                  NOT NULL DEFAULT 0,
             herschelDrawingsSilver                     INT(1)                  NOT NULL DEFAULT 0,
             herschelDrawingsGold                       INT(1)                  NOT NULL DEFAULT 0,
             herschelDrawingsDiamond                    INT(1)                  NOT NULL DEFAULT 0,
             herschelDrawingsPlatina                    INT(1)                  NOT NULL DEFAULT 0,
             herschelIIBronze                           INT(1)                  NOT NULL DEFAULT 0,
             herschelIISilver                           INT(1)                  NOT NULL DEFAULT 0,
             herschelIIGold                             INT(1)                  NOT NULL DEFAULT 0,
             herschelIIDiamond                          INT(1)                  NOT NULL DEFAULT 0,
             herschelIIPlatina                          INT(1)                  NOT NULL DEFAULT 0,
             herschelIIDrawingsBronze                   INT(1)                  NOT NULL DEFAULT 0,
             herschelIIDrawingsSilver                   INT(1)                  NOT NULL DEFAULT 0,
             herschelIIDrawingsGold                     INT(1)                  NOT NULL DEFAULT 0,
             herschelIIDrawingsDiamond                  INT(1)                  NOT NULL DEFAULT 0,
             herschelIIDrawingsPlatina                  INT(1)                  NOT NULL DEFAULT 0,
             drawingsNewbie                             INT(1)                  NOT NULL DEFAULT 0,
             drawingsRookie                             INT(1)                  NOT NULL DEFAULT 0,
             drawingsBeginner                           INT(1)                  NOT NULL DEFAULT 0,
             drawingsTalented                           INT(1)                  NOT NULL DEFAULT 0,
             drawingsSkilled                            INT(1)                  NOT NULL DEFAULT 0,
             drawingsIntermediate                       INT(1)                  NOT NULL DEFAULT 0,
             drawingsExperienced                        INT(1)                  NOT NULL DEFAULT 0,
             drawingsAdvanced                           INT(1)                  NOT NULL DEFAULT 0,
             drawingsSenior                             INT(1)                  NOT NULL DEFAULT 0,
             drawingsExpert                             INT(1)                  NOT NULL DEFAULT 0,
             cometObservationsNewbie                    INT(1)                  NOT NULL DEFAULT 0,
             cometObservationsRookie                    INT(1)                  NOT NULL DEFAULT 0,
             cometObservationsBeginner                  INT(1)                  NOT NULL DEFAULT 0,
             cometObservationsTalented                  INT(1)                  NOT NULL DEFAULT 0,
             cometObservationsSkilled                   INT(1)                  NOT NULL DEFAULT 0,
             cometObservationsIntermediate              INT(1)                  NOT NULL DEFAULT 0,
             cometObservationsExperienced               INT(1)                  NOT NULL DEFAULT 0,
             cometObservationsAdvanced                  INT(1)                  NOT NULL DEFAULT 0,
             cometObservationsSenior                    INT(1)                  NOT NULL DEFAULT 0,
             cometObservationsExpert                    INT(1)                  NOT NULL DEFAULT 0,
             cometsObservedNewbie                       INT(1)                  NOT NULL DEFAULT 0,
             cometsObservedRookie                       INT(1)                  NOT NULL DEFAULT 0,
             cometsObservedBeginner                     INT(1)                  NOT NULL DEFAULT 0,
             cometsObservedTalented                     INT(1)                  NOT NULL DEFAULT 0,
             cometsObservedSkilled                      INT(1)                  NOT NULL DEFAULT 0,
             cometsObservedIntermediate                 INT(1)                  NOT NULL DEFAULT 0,
             cometsObservedExperienced                  INT(1)                  NOT NULL DEFAULT 0,
             cometsObservedAdvanced                     INT(1)                  NOT NULL DEFAULT 0,
             cometsObservedSenior                       INT(1)                  NOT NULL DEFAULT 0,
             cometsObservedExpert                       INT(1)                  NOT NULL DEFAULT 0,
             cometDrawingsNewbie                        INT(1)                  NOT NULL DEFAULT 0,
             cometDrawingsRookie                        INT(1)                  NOT NULL DEFAULT 0,
             cometDrawingsBeginner                      INT(1)                  NOT NULL DEFAULT 0,
             cometDrawingsTalented                      INT(1)                  NOT NULL DEFAULT 0,
             cometDrawingsSkilled                       INT(1)                  NOT NULL DEFAULT 0,
             cometDrawingsIntermediate                  INT(1)                  NOT NULL DEFAULT 0,
             cometDrawingsExperienced                   INT(1)                  NOT NULL DEFAULT 0,
             cometDrawingsAdvanced                      INT(1)                  NOT NULL DEFAULT 0,
             cometDrawingsSenior                        INT(1)                  NOT NULL DEFAULT 0,
             cometDrawingsExpert                        INT(1)                  NOT NULL DEFAULT 0,
             openClusterNewbie                          INT(1)                  NOT NULL DEFAULT 0,
             openClusterRookie                          INT(1)                  NOT NULL DEFAULT 0,
             openClusterBeginner                        INT(1)                  NOT NULL DEFAULT 0,
             openClusterTalented                        INT(1)                  NOT NULL DEFAULT 0,
             openClusterSkilled                         INT(1)                  NOT NULL DEFAULT 0,
             openClusterIntermediate                    INT(1)                  NOT NULL DEFAULT 0,
             openClusterExperienced                     INT(1)                  NOT NULL DEFAULT 0,
             openClusterAdvanced                        INT(1)                  NOT NULL DEFAULT 0,
             openClusterSenior                          INT(1)                  NOT NULL DEFAULT 0,
             openClusterExpert                          INT(1)                  NOT NULL DEFAULT 0,
             openClusterDrawingsNewbie                  INT(1)                  NOT NULL DEFAULT 0,
             openClusterDrawingsRookie                  INT(1)                  NOT NULL DEFAULT 0,
             openClusterDrawingsBeginner                INT(1)                  NOT NULL DEFAULT 0,
             openClusterDrawingsTalented                INT(1)                  NOT NULL DEFAULT 0,
             openClusterDrawingsSkilled                 INT(1)                  NOT NULL DEFAULT 0,
             openClusterDrawingsIntermediate            INT(1)                  NOT NULL DEFAULT 0,
             openClusterDrawingsExperienced             INT(1)                  NOT NULL DEFAULT 0,
             openClusterDrawingsAdvanced                INT(1)                  NOT NULL DEFAULT 0,
             openClusterDrawingsSenior                  INT(1)                  NOT NULL DEFAULT 0,
             openClusterDrawingsExpert                  INT(1)                  NOT NULL DEFAULT 0,
             globularClusterNewbie                      INT(1)                  NOT NULL DEFAULT 0,
             globularClusterRookie                      INT(1)                  NOT NULL DEFAULT 0,
             globularClusterBeginner                    INT(1)                  NOT NULL DEFAULT 0,
             globularClusterTalented                    INT(1)                  NOT NULL DEFAULT 0,
             globularClusterSkilled                     INT(1)                  NOT NULL DEFAULT 0,
             globularClusterIntermediate                INT(1)                  NOT NULL DEFAULT 0,
             globularClusterExperienced                 INT(1)                  NOT NULL DEFAULT 0,
             globularClusterAdvanced                    INT(1)                  NOT NULL DEFAULT 0,
             globularClusterSenior                      INT(1)                  NOT NULL DEFAULT 0,
             globularClusterExpert                      INT(1)                  NOT NULL DEFAULT 0,
             globularClusterDrawingsNewbie              INT(1)                  NOT NULL DEFAULT 0,
             globularClusterDrawingsRookie              INT(1)                  NOT NULL DEFAULT 0,
             globularClusterDrawingsBeginner            INT(1)                  NOT NULL DEFAULT 0,
             globularClusterDrawingsTalented            INT(1)                  NOT NULL DEFAULT 0,
             globularClusterDrawingsSkilled             INT(1)                  NOT NULL DEFAULT 0,
             globularClusterDrawingsIntermediate        INT(1)                  NOT NULL DEFAULT 0,
             globularClusterDrawingsExperienced         INT(1)                  NOT NULL DEFAULT 0,
             globularClusterDrawingsAdvanced            INT(1)                  NOT NULL DEFAULT 0,
             globularClusterDrawingsSenior              INT(1)                  NOT NULL DEFAULT 0,
             globularClusterDrawingsExpert              INT(1)                  NOT NULL DEFAULT 0,
             planetaryNebulaNewbie                      INT(1)                  NOT NULL DEFAULT 0,
             planetaryNebulaRookie                      INT(1)                  NOT NULL DEFAULT 0,
             planetaryNebulaBeginner                    INT(1)                  NOT NULL DEFAULT 0,
             planetaryNebulaTalented                    INT(1)                  NOT NULL DEFAULT 0,
             planetaryNebulaSkilled                     INT(1)                  NOT NULL DEFAULT 0,
             planetaryNebulaIntermediate                INT(1)                  NOT NULL DEFAULT 0,
             planetaryNebulaExperienced                 INT(1)                  NOT NULL DEFAULT 0,
             planetaryNebulaAdvanced                    INT(1)                  NOT NULL DEFAULT 0,
             planetaryNebulaSenior                      INT(1)                  NOT NULL DEFAULT 0,
             planetaryNebulaExpert                      INT(1)                  NOT NULL DEFAULT 0,
             planetaryNebulaDrawingsNewbie              INT(1)                  NOT NULL DEFAULT 0,
             planetaryNebulaDrawingsRookie              INT(1)                  NOT NULL DEFAULT 0,
             planetaryNebulaDrawingsBeginner            INT(1)                  NOT NULL DEFAULT 0,
             planetaryNebulaDrawingsTalented            INT(1)                  NOT NULL DEFAULT 0,
             planetaryNebulaDrawingsSkilled             INT(1)                  NOT NULL DEFAULT 0,
             planetaryNebulaDrawingsIntermediate        INT(1)                  NOT NULL DEFAULT 0,
             planetaryNebulaDrawingsExperienced         INT(1)                  NOT NULL DEFAULT 0,
             planetaryNebulaDrawingsAdvanced            INT(1)                  NOT NULL DEFAULT 0,
             planetaryNebulaDrawingsSenior              INT(1)                  NOT NULL DEFAULT 0,
             planetaryNebulaDrawingsExpert              INT(1)                  NOT NULL DEFAULT 0,
             galaxyNewbie                               INT(1)                  NOT NULL DEFAULT 0,
             galaxyRookie                               INT(1)                  NOT NULL DEFAULT 0,
             galaxyBeginner                             INT(1)                  NOT NULL DEFAULT 0,
             galaxyTalented                             INT(1)                  NOT NULL DEFAULT 0,
             galaxySkilled                              INT(1)                  NOT NULL DEFAULT 0,
             galaxyIntermediate                         INT(1)                  NOT NULL DEFAULT 0,
             galaxyExperienced                          INT(1)                  NOT NULL DEFAULT 0,
             galaxyAdvanced                             INT(1)                  NOT NULL DEFAULT 0,
             galaxySenior                               INT(1)                  NOT NULL DEFAULT 0,
             galaxyExpert                               INT(1)                  NOT NULL DEFAULT 0,
             galaxyDrawingsNewbie                       INT(1)                  NOT NULL DEFAULT 0,
             galaxyDrawingsRookie                       INT(1)                  NOT NULL DEFAULT 0,
             galaxyDrawingsBeginner                     INT(1)                  NOT NULL DEFAULT 0,
             galaxyDrawingsTalented                     INT(1)                  NOT NULL DEFAULT 0,
             galaxyDrawingsSkilled                      INT(1)                  NOT NULL DEFAULT 0,
             galaxyDrawingsIntermediate                 INT(1)                  NOT NULL DEFAULT 0,
             galaxyDrawingsExperienced                  INT(1)                  NOT NULL DEFAULT 0,
             galaxyDrawingsAdvanced                     INT(1)                  NOT NULL DEFAULT 0,
             galaxyDrawingsSenior                       INT(1)                  NOT NULL DEFAULT 0,
             galaxyDrawingsExpert                       INT(1)                  NOT NULL DEFAULT 0,
             nebulaNewbie                               INT(1)                  NOT NULL DEFAULT 0,
             nebulaRookie                               INT(1)                  NOT NULL DEFAULT 0,
             nebulaBeginner                             INT(1)                  NOT NULL DEFAULT 0,
             nebulaTalented                             INT(1)                  NOT NULL DEFAULT 0,
             nebulaSkilled                              INT(1)                  NOT NULL DEFAULT 0,
             nebulaIntermediate                         INT(1)                  NOT NULL DEFAULT 0,
             nebulaExperienced                          INT(1)                  NOT NULL DEFAULT 0,
             nebulaAdvanced                             INT(1)                  NOT NULL DEFAULT 0,
             nebulaSenior                               INT(1)                  NOT NULL DEFAULT 0,
             nebulaExpert                               INT(1)                  NOT NULL DEFAULT 0,
             nebulaDrawingsNewbie                       INT(1)                  NOT NULL DEFAULT 0,
             nebulaDrawingsRookie                       INT(1)                  NOT NULL DEFAULT 0,
             nebulaDrawingsBeginner                     INT(1)                  NOT NULL DEFAULT 0,
             nebulaDrawingsTalented                     INT(1)                  NOT NULL DEFAULT 0,
             nebulaDrawingsSkilled                      INT(1)                  NOT NULL DEFAULT 0,
             nebulaDrawingsIntermediate                 INT(1)                  NOT NULL DEFAULT 0,
             nebulaDrawingsExperienced                  INT(1)                  NOT NULL DEFAULT 0,
             nebulaDrawingsAdvanced                     INT(1)                  NOT NULL DEFAULT 0,
             nebulaDrawingsSenior                       INT(1)                  NOT NULL DEFAULT 0,
             nebulaDrawingsExpert                       INT(1)                  NOT NULL DEFAULT 0,
             objectsNewbie                              INT(1)                  NOT NULL DEFAULT 0,
             objectsRookie                              INT(1)                  NOT NULL DEFAULT 0,
             objectsBeginner                            INT(1)                  NOT NULL DEFAULT 0,
             objectsTalented                            INT(1)                  NOT NULL DEFAULT 0,
             objectsSkilled                             INT(1)                  NOT NULL DEFAULT 0,
             objectsIntermediate                        INT(1)                  NOT NULL DEFAULT 0,
             objectsExperienced                         INT(1)                  NOT NULL DEFAULT 0,
             objectsAdvanced                            INT(1)                  NOT NULL DEFAULT 0,
             objectsSenior                              INT(1)                  NOT NULL DEFAULT 0,
             objectsExpert                              INT(1)                  NOT NULL DEFAULT 0,
             objectsDrawingsNewbie                      INT(1)                  NOT NULL DEFAULT 0,
             objectsDrawingsRookie                      INT(1)                  NOT NULL DEFAULT 0,
             objectsDrawingsBeginner                    INT(1)                  NOT NULL DEFAULT 0,
             objectsDrawingsTalented                    INT(1)                  NOT NULL DEFAULT 0,
             objectsDrawingsSkilled                     INT(1)                  NOT NULL DEFAULT 0,
             objectsDrawingsIntermediate                INT(1)                  NOT NULL DEFAULT 0,
             objectsDrawingsExperienced                 INT(1)                  NOT NULL DEFAULT 0,
             objectsDrawingsAdvanced                    INT(1)                  NOT NULL DEFAULT 0,
             objectsDrawingsSenior                      INT(1)                  NOT NULL DEFAULT 0,
             objectsDrawingsExpert                      INT(1)                  NOT NULL DEFAULT 0,
             PRIMARY KEY (observer)             
             )";
 $run = $objDatabase->execSQL($sql);

 // We loop over all observers
 $observers = $objObserver->getSortedObservers("id");

 foreach ($observers as $key => $value) {
    $sql = "INSERT INTO accomplishments (observer, messierBronze, messierSilver, messierGold, messierDrawingsBronze, messierDrawingsSilver, messierDrawingsGold, caldwellBronze, caldwellSilver, caldwellGold, caldwellDrawingsBronze, caldwellDrawingsSilver, caldwelldrawingsGold, herschelBronze, herschelSilver, herschelGold, herschelDiamond, herschelPlatina, herschelDrawingsBronze, herschelDrawingsSilver, herschelDrawingsGold, herschelDrawingsDiamond, herschelDrawingsPlatina, herschelIIBronze, herschelIISilver, herschelIIGold, herschelIIDiamond, herschelIIPlatina, herschelIIDrawingsBronze, herschelIIDrawingsSilver, herschelIIDrawingsGold, herschelIIDrawingsDiamond, herschelIIDrawingsPlatina, drawingsNewbie, drawingsRookie, drawingsBeginner, drawingsTalented, drawingsSkilled, drawingsIntermediate, drawingsExperienced, drawingsAdvanced, drawingsSenior, drawingsExpert, cometObservationsNewbie, cometObservationsRookie, cometObservationsBeginner, cometObservationsTalented, cometObservationsSkilled, cometObservationsIntermediate, cometObservationsExperienced, cometObservationsAdvanced, cometObservationsSenior, cometObservationsExpert, cometsObservedNewbie, cometsObservedRookie, cometsObservedBeginner, cometsObservedTalented, cometsObservedSkilled, cometsObservedIntermediate, cometsObservedExperienced, cometsObservedAdvanced, cometsObservedSenior, cometsObservedExpert, cometDrawingsNewbie, cometDrawingsRookie, cometDrawingsBeginner, cometDrawingsTalented, cometDrawingsSkilled, cometDrawingsIntermediate, cometDrawingsExperienced, cometDrawingsAdvanced, cometDrawingsSenior, cometDrawingsExpert, openClusterNewbie, openClusterRookie, openClusterBeginner, openClusterTalented, openClusterSkilled, openClusterIntermediate, openClusterExperienced, openClusterAdvanced, openClusterSenior, openClusterExpert, openClusterDrawingsNewbie, openClusterDrawingsRookie, openClusterDrawingsBeginner, openClusterDrawingsTalented, openClusterDrawingsSkilled, openClusterDrawingsIntermediate, openClusterDrawingsExperienced, openClusterDrawingsAdvanced, openClusterDrawingsSenior, openClusterDrawingsExpert, globularClusterNewbie, globularClusterRookie, globularClusterBeginner, globularClusterTalented, globularClusterSkilled, globularClusterIntermediate, globularClusterExperienced, globularClusterAdvanced, globularClusterSenior, globularClusterExpert, globularClusterDrawingsNewbie, globularClusterDrawingsRookie, globularClusterDrawingsBeginner, globularClusterDrawingsTalented, globularClusterDrawingsSkilled, globularClusterDrawingsIntermediate, globularClusterDrawingsExperienced, globularClusterDrawingsAdvanced, globularClusterDrawingsSenior, globularClusterDrawingsExpert, planetaryNebulaNewbie, planetaryNebulaRookie, planetaryNebulaBeginner, planetaryNebulaTalented, planetaryNebulaSkilled, planetaryNebulaIntermediate, planetaryNebulaExperienced, planetaryNebulaAdvanced, planetaryNebulaSenior, planetaryNebulaExpert, planetaryNebulaDrawingsNewbie, planetaryNebulaDrawingsRookie, planetaryNebulaDrawingsBeginner, planetaryNebulaDrawingsTalented, planetaryNebulaDrawingsSkilled, planetaryNebulaDrawingsIntermediate, planetaryNebulaDrawingsExperienced, planetaryNebulaDrawingsAdvanced, planetaryNebulaDrawingsSenior, planetaryNebulaDrawingsExpert, galaxyNewbie, galaxyRookie, galaxyBeginner, galaxyTalented, galaxySkilled, galaxyIntermediate, galaxyExperienced, galaxyAdvanced, galaxySenior, galaxyExpert, galaxyDrawingsNewbie, galaxyDrawingsRookie, galaxyDrawingsBeginner, galaxyDrawingsTalented, galaxyDrawingsSkilled, galaxyDrawingsIntermediate, galaxyDrawingsExperienced, galaxyDrawingsAdvanced, galaxyDrawingsSenior, galaxyDrawingsExpert, nebulaNewbie, nebulaRookie, nebulaBeginner, nebulaTalented, nebulaSkilled, nebulaIntermediate, nebulaExperienced, nebulaAdvanced, nebulaSenior, nebulaExpert, nebulaDrawingsNewbie, nebulaDrawingsRookie, nebulaDrawingsBeginner, nebulaDrawingsTalented, nebulaDrawingsSkilled, nebulaDrawingsIntermediate, nebulaDrawingsExperienced, nebulaDrawingsAdvanced, nebulaDrawingsSenior, nebulaDrawingsExpert, objectsNewbie, objectsRookie, objectsBeginner, objectsTalented, objectsSkilled, objectsIntermediate, objectsExperienced, objectsAdvanced, objectsSenior, objectsExpert, objectsDrawingsNewbie, objectsDrawingsRookie, objectsDrawingsBeginner, objectsDrawingsTalented, objectsDrawingsSkilled, objectsDrawingsIntermediate, objectsDrawingsExperienced, objectsDrawingsAdvanced, objectsDrawingsSenior, objectsDrawingsExpert) " .
    		"VALUES (\"". $value ."\", " . 
    		$objAccomplishments->calculateMessier($value)[0] .", " . 
    		$objAccomplishments->calculateMessier($value)[1] .", " . 
    		$objAccomplishments->calculateMessier($value)[2] . ", " .
    		$objAccomplishments->calculateMessierDrawings($value)[0] .", " . 
    		$objAccomplishments->calculateMessierDrawings($value)[1] .", " . 
    		$objAccomplishments->calculateMessierDrawings($value)[2] . ", " .
    		$objAccomplishments->calculateCaldwell($value)[0] .", " . 
    		$objAccomplishments->calculateCaldwell($value)[1] .", " . 
    		$objAccomplishments->calculateCaldwell($value)[2] .", " . 
    		$objAccomplishments->calculateCaldwellDrawings($value)[0] .", " . 
    		$objAccomplishments->calculateCaldwellDrawings($value)[1] .", " . 
    		$objAccomplishments->calculateCaldwellDrawings($value)[2] .", " . 
    		$objAccomplishments->calculateHerschel($value)[0] .", " . 
    		$objAccomplishments->calculateHerschel($value)[1] .", " . 
    		$objAccomplishments->calculateHerschel($value)[2] .", " . 
    		$objAccomplishments->calculateHerschel($value)[3] .", " . 
    		$objAccomplishments->calculateHerschel($value)[4] .", " . 
    		$objAccomplishments->calculateHerschelDrawings($value)[0] .", " . 
    		$objAccomplishments->calculateHerschelDrawings($value)[1] .", " . 
    		$objAccomplishments->calculateHerschelDrawings($value)[2] .", " . 
    		$objAccomplishments->calculateHerschelDrawings($value)[3] .", " . 
    		$objAccomplishments->calculateHerschelDrawings($value)[4] .", " . 
    		$objAccomplishments->calculateHerschelII($value)[0] .", " . 
    		$objAccomplishments->calculateHerschelII($value)[1] .", " . 
    		$objAccomplishments->calculateHerschelII($value)[2] .", " . 
    		$objAccomplishments->calculateHerschelII($value)[3] .", " . 
    		$objAccomplishments->calculateHerschelII($value)[4] .", " . 
    		$objAccomplishments->calculateHerschelIIDrawings($value)[0] .", " . 
    		$objAccomplishments->calculateHerschelIIDrawings($value)[1] .", " . 
    		$objAccomplishments->calculateHerschelIIDrawings($value)[2] .", " . 
    		$objAccomplishments->calculateHerschelIIDrawings($value)[3] .", " . 
    		$objAccomplishments->calculateHerschelIIDrawings($value)[4] .", " . 
    		$objAccomplishments->calculateDrawings($value)[0] .", " . 
    		$objAccomplishments->calculateDrawings($value)[1] .", " . 
    		$objAccomplishments->calculateDrawings($value)[2] .", " . 
    		$objAccomplishments->calculateDrawings($value)[3] .", " . 
    		$objAccomplishments->calculateDrawings($value)[4] .", " . 
    		$objAccomplishments->calculateDrawings($value)[5] .", " . 
    		$objAccomplishments->calculateDrawings($value)[6] .", " . 
    		$objAccomplishments->calculateDrawings($value)[7] .", " . 
    		$objAccomplishments->calculateDrawings($value)[8] .", " . 
    		$objAccomplishments->calculateDrawings($value)[9] .", " .
    		$objAccomplishments->calculateCometObservations($value)[0] .", " .
    		$objAccomplishments->calculateCometObservations($value)[1] .", " .
    		$objAccomplishments->calculateCometObservations($value)[2] .", " .
    		$objAccomplishments->calculateCometObservations($value)[3] .", " .
    		$objAccomplishments->calculateCometObservations($value)[4] .", " .
    		$objAccomplishments->calculateCometObservations($value)[5] .", " .
    		$objAccomplishments->calculateCometObservations($value)[6] .", " .
    		$objAccomplishments->calculateCometObservations($value)[7] .", " .
    		$objAccomplishments->calculateCometObservations($value)[8] .", " .
    		$objAccomplishments->calculateCometObservations($value)[9] .", " .
    		$objAccomplishments->calculateCometsObserved($value)[0] .", " .
    		$objAccomplishments->calculateCometsObserved($value)[1] .", " .
    		$objAccomplishments->calculateCometsObserved($value)[2] .", " .
    		$objAccomplishments->calculateCometsObserved($value)[3] .", " .
    		$objAccomplishments->calculateCometsObserved($value)[4] .", " .
    		$objAccomplishments->calculateCometsObserved($value)[5] .", " .
    		$objAccomplishments->calculateCometsObserved($value)[6] .", " .
    		$objAccomplishments->calculateCometsObserved($value)[7] .", " .
    		$objAccomplishments->calculateCometsObserved($value)[8] .", " .
    		$objAccomplishments->calculateCometsObserved($value)[9] .", " .
    		$objAccomplishments->calculateCometDrawings($value)[0] .", " .
    		$objAccomplishments->calculateCometDrawings($value)[1] .", " .
    		$objAccomplishments->calculateCometDrawings($value)[2] .", " .
    		$objAccomplishments->calculateCometDrawings($value)[3] .", " .
    		$objAccomplishments->calculateCometDrawings($value)[4] .", " .
    		$objAccomplishments->calculateCometDrawings($value)[5] .", " .
    		$objAccomplishments->calculateCometDrawings($value)[6] .", " .
    		$objAccomplishments->calculateCometDrawings($value)[7] .", " .
    		$objAccomplishments->calculateCometDrawings($value)[8] .", " .
    		$objAccomplishments->calculateCometDrawings($value)[9] .", " .
    		$objAccomplishments->calculateOpenClusters($value)[0] .", " .
    		$objAccomplishments->calculateOpenClusters($value)[1] .", " .
    		$objAccomplishments->calculateOpenClusters($value)[2] .", " .
    		$objAccomplishments->calculateOpenClusters($value)[3] .", " .
    		$objAccomplishments->calculateOpenClusters($value)[4] .", " .
    		$objAccomplishments->calculateOpenClusters($value)[5] .", " .
    		$objAccomplishments->calculateOpenClusters($value)[6] .", " .
    		$objAccomplishments->calculateOpenClusters($value)[7] .", " .
    		$objAccomplishments->calculateOpenClusters($value)[8] .", " .
    		$objAccomplishments->calculateOpenClusters($value)[9] .", " .
    		$objAccomplishments->calculateOpenClusterDrawings($value)[0] .", " .
    		$objAccomplishments->calculateOpenClusterDrawings($value)[1] .", " .
    		$objAccomplishments->calculateOpenClusterDrawings($value)[2] .", " .
    		$objAccomplishments->calculateOpenClusterDrawings($value)[3] .", " .
    		$objAccomplishments->calculateOpenClusterDrawings($value)[4] .", " .
    		$objAccomplishments->calculateOpenClusterDrawings($value)[5] .", " .
    		$objAccomplishments->calculateOpenClusterDrawings($value)[6] .", " .
    		$objAccomplishments->calculateOpenClusterDrawings($value)[7] .", " .
    		$objAccomplishments->calculateOpenClusterDrawings($value)[8] .", " .
    		$objAccomplishments->calculateOpenClusterDrawings($value)[9] .", " .
    		$objAccomplishments->calculateGlobularClusters($value)[0] .", " .
    		$objAccomplishments->calculateGlobularClusters($value)[1] .", " .
    		$objAccomplishments->calculateGlobularClusters($value)[2] .", " .
    		$objAccomplishments->calculateGlobularClusters($value)[3] .", " .
    		$objAccomplishments->calculateGlobularClusters($value)[4] .", " .
    		$objAccomplishments->calculateGlobularClusters($value)[5] .", " .
    		$objAccomplishments->calculateGlobularClusters($value)[6] .", " .
    		$objAccomplishments->calculateGlobularClusters($value)[7] .", " .
    		$objAccomplishments->calculateGlobularClusters($value)[8] .", " .
    		$objAccomplishments->calculateGlobularClusters($value)[9] .", " .
    		$objAccomplishments->calculateGlobularClusterDrawings($value)[0] .", " .
    		$objAccomplishments->calculateGlobularClusterDrawings($value)[1] .", " .
    		$objAccomplishments->calculateGlobularClusterDrawings($value)[2] .", " .
    		$objAccomplishments->calculateGlobularClusterDrawings($value)[3] .", " .
    		$objAccomplishments->calculateGlobularClusterDrawings($value)[4] .", " .
    		$objAccomplishments->calculateGlobularClusterDrawings($value)[5] .", " .
    		$objAccomplishments->calculateGlobularClusterDrawings($value)[6] .", " .
    		$objAccomplishments->calculateGlobularClusterDrawings($value)[7] .", " .
    		$objAccomplishments->calculateGlobularClusterDrawings($value)[8] .", " .
    		$objAccomplishments->calculateGlobularClusterDrawings($value)[9] .", " .
    		$objAccomplishments->calculatePlanetaryNebulae($value)[0] .", " .
    		$objAccomplishments->calculatePlanetaryNebulae($value)[1] .", " .
    		$objAccomplishments->calculatePlanetaryNebulae($value)[2] .", " .
    		$objAccomplishments->calculatePlanetaryNebulae($value)[3] .", " .
    		$objAccomplishments->calculatePlanetaryNebulae($value)[4] .", " .
    		$objAccomplishments->calculatePlanetaryNebulae($value)[5] .", " .
    		$objAccomplishments->calculatePlanetaryNebulae($value)[6] .", " .
    		$objAccomplishments->calculatePlanetaryNebulae($value)[7] .", " .
    		$objAccomplishments->calculatePlanetaryNebulae($value)[8] .", " .
    		$objAccomplishments->calculatePlanetaryNebulae($value)[9] .", " .
    		$objAccomplishments->calculatePlanetaryNebulaDrawings($value)[0] .", " .
    		$objAccomplishments->calculatePlanetaryNebulaDrawings($value)[1] .", " .
    		$objAccomplishments->calculatePlanetaryNebulaDrawings($value)[2] .", " .
    		$objAccomplishments->calculatePlanetaryNebulaDrawings($value)[3] .", " .
    		$objAccomplishments->calculatePlanetaryNebulaDrawings($value)[4] .", " .
    		$objAccomplishments->calculatePlanetaryNebulaDrawings($value)[5] .", " .
    		$objAccomplishments->calculatePlanetaryNebulaDrawings($value)[6] .", " .
    		$objAccomplishments->calculatePlanetaryNebulaDrawings($value)[7] .", " .
    		$objAccomplishments->calculatePlanetaryNebulaDrawings($value)[8] .", " .
    		$objAccomplishments->calculatePlanetaryNebulaDrawings($value)[9] .", " .
    		$objAccomplishments->calculateGalaxies($value)[0] .", " .
    		$objAccomplishments->calculateGalaxies($value)[1] .", " .
    		$objAccomplishments->calculateGalaxies($value)[2] .", " .
    		$objAccomplishments->calculateGalaxies($value)[3] .", " .
    		$objAccomplishments->calculateGalaxies($value)[4] .", " .
    		$objAccomplishments->calculateGalaxies($value)[5] .", " .
    		$objAccomplishments->calculateGalaxies($value)[6] .", " .
    		$objAccomplishments->calculateGalaxies($value)[7] .", " .
    		$objAccomplishments->calculateGalaxies($value)[8] .", " .
    		$objAccomplishments->calculateGalaxies($value)[9] .", " .
    		$objAccomplishments->calculateGalaxyDrawings($value)[0] .", " .
    		$objAccomplishments->calculateGalaxyDrawings($value)[1] .", " .
    		$objAccomplishments->calculateGalaxyDrawings($value)[2] .", " .
    		$objAccomplishments->calculateGalaxyDrawings($value)[3] .", " .
    		$objAccomplishments->calculateGalaxyDrawings($value)[4] .", " .
    		$objAccomplishments->calculateGalaxyDrawings($value)[5] .", " .
    		$objAccomplishments->calculateGalaxyDrawings($value)[6] .", " .
    		$objAccomplishments->calculateGalaxyDrawings($value)[7] .", " .
    		$objAccomplishments->calculateGalaxyDrawings($value)[8] .", " .
    		$objAccomplishments->calculateGalaxyDrawings($value)[9] .", " .
    		$objAccomplishments->calculateNebulae($value)[0] .", " .
    		$objAccomplishments->calculateNebulae($value)[1] .", " .
    		$objAccomplishments->calculateNebulae($value)[2] .", " .
    		$objAccomplishments->calculateNebulae($value)[3] .", " .
    		$objAccomplishments->calculateNebulae($value)[4] .", " .
    		$objAccomplishments->calculateNebulae($value)[5] .", " .
    		$objAccomplishments->calculateNebulae($value)[6] .", " .
    		$objAccomplishments->calculateNebulae($value)[7] .", " .
    		$objAccomplishments->calculateNebulae($value)[8] .", " .
    		$objAccomplishments->calculateNebulae($value)[9] .", " .
    		$objAccomplishments->calculateNebulaDrawings($value)[0] .", " .
    		$objAccomplishments->calculateNebulaDrawings($value)[1] .", " .
    		$objAccomplishments->calculateNebulaDrawings($value)[2] .", " .
    		$objAccomplishments->calculateNebulaDrawings($value)[3] .", " .
    		$objAccomplishments->calculateNebulaDrawings($value)[4] .", " .
    		$objAccomplishments->calculateNebulaDrawings($value)[5] .", " .
    		$objAccomplishments->calculateNebulaDrawings($value)[6] .", " .
    		$objAccomplishments->calculateNebulaDrawings($value)[7] .", " .
    		$objAccomplishments->calculateNebulaDrawings($value)[8] .", " .
    		$objAccomplishments->calculateNebulaDrawings($value)[9] .", " .
    		$objAccomplishments->calculateDifferentObjects($value)[0] .", " .
    		$objAccomplishments->calculateDifferentObjects($value)[1] .", " .
    		$objAccomplishments->calculateDifferentObjects($value)[2] .", " .
    		$objAccomplishments->calculateDifferentObjects($value)[3] .", " .
    		$objAccomplishments->calculateDifferentObjects($value)[4] .", " .
    		$objAccomplishments->calculateDifferentObjects($value)[5] .", " .
    		$objAccomplishments->calculateDifferentObjects($value)[6] .", " .
    		$objAccomplishments->calculateDifferentObjects($value)[7] .", " .
    		$objAccomplishments->calculateDifferentObjects($value)[8] .", " .
    		$objAccomplishments->calculateDifferentObjects($value)[9] .", " .
    		$objAccomplishments->calculateDifferentObjectDrawings($value)[0] .", " .
    		$objAccomplishments->calculateDifferentObjectDrawings($value)[1] .", " .
    		$objAccomplishments->calculateDifferentObjectDrawings($value)[2] .", " .
    		$objAccomplishments->calculateDifferentObjectDrawings($value)[3] .", " .
    		$objAccomplishments->calculateDifferentObjectDrawings($value)[4] .", " .
    		$objAccomplishments->calculateDifferentObjectDrawings($value)[5] .", " .
    		$objAccomplishments->calculateDifferentObjectDrawings($value)[6] .", " .
    		$objAccomplishments->calculateDifferentObjectDrawings($value)[7] .", " .
    		$objAccomplishments->calculateDifferentObjectDrawings($value)[8] .", " .
    		$objAccomplishments->calculateDifferentObjectDrawings($value)[9] .
            ");";
    $objDatabase->execSQL($sql);
    
    // We check for all observers if they have at least one observation... If they do, they have at least 
    // one accomplishment and we can send a message.
    if ($objAccomplishments->calculateDifferentObjects($value)[0] == 1) {
	    $sql = "select firstname from observers where id = \"". $value ."\""; 
        $run = $objDatabase->selectRecordset($sql);
        $get = $run->fetch(PDO::FETCH_OBJ);
    	$firstname = $get->firstname;
    	
	    $sql = "select language from observers where id = \"". $value ."\""; 
        $run = $objDatabase->selectRecordset($sql);
        $get = $run->fetch(PDO::FETCH_OBJ);
		if ($get->language == "nl") {
			$subject = 'Je hebt &eacute;&eacute;n of meerdere realisaties in DeepskyLog!';
			$content = 'Proficiat ' . $firstname;
			$content = $content . ', <br/><br/>Je hebt &eacute;&eacute;n of meerdere realisaties in DeepskyLog!<br/><br/>';
			$content = $content . 'Bekijk je realisaties op <a href="http://www.deepskylog.be/index.php?indexAction=detail_observer&user=' . $value . '">http://www.deepskylog.be/index.php?indexAction=detail_observer&user=' . $value . '</a>';
			$content = $content . '<br /><br />Het DeepskyLog team';
		} else if ($get->language == "en") {
			$subject = 'You have one of more realisations in DeepskyLog!';
			$content = 'Congratulations ' . $firstname;
			$content = $content . ', <br/><br/>You have one or more realisations in DeepskyLog!<br/><br/>';
			$content = $content . 'Look at your realisation at <a href="http://www.deepskylog.org/index.php?indexAction=detail_observer&user=' . $value . '">http://www.deepskylog.org/index.php?indexAction=detail_observer&user=' . $value . '</a>';
			$content = $content . '<br /><br />The DeepskyLog team';
		} else if ($get->language == "fr") {
			$subject = 'Vous avez une ou plusieures r&eacute;alisations dans DeepskyLog!';
			$content = 'F&eacute;licitations ' . $firstname;
			$content = $content . ', <br/><br/>Vous avez une ou plusieures r&eacute;alisations dans DeepskyLog!<br/><br/>';
			$content = $content . 'Regardez vos r&eacute;alisations &agrave; <a href="http://www.deepskylog.fr/index.php?indexAction=detail_observer&user=' . $value . '">http://www.deepskylog.fr/index.php?indexAction=detail_observer&user=' . $value . '</a>';
			$content = $content . '<br /><br />Le team DeepskyLog';
		} else {
			$subject = 'Sie haben ein oder mehrere Realisierungen in DeepskyLog!';
			$content = 'Gratulation ' . $firstname;
			$content = $content . ', <br/><br/>Sie haben ein oder mehrere Realisierungen in DeepskyLog!<br/><br/>';
			$content = $content . 'Sehen Sie Ihre Erkenntnisse auf <a href="http://www.deepskylog.de/index.php?indexAction=detail_observer&user=' . $value . '">http://www.deepskylog.de/index.php?indexAction=detail_observer&user=' . $value . '</a>';
			$content = $content . '<br /><br />Das DeepskyLog Team';
		}

        $objMessages->sendMessage("DeepskyLog", $value, $subject, $content);
    }
  }
 
  print "Database update successful.\n<br />";
?>
