<?php
class GroupUserModel {
	static $_table = 'group_user';
	static $_pk = 'id';
	static $_db = null;
	//1:系统已初始化数据，牌局已生成，等待打骰子
	static $_status = array(0=>'用户已准备',1=>'等待打骰子',2=>'进行中',3=>'已结束');

	static $_auto_add_time = true;
	static $_auto_up_time = true;
	static $_auto_add_time_name = 'a_time';
	static $_auto_up_time_name = 'u_time';

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

	public static function getByRoomId($room_id,$status = 1){
		return self::db()->getAll(" room_id = $room_id and status = $status ");
	}

	public static function upById($data , $id){
		if(self::$_auto_up_time){
			$data[self::$_auto_up_time_name] = time();
		}

		return self::db()->upById($id,$data );
	}



}
