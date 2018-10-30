<?php
//锦票赛
class ChampionGame extends Base {

    private $userModel;
    private $roomModel;
    private $matchModel;
    private $dbModel;
    private $messageModel;


    function __construct()
    {
        $this->userServerState = RedisOpt::getUserServerStateByUid($this->uid);
    }

    //匹配-入口,分2种情况
    //1:第一次报名,2：非第一次报名，也就是<轮次>不一样
    public function matching() {
        $request = new ChampionGameMatchRequest($this->client_data->data);
        //游戏ID
        $gameId = $request->getGameId();
        //房间级别ID
        $roomLevelId = $request->getRoomLevelId();

        LzLog::dEcho2(__CLASS__.__FUNCTION__, __FILE__, __LINE__, ["锦标赛 - 匹配请求-request", $gameId,$roomLevelId,$this->uid]) ;

        if (empty($this->uid)){
            $this->matchingGameError($this->uid, 9000);
            return true;
        }


        if (empty($gameId)) {
            $this->matchingGameError($this->uid, 3002);
            return true;
        }

        if (empty($roomLevelId)) {
            $this->matchingGameError($this->uid, 3003);
            return true;
        }

        //获取用户信息
        $userInfo = $this->userModel->getUserCacheSynch($this->uid);
        LzLog::dEcho2(__CLASS__.__FUNCTION__, __FILE__, __LINE__, ["锦标赛 - uinfo",$this->userServerState['online'],$this->userServerState['match']]) ;
        //用户是否有其它赛事匹配中
        if ($this->userServerState['match'] == 1) {
            $this->matchingGameError($this->uid, 4002);
            return true;
        }
        //用户是否在线
        if ($this->userServerState['online'] != 1) {
            $this->matchingGameError($this->uid, 5000);
            return true;
        }

        //计算用户进行到-第几轮       (32强，一共是5轮，结算的时候，进行清算轮次，如果赢了，会拿到下一个轮次中)

        //获取用户<轮次>信息，格式：round game roomlevel fialed_time a_time
        $user_round = RedisOpt::getUserChampionRoundByUid($this->uid);
        LzLog::dEcho2(__CLASS__.__FUNCTION__, __FILE__, __LINE__, ["getUserChampionRoundByUid",$user_round]) ;

        $groupId = rand(100,999).time().rand(1000,9999);
        if(!$user_round){
            //为空，证明是用户首次玩
            $round = 1;

            //设置 轮次  、每轮-失效时间  现在是5分钟
            $d = Func::setChampionUserRoundContent($round,$gameId,$roomLevelId,1,$groupId);
            RedisOpt::setUserChampionRoundNoByUid($this->uid,$d);
        }else{
            $user_round_fail_time = $user_round['failed_time'];

            //这里做个 容错处理，如果用户  锦标赛 <总标记-轮次-游戏-房间>，对不上
            //可能就是用户赢了，但是没有继续游戏，又没有点击退出，服务器又没有收到游戏端请求，也没有收到APP请求
            //还有可能是用户玩的A游戏，切到B游戏了
            if($user_round['game_id'] == $gameId && $user_round['room_level_id'] == $roomLevelId){
                if(time() > $user_round_fail_time){//失效
                    LzLog::dEcho2(__CLASS__.__FUNCTION__, __FILE__, __LINE__, ["time out,reset user_round "]) ;
                    $round = 1;
                    $d = Func::setChampionUserRoundContent($round,$gameId,$roomLevelId,1,$groupId);
                    RedisOpt::setUserChampionRoundNoByUid($this->uid,$d);

                }else{
                    $round = $user_round['round'];
                }
            }else{
                LzLog::dEcho2(__CLASS__.__FUNCTION__, __FILE__, __LINE__, ["tolerant round "]) ;
                $round = 1;
                $d = Func::setChampionUserRoundContent($round,$gameId,$roomLevelId,1,$groupId);
                RedisOpt::setUserChampionRoundNoByUid($this->uid,$d);
            }
        }

        //判断轮次ID 是否正确   目前只有：1 2 3 4 5
        $round_number = $this->config['main']['championGameRoundNumber'];
        if(!in_array($round,$round_number)){
            $this->matchingGameError($this->uid, 2002);
            return true;
        }

        //开始计算：AI机器人的级别，要跟奖池挂勾，略复杂
        //第一轮，根据奖池信息，计算出 用户在第X轮匹配必胜-机器人等级，防止 用户恶意刷金币

        //游戏AI信息
        $fastMatchGameList = $this->config['main']['fastMatchGameList'];


        //默认AI 随机 1-3 级
        if($gameId == 2014){
            $robotLevel = 6;//默认都是3
        }else{
            $robotLevel = 3;//默认都是3
        }
        //$robotLevel = 1;

        //奖金池=报名费-奖励费用
        $ChampionRewardGoldPool = $this->dbModel->getChampionRewardGoldPool();
        LzLog::dEcho2(__CLASS__.__FUNCTION__, __FILE__, __LINE__, ["ChampionRewardGoldPool：",$ChampionRewardGoldPool]) ;
        if(!$ChampionRewardGoldPool || $ChampionRewardGoldPool['total'] < 0){//为负，证明奖池里面没有钱了，防止 赔钱，停止匹配
            LzLog::dEcho2(__CLASS__.__FUNCTION__, __FILE__, __LINE__,"奖金池为负数：(2001)");
//            $this->dbModel->sendmail('champion_gold_pool_warning',$ChampionRewardGoldPool['total']);
        }

        $GoldPool = $ChampionRewardGoldPool['total'];
        if($round == 1){
            $is_free = $this->hasFreeTimes($this->uid,$roomLevelId,$gameId);
            if(!$is_free){
                //报名费
                $roomGoldLevel = $this->config['main']['championGameGoldLevel'];
                $less_gole =  $roomGoldLevel[$roomLevelId]['signin_gold'];
                LzLog::dEcho2(__CLASS__."-".__FUNCTION__, __FILE__, __LINE__, ['less_gole:'.$less_gole ]) ;
                if($less_gole && $less_gole > 0){
                    $rs = $this->userModel->checkLessUserGold($this->uid,"-".$less_gole);
                    if($rs < 0 ){
                        $this->matchingGameError($this->uid,1008);
                        return true;
                    }
                }
            }



            //必胜AI 出现在第几轮
            $userRoundAIMustWin = $this->getUserMatchRobotLevel($GoldPool,$roomLevelId);

            LzLog::dEcho2(__CLASS__.__FUNCTION__, __FILE__, __LINE__, ["setChampionMatchUserMustWinAILevel: ",$userRoundAIMustWin]) ;
            RedisOpt::setChampionMatchUserMustWinAILevel($this->uid,$userRoundAIMustWin);

        }else{
            //获取 用户 必胜AI 出现的 轮次
            $userRoundAIMustWinRedis = RedisOpt::getChampionMatchUserMustWinAILevel($this->uid);
            LzLog::dEcho2(__CLASS__.__FUNCTION__, __FILE__, __LINE__, ["userRoundAIMustWinRedis: ",$userRoundAIMustWinRedis]) ;
            if(!$userRoundAIMustWinRedis){
                //容错
                LzLog::dEcho2(__CLASS__.__FUNCTION__, __FILE__, __LINE__, ["userRoundAIMustWinRedis is null "]) ;

                $userRoundAIMustWin = $this->getUserMatchRobotLevel($GoldPool,$roomLevelId);
                RedisOpt::setChampionMatchUserMustWinAILevel($this->uid,$userRoundAIMustWin);

                $userRoundAIMustWinRedis = RedisOpt::getChampionMatchUserMustWinAILevel($this->uid);
                LzLog::dEcho2(__CLASS__.__FUNCTION__, __FILE__, __LINE__, ["tolerant get userRoundAIMustWinRedis: ",$userRoundAIMustWinRedis]) ;
            }
            //轮次:a_time
            $userRoundAIMustWin = $userRoundAIMustWinRedis['level'];

            if($round == $userRoundAIMustWin){
                LzLog::dEcho2(__CLASS__.__FUNCTION__, __FILE__, __LINE__, ["entry must WIN AI  process: "]) ;

                //进入必胜AI流程，不需要再匹配真人了
                //也不会进入   报名队列
                $robotLevel = $fastMatchGameList[$gameId]['must_win_level'];
                //$robotLevel = 1;
                LzLog::dEcho2(__CLASS__.__FUNCTION__, __FILE__, __LINE__, ["must WIN AI level: ",$robotLevel]) ;
                //参数
                $this->timer_after_data = array('roomLevelId'=>$roomLevelId,'uid'=>$this->uid,'round'=>$round,'gameId'=>$gameId,'robotLevel'=>$robotLevel);

                //迟延1秒，执行匹配机器人~先返回等待时间给前端，不要阻塞，后端继续计算
                $id = swoole_timer_after(1000, function ($timerId, $params  = null) {
                    $this->matchRobotAD($timerId, $params);
                },$this->timer_after_data);



                $array = array('match'=>1);
                $return = RedisOpt::setUserServerStateByUid($this->uid,$array);

                LzLog::dEcho2("setUserServerStateByUid return", __FILE__, __LINE__, $return) ;



                $response = new ChampionGameMatchResponse();
                //        $response->setCode(0);
                $response->setSec($this->config['main']['matchInterval']);

                $msgId = pack("N", 1052);
                $data = array(
                    'msgId' => $msgId,
                    'message' => $response
                );

                $this->send($data);

                return true;
            }
        }

        LzLog::dEcho2(__CLASS__.__FUNCTION__, __FILE__, __LINE__, ["锦标赛 - robot_level",$robotLevel]) ;


        //判断用户是否为重复报名，且时间没失效


        LzLog::dEcho2(__CLASS__.__FUNCTION__, __FILE__, __LINE__, ["send redis sign uid",$this->uid]) ;

        //将用户信息，扔进后台任务匹配池
        $this->matchModel->userMatchSign($this->uid,4,$round,$gameId,$roomLevelId);

        //先返回等待时间给前端，不要阻塞，后端继续计算
        $response = new ChampionGameMatchResponse();
//        $response->setCode(0);
        $response->setSec($this->config['main']['matchInterval']);

        $msgId = pack("N", 1052);
        $data = array(
            'msgId' => $msgId,
            'message' => $response
        );

        $this->send($data);
    }

