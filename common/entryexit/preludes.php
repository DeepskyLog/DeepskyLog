<?php
// preludes.php
// loads all libraries for further use in includeFile

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
else 
{ preludesA();
  require_once "lib/setup/".$language;
  preludesB();
}

function preludesA()
{ global $language,
         $objDatabase,
         $objLanguage,
         $objObserver,
         $objUtil;

 	if(!session_id()) session_start();
	require_once "lib/setup/databaseInfo.php";
	require_once "lib/database.php";               $objDatabase=new Database;
	require_once "lib/util.php";                   $objUtil=new Utils;
	require_once "lib/setup/language.php";         $objLanguage=new Language;
	require_once "lib/observers.php";              $objObserver=new Observers;
	require_once "lib/setup/vars.php";
	require_once "common/control/loginuser.php";
}
function preludesB()
{ global $FF,$MSIE,$leftMenu,$topMenu,$thisYear,$thisMonth,$thisDay,$DSOcatalogsLists,$DSOcatalogs,
         $objAstroCalc,
         $objAtlas,
         $objCatalog,
         $objCometObject,
         $objCometObservation,
         $objConstellation,
         $objContrast,
         $objDatabase,
         $objEyepiece,
         $objFilter,
         $objFormLayout,
         $objInstrument,
         $objLanguage,
         $objLens,
         $objList,
         $objLocation,
         $objObject,
         $objObservation,
         $objObserverQueries,
         $objObserver,
         $objPresentations,
         $objPrintAtlas,
         $objReportLayout,
         $objStar,
         $objUtil
         ;

  require_once "lib/observerqueries.php";         $objObserverQueries=new Observerqueries;
  require_once "lib/atlasses.php";                $objAtlas=new Atlasses;
	require_once "lib/locations.php";               $objLocation=new Locations;
	require_once "lib/instruments.php";             $objInstrument=new Instruments;
	require_once "lib/filters.php";                 $objFilter=new Filters;
	require_once "lib/lenses.php";                  $objLens=new Lenses;
	require_once "lib/contrast.php";                $objContrast = new Contrast;
	require_once "lib/eyepieces.php";               $objEyepiece=new Eyepieces;
	require_once "lib/observations.php";            $objObservation = new Observations;
	require_once "lib/lists.php";                   $objList=new Lists;
	require_once "lib/objects.php";                 $objObject=new Objects;
  include_once "lib/astrocalc.php";               $objAstroCalc=new AstroCalc;
	require_once "lib/stars.php";                   $objStar=new Stars;
	include_once "lib/cometobservations.php";       $objCometObservation = new cometObservations;
	include_once "lib/cometobjects.php";            $objCometObject=new CometObjects;
  include_once 'lib/presentation.php';            $objPresentations=new Presentations;
  include_once 'lib/constellations.php';          $objConstellation=new Constellation;
  include_once 'lib/formlayouts.php';             $objFormLayout = new formLayouts;
  include_once 'lib/reportlayouts.php';           $objReportLayout = new reportLayouts;
  include_once 'lib/catalogs.php';                $objCatalog=new catalogs;
  include_once "lib/moonphase.inc.php";
  include_once "lib/printatlas.php";              $objPrintAtlas = new PrintAtlas;
  include_once "lib/class.pdf.php";
  include_once "lib/class.ezpdf.php";
  include_once "lib/icqmethod.php";
  include_once "lib/icqreferencekey.php";

	if(strpos(($browser=$objUtil->checkArrayKey($_SERVER,'HTTP_USER_AGENT','')),'Firefox')===false)
	  $FF=false;
	else
	  $FF=true;
  if(strpos(($browser=$objUtil->checkArrayKey($_SERVER,'HTTP_USER_AGENT','')),'MSIE')===false)
	  $MSIE=false;
	else
	  $MSIE=true;
	  
	  
  if (array_key_exists('globalMonth',$_SESSION) && $_SESSION['globalMonth']) {
  } else {
    $_SESSION['globalYear']=$thisYear;
    $_SESSION['globalMonth']=$thisMonth;
    $_SESSION['globalDay']=$thisDay;
  }
  if(array_key_exists('changeMonth',$_GET) && $_GET['changeMonth'])
  { $_SESSION['globalMonth'] = $_GET['changeMonth'];
    if(($_SESSION['globalDay']>28)&&($_SESSION['globalMonth']==2))
      $_SESSION['globalDay']=28;
    if(($_SESSION['globalDay']==31)&&(($_SESSION['globalMonth']==4)||($_SESSION['globalMonth']==6)||($_SESSION['globalMonth']==9)||($_SESSION['globalMonth']==11)))
     $_SESSION['globalDay']=30;
    if(array_key_exists('Qobj',$_SESSION))
      $_SESSION['Qobj']=$objObject->getObjectRisSetTrans($_SESSION['Qobj']);
  }
  if(array_key_exists('changeYear',$_GET) && $_GET['changeYear'])
  { $_SESSION['globalYear'] = $_GET['changeYear'];
    if(array_key_exists('Qobj',$_SESSION))
      $_SESSION['Qobj']=$objObject->getObjectRisSetTrans($_SESSION['Qobj']);
  }
  if(array_key_exists('changeDay',$_GET) && $_GET['changeDay'])
  { $_SESSION['globalDay'] = $_GET['changeDay'];
    if(array_key_exists('Qobj',$_SESSION))
      $_SESSION['Qobj']=$objObject->getObjectRisSetTrans($_SESSION['Qobj']);
  }
  if(array_key_exists('leftmenu',$_GET))
	  $leftmenu=$_GET['leftmenu'];
	elseif(array_key_exists('leftmenu',$_COOKIE))
	  $leftmenu=$_COOKIE['leftmenu'];
  if(array_key_exists('topmenu',$_GET))
	  $topmenu=$_GET['topmenu'];
	elseif(array_key_exists('topmenu',$_COOKIE))
	  $topmenu=$_COOKIE['topmenu'];
  $DSOcatalogsLists = $objObject->getCatalogsAndLists();
  $DSOcatalogs      = $objObject->getCatalogs();
}
function Nz($arg)
{ if($arg) return $arg;
  else     return ""; 
}
function Nz0($arg)
{ if($arg) return $arg;
  else     return 0; 
}
function Nzx($arg,$default="")
{ if($arg) return $arg;
  else     return $default; 
}
if (!function_exists('fnmatch'))                                              // definition of the php fnmatch function for Windows environments
{ function fnmatch($pattern, $string) 
 	{ return @preg_match('/^' . strtr(addcslashes($pattern, '\\.+^$(){}=!<>|'), array('*' => '.*', '?' => '.?')) . '$/i', $string);
  }
}
?>
