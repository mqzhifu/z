<?php

namespace app\Controllers;

use app\Protobuf\FriendInviteReponse;
use Monolog\Logger;
use Server\CoreBase\Controller;
use Server\MooPHP\MooSign;
use app\Protobuf\MatchingRequest;
use app\Protobuf\InviteRequest;
use app\Protobuf\InviteResponse;
use app\Protobuf\User;
use app\Protobuf\AgreeRequest;
use app\Protobuf\AgreeResponse;
use app\Protobuf\LeaveRoomRequest;
use app\Protobuf\LeaveRoomResponse;
use app\Protobuf\StartGameRequest;
use app\Protobuf\StartGameResponse;
use app\Protobuf\ClearMatchResponse;
use app\Protobuf\MatchingResponse;
use app\Protobuf\LckResponse;
use app\Protobuf\MatchStartGameResponse;
use app\Protobuf\LogoutNoticeResponse;
use app\Tools\LzLog;

class Room extends Controller {

    private $userModel;
    private $roomModel;
    private $matchModel;
    private $messageModel;
    private $dbModel;
    private $id;

    protected function initialization($controller_name, $method_name) {
        parent::initialization($controller_name, $method_name);
        $this->userModel = $this->loader->model('UserModel', $this);
        $this->roomModel = $this->loader->model('RoomModel', $this);
        $this->matchModel = $this->loader->model('MatchModel', $this);
        $this->messageModel = $this->loader->model('MessageModel', $this);
        $this->dbModel = $this->loader->model('DbModel', $this);
    }

    // 好友请求通知
    public function http_friendInvite() {
        $uId = $this->http_input->postGet('uId');
        $sign = $this->http_input->postGet('sign');
        $num = $this->http_input->postGet('num');
        $userId = $this->http_input->postGet('userId');
        $name = $this->http_input->postGet('name');
        $avatar = $this->http_input->postGet('avatar');
        $sex = $this->http_input->postGet('sex');
        $type = $this->http_input->postGet('type');

        $secret = $this->config['main']['httpToServerSecret'];
        $params = ['uId' => $uId, 'num' => $num, 'userId' => $userId, 'name' => $name, 'avatar' => $avatar, 'sex' => $sex, 'type' => $type];
        $checkSign = MooSign::getSign($params, $secret);
        if ($checkSign == $sign) {
            $response = new FriendInviteReponse();
            $response->setNum($num);
            $response->setType($type);

            $user = new User();
            $user->setAvatar($avatar);
            $user->setUId($userId);
            $user->setUserName($name);
            $user->setSex($sex);
            $response->setUser($user);

            $msgId = pack("N", 1050);
            $data = array(
                'msgId' => $msgId,
                'message' => $response
            );
            $this->sendToUid($uId, $data, false);
        }


        $rs = ['code' => 0];
        $this->http_output->end($rs);
    }

    /**
     * 结算奖励
     */
    private function playGamesReward($userInfo, $win, $gameId) {
        LzLog::dEcho2("Room.playGamesReward", __FILE__, __LINE__, [$userInfo, $win, $gameId]) ;
        $data['integral'] = 0;   // 积分 
        $data['gameNum'] = 0;

        $integralConf = $this->config['main']['integral'];

        $userDayInfo = yield $this->userModel->getUserDayCache($userInfo['uid']);

        if (!isset($userDayInfo['integral'])) {
            $userDayInfo['integral'] = [];
        }
        if (!isset($userDayInfo['integral'][$gameId])) {
            $userDayInfo['integral'][$gameId] = 0;
        }

        if ($win == 1) {
            $playOneGameNumber = $userDayInfo['integral'][$gameId];
            if ($integralConf['number'] > $playOneGameNumber) {
                $data['integral'] = $integralConf['score'];
                $userDayInfo['integral'][$gameId] += 1;
            }
            yield $this->userModel->setUserDayCache($userInfo['uid'], $userDayInfo);
        }
        $data['gameNum'] = $userDayInfo['integral'][$gameId];

        return $data;
    }

    /**
     * 用户亲密度计算
     */
    private function getDearValue($uid, $pkUid) {
        LzLog::dEcho2("Room.getDearValue", __FILE__, __LINE__, [$uid, $pkUid]) ;
        $val = 0;
        $dearNum = $this->config['main']['pk']['dearNum'];
        $num = yield $this->userModel->getPlayGamesNum($uid, $pkUid);
        if ($dearNum > $num) {
            yield $this->userModel->setPlayGamesNum($uid, $pkUid);
            $val = 1;
        }
        return $val;
    }

