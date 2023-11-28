<?php

// date.php
// menu which allows the user to change the date
global $inIndex, $loggedUser, $objUtil;

if ((!isset($inIndex)) || (!$inIndex)) {
    include "../../redirect.php";
} elseif (!($objUtil->checkAdminOrUserID($loggedUser))) {
    throw new Exception(_("You need to be logged in to execute these operations."));
} else {
    menu_date();
}
function menu_date()
{
    global $baseURL, $loggedUser, $thisDay, $thisMonth, $thisYear;
    $link = $baseURL . "index.php?";
    reset($_GET);
    foreach ($_GET as $key => $value) {
        if (!(in_array($key, array(
                'changeDay',
                'changeMonth',
                'changeYear'
        )))) {
            $link .= $key . '=' . urlencode($value) . '&amp;';
        }
    }
    $link2 = "index.php?";
    reset($_GET);
    foreach ($_GET as $key => $value) {
        if (!(in_array($key, array(
                'changeDay',
                'changeMonth',
                'changeYear'
        )))) {
            $link2 .= $key . '=' . urlencode($value) . '&';
        }
    }
    $link2 = substr($link2, 0, strlen($link2) - 1);

    echo "<script type=\"text/javascript\" src=\"" . $baseURL . "lib/javascript/degrees.js\"></script>
          <script type=\"text/javascript\" src=\"" . $baseURL . "lib/javascript/astro.js\"></script>
          <script type=\"text/javascript\" src=\"" . $baseURL . "lib/javascript/moon.js\"></script>

		  <script>
			// Here we set the dates of the new moon.
			// How can we be sure that we only calculate the new moon for the displayed month?
            var eventDates = {};


			$(function() {

			// An array of dates

              $( \"#datepicker\" ).datepicker({
	  		    showButtonPanel: true,
                changeMonth: true,
                changeYear: true,
			    onChangeMonthYear: function(year, month) {
			      // Calculate all new moons for this month
			      // We use a method in javascript for this...
                  var moon = new MoonQuarters(year, month, 1);
                  var date = jdtocd(moon[0]);
                  eventDates[ new Date( date[1] + '/' + date[2] + '/'  + date[0] )] = 1;

          		  if (month - 1 < 1) {
          		    var moon = new MoonQuarters(year - 1, 12, 1);
          		  } else {
          		    var moon = new MoonQuarters(year, month - 1, 1);
          		  }
                  var date = jdtocd(moon[0]);
          		  eventDates[ new Date( date[1] + '/' + date[2] + '/'  + date[0] )] = 1;

           		  if (month + 1 > 12) {
           		    var moon = new MoonQuarters(year + 1, 1, 1);
           		  } else {
           		    var moon = new MoonQuarters(year, month + 1, 1);
           		  }
                  var date = jdtocd(moon[0]);
          		  eventDates[ new Date( date[1] + '/' + date[2] + '/'  + date[0] )] = 1;
			    },
			    beforeShow: function() {";
    // Calculate the new moons for the selected month
    // We calculated the new moon from the first of the month and from the 15th of the month.
    $phases = array();
    $date = $_SESSION['globalYear'] . "-" . $_SESSION ['globalMonth'] . "-01";
    $phases = phasehunt(strtotime($date));
    $newmoon = date("m/d/Y", (int)$phases[4]);
    echo "
			      eventDates[ new Date( '" . $newmoon . "' )] = 1;";

    $phases = array();
    $date = $_SESSION['globalYear'] . "-" . $_SESSION ['globalMonth'] . "-15";
    $phases = phasehunt(strtotime($date));
    $newmoon = date("m/d/Y", (int)$phases[4]);

    echo "
                  eventDates[ new Date( '" . $newmoon . "' )] = 1;
			    },
			    beforeShowDay: function(date) {
                  var highlight = eventDates[date];
                  if (highlight) {
			        return [true, \"event\", \"" . _("New moon") . "\"];
                  } else {
                    return [true, '', ''];
                  }
                },
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
    echo "<p class=\"navbar-text\">" . _('Date') . " ";
    echo "<span class=\"form-inline\">";
    echo "<input class=\"form-control\" type=\"text\" value=\"" . $_SESSION ['globalDay'] . "/" . $_SESSION ['globalMonth'] . "/" . $_SESSION ['globalYear'] . "\" id=\"datepicker\" size=\"10\" >";
    echo "</span>";
    echo "</p>";
    echo "</div>";
    echo "</form>";
    $link = "";
}
