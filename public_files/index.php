<?php
/**
 * Created by PhpStorm.
 * User: weroadshowdev
 * Date: 30/5/14
 * Time: 上午8:52
 */
// Init owncloud
require_once('../lib/base.php');

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
OC_App::setActiveNavigationEntry( "public_files" );
// Load the files
$dir = isset( $_GET['dir'] ) ? $_GET['dir'] : '';
//by kevin
OC_Filesystem::chroot(PUBLIC_DIR);

if (!file_exists(OC::$CONFIG_PUBLIC_DATA_DIR)) {
    //$ret = @mkdir(OC::$CONFIG_PUBLIC_DATA_DIR);
    $ret = false;
    if ($ret === false) {
        $erro_tmpl = new OC_Template( '', 'error', 'guest' );
        $erro_tmpl->assign('errors',array(1=>array('error'=>"Can't create data directory (".$CONFIG_DATADIRECTORY_ROOT.")",'hint'=>"You can usually fix this by setting the owner of '".OC::$SERVERROOT."' to the user that the web server uses (".OC_Util::checkWebserverUser().")")));
        $erro_tmpl->printPage();
        exit;
    }
}

$files = array();
foreach( OC_Files::getdirectorycontent( $dir ) as $i ){
    $i["date"] = OC_Util::formatDate($i["mtime"] );
    if($i['type']=='file'){
        $fileinfo=pathinfo($i['name']);
        $i['basename']=$fileinfo['filename'];
        if (!empty($fileinfo['extension'])) {
            $i['extention']='.' . $fileinfo['extension'];
        }
        else {
            $i['extention']='';
        }
    }
    if($i['directory']=='/'){
        $i['directory']='';
    }
    $files[] = $i;
}

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
$list = new OC_Template( "files", "part.list", "" );
$list->assign( "files", $files );
$list->assign( "baseURL", OC_Helper::linkTo("public_files", "index.php?dir="));
$list->assign( "downloadURL", OC_Helper::linkTo("puyblic_files", "download.php?file="));
$breadcrumbNav = new OC_Template( "files", "part.breadcrumb", "" );
$breadcrumbNav->assign( "breadcrumb", $breadcrumb );
$breadcrumbNav->assign( "baseURL", OC_Helper::linkTo("files", "index.php?dir="));

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

