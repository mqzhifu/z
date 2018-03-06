<?php
class Chat{
    function __construct($c){
        $this->commands = $c;
    }

    public function run($attr){
//        if(!isset($attr['db_name']))
//            exit('db_name=xxx');

		$ip = "139.129.243.12";
		$port = 9502;


//		$server = new swoole_websocket_server($ip, $port);
//
//		$server->on('open', function (swoole_websocket_server $server, $request) {
//			echo "server: handshake success with fd{$request->fd}\n";
//		});
//
//		$server->on('message', function (swoole_websocket_server $server, $frame) {
//			echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
//
//
//			$id = $frame->data;
//
//			$server->push($frame->fd, "this is server");
//		});
//
//		$server->on('close', function ($ser, $fd) {
//			echo "client {$fd} closed\n";
//		});
//
//		$server->start();


		//创建websocket服务器对象，监听0.0.0.0:9502端口
		$ws = new swoole_websocket_server($ip, $port);

		//监听WebSocket连接打开事件.
		$ws->on('open', function ($ws, $request) {
			echo "server: handshake success with fd{$request->fd}\n";
			$fd[] = $request->fd;
			$GLOBALS['fd'][] = $fd;
		});

		//监听WebSocket消息事件
		$ws->on('message', function ($ws, $frame) {
			echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
			//群发
//			$msg =  'from'.$frame->fd.":{$frame->data}\n";
//			foreach($GLOBALS['fd'] as $aa){
//				foreach($aa as $i){
//					$ws->push($i,$msg);
//				}
//			}
		});

		//监听WebSocket连接关闭事件
		$ws->on('close', function ($ws, $fd) {
			echo "client-{$fd} is closed\n";
		});

		$ws->start();



	}
}