    //用户取消匹配
    function cancelMatch(){
        $user_round = RedisOpt::getUserChampionRoundByUid($this->uid);

        LzLog::dEcho2(__CLASS__.__FUNCTION__, __FILE__, __LINE__, ["用户取消,getUserChampionRoundByUid:",$user_round]);

        $rs = RedisOpt::delUserChampionRoundNoByUid( $this->uid );
        LzLog::dEcho2(__CLASS__.__FUNCTION__, __FILE__, __LINE__,"删除用户<轮次>信息：".$rs);
        $rs = RedisOpt::delChampionMatchUserMustWinAILevel( $this->uid );
        LzLog::dEcho2(__CLASS__.__FUNCTION__, __FILE__, __LINE__,"删除用户<必胜AI>信息：".$rs);



        $signUserList = RedisOpt::getChampionMatchSignAllUser($user_round['round'],$user_round['game_id'],$user_round['room_level_id']);
        LzLog::dEcho2(__CLASS__.__FUNCTION__, __FILE__, __LINE__, ["getChampionMatchSignAllUser",$signUserList]);

        if(!$signUserList){
            return false;
        }

        foreach($signUserList as $k=>$v){
            if($v['uid'] == $this->uid){
                $rs = RedisOpt::delOneChampionMatchSignAllUser($user_round['round'],$user_round['game_id'],$user_round['room_level_id'],$v);
                LzLog::dEcho2(__CLASS__.__FUNCTION__, __FILE__, __LINE__,'del user,rs:'.$rs);
            }

        }

        return true;

    }

