<?php

namespace app\Controllers;

use app\Protobuf\MatchingGameRequest;
use app\Protobuf\MatchingGameResponse;
use Server\CoreBase\Controller;
use app\Protobuf\User;
use app\Protobuf\MatchStartGameResponse;
use app\Tools\LzLog;
use app\Tools\RedisOpt;
use app\Tools\Func;
use app\Protobuf\ProvideGameMatchRsRequest;
use app\Protobuf\AgreePkRequest;
use app\Protobuf\AgreePkResponse;
use app\Protobuf\RejectPkRequest;
use app\Protobuf\StartAgainGameRequest;
use app\Protobuf\StartAgainGameResponse;
use app\Protobuf\AgreeStartAgainGameRequest;
use app\Protobuf\RejectPkResponse;

class PkGame extends Controller
{

    private $userModel;
    private $roomModel;
    private $matchModel;
    private $dbModel;
//    private $messageModel;

    private $classDesc = 'PK约战';

    protected function initialization($controller_name, $method_name)
    {
        parent::initialization($controller_name, $method_name);
        $this->userModel = $this->loader->model('UserModel', $this);
        $this->roomModel = $this->loader->model('RoomModel', $this);
        $this->matchModel = $this->loader->model('MatchModel', $this);
        $this->dbModel = $this->loader->model('DbModel', $this);
//        $this->messageModel = $this->loader->model('MessageModel', $this);

        $this->userServerState = RedisOpt::getUserServerStateByUid($this->uid);

        LzLog::dEcho2('RoomGame.userServerState', __FILE__, __LINE__,$this->userServerState);
    }

    function err($errCode,$target_uid){
        $response = new AgreePkResponse();
        $response->setCode(1);
        $response->setMsg($this->config['code'][1110]);
        $msgId = pack("N", 1036);
        $data = array(
            'msgId' => $msgId,
            'message' => $response
        );


        LzLog::dEcho2("Chat.userToUserSend", __FILE__, __LINE__, "{$this->uid},{$target_uid} 约战邀请失败,对方游戏中或不在线,code($errCode)") ;
        $this->sendToUid($this->uid, $data, FALSE);


        $this->sendToUid($this->uid, $data);
        $this->destroy();
        return true;

    }

