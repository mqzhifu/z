<?php
//快速赛
namespace app\Controllers;

use app\Models\MessageModel;
//use app\Protobuf\LckResponse;
use app\Protobuf\PkResumeRequest;
use app\Tools\RedisOpt;
use Server\CoreBase\Controller;
use app\Protobuf\User;
use app\Protobuf\MatchingSingleResponse;
use app\Protobuf\MatchStartGameResponse;
use app\Protobuf\StartAgainGameRequest;
use app\Protobuf\StartAgainGameResponse;
use app\Protobuf\AgreeStartAgainGameRequest;
use app\Protobuf\PkRequest;
use app\Protobuf\PkResponse;
use app\Protobuf\AgreePkRequest;
use app\Protobuf\AgreePkResponse;
use app\Protobuf\AgreeStartAgainGameResponse;
use app\Protobuf\RejectPkRequest;
use app\Protobuf\RejectPkResponse;
use app\Tools\LzLog;
use app\Tools\RedisLock;
use app\Protobuf\FastGameMatchRequest;
use app\Protobuf\FastGameMatchResponse;
use app\Protobuf\FastGameMatchRsRequest;
use app\Protobuf\MatchingGameResponse;

class FastGame extends Controller
{

    private $userModel;
    private $roomModel;
    private $matchModel;
    private $dbModel;
    private $messageModel;


    private $classDesc = '快速赛';

    //快速赛
    //win_reward_gold:赢了奖励金币
    //signin_gold：报名费-金币


    protected function initialization($controller_name, $method_name)
    {

        parent::initialization($controller_name, $method_name);
        $this->userModel = $this->loader->model('UserModel', $this);
        $this->roomModel = $this->loader->model('RoomModel', $this);
        $this->matchModel = $this->loader->model('MatchModel', $this);
        $this->dbModel = $this->loader->model('DbModel', $this);
//        $this->messageModel = $this->loader->model('MessageModel', $this);

        $this->userServerState = RedisOpt::getUserServerStateByUid($this->uid);
    }


//    public function http_getOnline()
//    {
//        $num = yield get_instance()->coroutineCountOnline();
//        $this->http_output->end($num);
//    }


    //单人匹配
    public function matching()
    {
        LzLog::dEcho2('FastGame_matching', __FILE__, __LINE__, ['快速赛请求,start,uid:' . $this->uid]);
        if (empty($this->uid))
            return true;

        $request = new FastGameMatchRequest($this->client_data->data);
        $room_level_id = $request->getRoomLevelId();
        $game_id = $request->getGameId();
        if (!$game_id) {
            $game_id = 2014;
        }

//        $uId = $this->uid;

        $userInfo = $this->userModel->getUserCache($this->uid);
        LzLog::dEcho2('FastGame_matching', __FILE__, __LINE__, ['快速赛请求', $this->uid, $game_id, $room_level_id, $userInfo]);
        if ($this->userServerState ['match'] == 1) {
            return $this->matchingError($this->uid, 1005);

        }

        if ($this->userServerState ['online'] == 0) {
            return $this->matchingError($this->uid, 1004);
        }

        //报名费
        $fastMatchRoomGoldLevel = $this->config['main']['fastMatchRoomGoldLevel'];
        $less_gole = $fastMatchRoomGoldLevel[$room_level_id]['signin_gold'];
        if($less_gole){
            $rs = $this->userModel->checkLessUserGold($this->uid,"-".$less_gole);
            if($rs < 0 ){
                $this->matchingError($this->uid,1008);
                return true;
            }
        }



        $gameModel = $this->loader->model('DbModel', $this);
        $gameInfo = $gameModel->gameInfo($game_id);

        LzLog::dEcho2('FastGame_matching', __FILE__, __LINE__, ['gameinfo', $gameInfo]);


        //初始化，用户-快速赛，状态信息，为了2次请求使用
        RedisOpt::setUserFastMatchStatusByUid($this->uid, $game_id, $room_level_id);

        //将用户报名UID  扔到 内存池
        $this->matchModel->userMatchSign($this->uid, 3, null, $game_id, $room_level_id);

        $response = new MatchingGameResponse();
        $response->setCode(0);
        $response->setSec($this->config['main']['matchInterval']);

        $msgId = pack("N", 1052);
        $data = array(
            'msgId' => $msgId,
            'message' => $response
        );

        $this->send($data);


    }

    //用户取消匹配
    function cancelMatch()
    {
        $user_status = RedisOpt::getUserFastMatchStatusByUid($this->uid);

        LzLog::dEcho2(__CLASS__ . __FUNCTION__, __FILE__, __LINE__, ["用户取消,getUserFastMatchStatusByUid:", $user_status]);

        $signUserList = RedisOpt::getFastMatchSignAllUser($user_status['game_id'], $user_status['room_level_id']);


        LzLog::dEcho2(__CLASS__ . __FUNCTION__, __FILE__, __LINE__, ["getFastMatchSignAllUser", $signUserList]);


        if (!$signUserList) {
            return false;
        }

        foreach ($signUserList as $k => $v) {
            if ($v['uid'] == $this->uid) {
                $rs = RedisOpt::delOneFastMatchSignAllUser($user_status['game_id'], $user_status['room_level_id'], $v);
                LzLog::dEcho2(__CLASS__ . __FUNCTION__, __FILE__, __LINE__, 'del user,rs:' . $rs);
            }

        }

        RedisOpt::delUserFastMatchStatusByUid($this->uid);

        return true;

    }


