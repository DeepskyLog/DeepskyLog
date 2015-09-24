<?php require_once 'lib/datatables_setup.php'; ?>

<script type="text/javascript">		 
		$(document).ready(function() {
		
		    $('a[data-toggle="tab"]').on( 'shown.bs.tab', function (e) {
		        $.fn.dataTable.tables( {visible: true, api: true} ).columns.adjust();
		    } );

		  	//from datatables_setup.php
		    datatablesConfig.ajax = 'rank_objects_json.php';
		    datatablesConfig.columns = [
		       	{ "data": "objectname",
			      "render": function (data, type, row) { return '<a href="index.php?indexAction=detail_object&object=' + row.objectname + '">' + row.objectname + '</a>'; }},
		        { "data": "count",
		          "render": function (data, type, row) { return '<a href="index.php?indexAction=result_selected_observations&title=Overzicht+geselecteerde+waarnemingen&myLanguages=true&query=Zoek+waarnemingen&seen=A&object=' + row.objectname + '">' + row.count + '</a>'; }},                    	
		    ];
		    
		    $('#table1').DataTable( datatablesConfig );	    
		    
		    datatablesConfig.aoColumns = [
                { "data": "objectname",
  			      "render": function (data, type, row) { return '<a href="index.php?indexAction=detail_object&object=' + row.objectname + '">' + row.objectname + '</a>'; }},
                { "data": "count",
                  "render": function (data, type, row) { return '<a href="index.php?indexAction=result_selected_observations&title=Overzicht+geselecteerde+waarnemingen&myLanguages=true&query=Zoek+waarnemingen&seen=A&drawings=on&object=' + row.objectname + '">' + row.count + '</a>'; }},                    	
            ];		    

		    datatablesConfig.ajax = 'rank_objects_json.php?type=sketched';
		    $('#table2').DataTable( datatablesConfig );				    
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