<?php
class GameCtrl extends BaseCtrl implements GameInf {
    //用户再次打开游戏，恢复数据
    function getMapInfo(){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['towerMap']['key'],$this->uid);
        $tower = RedisPHPLib::getServerConnFD()->hGetAll($key);

        out_ajax(200,$tower);

    }

    function setMapInfo($mapInfo){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['towerMap'],$this->uid);
        RedisPHPLib::getServerConnFD()->del($key);

        foreach($mapInfo as $k=>$k){
            RedisPHPLib::getServerConnFD()->hSet($key,$k,$k);
        }

        out_ajax(200,'ok');

    }

    //boss升级
    function bossUpgrade(){
        $maxLevel = $GLOBALS['json']['tower'][count($GLOBALS['json']['tower']) -1 ]['level'];
        if($this->uinfo['boss_level'] >= $maxLevel){
            out_ajax(8200);
        }

        if(!$this->uinfo['boss_level'] || $this->uinfo['boss_level'] < 1){
            out_ajax(8201);
        }

        $newLevel = $this->uinfo['boss_level'] + 1;
        if($newLevel > $maxLevel){
            out_ajax(8202);
        }


        if($this->uinfo['goldcoin'] > $GLOBALS['json']['tower'][$newLevel]['price'] ){
            out_ajax(8203);
        }

        if($this->uinfo['sunflower'] > $GLOBALS['json']['tower'][$newLevel]['demfood'] ){
            out_ajax(8204);
        }

        $data = array('uid'=>$this->uid,'ori_level'=>$this->uinfo['boss_level'],'new_level'=>$newLevel);
        BossUpgradeLogModel::db()->add($data);

        $data = array('boss_level'=>$newLevel);
        UserModel::upById($data,$this->uid);

    }
    //基地升级
    function baseUpgrade(){
        $maxLevel = $GLOBALS['json']['home'][count($GLOBALS['json']['home']) -1 ]['level'];
        if($this->uinfo['base_level'] > $maxLevel){

        }

        if(!$this->uinfo['base_level'] || $this->uinfo['base_level'] < 1){

        }

        $newLevel = $this->uinfo['base_level'] + 1;

        if($this->uinfo['base_exp'] < $GLOBALS['json']['home'][$newLevel]['price'] ){
            out_ajax(8203);
        }


        $data = array('uid'=>$this->uid,'ori_level'=>$this->uinfo['base_level'],'new_level'=>$newLevel);
        BaseUpgradeLogModel::db()->add($data);

        $data = array('boss_level'=>$newLevel);
        UserModel::upById($data,$this->uid);


    }
    //合并塔
    function mergeTower($srcTowerId,$targetTowerId,$srcMapId,$targetMapId){
        if($srcMapId > $GLOBALS['main']['map_block_total']){

        }

        //1级合并 不做处理
        if($srcTowerId == 1 && $targetTowerId == 1){

        }

        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['towerMap'],$this->uid);
        RedisPHPLib::getServerConnFD()->hSet($key,$targetTowerId,$targetMapId);
        RedisPHPLib::getServerConnFD()->hDel($key,$srcTowerId);


        $data = array(
            'a_time'=>time(),'uid'=>$this->uid,'src_tower_id'=>$srcTowerId,'tarter_tower_id'=>$targetTowerId,'src_map_id'=>$srcMapId,'tartet_map_id'=>$targetMapId,'status'=>1
        );

        MergeTowerModel::db()->add($data);



    }
    function baseAddExp($num,$type,$isShare = 0,$memo = ''){//基地加血
        $data=  array("base_exp"=>array($num),'uid'=>$this->uid,'type'=>$type);
        UserModel::db()->upById($this->uid,$data);

        BaseExpLogModel::db()->add();
    }
    //基地减少血
    function baseLessExp($num,$type,$isShare = 0,$memo = ''){
        $data=  array("base_exp"=>array($num),'uid'=>$this->uid,'type'=>$type);
        UserModel::db()->upById($this->uid,$data);

        BaseExpLogModel::db()->add();
    }
    //增加愤怒值
    function useAngry($num,$type,$isShare = 0,$memo = ''){

    }
    //减少愤怒值
    function addAngry($num,$type,$isShare = 0,$memo = ''){

    }
}