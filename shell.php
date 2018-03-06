<?php
//define('APP_DIR',dirname(__FILE__));
//define('APP_NAME','ADMIN');
//
//define('KERNEL_DIR', dirname(__FILE__) );
//define('DEF_DB_CONN', 'local_test');
//
//define('DOMAIN', 'local.kernel.com');
//
//define('ACCESS_TYPE', 'SHELL');

define ('BASE_DIR' ,   dirname(__FILE__)  );
define('APP_NAME', 'admin');

define('DEF_DB_CONN', 'assistant');
define('DOMAIN', 'local.assistant.cn');

define('DOMAIN_URL', "http://" .DOMAIN. "/".APP_NAME);

include BASE_DIR.'/z.class.php';

try{
    $rs = include_once 'z.class.php';
	Z::init();
    Z::runConsoleApp();
}catch (Exception $e){
    var_dump($e->getMessage());exit;
}
