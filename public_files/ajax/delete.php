<?php

// Init owncloud
require_once('../../lib/base.php');

OC_JSON::checkLoggedIn();
$user = OC_User::public_get_user();

OC_Filesystem::chroot(PUBLIC_DIR);
// Get data
$dir = $_GET["dir"];
$files = isset($_GET["file"]) ? $_GET["file"] : $_GET["files"];

$files = explode(';', $files);
$filesWithError = '';
$success = true;
//Now delete
foreach($files as $file) {
    error_log($dir.'/'.$file);
    $file_info = OC_Public_Model::is_deletable($file, $dir.'/', $user);
    if( $file_info === false
        /*|| !OC_Files::delete( $dir, $file )*/){
		$filesWithError .= $file . "\n";
		$success = false;
	} else {
        OC_Public_Model::save_delete_handler($file_info);
    }
}

if($success) {
	OC_JSON::success(array("data" => array( "dir" => $dir, "files" => $files )));
} else {
	OC_JSON::error(array("data" => array( "message" => "无法删除:\n" . $filesWithError )));
}

?>
