<?php
class MemcacheLib {
	public $_host = '127.0.0.1';
	public $_port = '11211';
	public $_cache = null;
	public $_GetCnt= array();
	public $_SetCnt = array();
	public $_Log = array();
	public $_time = 60;
	
	function initMemcache(){
		if(!$this->_cache){
			$cache = new Memcache();
			$rs = $cache->connect($this->_host,$this->_port );
			if(!$rs)
				throw new ExceptionFrameLib('memcache 连接失败');
			//缓存数据过大，会自动开启压缩
			$cache->setCompressThreshold(20000, 0.2);
			$this->_cache = $cache;
		}
	}
	//清空所有MEM数据
	function clear(){
		$this->initMemcache();
		return $this->_cache->flush();
	}
	
	function set($k,$v,$time){
		$this->_SetCnt[]  = $k;
		$this->initMemcache();
		return $this->_cache->set($k,$v,0,$time);	
	}

	function get($k){
		$this->_GetCnt[]  = $k;
		$this->initMemcache();
		return $this->_cache->get($k);
	}
	
	//这样可以减少请求次数
	function get_multi($keys){
		$this->initMemcache();
		return $this->_cache->get($keys);
	}
	
	function replace($key,$v){
		$this->initMemcache();
		return $this->_cache->replace($key,$v);
	}
	//递减一个数字值
	function decremen($key,$v){
		$this->initMemcache();
		return $this->_cache->decremen($key,$v);
	}
	//递增一个数字值
	function increment($key,$v){
		$this->initMemcache();
		return $this->_cache->increment($key,$v);
	}
	
	function append($k,$v){
		$this->initMemcache();
		return $this->_cache->append($k,$v);
	}
	
	function del($key){
		$this->initMemcache();
		return $this->_cache->delete($key);
	}
	//获取服务的 get/set 等统计信息
	function getStats(){
		$this->initMemcache();
		$m = $this->_cache->getStats();
		foreach($m as $k=>$v){
			echo $k . ':' .$v ."<br/>";
		}
	}
	//获取PHP类自己的MEM统计信息
	function memInfo(){
		$str = 'memcache:GET nums:'.count($this->mGetCnt).' | SET nums:'.count($this->mSetCnt);
		return $str;
	}
}
