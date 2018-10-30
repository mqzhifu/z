<?php

namespace app\Models;

use app\Tools\LzLog;
use Server\CoreBase\Model;
use Server\Asyn\Mysql\Miner;
use Server\CoreBase\SwooleException;

class ChampionModel extends Model {

    const ORDER_COIN_TYPE_GIFT = 1 ; //买礼物
    const ORDER_COIN_TYPE_ACTION = 2 ; //行为

    const USER_GIFT_STATUS_NONE = 0 ;   //未收取
    const USER_GIFT_STATUS_GET = 1 ; // 已收取

    public function getUserTable($uId) {
        $table = "user_" . $uId % 10;

        return $table;
    }

    function getRedis(){
        $redis = new \Redis();

        $config = get_instance()->config;
        try {
            $status = $redis->connect($config['redis']['global']['ip'], $config['redis']['global']['port']);
            if (isset( $config['redis']['global']['password'])) {
                $redis->auth($config ['redis']['global']['password']);
            }
        } catch (\Exception $e) {
            throw new SwooleException('redis connect error');
        }

        return $redis;
    }


    function exec(){
        LzLog::dEcho2("ChampionModel.exec", __FILE__, __LINE__, ":start") ;

//        $redis = $this->getRedis();
//        $inc = $redis->get("test_task");
//        if(!$inc)
//            $inc = 0;
//        $redis->set("test_task",++$inc);

        $round = 1;

        $gameList = $this->config['main']['fastMatchGameList'];
        foreach($gameList as $k=>$v){
            $task = $this->loader->task("championMatchTask");
            $task->run($round,$k,1);
            $task->startTask();
        }


        LzLog::dEcho2("ChampionModel.exec,inc:", __FILE__, __LINE__, ":$inc") ;
    }
    
    public function saveMsgLog($sendUid, $recvUid, $content){
        $dao = get_instance()->getAsynPool('dbDao');
        $statement = yield $dao->dbQueryBuilder->insert("im_msglog")
            ->set('send_uid', $sendUid)
            ->set('recv_uid', $recvUid)
            ->set('content', $content)
            ->set('created_at', time())
            ->set('im_type', 'youme')
            ->coroutineSend();
        return $statement['insert_id'] ;
    }
    
    /**
     * 赠送礼物
     * @param int $fromUid 用户ID
     * @param int $toUid 目标用户ID
     * @param int $giftId 礼物ID
     * @param int $giftNum 礼物数量
     * @return mixed
     */
    public function doPresent($fromUid, $toUid, $giftId, $giftNum){
        $dao = $this->mysql_pool;
        $transaction_id = yield $dao->coroutineBegin($this);
        $userTable = $this->getUserTable($fromUid) ;
        try{
            $sql = "select * from goods_gift where id='{$giftId}' limit 1 " ;

            $statement = yield $dao->dbQueryBuilder->coroutineSend($transaction_id, $sql);
            if (!$statement || !$statement['result']) {
                LzLog::dEcho2("DbModel.doPresent", __FILE__, __LINE__, $sql) ;
                throw new \Exception("10001", 10001) ;
            }
            $giftInfo = $statement['result'][0] ;
            $coinTotal = $giftInfo['price'] * $giftNum ;

            //检查用户是否金币足够
            $sql = "select user_id,user_gold_num from {$userTable} where user_id={$fromUid} FOR UPDATE " ; // lock table,防止重复提交
            $statement = yield $dao->dbQueryBuilder->coroutineSend($transaction_id, $sql);
            $userGoldInfo = $statement['result'][0] ;
            if ($userGoldInfo['user_gold_num']<$coinTotal) {
                LzLog::dEcho2("DbModel.doPresent", __FILE__, __LINE__, $userGoldInfo, $coinTotal) ;
                throw new \Exception("10002", 10002) ;
            }

            //扣金币
            $changeGold = $userGoldInfo['user_gold_num'] - $coinTotal ;
            $sql = "update {$userTable} set user_gold_num={$changeGold} where user_id={$fromUid}" ;
            $statement = yield $dao->dbQueryBuilder->coroutineSend($transaction_id, $sql);
            if ($statement['affected_rows']<=0){
                LzLog::dEcho2("DbModel.doPresent", __FILE__, __LINE__, $sql) ;
                throw new \Exception("10003", 10003) ; // 扣金币失败
            }

            //生成订单
            $otype = self::ORDER_COIN_TYPE_GIFT ;
            $time = time();
            $save = [
                'uid' => $fromUid,
                'type' => $otype,
                'gid'=>$giftId,
                'num'=>$giftNum,
                'created_at'=>$time
            ] ;
            $sql = "insert into order_coin(`".implode("`,`", array_keys($save))."`) values('".implode("','", $save)."')";
            $statement = yield $dao->dbQueryBuilder->coroutineSend($transaction_id, $sql);
            if (!$statement['insert_id'] || $statement['insert_id']<=0){
                LzLog::dEcho2("DbModel.doPresent", __FILE__, __LINE__, $sql) ;
                throw new \Exception("10004", 10004) ; // 生成订单失败
            }
            $oid = $statement['insert_id'] ;

            //记录礼物
            $save = [
                'from_uid' => $fromUid ,
                'to_uid' => $toUid ,
                'status' => self::USER_GIFT_STATUS_NONE ,
                'gift_id' => $giftId ,
                'gift_num' => $giftNum ,
                'order_id' => $oid,
                'created_at' => $time ,
            ] ;
            $sql = "insert into user_gift(`".implode("`,`", array_keys($save))."`) values('".implode("','", $save)."')";
            $statement = yield $dao->dbQueryBuilder->coroutineSend($transaction_id, $sql);
            if (!$statement['insert_id'] || $statement['insert_id']<=0){
                LzLog::dEcho2("DbModel.doPresent", __FILE__, __LINE__, $sql) ;
                throw new \Exception("10005", 10005) ; // 生成礼物失败
            }
            $userGiftId = $statement['insert_id'] ;

            //返回
            $ret = [
                'gift_id' => $giftInfo['id'] ,
                'gift_name' => $giftInfo['name'] ,
                'gift_icon' => $giftInfo['icon'] ,
                'gift_num' => $giftNum ,
                'gift_from_uid' => $fromUid ,
                'user_gift_id' => $userGiftId ,
                'user_gift_status' => self::USER_GIFT_STATUS_NONE
            ] ;

            // commit
            yield $dao->coroutineCommit($transaction_id);
        }catch (\Exception $e){
            yield $dao->coroutineRollback($transaction_id) ;
            return ['succ'=>0, 'data'=>$e->getMessage()] ; //金币不足
        }

        //更新缓存
        $userModel = new UserModel() ;
        $userModel->setUserCache($fromUid, ['gold'=>$changeGold]) ;

        return ['succ'=>1, 'data'=>$ret] ;
    }

