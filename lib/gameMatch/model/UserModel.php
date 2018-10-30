<?php

/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 16-7-15
 * Time: 下午1:44
 */

namespace app\Models;

use Server\CoreBase\Model;
use Server\MooPHP\MooUtil;
use Server\Asyn\Mysql\Miner;
use app\Tools\LzLog ;
use app\Tools\SystemHint ;

class UserModel extends Model {

    const EXP_TYPE_WIN = 1; // 赢了
    const EXP_TYPE_LOSE = 2 ; //输了

    const EXP_DAY_LIMIT = 100; // 限制每日最高获取100积分
    

    const REASON_DAY_LIMIT = "REASON_DAY_LIMIT" ; // 达到每日积分上限

    public static function getExpNum($type=null) {
        $list = [
            self::EXP_TYPE_LOSE => 0 ,
            self::EXP_TYPE_WIN => 10
        ] ;

        if($type) {
            return $list[$type] ;
        }

        return $list ;
    }

    public function getTable($uId) {
        $table = "user_" . $uId % 10;

        return $table;
    }

    /**
     * 对战增加亲密度
     * @param $uid
     * @param $fuid
     * @param int $incr
     * @return bool|\Generator
     */
    public function battleAddDearValue($uid, $fuid){
        $host = $this->config['main']['httpService']['host'];
        $token = $this->config['main']['httpService']['token'];
        $getParams = [
            'token'=>md5($token) ,
            'uid'=>$uid,
            'fuid' => $fuid
        ] ;
        
        $getParams = http_build_query($getParams) ;
        $url = sprintf("%s/%s?%s", $host, "Service.pkSetDearValue", $getParams);
        $result = file_get_contents($url) ;

        LzLog::dEcho2("UserModel.battleAddDearValue", __FILE__, __LINE__, ["对战增加亲密度",$uid, $fuid, $url, $result]) ;
    }

    /**
     * 对战后增加奖励
     * @param $uid
     * @param $isWin
     * @param $roomId
     * @return array|\Generator|mixed
     */
    public function battleAddExp($uid, $isWin, $roomId){

        $type = self::EXP_TYPE_LOSE ;
        if ($isWin){
            $type = self::EXP_TYPE_WIN ;
        }

        $save['uid'] = $uid ;
        $save['exp'] = self::getExpNum($type) ;
        $save['real_exp'] = $save['exp'] ;
        $save['type'] = $type ;
        $save['ext_param'] = $roomId ;
        $save['created_at'] = time();
        $save['reason'] = '' ;

        $dao = $this->mysql_pool;
        $transaction_id = yield $dao->coroutineBegin($this);
        try{
            $sql = "select * from user_exp_record where ext_param='{$roomId}' and uid={$uid} limit 1 FOR UPDATE " ;
            $value = yield $dao->dbQueryBuilder->coroutineSend($transaction_id, $sql);
            if ($value && $value['result']){

                throw new \Exception($value['result'][0]['exp'], 200300) ;
            }

            //用户今日获取积分数
            $todayStart = strtotime(sprintf("%s 00:00:00", date("Y-m-d"))) ;
            $sql = "select sum(real_exp) as today_exp from user_exp_record where created_at>{$todayStart} and uid={$uid} " ;
            $value = yield $dao->dbQueryBuilder->coroutineSend($transaction_id, $sql);
            $todayRemainExp = self::EXP_DAY_LIMIT - $value['result'][0]['today_exp'] ; // 今日剩余可获取

            //本次实际获取积分数
            if ($save['exp']>$todayRemainExp){
                $save['real_exp'] = $todayRemainExp ;
                $save['reason'] = self::REASON_DAY_LIMIT ;
            }

            $table = $this->getTable($uid);

            $sql = "select * from {$table} where user_id={$uid} limit 1 " ;
            $data = yield $dao->dbQueryBuilder->coroutineSend($transaction_id, $sql);
            if (empty($data['result'])) {
                throw new \Exception("用户不存在", 200301);
            }
            $userInfo = $data['result'][0];
            $lastIntegral = $userInfo['user_integral'];
            $integral = $userInfo['user_integral'] + $save['real_exp'] ;

            //记录积分
            yield $dao->dbQueryBuilder->insert("user_exp_record")
                ->set('uid', $save['uid'])
                ->set('exp', $save['exp'])
                ->set('type', $save['type'])
                ->set('ext_param', $save['ext_param'])
                ->set('created_at', $save['created_at'])
                ->set('real_exp', $save['real_exp'])
                ->set('reason', $save['reason'])
                ->coroutineSend($transaction_id);

            //更新用户积分
            $sql = "update {$table} set user_integral='{$integral}' where user_id={$uid}" ;
            yield $dao->dbQueryBuilder->coroutineSend($transaction_id, $sql);

            // commit
            yield $dao->coroutineCommit($transaction_id);
        }catch (\Exception $e){
            yield $dao->coroutineRollback($transaction_id) ;
            return ['succ'=>0, 'e'=>$e] ;
        }

        //看用户有没有升级捏
        $sql = "select `level`,`need_exp` from base_level where need_exp>{$lastIntegral} order by need_exp asc limit 1" ;
        $nextLevelInfo = yield $dao->dbQueryBuilder->coroutineSend(null, $sql);
        if ($nextLevelInfo['result']){
            $nextLevelInfo = $nextLevelInfo['result'][0] ;
            if ($nextLevelInfo['need_exp']<=$integral){
                yield (new SystemHint())->levelUp($uid, $nextLevelInfo['level']);
            }
        }

        return ['succ'=>1, 'data'=>$save] ;
    }

