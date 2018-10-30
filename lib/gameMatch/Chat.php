<?php

namespace app\Controllers;

use app\Models\MessageModel;
use app\Protobuf\ChatUserToUserAgreeReponse;
use app\Protobuf\ChatUserToUserAgreeRequest;
use app\Protobuf\ChatUserToUserJoinRequest;
use app\Protobuf\ChatUserToUserLeaveRequest;
use app\Protobuf\ChatUserToUserLeaveResponse;
use app\Protobuf\ChatUserToUserRefuseReponse;
use app\Protobuf\ChatUserToUserRefuseRequest;
use app\Protobuf\ChatUserToUserRescindReponse;
use app\Protobuf\ChatUserToUserRescindRequest;
use app\Protobuf\FasterChatRequest;
use app\Protobuf\FasterChatResponse;
use Server\CoreBase\Controller;
use app\Protobuf\ChatUserToUserRequest;
use app\Protobuf\ChatUserToUserResponse;
use app\Protobuf\ChatRoomRequest;
use app\Protobuf\ChatRoomResponse;
use app\Protobuf\MatchStartGameResponse;
use app\Protobuf\User;
use app\Protobuf\PkResponse;
use app\Tools\LzLog;

class Chat extends Controller {

    private $userModel;
    private $roomModel;
    private $messageModel;
    private $dbModel;

    const CHAT_TYPE_PK = 3 ;// 约战类型消息
    const CHAT_TYPE_GIFT = 4 ; //赠送礼物类型消息

    protected function initialization($controller_name, $method_name) {
        parent::initialization($controller_name, $method_name);
        $this->dbModel = $this->loader->model('DbModel', $this);
        $this->userModel = $this->loader->model('UserModel', $this);
        $this->roomModel = $this->loader->model('RoomModel', $this);
        $this->messageModel = $this->loader->model('MessageModel', $this);
    }

    /**
     * 点对点聊天进入
     */
    public function userToUserJoin() {
        $request = new ChatUserToUserJoinRequest($this->client_data->data);
        $uId = $request->getUId();
        LzLog::dEcho2("chat.userToUserJoin", __FILE__, __LINE__, "进入聊天室{$this->uid}") ;
        yield $this->userModel->setUserCache($this->uid, ['online' => 3]);
        $this->destroy();
    }

    /**
     * 点对点聊天离开
     */
    public function userToUserLeave() {
        $request = new ChatUserToUserLeaveRequest($this->client_data->data);
        $uId = $request->getUId();
        //echo "离开聊天室{$this->uid}\n";
        LzLog::dEcho2("Chat.userToUserLeave", __FILE__, __LINE__, "离开聊天室{$this->uid}") ;
        $userInfo = yield $this->userModel->getUserCache($this->uid);

        if ($userInfo['online'] == 3) {
            yield $this->userModel->setUserCache($this->uid, ['online' => 1, 'chat' => []]);
            if ($userInfo['chat']) {
                yield $this->clearChat($userInfo['chat'], $this->uid);
            }
        }

        $this->destroy();
    }

    //点对点离开返回
    public function clearChat($chatInfo, $sendUid) {
        LzLog::dEcho2("Chat.clearChat", __FILE__, __LINE__, [$chatInfo, $sendUid]) ;
        $uId = $chatInfo['uId'];
        
        if (empty($uId))
            return true;

        $userModel = $this->loader->model('UserModel', $this);
        $otherInfo = yield $userModel->getUserCache($uId);
        if ($otherInfo['online'] == 3) {
            $response = new ChatUserToUserLeaveResponse();
            $response->setUId($sendUid);
            $msgId = pack('N', 1040);
            $data = array(
                'msgId' => $msgId,
                'message' => $response
            );

            $this->sendToUid($uId, $data, false);
        }

        // 设置游戏状态
//        $messageModel = $this->loader->model('MessageModel', $this);
//        yield $messageModel->updateCharMessage($sendUid, $uId);
    }

