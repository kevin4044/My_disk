<?php

// Init owncloud
require_once('../../lib/base.php');

$user = OC_User::public_get_user();
OC_Filesystem::chroot(PUBLIC_DIR);

// Get the params
$dir = isset( $_GET['dir'] ) ? $_GET['dir'] : '';
$foldername = isset( $_GET['foldername'] ) ? $_GET['foldername'] : '';

if($foldername == '') {
	OC_JSON::error(array("data" => array( "message" => "Empty Foldername" )));
	exit();
}
if(defined("DEBUG") && DEBUG) {error_log('try to create ' . $foldername . ' in ' . $dir);}
if(OC_Files::newFile($dir, $foldername, 'dir')) {
	OC_JSON::success(array("data" => array()));
	exit();
}

OC_JSON::error(array("data" => array( "message" => "Error when creating the folder" )));