    public function matchUser()
    {

        $uId = $this->uid;//当前登陆用户
        //获取用户快速赛，状态信息~主要是为二次请求服务
        $UserFastMatchStatus = RedisOpt::getUserFastMatchStatusByUid($this->uid);
        //游戏ID
        $gameId = $UserFastMatchStatus['game_id'];
        //房间等级ID
        $roomLevelId = $UserFastMatchStatus['room_level_id'];
        //游戏信息
        $gameInfo = $this->dbModel->gameInfo($gameId);

        LzLog::dEcho2($this->classDesc, __FILE__, __LINE__, ['start:', 'uid', $uId, 'gameId', $gameId, 'game_name', $gameInfo['name']]);
        //获取用户信息
        $userInfo = $this->userModel->getUserCacheSynch($uId);

        LzLog::dEcho2($this->classDesc, __FILE__, __LINE__, ['uinfo-online:', $this->userServerState ['online'], 'uinfo-match:', $this->userServerState ['match']]);

        if ($this->userServerState ['match'] != 1) {
            return $this->matchingError($this->uid, 1006);

        }

        if ($this->userServerState ['online'] == 0) {
            return $this->matchingError($this->uid, 1004);
        }

        //开始真人匹配
        $info = $this->matchModel->matchRealUser($uId, 3, $gameInfo, $roomLevelId);
        LzLog::dEcho2('快速赛.matchUser', __FILE__, __LINE__, ['真人匹配-结果返回', $info]);
        if ($info) {
            //扣取报名费用
            $err = 0;
            foreach ($info['list'] as $k => $v) {
                $rs = $this->upSignGold($v['uId'], $roomLevelId, $info['roomId']);
                if($rs < 0){
                    $err = 1;
                    $this->matchingError($this->uid,1008);
                }
            }

            if($err){
                return true;
            }

            LzLog::dEcho2('RoomGame.matchGameUser', __FILE__, __LINE__, ['已匹配到真人,开始映射数组', $userInfo['uid'], $userInfo['clearId']]);
            $response = $this->matchModel->mapMatchRealUser($this->uid, 3, $info);

            return true;
        }

        $level = $this->getRobotLevel($gameInfo['gameId'], $roomLevelId);
        $response = $this->matchModel->matchRobot($uId, $gameId, 3, $roomLevelId, $level);


        $rs = $this->upSignGold($uId, $roomLevelId, $response['roomInfo']['roomId']);
        if($rs < 0){
            $this->matchingError($this->uid,1008);
            return true;
        }

        $msgId = pack("N", 1104);
        $data = array(
            'msgId' => $msgId,
            'message' => $response['response'],
        );

        get_instance()->sendToUid($uId, $data);


//        $this->matchRobot($uId, $gameInfo,$roomLevelId);
    }

    function upSignGold($uId, $room_level_id, $roomId)
    {
        //报名费-只能非机器人
        $fastMatchRoomGoldLevel = $this->config['main']['fastMatchRoomGoldLevel'];
        $less_gole = $fastMatchRoomGoldLevel[$room_level_id]['signin_gold'];
        LzLog::dEcho2('FastGame_matching', __FILE__, __LINE__, ['less_gole:' . $less_gole]);
        //免费次数，或者练习场，是不扣金币的
        if ($less_gole && $less_gole > 0) {
            $userModel = $this->loader->model('UserModel', $this);
            $rs = $userModel->upUserGoldCoin($uId, "-" . $less_gole, 'fastmatch_singin', $roomId);
            return $rs;
        }

        return 0;


    }

    private function matchingError($uId, $code)
    {
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


    function getRobotLevel($game_id, $room_level_id)
    {
        $fastMatchGameList = $this->config['main']['fastMatchGameList'];

        if ($game_id == 2014) {
            $level = 6;//默认都是3
        } else {
            $level = 3;//默认都是3
        }

        $r = 0;
        if ($room_level_id == 2) {//初级场
            $r = rand(0, 1);//50%机率
            if ($r == 1) {
                //匹配 高级AI
                $level = $fastMatchGameList[$game_id]['high_level'];
            } else {

            }
        } elseif ($room_level_id == 3) {//高级场
            $r = rand(1, 10);//70%概率
            if ($r < 8) {//匹配最高级别AI
                $level = $fastMatchGameList[$game_id]['must_win_level'];
            }
        } else {//这是初始场的情况下

        }

        LzLog::dEcho2(__FUNCTION__, __FILE__, __LINE__, "r:" . $r . " ,level:" . $level);

        return $level;


    }

}