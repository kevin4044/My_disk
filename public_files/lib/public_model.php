<?php
/**
 * Created by PhpStorm.
 * User: weroadshowdev
 * Date: 30/5/14
 * Time: 下午10:48
 */
define('PB_ROOT_ID', 0);
/**
 *  是否是路径
 */
define('PB_ISDIR', 1);
/**
 *
 */
define('PB_NOTDIR', 0);

/**
 * @brief 公共目录 所用函数集合
 * Class OC_Public_Model
 */
class OC_Public_Model {
    //todo:maybe change it to another way
    /**
     * @var string
     */
    static private $db_name = "*PREFIX*public_map";


    /**
     * @brief 确保路径名的最后有'/'字符串
     * @param $dir
     * @return string
     */
    static private function dir_formatter($dir)
    {
        if (substr($dir, -1) !== '/')
        {
            $dir .= '/';
        }
        return $dir;
    }

    /**
     * @brief 增加对应id 数据库记录的引用计数
     * @param $id
     */
    static private  function add_dir_reference($id)
    {
        $query = OC_DB::prepare("UPDATE `"
            .self::$db_name ."` SET reference_count=reference_count+1"
            ." WHERE `id`=? and is_dir=1");
        $result = $query->execute(array($id));
    }

    /**
     * @brief 减少1 对应id 的数据库记录的引用计数
     * @param $id
     */
    static private function reduce_dir_reference($id)
    {
        $query = OC_DB::prepare("UPDATE `"
            .self::$db_name ."` SET reference_count=reference_count-1"
            ." WHERE `id`=? and is_dir=1");
        $result = $query->execute(array($id));
    }

    /**
     * @brief  获取对应文件夹信息
     * @param $file_name string 文件名，
     * @param $parent_dir string 路径
     * @return mixed
     */
    static public function get_fileinfo_by_name($file_name, $parent_dir)
    {
        $query = OC_DB::prepare("SELECT * FROM `"
            .self::$db_name
            ."` WHERE `is_dir`=0 and `file_name`=? and `path`=? limit 1");
        $result = $query->execute(array($file_name, $parent_dir));

        return $result->fetchRow();
    }

    /**
     * @brief  获取对应名称的路径信息
     * @param $name string 路径名称
     * @return mixed
     */
    static public function get_dirinfo_by_name($name)
    {
        $name = self::dir_formatter($name);
        $query = OC_DB::prepare("SELECT * FROM `"
            .self::$db_name
            ."` WHERE `is_dir`=1 and `file_name` IS NULL and `path`=? limit 1");
        $result = $query->execute(array($name));
        error_log("SELECT * FROM `"
            .self::$db_name
            ."` WHERE `is_dir`=1 and `file_name` IS NULL and `path`='{$name}' limit 1");

        return $result->fetchRow();
    }

    /**
     * @brief  获取对应id 对应的数据库记录
     * @param $id
     * @return mixed
     */
    static public function get_info_by_id($id)
    {
        $query = OC_DB::prepare("SELECT * FROM `"
            .self::$db_name
            ."` WHERE `id`=? limit 1");
        $result = $query->execute(array($id));

        return $result->fetchRow();
    }

    /**
     * @brief  获取对应id路径的直接子文件
     * @param $dir_id
     * @return array
     */
    static public function get_dir_sons($dir_id)
    {
        $ret = array();
        $query = OC_DB::prepare("SELECT * FROM `"
            .self::$db_name
            ."` WHERE `parent_id`=?");
        $result = $query->execute(array($dir_id));

        while( $row = $result->fetchRow()){
            $ret[] = $row;
        }
        return $ret;
    }

    /**
     * @brief  增加一条记录
     * @param $file array 文件详情
     * @return id 数据库记录行号
     */
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

    /**
     *
     * @param $id
     */
    static private function delete_row($id)
    {
        $query = OC_DB::prepare("DELETE FROM `"
            .self::$db_name
            ."` where id=?");
        $query->execute(array($id));
    }

