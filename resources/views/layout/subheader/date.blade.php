<span class="navbar-text">
    {{ _i('Date') }}
</span>
&nbsp;&nbsp;
<script type="text/javascript" src="{{ URL::asset('js/degrees.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('js/astro.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('js/moon.js') }}"></script>
<script>
    var eventDates = {};

    $( function() {
        $("#datepicker").data("selectedDate","02/26/2019");

        $( "#datepicker" ).datepicker({
            changeMonth: true,
            changeYear: true,
            showButtonPanel: true,
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
            beforeShow: function (input, inst) {
                var datePicked = new Date( $("#datepicker").data("selectedDate") );
                // This makes sure that the date is set, and that the onChangeMonthYear method is called.
                $(this).datepicker("setDate", new Date($("#datepicker").data("selectedDate")));
			},
			beforeShowDay: function(date) {
                var highlight = eventDates[date];
                if (highlight) {
                    return [true, 'event', '{{ _i("New moon") }}'];
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
