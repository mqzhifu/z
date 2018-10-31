<?php
class makeAPIList{
    public $host = "http://kjlw2.ifuqi.com/";
    function __construct($c){
        $this->commands = $c;
    }

    public function run($attr){
        for($i=1;$i<=2768;$i++){
            $url = $this->host."forum/forum-143-{$i}.html";
            $html = file_get_contents($url);


        }

    }
}

