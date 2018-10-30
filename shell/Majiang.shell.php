<?php
//        $ws->reload(bool $only_reload_taskworkrer = false);
//        $serv->shutdown()
//        swoole_server->stop   //停止 WORK  进程
//        swoole_server->exist  //检查FD 连接 是否存在
//        swoole_server->getClientInfo   //获取  FD client info
//          swoole_server->getClientList   所有连接FD,跨进程
//      swoole_server->bind    //将连接绑定到一个UID
//        array swoole_server->stats();   //得到当前Server的活动TCP连接数，启动时间，accpet/close的总次数等信息。

//swoole_server->task   投递一个异步任务到task_worker池中。此函数是非阻塞的，执行完毕会立即返回。


//主进程内有多个Reactor线程，基于epoll/kqueue进行网络事件轮询。收到数据后转发到worker进程去处理


$GLOBALS['cnf_main']  = include_once('./../config/main.php');
$GLOBALS['cnf_code']  = include_once('./../config/code.php');
$GLOBALS['cnf_message']  = include_once('./../config/message.php');
$GLOBALS['cnf_redis']  = include_once('./../config/redis.php');
$GLOBALS['cnf_mysql']  = include_once('./../config/mysql.php');


spl_autoload_register('autoload');
//===========模型层==================
//defined('M_EXT') or define('M_EXT', '.model.php');
//defined('M_DIR_NAME') or define('M_DIR_NAME', 'model');
defined('M_CLASS') or define('M_CLASS', 'Model');
//===========模型层==================
$GLOBALS['ctrlFile'] = array('Chat','Server','Room','ChampionGame','FastGame','Base','Loader');
$GLOBALS['toolFile'] = array('Func','LzLog');


function autoload($class){
    if( strpos($class,'Model') !== false){
        include_once "Models/".$class.".php";
    }elseif(in_array($class,$GLOBALS['ctrlFile'] )){
        include_once "Controllers/".$class.".php";
    }elseif(in_array($class,$GLOBALS['toolFile'] )){
        include_once "Tools/".$class.".php";
    }elseif( strpos($class,'Request') !== false || strpos($class,'Response') !== false){
        include_once "Protobuf/".$class.".php";
    }else{
        exit($class.' class not found');
    }
}

//Manager进程
//对所有worker进程进行管理，worker进程生命周期结束或者发生异常时自动回收，并创建新的worker进程

function out($title,$data = null){
    $str = $title;
    if($data){
        $str .= json_encode($data);
    }
    echo $str . "\n";
}

class Chat{

    private $redis ;

    const STATUS_ONLINE = 1 ;
    const STATUS_OFFLINE = 2 ;
    const STATUS_PLAYING = 3 ;
    const STATUS_AUTO = 4 ;

    function __construct(){
//        $this->commands = $c;
//		$this->redis = new RedisPHPLib() ;
    }

    function getConfig(){
        return array(
            'dispatch_mode'=>5,//这个东西，牵扯到 FD 绑定 UID，分到到哪个work进程
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
    }


    public function run(){
//		$ip = "127.0.0.1";
        $ip = "0.0.0.0";
        $port = 9502;

        out('ip'.$ip.",port:".$port);

        //创建websocket服务器对象，监听0.0.0.0:9502端口
        //SWOOLE_PROCESS 多进程模式
        //TCP 类型的连接,IPV4
        $server = new swoole_websocket_server($ip, $port ,SWOOLE_PROCESS,SWOOLE_TCP);

        out('new swoole_websocket_server:',$server);

        $rs = $server->set( $this->getConfig() );

        out('set config ',$rs);


        try{

        }catch( Exception $e ){

        }

        //监听WebSocket连接打开事件.握手完成后，调用这个方法
        $server->on('open', function ($server, $request) {
            out("server: handshake success with fd{$request->fd}\n");
            $Base = new Base($server,$request);
            $c = new Server($Base);
            $c->connect();
        });

        //监听WebSocket消息事件
        $server->on('message', function ($server, $frame) {
            echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
            if(! $frame->data){
                return false;
            }

            $msgId = unpack('N',substr( $frame->data,0,4));
            if(!$GLOBALS['cnf_message'] [$msgId]){
                exit('msg id err');
            }

            $class = new $GLOBALS['cnf_message'] [$msgId]['mod'];
            $class->$GLOBALS['cnf_message'] [$msgId]['do'];


            $server->push($frame->fd, "this is server");

        });

        //监听WebSocket连接关闭事件
        $server->on('close', function ($server, $fd) {
            echo "client-{$fd} is closed\n";
        });




        $server->start();

    }



}

$c = new Chat();
$c->run();
