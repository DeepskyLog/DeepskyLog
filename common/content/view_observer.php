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
	$location_id = $objObserver->getObserverProperty($user,'stdlocation');
	$location_name = $objLocation->getLocationPropertyFromId($location_id,'name');
	$instrumentname=$objInstrument->getInstrumentPropertyFromId($objObserver->getObserverProperty($user,'stdtelescope'),'name');
	$userDSobservation=$objObserver->getNumberOfDsObservations($user);
	$totalDSObservations=$objObservation->getNumberOfDsObservations();
	$userDSYearObservations=$objObservation->getObservationsLastYear($user);
	$totalDSYearObservations=$objObservation->getObservationsLastYear('%');
	$userDSObjects=$objObservation->getNumberOfObjects($user);
	$totalDSobjects=$objObservation->getNumberOfDifferentObservedDSObjects();
	$userMobjects=$objObservation->getObservedCountFromCatalogOrList($user,"M");
	$userCaldwellObjects=$objObservation->getObservedCountFromCatalogOrList($user,"Caldwell");
	$userH400objects=$objObservation->getObservedCountFromCatalogOrList($user,"H400");
	$userHIIobjects=$objObservation->getObservedCountFromCatalogOrList($user,"HII");
	$userDSrank=$objObserver->getDsRank($user);
	if($userDSrank===false)
	  $userDSrank = "-";
	else
	  $userDSrank++;
	$userCometobservation=$objObserver->getNumberOfCometObservations($user);
	$totalCometObservations=$objCometObservation->getNumberOfObservations();
	$userCometYearObservations=$objCometObservation->getObservationsThisYear($user);
	$totalCometYearObservations=$objCometObservation->getNumberOfObservationsThisYear();
	$userCometObjects = $objCometObservation->getNumberOfObjects($user);
	$totalCometobjects=$objCometObservation->getNumberOfDifferentObjects();
	$cometrank = $objObserver->getCometRank($user);
	if ($cometrank===false)
	  $cometrank = "-";
	else
	  $cometrank++;
	 
	for($i =0;$i<count($modules);$i++)
	{ if(strcmp($$modules[$i], $deepsky)==0)
	  { $key=$i;
	  	$information[$i][0]=$userDSobservation." / ".$totalDSObservations."&nbsp;(".sprintf("%.2f",($userDSobservation / $totalDSObservations) * 100)."%)";
	    $information[$i][1]=$userDSYearObservations." / ".$totalDSYearObservations."&nbsp;(".sprintf("%.2f",$userDSYearObservations/$totalDSYearObservations*100)."%)";
	    $information[$i][2]=$userDSObjects." / ".$totalDSobjects."&nbsp;(" . sprintf("%.2f",$userDSObjects/$totalDSobjects*100)."%)";
	    $information[$i][4]=$userDSrank;
	  }
	  if(strcmp($$modules[$i], $comets) == 0)
	  { $information[$i][0]=$userCometobservation." / ".$totalCometObservations." (".sprintf("%.2f", $userCometobservation/$totalCometObservations*100)."%)";
	    $information[$i][1]=$userCometYearObservations." / ".$totalCometYearObservations."&nbsp;(".sprintf("%.2f", $userCometYearObservations/($totalCometYearObservations?$totalCometYearObservations:1)*100)."%)";
	    $information[$i][2]=$userCometObjects . " / ".$totalCometobjects." (" . sprintf("%.2f", $userCometObjects/$totalCometobjects*100)."%)";
	    $information[$i][4]=$cometrank;
	  }
	}  
	echo "<div id=\"main\">";
	$objPresentations->line(array("<h4>".$firstname.' '. $name."</h4>"),"L",array(),30);
	echo "<hr />";
	
	if(array_key_exists('admin',$_SESSION)&&($_SESSION['admin']=="yes"))       // admin logged in
	{ echo "<form action=\"".$baseURL."index.php\" >";
	  echo "<input type=\"hidden\" name=\"indexAction\" value=\"change_emailNameFirstname_Password\" />";
	  echo "<input type=\"hidden\" name=\"user\" value=\"".$user."\" />";
	  $objPresentations->line(array(LangChangeAccountField1.":",$objObserver->getObserverProperty($user,'id')),"RL",array(20,80),30,array('type10','type10'));
	  $objPresentations->line(array(LangChangeAccountField2.":","<input name=\"email\" type=\"text\" value=\"".$objObserver->getObserverProperty($user,'email')."\" />"),"RL",array(20,80),30,array('type10','type10'));
	  $objPresentations->line(array(LangChangeAccountField3.":","<input name=\"firstname\" type=\"text\" value=\"".$objObserver->getObserverProperty($user,'firstname')."\" /><input type=\"submit\" name=\"change_email_name_firstname\" value=\"".LangViewObserverChangeNameFirstname."\" />"),"RL",array(20,80),30,array('type20','type20'));
	  $objPresentations->line(array(LangChangeAccountField4.":","<input name=\"name\" type=\"text\" value=\"".$objObserver->getObserverProperty($user,'name')."\" />"),"RL",array(20,80),30,array('type10','type10'));
	  $objPresentations->line(array(LangChangeAccountField5.":","<input name=\"password\" type=\"text\" value=\"\" /><input type=\"submit\" name=\"change_password\" value=\""."Change password"."\" />"),"RL",array(20,80),30,array('type20','type20'));
	  	  echo "</form>";
	}
	else
	{ $objPresentations->line(array(LangChangeAccountField3.":",$objObserver->getObserverProperty($user,'firstname')),"RL",array(20,80),20,array('type20','type20'));
	  $objPresentations->line(array(LangChangeAccountField4.":",$objObserver->getObserverProperty($user,'name')),"RL",array(20,80),20,array('type10','type10'));
	}
	$objPresentations->line(array(LangChangeAccountField7.":","<a href=\"".$baseURL."index.php?indexAction=detail_location&amp;location=".urlencode($location_id)."\">".$location_name."</a>"),"RL",array(20,80),20,array('type20','type20'));
	$objPresentations->line(array(LangChangeAccountField8.":",($instrumentname?"<a href=\"".$baseURL."index.php?indexAction=detail_instrument&amp;instrument=".urlencode($objObserver->getObserverProperty($user,'stdtelescope'))."\">".(($instrumentname=="Naked eye")?InstrumentsNakedEye:$instrumentname)."</a>":"")),"RL",array(20,80),20,array('type10','type10'));
	if($objUtil->checkSessionKey('admin')=="yes")
	{ echo "<form action=\"".$baseURL."index.php\" >";
	  echo "<input type=\"hidden\" name=\"indexAction\" value=\"change_role\" />";
	  echo "<input type=\"hidden\" name=\"user\" value=\"".$user."\" />";
	  $content='';
	  if($user!="admin")
	  { $content = "<select name=\"role\" class=\"\">";
	    $content.= "<option ".(($objObserver->getObserverProperty($user,'role',2)==RoleAdmin)?"selected=\"selected\"":"")." value=\"0\">".LangViewObserverAdmin."</option>";
	    $content.= "<option ".(($objObserver->getObserverProperty($user,'role',2)==RoleUser)?"selected=\"selected\"":"")." value=\"1\">".LangViewObserverUser."</option>";
	    $content.= "<option ".(($objObserver->getObserverProperty($user,'role',2)==RoleCometAdmin)?"selected=\"selected\"":"")." value=\"4\">".LangViewObserverCometAdmin."</option>";
	    $content.= "<option ".(($objObserver->getObserverProperty($user,'role',2)==RoleWaitlist)?"selected=\"selected\"":"")." value=\"2\">".LangViewObserverWaitlist."</option>";
	    $content.= "</select>&nbsp;";
	    $content.= "<input type=\"submit\" name=\"change\" value=\"".LangViewObserverChange."\" />";
	  }
	  elseif($objObserver->getObserverProperty($user,'role',2)==RoleWaitlist)
	    $content = LangViewObserverWaitlist;
	  else                                                                          // fixed admin role
	  { $content = LangViewObserverAdmin;
	  }
	  $objPresentations->line(array(LangViewObserverRole.":",$content),"RL",array(20,80),'40',array('fieldname type20','type20'));
	  echo "</form>";
	}
	echo "<hr />";
	$content=array();
	$classes=array();
	$content[]="";
	$alignment="R";
	$classes[]="";
	for($i=0;$i<count($modules);$i++)
	{ $content[]=$GLOBALS[$modules[$i]];
	  $classes[]="type30";
	  $alignment.="C";
	}
	$objPresentations->line($content,$alignment,array(33,33,34),25,$classes);
	
	$content=array();
	$classes=array();
	$content[]=LangViewObserverNumberOfObservations.":";
	$alignment="R";
	$classes[]="fieldname type10";
	for($i=0;$i<count($modules);$i++)
	{ $content[]=$information[$i][0];
	  $classes[]="fieldvalue type10";
	  $alignment.="C";
	}
	$objPresentations->line($content,$alignment,array(33,33,34),25,$classes);
	
	$content=array();
	$classes=array();
	$content[]=LangTopObserversHeader4.":";
	$alignment="R";
	$classes[]="fieldname type20";
	for($i=0;$i<count($modules);$i++)
	{ $content[]=$information[$i][1];
	  $classes[]="fieldvalue type20";
	  $alignment.="C";
	}
	$objPresentations->line($content,$alignment,array(33,33,34),25,$classes);
	
	$content=array();
	$classes=array();
	$content[]=LangTopObserversHeader6.":";
	$alignment="R";
	$classes[]="fieldname type10";
	for($i=0;$i<count($modules);$i++)
	{ $content[]=$information[$i][2];
	  $classes[]="fieldvalue type10";
	  $alignment.="C";
	}
	$objPresentations->line($content,$alignment,array(33,33,34),25,$classes);
	
	$content=array();
	$classes=array();
	$content[]=LangTopObserversHeader5.":";
	$alignment="R";
	$classes[]="fieldname type20";
	for($i=0;$i<count($modules);$i++)
	{ $content[]=(($key==$i)?$userMobjects." / 110":"-");
	  $classes[]="fieldvalue type20";
	  $alignment.="C";
	}
	$objPresentations->line($content,$alignment,array(33,33,34),25,$classes);
	
	$content=array();
	$classes=array();
	$content[]=LangTopObserversHeader5b.":";
	$alignment="R";
	$classes[]="fieldname type10";
	for($i=0;$i<count($modules);$i++)
	{ $content[]=(($key==$i)?$userCaldwellObjects." / 110":"-");
	  $classes[]="fieldvalue type10";
	  $alignment.="C";
	}
	$objPresentations->line($content,$alignment,array(33,33,34),25,$classes);
	
	$content=array();
	$classes=array();
	$content[]=LangTopObserversHeader5c.":";
	$alignment="R";
	$classes[]="fieldname type20";
	for($i=0;$i<count($modules);$i++)
	{ $content[]=(($key==$i)?$userH400objects." / 400":"-");
	  $classes[]="fieldvalue type20";
	  $alignment.="C";
	}
	$objPresentations->line($content,$alignment,array(33,33,34),25,$classes);
	
	$content=array();
	$classes=array();
	$content[]=LangTopObserversHeader5d.":";
	$alignment="R";
	$classes[]="fieldname type10";
	for($i=0;$i<count($modules);$i++)
	{ $content[]=(($key==$i)?$userHIIobjects." / 400":"-");
	  $classes[]="fieldvalue type10";
	  $alignment.="C";
	}
	$objPresentations->line($content,$alignment,array(33,33,34),25,$classes);
	
	$content=array();
	$classes=array();
	$content[]=LangViewObserverRank.":";
	$alignment="R";
	$classes[]="fieldname type20";
	for($i=0;$i<count($modules);$i++)
	{ $content[]=$information[$i][4];
	  $classes[]="fieldvalue type20";
	  $alignment.="C";
	}
	$objPresentations->line($content,$alignment,array(33,33,34),25,$classes);
	
	if ($loggedUser != "") {
	  echo "<hr />";
	  echo "<a href=\"" . $baseURL . "index.php?indexAction=new_message&amp;receiver=" . $user . "\">" .
	        LangMessagePublicList5 . $firstname . " " . $name ."</a>";
	}
	
	echo "<hr />";
	$dir = opendir($instDir.'common/observer_pics');
	while(FALSE!==($file=readdir($dir)))
	{ if(("." == $file)OR(".."== $file))
	    continue;                                                                   // skip current directory and directory above
	  if(fnmatch($user. ".gif", $file) || fnmatch($user. ".jpg",$file) || fnmatch($user. ".png", $file))
	  { echo "<div>";
	    echo "<a href=\"".$baseURL."common/observer_pics/".$file."\" rel=\"prettyPhoto\" title=\"\">";
	    echo "<img class=\"viewobserver\" src=\"".$baseURL."common/observer_pics/".$file."\" alt=\"".$firstname."&nbsp;".$name."\"></img>
	          </a></div>";
	    echo "<hr />";
	  }
	}
	
	// GRAFIEK
	// TODO : Moet in aparte tab komen
	
	// Check the date of the first observation
	$currentYear = date("Y");
	$sql = $objDatabase->selectSingleValue("select MIN(date) from observations where observerid=\"" . $user . "\";", "MIN(date)", $currentYear."0606");
  $startYear = floor($sql / 10000);	
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
	  	          text: '" . GraphTitle1 . "',
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
	  	                      name: '" . $firstname.' '. $name . "',
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
	  	                      }]
	  	                      });
	  	                      });
	  	                      
	  	                      </script>";
	
//	                      <!-- 3. Add the container -->
	echo "<div id=\"container\" style=\"width: 800px; height: 400px; margin: 0 auto\"></div>";
	                       
	
	echo "</div>";
}
?>