    /**
     * 获取需要恢复对战记录
     * @param $uid
     * @param $roomId
     * @return bool|string
     */
    public function getLastLck($uid, $roomId){

        $redis = new \Redis();
        $redis->connect($this->config['redis']['global']['ip'], $this->config['redis']['global']['port']);
        if (isset($this->config['redis']['global']['password'])) {
            $redis->auth($this->config['redis']['global']['password']);
        }
        $cacheTag = $this->config['main']['cacheKey'];
        $cacheKey = "{$cacheTag}_resumeGameLck_{$roomId}";
        $info = $redis->get($cacheKey);
        if ($info) {
            
            return $info;
        }else{
            return false;
        }
        
    }

    /**
     * 删除需要恢复对战记录
     * @param $roomId
     * @return bool
     */
    public function deleteLastLck($roomId){
        $redis = new \Redis();
        $redis->connect($this->config['redis']['global']['ip'], $this->config['redis']['global']['port']);
        if (isset($this->config['redis']['global']['password'])) {
            $redis->auth($this->config['redis']['global']['password']);
        }
        $cacheTag = $this->config['main']['cacheKey'];
        $cacheKey = "{$cacheTag}_resumeGameLck_{$roomId}";
        $redis->del($cacheKey);
        return true;
    }

    /**
     * 将需要恢复对战记录保存到内存
     * @param $roomId
     * @param $detail
     * @return bool
     */
    public function setLastLck($roomId, $detail){
        $redis = new \Redis();
        $redis->connect($this->config['redis']['global']['ip'], $this->config['redis']['global']['port']);
        if (isset($this->config['redis']['global']['password'])) {
            $redis->auth($this->config['redis']['global']['password']);
        }
        $cacheTag = $this->config['main']['cacheKey'];
        $cacheKey = "{$cacheTag}_resumeGameLck_{$roomId}";
        if (is_array($detail)){
            $detail = json_encode($detail) ;
        }
        $cTime = $this->config['main']['cacheTime']['roomCache'];
        $info = $redis->set($cacheKey, $detail, $cTime);
        if ($info) {
            
            return $info;
        }else{
            return false;
        }
    }

    /**
     * @param $lckDetail
     * @return bool|\Generator
     * 添加用户战绩
     */
    public function addLck($lckDetail) {
        $this->setLastLck($lckDetail['roomId'], $lckDetail) ;
        
        $dao = $this->mysql_pool;
        $time = time();
        $date = date('Ymd');
        foreach ($lckDetail['list'] as $row) {
            if ($row['uId'] <= $this->config['main']['robotId'])
                continue;
            $suffix = $row['uId'] % 100;
            $table = "user_lck_{$suffix}";

            foreach ($lckDetail['list'] as $item) {
                if ($row['uId'] == $item['uId'])
                    continue;
                yield $dao->dbQueryBuilder->insert($table)
                    ->set('user_id', $row['uId'])
                    ->set('ul_room_id', $lckDetail['roomId'])
                    ->set('game_id', $lckDetail['gameId'])
                    ->set('ul_uid', $item['uId'])
                    ->set('ul_date', $date)
                    ->set('ul_content', json_encode($lckDetail))
                    ->set('ul_dateline', $time)
                    ->coroutineSend();
            }
        }
        
        
        return true;
    }

