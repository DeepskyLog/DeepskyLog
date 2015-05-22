<?php
// date.php
// menu which allows the user to change the date
global $inIndex, $loggedUser, $objUtil;

if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
elseif (! ($loggedUser))
	throw new Exception ( LangExcpetion001 );
elseif (! ($objUtil->checkAdminOrUserID ( $loggedUser )))
	throw new Exception ( LangExcpetion012 );
else
	menu_date ();
function menu_date() {
	global $baseURL, $loggedUser, $thisDay, $thisMonth, $thisYear;
	$link = $baseURL . "index.php?";
	reset ( $_GET );
	while ( list ( $key, $value ) = each ( $_GET ) )
		if (! (in_array ( $key, array (
				'changeDay',
				'changeMonth',
				'changeYear' 
		) )))
			$link .= $key . '=' . urlencode ( $value ) . '&amp;';
	$link2 = "index.php?";
	reset ( $_GET );
	while ( list ( $key, $value ) = each ( $_GET ) )
		if (! (in_array ( $key, array (
				'changeDay',
				'changeMonth',
				'changeYear' 
		) )))
			$link2 .= $key . '=' . urlencode ( $value ) . '&';
	$link2 = substr ( $link2, 0, strlen ( $link2 ) - 1 );
	
	echo "<script>
			// Here we set the dates of the new moon.
			// How can we be sure that we only calculate the new moon for the displayed month?
            var eventDates = {};
			// Watch out: Here, we must use the American style date 
            eventDates[ new Date( '06/16/2015' )] = 1;
            eventDates[ new Date( '05/18/2015' )] = 1;

			$(function() {
			
			// An array of dates
			
              $( \"#datepicker\" ).datepicker({
	  		    showButtonPanel: true,
                changeMonth: true,
                changeYear: true,
			    beforeShowDay: function(date) {
                  var highlight = eventDates[date];
                  if (highlight) {
			        // TODO: Add to language file
			        return [true, \"event\", \"New Moon\"];
                  } else {
                    return [true, '', ''];
                  }
                },
			    // TODO: Make sure we use the correct date format
                dateFormat: \"dd/mm/yy\",
			    defaultDate: -7,
	  		    onSelect: function(dateText) {
	  		      var day = dateText.substring(0, 2);
	  		      var month = dateText.substring(3, 5);
	  		      var year = dateText.substring(6, 10);
	  		      var link = \"" . $link2 . "&changeDay=\" + day + \"&changeMonth=\" + month + \"&changeYear=\" + year;
	  		      location.href = link;
	            }
              });
        	});
	  		</script>";
	echo "<form class=\"nav navbar-nav navbar-right\">";
	
 	echo "<div class=\"form-group\">";
 	echo "<p class=\"navbar-text\">" . LangDate . " ";
    echo "<span class=\"form-inline\">";
	echo "<input class=\"form-control\" type=\"text\" value=\"" . $_SESSION ['globalDay'] . "/" . $_SESSION ['globalMonth'] . "/" . $_SESSION ['globalYear'] . "\" id=\"datepicker\" size=\"10\" >";
	echo "</span>";
 	echo "</p>";
 	echo "</div>";
	echo "</form>";
	$link = "";
}
?>