    /**
     * 点对点聊天游戏取消
     */
    public function userToUserRescind() {
        LzLog::dEcho2("Chat.userToUserRescind", __FILE__, __LINE__, []) ;
        $request = new ChatUserToUserRescindRequest($this->client_data->data);
        $id = $request->getId();
        $uId = $request->getUId();
        $userInfo = yield $this->userModel->getUserCache($uId);
        if ($userInfo['online'] == 3) {
            $response = new ChatUserToUserRescindReponse();
            $response->setId($id);
            $msgId = pack('N', 1042);
            $data = array(
                'msgId' => $msgId,
                'message' => $response
            );

            $this->sendToUid($uId, $data);
        } else {
            $this->destroy();
        }
    }

    /**
     * 点对点聊天游戏拒绝
     */
    public function userToUserRefuse() {
        LzLog::dEcho2("Chat.userToUserRefuse", __FILE__, __LINE__, []) ;
        $request = new ChatUserToUserRefuseRequest($this->client_data->data);
        $id = $request->getId();
        $uId = $request->getUId();
        $userInfo = yield $this->userModel->getUserCache($uId);
        if ($userInfo['online'] == 3) {
            $response = new ChatUserToUserRefuseReponse();
            $response->setId($id);
            $msgId = pack('N', 1044);
            $data = array(
                'msgId' => $msgId,
                'message' => $response
            );
            $this->sendToUid($uId, $data);
        } else {
            $this->destroy();
        }
    }

    //点对点同意游戏错误返回
    private function agreeError(int $code) {
        LzLog::dEcho2("Chat.agreeError", __FILE__, __LINE__, [$code]) ;
        $response = new ChatUserToUserAgreeReponse();
        $response->setCode($code);
        if ($code > 0) {
            $response->setMsg($this->config['code'][$code]);
        }

        $msgId = pack("N", 1046);
        $data = array(
            'msgId' => $msgId,
            'message' => $response
        );
        $this->send($data);
    }

    //点对点聊天同意游戏
    public function userToUserAgree() {
        LzLog::dEcho2("Chat.userToUserAgree", __FILE__, __LINE__, []) ;
        $request = new ChatUserToUserAgreeRequest($this->client_data->data);
        $uId = $request->getUId();
        $gameId = $request->getGameId();
        $roomId = $request->getId();
        if (empty($roomId) || empty($gameId) || empty($uId)) {
            $this->destroy();
            return true;
        }

        $gameInfo = yield $this->dbModel->getGameCache($gameId);
        $userInfo = $this->userModel->getUserCacheSynch($this->uid);
        $pkUserInfo = $this->userModel->getUserCacheSynch($uId);

        //echo "---------------{$this->uid}-------{$uId}\n";
        LzLog::dEcho2("Chat.userToUserAgree", __FILE__, __LINE__, [$this->uid, $uId, $pkUserInfo]) ;
        //print_r($pkUserInfo);
        if ($pkUserInfo['online'] != 3) {
            $this->agreeError(1107);
            return true;
        }

        $area = $userInfo['schoolName'] ?: $userInfo['areaName'];
        $pkArea = $pkUserInfo['schoolName'] ?: $pkUserInfo['areaName'];

        $roomInfo = array(
            'roomId' => $roomId,
            'chat' => 1,
            'type' => 3,
            'num' => 1,
            'start' => 1,
            'end' => 0,
            'match' => 0,
            'gameId' => $gameInfo['gameId'],
            'gameName' => $gameInfo['name'],
            'gameLabel' => $gameInfo['label'],
            'create' => array(
                'uId' => $this->uid,
                'eUid' => $uId
            ),
            'list' => array(
                $this->uid => array(
                    'uId' => $this->uid,
                    'name' => $userInfo['name'],
                    'avatar' => $userInfo['avatar'],
                    'sex' => $userInfo['sex'],
                    'age' => $userInfo['age'],
                    'birthday' => $userInfo['birthday'],
                    'constellation' => $userInfo['constellation'],
                    'area' => $area,
                    'loc' => 1,
                    'robot' => 0
                ),
                $uId => array(
                    'uId' => $uId,
                    'name' => $pkUserInfo['name'],
                    'avatar' => $pkUserInfo['avatar'],
                    'sex' => $pkUserInfo['sex'],
                    'age' => $pkUserInfo['age'],
                    'birthday' => $pkUserInfo['birthday'],
                    'constellation' => $pkUserInfo['constellation'],
                    'area' => $pkArea,
                    'loc' => 11,
                    'robot' => 0
                )
            )
        );

        yield $this->userModel->setUserCache($this->uid, ['online' => 2, 'roomId' => $roomId, 'match' => 0]);
        yield $this->userModel->setUserCache($uId, ['online' => 2, 'roomId' => $roomId, 'match' => 0]);

        $this->roomModel->setRoomInfoSynch($roomInfo);

        $response = new MatchStartGameResponse();
        $response->setGameId($roomInfo['gameId']);
        $response->setRoomId($roomInfo['roomId']);
        $response->setSec($this->config['main']['matchSuccess']);
        $response->setPosition(2);
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
            $response->addUserList($user);
        }

