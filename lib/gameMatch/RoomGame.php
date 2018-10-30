<?php

namespace app\Controllers;

use app\Protobuf\MatchingGameRequest;
use app\Protobuf\MatchingGameResponse;
use Server\CoreBase\Controller;
use app\Protobuf\User;
use app\Protobuf\MatchStartGameResponse;
use app\Tools\LzLog;


class RoomGame extends Controller {

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

    /**
     * 分配机器人
     */
    public function matchRobot($uId, $gameId) {
        
        $pkUid = rand(10000700, $this->config['main']['robotId']);
        LzLog::dEcho2('RoomGame.matchRobot', __FILE__, __LINE__, [$uId, $pkUid, $gameId]) ;
        $gameInfo = $this->dbModel->gameInfo($gameId);
        $userInfo = $this->userModel->getUserCacheSynch($uId);
        $pkUserInfo = $this->userModel->getUserCacheSynch($pkUid);

        if ($userInfo['online'] != 1) {
            LzLog::dEcho2('RoomGame.matchRobot', __FILE__, __LINE__, [$uId, $pkUid, $userInfo['online']]) ;
            return true;
        }

//        if(!$userInfo['uid']){
//           $userInfo = $this->userModel->userInfoErrRestInfo($uId,$userInfo);
//        }
//
//        if(!$pkUserInfo['uid']){
//            $pkUserInfo = $this->userModel->userInfoErrRestInfo($pkUid,$pkUserInfo);
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
            'gameName' => $gameInfo['name'],
            'gameLabel' => $gameInfo['label'],
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

        $this->userModel->setUserCacheSynch($uId, ['online' => 2, 'roomId' => $roomId, 'match' => 0]);
        $this->roomModel->setRoomInfoSynch($roomInfo);

        $response = new MatchStartGameResponse();
        $response->setGameId($roomInfo['gameId']);
        $response->setRoomId($roomInfo['roomId']);
        $response->setSec($this->config['main']['matchSuccess']);
        $response->setPosition(6);
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
        
        //LzLog::dEcho(["游戏内匹配游戏分配机器人消息入库", $message]) ;
        LzLog::dEcho2('RoomGame.matchRobot', __FILE__, __LINE__, ["游戏内匹配游戏分配机器人消息入库", $message]) ;
        $this->messageModel->rsyncAddMatchGameMessage($message, 3);
        /** **游戏入库message消息end*** */

        $msgId = pack("N", 1026);
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
    public function matchGameUser($timerId, $params) {
        LzLog::dEcho2('RoomGame.matchingGame', __FILE__, __LINE__, "start tick 3") ;
        $timestamp = $params['timestamp'];
        $uId = $params['uId'];
        $gameId = $params['gameId'];
        $time = time();
        $sec = $this->config['main']['matchInterval'] - ($time - $timestamp);
        LzLog::dEcho2('RoomGame.matchingGame', __FILE__, __LINE__, "start tick 4") ;
        $userInfo = $this->userModel->getUserCacheSynch($uId);
        
        LzLog::dEcho2('RoomGame.matchGameUser', __FILE__, __LINE__, ['单人匹配',$timerId, $sec, $params]) ;
        if ($userInfo['online'] != 1 || $userInfo['match'] != 1) {
            swoole_timer_clear($timerId);
            LzLog::dEcho2('RoomGame.matchGameUser', __FILE__, __LINE__, ['单人匹配离开',$userInfo['online'],$userInfo['match']]) ;
            return true;
        }

        $info = $this->userModel->getMatchGameInfo($uId);
        if ($info) {

            swoole_timer_clear($timerId);
            $this->userModel->setUserCacheSynch($uId, [
                'match' => 0,
                'online' => 2,
                'clearId' => 0,
                'roomId' => $info['roomId'],
            ]);

            LzLog::dEcho2('RoomGame.matchGameUser', __FILE__, __LINE__, ['匹配成功', $userInfo['uid'], $timerId, $userInfo['clearId']]) ;

            $this->userModel->delMatchGameInfo($uId);

            $info['start'] = 1;
            $this->roomModel->setRoomInfoSynch($info);

            $response = new MatchStartGameResponse();
            $response->setGameId($info['gameId']);
            $response->setRoomId($info['roomId']);
            $response->setSec($this->config['main']['matchSuccess']);
            $response->setPosition(5);
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
            
            $msgId = pack("N", 1026);
            $data = array(
                'msgId' => $msgId,
                'message' => $response
            );

            get_instance()->sendToUid($uId, $data);


            /**游戏入库message消息start*** */
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
            LzLog::dEcho2('RoomGame.matchGameUser', __FILE__, __LINE__, ['游戏内匹配游戏消息入库', $message]) ;

            $this->messageModel->rsyncAddMatchGameMessage($message, 3);
            /****游戏入库message消息end*** */
            
            return true;
        }
        
        // 开始清除房间id
        if ($sec < 9) {
            swoole_timer_clear($timerId);
            LzLog::dEcho2('RoomGame.matchGameUser', __FILE__, __LINE__, ["匹配机器人", $userInfo, $timerId]) ;
            $this->matchRobot($uId, $gameId);
        }
    }

    /**
     * @return bool|\Generator
     * 单人匹配请求
     */
    public function matchingGame() {
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

        $userInfo = yield $this->userModel->getUserCache($this->uid);
        //LzLog::dEcho(["matchingGame 单人匹配请求", $userInfo, $gameId]) ;
        LzLog::dEcho2('RoomGame.matchingGame', __FILE__, __LINE__, ["单人匹配请求", $userInfo, $gameId]) ;
        if ($userInfo['match'] == 1) {
            $this->matchingGameError($this->uid, 1108);
            $this->destroy();
            return true;
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
            'time' => $timestamp,
            'gameId' => $gameId
        );

        yield $this->matchModel->addGame($info);

        $id = swoole_timer_tick(1000, function ($timerId, $params = null) {
            $this->matchGameUser($timerId, $params);
        }, ['timestamp' => $timestamp, 'uId' => $this->uid, 'gameId' => $gameId]);

        yield $this->userModel->setUserCache($this->uid, [
                    'match' => 1,
                    'clearId' => $id,
                    'roomId' => '',
                    'chat' => []
        ]);

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

    private function matchingGameError($uId, $code) {
        LzLog::dEcho2('RoomGame.matchingGameError', __FILE__, __LINE__, [$uId, $code]) ;
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
