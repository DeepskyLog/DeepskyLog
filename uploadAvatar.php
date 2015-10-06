<?php
// upload.php
// 'images' refers to your file input name attribute
if (empty($_FILES['image'])) {
    echo json_encode(['error'=>'No files found for upload.']);
    // or you can throw an exception
    return; // terminate
}

// get the files posted
$image = $_FILES['image'];

// get user id posted
$userid = empty($_POST['userid']) ? '' : $_POST['userid'];

$oldFile = empty($_POST['oldFile']) ? '' : $_POST['oldFile'];

// a flag to see if everything is ok
$success = null;

// file paths to store
$paths= [];

// get file names
$filename = $image['name'];

// loop and process files
$ext = explode('.', basename($filename));
$upload_dir = 'common/observer_pics';
$target = $upload_dir . DIRECTORY_SEPARATOR . $userid . "." . array_pop($ext);

require_once $instDir . "common/control/resize.php"; // resize code

// First, we remove the old file
if ($oldFile != '') {
  unlink($oldFile);
}

if(move_uploaded_file($image['tmp_name'], $target)) {
    $success = true;
    $paths[] = $target;
} else {
    $success = false;
}

// check and process based on successful status
if ($success === true) {
    // store a successful response (default at least an empty array). You
    // could return any additional response info you need to the plugin for
    // advanced implementations.
    $output = [];
    // for example you can get the list of files uploaded this way
    // $output = ['uploaded' => $paths];
} elseif ($success === false) {
    $output = ['error'=>'Error while uploading images. Contact the system administrator'];
    // delete any uploaded files
    foreach ($paths as $file) {
        unlink($file);
    }
} else {
    $output = ['error'=>'No files were processed.'];
}

// return a json encoded response for plugin to process successfully
echo json_encode($output);
?>
