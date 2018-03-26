<?php
//各种变量的过滤
class SwooleLib {


    private $_port = "9501";
    private $_host = "127.0.0.1";

    function init(){

    }

    function websocket(){
        $server = new swoole_websocket_server($this->_host, $this->_port);

        $server->on('open', function (swoole_websocket_server $server, $request) {
            echo "server: handshake success with fd{$request->fd}\n";
        });

        $server->on('message', function (swoole_websocket_server $server, $frame) {
            echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
            $server->push($frame->fd, "this is server");
        });

        $server->on('close', function ($ser, $fd) {
            echo "client {$fd} closed\n";
        });

        $server->start();
    }
}