    public function setUserCache($uId, $params) {
        $userCache = yield $this->getUserCache($uId);
        foreach ($params as $key => $val) {
            $userCache[$key] = $val;
        }

        $cacheTag = $this->config['main']['cacheKey'];
        $cacheKey = "{$cacheTag}_user_{$uId}";
        

        yield $this->redis_pool->getCoroutine()->setex($cacheKey, $this->config['main']['cacheTime']['userCache'], json_encode($userCache));
        return true;
    }

    private function getConstellation($birthday) {
        $constellation = '';
        if (!$birthday) {
            return $constellation;
        }
        $birthday = date('Y-m-d', strtotime($birthday));

        $constellations = [
            '01' => [
                    ["start" => 1, "end" => 19, "name" => "摩羯座"],
                    ["start" => 20, "end" => 31, "name" => "水平座"]
            ],
            '02' => [
                    ["start" => 1, "end" => 18, "name" => "水平座"],
                    ["start" => 19, "end" => 29, "name" => "双鱼座"]
            ],
            '03' => [
                    ["start" => 1, "end" => 20, "name" => "双鱼座"],
                    ["start" => 21, "end" => 31, "name" => "白羊座"]
            ],
            '04' => [
                    ["start" => 1, "end" => 19, "name" => "白羊座"],
                    ["start" => 20, "end" => 30, "name" => "金牛座"]
            ],
            '05' => [
                    ["start" => 1, "end" => 20, "name" => "金牛座"],
                    ["start" => 21, "end" => 30, "name" => "双子座"]
            ],
            '06' => [
                    ["start" => 1, "end" => 21, "name" => "双子座"],
                    ["start" => 22, "end" => 30, "name" => "巨蟹座"]
            ],
            '07' => [
                    ["start" => 1, "end" => 22, "name" => "巨蟹座"],
                    ["start" => 23, "end" => 31, "name" => "狮子座"]
            ],
            '08' => [
                    ["start" => 1, "end" => 22, "name" => "狮子座"],
                    ["start" => 23, "end" => 31, "name" => "处女座"]
            ],
            '09' => [
                    ["start" => 1, "end" => 22, "name" => "处女座"],
                    ["start" => 23, "end" => 30, "name" => "天秤座"]
            ],
            '10' => [
                    ["start" => 1, "end" => 23, "name" => "天秤座"],
                    ["start" => 24, "end" => 31, "name" => "天蝎座"]
            ],
            '11' => [
                    ["start" => 1, "end" => 22, "name" => "天蝎座"],
                    ["start" => 23, "end" => 30, "name" => "射手座"]
            ],
            '12' => [
                    ["start" => 1, "end" => 21, "name" => "射手座"],
                    ["start" => 22, "end" => 31, "name" => "摩羯座"]
            ]
        ];


        $arr = explode('-', $birthday);

        foreach ($constellations[$arr[1]] as $val) {
            if ($val['start'] <= $arr[2] && $arr[2] <= $val['end']) {
                $constellation = $val['name'];
                break;
            }
        }

        return $constellation;
    }

    // 获取匹配信息
    public function getMatchGameInfo($uId) {
        $redis = new \Redis();
        $redis->connect($this->config['redis']['global']['ip'], $this->config['redis']['global']['port']);
        if (isset($this->config['redis']['global']['password'])) {
            $redis->auth($this->config['redis']['global']['password']);
        }
        $cacheTag = $this->config['main']['cacheKey'];
        $matchCacheKey = "{$cacheTag}_userMatchGame_{$uId}";
        $info = $redis->get($matchCacheKey);
        if ($info) {
            $info = json_decode($info, true);
        }
        return $info;
    }

