<?php

// Init owncloud
require_once('../../lib/base.php');

// Check if we are a user
// Delete by Kevin Cause we need to register it
/*if( !OC_User::isLoggedIn() || !OC_Group::inGroup( OC_User::getUser(), 'admin' )){
	OC_JSON::error(array("data" => array( "message" => "Authentication error" )));
	exit();
}*/

$groups = array();
if( isset( $_POST["groups"] )){
	$groups = $_POST["groups"];
}
$username = $_POST["username"];
$password = $_POST["password"];

// Does the group exist?
if( in_array( $username, OC_User::getUsers())){
	OC_JSON::error(array("data" => array( "message" => "User already exists" )));
	exit();
}

// Return Success story
if( OC_User::createUser( $username, $password )){
	foreach( $groups as $i ){
		if(!OC_Group::groupExists($i)){
			OC_Group::createGroup($i);
		}
		OC_Group::addToGroup( $username, $i );
	}
	OC_JSON::success(array("data" => array( "username" => $username, "groups" => implode( ", ", OC_Group::getUserGroups( $username )))));
}
else{
	OC_JSON::error(array("data" => array( "message" => "Unable to add user" )));
}

?>