    public function setPkLog($data, $roomInfo){

        $uidHash = md5($data[0]['uId']."-".$data[1]['uId']) ;

        $saveList[] = [
            'uid' => $data[0]['uId'] ,
            'uid_other' => $data[1]['uId'] ,
            'game_id' => $roomInfo['gameId'] ,
            'game_name' => $roomInfo['gameName'] ,
            'room_id' => $roomInfo['roomId'] ,
            'win' => $data[0]['win'] ,
            'match_type'=>$roomInfo['match_type'],
            'fastmatch_room_level_id'=>$roomInfo['room_level_id'],
        ] ;
        $saveList[] = [
            'uid' => $data[1]['uId'] ,
            'uid_other' => $data[0]['uId'] ,
            'game_id' => $roomInfo['gameId'] ,
            'game_name' => $roomInfo['gameName'] ,
            'room_id' => $roomInfo['roomId'] ,
            'win' => $data[1]['win'] ,
            'match_type'=>$roomInfo['match_type'],
            'fastmatch_room_level_id'=>$roomInfo['room_level_id']
        ] ;

//        $dao = get_instance()->getAsynPool('dbDao');

        $conf = $this->config['mysql']['db'];
        $dao = new Miner();
//        try {
            $dao->pdoConnect($conf);
//        } catch (\Exception $e) {
//            throw new SwooleException('pdo connect error');
//            return [];
//        }

        $ids = [];
        foreach ($saveList as $save) {
            $statement = $dao->insert("pklog")
                ->set('created_at', time())
                ->set('uid', $save['uid'])
                ->set('uid_other', $save['uid_other'])
                ->set('uid_hash', $uidHash)
                ->set('game_id', $save['game_id'])
                ->set('game_name', $save['game_name'])
                ->set('room_id', $save['room_id'])
                ->set('win', $save['win'])
                ->set('fastmatch_room_level_id', $save['fastmatch_room_level_id'])
                ->set('match_type', $save['match_type'])
                ->pdoQuery();

            //快速赛 这里需要，更新一下信息
            //因为，房间信息 初始化后，是存在REDIS里，所以USER_GOLD_LOG 的MEMO字段是有问题的，需要更新回来
            //又因为，扣金币只扣非机器人，所以只有一条机器人的数据需要更新
            if($roomInfo['match_type'] == 3){
                if($save['uid'] < 20000000){
                    $rs = $dao->update("user_gold_log")
                        ->set('memo', $statement['insert_id'])
//                        ->where('user_id', $save['uid'])
//                        ->where('type_key', 'fastmatch_singin')
                        ->where('memo',$roomInfo['roomId'])
                        ->pdoQuery();

                }
            }

            $ids[] = array('insert_id'=>$statement['insert_id'],'uid'=>$save['uid'],'win'=>$save['win']);
        }



        return $ids;
    }

    // 随机获取单个游戏
    public function getGameInfo() {
        $gameList = $this->getGameList();
        $count = count($gameList);
        $rand = rand(0, $count - 1);
        return $gameList[$rand];
    }

