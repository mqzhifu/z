<?php

namespace app\Controllers;

use app\Models\MessageModel;
use app\Protobuf\LckResponse;
use app\Protobuf\PkResumeRequest;
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

class RoomSingle extends Controller {

    private $userModel;
    private $roomModel;
    private $matchModel;
    private $dbModel;
    private $messageModel;

    protected function initialization($controller_name, $method_name) {
        parent::initialization($controller_name, $method_name);
        $this->userModel = $this->loader->model('UserModel', $this);
        $this->roomModel = $this->loader->model('RoomModel', $this);
        $this->matchModel = $this->loader->model('MatchModel', $this);
        $this->dbModel = $this->loader->model('DbModel', $this);
        $this->messageModel = $this->loader->model('MessageModel', $this);
    }

    public function http_getOnline() {
        $num = yield get_instance()->coroutineCountOnline();
        $this->http_output->end($num);
    }

    // 取消匹配请求
    public function singleLeave() {
        if (empty($this->uid))
            return true;
        //LzLog::dEcho(["singleLeave 匹配离开", $this->uid]) ;
        LzLog::dEcho2('RoomSingle.singleLeave', __FILE__, __LINE__, [$this->uid]) ;

        yield $this->userModel->setUserCache($this->uid, [
                    'match' => 0,
                    'roomId' => 0,
                    'chat' => [],
                    'online' => 1
        ]);
        //yield $this->matchModel->addLeaveSingle($this->uid);
        $this->destroy();
    }

    /**
     * 约战请求
     */
    public function pk() {
        if (empty($this->uid))
            return true;

        $request = new PkRequest($this->client_data->data);
        $uId = $request->getUId();

        $gameId = $request->getGameId();
        LzLog::dEcho2('RoomSingle.pk', __FILE__, __LINE__, ["PK 约战请求", $this->uid, $uId, $gameId]) ;

        if (empty($gameId)) {
            $response = new PkResponse();
            $response->setCode(1);
            $response->setUId($uId);
            $response->setGameId($gameId);
            $response->setMsg($this->config['code'][1110]);
            $msgId = pack('N', 1030);
            $data = array(
                'msgId' => $msgId,
                'message' => $response
            );
            $this->sendToUid($this->uid, $data);
            $this->destroy();
            return true;
        }

        $userInfo = yield $this->userModel->getUserCache($uId);

        // 机器人直接进入游戏
        if ($uId <= $this->config['main']['robotId']) {
            yield sleepCoroutine($this->config['main']['robotPkelay']);
            yield $this->robotAgreePk($userInfo, $gameId, $this->uid);
            return true;
        }
        LzLog::dEcho2('RoomSingle.pk', __FILE__, __LINE__, ["约战请求", "接收方 {$uId} 状态 {$userInfo['online']}"]) ;
        if (empty($userInfo) || $userInfo['online'] != 1 || $userInfo['match'] != 0) {
            // 通知发起人
            $response = new PkResponse();
            $response->setUId($uId);
            $response->setGameId($gameId);
            if ($userInfo['online'] == 0) {
                $response->setCode(1112);
                $response->setMsg($this->config['code'][1112]);
            } elseif ($userInfo['online'] == 2 || $userInfo['match'] == 1) {
                $response->setCode(1113);
                $response->setMsg($this->config['code'][1113]);
            } elseif ($userInfo['online'] == 3) {
                $response->setCode(1114);
                $response->setMsg($this->config['code'][1114]);
            }
            $msgId = pack('N', 1030);
            $data = array(
                'msgId' => $msgId,
                'message' => $response
            );
            $this->sendToUid($this->uid, $data);
            $this->destroy();
            return true;
        }

        LzLog::dEcho2('RoomSingle.pk', __FILE__, __LINE__, ["约战请求", "通知接受人 {$uId} 发起人 {$this->uid}"]) ;
        $roomId = $this->roomModel->getRoomId();
        $response = new PkResponse();
        $response->setCode(0);
        $response->setUId($this->uid);
        $response->setGameId($gameId);
        $response->setRoomId($roomId);
        $response->setType(2);
        $response->setMsg("ok");
        $msgId = pack('N', 1030);
        $data = array(
            'msgId' => $msgId,
            'message' => $response
        );
        $this->sendToUid($uId, $data, FALSE);


        // 通知发起人
        $response = new PkResponse();
        $response->setCode(0);
        $response->setUId($this->uid);
        $response->setGameId($gameId);
        $response->setMsg("约战邀请已发出");
        $msgId = pack('N', 1030);
        $data = array(
            'msgId' => $msgId,
            'message' => $response
        );
        $this->sendToUid($this->uid, $data);
    }

