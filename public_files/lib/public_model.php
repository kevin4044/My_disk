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
    static public function get_fileinfo_by_name($file_name, $parent_dir)
    {
        $query = OC_DB::prepare("SELECT * FROM `"
            .self::$db_name
            ."` WHERE `is_dir`=0 and `file_name`=? and `path`=? limit 1");
        $result = $query->execute(array($file_name, $parent_dir));

        return $result->fetchRow();
    }

    static public function get_dirinfo_by_name($name)
    {
        $query = OC_DB::prepare("SELECT * FROM `"
            .self::$db_name
            ."` WHERE `is_dir`=1 and `file_name` IS NULL and `path`=? limit 1");
        $result = $query->execute(array($name));

        return $result->fetchRow();
    }
    static public function get_info_by_id($id)
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
            ."`(`uid`, `file_name`, `path`, `reference_count`, `mtime`, `parent_id`, `is_dir`)
            VALUES (?,?,?,?,?,?,?)");
        $query->execute(array(
            $file['user_name'],//uid
            $file['name'],
            $file['directory'],
            $file['reference_count'],//reference_count
            $file['date'],
            $file['parent_id'],
            $file['type'] == 'dir'?PB_ISDIR:PB_NOTDIR
        ));
        return OC_DB::insertid();
    }
    static private function delete_row($id)
    {
        $query = OC_DB::prepare("DELETE FROM `"
            .self::$db_name
            ."` where id=?");
        $query->execute(array($id));
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
            $grand_parent_info = self::get_info_by_id($parent_info['parent_id']);
            self::check_parent_reference($grand_parent_info, $user);
        }
    }

    static public function upload_file_handler ($file_info,$user_name)
    {
        if (!$user_name) {
            error_log('NULL user try to upload file');
            return false;
        }
        $parent_dir = self::get_parent_dir($file_info['directory']);

        if ($parent_dir === false) {
            return false;
        }
        $parent_dir_info = self::get_dirinfo_by_name($parent_dir);

        self::check_parent_reference($parent_dir_info,$user_name);

        $parent_dir_id = $parent_dir_info['id'];
        $file_info['user_name'] = $user_name;
        $file_info['parent_id'] = $parent_dir_id;
        $file_info['reference_count'] = 0;//初始状态,引用计数为0

        return self::add_row($file_info);
    }

    /**
     * @brief 判断用户是否与父目录一致，如一致则增加上级引用计数
     * 并将本目录增添入数据库
     * @param string $dir_name formate as 'dddd'
     * @param string $parent_dir formate as '/aaa/vvv/'
     * @param string $user
     * @return true/false
     */

    static public function newfolder_handler($dir_name,$parent_dir, $user)
    {
        if (!$user) {
            error_log('NULL user try to create_dir');
            return false;
        }
        $parent_dir_info = self::get_dirinfo_by_name($parent_dir);
        error_log('parent id='.$parent_dir_info['id']);

        self::check_parent_reference($parent_dir_info,$user);

        $dir_info = array(
            'user_name' =>      $user,//uid
            'name'=>            null ,
            'directory' =>      $parent_dir.$dir_name.'/',
            'reference_count'=> 0,//reference_count
            'date'=>            date('Y-m-h H:i:s'),
            'parent_id'=>       $parent_dir_info['id'],
            'type' =>           'dir'
        );

        return self::add_row($dir_info);
    }

    /**
     * @breif is this file/dir deletable
     * @param string $name
     * @param string $parent_dir  formate like '/aaa/'
     * @param string $current_user
     * @return bool/array
     */
    static public function is_deletable($name, $parent_dir, $current_user)
    {
        $full_name = $parent_dir.$name;
        if (OC_Filesystem::is_file($full_name)) {
            $info = self::get_fileinfo_by_name($name,$parent_dir);
        } else if (OC_Filesystem::is_dir($full_name)) {
            $info = self::get_dirinfo_by_name($full_name);
        } else {
            return false;
        }

        if ($info['reference_count'] == 0 && $info['uid'] == $current_user) {
            return $info;
        } else {
            return false;
        }
    }

    static public function cut_up_parent_reference($file_info, $user)
    {
        $parent_id = $file_info['parent_id'];
        if ($parent_id == 0) {
            return;
        }

        $parent_info = self::get_info_by_id($parent_id);

        if ($parent_info['uid'] != $user) {
            self::reduce_dir_reference($parent_id);
        }

        self::cut_up_parent_reference($parent_info, $user);
    }

    static public function delete_handler($file_info, $user)
    {
        self::cut_up_parent_reference($file_info, $user);
        self::delete_row($file_info['id']);
    }


    static public function test()
    {
/*        echo self::get_parent_dir('/test/aaa/css');
        exit;*/
        self::delete_row(3);
    }
}