    /**
     * @param $data
     * @param $roomInfo
     * @return bool|\Generator
     * 单人匹配结算
     */
    private function execSingleRoom($data, $roomInfo) {
        LzLog::dEcho2("Room.execSingleRoom", __FILE__, __LINE__, [$data, $roomInfo]) ;
        $this->dbModel->setPkLog($data, $roomInfo);

        $lckDetail = array(
            'gameId' => $roomInfo['gameId'],
            'gameName' => $roomInfo['gameName'],
            'type' => $roomInfo['type'],
            'roomId' => $roomInfo['roomId'],
            'time' => time(),
            'num' => count($data),
            'list' => array(),
            'chat' => 0,
            'winnerAvatar' => ''
        );

        $uIds = $lckInfo = [];

        if (isset($roomInfo['chat'])) {
            $lckDetail['chat'] = 1;
        }
        $uidArray = array_column($data, 'uId');
        $lckDetail['dearVal'] = yield $this->getDearValue($uidArray[0], $uidArray[1]);

        $userInfos = [];
        foreach ($data as $val) {
            $uIds[] = $val['uId'];
            $val['uId'] = intval($val['uId']);
            $row = $val;

            $loc = $roomInfo['list'][$val['uId']]['loc'];
            $row['loc'] = $loc;

            $userInfo = yield $this->userModel->getUserCache($val['uId']);
            $userInfos[$val['uId']] = $userInfo;
            $row['name'] = $userInfo['name'];
            $row['avatar'] = $userInfo['avatar'];
            $row['sex'] = $userInfo['sex'];
            $row['age'] = $userInfo['age'];
            $row['birthday'] = $userInfo['birthday'];
            $row['area'] = $userInfo['schoolName'] ? $userInfo['schoolName'] : $userInfo['areaName'];
            $row['constellation'] = $userInfo['constellation'];

            $rewardInfo = yield $this->playGamesReward($userInfo, $val['win'], $roomInfo['gameId']);
            $row['integral'] = $rewardInfo['integral'];
            $row['gameNum'] = $rewardInfo['gameNum'];

            if ($val['win']==1){
                $lckDetail['winnerAvatar'] = $userInfo['avatar'] ;
            }

            $lckDetail['list'][] = $row;
        }
        LzLog::dEcho2("Room.execSingleRoom", __FILE__, __LINE__, ["message 正文", $lckDetail]) ;
        // lixin@0409 svn 冲突合并
        yield $this->messageModel->updateGameWinMessage($data[0]['uId'], $data[1]['uId'], $data[0]['win'],$roomInfo['roomId']);
        yield $this->messageModel->updateGameWinMessage($data[1]['uId'], $data[0]['uId'], $data[1]['win'],$roomInfo['roomId']);
        
        try {
            //添加用户战绩详情
            yield $this->userModel->addLck($lckDetail);
            $response = new LckResponse();
            $response->setType($roomInfo['type']);
            $response->setNum($roomInfo['num']);
            $response->setContent(json_encode($lckDetail));

            $msgId = pack("N", 1022);
            $data = array(
                'msgId' => $msgId,
                'message' => $response
            );
            //echo "====游戏结算=结果通知推送\n";
            LzLog::dEcho2("Room.execSingleRoom", __FILE__, __LINE__, "游戏结算,结果通知推送") ;
            foreach ($uIds as $uId) {
                if ($uId <= $this->config['main']['robotId']) {
                    continue;
                }
                //echo $uId . '---' . $userInfos[$uId]['online'] . '---' . $userInfos[$uId]['roomId'] . '---' . $roomInfo['roomId'] . "\n";
                LzLog::dEcho2("Room.execSingleRoom", __FILE__, __LINE__, [$uId,$userInfos[$uId]]) ;
                if ($userInfos[$uId]['online'] == 2) {
                    if ($userInfos[$uId]['roomId'] == $roomInfo['roomId']) {
                        $this->sendToUid($uId, $data, false);
                    }
                }
            }
            //echo "====游戏结算=结果通知推送完成\n";
            LzLog::dEcho2("Room.execSingleRoom", __FILE__, __LINE__, "游戏结算,结果通知推送完成") ;
            //向redis liaozhan_lck 中lpush战绩信息
            yield $this->roomModel->addLckList($lckDetail);
        } catch (\Exception $e) {
            LzLog::dEcho2("Room.execSingleRoom", __FILE__, __LINE__, ['exception', $e->getMessage(), $e->getCode()]) ;
        }

        return true;
    }

