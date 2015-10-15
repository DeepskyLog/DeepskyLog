<?php require_once 'lib/datatables_setup.php'; ?>

<script type="text/javascript">

	function format ( d ) {
		var result = '<table class="details" width="100%" cellpadding="5" cellspacing="0" border="0" style="padding-left:50px;">'+
	        '<tr>';

	    result += (d.observerimage == null)? '<td colspan="2"></td>' : '<td colspan="2"><img height="72" src="'+d.observerimage+'" class="img-rounded pull-left"></td>';
		result += '<td colspan="4">'+d.moonpic+'</td>'+
			'</tr>'+
			'<tr>'+
		    	'<td><?=LangViewObservationField4?></td>'+
            	'<td><a href="/index.php?indexAction=detail_location&location='+d.locationid+'">'+d.locationname+'</a></td>'+
		    	'<td><?=LangViewObservationField9?></td>'+
            	'<td>'+d.displaytime+'</td>'+
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
		            '<td>';					
						if(d.eyepieceid != 0){
			            	result += '<a href="index.php?indexAction=detail_eyepiece&eyepiece='+d.eyepieceid+'">'+d.eyepiecedescription+'</a>';
						} else {
							result += d.eyepiecedescription;
						}	
		            result += '</td>'+
			        '<td><?=LangViewObservationField31?></td>'+
		            '<td>';					
						if(d.filterid != 0){
			            	result += '<a href="index.php?indexAction=detail_filter&filter='+d.filterid+'">'+d.filterdescription+'</a>';
						} else {
							result += d.filterdescription;
						}	
	            	result += '</td>'+	
		            '<td><?=LangViewObservationField32?></td>'+
		            '<td>';					
					if(d.lensid != 0){
		            	result += '<a href="index.php?indexAction=detail_lens&lens='+d.lensid+'">'+d.lensdescription+'</a>';
					} else {
						result += d.lensdescription;
					}	
            	result += '</td>'+		            
	        	'</tr>'+	
		        '<tr>'+
		            '<td><?=LangViewObservationField22?></td>'+
		            '<td>'+d.visibility+'</td>'+
		            '<td><?=LangViewObservationField33?></td>'+
		            '<td>'+d.size+'</td>'+		
		            '<td><?=LangViewObservationField40?></td>'+
		            '<td>'+d.clustertype+'</td>'+	            
	        	'</tr>'+		        			        
		        '<tr >'+
		            '<td style="padding: 20px 0 20px 0" colspan="6">'+d.description+'</td>'+
		        '</tr>'+
	    	'</table>';

	    	return result;
		}

		$(document).ready(function() {
		  
		    datatablesConfig.ajax = "observations_json.php?object=<?=$_GET['object']?>",
		    datatablesConfig.columns = [
	            {
	                "class":          "details-control",
	                "orderable":      false,
	                "data":           null,
	                "defaultContent": "&nbsp;&nbsp;",
	                
	            },
	            //format of data columns
	            { "data": function ( row, type, val, meta ) { return '<a href="index.php?indexAction=detail_object&object='+row.objectname+'">'+row.objectname+'</a>'}},
	            { "data": "constellation"},
	            { "data": function ( row, type, val, meta ) { return '<a href="index.php?indexAction=detail_observer&user='+row.observerid+'">'+row.firstname+' '+row.name+'</a>' }},
	            { "data": function ( row, type, val, meta ) { return '<a href="index.php?indexAction=detail_instrument&instrument='+row.instrumentid+'">'+row.instrumentname+'('+row.instrumentdiameter+')</a>' }},
	            { "data": "sortdate", "visible": false},
		        { "data": "date", "orderData": 5 },
		        { "orderable" : false, "data": function ( row, type, val, meta ) { return '<a href="index.php?indexAction=detail_observation&observation='+row.id+'&dalm=D" title="<?=LangDetail ?>"><img src="/styles/images/details.png"/></a>' }}		      
	        ];

		var dt = $('#observations').DataTable( datatablesConfig );
		 	
	    // Array to track the ids of the details displayed rows
	    var detailRows = [];
	 
	    $('#observations tbody').on( 'click', 'tr td.details-control', function () {
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

<table id="observations" class="table table-striped table-bordered">
	<thead>
    	<tr>
          <th></th>
          <th><?= LangOverviewObservationsHeader1 ?></th>
          <th><?= LangViewObservationField1b ?></th>
          <th><?= LangOverviewObservationsHeader2 ?></th>
          <th><?= LangOverviewObservationsHeader3 ?></th>
          <th></th>
          <th><?= LangOverviewObservationsHeader4 ?></th>
          <th></th>
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
			<th></th>
		</tr>
	</tfoot>
</table>	
