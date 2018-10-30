<?php
class LoginModel {
	static $_table = 'login';
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
	
	static function login($uname,$ps){
		return self::db()->getRow(" uname = '$uname' and ps = '$ps'");
	}
	
	static function getName($uname){
		return self::db()->getRow(" uname = '$uname' ");
	}

	static function getById($uid){
        $user = self::db()->getById($uid);
        if($user){

        }
    }

    static function add($uid,$type){
        $areaInfo = AreaLib::getByIp();
        $data = array(
            'uid'=>$uid,
            'a_time'=>time(),
            'IP'=>$areaInfo['IP'],
            'long'=>$areaInfo['lng'],
            'lat'=>$areaInfo['lat'],//纬度
            'login_type'=>$type,
            'channel'=>$areaInfo['channel'],
            'area'=>json_encode($areaInfo),
            'country'=>$areaInfo['country'],
            'province'=>$areaInfo['province'],
            'city'=>$areaInfo['city'],
            'type'=>$type,
        );

        return self::db()->add($data);
    }
	
}