    /**
     * @param $data
     * @param $roomInfo
     * @return bool|\Generator
     * 多人匹配结算
     */
    private function execMoreRoom($data, $roomInfo) {
        $rs = [
            'code' => 0,
            'msg' => 'ok'
        ];
        $lckDetail = array(
            'gameId' => $roomInfo['gameId'],
            'gameName' => $roomInfo['gameName'],
            'type' => $roomInfo['type'],
            'num' => count($data),
            'list' => array(
                '1' => array(),
                '2' => array()
            )
        );

        $conf = $this->config['main']['pk']['lck'];
        $num1 = $num2 = 0;
        $uIds = [];
        foreach ($data as $val) {
            if ($val['win'] != 2) {
                $uIds[] = $val['uId'];
            }
            $row = $val;
            $row['num'] = 0;

            if (!isset($roomInfo['list'][$val['uId']])) {
                $rs['code'] = 10004;
                $rs['msg'] = '用户id ' . $val['uId'] . ' 不在房间内';
                $this->http_output->end($rs);
                return false;
            }
            $loc = $roomInfo['list'][$val['uId']]['loc'];

            $row['loc'] = $loc;
            if ($loc > 10) {
                if ($row['win'] == 1) {
                    $num2 += $conf['win'];
                    $row['num'] = $conf['win'];
                    $lckDetail['list']['2']['win'] = 1;
                } else if ($row['win'] == 0) {
                    $num2 -= $conf['lose'];
                    $row['num'] = -$conf['lose'];
                    $lckDetail['list']['2']['win'] = 0;
                }
                $lckDetail['list']['2']['l'][] = $row;
            } else {
                if ($row['win'] == 1) {
                    $num1 += $conf['win'];
                    $row['num'] = $conf['win'];
                    $lckDetail['list']['1']['win'] = 1;
                } else if ($row['win'] == 0) {
                    $num1 -= $conf['lose'];
                    $row['num'] = -$conf['lose'];
                    $lckDetail['list']['1']['win'] = 0;
                }
                $lckDetail['list']['1']['l'][] = $row;
            }
        }

        if ($roomInfo['top'] == true) {
            $lckDetail['list']['1']['dormId'] = $roomInfo['create']['dormId'];
            $lckDetail['list']['1']['dName'] = $roomInfo['create']['dName'];
            $lckDetail['list']['2']['dormId'] = $roomInfo['create']['eDormId'];
            $lckDetail['list']['2']['dName'] = $roomInfo['create']['eDname'];
        } else {
            $lckDetail['list']['1']['dormId'] = $roomInfo['create']['eDormId'];
            $lckDetail['list']['1']['dName'] = $roomInfo['create']['eDname'];
            $lckDetail['list']['2']['dormId'] = $roomInfo['create']['dormId'];
            $lckDetail['list']['2']['dName'] = $roomInfo['create']['dName'];
        }

        $lckDetail['list'][1]['num'] = $num1;
        $lckDetail['list'][2]['num'] = $num2;

        $response = new LckResponse();
        $response->setType($roomInfo['type']);
        $response->setNum($roomInfo['num']);
        $response->setContent(json_encode($lckDetail));

        $msgId = pack("N", 1022);
        $data = array(
            'msgId' => $msgId,
            'message' => $response
        );
        $this->sendToUids($uIds, $data, false);

        yield $this->roomModel->addLckList($lckDetail);

        return true;
    }

    /**
     * @return bool|\Generator
     * CP发送战绩
     */
    public function http_gameLck() {

        $rs = [
            'code' => 0,
            'msg' => 'ok'
        ];
        $params = [];
        $params['roomId'] = $this->http_input->postGet('roomId');
        $params['data'] = $this->http_input->postGet('data');
        $params['sign'] = $this->http_input->postGet('sign');
        $params['appId'] = $this->http_input->postGet('appId');

        LzLog::dEcho(['http_gameLck 游戏结算', $params]) ;
        //print_r($params);
        if (empty($params['roomId']) || empty($params['data']) || empty($params['sign']) || empty($params['appId'])) {
            $rs['code'] = 10000;
            $rs['msg'] = '参数错误';
            LzLog::dEcho("游戏结算=参数错误") ;
            $this->http_output->end($rs);
            return true;
        }

        $dbModel = $this->loader->model('DbModel', $this);
        $gameInfo = yield $dbModel->getGameCache($params['appId']);
        if (empty($gameInfo)) {
            $rs['code'] = 10001;
            $rs['msg'] = '游戏不存在';
            LzLog::dEcho("游戏结算=游戏不存在") ;
            $this->http_output->end($rs);
            return true;
        }
        $sign = MooSign::getSign($params, $gameInfo['appKey']);

        if ($sign != $params['sign']) {
            $rs['code'] = 10002;
            $rs['msg'] = '签名错误';
            LzLog::dEcho("游戏结算=签名错误") ;
            $this->http_output->end($rs);
            return true;
        }

        $data = json_decode($params['data'], true);
        if (count($data) < 2) {
            $rs['code'] = 10000;
            $rs['msg'] = '参数错误';
            LzLog::dEcho("游戏结算=结算用户信息有误") ;
            $this->http_output->end($rs);
            return true;
        }
        $roomInfo = yield $this->roomModel->getRoomInfo($params['roomId']);
        if (empty($roomInfo)) {
            $rs['code'] = 10000;
            $rs['msg'] = '房间信息不存在';
            LzLog::dEcho("游戏结算=房间信息不存在") ;
            $this->http_output->end($rs);
            return true;
        }

        if ($roomInfo['end'] == 1) {
            $rs['code'] = 10004;
            $rs['msg'] = '重复通知';
            LzLog::dEcho("游戏结算=重复通知") ;
            $this->http_output->end($rs);
            return true;
        }

        $win = 0;
        foreach ($data as $val) {
            if (!isset($roomInfo['list'][$val['uId']])) {
                $rs['code'] = 10004;
                $rs['msg'] = '用户id ' . $val['uId'] . ' 不在房间内';
                LzLog::dEcho('用户id ' . $val['uId'] . ' 不在房间内') ;
                $this->http_output->end($rs);
                return false;
            }
            if ($val['win'] == 1 || $val['win'] == 2) {
                //$this->userModel->battleAddExp($val['uId'], $isWin, $roomId)
                $win = 1;
            }
        }
        if ($win != 1) {
            $rs['code'] = 10005;
            $rs['msg'] = '结果数据错误';
            LzLog::dEcho("游戏结算=结果数据错误") ;
            $this->http_output->end($rs);
            return false;
        }
        
        //给用户增加积分
        $uids = [] ;
        foreach ($data as $val) {
            $uids[] = $val['uId'] ;
            if ($val['win'] == 1) {
                $addExpResult = yield $this->userModel->battleAddExp($val['uId'], true, $params['roomId']) ;
            }else{
                $addExpResult = yield $this->userModel->battleAddExp($val['uId'], false, $params['roomId']) ;
            }
            if ($addExpResult['succ']) {
                LzLog::dEcho(['增加积分成功', $addExpResult]) ;
            }else{
                LzLog::dEcho(['增加积分失败', $addExpResult['e']->getCode(), $addExpResult['e']->getMessage()]) ;
            }
        } 
        
        //增加亲密度
        yield $this->userModel->battleAddDearValue($uids[0], $uids[1]) ;

        $roomInfo['end'] = 1;
        // 设置房间信息
        yield $this->roomModel->setRoomInfo($roomInfo);
        
        LzLog::dEcho(['http_gameLck 结算推送', $data, $roomInfo]) ;

        if ($roomInfo['type'] == 3) {
            $row = yield $this->execSingleRoom($data, $roomInfo);
        } else {
            $row = yield $this->execMoreRoom($data, $roomInfo);
        }
        if ($row) {
            $this->http_output->end($rs);
        }
    }

