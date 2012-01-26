<?php 
// view_observer.php
// shows information of an observer 

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!($user=$objUtil->checkGetKey('user'))) throw new Exception(LangException015b);
else view_observer();

function view_observer()
{ global $user,$modules,$deepsky,$comets,$baseURL,$instDir,$loggedUser,$objDatabase,
         $objInstrument,$objPresentations,$objObservation,$objUtil,$objCometObservation,$objObserver,$objLocation;
  $name=$objObserver->getObserverProperty($user,'name'); 
	$firstname=$objObserver->getObserverProperty($user,'firstname');
	echo "<div id=\"main\">";
	$objPresentations->line(array("<h4>".$firstname.' '. $name."</h4>"),"L",array(),30);
	echo "<hr />";
	echo "<ol id=\"toc\">
	       <li><a href=\"" . $baseURL . "index.php?indexAction=detail_observer&user=" . $user . "\"><span>" . GraphInfo . "</span></a></li>
	       <li><a href=\"" . $baseURL . "index.php?indexAction=detail_observer1&user=" . $user . "\"><span>" . GraphObservationsTitle . "</span></a></li>
	       <li class=\"current\"><a href=\"" . $baseURL . "index.php?indexAction=detail_observer2&user=" . $user . "\"><span>" . GraphObservationsType . "</span></a></li>
	      </ol>";
   // Pie chart                  
  $objectsArray = array();
  $colors = Array();
  
  $all = count($objDatabase->selectRecordsetArray("select * from observations where observerid=\"" . $user . "\""));
  $rest = 0;

  $cometobservations = count($objDatabase->selectRecordsetArray("select * from cometobservations where observerid = \"" . $user . "\""));
  $all += $cometobservations;

  if (($cometobservations / $all) >= 0.01) {
    $objectsArray["comets"] = $cometobservations;
  } else {
    $rest += $cometobservations;
  }
  $colors["comets"] = "#4572A7";
  
  $aster = count($objDatabase->selectRecordsetArray("select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"ASTER\" and observations.observerid = \"" . $user . "\""));
  $aster += count($objDatabase->selectRecordsetArray("select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"AA8STAR\" and observations.observerid = \"" . $user . "\""));
  $aster += count($objDatabase->selectRecordsetArray("select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"AA4STAR\" and observations.observerid = \"" . $user . "\""));
  $aster += count($objDatabase->selectRecordsetArray("select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"AA3STAR\" and observations.observerid = \"" . $user . "\""));

  if (($aster / $all) >= 0.01) {
    $objectsArray["ASTER"] = $aster;
  } else {
    $rest += $aster;
  }
  $colors["ASTER"] = "#AA4643";
  
  $brtnb = count($objDatabase->selectRecordsetArray("select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"BRTNB\" and observations.observerid = \"" . $user . "\""));

  if (($brtnb / $all) >= 0.01) {
    $objectsArray["BRTNB"] = $brtnb;
  } else {
    $rest += $brtnb;
  }
  $colors["BRTNB"] = "#89A54E";
  
  
  $ds = count($objDatabase->selectRecordsetArray("select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"DS\" and observations.observerid = \"" . $user . "\""));
  $ds += count($objDatabase->selectRecordsetArray("select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"AA2STAR\" and observations.observerid = \"" . $user . "\""));

  if (($ds / $all) >= 0.01) {
    $objectsArray["DS"] = $ds;
  } else {
    $rest += $ds;
  }
  $colors["DS"] = "#80699B";
  
  $star = count($objDatabase->selectRecordsetArray("select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"AA1STAR\" and observations.observerid = \"" . $user . "\""));

  if (($star / $all) >= 0.01) {
    $objectsArray["AA1STAR"] = $star;
  } else {
    $rest += $star;
  }
  $colors["AA1STAR"] = "#3D96AE";
  
  $drknb = count($objDatabase->selectRecordsetArray("select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"DRKNB\" and observations.observerid = \"" . $user . "\""));

  if (($drknb / $all) >= 0.01) {
    $objectsArray["DRKNB"] = $drknb;
  } else {
    $rest += $drknb;
  }
  $colors["DRKNB"] = "#DB843D";
  
  $galcl = count($objDatabase->selectRecordsetArray("select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"GALCL\" and observations.observerid = \"" . $user . "\""));

  if (($galcl / $all) >= 0.01) {
    $objectsArray["GALCL"] = $galcl;
  } else {
    $rest += $galcl;
  }
  $colors["GALCL"] = "#92A8CD";
  
  $galxy = count($objDatabase->selectRecordsetArray("select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"GALXY\" and observations.observerid = \"" . $user . "\""));

  if (($galxy / $all) >= 0.01) {
    $objectsArray["GALXY"] = $galxy;
  } else {
    $rest += $galxy;
  }
  $colors["GALXY"] = "#68302F";
  
  $plnnb = count($objDatabase->selectRecordsetArray("select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"PLNNB\" and observations.observerid = \"" . $user . "\""));

  if (($plnnb / $all) >= 0.01) {
    $objectsArray["PLNNB"] = $plnnb;
  } else {
    $rest += $plnnb;
  }
  $colors["PLNNB"] = "#A47D7C";
  
  $opncl = count($objDatabase->selectRecordsetArray("select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"OPNCL\" and observations.observerid = \"" . $user . "\""));
  $opncl += count($objDatabase->selectRecordsetArray("select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"CLANB\" and observations.observerid = \"" . $user . "\""));

  if (($opncl / $all) >= 0.01) {
    $objectsArray["OPNCL"] = $opncl;
  } else {
    $rest += $opncl;
  }
  $colors["OPNCL"] = "#B5CA92";
  
  $glocl = count($objDatabase->selectRecordsetArray("select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"GLOCL\" and observations.observerid = \"" . $user . "\""));

  if (($glocl / $all) >= 0.01) {
    $objectsArray["GLOCL"] = $glocl;
  } else {
    $rest += $glocl;
  }
  $colors["GLOCL"] = "#00FF00";
  
  $eminb = count($objDatabase->selectRecordsetArray("select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"EMINB\" and observations.observerid = \"" . $user . "\""));
  $eminb += count($objDatabase->selectRecordsetArray("select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"ENRNN\" and observations.observerid = \"" . $user . "\""));
  $eminb += count($objDatabase->selectRecordsetArray("select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"ENSTR\" and observations.observerid = \"" . $user . "\""));

  if (($eminb / $all) >= 0.01) {
    $objectsArray["EMINB"] = $eminb;
  } else {
    $rest += $eminb;
  }
  $colors["EMINB"] = "#C0FFC0";
  
  $refnb = count($objDatabase->selectRecordsetArray("select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"REFNB\" and observations.observerid = \"" . $user . "\""));
  $refnb += count($objDatabase->selectRecordsetArray("select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"RNHII\" and observations.observerid = \"" . $user . "\""));
  $refnb += count($objDatabase->selectRecordsetArray("select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"HII\" and observations.observerid = \"" . $user . "\""));

  if (($refnb / $all) >= 0.01) {
    $objectsArray["REFNB"] = $refnb;
  } else {
    $rest += $refnb;
  }
  $colors["REFNB"] = "#0000C0";
  
  $nonex = count($objDatabase->selectRecordsetArray("select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"NONEX\" and observations.observerid = \"" . $user . "\""));

  if (($nonex / $all) >= 0.01) {
    $objectsArray["NONEX"] = $nonex;
  } else {
    $rest += $nonex;
  }
  $colors["NONEX"] = "#C0C0FF";
  
  $snrem = count($objDatabase->selectRecordsetArray("select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"SNREM\" and observations.observerid = \"" . $user . "\""));

  if (($snrem / $all) >= 0.01) {
    $objectsArray["SNREM"] = $snrem;
  } else {
    $rest += $snrem;
  }
  $colors["SNREM"] = "#808000";
  
  $quasr = count($objDatabase->selectRecordsetArray("select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"QUASR\" and observations.observerid = \"" . $user . "\""));

  if (($quasr / $all) >= 0.01) {
    $objectsArray["QUASR"] = $quasr;
  } else {
    $rest += $quasr;
  }
  $colors["QUASR"] = "#C0C000";
  
  $wrneb = count($objDatabase->selectRecordsetArray("select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"WRNEB\" and observations.observerid = \"" . $user . "\""));

  if (($wrneb / $all) >= 0.01) {
    $objectsArray["WRNEB"] = $wrneb;
  } else {
    $rest += $wrneb;
  }
  $colors["WRNEB"] = "#008080";
  
  $objectsArray["REST"] = $rest;
  $colors["REST"] = "#00FFFF";
	echo "<script type=\"text/javascript\">
		
			var chart;
			$(document).ready(function() {
				chart = new Highcharts.Chart({
					chart: {
						renderTo: 'container',
						plotBackgroundColor: null,
						plotBorderWidth: null,
						plotShadow: false
					},
					title: {
						text: \"" . ObjectsSeenGraph . $firstname . " " . $name . "\"
					},
                subtitle: {
                  text: '" . GraphSource . $baseURL . "'
                },
					tooltip: {
						formatter: function() {
							return '<b>'+ this.point.name +'</b>: '+ Math.round(this.percentage * 100) / 100 + '%';
						}
					},
					plotOptions: {
						pie: {
							allowPointSelect: true,
							cursor: 'pointer',
							showCheckbox: true,
							dataLabels: {
								enabled: true,
								color: '#000000',
								connectorColor: '#000000',
								formatter: function() {
									return '<b>'+ this.point.name +'</b>: '+ this.y;
								}
							}
						}
					},
				    series: [{
						type: 'pie',
						name: 'Objects seen',
						data: [";

  foreach ($objectsArray as $key => $value) {
    if ($key != "REST") {
      print "{name: \"" . html_entity_decode($GLOBALS[$key], ENT_QUOTES, "UTF-8") . "\", color: '" . $colors[$key] . "', y: " . $value . "}, ";
    } else {
      print "{name: \"" . html_entity_decode($GLOBALS[$key], ENT_QUOTES, "UTF-8") . "\", color: '" . $colors[$key] . "', y: " . $value . "}";
    }
  }
  echo                     "
						]
					}]
				});
			});
				
		</script>";
  echo "<div id=\"container\" style=\"width: 800px; height: 400px; margin: 0 auto\"></div>";
  echo "</div>";
}
?>
