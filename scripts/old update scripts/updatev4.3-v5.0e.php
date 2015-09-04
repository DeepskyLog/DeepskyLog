<?php
$inIndex = true;
require_once "../lib/setup/databaseInfo.php";
require_once "../lib/database.php";
date_default_timezone_set ( 'UTC' );

$objDatabase = new Database ();
print "Database update will update the observerobjectlist: remove 'Public' in front of all the public lists and add a new column 'public'<br />\n";
$sql = "ALTER TABLE observerobjectlist ADD COLUMN public SMALLINT NOT NULL DEFAULT 0;";
$objDatabase->execSQL ( $sql );

// Iterate over all the list entries, set the new public column and remove 'Public' from the name.
$sql = "SELECT * from observerobjectlist;";
$listEntries = $objDatabase->selectRecordsetArray( $sql );

$publicList = array();

foreach ($listEntries as $listEntry) {
	if (strpos ( $listEntry['listname'], "Public: " ) !== false ) {
		// The list is a public list.
		// We add the name of the list to the publicList array.
		if (! in_array($listEntry['listname'], $publicList)) {
			array_push($publicList, $listEntry['listname']);
		}
	}
}

foreach ($publicList as $listName) {
	// Set the public column to true.
	$sql = "UPDATE observerobjectlist SET public = \"1\" WHERE  listname = \"" . $listName . "\";"; 
	$objDatabase->execSQL ( $sql );
	
	// Change the name of the list.
	$sql = "UPDATE observerobjectlist SET listname = \"" . substr( $listName, 8 ) . "\" WHERE listname = \"" . $listName . "\";";
	$objDatabase->execSQL ( $sql );
}

// We move some Messier lists from public to private
$objDatabase->execSQL("UPDATE observerobjectlist set public=\"0\" where listname like \"De Messie%\";");
$objDatabase->execSQL("UPDATE observerobjectlist set public=\"0\" where listname like \"Messier\";");
$objDatabase->execSQL("UPDATE observerobjectlist set public=\"0\" where listname like \"Messier -%\";");

print "Database update successful.\n";
?>