    // 删除匹配信息
    public function delMatchGameInfo($uId) {
        $redis = new \Redis();
        $redis->connect($this->config['redis']['global']['ip'], $this->config['redis']['global']['port']);
        if (isset($this->config['redis']['global']['password'])) {
            $redis->auth($this->config['redis']['global']['password']);
        }
        $cacheTag = $this->config['main']['cacheKey'];
        $matchCacheKey = "{$cacheTag}_userMatchGame_{$uId}";
        $redis->del($matchCacheKey);
        return true;
    }

    // 获取匹配信息
    public function getMatchInfo($uId) {
        $redis = new \Redis();
        $redis->connect($this->config['redis']['global']['ip'], $this->config['redis']['global']['port']);
        if (isset($this->config['redis']['global']['password'])) {
            $redis->auth($this->config['redis']['global']['password']);
        }
        $cacheTag = $this->config['main']['cacheKey'];
        $matchCacheKey = "{$cacheTag}_userMatch_{$uId}";
        $info = $redis->get($matchCacheKey);
        if ($info) {
            $info = json_decode($info, true);
        }
        return $info;
    }

    // 删除匹配信息
    public function delMatchInfo($uId) {
        $redis = new \Redis();
        $redis->connect($this->config['redis']['global']['ip'], $this->config['redis']['global']['port']);
        if (isset($this->config['redis']['global']['password'])) {
            $redis->auth($this->config['redis']['global']['password']);
        }
        $cacheTag = $this->config['main']['cacheKey'];
        $matchCacheKey = "{$cacheTag}_userMatch_{$uId}";
        $redis->del($matchCacheKey);
        return true;
    }

    public function setRoomId($uId, $roomId) {
        yield $this->setUserCache($uId, array(
                    'roomId' => $roomId
        ));
        return true;
    }

    public function setUserCacheSynch($uId, $params) {
        $redis = new \Redis();
        $redis->connect($this->config['redis']['user']['ip'], $this->config['redis']['user']['port']);
        if (isset($this->config['redis']['user']['password'])) {
            $redis->auth($this->config['redis']['user']['password']);
        }

        $cacheTag = $this->config['main']['cacheKey'];
        $cacheKey = "{$cacheTag}_user_{$uId}";
        $userCache = $redis->get($cacheKey);
        if (empty($userCache)) {
            return true;
        }

        $userCache = json_decode($userCache, true);
        foreach ($params as $key => $val) {
            $userCache[$key] = $val;
        }
        
        $redis->setex($cacheKey, $this->config['main']['cacheTime']['userCache'], json_encode($userCache));
        return true;
    }

    public function getUserCacheSynch($uId) {
        $redis = new \Redis();
        $redis->connect($this->config['redis']['user']['ip'], $this->config['redis']['user']['port']);
        if (isset($this->config['redis']['user']['password'])) {
            $redis->auth($this->config['redis']['user']['password']);
        }

        $cacheTag = $this->config['main']['cacheKey'];
        $cacheKey = "{$cacheTag}_user_{$uId}";
        $redis_user = $redis->get($cacheKey);

        LzLog::dEcho2("redis_userinfo:", __FILE__, __LINE__,$redis_user) ;
        if ($redis_user   ) {
            $user = json_decode($redis_user, true);
            if( ! $user['uid'] ){
                $user  = $this->userInfoErrRestInfo($uId,$user);
            }
        } else {
            $user = $this->getUserSynch($uId);
            LzLog::dEcho2("userinfo is null~reset uInfo:getUserSynch", __FILE__, __LINE__, $user) ;
            if ($user) {
                $dbModel = $this->loader->model("DbModel", $this);
                $areaInfo = $dbModel->getUserAreaCacheSynch($user['uid']);
                $user['areaId'] = $areaInfo['areaId'];
                $user['areaName'] = $areaInfo['areaName'];
                $redis->setex($cacheKey, $this->config['main']['cacheTime']['userCache'], json_encode($user));

            }else{
                LzLog::dEcho2("swoole_err:getUserSynch info is null", __FILE__, __LINE__,$user) ;
            }

            $dbModel = $this->loader->model("DbModel", $this);
            $areaInfo = $dbModel->getUserAreaCacheSynch($user['uid']);
            $user['areaId'] = $areaInfo['areaId'];
            $user['areaName'] = $areaInfo['areaName'];
            $redis->setex($cacheKey, $this->config['main']['cacheTime']['userCache'], json_encode($user));
        }

        $user['constellation'] = $this->getConstellation($user['birthday']);
        return $user;
    }