    //正式开始匹配，分2部分
    //1 后台任务在计算真实用户匹配结果
    //2 后台任务没有找到符合要求的真实用户配对，那么，匹配一个机器人给用户
    public function matchGameUser() {
        $user_round = RedisOpt::getUserChampionRoundByUid($this->uid);
        LzLog::dEcho2(__CLASS__.__FUNCTION__, __FILE__, __LINE__, ["user_round",$user_round]) ;

        $uid = $this->uid;
        $gameId = $user_round['game_id'];
        $round = $user_round['round'];
        $roomLevelId = $user_round['room_level_id'];

        if ($this->userServerState ['match'] != 1) {
            return $this->matchingGameError($this->uid, 4001);

        }

        if ($this->userServerState ['online'] == 0) {
            return $this->matchingGameError($this->uid, 5000);
        }


        $ChampionRewardGoldPool = $this->dbModel->getChampionRewardGoldPool();
        LzLog::dEcho2(__CLASS__.__FUNCTION__, __FILE__, __LINE__, ["ChampionRewardGoldPool：",$ChampionRewardGoldPool]) ;
        //为负，证明奖池里面没有钱了，防止 赔钱，全都匹配必胜AI
        if(!$ChampionRewardGoldPool || $ChampionRewardGoldPool['total'] < 0){
            LzLog::dEcho2(__CLASS__.__FUNCTION__, __FILE__, __LINE__,"奖金池为负数：(2001)");


//            $this->dbModel->sendmail('champion_gold_pool_warning',$ChampionRewardGoldPool['total']);


            $fastMatchGameList = $this->config['main']['fastMatchGameList'];
            $robotLevel = $fastMatchGameList[$gameId]['must_win_level'];
            //$robotLevel = 1;
            LzLog::dEcho2(__CLASS__.__FUNCTION__, __FILE__, __LINE__, ["must WIN AI level: ",$robotLevel]) ;

            //开始匹配机器人
            $this->timer_after_data = array('roomLevelId'=>$roomLevelId,'uid'=>$this->uid,'round'=>$round,'gameId'=>$gameId,'robotLevel'=>$robotLevel);
            $this->matchRobotAD(null,null);

            return true;

        }





//        1,2003,1,1531370853

        //默认AI 随机 1-3 级
        if($gameId == 2014){
            $robotLevel = 6;//默认都是3
        }else{
            $robotLevel = 3;//默认都是3
        }
        //$robotLevel = 1;

        $gameInfo = $this->dbModel->gameInfo($gameId);
        LzLog::dEcho2(__CLASS__.__FUNCTION__, __FILE__, __LINE__,['游戏信息',$gameInfo['name'],$gameInfo['label']]);

        $info = $this->matchModel->matchRealUser($uid, 4, $gameInfo,$roomLevelId,$round);
        LzLog::dEcho2(__CLASS__.__FUNCTION__, __FILE__, __LINE__, ["获取后台任务已匹配真实用户",$info]) ;

        if ($info) {//已匹配到真人
            LzLog::dEcho2(__CLASS__.__FUNCTION__, __FILE__, __LINE__, ["匹配成功",$info]) ;
            $err = 0;
            foreach($info['list']  as $k=>$v){
                $is_free = $this->hasFreeTimes($v['uId'],$roomLevelId,$gameId);
                if(!$is_free ){
                    $info['list'][$k]['isFree'] = 0;
                    if( $round == 1){//第1轮，扣取报名金币费用
                        $rs  = $this->upUserSingGold( $v['uId'] , $roomLevelId,$info['roomId']);
                        if($rs < 0 ){
                            $err = 1;
                            $this->matchingGameError($this->uid,1008);
                        }
                    }
                }else{
                    $info['list'][$k]['isFree'] = 1;
                    LzLog::dEcho2(__CLASS__.__FUNCTION__, __FILE__, __LINE__,["不需要扣金币"]);
                }
            }

            if( $err ){
                return true;
            }


            $response = $this->matchModel->mapMatchRealUser($this->uid,4,$info);

            return true;
        }

        //开始匹配机器人
        $this->timer_after_data = array('roomLevelId'=>$roomLevelId,'uid'=>$this->uid,'round'=>$round,'gameId'=>$gameId,'robotLevel'=>$robotLevel);
        $this->matchRobotAD(null,null);

        return true;
    }

