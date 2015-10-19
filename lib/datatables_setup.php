<script type="text/javascript" src="lib/javascript/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="lib/javascript/dataTables.bootstrap.min.js"></script>

<link rel="stylesheet" type="text/css" href="styles/dataTables.bootstrap.css">

<script>
	var datatablesConfig = {
    	"language": {
    	    "search": 		  "<?=LangSearch?>",
    	    "lengthMenu":	  "_MENU_",
    	    "info":           "_START_ - _END_ (_TOTAL_)",
    	    "infoEmpty":      "",
    	    "loadingRecords": '<img src="/img/loading.gif">&nbsp;<?=LangIndexPleaseWait?>',
    	    "emptyTable":     "<?=LangObservationNoResults?>",
    	    "zeroRecords":    "<?=LangObservationNoResults?>",
    	    "paginate": {
    	        "next":       "<?=LangResultNext?>",
    	        "previous":   "<?=LangResultPrevious?>" }   	    
   		 },   		 
   		"order": [[1, 'desc']], 
        "lengthMenu": [[10, 25, 50, 100, 1000, -1], [10, 25, 50, 100, 1000, "<?=LangShowAll?>"]]

    };

</script>