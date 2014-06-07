<?php
/**
 * Created by PhpStorm.
 * User: weroadshowdev
 * Date: 7/6/14
 * Time: 上午8:10
 */
// Init owncloud
require_once('../lib/base.php');

// Check if we are a user
OC_Util::checkLoggedIn();
// Load the files we need
OC_Util::addStyle( "files", "files" );
OC_Util::addScript( "files", "files" );
OC_Util::addScript( 'files', 'filelist' );
OC_Util::addScript( 'files', 'fileactions' );
OC_Util::addScript( 'files', 'sort' );
OC_Util::addScript( 'files', 'jquery.searcher');
if(!isset($_SESSION['timezone'])){
    OC_Util::addScript( 'files', 'timezone' );
}
OC_App::setActiveNavigationEntry( "my_upload" );
// Load the files
$dir = isset( $_GET['dir'] ) ? $_GET['dir'] : '';

$user = OC_User::getUser();


$files = OC_Public_Model::get_user_uploads($user);

// Make breadcrumb
$breadcrumb = array();
$pathtohere = "";
foreach( explode( "/", $dir ) as $i ){
    if( $i != "" ){
        $pathtohere .= "/$i";
        $breadcrumb[] = array( "dir" => $pathtohere, "name" => $i );
    }
}

// make breadcrumb und filelist markup
$list = new OC_Template( "public_files", "part.list", "" );
$list->assign( "files", $files );
$list->assign( "baseURL", OC_Helper::linkTo("public_files", "index.php?dir="));
$list->assign( "downloadURL", OC_Helper::linkTo("public_files", "download.php?file="));
$list->assign( "readonly", true );
$breadcrumbNav = new OC_Template( "files", "part.breadcrumb", "" );
$breadcrumbNav->assign( "breadcrumb", $breadcrumb );
$breadcrumbNav->assign( "baseURL", OC_Helper::linkTo("public_files", "index.php?dir="));

$upload_max_filesize = OC_Helper::computerFileSize(ini_get('upload_max_filesize'));
$post_max_size = OC_Helper::computerFileSize(ini_get('post_max_size'));
$maxUploadFilesize = min($upload_max_filesize, $post_max_size);

$tmpl = new OC_Template( "files", "index", "user" );
$tmpl->assign( "fileList", $list->fetchPage() );
$tmpl->assign( "breadcrumb", $breadcrumbNav->fetchPage() );
$tmpl->assign( 'dir', $dir);
$tmpl->assign( "files", $files );
$tmpl->assign( 'uploadMaxFilesize', $maxUploadFilesize);
$tmpl->assign( 'uploadMaxHumanFilesize', OC_Helper::humanFileSize($maxUploadFilesize));
$tmpl->printPage();
