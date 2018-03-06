<?php
define ('BASE_DIR' ,dirname (  dirname (   dirname(__FILE__) ) ) );
define('APP_NAME', 'admin');

define('DEF_DB_CONN', 'assistant');
define('DOMAIN', 'local.assistant.cn');

define('DOMAIN_URL', "http://" .DOMAIN. "/".APP_NAME);

include BASE_DIR.'/z.class.php';

z::init();
z::runWebApp();