    /**
     * @brief  获取该路径的父路径
     * @param $dir string 当前文件绝对路径
     * @return bool|string
     */
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

    /**
     * @brief 根据当前路径名获取本文件夹的名称
     * @param $path string 格式：'/aaa/bbb/'
     * @return mixed
     */
    static private function get_dir_name_from_path($path)
    {
        $path_arry = explode('/',$path);
        return $path_arry[count($path_arry) - 2];
    }
    //引用计数+1操作
    /**
     * @brief 递归检查从本路径开始父路径的引用计数，并进行相应操作
     * @param $parent_info array 父路径的详细信息
     * @param $user
     */
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

    /**
     * @brief 上传新文件时用于检查父路径们的引用计数并进行相应操作，
     * 然后在数据库增加本次上传的文件的信息
     * @param $file_info array 文件基本详情
     * @param $user_name
     * @return bool|id
     */
    static public function upload_file_handler ($file_info,$user_name)
    {
        if (!$user_name) {
            error_log('NULL user try to upload file');
            return false;
        }
        $parent_dir = self::get_parent_dir($file_info['directory']);
        error_log(__FUNCTION__ .' parent_dir:'.$parent_dir);

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
        $info = self::get_any_info($name, $parent_dir);

        if ($info['reference_count'] == 0 && $info['uid'] == $current_user) {
            return $info;
        } else {
            return false;
        }
    }

    /**
     * @brief 递归检测并删减本目录造成的父目录引用计数
     * @param $file_info
     * @param $user
     */
    static public function cut_up_parent_reference($file_info, $user)
    {
        if ($file_info['id'] == 0)
            return;

        $parent_id = $file_info['parent_id'];


        $parent_info = self::get_info_by_id($parent_id);

        if ($parent_info['uid'] != $user) {
            self::reduce_dir_reference($parent_id);
        }

        self::cut_up_parent_reference($parent_info, $user);
    }

    /**
     * @brief 删除文件的处理函数
     * @param $file_info
     * @param $user
     */
    static public function delete_handler($file_info, $user)
    {
        self::cut_up_parent_reference($file_info, $user);
        self::delete_row($file_info['id']);
        //todo:目录删除
    }

    /**
     * @breif 判断文件是否能被重命名/移动
     * @param $file
     * @param $dir
     * @param $user_name
     * @return bool|array
     */
    static public function is_movable($file, $dir, $user_name)
    {
        if (OC_User::is_admin($user_name)) {
            return true;
        }
        $dir = self::dir_formatter($dir);
        error_log($dir.$file);
        $file_info = self::get_any_info($file, $dir);

        error_log(json_encode($file_info));

        if ($user_name != $file_info['uid']
            || $file_info['reference_count'] != 0) {
            return false;
        }

        return $file_info;
    }


    /**
     * @brief 文件移动处理
     * @param $file_info array
     * @param $new_path string
     * @param $new_name string
     * @return bool
     */
    static public function file_move_handle($file_info, $new_path, $new_name)
    {
        error_log(__FUNCTION__.'('.$new_path.','.$new_name.')');


        $new_parent_info = self::get_dirinfo_by_name($new_path);
        if ($new_parent_info === false) {
            error_log('ERR:'.__FUNCTION__.'path '.$new_path.' info fetch error');
        }
        error_log('parent:'.json_encode($new_parent_info));

        if ($file_info['is_dir'] == PB_ISDIR) {
            $query = OC_DB::prepare("UPDATE `"
                .self::$db_name ."` SET path=?, parent_id=?"
                ." WHERE `id`=?");
            $result = $query->execute(array(
                self::dir_formatter($new_path.$new_name)
                , $new_parent_info['id']
                , $file_info['id']
            ));
        } else {
            $query = OC_DB::prepare("UPDATE `"
                .self::$db_name ."` SET path=?, file_name=?, parent_id=?"
                ." WHERE `id`=?");
            $result = $query->execute(array($new_path, $new_name, $new_parent_info['id'], $file_info['id']));
        }


        return $new_parent_info;
    }

