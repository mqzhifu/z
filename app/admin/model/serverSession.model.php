<?php
class serverSessionModel {
	static $_table = 'server_session';
	static $_pk = 'id';
	static $_db = null;
	static $_status_desc = array('未处理','未分配','等待中','进行中','已结束');


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