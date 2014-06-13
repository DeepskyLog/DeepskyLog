<?php 
// date.php
// menu which allows the user to change the date

global $inIndex,$loggedUser,$objUtil;

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!($loggedUser)) throw new Exception(LangExcpetion001);
elseif(!($objUtil->checkAdminOrUserID($loggedUser))) throw new Exception(LangExcpetion012);
else menu_date();

function menu_date()
{ global $baseURL,$loggedUser,$thisDay,$thisMonth,$thisYear;
	  $link=$baseURL."index.php?";
	  reset($_GET);
	  while(list($key,$value)=each($_GET))
	    if(!(in_array($key,array('changeDay','changeMonth','changeYear'))))
	      $link.=$key.'='.urlencode($value).'&amp;';
	  $link2="index.php?";
	  reset($_GET);
	  while(list($key,$value)=each($_GET))
	    if(!(in_array($key,array('changeDay','changeMonth','changeYear'))))
	      $link2.=$key.'='.urlencode($value).'&';
	  $link2=substr($link2,0,strlen($link2)-1);

	  echo "<script>
	  			  $(function() {
              $( \"#datepicker\" ).datepicker({
	  		        dateFormat: \"dd/mm/yy\",
	  		        showButtonPanel: true,
                changeMonth: true,
                changeYear: true,
	  		        defaultDate: -7,
	  		        onSelect: function(dateText) {
	  		          var day = dateText.substring(0, 2);
	  		          var month = dateText.substring(3, 5);
	  		          var year = dateText.substring(6, 10);
	  		          var link = \"" . $link2  . "&changeDay=\" + day + \"&changeMonth=\" + month + \"&changeYear=\" + year;
	  		          location.href = link;
	              }
              });
        	});
	  		  </script>";
	  echo "<ul class=\"nav navbar-nav navbar-right\">";
	   
	  echo "<p class=\"navbar-text\">" . LangDate . " ";
	
	  echo "<input type=\"text\" value=\"" . $_SESSION['globalDay'] . "/" . $_SESSION['globalMonth'] . "/" . $_SESSION['globalYear'] . "\" id=\"datepicker\" size=\"10\" >";
	  echo "</p></ul>";
	  $link="";
}
?>
