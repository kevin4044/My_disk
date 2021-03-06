<?php

// Init owncloud
require_once('../../lib/base.php');

$l=new OC_L10N('files');
// Firefox and Konqueror tries to download application/json for me.  --Arthur
OC_JSON::setContentTypeHeader('text/plain');
OC_JSON::checkLoggedIn();

$user = OC_User::public_get_user();


OC_Filesystem::chroot(PUBLIC_DIR);
if (!isset($_FILES['files'])) {
	OC_JSON::error(array("data" => array( "message" => "No file was uploaded. Unknown error" )));
	exit();
}
foreach ($_FILES['files']['error'] as $error) {
	if ($error != 0) {
		$errors = array(
			0=>$l->t("There is no error, the file uploaded with success"),
			1=>$l->t("The uploaded file exceeds the upload_max_filesize directive in php.ini"),
			2=>$l->t("The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form"),
			3=>$l->t("The uploaded file was only partially uploaded"),
			4=>$l->t("No file was uploaded"),
			6=>$l->t("Missing a temporary folder")
		);
		OC_JSON::error(array("data" => array( "message" => $errors[$error] )));
		exit();
	}
}
$files=$_FILES['files'];

$dir = $_POST['dir'];
$dir .= '/';
$error='';

$totalSize=0;
foreach($files['size'] as $size){
	$totalSize+=$size;
}
if($totalSize>OC_Filesystem::free_space('/')){
	OC_JSON::error(array("data" => array( "message" => "Not enough space available" )));
	exit();
}

$result=array();
if(strpos($dir,'..') === false){
	$fileCount=count($files['name']);
	for($i=0;$i<$fileCount;$i++){
		$target=stripslashes($dir) . $files['name'][$i];
        $file_info = formate_file_info($target, $files['name'][$i], $dir);
		if(OC_Filesystem::fromUploadedFile($files['tmp_name'][$i],$target)
            && OC_Public_Model::upload_file_handler($file_info, $user)
        ){
			$result[]=array( "status" => "success", 'mime'=>OC_Filesystem::getMimeType($target),'size'=>OC_Filesystem::filesize($target),'name'=>$files['name'][$i]);
		}
	}
	OC_JSON::encodedPrint($result);
	exit();
}else{
	$error='invalid dir';
}

OC_JSON::error(array('data' => array('error' => $error, "file" => $fileName)));

function formate_file_info($target, $file_name, $dir)
{
    $current_file = array('name'=>$file_name, 'directory'=>$dir, 'size'=>OC_Filesystem::filesize($target), 'date'=>date('Y-m-d H:i:s'), 'type'=>'file');
    return $current_file;
}

?>
