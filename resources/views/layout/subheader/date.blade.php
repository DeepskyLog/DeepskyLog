<span class="navbar-text">
    {{ _i('Date') }}
</span>
&nbsp;&nbsp;
<script>
    $( function() {
        $( "#datepicker" ).datepicker({
            showOtherMonths: true,
            selectOtherMonths: true,
            showButtonPanel: true,
            beforeShow: function() {
			      // Calculate the new moons for the selected month
			      // We calculated the new moon from the first of the month and from the 15th of the month.
	              $phases = array();
	              $date = $_SESSION['globalYear'] . "-" . $_SESSION ['globalMonth'] . "-01";
	              $phases = phasehunt(strtotime($date));
			      $newmoon = date("m/d/Y", $phases[4]);
			      echo "
			      eventDates[ new Date( '" . $newmoon . "' )] = 1;";

			      $phases = array();
			      $date = $_SESSION['globalYear'] . "-" . $_SESSION ['globalMonth'] . "-15";
			      $phases = phasehunt(strtotime($date));
			      $newmoon = date("m/d/Y", $phases[4]);

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

            dateFormat: "dd/mm/yy",
            defaultDate: -7
        });
    } );
</script>

<input class="form-control" type="text" value="" id="datepicker" size="10" >
