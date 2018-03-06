<?php
class TableLib {
    public $_table =null;
    public $_pk =null;
    public $_cache = null;
    public $_cate_time = 60;

    function __construct($table,$pk,$cacheTime){
        $this->_table = $table;
        $this->_pk = $pk;
        if($cacheTime)
            $this->_cacheTime = $cacheTime;
        else
            $this->_cacheTime = $this->_cate_time;

        $this->_db = DbLib::getDbStatic('',$table,$pk);
        $this->_cache = new CacheLib($this->_table , $this->_cacheTime);

        return $this;
    }

    function getRow($where = ' 1 = 1 ',$table = '' ,$filed = '*'){
        $this->_db->getRow($where,$table,$filed,$this->_cache);
    }

    function getAll($where = ' 1 = 1 ',$table = '' ,$filed = '*'){
        $this->_db->getAll($where,$table,$filed,$this->_cache);
    }

    function add($data){
        $this->_db->add($data,$this->_table,$this->_cache);
    }

    function update($data,$where){
        $this->_db->update($data,$where,$this->_table,$this->_cache);
    }
}