    //用户接收XX 约战请求
    public function agreePk() {
        if (empty($this->uid))
            return true;

//        $pkUid = $this->uid;
        $request = new AgreePkRequest($this->client_data->data);
        $uId = $request->getUId();
        $gameId = $request->getGameId();
        $oldRoomId = $request->getRoomId();
        LzLog::dEcho2('RoomSingle.agreePk', __FILE__, __LINE__, [$this->uid."同意($uId)约战($gameId)请求,开始处理"]) ;
        if (empty($gameId)) {
            $this->err(2001);
            return true;
        }

        $gameInfo = $this->dbModel->getGameCache($gameId);
//        $userInfo = yield $this->userModel->getUserCache($uId);

        //判断-发起方-状态-是否可以匹配
        $otherUserServerState = RedisOpt::getUserServerStateByUid($uId);
        LzLog::dEcho2("约战:", __FILE__, __LINE__,['$otherUserServerState:'=>$otherUserServerState]);

        if(!Func::isOnline($otherUserServerState)){
            $this->err(4001,1);
            return true;
        }

        if($otherUserServerState['match'] != 1){
            $this->err(6000,1);
            return true;
        }

        //获取 被挑战方状态信息
//        $otherUserServerState = RedisOpt::getUserServerStateByUid($uId);
//        LzLog::dEcho2("约战:", __FILE__, __LINE__,['$otherUserServerState:'=>$otherUserServerState]);

        //判断-被挑战方-状态-是否可以匹配
        $UserHasMatchCode = Func::userHasMatch($this->userServerState);
        LzLog::dEcho2("约战:", __FILE__, __LINE__,['$UserHasMatchCode:'=>$UserHasMatchCode]);


        if($UserHasMatchCode != 200){
            $this->err($UserHasMatchCode,$uId);
            return true;
        }

        $signed = RedisOpt::pkedMatchUserInColl($this->uid,$gameId);
        LzLog::dEcho2("pkedMatchUserInColl:", __FILE__, __LINE__,$signed);
        //别人已经向你发起了挑战，请在30秒内，给予结果后，再进行PK
        if(!$signed){//该用户并没有，向你发起过，挑战
            $this->err(7002,$uId);
            return true;
        }

        $sign = RedisOpt::getPkMatchSignUser($uId);
        LzLog::dEcho2("挑战者info:", __FILE__, __LINE__,$sign);
        //清除定时器，主动进行清理，防止定时器触发晚，引起数据错误
        swoole_timer_clear($sign['timerId']);

        //两个用户的 缓存  都得删除

        //先删除 挑战者的
        $this->matchModel->cancelMatch($uId,5);

        //要把接收者，其它的 发起的挑战，清除掉
        RedisOpt::delPkMatchSignUser($this->uid);
        //要把接收者，所有 被挑战  集合  清除
        RedisOpt::delPkedMatchAllUser($this->uid);

        $matchData = array(
            array('uId'=>$uId,'a_time'=>0),//挑战者
            array('uId'=>$this->uid,'a_time'=>0),//被挑战者
        );

        $roomInfo = $this->matchModel->mapRealUserRoomInfo($matchData,5,$gameInfo,null,null);
        $roomInfo['roomId']= $oldRoomId;

        LzLog::dEcho2('RoomSingle.agreePk,mapRealUserRoomInfo,room_info', __FILE__, __LINE__,$roomInfo) ;

        $response = $this->matchModel->mapMatchRealUser($this->uid,5,$roomInfo);

        LzLog::dEcho2('$response', __FILE__, __LINE__,$response) ;

        LzLog::dEcho2('RoomSingle.agreePk', __FILE__, __LINE__,"真人-同意约战6") ;
//        $this->sendToUids($uIds, $data);

        return true;

    }
    //拒绝 约战 请求

    public function rejectPk() {
        if (empty($this->uid))
            return true;

        $pkUid = $this->uid;
        $request = new RejectPkRequest($this->client_data->data);
        $uId = $request->getUId(); // 发起人uid
        $gameId = $request->getGameId();
        $roomId = $request->getRoomId();
        LzLog::dEcho2('RoomSingle.rejectPk', __FILE__, __LINE__,[$uId, $pkUid, $gameId, $roomId]) ;

        $response = new RejectPkResponse();
        $response->setCode($pkUid);
        $response->setGameId($gameId);
        $response->setUId($uId);
        $response->setMsg("对方拒绝约战请求");
        $response->setRoomId($roomId);

        $msgId = pack('N', 1056);
        $data = array(
            'msgId' => $msgId,
            'message' => $response
        );


        $this->matchModel->cancelMatch($uId,5);

        $this->sendToUid($uId, $data, false);

        // 设置游戏消息状态
//        yield $this->messageModel->updatePkGameCharMessage($pkUid, $uId, $roomId);

        $this->destroy();
    }

    //取消PK,用户点击  返回  按钮
    function cancel(){
        $this->matchModel->cancelMatch($this->uid,5);
    }


