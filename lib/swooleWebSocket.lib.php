<?php
class SwooleWebSocketLib{
    private $_ip = "0.0.0.0";
    private $_port = "9500";
    private $_config = array(
        'task_worker_num',
        'worker_num'=>4,
        'reactor_num'=>2,//调节poll线程的数量
        'backlog'=>128,
        'max_request'=>500,//worker进程在处理完n次请求后结束运行。manager会重新创建一个worker进程
        'max_conn'=>500,//最大连接数
        'log_file'=>'a.log',
        'heartbeat_check_interval' =>30,//每隔多少秒检测一次，单位秒，超时就关掉
        'heartbeat_idle_time' => 60 //TCP连接的最大闲置时间，单位s , 如果某fd最后一次发包距离现在的时间超过
    );

    function __construct($ip =null ,$port = null,$config = null){

    }

    public function run(){

        out('ip'.$this->_ip.",port:".$this->_port);

        //创建websocket服务器对象，监听0.0.0.0:9502端口
        //SWOOLE_PROCESS 多进程模式
        //TCP 类型的连接,IPV4

        $server = new swoole_websocket_server($this->_ip, $this->_port ,SWOOLE_PROCESS,SWOOLE_TCP);

        eo('new swoole_websocket_server:',$server);
        $rs = $server->set( $this->_config() );

        eo('set config ',$rs);
        try{

        }catch( Exception $e ){

        }

        //监听WebSocket连接打开事件.握手完成后，调用这个方法
        $server->on('open', 'onOpen');
        //监听WebSocket消息事件
        $server->on('message', 'onMessage' );
        //监听WebSocket连接关闭事件
        $server->on('close','onClose');

        $server->start();
    }

}
//建立SOCKET 成功后
function onOpen(){
    $argv = func_get_args();
    $server = $argv[0] ;
    $request = $argv[1];

    eo("a new WS connect,fd:{$request->fd}\n");
    //$request->fd;
    //$request->head;
    //$request->server;
    //$request->data;
    //var_dump($request);

}
//关闭操作
function onClose(){
    $argv = func_get_args();
    $server = $argv[0] ;
    $fd = $argv[1];

    echo "client-{$fd} is closed\n";
}
//C向S端发送数据
function onMessage(){

    $argv = func_get_args();



    $server = $argv[0] ;

    $frame = $argv[1];

    echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";

    if(! $frame->data){

        return false;

    }

    $class = new Server($server,null,$frame,null);
    $class->connect();

    return 444;
    //$msgIdArr = unpack('N',substr( $frame->data,0,4));

    //if($msgIdArr && $msgIdArr[1]){
    //    $msgId = $msgIdArr[1];
    //}
    //var_dump($msgId);
    //var_dump($GLOBALS['cnf_message'] [$msgId]);

    //if(!$GLOBALS['cnf_message'] [$msgId]){
    //    exit('msg id err');
    //}
    $className = $GLOBALS['cnf_message'] [$msgId]['mod'];
    $class = new $className($server,null,$frame,null);
    var_dump($class);
    var_dump($GLOBALS['cnf_message'] [$msgId]['do']);
    $me = $GLOBALS['cnf_message'] [$msgId]['do'];
    $class->$me();

    $server->push($frame->fd, "this is server");

}



$c = new SWServer();

$c->run();
