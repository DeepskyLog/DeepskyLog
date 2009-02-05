<?php
//CLASS util
// INTERFACE
//   public function utiltiesDispatchIndexActionDS() -> returns the file to be included to execute the action specified in $_GET['indexAcction']
//   public function utilitiesSetModuleCookie($module)
//	 public function checkGetKey($key,$default='')
//   public function checkGetDate($year,$month,$day)
//   public function checkGetTimeOrDegrees($hr,$min,$sec)
//   public function searchAndLinkCatalogsInText($theText)
// ..
// PUBLIC OBJECT
//  $objUtil  

include_once "class.ezpdf.php";

class util
{ public function __construct()
	{ $this->checkUserInput();
  }
  public  function raToStringDSS($ra)
  { $ra_hours = floor($ra);
    $subminutes = 60 * ($ra - $ra_hours);
    $ra_minutes = floor($subminutes);
    $ra_seconds = round(60 * ($subminutes - $ra_minutes));
    return("$ra_hours" . "&#43;" . "$ra_minutes" . "&#43;" . "$ra_seconds" . "");
  }
  public  function decToStringDSS($decl)
  { $sign=0;
    if($decl<0)
    { $sign=-1;
      $decl=-$decl;
    }
    $decl_degrees=floor($decl);
    $subminutes=60*($decl-$decl_degrees);
    $decl_minutes=round($subminutes);
    if($sign==-1)
    { $decl_minutes = "-".$decl_minutes;
      $decl_degrees = "-".$decl_degrees;
    }
    return("$decl_degrees"."&#43;".$decl_minutes);
  }
  private function utilitiesGetIndexActionDefaultAction()
  { if($_SESSION['module']=='deepsky')
	  { $_GET['catalog']='%';
  	  $theDate = date('Ymd', strtotime('-1 year'));
      $_GET['minyear'] = substr($theDate,0,4);
      $_GET['minmonth'] = substr($theDate,4,2);
      $_GET['minday'] = substr($theDate,6,2);  
  	  return 'deepsky/content/selected_observations2.php';
		}
		else
		  return 'comets/content/overview_observations.php';	
  }
  private function utilitiesCheckIndexActionDSquickPick()
  { if($this->checkGetKey('indexAction')=='quickpick')
    { if($this->checkGetKey('object'))
	    { if($temp=$GLOBALS['objObject']->getExactDsObject($_GET['object']))
	      { $_GET['object']=$temp;
					if(array_key_exists('searchObservations', $_GET))
	          return 'deepsky/content/selected_observations2.php';  
	        elseif(array_key_exists('newObservation', $_GET))
	          return 'deepsky/content/new_observation.php';   
	        else
	          return 'deepsky/content/view_object.php';  
	      }
	      else
	      { $_GET['object']=ucwords(trim($_GET['object']));
	        if(array_key_exists('searchObservations', $_GET))
	          return 'deepsky/content/selected_observations2.php';  
	        elseif(array_key_exists('newObservation', $_GET))
	          return 'deepsky/content/setup_objects_query.php';   
	        else
	          return 'deepsky/content/setup_objects_query.php';  
	      }
	    }
      else
      {	if(array_key_exists('searchObservations', $_GET))
	        return 'deepsky/content/setup_observations_query.php';  
	      elseif(array_key_exists('newObservation', $_GET))
	        return 'deepsky/content/new_observation.php';   
	      else
	        return 'deepsky/content/setup_objects_query.php';  
       }
    }
  }
  private function utilitiesCheckIndexActionAll($action, $includefile)
  { if(array_key_exists('indexAction',$_GET)&&($_GET['indexAction']==$action))
      return $includefile;
  }
  private function utilitiesCheckIndexActionMember($action, $includefile)
  { if(array_key_exists('indexAction',$_GET) && ($_GET['indexAction'] == $action) && 
       array_key_exists('deepskylog_id', $_SESSION) && ($_SESSION['deepskylog_id']!=""))
      return $includefile; 
  }
  private function utilitiesCheckIndexActionAdmin($action, $includefile)
  { if(array_key_exists('indexAction',$_REQUEST) && ($_REQUEST['indexAction'] == $action) && 
       array_key_exists('admin', $_SESSION) && ($_SESSION['admin'] == "yes"))
      return $includefile; 
  }
  public function printNewListHeader(&$list, $link, $min, $step, $total)
  { global $baseURL;
	  $pages = ceil(count($list) / $step);       // total number of pages
    if($min)                                   // minimum value
    { $min = $min - ($min % $step);            // start display from number of $steps
      if ($min < 0)                            // minimum value smaller than 0
        $min = 0;
      if ($min > count($list))                 // minimum value bigger than number of elements
        $min = count($list) - (count($list) % $step);
    }
    else                                       // no minimum value defined
      $min = 0;
    $max = $min + $step;                       // maximum number to be displayed
    echo "<table>";
    echo "<tr style=\"vertical-align:top\">";
    if(count($list)>$step)
    { $currentpage = ceil($min / $step) + 1;
			echo "<td>";	
      echo "<a href=\"".$link."&amp;multiplepagenr=0\">";
      echo "<img src=\"".$baseURL."styles/images/allleft20.gif\" border=\"0\">"; // link to last page
      echo "</a>";
	    echo "</td>";
			
		  echo "<td>";
	    echo "<a href=\"".$link."&amp;multiplepagenr=".($currentpage>0?($currentpage-1):$currentpage)."\">";
      echo "<img src=\"".$baseURL."styles/images/left20.gif\" border=\"0\">"; // link to previous page
      echo "</a>";
		  echo "</td>";
		  
			echo "<td align=\"center\">";
      echo "<form action=\"".$link."\" method=\"post\">";
      echo "<input type=\"text\" name=\"multiplepagenr\" size=\"4\" class=\"inputfield\" style=\"text-align:center\" value=\"".$currentpage."\"></input>";
	    echo "</form>";
    	echo "</td>";	
	
		  echo "<td>";
      echo "<a href=\"".$link."&amp;multiplepagenr=".($currentpage<$pages?($currentpage+1):$currentpage)."\">";
      echo "<img src=\"".$baseURL."styles/images/right20.gif\" border=\"0\">"; // link to next page
      echo "</a>";
		  echo "</td>";

		  echo "<td>";				
		  echo "<a href=\"".$link."&amp;multiplepagenr=".$pages."\">";
      echo "<img src=\"".$baseURL."styles/images/allright20.gif\" border=\"0\">"; // link to last page
      echo "</a>\n";
	    echo" </td>";

	  }
    echo"<td>";
		echo "&nbsp;&nbsp;(".count($list)."&nbsp;".LangNumberOfRecords.(($total&&($total!=count($list)))?" / ".$total:"");
    if($pages>1)
      echo " in ".$pages." pages)";
    else
      echo ")";
    echo "</td>";
	  echo "</tr>";
	  echo "</table>";    
	  return array($min, $max);
  }
  public function printListHeader($list, $link, $min, $step, $total)	// printListHeader prints the list header of $list if the list has more than
																														  // $step entries. The first item from the list that should be shown is $min.
																														  // All numbers use the given link. An array is given back, with the min and
																														  // max value. Example :
																														  // list($min, $max) = $util->printListHeader($obs, $link, $_GET['min'], 25, 1221);
  {
    $pages = ceil(count($list) / $step); // total number of pages
    if($min) // minimum value
    { $min = $min - ($min % $step); // start display from number of $steps
      if ($min < 0)  // minimum value smaller than 0
        $min = 0;
      if ($min > count($list)) // minimum value bigger than number of elements
        $min = count($list) - (count($list) % $step);
    }
    else // no minimum value defined
      $min = 0;
    $max = $min + $step; // maximum number to be displayed
    if(count($list) > $step)
    {
      $currentpage = ceil($min / $step) + 1;
      echo("<p>\n");
      echo("<a href=\"".$link."&amp;min=0\">");
      echo LangOverviewObjectsFirstlink; // link to first page
      echo("</a>&nbsp;&nbsp;&nbsp;\n");

      if ($currentpage <= 7)
        $start = -$currentpage + 1;
      else if ($currentpage >= $pages - 7)
        $start = -14 + ($pages - $currentpage);
      else
        $start = -7;

      for ($i = $start; $i <= $start + 14; $i++)
      {
        $pagenumber = ($min + ($step * $i));
        if((($pagenumber/$step) >= 0) && (($pagenumber/$step) < $pages))
        { if($i != 0) // not current page
            echo("<a href=\"".$link."&amp;min=" . $pagenumber . "\">" . ($pagenumber/$step + 1) . "</a>&nbsp;"); // link to other page
          else
            echo(($pagenumber/$step + 1) . "&nbsp;"); // current page
        }
      }
      echo("&nbsp;&nbsp;");
			
			echo("<a href=\"".$link."&amp;min=".(($pages*$step) - 1) . "\">");
      echo LangOverviewObjectsLastlink; // link to last page
      echo("</a>\n");
      
			if ($total == "")
        echo("&nbsp;&nbsp;(" . count($list) . "&nbsp;" . LangNumberOfRecords . ")");
      else
        echo("&nbsp;&nbsp;(" . count($list) . "&nbsp;" . LangNumberOfRecords . " / " . $total . ")");
      echo("</p>\n");
    }
    return array($min, $max);
  }
  public function array_slice_key($array, $offset, $len=-1) // Array slice, but uses also keys.
  {
    if (!is_array($array))
    return FALSE;

    $length = $len >= 0? $len: count($array);
    $keys = array_slice(array_keys($array), $offset, $length);
    foreach($keys as $key)
    {
      $return[$key] = $array[$key];
    }
    return $return;
  }
  public function checkUserInput()  // function to correct data input of users to eliminate XSS exploits
  {
    foreach($_POST as $foo => $bar)
    {
      $_POST[$foo] = htmlentities(stripslashes($bar), ENT_COMPAT, "ISO-8859-15", 0);
    }
    foreach($_GET as $foo => $bar)
    {
      $_GET[$foo] = htmlentities(stripslashes($bar), ENT_COMPAT, "ISO-8859-15", 0);
    }
  }
  public function pdfObjects($result)  // Creates a pdf document from an array of objects
  { global $deepskylive, $dateformat;
    while(list ($key, $valueA) = each($result))
    { $mag = $valueA['objectmagnitude'];
      if ($mag == 99.9)
      $mag = "";
      else if ($mag - (int)$mag == 0.0)
      $mag = $mag.".0";

      $sb = $valueA['objectsurfacebrightness'];
      if ($sb == 99.9)
      $sb = "";
      else if ($sb - (int)$sb == 0.0)
      $sb = $sb.".0";

      $pa = $valueA['objectpa'];
      if($pa==999)
      $pa="-";

      $con = $valueA['objectconstellation'];
      $type = $valueA['objecttype'];
      $atlas = $GLOBALS['objObserver']->getStandardAtlasCode($_SESSION['deepskylog_id']);
      $page = $valueA[$atlas];
      $diam1 = $valueA['objectdiam1'];
      $diam2 = $valueA['objectdiam2'];
      $size = "";
      if ($diam1 != 0.0)
      if ($diam1 >= 40.0)
      {
        if (round($diam1 / 60.0) == ($diam1 / 60.0))
        if ($diam1 / 60.0 > 30.0)
        $size = sprintf("%.0f'", $diam1 / 60.0);
        else
        $size = sprintf("%.1f'", $diam1 / 60.0);
        else
        $size = sprintf("%.1f'", $diam1 / 60.0);
        if ($diam2 != 0.0)
        if (round($diam2 / 60.0) == ($diam2 / 60.0))
        if ($diam2 / 60.0 > 30.0)
        $size = $size.sprintf("x%.0f'", $diam2 / 60.0);
        else
        $size = $size.sprintf("x%.1f'", $diam2 / 60.0);
        else
        $size = $size.sprintf("x%.1f'", $diam2 / 60.0);
      }
      else
      {
        $size = sprintf("%.1f''", $diam1);
        if ($diam2 != 0.0)
        $size = $size.sprintf("x%.1f''", $diam2);
      }
      $contrast = $valueA['objectcontrast'];
      if ($contrast == "-")
      {
        $magnifi = "-";
      } else {
        $magnifi = (int)$valueA['objectoptimalmagnification'];
      }

      $temp = array("Name" => $valueA['showname'],
                 "ra" => raToString($valueA['objectra']),
                 "decl" => decToString($valueA['objectdecl'], 0),
                 "mag" => $mag,
                 "sb" => $sb,
                 "con" => $GLOBALS[$con],
                 "diam" => $size,
                 "pa" => $pa, 
                 "type" => $GLOBALS[$type],
                 "page" => $page,
                 "contrast" => $contrast,
                 "magnification" => $magnifi,
                 "seen" => $valueA['objectseen'],
								 "seendate" => $valueA['objectlastseen']
      );
      $obs1[] = $temp;
    }

    // Create pdf file
    $pdf = new Cezpdf('a4', 'landscape');
    $pdf->ezStartPageNumbers(450, 15, 10);
    $fontdir = $GLOBALS['instDir'].'lib/fonts/Helvetica.afm';
    $pdf->selectFont($fontdir);
        
    $pdf->ezTable($obs1,
    array("Name" => html_entity_decode(LangPDFMessage1),
                      "ra" =>   html_entity_decode(LangPDFMessage3),
                      "decl" => html_entity_decode(LangPDFMessage4),
                      "type" => html_entity_decode(LangPDFMessage5),
                      "con" =>  html_entity_decode(LangPDFMessage6),
                      "mag" =>  html_entity_decode(LangPDFMessage7),
                      "sb" =>   html_entity_decode(LangPDFMessage8),
                      "diam" => html_entity_decode(LangPDFMessage9),
                      "pa" =>   html_entity_decode(LangPDFMessage16),  
                      "page" => html_entity_decode($GLOBALS['objAtlas']->atlasCodes[$atlas]),
                      "contrast" => html_entity_decode(LangPDFMessage17),
                      "magnification" => html_entity_decode(LangPDFMessage18),
                      "seen" => html_entity_decode(LangOverviewObjectsHeader7),
                      "seendate" => html_entity_decode(LangOverviewObjectsHeader8)
    ),
    html_entity_decode($_GET['pdfTitle']),
    array("width" => "750",
			                "cols" => array("Name" => array('justification'=>'left',  'width'=>100),
			                                "ra" =>   array('justification'=>'center','width'=>65),
		              									  "decl" => array('justification'=>'center','width'=>50),
									              		  "type" => array('justification'=>'left',  'width'=>110),
              											  "con" =>  array('justification'=>'left',  'width'=>90),
							              				  "mag" =>  array('justification'=>'center','width'=>35),
              											  "sb" =>   array('justification'=>'center','width'=>35),
							              			  	"diam" => array('justification'=>'center','width'=>65),
       											          "pa" =>   array('justification'=>'center','width'=>30),
				              							  "page" => array('justification'=>'center', 'width'=>45),
          														"contrast" => array('justification'=>'center', 'width'=>35),
          														"magnification" => array('justification'=>'center', 'width'=>35),
											                "seen" => array('justification'=>'center','width'=>50),
											                "seendate" => array('justification'=>'center','width'=>50)
    ),
											"fontSize" => "7"				         
											)
											);
	$pdf->ezStream();
  }
  public function pdfObjectnames($result)  // Creates a pdf document from an array of objects
  {
    $page=1;
    $i=0;
    while(list($key, $valueA) = each($result))
    $obs1[] = array($valueA['showname']);
    // Create pdf file
    $pdf = new Cezpdf('a4', 'landscape');
    $pdf->ezStartPageNumbers(450, 15, 10);

    $fontdir = /*realpath*/('lib/fonts/Helvetica.afm');
    //  $pdf->selectFont($fontdir);
    $pdf->selectFont('lib/fonts/Helvetica.afm');
    $pdf->ezText(html_entity_decode($_GET['pdfTitle']),18);
    $pdf->ezColumnsStart(array('num'=>10));
    $pdf->ezTable($obs1,
                '', 
	              '',
    array("width" => "750",
			                "cols" => array(array('justification'=>'left', 'width'=>80)
    ),
											"fontSize" => "7",
											"showLines" => "0",
											"showHeadings" => "0",
											"rowGap" => "0",
											"colGap" => "0"				         
											)
											);
											$pdf->ezStream();
  }
  public function pdfObjectsDetails($result, $sort='')  // Creates a pdf document from an array of objects
  { if($sort=='objectconstellation') $sort='con'; else $sort='';
	  global $deepskylive, $dateformat;
		global $baseURL, $dbname;
    // Create pdf file
    $pdf = new Cezpdf('a4', 'landscape');
    $fontdir = ('lib/fonts/Helvetica.afm');
    $pdf->selectFont('lib/fonts/Helvetica.afm');

    $actualsort='';
    $y = 0;
    $bottom = 40;
    $bottomsection = 30;
    $top = 550;
    $header = 570;
    $footer = 10;
		$xleft = 20;
    $xmid = 431;
    $fontSizeSection = 10;
    $fontSizeText = 8;
    $deltaline = $fontSizeText+4;
		$deltalineSection = 2;
		$pagenr = 0;
    $xbase = $xmid;
		$sectionBarHeight = $fontSizeSection + 4;
		$descriptionLeadingSpace = 20;
		$sectionBarSpace = 3;
		$SectionBarWidth = 400+$sectionBarSpace;
    $theDate=date('d/m/Y');
    $pdf->addTextWrap($xleft, $header, 100, 8, $theDate);
		if($GLOBALS['objObserver']->getObserverProperty($_SESSION['deepskylog_id'],'name')
		&& $GLOBALS['objLocation']->getLocationPropertyFromId($GLOBALS['objObserver']->getStandardLocation($_SESSION['deepskylog_id']),'name')
		&& $GLOBALS['objInstrument']->getInstrumentPropertyFromId($GLOBALS['objObserver']->getStandardTelescope($_SESSION['deepskylog_id']),'name'))
      $pdf->addTextWrap($xleft, $footer, $xmid+$SectionBarWidth, 8, 
		    html_entity_decode(LangPDFMessage19 . $GLOBALS['objObserver']->getObserverProperty($_SESSION['deepskylog_id'],'firstname') . ' ' . 
				                   $GLOBALS['objObserver']->getObserverProperty($_SESSION['deepskylog_id'],'name') . ' ' .
		    LangPDFMessage20 . $GLOBALS['objInstrument']->getInstrumentPropertyFromId($GLOBALS['objObserver']->getStandardTelescope($_SESSION['deepskylog_id']),'name') . ' ' . 
				LangPDFMessage21 . $GLOBALS['objLocation']->getLocationPropertyFromId($GLOBALS['objObserver']->getStandardLocation($_SESSION['deepskylog_id']),'name')), 'center' );
		$pdf->addTextWrap($xleft, $header, $xmid+$SectionBarWidth, 10, html_entity_decode($_GET['pdfTitle']), 'center' );
		$pdf->addTextWrap($xmid+$SectionBarWidth-$sectionBarSpace-100, $header, 100, 8, LangPDFMessage22 . '1', 'right');
		while(list($key, $valueA) = each($result))
    {
			$mag = round($valueA['objectmagnitude'],1); if ($mag == 99.9) $mag = ""; else if ($mag - (int)$mag == 0.0) $mag = $mag.".0";
      $sb = round($valueA['objectsurfacebrightness'],1);  if ($sb == 99.9)  $sb = "";  else if ($sb - (int)$sb == 0.0)   $sb = $sb.".0";
      $pa = $valueA['objectpa'];          if($pa==999)      $pa="-";
			
      $con = $valueA['objectconstellation'];
      $type = $valueA['objecttype'];
      $atlas = $GLOBALS['objObserver']->getStandardAtlasCode($_SESSION['deepskylog_id']);
      $page = $valueA[$atlas];
      $diam1 = $valueA['objectdiam1'];
      $diam2 = $valueA['objectdiam2'];
      $size = "";
      if ($diam1 >= 40.0)
      { if (round($diam1 / 60.0) == ($diam1 / 60.0))
				{ if ($diam1 / 60.0 > 30.0)
            $size = sprintf("%.0f'", $diam1 / 60.0);
          else
            $size = sprintf("%.1f'", $diam1 / 60.0);
        }
				else
          $size = sprintf("%.1f'", $diam1 / 60.0);
				if ($diam2 != 0.0)
        { if (round($diam2 / 60.0) == ($diam2 / 60.0))
          { if ($diam2 / 60.0 > 30.0)
              $size = $size.sprintf("x%.0f'", $diam2 / 60.0);
            else
              $size = $size.sprintf("x%.1f'", $diam2 / 60.0);
          }
				  else
            $size = $size.sprintf("x%.1f'", $diam2 / 60.0);
				}
      }
      elseif ($diam1 != 0.0) 
      { $size = sprintf("%.1f''", $diam1);
        if ($diam2 != 0.0)
          $size = $size.sprintf("x%.1f''", $diam2);
      }
			$contrast = $valueA['objectcontrast'];
      if ($contrast == "-")
        $magnifi = "-"; 
			else 
			{ $magnifi = (int)$valueA['objectoptimalmagnification'];
        $contrast = round($valueA['objectcontrast'],1);  if ($contrast - (int)$contrast == 0.0) $contrast = $contrast.".0";
      }
			
      if(!$sort || ($actualsort!=$$sort))
			{ if($y<$bottom) 
  			{ $y=$top;
  			  if($xbase==$xmid)
  				{ if($pagenr++) 
					  { $pdf->newPage();
						  $pdf->addTextWrap($xleft, $header, 100, 8, $theDate);
							if($GLOBALS['objObserver']->getObserverProperty($_SESSION['deepskylog_id'],'name')
							&& $GLOBALS['objLocation']->getLocationPropertyFromId($GLOBALS['objObserver']->getStandardLocation($_SESSION['deepskylog_id']),'name')
							&& $GLOBALS['objInstrument']->getInstrumentPropertyFromId($GLOBALS['objObserver']->getStandardTelescope($_SESSION['deepskylog_id']),'name'))
						    $pdf->addTextWrap($xleft, $footer, $xmid+$SectionBarWidth, 8, 
		                   html_entity_decode(
		                   LangPDFMessage19 . $GLOBALS['objObserver']->getObserverProperty($_SESSION['deepskylog_id'],'name') . ' ' . 
		                                      $GLOBALS['objObserver']->getObserverProperty($_SESSION['deepskylog_id'],'firstname') . ' ' .
                       LangPDFMessage20 . $GLOBALS['objInstrument']->getInstrumentPropertyFromId($GLOBALS['objObserver']->getStandardTelescope($_SESSION['deepskylog_id']),'name') . ' ' . 
				               LangPDFMessage21 . $GLOBALS['objLocation']->getLocationPropertyFromId($GLOBALS['objObserver']->getStandardLocation($_SESSION['deepskylog_id']),'name')), 'center' );
		          $pdf->addTextWrap($xleft, $header, $xmid+$SectionBarWidth, 10, html_entity_decode($_GET['pdfTitle']), 'center' );
		          $pdf->addTextWrap($xmid+$SectionBarWidth-$sectionBarSpace-100, $header, 100, 8, LangPDFMessage22 . $pagenr, 'right');
  					}
						$xbase = $xleft;
  				}
  				else
  				{ $xbase = $xmid;
  				}
  			}
				if($sort)
				{ $y-=$deltalineSection;
          $pdf->rectangle($xbase-$sectionBarSpace, $y-$sectionBarSpace, $SectionBarWidth, $sectionBarHeight);
          $pdf->addText($xbase, $y, $fontSizeSection, $GLOBALS[$$sort]);  
          $y-=$deltaline+$deltalineSection;
				}
			}
      elseif($y<$bottomsection) 
			{ $y=$top;
			  if($xbase==$xmid)
				{ if($pagenr++) 
				  { $pdf->newPage();
					  $pdf->addTextWrap($xleft, $header, 100, 8, $theDate);
						if($GLOBALS['objObserver']->getObserverProperty($_SESSION['deepskylog_id'],'name')
						&& $GLOBALS['objLocation']->getLocationPropertyFromId($GLOBALS['objObserver']->getStandardLocation($_SESSION['deepskylog_id']),'name')
						&& $GLOBALS['objInstrument']->getInstrumentPropertyFromId($GLOBALS['objObserver']->getStandardTelescope($_SESSION['deepskylog_id']),'name'))
					    $pdf->addTextWrap($xleft, $footer, $xmid+$SectionBarWidth, 8, 
	                   html_entity_decode(LangPDFMessage19 . $GLOBALS['objObserver']->getObserverProperty($_SESSION['deepskylog_id'],'name') . ' ' .
	                                      $GLOBALS['objObserver']->getObserverProperty($_SESSION['deepskylog_id'],'firstname') . ' ' .
                     LangPDFMessage20 . $GLOBALS['objInstrument']->getInstrumentPropertyFromId($GLOBALS['objObserver']->getStandardTelescope($_SESSION['deepskylog_id']),'name') . ' ' . 
			               LangPDFMessage21 . $GLOBALS['objLocation']->getLocationPropertyFromId($GLOBALS['objObserver']->getStandardLocation($_SESSION['deepskylog_id']),'name')), 'center' );
            $pdf->addTextWrap($xleft, $header, $xmid+$SectionBarWidth, 10, html_entity_decode($_GET['pdfTitle']), 'center' );
	          $pdf->addTextWrap($xmid+$SectionBarWidth-$sectionBarSpace-100, $header, 100, 8, LangPDFMessage22 . $pagenr, 'right');
					}
					$xbase = $xleft;
          if($sort)
					{ $y-=$deltalineSection;
            $pdf->rectangle($xbase-$sectionBarSpace, $y-$sectionBarSpace, $SectionBarWidth, $sectionBarHeight);
            $pdf->addText($xbase, $y, $fontSizeSection, $GLOBALS[$$sort]);
            $y-=$deltaline+$deltalineSection;
					}
				}
				else
				{ $xbase = $xmid;
          if($sort)
					{ $y-=$deltalineSection;
            $pdf->rectangle($xbase-$sectionBarSpace, $y-$sectionBarSpace, $SectionBarWidth, $sectionBarHeight);
					  $pdf->addText($xbase, $y, $fontSizeSection, $GLOBALS[$$sort]);
            $y-=$deltaline+$deltalineSection;
					}
				}
			}
			if(!$sort)
			{ $pdf->addTextWrap($xbase    , $y,  30, $fontSizeText, $valueA['objectseen']);			                   // seen
			  $pdf->addTextWrap($xbase+ 30, $y,  40, $fontSizeText, $valueA['objectlastseen']);		                     // last seen	
			  $pdf->addTextWrap($xbase+ 70, $y,  85, $fontSizeText, '<b>'.
				  '<c:alink:'.$baseURL.'index.php?indexAction=detail_object&amp;object='.
					urlencode($valueA['objectname']).'>'.$valueA['showname']);		               //	object
			  $pdf->addTextWrap($xbase+150, $y,  30, $fontSizeText, '</c:alink></b>'.$type);			                 // type
			  $pdf->addTextWrap($xbase+180, $y,  20, $fontSizeText, $con);			                         // constellation
			  $pdf->addTextWrap($xbase+200, $y,  17, $fontSizeText, $mag, 'left');  	                 // mag
			  $pdf->addTextWrap($xbase+217, $y,  18, $fontSizeText, $sb, 'left');		                   // sb
			  $pdf->addTextWrap($xbase+235, $y,  60, $fontSizeText, raToStringHM($valueA['objectra']) . ' '.
				                                                      decToString($valueA['objectdecl'],0));	 // ra - decl
			  $pdf->addTextWrap($xbase+295, $y,  55, $fontSizeText, $size . '/' . $pa);			             // size
	  		$pdf->addTextWrap($xbase+351, $y,  17, $fontSizeText, $contrast, 'left');			             // contrast				
	  		$pdf->addTextWrap($xbase+368, $y,  17, $fontSizeText, $magnifi, 'left');			             // magnification				
			  $pdf->addTextWrap($xbase+380, $y,  20, $fontSizeText, '<b>'.$page.'</b>', 'right');			   // atlas page
      }
      else
			{ $pdf->addTextWrap($xbase    , $y,  30, $fontSizeText, $valueA['objectseen']);			                   // seen
			  $pdf->addTextWrap($xbase+ 30, $y,  40, $fontSizeText, $valueA['objectlastseen']);		                     // last seen	
			  $pdf->addTextWrap($xbase+ 70, $y, 100, $fontSizeText, '<b>'.
				  '<c:alink:'.$baseURL.'index.php?indexAction=detail_object&amp;object='.
					urlencode($valueA['objectname']).'>'.$valueA['showname']);		                                       //	object
			  $pdf->addTextWrap($xbase+170, $y,  30, $fontSizeText, '</c:alink></b>'.$type);			                 // type
			  $pdf->addTextWrap($xbase+200, $y,  17, $fontSizeText, $mag, 'left');			                 // mag
			  $pdf->addTextWrap($xbase+217, $y,  18, $fontSizeText, $sb, 'left');			                   // sb
			  $pdf->addTextWrap($xbase+235, $y,  60, $fontSizeText, raToStringHM($valueA['objectra']) . ' '.
				                                                      decToString($valueA['objectdecl'],0));	 // ra - decl
			  $pdf->addTextWrap($xbase+295, $y,  55, $fontSizeText, $size . '/' . $pa);         			   // size
	  		$pdf->addTextWrap($xbase+351, $y,  17, $fontSizeText, $contrast, 'left');			             // contrast				
	  		$pdf->addTextWrap($xbase+368, $y,  17, $fontSizeText, $magnifi, 'left');		               // magnification				
			  $pdf->addTextWrap($xbase+380, $y,  20, $fontSizeText, '<b>'.$page.'</b>', 'right');			   // atlas page
      }
			$y-=$deltaline;
      if($sort)
			  $actualsort = $$sort;
			if(array_key_exists('objectlistdescription',$valueA) && $valueA['objectlistdescription'])
      { $theText= $valueA['objectlistdescription'];
			  $theText= $pdf->addTextWrap($xbase+$descriptionLeadingSpace, $y, $xmid-$xleft-$descriptionLeadingSpace-10 ,$fontSizeText, '<i>'.$theText);
  			$y-=$deltaline;	
        while($theText)
				{ if($y<$bottomsection) 
			    { $y=$top;
			      if($xbase==$xmid)
				    { if($pagenr++)
						  { $pdf->newPage();
							  $pdf->addTextWrap($xleft, $header, 100, 8, $theDate);
								if($GLOBALS['objObserver']->getObserverProperty($_SESSION['deepskylog_id'],'name')
								&& $GLOBALS['objLocation']->getLocationPropertyFromId($GLOBALS['objObserver']->getStandardLocation($_SESSION['deepskylog_id']),'name')
								&& $GLOBALS['objInstrument']->getInstrumentPropertyFromId($GLOBALS['objObserver']->getStandardTelescope($_SESSION['deepskylog_id']),'name'))
							    $pdf->addTextWrap($xleft, $footer, $xmid+$SectionBarWidth, 8, 
		                   html_entity_decode(LangPDFMessage19 . $GLOBALS['objObserver']->getObserverProperty($_SESSION['deepskylog_id'],'name') . ' ' . 
		                                      $GLOBALS['objObserver']->getObserverProperty($_SESSION['deepskylog_id'],'firstname') . 
                       LangPDFMessage20 . $GLOBALS['objInstrument']->getInstrumentPropertyFromId($GLOBALS['objObserver']->getStandardTelescope($_SESSION['deepskylog_id']),'name') . ' ' . 
				               LangPDFMessage21 . $GLOBALS['objLocation']->getLocationPropertyFromId($GLOBALS['objObserver']->getStandardLocation($_SESSION['deepskylog_id']),'name')), 'center' );
		            $pdf->addTextWrap($xleft, $header, $xmid+$SectionBarWidth, 10, html_entity_decode($_GET['pdfTitle']), 'center' );
		            $pdf->addTextWrap($xmid+$SectionBarWidth-$sectionBarSpace-100, $header, 100, 8, LangPDFMessage22 . $pagenr, 'right');
          	  }
						  $xbase = $xleft;
              if($sort)
							{ $y-=$deltalineSection;
                $pdf->rectangle($xbase-$sectionBarSpace, $y-$sectionBarSpace, $SectionBarWidth, $sectionBarHeight);
                $pdf->addText($xbase, $y, $fontSizeSection, $GLOBALS[$$sort]);
                $y-=$deltaline+$deltalineSection;
							}
				    }
				    else
				    { $xbase = $xmid;
              if($sort)
							{ $y-=$deltalineSection;
                $pdf->rectangle($xbase-$sectionBarSpace, $y-$sectionBarSpace, $SectionBarWidth, $sectionBarHeight);
					      $pdf->addText($xbase, $y, $fontSizeSection, $GLOBALS[$$sort]);
                $y-=$deltaline+$deltalineSection;
							}
				    }
			    }
				$theText= $pdf->addTextWrap($xbase+$descriptionLeadingSpace, $y, $xmid-$xleft-$descriptionLeadingSpace-10 ,$fontSizeText, $theText);
  			$y-=$deltaline;	
				}
			  $pdf->addText(0,0,10,'</i>');
			}
			elseif(array_key_exists('objectdescription',$valueA) && $valueA['objectdescription'])
      { $theText= $valueA['objectdescription'];
			  $theText= $pdf->addTextWrap($xbase+$descriptionLeadingSpace, $y, $xmid-$xleft-$descriptionLeadingSpace-10 ,$fontSizeText, '<i>'.$theText);
  			$y-=$deltaline;	
        while($theText)
				{ if($y<$bottomsection) 
			    { $y=$top;
			      if($xbase==$xmid)
				    { if($pagenr++)
						  { $pdf->newPage();
							  $pdf->addTextWrap($xleft, $header, 100, 8, $theDate);
								if($GLOBALS['objObserver']->getObserverProperty($_SESSION['deepskylog_id'],'name')
								&& $GLOBALS['objLocation']->getLocationPropertyFromId($GLOBALS['objObserver']->getStandardLocation($_SESSION['deepskylog_id']),'name')
								&& $GLOBALS['objInstrument']->getInstrumentPropertyFromId($GLOBALS['objObserver']->getStandardTelescope($_SESSION['deepskylog_id']),'name'))
							    $pdf->addTextWrap($xleft, $footer, $xmid+$SectionBarWidth, 8, 
		                   html_entity_decode(LangPDFMessage19 . $GLOBALS['objObserver']->getObserverProperty($_SESSION['deepskylog_id'],'name') . ' ' . 
		                                      $GLOBALS['objObserver']->getObserverProperty($_SESSION['deepskylog_id'],'firstname') . 
                       LangPDFMessage20 . $GLOBALS['objInstrument']->getInstrumentPropertyFromId($GLOBALS['objObserver']->getStandardTelescope($_SESSION['deepskylog_id']),'name') . ' ' . 
				               LangPDFMessage21 . $GLOBALS['objLocation']->getLocationPropertyFromId($GLOBALS['objObserver']->getStandardLocation($_SESSION['deepskylog_id']),'name')), 'center' );
		            $pdf->addTextWrap($xleft, $header, $xmid+$SectionBarWidth, 10, html_entity_decode($_GET['pdfTitle']), 'center' );
		            $pdf->addTextWrap($xmid+$SectionBarWidth-$sectionBarSpace-100, $header, 100, 8, LangPDFMessage22 . $pagenr, 'right');
          	  }
						  $xbase = $xleft;
              if($sort)
							{ $y-=$deltalineSection;
                $pdf->rectangle($xbase-$sectionBarSpace, $y-$sectionBarSpace, $SectionBarWidth, $sectionBarHeight);
                $pdf->addText($xbase, $y, $fontSizeSection, $GLOBALS[$$sort]);
                $y-=$deltaline+$deltalineSection;
							}
				    }
				    else
				    { $xbase = $xmid;
              if($sort)
							{ $y-=$deltalineSection;
                $pdf->rectangle($xbase-$sectionBarSpace, $y-$sectionBarSpace, $SectionBarWidth, $sectionBarHeight);
					      $pdf->addText($xbase, $y, $fontSizeSection, $GLOBALS[$$sort]);
                $y-=$deltaline+$deltalineSection;
							}
				    }
			    }
				$theText= $pdf->addTextWrap($xbase+$descriptionLeadingSpace, $y, $xmid-$xleft-$descriptionLeadingSpace-10 ,$fontSizeText, $theText);
  			$y-=$deltaline;	
				}
			  $pdf->addText(0,0,10,'</i>');
			}			
		}		
    $pdf->Stream(); 
  }
  public function br2nl($data)  // The opposite of nl2br
  { return preg_replace( '!<br.*>!iU', " ", $data );
  }
  public function csvObservations($result)  // Creates a csv file from an array of observations
  { print LangCSVMessage3."\n";
    while(list ($key, $value) = each($result))
    { $obs = $GLOBALS['objObservation']->getAllInfoDsObservation($value['observationid']);
      $objectname = $obs["name"];
      $observerid = $obs["observer"];
      $inst = $obs["instrument"];
      $loc = $obs["location"];
      $date = sscanf($obs["date"], "%4d%2d%2d");
      $time = $obs["time"];
      $langObs = $obs["language"];
      $filt = $obs["filter"];
      $eyep = $obs["eyepiece"];
      $lns = $obs["lens"];

      if ($time >= "0")
      {
        $hours = (int)($time / 100);
        $minutes = $time - 100 * $hours;
        $time = sprintf("%d:%02d", $hours, $minutes);
      }
      else
      {
        $time = "";
      }
      $description = $this->br2nl(html_entity_decode($obs["description"]));
      $description = preg_replace("/;/", ",", $description);
      $visibility = $obs["visibility"];
      if ($visibility == "0")
      {
        $visibility = "";
      }
      $name = $GLOBALS['objObserver']->getObserverProperty($obs['observer'],'firstname'). " ".$GLOBALS['objObserver']->getObserverProperty($obs["observer"],'name');
      $seeing = $GLOBALS['objObservation']->getDsObservationProperty($value['observationid'],'seeing');
      $limmag = $GLOBALS['objObservation']->getDsObservationProperty($value['observationid'],'limmag');
      $description = preg_replace("/(\r\n|\n|\r)/", "", $description);
      $description = preg_replace("/(\")/", "", $description);
      echo (html_entity_decode($objectname) . ";" . html_entity_decode($name) . ";" . $date[2] . "-" . $date[1] . "-" . $date[0] . ";" . $time . ";" . html_entity_decode($GLOBALS['objLocation']->getLocationPropertyFromId($loc,'name')) . ";" . html_entity_decode($GLOBALS['objInstrument']->getInstrumentPropertyFromId($inst,'name')) . ";" . html_entity_decode($GLOBALS['objEyepiece']->getEyepiecePropertyFromId($eyep,'name')) . ";" . html_entity_decode($GLOBALS['objFilter']->getFilterPropertyFromId($filt,'name')) . ";" . html_entity_decode($GLOBALS['objLens']->getLensPropertyFromId($lns,'name')) . ";" . $seeing . ";" . $limmag . ";" . $visibility . ";" . $langObs . ";" . $description . "\n");
    }
  }
  public function csvObjects($result)  // Creates a csv file from an array of objects
  { print html_entity_decode(LangCSVMessage7)."\n";

    while(list ($key, $valueA) = each($result))
    { $alt="";
      $alts = $GLOBALS['objObject']->getAlternativeNames($valueA['objectname']);
      $first = true;
      while(list($key,$value)=each($alts))
      if($value!=$valueA['objectname'])
      if ($first)
      {
        $alt = $value;
        $first = false;
      } else
      {
  		    $alt = $alt . " - " . $value;
      }
      $mag = $valueA['objectmagnitude'];
      if ($mag == 99.9)
      $mag = "";
      else if ($mag - (int)$mag == 0.0)
      $mag = $mag.".0";
      $sb = $valueA['objectsurfacebrightness'];
      if ($sb == 99.9)
      $sb = "";
      else if ($sb - (int)$sb == 0.0)
      $sb = $sb.".0";
      $con = $valueA['objectconstellation'];
      $pa = $valueA['objectpa'];
      if($pa==999)
      $pa="";
      $type = $valueA['objecttype'];
      $atlas = $GLOBALS['objObserver']->getStandardAtlasCode($_SESSION['deepskylog_id']);
      $page = $valueA[$atlas];
      $diam1 = $valueA['objectdiam1'];
      $diam2 = $valueA['objectdiam2'];
      $size = "";
      if ($diam1 != 0.0)
      if ($diam1 >= 40.0)
      {
        if (round($diam1 / 60.0) == ($diam1 / 60.0))
        if ($diam1 / 60.0 > 30.0)
        $size = sprintf("%.0f'", $diam1 / 60.0);
        else
        $size = sprintf("%.1f'", $diam1 / 60.0);
        else
        $size = sprintf("%.1f'", $diam1 / 60.0);
        if ($diam2 != 0.0)
        if (round($diam2 / 60.0) == ($diam2 / 60.0))
        if ($diam2 / 60.0 > 30.0)
        $size = $size.sprintf("x%.0f'", $diam2 / 60.0);
        else
        $size = $size.sprintf("x%.1f'", $diam2 / 60.0);
        else
        $size = $size.sprintf("x%.1f'", $diam2 / 60.0);
      }
      else
      {
        $size = sprintf("%.1f''", $diam1);
        if ($diam2 != 0.0)
        $size = $size.sprintf("x%.1f''", $diam2);
      }

      if ($valueA['objectcontrast'] == "-")
      {
        $magnifi = "-";
      } else {
        $magnifi = (int)$valueA['objectoptimalmagnification'];
      }

      echo $valueA['objectname'].";". $alt .";".raToString($valueA['objectra']).";".decToString($valueA['objectdecl'], 0).";".$GLOBALS[$con].";".$GLOBALS[$type].";".$mag.";".$sb.";".$size.";".$pa.";".$page.";".$valueA['objectcontrast'].";".$magnifi.";".$valueA['objectseen'].";".$valueA['objectlastseen']."\n";
    }
  }
  public function argoObjects($result)  // Creates an argo navis file from an array of objects
  { $counter = 0;
    while(list ($key, $valueA) = each($result))
    { $mag = $valueA['objectmagnitude'];
      if ($mag == 99.9)
        $mag = "";
      else if ($mag - (int)$mag == 0.0)
        $mag = $mag.".0";
      $sb = $valueA['objectsurfacebrightness'];
      if ($sb == 99.9)
        $sb = "";
      else if ($sb - (int)$sb == 0.0)
        $sb = $sb.".0";
      $con = $valueA['objectconstellation'];
      $argotype = "argo".$valueA['objecttype'];
      $atlas = $GLOBALS['objObserver']->getStandardAtlasCode($_SESSION['deepskylog_id']);
      $page = $valueA[$atlas];
      $size = "";
			
      $diam1 = $valueA['objectdiam1'];
      $diam2 = $valueA['objectdiam2'];
      if ($diam1!=0.0)
        if ($diam1>=40.0)
        { if (round($diam1 / 60.0) == ($diam1 / 60.0))
            if ($diam1 / 60.0 > 30.0)
              $size = sprintf("%.0f'", $diam1 / 60.0);
            else
              $size = sprintf("%.1f'", $diam1 / 60.0);
          else
            $size = sprintf("%.1f'", $diam1 / 60.0);
          if ($diam2 != 0.0)
            if (round($diam2 / 60.0) == ($diam2 / 60.0))
              if ($diam2 / 60.0 > 30.0)
                $size = $size.sprintf("x%.0f'", $diam2 / 60.0);
              else
                $size = $size.sprintf("x%.1f'", $diam2 / 60.0);
            else
              $size = $size.sprintf("x%.1f'", $diam2 / 60.0);
        }
        else
        { $size = sprintf("%.1f''", $diam1);
          if ($diam2 != 0.0)
            $size = $size.sprintf("x%.1f''", $diam2);
        }
      echo "DSL " /*. sprintf("%03d", $counter). " " */. $valueA['objectname']."|".raArgoToString($valueA['objectra'])."|".decToArgoString($valueA['objectdecl'], 0)."|".$GLOBALS[$argotype]."|".$mag."|".$size.";".$atlas." ".$page.";CR ".$valueA['objectcontrast'].";".$valueA['objectseen'].";".$valueA['objectlastseen']."\n";
      $counter++;
    }
  }
  public function pdfObservations($result) // Creates a pdf document from an array of observations
  { global $AND,$ANT,$APS,$AQR,$AQL,$ARA,$ARI,$AUR,$BOO,$CAE,$CAM,$CNC,$CVN,$CMA,$CMI,$CAP,$CAR,$CAS,$CEN,$CEP,$CET,$CHA,$CIR,$COL,$COM,$CRA,$CRB,$CRV,$CRT,$CRU,
    $CYG,$DEL,$DOR,$DRA,$EQU,$ERI,$FOR,$GEM,$GRU,$HER,$HOR,$HYA,$HYI,$IND,$LAC,$LEO,$LMI,$LEP,$LIB,$LUP,$LYN,$LYR,$MEN,$MIC,$MON,$MUS,$NOR,$OCT,$OPH,
    $ORI,$PAV,$PEG,$PER,$PHE,$PIC,$PSC,$PSA,$PUP,$PYX,$RET,$SGE,$SGR,$SCO,$SCL,$SCT,$SER,$SEX,$TAU,$TEL,$TRA,$TRI,$TUC,$UMA,$UMI,$VEL,$VIR,$VOL,$VUL;

    global $ASTER,$BRTNB,$CLANB,$DRKNB,$GALCL,$GALXY,$GLOCL,$GXADN,$GXAGC,$GACAN,$LMCCN,$LMCDN,$LMCGC,$LMCOC,$NONEX,$OPNCL,$PLNNB,
    $SMCCN,$SMCDN,$SMCGC,$SMCOC,$SNREM,$QUASR,$AA1STAR,$AA2STAR,$AA3STAR,$AA4STAR,$AA8STAR;

    global $EMINB,$REFNB,$ENRNN,$ENSTR,$HII,$RNHII,$STNEB,$WRNEB;

    global $deepskylive, $dateformat;
    
    global $instDir, $objObservation;

    // Create pdf file
    $pdf = new Cezpdf('a4', 'portrait');
    $pdf->ezStartPageNumbers(300, 30, 10);

    $fontdir = realpath('lib/fonts/Helvetica.afm');
    //$pdf->selectFont($fontdir);
    $pdf->selectFont('lib/fonts/Helvetica.afm');
    $pdf->ezText(html_entity_decode($_GET['pdfTitle'])."\n");

    while(list ($key, $value) = each($result))
    { $obs = $GLOBALS['objObservation']->getAllInfoDsObservation($value['observationid']);
      $objectname = $obs["name"];
      $object = $GLOBALS['objObject']->getAllInfoDsObject($objectname);
      $type = $object["type"];
      $con = $object["con"];
      $observerid = $obs["observer"];
      $inst = $obs["instrument"];
      $loc = $obs["location"];
      $visibility = $obs["visibility"];
      $seeing = $obs["seeing"];
      $limmag = $obs["limmag"];
      $filt = $obs["filter"];
      $eyep = $obs["eyepiece"];
      $lns = $obs["lens"];

      if(array_key_exists('deepskylog_id',$_SESSION) && $_SESSION['deepskylog_id'] && ($GLOBALS['objObserver']->getUseLocal($_SESSION['deepskylog_id'])))
        $date = sscanf($obs["localdate"], "%4d%2d%2d");

      else
        $date = sscanf($obs["date"], "%4d%2d%2d");
      $description = $this->br2nl(html_entity_decode($obs["description"]));

      $formattedDate = date($dateformat, mktime(0,0,0,$date[1],$date[2],$date[0]));
      $visstr=""; $sstr = ""; $lstr = ""; $filtstr=""; $eyepstr=""; $lnsstr="";
      if     ($seeing == 1) $seeingstr = SeeingExcellent;
      elseif ($seeing == 2) $seeingstr = SeeingGood;
      elseif ($seeing == 3) $seeingstr = SeeingModerate;
      elseif ($seeing == 4) $seeingstr = SeeingPoor;
      elseif ($seeing == 5) $seeingstr = SeeingBad;
      if     ($visibility == 1) $visstr = LangVisibility1;
      elseif ($visibility == 2) $visstr = LangVisibility2;
      elseif ($visibility == 3) $visstr = LangVisibility3;
      elseif ($visibility == 4) $visstr = LangVisibility4;
      elseif ($visibility == 5) $visstr = LangVisibility5;
      elseif ($visibility == 6) $visstr = LangVisibility6;
      elseif ($visibility == 7) $visstr = LangVisibility7;
      if($seeing) $sstr = LangViewObservationField6." : ".$seeingstr;
      if($limmag) $lstr = LangViewObservationField7." : ".$limmag;
      if($filt)   $filtstr = LangViewObservationField31. " : " . $GLOBALS['objFilter']->getFilterPropertyFromId($filt,'name');
      if($eyep)   $eyepstr = LangViewObservationField30. " : " .$GLOBALS['objEyepiece']->getEyepiecePropertyFromId($eyep,'name');
      if($lns)    $lnsstr = LangViewObservationField32 . " : " . $GLOBALS['objLens']->getLensPropertyFromId($lns,'name');
      $temp = array("Name" => html_entity_decode(LangPDFMessage1)." : ".$objectname,
                 "altname" => html_entity_decode(LangPDFMessage2)." : ".$object["altname"],
                 "type" => $$type.html_entity_decode(LangPDFMessage12).$$con,
                 "visibility" => html_entity_decode(LangViewObservationField22)." : ".$visstr,
                 "seeing" => $sstr,
                 "limmag" => $lstr, 
                 "filter" => $filtstr,
                 "eyepiece" => $eyepstr,
								 "lens" => $lnsstr,
                 "observer" => html_entity_decode(LangPDFMessage13).$GLOBALS['objObserver']->getObserverProperty($observerid,'firstname')." ".$GLOBALS['objObserver']->getObserverProperty($observerid,'name').html_entity_decode(LangPDFMessage14).$formattedDate,
                 "instrument" => html_entity_decode(LangPDFMessage11)." : ".$GLOBALS['objInstrument']->getInstrumentPropertyFromId($inst,'name'),
                 "location" => html_entity_decode(LangPDFMessage10)." : ".$GLOBALS['objLocation']->getLocationPropertyFromId($loc,'name'),
                 "description" => $description,
                 "desc" => html_entity_decode(LangPDFMessage15)
      );
      $obs1[] = $temp;
      $nm=$objectname;
      if($object["altname"])
        $nm=$nm." (".$object["altname"].")";
      $pdf->ezText($nm, "14");
      $pdf->ezTable($tmp=array(array("type"=>$temp["type"])),array("type" => html_entity_decode(LangPDFMessage5)),"", array("width" => "500", "showHeadings" => "0", "showLines" => "0", "shaded" => "0"));
      $pdf->ezTable($tmp=array(array("location"=>$temp["location"], "instrument"=>$temp["instrument"])), array("location" => html_entity_decode(LangPDFMessage1), "instrument" => html_entity_decode(LangPDFMessage2)), "",  array("width" => "500", "showHeadings" => "0", "showLines" => "0", "shaded" => "0"));
      if ($eyep)      $pdf->ezTable($tmp=array(array("eyepiece"=>$temp["eyepiece"])), array("eyepiece" => "test"), "", array("width" => "500", "showHeadings" => "0", "showLines" => "0", "shaded" => "0"));
      if($filt)       $pdf->ezTable($tmp=array(array("filter"=>$temp["filter"])), array("filter" => "test"), "", array("width" => "500", "showHeadings" => "0", "showLines" => "0", "shaded" => "0"));
      if($lns)        $pdf->ezTable($tmp=array(array("lens"=>$temp["lens"])), array("lens" => "test"), "", array("width" => "500", "showHeadings" => "0", "showLines" => "0", "shaded" => "0"));
      if($seeing)     $pdf->ezTable($tmp=array(array("seeing"=>$temp["seeing"])), array("seeing" => "test"), "", array("width" => "500", "showHeadings" => "0", "showLines" => "0", "shaded" => "0"));
      if($limmag)     $pdf->ezTable($tmp=array(array("limmag"=>$temp["limmag"])), array("limmag" => "test"), "", array("width" => "500", "showHeadings" => "0", "showLines" => "0", "shaded" => "0"));
      if($visibility) $pdf->ezTable($tmp=array(array("visibility"=>$temp["visibility"])), array("visibility" => "test"), "", array("width" => "500", "showHeadings" => "0", "showLines" => "0", "shaded" => "0"));
      $pdf->ezTable($tmp=array(array("observer"=>$temp["observer"])), array("observer" => html_entity_decode(LangPDFMessage1)), "", array("width" => "500", "showHeadings" => "0", "showLines" => "0", "shaded" => "0"));
      //   $pdf->ezText(LangPDFMessage15, "12");
      //   $pdf->ezTable($obs1,
      //         array("desc" => LangPDFMessage1), "",
      //               array("width" => "500", "showHeadings" => "0",
      //                     "showLines" => "0", "shaded" => "0", "fontSize" => "12"));
      $pdf->ezText("");
      $pdf->ezTable($tmp=array(array("description"=>$temp["description"])), array("description" => html_entity_decode(LangPDFMessage1)), "", array("width" => "500", "showHeadings" => "0", "showLines" => "0", "shaded" => "0"));
      if($objObservation->getDsObservationProperty($value['observationid'],'hasDrawing'))
      { $pdf->ezText("");
        $pdf->ezImage($upload_dir."/".$value['observationid'].".jpg", 0, 500, "none", "left");
      }
      $pdf->ezText("");
      $pdf->ezNewPage();
    }
    $pdf->ezStream();
  }
  public function pdfCometObservations($result)// Creates a pdf document from an array of comet observations
  { include_once "cometobjects.php";
    include_once "observers.php";
    include_once "instruments.php";
    include_once "locations.php";
    include_once "cometobservations.php";
    include_once "ICQMETHOD.php";
    include_once "ICQREFERENCEKEY.php";
    include_once "setup/vars.php";
    include_once "setup/databaseInfo.php";

    $objects = new CometObjects;
    $observer = new Observers;
    $instrument = new Instruments;
    $observation = new CometObservations;
    $location = new Locations;
    $util = new Util;
    $ICQMETHODS = new ICQMETHOD();
    $ICQREFERENCEKEYS = new ICQREFERENCEKEY();
    $_GET['pdfTitle']="CometObservations.pdf";
    // Create pdf file
    $pdf = new Cezpdf('a4', 'portrait');
    $pdf->ezStartPageNumbers(300, 30, 10);

    $fontdir = $GLOBALS['instDir'].'lib/fonts/Helvetica.afm';
    $pdf->selectFont($fontdir);
    $pdf->ezText(html_entity_decode(LangPDFTitle3)."\n");

    while(list ($key, $value) = each($result))
    {
      $objectname = $GLOBALS['objCometObject']->getName($observation->getObjectId($value));

      $pdf->ezText($objectname, "14");

      $observerid = $observation->getDsObservationProperty($value,'observerid');

      if ($observer->getUseLocal($_SESSION['deepskylog_id']))
      {
        $date = sscanf($observation->getLocalDate($value), "%4d%2d%2d");
        $time = $observation->getLocalTime($value);
      }
      else
      {
        $date = sscanf($observation->getDate($value), "%4d%2d%2d");
        $time = $observation->getDsObservationProperty($value,'time');
      }

      $hour = (int)($time / 100);
      $minute = $time - $hour * 100;
      $formattedDate = date($GLOBALS['dateformat'], mktime(0,0,0,$date[1],$date[2],$date[0]));

      if ($minute < 10)
      {
        $minute = "0".$minute;
      }

      $observername = LangPDFMessage13.$observer->getObserverProperty($observerid,'firstname')." ".$observer->getObserverProperty($observerid,'name').html_entity_decode(LangPDFMessage14).$formattedDate." (".$hour.":".$minute.")";

       
      $pdf->ezText($observername, "12");


      // Location and instrument
      if (($observation->getLocationId($value) != 0 && $observation->getLocationId($value) != 1) || $observation->getInstrumentId($value) != 0)
      {
        if ($observation->getLocationId($value) != 0 && $observation->getLocationId($value) != 1)
        {
          $locationname = LangPDFMessage10." : ".$location->getLocationPropertyFromId($observation->getLocationId($value,'name'));
          $extra = ", ";
        }
        else
        {
          $locationname = "";
        }

        if ($observation->getInstrumentId($value) != 0)
        {
          $instr = $instrument->getInstrumentPropertyFromId($observation->getInstrumentId($value),'name');
          if ($instr == "Naked eye")
          {
            $instr = InstrumentsNakedEye;
          }

          $locationname = $locationname.$extra.html_entity_decode(LangPDFMessage11)." : ".$instr;

          if (strcmp($observation->getMagnification($value), "") != 0)
          {
            $locationname = $locationname." (".$observation->getMagnification($value)." x)";
          }
        }

        $pdf->ezText($locationname, "12");
      }

      // Methode
      $method = $observation->getMethode($value);

      if (strcmp($method, "") != 0)
      {
        $methodstr = html_entity_decode(LangViewObservationField15)." : ".$method." - ".$ICQMETHODS->getDescription($method);

        $pdf->ezText($methodstr, "12");
      }

      // Used chart
      $chart = $observation->getChart($value);

      if (strcmp($chart, "") != 0)
      {
        $chartstr = html_entity_decode(LangViewObservationField17)." : ".$chart." - ".$ICQREFERENCEKEYS->getDescription($chart);

        $pdf->ezText($chartstr, "12");
      }

      // Magnitude
      $magnitude = $observation->getMagnitude($value);

      if ($magnitude != -99.9)
      {
        $magstr = "";

        if ($observation->getMagnitudeWeakerThan($value))
        {
          $magstr = $magstr.LangNewComet3." ";
        }
        $magstr = $magstr.html_entity_decode(LangViewObservationField16)." : ".sprintf("%.01f", $magnitude);

        if ($observation->getMagnitudeUncertain($value))
        {
          $magstr = $magstr." (".LangNewComet2.")";
        }

        $pdf->ezText($magstr, "12");
      }
       
      // Degree of condensation
      $dc = $observation->getDc($value);
      $coma = $observation->getComa($value);

      $dcstr = "";
      $extra = "";

      if (strcmp($dc, "") != 0 || $coma != -99)
      {
        if (strcmp($dc, "") != 0)
        {
          $dcstr = $dcstr.html_entity_decode(LangNewComet8)." : ".$dc;
          $extra = ", ";
        }

        // Coma

        if ($coma != -99)
        {
          $dcstr = $dcstr.$extra.html_entity_decode(LangNewComet9)." : ".$coma."'";
        }

        $pdf->ezText($dcstr, "12");
      }

      // Tail
      $tail = $observation->getTail($value);
      $pa = $observation->getPa($value);

      $tailstr = "";
      $extra = "";

      if ($tail != -99 || $pa != -99)
      {
        if ($tail != -99)
        {
          $tailstr = $tailstr.html_entity_decode(LangNewComet10)." : ".$tail."'";
          $extra = ", ";
        }

        if ($pa != -99)
        {
          $tailstr = $tailstr.$extra.html_entity_decode(LangNewComet11)." : ".$pa."";
        }

        $pdf->ezText($tailstr, "12");
      }

      // Description
      $description = $observation->getDescription($value);

      if (strcmp($description, "") != 0)
      {
        $descstr = html_entity_decode(LangPDFMessage15)." : ".strip_tags($description);
        $pdf->ezText($descstr, "12");
      }


      $upload_dir = $GLOBALS['instDir'].'comets/'.'cometdrawings';
      $dir = opendir($upload_dir);

      while (FALSE !== ($file = readdir($dir)))
      {
        if ("." == $file OR ".." == $file)
        {
          continue; // skip current directory and directory above
        }
        if(fnmatch($value . ".gif", $file) ||
        fnmatch($value . ".jpg", $file) ||
        fnmatch($value. ".png", $file))
        {
          $pdf->ezImage($upload_dir . "/" . $value . ".jpg", 0, 500, "none", "left");
        }
      }

      $pdf->ezText("");
    }

    $pdf->ezStream();
  }
  public function utiltiesDispatchIndexAction()
  { if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('adapt_observation'                  ,'deepsky/content/change_observation.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('add_csv'                            ,'deepsky/content/new_observationcsv.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('add_xml'                            ,'deepsky/content/new_observationxml.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('add_object'                         ,'deepsky/content/new_object.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('add_observation'                    ,'deepsky/content/new_observation.php'))) 
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll(   'detail_object'                      ,'deepsky/content/view_object.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll(   'detail_observation'                 ,'deepsky/content/view_observation.php'))) 
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('import_csv_list'                    ,'deepsky/content/new_listdatacsv.php')))  
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll(   'listaction'                         ,'deepsky/content/tolist.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAdmin( 'manage_csv_object'                  ,'deepsky/content/manage_objects_csv.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll(   'query_objects'                      ,'deepsky/content/setup_objects_query.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll(   'query_observations'                 ,'deepsky/content/setup_observations_query.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll(   'rank_objects'                       ,'deepsky/content/top_objects.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll(   'rank_observers'                     ,'deepsky/content/top_observers.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll(   'result_query_objects'               ,'deepsky/content/execute_query_objects.php'))) 
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll(   'result_selected_observations'       ,'deepsky/content/selected_observations2.php')))  
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll(   'view_image'                         ,'deepsky/content/show_image.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll(   'view_observer_catalog'              ,'deepsky/content/details_observer_catalog.php')))
    
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('common_content_change_account'      ,'common/content/change_account.php')))          // former account_details
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('adapt_eyepiece'                     ,'common/content/change_eyepiece.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('adapt_filter'                       ,'common/content/change_filter.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('adapt_instrument'                   ,'common/content/change_instrument.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('adapt_lens'                         ,'common/content/change_lens.php')))	  
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('adapt_site'                         ,'common/content/change_site.php')))		
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('add_eyepiece'                       ,'common/content/new_eyepiece.php')))		 
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('add_filter'                         ,'common/content/new_filter.php')))		
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('add_instrument'                     ,'common/content/new_instrument.php'))) 		
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('add_lens'                           ,'common/content/new_lens.php')))		
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('add_site'                           ,'common/content/new_site.php'))) 		
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('detail_eyepiece'                    ,'common/content/view_eyepiece.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('detail_filter'                      ,'common/content/view_filter.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll   ('detail_instrument'                  ,'common/content/view_instrument.php')))		
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('detail_lens'                        ,'common/content/view_lens.php')))		
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll   ('detail_location'                    ,'common/content/view_location.php')))		
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll   ('detail_observer'                    ,'common/content/view_observer.php')))		
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll   ('message'                            ,'common/content/message.php')))		
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('search_sites'                       ,'common/content/search_locations.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('site_result'                        ,'common/content/getLocation.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll   ('subscribe'                          ,'common/content/register.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('validate_lens'                      ,'common/control/validate_lens.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('view_eyepieces'                     ,'common/content/overview_eyepieces.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('view_filters'                       ,'common/content/overview_filters.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('view_instruments'                   ,'common/content/overview_instruments.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('view_lenses'                        ,'common/content/overview_lenses.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('view_locations'                     ,'common/content/overview_locations.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('view_observers'                     ,'common/content/overview_observers.php')))
    
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll   ('comets_all_observations'            ,'comets/content/overview_observations.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll   ('comets_detail_object'               ,'comets/content/view_object.php'))) 
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll   ('comets_detail_observation'          ,'comets/content/view_observation.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('comets_adapt_observation'           ,'comets/content/change_observation.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('comets_add_observation'             ,'comets/content/new_observation.php')))   
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll   ('comets_result_query_observations'   ,'comets/content/selected_observations.php')))   
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll   ('comets_detail_observation'          ,'comets/content/view_observation.php')))   
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('comets_add_object'                  ,'comets/content/new_object.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll   ('comets_detail_object'               ,'comets/content/view_object.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll   ('comets_view_objects'                ,'comets/content/overview_objects.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll   ('comets_all_observations'            ,'comets/content/overview_observations.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll   ('comets_result_query_objects'        ,'comets/content/execute_query_objects.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll   ('comets_result_selected_observations','comets/content/selected_observations2.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll   ('comets_rank_observers'              ,'comets/content/top_observers.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll   ('comets_rank_objects'                ,'comets/content/top_objects.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll   ('comets_query_observations'          ,'comets/content/setup_observations_query.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionall   ('comets_query_objects'               ,'comets/content/setup_objects_query.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionDSquickPick()))
      $indexActionInclude=$this->utilitiesGetIndexActionDefaultAction();
    return $indexActionInclude;
  }
  public function comastObservations($result)  // Creates a csv file from an array of observations
  { 
    include_once "cometobjects.php";
    include_once "observers.php";
    include_once "instruments.php";
    include_once "locations.php";
    include_once "lenses.php";
    include_once "filters.php";
    include_once "cometobservations.php";
    include_once "ICQMETHOD.php";
    include_once "ICQREFERENCEKEY.php";
    include_once "setup/vars.php";
    include_once "setup/databaseInfo.php";

    $observer = $GLOBALS['objObserver'];
	$location = $GLOBALS['objLocation'];
	
  	$dom = new DomDocument('1.0', 'ISO-8859-1');

	$observers = array();
	$sites = array();
	$objects = array();
	$scopes = array();
    $eyepieces = array();
	$lenses = array();
	$filters = array();

    $cntObservers = 0;
    $cntSites = 0;
    $cntObjects = 0;
	$cntScopes = 0;
	$cntEyepieces = 0;
	$cntLens = 0;
	$cntFilter = 0;
	
	$allObs = $result;
	
    while(list ($key, $value) = each($result))
    {
      $obs = $GLOBALS['objObservation']->getAllInfoDsObservation($value['observationid']);
      $objectname = $obs["name"];
      $observerid = $obs["observer"];
      $inst = $obs["instrument"];
      $loc = $obs["location"];
      $visibility = $obs["visibility"];
      $seeing = $obs["seeing"];
      $limmag = $obs["limmag"];
      $filt = $obs["filter"];
      $eyep = $obs["eyepiece"];
      $lns = $obs["lens"];

      if (in_array($observerid, $observers) == false) {
      	$observers[$cntObservers] = $observerid;
      	$cntObservers = $cntObservers + 1;
      }

      if (in_array($loc, $sites) == false) {
      	$sites[$cntSites] = $loc;
      	$cntSites = $cntSites + 1;
      }
      
      if (in_array($objectname, $objects) == false) {
      	$objects[$cntObjects] = $objectname;
      	$cntObjects = $cntObjects + 1;
      }

      if (in_array($inst, $scopes) == false) {
      	$scopes[$cntScopes] = $inst;
      	$cntScopes = $cntScopes + 1;
      }

      if (in_array($eyep, $eyepieces) == false) {
      	$eyepieces[$cntEyepieces] = $eyep;
      	$cntEyepieces = $cntEyepieces + 1;
      }

      if (in_array($lns, $lenses) == false) {
      	$lenses[$cntLens] = $lns;
      	$cntLens = $cntLens + 1;
      }

      if (in_array($filt, $filters) == false) {
      	$filters[$cntFilter] = $filt;
      	$cntFilter = $cntFilter + 1;
      }
    }

	// add root fcga -> The header
	$fcgaInfo = $dom->createElement('fgca:observations');
	$fcgaDom = $dom->appendChild($fcgaInfo);

    $attr = $dom->createAttribute("version");
    $fcgaInfo->appendChild($attr);

//    $attrText = $dom->createTextNode("2.0");
    $attrText = $dom->createTextNode("1.7");
    $attr->appendChild($attrText);

    $attr = $dom->createAttribute("xmlns:fgca");
    $fcgaInfo->appendChild($attr);

    $attrText = $dom->createTextNode("http://observation.sourceforge.net/comast");
    $attr->appendChild($attrText);

    $attr = $dom->createAttribute("xmlns:xsi");
    $fcgaInfo->appendChild($attr);

    $attrText = $dom->createTextNode("http://www.w3.org/2001/XMLSchema-instance");
    $attr->appendChild($attrText);

    $attr = $dom->createAttribute("xsi:schemaLocation");
    $fcgaInfo->appendChild($attr);

//    $attrText = $dom->createTextNode("http://observation.sourceforge.net/comast comast20.xsd");
    $attrText = $dom->createTextNode("http://observation.sourceforge.net/comast comast17.xsd");
    $attr->appendChild($attrText);

    //add root - <observers> 
    $observersDom = $fcgaDom->appendChild($dom->createElement('observers')); 

	while(list($key, $value) = each($observers)) 
	{
      $observer2 = $dom->createElement('observer');
      $observerChild = $observersDom->appendChild($observer2);
      $attr = $dom->createAttribute("id");
      $observer2->appendChild($attr);

	  $correctedValue = utf8_encode(html_entity_decode(preg_replace( "/\s+/", "_", $value )));
	  $attrText = $dom->createTextNode("usr_".$correctedValue);
	  $attr->appendChild($attrText);

      $name = $observerChild->appendChild($dom->createElement('name')); 
      $name->appendChild($dom->createCDATASection(utf8_encode(html_entity_decode($observer->getObserverProperty($value,'firstname'))))); 
      
      $surname = $observerChild->appendChild($dom->createElement('surname')); 
      $surname->appendChild($dom->createCDataSection(($observer->getObserverProperty($value,'name')))); 

//      $account = $observerChild->appendChild($dom->createElement('account'));
//      $account->appendChild($dom->createCDataSection(utf8_encode(html_entity_decode($value))));

//      $attr = $dom->createAttribute("name");
//      $account->appendChild($attr);

//      $attrText = $dom->createTextNode("www.deepskylog.org");
//      $attr->appendChild($attrText);
    }
    
    //add root - <sites> 
    $observersDom = $fcgaDom->appendChild($dom->createElement('sites')); 

	while(list($key, $value) = each($sites)) 
	{
      $site2 = $dom->createElement('site');
      $siteChild = $observersDom->appendChild($site2);
      $attr = $dom->createAttribute("id");
      $site2->appendChild($attr);

	  $attrText = $dom->createTextNode("site_" . $value);
	  $attr->appendChild($attrText);

      $name = $siteChild->appendChild($dom->createElement('name')); 
      $name->appendChild($dom->createCDATASection(utf8_encode(html_entity_decode($location->getLocationPropertyFromId($value,'name'))))); 

      $longitude = $siteChild->appendChild($dom->createElement('longitude')); 
      $longitude->appendChild($dom->createTextNode($location->getLocationPropertyFromId($value,'longitude'))); 

      $attr = $dom->createAttribute("unit");
      $longitude->appendChild($attr);

	  $attrText = $dom->createTextNode("deg");
	  $attr->appendChild($attrText);


      $latitude = $siteChild->appendChild($dom->createElement('latitude')); 
      $latitude->appendChild($dom->createTextNode($location->getLocationPropertyFromId($value,'latitude'))); 

      $attr = $dom->createAttribute("unit");
      $latitude->appendChild($attr);

	  $attrText = $dom->createTextNode("deg");
	  $attr->appendChild($attrText);


      $timezone = $siteChild->appendChild($dom->createElement('timezone'));
      $dateTimeZone = new DateTimeZone($location->getLocationPropertyFromId($value,'timezone'));
	  $datestr = "01/01/2008";
	  $dateTime = new DateTime($datestr, $dateTimeZone);
	  // Geeft tijdsverschil terug in seconden
	  $timedifference = $dateTimeZone->getOffset($dateTime);
	  $timedifference = $timedifference / 60.0; 
      $timezone->appendChild($dom->createTextNode($timedifference)); 
    }

    //add root - <sessions>  DeepskyLog has no sessions
    $observersDom = $fcgaDom->appendChild($dom->createElement('sessions')); 

    //add root - <targets> 
    $observersDom = $fcgaDom->appendChild($dom->createElement('targets')); 

	while(list($key, $value) = each($objects)) 
	{
      $object2 = $dom->createElement('target');
      $objectChild = $observersDom->appendChild($object2);
      $attr = $dom->createAttribute("id");
      $object2->appendChild($attr);

	  $correctedValue = utf8_encode(html_entity_decode(preg_replace( "/\s+/", "_", $value )));
	  $correctedValue = utf8_encode(html_entity_decode(preg_replace( "/\+/", "_", $correctedValue )));
	  $correctedValue = utf8_encode(html_entity_decode(preg_replace( "/\//", "_", $correctedValue )));
	  $correctedValue = utf8_encode(html_entity_decode(preg_replace( "/\,/", "_", $correctedValue )));

	  $attrText = $dom->createTextNode("_" . $correctedValue);
	  $attr->appendChild($attrText);

      $attr = $dom->createAttribute("xsi:type");
      $object2->appendChild($attr);

      $object = $GLOBALS['objObject']->getAllInfoDsObject($value);

	  $type = $object["type"];
	  if ($type == "OPNCL" || $type == "SMCOC" || $type == "LMCOC")
	  {
	  	$type = "fgca:deepSkyOC";
	  } else if ($type == "GALXY" || $type == "GALCL") {
	  	$type = "fgca:deepSkyGX";
	  } else if ($type == "PLNNB") {
	  	$type = "fgca:deepSkyPN";
	  } else if ($type == "ASTER" || $type == "AA1STAR" || $type == "AA2STAR" 
	      || $type == "AA3STAR" || $type == "AA4STAR" || $type == "AA8STAR"
	      || $type == "DS") {
	  	$type = "fgca:deepSkyAS";
	  } else if ($type == "GLOCL" || $type == "GXAGC" || $type == "LMCGC" 
	      || $type == "SMCGC") {
	  	$type = "fgca:deepSkyGC";
	  } else if ($type == "BRTNB" || $type == "CLANB" || $type == "EMINB"
	      || $type == "ENRNN" || $type == "ENSTR" || $type == "GXADN"
	      || $type == "GACAN" || $type == "HII" || $type == "LMCCN"
	      || $type == "LMCDN" || $type == "REFNB" || $type == "RNHII"
	      || $type == "SMCCN" || $type == "SMCDN" || $type == "SNREM"
	      || $type == "STNEB" || $type == "WRNEB") {
	  	$type = "fgca:deepSkyGN";
	  } else if ($type == "QUASR") {
	  	$type = "fgca:deepSkyQS";
	  } else if ($type == "DRKNB") {
	  	$type = "fgca:deepSkyDN";
	  } else if ($type == "NONEX") {
	  	$type = "fgca:deepSkyNA";
	  }
	  $attrText = $dom->createTextNode($type);
	  $attr->appendChild($attrText);

      $datasource = $objectChild->appendChild($dom->createElement('datasource')); 
      $datasource->appendChild($dom->createCDATASection(utf8_encode(html_entity_decode($object["datasource"])))); 
      
      $name = $objectChild->appendChild($dom->createElement('name')); 
      $name->appendChild($dom->createCDATASection(($value)));
      
      $altnames = $GLOBALS['objObject']->getAlternativeNames($value);
      while(list($key2, $value2) = each($altnames)) // go through names array
  	  { if(trim($value2)!=trim($value))
  	  	{
  	  	  if (trim($value2) != "") {
            $alias = $objectChild->appendChild($dom->createElement('alias')); 
            $alias->appendChild($dom->createCDataSection((trim($value2))));
  	  	  }
  	  	} 
      }

      $position = $objectChild->appendChild($dom->createElement('position')); 

	  $raDom = $dom->createElement('ra');
      $ra = $position->appendChild($raDom); 
      $ra->appendChild($dom->createTextNode($object["ra"] * 15.0));

      $attr = $dom->createAttribute("unit");
      $raDom->appendChild($attr);

	  $attrText = $dom->createTextNode("deg");
	  $attr->appendChild($attrText);

	  $decDom = $dom->createElement('dec');
      $dec = $position->appendChild($decDom); 
      $dec->appendChild($dom->createTextNode($object["decl"]));

      $attr = $dom->createAttribute("unit");
      $decDom->appendChild($attr);

	  $attrText = $dom->createTextNode("deg");
	  $attr->appendChild($attrText);
	  
	  $constellation = $objectChild->appendChild($dom->createElement('constellation')); 
      $constellation->appendChild($dom->createCDATASection(($object["con"])));

  	  if ($object["diam2"] > 0.0 && $object["diam2"] != 99.9) {
	  	$sdDom = $dom->createElement('smallDiameter');
	  	$diam2 = $objectChild->appendChild($sdDom);
	  	$sDiameter = $object["diam2"] / 60.0;
      	$diam2->appendChild($dom->createTextNode($sDiameter));

        $attr = $dom->createAttribute("unit");
        $sdDom->appendChild($attr);

	    $attrText = $dom->createTextNode("arcmin");
	    $attr->appendChild($attrText);
	  }

      $diameter1 = $object["diam1"];
	  if ($diameter1 > 0.0 && $diameter1 != 99.9) {
	  	$ldDom = $dom->createElement('largeDiameter');
	  	$diam1 = $objectChild->appendChild($ldDom);
	  	$lDiameter = $diameter1 / 60.0;
      	$diam1->appendChild($dom->createTextNode($lDiameter));

        $attr = $dom->createAttribute("unit");
        $ldDom->appendChild($attr);

	    $attrText = $dom->createTextNode("arcmin");
	    $attr->appendChild($attrText);
	  }

	  if ($object["mag"] < 99.0) {
	  	$mag = $objectChild->appendChild($dom->createElement('visMag')); 
      	$mag->appendChild($dom->createTextNode(($object["mag"])));
	  }
	  
	  if ($object["subr"] < 99.0) {
	  	$mag = $objectChild->appendChild($dom->createElement('surfBr')); 
      	$mag->appendChild($dom->createTextNode(($object["subr"])));
	  }

	  if ($object["pa"] < 999.0) {
	  	$pa = $objectChild->appendChild($dom->createElement('pa')); 
      	$pa->appendChild($dom->createTextNode(($object["pa"])));
	  }
	}
    //add root - <scopes> 
    $observersDom = $fcgaDom->appendChild($dom->createElement('scopes')); 

	while(list($key, $value) = each($scopes)) 
	{
      $scope2 = $dom->createElement('scope');
      $siteChild = $observersDom->appendChild($scope2);
      $attr = $dom->createAttribute("id");
      $scope2->appendChild($attr);

	  $attrText = $dom->createTextNode("opt_" . $value);
	  $attr->appendChild($attrText);

      $attr = $dom->createAttribute("xsi:type");
      $scope2->appendChild($attr);

	  if ($GLOBALS['objInstrument']->getInstrumentPropertyFromId($value,'fixedMagnification') > 0) {
	  	$typeLong = "fgca:fixedMagnificationOpticsType";
	  } else {
	  	$typeLong = "fgca:scopeType";	  	
	  }
	  $tp = $GLOBALS['objInstrument']->getInstrumentPropertyFromId($value,'type');
	  if ($tp == InstrumentOther || $tp == InstrumentRest) {
	  	$typeShort = "";
	  } else if ($tp == InstrumentNakedEye) {
	  	$typeShort = "A";
	  } else if ($tp == InstrumentBinoculars || $tp == InstrumentFinderscope) {
	  	$typeShort = "B";
	  } else if ($tp == InstrumentRefractor) {
	  	$typeShort = "R";
	  } else if ($tp == InstrumentReflector) {
	  	$typeShort = "N";
	  } else if ($tp == InstrumentCassegrain) {
	  	$typeShort = "C";
	  } else if ($tp == InstrumentKutter) {
	  	$typeShort = "K";
	  } else if ($tp == InstrumentMaksutov) {
	  	$typeShort = "M";
	  } else if ($tp == InstrumentSchmidtCassegrain) {
	  	$typeShort = "S";
	  }

	  $attrText = $dom->createTextNode($typeLong);
	  $attr->appendChild($attrText);

      $name = $siteChild->appendChild($dom->createElement('model')); 
      $name->appendChild($dom->createCDATASection(utf8_encode(html_entity_decode($GLOBALS['objInstrument']->getInstrumentPropertyFromId($value,'name'))))); 

      $type = $siteChild->appendChild($dom->createElement('type')); 
      $type->appendChild($dom->createCDATASection(($typeShort))); 

      $aperture = $siteChild->appendChild($dom->createElement('aperture')); 
      $aperture->appendChild($dom->createTextNode(($GLOBALS['objInstrument']->getInstrumentPropertyFromId($value,'diameter')))); 

	  if ($GLOBALS['objInstrument']->getInstrumentPropertyFromId($value,'fixedMagnification') > 0) {
      	$magnification = $siteChild->appendChild($dom->createElement('magnification'));
        $magnification->appendChild($dom->createTextNode(($GLOBALS['objInstrument']->getInstrumentPropertyFromId($value,'fixedMagnification')))); 
	  } else {
      	$focalLength = $siteChild->appendChild($dom->createElement('focalLength'));
        $focalLength->appendChild($dom->createTextNode(($GLOBALS['objInstrument']->getInstrumentPropertyFromId($value,'fixedMagnification')) * $GLOBALS['objInstrument']->getInstrumentPropertyFromId($value,'diameter'))); 
	  }
    }

    //add root - <eyepieces> 
    $observersDom = $fcgaDom->appendChild($dom->createElement('eyepieces')); 

	while(list($key, $value) = each($eyepieces)) 
	{
	  if ($value != "" && $value > 0) {
        $eyepiece2 = $dom->createElement('eyepiece');
        $eyepieceChild = $observersDom->appendChild($eyepiece2);
        $attr = $dom->createAttribute("id");
        $eyepiece2->appendChild($attr);

	    $attrText = $dom->createTextNode("ep_" . $value);
	    $attr->appendChild($attrText);

        $model = $eyepieceChild->appendChild($dom->createElement('model')); 
        $model->appendChild($dom->createCDATASection(utf8_encode(html_entity_decode($GLOBALS['objEyepiece']->getEyepiecePropertyFromId($value,'name'))))); 

        $focalLength = $eyepieceChild->appendChild($dom->createElement('focalLength')); 
        $focalLength->appendChild($dom->createTextNode(($GLOBALS['objEyepiece']->getEyepiecePropertyFromId($value,'focalLength'))));

		if ($GLOBALS['objEyepiece']->getEyepiecePropertyFromId($value,'maxFocalLength') > 0) {
          $maxFocalLength = $eyepieceChild->appendChild($dom->createElement('maxFocalLength')); 
          $maxFocalLength->appendChild($dom->createTextNode(($GLOBALS['objEyepiece']->getEyepiecePropertyFromId($value,'maxFocalLength'))));
		}

        $apparentFOV = $eyepieceChild->appendChild($dom->createElement('apparentFOV')); 
        $apparentFOV->appendChild($dom->createTextNode(($GLOBALS['objEyepiece']->getEyepiecePropertyFromId($value,'apparentFOV'))));

        $attr = $dom->createAttribute("unit");
        $apparentFOV->appendChild($attr);

	    $attrText = $dom->createTextNode("deg");
	    $attr->appendChild($attrText);
      }
    }

    //add root - <lenses> 
    $observersDom = $fcgaDom->appendChild($dom->createElement('lenses')); 

	while(list($key, $value) = each($lenses)) 
	{
	  if ($value != "" && $value > 0) {
        $lens2 = $dom->createElement('lens');
        $lensChild = $observersDom->appendChild($lens2);
        $attr = $dom->createAttribute("id");
        $lens2->appendChild($attr);

	    $attrText = $dom->createTextNode("le_" . $value);
	    $attr->appendChild($attrText);

        $model = $lensChild->appendChild($dom->createElement('model')); 
        $model->appendChild($dom->createCDATASection(utf8_encode(html_entity_decode($GLOBALS['objLens']->getLensPropertyFromId($value,'name'))))); 

        $factor = $lensChild->appendChild($dom->createElement('factor')); 
        $factor->appendChild($dom->createTextNode(($GLOBALS['objLens']->getFilterPropertyFromId($value,'factor'))));
      }
    }

    //add root - <filters> 
    $observersDom = $fcgaDom->appendChild($dom->createElement('filters')); 

	while(list($key, $value) = each($filters)) 
	{
	  if ($value != "" && $value > 0) {
        $filter2 = $dom->createElement('filter');
        $filterChild = $observersDom->appendChild($filter2);
        $attr = $dom->createAttribute("id");
        $filter2->appendChild($attr);

	    $attrText = $dom->createTextNode("flt_" . $value);
	    $attr->appendChild($attrText);
 
        $model = $filterChild->appendChild($dom->createElement('model')); 
        $model->appendChild($dom->createCDATASection(utf8_encode(html_entity_decode($GLOBALS['objFilter']->getFilterPropertyFromId($value,'name'))))); 

		$tp = $GLOBALS['objFilter']->getFilterPropertyFromId($value,'type');
		if ($tp == 0) {
			$filType = "other";
		} else if ($tp == 1) {
			$filType = "broad band";
		} else if ($tp == 2) {
			$filType = "narrow band";
		} else if ($tp == 3) {
			$filType = "O-III";
		} else if ($tp == 4) {
			$filType = "H-beta";
		} else if ($tp == 5) {
			$filType = "H-alpha";
		} else if ($tp == 6) {
			$filType = "color";
		} else if ($tp == 7) {
			$filType = "neutral";
		} else if ($tp == 8) {
			$filType = "corrective";
		}

        $type = $filterChild->appendChild($dom->createElement('type')); 
        $type->appendChild($dom->createCDATASection($filType));

		if ($filType == "color") {
			$col = $GLOBALS['objFilter']->getFilterPropertyFromId($value,'color');
			if ($col == 1) {
				$colName = "light red";
			} else if ($col == 2) {
				$colName = "red";
			} else if ($col == 3) {
				$colName = "deep red";
			} else if ($col == 4) {
				$colName = "orange";
			} else if ($col == 5) {
				$colName = "light yellow";
			} else if ($col == 6) {
				$colName = "deep yellow";
			} else if ($col == 7) {
				$colName = "yellow";
			} else if ($col == 8) {
				$colName = "yellow-green";
			} else if ($col == 9) {
				$colName = "light green";
			} else if ($col == 10) {
				$colName = "green";
			} else if ($col == 11) {
				$colName = "medium blue";
			} else if ($col == 12) {
				$colName = "pale blue";
			} else if ($col == 13) {
				$colName = "blue";
			} else if ($col == 14) {
				$colName = "deep blue";
			} else if ($col == 15) {
				$colName = "violet";
			} 
			if ($colName != "") {
              $color = $filterChild->appendChild($dom->createElement('color')); 
              $color->appendChild($dom->createCDATASection($colName));
			}
			
			if ($GLOBALS['objFilter']->getFilterPropertyFromId($value,'wratten') != "") {
		      $wratten = $filterChild->appendChild($dom->createElement('wratten')); 
              $wratten->appendChild($dom->createCDATASection($GLOBALS['objFilter']->getFilterPropertyFromId($value,'wratten')));
			}

			if ($GLOBALS['objFilter']->getFilterPropertyFromId($value,'schott') != "") {
		      $schott = $filterChild->appendChild($dom->createElement('schott')); 
              $schott->appendChild($dom->createCDATASection($GLOBALS['objFilter']->getFilterPropertyFromId($value,'schott')));
			}
		}
      }
    }

    //add root - <imagers>  DeepskyLog has no imagers
    $observersDom = $fcgaDom->appendChild($dom->createElement('imagers')); 

	// Add the observations.
	while(list ($key, $value) = each($allObs))
    {
      $obs = $GLOBALS['objObservation']->getAllInfoDsObservation($value['observationid']);
      $objectname = $obs["name"];
      $observerid = $obs["observer"];
      $inst = $obs["instrument"];
      $loc = $obs["location"];
      $visibility = $obs["visibility"];
      $seeing = $obs["seeing"];
      $limmag = $obs["limmag"];
      $filt = $obs["filter"];
      $eyep = $obs["eyepiece"];
      $lns = $obs["lens"];

      $observation = $fcgaDom->appendChild($dom->createElement('observation')); 
	  $attr = $dom->createAttribute("id");
      $observation->appendChild($attr);

	  $attrText = $dom->createTextNode("obs_" . $value['observationid']);
	  $attr->appendChild($attrText);

	  $correctedValue = utf8_encode(html_entity_decode(preg_replace( "/\s+/", "_", $observerid )));
      $observer = $observation->appendChild($dom->createElement('observer')); 
      $observer->appendChild($dom->createTextNode("usr_" . $correctedValue));
	  
      $site = $observation->appendChild($dom->createElement('site')); 
      $site->appendChild($dom->createTextNode("site_" . $loc));

      $target = $observation->appendChild($dom->createElement('target')); 
      $correctedValue = utf8_encode(html_entity_decode(preg_replace( "/\s+/", "_", $objectname )));
	  $correctedValue = utf8_encode(html_entity_decode(preg_replace( "/\+/", "_", $correctedValue )));
	  $correctedValue = utf8_encode(html_entity_decode(preg_replace( "/\//", "_", $correctedValue )));
	  $correctedValue = utf8_encode(html_entity_decode(preg_replace( "/\,/", "_", $correctedValue )));
      
      $target->appendChild($dom->createTextNode("_" . $correctedValue));

	  if ($obs["time"] > 0)
	  {
	  	$time = sprintf("T%02d:%02d:00+00:00", (int)($obs["time"] / 100), $obs["time"] - (int)($obs["time"] / 100) * 100);
	  } else {
	  	$time = "T22:00:00+00:00";
	  }

	  $year = (int)($obs["date"] / 10000);
	  $month = (int)(($obs["date"] - $year * 10000) / 100);
	  $day = (int)(($obs["date"] - $year * 10000 - $month * 100));
	  $date = sprintf("%4d-%02d-%02d", $year, $month, $day);

      $begin = $observation->appendChild($dom->createElement('begin')); 
      $begin->appendChild($dom->createTextNode($date . $time));

	  if ($obs["limmag"] > 0) {
        $faintestStar = $observation->appendChild($dom->createElement('faintestStar')); 
        $faintestStar->appendChild($dom->createTextNode($obs["limmag"]));
	  } else if ($obs["sqm"] > 0) {
//        $magPerSquareArcsecond = $observation->appendChild($dom->createElement('magPerSquareArcsecond')); 
//        $magPerSquareArcsecond->appendChild($dom->createTextNode($obs["sqm"]));
	  }

	  if ($obs["seeing"] > 0) {
        $seeing = $observation->appendChild($dom->createElement('seeing')); 
        $seeing->appendChild($dom->createTextNode($obs["seeing"]));
	  }

      $scope = $observation->appendChild($dom->createElement('scope')); 
      $scope->appendChild($dom->createTextNode("opt_" . $inst));
 	  
 	  if ($eyep > 0) {
        $eyepiece = $observation->appendChild($dom->createElement('eyepiece')); 
        $eyepiece->appendChild($dom->createTextNode("ep_" . $eyep));
 	  }

	  if ($lns > 0) {
        $lens = $observation->appendChild($dom->createElement('lens')); 
        $lens->appendChild($dom->createTextNode("le_" . $lns));
	  }

	  if ($filt > 0) {
        $filter = $observation->appendChild($dom->createElement('filter')); 
        $filter->appendChild($dom->createTextNode("flt_" . $filt));
	  }

	  $magni = 0;
	  if ($GLOBALS['objInstrument']->getInstrumentPropertyFromId($inst,'fixedMagnification') > 0)
	  {
	  	$magni = $GLOBALS['objInstrument']->getInstrumentPropertyFromId($inst,'fixedMagnification');
	  } else if ($eyep > 0 && $GLOBALS['objInstrument']->getInstrumentPropertyFromId($inst,'fixedMagnification') > 0) {
	  	$factor = 1.0;
	  	if ($GLOBALS['objLens']->getFilterPropertyFromId($lns,'factor') > 0) {
	  		$factor = $GLOBALS['objLens']->getFilterPropertyFromId($lns,'factor');
	  	}
		$magni = sprintf("%.2f", $GLOBALS['objInstrument']->getInstrumentPropertyFromId($inst,'fixedMagnification') * $GLOBALS['objInstrument']->getInstrumentPropertyFromId($inst,'diameter') 
		        * $factor / $GLOBALS['objEyepiece']->getEyepiecePropertyFromId($eyep,'focalLength'));
	  }
	  
	  if ($magni > 0) {
        $magnification = $observation->appendChild($dom->createElement('magnification')); 
        $magnification->appendChild($dom->createTextNode($magni));
	  }

      $result = $observation->appendChild($dom->createElement('result'));

	  if ($obs["colorContrasts"] > 0)
	  {
	    $attr = $dom->createAttribute("colorContrasts");
        $result->appendChild($attr);

	    $attrText = $dom->createTextNode("true");
	    $attr->appendChild($attrText);
	  }
       
	  if ($obs["extended"] > 0)
	  {
	    $attr = $dom->createAttribute("extended");
        $result->appendChild($attr);

	    $attrText = $dom->createTextNode("true");
	    $attr->appendChild($attrText);
	  }

	  $attr = $dom->createAttribute("lang");
      $result->appendChild($attr);

	  $attrText = $dom->createTextNode($obs["language"]);
      $attr->appendChild($attrText);

	  if ($obs["mottled"] > 0)
	  {
	    $attr = $dom->createAttribute("mottled");
        $result->appendChild($attr);

	    $attrText = $dom->createTextNode("true");
	    $attr->appendChild($attrText);
	  }

	  if ($obs["partlyUnresolved"] > 0)
	  {
	    $attr = $dom->createAttribute("partlyUnresolved");
        $result->appendChild($attr);

	    $attrText = $dom->createTextNode("true");
	    $attr->appendChild($attrText);
	  }

	  if ($obs["resolved"] > 0)
	  {
	    $attr = $dom->createAttribute("resolved");
        $result->appendChild($attr);

	    $attrText = $dom->createTextNode("true");
	    $attr->appendChild($attrText);
	  }

	  if ($obs["stellar"] > 0)
	  {
	    $attr = $dom->createAttribute("stellar");
        $result->appendChild($attr);

	    $attrText = $dom->createTextNode("true");
	    $attr->appendChild($attrText);
	  }

	  if ($obs["unusualShape"] > 0)
	  {
	    $attr = $dom->createAttribute("unusualShape");
        $result->appendChild($attr);

	    $attrText = $dom->createTextNode("true");
	    $attr->appendChild($attrText);
	  }

	  $attr = $dom->createAttribute("xsi:type");
      $result->appendChild($attr);


      $object = $GLOBALS['objObject']->getAllInfoDsObject($objectname);

	  $type = $object["type"];
	  if ($type == "OPNCL" || $type == "SMCOC" || $type == "LMCOC")
	  {
	  	$type = "fgca:findingsDeepSkyOCType";
	  } else {
	  	$type = "fgca:findingsDeepSkyType";	  	
	  }
	  $attrText = $dom->createTextNode($type);
	  $attr->appendChild($attrText);

      $description = $result->appendChild($dom->createElement('description')); 
      $description->appendChild($dom->createCDATASection(utf8_encode($this->br2nl(html_entity_decode($obs["description"])))));

	  // TODO : Why is the rating mandatory? Set to 99 if not defined... Will be so in the upcoming version of comast. Should the visibility be made mandatory in DeepskyLog -> I guess so ;-)
      $rat = $obs["visibility"];
      if ($rat == 0) {
      	$rat = 3;
      }

      $rating = $result->appendChild($dom->createElement('rating')); 
      $rating->appendChild($dom->createTextNode($rat));

	  if ($obs["smallDiam"] > 0) {
/*        $smallDiameter = $result->appendChild($dom->createElement('smallDiameter')); 
        $smallDiameter->appendChild($dom->createTextNode($obs["smallDiam"]));

        $attr = $dom->createAttribute("unit");
        $smallDiameter->appendChild($attr);

        $attrText = $dom->createTextNode("arcsec");
	    $attr->appendChild($attrText);
*/	  }

  	  if ($obs["largeDiam"] > 0) {
/*        $largeDiameter = $result->appendChild($dom->createElement('largeDiameter')); 
        $largeDiameter->appendChild($dom->createTextNode($obs["largeDiam"]));

        $attr = $dom->createAttribute("unit");
        $largeDiameter->appendChild($attr);

        $attrText = $dom->createTextNode("arcsec");
	    $attr->appendChild($attrText);
*/	  }

	  if ($obs["characterType"] != "" && $obs["characterType"] != 0) {
        $character = $result->appendChild($dom->createElement('character')); 
        $character->appendChild($dom->createCDATASection($obs["characterType"]));
  	  }
    }

    //generate xml 
    $dom->formatOutput = true; // set the formatOutput attribute of 
                               // domDocument to true 
    // save XML as string or file 
    $test1 = $dom->saveXML(); // put string in test1 
 
  	print $test1;
  }

  public function utilitiesSetModuleCookie($module)
  { if((!array_key_exists('module',$_SESSION)) ||
     (array_key_exists('module',$_SESSION) && ($_SESSION['module'] != $module)))
    { $_SESSION['module'] = $module;
      $cookietime = time() + 365 * 24 * 60 * 60;     // 1 year
      setcookie("module",$module, $cookietime, "/");
    }
  }
	public function checkGetKey($key,$default='')
  { return (array_key_exists($key,$_GET)&&($_GET[$key]!=''))?$_GET[$key]:$default;
  }
	public function checkPostKey($key,$default='')
  { return (array_key_exists($key,$_POST)&&($_POST[$key]!=''))?$_POST[$key]:$default;
  }
	public function checkSessionKey($key,$default='')
  { return (array_key_exists($key,$_SESSION)&&($_SESSION[$key]!=''))?$_SESSION[$key]:$default;
  }
	public function checkArrayKey($theArray,$key,$default='')
  { return (array_key_exists($key,$theArray)&&($theArray[$key]!=''))?$theArray[$key]:$default;
  }
	public function checkGetKeyReturnString($key,$string,$default='')
  { return array_key_exists($key,$_GET)?$string:$default;
  }
  public function checkGetDate($year,$month,$day)
  { if($year=$this->checkGetKey($year))
      return sprintf("%04d",$year).sprintf("%02d",$this->checkGetKey($month,'00')).sprintf("%02d",$this->checkGetKey($day,'00'));
    elseif($month=$this->checkGetKey($month))
      return sprintf("%02d",$month).sprintf("%02d",$this->checkGetKey($day,'00'));
    else
  	  return '';
  }
  public function checkGetTimeOrDegrees($hr,$min,$sec)
  { if($this->checkGetKey($hr).$this->checkGetKey($min).$this->checkGetKey($sec))
      if(substr($this->checkGetKey($hr),0,1)=="-")
	      return -(abs($this->checkGetKey($hr,0))+($this->checkGetKey($min,0)/60)+($this->checkGetKey($sec,0)/3600));
			else
	      return $this->checkGetKey($hr,0)+($this->checkGetKey($min,0)/60)+($this->checkGetKey($sec,0)/3600);
  }
  public function promptWithLink($prompt,$promptDefault,$javaLink,$text)
	{ echo "<a href=\"\" onclick=\"thetitle = prompt('".addslashes($prompt)."','".addslashes($promptDefault)."'); location.href='".$javaLink."&amp;pdfTitle='+thetitle; return false;\"	target=\"new_window\">".$text."</a>";
  }
	public function checkLimitsInclusive($value,$low,$high)
	{ return(($value>=$low)&&($value<=$high));
	}
  public function checkAdminOrUserID($toCheck)
  { return array_key_exists('deepskylog_id', $_SESSION)&&$_SESSION['deepskylog_id']&&((array_key_exists('admin', $_SESSION)&&($_SESSION['admin']=="yes"))||($_SESSION['deepskylog_id']==$toCheck));
  }
	public function checkUserID($toCheck)
  { return array_key_exists('deepskylog_id', $_SESSION)&&$_SESSION['deepskylog_id']&&($_SESSION['deepskylog_id']==$toCheck);
  }
  public function searchAndLinkCatalogsInText($theText)
  { global $baseURL;
    $patterns[0]="/\s+(M)\s*(\d+)/";
		$replacements[0]="<a href=\"".$baseURL."index.php?indexAction=detail_object&amp;object=M%20\\2\">&nbsp;M&nbsp;\\2</a>";
		$patterns[1]= "/(NGC|Ngc|ngc)\s*(\d+\w+)/";
		$replacements[1]="<a href=\"".$baseURL."index.php?indexAction=detail_object&amp;object=NGC%20\\2\">NGC&nbsp;\\2</a>";
		$patterns[2]= "/(IC|Ic|ic)\s*(\d+)/";
		$replacements[2]="<a 	href=\"".$baseURL."index.php?indexAction=detail_object&amp;object=IC%20\\2\">IC&nbsp;\\2</a>";
		$patterns[3]= "/(Arp|ARP|arp)\s*(\d+)/";
		$replacements[3]="<a href=\"".$baseURL."index.php?indexAction=detail_object&amp;object=Arp%20\\2\">Arp&nbsp;\\2</a>";
		return preg_replace($patterns, $replacements, $theText);
  }
}
$objUtil=new Util();
?>
