<?php
/**
 * Created by PhpStorm.
 * User: weroadshowdev
 * Date: 30/5/14
 * Time: 下午10:48
 */
define('PB_ROOT_ID', 0);
define('PB_ISDIR', 1);
define('PB_NOTDIR', 0);
class OC_Public_Model {
    //todo:maybe change it to another way
    static private $db_name = "*PREFIX*public_map";


    //todo 改为引用计数
    static private  function add_dir_reference($id)
    {
        $query = OC_DB::prepare("UPDATE `"
            .self::$db_name ."` SET reference_count=reference_count+1"
            ." WHERE `id`=?");
        $result = $query->execute(array($id));
    }
    static private function reduce_dir_reference($id)
    {
        $query = OC_DB::prepare("UPDATE `"
            .self::$db_name ."` SET reference_count=reference_count-1"
            ." WHERE `id`=?");
        $result = $query->execute(array($id));
    }

    static public function get_dirinfo_by_name($name)
    {
        $query = OC_DB::prepare("SELECT * FROM `"
            .self::$db_name
            ."` WHERE `is_dir`=1 and `file_name` IS NULL and `path`=? limit 1");
        $result = $query->execute(array($name));

        return $result->fetchRow();
    }
    static public function get_dirinfo_by_id($id)
    {
        $query = OC_DB::prepare("SELECT * FROM `"
            .self::$db_name
            ."` WHERE `id`=? limit 1");
        $result = $query->execute(array($id));

        return $result->fetchRow();
    }
    static private function add_row($file)
    {

        $query = OC_DB::prepare("INSERT INTO `"
            .self::$db_name
            ."`(`uid`, `file_name`, `path`, `reference_count`, `size`, `mtime`, `parent_id`, `is_dir`)
            VALUES (?,?,?,?,?,?,?,?)");
        $query->execute(array(
            $file['user_name'],//uid
            $file['name'],
            $file['directory'],
            $file['reference_count'],//reference_count
            $file['size'],
            $file['date'],
            $file['parent_id'],
            $file['type'] == 'dir'?PB_ISDIR:PB_NOTDIR
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

    //引用计数+1操作
    static public function  check_parent_reference($parent_info, $user)
    {
        if ($parent_info['uid'] != $user) {
            self::add_dir_reference($parent_info['id']);
        }

        if ($parent_info['id'] == PB_ROOT_ID) {
            return;
        } else {
            $grand_parent_info = self::get_dirinfo_by_id($parent_info['parent_id']);
            self::check_parent_reference($grand_parent_info, $user);
        }
    }

    static public function upload_file_handler ($file,$user_name)
    {
        if (!$user_name) {
            error_log('NULL user try to upload file');
            return false;
        }
        $parent_dir = self::get_parent_dir($file['directory']);

        if ($parent_dir === false) {
            return false;
        }
        $parent_dir_info = self::get_dirinfo_by_name($parent_dir);

        self::check_parent_reference($parent_dir_info,$user_name);

        $parent_dir_id = $parent_dir_info['id'];
        $file['user_name'] = $user_name;
        $file['parent_id'] = $parent_dir_id;
        $file['reference_count'] = 0;//初始状态,引用计数为0

        return self::add_row($file);
    }




    static public function test()
    {
/*        echo self::get_parent_dir('/test/aaa/css');
        exit;*/
    }
}