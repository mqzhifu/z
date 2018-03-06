<?php


define ('BASE_DIR' ,dirname (  dirname (   dirname(__FILE__) ) ) );

$fd = fopen(BASE_DIR."/log/php_access.log","a+");
$str = $_SERVER["REQUEST_URI"] .",,";

foreach($_REQUEST as $k=>$v){
    $str .= $v .",,";
}
$str .= date("Y-m-d H:i:s")."\n";
fwrite($fd,$str);



define('APP_NAME', 'fe');

define('DEF_DB_CONN', 'assistant');
define('DOMAIN', '139.129.243.12');

define('DOMAIN_URL', "http://" .DOMAIN. "/".APP_NAME);

include BASE_DIR.'/z.class.php';

z::init();
z::runWebApp();

