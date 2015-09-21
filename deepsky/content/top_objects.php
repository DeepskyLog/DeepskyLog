<script type="text/javascript" src="lib/javascript/bootstrap.min.js"></script>
<script type="text/javascript" src="lib/javascript/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="lib/javascript/dataTables.bootstrap.min.js"></script>
<link rel="stylesheet" type="text/css" href="styles/dataTables.bootstrap.css">

<script type="text/javascript">		 
		$(document).ready(function() {
		    $('a[data-toggle="tab"]').on( 'shown.bs.tab', function (e) {
		        $.fn.dataTable.tables( {visible: true, api: true} ).columns.adjust();
		    } );

		    //TODO: move default values to js include
		    $('#table1').DataTable( {
		    	"language": {
		    	    "search": 		"<?=LangSearch?>",
		    	    "lengthMenu":	"_MENU_"
		   		 }, 
		        "ajax": 'rank_objects_json.php',
		        "lengthMenu": [[10, 25, 50, 100, 1000, -1], [10, 25, 50, 100, 1000, "<?=LangShowAll?>"]],
		        "order": [[1, 'desc']],
		        "columns": [
			    	//format of data columns
			        { "data": "objectname" },
			        { "data": "count"}
			    ]
		    } );
		    $('#table2').DataTable( {
		    	"language": {
		    	    "search": "<?=LangSearch?>",
		    	    "lengthMenu":	"_MENU_"
		    	},
		        "ajax": 'rank_objects_json.php?type=sketched',
		        "lengthMenu": [[10, 25, 50, 100, 1000, -1], [10, 25, 50, 100, 1000, "<?=LangShowAll?>"]],
		        "order": [[1, 'desc']],
		        "columns": [
			    	//format of data columns
			        { "data": "objectname" },
			        { "data": "count"}
			    ]
		    } );
					    
		} );
</script>

  <ul class="nav nav-tabs" role="tablist">
    <li role="presentation" class="active">
    	<a href="#tab1" aria-controls="tab1" role="tab" data-toggle="tab"><?=LangTopObjectsTitle?></a>
    </li>
    <li role="presentation">
    	<a href="#tab2" aria-controls="tab2" role="tab" data-toggle="tab"><?=LangTopObjectsDrawnTitle?></a>
    </li>
 </ul>

 <div class="tab-content">
    <div role="tabpanel" class="tab-pane active" id="tab1">
		<table id="table1" class="table table-striped table-bordered">
			<thead>
		    	<tr>
		          <th><?= LangOverviewObjectsHeader1 ?></th>
		          <th><?= GraphObservations ?></th>
		        </tr>
			</thead>
		    <tfoot>
				<tr>
					<th><?= LangOverviewObjectsHeader1 ?></th>
					<th><?= GraphObservations ?></th>
				</tr>
			</tfoot>
		</table>
	</div>
	<div role="tabpanel" class="tab-pane" id="tab2">
		<table id="table2" class="table table-striped table-bordered">
			<thead>
		    	<tr>
		          <th><?= LangOverviewObjectsHeader1 ?></th>
		          <th><?= GraphObservations ?></th>
		        </tr>
			</thead>
		    <tfoot>
				<tr>
					<th><?= LangOverviewObjectsHeader1 ?></th>
					<th><?= GraphObservations ?></th>
				</tr>
			</tfoot>
		</table>
	</div>
</div>