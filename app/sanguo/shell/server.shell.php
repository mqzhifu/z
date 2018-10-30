<?php
class server{
    function __construct($c){
        $this->commands = $c;
    }

    public function run($attr){
        $sw = new SwooleWebSocketLib();
        $sw->run();
    }
}