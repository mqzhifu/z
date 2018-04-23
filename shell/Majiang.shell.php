<?php
//include '../lib/redisPHP.lib.php' ;
class Majiang{

	private $redis ;

    private $user_fd = array();
	const STATUS_ONLINE = 1 ;
	const STATUS_OFFLINE = 2 ;
	const STATUS_PLAYING = 3 ;
	const STATUS_AUTO = 4 ;

    function __construct($c){
//        $this->commands = $c;
//		$this->redis = new RedisPHPLib() ;
    }

    public function run($attr){
        $ip = "0.0.0.0";
		$port = 9502;

//        $this->redis = new RedisPHPLib();

		//创建websocket服务器对象，监听0.0.0.0:9502端口
		$ws = new swoole_websocket_server($ip, $port);


        $ws->set(array(
//            'worker_num' => 2,
//            'reactor_num'=>8,
//            'task_worker_num'=>1,
//            'dispatch_mode' => 2,
//            'debug_mode'=> 1,
//            'daemonize' => true,
//            'log_file' => __DIR__.'/log/webs_swoole.log',
            'heartbeat_check_interval' => 60,
//            'heartbeat_idle_time' => 600,
        ));


        $ws->on('open', function (swoole_websocket_server $ws, $request) {
            echo "server: conn success with fd{$request->fd}\n";

        });

        $ws->on('message', function (swoole_websocket_server $ws, $frame) {
            echo "receive from {$frame->fd}:data[{$frame->data}],opcode:{$frame->opcode},fin:{$frame->finish}\n";
            $receive_data = json_decode( $frame->data);

            if( !isset($receive_data['uid']) || !$receive_data['uid']){
                $msg = 'err:uid is null...';
            }else{

                if( !isset($receive_data['fd']) || !$receive_data['fd'] ){
                    $msg = 'save user  new fd!';
                    $this->user_fd[$receive_data['uid']] = $frame->fd;
                }else{
                    //可能是 用户断开了连接，重新连接，也可能是被攻击
                    if( $this->user_fd[$receive_data['uid']] != $frame->fd){
                        $msg = "maybe user offline,so up new fd!";
                        $this->user_fd[$receive_data['uid']] = $frame->fd;
                    }
                }
            }

            $ws->push($frame->fd, "server copy that,$msg,waiting...,please~");
        });





//		//监听WebSocket消息事件
//		$ws->on('message', function ($ws, $frame) {
//			echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
//			$_data = json_decode($frame->data, true) ;
//
//			switch ($_data['req_action']){
//				case 'connect' :
//                    // 客户端连接
//                    $this->clientConnect($ws, $frame, $_data) ;
//                    break;
//                case 'heartbeat':
//                    // 心跳处理
//                    break;
//                default:
//                    // action_xxx 处理
//                    $this->doAction($ws, $frame, $_data) ;
//                    null ;
//			}
//
//		});
//
		//监听WebSocket连接关闭事件
		$ws->on('close', function ($ws, $fd) {
			echo "client-{$fd} is closed\n";
		});
//
		$ws->start();

	}


    function cnt($i){
        echo "cnt".$i."\n";
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


$r = new Majiang(null) ;
$r->run(null ) ;