    /**
     * 发起再来一局
     */
    public function startAgainGame() {
        if (empty($this->uid))
            return true;

        $request = new StartAgainGameRequest($this->client_data->data);
        $roomId = $request->getRoomId();
//        $roomInfo = yield $this->roomModel->getRoomInfo($roomId);
        $roomInfo = RedisOpt::getRoomById($roomId);



        LzLog::dEcho2('RoomSingle.startAgainGame', __FILE__, __LINE__,["发起再来一局", $this->uid, $roomInfo]) ;

        if (empty($roomInfo) || $roomInfo['end'] != 1) {
            $this->destroy();
            return true;
        }

        if (count($roomInfo['list']) < 2) {
            return true;
        }
        foreach ($roomInfo['list'] as $key => $val) {
            if ($this->uid != $key) {
                $uid = $key;
            }
        }

        // 机器人直接进入游戏
        if ($uid <= $this->config['main']['robotId']) {

//            $this->userModel->setUserCache($this->uid, ['roomId' => '']);
//            yield sleepCoroutine($this->config['main']['robotPkelay']);

            $this->robotAgreeStartAgainGame($roomInfo, $this->uid);

            return true;
        }

        $response = new StartAgainGameResponse();
        $val = $this->config['main']['pk']['anotherGame'];
        $response->setCode(0);
        $response->setMsg($val);
        $msgId = pack("N", 1032);
        $data = array(
            'msgId' => $msgId,
            'message' => $response
        );

        $this->sendToUid($uid, $data);
    }


    // 机器人自动同意再来一局
    private function robotAgreeStartAgainGame($roomInfo, $uId) {


        LzLog::dEcho2('RoomSingle.robotAgreeStartAgainGame', __FILE__, __LINE__,["机器人自动同意再来一局", $uId, $roomInfo]) ;
        $userHasMatch = Func::userHasMatch($this->userServerState);

        LzLog::dEcho2('robotAgreeStartAgainGame userHasMatch:', __FILE__, __LINE__,$userHasMatch);
        if ($userHasMatch != 200) {
            return true;
        }
        $newRoomId = md5(uniqid(md5(microtime(true)), true));
        $roomInfo['roomId'] = $newRoomId;
        $roomInfo['start'] = 1;
        $roomInfo['end'] = 0;
//        yield $this->roomModel->setRoomInfo($roomInfo);
        $this->roomModel->setRoomInfo($roomInfo);

        $response = new MatchStartGameResponse();
        $response->setGameId($roomInfo['gameId']);
        $response->setRoomId($roomInfo['roomId']);
        $response->setSec($this->config['main']['matchSuccess']);
        $response->setPosition(4);
        $response->setType($roomInfo['type']);
        $response->setNum($roomInfo['num']);

        foreach ($roomInfo['list'] as $val) {
            if ($uId == $val['uId']) {
                $fromUserInfo = $val;
            } else {
                $toUserInfo = $val;
            }
            $user = new User();
            $user->setUId($val['uId']);
            $user->setUserName($val['name']);
            $user->setAvatar($val['avatar']);
            $user->setLocation($val['loc']);
            $user->setAge($val['age']);
            $user->setSex($val['sex']);
            $user->setArea($val['area']);
            $user->setConstellation($val['constellation']);
            $response->addUserList($user);
        }

        $arr = array('roomId' => $newRoomId);
        RedisOpt::setUserServerStateByUid($this->uid,$arr);

        $msgId = pack("N", 1026);
        $data = array(
            'msgId' => $msgId,
            'message' => $response
        );

        $this->sendToUid($this->uid, $data);
    }




    //真人-同意再来一局
    public function agreeStartAgainGame() {
        if (empty($this->uid))
            return true;

        $request = new AgreeStartAgainGameRequest($this->client_data->data);
        $roomId = $request->getRoomId();
//        $roomInfo = yield $this->roomModel->getRoomInfo($roomId);
        $roomInfo = RedisOpt::getRoomById($roomId);
//        LzLog::dEcho2('RoomSingle.agreeStartAgainGame', __FILE__, __LINE__, ["同意再来一局", $this->uid, $roomInfo]) ;
//        if (empty($roomInfo)) {
//            $this->destroy();
//            return true;
//        }

        $gameInfo = $this->dbModel->getGameCache($roomInfo['gameId']);


        $matchData = array(
            array('uId'=>$roomInfo['create']['eUId'],'a_time'=>0),//挑战者
            array('uId'=>$roomInfo['create']['uId'],'a_time'=>0),//被挑战者
        );

        $roomInfo = $this->matchModel->mapRealUserRoomInfo($matchData,5,$gameInfo,null,null);
        $newRoomId = md5(uniqid(md5(microtime(true)), true));
        $roomInfo['roomId']= $newRoomId;

        LzLog::dEcho2('RoomSingle.agreePk,mapRealUserRoomInfo,room_info', __FILE__, __LINE__,$roomInfo) ;

        $response = $this->matchModel->mapMatchRealUser($this->uid,5,$roomInfo);

        LzLog::dEcho2('$response', __FILE__, __LINE__,$response) ;

        LzLog::dEcho2('RoomSingle.agreePk', __FILE__, __LINE__,"真人-再来一局-同意约战") ;
//        $this->sendToUids($uIds, $data);

        return true;
    }