    function hasFreeTimes($uid,$roomLevelId,$gameId){
        $is_free = 0;
        //前两个房间，每天有3次免费的机会
        if($roomLevelId == 1 || $roomLevelId == 2){
            $pklogFreeInfo =$this->dbModel->getPkLogChampionFreeInfo($uid,$gameId,$roomLevelId);
            LzLog::dEcho2(__CLASS__.__FUNCTION__, __FILE__, __LINE__,["pklogFreeInfo:",$pklogFreeInfo]);
            if(!$pklogFreeInfo){
                $is_free = 1;
                LzLog::dEcho2(__CLASS__.__FUNCTION__, __FILE__, __LINE__,["用户今天还有免费次数3"]);
            }elseif(count($pklogFreeInfo) >=3 ){
//                $this->upUserSingGold($uId,$roomLevelId,$roomId);
            }else{
                $mod = 3 - count($pklogFreeInfo);
                $is_free = 1;
                LzLog::dEcho2(__CLASS__.__FUNCTION__, __FILE__, __LINE__,["用户今天还有免费次数{$mod}"]);
            }
        }else{
//            $this->upUserSingGold($uId,$roomLevelId,$roomId);
        }

        return $is_free;
    }

    private function matchingGameError($uId, $code) {
        $response = new MatchingGameResponse();
        $response->setCode($code);
        $response->setSec(0);
        if ($code > 0) {
            $response->setMsg($this->config['code'][$code]);
        }

        $msgId = pack("N", 1052);
        $data = array(
            'msgId' => $msgId,
            'message' => $response
        );


        LzLog::dEcho2('matching_error', __FILE__, __LINE__, [$code, $uId, $this->config['code'][$code]]);

        get_instance()->sendToUid($uId, $data);

        $this->destroy();
        return true;
    }

