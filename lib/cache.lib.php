<?php
//memcache 缓存
class CacheLib{
    public $_cache = null;//memcache 实例
    public $_table = "";
    public $_table_key = "";

    function __construct($_table,$time = 0){
        $this->_table = $_table;
        $this->_table_key = "table_key".$this->_table;
        $this->_time = $time;

        $this->_cache = get_instance_of('memcacheLib');
    }

    function get($key){
        return $this->_cache->get($key);
	}
	
	function set($k,$v){
        $keys = $this->getTableKey();
        $this->appendTableKey($k);
		return $this->_cache->set($k,$v,$this->_time);
	}
	
	function getTableKey(){
		return $this->_cache->get($this->_table_key);
	}
	
	function appendTableKey($new_key){
        $table_keys = $this->_cache->get($this->_table_key);
        if($table_keys){
           if(strpos($table_keys,$new_key) === false){
               $this->_cache->append($this->_table_key.",",$new_key,60);
           }
        }else{
            $this->_cache->set($this->_table_key,$new_key,60);
        }

	}
	function delTableCache(){
		$table_cache = $this->getTableKey();
		if($table_cache){
            $keys = explode(",",$table_cache);
			foreach($keys as $k=>$v){
                if($v)
			        $this->_cache->del($v);
			}
        }
	}
}