    public function matchGameUser()
    {
        $request = new ProvideGameMatchRsRequest($this->client_data->data);

        $uId = $this->uid;
        $gameId = $request->getGameId();

        LzLog::dEcho2($this->classDesc, __FILE__, __LINE__, ['start:', 'uid', $uId, 'gameId', $gameId]);

        $gameInfo = $this->dbModel->gameInfo($gameId);

        LzLog::dEcho2($this->classDesc, __FILE__, __LINE__, ['$gameInfo:', $gameInfo]);

        $userInfo = $this->userModel->getUserCacheSynch($uId);

        LzLog::dEcho2($this->classDesc, __FILE__, __LINE__, ['uinfo-online:', $this->userServerState['online'], 'uinfo-match:', $this->userServerState['match']]);
        if ($this->userServerState['online'] != 1 || $this->userServerState['match'] != 1) {
//            swoole_timer_clear($timerId);
            LzLog::dEcho2('RoomGame.matchGameUser', __FILE__, __LINE__, ['单人匹配离开', $this->userServerState['online'], $this->userServerState['match']]);
            return true;
        }

        $info = $this->matchModel->matchRealUser($uId, 2, $gameInfo);
        LzLog::dEcho2('RoomGame.matchGameUser', __FILE__, __LINE__, ['指定游戏匹配-定时器-后台TASK-结果返回', $info]);
        if ($info) {
            LzLog::dEcho2('RoomGame.matchGameUser', __FILE__, __LINE__, ['已匹配到真人', $userInfo['uid'], $userInfo['clearId']]);

            $response = $this->matchModel->mapMatchRealUser($this->uid,2,$info);

            return true;
        }

        $response = $this->matchModel->matchRobot($uId, $gameId,2);


        $msgId = pack("N", 1026);
        $data = array(
            'msgId' => $msgId,
            'message' => $response['response']
        );

        get_instance()->sendToUid($uId, $data);

        return true;

    }

    /**
     * @return bool|\Generator
     * 单人匹配请求
     */
    public function matchingGame()
    {
        $timestamp = time();
        $request = new MatchingGameRequest($this->client_data->data);
        $gameId = $request->getGameId();

        if (empty($this->uid))
            return true;

        if (empty($gameId)) {
            $this->matchingGameError($this->uid, 1110);
            $this->destroy();
            return true;
        }

        $userInfo = $this->userModel->getUserCache($this->uid);
        LzLog::dEcho2('RoomGame.matchingGame', __FILE__, __LINE__, ["单人匹配请求", $userInfo, $gameId]);
        if ($userInfo['match'] == 1) {
            $this->matchingGameError($this->uid, 1108);
            $this->destroy();
            return true;
        }


        //将用户报名UID  扔到 内存池
        $this->matchModel->userMatchSign($this->uid,2 ,null,$gameId);


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


    private function matchingGameError($uId, $code)
    {
        LzLog::dEcho2('RoomGame.matchingGameError', __FILE__, __LINE__, [$uId, $code]);
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

        get_instance()->sendToUid($uId, $data);
    }


}