    public function setOffline($uId) {
        $cacheTag = $this->config['main']['cacheKey'];
        $cacheKey = "{$cacheTag}_user_{$uId}";
        $user = yield $this->getUserCache($uId);

        if ($user['match'] == 1) {
            $mod = new MatchModel();
            yield $mod->addLeaveSingle($uId);
        }
        $roomId = $user['roomId'];
        $user['online'] = 0;
        $user['roomId'] = '';
        $user['match'] = 0;
        $user['chat'] = [];
        $this->redis_pool->getCoroutine()->setex($cacheKey, $this->config['main']['cacheTime']['userCache'], json_encode($user));

        $rs = [];
        // 删除房间信息
        if ($roomId) {
            $roomModel = $this->loader->model('RoomModel', $this);
            $roomInfo = yield $roomModel->getRoomInfo($roomId);
            if (isset($roomInfo['list'][$uId])) {
                $rs['roomInfo'] = $roomInfo;
            }
        }

        if (isset($user['chat']) && $user['chat']) {
            $rs['chat'] = $user['chat'];
        }

        if ($rs) {
            return $rs;
        }
        return true;
    }

    public function getUserCache($uId) {
        $cacheTag = $this->config['main']['cacheKey'];
        $cacheKey = "{$cacheTag}_user_{$uId}";

        $user = yield $this->redis_pool->getCoroutine()->get($cacheKey);
        if ($user) {//怀疑这里有点问题
            $user = json_decode($user, true);
            if( ! $user['uid'] ){
                $user  = $this->userInfoErrRestInfo($uId,$user);
            }
        } else {
            $user = yield $this->getUser($uId);
            if ($user) {
                $dbModel = $this->loader->model("DbModel", $this);
                $areaInfo = yield $dbModel->getUserAreaCache($user['uid']);
                $user['areaId'] = $areaInfo['areaId'];
                $user['areaName'] = $areaInfo['areaName'];
                $this->redis_pool->getCoroutine()->setex($cacheKey, $this->config['main']['cacheTime']['userCache'], json_encode($user));
            }
        }
        $user['constellation'] = $this->getConstellation($user['birthday']);
        return $user;
    }

    public function getUsersCache($uIds) {
        if (empty($uIds)) {
            return [];
        }

        $cacheTag = $this->config['main']['cacheKey'];
        $cacheKeys = [];
        foreach ($uIds as $uId) {
            $cacheKeys[] = "{$cacheTag}_user_{$uId}";
        }

        $users = yield $this->redis_pool->getCoroutine()->mget($cacheKeys);
        foreach ($users as $user) {
            $user = json_decode($user, true);
            $rs[$user['uid']] = $user;
        }
        return $rs;
    }

    public function getUserSynch($uId) {
        $table = $this->getTable($uId);

        $data = $this->mysql_pool->getSync()->select('*')->from($table)->where('user_id', $uId)->pdoQuery();
        if (empty($data['result'])) {
            return array();
        }
        $userInfo = $data['result'][0];

        $info = [];
        $otherInfo = json_decode($userInfo['user_other'], true);
        $info['uid'] = intval($userInfo['user_id']);
        $info['name'] = trim($userInfo['user_name']);
        $info['avatar'] = trim($userInfo['user_avatar']);
        $info['sex'] = intval($userInfo['user_sex']);
        $info['mobile'] = $otherInfo['mobile'];
        $info['chanId'] = intval($userInfo['chan_id']);
        $info['subscribe'] = intval($userInfo['user_subscribe']);
        $info['adId'] = intval($userInfo['user_adid']);
        $info['schoolId'] = intval($userInfo['user_school_id']);
        $info['schoolName'] = trim($userInfo['user_school_name']);
        $info['gold'] = intval($userInfo['user_gold_num']);
        $info['integral'] = intval($userInfo['user_integral']);
        $info['diamond'] = intval($userInfo['user_diamond']);
        $info['rose'] = intval($userInfo['user_rose']);
        $info['achievement'] = intval($userInfo['user_achievement']);

        $info['dormId'] = intval($userInfo['user_dorm_id']);
        $info['enrolYear'] = intval($userInfo['user_enrol_year']);
        $info['userType'] = intval($otherInfo['userType']);
//        $info['online'] = 0;
//        $info['roomId'] = '';
//        $info['match'] = 0;
        if ($userInfo['user_birthday']) {
            $info['birthday'] = date('Y-m-d', strtotime($userInfo['user_birthday']));
            $info['age'] = $this->geUserAge($userInfo['user_birthday']);
        } else {
            $info['birthday'] = '';
            $info['age'] = 0;
        }
        $info['other'] = $otherInfo;
        $info['createTime'] = $userInfo['user_create_time'];
        $info['lastTime'] = $userInfo['user_last_time'];
        return $info;
    }