    /**
     * @param $timestamp
     * @param $uId
     * @param $roomId
     * @return bool
     * 多人匹配
     */
    public function matchUser($timestamp, $uId, $roomId) {
        $time = time();
        $sec = $this->config['main']['matchInterval'] - ($time - $timestamp);
        LzLog::dEcho(["matchUser 多人匹配 {$sec}-{$uId}", $timestamp, $uId, $roomId]) ;

        $info = $this->userModel->getMatchInfo($uId);
        if ($info) {
            swoole_timer_clear($this->id);
            $this->userModel->delMatchInfo($uId);

            if ($info['create']['uId'] == $uId) {
                $info['start'] = 1;
                $this->roomModel->setRoomInfo($info);

                $response = new MatchStartGameResponse();
                $response->setGameId($info['gameId']);
                $response->setRoomId($info['roomId']);
                $response->setSec($this->config['main']['matchSuccess']);
                $response->setType($info['type']);
                $response->setNum($info['num']);
                $response->setPosition(0);
                $uIds = [];
                foreach ($info['list'] as $uId => $val) {
                    $uIds[] = $uId;
                    $user = new User();
                    $user->setUId($val['uId']);
                    $user->setUserName($val['name']);
                    $user->setAvatar($val['avatar']);
                    $user->setLocation($val['loc']);
                    $response->addUserList($user);
                }

                $msgId = pack("N", 1026);
                $data = array(
                    'msgId' => $msgId,
                    'message' => $response
                );

                get_instance()->sendToUids($uIds, $data);
            }
            return true;
        }

        $roomInfo = $this->roomModel->getRoomInfoSynch($roomId);
        if (empty($roomInfo) || $roomInfo['match'] != 1) {
            swoole_timer_clear($this->id);
            return true;
        }

        // 开始清除房间id
        if ($sec <= 0) {
            swoole_timer_clear($this->id);
            $roomInfo['match'] = 0;
            $this->roomModel->setRoomInfoSynch($roomInfo);
        }

        $response = new MatchingResponse();
        $response->setCode(0);
        $response->setSec($sec);

        $msgId = pack("N", 1006);
        $data = array(
            'msgId' => $msgId,
            'message' => $response
        );

        foreach ($roomInfo['list'] as $uId => $val) {
            get_instance()->sendToUid($uId, $data);
        }
    }

    /**
     * @return bool|\Generator
     * 多人匹配请求
     */
    public function matching() {
        LzLog::dEcho("matching 多人匹配请求") ;
        if (empty($this->uid))
            return true;

        $request = new MatchingRequest($this->client_data->data);
        $roomId = $request->getRoomId();

        $roomInfo = yield $this->roomModel->getRoomInfo($roomId);
        if (empty($roomInfo)) {
            $this->matchError(1101);
            return true;
        }

        // 只有类型 1 可以匹配
        if ($roomInfo['type'] == 2) {
            $this->matchError(1101);
            return true;
        }

        // 不是自己房间
        if (!isset($roomInfo['list'][$this->uid]) || $roomInfo['create']['uId'] != $this->uid) {
            $this->matchError(1106);
            return true;
        }

        $timestamp = time();
        $info = array(
            'gameId' => $roomInfo['gameId'],
            'num' => $roomInfo['type'],
            'roomId' => $roomId,
            'time' => $timestamp
        );

        $roomInfo['match'] = 1;
        yield $this->roomModel->setRoomInfo($roomInfo);

        yield $this->matchModel->add($info);
        $uId = $this->uid;
        $this->id = swoole_timer_tick(1000, function () use ($timestamp, $uId, $roomId) {
            $this->matchUser($timestamp, $uId, $roomId);
        });

        $response = new MatchingResponse();
        $response->setCode(0);
        $response->setSec($this->config['main']['matchInterval']);

        $msgId = pack("N", 1006);
        $data = array(
            'msgId' => $msgId,
            'message' => $response
        );

        $uIds = [];
        foreach ($roomInfo['list'] as $uId => $val) {
            $uIds[] = $uId;
        }

        $this->sendToUids($uIds, $data);
    }

