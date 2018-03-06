<?php
include '../lib/redisPHP.lib.php' ;
class Chat{

	private $redis ;

	const STATUS_ONLINE = 1 ;
	const STATUS_OFFLINE = 2 ;
	const STATUS_PLAYING = 3 ;
	const STATUS_AUTO = 4 ;

    function __construct($c){
        $this->commands = $c;
		$this->redis = new RedisPHPLib() ;
    }

    public function run($attr){
		$ip = "127.0.0.1";
		$port = 9502;

		//创建websocket服务器对象，监听0.0.0.0:9502端口
		$ws = new swoole_websocket_server($ip, $port);
        $ws->set(array(
            'worker_num'=>4
        ));

		//监听WebSocket连接打开事件.
		$ws->on('open', function ($ws, $request) {
			echo "server: handshake success with fd{$request->fd}\n";


		});

		//监听WebSocket消息事件
		$ws->on('message', function ($ws, $frame) {
			echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
			$_data = json_decode($frame->data, true) ;

			switch ($_data['req_action']){
				case 'connect' :
                    // 客户端连接
                    $this->clientConnect($ws, $frame, $_data) ;
                    break;
                case 'heartbeat':
                    // 心跳处理
                    break;
                default:
                    // action_xxx 处理
                    $this->doAction($ws, $frame, $_data) ;
                    null ;
			}

		});

		//监听WebSocket连接关闭事件
		$ws->on('close', function ($ws, $fd) {
			echo "client-{$fd} is closed\n";
		});

		$ws->start();

	}

    private function doAction($ws, $frame, $wsData){
        //调用东岩action
        //返回格式 Chat::responseFormat(xx,xx);

        $result = $this->$wsData['req_action']($wsData) ;

        $wsData = json_encode(array(
            'req_action' => $wsData['req_action'] ,
            'unixtime' => time() ,
            'res_data' => $result['data']
        ), JSON_UNESCAPED_UNICODE) ;

        foreach ($result['uid'] as $uid){
            $clientInfo = $this->redis->get('USER_'.$uid) ;
            $clientInfo = json_decode($clientInfo, 1) ;
            $ws->push($clientInfo['sock_fd'], $wsData) ;
        }
    }

    public function sampleAction($data){
        echo 'sample action : ' , var_export($data, 1) , PHP_EOL ;
        return self::responseFormat(1, array('msg'=>'test')) ;
    }

    public static function responseFormat($uid, $data){

        if (!is_array($uid)){
            $uid = array(
                $uid ,
            ) ;
        }

        return array(
            'uid' => $uid ,
            'data' => $data
        );
    }

    private function clientConnect($ws, $frame, $wsData){

        $authcode = $wsData['req_data']['authcode'] ;
        $value = json_encode(array(
            'sock_fd' => $frame->fd ,
            'connect_time' => time() ,
            'last_heartbeat_time' => time() ,
            'status' => self::STATUS_ONLINE
        ), JSON_UNESCAPED_UNICODE) ;


        $uid = 1 ; // 这里通过authcode导出uid

        $this->redis->set('USER_'.$uid, $value) ;

        $data = json_encode(array(
            'req_action' => 'connect' ,
            'unixtime' => time() ,
            'res_data' => array(
                'msg' => 'ok'
            )
        ), JSON_UNESCAPED_UNICODE);

        $ws->push($frame->fd, $data);

        return true ;
    }
}


$r = new Chat(null) ;
$r->run(null ) ;
