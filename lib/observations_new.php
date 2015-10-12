<script type="text/javascript" src="https://cdn.datatables.net/1.10.9/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.9/css/jquery.dataTables.min.css" />

<script type="text/javascript">

	function format ( d ) {
		var result = '<table width="100%" cellpadding="5" cellspacing="0" border="0" style="padding-left:50px;">'+
	        '<tr>';

	    result += (d.observerimage == null)? '' : '<td colspan="2"><img height="72" src="'+d.observerimage+'" class="img-rounded pull-left"></td>';
		
	    result +='<td><?=LangViewObservationField4?></td>'+
            		 '<td>'+d.locationname+'</td>'+
            '</tr>'+
		        '<tr>'+
		            '<td><?=LangViewObservationField7?> / <?=LangViewObservationField34?></td>'+
		            '<td>'+d.limmag+' / '+d.sqm+'</td>'+
		            '<td><?=LangViewObservationField6?></td>'+
		            '<td>'+d.seeing+'</td>'+	
		            '<td></td>'+	
		            '<td></td>'+			            
		        '</tr>'+
		        '<tr>'+
		            '<td><?=LangViewObservationField30?></td>'+
		            '<td>'+d.eyepiecedescription+'</td>'+
		            '<td><?=LangViewObservationField31?></td>'+
		            '<td>'+d.filterdescription+'</td>'+		
		            '<td><?=LangViewObservationField32?></td>'+
		            '<td>'+d.lensdescription+'</td>'+	            
	        	'</tr>'+	
		        '<tr>'+
		            '<td><?=LangViewObservationField22?></td>'+
		            '<td>'+d.visibility+'</td>'+
		            '<td><?=LangViewObservationField33?></td>'+
		            '<td>'+d.size+'</td>'+		
		            '<td><?=LangViewObservationField40?></td>'+
		            '<td>'+d.clustertype+'</td>'+	            
	        	'</tr>'+		        			        
		        '<tr>'+
		            '<td colspan="6">'+d.description+'</td>'+
		        '</tr>'+
	    	'</table>';

	    	return result;
		}
		 
		$(document).ready(function() {
		    var dt = $('#example').DataTable( {
		        "ajax": "observations_json.php?object=<?=$_GET['object']?>",
	        "lengthMenu": [[10, 25, 50, 100, 1000, -1], [10, 25, 50, 100, 1000, "all"]],
	        "columns": [
	            {
	                "class":          "details-control",
	                "orderable":      false,
	                "data":           null,
	                "defaultContent": "",
	                
	            },
	            //format of data columns
	            { "data": "objectname" },
	            { "data": "constellation"},
	            { "data": function ( row, type, val, meta ) { return '<a href="?indexAction=detail_observer&user='+row.observerid+'">'+row.firstname+' '+row.name+'</a>' }},
	            { "data": function ( row, type, val, meta ) { return row.instrumentname+' ('+row.instrumentdiameter+')' } },
	            { "data": "sortdate", "visible": false},
		        { "data": "date", "orderData": 5 }
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

<table id="example" class="display">
	<thead>
    	<tr>
          <th></th>
          <th><?= LangOverviewObservationsHeader1 ?></th>
          <th><?= LangViewObservationField1b ?></th>
          <th><?= LangOverviewObservationsHeader2 ?></th>
          <th><?= LangOverviewObservationsHeader3 ?></th>
          <th></th>
          <th><?= LangOverviewObservationsHeader4 ?></th>
        </tr>
	</thead>
    <tfoot>
		<tr>
			<th></th>
			<th><?= LangOverviewObservationsHeader1 ?></th>
			<th><?= LangViewObservationField1b ?></th>
			<th><?= LangOverviewObservationsHeader2 ?></th>
			<th><?= LangOverviewObservationsHeader3 ?></th>
			<th></th>
			<th><?= LangOverviewObservationsHeader4 ?></th>
		</tr>
	</tfoot>
</table>	
