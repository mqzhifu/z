<?php

/**
 *
 * 任务配置
 */
class TaskConfigModel {
    static $_table = 'sunflower_log';
    static $_pk = 'id';
    static $_db_key =DEF_DB_CONN;
    static $_db = null;

    const DAILY_TYPE = 1;
    const GROWNUP_TYPE = 2;

    const FIX_TASK = 1;
    const RANDOM_TASK = 2;

    const OFF_TRUE = 1;
    const OFF_FALSE = 0;

    function getRandomDailyTask($num = 3){
        $sql = "select * from ".$this->_table." where type = ".self::DAILY_TYPE. " and  type_sub =".self::RANDOM_TASK . " and is_off = ".self::OFF_FALSE." limit 0,$num";
        return $this->dbDao->mget($sql);
    }

    function getFixDailyTask($num = 2){
        $sql = "select * from ".$this->_table." where type = ".self::DAILY_TYPE. " and  type_sub =".self::FIX_TASK. " and is_off = ".self::OFF_FALSE." limit 0,$num";
        return $this->dbDao->mget($sql);
    }

    function getFixGrownupTask($num = 3){
        $sql = "select * from ".$this->_table." where type = ".self::GROWNUP_TYPE. " and  type_sub =".self::FIX_TASK. " and is_off = ".self::OFF_FALSE." limit 0,$num";
        return $this->dbDao->mget($sql);
    }

    function getAllGrouwnupTask(){
        $sql = "select * from ".$this->_table." where type = ".self::GROWNUP_TYPE." and is_off = ".self::OFF_FALSE;
        return $this->dbDao->mget($sql);
    }

    function getById($id){
        $sql = "select * from ".$this->_table." where id = ".$id;
        return $this->dbDao->get($sql);
    }

    function getByIds($ids){
        $sql = "select * from ".$this->_table." where id in ($ids) ";
        return $this->dbDao->mget($sql);
    }


}