    public function getUser($uId) {
        $table = $this->getTable($uId);
        $data = yield $this->mysql_pool->dbQueryBuilder->select('*')->from($table)->where('user_id', $uId)->coroutineSend();
        if (empty($data['result'])) {
            return array();
        }
        $userInfo = $data['result'][0];

        $info = [];
        $otherInfo = json_decode($userInfo['user_other'], true);
        $info['uid'] = intval($userInfo['user_id']);
        $info['name'] = trim($userInfo['user_name']);
        $info['avatar'] = trim($userInfo['user_avatar']);
        $info['sex'] = intval($userInfo['user_sex']);
        $info['mobile'] = $otherInfo['mobile'];
        $info['chanId'] = intval($userInfo['chan_id']);
        $info['subscribe'] = intval($userInfo['user_subscribe']);
        $info['adId'] = intval($userInfo['user_adid']);
        $info['schoolId'] = intval($userInfo['user_school_id']);
        $info['schoolName'] = trim($userInfo['user_school_name']);
        $info['gold'] = intval($userInfo['user_gold_num']);
        $info['integral'] = intval($userInfo['user_integral']);
        $info['diamond'] = intval($userInfo['user_diamond']);
        $info['rose'] = intval($userInfo['user_rose']);
        $info['achievement'] = intval($userInfo['user_achievement']);
        
        $info['dormId'] = intval($userInfo['user_dorm_id']);
        $info['enrolYear'] = intval($userInfo['user_enrol_year']);
        $info['userType'] = intval($otherInfo['userType']);
//        $info['online'] = 0;
//        $info['roomId'] = '';
//        $info['match'] = 0;
        if ($userInfo['user_birthday']) {
            $info['birthday'] = date('Y-m-d', strtotime($userInfo['user_birthday']));
            $info['age'] = $this->geUserAge($userInfo['user_birthday']);
        } else {
            $info['birthday'] = '';
            $info['age'] = 0;
        }
        $info['other'] = $otherInfo;
        $info['createTime'] = $userInfo['user_create_time'];
        $info['lastTime'] = $userInfo['user_last_time'];
        return $info;
    }

    private function geUserAge($birthday) {
        $a = date("Y", strtotime($birthday));
        $b = date('Y');

        return $b - $a;
    }

    public function getUId($str) {
        $secret = $this->config['main']['tokenSecret'];
        $uId = MooUtil::crypt($str, $secret, 'decode');
        return intval($uId);
    }

    public function setUserDayCache($uId, $cacheInfo) {

        $cacheTag = $this->config['main']['cacheKey'];
        $cacheKey = "{$cacheTag}_user_day_{$uId}";
        $date = date('Ymd');

        $cacheInfo['date'] = $date;
        $time = 86400 * 2 - (time() - strtotime($date));
        yield $this->redis_pool->getCoroutine()->setex($cacheKey, $time, json_encode($cacheInfo));

        return $cacheInfo;
    }