    function upUserSingGold($uId,$roomLevelId,$roomId){
        //报名费-只能非机器人
        $roomGoldLevel = $this->config['main']['championGameGoldLevel'];
        $less_gole =  $roomGoldLevel[$roomLevelId]['signin_gold'];
        LzLog::dEcho2(__CLASS__."-".__FUNCTION__, __FILE__, __LINE__, ['less_gole:'.$less_gole ]) ;
        if($less_gole && $less_gole > 0){
            $userModel = $this->loader->model('UserModel', $this);
            $rs = $userModel->upUserGoldCoin($uId,"-".$less_gole,'championmatch_singin',$roomId);
            return $rs;
        }
    }

    function matchRobotAD($timer,$params2){
        $params = $this->timer_after_data;

        $uid = $params['uid'];
        $roomLevelId = $params['roomLevelId'];
        $gameId = $params['gameId'];
        $round = $params['round'];
        $robotLevel = $params['robotLevel'];




        LzLog::dEcho2(__CLASS__.__FUNCTION__, __FILE__, __LINE__, [$uid,$roomLevelId,$gameId,$round,$robotLevel]) ;


        $is_free = 0;
        if($round == 1){
            $is_free = $this->hasFreeTimes($uid,$roomLevelId,$gameId);
        }

        LzLog::dEcho2(__CLASS__.__FUNCTION__, __FILE__, __LINE__, ["没有真实用户进入匹配，进入 匹配机器人"]) ;


        $response = $this->matchModel->matchRobot($uid, $gameId,4,$roomLevelId,$robotLevel,$round,$is_free);


        $msgId = pack("N", 1106);
        $data = array(
            'msgId' => $msgId,
            'message' => $response['response']
        );


        if(!$is_free ){
            if( $round == 1){//第1轮，扣取报名金币费用
                $rs = $this->upUserSingGold($uid,$roomLevelId,$response['roomInfo']['roomId']);
                if($rs < 0 ){
                    $this->matchingGameError($this->uid,1008);
                    return true;
                }
            }
        }else{
            LzLog::dEcho2(__CLASS__.__FUNCTION__, __FILE__, __LINE__,["不需要扣金币"]);
        }


        get_instance()->sendToUid($uid, $data);


        $this->timer_after_data = null;

    }
    //必胜AI 出现在第几轮
    function getUserMatchRobotLevel($GoldPool,$roomLevelId){
        //return 0;
        $userRoundAIMustWin = 0;
        if($roomLevelId == 1){//
            if($GoldPool < 80){
                $userRoundAIMustWin = 4;
            }elseif($GoldPool < 310){
                $userRoundAIMustWin = 5;
            }
        }elseif($roomLevelId == 2){//
            if($GoldPool < 200){
                $userRoundAIMustWin = 4;
            }elseif($GoldPool < 760){
                $userRoundAIMustWin = 5;
            }
        }elseif($roomLevelId == 3){//

            if($GoldPool < 400){
                $userRoundAIMustWin = 4;
            }elseif($GoldPool < 1520){
                $userRoundAIMustWin = 5;
            }
        }elseif($roomLevelId == 4){//

            if($GoldPool < 1000){
                $userRoundAIMustWin = 4;
            }elseif($GoldPool < 3240){
                $userRoundAIMustWin = 5;
            }
        }elseif($roomLevelId == 5){//
            if($GoldPool < 3000){
                $userRoundAIMustWin = 4;
            }elseif($GoldPool < 8480){
                $userRoundAIMustWin = 5;
            }
        }

        return $userRoundAIMustWin;
    }

    function getRobotUid(){
        $rand = Func::getRobotUidRange();

        $r = rand($rand[0],$rand[1]);
        return $r;
    }

}
