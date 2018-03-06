<?php
class orderModel {
    static $_table = 'orders';
    static $_pk = 'id';
    static $_db = null;

    public static $_status_desc = array(1=>'未处理','支付失败','支付成功');

    static function db(){
        if(self::$_db)
            return self::$_db;

        self::$_db = DbLib::getDbStatic('',self::$_table,self::$_pk);
        return self::$_db;
    }

    public static function __callStatic($func, $arguments){
        return call_user_func_array(array(self::db(),$func), $arguments);
    }

    static function login($uname,$ps){
        return self::db()->getRow(" uname = '$uname' and ps = '$ps'");
    }

    static function getName($uname){
        return self::db()->getRow(" uname = '$uname' ");
    }

}