    /**
     * @param $code
     * 匹配报错
     */
    private function matchError($code) {
        LzLog::dEcho2("Room.matchError", __FILE__, __LINE__, ['matchError 匹配报错', $code]) ;
        $response = new MatchingResponse();
        $response->setCode($code);
        if ($code > 0) {
            $response->setMsg($this->config['code'][$code]);
        }

        $msgId = pack("N", 1006);
        $data = array(
            'msgId' => $msgId,
            'message' => $response
        );
        $this->send($data);
    }

    /**
     * @return bool|\Generator
     * 多人房间开始游戏
     */
    public function startGame() {
        LzLog::dEcho("startGame 多人房间开始游戏") ;
        if (empty($this->uid))
            return true;

        $request = new StartGameRequest($this->client_data->data);
        $roomId = $request->getRoomId();
        $roomInfo = yield $this->roomModel->getRoomInfo($roomId);
        if (empty($roomInfo) || $roomInfo['start'] == 1) {
            return true;
        }

        if (count($roomInfo['list']) != $roomInfo['num'])
            return true;

        if ($roomInfo['create']['uId'] != $this->uid)
            return true;

        $roomInfo['start'] = 1;

        yield $this->roomModel->setRoomInfo($roomInfo);

        $response = new StartGameResponse();
        $response->setSec($this->config['main']['matchSuccess']);

        $uIds = [];
        foreach ($roomInfo['list'] as $uId => $val) {
            $uIds[] = $uId;
        }

        // 设置用户状态游戏中
        foreach ($uIds as $uId) {
            yield $this->userModel->setUserCache($uId, array(
                        'online' => 2,
                        'roomId' => ''
            ));
        }

        if ($uIds) {
            $msgId = pack('N', 1014);
            $data = array(
                'msgId' => $msgId,
                'message' => $response
            );
            LzLog::dEcho(["startGame 多人房间开始游戏", $uIds, $data]) ;
            // 发给其他人
            $this->sendToUids($uIds, $data);
        }else{
            LzLog::dEcho(["startGame 多人房间开始游戏", $uIds]) ;
        }
    }

    /**
     * @param $uId
     * @param $roomInfo
     * @param bool $destroy
     * @return \Generator
     * 清理房间匹配
     */
    private function clearMatch($uId, $roomInfo, $destroy = true) {
        LzLog::dEcho2("Room.clearMatch", __FILE__, __LINE__, [$uId, $roomInfo, $destroy]) ;
        $roomInfo['match'] = 0;

        $matchModel = $this->loader->model('MatchModel', $this);

        // 加入房间匹配销毁
        yield $matchModel->addLeaveRoom($roomInfo['roomId']);

        $myInfo = $roomInfo['list'][$uId];
        unset($roomInfo['list'][$uId]);

        $roomInfo['locs'][] = $myInfo['loc'];

        $uIds = [];
        foreach ($roomInfo['list'] as $uId => $val) {
            $uIds[] = $uId;
        }

        $roomModel = $this->loader->model('RoomModel', $this);
        $userModel = $this->loader->model('UserModel', $this);
        if ($uIds) {
            if ($myInfo['uId'] == $roomInfo['create']['uId']) {
                $creatorInfo = [];
                foreach ($roomInfo['list'] as $uId => $val) {
                    $creatorInfo = yield $userModel->getUserCache($uId);
                    break;
                }
                $roomInfo['create']['uId'] = $creatorInfo['uid'];
                $roomInfo['create']['name'] = $creatorInfo['name'];
            }

            // 设置房间信息
            yield $roomModel->setRoomInfo($roomInfo);

            $response = new ClearMatchResponse();
            $response->setDName($roomInfo['create']['dName']);
            $response->setGameName($roomInfo['gameName']);
            $response->setRoomId($roomInfo['roomId']);
            $response->setType($roomInfo['type']);
            $response->setNum($roomInfo['num']);

            foreach ($roomInfo['list'] as $row) {
                $user = new User();
                $user->setAvatar($row['avatar']);
                $user->setLocation($row['loc']);
                $user->setType(3);
                $user->setUId($row['uId']);
                $user->setUserName($row['name']);
                if ($row['uId'] == $roomInfo['create']['uId']) {
                    $user->setIsCreator(1);
                } else {
                    $user->setIsCreator(0);
                }
                $response->addUserList($user);
            }

            $msgId = pack('N', 1020);
            $data = array(
                'msgId' => $msgId,
                'message' => $response
            );

            $this->sendToUids($uIds, $data, $destroy);
        }
    }

