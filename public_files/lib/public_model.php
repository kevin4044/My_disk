<?php
/**
 * Created by PhpStorm.
 * User: weroadshowdev
 * Date: 30/5/14
 * Time: 下午10:48
 */

class OC_Public_Model {
    //todo:maybe change it to another way
    static private $db_name = "*PREFIX*public_map";

    static public function find_dir_by_name($name)
    {
        $query = OC_DB::prepare("SELECT * FROM `"
            .self::$db_name
            ."` WHERE `is_dir`=1 and `file_name` IS NULL and `path`=? limit 1");
        $result = $query->execute(array($name));

        return $result->fetchRow();
    }
    static private function add_row($file)
    {

        $query = OC_DB::prepare("INSERT INTO `"
            .self::$db_name
            ."`(`uid`, `file_name`, `path`, `is_editable`, `size`, `mtime`, `parent_id`, `is_dir`)
            VALUES (?,?,?,?,?,?,?,?)");
        $query->execute(array(
            $file['user_name'],//uid
            $file['name'],
            $file['directory'],
            $file['is_editable'],//is_editable
            $file['size'],
            $file['date'],
            $file['parent_id'],
            $file['type'] == 'dir'?1:0
        ));
        return OC_DB::insertid();
    }

    static public function get_parent_dir($dir)
    {
        if (!is_string($dir)) {
            return false;
        }

        $last_slash = strrpos($dir,'/');

        if ($last_slash !== false) {
            return substr($dir,0,$last_slash+1);
        }
        return false;
    }

    static public function upload_file_handler ($file,$user_name)
    {
        $parent_dir = self::get_parent_dir($file['directory']);

        if ($parent_dir === false) {
            return false;
        }
        //todo:get parent_dir_id
        $parent_dir_id = 0;
        $file['user_name'] = $user_name;
        $file['parent_id'] = $parent_dir_id;
        $file['is_editable'] = 1;

        return self::add_row($file);
    }

}