    // 获取游戏列表
    public function getGameList() {
        $conf = $this->config['mysql']['db'];
        $dao = new Miner();
        try {
            $dao->pdoConnect($conf);
        } catch (\Exception $e) {
            throw new SwooleException('pdo connect error');
            return [];
        }

        $table = 'games_info';
        $rs = $dao->select('*')
            ->from($table)
            ->where('game_state', 2)
            ->where('game_status', 'online')
            ->pdoQuery();

        $lists = [];
        if ($rs) {
            foreach ($rs['result'] as $key => $val) {
                if ($val['game_show'] != 1)
                    continue;
                $row = [
                    'gameId' => (int)$val['game_id'],
                    'gameName' => $val['game_name'],
                    'gameLabel' => $val['game_label']
                ];
                $lists[] = $row;
            }
        }

        $dao = null;
        return $lists;
    }

    // 获取游戏信息
    public function getGameCache($gameId) {
        $redis = get_instance()->getAsynPool('redisDao');
        $cacheTag = $this->config['main']['cacheKey'];
        $cacheKey = "{$cacheTag}_game_{$gameId}";
        $gameInfo = yield $redis->getCoroutine()->get($cacheKey);
        if ($gameInfo) {
            return json_decode($gameInfo, true);
        } else {
            $gameInfo = $this->gameInfo($gameId);
            if ($gameInfo) {
                $redis->getCoroutine()->setex($cacheKey, 86400, json_encode($gameInfo));
            }
            return $gameInfo;
        }
    }

    // 获取用户地址
    public function getUserAreaCache($uId) {
        $cacheTag = $this->config['main']['cacheKey'];
        $cacheKey = "{$cacheTag}_lbs_{$uId}";
        $userAreaInfo = yield $this->redis_pool->getCoroutine()->get($cacheKey);
        if ($userAreaInfo) {
            return json_decode($userAreaInfo, true);
        } else {
            $userArea = $this->getUserAreaSynch($uId);
            if ($userArea) {
                $this->redis_pool->getCoroutine()->setex($cacheKey, $this->config['main']['cacheTime']['userCache'], json_encode($userArea));
            }
            return $userArea;
        }
    }

    // 同步获取用户地址
    public function getUserAreaCacheSynch($uId) {
        $redis = new \Redis();
        $redis->connect($this->config['redis']['user']['ip'], $this->config['redis']['user']['port']);
        if (isset($this->config['redis']['user']['password'])) {
            $redis->auth($this->config['redis']['user']['password']);
        }

        $cacheTag = $this->config['main']['cacheKey'];
        $cacheKey = "{$cacheTag}_lbs_{$uId}";
        $userAreaInfo = $redis->get($cacheKey);
        if ($userAreaInfo) {
            $userAreaInfo = json_decode($userAreaInfo, true);
        } else {
            $userAreaInfo = $this->getUserAreaSynch($uId);
            if ($userAreaInfo) {
                $redis->setex($cacheKey, $this->config['main']['cacheTime']['userCache'], json_encode($userAreaInfo));
            }
        }

        return $userAreaInfo;
    }


    private function getUserAreaSynch($uId) {
        $conf = $this->config['mysql']['db'];
        $dao = new Miner();
        $dao->pdoConnect($conf);

        $table = 'user_location';
        $data = $dao->select('*')->from($table)->where('user_id', $uId)->pdoQuery();
        if (empty($data['result'])) {
            return array('areaId' => '', 'areaName' => '');
        }

        $digit = $this->config['main']['geohashInfo']['digit'];
        $userAreaInfo = $data['result'][0];
        $location = [];
        $location['code'] = substr($userAreaInfo['ul_geo_code'], 0, $digit);
        $location['lat'] = $userAreaInfo['ul_latitude'];
        $location['lon'] = $userAreaInfo['ul_longitude'];
        $location['areaId'] = $userAreaInfo['ul_area_id'];
        $location['areaName'] = $userAreaInfo['ul_area_name'];
        $location['time'] = $userAreaInfo['ul_update_time'];
        return $location;
    }

    public function gameInfo($gameId) {

        $conf = $this->config['mysql']['db'];
        $dao = new Miner();
        try {
            $dao->pdoConnect($conf);
        } catch (\Exception $e) {
            throw new SwooleException('pdo connect error');
        }

        $table = 'games_info';
        $data = $dao->select('*')->from($table)->where('game_id', $gameId)->pdoQuery();

        if (empty($data['result'])) {
            return array();
        }

        $info = $data['result'][0];
        $rs['gameId'] = intval($info['game_id']);
        $rs['deverId'] = intval($info['dever_id']);
        $rs['categoryId'] = intval($info['game_category_id']);
        $rs['name'] = trim($info['game_name']);
        $rs['appKey'] = trim($info['game_app_key']);
        $rs['url'] = trim($info['game_url']);
        $rs['payCallBackUrl'] = trim($info['game_pay_callback_url']);
        $rs['icon'] = trim($info['game_icon']);
        $rs['other'] = json_decode($info['game_other'], TRUE);
        $rs['createTime'] = $info['game_create_time'];
        $rs['label'] = $info['game_label'];
        return $rs;
    }

}