    /**
     * @param $uId
     * @param $roomInfo
     * @param bool $destroy
     * @return bool|\Generator
     * 用户离线或者离开房间清理房间信息
     */
    public function clearRoom($uId, $roomInfo, $destroy = true) {
        LzLog::dEcho2("Room.clearRoom", __FILE__, __LINE__,['clearRoom 用户离线或者离开房间清理房间信息', $uId, $roomInfo, $destroy]) ;
        $roomModel = $this->loader->model('RoomModel', $this);
        if ($roomInfo['end'] == 1 || $roomInfo['start'] == 1) {
            return true;
        }

        // 匹配阶段
        if ($roomInfo['type'] == 1 && $roomInfo['match'] == 1) {
            yield $this->clearMatch($uId, $roomInfo, $destroy);
            return true;
        }

        $myInfo = $roomInfo['list'][$uId];
        unset($roomInfo['list'][$uId]);

        if ($roomInfo['type'] == 1) {
            $roomInfo['locs'][] = $myInfo['loc'];
        } else {
            if ($myInfo['loc'] > 10) {
                $roomInfo['locs'][2][] = $myInfo['loc'];
            } else {
                $roomInfo['locs'][1][] = $myInfo['loc'];
            }
        }

        $uIds = [];
        foreach ($roomInfo['list'] as $uId => $val) {
            $uIds[] = $uId;
        }

        $userModel = $this->loader->model('UserModel', $this);
        if ($uIds) {

            $response = new LeaveRoomResponse();
            $user = new User();
            $user->setAvatar($myInfo['avatar']);
            $user->setUId($myInfo['uId']);
            $user->setUserName($myInfo['name']);
            $user->setLocation($myInfo['loc']);
            $user->setType(3);
            $response->setUser($user);

            if ($myInfo['uId'] == $roomInfo['create']['uId']) {
                $creatorInfo = [];
                // 普通
                if ($roomInfo['type'] == 1) {
                    foreach ($roomInfo['list'] as $uId => $val) {
                        $creatorInfo = yield $userModel->getUserCache($uId);
                        break;
                    }
                    $response->setCreatorUid($creatorInfo['uid']);
                } else {
                    // 宿舍约战
                    foreach ($roomInfo['list'] as $uId => $val) {
                        if ($myInfo['loc'] < 10) {
                            if ($val['loc'] < 10) {
                                $creatorInfo = yield $userModel->getUserCache($uId);
                                break;
                            }
                        } else {
                            if ($val['loc'] > 10) {
                                $creatorInfo = yield $userModel->getUserCache($uId);
                                break;
                            }
                        }
                    }

                    if (empty($creatorInfo)) {
                        foreach ($roomInfo['list'] as $uId => $val) {
                            $creatorInfo = yield $userModel->getUserCache($uId);
                            break;
                        }
                    }

                    if ($creatorInfo['dormId'] != $roomInfo['dormId']) {
                        $dormInfo = yield $this->roomModel->getDormInfo($creatorInfo['dormId']);
                        // 更新敌方宿舍id为0
                        $roomInfo['create']['eDormId'] = 0;
                        $roomInfo['create']['eDname'] = '';
                        $roomInfo['create']['eUId'] = 0;
                        $roomInfo['top'] = !$roomInfo['top'];
                        $roomInfo['dormId'] = $creatorInfo['dormId'];
                        $roomInfo['dName'] = $dormInfo['name'];
                    }

                    $response->setCreatorUid($creatorInfo['uid']);
                }

                $roomInfo['create']['uId'] = $creatorInfo['uid'];
                $roomInfo['create']['name'] = $creatorInfo['name'];
            }

            $msgId = pack('N', 1012);
            $data = array(
                'msgId' => $msgId,
                'message' => $response
            );

            // 设置房间信息
            yield $roomModel->setRoomInfo($roomInfo);

            $this->sendToUids($uIds, $data, $destroy);
        } else {
            yield $roomModel->delRoomInfo($roomInfo);
        }
    }

    /** ds
     * @return bool|\Generator
     * 离开房间
     */
    public function leaveRoom() {
        if (empty($this->uid))
            return true;

        
        LzLog::dEcho2('Room.leaveRoom', __FILE__, __LINE__, ['leaveRoom 离开房间', $this->uid . " leaveRoom "]) ;
        $request = new LeaveRoomRequest($this->client_data->data);
        $roomId = $request->getRoomId();

        $roomInfo = yield $this->roomModel->getRoomInfo($roomId);
        // 房间已经销毁
        if (empty($roomInfo)) {
            $this->destroy();
            return true;
        }

        // 已经离开房间
        if (!isset($roomInfo['list'][$this->uid])) {
            $this->destroy();
            return true;
        }

        yield $this->userModel->setUserCache($this->uid, ['online' => 1, 'roomId' => '', 'match' => 0]);
        yield $this->clearRoom($this->uid, $roomInfo, false);

        foreach ($roomInfo['list'] as $val) {
            if ($val['uId'] == $this->uid)
                continue;

            $info = $val;
        }
        $myInfo = yield $this->userModel->getUserCache($info['uId']);
        if ($info['uId'] > $this->config['main']['robotId'] && $myInfo['online'] == 2) {
            $response = new LeaveRoomResponse();
            $user = new User();
            $user->setAvatar($info['avatar']);
            $user->setUId($info['uId']);
            $user->setUserName($info['name']);
            $user->setLocation($info['loc']);
            $user->setType(3);
            $response->setUser($user);
            $response->setCreatorUid($info['uId']);
            $msgId = pack('N', 1012);
            $data = array(
                'msgId' => $msgId,
                'message' => $response
            );
            print_r($info);
            $this->sendToUid($info['uId'], $data);
        }

        $this->destroy();
    }

