<?php

namespace app\Controllers;

use app\Protobuf\CloseRequest;
use app\Protobuf\CloseResponse;
use Server\CoreBase\Controller;
use app\Protobuf\ConnectRequest;
use app\Protobuf\ConnectResponse;
use app\Protobuf\HeartbeatResponse;
use app\Tools\LzLog ;

class Server extends Controller {

    private $userModel;
    private $roomModel;

    protected function initialization($controller_name, $method_name) {
        parent::initialization($controller_name, $method_name);
        $this->userModel = $this->loader->model('UserModel', $this);
        $this->roomModel = $this->loader->model('RoomModel', $this);
    }

    public function heartbeat() {

        if (empty($this->uid))
            return true;

        $response = new HeartbeatResponse();
        $response->setUId($this->uid);
        $msgId = pack('N', 1004);
        $data = array(
            'msgId' => $msgId,
            'message' => $response
        );

        $this->send($data);
        $this->destroy();
    }

    public function connect() {
        
        if ($this->uid)
            return true;

        $request = new ConnectRequest($this->client_data->data);
        $token = $request->getToken();
        $loginRandom = $request->getLoginRandom();
        if (empty($token)) {
            $this->error(1001);
            return true;
        }
        $uId = $this->userModel->getUId($token);
        if (empty($uId)) {
            $this->error(1002);
            return true;
        }


        $cacheRandom = yield $this->userModel->getLoginRandom($uId);
        LzLog::dEcho2("Server.connect", __FILE__, __LINE__, ['cacheRandom'=>$cacheRandom, 'loginRandom'=>$loginRandom, 'uid'=>$uId]) ;
        if ($cacheRandom) {
            if ($loginRandom != $cacheRandom) {
                $this->error(1111);
                return true;
            }
        }

        $user = yield $this->userModel->getUserCache($uId);

        if (empty($user)) {
            $this->error(1003);
            return true;
        }
        
        LzLog::dEcho2("Server.connect", __FILE__, __LINE__, "登录绑定 UID={$uId}") ;
        $this->bindUid($uId);

        $response = new ConnectResponse();
        $response->setCode(0);

        $msgId = pack('N', 1002);
        $data = array(
            'msgId' => $msgId,
            'message' => $response
        );

        // 初始化用户
        yield $this->userModel->setUserCache($uId, array('online' => 1, 'match' => 0, 'roomId' => '', 'chat' => []));

        $this->send($data);
    }

    private function leave() {
        
        LzLog::dEcho2("Server.leave", __FILE__, __LINE__, ['fd'=>$this->fd, 'uid'=>$this->uid, 'o'=>'close']) ;
        if (empty($this->uid)) {
            return true;
        }

        $info = yield $this->userModel->setOffline($this->uid);
        if ($info !== true) {

            // 设置用户离开房间信息
            if (isset($info['roomInfo'])) {
                $room = new Room();
                yield $room->clearRoom($this->uid, $info['roomInfo'], false);
            }

            if (isset($info['chat'])) {
                $chat = new Chat();
                yield $chat->clearChat($info['chat'], $this->uid);
            }
        }






        //设置附近的人
        yield $this->userModel->setNearOnlineUser($this->uid);

        $this->userModel->delMatchInfo($this->uid);
    }

    public function onConnect() {
        $this->destroy();
    }

    public function onClose() {
        yield $this->leave();
        $this->destroy();
    }

    private function error(int $code) {
        $response = new ConnectResponse();
        $response->setCode($code);
        if ($code > 0) {
            $response->setMsg($this->config['code'][$code]);
        }

        $msgId = pack("N", 1002);
        $data = array(
            'msgId' => $msgId,
            'message' => $response
        );
        echo "===返回{$code}\n";
        $this->send($data);
    }

}
