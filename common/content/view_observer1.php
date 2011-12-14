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
	       <li><a href=\"" . $baseURL . "index.php?indexAction=detail_observer&user=" . $user . "\"><span>Info</span></a></li>
	       <li class=\"current\"><a href=\"" . $baseURL . "index.php?indexAction=detail_observer1&user=" . $user . "\"><span>Observations per year</span></a></li>
	       <li><a href=\"" . $baseURL . "index.php?indexAction=detail_observer2&user=" . $user . "\"><span>Object types observed</span></a></li>
	      </ol>";

	// GRAFIEK
	
	// Check the date of the first observation
	$currentYear = date("Y");
	$sql = $objDatabase->selectSingleValue("select MIN(date) from observations where observerid=\"" . $user . "\";", "MIN(date)", $currentYear."0606");
	$sql2 = $objDatabase->selectSingleValue("select MIN(date) from cometobservations where observerid=\"" . $user . "\";", "MIN(date)", $currentYear."0606");
  $startYear = min(floor($sql / 10000), floor($sql2 / 10000));	
	// Add the JavaScript to initialize the chart on document ready
	echo "<script type=\"text/javascript\">
	  	        
	  	      var chart;
	  	      $(document).ready(function() {
	  	      chart = new Highcharts.Chart({
	  	        chart: {
	  	          renderTo: 'container',
	  	          defaultSeriesType: 'line',
	  	          marginRight: 130,
	  	          marginBottom: 25
	  	        },
	  	        title: {
	  	          text: '" . GraphTitle1 . " " . $firstname . " " . $name . "',
	  	          x: -20 //center
	  	        },
	  	        subtitle: {
	  	          text: '" . GraphSource . $baseURL . "',
	  	          x: -20
	  	        },
	  	        xAxis: {
	  	          categories: [";

	for ($i = $startYear;$i <= $currentYear;$i++) {
	  if ($i != $currentYear) {
	    echo "'" . $i . "', ";
	  } else {
	    echo "'" . $i . "'";
	  }
	}
	
  echo "]
	  	        },
	  	        yAxis: {
	  	          title: {
	  	            text: '" . GraphObservations . "'
	  	        },
	  	        plotLines: [{
	  	          value: 0,
	  	          width: 1,
	  	          color: '#808080'
	  	        }]
	  	      },
	  	      tooltip: {
	  	        formatter: function() {
	  	                            return '<b>'+ this.series.name +'</b><br/>'+
	  	        this.x +': '+ this.y;
	  	        }
	  	                    },
	  	                    legend: {
	  	                    layout: 'vertical',
	  	                    align: 'right',
	  	                    verticalAlign: 'top',
	  	                    x: -10,
	  	                        y: 100,
	  	                    borderWidth: 0
	  	      },
	  	                    series: [{
	  	                      name: '" . $deepsky . "',
	  	                        data: [";
  for ($i = $startYear;$i <= $currentYear;$i++) {
    $obs = $objDatabase->selectSingleValue("select COUNT(date) from observations where observerid=\"" . $user . "\" and date >= \"" . $i . "0101\" and date <= \"" . $i . "1231\";", "COUNT(date)", "0");
    if ($i != $currentYear) {
      echo $obs . ", ";
    } else {
      echo $obs;
    }
  }
  echo "                    ]
	  	                      }, {
                              name: '" . $comets . "',
                                data: [";

  for ($i = $startYear;$i <= $currentYear;$i++) {
    $obs = $objDatabase->selectSingleValue("select COUNT(date) from cometobservations where observerid=\"" . $user . "\" and date >= \"" . $i . "0101\" and date <= \"" . $i . "1231\";", "COUNT(date)", "0");
    if ($i != $currentYear) {
      echo $obs . ", ";
    } else {
      echo $obs;
    }
  }

  echo "                     ] }]
	  	                      });
	  	                      });
	  	                      
	  	                      </script>";
	
    // Show graph
	echo "<div id=\"container\" style=\"width: 800px; height: 400px; margin: 0 auto\"></div>";
  echo "</div>";
}
?>