    /**
     * @brief 递归删除本文件以及子文件的引用计数
     * @param $file_info
     * @internal param $user
     */
    static private function cut_all_reference_recursion($file_info)
    {
        $user = $file_info['uid'];
        self::cut_up_parent_reference($file_info, $user);
        if ($file_info['is_dir'] == PB_ISDIR) {
            $son_files = self::get_dir_sons($file_info['id']);
            foreach ($son_files as $son_file) {
                self::cut_all_reference_recursion($son_file);
            }
        }

    }

    /**
     * @brief 递归检查并增加本文件以及子文件的引用计数
     * @param $file_info
     * @param null $parent_info
     * @internal param $user
     */
    static private function check_all_reference_recursion($file_info, $parent_info = null)
    {
        $user = $file_info['uid'];
        if ($parent_info === null) {
            $parent_info = self::get_info_by_id($file_info['parent_id']);
        }

        self::check_parent_reference($parent_info, $user);
        if ($file_info['is_dir'] == PB_ISDIR) {
            $son_files = self::get_dir_sons($file_info['id']);
            foreach ($son_files as $son_file) {
                self::check_all_reference_recursion($son_file);
            }
        }
    }


    /**
     * @brief 递归移动文件
     * @param $file_info
     * @param $new_path
     * @param $new_name
     * @return bool
     */
    static private function move_all_file_handler($file_info, $new_path, $new_name)
    {
        $new_path = self::dir_formatter($new_path);

        self::file_move_handle($file_info, $new_path, $new_name);
        if ($file_info['is_dir'] == PB_ISDIR) {
            error_log(__FUNCTION__.' dir rename');

            $son_files = self::get_dir_sons($file_info['id']);

            error_log(__FUNCTION__.' sons '.json_encode($son_files));
            foreach ($son_files as $son_file) {
                if ($son_file['is_dir'] == PB_ISDIR) {
                    $son_name = self::get_dir_name_from_path($son_file['path']);
                } else {
                    $son_name = $son_file['file_name'];
                }
                self::move_all_file_handler($son_file, $new_path.$new_name, $son_name);
            }
        }

        return true;
    }

    /**
     * @brief 移动文件总处理函数
     * @param $file_info
     * @param $new_path
     * @param $new_name
     * @internal param $user
     */
    static public function move_handler($file_info, $new_path, $new_name)
    {
        self::cut_all_reference_recursion($file_info);

        self::move_all_file_handler($file_info,$new_path, $new_name);

        self::check_all_reference_recursion($file_info);

    }


    /**
     *
     */
    static public function test()
    {
/*        echo self::get_parent_dir('/test/aaa/css');
        exit;*/
       // self::delete_row(3);
/*       echo self::dir_formatter('aaa');
        exit;*/
/*        echo self::get_dir_name_from_path('/aaa/vvv/ccc/');
        exit;*/
    }

    /**
     * @breif get info no matter it is dir or not
     * @param $name
     * @param $parent_dir '/aaa/'
     * @return mixed
     */
    public static function get_any_info($name, $parent_dir)
    {
        $full_name = $parent_dir . $name;
        error_log('full_name:'.$full_name);
        if (OC_Filesystem::is_file($full_name)) {
            error_log('full_name:'.$full_name .'is_file');
            $info = self::get_fileinfo_by_name($name, $parent_dir);
            return $info;
        } else if (OC_Filesystem::is_dir($full_name)) {
            error_log('full_name:'.$full_name .'is_dir');
            $info = self::get_dirinfo_by_name($full_name);
            return $info;
        }
        error_log(__FUNCTION__ .$full_name.': neither dir or file');
        return false;
    }
}