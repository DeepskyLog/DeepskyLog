
		<script type="text/javascript" src="https://cdn.datatables.net/1.10.9/js/jquery.dataTables.min.js"></script>
		<!--script type="text/javascript" src="https://code.jquery.com/jquery-1.11.3.min.js"></script-->
		<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.9/css/jquery.dataTables.min.css" />
		
		<script type="text/javascript">
		

		function format ( d ) {
		    return d.description;
		}
		 
		$(document).ready(function() {
		    var dt = $('#example').DataTable( {
		        "ajax": "observations_json.php",
		        "columns": [
		            {
		                "class":          "details-control",
		                "orderable":      false,
		                "data":           null,
		                "defaultContent": "+"
		            },
		            //format of data columns
		            { "data": "objectname" },
		            { "data": "con"},
		            { "data": function ( row, type, val, meta ) { return '<a href="?indexAction=detail_observer&user='+row.observerid+'">'+row.firstname+' '+row.name+'</a>' }},
		            { "data": function ( row, type, val, meta ) { return row.instrumentname+' ('+row.instrumentdiameter+')' }},
		            { "data": "date" }
		        ],
		        "order": [[1, 'asc']]
		    } );
		 
		    // Array to track the ids of the details displayed rows
		    var detailRows = [];
		 
		    $('#example tbody').on( 'click', 'tr td.details-control', function () {
		        var tr = $(this).closest('tr');
		        var row = dt.row( tr );
		        var idx = $.inArray( tr.attr('id'), detailRows );
		 
		        if ( row.child.isShown() ) {
		            tr.removeClass( 'details' );
		            row.child.hide();
		 
		            // Remove from the 'open' array
		            detailRows.splice( idx, 1 );
		        }
		        else {
		            tr.addClass( 'details' );
		            row.child( format( row.data() ) ).show();
		 
		            // Add to the 'open' array
		            if ( idx === -1 ) {
		                detailRows.push( tr.attr('id') );
		            }
		        }
		    } );
		 
		    // On each draw, loop over the `detailRows` array and show any child rows
		    dt.on( 'draw', function () {
		        $.each( detailRows, function ( i, id ) {
		            $('#'+id+' td.details-control').trigger( 'click' );
		        } );
		    } );
		} );
		</script>

		<table id="example" class="display" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th></th>
                <th>Object naam</th>
                <th>Sterrenbeeld</th>
                <th>Waarnemer</th>
                <th>Instrument</th>
                <th>Datum</th>
            </tr>
        </thead>
 
        <tfoot>
            <tr>
                <th>Object naam</th>
                <th>Sterrenbeeld</th>
                <th>Waarnemer</th>
                <th>Instrument</th>
                <th>Datum</th>
            </tr>
        </tfoot>
    </table>	
