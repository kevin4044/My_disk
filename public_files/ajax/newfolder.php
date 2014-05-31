<?php

// Init owncloud
require_once('../../lib/base.php');

OC_JSON::checkLoggedIn();

OC_Filesystem::chroot(PUBLIC_DIR);
$user = OC_User::public_get_user();
// Get the params
$dir = isset( $_GET['dir'] ) ? $_GET['dir'] : '';
$foldername = isset( $_GET['foldername'] ) ? $_GET['foldername'] : '';

if($foldername == '') {
	OC_JSON::error(array("data" => array( "message" => "Empty Foldername" )));
	exit();
}
if(defined("DEBUG") && DEBUG) {error_log('try to create ' . $foldername . ' in ' . $dir);}
if(OC_Files::newFile($dir, $foldername, 'dir')
    && OC_Public_Model::newfolder_handler($foldername, $dir.'/', $user)) {

	OC_JSON::success(array("data" => array()));
	exit();
}

OC_JSON::error(array("data" => array( "message" => "Error when creating the folder" )));
