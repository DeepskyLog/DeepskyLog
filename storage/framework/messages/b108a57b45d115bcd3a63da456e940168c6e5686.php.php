<span class="navbar-text">
    <?php echo e(_i('Date')); ?>

</span>
&nbsp;&nbsp;
<script type="text/javascript" src="<?php echo e(URL::asset('js/degrees.js')); ?>"></script>
<script type="text/javascript" src="<?php echo e(URL::asset('js/astro.js')); ?>"></script>
<script type="text/javascript" src="<?php echo e(URL::asset('js/moon.js')); ?>"></script>
<script>
    var eventDates = {};

    $( function() {
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
                var queryDate = "<?php echo e(Session::get('date')); ?>";
                dateParts = queryDate.match(/(\d+)/g)
                realDate = new Date(dateParts[2], dateParts[1] - 1, dateParts[0]);

                $(this).datepicker('setDate', realDate);
			},
			beforeShowDay: function(date) {
                var highlight = eventDates[date];
                if (highlight) {
                    return [true, 'event', '<?php echo e(_i("New moon")); ?>'];
                } else {
                    return [true, '', ''];
                }
            },

            dateFormat: "dd/mm/yy",
            defaultDate: -7,
            onSelect: function(dateText) {

                $.ajax({
                    type: 'POST',
                    url: "/setSession",
                    data: { date: dateText }
                });
                location.href = window.location;
	        }
        });
    } );
</script>

<?php
    if (session('date')) {
        $date = session('date');
    } else {
        // Current date
        $datetime = new DateTime();
        $date = $datetime->format('d/m/Y');
        Session::put('date', $date);
    }
?>
<input class="form-control" type="text" value="<?php echo e($date); ?>" id="datepicker" size="10" >
