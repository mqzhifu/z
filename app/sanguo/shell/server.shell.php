<?php
class server{
    function __construct($c){
        $this->commands = $c;
    }

    public function run($attr){
        exec('chcp 936');
    }
}