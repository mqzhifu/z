<?php
class serverModel {
	static $_table = 'server';
	static $_pk = 'id';
	static $_db = null;

	static $_is_online = array(0=>'未知',1=>'在线',2=>'离线');


	static function db(){
		if(self::$_db)
			return self::$_db;
		
		self::$_db = DbLib::getDbStatic('',self::$_table,self::$_pk);
		return self::$_db;
	}
	
	public static function __callStatic($func, $arguments){
		return call_user_func_array(array(self::db(),$func), $arguments);
	}
	

}