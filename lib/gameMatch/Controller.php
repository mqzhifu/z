<?php
namespace app\Controllers;

use app\Models\DbModel;
use app\Models\UserModel;
use app\Models\MatchModel;
use app\Models\RoomModel;

class Controller  {

    public $server = null;
    public $request = null;
    public $frame = null;
    public $fd = null;
    public $uid = null;
    public $client_data = null;
    public $config = null;

    public $userModel = null;
    public $roomModel = null;
    public $matchModel = null;
    public $dbModel = null;

    function __construct($server = null,$request= null,$frame= null,$fd= null){

        $this->server = $server;
        $this->request = $request;
        $this->frame = $frame;
        $this->fd = $fd;


        $fdinfo = $server->connection_info($this->frame->fd);
        if(isset($fdinfo->uid) && $fdinfo->uid)
            $this->uid = $fdinfo->uid ;


        if($this->frame && $this->frame->data ){
            $data = substr($this->frame->data, 4);
            $this->client_data = array('data' => $data);
            $this->client_data  = (object)$this->client_data;
        }

        $this->cofnig = array(
            'code'=>$GLOBALS['cnf_code'],
            'main'=>$GLOBALS['cnf_main'],
            'message'=>$GLOBALS['cnf_message'],
            'redis'=>$GLOBALS['cnf_redis'],
            'mysql'=>$GLOBALS['cnf_mysql'],

        );

        $this->userModel = new UserModel();
        $this->roomModel = new RoomModel();
        $this->matchModel =new MatchModel();
        $this->dbModel = new DbModel();



    }
    function send( $data ){
        $return_data = $data['msgId'].$data['message'];
        $this->server->push($this->frame->fd,$return_data);
    }
    //将连接绑定到一个UID
    function bindUid($uid){
        if($this->uid      ){
            echo "已经绑定UID了，不要重复";
            return true;
        }
        $this->server->bind($this->frame->fd,$uid);

    }

    function unpackMsg(){

    }

    function packMsg(){

    }

}