        $msgId = pack("N", 1026);
        $data = array(
            'msgId' => $msgId,
            'message' => $response
        );
        //echo "同意进入游戏{$this->uid}------{$uId}\n";
        LzLog::dEcho2("Chat.userToUserAgree", __FILE__, __LINE__, "同意进入游戏{$this->uid},{$uId}") ;

        $this->sendToUids($uIds, $data);
    }

    /**
     * 快捷聊天
     */
    public function fasterChat() {
        LzLog::dEcho2("Chat.fasterChat", __FILE__, __LINE__, "") ;
        if (empty($this->uid))
            return true;
        $request = new FasterChatRequest($this->client_data->data);
        $roomId = $request->getRoomId();
        $id = $request->getId();

        $roomInfo = yield $this->roomModel->getRoomInfo($roomId);
        if (empty($roomInfo)) {
            $this->destroy();
            return true;
        }

        $response = new FasterChatResponse();
        $response->setId($id);

        $msgId = pack('N', 1028);
        $data = array(
            'msgId' => $msgId,
            'message' => $response
        );
        unset($roomInfo['list'][$this->uid]);
        $uIds = array_keys($roomInfo['list']);
        $this->sendToUids($uIds, $data);
    }

    /**
     * @return bool
     * 房间聊天
     */
    public function roomSend() {
        LzLog::dEcho2("Chat.roomSend", __FILE__, __LINE__, "") ;
        if (empty($this->uid))
            return true;

        $request = new ChatRoomRequest($this->client_data->data);
        $roomId = $request->getRoomId();
        $msg = $request->getMsg();
        $type = $request->getType();
        $roomInfo = yield $this->roomModel->getRoomInfo($roomId);

        if (empty($roomInfo)) {
            $this->destroy();
            return true;
        }

        if (!isset($roomInfo['list'][$this->uid])) {
            $this->destroy();
            return true;
        }

        $response = new ChatRoomResponse();
        $response->setType($type);
        $response->setMsg($msg);

        $myInfo = yield $this->userModel->getUserCache($this->uid);
        $user = new User();
        $user->setAvatar($myInfo['avatar']);
        $user->setUId($myInfo['uid']);
        $user->setUserName($myInfo['name']);
        $response->setUser($user);

        $msgId = pack('N', 1016);
        $data = array(
            'msgId' => $msgId,
            'message' => $response
        );

        $uIds = array_keys($roomInfo['list']);
        $this->sendToUids($uIds, $data);
    }

    /**
     * @return bool|\Generator
     * 点对点聊天
     */
    public function userToUserSend() {

        if (empty($this->uid))
            return true;
        
        $request = new ChatUserToUserRequest($this->client_data->data);
        $uId = $request->getUId();
        $type = $request->getType();
        $msg = trim($request->getMsg());
        if (empty($uId) || empty($msg)) {
            $this->destroy();
            return true;
        }
        LzLog::dEcho2("Chat.userToUserSend", __FILE__, __LINE__, ["点对点消息",$uId, $type, $msg]) ;

        $myInfo = yield $this->userModel->getUserCache($this->uid);
        $userInfo = yield $this->userModel->getUserCache($uId);

        if ($type == self::CHAT_TYPE_PK) {
            
            if ($userInfo['online']==2){
                // 通知发起人
                $response = new PkResponse();
                $response->setCode(5001);
                $response->setUId($this->uid);
                $response->setGameId(0);
                $response->setRoomId("xxx");
                $response->setType($type);
                $response->setMsg("对方正在游戏中");
                $msgId = pack('N', 1030);
                $data = array(
                    'msgId' => $msgId,
                    'message' => $response
                );

                LzLog::dEcho2("Chat.userToUserSend", __FILE__, __LINE__, "{$this->uid},{$uId} 约战邀请失败,对方游戏中或不在线") ;
                $this->sendToUid($this->uid, $data, FALSE);
                //$this->destroy();
                return true;
            } 
            
            
            $id = $this->roomModel->getRoomId();
            $gameId = intval($msg);
            $sec = $this->config['main']['chatGameInterval'];

            $msg = ['gameId' => $gameId, 'sec' => $sec, 'id' => $id];

            // 设置用户进入点对点游戏
            if (!isset($myInfo['chat']) || empty($myInfo['chat'])) {
                yield $this->userModel->setUserCache($this->uid, ['chat' => ['uId' => $uId]]);
            }
        }
        if ($type == self::CHAT_TYPE_GIFT) {
            $present = json_decode($msg, true) ;
            $giftId = $present['giftId'] ;
            $giftNum = $present['giftNum'] ;
            
            //处理赠礼
            $presentGive = yield $this->dbModel->doPresent($this->uid, $uId, $giftId, $giftNum) ;
            $msg = $presentGive ;
            if (!$presentGive['succ']){
                // 通知发起人
                $response = new ChatUserToUserResponse();
                $response->setCode(intval($presentGive['data']));
                $response->setType($type);
                $response->setMsg(json_encode($msg));
                $user = new User();
                $user->setAvatar($userInfo['avatar']);
                $user->setUId($userInfo['uid']);
                $user->setUserName($userInfo['name']);
                $response->setUser($user);
                $response->setTime(time());

                $msgId = pack('N', 1018);
                $data = array(
                    'msgId' => $msgId,
                    'message' => $response
                );

                LzLog::dEcho2("Chat.userToUserSend", __FILE__, __LINE__, ["赠礼失败", $this->uid, $uId, $msg]) ;
                $this->sendToUid($this->uid, $data, FALSE);
                //$this->destroy();
                return true;
            }
        }

        $msgNo = MessageModel::createMsgNo() ; // 消息唯一系列号(用户维度,非全局维度)
        $msg['msgNo'] = $msgNo ;

        $message = [
            'from' => array(
                'uId' => $this->uid,
                'name' => $myInfo['name'],
                'avatar' => $myInfo['avatar']
            ),
            'to' => array(
                'uId' => $uId,
                'name' => $userInfo['name'],
                'avatar' => $userInfo['avatar']
            ),
            'type' => 3,
            'chatType' => $type,
            'msg' => $msg ,
            'msgNo' => $msgNo
        ];
        LzLog::dEcho2("Chat.userToUserSend", __FILE__, __LINE__, ["message消息入库",$myInfo,$userInfo,$message]) ;
        yield $this->messageModel->addChatMessage($message, $userInfo['online']);

        if ($type == self::CHAT_TYPE_PK) {

            if ($uId <= $this->config['main']['robotId']) {

                LzLog::dEcho2("Chat.userToUserSend", __FILE__, __LINE__, "IM 中和机器人聊天") ;
                //yield $this->andRobotChat($gameId, $id, $userInfo, $myInfo);
            }

            if ($userInfo['online'] == 1&& $uId > $this->config['main']['robotId']) {
                LzLog::dEcho2("Chat.userToUserSend", __FILE__, __LINE__, "{$this->uid},{$uId} 发起约战请求") ;

                // 通知目标用户
                $response = new PkResponse();
                $response->setCode(0);
                $response->setUId($this->uid);
                $response->setGameId($gameId);
                $response->setRoomId($id);
                $response->setType($userInfo['online']);
                $response->setMsg("ok");
                $msgId = pack('N', 1030);
                $data2 = array(
                    'msgId' => $msgId,
                    'message' => $response
                );
                $this->sendToUid($uId, $data2, false);
            }

            // 通知发起人
            $response = new PkResponse();
            $response->setCode(0);
            $response->setUId($this->uid);
            $response->setGameId($gameId);
            $response->setRoomId($id);
            $response->setType(3);
            $response->setMsg("约战邀请已发出");
            $msgId = pack('N', 1030);
            $data = array(
                'msgId' => $msgId,
                'message' => $response
            );

            LzLog::dEcho2("Chat.userToUserSend", __FILE__, __LINE__, "{$this->uid},{$uId} 约战邀请已发出") ;
            $this->sendToUid($this->uid, $data, FALSE);
        }

        if ($type == self::CHAT_TYPE_GIFT){
            if ($userInfo['online'] == 1&& $uId > $this->config['main']['robotId']) {
                LzLog::dEcho2("Chat.userToUserSend", __FILE__, __LINE__, "{$this->uid},{$uId} 赠送礼物") ;

                // 通知目标用户
                $response = new ChatUserToUserResponse();
                $response->setCode(0);
                $response->setType($type);
                $response->setMsg(json_encode($msg));
                $user = new User();
                $user->setAvatar($myInfo['avatar']);
                $user->setUId($myInfo['uid']);
                $user->setUserName($myInfo['name']);
                $response->setUser($user);
                $response->setTime(time());

                $msgId = pack('N', 1018);
                $data2 = array(
                    'msgId' => $msgId,
                    'message' => $response
                );
                $this->sendToUid($uId, $data2, false);
            }

//            if(!$userInfo['uid']){
//                $userInfo = $this->dbModel->userInfoErrRestInfo($this->uid,$userInfo);
//            }

            // 通知发起人
            $response = new ChatUserToUserResponse();
            $response->setCode(0);
            $response->setType($type);
            $response->setMsg(json_encode($msg));
            $user = new User();
            $user->setAvatar($userInfo['avatar']);
            $user->setUId($userInfo['uid']);
            $user->setUserName($userInfo['name']);
            $response->setUser($user);
            $response->setTime(time());

            //$response->setMsg("礼物已发出");
            $msgId = pack('N', 1018);
            $data = array(
                'msgId' => $msgId,
                'message' => $response
            );

            LzLog::dEcho2("Chat.userToUserSend", __FILE__, __LINE__, "{$this->uid},{$uId} 礼物已发出") ;
            $this->sendToUid($this->uid, $data, FALSE);
        }

        $this->destroy();
        return true;
    }

    /**
     * @return bool|\Generator
     * 点对点聊天
     */
    public function userToUserSend_back() {
        if (empty($this->uid))
            return true;
        
        LzLog::dEcho2("Chat.userToUserSend_back", __FILE__, __LINE__, [$this->uid]) ;
        $request = new ChatUserToUserRequest($this->client_data->data);
        $uId = $request->getUId();
        $type = $request->getType();
        $msg = trim($request->getMsg());
        if (empty($uId) || empty($msg)) {
            $this->destroy();
            return true;
        }

        $myInfo = yield $this->userModel->getUserCache($this->uid);

        if ($type == 3) {
            $id = $this->roomModel->getRoomId();
            $gameId = intval($msg);
            $sec = $this->config['main']['chatGameInterval'];
            $sendmsg = [
                'mId' => 1,
                'time' => time(),
                'avatar' => $myInfo['avatar'],
                'name' => $myInfo['name'],
                'content' => ['gameId' => $gameId, 'sec' => $sec, 's' => 0, 'id' => $id],
                'from' => intval($this->uid),
                'type' => 3
            ];

            $msg = ['gameId' => $gameId, 'sec' => $sec, 'id' => $id];

            // 设置用户进入点对点游戏
            if (!isset($myInfo['chat']) || empty($myInfo['chat'])) {
                yield $this->userModel->setUserCache($this->uid, ['chat' => ['uId' => $uId]]);
            }
        } else {
            $sendmsg = [
                'mId' => 1,
                'time' => time(),
                'avatar' => $myInfo['avatar'],
                'name' => $myInfo['name'],
                'content' => $msg,
                'from' => intval($this->uid),
                'type' => $type
            ];
            if ($type == 2) {
                $picInfo = @getimagesize($msg);
                $sendmsg['width'] = $picInfo[0];
                $sendmsg['height'] = $picInfo[1];
            }
        }

        $response = new ChatUserToUserResponse();
        $response->setCode(0);
        $response->setType($type);
        $response->setMsg(json_encode($sendmsg));


        $user = new User();
        $user->setAvatar($myInfo['avatar']);
        $user->setUId($myInfo['uid']);
        $user->setUserName($myInfo['name']);
        $response->setUser($user);
        $response->setTime(time());

        $msgId = pack('N', 1018);
        $data = array(
            'msgId' => $msgId,
            'message' => $response
        );

        $userInfo = yield $this->userModel->getUserCache($uId);

        $message = [
            'from' => array(
                'uId' => $this->uid,
                'name' => $myInfo['name'],
                'avatar' => $myInfo['avatar']
            ),
            'to' => array(
                'uId' => $uId,
                'name' => $userInfo['name'],
                'avatar' => $userInfo['avatar']
            ),
            'type' => 3,
            'chatType' => $type,
            'msg' => $msg
        ];
        //print_r($message);
        yield $this->messageModel->addChatMessage($message, $userInfo['online']);

        if ($userInfo['online'] > 0) {
            if ($type == 3 && $userInfo['online'] == 1) {
                //echo "{$this->uid}========={$uId}发起约战请求\n";
                LzLog::dEcho2("Chat.userToUserSend_back", __FILE__, __LINE__, "{$this->uid},{$uId}发起约战请求") ;
                $response = new PkResponse();
                $response->setCode(0);
                $response->setUId($this->uid);
                $response->setGameId($gameId);
                $response->setMsg("ok");
                $msgId = pack('N', 1030);
                $data2 = array(
                    'msgId' => $msgId,
                    'message' => $response
                );
                $this->sendToUid($uId, $data2, false);
            } else {
                //echo "{$this->uid}========={$uId}正常聊天\n";
                LzLog::dEcho2("Chat.userToUserSend_back", __FILE__, __LINE__, "{$this->uid},{$uId}正常聊天") ;

                $this->sendToUid($uId, $data, false);
            }
        }

        $this->send($data);
    }
    /*
     * 跟机器人聊天发送游戏是直接同意
     */

    public function andRobotChat($gameId, $id, $userInfo, $myInfo) {
        echo "IM 中分配机器人\n";
        $area = $userInfo['schoolName'] ?: $userInfo['areaName'];
        $myArea = $myInfo['schoolName'] ?: $myInfo['areaName'];

        $roomInfo = array(
            'roomId' => $id,
            'type' => 3,
            'num' => 1,
            'start' => 1,
            'end' => 0,
            'match' => 0,
            'gameId' => $gameId,
            'gameName' => "",
            'gameLabel' => "",
            'create' => array(
                'uId' => $userInfo['uid'],
                'eUid' => $myInfo['uid']
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
                $myInfo['uid'] => array(
                    'uId' => $myInfo['uid'],
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

        yield $this->userModel->setUserCache($myInfo['uid'], ['online' => 2, 'roomId' => $id]);
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
        echo "IM聊天中,机器人直接同意游戏\n";
        $this->sendToUid($myInfo['uid'], $data, FALSE);
    }
}
