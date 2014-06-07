<?php
/**
 * Created by PhpStorm.
 * User: weroadshowdev
 * Date: 7/6/14
 * Time: 下午5:03
 */
// Init owncloud
require_once('../lib/base.php');


$filename = $_GET["file"];
OC_Filesystem::chroot(PUBLIC_DIR);

if(!OC_Filesystem::file_exists($filename)){
    header("HTTP/1.0 404 Not Found");
    $tmpl = new OC_Template( '', '404', 'guest' );
    $tmpl->assign('file',$filename);
    $tmpl->printPage();
    exit;
}

$ftype=OC_Filesystem::getMimeType( $filename );

header('Content-Type:'.$ftype);
header('Content-Disposition: attachment; filename="'.basename($filename).'"');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: '.OC_Filesystem::filesize($filename));

ob_end_clean();
OC_Filesystem::readfile( $filename );
?>
