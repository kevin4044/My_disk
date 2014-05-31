<?php

$l=new OC_L10N('files');

OC::$CLASSPATH['OC_Public_Model'] = 'public_files/lib/public_model.php';
OC_App::register( array( "order" => 2, "id" => "files", "name" => "Files" ));

OC_App::addNavigationEntry( array( "id" => "files_index", "order" => 1, "href" => OC_Helper::linkTo( "files", "index.php" ), "icon" => OC_Helper::imagePath( "core", "places/home.svg" ), "name" => $l->t("Files") ));
OC_App::addNavigationEntry( array( "id" => "public_files", "order" => 2, "href" => OC::$WEBROOT.'/public_files/index.php', "icon" => OC_Helper::imagePath( "core", "places/home.svg" ), "name" => 'just 资料库' ));

?>
