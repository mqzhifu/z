<?php
class GroupRecordModel {
	static $_table = 'group_record';
	static $_pk = 'id';
	static $_db = null;

    static $_auto_add_time = true;
    static $_auto_up_time = true;
    static $_auto_add_time_name = 'a_time';
    static $_auto_up_time_name = 'u_time';


    static $_status_desc = array(0=>'未处理',1=>'用户手里',2=>'废弃',3=>'不能抓');


    static function db(){
        if(self::$_db)
            return self::$_db;

        self::$_db = DbLib::getDbStatic('',self::$_table,self::$_pk);
        return self::$_db;
    }

    public static function __callStatic($func, $arguments){
        return call_user_func_array(array(self::db(),$func), $arguments);
    }

    public static function add($data){
        if(self::$_auto_add_time){
            $data[self::$_auto_add_time_name] = time();
        }
        if(self::$_auto_up_time){
            $data[self::$_auto_up_time_name] = time();
        }

        return self::db()->add($data);
    }

    public static function update($data , $where){
        if(self::$_auto_up_time){
            $data[self::$_auto_up_time_name] = time();
        }

        return self::db()->update($data , $where);
    }

    public static function upById($data , $id){
        if(self::$_auto_up_time){
            $data[self::$_auto_up_time_name] = time();
        }

        return self::db()->upById($id,$data );
    }

}