    /**
     * @return bool|\Generator
     * 同意加入房间
     */
    public function agree() {
        if (empty($this->uid))
            return true;
        
        $request = new AgreeRequest($this->client_data->data);
        $roomId = $request->getRoomId();

        $roomInfo = yield $this->roomModel->getRoomInfo($roomId);
        //LzLog::dEcho(['agree 同意加入房间', $this->uid, $roomId, $roomInfo]) ;
        LzLog::dEcho2('Room.agree', __FILE__, __LINE__, ['同意加入房间', $this->uid, $roomId, $roomInfo]) ;
        // 房间已经销毁
        if (empty($roomInfo)) {
            $this->agreeError(1101);
            return true;
        }

        // 已经加入房间
        if (isset($roomInfo['list'][$this->uid])) {
            $this->agreeError(1102);
            return true;
        }

        $myInfo = yield $this->userModel->getUserCache($this->uid);
        if (empty($myInfo['dormId'])) {
            $this->agreeError(1105);
            return true;
        }

        if ($roomInfo['start'] == 1) {
            $this->agreeError(1103);
            return true;
        }

        if ($roomInfo['type'] == 1) {
            // 房间人数已满员
            if (count($roomInfo['list']) >= $roomInfo['num'] || $roomInfo['match'] == 1) {
                $this->agreeError(1103);
                return true;
            }

            if ($roomInfo['create']['dormId'] != $myInfo['dormId']) {
                $this->agreeError(1104);
                return true;
            }
            $rs = $this->generalAgree($roomInfo, $myInfo);
        } else {

            if ($roomInfo['create']['dormId'] == $myInfo['dormId']) {
                // 房间人数已满员
                if ($roomInfo['top']) {
                    if (count($roomInfo['locs'][1]) == 0) {
                        $this->agreeError(1103);
                        return true;
                    }
                } else {
                    if (count($roomInfo['locs'][2]) == 0) {
                        $this->agreeError(1103);
                        return true;
                    }
                }
            } else {
                // 房间人数已满员
                if ($roomInfo['top']) {
                    if (count($roomInfo['locs'][2]) == 0) {
                        $this->agreeError(1103);
                        return true;
                    }
                } else {
                    if (count($roomInfo['locs'][1]) == 0) {
                        $this->agreeError(1103);
                        return true;
                    }
                }

                if ($roomInfo['create']['eDormId'] && $myInfo['dormId'] != $roomInfo['create']['eDormId']) {
                    $this->agreeError(1103);
                    return true;
                }
            }
            $rs = yield $this->dormAgree($roomInfo, $myInfo);
        }

        $msgId = pack('N', 1010);
        $data = array(
            'msgId' => $msgId,
            'message' => $rs['my']
        );

        yield $this->userModel->setRoomId($this->uid, $roomInfo['roomId']);

        // 设置房间信息
        yield $this->roomModel->setRoomInfo($rs['roomInfo']);

        $this->send($data, false);

        $data = array(
            'msgId' => $msgId,
            'message' => $rs['other']['info']
        );

        // 发给其他人
        $this->sendToUids($rs['other']['uIds'], $data);
    }

    private function agreeError($code) {
        LzLog::dEcho2('Room.agreeError', __FILE__, __LINE__, [$code]) ;

        $response = new AgreeResponse();
        $response->setCode($code);
        $response->setType(1);
        $response->setDataType(1);
        if ($code > 0) {
            $response->setMsg($this->config['code'][$code]);
        }

        $msgId = pack("N", 1010);
        $data = array(
            'msgId' => $msgId,
            'message' => $response
        );
        $this->send($data);
    }

    // 宿舍约战同意信息
    private function dormAgree($roomInfo, $myInfo) {
        if ($roomInfo['create']['dormId'] == $myInfo['dormId']) {
            if ($roomInfo['top']) {
                $los = $roomInfo['locs'][1][0];
                unset($roomInfo['locs'][1][0]);
                $roomInfo['locs'][1] = array_values($roomInfo['locs'][1]);
            } else {
                $los = $roomInfo['locs'][2][0];
                unset($roomInfo['locs'][2][0]);
                $roomInfo['locs'][2] = array_values($roomInfo['locs'][2]);
            }
        } else {
            if ($roomInfo['top']) {
                $los = $roomInfo['locs'][2][0];
                unset($roomInfo['locs'][2][0]);
                $roomInfo['locs'][2] = array_values($roomInfo['locs'][2]);
            } else {
                $los = $roomInfo['locs'][1][0];
                unset($roomInfo['locs'][1][0]);
                $roomInfo['locs'][1] = array_values($roomInfo['locs'][1]);
            }

            $dormInfo = yield $this->roomModel->getDormInfo($myInfo['dormId']);
            $roomInfo['create']['eDormId'] = $myInfo['dormId'];
            $roomInfo['create']['eDname'] = $dormInfo['name'];
            $roomInfo['create']['eUId'] = $myInfo['uid'];
        }
        $info = array();
        $info['loc'] = $los;
        $info['uId'] = $myInfo['uid'];
        $info['name'] = $myInfo['name'];
        $info['avatar'] = $myInfo['avatar'];
        $roomInfo['list'][$this->uid] = $info;

        $rs = array(
            'other' => array(),
            'my' => array(),
            'roomInfo' => $roomInfo
        );

        $response = new AgreeResponse();
        $response->setCode(0);
        $response->setType(2);
        $response->setDataType(1);
        foreach ($roomInfo['list'] as $row) {
            $user = new User();
            $user->setAvatar($row['avatar']);
            $user->setLocation($row['loc']);
            $user->setType(3);
            $user->setUId($row['uId']);
            $user->setUserName($row['name']);
            if ($row['uId'] == $roomInfo['create']['uId']) {
                $user->setIsCreator(1);
            } else {
                $user->setIsCreator(0);
            }
            $response->addUserList($user);
        }

        if ($roomInfo['top']) {
            $response->setDName($roomInfo['create']['dName']);
            $response->setEDname($roomInfo['create']['eDname']);
        } else {
            $response->setDName($roomInfo['create']['eDname']);
            $response->setEDname($roomInfo['create']['dName']);
        }

        $response->setGameName($roomInfo['gameName']);
        $response->setRoomId($roomInfo['roomId']);
        $response->setNum($roomInfo['num']);

        $dormInfo = yield $this->roomModel->getDormInfo($myInfo['dormId']);
        $rs['my'] = $response;
        $user = new User();
        $user->setAvatar($info['avatar']);
        $user->setLocation($info['loc']);
        $user->setType(3);
        $user->setUId($info['uId']);
        $user->setUserName($info['name']);
        $user->setDName($dormInfo['name']);

        $response = new AgreeResponse();
        $response->setCode(0);
        $response->setType(2);
        $response->setDataType(2);
        $response->setUser($user);

        foreach ($roomInfo['list'] as $row) {
            if ($row['uId'] != $myInfo['uid']) {
                $rs['other']['uIds'][] = $row['uId'];
            }
        }
        $rs['other']['info'] = $response;

        return $rs;
    }