    public function getUserDayCache($uId) {

        $cacheTag = $this->config['main']['cacheKey'];
        $cacheKey = "{$cacheTag}_user_day_{$uId}";
        $cacheInfo = yield $this->redis_pool->getCoroutine()->get($cacheKey);

        $date = date('Ymd');
        if ($cacheInfo) {
            $cacheInfo = json_decode($cacheInfo, true);
            if ($cacheInfo['date'] != $date) {
                $cacheInfo = ['date' => $date];
            } else {
                return $cacheInfo;
            }
        } else {
            $cacheInfo = ['date' => $date];
        }

        $time = 86400 * 2 - (time() - strtotime($date));
        $this->redis_pool->getCoroutine()->setex($cacheKey, $time, json_encode($cacheInfo));

        return $cacheInfo;
    }

    //设置两人每天的对战次数
    public function setPlayGamesNum($uid, $userId) {
        $cacheTag = $this->config['main']['cacheKey'];
        $uidStr = $uid > $userId ? $uid . "_" . $userId : $userId . "_" . $uid;
        $cacheKey = "{$cacheTag}_playgames_" . date("Ymd");
        yield $this->redis_pool->getCoroutine()->ZINCRBY($cacheKey, 1, $uidStr);
        return true;
    }

    //获取两人每天的对战次数
    public function getPlayGamesNum($uid, $userId) {
        $cacheTag = $this->config['main']['cacheKey'];
        $uidStr = $uid > $userId ? $uid . "_" . $userId : $userId . "_" . $uid;
        $cacheKey = "{$cacheTag}_playgames_" . date("Ymd");
        $number = yield $this->redis_pool->getCoroutine()->ZSCORE($cacheKey, $uidStr);
        return (int) $number;
    }

    //更新用户金币
    public function updateUserGold($uid, $gold) {
        yield $this->userModel->setUserCache($uid, ['gold' => $gold]);
        yield $this->setUserGoldInfo($uid, $gold);
        return true;
    }

    //设置用户金币信息
    public function setUserGoldInfo($uid, $gold) {
        $table = "user_" . $uid % 10;
        $dao = $this->mysql_pool;
        yield $dao->dbQueryBuilder->update($table)
                        ->set('user_gold_num', $gold)
                        ->where('user_id', $uid)
                        ->coroutineSend();
        return true;
    }

    public function setUserGoldInfoSynch($uid, $gold) {
        $conf = $this->config['mysql']['user'];
        $dao = new Miner();
        try {
            $dao->pdoConnect($conf);
        } catch (\Exception $e) {
            throw new SwooleException('pdo connect error');
            return [];
        }
        $table = "user_" . $uid % 10;
        $rs = $dao->update($table)
                ->set('user_gold_num', $gold)
                ->where('user_id', $uid)
                ->pdoQuery();
        return true;
    }

    //设置附近的人
    public function setNearOnlineUser($uid) {
        $dbModel = $this->loader->model("DbModel", $this);
        $areaInfo = yield $dbModel->getUserAreaCache($uid);
        if ($areaInfo && isset($areaInfo['code'])) {
            $code = $areaInfo['code'];
            if ($code) {
                $cacheTag = $this->config['main']['cacheKey'];
                $cacheKey = "{$cacheTag}_online_lbs_{$code}";
                yield $this->redis_pool->getCoroutine()->HDEL($cacheKey, $uid);
            }
        }
        return true;
    }

    // 获取登录随机码
    public function getLoginRandom($uid) {
        $cacheTag = $this->config['main']['cacheKey'];
        $cacheKey = "{$cacheTag}_login_random_{$uid}";
        $random = yield $this->redis_pool->getCoroutine()->get($cacheKey);
        return $random;
    }

    function userInfoErrRestInfo($uid,$uInfo){
            LzLog::dEcho2('swoole_err :userInfoErrRestInfo   uid is null,ori data:', __FILE__, __LINE__, $uInfo) ;
            $userInfo =  $this->getUserSynch($uid);
            LzLog::dEcho2('getUserSynch data!', __FILE__, __LINE__, $userInfo) ;
            foreach($userInfo as $k=>$v){
                if(!$v)
                    continue;

                $uInfo[$k] = $v;
            }

            LzLog::dEcho2('use  getUserSynch SET, final data: !', __FILE__, __LINE__, $uInfo) ;

            return $uInfo;
    }

}
