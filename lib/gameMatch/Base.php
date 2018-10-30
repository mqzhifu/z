<?php
class Base{

    public $server = null;
    public $request = null;
    public $frame = null;
    public $fd = null;
    public $uid = null;
    public $client_data = null;
    public $config = null;

    function __construct($server = null,$request= null,$frame= null,$fd= null){

        $this->server = $server;
        $this->request = $request;
        $this->frame = $frame;
        $this->fd = $fd;


        $fdinfo = $server->connection_info($this->request->fd);
        if($fdinfo->uid)
            $this->uid = $fdinfo->uid ;


        if($this->frame && $this->frame->data ){
            $this->client_data = $this->frame->data;
        }

        $this->cofnig = array(
            'code'=>$GLOBALS['cnf_code'],
            'main'=>$GLOBALS['cnf_main'],
            'message'=>$GLOBALS['cnf_message'],
            'redis'=>$GLOBALS['cnf_redis'],
            'mysql'=>$GLOBALS['cnf_mysql'],

        );

    }
    function send($uid,$data){
        $this->server->push($this->request->fd,'123');
    }

    function unpackMsg(){

    }

    function packMsg(){

    }

}