    // 机器人自动同意约战请求
    private function robotAgreePk($userInfo, $gameId, $uId) {
        $myInfo = yield $this->userModel->getUserCache($uId);
        if ($myInfo['online'] != 1) {
            return true;
        }
        LzLog::dEcho2('RoomSingle.robotAgreePk', __FILE__, __LINE__, [$userInfo, $gameId, $uId]) ;
        $gameInfo = yield $this->dbModel->getGameCache($gameId);
        $gameName = $gameInfo['name'];
        $gameLabel = $gameInfo['label'];

        $area = $userInfo['schoolName'] ?: $userInfo['areaName'];
        $myArea = $myInfo['schoolName'] ?: $myInfo['areaName'];

        $roomId = md5(uniqid(md5(microtime(true)), true));
        $roomInfo = array(
            'roomId' => $roomId,
            'type' => 3,
            'num' => 1,
            'start' => 1,
            'end' => 0,
            'match' => 0,
            'gameId' => $gameId,
            'gameName' => $gameName,
            'gameLabel' => $gameLabel,
            'create' => array(
                'uId' => $userInfo['uid'],
                'eUid' => $this->uid
            ),
            'list' => array(
                $userInfo['uid'] => array(
                    'uId' => $userInfo['uid'],
                    'name' => $userInfo['name'],
                    'avatar' => $userInfo['avatar'],
                    'sex' => $userInfo['sex'],
                    'age' => $userInfo['age'],
                    'constellation' => $userInfo['constellation'],
                    'birthday' => $userInfo['birthday'],
                    'area' => $area,
                    'loc' => 1,
                    'robot' => 1
                ),
                $this->uid => array(
                    'uId' => $this->uid,
                    'name' => $myInfo['name'],
                    'avatar' => $myInfo['avatar'],
                    'sex' => $myInfo['sex'],
                    'age' => $myInfo['age'],
                    'constellation' => $myInfo['constellation'],
                    'birthday' => $myInfo['birthday'],
                    'area' => $myArea,
                    'loc' => 11,
                    'robot' => 0
                )
            )
        );

        yield $this->userModel->setUserCache($this->uid, ['online' => 2, 'roomId' => $roomId]);
        yield $this->roomModel->setRoomInfo($roomInfo);

        $response = new MatchStartGameResponse();
        $response->setGameId($roomInfo['gameId']);
        $response->setRoomId($roomInfo['roomId']);
        $response->setSec($this->config['main']['matchSuccess']);
        $response->setPosition(3);
        $response->setType($roomInfo['type']);
        $response->setNum($roomInfo['num']);
        $uIds = [];
        foreach ($roomInfo['list'] as $uId => $val) {
            $uIds[] = $uId;
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

        $msgId = pack("N", 1026);
        $data = array(
            'msgId' => $msgId,
            'message' => $response
        );

        $this->send($data);
    }

    /**
     * 同意约战请求
     */
    public function agreePk() {
        if (empty($this->uid))
            return true;


        $pkUid = $this->uid;
        $request = new AgreePkRequest($this->client_data->data);
        $uId = $request->getUId();
        $gameId = $request->getGameId();
        $getRoomId = $request->getRoomId();
        LzLog::dEcho2('RoomSingle.agreePk', __FILE__, __LINE__, ["同意约战请求", $this->uid, $uId, $gameId, $getRoomId]) ;
        if (empty($gameId)) {
            $response = new AgreePkResponse();
            $response->setCode(1);
            $response->setMsg($this->config['code'][1110]);
            $msgId = pack("N", 1036);
            $data = array(
                'msgId' => $msgId,
                'message' => $response
            );
            $this->sendToUid($this->uid, $data);
            $this->destroy();
            return true;
        }

        $gameInfo = yield $this->dbModel->getGameCache($gameId);
        $gameName = $gameInfo['name'];
        $gameLabel = $gameInfo['label'];

        $userInfo = yield $this->userModel->getUserCache($uId);
        $pkUserInfo = yield $this->userModel->getUserCache($pkUid);
        if ($userInfo['online'] != 1 && $pkUserInfo['online'] != 1) {
            $this->destroy();
            return true;
        }


//        if(!$userInfo['uid']){
//            $userInfo = $this->dbModel->userInfoErrRestInfo($this->uid,$userInfo);
//        }
//
//        if(!$pkUserInfo['uid']){
//            $pkUserInfo = $this->dbModel->userInfoErrRestInfo($pkUid,$pkUserInfo);
//        }




        $area = $userInfo['schoolName'] ?: $userInfo['areaName'];
        $pkArea = $pkUserInfo['schoolName'] ?: $pkUserInfo['areaName'];

        $roomId = md5(uniqid(md5(microtime(true)), true));
        $newRoomId = $getRoomId ? $getRoomId : $roomId;
        $roomInfo = array(
            'roomId' => $newRoomId,
            'type' => 3,
            'num' => 1,
            'start' => 1,
            'end' => 0,
            'match' => 0,
            'gameId' => $gameId,
            'gameName' => $gameName,
            'gameLabel' => $gameLabel,
            'create' => array(
                'uId' => $uId,
                'eUid' => $pkUid
            ),
            'list' => array(
                $uId => array(
                    'uId' => $uId,
                    'name' => $userInfo['name'],
                    'avatar' => $userInfo['avatar'],
                    'sex' => $userInfo['sex'],
                    'age' => $userInfo['age'],
                    'constellation' => $userInfo['constellation'],
                    'birthday' => $userInfo['birthday'],
                    'area' => $area,
                    'loc' => 1,
                    'robot' => 0
                ),
                $pkUid => array(
                    'uId' => $pkUid,
                    'name' => $pkUserInfo['name'],
                    'avatar' => $pkUserInfo['avatar'],
                    'sex' => $pkUserInfo['sex'],
                    'age' => $pkUserInfo['age'],
                    'constellation' => $pkUserInfo['constellation'],
                    'birthday' => $pkUserInfo['birthday'],
                    'area' => $pkArea,
                    'loc' => 11,
                    'robot' => 0
                )
            )
        );

        // lixin@0409 svn 冲突合并
        yield $this->userModel->setUserCache($uId, ['online' => 2, 'roomId' => $newRoomId]);
        yield $this->userModel->setUserCache($pkUid, ['online' => 2, 'roomId' => $newRoomId]);
        //yield $this->userModel->setUserCache($uId, ['online' => 2, 'roomId' => $roomId]);
        //yield $this->userModel->setUserCache($pkUid, ['online' => 2, 'roomId' => $roomId]);
        yield $this->roomModel->setRoomInfo($roomInfo);

        $response = new MatchStartGameResponse();
        $response->setGameId($roomInfo['gameId']);
        $response->setRoomId($roomInfo['roomId']);
        $response->setSec($this->config['main']['matchSuccess']);
        $response->setPosition(3);
        $response->setType($roomInfo['type']);
        $response->setNum($roomInfo['num']);
        $uIds = [];
        foreach ($roomInfo['list'] as $uId => $val) {
            $uIds[] = $uId;
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

        $msgId = pack("N", 1026);
        $data = array(
            'msgId' => $msgId,
            'message' => $response
        );

        LzLog::dEcho2('RoomSingle.agreePk', __FILE__, __LINE__,"同意约战请求同意约战请求发送1026") ;
        $this->sendToUids($uIds, $data);
    }

    /**
     * 拒绝约战请求
     */
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

        $this->sendToUid($uId, $data, false);

        // 设置游戏消息状态
        yield $this->messageModel->updatePkGameCharMessage($pkUid, $uId, $roomId);

        $this->destroy();
    }

    /**
     * 发起再来一局
     */
    public function startAgainGame() {
        if (empty($this->uid))
            return true;

        $request = new StartAgainGameRequest($this->client_data->data);
        $roomId = $request->getRoomId();
        $roomInfo = yield $this->roomModel->getRoomInfo($roomId);

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

            yield $this->userModel->setUserCache($this->uid, ['roomId' => '']);
            yield sleepCoroutine($this->config['main']['robotPkelay']);

            yield $this->robotAgreeStartAgainGame($roomInfo, $this->uid);

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
        $userInfo = yield $this->userModel->getUserCache($uId);
        if ($userInfo['online'] != 2 || $userInfo['roomId']) {
            return true;
        }
        $newRoomId = md5(uniqid(md5(microtime(true)), true));
        $roomInfo['roomId'] = $newRoomId;
        $roomInfo['start'] = 1;
        $roomInfo['end'] = 0;
        yield $this->roomModel->setRoomInfo($roomInfo);

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

        yield $this->userModel->setUserCache($this->uid, ['roomId' => $newRoomId]);

        /*         * **游戏入库message消息start*** */
        $msg = ['gameId' => $roomInfo['gameId'], 'sec' => 3, 'id' => $roomInfo['roomId']];
        $msgNo = MessageModel::createMsgNo() ; // 消息唯一系列号(用户维度,非全局维度)
        $msg['msgNo'] = $msgNo ;
        $message = [
            'from' => array(
                'uId' => $fromUserInfo['uId'],
                'name' => $fromUserInfo['name'],
                'avatar' => $fromUserInfo['avatar']
            ),
            'to' => array(
                'uId' => $toUserInfo['uId'],
                'name' => $toUserInfo['name'],
                'avatar' => $toUserInfo['avatar']
            ),
            'type' => 3,
            'chatType' => 3,
            'msg' => $msg,
            'msgNo' => $msgNo
        ];

        yield $this->messageModel->addChatMessage($message, 3);
        /**游戏入库message消息end****/

        $msgId = pack("N", 1026);
        $data = array(
            'msgId' => $msgId,
            'message' => $response
        );

        $this->sendToUid($this->uid, $data);
    }

    /**
     * 客户端离开后恢复游戏
     */
    public function pkResume(){
        $request = new PkResumeRequest($this->client_data->data) ;
        $uId = $request->getUId() ;
        $roomId = $request->getRoomId() ;
        LzLog::dEcho2("RoomSingle.pkResume", __FILE__, __LINE__, ['客户端离开后恢复游戏', $uId, $roomId]) ;
        if ($uId<-1 || empty($roomId)){
            return false;
        }

        $timerid = swoole_timer_tick(2000, function ($tid, $params){
            $this->_doPkResume($tid,$params);
        }, ['uId'=>$uId, 'roomId'=>$roomId, 'timestamp'=>time()]);

        return true;
    }

    private function _doPkResume($tid, $params){
        $uId = $params['uId'] ;
        $roomId = $params['roomId'] ;
        $timeLoad = 20 - (time()-$params['timestamp']) ; //延迟20秒
        
        LzLog::dEcho2("RoomSingle._doPkResume", __FILE__, __LINE__, ['回调测试', $params]) ;

        $lckDetail = $this->userModel->getLastLck($uId, $roomId);

        if(!$lckDetail){
            LzLog::dEcho2("RoomSingle._doPkResume", __FILE__, __LINE__, ['没有找到最后一局', $uId, $roomId, $lckDetail]) ;
            
        }else{
            LzLog::dEcho2("RoomSingle._doPkResume", __FILE__, __LINE__, ['找到最后一局', $uId, $roomId, $lckDetail]) ;

            $jContent = json_decode($lckDetail, true) ;
            $response = new LckResponse();
            $response->setType($jContent['type']);
            $response->setNum($jContent['num']);
            $response->setContent($lckDetail);

            $msgId = pack("N", 1022);
            $data = array(
                'msgId' => $msgId,
                'message' => $response
            );
            $this->userModel->deleteLastLck($roomId);
            get_instance()->sendToUid($uId, $data, false);
        }

        if ($timeLoad<=0){
            swoole_timer_clear($tid);
        }
        
    }

    /**
     * 同意再来一局
     */
    public function agreeStartAgainGame() {
        if (empty($this->uid))
            return true;

        $request = new AgreeStartAgainGameRequest($this->client_data->data);
        $roomId = $request->getRoomId();
        $roomInfo = yield $this->roomModel->getRoomInfo($roomId);
        LzLog::dEcho2('RoomSingle.agreeStartAgainGame', __FILE__, __LINE__, ["同意再来一局", $this->uid, $roomInfo]) ;
        if (empty($roomInfo)) {
            $this->destroy();
            return true;
        }

        $newRoomId = md5(uniqid(md5(microtime(true)), true));
        $roomInfo['roomId'] = $newRoomId;
        $roomInfo['start'] = 1;
        $roomInfo['end'] = 0;
        yield $this->roomModel->setRoomInfo($roomInfo);

        $response = new MatchStartGameResponse();
        $response->setGameId($roomInfo['gameId']);
        $response->setRoomId($roomInfo['roomId']);
        $response->setSec($this->config['main']['matchSuccess']);
        $response->setPosition(4);
        $response->setType($roomInfo['type']);
        $response->setNum($roomInfo['num']);
        $uIds = [];
        $pkUid = 0;
        foreach ($roomInfo['list'] as $uId => $val) {
            if ($val['uId'] != $this->uid) {
                $fromUserInfo = $val;
            } else {
                $toUserInfo = $val;
            }
            if ($this->uid != $uId) {
                $pkUid = $uId;
            }
            $uIds[] = $uId;
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
        $pkUserInfo = yield $this->userModel->getUserCache($pkUid);

        yield $this->userModel->setUserCache($this->uid, ['roomId' => $newRoomId]);
        yield $this->userModel->setUserCache($pkUid, ['roomId' => $newRoomId]);


        /*         * **游戏入库message消息start*** */
        $msg = ['gameId' => $roomInfo['gameId'], 'sec' => 3, 'id' => $roomInfo['roomId']];
        $msgNo = MessageModel::createMsgNo() ; // 消息唯一系列号(用户维度,非全局维度)
        $msg['msgNo'] = $msgNo ;
        $message = [
            'from' => array(
                'uId' => $fromUserInfo['uId'],
                'name' => $fromUserInfo['name'],
                'avatar' => $fromUserInfo['avatar']
            ),
            'to' => array(
                'uId' => $toUserInfo['uId'],
                'name' => $toUserInfo['name'],
                'avatar' => $toUserInfo['avatar']
            ),
            'type' => 3,
            'chatType' => 3,
            'msg' => $msg,
            'msgNo' => $msgNo
        ];

        yield $this->messageModel->addChatMessage($message, 3);
        /*         * **游戏入库message消息end*** */

        $msgId = pack("N", 1026);
        $data = array(
            'msgId' => $msgId,
            'message' => $response
        );

        $this->sendToUids($uIds, $data);
    }

    /**
     * 分配机器人
     */
    public function matchRobot($uId) {
        $pkUid = rand(10000700, $this->config['main']['robotId']);
        $gameModel = $this->loader->model('DbModel', $this);
        $gameInfo = $gameModel->getGameInfo();
        $userInfo = $this->userModel->getUserCacheSynch($uId);
        $pkUserInfo = $this->userModel->getUserCacheSynch($pkUid);
        LzLog::dEcho2('RoomSingle.matchRobot', __FILE__, __LINE__, ["分配机器人", $userInfo, $pkUserInfo, $gameInfo]) ;
        if ($userInfo['online'] != 1) {
            LzLog::dEcho2('RoomSingle.matchRobot', __FILE__, __LINE__, ["分配机器人,is online not 1", $userInfo['online']]) ;
            return true;
        }

//        if(!$userInfo['uid']){
//            $userInfo = $this->dbModel->userInfoErrRestInfo($uId,$userInfo);
//        }
//
//        if(!$pkUserInfo['uid']){
//            $pkUserInfo = $this->dbModel->userInfoErrRestInfo($pkUid,$pkUserInfo);
//        }

        $area = $userInfo['schoolName'] ?: $userInfo['areaName'];
        $pkArea = $pkUserInfo['schoolName'] ?: $pkUserInfo['areaName'];

        $roomId = md5(uniqid(md5(microtime(true)), true));
        $roomInfo = array(
            'roomId' => $roomId,
            'type' => 3,
            'num' => 1,
            'start' => 1,
            'end' => 0,
            'match' => 0,
            'gameId' => $gameInfo['gameId'],
            'gameName' => $gameInfo['gameName'],
            'gameLabel' => $gameInfo['gameLabel'],
            'create' => array(
                'uId' => $uId,
                'eUid' => $pkUid
            ),
            'list' => array(
                $uId => array(
                    'uId' => $uId,
                    'name' => $userInfo['name'],
                    'avatar' => $userInfo['avatar'],
                    'sex' => $userInfo['sex'],
                    'age' => $userInfo['age'],
                    'constellation' => $userInfo['constellation'],
                    'birthday' => $userInfo['birthday'],
                    'area' => $area,
                    'loc' => 1,
                    'robot' => 0
                ),
                $pkUid => array(
                    'uId' => $pkUid,
                    'name' => $pkUserInfo['name'],
                    'avatar' => $pkUserInfo['avatar'],
                    'sex' => $pkUserInfo['sex'],
                    'age' => $pkUserInfo['age'],
                    'constellation' => $pkUserInfo['constellation'],
                    'birthday' => $pkUserInfo['birthday'],
                    'area' => $pkArea,
                    'loc' => 11,
                    'robot' => 1
                )
            )
        );

        $this->userModel->setUserCacheSynch($uId, [
            'online' => 2,
            'roomId' => $roomId,
            'match' => 0
        ]);
        $this->roomModel->setRoomInfoSynch($roomInfo);

        $response = new MatchStartGameResponse();
        $response->setGameId($roomInfo['gameId']);
        $response->setRoomId($roomInfo['roomId']);
        $response->setSec($this->config['main']['matchSuccess']);
        $response->setPosition(1);
        $response->setType($roomInfo['type']);
        $response->setNum($roomInfo['num']);
        $uIds = [];
        foreach ($roomInfo['list'] as $key => $val) {
            $uIds[] = $key;
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

        /*         * **游戏入库message消息start*** */
        $msg = ['gameId' => $roomInfo['gameId'], 'sec' => 3, 'id' => $roomInfo['roomId']];
        $message = [
            'from' => array(
                'uId' => $userInfo['uid'],
                'name' => $userInfo['name'],
                'avatar' => $userInfo['avatar']
            ),
            'to' => array(
                'uId' => $pkUserInfo['uid'],
                'name' => $pkUserInfo['name'],
                'avatar' => $pkUserInfo['avatar']
            ),
            'type' => 3,
            'chatType' => 3,
            'msg' => $msg
        ];

        $this->messageModel->rsyncAddMatchGameMessage($message, 3);
        /*         * **游戏入库message消息end*** */

        $msgId = pack("N", 1026);
        $data = array(
            'msgId' => $msgId,
            'message' => $response
        );

        get_instance()->sendToUid($uId, $data);
    }

    private function matchingSingleError($uId, $code) {
        $response = new MatchingSingleResponse();
        $response->setCode($code);
        $response->setSec(0);
        if ($code > 0) {
            $response->setMsg($this->config['code'][$code]);
        }

        $msgId = pack("N", 1024);
        $data = array(
            'msgId' => $msgId,
            'message' => $response
        );

        get_instance()->sendToUid($uId, $data);
    }

    /**
     * @param $timestamp
     * @param $uId
     * @return bool
     * 单人匹配
     */
    public function matchSingleUser($timerId, $params) {
        $timestamp = $params['timestamp'];
        $uId = $params['uId'];
        $time = time();
        $sec = $this->config['main']['matchInterval'] - ($time - $timestamp);
        $userInfo = $this->userModel->getUserCacheSynch($uId);

        LzLog::dEcho2('RoomSingle.matchSingleUser', __FILE__, __LINE__, ["单人匹配", $sec, $userInfo]) ;
        if ($userInfo['online'] != 1 || $userInfo['match'] != 1) {
            swoole_timer_clear($timerId);
            LzLog::dEcho2('RoomSingle.matchSingleUser', __FILE__, __LINE__, ["单人匹配离开", $userInfo['online'] ,$userInfo['match']]) ;
            return true;
        }

        $info = $this->userModel->getMatchInfo($uId);
        if ($info) {

            swoole_timer_clear($timerId);
            $this->userModel->setUserCacheSynch($uId, [
                'match' => 0,
                'online' => 2,
                'clearId' => 0,
                'roomId' => $info['roomId']
            ]);

            LzLog::dEcho2('RoomSingle.matchSingleUser', __FILE__, __LINE__, ["匹配成功", $userInfo['uid'] ,$timerId]) ;

            $this->userModel->delMatchInfo($uId);

            $info['start'] = 1;
            $this->roomModel->setRoomInfoSynch($info);

            $response = new MatchStartGameResponse();
            $response->setGameId($info['gameId']);
            $response->setRoomId($info['roomId']);
            $response->setSec($this->config['main']['matchSuccess']);
            $response->setPosition(1);
            $response->setType($info['type']);
            $response->setNum($info['num']);

            foreach ($info['list'] as $val) {
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

            /*             * **游戏入库message消息start*** */
            $msg = ['gameId' => $info['gameId'], 'sec' => 3, 'id' => $info['roomId']];
            $message = [
                'from' => array(
                    'uId' => $fromUserInfo['uId'],
                    'name' => $fromUserInfo['name'],
                    'avatar' => $fromUserInfo['avatar']
                ),
                'to' => array(
                    'uId' => $toUserInfo['uId'],
                    'name' => $toUserInfo['name'],
                    'avatar' => $toUserInfo['avatar']
                ),
                'type' => 3,
                'chatType' => 3,
                'msg' => $msg
            ];

            $this->messageModel->rsyncAddMatchGameMessage($message, 3);
            /*             * **游戏入库message消息end*** */

            $msgId = pack("N", 1026);
            $data = array(
                'msgId' => $msgId,
                'message' => $response
            );

            get_instance()->sendToUid($uId, $data);
            return true;
        }


        // 开始清除房间id
        if ($sec < 9) {
            swoole_timer_clear($timerId);
            LzLog::dEcho2('RoomSingle.matchSingleUser', __FILE__, __LINE__, ["单人匹配游戏 匹配机器人", $userInfo, $timerId]) ;
            $this->matchRobot($uId);
        }
    }

    /**
     * @return bool|\Generator
     * 单人匹配请求
     */
    public function matchingSingle() {
        $timestamp = time();

        if (empty($this->uid))
            return true;

        $userInfo = yield $this->userModel->getUserCache($this->uid);
        LzLog::dEcho2('RoomSingle.matchSingleUser', __FILE__, __LINE__, ['单人匹配请求', $userInfo]) ;
        if ($userInfo['match'] == 1) {
            LzLog::dEcho2('RoomSingle.matchSingleUser', __FILE__, __LINE__, ['单人匹配请求ERR,匹配中', $userInfo['match']]) ;
            $this->matchingSingleError($this->uid, 1108);
            $this->destroy();
            return true;
        }

        if (isset($userInfo['chat']) && $userInfo['chat']) {
            $chat = new Chat();
            yield $chat->clearChat($userInfo['chat'], $this->uid);
        }


//        if(!$userInfo['uid']){
//            $userInfo = $this->dbModel->userInfoErrRestInfo($this->uid,$userInfo);
//        }


        $roomId = md5(uniqid(md5(microtime(true)), true));

        $area = $userInfo['schoolName'] ?: $userInfo['areaName'];

        $info = array(
            'type' => 3,
            'roomId' => $roomId,
            'uId' => intval($this->uid),
            'name' => $userInfo['name'],
            'avatar' => $userInfo['avatar'],
            'sex' => $userInfo['sex'],
            'age' => $userInfo['age'],
            'birthday' => $userInfo['birthday'],
            'constellation' => $userInfo['constellation'],
            'robot' => 0,
            'area' => $area,
            'time' => $timestamp
        );

        yield $this->matchModel->addSingle($info);

        $id = swoole_timer_tick(1000, function ($timerId, $params = null) {
            $this->matchSingleUser($timerId, $params);
        }, ['timestamp' => $timestamp, 'uId' => $this->uid]);

        yield $this->userModel->setUserCache($this->uid, [
                    'match' => 1,
                    'clearId' => $id,
                    'roomId' => '',
                    'chat' => []
        ]);

        $response = new MatchingSingleResponse();
        $response->setCode(0);
        $response->setSec($this->config['main']['matchInterval']);

        $msgId = pack("N", 1024);
        $data = array(
            'msgId' => $msgId,
            'message' => $response
        );

        $this->send($data);
    }

}