    // 普通对战
    private function generalAgree($roomInfo, $myInfo) {
        LzLog::dEcho2('Room.generalAgree', __FILE__, __LINE__, [$roomInfo, $myInfo]) ;
        $info = array();
        $info['loc'] = $roomInfo['locs'][0];
        $info['uId'] = $myInfo['uid'];
        $info['name'] = $myInfo['name'];
        $info['avatar'] = $myInfo['avatar'];
        $roomInfo['list'][$this->uid] = $info;
        unset($roomInfo['locs'][0]);
        $roomInfo['locs'] = array_values($roomInfo['locs']);
        $rs = array(
            'other' => array(),
            'my' => array(),
            'roomInfo' => $roomInfo
        );

        $response = new AgreeResponse();
        $response->setCode(0);
        $response->setType(1);
        $response->setDataType(1);
        foreach ($roomInfo['list'] as $row) {
            $user = new User();
            $user->setAvatar($row['avatar']);
            $user->setLocation($row['loc']);
            $user->setType(1);
            $user->setUId($row['uId']);
            $user->setUserName($row['name']);
            if ($row['uId'] == $roomInfo['create']['uId']) {
                $user->setIsCreator(1);
            } else {
                $user->setIsCreator(0);
            }
            $response->addUserList($user);
        }
        $response->setDName($roomInfo['create']['dName']);
        $response->setGameName($roomInfo['gameName']);
        $response->setRoomId($roomInfo['roomId']);
        $response->setNum($roomInfo['num']);

        $rs['my'] = $response;

        $user = new User();
        $user->setAvatar($info['avatar']);
        $user->setLocation($info['loc']);
        $user->setType(1);
        $user->setUId($info['uId']);
        $user->setUserName($info['name']);

        $response = new AgreeResponse();
        $response->setCode(0);
        $response->setType(1);
        $response->setDataType(2);
        $response->setUser($user);

        foreach ($roomInfo['list'] as $row) {
            if ($row['uId'] != $myInfo['uid']) {
                $rs['other']['uIds'][] = $row['uId'];
            }
        }
        $rs['other']['info'] = $response;

        return $rs;
    }

    // 邀请
    public function invite() {
        if (empty($this->uid))
            return true;

        $request = new InviteRequest($this->client_data->data);
        $roomId = $request->getRoomId();
        $uId = $request->getUId();
        $roomInfo = yield $this->roomModel->getRoomInfo($roomId);
        //LzLog::dEcho(["invite 邀请", $this->uid, $roomInfo, $uId]);
        LzLog::dEcho2('Room.invite', __FILE__, __LINE__, ["邀请", $this->uid, $roomInfo, $uId]) ;
        if (empty($roomInfo)) {
            return true;
        }

        if (isset($roomInfo['list'][$uId])) {
            return true;
        }

        if ($roomInfo['type'] == 1 && $roomInfo['create']['uId'] != $this->uid) {
            return true;
        }

        $inviteUser = yield $this->userModel->getUserCache($uId);
        if (empty($inviteUser) || $inviteUser['online'] != 1)
            return true;

        $myUser = yield $this->userModel->getUserCache($this->uid);

        $message = [
            'uId' => $uId,
            'name' => $myUser['name'],
            'avatar' => $myUser['avatar'],
            'gameName' => $roomInfo['gameName'],
            'sendUid' => $this->uid,
            'type' => 2,
            'dName' => $roomInfo['create']['dName'],
            'roomType' => $roomInfo['type'],
            'roomNum' => $roomInfo['num']
        ];

        yield $this->messageModel->addInviteMessage($message);

        $user = new User();
        $user->setUId($this->uid);
        $user->setUserName($myUser['name']);
        $user->setAvatar($myUser['avatar']);
        $response = new InviteResponse();
        $response->setUser($user);
        $response->setContent('邀请你玩游戏' . $roomInfo['gameName']);
        $response->setRoomId($roomId);

        $msgId = pack('N', 1008);
        $data = array(
            'msgId' => $msgId,
            'message' => $response
        );
        $this->sendToUid($uId, $data);
    }

}
