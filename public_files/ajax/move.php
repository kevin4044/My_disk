<?php

// Init owncloud
require_once('../../lib/base.php');

OC_JSON::checkLoggedIn();
$user = OC_User::public_get_user();
OC_Filesystem::chroot(PUBLIC_DIR);

// Get data
$dir = $_GET["dir"];
$file = $_GET["file"];
$target = $_GET["target"];

error_log('into rename '.$dir.'/'.$file);
$file_info = OC_Public_Model::is_movable($file, $dir.'/', $user);
error_log(json_encode($file_info));

if ($file_info === false) {
    error_log('unable to move file' . $dir.'/'.$file);
    OC_JSON::error(array("data" => array( "message" => "Unable to rename file" )));
    exit;
}

if(OC_Files::move($dir,$file,$target,$file)){
    OC_Public_Model::move_handler($file_info, $target, $file);
	OC_JSON::success(array("data" => array( "dir" => $dir, "files" => $file )));
}else{
	OC_JSON::error(array("data" => array( "message" => "Could move $file" )));
}

?>
