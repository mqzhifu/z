<?php
class BaseUpgradeLogModel {
	static $_table = 'base_upgrade_log';
	static $_pk = 'id';
	static $_db_key = DEF_DB_CONN;
	static $_db = null;

	static function db(){
		if(self::$_db)
			return self::$_db;
		
		self::$_db = DbLib::getDbStatic(self::$_db_key,self::$_table,self::$_pk);
		return self::$_db;
	}
	
	public static function __callStatic($func, $arguments){
		return call_user_func_array(array(self::db(),$func), $arguments);
	}
	
	static function getName($uname){
		return self::db()->getRow(" uname = '$uname' ");
	}

	static function getById($uid){
        $user = self::db()->getById($uid);
        if($user){

        }
    }
    //默认取当前时间 - 5分钟 内，用户的总访问次数
    static function getUserReqCntByTime($uid,$time = 300){
        $time = time() - $time;
        return self::db()->getCount(" uid = $uid and a_time > $time");
    }

    //默认取当前时间 - 5分钟 内，用户的总访问次数
    static function getIPReqCntByTime($IP,$time = 300){
        $time = time() - $time;
        return self::db()->getCount(" IP = '$IP' and a_time > $time");
    }

    static function addReq(){
	    $data = array(
	        'ctrl'=>CTRL,
            'AC'=>AC,
            'a_time'=>time(),
            'IP'=>get_client_ip(),
            'request'=>json_encode($_REQUEST)
        );

	    $id = self::db()->add($data);
	    return $id;
    }

    static function upInfo(){

    }
	
}